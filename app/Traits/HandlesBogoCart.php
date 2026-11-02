<?php

namespace App\Traits;

use App\CentralLogics\Helpers;
use App\Models\AddOn;
use App\Models\BogoOffer;
use App\Models\BogoOfferItem;
use App\Models\BogoOfferRestaurant;
use App\Models\BogoOfferUsage;
use App\Models\Cart;
use App\Models\Food;
use App\Models\VariationOption;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Cart and checkout rules for a BOGO bundle.
 *
 * A bundle is atomic: adding it writes one row per enrolled item, all sharing a
 * bogo_group_id, and the group only ever moves as a whole. Quantity N means N copies of the
 * whole bundle - N x every buy item and N x every free item - so a group's line quantities
 * are the enrolled quantity multiplied by N.
 *
 * The same rules run twice: when the bundle is added, and again when the order is placed, so
 * a limit reached or an offer ended while the cart sat idle cannot slip through.
 */
trait HandlesBogoCart
{
    /**
     * The approved enrolment behind an offer/restaurant pair, or null. Both accept an id or
     * a slug, matching the read endpoints.
     */
    protected function resolveBogoBundle($offerKey, $restaurantKey, array $zoneIds = []): ?BogoOfferRestaurant
    {
        $offer = BogoOffer::where(fn ($q) => $q->where('id', $offerKey)->orWhere('slug', $offerKey))->first();

        if (! $offer) {
            return null;
        }

        return BogoOfferRestaurant::where('bogo_offer_id', $offer->id)
            ->approved()
            ->with(['items', 'restaurant', 'bogo_offer'])
            ->whereHas('restaurant', function ($q) use ($restaurantKey, $zoneIds) {
                $q->where(fn ($r) => $r->where('id', $restaurantKey)->orWhere('slug', $restaurantKey));

                if ($zoneIds) {
                    $q->whereIn('zone_id', $zoneIds);
                }
            })
            ->first();
    }

    /**
     * A bundle by its own id - the bogo_offer_restaurant row. This is the identifier every
     * read endpoint hands back as bundle_id, and it names an offer at one restaurant, which
     * an offer id alone cannot do when several restaurants run the same offer.
     */
    protected function resolveBogoBundleById($bundleId, array $zoneIds = []): ?BogoOfferRestaurant
    {
        return BogoOfferRestaurant::where('id', $bundleId)
            ->approved()
            ->with(['items', 'restaurant', 'bogo_offer'])
            ->when($zoneIds, fn ($q) => $q->whereHas('restaurant', fn ($r) => $r->whereIn('zone_id', $zoneIds)))
            ->first();
    }

    /**
     * Why this bundle cannot be ordered, or null when it can.
     *
     * Returned as a message rather than a boolean because the client shows it against the
     * flagged group in the cart, and the same text explains a refused add.
     *
     * $at is the moment the order is actually fulfilled. It defaults to now, which is right
     * for the cart and for an instant order, but a scheduled order has to be judged against
     * its slot: a bundle booked for next Tuesday is worthless if the offer expires on Monday,
     * and the customer has to be told at checkout rather than at delivery. Only the offer's
     * own date window moves with it - status, enrolment, stock and usage are read as they
     * stand now, because a future value of any of them is unknowable.
     */
    protected function bogoUnavailableReason(
        BogoOfferRestaurant $enrollment,
        int $bundleQuantity,
        $userId,
        int $isGuest,
        ?string $phone = null,
        // CarbonInterface, not Illuminate\Support\Carbon: callers hand over whichever Carbon
        // they already have, and Carbon\Carbon is not an instance of the Illuminate subclass.
        ?CarbonInterface $at = null,
        // The order type the customer is checking out with. Null in the cart, which is
        // written before the customer has chosen one.
        ?string $orderType = null
    ): ?string {
        $offer = $enrollment->bogo_offer;
        $at = $at ?: now();
        $isScheduled = $at->greaterThan(now());

        if (! $offer || ! $offer->status) {
            return translate('messages.This_offer_is_no_longer_available');
        }

        // An offer runs for the order types it was published for. The read endpoints filter
        // on this when the client passes order_type, but that is a browsing convenience -
        // nothing stopped a take-away-only offer being checked out as a delivery.
        if (BogoOffer::orderTypesEnabled() && $orderType && ! $this->bogoServesOrderType($offer, $orderType)) {
            return translate('messages.This_offer_is_not_available_for_this_order_type');
        }

        if ($offer->start_date && $offer->start_date->greaterThan($at)) {
            return $isScheduled
                ? translate('messages.This_offer_does_not_start_until_after_your_scheduled_time')
                : translate('messages.This_offer_has_not_started_yet');
        }

        if ($offer->end_date && $offer->end_date->lessThan($at)) {
            return $isScheduled
                ? translate('messages.This_offer_will_have_ended_by_your_scheduled_time')
                : translate('messages.This_offer_has_already_ended');
        }

        if ($enrollment->status !== 'approved') {
            return translate('messages.This_restaurant_is_no_longer_part_of_the_offer');
        }

        if (! $enrollment->restaurant || ! $enrollment->restaurant->status) {
            return translate('messages.This_restaurant_is_currently_unavailable');
        }

        // An offer published for dine-in only, joined by a store that does not do dine-in, can
        // never be ordered at that store whatever the customer picks. The read endpoints hide
        // it; this stops one already sitting in a cart from being checked out. Deliberately
        // not a check on whether the store is open: a scheduled order at a closed store is
        // legitimate, and refusing it here would block one.
        if (BogoOffer::orderTypesEnabled() && ! $enrollment->restaurant->servesAnyOrderType($offer->order_types ?? [])) {
            return translate('messages.This_offer_is_not_available_at_this_restaurant');
        }

        // The whole-offer cap counts bundles, so N copies need N left.
        if ($offer->usage_limit_total !== null
            && ($offer->total_uses + $bundleQuantity) > $offer->usage_limit_total) {
            return translate('messages.This_offer_has_reached_its_usage_limit');
        }

        $remaining = $this->bogoRemainingUses($offer, $userId, $isGuest, $phone);

        if ($remaining !== null && $bundleQuantity > $remaining) {
            return $remaining > 0
                ? translate('messages.You_can_only_use_this_offer').' '.$remaining.' '.translate('messages.more_times')
                : translate('messages.You_have_already_used_this_offer_the_maximum_number_of_times');
        }

        return $this->bogoItemsUnavailableReason($enrollment, $bundleQuantity);
    }

