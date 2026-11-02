<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $casts = [
        'user_id' => 'integer',
        'restaurant_id' => 'integer',
        'item_id' => 'integer',
        'reel_id' => 'integer',
        'is_guest' => 'boolean',
        'price' => 'float',
        'quantity' => 'integer',
        'add_on_qtys' => 'array',
        'add_on_ids' => 'array',
    ];

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'item_id',
        'reel_id',
        'is_guest',
        'add_on_ids',
        'add_on_qtys',
        'item_type',
        'price',
        'quantity',
        'variations',
        // A bundle writes one row per enrolled item, all sharing bogo_group_id, with the
        // free ones flagged so the cart can price and group them without re-deriving.
        'bogo_offer_id',
        'bogo_group_id',
        'is_free_item',
    ];

    public function item()
    {
        return $this->morphTo();
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
