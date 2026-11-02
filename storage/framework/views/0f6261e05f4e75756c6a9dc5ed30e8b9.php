<?php $__env->startSection('title', $restaurant->name . "'s" . translate('messages.Food')); ?>

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
                    <a class="hs-nav-scroller-arrow-link" href="javascript:">
                        <i class="tio-chevron-left"></i>
                    </a>
                </span>

                <span class="hs-nav-scroller-arrow-next initial-hidden">
                    <a class="hs-nav-scroller-arrow-link" href="javascript:">
                        <i class="tio-chevron-right"></i>
                    </a>
                </span>

                <!-- Nav -->
                <?php echo $__env->make('admin-views.vendor.view.partials._header', ['restaurant' => $restaurant], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <!-- End Nav -->
            </div>
            <!-- End Nav Scroller -->
        </div>
        <!-- End Page Header -->

        <!-- End Page Header -->

        <div class="resturant-card-navbar px-xl-4 justify-content-evenly">
            <div class="order-info-item">
                <div class="order-info-icon icon-sm">
                    <img src="<?php echo e(dynamicAsset('assets/admin/img/resturant/foods/all.png')); ?>" alt="public">
                </div>
                <?php (  $food = \App\Models\Food::withoutGlobalScope(\App\Scopes\RestaurantScope::class)->where(['restaurant_id' => $restaurant->id])->count()); ?>
                <?php ($food = $food == null ? 0 : $food); ?>
                <h6 class="card-subtitle"><?php echo e(translate('messages.all')); ?><span
                        class="amount text--primary"><?php echo e($food); ?></span></h6>
            </div>
            <span class="order-info-seperator"></span>
            <div class="order-info-item">
                <div class="order-info-icon icon-sm">
                    <img src="<?php echo e(dynamicAsset('assets/admin/img/resturant/foods/active.png')); ?>" alt="public">
                </div>
                <?php (
    $food = \App\Models\Food::withoutGlobalScope(\App\Scopes\RestaurantScope::class)->where(['restaurant_id' => $restaurant->id, 'status' => 1])->count()); ?>
                <?php ($food = $food == null ? 0 : $food); ?>
                <h6 class="card-subtitle"><?php echo e(translate('Active_Food')); ?><span
                        class="amount text--primary"><?php echo e($food); ?></span></h6>
            </div>
            <span class="order-info-seperator"></span>
            <div class="order-info-item">
                <div class="order-info-icon icon-sm">
                    <img src="<?php echo e(dynamicAsset('assets/admin/img/resturant/foods/inactive.png')); ?>" alt="public">
                </div>
                <?php (
    $food = \App\Models\Food::withoutGlobalScope(\App\Scopes\RestaurantScope::class)->where(['restaurant_id' => $restaurant->id, 'status' => 0])->count()); ?>
                <?php ($food = $food == null ? 0 : $food); ?>
                <h6 class="card-subtitle"><?php echo e(translate('Inactive_Food')); ?><span
                        class="amount text--primary"><?php echo e($food); ?></span></h6>
            </div>
        </div>
        <!-- End Page Header -->
        <!-- Page Heading -->
        <div class="card h-100">
            <div class="card-header flex-wrap border-0 py-2">
                <div class="search--button-wrapper">
                    <h3 class="card-title d-flex align-items-center"> <span class="card-header-icon mr-1"><i
                                class="tio-restaurant"></i></span> <?php echo e(translate('messages.foods')); ?> <span
                            class="badge badge-soft-dark ml-2 badge-circle"><?php echo e($foods->total()); ?></span></h3>
                    <form class="my-2 ml-auto mr-sm-2 mr-xl-4 ml-sm-auto flex-grow-1 flex-grow-sm-0">
                        <!-- Search -->
                        <input type="hidden" name="restaurant_id" value="<?php echo e($restaurant->id); ?>">
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input id="datatableSearch_" type="search" name="search" class="form-control"
                                value="<?php echo e(request()?->search ?? null); ?>" placeholder="<?php echo e(translate('Search_by_name.')); ?>"
                                aria-label="<?php echo e(translate('messages.search')); ?>" required>
                            <button type="submit" class="btn btn--secondary secondary-cmn"><i class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>
                    <!-- Static Export Button -->
                    <div class="hs-unfold ml-3 mr-3">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm"
                            href="javascript:"
                            data-hs-unfold-options='{
                            "target": "#usersExportDropdown",
                            "type": "css-animation"
                        }'>
                            <i class="tio-download-to mr-1"></i> <?php echo e(translate('messages.export')); ?>

                        </a>

                        <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">

                            <span class="dropdown-header"><?php echo e(translate('messages.download_options')); ?></span>

                            <a target="__blank" id="export-excel" class="dropdown-item"
                                href="<?php echo e(route('admin.food.restaurant-food-export', ['type' => 'excel', 'restaurant_id' => $restaurant->id, request()->getQueryString()])); ?>">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/excel.svg"
                                    alt="Image Description">
                                <?php echo e(translate('messages.excel')); ?>

                            </a>

                            <a target="__blank" id="export-csv" class="dropdown-item"
                                href="<?php echo e(route('admin.food.restaurant-food-export', ['type' => 'csv', 'restaurant_id' => $restaurant->id, request()->getQueryString()])); ?>">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                <?php echo e(translate('messages.csv')); ?>

                            </a>
                        </div>
                    </div>
                    <!-- Static Export Button -->
                    <a href="<?php echo e(route('admin.food.add-new', ['restaurant_id' => $restaurant->id])); ?>" class="btn btn--primary pull-right"><i
                            class="tio-add-circle"></i> <?php echo e(translate('messages.add_new_food')); ?></a>
                </div>
            </div>
            <div class="table-responsive datatable-custom">
                <table id="columnSearchDatatable"
                    class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                    data-hs-datatables-options='{
                        "order": [],
                        "orderCellsTop": true,
                        "paging": false
                    }'>
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center pl-4 w-100px"><?php echo e(translate('messages.sl')); ?></th>
                            <th class="w-120px"><?php echo e(translate('messages.name')); ?></th>
                            <th class="w-120px"><?php echo e(translate('messages.category')); ?></th>
                            <th class="text-center w-120px pr-80px">
                                <?php echo e(translate('messages.price')); ?>

                            </th>
                            <?php if($productWiseTax): ?>
                                <th class="w-100px"><?php echo e(translate('messages.Vat/Tax')); ?></th>
                            <?php endif; ?>
                            <th class="w-100px"><?php echo e(translate('messages.status')); ?></th>
                            <th class="w-60px text-center"><?php echo e(translate('messages.action')); ?></th>
                        </tr>
                    </thead>

                    <tbody id="set-rows">

                        <?php $__currentLoopData = $foods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $food): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($stock_out = null); ?>

                            <tr>
                                <td class="text-center">

                                    <?php echo e($key + $foods->firstItem()); ?>

                                </td>
                                <td class="py-2">
                                    <a class="media align-items-center"
                                        href="<?php echo e(route('admin.food.view', [$food['id']])); ?>">
                                        <img class="avatar avatar-lg mr-3 onerror-image"
                                            src="<?php echo e($food['image_full_url'] ?? dynamicAsset('assets/admin/img/100x100/food-default-image.png')); ?>"
                                            data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/100x100/food-default-image.png')); ?>"
                                            alt="<?php echo e($food->name); ?> image">
                                        <div class="media-body">
                                            <h5 class="text-hover-primary mb-0"><?php echo e(Str::limit($food['name'], 20, '...')); ?>



                                                <?php if($food->stock_type != 'unlimited' && $food->item_stock <= 0): ?>
                                                    <?php ($stock_out = true); ?>

                                                    <span
                                                        class="badge badge-soft-warning badge-pill font-medium"><?php echo e(translate('Out Of Stock')); ?></span>
                                                    <span class="input-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="<?php echo e(translate('messages.Your_main_stock_is_out_of_stock.')); ?>"><img
                                                            src="<?php echo e(dynamicAsset('assets/admin/img/info-circle.svg')); ?>"
                                                            alt="public/img"></span>
                                                <?php else: ?>
                                                    <?php

                                                    if (isset($food->variations)) {
                                                        foreach (json_decode($food->variations, true) as $item) {
                                                            if (isset($item['values']) && is_array($item['values'])) {
                                                                foreach ($item['values'] as $value) {
                                                                    if (isset($value['stock_type']) && $value['stock_type'] != 'unlimited' && $value['current_stock'] <= 0) {
                                                                        $stock_out = true;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    <?php if($stock_out): ?>
                                                        
                                                        <span class="input-label-secondary" data-toggle="tooltip"
                                                            data-placement="right"
                                                            data-original-title="<?php echo e(translate('messages.One_or_more_variations_are_out_of_stock.')); ?>"><img
                                                                src="<?php echo e(dynamicAsset('assets/admin/img/info-circle.svg')); ?>"
                                                                alt="public/img"></span>
                                                    <?php endif; ?>
                                                <?php endif; ?>


                                            </h5>
                                        </div>
                                    </a>
                                </td>
                                <td>
                                    <div>
                                        <?php echo e(Str::limit(
                                            ($food?->category?->parent ? $food?->category?->parent?->name : $food?->category?->name) ??
                                                translate('messages.uncategorize'),
                                            20,
                                            '...',
                                        )); ?>

                                    </div>
                                </td>
                                <td>
                                    <div class="table--food-price text-right">
                                        <?php ($price = \App\CentralLogics\Helpers::format_currency($food['price'])); ?>
                                        <?php echo e($price); ?>

                                    </div>
                                </td>
                                <?php if($productWiseTax): ?>
                                    <td>
                                        <span class="">
                                            <?php $__empty_1 = true; $__currentLoopData = $food?->taxVats?->pluck('tax.name', 'tax.tax_rate')->toArray(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <span> <?php echo e($tax); ?> : <span class="font-bold">
                                                        (<?php echo e($key); ?>%)
                                                    </span> </span>
                                                <br>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <span> <?php echo e(translate('messages.N/A')); ?> </span>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <label class="toggle-switch toggle-switch-sm"
                                        for="stocksCheckbox<?php echo e($food->id); ?>">
                                        <input type="checkbox"
                                            data-url="<?php echo e(route('admin.food.status', [$food['id'], $food->status ? 0 : 1])); ?>"
                                            class="toggle-switch-input redirect-url"
                                            id="stocksCheckbox<?php echo e($food->id); ?>" <?php echo e($food->status ? 'checked' : ''); ?>>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </td>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        <?php if($stock_out): ?>
                                            <a class="btn btn-sm btn--primary btn-outline-primary action-btn "
                                                href="#update-stock<?php echo e($food['id']); ?>"
                                                title="<?php echo e(translate('update_stock')); ?>" data-toggle="modal">
                                                <i class="tio-autorenew"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                            href="<?php echo e(route('admin.food.edit', [$food['id'], 'from' => 'restaurant'])); ?>"
                                            title="<?php echo e(translate('messages.edit_food')); ?>"><i class="tio-edit"></i>
                                        </a>
                                        <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert"
                                            href="javascript:" data-id="food-<?php echo e($food['id']); ?>"
                                            data-message="<?php echo e(translate('Want to delete this item')); ?>"
                                            title="<?php echo e(translate('messages.delete_food')); ?>"><i
                                                class="tio-delete-outlined"></i>
                                        </a>
                                        <form action="<?php echo e(route('admin.food.delete', [$food['id']])); ?>" method="post"
                                            id="food-<?php echo e($food['id']); ?>">
                                            <?php echo csrf_field(); ?> <?php echo method_field('delete'); ?>
                                        </form>
                                    </div>
                                </td>
                            </tr>



                            
                            <div class="modal fade" id="update-stock<?php echo e($food['id']); ?>">
                                <div class="modal-dialog modal-dialog-centered max-w-540px">
                                    <div class="modal-content">
                                        <div class="modal-header position-absolute top-0 right-0 px-2 pt-2 z-index-2">
                                            <div></div>
                                            <button type="button" data-dismiss="modal" class="btn p-0">
                                                <i class="tio-clear fs-24"></i>
                                            </button>
                                        </div>
                                        <div class="modal-body p-0 pt-4">
                                            <div class="table-rest-info mr-2 p-md-4 p-3 pt-0 align-items-start">
                                                <div class="thumb position-relative min-w-80">
                                                    <img src="<?php echo e($food['image_full_url']); ?>" class="w-80px">
                                                    <?php if($food->veg == 1): ?>
                                                        <span
                                                            class="badge badge-soft-success bg-white position-absolute top-0 left-0 m-1 rounded fs-10"><?php echo e(translate('Veg')); ?></span>
                                                    <?php else: ?>
                                                        <span
                                                            class="badge badge-soft-danger bg-white position-absolute top-0 left-0 m-1 rounded fs-10"><?php echo e(translate('Non_Veg')); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="info fs-12 text-body">
                                                    <span class="d-block text-title fs-16 mb-1">
                                                        <span class="line-limit-1">
                                                            <?php echo e($food['name']); ?>

                                                        </span>
                                                    </span>
                                                    <span class="rating d-flex align-items-center gap-1 mb-1">
                                                        <i class="tio-star"></i>
                                                        <span class="text-title font-semibold">(<?php echo e(round($food->avg_rating,2)); ?>/5)</span>
                                                        <span class="text-gray1">(25+)</span>
                                                    </span>
                                                    <div class="fs-14 price-food-modal d-flex algin-items-center gap-1 flex-wrap">
                                                        <span class="gray-dark font-regular"><?php echo e(translate('Price')); ?> :</span>
                                                        <span class="font-medium text-title"><?php echo e(\App\CentralLogics\Helpers::format_currency($food['price'])); ?></span>
                                                        <span class="line mx-1 d-sm-block d-none"></span> <span class="gray-dark font-regular"><?php echo e(translate('Discount')); ?> :</span> <span class="font-medium text-title">
                                                            <?php echo e($food->discount_type == 'percent' ? $food->discount . ' %' : \App\CentralLogics\Helpers::format_currency($food['discount'])); ?></span>
                                                    </div>
                                                    <div>
                                                        <?php
                                                        $addonIds = json_decode($food->add_ons, true) ?? [];
                                                        $addonNames = collect($addonIds)->map(fn($id) => $addons[$id] ?? null)->filter();
                                                        ?>

                                                        <?php echo e(translate('Addons')); ?>: <span class="font-medium">
                                                            <?php if($addonNames->isNotEmpty()): ?>
                                                                <?php echo e($addonNames->join(', ')); ?>.
                                                            <?php else: ?>
                                                                <?php echo e(translate('No_addons_found.')); ?>

                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <form action="<?php echo e(route('admin.food.updateStock')); ?>" method="POST">
                                                <?php echo method_field('post'); ?>
                                                <?php echo csrf_field(); ?>
                                                <div class="stock-body-controller p-md-4 p-3 pt-0">
                                                <div class="__bg-F8F9FC-card d-flex flex-column gap-3">
                                                    <input type="hidden" value="<?php echo e($food->id); ?>" name="food_id">
                                                    <div class="__bg-F8F9FC-card text-left p-0">
                                                        <label class="input-label">
                                                            <?php echo e(translate('Main_Stock')); ?>

                                                        </label>
                                                        <input type="number" step="1" name="item_stock"
                                                            value="<?php echo e($food->item_stock); ?>" required min="1"
                                                            max="99999999999" class="form-control" placeholder="Ex : 50">
                                                    </div>
                                                    <div class="__bg-F8F9FC-card text-left p-0">
                                                        <?php if(isset($food->variations) && count(json_decode($food->variations, true)) > 0): ?>
                                                            <div class="row g-2">
                                                                <div class="col-6">
                                                                    <h4><?php echo e(translate('Variation')); ?></h4>
                                                                </div>
                                                                <div class="col-6">
                                                                    <h4><?php echo e(translate('Stock')); ?></h4>
                                                                </div>
                                                            </div>
                                                            <?php ($variations = json_decode($food->variations, true) ?? []); ?>

                                                            <?php $__currentLoopData = $variations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <?php if(!is_array($item)): ?>
                                                                    <?php continue; ?>
                                                                <?php endif; ?>

                                                                <div class="row g-1 mb-3">

                                                                    <div class="col-12">
                                                                        <h6 class="m-0">
                                                                            <?php echo e($item['name'] ?? 'Variation'); ?>

                                                                        </h6>
                                                                    </div>

                                                                    <?php $__currentLoopData = ($item['values'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <?php if(isset($value['option_id'])): ?>
                                                                            <div class="col-12">
                                                                                <div class="row g-1 align-items-center">
                                                                                    <span class="col-6">
                                                                                        <?php echo e($value['label'] ?? ''); ?> :
                                                                                    </span>

                                                                                    <div class="col-6">
                                                                                        <input
                                                                                            class="form-control"
                                                                                            required
                                                                                            value="<?php echo e($value['current_stock'] ?? 0); ?>"
                                                                                            type="number"
                                                                                            min="1"
                                                                                            step="1"
                                                                                            max="999999999"
                                                                                            name="option[<?php echo e($value['option_id']); ?>]"
                                                                                            placeholder="Ex : 50">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                                                </div>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                </div>

                                               <div class="modal-footer modal-footer-shadow d-flex flex-nowrap justify-content-end gap-lg-3 gap-2 border-0">
                                                    <button type="button" data-dismiss="modal" class="btn min-w-120 btn--reset"><?php echo e(translate('Cancel')); ?></button>
                                                    <button type="submit" class="btn min-w-120 btn--primary"><?php echo e(translate('Update')); ?></button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div>
                            <?php echo $foods->links(); ?>

                        </div>
                    </div>
                </div>
            </div>
                <?php if(count($foods) === 0): ?>
                    <div class="empty--data">
                        <img src="<?php echo e(dynamicAsset('assets/admin/img/empty.png')); ?>" alt="public">
                        <h5>
                            <?php echo e(translate('no_data_found')); ?>

                        </h5>
                    </div>
                    <?php endif; ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <!-- Page level plugins -->
    <script>
        // Call the dataTables jQuery plugin
        $(document).ready(function() {
            $('#dataTable').DataTable();
        });

        $(document).on('ready', function() {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function() {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });

            $('#column2_search').on('keyup', function() {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });

            $('#column3_search').on('change', function() {
                datatable
                    .columns(3)
                    .search(this.value)
                    .draw();
            });

            $('#column4_search').on('keyup', function() {
                datatable
                    .columns(4)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function() {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/vendor/view/product.blade.php ENDPATH**/ ?>