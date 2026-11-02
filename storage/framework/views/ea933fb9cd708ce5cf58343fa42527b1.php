<!-- Header -->
<div class="card-header">
    <h5 class="card-header-title">
        <img src="<?php echo e(dynamicAsset('assets/admin/img/dashboard/top-selling.png')); ?>" alt="dashboard" class="card-header-icon">
        <span><?php echo e(translate('messages.top_selling_foods')); ?></span>
    </h5>
    <?php ($params=session('dash_params')); ?>
    <?php if($params['zone_id']!='all'): ?>
        <?php ($zone_name=\App\Models\Zone::where('id',$params['zone_id'])->first()->name); ?>
    <?php else: ?>
    <?php ($zone_name=translate('All')); ?>
    <?php endif; ?>
    <span class="badge badge-soft--info my-2"><?php echo e(translate('messages.zone')); ?> : <?php echo e($zone_name); ?></span>
</div>
<!-- End Header -->

<!-- Body -->
<div class="card-body">
    <?php if($top_sell->count() > 0): ?>
        <div class="row g-2">
            <?php $__currentLoopData = $top_sell; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4 col-sm-6">
                    <div class="grid-card top-selling-food-card pb-3 redirect-url" data-url="<?php echo e(route('admin.food.view',[$item['id']])); ?>">
                        <div class="position-relative">
                            <span class="sold--count-badge"><span><?php echo e(translate('messages.sold')); ?></span> <span>:</span> <span><?php echo e($item['order_count']); ?></span></span>
                            <img class="initial-43 onerror-image"
                                 src="<?php echo e($item['image_full_url'] ?? dynamicAsset('assets/admin/img/100x100/food.png')); ?>"  data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/100x100/food.png')); ?>"
                                alt="<?php echo e($item->name); ?> image">
                        </div>
                        <div class="text-center mt-3">
                            <span><?php echo e(Str::limit($item['name'],20,'...')); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <div class="d-flex justify-content-center align-items-center h-100 min-h-200">
            <div class="d-flex flex-column gap-3 justify-content-center align-items-center">
                <img src="<?php echo e(dynamicAsset('assets/admin/img/dashboard/top_food.svg')); ?>" alt="">
                <p><?php echo e(translate('No items available in this zone')); ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>
<!-- End Body -->
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/partials/_top-selling-foods.blade.php ENDPATH**/ ?>