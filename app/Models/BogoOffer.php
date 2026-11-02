<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use App\Traits\GeneratesSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BogoOffer extends Model
{
    use HasFactory, GeneratesSlug;

    /**
     * Whether an offer may be restricted to particular order types.
     *
     * Turned off for now at the client's request, and switched off in one place rather than
     * taken out: the columns, the stored values, the panel field and every check are all still
     * here, they just do not apply while this is false. Flip it back to true and BOGO restricts
     * itself by delivery / take away / dine in again, with nothing to rewrite.
     *
     * While it is false:
     *   - the panel form hides the Order Type picker and keeps whatever an offer already stored
     *   - a restaurant is never turned away from an offer over its order types
     *   - the customer endpoints ignore an order_type filter and omit the key from responses
     *   - the cart and checkout never refuse a bundle for the order type it is checked out with
     */
    public const ORDER_TYPES_ENABLED = false;

    public static function orderTypesEnabled(): bool
    {
        return self::ORDER_TYPES_ENABLED;
    }

    protected $casts = [
        'status' => 'integer',
        'admin_id' => 'integer',
        'buy_qty' => 'integer',
        'get_qty' => 'integer',
        'usage_limit_total' => 'integer',
        'usage_limit_per_customer' => 'integer',
        'total_uses' => 'integer',
        'order_types' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
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
                    return Helpers::get_full_url('bogo_offer', $value, $storage['value'], 'campaign');
                }
            }
        }

        return Helpers::get_full_url('bogo_offer', $value, 'public', 'campaign');
    }

    public function storage()
    {
        return $this->morphMany(Storage::class, 'data');
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getTitleAttribute($value)
    {
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'title') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function getDescriptionAttribute($value)
    {
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'description') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function restaurants()
    {
        return $this->belongsToMany(Restaurant::class, 'bogo_offer_restaurant')
            ->withPivot(['id', 'status', 'rejection_reason', 'rejected_by', 'requested_by', 'joined_at', 'bundle_price', 'combination_signature'])
            ->withTimestamps();
    }

    public function enrollments()
    {
        return $this->hasMany(BogoOfferRestaurant::class);
    }

    public function usages()
    {
        return $this->hasMany(BogoOfferUsage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeRunning($query)
    {
        return $query->where(function ($q) {
            $q->where('end_date', '>=', now())->orWhereNull('end_date');
        })->where(function ($q) {
            $q->where('start_date', '<=', now())->orWhereNull('start_date');
        });
    }

    /**
     * Once the total usage cap is hit the offer disappears entirely, unlike the
     * per-customer cap which leaves it visible but unaddable.
     */
    public function scopeNotExhausted($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('usage_limit_total')
                ->orWhereColumn('total_uses', '<', 'usage_limit_total');
        });
    }

    /**
     * Buy/get quantities are editable only until the first restaurant enrols - after that
     * the enrolled item sets are built against them and can no longer be reconciled.
     */
    public function isQuantityLocked(): bool
    {
        return $this->enrollments()->exists();
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

        static::created(function ($offer) {
            $offer->slug = $offer->generateSlug($offer->title);
            $offer->save();
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
    }
}
