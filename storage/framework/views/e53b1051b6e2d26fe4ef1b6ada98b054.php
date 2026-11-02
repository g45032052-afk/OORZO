<?php $__env->startSection('title',translate('Create_Happy_Hour')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php echo $__env->make('admin-views.happy-hour.partials._schedule_styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title"><?php echo e(translate('messages.Happy_Hour')); ?></h1>
        </div>

        <form action="<?php echo e(route('admin.happy-hour.store')); ?>" method="post" enctype="multipart/form-data" id="happy-hour-form">
            <?php echo csrf_field(); ?>
            <?php echo $__env->make('admin-views.happy-hour.partials._form_body', ['happyHour' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="btn--container justify-content-end mb-4">
                <button type="reset" class="btn min-w-120 btn--reset"><?php echo e(translate('messages.reset')); ?></button>
                <button type="submit" class="btn min-w-120 btn--primary"><?php echo e(translate('messages.submit')); ?></button>
            </div>
        </form>
    </div>

    <?php echo $__env->make('admin-views.happy-hour.partials._schedule_modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <script>const initialState = {};</script>
    <?php echo $__env->make('admin-views.happy-hour.partials._schedule_scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('admin-views.happy-hour.partials._overlap_alert', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <script>
        "use strict";
        $('#happy-hour-form').on('submit', function (e) {
            e.preventDefault();
            syncDateRange();

            // Held down until the answer comes back: without it a double click sent two
            // identical creates, and both landed.
            const $button = $(this).find('button[type="submit"]');
            if ($button.prop('disabled')) return;
            $button.prop('disabled', true);
            const release = () => $button.prop('disabled', false);

            let formData = new FormData(this);
            $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
            $.post({
                url: '<?php echo e(route('admin.happy-hour.store')); ?>',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.errors) {
                        release();
                        if (handleScheduleConflict(data.errors)) return;
                        data.errors.forEach(e => toastr.error(e.message, {CloseButton: true, ProgressBar: true}));
                    } else {
                        toastr.success('<?php echo e(translate('Happy_Hour_created_successfully!')); ?>', {CloseButton: true, ProgressBar: true});
                        setTimeout(() => location.href = '<?php echo e(route('admin.happy-hour.list')); ?>', 2000);
                    }
                },
                error: function (xhr) {
                    release();
                    const errors = xhr.responseJSON?.errors || [];

                    // An overlap comes back 409 and gets the modal; everything else toasts.
                    if (handleScheduleConflict(errors)) return;

                    errors.length
                        ? errors.forEach(e => toastr.error(e.message))
                        : toastr.error('<?php echo e(translate('messages.something_went_wrong')); ?>');
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/happy-hour/index.blade.php ENDPATH**/ ?>