<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use App\Scopes\ZoneScope;
use Illuminate\Database\Eloquent\Builder;

class Zone extends Model
{
    use HasFactory;
    use HasSpatial;
    protected $with = ['translations'];

    protected $casts = [
        'id'=>'integer',
        'status'=>'integer',
        'minimum_shipping_charge'=>'float',
        'maximum_shipping_charge'=>'float',
        'per_km_shipping_charge'=>'float',
        'max_cod_order_amount'=>'float',
        'increased_delivery_fee'=>'float',
        'increased_delivery_fee_status'=>'integer',
        'incentive_status'=>'boolean',
        'coordinates' => Polygon::class,
        'additional_delivery_option_status' => 'boolean',
        'minimum_delivery_time' => 'integer',
        'minimum_delivery_charge' => 'float',
    ];

    protected $guarded = ['id'];


    public function scopeContains($query,$abc){
        return $query->whereRaw("ST_Distance_Sphere(coordinates, POINT({$abc}))");
    }
    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function restaurants()
    {
        return $this->hasMany(Restaurant::class);
    }

    public function deliverymen()
    {
        return $this->hasMany(DeliveryMan::class);
    }

    public function surge_prices()
    {
        return $this->hasMany(SurgePrice::class);
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, Restaurant::class);
    }


    public function campaigns()
    {
        return $this->hasManyThrough(Campaigns::class, Restaurant::class);
    }

    public function happyHours()
    {
        return $this->hasMany(HappyHour::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }

    public function deliveryRules()
    {
        return $this->hasMany(DeliveryRule::class);
    }

    /** A zone holds at most one free delivery setup. */
    public function freeDelivery()
    {
        return $this->hasOne(FreeDelivery::class);
    }

    /**
     * A zone holds as many ETA configurations as the admin cares to author, of
     * which exactly one is active - the same arrangement as its delivery rules.
     */
    public function etaConfigurations()
    {
        return $this->hasMany(EtaConfiguration::class);
    }

    /** The one the zone is estimating its delivery times from right now. */
    public function activeEtaConfiguration()
    {
        return $this->hasOne(EtaConfiguration::class)->where('status', 1);
    }

    /**
     * A zone is only set up once an admin has priced delivery inside it, which
     * means one active {@see DeliveryRule}. Without that there is no way to
     * charge for a delivery, so the zone must not serve anyone.
     */
    public function hasDeliveryChargeSetup(): bool
    {
        return $this->deliveryRules()->where('status', 1)->exists();
    }

    public function scopeWithDeliveryChargeSetup($query)
    {
        return $query->whereHas('deliveryRules', function ($q) {
            $q->where('status', 1);
        });
    }

    /**
     * A zone also has to be able to quote a delivery time, which means one
     * active {@see EtaConfiguration}. Without it an order has no ETA to show.
     */
    public function hasEtaSetup(): bool
    {
        return $this->etaConfigurations()->where('status', 1)->exists();
    }

    public function scopeWithEtaSetup($query)
    {
        return $query->whereHas('etaConfigurations', function ($q) {
            $q->where('status', 1);
        });
    }

    /**
     * What the zone is still missing before it may be switched on.
     *
     * Returns the setups that are absent, so a caller can name them rather than
     * saying only that something is wrong. Empty means the zone is ready.
     */
    public function readinessGaps(): array
    {
        $gaps = [];

        if (! $this->hasDeliveryChargeSetup()) {
            $gaps[] = 'delivery_rule';
        }

        if (! $this->hasEtaSetup()) {
            $gaps[] = 'eta';
        }

        return $gaps;
    }

    /** Both setups present, so the zone may serve customers. */
    public function isReadyToActivate(): bool
    {
        return $this->readinessGaps() === [];
    }

    /**
     * Why the zone may not be switched on, as a translation key.
     *
     * Names only what is actually absent — an admin who has already added one of
     * the two is not told to add it again. Every path that switches a zone on
     * reads from here, so the status toggle and "make default" cannot drift into
     * saying different things about the same zone.
     *
     * Pass the gaps when the caller has already computed them; readinessGaps()
     * costs two exists() queries and there is no reason to run them twice.
     */
    public function readinessMessageKey(?array $gaps = null): string
    {
        return match ($gaps ?? $this->readinessGaps()) {
            ['delivery_rule'] => 'messages.add_a_delivery_charge_setup_for_this_zone_before_activating_it',
            ['eta'] => 'messages.add_an_ETA_configuration_for_this_zone_before_activating_it',
            default => 'messages.add_a_delivery_charge_setup_and_an_ETA_configuration_for_this_zone_before_activating_it',
        };
    }

    /**
     * Switched on *and* priced — the only zones a customer may be routed into.
     * Use this instead of `active()` anywhere a zone decides what a customer
     * can see or order.
     *
     * The ETA requirement is enforced when a zone is switched on, not filtered
     * here: no zone has an ETA configuration yet, so adding withEtaSetup() would
     * make every zone unserviceable the moment this deploys. Once ETAs exist,
     * add it — a zone with no ETA cannot quote a delivery time.
     */
    public function scopeEffective($query)
    {
        return $query->active()->withDeliveryChargeSetup();
    }

    protected static function booted()
    {
        static::addGlobalScope(new ZoneScope);
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function($query){
                    return $query->where('locale', app()->getLocale());
            }]);
        });
    }
    public function incentives()
    {
        return $this->hasMany(Incentive::class)->orderBy('earning');
    }

    /**
     * The tiers a deliveryman of this zone can actually earn.
     *
     * Switching incentives off leaves the tiers where they are — see the
     * `incentive_status` migration — so every reader must ask this rather than
     * the relation, or a switched off zone would keep paying out.
     */
    public function activeIncentives()
    {
        return $this->incentives()->when(! $this->incentive_status, fn ($query) => $query->whereRaw('1 = 0'));
    }

    public function incentive_logs()
    {
        return $this->hasMany(IncentiveLog::class);
    }
    public static function query()
    {
        return parent::query();
    }
    public function getNameAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'name') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }
    public function getDisplayNameAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'display_name') {
                    return $translation['value'];
                }
            }
        }

        return $value ?? $this->name;
    }

    public function deliveryOptions()
    {
        return $this->hasMany(ZoneDeliveryOption::class);
    }

    public function setMinimumDeliveryTimeAttribute($value)
    {
        if (is_array($value)) {
            $time = $value['value'] ?? null;
            $unit = $value['unit'] ?? 'min';
            if (! is_numeric($time)) {
                $this->attributes['minimum_delivery_time'] = null;
                return;
            }
            $this->attributes['minimum_delivery_time'] = $unit === 'hour' ? $time * 60 : $time;
        } else {
            $this->attributes['minimum_delivery_time'] = is_numeric($value) ? $value : null;
        }
    }

    public function getMinimumDeliveryTimeAttribute($value)
    {
        if (is_numeric($value) && $value >= 60 && $value % 60 === 0) {
            return [
                'value' => $value / 60,
                'unit' => 'hour',
            ];
        }
        return [
            'value' => $value,
            'unit' => 'min',
        ];
    }

}
