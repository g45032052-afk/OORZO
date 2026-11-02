<?php

namespace App\Builder;

use App\Models\Wishlist;
use Illuminate\Validation\ValidationException;
use Modules\Builder\Contracts\WishlistProvider as WishlistProviderContract;
use Modules\Builder\Services\StorefrontContext;

/**
 * StackFood wishlist adapter.
 *
 * StackFood's `wishlists` table keys food items by `food_id` (vs 6amMart's
 * polymorphic `item_id` + `store_id`). The storefront wishlist is for food, so
 * the contract's $itemId is a food id → `food_id`. The model has no `$fillable`,
 * so writes use property-assignment + save() to avoid a MassAssignmentException.
 */
class WishlistProvider implements WishlistProviderContract
{
    public function __construct(private StorefrontContext $context)
    {
    }

    public function toggle(int $itemId): array
    {
        $userId = $this->requireUserId();

        $existing = Wishlist::query()
            ->where('user_id', $userId)
            ->where('food_id', $itemId)
            ->first();

        if ($existing) {
            $existing->delete();
            $inWishlist = false;
        } else {
            $wishlist = new Wishlist();
            $wishlist->user_id = $userId;
            $wishlist->food_id = $itemId;
            $wishlist->save();
            $inWishlist = true;
        }

        return [
            'inWishlist' => $inWishlist,
            'count'      => $this->countFor($userId),
        ];
    }

    public function count(): int
    {
        $userId = $this->context->getUserId();
        return $userId ? $this->countFor($userId) : 0;
    }

    public function has(int $itemId): bool
    {
        $userId = $this->context->getUserId();
        if (!$userId) {
            return false;
        }
        return Wishlist::query()
            ->where('user_id', $userId)
            ->where('food_id', $itemId)
            ->exists();
    }

    private function countFor(int $userId): int
    {
        return Wishlist::query()
            ->where('user_id', $userId)
            ->whereNotNull('food_id')
            ->count();
    }

    private function requireUserId(): int
    {
        $userId = $this->context->getUserId();
        if (!$userId) {
            throw ValidationException::withMessages([
                '_form' => __('Sign in required'),
            ]);
        }
        return $userId;
    }
}
