<?php $__env->startSection('title',translate('messages.Order_Invoice')); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('new_invoice', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/order/invoice.blade.php ENDPATH**/ ?>