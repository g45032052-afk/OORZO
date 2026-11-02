<?php

namespace App\Traits;

use App\CentralLogics\Helpers;
use App\Models\AddOn;
use App\Models\Admin;
use App\Models\BogoOffer;
use App\Models\BogoOfferItem;
use App\Models\BogoOfferRestaurant;
use App\Models\Food;
use App\Models\Restaurant;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;

/**
 * Enrolment rules shared by the admin panel, the vendor panel and the vendor app.
 *
 * All three build the same thing - a restaurant's buy/get selection for one offer - so
 * validation, the combination hash, the frozen item snapshot, the pricing, the joinable
 * guard and the admin notifications all live here rather than being written three times and
 * drifting apart. The only difference between the callers is who they enrol on behalf of and
 * what status the enrolment starts in.
 */
trait HandlesBogoEnrollment
{
    use HandlesPromotionEnrollment;
    // The item rule the customer endpoints actually apply, so customerVisibilityProblems()
    // can ask it rather than approximate it - see the comment there. No method of it clashes
    // with anything here; the two classes that already hold both traits compose them fine.
    use HandlesBogoCart;

    /**
     * Menu for the food picker, shaped for the select2 rows and the config modal.
     *
     * The translate global scope is left on deliberately. Removing it makes the name
     * accessor lazy-load every locale and return whichever row comes back first, which
     * surfaces Arabic names in an English panel.
     */
    protected function bogoRestaurantFoods(int $restaurantId, ?string $search = null): array
    {
        $foods = Food::with('category:id,name')
            ->where('restaurant_id', $restaurantId)
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->limit(50)
            ->get();

        $addOnIds = $foods->flatMap(fn ($food) => $this->decodeArray($food->add_ons) ?? [])->unique()->all();
        $addOns = AddOn::whereIn('id', $addOnIds)->get()->keyBy('id');

        return $foods->map(function ($food) use ($addOns) {
            $foodAddOnIds = $this->decodeArray($food->add_ons) ?? [];

            $discountAmount = $food->discount_type === 'percent'
                ? ((float) $food->price / 100) * (float) $food->discount
                : (float) $food->discount;

            return [
                'id' => $food->id,
                'name' => $food->name,
                // Second line of each row in the food picker.
                'category_name' => $food->category->name ?? '',
                'description' => $food->description,
                'image_full_url' => $food->image_full_url,
                'price' => (float) $food->price,
                'discounted_price' => max(0, (float) $food->price - $discountAmount),
                'discount' => (float) $food->discount,
                'discount_type' => $food->discount_type,
                'veg' => (int) $food->veg,
                'maximum_cart_quantity' => $food->maximum_cart_quantity,
                // [{name, type, min, max, required, values:[{label, optionPrice}]}]
                'variations' => $this->decodeArray($food->variations) ?? [],
                // Resolved to names and prices so the picker can label them properly.
                'add_ons' => collect($foodAddOnIds)
                    ->filter(fn ($id) => isset($addOns[$id]))
                    ->map(fn ($id) => [
                        'id' => $addOns[$id]->id,
                        'name' => $addOns[$id]->name,
                        'price' => $addOns[$id]->price,
                    ])->values(),
                'is_available' => (bool) $food->status,
            ];
        })->all();
    }

