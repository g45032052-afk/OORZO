<?php

namespace Modules\AI\app\Support;

use App\Models\Food;
final class FoodAvailability
{
    /**
     * Item availability that also accounts for the item's restaurant being
     * closed right now (temporarily paused, or outside opening hours when
     * scheduled ordering is off). Use this wherever items are offered so a
     * closed restaurant's items are never presented as orderable.
     *
     * The item's restaurant should be loaded WITH its `active` column (and, when
     * scheduled ordering is disabled, its `schedules`); otherwise the store is
     * conservatively treated as closed.
     */
    public static function withStore(Food $item): array
    {
        $availability = self::of($item);

        if ($availability['available'] && $item->restaurant) {
            $store = StoreAvailability::of($item->restaurant);
            if (! $store['open']) {
                return ['available' => false, 'reason' => $store['reason'], 'label' => $store['label']];
            }
        }

        return $availability;
    }

    public static function of(Food $item): array
    {
        $startsAt = $item->getAttribute('available_time_starts');
        $endsAt   = $item->getAttribute('available_time_ends');

        if ($startsAt && $endsAt) {
            $now = now()->format('H:i:s');
            if ($now < $startsAt || $now > $endsAt) {
                $window = date('g:i A', strtotime((string) $startsAt)) . '–' . date('g:i A', strtotime((string) $endsAt));

                return [
                    'available' => false,
                    'reason'    => 'outside_serving_time',
                    'label'     => 'not available now — only served ' . $window,
                ];
            }
        }

        if (self::itemStock($item) === 0) {
            return ['available' => false, 'reason' => 'out_of_stock', 'label' => 'out of stock'];
        }

        if (self::allRequiredOptionsSoldOut($item)) {
            return ['available' => false, 'reason' => 'options_out_of_stock', 'label' => 'all options out of stock'];
        }

        return ['available' => true, 'reason' => null, 'label' => null];
    }

    public static function itemStock(Food $item): ?int
    {
        $stockType = (string) $item->getAttribute('stock_type');

        if ($stockType === '' || $stockType === 'unlimited') {
            return null;
        }

        return max(0, (int) $item->getAttribute('item_stock'));
    }

    public static function optionStock(array $option): ?int
    {
        $stockType = (string) ($option['stock_type'] ?? '');

        if ($stockType === '' || $stockType === 'unlimited') {
            return null;
        }

        return max(0, (int) ($option['total_stock'] ?? 0) - (int) ($option['sell_count'] ?? 0));
    }

    public static function optionSoldOut(array $option): bool
    {
        return self::optionStock($option) === 0;
    }

    public static function allRequiredOptionsSoldOut(Food $item): bool
    {
        foreach (self::variations($item) as $group) {
            if (! self::isRequired($group)) {
                continue;
            }

            $options = $group['values'] ?? [];
            if (empty($options)) {
                continue;
            }

            $inStock = array_filter($options, static fn ($option) => ! self::optionSoldOut((array) $option));
            if (empty($inStock)) {
                return true;
            }
        }

        return false;
    }

    public static function variations(Food $item): array
    {
        $raw = $item->getAttribute('variations');

        if (empty($raw)) {
            return [];
        }

        $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function isRequired(array $group): bool
    {
        $required = $group['required'] ?? 'off';

        return $required === 'on' || $required === true || $required === 1 || $required === '1';
    }
}
