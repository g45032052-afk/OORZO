<?php

namespace Modules\AI\app\Agents\Tools;

use Modules\AI\app\Agents\AiResponseContext;
use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * Data-backed answers for meta questions ("what's trending?", "top
 * categories?", "popular restaurants?"). Single-module (food): the 6amMart
 * "modules" metric is dropped — only categories + restaurants remain. Zone-
 * scoped to the customer's area and cached for 5 minutes.
 */
class GetPlatformStatsTool implements Tool
{
    private const CACHE_TTL = 300;
    private const TOP_N     = 5;

    /** @param int[] $zoneIds Overlapping zones the user falls inside. */
    public function __construct(
        private readonly AiResponseContext $context,
        private readonly array $zoneIds = [],
    ) {}

    public function description(): string
    {
        return 'Get platform popularity stats: top food categories and top restaurants by order volume. Use this when the user asks meta questions like "what\'s trending", "which category sells the most", "popular restaurants", or "is X popular". Data is zone-scoped to the customer\'s delivery area.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'metric' => $schema->string()
                ->description('Which breakdown: "categories" (top food categories), "stores" (top restaurants by order volume), or null for a summary of both.')
                ->required()
                ->nullable(),
        ];
    }

    public function handle(Request $request): string
    {
        $this->context->recordTool('GetPlatformStatsTool');
        $metric = $request->all()['metric'] ?? null;

        $cacheKey = 'ai:platform_stats:' . md5(json_encode([$metric, $this->zoneIds]));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($metric) {
            $parts = [];

            if ($metric === null || $metric === 'categories') {
                $parts[] = $this->topCategories();
            }
            if ($metric === null || $metric === 'stores' || $metric === 'restaurants') {
                $parts[] = $this->topRestaurants();
            }

            $parts = array_filter($parts);
            return empty($parts) ? 'No popularity data available right now.' : implode(' | ', $parts);
        });
    }

    private function topRestaurants(): string
    {
        $rows = Restaurant::query()
            ->where('status', 1)
            ->when(!empty($this->zoneIds), fn ($q) => $q->whereIn('zone_id', $this->zoneIds))
            ->orderByDesc('order_count')
            ->limit(self::TOP_N)
            ->get(['id', 'name', 'order_count']);

        if ($rows->isEmpty()) {
            return '';
        }

        $list = $rows->map(fn ($r) => $r->name . ' (' . (int) $r->order_count . ' orders)')->implode(', ');
        return 'Top restaurants: ' . $list;
    }

    private function topCategories(): string
    {
        // Top categories by total food order_count within the customer's zone.
        $rows = DB::table('food')
            ->join('categories', 'categories.id', '=', 'food.category_id')
            ->when(!empty($this->zoneIds), function ($q) {
                $q->join('restaurants', 'restaurants.id', '=', 'food.restaurant_id')
                    ->whereIn('restaurants.zone_id', $this->zoneIds);
            })
            ->where('food.status', 1)
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc(DB::raw('SUM(food.order_count)'))
            ->limit(self::TOP_N)
            ->get(['categories.name as name', DB::raw('SUM(food.order_count) as total_orders')]);

        if ($rows->isEmpty()) {
            return '';
        }

        $list = $rows->map(fn ($r) => $r->name . ' (' . (int) $r->total_orders . ' orders)')->implode(', ');
        return 'Top categories: ' . $list;
    }
}
