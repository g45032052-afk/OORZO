<?php $__env->startSection('title',translate('messages.update_notification')); ?>

<?php $__env->startPush('css_or_js'); ?>

<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img width="18" src="<?php echo e(dynamicAsset('assets/admin/img/bell.png')); ?>" alt="public">
                        </div>
                        <span>
                            <?php echo e(translate('messages.notification_update')); ?>

                        </span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="card">
            <div class="card-body">
                <form action="<?php echo e(route('admin.notification.update',[$notification['id']])); ?>" method="post" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1"><?php echo e(translate('messages.title')); ?>

                                      <span class="text-danger">*</span>
                                </label>
                                <input id="notification_title" type="text" value="<?php echo e($notification['title']); ?>" name="notification_title" class="form-control" placeholder="<?php echo e(translate('messages.new_notification')); ?>" required maxlength="191">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1"><?php echo e(translate('messages.zone')); ?>

                                      <span class="text-danger">*</span>
                                </label>
                                <select id="zone" name="zone" class="form-control js-select2-custom" >
                                    <option value="all" <?php echo e(isset($notification->zone_id)?'':'selected'); ?>><?php echo e(translate('messages.all_zone')); ?></option>
                                    <?php $__currentLoopData = \App\Models\Zone::orderBy('name')->get(['id','name']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $z): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($z['id']); ?>"  <?php echo e($notification->zone_id==$z['id']?'selected':''); ?>><?php echo e($z['name']); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="input-label" for="tergat"><?php echo e(translate('messages.send_to')); ?>

                                      <span class="text-danger">*</span>
                                </label>

                                <select id="tergat" name="tergat" class="form-control" id="tergat" data-placeholder="<?php echo e(translate('messages.select_tergat')); ?>" required>
                                    <option value="customer" <?php echo e($notification->tergat=='customer'?'selected':''); ?>><?php echo e(translate('messages.customer')); ?></option>
                                    <option value="deliveryman" <?php echo e($notification->tergat=='deliveryman'?'selected':''); ?>><?php echo e(translate('messages.deliveryman')); ?></option>
                                    <option value="restaurant" <?php echo e($notification->tergat=='restaurant'?'selected':''); ?>><?php echo e(translate('messages.restaurant')); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                               <div class="p-xxl-20 p-12 global-bg-box rounded mb-20">
                                    <div class="pb-md-5">
                                        <div class="mb-4 text-start">
                                            <p class="mb-1">
                                                <?php echo e(translate('notification_banner')); ?>

                                            </p>

                                        </div>
                                        <div class="text-center">
                                            <?php echo $__env->make('admin-views.partials._image-uploader', [
                                                'id' => 'image-input',
                                                'name' => 'image',
                                                'ratio' => '3:1',
                                                'isRequired' => false,
                                                'existingImage' => $notification->image_full_url,
                                                'imageExtension' => IMAGE_EXTENSION,
                                                'imageFormat' => IMAGE_FORMAT,
                                                'maxSize' => MAX_FILE_SIZE,
                                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </div>
                                    </div>
                                </div>

                        </div>
                        <div class="col-md-8">
                            <label class="input-label" for="exampleFormControlInput1"><?php echo e(translate('messages.description')); ?>

                                  <span class="text-danger">*</span>
                            </label>
                            <textarea id="description" name="description" class="form-control h--md-200px" required><?php echo e($notification['description']); ?></textarea>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mb-0">
                        <button id="reset_btn" type="button" class="btn btn--reset"><?php echo e(translate('messages.reset')); ?></button>
                        <button type="submit" class="btn btn--primary"><?php echo e(translate('send_again')); ?></button>
                    </div>
                </form>
            </div>
            <!-- End Table -->
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <script>
        "use strict";
        $("#customFileEg1").change(function () {
            readURL(this);
        });
        $('#reset_btn').click(function(){
            $('#notification_title').val("<?php echo e($notification['title']); ?>");
            $('#zone').val("<?php echo e($notification->zone_id); ?>").trigger('change');
            $('#tergat').val("<?php echo e($notification->tergat); ?>").trigger('change');
            $('#viewer').attr('src', "<?php echo e(dynamicStorage('storage/app/public/notification')); ?>/<?php echo e($notification['image']); ?>");
            $('#customFileEg1').val(null);
            $('#description').val("<?php echo e($notification['description']); ?>");
        })
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/notification/edit.blade.php ENDPATH**/ ?>