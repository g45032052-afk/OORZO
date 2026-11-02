<?php

namespace Modules\AI\app\Agents\Tools;

use Modules\AI\app\Agents\AiResponseContext;
use Modules\AI\app\Support\FoodAvailability;
use Modules\AI\app\Support\FoodPricing;
use App\CentralLogics\PersonalizationService;
use App\CentralLogics\Helpers;
use App\Models\Food;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetBestDealsTool implements Tool
{
    public function __construct(
        private readonly AiResponseContext $context,
        private readonly array $zoneIds = [],
        private readonly ?User $user     = null,
    ) {}

    public function description(): string
    {
        return 'Get foods with the best discounts and current offers. Use for "deals", "offers", "cheap options", or "what\'s on sale" queries.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'veg_only' => $schema->boolean()->description('true for veg items only, false for non-veg, null for all')->required()->nullable(),
            'limit'    => $schema->number()->description('Number of deals to return, default 8, or null for default')->required()->nullable(),
        ];
    }

    public function handle(Request $request): string
    {
        $args    = $request->all();
        $limit   = min((int) ($args['limit'] ?? 8), 10);
        $vegOnly = ($args['veg_only'] ?? null) !== null ? (bool) $args['veg_only'] : null;

        // Restaurants running a store wide rate right now - a happy hour, or the admin's
        // standing restaurant discount. No longer used to widen the search: those items are
        // the restaurant's offer rather than their own, and the filter below leaves them out.
        // Kept because the rate is still read further down when a card is priced.
        $storeWide    = $this->storeWideRates();

        $query = Food::active()
            // The item's own rate only. A store wide discount belongs to the restaurant and a
            // happy hour to the window, and both override the item's own while they run, so an
            // item under either is not selling at the rate this answer would be quoting.
            ->itemDiscounted($this->zoneIds ?: [])
            ->with([
                'restaurant:id,name,logo,rating,delivery_time,minimum_order,free_delivery,zone_id,active',
                'restaurant.discount',
                'category:id,name',
                'newVariations', 'newVariationOptions',
            ])
            ->when(!empty($this->zoneIds), fn ($q) => $q->whereHas('restaurant', fn ($s) => $s->whereIn('zone_id', $this->zoneIds)))
            ->when($vegOnly !== null, fn ($q) => $q->type($vegOnly ? 'veg' : 'non_veg'))
            // Rank by the SAVING, normalised to a percentage. Ordering by the raw
            // `discount` column compares flat amounts against percentages — a
            // "655 off" item outranks a genuine "35% off" one purely because 655
            // is the bigger number.
            ->orderByRaw("CASE WHEN discount_type = 'percent' THEN discount ELSE (discount / NULLIF(price, 0)) * 100 END DESC");

        // Storefront-parity: the Discounted filter personalizes toward the
        // customer's preferences as the primary sort (saving stays the tiebreaker);
        // a no-op for guests. Guests keep the campaign-accurate saving-first order
        // via bestSavingFirst() below.
        $query = PersonalizationService::applyItemPersonalization($query, $this->user?->getKey());

        // Fetch a wider pool than we return, then cut AFTER ranking.
        //
        // The SQL ordering can only sort by the item's own discount column, and an item
        // discounted by a store wide rate has 0 there - so it sorts last and the limit
        // slices it off before anyone sees it. Ranking by the real saving is only possible
        // in PHP, because that saving comes from the effective rate. Bounded so a wide
        // fetch cannot turn into a scan.
        $pool = min($limit * 4, 40);

        $items = $query
            ->limit($pool)
            ->get([
                'id', 'name', 'price', 'discount', 'discount_type',
                'avg_rating', 'rating_count', 'order_count', 'image',
                'restaurant_id', 'category_id', 'veg', 'recommended', 'item_stock',
                'stock_type', 'sell_count', 'available_time_starts', 'available_time_ends',
                'variations', 'choice_options',
            ]);

        $items = $items->unique(fn ($i) =>
            (int) $i->getAttribute('restaurant_id') . '|' . mb_strtolower(trim((string) $i->getAttribute('name')))
        )->values();

        $products = $items->map($this->format(...))->values()->all();

        // An item discounted only by a store wide rate reports discount 0, deliberately:
        // product_discount_calculate_data() returns 0 whenever a store wide rate is live,
        // because the saving is taken off the cart subtotal under the store's own banner
        // rather than off the item. Quoting a per-item "now X" here would invent a number
        // the checkout never uses - a store wide rate carries a minimum spend and a cap,
        // so the real saving depends on the whole basket. So the rate is NAMED instead.
        foreach ($products as &$product) {
            $rate = $storeWide[$product['store_id'] ?? 0] ?? null;

            if ($rate && (float) $product['discount'] === 0.0) {
                $product['store_wide'] = $rate;
                $product['discount_label'] = $rate['percent'].'% off '
                    .($rate['is_happy_hour'] ? 'everything during happy hour' : 'the whole menu');
            }
        }
        unset($product);
        // For guests, order by the campaign-accurate saving. For a personalized
        // (logged-in) request, keep the personalized order from the query instead
        // of overriding it with saving-first.
        // Ranked by the saving the customer actually sees, for everyone. Personalization
        // has already chosen WHICH items are in the pool; ordering that pool by a discount
        // column that reads 0 for every store wide item would bury exactly the deals this
        // widening was meant to surface.
        $products = $this->bestSavingFirst($products);
        $products = $this->availableFirst($products);
        // Now that the real saving has decided the order, cut to what was asked for.
        $products = array_slice($products, 0, $limit);
        $this->context->recordTool('GetBestDealsTool');
        // Only orderable items on the suggestion carousel; unavailable ones still
        // appear tagged in the text below so questions can be answered.
        $this->context->addProducts(array_values(array_filter($products, static fn (array $p) => $p['is_available'])));

        if (count($products) === 0) {
            return 'No discounted items found right now.';
        }

        $lines = implode('; ', array_map(function (array $p): string {
            $base = isset($p['store_wide'])
                // No "now X": the saving lands on the cart, not on this line.
                ? $p['name'].' [ID:'.$p['id'].'] '.$p['price'].' — '.$p['discount_label']
                    .' at '.$p['store_wide']['store_name']
                    .($p['store_wide']['min_purchase'] > 0
                        ? ' (min spend '.$p['store_wide']['min_purchase'].')' : '')
                : $p['name'].' [ID:'.$p['id'].'] '.$p['discount_label'].' now '.$p['discounted_price'];
            if (! $p['is_available']) {
                $base .= ' [UNAVAILABLE — ' . $p['availability_label'] . ' — DO NOT RECOMMEND]';
            }
            if (!empty($p['variations'])) {
                $base .= ' [variations:' . implode('/', array_column($p['variations'], 'type')) . ']';
            }
            return $base;
        }, $products));

        return 'Best deals (' . count($products) . ' items): ' . $lines;
    }

    /**
     * Restaurants with a store wide rate in force at this moment.
     *
     * Resolved in PHP because a happy hour is a schedule rather than a column and cannot
     * be asked in SQL. Primed in one batch first: asked one restaurant at a time this
     * turns a single listing into a query per store in the zone.
     *
     * @return array<int, int>
     */
    private function storeWideRates(): array
    {
        if (! $this->zoneIds) {
            return [];
        }

        $restaurants = Restaurant::active()->whereIn('zone_id', $this->zoneIds)
            ->with('discount')->get(['id', 'zone_id', 'name']);

        Helpers::prime_running_happy_hours($restaurants);

        $rates = [];
        foreach ($restaurants as $restaurant) {
            $rate = Helpers::get_effective_restaurant_discount($restaurant);
            if (! $rate) {
                continue;
            }

            $rates[$restaurant->id] = [
                'percent'        => (float) $rate['discount'],
                'min_purchase'   => (float) ($rate['min_purchase'] ?? 0),
                'max_discount'   => (float) ($rate['max_discount'] ?? 0),
                'store_name'     => $restaurant->name,
                // Which of the two store wide promotions it is. They are mutually
                // exclusive and a customer reads them differently: a happy hour is a
                // window that closes, a standing discount is not.
                'is_happy_hour'  => Helpers::get_running_happy_hour($restaurant) !== null,
            ];
        }

        return $rates;
    }

    // Order by the saving the customer actually sees: the SQL ordering uses the
    // item's own discount, but an active restaurant campaign can replace it.
    private function bestSavingFirst(array $products): array
    {
        usort($products, static function (array $a, array $b) {
            $fraction = static function (array $p) {
                // A store wide item saves at the store's rate, not at its own - its own
                // reads 0 by design, which would otherwise rank every one of them last.
                if (isset($p['store_wide'])) {
                    return ((float) $p['store_wide']['percent']) / 100;
                }

                return ((float) ($p['price'] ?? 0)) > 0
                    ? (((float) $p['price'] - (float) $p['discounted_price']) / (float) $p['price'])
                    : 0.0;
            };

            return $fraction($b) <=> $fraction($a);
        });

        return $products;
    }

    private function availableFirst(array $products): array
    {
        $available   = array_values(array_filter($products, static fn (array $p) => $p['is_available'] ?? true));
        $unavailable = array_values(array_filter($products, static fn (array $p) => ! ($p['is_available'] ?? true)));

        return array_merge($available, $unavailable);
    }

    private function parseVariations(mixed $raw): array
    {
        if (empty($raw)) return [];
        $decoded = \is_array($raw) ? $raw : json_decode((string) $raw, true);
        return \is_array($decoded) ? $decoded : [];
    }

    private function format(Food $item): array
    {
        $price        = (float) $item->getAttribute('price');
        $pricing      = FoodPricing::resolve($item, $price);
        $availability = FoodAvailability::withStore($item);

        return [
            'id'               => $item->getKey(),
            'name'             => $item->getAttribute('name'),
            'item_type'        => 'food',
            'price'            => $price,
            'is_available'       => $availability['available'],
            'unavailable_reason' => $availability['reason'],
            'availability_label' => $availability['label'],
            'discounted_price' => $pricing['discounted_price'],
            'discount'         => $pricing['discount'],
            'discount_type'    => $pricing['discount_type'],
            'discount_label'   => $pricing['discount_label'],
            'avg_rating'       => (float) $item->getAttribute('avg_rating'),
            'rating_count'     => (int) $item->getAttribute('rating_count'),
            'order_count'      => (int) $item->getAttribute('order_count'),
            'image'            => $item->getAttribute('image'),
            'image_full_url'   => $item->image_full_url,
            'veg'              => (bool) $item->getAttribute('veg'),
            'recommended'      => (bool) $item->getAttribute('recommended'),
            'stock'            => (int) $item->getAttribute('item_stock'),
            'store_id'         => (int) $item->getAttribute('restaurant_id'),
            'store_name'       => $item->restaurant?->getAttribute('name'),
            'store_logo'       => $item->restaurant?->getAttribute('logo'),
            'store_logo_full_url' => $item->restaurant?->logo_full_url,
            'store_avg_rating' => $item->restaurant?->getAttribute('rating'),
            'delivery_time'    => $item->restaurant?->getAttribute('delivery_time'),
            'minimum_order'    => $item->restaurant?->getAttribute('minimum_order'),
            'free_delivery'    => (bool) $item->restaurant?->getAttribute('free_delivery'),
            'category_id'      => (int) $item->getAttribute('category_id'),
            'category_name'    => $item->category?->getAttribute('name'),
            'variations'       => $this->parseVariations($item->getAttribute('variations')),
        ];
    }
}
