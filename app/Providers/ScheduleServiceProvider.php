<?php

namespace App\Providers;

use App\Models\BusinessSetting;
use App\Models\DataSetting;
use App\Models\NotificationMessage;
use App\Support\DisbursementScheduleResolver;
use Cron\CronExpression;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

/**
 * Central scheduler for StackFood's automated tasks. Replaces the legacy
 * per-command OS-crontab entries that were installed via exec() in
 * BusinessSettingsController. A single `* * * * * php artisan schedule:run`
 * crontab line now drives everything; each task's cadence is evaluated live in
 * its when() gate from admin settings, so changes take effect immediately under
 * both cron-driven schedule:run and supervisor-driven schedule:work.
 */
class ScheduleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->command('dm:disbursement')
                ->everyMinute()
                ->when(fn () => $this->disbursementShouldFire('dm'))
                ->runInBackground()
                ->withoutOverlapping(10)
                ->appendOutputTo(storage_path('logs/schedule.log'));

            $schedule->command('restaurant:disbursement')
                ->everyMinute()
                ->when(fn () => $this->disbursementShouldFire('restaurant'))
                ->runInBackground()
                ->withoutOverlapping(10)
                ->appendOutputTo(storage_path('logs/schedule.log'));

            $schedule->command('customer-subscription:reminder')
                ->everyMinute()
                ->when(fn () => $this->subscriptionShouldFire())
                ->runInBackground()
                ->withoutOverlapping(10)
                ->appendOutputTo(storage_path('logs/schedule.log'));

            $schedule->command('cart:reminder')
                ->everyMinute()
                ->when(fn () => $this->cartReminderShouldFire())
                ->runInBackground()
                ->withoutOverlapping(10)
                ->appendOutputTo(storage_path('logs/schedule.log'));
        });
    }

    private function disbursementShouldFire(string $prefix): bool
    {
        if (BusinessSetting::where('key', 'disbursement_type')->value('value') !== 'automated') {
            return false;
        }

        $expression = $prefix === 'dm'
            ? DisbursementScheduleResolver::forDeliveryMan()
            : DisbursementScheduleResolver::forRestaurant();

        return (new CronExpression($expression))->isDue(now()->setSecond(0));
    }

    private function subscriptionShouldFire(): bool
    {
        $beforeTime = (int) (DataSetting::where(['key' => 'subscription_reminder_before_time', 'type' => 'notification_settings'])->value('value') ?? 0);
        if ($beforeTime <= 0) {
            return false;
        }

        // Mirror the gate in App\Traits\ManagesProCustomerSubscription.
        $enabled = NotificationMessage::where('key', 'subscription_expire_reminder')
            ->where('user_type', 'user')
            ->where('status', 1)
            ->exists();
        if (!$enabled) {
            return false;
        }

        $unit = DataSetting::where(['key' => 'subscription_reminder_before', 'type' => 'notification_settings'])->value('value') ?? 'days';
        $now = now();

        return match ($unit) {
            'min'   => true,
            'hour'  => $now->minute === 0,
            default => $now->hour === 0 && $now->minute === 0,
        };
    }

    private function cartReminderShouldFire(): bool
    {
        // NotificationController::sendCartAbandonNotification() no-ops when the
        // "after time" is 0, so gating on it here avoids waking the command for
        // nothing. It runs every minute otherwise (the command dedupes users
        // already notified).
        $afterTime = (int) (DataSetting::where(['key' => 'cart_reminder_after_time', 'type' => 'notification_settings'])->value('value') ?? 0);

        return $afterTime > 0;
    }
}
