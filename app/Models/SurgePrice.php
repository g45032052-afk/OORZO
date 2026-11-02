<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A surge pricing rule for one business zone.
 *
 * The rule is authored once and then expanded into dated rows on
 * {@see SurgePriceDate} so checkout can resolve it with a single indexed
 * query. See the migrations for why the two tables exist.
 */
class SurgePrice extends Model
{
    use HasFactory;

    protected $casts = [
        'zone_id' => 'integer',
        'status' => 'integer',
        'is_permanent' => 'integer',
        'customer_note_status' => 'integer',
        'price' => 'float',
        'weekly_days' => 'array',
        'custom_days' => 'array',
        'custom_times' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $guarded = ['id'];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * The materialised calendar rows this rule expanded into. Empty for
     * permanent weekly rules, which are resolved at runtime instead.
     */
    public function details()
    {
        return $this->hasMany(SurgePriceDate::class, 'surge_price_id');
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getSurgePriceNameAttribute($value)
    {
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'surge_price_name') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function getCustomerNoteAttribute($value)
    {
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'customer_note') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }

    protected static function booted()
    {
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                return $query->where('locale', app()->getLocale());
            }]);
        });
    }
}
