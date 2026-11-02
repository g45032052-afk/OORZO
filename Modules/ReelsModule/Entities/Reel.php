<?php

namespace Modules\ReelsModule\Entities;

use App\CentralLogics\Helpers;
use App\Models\Food;
use App\Models\Storage;
use App\Models\Restaurant;
use App\Models\Translation;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Reel extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'restaurant_id' => 'integer',
        'food_id' => 'integer',
        'is_always_visible' => 'boolean',
        'order_now_button' => 'boolean',
        'order_count' => 'integer',
        'total_sale_amount' => 'decimal:2',
        'status' => 'boolean',
        'total_views' => 'integer',
        'total_likes' => 'integer',
        'total_store_visits' => 'integer',
        'created_by_id' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    protected $appends = ['thumbnail_full_url', 'video_full_url', 'reel_status_label'];

    public function created_by()
    {
        return $this->morphTo(__FUNCTION__, 'created_by_type', 'created_by_id');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }

    public function food()
    {
        return $this->belongsTo(Food::class, 'food_id');
    }

    public function activeFood()
    {
        return $this->belongsTo(Food::class, 'food_id')->active();
    }

    public function storage()
    {
        return $this->morphMany(Storage::class, 'data');
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function engagements(): HasMany
    {
        return $this->hasMany(ReelEngagement::class, 'reel_id');
    }

    public function getDescriptionAttribute($value)
    {
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] === 'description' && $translation['locale'] === app()->getLocale()) {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function getThumbnailFullUrlAttribute(): ?string
    {
        $value = $this->thumbnail;
        if (!$value) {
            return null;
        }

        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] === 'thumbnail') {
                    return Helpers::get_full_url('reels', $value, $storage['value']);
                }
            }
        }

        return Helpers::get_full_url('reels', $value, 'public');
    }

    public function getVideoFullUrlAttribute(): ?string
    {
        $value = $this->video;
        if (!$value) {
            return null;
        }

        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] === 'video') {
                    return Helpers::get_full_url('reels', $value, $storage['value']);
                }
            }
        }

        return Helpers::get_full_url('reels', $value, 'public');
    }

    public function getReelStatusLabelAttribute(): string
    {
        if (!$this->status) {
            return 'deactivated';
        }

        if ($this->is_always_visible) {
            return 'live';
        }

        $today = new DateTime(date('Y-m-d'));
        $startDate = $this->start_date ? new DateTime($this->start_date) : null;
        $endDate = $this->end_date ? new DateTime($this->end_date) : null;

        if ($startDate && $endDate && $today >= $startDate && $today <= $endDate) {
            return 'live';
        }

        if ($startDate && $today < $startDate) {
            return 'upcoming';
        }

        return 'expired';
    }

    public function scopeActive(Builder $query): Builder
    {
        $now = now();

        return $query->where('status', 1)
            ->where(function (Builder $builder) use ($now) {
                $builder->where('is_always_visible', 1)
                    ->orWhere(function (Builder $dateQuery) use ($now) {
                        $dateQuery->where('is_always_visible', 0)
                            ->whereNotNull('start_date')
                            ->whereNotNull('end_date')
                            ->where('start_date', '<=', $now)
                            ->where('end_date', '>=', $now);
                    });
            });
    }

    public function hasEngaged(?int $userId, ?string $guestId, string $type): bool
    {
        return $this->engagements()
            ->where('type', $type)
            ->when($userId, fn (Builder $query) => $query->where('user_id', $userId))
            ->when(!$userId && $guestId, fn (Builder $query) => $query->where('guest_id', $guestId))
            ->exists();
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            if ($model->isDirty('thumbnail')) {
                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'thumbnail',
                ], [
                    'value' => Helpers::getDisk(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($model->isDirty('video')) {
                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'video',
                ], [
                    'value' => Helpers::getDisk(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
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
