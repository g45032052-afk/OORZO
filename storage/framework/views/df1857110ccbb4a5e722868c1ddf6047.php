<?php ($params = session('dash_params')); ?>
<?php if($params['zone_id'] != 'all'): ?>
    <?php ($zone_name = \App\Models\Zone::where('id', $params['zone_id'])->first()->name); ?>
<?php else: ?>
<?php ($zone_name=translate('All')); ?>
<?php endif; ?>



<div class="card-body">
     <div class="row justify-content-end">
        <div class="col-sm-6 col-md-4">
            <div class="ml-auto mb-2 pb-xl-5">
            <select class="custom-select user-overview-stats-update" name="user_overview">
                <option
                    value="overall" <?php echo e($params['user_overview'] == 'overall'?'selected':''); ?>>
                    <?php echo e(translate('messages.Overall')); ?>

                </option>

                <option
                    value="this_year" <?php echo e($params['user_overview'] == 'this_year'?'selected':''); ?>>
                    <?php echo e(translate('This_Year')); ?>

                </option>
                <option
                    value="this_month" <?php echo e($params['user_overview'] == 'this_month'?'selected':''); ?>>
                    <?php echo e(translate('This_Month')); ?>

                </option>
                <option
                    value="this_week" <?php echo e($params['user_overview'] == 'this_week'?'selected':''); ?>>
                    <?php echo e(translate('This_Week')); ?>

                </option>

                <option
                value="today" <?php echo e($params['user_overview'] == 'today'?'selected':''); ?>>
                <?php echo e(translate('Today')); ?>

            </option>
            </select>
        </div>
        </div>
    </div>

    <?php if($data['customer'] || $data['restaurants'] || $data['delivery_man']): ?>

     <div class="position-relative" >
        <div id="user-overview-board">

            <div class="chartjs-custom mx-auto">
                <div data-id="#user-overview" data-value="<?php echo e($data['customer'].','. $data['restaurants'].','. $data['delivery_man']); ?>"
                data-labels="<?php echo e(translate('messages.Customer')); ?>, <?php echo e(translate('messages.Restaurant')); ?>,<?php echo e(translate('messages.Delivery_man')); ?>" id="user-overview"></div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="d-flex justify-content-center align-items-center h-100 min-h-200">
        <div class="d-flex flex-column gap-3 justify-content-center align-items-center">
            <img src="<?php echo e(dynamicAsset('assets/admin/img/dashboard/user_stat.svg')); ?>" alt="">
            <p><?php echo e(translate('No user available in this zone')); ?></p>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/partials/_user-overview-chart.blade.php ENDPATH**/ ?>