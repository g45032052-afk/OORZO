<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * The free delivery setup of one zone.
 *
 * A zone holds at most one, and only while it is active does the zone give its
 * customers free delivery. The admin bears the cost, so an order freed here is
 * attributed to 'admin' exactly as the global free delivery setting is.
 */
class FreeDelivery extends Model
{
    use HasFactory;

    /** Every order in the zone rides free. */
    public const TYPE_ALL = 'all_restaurant';

    /** Only orders that reach `minimum_order_amount` ride free. */
    public const TYPE_CRITERIA = 'specific_criteria';

    /** Every value `type` may hold — the validation whitelist. */
    public const TYPES = [self::TYPE_ALL, self::TYPE_CRITERIA];

    protected $casts = [
        'zone_id' => 'integer',
        'status' => 'integer',
        'minimum_order_amount' => 'float',
    ];

    protected $guarded = ['id'];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }

    /**
     * The name shipped clients know this type by.
     *
     * Free delivery used to be one global business setting whose value the apps
     * and the web switch on - `free_delivery_to_all_store` /
     * `free_delivery_by_specific_criteria`. Moving it per zone renamed the type
     * in the column, but `admin_free_delivery.type` in the config api is the old
     * contract, so it keeps answering in the old words.
     */
    public function apiType(): string
    {
        return $this->type === self::TYPE_CRITERIA
            ? 'free_delivery_by_specific_criteria'
            : 'free_delivery_to_all_store';
    }

    /** Label shown in the list's Free Delivery Type column. */
    public function typeLabel(): string
    {
        return $this->type === self::TYPE_CRITERIA
            ? translate('messages.Specific_Criteria')
            : translate('messages.All_Restaurant');
    }

    /**
     * True when the setup frees every order regardless of what it costs.
     *
     * A criteria setup without an amount is one of these. The form now demands
     * the amount, so only rows saved before that rule can reach this branch -
     * they keep the old reading rather than silently freeing nothing.
     */
    public function coversAnyAmount(): bool
    {
        return $this->type === self::TYPE_ALL || ! ($this->minimum_order_amount > 0);
    }

    /**
     * Whether an order of this amount rides free.
     *
     * A null amount means the caller has no cart to weigh — a restaurant list,
     * say — so only a setup that frees everything can answer yes.
     */
    public function appliesTo(?float $orderAmount = null): bool
    {
        if ($this->coversAnyAmount()) {
            return true;
        }

        return $orderAmount !== null && $orderAmount >= $this->minimum_order_amount;
    }
}
