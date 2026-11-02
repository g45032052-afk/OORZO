<?php

namespace Modules\AI\app\Agents\Tools;

use Modules\AI\app\Agents\AiResponseContext;
use App\Models\User;
use Modules\AI\app\Services\CartSnapshotService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * Show the customer's cart, grouped by restaurant (StackFood carts can hold
 * items from multiple restaurants — checked out one at a time). Item→Food,
 * Store→Restaurant.
 */
class GetCartItemsTool implements Tool
{
    public function __construct(
        private readonly AiResponseContext $context,
        private readonly ?User             $user    = null,
        private readonly ?string           $guestId = null,
    ) {}

    public function description(): string
    {
        return 'Show the customer\'s current cart — items grouped by restaurant, with quantities, prices, and per-restaurant + overall totals. Works for both authenticated users and guests. Use when the customer asks "show my cart", "what\'s in my cart", or "view cart".';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): string
    {
        $this->context->recordTool('GetCartItemsTool');

        if (! $this->user && ! $this->guestId) {
            return 'Cannot show cart: no customer identity available in this chat session.';
        }

        $isGuest    = $this->user === null;
        $cartUserId = $isGuest ? $this->guestId : $this->user->getKey();

        // One read, used for BOTH the cards the customer sees and the text this tool
        // hands back. It used to run its own query for the text and call the snapshot
        // separately for the cards, so the two could describe different carts - and a
        // model told it holds five items while three are rendered reasons about a cart
        // nobody is looking at.
        $items = CartSnapshotService::items($cartUserId, $isGuest);

        if (! $items) {
            return 'Cart is empty.';
        }

        $this->context->addCartItems($items);

        $byStore = [];
        foreach ($items as $item) {
            $byStore[(int) $item['store_id']][] = $item;
        }

        $summaries  = [];
        $grandTotal = 0.0;

        foreach ($byStore as $storeId => $lines) {
            $storeName    = $lines[0]['store_name'] ?? ('Restaurant #' . $storeId);
            $storeTotal   = 0.0;
            $storeLines   = [];

            foreach ($lines as $line) {
                $storeTotal += (float) $line['line_total'];
                $storeLines[] = $line['bogo_details']
                    ? $this->bundleLine($line)
                    : $this->itemLine($line);
            }

            $grandTotal  += $storeTotal;
            $summaries[]  = '"' . $storeName . '": ' . implode('; ', $storeLines)
                . ' [restaurant subtotal: ' . round($storeTotal, 2) . ']';
        }

        $storeCount = count($byStore);

        return count($items) . ' line(s) across ' . $storeCount . ' '
            . ($storeCount === 1 ? 'restaurant' : 'restaurants') . ' — '
            . implode(' | ', $summaries)
            . '. Grand total: ' . round($grandTotal, 2)
            . '. Note: each restaurant is checked out separately.';
    }

    /** An ordinary cart line. */
    private function itemLine(array $line): string
    {
        return $line['name'] . ' [ID:' . $line['item_id'] . '] x' . $line['quantity']
            . ' = ' . $line['line_total'];
    }

    /**
     * A bundle line.
     *
     * Says what is inside it and that it cannot be split, because the model is about to
     * be asked to remove things and the rule is not guessable from a price and a name.
     * The group id is spelled out: it is the only handle RemoveFromCartTool accepts for a
     * bundle, and a model cannot pass back an identifier it was never shown.
     */
    private function bundleLine(array $line): string
    {
        $details  = $line['bogo_details'];
        $contents = [];

        foreach (['buy_items' => 'buy', 'free_items' => 'free'] as $key => $label) {
            foreach ($details[$key] ?? [] as $row) {
                $name = data_get($row, 'item.name') ?? data_get($row, 'item_name') ?? 'item';
                $contents[] = $label === 'free'
                    ? $name . ' x' . data_get($row, 'quantity', 1) . ' (free)'
                    : $name . ' x' . data_get($row, 'quantity', 1);
            }
        }

        $unavailable = $details['is_available'] === false
            ? ' — UNAVAILABLE: ' . $details['unavailable_reason']
            : '';

        return 'BOGO bundle "' . $line['name'] . '" [BUNDLE:' . $details['bogo_group_id'] . ']'
            . ' x' . $line['quantity'] . ' = ' . $line['line_total']
            . ($contents ? ' containing ' . implode(' + ', $contents) : '')
            . ' (one unit — its items cannot be removed separately)'
            . $unavailable;
    }
}
