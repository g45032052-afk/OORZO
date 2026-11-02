<?php

namespace App\CentralLogics;

use App\Models\EtaConfiguration;
use App\Models\Order;
use App\Models\ZoneDeliveryOption;
use Carbon\Carbon;

/**
 * The estimated delivery time a customer is shown.
 *
 * One place, so the app, the web frontend and anything added later cannot drift
 * apart. Everything here is minutes; the only figure that arrives in another
 * unit is the order's `delivery_duration`, which the app takes from the map API
 * in seconds and which is converted once, on the way in.
 *
 * The estimate is the travel time plus the two buffers, and the method decides
 * only where the travel time is read from — the map, or the restaurant's own
 * delivery time. Before the kitchen starts, the configuration's preparation
 * buffer stands in for how long cooking will take; once the restaurant has said
 * how long it needs, that figure replaces the buffer.
 *
 * Nothing is estimated for a zone whose admin has not set one up: silence is
 * correct where a made-up number is not.
 */
class EtaLogic
{
    /** Statuses past which there is nothing left to estimate. */
    private const FINISHED = [
        'delivered', 'canceled', 'failed', 'refunded', 'refund_requested', 'refund_request_canceled',
    ];

    /** Statuses at or beyond the kitchen starting work. */
    private const PROCESSED = ['processing', 'handover', 'picked_up'];

    /**
     * How far ahead of now an overdue order's window is closed off, in minutes.
     *
     * Short on purpose: past its window the order is on its way rather than being
     * estimated afresh, and the only honest thing left to say is "any minute now".
     */
    private const OVERDUE_MINUTES = 5;

    /**
     * The zone's live configuration, or null.
     *
     * A zone may hold several configurations but estimates from the one that is
     * switched on, and the panel keeps exactly one of a zone's set active — which
     * is what makes a single first() the whole answer.
     */
    public static function forZone($zoneId): ?EtaConfiguration
    {
        if (! $zoneId) {
            return null;
        }

        return EtaConfiguration::active()->where('zone_id', $zoneId)->first();
    }

    /**
     * The whole `eta` object for one order, or null when none can be made.
     *
     * @return array{min:int,max:int,unit:string,text:string,from:string,to:string,window:string,from_at:string,to_at:string,timezone:string,stage:string}|null
     */
    public static function forOrder(Order $order): ?array
    {
        if ($order->order_type !== 'delivery' || in_array($order->order_status, self::FINISHED, true)) {
            return null;
        }

        // The inputs this order was quoted against, if they were frozen at placement. Orders
        // placed before the freeze shipped have none and keep reading the live configuration.
        $frozen = is_array($order->eta_snapshot) && $order->eta_snapshot !== [] ? $order->eta_snapshot : null;

        $eta = $frozen ? self::hydrate($frozen) : self::forZone($order->zone_id);

        if (! $eta) {
            return null;
        }

        // The restaurant's own figure is only trusted once it has been written;
        // a status that ran ahead of the write keeps the buffer rather than
        // adding a zero.
        $processing = in_array($order->order_status, self::PROCESSED, true) && (int) $order->processing_time > 0;
        $head = $processing ? (int) $order->processing_time : (int) $eta->preparation_buffer;

        $range = self::range($order, $eta, $head, $frozen);

        if (! $range) {
            return null;
        }

        return self::present($order, $range[0], $range[1], $processing ? 'processing' : 'before_processing');
    }

    /**
     * A restaurant's `delivery_time` as [min, max] minutes.
     *
     * Stored as `30-40-min` or `10-25-hours`, and the unit suffix is real — the
     * readers elsewhere in the panel drop it, so an hours restaurant reads as
     * minutes to them. Here it does not.
     *
     * @return array{0:?int,1:?int}
     */
    public static function parseDeliveryTime(?string $value): array
    {
        if (! $value || ! preg_match('/(\d+(?:\.\d+)?)\D+(\d+(?:\.\d+)?)/', $value, $matches)) {
            return [null, null];
        }

        $factor = str_contains(strtolower($value), 'hour') ? 60 : 1;

        return [(int) round($matches[1] * $factor), (int) round($matches[2] * $factor)];
    }