    /**
     * Buy quantities must total the offer's buy_qty and get quantities its get_qty. Per-item
     * quantity is additionally capped by the food's own maximum_cart_quantity.
     */
    protected function validateEnrollmentItems(BogoOffer $offer, array $buyItems, array $getItems, int $restaurantId): array
    {
        $errors = [];

        $buyTotal = collect($buyItems)->sum(fn ($item) => (int) ($item['quantity'] ?? 0));
        $getTotal = collect($getItems)->sum(fn ($item) => (int) ($item['quantity'] ?? 0));

        if ($buyTotal !== $offer->buy_qty) {
            $errors[] = ['code' => 'buy_items', 'message' => translate('messages.you_must_add_buy_foods_matching_the_offer_quantity')];
        }

        if ($getTotal !== $offer->get_qty) {
            $errors[] = ['code' => 'get_items', 'message' => translate('messages.you_must_add_get_foods_matching_the_offer_quantity')];
        }

        foreach ([$buyItems, $getItems] as $side) {
            // Each line is one unit and the same food may appear on several lines, so the
            // food's cart cap applies to the side's total for that food, not to one line.
            $quantityByFood = [];

            foreach ($side as $item) {
                $food = Food::find($item['food_id'] ?? null);

                if (!$food || $food->restaurant_id != $restaurantId) {
                    $errors[] = ['code' => 'food', 'message' => translate('messages.selected_food_does_not_belong_to_this_restaurant')];
                    continue;
                }

                $quantity = (int) ($item['quantity'] ?? 0);

                if ($quantity < 1) {
                    $errors[] = ['code' => 'food', 'message' => translate('messages.quantity_must_be_at_least_one')];
                    continue;
                }

                $quantityByFood[$food->id] = ($quantityByFood[$food->id] ?? 0) + $quantity;

                if ($food->maximum_cart_quantity && $quantityByFood[$food->id] > $food->maximum_cart_quantity) {
                    $errors[] = [
                        'code' => 'cart_item_limit',
                        'message' => $food->getRawOriginal('name').' : '.translate('messages.maximum_cart_quantity_exceeded'),
                    ];
                }
            }
        }

        return array_values(array_unique($errors, SORT_REGULAR));
    }

    /**
     * Whether a frozen selection can still be built from today's menu.
     *
     * A request is validated when it is submitted and approved some time later, and the menu
     * moves in between: the food is deleted, the Size option the restaurant picked is dropped,
     * the add-on is removed. Approving into that leaves a bundle that can never be assembled -
     * the customer sees a package whose parts no longer exist - so the two kinds of change are
     * separated here rather than treated alike:
     *
     *   blocking  - the selection cannot be rebuilt at all. Only editing the combination fixes
     *               it, so approval is refused.
     *   warnings  - the parts exist but are not sellable today (switched off, out of stock).
     *               That is a state the restaurant reverses, and the customer-side rules
     *               already hide the offer while it lasts, so approval goes through and the
     *               admin is told what is currently hidden.
     */
    protected function enrollmentIntegrityProblems(BogoOfferRestaurant $enrollment): array
    {
        $blocking = [];
        $warnings = [];

        foreach ($enrollment->items as $item) {
            $label = $item->food_name ?: translate('messages.this_food');
            // Primed by primeEnrollmentFoods() when a whole page of enrolments is being
            // asked at once; looked up on its own otherwise, which is what the drawers do.
            $food = $item->relationLoaded('food')
                ? $item->food
                : Food::withoutGlobalScope(\App\Scopes\RestaurantScope::class)->find($item->food_id);

            if (! $food || $food->restaurant_id != $enrollment->restaurant_id) {
                $blocking[] = $label.' : '.translate('messages.this_food_no_longer_exists_on_the_restaurants_menu');
                continue;
            }

            // Frozen variations name the option by label, so a renamed or deleted option is
            // the same failure: nothing on the food matches what was chosen.
            $chosen = is_array($item->variations) ? $item->variations : json_decode((string) $item->variations, true);
            $available = is_array($food->variations) ? $food->variations : json_decode((string) $food->variations, true);

            foreach ($chosen ?? [] as $variation) {
                $group = collect($available ?? [])->firstWhere('name', $variation['name'] ?? null);
                $labels = collect($group['values'] ?? [])->pluck('label')->all();

                foreach ($variation['values']['label'] ?? [] as $option) {
                    if (! in_array($option, $labels, true)) {
                        $blocking[] = $label.' : '.translate('messages.the_selected_variation_is_no_longer_available')
                            .' ('.($variation['name'] ?? '').' - '.$option.')';
                    }
                }
            }

            $addOnIds = is_array($item->add_on_ids) ? $item->add_on_ids : json_decode((string) $item->add_on_ids, true);

            if (! empty($addOnIds)) {
                $alive = AddOn::withoutGlobalScope(\App\Scopes\RestaurantScope::class)
                    ->whereIn('id', $addOnIds)->pluck('id')->all();

                if (count($alive) !== count(array_unique($addOnIds))) {
                    $blocking[] = $label.' : '.translate('messages.a_selected_add_on_has_been_deleted_from_this_food',
                        $this->promotionLabel('bogo'));
                }
            }

            if (! $food->status) {
                $warnings[] = $label.' : '.translate('messages.this_food_is_currently_switched_off');
            } elseif (! $this->foodHasStock($food, $item->quantity)) {
                $warnings[] = $label.' : '.translate('messages.this_food_is_currently_out_of_stock');
            }

            // A switched-off CATEGORY takes the item off every customer surface while its own
            // switch stays on and its stock stays full, so the vendor reads the item as fine
            // and cannot see what is wrong with it. The customer-side rule that hides the
            // bundle - Food::active() - can only answer "<food> is currently unavailable",
            // which sends them to look at the item. Named here, where the panel is explaining
            // itself and may say what the customer messages must not.
            if ($food->status && ! $this->foodCategoryIsListable($food)) {
                $warnings[] = $label.' : '.translate(
                    'messages.the_category_of_this_item_is_switched_off_so_customers_can_not_see_it',
                    ['category' => $food->category?->name ?? translate('messages.this_food')]
                );
            }
        }

        return ['blocking' => array_values(array_unique($blocking)), 'warnings' => array_values(array_unique($warnings))];
    }

