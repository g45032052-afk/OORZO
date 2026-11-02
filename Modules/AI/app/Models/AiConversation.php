<?php

namespace Modules\AI\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * An AI chat conversation. Single-module (food) — no module_id.
 */
class AiConversation extends Model
{
    protected $fillable = [
        'user_id',
        'guest_id',
        'zone_id',
        'title',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'zone_id' => 'integer',
    ];

    public function messages()
    {
        return $this->hasMany(AiMessage::class, 'conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
