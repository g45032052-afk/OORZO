<?php $__env->startSection('title',$restaurant->name."'s".translate('messages.order')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <!-- Custom styles for this page -->
    <link href="<?php echo e(dynamicAsset('assets/admin/css/croppie.css')); ?>" rel="stylesheet">

<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="content container-fluid">
    <!-- Page Header -->
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
    <!-- End Page Header -->
    <div class="resturant-card-navbar">

            <div class="order-info-item redirect-url" data-url="<?php echo e(route('admin.order.list',['all'])); ?>?vendor[]=<?php echo e($restaurant->id); ?>">
                <div class="order-info-icon">
                    <img src="<?php echo e(dynamicAsset('assets/admin/img/resturant/navbar/all.png')); ?>" alt="public">
                </div>
                    <h6 class="card-subtitle"><?php echo e(translate('messages.all_orders')); ?> <span class="amount text--primary"><?php echo e(\App\Models\Order::where('restaurant_id', $restaurant->id)->Notpos()->count()); ?></span></h6>
            </div>
            <span class="order-info-seperator"></span>
            <div class="order-info-item redirect-url" data-url="<?php echo e(route('admin.order.list',['scheduled'])); ?>?vendor[]=<?php echo e($restaurant->id); ?>">
                <div class="order-info-icon">
                    <img src="<?php echo e(dynamicAsset('assets/admin/img/resturant/navbar/schedule.png')); ?>" alt="public">
                </div>
                <h6 class="card-subtitle"><?php echo e(translate('messages.scheduled_orders')); ?>

                <span class="amount text--warning"><?php echo e(\App\Models\Order::Scheduled()->Notpos()->where('restaurant_id', $restaurant->id)->count()); ?></span></h6>
            </div>
            <span class="order-info-seperator"></span>
            <div class="order-info-item redirect-url" data-url="<?php echo e(route('admin.order.list',['pending'])); ?>?vendor[]=<?php echo e($restaurant->id); ?>">
                <div class="order-info-icon">
                    <img src="<?php echo e(dynamicAsset('assets/admin/img/resturant/navbar/pending.png')); ?>" alt="public">
                </div>
                <h6 class="card-subtitle"><?php echo e(translate('messages.pending_orders')); ?>

                <span class="amount text--info">
                <?php echo e(\App\Models\Order::where(['order_status'=>'pending','restaurant_id'=>$restaurant->id])->Notpos()->count()); ?></span></h6>
            </div>
            <span class="order-info-seperator"></span>
            <div class="order-info-item redirect-url" data-url="<?php echo e(route('admin.order.list',['delivered'])); ?>?vendor[]=<?php echo e($restaurant->id); ?>">
                <div class="order-info-icon">
                    <img src="<?php echo e(dynamicAsset('assets/admin/img/resturant/navbar/delivered.png')); ?>" alt="public">
                </div>
                <h6 class="card-subtitle"><?php echo e(translate('messages.delivered_orders')); ?>

                <span class="amount text--success"><?php echo e(\App\Models\Order::where(['order_status'=>'delivered', 'restaurant_id'=>$restaurant->id])->Notpos()->count()); ?></span></h6>
            </div>
            <span class="order-info-seperator"></span>
            <div class="order-info-item redirect-url" data-url="<?php echo e(route('admin.order.list',['canceled'])); ?>?vendor[]=<?php echo e($restaurant->id); ?>">
                <div class="order-info-icon">
                    <img src="<?php echo e(dynamicAsset('assets/admin/img/resturant/navbar/cancel.png')); ?>" alt="public">
                </div>
                <h6 class="card-subtitle"><?php echo e(translate('messages.canceled_orders')); ?>

                <span class="amount text--danger"><?php echo e(\App\Models\Order::where(['order_status'=>'canceled', 'restaurant_id'=>$restaurant->id])->count()); ?></span></h6>
            </div>

    </div>
    <!-- End Page Header -->
    <!-- Page Heading -->
    <div class="card">
        <!-- Card Header -->
        <div class="card-header py-2 border-0">
            <div class="search--button-wrapper">
                <span class="mr-auto">&nbsp;</span>
                <form  class="my-2 ml-auto mr-sm-2 mr-xl-4 ml-sm-auto flex-grow-1 flex-grow-sm-0">
                    <!-- Search -->

                    <input type="hidden" name="restaurant_id" value="<?php echo e($restaurant->id); ?>">
                    <div class="input--group input-group input-group-merge input-group-flush">
                        <input id="datatableSearch_" type="search" name="search" class="form-control" value="<?php echo e(request()?->search ?? null); ?>"
                                placeholder="<?php echo e(translate('messages.Search_by_ID')); ?>" aria-label="<?php echo e(translate('messages.search')); ?>" required>
                        <button type="submit" class="btn btn--secondary secondary-cmn"><i class="tio-search"></i></button>

                    </div>
                    <!-- End Search -->
                </form>
                <!-- Static Export Button -->
                <div class="hs-unfold ml-3">
                    <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm" href="javascript:;"
                        data-hs-unfold-options='{
                            "target": "#usersExportDropdown",
                            "type": "css-animation"
                        }'>
                        <i class="tio-download-to mr-1"></i> <?php echo e(translate('messages.export')); ?>

                    </a>

                    <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">

                        <span class="dropdown-header"><?php echo e(translate('messages.download_options')); ?></span>

                        <a target="__blank" id="export-excel" class="dropdown-item" href="<?php echo e(route('admin.order.export-orders', ['type'=>'excel', 'restaurant_id'=>$restaurant->id , request()->getQueryString()])); ?>">
                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                            src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/excel.svg"
                            alt="Image Description">
                            <?php echo e(translate('messages.excel')); ?>

                        </a>
                        <a target="__blank" id="export-csv" class="dropdown-item" href="<?php echo e(route('admin.order.export-orders', ['type'=>'csv', 'restaurant_id'=>$restaurant->id , request()->getQueryString()])); ?>">
                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                            src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/placeholder-csv-format.svg"
                            alt="Image Description">
                            <?php echo e(translate('messages.csv')); ?>

                        </a>


                    </div>
                </div>
                <!-- Static Export Button -->
            </div>
        </div>
        <!-- Card Header -->
        <div class="card-body p-0">
            <!-- Table -->
            <div class="table-responsive datatable-custom">
                <table id="datatable"
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                    data-hs-datatables-options='{
                    "columnDefs": [{
                        "targets": [0],
                        "orderable": false
                    }],
                    "order": [],
                    "info": {
                    "totalQty": "#datatableWithPaginationInfoTotalQty"
                    },
                    "search": "#datatableSearch",
                    "entries": "#datatableEntries",
                    "pageLength": 25,
                    "isResponsive": false,
                    "isShowPaging": false,
                    "pagination": "datatablePagination"
                }'>
                    <thead class="thead-light">
                    <tr>
                        <th class="text-center pl-4 w-100px">
                            <?php echo e(translate('sl')); ?>

                        </th>
                        <th class="table-column-pl-0"><?php echo e(translate('messages.order_id')); ?></th>
                        <th>
                            <div class="pl-2">
                                <?php echo e(translate('messages.order_date')); ?>

                            </div>
                        </th>
                        <th><?php echo e(translate('messages.customer_info')); ?></th>
                        <th><?php echo e(translate('messages.total_amount')); ?></th>
                        <th><?php echo e(translate('messages.order_status')); ?></th>
                        <th class="w-100px"><?php echo e(translate('messages.action')); ?></th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                        <tr class="status-<?php echo e($order['order_status']); ?> class-all">
                            <td class="text-center pl-4">
                                <?php echo e($key+ $orders->firstItem()); ?>

                            </td>
                            <td class="table-column-pl-0">
                                <a class="text--title" href="<?php echo e(route('admin.order.details',['id'=>$order['id']])); ?>"><?php echo e($order['id']); ?></a>
                            </td>
                            <td>
                                <div class="d-inline-block text-right text-uppercase">
                                    <span class="d-block"><?php echo e(date('d-m-Y',strtotime($order['created_at']))); ?></span>
                                    <span class="d-block"><?php echo e(date(config('timeformat'),strtotime($order['created_at']))); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="text-center d-inline-block customer-info-table-data">
                                    <?php if($order->is_guest): ?>
                                         <?php
                                        $customer_details = json_decode($order['delivery_address'],true);
                                    ?>
                                        <strong><?php echo e($customer_details['contact_person_name']); ?></strong>
                                        <div><?php echo e($customer_details['contact_person_number']); ?></div>
                                    <?php elseif($order->customer): ?>
                                        <a class="text-capitalize" href="<?php echo e(route('admin.customer.view',[$order['user_id']])); ?>">
                                            <span class="d-block"><?php echo e($order->customer['f_name'].' '.$order->customer['l_name']); ?></span>
                                            <small class="d-block"><?php echo e($order->customer['phone']); ?></small>
                                        </a>
                                    <?php else: ?>
                                        <label class="badge badge-danger"><?php echo e(translate('messages.invalid_customer_data')); ?></label>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="d-inline-block text-right total-amount-table-data">
                                    <div class="paid--amount-status">
                                        <?php echo e(\App\CentralLogics\Helpers::format_currency($order['order_amount'])); ?>

                                    </div>
                                    <?php if($order->payment_status=='paid'): ?>
                                        <strong class="text--success order--status">
                                            <?php echo e(translate('messages.paid')); ?>

                                        </strong>
                                    <?php else: ?>
                                        <strong class="text--danger order--status">
                                            <?php echo e(translate('messages.unpaid')); ?>

                                        </strong>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-capitalize">
                                <?php if($order['order_status']=='pending'): ?>
                                    <span class="badge badge-soft-info badge--pending">
                                        <?php echo e(translate('messages.pending')); ?>

                                    </span>
                                <?php elseif($order['order_status']=='confirmed'): ?>
                                    <span class="badge badge-soft-info ">
                                        <?php echo e(translate('messages.confirmed')); ?>

                                    </span>
                                <?php elseif($order['order_status']=='processing'): ?>
                                    <span class="badge badge-soft-warning">
                                        <?php echo e(translate('messages.processing')); ?>

                                    </span>
                                <?php elseif($order['order_status']=='out_for_delivery'): ?>
                                    <span class="badge badge-soft-warning badge--on-the-way">
                                        <?php echo e(translate('messages.out_for_delivery')); ?>

                                    </span>
                                <?php elseif($order['order_status']=='delivered'): ?>
                                    <span class="badge badge-soft-success ">
                                        <?php echo e($order?->order_type == 'dine_in' ? translate('messages.Completed') : translate('messages.delivered')); ?>

                                    </span>
                                <?php elseif($order['order_status']=='accepted'): ?>
                                    <span class="badge badge-soft-success badge--accepted">
                                        <?php echo e(translate('messages.accepted')); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-soft-danger badge--cancel">
                                        <?php echo e(translate(str_replace('_',' ',$order['order_status']))); ?>

                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a class="btn btn-sm btn--warning btn-outline-warning action-btn"
                                href="<?php echo e(route('admin.order.details',['id'=>$order['id']])); ?>">
                                    <i class="tio-invisible"></i>
                                </a>
                            </td>
                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <!-- End Table -->
        </div>
        <!-- Footer -->
        <div class="page-area px-4 pb-3">
            <div class="d-flex align-items-center justify-content-end">
                <div>
                    <?php echo $orders->links(); ?>

                </div>
            </div>
        </div>
        <!-- End Footer -->
        <!-- End Card -->
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <!-- Page level plugins -->
    <script>
        "use strict";
        // Call the dataTables jQuery plugin
        $(document).ready(function () {
            $('#dataTable').DataTable();
        });

        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function () {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });

            $('#column2_search').on('keyup', function () {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });

            $('#column3_search').on('change', function () {
                datatable
                    .columns(3)
                    .search(this.value)
                    .draw();
            });

            $('#column4_search').on('keyup', function () {
                datatable
                    .columns(4)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
    </script>

<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/vendor/view/order.blade.php ENDPATH**/ ?>