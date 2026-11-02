<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use App\Traits\ReportFilter;
use Illuminate\Support\Facades\DB;
use App\Traits\GeneratesSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\TaxModule\Entities\Taxable;

class Category extends Model
{
    use HasFactory ,ReportFilter, GeneratesSlug;
    protected $with = ['translations','storage'];
    protected $casts = [
        'parent_id' => 'integer',
        'position' => 'integer',
        'priority' => 'integer',
        'status' => 'integer',
        'products_count' => 'integer',
        'childes_count' => 'integer',
        'meta_data' => 'array',
    ];
    protected $appends = ['image_full_url','meta_image_full_url'];

    public function getImageFullUrlAttribute(){
        $value = $this->image;
        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] == 'image') {
                    return Helpers::get_full_url('category',$value,$storage['value']);
                }
            }
        }

        return Helpers::get_full_url('category',$value,'public');
    }
    public function getMetaImageFullUrlAttribute()
    {
        $value = $this->meta_image;
        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] == 'meta_image') {
                    return Helpers::get_full_url('meta_image', $value, $storage['value']);
                }
            }
        }
        return Helpers::get_full_url('meta_image', $value, 'public');
    }
    // public function getMetaTitleAttribute($value)
    // {
    //     if (count($this->translations) > 0) {

    //         foreach ($this->translations as $translation) {
    //             if ($translation['key'] == 'meta_title') {
    //                 return $translation['value'];
    //             }
    //         }
    //     }

    //     return $value;
    // }

    // public function getMetaDescriptionAttribute($value)
    // {
    //     if (count($this->translations) > 0) {
    //         foreach ($this->translations as $translation) {
    //             if ($translation['key'] == 'meta_description') {
    //                 return $translation['value'];
    //             }
    //         }
    //     }

    //     return $value;
    // }
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
        return $query->where('status', '=', 1);
    }

    public function childes()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Food::class);
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

    protected static function booted()
    {
        // static::addGlobalScope('storage', function ($builder) {
        //     $builder->with('storage');
        // });
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                return $query->where('locale', app()->getLocale());
            }]);
        });
    }

    protected static function boot()
    {
        parent::boot();
        static::created(function ($category) {
            $category->slug = $category->generateSlug($category->name);
            $category->save();
        });
        static::saved(function ($model) {
            if($model->isDirty('image')){
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

    public function users()
    {
        return $this->morphToMany(User::class ,'visitor_log' );
    }

    public function taxVats()
    {
        return $this->morphMany(Taxable::class, 'taxable');
    }

}
