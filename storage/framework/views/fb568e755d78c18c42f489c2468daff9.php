<?php $__env->startSection('title',$restaurant->name); ?>

<?php $verified_seller_badge = \App\CentralLogics\Helpers::get_business_settings('verified_seller_badge'); ?>
<?php $verified_seller = $restaurant?->restaurant_config?->verified_seller; ?>
<?php
    $reviewsInfo = $restaurant->reviews()->where('reviews.status', 1)->selectRaw('AVG(reviews.rating) as average_rating, COUNT(reviews.id) as total_reviews')->first();
    $average_rating = round((float) ($reviewsInfo->average_rating ?? 0), 1);
    $review_count = (int) ($reviewsInfo->total_reviews ?? 0);
    $order_stats = $restaurant->orders()->selectRaw("
        COUNT(*) as total_orders,
        SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
        SUM(CASE WHEN order_status = 'canceled' THEN 1 ELSE 0 END) as canceled_orders
    ")->first();
    $total_orders = (int) ($order_stats->total_orders ?? 0);
    $delivered_orders = (int) ($order_stats->delivered_orders ?? 0);
    $canceled_orders = (int) ($order_stats->canceled_orders ?? 0);
    $comparison_orders = $delivered_orders + $canceled_orders;
    $performance_rate = $average_rating > 2 && $comparison_orders > 0 ? round(($delivered_orders / $comparison_orders) * 100) : null;
?>

<?php $__env->startPush('css_or_js'); ?>
    <!-- Custom styles for this page -->
    <link href="<?php echo e(dynamicAsset('assets/admin/css/croppie.css')); ?>" rel="stylesheet">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <h1 class="page-header-title text-break me-2">
                    <i class="tio-shop"></i> <span><?php echo e($restaurant->name); ?></span>
                    <?php echo $__env->make('partials._verified_restaurant_badge', ['restaurant' => $restaurant], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </h1>
                <?php if($restaurant->vendor->status): ?>
                    <div class="d-flex flex-wrap gap-2 my-2">
                           <?php if($restaurant->status == 1 && $restaurant->vendor->status == 1 && \App\CentralLogics\Helpers::get_business_settings('verified_seller_badge')): ?>
                            <a href="javascript:"
                               class="btn  <?php echo e($verified_seller ? 'btn btn--secondary ' : 'btn-success bg-success-gradient'); ?>"
                               data-toggle="modal"
                               data-target="<?php echo e($verified_seller ? '#remove-badge-btn' : '#badge-modal-btn'); ?>">
                                <?php echo e($verified_seller ? translate('messages.removed_verified_badge') : translate('messages.give_verified_badge')); ?>

                            </a>
                        <?php endif; ?>
                        <a href="<?php echo e(route('admin.restaurant.edit',[$restaurant->id])); ?>" class="btn btn--primary">
                            <i class="tio-edit mr-2"></i> <?php echo e(translate('messages.edit_restaurant')); ?>

                        </a>
                    </div>
                <?php else: ?>
                    <div>
                        <?php if(!isset($restaurant->vendor->status)): ?>
                            <a class="btn btn--danger text-capitalize my-2 request_alert"
                               data-url="<?php echo e(route('admin.restaurant.application',[$restaurant['id'],0])); ?>" data-message="<?php echo e(translate('messages.you_want_to_deny_this_application')); ?>"
                               href="javascript:"><?php echo e(translate('messages.deny')); ?></a>
                        <?php endif; ?>
                        <a class="btn btn--primary text-capitalize my-2 request_alert"
                           data-url="<?php echo e(route('admin.restaurant.application',[$restaurant['id'],1])); ?>" data-message="<?php echo e(translate('messages.you_want_to_approve_this_application')); ?>"
                           href="javascript:"><?php echo e(translate('messages.approve')); ?></a>
                    </div>
                <?php endif; ?>
            </div>
            <?php if($restaurant->vendor->status): ?>
                <!-- Nav Scroller -->
                <div class="js-nav-scroller hs-nav-scroller-horizontal">
                    <!-- Nav -->
                    <?php echo $__env->make('admin-views.vendor.view.partials._header',['restaurant'=>$restaurant], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    <!-- End Nav -->
                </div>
                <!-- End Nav Scroller -->
            <?php endif; ?>
        </div>
        <!-- End Page Header -->
        <!-- Page Heading -->
        <div class="row my-2 g-3">
            <!-- Earnings (Monthly) Card Example -->
            <div class="for-card col-md-4">
                <div class="card bg--1 h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
                        <div class="cash--subtitle">
                            <?php echo e(translate('messages.collected_cash_by_restaurant')); ?>

                        </div>
                        <div class="d-flex align-items-center justify-content-center mt-3">
                            <img class="cash-icon mr-3" src="<?php echo e(dynamicAsset('assets/admin/img/transactions/cash.png')); ?>"
                                 alt="transactions">
                            <h2 class="cash--title"><?php echo e(\App\CentralLogics\Helpers::format_currency($wallet->collected_cash)); ?>

                            </h2>
                        </div>
                    </div>
                    <div class="card-footer pt-0 bg-transparent">
                        <a class="btn btn-- bg--title h--45px w-100" href="<?php echo e(route('admin.account-transaction.index')); ?>"
                           title="<?php echo e(translate('messages.goto_account_transaction')); ?>"><?php echo e(translate('messages.collect_cash_from_restaurant')); ?></a>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="row g-3">
                    <!-- Panding Withdraw Card Example -->
                    <div class="col-sm-6">
                        <div class="resturant-card  bg--2">
                            <h4 class="title"><?php echo e(\App\CentralLogics\Helpers::format_currency($wallet->pending_withdraw)); ?>

                            </h4>
                            <span class="subtitle"><?php echo e(translate('messages.pending_withdraw')); ?></span>
                            <img class="resturant-icon" src="<?php echo e(dynamicAsset('assets/admin/img/transactions/pending.png')); ?>"
                                 alt="transactions">
                        </div>
                    </div>

                    <!-- Earnings (Monthly) Card Example -->
                    <div class="col-sm-6">
                        <div class="resturant-card  bg--3">
                            <h4 class="title"><?php echo e(\App\CentralLogics\Helpers::format_currency($wallet->total_withdrawn)); ?></h4>
                            <span class="subtitle"><?php echo e(translate('messages.total_withdrawn_amount')); ?></span>
                            <img class="resturant-icon"
                                 src="<?php echo e(dynamicAsset('assets/admin/img/transactions/withdraw-amount.png')); ?>"
                                 alt="transactions">
                        </div>
                    </div>

                    <!-- Collected Cash Card Example -->
                    <div class="col-sm-6">
                        <div class="resturant-card  bg--5">
                            <?php if($wallet->balance ==  0): ?>
                                <h4 class="title"><?php echo e(\App\CentralLogics\Helpers::format_currency($wallet->balance)); ?></h4>
                                <span class="subtitle"><?php echo e(translate('messages.Balance')); ?></span>
                            <?php elseif($wallet->balance >  0): ?>
                                <h4 class="title"><?php echo e(\App\CentralLogics\Helpers::format_currency($wallet->balance)); ?></h4>
                                <span class="subtitle"><?php echo e(translate('messages.Withdraw_able_balance')); ?></span>
                            <?php else: ?>
                                <h4 class="title"><?php echo e(\App\CentralLogics\Helpers::format_currency(abs($wallet->balance))); ?></h4>
                                <span class="subtitle"><?php echo e(translate('messages.Payable_balance')); ?></span>

                            <?php endif; ?>



                            <img class="resturant-icon"
                                 src="<?php echo e(dynamicAsset('assets/admin/img/transactions/withdraw-balance.png')); ?>"
                                 alt="transactions">
                        </div>
                    </div>

                    <!-- Pending Requests Card Example -->
                    <div class="col-sm-6">
                        <div class="resturant-card  bg--1">
                            <h4 class="title"><?php echo e(\App\CentralLogics\Helpers::format_currency($wallet->total_earning)); ?></h4>
                            <span class="subtitle"><?php echo e(translate('messages.total_earning')); ?></span>
                            <img class="resturant-icon" src="<?php echo e(dynamicAsset('assets/admin/img/transactions/earning.png')); ?>"
                                 alt="transactions">
                        </div>
                    </div>

                </div>

            </div>
        </div>
        <div class="mt-4">
            <div id="restaurant_details" class="row g-3">
                <div class="col-lg-12">
                    <div class="card mt-2">
                        <div class="card-header">
                            <h5 class="card-title m-0 d-flex align-items-center">
                            <span class="card-header-icon mr-2">
                                <i class="tio-shop-outlined"></i>
                            </span>
                                <span class="ml-1"><?php echo e(translate('messages.restaurant_info')); ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center g-3">
                                <div class="col-lg-6">
                                    <div class="resturant--info-address">
                                        <div class="logo">
                                            <img class="onerror-image" data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/100x100/restaurant-default-image.png')); ?>"

                                                 src="<?php echo e($restaurant->logo_full_url ?? dynamicAsset('assets/admin/img/100x100/restaurant-default-image.png')); ?>">
                                        </div>
                                        <ul class="address-info list-unstyled list-unstyled-py-3 text-dark">
                                            <li>
                                                <h5 class="name d-flex align-items-center gap-1 flex-wrap">
                                                    <span><?php echo e($restaurant->name); ?></span>
                                                    <?php echo $__env->make('partials._verified_restaurant_badge', ['restaurant' => $restaurant], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                </h5>
                                            </li>
                                            <li>
                                                <i class="tio-city nav-icon"></i>
                                                <span class="pl-1">
                                                <?php echo e(translate('messages.address')); ?> : <?php echo e($restaurant->address); ?>

                                            </span>
                                            </li>

                                            <li>
                                                <i class="tio-call-talking nav-icon"></i>
                                                <span class="pl-1">
                                                <?php echo e(translate('messages.phone')); ?> : <a class="text-dark" href="tel:<?php echo e($restaurant->phone); ?>"><?php echo e($restaurant->phone); ?></a>
                                            </span>
                                            </li>
                                            <li>
                                                <i class="tio-email nav-icon"></i>
                                                <span class="pl-1">
                                                <?php echo e(translate('messages.email')); ?> :  <a class="text-dark" href="mailto:<?php echo e($restaurant->email); ?>"><?php echo e($restaurant->email); ?></a>
                                            </span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div id="map" class="single-page-map"></div>
                                </div>
                                <div class="col-12">
                                        <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info mb-1" style="--bs-bg-opacity: 0.1;">
                                            <span class="text-info lh-1 fs-14">
                                                <img src="<?php echo e(dynamicAsset('assets/admin/img/svg/bulb.svg')); ?>" class="svg" alt="">
                                            </span>
                                            <span>
                                                <?php if(!is_null($performance_rate)): ?>
                                                    <?php echo e(translate('messages.this_restaurants_performance_is_rated_as')); ?>

                                                    <span class="fz-12px font-semibold <?php echo e($performance_rate >= 80 ? 'text-success' : ($performance_rate >= 50 ? 'text-warning' : 'text-danger')); ?>">
                                                        <a class="<?php echo e($performance_rate >= 80 ? 'text-success' : ($performance_rate >= 50 ? 'text-warning' : 'text-danger')); ?>" href="#0">
                                                            <?php echo e($performance_rate >= 80 ? translate('messages.good') : ($performance_rate >= 50 ? translate('messages.average') : translate('messages.needs_improvement'))); ?>

                                                        </a>
                                                    </span>
                                                    <?php echo e(translate('messages.based_on_its_overall_activity_reliability_and_service_quality')); ?>

                                                <?php else: ?>
                                                    <?php echo e(translate('messages.this_restaurant_does_not_have_much_available_data')); ?>

                                                <?php endif; ?>
                                            </span>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <?php if($restaurant->tin || $restaurant->tin_certificate_image): ?>
                        <div class="row pt-3 g-3">
                            <div class="col-lg-12">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title m-0 d-flex align-items-center">
                                            <span class="card-header-icon mr-2">
                                                <i class="tio-user"></i>
                                            </span>
                                            <span class="ml-1"><?php echo e(translate('TIN Documents')); ?></span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="resturant--info-address flex-sm-nowrap flex-wrap gap-2 single-document-uploaderwrap" data-document-uploader>
                                            <div>
                                                <div class="file-assets"
                                                     data-picture-icon="<?php echo e(dynamicAsset('assets/admin/img/picture.svg')); ?>"
                                                     data-document-icon="<?php echo e(dynamicAsset('assets/admin/img/document.svg')); ?>"
                                                     data-blank-thumbnail="<?php echo e(dynamicAsset('assets/admin/img/picture.svg')); ?>">
                                                </div>
                                                <!-- Upload box -->
                                                <div class="d-flex align-items-center flex-xxl-nowrap flex-wrap gap-2 justify-content-center pdf-container">
                                                    <div class="document-upload-wrapper d-none">
                                                        <input type="file" name="tin_certificate_image" class="document_input" accept=".doc, .pdf, .jpg, .png, .jpeg">
                                                        <div class="textbox">
                                                            <img width="40" height="40" class="svg"
                                                                 src="<?php echo e(dynamicAsset('assets/admin/img/doc-uploaded.png')); ?>"
                                                                 alt="">
                                                            <p class="fs-12 mb-0"><?php echo e(translate('Select a file or')); ?> <span class="font-semibold"><?php echo e(translate('Drag & Drop')); ?></span>
                                                                <?php echo e(translate('here')); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="pdf-single pdf-single-reponsive" data-file-name="${file.name}" data-file-url="<?php echo e($restaurant->tin_certificate_image_full_url ?? dynamicAsset('assets/admin/img/upload-cloud.png')); ?>">
                                                        <div class="pdf-frame">
                                                            <?php
                                                                $imgPath =  $restaurant->tin_certificate_image_full_url ?? dynamicAsset('assets/admin/img/upload-cloud.png');
                                                            ?>
                                                            <?php if(Str::endsWith($imgPath, ['.pdf', '.doc', '.docx'])): ?>
                                                                <?php
                                                                    $imgPath =  dynamicAsset('assets/admin/img/document.svg');
                                                                ?>
                                                            <?php endif; ?>
                                                            <img class="pdf-thumbnail-alt" src="<?php echo e($imgPath); ?>" alt="File Thumbnail">
                                                        </div>
                                                        <div class="overlay">
                                                            <div class="pdf-info">
                                                                <?php if(Str::endsWith($imgPath, ['.pdf', '.doc', '.docx'])): ?>
                                                                    <img src="<?php echo e(dynamicAsset('assets/admin/img/document.svg')); ?>" width="34" alt="File Type Logo">
                                                                <?php else: ?>
                                                                    <img src="<?php echo e(dynamicAsset('assets/admin/img/picture.svg')); ?>" width="34" alt="File Type Logo">
                                                                <?php endif; ?>
                                                                <div class="file-name-wrapper">
                                                                    <span class="file-name js-filename-truncate text-limit-show" data-limit="15"><?php echo e($restaurant->tin_certificate_image); ?></span>
                                                                    <span class="opacity-50"><?php echo e(translate('Click to view the file')); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php if($restaurant->tin): ?>

                                                        <div class="d-flex-column address-info address-info-2 list-unstyled list-unstyled-py-3">
                                                            <div class=" d-flex justify-content-start gap-1">
                                                    <span class="text-custom-nowrap text-wrap"><strong class=" text-dark">
                                                        <?php echo e(translate('Taxpayer Identification Number(TIN)')); ?>: </strong></span>
                                                                <span class="pl-1"><?php echo e($restaurant->tin); ?></span>
                                                            </div>
                                                            <div class=" d-flex justify-content-start gap-1">
                                                        <span class="text-custom-nowrap text-wrap"><strong
                                                                class=" text-dark"><?php echo e(translate('Expire Date')); ?>: </strong></span>
                                                                <span class="pl-1"><?php echo e($restaurant->tin_expire_date); ?></span>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title m-0 d-flex align-items-center">
                            <span class="card-header-icon mr-2">
                                <i class="tio-user"></i>
                            </span>
                                <span class="ml-1"><?php echo e(translate('messages.owner_info')); ?></span>
                            </h5>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-center">
                            <div class="resturant--info-address">
                                <div class="avatar avatar-xxl avatar-circle avatar-border-lg">
                                    <img class="avatar-img onerror-image" data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/160x160/img1.jpg')); ?>"
                                         src="<?php echo e($restaurant?->vendor?->image_full_url ?? dynamicAsset('assets/admin/img/160x160/img1.jpg')); ?>"
                                         alt="Image Description">
                                </div>
                                <ul class="address-info address-info-2 list-unstyled list-unstyled-py-3 text-dark">
                                    <li>
                                        <h5 class="name">
                                            <?php echo e($restaurant->vendor->f_name); ?> <?php echo e($restaurant->vendor->l_name); ?>

                                        </h5>
                                    </li>
                                    <li>
                                        <i class="tio-call-talking nav-icon"></i>
                                        <span class="pl-1">
                                            <a class="text-dark" href="tel:<?php echo e($restaurant->vendor->phone); ?>"><?php echo e($restaurant->vendor->phone); ?></a>
                                        </span>
                                    </li>
                                    <li>
                                        <i class="tio-email nav-icon"></i>
                                        <span class="pl-1">
                                                <a class="text-dark" href="mailto:<?php echo e($restaurant->vendor->email); ?>"><?php echo e($restaurant->vendor->email); ?></a>
                                            </span>

                                    </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title m-0 d-flex align-items-center">
                            <span class="card-header-icon mr-2">
                                <i class="tio-crown"></i>
                            </span>
                                <span class="ml-1"><?php echo e(translate('messages.Business_Plan')); ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="resturant--info-address">
                                <ul class="address-info address-info-2 list-unstyled list-unstyled-py-3 text-dark">

                                    <?php if($restaurant->restaurant_model == 'commission'): ?>
                                        <li>
                                            <span>  <strong><?php echo e(translate('messages.Business_Plan')); ?></span></strong>  <span>:</span> &nbsp; <?php echo e(translate($restaurant->restaurant_model)); ?>

                                        </li>
                                        <?php
                                            $admin_commission = \App\Models\BusinessSetting::where(['key' => 'admin_commission'])->first()?->value;
                                        ?>
                                        <li>
                                            <span><strong><?php echo e(translate('messages.Commission_percentage')); ?></strong></span> <span>:</span> &nbsp; <?php echo e($restaurant->comission > 0 ?  $restaurant->comission : $admin_commission); ?> %
                                        </li>
                                    <?php elseif($restaurant->restaurant_model == 'subscription'): ?>
                                        <li>
                                            <span>  <strong><?php echo e(translate('messages.Business_Plan')); ?></span></strong>  <span>:</span> &nbsp; <?php echo e(translate($restaurant->restaurant_model)); ?> &nbsp;
                                            <?php if($restaurant?->restaurant_sub_update_application->is_trial == '1'): ?>
                                                <small> <span class="badge badge-info" ><?php echo e(translate('messages.Free_trial')); ?></span> </small>
                                            <?php endif; ?>
                                        </li>
                                        <li>
                                            <span> <strong><?php echo e(translate('messages.Package_name')); ?></strong></span> <span>:</span> &nbsp; <?php echo e($restaurant?->restaurant_sub_update_application?->package?->package_name  ?? translate('Pacakge_not_found!!!')); ?>

                                        </li>
                                    <?php elseif($restaurant->restaurant_model == 'unsubscribed' && $restaurant?->restaurant_sub_update_application ): ?>
                                        <li>
                                            <span>  <strong><?php echo e(translate('messages.Business_Plan')); ?></span></strong>  <span>:</span> &nbsp; <?php echo e(translate($restaurant->restaurant_model)); ?> &nbsp;

                                            <small> <span class="badge badge-danger" ><?php echo e(translate('messages.Expired')); ?></span> </small>

                                        </li>
                                        <li>
                                            <span> <strong><?php echo e(translate('messages.Package_name')); ?></strong></span> <span>:</span> &nbsp; <?php echo e($restaurant?->restaurant_sub_update_application?->package?->package_name  ?? translate('Pacakge_not_found!!!')); ?>

                                        </li>
                                    <?php elseif($restaurant->restaurant_model == 'unsubscribed'): ?>
                                        <li>
                                            <span>  <strong><?php echo e(translate('messages.Business_Plan')); ?></span></strong>  <span>:</span> &nbsp; <?php echo e(translate('Plan_isn’t_selected_yet')); ?> &nbsp;
                                        </li>

                                    <?php elseif($restaurant->restaurant_model == 'none' && $restaurant->package_id ): ?>
                                        <li>
                                            <span>  <strong><?php echo e(translate('messages.Business_Plan')); ?></span></strong>  <span>:</span> &nbsp; <?php echo e(translate('messages.Subscription')); ?>

                                        </li>
                                        <li>
                                            <span>  <strong><?php echo e(translate('messages.Package_Name')); ?></span></strong>  <span>:</span> &nbsp; <?php echo e(App\Models\SubscriptionPackage::where('id',$restaurant->package_id)->first()?->package_name); ?>

                                        </li>
                                        <li>
                                            <span>  <strong><?php echo e(translate('Payment_status')); ?></span></strong>  <span>:</span> &nbsp; <?php echo e(translate('messages.payment_failed')); ?>

                                        </li>
                                    <?php else: ?>
                                        <li>
                                            <span>  <strong><?php echo e(translate('messages.Business_Plan')); ?></span></strong>  <span>:</span> &nbsp; <?php echo e(translate('Plan_isn’t_selected_yet.')); ?>

                                        </li>
                                    <?php endif; ?>




                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <?php if($restaurant->additional_data && count(json_decode($restaurant->additional_data, true)) > 0 ): ?>
                        <div class="row mb-2">
                            <div class="col-lg-12  mt-2">
                                <div class="card h-100">

                                    <div class="card-header justify-content-between align-items-center">
                                        <label class="input-label text-capitalize d-inline-flex align-items-center m-0">
                                            <h5 class="d-flex align-items-center gap-2">
                                                <img src="<?php echo e(dynamicAsset('assets/admin/img/company.png')); ?>" alt="">
                                                <span class="line--limit-1 mt-1">
                                            <?php echo e(translate('Additional_Information')); ?>

                                        </span>
                                            </h5>
                                            <span data-toggle="tooltip" data-placement="right"
                                                  data-original-title="<?php echo e(translate('Optional details about restaurant.')); ?>"
                                                  class="input-label-secondary">
                                        <img width="14" src="<?php echo e(dynamicAsset('assets/admin/img/info-circle.svg')); ?>" alt="info"></span>
                                        </label>
                                    </div>
                                    <div class="card-body">
                                        <div class="__registration-information p-0">
                                            <div class="item">
                                                <ul>
                                                    <?php $__currentLoopData = json_decode($restaurant->additional_data, true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php if(is_array($item)): ?>


                                                            <?php $__currentLoopData = $item; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <li>
                                                                    <span class="left fs-14"> <?php echo e($k == 0 ? translate($key) : ''); ?> </span>
                                                                    <span class="right fs-14"><?php echo e($data); ?> </span>
                                                                </li>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        <?php else: ?>
                                                            <li>
                                                                <span class="left fs-14"> <?php echo e(translate($key)); ?> </span>
                                                                <span class="right fs-14"><?php echo e($item ?? translate('messages.N/A')); ?> </span>
                                                            </li>
                                                        <?php endif; ?>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6">
                    <?php echo $__env->make('admin-views.partials._additional_documents', ['documents' => $restaurant->additional_documents, 'prefix' => 'additional_documents'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=<?php echo e(\App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value); ?>&libraries=marker&callback=initMap&v=3.61">
    </script>
    <script src="<?php echo e(dynamicAsset('assets/admin')); ?>/js/file-preview/document-upload.js"></script>
    <script>
        "use strict";

        $(document).ready(function () {
            $('#dataTable').DataTable();
        });

        const mapId = "<?php echo e(\App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value); ?>";
        const { AdvancedMarkerElement } = google.maps.marker;

        const myLatLng = { lat: <?php echo e($restaurant->latitude); ?>, lng: <?php echo e($restaurant->longitude); ?> };
        let map;
        initMap();
        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: myLatLng,
                mapId: mapId,
            });
            new AdvancedMarkerElement({
                map,
                position: myLatLng,
                title: "<?php echo e($restaurant->name); ?>",
            });
        }

        $('.request_alert').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            request_alert(url, message)
        })

        function request_alert(url, message) {
            Swal.fire({
                title: "<?php echo e(translate('messages.are_you_sure_?')); ?>",
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: "<?php echo e(translate('messages.no')); ?>",
                confirmButtonText: "<?php echo e(translate('messages.yes')); ?>",
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = url;
                }
            })
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/vendor/view/index.blade.php ENDPATH**/ ?>