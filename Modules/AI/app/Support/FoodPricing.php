<?php

namespace Modules\AI\app\Support;

use App\CentralLogics\Helpers;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Model;

/**
 * Single source of truth for the price the AI chat quotes.
 *
 * Every product API response prices an item through
 * Helpers::product_discount_calculate_data(): an ACTIVE restaurant-level
 * discount (a campaign inside its date/time window) REPLACES the item's own
 * discount — it is not stacked and not max()'d. Deriving the price from the
 * food's `discount` column alone quotes a different number than the food
 * details modal for every restaurant currently running a campaign
 * (e.g. price 190, item discount 10%, restaurant discount 16% → the API and
 * storefront say 159.60 while the raw column says 171).
 *
 * Tools must eager-load `restaurant.discount` (or `item.restaurant.discount`)
 * so resolving a list of foods stays one query instead of one per row.
 */
final class FoodPricing
{
    /**
     * Effective discount + price for a food, matching the product APIs.
     *
     * Accepts any model with `discount`/`discount_type` + a `restaurant`
     * relation — Food and ItemCampaign are both priced this way by core.
     *
     * @param float|null $price Unit price to discount — pass the cart/variation
     *                          price when it differs from the item's base price.
     * @return array{discount: float, discount_type: string, discounted_price: float, discount_label: string}
     */
    public static function resolve(Model $item, ?float $price = null): array
    {
        $price      = $price ?? (float) $item->getAttribute('price');
        $restaurant = self::restaurantOf($item);

        if ($restaurant) {
            $data         = Helpers::product_discount_calculate_data($item, $restaurant);
            $discount     = (float) $data['discount_percentage'];
            $discountType = (string) $data['original_discount_type'];
        } else {
            $discount     = (float) $item->getAttribute('discount');
            $discountType = (string) $item->getAttribute('discount_type');
        }

        $discountedPrice = $discount > 0
            ? ($discountType === 'percent' ? round($price - $price * $discount / 100, 2) : round($price - $discount, 2))
            : $price;

        return [
            'discount'         => $discount,
            'discount_type'    => $discountType,
            // A flat discount configured above the price would otherwise quote a
            // negative amount to the customer.
            'discounted_price' => max($discountedPrice, 0),
            'discount_label'   => self::label($discount, $discountType),
        ];
    }

    /** Human label for a deal line — "16% off" / "20.00 off". */
    public static function label(float $discount, string $discountType): string
    {
        if ($discount <= 0) {
            return 'No discount';
        }

        return $discountType === 'percent'
            ? $discount . '% off'
            : number_format($discount, 2) . ' off';
    }

    /**
     * True when a store wide rate is in force at this restaurant right now.
     *
     * Used to pull its items into deal listings: a store wide rate discounts the whole
     * menu, so the food's own `discount` column stays 0 and a listing that filters on
     * that column alone shows nothing from the restaurant at exactly the moment it has
     * the most to sell.
     *
     * Reads the EFFECTIVE rate rather than the standing restaurant discount alone. The
     * older get_restaurant_discount() knows nothing about happy hours, so a store whose
     * only live rate is a happy hour answered false - which is precisely the case this
     * was written for.
     */
    public static function hasActiveRestaurantDiscount(?Restaurant $restaurant): bool
    {
        return $restaurant !== null && Helpers::get_effective_restaurant_discount($restaurant) !== null;
    }

    private static function restaurantOf(Model $item): ?Restaurant
    {
        return $item->relationLoaded('restaurant')
            ? $item->getRelation('restaurant')
            : $item->restaurant;
    }
}