    /*
    |--------------------------------------------------------------------------
    | Internals
    |--------------------------------------------------------------------------
    */

    /**
     * The range in minutes.
     *
     * The floor is applied to the subtotal *before* the gap widens it: the gap
     * is how far apart the two ends of the range sit, so clamping afterwards
     * would hand back a range narrower than the zone asked for.
     *
     * @return array{0:int,1:int}|null
     */
    private static function range(Order $order, EtaConfiguration $eta, int $head, ?array $frozen = null): ?array
    {
        $transit = (int) $eta->transit_buffer;
        $floor = (int) $eta->minimum_delivery_time;

        if ($eta->calculation_method === EtaConfiguration::METHOD_DISTANCE && is_numeric($order->delivery_duration)) {
            $subtotal = self::minutes($order->delivery_duration) + $head + $transit;
            $min = max(self::saverAdjusted($order, $subtotal, $frozen), $floor);

            return [$min, $min + (int) $eta->time_gap];
        }

        // Either the zone estimates from the restaurant's own delivery time, or
        // the order carries no travel time to estimate from — an app build older
        // than the field, say. Falling back beats answering nothing.
        // The restaurant's figure as it stood when the order was placed. It is a configuration
        // value like any other here, so an admin editing it must not move an in-flight estimate.
        [$restMin, $restMax] = self::parseDeliveryTime(
            $frozen ? ($frozen['restaurant_delivery_time'] ?? null) : $order->restaurant?->delivery_time
        );

        // A restaurant with no usable delivery_time still has to produce an estimate: returning
        // null here surfaced as a blank ETA to the customer. The zone's own minimum is the
        // sensible floor to fall back on, and the configured gap gives it a range; with neither
        // set the column default (30-40) is used so the answer is never blank or zero.
        if ($restMin === null) {
            $fallbackMin = $floor > 0 ? $floor : 30;
            $gap = (int) $eta->time_gap;
            $restMin = $fallbackMin;
            $restMax = $fallbackMin + ($gap > 0 ? $gap : 10);
            $head = 0;
            $transit = 0;
        }

        $min = max(self::saverAdjusted($order, $restMin + $head + $transit, $frozen), $floor);
        $max = max(self::saverAdjusted($order, $restMax + $head + $transit, $frozen), $min);

        return [$min, $max];
    }

    /** Seconds to minutes. Half a minute of travel is still a minute of waiting. */
    private static function minutes($seconds): int
    {
        return (int) round(((int) $seconds) / 60);
    }

    /**
     * Express and slightly delayed delivery move the estimate.
     *
     * The customer paid for the faster one, so the time they are shown has to
     * move with it. Self delivering restaurants are left alone, exactly as the
     * saver delivery charge leaves them alone.
     */
    private static function saverAdjusted(Order $order, int $minutes, ?array $frozen = null): int
    {
        $shift = $frozen !== null ? (int) ($frozen['saver_shift'] ?? 0) : self::saverShift($order);

        return max(0, $minutes + $shift);
    }

    /**
     * How far the order's saver choice moves the estimate, in signed minutes.
     *
     * Negative for express, positive for slightly delayed, zero for anything else. Read from the
     * live zone delivery option, so this is what {@see snapshotFor()} captures at placement -
     * afterwards the frozen figure is used and editing the option no longer moves the order.
     */
    private static function saverShift(Order $order): int
    {
        if (! $order->delivery_type || $order->restaurant?->sub_self_delivery == 1) {
            return 0;
        }

        $option = ZoneDeliveryOption::where('zone_id', $order->zone_id)
            ->where('delivery_type', $order->delivery_type)
            ->first();

        if (! $option) {
            return 0;
        }

        if ($order->delivery_type === 'express') {
            return -self::optionMinutes($option->reduce_delivery_time);
        }

        if ($order->delivery_type === 'slightly_delay') {
            return self::optionMinutes($option->add_delivery_time);
        }

        return 0;
    }

