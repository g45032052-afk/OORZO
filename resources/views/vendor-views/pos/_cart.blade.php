
<div class="d-flex flex-row initial-47">
    <table class="table table-align-middle">
        <thead class="thead-light border-0 text-center">
        <tr>
            <th class="py-2" scope="col">{{translate('messages.item')}}</th>
            <th class="py-2" scope="col" class="text-center">{{translate('messages.qty')}}</th>
            <th class="py-2" scope="col">{{translate('messages.price')}}</th>
            <th class="py-2" scope="col">{{translate('messages.delete')}}</th>
        </tr>
        </thead>
        <tbody>
        <?php
        use App\CentralLogics\Helpers;
        $subtotal = 0;
        $addon_price = 0;
        $discount_on_product = 0;
        $variation_price = 0;
        $show_ddditional_price = false;
        ?>
        @if(session()->has('cart') && count( session()->get('cart')) > 0 && data_get(session()->get('cart'),0) )
                <?php
                $cart = session()->get('cart');

                $show_ddditional_price=true;
                ?>
            @foreach(session()->get('cart') as $key => $cartItem)
                @if(is_array($cartItem))
                        <?php
                        $variation_price += $cartItem['variation_price'];
                        $product_subtotal = ($cartItem['price']) * $cartItem['quantity'];
                        $discount_on_product += $cartItem['discount'];
                        $subtotal += $product_subtotal;
                        $addon_price += $cartItem['addon_price'];
                        ?>
                    <tr>
                        <td class="media cart--media align-items-center cursor-pointer quick-View-Cart-Item"
                            data-product-id="{{$cartItem['id']}}" data-item-key="{{$key}}">
                            <img class="avatar avatar-sm mr-2 onerror-image"
                                 src="{{ $cartItem['image_full_url'] }}"
                             data-onerror-image="{{dynamicAsset('assets/admin/img/160x160/img2.jpg')}}"
                             alt="{{data_get($cartItem,'image')}} image">


                            <div class="media-body">
                                <h5 class="text-hover-primary mb-0">{{Str::limit($cartItem['name'], 10)}}</h5>
                                <small>{{Str::limit($cartItem['variant'], 20)}}</small>
                            </div>
                        </td>
                        <td class="align-items-center text-center">
                            <label>
                                <input type="number" data-key="{{$key}}"
                                data-value="{{$cartItem['quantity']}}"
                                data-option_ids="{{  $cartItem['variation_option_ids']  }}"
                                data-food_id="{{  $cartItem['id']  }}"

                                class="w-50px text-center rounded border  update-Quantity"
                                       value="{{$cartItem['quantity']}}" min="1"
                                       max="{{$cartItem['maximum_cart_quantity'] ?? '9999999999'}}" >
                            </label>
                        </td>
                        <td class="text-center px-0 py-1">
                            <div class="btn">
                                {{Helpers::format_currency($product_subtotal)}}
                            </div>
                        </td>
                        <td class="align-items-center">
                            <div class="btn--container justify-content-center">
                                <a href="javascript:"
                                   data-product-id="{{$key}}"
                                   class="btn btn-sm btn--danger action-btn btn-outline-danger remove-From-Cart"> <i
                                        class="tio-delete-outlined"></i></a>
                            </div>
                        </td>
                    </tr>
                @endif

                @endforeach
                @else

                <tr>
                    <td colspan="4">
                        <div class="py-5 text-center border-bottom">
                             <div class="">
                                 <img src="{{dynamicAsset('assets/admin/img/no-items_add.png')}}" alt="img" class="mb-2">
                                 <div class="mb-0">
                                     <span class="fs-14 text-muted">{{translate('messages.No Items added yet')}}</span>
                                 </div>
                             </div>
                         </div>

                    </td>
                </tr>

                @endif


        </tbody>
    </table>
</div>

<?php
$add = false;

$delivery_fee =  $show_ddditional_price? session()->get('delivery_charge'): 0 ;
$total = $subtotal + $addon_price;

$total = $total - $discount_on_product;