    /**
     * Does the offer run for this order type.
     *
     * An offer with no order types set is unrestricted. 'delivery' and 'home_delivery' are
     * the same thing written two ways - the panels store the first, older records and some
     * clients send the second - so either matches either.
     */
    protected function bogoServesOrderType(BogoOffer $offer, string $orderType): bool
    {
        // Every offer runs for every order type while the feature is off.
        if (! BogoOffer::orderTypesEnabled()) {
            return true;
        }

        $types = $offer->order_types ?? [];

        if (! $types) {
            return true;
        }

        $aliases = in_array($orderType, ['delivery', 'home_delivery'], true)
            ? ['delivery', 'home_delivery']
            : [$orderType];

        return (bool) array_intersect($aliases, $types);
    }

    /**
     * Stock and availability of the enrolled foods, free ones included - a bundle whose free
     * item cannot be fulfilled is not orderable.
     */
    /**
     * Foods already known to satisfy Food::active(), keyed by id.
     *
     * A listing asks the availability rule about every bundle in a zone, so resolving this
     * one food at a time turns one page into dozens of queries - the same reason the foods
     * themselves are eager loaded. Primed in a single query by bogoPrimeActiveFoods() and
     * consulted here; an unprimed id falls back to its own lookup, so the cart and the order
     * paths need no special handling.
     *
     * Valid only for the batch it was primed for, and REPLACED on every prime rather than
     * added to. Some holders of this trait live for the whole process - RestaurantLogic keeps
     * one in a `static $probe` - so a memo that accumulated would answer from a world that had
     * since changed: switch a food off and the listing keeps advertising it. A listing primes
     * once at the top of the call, which is exactly how long the answer is good for.
     *
     * @var array<int, bool>
     */
    private array $bogo_active_foods = [];

    /**
     * Resolve every food a listing is about to ask for, in one query.
     *
     * Without this a page of bundles pays a query per enrolled item - the same reason the
     * foods themselves are eager loaded.
     */
    protected function bogoPrimeActiveFoods(array $foodIds): void
    {
        $ids = array_values(array_unique(array_map('intval', array_filter($foodIds))));

        $this->bogo_active_foods = [];

        if (! $ids) {
            return;
        }

        $active = Food::withoutGlobalScope(\App\Scopes\RestaurantScope::class)
            ->active()->whereIn('id', $ids)->pluck('id')->all();

        foreach ($ids as $id) {
            $this->bogo_active_foods[$id] = in_array($id, $active, true);
        }
    }

    protected function bogoFoodIsActive($food): bool
    {
        $id = (int) $food->getKey();

        // A primed answer is from this call and can be trusted. Anything else is asked
        // outright and NOT remembered - the cart and order paths look at one bundle at a
        // time, where a query is cheap and a stale answer is not.
        if (array_key_exists($id, $this->bogo_active_foods)) {
            return $this->bogo_active_foods[$id];
        }

        return Food::withoutGlobalScope(\App\Scopes\RestaurantScope::class)
            ->active()->whereKey($id)->exists();
    }

    protected function bogoItemsUnavailableReason(BogoOfferRestaurant $enrollment, int $bundleQuantity): ?string
    {
        foreach ($enrollment->items as $item) {
            // Use the eager loaded food when the caller supplied one. The read endpoints ask
            // this of every bundle in a zone to decide what to advertise, and fetching a food
            // per item there turned one listing into dozens of queries.
            $food = $item->relationLoaded('food') ? $item->food : Food::find($item->food_id);

            if (! $food) {
                return $item->food_name.' '.translate('messages.is_no_longer_available');
            }

            // The platform's own definition of a listable food, not just its status column.
            // Food::active() also requires the restaurant to be trading and the food's
            // category to be enabled - and a food in a DISABLED CATEGORY keeps status 1
            // while disappearing from every other surface. Checking status alone advertised
            // a bundle the checkout then refused with "<food> is currently unavailable",
            // because order placement resolves its lines through active().
            if (! $this->bogoFoodIsActive($food)) {
                return $food->name.' '.translate('messages.is_currently_unavailable');
            }

            // Outside its serving hours the food cannot be made, so the bundle cannot be
            // served either - and a bundle is all or nothing.
            //
            // This is asked for bundles only. Serving hours are not enforced anywhere else in
            // the system: Food::scopeAvailable() exists but is used as an opt-in listing
            // filter, and an ordinary item outside its hours still reaches a cart and an
            // order. Applying it here keeps a bundle honest without changing how single items
            // behave. The comparison mirrors that scope exactly - a window cannot cross
            // midnight, because the two columns cannot express one.
            if ($reason = $this->bogoOutsideServingHoursReason($food)) {
                return $reason;
            }

            // Asked before anything about stock, because a variation the food no longer
            // carries has no stock to ask about - and the questions below would all answer
            // "nothing to check" rather than "cannot be built".
            if ($reason = $this->bogoVariationMissingReason($food, $item)) {
                return $reason;
            }

            $needed = (int) $item->quantity * $bundleQuantity;
            $optionIds = BogoOfferItem::resolveVariationOptionIds(
                $item->food_id, $item->variations, $item->variation_options
            );

            $stock = Helpers::addonAndVariationStockCheck(
                product: $food,
                quantity: $needed,
                add_on_qtys: $item->add_on_qtys ?: 1,
                variation_options: $optionIds,
                add_on_ids: $item->add_on_ids,
                incrementCount: false,
            );

            if (! empty($stock['out_of_stock'])) {
                return $this->bogoStockShortfallReason($food, $item, $bundleQuantity, $stock);
            }

            if ($reason = $this->bogoVariationStockReason($food, $item, $needed)) {
                return $reason;
            }
        }

        return null;
    }

