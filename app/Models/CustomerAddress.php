<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\CentralLogics\Helpers;
use App\Traits\MasksSensitiveAttributes;

class CustomerAddress extends Model
{
    use MasksSensitiveAttributes;
    protected $casts = [
        'user_id' => 'integer',
        'zone_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_default' => 'boolean'
    ];
    protected $guarded = ['id'];


  protected static function boot()
    {
        parent::boot();
        static::saved(function () {
            Helpers::deleteCacheData('distance_data_');
        });
        static::created(function () {
            Helpers::deleteCacheData('distance_data_');
        });
        static::deleted(function(){
            Helpers::deleteCacheData('distance_data_');
        });
        static::updated(function(){
            Helpers::deleteCacheData('distance_data_');
        });
    }




}
