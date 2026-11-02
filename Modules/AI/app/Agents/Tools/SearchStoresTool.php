<?php

namespace Modules\AI\app\Agents\Tools;

use Modules\AI\app\Agents\AiResponseContext;
use App\CentralLogics\RestaurantLogic;
use App\Models\Cuisine;
use App\Models\Restaurant;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * Restaurant search (Store→Restaurant). Single-module (food). Distance-based
 * "nearest" sorting is not wired (StackFood's withOpen scope adds open-status
 * but not a distance column here), so near-intent falls back to the popular
 * sort and the result text says so.
 */
class SearchStoresTool implements Tool
{
    private const INTENT_TOP = [
        'best', 'top', 'popular', 'trending', 'highest', 'rated', 'good',
        'suggest', 'suggestions', 'recommend', 'recommended',
        'restaurants', 'restaurant', 'stores', 'store', 'shops', 'shop',
        'vendors', 'vendor', 'all', 'me',
    ];
    private const INTENT_NEAR = ['nearby', 'nearest', 'near', 'around', 'close', 'closest'];

    /** @param int[] $zoneIds Overlapping zones the user falls inside. */
    public function __construct(
        private readonly AiResponseContext $context,
        private readonly array  $zoneIds   = [],
        private readonly ?float $latitude  = null,
        private readonly ?float $longitude = null,
    ) {}

