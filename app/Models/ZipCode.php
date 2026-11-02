<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A postal code registered under a business zone.
 *
 * No translations here, unlike {@see Area}: the value is a code, identical in
 * every language.
 */
class ZipCode extends Model
{
    use HasFactory;

    protected $casts = [
        'zone_id' => 'integer',
        'status' => 'integer',
    ];

    protected $guarded = ['id'];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function restaurants()
    {
        return $this->hasMany(Restaurant::class);
    }

    public function deliverymen()
    {
        return $this->hasMany(DeliveryMan::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }
}
