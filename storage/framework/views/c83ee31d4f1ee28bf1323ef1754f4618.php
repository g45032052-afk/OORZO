<form action="<?php echo e(route('admin.cuisine.update', [$cuisine['id']])); ?>" method="post" class="d-flex flex-column h-100"
      enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <div>
        <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
            <h3 class="mb-0"><?php echo e(translate('Edit_Cuisine')); ?></h3>
            <button type="button"
                    class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                    aria-label="Close">&times;
            </button>
        </div>
        <div class="custom-offcanvas-body p-20">
            <div class="bg--secondary rounded pt-2 px-3 mb-20 pb-3">
                <?php if($language): ?>
                    <div class="js-nav-scroller hs-nav-scroller-horizontal">
                        <ul class="nav nav-tabs mb-4 border-0">
                            <li class="nav-item">
                                <a class="nav-link lang_link active" href="#"
                                   id="default-link"><?php echo e(translate('messages.default')); ?></a>
                            </li>
                            <?php $__currentLoopData = $language; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="nav-item">
                                    <a class="nav-link lang_link" href="#"
                                       id="<?php echo e($lang); ?>-link"><?php echo e(\App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')'); ?></a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <div class="row">
                    <div class="col-12">
                        <?php if($language): ?>
                            <div class="form-group lang_form" id="default-form">
                                <label class="input-label"
                                       for="exampleFormControlInput1"><?php echo e(translate('messages.Cuisine_Name')); ?>

                                    (<?php echo e(translate('messages.default')); ?>)
                                    <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                          data-placement="right"
                                          data-original-title="<?php echo e(translate('messages.Required.')); ?>"> *
                                    </span>

                                </label>
                                <input type="text" name="name[]"
                                       value="<?php echo e($cuisine?->getRawOriginal('name')); ?>" class="form-control"
                                       placeholder="<?php echo e(translate('messages.new_cuisine')); ?>" maxlength="255">
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                            <?php $__currentLoopData = $language; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                    if (count($cuisine['translations'])) {
                                        $translate = [];
                                        foreach ($cuisine['translations'] as $t) {
                                            if ($t->locale == $lang && $t->key == 'name') {
                                                $translate[$lang]['name'] = $t->value;
                                            }
                                        }
                                    }
                                    ?>

                                <div class="form-group d-none lang_form" id="<?php echo e($lang); ?>-form">
                                    <label class="input-label"
                                           for="exampleFormControlInput1"><?php echo e(translate('messages.Cuisine_Name')); ?>

                                        (<?php echo e(strtoupper($lang)); ?>)
                                    </label>
                                    <input type="text" name="name[]" value="<?php echo e($translate[$lang]['name'] ?? ''); ?>"
                                           class="form-control"
                                           placeholder="<?php echo e(translate('messages.Cuisine_Name')); ?>" maxlength="191">
                                </div>
                                <input type="hidden" name="lang[]" value="<?php echo e($lang); ?>">
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <div class="form-group">
                                <label class="input-label"
                                       for="exampleFormControlInput1"><?php echo e(translate('messages.Cuisine_Name')); ?></label>
                                <input type="text" name="name" class="form-control"
                                       placeholder="<?php echo e(translate('messages.new_cuisine')); ?>"
                                       value="<?php echo e($cuisine?->getRawOriginal('name')); ?>" maxlength="191">
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                        <?php endif; ?>

                    </div>

                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex flex-column align-items-center gap-3">
                            <div class="d-flex flex-column align-items-center">
                                <div class="mb-20 text-center">
                                    <h5 class="mb-1">
                                        <?php echo e(translate('Cuisine image')); ?>

                                    </h5>
                                    <p class="mb-0 fs-12 gray-dark"><?php echo e(translate('Update Cuisine image')); ?></p>
                                </div>
                                <div class="text-center">
                                <?php echo $__env->make('admin-views.partials._image-uploader', [
                                            'id' => 'image-input',
                                            'name' => 'image',
                                            'ratio' => '1:1',
                                            'isRequired' => $cuisine['image_full_url'] ? false : true,
                                            'existingImage' => $cuisine['image_full_url'] ?? null,
                                            'imageExtension' => IMAGE_EXTENSION,
                                            'imageFormat' => IMAGE_FORMAT,
                                            'maxSize' => MAX_FILE_SIZE,
                                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php echo $__env->make('admin-views.category.partials._meta-section', ['data' => $cuisine], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        </div>
    </div>
    <div
        class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center mt-auto offcanvas-footer p-3 position-sticky">
        <button type="button"
                class="btn w-100 btn--secondary offcanvas-close h--40px"><?php echo e(translate('Cancel')); ?></button>
        <button type="submit" class="btn w-100 btn--primary h--40px"><?php echo e(translate('Update')); ?></button>
    </div>
</form>


<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/cuisine/partials/_edit.blade.php ENDPATH**/ ?>