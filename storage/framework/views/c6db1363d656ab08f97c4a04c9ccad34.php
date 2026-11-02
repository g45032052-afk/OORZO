
<div class="card-body">
    <div class="row mb-3">
        <div class="col-12">
            <?php ($params=session('dash_params')); ?>
            <?php if($params['zone_id']!='all'): ?>
                <?php ($zone_name=\App\Models\Zone::where('id',$params['zone_id'])->first()->name); ?>
            <?php else: ?>
            <?php ($zone_name=translate('All')); ?>
        <?php endif; ?>
            <div class="d-flex flex-wrap justify-content-center align-items-center">
                <span class="h5 m-0 mr-3 fz--11 d-flex align-items-center mb-2 mb-md-0">
                    <span class="legend-indicator bg-0661CB"></span>
                    <span><?php echo e(translate('messages.admin_commission')); ?></span> <span>:</span> <span><?php echo e(\App\CentralLogics\Helpers::format_currency(array_sum($commission))); ?></span>
                </span>
                <span class="h5 m-0 fz--11 d-flex align-items-center mb-2 mb-md-0">
                    <span class="legend-indicator bg-sell-green"></span>
                    <span><?php echo e(translate('messages.total_sell')); ?></span> <span>:</span> <span><?php echo e(\App\CentralLogics\Helpers::format_currency(array_sum($total_sell))); ?></span>
                </span> &nbsp;&nbsp;
        <?php if(\App\CentralLogics\Helpers::subscription_check()): ?>
        <span class="h5 m-0 fz--11 d-flex align-items-center mb-2 mb-md-0">
            <span class="legend-indicator bg-subs-orange"></span>
            <span><?php echo e(translate('Subscription')); ?></span> <span>:</span> <span><?php echo e(\App\CentralLogics\Helpers::format_currency(array_sum($total_subs))); ?></span>
        </span>
        <?php endif; ?>

        </div>
          </div>
          <div class="col-12">
              <div class="text-right mt--xl--10"><span class="badge badge-soft--info"><?php echo e(translate('messages.zone')); ?> : <?php echo e($zone_name); ?></span>
              </div>
          </div>
    </div>

    <div class="d-flex align-items-center">
      <div class="chartjs-custom w-75 flex-grow-1">

          <div  data-commission="<?php echo e($commission[1]); ?>,<?php echo e($commission[2]); ?>,<?php echo e($commission[3]); ?>,<?php echo e($commission[4]); ?>,<?php echo e($commission[5]); ?>,<?php echo e($commission[6]); ?>,<?php echo e($commission[7]); ?>,<?php echo e($commission[8]); ?>,<?php echo e($commission[9]); ?>,<?php echo e($commission[10]); ?>,<?php echo e($commission[11]); ?>,<?php echo e($commission[12]); ?>"

            data-subscription="<?php echo e($total_subs[1]); ?>,<?php echo e($total_subs[2]); ?>,<?php echo e($total_subs[3]); ?>,<?php echo e($total_subs[4]); ?>,<?php echo e($total_subs[5]); ?>,<?php echo e($total_subs[6]); ?>,<?php echo e($total_subs[7]); ?>,<?php echo e($total_subs[8]); ?>,<?php echo e($total_subs[9]); ?>,<?php echo e($total_subs[10]); ?>,<?php echo e($total_subs[11]); ?>,<?php echo e($total_subs[12]); ?>"

            data-total_sell="<?php echo e($total_sell[1]); ?>,<?php echo e($total_sell[2]); ?>,<?php echo e($total_sell[3]); ?>,<?php echo e($total_sell[4]); ?>,<?php echo e($total_sell[5]); ?>,<?php echo e($total_sell[6]); ?>,<?php echo e($total_sell[7]); ?>,<?php echo e($total_sell[8]); ?>,<?php echo e($total_sell[9]); ?>,<?php echo e($total_sell[10]); ?>,<?php echo e($total_sell[11]); ?>,<?php echo e($total_sell[12]); ?>"
                    id="updatingData"  ></div>

      </div>
    </div>

</div>




<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/partials/_monthly-earning-graph.blade.php ENDPATH**/ ?>