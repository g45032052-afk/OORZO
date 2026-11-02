<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BogoOfferRestaurant extends Model
{
    use HasFactory;

    protected $table = 'bogo_offer_restaurant';

    protected $fillable = [
        'bogo_offer_id',
        'restaurant_id',
        'status',
        'rejection_reason',
        'rejected_by',
        'requested_by',
        'checked',
        'joined_at',
        'bundle_price',
        'combination_signature',
    ];

    protected $casts = [
        'bogo_offer_id' => 'integer',
        'restaurant_id' => 'integer',
        'checked' => 'boolean',
        'bundle_price' => 'float',
        'joined_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function bogo_offer()
    {
        return $this->belongsTo(BogoOffer::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function items()
    {
        return $this->hasMany(BogoOfferItem::class);
    }

    public function buyItems()
    {
        return $this->hasMany(BogoOfferItem::class)->where('type', 'buy');
    }

    public function getItems()
    {
        return $this->hasMany(BogoOfferItem::class)->where('type', 'get');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
