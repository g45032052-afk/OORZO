<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'user_id' => 'integer',
        'delivery_man_id' => 'integer',
        'credit' => 'float',
        'debit' => 'float',
        'admin_bonus'=>'float',
        'balance'=>'float',
        'reference'=>'string',
        'reference_id'=>'string',
        'created_at'=>'string'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function delivery_man()
    {
        return $this->belongsTo(DeliveryMan::class,'delivery_man_id');
    }

    public function getReferenceIdAttribute($value)
    {
        if ($value) {
            return $value;
        }

        $code = Helpers::generateHumanReadableId($this,'reference_id');

        $this->newQuery()
            ->where('id', $this->id)
            ->update(['reference_id' => $code]);

        $this->attributes['reference_id'] = $code;

        return $code;
    }
}
