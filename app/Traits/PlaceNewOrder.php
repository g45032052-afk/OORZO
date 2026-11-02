<?php

namespace App\Traits;

use App\Models\Cart;
use App\Models\Food;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\ItemCampaign;
use App\Scopes\RestaurantScope;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\CentralLogics\CouponLogic;
use App\Models\AddOn;
use App\Models\CustomerAddress;
use App\Models\OrderDetail;
use App\Models\OrderEditLog;
use App\Models\OrderReference;
use App\Models\Zone;
use App\Models\ZoneDeliveryOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\CentralLogics\CustomerLogic;

trait PlaceNewOrder
{
    use ManagesProCustomerSubscription;
    use HasSurgePrice;
    // bogoFrozenUnitPrices()/bogoLinePrice() come from here so the quote, the order and an
    // edited order all price a bundle by the one rule.
    use HandlesBogoCart;

    private function makeOrderDetails($carts, $order, $restaurant)
    {
        $total_addon_price = 0;
        $product_price = 0;
        $restaurant_discount_amount = 0;
        $product_data = [];
        $order_details = [];
        $variations = [];
        $discount_on_product_by = 'vendor';
        // One lookup for the whole cart. The quote has to price a bundle the same way the
        // order will, or the tax and summary screens promise a total checkout does not honour.
        $bogo_frozen_prices = $this->bogoFrozenUnitPrices($carts, (int) $order->restaurant_id);

        foreach ($carts as $c) {
            $isCampaign = false;
            if ($c['item_type'] === 'App\Models\ItemCampaign' || $c['item_type'] === 'AppModelsItemCampaign') {
                $product = ItemCampaign::active()->find($c['item_id']);
                $isCampaign = true;
            } else {
                $product = Food::withoutGlobalScope(RestaurantScope::class)->active()->find($c['item_id']);
            }
            if ($product) {
                if ($product->restaurant_id != $order->restaurant_id) {
                    return [
                        'status_code' => 403,
                        'code' => 'different_restaurants',
                        'message' => translate('messages.Please_select_items_from_the_same_restaurant'),
                    ];
                }

                if ($product?->maximum_cart_quantity && $c['quantity'] > $product?->maximum_cart_quantity) {
                    return [
                        'status_code' => 403,
                        'code' => 'quantity',
                        'message' => translate('messages.maximum_cart_quantity_limit_over'),
                    ];
                }

                $product_variations = json_decode($product->variations, true);

                if (count($product_variations)) {
                    $variation_data = Helpers::get_varient($product_variations, gettype($c['variations']) == 'array' ? $c['variations'] : json_decode($c['variations'], true), $product->id);
                    $price = $product['price'] + $variation_data['price'];
                    $variations = $variation_data['variations'];
                } else {
                    $price = $product['price'];
                }

                $product = Helpers::product_data_formatting(data: $product, multi_data: false, trans: false, local: app()->getLocale(), maxDiscount: false);
                $addon_data = Helpers::calculate_addon_price(AddOn::withoutGlobalScope(RestaurantScope::class)->whereIn('id', $c['add_on_ids'])->get(), $c['add_on_qtys']);

                // A bundle line is priced off the enrolment, never off the menu, and its
                // add-ons are already inside that price. The free line is worth nothing here:
                // it is the offer's give, so it must not be quoted or taxed as a sale.
                $isBogo = (bool) data_get($c, 'bogo_group_id');

                if ($isBogo) {
                    [$price] = $this->bogoLinePrice($bogo_frozen_prices, $c, $price);
                    $addon_data['total_add_on_price'] = 0;
                }

                // The food's own discount is the promotion's business - the bundle price is
                // built from real prices and the free item is the offer, so taking the item
                // discount as well discounts the same thing twice.
                $product_discount = $isBogo
                    ? ['discount_type' => 'discount_on_product', 'discount_amount' => 0, 'discount_percentage' => 0]
                    : Helpers::food_discount_calculate($product, $price, $restaurant, false);

                $or_d = [
                    'food_id' => $isCampaign ? null : $c['item_id'],
                    'item_campaign_id' => $isCampaign ? $c['item_id'] : null,
                    'food_details' => json_encode($product),
                    'quantity' => $c['quantity'],
                    'price' => round($price, config('round_up_to_digit')),

                    'category_id' => collect(is_string($product->category_ids) ? json_decode($product->category_ids, true) : $product->category_ids)->firstWhere('position', 1)['id'] ?? null,
                    'tax_amount' => 0,
                    'tax_status' => null,

                    'discount_on_product_by' => $product_discount['discount_type'],
                    'discount_type' => $product_discount['discount_type'],
                    'discount_on_food' => $product_discount['discount_amount'],
                    'discount_percentage' => $product_discount['discount_percentage'],

                    // 'variant' => json_encode($c['variant']),
                    'variation' => json_encode($variations),
                    'add_ons' => json_encode($addon_data['addons']),

                    'total_add_on_price' => round($addon_data['total_add_on_price'], config('round_up_to_digit')),
                    'addon_discount' => 0,

                    // Carried onto the line, not merely read above. applyStoreWideDiscount()
                    // runs at the end of this method and asks each line for its bogo_group_id -
                    // see bogoLineIsDiscountable(). Without these keys every bundle line read as
                    // an ordinary one, so the quote discounted the bundle and counted it towards
                    // min_purchase, while place_order - which builds its own rows and does carry
                    // them - did neither. The customer was quoted one figure and charged another.
                    'bogo_offer_id' => data_get($c, 'bogo_offer_id'),
                    'bogo_group_id' => data_get($c, 'bogo_group_id'),
                    'is_free_item' => (int) data_get($c, 'is_free_item', 0),

                    'created_at' => now(),
                    'updated_at' => now()
                ];


                $total_addon_price += $or_d['total_add_on_price'];
                $product_price += $price * $or_d['quantity'];
                $restaurant_discount_amount += $or_d['discount_on_food'] * $or_d['quantity'] ?? 0;
                $order_details[] = $or_d;
                $addon_data[] = $addon_data['addons'];
            } else {
                // active() is more than "the row exists": the food, its category and its
                // restaurant all have to be switched on. A bare "Product not found" leaves the
                // customer at a checkout they cannot complete with no idea which line is at
                // fault, so name it when it can be named.
                $missing = $isCampaign
                    ? ItemCampaign::withoutGlobalScopes()->find($c['item_id'])
                    : Food::withoutGlobalScope(RestaurantScope::class)->find($c['item_id']);

                return [
                    'status_code' => 403,
                    'code' => 'not_found',
                    'message' => $missing
                        ? $missing->name.' '.translate('messages.is_currently_unavailable')
                        : translate('messages.product_not_found'),
                ];
            }
        }


        $discount_on_product_by = 'vendor';
        $discount = $restaurant_discount_amount;
        $restaurantDiscount = Helpers::get_effective_restaurant_discount($restaurant);
        if (isset($restaurantDiscount)) {
            $applied = $this->applyStoreWideDiscount($order_details, $restaurantDiscount, $restaurant);

            $order_details = $applied['order_details'];
            $discount = $applied['amount'];
            $discount_on_product_by = $applied['bearer'];
        }

        return [
            'order_details' => $order_details,
            'total_addon_price' => $total_addon_price,
            'product_price' => $product_price,
            'restaurant_discount_amount' => $discount,
            'discount_on_product_by' => $discount_on_product_by,
            'product_data' => $product_data

        ];
    }

