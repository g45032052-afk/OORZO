<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Shift extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'status' => 'integer',
        'is_full_day' => 'integer',
    ];

    public function delivery_men()
    {
        return $this->belongsToMany(DeliveryMan::class, 'delivery_man_shift');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }
    protected static function booted()
    {
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function($query){
                return $query->where('locale', app()->getLocale());
            }]);
        });
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
}
