<?php

namespace App\CentralLogics;

use App\Models\HappyHour;
use Illuminate\Support\Facades\DB;

/**
 * Whether two happy hours would ever be live at the same moment.
 *
 * The admin side already refuses to create two overlapping happy hours in one zone, and a
 * restaurant only ever sees its own zone's offers, so in the ordinary case a restaurant's
 * enrolments cannot collide. That guarantee is a property of the zone, not of the
 * restaurant: a store moved to another zone keeps the enrolments it made in the old one, and
 * those were never checked against the offers it can join now. This answers the question
 * from the restaurant's own set of enrolments instead, so the join path does not have to
 * assume the zone rule held.
 *
 * Schedules come in two shapes and every pairing has to be handled:
 *   dated (dated / weekly / custom) - one expanded row per occurrence in happy_hour_dates
 *   permanent weekly                - no rows at all, matched from weekly_days + start/end
 *
 * Comparisons are half-open (start < end AND end > start), the same as the admin's zone
 * guard, so back-to-back windows - 16:00-17:00 and 17:00-18:00 - are not a clash. Past
 * occurrences are ignored: a window that has already run cannot collide with one the
 * restaurant is joining now.
 */
class HappyHourScheduleLogic
{
    /** MySQL DAYOFWEEK(): 1 = Sunday through 7 = Saturday. */
    private const WEEKDAY_NUMBERS = [
        'Sunday' => 1,
        'Monday' => 2,
        'Tuesday' => 3,
        'Wednesday' => 4,
        'Thursday' => 5,
        'Friday' => 6,
        'Saturday' => 7,
    ];

    /**
     * The first of $otherIds whose window collides with $candidate's, or null when none do.
     *
     * Returns the clashing happy hour together with the occurrence that collides, because
     * naming the offer alone leaves the vendor to work out which of its windows is in the
     * way - and a custom schedule keeps its times on the dated rows rather than on the
     * record, so there is nothing on the offer itself to quote.
     *
     * @return array{happy_hour: HappyHour, window: string}|null
     */
    public static function firstClash(HappyHour $candidate, array $otherIds): ?array
    {
        $otherIds = array_values(array_diff(
            array_unique(array_map('intval', $otherIds)),
            [(int) $candidate->id]
        ));

        if (! $otherIds) {
            return null;
        }

        $clash = self::isPermanentWeekly($candidate)
            ? self::clashAgainstPermanentCandidate($candidate, $otherIds)
            : self::clashAgainstDatedCandidate($candidate, $otherIds);

        if (! $clash) {
            return null;
        }

        $happyHour = HappyHour::find($clash['happy_hour_id']);

        if (! $happyHour) {
            return null;
        }

        return [
            'happy_hour' => $happyHour,
            'window' => self::windowLabel($clash['date'], $clash['start_time'], $clash['end_time']),
        ];
    }

    /**
     * A permanent weekly rule is matched from its definition against both shapes: other
     * standing rules by weekday, and dated rows by the weekday each date falls on.
     */
    private static function clashAgainstPermanentCandidate(HappyHour $candidate, array $otherIds): ?array
    {
        $weekdays = $candidate->weekly_days ?? [];

        if (! $weekdays || ! $candidate->start_time || ! $candidate->end_time) {
            return null;
        }

        foreach (self::permanentRules($otherIds) as $other) {
            if (array_intersect($weekdays, $other->weekly_days ?? [])
                && self::timesOverlap($candidate->start_time, $candidate->end_time, $other->start_time, $other->end_time)) {
                return [
                    'happy_hour_id' => $other->id,
                    'date' => null,
                    'start_time' => $other->start_time,
                    'end_time' => $other->end_time,
                ];
            }
        }

        $numbers = self::weekdayNumbers($weekdays);

        if (! $numbers) {
            return null;
        }

        // DAYOFWEEK rather than DAYNAME: DAYNAME follows lc_time_names and would stop
        // matching the English weekdays we store the moment that server var differs.
        $row = DB::table('happy_hour_dates')
            ->whereIn('happy_hour_id', $otherIds)
            ->whereDate('applicable_date', '>=', now()->toDateString())
            ->whereRaw('DAYOFWEEK(applicable_date) IN ('.implode(',', $numbers).')')
            ->where('start_time', '<', $candidate->end_time)
            ->where('end_time', '>', $candidate->start_time)
            ->orderBy('applicable_date')
            ->orderBy('start_time')
            ->first();

        return $row ? [
            'happy_hour_id' => $row->happy_hour_id,
            'date' => $row->applicable_date,
            'start_time' => $row->start_time,
            'end_time' => $row->end_time,
        ] : null;
    }

