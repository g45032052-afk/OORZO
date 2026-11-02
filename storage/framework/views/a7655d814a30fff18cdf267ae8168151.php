<div class="content container-fluid initial-38 new-invoice ">
    <div class="row justify-content-center" id="printableArea">
        <div class="col-md-12">
            <div class="text-center">
                <input type="button" class="btn text-white btn--primary non-printable print-Div"
                    value="<?php echo e(translate('messages.Proceed_If_thermal_printer_is_ready.')); ?>" />
                <a href="<?php echo e(url()->previous()); ?>" class="btn btn-danger non-printable"><?php echo e(translate('messages.back')); ?></a>
            </div>
            <hr class="non-printable">
            <?php ($businessLogoUrl = (new \App\Models\DataSetting)->invoice_logo); ?>
            <div class="initial-38-1">
                <?php if(isset($invoiceSettings['invoice_logo_status']) && $invoiceSettings['invoice_logo_status']): ?>
                    <div class="pt-3">
                    <img src="<?php echo e($businessLogoUrl); ?>"
                    data-default="<?php echo e(dynamicAsset('assets/admin/img/restaurant-invoice.png')); ?>"
                    class="initial-38-2" alt="">
                </div>
                <?php endif; ?>
                <div class="text-center pt-3 mb-3">
                    <h5  class="initial-38-3"><?php echo e($order->restaurant->name); ?></h5>
                    <h5 class="text-break initial-38-4">
                        <?php echo e($order->restaurant->address); ?>

                    </h5>
                    <h5 class="text-muted"><?php echo e(Carbon\Carbon::parse($order['created_at'])->locale(app()->getLocale())->translatedFormat('d/M/Y ' . config('timeformat'))); ?></h5>
                    <h5>
                        <span><?php echo e(translate('phone')); ?></span> <span>:</span> <span><?php echo e($order->restaurant->phone); ?></span>
                    </h5>
                    <?php if($order?->restaurant?->gst_status): ?>
                        <h5 class="initial-38-4 initial-38-3 fz-12px text-center">
                            <span><?php echo e(translate('Gst_No')); ?></span> <span>:</span> <span><?php echo e($order->restaurant->gst_code); ?></span>
                        </h5>
                    <?php endif; ?>
                    <?php if(isset($invoiceSettings['business_identity_status']) && $invoiceSettings['business_identity_status']): ?>

                        <div id="tax_id_text"
                            class="text-muted mt-1 <?php echo e((isset($invoiceSettings['business_identity_type']) && $invoiceSettings['business_identity_type'] != 'tax') ? 'd--none' : ''); ?>">
                            <?php echo e(translate('Tax Id')); ?> : <?php echo e($invoiceSettings['tax_number'] ?? '8494646894'); ?></div>
                        <div id="bin_number_text"
                            class="text-muted mt-1 <?php echo e((!isset($invoiceSettings['business_identity_type']) || $invoiceSettings['business_identity_type'] != 'bin') ? 'd--none' : ''); ?>">
                            <?php echo e(translate('Bin Number')); ?> : <?php echo e($invoiceSettings['bin_number'] ?? '8494646894'); ?></div>
                        <div id="musak_text"
                            class="text-muted mt-1 <?php echo e((!isset($invoiceSettings['business_identity_type']) || $invoiceSettings['business_identity_type'] != 'musak') ? 'd--none' : ''); ?>">
                            <?php echo e(translate('Musak')); ?> : <?php echo e($invoiceSettings['musak_number'] ?? '8494646894'); ?></div>
                    <?php endif; ?>
                        
                </div>

                <h5 class="d-flex justify-content-between gap-2">
                    <span class=""> <?php echo e(translate('Order_Type')); ?> </span>
                    <span class=""><?php echo e($order->order_type == 'delivery' ? translate('Home_Delivery'): translate($order->order_type)); ?></span>
                </h5>

                <div class="border border-dashed border-secondary p-3 rounded">
                    <h5 class="d-flex justify-content-between gap-2">
                        <span class="text-muted"> <?php echo e(translate('Order_ID')); ?> </span>
                        <span class=""><?php echo e($order['id']); ?></span>
                    </h5>
                    <?php if($order->order_type == 'dine_in' &&  $order?->OrderReference?->table_number ): ?>
                        <h5 class="d-flex justify-content-between gap-2">
                            <span class="text-muted"> <?php echo e(translate('Table_Number')); ?> </span>
                            <span class=""><?php echo e($order?->OrderReference?->table_number); ?></span>
                        </h5>
                    <?php endif; ?>
                    <?php if($order->order_type == 'dine_in' &&  $order?->OrderReference?->token_number ): ?>
                        <h5 class="d-flex justify-content-between gap-2">
                            <span class="text-muted"> <?php echo e(translate('Token_Number')); ?> </span>
                            <span class=""><?php echo e($order?->OrderReference?->token_number); ?></span>
                        </h5>
                    <?php endif; ?>

                    <?php if($order?->delivery_address): ?>
                    <h5 class="d-flex justify-content-between gap-2">
                        <span class="text-muted"><?php echo e(translate('Customer_Name')); ?></span>
                        <span>
                            <?php echo e(isset($order->delivery_address) ? json_decode($order->delivery_address, true)['contact_person_name'] : ''); ?>

                        </span>
                    </h5>
                    <h5 class="d-flex justify-content-between gap-2">
                        <span class="text-muted"><?php echo e(translate('messages.phone')); ?></span>
                        <span>
                            <?php echo e(isset($order->delivery_address) ? json_decode($order->delivery_address, true)['contact_person_number'] : ''); ?>

                        </span>
                    </h5>

                    <?php if(!in_array($order->order_type, ['dine_in','take_away'])   ): ?>
                        <h5 class="d-flex justify-content-between gap-2 text-break">
                            <span class="text-muted text-nowrap"><?php echo e(translate('messages.delivery_Address')); ?></span>
                            <span class="text-right">
                                <?php echo e(isset($order->delivery_address) ? json_decode($order->delivery_address, true)['address'] : ''); ?>

                            </span>
                        </h5>
                        <div class="d-flex gap-2 align-items-center justify-content-end" style="font-size: 10px">
                            <?php if(isset($order->delivery_address) && isset(json_decode($order->delivery_address, true)['road'])  ): ?>
                            <div class="d-flex gap-1">
                                <span class="text-muted"><?php echo e(translate('messages.street_No')); ?></span>:
                                <span>
                                    <?php echo e(json_decode($order->delivery_address, true)['road']); ?>

                                </span>
                            </div>
                            <?php endif; ?>


                            <?php if(isset($order->delivery_address) && isset(json_decode($order->delivery_address, true)['house']) ): ?>
                            <div class="d-flex gap-1">
                                <span class="text-muted"><?php echo e(translate('messages.House')); ?></span>:
                                <span class="font-light">
                                    <?php echo e(json_decode($order->delivery_address, true)['house']); ?>

                                </span>
                            </div>
                            <?php endif; ?>

                            <?php if(isset($order->delivery_address) && isset(json_decode($order->delivery_address, true)['floor']) ): ?>
                                <div class="d-flex gap-1">
                                    <span class="text-muted"><?php echo e(translate('messages.floor')); ?></span>:
                                    <span class="font-light">
                                        <?php echo e(json_decode($order->delivery_address, true)['floor']); ?>

                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php elseif($order?->customer): ?>
                    <h5 class="d-flex justify-content-between gap-2">
                        <span class="text-muted"><?php echo e(translate('Customer_Name')); ?></span>
                        <span>
                            <?php echo e($order?->customer?->f_name .' '. $order?->customer?->l_name); ?>

                        </span>
                    </h5>
                    <h5 class="d-flex justify-content-between gap-2">
                        <span class="text-muted"><?php echo e(translate('messages.phone')); ?></span>
                        <span>
                            <?php echo e($order?->customer?->phone); ?>

                        </span>
                    </h5>
                    <?php else: ?>
                    <h5 class="d-flex justify-content-between gap-2">
                        <?php echo e(translate('Customer_Name')); ?> :
                        <span class="font-light">
                            <?php echo e(translate('messages.walk_in_customer')); ?>

                        </span>
                    </h5>
                    <?php endif; ?>
                </div>

                <table class="table table-borderless table-align-middle mt-1 mb-1">
                    <thead>
                        <tr>
                            <th style="border-bottom: 1px dashed #979797 !important;"><?php echo e(translate('messages.QTY')); ?></th>
                            <th style="border-bottom: 1px dashed #979797 !important;"><?php echo e(translate('messages.item')); ?></th>
                            <th class="text-right" style="border-bottom: 1px dashed #979797 !important;"><?php echo e(translate('messages.price')); ?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php ($sub_total = 0); ?>
                        <?php ($total_tax = 0); ?>
                        <?php ($total_dis_on_pro = 0); ?>
                        <?php ($add_ons_cost = 0); ?>
                        <?php $__currentLoopData = $order->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($detail->food_id || $detail->campaign == null): ?>
                                <tr>
                                    <td class="">
                                        <?php echo e($detail['quantity']); ?>x
                                    </td>
                                    <td class="text-break text-left">
                                        <?php echo e(json_decode($detail->food_details, true)['name']); ?> <br>

                                        <?php if(count(json_decode($detail['variation'], true)) > 0): ?>
                                            <strong><?php echo e(translate('messages.variation')); ?> : </strong>
                                            <?php $__currentLoopData = json_decode($detail['variation'],true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if( isset($variation['name'])  && isset($variation['values'])): ?>
                                                <span class="d-block text-capitalize">
                                                        <strong><?php echo e($variation['name']); ?> - </strong>
                                                </span>
                                                <?php $__currentLoopData = $variation['values']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="d-block text-capitalize">
                                                    <!-- &nbsp;   &nbsp; -->
                                                     <?php echo e($value['label']); ?> :
                                                    <strong><?php echo e(\App\CentralLogics\Helpers::format_currency( $value['optionPrice'])); ?></strong>
                                                </span>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php else: ?>
                                                <?php if(isset(json_decode($detail['variation'],true)[0])): ?>
                                                    <?php $__currentLoopData = json_decode($detail['variation'],true)[0]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key1 =>$variation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="font-size-sm text-body">
                                                            <span><?php echo e($key1); ?> :  </span>
                                                            <span class="font-weight-bold"><?php echo e($variation); ?></span>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php endif; ?>
                                                    <?php break; ?>
                                            <?php endif; ?>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    <?php else: ?>
                                                    <div class="font-size-sm text-body">
                                                        <span><?php echo e(translate('messages.Price')); ?> : </span>
                                                        <span
                                                            class="font-weight-bold"><?php echo e(\App\CentralLogics\Helpers::format_currency($detail->price)); ?></span>
                                                    </div>
                                            <?php endif; ?>




                                        <?php $__currentLoopData = json_decode($detail['add_ons'], true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key2 => $addon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($key2 == 0): ?>
                                                <strong><u><?php echo e(translate('messages.addons')); ?> : </u></strong>
                                            <?php endif; ?>
                                            <div class="font-size-sm text-body">
                                                <span class="text-break"><?php echo e($addon['name']); ?> : </span>
                                                <span class="font-weight-bold">
                                                    <?php echo e($addon['quantity']); ?> x
                                                    <?php echo e(\App\CentralLogics\Helpers::format_currency($addon['price'])); ?>

                                                </span>
                                            </div>
                                            <?php ($add_ons_cost += $addon['price'] * $addon['quantity']); ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </td>
                                    <td class="text-right w-28p">
                                        <?php ($amount = $detail['price'] * $detail['quantity']); ?>
                                        <?php echo e(\App\CentralLogics\Helpers::format_currency($amount)); ?>

                                    </td>
                                </tr>
                                <?php ($sub_total += $amount); ?>
                                <?php ($total_tax += $detail['tax_amount'] * $detail['quantity']); ?>
                            <?php elseif($detail->campaign): ?>
                                <tr>
                                    <td class="">
                                        <?php echo e($detail['quantity']); ?>

                                    </td>
                                    <td class="">
                                        <?php echo e($detail->campaign['title']); ?> <br>
                                    <?php if(count(json_decode($detail['variation'], true)) > 0): ?>
                                        <strong><u><?php echo e(translate('messages.variation')); ?> : </u></strong>
                                        <?php $__currentLoopData = json_decode($detail['variation'],true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if( isset($variation['name'])  && isset($variation['values'])): ?>
                                            <span class="d-block text-capitalize">
                                                    <strong><?php echo e($variation['name']); ?> - </strong>
                                            </span>
                                            <?php $__currentLoopData = $variation['values']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="d-block text-capitalize">
                                                &nbsp;   &nbsp; <?php echo e($value['label']); ?> :
                                                <strong><?php echo e(\App\CentralLogics\Helpers::format_currency( $value['optionPrice'])); ?></strong>
                                                </span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php else: ?>
                                            <?php if(isset(json_decode($detail['variation'],true)[0])): ?>
                                                <?php $__currentLoopData = json_decode($detail['variation'],true)[0]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key1 =>$variation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="font-size-sm text-body">
                                                        <span><?php echo e($key1); ?> :  </span>
                                                        <span class="font-weight-bold"><?php echo e($variation); ?></span>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php endif; ?>
                                                <?php break; ?>
                                        <?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php else: ?>
                                                <div class="font-size-sm text-body">
                                                    <span><?php echo e(translate('messages.Price')); ?> : </span>
                                                    <span
                                                        class="font-weight-bold"><?php echo e(\App\CentralLogics\Helpers::format_currency($detail->price)); ?></span>
                                                </div>
                                <?php endif; ?>

                                        <?php $__currentLoopData = json_decode($detail['add_ons'], true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key2 => $addon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($key2 == 0): ?>
                                                <strong><u><?php echo e(translate('messages.addons')); ?> : </u></strong>
                                            <?php endif; ?>
                                            <div class="font-size-sm text-body">
                                                <span><?php echo e($addon['name']); ?> : </span>
                                                <span class="font-weight-bold">
                                                    <?php echo e($addon['quantity']); ?> x
                                                    <?php echo e(\App\CentralLogics\Helpers::format_currency($addon['price'])); ?>

                                                </span>
                                            </div>
                                            <?php ($add_ons_cost += $addon['price'] * $addon['quantity']); ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </td>
                                    <td class="w-28p">
                                        <?php ($amount = $detail['price'] * $detail['quantity']); ?>
                                        <?php echo e(\App\CentralLogics\Helpers::format_currency($amount)); ?>

                                    </td>
                                </tr>
                                <?php ($sub_total += $amount); ?>
                                <?php ($total_tax += $detail['tax_amount'] * $detail['quantity']); ?>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <div class="border-bottom-dashed mb-3"></div>
                <div class="initial-38-9">
                    <div class="px-3">
                        <dl class="row text-right">
                            <dt class="col-6 text-left text-muted"><?php echo e(translate('Items_Price')); ?></dt>
                            <dd class="col-6"><?php echo e(\App\CentralLogics\Helpers::format_currency($sub_total)); ?></dd>
                            <dt class="col-6 text-left text-muted"><?php echo e(translate('Addon_Cost')); ?></dt>
                            <dd class="col-6">
                                <?php echo e(\App\CentralLogics\Helpers::format_currency($add_ons_cost)); ?>

                            </dd>

                            <dd class="col-12 border-bottom-dashed mb-2"></dd>

                            <dt class="col-6 text-left fw-500"><?php echo e(translate('messages.subtotal')); ?>

                                <?php if($order->tax_status == 'included' ): ?>
                                (<?php echo e(translate('messages.TAX_Included')); ?>)
                                <?php endif; ?>
                            </dt>
                            <dd class="col-6 fw-500">
                                <?php echo e(\App\CentralLogics\Helpers::format_currency($sub_total + $add_ons_cost)); ?></dd>

                            <dd class="col-12 border-bottom-dashed mb-2"></dd>

                            <dt class="col-6 text-left text-muted"><?php echo e(translate('messages.discount')); ?></dt>
                            <dd class="col-6">
                                - <?php echo e(\App\CentralLogics\Helpers::format_currency($order['restaurant_discount_amount'])); ?>

                            </dd>

                            <dt class="col-6 text-left text-muted"><?php echo e(translate('messages.coupon_discount')); ?></dt>
                            <dd class="col-6">
                                - <?php echo e(\App\CentralLogics\Helpers::format_currency($order['coupon_discount_amount'])); ?></dd>

                            <?php if($order['ref_bonus_amount'] > 0): ?>
                                <dt class="col-6  text-left text-muted"><?php echo e(translate('messages.Referral_Discount')); ?>:</dt>
                                <dd class="col-6">
                                    - <?php echo e(\App\CentralLogics\Helpers::format_currency($order['ref_bonus_amount'])); ?>

                                </dd>
                            <?php endif; ?>

                            <?php if(($order->orderProDiscount?->amount_saved ?? 0) > 0): ?>
                                <dt class="col-6  text-left text-muted"><?php echo e(translate('messages.Pro_Discount')); ?>:</dt>
                                <dd class="col-6">
                                    - <?php echo e(\App\CentralLogics\Helpers::format_currency($order->orderProDiscount->amount_saved)); ?>

                                </dd>
                            <?php endif; ?>


                                <?php if($order->tax_status == 'excluded' || $order->tax_status == null  ): ?>
                                <dt class="col-6 text-left text-muted"><?php echo e(translate('messages.vat/tax')); ?></dt>
                                <dd class="col-6">
                                    <?php echo e(\App\CentralLogics\Helpers::format_currency($order['total_tax_amount'])); ?>

                                </dd>
                                <?php endif; ?>
                            <dt class="col-6 text-left text-muted"><?php echo e(translate('messages.delivery_man_tips')); ?></dt>
                            <dd class="col-6">
                                <?php ($dm_tips = $order['dm_tips']); ?>
                                <?php echo e(\App\CentralLogics\Helpers::format_currency($dm_tips)); ?>

                            </dd>
                            <dt class="col-6 text-left text-muted"><?php echo e(translate('messages.delivery_charge')); ?></dt>
                            <dd class="col-6">
                                <?php echo e(\App\CentralLogics\Helpers::format_currency($order['delivery_charge'])); ?>

                            </dd>
                            <?php if(!($order->delivery_type_charge > 0)): ?>
                                <dd class="col-12"><hr></dd>
                            <?php endif; ?>

                            <?php ($additional_charge_status = (\App\CentralLogics\Helpers::get_business_data('additional_charge_status') == 1 || $order['additional_charge'] > 0) ? 1 : 0); ?>

                            <?php if($order->delivery_type_charge > 0): ?>
                            <?php
                                $invoiceDeliveryTypeLabels = [
                                    'standard'       => translate('Standard Delivery'),
                                    'express'        => translate('Express Delivery'),
                                    'slightly_delay' => translate('Slightly Delay Delivery'),
                                ];
                                $invoiceDeliveryTypeLabel = $invoiceDeliveryTypeLabels[$order->delivery_type] ?? translate('messages.delivery_type_charge');
                                $additional_charge_status = (\App\CentralLogics\Helpers::get_business_data('additional_charge_status') == 1 || $order['additional_charge'] > 0) ? 1 : 0;
                            ?>
                            <dt class="col-6 text-left text-muted"><?php echo e($invoiceDeliveryTypeLabel); ?></dt>
                            <dd class="col-6">
                                <?php echo e($order['delivery_type'] == 'slightly_delay' ? '-' : '+'); ?>

                                <?php echo e(\App\CentralLogics\Helpers::format_currency($order['delivery_type_charge'])); ?>

                            </dd>
                            <?php if(!$additional_charge_status): ?>
                                <dd class="col-12"><hr></dd>
                            <?php endif; ?>
                            <?php endif; ?>
                            <?php if($additional_charge_status ): ?>
                                <dt class="col-6 text-left text-muted"><?php echo e(\App\CentralLogics\Helpers::get_business_data('additional_charge_name')??translate('messages.additional_charge')); ?>:</dt>
                                <dd class="col-6">
                                    + <?php echo e(\App\CentralLogics\Helpers::format_currency($order['additional_charge'])); ?>

                                </dd>
                            <?php endif; ?>


                            <?php if($order['extra_packaging_amount'] > 0): ?>
                            <dt class="col-6  text-left text-muted"><?php echo e(translate('messages.Extra_Packaging_Amount')); ?>:</dt>
                            <dd class="col-6">
                                + <?php echo e(\App\CentralLogics\Helpers::format_currency($order['extra_packaging_amount'])); ?>

                            </dd>
                            <?php endif; ?>

                            <dd class="col-12 border-bottom-dashed mb-2"></dd>

                            <dt class="col-6 text-left fw-500 fz-20px"><?php echo e(translate('messages.total')); ?></dt>
                            <dd class="col-6 fz-20px fw-500">
                                <?php echo e(\App\CentralLogics\Helpers::format_currency($order['order_amount'] )); ?>

                            </dd>

                            <?php if($order?->payments): ?>
                            <?php $__currentLoopData = $order?->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($payment->payment_status == 'paid'): ?>
                                    <?php if( $payment->payment_method == 'cash_on_delivery'): ?>

                                    <dt class="col-6 text-left text-muted"><?php echo e(translate('messages.Paid_with_Cash')); ?> (<?php echo e(translate('COD')); ?>) </dt>
                                    <?php else: ?>

                                    <dt class="col-6 text-left text-muted"><?php echo e(translate('messages.Paid_by')); ?> <?php echo e(translate($payment->payment_method)); ?> </dt>
                                    <?php endif; ?>
                                <?php else: ?>

                                <dt class="col-6 text-left text-muted"><?php echo e(translate('Due_Amount')); ?> (<?php echo e($payment->payment_method == 'cash_on_delivery' ?  translate('messages.COD') : translate($payment->payment_method)); ?>) :</dt>
                                <?php endif; ?>
                            <dd class="col-6 ">
                                <?php echo e(\App\CentralLogics\Helpers::format_currency($payment->amount)); ?>

                            </dd>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                        </dl>
                    </div>
                </div>

                <dd class="col-12 border-bottom-dashed mb-2"></dd>

                <div class="d-flex flex-row justify-content-between">
                    <span class="text-capitalize d-flex"><span><?php echo e(translate('Paid_by')); ?></span> <span>:</span> <span><?php echo e(translate(str_replace('_', ' ', $order['payment_method']))); ?></span> </span>
                    <?php if($order->adjusment > $order->order_amount): ?>
                    <span><?php echo e(translate('messages.amount')); ?>: <?php echo e($order->adjusment); ?></span>
                    <span><?php echo e(translate('messages.change')); ?>: <?php echo e($order->adjusment - $order->order_amount); ?></span>
                    <?php endif; ?>

                </div>

                <dd class="col-12 border-bottom-dashed my-2"></dd>
                <?php if(isset($invoiceSettings['terms&condition_status']) && $invoiceSettings['terms&condition_status'] == 1): ?>
                <div class="text--black fs-12 mb-2 text-center terms-condition-text-wrapper">
                    <?php echo e(translate('Terms & Condition')); ?> :
                    <span id="terms-condition-text">
                        <?php echo e(!empty($invoiceSettings['terms&condition_content']) 
                            ? $invoiceSettings['terms&condition_content'] 
                            : 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,'); ?>

                    </span>
                </div>
                <?php endif; ?>
                <h5 class="text-center pt-1  justify-content-center mb-0">
                    <span class="d-block fw-500"><?php echo e(translate('messages.THANK_YOU')); ?></span>
                </h5>
                <div class="text-center"><?php echo e(translate('for_ordering_food_from')); ?> <?php echo e(\App\CentralLogics\Helpers::get_business_settings('business_name', false)); ?></div>

                <dd class="col-12 border-bottom-dashed my-2"></dd>
                <?php if(isset($invoiceSettings['copyright_text_status']) && $invoiceSettings['copyright_text_status'] == 1): ?>
                <div class="d-block text-center" id="copyright-text">
                    <?php echo e(!empty($invoiceSettings['copyright_text_content']) 
                        ? $invoiceSettings['copyright_text_content'] 
                        : '© ' . translate('2024 StackFood. All right reserved')); ?>

                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/new_invoice.blade.php ENDPATH**/ ?>