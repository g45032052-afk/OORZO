<?php

namespace App\Http\Controllers\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Traits\HandlesHappyHourEnrollment;
use App\Models\HappyHour;
use App\Models\HappyHourRestaurant;
use App\Models\Restaurant;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Vendor side of Happy Hour. There is no food selection here - joining is one click - so the
 * whole surface is: see the offers for my zone, join one, respond to one the admin assigned
 * me, cancel my own request, or leave. Nothing to rework means no resubmit: a denied
 * restaurant cancels and joins again.
 *
 * Two directions of request share the same pivot row and are told apart by requested_by:
 *   restaurant -> pending, the admin approves      (vendor sees "Pending")
 *   admin      -> pending, the restaurant approves (vendor sees "Admin Requested")
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
        $restaurantId = Helpers::get_restaurant_id();
        $restaurant = Restaurant::findOrFail($restaurantId);

        // The restaurant has seen the admin's invitation, so the dashboard prompt stops -
        // the same clearing the BOGO list does, and the same the order list does for the
        // new-order popup.
        HappyHourRestaurant::where('restaurant_id', $restaurantId)
            ->where('checked', 0)->update(['checked' => 1]);
        // Trimmed and emptied out first: a padded search splits into empty pieces, each of
        // which becomes LIKE '%%' and matches every row - so "  term  " used to answer with
        // the whole list instead of the one match.
        $key = array_values(array_filter(explode(' ', trim($request['search'] ?? ''))));

        $happyHours = HappyHour::with([
                'zone',
                'enrollments' => fn ($q) => $q->where('restaurant_id', $restaurantId),
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
            })
            ->latest()
            ->paginate(config('default_pagination', 25))
            ->withQueryString();

        // "Approved" is the admin's answer to the restaurant, not proof that customers are
        // being given the discount: a zero rate, a switched-off happy hour, a store moved to
        // another zone or one out of subscription orders all stop it reaching anybody. Read
        // here so the row can carry the warning, the same way the BOGO list does. A window
        // that is simply closed right now is not a problem and reports nothing.
        $happyHours->getCollection()->transform(function ($happyHour) use ($restaurant) {
            $enrollment = $happyHour->enrollments->first();

            $happyHour->hidden_reasons = $enrollment && $enrollment->status === 'approved'
                ? $this->happyHourVisibilityProblems($enrollment, $happyHour, $restaurant)
                : [];

            // Resolved here rather than in the blade, and from the same helpers the app
            // reads, so the panel and the restaurant app cannot word one enrolment two ways.
            $happyHour->state_label = $this->enrollmentStateLabel($enrollment);
            $happyHour->rejection_note = $this->enrollmentRejectionNote($enrollment, 'happy_hour');

            return $happyHour;
        });

        return view('vendor-views.happy-hour.list', compact('happyHours', 'restaurant'));
    }

    public function detail($id)
    {
        $restaurantId = Helpers::get_restaurant_id();
        $restaurant = Restaurant::findOrFail($restaurantId);

        $happyHour = HappyHour::with('zone')
            ->where('zone_id', $restaurant->zone_id)
            ->findOrFail($id);

        $enrollment = HappyHourRestaurant::where('happy_hour_id', $happyHour->id)
            ->where('restaurant_id', $restaurantId)
            ->first();

        return view('vendor-views.happy-hour.partials._detail_drawer', compact('happyHour', 'enrollment'));
    }

    /**
     * Restaurant-initiated join. Lands pending for the admin to approve.
     */
    public function join($id)
    {
        $restaurantId = Helpers::get_restaurant_id();
        $happyHour = $this->guardJoinable($id, $restaurantId);

        $existing = HappyHourRestaurant::where('happy_hour_id', $happyHour->id)
            ->where('restaurant_id', $restaurantId)
            ->first();

        // Joining is only ever a first join. A denied restaurant cancels its enrolment first,
        // which deletes the row - reviving a rejected row in place would hide the refusal
        // from the admin, who would see a fresh request that had already been turned down.
        if ($existing) {
            Toastr::warning(match ($this->enrollmentState($existing)) {
                'pending' => translate('messages.you_have_already_requested_to_join_this_happy_hour'),
                'approved' => translate('messages.you_have_already_joined_this_happy_hour'),
                'admin_requested' => translate('messages.respond_to_the_admin_request_for_this_happy_hour'),
                default => translate('messages.cancel_the_denied_request_before_joining_again'),
            });

            return back();
        }

        // A restaurant may hold as many happy hours as it likes as long as their windows do
        // not touch: the discount is store-wide, so two running at once would leave the
        // basket's price decided by whichever enrolment was read first.
        if ($blocked = $this->happyHourOverlapBlockedReason($happyHour, $restaurantId)) {
            Toastr::warning($blocked['message']);

            return back();
        }

        $enrollment = new HappyHourRestaurant([
            'happy_hour_id' => $happyHour->id,
            'restaurant_id' => $restaurantId,
        ]);

        $enrollment->status = 'pending';
        $enrollment->requested_by = 'restaurant';
        $enrollment->rejection_reason = null;
        $enrollment->rejected_by = null;
        $enrollment->joined_at = now();
        $enrollment->save();

        $this->notifyAdminOfHappyHourJoinRequest($restaurantId);

        Toastr::success(translate('messages.your_request_has_been_sent_for_approval'));

        return back();
    }

    /**
     * The restaurant's answer to an offer the admin assigned it. Only an admin-initiated
     * pending row can be answered - a restaurant cannot approve its own request.
     */
    public function respond(Request $request, $id, $status)
    {
        if (! in_array($status, ['approved', 'rejected'], true)) {
            abort(404);
        }

        $restaurantId = Helpers::get_restaurant_id();

        $enrollment = HappyHourRestaurant::where('happy_hour_id', $id)
            ->where('restaurant_id', $restaurantId)
            ->firstOrFail();

        if ($enrollment->status !== 'pending' || $enrollment->requested_by !== 'admin') {
            Toastr::error(translate('messages.there_is_no_pending_admin_request_for_this_happy_hour'));

            return back();
        }

        if ($status === 'rejected') {
            // rejection_reason lands in a TEXT column, so without a bound a restaurant can
            // store 64KB of anything and the admin's screens then have to render it. Same
            // limit the API sibling of this endpoint already applies, so the two ways of
            // answering the same request cannot disagree.
            $validator = Validator::make($request->all(), [
                'rejection_reason' => 'nullable|string|max:255',
            ], [
                'rejection_reason.max' => translate('messages.rejection_reason_may_not_be_longer_than_255_characters'),
            ]);

            if ($validator->fails()) {
                Toastr::error($validator->errors()->first());

                return back();
            }

            $this->markRejected($enrollment, 'restaurant', $request->rejection_reason);
            $this->notifyAdminOfHappyHourDenial($restaurantId);
        } else {
            $enrollment->status = 'approved';
            $enrollment->rejection_reason = null;
            $enrollment->rejected_by = null;
            $enrollment->save();
            $this->notifyAdminOfHappyHourAcceptance($restaurantId);
        }

        Toastr::success($status === 'approved'
            ? translate('messages.you_have_joined_the_happy_hour')
            : translate('messages.you_have_declined_the_happy_hour'));

        return back();
    }

    /**
     * Backs both Cancel Request and Leave: withdrawing a request that never went live and
     * walking away from a running one are the same delete, and differ only in what the
     * restaurant is told. Leaving is allowed even while the window is live - the same rule
     * the admin side follows for removing a restaurant.
     */
    public function leave($id)
    {
        $restaurantId = Helpers::get_restaurant_id();

        $enrollment = HappyHourRestaurant::where('happy_hour_id', $id)
            ->where('restaurant_id', $restaurantId)
            ->firstOrFail();

        if ($blocked = $this->restaurantDeleteBlockedReason($enrollment)) {
            Toastr::error($blocked);

            return back();
        }

        $wasApproved = $this->enrollmentState($enrollment) === 'approved';

        $enrollment->delete();

        Toastr::success($wasApproved
            ? translate('messages.you_have_left_the_happy_hour')
            : translate('messages.your_request_has_been_cancelled'));

        return back();
    }

    /**
     * A happy hour reaches a restaurant only through its zone, so an id outside the zone is
     * not found. The joinable rule itself lives in HandlesHappyHourEnrollment so the vendor
     * app applies the same one.
     */
    private function guardJoinable($id, int $restaurantId): HappyHour
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $happyHour = HappyHour::where('zone_id', $restaurant->zone_id)->findOrFail($id);

        if ($blocked = $this->happyHourJoinBlockedReason($happyHour)) {
            abort(403, $blocked['message']);
        }

        return $happyHour;
    }
}
