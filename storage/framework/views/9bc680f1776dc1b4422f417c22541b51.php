<?php
    $aspectRatio = match ($ratio ?? '1:1') {
        '1:1' => 'ratio-1',
        '2:1' => 'ratio-2-1',
        '3:1' => 'ratio-3-1',
        '9:1' => 'ratio-9-1',
        default => 'ratio-1',
    };
    $imageExtension = $imageExtension ?? IMAGE_EXTENSION;
    $maxSize = $maxSize ?? MAX_FILE_SIZE;
    $isRequired = $isRequired ?? false;
    $existingImage = $existingImage ?? '';
    $ratio = $ratio ?? '1:1';
    $id = $id ?? 'image-input';
    $name = $name ?? 'image';
    $imageFormat = $imageFormat ?? IMAGE_FORMAT;
    $pixel = isset($pixel) && $pixel !== '' ? $pixel . ' px' : null;
    $size = $pixel ?? $ratio;
?>

<div class="upload-file mx-auto" data-invalid-icon="<?php echo e(dynamicAsset('assets/admin/img/invalid-icon.png')); ?>">
    <input type="hidden" name="<?php echo e($name); ?>_deleted" class="image-delete-flag" value="0">
    <input type="file" name="<?php echo e($name); ?>" id="<?php echo e($id); ?>" class="upload-file__input single_file_input"
        accept="<?php echo e($imageExtension); ?>" <?php echo e($isRequired && !$existingImage ? 'required' : ''); ?>

        data-max-size="<?php echo e($maxSize); ?>">
    <label class="upload-file__wrapper <?php echo e($aspectRatio); ?> mx-auto m-0">
        <div class="upload-file-textbox text-center" style="">
            <img width="27" class="svg" src="<?php echo e(dynamicAsset('assets/admin/img/image-upload.png')); ?>" alt="img">
            <h6 class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">
                <span class="text-info"><?php echo e(translate('Click to upload')); ?></span>
                <br>
                <?php echo e(translate('Or drag and drop')); ?>

            </h6>
        </div>
        
        <img class="upload-file-img" loading="lazy" src="<?php echo e($existingImage); ?>" data-default-src="<?php echo e($defaultImage ?? ''); ?>" alt=""
            style="display: none;">
    </label>
    <div class="overlay">
        <div class="d-flex gap-1 justify-content-center align-items-center h-100">
            <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                <i class="tio-edit"></i>
            </button>
            <button type="button" class="btn btn-outline-info icon-btn view_btn">
                <i class="tio-invisible"></i>
            </button>
            <?php if(!$isRequired): ?>
                <button type="button" class="remove_btn btn icon-btn">
                    <i class="tio-delete text-danger"></i>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>
<p class="fs-10 text-center mb-0 mt-4 text-capitalize">
    <?php echo e(translate($imageFormat . ' Image size : Max ' . $maxSize . ' MB')); ?> <span
        class="font-medium text-title"><?php echo e(translate('(' . $size . ')')); ?></span>
</p>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/partials/_image-uploader.blade.php ENDPATH**/ ?>