    private function makePosOrderDetails($carts, $restaurant)
    {
        $total_addon_price = 0;
        $product_price = 0;
        $restaurant_discount_amount = 0;
        $product_data = [];
        $order_details = [];
        $variations = [];
        $discount_on_product_by = 'vendor';
        foreach ($carts as $c) {
            if (is_array($c)) {
                $isCampaign = false;
                if (isset($c['item_type']) && ($c['item_type'] === 'App\Models\ItemCampaign' || $c['item_type'] === 'AppModelsItemCampaign')) {
                    $product = ItemCampaign::with('module')->active()->find($c['item_id']);
                    $isCampaign = true;
                } else {
                    // $c['item_id'] ?? $c['id'], not isset() ?: - the ternary yields the boolean true
                    // rather than the id, so the lookup became find(true) = find(1) and priced
                    // whatever food happens to be first. A POS row carries 'id' alone, which is
                    // why it went unnoticed; every other lookup in this file reads item_id.
                    $product = Food::withoutGlobalScope(RestaurantScope::class)->active()->find($c['item_id'] ?? $c['id']);
                }

                if ($product) {
                    if ($product->restaurant_id != $restaurant->id) {
                        return [
                            'status_code' => 403,
                            'code' => 'different_restaurants',
                            'message' => translate('messages.Please_select_food_from_the_same_restaurant'),
                        ];
                    }

                    if ($product?->maximum_cart_quantity && $c['quantity'] > $product?->maximum_cart_quantity) {
                        return [
                            'status_code' => 403,
                            'code' => 'quantity',
                            'message' => translate('messages.maximum_cart_quantity_limit_over'),
                        ];
                    }

                    $product_variations = json_decode($product->variations, true);

                    if (count($product_variations)) {
                        $variation_data = Helpers::get_varient($product_variations, $c['variations'], $product->id);
                        $price = $product['price'] + $variation_data['price'];
                        $variations = $variation_data['variations'];
                    } else {
                        $price = $product['price'];
                    }
                    $product = Helpers::product_data_formatting(data: $product, multi_data: false, trans: false, local: app()->getLocale(), maxDiscount: false);
                    $addon_data = Helpers::calculate_addon_price(AddOn::whereIn('id', $c['add_ons'])->get(), $c['add_on_qtys']);
                    $product_discount = Helpers::food_discount_calculate($product, $price, $restaurant, false);

                    $or_d = [
                        'food_id' => $isCampaign ? null : $c['id'],
                        'item_campaign_id' => $isCampaign ? $c['id'] : null,
                        'food_details' => json_encode($product),
                        'quantity' => $c['quantity'],
                        'price' => round($price, config('round_up_to_digit')),

                        'category_id' => collect(is_string($product->category_ids) ? json_decode($product->category_ids, true) : $product->category_ids)->firstWhere('position', 1)['id'] ?? null,
                        'tax_amount' => 0,
                        'tax_status' => null,

                        'discount_on_product_by' => $product_discount['discount_type'],
                        'discount_type' => $product_discount['discount_type'],
                        'discount_on_food' => $product_discount['discount_amount'],
                        'discount_percentage' => $product_discount['discount_percentage'],

                        'variant' => json_encode($c['variant']),
                        'variation' => json_encode($variations),
                        'add_ons' => json_encode($addon_data['addons']),

                        'total_add_on_price' => round($addon_data['total_add_on_price'], config('round_up_to_digit')),
                        'addon_discount' => 0,

                        'created_at' => now(),
                        'updated_at' => now()
                    ];


                    $total_addon_price += $or_d['total_add_on_price'];
                    $product_price += $price * $or_d['quantity'];
                    $restaurant_discount_amount += $or_d['discount_on_food'] * $or_d['quantity'] ?? 0;
                    $order_details[] = $or_d;
                    $addon_data[] = $addon_data['addons'];
                } else {
                    return [
                        'status_code' => 403,
                        'code' => 'not_found',
                        'message' => translate('messages.product_not_found'),
                    ];
                }
            }
        }



        $discount_on_product_by = 'vendor';
        $discount = $restaurant_discount_amount;
        $store_wide_amount = 0.0;
        $restaurantDiscount = Helpers::get_effective_restaurant_discount($restaurant);
        if (isset($restaurantDiscount)) {
            $applied = $this->applyStoreWideDiscount($order_details, $restaurantDiscount, $restaurant);

            $order_details = $applied['order_details'];
            $discount = $applied['amount'];
            $discount_on_product_by = $applied['bearer'];
            $store_wide_amount = $applied['store_wide_amount'];
        }

        $finalCart = [];
        foreach ($carts as $key => $cart) {
            if (is_array($cart)) {
                // discount_on_food is per unit whatever the discount came from, so the line is
                // simply the unit times the quantity. This used to branch, because a store wide
                // discount was written as the whole line and an item's own per unit.
                $cart['discount'] = ($order_details[$key]['discount_on_food'] ?? 0)
                    * ($order_details[$key]['quantity'] ?? 1);
            }
            $finalCart[] = $cart;
        }
        $finalCart['restaurant_id'] = $product?->restaurant_id;

        session()->put('cart', collect($finalCart));

        $total_price = $product_price + $total_addon_price - $discount;
        session()->put('cart_total_price', $total_price);

        return [
            'order_details' => $order_details,
            'total_addon_price' => $total_addon_price,
            'product_price' => $product_price,
            'restaurant_discount_amount' => $discount,
            // The part a store wide rate actually gave. restaurant_discount_amount can also hold
            // a campaign line's own discount, which no happy hour is entitled to be billed for.
            'store_wide_amount' => $store_wide_amount,
            'discount_on_product_by' => $discount_on_product_by,
            'product_data' => $product_data
        ];
    }


