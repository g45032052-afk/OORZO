<?php

namespace Modules\AI\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * A single personalization signal (item/store/category/search) for a customer.
 * Single-module (food) — no module_id.
 */
class CustomerPreference extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'user_id' => 'integer',
        'reference_id' => 'integer',
        'score' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
