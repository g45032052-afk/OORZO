<?php

namespace Modules\AI\app\Services;

use Modules\AI\app\Agents\AiResponseContext;
use Modules\AI\app\Agents\PlatformAssistantAgent;
use Modules\AI\app\Agents\Tools\GetAvailableLanguagesTool;
use Modules\AI\app\Models\AiConversation;
use Modules\AI\app\Models\AiMessage;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\UserMessage;

class AiChatService
{
    private ?string $userContext;
    private array   $currency;
    private array   $languages;

    public function __construct(
        private readonly ?User   $user      = null,
        private readonly array   $zoneIds   = [],
        private readonly ?string $guestId   = null,
        private readonly ?float  $latitude  = null,
        private readonly ?float  $longitude = null,
    ) {
        $this->userContext = $this->resolveUserContext();
        $this->currency    = $this->resolveCurrency();
        $this->languages   = GetAvailableLanguagesTool::loadActive();
    }

    public function chat(AiConversation $conversation, string $userMessage): array
    {
        if (! $conversation->title) {
            $conversation->update(['title' => mb_substr($userMessage, 0, 80)]);
        }

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role'            => 'user',
            'content'         => $userMessage,
        ]);

        $history  = $this->buildHistory($conversation);
        $context  = new AiResponseContext();
        $lastMeta = $this->lastAssistantMetadata($conversation);

        $agent = new PlatformAssistantAgent(
            context:          $context,
            history:          $history,
            user:             $this->user,
            zoneIds:          $this->zoneIds,
            userContext:      $this->userContext,
            currency:         $this->currency,
            languages:        $this->languages,
            guestId:          $this->guestId,
            latitude:         $this->latitude,
            longitude:        $this->longitude,
            pendingVariation: is_array($lastMeta['pending_variation'] ?? null) ? $lastMeta['pending_variation'] : [],
        );

        try {
            $hint         = $this->buildContextHint($lastMeta);
            $promptToSend = $hint === '' ? $userMessage : $hint . "\n\n" . $userMessage;
            $response     = $agent->prompt($promptToSend);
            $replyText    = $this->stripContextLeak($response->text ?? '');
        } catch (\Throwable $e) {
            Log::error('AiChatService: agent prompt failed', [
                'conversation_id' => $conversation->id,
                'error'           => $e->getMessage(),
            ]);
            $replyText = "I'm sorry, I couldn't process that right now. Please try again.";
        }
        $products     = $this->orderByMention($context->getProducts(), $replyText);
        $stores       = $this->orderByMention($context->getStores(), $replyText);
        $categories   = $this->orderByMention($context->getCategories(), $replyText);
        $cuisines     = $this->orderByMention($context->getCuisines(), $replyText);
        $bogoOffers   = $this->orderByMention($context->getBogoOffers(), $replyText);
        $happyHours   = $this->orderByMention($context->getHappyHours(), $replyText);
        $cartItems    = $context->getCartItems();
        $cart         = \Modules\AI\app\Services\CartSnapshotService::grouped($cartItems);
        $toolsInvoked = $context->getToolsInvoked();

        $cartUpdated = count(array_intersect($toolsInvoked, [
            'AddToCartTool',
            'RemoveFromCartTool',
            'UpdateCartQuantityTool',
        ])) > 0;

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role'            => 'assistant',
            'content'         => $replyText,
            'tool_name'       => $toolsInvoked ? implode(',', $toolsInvoked) : null,
            'metadata'        => [
                'products'          => $products,
                'stores'            => $stores,
                'categories'        => $categories,
                'cuisines'          => $cuisines,
                'bogo_offers'       => $bogoOffers,
                'happy_hours'       => $happyHours,
                'cart'              => $cart,
                'cart_updated'      => $cartUpdated,
                // Internal (not returned to the client): the option prompt shown
                // this turn, so the next turn can keep a terse reply attached to
                // the right item.
                'pending_variation' => $context->getPendingVariation() ?: null,
            ],
        ]);

        return [
            'message'      => $replyText,
            'products'     => $products,
            'stores'       => $stores,
            'categories'   => $categories,
            'cuisines'     => $cuisines,
            'bogo_offers'  => $bogoOffers,
            'happy_hours'  => $happyHours,
            'cart'         => $cart,
            'cart_updated' => $cartUpdated,
            'tool_name'    => $toolsInvoked ? implode(',', $toolsInvoked) : null,
        ];
    }

    private function orderByMention(array $cards, string $reply): array
    {
        if (count($cards) < 2 || trim($reply) === '') {
            return $cards;
        }

        $haystack = $this->comparableText($reply);
        if ($haystack === '') {
            return $cards;
        }

        $ranked = [];
        foreach (array_values($cards) as $index => $card) {
            // Foods and stores carry `name`; an offer or a happy hour carries `title`.
            // Reading only `name` left every one of those unranked at the end of the list.
            $name     = $this->comparableText((string) ($card['name'] ?? $card['title'] ?? ''));
            $position = $name !== '' ? mb_strpos($haystack, $name) : false;

            $ranked[] = [
                'position' => $position === false ? PHP_INT_MAX : $position,
                'index'    => $index,
                'card'     => $card,
            ];
        }

        usort($ranked, fn (array $a, array $b) => [$a['position'], $a['index']] <=> [$b['position'], $b['index']]);

        return array_column($ranked, 'card');
    }

    private function comparableText(string $value): string
    {
        $ascii   = Str::ascii($value);
        $spaced  = preg_replace('/[^a-zA-Z0-9]+/', ' ', $ascii) ?? $ascii;

        return trim(mb_strtolower(preg_replace('/\s+/', ' ', $spaced) ?? $spaced));
    }

    private function resolveCurrency(): array
    {

        $symbol   = Helpers::currency_symbol() ?: '';
        $position = Helpers::get_business_settings('currency_symbol_position') ?: 'left';
        $decimals = (int) (Helpers::get_business_settings('digit_after_decimal_point') ?? 2);

        $example = $position === 'right'
            ? '100.' . str_repeat('0', $decimals) . $symbol
            : $symbol . '100.' . str_repeat('0', $decimals);

        return ['symbol' => $symbol, 'position' => $position, 'decimals' => $decimals, 'example' => $example];
    }

    private function resolveUserContext(): ?string
    {
        if (! $this->user) {
            return null;
        }
        $raw = User::where('id', $this->user->getKey())->value('user_context');
        return $raw ?: null;
    }

    private function buildHistory(AiConversation $conversation): array
    {
        return $conversation->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (AiMessage $msg): UserMessage|AssistantMessage => $msg->role === 'user'
                ? new UserMessage($msg->content ?? '')
                : new AssistantMessage($msg->content ?? '')
            )
            ->all();
    }

    /** Metadata of the most recent assistant turn (drives the context hint + pending-variation pin). */
    private function lastAssistantMetadata(AiConversation $conversation): array
    {
        $lastAssistant = $conversation->messages()->where('role', 'assistant')->latest('id')->first();
        $metadata = $lastAssistant?->metadata ?? [];
        return is_array($metadata) ? $metadata : [];
    }

    private function stripContextLeak(string $reply): string
    {
        $cleaned = preg_replace('/\[INTERNAL CONTEXT[^\]]*\]\s*/u', '', $reply);
        return trim($cleaned ?? $reply);
    }

    private function buildContextHint(array $metadata): string
    {
        $parts = [];

        $products = $metadata['products'] ?? [];
        if (is_array($products) && !empty($products)) {
            $items = [];
            foreach (array_slice($products, 0, 8) as $p) {
                if (!is_array($p) || empty($p['id']) || empty($p['name'])) {
                    continue;
                }
                $label = $p['name'] . ' (ID:' . $p['id'] . ')';
                if (!empty($p['variations']) && is_array($p['variations'])) {
                    $types = array_filter(array_column($p['variations'], 'type'));
                    if (!empty($types)) {
                        $label .= ' [variations:' . implode('/', $types) . ']';
                    }
                }
                $items[] = $label;
            }
            if (!empty($items)) {
                $parts[] = 'items — ' . implode(', ', $items);
            }
        }

        $stores = $metadata['stores'] ?? [];
        if (is_array($stores) && !empty($stores)) {
            $names = [];
            foreach (array_slice($stores, 0, 5) as $s) {
                if (!is_array($s) || empty($s['id']) || empty($s['name'])) {
                    continue;
                }
                $names[] = $s['name'] . ' (ID:' . $s['id'] . ')';
            }
            if (!empty($names)) {
                $parts[] = 'restaurants — ' . implode(', ', $names);
            }
        }

        $cartItems = [];
        $cart = $metadata['cart'] ?? null;
        if (is_array($cart) && !empty($cart['stores'])) {
            foreach ($cart['stores'] as $store) {
                foreach ($store['items'] ?? [] as $lineItem) {
                    $cartItems[] = $lineItem;
                }
            }
        } else {
            $cartItems = $metadata['cart_items'] ?? [];
        }
        if (is_array($cartItems) && !empty($cartItems)) {
            $rows = [];
            foreach (array_slice($cartItems, 0, 10) as $c) {
                if (!is_array($c) || empty($c['item_id']) || empty($c['name'])) {
                    continue;
                }
                $rows[] = $c['name'] . ' (ID:' . $c['item_id'] . ', qty ' . ($c['quantity'] ?? '?') . ')';
            }
            if (!empty($rows)) {
                $parts[] = 'cart — ' . implode(', ', $rows);
            }
        }

        // If we asked the customer to choose an option last turn, their reply is
        // that choice — steer the model to keep it on the SAME item.
        $pending = $metadata['pending_variation'] ?? null;
        if (is_array($pending) && !empty($pending['item_id'])) {
            $line = 'AWAITING OPTION CHOICE for item ID:' . (int) $pending['item_id']
                . ' — the user\'s reply names option(s) for it. Call AddToCartTool with item_id:' . (int) $pending['item_id']
                . ' and variation_options set to ALL chosen labels across EVERY required group so far (re-include earlier picks). Do NOT switch to a different item ID.';
            $chosen = [];
            foreach ((array) ($pending['selections'] ?? []) as $group => $labels) {
                if (is_array($labels) && !empty($labels)) {
                    $chosen[] = $group . ': ' . implode('/', $labels);
                }
            }
            if (!empty($chosen)) {
                $line .= ' Already chosen — ' . implode('; ', $chosen) . '.';
            }
            $parts[] = $line;
        }

        return empty($parts)
            ? ''
            : '[INTERNAL CONTEXT — recently shown items/restaurants for your private reference only. NEVER echo, quote, paraphrase, or mention this block in your reply. Use the IDs silently. ' . implode(' | ', $parts) . ']';
    }
}