    /**
     * Why an approved bundle is not on the customer's screen right now, or an empty list when
     * it is.
     *
     * "Approved" is the admin's answer to the restaurant, not the customer's view of the
     * offer, and the two came apart often enough that both panels showed a bundle as running
     * while nobody could order it. Every condition the customer-side query applies is asked
     * again here, in the same order the customer query applies it, so the panel can name the
     * one that is actually in the way:
     *
     *   BogoOfferController::availableOffers()   - active, running, not exhausted
     *   BogoOfferController::atServableStore()   - store switched on, open now
     *   BogoOfferController::orderableEnrollments() - every item sellable, order type served
     *
     * Zone is the one rule not reproduced: it is asked against the customer's own zones, so
     * there is no single answer to give a panel.
     *
     * @return string[] plain sentences, most decisive first
     */
    public function customerVisibilityProblems(BogoOfferRestaurant $enrollment, ?BogoOffer $offer = null): array
    {
        $offer ??= $enrollment->bogo_offer;
        $restaurant = $enrollment->restaurant;
        $reasons = [];

        // A single enrolment asked on its own - the drawers - primes itself, so the item
        // rules below read one batch of foods rather than one query per item. A page that
        // primed the whole listing first has already done this and is left alone.
        if ($enrollment->items->contains(fn ($item) => ! $item->relationLoaded('food'))) {
            $this->primeEnrollmentFoods([$enrollment]);
        }

        // Every sentence names the promotion it is about rather than saying "the offer" or
        // "the bundle": the vendor is reading it beside a Happy Hour that words the same
        // conditions its own way, and the two must not be told apart by guesswork.
        $label = $this->promotionLabel('bogo');

        if (! $offer) {
            return [translate('messages.This_offer_no_longer_exists.', $label)];
        }

        if (! $offer->status) {
            $reasons[] = translate('messages.The_offer_is_switched_off.', $label);
        }

        // Each of these carries the figure that settles it - the schedule the offer is
        // waiting on, or the redemptions it has spent - so the reader is not sent back to the
        // offer form to find out how far off it is.
        if ($offer->start_date && $offer->start_date->isFuture()) {
            $reasons[] = translate('messages.The_offer_has_not_started_yet.', $label)
                .' ('.translate('messages.Starts').' '.$offer->start_date->format('d M, Y h:i A').')';
        }

        if ($offer->end_date && $offer->end_date->isPast()) {
            $reasons[] = translate('messages.The_offer_has_ended.', $label)
                .' ('.translate('messages.Ended').' '.$offer->end_date->format('d M, Y h:i A').')';
        }

        // The total cap removes the offer outright, unlike the per-customer cap which leaves
        // it on screen and only refuses the add.
        if ($offer->usage_limit_total && (int) $offer->total_uses >= (int) $offer->usage_limit_total) {
            $reasons[] = translate('messages.The_total_usage_limit_has_been_reached.', $label)
                .' ('.$offer->total_uses.'/'.$offer->usage_limit_total.' '.translate('messages.orders_used').')';
        }

        if (! $restaurant) {
            $reasons[] = translate('messages.The_restaurant_no_longer_exists.');

            return $reasons;
        }

        // A closed store hides a BOGO offer outright - availableOffers() filters on openNow() -
        // which is not true of every promotion, so the store side is asked with that named.
        $reasons = array_merge($reasons, $this->promotionStoreProblems($restaurant, hiddenWhenClosed: true));

        if (! $this->servesAnyOrderType($restaurant, $offer->order_types ?? [])) {
            $reasons[] = translate('messages.The_restaurant_does_not_offer_any_of_this_offers_order_types.', $label);
        }

        $problems = $this->enrollmentIntegrityProblems($enrollment);
        $reasons = array_merge($reasons, $problems['blocking'], $problems['warnings']);

        // The integrity check reads a food's own status and stock, which is not the whole of
        // what the customer endpoints ask: they resolve every item through Food::active() and
        // the bundle's serving hours, variation options and add-ons as well. A food in a
        // switched-off CATEGORY, one outside its serving window, or a sold-out Large all keep
        // status 1 and stock, so the panel called the bundle live while the API hid it. Ask
        // the customer rule itself for anything the item loop above did not already name -
        // it stops at the first item it finds, so it is a fallback rather than a second list.
        if (! $problems['blocking'] && ! $problems['warnings']
            && ($itemReason = $this->bogoItemsUnavailableReason($enrollment, 1))) {
            $reasons[] = $itemReason;
        }

        return $reasons;
    }

