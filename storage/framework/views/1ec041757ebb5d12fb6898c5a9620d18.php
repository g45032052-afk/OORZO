<?php $__env->startSection('title',translate('messages.Cash_Collection')); ?>

<?php $__env->startPush('css_or_js'); ?>

<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="content container-fluid">

    <!-- Page Heading -->
    <div class="page-header">
        <h1 class="page-header-title mb-2 text-capitalize">
            <div class="card-header-icon d-inline-flex mr-2 img">
                <img src="<?php echo e(dynamicAsset('assets/admin/img/collect-cash.png')); ?>" class="w-20px" alt="public">
            </div>
            <span>
                <?php echo e(translate('Cash_Collection')); ?>

            </span>
        </h1>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <form action="<?php echo e(route('admin.account-transaction.store')); ?>" method='post' id="add_transaction">
                <?php echo csrf_field(); ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                        <label class="input-label" for="type"><?php echo e(translate('messages.type')); ?> <span class="form-label-secondary text-danger"
                            data-toggle="tooltip" data-placement="right"
                            data-original-title="<?php echo e(translate('messages.Required.')); ?>"> *
                            </span>
                        <span class="input-label-secondary"></span></label>
                            <select name="type" id="type" class="form-control h--48px">
                                <option value="deliveryman"><?php echo e(translate('messages.deliveryman')); ?></option>
                                <option value="restaurant"><?php echo e(translate('messages.restaurant')); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="restaurant"><?php echo e(translate('messages.restaurant')); ?><span class="input-label-secondary"></span></label>
                            <select id="restaurant" name="restaurant_id" data-placeholder="<?php echo e(translate('messages.select_restaurant')); ?>" data-url="<?php echo e(url('/')); ?>/admin/restaurant/get-account-data/" data-type="restaurant" class="form-control h--48px get-account-data" title="Select Restaurant" disabled>

                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="deliveryman"><?php echo e(translate('messages.deliveryman')); ?><span class="input-label-secondary"></span></label>
                            <select id="deliveryman" name="deliveryman_id" data-placeholder="<?php echo e(translate('messages.select_deliveryman')); ?>" data-url="<?php echo e(url('/')); ?>/admin/delivery-man/get-account-data/" data-type="deliveryman" class="form-control h--48px get-account-data" title="Select deliveryman">

                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="method"><?php echo e(translate('messages.method')); ?><span class="form-label-secondary text-danger"
                                data-toggle="tooltip" data-placement="right"
                                data-original-title="<?php echo e(translate('messages.Required.')); ?>"> *
                                </span>
                            </label>
                            <input class="form-control h--48px" type="text" name="method" id="method" required maxlength="191" placeholder="<?php echo e(translate('messages.Ex_:_Cash')); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="ref"><?php echo e(translate('messages.reference')); ?><span class="input-label-secondary"></span></label>
                            <input  class="form-control h--48px" type="text" name="ref" id="ref" maxlength="191" placeholder="<?php echo e(translate('messages.Ex_:_Collect_Cash')); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="amount"><?php echo e(translate('messages.amount')); ?> <span class="form-label-secondary text-danger"
                                data-toggle="tooltip" data-placement="right"
                                data-original-title="<?php echo e(translate('messages.Required.')); ?>"> *
                                </span>
                            <span class="input-label-secondary" id="account_info"></span></label>
                            <input class="form-control h--48px" type="number" min=".01" step="0.01" name="amount" id="amount" max="999999999999.99" placeholder="<?php echo e(translate('messages.Ex_:_100')); ?>">
                        </div>
                    </div>
                </div>
                <div class="btn--container justify-content-end">
                    <button type="reset" id="reset_btn" class="btn btn--reset"><?php echo e(translate('messages.reset')); ?></button>
                    <button type="submit" id="collect_cash" class="btn btn--primary"><?php echo e(translate('messages.collect_cash')); ?></button>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header py-2 border-0">
            <div class="search--button-wrapper">
                <h3 class="card-title">
                    <span><?php echo e(translate('messages.transaction_table')); ?></span>
                    <span class="badge badge-soft-secondary" id="itemCount" ><?php echo e($account_transaction->total()); ?></span>
                </h3>
                <!-- Static Search Form -->
                <form class="my-2 ml-auto mr-sm-2 mr-xl-4 ml-sm-auto flex-grow-1 flex-grow-sm-0">
                        <div class="input--group input-group input-group-merge input-group-flush">
                        <input id="datatableSearch_" type="search" name="search" class="form-control" value="<?php echo e(request()?->search ?? null); ?>"  placeholder="<?php echo e(translate('messages.Search_by_Reference')); ?>" aria-label="Search" required>
                        <button type="submit" class="btn btn--secondary secondary-cmn"><i class="tio-search"></i></button>
                    </div>
                    <!-- End Search -->
                </form>
                <!-- Static Search Form -->
                <!-- Static Export Button -->
                <div class="hs-unfold ml-3">
                    <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm" href="javascript:;"
                        data-hs-unfold-options='{
                            "target": "#usersExportDropdown",
                            "type": "css-animation"
                        }'>
                        <i class="tio-download-to mr-1"></i> <?php echo e(translate('messages.export')); ?>

                    </a>
                    <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                        <span class="dropdown-header"><?php echo e(translate('messages.download_options')); ?></span>
                        <a id="export-excel" class="dropdown-item" href="<?php echo e(route('admin.export-account-transaction', ['type'=>'excel',request()->getQueryString()])); ?>">
                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/excel.svg"
                                    alt="Image Description">
                            <?php echo e(translate('messages.excel')); ?>

                        </a>
                        <a id="export-csv" class="dropdown-item" href="<?php echo e(route('admin.export-account-transaction', ['type'=>'csv',request()->getQueryString()])); ?>">
                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                            <?php echo e(translate('messages.csv')); ?>

                        </a>
                    </div>
                </div>
                <!-- Static Export Button -->
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="datatable"
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th><?php echo e(translate('messages.sl')); ?></th>
                            <th><?php echo e(translate('messages.Collected_From')); ?></th>
                            <th><?php echo e(translate('messages.User_Type')); ?></th>
                            <th><?php echo e(translate('messages.Collected_At')); ?></th>
                            <th><?php echo e(translate('messages.Collected_Amount')); ?></th>
                            <th><?php echo e(translate('messages.Paymen_Method')); ?></th>
                            <th><?php echo e(translate('messages.Reference')); ?></th>
                            <th class="text-center w-120px"><?php echo e(translate('messages.action')); ?></th>
                        </tr>
                    </thead>

                    <tbody id="set-rows">
                        <?php $__currentLoopData = $account_transaction; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$at): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php ($data= null); ?>
                        <tr>

                            <td scope="row"><?php echo e($k+$account_transaction->firstItem()); ?></td>
                            <td>
                                <?php if($at->restaurant): ?>
                                <?php ($data=$at->restaurant); ?>
                                <a href="<?php echo e(route('admin.restaurant.view',[$data->id])); ?>">
                                    <?php echo e(Str::limit($data->name, 20, '...')); ?>

                                    <?php echo $__env->make('partials._verified_restaurant_badge', ['restaurant' => $data], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </a>
                                <?php elseif($at->deliveryman): ?>
                                <?php ($data=$at->deliveryman); ?>
                                <a href="<?php echo e(route('admin.delivery-man.preview',[$data->id])); ?>"><?php echo e($data->f_name); ?> <?php echo e($data->l_name); ?></a>
                                <?php else: ?>
                                <?php ($data=null); ?>
                                    <?php echo e(translate('messages.not_found')); ?>

                                <?php endif; ?>
                            </td>
                            <td><label class=""><?php echo e(translate($at['from_type'])); ?></label></td>
                            <td>
                                <?php echo e(\App\CentralLogics\Helpers::time_date_format($at->created_at)); ?>

                            </td>
                            <td><?php echo e(\App\CentralLogics\Helpers::format_currency($at['amount'])); ?></td>
                            <td><?php echo e(translate($at['method'])); ?></td>
                            <td class="text-capitalize"><?php echo e($at['ref'] ? translate($at['ref']) : translate('messages.N/A')); ?> </td>
                            <td>
                                <div class="btn--container justify-content-center"> <a href="#"
                                    data-payment_method="<?php echo e($at->method); ?>"
                                    data-ref="<?php echo e(translate($at['ref'])); ?>"
                                    data-amount="<?php echo e(\App\CentralLogics\Helpers::format_currency($at['amount'])); ?>"
                                    data-date="<?php echo e(\App\CentralLogics\Helpers::time_date_format($at->created_at)); ?>"
                                    data-type="<?php echo e($at->from_type == 'deliveryman' ?  translate('DeliveryMan_Info') : translate('Restaurant_Info')); ?>"
                                    data-phone="<?php echo e($data?->phone); ?>"

                                    data-address="<?php echo e($at->from_type == 'restaurant' ?  $data?->address : $data?->last_location?->location ?? translate('address_not_found')); ?>"
                                    data-latitude="<?php echo e($at->from_type == 'restaurant' ?   $data?->latitude : $data?->last_location?->latitude ?? 0); ?>"
                                    data-longitude="<?php echo e($at->from_type == 'restaurant' ?   $data?->longitude : $data?->last_location?->longitude ?? 0); ?>"
                                    data-name="<?php echo e($at->from_type == 'restaurant' ?   $data?->name : $data?->f_name.' '.$data?->l_name); ?>"

                                    class="btn action-btn btn--warning btn-outline-warning withdraw-info-show" ><i class="tio-visible"></i>
                                    </a>
                                </div>


                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <?php if(count($account_transaction) === 0): ?>
                <div class="empty--data">
                    <img src="<?php echo e(dynamicAsset('assets/admin/img/empty.png')); ?>" alt="public">
                    <h5>
                        <?php echo e(translate('messages.no_data_found')); ?>

                    </h5>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-footer border-0 pt-0">
            <div class="page-area px-4 pb-3">
                <div class="d-flex align-items-center justify-content-end">

                    <div>
                        <?php echo e($account_transaction->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="sidebar-wrap">
    <div class="withdraw-info-sidebar-overlay"></div>
    <div class="withdraw-info-sidebar">
        <div class="d-flex pb-3">
            <span class="circle bg-light withdraw-info-hide cursor-pointer">
                <i class="tio-clear"></i>
            </span>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 " id="type"></h6>
            </div>
            <div class="card-body">
                <div class="key-val-list d-flex flex-column gap-2" style="--min-width: 60px">
                    <div class="key-val-list-item d-flex gap-3">
                        <span><?php echo e(translate('name')); ?>:</span>
                        <span id="name"></span>
                    </div>
                    <div class="key-val-list-item d-flex gap-3">
                        <span><?php echo e(translate('phone')); ?>:</span>
                        <a href="tel:" id="phone" class="text-dark"></a>
                    </div>
                    <div class="key-val-list-item d-flex gap-3">
                        <span><?php echo e(translate('address')); ?>:</span>
                        <a id="address" target="_blank"></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-2">
            <div class="card-header">
                <h6 class="mb-0 " id=""><?php echo e(translate('Transaction_Info')); ?></h6>
            </div>
            <div class="card-body">

                <div class="key-val-list d-flex flex-column gap-2" style="--min-width: 60px">

                    <div class="d-flex gap-2 align-items-center ">
                        <span><?php echo e(translate('method')); ?>:</span>
                        <span id="payment_method" class="text-dark font-semibold text-capitalize"></span>
                    </div>
                    <div class="d-flex gap-2 align-items-center ">
                        <span><?php echo e(translate('amount')); ?>:</span>
                        <span class="text-primary font-bold" id="amount"> </span>
                    </div>
                    <div class="d-flex gap-2 align-items-center ">
                        <span><?php echo e(translate('request_time')); ?>:</span>
                        <span id="date"></span>
                    </div>
                    <div class="d-flex gap-2 align-items-center  ">
                        <span><?php echo e(translate('reference')); ?>:</span>
                        <span id="ref" class="text-capitalize fs-12"></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
<script src="<?php echo e(dynamicAsset('assets/admin')); ?>/js/view-pages/account-index.js"></script>
<script>
    "use strict";
    $('.withdraw-info-hide, .withdraw-info-sidebar-overlay').on('click', function () {
        $('.withdraw-info-sidebar, .withdraw-info-sidebar-overlay').removeClass('show');
    });
    $('.withdraw-info-show').on('click', function () {
        let data = $(this).data();
        // console.log(data)
            $('.sidebar-wrap #payment_method').text(data.payment_method);
            $('.sidebar-wrap #amount').text(data.amount);
            $('.sidebar-wrap #type').text(data.type);
            $('.sidebar-wrap #date').text(data.date);
            $('.sidebar-wrap #ref').text(data.ref);
            $('.sidebar-wrap #name') .text(data.name);
            $('.sidebar-wrap #phone').text(data.phone).attr('href', 'tel:' + data.phone);
            $('.sidebar-wrap #address').text(data.address).attr('href', "https://www.google.com/maps/search/?api=1&query=" + data.latitude + "," + data.longitude);
        $('.withdraw-info-sidebar, .withdraw-info-sidebar-overlay').addClass('show');
    })

    $('#restaurant').select2({
        ajax: {
            url: '<?php echo e(url('/')); ?>/admin/restaurant/get-restaurants',
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data) {
                return {
                results: data
                };
            },
            __port: function (params, success, failure) {
                let $request = $.ajax(params);

                $request.then(success);
                $request.fail(failure);

                return $request;
            }
        }
    });

    $('#deliveryman').select2({
        ajax: {
            url: '<?php echo e(url('/')); ?>/admin/delivery-man/get-deliverymen',
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data) {
                return {
                results: data
                };
            },
            __port: function (params, success, failure) {
                let $request = $.ajax(params);

                $request.then(success);
                $request.fail(failure);

                return $request;
            }
        }
    });

    $('.get-account-data').on('change', function() {
        let route = $(this).data('url');
        let type = $(this).data('type');
        let data_id = $(this).val();
        getAccountData(route, data_id, type);
    })

    function getAccountData(route, data_id, type)
    {
        $.get({
                url: route+data_id,
                dataType: 'json',
                success: function (data) {
                    $('#account_info').html('(<?php echo e(translate('messages.cash_in_hand')); ?>: '+data.cash_in_hand+' <?php echo e(translate('messages.earning_balance')); ?>: '+data.earning_balance+')');
                },
            });
    }

    $('#add_transaction').on('submit', function (e) {
        e.preventDefault();
        let formData = new FormData(this);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.post({
            url: '<?php echo e(route('admin.account-transaction.store')); ?>',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                if (data.errors) {
                    for (let i = 0; i < data.errors.length; i++) {
                        toastr.error(data.errors[i].message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                } else {
                    toastr.success('<?php echo e(translate('messages.transaction_saved')); ?>', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    setTimeout(function () {
                        location.href = '<?php echo e(route('admin.account-transaction.index')); ?>';
                    }, 2000);
                }
            }
        });
    });



$('form').on('submit', function() {
    $('#collect_cash').prop('disabled', true);
});

</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/account/index.blade.php ENDPATH**/ ?>