<?php

namespace App\Http\Controllers\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BogoOffer;
use App\Models\BogoOfferRestaurant;
use App\Models\Restaurant;
use App\Traits\HandlesBogoEnrollment;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * Vendor side of BOGO. A restaurant never creates or edits an offer - it joins one the admin
 * published, answers one the admin assigned it, reworks its own selection after a denial or
 * to change what is running, cancels its request, or leaves.
 *
 * Every enrolment rule lives in HandlesBogoEnrollment, shared with the admin panel, so the
 * two sides cannot drift on what a valid buy/get selection is.
 */
class BogoOfferController extends Controller
{
    use HandlesBogoEnrollment;

    /**
     * Offers the restaurant may see: switched on and either still running or one this
     * restaurant is on. An expired offer nobody here joined drops off; one the restaurant did
     * join stays, marked Ended and read-only, because dropping it took away the only record of
     * what ran - and the details drawer was built to show that ended state but had no way in.
     * Offers whose order types the restaurant cannot serve stay visible but are not joinable.
     */
    public function list(Request $request)
    {
        $restaurantId = Helpers::get_restaurant_id();
        $restaurant = Restaurant::with('restaurant_config')->findOrFail($restaurantId);

        // Opening this screen is the restaurant seeing the admin's invitation, so the
        // dashboard prompt has said what it had to say and stops. Exactly how the order list
        // clears orders.checked for the new-order popup. A request the admin sends after this
        // lands unchecked again and is announced again.
        BogoOfferRestaurant::where('restaurant_id', $restaurantId)
            ->where('checked', 0)->update(['checked' => 1]);
        // Trimmed and emptied out first: a padded search splits into empty pieces, each of
        // which becomes LIKE '%%' and matches every row - so "  term  " used to answer with
        // the whole list instead of the one match.
        $key = array_values(array_filter(explode(' ', trim($request['search'] ?? ''))));

        $offers = BogoOffer::with(['enrollments' => fn ($q) => $q->where('restaurant_id', $restaurantId), 'enrollments.items'])
            ->where('status', 1)
            ->where(function ($q) use ($restaurantId) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now())
                    ->orWhereHas('enrollments', fn ($e) => $e->where('restaurant_id', $restaurantId));
            })
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

        // "Approved" is the admin's answer to the restaurant, not the customer's view of the
        // bundle: a sold-out item, a switched-off category, a closed store or a filled usage
        // cap all hide it while that lasts. The details drawer already said so; the row said
        // only "Approved", so the vendor had to open every offer in turn to find the one that
        // was not running. Asked for the whole page at once, then handed to the row.
        $ownApproved = $offers->getCollection()
            ->map(function ($offer) use ($restaurant) {
                $enrollment = $offer->enrollments->first();

                if (! $enrollment || $enrollment->status !== 'approved') {
                    return null;
                }

                // Both sides are already in hand - the offer is the row's own and there is
                // one restaurant on this screen - so neither is fetched again per row.
                $enrollment->setRelation('bogo_offer', $offer);
                $enrollment->setRelation('restaurant', $restaurant);

                return $enrollment;
            })
            ->filter();

        $hiddenReasons = $this->customerVisibilityProblemsFor($ownApproved);

        // Resolved here rather than in the blade so the view holds no query or controller.
        $offers->getCollection()->transform(function ($offer) use ($restaurant, $hiddenReasons) {
            $offer->is_eligible = $this->servesAnyOrderType($restaurant, $offer->order_types ?? []);
            // Reworking a selection goes back through join's own guard, which refuses an offer
            // that has ended - so the button has to disappear with it rather than sit there
            // answering 403.
            $offer->has_ended = (bool) ($offer->end_date && $offer->end_date->isPast());
            $offer->hidden_reasons = $hiddenReasons[$offer->enrollments->first()?->id] ?? [];
            // Why the row's actions are inert, from the same rule the endpoints refuse with -
            // a denial that may only be reworked, or an offer that has finished running.
            $offer->locked_reason = $this->bogoEnrollmentLockedReason($offer->enrollments->first(), $offer);
            // Composed from the helpers the restaurant app reads too, so one enrolment is
            // never worded two ways.
            $offer->state_label = $this->enrollmentStateLabel($offer->enrollments->first(), $offer->has_ended);
            $offer->rejection_note = $this->enrollmentRejectionNote($offer->enrollments->first());

            return $offer;
        });

        return view('vendor-views.bogo-offer.list', compact('offers', 'restaurant'));
    }

    /**
     * Drawer body for one offer, showing the restaurant's own enrolment if it has one.
     */
    public function detail($id)
    {
        $restaurantId = Helpers::get_restaurant_id();
        $restaurant = Restaurant::with('restaurant_config')->findOrFail($restaurantId);
        $offer = BogoOffer::findOrFail($id);

        // The drawer's Join button is gated on the same rule as the list row.
        $offer->is_eligible = $this->servesAnyOrderType($restaurant, $offer->order_types ?? []);
        $offer->has_ended = (bool) ($offer->end_date && $offer->end_date->isPast());
        $offer->has_started = ! $offer->start_date || ! $offer->start_date->isFuture();

        $enrollment = BogoOfferRestaurant::with(['items', 'buyItems', 'getItems'])
            ->where('bogo_offer_id', $offer->id)
            ->where('restaurant_id', $restaurantId)
            ->first();

        // "Approved" says the admin agreed; it does not say a customer can see the bundle
        // today. A switched-off or sold-out item hides it while that lasts, and so do a closed
        // store, an exhausted usage cap and an order type the store does not offer - all of
        // which used to leave the vendor to work it out from the app.
        $hiddenReasons = [];

        if ($enrollment && $enrollment->status === 'approved') {
            $enrollment->setRelation('restaurant', $restaurant);
            $hiddenReasons = $this->customerVisibilityProblems($enrollment, $offer);
        }

        // Same rule the row is locked by, so the drawer's footer and the row's buttons cannot
        // disagree about what is still allowed.
        $offer->locked_reason = $this->bogoEnrollmentLockedReason($enrollment, $offer);

        $addOnIds = $enrollment
            ? $enrollment->items->flatMap(fn ($item) => $item->add_on_ids ?? [])->unique()->all()
            : [];

        $addOnNames = \App\Models\AddOn::whereIn('id', $addOnIds)->pluck('name', 'id');
        // Prices too: an add-on shown by name alone said nothing about what it adds.
        $addOnPrices = \App\Models\AddOn::whereIn('id', $addOnIds)->pluck('price', 'id');

        return view('vendor-views.bogo-offer.partials._detail_drawer', compact('offer', 'enrollment', 'addOnNames', 'addOnPrices', 'hiddenReasons'));
    }

    /**
     * Own menu only - the restaurant is taken from the session, never from the request, so
     * a vendor cannot enumerate another restaurant's foods.
     */
    public function restaurantFoods(Request $request)
    {
        return response()->json(
            $this->bogoRestaurantFoods(Helpers::get_restaurant_id(), $request['search']),
            200
        );
    }

    public function join(Request $request, $id)
    {
        $restaurantId = Helpers::get_restaurant_id();
        $offer = BogoOffer::findOrFail($id);

        if ($blocked = $this->guardJoinable($offer, $restaurantId)) {
            return $blocked;
        }

        if (BogoOfferRestaurant::where('bogo_offer_id', $offer->id)->where('restaurant_id', $restaurantId)->exists()) {
            return $this->errorResponse('restaurant', translate('messages.you_have_already_joined_this_offer'));
        }

        return $this->writeEnrollment($request, $offer, $restaurantId, null);
    }

    /**
     * The restaurant's answer to an offer the admin assigned it, with the foods the admin
     * chose. Only an admin-initiated pending row can be answered - a restaurant cannot
     * approve a request it raised itself.
     */
    public function respond(Request $request, $id, $status)
    {
        if (! in_array($status, ['approved', 'rejected'], true)) {
            abort(404);
        }

        $restaurantId = Helpers::get_restaurant_id();

        $enrollment = BogoOfferRestaurant::where('bogo_offer_id', $id)
            ->where('restaurant_id', $restaurantId)
            ->firstOrFail();

        if ($blocked = $this->bogoRespondBlockedReason($enrollment, BogoOffer::find($id),
            translate('messages.there_is_no_pending_admin_request_for_this_offer'))) {
            Toastr::error($blocked);

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
            $this->notifyAdminOfBogoDenial($restaurantId);
        } else {
            // The admin picked these foods when it sent the request; the restaurant may have
            // changed its menu since. Accepting into a selection it can no longer serve is the
            // same broken bundle whichever side clicks approve.
            $problems = $this->enrollmentIntegrityProblems($enrollment);

            if (! empty($problems['blocking'])) {
                Toastr::error(translate('messages.this_selection_can_no_longer_be_built').' '.implode(' | ', $problems['blocking']));

                return back();
            }

            $enrollment->status = 'approved';
            $enrollment->rejection_reason = null;
            $enrollment->rejected_by = null;
            $enrollment->save();
            $this->notifyAdminOfBogoAcceptance($restaurantId);
        }

        Toastr::success($status === 'approved'
            ? translate('messages.you_have_joined_the_bogo_offer')
            : translate('messages.you_have_declined_the_bogo_offer'));

        return back();
    }

    /**
     * Reworked selection - after a denial, or to change what is already running. Goes back to
     * pending as a restaurant request, so the admin is still the gate on whatever finally
     * goes live; an approved offer therefore stops applying until it is approved again.
     *
     * An admin request cannot be edited here. The restaurant answers it as it stands, and
     * reworks it only after saying no.
     */
    public function resubmit(Request $request, $id)
    {
        $restaurantId = Helpers::get_restaurant_id();
        $offer = BogoOffer::findOrFail($id);

        $enrollment = BogoOfferRestaurant::where('bogo_offer_id', $offer->id)
            ->where('restaurant_id', $restaurantId)
            ->firstOrFail();

        if ($blocked = $this->restaurantResubmitBlockedReason($enrollment)) {
            return $this->errorResponse('status', $blocked);
        }

        if ($blocked = $this->guardJoinable($offer, $restaurantId)) {
            return $blocked;
        }

        return $this->writeEnrollment($request, $offer, $restaurantId, $enrollment);
    }

    /**
     * Backs both Cancel Request and Leave: withdrawing a request that never went live and
     * walking away from a running one are the same delete, and differ only in what the
     * restaurant is told. An unanswered admin request is neither - it is responded to.
     */
    public function leave($id)
    {
        $restaurantId = Helpers::get_restaurant_id();

        $enrollment = BogoOfferRestaurant::where('bogo_offer_id', $id)
            ->where('restaurant_id', $restaurantId)
            ->firstOrFail();

        if ($blocked = $this->bogoDeleteBlockedReason($enrollment, BogoOffer::find($id))) {
            Toastr::error($blocked);

            return back();
        }

        $wasApproved = $this->enrollmentState($enrollment) === 'approved';

        DB::transaction(function () use ($enrollment) {
            $enrollment->items()->delete();
            $enrollment->delete();
        });

        Toastr::success($wasApproved
            ? translate('messages.you_have_left_the_bogo_offer')
            : translate('messages.your_request_has_been_cancelled'));

        return back();
    }

    /**
     * Shared by join and resubmit: validate, freeze the snapshot, land on pending.
     */
    private function writeEnrollment(Request $request, BogoOffer $offer, int $restaurantId, ?BogoOfferRestaurant $existing)
    {
        $buyItems = $this->decodeArray($request->buy_items) ?? [];
        $getItems = $this->decodeArray($request->get_items) ?? [];

        $errors = $this->validateEnrollmentItems($offer, $buyItems, $getItems, $restaurantId);

        if ($errors) {
            return response()->json(['errors' => $errors], 403);
        }

        $signature = $this->buildCombinationSignature($buyItems, $getItems);

        if ($this->combinationTaken($restaurantId, $signature, $existing?->id)) {
            return $this->errorResponse('combination', translate('messages.this_food_combination_is_already_used_in_another_running_offer'));
        }

        DB::transaction(function () use ($offer, $restaurantId, $existing, $buyItems, $getItems, $signature) {
            $enrollment = $existing ?: new BogoOfferRestaurant([
                'bogo_offer_id' => $offer->id,
                'restaurant_id' => $restaurantId,
            ]);

            // Always the restaurant: this selection is the restaurant's, so it waits on the
            // admin. An approved row drops back to pending here and stops applying until the
            // admin approves the new selection.
            $enrollment->requested_by = 'restaurant';
            $enrollment->status = 'pending';
            $enrollment->rejection_reason = null;
            $enrollment->rejected_by = null;
            $enrollment->joined_at = $enrollment->joined_at ?: now();
            $enrollment->combination_signature = $signature;
            $enrollment->save();

            $enrollment->bundle_price = $this->syncEnrollmentItems($enrollment, $buyItems, $getItems);
            $enrollment->save();
        });

        $this->notifyAdminOfBogoJoinRequest($offer, $restaurantId);

        return response()->json([], 200);
    }

    /**
     * Offer must be running and the restaurant must serve at least one of its order types.
     * The rule itself lives in HandlesBogoEnrollment so the vendor app applies the same one.
     */
    private function guardJoinable(BogoOffer $offer, int $restaurantId)
    {
        $blocked = $this->bogoJoinBlockedReason($offer, $restaurantId);

        return $blocked ? $this->errorResponse($blocked['code'], $blocked['message']) : null;
    }
}