    /**
     * What a customer would see of this enrolment right now: one status word, a label for it
     * and the sentences behind it.
     *
     * The panels ask customerVisibilityProblems() and draw their own badge from whether the
     * list came back empty. The app cannot - it has no offer form beside it to read the dates
     * off - so the same judgement is made here once and handed over decided, which also stops
     * the two surfaces from disagreeing about what "running" means.
     *
     * @return array{status: string, label: string|null, reasons: string[]}
     */
    public function bogoCustomerVisibility(?BogoOfferRestaurant $enrollment, ?BogoOffer $offer = null): array
    {
        $offer ??= $enrollment?->bogo_offer;

        // Not approved: the enrolment state already says what is happening - waiting on the
        // admin, waiting on the restaurant, denied - and none of it is about customers.
        if (! $enrollment || $enrollment->status !== 'approved' || ! $offer) {
            return $this->promotionVisibility('not_applicable');
        }

        $reasons = $this->customerVisibilityProblems($enrollment, $offer);

        // Ended and not-yet-started are read off the offer rather than off the reason list:
        // both are on it, but they are the promotion's own schedule doing what it was set up
        // to do, and calling either a fault would have the vendor hunting a problem that is
        // not there. The sentences still travel, so the app can say when.
        $status = match (true) {
            (bool) ($offer->end_date && $offer->end_date->isPast()) => 'ended',
            (bool) ($offer->start_date && $offer->start_date->isFuture()) => 'scheduled',
            (bool) $reasons => 'not_visible',
            default => 'running',
        };

        return $this->promotionVisibility($status, $reasons);
    }

    /**
     * The same question for a page of enrolments, keyed by enrolment id.
     *
     * The list screens ask it of every approved row, and every caller has the offer and the
     * restaurant in hand already - so both are set on the row before it is asked, and the
     * enrolled foods are resolved for the whole page in one query. Left to itself each row
     * would refetch the offer, the restaurant and a food per item.
     *
     * @return array<int, string[]>
     */
    public function customerVisibilityProblemsFor($enrollments): array
    {
        $enrollments = collect($enrollments)->filter()->values();

        if ($enrollments->isEmpty()) {
            return [];
        }

        $this->primeEnrollmentFoods($enrollments);

        return $enrollments->mapWithKeys(fn ($enrollment) => [
            $enrollment->id => $this->customerVisibilityProblems($enrollment),
        ])->all();
    }

