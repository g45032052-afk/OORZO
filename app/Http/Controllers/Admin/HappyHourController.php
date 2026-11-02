<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Exports\HappyHourExport;
use App\Exports\HappyHourRestaurantExport;
use App\Http\Controllers\Controller;
use App\Models\HappyHour;
use App\Models\HappyHourDate;
use App\Models\HappyHourRestaurant;
use App\Models\Restaurant;
use App\Models\Zone;
use App\Traits\HandlesPromotionEnrollment;
use Brian2694\Toastr\Facades\Toastr;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Ported from 6amMart's SurgePriceController. Two differences, both deliberate:
 *
 *  - Overlap boundaries are exclusive. Surge uses whereBetween, which is inclusive on both
 *    ends and would reject a 17:00-18:00 window against an existing 16:00-17:00. Harmless
 *    when durations vary; fatal here, where every window is exactly one hour and
 *    back-to-back slots are the normal case.
 *  - end_time is always derived as start_time + 1h, so custom_times holds bare start times
 *    rather than surge's "H:i - H:i" pairs.
 */
class HappyHourController extends Controller
{
    // The enrolment state machine is shared with the vendor panel and the vendor app, so the
    // three sides cannot disagree about who may decide what on the same row.
    use HandlesPromotionEnrollment;

    private const DURATION_SECONDS = 3600;

    /** MySQL DAYOFWEEK(): 1 = Sunday through 7 = Saturday. */
    private const WEEKDAY_NUMBERS = [
        'Sunday' => 1,
        'Monday' => 2,
        'Tuesday' => 3,
        'Wednesday' => 4,
        'Thursday' => 5,
        'Friday' => 6,
        'Saturday' => 7,
    ];

    public function index()
    {
        $zones = Zone::active()->get();

        return view('admin-views.happy-hour.index', compact('zones'));
    }