    public function description(): string
    {
        return 'Search restaurants / vendors, optionally restricted to ONE cuisine (Bengali, Italian, Chinese…). Pass a specific name to filter by name, or leave query empty (or pass a generic phrase like "best", "top", "popular", "nearby") to get the active restaurants in the customer\'s zone ranked by open-now status and order volume. Returns each restaurant\'s rating, delivery time, minimum order, free delivery, and open/closed status.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('Specific restaurant name to search, or leave null for top restaurants')->required()->nullable(),
            'cuisine' => $schema->string()->description('Cuisine name to restrict results to (e.g. "Bengali", "Italian"). Null for all cuisines. Use this for "Bengali cuisine restaurants" — do NOT put the cuisine in query, that searches restaurant NAMES.')->required()->nullable(),
            'limit' => $schema->number()->description('Number of results, default 6, or null for default')->required()->nullable(),
        ];
    }

    public function handle(Request $request): string
    {
        $args  = $request->all();
        $query = trim((string) ($args['query'] ?? ''));
        $limit = min((int) ($args['limit'] ?? 6), 10);

        $cuisineName  = isset($args['cuisine']) && $args['cuisine'] !== null ? trim((string) $args['cuisine']) : null;

        $keywords     = array_filter(array_map('trim', explode(' ', $query)));
        $isTopListing = $query === '' || $this->isGenericIntent($keywords);

        // Cuisine is a RELATION (cuisine_restaurant), not part of the restaurant
        // name — "Bengali cuisine restaurants" must filter on it, not text-match
        // "Bengali" against names. Resolve the name to IDs the way the storefront's
        // cuisine page does (Restaurant::scopeCuisine).
        $cuisineIds = null;
        if ($cuisineName !== null && $cuisineName !== '') {
            $cuisineIds = Cuisine::where('status', 1)
                ->where('name', 'like', "%{$cuisineName}%")
                ->pluck('id')->all();

            if (empty($cuisineIds)) {
                $this->context->recordTool('SearchStoresTool');
                return "I couldn't find a cuisine called \"{$cuisineName}\". Ask me to list the cuisines and pick one from there.";
            }
        }

        $lng = $this->longitude ?? 0;
        $lat = $this->latitude ?? 0;

        $stores = Restaurant::withOpen($lng, $lat)
            ->with('restaurant_config') // needed by the verified_seller accessor — eager-load to avoid N+1
            ->active()
            ->when(!empty($this->zoneIds), fn ($q) => $q->whereIn('zone_id', $this->zoneIds))
            ->when($cuisineIds !== null, fn ($q) => $q->whereHas('cuisine', fn ($c) => $c->whereIn('cuisine_restaurant.cuisine_id', $cuisineIds)))
            ->when(! $isTopListing, fn ($q) => $q->where(function ($qq) use ($keywords, $query) {
                $qq->where('name', 'like', "%{$query}%");
                foreach ($keywords as $kw) {
                    $qq->orWhere('name', 'like', "%{$kw}%");
                }
                $qq->orWhereHas('foods', function ($iq) use ($query, $keywords) {
                    $iq->where('name', 'like', "%{$query}%");
                    foreach ($keywords as $kw) {
                        $iq->orWhere('name', 'like', "%{$kw}%");
                    }
                });
            }))
            ->orderByDesc('open')
            ->orderByDesc('order_count')
            ->limit($limit)
            ->get();

        $storeList = $stores->map($this->format(...))->values()->all();
        $this->context->recordTool('SearchStoresTool');
        $this->context->addStores($storeList);

        if (count($storeList) === 0) {
            if ($cuisineIds !== null) {
                return "No {$cuisineName} restaurants are available in your area right now.";
            }
            return $isTopListing ? 'No active restaurants available right now.' : "No restaurants found for \"{$query}\".";
        }

        $matchedAs = $isTopListing ? ' (top restaurants)' : '';
        if ($this->isNearestIntent($keywords)) {
            $matchedAs = ' (distance sorting not available — showing popular restaurants instead)';
        }

        $lines = implode('; ', array_map(function (array $s): string {
            $parts = [$s['name'] . ' [ID:' . $s['id'] . ']'];
            if ($s['avg_rating'] > 0)        $parts[] = '★' . number_format($s['avg_rating'], 1);
            if (! empty($s['delivery_time'])) $parts[] = $s['delivery_time'];
            $parts[] = $s['is_open'] ? 'open' : 'closed';
            if ($s['free_delivery'])          $parts[] = 'free delivery';
            return implode(' — ', $parts);
        }, $storeList));

        $label = $isTopListing ? ('Top restaurants' . ($query !== '' ? " for \"{$query}\"" : '')) : ('restaurant(s) found for "' . $query . '"');
        if ($cuisineIds !== null) {
            $label .= " serving {$cuisineName} cuisine";
        }

        return count($storeList) . ' ' . $label . $matchedAs . ': ' . $lines;
    }

    private function isGenericIntent(array $keywords): bool
    {
        if (empty($keywords)) return true;
        $allowed = array_merge(self::INTENT_TOP, self::INTENT_NEAR);
        foreach ($keywords as $kw) {
            if (! in_array(strtolower($kw), $allowed, true)) return false;
        }
        return true;
    }

    private function isNearestIntent(array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (in_array(strtolower($kw), self::INTENT_NEAR, true)) return true;
        }
        return false;
    }

    private function format(Restaurant $store): array
    {
        $avg     = 0.0;
        $buckets = $store->getAttribute('rating');
        if (is_array($buckets) && count($buckets) === 5) {
            $avg = (float) (RestaurantLogic::calculate_restaurant_rating($buckets)['rating'] ?? 0);
        }

        $isOpen = $store->getAttribute('open') !== null
            ? (bool) $store->getAttribute('open')
            : (bool) $store->getAttribute('active');

        return [
            'id'                   => $store->getKey(),
            'name'                 => $store->getAttribute('name'),
            'logo'                 => $store->getAttribute('logo'),
            'logo_full_url'        => $store->logo_full_url,
            'cover_photo'          => $store->getAttribute('cover_photo'),
            'cover_photo_full_url' => $store->cover_photo_full_url,
            'verified_seller'      => (int) $store->verified_seller,
            'avg_rating'           => $avg,
            'order_count'          => (int) ($store->getAttribute('order_count') ?? 0),
            'delivery_time'        => $store->getAttribute('delivery_time'),
            'minimum_order'        => (float) $store->getAttribute('minimum_order'),
            'free_delivery'        => (bool) $store->getAttribute('free_delivery'),
            'is_open'              => $isOpen,
            'delivery'             => (bool) $store->getAttribute('delivery'),
            'take_away'            => (bool) $store->getAttribute('take_away'),
            'veg'                  => (bool) $store->getAttribute('veg'),
            'non_veg'              => (bool) $store->getAttribute('non_veg'),
        ];
    }
}
