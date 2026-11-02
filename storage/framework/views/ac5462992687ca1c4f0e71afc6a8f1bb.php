<?php $__env->startSection('title',translate('Happy_Hour')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title"><?php echo e(translate('messages.Happy_Hour')); ?></h1>
        </div>

        <div class="card">
            
            <?php if($happyHours->total() === 0 && ! request()->filled('search')): ?>
                <div class="d-flex flex-column align-items-center justify-content-center text-center px-3"
                     style="min-height: 440px">
                    <img src="<?php echo e(dynamicAsset('assets/admin/img/empty.png')); ?>" alt="empty" style="width:64px;height:auto">
                    <h4 class="mt-4 mb-2 font-weight-bold"><?php echo e(translate('messages.No_Happy_Hours_Yet')); ?></h4>
                    <p class="opacity-75 mb-4" style="max-width: 480px">
                        <?php echo e(translate('messages.happy_hour_empty_state_description')); ?>

                    </p>
                    <a href="<?php echo e(route('admin.happy-hour.add-new')); ?>" class="btn btn--primary">
                        <i class="tio-add-circle"></i> <?php echo e(translate('messages.Add_Happy_Hours')); ?>

                    </a>
                </div>
            <?php else: ?>
                <div class="card-header border-0 py-2 search--button-wrapper">
                    <h5 class="card-title">
                        <?php echo e(translate('messages.Happy_Hour_List')); ?>

                        <span class="badge badge-soft-dark ml-2"><?php echo e($happyHours->total()); ?></span>
                    </h5>
                    <form id="search-form" action="<?php echo e(url()->current()); ?>" method="GET">
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input id="datatableSearch_" type="search" name="search" value="<?php echo e(request('search')); ?>"
                                   class="form-control" placeholder="<?php echo e(translate('messages.search_here')); ?>" aria-label="Search">
                            <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                        </div>
                    </form>
                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm"
                           href="javascript:;"
                           data-hs-unfold-options='{"target": "#happyHourExportDropdown", "type": "css-animation"}'>
                            <i class="tio-download-to mr-1"></i> <?php echo e(translate('messages.export')); ?>

                        </a>
                        <div id="happyHourExportDropdown" class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <span class="dropdown-header"><?php echo e(translate('messages.download_options')); ?></span>
                            <a target="__blank" id="export-excel" class="dropdown-item"
                               href="<?php echo e(route('admin.happy-hour.export', ['type' => 'excel', 'search' => request('search')])); ?>">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/excel.svg" alt="excel">
                                <?php echo e(translate('messages.excel')); ?>

                            </a>
                            <a target="__blank" id="export-csv" class="dropdown-item"
                               href="<?php echo e(route('admin.happy-hour.export', ['type' => 'csv', 'search' => request('search')])); ?>">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/placeholder-csv-format.svg" alt="csv">
                                <?php echo e(translate('messages.csv')); ?>

                            </a>
                        </div>
                    </div>

                    <a href="<?php echo e(route('admin.happy-hour.add-new')); ?>" class="btn btn--primary">
                        <i class="tio-add-circle"></i> <?php echo e(translate('messages.Create_Happy_hour')); ?>

                    </a>
                </div>

                <div class="table-responsive datatable-custom">
                    <table class="font-size-sm table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                        <tr>
                            <th><?php echo e(translate('messages.sl')); ?></th>
                            <th><?php echo e(translate('messages.title')); ?></th>
                            <th><?php echo e(translate('messages.Discount')); ?></th>
                            <th><?php echo e(translate('messages.Duration')); ?></th>
                            <th><?php echo e(translate('messages.Min_Order_Amount')); ?></th>
                            <th><?php echo e(translate('messages.Offer_Schedule')); ?></th>
                            <th><?php echo e(translate('messages.Zone')); ?></th>
                            <th><?php echo e(translate('messages.status')); ?></th>
                            <th class="text-center"><?php echo e(translate('messages.action')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $happyHours; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $happyHour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php ($running = $happyHour->isRunningNow()); ?>
                            <tr>
                                <td><?php echo e($key + $happyHours->firstItem()); ?></td>
                                <td>
                                    <a href="<?php echo e(route('admin.happy-hour.view',$happyHour->id)); ?>" class="d-block text-body">
                                        <?php echo e(Str::limit($happyHour->title, 25, '...')); ?>

                                    </a>
                                    <?php if($running): ?>
                                        <span class="badge badge-soft-success"><?php echo e(translate('messages.Active')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($happyHour->discount); ?>%</td>
                                <td>
                                    <?php if($happyHour->duration_type === 'custom'): ?>
                                        <span class="d-block"><?php echo e(count($happyHour->custom_days ?? [])); ?> <?php echo e(translate('messages.days_selected')); ?></span>
                                    <?php else: ?>
                                        <span class="d-block">
                                            <?php echo e($happyHour->start_time ? \App\CentralLogics\Helpers::time_format($happyHour->start_time) : ''); ?>

                                            - <?php echo e($happyHour->end_time ? \App\CentralLogics\Helpers::time_format($happyHour->end_time) : ''); ?>

                                        </span>
                                        <span class="d-block font-size-sm opacity-75">
                                            <?php if($happyHour->is_permanent): ?>
                                                <?php echo e(translate('messages.Permanent')); ?>

                                            <?php else: ?>
                                                <?php echo e($happyHour->start_date ? $happyHour->start_date->format('d M Y') : ''); ?>

                                                - <?php echo e($happyHour->end_date ? $happyHour->end_date->format('d M Y') : ''); ?>

                                            <?php endif; ?>
                                        </span>
                                        <?php if($happyHour->duration_type === 'weekly'): ?>
                                            <span class="d-block font-size-sm opacity-75">
                                                <?php echo e(implode(', ', array_map(fn($d) => substr($d, 0, 3), $happyHour->weekly_days ?? []))); ?>

                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($happyHour->min_order_amount ? \App\CentralLogics\Helpers::format_currency($happyHour->min_order_amount) : 'N/A'); ?></td>
                                <td class="text-capitalize"><?php echo e(translate('messages.'.$happyHour->duration_type)); ?></td>
                                <td><?php echo e($happyHour->zone ? $happyHour->zone->name : translate('messages.zone_deleted')); ?></td>
                                <td>
                                    
                                    <?php ($locked = $running && $happyHour->status); ?>
                                    <label class="toggle-switch toggle-switch-sm mb-0 <?php echo e($locked ? 'cursor-default' : ''); ?>"
                                           for="hhStatus<?php echo e($happyHour->id); ?>"
                                           <?php if($locked): ?> data-toggle="tooltip" data-placement="left"
                                                title="<?php echo e(translate('messages.an_ongoing_happy_hour_cannot_be_turned_off')); ?>" <?php endif; ?>>
                                        <input type="checkbox" class="toggle-switch-input <?php echo e($locked ? '' : 'status_change_alert'); ?>"
                                               <?php if(! $locked): ?>
                                               data-url="<?php echo e(route('admin.happy-hour.status',[$happyHour->id, $happyHour->status ? 0 : 1])); ?>"
                                               data-message="<?php echo e($happyHour->status
                                                    ? translate('messages.Want_to_turn_off_this_happy_hour')
                                                    : translate('messages.Want_to_turn_on_this_happy_hour')); ?>"
                                               <?php else: ?> disabled <?php endif; ?>
                                               id="hhStatus<?php echo e($happyHour->id); ?>" <?php echo e($happyHour->status ? 'checked' : ''); ?>>
                                        <span class="toggle-switch-label"><span class="toggle-switch-indicator"></span></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                           href="<?php echo e(route('admin.happy-hour.view',$happyHour->id)); ?>" title="<?php echo e(translate('messages.view')); ?>">
                                            <i class="tio-visible"></i>
                                        </a>

                                        
                                        <?php if($running): ?>
                                            <span class="btn btn-sm btn--primary btn-outline-primary action-btn disabled"
                                                  data-toggle="tooltip" title="<?php echo e(translate('messages.an_ongoing_happy_hour_cannot_be_edited')); ?>"
                                                  style="opacity:.45;cursor:not-allowed">
                                                <i class="tio-edit"></i>
                                            </span>
                                        <?php else: ?>
                                            <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                               href="<?php echo e(route('admin.happy-hour.edit',$happyHour->id)); ?>" title="<?php echo e(translate('messages.edit')); ?>">
                                                <i class="tio-edit"></i>
                                            </a>
                                        <?php endif; ?>

                                        
                                        <?php if($running): ?>
                                            <span class="btn btn-sm btn--danger btn-outline-danger action-btn disabled"
                                                  data-toggle="tooltip" title="<?php echo e(translate('messages.an_ongoing_happy_hour_cannot_be_deleted')); ?>"
                                                  style="opacity:.45;cursor:not-allowed">
                                                <i class="tio-delete-outlined"></i>
                                            </span>
                                        <?php else: ?>
                                            <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert" href="javascript:"
                                               data-id="happy-hour-<?php echo e($happyHour->id); ?>"
                                               data-message="<?php echo e(translate('messages.Want_to_delete_this_happy_hour')); ?>"
                                               title="<?php echo e(translate('messages.delete')); ?>">
                                                <i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="<?php echo e(route('admin.happy-hour.delete',$happyHour->id)); ?>" method="post" id="happy-hour-<?php echo e($happyHour->id); ?>">
                                                <?php echo csrf_field(); ?> <?php echo method_field('delete'); ?>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5"><?php echo e(translate('no_data_found')); ?></td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="page-area px-4 pb-3">
                        <div class="d-flex align-items-center justify-content-end">
                            <div><?php echo $happyHours->links(); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <?php echo $__env->make('admin-views.partials._status_change_alert', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/happy-hour/list.blade.php ENDPATH**/ ?>