    private function makeEditOrderDetails($order, $carts, $restaurant, $editedBy, $editLogs = null)
    {
        DB::beginTransaction();
        // Read before the rebuild replaces them: what the edit adds is judged against what the
        // order already had, so an untouched bundle is never put back through the offer rules.
        $bundles_before = $this->bogoBundlesPerOffer($order->details, (int) $restaurant->id);
        Helpers::decreaseSellCount(order_details: $order->details);
        $total_addon_price = 0;
        $product_price = 0;
        $restaurant_discount_amount = 0;
        $order_details = [];
        $variations = [];
        $coupon_created_by = null;
        $coupon = null;
        $free_delivery_by = null;
        $has_new_item = false;

        $discount_on_product_by = 'vendor';
        if ($order->coupon_code) {
            $coupon_check = Helpers::coupon_check(coupon_code: $order->coupon_code, restaurant_id: $restaurant->id, user_id: $order->user_id, is_guest: $order->is_guest);
            $coupon = data_get($coupon_check, 'coupon');
            $coupon_created_by = data_get($coupon_check, 'coupon_created_by', null);
            $free_delivery_by = data_get($coupon_check, 'free_delivery_by', null);
        }

        if (count($carts) == 0) {
            // The transaction is already open and decreaseSellCount() has already handed the
            // order's stock back, so returning without rolling back would leave the food and
            // its variation options credited for an edit that never happened. Every other
            // error path below rolls back; this one did not.
            DB::rollBack();

            return ['status_code' => 403, 'code' => 'cart', 'message' => translate('You_can_not_place_an_empty_order')];
        }

        // The editor's rows carry the offer but not the restaurant, and the whole order
        // belongs to one, so the restaurant is passed in.
        $bogo_frozen_prices = $this->bogoFrozenUnitPrices($carts, (int) $restaurant->id);

        foreach ($carts as $cart) {

            if ($cart['item_type'] === 'App\Models\ItemCampaign' || $cart['item_type'] === 'AppModelsItemCampaign') {
                $product = ItemCampaign::active()->find($cart['item_id']);
                $campaign_id = $cart['item_id'];
                $code = 'campaign';
            } else {
                $product = Food::active()->with('restaurant')->find($cart['item_id']);
                $food_id = $cart['item_id'];
                $code = 'food';
            }
            if (data_get($cart, 'new_item') == true) {
                $has_new_item = true;
            }

            if ($product->restaurant_id != $restaurant->id) {
                DB::rollBack();
                return ['status_code' => 403, 'code' => 'restaurant', 'message' => translate('messages.you_need_to_order_food_from_single_restaurant')];
            }

            if ($product) {
                if ($product->maximum_cart_quantity && ($cart['quantity'] > $product->maximum_cart_quantity)) {
                    DB::rollBack();
                    return ['status_code' => 403, 'code' => 'quantity', 'message' => $product?->name ?? $product?->title ?? $code . ' ' . translate('messages.has_reached_the_maximum_cart_quantity_limit')];
                }

                $addon_data = Helpers::calculate_addon_price(addons: AddOn::whereIn('id', $cart['add_on_ids'])->get(), add_on_qtys: $cart['add_on_qtys']);

                if ($code == 'food') {
                    // A cart row out of the database holds this as a JSON string; the order
                    // editor's rows come from the session and hold it as an array already.
                    // Accepting only the string threw the array away, so an edit handed the
                    // option's stock back and never took it again - every save drifted the
                    // count down by the order's quantity.
                    $variation_options = data_get($cart, 'variation_options');
                    $variation_options = is_string($variation_options)
                        ? (json_decode($variation_options, true) ?: [])
                        : (is_array($variation_options) ? $variation_options : []);
                    $addonAndVariationStock = Helpers::addonAndVariationStockCheck(product: $product, quantity: $cart['quantity'], add_on_qtys: $cart['add_on_qtys'], variation_options: $variation_options, add_on_ids: $cart['add_on_ids'], incrementCount: true);
                    if (data_get($addonAndVariationStock, 'out_of_stock') != null) {
                        DB::rollBack();
                        return ['status_code' => 403, 'code' => data_get($addonAndVariationStock, 'type') ?? 'food', 'message' => data_get($addonAndVariationStock, 'out_of_stock')];
                    }
                }

                $product_variations = json_decode($product->variations, true);
                $variations = [];
                if (count($product_variations)) {

                    if ($editedBy == 'vendor_app') {
                        $variation_data = Helpers::get_varient($product_variations, isset($cart['variations']) ? $cart['variations'] : [], $product->id);
                    } else {
                        $variation_data = Helpers::get_edit_varient($product_variations, isset($cart['variations']) ? $cart['variations'] : [], $product->id);
                    }

                    $price = $product['price'] + $variation_data['price'];
                    $variations = $variation_data['variations'];
                } else {
                    $price = $product['price'];
                }


                $product = Helpers::product_data_formatting(data: $product, multi_data: false, trans: false, local: app()->getLocale(), maxDiscount: false);

                // A bundle line survives an edit as a bundle line. Without this the rebuild
                // dropped bogo_group_id, re-priced the free item from the menu and applied
                // the food's own discount - so simply opening the editor and saving turned a
                // 362.00 bundle order into 573.20 and destroyed the offer.
                //
                // The buy lines come off the enrolment for the same reason: reading them from
                // the menu meant a food repriced after the order was placed changed what an
                // already-placed order cost, the moment anyone opened it and saved.
                $isBogo = ! empty($cart['bogo_group_id']);
                $isFree = $isBogo && ! empty($cart['is_free_item']);

                $bogo_unit_worth = null;

                if ($isBogo) {
                    [$price, , $bogo_unit_worth] = $this->bogoLinePrice($bogo_frozen_prices, $cart, $price);
                    // Already inside the frozen price.
                    $addon_data['total_add_on_price'] = 0;
                }

                $product_discount = $isBogo
                    ? ['discount_amount' => 0, 'discount_percentage' => 0]
                    : Helpers::food_discount_calculate($product, $price, $restaurant, false);

                $order_detail = [
                    'food_id' => $food_id ??  null,
                    'item_campaign_id' => $campaign_id ?? null,
                    'food_details' => json_encode($product),
                    'quantity' => $cart['quantity'],
                    'price' => round($price, config('round_up_to_digit')),
                    'category_id' => collect(is_string($product->category_ids) ? json_decode($product->category_ids, true) : $product->category_ids)->firstWhere('position', 1)['id'] ?? null,
                    'tax_amount' => 0,
                    'tax_status' => null,
                    'discount_type' => 'discount_on_product',
                    'discount_on_product_by' => 'vendor',
                    'discount_on_food' => $product_discount['discount_amount'],
                    'discount_percentage' => $product_discount['discount_percentage'],
                    'variation' => json_encode($variations),
                    'add_ons' => json_encode($addon_data['addons']),
                    'total_add_on_price' => $addon_data['total_add_on_price'],
                    'bogo_offer_id' => $cart['bogo_offer_id'] ?? null,
                    'bogo_group_id' => $cart['bogo_group_id'] ?? null,
                    'is_free_item' => $isFree ? 1 : 0,
                    // Rebuilt from the enrolment like the price beside it, so editing an order
                    // never drops what the free item was worth.
                    'bogo_free_value' => $isFree ? $bogo_unit_worth : null,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $order_details[] = $order_detail;
                $total_addon_price += $order_detail['total_add_on_price'];
                $product_price += $price * $order_detail['quantity'];
                $restaurant_discount_amount += $order_detail['discount_on_food'] * $order_detail['quantity'];
            } else {
                DB::rollBack();
                return ['status_code' => 404, 'code' => $code ?? null, 'message' => translate('messages.product_unavailable_warning')];
            }
        }

        // A bundle the edit adds or grows has to clear the same rules a new one does - the
        // offer still running, the restaurant still on it, the caps not spent. Only the extra
        // copies are judged, so an order placed while the offer ran can still be edited after
        // it ends, which is when support most often has to touch one.
        if ($bogoRefusal = $this->bogoEditRefusalReason($order, $order_details, $bundles_before, (int) $restaurant->id)) {
            DB::rollBack();

            return ['status_code' => 403, 'code' => 'bogo_offer', 'message' => $bogoRefusal];
        }

        $discount = $restaurant_discount_amount;
        $restaurantDiscount = Helpers::get_effective_restaurant_discount($restaurant);
        if (isset($restaurantDiscount)) {
            $applied = $this->applyStoreWideDiscount($order_details, $restaurantDiscount, $restaurant);

            $order_details = $applied['order_details'];
            $discount = $applied['amount'];
            $discount_on_product_by = $applied['bearer'];
        }

        $restaurant_discount_amount = $discount;

        $order->delivery_charge = $order->delivery_charge;
        $order->discount_on_product_by = $discount_on_product_by;

        if ($free_delivery_by) {
            $order->delivery_charge = 0;
        }

        if ($restaurant->free_delivery) {
            $order->delivery_charge = 0;
        }
        if (in_array($order->free_delivery_by, ['admin'])) {
            $order->delivery_charge = 0;
        }

        $coupon_discount_amount = $coupon ? CouponLogic::get_discount(coupon: $coupon, order_amount: $product_price + $total_addon_price - $restaurant_discount_amount) : 0;
        $order->coupon_discount_amount = round($coupon_discount_amount, config('round_up_to_digit'));
        $order->coupon_discount_title = $coupon ? $coupon->title : '';
        $order->coupon_code = $coupon_created_by ? $order->coupon_code : null;
        $order->coupon_created_by = $coupon_created_by;
        $total_price = $product_price + $total_addon_price - $restaurant_discount_amount - $coupon_discount_amount;

        // Re-apply the Pro Customer discount benefit against the recalculated total (mirrors placement).
        $pro_offer          = ['status' => false, 'benefit' => null];
        $proDiscount        = 0.0;
        $proDeliverySavings = 0.0;
        $proApply    = $this->applyProCustomerDiscount(
            $order->user_id ? (int) $order->user_id : null,
            $product_price + $total_addon_price,
            $total_price
        );
        $pro_offer   = $proApply['offer'];
        $proDiscount = $proApply['discount'];
        $total_price = $proApply['total_price'];

        if ($order->ref_bonus_amount > 0) {
            $user = User::find($order->user_id);
            $discount_data = Helpers::getCusromerFirstOrderDiscount(order_count: 0, user_creation_date: $user->created_at,  refby: $user->ref_by, price: $total_price);
            if (data_get($discount_data, 'is_valid') == true &&  data_get($discount_data, 'calculated_amount') > 0) {
                $total_price = $total_price - data_get($discount_data, 'calculated_amount');
                $order->ref_bonus_amount = data_get($discount_data, 'calculated_amount');
            }
        }

        $total_price = max($total_price, 0);
        $totalDiscount = $restaurant_discount_amount + $coupon_discount_amount +  $order->ref_bonus_amount;

        $additionalCharges = [];

        $settings = BusinessSetting::whereIn('key', [
            'additional_charge_status',
            'additional_charge',
            'extra_packaging_charge',
        ])->pluck('value', 'key');

        $additional_charge_status  = $settings['additional_charge_status'] ?? null;
        $additional_charge         = $settings['additional_charge'] ?? null;
        $extra_packaging_data  = $settings['extra_packaging_charge'] ?? 0;

        $order->dm_tips = $order->dm_tips;
        $order->additional_charge = $order->additional_charge;

        if ($additional_charge_status == 1) {
            $order->additional_charge = $additional_charge ?? 0;
        }

        $order->extra_packaging_amount =  ($extra_packaging_data == 1 && $restaurant?->restaurant_config?->is_extra_packaging_active == 1  && $order?->extra_packaging_amount > 0) ? $restaurant?->restaurant_config?->extra_packaging_amount : $order->extra_packaging_amount;

        if ($order->extra_packaging_amount > 0) {
            $additionalCharges['tax_on_packaging_charge'] =  $order->extra_packaging_amount;
        }


        $finalCalculatedTax =  Helpers::getFinalCalculatedTax($order_details, $additionalCharges, $totalDiscount, $total_price, $restaurant->id);
        $taxType =  data_get($finalCalculatedTax, 'taxType');
        $tax_amount = $finalCalculatedTax['tax_amount'];
        $tax_status = $finalCalculatedTax['tax_status'];
        $taxMap = $finalCalculatedTax['taxMap'];
        $orderTaxIds = data_get($finalCalculatedTax, 'taxData.orderTaxIds', []);

        $order->tax_status = $tax_status;
        $order->tax_type = $taxType;

        if ($restaurant->minimum_order > $product_price + $total_addon_price) {
            DB::rollBack();
            return ['status_code' => 404, 'code' => $code ?? null, 'message' => translate('messages.you_need_to_order_at_least', ['amount' => $restaurant->minimum_order . ' ' . Helpers::currency_code()])];
        }





        // The zone's own free delivery setup, which the admin also pays for.
        if (Helpers::zoneFreeDeliveryApplies($restaurant->zone_id, (float) $total_price)) {
            $order->delivery_charge = 0;
            $free_delivery_by = 'admin';
        }

        if ($restaurant->free_delivery) {
            $order->delivery_charge = 0;
            $free_delivery_by = 'vendor';
        }

        // Distance-based free delivery was removed in 9.3 (admin zone-wise free delivery
        // replaces it) and its controls are gone from both panels. Stale restaurant rows are no
        // longer honoured, so a system upgraded with the old value set stops giving free
        // delivery on short-distance orders.

        $order->free_delivery_by = $free_delivery_by;

        // Re-apply the Pro Customer delivery-fee benefit against the recalculated delivery charge,
        // held to the same floor an original placement is.
        $proDelivery            = $this->applyProCustomerDeliveryFee(
            $pro_offer,
            (float) $order->delivery_charge,
            (float) $total_price,
            $order->free_delivery_by,
            Helpers::minimumDeliveryCharge($restaurant)
        );
        $order->delivery_charge  = $proDelivery['delivery_charge'];
        $order->free_delivery_by = $proDelivery['free_delivery_by'];
        $proDeliverySavings      = $proDelivery['savings'];

        $order_amount = round($total_price + $tax_amount + $order->delivery_charge + $order->additional_charge + $order->extra_packaging_amount, config('round_up_to_digit'));
        $order->total_tax_amount = round($tax_amount, config('round_up_to_digit'));
        $order->order_amount = $order_amount + $order->dm_tips;
        $order->restaurant_discount_amount = round($restaurant_discount_amount, config('round_up_to_digit'));
        // Kept on the order so the discount stays attributable to its happy hour after
        // the window closes, and so OrderLogic can bill it to the restaurant in full. Stamped
        // only when the window actually discounted something: OrderLogic books the whole of
        // restaurant_discount_amount as a happy hour expense off the back of this, and that
        // total can also hold a campaign line's own discount, which the window did not give.
        $order->happy_hour_id = ($applied['store_wide_amount'] ?? 0) > 0
            ? Helpers::running_happy_hour_id($restaurant)
            : null;

        // What the restaurant gave away, re-totalled from the rebuilt lines. Placement sums
        // the same thing as it prices each line; an edit did not, so the column kept the
        // figure from placement while the bundles under it grew or shrank - and OrderLogic
        // books the restaurant's bogo expense off this column, so the wrong amount was
        // charged the moment anyone touched the order.
        $order->bogo_discount_amount = round(
            collect($order_details)->sum(fn ($line) => (float) ($line['bogo_free_value'] ?? 0) * (int) $line['quantity']),
            config('round_up_to_digit')
        );

        // $order->adjusment = 0;
        $order->edited = true;

        $order->save();

        // Recalculate the Pro Customer benefit audit row against the edited order.
        $order->orderProDiscount()->delete();
        if ($order->user_id && ($pro_offer['status'] ?? false)) {
            $amountSaved = match ($pro_offer['benefit']['type']) {
                'discount'     => (float) $proDiscount,
                'delivery_fee' => (float) ($proDeliverySavings ?? 0.0),
                default        => 0.0,
            };
            $this->recordOrderProDiscount(
                orderId: $order->id,
                userId: (int) $order->user_id,
                proOffer: $pro_offer,
                amountSaved: $amountSaved,
                originalDeliveryCharge: $order->original_delivery_charge,
            );
        }

        $taxMapCollection = collect($taxMap);
        foreach ($order_details as $key => $item) {
            $order_details[$key]['order_id'] = $order->id;


            if ($item['food_id']) {
                $item_id = $item['food_id'];
            } else {
                $item_id = $item['item_campaign_id'];
            }
            $index = $taxMapCollection->search(function ($tax) use ($item_id) {
                return $tax['product_id'] == $item_id;
            });
            if ($index !== false) {
                $matchedTax = $taxMapCollection->pull($index);
                $order_details[$key]['tax_status'] = $matchedTax['include'] == 1 ? 'included' : 'excluded';
                $order_details[$key]['tax_amount'] = $matchedTax['totalTaxamount'];
            }
        }
        $order->details()->delete();
        $order->orderTaxes()->delete();
        OrderDetail::insert($order_details);

        // Placement books a usage row per bundle and bumps the offer's running total; an edit
        // used to leave both untouched, so the caps were measured against what was ordered
        // first rather than what the order now holds.
        $this->syncBogoUsage($order, $order_details);

        if (count($orderTaxIds)) {
            \Modules\TaxModule\Services\CalculateTaxService::updateOrderTaxData(
                orderId: $order->id,
                orderTaxIds: $orderTaxIds,
            );
        }

        if ($editLogs && is_array($editLogs)) {
            $editLogs = array_values(array_unique($editLogs));
            if ($has_new_item == false  && in_array('add_new_item', $editLogs)) {
                $index = array_search('add_new_item', $editLogs);
                if ($index !== false) {
                    unset($editLogs[$index]);
                }
            }

            foreach ($editLogs as $log) {
                $this->makeEditOrderLogs($order->id, $log, $editedBy == 'vendor_app' ? 'vendor' : $editedBy);
            }
        }

        DB::commit();
        return  ['status_code' => 200, 'message' => translate('messages.order_has_been_updated_successfully')];
    }


    /**
     * Every rule that can reduce or zero an already-computed delivery charge.
     *
     * These used to live inline in place_order, which meant a quote could never
     * agree with the charge — checkoutSummary() ran the fee engine but none of
     * this. Both now call the same method, in the same order:
     *
     *   1. the zone's free delivery setup   (admin bears it)
     *   2. the restaurant's own free delivery (vendor bears it)
     *   3. the restaurant's free-delivery distance, self-delivery only
     *   4. the customer's Pro Customer delivery benefit
     *
     * A coupon's free delivery is settled earlier, before the fee engine runs,
     * and reaches here as $freeDeliveryBy.
     *
     * @param float $eligibleAmount the post-discount order total the amount-based
     *                              rules are measured against
     */
    public function applyDeliveryChargeOverrides(
        float $deliveryCharge,
        $restaurant,
        float $eligibleAmount,
        float $distance,
        ?string $freeDeliveryBy = null,
        array $proOffer = ['status' => false]
    ): array {
        if (Helpers::zoneFreeDeliveryApplies($restaurant->zone_id, $eligibleAmount)) {
            $deliveryCharge = 0;
            $freeDeliveryBy = 'admin';
        }

        if ($restaurant->free_delivery) {
            $deliveryCharge = 0;
            $freeDeliveryBy = 'vendor';
        }

        // Distance-based free delivery was removed in 9.3 (admin zone-wise free delivery
        // replaces it) and its controls are gone from both panels. Stale restaurant rows are no
        // longer honoured, so a system upgraded with the old value set stops giving free
        // delivery on short-distance orders.

        // Runs last so admin/vendor free delivery still wins when already free. The floor comes
        // from whichever engine priced this order, so a partial Pro discount cannot take the
        // charge below the minimum the engine itself was clamped to.
        $pro = $this->applyProCustomerDeliveryFee(
            $proOffer,
            (float) $deliveryCharge,
            $eligibleAmount,
            $freeDeliveryBy,
            Helpers::minimumDeliveryCharge($restaurant)
        );

        return [
            'delivery_charge'  => $pro['delivery_charge'],
            'free_delivery_by' => $pro['free_delivery_by'],
            'pro_savings'      => $pro['savings'],
        ];
    }

    public function getCalculatedTax($request)
    {
        $product_price = $request->order_amount ?? 0;
        $coupon = null;
        $ref_bonus_amount = 0;
        $total_addon_price = 0;
        $restaurant_discount_amount = 0;
        $coupon_discount_amount = 0;
        $order_details = [];


        $order = new Order();
        $order->user_id = $request->user ? $request->user->id : $request['guest_id'];
        $order->is_guest = $request->user ? 0 : 1;
        $order->restaurant_id = $request['restaurant_id'];



        $additionalCharges = [];
        $settings = BusinessSetting::whereIn('key', [
            'additional_charge_status',
            'additional_charge',
            'extra_packaging_charge',
        ])->pluck('value', 'key');


        $extra_packaging_data  = $settings['extra_packaging_charge'] ?? 0;

        $restaurant = Restaurant::with(['discount'])->where('id', $request->restaurant_id)->first();

        if ($request['coupon_code']) {
            $couponData =  $this->getCouponData($request);
            if (data_get($couponData, 'status_code') === 403) {

                return response()->json([
                    'errors' => [
                        ['code' => data_get($couponData, 'code'), 'message' => data_get($couponData, 'message')]
                    ]
                ], data_get($couponData, 'status_code'));
            } else {
                $coupon = data_get($couponData, 'coupon');
            }
        }
        $extra_packaging_amount =  ($extra_packaging_data == 1 && $restaurant?->restaurant_config?->is_extra_packaging_active == 1  && $request?->extra_packaging_amount > 0) ? $restaurant?->restaurant_config?->extra_packaging_amount : 0;

        if ($extra_packaging_amount > 0) {
            $additionalCharges['tax_on_packaging_charge'] =  $extra_packaging_amount;
        }

        $carts = Cart::where('user_id', $order->user_id)->where('is_guest', $order->is_guest)
            ->when(isset($request->is_buy_now) && $request->is_buy_now == 1 && $request->cart_id, function ($query) use ($request) {
                return $query->where('id', $request->cart_id);
            })
            ->whereHas('item', function ($query) use ($request) {
                $query->where('restaurant_id', $request['restaurant_id']);
            })
            ->get()->map(function ($data) {
                // add_on_ids and add_on_qtys are cast to array on the Cart model, so what
                // arrives here depends on how the row was written: an ordinary add stores the
                // value json_encoded and the cast hands back a string, while a bundle is
                // written straight through the query builder and the cast hands back a real
                // array. json_decode() on the array is a TypeError, which is why get-Tax and
                // checkout-summary only fell over once a cart held a bundle. Decode what is
                // still a string and leave the rest alone.
                $decode = fn ($value) => is_string($value) ? json_decode($value, true) : $value;

                $data->add_on_ids = $decode($data->add_on_ids);
                $data->add_on_qtys = $decode($data->add_on_qtys);
                $data->variation = $decode($data->variation);
                return $data;
            });

        if (isset($request->is_buy_now) && $request->is_buy_now == 1) {
            $carts = gettype($request['cart']) == 'array' ? $request['cart'] : json_decode($request['cart'], true);
        }

        // The quote has to fail for the same reason the order will. makeOrderDetails only ever
        // answers "this line does not resolve", which says nothing about a bundle that is intact
        // but can no longer be served - ended, left, sold out, out of allowance, out of hours.
        // place_order re-checks every bundle before it writes; the quote asks the same rule, so
        // the checkout screen and the Place Order button cannot disagree. Skipped for buy-now,
        // whose basket is the payload rather than the cart.
        if (! (isset($request->is_buy_now) && $request->is_buy_now == 1)) {
            foreach ($this->bogoOffersInCart($order->user_id, (int) $order->is_guest, (int) $order->restaurant_id) as $group) {
                $reason = $group['enrollment']
                    ? $this->bogoUnavailableReason(
                        $group['enrollment'],
                        $group['quantity'],
                        $order->user_id,
                        (int) $order->is_guest,
                        $request['contact_person_number'] ?? null,
                        null,
                        $request['order_type'] ?? null
                    )
                    : translate('messages.Offer_not_found');

                if ($reason) {
                    return response()->json([
                        'errors' => [['code' => 'bogo_offer', 'message' => $reason]]
                    ], 403);
                }
            }
        }

        $order_details = $this->makeOrderDetails($carts, $order, $restaurant);
        if (data_get($order_details, 'status_code') === 403) {

            return response()->json([
                'errors' => [
                    ['code' => data_get($order_details, 'code'), 'message' => data_get($order_details, 'message')]
                ]
            ], data_get($order_details, 'status_code'));
        }

        $total_addon_price = $order_details['total_addon_price'];
        $product_price = $order_details['product_price'];
        $restaurant_discount_amount = $order_details['restaurant_discount_amount'];
        $order_details = $order_details['order_details'];

        $coupon_discount_amount = $coupon ? CouponLogic::get_discount($coupon, $product_price + $total_addon_price - $restaurant_discount_amount) : 0;

        $total_price = $product_price + $total_addon_price - $restaurant_discount_amount - $coupon_discount_amount;


        if ($order->is_guest  == 0 && $order->user_id) {
            $user = User::withcount('orders')->find($order->user_id);
            $discount_data = Helpers::getCusromerFirstOrderDiscount(order_count: $user->orders_count, user_creation_date: $user->created_at,  refby: $user->ref_by, price: $total_price);
            if (data_get($discount_data, 'is_valid') == true &&  data_get($discount_data, 'calculated_amount') > 0) {
                $total_price = $total_price - data_get($discount_data, 'calculated_amount');
                $ref_bonus_amount = data_get($discount_data, 'calculated_amount');
            }
        }

        // Pro Customer benefits: resolve active offer and apply the discount benefit.
        //
        // The signed in customer's id, never $order->user_id: that field holds the guest id for
        // a guest, and guest ids and user ids are separate auto-increment sequences, so a guest
        // was quoted whatever pro benefit the customer with the same number happened to hold -
        // a discount place_order then refused, because it reads $request->user?->id here.
        $proApply    = $this->applyProCustomerDiscount(
            $order->is_guest ? null : $order->user_id,
            $product_price + $total_addon_price,
            $total_price
        );
        $pro_offer   = $proApply['offer'];
        $proDiscount = $proApply['discount'];
        $total_price = $proApply['total_price'];


        $totalDiscount = $restaurant_discount_amount  + $coupon_discount_amount +  $ref_bonus_amount + $proDiscount;
        $finalCalculatedTax =  Helpers::getFinalCalculatedTax($order_details, $additionalCharges, $totalDiscount, $total_price, $order->restaurant_id, false);

        $data = [
            'tax_amount' => $finalCalculatedTax['tax_amount'],
            'tax_status' => $finalCalculatedTax['tax_status'],
            'tax_included' => $finalCalculatedTax['tax_included'],
            // The post-discount total the free-delivery and Pro Customer rules are
            // measured against. checkoutSummary() needs the same figure place_order
            // uses, or an amount-based free delivery would be judged differently in
            // the quote than at placement.
            'total_price' => round($total_price, config('round_up_to_digit')),
            // What the Pro Customer discount benefit took off, as its own figure. It is already
            // inside total_price above, but a checkout cannot itemise a line it can only see
            // the effect of - the delivery benefit has been returned this way all along, and
            // the two benefits are alternatives to each other, so they answer alike.
            'pro_discount' => round((float) $proDiscount, config('round_up_to_digit')),
            // The first-order / referral discount, for the same reason: it is inside
            // total_price too, and a checkout that adopts total_price as its subtotal - as the
            // Builder storefront does - would otherwise show a total smaller than its own lines
            // add up to, with nothing to name the difference.
            'ref_bonus_amount' => round((float) $ref_bonus_amount, config('round_up_to_digit')),
        ];

        return response()->json($data, 200);
    }
    public function setPosCalculatedTax($restaurant, $restaurantData = false)
    {

        $sessionCart = session()->get('cart');
        $carts = collect($sessionCart)->filter(fn($item, $key) => is_numeric($key))->values()->all();

        $order_details = $this->makePosOrderDetails($carts, $restaurant);
        $total_addon_price = $order_details['total_addon_price'];
        $product_price = $order_details['product_price'];
        $restaurant_discount_amount = $order_details['restaurant_discount_amount'];
        $order_details = $order_details['order_details'];

        $totalDiscount = $restaurant_discount_amount;
        $price = $product_price + $total_addon_price - $totalDiscount ?? 0;

        // Pro Customer "discount" benefit applies the same way as the non-POS
        // path (see applyProCustomerDiscount usage above): reduce the taxable
        // price by the discount and fold the amount into totalDiscount so tax
        // is calculated on the post-discount base.
        $pos_customer_id = Session::get('customer_id');
        $proApply = $this->applyProCustomerDiscount(
            $pos_customer_id ? (int) $pos_customer_id : null,
            $product_price + $total_addon_price,
            $price
        );
        if ($proApply['discount'] > 0) {
            $totalDiscount += $proApply['discount'];
            $price = $proApply['total_price'];
        }

        $additionalCharges = [];
        $extra_packaging_data  =  Helpers::get_business_settings('extra_packaging_charge')   ?? 0;
        $extra_packaging_amount =  ($extra_packaging_data == 1 && $restaurant?->restaurant_config?->is_extra_packaging_active == 1  && $restaurant?->restaurant_config?->extra_packaging_status == 1) ? $restaurant?->restaurant_config?->extra_packaging_amount : 0;

        if ($extra_packaging_amount > 0) {
            $additionalCharges['tax_on_packaging_charge'] =  $extra_packaging_amount;
        }

        $finalCalculatedTax =  Helpers::getFinalCalculatedTax(
            $order_details,
            $additionalCharges,
            $totalDiscount,
            $price,
            $restaurant->id,
            $restaurantData
        );

        session()->put('tax_amount', $finalCalculatedTax['tax_amount']);
        session()->put('tax_included', $finalCalculatedTax['tax_included']);

        $data = [
            'tax_amount' => $finalCalculatedTax['tax_amount'],
            'tax_status' => $finalCalculatedTax['tax_status'],
            'tax_included' => $finalCalculatedTax['tax_included'],
        ];
        return $data;
    }


    private function getCouponData($request)
    {

        if ($request['coupon_code']) {
            $coupon = Coupon::active()->where(['code' => $request['coupon_code']])->first();

            if (!$coupon) {
                return [
                    'status_code' => 403,
                    'code' => 'coupon',
                    'message' => translate('messages.coupon_expire'),
                ];
            }

            $status = $request->is_guest
                ? CouponLogic::is_valid_for_guest($coupon, $request['restaurant_id'])
                : CouponLogic::is_valide($coupon, $request->user->id, $request['restaurant_id']);

            $validationError = match ($status) {
                407 => [
                    'status_code' => 403,
                    'code' => 'coupon',
                    'message' => translate('messages.coupon_expire'),
                ],
                408 => [
                    'status_code' => 403,
                    'code' => 'coupon',
                    'message' => translate('messages.You_are_not_eligible_for_this_coupon'),
                ],
                406 => [
                    'status_code' => 403,
                    'code' => 'coupon',
                    'message' => translate('messages.coupon_usage_limit_over'),
                ],
                404 => [
                    'status_code' => 403,
                    'code' => 'coupon',
                    'message' => translate('messages.not_found'),
                ],
                410 => [
                    'status_code' => 403,
                    'code' => 'coupon',
                    'message' => translate('messages.free_delivery_already_covered_by_pro'),
                ],
                default => null,
            };

            if ($validationError) {
                return $validationError;
            }

            $coupon_created_by = $coupon->created_by;

            if ($coupon->coupon_type === 'free_delivery') {
                $delivery_charge = 0;
                $free_delivery_by = $coupon_created_by;
                $coupon_created_by = null;
            }
        }

        return [
            'coupon' => $coupon ?? null,
            'coupon_created_by' => $coupon_created_by ?? null,
            'delivery_charge' => $delivery_charge ?? null,
            'free_delivery_by' => $free_delivery_by ?? null,
        ];
    }



    private function makeEditOrderLogs($order_id, $log, $edited_by)
    {
        $editLog = new OrderEditLog();
        $editLog->order_id = $order_id;
        $editLog->log = $log;
        $editLog->edited_by = $edited_by;
        $editLog->save();
        return true;
    }

    public function placePosOrder($cart, $request, $restaurant, $distance)
    {
        try {
            if (count($cart) == 0 || !data_get($cart,0) ) {
                return ['code' => 'cart', 'status_code' => 403, 'message' => translate('cart_is_empty')];
            }

            $total_addon_price = 0;
            $product_price = 0;
            $restaurant_discount_amount = 0;

            $order_details = [];
            $lastId = Order::max('id') ?? 99999;
            $order = new Order();
            $order->id = $lastId + 1;

            $customer_id = Session::get('customer_id');
            $user = null;

            if ($customer_id) {
                $user = User::where('id', $customer_id)->first();
                $address = CustomerAddress::where('user_id', $customer_id)->where('id', Session::get('address_id'))->first();

                $address = [
                    'contact_person_number' => $address?->contact_person_number ?? $user?->phone,
                    'contact_person_name' => $address?->contact_person_name ?? $user?->full_name,
                    'address_type' => $address?->address_type ?? 'delivery',
                    'address' => $address?->address,
                    'latitude' => $address?->latitude,
                    'longitude' => $address?->longitude,
                    'floor' => $address?->floor,
                    'road' => $address?->road,
                    'house' => $address?->house,
                ];
            }


            $order->payment_method = $request->type == 'cash' ?  'cash_on_delivery' : $request->type;

            if ($order->payment_method == 'wallet' && !$user) {
                return ['code' => 'wallet', 'status_code' => 403, 'message' => translate('messages.wallet_not_found')];
            }

            $order->payment_status = !$customer_id  || $request->type == 'wallet' ? 'paid' : 'unpaid';
            $order->order_status = !$customer_id || $request->type == 'wallet' ? 'delivered' : 'confirmed';
            $order->order_type = Session::get('order_type') ?? 'take_away';
            $order->delivered = $order->order_status ==  'delivered' ?  now() : null;


            $order->scheduled = 0;
            $order->is_pos = 1;
            $order->restaurant_id = $restaurant->id;
            $order->user_id = $customer_id;
            $order->zone_id = $restaurant->zone_id;
            // Recorded on the same terms as the customer API, so a POS order
            // priced by an area- or zip-wise rule stays explainable. POS holds the
            // operator's pick in the session rather than on the request, so read it
            // from there first - reading only the request stamped every POS order
            // null and priced them all at the rule's minimum.
            [$posAreaId, $posZipCodeId] = $this->posCoveragePick();
            $order->area_id = $posAreaId ?? request()->input('area_id');
            $order->zip_code_id = $posZipCodeId ?? request()->input('zip_code_id');
            $order->delivery_address = isset($customer_id) ? json_encode($address) : null;
            $order->checked = 1;
            $order->created_at = now();
            $order->schedule_at = now();
            $order->updated_at = now();
            $order->otp = rand(1000, 9999);




            $additionalCharges = [];
            $additional_charge_status = Helpers::get_business_settings('additional_charge_status');
            $additional_charge = Helpers::get_business_settings('additional_charge');

            if ($additional_charge_status == 1) {
                $order->additional_charge = $additional_charge ?? 0;
            } else {
                $order->additional_charge = 0;
            }

            $extra_packaging_data  =  Helpers::get_business_settings('extra_packaging_charge')   ?? 0;
            $extra_packaging_amount =  ($extra_packaging_data == 1 && $restaurant?->restaurant_config?->is_extra_packaging_active == 1  && $restaurant?->restaurant_config?->extra_packaging_status == 1) ? $restaurant?->restaurant_config?->extra_packaging_amount : 0;

            if ($extra_packaging_amount > 0) {
                $additionalCharges['tax_on_packaging_charge'] =  $extra_packaging_amount;
                $order->extra_packaging_amount = $extra_packaging_amount;
            } else {
                $order->extra_packaging_amount = 0;
            }

            $order_details = $this->makePosOrderDetails($cart, $restaurant);

            if (data_get($order_details, 'status_code') === 403) {
                return ['code' => data_get($order_details, 'code'), 'status_code' => data_get($order_details, 'status_code'), 'message' => data_get($order_details, 'message')];
            }

            $total_addon_price = $order_details['total_addon_price'];
            $product_price = $order_details['product_price'];
            $restaurant_discount_amount = $order_details['restaurant_discount_amount'];
            // Read before the variable is reassigned below.
            $store_wide_amount = $order_details['store_wide_amount'] ?? 0;
            $order_details = $order_details['order_details'];

            $total_price = $product_price + $total_addon_price - $restaurant_discount_amount;

            $proApply    = $this->applyProCustomerDiscount(
                $customer_id ? (int) $customer_id : null,
                $product_price + $total_addon_price,
                $total_price
            );
            $pro_offer   = $proApply['offer'];
            $proDiscount = $proApply['discount'];
            $total_price = $proApply['total_price'];

            if ($order->order_type == 'delivery') {
                // A scheduled order is surged for the slot it will be delivered in,
                // not for the moment the customer checks out.
                $calculateDeliveryCharge = $this->calculateDeliveryCharge(
                    distance_data: $distance,
                    restaurant_data: $restaurant,
                    totalProductPrice: $total_price,
                    applySurge: true,
                    scheduleAt: $order->schedule_at ?? null,
                    areaId: $order->area_id,
                    zipCodeId: $order->zip_code_id,
                );

                $order->distance = $calculateDeliveryCharge['distance'];
                $order->vehicle_id =  $calculateDeliveryCharge['vehicle_id'];
                // Rounded the way place_order rounds it, so the POS cart, the order row and
                // the customer API all read back one figure rather than three that differ in
                // the last place.
                $order->delivery_charge = round((float) $calculateDeliveryCharge['delivery_charge'], config('round_up_to_digit'));
                $order->original_delivery_charge = round((float) $calculateDeliveryCharge['original_delivery_charge'], config('round_up_to_digit'));
                $order->free_delivery_by = $calculateDeliveryCharge['free_delivery_by'];

                $proDelivery            = $this->applyProCustomerDeliveryFee(
                    $pro_offer,
                    (float) $order->delivery_charge,
                    (float) $total_price,
                    $order->free_delivery_by,
                    Helpers::minimumDeliveryCharge($restaurant)
                );
                $order->delivery_charge = $proDelivery['delivery_charge'];
                $order->free_delivery_by = $proDelivery['free_delivery_by'];
                $proDeliverySavings     = $proDelivery['savings'];

                // Resolved last, against the charge actually being paid, which is the order
                // place_order settles it in too: a delayed delivery's discount can never be
                // larger than the fee it discounts.
                $saver = $this->resolveSaverDeliveryOption(
                    $restaurant,
                    $order->zone_id,
                    $request->delivery_type,
                    (float) $order->delivery_charge
                );
                $order->delivery_type = $saver['delivery_type'];
                $order->delivery_type_charge = $saver['delivery_type_charge'];
            } elseif (in_array($order->order_type, ['dine_in', 'take_away'])) {
                $order->distance = 0;
                $order->vehicle_id = null;
                $order->delivery_charge = 0;
                $order->original_delivery_charge = 0;
                $order->delivery_type = null;
                $order->delivery_type_charge = 0;
                $proDeliverySavings = 0.0;
            }



            $totalDiscount = $restaurant_discount_amount;
            $finalCalculatedTax =  Helpers::getFinalCalculatedTax($order_details, $additionalCharges, $totalDiscount, $total_price, $restaurant->id);


            $tax_amount = $finalCalculatedTax['tax_amount'];
            $tax_status = $finalCalculatedTax['tax_status'];
            $taxMap = $finalCalculatedTax['taxMap'];
            $orderTaxIds = data_get($finalCalculatedTax, 'taxData.orderTaxIds', []);
            $taxType =  data_get($finalCalculatedTax, 'taxType');
            $order->tax_type = $taxType;
            $order->tax_status = $tax_status;


            $order->restaurant_discount_amount = $restaurant_discount_amount;
            // Stamped only when the window actually discounted something - see the note in
            // makeEditOrderDetails.
            $order->happy_hour_id = ($store_wide_amount ?? 0) > 0
                ? Helpers::running_happy_hour_id($restaurant)
                : null;
            $order->total_tax_amount = $tax_amount;

            // The saver option that was actually resolved and stored, not the one the browser
            // asked for. Reading the request meant a vendor POS - which posts no delivery_type
            // at all, merging it in from the session - added a delayed delivery's discount to
            // the bill instead of subtracting it.
            $order->order_amount = $total_price + $tax_amount + $order->delivery_charge + $order->additional_charge + $order->extra_packaging_amount;
            if($order->delivery_type == 'slightly_delay'){
                $order->order_amount -= $order->delivery_type_charge ?? 0;
            }else{
                $order->order_amount += $order->delivery_type_charge ?? 0;
            }
            $order->adjusment = $request?->amount ?? $order->order_amount;


            $max_cod_order_amount_value =  BusinessSetting::where('key', 'max_cod_order_amount')->first()?->value ?? 0;
            if ($max_cod_order_amount_value > 0 && $order->payment_method == 'cash_on_delivery' && $order->order_amount > $max_cod_order_amount_value) {
                return ['code' => 403, 'status_code' => 403, 'message' => translate('messages.You can not Order more then ') . $max_cod_order_amount_value . Helpers::currency_symbol() . ' ' . translate('messages.on COD order.')];
            }

            if ($order->payment_method == 'wallet' && $user && $user?->wallet_balance < $order->order_amount) {
                return ['code' => 'order_amount', 'status_code' => 403, 'message' => translate('insufficient_balance')];
            } elseif ($order->payment_method == 'wallet' && $user && $user?->wallet_balance >= $order->order_amount) {
                CustomerLogic::create_wallet_transaction(user_id: $order->user_id, amount: $order->order_amount, transaction_type: 'order_place', referance: $order->id);
            }

            $order->save();

            if ($customer_id && ($pro_offer['status'] ?? false)) {
                $amountSaved = match ($pro_offer['benefit']['type']) {
                    'discount'     => (float) $proDiscount,
                    'delivery_fee' => (float) ($proDeliverySavings ?? 0.0),
                    default        => 0.0,
                };
                $this->recordOrderProDiscount(
                    orderId: $order->id,
                    userId: (int) $customer_id,
                    proOffer: $pro_offer,
                    amountSaved: $amountSaved,
                    originalDeliveryCharge: $order->original_delivery_charge,
                );
                $this->getProCustomerOffer(userId: $customer_id, incrementCount: true);
            }

            $taxMapCollection = collect($taxMap);
            foreach ($order_details as $key => $item) {
                $order_details[$key]['order_id'] = $order->id;

                if ($item['food_id']) {
                    $item_id = $item['food_id'];
                } else {
                    $item_id = $item['item_campaign_id'];
                }
                $index = $taxMapCollection->search(function ($tax) use ($item_id) {
                    return $tax['product_id'] == $item_id;
                });
                if ($index !== false) {
                    $matchedTax = $taxMapCollection->pull($index);
                    $order_details[$key]['tax_status'] = $matchedTax['include'] == 1 ? 'included' : 'excluded';
                    $order_details[$key]['tax_amount'] = $matchedTax['totalTaxamount'];
                }
            }

            OrderDetail::insert($order_details);

            if (count($orderTaxIds)) {
                \Modules\TaxModule\Services\CalculateTaxService::updateOrderTaxData(
                    orderId: $order->id,
                    orderTaxIds: $orderTaxIds,
                );
            }


            Session::forget('cart');
            Session::forget('address');
            Session::forget('tax_amount');
            Session::forget('tax_included');
            Session::forget('delivery_charge');
            Session::forget('delivery_charge_display');
            Session::forget('delivery_charge_note');
            Session::forget('order_type');
            Session::forget('customer_id');
            Session::forget('address_id');
            Session::forget('pos_restaurant_id');
            Session::forget('cart_total_price');
            Session::forget('delivery_type');
            Session::forget('delivery_type_charge');
            Session::forget('pos_area_id');
            Session::forget('pos_zip_code_id');

            session(['last_order' => $order->id]);

            if ($restaurant->restaurant_model == 'subscription' && isset($rest_sub)) {
                if ($rest_sub->max_order != "unlimited" && $rest_sub->max_order > 0) {
                    $rest_sub->decrement('max_order', 1);
                }
            }

            if (in_array($order->order_type, ['dine_in'])) {
                $OrderReference = new OrderReference();
                $OrderReference->order_id = $order->id;
                $OrderReference->save();
            }

            return ['code' => 'success', 'order' => $order, 'status_code' => 200, 'message' => translate('messages.Order has been placed successfully.')];
        } catch (\Exception $exception) {
            info([$exception->getFile(), $exception->getLine(), $exception->getMessage()]);
            return ['code' => 'error', 'status_code' => 500, 'message' => $exception->getMessage()];
        }
    }
    /**
     * @param bool        $applySurge Whether the zone's surge price is added on top.
     *                                Defaults to true, and every caller that prices a
     *                                real order leaves it there - POS included, since
     *                                placePosOrder() has always surged what it wrote
     *                                and a cart preview that opted out quoted one fare
     *                                and billed another. Kept as a parameter for the
     *                                display-only estimators that must not surge.
     * @param string|null $scheduleAt When the order is scheduled for. Surge is
     *                                resolved against this, not the current time,
     *                                so a scheduled order is priced for its slot.
     * @param int|null    $areaId     The customer's pick, when the zone's active
     *                                delivery rule prices area-wise. Falls back to
     *                                the request input, so a checkout that posts it
     *                                needs no change here.
     * @param int|null    $zipCodeId  As above, for zip-code-wise rules.
     */
    public function calculateDeliveryCharge($distance_data, $restaurant_data, $totalProductPrice = 0, bool $applySurge = true, $scheduleAt = null, $areaId = null, $zipCodeId = null)
    {

        $free_delivery_by = null;

        // Free delivery is configured per zone under Delivery Management; the old
        // global business-settings version has been removed.
        if (Helpers::zoneFreeDeliveryApplies($restaurant_data->zone_id ?? ($restaurant_data->zone->id ?? null), (float) $totalProductPrice)) {
            $free_delivery_by = 'admin';
        }

        $is_self_delivery = (bool) $restaurant_data->sub_self_delivery;

        // The restaurant bears the delivery itself, so it is priced from the restaurant's own
        // rates whether or not it also runs its own riders. This used to sit inside the
        // self-delivery branch below, which meant a marketplace restaurant with free delivery
        // switched on was still billed the zone's fare in POS while the customer API - and
        // edit-order, and the Builder quote - all handed it over free.
        $vendor_free_delivery = (bool) $restaurant_data->free_delivery;

        if ($vendor_free_delivery) {
            $free_delivery_by = 'vendor';
        }

        if ($is_self_delivery || $vendor_free_delivery) {
            $per_km_shipping_charge = (float)$restaurant_data->per_km_shipping_charge;
            $minimum_shipping_charge = (float)$restaurant_data->minimum_shipping_charge;
            $maximum_shipping_charge = (float)$restaurant_data->maximum_shipping_charge;
            $increased = 0;
            $extra_charge = 0;
            $vehicle_id = null;
        } else {
            // Both halves of the zone's distance pricing have to be set for it to price
            // anything - a zone with only one of them configured is not priced by distance at
            // all, and the fare is the vehicle extra alone. claculate_original_delivery_fee()
            // has always read them under this guard; reading them unconditionally here made
            // a zone with a zero minimum quote a fare the API would not charge.
            $per_km_shipping_charge = 0;
            $minimum_shipping_charge = 0;
            $maximum_shipping_charge = 0;
            $increased = 0;

            if (($restaurant_data->zone->per_km_shipping_charge ?? 0) && ($restaurant_data->zone->minimum_shipping_charge ?? 0)) {
                $per_km_shipping_charge = $restaurant_data->zone->per_km_shipping_charge;
                $minimum_shipping_charge = $restaurant_data->zone->minimum_shipping_charge;
                $maximum_shipping_charge = $restaurant_data->zone->maximum_shipping_charge ?? 0;

                if ($restaurant_data->zone->increased_delivery_fee_status == 1) {
                    $increased = $restaurant_data->zone->increased_delivery_fee ?? 0;
                }
            }

            $data = Helpers::vehicle_extra_charge(distance_data: $distance_data);
            $vehicle_id = (isset($data) ? $data['vehicle_id']  : null);
            $extra_charge = (float) (isset($data) ? $data['extra_charge']  : 0);
        }
        // When the zone has an active delivery rule it decides the base charge
        // outright — its own per-km rate, minimum and maximum — and the zone's
        // distance pricing below is skipped. This is the same resolution the
        // customer API uses, so web, Builder and app quote one figure. A
        // self-delivery restaurant, and one giving the delivery away, price from
        // their own rates and are exempt.
        //
        // The pick is read from the request when the caller does not supply it,
        // so a checkout that posts area_id/zip_code_id is honoured automatically.
        $areaId = $areaId ?? request()->input('area_id');
        $zipCodeId = $zipCodeId ?? request()->input('zip_code_id');

        $rule_base = ($is_self_delivery || $vendor_free_delivery)
            ? null
            : Helpers::deliveryRuleBaseCharge(
                $restaurant_data->zone_id ?? ($restaurant_data->zone->id ?? null),
                $distance_data,
                $areaId,
                $zipCodeId
            );

        if ($rule_base !== null) {
            $original_delivery_charge = $rule_base;
            $delivery_amount = $rule_base + $extra_charge;
            // The rule is the single source of truth, so the zone's standing
            // "increased delivery fee" is not stacked on top of it.
            $increased = 0;
        } else {
            // The per-unit rate meets the distance expressed in the same unit. The delivery-rule
            // branch above already did this through deliveryRuleBaseCharge(), so without it here
            // a zone or self-delivery restaurant quoted one fare and charged another the moment
            // the unit was anything but kilometres.
            $chargeable_distance = Helpers::chargeableDistance($distance_data);
            $original_delivery_charge = ($chargeable_distance * $per_km_shipping_charge > $minimum_shipping_charge) ? $chargeable_distance * $per_km_shipping_charge : $minimum_shipping_charge;
            $delivery_amount = ($maximum_shipping_charge  >  $minimum_shipping_charge  && $original_delivery_charge + $extra_charge >  $maximum_shipping_charge  ?  $maximum_shipping_charge  : $original_delivery_charge + $extra_charge);
        }

        $with_increased_fee = ($delivery_amount *  $increased) / 100;
        $delivery_charge = $delivery_amount + $with_increased_fee;

        // Surge sits on top of the zone's standing "increased delivery fee" and
        // after the min/max clamp, so it applies to what the customer would
        // otherwise have paid. A restaurant that sets its own rates - because it
        // delivers itself, or because it is giving the delivery away - is not
        // covered by zone surge, which is the same gate the customer API applies.
        //
        // Deliberately NOT gated on an *admin* free delivery: the value below feeds
        // `original_delivery_charge`, which is the notional full fee used for
        // partner and deliveryman accounting when someone else covers delivery.
        // `delivery_charge` is still zeroed at the return, and the zone's own
        // `increased_delivery_fee` above already behaves exactly this way — so
        // gating surge here would make the two increases disagree.
        $surge = ['price' => 0, 'price_type' => 'amount'];
        // What the surge actually added, in money. A percent rule's `price` is a rate,
        // not an amount, so the difference either side of applySurgeToFee() is the only
        // figure that means the same thing for both rule types - and it is the figure a
        // customer-facing note has to quote.
        $surge_amount = 0.0;
        if ($applySurge && !$is_self_delivery && !$vendor_free_delivery) {
            $surge = $this->getSurgePriceValue(
                $restaurant_data->zone_id ?? ($restaurant_data->zone->id ?? 0),
                $scheduleAt ?: now()
            );
            $before_surge = (float) $delivery_charge;
            $delivery_charge = $this->applySurgeToFee($before_surge, $surge);
            $surge_amount = max(0.0, (float) $delivery_charge - $before_surge);
        }

        // info(['delivery_charge' => $delivery_charge,'distance_data'=> $distance_data , 'free_delivery_by' => $free_delivery_by,  'free_delivery_by' =>$free_delivery_by,'totalProductPrice'=>$totalProductPrice]);
        return ['delivery_charge' => $free_delivery_by ? 0 : $delivery_charge, 'free_delivery_by' => $free_delivery_by, 'original_delivery_charge' => $delivery_charge, 'vehicle_id' => $vehicle_id ?? null, 'vehicle_extra_charge' => (float) $extra_charge, 'distance' => $distance_data, 'surge' => $surge, 'surge_amount' => $free_delivery_by ? 0.0 : $surge_amount, 'surge_note' => $free_delivery_by ? null : $this->surgeCustomerNote($surge)];
    }

    /**
     * The saver delivery option in force on an order, and what it costs.
     *
     * The amounts come from the zone's own zone_delivery_options rows, never from
     * the caller: POS used to store `abs($request->delivery_type_charge)`, a figure
     * the browser computed and could be edited to anything before it was posted.
     * The rules are place_order's, verbatim, so an order taken over the counter and
     * one taken in the app price the same option identically:
     *
     *   - the zone has to have the option set turned on;
     *   - a self-delivery restaurant has no saver options at all;
     *   - `standard` is the absence of an option, so it is stored as null;
     *   - express costs the option's extra charge;
     *   - a delayed delivery discounts the option's reduce charge, but never more
     *     than the delivery charge actually being paid.
     *
     * @param float $deliveryCharge the charge after every free-delivery and Pro
     *                              Customer reduction has already been applied
     * @return array{delivery_type: string|null, delivery_type_charge: float}
     */
    public function resolveSaverDeliveryOption($restaurant, $zoneId, $deliveryType, float $deliveryCharge): array
    {
        $none = ['delivery_type' => null, 'delivery_type_charge' => 0.0];

        if (! $deliveryType || $restaurant?->sub_self_delivery == 1) {
            return $none;
        }

        if (! Zone::where('id', $zoneId)->value('additional_delivery_option_status')) {
            return $none;
        }

        $option = ZoneDeliveryOption::where('zone_id', $zoneId)
            ->when(
                is_numeric($deliveryType),
                fn ($query) => $query->where('id', $deliveryType),
                fn ($query) => $query->where('delivery_type', $deliveryType)
            )
            ->first();

        if (! $option || $option->delivery_type === 'standard') {
            return $none;
        }

        // Express is a service upgrade priced on its own, so it stands whatever the
        // delivery underneath costs - a free delivery does not make express free.
        if ($option->delivery_type === 'express') {
            return [
                'delivery_type' => 'express',
                'delivery_type_charge' => round(max(0.0, (float) $option->extra_charge), config('round_up_to_digit')),
            ];
        }

        // A delayed delivery is nothing but its discount, and the discount can never
        // exceed the fee being paid. Once that clamp leaves nothing - a free delivery,
        // or one the Pro Customer benefit already zeroed - there is no option left to
        // book: recording it anyway gave the customer the later delivery window
        // (get_saver_delivery_time and EtaLogic both shift on delivery_type alone,
        // never on the charge) in exchange for a discount of zero.
        $reduce = round(min((float) $option->reduce_charge, $deliveryCharge), config('round_up_to_digit'));

        if ($reduce <= 0) {
            return $none;
        }

        return [
            'delivery_type' => 'slightly_delay',
            'delivery_type_charge' => $reduce,
        ];
    }

    /**
     * The area the POS operator picked for an area-wise delivery rule, if any.
     *
     * POS has no request to read the pick out of the way a checkout does, so it
     * is held in the session between the picker and every recalculation.
     */
    public function posCoveragePick(): array
    {
        return [
            Session::get('pos_area_id') ?: null,
            Session::get('pos_zip_code_id') ?: null,
        ];
    }

    /**
     * Why this POS delivery order cannot be priced yet, or null when it can.
     *
     * The counter's half of OrderController::coverage_selection_check(): where the
     * zone's active rule reads a charge table, an order with no pick has no charge
     * to read and would silently fall back to the rule's minimum. place_order
     * refuses that for a customer, so the counter refuses it too.
     */
    public function posCoverageMissing($restaurant): ?string
    {
        if (Session::get('order_type') !== 'delivery' || ! $restaurant) {
            return null;
        }

        // Self-delivery prices from the restaurant's own rates and never reads the
        // charge table, so a pick could not change what is charged.
        if ((int) $restaurant->sub_self_delivery === 1) {
            return null;
        }

        $rule = Helpers::activeDeliveryRule($restaurant->zone_id);

        if (! $rule || ! $rule->usesChargeTable()) {
            return null;
        }

        $isArea = $rule->pricing_method === \App\Models\DeliveryRule::METHOD_AREA;

        if ($isArea ? Session::get('pos_area_id') : Session::get('pos_zip_code_id')) {
            return null;
        }

        return $isArea
            ? translate('messages.Please_select_your_area_to_calculate_the_delivery_charge.')
            : translate('messages.Please_select_your_ZIP_code_to_calculate_the_delivery_charge.');
    }

    /**
     * Reprice the POS cart's delivery and put the result back in the session.
     *
     * Every POS screen that can move the fare — picking a customer, an address,
     * an area, changing the cart — calls this, so there is one set of arguments
     * rather than ten copies that can drift. Surge is applied, because
     * placePosOrder() applies it too and the operator must be shown the fare the
     * order will actually be written with.
     */
    public function syncPosDeliveryCharge($restaurant_data): void
    {
        if (! $restaurant_data) {
            return;
        }

        [$areaId, $zipCodeId] = $this->posCoveragePick();

        $data = $this->calculateDeliveryCharge(
            distance_data: $this->getDistance($restaurant_data),
            restaurant_data: $restaurant_data,
            totalProductPrice: Session::get('cart_total_price') ?? 0,
            applySurge: true,
            scheduleAt: null,
            areaId: $areaId,
            zipCodeId: $zipCodeId,
        );

        // Rounded the way place_order rounds what it stores, so the cart, the
        // order row and the customer API all read back the same figure.
        $payable = round((float) $data['delivery_charge'], config('round_up_to_digit'));

        Session::put('delivery_charge', $payable);
        Session::put('delivery_charge_display', round((float) $data['original_delivery_charge'], config('round_up_to_digit')));
        Session::put('delivery_charge_note', $this->deliveryChargeNote($data, $payable));
    }

    /**
     * A short line explaining why this delivery fee is what it is, or null when
     * there is nothing to explain.
     *
     * Only the two things that quietly inflate the fee are named:
     *
     *   - the vehicle category's extra charge;
     *   - the zone's surge, quoted as the money it added, followed by the admin's
     *     own `customer_note` when they turned that note on.
     *
     * The surge is named whenever one applied. `customer_note_status` governs only
     * whether the admin's wording is shown beside the amount - it is not a switch on
     * the surge itself, and whoever is looking at the fee still has to be able to
     * account for one that is higher than the rate card.
     *
     * Shared by the counter and the storefront rather than written twice: both are
     * explaining the same fee from the same calculateDeliveryCharge() payload, and two
     * copies would drift the moment either gained a case.
     *
     * Null while the customer pays nothing: a line explaining a higher fee has
     * nothing to explain beside a zero, which is the same reason the fee engine
     * withholds `surge_note` on a free delivery.
     */
    public function deliveryChargeNote(array $data, float $payableCharge): ?string
    {
        // Nothing to explain beside a fee of nothing - the same reason the fee engine
        // withholds surge_note on a free delivery.
        if ($payableCharge <= 0) {
            return null;
        }

        $parts = [];

        // Label then amount, for both lines, rather than a sentence with the figure
        // interpolated into it. translate() registers a key it has never seen as the
        // humanised key itself - which carries no :amount to substitute - and it rewrites
        // the whole language file on every miss, so an entry that once held a sentence can
        // come back as its own name. A label survives that: the humanised fallback of
        // `vehicle_category_charge` is "Vehicle category charge", which is what the line
        // wanted to say anyway, and the amount is concatenated where nothing can drop it.
        $vehicleExtra = (float) ($data['vehicle_extra_charge'] ?? 0);

        if ($vehicleExtra > 0) {
            $parts[] = translate('messages.vehicle_category_charge').' '.Helpers::format_currency($vehicleExtra);
        }

        $surgeAmount = round((float) ($data['surge_amount'] ?? 0), config('round_up_to_digit'));

        if ($surgeAmount > 0) {
            $line = translate('messages.surge_price').' '.Helpers::format_currency($surgeAmount);

            // Verbatim, and only when the admin switched it on: it is their own wording,
            // written for this exact moment, so it is shown as written rather than folded
            // into a sentence of ours. SurgePriceLogic::customerNote() is the single gate
            // on customer_note_status and the fee engine passes its answer straight through,
            // so an empty string here means "amount only".
            $surgeNote = trim((string) ($data['surge_note'] ?? ''));

            if ($surgeNote !== '') {
                $line .= ' - '.$surgeNote;
            }

            $parts[] = $line;
        }

        // No vehicle charge and no surge means no tooltip, and the views drop the icon
        // with it. Separated by a middot rather than punctuation: the admin's note is
        // free text that often ends in a full stop of its own, and a second one beside it
        // reads as a typo.
        return $parts ? implode(' · ', $parts) : null;
    }

    /**
     * The distance POS prices this cart over.
     *
     * The measurement itself is Helpers::routeDistanceKm(), shared with the Builder
     * storefront so both quote one fare for one trip; only the "which address" part is
     * POS's own, since POS holds the pick in the session.
     */
    public function getDistance($restaurant_data)
    {
        $address = CustomerAddress::where('id', Session::get('address_id'))->first();

        if (!$address || !$restaurant_data || Session::get('order_type') != 'delivery') {
            return 0;
        }

        return Helpers::routeDistanceKm(
            $restaurant_data['latitude'],
            $restaurant_data['longitude'],
            $address->latitude,
            $address->longitude,
        );
    }

}
