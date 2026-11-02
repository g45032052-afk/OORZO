<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class RefundReason extends Model
{
    protected $casts = [
        'status' => 'integer',
        'id' => 'integer',
    ];
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * The reason in the panel's current language, or the default one.
     *
     * The locale is compared here rather than left to the eager loading
     * constraint below. The refund settings page loads every language's
     * translation at once, so the edit modal can fill one field per tab, and
     * without this check the list showed whichever row came back first - an
     * Arabic reason on an English panel. A lazy loaded relation is unfiltered
     * for the same reason.
     */
    public function getReasonAttribute($value)
    {
        foreach ($this->translations as $translation) {
            if ($translation['key'] == 'reason' && $translation['locale'] == app()->getLocale()) {
                return $translation['value'];
            }
        }

        return $value;
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
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
