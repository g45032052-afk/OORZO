<?php $__env->startSection('title',translate('Add_New_Campaign')); ?>

<?php $__env->startPush('css_or_js'); ?>

<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><i class="tio-add-circle-outlined"></i></div>
                        <?php echo e(translate('messages.Add_New_Campaign')); ?>

                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="card">
            <div class="card-body">
                <form action="<?php echo e(route('admin.campaign.store-basic')); ?>" method="post" enctype="multipart/form-data" id="campaign-form">
                    <?php echo csrf_field(); ?>
                    <?php ($language=\App\Models\BusinessSetting::where('key','language')->first()); ?>
                    <?php ($language = $language->value ?? null); ?>
                    <?php ($default_lang = str_replace('_', '-', app()->getLocale())); ?>
                    <?php if($language): ?>
                                <div class="js-nav-scroller hs-nav-scroller-horizontal">
                        <ul class="nav nav-tabs mb-4">
                            <li class="nav-item">
                                <a class="nav-link lang_link active" href="#" id="default-link"><?php echo e(translate('Default')); ?></a>
                            </li>
                            <?php $__currentLoopData = json_decode($language); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="nav-item">
                                    <a class="nav-link lang_link"  href="#" id="<?php echo e($lang); ?>-link"><?php echo e(\App\CentralLogics\Helpers::get_language_name($lang).'('.strtoupper($lang).')'); ?></a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                                </div>
                        <div class="mb-1 lang_form" id="default-form">
                            <div class="form-group">
                                <label class="input-label" for="default_title"><?php echo e(translate('messages.title')); ?> (<?php echo e(translate('Default')); ?>)</label>
                                <input type="text"  name="title[]" id="default_title" class="form-control h--45px" placeholder="<?php echo e(translate('messages.Ex_:_Campaign')); ?>"  >
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                            <div class="form-group mb-0">
                                <label class="input-label" for="exampleFormControlInput1"><?php echo e(translate('messages.short_description')); ?> (<?php echo e(translate('Default')); ?>)</label>
                                <textarea type="text" name="description[]" class="form-control ckeditor"></textarea>
                            </div>
                        </div>
                        <?php $__currentLoopData = json_decode($language); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="mb-1 d-none lang_form" id="<?php echo e($lang); ?>-form">
                                <div class="form-group">
                                    <label class="input-label" for="<?php echo e($lang); ?>_title"><?php echo e(translate('messages.title')); ?> (<?php echo e(strtoupper($lang)); ?>)</label>
                                    <input type="text"  name="title[]" id="<?php echo e($lang); ?>_title" class="form-control h--45px" placeholder="<?php echo e(translate('messages.Ex_:_Campaign')); ?> "  >
                                </div>
                                <input type="hidden" name="lang[]" value="<?php echo e($lang); ?>">
                                <div class="form-group mb-0">
                                    <label class="input-label" for="exampleFormControlInput1"><?php echo e(translate('messages.short_description')); ?> (<?php echo e(strtoupper($lang)); ?>)</label>
                                    <textarea type="text" name="description[]" class="form-control ckeditor"></textarea>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                    <div class="mb-1" id="default-form">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1"><?php echo e(translate('messages.title')); ?> (<?php echo e(translate('Default')); ?>)</label>
                            <input type="text" name="title[]" class="form-control h--45px" placeholder="<?php echo e(translate('messages.Ex_:_Campaign')); ?> " >
                        </div>
                        <input type="hidden" name="lang[]" value="default">
                        <div class="form-group mb-0">
                            <label class="input-label" for="exampleFormControlInput1"><?php echo e(translate('messages.short_description')); ?> (<?php echo e(translate('Default')); ?>)</label>
                            <textarea type="text" name="description[]" class="form-control ckeditor"></textarea>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="row mt-3">
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="title"><?php echo e(translate('messages.start_date')); ?></label>
                                        <input type="date" id="date_from" class="form-control h--45px" required="" name="start_date">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="input-label" for="title"><?php echo e(translate('messages.end_date')); ?></label>
                                    <input type="date" id="date_to" class="form-control h--45px" required="" name="end_date">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label text-capitalize" for="title"><?php echo e(translate('messages.daily_start_time')); ?></label>
                                        <input type="time" id="start_time" class="form-control h--45px" name="start_time">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="input-label text-capitalize" for="title"><?php echo e(translate('messages.daily_end_time')); ?></label>
                                    <input type="time" id="end_time" class="form-control h--45px" name="end_time">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex flex-column align-items-center gap-3 mt-4">
                                <p class="mb-0"><?php echo e(translate('campaign_image')); ?></p>

                                <?php echo $__env->make('admin-views.partials._image-uploader', [
                                    'id' => 'image-input',
                                    'name' => 'image',
                                    'isRequired' => true,
                                    'existingImage' => null,
                                    'ratio' => '2:1',
                                    'imageExtension' => IMAGE_EXTENSION,
                                    'imageFormat' => IMAGE_FORMAT,
                                    'maxSize' => MAX_FILE_SIZE,
                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </div>
                        </div>
                    </div>

                    <div class="btn--container justify-content-end">
                        <button type="reset" id="reset_btn" class="btn btn--reset"><?php echo e(translate('messages.reset')); ?></button>
                        <button type="submit" class="btn btn--primary"><?php echo e(translate('messages.submit')); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <script src="<?php echo e(dynamicAsset('assets/admin')); ?>/js/view-pages/basic-campaign-index.js"></script>
    <script>
        "use strict";
        $('#campaign-form').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '<?php echo e(route('admin.campaign.store-basic')); ?>',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        toastr.success('<?php echo e(translate('Campaign_created_successfully!')); ?>', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function () {
                            location.href = '<?php echo e(route('admin.campaign.list', 'basic')); ?>';
                        }, 2000);
                    }
                }
            });
        });


            $('#reset_btn').click(function(){
                $('#choice_item').val(null).trigger('change');
            })
        </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/campaign/basic/index.blade.php ENDPATH**/ ?>