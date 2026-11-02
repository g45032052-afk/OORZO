<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryManDevice extends Model
{
    protected $fillable = [
        'delivery_man_id',
        'device_id',
        'device_name',
        'biometric_token',
        'biometric_enabled',
        'last_login_at'
    ];

    public function deliveryMan()
    {
        return $this->belongsTo(DeliveryMan::class);
    }
}
