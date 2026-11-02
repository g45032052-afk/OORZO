<?php

namespace App\Builder\Support;

use Modules\Builder\Services\StorefrontContext;

/**
 * Shared default $context array for ItemCardResource calls.
 * Used by ItemProvider and CategoryProvider so currency (and future
 * cart/wishlist hooks) come from one place.
 */
class CardContext
{
    public static function default(): array
    {
        $context = app(StorefrontContext::class);

        return [
            'currency' => $context->getCurrencySymbol(),
            // 'cart_lookup'     => fn (int $id) => /* hook a cart lookup */,
            // 'wishlist_lookup' => fn (int $id) => /* hook a wishlist lookup */,
        ];
    }
}
