<?php $__env->startSection('title', translate('Restaurant Categories')); ?>

<?php $__env->startPush('css_or_js'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content container-fluid">
        <div class="page-header pb-0">
            <div class="row g-1 align-items-center">
                <div class="col-sm-6 mb-2 mb-sm-0">
                    <h2 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img src="<?php echo e(dynamicAsset('assets/admin/img/category.png')); ?>" alt="">
                        </div>
                        <span><?php echo e(translate('Restaurant Category Setup')); ?></span>
                    </h2>
                </div>
            </div>
        </div>
             <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-warning mt-3 bg-opacity-10">
            <span class="text-warning lh-1 fs-14">
                <i class="tio-info"></i>
            </span>
            <span>
                <?php echo e(translate('messages.All restaurant categories will be show only in restaurant details page.')); ?>

            </span>
        </div>

        <div class="card mt-3">
            <div class="card-header border-0 pt-3 pb-1">
                <div class="search--button-wrapper">
                    <h5 class="card-title">
                        <span class="card-header-icon">
                            <?php echo e(translate('Restaurant Category List')); ?>

                            <span class="badge badge-soft-dark ml-2" id="itemCount"><?php echo e($categories->total()); ?></span>
                        </span>
                    </h5>

                    <div class="hs-unfold ml-3">
                         <form method="get" >
                        <!-- Search -->
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input id="datatableSearch" type="search" value="<?php echo e(request()?->search ?? null); ?>"  name="search" class="form-control" placeholder="<?php echo e(translate('messages.search_here')); ?>" aria-label="<?php echo e(translate('messages.search_here')); ?>">
                            <button type="submit" class="btn btn--secondary secondary-cmn"><i class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>

                   </div>

                    <div class="hs-unfold ml-3">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm"
                            href="javascript:;"
                            data-hs-unfold-options='{
                                "target": "#restaurantCategoryExportDropdown",
                                "type": "css-animation"
                            }'>
                            <i class="tio-download-to mr-1"></i> <?php echo e(translate('messages.export')); ?>

                        </a>

                        <div id="restaurantCategoryExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <span class="dropdown-header"><?php echo e(translate('messages.download_options')); ?></span>
                            <a target="__blank" class="dropdown-item"
                                href="<?php echo e(route('admin.restaurant-category.export', array_merge(['type' => 'excel'], request()->query()))); ?>">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/excel.svg"
                                    alt="Image Description">
                                <?php echo e(translate('messages.excel')); ?>

                            </a>
                            <a target="__blank" class="dropdown-item"
                                href="<?php echo e(route('admin.restaurant-category.export', array_merge(['type' => 'csv'], request()->query()))); ?>">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                <?php echo e(translate('messages.csv')); ?>

                            </a>
                        </div>
                    </div>

                    <div>
                        <a href="javascript:void(0)" class="btn btn--primary pull-right offcanvas-trigger"
                            data-target="#offcanvas__restaurantCategory" data-action="create">
                            <i class="tio-add-circle"></i> <?php echo e(translate('Add Restaurant Category')); ?>

                        </a>
                    </div>
                </div>
            </div>

            <div class="table-responsive datatable-custom pb-0">
                <table id="columnSearchDatatable"
                    class="table mb-0 table-borderless table-thead-bordered table-align-middle text-nowrap">
                    <thead class="thead-light">
                        <tr>
                            <th><?php echo e(translate('messages.SL')); ?></th>
                            <th><?php echo e(translate('messages.Category_Info')); ?></th>
                            <th><?php echo e(translate('messages.Restaurant')); ?></th>
                            <th><?php echo e(translate('messages.priority Level')); ?>

                                 <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                      data-original-title="<?php echo e(translate('Set how prominently this category should appear to customers')); ?>"><i
                                        class="tio-info text-gray1 fs-14"></i></span>
                            </th>
                            <th><?php echo e(translate('messages.status')); ?></th>
                            <th class="text-center w-130px"><?php echo e(translate('messages.action')); ?></th>
                        </tr>
                    </thead>
                    <tbody id="table-div">
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><div class="pl-3"><?php echo e($key + $categories->firstItem()); ?></div></td>
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <img class="avatar border flex-shrink-0"
                                            src="<?php echo e($category->image_full_url); ?>"
                                            alt="<?php echo e(Str::limit($category->name, 20, '...')); ?>">
                                        <div class="d-block font-size-sm text-title">
                                            <div><?php echo e(Str::limit($category->name, 20, '...')); ?></div>
                                            <div class="text-muted"><?php echo e(translate('ID')); ?> #<?php echo e($category->id); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="d-block font-size-sm text-body">
                                         <img class="avatar border rounded-circle flex-shrink-0"
                                         src="<?php echo e($category?->restaurant['logo_full_url']); ?>"
                                         alt="<?php echo e(Str::limit('Hungry Puppet', 20,'...')); ?>">
                                        <?php echo e(Str::limit($category?->restaurant?->name, 20, '...')); ?>

                                         <?php echo $__env->make('partials._verified_restaurant_badge', ['restaurant' => $category?->restaurant], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    </span>
                                </td>
                                <td>
                                    <form action="<?php echo e(route('admin.restaurant-category.priority', $category->id)); ?>" class="priority-form">
                                        <select name="priority" class="form-control form--control-select priority-select <?php echo e($category->priority == 0 ? 'text--title' : ''); ?> <?php echo e($category->priority == 1 ? 'text--info' : ''); ?> <?php echo e($category->priority == 2 ? 'text--success' : ''); ?>">
                                            <option value="0" <?php echo e($category->priority == 0 ? 'selected' : ''); ?>><?php echo e(translate('messages.normal')); ?></option>
                                            <option value="1" <?php echo e($category->priority == 1 ? 'selected' : ''); ?>><?php echo e(translate('messages.medium')); ?></option>
                                            <option value="2" <?php echo e($category->priority == 2 ? 'selected' : ''); ?>><?php echo e(translate('messages.high')); ?></option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <label class="toggle-switch toggle-switch-sm ml-2" for="restaurantCategoryStatus<?php echo e($category->id); ?>">
                                        <input type="checkbox"
                                            data-url="<?php echo e(route('admin.restaurant-category.status', [$category->id, $category->status ? 0 : 1])); ?>"
                                            class="toggle-switch-input redirect-url"
                                            id="restaurantCategoryStatus<?php echo e($category->id); ?>"
                                            <?php echo e($category->status ? 'checked' : ''); ?>>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </td>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        <a class="btn btn-sm text-end action-btn info--outline text--info info-hover offcanvas-trigger"
                                            data-target="#offcanvas__restaurantCategory"
                                            data-id="<?php echo e($category->id); ?>"
                                            data-url="<?php echo e(route('admin.restaurant-category.edit', $category->id)); ?>"
                                            data-action="edit"
                                            href="javascript:"
                                            title="<?php echo e(translate('Edit Restaurant Category')); ?>">
                                            <i class="tio-edit"></i>
                                        </a>
                                        <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert"
                                            href="javascript:"
                                            data-id="restaurant-category-<?php echo e($category->id); ?>"
                                            data-message="<?php echo e(translate('Want to delete this restaurant category?')); ?>"
                                            title="<?php echo e(translate('Delete Restaurant Category')); ?>">
                                            <i class="tio-delete-outlined"></i>
                                        </a>
                                    </div>

                                    <form action="<?php echo e(route('admin.restaurant-category.delete', $category->id)); ?>" method="post"
                                        id="restaurant-category-<?php echo e($category->id); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('delete'); ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>

                <?php if(count($categories) === 0): ?>
                    <div class="empty--data">
                        <img src="<?php echo e(dynamicAsset('assets/admin/img/empty.png')); ?>" alt="public">
                        <h5><?php echo e(translate('no_data_found')); ?></h5>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card-footer pt-0 border-0">
                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div><?php echo $categories->links(); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="offcanvas__restaurantCategory" class="custom-offcanvas d-flex flex-column justify-content-between">
        <div id="data-view" class="h-100"></div>
    </div>

    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <script src="<?php echo e(dynamicAsset('assets/admin')); ?>/js/view-pages/category-index.js"></script>
    <script src="<?php echo e(dynamicAsset('assets/admin/js/offcanvas.js')); ?>"></script>
    <script>
        "use strict";

        document.addEventListener("DOMContentLoaded", function () {
            $('.select2-basic').select2({ width: '100%' });

            $('#restaurantFilterSelect, #priorityFilterSelect').on('change', function () {
                $('#restaurantCategoryFilterForm').submit();
            });
        });

        $(document).on('click', '.offcanvas-trigger', function () {
            const action = $(this).data('action');

            if (action === 'create') {
                fetchRestaurantCategoryForm("<?php echo e(route('admin.restaurant-category.create')); ?>");
                return;
            }

            fetchRestaurantCategoryForm($(this).data('url'));
        });

        function fetchRestaurantCategoryForm(url) {
            $.ajax({
                url: url,
                type: "get",
                beforeSend: function () {
                    $('#data-view').empty();
                    $('#loading').show();
                },
                success: function (data) {
                    $('#data-view').append(data.view);
                    initLangTabs();
                    initSelect2Dropdowns();
                    initFileUpload();
                    checkPreExistingImages();
                    initCloseButtons();
                    initRemoveImage();
                },
                complete: function () {
                    $('#loading').hide();
                }
            });
        }

        function initLangTabs() {
            const langLinks = document.querySelectorAll('.lang_link1');
            langLinks.forEach(function (langLink) {
                langLink.addEventListener('click', function (e) {
                    e.preventDefault();
                    langLinks.forEach(function (link) {
                        link.classList.remove('active');
                    });
                    this.classList.add('active');
                    document.querySelectorAll('.lang_form1').forEach(function (form) {
                        form.classList.add('d-none');
                    });
                    const lang = this.id.substring(0, this.id.length - 5);
                    $('#' + lang + '-form1').removeClass('d-none');
                });
            });
        }

        function initSelect2Dropdowns() {
            $('.js-select2-custom1').select2({
                width: '100%'
            });
        }

        function initCloseButtons() {
            $('.offcanvas-close, #offcanvasOverlay').off('click').on('click', function () {
                $('.custom-offcanvas').removeClass('open');
                $('#offcanvasOverlay').removeClass('show');
            });
        }

        function initRemoveImage() {
            const removeBtn = document.getElementById('remove_image_btn');
            const removeFlag = document.getElementById('image_remove');
            const fileInput = document.querySelector('input[name="image"]');

            if (removeBtn && removeFlag) {
                removeBtn.addEventListener('click', function () {
                    removeFlag.value = '1';
                    if (fileInput) {
                        fileInput.removeAttribute('disabled');
                        fileInput.value = '';
                    }
                });
            }
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/restaurant-category/index.blade.php ENDPATH**/ ?>