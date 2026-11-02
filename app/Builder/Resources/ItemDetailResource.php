<?php

namespace App\Builder\Resources;

use App\Builder\Support\CardContext;
use App\Builder\Support\ItemPricing;
use App\CentralLogics\Helpers;
use App\Models\Food;
use App\Models\Review;
use Illuminate\Support\Str;
use Modules\Builder\Contracts\WishlistProvider;
use Modules\Builder\ValueObjects\Storefront\ItemDetailDTO;

/**
 * Maps an App\Models\Food into the item-details page shape (ItemDetailDTO).
 * StackFood is food-only: moduleType is always 'food', reviews key on
 * `food_id`, the reviewer's restaurant (not store) authors the reply, and
 * there is no HostScope to drop. Multi-axis grocery/pharmacy fields
 * (unit, stock, generic) degrade to null/empty.
 */
class ItemDetailResource
{
    public static function fromOne(Food $item): array
    {
        $formatted = Helpers::product_data_formatting($item, false, true, app()->getLocale());

        $images = self::collectImages($formatted, $item);
        if (empty($images)) {
            // Host-served placeholder. dynamicAsset() emits the mode-correct
            // path (/assets/... in public-mode, /public/assets/... in root-mode);
            // 6amMart's hardcoded `2.jpg` doesn't exist in StackFood, so use the
            // food default image that ships with the admin assets.
            $images = [dynamicAsset('assets/admin/img/100x100/food-default-image.png')];
        }

        $variationCombinations = self::detailVariationCombinations($formatted, $item);
        $pricing = ItemPricing::compute($item);

        // StackFood tracks stock per food row, not per module: `stock_type` is
        // `unlimited` (untracked) or `limited`/`daily` (tracked), and the
        // `item_stock` accessor already returns what is left
        // (raw item_stock − sell_count). Same rule the host cart add enforces
        // via Helpers::addonAndVariationStockCheck().
        $tracksStock = $item->stock_type !== null && $item->stock_type !== 'unlimited';
        $stockLeft   = $tracksStock ? (int) $item->item_stock : 0;

        // Live review average so the detail headline agrees with the card and
        // the admin panel, rather than the drift-prone stored avg_rating column.
        $live = self::liveRating($item);

        $data = [
            'id' => $item->id,
            'name' => $formatted['name'] ?? $item->name,
            'unit' => $item->unit_type ?? null,
            'currency' => CardContext::default()['currency'] ?? '$',
            'price' => $pricing['price'],
            'oldPrice' => $pricing['oldPrice'],
            'discountPercent' => $pricing['discountPercent'],
            'discountType' => $pricing['discountType'],
            'discountSource' => $pricing['discountSource'],
            'rating' => $live['rating'],
            'ratingCount' => $live['count'],
            'reviewCount' => (int) ($formatted['review_count'] ?? 0),
            // 5-bucket distribution for the "View All" reviews drawer's
            // summary bars. One GROUP-BY query, status-approved only.
            'ratingDistribution' => self::ratingDistribution($item),
            // `tracksStock` false means the row is `unlimited` — the storefront
            // must then ignore `stock` entirely, otherwise an untracked row
            // sitting at 0 reads as depleted and freezes the quantity stepper
            // behind a permanent "Out of Stock".
            'tracksStock' => $tracksStock,
            'inStock' => !$tracksStock || $stockLeft > 0,
            'stock' => $stockLeft,
            'lowStockThreshold' => 10,
            'maxCartQuantity' => (int) ($item->maximum_cart_quantity ?? 0),
            'moduleType' => 'food',
            'genericName' => null,
            'images' => $images,
            'variations' => self::detailVariations($formatted),
            'variationCombinations' => $variationCombinations,
            'tags' => $item->tags->pluck('tag')->filter()->values()->all(),
            'description' => (string) ($formatted['description'] ?? ''),
            'reviews' => self::detailReviews($item),
            'isWishlist' => self::isWishlist((int) $item->id),
            'nutritionsName' => collect($formatted['nutritions_name'] ?? [])->filter()->values()->all(),
            'allergiesName' => collect($formatted['allergies_name'] ?? [])->filter()->values()->all(),
            'seoTitle' => $formatted['meta_title'] ?? null,
            'seoDescription' => $formatted['meta_description'] ?? null,
            'seoImage' => $formatted['meta_image'] ?? ($images[0] ?? null),
        ];

        return ItemDetailDTO::fromArray($data)->toArray();
    }

    /**
     * StackFood food carries a single `image_full_url`; some installs also keep
     * an `images` JSON column. Prefer multiples when present, else the single.
     */
    private static function collectImages($formatted, Food $item): array
    {
        $multi = $formatted['images_full_url'] ?? $item->images_full_url ?? null;
        $images = collect(is_array($multi) ? $multi : [])
            ->filter()
            ->values();

        if ($images->isEmpty()) {
            $single = $formatted['image_full_url'] ?? $item->image_full_url ?? null;
            if (!empty($single)) {
                $images = collect([$single]);
            }
        }

        return $images->all();
    }

