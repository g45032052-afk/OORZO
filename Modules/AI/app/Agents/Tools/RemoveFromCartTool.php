<?php

namespace Modules\AI\app\Agents\Tools;

use Modules\AI\app\Agents\AiResponseContext;
use App\Models\Cart;
use App\Models\BogoOfferRestaurant;
use App\Models\Food;
use App\Models\Restaurant;
use App\Models\User;
use Modules\AI\app\Services\CartSnapshotService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * Remove an item from the cart, or clear the whole cart / one restaurant's
 * bucket. Item→Food, Store→Restaurant.
 */
class RemoveFromCartTool implements Tool
{
    public function __construct(
        private readonly AiResponseContext $context,
        private readonly ?User             $user    = null,
        private readonly ?string           $guestId = null,
    ) {}

    public function description(): string
    {
        return 'Remove an item from the customer\'s cart by item_id (pass item_name too as a fallback). Or clear the whole cart with clear_all:true (optionally scope to one restaurant with store_id). A BOGO bundle is NOT removed this way: it comes out as a whole unit, by passing bundle_id (the value shown as [BUNDLE:...] in the cart) — call it once to see what would go, then again with confirm:true. Individual foods inside a bundle can never be removed on their own. Works for authenticated users and guests.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'item_id'   => $schema->number()->description('The ID of the item to remove, or null when clearing')->required()->nullable(),
            'item_name' => $schema->string()->description('Fallback item name the user referred to, used if item_id does not match')->required()->nullable(),
            'clear_all' => $schema->boolean()->description('true to clear the whole cart (or one restaurant if store_id is given)')->required()->nullable(),
            'store_id'  => $schema->number()->description('Restaurant ID to scope clear_all to a single restaurant bucket, or null')->required()->nullable(),
            'bundle_id' => $schema->string()->description('The BOGO bundle to remove. Prefer the value shown as [BUNDLE:...] in the cart. The offer id also works. Removes every item in that bundle, including the free one. Null when removing an ordinary item.')->required()->nullable(),
            'confirm'   => $schema->boolean()->description('Pass true only after the customer has agreed to remove the whole bundle. Without it the tool only reports what would be removed.')->required()->nullable(),
        ];
    }

    public function handle(Request $request): string
    {
        $this->context->recordTool('RemoveFromCartTool');

        if (! $this->user && ! $this->guestId) {
            return 'Cannot modify cart: no customer identity available in this chat session.';
        }

        $args     = $request->all();
        $itemId   = ($args['item_id'] ?? null) !== null ? (int) $args['item_id'] : null;
        $itemName = isset($args['item_name']) && $args['item_name'] !== null ? trim((string) $args['item_name']) : null;
        $clearAll = (bool) ($args['clear_all'] ?? false);
        $storeId  = ($args['store_id'] ?? null) !== null ? (int) $args['store_id'] : null;
        $bundleId = isset($args['bundle_id']) && $args['bundle_id'] !== null ? trim((string) $args['bundle_id']) : null;
        $confirm  = (bool) ($args['confirm'] ?? false);

        $isGuest    = $this->user === null;
        $cartUserId = $isGuest ? $this->guestId : $this->user->getKey();

        $base = fn () => Cart::where('user_id', $cartUserId)->where('is_guest', $isGuest)->where('item_type', Food::class);

        if ($bundleId !== null && $bundleId !== '') {
            return $this->removeBundle($base, $bundleId, $confirm, $cartUserId, $isGuest);
        }

        if ($clearAll) {
            $deleted = $base()->when($storeId, fn ($q) => $q->where('restaurant_id', $storeId))->delete();
            $this->publishCartSnapshot($cartUserId, $isGuest);
            return $deleted > 0
                ? ($storeId ? "Cleared that restaurant's items from your cart." : 'Your cart has been cleared.')
                : 'Your cart was already empty.';
        }

        // Ordinary rows only. The same food is often in the cart twice - once on its own
        // and once as a leg of a bundle - and without this fence "remove the pizza" can
        // delete the bundle's copy, leaving the rest of the bundle stranded: buy items
        // with no free item, or a free item with nothing earning it.
        $ordinary = fn () => $base()->whereNull('bogo_group_id');

        $row = null;
        $resolvedFoodId = $itemId;

        if ($itemId) {
            $row = $ordinary()->where('item_id', $itemId)->first();
        }
        if (! $row && $itemName) {
            $foodId = Food::where('name', 'like', "%{$itemName}%")->orderByDesc('order_count')->value('id');
            if ($foodId) {
                $resolvedFoodId = $foodId;
                $row = $ordinary()->where('item_id', $foodId)->first();
            }
        }

        if (! $row) {
            // Before answering "not in your cart", check whether it is - inside a bundle.
            // The customer can see it there, so that reply would read as a plain error.
            if ($resolvedFoodId) {
                $inBundle = $base()->whereNotNull('bogo_group_id')
                    ->where('item_id', $resolvedFoodId)->first();

                if ($inBundle) {
                    return $this->describeBundleOnly($inBundle, $cartUserId, $isGuest);
                }
            }

            return $itemName
                ? "I couldn't find \"{$itemName}\" in your cart."
                : "Item #{$itemId} is not in your cart.";
        }

        $name = $row->item?->getAttribute('name') ?? ('Item #' . $row->getAttribute('item_id'));
        $row->delete();
        $this->publishCartSnapshot($cartUserId, $isGuest);

        return "Removed {$name} from your cart.";
    }

    /**
     * Remove a whole bundle, but only once the customer has agreed to it.
     *
     * The confirmation is enforced here rather than left to the agent's instructions: a
     * model that skips the step would otherwise silently discard a bundle the customer
     * spent several screens configuring. Structural, so prompt drift cannot reach it.
     */
    private function removeBundle(callable $base, string $bundleId, bool $confirm, int|string $cartUserId, bool $isGuest): string
    {
        $rows = $base()->where('bogo_group_id', $bundleId)->get();

        // bogo_details carries THREE identifiers - bogo_group_id (this cart line),
        // bundle_id (the restaurant's enrolment) and bogo_offer_id (the offer) - and a
        // caller reaching for "the bundle id" picks the wrong one about as often as the
        // right one. Rather than answering "not in your cart" about a bundle plainly
        // sitting in it, the other two resolve to the same line.
        if ($rows->isEmpty() && is_numeric($bundleId)) {
            $numeric = (int) $bundleId;

            // The number could be either id, and the two spaces overlap: enrolment 8 and
            // offer 8 both exist and name different things. So both readings are resolved
            // SEPARATELY and only agreement counts. Reading them as one OR query meant a
            // caller naming an offer that is not in the cart could match some other
            // bundle through the enrolment reading and silently delete it.
            $groupsFor = fn ($query) => $query->whereNotNull('bogo_group_id')
                ->pluck('bogo_group_id')->unique()->values()->all();

            $byOffer = $groupsFor($base()->where('bogo_offer_id', $numeric));

            $enrolment = BogoOfferRestaurant::find($numeric);
            $byEnrolment = $enrolment
                ? $groupsFor($base()
                    ->where('bogo_offer_id', $enrolment->bogo_offer_id)
                    ->where('restaurant_id', $enrolment->restaurant_id))
                : [];

            $groups = array_values(array_unique(array_merge($byOffer, $byEnrolment)));

            if (count($groups) === 1) {
                $bundleId = (string) $groups[0];
                $rows = $base()->where('bogo_group_id', $bundleId)->get();
            } elseif (count($groups) > 1) {
                // Either the same offer sits in the cart twice - two lines configured
                // separately, not interchangeable - or the number reads as two different
                // bundles. Both cases need the customer to say which; guessing deletes
                // something they did not choose.
                return 'That could mean more than one bundle in the cart, so I need the exact one.'
                    .' Show the customer their cart and use the [BUNDLE:...] value of the one they mean.';
            }
        }

        if ($rows->isEmpty()) {
            return "That offer is not in your cart.";
        }

        $entry = collect(CartSnapshotService::items($cartUserId, $isGuest))
            ->first(fn ($e) => ($e['bogo_details']['bogo_group_id'] ?? null) === $bundleId);

        $title = $entry['name'] ?? 'this offer';
        $contents = $this->contentsOf($entry);

        if (! $confirm) {
            return 'Removing "' . $title . '" takes out the whole bundle'
                . ($contents ? ' — ' . $contents : '')
                . ($entry ? ' (' . $entry['line_total'] . ')' : '')
                . '. Its items cannot be removed separately. Confirm with the customer, then'
                . ' call again with confirm:true and the same bundle_id.';
        }

        $base()->where('bogo_group_id', $bundleId)->delete();
        $this->publishCartSnapshot($cartUserId, $isGuest);

        return 'Removed the "' . $title . '" bundle from your cart'
            . ($contents ? ', including ' . $contents : '') . '.';
    }

    /**
     * The food is in the cart, but only as part of a bundle. Say so, and offer the only
     * action that exists for it - taking the whole bundle out.
     */
    private function describeBundleOnly($row, int|string $cartUserId, bool $isGuest): string
    {
        $groupId = $row->bogo_group_id;
        $entry = collect(CartSnapshotService::items($cartUserId, $isGuest))
            ->first(fn ($e) => ($e['bogo_details']['bogo_group_id'] ?? null) === $groupId);

        $name  = $row->item?->getAttribute('name') ?? 'That item';
        $title = $entry['name'] ?? 'a BOGO offer';

        return $name . ' is part of your "' . $title . '" bundle, so it cannot be removed on its own.'
            . ' The whole bundle can come out'
            . (($c = $this->contentsOf($entry)) ? ' — ' . $c : '')
            . '. If the customer wants that, call this tool with bundle_id:' . $groupId . '.';
    }

    /** "2x Meat Pizza + 1x Coke (free)", from a snapshot entry. */
    private function contentsOf(?array $entry): string
    {
        if (! $entry || empty($entry['bogo_details'])) {
            return '';
        }

        $parts = [];
        foreach (['buy_items' => false, 'free_items' => true] as $key => $isFree) {
            foreach ($entry['bogo_details'][$key] ?? [] as $row) {
                $name = data_get($row, 'item.name') ?? 'item';
                $parts[] = data_get($row, 'quantity', 1) . 'x ' . $name . ($isFree ? ' (free)' : '');
            }
        }

        return implode(' + ', $parts);
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
