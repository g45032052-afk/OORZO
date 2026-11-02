<div class="d-flex flex-wrap justify-content-between statistics--title-area">
    <div class="statistics--title pr-sm-3">
        <h4 class="m-0 mr-1">
            <?php echo e(translate('order_statistics')); ?>

        </h4>
        <?php ($params=session('dash_params')); ?>
        <?php if($params['zone_id']!='all'): ?>
            <?php ($zone_name=\App\Models\Zone::where('id',$params['zone_id'])->first()->name); ?>
        <?php else: ?>
            <?php ($zone_name=translate('All')); ?>
        <?php endif; ?>
        <span class="badge badge-soft--info my-2"><?php echo e(translate('messages.zone')); ?> : <?php echo e($zone_name); ?></span>
    </div>
    <div class="statistics--select">
        <select class="custom-select order-stats-update" name="statistics_type">
            <option
                value="overall" <?php echo e($params['statistics_type'] == 'overall'?'selected':''); ?>>
                <?php echo e(translate('messages.Overall')); ?>

            </option>
            <option
            value="this_year" <?php echo e($params['statistics_type'] == 'this_year'?'selected':''); ?>>
            <?php echo e(translate('messages.This_year')); ?>

        </option>
            <option
                value="this_month" <?php echo e($params['statistics_type'] == 'this_month'?'selected':''); ?>>
                <?php echo e(translate('messages.This_Month')); ?>

            </option>
            <option
                value="this_week" <?php echo e($params['statistics_type'] == 'this_week'?'selected':''); ?>>
                <?php echo e(translate('messages.This_Week')); ?>

            </option>

            <option
                value="today" <?php echo e($params['statistics_type'] == 'today'?'selected':''); ?>>
                <?php echo e(translate('messages.Today')); ?>

            </option>
        </select>
    </div>
</div>

<div class="row g-2">
    <div class="col-xl-3 col-sm-6">
        <div class="resturant-card dashboard--card bg--2 cursor-pointer redirect-url" data-url="<?php echo e(route('admin.order.list',['delivered'])); ?>">

            <h4 class="title"><?php echo e($data['delivered']); ?></h4>
            <span class="subtitle"><?php echo e(translate('messages.delivered_orders')); ?></span>
            <img class="resturant-icon" src="<?php echo e(dynamicAsset('assets/admin/img/dashboard/1.png')); ?>" alt="dashboard">
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="resturant-card dashboard--card bg--3 cursor-pointer redirect-url" data-url="<?php echo e(route('admin.order.list',['canceled'])); ?>">
            <h4 class="title"><?php echo e($data['canceled']); ?></h4>
            <span class="subtitle"><?php echo e(translate('messages.canceled_orders')); ?></span>
            <img class="resturant-icon" src="<?php echo e(dynamicAsset('assets/admin/img/dashboard/2.png')); ?>" alt="dashboard">
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="resturant-card dashboard--card bg--5 cursor-pointer redirect-url" data-url="<?php echo e(route('admin.order.list',['refunded'])); ?>">
            <h4 class="title"><?php echo e($data['refunded']); ?></h4>
            <span class="subtitle"><?php echo e(translate('messages.refunded_orders')); ?></span>
            <img class="resturant-icon" src="<?php echo e(dynamicAsset('assets/admin/img/dashboard/3.png')); ?>" alt="dashboard">
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="resturant-card dashboard--card bg--14 cursor-pointer redirect-url" data-url="<?php echo e(route('admin.order.list',['failed'])); ?>">
            <h4 class="title"><?php echo e($data['refund_requested']); ?></h4>
            <span class="subtitle"><?php echo e(translate('messages.payment_failed_orders')); ?></span>
            <img class="resturant-icon" src="<?php echo e(dynamicAsset('assets/admin/img/dashboard/4.png')); ?>" alt="dashboard">
        </div>
    </div>
</div>


<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/partials/_order-statics.blade.php ENDPATH**/ ?>