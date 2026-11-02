<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProCustomerSubscription extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'user_id' => 'integer',
        'plan_id' => 'integer',
        'plan_price' => 'float',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'auto_renew' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function plan()
    {
        return $this->belongsTo(ProCustomerSubscriptionPlan::class, 'plan_id');
    }

    public function transactions()
    {
        return $this->hasMany(ProCustomerTransaction::class, 'subscription_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }
}
