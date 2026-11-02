<?php $__env->startSection('title',translate('messages.Campaign_List')); ?>

<?php $__env->startPush('css_or_js'); ?>

<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-notice"></i> <?php echo e(translate('messages.basic_campaign')); ?> <span class="badge badge-soft-dark ml-2 py-2px" id="itemCount"><?php echo e($campaigns->total()); ?></span></h1>
                </div>

                <div class="col-sm-auto">
                    <a class="btn btn--primary" href="<?php echo e(route('admin.campaign.add-new', 'basic')); ?>">
                        <i class="tio-add"></i> <?php echo e(translate('messages.add_new_campaign')); ?>

                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h5 class="card-title"></h5>
                            <form>
                                <!-- Search -->
                                <div class="input--group input-group input-group-merge input-group-flush">
                                    <input id="datatableSearch" type="search" name="search"  value="<?php echo e(request()?->search ?? null); ?>" class="form-control" placeholder="<?php echo e(translate('Ex_:_Search_by_name...')); ?>" aria-label="Search here">
                                    <button type="submit" class="btn btn--secondary secondary-cmn"><i class="tio-search"></i></button>
                                </div>
                                <!-- End Search -->
                            </form>
                            <div class="hs-unfold mr-2">
                                <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40" href="javascript:"
                                    data-hs-unfold-options='{
                                            "target": "#usersExportDropdown",
                                            "type": "css-animation"
                                        }'>
                                    <i class="tio-download-to mr-1"></i> <?php echo e(translate('messages.export')); ?>

                                </a>

                                <div id="usersExportDropdown"
                                    class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">

                                    <span class="dropdown-header"><?php echo e(translate('messages.download_options')); ?></span>
                                    <a id="export-excel" class="dropdown-item" href="
                                        <?php echo e(route('admin.campaign.basic_campaign_export', ['type' => 'excel', request()->getQueryString()])); ?>

                                        ">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/excel.svg"
                                            alt="Image Description">
                                        <?php echo e(translate('messages.excel')); ?>

                                    </a>
                                    <a id="export-csv" class="dropdown-item" href="
                                    <?php echo e(route('admin.campaign.basic_campaign_export', ['type' => 'csv', request()->getQueryString()])); ?>">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/placeholder-csv-format.svg"
                                            alt="Image Description">
                                        <?php echo e(translate('messages.csv')); ?>

                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table id="columnSearchDatatable"
                               class="font-size-sm table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                 "order": [],
                                 "orderCellsTop": true,
                                 "paging":false
                               }'>
                            <thead class="thead-light">
                            <tr>
                                <th><?php echo e(translate('messages.sl')); ?></th>
                                <th ><?php echo e(translate('messages.title')); ?></th>
                                <th ><?php echo e(translate('messages.date_duration')); ?></th>
                                <th ><?php echo e(translate('messages.time_duration')); ?></th>
                                <th><?php echo e(translate('messages.status')); ?></th>
                                <th class="text-center"><?php echo e(translate('messages.action')); ?></th>
                            </tr>
                            </thead>

                            <tbody id="set-rows">
                            <?php $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($key+$campaigns->firstItem()); ?></td>
                                    <td>
                                        <span class="d-block text-body"><a href="<?php echo e(route('admin.campaign.view',['basic',$campaign->id])); ?>"><?php echo e(Str::limit($campaign['title'],25, '...')); ?></a>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="bg-gradient-light text-dark"><?php echo e($campaign->start_date?  \App\CentralLogics\Helpers::date_format($campaign->start_date)  : 'N/A'); ?></span>
                                        <span class="bg-gradient-light text-dark">-</span>
                                        <span class="bg-gradient-light text-dark"><?php echo e($campaign->start_time?  \App\CentralLogics\Helpers::date_format($campaign->end_date) : 'N/A'); ?></span>
                                    </td>
                                    <td>
                                        <span class="bg-gradient-light text-dark"><?php echo e($campaign->start_time?  \App\CentralLogics\Helpers::time_format($campaign->start_time). ' - ' .\App\CentralLogics\Helpers::time_format($campaign->end_time): 'N/A'); ?></span>
                                    </td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox<?php echo e($campaign->id); ?>">
                                            <input type="checkbox" data-url="<?php echo e(route('admin.campaign.status',['basic',$campaign['id'],$campaign->status?0:1])); ?>" class="toggle-switch-input redirect-url" id="stocksCheckbox<?php echo e($campaign->id); ?>" <?php echo e($campaign->status?'checked':''); ?>>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                                href="<?php echo e(route('admin.campaign.edit',['basic',$campaign['id']])); ?>" title="<?php echo e(translate('messages.edit_campaign')); ?>"><i class="tio-edit"></i>
                                            </a>
                                            <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert" href="javascript:"
                                                data-id="campaign-<?php echo e($campaign['id']); ?>" data-message="<?php echo e(translate('messages.Want_to_delete_this_campaign')); ?>" title="<?php echo e(translate('messages.delete_campaign')); ?>"><i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="<?php echo e(route('admin.campaign.delete',[$campaign['id']])); ?>"
                                                        method="post" id="campaign-<?php echo e($campaign['id']); ?>">
                                                <?php echo csrf_field(); ?> <?php echo method_field('delete'); ?>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                        <?php if(count($campaigns) === 0): ?>
                        <div class="empty--data">
                            <img src="<?php echo e(dynamicAsset('assets/admin/img/empty.png')); ?>" alt="public">
                            <h5>
                                <?php echo e(translate('no_data_found')); ?>

                            </h5>
                        </div>
                        <?php endif; ?>
                        <div class="page-area px-4 pb-3">
                            <div class="d-flex align-items-center justify-content-end">
                                <div>
                                    <?php echo $campaigns->links(); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Table -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <script>
        "use strict";
        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function () {
                datatable
                    .search(this.value)
                    .draw();
            });

            $('#column2_search').on('keyup', function () {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });

            $('#column3_search').on('change', function () {
                datatable
                    .columns(3)
                    .search(this.value)
                    .draw();
            });

            $('#column4_search').on('keyup', function () {
                datatable
                    .columns(4)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/campaign/basic/list.blade.php ENDPATH**/ ?>