<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\HappyHour;
use App\Models\Restaurant;
use App\Services\HappyHour\HappyHourCatalog;
use Illuminate\Http\Request;

/**
 * Customer-facing happy hour.
 *
 * A happy hour belongs to one zone and reaches a restaurant only through an approved
 * enrolment, so the store list is zone scoped by definition - the caller's zoneId header
 * decides which happy hours are even in play.
 *
 * The discount itself is not computed here. It comes from
 * Helpers::get_effective_restaurant_discount(), the same central resolver every product price
 * goes through, so a store's happy hour price cannot disagree with the price on its menu.
 */
class HappyHourController extends Controller
{
    /**
     * Reading happy hours lives in a service because the AI chat assistant answers the
     * same questions and must apply the same zone and enrolment rules.
     */
    public function __construct(private readonly HappyHourCatalog $catalog) {}

    /**
     * Stores taking part in a happy hour in the caller's zone.
     *
     * Defaults to every participating store with an `is_running_now` flag, so one call can
     * drive both a "happy hour stores" rail and a live-only view. Pass `running=1` to return
     * only the stores whose window is open right now.
     */
    public function stores(Request $request)
    {
        Helpers::getZoneIds($request);
        $zoneIds = json_decode($request->header('zoneId'), true) ?? [];
        $longitude = $request->header('longitude');
        $latitude = $request->header('latitude');

        $limit = (int) ($request['limit'] ?? 10);
        $offset = (int) ($request['offset'] ?? 1);
        $runningOnly = (bool) $request['running'];

        $query = $this->catalog->storesQuery($zoneIds, $longitude, $latitude);

        // A window is a schedule rather than a column, so "running now" can only be decided
        // in PHP. Filtering that way would break the paginator's count, so the live-only view
        // reduces to a set of ids first and pages over those.
        if ($runningOnly) {
            $query = $this->catalog->restrictToLive($query, $zoneIds, $longitude, $latitude);
        }

        $paginator = $query->paginate($limit, ['*'], 'page', $offset);

        $restaurants = Helpers::restaurant_data_formatting(collect($paginator->items()), true);

        // The formatter already carries the running window; this screen additionally needs
        // every window the store is enrolled in and the rate actually in force.
        $restaurants = collect($restaurants)->map(function ($restaurant) use ($paginator) {
            $model = collect($paginator->items())->firstWhere('id', $restaurant['id']);
            $happyHour = $model ? $this->catalog->runningHappyHour($model) : null;
            $discount = $model ? Helpers::get_effective_restaurant_discount($model) : null;

            $restaurant['happy_hour'] = $happyHour ? $this->catalog->happyHourPayload($happyHour) : null;
            $restaurant['is_running_now'] = (bool) $happyHour;
            $restaurant['happy_hours'] = $model
                ? $model->happyHourEnrollments->pluck('happy_hour')->filter()
                    ->map(fn ($h) => $this->catalog->happyHourPayload($h))->values()
                : [];
            // Whatever is actually in force, which may be the admin's restaurant discount
            // if it beats the happy hour.
            $restaurant['active_discount'] = $discount ? (float) $discount['discount'] : 0;

            return $restaurant;
        })->values();

        return response()->json([
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'restaurants' => $restaurants,
        ], 200);
    }

    /**
     * The happy hour running in the caller's zone right now - the home screen banner.
     *
     * Exactly one, never a list. A zone cannot have two overlapping happy hours: the admin
     * side refuses a schedule that collides with an existing one, in either order of
     * creation, across dated, weekly, permanent and custom modes, and a switched-off record
     * still holds its slot. So "the running happy hour" is a well defined single thing and
     * the banner never has to choose between two.
     *
     * Answers with is_running:false rather than 404 when nothing is on, so the app can treat
     * it as a poll and simply hide the banner.
     */
    public function running(Request $request)
    {
        Helpers::getZoneIds($request);
        $zoneIds = json_decode($request->header('zoneId'), true) ?? [];

        $happyHour = $this->catalog->runningIn($zoneIds);

        if (! $happyHour) {
            return response()->json(['is_running' => false, 'happy_hour' => null], 200);
        }

        return response()->json([
            'is_running' => true,
            'happy_hour' => $this->catalog->happyHourPayload($happyHour) + $this->catalog->windowPayload($happyHour, $zoneIds),
        ], 200);
    }

}
