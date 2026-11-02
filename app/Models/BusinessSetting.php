<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BusinessSetting extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $with = ['storage'];
    protected $fillable = [
        'key',
        'value'
    ];

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getValueAttribute($value)
    {
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == $this->key) {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function storage()
    {
        return $this->morphMany(Storage::class, 'data');
    }

    protected static function booted(): void
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
        static::saved(function ($model) {
             Helpers::deleteCacheData('business_settings_all_data');
            $value = Helpers::getDisk();

            DB::table('storages')->updateOrInsert([
                'data_type' => get_class($model),
                'data_id' => $model->id,
            ], [
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
        static::created(function () {
            Helpers::deleteCacheData('business_settings_all_data');
        });
        static::deleted(function(){
            Helpers::deleteCacheData('business_settings_all_data');
        });
        static::updated(function(){
            Helpers::deleteCacheData('business_settings_all_data');
        });
    }
}