    /**
     * The ETA inputs to freeze onto an order at placement, or null when its zone has no
     * active configuration and there is nothing to quote against.
     *
     * Only configuration is captured. Everything that belongs to the order itself - its travel
     * duration, its processing time, its stage - stays live, so the estimate still narrows as
     * the restaurant accepts and cooks it. What stops moving is the effect of an admin editing
     * the zone's ETA setup, its Additional Charge setup, or the restaurant's delivery time.
     */
    public static function snapshotFor(Order $order): ?array
    {
        $eta = self::forZone($order->zone_id);

        if (! $eta) {
            return null;
        }

        return [
            'calculation_method' => $eta->calculation_method,
            'minimum_delivery_time' => (int) $eta->minimum_delivery_time,
            'preparation_buffer' => (int) $eta->preparation_buffer,
            'transit_buffer' => (int) $eta->transit_buffer,
            'time_gap' => (int) $eta->time_gap,
            'restaurant_delivery_time' => $order->restaurant?->delivery_time,
            'saver_shift' => self::saverShift($order),
        ];
    }

    /** A configuration object built from a frozen snapshot; never saved. */
    private static function hydrate(array $frozen): EtaConfiguration
    {
        $eta = new EtaConfiguration();

        $eta->calculation_method = $frozen['calculation_method'] ?? EtaConfiguration::METHOD_FIXED;
        $eta->minimum_delivery_time = (int) ($frozen['minimum_delivery_time'] ?? 0);
        $eta->preparation_buffer = (int) ($frozen['preparation_buffer'] ?? 0);
        $eta->transit_buffer = (int) ($frozen['transit_buffer'] ?? 0);
        $eta->time_gap = (int) ($frozen['time_gap'] ?? 0);

        return $eta;
    }

    /** ZoneDeliveryOption hands its times back as a value/unit pair. */
    private static function optionMinutes($value): int
    {
        if (is_array($value)) {
            $amount = (float) ($value['value'] ?? 0);

            return (int) round(($value['unit'] ?? 'min') === 'hour' ? $amount * 60 : $amount);
        }

        return (int) $value;
    }

    /**
     * The range, said both ways: minutes, and the clock window it lands on.
     *
     * @return array{min:int,max:int,unit:string,text:string,from:string,to:string,window:string,from_at:string,to_at:string,timezone:string,stage:string}
     */
    private static function present(Order $order, int $min, int $max, string $stage): array
    {
        $anchor = self::anchor($order, $stage, $max);
        $from = $anchor->copy()->addMinutes($min);
        $to = $anchor->copy()->addMinutes($max);

        // Once the far end of the window has gone by the order is overdue. The near
        // end stands — it is what the customer was promised and nothing about it
        // has changed — while the far end moves to a few minutes from now, so a
        // customer refreshing an hour late reads minutes rather than being quoted
        // the whole wait a second time.
        //
        // The two stay in order without needing a guard: overdue means now is past
        // anchor + max, which is at or past anchor + min, so the new far end is
        // always the later of the pair.
        //
        // isPast() compares the whole timestamp, so a window that closed yesterday
        // evening is overdue this morning; only the clock labels drop the date, and
        // from_at/to_at keep it.
        //
        // One rule for every kind of order. An instant one is overdue measured from
        // when it was placed, a scheduled or repeat one from the slot it was booked
        // for — the anchor differs, what "past" means does not.
        if ($to->isPast()) {
            $to = Carbon::now()->addMinutes(self::OVERDUE_MINUTES);
            // Restated from the anchor, so the minutes and the clock times keep
            // describing the same span. Cast because Carbon 3 hands back a float
            // where Carbon 2 gave an int, and `max` is published as whole minutes.
            $max = (int) round($anchor->diffInMinutes($to));
        }

        $unit = translate('messages.eta_minute_unit');
        $fromLabel = $from->format('h:i A');
        $toLabel = $to->format('h:i A');

        return [
            'min' => $min,
            'max' => $max,
            'unit' => $unit,
            'text' => $min === $max ? "{$min} {$unit}" : "{$min} - {$max} {$unit}",
            'from' => $fromLabel,
            'to' => $toLabel,
            'window' => $fromLabel === $toLabel ? $fromLabel : "{$fromLabel} - {$toLabel}",
            'from_at' => $from->toIso8601String(),
            'to_at' => $to->toIso8601String(),
            // The panel's own timezone: ConfigServiceProvider makes it PHP's
            // default, which is what every order timestamp is written in.
            // config('app.timezone') is still UTC and would be six hours wrong.
            'timezone' => date_default_timezone_get(),
            'stage' => $stage,
        ];
    }

