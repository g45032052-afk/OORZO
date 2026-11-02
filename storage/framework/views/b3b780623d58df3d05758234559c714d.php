<?php $__env->startSection('title',translate('BOGO_Offer')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title"><?php echo e(translate('messages.BOGO_Offer')); ?></h1>
        </div>

        <div class="card">
            
            <?php if($offers->total() === 0 && ! request()->filled('search')): ?>
                <div class="d-flex flex-column align-items-center justify-content-center text-center px-3"
                     style="min-height: 440px">
                    <img src="<?php echo e(dynamicAsset('assets/admin/img/empty.png')); ?>" alt="empty" style="width:64px;height:auto">
                    <h4 class="mt-4 mb-2 font-weight-bold"><?php echo e(translate('messages.No_BOGO_Offers_Yet')); ?></h4>
                    <p class="opacity-75 mb-4" style="max-width: 480px">
                        <?php echo e(translate('messages.bogo_empty_state_description')); ?>

                    </p>
                    <a href="<?php echo e(route('admin.bogo-offer.add-new')); ?>" class="btn btn--primary">
                        <i class="tio-add-circle"></i> <?php echo e(translate('messages.Add_BOGO_Offer')); ?>

                    </a>
                </div>
            <?php else: ?>
                <div class="card-header border-0 py-2 search--button-wrapper">
                    <h5 class="card-title">
                        <?php echo e(translate('messages.BOGO_Offer_List')); ?>

                        <span class="badge badge-soft-dark ml-2"><?php echo e($offers->total()); ?></span>
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
                           data-hs-unfold-options='{"target": "#bogoExportDropdown", "type": "css-animation"}'>
                            <i class="tio-download-to mr-1"></i> <?php echo e(translate('messages.export')); ?>

                        </a>
                        <div id="bogoExportDropdown" class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <span class="dropdown-header"><?php echo e(translate('messages.download_options')); ?></span>
                            <a target="__blank" id="export-excel" class="dropdown-item"
                               href="<?php echo e(route('admin.bogo-offer.export', ['type' => 'excel', 'search' => request('search')])); ?>">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/excel.svg" alt="excel">
                                <?php echo e(translate('messages.excel')); ?>

                            </a>
                            <a target="__blank" id="export-csv" class="dropdown-item"
                               href="<?php echo e(route('admin.bogo-offer.export', ['type' => 'csv', 'search' => request('search')])); ?>">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/placeholder-csv-format.svg" alt="csv">
                                <?php echo e(translate('messages.csv')); ?>

                            </a>
                        </div>
                    </div>

                    <a href="<?php echo e(route('admin.bogo-offer.add-new')); ?>" class="btn btn--primary">
                        <i class="tio-add-circle"></i> <?php echo e(translate('messages.Add_BOGO_Offer')); ?>

                    </a>
                </div>

                <div class="table-responsive datatable-custom">
                    <table class="font-size-sm table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                        <tr>
                            <th><?php echo e(translate('messages.sl')); ?></th>
                            <th><?php echo e(translate('messages.Offer_Name')); ?></th>
                            <th><?php echo e(translate('messages.Food_Qty')); ?></th>
                            <th><?php echo e(translate('messages.Duration')); ?></th>
                            <th><?php echo e(translate('messages.Restaurant')); ?></th>
                            <th><?php echo e(translate('messages.status')); ?></th>
                            <th class="text-center"><?php echo e(translate('messages.action')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $offers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $offer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($key + $offers->firstItem()); ?></td>
                                <td>
                                    <a href="<?php echo e(route('admin.bogo-offer.view',$offer->id)); ?>" class="d-block text-body">
                                        <?php echo e(Str::limit($offer->title, 25, '...')); ?>

                                    </a>
                                </td>
                                <td>
                                    <span class="d-block"><?php echo e(translate('messages.Buy_Food_Qty')); ?> : <strong><?php echo e($offer->buy_qty); ?></strong></span>
                                    <span class="d-block"><?php echo e(translate('messages.Get_Food_Qty')); ?> : <strong><?php echo e($offer->get_qty); ?></strong></span>
                                </td>
                                <td>
                                    <span class="d-block"><?php echo e(translate('messages.Start_Date')); ?> &nbsp;:&nbsp; <?php echo e($offer->start_date ? $offer->start_date->format('d M y, h:i a') : 'N/A'); ?></span>
                                    <span class="d-block"><?php echo e(translate('messages.End_Date')); ?> &nbsp;&nbsp;&nbsp;:&nbsp; <?php echo e($offer->end_date ? $offer->end_date->format('d M y, h:i a') : 'N/A'); ?></span>
                                </td>
                                <td>
                                    <span data-toggle="tooltip" data-placement="top"
                                          title="<?php echo e($offer->approved_count); ?> <?php echo e(translate('messages.approved')); ?>, <?php echo e($offer->enrollments_count - $offer->approved_count); ?> <?php echo e(translate('messages.pending')); ?>">
                                        <?php echo e($offer->enrollments_count); ?>

                                    </span>
                                </td>
                                <td>
                                    <label class="toggle-switch toggle-switch-sm" for="bogoStatus<?php echo e($offer->id); ?>">
                                        <input type="checkbox" class="toggle-switch-input status_change_alert"
                                               data-url="<?php echo e(route('admin.bogo-offer.status',[$offer->id, $offer->status ? 0 : 1])); ?>"
                                               data-message="<?php echo e($offer->status
                                                    ? translate('messages.Want_to_turn_off_this_BOGO_offer')
                                                    : translate('messages.Want_to_turn_on_this_BOGO_offer')); ?>"
                                               id="bogoStatus<?php echo e($offer->id); ?>" <?php echo e($offer->status ? 'checked' : ''); ?>>
                                        <span class="toggle-switch-label"><span class="toggle-switch-indicator"></span></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                           href="<?php echo e(route('admin.bogo-offer.view',$offer->id)); ?>" title="<?php echo e(translate('messages.view')); ?>">
                                            <i class="tio-visible"></i>
                                        </a>
                                        <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                           href="<?php echo e(route('admin.bogo-offer.edit',$offer->id)); ?>" title="<?php echo e(translate('messages.edit')); ?>">
                                            <i class="tio-edit"></i>
                                        </a>
                                        
                                        <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert" href="javascript:"
                                           data-id="bogo-offer-<?php echo e($offer->id); ?>"
                                           data-message="<?php echo e($offer->enrollments_count > 0
                                                ? $offer->enrollments_count.' '.translate('messages.restaurants_have_joined_this_offer_Deleting_it_removes_the_offer_from_all_of_them')
                                                    .' '.translate('messages.Any_items_customers_added_to_their_cart_from_this_offer_will_be_removed_too')
                                                    .' '.translate('messages.Want_to_delete_this_BOGO_offer')
                                                : translate('messages.Want_to_delete_this_BOGO_offer')); ?>"
                                           title="<?php echo e(translate('messages.delete')); ?>">
                                            <i class="tio-delete-outlined"></i>
                                        </a>
                                        <form action="<?php echo e(route('admin.bogo-offer.delete',$offer->id)); ?>" method="post" id="bogo-offer-<?php echo e($offer->id); ?>">
                                            <?php echo csrf_field(); ?> <?php echo method_field('delete'); ?>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5"><?php echo e(translate('no_data_found')); ?></td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>

                    <div class="page-area px-4 pb-3">
                        <div class="d-flex align-items-center justify-content-end">
                            <div><?php echo $offers->links(); ?></div>
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

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/bogo-offer/list.blade.php ENDPATH**/ ?>