    /**
     * Resolve the foods behind these enrolments once and hang each on its own item.
     *
     * Read without the restaurant and zone scopes on purpose: the question is whether the
     * enrolled food exists at all, and a scope narrowed to the panel's own restaurant or zone
     * would answer "deleted" for a food that is simply somebody else's. The checks that
     * follow compare restaurant_id themselves.
     */
    protected function primeEnrollmentFoods($enrollments): void
    {
        $items = collect($enrollments)->flatMap(fn ($enrollment) => $enrollment->items);
        $ids = $items->pluck('food_id')->filter()->unique()->values()->all();

        $foods = $ids
            ? Food::withoutGlobalScopes([\App\Scopes\RestaurantScope::class, \App\Scopes\ZoneScope::class])
                // The variations accessor builds its JSON from these two when the column is
                // empty, so reading it lazily costs two queries per food - and every check
                // below reads it. The category comes along for the listable question, which
                // reads the parent's switch when the food sits in a sub-category.
                ->with(['newVariations', 'newVariationOptions', 'category.parent'])
                ->whereIn('id', $ids)->get()->keyBy('id')
            : collect();

        foreach ($items as $item) {
            // Null is an answer too - the item loops read it as a deleted food - so the
            // relation is set either way rather than left unloaded.
            $item->setRelation('food', $foods->get($item->food_id));
        }

        // The customer rule's own memo of which foods are listable, for the same batch.
        $this->bogoPrimeActiveFoods($ids);
    }

    /**
     * Mirrors the customer-side stock rule: an unlimited food always has stock, and a stocked
     * one has whatever its remaining stock is.
     *
     * item_stock is an accessor that has already taken sell_count off, so subtracting it here
     * as well would count every sale twice and call a stocked food empty long before it was.
     */
    /**
     * Whether the food's category leaves it listable, which is the half of Food::active()
     * that says nothing about the food itself.
     *
     * Mirrors that scope exactly, including its asymmetry: a top-level category must be
     * switched on, while a sub-category is judged by its PARENT's switch and not by its own.
     */
    private function foodCategoryIsListable($food): bool
    {
        $category = $food->category;

        if (! $category) {
            return false;
        }

        return (int) $category->parent_id === 0
            ? (int) $category->status === 1
            : (int) ($category->parent->status ?? 0) === 1;
    }

    private function foodHasStock(Food $food, int $quantity): bool
    {
        if (! $food->stock_type || $food->stock_type === 'unlimited') {
            return true;
        }

        return (int) $food->item_stock >= max(1, $quantity);
    }

    /**
     * A restaurant may reuse the same food across offers, but not the same buy+get
     * combination. Hashing a canonical form makes that a single indexed lookup.
     */
    protected function buildCombinationSignature(array $buyItems, array $getItems): string
    {
        // Identical lines are folded together before hashing, so the same bundle hashes the
        // same however the client chose to split it: two lines of one pizza and one line of
        // two are the same thing to a customer, and used to produce two different signatures -
        // which let the same combination be submitted twice under a different shape.
        $normalise = function (array $items, string $type) {
            return collect($items)
                ->map(fn ($item) => [
                    'type' => $type,
                    'food_id' => (int) ($item['food_id'] ?? 0),
                    'quantity' => (int) ($item['quantity'] ?? 1),
                    'variations' => $this->canonicalise($item['variations'] ?? null),
                    'add_ons' => $this->canonicalise($item['add_on_ids'] ?? null),
                ])
                ->groupBy(fn ($item) => json_encode(Arr::except($item, 'quantity')))
                ->map(fn ($group) => array_merge($group->first(), ['quantity' => $group->sum('quantity')]))
                ->map(fn ($item) => json_encode($item))
                ->sort()->values()->all();
        };

        $payload = array_merge($normalise($buyItems, 'buy'), $normalise($getItems, 'get'));

        return hash('sha256', json_encode($payload));
    }

    /**
     * Sorts recursively so key or selection order cannot produce a different hash for the
     * same combination.
     */
    protected function canonicalise($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        if (!is_array($value)) {
            return $value;
        }

        $value = array_map(fn ($item) => $this->canonicalise($item), $value);

        if (array_is_list($value)) {
            sort($value);
        } else {
            ksort($value);
        }

        return $value;
    }

