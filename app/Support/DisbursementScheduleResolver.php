<?php

namespace App\Support;

use App\Models\BusinessSetting;

/**
 * Builds the cron expression that governs how often each disbursement command
 * fires, derived live from the operator's Business Settings. Consumed by the
 * when() gates in App\Providers\ScheduleServiceProvider so changes made in the
 * admin panel take effect on the next scheduler tick — no crontab rewrite.
 */
class DisbursementScheduleResolver
{
    public static function forDeliveryMan(): string
    {
        return self::build('dm');
    }

    public static function forRestaurant(): string
    {
        return self::build('restaurant');
    }

    private static function build(string $prefix): string
    {
        $settings = BusinessSetting::whereIn('key', [
            "{$prefix}_disbursement_time_period",
            "{$prefix}_disbursement_week_start",
            "{$prefix}_disbursement_create_time",
        ])->pluck('value', 'key')->all();

        $frequency = $settings["{$prefix}_disbursement_time_period"] ?? 'daily';
        $weekStart = $settings["{$prefix}_disbursement_week_start"] ?? 'sunday';
        $createAt  = $settings["{$prefix}_disbursement_create_time"] ?? '00:00';

        [$hour, $min] = array_pad(explode(':', $createAt), 2, '0');
        $hour = (int) $hour;
        $min  = (int) $min;

        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $day  = array_search(strtolower($weekStart), $days, true);
        if ($day === false) {
            $day = 0;
        }

        // NOTE: kept identical to the 6amMart resolver for cross-project parity.
        // 'monthly' uses the 28-31 window (end-of-month); generate_disbursement()
        // is idempotent (paid amounts move to pending_withdraw) so the extra
        // ticks compute 0. Revisit in both projects together if changed.
        return match ($frequency) {
            'weekly'  => "{$min} {$hour} * * {$day}",
            'monthly' => "{$min} {$hour} 28-31 * *",
            default   => "{$min} {$hour} * * *",
        };
    }
}
