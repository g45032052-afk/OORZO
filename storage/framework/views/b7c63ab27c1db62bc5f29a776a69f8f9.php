<!-- Header -->
<div class="card-header">
    <h5 class="card-header-title">
        <img src="<?php echo e(dynamicAsset('assets/admin/img/dashboard/most-popular.png')); ?>" alt="dashboard"
            class="card-header-icon">
        <?php echo e(translate('Most_Popular_Restaurants')); ?>

        <span data-toggle="tooltip" data-placement="right" data-original-title="<?php echo e(translate('most_popular_restaurants_based_on_users_wishlisted_Foods')); ?>" class="input-label-secondary"><img src="<?php echo e(dynamicAsset('assets/admin/img/info-circle.svg')); ?>" alt="<?php echo e(translate('messages.Most_Popular_Restaurants_Based_on_Users_Wishlisted_Foods.')); ?>"></span>

    </h5>
    <?php ($params = session('dash_params')); ?>
    <?php if($params['zone_id'] != 'all'): ?>
        <?php ($zone_name = \App\Models\Zone::where('id', $params['zone_id'])->first()->name); ?>
    <?php else: ?>
        <?php ($zone_name = translate('All')); ?>
    <?php endif; ?>
    <span class="badge badge-soft--info my-2"><?php echo e(translate('messages.zone')); ?> : <?php echo e($zone_name); ?></span>
</div>
<!-- End Header -->

<!-- Body -->
<div class="card-body">
    <?php if($popular->count() > 0): ?>
         <ul class="most-popular most-popular__restaurant">
            <?php $__currentLoopData = $popular; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li data-url="<?php echo e(route('admin.restaurant.view', $item->restaurant_id)); ?>" class="cursor-pointer redirect-url">
                    <div class="img-container">
                        <img class="onerror-image" data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/100x100/1.png')); ?>"
                             src="<?php echo e($item->restaurant['logo_full_url']); ?>" alt="<?php echo e(translate('store')); ?>">
                        <span class="ml-2">
                            <?php echo e(Str::limit($item->restaurant->name ?? translate('messages.Restaurant_deleted!'), 20, '...')); ?>

                            <?php echo $__env->make('partials._verified_restaurant_badge', ['restaurant' => $item->restaurant], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </span>
                    </div>
                    <span class="count">
                        <?php echo e($item['count']); ?> <i class="tio-heart"></i>
                    </span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    <?php else: ?>
        <div class="d-flex justify-content-center align-items-center h-100 min-h-200">
            <div class="d-flex flex-column gap-3 justify-content-center align-items-center">
                <img src="<?php echo e(dynamicAsset('assets/admin/img/dashboard/mp_restaurant.svg')); ?>" alt="">
                <p><?php echo e(translate('No restaurant available in this zone')); ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/partials/_popular-restaurants.blade.php ENDPATH**/ ?>