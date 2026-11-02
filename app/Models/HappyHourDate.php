<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HappyHourDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'happy_hour_id',
        'zone_id',
        'status',
        'applicable_date',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'happy_hour_id' => 'integer',
        'zone_id' => 'integer',
        'status' => 'boolean',
        'applicable_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function happy_hour()
    {
        return $this->belongsTo(HappyHour::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
