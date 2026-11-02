<?php

namespace App\Builder\Resources;

use App\Builder\Support\ItemPricing;
use App\CentralLogics\Helpers;
use App\Models\Food;
use Modules\Builder\ValueObjects\Storefront\FoodDetailDTO;


class FoodDetailsResource
{
    public static function fromOne(Food $item): array
    {
        $formatted = Helpers::product_data_formatting($item, false, true, app()->getLocale());
        $pricing   = ItemPricing::compute($item);

        $foodVariations = \is_array($formatted['variations'] ?? null)
            ? array_values($formatted['variations'])
            : [];

        $addOns = collect($formatted['add_ons'] ?? [])
            ->map(fn ($addon) => [
                'id'    => (int) ($addon['id'] ?? 0),
                'name'  => (string) ($addon['name'] ?? ''),
                'price' => (float) ($addon['price'] ?? 0),
            ])
            ->filter(fn (array $addon) => $addon['id'] > 0)
            ->values()
            ->all();

        $nutritionDetails = collect($formatted['nutritions_name'] ?? [])
            ->filter()
            ->implode(', ');
        $allergicIngredients = collect($formatted['allergies_name'] ?? [])
            ->filter()
            ->implode(', ');

        $vegRaw       = $item->getAttributes()['veg'] ?? null;
        $showVegBadge = (bool) config('toggle_veg_non_veg');
        $isVeg    = $showVegBadge && $vegRaw !== null && (int) $vegRaw === 1;
        $isNonVeg = $showVegBadge && $vegRaw !== null && (int) $vegRaw === 0;

        $image = $formatted['image_full_url'] ?? $item->image_full_url ?? null;
        $images = collect($formatted['images_full_url'] ?? $item->images_full_url ?? [])
            ->filter()
            ->values();
        if ($images->isEmpty() && !empty($image)) {
            $images = collect([$image]);
        }

        // StackFood tracks stock per food row, not per module: `stock_type` is
        // `unlimited` (untracked) or `limited`/`daily` (tracked), and the
        // `item_stock` accessor already returns what is left
        // (raw item_stock - sell_count).
        $tracksStock = $item->stock_type !== null && $item->stock_type !== 'unlimited';
        $stockLeft   = $tracksStock ? (int) $item->item_stock : 0;

        // Live review average — see ItemDetailResource::liveRating(). Keeps the
        // quick-view modal's rating consistent with the card and admin panel.
        $live = ItemDetailResource::liveRating($item);

        $data = [
            'id'           => (int) $item->id,
            'module_type'  => 'food',
            'store_id'     => (int) ($item->restaurant_id ?? $item->restaurant?->id ?? 0),
            'vendor_id'    => (int) ($item->restaurant?->vendor_id ?? 0),
            'module_id'    => 0,
            'zone_id'      => (int) ($item->restaurant?->zone_id ?? 0),

            'name'         => (string) ($formatted['name'] ?? $item->name ?? ''),
            'image'        => $image,
            'images'       => $images->all(),
            'rating'          => $live['rating'],
            'ratingCount'     => $live['count'],
            'ratingDistribution' => ItemDetailResource::ratingDistribution($item),
            'isVeg'        => $isVeg,
            'isNonVeg'     => $isNonVeg,
            'description'  => (string) ($formatted['description'] ?? ''),
            'nutritionDetails'    => $nutritionDetails ?: null,
            'allergicIngredients' => $allergicIngredients ?: null,

            'price'          => $pricing['oldPrice'],
            'oldPrice'       => $pricing['oldPrice'],
            'displayPrice'   => $pricing['price'],
            'discount'       => (float) $pricing['discountPercent'],
            'discount_type'  => 'percent',
            'discountSource' => $pricing['discountSource'],
            'store_discount' => 0.0,

            'maximum_cart_quantity'  => (int) ($item->maximum_cart_quantity ?? 0),
            // Inventory cap for the modal's +/- stepper. `stock` only means
            // anything when `tracksStock` is set.
            'tracksStock'            => $tracksStock,
            'stock'                  => $stockLeft,
            'available_time_starts'  => $formatted['available_time_starts'] ?? $item->available_time_starts,
            'available_time_ends'    => $formatted['available_time_ends'] ?? $item->available_time_ends,
            'available_date_starts'  => $formatted['available_date_starts'] ?? null,
            'available_date_ends'    => $formatted['available_date_ends'] ?? null,

            'food_variations' => $foodVariations,
            'add_ons'         => $addOns,
        ];

        return FoodDetailDTO::fromArray($data)->toArray();
    }
}
