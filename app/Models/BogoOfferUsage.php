<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BogoOfferUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'bogo_offer_id',
        'order_id',
        'user_id',
        'is_guest',
        'phone',
        'quantity',
    ];

    protected $casts = [
        'bogo_offer_id' => 'integer',
        'order_id' => 'integer',
        'user_id' => 'integer',
        'is_guest' => 'boolean',
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function bogo_offer()
    {
        return $this->belongsTo(BogoOffer::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Guests are counted by normalised phone, signed-in customers by user id.
     */
    public function scopeForCustomer($query, ?int $userId, bool $isGuest, ?string $phone)
    {
        return $isGuest
            ? $query->where('is_guest', 1)->where('phone', $phone)
            : $query->where('is_guest', 0)->where('user_id', $userId);
    }
}
