<?php if(isset($page_data) && count($page_data) > 0): ?>
    <?php
        $all_data = data_get($page_data, 'data', []);
        $input_fields = [];
        $file_fields = [];
        foreach ($all_data as $key => $item) {
            if ($item['field_type'] == 'file') {
                $file_fields[] = ['key' => $key, 'item' => $item];
            } else {
                $input_fields[] = ['key' => $key, 'item' => $item];
            }
        }
    ?>

    <div class="card shadow--card-2 mt-3 col-lg-12">
        <div class="card-header">
            <div>
                <h3 class="mb-1">
                    <span><?php echo e(translate('messages.Additional_Data')); ?></span>
                </h3>
                <p class="m-0 fs-12"><?php echo e(translate('messages.Setup your additional data')); ?></p>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php if(count($input_fields) > 0): ?>
                    <div class="col-lg-12">
                        <div class="p-xxl-20 p-12 global-bg-box rounded">
                            <div class="row g-3">
                                <?php $__currentLoopData = $input_fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $key = $data['key'];
                                        $item = $data['item'];
                                        $value = $additional_data[$item['input_data']] ?? '';
                                    ?>

                                    <?php if(!in_array($item['field_type'], ['file', 'check_box'])): ?>
                                        <div class="col-md-4 col-12">
                                            <div class="form-group m-0">
                                                <label class="form-label" for="<?php echo e($item['input_data']); ?>">
                                                    <?php echo e(translate($item['input_data'])); ?> <?php echo $item['is_required'] == 1 ? '<span class="text-danger">*</span>' : ''; ?>

                                                </label>
                                                <input
                                                    id="<?php echo e($item['input_data']); ?>"
                                                    <?php echo e($item['is_required'] == 1 ? 'required' : ''); ?>

                                                    type="<?php echo e($item['field_type'] == 'phone' ? 'tel' : $item['field_type']); ?>"
                                                    name="additional_data[<?php echo e($item['input_data']); ?>]"
                                                    class="form-control h--45px"
                                                    placeholder="<?php echo e(translate($item['placeholder_data'])); ?>"
                                                    value="<?php echo e(old('additional_data.'.$item['input_data'], $value)); ?>"
                                                >
                                            </div>
                                        </div>

                                    <?php elseif($item['field_type'] == 'check_box' && $item['check_data']): ?>
                                        <div class="col-md-4 col-12">
                                            <div class="form-group m-0">
                                                <label class="form-label"><?php echo e(translate($item['input_data'])); ?> <?php echo $item['is_required'] == 1 ? '<span class="text-danger">*</span>' : ''; ?></label>
                                                <?php $__currentLoopData = $item['check_data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $checked = in_array($i, (array)($additional_data[$item['input_data']] ?? [])) ? 'checked' : '';
                                                    ?>
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input type="checkbox"
                                                                   name="additional_data[<?php echo e($item['input_data']); ?>][]"
                                                                   class="form-check-input"
                                                                   value="<?php echo e($i); ?>"
                                                                <?php echo e($checked); ?>>
                                                            <?php echo e(translate($i)); ?>

                                                        </label>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(count($file_fields) > 0): ?>
                    <div class="col-lg-12">
                        <div class="p-xxl-20 p-12 global-bg-box rounded">
                            <div class="row g-3">
                                <?php $__currentLoopData = $file_fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $key = $data['key'];
                                        $item = $data['item'];
                                    ?>
                                    <?php if($item['field_type'] == 'file' ): ?>
                                        <?php if($item['media_data'] != null): ?>
                                                <?php
                                                $image = '';
                                                $pdf = '';
                                                $docs = '';
                                                if (data_get($item['media_data'], 'image', null)) {
                                                    $image = '.jpg,.jpeg,.png,.webp,';
                                                }
                                                if (data_get($item['media_data'], 'pdf', null)) {
                                                    $pdf = '.pdf,';
                                                }
                                                if (data_get($item['media_data'], 'docs', null)) {
                                                    $docs = '.doc,.docs,.docx,';
                                                }
                                                $accept = trim($image.$pdf.$docs, ',');
                                                ?>
                                            <div class="col-md-12 image_count_<?php echo e($key); ?>" data-id="<?php echo e($key); ?>" >
                                                <div class="global-bg-box rounded mt-3">
                                                    <div class="form-group m-0">
                                                        <div class="mb-20">
                                                            <label class="form-label" for="<?php echo e($item['input_data']); ?>"><?php echo e(translate($item['input_data'])); ?> <?php echo $item['is_required'] == 1 ? '<span class="text-danger">*</span>' : ''; ?></label>
                                                            <p class="m-0 fs-12">
                                                                <?php echo e(translate('messages.pdf, doc, jpg, jpeg, png, webp. File size : max 2 MB')); ?>

                                                            </p>
                                                        </div>
                                                        <div class="single-document-uploaderwrap multiple_doc" data-document-uploader-multiple >
                                                            <div>
                                                                <div class="file-assets"
                                                                     data-picture-icon="<?php echo e(dynamicAsset('assets/admin/img/picture.svg')); ?>"
                                                                     data-document-icon="<?php echo e(dynamicAsset('assets/admin/img/document.svg')); ?>"
                                                                     data-blank-thumbnail="<?php echo e(dynamicAsset('assets/admin/img/picture.svg')); ?>">
                                                                </div>
                                                                <!-- Upload box -->
                                                                <div class="doc-slider-wrapper position-relative">

                                                                    <?php
                                                                        $docs_for_this_field = $additional_documents[$item['input_data']] ?? [];
                                                                    ?>
                                                                    <div class="d-flex gap-3 pdf-container">
                                                                        <div class="document-upload-wrapper">
                                                                            <input
                                                                                type="<?php echo e($item['field_type']); ?>"
                                                                                id="<?php echo e($item['input_data']); ?>"
                                                                                name="additional_documents[<?php echo e($item['input_data']); ?>][]"
                                                                                class="document_input"
                                                                                accept="<?php echo e($accept ??  '.jpg, .jpeg, .png, .webp'); ?>"
                                                                                data-max-limit="<?php echo e(data_get($item['media_data'],'file_upload_quantity') ?? 9999); ?>"
                                                                                data-max-filesize="2"
                                                                                <?php echo e(data_get($item['media_data'],'upload_multiple_files',null) ==  1 || data_get($item['media_data'],'file_upload_quantity') >  1 || data_get($item['media_data'],'file_upload_quantity') == null ? 'multiple' : ''); ?>

                                                                            >
                                                                            <div class="textbox">
                                                                                <img width="40" height="40" class="svg"
                                                                                     src="<?php echo e(dynamicAsset('assets/admin/img/doc-uploaded.png')); ?>"
                                                                                     alt="">
                                                                                <p class="fs-12 mb-0 text-center">
                                                                                    <?php echo e(translate('Select a file or')); ?>

                                                                                    <span class="font-semibold"><?php echo e(translate('Drag & Drop')); ?></span>
                                                                                    <?php echo e(translate('here')); ?>

                                                                                </p>
                                                                            </div>
                                                                        </div>

                                                                        <?php $__currentLoopData = $docs_for_this_field; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <?php ($path = $path ?? 'additional_documents/dm/'); ?>
                                                                            <?php ($fileUrl = dynamicStorage('storage/app/public/' . $path . $doc['file'])); ?>
                                                                            <?php ($fileType = strtolower(pathinfo($doc['file'], PATHINFO_EXTENSION))); ?>
                                                                            <div class="pdf-single" data-file-name="<?php echo e($doc['file']); ?>" data-file-url="<?php echo e($fileUrl); ?>">
                                                                                <div class="pdf-frame">
                                                                                    <canvas class="pdf-preview d--none"></canvas>
                                                                                    <img class="pdf-thumbnail" src="<?php echo e(in_array($fileType, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tif', 'tiff']) ? $fileUrl : dynamicAsset('assets/admin/img/document.svg')); ?>">
                                                                                </div>

                                                                                <div class="overlay">
                                                                                    <div class="pdf-info">
                                                                                        <img src="<?php echo e(in_array($fileType, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tif', 'tiff']) ? dynamicAsset('assets/admin/img/picture.svg') : dynamicAsset('assets/admin/img/document.svg')); ?>" width="34" alt="File Type Logo">
                                                                                        <div class="file-name-wrapper">
                                                                                            <span class="file-name js-filename-truncate"><?php echo e($doc['file']); ?></span>
                                                                                            <span class="opacity-50"><?php echo e(translate('Click to view the file')); ?></span>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="actions d-flex gap-2">
                                                                                        <button type="button" class="btn btn-circle rounded btn-danger p-0 remove-existing-doc" style="--size:26px;" data-key="<?php echo e($item['input_data']); ?>" data-file="<?php echo e($doc['file']); ?>"><i class="tio-delete-outlined"></i></button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                                                    </div>

                                                                    <div>
                                                                        <div class="docSlide_prev">
                                                                            <div class="d-flex justify-content-center align-items-center h-100">
                                                                                <button type="button" class="btn btn-circle border-0 text-body bg-white shadow-sm">
                                                                                    <i class="tio-chevron-left fs-24"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                        <div class="docSlide_next">
                                                                            <div class="d-flex justify-content-center align-items-center h-100">
                                                                                <button type="button" class="btn btn-circle border-0 text-body bg-white shadow-sm">
                                                                                    <i class="tio-chevron-right fs-24"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/partials/_custom-fields.blade.php ENDPATH**/ ?>