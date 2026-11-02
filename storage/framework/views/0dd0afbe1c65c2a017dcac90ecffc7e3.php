<?php $__env->startSection('title',$restaurant->name."'s ".translate('messages.business_plan')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <!-- Custom styles for this page -->
    <link href="<?php echo e(dynamicAsset('assets/admin/css/croppie.css')); ?>" rel="stylesheet">

<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="content container-fluid">
    <div class="page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <h1 class="page-header-title text-break">
                <i class="tio-museum"></i> <span><?php echo e($restaurant->name); ?> <?php echo $__env->make('partials._verified_restaurant_badge', ['restaurant' => $restaurant], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
            </h1>
        </div>
        <!-- Nav Scroller -->
        <div class="js-nav-scroller hs-nav-scroller-horizontal">
            <span class="hs-nav-scroller-arrow-prev initial-hidden">
                <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                    <i class="tio-chevron-left"></i>
                </a>
            </span>

            <span class="hs-nav-scroller-arrow-next initial-hidden">
                <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                    <i class="tio-chevron-right"></i>
                </a>
            </span>

            <!-- Nav -->
            <?php echo $__env->make('admin-views.vendor.view.partials._header',['restaurant'=>$restaurant], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <!-- End Nav -->
        </div>
        <!-- End Nav Scroller -->
    </div>
    <?php if($restaurant->restaurant_model == 'commission' &&  \App\CentralLogics\Helpers::commission_check()): ?>

    <div class="mt-3 card mb-3">
        <div class="card-header border-0 align-items-center">
            <h4 class="card-title align-items-center gap-2">
                <span class="card-header-icon">
                    <img width="25" src="<?php echo e(dynamicAsset('assets/admin/img/subscription-plan/subscribed-user.png')); ?>" alt="">
                </span>
                <span><?php echo e(translate('Overview')); ?></span>
            </h4>
        </div>
        <div class="card-body pt-0">
            <div class="__bg-F8F9FC-card __plan-details">
                <div class="d-flex flex-wrap flex-md-nowrap justify-content-between __plan-details-top">
                    <div class="w-100">
                        <h2 class="name text--primary"><?php echo e(translate('Commission Base Plan')); ?></h2>
                        <h4 class="title mt-2"><span class="text-180"><?php echo e($restaurant->comission > 0 ?  $restaurant->comission :  $admin_commission); ?> %</span> <?php echo e(translate('messages.Commission_per_order')); ?></h4>
                        <div class="info-text ">
                            <?php echo e(translate('Restaurant will pay')); ?> <?php echo e($restaurant->comission > 0 ?  $restaurant->comission :  $admin_commission); ?>% <?php echo e(translate('commission to')); ?> <strong><?php echo e($business_name); ?></strong> <?php echo e(translate('from each order. You will get access of all the features and options  in restaurant panel , app and interaction with user.')); ?>

                        </div>
                                <div class="mt-3">
                                    <form action="<?php echo e(route('admin.restaurant.update-settings',[$restaurant['id'] , 'tab' => 'business_plan'])); ?>" method="post">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field("post"); ?>
                                        <div class="row">
                                            <div class="col-xl-6 col-xxl-5">
                                                <div>
                                                    <label class="d-flex mb-1 justify-content-between toggle-switch-sm text-dark text-capitalize" for="comission_status">
                                                        <span><?php echo e(translate('messages.Change_Commission_Rate')); ?>(%) <span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="<?php echo e(translate('When_enabled,_admin_will_only_receive_the_certain_commission_percentage_he_set_for_this_restaurant._Otherwise,_the_system_default_commission_will_be_applied.')); ?>"><img src="<?php echo e(dynamicAsset('assets/admin/img/info-circle.svg')); ?>" alt="<?php echo e(translate('When_enabled,_admin_will_only_receive_the_certain_commission_percentage_he_set_for_this_restaurant._Otherwise,_the_system_default_commission_will_be_applied.')); ?>"></span></span>
                                                        <input type="checkbox" class="toggle-switch-input" name="comission_status" id="comission_status" value="1" <?php echo e(isset($restaurant->comission)?'checked':''); ?>>
                                                        <span class="toggle-switch-label">
                                                            <span class="toggle-switch-indicator"></span>
                                                        </span>
                                                    </label>
                                                    <div class="d-flex flex-wrap gap-3">
                                                        <input type="number" id="comission" min="0" max="10000" step="0.01" name="comission" class="form-control w-200px flex-grow-1 bg-white" required value="<?php echo e($restaurant->comission??'0'); ?>" <?php echo e(isset($restaurant->comission)?'':'readonly'); ?>>
                                                        <button type="submit" class="btn btn--primary"><?php echo e(translate('Change')); ?></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                    </div>
                </div>

            </div>
            <?php if(\App\CentralLogics\Helpers::subscription_check() ): ?>
                <div class="btn--container justify-content-end mt-3">
                    <button type="button" data-toggle="modal" data-target="#plan-modal" class="btn btn--primary"><?php echo e(translate('Change Business Plan')); ?></button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php elseif(in_array($restaurant->restaurant_model,[ 'subscription' ,'unsubscribed']) && $restaurant?->restaurant_sub_update_application): ?>

                <div class="card mb-20">
                    <div class="card-header border-0 align-items-center">
                        <h4 class="card-title align-items-center gap-2">
                            <span class="card-header-icon">
                                <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription/billing.png')); ?>" alt="">
                            </span>
                            <span class="text-title"><?php echo e(translate('Billing')); ?></span>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6 col-lg-4">
                                <a class="__card-2 __bg-1 flex-row align-items-center gap-4" href="#">
                                    <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription/expiring.png')); ?>" alt="report/new" class="w-60px">
                                    <div class="w-0 flex-grow-1 py-md-3">
                                        <span class="text-body"><?php echo e(translate('Expire Date')); ?></span>
                                        <h4 class="title m-0"><?php echo e(\App\CentralLogics\Helpers::date_format($restaurant?->restaurant_sub_update_application?->expiry_date_parsed)); ?></h4>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <a class="__card-2 __bg-8 flex-row align-items-center gap-4" href="#">
                                    <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription/total-bill.png')); ?>" alt="report/new" class="w-60px">
                                    <div class="w-0 flex-grow-1 py-md-3">
                                        <span class="text-body"><?php echo e(translate('Total_Bill')); ?></span>
                                        <h4 class="title m-0"><?php echo e(\App\CentralLogics\Helpers::format_currency($restaurant?->restaurant_sub_update_application?->last_transcations?->price ?? $restaurant?->restaurant_sub_update_application?->package?->price * ($restaurant?->restaurant_sub_update_application?->total_package_renewed + 1) )); ?></h4>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <a class="__card-2 __bg-4 flex-row align-items-center gap-4" href="#">
                                    <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription/number.png')); ?>" alt="report/new" class="w-60px">
                                    <div class="w-0 flex-grow-1 py-md-3">
                                        <span class="text-body"><?php echo e(translate('Number of Uses')); ?></span>
                                        <h4 class="title m-0"><?php echo e($restaurant?->restaurant_sub_update_application?->total_package_renewed + 1); ?></h4>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-header border-0 align-items-center">
                        <h4 class="card-title align-items-center gap-2">
                            <span class="card-header-icon">
                                <img width="25" src="<?php echo e(dynamicAsset('assets/admin/img/subscription-plan/subscribed-user.png')); ?>" alt="">
                            </span>
                            <span><?php echo e(translate('Package Overview')); ?>


                                <?php if($restaurant?->status == 0 &&  $restaurant?->vendor?->status == 0): ?>
                                <span class=" badge badge-pill badge-info">  &nbsp; <?php echo e(translate('Approval_Pending')); ?>  &nbsp; </span>
                                <?php elseif($restaurant?->restaurant_sub_update_application?->status == 0): ?>
                                <span class=" badge badge-pill badge-danger">  &nbsp; <?php echo e(translate('Expired')); ?>  &nbsp; </span>
                                <?php elseif($restaurant?->restaurant_sub_update_application?->is_canceled == 1): ?>
                                <span class=" badge badge-pill badge-warning">  &nbsp; <?php echo e(translate('canceled')); ?>  &nbsp; </span>
                                <?php elseif($restaurant?->restaurant_sub_update_application?->status == 1): ?>
                                <span class=" badge badge-pill badge-success">  &nbsp; <?php echo e(translate('Active')); ?>  &nbsp; </span>
                                <?php endif; ?>

                            </span>
                        </h4>
                    </div>
                    <div class="card-body pt-0">
                        <div class="__bg-F8F9FC-card __plan-details">
                            <div class="d-flex flex-wrap flex-md-nowrap justify-content-between __plan-details-top">
                                <div class="left">
                                    <h3 class="name"><?php echo e($restaurant?->restaurant_sub_update_application?->package?->package_name); ?></h3>
                                    <div class="font-medium text--title"><?php echo e($restaurant?->restaurant_sub_update_application?->package?->text); ?></div>
                                </div>
                                <h3 class="right"><?php echo e(\App\CentralLogics\Helpers::format_currency($restaurant?->restaurant_sub_update_application?->last_transcations?->price?? $restaurant?->restaurant_sub_update_application?->package?->price)); ?> /<small class="font-medium text--title"><?php echo e($restaurant?->restaurant_sub_update_application?->last_transcations?->validity ?? $restaurant?->restaurant_sub_update_application?->package?->validity); ?> <?php echo e(translate('messages.Days')); ?></small></h3>
                            </div>


                            <div class="check--grid-wrapper mt-3 max-w-850px">


                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription-plan/check.png')); ?>" alt="">
                                        <?php if( $restaurant?->restaurant_sub_update_application?->max_order == 'unlimited' ): ?>
                                        <span class="form-check-label text-dark"><?php echo e(translate('messages.unlimited_orders')); ?></span>
                                        <?php else: ?>
                                        <span class="form-check-label text-dark"> <?php echo e($restaurant?->restaurant_sub_update_application?->package?->max_order); ?> <?php echo e(translate('messages.Orders')); ?> <small>(<?php echo e($restaurant?->restaurant_sub_update_application?->max_order); ?> <?php echo e(translate('left')); ?>) </small> </span>
                                        <?php endif; ?>
                                    </div>
                                </div>


                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if( $restaurant?->restaurant_sub_update_application?->pos == 1 ): ?>
                                        <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription-plan/check.png')); ?>" alt="">
                                        <?php else: ?>
                                        <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription-plan/check-1.png')); ?>" alt="">
                                        <?php endif; ?>
                                        <span class="form-check-label text-dark"><?php echo e(translate('messages.POS')); ?></span>
                                    </div>
                                </div>

                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if( $restaurant?->restaurant_sub_update_application?->mobile_app == 1 ): ?>
                                        <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription-plan/check.png')); ?>" alt="">
                                        <?php else: ?>
                                        <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription-plan/check-1.png')); ?>" alt="">
                                        <?php endif; ?>
                                        <span class="form-check-label text-dark"><?php echo e(translate('messages.Mobile_App')); ?></span>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if( $restaurant?->restaurant_sub_update_application?->self_delivery == 1 ): ?>
                                        <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription-plan/check.png')); ?>" alt="">
                                        <?php else: ?>
                                        <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription-plan/check-1.png')); ?>" alt="">
                                        <?php endif; ?>
                                        <span class="form-check-label text-dark"><?php echo e(translate('messages.self_delivery')); ?></span>
                                    </div>
                                </div>

                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription-plan/check.png')); ?>" alt="">
                                        <?php if( $restaurant?->restaurant_sub_update_application?->max_product == 'unlimited' ): ?>
                                        <span class="form-check-label text-dark"><?php echo e(translate('messages.unlimited_item_Upload')); ?></span>
                                        <?php else: ?>
                                        <span class="form-check-label text-dark"> <?php echo e($restaurant?->restaurant_sub_update_application?->max_product); ?> <?php echo e(translate('messages.product_Upload')); ?> <small>(<?php echo e($restaurant?->restaurant_sub_update_application?->max_product  - $restaurant->foods_count > 0 ? $restaurant?->restaurant_sub_update_application?->max_product  - $restaurant->foods_count : 0); ?> <?php echo e(translate('left')); ?>) </small></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if( $restaurant?->restaurant_sub_update_application?->review == 1 ): ?>
                                        <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription-plan/check.png')); ?>" alt="">
                                        <?php else: ?>
                                        <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription-plan/check-1.png')); ?>" alt="">
                                        <?php endif; ?>
                                        <span class="form-check-label text-dark"><?php echo e(translate('messages.review')); ?></span>
                                    </div>
                                </div>

                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if( $restaurant?->restaurant_sub_update_application?->chat == 1 ): ?>
                                        <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription-plan/check.png')); ?>" alt="">
                                        <?php else: ?>
                                        <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription-plan/check-1.png')); ?>" alt="">
                                        <?php endif; ?>
                                        <span class="form-check-label text-dark"><?php echo e(translate('messages.chat')); ?></span>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="btn--container justify-content-end mt-3">
                            <?php if( $restaurant?->restaurant_sub_update_application?->is_canceled == 0 && $restaurant?->restaurant_sub_update_application?->status == 1  ): ?>
                            <button type="button"  data-url="<?php echo e(route('admin.subscription.cancelSubscription',$restaurant?->id)); ?>" data-message="<?php echo e(translate('If_you_cancel_the_subscription,_after_')); ?> <?php echo e(Carbon\Carbon::now()->diffInDays($restaurant?->restaurant_sub_update_application?->expiry_date_parsed->format('Y-m-d'), false)); ?> <?php echo e(translate('days_the_vendor_will_no_longer_be_able_to_run_the_business_before_subscribe_a_new_plan.')); ?>"
                                class="btn btn--danger text-white status_change_alert"><?php echo e(translate('Cancel Subscription')); ?></button>
                            <?php endif; ?>

                            <button type="button" data-toggle="modal" data-target="#plan-modal" class="btn btn--primary"><?php echo e(translate('Change / Renew Subscription Plan')); ?></button>

                        </div>
                    </div>
                </div>

        <?php else: ?>


        <div class="card">
            <div class="card-body text-center py-5">
                <div class="max-w-542 mx-auto py-sm-5 py-4">
                    <img class="mb-4" src="<?php echo e(dynamicAsset('assets/admin/img/empty-subscription.svg')); ?>" alt="img">
                    <h4 class="mb-3"><?php echo e(translate('Chose Business Plan')); ?></h4>
                    <p class="mb-4 max-w-450px mx-auto">
                        <?php echo e(translate('Chose a Business Plan from the list. So that Restaurants get more options to join the business for the growth and success.')); ?><br>
                    </p>
                    <button type="button" data-toggle="modal" data-target="#plan-modal" class="btn btn-sm py-3 btn--primary"><?php echo e(translate('Chose Business Plan')); ?></button>
                </div>
            </div>
        </div>
    <?php endif; ?>



    <div class="modal fade show" id="plan-modal">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header px-3 pt-3">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body px-4 pt-0">
                    <div>
                        <div class="text-center">
                            <h2 class="modal-title"><?php echo e(translate('Chose a Business')); ?></h2>
                        </div>
                        <div class="text-center text-14 mb-4 pb-3">
                           <?php echo e(translate('Chose a Business plan to get better experience!')); ?>

                        </div>
                        <div class="plan-slider owl-theme owl-carousel owl-refresh">
                            <?php if(\App\CentralLogics\Helpers::commission_check()): ?>
                            <div class="__plan-item hover <?php echo e($restaurant->restaurant_model == 'commission'  ? 'active' : ''); ?> ">
                                <div class="inner-div">
                                    <div class="text-center">
                                        <h3 class="title"><?php echo e(translate('Commission Base')); ?></h3>
                                        <h2 class="price"><?php echo e($admin_commission); ?>%</h2>
                                    </div>
                                    <div class="py-5 mt-4">
                                        <div class="info-text text-center">
                                            <?php echo e(translate('Restaurant will pay')); ?> <?php echo e($admin_commission); ?>% <?php echo e(translate('commission to')); ?> <?php echo e($business_name); ?> <?php echo e(translate('from each order. You will get access of all the features and options  in restaurant panel , app and interaction with user.')); ?>

                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <?php if($restaurant->restaurant_model == 'commission'): ?>
                                        <button type="button" class="btn btn--secondary"><?php echo e(translate('Current_Plan')); ?></button>
                                        <?php else: ?>
                                        <?php
                                        $cash_backs= \App\CentralLogics\Helpers::calculateSubscriptionRefundAmount(restaurant:$restaurant ,return_data:true);
                                        ?>

                                        <button type="button" data-url="<?php echo e(route('admin.subscription.switchToCommission',$restaurant->id)); ?>" data-message="<?php echo e(translate('You_Want_To_Migrate_To_Commission.')); ?> <?php echo e(data_get($cash_backs,'back_amount') > 0  ?  translate('You will get').' '. \App\CentralLogics\Helpers::format_currency(data_get($cash_backs,'back_amount')) .' '.translate('to_your_wallet_for_remaining') .' '.data_get($cash_backs,'days').' '.translate('messages.days_subscription_plan') : ''); ?>"  class="btn btn--primary shift_to_commission"><?php echo e(translate('Shift in this plan')); ?></button>
                                        <?php endif; ?>

                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php $__empty_1 = true; $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <div class="__plan-item hover <?php echo e($restaurant?->restaurant_sub_update_application?->package_id == $package->id  && $restaurant->restaurant_model != 'commission'  ? 'active' : ''); ?>">
                                <div class="inner-div">
                                    <div class="text-center">
                                        <h3 class="title"><?php echo e($package->package_name); ?></h3>
                                        <h2 class="price"><?php echo e(\App\CentralLogics\Helpers::format_currency($package->price)); ?></h2>
                                        <div class="day-count"><?php echo e($package->validity); ?> <?php echo e(translate('messages.days')); ?></div>
                                    </div>
                                    <ul class="info">

                                        <?php if($package->pos): ?>
                                        <li>
                                            <i class="tio-checkmark-circle"></i> <span>  <?php echo e(translate('messages.POS')); ?> </span>
                                        </li>
                                        <?php endif; ?>
                                        <?php if($package->mobile_app): ?>
                                        <li>
                                            <i class="tio-checkmark-circle"></i> <span>  <?php echo e(translate('messages.mobile_app')); ?> </span>
                                        </li>
                                        <?php endif; ?>
                                        <?php if($package->chat): ?>
                                        <li>
                                            <i class="tio-checkmark-circle"></i> <span>  <?php echo e(translate('messages.chatting_options')); ?> </span>
                                        </li>
                                        <?php endif; ?>
                                        <?php if($package->review): ?>
                                        <li>
                                            <i class="tio-checkmark-circle"></i> <span>  <?php echo e(translate('messages.review_section')); ?> </span>
                                        </li>
                                        <?php endif; ?>
                                        <?php if($package->self_delivery): ?>
                                        <li>
                                            <i class="tio-checkmark-circle"></i> <span>  <?php echo e(translate('messages.self_delivery')); ?> </span>
                                        </li>
                                        <?php endif; ?>
                                        <?php if($package->max_order == 'unlimited'): ?>
                                        <li>
                                            <i class="tio-checkmark-circle"></i> <span>  <?php echo e(translate('messages.Unlimited_Orders')); ?> </span>
                                        </li>
                                        <?php else: ?>
                                        <li>
                                            <i class="tio-checkmark-circle"></i> <span>  <?php echo e($package->max_order); ?> <?php echo e(translate('messages.Orders')); ?> </span>
                                        </li>
                                        <?php endif; ?>
                                        <?php if($package->max_product == 'unlimited'): ?>
                                        <li>
                                            <i class="tio-checkmark-circle"></i> <span>  <?php echo e(translate('messages.Unlimited_uploads')); ?> </span>
                                        </li>
                                        <?php else: ?>
                                        <li>
                                            <i class="tio-checkmark-circle"></i> <span>  <?php echo e($package->max_product); ?> <?php echo e(translate('messages.uploads')); ?> </span>
                                        </li>
                                        <?php endif; ?>

                                    </ul>
                                    <div class="text-center">

                                        <?php if( $restaurant?->restaurant_model == 'none' ||  ($restaurant?->restaurant_model == 'unsubscribed' &&  !$restaurant?->restaurant_sub_update_application)): ?>
                                        <button data-id="<?php echo e($package->id); ?>"  data-url="<?php echo e(route('admin.subscription.packageView',[$package->id,$restaurant->id ])); ?>"
                                            data-target="#package_detail" id="package_detail" type="button" class="btn btn--primary text-white renew-btn package_detail"><?php echo e(translate('messages.Purchase')); ?></button>
                                        <?php elseif( $restaurant?->restaurant_model != 'commission'  && $restaurant?->restaurant_sub_update_application?->package_id == $package->id): ?>
                                        <button data-id="<?php echo e($package->id); ?>"  data-url="<?php echo e(route('admin.subscription.packageView',[$package->id,$restaurant->id ])); ?>"
                                            data-target="#package_detail" id="package_detail" type="button" class="btn btn--warning text-white renew-btn package_detail"><?php echo e(translate('messages.Renew')); ?></button>
                                        <?php else: ?>
                                        <button data-id="<?php echo e($package->id); ?>" data-url="<?php echo e(route('admin.subscription.packageView',[$package->id,$restaurant->id ])); ?>"
                                            data-target="#package_detail" id="package_detail" type="button" class="btn btn--primary shift-btn package_detail"><?php echo e(translate('messages.Shift_in_this_plan')); ?></button>
                                        <?php endif; ?>


                                    </div>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <?php endif; ?>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- subscription Plan Modal 2 -->
    <div class="modal fade __modal" id="subscription-renew-modal">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body px-4 pt-0">
                    <div class="data_package" id="data_package">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="product_warning">
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
                                <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription-plan/package-status-disable.png')); ?>" class="mb-20">
                                <h5 class="modal-title" ></h5>
                            </div>
                            <div class="text-center">
                                <h3><?php echo e(translate('Are_You_Sure_You_want_To_switch_to_this_plan?')); ?></h3>
                                <p><?php echo e(translate('You_are_about_to_downgrade_your_plan.After_subscribing_to_this_plan_your_oldest_')); ?> <span id="disable_item_count"></span> <?php echo e(translate('Items_will_be_inactivated.')); ?> </p>
                            </div>
                        </div>
                        <div class="btn--container justify-content-center">
                            <button  id="continue_btn" class="btn btn-outline-primary min-w-120" data-dismiss="modal" >
                                <?php echo e(translate("Continue")); ?>

                            </button>
                            <button  class="btn btn--primary min-w-120  shift_package"  id="back_to_planes" data-dismiss="modal" ><?php echo e(translate('Go_Back')); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <script>
        $('.plan-slider').owlCarousel({
            loop: false,
            margin: 0,
            responsiveClass:true,
            nav:false,
            dots:false,
            items: 3,
            startPosition: '<?php echo e($index); ?>',

            responsive:{
                0: {
                    items:1.1,
                },
                375: {
                    items:1.3,
                },
                576: {
                    items:1.7,
                },
                768: {
                    items:2.2,
                },
                992: {
                    items: 3,
                },
                1200: {
                    items: 4,
                }
            }
        })

        "use strict";
            $('.status_change_alert').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            status_change_alert(url, message, event)
        })

        function status_change_alert(url, message, e) {
            e.preventDefault();
            Swal.fire({
                title: '<?php echo e(translate('Are_you_sure?')); ?>',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '<?php echo e(translate('no')); ?>',
                confirmButtonText: '<?php echo e(translate('yes')); ?>',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.post({
                        url: url,
                        data: {
                            id: '<?php echo e($restaurant->id); ?>',
                            subscription_id:'<?php echo e($restaurant?->restaurant_sub_update_application?->id); ?>',
                        },
                        beforeSend: function () {
                            $('#loading').show()
                        },
                        success: function (data) {
                            toastr.success('<?php echo e(translate('Successfully_canceled_the_subscription')); ?>!');
                        },
                        complete: function () {
                            $('#loading').hide();
                            location.reload();
                        }
                    });
                }
            })
        }

        $('.shift_to_commission').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            shift_to_commission(url, message, event)
        })

        function shift_to_commission(url, message, e) {
            e.preventDefault();
            Swal.fire({
                title: '<?php echo e(translate('Are_you_sure?')); ?>',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '<?php echo e(translate('no')); ?>',
                confirmButtonText: '<?php echo e(translate('yes')); ?>',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.post({
                        url: url,
                        data: {
                            id: '<?php echo e($restaurant->id); ?>',
                        },
                        beforeSend: function () {
                            $('#loading').show()
                        },
                        success: function (data) {
                            toastr.success('<?php echo e(translate('Successfully_Switched_To_Commission')); ?>!');
                        },
                        complete: function () {
                            $('#loading').hide();
                            location.reload();
                        }
                    });
                }
            })
        }

        $(document).on('click', '.package_detail', function () {
            var url = $(this).attr('data-url');
            $.ajax({
                url: url,
                method: 'get',
                beforeSend: function() {
                            $('#loading').show();
                            $('#plan-modal').modal('hide')
                            },
                success: function(data){
                    $('#data_package').html(data.view);
                    if(data.disable_item_count !== null && data.disable_item_count > 0){
                        $('#product_warning').modal('show')
                        $('#disable_item_count').text(data.disable_item_count)
                    } else{
                        $('#subscription-renew-modal').modal('show')
                    }
                },
                complete: function() {
                        $('#loading').hide();
                    },

            });
        });
        $(document).on('click', '#continue_btn', function () {
            $('#subscription-renew-modal').modal('show')
        });

        $(document).on('click', '#back_to_planes', function () {
            $('#plan-modal').modal('show')
        });

        $("#comission_status").on('change', function(){
                if($("#comission_status").is(':checked')){
                    $('#comission').removeAttr('readonly');
                } else {
                    $('#comission').attr('readonly', true);
                    $('#comission').val('0');
                }
            });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/vendor/view/subscriptions.blade.php ENDPATH**/ ?>