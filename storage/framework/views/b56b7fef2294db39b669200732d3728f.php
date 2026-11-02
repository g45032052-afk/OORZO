<?php if($documents && count(json_decode($documents, true)) > 0): ?>
<div class="row mb-2">
    <div class="col-lg-12 mb-2 mt-2">
        <div class="card ">
            <div class="card-header justify-content-between align-items-center">
                <label class="input-label text-capitalize d-inline-flex align-items-center m-0">
                    <h5 class="line--limit-1"><i class="tio-file-text-outlined"></i>
                        <?php echo e(translate('Documents')); ?> </h5>
                    <span data-toggle="tooltip" data-placement="right"
                        data-original-title="<?php echo e(translate('Optional information about restaurant documents.')); ?>" class="input-label-secondary">
                        <img src="<?php echo e(dynamicAsset('assets/admin/img/info-circle.svg')); ?>" alt="info"></span>
                </label>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-3">
                    <?php $__currentLoopData = json_decode($documents, true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(!empty($item)): ?>
                    <div class="w-100">
                        <h5 class="mb-3 text-capitalize"><?php echo e(translate($key)); ?></h5>
                        <div class="d-flex flex-wrap gap-4 align-items-start">
                            <?php ($item = is_string($item) ? json_decode($item, true) : $item); ?>
                            <?php $__currentLoopData = $item; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($file = is_string($file) ? ['file' => $file, 'storage' => 'public'] : $file); ?>
                            <?php ($full_url = \App\CentralLogics\Helpers::get_full_url($prefix, $file['file'], $file['storage'])); ?>
                            <?php ($path_info = pathinfo($full_url)); ?>
                            <?php ($f_date = $path_info['extension'] ?? ''); ?>

                            <?php if(in_array($f_date, ['pdf', 'doc', 'docs', 'docx'])): ?>
                                <?php if($f_date == 'pdf'): ?>
                                    <div class="attachment-card max-w-360">
                                        <a href="<?php echo e($full_url); ?>" target="_blank" rel="noopener noreferrer">
                                            <div class="img">
                                                <iframe
                                                    src="https://docs.google.com/gview?url=<?php echo e($full_url); ?>&embedded=true"></iframe>
                                            </div>
                                        </a>
                                        <a href="<?php echo e($full_url); ?>" download class="download-icon mt-3">
                                            <img src="<?php echo e(dynamicAsset('assets/admin/img/download/download.svg')); ?>"
                                                alt="">
                                        </a>
                                        <a href="<?php echo e($full_url); ?>" target="_blank" rel="noopener noreferrer" class="pdf-info">
                                            <img src="<?php echo e(dynamicAsset('assets/admin/new-img/pdf.png')); ?>" alt="">
                                            <div class="w-0 flex-grow-1">
                                                <h6 class="title"><?php echo e(translate('Click_To_View_The_file.pdf')); ?></h6>
                                            </div>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="attachment-card max-w-360">
                                        <a href="<?php echo e($full_url); ?>" target="_blank" rel="noopener noreferrer">
                                            <div class="img">
                                                <iframe
                                                    src="https://docs.google.com/gview?url=<?php echo e($full_url); ?>&embedded=true"></iframe>
                                            </div>
                                        </a>
                                        <a href="<?php echo e($full_url); ?>" download class="download-icon mt-3">
                                            <img src="<?php echo e(dynamicAsset('assets/admin/img/download/download.svg')); ?>"
                                                alt="">
                                        </a>
                                        <a href="<?php echo e($full_url); ?>" target="_blank" rel="noopener noreferrer" class="pdf-info">
                                            <img src="<?php echo e(dynamicAsset('assets/admin/new-img/doc.png')); ?>" alt="">
                                            <div class="w-0 flex-grow-1">
                                                <h6 class="title"><?php echo e(translate('Click_To_View_The_file.doc')); ?></h6>
                                            </div>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php elseif(in_array($f_date, ['jpg', 'jpeg', 'png', 'webp'])): ?>
                                <div class="attachment-card max-w-360">
                                    <a href="<?php echo e($full_url); ?>" download class="download-icon mt-3">
                                        <img src="<?php echo e(dynamicAsset('assets/admin/img/download/download.svg')); ?>"
                                            alt="">
                                    </a>
                                    <img src="<?php echo e($full_url); ?>" class="aspect-615-350 cursor-pointer mw-100 object--cover"
                                        alt="" data-toggle="modal" data-target="#document-<?php echo e($key); ?>-<?php echo e($index); ?>">
                                </div>
                                <?php $__env->startPush('script_2'); ?>
                                    <div class="modal fade" id="document-<?php echo e($key); ?>-<?php echo e($index); ?>" tabindex="-1" role="dialog"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title"><?php echo e(translate('messages.document_view')); ?></h4>
                                                    <button type="button" class="close" data-dismiss="modal"><span
                                                            aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <img src="<?php echo e($full_url); ?>" class="w-100" alt="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php $__env->stopPush(); ?>
                            <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/partials/_additional_documents.blade.php ENDPATH**/ ?>