<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One concrete date a {@see SurgePrice} rule applies to.
 *
 * Written by SurgePriceController::insertSurgePriceDates() when a rule is
 * saved, and deleted/rebuilt wholesale on update. Never edited directly.
 */
class SurgePriceDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'surge_price_id',
        'zone_id',
        'status',
        'applicable_date',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'surge_price_id' => 'integer',
        'zone_id' => 'integer',
        'status' => 'integer',
        'applicable_date' => 'date',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function surge_price()
    {
        return $this->belongsTo(SurgePrice::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }
}
