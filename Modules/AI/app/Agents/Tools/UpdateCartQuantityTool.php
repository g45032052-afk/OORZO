<?php

namespace Modules\AI\app\Agents\Tools;

use Modules\AI\app\Agents\AiResponseContext;
use App\Models\Cart;
use App\Models\Food;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * Set a cart line's quantity to a new absolute value (clamped to the item's
 * maximum_cart_quantity). Item→Food, Store→Restaurant.
 */
class UpdateCartQuantityTool implements Tool
{
    public function __construct(
        private readonly AiResponseContext $context,
        private readonly ?User             $user    = null,
        private readonly ?string           $guestId = null,
    ) {}

    public function description(): string
    {
        return 'Update the quantity of an item already in the cart to a new ABSOLUTE value. Pass item_id (and item_name as a fallback) and the new quantity. For "add N more", read the current quantity from the cart and pass current+N. Works for authenticated users and guests.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'item_id'   => $schema->number()->description('The ID of the cart item to update')->required(),
            'item_name' => $schema->string()->description('Fallback item name the user referred to, used if item_id does not match')->required()->nullable(),
            'quantity'  => $schema->number()->description('The new absolute quantity (>=1). Use 0 to remove.')->required(),
        ];
    }

    public function handle(Request $request): string
    {
        $this->context->recordTool('UpdateCartQuantityTool');

        if (! $this->user && ! $this->guestId) {
            return 'Cannot modify cart: no customer identity available in this chat session.';
        }

        $args     = $request->all();
        $itemId   = (int) ($args['item_id'] ?? 0);
        $itemName = isset($args['item_name']) && $args['item_name'] !== null ? trim((string) $args['item_name']) : null;
        $quantity = (int) ($args['quantity'] ?? 0);

        $isGuest    = $this->user === null;
        $cartUserId = $isGuest ? $this->guestId : $this->user->getKey();

        $base = fn () => Cart::where('user_id', $cartUserId)->where('is_guest', $isGuest)->where('item_type', Food::class);

        // Ordinary rows only. Two separate reasons, both fatal to a bundle:
        //   - a bundle is one of each item by construction, so changing one row's count
        //     breaks the ratio the offer is defined by;
        //   - quantity 0 below is a DELETE, which would take a single leg out and strand
        //     the rest just as surely as the remove tool would have.
        // Changing how many bundles the cart holds means re-running the stock and
        // per-customer allowance checks, which is the enrolment path in the app.
        $ordinary = fn () => $base()->whereNull('bogo_group_id');

        $resolvedFoodId = $itemId;
        $row = $itemId ? $ordinary()->where('item_id', $itemId)->first() : null;
        if (! $row && $itemName) {
            $foodId = Food::where('name', 'like', "%{$itemName}%")->orderByDesc('order_count')->value('id');
            if ($foodId) {
                $resolvedFoodId = $foodId;
                $row = $ordinary()->where('item_id', $foodId)->first();
            }
        }

        if (! $row) {
            if ($resolvedFoodId) {
                $inBundle = $base()->whereNotNull('bogo_group_id')
                    ->where('item_id', $resolvedFoodId)->first();

                if ($inBundle) {
                    $name = $inBundle->item?->getAttribute('name') ?? 'That item';

                    return $name.' is part of a BOGO bundle, so its quantity cannot be changed here.'
                        .' A bundle is one unit: to change how many the customer has, they use the'
                        .' offer screen in the app. The whole bundle can be removed from the cart if'
                        .' they no longer want it.';
                }
            }

            return $itemName ? "I couldn't find \"{$itemName}\" in your cart." : "Item #{$itemId} is not in your cart.";
        }

        $name = $row->item?->getAttribute('name') ?? ('Item #' . $row->getAttribute('item_id'));

        if ($quantity <= 0) {
            $row->delete();
            $this->publishCartSnapshot($cartUserId, $isGuest);
            return "Removed {$name} from your cart.";
        }

        $maxQty = (int) ($row->item?->getAttribute('maximum_cart_quantity') ?? 0);
        if ($maxQty > 0 && $quantity > $maxQty) {
            $quantity = $maxQty;
            $row->update(['quantity' => $quantity]);
            $this->publishCartSnapshot($cartUserId, $isGuest);
            return "The maximum for {$name} is {$maxQty} per cart, so I set it to {$maxQty}.";
        }

        $row->update(['quantity' => $quantity]);
        $this->publishCartSnapshot($cartUserId, $isGuest);

        return "Updated {$name} quantity to {$quantity}.";
    }

    private function publishCartSnapshot(int|string $cartUserId, bool $isGuest): void
    {
        // Canonical flat snapshot (shared across all cart tools). The response
        // layer folds it into the per-store `cart` shape for multi-store carts.
        $this->context->addCartItems(
            \Modules\AI\app\Services\CartSnapshotService::items($cartUserId, $isGuest)
        );
    }
}
