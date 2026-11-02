<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Services\TrendingSearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(protected TrendingSearchService $trendingService) {}

    /**
     * What customers in this zone are searching for right now.
     *
     * Answers 200 with whatever it has - a quiet zone falls back to its own categories rather
     * than an empty list, so the search screen always has something to offer.
     *
     * `?cache=false` recomputes instead of serving the 15 minute cache, for admins checking
     * that a term has started trending.
     */
    public function getTrendingSearches(Request $request)
    {
        Helpers::getZoneIds($request);

        $trending = array_map(
            fn ($keyword) => ['keyword' => $keyword],
            $this->trendingService->getTrending(
                (string) $request->header('zoneId'),
                filter_var($request->query('cache', true), FILTER_VALIDATE_BOOLEAN)
            )
        );

        return response()->json(['trending_searches' => $trending], 200);
    }
}
