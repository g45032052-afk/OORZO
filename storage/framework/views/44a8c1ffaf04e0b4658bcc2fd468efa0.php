<option value=""><?php echo e(translate('Select_Restaurant_Category')); ?></option>
<?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $restaurantCategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <option value="<?php echo e($restaurantCategory->id); ?>" <?php echo e((string) $selectedId === (string) $restaurantCategory->id ? 'selected' : ''); ?>>
        <?php echo e($restaurantCategory->name); ?>

    </option>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/product/partials/_restaurant_category_options.blade.php ENDPATH**/ ?>