<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZoneDeliveryOption extends Model
{
    protected $guarded = [];

    protected $casts = [
        'extra_charge' => 'float',
        'reduce_charge' => 'float',
        'add_delivery_time' => 'integer',
        'reduce_delivery_time' => 'integer',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function setAddDeliveryTimeAttribute($value)
    {
        if (is_array($value) && isset($value['value'], $value['unit'])) {
            $minutes = $value['unit'] === 'hour' ? $value['value'] * 60 : $value['value'];
            $this->attributes['add_delivery_time'] = $minutes;
        } elseif (is_numeric($value)) {
            $this->attributes['add_delivery_time'] = $value;
        } else {
            $this->attributes['add_delivery_time'] = 0; // default fallback
        }
    }

    public function setReduceDeliveryTimeAttribute($value)
    {
        if (is_array($value) && isset($value['value'], $value['unit'])) {
            $minutes = $value['unit'] === 'hour' ? $value['value'] * 60 : $value['value'];
            $this->attributes['reduce_delivery_time'] = $minutes;
        } elseif (is_numeric($value)) {
            $this->attributes['reduce_delivery_time'] = $value;
        } else {
            $this->attributes['reduce_delivery_time'] = 0; // default fallback
        }
    }

    public function getAddDeliveryTimeAttribute($value)
    {
        if (is_numeric($value) && $value >= 60 && $value % 60 === 0) {
            return [
                'value' => $value / 60,
                'unit' => 'hour',
            ];
        }
        return [
            'value' => $value,
            'unit' => 'min',
        ];
    }

    public function getReduceDeliveryTimeAttribute($value)
    {
        if (is_numeric($value) && $value >= 60 && $value % 60 === 0) {
            return [
                'value' => $value / 60,
                'unit' => 'hour',
            ];
        }
        return [
            'value' => $value,
            'unit' => 'min',
        ];
    }
}
