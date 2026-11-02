<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantConfig extends Model
{
    use HasFactory;

    protected $casts = [
        'customer_order_date'=>'integer',
        'restaurant_id'=>'integer',
        'customer_date_order_sratus'=>'boolean',
        'instant_order'=>'boolean',
        'verified_seller'=>'boolean',
        'has_seen_verified_badge_popup'=>'boolean',
        'extra_packaging_status'=>'boolean',
        'is_extra_packaging_active'=>'boolean',
        'extra_packaging_amount'=>'float',
        'opening_closing_status'=>'boolean',
        'same_time_for_every_day'=>'boolean',
    ];
    protected $guarded = ['id'];

}
