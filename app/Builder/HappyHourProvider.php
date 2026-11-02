<?php

namespace App\Builder;

use App\CentralLogics\Helpers;
use App\Models\HappyHour;
use App\Models\Restaurant;
use Carbon\Carbon;
use Modules\Builder\Contracts\HappyHourProvider as HappyHourProviderContract;
use Modules\Builder\ValueObjects\StorefrontScope;

/**
 * StackFood happy hour - the storefront's read-only view of it.
 *
 * A storefront is one restaurant, so there is no zone scan to do: whether a happy hour is
 * running is asked of that restaurant alone, through Helpers::get_running_happy_hour(), the
 * same resolver every product price on the site goes through. The banner therefore cannot
 * advertise a rate the menu is not already charging.
 *
 * The window end is derived the same way Api\V1\HappyHourController::windowPayload() derives
 * it, so the storefront countdown and the customer app countdown agree to the second.
 */
class HappyHourProvider implements HappyHourProviderContract
{
    public function running(?StorefrontScope $scope): ?array
    {
        $restaurantId = $scope?->subTenantId;

        if (! $restaurantId) {
            return null;
        }

        try {
            $restaurant = Restaurant::query()->find($restaurantId);

            if (! $restaurant) {
                return null;
            }

            $happyHour = Helpers::get_running_happy_hour($restaurant);

            // A happy hour configured at 0% changes no price, so there is nothing to
            // announce - the host's pricing resolver treats it as no happy hour too.
            if (! $happyHour || $happyHour->discount <= 0) {
                return null;
            }

            $endsAt = $this->windowEnd($happyHour);

            // The lookup says the window is open, so an end that has already passed means
            // the two disagree (a schedule edited mid-window, a missing dated row). Rather
            // than render a banner counting down from zero, show nothing.
            $remainingSeconds = $endsAt ? (int) now()->diffInSeconds($endsAt, false) : 0;

            if ($remainingSeconds <= 0) {
                return null;
            }

            return [
                'id' => (int) $happyHour->id,
                'title' => (string) $happyHour->title,
                'shortDescription' => $happyHour->short_description,
                'discount' => (float) $happyHour->discount,
                'minOrderAmount' => $happyHour->min_order_amount !== null
                    ? (float) $happyHour->min_order_amount
                    : null,
                'iconUrl' => $happyHour->icon_full_url,
                'coverImageUrl' => $happyHour->cover_image_full_url,
                'endsAt' => $endsAt->format('Y-m-d H:i:s'),
                // What the countdown ticks down from. The client cannot trust its own clock
                // against the server's, so it counts this down rather than diffing endsAt.
                'remainingSeconds' => $remainingSeconds,
            ];
        } catch (\Throwable $e) {
            // A home page must never fail over a promotion lookup.
            \info(['storefront_happy_hour' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * When the window that is open right now closes.
     *
     * A permanent weekly rule writes no dated rows and carries its times on the definition;
     * everything else is expanded into happy_hour_dates. The dated row is matched on the
     * clock as well as the date, with the same predicate isRunningNow() uses, so the two can
     * never disagree - a custom schedule may legitimately put two windows on one day, and
     * taking the day's first row would then count down to the wrong one.
     *
     * A window never crosses midnight - the admin form caps a start at 22:59 - so today's
     * date plus the end time is always the right instant.
     */
    private function windowEnd(HappyHour $happyHour): ?Carbon
    {
        $now = now();
        $today = $now->toDateString();

        $row = $happyHour->is_permanent
            ? null
            : $happyHour->dates()
                ->whereDate('applicable_date', $today)
                ->whereTime('start_time', '<=', $now->format('H:i:s'))
                ->whereTime('end_time', '>=', $now->format('H:i:s'))
                ->first();

        $endTime = $row?->end_time ?? $happyHour->end_time;

        if (! $endTime) {
            return null;
        }

        return Carbon::parse($today.' '.$endTime);
    }
}
