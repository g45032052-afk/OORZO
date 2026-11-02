<?php
namespace App\Http\Controllers\Api\V1;

use App\Models\ZoneDeliveryOption;
use App\Traits\ManagesProCustomerSubscription;
use App\Traits\PlaceNewOrder;
use Carbon\Carbon;
use App\Models\BogoOfferRestaurant;
use App\Models\Cart;
use App\Models\Food;
use App\Models\User;
use App\Models\Zone;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Refund;
use App\Models\Review;
use App\Mail\PlaceOrder;
use App\Models\DMReview;
use App\Models\Restaurant;
use App\Mail\RefundRequest;
use App\Models\OrderDetail;
use App\Models\ItemCampaign;
use App\Models\RefundReason;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\OrderReference;
use App\Models\BusinessSetting;
use App\Models\CashBackHistory;
use App\Models\OfflinePayments;
use App\CentralLogics\EtaLogic;
use App\CentralLogics\OrderLogic;
use App\Models\OrderCancelReason;
use App\CentralLogics\CouponLogic;
use Illuminate\Support\Facades\DB;
use App\Mail\OrderVerificationMail;
use App\CentralLogics\CustomerLogic;
use App\CentralLogics\SurgePriceLogic;
use App\Models\DeliveryRule;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderListResource;
use App\Services\OrderListService;
use App\Models\OfflinePaymentMethod;
use App\Models\SubscriptionSchedule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use MatanYadaev\EloquentSpatial\Objects\Point;

class OrderController extends Controller
{
    use \App\Traits\HandlesBogoCart;

    use PlaceNewOrder , ManagesProCustomerSubscription;
    public function track_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'guest_id' => $request->user ? 'nullable' : 'required',
            'contact_number' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $user_id = $request->user ? $request->user->id : $request['guest_id'];

        if (isset($request['contact_number'])) {
            $contact = trim($request['contact_number']);
            if (substr($contact, 0, 1) !== '+') {
                $contact = '+' . $contact;
            }
            $request['contact_number'] = $contact;
        }

        $order = Order::with(['restaurant','restaurant.restaurant_sub', 'refund', 'delivery_man', 'delivery_man.rating','subscription','subscription.schedules','payments','OrderReference','orderProDiscount'])->withCount('details')->where(['id' => $request['order_id'], 'user_id' => $user_id])
            ->when(!$request->user, function ($query) use ($request) {
                return $query->whereJsonContains('delivery_address->contact_person_number', $request['contact_number']);
            })
            ->Notpos()->first();

