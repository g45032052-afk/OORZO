<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One ETA configuration of one zone.
 *
 * A zone holds as many as the admin authors but estimates from exactly one - the
 * active one - and which that is, is settled by
 * {@see \App\Http\Controllers\Admin\EtaConfigurationController::status()}
 * rather than by anything stored here. Every stored figure is minutes.
 *
 * The estimate is always the floor plus both buffers plus the travel time; the
 * method decides only where that travel time is read from — the delivery
 * distance, or the restaurant's own delivery time. Because the restaurant
 * already states a minimum and a maximum, only the distance based method keeps
 * a {@see $time_gap} to widen its single figure into a range.
 */
class EtaConfiguration extends Model
{
    use HasFactory;

    /** Travel time comes from the distance between restaurant and customer. */
    public const METHOD_DISTANCE = 'distance_based';

    /** Travel time comes from the restaurant's own minimum and maximum delivery time. */
    public const METHOD_FIXED = 'fixed_delivery_time';

    /** Every value `calculation_method` may hold — the validation whitelist. */
    public const METHODS = [self::METHOD_DISTANCE, self::METHOD_FIXED];

    protected $casts = [
        'zone_id' => 'integer',
        'status' => 'integer',
        'minimum_delivery_time' => 'integer',
        'preparation_buffer' => 'integer',
        'transit_buffer' => 'integer',
        'time_gap' => 'integer',
    ];

    protected $guarded = ['id'];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    /**
     * The name in the active locale, falling back to the stored default.
     *
     * The column keeps the default-language value; other locales live in the polymorphic
     * translations table, the same arrangement Surge Price uses. Reading the raw column back
     * - to prefill the default-language input on the edit form - needs getRawOriginal('name').
     */
    public function getNameAttribute($value)
    {
        foreach ($this->translations ?? [] as $translation) {
            if ($translation['key'] === 'name') {
                return $translation['value'];
            }
        }

        return $value;
    }

    protected static function booted()
    {
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                return $query->where('locale', app()->getLocale());
            }]);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }

    /** The zone's other configurations - what a switch off or a delete may hand over to. */
    public function scopeSiblings($query)
    {
        return $query->where('zone_id', $this->zone_id)->where('id', '!=', $this->id);
    }

    /**
     * How the configuration is named where one has to be picked out of a zone's set.
     *
     * The name is optional, so a nameless configuration is identified by what it
     * does and which one it is - "Distance Based #4" tells the admin more than an
     * empty option would.
     */
    public function optionLabel(): string
    {
        return $this->name ?: $this->methodLabel() . ' #' . $this->id;
    }

    /** Label shown in the list's ETA Type column. */
    public function methodLabel(): string
    {
        return $this->calculation_method === self::METHOD_FIXED
            ? translate('messages.Fixed_Delivery_Time')
            : translate('messages.Distance_Based');
    }

    /** True when the method widens its estimate with a gap of its own. */
    public function usesTimeGap(): bool
    {
        return $this->calculation_method === self::METHOD_DISTANCE;
    }

    /** What is added to every estimate before the travel time is counted. */
    public function bufferMinutes(): int
    {
        return (int) $this->preparation_buffer + (int) $this->transit_buffer;
    }

    /**
     * The shortest estimate this configuration can produce.
     *
     * `minimum_delivery_time` is a floor rather than a term: the buffers and the
     * travel time are added up first, and only if that total comes in under the
     * floor does the floor replace it. With no order in hand there is no travel
     * time, so this is the buffers alone against the floor — the estimate of a
     * journey that takes no time at all.
     *
     * {@see \App\CentralLogics\EtaLogic} is what actually estimates an order;
     * this exists so the panel can preview the same arithmetic.
     */
    public function minimumEtaMinutes(): int
    {
        return max($this->bufferMinutes(), (int) $this->minimum_delivery_time);
    }

    /**
     * The longest estimate, or null when the configuration cannot name one on
     * its own — the fixed method reads its maximum from the restaurant, which
     * is only known once an order is being estimated.
     */
    public function maximumEtaMinutes(): ?int
    {
        return $this->usesTimeGap()
            ? $this->minimumEtaMinutes() + (int) $this->time_gap
            : null;
    }

    /** The estimate as the customer would see it, e.g. "10 min - 15 min". */
    public function etaRangeLabel(): string
    {
        $unit = translate('messages.eta_minute_unit');
        $maximum = $this->maximumEtaMinutes();

        return $maximum === null || $maximum === $this->minimumEtaMinutes()
            ? "{$this->minimumEtaMinutes()} {$unit}"
            : "{$this->minimumEtaMinutes()} {$unit} - {$maximum} {$unit}";
    }
}