    /**
     * What the window is measured from.
     *
     * The stage's own moment rather than now, so the window holds still and the
     * wait visibly shrinks; it moves once, when the kitchen starts and the
     * estimate is genuinely made again.
     *
     * A booked order — scheduled, or a repeat order's occurrence — is measured
     * from the slot it was booked for, at every stage including once the kitchen
     * has started. A restaurant may begin an order due tonight early in the
     * afternoon, and anchoring on the moment it began would promise the food
     * hours before the customer asked for it. Preparation and travel are added
     * on top of the slot, so `text` states the length of the wait and `window`
     * the clock times it lands on; for an order booked for later those two
     * describe the same span from different starting points, and a client
     * showing a countdown wants `from_at`, not `text`.
     *
     * Every one of these attributes is a string on this model — `created_at` has
     * an accessor that formats it and the status columns are not cast at all —
     * so each is parsed rather than used as a date.
     */
    private static function anchor(Order $order, string $stage, int $max): Carbon
    {
        $cooking = $stage === 'processing';

        if ($order->subscription_id) {
            // Once the kitchen has started, the cycle on the stove is the one that
            // slot belongs to however late it is running, so the roll-forward to the
            // next occurrence is suppressed and the search begins on the day work
            // started rather than today.
            $occurrence = $cooking
                ? self::occurrence($order, $max, self::dayOf($order->processing ?: $order->created_at), false)
                : self::occurrence($order, $max, Carbon::today(), true);

            if ($occurrence) {
                return $occurrence;
            }
        }

        $value = match (true) {
            self::booked($order) => $order->schedule_at,
            $cooking => $order->processing ?: $order->created_at,
            default => $order->created_at,
        };

        try {
            return $value ? Carbon::parse($value) : Carbon::now();
        } catch (\Exception $e) {
            return Carbon::now();
        }
    }

    /** The start of the day a stored timestamp falls on, today when it will not parse. */
    private static function dayOf($value): Carbon
    {
        try {
            return $value ? Carbon::parse($value)->startOfDay() : Carbon::today();
        } catch (\Exception $e) {
            return Carbon::today();
        }
    }

    /**
     * Whether the order is booked for a slot rather than wanted now.
     *
     * `scheduled` alone does not answer it: OrderLogic::create_subscription_log()
     * rolls a repeat order's `schedule_at` to the next occurrence but leaves the
     * flag at 0, so every subscription order looks unscheduled. The subscription
     * is what marks those.
     */
    private static function booked(Order $order): bool
    {
        return (bool) (($order->scheduled || $order->subscription_id) && $order->schedule_at);
    }

