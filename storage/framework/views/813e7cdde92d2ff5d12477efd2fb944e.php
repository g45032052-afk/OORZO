<?php $__env->startSection('title', translate('Add_New_Food')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link href="<?php echo e(dynamicAsset('assets/admin/css/tags-input.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(dynamicAsset('assets/admin/css/AI/animation/product/ai-sidebar.css')); ?>" rel="stylesheet">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-add-circle-outlined"></i>
                        <?php echo e(translate('messages.Add_New_Food')); ?></h1>
                </div>
            </div>
        </div>
        <?php ($openai_config = \App\CentralLogics\Helpers::get_business_settings('openai_config')); ?>

        <!-- End Page Header -->
        <form action="<?php echo e(route('admin.food.store')); ?>" method="post" id="" enctype="multipart/form-data" class="validate-form global-ajax-form">
            <?php echo csrf_field(); ?>
            <div class="row g-2">
                <input type="hidden" id="request_type" value="admin">

                <?php if ($__env->exists('admin-views.product.partials._title_and_discription')) echo $__env->make('admin-views.product.partials._title_and_discription', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <div class="col-lg-6">
                    <div class="card shadow--card-2 border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex flex-column align-items-center gap-3">
                                <p class="mb-0"><?php echo e(translate('Food_Image')); ?></p>

                                <?php echo $__env->make('admin-views.partials._image-uploader', [
                                    'id' => 'image-input',
                                    'name' => 'image',
                                    'isRequired' => true,
                                    'existingImage' => null,
                                    'ratio' => '1:1',
                                    'imageExtension' => IMAGE_EXTENSION,
                                    'imageFormat' => IMAGE_FORMAT,
                                    'maxSize' => 1,
                                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($__env->exists('admin-views.product.partials._category_and_general')) echo $__env->make('admin-views.product.partials._category_and_general', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if ($__env->exists('admin-views.product.partials._price_and_stock')) echo $__env->make('admin-views.product.partials._price_and_stock', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if ($__env->exists('admin-views.product.partials._food_variations')) echo $__env->make('admin-views.product.partials._food_variations', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if ($__env->exists('admin-views.product.partials._ai_sidebar')) echo $__env->make('admin-views.product.partials._ai_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <div class="col-lg-12">
                    <?php if ($__env->exists('admin-views.product.partials._seo-section')) echo $__env->make('admin-views.product.partials._seo-section', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                <div class="col-lg-12">
                    <div class="btn--container justify-content-end">
                        <button type="reset" id="reset_btn"
                            class="btn btn--reset"><?php echo e(translate('messages.reset')); ?></button>
                        <button type="submit" class="btn btn--primary"><?php echo e(translate('messages.submit')); ?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>

<?php $__env->stopSection(); ?>


<?php $__env->startPush('script_2'); ?>
    <script src="<?php echo e(dynamicAsset('assets/admin')); ?>/js/tags-input.min.js"></script>
    <script src="<?php echo e(dynamicAsset('assets/admin/js/spartan-multi-image-picker.js')); ?>"></script>
    <script src="<?php echo e(dynamicAsset('assets/admin')); ?>/js/view-pages/product-index.js"></script>



    <script src="<?php echo e(dynamicAsset('assets/admin/js/AI/products/product-title-autofill.js')); ?>"></script>
    <script src="<?php echo e(dynamicAsset('assets/admin/js/AI/products/product-description-autofill.js')); ?>"></script>
    <script src="<?php echo e(dynamicAsset('assets/admin/js/AI/products/general-setup-autofill.js')); ?>"></script>
    <script src="<?php echo e(dynamicAsset('assets/admin/js/AI/products/product-others-autofill.js')); ?>"></script>
    <script src="<?php echo e(dynamicAsset('assets/admin/js/AI/products/variation-setup-auto-fill.js')); ?>"></script>
    


    <script src="<?php echo e(dynamicAsset('assets/admin/js/AI/products/ai-sidebar.js')); ?>"></script>

    <script src="<?php echo e(dynamicAsset('assets/admin/js/AI/products/compressor/image-compressor.js')); ?>"></script>
    <script src="<?php echo e(dynamicAsset('assets/admin/js/AI/products/compressor/compressor.min.js')); ?>"></script>


    <script>
        "use strict";
        $('#stock_type').on('change', function() {
            if ($(this).val() == 'unlimited') {
                $('.stock_disable').prop('readonly', true).prop('required', false).attr('placeholder',
                    '<?php echo e(translate('Unlimited')); ?>').val('');
                $('.hide_this').addClass('d-none');
            } else {
                $('.stock_disable').prop('readonly', false).prop('required', true).attr('placeholder',
                    '<?php echo e(translate('messages.Ex:_100')); ?>');
                $('.hide_this').removeClass('d-none');
            }
        });
        $(document).ready(function() {
            $("#description-default").on('input', function() {
                let value = $(this).val().substring(0, 600);
                $('#meta_description').val(value);
            });

            $("#default_name").on('input', function() {
                let value = $(this).val().substring(0, 100);
                $('#meta_title').val(value);
            });
        });

        updatestockCount();

        function updatestockCount() {
            if ($('#stock_type').val() == 'unlimited') {
                $('.stock_disable').prop('readonly', true).prop('required', false).attr('placeholder',
                    '<?php echo e(translate('Unlimited')); ?>').val('');
                $('.hide_this').addClass('d-none');
            } else {
                $('.stock_disable').prop('readonly', false).prop('required', true).attr('placeholder',
                    '<?php echo e(translate('messages.Ex:_100')); ?>');
                $('.hide_this').removeClass('d-none');
            }
        }

        $(document).ready(function() {
            $("#add_new_option_button").click(function() {
                add_new_option_button();
            });
        });

        let count = 0;
        let countRow = 0;

        function add_new_option_button() {
            $('#empty-variation').hide();
            count++;
            let add_option_view = <?php echo $__env->make('admin-views.product.partials._js_new_variations_div', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>;
            $("#add_new_option").append(add_option_view);
            updatestockCount();
        }


        function add_new_row_button(data) {
            count = data;
            countRow = 1 + $('#option_price_view_' + data).children('.add_new_view_row_class').length;
            let add_new_row_view = `
            <div class="row add_new_view_row_class mb-3 position-relative pt-3 pt-sm-0">
                <div class="col-md-3 col-sm-5">
                        <label for=""><?php echo e(translate('Option_name')); ?> &nbsp;<span class="form-label-secondary text-danger"
                                data-toggle="tooltip" data-placement="right"
                                data-original-title="<?php echo e(translate('messages.Required.')); ?>"> *
                                </span></label>
                        <input class="form-control" required type="text" name="options[` + count + `][values][` +
                countRow + `][label]" id="">
                    </div>
                    <div class="col-md-3 col-sm-5">
                        <label for=""><?php echo e(translate('Additional_price')); ?> (<?php echo e(\App\CentralLogics\Helpers::currency_symbol()); ?>)&nbsp;<span class="form-label-secondary text-danger"
                                data-toggle="tooltip" data-placement="right"
                                data-original-title="<?php echo e(translate('messages.Required.')); ?>"> *
                                </span></label>
                        <input class="form-control"  required type="number" min="0" step="0.01" name="options[` +
                count + `][values][` + countRow +
                `][optionPrice]" id="">
                    </div>


                    <div class="col-md-3 col-sm-6 hide_this">
                                                <label for=""><?php echo e(translate('Stock')); ?></label>
                                                <input class="form-control stock_disable count_stock" required type="number" min="0" max="9999999" name="options[` +
                count + `][values][` + countRow + `][total_stock]" id="">
                                            </div>


                    <div class="col-sm-2 max-sm-absolute">
                        <label class="d-none d-sm-block">&nbsp;</label>
                        <div class="mt-1">
                            <button type="button" class="btn btn-danger btn-sm deleteRow"
                                title="<?php echo e(translate('Delete')); ?>">
                                <i class="tio-add-to-trash"></i>
                            </button>
                        </div>
                </div>
            </div>`;
            $('#option_price_view_' + data).append(add_new_row_view);
            updatestockCount();

        }

        $('#restaurant_id').on('change', function() {
            let route = '<?php echo e(url('/')); ?>/admin/restaurant/get-addons?data[]=0&restaurant_id=';
            let restaurant_id = $(this).val();
            let id = 'add_on';

            // The addons are the picked restaurant's own, so the field stays disabled -
            // with the hint showing why - until there is a restaurant to read them from.
            $('#add_on').prop('disabled', !restaurant_id);
            $('#addon_hint').toggleClass('d-none', !!restaurant_id);

            getRestaurantData(route, restaurant_id, id);
            loadRestaurantCategories(restaurant_id);

        }).trigger('change');

        function getRestaurantData(route, restaurant_id, id) {
            $.get({
                url: route + restaurant_id,
                dataType: 'json',
                success: function(data) {
                    $('#' + id).empty().append(data.options);
                },
            });
        }

        $('.get-request').on('change', function() {
            let route = '<?php echo e(url('/')); ?>/admin/food/get-categories?parent_id=' + $(this).val();
            let id = 'sub-categories';
            getRequest(route, id);
        });

        function getRequest(route, id) {
            $.get({
                url: route,
                dataType: 'json',
                success: function(data) {
                    $('#' + id).empty().append(data.options);
                },
            });
        }

        function loadRestaurantCategories(restaurantId, selectedId = '') {
            let $restaurantCategory = $('#restaurant_category_id');
            $restaurantCategory.attr('data-id', selectedId);

            if (!restaurantId) {
                $restaurantCategory.empty().append('<option value=""><?php echo e(translate('Select_Restaurant_Category')); ?></option>').val('');
                // No restaurant selected → no asterisk, not required.
                $restaurantCategory.prop('required', false);
                $('.restaurant-category-required-mark').hide();
                return;
            }

            $.get({
                url: '<?php echo e(route('admin.food.get-restaurant-categories')); ?>',
                data: {
                    restaurant_id: restaurantId,
                    selected_id: selectedId
                },
                dataType: 'json',
                success: function(data) {
                    $restaurantCategory.empty().append(data.options).val(selectedId).trigger('change');
                    // Toggle the "*" mark + required attribute based on whether
                    // the selected restaurant has any of its own categories.
                    const required = !!(data && data.has_categories);
                    $restaurantCategory.prop('required', required);
                    $('.restaurant-category-required-mark').toggle(required);
                },
            });
        }

        $(document).on('ready', function() {
            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function() {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
        $('.js-data-example-ajax').select2({
            ajax: {
                url: '<?php echo e(url('/')); ?>/admin/restaurant/get-restaurants',
                data: function(params) {
                    return {
                        q: params.term, // search term
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

        function validateImageSize(inputSelector, imageType = "Image", maxSizeMB = 2) {
            let fileInput = $(inputSelector)[0];
            if (fileInput && fileInput.files.length > 0) {
                let fileSize = fileInput.files[0].size;
                if (fileSize > maxSizeMB * 1024 * 1024) {
                    toastr.error(`${imageType} size should not exceed ${maxSizeMB}MB`, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    return false;
                }
            }
            return true;
        }


        $('#reset_btn').click(function() {
            $('#restaurant_id').val(null).trigger('change');
            $('#category_id').val(null).trigger('change');
            $('#categories').val(null).trigger('change');
            $('#restaurant_category_id').empty().append('<option value=""><?php echo e(translate('Select_Restaurant_Category')); ?></option>').val('');
            $('#sub-veg').val(0).trigger('change');
            $('#add_on').val(null).trigger('change');
            $('#viewer').attr('src', "<?php echo e(dynamicAsset('assets/admin/img/upload.png')); ?>");
            $('#stock_type').val('unlimited').trigger('change');
            updatestockCount();
        })
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/product/index.blade.php ENDPATH**/ ?>