    /**
     * Everything that is not a standing rule has its occurrences on the table, so its own
     * rows are compared to the others' rows directly, and to standing rules by weekday.
     */
    private static function clashAgainstDatedCandidate(HappyHour $candidate, array $otherIds): ?array
    {
        $today = now()->toDateString();

        $row = DB::table('happy_hour_dates as mine')
            ->join('happy_hour_dates as theirs', function ($join) {
                $join->on('mine.applicable_date', '=', 'theirs.applicable_date')
                    ->on('mine.start_time', '<', 'theirs.end_time')
                    ->on('mine.end_time', '>', 'theirs.start_time');
            })
            ->where('mine.happy_hour_id', $candidate->id)
            ->whereIn('theirs.happy_hour_id', $otherIds)
            ->whereDate('mine.applicable_date', '>=', $today)
            ->orderBy('mine.applicable_date')
            ->orderBy('theirs.start_time')
            ->select('theirs.happy_hour_id', 'theirs.applicable_date', 'theirs.start_time', 'theirs.end_time')
            ->first();

        if ($row) {
            return [
                'happy_hour_id' => $row->happy_hour_id,
                'date' => $row->applicable_date,
                'start_time' => $row->start_time,
                'end_time' => $row->end_time,
            ];
        }

        foreach (self::permanentRules($otherIds) as $other) {
            $numbers = self::weekdayNumbers($other->weekly_days ?? []);

            if (! $numbers || ! $other->start_time || ! $other->end_time) {
                continue;
            }

            $hit = DB::table('happy_hour_dates')
                ->where('happy_hour_id', $candidate->id)
                ->whereDate('applicable_date', '>=', $today)
                ->whereRaw('DAYOFWEEK(applicable_date) IN ('.implode(',', $numbers).')')
                ->where('start_time', '<', $other->end_time)
                ->where('end_time', '>', $other->start_time)
                ->orderBy('applicable_date')
                ->first();

            if ($hit) {
                // The date comes from this offer's occurrence; the window is the standing
                // rule's, which is what the vendor would have to leave to get past it.
                return [
                    'happy_hour_id' => $other->id,
                    'date' => $hit->applicable_date,
                    'start_time' => $other->start_time,
                    'end_time' => $other->end_time,
                ];
            }
        }

        return null;
    }

    private static function permanentRules(array $ids)
    {
        return HappyHour::whereIn('id', $ids)
            ->where('is_permanent', 1)
            ->where('duration_type', 'weekly')
            ->get();
    }

    /**
     * Matches isRunningNow(): only a weekly rule marked permanent is evaluated from its
     * definition, everything else resolves from the expanded table.
     */
    private static function isPermanentWeekly(HappyHour $happyHour): bool
    {
        return (bool) $happyHour->is_permanent && $happyHour->duration_type === 'weekly';
    }

    private static function timesOverlap(?string $aStart, ?string $aEnd, ?string $bStart, ?string $bEnd): bool
    {
        if (! $aStart || ! $aEnd || ! $bStart || ! $bEnd) {
            return false;
        }

        return $aStart < $bEnd && $aEnd > $bStart;
    }

    private static function weekdayNumbers(array $weekdays): array
    {
        return array_values(array_filter(array_map(
            fn ($day) => self::WEEKDAY_NUMBERS[$day] ?? null,
            $weekdays
        )));
    }

    /**
     * A standing rule repeats every week, so it has no one date to name - only its hours.
     */
    private static function windowLabel(?string $date, ?string $startTime, ?string $endTime): string
    {
        $hours = trim(sprintf(
            '%s - %s',
            $startTime ? date('g:i A', strtotime($startTime)) : '',
            $endTime ? date('g:i A', strtotime($endTime)) : ''
        ), ' -');

        if (! $date) {
            return $hours;
        }

        return trim(date('M j', strtotime($date)).', '.$hours, ', ');
    }
}