    /**
     * Scoped to offers that have not ended, so an expired offer's combination becomes
     * reusable rather than being burned forever.
     */
    protected function combinationTaken(int $restaurantId, string $signature, ?int $ignoreEnrollmentId = null): bool
    {
        return BogoOfferRestaurant::where('restaurant_id', $restaurantId)
            ->where('combination_signature', $signature)
            ->when($ignoreEnrollmentId, fn ($q) => $q->where('id', '!=', $ignoreEnrollmentId))
            ->whereHas('bogo_offer', fn ($q) => $q->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            }))
            ->exists();
    }

    /**
     * Freezes a copy of every selected item so later edits to the food cannot move the
     * offer, and returns the bundle price. Buy items carry their variation and add-on cost;
     * get items are fully free, add-ons included.
     */
    protected function syncEnrollmentItems(BogoOfferRestaurant $enrollment, array $buyItems, array $getItems): float
    {
        $enrollment->items()->delete();
        $bundlePrice = 0.0;

        foreach ([['buy', $buyItems], ['get', $getItems]] as [$type, $items]) {
            foreach ($items as $item) {
                $food = Food::find($item['food_id']);
                $quantity = (int) ($item['quantity'] ?? 1);
                $variations = $this->decodeArray($item['variations'] ?? null);
                $addOnIds = $this->decodeArray($item['add_on_ids'] ?? null);
                $addOnQtys = $this->decodeArray($item['add_on_qtys'] ?? null);

                // Worth is recorded whatever the type. price is what the line costs inside
                // the bundle, so a get row is 0; original_price is what the item was worth
                // the day the bundle was built. Without the second figure a free item could
                // only be valued from the live menu, which put the same food on screen at two
                // different prices once its menu entry was edited.
                $worth = $this->resolveUnitPrice($food, $variations, $addOnIds, $addOnQtys);
                $unitPrice = $type === 'buy' ? $worth : 0.0;

                BogoOfferItem::create([
                    'bogo_offer_restaurant_id' => $enrollment->id,
                    'food_id' => $food->id,
                    'type' => $type,
                    'quantity' => $quantity,
                    // The default-language name, not the caller's current locale - this copy
                    // has to outlive edits and deletion of the food.
                    'food_name' => $food->getRawOriginal('name'),
                    'food_image' => $food->image,
                    'price' => $unitPrice,
                    'original_price' => $worth,
                    'variations' => $variations,
                    // The picker posts a selection by group name and option label, the way the
                    // cart does, so the option ids have to be looked up here - stock lives on
                    // variation_options and is addressed by id. Left unresolved, this column
                    // stayed null on every enrolment and option stock was never checked before
                    // a bundle was advertised, nor consumed when one was ordered.
                    'variation_options' => BogoOfferItem::resolveVariationOptionIds(
                        $food->id, $variations, $this->decodeArray($item['variation_options'] ?? null)
                    ),
                    'add_on_ids' => $addOnIds,
                    'add_on_qtys' => $addOnQtys,
                ]);

                $bundlePrice += $unitPrice * $quantity;
            }
        }

        return round($bundlePrice, config('round_up_to_digit', 2));
    }

    protected function resolveUnitPrice(Food $food, ?array $variations, ?array $addOnIds, ?array $addOnQtys): float
    {
        $price = (float) $food->price;

        if (!empty($variations)) {
            $foodVariations = is_array($food->variations) ? $food->variations : json_decode($food->variations, true);
            $price += (float) Helpers::variation_price($foodVariations ?? [], $variations);
        }

        if (!empty($addOnIds)) {
            $quantities = array_combine($addOnIds, $addOnQtys ?: array_fill(0, count($addOnIds), 1));

            foreach (AddOn::whereIn('id', $addOnIds)->get() as $addOn) {
                $price += (float) $addOn->price * (int) ($quantities[$addOn->id] ?? 1);
            }
        }

        return $price;
    }

    protected function decodeArray($value): ?array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        return null;
    }

    /**
     * Why this restaurant cannot join the offer, or null when it can: the offer must be
     * running and the restaurant must serve at least one of its order types.
     */
    protected function bogoJoinBlockedReason(BogoOffer $offer, int $restaurantId): ?array
    {
        if (! $offer->status) {
            return ['code' => 'offer', 'message' => translate('messages.this_offer_is_not_running')];
        }

        if ($offer->end_date && $offer->end_date->isPast()) {
            return ['code' => 'offer', 'message' => translate('messages.this_offer_has_already_ended')];
        }

        $restaurant = Restaurant::with('restaurant_config')->find($restaurantId);

        if (\App\Models\BogoOffer::orderTypesEnabled() && ! $this->servesAnyOrderType($restaurant, $offer->order_types ?? [])) {
            return ['code' => 'order_type', 'message' => translate('messages.your_restaurant_does_not_support_the_order_types_of_this_offer')];
        }

        return null;
    }

    protected function bogoOfferHasEnded(?BogoOffer $offer): bool
    {
        return (bool) ($offer?->end_date && $offer->end_date->isPast());
    }

    /**
     * A denial the ADMIN issued, as against one the restaurant issued itself.
     *
     * The two land in the same state and are answered differently. The admin's refusal is a
     * record the restaurant may not erase - it reworks the selection and asks again - while a
     * restaurant that declined an admin's invitation is refusing something offered to it, and
     * may drop the row entirely rather than carry an invitation it does not want.
     */
    protected function bogoDeniedByAdmin($enrollment): bool
    {
        return $enrollment
            && $this->enrollmentState($enrollment) === 'rejected'
            && $enrollment->rejected_by === 'admin';
    }

    /**
     * Why this enrolment may not be cancelled or left, or null when it may.
     *
     * Two records are closed rather than deleted, and both stay on the list to be read:
     *
     *   an admin's denial - the restaurant answers it by reworking the selection, which is the
     *                one thing it may still do. Cancelling it instead would throw away the
     *                refusal and its reason, and let the same request be raised again as
     *                though it had never been turned down. A denial the restaurant issued
     *                itself is not this: it may cancel that and be rid of the invitation.
     *   an ended offer - there is nothing left to join, leave or answer. Every write endpoint
     *                already refuses it; without this the buttons stayed live and could only
     *                ever collect an error.
     *
     * BOGO's rule alone, deliberately. Happy Hour has no selection to rework, so a denied
     * restaurant there cancels and joins again - locking the cancel would leave it unable to
     * ever join that happy hour, which is why this does not live in the shared trait.
     */
    protected function bogoEnrollmentLockedReason($enrollment, ?BogoOffer $offer = null): ?string
    {
        $offer ??= $enrollment?->bogo_offer;

        if ($this->bogoOfferHasEnded($offer)) {
            return translate('messages.this_offer_has_already_ended');
        }

        if ($this->bogoDeniedByAdmin($enrollment)) {
            return translate('messages.a_denied_request_can_only_be_reworked_and_resubmitted');
        }

        return null;
    }

    /**
     * The shared delete guard plus the closed-record rule above, which is the pair every
     * caller wants - the panel's Leave and Cancel Request and the app's leave endpoint.
     */
    protected function bogoDeleteBlockedReason($enrollment, ?BogoOffer $offer = null): ?string
    {
        // Closed-record first. The shared guard sends an unanswered admin request off to be
        // answered instead of deleted, which is right until the offer ends - after that there
        // is nothing to answer either, and pointing the vendor at a decision they can no
        // longer make is worse than saying plainly that the offer is over.
        return $this->bogoEnrollmentLockedReason($enrollment, $offer)
            ?? $this->restaurantDeleteBlockedReason($enrollment);
    }

    /**
     * Answering an admin's invitation, which an ended offer no longer has anything to give.
     */
    protected function bogoRespondBlockedReason($enrollment, ?BogoOffer $offer, string $notPendingMessage): ?string
    {
        return $this->respondBlockedReason($enrollment, $notPendingMessage)
            ?? ($this->bogoOfferHasEnded($offer) ? translate('messages.this_offer_has_already_ended') : null);
    }

    /**
     * What the restaurant may do, with the closed-record rule applied.
     *
     * Rework survives a denial - it is how a restaurant answers one - and is already off for
     * an ended offer, since it runs through the same joinable guard that refuses one. Nothing
     * else survives either.
     */
    protected function bogoEnrollmentActions($enrollment, ?BogoOffer $offer, bool $isEligible): array
    {
        $actions = $this->enrollmentActions($enrollment, $isEligible);
        $ended = $this->bogoOfferHasEnded($offer);

        return array_merge($actions, [
            'can_respond' => $actions['can_respond'] && ! $ended,
            // Cancelling survives the restaurant's own refusal but not the admin's.
            'can_cancel' => $actions['can_cancel'] && ! $ended && ! $this->bogoDeniedByAdmin($enrollment),
            'can_leave' => $actions['can_leave'] && ! $ended,
        ]);
    }

    /**
     * Kept as the enrolment side's way of asking, but the rule itself lives on the restaurant
     * now - the cart needs the same answer and does not use this trait, and one match table in
     * two places is one that drifts.
     */
    public function servesAnyOrderType(?Restaurant $restaurant, array $orderTypes): bool
    {
        // Nothing is ineligible on order types while the feature is off. Also what decides the
        // "not eligible" badge on the vendor's offer list, which would otherwise sit on offers
        // for a reason the panel no longer shows.
        if (! \App\Models\BogoOffer::orderTypesEnabled()) {
            return (bool) $restaurant;
        }

        return $restaurant ? $restaurant->servesAnyOrderType($orderTypes) : false;
    }

    protected function errorResponse(string $code, string $message)
    {
        return response()->json(['errors' => [['code' => $code, 'message' => $message]]], 403);
    }

    /**
     * Mirrors the campaign join flow: the admin is told a restaurant is waiting, and the
     * restaurant gets its own copy for the record.
     */
    protected function notifyAdminOfBogoJoinRequest(BogoOffer $offer, int $restaurantId): void
    {
        try {
            $restaurant = Restaurant::with('vendor')->find($restaurantId);
            $admin = Admin::where('role_id', 1)->first();

            $adminStatus = Helpers::getNotificationStatusData('admin', 'bogo_join_request');

            if ($adminStatus?->mail_status == 'active' && config('mail.status')
                && Helpers::get_mail_status('bogo_request_mail_status_admin') == '1' && $admin) {
                Mail::to($admin->getRawOriginal('email'))->send(new \App\Mail\BogoRequestMail($restaurant?->name));
            }

            $vendorStatus = Helpers::getNotificationStatusData('restaurant', 'restaurant_bogo_join_request');
            $restaurantStatus = Helpers::getRestaurantNotificationStatusData($restaurantId, 'restaurant_bogo_join_request');

            if ($vendorStatus?->mail_status == 'active' && $restaurantStatus?->mail_status == 'active'
                && config('mail.status') && $restaurant?->vendor) {
                Mail::to($restaurant->vendor->getRawOriginal('email'))
                    ->send(new \App\Mail\VendorBogoRequestMail($restaurant->name, 'pending'));
            }
        } catch (\Exception $e) {
            // Never let a mail failure roll back a saved enrolment.
            info(['bogo_join_notify' => $e->getMessage()]);
        }
    }

    /**
     * The admin invited this restaurant and it said no, so the admin needs to know - the
     * enrolment just sits rejected otherwise.
     */
    protected function notifyAdminOfBogoDenial(int $restaurantId): void
    {
        $this->notifyAdminOfBogoDecision($restaurantId, 'denied');
    }

    /**
     * The other half of the same answer. An assignment the restaurant accepts goes live
     * immediately, so the admin hears about the yes for the same reason it hears about the
     * no - only the no was ever sent, which made silence mean either "not answered yet" or
     * "running since Tuesday".
     */
    protected function notifyAdminOfBogoAcceptance(int $restaurantId): void
    {
        $this->notifyAdminOfBogoDecision($restaurantId, 'accepted');
    }

    private function notifyAdminOfBogoDecision(int $restaurantId, string $decision): void
    {
        try {
            $restaurant = Restaurant::find($restaurantId);
            $admin = Admin::where('role_id', 1)->first();

            // The mail flag name is not free: the settings screen derives it from the tab slug
            // as <tab with dashes as underscores>_mail_status_<type>, so bogo-approve/admin
            // can only ever read bogo_approve_mail_status_admin.
            [$notificationKey, $mailKey] = $decision === 'denied'
                ? ['bogo_join_denied', 'bogo_deny_mail_status_admin']
                : ['bogo_join_accepted', 'bogo_approve_mail_status_admin'];

            $status = Helpers::getNotificationStatusData('admin', $notificationKey);

            if ($status?->mail_status == 'active' && config('mail.status')
                && Helpers::get_mail_status($mailKey) == '1' && $admin) {
                Mail::to($admin->getRawOriginal('email'))
                    ->send(new \App\Mail\BogoRequestMail($restaurant?->name, $decision));
            }
        } catch (\Exception $e) {
            info(['bogo_decision_notify' => $e->getMessage()]);
        }
    }
}
