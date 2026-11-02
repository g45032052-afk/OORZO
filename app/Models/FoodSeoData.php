<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;

class FoodSeoData extends Model
{
    use HasFactory;
    protected  $guarded = ['id'];
    protected $casts = [
        'food_id' => 'integer',
        'title' => 'string',
        'description' => 'string',
        'index' => 'string',
        'no_follow' => 'string',
        'no_image_index' => 'string',
        'no_archive' => 'string',
        'no_snippet' => 'string',
        'max_snippet' => 'string',
        'max_snippet_value' => 'string',
        'max_video_preview' => 'string',
        'max_video_preview_value' => 'string',
        'max_image_preview' => 'string',
        'max_image_preview_value' => 'string',
        'image' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['image_full_url'];
    public function getImageFullUrlAttribute()
    {
        $value = $this->image;
        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] == 'image') {
                    return Helpers::get_full_url('product/meta', $value, $storage['value']);
                }
            }
        }

        return Helpers::get_full_url('product/meta', $value, 'public');
    }

      public function storage()
    {
        return $this->morphMany(Storage::class, 'data');
    }
    protected static function boot(): void
    {
        parent::boot();
        static::saved(function ($model) {
            if ($model->isDirty('image')) {
                $storage = config('filesystems.disks.default') ?? 'public';
                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'image',
                ], [
                    'value' => $storage,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