$pos_customer_id = \Illuminate\Support\Facades\Session::get('customer_id');
// PlaceNewOrder rather than the Pro trait alone: the saver charge below has to be the
// figure placePosOrder() will write, and the only way to guarantee that is to ask the
// same method for it.
$pos_pro_helper = new class { use \App\Traits\PlaceNewOrder; };
$pos_pro_apply = $pos_pro_helper->applyProCustomerDiscount(
    $pos_customer_id ? (int) $pos_customer_id : null,
    $subtotal + $addon_price,
    $total
);
$pos_pro_offer = $pos_pro_apply['offer'];
$pos_pro_discount = (float) $pos_pro_apply['discount'];
$total = $pos_pro_apply['total_price'];

$pos_restaurant_for_delivery = Helpers::get_restaurant_data();

$pos_pro_delivery = $pos_pro_helper->applyProCustomerDeliveryFee(
    $pos_pro_offer,
    (float) $delivery_fee,
    (float) $total,
    // Passed for the same reason placePosOrder passes them: without the bearer and the
    // engine's own floor, a partial benefit discounts a fee the cart shows and the order
    // does not, and the two disagree at the moment of placing.
    Helpers::zoneFreeDeliveryApplies($pos_restaurant_for_delivery?->zone_id, (float) $total) ? 'admin' : ($pos_restaurant_for_delivery?->free_delivery ? 'vendor' : null),
    Helpers::minimumDeliveryCharge($pos_restaurant_for_delivery)
);
$pos_pro_delivery_savings = (float) $pos_pro_delivery['savings'];
$delivery_fee = (float) $pos_pro_delivery['delivery_charge'];
$pos_pro_hides_delivery_type = ($pos_pro_offer['status'] ?? false)
    && ($pos_pro_offer['benefit']['type'] ?? null) === 'delivery_fee';

// ?? 0 because the session holds no tax until setPosCalculatedTax() has run once,
// and the cart renders before that on a page opened with a cart already in session.
$tax_amount = $show_ddditional_price ? (session()->get('tax_amount') ?? 0) : 0;
$tax_included =  session()->get('tax_included')  ?? 0;

$additional_charge= 0.00;
if(Helpers::get_business_data('additional_charge_status')){
   $additional_charge= $show_ddditional_price ? Helpers::get_business_data('additional_charge') : 0;
}
$restaurant_data = Helpers::get_restaurant_data();
$extra_packaging_data  =  Helpers::get_business_settings('extra_packaging_charge')   ?? 0;
$extra_packaging_amount =  ($show_ddditional_price && $extra_packaging_data == 1 && $restaurant_data?->restaurant_config?->is_extra_packaging_active == 1  && $restaurant_data?->restaurant_config?->extra_packaging_status == 1) ? $restaurant_data?->restaurant_config?->extra_packaging_amount : 0;

// Resolved against the fee actually being paid, after every free-delivery and Pro
// reduction — the same order, and the same method, placePosOrder() uses. The cart no
// longer echoes back the browser's own arithmetic from the session.
$pos_saver = ($show_ddditional_price && session()->get('order_type') == 'delivery')
    ? $pos_pro_helper->resolveSaverDeliveryOption(
        $pos_restaurant_for_delivery,
        $pos_restaurant_for_delivery?->zone_id,
        session()->get('delivery_type'),
        (float) $delivery_fee
    )
    : ['delivery_type' => null, 'delivery_type_charge' => 0.0];

$deliveryTypeLabels = [
    'standard'       => translate('Standard Delivery'),
    'express'        => translate('Express Delivery'),
    'slightly_delay' => translate('Slightly Delay Delivery'),
];
$deliveryTypeLabel = $deliveryTypeLabels[$pos_saver['delivery_type']] ?? translate('messages.delivery_type_charge');

// Signed, so the running total below adds an express surcharge and subtracts a delay
// discount without a branch of its own.
$delivery_type_charge = $pos_saver['delivery_type'] === 'slightly_delay'
    ? -(float) $pos_saver['delivery_type_charge']
    : (float) $pos_saver['delivery_type_charge'];

