
<div class="d-flex flex-row initial-47">
    <table class="table table-align-middle">
        <thead class="thead-light border-0 text-center">
        <tr>
            <th class="py-2" scope="col"><?php echo e(translate('messages.item')); ?></th>
            <th class="py-2" scope="col" class="text-center"><?php echo e(translate('messages.qty')); ?></th>
            <th class="py-2" scope="col"><?php echo e(translate('messages.price')); ?></th>
            <th class="py-2" scope="col"><?php echo e(translate('messages.delete')); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        use App\CentralLogics\Helpers;
        $subtotal = 0;
        $addon_price = 0;
        $tax = 0;


        $discount_on_product = 0;
        $variation_price = 0;
        $show_ddditional_price = false;
        ?>
        <?php if(session()->has('cart') && count( session()->get('cart')) > 0 && data_get(session()->get('cart'),0)): ?>
                <?php
                $cart = session()->get('cart');
                $show_ddditional_price=true;
                ?>

            <?php $__currentLoopData = session()->get('cart'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $cartItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(is_array($cartItem)): ?>
                        <?php
                        $variation_price += $cartItem['variation_price'];
                        $product_subtotal = ($cartItem['price']) * $cartItem['quantity'];
                        $discount_on_product += $cartItem['discount'];
                        $subtotal += $product_subtotal;
                        $addon_price += $cartItem['addon_price'];
                        ?>
                    <tr>
                        <td class="media cart--media align-items-center cursor-pointer quick-View-Cart-Item"
                            data-product-id="<?php echo e($cartItem['id']); ?>" data-item-key="<?php echo e($key); ?>">
                            <img class="avatar avatar-sm mr-2 onerror-image"
                                 src="<?php echo e($cartItem['image_full_url']); ?>"
                             data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/160x160/img2.jpg')); ?>"
                             alt="<?php echo e(data_get($cartItem,'image')); ?> image">


                            <div class="media-body">
                                <h5 class="text-hover-primary mb-0"><?php echo e(Str::limit($cartItem['name'], 10)); ?></h5>
                                <small><?php echo e(Str::limit($cartItem['variant'], 20)); ?></small>
                            </div>
                        </td>
                        <td class="align-items-center text-center">
                            <label>
                                <input type="number" data-key="<?php echo e($key); ?>"
                                data-value="<?php echo e($cartItem['quantity']); ?>"
                                data-option_ids="<?php echo e($cartItem['variation_option_ids']); ?>"
                                data-food_id="<?php echo e($cartItem['id']); ?>"

                                class="w-50px text-center rounded border  update-Quantity"
                                       value="<?php echo e($cartItem['quantity']); ?>" min="1"
                                       max="<?php echo e($cartItem['maximum_cart_quantity'] ?? '9999999999'); ?>" >
                            </label>
                        </td>
                        <td class="text-center px-0 py-1">
                            <div class="btn">
                                <?php echo e(Helpers::format_currency($product_subtotal)); ?>

                            </div>
                        </td>
                        <td class="align-items-center">
                            <div class="btn--container justify-content-center">
                                <a href="javascript:"
                                   data-product-id="<?php echo e($key); ?>"
                                   class="btn btn-sm btn--danger action-btn btn-outline-danger remove-From-Cart"> <i
                                        class="tio-delete-outlined"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>

                <tr>
                    <td colspan="4">
                        <div class="py-5 text-center border-bottom">
                             <div class="">
                                 <img src="<?php echo e(dynamicAsset('assets/admin/img/no-items_add.png')); ?>" alt="img" class="mb-2">
                                 <div class="mb-0">
                                     <span class="fs-14 text-muted"><?php echo e(translate('messages.No Items added yet')); ?></span>
                                 </div>
                             </div>
                         </div>

                    </td>
                </tr>

                <?php endif; ?>


        </tbody>
    </table>
</div>

<?php
$add = false;

$is_delivery = session()->get('order_type') == 'delivery';

$delivery_fee = $show_ddditional_price ? session()->get('delivery_charge') : 0;

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

$pos_pro_delivery = $pos_pro_helper->applyProCustomerDeliveryFee(
    $pos_pro_offer,
    (float) $delivery_fee,
    (float) $total,
    // Passed for the same reason placePosOrder passes them: without the bearer and the
    // engine's own floor, a partial benefit discounts a fee the cart shows and the order
    // does not, and the two disagree at the moment of placing.
    \App\CentralLogics\Helpers::zoneFreeDeliveryApplies($restaurant_data?->zone_id, (float) $total) ? 'admin' : ($restaurant_data?->free_delivery ? 'vendor' : null),
    \App\CentralLogics\Helpers::minimumDeliveryCharge($restaurant_data)
);
$pos_pro_delivery_savings = (float) $pos_pro_delivery['savings'];
$delivery_fee = (float) $pos_pro_delivery['delivery_charge'];
$pos_pro_hides_delivery_type = ($pos_pro_offer['status'] ?? false)
    && ($pos_pro_offer['benefit']['type'] ?? null) === 'delivery_fee';

// Resolved against the fee actually being paid, after every free-delivery and Pro
// reduction — the same order, and the same method, placePosOrder() uses. The cart no
// longer echoes back the browser's own arithmetic from the session.
$pos_saver = ($show_ddditional_price && $is_delivery)
    ? $pos_pro_helper->resolveSaverDeliveryOption(
        $restaurant_data,
        $restaurant_data?->zone_id,
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

// ?? 0 because the session holds no tax until setPosCalculatedTax() has run once,
// and the cart renders before that on a page opened with a cart already in session.
$tax_amount = $show_ddditional_price ? (session()->get('tax_amount') ?? 0) : 0;
$tax_included = $show_ddditional_price? session()->get('tax_included')  ?? 0 : 0;

$additional_charge= 0.00;
if(Helpers::get_business_data('additional_charge_status')){
   $additional_charge= $show_ddditional_price ? Helpers::get_business_data('additional_charge') : 0;
}
$extra_packaging_data  =  Helpers::get_business_settings('extra_packaging_charge')   ?? 0;
$extra_packaging_amount =  ($show_ddditional_price && $extra_packaging_data == 1 && $restaurant_data?->restaurant_config?->is_extra_packaging_active == 1  && $restaurant_data?->restaurant_config?->extra_packaging_status == 1) ? $restaurant_data?->restaurant_config?->extra_packaging_amount : 0;

$total = $total + $delivery_fee + $delivery_type_charge;
if (isset($cart['paid'])) {
    $paid = $cart['paid'];
    $change = $total +  $tax_amount + $additional_charge + $extra_packaging_amount - $paid;
} else {
    $paid = $total +  $tax_amount + $additional_charge + $extra_packaging_amount;
    $change = 0;
}
?>

<?php ($pos_delivery_note = $show_ddditional_price && $is_delivery && $delivery_fee > 0 ? session()->get('delivery_charge_note') : null); ?>
<form action="<?php echo e(route('admin.pos.order')); ?>?restaurant_id=<?php echo e($restaurant_data?->id ?? ''); ?>" id='order_place' method="post">
    <?php echo csrf_field(); ?>
    <input type="hidden" value="<?php echo e($food_id ?? null); ?>" id="cart_food_id">
    <input type="hidden" name="user_id" id="customer_id">
    <input type="hidden" name="delivery_type" value="<?php echo e(session()->get('delivery_type')); ?>">
    
    <input type="hidden" name="delivery_type_charge" value="<?php echo e($delivery_type_charge); ?>">
    <input type="hidden" id="delivery_charge_display" value="<?php echo e(session()->get('delivery_charge_display', $delivery_fee)); ?>">
    <div class="box p-3">
        <dl class="row">

            <dt class="col-6 font-regular"><?php echo e(translate('messages.addon')); ?>:</dt>
            <dd class="col-6 text-right"><?php echo e(Helpers::format_currency($addon_price)); ?></dd>

            <dt class="col-6 font-regular"><?php echo e(translate('messages.subtotal')); ?>


                <?php if($tax_included ==  1): ?>
                    (<?php echo e(translate('messages.TAX_Included')); ?>)
                <?php endif; ?>
                :
            </dt>
            <dd class="col-6 text-right"><?php echo e(Helpers::format_currency($subtotal+$addon_price)); ?></dd>

            <?php if($delivery_type_charge != 0): ?>
                <dt class="col-6 font-regular d-none"><?php echo e($deliveryTypeLabel); ?> :</dt>
                <dd class="col-6 text-right d-none"><?php echo e(Helpers::format_currency($delivery_type_charge)); ?></dd>
            <?php endif; ?>
            
            <dt class="col-6 font-regular">
                <?php echo e(translate('messages.discount')); ?>

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
                <?php if($pos_discount_note): ?>
                    <i class="tio-info fs-14 text-8797AB" data-toggle="tooltip"
                       title="<?php echo e($pos_discount_note); ?>"></i>
                <?php endif; ?>
                :
            </dt>
            <dd class="col-6 text-right">- <?php echo e(Helpers::format_currency(round($discount_on_product,2))); ?></dd>
            <?php if($pos_pro_discount > 0): ?>
                <dt class="col-6 font-regular"><?php echo e(translate('messages.Pro_Discount')); ?> :</dt>
                <dd class="col-6 text-right">- <?php echo e(Helpers::format_currency(round($pos_pro_discount, 2))); ?></dd>
            <?php endif; ?>
            <dt class="col-6 font-regular">
                <?php echo e(translate('messages.delivery_fee')); ?> <?php echo e($delivery_type_charge != 0 ? '('.$deliveryTypeLabel.')' : ''); ?>

                
                <?php if($pos_delivery_note): ?>
                    <i class="tio-info fs-14 text-8797AB pos-delivery-note" data-toggle="tooltip"
                       title="<?php echo e($pos_delivery_note); ?>"></i>
                <?php endif; ?>
                :
            </dt>
            <dd class="col-6 text-right" id="delivery_price">
                <?php echo e(Helpers::format_currency($delivery_fee + $delivery_type_charge)); ?>

            </dd>
            <?php if($tax_included != 1): ?>
                <dt class="col-6 font-regular"><?php echo e(translate('messages.vat/tax')); ?>:</dt>
                <dd class="col-6 text-right">
                    <?php echo e(Helpers::format_currency(round($tax_amount,2))); ?>

                </dd>
            <?php endif; ?>

           <?php if(Helpers::get_business_data('additional_charge_status')): ?>
               <dt class="col-6 font-regular"><?php echo e(Helpers::get_business_data('additional_charge_name')??translate('messages.additional_charge')); ?> :</dt>
               <dd class="col-6 text-right">
                   <?php if($subtotal + $addon_price > 0): ?>
                   <?php echo e(Helpers::format_currency(round($additional_charge, 2))); ?>

                   <?php else: ?>
                   <?php echo e(Helpers::format_currency($additional_charge)); ?>

                   <?php endif; ?>
               </dd>
           <?php endif; ?>


            <dt class="col-6 font-regular"><?php echo e(translate('Extra Packaging Amount')); ?> :</dt>
            <dd class="col-6 text-right"> <?php echo e(Helpers::format_currency(round($extra_packaging_amount,2))); ?></dd>

            <dd class="col-12">
                <hr class="m-0">
            </dd>
            <dt class="col-6 font-regular"><?php echo e(translate('Total')); ?>:</dt>
            <dd class="col-6 text-right h4 b"> <?php echo e(Helpers::format_currency(round($total+ $additional_charge + $extra_packaging_amount + $tax_amount, 2))); ?> </dd>
        </dl>
        <div class="pos--payment-options mt-3 mb-3">
            <h5 class="mb-3"><?php echo e(translate($add ? 'messages.Payment Method' : 'Paid by')); ?></h5>
            <ul>
                <?php if($add): ?>
                    <?php ($cod=Helpers::get_business_settings('cash_on_delivery')); ?>
                    <?php if($cod['status']): ?>
                        <li>
                            <label>
                                <input type="radio" name="type" value="cash" hidden checked>
                                <span><?php echo e(translate('Cash_On_Delivery')); ?></span>
                            </label>
                        </li>
                    <?php endif; ?>
                <?php else: ?>
                    <li>
                        <label>
                            <input type="radio" name="type" value="cash" hidden="" checked>
                            <span><?php echo e(translate('messages.Cash')); ?></span>
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="radio" name="type" value="card" hidden="">
                            <span><?php echo e(translate('messages.Card')); ?></span>
                        </label>
                    </li>
                    <li id="wallet_payment_li">
                        <label>
                            <input type="radio" name="type" value="wallet" id="wallet_payment" hidden="">
                            <span><?php echo e(translate('messages.wallet')); ?></span>
                        </label>
                    </li>
                <?php endif; ?>

            </ul>
        </div>


        <div class="row button--bottom-fixed g-1 bg-white pt-0">
            <div class="col-6">
                <button type="submit"
                        class="btn  btn--primary btn-sm btn-block"><?php echo e(translate('place_order')); ?> </button>
            </div>
            <div class="col-6">
                <a href="#" class="btn btn--reset btn-sm btn-block empty-Cart"><?php echo e(translate('Clear_Cart')); ?></a>
            </div>
        </div>
    </div>
</form>

<script>
    window.posProHidesDeliveryType = <?php echo json_encode((bool) $pos_pro_hides_delivery_type, 15, 512) ?>;
    $(function () {
        if (typeof posCartUpdated === 'function') {
            posCartUpdated();
        } else if (window.posProHidesDeliveryType && typeof setDeliveryTypeSectionState === 'function') {
            setDeliveryTypeSectionState(true);
        }
    });
</script>

<div id="show-address-modal" >
    <?php if ($__env->exists('admin-views.pos._address')) echo $__env->make('admin-views.pos._address', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/pos/_cart.blade.php ENDPATH**/ ?>