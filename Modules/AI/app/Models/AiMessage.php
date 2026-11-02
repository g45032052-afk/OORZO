<?php

namespace Modules\AI\app\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single AI chat message (user / assistant / tool).
 */
class AiMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'tool_name',
        'metadata',
    ];

    protected $casts = [
        'conversation_id' => 'integer',
        'metadata'        => 'array',
    ];

    public function conversation()
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }
}
