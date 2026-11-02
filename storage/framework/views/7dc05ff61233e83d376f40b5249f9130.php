<?php $__env->startSection('title',translate('messages.update_banner')); ?>


<?php $__env->startSection('content'); ?>
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header mb-1">
            <div class="row align-items-center">
                <div class="col-sm">
                    <h1 class="page-header-title fs-24">
                        <!-- <i class="tio-add-circle-outlined"></i> -->
                        <?php echo e(translate('messages.add_new_banner')); ?></h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-body">
                        <form action="<?php echo e(route('admin.banner.update', [$banner->id])); ?>" method="post" id="banner_form">
                            <?php echo csrf_field(); ?>
                            <div class="row g-3">
                                <div class="col-lg-8">
                                    <div class="global-bg-box p-xxl-20 p-12 rounded">
                                        <div class="row">
                                           <div class="col-md-12">
                                               <div class="form-group">
                                                   <?php ($language=\App\Models\BusinessSetting::where('key','language')->first()); ?>
                                                   <?php ($language = $language->value ?? null); ?>
                                                   <?php ($default_lang = str_replace('_', '-', app()->getLocale())); ?>
                                                   <?php if($language): ?>
                                                   <div class="js-nav-scroller tabs-slide-language mb-4 hs-nav-scroller-horizontal">
                                                       <ul class="nav nav-tabs border-0">
                                                           <li class="nav-item">
                                                               <a class="nav-link lang_link active"
                                                               href="#"
                                                               id="default-link"><?php echo e(translate('messages.default')); ?></a>
                                                           </li>
                                                           <?php $__currentLoopData = json_decode($language); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                               <li class="nav-item">
                                                                   <a class="nav-link lang_link"
                                                                       href="#"
                                                                       id="<?php echo e($lang); ?>-link"><?php echo e(\App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')'); ?></a>
                                                               </li>
                                                           <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                       </ul>
                                                       <div class="arrow-area">
                                                            <div class="button-prev align-items-center">
                                                                <button type="button"
                                                                    class="btn btn-click-prev mr-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                                                                    <i class="tio-chevron-left fs-24"></i>
                                                                </button>
                                                            </div>
                                                            <div class="button-next align-items-center">
                                                                <button type="button"
                                                                    class="btn btn-click-next ml-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                                                                    <i class="tio-chevron-right fs-24"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                   </div>
                                                       <div class="lang_form" id="default-form">
                                                           <div class="form-group">
                                                               <label class="input-label" for="default_title"><?php echo e(translate('messages.title')); ?> (<?php echo e(translate('messages.default')); ?>)<span class="text-danger">*</span></label>
                                                               <input type="text" name="title[]" id="default_title" class="form-control" placeholder="<?php echo e(translate('messages.new_banner')); ?>" value="<?php echo e($banner->getRawOriginal('title')); ?>"  >
                                                           </div>
                                                           <input type="hidden" name="lang[]" value="default">
                                                       </div>
                                                       <?php $__currentLoopData = json_decode($language); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                           <?php
                                                               if(count($banner['translations'])){
                                                                   $translate = [];
                                                                   foreach($banner['translations'] as $t)
                                                                   {
                                                                       if($t->locale == $lang && $t->key=="title"){
                                                                           $translate[$lang]['title'] = $t->value;
                                                                       }
                                                                   }
                                                               }
                                                           ?>
                                                           <div class="d-none lang_form" id="<?php echo e($lang); ?>-form">
                                                               <div class="form-group">
                                                                   <label class="input-label" for="<?php echo e($lang); ?>_title"><?php echo e(translate('messages.title')); ?> (<?php echo e(strtoupper($lang)); ?>)</label>
                                                                   <input type="text" name="title[]" id="<?php echo e($lang); ?>_title" class="form-control" placeholder="<?php echo e(translate('messages.new_banner')); ?>" value="<?php echo e($translate[$lang]['title']??''); ?>"  >
                                                               </div>
                                                               <input type="hidden" name="lang[]" value="<?php echo e($lang); ?>">
                                                           </div>
                                                       <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                   <?php else: ?>
                                                   <div id="default-form">
                                                       <div class="form-group">
                                                           <label class="input-label" for="exampleFormControlInput1"><?php echo e(translate('messages.title')); ?> (<?php echo e(translate('messages.default')); ?>)<span class="text-danger">*</span></label>
                                                           <input type="text" name="title[]" class="form-control" placeholder="<?php echo e(translate('messages.new_banner')); ?>"  value="<?php echo e($banner->getRawOriginal('title')); ?>" maxlength="100" >
                                                       </div>
                                                       <input type="hidden" name="lang[]" value="default">
                                                   </div>
                                                   <?php endif; ?>
                                               </div>
                                           </div>
                                           <div class="col-md-6">
                                               <div class="form-group">
                                                   <label class="input-label" for="title"><?php echo e(translate('messages.zone')); ?><span class="text-danger">*</span></label>
                                                   <select name="zone_id" id="zone" class="form-control js-select2-custom get-request" data-url="<?php echo e(url('/')); ?>/admin/food/get-foods?zone_id=" data-id="choice_item">
                                                       <option disabled selected>---<?php echo e(translate('messages.select')); ?>---</option>
                                                       <?php ($zones=\App\Models\Zone::active()->get(['id','name'])); ?>

                                                       <?php $__currentLoopData = $zones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $zone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                           <?php if(auth('admin')?->user()?->zone_id): ?>
                                                               <?php if(auth('admin')->user()->zone_id == $zone->id): ?>
                                                                   <option value="<?php echo e($zone['id']); ?>" <?php echo e($zone->id == $banner->zone_id ? 'selected' : ''); ?>><?php echo e($zone['name']); ?></option>
                                                               <?php endif; ?>
                                                           <?php else: ?>
                                                               <option value="<?php echo e($zone['id']); ?>" <?php echo e($zone->id == $banner->zone_id ? 'selected' : ''); ?>><?php echo e($zone['name']); ?></option>
                                                           <?php endif; ?>
                                                       <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                   </select>
                                               </div>
                                           </div>
                                           <div class="col-md-6">
                                               <div class="form-group">
                                                   <label class="input-label" for="exampleFormControlInput1"><?php echo e(translate('messages.banner_type')); ?><span class="text-danger">*</span></label>
                                                   <select id="banner_type" name="banner_type" class="form-control banner_type_change">
                                                       <option value="restaurant_wise" <?php echo e($banner->type == 'restaurant_wise'? 'selected':''); ?>><?php echo e(translate('messages.restaurant_wise')); ?></option>
                                                       <option value="item_wise" <?php echo e($banner->type == 'item_wise'? 'selected':''); ?>><?php echo e(translate('messages.food_wise')); ?></option>
                                                   </select>
                                               </div>
                                           </div>
                                           <div class="col-md-12">
                                               <div class="form-group" id="restaurant_wise">
                                                   <label class="input-label" for="exampleFormControlSelect1"><?php echo e(translate('messages.restaurant')); ?><span class="text-danger">*</span><span
                                                           class="input-label-secondary"></span></label>
                                                   <select name="restaurant_id" class="js-data-example-ajax" id="resturant_ids"  title="Select Restaurant">
                                                       <?php if($banner->type=='restaurant_wise'): ?>
                                                        <?php ($restaurant = \App\Models\Restaurant::where('id', $banner->data)->first()); ?>
                                                           <?php if($restaurant): ?>
                                                               <option value="<?php echo e($restaurant->id); ?>" data-verified="<?php echo e((int) $restaurant->verified_seller); ?>" selected><?php echo e($restaurant->name); ?></option>
                                                           <?php endif; ?>
                                                       <?php endif; ?>
                                                   </select>
                                               </div>
                                           </div>
                                           <div class="col-md-12">
                                               <div class="form-group" id="item_wise">
                                                   <label class="input-label" for="exampleFormControlInput1"><?php echo e(translate('messages.select_food')); ?><span class="text-danger">*</span></label>
                                                   <select name="item_id" id="choice_item" class="form-control js-select2-custom" placeholder="<?php echo e(translate('messages.select_food')); ?>">

                                                   </select>
                                               </div>
                                           </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="p-xxl-20 d-flex align-items-center justify-content-center p-12 global-bg-box text-center rounded h-100">
                                        <div class="">
                                            <div class="mb-lg-4 mb-3 text-start">
                                                <h5 class="mb-1">
                                                    <?php echo e(translate('Banner Image')); ?> <span class="text-danger">*</span>
                                                </h5>
                                                <p class="mb-0 fs-12 gray-dark"><?php echo e(translate('Upload your image here')); ?></p>
                                            </div>
                                            <div class="upload-file  mx-auto">
                                                <input type="file" name="image" class="upload-file__input single_file_input"
                                                        accept=".webp, .jpg, .jpeg, .png, .gif" <?php if(!$banner->image): ?> required <?php endif; ?>>
                                                <label class="upload-file__wrapper ratio-2-1 m-0">
                                                    <div class="upload-file-textbox text-center" style="">
                                                        <img width="22" class="svg" src="<?php echo e(dynamicAsset('assets/admin/img/image-upload.png')); ?>" alt="img">
                                                        <h6 class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">
                                                            <span class="text-info"><?php echo e(translate('Click to upload')); ?></span>
                                                            <br>
                                                            <?php echo e(translate('Or drag and drop')); ?>

                                                        </h6>
                                                    </div>
                                                    <img class="upload-file-img" loading="lazy" src="<?php echo e($banner['image_full_url']); ?>" data-default-src="<?php echo e($banner['image_full_url']); ?>" alt="" style="display: none;">
                                                </label>
                                                <div class="overlay">
                                                    <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                                        <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                            <i class="tio-invisible"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                            <i class="tio-edit"></i>
                                                        </button>
                                                        <button type="button" class="remove_btn btn icon-btn">
                                                            <i class="tio-delete text-danger"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="fs-10 text-center max-w-187px mx-auto mb-0 mt-20">
                                                <?php echo e(translate('Supported format : JPG, JPEG, PNG, Gif mage size : Max 2 MB')); ?> <span class="font-medium text-title"><?php echo e(translate('(2:1)')); ?></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="btn--container justify-content-end mt-4">
                                <button id="reset_btn" type="reset" class="btn btn--reset"><?php echo e(translate('messages.reset')); ?></button>
                                <button type="submit" class="btn btn--primary"><?php echo e(translate('messages.submit')); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- End Table -->
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
<script src="<?php echo e(dynamicAsset('assets/admin')); ?>/js/view-pages/banner-index.js"></script>
<script>
    "use strict";
    $(document).on('ready', function () {
        let zone_id = <?php echo e($banner->zone_id); ?>;
        banner_type_change('<?php echo e($banner->type); ?>');

        $(document).on('change', '#zone', function(){
            zone_id = $(this).val() || true;
        });

        $('.js-data-example-ajax').select2({
            ajax: {
                url: '<?php echo e(url('/')); ?>/admin/restaurant/get-restaurants',
                data: function (params) {
                    return {
                        q: params.term,
                        zone_ids: [zone_id],
                        page: params.page
                    };
                },
                processResults: function (data) {
                    return { results: data };
                }
            }
        });

        $('.js-select2-custom').each(function () {
            $.HSCore.components.HSSelect2.init($(this));
        });
    });

    $('.banner_type_change').on('change', function (){
        let order_type = $(this).val();
        banner_type_change(order_type);
    });

    function banner_type_change(order_type) {
        if(order_type=='item_wise') {
            $('#restaurant_wise').hide();
            $('#item_wise').show();
            getRequest('<?php echo e(url('/')); ?>/admin/food/get-foods?zone_id=<?php echo e($banner->zone_id); ?>&data[]=<?php echo e($banner->data); ?>','choice_item');
        } else if(order_type=='restaurant_wise') {
            $('#restaurant_wise').show();
            $('#item_wise').hide();
        } else {
            $('#item_wise').hide();
            $('#restaurant_wise').hide();
        }
    }

    <?php if($banner->type == 'item_wise'): ?>
    getRequest('<?php echo e(url('/')); ?>/admin/food/get-foods?zone_id=<?php echo e($banner->zone_id); ?>&data[]=<?php echo e($banner->data); ?>','choice_item');
    <?php endif; ?>

    $('#banner_form').on('submit', function (e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: '<?php echo e(route('admin.banner.update', [$banner['id']])); ?>',
            method: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                if (data.errors) {
                    data.errors.forEach(error => toastr.error(error.message));
                } else {
                    toastr.success('<?php echo e(translate('messages.banner_updated_successfully')); ?>');
                    setTimeout(() => location.href = '<?php echo e(route('admin.banner.add-new')); ?>', 2000);
                }
            }
        });
    });

    $('#reset_btn').click(function(){
        // Reset text fields
        $('#default_title').val("<?php echo e($banner->getRawOriginal('title')); ?>");
        $('#banner_type').val("<?php echo e($banner->type); ?>").trigger('change');

        // Reset image
        let defaultImgSrc = $('.upload-file-img').data('default-src') || '<?php echo e($banner['image_full_url']); ?>';
        $('.upload-file-img').attr('src', defaultImgSrc).show();
        $('.upload-file-textbox').hide();

        // ✅ Reset zone (Select2 সহ)
        let defaultZone = "<?php echo e($banner->zone_id); ?>";
        $('#zone').val(defaultZone).trigger('change.select2');

        // ✅ Reset restaurant / item after zone reset
        setTimeout(function () {
            <?php if($banner->type == 'restaurant_wise'): ?>
            $('#resturant_ids').val("<?php echo e($banner->data); ?>").trigger('change.select2');
            <?php elseif($banner->type == 'item_wise'): ?>
            $('#choice_item').val("<?php echo e($banner->data); ?>").trigger('change.select2');
            <?php endif; ?>
        }, 400);
    });

    function getRequest(route, id) {
        $.get({
            url: route,
            dataType: 'json',
            success: function (data) {
                $('#' + id).empty().append(data.options);
            },
        });
    }
</script>

<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/banner/edit.blade.php ENDPATH**/ ?>