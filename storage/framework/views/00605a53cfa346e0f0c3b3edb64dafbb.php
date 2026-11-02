<?php $__env->startSection('title',translate('messages.notification')); ?>


<?php $__env->startSection('content'); ?>
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img width="20" src="<?php echo e(dynamicAsset('assets/admin/img/bell.png')); ?>" alt="public">
                        </div>
                        <span>
                            <?php echo e(translate('messages.notification')); ?>

                        </span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="card mb-3">
            <div class="card-body">
                <form action="<?php echo e(route('admin.notification.store')); ?>" method="post" enctype="multipart/form-data" id="notification">
                    <?php echo csrf_field(); ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1"><?php echo e(translate('messages.title')); ?>

                                    <span class="text-danger">*</span>
                                </label>
                                <input id="notification_title" type="text" name="notification_title" class="form-control" placeholder="<?php echo e(translate('Ex:_Notification_Title')); ?>" required maxlength="191">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1"><?php echo e(translate('messages.zone')); ?>   <span class="text-danger">*</span></label>
                                <select id="zone" name="zone" class="form-control js-select2-custom" >
                                    <option value="all"><?php echo e(translate('messages.all')); ?></option>
                                    <?php $__currentLoopData = \App\Models\Zone::orderBy('name')->get(['id','name']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $z): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($z['id']); ?>"><?php echo e($z['name']); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="input-label" for="tergat"><?php echo e(translate('messages.send_to')); ?>   <span class="text-danger">*</span></label>

                                <select name="tergat" class="form-control" id="tergat" data-placeholder="<?php echo e(translate('messages.Ex:_contact@company.com')); ?> " required>
                                    <option value="customer"><?php echo e(translate('messages.customer')); ?></option>
                                    <option value="deliveryman"><?php echo e(translate('messages.deliveryman')); ?></option>
                                    <option value="restaurant"><?php echo e(translate('messages.restaurant')); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            

                            <div class="p-xxl-20 p-12 global-bg-box rounded flex-grow-1">
                                                <div class="pb-md-5">
                                                    <div class="mb-4 text-start">
                                                        <p class="mb-1">
                                                            <?php echo e(translate('notification_Banner')); ?>

                                                        </p>
                                                        
                                                    </div>
                                                    <div class="text-center">
                                                        <?php echo $__env->make('admin-views.partials._image-uploader', [
                                                            'id' => 'image-input',
                                                            'name' => 'image',
                                                            'ratio' => '3:1',
                                                            'isRequired' => false,
                                                            'existingImage' => null,
                                                            'imageExtension' => IMAGE_EXTENSION,
                                                            'imageFormat' => IMAGE_FORMAT,
                                                            'maxSize' => MAX_FILE_SIZE,
                                                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                    </div>
                                                </div>
                                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1"><?php echo e(translate('messages.description')); ?>   <span class="text-danger">*</span></label>
                                <textarea id="description"  name="description" class="form-control h--md-200px" placeholder="<?php echo e(translate('Ex:_Notification_Descriptions')); ?>" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mb-0">
                        <button type="button" id="reset_btn" class="btn btn--reset"><?php echo e(translate('messages.reset')); ?></button>
                        <button type="submit" id="submit" class="btn btn--primary"><?php echo e(translate('messages.send_notification')); ?></button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Start Table -->
        <div class="card">
            <div class="card-header py-2 border-0">
                    <div class="search--button-wrapper">
                    <h3 class="card-title"><?php echo e(translate('notification_list')); ?>

                        <span class="badge badge-soft-dark ml-2"><?php echo e($notifications->total()); ?></span>
                    </h3>
                    <form>
                    <!-- Search -->
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input type="search"  value="<?php echo e(request()?->search ?? null); ?>" name="search" id="column1_search" class="form-control"
                                placeholder="<?php echo e(translate('Search_by_title')); ?>">
                            <button type="submit" class="btn btn--secondary">
                                <i class="tio-search"></i>
                            </button>
                        </div>
                    <!-- End Search -->
                    </form>

                <div class="hs-unfold ml-3">
                    <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm" href="javascript:"
                        data-hs-unfold-options='{
                            "target": "#usersExportDropdown",
                            "type": "css-animation"
                        }'>
                        <i class="tio-download-to mr-1"></i> <?php echo e(translate('messages.export')); ?>

                    </a>

                    <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                        <span class="dropdown-header"><?php echo e(translate('messages.download_options')); ?></span>
                        <a  id="export-excel" class="dropdown-item" href="<?php echo e(route('admin.notification.export', ['type'=>'excel', request()->getQueryString()])); ?>">
                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/components/excel.svg"
                                    alt="Image Description">
                            <?php echo e(translate('messages.excel')); ?>

                        </a>
                        <a  id="export-csv" class="dropdown-item" href="<?php echo e(route('admin.notification.export', ['type'=>'csv', request()->getQueryString()])); ?>">
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
                       class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                       data-hs-datatables-options='{
                         "order": [],
                         "orderCellsTop": true,
                         "paging": false
                       }'>
                    <thead class="thead-light">
                        <tr>
                            <th><?php echo e(translate('sl')); ?></th>
                            <th class="w-20p"><?php echo e(translate('messages.title')); ?></th>
                            <th><?php echo e(translate('messages.description')); ?></th>
                            <th><?php echo e(translate('messages.image')); ?></th>
                            <th class="w-08p"><?php echo e(translate('messages.zone')); ?></th>
                            <th><?php echo e(translate('messages.tergat')); ?></th>
                            <th class="w-08p"><?php echo e(translate('messages.status')); ?></th>
                            <th class="text-center w-12p"><?php echo e(translate('messages.action')); ?></th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($key+$notifications->firstItem()); ?></td>
                            <td>
                            <span class="d-block font-size-sm text-body">
                                <?php echo e(substr($notification['title'],0,25)); ?> <?php echo e(strlen($notification['title'])>25?'...':''); ?>

                            </span>
                            </td>
                            <td>
                                <?php echo e(substr($notification['description'],0,25)); ?> <?php echo e(strlen($notification['description'])>25?'...':''); ?>

                            </td>
                            <td>
                                <img class="initial-31 onerror-image"
                                     src="<?php echo e($notification['image_full_url']); ?>"
                                     data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/900x400/img1.jpg')); ?>">
                            </td>
                            <td>
                                <?php echo e($notification->zone_id==null?translate('messages.all'):($notification->zone?$notification->zone->name:translate('messages.zone_deleted'))); ?>

                            </td>
                            <?php if($notification->tergat == 'customer'): ?>
                            <td class="text-capitalize">
                                <?php echo e(translate('messages.customer')); ?>

                            </td>
                            <?php elseif($notification->tergat=='deliveryman'): ?>
                            <td class="text-capitalize">
                                <?php echo e(translate('messages.delivery_man')); ?>

                            </td>
                            <?php elseif($notification->tergat=='restaurant'): ?>
                            <td class="text-capitalize">
                                <?php echo e(translate('messages.restaurant')); ?>

                            </td>
                            <?php endif; ?>
                            <td>
                                <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox<?php echo e($notification->id); ?>">
                                    <input type="checkbox" data-url="<?php echo e(route('admin.notification.status',[$notification['id'],$notification->status?0:1])); ?>" class="toggle-switch-input redirect-url" id="stocksCheckbox<?php echo e($notification->id); ?>" <?php echo e($notification->status?'checked':''); ?>>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </td>
                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="btn btn--primary btn-outline-primary action-btn"
                                        href="<?php echo e(route('admin.notification.edit',[$notification['id']])); ?>" title="<?php echo e(translate('messages.edit_notification')); ?>"><i class="tio-edit"></i>
                                    </a>
                                    <a class="btn btn--danger btn-outline-danger action-btn form-alert" href="javascript:"
                                        data-id="notification-<?php echo e($notification['id']); ?>" data-message="<?php echo e(translate('Want_to_delete_this_notification?')); ?>" title="<?php echo e(translate('messages.delete_notification')); ?>"><i class="tio-delete-outlined"></i>
                                    </a>
                                </div>
                                <form action="<?php echo e(route('admin.notification.delete',[$notification['id']])); ?>" method="post" id="notification-<?php echo e($notification['id']); ?>">
                                            <?php echo csrf_field(); ?> <?php echo method_field('delete'); ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <?php if(count($notifications) === 0): ?>
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
                            <?php echo $notifications->links(); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Table -->
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <script>
        "use strict";
        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });

        $("#customFileEg1").change(function () {
            readURL(this);
        });

        $('#notification').on('submit', function (e) {

            e.preventDefault();
            let formData = new FormData(this);

            Swal.fire({
                title: '<?php echo e(translate('messages.Are_you_sure?')); ?>',
                text: '<?php echo e(translate('You_want_to_sent_notification?')); ?>',
                type: 'info',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: 'primary',
                cancelButtonText: '<?php echo e(translate('messages.no')); ?>',
                confirmButtonText: '<?php echo e(translate('messages.send')); ?>',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.post({
                        url: '<?php echo e(route('admin.notification.store')); ?>',
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
                                toastr.success('<?php echo e(translate('Notification_sent_successfully!')); ?>', {
                                    CloseButton: true,
                                    ProgressBar: true
                                });
                                setTimeout(function () {
                                    location.href = '<?php echo e(route('admin.notification.add-new')); ?>';
                                }, 2000);
                            }
                        }
                    });
                }
            })
        })

        $('#reset_btn').on('click', function () {
            $('#notification_title').val(null);
            $('#zone').val('all').trigger('change');
            $('#tergat').val('customer').trigger('change');
            $('#description').val(null);
            $('#viewer').attr('src', '<?php echo e(dynamicAsset('assets/admin/img/900x400/img1.png')); ?>');
            $('#customFileEg1').val(null);

            $('#image-input').val(null);
            $('.preview-image').attr('src', '#').hide();
            $('.upload-icon, .upload-text').show();
            $('.delete_image').hide();
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/notification/index.blade.php ENDPATH**/ ?>