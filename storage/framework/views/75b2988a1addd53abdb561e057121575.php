<form action="<?php echo e(route('admin.category.update', [$category['id']])); ?>" method="post" class="d-flex flex-column h-100"
      enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <div>
        <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
            <h3 class="mb-0"><?php echo e($model == 'sub' ? translate('Edit Main Sub Category') : translate('Edit Main Category')); ?></h3>
            <button type="button"
                    class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                    aria-label="Close">&times;
            </button>
        </div>
        <div class="custom-offcanvas-body p-20">
            <div class="bg--secondary rounded p-3 mb-20">
                <div class="bg-white rounded p-3 mb-20">
                    <?php if($language): ?>
                        <div class="js-nav-scroller hs-nav-scroller-horizontal">
                            <ul class="nav nav-tabs mb-4 border-0">
                                <li class="nav-item">
                                    <a class="nav-link lang_link1 active" href="#"
                                       id="default-link"><?php echo e(translate('messages.default')); ?></a>
                                </li>
                                <?php $__currentLoopData = $language; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="nav-item">
                                        <a class="nav-link lang_link1" href="#"
                                           id="<?php echo e($lang); ?>-link"><?php echo e(\App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')'); ?></a>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if($language): ?>
                        <div class="form-group mb-0 lang_form1" id="default-form1">
                            <label class="input-label"
                                   for="exampleFormControlInput1"><?php echo e($model == 'sub' ? translate('Main Sub Category Name') : translate('Main Category Name')); ?>

                                (<?php echo e(translate('messages.default')); ?>)
                                <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                      data-placement="right"
                                      data-original-title="<?php echo e(translate('messages.Required.')); ?>"> *
                                </span>

                            </label>
                            <input type="text" name="name[]"
                                   value="<?php echo e($category?->getRawOriginal('name')); ?>" class="form-control"
                                   placeholder="<?php echo e(translate('messages.new_category')); ?>" maxlength="255">
                        </div>
                        <input type="hidden" name="lang[]" value="default">
                        <?php $__currentLoopData = $language; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                if (count($category['translations'])) {
                                    $translate = [];
                                    foreach ($category['translations'] as $t) {
                                        if ($t->locale == $lang && $t->key == 'name') {
                                            $translate[$lang]['name'] = $t->value;
                                        }
                                    }
                                }
                                ?>

                            <div class="form-group mb-0 d-none lang_form1" id="<?php echo e($lang); ?>-form1">
                                <label class="input-label"
                                       for="exampleFormControlInput1"><?php echo e($model == 'sub' ? translate('Main Sub Category Name') : translate('Main Category Name')); ?>

                                    (<?php echo e(strtoupper($lang)); ?>)
                                </label>
                                <input type="text" name="name[]" value="<?php echo e($translate[$lang]['name'] ?? ''); ?>"
                                       class="form-control"
                                       placeholder="<?php echo e(translate('messages.Type_Category_Name')); ?>" maxlength="191">
                            </div>
                            <input type="hidden" name="lang[]" value="<?php echo e($lang); ?>">
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <div class="form-group mb-0">
                            <label class="input-label"
                                   for="exampleFormControlInput1"><?php echo e($model == 'sub' ? translate('Main Sub Category Name') : translate('Main Category Name')); ?></label>
                            <input type="text" name="name" class="form-control"
                                   placeholder="<?php echo e($model == 'sub' ? translate('messages.new_sub_category') : translate('messages.new_category')); ?>"
                                   value="<?php echo e($category?->getRawOriginal('name')); ?>" maxlength="191">
                        </div>
                        <input type="hidden" name="lang[]" value="default">
                    <?php endif; ?>

                </div>

                <div class="bg-white rounded p-3">

                     <div class="mb-3">
                        <label class="input-label" for="">
                            <?php echo e(translate('messages.Priority')); ?>

                            <span class="form-label-secondary" data-toggle="tooltip"
                                    data-placement="right"
                                    title="<?php echo e(translate('messages.category_priority_help')); ?>">
                                <i class="tio-info text-gray1 fs-16"></i>
                            </span>
                        </label>
                        <select name="priority" id="priority" class="form-control custom-select js-select2-custom">
                            <option value="0" <?php echo e(old('priority', (string) $category->priority) == '0' ? 'selected' : ''); ?>><?php echo e(translate('messages.Normal')); ?></option>
                            <option value="1" <?php echo e(old('priority', (string) $category->priority) == '1' ? 'selected' : ''); ?>><?php echo e(translate('messages.Medium')); ?></option>
                            <option value="2" <?php echo e(old('priority', (string) $category->priority) == '2' ? 'selected' : ''); ?>><?php echo e(translate('messages.High')); ?></option>
                        </select>
                    </div>
                    <?php if($category->position != 1): ?>
                        <?php if($categoryWiseTax): ?>
                            <div class="mb-3">
                                <span class="mb-2 d-block title-clr fw-normal"><?php echo e(translate('Select Tax Rate')); ?></span>
                                <select name="tax_ids[]" required id="" class="form-control multiple-select2"
                                        multiple="multiple" placeholder="<?php echo e(translate('--Select Tax Rate--')); ?>">
                                    <?php $__currentLoopData = $taxVats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $taxVat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option <?php echo e(in_array($taxVat->id, $taxVatIds) ? 'selected' : ''); ?>

                                                value="<?php echo e($taxVat->id); ?>"> <?php echo e($taxVat->name); ?>

                                            (<?php echo e($taxVat->tax_rate); ?>%)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        <div class="bg--secondary rounded p-3">
                            <div class="d-flex flex-column align-items-center">
                                <div class="mb-20 text-center">
                                    <h5 class="mb-1">
                                        <?php echo e(translate('Category image')); ?>

                                        <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                              data-placement="right"
                                              data-original-title="<?php echo e(translate('messages.Required.')); ?>"> *
                                </span>
                                    </h5>
                                    <p class="mb-0 fs-12 gray-dark"><?php echo e(translate('Update Category image')); ?></p>
                                </div>
                                <div class="upload-file mx-auto">
                                    <input type="file" name="image" class="upload-file__input single_file_input"
                                            accept="<?php echo e(IMAGE_EXTENSION); ?>" <?php echo e($category?->image ? '':'required'); ?>>
                                    <label class="upload-file__wrapper w-150 h-150px mx-auto ratio-1 m-0">
                                        <div class="upload-file-textbox text-center">
                                            <img width="22" class="svg"
                                                 src="<?php echo e(dynamicAsset('assets/admin/img/image-upload.png')); ?>"
                                                 alt="img">
                                            <h6 class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">
                                                <span class="text-info"><?php echo e(translate('Click to upload')); ?></span>
                                                <br>
                                                <?php echo e(translate('Or drag and drop')); ?>

                                            </h6>
                                        </div>
                                        <img class="upload-file-img" loading="lazy"
                                             src="<?php echo e($category?->image_full_url); ?>"
                                             alt="" style="">
                                    </label>
                                    <div class="overlay">
                                        <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                            <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                <i class="tio-invisible"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                <i class="tio-edit"></i>
                                            </button>
                                            <input type="hidden" name="image_remove" id="image_remove"
                                                   value="0">
                                        </div>
                                    </div>
                                </div>
                                <p class="fs-10 text-center mb-0 mt-20">
                                    <?php echo e(translate(IMAGE_FORMAT.' Less Than '.MAX_FILE_SIZE.'MB')); ?> <span
                                        class="font-medium text-title"><?php echo e(translate('(1:1)')); ?></span>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if($category->position != 1): ?>
                <?php echo $__env->make('admin-views.category.partials._meta-section', ['data' => $category], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>
        </div>
    </div>
    <div
        class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center mt-auto offcanvas-footer p-3 position-sticky">
        <button type="button"
                class="btn w-100 btn--secondary offcanvas-close h--40px"><?php echo e(translate('Cancel')); ?></button>
        <button type="submit" class="btn w-100 btn--primary h--40px"><?php echo e(translate('Update')); ?></button>
    </div>
</form>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/category/partials/_edit.blade.php ENDPATH**/ ?>