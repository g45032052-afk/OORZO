<?php $__env->startSection('title', translate('Customer_list')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<?php $__env->stopPush(); ?>
<?php $__env->startSection('customerDetails'); ?>
    active
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="content container-fluid">

        <div>
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-sm">
                        <h1 class="page-header-title">
                            <?php echo e(translate('messages.customers')); ?>

                        </h1>
                    </div>
                </div>
                <!-- End Row -->
            </div>
            <!-- End Page Header -->

            <?php if($builder_published): ?>
                
                <ul class="nav nav-tabs border-0 nav--tabs nav--pills mb-3">
                    <li class="nav-item">
                        <a class="nav-link <?php echo e($tab === 'main' ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.customer.list', ['tab' => 'main'])); ?>">
                            <?php echo e(translate('messages.customers')); ?>

                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e($tab === 'storefront' ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.customer.list', ['tab' => 'storefront'])); ?>">
                            <?php echo e(translate('Storefront Customers')); ?>

                        </a>
                    </li>
                </ul>
            <?php endif; ?>

            <!-- End Page Header -->
            <div class="card mb-3">
                <div class="card-body p-10px">
                    <div class="row g-2">
                        <div class="col-lg-3 col-sm-6">
                            <a class="shadow-effect-hover d-flex align-items-center gap-3 p-3 rounded card-bg1 w-100" href="#0">
                                <div class="thumb min-w-45px w-45px h-45px rounded-circle d-center bg-white">
                                    <img class="resturant-icon" width="20"
                                        src="<?php echo e(dynamicAsset('assets/admin/img/customer-g.png')); ?>" alt="img">
                                </div>
                                <div>
                                    <h4 class="title fs-18 font-weight-bold text-dark mb-1"><?php echo e($total_customers); ?></h4>
                                    <span
                                        class="subtitle fs-14 text-dark fw-normal"><?php echo e(translate('messages.Total Customers')); ?></span>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <a class="shadow-effect-hover d-flex align-items-center gap-3 p-3 rounded card-bg2 w-100" href="#0">
                                <div class="thumb min-w-45px w-45px h-45px rounded-circle d-center bg-white">
                                    <img class="resturant-icon" width="20"
                                        src="<?php echo e(dynamicAsset('assets/admin/img/customer-a.png')); ?>" alt="img">
                                </div>
                                <div>
                                    <h4 class="title fs-18 font-weight-bold text-dark mb-1"><?php echo e($active_customers); ?></h4>
                                    <span
                                        class="subtitle fs-14 text-dark fw-normal"><?php echo e(translate('messages.Active Customer')); ?></span>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <a class="shadow-effect-hover d-flex align-items-center gap-3 p-3 rounded card-bg3 w-100" href="#0">
                                <div class="thumb min-w-45px w-45px h-45px rounded-circle d-center bg-white">
                                    <img class="resturant-icon" width="20"
                                        src="<?php echo e(dynamicAsset('assets/admin/img/customer-inactive.png')); ?>"
                                        alt="img">
                                </div>
                                <div>
                                    <h4 class="title fs-18 font-weight-bold text-dark mb-1"><?php echo e($inactive_customers); ?></h4>
                                    <span
                                        class="subtitle fs-14 text-dark fw-normal"><?php echo e(translate('messages.Inactive Customer')); ?></span>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <a class="shadow-effect-hover d-flex align-items-center gap-3 p-3 rounded bg-opacity-warning-10 w-100 position-relative"
                                href="#0">
                                <div class="thumb min-w-45px w-45px h-45px rounded-circle d-center bg-white">
                                    <img class="resturant-icon" width="20"
                                        src="<?php echo e(dynamicAsset('assets/admin/img/customer-add.png')); ?>"
                                        alt="img">
                                </div>
                                <div>
                                    <h4 class="title fs-18 font-weight-bold text-dark mb-1"><?php echo e($new_customers); ?></h4>
                                    <span
                                        class="subtitle fs-14 text-dark fw-normal"><?php echo e(translate('messages.New Customers ')); ?></span>
                                </div>
                                <span class="text-info text-left position-absolute right-0 top-0 m-2" data-toggle="tooltip"
                                    data-placement="right"
                                    data-original-title="<?php echo e(translate('messages.Customers who joined in the last 6 months are considered new customers.')); ?>">
                                    <i class="tio-info fs-14"></i>
                                </span>

                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Card -->

            <!-- Card -->
            <div class="card">
                <!-- Header -->
                <div class="card-header gap-2 flex-wrap pt-4 border-0">
                    <h3 class="m-0">
                        <?php echo e(translate('messages.customer_list')); ?> <span class="badge badge-soft-dark ml-2"
                            id="count"><?php echo e($customers->total()); ?></span>
                    </h3>
                    <div class="search--button-wrapper justify-content-end">
                        <form>
                            
                            <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
                            <?php if($tab === 'storefront' && $storefront_id): ?>
                                <input type="hidden" name="storefront_id" value="<?php echo e($storefront_id); ?>">
                            <?php endif; ?>
                            <!-- Search -->
                            <div class="input--group input-group input-group-merge input-group-flush">
                                <input id="datatableSearch_" type="search" name="search" class="form-control"
                                    value="<?php echo e(request()?->search ?? null); ?>"
                                    placeholder="<?php echo e(translate('Search here')); ?>" aria-label="Search" required>
                                <button type="submit" class="btn btn--secondary">
                                    <i class="tio-search"></i>
                                </button>
                            </div>
                            <!-- End Search -->
                        </form>
                        <?php if($tab === 'storefront' && count($storefronts)): ?>
                            <form method="GET" action="<?php echo e(route('admin.customer.list')); ?>" class="mr-2">
                                <input type="hidden" name="tab" value="storefront">
                                <select name="storefront_id" class="form-control" onchange="this.form.submit()">
                                    <option value=""><?php echo e(translate('All Storefronts')); ?></option>
                                    <?php $__currentLoopData = $storefronts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($sf->id); ?>"
                                            <?php echo e((int) $storefront_id === (int) $sf->id ? 'selected' : ''); ?>>
                                            <?php echo e($sf->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </form>
                        <?php endif; ?>
                        <div class="d-flex flex-wrap gpa-3 justify-content-sm-end align-items-sm-center ml-0 mr-0">
                            <!-- Unfold -->
                            <div class="hs-unfold mr-2">
                                <a class="js-hs-unfold-invoker btn btn-sm btn-outline-primary dropdown-toggle min-height-40 fs-14"
                                    href="javascript:;"
                                    data-hs-unfold-options='{
                                        "target": "#usersExportDropdown",
                                        "type": "css-animation"
                                    }'>
                                    <i class="tio-download-to mr-1"></i> <?php echo e(translate('messages.export')); ?>

                                </a>

                                <div id="usersExportDropdown"
                                    class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                    <span class="dropdown-header"><?php echo e(translate('messages.download_options')); ?></span>
                                    <a id="export-excel" class="dropdown-item"
                                        href="<?php echo e(route('admin.customer.export', ['type' => 'excel', request()->getQueryString()])); ?>">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/excel.svg"
                                            alt="Image Description">
                                        <?php echo e(translate('messages.excel')); ?>

                                    </a>
                                    <a id="export-csv" class="dropdown-item"
                                        href="<?php echo e(route('admin.customer.export', ['type' => 'csv', request()->getQueryString()])); ?>">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/placeholder-csv-format.svg"
                                            alt="Image Description">
                                        .<?php echo e(translate('messages.csv')); ?>

                                    </a>
                                </div>
                            </div>
                            <!-- End Unfold -->
                        </div>
                        <div class="hs-unfold">
                            <?php
                            $filtered = request()->hasAny(['filter', 'order_wise', 'show_limit', 'from_date']); ?>
                            <a class="js-hs-unfold-invoker btn min-w-100px justify-content-center font-medium btn-sm btn-outline-primary filter-show offcanvas-trigger"
                                data-target="#customer_list_offcanvas" href="javascript:">
                                <i class="tio-tune-horizontal mr-1 fs-16"></i> <span
                                    class="mt-1"><?php echo e(translate('Filter')); ?></span>
                                <?php if($filtered): ?>
                                    <span class="filter-dot"></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                    <!-- End Row -->
                </div>
                <!-- End Header -->

                <!-- Table -->
                <div class="px-xxl-20 px-3">
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
                            "paging":false
                        }'>
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0">
                                        <?php echo e(translate('sl')); ?>

                                    </th>
                                    <th class="table-column-pl-0 border-0"><?php echo e(translate('Customer Info')); ?></th>
                                    <?php if($tab === 'storefront'): ?>
                                        <th class="border-0"><?php echo e(translate('Storefront')); ?></th>
                                    <?php endif; ?>
                                    <th class="border-0"><?php echo e(translate('Joining Date')); ?></th>
                                    <th class="border-0"><?php echo e(translate('Total Delivered Order')); ?></th>
                                    <th class="border-0"><?php echo e(translate('Total Spent')); ?></th>
                                    <th class="border-0 "><?php echo e(translate('AOV')); ?></th>
                                    <th class="border-0 text-center"><?php echo e(translate('Last Purchase')); ?></th>
                                    <th class="border-0">
                                        <?php echo e(translate('Active')); ?>/<?php echo e(translate('Inactive')); ?></th>
                                    <th class="border-0"><?php echo e(translate('Actions')); ?></th>
                                </tr>
                            </thead>
                            <?php
                                $count = 0;
                                $storefrontNames = ($storefronts ?? collect())->pluck('name', 'id');
                            ?>
                            <tbody id="set-rows">
                                <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="">
                                        <td class="">
                                            <?php echo e((request()->input('show_limit') ? $count++ : $key) + $customers->firstItem()); ?>

                                        </td>
                                        <td class="table-column-pl-0">
                                            <div class="d-flex align-items-center gap-2 min-w-280">
                                                <div class="position-relative">
                                                    <img class="rounded-circle aspect-1-1 object-cover" width="40"
                                                        src="<?php echo e($customer->image_full_url); ?>" alt="Image Description">
                                                        <?php if($customer->pro_status): ?>
                                                        <?php echo $__env->make('partials._pro_customer_badge', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                        <?php endif; ?>
                                                </div>

                                                <div>
                                                    <a href="<?php echo e(route('admin.customer.view', [$customer['id']])); ?>"
                                                        class="text-dark fw-500 text-hover-primary max-w-215px min-w-135px text-wrap line--limit-1">
                                                        <?php echo e($customer['f_name'] ? $customer['f_name'] . ' ' . $customer['l_name'] : translate('Incomplete_profile')); ?>

                                                    </a>
                                                    <div>
                                                        <div>
                                                            <a href="mailto:<?php echo e($customer['email']); ?>"
                                                                class="gray-dark fs-12 text-hover-primary max-w-215px min-w-170px text-wrap line--limit-1">
                                                                <?php echo e($customer['email']); ?>

                                                            </a>
                                                        </div>
                                                        <div>
                                                            <a href="tel:<?php echo e($customer['phone']); ?>"
                                                                class="gray-dark fs-12 text-hover-primary">
                                                                <?php echo e($customer['phone']); ?>

                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <?php if($tab === 'storefront'): ?>
                                            <td>
                                                <span class="fw-500 text-dark text-wrap"><?php echo e($storefrontNames[$customer->sub_tenant_id] ?? translate('Storefront') . ' #' . $customer->sub_tenant_id); ?></span>
                                            </td>
                                        <?php endif; ?>
                                        <td class=" ">
                                            <div class="d-flex align-items-center gap-2">
                                                <div>
                                                    <div>
                                                        <?php echo e(\App\CentralLogics\Helpers::date_format($customer->created_at)); ?>

                                                    </div>
                                                    <div>
                                                        <?php echo e(\App\CentralLogics\Helpers::time_format($customer->created_at)); ?>

                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <label class="badge">
                                                <?php echo e($customer->orders_count); ?>

                                            </label>
                                        </td>
                                        <td>
                                            <label class="badge">
                                                <?php echo e(\App\CentralLogics\Helpers::format_currency($customer->total_order_amount)); ?>

                                            </label>
                                        </td>
                                        <td>
                                            <span  class="fs-14 text-dark text-center"><?php echo e(\App\CentralLogics\Helpers::format_currency($customer->total_order_amount > 0 && $customer->orders_count > 0 ? $customer->total_order_amount / $customer->orders_count : 0)); ?></span>
                                        </td>

                                        <td>
                                            <span
                                                class="fs-14 text-dark text-center d-block"><?php echo e($customer->lastOrder ? \App\CentralLogics\Helpers::human_time_format($customer->lastOrder->created_at) : translate('messages.N/A')); ?></span>
                                        </td>

                                        <td>
                                            <label class="toggle-switch toggle-switch-sm ml-xl-4 "
                                                for="stocksCheckbox<?php echo e($customer->id); ?>">
                                                <input type="checkbox"
                                                    data-url="<?php echo e(route('admin.customer.status', [$customer->id])); ?>"
                                                    data-status="<?php echo e($customer->status); ?>"
                                                    class="toggle-switch-input status_change_alert"
                                                    id="stocksCheckbox<?php echo e($customer->id); ?>"
                                                    <?php echo e($customer->status ? 'checked' : ''); ?>>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </td>


                                        <td>
                                            <a class="btn action-btn btn-primary btn-outline-primary"
                                                href="<?php echo e(route('admin.customer.view', [$customer['id']])); ?>"
                                                title="<?php echo e(translate('messages.view_customer')); ?>"><i
                                                    class="tio-visible-outlined"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if(count($customers) === 0): ?>
                    <div class="empty--data">
                        <img src="<?php echo e(dynamicAsset('assets/admin/img/empty.png')); ?>" alt="public">
                        <h5>
                            <?php echo e(translate('no_data_found')); ?>

                        </h5>
                    </div>
                <?php endif; ?>
                <!-- End Table -->
                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div>
                            <?php echo $customers->withQueryString()->links(); ?>

                        </div>
                    </div>
                </div>
                <!-- End Footer -->

            </div>
            <!-- End Card -->
        </div>

    </div>


    <!-- Filter Offcanvas -->
    <div id="customer_list_offcanvas" class="custom-offcanvas d-flex flex-column justify-content-between"
        style="--offcanvas-width: 500px">
            <div>
                <form id="filterForm" action="<?php echo e(route('admin.customer.list')); ?>" method="GET">
                <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
                <?php if($tab === 'storefront' && $storefront_id): ?>
                    <input type="hidden" name="storefront_id" value="<?php echo e($storefront_id); ?>">
                <?php endif; ?>
                <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                    <div class="px-3 py-3 d-flex justify-content-between w-100">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <h2 class="mb-0 fs-18 text-title font-medium"><?php echo e(translate('Filter - Customer List')); ?></h2>

                        </div>
                        <button type="button"
                            class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                            aria-label="Close">&times;
                        </button>
                    </div>
                </div>
                <div class="custom-offcanvas-body p-20">
                    <div class="d-flex flex-column gap-20px">
                        <div class="global-bg-box p-xxl-20 p-3 rounded">
                            <span class="fs-14 d-block mb-2 text-title"><?php echo e(translate('Date Range')); ?></span>
                            <div class="bg-white rounded p-xxl-3 p-2 d-flex flex-column gap-1">
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label class="form-label fw-400"><?php echo e(translate('Start Date')); ?></label>
                                        <div class="position-relative">
                                            <input type="date" name="from_date" class="form-control" id="date_from"
                                                value="<?php echo e(request()->input('from_date') ?? null); ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label fw-400"><?php echo e(translate('End Date')); ?></label>
                                        <div class="position-relative">
                                            <input type="date" name="to_date" class="form-control" id="date_to"
                                                value="<?php echo e(request()->input('to_date') ?? null); ?>">
                                        </div>
                                    </div>
                                    <span id="date_error" style="color:red"></span>
                                </div>
                            </div>
                        </div>
                        <div class="global-bg-box p-xxl-20 p-3 rounded">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-400"><?php echo e(translate('Customer Status')); ?></label>
                                    <select name="filter" data-placeholder="<?php echo e(translate('messages.Select_Status')); ?>"
                                        class="form-control js-select2-custom ">
                                        <option value="" selected disabled>
                                            <?php echo e(translate('messages.Select_Status')); ?>

                                        </option>
                                        <option <?php echo e(request()->input('filter') == 'all' ? 'selected' : ''); ?> value="all">
                                            <?php echo e(translate('messages.All_Customers')); ?></option>
                                        <option <?php echo e(request()->input('filter') == 'active' ? 'selected' : ''); ?>

                                            value="active">
                                            <?php echo e(translate('messages.Active_Customers')); ?></option>
                                        <option <?php echo e(request()->input('filter') == 'blocked' ? 'selected' : ''); ?>

                                            value="blocked">
                                            <?php echo e(translate('messages.Inactive_Customers')); ?></option>
                                    </select>
                                </div>
                                <div class="col-md-12 text-capitalize">
                                    <label class="form-label fw-400"><?php echo e(translate('Sort By')); ?></label>
                                    <select name="order_wise"
                                        data-placeholder="<?php echo e(translate('messages.Select Customer Sorting Order')); ?>"
                                        class="form-control js-select2-custom">
                                        <option value="" selected disabled>
                                            <?php echo e(translate('messages.Select Customer Sorting Order')); ?> </option>
                                        <option class="text-capitalize" <?php echo e(request()->input('order_wise') == 'top' ? 'selected' : ''); ?>

                                            value="top">
                                            <?php echo e(translate('Most Ordered')); ?></option>
                                        <option class="text-capitalize" <?php echo e(request()->input('order_wise') == 'order_amount' ? 'selected' : ''); ?>

                                            value="order_amount"><?php echo e(translate('Most Spent')); ?></option>
                                        <option class="text-capitalize" <?php echo e(request()->input('order_wise') == 'oldest' ? 'selected' : ''); ?>

                                            value="oldest">
                                            <?php echo e(translate('Oldest')); ?></option>
                                        <option class="text-capitalize" <?php echo e(request()->input('order_wise') == 'latest' ? 'selected' : ''); ?>

                                            value="latest">
                                            <?php echo e(translate('messages.Default')); ?> (<?php echo e(translate('latest')); ?>) </option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-400"><?php echo e(translate('Choose First')); ?></label>
                                    <input type="number" min="1" name="show_limit" class="form-control"
                                        value="<?php echo e(request()->input('show_limit')); ?>"
                                        placeholder="<?php echo e(translate('Ex : 100')); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div  class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3 position-sticky">
                <a href="<?php echo e(route('admin.customer.list')); ?>"
                    class="btn w-100 btn--reset offcanvas-close"><?php echo e(translate('Reset')); ?></a>
                <button type="submit" id="apply_filter" class="btn w-100 btn--primary"><?php echo e(translate('Apply')); ?></button>
            </form>
            </div>
    </div>
    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>
    <!-- Filter Offcanvas End -->

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
    <script>
        "use strict";
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



document.getElementById('filterForm').addEventListener('submit', function(e) {
    const from = document.getElementById('date_from').value.trim();
    const to   = document.getElementById('date_to').value.trim();
    const errorSpan = document.getElementById('date_error');
    errorSpan.textContent = '';

    if ((from && !to) || (!from && to)) {
        e.preventDefault();
        errorSpan.textContent = "<?php echo e(translate('Both From and To dates must be filled.')); ?>";
        return false;
    }

if (!from && !to && !this.querySelector('select[name="filter"]').value && !this.querySelector('select[name="order_wise"]').value && !this.querySelector('input[name="show_limit"]').value) {
    e.preventDefault();
    toastr.error("<?php echo e(translate('Please select at least one filter option.')); ?>");
    return false;
}



});
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/customer/list.blade.php ENDPATH**/ ?>