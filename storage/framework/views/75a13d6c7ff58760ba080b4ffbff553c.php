
<?php if(\App\CentralLogics\Helpers::get_business_settings('verified_seller_badge') == 1 && isset( $restaurant?->restaurant_config) && $restaurant?->restaurant_config?->verified_seller): ?>
    
     <i class="tio-verified text-success" data-toggle="tooltip" data-placement="top"
        title="<?php echo e(translate('Verified Restaurant')); ?>"></i>
<?php endif; ?>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/partials/_verified_restaurant_badge.blade.php ENDPATH**/ ?>