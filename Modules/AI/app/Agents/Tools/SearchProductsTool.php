<?php

namespace Modules\AI\app\Agents\Tools;

use Modules\AI\app\Agents\AiResponseContext;
use Modules\AI\app\Support\FoodAvailability;
use Modules\AI\app\Support\FoodPricing;
use App\CentralLogics\Helpers;
use App\CentralLogics\PersonalizationService;
use App\Models\Category;
use App\Models\Food;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class SearchProductsTool implements Tool
{
    public function __construct(
        private readonly AiResponseContext $context,
        private readonly array $zoneIds = [],
        private readonly ?User $user     = null,
    ) {}

    public function description(): string
    {
        return 'Search for food items by name or keyword. Returns matching active foods with prices, discounts, ratings, and restaurant info. When the customer wants items from ONE specific named restaurant (e.g. "burgers from Hungry Puppets", "the great impasta menu"), put the food keyword in `query` and the restaurant name in `restaurant_name` so results stay within that restaurant only.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query'       => $schema->string()->description('Search keyword or food name (e.g. "burger"). Leave empty only when the customer wants a whole restaurant menu via restaurant_name.')->required(),
            'restaurant_name' => $schema->string()->description('Restrict the search to ONE specific restaurant the customer named (e.g. "Hungry Puppets"). Put the food keyword in query. Null when no specific restaurant is named.')->required()->nullable(),
            'category_id' => $schema->number()->description('Filter by category ID (use GetCategoriesTool first), or null for all categories')->required()->nullable(),
            'max_price'   => $schema->number()->description('Maximum price filter, or null for no limit')->required()->nullable(),
            'veg_only'    => $schema->boolean()->description('true for veg items only, false for non-veg, null for all')->required()->nullable(),
            'sort_by'     => $schema->string()->description('Result order: "top_rated" (highest rated first) or "popular" (most ordered first). Null uses the default — dietary browses (veg / non-veg) are top-rated first, everything else is popularity first.')->required()->nullable(),
            'limit'       => $schema->number()->description('Number of results to return. Default 8 for a keyword search. When the customer asks for a whole restaurant menu or "all items" from a category, pass a higher number (e.g. 30) — the tool returns the full menu (up to 50) for restaurant/category matches.')->required()->nullable(),
        ];
    }

    public function handle(Request $request): string
    {
        $args     = $request->all();
        $query    = trim((string) ($args['query'] ?? ''));
        $requestedLimit = isset($args['limit']) && $args['limit'] !== null ? (int) $args['limit'] : null;
        $limit    = min($requestedLimit ?? 8, 12);
        $maxPrice = ($args['max_price'] ?? null) !== null ? (float) $args['max_price'] : null;
        $catId    = ($args['category_id'] ?? null) !== null ? (int) $args['category_id'] : null;
        $vegOnly  = ($args['veg_only'] ?? null) !== null ? (bool) $args['veg_only'] : null;
        $restaurantName = isset($args['restaurant_name']) && $args['restaurant_name'] !== null ? trim((string) $args['restaurant_name']) : null;
        $sortBy   = isset($args['sort_by']) && $args['sort_by'] !== null ? strtolower(trim((string) $args['sort_by'])) : null;

        // "veg" / "vegan" / "non-veg" describe the item's DIETARY TAG, not its
        // name. Searching them as text returns only items with "veg" in the
        // title (2 of 14 veg items here) and — worse — keeps matching those
        // titles after the items are switched to non-veg. Strip the word, turn
        // it into the veg filter, and let whatever is left search by name
        // ("veg burger" → burger + veg filter; "veg" alone → browse all veg).
        $dietary = $this->extractDietary($query);
        if ($dietary['diet'] !== null) {
            $vegOnly = $vegOnly ?? ($dietary['diet'] === 'veg');
            $query   = $dietary['query'];
        }

        // A dietary browse ("suggest veg items") is a recommendation request, so
        // lead with the best-rated food rather than the most-ordered. An explicit
        // sort_by from the caller always wins.
        $sortBy ??= $vegOnly !== null ? 'top_rated' : 'popular';
        $topRated = $sortBy === 'top_rated';

        $keywords = array_filter(array_map('trim', explode(' ', $query)));

        $scopedStoreIds = null;
        if ($restaurantName !== null && $restaurantName !== '') {
            $scopedStoreIds = Restaurant::where('status', 1)
                ->when(!empty($this->zoneIds), fn ($q) => $q->whereIn('zone_id', $this->zoneIds))
                ->where('name', 'like', "%{$restaurantName}%")
                ->pluck('id')->all();

            if (empty($scopedStoreIds)) {
                $this->context->recordTool('SearchProductsTool');
                return "I couldn't find a restaurant named \"{$restaurantName}\" in your area. Could you double-check the name?";
            }
        }
        $scoped = $scopedStoreIds !== null;

        $browseAll = ! $scoped && $this->isGenericBrowse($query);

        $core         = $this->coreSearchQuery($query);
        $cascadeQuery = $core !== '' ? $core : $query;

        $storeIds   = [];
        $autoCatIds = [];
        $matchKind  = 'name';
        $looseName  = false;

        if (! $scoped && $catId === null && $query !== '' && ! $browseAll) {
            $storeIds = Restaurant::where('status', 1)
                ->when(!empty($this->zoneIds), fn ($q) => $q->whereIn('zone_id', $this->zoneIds))
                ->where('name', 'like', "%{$cascadeQuery}%")
                ->pluck('id')->all();

            if (!empty($storeIds)) {
                $matchKind = 'store';
            } else {
                $autoCatIds = Category::where('status', 1)
                    ->where('name', 'like', "%{$cascadeQuery}%")
                    ->pluck('id')->map(fn ($id) => (string) $id)->all();
                if (!empty($autoCatIds)) {
                    $matchKind = 'category';
                }
            }
        }

        if ($browseAll || $matchKind === 'store' || $matchKind === 'category' || ($scoped && $query === '')) {
            $limit = min($requestedLimit ?? 30, 50);
        }

        // Whether the restaurant is open right now depends on its `active` flag
        // and — only when scheduled ordering is off — today's opening schedule.
        $eagerLoads = [
            'restaurant:id,name,logo,rating,delivery_time,minimum_order,free_delivery,zone_id,active',
            'restaurant.discount',
            'category:id,name',
            'newVariations', 'newVariationOptions',
        ];
        if (! Helpers::schedule_order()) {
            $eagerLoads[] = 'restaurant.schedules';
        }

        $runQuery = function (bool $loose, ?string $matchKind = null) use (
            $catId, $maxPrice, $vegOnly, $storeIds, $autoCatIds, $keywords, $query, $limit, $browseAll,
            $scoped, $scopedStoreIds, $topRated, $eagerLoads, &$resolvedMatchKind
        ) {
            $matchKind ??= $resolvedMatchKind;
            $builder = Food::active()
                ->with($eagerLoads)
                ->when(!empty($this->zoneIds), fn ($q) => $q->whereHas('restaurant', fn ($s) => $s->whereIn('zone_id', $this->zoneIds)))
                ->when($scoped, fn ($q) => $q->whereIn('restaurant_id', $scopedStoreIds))
                ->when($catId, fn ($q) => $q->where(function ($c) use ($catId) {
                    $c->where('category_id', $catId)
                        ->orWhereJsonContains('category_ids', ['id' => (string) $catId])
                        ->orWhereHas('category', fn ($cat) => $cat->where('parent_id', $catId));
                }))
                ->when($maxPrice !== null, fn ($q) => $q->where('price', '<=', $maxPrice))
                ->when($vegOnly !== null, fn ($q) => $q->type($vegOnly ? 'veg' : 'non_veg'))
                ->when($matchKind === 'store', fn ($q) => $q->whereIn('restaurant_id', $storeIds))
                ->when($matchKind === 'category', fn ($q) => $q->where(function ($c) use ($autoCatIds) {
                    foreach ($autoCatIds as $cid) {
                        $c->orWhere('category_id', (int) $cid)
                            ->orWhereJsonContains('category_ids', ['id' => $cid]);
                    }
                }))
                ->when(($matchKind === 'name' || $scoped) && ! $loose && ! $browseAll && $query !== '', fn ($q) => $q->where('name', 'like', "%{$query}%"))
                ->when(($matchKind === 'name' || $scoped) && $loose, fn ($q) => $q->where(function ($c) use ($keywords, $query) {
                    $c->where('name', 'like', "%{$query}%");
                    foreach ($keywords as $kw) {
                        if (mb_strlen($kw) >= 3) {
                            $c->orWhere('name', 'like', "%{$kw}%");
                        }
                    }
                }))
                ->when($topRated, fn ($q) => $q->orderByDesc('avg_rating')->orderByDesc('rating_count'))
                ->orderByDesc('order_count');

            // Storefront-parity: personalize toward the customer's preferences as
            // the primary sort (relevance / rating / popularity become tiebreakers);
            // a no-op for guests.
            $builder = PersonalizationService::applyItemPersonalization($builder, $this->user?->getKey());

            return $builder
                ->limit($limit)
                ->get([
                    'id', 'name', 'price', 'discount', 'discount_type',
                    'avg_rating', 'rating_count', 'order_count', 'image',
                    'restaurant_id', 'category_id', 'veg', 'recommended', 'item_stock',
                    'stock_type', 'sell_count', 'available_time_starts', 'available_time_ends',
                    'maximum_cart_quantity', 'variations', 'choice_options',
                ]);
        };

        $resolvedMatchKind = $matchKind;

        $items = $runQuery(false);

        // A restaurant/category name match that yields nothing is a dead end —
        // "veg burger" resolves "burger" to a restaurant NAME, and once the veg
        // filter is applied that restaurant may have no match at all. Retry as a
        // plain item-name search instead of reporting "no food found".
        if ($items->isEmpty() && ($matchKind === 'store' || $matchKind === 'category') && $query !== '') {
            $matchKind = $resolvedMatchKind = 'name';
            $items     = $runQuery(false, 'name');
        }

        if ($items->isEmpty() && ($matchKind === 'name' || $scoped) && count($keywords) > 1) {
            $items     = $runQuery(true);
            $looseName = true;
        }

        $items = $items->unique(fn ($i) =>
            (int) $i->getAttribute('restaurant_id') . '|' . mb_strtolower(trim((string) $i->getAttribute('name')))
        )->values();

        $products = $items->map($this->format(...))->values()->all();
        $this->context->recordTool('SearchProductsTool');

        $available   = array_values(array_filter($products, static fn (array $p) => $p['is_available']));
        $unavailable = array_values(array_filter($products, static fn (array $p) => ! $p['is_available']));
        $products    = array_merge($available, $unavailable); // available first

        // The product carousel is a "suggestions" surface — only orderable items
        // belong on it. Closed-restaurant / sold-out items stay out of the cards
        // (they still appear, tagged, in the text below so questions can be
        // answered) so nothing unorderable is ever presented as a suggestion.
        $this->context->addProducts($available);

        $matchedAs = match (true) {
            $scoped                             => " (only from {$restaurantName})",
            $matchKind === 'store'              => ' (matched restaurant name)',
            $matchKind === 'category'           => ' (matched category name)',
            $matchKind === 'name' && $looseName => ' (no exact item — showing related items by keyword)',
            default                             => '',
        };

        if (count($products) === 0) {
            if ($scoped) {
                return $query !== ''
                    ? "\"{$restaurantName}\" doesn't have any \"{$query}\" items available right now."
                    : "\"{$restaurantName}\" has no items available right now.";
            }
            return $browseAll
                ? 'No items are available right now.'
                : "No food found for \"{$query}\"{$matchedAs}.";
        }

        // Matches exist, but NONE can be ordered right now — don't present them
        // as options (this is the "restaurant closed / all out of stock" case).
        if (count($available) === 0) {
            return $this->allUnavailableMessage($unavailable, $scoped, $restaurantName, $query, $browseAll);
        }

        $lines = implode('; ', array_map(function (array $p): string {
            $base = $p['name'] . ' [ID:' . $p['id'] . '] ' . $p['discounted_price'] . ($p['veg'] ? ' (veg)' : '');
            if (! $p['is_available']) {
                $base .= ' [UNAVAILABLE — ' . $p['availability_label'] . ' — DO NOT RECOMMEND]';
            }
            if (!empty($p['variations'])) {
                $base .= ' [variations:' . implode('/', array_column($p['variations'], 'type')) . ']';
            }
            return $base;
        }, $products));

        $orderNote = $topRated ? ' (highest rated first)' : '';

        if ($browseAll) {
            return count($products) . ' item(s) available across restaurants' . $orderNote . ': ' . $lines;
        }

        return count($products) . ' food item(s) found for "' . $query . '"' . $matchedAs . $orderNote . ': ' . $lines;
    }

    // Splits a dietary tag off the query: ['diet' => 'veg'|'non_veg'|null,
    // 'query' => whatever remains to search by name].
    private function extractDietary(string $query): array
    {
        $normalised = preg_replace('/\bnon[\s\-_]*veg(etarian)?s?\b/iu', ' __nonveg__ ', $query) ?? $query;

        $vegWords = ['veg', 'vegs', 'veggie', 'veggies', 'vegetarian', 'vegetarians', 'vegan', 'vegans'];
        $tokens   = array_values(array_filter(array_map('trim', preg_split('/\s+/', $normalised) ?: [])));

        $diet = null;
        $kept = [];
        foreach ($tokens as $token) {
            $bare = strtolower(trim($token, ".,!?:;\"'()"));

            if ($bare === '__nonveg__') {
                $diet = 'non_veg';
                continue;
            }
            if (in_array($bare, $vegWords, true)) {
                // An explicit non-veg ask wins if both somehow appear.
                $diet = $diet ?? 'veg';
                continue;
            }
            $kept[] = $token;
        }

        return ['diet' => $diet, 'query' => trim(implode(' ', $kept))];
    }

    private function isGenericBrowse(string $query): bool
    {
        $q = strtolower(trim($query));
        if ($q === '') {
            return true;
        }
        // Drop scattered filler words ("show me all the …").
        $q = preg_replace('/\b(show|me|give|list|all|any|some|the|please|see|view|browse|available|suggest|suggestions|recommend|recommendations|find|get|want|need|looking|for|my|i|a|an|of)\b/u', ' ', $q) ?? $q;
        $q = trim(preg_replace('/\s+/', ' ', $q) ?? '');

        $generic = [
            '', 'food', 'foods', 'item', 'items', 'product', 'products',
            'dish', 'dishes', 'meal', 'meals', 'menu', 'menus',
            'everything', 'anything', 'stuff', 'option', 'options',
        ];
        return in_array($q, $generic, true);
    }

    private function coreSearchQuery(string $query): string
    {
        $strip = [
            'food', 'foods', 'item', 'items', 'product', 'products',
            'dish', 'dishes', 'meal', 'meals', 'menu', 'menus',
            'give', 'me', 'from', 'show', 'all', 'the', 'please', 'for',
            'some', 'any', 'a', 'an', 'of', 'in', 'my',
        ];
        $tokens = array_filter(array_map('trim', preg_split('/\s+/', strtolower($query)) ?: []));
        $kept   = array_values(array_filter($tokens, fn ($t) => $t !== '' && !in_array($t, $strip, true)));
        return trim(implode(' ', $kept));
    }

    /**
     * The response when items matched but none are orderable right now. Names the
     * cause (closed restaurant vs stock/serving-time) and offers a way forward,
     * without listing the items as if they could be added.
     *
     * @param array<int, array<string, mixed>> $unavailable
     */
    private function allUnavailableMessage(array $unavailable, bool $scoped, ?string $restaurantName, string $query, bool $browseAll): string
    {
        $reasons     = array_values(array_unique(array_filter(array_map(static fn (array $p) => $p['unavailable_reason'] ?? null, $unavailable))));
        $storeReasons = ['store_paused', 'store_closed'];
        $anyClosed   = ! empty(array_intersect($reasons, $storeReasons));
        $onlyClosed  = ! empty($reasons) && empty(array_diff($reasons, $storeReasons));

        if ($scoped) {
            // All items share one restaurant, so if any are store-closed the whole
            // restaurant is closed — that's the overriding reason.
            if ($anyClosed) {
                return "\"{$restaurantName}\" is currently closed, so none of its items can be ordered right now. Want me to find similar items from other open restaurants?";
            }
            return $query !== ''
                ? "\"{$restaurantName}\" has no \"{$query}\" items you can order right now — they're out of stock or outside their serving time. Want me to check other restaurants?"
                : "\"{$restaurantName}\" has no items you can order right now. Want me to check other restaurants?";
        }

        if ($onlyClosed) {
            return "The matching items are from restaurants that are currently closed, so they can't be ordered right now. Want me to suggest options from open restaurants?";
        }

        return $browseAll
            ? "I couldn't find anything available to order right now."
            : "I found matches for \"{$query}\", but none can be ordered right now (out of stock, restaurant closed, or outside serving hours). Want me to suggest available alternatives?";
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
        // Availability includes the restaurant being closed right now, so a
        // closed store's items are never offered as orderable.
        $availability = FoodAvailability::withStore($item);

        return [
            'id'                    => $item->getKey(),
            'name'                  => $item->getAttribute('name'),
            'item_type'             => 'food',
            'price'                 => $price,
            'is_available'     => $availability['available'],
            'unavailable_reason' => $availability['reason'],
            'availability_label' => $availability['label'],
            'discounted_price'      => $pricing['discounted_price'],
            'discount'              => $pricing['discount'],
            'discount_type'         => $pricing['discount_type'],
            'avg_rating'            => (float) $item->getAttribute('avg_rating'),
            'rating_count'          => (int) $item->getAttribute('rating_count'),
            'order_count'           => (int) $item->getAttribute('order_count'),
            'image'                 => $item->getAttribute('image'),
            'image_full_url'        => $item->image_full_url,
            'veg'                   => (bool) $item->getAttribute('veg'),
            'recommended'           => (bool) $item->getAttribute('recommended'),
            'stock'                 => (int) $item->getAttribute('item_stock'),
            'maximum_cart_quantity' => (int) $item->getAttribute('maximum_cart_quantity'),
            'store_id'              => (int) $item->getAttribute('restaurant_id'),
            'store_name'            => $item->restaurant?->getAttribute('name'),
            'store_logo'           => $item->restaurant?->getAttribute('logo'),
            'store_logo_full_url'  => $item->restaurant?->logo_full_url,
            'store_avg_rating'     => $item->restaurant?->getAttribute('rating'),
            'delivery_time'        => $item->restaurant?->getAttribute('delivery_time'),
            'minimum_order'        => $item->restaurant?->getAttribute('minimum_order'),
            'free_delivery'        => (bool) $item->restaurant?->getAttribute('free_delivery'),
            'category_id'          => (int) $item->getAttribute('category_id'),
            'category_name'        => $item->category?->getAttribute('name'),
            'variations'           => $this->parseVariations($item->getAttribute('variations')),
            'choice_options'       => $this->parseVariations($item->getAttribute('choice_options')),
        ];
    }
}
