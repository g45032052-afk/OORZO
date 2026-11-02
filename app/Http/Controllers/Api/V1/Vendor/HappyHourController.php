<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\HappyHour;
use App\Models\HappyHourRestaurant;
use App\Models\Restaurant;
use App\Traits\HandlesHappyHourEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Happy Hour for the restaurant app - the same surface the vendor panel has.
 *
 * There is no food selection, so joining is one call: see the happy hours for my zone, join
 * one, answer one the admin assigned me, cancel my own request, or leave. There is no
 * resubmit either - nothing to rework - so a denied restaurant cancels and joins again.
 *
 * The state machine, the joinable rule and the notifications all come from
 * HandlesHappyHourEnrollment, shared with the panel.
 */
class HappyHourController extends Controller
{
    use HandlesHappyHourEnrollment;

    /**
     * Switched-on happy hours in this restaurant's zone that have not ended. A permanent
     * weekly rule has no end date at all, so it never expires out of the list.
     */
    public function list(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $limit = $request['limit'] ?? 25;
        $offset = $request['offset'] ?? 1;
        $key = explode(' ', $request['search'] ?? '');

        $query = HappyHour::with([
                'zone',
                'enrollments' => fn ($q) => $q->where('restaurant_id', $restaurant->id),
            ])
            ->where('status', 1)
            ->where('zone_id', $restaurant->zone_id)
            // Anything with a day left to run. Not "end_date is null or in the future": a
            // custom schedule has no end_date at all, so that kept every expired one on the
            // list. An extended or re-dated schedule comes straight back, since this is read
            // per request and nothing is recorded.
            ->notEnded()
            ->when($request['search'], function ($query) use ($key) {
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('title', 'like', "%{$value}%");
                    }
                });
            });

        // Counted before the type filter, so the tabs keep their totals while one is open.
        $counts = $this->stateCounts((clone $query)->get(), $restaurant);

        $happyHours = (clone $query)
            ->when($request['type'] && $request['type'] !== 'all', fn ($q) => $this->filterByState($q, $request['type'], $restaurant->id))
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        $data = [
            'total_size' => $happyHours->total(),
            'limit' => (int) $limit,
            'offset' => (int) $offset,
        ] + $counts + [
            'happy_hours' => $happyHours->getCollection()->map(fn ($h) => $this->formatHappyHour($h, $restaurant))->values(),
        ];

        return response()->json($data, 200);
    }

    /**
     * One happy hour with this restaurant's enrolment and the full schedule, so the app can
     * show when the window actually opens.
     */
    public function details(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);

        $happyHour = HappyHour::with('zone')
            ->where('zone_id', $restaurant->zone_id)
            ->where(fn ($q) => $q->where('id', $id)->orWhere('slug', $id))
            ->first();

        if (! $happyHour) {
            return $this->notFound();
        }

        return response()->json($this->formatHappyHour($happyHour, $restaurant, true), 200);
    }

    /**
     * Restaurant-initiated join. Lands pending for the admin to approve.
     */
    public function join(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);

        // Out of zone is not found rather than refused - the happy hour does not reach this
        // restaurant at all.
        $happyHour = HappyHour::where('zone_id', $restaurant->zone_id)->find($id);

        if (! $happyHour) {
            return $this->notFound();
        }

        if ($blocked = $this->happyHourJoinBlockedReason($happyHour)) {
            return $this->errorResponse($blocked['code'], $blocked['message']);
        }

        $existing = HappyHourRestaurant::where('happy_hour_id', $happyHour->id)
            ->where('restaurant_id', $restaurant->id)
            ->first();

        // Joining is only ever a first join. A denied restaurant cancels its enrolment first,
        // which deletes the row - reviving a rejected row in place would hide the refusal
        // from the admin, who would see a fresh request that had already been turned down.
        if ($existing) {
            return $this->errorResponse('happy_hour', match ($this->enrollmentState($existing)) {
                'pending' => translate('messages.you_have_already_requested_to_join_this_happy_hour'),
                'approved' => translate('messages.you_have_already_joined_this_happy_hour'),
                'admin_requested' => translate('messages.respond_to_the_admin_request_for_this_happy_hour'),
                default => translate('messages.cancel_the_denied_request_before_joining_again'),
            });
        }

        // A restaurant may hold as many happy hours as it likes as long as their windows do
        // not touch: the discount is store-wide, so two running at once would leave the
        // basket's price decided by whichever enrolment was read first.
        if ($blocked = $this->happyHourOverlapBlockedReason($happyHour, (int) $restaurant->id)) {
            return $this->errorResponse($blocked['code'], $blocked['message']);
        }

        $enrollment = new HappyHourRestaurant([
            'happy_hour_id' => $happyHour->id,
            'restaurant_id' => $restaurant->id,
        ]);

        $enrollment->status = 'pending';
        $enrollment->requested_by = 'restaurant';
        $enrollment->rejection_reason = null;
        $enrollment->rejected_by = null;
        $enrollment->joined_at = now();
        $enrollment->save();

        $this->notifyAdminOfHappyHourJoinRequest($restaurant->id);

        return response()->json([
            'message' => translate('messages.your_request_has_been_sent_for_approval'),
            'enrollment_state' => 'pending',
        ], 200);
    }

    /**
     * The restaurant's answer to a happy hour the admin assigned it. Only an admin-initiated
     * pending row can be answered.
     */
    public function respond(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'nullable|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $restaurant = $this->restaurant($request);

        $enrollment = HappyHourRestaurant::where('happy_hour_id', $id)
            ->where('restaurant_id', $restaurant->id)
            ->first();

        if (! $enrollment) {
            return $this->notFound();
        }

        if ($blocked = $this->respondBlockedReason($enrollment, translate('messages.there_is_no_pending_admin_request_for_this_happy_hour'))) {
            return $this->errorResponse('status', $blocked);
        }

        if ($request['status'] === 'rejected') {
            $this->markRejected($enrollment, 'restaurant', $request['rejection_reason']);
            $this->notifyAdminOfHappyHourDenial($restaurant->id);
        } else {
            $enrollment->status = 'approved';
            $enrollment->rejection_reason = null;
            $enrollment->rejected_by = null;
            $enrollment->save();
            $this->notifyAdminOfHappyHourAcceptance($restaurant->id);
        }

        return response()->json([
            'message' => $request['status'] === 'approved'
                ? translate('messages.you_have_joined_the_happy_hour')
                : translate('messages.you_have_declined_the_happy_hour'),
        ], 200);
    }

    /**
     * Backs both can_cancel and can_leave: withdrawing a request that never went live and
     * walking away from a running one are the same delete, and differ only in what the
     * restaurant is told. Leaving is allowed even while the window is live - the same rule
     * the admin side follows for removing a restaurant. An unanswered admin request is
     * neither - it is responded to.
     */
    public function leave(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);

        $enrollment = HappyHourRestaurant::where('happy_hour_id', $id)
            ->where('restaurant_id', $restaurant->id)
            ->first();

        if (! $enrollment) {
            return $this->notFound();
        }

        if ($blocked = $this->restaurantDeleteBlockedReason($enrollment)) {
            return $this->errorResponse('status', $blocked);
        }

        $wasApproved = $this->enrollmentState($enrollment) === 'approved';

        $enrollment->delete();

        return response()->json([
            'message' => $wasApproved
                ? translate('messages.you_have_left_the_happy_hour')
                : translate('messages.your_request_has_been_cancelled'),
        ], 200);
    }

    /**
     * One happy hour as the app needs it. A happy hour in this restaurant's zone is always
     * joinable unless it is off or ended, so is_eligible follows the same guard the join
     * call applies rather than a separate rule.
     */
    private function formatHappyHour(HappyHour $happyHour, Restaurant $restaurant, bool $withSchedule = false): array
    {
        $enrollment = $happyHour->relationLoaded('enrollments')
            ? $happyHour->enrollments->firstWhere('restaurant_id', $restaurant->id)
            : HappyHourRestaurant::where('happy_hour_id', $happyHour->id)->where('restaurant_id', $restaurant->id)->first();

        $blocked = $this->happyHourJoinBlockedReason($happyHour);
        $isEligible = $blocked === null;

        // Whether customers are actually being given the discount, which "approved" does not
        // answer: a switched-off happy hour, a zero rate, a store moved to another zone or
        // one out of subscription orders all stop it reaching anybody.
        $visibility = $this->happyHourCustomerVisibility($enrollment, $happyHour, $restaurant);

        $data = [
            'id' => $happyHour->id,
            'slug' => $happyHour->slug,
            'title' => $happyHour->title,
            'short_description' => $happyHour->short_description,
            'cover_image_full_url' => $happyHour->cover_image_full_url,
            'icon_full_url' => $happyHour->icon_full_url,
            'zone_id' => $happyHour->zone_id,
            'zone_name' => $happyHour->zone->name ?? null,
            'discount' => (float) $happyHour->discount,
            'min_order_amount' => (float) $happyHour->min_order_amount,
            'is_permanent' => (bool) $happyHour->is_permanent,
            'duration_type' => $happyHour->duration_type,
            'weekly_days' => $happyHour->weekly_days ?? [],
            'start_date' => $happyHour->start_date?->format('Y-m-d'),
            'end_date' => $happyHour->end_date?->format('Y-m-d'),
            'start_time' => $happyHour->start_time,
            'end_time' => $happyHour->end_time,
            // Whether the window is open at this moment, which is a schedule question and
            // cannot be answered from the columns alone.
            'is_running_now' => $happyHour->isRunningNow(),
            'status' => $happyHour->status,
            'is_eligible' => $isEligible,
            'ineligible_reason' => $blocked['message'] ?? null,
            'enrollment_state' => $this->enrollmentState($enrollment),
            // The same phrase the vendor panel puts in its status column. A happy hour that
            // has ended never reaches this list, so there is no expired label to report.
            'enrollment_state_label' => $this->enrollmentStateLabel($enrollment),
            // Who said no and why, worded as the panel words it for a happy hour.
            'rejection_note' => $this->enrollmentRejectionNote($enrollment, 'happy_hour'),
            // What a customer sees, as against what the admin answered. One of running,
            // scheduled, ended, not_visible, or not_applicable while the enrolment is not
            // approved - the same five words the BOGO endpoints answer with, so the app
            // renders one badge for both. An approved happy hour between its windows is
            // scheduled rather than a problem: an hour a day is what it is for.
            'visibility_status' => $visibility['status'],
            'visibility_label' => $visibility['label'],
            'visibility_reasons' => $visibility['reasons'],
            'enrollment' => $this->formatEnrollment($enrollment),
            // No resubmit for Happy Hour: there is no selection to rework.
            'actions' => $this->enrollmentActions($enrollment, $isEligible, supportsResubmit: false),
        ];

        if ($withSchedule) {
            $data['dates'] = $happyHour->dates()
                ->whereDate('applicable_date', '>=', now()->toDateString())
                ->orderBy('applicable_date')
                ->get()
                ->map(fn ($d) => [
                    'applicable_date' => $d->applicable_date?->format('Y-m-d'),
                    'start_time' => $d->start_time,
                    'end_time' => $d->end_time,
                    'status' => (bool) $d->status,
                ])->values();
        }

        return $data;
    }

    private function formatEnrollment(?HappyHourRestaurant $enrollment): ?array
    {
        if (! $enrollment) {
            return null;
        }

        return [
            'id' => $enrollment->id,
            'status' => $enrollment->status,
            'requested_by' => $enrollment->requested_by,
            'rejection_reason' => $enrollment->rejection_reason,
            // Who said no, so the app can word it as "Denied by admin" or "You declined".
            'rejected_by' => $enrollment->rejected_by,
            'joined_at' => $enrollment->joined_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function stateCounts($happyHours, Restaurant $restaurant): array
    {
        $counts = ['all' => $happyHours->count(), 'not_joined' => 0, 'pending' => 0,
            'admin_requested' => 0, 'approved' => 0, 'rejected' => 0];

        foreach ($happyHours as $happyHour) {
            $enrollment = $happyHour->enrollments->firstWhere('restaurant_id', $restaurant->id);
            $counts[$this->enrollmentState($enrollment)]++;
        }

        return $counts;
    }

    private function filterByState($query, string $state, int $restaurantId)
    {
        $enrolled = fn ($q) => $q->where('restaurant_id', $restaurantId);

        return match ($state) {
            'not_joined' => $query->whereDoesntHave('enrollments', $enrolled),
            'pending' => $query->whereHas('enrollments', fn ($q) => $enrolled($q)->where('status', 'pending')->where('requested_by', 'restaurant')),
            'admin_requested' => $query->whereHas('enrollments', fn ($q) => $enrolled($q)->where('status', 'pending')->where('requested_by', 'admin')),
            'approved' => $query->whereHas('enrollments', fn ($q) => $enrolled($q)->where('status', 'approved')),
            'rejected' => $query->whereHas('enrollments', fn ($q) => $enrolled($q)->where('status', 'rejected')),
            default => $query,
        };
    }

    private function errorResponse(string $code, string $message)
    {
        return response()->json(['errors' => [['code' => $code, 'message' => $message]]], 403);
    }

    private function restaurant(Request $request): Restaurant
    {
        return $request['vendor']->restaurants[0];
    }

    private function notFound()
    {
        return response()->json([
            'errors' => [['code' => 'happy_hour', 'message' => translate('messages.not_found')]],
        ], 404);
    }
}
