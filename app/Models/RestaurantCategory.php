<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use App\Traits\ReportFilter;
use App\Models\Food;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\GeneratesSlug;

class RestaurantCategory extends Model
{
    use HasFactory, ReportFilter, GeneratesSlug;

    protected $with = ['translations', 'storage', 'restaurant'];

    protected $casts = [
        'restaurant_id' => 'integer',
        'priority' => 'integer',
        'status' => 'integer',
    ];

    protected $appends = ['image_full_url'];

    public function getImageFullUrlAttribute()
    {
        $value = $this->image;
        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] == 'image') {
                    return Helpers::get_full_url('category', $value, $storage['value']);
                }
            }
        }

        return Helpers::get_full_url('category', $value, 'public');
    }

    public function getNameAttribute($value)
    {
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'name') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function foods()
    {
        return $this->hasMany(Food::class, 'restaurant_category_id');
    }

    public function storage()
    {
        return $this->morphMany(Storage::class, 'data');
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    protected static function booted()
    {
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                return $query->where('locale', app()->getLocale());
            }]);
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($restaurantCategory) {
            $restaurantCategory->slug = $restaurantCategory->generateSlug($restaurantCategory->name);
            $restaurantCategory->save();
        });

        static::saved(function ($model) {
            if ($model->isDirty('image')) {
                $value = Helpers::getDisk();

                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'image',
                ], [
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        static::saved(function () {
            Helpers::deleteCacheData('restaurant_cat_foods_');
        });

        static::deleted(function () {
            Helpers::deleteCacheData('restaurant_cat_foods_');
        });
    }
}
