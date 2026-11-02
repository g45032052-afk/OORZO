<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A named area under a business zone.
 *
 * Areas group the restaurants and deliverymen that operate within part of a
 * zone, so delivery can later be reasoned about at a finer grain than the zone
 * polygon allows.
 */
class Area extends Model
{
    use HasFactory;

    protected $casts = [
        'zone_id' => 'integer',
        'status' => 'integer',
    ];

    protected $guarded = ['id'];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function restaurants()
    {
        return $this->hasMany(Restaurant::class);
    }

    public function deliverymen()
    {
        return $this->hasMany(DeliveryMan::class);
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getNameAttribute($value)
    {
        return $this->translatedValue('name', $value);
    }

    public function getDisplayNameAttribute($value)
    {
        return $this->translatedValue('display_name', $value);
    }

    private function translatedValue(string $key, $fallback)
    {
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == $key) {
                    return $translation['value'];
                }
            }
        }

        return $fallback;
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