    public function list(Request $request)
    {
        // Trimmed and emptied out first: a padded search splits into empty pieces, each of
        // which becomes LIKE '%%' and matches every row - so "  term  " used to answer with
        // the whole list instead of the one match.
        $key = array_values(array_filter(explode(' ', trim($request['search'] ?? ''))));

        $happyHours = HappyHour::with('zone')
            ->withCount('enrollments')
            ->when($request['search'], function ($query) use ($key) {
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('title', 'like', "%{$value}%");
                    }
                });
            })
            ->latest()
            ->paginate(config('default_pagination', 25))
            ->withQueryString();

        return view('admin-views.happy-hour.list', compact('happyHours'));
    }

    public function store(Request $request)
    {
        $validator = $this->buildValidator($request);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        try {
            // Serialised per zone. The overlap check reads happy_hour_dates from inside the
            // transaction, so two creates racing each other each see a table without the
            // other's rows and both succeed - which is how a double-click produced two
            // identical windows. Holding the zone makes the second one check after the first
            // has committed, and it is then refused as an overlap like any other clash.
            $lock = Cache::lock('happy-hour-zone-'.$request->zone_id, 10);

            if (! $lock->block(5)) {
                return response()->json([
                    'errors' => [['code' => 'busy', 'message' => translate('messages.another_happy_hour_is_being_saved_for_this_zone')]],
                ], 409);
            }

            DB::transaction(function () use ($request) {
                $happyHour = new HappyHour();
                $this->fillFromRequest($happyHour, $request);
                $happyHour->cover_image = $request->hasFile('cover_image')
                    ? Helpers::upload(dir: 'happy_hour/', format: 'png', image: $request->file('cover_image'))
                    : null;
                $happyHour->icon = $request->hasFile('icon')
                    ? Helpers::upload(dir: 'happy_hour/', format: 'png', image: $request->file('icon'))
                    : null;
                $happyHour->save();

                Helpers::add_or_update_translations(request: $request, key_data: 'title', name_field: 'title', model_name: 'HappyHour', data_id: $happyHour->id, data_value: $happyHour->title);
                Helpers::add_or_update_translations(request: $request, key_data: 'short_description', name_field: 'short_description', model_name: 'HappyHour', data_id: $happyHour->id, data_value: $happyHour->short_description);

                $this->insertHappyHourDates($happyHour);

                // Zone bound, so only the restaurants that could actually join hear about it.
                $this->notifyVendorsOfNewOffer($happyHour->id, 'zone_'.$happyHour->zone_id.'_restaurant');

                return $happyHour;
            });
        } catch (\Exception $e) {
            return $this->conflictResponse($e);
        } finally {
            if (isset($lock)) {
                $lock->release();
            }
        }

        return response()->json([], 200);
    }

    public function edit($id)
    {
        $happyHour = HappyHour::withoutGlobalScope('translate')->with(['translations', 'dates'])->findOrFail($id);

        // A running window has carts mid-flight priced against it, and no clean fallback
        // the way deletion has. Deleting or switching off is allowed at any time.
        if ($happyHour->isRunningNow()) {
            Toastr::error(translate('messages.an_ongoing_happy_hour_cannot_be_edited'));

            return back();
        }

        $zones = Zone::active()->get();

        return view('admin-views.happy-hour.edit', compact('happyHour', 'zones'));
    }

    public function update(Request $request, $id)
    {
        $happyHour = HappyHour::findOrFail($id);

        if ($happyHour->isRunningNow()) {
            return response()->json([
                'errors' => [['code' => 'ongoing', 'message' => translate('messages.an_ongoing_happy_hour_cannot_be_edited')]],
            ], 403);
        }

        $validator = $this->buildValidator($request, $happyHour);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        try {
            DB::transaction(function () use ($request, $happyHour) {
                $this->fillFromRequest($happyHour, $request);

                if ($request->hasFile('cover_image')) {
                    $happyHour->cover_image = Helpers::update(dir: 'happy_hour/', old_image: $happyHour->cover_image, format: 'png', image: $request->file('cover_image'));
                }

                if ($request->hasFile('icon')) {
                    $happyHour->icon = Helpers::update(dir: 'happy_hour/', old_image: $happyHour->icon, format: 'png', image: $request->file('icon'));
                }

                $happyHour->save();

                Helpers::add_or_update_translations(request: $request, key_data: 'title', name_field: 'title', model_name: 'HappyHour', data_id: $happyHour->id, data_value: $happyHour->title);
                Helpers::add_or_update_translations(request: $request, key_data: 'short_description', name_field: 'short_description', model_name: 'HappyHour', data_id: $happyHour->id, data_value: $happyHour->short_description);

                // Rebuilt wholesale so the record can never conflict with its own old rows.
                $happyHour->dates()->delete();

                $this->insertHappyHourDates($happyHour);
            });
        } catch (\Exception $e) {
            return $this->conflictResponse($e);
        }

        return response()->json([], 200);
    }

    public function view(Request $request, $id)
    {
        $happyHour = HappyHour::with(['zone', 'dates'])->findOrFail($id);
        // Trimmed and emptied out first: a padded search splits into empty pieces, each of
        // which becomes LIKE '%%' and matches every row - so "  term  " used to answer with
        // the whole list instead of the one match.
        $key = array_values(array_filter(explode(' ', trim($request['search'] ?? ''))));

        $enrollments = HappyHourRestaurant::with('restaurant')
            ->where('happy_hour_id', $happyHour->id)
            ->when($request['search'], function ($query) use ($key) {
                $query->whereHas('restaurant', function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                });
            })
            ->latest()
            ->paginate(config('default_pagination', 25))
            ->withQueryString();

        // A happy hour only applies inside its own zone, so only restaurants in that zone
        // can be enrolled. Anyone already on the offer is kept out of the picker too - the
        // pivot is unique per (happy_hour, restaurant). Resolved here rather than in the
        // blade, matching how BogoOfferController::view supplies its own picker list.
        $availableRestaurants = Restaurant::active()
            ->where('zone_id', $happyHour->zone_id)
            ->whereNotIn('id', $happyHour->enrollments()->pluck('restaurant_id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin-views.happy-hour.view', compact('happyHour', 'enrollments', 'availableRestaurants'));
    }

    public function restaurantExport(Request $request, $id)
    {
        try {
            $happyHour = HappyHour::findOrFail($id);
            // Trimmed and emptied out first: a padded search splits into empty pieces, each of
            // which becomes LIKE '%%' and matches every row - so "  term  " used to answer with
            // the whole list instead of the one match.
            $key = array_values(array_filter(explode(' ', trim($request['search'] ?? ''))));

            $enrollments = HappyHourRestaurant::with('restaurant')
                ->where('happy_hour_id', $happyHour->id)
                ->when($request['search'], function ($query) use ($key) {
                    $query->whereHas('restaurant', function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('name', 'like', "%{$value}%");
                        }
                    });
                })
                ->latest()
                ->get();

            $fileName = 'HappyHourRestaurant.'.($request->type == 'csv' ? 'csv' : 'xlsx');

            return Excel::download(new HappyHourRestaurantExport($enrollments, $happyHour, $request['search']), $fileName);
        } catch (\Exception $e) {
            info(["line___{$e->getLine()}", $e->getMessage()]);
            Toastr::error($e->getMessage());

            return back();
        }
    }

    public function export(Request $request)
    {
        try {
            // Trimmed and emptied out first: a padded search splits into empty pieces, each of
            // which becomes LIKE '%%' and matches every row - so "  term  " used to answer with
            // the whole list instead of the one match.
            $key = array_values(array_filter(explode(' ', trim($request['search'] ?? ''))));

            $happyHours = HappyHour::with('zone')
                ->when($request['search'], function ($query) use ($key) {
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('title', 'like', "%{$value}%");
                        }
                    });
                })
                ->latest()
                ->get();

            $fileName = 'HappyHour.'.($request->type == 'csv' ? 'csv' : 'xlsx');

            return Excel::download(new HappyHourExport($happyHours, $request['search']), $fileName);
        } catch (\Exception $e) {
            info(["line___{$e->getLine()}", $e->getMessage()]);
            Toastr::error($e->getMessage());

            return back();
        }
    }

    public function status($id, $status)
    {
        $happyHour = HappyHour::findOrFail($id);

        // Guarded like edit() and delete(): switching a live window off drops every store in
        // it back to full price mid session, while customers are holding baskets priced at
        // the happy hour rate. Turning one on is left alone - that only ever adds a discount.
        //
        // isRunningNow() reads the schedule alone, so a switched off window inside its hours
        // still answers true; the check is therefore on the direction of the change.
        if (! $status && $happyHour->status && $happyHour->isRunningNow()) {
            Toastr::error(translate('messages.an_ongoing_happy_hour_cannot_be_turned_off'));

            return back();
        }

        $happyHour->status = $status;
        $happyHour->save();

        Toastr::success(translate('messages.happy_hour_status_updated'));

        return back();
    }

    public function delete($id)
    {
        $happyHour = HappyHour::findOrFail($id);

        // Guarded like edit() and update(). Without it a live window could be removed mid
        // session, taking its enrolments with it: every store in it would jump back to full
        // price while customers were mid-basket, and the promotion would leave no trace.
        if ($happyHour->isRunningNow()) {
            Toastr::error(translate('messages.an_ongoing_happy_hour_cannot_be_deleted'));

            return back();
        }

        DB::transaction(function () use ($happyHour) {
            $happyHour->dates()->delete();
            $happyHour->enrollments()->delete();
            $happyHour->translations()->delete();
            Helpers::check_and_delete('happy_hour/', $happyHour->cover_image);
            Helpers::check_and_delete('happy_hour/', $happyHour->icon);
            $happyHour->delete();
        });

        Toastr::success(translate('messages.happy_hour_deleted_successfully'));

        // Not back(): deleting from the detail page would send the admin straight back to
        // the record that no longer exists, and findOrFail would 404.
        return redirect()->route('admin.happy-hour.list');
    }

    /**
     * Enrol one or more restaurants.
     *
     * The picker is multi-select, so a batch is the normal case and a single pick is just a
     * batch of one. A batch is not all-or-nothing: an admin selecting twelve stores should
     * not lose the eleven good ones because the twelfth has since been enrolled by the
     * vendor, so ineligible picks are reported back and the rest go in.
     */
    public function addRestaurant(Request $request, $id)
    {
        $happyHour = HappyHour::findOrFail($id);

        // Nothing can be enrolled into a happy hour that is already over - it would never
        // discount a single order. The Add Restaurant button is disabled for the same reason;
        // this is the rule itself, for anything that reaches the endpoint another way.
        // HappyHour::hasEnded() rather than end_date: a custom schedule has no end_date at
        // all, and every one of them read as live here.
        if ($happyHour->hasEnded()) {
            return response()->json(['errors' => [
                ['code' => 'happy_hour', 'message' => translate('messages.this_happy_hour_has_already_ended')],
            ]], 403);
        }

        $validator = Validator::make($request->all(), [
            'restaurant_ids' => 'required|array|min:1',
            'restaurant_ids.*' => 'required|integer',
        ], [
            'restaurant_ids.required' => translate('messages.Please_select_a_restaurant'),
            'restaurant_ids.array' => translate('messages.Please_select_a_restaurant'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $ids = array_values(array_unique(array_map('intval', $request->restaurant_ids)));

        // Both lookups are one query each: the loop below decides per restaurant but must
        // not issue a query per restaurant to do it.
        $restaurants = Restaurant::whereIn('id', $ids)->get()->keyBy('id');
        $enrolled = HappyHourRestaurant::where('happy_hour_id', $happyHour->id)
            ->whereIn('restaurant_id', $ids)
            ->pluck('restaurant_id')
            ->all();

        $addable = [];
        $skipped = [];

        foreach ($ids as $restaurantId) {
            $restaurant = $restaurants->get($restaurantId);

            if (! $restaurant) {
                $skipped[] = ['name' => '#'.$restaurantId, 'message' => translate('messages.not_found')];
                continue;
            }

            // A happy hour only reaches its own zone, so an out-of-zone pick is refused here
            // exactly as it was when one restaurant was added at a time.
            if ($restaurant->zone_id != $happyHour->zone_id) {
                $skipped[] = [
                    'name' => $restaurant->name,
                    'message' => translate('messages.restaurant_is_not_in_this_happy_hour_zone'),
                ];
                continue;
            }

            if (in_array($restaurant->id, $enrolled)) {
                $skipped[] = [
                    'name' => $restaurant->name,
                    'message' => translate('messages.restaurant_already_enrolled_in_this_happy_hour'),
                ];
                continue;
            }

            $addable[] = $restaurant;
        }

        // Nothing survived, so the whole batch is an error rather than a partial success -
        // the admin gets the same shaped response a single bad pick always produced.
        if (! $addable) {
            return response()->json([
                'errors' => array_map(
                    fn ($row) => ['code' => 'restaurant', 'message' => $row['name'].' - '.$row['message']],
                    $skipped
                ),
            ], 403);
        }

        DB::transaction(function () use ($addable, $happyHour) {
            foreach ($addable as $restaurant) {
                // Unlike BOGO, the restaurant gets a say: an admin assignment lands as
                // pending and shows in the vendor panel as "Admin Requested" for them to
                // accept or deny.
                HappyHourRestaurant::create([
                    'happy_hour_id' => $happyHour->id,
                    'restaurant_id' => $restaurant->id,
                    'status' => 'pending',
                    'requested_by' => 'admin',
                    'joined_at' => now(),
                ]);
            }
        });

        // Outside the transaction: a push or mail that fails must not undo enrolments that
        // are already valid.
        foreach ($addable as $restaurant) {
            $this->notifyRestaurantOfInvitation((int) $restaurant->id, $happyHour->id);
        }

        return response()->json([
            'added' => count($addable),
            'skipped' => $skipped,
        ], 200);
    }

    /**
     * The admin's answer to a restaurant's request. Its own request waits on the restaurant,
     * and a decided enrolment is cancelled rather than re-decided - so neither can be
     * confirmed here. There is nothing to rework in a happy hour, so a denied enrolment is
     * cancelled and the restaurant joins again.
     */
    public function restaurantConfirmation(Request $request, $id, $enrollment_id, $status)
    {
        $enrollment = HappyHourRestaurant::where('happy_hour_id', $id)->findOrFail($enrollment_id);

        if ($blocked = $this->adminDecisionBlockedReason($enrollment)) {
            Toastr::error($blocked);

            return back();
        }

        if ($status === 'rejected') {
            // A refusal owes the restaurant a reason, so one is required and must survive
            // trimming - a field of spaces is an empty field. The column is text, so the
            // length cap is the only thing keeping a pasted essay out of the popup.
            $validator = Validator::make($request->all(), [
                'rejection_reason' => 'required|string|max:255',
            ], [
                'rejection_reason.required' => translate('messages.rejection_reason_is_required'),
            ]);

            if ($validator->fails() || trim((string) $request->rejection_reason) === '') {
                Toastr::error($validator->fails()
                    ? $validator->errors()->first()
                    : translate('messages.rejection_reason_is_required'));

                return back();
            }

            $applied = $this->applyPendingDecision($enrollment, [
                'status' => 'rejected',
                'rejected_by' => 'admin',
                // Trimmed so a field of spaces stores as nothing rather than as whitespace.
                'rejection_reason' => trim((string) $request->rejection_reason) ?: null,
            ]);
        } else {
            $applied = $this->applyPendingDecision($enrollment, [
                'status' => 'approved',
                'rejection_reason' => null,
                'rejected_by' => null,
            ]);
        }

        // Someone answered this request between the check above and the write. The row is
        // whatever they made it, so this admin is shown the state rather than a success
        // they did not cause - and the restaurant is not told twice.
        if (! $applied) {
            Toastr::error(translate('messages.only_a_pending_request_can_be_approved_or_rejected'));

            return back();
        }

        $this->notifyRestaurant($enrollment->refresh(), $status);

        Toastr::success($status === 'rejected'
            ? translate('messages.happy_hour_join_request_rejected')
            : translate('messages.restaurant_added_to_happy_hour'));

        return back();
    }

    /**
     * Write an admin decision, but only while the request is still pending.
     *
     * Two admins can open the same request and answer it in the same second. A read, a
     * status check and a save is three steps, so both would pass the check and both would
     * write - the row ends up right, but the loser is told their answer was recorded when
     * the winner's stands, and the restaurant is notified twice. The condition rides on the
     * UPDATE instead, so exactly one of them touches a row and the other gets nothing back.
     *
     * The pivot is unique on (happy_hour_id, restaurant_id) and this is an update, so a
     * duplicate enrolment was never possible either way.
     */
    private function applyPendingDecision(HappyHourRestaurant $enrollment, array $attributes): bool
    {
        return HappyHourRestaurant::whereKey($enrollment->id)
            ->where('status', 'pending')
            ->update($attributes + ['updated_at' => now()]) > 0;
    }

    /**
     * Tells the restaurant its request was approved or rejected. Mail and push are gated
     * separately - an admin can switch off either alone.
     */
    private function notifyRestaurant(HappyHourRestaurant $enrollment, string $status): void
    {
        try {
            $restaurant = Restaurant::with('vendor')->find($enrollment->restaurant_id);

            if (! $restaurant) {
                return;
            }

            $notificationKey = $status === 'rejected'
                ? 'restaurant_happy_hour_join_rejection'
                : 'restaurant_happy_hour_join_approval';

            $message = Helpers::getPushNotificationMessage(
                status: $notificationKey,
                userType: 'restaurant',
                lang: $restaurant->vendor?->current_language_key,
                restaurantName: $restaurant->name
            );

            $pushStatus = Helpers::getRestaurantNotificationStatusData($restaurant->id, $notificationKey);

            if ($pushStatus?->push_notification_status == 'active' && $message && isset($restaurant->vendor->firebase_token)) {
                $data = Helpers::makeDataForPushNotification(
                    title: $status === 'rejected'
                        ? translate('Happy_Hour_Request_Rejected')
                        : translate('Happy_Hour_Request_Approved'),
                    message: $message,
                    orderId: '',
                    type: 'happy_hour',
                    orderStatus: '',
                    dataId: $enrollment->happy_hour_id
                );

                Helpers::send_push_notif_to_device($restaurant->vendor->firebase_token, $data);
                Helpers::insertDataOnNotificationTable($data, 'vendor', $restaurant->vendor_id);
            }

            $mailStatus = Helpers::getNotificationStatusData('restaurant', $notificationKey);
            $mailTab = $status === 'rejected' ? 'happy_hour_deny' : 'happy_hour_approve';

            if ($mailStatus?->mail_status == 'active' && config('mail.status')
                && Helpers::get_mail_status($mailTab.'_mail_status_restaurant') == '1'
                && $restaurant->vendor) {
                Mail::to($restaurant->vendor->getRawOriginal('email'))
                    ->send(new \App\Mail\VendorHappyHourRequestMail(
                        $restaurant->name,
                        $status === 'rejected' ? 'denied' : 'approved'
                    ));
            }
        } catch (\Exception $e) {
            info(['happy_hour_notify' => $e->getMessage()]);
        }
    }

    /**
     * Cancels an admin request or a denied enrolment, and removes an approved restaurant. A
     * restaurant's pending request is rejected instead - it is owed the refusal and a reason.
     */
    public function removeRestaurant($id, $enrollment_id)
    {
        $enrollment = HappyHourRestaurant::where('happy_hour_id', $id)->findOrFail($enrollment_id);

        if ($blocked = $this->adminRemoveBlockedReason($enrollment)) {
            Toastr::error($blocked);

            return back();
        }

        $enrollment->delete();

        Toastr::success(translate('messages.restaurant_removed_from_happy_hour'));

        return back();
    }

    private function buildValidator(Request $request, ?HappyHour $existing = null)
    {
        $ignoreId = $existing?->id;

        $validator = Validator::make($request->all(), [
            'title' => 'required|max:100',
            'title.0' => 'required',
            // Matches the form's own maxlength, so a value that got past the browser
            // (a translation tab filled by script, a replayed request) is refused here.
            'short_description' => 'nullable|max:150',
            'cover_image' => ($ignoreId ? 'nullable' : 'required').'|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'icon' => ($ignoreId ? 'nullable' : 'required').'|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'zone_id' => 'required|integer',
            'discount' => 'required|numeric|min:0|max:100',
            // Required only when the toggle is on: min_order_enabled carries that state, since
            // the checkbox itself posts nothing. Without this the form saved a happy hour whose
            // minimum was switched on and empty.
            'min_order_amount' => 'required_if:min_order_enabled,1|nullable|numeric|min:0',
            'duration_type' => 'required|in:daily,weekly,custom',
            // The expanded rows are per date, so a window has to end on the day it starts.
            // 23:00 itself is out: +1h wraps its end_time to 00:00:00, which then sorts
            // before start_time and makes every "is it running" comparison false. Latest
            // usable start is therefore 22:59.
            'start_time' => 'required_unless:duration_type,custom|nullable|date_format:H:i|before:23:00',
            // Custom carries its dates in custom_days, and a permanent weekly rule has no
            // range at all - it matches on weekday alone - so neither can require one.
            'date_range' => [
                Rule::requiredIf(fn () => $request->duration_type !== 'custom' && ! $request->boolean('is_permanent')),
                'nullable',
                'string',
            ],
            // Comma-joined by the "Select Days" modal.
            'weekly_days' => 'required_if:duration_type,weekly|nullable|string',
            'custom_days' => 'required_if:duration_type,custom|nullable|string',
            'custom_times' => 'required_if:duration_type,custom|nullable|string',
        ], [
            'title.0.required' => translate('default_title_is_required'),
            'start_time.before' => translate('messages.happy_hour_cannot_start_after_23_00'),
        ]);

        // custom_days / custom_times arrive as parallel comma lists, so the per-entry rules
        // cannot be expressed as field rules. Daily and weekly get date_format and the
        // 23:00 cap from start_time above; without this the custom mode would skip both.
        $validator->after(function ($v) use ($request, $existing) {
            $this->validateDateRangeOrder($v, $request, $existing);

            if ($request->duration_type === 'custom') {
                $this->validateCustomSchedule($v, $request, $existing);
            }
        });

        return $validator;
    }

    /**
     * Earliest date a submission may name. Normally today, but an edit keeps whatever start
     * the record already had - otherwise a run that began last week could not be touched
     * without first being dragged forward.
     */
    private function earliestAllowedDate(?HappyHour $existing): string
    {
        $today = now()->toDateString();
        $stored = $existing?->start_date?->format('Y-m-d');

        return $stored && $stored < $today ? $stored : $today;
    }

    private function validateDateRangeOrder($validator, Request $request, ?HappyHour $existing = null): void
    {
        if ($request->duration_type === 'custom' || $request->boolean('is_permanent') || ! $request->date_range) {
            return;
        }

        [$start, $end] = array_pad(explode(' - ', $request->date_range), 2, null);

        // A reversed range makes DatePeriod yield nothing, which would save a happy hour
        // that owns no dates and can therefore never run.
        if ($start && $end && strtotime($end) < strtotime($start)) {
            $validator->errors()->add('date_range', translate('messages.the_end_date_must_be_on_or_after_the_start_date'));
        }

        $earliest = $this->earliestAllowedDate($existing);

        // Expanding into dates that have already passed writes rows that can never fire.
        if ($start && date('Y-m-d', strtotime($start)) < $earliest) {
            $validator->errors()->add('date_range', translate('messages.the_schedule_cannot_start_in_the_past'));
        }

        if ($end && date('Y-m-d', strtotime($end)) < $earliest) {
            $validator->errors()->add('date_range', translate('messages.the_schedule_cannot_end_in_the_past'));
        }
    }

    private function validateCustomSchedule($validator, Request $request, ?HappyHour $existing = null): void
    {
        $days = array_values(array_filter(explode(',', (string) $request->custom_days)));
        $times = array_values(array_filter(explode(',', (string) $request->custom_times)));

        // Mismatched lengths would silently drop the unpaired dates during expansion.
        if (count($days) !== count($times)) {
            $validator->errors()->add('custom_times', translate('messages.every_selected_day_needs_its_own_time'));

            return;
        }

        if (count($days) !== count(array_unique($days))) {
            $validator->errors()->add('custom_days', translate('messages.the_same_day_cannot_be_selected_twice'));
        }

        $today = now()->toDateString();
        // Dates the record already held stay valid on edit; only newly added ones have to
        // be today or later.
        $alreadyStored = array_map(fn ($d) => date('Y-m-d', strtotime($d)), $existing?->custom_days ?? []);

        foreach ($days as $day) {
            if (strtotime($day) === false) {
                $validator->errors()->add('custom_days', translate('messages.the_selected_days_contain_an_invalid_date'));
                break;
            }

            $normalised = date('Y-m-d', strtotime($day));

            if ($normalised < $today && ! in_array($normalised, $alreadyStored, true)) {
                $validator->errors()->add('custom_days', translate('messages.the_selected_days_cannot_be_in_the_past'));
                break;
            }
        }

        foreach ($times as $time) {
            if (! preg_match('/^\d{1,2}:\d{2}$/', $time) || strtotime($time) === false) {
                $validator->errors()->add('custom_times', translate('messages.the_selected_days_contain_an_invalid_time'));
                break;
            }

            // Same one-hour wrap rule the daily and weekly start_time is held to.
            if (strtotime($time) >= strtotime('23:00')) {
                $validator->errors()->add('custom_times', translate('messages.happy_hour_cannot_start_after_23_00'));
                break;
            }
        }
    }

    private function fillFromRequest(HappyHour $happyHour, Request $request): void
    {
        $happyHour->title = $request->title[array_search('default', $request->lang)];
        $happyHour->short_description = $request->short_description[array_search('default', $request->lang)] ?? null;
        $happyHour->zone_id = $request->zone_id;
        $happyHour->discount = $request->discount;
        $happyHour->min_order_amount = $request->min_order_amount;
        $happyHour->duration_type = $request->duration_type;
        $happyHour->is_permanent = $request->duration_type === 'weekly' && $request->boolean('is_permanent');

        if ($request->duration_type === 'custom') {
            $happyHour->custom_days = array_filter(explode(',', $request->custom_days));
            $happyHour->custom_times = array_filter(explode(',', $request->custom_times));
            $happyHour->weekly_days = null;
            $happyHour->start_date = null;
            $happyHour->end_date = null;
            $happyHour->start_time = null;
            $happyHour->end_time = null;

            return;
        }

        $happyHour->custom_days = null;
        $happyHour->custom_times = null;
        $happyHour->weekly_days = $request->duration_type === 'weekly'
            ? array_values(array_filter(explode(',', (string) $request->weekly_days)))
            : null;
        $happyHour->start_time = $this->normaliseTime($request->start_time);
        $happyHour->end_time = $this->deriveEndTime($request->start_time);

        if ($happyHour->is_permanent) {
            $happyHour->start_date = null;
            $happyHour->end_date = null;

            return;
        }

        [$startDate, $endDate] = array_pad(explode(' - ', $request->date_range), 2, null);
        $happyHour->start_date = date('Y-m-d', strtotime($startDate));
        $happyHour->end_date = date('Y-m-d', strtotime($endDate ?: $startDate));
    }

    /**
     * Expands the definition into one row per applicable date. A permanent weekly rule
     * writes nothing - it is matched from the definition at resolve time - but is still
     * checked for conflicts against other permanent rules.
     */
    private function insertHappyHourDates(HappyHour $happyHour): void
    {
        $zoneId = $happyHour->zone_id;

        if ($happyHour->duration_type === 'custom') {
            foreach ($happyHour->custom_days ?? [] as $i => $day) {
                $startTime = $this->normaliseTime($happyHour->custom_times[$i] ?? null);

                if (!$startTime) {
                    continue;
                }

                $endTime = $this->deriveEndTime($startTime);
                $dateStr = date('Y-m-d', strtotime($day));

                $this->guardConflicts($happyHour, $zoneId, $dateStr, date('l', strtotime($day)), $startTime, $endTime);
                $this->writeDate($happyHour, $zoneId, $dateStr, $startTime, $endTime);
            }

            return;
        }

        $startTime = $happyHour->start_time;
        $endTime = $happyHour->end_time;
        $weekdays = $happyHour->weekly_days ?? [];

        if ($happyHour->is_permanent) {
            foreach ($weekdays as $weekday) {
                if ($this->isBlockedByPermanentWeekly($zoneId, $weekday, $startTime, $endTime, $happyHour->id)) {
                    abort(409, "Time conflict: a permanent happy hour already covers {$weekday} in this zone.");
                }
            }

            // Permanent writes no rows of its own, so nothing downstream would ever compare
            // it against dated schedules. Surge iterates its own start_date..end_date here,
            // which is null for a permanent rule - it walks a single day and effectively
            // checks nothing. Every upcoming occurrence is checked instead.
            if ($clash = $this->permanentWeeklyHitsDatedRows($happyHour, $weekdays, $startTime, $endTime)) {
                abort(409, "Time conflict on {$clash} in this zone.");
            }

            return;
        }

        $period = new DatePeriod(
            new DateTime($happyHour->start_date->format('Y-m-d')),
            new DateInterval('P1D'),
            (new DateTime($happyHour->end_date->format('Y-m-d')))->modify('+1 day')
        );

        foreach ($period as $date) {
            $dayName = $date->format('l');

            if ($happyHour->duration_type === 'weekly' && !in_array($dayName, $weekdays, true)) {
                continue;
            }

            $dateStr = $date->format('Y-m-d');

            $this->guardConflicts($happyHour, $zoneId, $dateStr, $dayName, $startTime, $endTime);
            $this->writeDate($happyHour, $zoneId, $dateStr, $startTime, $endTime);
        }
    }

    private function guardConflicts(HappyHour $happyHour, int $zoneId, string $dateStr, string $dayName, string $startTime, string $endTime): void
    {
        if ($this->isBlockedByPermanentWeekly($zoneId, $dayName, $startTime, $endTime, $happyHour->id)) {
            abort(409, "Time conflict: a permanent happy hour already covers {$dayName} in this zone.");
        }

        // Exclusive on both ends, so 16:00-17:00 and 17:00-18:00 do not collide.
        $conflict = DB::table('happy_hour_dates')
            ->where('zone_id', $zoneId)
            ->where('applicable_date', $dateStr)
            ->where('happy_hour_id', '!=', $happyHour->id)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        if ($conflict) {
            abort(409, "Time conflict on {$dateStr} in this zone.");
        }
    }

    /**
     * First upcoming dated row a permanent weekly rule would collide with, or null.
     * DAYOFWEEK is used rather than DAYNAME because DAYNAME follows lc_time_names and
     * would stop matching the English weekdays we store the moment that server var differs.
     */
    private function permanentWeeklyHitsDatedRows(HappyHour $happyHour, array $weekdays, string $startTime, string $endTime): ?string
    {
        $numbers = array_values(array_filter(array_map(
            fn ($day) => self::WEEKDAY_NUMBERS[$day] ?? null,
            $weekdays
        )));

        if (! $numbers) {
            return null;
        }

        $row = DB::table('happy_hour_dates')
            ->where('zone_id', $happyHour->zone_id)
            ->where('happy_hour_id', '!=', $happyHour->id)
            // Past occurrences cannot clash with a rule that starts applying now.
            ->whereDate('applicable_date', '>=', now()->toDateString())
            ->whereRaw('DAYOFWEEK(applicable_date) IN ('.implode(',', $numbers).')')
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->orderBy('applicable_date')
            ->first();

        return $row?->applicable_date;
    }

    private function isBlockedByPermanentWeekly(int $zoneId, string $weekday, string $startTime, string $endTime, ?int $ignoreId = null): bool
    {
        return DB::table('happy_hours')
            ->where('zone_id', $zoneId)
            ->where('duration_type', 'weekly')
            ->where('is_permanent', 1)
            ->whereJsonContains('weekly_days', $weekday)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    }

    private function writeDate(HappyHour $happyHour, int $zoneId, string $dateStr, string $startTime, string $endTime): void
    {
        HappyHourDate::create([
            'happy_hour_id' => $happyHour->id,
            'zone_id' => $zoneId,
            'status' => 1,
            'applicable_date' => $dateStr,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
    }

    private function normaliseTime(?string $time): ?string
    {
        return $time ? date('H:i:s', strtotime($time)) : null;
    }

    private function deriveEndTime(?string $startTime): ?string
    {
        return $startTime ? date('H:i:s', strtotime($this->normaliseTime($startTime)) + self::DURATION_SECONDS) : null;
    }

    private function conflictResponse(\Exception $e)
    {
        $message = $e->getMessage();

        if (str_contains(strtolower($message), 'time conflict')) {
            return response()->json(['errors' => [['code' => 'conflict', 'message' => $message]]], 409);
        }

        info(["line___{$e->getLine()}", $message]);

        return response()->json(['errors' => [['code' => 'server', 'message' => $message]]], 500);
    }

    /**
     * Live nudge the moment the admin assigns this restaurant. The standing panel popup
     * catches it on the next page load either way; this is what makes it arrive without one,
     * on both the panel (FCM onMessage) and the vendor app.
     */
    private function notifyRestaurantOfInvitation(int $restaurantId, $promotionId): void
    {
        try {
            $restaurant = Restaurant::with('vendor')->find($restaurantId);

            if (! $restaurant || ! isset($restaurant->vendor->firebase_token)) {
                return;
            }

            $key = 'restaurant_happy_hour_invitation';
            $pushStatus = Helpers::getRestaurantNotificationStatusData($restaurant->id, $key);

            if ($pushStatus?->push_notification_status != 'active') {
                return;
            }

            $message = Helpers::getPushNotificationMessage(
                status: $key,
                userType: 'restaurant',
                lang: $restaurant->vendor?->current_language_key,
                restaurantName: $restaurant->name
            ) ?: translate('messages.The_Admin_has_invited_you_to_join_a_Happy_Hour_Review_and_approve_the_request_to_participate');

            $data = Helpers::makeDataForPushNotification(
                title: translate('messages.Happy_Hour_Request'),
                message: $message,
                orderId: '',
                type: 'happy_hour',
                orderStatus: '',
                dataId: $promotionId
            );

            Helpers::send_push_notif_to_device($restaurant->vendor->firebase_token, $data);
            Helpers::insertDataOnNotificationTable($data, 'vendor', $restaurant->vendor_id);
        } catch (\Exception $e) {
            info(['happy_hour_invitation_notify' => $e->getMessage()]);
        }
    }

    /**
     * Announces a newly published promotion to the restaurants that can act on it.
     *
     * Sent to an FCM topic rather than per device: a broadcast to every restaurant would
     * otherwise be one HTTP call each, inside the admin's save request.
     */
    private function notifyVendorsOfNewOffer($promotionId, $topic): void
    {
        try {
            $data = Helpers::makeDataForPushNotification(
                title: translate('messages.New_Happy_Hour_Available'),
                message: translate('messages.Join_the_new_Happy_Hour_promotion_and_attract_more_customers_during_selected_hours'),
                orderId: '',
                type: 'happy_hour',
                orderStatus: '',
                dataId: $promotionId
            );

            Helpers::send_push_notif_to_topic($data, $topic, 'happy_hour');
        } catch (\Exception $e) {
            info(['happy_hour_broadcast' => $e->getMessage()]);
        }
    }
}