    /**
     * A shortage told in bundles.
     *
     * The stock check counts units of one food, so asked for three bundles of two it answers
     * "Only 4 Quantity is available for Meat Pizza" - a number the customer cannot act on,
     * because they are not buying Meat Pizza by the unit. What they can act on is how many
     * bundles are left, so that is what is said, with the item that is short so the figure is
     * not a mystery.
     */
    private function bogoStockShortfallReason($food, $item, int $bundleQuantity, array $stock): string
    {
        $perBundle = max(1, (int) $item->quantity);
        $available = (int) ($stock['current_stock'] ?? 0);
        $possible = intdiv($available, $perBundle);

        // Nothing can be built at all, or the shortage is not about how many were asked for -
        // either way the stock check's own wording is the better answer.
        if ($possible < 1 || $possible >= $bundleQuantity) {
            return $stock['out_of_stock'];
        }

        // 'Only' and 'left' already exist as keys with other meanings elsewhere in the file, so
        // the first is reused and the second is named for this use.
        return translate('messages.Only').' '.$possible.' '.translate('messages.of_this_offer_can_be_ordered')
            .' - '.$food->name.' '.translate('messages.has_only').' '.$available.' '.translate('messages.left_in_stock');
    }

    /**
     * The food's own serving hours, if it has any and now is not inside them.
     */
    private function bogoOutsideServingHoursReason($food): ?string
    {
        $starts = $food->getRawOriginal('available_time_starts');
        $ends = $food->getRawOriginal('available_time_ends');

        if (! $starts || ! $ends) {
            return null;
        }

        $now = now()->format('H:i:s');

        if ($starts <= $now && $ends >= $now) {
            return null;
        }

        return $food->name.' '.translate('messages.is_not_available_at_this_time')
            .' ('.substr($starts, 0, 5).' - '.substr($ends, 0, 5).')';
    }

    /**
     * Whether the food still offers the variation the bundle was frozen on.
     *
     * The snapshot names its options by label, so a label the restaurant has since dropped or
     * renamed resolves to no option id at all - and an empty set of ids reads as "this line
     * has no variation", which every stock rule below is happy to pass. The bundle stayed on
     * every listing, went into carts, and reached the kitchen as an order line with an empty
     * variation: the customer paid the price of a size the restaurant no longer makes.
     *
     * The enrolment side already refuses to approve into this state; this is the same question
     * asked again afterwards, because the menu keeps moving once the bundle is live.
     */
    private function bogoVariationMissingReason($food, $item): ?string
    {
        $chosen = is_array($item->variations) ? $item->variations : json_decode((string) $item->variations, true);

        if (empty($chosen)) {
            return null;
        }

        $available = is_array($food->variations) ? $food->variations : json_decode((string) $food->variations, true);

        foreach ($chosen as $variation) {
            // A renamed group is the same failure as a deleted option: nothing on the food
            // matches what was chosen, so firstWhere returns null and every label is missing.
            $group = collect($available ?? [])->firstWhere('name', $variation['name'] ?? null);
            $labels = collect($group['values'] ?? [])->pluck('label')->all();

            foreach ($variation['values']['label'] ?? [] as $option) {
                if (! in_array($option, $labels, true)) {
                    return $food->name.' - '.$option.' : '
                        .translate('messages.the_selected_variation_is_no_longer_available');
                }
            }
        }

        return null;
    }

    /**
     * Stock of the exact variation options the bundle was built on.
     *
     * The shared stock helper nests its variation loop inside its "food is stocked" branch, so
     * a food marked unlimited never has its options checked at all - a bundle built on a
     * sold-out Large stayed on every listing and went into the cart. The option is what the
     * customer would actually receive, so its stock is asked here on its own terms, whatever
     * the food above it says.
     */
    private function bogoVariationStockReason($food, $item, int $needed): ?string
    {
        // Only the unlimited-food case is left to cover; anything else the helper already saw.
        if ($food->stock_type && $food->stock_type !== 'unlimited') {
            return null;
        }

        $optionIds = BogoOfferItem::resolveVariationOptionIds(
            $item->food_id, $item->variations, $item->variation_options
        );

        if (empty($optionIds)) {
            return null;
        }

        foreach (VariationOption::whereIn('id', $optionIds)->get() as $option) {
            if ($option->stock_type === 'unlimited') {
                continue;
            }

            $available = (int) $option->total_stock - (int) $option->sell_count;

            if ($available <= 0 || $available < $needed) {
                // Worded exactly as the shared helper words it, so the customer never sees two
                // different sentences for the same situation.
                return $available > 0
                    ? translate('Only').' '.$available.' '.translate('Quantity_is_abailable_for').' '
                        .$food->name.' \''.'s '.$option->option_name.' '.translate('Variation_!!!')
                    : $food->name.' \''.'s '.$option->option_name.' '.translate('Variation_is_out_of_stock_!!!');
            }
        }

        return null;
    }

    /**
     * Bundles per offer behind a set of order lines, keyed by offer id.
     *
     * Reads both shapes the order carries: OrderDetail models before an edit rebuilds them,
     * and the plain arrays the rebuild produces. Two groups may share one offer, so the counts
     * are summed - the caps are counted per offer, not per group.
     */
    protected function bogoBundlesPerOffer($rows, int $restaurantId): array
    {
        $perOffer = [];

        foreach (collect($rows)->filter(fn ($row) => data_get($row, 'bogo_group_id'))->groupBy(fn ($row) => data_get($row, 'bogo_group_id')) as $group) {
            $offerId = (int) data_get($group->first(), 'bogo_offer_id');

            if (! $offerId) {
                continue;
            }

            $bundles = Helpers::bogo_bundle_quantity(
                $group->map(fn ($row) => (object) [
                    'bogo_offer_id' => data_get($row, 'bogo_offer_id'),
                    'food_id' => data_get($row, 'food_id'),
                    'quantity' => data_get($row, 'quantity', 1),
                ]),
                $restaurantId
            );

            $perOffer[$offerId] = ($perOffer[$offerId] ?? 0) + $bundles;
        }

        return $perOffer;
    }