$total = $total + $delivery_fee + $delivery_type_charge;
if (isset($cart['paid'])) {
    $paid = $cart['paid'];
    $change = $total +  $tax_amount + $additional_charge + $extra_packaging_amount - $paid;
} else {
    $paid = $total +  $tax_amount + $additional_charge + $extra_packaging_amount;
    $change = 0;
}
?>
{{-- Gated on the fee the customer is actually left paying: the note is built from the
     engine's figure, but the Pro Customer benefit is applied above and can take it to
     nothing, and a line explaining a higher fee has nothing to explain beside a zero. --}}
@php($pos_delivery_note = $show_ddditional_price && session()->get('order_type') == 'delivery' && $delivery_fee > 0 ? session()->get('delivery_charge_note') : null)
<form action="{{route('vendor.pos.order')}}" id='order_place' method="post">
    @csrf
    <input type="hidden" name="user_id" id="customer_id">
    <input type="hidden" name="delivery_type_charge" value="{{ $delivery_type_charge }}">
    <input type="hidden" id="delivery_charge_display" value="{{ session()->get('delivery_charge_display', $delivery_fee) }}">
    <div class="box p-3">
        <dl class="row">

            <dt class="col-6 font-regular">{{translate('messages.addon')}}:</dt>
            <dd class="col-6 text-right">{{Helpers::format_currency($addon_price)}}</dd>

            <dt class="col-6 font-regular">{{translate('messages.subtotal')}}

                @if ($tax_included ==  1)
                    ({{ translate('messages.TAX_Included') }})
                @endif
                :
            </dt>
            <dd class="col-6 text-right">{{Helpers::format_currency($subtotal+$addon_price)}}</dd>


            {{-- The row already carries the happy hour: setPosCalculatedTax() re-runs
                 makePosOrderDetails() after every cart change, which re-prices each line at
                 the effective store wide rate. So this only names what is already in the
                 figure. title= as well as data-toggle, because the cart is swapped in by
                 AJAX and a tooltip initialised at page load never reaches the new markup. --}}
            <dt class="col-6 font-regular">
                {{translate('messages.discount')}}
                <?php
                // Names whichever store wide rate produced this figure. The two are mutually
                // exclusive - get_effective_restaurant_discount() returns the happy hour when a
                // window is open and the standing discount otherwise - so there is one rate to
                // name, never both.
                //
                // Written as a raw PHP block, the way the rest of this file computes its
                // figures. A Blade directive block here stops the compiler closing the inline
                // directive that builds the delivery note above, and the view dies parsing.
                // Guarded on the store: POS opens with no restaurant chosen - /admin/pos
                // carries no restaurant_id until one is picked - and asking a rate of
                // nothing is not a question worth answering.
                $pos_discount_note = $restaurant_data && Helpers::get_effective_restaurant_discount($restaurant_data)
                    ? (Helpers::get_running_happy_hour($restaurant_data)
                        ? translate('messages.Happy_Hour_Discount')
                        : translate('messages.Restaurant_Discount'))
                    : null;
                ?>
                @if ($pos_discount_note)
                    <i class="tio-info fs-14 text-8797AB" data-toggle="tooltip"
                       title="{{ $pos_discount_note }}"></i>
                @endif
                :
            </dt>
            <dd class="col-6 text-right">- {{Helpers::format_currency(round($discount_on_product,2))}}</dd>
            @if ($pos_pro_discount > 0)
                <dt class="col-6 font-regular">{{ translate('messages.Pro_Discount') }} :</dt>
                <dd class="col-6 text-right">- {{ Helpers::format_currency(round($pos_pro_discount, 2)) }}</dd>
            @endif
            <dt class="col-6 font-regular">
                {{ translate('messages.delivery_fee') }}
                {{-- Says in one sentence what quietly raised this fee: the vehicle
                     category's extra charge and the zone's surge, the latter in the
                     admin's own customer-note wording. Absent when neither applies. --}}
                @if ($pos_delivery_note)
                    <i class="tio-info fs-14 text-8797AB pos-delivery-note" data-toggle="tooltip"
                       title="{{ $pos_delivery_note }}"></i>
                @endif
                :
            </dt>
            <dd class="col-6 text-right" id="delivery_price">
                {{ Helpers::format_currency($delivery_fee) }}</dd>

            @if ($delivery_type_charge != 0)
                <dt class="col-6 font-regular">{{ $deliveryTypeLabel }} :</dt>
                <dd class="col-6 text-right">{{ Helpers::format_currency($delivery_type_charge) }}</dd>
            @endif

            {{-- <dt class="col-6 font-regular">{{translate('messages.extra_discount')}} :</dt>--}}
            {{-- <dd class="col-6 text-right">--}}
                {{-- <button class="btn btn-sm" type="button" data-toggle="modal" data-target="#add-discount"><i--}}
                        {{-- class="tio-edit"></i></button>--}}
                {{-- - {{Helpers::format_currency(round($discount_amount,2))}}</dd>--}}

            @if ($tax_included !=  1)
                <dt class="col-6 font-regular">{{ translate('messages.vat/tax') }}:</dt>
                <dd class="col-6 text-right">
{{--                    <button class="btn btn-sm" type="button" data-toggle="modal" data-target="#add-tax"><i--}}
{{--                            class="tio-edit"></i></button>--}}

                    {{Helpers::format_currency(round($tax_amount,2))}}
                </dd>
            @endif

           @if (\App\CentralLogics\Helpers::get_business_data('additional_charge_status'))
               <dt class="col-6 font-regular">{{ \App\CentralLogics\Helpers::get_business_data('additional_charge_name')??translate('messages.additional_charge')  }} :</dt>
               <dd class="col-6 text-right">
                   @if ($subtotal + $addon_price > 0)
                   {{ Helpers::format_currency(round($additional_charge, 2)) }}
                   @else
                   {{Helpers::format_currency($additional_charge)  }}
                   @endif
               </dd>
           @endif


            <dt class="col-6 font-regular">{{translate('Extra Packaging Amount')}} :</dt>
            <dd class="col-6 text-right"> {{Helpers::format_currency(round($extra_packaging_amount,2))}}</dd>

            <dd class="col-12">
                <hr class="m-0">
            </dd>
            <dt class="col-6 font-regular">{{ translate('Total') }}:</dt>
            <dd class="col-6 text-right h4 b"> {{Helpers::format_currency(round($total+ $additional_charge + $extra_packaging_amount + $tax_amount, 2))}} </dd>
       <input type="hidden"  id="total_amount" name="total_amount" value="{{$total+ $additional_charge + $extra_packaging_amount + $tax_amount}}">
        </dl>
        <div class="pos--payment-options mt-3 mb-3">
            <h5 class="mb-3">{{ translate($add ? 'messages.Payment Method' : 'Paid by') }}</h5>
            <ul>
                @if ($add)
                    @php($cod=Helpers::get_business_settings('cash_on_delivery'))
                    @if ($cod['status'])
                        <li>
                            <label>
                                <input type="radio" name="type" value="cash" hidden checked>
                                <span>{{ translate('Cash_On_Delivery') }}</span>
                            </label>
                        </li>
                    @endif
                @else
                    <li>
                        <label>
                            <input type="radio" name="type" value="cash" hidden="" checked>
                            <span>{{ translate('messages.Cash') }}</span>
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="radio" name="type" value="card" hidden="">
                            <span>{{ translate('messages.Card') }}</span>
                        </label>
                    </li>
                    <li id="wallet_payment_li">
                        <label>
                            <input type="radio" name="type" value="wallet" id="wallet_payment" hidden="">
                            <span>{{ translate('messages.wallet') }}</span>
                        </label>
                    </li>
                @endif

            </ul>
        </div>
        @if (!$add)
        <div id="pos--payable-amount">
            <div class="mt-4 d-flex justify-content-between pos--payable-amount">
                <label class="m-0">{{ translate('Paid Amount') }} :</label>
                <div>
                    <span data-toggle="modal" data-target="#insertPayableAmount" class="text-body"><i
                            class="tio-edit"></i></span>
                    <span>{{ Helpers::format_currency($paid) }}</span>
                    <input type="hidden" name="amount" value="{{ $paid }}">
                </div>
            </div>
            <div class="mt-4 d-flex justify-content-between pos--payable-amount">
                <label class="m-0">{{ translate('Change Amount') }} :</label>
                <div>
                    <span>{{ Helpers::format_currency(abs($change)) }}</span>
                    <input type="hidden" value="{{ $change }}">
                </div>
            </div>

        </div>
        @endif

        <div class="row button--bottom-fixed g-1 bg-white pt-0 mt-5">
            <div class="col-6">
                <button type="submit"
                        class="btn  btn--primary btn-sm btn-block">{{ translate('place_order') }} </button>
            </div>
            <div class="col-6">
                <a href="#" class="btn btn--reset btn-sm btn-block empty-Cart">{{  translate('Clear_Cart') }}</a>
            </div>
        </div>
    </div>
</form>

<script>
    window.posProHidesDeliveryType = @json((bool) $pos_pro_hides_delivery_type);
    $(function () {
        if (typeof posCartUpdated === 'function') {
            posCartUpdated();
        } else if (window.posProHidesDeliveryType && typeof setDeliveryTypeSectionState === 'function') {
            setDeliveryTypeSectionState(true);
        }
    });
</script>

<div class="modal fade" id="insertPayableAmount" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light border-bottom py-3">
                <h5 class="modal-title">{{ translate('messages.payment') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id='payable_store_amount'>
                    @csrf
                    <div class="row">
                        <div class="form-group col-12">
                            <label class="input-label"
                                   for="paid">{{ translate('messages.amount') }}({{ Helpers::currency_symbol() }})</label>
                            <input id="paid" type="number" class="form-control" name="paid" min="0" step="0.01"
                                   value="{{ $paid }}">
                        </div>
                    </div>
                    <div class="form-group col-12 mb-0">
                        <div class="btn--container justify-content-end">
                            <button class="btn btn-sm btn--primary payable-Amount" type="button">
                                {{ translate('messages.submit') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- <div class="modal fade" id="add-discount" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{translate('messages.update_discount')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('vendor.pos.discount')}}" method="post" class="row">
                    @csrf
                    <div class="form-group col-sm-6">
                        <label for="discount_input">{{translate('messages.discount')}}</label>
                        <input type="number" class="form-control" name="discount" min="0" id="discount_input"
                            value="{{$discount}}" max="{{ $discount_type=='percent' ? 100 : 1000000000}}">
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="discount_input_type">{{translate('messages.type')}}</label>
                        <select name="type" class="form-control discount-type" id="discount_input_type">
                            <option value="amount" {{$discount_type=='amount'?'selected':''}}>{{translate('messages.amount')}}
                                ({{Helpers::currency_symbol()}})
                            </option>
                            <option value="percent" {{$discount_type=='percent'?'selected':''}}>{{translate('messages.percent')}}
                                (%)
                            </option>
                        </select>
                    </div>
                    <div class="form-group col-sm-12">
                        <button class="btn btn-sm btn--primary" type="submit">{{translate('messages.submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> --}}

{{-- <div class="modal fade" id="add-tax" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{translate('messages.update_tax')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('vendor.pos.tax')}}" method="POST" class="row" id="order_submit_form">
                    @csrf
                    <div class="form-group col-12">
                        <label for="tax">{{translate('messages.tax')}}(%)</label>
                        <input id="tax" type="number" class="form-control" max="100" name="tax" min="0">
                    </div>

                    <div class="form-group col-sm-12">
                        <button class="btn btn-sm btn--primary" type="submit">{{translate('messages.submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> --}}






<div id="show-address-modal" >
    @includeif('vendor-views.pos._address')
</div>
