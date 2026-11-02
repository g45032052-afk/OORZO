<?php

namespace App\Builder;

use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\Models\DataSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\Builder\Contracts\MaintenanceProvider as MaintenanceProviderContract;
use Modules\Builder\ValueObjects\MaintenanceState;
use Modules\Builder\ValueObjects\StorefrontScope;

/**
 * StackFood maintenance adapter — gates the builder storefront on the admin
 * maintenance feature when the dedicated `vendor_storefront` system is selected.
 * Master switch = `maintenance_mode` business setting; systems/duration/message
 * live in `data_settings(type=maintenance_mode)` (read through the same
 * `data_settings_maintenance_mode` cache the mobile API config uses).
 *
 * Duration semantics match the host MaintenanceMode middleware: 'until_change'
 * is always live; a dated window is live only between start and end.
 *
 * NOTE: to fully wire the storefront maintenance UX, add `vendor_storefront` to
 * StackFood's admin SystemController $systems + a module-gated checkbox in the
 * business-settings maintenance blade (see the 6amMart change for reference).
 */
class MaintenanceProvider implements MaintenanceProviderContract
{
    private const SYSTEM_KEY = 'vendor_storefront';

    public function state(?StorefrontScope $scope = null): MaintenanceState
    {
        if (! (int) (Helpers::get_business_settings('maintenance_mode') ?? 0)) {
            return MaintenanceState::inactive();
        }

        $data = $this->maintenanceData();

        $systems = $this->decode($data['maintenance_system_setup'] ?? null, []);
        if (! \in_array(self::SYSTEM_KEY, (array) $systems, true)) {
            return MaintenanceState::inactive();
        }

        $duration = $this->decode($data['maintenance_duration_setup'] ?? null, []);
        if (! $this->withinWindow($duration)) {
            return MaintenanceState::inactive();
        }

        $message = $this->decode($data['maintenance_message_setup'] ?? null, []);

        return new MaintenanceState(
            active:  true,
            title:   $message['maintenance_message'] ?? null,
            body:    $message['message_body'] ?? null,
            endDate: $this->endDate($duration),
            phone:   ! empty($message['business_number'])
                ? (BusinessSetting::where('key', 'phone')->value('value') ?: null)
                : null,
            email:   ! empty($message['business_email'])
                ? (BusinessSetting::where('key', 'email')->value('value') ?: null)
                : null,
        );
    }

    /** @return array<string,mixed> */
    private function maintenanceData(): array
    {
        return Cache::rememberForever('data_settings_maintenance_mode', function () {
            return DataSetting::where('type', 'maintenance_mode')
                ->whereIn('key', [
                    'maintenance_system_setup',
                    'maintenance_duration_setup',
                    'maintenance_message_setup',
                ])
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    private function withinWindow(array $duration): bool
    {
        if (($duration['maintenance_duration'] ?? null) === 'until_change') {
            return true;
        }

        $start = $duration['start_date'] ?? null;
        $end   = $duration['end_date'] ?? null;
        if (! $start || ! $end) {
            return false;
        }

        try {
            return Carbon::now()->between(Carbon::parse($start), Carbon::parse($end));
        } catch (\Throwable) {
            return false;
        }
    }

    private function endDate(array $duration): ?string
    {
        if (($duration['maintenance_duration'] ?? null) === 'until_change') {
            return null;
        }

        $end = $duration['end_date'] ?? null;
        if (! $end) {
            return null;
        }

        try {
            return Carbon::parse($end)->toIso8601String();
        } catch (\Throwable) {
            return null;
        }
    }

    private function decode(mixed $value, mixed $fallback): mixed
    {
        if (\is_array($value)) {
            return $value;
        }
        if (\is_string($value)) {
            $decoded = \json_decode($value, true);
            return \is_null($decoded) ? $fallback : $decoded;
        }
        return $fallback;
    }
}
