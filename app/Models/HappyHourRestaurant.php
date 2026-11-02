<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HappyHourRestaurant extends Model
{
    use HasFactory;

    protected $table = 'happy_hour_restaurant';

    protected $fillable = [
        'happy_hour_id',
        'restaurant_id',
        'status',
        'rejection_reason',
        'rejected_by',
        'requested_by',
        'checked',
        'joined_at',
    ];

    protected $casts = [
        'happy_hour_id' => 'integer',
        'restaurant_id' => 'integer',
        'checked' => 'boolean',
        'joined_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function happy_hour()
    {
        return $this->belongsTo(HappyHour::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
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
