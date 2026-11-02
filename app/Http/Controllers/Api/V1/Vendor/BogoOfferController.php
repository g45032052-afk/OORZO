<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\AddOn;
use App\Models\BogoOffer;
use App\Models\BogoOfferRestaurant;
use App\Models\Restaurant;
use App\Traits\HandlesBogoEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * BOGO for the restaurant app - the same surface the vendor panel has.
 *
 * A restaurant never creates or edits an offer. It joins one the admin published with its
 * own buy/get selection, answers one the admin assigned it, reworks its own selection - after
 * a denial or to change what is running - cancels its request, or leaves.
 *
 * Every rule - what a valid selection is, when an offer is joinable, what a restaurant may
 * do next, who gets notified - comes from HandlesBogoEnrollment, shared with both panels, so
 * the app and the panel cannot disagree about the same enrolment.
 */
class BogoOfferController extends Controller
{
    use HandlesBogoEnrollment;

    /**
     * Offers this restaurant may see: switched on, and either still running or one this
     * restaurant is on.
     *
     * An expired offer nobody here joined drops off; one the restaurant did join stays, and
     * is read-only. Dropping those took away the only account of what ran - the restaurant
     * could not see what it had been enrolled on last month, or what an admin had denied -
     * and it also made the app disagree with the vendor panel, which has always kept them.
     *
     * They are in every tab their enrolment state belongs to, not just "all": an approved
     * offer that has ended is still what the Approved tab is counting, and leaving it out of
     * the tab while showing it in the total would only make the two disagree.
     *
     * Offers whose order types the restaurant cannot serve stay visible but are not joinable,
     * which is why is_eligible is returned rather than the row being filtered out.
     */
    public function list(Request $request)
    {
        $restaurant = $this->restaurant($request);
        $limit = $request['limit'] ?? 25;
        $offset = $request['offset'] ?? 1;
        $key = explode(' ', $request['search'] ?? '');

        $query = BogoOffer::with(['enrollments' => fn ($q) => $q->where('restaurant_id', $restaurant->id), 'enrollments.items'])
            ->where('status', 1)
            ->where(function ($q) use ($restaurant) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now())
                    ->orWhereHas('enrollments', fn ($e) => $e->where('restaurant_id', $restaurant->id));
            })
            ->when($request['search'], function ($query) use ($key) {
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('title', 'like', "%{$value}%");
                    }
                });
            });

        // Tab counts are taken before the type filter, so the tabs keep their totals while
        // one of them is open.
        $counts = $this->stateCounts((clone $query)->get(), $restaurant);

        $offers = (clone $query)
            ->when($request['type'] && $request['type'] !== 'all', fn ($q) => $this->filterByState($q, $request['type'], $restaurant->id))
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        // Every approved row on the page reports whether customers can actually see it, and
        // that answer reads the enrolled foods. Resolved for the whole page in one query here
        // rather than one query per food per row.
        $this->primeEnrollmentFoods(
            $offers->getCollection()
                ->map(fn ($offer) => $this->ownEnrollment($offer, $restaurant))
                ->filter(fn ($enrollment) => $enrollment?->status === 'approved')
        );

        $data = [
            'total_size' => $offers->total(),
            'limit' => (int) $limit,
            'offset' => (int) $offset,
        ] + $counts + [
            'offers' => $offers->getCollection()->map(fn ($offer) => $this->formatOffer($offer, $restaurant))->values(),
        ];

        return response()->json($data, 200);
    }

    /**
     * One offer with this restaurant's own enrolment, including the frozen buy/get snapshot
     * so the app can show exactly what was submitted.
     */
    public function details(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);
        $offer = BogoOffer::where('id', $id)->orWhere('slug', $id)->first();

        if (! $offer) {
            return $this->notFound();
        }

        return response()->json($this->formatOffer($offer, $restaurant, true), 200);
    }

    /**
     * Own menu for the combination builder, with variations and add-ons resolved. The
     * restaurant comes from the token, never from the request, so a vendor cannot enumerate
     * another restaurant's foods.
     */
    public function foods(Request $request)
    {
        $restaurant = $this->restaurant($request);

        return response()->json([
            'foods' => $this->bogoRestaurantFoods($restaurant->id, $request['search']),
        ], 200);
    }

    public function join(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);
        $offer = BogoOffer::find($id);

        if (! $offer) {
            return $this->notFound();
        }

        if ($blocked = $this->bogoJoinBlockedReason($offer, $restaurant->id)) {
            return $this->errorResponse($blocked['code'], $blocked['message']);
        }

        if (BogoOfferRestaurant::where('bogo_offer_id', $offer->id)->where('restaurant_id', $restaurant->id)->exists()) {
            return $this->errorResponse('restaurant', translate('messages.you_have_already_joined_this_offer'));
        }

        return $this->writeEnrollment($request, $offer, $restaurant->id, null);
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
        $restaurant = $this->restaurant($request);
        $offer = BogoOffer::find($id);

        if (! $offer) {
            return $this->notFound();
        }

        $enrollment = BogoOfferRestaurant::where('bogo_offer_id', $offer->id)
            ->where('restaurant_id', $restaurant->id)
            ->first();

        if (! $enrollment) {
            return $this->notFound();
        }

        if ($blocked = $this->restaurantResubmitBlockedReason($enrollment)) {
            return $this->errorResponse('status', $blocked);
        }

        if ($blocked = $this->bogoJoinBlockedReason($offer, $restaurant->id)) {
            return $this->errorResponse($blocked['code'], $blocked['message']);
        }

        return $this->writeEnrollment($request, $offer, $restaurant->id, $enrollment);
    }

    /**
     * The restaurant's answer to an offer the admin assigned it, with the foods the admin
     * chose. Only an admin-initiated pending row can be answered.
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

        $enrollment = BogoOfferRestaurant::where('bogo_offer_id', $id)
            ->where('restaurant_id', $restaurant->id)
            ->first();

        if (! $enrollment) {
            return $this->notFound();
        }

        if ($blocked = $this->bogoRespondBlockedReason($enrollment, BogoOffer::find($id),
            translate('messages.there_is_no_pending_admin_request_for_this_offer'))) {
            return $this->errorResponse('status', $blocked);
        }

        if ($request['status'] === 'rejected') {
            $this->markRejected($enrollment, 'restaurant', $request['rejection_reason']);
            $this->notifyAdminOfBogoDenial($restaurant->id);
        } else {
            // Same gate as the panel: a selection whose food, variation or add-on has gone
            // cannot be accepted, because the bundle could never be assembled.
            $problems = $this->enrollmentIntegrityProblems($enrollment);

            if (! empty($problems['blocking'])) {
                return $this->errorResponse('items', translate('messages.this_selection_can_no_longer_be_built')
                    .' '.implode(' | ', $problems['blocking']));
            }

            $enrollment->status = 'approved';
            $enrollment->rejection_reason = null;
            $enrollment->rejected_by = null;
            $enrollment->save();
            $this->notifyAdminOfBogoAcceptance($restaurant->id);
        }

        return response()->json([
            'message' => $request['status'] === 'approved'
                ? translate('messages.you_have_joined_the_bogo_offer')
                : translate('messages.you_have_declined_the_bogo_offer'),
        ], 200);
    }

    /**
     * Backs both can_cancel and can_leave: withdrawing a request that never went live and
     * walking away from a running one are the same delete, and differ only in what the
     * restaurant is told. An unanswered admin request is neither - it is responded to.
     */
    public function leave(Request $request, $id)
    {
        $restaurant = $this->restaurant($request);

        $enrollment = BogoOfferRestaurant::where('bogo_offer_id', $id)
            ->where('restaurant_id', $restaurant->id)
            ->first();

        if (! $enrollment) {
            return $this->notFound();
        }

        if ($blocked = $this->bogoDeleteBlockedReason($enrollment, BogoOffer::find($id))) {
            return $this->errorResponse('status', $blocked);
        }

        $wasApproved = $this->enrollmentState($enrollment) === 'approved';

        DB::transaction(function () use ($enrollment) {
            $enrollment->items()->delete();
            $enrollment->delete();
        });

        return response()->json([
            'message' => $wasApproved
                ? translate('messages.you_have_left_the_bogo_offer')
                : translate('messages.your_request_has_been_cancelled'),
        ], 200);
    }

    /**
     * Shared by join and resubmit: validate, freeze the snapshot, land on pending.
     */
    private function writeEnrollment(Request $request, BogoOffer $offer, int $restaurantId, ?BogoOfferRestaurant $existing)
    {
        $buyItems = $this->decodeArray($request['buy_items']) ?? [];
        $getItems = $this->decodeArray($request['get_items']) ?? [];

        $errors = $this->validateEnrollmentItems($offer, $buyItems, $getItems, $restaurantId);

        if ($errors) {
            return response()->json(['errors' => $errors], 403);
        }

        $signature = $this->buildCombinationSignature($buyItems, $getItems);

        if ($this->combinationTaken($restaurantId, $signature, $existing?->id)) {
            return $this->errorResponse('combination', translate('messages.this_food_combination_is_already_used_in_another_running_offer'));
        }

        $bundlePrice = DB::transaction(function () use ($offer, $restaurantId, $existing, $buyItems, $getItems, $signature) {
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

            return $enrollment->bundle_price;
        });

        $this->notifyAdminOfBogoJoinRequest($offer, $restaurantId);

        return response()->json([
            'message' => $existing
                ? translate('messages.your_request_has_been_resubmitted')
                : translate('messages.your_request_has_been_sent_for_approval'),
            'bundle_price' => (float) $bundlePrice,
            'enrollment_state' => 'pending',
        ], 200);
    }

    /**
     * One offer as the app needs it: the offer, whether this restaurant may join, its own
     * enrolment, and what it is allowed to do next.
     */
    private function formatOffer(BogoOffer $offer, Restaurant $restaurant, bool $withItems = false): array
    {
        $enrollment = $this->ownEnrollment($offer, $restaurant);

        $blocked = $this->bogoJoinBlockedReason($offer, $restaurant->id);
        $isEligible = $blocked === null;

        // Whether a customer can see the bundle today, which "approved" does not answer: a
        // sold-out item, a switched-off category, a closed store or a filled redemption cap
        // all hide it while they last. The app had no way to tell a restaurant why its
        // approved offer was nowhere in the customer app.
        $visibility = $this->bogoCustomerVisibility($enrollment, $offer);

        return [
            'id' => $offer->id,
            'slug' => $offer->slug,
            'title' => $offer->title,
            'description' => $offer->description,
            'image_full_url' => $offer->image_full_url,
            'buy_qty' => $offer->buy_qty,
            'get_qty' => $offer->get_qty,
            // Omitted rather than sent empty while order types are switched off - see
            // BogoOffer::ORDER_TYPES_ENABLED.
            ...(\App\Models\BogoOffer::orderTypesEnabled()
                ? ['order_types' => $offer->order_types ?? []]
                : []),
            'usage_limit_total' => $offer->usage_limit_total,
            'usage_limit_per_customer' => $offer->usage_limit_per_customer,
            'total_uses' => $offer->total_uses,
            'start_date' => $offer->start_date?->format('Y-m-d'),
            'end_date' => $offer->end_date?->format('Y-m-d'),
            'status' => $offer->status,
            // A finished offer, kept on the list because this restaurant is on it. The panel
            // shows nothing else on such a row - not the enrolment state, not an action - so
            // the app has the same one fact to render it from.
            'is_expired' => $this->bogoOfferHasEnded($offer),
            'is_eligible' => $isEligible,
            // Why the Join button is off, so the app does not have to guess a message.
            'ineligible_reason' => $blocked['message'] ?? null,
            'enrollment_state' => $this->enrollmentState($enrollment),
            // The same phrase the vendor panel puts in its status column, so the app does not
            // have to invent a second vocabulary for one enrolment. An expired offer reports
            // only that, exactly as the panel row does.
            'enrollment_state_label' => $this->enrollmentStateLabel($enrollment, $this->bogoOfferHasEnded($offer)),
            // Who said no and why, as one line - the panel's own wording, since a denial the
            // restaurant issued itself must not read as the admin's.
            'rejection_note' => $this->enrollmentRejectionNote($enrollment),
            // What a customer sees, as against what the admin answered. One of running,
            // scheduled, ended, not_visible, or not_applicable while the enrolment is not
            // approved; label is the same word translated, and reasons name what is in the
            // way. Empty reasons on a running offer.
            'visibility_status' => $visibility['status'],
            'visibility_label' => $visibility['label'],
            'visibility_reasons' => $visibility['reasons'],
            'enrollment' => $this->formatEnrollment($enrollment, $withItems),
            'actions' => $this->bogoEnrollmentActions($enrollment, $offer, $isEligible),
            // Why the actions above are off when it is not about eligibility: a denied
            // request may only be reworked, and an ended offer may not be touched at all.
            // Null while the enrolment is still live.
            'actions_locked_reason' => $this->bogoEnrollmentLockedReason($enrollment, $offer),
        ];
    }

    /**
     * This restaurant's own enrolment on an offer, with the offer and the store hung back on
     * it: the visibility rules read both, and without them each row would fetch again what
     * the caller already had.
     */
    private function ownEnrollment(BogoOffer $offer, Restaurant $restaurant): ?BogoOfferRestaurant
    {
        $enrollment = $offer->relationLoaded('enrollments')
            ? $offer->enrollments->firstWhere('restaurant_id', $restaurant->id)
            : BogoOfferRestaurant::where('bogo_offer_id', $offer->id)->where('restaurant_id', $restaurant->id)->first();

        $enrollment?->setRelation('bogo_offer', $offer);
        $enrollment?->setRelation('restaurant', $restaurant);

        return $enrollment;
    }

    private function formatEnrollment(?BogoOfferRestaurant $enrollment, bool $withItems): ?array
    {
        if (! $enrollment) {
            return null;
        }

        $data = [
            'id' => $enrollment->id,
            'status' => $enrollment->status,
            'requested_by' => $enrollment->requested_by,
            'rejection_reason' => $enrollment->rejection_reason,
            // Who said no, so the app can word it as "Denied by admin" or "You declined".
            'rejected_by' => $enrollment->rejected_by,
            'bundle_price' => (float) $enrollment->bundle_price,
            'joined_at' => $enrollment->joined_at?->format('Y-m-d H:i:s'),
        ];

        if (! $withItems) {
            return $data;
        }

        $items = $enrollment->items()->get();
        $addOns = AddOn::whereIn('id', $items->flatMap(fn ($i) => $i->add_on_ids ?? [])->unique()->all())
            ->get()->keyBy('id');

        $shape = fn ($item) => [
            'food_id' => $item->food_id,
            'food_name' => $item->food_name,
            'food_image_full_url' => Helpers::get_full_url('product', $item->food_image, 'public', 'product'),
            'quantity' => $item->quantity,
            // What the line costs inside the bundle - a get item is 0 - beside what the item
            // was worth when the bundle was built, which is what the restaurant hands over.
            'price' => (float) $item->price,
            'original_price' => (float) ($item->original_price ?? $item->price),
            'variations' => $item->variations ?? [],
            'variation_options' => $item->variation_options ?? [],
            'add_on_ids' => $item->add_on_ids ?? [],
            'add_on_qtys' => $item->add_on_qtys ?? [],
            'add_ons' => collect($item->add_on_ids ?? [])
                ->filter(fn ($id) => isset($addOns[$id]))
                ->map(fn ($id) => ['id' => $addOns[$id]->id, 'name' => $addOns[$id]->name, 'price' => (float) $addOns[$id]->price])
                ->values(),
        ];

        $data['buy_items'] = $items->where('type', 'buy')->map($shape)->values();
        $data['get_items'] = $items->where('type', 'get')->map($shape)->values();

        return $data;
    }

    /** How many offers sit in each state, for the app's tabs. */
    private function stateCounts($offers, Restaurant $restaurant): array
    {
        $counts = ['all' => $offers->count(), 'not_joined' => 0, 'pending' => 0,
            'admin_requested' => 0, 'approved' => 0, 'rejected' => 0];

        foreach ($offers as $offer) {
            $enrollment = $offer->enrollments->firstWhere('restaurant_id', $restaurant->id);
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

    private function restaurant(Request $request): Restaurant
    {
        return $request['vendor']->restaurants[0];
    }

    private function notFound()
    {
        return response()->json([
            'errors' => [['code' => 'bogo_offer', 'message' => translate('messages.not_found')]],
        ], 404);
    }
}