        if($order){
            $order['restaurant'] = $order['restaurant'] ? Helpers::restaurant_data_formatting($order['restaurant']): $order['restaurant'];
            $order['delivery_address'] = $order['delivery_address']?json_decode($order['delivery_address'],true):$order['delivery_address'];
            $order['delivery_man'] = $order['delivery_man']?Helpers::deliverymen_data_formatting([$order['delivery_man']]):$order['delivery_man'];
            $order['offline_payment'] =  isset($order->offline_payments) ? Helpers::offline_payment_formater($order->offline_payments) : null;
            $order['is_reviewed'] =   $order->details_count >  Review::whereOrderId($request->order_id)->count() ? False :True ;
            $order['is_dm_reviewed'] =  $order?->delivery_man ? DMReview::whereOrderId($order->id)->exists()  : True ;

            // Whether this order carries a BOGO bundle and whether it carries a campaign item,
            // so a tracking screen can label it without pulling the lines back and working it
            // out itself. Independent of each other: an order can hold both, or neither, and a
            // campaign item sits alongside ordinary foods rather than replacing them - so each
            // answers "is there at least one", not "is the whole order this".
            //
            // One aggregate rather than two exists() calls, and the details are not loaded here
            // at all - only counted - so this stays a single cheap query.
            $lineKinds = DB::table('order_details')
                ->where('order_id', $order->id)
                ->selectRaw('MAX(bogo_group_id IS NOT NULL) as has_bogo, MAX(item_campaign_id IS NOT NULL) as has_campaign')
                ->first();

            $order['is_bogo'] = (bool) ($lineKinds->has_bogo ?? false);
            $order['is_item_campaign'] = (bool) ($lineKinds->has_campaign ?? false);

            foreach (Helpers::pro_discount_data($order) as $pro_key => $pro_value) {
                $order[$pro_key] = $pro_value;
            }


            if($order->subscription){
                $order->subscription['delivered_count']= (int) $order->subscription->logs()->whereOrderStatus('delivered')->count();
                $order->subscription['canceled_count']= (int) $order->subscription->logs()->whereOrderStatus('canceled')->count();
            }

            unset($order['offline_payments']);
            unset($order['orderProDiscount']);
            unset($order['details']);
        } else{
            return response()->json([
                'errors' => [
                    ['code' => 'order_not_found', 'message' => translate('messages.Order_not_found')]
                ]
            ], 404);
        }
        $order['saver_delivery_time'] = $this->get_saver_delivery_time($order);
        $order['eta'] = EtaLogic::forOrder($order);
        return response()->json($order, 200);
    }

    public function place_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_amount' => 'required',
            'payment_method'=>'required|in:cash_on_delivery,digital_payment,wallet,offline_payment',
            'order_type' => 'required|in:take_away,delivery,dine_in',
            'restaurant_id' => 'required',
            'distance' => 'required_if:order_type,delivery',
            'delivery_duration' => 'nullable|integer',
            'address' => 'required_if:order_type,delivery',
            'longitude' => 'required_if:order_type,delivery',
            'latitude' => 'required_if:order_type,delivery',
            'dm_tips' => 'nullable|numeric',
            'guest_id' => $request->user ? 'nullable' : 'required',
            'contact_person_name' => $request->user ? 'nullable' : 'required',
            'contact_person_number' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $order_validation_check =  $this->order_validation_check($request);

        if(data_get($order_validation_check,'status_code') === 403 ){
            return response()->json([
                'errors' => [
                    ['code' => data_get($order_validation_check,'code'), 'message' => data_get($order_validation_check,'message')]
                ]
            ], data_get($order_validation_check,'status_code'));
        } else{
            $restaurant = $order_validation_check;
        }

        $coverage_check = self::coverage_selection_check($request, $restaurant, requirePick: true);
        if ($coverage_check) {
            return response()->json([
                'errors' => [
                    ['code' => data_get($coverage_check, 'code'), 'message' => data_get($coverage_check, 'message')]
                ]
            ], data_get($coverage_check, 'status_code'));
        }

        $coupon = null;
        $coupon_created_by = null;
        $delivery_charge = null;
        $free_delivery_by = null;

        $schedule_at = Helpers::parse_schedule_at($request->schedule_at) ?? now();

        DB::beginTransaction();

        if ($request['coupon_code']) {
            $coupon_check = Helpers::coupon_check(coupon_code:$request['coupon_code'],restaurant_id:$restaurant->id,user_id:$request?->user?->id, is_guest:$request->user ? false : true);
            if(data_get($coupon_check,'code') === 'coupon' ){
                DB::rollBack();

                return response()->json([
                    'errors' => [
                        ['code' => data_get($coupon_check,'code'), 'message' => data_get($coupon_check,'message')]
                    ]
                ], data_get($coupon_check,'status_code'));
            } else{
                $coupon = data_get($coupon_check,'coupon');
                $coupon_created_by = data_get($coupon_check,'coupon_created_by');
                $delivery_charge = data_get($coupon_check,'delivery_charge');
                $free_delivery_by = data_get($coupon_check,'free_delivery_by');
            }
        }


        $claculate_original_delivery_fee= $this->claculate_original_delivery_fee(request:$request ,restaurant: $restaurant, delivery_charge:$delivery_charge, free_delivery_by:$free_delivery_by);

        $max_cod_order_amount_value = data_get($claculate_original_delivery_fee,'max_cod_order_amount_value');
        $vehicle_id = data_get($claculate_original_delivery_fee,'vehicle_id');
        $original_delivery_charge = data_get($claculate_original_delivery_fee,'original_delivery_charge');
        $delivery_charge = data_get($claculate_original_delivery_fee,'delivery_charge');

        $address = [
            'contact_person_name' => $request->contact_person_name ? $request->contact_person_name : ($request->user?$request->user->f_name . ' ' . $request->user->f_name:''),
            'contact_person_number' => $request->contact_person_number ?  $request->contact_person_number: $request->user?->phone,
            'contact_person_email' => $request->contact_person_email ? $request->contact_person_email : ($request->user?$request->user->email:''),
            'address_type' => $request->address_type?$request->address_type:'Delivery',
            'address' => $request->address,
            'floor' => $request->floor,
            'road' => $request->road,
            'house' => $request->house,
            'longitude' => (string)$request->longitude,
            'latitude' => (string)$request->latitude,
        ];

        $total_addon_price = 0;
        $product_price = 0;
        $restaurant_discount_amount = 0;
        $bogo_free_value = 0;
        $taxMap = [];
        $orderTaxIds = [];

        $order_details = [];


        $lastId = Order::max('id') ?? 99999;
        $order = new Order();
        $order->id = $lastId + 1;

        $order_status ='pending';
        if(($request->partial_payment && $request->payment_method != 'offline_payment') || $request->payment_method == 'wallet' ){
            $order_status ='confirmed';
        }

        $order->bring_change_amount = $request['bring_change_amount'] ?? 0 ;

        $order->distance = $request->distance ?? 0;
        $order->delivery_duration = is_numeric($request->delivery_duration) ? (int) $request->delivery_duration : null;
        $order->user_id = $request->user ? $request->user->id : $request['guest_id'];
        $order->order_amount = $request['order_amount'];
        $order->payment_status = ($request->partial_payment ? 'partially_paid' : ($request['payment_method'] == 'wallet' ? 'paid' : 'unpaid'));
        $order->order_status = $order_status;
        $order->coupon_code = $request['coupon_code'];
        $order->payment_method = $request->partial_payment? 'partial_payment' :$request->payment_method;
        $order->transaction_reference = null;
        $order->order_note = $request['order_note'];
        $order->order_type = $request['order_type'];
        $order->restaurant_id = $request['restaurant_id'];
        $order->delivery_charge = round($delivery_charge, config('round_up_to_digit'))??0;
        $order->original_delivery_charge = round($original_delivery_charge, config('round_up_to_digit'));
        $order->delivery_address = json_encode($address);
        $order->schedule_at = $schedule_at;
        $order->scheduled = $request->schedule_at && $request->order_type != 'dine_in' ?1:0;
        $order->is_guest = $request->user ? 0 : 1;
        $order->otp = rand(1000, 9999);
        $order->zone_id = $restaurant->zone_id;
        $order->area_id = $request->area_id;
        $order->zip_code_id = $request->zip_code_id;
        $order->vehicle_id = $vehicle_id;
        $order->pending = now();

        if ($order_status == 'confirmed') {
            $order->confirmed = now();
        }

        $order->created_at = now();
        $order->updated_at = now();

        $order->cutlery = $request->cutlery ? 1 : 0;
        $order->unavailable_item_note = $request->unavailable_item_note ?? null ;
        $order->delivery_instruction = $request->delivery_instruction ?? null ;
        // $order->tax_percentage = $restaurant->tax ;


        $carts = Cart::where('user_id', $order->user_id)->where('is_guest',$order->is_guest)
            ->when(isset($request->is_buy_now) && $request->is_buy_now == 1 && $request->cart_id, function ($query) use ($request) {
                return $query->where('id',$request->cart_id);
            })
            ->whereHas('item', function ($query) use ($request) {
                $query->where('restaurant_id', $request['restaurant_id']);
            })
            ->get()->map(function ($data) {
                $decode = fn ($value) => is_string($value) ? json_decode($value, true) : $value;

                $data->add_on_ids = $decode($data->add_on_ids);
                $data->add_on_qtys = $decode($data->add_on_qtys);
                $data->variations = $decode($data->variations);
                return $data;
            });

        if(isset($request->is_buy_now) && $request->is_buy_now == 1){
            $carts = $request['cart'];
        }

        if(count($carts) == 0 ){
            DB::rollBack();

            return response()->json([
                'errors' => [
                    ['code' => 'empty_order', 'message' => translate('you_can_not_place_empty_order')]
                ]
            ], 403);
        }

        // Asked before the line loop below, not after it.
        //
        // That loop reserves stock as it goes - addonAndVariationStockCheck() increments
        // sell_count for every line it passes - while this check asks the enrolment whether the
        // bundle can still be served, stock included. Run afterwards it was reading back the
        // reservation this very order had just made, so a bundle holding the last unit of one of
        // its items placed the hold and then failed its own re-check with
        // "<food> 's <option> Variation is out of stock !!!". Only bundles could hit it: nothing
        // re-checks an ordinary line, which is why the same item ordered on its own went through.
        // Asked first, both questions see the stock as it stood when Place Order was pressed.
        foreach ($this->bogoOffersInCart($order->user_id, $order->is_guest, $restaurant->id) as $group) {
            $reason = $group['enrollment']
                ? $this->bogoUnavailableReason($group['enrollment'], $group['quantity'], $order->user_id, (int) $order->is_guest, $request['contact_person_number'], $schedule_at, $request['order_type'])
                : translate('messages.Offer_not_found');

            if ($reason) {
                DB::rollBack();

                return response()->json(['errors' => [['code' => 'bogo_offer', 'message' => $reason]]], 403);
            }
        }

        $bogo_frozen_prices = $this->bogoFrozenUnitPrices($carts, (int) $request['restaurant_id']);

        foreach ($carts as $c) {

            if ($c['item_type'] === 'App\Models\ItemCampaign' || $c['item_type'] === 'AppModelsItemCampaign')  {
                $product = ItemCampaign::active()->find($c['item_id']);
                $campaign_id = $c['item_id'];
                $code = 'campaign';
            } else{
                $product = Food::active()->find($c['item_id']);
                $food_id = $c['item_id'];
                $code = 'food';
            }

            if ($product) {
                if ($product->restaurant_id != $request['restaurant_id']) {
                    DB::rollBack();

                    return response()->json([
                        'errors' => [
                            ['code' => 'restaurant', 'message' => translate('messages.you_need_to_order_food_from_single_restaurant')],
                        ]
                    ], 406);
                }

                if($product->maximum_cart_quantity && ($c['quantity'] > $product->maximum_cart_quantity)){
                    DB::rollBack();

                    return response()->json([
                        'errors' => [
                            ['code' => 'quantity', 'message' => ($product?->name ?? $product?->title ?? $code).' '.translate('messages.has_reached_the_maximum_cart_quantity_limit')]
                        ]
                    ], 406);
                }

                $addon_data = Helpers::calculate_addon_price(addons: \App\Models\AddOn::whereIn('id',$c['add_on_ids'])->get(), add_on_qtys: $c['add_on_qtys']);

                if($code == 'food'){
                    $variation_options = data_get($c, 'variation_options');
                    $variation_options = is_string($variation_options)
                        ? (json_decode($variation_options, true) ?: [])
                        : (is_array($variation_options) ? $variation_options : []);
                    $addonAndVariationStock= Helpers::addonAndVariationStockCheck(product:$product,quantity: $c['quantity'],add_on_qtys:$c['add_on_qtys'], variation_options:$variation_options,add_on_ids:$c['add_on_ids'],incrementCount: true );
                    if(data_get($addonAndVariationStock, 'out_of_stock') != null) {
                        DB::rollBack();

                        return response()->json([
                            'errors' => [
                                ['code' => data_get($addonAndVariationStock, 'type') ?? 'food', 'message' =>data_get($addonAndVariationStock, 'out_of_stock') ],
                            ]
                        ], 406);
                    }
                }

                $product_variations = json_decode($product->variations, true);
                $variations=[];
                if (count($product_variations)) {
                    $variation_data = Helpers::get_varient($product_variations, isset($c['variations']) ? $c['variations'] : [], $product->id);
                    $price = $product['price'] + $variation_data['price'];
                    $variations = $variation_data['variations'];
                } else {
                    $price = $product['price'];
                }

                $bogo_unit_worth = null;

                if (data_get($c, 'bogo_group_id')) {
                    [$price, $gave_away, $bogo_unit_worth] = $this->bogoLinePrice($bogo_frozen_prices, $c, $price);
                    $bogo_free_value += $gave_away;
                    $addon_data['total_add_on_price'] = 0;
                }

                $product->tax = $restaurant->tax;
                $product = Helpers::product_data_formatting(data:$product,multi_data: false,trans: false,local: app()->getLocale(),maxDiscount:false);

                $product_discount = data_get($c, 'bogo_group_id')
                    ? ['discount_amount' => 0, 'discount_percentage' => 0]
                    : Helpers::food_discount_calculate($product, $price, $restaurant, false);
                $or_d = [
                    'food_id' => $food_id ??  null,
                    'item_campaign_id' => $campaign_id ?? null,
                    'food_details' => json_encode($product),
                    'quantity' => $c['quantity'],
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
                    'bogo_offer_id' => $c['bogo_offer_id'] ?? null,
                    'bogo_group_id' => $c['bogo_group_id'] ?? null,
                    'is_free_item' => $c['is_free_item'] ?? 0,
                    'bogo_free_value' => empty($c['is_free_item']) ? null : $bogo_unit_worth,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $order_details[] = $or_d;
                $total_addon_price += $or_d['total_add_on_price'];
                $product_price += $price*$or_d['quantity'];
                $restaurant_discount_amount += $or_d['discount_on_food']*$or_d['quantity'];

                if (data_get($c, 'reel_id') &&  ($reelService = $this->getReelsApiService())) {
                $reelService->recordOrderSale(
                    reelId: (int) $c['reel_id'],
                    amount: (float) (round($price, config('round_up_to_digit')) * $c['quantity'] ?? 0),
                    userId: $request->user?->id,
                    guestId: $request->user ? null : $request['guest_id']
                );
            }

            } else {
                DB::rollBack();

                $missing = $code === 'campaign'
                    ? ItemCampaign::withoutGlobalScopes()->find($c['item_id'])
                    : Food::withoutGlobalScopes()->find($c['item_id']);

                return response()->json([
                    'errors' => [
                        [
                            'code' => $code ?? null,
                            'message' => $missing
                                ? $missing->name.' '.translate('messages.is_currently_unavailable')
                                : translate('messages.product_unavailable_warning'),
                        ]
                    ]
                ], 404);
            }
        }

        $order->discount_on_product_by = 'vendor';


        $discount = $restaurant_discount_amount;
        $restaurantDiscount = Helpers::get_effective_restaurant_discount($restaurant);
        if (isset($restaurantDiscount)) {
            $applied = $this->applyStoreWideDiscount($order_details, $restaurantDiscount, $restaurant);

            $order_details = $applied['order_details'];
            $discount = $applied['amount'];
            $order->discount_on_product_by = $applied['bearer'];
            $order->happy_hour_id = $applied['store_wide_amount'] > 0 ? $applied['happy_hour_id'] : null;
        }



        $restaurant_discount_amount= $discount;


        $coupon_discount_amount = $coupon ? CouponLogic::get_discount(coupon:$coupon, order_amount: $product_price + $total_addon_price - $restaurant_discount_amount) : 0;

        $total_price = $product_price + $total_addon_price - $restaurant_discount_amount - $coupon_discount_amount ;

        if($order->is_guest  == 0 && $order->user_id  && !($request->subscription_order && $request->subscription_quantity) ){
            $user= User::withcount('orders')->find($order->user_id);
            $discount_data= Helpers::getCusromerFirstOrderDiscount(order_count:$user->orders_count ,user_creation_date:$user->created_at,  refby:$user->ref_by, price: $total_price);
            if(data_get($discount_data,'is_valid') == true &&  data_get($discount_data,'calculated_amount') > 0){
                $total_price = $total_price - data_get($discount_data,'calculated_amount');
                $order->ref_bonus_amount = data_get($discount_data,'calculated_amount');
            }
        }

        $total_price = max($total_price, 0);

        $proApply    = $this->applyProCustomerDiscount(
            $request->user?->id,
            $product_price + $total_addon_price,
            $total_price
        );
        $pro_offer   = $proApply['offer'];
        $proDiscount = $proApply['discount'];
        $total_price = $proApply['total_price'];


        $totalDiscount = $restaurant_discount_amount + $coupon_discount_amount +  $order->ref_bonus_amount + $proDiscount;

        $additionalCharges = [];

        $settings = BusinessSetting::whereIn('key', [
            'dm_tips_status',
            'additional_charge_status',
            'additional_charge',
            'extra_packaging_charge',
        ])->pluck('value', 'key');

        $dm_tips_manage_status     = $settings['dm_tips_status'] ?? null;
        $additional_charge_status  = $settings['additional_charge_status'] ?? null;
        $additional_charge         = $settings['additional_charge'] ?? null;

        $extra_packaging_data  = $settings['extra_packaging_charge'] ?? 0;

        $order->dm_tips = 0;
        if ($dm_tips_manage_status == 1) {
            $order->dm_tips = $request->dm_tips ?? 0;
        }

        $order->additional_charge = 0;

        if ($additional_charge_status == 1) {
            $order->additional_charge = $additional_charge ?? 0;
        }

        $order->extra_packaging_amount =  ($extra_packaging_data == 1 && $restaurant?->restaurant_config?->is_extra_packaging_active == 1  && $request?->extra_packaging_amount > 0)?$restaurant?->restaurant_config?->extra_packaging_amount:0;

        if ($order->extra_packaging_amount > 0) {
            $additionalCharges['tax_on_packaging_charge'] =  $order->extra_packaging_amount;
        }

        $finalCalculatedTax =  Helpers::getFinalCalculatedTax($order_details, $additionalCharges, $totalDiscount, $total_price, $restaurant->id);
        $taxType=  data_get($finalCalculatedTax ,'taxType');
        $tax_amount = $finalCalculatedTax['tax_amount'];
        $tax_status = $finalCalculatedTax['tax_status'];
        $taxMap = $finalCalculatedTax['taxMap'];
        $orderTaxIds = data_get($finalCalculatedTax, 'taxData.orderTaxIds', []);

        $order->tax_status = $tax_status;
        $order->tax_type = $taxType;


        if($restaurant->minimum_order > $product_price + $total_addon_price )
        {
            DB::rollBack();

            return response()->json([
                'errors' => [
                    ['code' => 'order_amount', 'message' => translate('messages.you_need_to_order_at_least').' '. $restaurant->minimum_order.' '.Helpers::currency_code()],
                ]
            ], 406);
        }



        $proDelivery            = $this->applyDeliveryChargeOverrides(
            (float) $order->delivery_charge,
            $restaurant,
            (float) $total_price,
            (float) $request->distance,
            $free_delivery_by,
            $pro_offer
        );
        $order->delivery_charge = $proDelivery['delivery_charge'];
        $free_delivery_by       = $proDelivery['free_delivery_by'];
        $proDeliverySavings     = $proDelivery['pro_savings'];


        // Set outright rather than left to the column default: the branch below can now
        // decline to book an option at all, and the order_amount line reads both.
        $order->delivery_type = null;
        $order->delivery_type_charge = 0;

        $additional_delivery_option_status = $order?->zone?->additional_delivery_option_status;

        if($additional_delivery_option_status == 1 && isset($request->delivery_type) && $order->order_type == 'delivery' && $restaurant->sub_self_delivery != 1){
            $deliveryOption = ZoneDeliveryOption::where('zone_id', $order->zone_id)
                ->when(is_numeric($request->delivery_type),
                    fn ($query) => $query->where('id', $request->delivery_type),
                    fn ($query) => $query->where('delivery_type', $request->delivery_type))
                ->first();

            if($deliveryOption && $deliveryOption->delivery_type != 'standard'){
                if ($deliveryOption->delivery_type == 'express') {
                    // A service upgrade priced on its own: a free delivery underneath does
                    // not make express free.
                    $order->delivery_type = 'express';
                    $order->delivery_type_charge = $deliveryOption->extra_charge;
                } else {
                    // A delayed delivery is nothing but its discount, and the discount can
                    // never exceed the fee being paid. Once that clamp leaves nothing - a
                    // free delivery, or one the Pro Customer benefit already zeroed - there
                    // is no option left to book: recording it anyway handed the customer the
                    // later delivery window (get_saver_delivery_time and EtaLogic both shift
                    // on delivery_type alone, never on the charge) for a discount of zero.
                    $reduce = min(
                        (float) $deliveryOption->reduce_charge,
                        (float) $order->delivery_charge
                    );

                    if ($reduce > 0) {
                        $order->delivery_type = 'slightly_delay';
                        $order->delivery_type_charge = $reduce;
                    }
                }
            }
        }

        $order->coupon_created_by = $coupon_created_by;
        $order_amount = round($total_price + $tax_amount + $order->delivery_charge + $order->additional_charge + $order->extra_packaging_amount, config('round_up_to_digit'));
        $order->total_tax_amount= round($tax_amount, config('round_up_to_digit'));
        $order->order_amount = $order_amount + $order->dm_tips;
        // The option that was actually resolved and stored, not the value the client sent.
        // The lookup above accepts the option's id as readily as its name, and an id never
        // equals 'slightly_delay' - so posting one made a delayed delivery *add* its
        // discount instead of subtracting it.
        if($order->delivery_type == 'slightly_delay'){
            $order->order_amount -= $order->delivery_type_charge;
        }else{
            $order->order_amount += $order->delivery_type_charge;
        }
        if($request->payment_method == 'wallet' && $request->user->wallet_balance < $order_amount)
        {
            DB::rollBack();
            return response()->json([
                'errors' => [
                    ['code' => 'order_amount', 'message' => translate('messages.insufficient_balance')]
                ]
            ], 203);
        }
        if ($request->partial_payment && $request->user->wallet_balance > $order->order_amount) {
            DB::rollBack();
            return response()->json([
                'errors' => [
                    ['code' => 'partial_payment', 'message' => translate('messages.order_amount_must_be_greater_than_wallet_amount')]
                ]
            ], 203);
        }
        try {
            $order->coupon_discount_amount = round($coupon_discount_amount, config('round_up_to_digit'));
            $order->coupon_discount_title = $coupon ? $coupon->title : '';
            $order->free_delivery_by = $free_delivery_by;
            $order->restaurant_discount_amount= round($restaurant_discount_amount, config('round_up_to_digit'));
            $order->bogo_discount_amount = round($bogo_free_value, config('round_up_to_digit'));



            if( $max_cod_order_amount_value > 0 && $order->payment_method == 'cash_on_delivery' && $order->order_amount > $max_cod_order_amount_value){
                DB::rollBack();
                return response()->json([
                    'errors' => [
                        ['code' => 'order_amount', 'message' => translate('messages.You can not Order more then ').$max_cod_order_amount_value .Helpers::currency_symbol().' '. translate('messages.on COD order.')]
                    ]
                ], 203);
            }

            if($request->subscription_order && $request->subscription_quantity){
                $subscription = new Subscription();
                $subscription->start_at = $request->subscription_start_at;
                $subscription->status = 'active';
                $subscription->end_at = $request->subscription_end_at;
                $subscription->type = $request->subscription_type;
                $subscription->quantity = $request->subscription_quantity;
                $subscription->user_id = $request->user->id;
                $subscription->restaurant_id = $restaurant->id;
                $subscription->save();
                $order->subscription_id = $subscription->id;

                $days = array_map(function($day)use($subscription){
                    $day['subscription_id'] = $subscription->id;
                    $day['type'] = $subscription->type;
                    $day['created_at'] = now();
                    $day['updated_at'] = now();
                    return $day;
                },json_decode($request->subscription_days, true));
                SubscriptionSchedule::insert($days);
            }

            $order->save();

            if ($pro_offer['status']) {
                $amountSaved = match ($pro_offer['benefit']['type']) {
                    'discount'     => (float) $proDiscount,
                    'delivery_fee' => (float) $proDeliverySavings,
                    default        => 0.0,
                };

                $this->recordOrderProDiscount(
                    orderId: $order->id,
                    userId: (int) $order->user_id,
                    proOffer: $pro_offer,
                    amountSaved: $amountSaved,
                    originalDeliveryCharge: $order->original_delivery_charge,
                );

                $this->getProCustomerOffer(userId: $request->user?->id, incrementCount: true);
            }

            OrderLogic::create_subscription_log(id:$order->id);

            $taxMapCollection = collect($taxMap);
            foreach ($order_details as $key => $item) {
                $order_details[$key]['order_id'] = $order->id;

                if($restaurant_discount_amount <= 0 && ! Helpers::is_campaign_item($item)){
                    $order_details[$key]['discount_on_food'] = 0;
                }

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

            foreach ($this->bogoGroupsInCart($order->user_id, $order->is_guest, $restaurant->id) as $group) {
                \App\Models\BogoOfferUsage::create([
                    'bogo_offer_id' => $group['bogo_offer_id'],
                    'order_id' => $order->id,
                    'user_id' => $order->is_guest ? null : $order->user_id,
                    'is_guest' => $order->is_guest ? 1 : 0,
                    'phone' => $order->is_guest ? $request['contact_person_number'] : null,
                    'quantity' => $group['quantity'],
                ]);

                \App\Models\BogoOffer::where('id', $group['bogo_offer_id'])->increment('total_uses', $group['quantity']);
            }

            if (count($orderTaxIds)) {
                \Modules\TaxModule\Services\CalculateTaxService::updateOrderTaxData(
                    orderId: $order->id,
                    orderTaxIds: $orderTaxIds,
                );
            }

            if(!isset($request->is_buy_now) || (isset($request->is_buy_now) && $request->is_buy_now == 0 )){
                foreach ($carts as $cart) {
                    $cart->delete();
                }
            }

            $restaurant->increment('total_order');

            if($request->user){
                $customer = $request->user;
                $customer->zone_id = $restaurant->zone_id;
                $customer->save();

                Helpers::visitor_log(model: 'restaurant', user_id:$customer->id, visitor_log_id:$restaurant->id, order_count:true);
            }
            if($request->payment_method == 'wallet') CustomerLogic::create_wallet_transaction(user_id:$order->user_id, amount:$order->order_amount, transaction_type:'order_place', referance:$order->id);

            if ($request->partial_payment) {
                if ($request->user->wallet_balance<=0) {
                    DB::rollBack();
                    return response()->json([
                        'errors' => [
                            ['code' => 'order_amount', 'message' => translate('messages.insufficient_balance_for_partial_amount')]
                        ]
                    ], 203);
                }
                $p_amount = min($request->user->wallet_balance, $order->order_amount);
                $unpaid_amount = $order->order_amount - $p_amount;
                $order->partially_paid_amount = $p_amount;
                $order->save();
                CustomerLogic::create_wallet_transaction($order->user_id, $p_amount, 'partial_payment', $order->id);
                OrderLogic::create_order_payment(order_id:$order->id, amount:$p_amount, payment_status:'paid', payment_method:'wallet');
                OrderLogic::create_order_payment(order_id:$order->id, amount:$unpaid_amount, payment_status:'unpaid',payment_method:$request->payment_method);
            }


            if($order->is_guest  == 0 && $order->user_id && !($request->subscription_order && $request->subscription_quantity) ){
                $this->createCashBackHistory($order->order_amount, $order->user_id,$order->id);
            }
            if(in_array($request['order_type'],['dine_in']) ){
                $OrderReference = new OrderReference();
                $OrderReference->order_id = $order->id;
                $OrderReference->save();
            }

            DB::commit();
            //PlaceOrderMail

            return response()->json([
                'message' => translate('messages.order_placed_successfully'),
                'order_id' => $order->id,
                'total_ammount' => $total_price+$order->delivery_charge+$tax_amount
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            info($e->getMessage());
            return response()->json([$e->getMessage()], 403);
        }

        return response()->json([
            'errors' => [
                ['code' => 'order_time', 'message' => translate('messages.failed_to_place_order')]
            ]
        ], 403);
    }

    public function orderList(Request $request, OrderListService $listService)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
            'guest_id' => $request->user ? 'nullable' : 'required',
            'status' => 'nullable|in:all,running,history,' . implode(',', OrderListService::ORDER_STATUSES),
            'type' => 'nullable|in:regular,repeat',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $query = $listService->listQuery()
            ->where('user_id', $request->user ? $request->user->id : $request['guest_id'])
            ->where('is_guest', $request->user ? 0 : 1);

        $listService->applyFilters($query, $request);
        $listService->applySearch($query, $request);

        $paginator = $listService->paginate($query, $request);

        return response()->json($listService->respond($paginator, OrderListResource::class), 200);
    }

    public function deleteOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $order_ids = is_array($request['order_id']) ? $request['order_id'] : [$request['order_id']];

        $matching = Order::whereIn('id', $order_ids)
            ->where('user_id', $request->user ? $request->user->id : $request['guest_id'])
            ->where('is_guest', $request->user ? 0 : 1)
            ->whereIn('order_status', Order::TERMINAL_STATUSES)
            ->pluck('id')
            ->all();

        if (empty($matching)) {
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('messages.no_removable_order_found')],
                ],
            ], 404);
        }

        Order::whereIn('id', $matching)->update(['is_hidden' => 1]);

        return response()->json(['message' => translate('messages.order_removed_successfully')], 200);
    }

    public function get_order_list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $user_id = $request->user ? $request->user->id : $request['guest_id'];

        $paginator = Order::with(['restaurant', 'delivery_man.rating', 'details:id,order_id,item_campaign_id', 'orderProDiscount'])->withCount('details')->where(['user_id' => $user_id])->
        whereIn('order_status', ['delivered','canceled','refund_requested','refund_request_canceled','refunded','failed'])->Notpos()
            ->notHiddenForCustomer()
            ->whereNull('subscription_id')
            ->when(!isset($request->user) , function($query){
                $query->where('is_guest' , 1);
            })

            ->when(isset($request->user)  , function($query){
                $query->where('is_guest' , 0);
            })

            ->latest()->paginate($request['limit'], ['*'], 'page', $request['offset']);
        $orders = array_map(function ($data) {
            $data['delivery_address'] = $data['delivery_address']?json_decode($data['delivery_address']):$data['delivery_address'];
            $data['restaurant'] = $data['restaurant']?Helpers::restaurant_data_formatting($data['restaurant']):$data['restaurant'];
            $data['delivery_man'] = $data['delivery_man']?Helpers::deliverymen_data_formatting([$data['delivery_man']]):$data['delivery_man'];
            $data['is_reviewed'] =   $data['details_count'] >  Review::whereOrderId($data->id)->count() ? False :True ;
            $data['is_dm_reviewed'] = $data['delivery_man'] ? DMReview::whereOrderId($data->id)->exists()  : True ;
            $data['item_campaign_id'] = $data['details'] ? $data['details'][0]['item_campaign_id'] : True ;
            foreach (Helpers::pro_discount_data($data) as $pro_key => $pro_value) {
                $data[$pro_key] = $pro_value;
            }
            unset($data['details']);
            unset($data['orderProDiscount']);
            return $data;
        }, $paginator->items());
        $data = [
            'total_size' => $paginator->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'orders' => $orders
        ];
        return response()->json($data, 200);
    }
    public function get_order_subscription_list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $user_id = $request->user ? $request->user->id : $request['guest_id'];
        $paginator = Order::with(['restaurant', 'delivery_man.rating', 'orderProDiscount'])->withCount('details')->where(['user_id' => $user_id])
            ->Notpos()
            ->notHiddenForCustomer()
            ->whereNotNull('subscription_id')
            ->when(!isset($request->user) , function($query){
                $query->where('is_guest' , 1);
            })

            ->when(isset($request->user)  , function($query){
                $query->where('is_guest' , 0);
            })
            ->latest()->paginate($request['limit'], ['*'], 'page', $request['offset']);
        $orders = array_map(function ($data) {
            $data['delivery_address'] = $data['delivery_address']?json_decode($data['delivery_address']):$data['delivery_address'];
            $data['restaurant'] = $data['restaurant']?Helpers::restaurant_data_formatting($data['restaurant']):$data['restaurant'];
            $data['delivery_man'] = $data['delivery_man']?Helpers::deliverymen_data_formatting([$data['delivery_man']]):$data['delivery_man'];
            $data['is_reviewed'] =   $data['details_count'] >  Review::whereOrderId($data->id)->count() ? False :True ;
            $data['is_dm_reviewed'] =  $data['delivery_man'] ? DMReview::whereOrderId($data->id)->exists()  : True ;
            foreach (Helpers::pro_discount_data($data) as $pro_key => $pro_value) {
                $data[$pro_key] = $pro_value;
            }
            unset($data['orderProDiscount']);

            return $data;
        }, $paginator->items());
        $data = [
            'total_size' => $paginator->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'orders' => $orders
        ];
        return response()->json($data, 200);
    }


    public function get_running_orders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $user_id = $request->user ? $request->user->id : $request['guest_id'];

        $paginator = Order::with(['restaurant', 'delivery_man.rating', 'orderProDiscount'])->withCount('details')->where(['user_id' => $user_id])
            ->notHiddenForCustomer()
            ->whereNull('subscription_id')
            ->whereNotIn('order_status', ['delivered','canceled','refund_requested','refund_request_canceled','refunded','failed'])
            ->when(!isset($request->user) , function($query){
                $query->where('is_guest' , 1);
            })

            ->when(isset($request->user)  , function($query){
                $query->where('is_guest' , 0);
            })
            ->Notpos()->latest()->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $orders = array_map(function ($data) {
            $data['delivery_address'] = $data['delivery_address']?json_decode($data['delivery_address']):$data['delivery_address'];
            $data['restaurant'] = $data['restaurant']?Helpers::restaurant_data_formatting($data['restaurant']):$data['restaurant'];
            $data['delivery_man'] = $data['delivery_man']?Helpers::deliverymen_data_formatting([$data['delivery_man']]):$data['delivery_man'];
            foreach (Helpers::pro_discount_data($data) as $pro_key => $pro_value) {
                $data[$pro_key] = $pro_value;
            }
            unset($data['orderProDiscount']);
            return $data;
        }, $paginator->items());
        $data = [
            'total_size' => $paginator->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'orders' => $orders
        ];
        return response()->json($data, 200);
    }

    public function get_order_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $user_id = $request->user ? $request->user->id : $request['guest_id'];
        $order = Order::with('details','offline_payments','subscription.schedules', 'restaurant')->where('user_id', $user_id)

            ->when(!isset($request->user) , function($query){
                $query->where('is_guest' , 1);
            })

            ->when(isset($request->user)  , function($query){
                $query->where('is_guest' , 0);
            })
            ->find($request->order_id);
        $details = $order?->details;

        if ($details != null && $details->count() > 0) {
            $storage = [];
            foreach ($details as $item) {
                $item['add_ons'] = json_decode($item['add_ons']);
                $item['variation'] = json_decode($item['variation']);
                $item['food_details'] = json_decode($item['food_details'], true);
                $item['zone_id'] = (int) (isset($order->zone_id) ? $order->zone_id :  $order->restaurant->zone_id);
                array_push($storage, $item);
            }
            $data = $this->groupBogoOrderDetails(collect($storage), (int) $order->restaurant_id);
            $subscription_schedules =  $order?->subscription?->schedules;
            $offline_payment = isset($order->offline_payments) ? Helpers::offline_payment_formater($order->offline_payments) : null;

            return response()->json(['details'=>$data, 'subscription_schedules'=> $subscription_schedules, 'offline_payment' => $offline_payment, 'saver_delivery_time' => $this->get_saver_delivery_time($order)
            ], 200);
        }

        else {
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('messages.not_found')]
                ]
            ], 200);
        }
    }

    private function groupBogoOrderDetails($details, ?int $restaurantId = null): array
    {
        $digits = config('round_up_to_digit', 2);
        $rows = [];

        foreach ($details as $detail) {
            if ($detail->bogo_group_id) {
                continue;
            }

            $detail->bogo_details = null;
            $rows[] = $detail;
        }

        foreach ($details->filter(fn ($d) => $d->bogo_group_id)->groupBy('bogo_group_id') as $groupId => $group) {
            $first = $group->first();

            $enrollment = Helpers::bogo_enrollment_for($first->bogo_offer_id, $restaurantId);

            $quantity = Helpers::bogo_bundle_quantity($group, $restaurantId);

            $charged = (float) $group->sum(fn ($d) => $d->price * $d->quantity);

            $entry = clone ($group->firstWhere('is_free_item', 0) ?: $first);

            foreach (['id', 'food_id', 'item_campaign_id', 'food_details', 'variation',
                      'add_ons', 'is_free_item'] as $itemField) {
                $entry->{$itemField} = null;
            }

            $entry->price = round($quantity > 0 ? $charged / $quantity : $charged, $digits);
            $entry->quantity = $quantity;
            $entry->bogo_details = [
                'bogo_group_id' => $groupId,
                'bogo_offer_id' => $first->bogo_offer_id,
                'offer_title' => $enrollment?->bogo_offer?->title,
                'offer_slug' => $enrollment?->bogo_offer?->slug,
                'bundle_id' => $enrollment?->id,
                'quantity' => $quantity,
                'bundle_price' => round($quantity > 0 ? $charged / $quantity : $charged, $digits),
                'total_price' => round($charged, $digits),
                'buy_items' => $group->where('is_free_item', 0)->values()->all(),
                'free_items' => $group->where('is_free_item', 1)->values()->all(),
            ];

            $rows[] = $entry;
        }

        return $rows;
    }

    public function cancel_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $user_id = $request->user ? $request->user->id : $request['guest_id'];
        $order = Order::where(['user_id' => $user_id, 'id' => $request['order_id']])

            ->when(!isset($request->user) , function($query){
                $query->where('is_guest' , 1);
            })

            ->when(isset($request->user)  , function($query){
                $query->where('is_guest' , 0);
            })
            ->with('details')
            ->Notpos()->first();
        if(!$order){
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('messages.not_found')]
                ]
            ], 404);
        }
        else if ($order->order_status == 'pending' || $order->order_status == 'failed' || $order->order_status == 'canceled'  ) {

            if(!$request->reason && !$request->note){
                return response()->json([
                    'errors' => [
                        ['code' => 'order', 'message' => translate('messages.cancellation_reason_required')]
                    ]
                ], 403);
            }

            $order->order_status = 'canceled';
            $order->canceled = now();
            $order->cancellation_reason = $request->reason;
            $order->cancellation_note = $request->note;
            $order->canceled_by = 'customer';
            $order->save();

            Helpers::decreaseSellCount(order_details:$order->details);
            Helpers::send_order_notification($order);
            Helpers::increment_order_count($order->restaurant); //for subscription package order increase


            $wallet_status= BusinessSetting::where('key','wallet_status')->first()?->value;
            $refund_to_wallet= BusinessSetting::where('key', 'wallet_add_refund')->first()?->value;

            if($order?->payments && $order?->is_guest == 0){
                $refund_amount =$order->payments()->where('payment_status','paid')->sum('amount');
                if($wallet_status &&  $refund_to_wallet && $refund_amount > 0){
                    CustomerLogic::create_wallet_transaction(user_id:$order->user_id, amount:$refund_amount,transaction_type: 'order_refund',referance: $order->id);

                    return response()->json(['message' => translate('messages.order_canceled_successfully_and_refunded_to_wallet')], 200);
                } else {
                    return response()->json(['message' => translate('messages.order_canceled_successfully_and_for_refund_amount_contact_admin')], 200);
                }
            }


            return response()->json(['message' => translate('messages.order_canceled_successfully')], 200);
        }
        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => translate('messages.you_can_not_cancel_after_confirm')]
            ]
        ], 403);
    }

    public function refund_reasons(){
        $refund_reasons=RefundReason::where('status',1)->get();
        return response()->json([
            'refund_reasons' => $refund_reasons
        ], 200);
    }

    private function getReelsApiService(): ?object
    {
        $serviceClass = 'Modules\\ReelsModule\\Services\\ReelApiService';

        if (!addon_published_status('ReelsModule')) {
            return null;
        }

        if (!class_exists($serviceClass)) {
            return null;
        }

        return app($serviceClass);
    }

    public function refund_request(Request $request)
    {
        if(BusinessSetting::where(['key'=>'refund_active_status'])->first()->value == false){
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('You can not request for a refund')]
                ]
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'customer_reason' => 'required|string|max:254',
            'refund_method'=>'nullable|string|max:100',
            'customer_note'=>'nullable|string|max:65535',
            'image.*' => 'nullable|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $user_id = $request->user ? $request->user->id : $request['guest_id'];
        $order = Order::where(['user_id' => $user_id, 'id' => $request['order_id']])


            ->when(!isset($request->user) , function($query){
                $query->where('is_guest' , 1);
            })

            ->when(isset($request->user)  , function($query){
                $query->where('is_guest' , 0);
            })
            ->Notpos()->first();
        if(!$order){
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('messages.not_found')]
                ]
            ], 404);
        }

        else if ($order->order_status == 'delivered' && $order->payment_status == 'paid') {

            $id_img_names = [];
            if ($request->has('image')) {
                foreach ($request->file('image') as $img) {
                    $image_name = Helpers::upload(dir:'refund/', format:'png', image:$img);
                    array_push($id_img_names, $image_name);
                }
                $images = json_encode($id_img_names);
            } else {
                $images = json_encode([]);
                // return response()->json(['message' => 'no_image'], 200);
            }

            $refund_amount = round($order->order_amount - $order->delivery_charge- $order->dm_tips , config('round_up_to_digit'));

            $refund = new Refund();
            $refund->order_id = $order->id;
            $refund->user_id = $order->user_id;
            $refund->order_status= $order->order_status;
            $refund->refund_status= 'pending';
            $refund->refund_method= $request->refund_method ?? 'wallet';
            $refund->customer_reason= $request->customer_reason;
            $refund->customer_note= $request->customer_note;
            $refund->refund_amount= $refund_amount;
            $refund->image = $images;
            $refund->save();

            $order->order_status = 'refund_requested';
            $order->refund_requested = now();
            $order->save();
            // Helpers::send_order_notification($order);

            $admin = Admin::where('role_id',1)->first();
            try {
                $notification_status= Helpers::getNotificationStatusData('admin','order_refund_request');

                if($notification_status?->mail_status == 'active' && config('mail.status') && $admin['email'] && Helpers::get_mail_status('refund_request_mail_status_admin') == '1') {
                    Mail::to($admin?->getRawOriginal('email'))->send(new RefundRequest($order->id));
                }
            } catch (\Exception $ex) {
                info($ex->getMessage());
            }
            return response()->json(['message' => translate('messages.refund_request_placed_successfully')], 200);
        }

        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => translate('messages.you_can_not_request_for_refund_after_delivery')]
            ]
        ], 403);
    }

    public function update_payment_method(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $config=Helpers::get_business_settings('cash_on_delivery');
        if($config['status']==0)
        {
            return response()->json([
                'errors' => [
                    ['code' => 'cod', 'message' => translate('messages.Cash on delivery order not available at this time')]
                ]
            ], 403);
        }
        $user_id = $request->user ? $request->user->id : $request['guest_id'];
        $order = Order::where(['user_id' => $user_id, 'id' => $request['order_id']])->Notpos()->first();
        if ($order) {
            Order::where(['user_id' =>$user_id, 'id' => $request['order_id']])->update([
                'payment_method' => 'cash_on_delivery', 'order_status'=>'pending', 'pending'=> now()
            ]);
            $order_mail_status = Helpers::get_mail_status('place_order_mail_status_user');
            $order_verification_mail_status = Helpers::get_mail_status('order_verification_mail_status_user');
            $address = json_decode($order->delivery_address, true);
            try {

                Helpers::send_order_notification($order);
                $notification_status= Helpers::getNotificationStatusData('customer','customer_order_notification');

                if($notification_status?->mail_status == 'active' && $order->is_guest == 0 && config('mail.status') && $order_mail_status == '1'&& $order->customer) {
                    Mail::to($order->customer?->getRawOriginal('email'))->send(new PlaceOrder($order->id));
                }
                if($notification_status?->mail_status == 'active' && $order->is_guest == 1 && config('mail.status') && $order_mail_status == '1' && isset($address['contact_person_email'])) {
                    Mail::to($address['contact_person_email'])->send(new PlaceOrder($order->id));
                }

            } catch (\Exception $e) {
                info($e->getMessage());
            }
            return response()->json(['message' => translate('messages.payment_method_updated_successfully')], 200);
        }
        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => translate('messages.not_found')]
            ]
        ], 404);
    }

    public function digital_payment_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $userId = $request->user ? $request->user->id : $request['guest_id'];
        $order = Order::find($request['order_id']);
        $reason = OrderLogic::cod_to_digital_blocked_reason($order, $userId);

        return response()->json([
            'eligible' => $reason === null,
            'reason' => $reason,
            'amount_due' => $order && $reason === null ? OrderLogic::cod_amount_due($order) : 0,
        ], 200);
    }

    public function pay_digitally(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'payment_method' => 'required',
            'callback' => 'nullable',
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $userId = $request->user ? $request->user->id : $request['guest_id'];
        $order = Order::find($request['order_id']);

        if ($reason = OrderLogic::cod_to_digital_blocked_reason($order, $userId)) {
            return response()->json(['errors' => [['code' => 'order_payment', 'message' => $reason]]], 403);
        }

        $payment_url = route('cod-to-digital-payment').'?'.http_build_query(array_filter([
            'order_id' => $order->id,
            'customer_id' => $userId,
            'payment_method' => $request['payment_method'],
            'payment_platform' => $request['payment_platform'] ?? 'app',
            'callback' => $request['callback'],
        ], fn ($value) => $value !== null && $value !== ''));

        return response()->json([
            'payment_url' => $payment_url,
            'amount_due' => OrderLogic::cod_amount_due($order),
        ], 200);
    }

    public function cancellation_reason(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $limit = $request->query('limit', 1);
        $offset = $request->query('offset', 1);

        $reasons = OrderCancelReason::where('status', 1)->when($request->type,function($query) use($request){
            return $query->where('user_type',$request->type);
        })
            ->paginate($limit, ['*'], 'page', $offset);
        $data = [
            'total_size' => $reasons->total(),
            'limit' => $limit,
            'offset' => $offset,
            'reasons' => $reasons->items(),
        ];
        return response()->json($data, 200);
    }


    public function food_list(Request $request){

        $validator = Validator::make($request->all(), [
            'food_id' => 'required',
        ]);

        $food_ids= json_decode($request['food_id'], true);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $product = Food::active()->whereIn('id',$food_ids)->get();
        return response()->json(Helpers::product_data_formatting($product, true, false, app()->getLocale()), 200);
    }


    public function order_notification(Request $request,$order_id){
        $user_id = $request->user ? $request->user->id : $request['guest_id'];
        $order = Order::where('user_id', $user_id)->where('id',$order_id)->with(['restaurant','customer'])->first();
        if(!$order){
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('messages.not_found')]
                ]
            ], 404);
        }
        $payments = $order->payments()->where('payment_method','cash_on_delivery')->exists();
        $reload_home= false;
        $restaurant= $order?->restaurant;

        if($order && (!in_array($order->payment_method, ['digital_payment', 'partial_payment', 'offline_payment']) || $payments ) ){
             if ($restaurant?->is_valid_subscription == 1 && $restaurant?->restaurant_sub?->max_order != "unlimited" && $restaurant?->restaurant_sub?->max_order > 0) {
                    $restaurant?->restaurant_sub?->decrement('max_order', 1);
                    $reload_home=$restaurant?->restaurant_sub?->max_order <= 0 ?  true : false;
                }
                Helpers::send_order_notification($order);

                $address = json_decode($order['delivery_address'] , true);

                $email = $order->is_guest == 1 ? data_get($address,'contact_person_email')  : $order->customer?->email;
                $name = $order->is_guest == 1 ? data_get($address,'contact_person_name')  : $order->customer?->f_name.' '.$order->customer?->l_name;

                if (config('mail.status') && $email && $order->order_status == 'pending') {
                    try {
                        $notification_status= Helpers::getNotificationStatusData('customer','customer_order_notification');
                        if ($order->order_status == 'pending'  &&  Helpers::get_mail_status('place_order_mail_status_user') == '1' && $notification_status?->mail_status == 'active') {
                            Mail::to($email)->send(new PlaceOrder($order->id));
                        }
                        $notification_status=null;
                        $notification_status= Helpers::getNotificationStatusData('customer','customer_delivery_verification');
                        if (config('order_delivery_verification') == 1 && Helpers::get_mail_status('order_verification_mail_status_user') == '1'  && $notification_status?->mail_status == 'active') {
                            Mail::to($email)->send(new OrderVerificationMail($order->otp, $name));
                        }
                    }catch (\Exception $ex) {
                        info($ex);
                    }
                }
        }

        return response()->json([
            'reload_home' => $reload_home
        ], 200);
    }

    public function most_tips()
    {
        $data = Order::whereNot('dm_tips',0)->get()->mode('dm_tips');
        $data = ($data && (count($data)>0))?$data[0]:null;
        return response()->json([
            'most_tips_amount' => $data
        ], 200);
    }
    public function order_again(Request $request){
        Helpers::getZoneIds($request);

        $longitude= $request->header('longitude') ?? 0;
        $latitude= $request->header('latitude') ?? 0;
        $user_id = $request->user ? $request->user->id : $request['guest_id'];
        $zone_id= json_decode($request->header('zoneId'), true);
        $additional_data = [
            'filter_by' => $request->query('filter_by', []) ?? [],
            'cuisine_id' => $request->query('cuisine_id') ? (is_array($request->query('cuisine_id')) ? $request->query('cuisine_id') : json_decode($request->query('cuisine_id'), true)) : [],
            'order_type' => $request->query('order_type'),
        ];

        $query = Restaurant::withOpen($longitude,$latitude)->
        wherehas('orders' ,function($q) use($user_id){
            $q->where('user_id', $user_id)->where('is_guest' , 0)->latest();
        })

            ->withcount('foods')
            ->with(['foods_for_reorder'])
            ->Active()
            ->type($request->query('type', 'all'))
            ->searchKeyword($request->query('name') ?? $request->query('search'))
            ->applyFilters($additional_data)
            ->whereIn('zone_id', $zone_id);

        $query = \App\CentralLogics\PersonalizationService::applyStorePersonalization($query, $request->user?->id);

        $data = $query
            ->take(20)
            ->orderBy('open', 'desc')
            ->get()
            ->map(function ($data) {
                $data->foods = $data->foods_for_reorder->take(5);
                unset($data->foods_for_reorder);
                return $data;
            });
        return response()->json(Helpers::restaurant_data_formatting($data, true), 200);
    }

    public function offline_payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'method_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $config = Helpers::get_mail_status('offline_payment_status');
        if ($config == 0) {
            return response()->json([
                'errors' => [
                    ['code' => 'offline_payment_status', 'message' => translate('messages.offline_payment_for_the_order_not_available_at_this_time')]
                ]
            ], 403);
        }
        $order = Order::find($request->order_id);

        $offline_payment_info = [];
        $method = OfflinePaymentMethod::where(['id'=>$request->method_id,'status'=>1])->first();

        if(!$method || !$order ) {
            return response()->json([
                'errors' => [
                    ['code' => 'offline_payment_order_or_method', 'message' => translate('messages.offline_payment_order_or_method_not_found')]
                ]
            ], 403);
        }

        try{
            $fields = array_column($method->method_informations, 'customer_input');
            $values = $request->all();

            $offline_payment_info['method_id'] = $request->method_id;
            $offline_payment_info['method_name'] = $method->method_name;
            foreach ($fields as $field) {
                if(key_exists($field, $values)) {
                    $offline_payment_info[$field] = $values[$field];
                }
            }

            $OfflinePayments= OfflinePayments::firstOrNew(['order_id' => $order->id]);
            $OfflinePayments->payment_info =json_encode($offline_payment_info);
            $OfflinePayments->customer_note = $request->customer_note;
            $OfflinePayments->method_fields = json_encode($method?->method_fields);
            DB::beginTransaction();
            $OfflinePayments->save();
            $order->save();
            DB::commit();

            Helpers::sentAdminPanelNotification($order);

            return response()->json([
                'payment' => 'success'
            ], 200);


        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([ 'payment' => $e->getMessage()], 403);
        }
    }


    public function update_offline_payment_info(Request $request){
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $info= OfflinePayments::where('order_id' , $request->order_id)->first();
        $order= Order::find($request->order_id);

        if(!$info || !$order ) {
            return response()->json([
                'errors' => [
                    ['code' => 'offline_payment_order_or_method', 'message' => translate('messages.offline_payment_order_or_method_not_found')]
                ]
            ], 403);
        }
        $old_data =   json_decode($info->payment_info , true) ;
        $method_id= data_get($old_data,'method_id',null);
        $method = OfflinePaymentMethod::where('id', $method_id)->first();

        if(!$method ) {
            return response()->json([
                'errors' => [
                    ['code' => 'offline_payment_order_or_method', 'message' => translate('messages.offline_payment_method_not_found')]
                ]
            ], 403);
        }
        $offline_payment_info = [];
        $fields = array_column($method->method_informations, 'customer_input');
        $values = $request->all();
        $offline_payment_info['method_id'] =$method->id;
        $offline_payment_info['method_name'] = $method->method_name;
        foreach ($fields as $field) {
            if(key_exists($field, $values)) {
                $offline_payment_info[$field] = $values[$field];
            }
        }

        $info->customer_note = $request->customer_note;
        $info->payment_info =json_encode($offline_payment_info);
        $info->status = 'pending';
        $info->save();

        return response()->json([ 'payment' => 'Payment_Info_Updated_successfully'], 200);
    }

    public function getPendingReviews(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $foodIds=[];
        $itemIds=[];

        $orderDetails= OrderDetail::whereOrderId($request->order_id)->get(['id','food_id', 'item_campaign_id','food_details']);
        foreach($orderDetails as $detail){
            $foodIds[]=$detail->food_id;
            $itemIds[]=$detail->item_campaign_id;
        }
        $reviews =   Review::whereOrderId($request->order_id)->where(function($query) use($foodIds ,$itemIds) {
            $query->whereIn('food_id',$foodIds)->orWhereIn('item_campaign_id',$itemIds);
        })->get(['id','food_id','item_campaign_id'])->toArray();

        $reviewedFoodIds = array_column($reviews, 'food_id');
        $reviewedItemIds = array_column($reviews, 'item_campaign_id');
        $storage = [];
        foreach($orderDetails as $detail){
            if(!in_array($detail->food_id, $reviewedFoodIds) || !in_array($detail->item_campaign_id, $reviewedItemIds)){
                $detail['food_details'] = json_decode($detail['food_details'], true);
                $storage[] = $detail;
            }
        }
        return response()->json(['details'=>$storage], 200);
    }


    private function createCashBackHistory($order_amount, $user_id,$order_id){
        $cashBack =  Helpers::getCalculatedCashBackAmount(amount:$order_amount, customer_id:$user_id);
        if(data_get($cashBack,'calculated_amount') > 0){
            $CashBackHistory = new CashBackHistory();
            $CashBackHistory->user_id = $user_id;
            $CashBackHistory->order_id = $order_id;
            $CashBackHistory->calculated_amount = data_get($cashBack,'calculated_amount');
            $CashBackHistory->cashback_amount = data_get($cashBack,'cashback_amount');
            $CashBackHistory->cash_back_id = data_get($cashBack,'id');
            $CashBackHistory->cashback_type = data_get($cashBack,'cashback_type');
            $CashBackHistory->min_purchase = data_get($cashBack,'min_purchase');
            $CashBackHistory->max_discount = data_get($cashBack,'max_discount');
            $CashBackHistory->save();

            $CashBackHistory?->order()->update([
                'cash_back_id'=> $CashBackHistory->id
            ]);
        }
        return true;
    }
    /**
     * Every reason an order may not be placed, or the restaurant when there is none.
     *
     * @param bool $allowClosed keep going even though the restaurant is shut at the
     *                          order time. Quoting a price is not placing an order, so
     *                          checkout-summary asks for this: a customer looking at a
     *                          closed restaurant still gets to see what delivery would
     *                          cost, and is stopped at place_order, which never sets it.
     */
    public static function order_validation_check($request, bool $allowClosed = false){
        $schedule_at = Helpers::parse_schedule_at($request->schedule_at) ?? now();

        $settings_key = ['wallet_status','partial_payment_status','offline_payment_status','digital_payment','guest_checkout_status','home_delivery',
            'take_away','cash_on_delivery','instant_order','dine_in_order_option'];

        $settings =  array_column(BusinessSetting::whereIn('key', $settings_key)->get()->toArray(), 'value', 'key');

        $restaurant = Restaurant::with(['discount', 'restaurant_sub','restaurant_config'])->selectRaw('*, IF(((select count(*) from `restaurant_schedule` where `restaurants`.`id` = `restaurant_schedule`.`restaurant_id` and `restaurant_schedule`.`day` = '.$schedule_at->format('w').' and `restaurant_schedule`.`opening_time` < "'.$schedule_at->format('H:i:s').'" and `restaurant_schedule`.`closing_time` >"'.$schedule_at->format('H:i:s').'") > 0), true, false) as open')->where('id', $request->restaurant_id)->first();

        $response = match (true) {
            !$restaurant => [
                'code' => 'restaurant',
                'message' =>  'restaurant_not_found',
                'status' => 403
            ],
            in_array($restaurant->restaurant_model,['unsubscribed','none']) || ( in_array($restaurant->restaurant_model,['subscription']) && $restaurant?->restaurant_sub == null) || (in_array($restaurant->restaurant_model,['subscription']) && $restaurant?->restaurant_sub?->max_order != "unlimited" && $restaurant?->restaurant_sub?->max_order <= 0 ) => [
                'code' => 'restaurant',
                'message' =>  'Sorry_the_restaurant_is_unable_to_take_any_order',
                'status' => 403
            ],
            $request->schedule_at && $request->order_type != 'dine_in'  && !$restaurant->schedule_order => [
                'code' => 'schedule_at',
                'message' => 'schedule_order_not_available',
                'status' => 403
            ],
            !$allowClosed && $restaurant->open == false && !$request->subscription_order => [
                'code' => 'schedule_at',
                'message' => 'restaurant_is_closed_at_order_time',
                'status' => 403
            ],

            $request->payment_method == 'wallet' && ($settings['wallet_status'] ?? 0) != 1 => [
                'code' => 'payment_method',
                'message' => 'customer_wallet_disable_warning',
                'status' => 403
            ],
            $request->partial_payment && ($settings['partial_payment_status'] ?? 0) == 0 => [
                'code' => 'order_method',
                'message' => 'partial_payment_is_not_active',
                'status' => 403
            ],
            $request->payment_method == 'offline_payment' && ($settings['offline_payment_status'] ?? 0) == 0 => [
                'code' => 'offline_payment_status',
                'message' =>  'offline_payment_for_the_order_not_available_at_this_time',
                'status' => 403
            ],
            json_decode($settings['digital_payment'] ?? '', true)['status'] == 0 && $request->payment_method == 'digital_payment' => [
                'code' => 'digital_payment',
                'message' => 'digital_payment_for_the_order_not_available_at_this_time',
                'status' => 403
            ],
            $request->is_guest && ($settings['guest_checkout_status'] ?? 0)  == 0 => [
                'code' => 'is_guest',
                'message' => 'Guest_order_is_not_active',
                'status' => 403
            ],
            ($settings['home_delivery'] ?? 0) == 0 && $request->order_type == 'delivery' => [
                'code' => 'home_delivery',
                'message' => 'Home_delivery_is_disabled',
                'status' => 403
            ],
            ($settings['take_away'] ?? 0) == 0 && $request->order_type == 'take_away' => [
                'code' => 'take_away',
                'message' =>  'Take_away_is_disabled',
                'status' => 403
            ],
            $request->order_type != 'dine_in' && ((($settings['instant_order'] ?? 0) != 1 ||  $restaurant->restaurant_config?->instant_order != 1 ) && !$request->schedule_at && !$request->subscription_order)   => [
                'code' => 'instant_order',
                'message' => 'instant_order_is_not_available_for_now!',
                'status' => 403
            ],
            ($settings['dine_in_order_option'] ?? 0) == 0 && $request->order_type == 'dine_in' => [
                'code' => 'dine_in',
                'message' =>  'Dine_in_is_disabled',
                'status' => 403
            ],
            json_decode($settings['cash_on_delivery'] ?? '', true)['status'] != 1 && $request->payment_method == 'cash_on_delivery' => [
                'code' => 'order_time',
                'message' => 'Cash_on_delivery_is_not_active',
                'status' => 403
            ],

            $request->schedule_at && $schedule_at < now() => [
                'code' => 'order_time',
                'message' =>  'you_can_not_schedule_a_order_in_past',
                'status' => 403
            ],
            $request->schedule_at && $request->order_type == 'dine_in' && Carbon::now()->add($restaurant?->restaurant_config?->schedule_advance_dine_in_booking_duration, $restaurant?->restaurant_config?->schedule_advance_dine_in_booking_duration_time_format) > $schedule_at => [
                'code' => 'order_time',
                'message' => 'Dine_date_is_not_available',
                'status' => 403
            ],

            default => null
        };

        if ($response) {
            return ['code' => $response['code'],'message' => translate($response['message']),'status_code'=> $response['status']];
        }

        return $restaurant;
    }



    public static function claculate_original_delivery_fee($request , $restaurant, $delivery_charge,$free_delivery_by){

        if(in_array($request['order_type'],['take_away','dine_in']))
        {
            return ['max_cod_order_amount_value' => 0,'vehicle_id' => null,'original_delivery_charge' => 0 ,'delivery_charge' => $delivery_charge, 'base_delivery_charge' => (float) $delivery_charge, 'surge' => SurgePriceLogic::empty()];
        }

        $per_km_shipping_charge = 0;
        $minimum_shipping_charge = 0;
        $maximum_shipping_charge =  0;
        $max_cod_order_amount_value=  0;
        $increased=0;

        $data = Helpers::vehicle_extra_charge(distance_data:$request->distance);
        $extra_charges = (float) (isset($data) ? $data['extra_charge']  : 0);
        $vehicle_id= (isset($data) ? (int) $data['vehicle_id']  : null);

        if($request->latitude && $request->longitude){
            $zone = Zone::where('id', $restaurant->zone_id)->whereContains('coordinates', new Point($request->latitude, $request->longitude, POINT_SRID))->first();
            if(!$zone)
            {
                return ['code' => 'coordinates', 'message' => translate('messages.out_of_coverage') , 'status_code' => 403];
            }
            if( $zone->per_km_shipping_charge && $zone->minimum_shipping_charge ) {
                $per_km_shipping_charge = $zone->per_km_shipping_charge;
                $minimum_shipping_charge = $zone->minimum_shipping_charge;
                $maximum_shipping_charge = $zone->maximum_shipping_charge;
                $max_cod_order_amount_value= $zone->max_cod_order_amount;
                if($zone->increased_delivery_fee_status == 1){
                    $increased=$zone->increased_delivery_fee ?? 0;
                }
            }
        }

        $uses_zone_pricing = true;

        $is_self_delivery = (bool) $restaurant->sub_self_delivery;

        if(!in_array($request['order_type'],['take_away','dine_in'])  && !$restaurant->free_delivery &&  !isset($delivery_charge) && $is_self_delivery){

            $per_km_shipping_charge = $restaurant->per_km_shipping_charge;
            $minimum_shipping_charge = $restaurant->minimum_shipping_charge;
            $maximum_shipping_charge = $restaurant->maximum_shipping_charge;
            $extra_charges= 0;
            $vehicle_id=null;
            $increased=0;
            $uses_zone_pricing = false;
        }

        if($restaurant->free_delivery || $free_delivery_by == 'vendor' ){
            $per_km_shipping_charge = $restaurant->per_km_shipping_charge;
            $minimum_shipping_charge = $restaurant->minimum_shipping_charge;
            $maximum_shipping_charge = $restaurant->maximum_shipping_charge;
            $extra_charges= 0;
            $vehicle_id=null;
            $increased=0;
            $uses_zone_pricing = false;
        }

        $rule_base = $uses_zone_pricing && isset($zone)
            ? Helpers::deliveryRuleBaseCharge($zone->id, $request->distance, $request->area_id, $request->zip_code_id)
            : null;

        if ($rule_base !== null) {
            $original_delivery_charge = $rule_base + $extra_charges;

            if (!isset($delivery_charge)) {
                $delivery_charge = $rule_base + $extra_charges;
            }

            $increased = 0;
        } else {

        // Same rule as every other rate site: the per-unit rate meets the distance in that same
        // unit. deliveryRuleBaseCharge() above already converts internally, so only this branch
        // needed it.
        $chargeable_distance = Helpers::chargeableDistance($request->distance);

        $original_delivery_charge = ($chargeable_distance * $per_km_shipping_charge > $minimum_shipping_charge) ? $chargeable_distance * $per_km_shipping_charge + $extra_charges  : $minimum_shipping_charge + $extra_charges;

        if ($maximum_shipping_charge  > $minimum_shipping_charge  && $original_delivery_charge >  $maximum_shipping_charge ){
            $original_delivery_charge = $maximum_shipping_charge;
        }


        if(!isset($delivery_charge)){
            $delivery_charge = ($chargeable_distance * $per_km_shipping_charge > $minimum_shipping_charge) ? $chargeable_distance * $per_km_shipping_charge : $minimum_shipping_charge;
            if ( $maximum_shipping_charge  > $minimum_shipping_charge  && $delivery_charge + $extra_charges >  $maximum_shipping_charge ){
                $delivery_charge =$maximum_shipping_charge;
            }
            else{
                $delivery_charge =$extra_charges + $delivery_charge;
            }
        }

        }
        if($increased > 0 ){
            if($delivery_charge > 0){
                $increased_fee = ($delivery_charge * $increased) / 100;
                $delivery_charge = $delivery_charge + $increased_fee;
            }
            if($original_delivery_charge > 0){
                $increased_fee = ($original_delivery_charge * $increased) / 100;
                $original_delivery_charge = $original_delivery_charge + $increased_fee;
            }
        }

        $base_delivery_charge = (float) $delivery_charge;
        $surge = SurgePriceLogic::empty();

        if (isset($zone) && !$is_self_delivery && !$restaurant->free_delivery && $free_delivery_by !== 'vendor') {
            $surge = SurgePriceLogic::resolve(
                $zone->id,
                Helpers::parse_schedule_at($request->schedule_at) ?? now()
            );
            $delivery_charge = SurgePriceLogic::applyToFee((float) $delivery_charge, $surge);
            $original_delivery_charge = SurgePriceLogic::applyToFee((float) $original_delivery_charge, $surge);
        }

        return ['max_cod_order_amount_value' => $max_cod_order_amount_value,'vehicle_id' => $vehicle_id,'original_delivery_charge' => $original_delivery_charge,'delivery_charge' => $delivery_charge, 'base_delivery_charge' => $base_delivery_charge, 'surge' => $surge];
    }

    public function check_restaurant_validation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_type' => 'required|in:take_away,delivery,dine_in',
            'restaurant_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $schedule_at = Helpers::parse_schedule_at($request->schedule_at) ?? now();

        $settings_key = ['instant_order'];

        $settings =  array_column(BusinessSetting::whereIn('key', $settings_key)->get()->toArray(), 'value', 'key');

        $restaurant = Restaurant::with(['discount', 'restaurant_sub','restaurant_config'])->selectRaw('*, IF(((select count(*) from `restaurant_schedule` where `restaurants`.`id` = `restaurant_schedule`.`restaurant_id` and `restaurant_schedule`.`day` = '.$schedule_at->format('w').' and `restaurant_schedule`.`opening_time` < "'.$schedule_at->format('H:i:s').'" and `restaurant_schedule`.`closing_time` >"'.$schedule_at->format('H:i:s').'") > 0), true, false) as open')->where('id', $request->restaurant_id)->first();

        $response = match (true) {
            !$restaurant => [
                'code' => 'restaurant',
                'message' =>  'restaurant_not_found',
                'status' => 403
            ],
            in_array($restaurant->restaurant_model,['unsubscribed','none']) || ( in_array($restaurant->restaurant_model,['subscription']) && $restaurant?->restaurant_sub == null) || (in_array($restaurant->restaurant_model,['subscription']) && $restaurant?->restaurant_sub?->max_order != "unlimited" && $restaurant?->restaurant_sub?->max_order <= 0 ) => [
                'code' => 'restaurant',
                'message' =>  'Sorry_the_restaurant_is_unable_to_take_any_order',
                'status' => 403
            ],

            $request->schedule_at && $request->order_type != 'dine_in'  && !$restaurant->schedule_order => [
                'code' => 'schedule_at',
                'message' => 'schedule_order_not_available',
                'status' => 403
            ],

            $restaurant->open == false && !$request->subscription_order => [
                'code' => 'schedule_at',
                'message' => 'restaurant_is_closed_at_order_time',
                'status' => 403
            ],

            $request->order_type != 'dine_in' && ((($settings['instant_order'] ?? 0) != 1 ||  $restaurant->restaurant_config?->instant_order != 1 ) && !$request->schedule_at && !$request->subscription_order)   => [
                'code' => 'instant_order',
                'message' => 'instant_order_is_not_available_for_now!',
                'status' => 403
            ],

            $request->schedule_at && $schedule_at < now() => [
                'code' => 'order_time',
                'message' =>  'you_can_not_schedule_a_order_in_past',
                'status' => 403
            ],
            $request->schedule_at && $request->order_type == 'dine_in' && Carbon::now()->add($restaurant?->restaurant_config?->schedule_advance_dine_in_booking_duration, $restaurant?->restaurant_config?->schedule_advance_dine_in_booking_duration_time_format) > $schedule_at => [
                'code' => 'order_time',
                'message' => 'Dine_date_is_not_available',
                'status' => 403
            ],

            default => null
        };

        if ($response) {
            return response()->json([
                'errors' => [
                    ['code' => $response['code'], 'message' => translate($response['message'])]
                ]
            ], $response['status']);
        }

        return response()->json([], 200);
    }

    public function getTaxFromCart(Request $request)
    {
        return $this->getCalculatedTax($request);
    }

    public function checkoutSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required',
            'order_amount' => 'required|numeric',
            'order_type' => 'required|in:take_away,dine_in,delivery',
            'distance' => 'required_if:order_type,delivery',
            'latitude' => 'required_if:order_type,delivery',
            'longitude' => 'required_if:order_type,delivery',
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $taxResponse = $this->getCalculatedTax($request);
        if ($taxResponse->getStatusCode() !== 200) {
            return $taxResponse;
        }

        // A quote, not an order. The restaurant being shut - or switched off - must not
        // cost the customer the delivery figure, so the summary is still built and
        // place_order stays the one place that refuses.
        $restaurant = self::order_validation_check($request, allowClosed: true);
        if (data_get($restaurant, 'status_code') === 403) {
            return response()->json([
                'errors' => [
                    ['code' => data_get($restaurant, 'code'), 'message' => data_get($restaurant, 'message')]
                ]
            ], 403);
        }

        $coverage_check = self::coverage_selection_check($request, $restaurant);

        if ($coverage_check) {
            return response()->json([
                'errors' => [
                    ['code' => data_get($coverage_check, 'code'), 'message' => data_get($coverage_check, 'message')]
                ]
            ], data_get($coverage_check, 'status_code'));
        }

        $taxData = $taxResponse->getData(true);

        // Resolved once and handed down, the way place_order threads $pro_offer: every call
        // is a user lookup plus a subscription and settings read, and three independent reads
        // of one piece of state inside a single request can disagree as well as cost queries.
        $proOffer = $this->getProCustomerOffer($request->user?->id);

        $delivery = $this->checkoutDeliveryBlock(
            $request,
            $restaurant,
            (float) ($taxData['total_price'] ?? $request->order_amount),
            $proOffer
        );

        // Nothing is surged onto a free delivery, so the note explaining a higher
        // fee has nothing to explain - it is hidden while the charge is 0.
        $delivery_is_free = $delivery === null
            || (float) ($delivery['delivery_charge'] ?? 0) <= 0
            || ! empty($delivery['free_delivery_by']);

        $proDiscount = (float) ($taxData['pro_discount'] ?? 0);
        $proDeliverySavings = (float) data_get($delivery, 'pro_customer_savings', 0);

        return response()->json([
            'tax' => $taxData,
            'delivery' => $delivery,
            'surge' => $this->checkoutSurgeBlock($request, $delivery_is_free),
            // One place to read what being Pro is worth on this basket. The two figures are
            // already inside tax.total_price and delivery.delivery_charge respectively, and
            // only one of them can ever be non-zero - the admin enables a single benefit - but
            // a checkout should not have to know which to look for, or to subtract to find it.
            //
            // `status` and `type` are named as /pro-customer/active-offer names them, so the
            // two endpoints answer the same two questions in the same words and a client can
            // read them with one parser. What follows is this basket's outcome, which is the
            // part active-offer has no way to know - it describes the entitlement, not what
            // the entitlement was worth here.
            'pro' => [
                'status' => (bool) ($proOffer['status'] ?? false),
                'type' => $proOffer['benefit']['type'] ?? null,
                'discount' => round($proDiscount, config('round_up_to_digit')),
                'delivery_savings' => round($proDeliverySavings, config('round_up_to_digit')),
                'total_savings' => round($proDiscount + $proDeliverySavings, config('round_up_to_digit')),
            ],
            'cashback' => Helpers::getCalculatedCashBackAmount(
                amount: $request->order_amount,
                customer_id: $request->user?->id ?? $request->guest_id ?? 'all'
            ),
        ], 200);
    }

    /**
     * The delivery charge as place_order would compute it, plus the pieces the
     * checkout page shows separately.
     *
     * Returns null when the address falls outside the restaurant's zone — the
     * same case claculate_original_delivery_fee reports as a 403 during ordering.
     */
    private function checkoutDeliveryBlock(Request $request, $restaurant, float $eligibleAmount = 0.0, array $proOffer = ['status' => false]): ?array
    {
        // A free-delivery coupon pre-sets the charge, exactly as in place_order.
        $delivery_charge = null;
        $free_delivery_by = null;

        if ($request['coupon_code']) {
            $coupon_check = Helpers::coupon_check(
                coupon_code: $request['coupon_code'],
                restaurant_id: $restaurant->id,
                user_id: $request?->user?->id,
                is_guest: $request->user ? false : true
            );

            if (data_get($coupon_check, 'code') !== 'coupon') {
                $delivery_charge = data_get($coupon_check, 'delivery_charge');
                $free_delivery_by = data_get($coupon_check, 'free_delivery_by');
            }
        }

        $fee = self::claculate_original_delivery_fee(
            request: $request,
            restaurant: $restaurant,
            delivery_charge: $delivery_charge,
            free_delivery_by: $free_delivery_by
        );

        if (data_get($fee, 'status_code') === 403) {
            return null;
        }

        $base = (float) data_get($fee, 'base_delivery_charge', 0);
        $computed = (float) data_get($fee, 'delivery_charge', 0);

        $overrides = $this->applyDeliveryChargeOverrides(
            $computed,
            $restaurant,
            $eligibleAmount,
            (float) $request->distance,
            $free_delivery_by,
            $proOffer
        );

        $final = (float) $overrides['delivery_charge'];
        $proSavings = (float) $overrides['pro_savings'];

        return [
            // Still the net figure - what the customer pays, and what the total is built from.
            // The row above it on the checkout is delivery_charge_before_pro_discount.
            'delivery_charge' => round($final, config('round_up_to_digit')),
            // The same charge as it stood immediately before the Pro benefit reduced it, so a
            // checkout can show the fee and the discount as two lines - "Delivery 140" then
            // "Pro discount -70" - instead of one already-netted 70 that reads as a cheap
            // delivery rather than a benefit the membership earned.
            //
            // Equal to delivery_charge whenever no Pro discount applied, so a client can render
            // this row unconditionally and show the discount line only while savings are above
            // zero. Zone and vendor free delivery are deliberately *not* unwound here: they are
            // the store's price, not something taken off the customer's.
            'delivery_charge_before_pro_discount' => round($final + $proSavings, config('round_up_to_digit')),
            'original_delivery_charge' => round((float) data_get($fee, 'original_delivery_charge', 0), config('round_up_to_digit')),
            'base_delivery_charge' => $base,
            'surge_amount' => round($computed - $base, 2),
            'free_delivery_by' => $overrides['free_delivery_by'],
            // Deliberately kept, though `pro.delivery_savings` now carries the same figure:
            // this is the key shipped clients already read. The duplicate is the price of not
            // breaking them, so leave it - it is not an oversight to tidy away.
            'pro_customer_savings' => round($proSavings, 2),
            'vehicle_id' => data_get($fee, 'vehicle_id'),
        ];
    }

    public static function coverage_selection_check($request, $restaurant, bool $requirePick = false)
    {
        if (in_array($request['order_type'], ['take_away', 'dine_in'])) {
            return null;
        }

        // Self-delivery bypasses the zone rule entirely in calculateDeliveryCharge(),
        // so there is no charge table to look the pick up in - requiring one would
        // block the order over a field that cannot affect what is charged.
        if ((int) $restaurant->sub_self_delivery === 1) {
            return null;
        }

        $rule = Helpers::activeDeliveryRule($restaurant->zone_id);

        if (! $rule || ! $rule->usesChargeTable()) {
            return null;
        }

        $isArea = $rule->pricing_method === DeliveryRule::METHOD_AREA;
        $picked = $isArea ? $request->area_id : $request->zip_code_id;

        if (! $picked) {
            if (! $requirePick) {
                return null;
            }

            return [
                'status_code' => 403,
                'code' => $isArea ? 'area_id' : 'zip_code_id',
                'message' => $isArea
                    ? translate('messages.Please_select_your_area_to_calculate_the_delivery_charge.')
                    : translate('messages.Please_select_your_ZIP_code_to_calculate_the_delivery_charge.'),
            ];
        }

        $belongs = Helpers::coverageBelongsToZone(
            $restaurant->zone_id,
            $isArea ? $picked : null,
            $isArea ? null : $picked
        );

        if (! $belongs) {
            return [
                // A wrong id is a real validation error and fails everywhere.
                'reason' => 'invalid',
                'status_code' => 403,
                'code' => $isArea ? 'area_id' : 'zip_code_id',
                'message' => translate('messages.The_selected_option_is_not_available_in_this_zone.'),
            ];
        }

        return null;
    }
    private function checkoutSurgeBlock(Request $request, bool $hideCustomerNote = false): ?array
    {
        Helpers::getZoneIds($request);

        $zoneIds = json_decode($request->header('zoneId'), true);
        $zoneIds = is_array($zoneIds) ? $zoneIds : [$zoneIds];

        $at = Helpers::parse_schedule_at($request->schedule_at) ?? now();

        foreach (array_filter($zoneIds) as $zoneId) {
            $surge = SurgePriceLogic::resolve($zoneId, $at);

            if ((float) $surge['price'] <= 0) {
                continue;
            }

            $surge['customer_note'] = $hideCustomerNote ? '' : (SurgePriceLogic::customerNote($surge) ?? '');
            if ($hideCustomerNote) {
                $surge['customer_note_status'] = 0;
            }
            $surge['zone_id'] = (int) $zoneId;

            return $surge;
        }

        return null;
    }

    public function getSurgePriceAmount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_time' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        Helpers::getZoneIds($request);

        $zoneIds = json_decode($request->header('zoneId'), true);
        $zoneIds = is_array($zoneIds) ? $zoneIds : [$zoneIds];

        foreach (array_filter($zoneIds) as $zoneId) {
            $surge = SurgePriceLogic::resolve($zoneId, $request->date_time);

            if ((float) $surge['price'] <= 0) {
                continue;
            }
            $surge['customer_note'] = SurgePriceLogic::customerNote($surge) ?? '';
            $surge['zone_id'] = (int) $zoneId;

            return response()->json(['surge_price' => $surge], 200);
        }
        return response()->json(['surge_price' => null], 200);
    }

    public function deliveryChargeCoverageList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if (! $request->restaurant_id) {
            return response()->json(Helpers::deliveryChargeCoverageForRequest($request), 200);
        }

        $restaurant = Restaurant::withoutGlobalScopes()->with('restaurant_sub')->find($request->restaurant_id);

        if (! $restaurant || ! $restaurant->zone_id) {
            return response()->json([
                'errors' => [
                    ['code' => 'restaurant', 'message' => translate('messages.Restaurant_not_found')]
                ]
            ], 404);
        }

        // A self delivery restaurant charges its own rates - calculateDeliveryCharge() and
        // getDeliveryFee() both take the restaurant's own per km, minimum and maximum and
        // never look at the zone's rule - so there is no charge table for an area or a zip
        // code to be looked up in. coverage_selection_check() already skips these orders for
        // that reason; sending the list anyway asked the customer to pick from something that
        // cannot change what they pay.
        //
        // The empty payload is the shape a zone with no rule already answers with, so a client
        // reading delivery_charge_type to decide which picker to draw shows none, unchanged.
        if ((int) $restaurant->sub_self_delivery === 1) {
            return response()->json(['delivery_charge_type' => null, 'data' => []], 200);
        }

        return response()->json(Helpers::deliveryChargeCoverageForZone($restaurant->zone_id), 200);
    }

    private function get_saver_delivery_time($order)
    {
        if (! $order->delivery_type || $order->order_type != 'delivery' || $order->restaurant?->sub_self_delivery == 1) {
            return null;
        }

        $deliveryOption = ZoneDeliveryOption::where('delivery_type', $order->delivery_type)
            ->where('zone_id', $order->zone_id)
            ->first();

        if (! $deliveryOption) {
            return null;
        }

        [$min, $max] = EtaLogic::parseDeliveryTime($order->restaurant?->delivery_time);

        if ($min === null) {
            return null;
        }

        $shift = $order->delivery_type == 'express'
            ? -$this->optionMinutes($deliveryOption->reduce_delivery_time)
            : ($order->delivery_type == 'slightly_delay' ? $this->optionMinutes($deliveryOption->add_delivery_time) : 0);

        $min = max(0, $min + $shift);
        $max = max($min, $max + $shift);

        return $min . '-' . $max . ' ' . translate('messages.min');
    }

    private function optionMinutes($value): int
    {
        if (is_array($value)) {
            $amount = (float) ($value['value'] ?? 0);

            return (int) round(($value['unit'] ?? 'min') === 'hour' ? $amount * 60 : $amount);
        }

        return (int) $value;
    }
     public function get_order_latest_list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $user_id = $request->user ? $request->user->id : $request['guest_id'];

        $paginator = Order::withCount('details')
            ->with([
                'restaurant:id,name,logo',
                'details:id,order_id,food_id,item_campaign_id',
                'details.food:id,image',
                'details.campaign' => fn($query) => $query->select('id', 'image')->withoutGlobalScope(\App\Scopes\ZoneScope::class),
                'restaurant.restaurant_config:id,restaurant_id,verified_seller'

            ])
            ->whereHas('restaurant', function($query){
                $query->active();
            })
            ->where(['user_id' => $user_id])
            ->notHiddenForCustomer()
            ->when($request->restaurant_id, function($query) use($request){
                $query->where('restaurant_id', $request->restaurant_id);
            })
            ->whereNull('subscription_id')
            ->whereDoesntHave('details', function ($query) {
                $query->whereNotNull('bogo_offer_id');
            })
            ->when(!isset($request->user) , function($query){
                $query->where('is_guest' , 1);
            })

            ->when(isset($request->user)  , function($query){
                $query->where('is_guest' , 0);
            })

            ->latest()->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $orders = collect($paginator->items())->map(function ($order) {
            return [
                'id'               => $order->id,
                'created_at'       => $order->created_at,
                'order_amount'     => (float) $order->order_amount,
                'total_item_count' => (int) $order->details_count,
                'campaign'         => $order->details->contains(fn($d) => $d->food_id == null),
                'item_images'      => $order->details->map(fn($d) => $d->food?->image_full_url ?? $d->campaign?->image_full_url)->filter()->values(),
                'restaurant'       => $order->restaurant ? [
                    'id'             => $order->restaurant->id,
                    'name'           => $order->restaurant->name,
                    'logo_full_url'  => $order->restaurant->logo_full_url,
                    'verified_seller' => Helpers::get_verified_seller_status($order->restaurant, $order->restaurant?->restaurant_config),
                ] : null,
            ];
        });

        $data = [
            'total_size' => $paginator->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'orders' => $orders,
        ];
        return response()->json($data, 200);
    }


}
