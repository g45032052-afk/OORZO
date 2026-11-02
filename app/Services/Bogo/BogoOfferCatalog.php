<?php

namespace App\Services\Bogo;

use App\CentralLogics\Helpers;
use App\Models\AddOn;
use App\Models\BogoOffer;
use App\Models\BogoOfferItem;
use App\Models\BogoOfferRestaurant;
use App\Models\BogoOfferUsage;
use App\Models\Food;
use App\Models\Restaurant;
use App\Traits\HandlesBogoCart;
use App\Traits\HandlesBogoEnrollment;
use Illuminate\Http\Request;

/**
 * Reading the BOGO catalogue: which offers can be taken, and how one is described.
 *
 * Extracted verbatim from BogoOfferController so the AI chat assistant can answer
 * "any buy one get one deals?" from the SAME source the app's offer screens read.
 * The availability rules here are subtle - an offer is hidden when any single food
 * in any of its bundles is out of stock, switched off, outside its serving hours or
 * at a closed store - and a second implementation would have drifted from them
 * within a release, leaving the assistant advertising bundles the cart then refuses.
 *
 * The move was proven by qa_c9_bogooffersapi.php, which recorded /bogo/home,
 * /bogo/offers and /bogo/offers/{id} beforehand and asserts them byte for byte after.
 */
class BogoOfferCatalog
{
    use HandlesBogoEnrollment;
    use HandlesBogoCart;

    /**
     * Offers a customer in these zones may see: switched on, inside its date window, total
     * usage cap not reached, and with at least one approved restaurant they can order from.
     */
    public function availableOffers(array $zoneIds, ?string $orderType = null)
    {
        return BogoOffer::active()->running()->notExhausted()
            ->whereHas('enrollments', fn ($q) => $this->atServableStore($q->approved(), $zoneIds))
            // A bundle is all or nothing: if one of its foods is switched off, deleted or out
            // of stock, the whole thing cannot be served, so the offer must not be advertised
            // at all. The cart already refuses such a bundle - this stops the customer being
            // shown it in the first place.
            ->whereIn('id', $this->orderableOfferIds($zoneIds))
            ->when(BogoOffer::orderTypesEnabled() && $orderType, function ($q) use ($orderType) {
                $q->where(function ($inner) use ($orderType) {
                    foreach ($this->orderTypeAliases($orderType) as $alias) {
                        $inner->orWhereJsonContains('order_types', $alias);
                    }
                });
            });
    }

    /**
     * Offers with at least one bundle that can actually be served in these zones.
     *
     * Availability is a stock and status question - item_stock is raw stock minus sell_count,
     * and variation and add-on stock sit on their own rows - so it cannot be asked in SQL
     * without reimplementing the rule. It is resolved through the same trait the cart and
     * checkout use, then applied as an id constraint so the paginator still counts correctly.
     */
    private function orderableOfferIds(array $zoneIds): array
    {
        return $this->orderableEnrollments(
            $this->atServableStore(BogoOfferRestaurant::approved(), $zoneIds)
        )->pluck('bogo_offer_id')->unique()->values()->all();
    }

    /**
     * The store side of "may a customer be shown this bundle": in their zones, switched on,
     * and open at this moment.
     *
     * A closed store is hidden rather than greyed out. An offer is a reason to visit a store
     * now, so advertising one nobody can order from is worse than not advertising it - and
     * scheduling around it is not something the offer screens support.
     */
    public function atServableStore($enrollmentQuery, array $zoneIds)
    {
        return $enrollmentQuery->whereHas('restaurant',
            fn ($r) => $r->active()->openNow()->whereIn('zone_id', $zoneIds));
    }

    /**
     * The enrolments in the given query a customer could actually order from right now.
     *
     * Two questions that SQL cannot answer, so they are resolved here and applied as an id
     * constraint, which keeps the paginator counting correctly:
     *
     *  - every item in stock and switched on, decided by the same trait the cart and checkout
     *    use, because item_stock is raw stock minus sell_count and variation and add-on stock
     *    sit on their own rows;
     *  - the store offering at least one of the offer's order types. An offer published for
     *    dine-in only, joined by a store that does not do dine-in, can never be ordered - so
     *    it must not be shown at that store, whatever order type the customer is browsing with.
     */
    public function orderableEnrollments($query)
    {
        $enrollments = $query->with(['items.food', 'bogo_offer', 'restaurant.restaurant_config'])->get();

        // Every food across every bundle resolved in one query, before the rule is asked
        // about any of them. Asked one at a time this listing would pay a query per item.
        $this->bogoPrimeActiveFoods(
            $enrollments->flatMap(fn ($enrollment) => $enrollment->items->pluck('food_id'))->all()
        );

        return $enrollments
            ->filter(fn ($enrollment) => $this->bogoItemsUnavailableReason($enrollment, 1) === null
                && $this->servesAnyOrderType($enrollment->restaurant, $enrollment->bogo_offer?->order_types ?? []));
    }

