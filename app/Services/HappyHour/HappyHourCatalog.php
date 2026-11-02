<?php

namespace App\Services\HappyHour;

use App\CentralLogics\Helpers;
use App\Models\HappyHour;
use App\Models\Restaurant;

/**
 * Reading happy hours: which stores are in one, which window is live, and how a window
 * is described.
 *
 * Extracted from HappyHourController so the AI chat assistant answers "is anything on
 * happy hour?" from the same rules the app's screens use. A happy hour reaches a customer
 * only through an approved enrolment in their own zone, and whether it is running right
 * now can only be decided in PHP - both are easy to get subtly wrong twice.
 *
 * Proven by qa_d2_happyhourapi.php, which recorded /happy-hour/stores and
 * /happy-hour/running beforehand and asserts them byte for byte after.
 */
class HappyHourCatalog
{
    /**
     * When today's window closes, for the countdown on the banner.
     *
     * A window never crosses midnight - the admin form caps a start at 22:59 precisely so it
     * cannot - so today's date plus the end time is always the right instant.
     */
    public function windowPayload(HappyHour $happyHour, array $zoneIds = []): array
    {
        $today = now()->toDateString();

        // A permanent weekly rule writes no dated rows and is matched from its definition.
        $row = $happyHour->is_permanent
            ? null
            : $happyHour->dates()->whereDate('applicable_date', $today)->first();

        $startTime = $row?->start_time ?? $happyHour->start_time;
        $endTime = $row?->end_time ?? $happyHour->end_time;

        $startsAt = \Carbon\Carbon::parse($today.' '.$startTime);
        $endsAt = \Carbon\Carbon::parse($today.' '.$endTime);

        return [
            'started_at' => $startsAt->format('Y-m-d H:i:s'),
            'ends_at' => $endsAt->format('Y-m-d H:i:s'),
            // What the countdown ticks down from. Whole seconds - diffInSeconds returns a
            // float, and a banner counting "1199.053584" is not what anyone wants. Never
            // negative.
            'remaining_seconds' => max(0, (int) now()->diffInSeconds($endsAt, false)),
            // Counted the same way the banner is decided, so a live banner always says at
            // least one store.
            'store_count' => $this->servableBy($happyHour->enrollments(), $zoneIds)->count(),
        ];
    }

    /**
     * Enrolments a customer in these zones could actually order from: approved, and pointing
     * at a store that is switched on and reachable from where they are.
     *
     * One rule, used to decide whether a happy hour counts as running and to count its
     * stores, so the two can never disagree. An empty $zoneIds leaves the zone open, which is
     * what a caller that has already scoped the query wants.
     */
    public function servableBy($query, array $zoneIds = [])
    {
        return $query->approved()->whereHas('restaurant', function ($r) use ($zoneIds) {
            $r->active();

            if ($zoneIds) {
                $r->whereIn('zone_id', $zoneIds);
            }
        });
    }

    public function runningHappyHour(Restaurant $restaurant): ?HappyHour
    {
        return Helpers::get_running_happy_hour($restaurant);
    }

    public function happyHourPayload(HappyHour $happyHour): array
    {
        return $happyHour->customerPayload();
    }
    /**
     * Restaurants in these zones taking part in a happy hour.
     *
     * The enrolment has to be approved AND the happy hour active AND in the caller's zone:
     * a window reaches a customer only through an approved enrolment, so anything less
     * would list a store advertising a discount it will not honour.
     */
    public function storesQuery(array $zoneIds, $longitude = null, $latitude = null)
    {
        return Restaurant::active()
            ->whereIn('zone_id', $zoneIds)
            ->whereHas('happyHourEnrollments', function ($q) use ($zoneIds) {
                $q->approved()->whereHas('happy_hour', function ($h) use ($zoneIds) {
                    $h->active()->whereIn('zone_id', $zoneIds);
                });
            })
            ->with(['happyHourEnrollments' => function ($q) use ($zoneIds) {
                $q->approved()->with(['happy_hour' => fn ($h) => $h->active()->whereIn('zone_id', $zoneIds)]);
            }])
            ->WithOpen($longitude, $latitude);
    }

    /**
     * The same query reduced to the stores whose window is open right now.
     *
     * A window is a schedule rather than a column, so "running now" can only be decided in
     * PHP. Filtering that way would break the paginator's count, so it reduces to a set of
     * ids first and the caller pages over those.
     */
    public function restrictToLive($query, array $zoneIds, $longitude = null, $latitude = null)
    {
        $liveIds = (clone $query)->get()
            ->filter(fn ($restaurant) => (bool) $this->runningHappyHour($restaurant))
            ->pluck('id');

        return Restaurant::active()->whereIn('id', $liveIds)
            ->with(['happyHourEnrollments' => function ($q) use ($zoneIds) {
                $q->approved()->with(['happy_hour' => fn ($h) => $h->active()->whereIn('zone_id', $zoneIds)]);
            }])
            ->WithOpen($longitude, $latitude);
    }

    /**
     * The one happy hour running in these zones, or null.
     *
     * Exactly one, never a list: a zone cannot hold two overlapping happy hours, because
     * the admin side refuses a schedule that collides with an existing one in either order
     * of creation, and a switched-off record still holds its slot.
     */
    public function runningIn(array $zoneIds): ?HappyHour
    {
        return HappyHour::active()->whereIn('zone_id', $zoneIds)
            ->whereHas('enrollments', fn ($q) => $this->servableBy($q, $zoneIds))
            ->get()
            ->first(fn ($candidate) => $candidate->isRunningNow());
    }
}
