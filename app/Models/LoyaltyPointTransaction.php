<?php
namespace App\Models;

use App\CentralLogics\Helpers;
use App\Traits\ReportFilter;
use Illuminate\Database\Eloquent\Model;

class LoyaltyPointTransaction extends Model
{
    use ReportFilter;
    public $timestamps = false;

    protected $casts = [
        'user_id' => 'integer',
        'credit' => 'float',
        'debit' => 'float',
        'balance'=>'float',
        'reference'=>'string',
        'reference_id'=>'string'
    ];

    /**
     * Get the user that owns the LoyaltyPointTransaction
     *
     * @return \App\Models\User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
