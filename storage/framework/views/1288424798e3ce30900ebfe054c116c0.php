<?php
use App\CentralLogics\Helpers;
$max_processing_time = $order->restaurant?explode('-', $order->restaurant['delivery_time'])[0]:0;
?>



    <?php if($order->scheduled == 1): ?>
        <?php $__env->startSection('scheduled'); ?>
    <?php elseif( in_array($order->order_status, ['confirmed','processing','handover']) ): ?>
        <?php $__env->startSection('processing'); ?>
    <?php elseif( in_array($order->order_status, ['refunded','refund_requested','refund_request_canceled']) ): ?>
        <?php $__env->startSection('refunded'); ?>
    <?php else: ?>
        <?php $__env->startSection($order->order_status); ?>
    <?php endif; ?>

    active
    <?php $__env->stopSection(); ?>

<?php $__env->startSection('title', translate('Order_Details')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <link rel="stylesheet" type="text/css" href="<?php echo e(dynamicAsset('assets/admin/css/daterangepicker.css')); ?>"/>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php $campaign_order = isset($order->details[0]->campaign) ? true : false;
        $subscription = isset($order->subscription_id) ? true : false;
        $reasons=\App\Models\OrderCancelReason::where('status', 1)->where('user_type' ,'admin' )->get();
    ?>

    <div id="content-disable" class="content container-fluid initial-39">
        <!-- Page Header -->
        <div class="page-header d-print-none">

            <h1 class="page-header-title text-capitalize">
                <div class="card-header-icon d-inline-flex mr-2 img">
                    <img src="<?php echo e(dynamicAsset('assets/admin/img/orders.png')); ?>" alt="public">
                </div>
                <span>
                    <?php echo e(translate('messages.order_details')); ?>

                </span>
                <div class="d-flex ml-auto">
                    <a class="btn btn-icon btn-sm badge-soft-primary rounded-circle justify-content-center mr-1"
                        href="<?php echo e(route('admin.order.details', [$order['id'] - 1])); ?>" data-toggle="tooltip"
                        data-placement="top" title="<?php echo e(translate('Previous_order')); ?>">
                        <i class="tio-chevron-left m-0"></i>
                    </a>
                    <a class="btn btn-icon btn-sm badge-soft-primary rounded-circle justify-content-center"
                        href="<?php echo e(route('admin.order.details', [$order['id'] + 1])); ?>" data-toggle="tooltip"
                        data-placement="top" title="<?php echo e(translate('Next_order')); ?>">
                        <i class="tio-chevron-right m-0"></i>
                    </a>
                </div>
            </h1>

            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <div class="mt-2">
                        <?php
                        $refund_amount = $order->order_amount - $order->delivery_charge - $order->dm_tips;
                        $refund= Helpers::get_business_data('refund_active_status');
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row g-1" id="printableArea">
            <div class="col-lg-8 order-print-area-left">
                <!-- Card -->
                <div class="card mb-3 mb-lg-5">
                    <!-- Header -->
                    <div class="card-header border-0 align-items-start flex-wrap">
                        <div class="order-invoice-left">
                            <h1 class="page-header-title flex-wrap mt-2">
                                <span class="font--max-sm"><?php echo e(translate('messages.order_id_#')); ?><?php echo e($order['id']); ?></span>
                                <!-- Static -->
                                <?php if($order->edited): ?>
                                    <span class="badge badge-soft-danger text-capitalize my-2 ml-2">
                                        <?php echo e(translate('messages.edited')); ?>

                                    </span>
                                <?php endif; ?>

                                <?php if($order->orderEditLogs && count($order->orderEditLogs) > 0): ?>

                                <a href="#0" class="w-sm-auto w-100 fs-12 font-medium hover-info ml-2 offcanvas-trigger" data-target="#offcanvas__history_log">(<?php echo e(translate('Edit History Log')); ?>)</a>
                                <?php endif; ?>
                                <!-- Static -->
                                <div class="d-sm-none d-flex flex-wrap ml-auto align-items-center justify-content-end initial-39-2">
                                    <?php if(!$subscription && !$campaign_order  && $order->payment_method == 'cash_on_delivery'  &&  in_array($order->order_status, ['pending', 'confirmed', 'processing', 'accepted']) && $order->restaurant): ?>
                                    <button class="btn bn--primary btn-outline-primary m-1 print--btn "data-toggle="modal" data-target="#edit-order" type="button">
                                        <i class="tio-edit m-0"></i>
                                        <span><?php echo e(translate('messages.edit_Item')); ?></span>
                                    </button>
                                    <?php endif; ?>
                                    <a target="_blank" class="btn btn-primary m-1 print--btn" href=<?php echo e(route('admin.order.generate-invoice', [$order['id']])); ?>>
                                        <i class="tio-print mr-0"></i> <span><?php echo e(translate('messages.print_invoice')); ?></span>
                                    </a>
                                </div>
                            </h1>
                            <span class="mt-2 d-block text--title">
                                <?php echo e(translate('messages.Placed_Date')); ?> :
                                <span class="font-semibold">
                                    <?php echo e(date('d M Y ' . config('timeformat'), strtotime($order['created_at']))); ?></span>
                            </span>
                            <?php if($order->order_type == 'dine_in'): ?>
                            <span class="mt-2 d-block text--title">
                                <?php echo e(translate('messages.Din-in_Date')); ?> :
                                <span class="font-semibold">
                                    <?php echo e(date('d M Y ' . config('timeformat'), strtotime($order['schedule_at']))); ?>

                                </span>
                            </span>
                            <?php endif; ?>
                            <?php if($subscription): ?>
                            <span>
                                <strong class="text-primary"> <?php echo e(translate('messages.subscription_order')); ?></strong>
                            </span>
                            <br>
                            <?php endif; ?>
                            <?php if($order->schedule_at && ($order->scheduled || $subscription)): ?>
                                <span>
                                    <span><?php echo e(translate('messages.scheduled_at')); ?> :</span>
                                    <strong class="text-warning">
                                        <?php echo e(date('d M Y ' . config('timeformat'), strtotime($order['schedule_at']))); ?>

                                    </strong>
                                </span>
                            <?php endif; ?>
                            <?php if(!empty($eta)): ?>
                                <span class="mt-2 d-block text--title">
                                    <?php echo e(translate('messages.estimated_delivery')); ?> :
                                    <span class="font-semibold">
                                        <?php echo e(date('d M Y ' . config('timeformat'), strtotime($eta['from_at']))); ?>

                                        <?php if($eta['to_at'] !== $eta['from_at']): ?>
                                            <?php
                                                // An overdue window can close on a later date than it opened, and a
                                                // bare clock time then reads as though it ends before it starts.
                                                $eta_same_day = date('Y-m-d', strtotime($eta['from_at'])) === date('Y-m-d', strtotime($eta['to_at']));
                                            ?>
                                            - <?php echo e(date(($eta_same_day ? '' : 'd M Y ') . config('timeformat'), strtotime($eta['to_at']))); ?>

                                        <?php endif; ?>
                                    </span>
                                    <small class="text-muted">(<?php echo e($eta['text']); ?>)</small>
                                </span>
                            <?php endif; ?>
                            <?php if(isset($order->restaurant)): ?>
                                <h6 class="mt-2 pt-1 mb-2">
                                    <i class="tio-shop"></i>
                                    <?php echo e(translate('messages.restaurant')); ?> : <label
                                        class="badge badge-soft-info font-regular m-0"><?php echo e(Str::limit($order->restaurant->name, 25, '...')); ?></label>
                                        <?php echo $__env->make('partials._verified_restaurant_badge', ['restaurant' => $order->restaurant], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                                </h6>
                            <?php else: ?>
                                <h6 class="mt-2 pt-1 mb-2">
                                    <i class="tio-shop"></i>
                                    <?php echo e(translate('messages.restaurant')); ?> : <label
                                        class="badge badge-soft-danger font-regular m-0"><?php echo e(Str::limit(translate('messages.Restaurant_deleted!'), 25, '...')); ?></label>
                                </h6>
                            <?php endif; ?>
                                <h6 class="m-0">
                            <?php if($campaign_order): ?>
                                    <span class="badge badge-soft-primary ml-sm-3">
                                        <?php echo e(translate('messages.campaign_order')); ?>

                                    </span>
                            <?php endif; ?>
                                </h6>
                                <div class="hs-unfold mt-2">
                                    <button class="btn order--details-btn-sm btn--primary btn-outline-primary btn--sm" data-toggle="modal" data-target="#locationModal"><i
                                            class="tio-poi-outlined"></i> <span class="ml-1"><?php echo e(translate('messages.show_locations_on_map')); ?></span> </button>

                                </div>
                            <div class="order--note mt-3">

                            <?php if($order['order_note']): ?>
                            <span  class="bg--F6F6F6 rounded py-1 px-2 fs-12 mt-3 d-flex align-items-center gap-1 font-regular m-0"><strong
                                    class="text-dark font-semibold"><?php echo e(translate('messages.Note:')); ?></strong> <?php echo e($order['order_note']); ?></span>
                            <?php endif; ?>


                                <?php if($order?->offline_payments && $order?->offline_payments->status == 'denied' && $order?->offline_payments->note ): ?>
                                <h6 class="w-100 badge-soft-warning">
                                    <span class="text-dark">
                                        <?php echo e(translate('messages.Offline_payment_rejection_note')); ?> :
                                    </span>
                                        <?php echo e($order?->offline_payments->note); ?>

                                    </h6>
                                <?php endif; ?>
                                <?php if($order['unavailable_item_note']): ?>
                                <h6 class="my-2 ml-2">
                                    <span class="text--title">
                                        <?php echo e(translate('messages.if_item_is_not_available')); ?> :
                                    </span>
                                    <?php echo e(translate($order->unavailable_item_note)); ?>

                                </h6>
                                <?php endif; ?>
                                <?php if($order['delivery_instruction']): ?>
                                    <h6 class="my-2 ml-2">
                                        <span class="text--title">
                                            <?php echo e(translate('messages.order_delivery_instruction')); ?> :
                                        </span>
                                        <?php echo e(translate($order->delivery_instruction)); ?>

                                    </h6>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="order-invoice-right">
                            <div class="d-none d-sm-flex flex-wrap ml-auto align-items-center justify-content-end initial-39-1">
                                <?php if(!$subscription && !$campaign_order && $order->payment_method == 'cash_on_delivery'  &&  in_array($order->order_status, ['pending', 'confirmed', 'processing', 'accepted']) && $order->restaurant): ?>
                                    <button class="btn bn--primary btn-outline-primary m-2 print--btn  "
                                       data-toggle="modal" data-target="#edit-order"type="button">
                                        <i class="tio-edit"></i>
                                        <span><?php echo e(translate('messages.edit_Item')); ?></span>
                                    </button>
                                <?php endif; ?>
                                <a target="_blank" class="btn btn-primary m-2 print--btn" href=<?php echo e(route('admin.order.generate-invoice', [$order['id']])); ?>>
                                    <i class="tio-print mr-0"></i> <span><?php echo e(translate('messages.print_invoice')); ?></span>
                                </a>
                            </div>
                            <div class="text-right mt-3 order-invoice-right-contents text-capitalize">
                                <h6>
                                    <span><?php echo e(translate('messages.order_type')); ?> :</span>
                                    <strong><?php echo e(translate(str_replace('_', ' ', $order['order_type']))); ?></strong>
                                </h6>

                                <?php if(isset($order->subscription)): ?>
                                        <h6>
                                            <span><?php echo e(translate('messages.Subscription_status')); ?> :</span>
                                                <?php if($order->subscription->status == 'active'): ?>
                                                <span class="badge badge-soft-success ">
                                                <span class="legend-indicator bg-success"></span><?php echo e(translate('messages.'.$order->subscription->status)); ?>

                                                </span>
                                                <?php elseif($order->subscription->status == 'paused'): ?>
                                                <span class="badge badge-soft-primary">
                                                <span class="legend-indicator bg-danger"></span><?php echo e(translate('messages.'.$order->subscription->status)); ?>

                                                </span>
                                                <?php else: ?>
                                                <span class="badge badge-soft-primary ">
                                                <span class="legend-indicator bg-info"></span><?php echo e(translate('messages.'.$order->subscription->status)); ?>

                                                </span>
                                                <?php endif; ?>
                                        </h6>
                                <?php endif; ?>

                                <h6>
                                    <?php if(isset($order->subscription) && $order->subscription->status != 'canceled' ): ?>
                                        <?php
                                        $order->order_status = $order->subscription_log ? $order->subscription_log->order_status : $order->order_status;
                                        ?>
                                    <?php endif; ?>

                                    <span><?php echo e(translate('messages.Order_Status')); ?> :</span>
                                    <?php if($order['order_status'] == 'pending'): ?>
                                        <span class="badge badge-soft-info ml-2 ml-sm-3 text-capitalize">
                                            <?php echo e(translate('messages.pending')); ?>

                                        </span>
                                    <?php elseif($order['order_status'] == 'confirmed'): ?>
                                        <span class="badge badge-soft-success ml-2 ml-sm-3 text-capitalize">
                                            <?php echo e(translate('messages.confirmed')); ?>

                                        </span>
                                    <?php elseif($order['order_status'] == 'processing'): ?>
                                        <span class="badge badge-soft-warning ml-2 ml-sm-3 text-capitalize">
                                            <?php echo e(translate('messages.processing')); ?>

                                        </span>
                                    <?php elseif($order['order_status'] == 'picked_up'): ?>
                                        <span class="badge badge-soft-warning ml-2 ml-sm-3 text-capitalize">
                                            <?php echo e(translate('messages.out_for_delivery')); ?>

                                        </span>
                                    <?php elseif($order['order_status'] == 'delivered'): ?>
                                        <span class="badge badge-soft-success ml-2 ml-sm-3 text-capitalize">
                                            <?php echo e($order?->order_type == 'dine_in' ? translate('messages.Completed') : translate('messages.delivered')); ?>


                                        </span>
                                    <?php elseif($order['order_status'] == 'failed'): ?>
                                        <span class="badge badge-soft-danger ml-2 ml-sm-3 text-capitalize">
                                            <?php echo e(translate('messages.payment_failed')); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-soft-danger ml-2 ml-sm-3 text-capitalize">
                                            <?php echo e(translate($order['order_status'])); ?>

                                        </span>
                                    <?php endif; ?>



                                </h6>
                                <?php
                                    $deliveryTypes = [
                                        'express' => ['class' => 'badge-soft-warning', 'label' => 'messages.Express'],
                                        'standard' => ['class' => 'badge-soft-info', 'label' => 'messages.Standard'],
                                        'slightly_delay' => ['class' => 'badge-soft-secondary', 'label' => 'messages.Slightly_Delay'],
                                    ];

                                    // Only when the order actually carries a saver option. Falling back to
                                    // 'standard' badged every order with a delivery type - take-away and
                                    // dine-in included, and every app order, where a standard delivery is
                                    // deliberately stored as null because it is the absence of an option.
                                    $type = $deliveryTypes[$order->delivery_type] ?? null;
                                ?>
                                    <?php if($type): ?>

                                <h6>
                                    <span><?php echo e(translate('messages.Delivery_Type')); ?> :</span>
                                        <span class="badge <?php echo e($type['class']); ?> ml-2 ml-sm-3 text-capitalize">
                                            <?php echo e(translate($type['label'])); ?>

                                        </span>
                                </h6>
                                    <?php endif; ?>

                                <h6>
                                    <span><?php echo e(translate('messages.payment_method')); ?> :</span>
                                    <strong><?php echo e(translate(str_replace('_', ' ', $order['payment_method']))); ?></strong>
                                </h6>
                                <h6>
                                    <span><?php echo e(translate('messages.payment_status')); ?> :</span>
                                    <?php if($order['payment_status'] == 'paid'): ?>
                                    <strong class="text-success"><?php echo e(translate('messages.paid')); ?></strong>
                                    <?php elseif($order['payment_status'] == 'partially_paid'): ?>

                                        <?php if($order->payments()->where('payment_status','unpaid')->exists()): ?>
                                        <strong class="text-danger"><?php echo e(translate('messages.partially_paid')); ?></strong>
                                        <?php else: ?>
                                        <strong class="text-success"><?php echo e(translate('messages.paid')); ?></strong>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <strong class="text-danger"><?php echo e(translate('messages.unpaid')); ?></strong>
                                    <?php endif; ?>
                                </h6>


                                    <!-- offline_payment -->
                                    <?php if($order?->offline_payments): ?>
                                        <span><?php echo e(translate('Payment_verification')); ?></span> <span>:</span>
                                        <?php if($order?->offline_payments->status == 'pending'): ?>
                                            <span class="badge badge-soft-info ml-2 ml-sm-3 text-capitalize">
                                                <?php echo e(translate('messages.pending')); ?>

                                            </span>
                                        <?php elseif($order?->offline_payments->status == 'verified'): ?>
                                            <span class="badge badge-soft-success ml-2 ml-sm-3 text-capitalize">
                                                <?php echo e(translate('messages.verified')); ?>

                                            </span>
                                        <?php elseif($order?->offline_payments->status == 'denied'): ?>
                                            <span class="badge badge-soft-danger ml-2 ml-sm-3 text-capitalize">
                                                <?php echo e(translate('messages.denied')); ?>

                                            </span>
                                        <?php endif; ?>

                                        <?php $__currentLoopData = json_decode($order->offline_payments->payment_info); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($key != 'method_id'): ?>
                                                <h6 class="">
                                                    <div class="d-flex justify-content-sm-end text-capitalize">
                                                        <span class="title-color"><?php echo e(translate($key)); ?> :</span>
                                                        <strong><?php echo e($item); ?></strong>
                                                    </div>
                                                </h6>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                <h6>
                                    <?php if($order['transaction_reference'] == null): ?>
                                        <span><?php echo e(translate('messages.reference_code')); ?> :</span>
                                        <?php if(isset($order->restaurant)): ?>
                                            <button class="btn btn-outline-primary btn--primary btn-sm add--referal" data-toggle="modal"
                                                data-target=".bd-example-modal-sm">
                                                <?php echo e(translate('messages.add')); ?>

                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span><?php echo e(translate('messages.reference_code')); ?> :</span>
                                        <strong><?php echo e($order['transaction_reference']); ?></strong>
                                    <?php endif; ?>
                                </h6>
                                <?php if($order->coupon): ?>
                                    <h6>
                                        <span><?php echo e(translate('messages.coupon')); ?></span>
                                        <label class="text-info"><?php echo e($order->coupon_code); ?>

                                            (<?php echo e(translate('messages.' . $order->coupon->coupon_type)); ?>)</label>
                                    </h6>
                                <?php endif; ?>

                                <?php if($order->cutlery): ?>
                                <h6>
                                    <span><?php echo e(translate('cutlery')); ?></span> <span>:</span>
                                        <span class="badge badge-soft-success ml-sm-3">
                                            <?php echo e(translate('messages.yes')); ?>

                                        </span>
                                </h6>
                                <?php else: ?>
                                <h6>
                                    <span><?php echo e(translate('cutlery')); ?></span> <span>:</span>
                                        <span class="badge badge-soft-danger ml-sm-3">
                                            <?php echo e(translate('messages.No')); ?>

                                        </span>
                                </h6>

                                <?php endif; ?>
                            </div>
                        </div>
                    </div>


                    <!-- Body -->
                    <div class="card-body pb-0 px-0">
                            <?php
                                $total_addon_price = 0;
                                $product_price = 0;
                                $details = $order->details;
                                // A bundle is stored as one row per item. Shown back flat it
                                // reads as separate foods, so the group is collapsed into one
                                // line and its items are drawn inside it.
                                $bogo_groups = Helpers::bogo_order_groups($details);
                        $displaySl = 0;
                                $has_addons = false;
                                foreach ($details as $detail) {
                                    if (isset($detail['add_ons'])) {
                                        $addon_data = json_decode($detail['add_ons'], true);
                                        if (is_array($addon_data) && count($addon_data) > 0) {
                                            $has_addons = true;
                                            break;
                                        }
                                    }
                                }
                            ?>
                    <div class="table-responsive">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table dataTable no-footer mb-0">
                            <thead class="thead-light">
                                <th><?php echo e(translate('sl')); ?></th>
                                <th><?php echo e(translate('item_details')); ?></th>
                                <?php if(isset($has_addons) && $has_addons): ?>
                                <th><?php echo e(translate('addons')); ?></th>
                                <?php endif; ?>
                                <th class="text-right"><?php echo e(translate('price')); ?></th>
                            </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <?php if($detail->bogo_group_id) continue; ?>
                                        <?php if(isset($detail->food_id) ): ?>
                                            <?php

                                            $deleted_food = $detail->food == null?1:0;
                                            $detail->food = json_decode($detail->food_details, true);
                                            $food = \App\Models\Food::where(['id' => $detail?->food['id']])->with('storage')->select('id','image')->first();
                                            ?>
                                            <!-- Media -->
                                            <tr>
                                                <td>
                                                    <!-- Static Count Number -->
                                                    <div>
                                                        <?php ($displaySl++); ?><?php echo e($displaySl); ?>

                                                    </div>
                                                    <!-- Static Count Number -->
                                                </td>
                                                <td>
                                                    <div class="media media--sm">
                                                            <?php if(!$deleted_food): ?>
                                                                <a class="avatar avatar-xl mr-3"
                                                                href="<?php echo e(route('admin.food.view', $detail->food['id'])); ?>">
                                                                    <img class="img-fluid rounded onerror-image"
                                                                        src="<?php echo e($food['image_full_url'] ?? dynamicAsset('assets/admin/img/100x100/food-default-image.png')); ?>"
                                                                        data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/100x100/food-default-image.png')); ?>"
                                                                        alt="Image Description">
                                                                    </a>
                                                            <?php else: ?>
                                                                <div class="avatar avatar-xl mr-3">
                                                                    <img class="img-fluid rounded"
                                                                        src="<?php echo e(dynamicAsset('assets/admin/img/100x100/food-default-image.png')); ?>"
                                                                        alt="Image Description">
                                                                    </div>
                                                            <?php endif; ?>
                                                        <div class="media-body">
                                                            <div>
                                                                <strong class="line--limit-1"> <?php echo e($detail->food == null?'Not Found':$detail->food['name']); ?></strong>
                                                                <?php if(isset($detail['variation']) ? json_decode($detail['variation'], true) : [] ): ?>
                                                                    <?php $__currentLoopData = json_decode($detail['variation'],true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <?php if(isset($variation['name'])  && isset($variation['values'])): ?>
                                                                                <span class="d-block text-capitalize">
                                                                                    <strong>
                                                                                        <?php echo e($variation['name']); ?> -
                                                                                    </strong>
                                                                                </span>
                                                                                <?php $__currentLoopData = $variation['values']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                        <span class="d-block text-capitalize">
                                                                                            &nbsp;   &nbsp; <?php echo e($value['label']); ?> :
                                                                                            <strong><?php echo e(Helpers::format_currency( $value['optionPrice'])); ?></strong>
                                                                                        </span>
                                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                        <?php else: ?>
                                                                                    <?php if(isset(json_decode($detail['variation'],true)[0])): ?>
                                                                                        <strong><u> <?php echo e(translate('messages.Variation')); ?> : </u></strong>
                                                                                        <?php $__currentLoopData = json_decode($detail['variation'],true)[0]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key1 =>$variation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                            <div class="font-size-sm text-body">
                                                                                                <span><?php echo e($key1); ?> :  </span>
                                                                                                <span class="font-weight-bold"><?php echo e($variation); ?></span>
                                                                                            </div>
                                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                                    <?php endif; ?>
                                                                        <?php endif; ?>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                <?php endif; ?>

                                                                <h6>
                                                                    <?php echo e(translate('qty')); ?> : <?php echo e($detail['quantity']); ?>

                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <?php if(isset($has_addons) && $has_addons): ?>
                                                <td>
                                                    <div>
                                                        <?php $__currentLoopData = json_decode($detail['add_ons'], true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key2 => $addon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <div class="font-size-sm text-body">
                                                                <span><?php echo e(Str::limit($addon['name'], 20, '...')); ?> : </span>
                                                                <span class="font-weight-bold">
                                                                    <?php echo e($addon['quantity']); ?> x
                                                                    <?php echo e(Helpers::format_currency($addon['price'])); ?>

                                                                </span>

                                                            </div>
                                                            <?php ($total_addon_price += $addon['price'] * $addon['quantity']); ?>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>
                                                </td>
                                                <?php endif; ?>
                                                <td class="text-right">
                                                    <div>
                                                        <?php ($amount = $detail['price'] * $detail['quantity']); ?>
                                                        <h5><?php echo e(Helpers::format_currency($amount)); ?></h5>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php ($product_price += $amount); ?>

                                        <?php elseif(isset($detail->item_campaign_id)): ?>
                                            <?php
                                            $deleted_food = $detail->campaign == null?1:0;
                                            $detail->campaign = json_decode($detail->food_details, true);
                                            $campaign = \App\Models\ItemCampaign::where(['id' => $detail->campaign['id']])->select('id','image')->first();
                                            ?>

                                            <tr>
                                                <td>
                                                    <div>
                                                        <?php ($displaySl++); ?><?php echo e($displaySl); ?>

                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="media media--sm">
                                                            <?php if(!$deleted_food): ?>
                                                                <a class="avatar avatar-xl mr-3"
                                                                href="<?php echo e(route('admin.campaign.view', ['item', $detail->campaign['id']])); ?>">
                                                                <img class="img-fluid rounded onerror-image"
                                                                    src="<?php echo e($campaign['image_full_url'] ?? dynamicAsset('assets/admin/img/100x100/food-default-image.png')); ?>"
                                                                    data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/100x100/food-default-image.png')); ?>"
                                                                    alt="Image Description">
                                                                </a>
                                                            <?php else: ?>
                                                                <div class="avatar avatar-xl mr-3">
                                                                    <img class="img-fluid"
                                                                        src="<?php echo e(dynamicAsset('assets/admin/img/100x100/food-default-image.png')); ?>"
                                                                        alt="Image Description">
                                                                </div>
                                                            <?php endif; ?>
                                                        <div class="media-body">
                                                            <div>
                                                                <strong class="line--limit-1"> <?php echo e($detail->campaign == null?'Not Found':$detail->campaign['name']); ?></strong>
                                                                <?php if(count(json_decode($detail['variation'], true)) > 0): ?>
                                                                <?php $__currentLoopData = json_decode($detail['variation'],true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <?php if( isset($variation['name'])  && isset($variation['values'])): ?>
                                                                    <span class="d-block text-capitalize">
                                                                            <strong>
                                                                        <?php echo e($variation['name']); ?> -
                                                                            </strong>
                                                                    </span>
                                                                    <?php $__currentLoopData = $variation['values']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <span class="d-block text-capitalize">
                                                                        &nbsp;   &nbsp; <?php echo e($value['label']); ?> :
                                                                        <strong><?php echo e(Helpers::format_currency( $value['optionPrice'])); ?></strong>
                                                                        </span>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                <?php else: ?>
                                                                    <?php if(isset(json_decode($detail['variation'],true)[0])): ?>
                                                                    <strong><u> <?php echo e(translate('messages.Variation')); ?> : </u></strong>
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
                                                            <?php endif; ?>
                                                                <h6>
                                                                    <?php echo e($detail['quantity']); ?> x <?php echo e(Helpers::format_currency($detail['price'])); ?>

                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <?php if(isset($has_addons) && $has_addons): ?>
                                                <td>
                                                    <div>
                                                        <?php $__currentLoopData = json_decode($detail['add_ons'], true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key2 => $addon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <?php if($key2 == 0): ?>
                                                                <strong><u><?php echo e(translate('messages.addons')); ?> : </u></strong>
                                                            <?php endif; ?>
                                                            <div class="font-size-sm text-body">
                                                                <span class="font-weight-bold">
                                                                    <?php echo e($addon['quantity']); ?> x
                                                                    <?php echo e(Helpers::format_currency($addon['price'])); ?>

                                                                </span>
                                                                <span><?php echo e(Str::limit($addon['name'], 20, '...')); ?> : </span>
                                                            </div>
                                                            <?php ($total_addon_price += $addon['price'] * $addon['quantity']); ?>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>
                                                </td>
                                                <?php endif; ?>
                                                <td class="text-right">
                                                    <div>
                                                        <?php ($amount = $detail['price'] * $detail['quantity']); ?>
                                                        <h5><?php echo e(Helpers::format_currency($amount)); ?></h5>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php ($product_price += $amount); ?>
                                            <!-- End Media -->
                                        <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td>
                                                    <?php echo e(translate('Food_was_deleted')); ?>


                                                </td>
                                            </tr>
                                    <?php endif; ?>

                                    
                                    <?php $__currentLoopData = $bogo_groups['bundles']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bogoKey => $bogo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php echo $__env->make('partials._bogo-order-row', [
                                            'bogo' => $bogo,
                                            'sl' => ++$displaySl,
                                            'has_addons' => $has_addons,
                                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        <?php ($product_price += $bogo['total']); ?>
                                        <?php ($total_addon_price += $bogo['addon_total']); ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                        </table>
                    </div>
                    <?php echo $__env->make('partials._bogo-order-modals', ['bogo_groups' => $bogo_groups], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <hr class="mt-0">

                        <?php
                        $total_tax_amount = $order['total_tax_amount'];
                        if($order->tax_status == 'included'){
                            $total_tax_amount = 0;
                        }
                        ?>

                        <div class="row md-row-reverse justify-content-between align-items-center px-4 mb-3">
                            <div class="col-md-6 col-lg-5">

                            </div>
                            <div class="col-md-6 col-lg-5">
                                <dl class="row">
                                    <dt class="col-6 text-capitalize"><?php echo e(translate('messages.items_price')); ?>:</dt>
                                    <dd class="col-6 text-right">
                                        <?php echo e(Helpers::format_currency($product_price)); ?></dd>
                                    <dt class="col-6"><?php echo e(translate('messages.addon_cost')); ?>:</dt>
                                    <dd class="col-6 text-right">
                                        <?php echo e(Helpers::format_currency($total_addon_price)); ?>


                                    </dd>

                                    <dt class="col-6"><?php echo e(translate('messages.subtotal')); ?>

                                    <?php if($order->tax_status == 'included'): ?>
                                    (<?php echo e(translate('messages.TAX_Included')); ?>)
                                    <?php endif; ?>
                                        :</dt>
                                    <dd class="col-6 text-right">
                                        <?php echo e(Helpers::format_currency($product_price + $total_addon_price)); ?>

                                    </dd>
                                    <dt class="col-6"><?php echo e(translate('messages.discount')); ?>:</dt>
                                    <dd class="col-6 text-right">
                                        - <?php echo e(Helpers::format_currency($order['restaurant_discount_amount'])); ?>

                                    </dd>
                                    <dt class="col-6"><?php echo e(translate('messages.coupon_discount')); ?>

                                        <?php if($order->orderProDiscount && $order->orderProDiscount->benefit_type === 'coupon'): ?>
                                            <i class="tio-info fs-14 text-8797AB" data-toggle="tooltip"
                                               title="<?php echo e(translate('Pro Customer coupon applied.')); ?>"></i>
                                        <?php endif; ?>
                                        :</dt>
                                    <dd class="col-6 text-right">
                                        - <?php echo e(Helpers::format_currency($order['coupon_discount_amount'])); ?>

                                    </dd>

                                    <?php if($order['ref_bonus_amount'] > 0): ?>
                                    <dt class="col-6"><?php echo e(translate('messages.Referral_Discount')); ?>:</dt>
                                    <dd class="col-6 text-right">
                                        - <?php echo e(Helpers::format_currency($order['ref_bonus_amount'])); ?>

                                    </dd>
                                    <?php endif; ?>

                                    <?php if(($order->orderProDiscount?->amount_saved ?? 0) > 0): ?>
                                    <dt class="col-6"><?php echo e(translate('messages.Pro_Discount')); ?>:</dt>
                                    <dd class="col-6 text-right">
                                        - <?php echo e(Helpers::format_currency($order->orderProDiscount->amount_saved)); ?>

                                    </dd>
                                    <?php endif; ?>


                                    <?php if($order->tax_status == 'excluded' || $order->tax_status == null  ): ?>
                                    <dt class="col-6"><?php echo e(translate('messages.vat/tax')); ?>:</dt>
                                    <dd class="col-6 text-right">
                                        +
                                        <?php echo e(Helpers::format_currency($total_tax_amount)); ?>

                                    </dd>
                                    <?php endif; ?>
                                    <?php if($order['dm_tips'] > 0): ?>
                                    <dt class="col-6"><?php echo e(translate('DM_Tips')); ?></dt>
                                    <dd class="col-6 text-right">
                                        + <?php echo e(Helpers::format_currency($order['dm_tips'])); ?></dd>
                                    <?php endif; ?>

                                    <dt class="col-6">
                                        <?php echo e(translate('Delivery_Fee')); ?>

                                        <?php if(
                                            $order->orderProDiscount
                                            && $order->orderProDiscount->benefit_type === 'delivery_fee'
                                            && ($order->orderProDiscount->delivery_fee_reduction_amount ?? 0) > 0
                                        ): ?>
                                            <?php if($order->orderProDiscount->delivery_offer_type === 'full_free'): ?>
                                                <i class="tio-info fs-14 text-8797AB" data-toggle="tooltip"
                                                   title="<?php echo e(translate('messages.Pro_Customer_free_delivery_applied')); ?>"></i>
                                            <?php else: ?>
                                                <i class="tio-info fs-14 text-8797AB" data-toggle="tooltip"
                                                   title="<?php echo e(translate('messages.Pro_Customer_partial_delivery_discount_applied')); ?> (<?php echo e((float) ($order->orderProDiscount->delivery_charge_discount_percentage ?? 0)); ?>%)"></i>
                                            <?php endif; ?>
                                        <?php elseif($order->free_delivery_by == 'admin'): ?>
                                            <i class="tio-info fs-14 text-8797AB" data-toggle="tooltip" title="<?php echo e(translate('Delivery fee is applicable and will be covered by the admin.')); ?>"></i>
                                        <?php elseif($order->free_delivery_by == 'vendor'): ?>
                                            <i class="tio-info fs-14 text-8797AB" data-toggle="tooltip" title="<?php echo e(translate('Delivery fee is applicable and will be covered by the Restaurant.')); ?>"></i>
                                        <?php endif; ?>
                                        <?php if($order['delivery_type_charge']==0): ?>
                                            <hr>
                                        <?php endif; ?>
                                    </dt>
                                    <dd class="col-6 text-right">
                                        + <?php echo e(Helpers::format_currency($order['delivery_charge'])); ?>

                                        <?php if($order['delivery_type_charge']==0): ?>
                                            <hr>
                                        <?php endif; ?>
                                    </dd>
                                    <?php if($order['delivery_type_charge'] > 0): ?>
                                        <dt class="col-6">
                                        <?php echo e(translate($order['delivery_type'].'_delivery')); ?>

                                        <?php if($order['additional_charge']==0): ?>
                                            <hr>
                                        <?php endif; ?>
                                    </dt>
                                    <dd class="col-6 text-right">
                                        <?php echo e($order['delivery_type'] == 'slightly_delay' ? '-' : '+'); ?> <?php echo e(Helpers::format_currency($order['delivery_type_charge'])); ?>

                                        <?php if($order['additional_charge']==0): ?>
                                            <hr>
                                        <?php endif; ?>
                                    </dd>

                                    <?php endif; ?>
                                        <?php if($order['additional_charge'] > 0): ?>
                                            <dt class="col-6">
                                                <?php echo e(Helpers::get_business_data('additional_charge_name')??translate('messages.additional_charge')); ?>

                                            </dt>
                                            <dd class="col-6 text-right">
                                                + <?php echo e(Helpers::format_currency($order['additional_charge'])); ?>

                                            </dd>
                                            <?php endif; ?>



                                        <?php if($order['extra_packaging_amount'] > 0): ?>
                                        <dt class="col-6"><?php echo e(translate('messages.Extra_Packaging_Amount')); ?>:</dt>
                                        <dd class="col-6 text-right">
                                            + <?php echo e(Helpers::format_currency($order['extra_packaging_amount'])); ?>

                                        </dd>
                                        <?php endif; ?>



                                    <?php if($order?->payments): ?>
                                        <?php $__currentLoopData = $order?->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($payment->payment_status == 'paid'): ?>
                                                <?php if( $payment->payment_method == 'cash_on_delivery'): ?>

                                                <dt class="col-6"><?php echo e(translate('messages.Paid_with_Cash')); ?> (<?php echo e(translate('COD')); ?>) :</dt>
                                                <?php else: ?>

                                                <dt class="col-6"><?php echo e(translate('messages.Paid_by')); ?> <?php echo e(translate($payment->payment_method)); ?> :</dt>
                                                <?php endif; ?>
                                            <?php else: ?>

                                            <dt class="col-6"><?php echo e(translate('Due_Amount')); ?> (<?php echo e($payment->payment_method == 'cash_on_delivery' ?  translate('messages.COD') : translate($payment->payment_method)); ?>) :</dt>
                                            <?php endif; ?>
                                        <dd class="col-6 text-right">
                                            <?php echo e(Helpers::format_currency($payment->amount)); ?>

                                        </dd>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </dl>
                            </div>


                        </div>

                        <div id="stickyDiv" class="bg-white pb-1 mt-n25 py-2 mb-3 <?php echo e($order->bring_change_amount > 0 ? 'position-sticky bottom-0' : ''); ?>">
                            <div class="row  md-row-reverse justify-content-between align-items-center px-4">
                                <div class="col-md-6 col-lg-5">
                                    <?php if($order->bring_change_amount > 0): ?>
                                        <div class="alert fs-12 p-2 alert-primary-light text-dark mb-0 mt-3 mt-md-0 row" role="alert">
                                            <?php echo e(translate('Please instruct the delivery man to collect '.Helpers::format_currency($order->bring_change_amount).' in change upon delivery')); ?>

                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 col-lg-5">
                                    <dl class="row align-items-center font-weight-bold text--title">
                                        <dd class="col-6 mb-0"><?php echo e(translate('messages.total')); ?>:</dd>
                                        <dt class="col-6 text-right">
                                            <?php echo e(Helpers::format_currency($order->order_amount)); ?>

                                        </dt>
                                    </dl>
                                </div>
                            </div>
                        </div>



                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 order-print-area-right">
                <?php if($order->order_status == 'canceled'): ?>
                <div  class="col-12">
                    <div class="card ">
                        <div class="card-body pt-2">
                            <ul class="delivery--information-single mt-3">
                                <li>
                                    <span class=" badge badge-soft-danger "> <?php echo e(translate('messages.Cancel_Reason')); ?> :</span>
                                    <span class="info">  <?php echo e($order->cancellation_reason); ?> </span>
                                </li>
                                <hr class="w-100">
                                <li>
                                    <span class="name"><?php echo e(translate('Cancel_Note')); ?> </span>
                                    <span class="info">  <?php echo e($order->cancellation_note ?? translate('messages.N/A')); ?> </span>
                                </li>
                                <li>
                                    <span class="name"><?php echo e(translate('Canceled_By')); ?> </span>
                                    <span class="info">  <?php echo e(translate($order->canceled_by)); ?> </span>
                                </li>
                                <?php if($order->payment_status == 'paid' || $order->payment_status == 'partially_paid' ): ?>
                                        <?php if( $order?->payments): ?>
                                            <?php ( $pay_infos =$order->payments()->where('payment_status','paid')->get()); ?>
                                            <?php $__currentLoopData = $pay_infos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pay_info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li>
                                                    <span class="name"><?php echo e(translate('Amount_paid_by')); ?> <?php echo e(translate($pay_info->payment_method)); ?> </span>
                                                    <span class="info">  <?php echo e(Helpers::format_currency($pay_info->amount)); ?> </span>
                                                </li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php else: ?>
                                        <li>
                                            <span class="name"><?php echo e(translate('Amount_paid_by')); ?> <?php echo e(translate($order->payment_method)); ?> </span>
                                            <span class="info ">  <?php echo e(Helpers::format_currency($order->order_amount)); ?> </span>
                                        </li>
                                        <?php endif; ?>
                                <?php endif; ?>

                                <?php if($order->payment_status == 'paid' || $order->payment_status == 'partially_paid'): ?>
                                    <?php if( $order?->payments): ?>
                                        <?php ( $amount =$order->payments()->where('payment_status','paid')->sum('amount')); ?>
                                            <li>
                                                <span class="name"><?php echo e(translate('Amount_Returned_To_Wallet')); ?> </span>
                                                <span class="info">  <?php echo e(Helpers::format_currency($amount)); ?> </span>
                                            </li>
                                    <?php else: ?>
                                    <li>
                                        <span class="name"><?php echo e(translate('Amount_Returned_To_Wallet')); ?> </span>
                                        <span class="info">  <?php echo e(Helpers::format_currency($order->order_amount)); ?> </span>
                                    </li>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>



                <?php if(!empty($order->refund)): ?>
                    <?php if( $order->order_status == 'refund_requested' || $order->order_status == 'refunded' || $order->order_status == 'refund_request_canceled'): ?>
                   <div class="col-12">
                        <div class="card mb-2">
                            <div class="card-header border-0 d-block text-center pb-0">
                                <h4 class="m-0" ><?php echo e(translate('messages.Refund_Request')); ?> </h4>
                                <span>
                                    <?php echo e(date('d M Y ' . config('timeformat'), strtotime($order->refund->created_at))); ?>

                                </span>

                                <?php if($order->order_status == 'refund_requested'): ?>
                                    <span class="badge __badge badge-primary __badge-abs"><?php echo e(translate('messages.pending')); ?></span>
                                    <?php elseif($order->order_status == 'refunded'): ?>
                                    <span class="badge __badge badge-info __badge-abs"><?php echo e(translate('messages.refunded')); ?></span>
                                    <?php elseif($order->refund->order_status == 'refund_request_canceled'): ?>
                                    <span class="badge __badge-pill badge-danger __badge-abs"><?php echo e(translate('messages.rejected')); ?></span>
                                <?php endif; ?>

                            </div>
                            <div class="card-body pt-2">

                                <label class="input-label" for="exampleFormControlInput1"><?php echo e(translate('messages.image')); ?> : </label>
                                <div class="row g-3">
                                    <?php ( $data=  (isset($order->refund->image)) ? json_decode($order->refund->image,true)  : 0 ); ?>
                                    <?php if($data): ?>
                                        <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php ($img = is_array($img)?$img:['img'=>$img,'storage'=>'public']); ?>
                                        <div class="col-3">
                                            <img class="img__aspect-1 rounded border w-100 onerror-image"
                                                data-toggle="modal"
                                                data-target="#imagemodal<?php echo e($key); ?>"
                                                data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/160x160/img2.jpg')); ?>"
                                                src="<?php echo e(Helpers::get_full_url('refund',$img['img'],$img['storage'])); ?>">
                                    </div>
                                        <div class="modal fade" id="imagemodal<?php echo e($key); ?>" tabindex="-1" role="dialog"
                                        aria-labelledby="myModalLabel<?php echo e($key); ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="myModalLabel<?php echo e($key); ?>">
                                                        <?php echo e(translate('Refund Image')); ?></h4>
                                                    <button type="button" class="close" data-dismiss="modal"><span
                                                            aria-hidden="true">&times;</span><span
                                                            class="sr-only"><?php echo e(translate('messages.cancel')); ?></span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <img src="<?php echo e(Helpers::get_full_url('refund',$img['img'],$img['storage'])); ?>"
                                                        class="initial--22 w-100">
                                                </div>
                                                <?php ($storage = $img['storage']??'public'); ?>
                                                <?php ($file = $storage == 's3'?base64_encode('refund/' . $img['img']):base64_encode('public/refund/' . $img['img'])); ?>
                                                <div class="modal-footer">
                                                    <a class="btn btn-primary"
                                                        href="<?php echo e(route('admin.file-manager.download', [$file,$storage])); ?>"><i
                                                            class="tio-download"></i> <?php echo e(translate('messages.download')); ?>

                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php else: ?>
                                        <div class="col-3">
                                            <img class="img__aspect-1 rounded border w-100"
                                            src="<?php echo e(dynamicAsset('assets/admin/img/160x160/img2.jpg')); ?>">
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <hr>


                                <ul class="delivery--information-single mt-3">
                                    <li>
                                        <span class="name"><?php echo e(translate('Reason')); ?> </span>
                                        <span class="info">  <?php echo e($order->refund->customer_reason); ?> </span>
                                    </li>
                                    <li>
                                        <span class="name"><?php echo e(translate('amount')); ?> </span>
                                        <span class="info"> <?php echo e($order->refund->refund_amount); ?></span>
                                    </li>
                                    <li>
                                        <span class="name"><?php echo e(translate('Method')); ?> </span>
                                        <span class="info"> <?php echo e($order->refund->refund_method); ?></span>
                                    </li>
                                    <li>
                                        <span class="name"> <?php echo e(translate('Status')); ?> </span>
                                        <span class="info"> <?php echo e($order->refund->refund_status); ?></span>
                                    </li>
                                    <?php if(isset($order->refund->admin_note)): ?>
                                    <li>
                                        <span class="name">   <?php echo e(translate('Admin_Note')); ?> </span>
                                        <span class="info">  <?php echo e($order->refund->admin_note ?? 'No Note'); ?></span>
                                    </li>
                                    <?php endif; ?>
                                    <?php if(isset($order->refund->customer_note)): ?>
                                    <li>
                                        <span class="name">  <?php echo e(translate('Customer_Note')); ?> </span>
                                        <span class="info">   <?php echo e($order->refund->customer_note ?? 'No Note'); ?></span>
                                    </li>
                                    <?php endif; ?>
                                    <hr class="w-100">
                                </ul>

                                <div class="btn--container refund--btn">
                                    <?php if($refund  == true &&  $order->order_status == 'refund_requested'): ?>
                                    <button type="button" class="btn btn--danger btn-outline-danger" data-toggle="modal" data-target="#refund_cancelation_note">
                                    <i class="tio-money"></i> <span class="ml-1"><?php echo e(translate('messages.Cancel_Refund')); ?></span> </button>
                                    <?php endif; ?>
                                <?php if( $refund  == true &&  $order->payment_status == 'paid' && $order->order_status != 'refunded'  ): ?>
                                        <?php if($order->order_status == 'refunded' ): ?>

                                        <?php else: ?>
                                        <button class="btn btn--primary btn--sm route-alert"
                                        data-url="<?php echo e(route('admin.order.status', ['id' => $order['id'],
                                        'order_status' => 'refunded'])); ?>" data-message="<?php echo e(translate('messages.you_want_to_refund_this_order', ['amount' => $refund_amount . ' ' . Helpers::currency_code()])); ?>" data-title="<?php echo e(translate('messages.are_you_sure_want_to_refund')); ?>"><i
                                            class="tio-money"></i> <span class="ml-1"><?php echo e(translate('messages.Refund')); ?></span> </button>

                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="">
                    <?php if(isset($order->restaurant)): ?>
                    <div class="col-12 ">
                        <?php if($order?->offline_payments && !in_array($order->order_status, ['canceled'])): ?>
                            <div class="card mb-2">
                                <div class="card-body">
                                    <div class="card border-info text-center  mb-2">
                                        <div class="card-body">
                                            <h2>
                                                <?php if($order?->offline_payments->status == 'verified'): ?>
                                                <?php echo e(translate('Payment_Verified')); ?>

                                                <?php elseif($order?->offline_payments->status == 'denied'): ?>

                                                <?php echo e(translate('Payment_Verification_Denied')); ?>

                                                <?php else: ?>
                                                <?php echo e(translate('Payment_Verification_Needed ')); ?>

                                                <?php endif; ?>
                                            </h2>

                                            <?php if($order?->offline_payments->status == 'pending'): ?>
                                                <p class="text-danger"> <?php echo e(translate('Order_amount_is_paid_via_offline_payment_method._Please_Verify_the_payment_before_confirm_order.')); ?></p>
                                                <div class="btn--container justify-content-center">
                                                    <button  type="button" class="btn btn--primary btn-sm" data-toggle="modal" data-target="#verifyViewModal" ><?php echo e(translate('messages.Verify_Payment')); ?></button>
                                                </div>
                                            <?php elseif($order?->offline_payments->status == 'verified'): ?>
                                            <p class="text-success"> <?php echo e(translate('Payment_is_verified_by_admin.')); ?></p>
                                                <div class="btn--container justify-content-center">
                                                    <button  type="button" class="btn btn--primary btn-sm" data-toggle="modal" data-target="#verifyViewModal" ><?php echo e(translate('messages.Payment_Details')); ?></button>
                                                </div>
                                            <?php elseif($order?->offline_payments->status == 'denied'): ?>
                                                <p class="text-danger"> <?php echo e(translate('Please_take_actions_before_confirming_the_order.')); ?></p>

                                                <div class="btn--container justify-content-center">
                                                    <button  type="button" class="btn btn--danger btn-sm" data-toggle="modal" data-target="#verifyViewModal" ><?php echo e(translate('messages.Recheck')); ?></button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php if($order->offline_payments == null  || ($order?->offline_payments && $order?->offline_payments->status == 'verified')): ?>
                        <!-- Card -->
                        <?php if(empty($order->refund)): ?>
                        <?php if(!in_array($order['order_status'], ['delivered','take_away','refund_requested','canceled','refunded','refund_request_canceled']) ): ?>

                        <div class="card mb-2">
                            <!-- Header -->
                                <div class="card-header border-0 justify-content-center pt-4 pb-0">
                                    <h4 class="card-header-title"><?php echo e(translate('order_Setup')); ?></h4>
                                </div>
                                <!-- End Header -->
                                <!-- Body -->
                                <div class="card-body">
                                    <label class="form-label"><?php echo e(translate('change_order_status')); ?></label>
                                    <!-- Unfold -->
                                    <div>
                                        <div class="dropdown">
                                            <?php if(isset($order->restaurant)): ?>
                                                <button class="form-control h--45px dropdown-toggle d-flex justify-content-between align-items-center" type="button"
                                                    id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">

                                                    <?php
                                                        $message= match($order['order_status']){
                                                                        'pending' => translate('messages.pending'),
                                                                        'confirmed' => translate('messages.confirmed'),
                                                                        'accepted' => translate('messages.accepted'),
                                                                        'processing' => translate('messages.processing'),
                                                                        'handover' => translate('messages.handover'),
                                                                        'picked_up' => translate('messages.out_for_delivery'),
                                                                        'delivered' => $order['order_type'] == 'dine_in' ? translate('messages.Completed') : translate('messages.delivered')                                                                                    ,
                                                                        'canceled' => translate('messages.canceled'),
                                                                        default => translate('messages.status') ,
                                                                    };
                                                    ?>
                                                    <?php echo e($message); ?>

                                                </button>
                                            <?php endif; ?>
                                                <?php ($order_delivery_verification = (bool) Helpers::get_business_data('order_delivery_verification') ); ?>
                                                <div class="dropdown-menu text-capitalize" aria-labelledby="dropdownMenuButton">
                                                    <a class="dropdown-item route-alert <?php echo e($order['order_status'] == 'pending' ? 'active' : ''); ?>"
                                                        data-url="<?php echo e(route('admin.order.status', ['id' => $order['id'], 'order_status' => 'pending'])); ?>" data-message="<?php echo e(translate('Change_status_to_pending_?')); ?>"
                                                        href="javascript:"><?php echo e(translate('messages.pending')); ?></a>
                                                    <a class="dropdown-item route-alert <?php echo e($order['order_status'] == 'confirmed' ? 'active' : ''); ?>"
                                                        data-url="<?php echo e(route('admin.order.status', ['id' => $order['id'], 'order_status' => 'confirmed'])); ?>" data-message="<?php echo e(translate('Change_status_to_confirmed_?')); ?>"
                                                        href="javascript:"><?php echo e(translate('messages.confirmed')); ?></a>

                                                    <a class="dropdown-item route-alert <?php echo e($order['order_status'] == 'processing' ? 'active' : ''); ?>"
                                                        data-url="<?php echo e(route('admin.order.status', ['id' => $order['id'], 'order_status' => 'processing'])); ?>" data-message="<?php echo e(translate('Change_status_to_processing_?')); ?>" data-title="<?php echo e(translate('Are_you_sure?')); ?>" data-processing="<?php echo e($max_processing_time); ?>"
                                                        href="javascript:">
                                                        <?php echo e(translate('messages.processing')); ?></a>

                                                    <a class="dropdown-item route-alert <?php echo e($order['order_status'] == 'handover' ? 'active' : ''); ?>"
                                                        data-url="<?php echo e(route('admin.order.status', ['id' => $order['id'], 'order_status' => 'handover'])); ?>" data-message="<?php echo e(translate('Change_status_to_handover_?')); ?>"
                                                        href="javascript:"><?php echo e(translate('messages.handover')); ?></a>
                                                    <?php if($order['order_type'] == 'delivery'): ?>
                                                    <a class="dropdown-item route-alert <?php echo e($order['order_status'] == 'picked_up' ? 'active' : ''); ?>"
                                                        data-url="<?php echo e(route('admin.order.status', ['id' => $order['id'], 'order_status' => 'picked_up'])); ?>" data-message="<?php echo e(translate('Change_status_to_out_for_delivery_?')); ?>"
                                                        href="javascript:"><?php echo e(translate('messages.out_for_delivery')); ?></a>
                                                    <?php endif; ?>

                                                    <a class="dropdown-item route-alert <?php echo e($order['order_status'] == 'delivered' ? 'active' : ''); ?>"
                                                        data-url="<?php echo e(route('admin.order.status', ['id' => $order['id'], 'order_status' => 'delivered'])); ?>" data-message="<?php echo e(translate('Change_status_to_delivered_(payment_status_will_be_paid_if_not)_?')); ?>"
                                                        href="javascript:"> <?php echo e($order?->order_type == 'dine_in' ? translate('messages.Completed') : translate('messages.delivered')); ?>

                                                    </a>
                                                    <a class="dropdown-item cancelled_status <?php echo e($order['order_status'] == 'canceled' ? 'active' : ''); ?>"
                                                        ><?php echo e(translate('messages.canceled')); ?></a>
                                                </div>
                                        </div>
                                    </div>

                                    <!-- End Unfold -->
                                    <!-- Static -->
                                    <?php if(!in_array($order['order_type'],['dine_in','take_away']) && !$order->delivery_man &&
                                        (isset($order->restaurant) &&   ($order->restaurant->restaurant_model == 'commission'
                                        && !$order->restaurant->self_delivery_system ) ||  ($order->restaurant->restaurant_model == 'subscription'
                                        && isset($order->restaurant->restaurant_sub) && $order->restaurant->restaurant_sub->self_delivery == 0))): ?>
                                        <div class="w-100 text-center mt-4">
                                            <button type="button" class="btn w-100 btn-primary font-regular" data-toggle="modal"
                                                data-target="#myModal" data-lat='21.03' data-lng='105.85'>
                                                <i class="tio-bike"></i> <?php echo e(translate('messages.assign_delivery_man')); ?>

                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <!-- End Body -->
                        </div>
                        <!-- End Card -->
                            <?php if($order->scheduled): ?>
                            <div class="col-12">
                                <div class="card mt-0">
                                    <div class="card-body pt-3">
                                        <div class="mb-3 mt-2 d-flex justify-content-between align-items-center">
                                            <div>
                                                <label class="form-label mb-0 fs-14 font-bold"><?php echo e(translate('Schedule Date')); ?></label>
                                                <div class="m-0 font-normal text-title"><?php echo e(Helpers::time_date_format($order->schedule_at)); ?></div>
                                            </div>
                                            <?php if( in_array($order->order_status, ['pending', 'confirmed', 'processing', 'accepted'])): ?>
                                            <div class="position-relative">
                                                <div id="customTrigger">
                                                    <i class="tio-calendar-month -absolute-on-right text-gray1"></i>
                                                </div>
                                                <input id="hiddenInput" type="text" value="<?php echo e(\Carbon\Carbon::parse($order->schedule_at)->format('m/d/Y h:i A')); ?>" class="border-0 p-0 bg-transparent position-absolute right-0 bottom-0"  name="schedule_at" autocomplete="off">
                                            </div>

                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        <!-- <?php endif; ?> -->
                    </div>
                    <?php endif; ?>

                    <?php if($order->order_type == 'dine_in'): ?>
                    <div class="col-12">
                        <div class="card mt-2 mb-2">
                            <div class="card-body pt-3">
                                <form action="<?php echo e(route('admin.order.add_dine_in_table_number', [$order['id']])); ?>" method="post">
                                    <?php echo method_field('PUT'); ?>
                                    <?php echo csrf_field(); ?>
                                <div class="form-group mb-20">
                                    <label class="form-label"><?php echo e(translate('Table_Number')); ?></label>
                                    <div class="d-flex">
                                        <input type="text"  <?php if(in_array($order['order_status'],['failed','delivered','refund_requested','canceled','refunded','refund_request_canceled'])): echo 'readonly'; endif; ?> maxlength="20" value="<?php echo e($order?->OrderReference?->table_number); ?>"  name="table_number" class="form-control" placeholder="Ex: 10">
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label"><?php echo e(translate('Token_Number')); ?></label>
                                    <div class="d-flex">
                                        <input type="text"  <?php if(in_array($order['order_status'],['failed','delivered','refund_requested','canceled','refunded','refund_request_canceled'])): echo 'readonly'; endif; ?> maxlength="20" value="<?php echo e($order?->OrderReference?->token_number); ?>" name="token_number" class="form-control" placeholder="Ex: 32">
                                    </div>
                                </div>
                                <?php if(!in_array($order['order_status'],['failed','delivered','refund_requested','canceled','refunded','refund_request_canceled'])): ?>
                                <div class="w-100 text-center mt-4">
                                    <button type="submit" class="btn w-100 btn-primary font-regular"><?php echo e(translate('messages.Save')); ?>

                                    </button>
                                </div>
                                <?php endif; ?>
                            </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if($order->delivery_man && $order->delivery_man->type == 'zone_wise'): ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body pt-2">
                                <div class="mb-3 mt-2 d-flex justify-content-between align-items-center">
                                    <h5 class="card-title">
                                        <span class="card-header-icon">
                                            <i class="tio-user"></i>
                                        </span>
                                        <span>
                                            <?php echo e(translate('deliveryman')); ?>

                                        </span>
                                    </h5>
                                    <?php if( !in_array($order['order_status'], ['delivered','refund_requested','canceled','refunded','refund_request_canceled']) &&  !in_array($order['order_type'],['dine_in','take_away']) &&
                                    (isset($order->restaurant) && ($order->restaurant->restaurant_model == 'commission'
                                            && !$order->restaurant->self_delivery_system ) ||  ($order->restaurant->restaurant_model == 'subscription'
                                                && isset($order->restaurant->restaurant_sub) && $order->restaurant->restaurant_sub->self_delivery == 0))): ?>

                                        <span class="ml-auto text--primary position-relative p-2 cursor-pointer" data-toggle="modal" data-target="#myModal">
                                            <?php echo e(translate('messages.change')); ?>

                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="w-100 text-right initial-39-4">
                                </div>
                                <a class="media align-items-center  deco-none customer--information-single"
                                    href="<?php echo e(route('admin.delivery-man.preview', [$order->delivery_man['id']])); ?>">
                                    <div class="avatar avatar-circle">
                                        <img class="avatar-img w-75px onerror-image"
                                                src="<?php echo e($order?->delivery_man?->image_full_url ?? dynamicAsset('assets/admin/img/160x160/img3.png')); ?>"
                                                alt="Image Description"
                                                data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/160x160/img3.png')); ?>">
                                    </div>
                                    <div class="media-body">
                                        <strong class="d-block text--title">
                                            <?php echo e($order->delivery_man['f_name'] . ' ' . $order->delivery_man['l_name']); ?>

                                        </strong>
                                        <span>
                                            <strong class="text--title font-semibold">
                                                <?php echo e($order->delivery_man->orders_count); ?>

                                            </strong>
                                            <?php echo e(translate('messages.orders_delivered')); ?>

                                        </span>
                                        <span class="text--title font-semibold d-block">
                                            <i class="tio-call-talking-quiet"></i> <?php echo e($order->delivery_man['phone']); ?>

                                        </span>
                                        <span class="text--title text-lowercase">
                                            <i class="tio-email"></i> <?php echo e($order->delivery_man['email']); ?>

                                        </span>
                                    </div>
                                </a>
                                <hr>
                                <?php ($address = $order->dm_last_location); ?>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5><?php echo e(translate('messages.last_location')); ?></h5>
                                </div>
                                <?php if(isset($address)): ?>
                                    <span class="d-block">
                                        <a target="_blank"
                                            href="http://maps.google.com/maps?z=12&t=m&q=loc:<?php echo e($address['latitude']); ?>+<?php echo e($address['longitude']); ?>">
                                            <i class="tio-poi"></i> <?php echo e($address['location']); ?><br>
                                        </a>
                                    </span>
                                <?php else: ?>
                                    <span class="d-block text-lowercase qcont">
                                        <?php echo e(translate('messages.location_not_found')); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="col-12 mt-2">
                    <!-- Customer Card -->
                        <div class="card">
                            <div class="card-body pt-3">
                                <!-- Header -->
                                <h5 class="card-title mb-3">
                                    <span class="card-header-icon">
                                        <i class="tio-user"></i>
                                    </span>
                                    <span><?php echo e(translate('messages.customer_info')); ?></span>
                                </h5>
                                <!-- End Header -->
                                    <?php if($order?->customer && $order->is_guest == 0): ?>
                                        <a class="media align-items-center deco-none customer--information-single"
                                            href="<?php echo e(route('admin.customer.view', [$order?->customer['id']])); ?>">
                                            <div class="avatar avatar-circle position-relative">
                                                <img class="avatar-img onerror-image"
                                                        src="<?php echo e($order?->customer?->image_full_url ?? dynamicAsset('assets/admin/img/160x160/img1.png')); ?>"
                                                        alt="Image Description"
                                                        data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/160x160/img1.png')); ?>">
                                                <?php if($order?->customer?->pro_status): ?>
                                                    <?php echo $__env->make('partials._pro_customer_badge', ['position' => '', 'style' => 'top: 5px !important;'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="media-body">
                                                <span
                                                    class="fz--14px text--title font-semibold text-hover-primary d-block">
                                                    <?php echo e($order?->customer['f_name'] . ' ' . $order?->customer['l_name']); ?>

                                                </span>
                                                <span>
                                                    <strong class="text--title font-semibold"><?php echo e($order?->customer->orders_count); ?></strong>
                                                    <?php echo e(translate('messages.orders')); ?>

                                                </span>
                                                <span class="text--title font-semibold d-block">
                                                    <i class="tio-call-talking-quiet"></i> <?php echo e($order?->customer['phone']); ?>

                                                </span>
                                                <span class="text--title">
                                                    <i class="tio-email"></i> <?php echo e($order?->customer['email']); ?>

                                                </span>
                                            </div>

                                        </a>
                                    <?php elseif($order->is_guest): ?>
                                        <span class="badge badge-soft-success py-2 d-block qcont">
                                            <?php echo e(translate('Guest_user')); ?>

                                        </span>

                                    <?php else: ?>
                                        <?php echo e(translate('messages.customer_not_found')); ?>

                                    <?php endif; ?>
                                    <?php if($order->delivery_address): ?>
                                        <div class="pt-2"></div>
                                            <hr>
                                            <?php ($address = json_decode($order->delivery_address, true)); ?>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="card-title">
                                                    <span class="card-header-icon">
                                                        <i class="tio-user"></i>
                                                    </span>
                                                    <span><?php echo e($order->order_type == 'dine_in' ? translate('Order_Info') : translate('delivery_info')); ?></span>
                                                </h5>
                                                <?php if(isset($address) && isset($order->restaurant) && !in_array($order['order_type'],['dine_in','take_away'])  && in_array($order->order_status, ['pending', 'confirmed', 'processing', 'accepted']) ): ?>
                                                    <a class="link" data-toggle="modal" data-target="#shipping-address-modal" href="javascript:"><i class="tio-edit"></i></a>
                                                <?php endif; ?>
                                            </div>
                                            <?php if(isset($address)): ?>
                                                <span class="delivery--information-single mt-3">
                                                    <span class="name"><?php echo e(translate('name')); ?></span>
                                                    <span class="info"><?php echo e($address['contact_person_name']); ?></span>
                                                    <span class="name"><?php echo e(translate('contact')); ?></span>
                                                    <a class="deco-none info" href="tel:<?php echo e($address['contact_person_number']); ?>">
                                                        <i class="tio-call-talking-quiet"></i>
                                                        <?php echo e($address['contact_person_number']); ?></a>


                                                        <?php if( !in_array($order->order_type, ['dine_in', 'take_away'])): ?>
                                                            <span class="name"><?php echo e(translate('Road')); ?> #</span>
                                                            <span class="info"><?php echo e(isset($address['road']) ? $address['road'] : ''); ?></span>
                                                            <span class="name"><?php echo e(translate('House')); ?> #</span>
                                                            <span class="info">
                                                                <?php echo e(isset($address['house']) ? $address['house'] : ''); ?>

                                                            </span>
                                                            <span class="name"><?php echo e(translate('Floor')); ?></span>
                                                            <span class="info"><?php echo e(isset($address['floor']) ? $address['floor'] : ''); ?></span>

                                                            <?php if(isset($address['address'])): ?>
                                                                <?php if(empty($address['longitude']) && empty($address['latitude']) && is_numeric($address['latitude'] ?? null) && is_numeric($address['longitude'] ?? null)): ?>
                                                                    <div class="mt-2 d-flex w-100">
                                                                        <a target="_blank"
                                                                            href="http://maps.google.com/maps?z=12&t=m&q=loc:<?php echo e($address['latitude']); ?>+<?php echo e($address['longitude']); ?>">
                                                                            <span><i class="tio-poi text--title"></i></span>
                                                                            <span class="info pl-2"><?php echo e($address['address']); ?></span>
                                                                        </a>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <div class="mt-2 d-flex w-100">
                                                                        <span><i class="tio-poi text--title"></i></span>
                                                                        <span class="info pl-2"><?php echo e($address['address']); ?></span>
                                                                    </div>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                </span>
                                            <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            <!-- End Body -->
                        </div>
                    </div>

                    <?php if($order->order_type != 'dine_in'): ?>
                    <!-- order proof -->
                    <div  class="col-12 mt-2">
                        <div class="card ">
                            <div class="card-header d-flex border-0 text-center justify-content-between pb-0">
                                <h4 class="m-0">
                                    <span class="card-header-icon">
                                        <i class="tio-user"></i>
                                    </span>
                                    <span><?php echo e(translate('messages.Delivery_Proof')); ?></span>
                                </h4>

                                    <button class="btn align-items-center btn-primary btn-sm" data-toggle="modal" data-target=".order-proof-modal">
                                                        <?php echo e(translate('messages.add')); ?> </button>
                            </div>
                            <?php ($data = isset($order->order_proof) ? json_decode($order->order_proof, true) : 0); ?>

                            <div class="card-body pt-2">
                                <?php if($data): ?>
                                    <label class="input-label"for="order_proof"><?php echo e(translate('messages.image')); ?> : </label>
                                    <div class="row g-3">
                                            <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php ($img = is_array($img)?$img:['img'=>$img,'storage'=>'public']); ?>
                                                <div class="col-3">
                                                        <img class="img__aspect-1 rounded border w-100" data-toggle="modal"  data-target="#imagemodal<?php echo e($key); ?>"
                                                        src="<?php echo e(Helpers::get_full_url('order',$img['img'],$img['storage'])); ?>"
                                                        alt="image">

                                                </div>
                                                <div class="modal fade" id="imagemodal<?php echo e($key); ?>" tabindex="-1"
                                                    role="dialog" aria-labelledby="order_proof_<?php echo e($key); ?>"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h4 class="modal-title"
                                                                    id="order_proof_<?php echo e($key); ?>">
                                                                    <?php echo e(translate('order_proof_image')); ?></h4>
                                                                <button type="button" class="close"
                                                                    data-dismiss="modal"><span
                                                                        aria-hidden="true">&times;</span><span
                                                                        class="sr-only"><?php echo e(translate('messages.cancel')); ?></span></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <img src="<?php echo e(Helpers::get_full_url('order',$img['img'],$img['storage'])); ?>"
                                                                    class="initial--22 w-100">
                                                            </div>
                                                            <?php ($storage = $img['storage'] ?? 'public'); ?>
                                                            <?php ($file = $storage == 's3'?base64_encode('order/' . $img['img']):base64_encode('public/order/' . $img['img'])); ?>
                                                            <div class="modal-footer">
                                                                <a class="btn btn-primary"
                                                                    href="<?php echo e(route('admin.file-manager.download', [$file,$storage])); ?>"><i
                                                                        class="tio-download"></i>
                                                                    <?php echo e(translate('messages.download')); ?>

                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="col-12 mt-2">
                        <!-- End Card -->
                        <?php if($order->restaurant): ?>
                            <!-- Restaurant Card -->
                            <div class="card">
                                <!-- Body -->
                                <div class="card-body">
                                    <!-- Header -->
                                    <h5 class="card-title mb-3">
                                        <span class="card-header-icon">
                                            <i class="tio-shop"></i>
                                        </span>
                                        <span><?php echo e(translate('messages.restaurant_info')); ?></span>
                                    </h5>
                                    <!-- End Header -->
                                    <a class="media align-items-center deco-none resturant--information-single"
                                        href="<?php echo e(route('admin.restaurant.view', [$order->restaurant['id']])); ?>">
                                        <div class="avatar avatar-circle">
                                                <img class="avatar-img w-75px"
                                                src="<?php echo e($order?->restaurant?->logo_full_url ?? dynamicAsset('assets/admin/img/100x100/restaurant-default-image.png')); ?>"
                                                alt="image">

                                        </div>
                                        <div class="media-body">
                                            <span class="text-body text-hover-primary text-break"></span>
                                            <span></span>


                                            <span class="fz--14px text--title font-semibold text-hover-primary d-block">
                                                <?php echo e($order->restaurant->name); ?>

                                                <?php echo $__env->make('partials._verified_restaurant_badge', ['restaurant' => $order->restaurant], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                                            </span>
                                            <span>
                                                <strong class="text--title font-semibold">
                                                    <?php echo e($order->restaurant->orders_count); ?>

                                                </strong>
                                                <?php echo e(translate('messages.orders_served')); ?>

                                            </span>
                                            <span class="text--title font-semibold d-block">
                                                <i class="tio-call-talking-quiet"></i> <?php echo e($order->restaurant['phone']); ?>

                                            </span>
                                            <span class="text--title">
                                                <i class="tio-poi"></i> <?php echo e($order->restaurant['address']); ?>

                                            </span>
                                        </div>
                                    </a>
                                </div>
                                <!-- End Body -->
                            </div>
                        <?php endif; ?>
                        <!-- End Card -->
                    </div>
                </div>
            </div>
        </div>
        <!-- End Row -->
    </div>

    <!-- Modal -->
    <div class="modal fade" id="refund_cancelation_note" tabindex="-1" role="dialog" aria-labelledby="refund_cancelation_note_l" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="refund_cancelation_note_l"><?php echo e(translate('messages.Add_Order_Rejection_Note')); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?php echo e(route('admin.refund.order_refund_rejection')); ?>" method="post">
                    <?php echo method_field('PUT'); ?>
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                            <input type="hidden" name="order_id" value="<?php echo e($order->id); ?>" >
                    <input type="text" class="form-control" name="admin_note" value="<?php echo e(old('admin_note')); ?>" placeholder="Fake Order">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(translate('messages.Close')); ?> </button>
                        <button type="submit" class="btn btn-primary"> <?php echo e(translate('messages.Submit')); ?> </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4" id="mySmallModalLabel"><?php echo e(translate('messages.reference_code_add')); ?></h5>
                    <button type="button" class="btn btn-xs btn-icon btn-ghost-secondary" data-dismiss="modal"
                        aria-label="Close">
                        <i class="tio-clear tio-lg"></i>
                    </button>
                </div>

                <form action="<?php echo e(route('admin.order.add-payment-ref-code', [$order['id']])); ?>" method="post">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <!-- Input Group -->
                        <div class="form-group">
                            <input type="text" name="transaction_reference" class="form-control"
                                placeholder="<?php echo e(translate('messages.Ex:_Code123')); ?>" required>
                        </div>
                        <!-- End Input Group -->
                        <button class="btn btn-primary"><?php echo e(translate('messages.submit')); ?></button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <!-- End Modal -->

    <!-- Modal -->
    <div id="shipping-address-modal" class="modal fade" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalTopCoverTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <!-- Header -->
                <div class="modal-top-cover bg-dark text-center">
                    <figure class="position-absolute right-0 bottom-0 left-0 mb-n-1">
                        <svg preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                            viewBox="0 0 1920 100.1">
                            <path fill="#fff" d="M0,0c0,0,934.4,93.4,1920,0v100.1H0L0,0z" />
                        </svg>
                    </figure>

                    <div class="modal-close">
                        <button type="button" class="btn btn-icon btn-sm btn-ghost-light" data-dismiss="modal"
                            aria-label="Close">
                            <svg width="16" height="16" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                                <path fill="currentColor"
                                    d="M11.5,9.5l5-5c0.2-0.2,0.2-0.6-0.1-0.9l-1-1c-0.3-0.3-0.7-0.3-0.9-0.1l-5,5l-5-5C4.3,2.3,3.9,2.4,3.6,2.6l-1,1 C2.4,3.9,2.3,4.3,2.5,4.5l5,5l-5,5c-0.2,0.2-0.2,0.6,0.1,0.9l1,1c0.3,0.3,0.7,0.3,0.9,0.1l5-5l5,5c0.2,0.2,0.6,0.2,0.9-0.1l1-1 c0.3-0.3,0.3-0.7,0.1-0.9L11.5,9.5z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <!-- End Header -->

                <div class="modal-top-cover-icon">
                    <span class="icon icon-lg icon-light icon-circle icon-centered shadow-soft">
                        <i class="tio-location-search"></i>
                    </span>
                </div>

                <?php if(isset($address)): ?>
                    <form action="<?php echo e(route('admin.order.update-shipping', [$order['id']])); ?>" method="post">
                        <?php echo csrf_field(); ?>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    <?php echo e(translate('messages.type')); ?>

                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="address_type"
                                        value="<?php echo e(isset($address['address_type']) ? $address['address_type'] : ''); ?>" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    <?php echo e(translate('messages.contact')); ?>

                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="contact_person_number"
                                        value="<?php echo e($address['contact_person_number']); ?>" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    <?php echo e(translate('messages.name')); ?>

                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="contact_person_name"
                                        value="<?php echo e($address['contact_person_name']); ?>" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    <?php echo e(translate('House')); ?>

                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="house"
                                        value="<?php echo e(isset($address['house']) ? $address['house'] : ''); ?>">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    <?php echo e(translate('Floor')); ?>

                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="floor"
                                        value="<?php echo e(isset($address['floor']) ? $address['floor'] : ''); ?>" >
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    <?php echo e(translate('Road')); ?>

                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="road"
                                        value="<?php echo e(isset($address['road']) ? $address['road'] : ''); ?>" >
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    <?php echo e(translate('address')); ?>

                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="address" id="address"
                                        value="<?php echo e($address['address']); ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    <?php echo e(translate('latitude')); ?>

                                </label>
                                <div class="col-md-4 js-form-message">
                                    <input type="text" class="form-control" readonly name="latitude" id="latitude"
                                        value="<?php echo e($address['latitude']); ?>">
                                </div>
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    <?php echo e(translate('messages.longitude')); ?>

                                </label>
                                <div class="col-md-4 js-form-message">
                                    <input type="text" class="form-control" readonly name="longitude" id="longitude"
                                        value="<?php echo e($address['longitude']); ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <input id="pac-input" class="controls rounded initial-8"
                                title="<?php echo e(translate('messages.search_your_location_here')); ?>" type="text"
                                placeholder="<?php echo e(translate('messages.search_here')); ?>" />
                            <div class="mb-2 h-200px" id="map"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-white"
                                data-dismiss="modal"><?php echo e(translate('messages.close')); ?></button>
                            <button type="submit" class="btn btn-primary"><?php echo e(translate('messages.save_changes')); ?></button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- End Modal -->

    <!--Dm assign Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header align-items-start">
                    <div>
                        <h4 class="modal-title" id="myModalLabel"><?php echo e($selected_delivery_man != [] ?  translate('messages.Change_Delivery_Men') :  translate('messages.Available_Delivery_Men')); ?></h4>
                        <p class="fs-12 text--semititle"  ><?php echo e(count($deliveryMen) > 0  ?  count($deliveryMen) - ($order->delivery_man_id ? 1 : 0) : 0); ?>   <?php echo e(translate('Delivery_Man_Available')); ?></p>

                    </div>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="row">
                        <div class="col-md-5 d-flex  justify-content-center">
                            <ul class="list-group overflow-auto max-height-400">
                                <?php if($selected_delivery_man != [] ): ?>

                                <li class="list-group-item">
                                    <div class="d-flex align-items-center gap-2 justify-content-between">
                                            <div class="dm_list_selected media gap-2" data-id="<?php echo e($selected_delivery_man['id']); ?>">
                                                <img class="avatar avatar-60 rounded-10"
                                                src="<?php echo e($selected_delivery_man['image_full_url'] ?? dynamicAsset('assets/admin/img/160x160/img3.png')); ?>"
                                                     alt="Image Description"
                                                     data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/160x160/img1.png')); ?>">
                                                    <div class="media-body d-flex gap-1 flex-column">
                                                        <h6 class="mb-1">  <?php echo e($selected_delivery_man['name']); ?></h6>
                                                        <div class="fs-12 text-muted">
                                                            <?php echo e(translate('Active_Orders')); ?> : <?php echo e($selected_delivery_man['current_orders']); ?>

                                                        </div>

                                                        <div class="fs-12 text-muted">
                                                            <?php echo e($selected_delivery_man['distance']); ?> <?php echo e(translate('away_from_restaurant')); ?>

                                                        </div>
                                                    </div>
                                            </div>
                                            <span class="badge __badge badge-success __badge-abs">
                                                <?php echo e(translate('Currently_Assigned')); ?>

                                            </span>
                                    </div>
                                </li>
                                <?php endif; ?>



                                <?php $__empty_1 = true; $__currentLoopData = $deliveryMen; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php if($dm['id'] != $order->delivery_man_id ): ?>
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center gap-2 justify-content-between">
                                                    <div class="dm_list media gap-2" data-id="<?php echo e($dm['id']); ?>">
                                                        <img class="avatar avatar-60 rounded-10 onerror-image"
                                                             src="<?php echo e($dm['image_full_url'] ?? dynamicAsset('assets/admin/img/160x160/img1.jpg')); ?>"
                                                             alt="<?php echo e($dm['name']); ?>"
                                                             data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/160x160/img1.jpg')); ?>">
                                                            <div class="media-body d-flex gap-1 flex-column">
                                                                <h6 class="mb-1">  <?php echo e($dm['name']); ?></h6>
                                                                <div class="fs-12 text-muted">
                                                                    <?php echo e(translate('Active_Orders')); ?> : <?php echo e($dm['current_orders']); ?>

                                                                </div>

                                                                <div class="fs-12 text-muted">
                                                                    <?php echo e($dm['distance']); ?> <?php echo e(translate('away_from_restaurant')); ?>

                                                                </div>
                                                            </div>
                                                    </div>



                                                    <?php
                                                    $dm_over_flow= false;
                                                    $cash_in_hand = $dm['wallet']?->collected_cash ?? 0;
                                                    $dm_max_cash=Helpers::get_business_data('dm_max_cash_in_hand') ?? 0;
                                                    $cash_in_hand_overflow_delivery_man=Helpers::get_business_data('cash_in_hand_overflow_delivery_man') ?? 0;

                                                    if($order->payment_method == "cash_on_delivery" && $cash_in_hand_overflow_delivery_man && (($cash_in_hand+$order->order_amount) >= $dm_max_cash)){
                                                       $dm_over_flow = true;
                                                    }
                                                    ?>
                                                    <?php if($order->delivery_man_id ): ?>

                                                        <a class="btn  btn-xs float-right  <?php echo e($dm_over_flow == true ? 'btn-secondary disableDM'  : 'btn-primary add-delivery-man'); ?>"
                                                        data-id="<?php echo e($dm['id']); ?>"><?php echo e(translate('messages.Reassign')); ?></a>
                                                    <?php else: ?>
                                                        <a class="btn btn-xs float-right <?php echo e($dm_over_flow == true ? 'btn-secondary disableDM'  : 'btn-primary add-delivery-man'); ?>"
                                                        data-id="<?php echo e($dm['id']); ?>"><?php echo e(translate('messages.assign')); ?></a>
                                                    <?php endif; ?>
                                            </div>
                                        </li>
                                    <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="text-center">
                                        <img src="<?php echo e(dynamicAsset('assets/admin/img/dmimage.png')); ?>" alt="image">

                                        <div class="text-center">
                                            <p><?php echo e(translate('Currently_no_deliveryman_available.')); ?>

                                                <br>
                                                <span class="fz-12px">
                                                    <?php echo e(translate('Please_check_after_some_times_or_reload_the_page.')); ?>

                                                </span>
                                            </p>

                                        </div>
                                    </div>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="col-md-7 modal_body_map">
                            <div class="location-map" id="dmassign-map">
                                <div id="map_canvas"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->

    <!--Show locations on map Modal -->
    <div class="modal fade" id="locationModal" tabindex="-1" role="dialog" aria-labelledby="locationModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="locationModalLabel"><?php echo e(translate('messages.location_data')); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 modal_body_map">
                            <div class="location-map" id="location-map">
                                <div id="location_map_canvas"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->

    <div class="modal quick_modal_init fade" id="quick-view" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" id="quick-view-modal">

            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade order-proof-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4" id="mySmallModalLabel"><?php echo e(translate('messages.add_delivery_proof')); ?></h5>
                    <button type="button" class="btn btn-xs btn-icon btn-ghost-secondary" data-dismiss="modal"
                        aria-label="Close">
                        <i class="tio-clear tio-lg"></i>
                    </button>
                </div>

                <form action="<?php echo e(route('admin.order.add-order-proof', [$order['id']])); ?>" method="post" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <!-- Input Group -->
                        <div class="flex-grow-1 mx-auto">
                            <div class="d-flex flex-wrap __gap-12px __new-coba" id="coba">
                                <?php ($proof = isset($order->order_proof) ? json_decode($order->order_proof, true) : 0); ?>
                                <?php if($proof): ?>

                                <?php $__currentLoopData = $proof; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php ($photo =  is_string($photo) ? ['img' => $photo, 'storage' => 'public'] :  $photo ); ?>
                                            <div class="spartan_item_wrapper min-w-100px max-w-100px">
                                                <img class="img--square"
                                                    src="<?php echo e($order->order_proof_full_url[$key]); ?>"
                                                    alt="order image">
                                                <a href="<?php echo e(route('admin.order.remove-proof-image', ['id' => $order['id'], 'name' => $photo['img']])); ?>"
                                                    class="spartan_remove_row"><i class="tio-add-to-trash"></i></a>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- End Input Group -->
                        <div class="text-right mt-2">
                            <button class="btn btn--primary"><?php echo e(translate('messages.submit')); ?></button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <!-- End Modal -->

    <?php if($order?->offline_payments): ?>
        <div class="modal fade" id="verifyViewModal" tabindex="-1" aria-labelledby="verifyViewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-end  border-0">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true" class="tio-clear"></span>
                        </button>
                    </div>
                <div class="modal-body">
                <div class="d-flex align-items-center flex-column gap-3 text-center">
                    <h2><?php echo e(translate('Payment_Verification')); ?>

                        <?php if($order?->offline_payments->status == 'verified'): ?>
                            <span class="badge badge-soft-success mt-3 mb-3"><?php echo e(translate('messages.verified')); ?></span>
                        <?php endif; ?>
                    </h2>
                    <p class="text-danger mb-2 mt-2"><?php echo e(translate('Please_Check_&_Verify_the_payment_information_weather_it_is_correct_or_not_before_confirm_the_order.')); ?></p>
                </div>

                <div class="card">
                    <div class="card-body">

                        <div class="row">
                            <div class="col-sm-6 col-xl-4">
                                <h4 class="mb-3"><?php echo e(translate('messages.customer_information')); ?></h4>
                                <div class="d-flex flex-column gap-2">
                                    <?php if($order->is_guest): ?>
                                    <?php ($customer_details = json_decode($order['delivery_address'],true)); ?>

                                    <div class="d-flex align-items-center gap-2">
                                        <span><?php echo e(translate('Name')); ?></span>:
                                        <span class="text-dark"> <?php echo e($customer_details['contact_person_name']); ?></span>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        <span><?php echo e(translate('Phone')); ?></span>:
                                        <span class="text-dark">  <?php echo e($customer_details['contact_person_number']); ?></span>
                                    </div>

                                    <?php elseif($order?->customer): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <span><?php echo e(translate('Name')); ?></span>:
                                        <span class="text-dark"> <a class="text-body text-capitalize" href="<?php echo e(route('admin.customer.view',[$order['user_id']])); ?>"> <?php echo e($order?->customer['f_name'].' '.$order?->customer['l_name']); ?>  </a>  </span>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        <span><?php echo e(translate('Phone')); ?></span>:
                                        <span class="text-dark"><?php echo e($order?->customer['phone']); ?>  </span>
                                    </div>

                                    <?php else: ?>
                                        <label class="badge badge-danger"><?php echo e(translate('messages.invalid_customer_data')); ?></label>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-4">
                                <h4 class="mb-3"><?php echo e(translate('messages.Order_Information')); ?></h4>
                                <div class="d-flex flex-column gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span><?php echo e(translate('Order_ID')); ?></span>:
                                        <span class="text-dark"> <?php echo e($order->id); ?></span>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        <span><?php echo e(translate('Order_Time')); ?></span>:
                                        <span class="text-dark"> <?php echo e(Helpers::time_date_format($order->created_at)); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>


                                <div class="mt-5">
                                    <h4 class="mb-3"><?php echo e(translate('messages.Payment_Information')); ?></h4>
                                    <div class="row g-3">
                                        <?php $__currentLoopData = json_decode($order->offline_payments->payment_info); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($key != 'method_id'): ?>
                                            <div class="col-sm-6  col-lg-5">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="w-sm-25"> <?php echo e(translate($key)); ?></span>:
                                                    <span class="text-dark text-break"><?php echo e($item); ?></span>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>

                                    <div class="d-flex flex-column gap-2 mt-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span><?php echo e(translate('Customer_Note')); ?></span>:
                                            <span class="text-dark text-break"><?php echo e($order->offline_payments?->customer_note ?? translate('messages.N/A')); ?> </span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if($order?->offline_payments->status != 'verified'): ?>
                        <div class="btn--container justify-content-end mt-3">
                            <?php if($order?->offline_payments->status != 'denied'): ?>
                            <button type="button" class="btn btn--danger btn-outline-danger offline_payment_cancelation_note" data-toggle="modal" data-target="#offline_payment_cancelation_note" data-id="<?php echo e($order['id']); ?>" class="btn btn--reset"><?php echo e(translate('Payment_Didn’t_Receive')); ?></button>
                            <?php elseif($order?->offline_payments->status == 'denied'): ?>
                            <button type="button" class="btn btn-danger btn-outline-danger  cancelled_status"><?php echo e(translate('Cancel_Order')); ?></button>
                                <button type="button" data-url="<?php echo e(route('admin.order.offline_payment', [ 'id' => $order['id'], 'verify' => 'switched_to_cod', ])); ?>" data-message="<?php echo e(translate('messages.Make_the_payment_verified_for_this_order')); ?>"  class="btn btn-primary btn-outline-primary route-alert"><?php echo e(translate('Continue_with_COD')); ?></button>
                            <?php endif; ?>
                            <button type="button" data-url="<?php echo e(route('admin.order.offline_payment', [ 'id' => $order['id'], 'verify' => 'yes', ])); ?>" data-message="<?php echo e(translate('messages.Make_the_payment_verified_for_this_order')); ?>" class="btn btn-primary  route-alert"><?php echo e(translate('Yes,_Payment_Received')); ?></button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
                <!-- Modal -->
        <div class="modal fade" id="offline_payment_cancelation_note" tabindex="-1" role="dialog"
            aria-labelledby="offline_payment_cancelation_note_l" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="offline_payment_cancelation_note_l"><?php echo e(translate('messages.Add_Offline_Payment_Rejection_Note')); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="<?php echo e(route('admin.order.offline_payment')); ?>" method="get">
                            <input type="hidden" name="id" value="<?php echo e($order->id); ?>">
                            <input type="text" required class="form-control" name="note" value="<?php echo e(old('note')); ?>"
                                placeholder="<?php echo e(translate('transaction_id_mismatched')); ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(translate('close')); ?></button>
                        <button type="submit" class="btn btn--danger btn-outline-danger"><?php echo e(translate('messages.Confirm_Rejection')); ?> </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- Order Edit -->
    <div id="offcanvas__order_edit" class="custom-offcanvas d-flex flex-column justify-content-between" style="--offcanvas-width: 750px">
        <div>
            <form action="<?php echo e(route('admin.order.update', [$order['id']])); ?>" method="post">
                <?php echo csrf_field(); ?>
            <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                <div class="px-3 py-3 d-flex justify-content-between w-100">
                    <div>
                        <h2 class="mb-1"><?php echo e(translate('Edit Item')); ?></h2>
                        <div class="d-flex flex-wrap align-items-center gap-30">
                            <h3 class="page-header-title d-flex align-items-center gap-2">
                                <span class="font--max-sm fs-14"><?php echo e(translate('Order')); ?> #<?php echo e($order['id']); ?></span>
                                <span class="badge badge-soft-info font-regular m-0"><?php echo e(translate($order['order_status'])); ?></span>
                            </h3>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-14 font-regular d-block text-dark"><?php echo e(translate('Order_Placed')); ?> :</span>
                                <span class="fs-14 font-semibold d-block text-dark"> <?php echo e(Helpers::time_date_format($order->created_at)); ?></span>
                            </div>
                        </div>
                    </div>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary location-reload offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;
                    </button>
                </div>
            </div>
            <div class="custom-offcanvas-body p-20">
                <div class="mb-20 position-relative edit-search-form">
                    <div class="form-control position-relative bg-white d-flex align-items-center gap-2">
                        <i class="tio-search"></i>
                        <input id="food_search" data-restaurant_id="<?php echo e($order['restaurant_id']); ?>" data-url="<?php echo e(route('admin.order.getSearchedFoods')); ?>" type="search" name="search" class="h-100 fs-12 bg-transparent w-100 border-0 rounded-0"  placeholder="Search by food name" autocomplete="off">
                        <!--- After Search -->
                       <div id="food_search_result"> </div>
                    </div>
                </div>


                <div id ="data-view">  </div>

            </div>
            <div class="offcanvas-footer position-absolute bottom-0 start-0 w-100 bg-white p-3 d-flex align-items-center justify-content-end gap-3">
                <button type="button"   class="btn min-w-120 btn--reset location-reload reset"><?php echo e(translate('Cancel')); ?></button>
                <button type="submit" class="btn min-w-120 btn--primary"><?php echo e(translate('Update Cart')); ?></button>
            </div>
            </form>
        </div>
    </div>
    <div id="offcanvasOverlay_fixed" class="offcanvasOverlay_fixed"></div>


    <!-- History Log -->
     <div id="offcanvas__history_log" class="custom-offcanvas d-flex flex-column justify-content-between" style="--offcanvas-width: 570px">
        <div>
            <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                <div class="px-3 py-3 d-flex justify-content-between w-100">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h2 class="mb-1 fs-18 font-medium"><?php echo e(translate('Edit History Log')); ?></h2>
                        <h3 class="page-header-title d-flex align-items-center gap-2">
                            <span class="font--max-sm fs-14 font-normal fs-14">(# <?php echo e($order['id']); ?>)</span>
                        </h3>
                    </div>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;
                    </button>
                </div>
            </div>
            <div class="custom-offcanvas-body p-20">
                <div class="p-xl-0">
                    <div class="table-responsive pt-0 mb-20">
                        <div class="p-1">
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table dataTable no-footer mb-0">
                                <thead class="border-0 initial-94">
                                    <tr>
                                        <th class="border-0"><?php echo e(translate('Sl')); ?></th>
                                        <th class="border-0"><?php echo e(translate('Date & time')); ?></th>
                                        <th class="border-0 "><?php echo e(translate('Remark')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $order->orderEditLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <?php echo e($key + 1); ?>

                                            </div>
                                        </td>
                                        <td class="fs-14">
                                            <span class="d-block text-dark"><?php echo e(Helpers::date_format($log->created_at)); ?> </span>
                                            <span class="text-muted"><?php echo e(Helpers::time_format($log->created_at)); ?></span>
                                        </td>
                                        <td class="fs-14">
                                           <?php echo e(translate($log->log)); ?>

                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        <?php echo $__env->make('partials._bogo-order-modals', ['bogo_groups' => $bogo_groups], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>





    <div class="modal fade" id="edit-order">
        <div class="modal-dialog modal-dialog-centered status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div>
                            <div class="text-center">
                                <img src="<?php echo e(dynamicAsset('assets/admin/img/fi_10308693.png')); ?>" alt="" class="mb-20">
                                <h5 class="modal-title text-center text-capitalize"><?php echo e(translate('Are You sure you want to edit this order?')); ?></h5>
                            </div>
                            <div class="text-center">
                                <p><?php echo e(translate('If you edit this order, some product details will be updated, which may affect the total price.')); ?></p>
                            </div>
                        </div>
                        <div class="btn--container justify-content-center">
                            <button id="reset_btn" type="reset"  class="btn btn--secondary min-w-120  " data-dismiss="modal"><?php echo e(translate('No')); ?></button>
                            <button class="btn btn--primary min-w-120 offcanvas-trigger offcanvas-trigger_fixed"  id="edit_order_cart"
                            data-url="<?php echo e(route('admin.order.edit',$order->id)); ?>"

                            data-target="#offcanvas__order_edit" data-dismiss="modal">
                                <?php echo e(translate("Yes")); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="remove-from-cart">
        <div class="modal-dialog modal-dialog-centered status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div>
                            <div class="text-center">
                                <img src="<?php echo e(dynamicAsset('assets/admin/img/fi_10308693.png')); ?>" alt="" class="mb-20">
                                <h5 class="modal-title text-center text-capitalize"><?php echo e(translate('Are you sure you want to delete this Food?')); ?></h5>
                            </div>
                            <div class="text-center">
                                <p><?php echo e(translate('If you delete this food item, it will be removed from the list. You’ll need to add it again by searching in the food list.')); ?></p>
                            </div>
                        </div>

                        <input type="hidden" name="delete_cart_id" id="delete_cart_id" value="" >
                        <div class="btn--container justify-content-center">
                            <button  class="btn btn--secondary min-w-120" data-dismiss="modal"><?php echo e(translate('Cancel')); ?></button>
                            <button class="btn btn--danger min-w-120 removeFromCart"

                            data-dismiss="modal">
                                <?php echo e(translate("Delete")); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo e(Helpers::get_business_data('map_api_key')); ?>&libraries=places,marker&callback=initMap&v=3.61"></script>
    <script src="<?php echo e(dynamicAsset('assets/admin/js/spartan-multi-image-picker.js')); ?>"></script>
    <script type="text/javascript" src="<?php echo e(dynamicAsset('assets/admin/js/moment.min.js')); ?>"></script>
    <script type="text/javascript" src="<?php echo e(dynamicAsset('assets/admin/js/daterangepicker.min.js')); ?>"></script>
    <script>
        $(document).ready(function() {
            $('.offcanvas-trigger_fixed').on('click', function() {
                // Add 'active' class to the overlay
                $('.offcanvasOverlay_fixed').addClass('active');
            });

            // Close functionality
            $('.offcanvas-close').on('click', function() {
                $('.offcanvasOverlay_fixed').removeClass('active');
            });
        });
    </script>
    <script>

        $(document).on('click', '.disable-delete-btn-text', function () {
            toastr.error('<?php echo e(translate('You must have at least one food in cart.You can not delete this food.')); ?>', { CloseButton: true,ProgressBar: true });
        });

        $(document).on('click', '.quick-View', function () {
            let product_id = $(this).data('id');
            $.get({
                url: '<?php echo e(route('admin.order.quick-view')); ?>',
                dataType: 'json',
                data: {
                    product_id: product_id,
                    order_id: '<?php echo e($order->id); ?>',
                },
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    $('#quick-view').modal('show');
                    $('#quick-view-modal').empty().html(data.view);
                },
                complete: function() {
                    $('#loading').hide();
                },
            });
        })
        $(document).on('click', '.remove_from_cart_id', function () {
            var cartId = $(this).data('cart_id');
            $('#delete_cart_id').val(cartId);
        });



        $(document).ready(function () {
            let searchTimeout;

            $('#food_search').on('keyup', function () {
                clearTimeout(searchTimeout);

                let query = $(this).val().trim();
                let restaurantId = $(this).data('restaurant_id');
                let url = $(this).data('url');

                searchTimeout = setTimeout(function () {
                    if (query.length > 0) {
                        $.ajax({
                            url: url,
                            method: 'GET',
                            data: {
                                search: query,
                                restaurant_id: restaurantId
                            },
                            beforeSend: function() {
                                $('#food_search_result').empty();

                            },
                            success: function (response) {
                                $('#food_search_result').append(response.view);
                            }
                        });
                    }
                }, 300);
            });


                document.querySelectorAll('[name="search"]').forEach(function (element) {
                element.addEventListener("input", function (event) {
                        if (this.value === "" ) {
                          $('#food_search_result').empty();
                        }
                    });
                });
        });

        $(document).on('click', '#edit_order_cart', function() {
            // $('#content-disable').addClass('disabled');
            fetch_data($(this).data('url'))
        })

        function fetch_data(url) {
            $.ajax({
                url: url,
                type: "get",
                beforeSend: function() {
                    $('#data-view').empty();
                    $('#loading').show()
                },
                success: function(data) {
                    if(data.error === 'food_not_found'){
                        $('.custom-offcanvas').removeClass('open');
                        $('#offcanvasOverlay').removeClass('show');
                        toastr.error('<?php echo e(translate('messages.product_deleted')); ?>', { CloseButton: true,ProgressBar: true });
                    } else {
                        $("#data-view").append(data.view);

                    }
                },
                complete: function() {
                    $('#loading').hide()
                }
            })
        }

        $(function () {
            const $input = $('#hiddenInput');
         $input.daterangepicker({
            singleDatePicker: true,
            timePicker: true,
            timePicker24Hour: false,
            timePickerSeconds: true,
            autoUpdateInput: false,
            minDate: moment(),
            locale: { format: 'MM/DD/YYYY hh:mm A' },
            parentEl: 'body'
        });

            $input.on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY hh:mm A'));
                $.post('<?php echo e(route("admin.order.updateSchedule")); ?>', {
                    _token: '<?php echo e(csrf_token()); ?>',
                    order_id: '<?php echo e($order->id); ?>',
                    date: picker.startDate.format('YYYY-MM-DD HH:mm:ss')
                }, function (response) {
                    if (response.status === 'success') {
                         toastr.success(response.message);
                          setTimeout(function () {
                            location.reload();
                        }, 800);
                    } else {
                       toastr.error(response.message);
                    }
                });
            });
            $('#customTrigger').on('click', function () {
                $input.data('daterangepicker').show();
            });


            $input.on('show.daterangepicker', function (e, picker) {
                picker.container.addClass('date__custom_width');
            });
        });

         $(document).ready(function () {
            let $stickyDiv = $("#stickyDiv");

            function checkSticky() {
                let rect = $stickyDiv[0].getBoundingClientRect();

                if (rect.bottom >= window.innerHeight && rect.top < window.innerHeight) {
                    $stickyDiv.addClass("total_price_sticky_wrapper");
                } else {
                    $stickyDiv.removeClass("total_price_sticky_wrapper");
                }
            }

            $(window).on("scroll resize", checkSticky);

            if (<?php echo e($order->bring_change_amount > 0 ? 'true' : 'false'); ?>){
                checkSticky();
            }
        });
    </script>

    <script>
        $(document).on('keydown', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

        $(document).on('click', '.addon-quantity-input-toggle', function (event) {
            let cb = $(event.target);
            if (cb.is(":checked")) {
                cb.siblings('.addon-quantity-input').css({
                    'visibility': 'visible'
                });
            } else {
                cb.siblings('.addon-quantity-input').css({
                    'visibility': 'hidden'
                });
            }
        })

        $(document).on('click', '.quick_view_cart_item', function (event) {
            quick_view_cart_item($(this).data('id'), $(this).data('cart_id'));
        })

        function quick_view_cart_item(key, cart_id) {
            $.get({
                url: '<?php echo e(route('admin.order.quick-view-cart-item')); ?>',
                dataType: 'json',
                data: {
                    key: key,
                    cart_id: cart_id,
                    order_id: '<?php echo e($order->id); ?>',
                },
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    $('#quick-view').modal('show');
                    $('#quick-view-modal').empty().html(data.view);
                },
                complete: function() {
                    $('#loading').hide();
                },
            });
        }

        function cartQuantityInitialize() {
            $('.btn-number').click(function(e) {
                e.preventDefault();

                let fieldName = $(this).attr('data-field');
                let type = $(this).attr('data-type');
                let input = $("input[name='" + fieldName + "']");
                let currentVal = parseInt(input.val());

                if (!isNaN(currentVal)) {
                    if (type == 'minus') {

                        if (currentVal > input.attr('min')) {
                            input.val(currentVal - 1).change();
                        }
                        if (parseInt(input.val()) == input.attr('min')) {
                            $(this).attr('disabled', true);
                        }

                    } else if (type == 'plus') {

                        if (currentVal < input.attr('max')) {
                            input.val(currentVal + 1).change();
                        }
                        if (parseInt(input.val()) == input.attr('max')) {
                            $(this).attr('disabled', true);
                        }

                    }
                } else {
                    input.val(0);
                }
            });

            $('.input-number').focusin(function() {
                $(this).data('oldValue', $(this).val());
            });

            $('.input-number').change(function() {

                minValue = parseInt($(this).attr('min'));
                maxValue = parseInt($(this).attr('max'));
                valueCurrent = parseInt($(this).val());

                let name = $(this).attr('name');
                if (valueCurrent >= minValue) {
                    $(".btn-number[data-type='minus'][data-field='" + name + "']").removeAttr('disabled')
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Cart',
                        text: 'Sorry, the minimum value was reached'
                    });
                    $(this).val($(this).data('oldValue'));
                }
                if (valueCurrent <= maxValue) {
                    $(".btn-number[data-type='plus'][data-field='" + name + "']").removeAttr('disabled')
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Cart',
                        text: 'Sorry, stock limit exceeded.'
                    });
                    $(this).val($(this).data('oldValue'));
                }
            });
            $(".input-number").keydown(function(e) {
                // Allow: backspace, delete, tab, escape, enter and .
                if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                    // Allow: Ctrl+A
                    (e.keyCode == 65 && e.ctrlKey === true) ||
                    // Allow: home, end, left, right
                    (e.keyCode >= 35 && e.keyCode <= 39)) {
                    // let it happen, don't do anything
                    return;
                }
                // Ensure that it is a number and stop the keypress
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
        }

        function getVariantPrice() {
            if (typeof getCheckedInputs === 'function') {
                getCheckedInputs();
            }
            if ($('#add-to-cart-form input[name=quantity]').val() > 0) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "POST",
                    url: '<?php echo e(route('admin.pos.variant_price')); ?>',
                    data: $('#add-to-cart-form').serializeArray(),
                    success: function(data) {
                        if (data.error === 'quantity_error') {
                            toastr.error(data.message);
                        }
                        else if(data.error === 'stock_out'){
                            toastr.warning(data.message);
                            if(data.type == 'addon'){
                                $('#addon_quantity_button'+data.id).attr("disabled", true);
                                $('#addon_quantity_input'+data.id).val(data.current_stock);
                            }

                            else{
                                $('#quantity_increase_button').attr("disabled", true);
                                $('#add_new_product_quantity').val(data.current_stock);
                            }
                            getVariantPrice();
                        }

                        else {
                            $('#add-to-cart-form #chosen_price_div').removeClass('d-none');
                            $('#add-to-cart-form #chosen_price_div #chosen_price').html(data.price);
                            $('.add-To-Cart').removeAttr("disabled");
                            $('.increase-button').removeAttr("disabled");
                            $('#quantity_increase_button').removeAttr("disabled");

                        }

                    }
                });
            }
        }

        $(document).on('change', '.update-Quantity', function () {
            handleQuantityChange($(this));
        });

        $(document).on('click', '.decrease-button', function () {
            let addonId = $(this).data('id');
            let addon_quantity_input = $('input[name="addon-quantity' + addonId + '"]');
            let currentValue = parseInt(addon_quantity_input.val(), 10);
            if (currentValue > 1) {
                addon_quantity_input.val(currentValue - 1);
                getVariantPrice();
            }
        });

        $(document).on('click', '.increase-button', function () {
            let addonId = $(this).data('id');
            let addon_quantity_input = $('input[name="addon-quantity' + addonId + '"]');
            let currentValue = parseInt(addon_quantity_input.val(), 10);
            addon_quantity_input.val(currentValue + 1);
            getVariantPrice();
        });

        $(document).on('click', '.decrease-quantity-button', function () {
            let Id = $(this).data('id');
            let quantity_input = $('#update_quantity_' + Id);
            let currentValue = parseInt(quantity_input.val(), 10);
            if (currentValue > parseInt(quantity_input.attr('min'))) {
                quantity_input.val(currentValue - 1);
            }
             quantity_input.trigger('change');
        });

        $(document).on('click', '.increase-quantity-button', function () {
            let Id = $(this).data('id');
            let quantity_input = $('#update_quantity_' + Id);
            let currentValue = parseInt(quantity_input.val(), 10);
            // if (currentValue < parseInt(quantity_input.attr('max'))) {
            // }
            quantity_input.val(currentValue + 1);
            quantity_input.trigger('change');
        });

        function handleQuantityChange(element) {

            let valueCurrent = parseInt(element.val());
            let key = element.data('key');
            let option_ids = element.data('option_ids');
            let variation_options_old = element.data('variation_options_old');
            let addon_price = element.data('addon_price');
            let order_detail_id = element.data('order_detail_id');
            let food_id = element.data('food_id');
            let oldvalue = element.data('value');

                $.post('<?php echo e(route('admin.order.getSingleFoodPrice')); ?>', {
                    _token: '<?php echo e(csrf_token()); ?>',
                    cart_id: key,
                    option_ids: option_ids,
                    food_id: food_id,
                    addon_price: addon_price,
                    variation_options_old: variation_options_old,
                    order_detail_id: order_detail_id,
                    quantity: valueCurrent
                }, function (data) {
                    if (data.data === 'stock_out') {
                        element.val(oldvalue);
                        Swal.fire({
                            icon: 'error',
                            title: "<?php echo e(translate('Cart')); ?>",
                            text: data.message
                        });
                    } else if (data.data === 'maximum_cart_quantity') {
                         element.val(oldvalue);
                         toastr.error(data.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                    else {
                        element.data('value', valueCurrent);
                         toastr.success(data.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        $("#data-view").empty().append(data.view);
                    }
                });

        }

        $(document).on('change', '[name="quantity"]', function (event) {
            getVariantPrice();
            if($('#option_ids').val() == ''){
                $(this).attr('max', $(this).data('maximum_cart_quantity'));
            }
        });

        $(document).on('click', '.add-To-Cart', function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': "<?php echo e(csrf_token()); ?>"
                }
            });
            let form_id = 'add-to-cart-form';
            $.post({
                url: '<?php echo e(route('admin.order.add-to-cart')); ?>',
                data: $('#' + form_id).serializeArray(),
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    if (data.data == 1) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Cart',
                            text: "<?php echo e(translate('messages.product_already_added_in_cart')); ?>"
                        });
                        return false;
                    } else if (data.data == 0) {
                        $('#food_search_result').empty();
                        $('#quick-view').modal('hide');
                        $("#data-view").empty().append(data.view);
                        toastr.success(data.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });

                        return false;

                    } else if (data.data === 'stock_out') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Cart',
                            text: data.message
                        });
                        return false;
                    }

                    else if (data.data == 'variation_error') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Cart',
                            text: data.message
                        });
                        return false;
                    }
                    $('.call-when-done').click();

                },
                complete: function() {
                    $('#loading').hide();
                }
            });
        })

        $(document).on('click', '.add-To-Cart-Single', function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': "<?php echo e(csrf_token()); ?>"
                }
            });

            let id = $(this).data('id');
            let item_type = $(this).data('item_type');
            let quantity = $(this).data('quantity');

            $.post({
                url: '<?php echo e(route('admin.order.add-to-cart')); ?>',
                data: {
                    id: id,
                    item_type: item_type,
                    quantity: quantity
                },
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    if (data.data == 1) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Cart',
                            text: "<?php echo e(translate('messages.product_already_added_in_cart')); ?>"
                        });
                        return false;
                    } else if (data.data == 0) {
                        $('#food_search_result').empty();
                        $('#quick-view').modal('hide');
                        $("#data-view").empty().append(data.view);
                        toastr.success('<?php echo e(translate('messages.product_has_been_added_in_cart')); ?>', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        return false;
                    } else if (data.data === 'stock_out') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Cart',
                            text: data.message
                        });
                        return false;
                    } else if (data.data == 'variation_error') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Cart',
                            text: data.message
                        });
                        return false;
                    }
                    $('.call-when-done').click();
                },
                complete: function() {
                    $('#loading').hide();
                }
            });
        });

        $(document).on('click', '.removeFromCart', function () {
                $.post('<?php echo e(route('admin.order.remove-from-cart')); ?>', {
                        _token: '<?php echo e(csrf_token()); ?>',
                        cart_id:  $('#delete_cart_id').val(),
                        order_id: '<?php echo e($order->id); ?>'
                    }, function(data) {
                        $('#quick-view').modal('hide');
                        if (data.errors) {
                            for (let i = 0; i < data.errors.length; i++) {
                                toastr.error(data.errors[i].message, {
                                    CloseButton: true,
                                    ProgressBar: true
                                });
                            }
                        } else {
                        $('#food_search_result').empty();
                        $('#quick-view').modal('hide');
                        $("#data-view").empty().append(data.view);
                            toastr.success('<?php echo e(translate('messages.item_has_been_removed_from_cart')); ?>', {
                                CloseButton: true,
                                ProgressBar: true
                            });

                        }
                    });
        })

        $('.js-select2-custom').each(function () {
            let select2 = $.HSCore.components.HSSelect2.init($(this));
        });

        $('.add-delivery-man').on('click', function(){
            let id = $(this).data('id');
            addDeliveryMan(id);
        })

        $('.disableDM').on('click', function(){
            toastr.info('<?php echo e(translate('messages.delivery_man_max_cash_in_hand_exceeds')); ?>', {
                                CloseButton: true,
                                ProgressBar: true
                            });

        })

        function addDeliveryMan(id) {
            $.ajax({
                type: "GET",
                url: '<?php echo e(url('/')); ?>/admin/order/add-delivery-man/<?php echo e($order['id']); ?>/' + id,
                success: function(data) {

                    toastr.success('<?php echo e(translate('Successfully_added')); ?>', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    location.reload();
                },
                error: function(response) {
                    console.log(response);
                    toastr.error(response.responseJSON.message, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }

        function last_location_view() {
            toastr.warning('<?php echo e(translate('Only_available_when_order_is_out_for_delivery!')); ?>', {
                CloseButton: true,
                ProgressBar: true
            });
        }

        $('.cancelled_status').on('click', function(){
            Swal.fire({
                title: '<?php echo e(translate('messages.are_you_sure?')); ?>',
                text: '<?php echo e(translate('messages.Change_status_to_canceled_?')); ?>',
                type: 'warning',
                html:
                `   <select class="form-control js-select2-custom mx-1" name="reason" id="reason" required>
                    <option value=" ">
                            <?php echo e(translate('select_cancellation_reason')); ?>

                        </option>
                    <?php $__currentLoopData = $reasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($r->reason); ?>">
                            <?php echo e($r->reason); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </select>
                    <div class="mt-3 text-start">
                        <textarea class="form-control mx-1" name="note" id="note" rows="4" placeholder="<?php echo e(translate('Cancellation note')); ?>"></textarea>
                    </div>`
                ,
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '<?php echo e(translate('messages.no')); ?>',
                confirmButtonText: '<?php echo e(translate('messages.yes')); ?>',
                reverseButtons: true,
                onOpen: function () {
                        $('.js-select2-custom').select2({
                            minimumResultsForSearch: 5,
                            width: '100%',
                            placeholder: "Select Reason",
                            language: "en",
                        });
                    }
            }).then((result) => {
                if (result.value) {
                    // console.log(result);
                    let reason = document.getElementById('reason').value;
                    let note = document.getElementById('note').value;
                    location.href = '<?php echo route('admin.order.status', ['id' => $order['id'],'order_status' => 'canceled']); ?>&reason='+reason+'&note='+note,'<?php echo e(translate('Change_status_to_canceled_?')); ?>';
                }
            })
        })

        let deliveryMan = <?php echo json_encode($deliveryMen); ?>;
        let map = null;

        const mapId = "<?php echo e(\App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value); ?>";
        const { AdvancedMarkerElement } = google.maps.marker;

        function createMarkerIcon(src) {
            const img = document.createElement('img');
            img.src = src;
            img.style.width = '100%';
            img.style.height = '100%';
            return img;
        }

        const customerIcon = createMarkerIcon("<?php echo e(dynamicAsset('assets/admin/img/customer_location.png')); ?>");
        const restaurantIcon = createMarkerIcon("<?php echo e(dynamicAsset('assets/admin/img/restaurant_map_1.png')); ?>");
        const deliveryBoy1Icon = createMarkerIcon("<?php echo e(dynamicAsset('assets/admin/img/delivery_boy_map_1.png')); ?>");
        const deliveryBoy2Icon = createMarkerIcon("<?php echo e(dynamicAsset('assets/admin/img/delivery_boy_map_2.png')); ?>");

        let myLatlng = new google.maps.LatLng(<?php echo e(isset($order->restaurant) ? $order->restaurant->latitude : 0); ?>,
            <?php echo e(isset($order->restaurant) ? $order->restaurant->longitude : 0); ?>);
        let dmbounds = new google.maps.LatLngBounds(null);
        let locationbounds = new google.maps.LatLngBounds(null);
        let dmMarkers = [];
        dmbounds.extend(myLatlng);
        locationbounds.extend(myLatlng);
        let myOptions = {
            center: myLatlng,
            mapId: mapId,
            zoom: 13,
            mapTypeId: google.maps.MapTypeId.ROADMAP,

            panControl: true,
            mapTypeControl: false,
            panControlOptions: {
                position: google.maps.ControlPosition.RIGHT_CENTER
            },
            zoomControl: true,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle.LARGE,
                position: google.maps.ControlPosition.RIGHT_CENTER
            },
            scaleControl: false,
            streetViewControl: false,
            streetViewControlOptions: {
                position: google.maps.ControlPosition.RIGHT_CENTER
            }
        };

        function initializeGMap() {

            map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

            let infowindow = new google.maps.InfoWindow();
            let dm_default_image = null;

            <?php if($order->restaurant): ?>
                let Restaurantmarker = new AdvancedMarkerElement({
                    position: new google.maps.LatLng(
                        <?php echo e(isset($order->restaurant) ? $order->restaurant->latitude : 0); ?>,
                        <?php echo e(isset($order->restaurant) ? $order->restaurant->longitude : 0); ?>),
                    map: map,
                    title: "<?php echo e(isset($order->restaurant) ? Str::limit($order->restaurant->name, 15, '...') : ''); ?>",
                    content: restaurantIcon
                });


                google.maps.event.addListener(Restaurantmarker, 'click', (function(Restaurantmarker) {
                    return function() {
                        infowindow.setContent(
                            `<div class='float--left'><img class='js--design-1'
                                src="<?php echo e($order?->restaurant?->logo_full_url ?? dynamicAsset('assets/admin/img/160x160/img1.jpg')); ?>"
                                                    alt='image'></div><div class='text-break float--right p--10px'><b><?php echo e(Str::limit($order->restaurant->name, 15, '...')); ?></b><br/> <?php echo e($order->restaurant->address); ?></div>`
                        );
                        infowindow.open(map, Restaurantmarker);
                    }
                })(Restaurantmarker));
            <?php endif; ?>
            map.fitBounds(dmbounds);
            for (let i = 0; i < deliveryMan.length; i++) {
                if (deliveryMan[i].lat) {
                    let point = new google.maps.LatLng(deliveryMan[i].lat, deliveryMan[i].lng);
                    dmbounds.extend(point);
                    map.fitBounds(dmbounds);
                    let d_man = "<?php echo e($order?->delivery_man_id ?? 0); ?>" ;
                    if ( deliveryMan[i].id ==  d_man){
                        dm_default_image=  deliveryBoy2Icon;
                    } else {
                        dm_default_image=  deliveryBoy1Icon;
                    }

                    let marker = new AdvancedMarkerElement({
                        position: point,
                        map: map,
                        title: deliveryMan[i].location,
                        content: dm_default_image
                    });
                    dmMarkers[deliveryMan[i].id] = marker;
                    google.maps.event.addListener(marker, 'click', (function(marker, i) {
                        return function() {
                            infowindow.setContent(
                                `<div class='float--left'><img class='js--design-1 mt-2' onerror="this.src='<?php echo e(dynamicAsset('assets/admin/img/160x160/img1.jpg')); ?>'"   src='<?php echo e(dynamicStorage('storage/app/public/delivery-man')); ?>/` +
                                deliveryMan[i].image +
                                "'></div><div class='float--right p--10px'><b>" + deliveryMan[i]
                                .name + "</b><br/> <div> <?php echo e(translate('messages.Active_Orders')); ?> :" + deliveryMan[i].current_orders + "</div> </b>" + deliveryMan[i].location + "</div>");
                            infowindow.open(map, marker);
                        }
                    })(marker, i));
                }

            };
        }

        function initMap() {
            let zonePolygon = null;
            //get current location block
            let infoWindow = new google.maps.InfoWindow();
            // Try HTML5 geolocation.
            if(!document.getElementById("map")){
                return true;
            }
            let map = new google.maps.Map(document.getElementById("map"), myOptions);

            <?php if(isset($address) && is_numeric($address['latitude'] ?? null) && is_numeric($address['longitude'] ?? null) ): ?>
            let marker = new AdvancedMarkerElement({
                position: new google.maps.LatLng(<?php echo e($address['latitude']); ?>,
                    <?php echo e($address['longitude']); ?>),
                map: map,
                title: "<?php echo e($order?->customer ? $order?->customer->f_name .' '. $order?->customer->l_name : $address['contact_person_name']); ?>",
                content: customerIcon
            });

            google.maps.event.addListener(marker, 'click', (function(marker) {
                return function() {
                    infowindow.setContent(
                        `<div class='float--left'><img class='js--design-1' onerror="this.src='<?php echo e(dynamicAsset('assets/admin/img/160x160/img1.jpg')); ?>'"  src='<?php echo e($order?->customer ? dynamicStorage('storage/app/public/profile/' . $order?->customer?->image) : dynamicAsset('assets/admin/img/160x160/img3.png')); ?>'></div><div class='float--right p--10px'><b><?php echo e($order?->customer ? $order?->customer->f_name .' '. $order?->customer->l_name : $address['contact_person_name']); ?></b><br/><?php echo e($address['address']); ?></div>`
                    );
                    infowindow.open(map, marker);
                }
            })(marker));
            locationbounds.extend(marker.position);
            <?php endif; ?>
            //-----end block------
            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
            let markers = [];
            const bounds = new google.maps.LatLngBounds();
            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();

                if (places.length == 0) {
                    return;
                }
                // Clear out the old markers.
                // markers.forEach((marker) => {
                //     marker.setMap(null);
                // });

                markers.forEach(m => m.map = null);

                markers = [];
                // For each place, get the icon, name and location.
                places.forEach((place) => {
                    if (!place.geometry || !place.geometry.location) {
                        console.log("Returned place contains no geometry");
                        return;
                    }
                    console.log(place.geometry.location);
                    if(!google.maps.geometry.poly.containsLocation(
                        place.geometry.location,
                        zonePolygon
                    )){
                        toastr.error('<?php echo e(translate('messages.out_of_coverage')); ?>', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        return false;
                    }

                    document.getElementById('latitude').value = place.geometry.location.lat();
                    document.getElementById('longitude').value = place.geometry.location.lng();

                    const icon = {
                        url: place.icon,
                        size: new google.maps.Size(71, 71),
                        origin: new google.maps.Point(0, 0),
                        anchor: new google.maps.Point(17, 34),
                        scaledSize: new google.maps.Size(25, 25),
                    };
                    // Create a marker for each place.
                    markers.push(
                        new AdvancedMarkerElement({
                            map,
                            icon,
                            title: place.name,
                            position: place.geometry.location,
                        })
                    );

                    if (place.geometry.viewport) {
                        // Only geocodes have viewport.
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });
                map.fitBounds(bounds);
            });
            <?php if($order->restaurant): ?>
                $.get({
                    url: '<?php echo e(url('/')); ?>/admin/zone/get-coordinates/<?php echo e($order->restaurant->zone_id); ?>',
                    dataType: 'json',
                    success: function(data) {
                        zonePolygon = new google.maps.Polygon({
                            paths: data.coordinates,
                            strokeColor: "#FF0000",
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: 'white',
                            fillOpacity: 0,
                        });
                        zonePolygon.setMap(map);
                        zonePolygon.getPaths().forEach(function(path) {
                            path.forEach(function(latlng) {
                                bounds.extend(latlng);
                                map.fitBounds(bounds);
                            });
                        });
                        map.setCenter(data.center);
                        google.maps.event.addListener(zonePolygon, 'click', function(mapsMouseEvent) {
                            infoWindow.close();
                            // Create a new InfoWindow.
                            infoWindow = new google.maps.InfoWindow({
                                position: mapsMouseEvent.latLng,
                                content: JSON.stringify(mapsMouseEvent.latLng.toJSON(), null,
                                    2),
                            });
                            let coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                            coordinates = JSON.parse(coordinates);

                            document.getElementById('latitude').value = coordinates['lat'];
                            document.getElementById('longitude').value = coordinates['lng'];
                            infoWindow.open(map);
                            const geocoder = new google.maps.Geocoder();
                            geocoder.geocode({ location: mapsMouseEvent.latLng }, function (results, status) {
                                if (status === "OK") {
                                    if (results[0]) {
                                        document.getElementById('address').value = results[0].formatted_address;
                                    }
                                }
                            });
                        });
                    },
                });
            <?php endif; ?>

        }
        initMap();

        $(document).ready(function() {

            // Re-init map before show modal
            $('#myModal').on('shown.bs.modal', function(event) {
                let button = $(event.relatedTarget);
                $("#dmassign-map").css("width", "100%");
                $("#map_canvas").css("width", "100%");
            });

            // Trigger map resize event after modal shown
            $('#myModal').on('shown.bs.modal', function() {
                initializeGMap();
                google.maps.event.trigger(map, "resize");
                map.setCenter(myLatlng);
            });

            $('#shipping-address-modal').on('shown.bs.modal', function() {
                initMap();
            });


            function initializegLocationMap() {
                map = new google.maps.Map(document.getElementById("location_map_canvas"), myOptions);

                let infowindow = new google.maps.InfoWindow();
                <?php if(isset($address) && is_numeric($address['latitude'] ?? null) && is_numeric($address['longitude'] ?? null) ): ?>
                    let marker = new AdvancedMarkerElement({
                        position: new google.maps.LatLng(<?php echo e($address['latitude']); ?>,
                            <?php echo e($address['longitude']); ?>),
                        map: map,
                        title: "<?php echo e($order?->customer ? $order?->customer->f_name .' '. $order?->customer->l_name : $address['contact_person_name']); ?>",
                        content: customerIcon
                    });

                    google.maps.event.addListener(marker, 'click', (function(marker) {
                        return function() {
                            infowindow.setContent(
                                `<div class='float--left'><img class='js--design-1' onerror="this.src='<?php echo e(dynamicAsset('assets/admin/img/160x160/img1.jpg')); ?>'"  src='<?php echo e($order?->customer ? dynamicStorage('storage/app/public/profile/' . $order?->customer?->image) : dynamicAsset('assets/admin/img/160x160/img3.png')); ?>'></div><div class='float--right p--10px'><b><?php echo e($order?->customer ? $order?->customer->f_name .' '. $order?->customer->l_name : $address['contact_person_name']); ?></b><br/><?php echo e($address['address']); ?></div>`
                            );
                            infowindow.open(map, marker);
                        }
                    })(marker));
                    locationbounds.extend(marker.position);
                <?php endif; ?>
                <?php if($order->delivery_man && $order->dm_last_location): ?>
                    let dmmarker = new AdvancedMarkerElement({
                        position: new google.maps.LatLng(<?php echo e($order->dm_last_location['latitude']); ?>,
                            <?php echo e($order->dm_last_location['longitude']); ?>),
                        map: map,
                        title: "<?php echo e($order->delivery_man->f_name); ?>  <?php echo e($order->delivery_man->l_name); ?>",
                        content: deliveryBoy2Icon
                    });

                    google.maps.event.addListener(dmmarker, 'click', (function(dmmarker) {
                        return function() {
                            infowindow.setContent(
                                `<div class='float--left'><img class='js--design-1 mt-2' onerror="this.src='<?php echo e(dynamicAsset('assets/admin/img/160x160/img1.jpg')); ?>'"  src='<?php echo e(dynamicStorage('storage/app/public/delivery-man/' . $order->delivery_man->image)); ?>'></div><div class='float--right p--10px'><b><?php echo e($order->delivery_man->f_name); ?>  <?php echo e($order->delivery_man->l_name); ?></b><br/>  <div> <?php echo e(translate('messages.Active_Orders')); ?> : <?php echo e($order->delivery_man->current_orders); ?> </div> </b> <?php echo e($order->dm_last_location['location']); ?></div>`
                            );
                            infowindow.open(map, dmmarker);
                        }
                    })(dmmarker));
                    locationbounds.extend(dmmarker.position);
                <?php endif; ?>

                <?php if($order->restaurant): ?>
                    let Retaurantmarker = new AdvancedMarkerElement({
                        position: new google.maps.LatLng(<?php echo e($order->restaurant->latitude); ?>,
                            <?php echo e($order->restaurant->longitude); ?>),
                        map: map,
                        title: "<?php echo e(Str::limit($order->restaurant->name, 15, '...')); ?>",
                        content: restaurantIcon
                    });

                    google.maps.event.addListener(Retaurantmarker, 'click', (function(Retaurantmarker) {
                        return function() {
                            infowindow.setContent(
                                `<div class='float--left'><img class='js--design-1' onerror="this.src='<?php echo e(dynamicAsset('assets/admin/img/160x160/img1.jpg')); ?>'"  src='<?php echo e(dynamicStorage('storage/app/public/restaurant/' . $order->restaurant->logo)); ?>'></div><div class='float--right p--10px'><b><?php echo e(Str::limit($order->restaurant->name, 15, '...')); ?></b><br/> <?php echo e($order->restaurant->address); ?></div>`
                            );
                            infowindow.open(map, Retaurantmarker);
                        }
                    })(Retaurantmarker));
                    locationbounds.extend(Retaurantmarker.position);
                <?php endif; ?>

                google.maps.event.addListenerOnce(map, 'idle', function() {
                    map.fitBounds(locationbounds);
                });
            }

            // Re-init map before show modal
            $('#locationModal').on('shown.bs.modal', function(event) {
                initializegLocationMap();
            });



            $('.dm_list').on('click', function() {
                let id = $(this).data('id');
                map.panTo(dmMarkers[id].position);
                map.setZoom(13);
                dmMarkers[id].setAnimation(google.maps.Animation.BOUNCE);
                window.setTimeout(() => {
                    dmMarkers[id].setAnimation(null);
                }, 3);
            });




            $('.dm_list_selected').on('click', function() {
                let id = $(this).data('id');
                map.panTo(dmMarkers[id].position);
                map.setZoom(13);
                dmMarkers[id].setAnimation(google.maps.Animation.BOUNCE);
                window.setTimeout(() => {
                    dmMarkers[id].setAnimation(null);
                }, 3);

            });

        })

        $(function() {
            $("#coba").spartanMultiImagePicker({
                fieldName: 'order_proof[]',
                maxCount: 6-<?php echo e(($order->order_proof && is_array($order->order_proof))?count(json_decode($order->order_proof)):0); ?>,
                rowHeight: '100px !important',
                groupClassName: 'spartan_item_wrapper min-w-100px max-w-100px',
                maxFileSize: '',
                placeholderImage: {
                    image: "<?php echo e(dynamicAsset('assets/admin/img/upload.png')); ?>",
                    width: '100px'
                },
                dropFileLabel: "Drop Here",
                onAddRow: function(index, file) {

                },
                onRenderedPreview: function(index) {

                },
                onRemoveRow: function(index) {

                },
                onExtensionErr: function(index, file) {
                    toastr.error(
                        "<?php echo e(translate('messages.please_only_input_png_or_jpg_type_file')); ?>", {
                            CloseButton: true,
                            ProgressBar: true
                        });
                },
                onSizeErr: function(index, file) {
                    toastr.error("<?php echo e(translate('messages.file_size_too_big')); ?>", {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });


    </script>



<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/order/order-view.blade.php ENDPATH**/ ?>