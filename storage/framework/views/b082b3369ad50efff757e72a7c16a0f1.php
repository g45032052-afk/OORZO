<?php
    // Group schedules by day
    $schedulesByDay = $restaurant->schedules
        ->groupBy('day')
        ->map(fn ($items) =>
            $items->map(fn ($s) => [
                'id' => $s->id,
                'start_time' => $s->opening_time,
                'end_time' => $s->closing_time,
            ])->toArray()
        )
        ->toArray();

    $deleteRoute = Auth::guard('admin')->check()
        ? 'admin.restaurant.remove-schedule'
        : 'vendor.business-settings.remove-schedule';

    $days = [
        1 => 'monday',
        2 => 'tuesday',
        3 => 'wednesday',
        4 => 'Thursday',
        5 => 'friday',
        6 => 'saturday',
        0 => 'sunday',
    ];
?>

<?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayId => $dayKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="schedule-item p-3 border-bottom" data-day="<?php echo e($dayId); ?>">
        <span class="btn"><?php echo e(translate("messages.$dayKey")); ?></span>

        <div class="schedult-date-content border-0 p-0">
            <?php $__empty_1 = true; $__currentLoopData = $schedulesByDay[$dayId] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <span class="d-inline-flex align-items-center position-relative my-2">
                    <span class="start--time">
                        <span class="info text-nowrap">
                            <?php echo e(date(config('timeformat'), strtotime($schedule['start_time']))); ?>

                            -
                            <?php echo e(date(config('timeformat'), strtotime($schedule['end_time']))); ?>

                        </span>
                    </span>

                    <span
                        class="dismiss--date dismiss--date-absolute delete-schedule"
                        data-url="<?php echo e(route($deleteRoute, [
                            'restaurant_schedule' => $schedule['id'],
                            'id' => $restaurant->id
                        ])); ?>"
                    >
                        <i class="tio-clear-circle-outlined"></i>
                    </span>
                </span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <span class="btn btn-sm font-semibold text-danger bg-danger m-1" style="--bs-bg-opacity:.1;">
                    <?php echo e(translate('messages.Offday')); ?>

                </span>
            <?php endif; ?>

            <span
                class="btn add--primary mt-0 ml-2 offcanvas-trigger"
                style="border-radius:5px!important;"
                data-toggle="offcanvas"
                data-target="#offcanvasAddSchedule"
                data-dayid="<?php echo e($dayId); ?>"
                data-day="<?php echo e(translate("messages.$dayKey")); ?>"
            >
                <i class="tio-add"></i>
            </span>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/vendor-views/business-settings/partials/_schedule.blade.php ENDPATH**/ ?>