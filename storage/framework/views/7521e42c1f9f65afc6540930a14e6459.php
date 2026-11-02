<?php $__env->startSection('title',translate('Customer_Details')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <style>
        .pac-container {
            z-index: 100000 !important;
        }
    </style>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('customerDetails'); ?>
    active
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content container-fluid">
<?php
use App\CentralLogics\Helpers;
?>
        <!-- Customer Details for 8.5 Start -->
        <div class="page-header">
            <div class="row flex-nowrap align-items-center mb-1 g-2">
                <div class="col-sm">
                    <h1 class="page-header-title mb-0 gap-1 flex-wrap">
                       <?php echo e(translate('messages.customer_id')); ?>  <span class="gray-dark"> #<?php echo e($customer['id']); ?></span>
                    </h1>
                </div>
                <div class="col-auto m-0 p-0">
                    <div class="d-flex align-items-center gap-3 flex-wrap">

                        <label class="toggle-switch toggle-switch-xs py-2 px-3 d-flex align-items-center gap-3 cursor-pointer bg-white rounded border min-h-41">
                            <span class="pr-1 d-flex align-items-center switch--label">
                                <span class="fs-14 fw-500 text-title">
                                    <span class="d-sm-inline-block d-none"><?php echo e(translate('messages.Active')); ?></span> <?php echo e(translate('messages.Status')); ?>

                                </span>
                            </span>
                            <input type="checkbox" class="status status_change_alert toggle-switch-input"
                                data-url="<?php echo e(route('admin.customer.status', [$customer->id])); ?>"  data-status="<?php echo e($customer->status); ?>"
                            <?php echo e($customer->status ? 'checked' : ''); ?> >
                            <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <?php echo $__env->make('admin-views.customer.partials._user_details_urls', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="row g-3 mt-lg-2">
            <div class="col-lg-8">
                <div class="single-customer-leftside-bar">
                    <div class="row g-2 mb-2">
                        <div class="col-sm-6">
                            <div class="py-xxl-4 shadow-effect-hover1 global-shadow bg-white gap-10 d-flex rounded-8 gap-2 p--20">
                                <div class="w-40 min-w-40 h-40 rounded-circle d-center bg-white">
                                    <img width="22" src="<?php echo e(dynamicAsset('assets/admin/img/total-spent-new.svg')); ?>" alt="img">
                                </div>
                                <div>
                                    <span class="text-gray fs-12 lh-1"><?php echo e(translate('messages.Total Spent')); ?> </span>
                                    <h5 class="text-color m-0"><?php echo e(\App\CentralLogics\Helpers::format_currency($customer->total_order_amount)); ?></h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="py-xxl-4 shadow-effect-hover1 global-shadow bg-white gap-10 d-flex rounded-8 gap-2 p--20">
                                <div class="w-40 min-w-40 h-40 rounded-circle d-center bg-white">
                                    <img width="22" src="<?php echo e(dynamicAsset('assets/admin/img/last-purchase.svg')); ?>" alt="img">
                                </div>
                                <div>
                                    <span class="text-gray fs-12 lh-1"><?php echo e(translate('messages.Last Purchase')); ?> </span>
                                    <h5 class="text-color m-0"><?php echo e($customer->lastOrder ? \App\CentralLogics\Helpers::human_time_format($customer->lastOrder->created_at) : translate('messages.N/A')); ?></h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="py-xxl-4 shadow-effect-hover1 global-shadow bg-white gap-10 d-flex rounded-8 gap-2 p--20">
                                <div class="w-40 min-w-40 h-40 rounded-circle d-center bg-white">
                                    <img width="22" src="<?php echo e(dynamicAsset('assets/admin/img/order-value.svg')); ?>" alt="img">
                                </div>
                                <div>
                                    <span class="text-gray fs-12 lh-1"><?php echo e(translate('messages.Avg. Order Value')); ?> </span>
                                    <h5 class="text-color m-0"> <?php echo e($customer->total_order_amount > 0 && $customer->orders_count > 0 ? \App\CentralLogics\Helpers::format_currency($customer->total_order_amount / $customer->orders_count) : 0); ?></h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="py-xxl-4 shadow-effect-hover1 global-shadow bg-white gap-10 d-flex rounded-8 gap-2 p--20">
                                <div class="w-40 min-w-40 h-40 rounded-circle d-center bg-white">
                                    <img width="22" src="<?php echo e(dynamicAsset('assets/admin/img/order-price-range.svg')); ?>" alt="img">
                                </div>
                                <div>
                                    <span class="text-gray fs-12 lh-1"> <?php echo e(translate('messages.Order price range')); ?> </span>
                                    <h5 class="text-color m-0"><?php echo e(\App\CentralLogics\Helpers::format_currency($orderRange->min_amount == $orderRange->max_amount ?  0 : $orderRange->min_amount)); ?> - <?php echo e(\App\CentralLogics\Helpers::format_currency($orderRange->max_amount ?? 0)); ?></h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="py-xxl-4 shadow-effect-hover1 global-shadow bg-white gap-10 d-flex rounded-8 p--20 h-100">
                                <div class="py-xxl-4 px-xxl-3 flex-grow-1 global-bg-box py-2 px-2 rounded h-100">
                                    <span class="text-gray fs-12 mb-2 d-block"><?php echo e(translate('messages.Total Ordered Items')); ?> </span>
                                    <div class="d-flex align-items-center gap-1 justify-content-between">
                                        <h4 class="text-title m-0"><?php echo e($itemsCount); ?></h4>

                                        <a href="javascript:void(0)" class="fs-12 theme-clr text-underline offcanvas-trigger data-info-show"
                                            data-target="#offcanvas__customBtn3"
                                            data-id="<?php echo e($customer->id); ?>"
                                            data-url="<?php echo e(route('admin.customer.top-items', [$customer->id])); ?>"><?php echo e(translate('View Top 10')); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="py-xxl-4 shadow-effect-hover1 global-shadow bg-white gap-10 d-flex rounded-8 p--20 h-100">
                                <div class="py-xxl-4 px-xxl-3 flex-grow-1 global-bg-box py-2 px-2 rounded h-100">
                                    <span class="text-gray fs-12 mb-2 d-block"><?php echo e(translate('messages.Total Ordered Restaurants')); ?></span>
                                    <div class="d-flex align-items-center gap-1 justify-content-between">
                                        <h4 class="text-title m-0"><?php echo e($restaurantsCount); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-xxl-4 p-15 global-shadow bg-white rounded-10">
                        <div class="row g-2">
                            <div class="col-lg-3 col-md-4">
                                <div class="shadow-effect-hover1 global-shadow text-center global-bg-box gap-10 d-flex flex-column align-items-center justify-content-center rounded-8 gap-2 p--20 h-100">
                                    <div class="mb-4">
                                        <img width="35" src="<?php echo e(dynamicAsset('assets/admin/img/total-box-list.svg')); ?>" alt="img">
                                    </div>
                                    <div>
                                        <h3 class="m-0" data-text-color="#3C76F1"><?php echo e($orderRange->total_orders); ?></h3>
                                        <span class="text-gray fs-14 lh-1"> <?php echo e(translate('Total Orders')); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-9 col-md-8">
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <div class="py-xxl-4 shadow-effect-hover1 global-shadow global-bg-box gap-10 d-flex rounded-8 gap-2 p-15">
                                            <div class="w-40 min-w-40 h-40 rounded-circle d-center bg-white">
                                                <img width="22" src="<?php echo e(dynamicAsset('assets/admin/img/delivery-new-box.svg')); ?>" alt="img">
                                            </div>
                                            <div>
                                                <h5 class="m-0" data-text-color="#019463"><?php echo e($orderRange->total_delivered ?? 0); ?></h5>
                                                <span class="text-gray fs-12 lh-1"> <?php echo e(translate('Delivered')); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="py-xxl-4 shadow-effect-hover1 global-shadow global-bg-box gap-10 d-flex rounded-8 gap-2 p-15">
                                            <div class="w-40 min-w-40 h-40 rounded-circle d-center bg-white">
                                                <img width="22" src="<?php echo e(dynamicAsset('assets/admin/img/ongoing-new-box.svg')); ?>" alt="img">
                                            </div>
                                            <div>
                                                <h5 class="m-0" data-text-color="#E6A832"><?php echo e($orderRange->total_on_going ?? 0); ?></h5>
                                                <span class="text-gray fs-12 lh-1"> <?php echo e(translate('Ongoing')); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="py-xxl-4 shadow-effect-hover1 global-shadow position-relative global-bg-box gap-10 d-flex rounded-8 gap-2 p-15">
                                            <div class="w-40 min-w-40 h-40 rounded-circle d-center bg-white">
                                                <img width="22" src="<?php echo e(dynamicAsset('assets/admin/img/cancel-new-box.svg')); ?>" alt="img">
                                            </div>
                                            <div>
                                                <h5 class="m-0" data-text-color="#C33D3D"><?php echo e($orderRange->total_canceled ?? 0); ?></h5>
                                                <span class="text-gray fs-12 lh-1"> <?php echo e(translate('Canceled')); ?></span>
                                            </div>
                                            <span class="text-info text-left position-absolute right-0 top-0 m-2" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo e(translate('messages.Failed orders are also counted as canceled orders.')); ?>">
                                                <i class="tio-info fs-14"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="py-xxl-4 shadow-effect-hover1 global-shadow global-bg-box gap-10 d-flex rounded-8 gap-2 p-15">
                                            <div class="w-40 min-w-40 h-40 rounded-circle d-center bg-white">
                                                <img width="22" src="<?php echo e(dynamicAsset('assets/admin/img/refund-new.svg')); ?>" alt="img">
                                            </div>
                                            <div>
                                                <h5 class="m-0" data-text-color="#FF4040"><?php echo e($orderRange->total_refunded ?? 0); ?></h5>
                                                <span class="text-gray fs-12 lh-1"> <?php echo e(translate('Refunded')); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="h-100 single-customer-rightside-bar bg-white global-shadow rounded-8 p-xxl-4 p-15">
                    <div class="dropdown">
                        <button class="btn border-0 p-0 bg-transparent lh-1 fs-18 position-absolute right-0 top-0" type="button" data-toggle="dropdown" aria-expanded="false">
                            <i class="tio-more-horizontal lh-1"></i>
                        </button>
                        <div class="dropdown-menu me-3">

                            <?php if((int) ($customer->sub_tenant_id ?? 0) <= 0): ?>
                            <div class="dropdown-item d-flex align-items-center gap-1 fs-14 text-gray cursor-pointer">
                              <a href="<?php echo e(route('admin.customer.wallet.add-fund', ['customer_id' => $customer->id])); ?>" target="_blank" rel="noopener noreferrer" class="d-flex align-items-center gap-1"> <i class="tio-wallet theme-clr"></i> <?php echo e(translate('Add Fund')); ?> </a>
                            </div>
                            <?php endif; ?>
                            <div class="dropdown-item d-flex align-items-center gap-1 fs-14 text-gray cursor-pointer  addressAdd__modal" data-url="<?php echo e(route('admin.customer.add-customer-address', $customer->id)); ?>" data-target="#addressEdit__modal">
                                <i class="tio-poi theme-clr"></i> <?php echo e(translate('Add Address')); ?>

                            </div>
                        </div>
                    </div>
                    <div class="mb-20">
                        <div class="text-center mb-3 rounded-circle position-relative d-inline-block w-100">
                            <span class="position-relative d-inline-block">
                                <img width="60" height="60" src="<?php echo e($customer->image_full_url); ?>" alt="img" class="rounded-circle">
                                <?php if($customer->pro_status): ?>
                                    <?php echo $__env->make('partials._pro_customer_badge', ['size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="mb-15 text-center">
                            <h4 class="text-color mb-1 lh-1"><?php echo e($customer->full_name); ?></h4>
                            <span class="text-gray fs-12"><?php echo e(translate('Joined on')); ?> <?php echo e(\App\CentralLogics\Helpers::date_format($customer->created_at)); ?></span>
                        </div>
                        <div class="social-media-group gap-3 d-flex align-items-center justify-content-center">
                            <a href="mailto:<?php echo e($customer->email); ?>" class="d-center rounded-10">
                                <i class="tio-messages"></i>
                            </a>
                            <a href="<?php echo e(route('admin.message.list',['tab'=> 'customer','customer_id' => $customer->id])); ?>" target="_blank" class="d-center rounded-10">
                                <i class="tio-sms"></i>
                            </a>
                            <a href="tel:<?php echo e($customer->phone); ?>" class="d-center rounded-10">
                                <i class="tio-call"></i>
                            </a>
                        </div>
                    </div>
                    <div class="global-bg-box p-xxl-4 p-15 d-flex flex-column gap-3 mb-20">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-12 before-info text-gray w-60px"><?php echo e(translate('Phone')); ?></span>
                            <span class="fs-12 text-color"><?php echo e($customer->phone); ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-12 before-info text-gray w-60px"><?php echo e(translate('Email')); ?></span>
                            <span class="fs-12 text-color"><?php echo e($customer->email); ?></span>
                        </div>
                        <?php
                            $addressCount = $customer->addresses ? $customer->addresses()->count() : null  ?>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-12 before-info text-gray w-60px"><?php echo e(translate('Address')); ?></span>
                            <?php if($addressCount > 0): ?>
                            <div class="d-flex align-items-center cursor-pointer">
                                <span class="fs-12 theme-clr offcanvas-trigger" style="text-decoration: underline;" data-target="#saved-address_offcanvas"><?php echo e($addressCount); ?> <?php echo e(translate('Address saved')); ?></span><i class="tio-chevron-right"></i>
                            </div>
                            <?php else: ?>

                                <div class="text-primary d-flex align-items-center gap-1 fs-12 cursor-pointer addressAdd__modal"
                                data-url="<?php echo e(route('admin.customer.add-customer-address', $customer->id)); ?>">
                                <i class="tio-poi theme-clr"></i> <?php echo e(translate('Add Address')); ?>

                            </div>

                            <?php endif; ?>


                        </div>
                    </div>
                    
                    <?php if((int) ($customer->sub_tenant_id ?? 0) <= 0): ?>
                    <div class="d-flex flex-column gap-3">
                        <div class="p-xxl-4 shadow-effect-hover d-flex align-items-center rounded-8 justify-content-between gap-2 p-15" data-bg-color="#F0FFF3">
                           <div>
                                <h4 class="fs-16 m-0" data-text-color="#019463"><?php echo e(\App\CentralLogics\Helpers::format_currency($customer->wallet_balance)); ?></h4>
                                <span class="fs-12" data-text-color="#656566">
                                   <?php echo e(translate('Wallet Balance')); ?>

                                </span>
                           </div>
                            <img width="38" height="38" src="<?php echo e(dynamicAsset('assets/admin/img/new-wallet.svg')); ?>" alt="img">
                        </div>
                        <div class="p-xxl-4 shadow-effect-hover d-flex align-items-center rounded-8 justify-content-between gap-2 p-15" data-bg-color="#FFF9F0">
                           <div>
                                <h4 class="fs-16 m-0" data-text-color="#FFBB38"><?php echo e($customer->loyalty_point ?? 0); ?></h4>
                                <span class="fs-12" data-text-color="#656566">
                                      <?php echo e(translate('Loyalty Points')); ?>

                                </span>
                           </div>
                            <img width="40" height="40" src="<?php echo e(dynamicAsset('assets/admin/img/new-star.svg')); ?>" alt="img">
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>


     <!-- Saved Address Offcanvas -->
    <div id="saved-address_offcanvas" class="custom-offcanvas d-flex flex-column justify-content-between" style="--offcanvas-width: 500px">
        <div>
            <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                <div class="px-3 py-3 d-flex justify-content-between w-100">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h2 class="mb-0 fs-18 text-title font-medium"><?php echo e(translate('Saved Address')); ?></h2>

                    </div>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;
                    </button>
                </div>
            </div>
            <div class="custom-offcanvas-body p-20">
                <div class="d-flex flex-column gap-20px pb-5">

                        <?php $__empty_1 = true; $__currentLoopData = $customer->addresses ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $address): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <div class="global-bg-box p-10px rounded">
                            <div class="d-flex align-items-cetner justify-content-between gap-2 flex-wrap mb-10px">
                                <h5 class="text-title m-0 text-capitalize">
                                    <?php echo e($address->address_type); ?>

                                    
                                </h5>
                                <button type="button" class="btn p-0 bg-transparent text-primary addressEdit__modal"
                                        data-url="<?php echo e(route('admin.customer.edit-customer-address', $address->id)); ?>"
                                                data-address_id="<?php echo e($address->id); ?>"  >
                                    <i class="tio-edit"></i>
                                </button>
                            </div>
                            <div class="bg-white rounded p-10px d-flex flex-column gap-1">
                                <div class="d-flex gap-2">
                                    <span class="before-info w-90px min-w-90 gray-dark fs-12">
                                        <?php echo e(translate('Name')); ?>

                                    </span>
                                    <span class="fs-12 text-title">
                                        <?php echo e($address->contact_person_name); ?>

                                        (<?php echo e($address->contact_person_number); ?>)
                                    </span>
                                </div>
                                <?php if($address->emai): ?>
                                <div class="d-flex gap-2">
                                    <span class="before-info w-90px min-w-90 gray-dark fs-12">
                                        <?php echo e(translate('Email')); ?>

                                    </span>
                                    <span class="fs-12 text-title">
                                        <?php echo e($address->email); ?>

                                    </span>
                                </div>
                                <?php endif; ?>
                                <div class="d-flex gap-2">
                                    <span class="before-info w-90px min-w-90 gray-dark fs-12">
                                        <?php echo e(translate('Address Type')); ?>

                                    </span>
                                    <span class="fs-12 text-title">
                                        <?php echo e(ucfirst($address->address_type)); ?>

                                    </span>
                                </div>

                                <?php if($address->road ): ?>
                                <div class="d-flex gap-2">
                                    <span class="before-info w-90px min-w-90 gray-dark fs-12">
                                        <?php echo e(translate('Road')); ?>

                                    </span>
                                    <span class="fs-12 text-title">
                                        <?php echo e($address->road); ?>

                                    </span>
                                </div>
                                <?php endif; ?>


                                <?php if($address->house): ?>
                                <div class="d-flex gap-2">
                                    <span class="before-info w-90px min-w-90 gray-dark fs-12">
                                        <?php echo e(translate('house')); ?>

                                    </span>
                                    <span class="fs-12 text-title">
                                        <?php echo e($address->house); ?>

                                    </span>
                                </div>

                                <?php endif; ?>

                                <?php if($address->floor): ?>
                                <div class="d-flex gap-2">
                                    <span class="before-info w-90px min-w-90 gray-dark fs-12">
                                        <?php echo e(translate('Floor')); ?>

                                    </span>
                                    <span class="fs-12 text-title">
                                        <?php echo e($address->floor); ?>

                                    </span>
                                </div>

                                <?php endif; ?>
                                <div class="d-flex gap-2">
                                    <span class="before-info align-items-start w-90px min-w-90 gray-dark fs-12">
                                        <?php echo e(translate('Address')); ?>

                                    </span>
                                    <span class="fs-12 text-title">
                                        <?php if($address->latitude && $address->longitude): ?>
                                        <a
                                        href="https://www.google.com/maps/search/?api=1&query=<?php echo e($address->latitude); ?>,<?php echo e($address->longitude); ?>"
                                        target="_blank"  class="fs-12 text-title" >
                                        <?php echo e($address->address); ?>  </a>
                                        <?php else: ?>
                                        <?php echo e($address->address); ?>

                                        <?php endif; ?>


                                    </span>
                                </div>
                            </div>
                        </div>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                        <?php endif; ?>

                </div>
            </div>
        </div>
        <div class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3 position-sticky">
            <button type="button"  data-url="<?php echo e(route('admin.customer.add-customer-address', $customer->id)); ?>"
            class="btn w-100 btn--primary addressAdd__modal"><?php echo e(translate('Add New Address')); ?></button>
        </div>
    </div>







    <div id="offcanvas__customBtn3" class="custom-offcanvas d-flex flex-column justify-content-between">
        <div id="data-view" class="h-100">  </div>
    </div>
    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>




    <!--- Address Add Modal --->
    <div id="data-view-address-empty-address" class=""></div>

    <!--- Address Edit Modal --->
    <div id="data-view-address" class=""></div>



        <!-- Confirmation modal -->
    <div class="modal fade" id="confirmation_modal_customer" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
        aria-hidden="true">
        <div class=" modal-dialog max-w-500px mx-auto modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <form class="set_status_url" method="post">
                            <?php echo method_field('put'); ?>
                            <?php echo csrf_field(); ?>
                            <div class="text-center">
                                <div>
                                    <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription-plan/package-status-disable.png')); ?>"
                                        class="mb-20">

                                    <h5 class="modal-title"></h5>
                                </div>
                                <div class="text-center pb-0">
                                    <h3 class="mb-2 fs-18"> <?php echo e(translate('Are you sure ?')); ?></h3>
                                    <div>
                                        <p><?php echo e(translate('You want to Active this customer.')); ?></h3>
                                        </p>
                                    </div>
                                </div>

                                <div class="btn--container justify-content-center mt-4 pt-1">
                                    <button data-dismiss="modal"
                                        class="btn btn--reset min-w-120"><?php echo e(translate('No')); ?></button>
                                    <button type="submit" id=""
                                        class="btn btn--primary min-w-120"><?php echo e(translate('Yes')); ?></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Confirmation modal -->
    <div class="modal fade" id="confirmation_modal_customer_inactive" tabindex="-1" role="dialog"
        aria-labelledby="modalLabel" aria-hidden="true">
        <div class=" modal-dialog max-w-500px mx-auto modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <form class="set_status_url" method="post">
                            <?php echo method_field('put'); ?>
                            <?php echo csrf_field(); ?>
                            <div class="text-center">
                                <div>
                                    <img src="<?php echo e(dynamicAsset('assets/admin/img/subscription-plan/package-status-disable.png')); ?>"
                                        class="mb-20">

                                    <h5 class="modal-title"></h5>
                                </div>
                                <div class="text-center pb-0">
                                    <h3 class="mb-2 fs-18"> <?php echo e(translate('Are you sure ?')); ?></h3>
                                    <div>
                                        <p><?php echo e(translate('You want to Inactive this customer.')); ?></h3>
                                        </p>
                                    </div>
                                </div>

                                <div class="btn--container justify-content-center mt-4 pt-1">
                                    <button type="submit" id=""
                                        class="btn btn--danger min-w-120"><?php echo e(translate('Yes')); ?></button>
                                    <button data-dismiss="modal"
                                        class="btn btn--reset min-w-120"><?php echo e(translate('No')); ?></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>


  <script src="<?php echo e(dynamicAsset('assets/admin/js/view-pages/offcanvas-edit.js')); ?>"></script>

    <script  src="https://maps.googleapis.com/maps/api/js?key=<?php echo e(Helpers::get_business_settings('map_api_key')); ?>&libraries=places,marker&callback=initMap&v=3.61"></script>

   <script>

        function fetch_address_data(url) {
            $.ajax({
                url: url,
                type: "get",
                beforeSend: function () {
                    $('#data-view-address-empty-address').empty();
                     $('#data-view-address').empty();
                    $('#loading').show()
                },
                success: function (data) {
                    $("#data-view-address").append(data.view);
                    $('#addressEdit__modal').modal('show');

                    initTextMaxLimit();
                    initMap(data.latitude, data.longitude);
                    initTelInputs();
                },
                complete: function () {
                    $('#loading').hide()
                }
            })
        }

        function fetch_address_empty_data(url) {
            $.ajax({
                url: url,
                type: "get",
                beforeSend: function () {
                    $('#data-view-address-empty-address').empty();
                     $('#data-view-address').empty();
                    $('#loading').show()
                },
                success: function (data) {
                    $("#data-view-address-empty-address").append(data.view);
                    $('#addressAdd__modal').modal('show');
                    initMap();
                    initTelInputs();
                },
                complete: function () {
                    $('#loading').hide()
                }
            })
        }


        $('.addressEdit__modal').on('click', function(event) {
            event.preventDefault();
            let address_id = $(this).data('address_id') || null;
            $('#address_id').val(address_id);
            let url = $(this).data('url');
            fetch_address_data(url);

        })
        $('.addressAdd__modal').on('click', function(event) {
            event.preventDefault();
            let url = $(this).data('url');
            fetch_address_empty_data(url);
        })

        $('.status_change_alert').on('click', function(event) {
            event.preventDefault();
            let url = $(this).data('url');
            let status = $(this).data('status');
            $('.set_status_url').attr('action', url);
            if (status) {
                $('#confirmation_modal_customer_inactive').modal().show();
            } else {
                $('#confirmation_modal_customer').modal().show();
            }
        })


        function initMap(lat = null, lng = null) {
            const defaultLat = <?php echo e(23.757989); ?>;
            const defaultLng = <?php echo e(90.360587); ?>;
            const mapId = "<?php echo e(Helpers::get_business_settings('map_api_key')); ?>";

            const { AdvancedMarkerElement } = google.maps.marker;

                const map = new google.maps.Map(document.getElementById("location_map_canvas"), {
                    zoom: 13,
                    center: {
                        lat: lat != null ? parseFloat(lat) : defaultLat,
                        lng: lng != null ? parseFloat(lng) : defaultLng
                    },
                    mapId: mapId,
                });

            const geocoder = new google.maps.Geocoder();
            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);

            input.addEventListener("keydown", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                }
            });

            let marker = new AdvancedMarkerElement({
                map,
                draggable: true
            });

            if (lat != null && lng != null) {
                setLocation(parseFloat(lat), parseFloat(lng));

            }
            map.addListener("click", (e) => {
                setLocation(e.latLng.lat(), e.latLng.lng());
            });


            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();
                if (places.length === 0) return;

                const place = places[0];
                if (!place.geometry || !place.geometry.location) return;

                setLocation(place.geometry.location.lat(), place.geometry.location.lng());
                map.panTo(place.geometry.location);
                map.setZoom(15);
            });

            marker.addListener("dragend", () => {
                const pos = marker.getPosition();
                setLocation(pos.lat(), pos.lng());
            });

            function setLocation(lat, lng) {
                document.getElementById("latitude").value = lat;
                document.getElementById("longitude").value = lng;

                const latlng = { lat: lat, lng: lng };
                marker.position = latlng;

                geocoder.geocode({ location: latlng }, (results, status) => {
                    if (status === "OK" && results[0]) {
                        document.getElementById("address").value = results[0].formatted_address;
                    }
                });
            }
        }


    </script>

<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/customer/customer-details/customer-view.blade.php ENDPATH**/ ?>