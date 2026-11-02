<?php

namespace App\Builder\Resources;

use App\Builder\Support\ItemPricing;
use App\Models\Food;
use Illuminate\Support\Facades\DB;
use Modules\Builder\ValueObjects\Storefront\ItemCardDTO;

/**
 * Canonical transformer that maps an App\Models\Food into the exact shape
 * Modules/Builder/resources/js/Components/shared/ItemCard.jsx consumes.
 *
 * Covers the union of all 8 CARD_TYPE variants:
 *   id, name, slug, image, price (final), oldPrice (original), discountPercent,
 *   rating, rating_count, currency, isVeg, isNonVeg, inCart, cartQty, isWishlist.
 *
 * StackFood is food-only, so moduleType is always 'food' and the veg/non-veg
 * badge is always module-supported (no config/module.php gate as in 6amMart).
 *
 * Accepts a $context array so callers can inject:
 *   - 'currency'         => string symbol (default '$')
 *   - 'cart_lookup'      => callable(int $itemId): ?array  // e.g. fn($id) => $cart[$id] ?? null
 *   - 'wishlist_lookup'  => callable(int $itemId): bool
 */
class ItemCardResource
{
    public static function fromCollection(iterable $items, array $context = []): array
    {
        $items = is_array($items) ? $items : iterator_to_array($items);

        // Show the LIVE review average on the card instead of the denormalized
        // `foods.avg_rating` column. That column is maintained incrementally
        // from the `rating` JSON distribution and drifts out of sync when a
        // review is deleted/edited (the removed review lingers in the JSON), so
        // it can disagree with the real reviews the admin panel counts. One
        // aggregate query for the whole collection keeps this O(1), not N+1.
        if (! isset($context['rating_lookup'])) {
            $context['rating_lookup'] = self::liveRatingLookup(
                array_values(array_filter(array_map(
                    static fn ($it) => isset($it->id) ? (int) $it->id : null,
                    $items
                )))
            );
        }

        $result = [];
        foreach ($items as $item) {
            $result[] = self::fromOne($item, $context);
        }
        return $result;
    }

    /**
     * Batch-load AVG(rating)/COUNT(*) from the reviews table for the given food
     * ids and return a lookup closure: fn(int $id): ?array{rating,count}. Uses
     * the Review model so any global scopes match what the admin panel counts.
     */
    private static function liveRatingLookup(array $foodIds): callable
    {
        $ratings = [];
        if ($foodIds !== []) {
            $rows = \App\Models\Review::query()
                ->whereIn('food_id', $foodIds)
                ->groupBy('food_id')
                ->selectRaw('food_id, AVG(rating) as avg_rating, COUNT(*) as rating_count')
                ->get();
            foreach ($rows as $row) {
                $ratings[(int) $row->food_id] = [
                    'rating' => round((float) $row->avg_rating, 1),
                    'count'  => (int) $row->rating_count,
                ];
            }
        }

        return static fn (int $id): ?array => $ratings[$id] ?? null;
    }

    public static function fromOne(Food $item, array $context = []): array
    {
        $pricing = ItemPricing::compute($item);


        $needsConfig = self::hasRequiredVariation($item);
        $vegRaw       = $item->getAttributes()['veg'] ?? null;
        $showVegBadge = (bool) config('toggle_veg_non_veg');
        $isVeg    = $showVegBadge && $vegRaw !== null && (int) $vegRaw === 1;
        $isNonVeg = $showVegBadge && $vegRaw !== null && (int) $vegRaw === 0;

        // StackFood tracks stock per food row, not per module: `stock_type` is
        // `unlimited` (untracked) or `limited`/`daily` (tracked), and the
        // `item_stock` accessor already returns what is left
        // (raw item_stock − sell_count). Same rule the host cart add enforces
        // via Helpers::addonAndVariationStockCheck().
        $tracksStock = $item->stock_type !== null && $item->stock_type !== 'unlimited';
        $stock       = $tracksStock ? (int) $item->item_stock : 0;

        $currency       = $context['currency']        ?? '$';
        $cartLookup     = $context['cart_lookup']     ?? null;
        $wishlistLookup = $context['wishlist_lookup'] ?? null;
        $ratingLookup   = $context['rating_lookup']   ?? null;

        $cartEntry = is_callable($cartLookup) ? $cartLookup($item->id) : null;

        // Prefer the live review aggregate (batched in fromCollection); fall back
        // to the stored column for a food with no reviews or a single-item call.
        $liveRating = is_callable($ratingLookup) ? $ratingLookup($item->id) : null;
        $rating      = $liveRating['rating'] ?? round((float) ($item->avg_rating ?? 0), 1);
        $ratingCount = $liveRating['count']  ?? (int) ($item->rating_count ?? 0);

        $data = [
            'id'              => $item->id,
            'name'            => $item->name,
            'slug'            => $item->slug ?? null,
            'image'           => $item->image_full_url ?? null,
            'price'           => $pricing['price'],
            'oldPrice'        => $pricing['oldPrice'],
            'discountPercent' => $pricing['discountPercent'],
            'discountSource'  => $pricing['discountSource'],
            'rating'          => $rating,
            'ratingCount'     => $ratingCount,
            'currency'        => $currency,
            'isVeg'           => $isVeg,
            'isNonVeg'        => $isNonVeg,
            'inCart'          => $cartEntry !== null,
            'cartQty'         => (int) ($cartEntry['qty'] ?? 0),
            'isWishlist'      => is_callable($wishlistLookup) ? (bool) $wishlistLookup($item->id) : false,
            'moduleType'      => 'food',
            // Inventory cap for the card's +/- stepper — see $tracksStock above.
            'stock'           => $stock,
            'tracksStock'     => $tracksStock,
            'needsConfig'     => $needsConfig,
        ];

        return ItemCardDTO::fromArray($data)->toArray();
    }

    private static function hasRequiredVariation(Food $item): bool
    {
        foreach (['variations', 'food_variations'] as $column) {
            foreach (self::decodeJsonField($item->getAttributes()[$column] ?? null) as $variation) {
                $required = $variation['required'] ?? 'off';
                if ($required === 'on' || $required === '1' || $required === 1 || $required === true) {
                    return true;
                }
            }
        }

        if ($item->relationLoaded('newVariations')) {
            foreach ($item->newVariations as $variation) {
                if ((int) ($variation->is_required ?? 0) === 1) {
                    return true;
                }
            }
            return false;
        }

        return DB::table('variations')
            ->where('food_id', $item->id)
            ->where('is_required', 1)
            ->exists();
    }

    private static function decodeJsonField($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }
}
