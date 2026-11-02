<?php

namespace Modules\AI\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\AI\app\Models\AiConversation;
use Modules\AI\app\Models\AiMessage;
use App\Models\User;
use Modules\AI\app\Services\AiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Customer-facing AI chat API. Single-module (food) — no module_id; auto-resume
 * keys on user/guest + zone only.
 */
class AiChatController extends Controller
{
    /**
     * POST /api/v1/customer/ai-chat/send
     */
    public function send(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message'         => 'required|string|max:2000',
            'conversation_id' => 'nullable|integer',
            'guest_id'        => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $this->errorProcessor($validator)], 403);
        }

        $user    = $this->authUser();
        $guestId = $this->resolveGuestId($request, $user);

        if (!$user && !$guestId) {
            return response()->json(['errors' => [['code' => 'identity', 'message' => 'Provide either authentication token or guest_id.']]], 403);
        }

        $zoneIds       = $this->getZoneIds($request);
        $primaryZoneId = $zoneIds[0] ?? null;
        $latitude      = $this->floatHeader($request, 'latitude');
        $longitude     = $this->floatHeader($request, 'longitude');

        $conversation = $this->resolveConversation($request, $user, $guestId, $primaryZoneId);

        if (empty($zoneIds) && $conversation->zone_id) {
            $zoneIds = [(int) $conversation->zone_id];
        }

        $result = (new AiChatService($user, $zoneIds, $guestId, $latitude, $longitude))
            ->chat($conversation, $request->input('message'));

        return response()->json([
            'conversation_id' => $conversation->id,
            'role'            => 'assistant',
            'content'         => $result['message'],
            'tool_name'       => $result['tool_name'],
            'metadata'        => [
                'stores'       => $result['stores'],
                'products'     => $result['products'],
                'categories'   => $result['categories'],
                'cuisines'     => $result['cuisines'],
                // This block is a whitelist: a key the service produces but that is not
                // named here never reaches the client. The offer and happy hour cards were
                // built, ranked and then silently dropped exactly here.
                'bogo_offers'  => $result['bogo_offers'],
                'happy_hours'  => $result['happy_hours'],
                'cart'         => $result['cart'],
                'cart_updated' => $result['cart_updated'],
            ],
        ], 200);
    }

    /**
     * GET /api/v1/customer/ai-chat/conversations
     */
    public function conversations(Request $request): JsonResponse
    {
        $user    = $this->authUser();
        $guestId = $this->resolveGuestId($request, $user);

        if (!$user && !$guestId) {
            return response()->json(['errors' => [['code' => 'identity', 'message' => 'Provide either authentication token or guest_id.']]], 403);
        }

        $conversations = AiConversation::where('status', 'active')
            ->when($user, fn ($q) => $q->where('user_id', $user->getKey()))
            ->when(!$user && $guestId, fn ($q) => $q->where('guest_id', $guestId))
            ->withCount('messages')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        return response()->json(['total_size' => $conversations->count(), 'data' => $conversations], 200);
    }

    /**
     * GET /api/v1/customer/ai-chat/messages?conversation_id=xxx
     */
    public function messages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|integer',
            'limit'           => 'nullable|integer|min:1|max:100',
            'offset'          => 'nullable|integer|min:1',
            'guest_id'        => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $this->errorProcessor($validator)], 403);
        }

        $user    = $this->authUser();
        $guestId = $this->resolveGuestId($request, $user);

        if (!$user && !$guestId) {
            return response()->json(['errors' => [['code' => 'identity', 'message' => 'Provide either authentication token or guest_id.']]], 403);
        }

        $conversation = $this->findConversation((int) $request->input('conversation_id'), $user, $guestId);

        if (! $conversation) {
            return response()->json(['errors' => [['code' => 'not_found', 'message' => 'Conversation not found.']]], 404);
        }

        $limit  = (int) $request->input('limit', 50);
        $offset = (int) $request->input('offset', 1);

        $messages = AiMessage::where('conversation_id', $conversation->id)
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('id')
            ->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'total_size'      => $messages->total(),
            'limit'           => $limit,
            'offset'          => $offset,
            'conversation_id' => $conversation->id,
            'data'            => $messages->items(),
        ], 200);
    }

    /**
     * DELETE /api/v1/customer/ai-chat/conversations/{id}
     */
    public function deleteConversation(Request $request, int $id): JsonResponse
    {
        $user    = $this->authUser();
        $guestId = $this->resolveGuestId($request, $user);

        if (!$user && !$guestId) {
            return response()->json(['errors' => [['code' => 'identity', 'message' => 'Provide either authentication token or guest_id.']]], 403);
        }

        $conversation = $this->findConversation($id, $user, $guestId);

        if (! $conversation) {
            return response()->json(['errors' => [['code' => 'not_found', 'message' => 'Conversation not found.']]], 404);
        }

        $conversation->update(['status' => 'archived']);

        return response()->json(['message' => 'Conversation archived successfully.'], 200);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function authUser(): ?User
    {
        return auth('api')->user();
    }

    private function resolveGuestId(Request $request, ?User $user): ?string
    {
        if ($user) {
            return null;
        }
        $guestId = $request->input('guest_id');
        return $guestId ? (string) $guestId : null;
    }

    private function resolveConversation(Request $request, ?User $user, ?string $guestId, ?int $zoneId): AiConversation
    {
        $conversationId = (int) $request->input('conversation_id');

        if ($conversationId) {
            $existing = $this->findConversation($conversationId, $user, $guestId);
            if ($existing && $existing->status === 'active') {
                return $existing;
            }
        }

        // Auto-resume: most recent active conversation in the same zone within
        // the last 30 minutes (single-module, so no module_id key).
        $recent = AiConversation::where('status', 'active')
            ->when($user, fn ($q) => $q->where('user_id', $user->getKey()))
            ->when(!$user && $guestId, fn ($q) => $q->where('guest_id', $guestId))
            ->where('zone_id', $zoneId)
            ->where('updated_at', '>=', now()->subMinutes(30))
            ->orderByDesc('updated_at')
            ->first();

        if ($recent) {
            return $recent;
        }

        return AiConversation::create([
            'user_id'  => $user?->getKey(),
            'guest_id' => $user ? null : $guestId,
            'zone_id'  => $zoneId,
            'status'   => 'active',
        ]);
    }

    private function findConversation(int $id, ?User $user, ?string $guestId): ?AiConversation
    {
        if (!$user && !$guestId) {
            return null;
        }

        return AiConversation::where('id', $id)
            ->when($user, fn ($q) => $q->where('user_id', $user->getKey()))
            ->when(!$user && $guestId, fn ($q) => $q->where('guest_id', $guestId))
            ->first();
    }

    private function floatHeader(Request $request, string $name): ?float
    {
        $value = $request->header($name) ?? $request->input($name);
        if ($value === null || $value === '') {
            return null;
        }
        return is_numeric($value) ? (float) $value : null;
    }

    /** @return int[] */
    private function getZoneIds(Request $request): array
    {
        $raw = $request->header('zoneId') ?? $request->input('zone_id');
        if (! $raw) {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map(static fn ($v) => (int) $v, $decoded), static fn ($v) => $v > 0));
        }
        if ((int) $raw > 0) {
            return [(int) $raw];
        }
        return [];
    }

    private function errorProcessor($validator): array
    {
        $errors = [];
        foreach ($validator->errors()->getMessages() as $field => $messages) {
            foreach ($messages as $message) {
                $errors[] = ['code' => $field, 'message' => $message];
            }
        }
        return $errors;
    }
}