    /**
     * Why this edit may not take the offers it is asking for, or null.
     *
     * Only what the edit *adds* is judged. An order already holds its bundles - they were
     * checked when it was placed - and refusing to save an untouched one would strand support
     * with an order nobody can edit the moment the offer ends. So a bundle left alone or made
     * smaller passes without question, and only the extra copies are put to the same rules a
     * new bundle faces: the offer still running, the restaurant still enrolled, the caps not
     * yet reached.
     *
     * The delta is what the caps are asked about for the same reason: the copies already on
     * the order are already counted in the offer's usage, so asking about the full new figure
     * would count them twice and refuse an edit that fits.
     */
    protected function bogoEditRefusalReason($order, array $newRows, array $bundlesBefore, int $restaurantId): ?string
    {
        $address = is_string($order->delivery_address)
            ? json_decode($order->delivery_address, true)
            : (array) $order->delivery_address;

        // A scheduled order is judged against its slot, exactly as placement judges it: an
        // offer that will have ended by the delivery time cannot be honoured then either.
        $at = $order->scheduled && $order->schedule_at ? $order->schedule_at : null;

        foreach ($this->bogoBundlesPerOffer($newRows, $restaurantId) as $offerId => $bundles) {
            $added = $bundles - ($bundlesBefore[$offerId] ?? 0);

            if ($added <= 0) {
                continue;
            }

            $enrollment = Helpers::bogo_enrollment_for($offerId, $restaurantId);

            if (! $enrollment) {
                return translate('messages.Offer_not_found');
            }

            $reason = $this->bogoUnavailableReason(
                $enrollment,
                $added,
                $order->is_guest ? null : $order->user_id,
                (int) $order->is_guest,
                $order->is_guest ? data_get($address, 'contact_person_number') : null,
                $at,
                $order->order_type
            );

            if ($reason) {
                return $reason;
            }
        }

        return null;
    }

    /**
     * Make the offer's usage match what the order now holds.
     *
     * Placement writes one usage row per bundle group and adds the same count to the offer's
     * running total; those two numbers are what usage_limit_total and usage_limit_per_customer
     * are measured against. An edit rebuilds the lines, so the counts have to move with them -
     * up when a bundle is scaled up or another offer is added, down when one shrinks or is
     * removed. Left alone, a customer could pass their own cap by ordering one bundle and
     * editing it upwards, and a removed bundle went on eating someone else's allowance.
     *
     * The rows for this order are rewritten rather than patched: an edit can drop an offer
     * entirely or bring in one that was never there, so there is not always a row to patch.
     */
    protected function syncBogoUsage($order, array $orderDetails): void
    {
        // Bundles, not line quantity: three copies of a Buy 2 Get 1 are nine lines worth of
        // food and three uses of the offer.
        $wanted = $this->bogoBundlesPerOffer($orderDetails, (int) $order->restaurant_id);

        $existing = BogoOfferUsage::where('order_id', $order->id)
            ->get()
            ->groupBy('bogo_offer_id')
            ->map(fn ($rows) => (int) $rows->sum('quantity'))
            ->all();

        foreach (array_unique(array_merge(array_keys($wanted), array_keys($existing))) as $offerId) {
            $delta = ($wanted[$offerId] ?? 0) - ($existing[$offerId] ?? 0);

            if ($delta === 0) {
                continue;
            }

            // GREATEST rather than decrement(): a total that has already been corrected by
            // hand, or an order whose offer was counted twice, must not drive it negative.
            BogoOffer::where('id', $offerId)->update([
                'total_uses' => DB::raw('GREATEST(COALESCE(total_uses, 0) + ('.$delta.'), 0)'),
            ]);
        }

        BogoOfferUsage::where('order_id', $order->id)->delete();

        if (empty($wanted)) {
            return;
        }

        // A guest is counted by the phone on the order, which is where placement takes it
        // from too - the number the customer gave at checkout.
        $address = is_string($order->delivery_address) ? json_decode($order->delivery_address, true) : (array) $order->delivery_address;
        $phone = $order->is_guest ? data_get($address, 'contact_person_number') : null;

        foreach ($wanted as $offerId => $quantity) {
            BogoOfferUsage::create([
                'bogo_offer_id' => $offerId,
                'order_id' => $order->id,
                'user_id' => $order->is_guest ? null : $order->user_id,
                'is_guest' => $order->is_guest ? 1 : 0,
                'phone' => $phone,
                'quantity' => $quantity,
            ]);
        }
    }

    /**
     * How many more times this customer may use the offer, or null when uncapped.
     * A guest has no account, so their history is keyed by phone.
     */
    protected function bogoRemainingUses(BogoOffer $offer, $userId, int $isGuest, ?string $phone = null): ?int
    {
        if (! $offer->usage_limit_per_customer) {
            return null;
        }

        $used = (int) BogoOfferUsage::where('bogo_offer_id', $offer->id)
            ->when($isGuest, fn ($q) => $q->where('is_guest', 1)->where('phone', $phone))
            ->when(! $isGuest, fn ($q) => $q->where('user_id', $userId))
            ->sum('quantity');

        return max(0, $offer->usage_limit_per_customer - $used);
    }

