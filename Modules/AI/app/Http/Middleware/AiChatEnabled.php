<?php

namespace Modules\AI\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\AI\app\Core\AiModule;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the AI chat API on the admin `ai_chat_status` toggle (503 when off).
 */
class AiChatEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! AiModule::isChatActive()) {
            return response()->json([
                'errors' => [[
                    'code'    => 'ai_disabled',
                    'message' => 'AI chat is currently disabled.',
                ]],
            ], 503);
        }

        return $next($request);
    }
}
