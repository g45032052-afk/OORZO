<?php

namespace Modules\AI\app\Agents\Tools;

use Modules\AI\app\Agents\AiResponseContext;
use Modules\AI\app\Support\FoodAvailability;
use Modules\AI\app\Support\FoodPricing;
use App\CentralLogics\PersonalizationService;
use App\Models\Food;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * The storefront's "New Arrivals" filter — newest first by creation date, inside
 * the customer's zone, personalized for logged-in customers. Unrated placeholder
 * items (avg_rating 0) are hidden so the list shows real, rated items rather than
 * raw newest (which surfaces test/placeholder rows); this mirrors the storefront's
 * rating-filtered New Arrivals view. Without this the assistant has no way to
 * answer "what's new?" and falls back to popular/deals, a different list that
 * reads as if the feature doesn't exist.
 */
class GetNewArrivalsTool implements Tool
{
    /** @param int[] $zoneIds Overlapping zones the user falls inside. */
    public function __construct(
        private readonly AiResponseContext $context,
        private readonly array $zoneIds = [],
        private readonly ?User $user     = null,
    ) {}

    public function description(): string
    {
        return 'Get the newest foods added to the platform — the same list as the storefront\'s "New Arrivals" filter, ordered newest first. Use for "new arrivals", "what\'s new", "newly added", "latest items", "recently added foods".';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'veg_only' => $schema->boolean()->description('true for veg items only, false for non-veg, null for all')->required()->nullable(),
            'limit'    => $schema->number()->description('Number of items to return, default 8, or null for default')->required()->nullable(),
        ];
    }

    public function handle(Request $request): string
    {
        $args    = $request->all();
        $limit   = min((int) ($args['limit'] ?? 8), 12);
        $vegOnly = ($args['veg_only'] ?? null) !== null ? (bool) $args['veg_only'] : null;

        $query = Food::active()
            ->with([
                'restaurant:id,name,logo,rating,delivery_time,minimum_order,free_delivery,zone_id,active',
                'restaurant.discount',
                'category:id,name',
                'newVariations', 'newVariationOptions',
            ])
            ->when(!empty($this->zoneIds), fn ($q) => $q->whereHas('restaurant', fn ($s) => $s->whereIn('zone_id', $this->zoneIds)))
            ->when($vegOnly !== null, fn ($q) => $q->type($vegOnly ? 'veg' : 'non_veg'))
            // Hide unrated placeholder/test items (avg_rating 0) so the chat's
            // new-arrivals shows real, rated items — mirroring the storefront's
            // rating-filtered New Arrivals view rather than raw newest.
            ->where('avg_rating', '>', 0);

        // Same personalized ordering the storefront's new_arrivals filter applies
        // (primary sort toward the customer's preferences; no-op for guests), so
        // the chat's list matches what the customer sees on the storefront.
        $query = PersonalizationService::applyItemPersonalization($query, $this->user?->getKey());

        $items = $query
            // newest first (tiebreaker under personalization)
            ->latest()
            ->limit($limit)
            ->get([
                'id', 'name', 'price', 'discount', 'discount_type',
                'avg_rating', 'rating_count', 'order_count', 'image',
                'restaurant_id', 'category_id', 'veg', 'recommended', 'item_stock',
                'stock_type', 'sell_count', 'available_time_starts', 'available_time_ends',
                'created_at', 'variations', 'choice_options',
            ]);

        // Collapse duplicates: the same item name from the same restaurant must
        // not appear twice in the list or the product carousel.
        $items = $items->unique(fn ($i) =>
            (int) $i->getAttribute('restaurant_id') . '|' . mb_strtolower(trim((string) $i->getAttribute('name')))
        )->values();

        $products = $items->map($this->format(...))->values()->all();
        $products = $this->availableFirst($products);

        $this->context->recordTool('GetNewArrivalsTool');
        // Only orderable items on the suggestion carousel; unavailable ones still
        // appear tagged in the text below so questions can be answered.
        $this->context->addProducts(array_values(array_filter($products, static fn (array $p) => $p['is_available'])));

        if (count($products) === 0) {
            return 'No new items have been added recently.';
        }

        $lines = implode('; ', array_map(function (array $p): string {
            $base = $p['name'] . ' [ID:' . $p['id'] . '] ' . $p['discounted_price'];
            if (! $p['is_available']) {
                $base .= ' [UNAVAILABLE — ' . $p['availability_label'] . ' — DO NOT RECOMMEND]';
            }
            if (!empty($p['variations'])) {
                $base .= ' [variations:' . implode('/', array_column($p['variations'], 'type')) . ']';
            }
            return $base;
        }, $products));

        return count($products) . ' newest item(s), newest first: ' . $lines;
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
            'id'                 => $item->getKey(),
            'name'               => $item->getAttribute('name'),
            'item_type'          => 'food',
            'price'              => $price,
            'is_available'       => $availability['available'],
            'unavailable_reason' => $availability['reason'],
            'availability_label' => $availability['label'],
            'discounted_price'   => $pricing['discounted_price'],
            'discount'           => $pricing['discount'],
            'discount_type'      => $pricing['discount_type'],
            'avg_rating'         => (float) $item->getAttribute('avg_rating'),
            'rating_count'       => (int) $item->getAttribute('rating_count'),
            'order_count'        => (int) $item->getAttribute('order_count'),
            'image'              => $item->getAttribute('image'),
            'image_full_url'     => $item->image_full_url,
            'veg'                => (bool) $item->getAttribute('veg'),
            'recommended'        => (bool) $item->getAttribute('recommended'),
            'stock'              => (int) $item->getAttribute('item_stock'),
            'store_id'           => (int) $item->getAttribute('restaurant_id'),
            'store_name'         => $item->restaurant?->getAttribute('name'),
            'store_logo'         => $item->restaurant?->getAttribute('logo'),
            'store_logo_full_url' => $item->restaurant?->logo_full_url,
            'store_avg_rating'   => $item->restaurant?->getAttribute('rating'),
            'delivery_time'      => $item->restaurant?->getAttribute('delivery_time'),
            'minimum_order'      => $item->restaurant?->getAttribute('minimum_order'),
            'free_delivery'      => (bool) $item->restaurant?->getAttribute('free_delivery'),
            'category_id'        => (int) $item->getAttribute('category_id'),
            'category_name'      => $item->category?->getAttribute('name'),
            'variations'         => $this->parseVariations($item->getAttribute('variations')),
        ];
    }
}
