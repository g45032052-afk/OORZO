<?php $__env->startSection('title', translate('Food_List')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center items-customize">
                <div class="">
                    <h1 class="page-header-title"> <?php echo e(translate('messages.food_list')); ?><span
                            class="badge badge-soft-dark ml-2" id="foodCount"><?php echo e($foods->total()); ?></span></h1>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-lg-3 gap-2">
                    <!-- <div class="col-auto">
                        <a href="<?php echo e(route('admin.food.stockOutList')); ?>" class="btn max-sm-12 badge-soft-danger font-medium py-2 fs-12 w-100"> <?php echo e(translate('messages.Out of Stock Foods')); ?></a>
                    </div> -->
                    <!-- <a href="<?php echo e(route('admin.food.add-new')); ?>" type="button" class="btn btn--primary lh-1 d-flex align-items-center gap-1 font-semibold fs-12 h-40 py-1 px-3">
                        <i class="tio-add-circle mb-1"></i><span> <?php echo e(translate('Add New Food')); ?></span>
                    </a> -->
                </div>
                
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header flex-wrap gap-2 border-0 p-20">
                        <form id="search-form">
                            <div class="input--group input-group border rounded input-group-merge input-group-flush">
                                <input id="datatableSearch" name="search" type="search" value="<?php echo e(request()?->search ?? null); ?>" class="form-control border-0"
                                    placeholder="<?php echo e(translate('Search_by_name')); ?>"
                                    aria-label="<?php echo e(translate('messages.search_here')); ?>">
                                <button type="submit" class="btn btn--reset w-auto px-2 py-2 h-40px min-w-35px">
                                    <i class="tio-search"></i>
                                </button>
                            </div>
                        </form>
                        <div class="d-flex align-items-center gap-lg-3 gap-2 flex-wrap">
                            <div class="hs-unfold">
                                <a class="js-hs-unfold-invoker btn btn-sm btn-outline-primary dropdown-toggle min-height-40" href="javascript:;"
                                    data-hs-unfold-options='{
                                            "target": "#usersExportDropdown",
                                            "type": "css-animation"
                                        }'>
                                    <i class="tio-download-from-cloud mr-1 fs-16"></i> <?php echo e(translate('messages.export')); ?>

                                </a>

                                <div id="usersExportDropdown"
                                    class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">

                                    <span class="dropdown-header"><?php echo e(translate('messages.download_options')); ?></span>
                                    <a id="export-excel" class="dropdown-item" href="<?php echo e(route('admin.food.export', ['type' => 'excel', request()->getQueryString()])); ?>">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/excel.svg"
                                            alt="Image Description">
                                        <?php echo e(translate('messages.excel')); ?>

                                    </a>
                                    <a id="export-csv" class="dropdown-item" href="<?php echo e(route('admin.food.export', ['type' => 'csv', request()->getQueryString()])); ?>">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/placeholder-csv-format.svg"
                                            alt="Image Description">
                                        .<?php echo e(translate('messages.csv')); ?>

                                    </a>
                                </div>
                            </div>
                            <?php ($filtered = request()->has('types') || request()->has('min_price') || request()->has('max_price') || request()->has('status') || request()->has('category_ids')); ?>
                            <div class="hs-unfold">
                                <a class="js-hs-unfold-invoker btn min-w-100px justify-content-center font-medium btn-sm filter-show offcanvas-trigger <?php echo e($filtered ? 'filter-active' : 'btn-outline-primary'); ?>"
                                        data-target="#Food-list_filter" href="javascript:">
                                    <i class="tio-tune-horizontal mr-1 fs-16"></i> <span class="mt-1"><?php echo e(translate('Filter')); ?></span>
                                    <?php if($filtered): ?>
                                        <span class="filter-dot"></span>
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="hs-unfold">
                                <a href="<?php echo e(route('admin.food.stockOutList')); ?>" class="btn max-sm-12 badge-danger font-semibold py-2 fs-12 w-100"> <?php echo e(translate('messages.Out of Stock Foods')); ?></a>
                            </div>
                            <div class="hs-unfold">
                                <a href="<?php echo e(route('admin.food.add-new')); ?>" type="button" class="btn btn--primary lh-1 d-flex align-items-center gap-1 font-semibold fs-12 h-40 py-1 px-3">
                                    <i class="tio-add-circle mb-1"></i><span> <?php echo e(translate('Add New Food')); ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Table -->
                    <div class="table-responsive datatable-custom pt-0" id="table-div">
                        <table id="datatable"
                            class="table table-borderless table-thead-borderedleff table-nowrap table-align-middle card-table"
                            data-hs-datatables-options='{
                                    "columnDefs": [{
                                        "targets": [],
                                        "width": "5%",
                                        "orderable": false
                                    }],
                                    "order": [],
                                    "info": {
                                    "totalQty": "#datatableWithPaginationInfoTotalQty"
                                    },

                                    "entries": "#datatableEntries",

                                    "isResponsive": false,
                                    "isShowPaging": false,
                                    "paging":false,
                                    "columnDefs": [
               { "orderable": false, "targets": [6,7] }
           ]
                                }'>
                            <thead class="global-bg-box">
                                <tr>
                                    <th class="w-60px"><?php echo e(translate('messages.sl')); ?></th>
                                    <th class="w-100px"><?php echo e(translate('messages.name')); ?></th>
                                    <th class="w-120px"><?php echo e(translate('messages.category')); ?></th>
                                    <th class="w-120px"><?php echo e(translate('messages.restaurant')); ?></th>
                                    <th class="w-100px"><?php echo e(translate('messages.price')); ?></th>
                                    <?php if($productWiseTax): ?>
                                    <th  class="w-100px"><?php echo e(translate('messages.Vat/Tax')); ?></th>
                                    <?php endif; ?>
                                    <th class="w-100px text-center"><?php echo e(translate('messages.Stock')); ?></th>
                                    <th class="w-100px"><?php echo e(translate('messages.status')); ?></th>

                                    <th class="w-120px text-center">
                                        <?php echo e(translate('messages.action')); ?>

                                    </th>
                                </tr>
                            </thead>

                            <tbody id="set-rows">
                                <?php $__currentLoopData = $foods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $food): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php ( $stock_out = null); ?>

                                    <tr>
                                        <td><?php echo e($key + $foods->firstItem()); ?></td>
                                        <td>
                                            <a class="media align-items-center min-w-370"
                                                href="<?php echo e(route('admin.food.view', [$food['id']])); ?>">
                                                <img class="avatar avatar-lg mr-3 onerror-image"
                                                     src="<?php echo e($food['image_full_url']); ?>"
                                                     alt="<?php echo e($food->name); ?> image">
                                                <div class="media-body">
                                                    <h5 class="text-hover-primary mb-1 text-wrap line-limit-2"><?php echo e(ucwords(Str::limit($food['name'], 20, '...'))); ?></h5>
                                                    <span>
                                                        <?php if($food->stock_type != 'unlimited' &&  $food->item_stock <= 0 ): ?>
                                                            <?php ( $stock_out = true); ?>

                                                            <span class="badge badge-soft-danger badge-pill font-medium px-3"><?php echo e(translate('Out Of Stock')); ?>

                                                            <span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="<?php echo e(translate('messages.Your_main_stock_is_out_of_stock.')); ?>"><i class="tio-info fs-14 text-danger"></i></span>
                                                            </span>
                                                        <?php else: ?>

                                                                <?php

                                                                if(isset($food->variations)){
                                                                    foreach (json_decode($food->variations,true) as $item) {
                                                                        if (isset($item['values']) && is_array($item['values'])) {
                                                                            foreach ($item['values'] as $value) {
                                                                                if(isset($value['stock_type']) && $value['stock_type'] != 'unlimited' &&   $value['current_stock'] <= 0){
                                                                                    $stock_out = true;
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                ?>
                                                            <?php if($stock_out): ?>
                                                                <span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="<?php echo e(translate('messages.One_or_more_variations_are_out_of_stock.')); ?>"><img src="<?php echo e(dynamicAsset('assets/admin/img/info-circle.svg')); ?>" alt="public/img"></span>

                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </a>
                                        </td>
                                        <td>
                                            <?php echo e(Str::limit(($food?->category?->parent ? $food?->category?->parent?->name : $food?->category?->name )  ?? translate('messages.uncategorize')
                                            , 20, '...')); ?>

                                        </td>
                                        <td>
                                            <?php if($food->restaurant): ?>
                                                <a class="text--title" href="<?php echo e(route('admin.restaurant.view',['restaurant'=>$food->restaurant_id])); ?>" title="<?php echo e(translate('view_restaurant')); ?>">
                                                    <?php echo e(Str::limit($food->restaurant->name, 20, '...')); ?>

                                                    <?php echo $__env->make('partials._verified_restaurant_badge', ['restaurant' => $food->restaurant], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text--danger text-capitalize"><?php echo e(Str::limit( translate('messages.Restaurant_deleted!'), 20, '...')); ?><span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e(\App\CentralLogics\Helpers::format_currency($food['price'])); ?></td>

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
                                            <div class="d-flex justify-content-center align-items-center gap-1">
                                                <span class="lh-1 mt-1"><?php echo e($food->stock_type == 'unlimited' ? translate('messages.unlimited_Stock') :$food->item_stock); ?></span>
                                                <span class="lh-1" data-toggle="tooltip" data-placement="right" data-original-title="<?php echo e(translate('messages.Daily Stock')); ?>">
                                                    <?php if($food->stock_type == 'daily'): ?>
                                                        <i class="tio-time brand-base-clr fs-14" style="background:#F58300FF; color:#fff; width:14px; height:14px; padding:6px; border-radius:60%; display:inline-flex; align-items:center; justify-content:center;"></i>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <label class="toggle-switch toggle-switch-sm"
                                                for="stocksCheckbox<?php echo e($food->id); ?>">
                                                <input type="checkbox"
                                                    data-url="<?php echo e(route('admin.food.status', [$food['id'], $food->status ? 0 : 1])); ?>"
                                                    class="toggle-switch-input redirect-url" id="stocksCheckbox<?php echo e($food->id); ?>"
                                                    <?php echo e($food->status ? 'checked' : ''); ?>>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </td>
                                        <td>
                                            <div class="btn--container justify-content-center">
                                                <?php if($stock_out): ?>
                                                    <a class="btn btn-sm btn--primary btn-outline-primary action-btn " href="#update-stock<?php echo e($food['id']); ?>" title="<?php echo e(translate('update_stock')); ?>" data-toggle="modal">
                                                        <i class="tio-autorenew"></i>
                                                    </a>
                                                <?php endif; ?>
                                                    <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                                        href="<?php echo e(route('admin.food.edit', [$food['id']])); ?>"
                                                        title="<?php echo e(translate('messages.edit_food')); ?>"><i
                                                            class="tio-edit"></i>
                                                    </a>
                                                    <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert" href="javascript:"
                                                        data-id="food-<?php echo e($food['id']); ?>" data-message="<?php echo e(translate('messages.Want_to_delete_this_item')); ?>"
                                                        title="<?php echo e(translate('messages.delete_food')); ?>"><i
                                                            class="tio-delete-outlined"></i>
                                                    </a>
                                            </div>
                                            <form action="<?php echo e(route('admin.food.delete', [$food['id']])); ?>" method="post"
                                                id="food-<?php echo e($food['id']); ?>">
                                                <?php echo csrf_field(); ?> <?php echo method_field('delete'); ?>
                                            </form>
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
                                                            <span class="badge badge-soft-success bg-white position-absolute top-0 left-0 m-1 rounded fs-10"><?php echo e(translate('Veg')); ?></span>
                                                            <?php else: ?>
                                                            <span class="badge badge-soft-danger bg-white position-absolute top-0 left-0 m-1 rounded fs-10"><?php echo e(translate('Non_Veg')); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="info fs-12 text-body">
                                                            
                                                            <span class="d-block text-title fs-16 mb-1">
                                                                <span class="line-limit-1"><?php echo e($food['name']); ?></span>
                                                            </span>
                                                            <span class="rating d-flex align-items-center gap-1 mb-1">
                                                                <i class="tio-star"></i>
                                                                <span class="text-title font-semibold"><?php echo e(round($food->avg_rating,2)); ?>/5</span>
                                                                <span class="text-gray1">(<?php echo e($food->rating_count); ?><?php echo e($food->rating_count > 24 ? '+' : ''); ?>)</span>
                                                            </span>
                                                            <div class="fs-14 price-food-modal d-flex algin-items-center gap-1 flex-wrap">
                                                                <span class="gray-dark font-regular"><?php echo e(translate('Price')); ?> :</span> <span class="font-medium text-title"><?php echo e(\App\CentralLogics\Helpers::format_currency($food['price'])); ?></span> <span class="line mx-1 d-sm-block d-none"></span> <span class="gray-dark font-regular"><?php echo e(translate('Discount')); ?> :</span> <span class="font-medium text-title"> <?php echo e($food->discount_type == 'percent' ?  $food->discount . ' %' :  \App\CentralLogics\Helpers::format_currency($food['discount'])); ?></span>
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
                                                    <form action="<?php echo e(route('admin.food.updateStock')); ?>" method="POST" >
                                                        <?php echo method_field("post"); ?>
                                                        <?php echo csrf_field(); ?>
                                                        <div class="stock-body-controller p-md-4 p-3 pt-0">
                                                            <div class="__bg-F8F9FC-card d-flex flex-column gap-3">
                                                                <input type="hidden" value="<?php echo e($food->id); ?>"  name="food_id">
                                                                <div class="__bg-F8F9FC-card p-0 text-left">
                                                                    <label class="input-label fs-14">
                                                                        <?php echo e(translate('Main_Stock')); ?>

                                                                    </label>
                                                                    <input type="number" step="1" name="item_stock" value="<?php echo e($food->item_stock); ?>" required min="1" max="99999999999" class="form-control" placeholder="Ex : 50">
                                                                </div>
                                                                <div class="__bg-F8F9FC-card p-0 text-left">
                                                                    <?php if(isset($food->variations) && count(json_decode($food->variations,true)) >0 ): ?>
                                                                        <div class="row g-2">
                                                                            <div class="col-6">
                                                                                <h4><?php echo e(translate('Variation')); ?></h4>
                                                                            </div>
                                                                            <div class="col-6">
                                                                                <h4><?php echo e(translate('Stock')); ?></h4>
                                                                            </div>
                                                                        </div>
                                                                    <?php $__currentLoopData = json_decode($food->variations,true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <div class="row g-1 mb-3">
                                                                        <div class="col-12">
                                                                            <h5 class="m-0">
                                                                                <?php echo e($item['name']); ?>

                                                                            </h5>
                                                                        </div>
                                                                        <?php if(isset($item['values']) && is_array($item['values'])): ?>
                                                                            <?php $__currentLoopData = $item['values']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <?php if(isset($value['option_id'])): ?>
                                                                                <div class="col-12">
                                                                                    <div class="row g-1 align-items-center">
                                                                                        <span class="col-6"><?php echo e($value['label']); ?> :</span>
                                                                                        <div class="col-6">
                                                                                            <input class="form-control" required value="<?php echo e($value['current_stock']); ?>" type="number" min="1" step="1" max="999999999" name="option[<?php echo e($value['option_id']); ?>]"  placeholder="Ex : 50">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                                <?php endif; ?>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php if($food->stock_type == 'limited'): ?>
                                                            <div class="modal-footer modal-footer-shadow d-flex flex-nowrap justify-content-end gap-lg-3 gap-2 border-0">
                                                                <button type="button" data-dismiss="modal" class="btn min-w-120 btn--reset"><?php echo e(translate('Cancel')); ?></button>
                                                                <button type="submit" class="btn min-w-120 btn--primary"><?php echo e(translate('Update')); ?></button>
                                                            </div>
                                                        <?php endif; ?>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if(count($foods) === 0): ?>
                    <div class="empty--data">
                        <img src="<?php echo e(dynamicAsset('assets/admin/img/empty.png')); ?>" alt="public">
                        <h5>
                            <?php echo e(translate('no_data_found')); ?>

                        </h5>
                    </div>
                    <?php endif; ?>
                    <div class="page-area px-4 pb-3">
                        <div class="d-flex align-items-center justify-content-end">
                            <div>
                                <?php echo $foods->withQueryString()->links(); ?>

                            </div>
                        </div>
                    </div>
                    <!-- End Table -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>










<!-- Food List Filter -->
<div id="Food-list_filter" class="custom-offcanvas d-flex flex-column justify-content-between"
    style="--offcanvas-width: 500px">
    <div>
        <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
            <div class="px-3 py-3 d-flex justify-content-between w-100">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h2 class="mb-0 fs-18 text-title font-medium"><?php echo e(translate('Filter - Food List')); ?></h2>
                </div>
                <button type="button"
                    class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                    aria-label="Close">&times;
                </button>
            </div>
        </div>
        <form id="food-filter-form" action="<?php echo e(route('admin.food.list')); ?>" method="GET">
        <div class="custom-offcanvas-body p-20">
            <input type="hidden" name="search" value="<?php echo e(request()->query('search')); ?>">
            <div class="d-flex flex-column gap-20px">
                <div class="global-bg-box rounded p-xl-20 p-16">
                    <h5 class="mb-10px font-regular text-color font-normal"><?php echo e(translate('Food Type')); ?></h5>
                    <div class="bg-white rounded p-xl-3 p-2">
                        <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2">
                            <div class="col-sm-6 col-auto">
                                <div class="form-group m-0">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="food_veg" name="types[]" value="veg" <?php echo e(in_array('veg', (array) request()->query('types', [])) ? 'checked' : ''); ?>>
                                        <label class="custom-control-label text-title" for="food_veg">
                                            <?php echo e(translate('messages.Veg')); ?>

                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-auto">
                                <div class="form-group m-0">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="food_non_veg" name="types[]" value="non_veg" <?php echo e(in_array('non_veg', (array) request()->query('types', [])) ? 'checked' : ''); ?>>
                                        <label class="custom-control-label text-title" for="food_non_veg">
                                            <?php echo e(translate('messages.Non-veg')); ?>

                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="global-bg-box rounded p-xl-20 p-16">
                    <div class="price-range-container">
                        <label class="fs-14 d-block mb-3 text-title"><?php echo e(translate('Price Range')); ?></label>
                        <div class="slider-wrapper mb-lg-4 mb-3">
                            <?php (
                                $rangeMinBound = is_numeric($foodMinPrice ?? null) ? (float)$foodMinPrice : 0
                            ); ?>
                            <?php (
                                $rangeMaxBound = is_numeric($foodMaxPrice ?? null) ? (float)$foodMaxPrice : 0
                            ); ?>
                            <?php (
                                $sliderMin = request()->query('min_price') !== null ? (float) request()->query('min_price') : $rangeMinBound
                            ); ?>
                            <?php (
                                $sliderMax = request()->query('max_price') !== null ? (float) request()->query('max_price') : $rangeMaxBound
                            ); ?>
                            <input type="range" id="min_price" min="<?php echo e($rangeMinBound); ?>" max="<?php echo e($rangeMaxBound); ?>" value="<?php echo e($sliderMin); ?>">
                            <input type="range" id="max_price" min="<?php echo e($rangeMinBound); ?>" max="<?php echo e($rangeMaxBound); ?>" value="<?php echo e($sliderMax); ?>">
                            <div class="slider-track">
                                <div class="slider-track-filled" id="slider_track_filled"></div>
                            </div>
                        </div>
                        <div class="price-inputs gap-xxl-4 gap-3">
                            <div class="d-flex w-100 align-items-center justify-content-between gap-1 border rounded  h-50px bg-white overflow-hidden">
                                <div class="gray-dark fs-12 m-0 __bg-FAFAFA px-2 h-100 d-flex align-items-center"><?php echo e(translate('Min Price')); ?>:</div>
                                <div class="d-flex align-items-center text-title fs-14 w-auto">
                                    $
                                    <input type="number" id="min_input" name="min_price" value="<?php echo e($sliderMin); ?>" min="<?php echo e($rangeMinBound); ?>" max="<?php echo e($rangeMaxBound); ?>" class="border-0 bg-transparent text-title">
                                </div>
                            </div>
                            <div class="d-flex w-100 align-items-center justify-content-between gap-1 border rounded  h-50px bg-white overflow-hidden">
                                <div class="gray-dark fs-12 m-0 __bg-FAFAFA px-2 h-100 d-flex align-items-center"><?php echo e(translate('Max Price')); ?>:</div>
                                <div class="d-flex align-items-center text-title fs-14 w-auto">
                                    $
                                    <input type="number" id="max_input" name="max_price" value="<?php echo e($sliderMax); ?>" min="<?php echo e($rangeMinBound); ?>" max="<?php echo e($rangeMaxBound); ?>" class="border-0 bg-transparent text-title">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="global-bg-box rounded p-xl-20 p-16">
                    <h5 class="mb-10px font-regular text-color font-normal"><?php echo e(translate('Status')); ?></h5>
                    <div class="bg-white rounded p-xl-3 p-2">
                        <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2">
                            <div class="col-sm-6 col-auto">
                                <div class="form-group m-0">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="active__status" name="status[]" value="1" <?php echo e(in_array('1', (array) request()->query('status', [])) ? 'checked' : ''); ?>>
                                        <label class="custom-control-label text-title" for="active__status">
                                            <?php echo e(translate('messages.Active')); ?>

                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-auto">
                                <div class="form-group m-0">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="inactive__status" name="status[]" value="0" <?php echo e(in_array('0', (array) request()->query('status', [])) ? 'checked' : ''); ?>>
                                        <label class="custom-control-label text-title" for="inactive__status">
                                            <?php echo e(translate('messages.Inactive')); ?>

                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="global-bg-box rounded p-xl-20 p-16">
                    <h5 class="mb-10px font-regular text-color font-normal"><?php echo e(translate('Restaurant')); ?> </h5>
                    <div class="bg-white rounded p-xl-3 p-2">
                        <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2 order-status_controller restaurent-select-controller">
                            <?php ($selectedRestaurants = (array) request()->query('restaurant_ids', [])); ?>
                            <div class="col-sm-6 col-auto">
                                <div class="form-group m-0">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input check-all" id="all1" name="restaurant_check" <?php echo e((isset($restaurantsList) && count($restaurantsList) > 0 && count($selectedRestaurants) === count($restaurantsList)) ? 'checked' : ''); ?>>
                                        <label class="custom-control-label text-title" for="all1">
                                            <?php echo e(translate('messages.All')); ?>

                                        </label>
                                    </div>
                                </div>
                            </div>
                            <?php $__currentLoopData = $restaurantsList ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="restaurant-status<?php echo e($rest->id); ?>" name="restaurant_ids[]" value="<?php echo e($rest->id); ?>" <?php echo e(in_array($rest->id, $selectedRestaurants) ? 'checked' : ''); ?>>
                                            <label class="custom-control-label text-title" for="restaurant-status<?php echo e($rest->id); ?>">
                                                <?php echo e(Str::limit($rest->name, 30, '...')); ?>

                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-sm-12">
                                <div class="text-center w-100">
                                    <button type="button" class="see__more btn mx-auto d-flex fs-12 align-items-center justify-content-center gap-1 p-0 border-0 text--primary font-semibold text-center">
                                        <?php echo e(translate('See More')); ?> <span class="text-primary count"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="global-bg-box rounded p-xl-20 p-16">
                    <h5 class="mb-10px font-regular text-color font-normal"><?php echo e(translate('Category')); ?>  </h5>
                    <div class="bg-white rounded p-xl-3 p-2">
                        <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2 order-status_controller restaurent-select-controller">
                            <?php ($selectedCategories = (array) request()->query('category_ids', [])); ?>
                            <div class="col-sm-6 col-auto">
                                <div class="form-group m-0">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input check-all" id="all2" name="category_check" <?php echo e((isset($categoriesList) && count($categoriesList) > 0 && count($selectedCategories) === count($categoriesList)) ? 'checked' : ''); ?>>
                                        <label class="custom-control-label text-title" for="all2">
                                            <?php echo e(translate('messages.All')); ?>

                                        </label>
                                    </div>
                                </div>
                            </div>
                            <?php $__currentLoopData = $categoriesList ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="category-status<?php echo e($cat->id); ?>" name="category_ids[]" value="<?php echo e($cat->id); ?>" <?php echo e(in_array($cat->id, $selectedCategories) ? 'checked' : ''); ?>>
                                            <label class="custom-control-label text-title" for="category-status<?php echo e($cat->id); ?>">
                                                <?php echo e(Str::limit($cat->name, 30, '...')); ?>

                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-sm-12">
                                <div class="text-center w-100">
                                    <button type="button" class="see__more btn mx-auto d-flex fs-12 align-items-center justify-content-center gap-1 p-0 border-0 text--primary font-semibold text-center">
                                        <?php echo e(translate('See More')); ?> <span class="text-primary count"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3 position-sticky">
            <a href="<?php echo e(route('admin.food.list')); ?>" class="btn w-100 btn--reset offcanvas-close"><?php echo e(translate('Reset')); ?></a>
            <button type="submit" class="btn w-100 btn--primary"><?php echo e(translate('Apply')); ?></button>
        </div>
        </form>
    </div>
</div>
<div id="offcanvasOverlay" class="offcanvas-overlay"></div>




<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <script>
        "use strict";
        $(document).on('ready', function() {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#datatable'), {
                select: {
                    style: 'multi',
                    classMap: {
                        checkAll: '#datatableCheckAll',
                        counter: '#datatableCounter',
                        counterInfo: '#datatableCounterInfo'
                    }
                },
                language: {
                    zeroRecords: '<div class="text-center p-4">' +
                        '<img class="w-7rem mb-3" src="<?php echo e(dynamicAsset('assets/admin/svg/illustrations/sorry.svg')); ?>" alt="Image Description">' +
                        '<p class="mb-0"><?php echo e(translate('No_data_to_show')); ?></p>' +
                        '</div>'
                }
            });

            $('#datatableSearch').on('mouseup', function(e) {
                let $input = $(this),
                    oldValue = $input.val();

                if (oldValue == "") return;

                setTimeout(function() {
                    let newValue = $input.val();

                    if (newValue == "") {
                        // Gotcha
                        datatable.search('').draw();
                    }
                }, 1);
            });

            $('#toggleColumn_index').change(function(e) {
                datatable.columns(0).visible(e.target.checked)
            })
            $('#toggleColumn_name').change(function(e) {
                datatable.columns(1).visible(e.target.checked)
            })

            $('#toggleColumn_type').change(function(e) {
                datatable.columns(2).visible(e.target.checked)
            })

            $('#toggleColumn_vendor').change(function(e) {
                datatable.columns(3).visible(e.target.checked)
            })

            $('#toggleColumn_status').change(function(e) {
                datatable.columns(5).visible(e.target.checked)
            })
            $('#toggleColumn_price').change(function(e) {
                datatable.columns(4).visible(e.target.checked)
            })
            $('#toggleColumn_action').change(function(e) {
                datatable.columns(6).visible(e.target.checked)
            })

            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function() {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });

        $('#restaurant').select2({
            ajax: {
                url: '<?php echo e(url('/')); ?>/admin/restaurant/get-restaurants',
                data: function(params) {
                    return {
                        q: params.term, // search term
                        all: true,
                        page: params.page
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                __port: function(params, success, failure) {
                    let $request = $.ajax(params);

                    $request.then(success);
                    $request.fail(failure);

                    return $request;
                }
            }
        });

        $('#category').select2({
            ajax: {
                url: '<?php echo e(route('admin.category.get-all')); ?>',
                data: function(params) {
                    return {
                        q: params.term, // search term
                        all: true,
                        page: params.page
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                __port: function(params, success, failure) {
                    let $request = $.ajax(params);

                    $request.then(success);
                    $request.fail(failure);

                    return $request;
                }
            }
        });


    </script>
    <script>
        $(document).ready(function () {
            const showCount = 8;
            $(".restaurent-select-controller").each(function () {
                const $container = $(this);
                const $items = $container.find(".col-sm-6");
                const $button = $container.find(".see__more");

                // Initially hide extra items
                $items.slice(showCount).hide();

                // Update button text + count
                function updateButton() {
                    const hiddenCount = $items.filter(":hidden").length;
                    if (hiddenCount > 0) {
                        $button.html(`See More <span class="count">(${hiddenCount})</span>`);
                    } else {
                        $button.html(`See Less`);
                    }
                }

                updateButton();

                // Toggle on button click
                $button.on("click", function () {
                    const hiddenCount = $items.filter(":hidden").length;

                    if (hiddenCount > 0) {
                        // Show all
                        $items.show();
                    } else {
                        // Show only first 8 again
                        $items.slice(showCount).hide();
                    }

                    updateButton();
                });
            });
        });
    </script>
    <script>
         $(function() {
            var $minSlider = $('#min_price'),
                $maxSlider = $('#max_price'),
                $minInput = $('#min_input'),
                $maxInput = $('#max_input'),
                $trackFilled = $('#slider_track_filled');

            var rangeMin = parseFloat('<?php echo e($rangeMinBound ?? 0); ?>') || 0,
                rangeMax = parseFloat('<?php echo e($rangeMaxBound ?? 0); ?>') || 0;

            function updateTrack() {
                var min = parseFloat($minSlider.val()),
                    max = parseFloat($maxSlider.val());

                var denom = Math.max((rangeMax - rangeMin), 1);
                var minPercent = ((min - rangeMin) / denom) * 100;
                var maxPercent = ((max - rangeMin) / denom) * 100;

                $trackFilled.css({
                    'left': minPercent + '%',
                    'width': (maxPercent - minPercent) + '%'
                });
            }

            $minSlider.on('input', function() {
                if (parseInt($minSlider.val()) > parseInt($maxSlider.val())) {
                    $minSlider.val($maxSlider.val());
                }
                $minInput.val($minSlider.val());
                updateTrack();
            });

            $maxSlider.on('input', function() {
                if (parseInt($maxSlider.val()) < parseInt($minSlider.val())) {
                    $maxSlider.val($minSlider.val());
                }
                $maxInput.val($maxSlider.val());
                updateTrack();
            });

            $minInput.on('input', function() {
                var val = parseFloat($minInput.val());
                if (isNaN(val)) val = rangeMin;
                val = Math.max(val, rangeMin);
                val = Math.min(val, parseFloat($maxSlider.val()));
                val = Math.min(val, parseInt($maxSlider.val()));
                $minSlider.val(val);
                $minInput.val(val);
                updateTrack();
            });

            $maxInput.on('input', function() {
                var val = parseFloat($maxInput.val());
                if (isNaN(val)) val = rangeMax;
                val = Math.min(Math.max(val, parseFloat($minSlider.val())), rangeMax);
                val = Math.max(val, parseInt($minSlider.val()));
                $maxSlider.val(val);
                $maxInput.val(val);
                updateTrack();
            });

            updateTrack();
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/product/list.blade.php ENDPATH**/ ?>