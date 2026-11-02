<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A delivery pricing rule for one zone.
 *
 * Only one rule per zone may be active; the active rule decides how that zone's
 * delivery charge is calculated.
 */
class DeliveryRule extends Model
{
    use HasFactory;

    public const METHOD_AREA = 'area_wise';
    public const METHOD_ZIP = 'zip_code_wise';
    public const METHOD_DISTANCE = 'distance_wise';
    public const METHOD_FIXED = 'fixed_amount';

    /** Every value `pricing_method` may hold — the validation whitelist. */
    public const METHODS = [
        self::METHOD_AREA,
        self::METHOD_ZIP,
        self::METHOD_DISTANCE,
        self::METHOD_FIXED,
    ];

    /** Methods whose amounts live in delivery_rule_charges rather than on the rule. */
    public const TABULAR_METHODS = [self::METHOD_AREA, self::METHOD_ZIP];

    protected $casts = [
        'zone_id' => 'integer',
        'status' => 'integer',
        'minimum_delivery_charge' => 'float',
        'per_km_charge' => 'float',
        'maximum_delivery_charge' => 'float',
        'fixed_charge' => 'float',
    ];

    protected $guarded = ['id'];

    /**
     * A zone never holds two active rules.
     *
     * Whichever path switched this one on — the status endpoint, a store, or an
     * update that moved the rule to another zone — the rest of that zone's rules
     * are switched off with it, so the invariant cannot be worked around.
     */
    protected static function booted()
    {
        static::saved(function (self $rule) {
            if (! $rule->status) {
                return;
            }

            static::where('zone_id', $rule->zone_id)
                ->where('id', '!=', $rule->id)
                ->where('status', 1)
                ->update(['status' => 0]);
        });
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function charges()
    {
        return $this->hasMany(DeliveryRuleCharge::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }

    /** True when the amounts are held per area or per zip code. */
    public function usesChargeTable(): bool
    {
        return in_array($this->pricing_method, self::TABULAR_METHODS, true);
    }

    /** Label shown in the list's Delivery Method column. */
    public function methodLabel(): string
    {
        return match ($this->pricing_method) {
            self::METHOD_AREA => translate('messages.Area_Wise'),
            self::METHOD_ZIP => translate('messages.Zip_Code_Wise'),
            self::METHOD_DISTANCE => translate('messages.Distance_Wise'),
            default => translate('messages.Fixed_Amount'),
        };
    }
}
