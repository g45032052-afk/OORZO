<?php

namespace App\CentralLogics;

use App\Models\SurgePrice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Resolves the surge price in effect for a zone at a given moment, and applies
 * it to a delivery charge.
 *
 * Every engine that computes a delivery fee goes through here so the quoted fee
 * always equals the charged fee:
 *   - PlaceNewOrder::calculateDeliveryCharge()          web checkout + Builder quote
 *   - Api\V1\OrderController::claculate_original_delivery_fee()   customer API
 *   - Helpers::getDeliveryCharge-style list estimator    restaurant list display
 *
 * Admin and vendor POS go through the first of those and are surged like any
 * other order: the fee the operator is shown has to be the fee the order is
 * written with. See calculateDeliveryCharge's $applySurge parameter, which now
 * only exists for the display-only estimators.
 *
 * Static because two of the three call-sites are themselves static.
 */
class SurgePriceLogic
{
    /**
     * Per-request memo. The restaurant list resolves the same zone for dozens of
     * rows in one request; without this each row fires its own two queries.
     *
     * @var array<string, array>
     */
    private static array $memo = [];

    /**
     * Drop the memo.
     *
     * Entries are keyed to the minute, so a stale one can only survive within
     * the same minute for the same zone. That is harmless per request, but a
     * long-lived process (queue worker, Octane) should clear it after a surge
     * rule changes — and tests need it between assertions that move the clock.
     */
    public static function flushMemo(): void
    {
        self::$memo = [];
    }

    /**
     * The surge in effect for $zoneId at $datetime, or a zeroed payload.
     *
     * Resolution order:
     *   1. a materialised surge_price_dates row for this zone + date whose
     *      window contains the time, and whose parent rule is active;
     *   2. an active permanent weekly rule matching this weekday + time — those
     *      are never materialised because they have no end date;
     *   3. nothing.
     *
     * @return array{title:string,customer_note:string,customer_note_status:int,price:float,price_type:string}
     */
    public static function resolve($zoneId, $datetime = null): array
    {
        $zoneId = (int) $zoneId;
        if ($zoneId <= 0) {
            return self::empty();
        }

        try {
            $carbon = $datetime instanceof Carbon
                ? $datetime->copy()
                : ($datetime ? Carbon::parse($datetime) : Carbon::now());
        } catch (\Throwable $e) {
            $carbon = Carbon::now();
        }

        // Minute granularity is enough — surge windows are never sub-minute, and
        // it keeps the memo useful across a request.
        $key = $zoneId . '|' . $carbon->format('Y-m-d H:i');
        if (array_key_exists($key, self::$memo)) {
            return self::$memo[$key];
        }

        return self::$memo[$key] = self::lookup(
            $zoneId,
            $carbon->format('Y-m-d'),
            $carbon->format('H:i:s'),
            $carbon->format('l')
        );
    }

    private static function lookup(int $zoneId, string $date, string $time, string $weekday): array
    {
        // The overlap guard that admits these rows is half-open (start < end AND end > start),
        // so 10:00-11:00 and 11:00-12:00 are allowed to coexist - and at exactly 11:00 both
        // match this closed comparison. Ordering by the latest start makes the shared instant
        // belong to the window that begins on it, which is what [start, end) means, without
        // narrowing the comparison and losing each window's final minute.
        $surgePriceId = DB::table('surge_price_dates')
            ->where('zone_id', $zoneId)
            ->where('status', 1)
            ->where('applicable_date', $date)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->orderByDesc('start_time')
            ->value('surge_price_id');

        if ($surgePriceId) {
            $surge = SurgePrice::active()->find($surgePriceId);
            if ($surge) {
                return self::payload($surge);
            }
        }

        $permanent = SurgePrice::active()
            ->where('zone_id', $zoneId)
            ->where('duration_type', 'weekly')
            ->where('is_permanent', 1)
            ->whereJsonContains('weekly_days', $weekday)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->first();

        return $permanent ? self::payload($permanent) : self::empty();
    }

    private static function payload(SurgePrice $surge): array
    {
        return [
            'title' => (string) $surge->surge_price_name,
            'customer_note' => (string) ($surge->customer_note ?? ''),
            'customer_note_status' => (int) $surge->customer_note_status,
            'price' => (float) $surge->price,
            'price_type' => (string) $surge->price_type,
        ];
    }

    public static function empty(): array
    {
        return [
            'title' => '',
            'customer_note' => '',
            'customer_note_status' => 0,
            'price' => 0.0,
            'price_type' => 'amount',
        ];
    }

    /**
     * Add the surge to a delivery charge.
     *
     * A zero or negative fee is returned untouched: free delivery, pickup and
     * self-delivery orders are never surged, and surging 0 would invent a fee
     * where none was payable.
     */
    public static function applyToFee(float $fee, array $surge): float
    {
        $amount = (float) ($surge['price'] ?? 0);

        if ($fee <= 0 || $amount <= 0) {
            return $fee;
        }

        $extra = ($surge['price_type'] ?? 'amount') === 'percent'
            ? ($fee * $amount) / 100
            : $amount;

        return $fee + $extra;
    }

    /**
     * The note to show the customer beside the delivery charge, or null when
     * there is no surge or the admin left the note toggle off.
     */
    public static function customerNote(array $surge): ?string
    {
        if ((float) ($surge['price'] ?? 0) <= 0) {
            return null;
        }
        if ((int) ($surge['customer_note_status'] ?? 0) !== 1) {
            return null;
        }

        $note = trim((string) ($surge['customer_note'] ?? ''));

        return $note !== '' ? $note : null;
    }
}
