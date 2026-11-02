<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One charge line of an area-wise or zip-code-wise {@see DeliveryRule}.
 */
class DeliveryRuleCharge extends Model
{
    use HasFactory;

    protected $fillable = ['delivery_rule_id', 'area_id', 'zip_code_id', 'charge'];

    protected $casts = [
        'delivery_rule_id' => 'integer',
        'area_id' => 'integer',
        'zip_code_id' => 'integer',
        'charge' => 'float',
    ];

    public function rule()
    {
        return $this->belongsTo(DeliveryRule::class, 'delivery_rule_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function zipCode()
    {
        return $this->belongsTo(ZipCode::class);
    }

    /** The area name or the code itself, whichever this line targets. */
    public function label(): string
    {
        return $this->area?->name ?? $this->zipCode?->zip_code ?? translate('messages.N/A');
    }
}