    /**
     * The cart rows for one bundle. Buy items carry their frozen price, free items are zero
     * so the cart total never charges for them.
     */
    protected function bogoCartRows(
        BogoOfferRestaurant $enrollment,
        string $groupId,
        int $bundleQuantity,
        $userId,
        int $isGuest
    ): array {
        $rows = [];

        foreach ($enrollment->items as $item) {
            $rows[] = [
                'user_id' => $userId,
                'is_guest' => $isGuest,
                'restaurant_id' => $enrollment->restaurant_id,
                'item_id' => $item->food_id,
                'item_type' => Food::class,
                'price' => $item->type === 'buy' ? (float) $item->price : 0,
                'quantity' => (int) $item->quantity * $bundleQuantity,
                'variations' => json_encode($item->variations ?: []),
                // Resolved, not copied: placement reads this column to decide which option's
                // sell_count to increment, so an empty one meant a bundle could be ordered all
                // day without ever consuming the variation stock it was built on.
                'variation_options' => json_encode(BogoOfferItem::resolveVariationOptionIds(
                    $item->food_id, $item->variations, $item->variation_options
                )),
                'add_on_ids' => json_encode($item->add_on_ids ?: []),
                'add_on_qtys' => json_encode($item->add_on_qtys ?: []),
                'bogo_offer_id' => $enrollment->bogo_offer_id,
                'bogo_group_id' => $groupId,
                'is_free_item' => $item->type === 'get' ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $rows;
    }

    /**
     * The frozen unit price of every enrolled item behind the bundles in these cart rows,
     * as [bogo_group_id]['buy:<food_id>' | 'get:<food_id>'] => price.
     *
     * Read from the enrolment rather than from the cart row so there is one source of truth:
     * the cart already quotes the enrolment's current bundle_price, and pricing the order
     * from anywhere else would bill something the customer was never shown. The menu is not
     * consulted at all - a bundle costs what it cost when the restaurant built it.
     *
     * $restaurantId covers the panel order editor, whose rows carry the offer but not the
     * restaurant, since the whole order belongs to one.
     */
    protected function bogoFrozenUnitPrices($carts, ?int $restaurantId = null): array
    {
        $prices = [];

        $groups = collect($carts)
            ->filter(fn ($row) => data_get($row, 'bogo_group_id'))
            ->groupBy(fn ($row) => data_get($row, 'bogo_group_id'));

        foreach ($groups as $groupId => $rows) {
            $enrollment = BogoOfferRestaurant::where('bogo_offer_id', data_get($rows->first(), 'bogo_offer_id'))
                ->where('restaurant_id', data_get($rows->first(), 'restaurant_id') ?? $restaurantId)
                ->with('items')
                ->first();

            if (! $enrollment) {
                continue;
            }

            foreach ($enrollment->items as $item) {
                $price = (float) ($item->original_price ?? $item->price);

                // Keyed by what actually distinguishes one enrolled line from another: its side,
                // its food, the variation options it was frozen on and its add-ons. Side and
                // food alone collided whenever a bundle enrolled the same food twice at
                // different sizes - a Buy 2 Get 3 of one pizza in Small and Large priced every
                // buy line at whichever the loop wrote last, so the customer was billed 1501
                // twice for a 1169 + 1501 bundle, and all three free lines were booked at one
                // price too.
                $prices[$groupId][$this->bogoLineIdentity(
                    $item->type,
                    $item->food_id,
                    BogoOfferItem::resolveVariationOptionIds($item->food_id, $item->variations, $item->variation_options),
                    $item->add_on_ids
                )] = $price;

                // The old key is kept as a fallback for rows written before this change, which
                // carry no way to tell one same-food line from another.
                $prices[$groupId][$item->type.':'.$item->food_id] ??= $price;
            }
        }

        return $prices;
    }

    /**
     * What a bundle costs, and what a happy hour takes off it.
     *
     * A bundle is discounted by a happy hour and by nothing else - the bundle is already the
     * promotion, so the admin's standing restaurant discount would be discounting the same
     * thing twice. A happy hour is different in kind: the restaurant's own scheduled window,
     * covering the whole menu while it is open, and borne by the restaurant.
     *
     * Every surface reads this one method - the offer list, the offer details, the store page
     * and the cart - because a bundle that costs one thing on the offer screen and another in
     * the cart is the bug this exists to prevent.
     */
    protected function bogoBundlePricing(BogoOfferRestaurant $enrollment): array
    {
        $digits = config('round_up_to_digit', 2);
        $bundlePrice = round((float) $enrollment->bundle_price, $digits);
        $restaurant = $enrollment->restaurant;

        $happyHour = $restaurant ? Helpers::get_running_happy_hour($restaurant) : null;
        $discount = $happyHour ? Helpers::get_effective_restaurant_discount($restaurant) : null;

        // The amount comes from checkAdminDiscount() rather than arithmetic here, so the cap
        // and the minimum spend are applied the same way they are everywhere else.
        $discountAmount = $discount
            ? (float) Helpers::checkAdminDiscount(
                price: $bundlePrice,
                discount: $discount['discount'],
                max_discount: $discount['max_discount'],
                min_purchase: $discount['min_purchase'],
            )
            : 0.0;

        return [
            'bundle_price' => $bundlePrice,
            'original_price' => $this->bogoOriginalBundlePrice($enrollment),
            'discount_percentage' => $discount ? (float) $discount['discount'] : 0,
            'discount_amount' => round($discountAmount, $digits),
            // What the customer pays once the happy hour is taken off.
            'final_price' => round(max(0, $bundlePrice - $discountAmount), $digits),
            'is_happy_hour' => (bool) $happyHour,
            'happy_hour' => $happyHour ? [
                'id' => $happyHour->id,
                'slug' => $happyHour->slug,
                'title' => $happyHour->title,
                'discount' => (float) $happyHour->discount,
                'end_time' => $happyHour->end_time,
            ] : null,
        ];
    }

    /**
     * What the bundle would have cost without the offer: every item at what it was worth the
     * day the bundle was built, the free ones included.
     *
     * Deliberately never the live menu. original_price is written on every enrolled item and
     * backfilled for the rows that predate the column, so the only reason to fall back to
     * price is a row written by something that skipped the trait - and even then today's menu
     * would be the wrong answer, because the whole point of the frozen figure is that editing
     * a food does not move an offer the restaurant already agreed to.
     */
    protected function bogoOriginalBundlePrice(BogoOfferRestaurant $enrollment): float
    {
        $total = 0.0;

        foreach ($enrollment->items as $item) {
            $total += (float) ($item->original_price ?? $item->price) * (int) $item->quantity;
        }

        return round($total, config('round_up_to_digit', 2));
    }

    /**
     * How one cart line inside a bundle is priced: [unit price to charge, worth given away].
     *
     * A free line costs nothing and its worth is reported separately, because the restaurant
     * carries that cost and the payout has to book it. $fallback is what the caller worked
     * out from the menu, used only when the enrolment can no longer be found.
     *
     * The frozen price already includes the item's variation and add-on cost, so a caller
     * must clear the line's add-on total instead of charging it again on top.
     */
    protected function bogoLinePrice(array $frozenPrices, $cart, float $fallback): array
    {
        $isFree = (bool) data_get($cart, 'is_free_item');
        $type = $isFree ? 'get' : 'buy';
        $group = $frozenPrices[data_get($cart, 'bogo_group_id')] ?? [];

        $identity = $this->bogoLineIdentity(
            $type,
            data_get($cart, 'item_id'),
            data_get($cart, 'variation_options'),
            data_get($cart, 'add_on_ids')
        );

        // Falls back to the food-wide key for a row written before lines were keyed by their
        // variation, so an order already in flight prices exactly as it did when it was placed.
        $unit = $group[$identity] ?? $group[$type.':'.data_get($cart, 'item_id')] ?? $fallback;

        // Third element is the same worth per unit, for the order line to store: the Food
        // Report attributes discounts per food and multiplies by quantity itself, so it needs
        // the unit figure rather than the line total. Callers that only want a price keep
        // destructuring the first one or two.
        return $isFree
            ? [0.0, $unit * (int) data_get($cart, 'quantity', 1), $unit]
            : [$unit, 0.0, 0.0];
    }

    /**
     * What tells one line of a bundle from another.
     *
     * A bundle can enrol the same food more than once on the same side - the same pizza in two
     * sizes, or with different add-ons - and those lines have different prices. The food id
     * alone cannot tell them apart, so everything the enrolment froze goes into the key:
     * variation options and add-ons, both normalised to sorted integers so the enrolment and
     * the cart row that was written from it always produce the same string.
     */
    private function bogoLineIdentity(string $type, $foodId, $optionIds, $addOnIds): string
    {
        $normalise = function ($value): string {
            if (is_string($value)) {
                $value = json_decode($value, true);
            }

            if (! is_array($value)) {
                return '';
            }

            $ids = array_values(array_unique(array_map('intval', array_filter($value, 'is_numeric'))));
            sort($ids);

            return implode(',', $ids);
        };

        return $type.':'.(int) $foodId.':'.$normalise($optionIds).':'.$normalise($addOnIds);
    }

    /**
     * Whether a store wide discount may be taken off this line.
     *
     * A bundle is already the promotion, so the admin's standing restaurant discount is not
     * taken off it as well - that would discount the same thing twice and bill the admin for
     * a give-away the offer had already paid for. A happy hour is different in kind: it is the
     * restaurant's own scheduled window, it discounts the whole menu while it is open, and the
     * restaurant bears it, so it does reach a bundle.
     */
    protected function bogoLineIsDiscountable($detail, bool $isHappyHour): bool
    {
        // A campaign item is the restaurant's own promotion at its own price. No store wide rate
        // reaches it, so it takes no share of one - and it does not count towards the minimum
        // that unlocks one either, the same way a bundle does not. Say the word if a campaign
        // line should count towards a threshold it cannot benefit from.
        if (Helpers::is_campaign_item((array) $detail)) {
            return false;
        }

        return $isHappyHour || ! data_get($detail, 'bogo_group_id');
    }

    /**
     * What a store wide discount would come to on this cart, and what is still missing.
     *
     * The customer never learns a threshold exists until the bill arrives: a card advertises a
     * rate, the order applies it or silently does not, and nothing in between says why. This is
     * the figure a cart screen needs to say "add 120 more and this becomes 25% off" - and to
     * say it once, over the basket, rather than per line, because the threshold is a basket
     * rule.
     *
     * Worked out with the same helper the order uses, so what the cart promises is what the
     * checkout charges. source is null when the restaurant has no store wide rate running at
     * all, and the rest of the block is then zeroed rather than absent, so a client can read
     * the same keys either way.
     */
    protected function cartDiscountEligibility($userId, int $isGuest, $restaurant): array
    {
        $empty = [
            'source' => null,
            'percentage' => 0.0,
            'min_purchase' => 0.0,
            'max_discount' => null,
            'qualifying_amount' => 0.0,
            'shortfall' => 0.0,
            'is_qualified' => false,
            'discount_amount' => 0.0,
        ];

        if (! $restaurant) {
            return $empty;
        }

        $rate = Helpers::get_effective_restaurant_discount($restaurant);

        if (! isset($rate) || $rate['discount'] <= 0) {
            return $empty;
        }

        $rows = Cart::where('user_id', $userId)
            ->where('is_guest', $isGuest)
            ->where('restaurant_id', $restaurant->id)
            ->get();

        if ($rows->isEmpty()) {
            return $empty;
        }

        $is_happy_hour = (bool) Helpers::get_running_happy_hour($restaurant);

        // Cart rows keep their add-ons as ids rather than a total, so the total is resolved
        // here - once for the whole cart rather than per row.
        $addOnPrices = AddOn::whereIn('id', $rows->flatMap(fn ($r) => $this->cartRowAddOnIds($r))->unique()->all())
            ->pluck('price', 'id');

        // carts.price is whatever the client posted - add_to_cart stores $request->price
        // verbatim and never checks it - so some clients hold a unit price there and others
        // the line total. Multiplying it by quantity is right for one and doubles the other.
        //
        // Order placement does not read that column at all: it re-derives a per unit price
        // from the food plus its variation and multiplies by quantity itself. This does the
        // same, so the figure the cart promises is built exactly the way the bill is.
        $foods = Food::withoutGlobalScopes()
            ->whereIn('id', $rows->pluck('item_id')->filter()->unique()->all())
            ->get()->keyBy('id');

        $frozen = $this->bogoFrozenUnitPrices($rows, (int) $restaurant->id);

        $qualifying = 0.0;

        foreach ($rows as $row) {
            if (! $this->bogoLineIsDiscountable($row, $is_happy_hour)) {
                continue;
            }

            $food = $foods[$row->item_id] ?? null;

            if (! $food) {
                continue;
            }

            $unit = (float) $food->price;
            $variations = json_decode($food->variations, true) ?: [];

            if ($variations) {
                $chosen = is_array($row->variations) ? $row->variations : json_decode((string) $row->variations, true);
                $unit += (float) (Helpers::get_varient($variations, $chosen ?: [], $food->id)['price'] ?? 0);
            }

            // A bundle line is priced off the enrolment, never off the menu, and its add-ons
            // are already inside that frozen price - so it contributes the bundle's own worth
            // and nothing is added on top.
            if (data_get($row, 'bogo_group_id')) {
                [$unit] = $this->bogoLinePrice($frozen, $row, $unit);
                $qualifying += $unit * (int) $row->quantity;

                continue;
            }

            $qualifying += $unit * (int) $row->quantity;

            $ids = $this->cartRowAddOnIds($row);
            $qtys = $this->cartRowAddOnQtys($row);

            foreach ($ids as $index => $id) {
                $qualifying += (float) ($addOnPrices[$id] ?? 0) * (int) ($qtys[$index] ?? 1);
            }
        }

        $qualifying = round($qualifying, config('round_up_to_digit', 2));
        $minimum = (float) ($rate['min_purchase'] ?? 0);

        $amount = (float) Helpers::checkAdminDiscount(
            price: $qualifying,
            discount: $rate['discount'],
            max_discount: $rate['max_discount'],
            min_purchase: $minimum,
        );

        return [
            'source' => $is_happy_hour ? 'happy_hour' : 'restaurant_discount',
            'percentage' => (float) $rate['discount'],
            'min_purchase' => $minimum,
            'max_discount' => $rate['max_discount'] !== null ? (float) $rate['max_discount'] : null,
            // What the minimum is judged against: the food and its add-ons, and the bundle
            // lines only while a happy hour is running - exactly what the order will count.
            'qualifying_amount' => $qualifying,
            'shortfall' => round(max(0, $minimum - $qualifying), config('round_up_to_digit', 2)),
            'is_qualified' => $qualifying >= $minimum,
            'discount_amount' => round($amount, config('round_up_to_digit', 2)),
        ];
    }

    /** Cart rows store these as JSON text or as an array, depending on how they were written. */
    private function cartRowAddOnIds($row): array
    {
        $ids = is_string($row->add_on_ids) ? json_decode($row->add_on_ids, true) : $row->add_on_ids;

        return is_array($ids) ? array_values($ids) : [];
    }

    private function cartRowAddOnQtys($row): array
    {
        $qtys = is_string($row->add_on_qtys) ? json_decode($row->add_on_qtys, true) : $row->add_on_qtys;

        return is_array($qtys) ? array_values($qtys) : [];
    }

    /**
     * Write the store wide discount onto a set of order lines, and report what it came to.
     *
     * Four order paths need this and all four had their own copy: the customer API, the panel
     * and app order place, the POS, and the order editor. They were identical, which is the
     * problem - nothing failed loudly when one of them drifted, so a change to the minimum or
     * the cap had to be made four times and remembered four times. This is that rule, once.
     *
     * $restaurant_discount is the rate already resolved by the caller through
     * get_effective_restaurant_discount(), so a caller with no store wide rate in force simply
     * does not come here.
     *
     * The returned lines are the caller's to keep; the summary carries the order level figures
     * it still has to write onto the order itself, because the four differ in where they put
     * them.
     */
    protected function applyStoreWideDiscount(array $order_details, array $restaurant_discount, $restaurant): array
    {
        // A bundle is already the promotion, so only a happy hour discounts it. Outside a
        // window the standing discount is worked out on the rest of the order and the bundle
        // lines are left alone, matching what the offer card quotes. A cart with no bundle in
        // it - every POS order, most app orders - is unaffected.
        $is_happy_hour = (bool) Helpers::get_running_happy_hour($restaurant);
        // Add-ons are inside this figure, so they both count towards the minimum and are
        // discounted along with the food they were ordered with.
        $discountable_price = $this->bogoDiscountablePrice($order_details, $is_happy_hour);

        $amount = (float) Helpers::checkAdminDiscount(
            price: $discountable_price,
            discount: $restaurant_discount['discount'],
            max_discount: $restaurant_discount['max_discount'],
            min_purchase: $restaurant_discount['min_purchase'],
        );

        // A happy hour is the restaurant's own promotion, so it bears the cost; the admin's
        // standing restaurant discount stays reimbursed by the admin.
        $bearer = Helpers::discount_cost_bearer($restaurant);

        // What the campaign lines discount on their own, untouched by any of this.
        $campaign_discount = 0.0;

        foreach ($order_details as $key => $line) {
            // A campaign line is left exactly as the caller priced it. Falling through to the
            // branches below would zero the campaign's own discount - the else branch clears
            // every line a store wide rate does not reach, which is right for an ordinary item
            // whose own discount the rate has displaced, and wrong for one the rate never had
            // any claim on.
            if (Helpers::is_campaign_item($line)) {
                // Its own discount still has to come off the bill. The caller replaces its
                // running total with the figure returned below, so a campaign discount left out
                // of it would be advertised on the card and never charged - the customer would
                // see 20% off and pay full price. Per unit, like every other line here.
                $campaign_discount += (float) data_get($line, 'discount_on_food', 0)
                    * max(1, (int) data_get($line, 'quantity', 1));

                continue;
            }

            // The order wide figure is 0 when the basket missed the minimum, and then no line
            // gets a share of it either.
            if ($amount > 0 && $this->bogoLineIsDiscountable($line, $is_happy_hour)) {
                $order_details[$key]['discount_on_product_by'] = $bearer;
                $order_details[$key]['discount_type'] = 'percentage';
                $order_details[$key]['discount_percentage'] = $restaurant_discount['discount'];
                // The same call again with one unit's worth, so each line takes its share of
                // the order wide figure rather than being discounted on its own.
                //
                // Per unit, not per line: twenty seven report queries and export templates read
                // this column as SUM(discount_on_food * quantity), and an item's own discount
                // has always been written per unit. Writing the whole line here made every
                // store wide discount on a line of two or more report at multiples of itself.
                //
                // The line's whole worth divided by its quantity, not the bare unit price: the
                // add-ons belong to the line rather than to a unit, and leaving them out of
                // this share would discount them in the order total but nowhere on the lines.
                $units = max(1, (int) $line['quantity']);
                $order_details[$key]['discount_on_food'] = Helpers::checkAdminDiscount(
                    price: $discountable_price,
                    discount: $restaurant_discount['discount'],
                    max_discount: $restaurant_discount['max_discount'],
                    min_purchase: $restaurant_discount['min_purchase'],
                    item_wise_price: $this->bogoLineWorth($line) / $units,
                );
            } else {
                $order_details[$key]['discount_on_product_by'] = null;
                $order_details[$key]['discount_type'] = 'percentage';
                $order_details[$key]['discount_percentage'] = 0;
                $order_details[$key]['discount_on_food'] = 0;
            }
        }

        return [
            'order_details' => $order_details,
            // The order's product discount total: the store wide figure plus whatever the
            // campaign lines discount themselves. Same meaning the total carries when no store
            // wide rate runs at all, where it is the sum of the items' own discounts.
            'amount' => round($amount + $campaign_discount, config('round_up_to_digit', 2)),
            // The store wide part on its own, for a caller that has to attribute the cost.
            'store_wide_amount' => $amount,
            'campaign_discount' => round($campaign_discount, config('round_up_to_digit', 2)),
            'bearer' => $bearer,
            'happy_hour_id' => Helpers::running_happy_hour_id($restaurant),
            'is_happy_hour' => $is_happy_hour,
            // Also what the minimum was judged against, so a caller can explain a refusal.
            'discountable_price' => $discountable_price,
        ];
    }

    /**
     * The part of an order a store wide discount is worked out on: everything during a happy
     * hour, everything except the bundle lines otherwise - add-ons included, because a store
     * wide rate is a rate on what the customer spends at the restaurant and an add-on is money
     * spent there.
     *
     * The whole order during a happy hour, so nothing changes for the case that already
     * worked. Outside one, an order that is nothing but a bundle comes back 0 and
     * checkAdminDiscount() then returns 0 - no discount, which is the point.
     *
     * min_purchase is judged against this same figure rather than the order total, so the
     * admin's discount never sees the bundle at all: it neither counts towards qualifying nor
     * gets discounted. Say the word if a bundle should count towards the threshold it cannot
     * benefit from.
     */
    protected function bogoDiscountablePrice(array $orderDetails, bool $isHappyHour): float
    {
        $total = 0.0;

        foreach ($orderDetails as $detail) {
            if ($this->bogoLineIsDiscountable($detail, $isHappyHour)) {
                $total += $this->bogoLineWorth($detail);
            }
        }

        return round($total, config('round_up_to_digit', 2));
    }

    /**
     * One line's worth to a store wide rate: its food, times how many, plus its add-ons.
     *
     * total_add_on_price is already the line's add-on total rather than a per-unit figure, so
     * it is added once - the same way the order total treats it.
     */
    protected function bogoLineWorth($detail): float
    {
        return (float) data_get($detail, 'price', 0) * (int) data_get($detail, 'quantity', 0)
            + (float) data_get($detail, 'total_add_on_price', 0);
    }

    /**
     * Bundle groups in a cart, keyed by bogo_group_id, each with the enrolment it came from
     * and how many copies it holds. Used to price, validate and render the cart.
     */
    protected function bogoGroupsInCart($userId, int $isGuest, ?int $restaurantId = null): array
    {
        $rows = Cart::where('user_id', $userId)
            ->where('is_guest', $isGuest)
            ->whereNotNull('bogo_group_id')
            ->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId))
            ->get()
            ->groupBy('bogo_group_id');

        $groups = [];

        foreach ($rows as $groupId => $cartRows) {
            $offerId = $cartRows->first()->bogo_offer_id;
            $restaurant = $cartRows->first()->restaurant_id;

            $enrollment = BogoOfferRestaurant::where('bogo_offer_id', $offerId)
                ->where('restaurant_id', $restaurant)
                ->with(['items', 'restaurant', 'bogo_offer'])
                ->first();

            // How many copies of the bundle these rows represent. Derived from a buy line
            // rather than stored, so it cannot drift from the rows themselves.
            $quantity = 1;

            if ($enrollment) {
                $reference = $enrollment->items->firstWhere('type', 'buy') ?: $enrollment->items->first();
                $cartRow = $reference ? $cartRows->firstWhere('item_id', $reference->food_id) : null;

                if ($reference && $cartRow && $reference->quantity > 0) {
                    $quantity = max(1, (int) round($cartRow->quantity / $reference->quantity));
                }
            }

            $groups[$groupId] = [
                'group_id' => $groupId,
                'bogo_offer_id' => $offerId,
                'enrollment' => $enrollment,
                'quantity' => $quantity,
                'rows' => $cartRows,
            ];
        }

        return $groups;
    }