    private static function isWishlist(int $itemId): bool
    {
        try {
            return app(WishlistProvider::class)->has($itemId);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private static function detailVariations($formatted): array
    {
        // StackFood keeps multi-select choices in the `choice_options` JSON
        // column; product_data_formatting leaves it raw, so decode here.
        $choiceOptions = $formatted['choice_options'] ?? null;
        if (is_string($choiceOptions)) {
            $choiceOptions = json_decode($choiceOptions, true) ?: [];
        }

        return collect(is_array($choiceOptions) ? $choiceOptions : [])
            ->map(function ($choice) {
                $title = (string) ($choice['title'] ?? $choice['name'] ?? 'Option');
                $isColor = \str_contains(\strtolower($title), 'color');

                return [
                    'title' => $title,
                    'type' => $isColor ? 'color' : 'text',
                    'required' => true,
                    'options' => collect($choice['options'] ?? [])
                        ->map(function ($option) use ($title, $isColor) {
                            $label = (string) $option;

                            return [
                                'id' => Str::slug($title . '-' . $label),
                                'label' => $label,
                                'value' => $isColor ? $label : null,
                                'variantKey' => self::normalizeVariationValue($label),
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->filter(fn (array $choice) => $choice['options'] !== [])
            ->values()
            ->all();
    }

    private static function detailVariationCombinations($formatted, Food $item): array
    {
        return collect($formatted['variations'] ?? [])
            ->filter(fn ($variation) => \filled($variation['type'] ?? null))
            ->mapWithKeys(function ($variation) use ($item) {
                $originalPrice = (float) ($variation['price'] ?? 0);
                // Apply the same restaurant/product discount to each
                // combination's base price so the modal's per-combo display
                // matches the page header (and the cart line).
                $pricing = ItemPricing::compute($item, $originalPrice);
                $stock = (int) ($variation['stock'] ?? 0);

                return [
                    (string) $variation['type'] => [
                        'price' => $pricing['price'],
                        'oldPrice' => $pricing['oldPrice'],
                        'discountPercent' => $pricing['discountPercent'],
                        'stock' => $stock,
                        // Legacy JSON variations carry their own stock; the new
                        // variation-option system tracks it on VariationOption
                        // instead, where this shape cannot reach it.
                        'inStock' => $stock > 0,
                    ],
                ];
            })
            ->all();
    }

    /**
     * Map a single Review row to the shape the frontend expects.
     * Shared between `detailReviews()` (first-page bootstrap) and
     * `ItemProvider::listReviews()` (paginated drawer fetch) so both
     * surfaces render identical cards.
     */
    public static function reviewRow(Review $review): array
    {
        $images = collect(self::decodeJson($review->attachment ?? []))
            ->map(function ($image) {
                if (!\is_string($image) || $image === '') {
                    return null;
                }
                return Helpers::get_full_url('review', $image, 'public');
            })
            ->filter()
            ->values()
            ->all();

        return [
            'id' => $review->id,
            'authorName' => $review->customer?->f_name
                ? \trim(($review->customer?->f_name ?? '') . ' ' . ($review->customer?->l_name ?? ''))
                : ($review->customer?->name ?? 'Customer'),
            'avatar' => $review->customer?->image_full_url ?? null,
            'rating' => round((float) ($review->rating ?? 0), 1),
            'date' => optional($review->created_at)->format('F j, Y'),
            'text' => (string) ($review->comment ?? ''),
            'images' => $images,
            'hasReply' => \filled($review->reply ?? null),
            'reply' => (string) ($review->reply ?? ''),
            'replyAuthor' => $review->restaurant?->name ?? null,
            'replyDate' => optional($review->replied_at ?? $review->updated_at)->format('F j, Y'),
        ];
    }

    private static function detailReviews(Food $item): array
    {
        $reviews = $item->reviews()
            ->where('status', 1)
            ->take(12)
            ->with([
                'customer',
                'restaurant:id,name',
            ])
            ->get();

        return $reviews
            ->map(fn (Review $review) => self::reviewRow($review))
            ->values()
            ->all();
    }

    private static function normalizeVariationValue(string $value): string
    {
        return \preg_replace('/\s+/', '', \trim($value)) ?? '';
    }

    private static function decodeJson($value): array
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

    /**
     * Approved-review counts grouped by integer star bucket (5..1).
     * Returns every bucket so the drawer's bar chart renders a row
     * even for empty buckets. Public so FoodDetailsResource can reuse it.
     */
    /**
     * Live AVG(rating)/COUNT(*) for a single food from the reviews table (all
     * reviews, matching how the admin panel counts them). Falls back to the
     * stored avg_rating/rating_count columns when the food has no reviews.
     * Public so FoodDetailsResource (the quick-view modal) can reuse it.
     */
    public static function liveRating(Food $item): array
    {
        $agg = Review::query()
            ->where('food_id', $item->id)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as rating_count')
            ->first();

        $count = (int) ($agg->rating_count ?? 0);

        return [
            'rating' => $count > 0
                ? round((float) $agg->avg_rating, 1)
                : round((float) ($item->avg_rating ?? 0), 1),
            'count'  => $count > 0
                ? $count
                : (int) ($item->rating_count ?? 0),
        ];
    }

    public static function ratingDistribution(Food $item): array
    {
        $rows = Review::query()
            ->where('food_id', $item->id)
            ->where('status', 1)
            ->selectRaw('ROUND(rating) as bucket, COUNT(*) as cnt')
            ->groupBy('bucket')
            ->pluck('cnt', 'bucket');

        $out = [];
        foreach ([5, 4, 3, 2, 1] as $star) {
            $out[$star] = (int) ($rows[$star] ?? 0);
        }
        return $out;
    }
}
