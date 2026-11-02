<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Exports\BogoOfferExport;
use App\Exports\BogoOfferRestaurantExport;
use App\Http\Controllers\Controller;
use App\Traits\HandlesBogoEnrollment;
use App\Models\AddOn;
use App\Models\BogoOffer;
use App\Models\BogoOfferItem;
use App\Models\Cart;
use App\Models\BogoOfferRestaurant;
use App\Models\BogoOfferUsage;
use App\Models\Food;
use App\Models\Restaurant;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class BogoOfferController extends Controller
{
    use HandlesBogoEnrollment;

    public function index()
    {
        return view('admin-views.bogo-offer.index');
    }

    public function list(Request $request)
    {
        // Trimmed and emptied out first: a padded search splits into empty pieces, each of
        // which becomes LIKE '%%' and matches every row - so "  term  " used to answer with
        // the whole list instead of the one match.
        $key = array_values(array_filter(explode(' ', trim($request['search'] ?? ''))));

        // A denied request is not a restaurant on the offer - counting it made the column read
        // higher than the number of restaurants that could ever serve the bundle, and nothing
        // on the page said so. Approved plus still-to-be-decided is what the number means; the
        // tooltip splits it so the reader does not have to open the offer to find out.
        $offers = BogoOffer::withCount([
            'enrollments as enrollments_count' => fn ($q) => $q->whereIn('status', ['approved', 'pending']),
            'enrollments as approved_count' => fn ($q) => $q->where('status', 'approved'),
        ])
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

        return view('admin-views.bogo-offer.list', compact('offers'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // title/description arrive as per-language arrays, so the length rule has to be
            // on the elements - on the array itself it would only bound the element count.
            'title' => 'required|array',
            'title.*' => 'nullable|max:100',
            'description' => 'nullable|array',
            'description.*' => 'nullable|max:150',
            'image' => 'required|mimes:jpeg,jpg,png,gif,webp|max:2048',
            // max:100 is repeated here on purpose: naming title.0 explicitly replaces the
            // title.* wildcard for index 0, so without it the default language title -
            // the one that is actually stored - had no length limit at all.
            'title.0' => 'required|max:100',
            // No upper bound: Buy 9 Get 18 is a valid offer.
            'buy_qty' => 'required|integer|min:1',
            'get_qty' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'usage_limit_total' => 'nullable|integer|min:1',
            // Bounded by the total when one is set: a per-customer allowance larger than the
            // offer's own allowance can never be reached, so it is a mistake rather than a
            // generous setting. Without a total there is nothing to exceed.
            'usage_limit_per_customer' => 'nullable|integer|min:1'
                .($request->usage_limit_total ? '|lte:usage_limit_total' : ''),
            // Not posted while the picker is hidden - see BogoOffer::ORDER_TYPES_ENABLED. The
            // rule is kept and simply relaxed, so re-enabling the constant restores it.
            'order_types' => (BogoOffer::orderTypesEnabled() ? 'required' : 'nullable').'|array'
                .(BogoOffer::orderTypesEnabled() ? '|min:1' : ''),
            'order_types.*' => 'in:delivery,take_away,dine_in',
        ], [
            'title.0.required' => translate('default_title_is_required'),
            'order_types.required' => translate('At least one order type must be selected'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        // Saving, pressing browser back and saving again posts the identical form a second
        // time - a fresh page load, so no client side guard catches it. An offer with the same
        // title running over the same window for the same quantities is that resubmission
        // rather than a second offer anyone meant to create.
        $duplicate = BogoOffer::where('title', $request->title[array_search('default', $request->lang)])
            ->where('buy_qty', $request->buy_qty)
            ->where('get_qty', $request->get_qty)
            ->whereDate('start_date', date('Y-m-d', strtotime($request->start_date)))
            ->whereDate('end_date', date('Y-m-d', strtotime($request->end_date)))
            ->exists();

        if ($duplicate) {
            return response()->json(['errors' => [
                ['code' => 'title', 'message' => translate('messages.This_offer_has_already_been_created')],
            ]]);
        }

        $offer = new BogoOffer();
        $offer->title = $request->title[array_search('default', $request->lang)];
        $offer->description = $request->description[array_search('default', $request->lang)] ?? null;
        $offer->image = Helpers::upload(dir: 'bogo_offer/', format: 'png', image: $request->file('image'));
        $offer->buy_qty = $request->buy_qty;
        $offer->get_qty = $request->get_qty;
        $offer->start_date = $request->start_date;
        $offer->end_date = $request->end_date;
        $offer->usage_limit_total = $request->usage_limit_total;
        $offer->usage_limit_per_customer = $request->usage_limit_per_customer;
        // Every type while the picker is hidden, so an offer created now is unrestricted rather
        // than restricted to nothing once order types come back.
        $offer->order_types = BogoOffer::orderTypesEnabled()
            ? $request->order_types
            : ['delivery', 'take_away', 'dine_in'];
        $offer->admin_id = auth('admin')->id();
        $offer->save();

        Helpers::add_or_update_translations(request: $request, key_data: 'title', name_field: 'title', model_name: 'BogoOffer', data_id: $offer->id, data_value: $offer->title);
        Helpers::add_or_update_translations(request: $request, key_data: 'description', name_field: 'description', model_name: 'BogoOffer', data_id: $offer->id, data_value: $offer->description);

        // BOGO is not zone bound, so every restaurant is a candidate.
        $this->notifyVendorsOfNewOffer($offer->id, 'all_zone_restaurant');

        return response()->json([], 200);
    }

    public function edit($id)
    {
        $offer = BogoOffer::withoutGlobalScope('translate')->with('translations')->findOrFail($id);

        return view('admin-views.bogo-offer.edit', [
            'offer' => $offer,
            'quantity_locked' => $offer->isQuantityLocked(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $offer = BogoOffer::findOrFail($id);
        $quantityLocked = $offer->isQuantityLocked();

        $validator = Validator::make($request->all(), [
            // title/description arrive as per-language arrays, so the length rule has to be
            // on the elements - on the array itself it would only bound the element count.
            'title' => 'required|array',
            'title.*' => 'nullable|max:100',
            'description' => 'nullable|array',
            'description.*' => 'nullable|max:150',
            // An offer always carries an image. On update the upload is optional only
            // because the stored one stands in for it - with nothing stored there is
            // nothing to fall back on, so a file has to be given.
            'image' => ($offer->image ? 'nullable' : 'required').'|mimes:jpeg,jpg,png,gif,webp|max:2048',
            // max:100 is repeated here on purpose: naming title.0 explicitly replaces the
            // title.* wildcard for index 0, so without it the default language title -
            // the one that is actually stored - had no length limit at all.
            'title.0' => 'required|max:100',
            'buy_qty' => 'required|integer|min:1',
            'get_qty' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'usage_limit_total' => 'nullable|integer|min:1',
            // Bounded by the total when one is set: a per-customer allowance larger than the
            // offer's own allowance can never be reached, so it is a mistake rather than a
            // generous setting. Without a total there is nothing to exceed.
            'usage_limit_per_customer' => 'nullable|integer|min:1'
                .($request->usage_limit_total ? '|lte:usage_limit_total' : ''),
            // Not posted while the picker is hidden - see BogoOffer::ORDER_TYPES_ENABLED. The
            // rule is kept and simply relaxed, so re-enabling the constant restores it.
            'order_types' => (BogoOffer::orderTypesEnabled() ? 'required' : 'nullable').'|array'
                .(BogoOffer::orderTypesEnabled() ? '|min:1' : ''),
            'order_types.*' => 'in:delivery,take_away,dine_in',
        ], [
            'title.0.required' => translate('default_title_is_required'),
        ]);

        // Saving a switched-on offer whose end date has already passed would leave it looking
        // live while nothing can be ordered from it, so a future end date is required to keep
        // it on. A switched-off offer stays editable, which is what lets an old one be
        // corrected or revived by setting a new window first.
        $validator->after(function ($validator) use ($request, $offer) {
            if ($offer->status && strtotime($request->end_date) < time()) {
                $validator->errors()->add('end_date', translate('messages.the_end_date_must_be_in_the_future'));
            }
        });

        // Enrolled item sets are built against buy_qty/get_qty, so once anyone has joined
        // there is no way to reconcile a change to either.
        $validator->after(function ($validator) use ($request, $offer, $quantityLocked) {
            if (!$quantityLocked) {
                return;
            }

            if ((int) $request->buy_qty !== $offer->buy_qty || (int) $request->get_qty !== $offer->get_qty) {
                $validator->errors()->add('buy_qty', translate('messages.bogo_quantity_locked_after_join'));
            }
        });

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $offer->title = $request->title[array_search('default', $request->lang)];
        $offer->description = $request->description[array_search('default', $request->lang)] ?? null;
        $offer->image = $request->has('image')
            ? Helpers::update(dir: 'bogo_offer/', old_image: $offer->image, format: 'png', image: $request->file('image'))
            : $offer->image;
        $offer->buy_qty = $request->buy_qty;
        $offer->get_qty = $request->get_qty;
        $offer->start_date = $request->start_date;
        $offer->end_date = $request->end_date;
        $offer->usage_limit_total = $request->usage_limit_total;
        $offer->usage_limit_per_customer = $request->usage_limit_per_customer;
        // Left exactly as it was while the picker is hidden: an edit must not quietly wipe a
        // selection the form no longer shows.
        if (BogoOffer::orderTypesEnabled()) {
            $offer->order_types = $request->order_types;
        }
        $offer->save();

        Helpers::add_or_update_translations(request: $request, key_data: 'title', name_field: 'title', model_name: 'BogoOffer', data_id: $offer->id, data_value: $offer->title);
        Helpers::add_or_update_translations(request: $request, key_data: 'description', name_field: 'description', model_name: 'BogoOffer', data_id: $offer->id, data_value: $offer->description);

        // The restaurants already on the offer are running it, so a change to its terms is
        // news to them - the same notification the offer's creation sends.
        $this->notifyVendorsOfNewOffer($offer->id, 'all_zone_restaurant');

        return response()->json([], 200);
    }

    public function view(Request $request, $id)
    {
        // Counted for the delete warning: the admin is told how many restaurants go with the
        // offer before it is taken away from them.
        $offer = BogoOffer::withCount([
            'enrollments as joined_count' => fn ($q) => $q->whereIn('status', ['approved', 'pending']),
        ])->findOrFail($id);
        // Trimmed and emptied out first: a padded search splits into empty pieces, each of
        // which becomes LIKE '%%' and matches every row - so "  term  " used to answer with
        // the whole list instead of the one match.
        $key = array_values(array_filter(explode(' ', trim($request['search'] ?? ''))));

        $enrollments = BogoOfferRestaurant::with(['restaurant', 'items', 'buyItems', 'getItems'])
            ->where('bogo_offer_id', $offer->id)
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

        // Approving is not publishing, and the row said only "Approved" - so an admin asked
        // "why is our bundle not showing?" had to open every drawer in turn to find out. The
        // same check the drawer runs, asked for the whole page at once, lets the row carry a
        // warning that names the reason on hover. Only approved rows have a customer-facing
        // state to report; every other status is still waiting on somebody, which the status
        // badge already says.
        $hiddenReasons = $this->customerVisibilityProblemsFor(
            $enrollments->getCollection()
                ->where('status', 'approved')
                // The offer is the page's own, and the restaurant is loaded with the row, so
                // neither has to be fetched again per enrolment.
                ->each(fn ($enrollment) => $enrollment->setRelation('bogo_offer', $offer))
        );

        // Frozen items keep add-on ids only, so resolve the names once for the whole page
        // rather than per row.
        $addOnIds = $enrollments->getCollection()
            ->flatMap(fn ($enrollment) => $enrollment->items)
            ->flatMap(fn ($item) => $item->add_on_ids ?? [])
            ->unique()
            ->all();

        $addOnNames = AddOn::whereIn('id', $addOnIds)->pluck('name', 'id');
        // Prices too: an add-on shown by name alone said nothing about what it adds.
        $addOnPrices = AddOn::whereIn('id', $addOnIds)->pluck('price', 'id');

        // A restaurant enrols in a given offer once only, so anyone already on the offer is
        // kept out of the Add Restaurant picker.
        $availableRestaurants = Restaurant::active()
            ->whereNotIn('id', $offer->enrollments()->pluck('restaurant_id'))
            ->get(['id', 'name']);

        return view('admin-views.bogo-offer.view', compact('offer', 'enrollments', 'addOnNames', 'addOnPrices', 'availableRestaurants', 'hiddenReasons'));
    }

    /**
     * Drawer body for a single enrolment, opened from the eye action and the "N Foods" link.
     */
    public function enrollmentDetail($id, $enrollment_id)
    {
        $offer = BogoOffer::findOrFail($id);
        $enrollment = BogoOfferRestaurant::with(['restaurant', 'buyItems', 'getItems'])
            ->where('bogo_offer_id', $offer->id)
            ->findOrFail($enrollment_id);

        $addOnIds = $enrollment->items->flatMap(fn ($item) => $item->add_on_ids ?? [])->unique()->all();
        $addOnNames = AddOn::whereIn('id', $addOnIds)->pluck('name', 'id');
        // Prices too: an add-on shown by name alone said nothing about what it adds.
        $addOnPrices = AddOn::whereIn('id', $addOnIds)->pluck('price', 'id');

        // Approving is not publishing. The drawer said "Approved" and stopped there, so an
        // admin fielding "why is our bundle not showing?" had nothing to answer with - the
        // vendor panel already worked this out and the admin panel did not. Same check.
        $hiddenReasons = $enrollment->status === 'approved'
            ? $this->customerVisibilityProblems($enrollment, $offer)
            : [];

        return view('admin-views.bogo-offer.partials._enrollment_detail', compact('offer', 'enrollment', 'addOnNames', 'addOnPrices', 'hiddenReasons'));
    }

    public function restaurantExport(Request $request, $id)
    {
        try {
            $offer = BogoOffer::findOrFail($id);
            // Trimmed and emptied out first: a padded search splits into empty pieces, each of
            // which becomes LIKE '%%' and matches every row - so "  term  " used to answer with
            // the whole list instead of the one match.
            $key = array_values(array_filter(explode(' ', trim($request['search'] ?? ''))));

            $enrollments = BogoOfferRestaurant::with(['restaurant', 'buyItems', 'getItems'])
                ->where('bogo_offer_id', $offer->id)
                ->when($request['search'], function ($query) use ($key) {
                    $query->whereHas('restaurant', function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('name', 'like', "%{$value}%");
                        }
                    });
                })
                ->latest()
                ->get();

            $fileName = 'BogoOfferRestaurant.'.($request->type == 'csv' ? 'csv' : 'xlsx');

            return Excel::download(new BogoOfferRestaurantExport($enrollments, $offer, $request['search']), $fileName);
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

            // Same rule as the list, or the export would contradict the screen it came from.
            $offers = BogoOffer::withCount([
                'enrollments as enrollments_count' => fn ($q) => $q->whereIn('status', ['approved', 'pending']),
            ])
                ->when($request['search'], function ($query) use ($key) {
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('title', 'like', "%{$value}%");
                        }
                    });
                })
                ->latest()
                ->get();

            $fileName = 'BogoOffer.'.($request->type == 'csv' ? 'csv' : 'xlsx');

            return Excel::download(new BogoOfferExport($offers, $request['search']), $fileName);
        } catch (\Exception $e) {
            info(["line___{$e->getLine()}", $e->getMessage()]);
            Toastr::error($e->getMessage());

            return back();
        }
    }

    public function status($id, $status)
    {
        $offer = BogoOffer::findOrFail($id);
        $wasOn = (bool) $offer->status;
        $offer->status = $status;
        $offer->save();

        // Switching an offer off takes it off every vendor list and stops the bundle selling,
        // which is the same thing deleting it does to the restaurants on it - so they are told
        // the same way. Without this the offer simply vanished from their panel unannounced.
        if ($wasOn && ! $status) {
            foreach ($offer->enrollments()->pluck('restaurant_id') as $restaurantId) {
                $this->notifyRestaurantOfOfferWithdrawn((int) $restaurantId, $offer->id);
            }

        }

        // Switching an expired offer back on changes nothing a customer can see - the read
        // endpoints filter on the date window - so say so rather than leaving the admin with
        // a success message and an offer that never appears.
        if ($status && $offer->end_date && $offer->end_date->isPast()) {
            Toastr::warning(translate('messages.this_offer_has_already_ended_set_a_new_duration_to_make_it_available_again'));

            return back();
        }

        Toastr::success(translate('messages.bogo_offer_status_updated'));

        return back();
    }

    public function delete($id)
    {
        $offer = BogoOffer::findOrFail($id);

        // Told before the rows go, while the enrolments still say who was on it. Only those
        // restaurants are told, and they are told it has been withdrawn: this used to send the
        // "a new BOGO is available, enrol now" broadcast to every restaurant in every zone, so
        // deleting an offer invited the whole platform to join the thing that had just gone.
        foreach ($offer->enrollments()->pluck('restaurant_id') as $restaurantId) {
            $this->notifyRestaurantOfOfferWithdrawn((int) $restaurantId, $offer->id);
        }

        DB::transaction(function () use ($offer) {
            BogoOfferItem::whereIn('bogo_offer_restaurant_id', $offer->enrollments()->pluck('id'))->delete();
            $offer->enrollments()->delete();
            $offer->translations()->delete();

            // The caps these rows count for die with the offer, and left behind they point at
            // an id that no longer exists.
            BogoOfferUsage::where('bogo_offer_id', $offer->id)->delete();

            Helpers::check_and_delete('bogo_offer/', $offer->image);
            $offer->delete();
        });

        Toastr::success(translate('messages.bogo_offer_deleted_successfully'));

        // Not back(): deleting from the detail page would send the admin straight back to
        // the record that no longer exists, and findOrFail would 404.
        return redirect()->route('admin.bogo-offer.list');
    }

    /**
     * Admin-initiated enrolment. The admin picks the foods themselves, so there is nothing
     * left for the restaurant to submit - it lands approved, matching how CampaignController
     * attaches a restaurant as 'confirmed'. A vendor-initiated join still arrives pending.
     */
    public function addRestaurant(Request $request, $id)
    {
        $offer = BogoOffer::findOrFail($id);

        // Nothing can be enrolled into an offer that is already over - the bundle would never
        // be served once. The Add Restaurant button is disabled for the same reason; this is
        // the rule itself, for anything that reaches the endpoint another way.
        if ($this->bogoOfferHasEnded($offer)) {
            return response()->json(['errors' => [
                ['code' => 'offer', 'message' => translate('messages.this_offer_has_already_ended')],
            ]], 403);
        }

        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required|integer',
            'buy_items' => 'required|array|min:1',
            'get_items' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if (BogoOfferRestaurant::where('bogo_offer_id', $offer->id)->where('restaurant_id', $request->restaurant_id)->exists()) {
            return response()->json(['errors' => [['code' => 'restaurant', 'message' => translate('messages.restaurant_already_enrolled_in_this_offer')]]], 403);
        }

        $errors = $this->validateEnrollmentItems($offer, $request->buy_items, $request->get_items, (int) $request->restaurant_id);

        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 403);
        }

        $signature = $this->buildCombinationSignature($request->buy_items, $request->get_items);

        if ($this->combinationTaken((int) $request->restaurant_id, $signature)) {
            return response()->json(['errors' => [['code' => 'combination', 'message' => translate('messages.this_buy_get_combination_is_already_used_by_the_restaurant')]]], 403);
        }

        DB::transaction(function () use ($offer, $request, $signature) {
            // Pending, not approved: the restaurant gets a say. It shows in the vendor panel
            // as "Admin Requested", where they accept, decline, or edit the food picks and
            // send it back for re-approval.
            $enrollment = BogoOfferRestaurant::create([
                'bogo_offer_id' => $offer->id,
                'restaurant_id' => $request->restaurant_id,
                'status' => 'pending',
                'requested_by' => 'admin',
                'joined_at' => now(),
                'combination_signature' => $signature,
            ]);

            $enrollment->bundle_price = $this->syncEnrollmentItems($enrollment, $request->buy_items, $request->get_items);
            $enrollment->save();
        });

        $this->notifyRestaurantOfInvitation((int) $request->restaurant_id, $offer->id);

        Toastr::success(translate('messages.restaurant_added_to_bogo_offer'));

        return response()->json([], 200);
    }

    /**
     * Backs both edit buttons, which are the same save with different outcomes decided by the
     * state rather than by the caller:
     *
     *   pending  -> "Edit & Approve"  : the restaurant asked, the admin reworks and approves.
     *   rejected -> "Edit & Resubmit" : the admin reworks a denied enrolment and sends it back
     *                                   as its own request, so the restaurant still gets the
     *                                   last word on foods it did not choose.
     *
     * Both re-run the full validation and re-freeze the snapshot, so an edited enrolment is
     * held to exactly the rules a new one is.
     */
    public function updateEnrollment(Request $request, $id, $enrollment_id)
    {
        $offer = BogoOffer::findOrFail($id);
        $enrollment = BogoOfferRestaurant::where('bogo_offer_id', $offer->id)->findOrFail($enrollment_id);

        $state = $this->enrollmentState($enrollment);

        // An admin request the restaurant has not answered is not the admin's to re-decide,
        // and an approved enrolment is the restaurant's own selection to change.
        if (! in_array($state, ['pending', 'rejected'], true)) {
            return response()->json(['errors' => [[
                'code' => 'status',
                'message' => translate('messages.only_a_pending_or_rejected_enrolment_can_be_edited_here'),
            ]]], 403);
        }

        $validator = Validator::make($request->all(), [
            'buy_items' => 'required|array|min:1',
            'get_items' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $errors = $this->validateEnrollmentItems($offer, $request->buy_items, $request->get_items, (int) $enrollment->restaurant_id);

        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 403);
        }

        $signature = $this->buildCombinationSignature($request->buy_items, $request->get_items);

        if ($this->combinationTaken((int) $enrollment->restaurant_id, $signature, $enrollment->id)) {
            return response()->json(['errors' => [['code' => 'combination', 'message' => translate('messages.this_buy_get_combination_is_already_used_by_the_restaurant')]]], 403);
        }

        // A reworked denial goes back out as an admin request; a reworked pending request is
        // the admin approving what it just edited.
        $resubmitting = $state === 'rejected';

        DB::transaction(function () use ($enrollment, $request, $signature, $resubmitting) {
            $enrollment->bundle_price = $this->syncEnrollmentItems($enrollment, $request->buy_items, $request->get_items);
            $enrollment->combination_signature = $signature;
            $enrollment->status = $resubmitting ? 'pending' : 'approved';
            $enrollment->requested_by = $resubmitting ? 'admin' : $enrollment->requested_by;
            // A reworked denial is a fresh invitation, so it is unread again and the vendor
            // dashboard announces it - even if the restaurant had already seen the old one.
            $enrollment->checked = $resubmitting ? 0 : $enrollment->checked;
            $enrollment->rejection_reason = null;
            $enrollment->rejected_by = null;
            $enrollment->save();
        });

        if ($resubmitting) {
            // Same message the initial assignment sends: from the restaurant's side this is
            // a fresh admin request waiting on its answer.
            $this->notifyRestaurantOfInvitation((int) $enrollment->restaurant_id, $offer->id);
        }

        Toastr::success($resubmitting
            ? translate('messages.the_request_has_been_resent_to_the_restaurant')
            : translate('messages.restaurant_added_to_bogo_offer'));

        return response()->json([], 200);
    }

    /**
     * The admin's answer to a restaurant's request. Its own request waits on the restaurant,
     * and a decided enrolment is reworked or removed rather than re-decided - so neither can
     * be confirmed here.
     */
    public function restaurantConfirmation(Request $request, $id, $enrollment_id, $status)
    {
        $offer = BogoOffer::findOrFail($id);
        $enrollment = BogoOfferRestaurant::where('bogo_offer_id', $offer->id)->findOrFail($enrollment_id);

        if ($blocked = $this->adminDecisionBlockedReason($enrollment)) {
            Toastr::error($blocked);

            return back();
        }

        if ($status === 'rejected') {
            // A refusal owes the restaurant a reason, so one is required and must survive
            // trimming - a field of spaces is an empty field. The column is text, so the
            // length cap is the only thing keeping a pasted essay out of the popup. 1000 is
            // the same number the textarea's maxlength enforces, so the client and the server
            // refuse at the same point.
            $validator = Validator::make($request->all(), [
                'rejection_reason' => 'required|string|max:1000',
            ], [
                'rejection_reason.required' => translate('messages.rejection_reason_is_required'),
            ]);

            if ($validator->fails() || trim((string) $request->rejection_reason) === '') {
                Toastr::error($validator->fails()
                    ? $validator->errors()->first()
                    : translate('messages.rejection_reason_is_required'));

                return back();
            }

            $this->markRejected($enrollment, 'admin', $request->rejection_reason);
        } else {
            // The selection was validated when it was submitted, not now. Between then and
            // this click the food can have been deleted or its variation or add-on dropped,
            // and approving that publishes a bundle nobody can assemble.
            $problems = $this->enrollmentIntegrityProblems($enrollment);

            if (! empty($problems['blocking'])) {
                Toastr::error(translate('messages.this_selection_can_no_longer_be_built').' '.implode(' | ', $problems['blocking']));

                return back();
            }

            $enrollment->status = 'approved';
            $enrollment->rejection_reason = null;
            $enrollment->rejected_by = null;
            $enrollment->save();

            // Approved, but the customer will not see it until the restaurant puts the food
            // back - so say which food, rather than leaving the admin to wonder why a live
            // offer is nowhere on the app.
            if (! empty($problems['warnings'])) {
                Toastr::warning(translate('messages.this_offer_stays_hidden_until_the_food_is_available_again')
                    .' '.implode(' | ', $problems['warnings']));
            }
        }

        $this->notifyRestaurant($offer, $enrollment, $status);

        Toastr::success($status === 'rejected'
            ? translate('messages.bogo_join_request_rejected')
            : translate('messages.restaurant_added_to_bogo_offer'));

        return back();
    }

    /**
     * Cancels an admin request or a denied enrolment, and removes an approved restaurant. A
     * restaurant's pending request is rejected instead - it is owed the refusal and a reason.
     */
    public function removeRestaurant($id, $enrollment_id)
    {
        $enrollment = BogoOfferRestaurant::where('bogo_offer_id', $id)->findOrFail($enrollment_id);

        if ($blocked = $this->adminRemoveBlockedReason($enrollment)) {
            Toastr::error($blocked);

            return back();
        }

        DB::transaction(function () use ($enrollment) {
            $enrollment->items()->delete();
            $enrollment->delete();
        });

        Toastr::success(translate('messages.restaurant_removed_from_bogo_offer'));

        return back();
    }

    /**
     * Feeds the "Select Food" picker in the Add Restaurant drawer. Scoped to the chosen
     * restaurant, and flags unavailable items so the picker can grey them out rather than
     * hide them.
     */
    public function restaurantFoods(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        return response()->json($this->bogoRestaurantFoods((int) $request->restaurant_id, $request['search']), 200);
    }

    private function notifyRestaurant(BogoOffer $offer, BogoOfferRestaurant $enrollment, string $status): void
    {
        try {
            $restaurant = Restaurant::find($enrollment->restaurant_id);

            if (!$restaurant) {
                return;
            }

            $title = $status === 'rejected'
                ? translate('BOGO_Request_Rejected')
                : translate('BOGO_Request_Approved');

            // Dedicated BOGO keys: borrowing the campaign ones meant switching campaign
            // notifications off silently killed these too.
            $notificationKey = $status === 'rejected'
                ? 'restaurant_bogo_join_rejection'
                : 'restaurant_bogo_join_approval';

            $message = Helpers::getPushNotificationMessage(
                status: $notificationKey,
                userType: 'restaurant',
                lang: $restaurant?->vendor?->current_language_key,
                restaurantName: $restaurant?->name
            );

            $pushStatus = Helpers::getRestaurantNotificationStatusData($restaurant->id, $notificationKey);

            if ($pushStatus?->push_notification_status == 'active' && $message && isset($restaurant->vendor->firebase_token)) {
                $data = Helpers::makeDataForPushNotification(
                    title: $title,
                    message: $message,
                    orderId: '',
                    type: 'bogo_offer',
                    orderStatus: '',
                    dataId: $offer->id
                );

                Helpers::send_push_notif_to_device($restaurant->vendor->firebase_token, $data);
                Helpers::insertDataOnNotificationTable($data, 'vendor', $restaurant->vendor_id);
            }

            // Mail is gated separately from push: an admin can switch either off alone.
            $mailStatus = Helpers::getNotificationStatusData('restaurant', $notificationKey);
            $mailTab = $status === 'rejected' ? 'bogo_deny' : 'bogo_approve';

            if ($mailStatus?->mail_status == 'active' && config('mail.status')
                && Helpers::get_mail_status($mailTab.'_mail_status_restaurant') == '1'
                && $restaurant->vendor) {
                Mail::to($restaurant->vendor->getRawOriginal('email'))
                    ->send(new \App\Mail\VendorBogoRequestMail(
                        $restaurant->name,
                        $status === 'rejected' ? 'denied' : 'approved'
                    ));
            }
        } catch (\Exception $e) {
            info(["line___{$e->getLine()}", $e->getMessage()]);
        }
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

            $key = 'restaurant_bogo_invitation';
            $pushStatus = Helpers::getRestaurantNotificationStatusData($restaurant->id, $key);

            if ($pushStatus?->push_notification_status != 'active') {
                return;
            }

            $message = Helpers::getPushNotificationMessage(
                status: $key,
                userType: 'restaurant',
                lang: $restaurant->vendor?->current_language_key,
                restaurantName: $restaurant->name
            ) ?: translate('messages.The_Admin_has_invited_you_to_join_a_BOGO_Review_and_approve_the_request_to_participate');

            $data = Helpers::makeDataForPushNotification(
                title: translate('messages.BOGO_Request'),
                message: $message,
                orderId: '',
                type: 'bogo_offer',
                orderStatus: '',
                dataId: $promotionId
            );

            Helpers::send_push_notif_to_device($restaurant->vendor->firebase_token, $data);
            Helpers::insertDataOnNotificationTable($data, 'vendor', $restaurant->vendor_id);
        } catch (\Exception $e) {
            info(['bogo_offer_invitation_notify' => $e->getMessage()]);
        }
    }

    /**
     * Announces a newly published promotion to the restaurants that can act on it.
     *
     * Sent to an FCM topic rather than per device: a broadcast to every restaurant would
     * otherwise be one HTTP call each, inside the admin's save request.
     */
    /**
     * Tells a restaurant an offer it had joined has been withdrawn.
     *
     * Sent per device rather than to a topic: the only restaurants this concerns are the ones
     * on the offer, and a topic reaches every restaurant in the zone.
     */
    private function notifyRestaurantOfOfferWithdrawn(int $restaurantId, $promotionId): void
    {
        try {
            $restaurant = Restaurant::with('vendor')->find($restaurantId);

            if (! $restaurant || ! isset($restaurant->vendor->firebase_token)) {
                return;
            }

            $key = 'restaurant_bogo_offer_withdrawn';
            $pushStatus = Helpers::getRestaurantNotificationStatusData($restaurant->id, $key);

            if ($pushStatus?->push_notification_status != 'active') {
                return;
            }

            $message = Helpers::getPushNotificationMessage(
                status: $key,
                userType: 'restaurant',
                lang: $restaurant->vendor?->current_language_key,
                restaurantName: $restaurant->name
            ) ?: translate('messages.A_BOGO_offer_you_had_joined_has_been_withdrawn_by_the_admin');

            $data = Helpers::makeDataForPushNotification(
                title: translate('messages.BOGO_Offer_Withdrawn'),
                message: $message,
                orderId: '',
                type: 'bogo_offer',
                orderStatus: '',
                dataId: $promotionId
            );

            Helpers::send_push_notif_to_device($restaurant->vendor->firebase_token, $data);
            Helpers::insertDataOnNotificationTable($data, 'vendor', $restaurant->vendor_id);
        } catch (\Exception $e) {
            info(['bogo_offer_withdrawn_notify' => $e->getMessage()]);
        }
    }

    private function notifyVendorsOfNewOffer($promotionId, $topic): void
    {
        try {
            $data = Helpers::makeDataForPushNotification(
                title: translate('messages.New_BOGO_Available'),
                message: translate('messages.A_new_BOGO_is_available_Enroll_now_to_boost_your_sales'),
                orderId: '',
                type: 'bogo_offer',
                orderStatus: '',
                dataId: $promotionId
            );

            Helpers::send_push_notif_to_topic($data, $topic, 'bogo_offer');
        } catch (\Exception $e) {
            info(['bogo_offer_broadcast' => $e->getMessage()]);
        }
    }
}