    /**
     * The same cart, collapsed to one entry per offer.
     *
     * Every add writes its own group, so one offer can sit in a cart several times over. The
     * rules that cap a bundle - the item stock behind it, the offer's total cap, the customer's
     * allowance - all apply to the offer as a whole, never to whichever group is being looked
     * at. Judged group by group, two adds of two each passed as "two" and put four in the cart.
     */
    protected function bogoOffersInCart($userId, int $isGuest, ?int $restaurantId = null): array
    {
        $offers = [];

        foreach ($this->bogoGroupsInCart($userId, $isGuest, $restaurantId) as $group) {
            $key = $group['bogo_offer_id'].':'.($group['enrollment']?->restaurant_id ?? 0);

            $offers[$key] ??= [
                'bogo_offer_id' => $group['bogo_offer_id'],
                'enrollment' => $group['enrollment'],
                'quantity' => 0,
                'group_ids' => [],
            ];

            $offers[$key]['quantity'] += $group['quantity'];
            $offers[$key]['group_ids'][] = $group['group_id'];
        }

        return array_values($offers);
    }

    /**
     * How many copies of one offer's bundle the cart already holds.
     *
     * $exceptGroupId leaves out the group being rewritten, so an update is judged on what the
     * cart would hold afterwards rather than on what it holds now plus itself.
     */
    protected function bogoBundlesInCart($userId, int $isGuest, $offerId, ?int $restaurantId = null, ?string $exceptGroupId = null): int
    {
        $total = 0;

        foreach ($this->bogoGroupsInCart($userId, $isGuest, $restaurantId) as $group) {
            if ((int) $group['bogo_offer_id'] !== (int) $offerId || $group['group_id'] === $exceptGroupId) {
                continue;
            }

            $total += $group['quantity'];
        }

        return $total;
    }
}
