<?php

namespace App\Builder\Support;

use App\CentralLogics\Helpers;
use App\Models\Food;

/**
 * Single source of truth for storefront prices and discounts.
 *
 * Wraps Helpers::food_discount_calculate (which considers restaurant-wide
 * discounts and product-level discounts and picks the highest applicable one)
 * so every Builder resource — card list, detail page, food modal, quick-view
 * modal, per-variation combination — emits the same numbers. Without this,
 * lists computed discount differently from details/modal/cart and prices
 * visibly drifted between views.
 *
 * StackFood is food-only, so the 6amMart multi-module `product_discount_calculate`
 * (which returns an array) maps to `food_discount_calculate` here — StackFood's
 * `product_discount_calculate` returns only the raw amount as a float. There is
 * no flash-sale concept; `discount_type` is 'admin' (restaurant-wide) or
 * 'discount_on_product'.
 *
 * Pass `$basePrice` to compute pricing for a specific variation row;
 * otherwise the item's base price is used.
 */
final class ItemPricing
{
    public static function compute(Food $item, ?float $basePrice = null): array
    {
        $base = $basePrice ?? (float) ($item->price ?? 0);
        $restaurant = $item->restaurant ?? null;
        $calc = Helpers::food_discount_calculate($item, $base, $restaurant, true);

        // A store wide rate - a happy hour, or the restaurant's standing discount - is
        // deliberately not shown on the item. It is applied once, in the checkout summary,
        // for two reasons the item cannot answer on its own:
        //
        //   - it carries a minimum purchase and a cap, and both are judged against the whole
        //     basket. Shown per item they were simply ignored, so a card advertised a rate the
        //     order then refused because the basket never reached its minimum.
        //   - a happy hour lasts a fixed window. A price captured while one was open kept its
        //     discount into a cart the window had already closed on.
        //
        // The item's own discount still shows, but only when no store wide rate displaced it -
        // food_discount_calculate() settles that by priority, and 'admin' means it did.
        $isStoreWide = ($calc['discount_type'] ?? null) === 'admin';

        $discountAmount = $isStoreWide ? 0.0 : (float) ($calc['discount_amount'] ?? 0);
        $final = max(0.0, $base - $discountAmount);
        $percentOff = $base > 0
            ? (int) round((1 - ($final / $base)) * 100)
            : 0;

        // StackFood's discount_type: 'admin' = restaurant-wide discount,
        // 'discount_on_product' = product-level discount. Map to the canonical
        // storefront source the card/detail components expect.
        $type = $discountAmount > 0 ? ($calc['discount_type'] ?? null) : null;
        // $type can no longer be 'admin' - a store wide rate zeroes the amount above - so
        // store_discount is gone from the storefront's vocabulary by construction.
        $source = match ($type) {
            'admin' => 'store_discount',
            'discount_on_product' => 'product_discount',
            default => null,
        };

        return [
            'price'           => $final,
            'oldPrice'        => $base,
            'discountAmount'  => $discountAmount,
            'discountPercent' => $percentOff,
            'discountType'    => $calc['original_discount_type'] ?? 'percent',
            'discountSource'  => $source, // 'store_discount' | 'product_discount' | null
            'isFlashSale'     => false,   // StackFood has no flash-sale pricing
            'isStoreDiscount' => $type === 'admin',
        ];
    }
}
