<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use App\Traits\ReportFilter;
use Illuminate\Support\Facades\DB;
use App\Traits\GeneratesSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Cuisine extends Model
{
    use HasFactory ,ReportFilter, GeneratesSlug;
    protected $with = ['translations','storage'];

    protected $fillable = ['name', 'image'];
    protected $casts = [
        'id' => 'integer',
        'status' => 'integer',
        'meta_data' => 'array',
    ];

    protected $appends = ['image_full_url','meta_image_full_url'];

    public function getImageFullUrlAttribute(){
        $value = $this->image;
        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] == 'image') {
                    return Helpers::get_full_url('cuisine',$value,$storage['value']);
                }
            }
        }

        return Helpers::get_full_url('cuisine',$value,'public');
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
    public function restaurants()
    {
        return $this->belongsToMany(Restaurant::class)->using('App\Models\Cuisine_restaurant');
    }

    protected static function boot()
    {
        parent::boot();
        static::created(function ($cuisine) {
            $cuisine->slug = $cuisine->generateSlug($cuisine->name);
            $cuisine->save();
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
}
