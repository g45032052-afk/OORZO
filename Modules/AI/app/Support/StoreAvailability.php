<?php

namespace Modules\AI\app\Support;

use App\CentralLogics\Helpers;
use App\Models\Restaurant;

/**
 * Whether a restaurant can take an order RIGHT NOW — the "store closed" case,
 * separate from item stock and an item's own serving-time window. Mirrors the
 * storefront: a restaurant is not orderable now when it is temporarily paused
 * (`active` = 0), or — when scheduled ordering is disabled — when the current
 * time falls outside today's opening schedule.
 */
final class StoreAvailability
{
    /**
     * @return array{open:bool, reason:?string, label:?string}
     */
    public static function of(Restaurant $restaurant): array
    {
        // Manual pause / vacation toggle — closed regardless of the schedule.
        if (! (bool) $restaurant->getAttribute('active')) {
            return ['open' => false, 'reason' => 'store_paused', 'label' => 'the restaurant is temporarily closed'];
        }

        // Schedule-window closure only blocks ordering when customers cannot
        // place scheduled (future) orders. With scheduled ordering enabled a
        // closed-by-schedule restaurant still accepts orders for later.
        if (! Helpers::schedule_order() && ! self::withinSchedule($restaurant)) {
            return ['open' => false, 'reason' => 'store_closed', 'label' => 'the restaurant is closed right now'];
        }

        return ['open' => true, 'reason' => null, 'label' => null];
    }

    public static function isOpen(Restaurant $restaurant): bool
    {
        return self::of($restaurant)['open'];
    }

    /**
     * Is "now" inside one of today's opening windows? No schedule rows means no
     * restriction is configured, so treat the restaurant as always open. Matches
     * Restaurant::scopeWithOpen (opening_time < now < closing_time).
     */
    private static function withinSchedule(Restaurant $restaurant): bool
    {
        $schedules = $restaurant->relationLoaded('schedules')
            ? $restaurant->getRelation('schedules')
            : $restaurant->schedules;

        if ($schedules === null || $schedules->isEmpty()) {
            return true;
        }

        $day = now()->dayOfWeek;
        $now = now()->format('H:i:s');

        foreach ($schedules as $schedule) {
            if ((int) $schedule->getAttribute('day') === $day
                && (string) $schedule->getAttribute('opening_time') < $now
                && (string) $schedule->getAttribute('closing_time') > $now) {
                return true;
            }
        }

        return false;
    }
}
