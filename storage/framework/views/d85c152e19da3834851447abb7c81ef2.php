<?php $__env->startSection('title',translate('Create_BOGO_Offer')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title"><?php echo e(translate('messages.BOGO_Offer')); ?></h1>
        </div>

        <form action="<?php echo e(route('admin.bogo-offer.store')); ?>" method="post" enctype="multipart/form-data" id="bogo-offer-form">
            <?php echo csrf_field(); ?>
            <?php echo $__env->make('admin-views.bogo-offer.partials._form_body', ['offer' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="btn--container justify-content-end my-4">
                <button type="reset" class="btn min-w-120 btn--reset"><?php echo e(translate('messages.reset')); ?></button>
                <button type="submit" class="btn min-w-120 btn--primary"><?php echo e(translate('messages.save')); ?></button>
            </div>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <script>
        const formUrl = '<?php echo e(route('admin.bogo-offer.store')); ?>';
        const redirectUrl = '<?php echo e(route('admin.bogo-offer.list')); ?>';
        const successMessage = '<?php echo e(translate('messages.BOGO_offer_created_successfully')); ?>';
    </script>
    <?php echo $__env->make('admin-views.bogo-offer.partials._form_scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/bogo-offer/index.blade.php ENDPATH**/ ?>