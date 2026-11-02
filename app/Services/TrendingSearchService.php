<?php

namespace App\Services;

use App\Models\Category;
use App\Models\SearchLog;
use Illuminate\Support\Facades\Cache;

/**
 * What customers near you are searching for right now.
 *
 * Ported from mart's App\Services\TrendingSearchService with the module dimension removed -
 * StackFood is single-module, so a keyword trends per zone and nothing else.
 *
 * The rules that make the list trustworthy rather than noisy are all kept:
 *
 *   - a six hour window, so "trending" means now and not this month;
 *   - distinct searchers, so one person searching the same word forty times proves nothing;
 *   - searches that returned something, so a term nobody can actually order never surfaces;
 *   - and the customer's own zones, so a term trending across town does not lead here.
 *
 * When nothing clears that bar - a quiet zone, a new deployment - the list falls back to the
 * categories the zone actually sells, shuffled, so the search screen is never blank.
 */
class TrendingSearchService
{
    /** Terms trending in these zones, most searchers first, at most 10. */
    public function getTrending(string $zoneId, bool $cache = true): array
    {
        $cacheKey = 'trending_searches_'.md5($zoneId);
        $zoneIds = $this->parseZoneIds($zoneId);

        $compute = function () use ($zoneIds) {
            $trending = SearchLog::selectRaw('keyword, COUNT(DISTINCT CASE WHEN user_id IS NOT NULL THEN CONCAT("u_", user_id) ELSE CONCAT("g_", guest_id) END) as unique_users')
                ->when(! empty($zoneIds), function ($query) use ($zoneIds) {
                    $query->where(function ($q) use ($zoneIds) {
                        foreach ($zoneIds as $zone) {
                            $q->orWhereRaw('JSON_CONTAINS(zone_id, ?)', [(string) $zone]);
                        }
                    });
                })
                ->where('created_at', '>=', now()->subHours(6))
                ->where('result_count', '>', 0)
                ->whereRaw('LENGTH(TRIM(keyword)) >= 2')
                ->groupBy('keyword')
                ->havingRaw('unique_users >= 2')
                ->orderByDesc('unique_users')
                ->limit(10)
                ->pluck('keyword')
                ->toArray();

            if (! empty($trending)) {
                return $trending;
            }

            $fallback = $this->fallbackCategories($zoneIds);

            // Shuffled so a quiet zone does not show the same frozen list every visit.
            shuffle($fallback);

            return $fallback;
        };

        if (! $cache) {
            Cache::forget($cacheKey);

            return $compute();
        }

        return Cache::remember($cacheKey, now()->addMinutes(15), $compute);
    }

    /**
     * Record a search. Called by the search endpoints themselves, because only they know how
     * many results the keyword actually returned.
     */
    public function log(?string $keyword, ?int $userId, ?string $guestId, string $zoneId, int $resultCount): void
    {
        $keyword = trim((string) $keyword);

        // Single letters are noise, and "null" is what the apps send for an empty search box.
        if (strlen($keyword) < 2 || strtolower($keyword) === 'null') {
            return;
        }

        try {
            SearchLog::create([
                'keyword' => strtolower($keyword),
                'user_id' => $userId,
                'guest_id' => $guestId,
                'zone_id' => $zoneId,
                'result_count' => $resultCount,
                // Stamped here rather than left to the column's CURRENT_TIMESTAMP default: the
                // six hour window is measured with php's clock, and php's timezone comes from the
                // business setting while mysql's comes from the server. Where those disagree - a
                // db in utc under an Asia/Dhaka panel - every row is born six hours old and the
                // window closes on it immediately, so nothing ever trends.
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Analytics must never break a search.
            info(['search_log' => $e->getMessage()]);
        }
    }

    /** Top level categories this zone actually sells, most prioritised first. */
    private function fallbackCategories(array $zoneIds): array
    {
        $hasAvailableFood = function ($query) use ($zoneIds) {
            $query->active()->whereHas('restaurant', function ($restaurant) use ($zoneIds) {
                $restaurant->Active();

                if (! empty($zoneIds)) {
                    $restaurant->whereIn('zone_id', $zoneIds);
                }
            });
        };

        return Category::where('status', 1)
            ->where('position', 0)
            ->where(function ($query) use ($hasAvailableFood) {
                $query->whereHas('products', $hasAvailableFood)
                    ->orWhereHas('childes.products', $hasAvailableFood);
            })
            ->orderByDesc('priority')
            ->limit(10)
            ->pluck('name')
            ->toArray();
    }

    /** The zoneId header is a json list, but a bare id is accepted too. */
    private function parseZoneIds(string $zoneId): array
    {
        $raw = trim($zoneId);

        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            $decoded = [$raw];
        }

        return array_values(array_unique(array_filter(
            array_map(fn ($value) => (int) $value, $decoded),
            fn ($value) => $value > 0
        )));
    }
}