    /**
     * Approved enrolments for this offer that the customer can actually order from, with
     * the items needed to price the bundle and draw its thumbnails.
     */
    public function bundleQuery(BogoOffer $offer, array $zoneIds, Request $request)
    {
        $orderType = $request['order_type'];

        return BogoOfferRestaurant::where('bogo_offer_id', $offer->id)
            ->approved()
            // One restaurant's bundle can be unservable while another's is fine, so this is
            // filtered per bundle rather than per offer.
            ->whereIn('id', $this->orderableEnrollments(
                BogoOfferRestaurant::where('bogo_offer_id', $offer->id)->approved()
            )->pluck('id')->all())
            // items.food so valuing the free items does not fetch a food per item.
            ->with(['items.food', 'restaurant' => function ($q) use ($request) {
                // WithOpen adds selectRaw columns, so it can only go on the eager load -
                // inside a whereHas it lands in an EXISTS subquery and the SQL is invalid.
                $q->WithOpen($request->header('longitude'), $request->header('latitude'));
            }])
            ->whereHas('restaurant', function ($q) use ($zoneIds, $orderType) {
                $q->active()->openNow()->whereIn('zone_id', $zoneIds);

                if ($orderType) {
                    $this->constrainByOrderType($q, $orderType);
                }
            });
    }

    /**
     * dine_in is not a restaurants column - it lives on restaurant_config - so it needs a
     * relation check while the other two are plain flags.
     */
    private function constrainByOrderType($query, string $orderType): void
    {
        // Part of the same mechanism, so it goes quiet with it - see
        // BogoOffer::ORDER_TYPES_ENABLED. Note this one narrows by what the *store* can do
        // rather than by what the offer allows, so it is the one worth reviewing first if the
        // order type filter should come back before the offer level restriction does.
        if (! BogoOffer::orderTypesEnabled()) {
            return;
        }

        if (in_array($orderType, ['delivery', 'home_delivery'], true)) {
            $query->where('delivery', 1);
        } elseif ($orderType === 'take_away') {
            $query->where('take_away', 1);
        } elseif ($orderType === 'dine_in') {
            $query->whereHas('restaurant_config', fn ($c) => $c->where('dine_in', 1));
        }
    }

    private function orderTypeAliases(string $orderType): array
    {
        // The panels write 'delivery', older records and some clients say 'home_delivery'.
        return in_array($orderType, ['delivery', 'home_delivery'], true)
            ? ['delivery', 'home_delivery']
            : [$orderType];
    }

    public function offerCard(BogoOffer $offer, ?Request $request = null): array
    {
        $card = [
            'id' => $offer->id,
            'slug' => $offer->slug,
            'title' => $offer->title,
            'description' => $offer->description,
            'image_full_url' => $offer->image_full_url,
            'buy_qty' => $offer->buy_qty,
            'get_qty' => $offer->get_qty,
            'offer_label' => 'Buy '.$offer->buy_qty.' Get '.$offer->get_qty,
            'start_date' => $offer->start_date?->format('Y-m-d H:i:s'),
            'end_date' => $offer->end_date?->format('Y-m-d H:i:s'),
            'valid_until' => $offer->end_date?->format('d M Y'),
        ];

        // Omitted rather than sent empty while order types are switched off - see
        // BogoOffer::ORDER_TYPES_ENABLED.
        if (BogoOffer::orderTypesEnabled()) {
            $card['order_types'] = $offer->order_types ?? [];
        }

        if ($request) {
            $card['usage_limit_per_customer'] = $offer->usage_limit_per_customer;
            $card['remaining_uses'] = $this->remainingUses($offer, $request);
        }

        return $card;
    }

    /**
     * null means "no per-customer cap", so the client should not show a counter. A guest has
     * no usage history to read, so the full allowance is reported.
     */
    public function remainingUses(BogoOffer $offer, Request $request): ?int
    {
        if (! $offer->usage_limit_per_customer) {
            return null;
        }

        $user = $request->user;

        if (! $user) {
            return $offer->usage_limit_per_customer;
        }

        $used = (int) BogoOfferUsage::where('bogo_offer_id', $offer->id)
            ->where('user_id', $user->id)->sum('quantity');

        return max(0, $offer->usage_limit_per_customer - $used);
    }