    /**
     * The next delivery slot of a repeat order, read from the subscription itself.
     *
     * `schedule_at` cannot answer this for a weekly or monthly subscription.
     * OrderLogic::create_subscription_log() rolls it forward only on a day the
     * subscription actually falls on — every other day it returns before writing
     * anything, leaving the previous occurrence in place — and its arithmetic
     * indexes a day-of-month into a list of weekday names, so a monthly order is
     * dated to the coming Sunday rather than the same date next month. The
     * schedule rows hold what the customer actually signed up for, so the
     * estimate is taken from them; how the deliveries themselves are booked is
     * left exactly as it was.
     *
     * Null when the subscription is over, has no schedule, or was cancelled
     * outright — the caller then falls back to `schedule_at` as before.
     *
     * @param Carbon $from    the day to search from: today while the order is
     *                        waiting, the day the kitchen started once it is cooking
     * @param bool   $advance whether a slot whose window has run out gives way to
     *                        the following occurrence. False once cooking has begun,
     *                        because the order on the stove belongs to that slot
     *                        however late it is running.
     */
    private static function occurrence(Order $order, int $max, Carbon $from, bool $advance): ?Carbon
    {
        $subscription = $order->subscription;

        if (! $subscription || $subscription->schedules->isEmpty()) {
            return null;
        }

        // A subscription that has not begun yet delivers no earlier than its start.
        if ($subscription->start_at && ($start = Carbon::parse($subscription->start_at)->startOfDay())->gt($from)) {
            $from = $start;
        }

        $end = $subscription->end_at ? Carbon::parse($subscription->end_at)->endOfDay() : null;
        $soonest = null;

        foreach ($subscription->schedules as $schedule) {
            $slot = self::slot($schedule, $subscription->type, $from, $max, $advance);

            if (! $slot || ($end && $slot->gt($end))) {
                continue;
            }

            if (! $soonest || $slot->lt($soonest)) {
                $soonest = $slot;
            }
        }

        return $soonest;
    }

    /**
     * One schedule row's current slot.
     *
     * Today's slot stays the answer while its own delivery window is still open
     * — the same `+ $max` test present() applies — so an order running late is
     * not shunted to the next cycle. Once that window has run out today's
     * delivery is done with, and the following occurrence is the one being
     * waited on.
     *
     * The row carries its own `type`, written beside the day it belongs to, so
     * that is preferred over the subscription's.
     */
    private static function slot($schedule, ?string $fallbackType, Carbon $from, int $max, bool $advance): ?Carbon
    {
        $type = $schedule->type ?: $fallbackType;
        $day = (int) $schedule->day;
        $time = $schedule->time ?: '00:00:00';

        $slot = self::at(self::slotDate($type, $day, $from), $time);

        if ($advance && $slot && $slot->copy()->addMinutes($max)->isPast()) {
            $slot = self::at(self::slotDate($type, $day, $slot->copy()->addDay()->startOfDay()), $time);
        }

        return $slot;
    }

    /** The first date on or after $from that this schedule falls on. */
    private static function slotDate(?string $type, int $day, Carbon $from): Carbon
    {
        return match ($type) {
            'weekly' => $from->copy()->addDays(((($day % 7) - (int) $from->dayOfWeek) + 7) % 7),
            'monthly' => self::monthlyDate($from, $day),
            // Daily, and anything unrecognised, falls on the day itself.
            default => $from->copy(),
        };
    }

    /** A date and a `H:i:s` joined, or null when the pair will not parse. */
    private static function at(Carbon $date, string $time): ?Carbon
    {
        try {
            return Carbon::parse($date->toDateString().' '.$time);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * The first date on or after $from landing on day-of-month $day.
     *
     * A short month is clamped rather than skipped: a subscription taken out on
     * the 31st still delivers in February, on the last day February has.
     */
    private static function monthlyDate(Carbon $from, int $day): Carbon
    {
        $day = max(1, min(31, $day));
        $candidate = $from->copy()->day(min($day, $from->daysInMonth));

        if ($candidate->lt($from)) {
            $next = $from->copy()->startOfMonth()->addMonthNoOverflow();
            $candidate = $next->day(min($day, $next->daysInMonth));
        }

        return $candidate;
    }
}