    /**
     * $withRestaurant is off for the store flow, which already knows the restaurant. It is a
     * parameter rather than something the caller strips afterwards because building the
     * payload costs a couple of queries per bundle - formatting a restaurant pulls its food
     * list and cheapest price - and there is no sense paying for it to throw it away.
     */
    public function bundleCard(BogoOfferRestaurant $enrollment, bool $withRestaurant = true): array
    {
        $items = $enrollment->items;

        return [
            'bundle_id' => $enrollment->id,
            'buy_count' => (int) $items->where('type', 'buy')->sum('quantity'),
            'get_count' => (int) $items->where('type', 'get')->sum('quantity'),
            'item_thumbnails' => $items->take(4)->map(fn ($item) => $this->foodImageUrl($item->food_image))->values(),
        ]
        + ($withRestaurant ? ['restaurant' => $this->restaurantPayload($enrollment->restaurant)] : [])
        + [
            // Everything the bottom sheet needs, so opening a bundle costs no second call.
            'buy_items' => $items->where('type', 'buy')->map(fn ($i) => $this->itemPayload($i))->values(),
            'free_items' => $items->where('type', 'get')->map(fn ($i) => $this->itemPayload($i))->values(),
        ] + $this->pricing($enrollment);
    }

    /**
     * The bundle's price and any happy hour on it.
     *
     * The rule and the arithmetic both live in HandlesBogoCart so that the offer screens and
     * the cart cannot drift apart - which they had, the cart being the one surface that never
     * mentioned a running happy hour at all.
     */
    private function pricing(BogoOfferRestaurant $enrollment): array
    {
        return $this->bogoBundlePricing($enrollment);
    }

    /**
     * What one enrolled item was worth when the restaurant built the bundle.
     *
     * The menu is only consulted for a row written before that figure was recorded and never
     * backfilled - otherwise editing a food would move an offer that was already agreed, and
     * a food sitting on both sides of one bundle would be shown at two different prices.
     */
    private function frozenUnitPrice(BogoOfferItem $item): float
    {
        if ($item->original_price !== null) {
            return (float) $item->original_price;
        }

        $food = $item->relationLoaded('food') ? $item->food : Food::find($item->food_id);

        // Nothing left to value it against, so it contributes nothing rather than
        // breaking the total.
        if (! $food) {
            return 0.0;
        }

        return $this->resolveUnitPrice($food, $item->variations, $item->add_on_ids, $item->add_on_qtys);
    }

    private function restaurantPayload(?Restaurant $restaurant): ?array
    {
        if (! $restaurant) {
            return null;
        }

        $data = Helpers::restaurant_data_formatting($restaurant, false);

        return is_array($data) ? $data : $data->toArray();
    }

    private function itemPayload(BogoOfferItem $item): array
    {
        $food = $item->food;

        return [
            'id' => $item->id,
            'food_id' => $item->food_id,
            'slug' => $food?->slug,
            // The frozen copy, so a renamed or deleted food still renders what was enrolled.
            'name' => $item->food_name,
            'image_full_url' => $this->foodImageUrl($item->food_image),
            'quantity' => (int) $item->quantity,
            // What the item was worth when the bundle was built - the same figure for both
            // sides. A buy item is charged it; a free one is what the customer is spared.
            'price' => round($this->frozenUnitPrice($item), config('round_up_to_digit', 2)),
            'is_free' => $item->type === 'get',
            'variations' => $item->variations ?? [],
            'variation_options' => $item->variation_options ?? [],
            'variation_summary' => $this->variationSummary($item->variations),
            'add_ons' => $this->addOnPayload($item),
            'avg_rating' => $food ? (float) $food->avg_rating : 0,
            'rating_count' => $food ? (int) $food->rating_count : 0,
            'veg' => $food ? (int) $food->veg : null,
            'is_available' => (bool) $food,
        ];
    }

    /**
     * "Size : Small ; Toppings : Capsicum, Sauce" - the one-line summary under the item name.
     */
    private function variationSummary(?array $variations): string
    {
        if (empty($variations)) {
            return '';
        }

        $parts = [];

        foreach ($variations as $variation) {
            $labels = $variation['values']['label'] ?? [];

            if (! empty($variation['name']) && ! empty($labels)) {
                $parts[] = $variation['name'].' : '.implode(', ', (array) $labels);
            }
        }

        return implode(' ; ', $parts);
    }

    private function addOnPayload(BogoOfferItem $item): array
    {
        if (empty($item->add_on_ids)) {
            return [];
        }

        $quantities = array_combine(
            $item->add_on_ids,
            $item->add_on_qtys ?: array_fill(0, count($item->add_on_ids), 1)
        );

        return AddOn::whereIn('id', $item->add_on_ids)->get()->map(fn ($addOn) => [
            'id' => $addOn->id,
            'name' => $addOn->name,
            'price' => (float) $addOn->price,
            'quantity' => (int) ($quantities[$addOn->id] ?? 1),
        ])->values()->toArray();
    }

    /**
     * Null when the food carries no image - the clients already fall back to a placeholder,
     * and this matches the nullable image_full_url the models append elsewhere.
     */
    private function foodImageUrl(?string $image): ?string
    {
        return Helpers::get_full_url('product', $image, 'public', 'product');
    }
}
