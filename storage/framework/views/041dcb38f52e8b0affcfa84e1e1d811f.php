<?php
    $statusClasses = [
        'live' => 'text-success bg-success bg-opacity-10',
        'upcoming' => 'text-info bg-info bg-opacity-10',
        'expired' => 'text-danger bg-danger bg-opacity-10',
        'deactivated' => 'text-warning bg-warning bg-opacity-10',
    ];
    $showRestaurantLink = $showRestaurantLink ?? true;
    $showStoreColumn = $showStoreColumn ?? true;
    $restaurantViewRoute = $restaurantViewRoute ?? 'admin.restaurant.view';
    $reelStatusRoute = $reelStatusRoute ?? 'admin.reels.status';
    $reelEditRoute = $reelEditRoute ?? 'admin.reels.edit';
    $reelDestroyRoute = $reelDestroyRoute ?? 'admin.reels.destroy';
?>

<div class="table-responsive datatable-custom">
    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table fz--14px text-title">
        <thead class="thead-light">
            <tr>
                <th class="border-0"><?php echo e(translate('messages.Sl')); ?></th>
                <th class="table-column-pl-0 border-0"><?php echo e(translate('messages.Reel_Id')); ?></th>
                <th class="border-0"><?php echo e(translate('messages.Reel_information')); ?></th>
                <?php if($showStoreColumn): ?>
                    <th class="border-0"><?php echo e($storeInformationLabel); ?></th>
                <?php endif; ?>
                <th class="text-center border-0"><?php echo e(translate('messages.Total_Views')); ?></th>
                <th class="text-center border-0"><?php echo e(translate('messages.Total_Likes')); ?></th>
                <th class="text-center border-0"><?php echo e(translate('messages.Total_Store_visit')); ?></th>
                <th class="border-0"><?php echo e(translate('messages.Reel_Duration')); ?></th>
                <th class="text-center border-0"><?php echo e(translate('messages.Reels_Status')); ?></th>
                <th class="text-center border-0"><?php echo e(translate('messages.Status')); ?></th>
                <th class="text-center border-0"><?php echo e(translate('messages.Action')); ?></th>
            </tr>
        </thead>

        <tbody id="set-rows">
            <?php $__empty_1 = true; $__currentLoopData = $reels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $reel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($reels->firstItem() + $key); ?></td>
                    <td class="table-column-pl-0">
                        <a href="javascript:;"><?php echo e($reel->id); ?></a>
                    </td>
                    <td>
                        <a class="media align-items-center min-w-300px overflow-hidden" href="javascript:;">
                            <img class="avatar h-160px w-100px mr-3 onerror-image"
                                src="<?php echo e($reel->thumbnail_full_url ?? dynamicAsset('assets/admin/img/160x160/img2.jpg')); ?>"
                                data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/160x160/img2.jpg')); ?>" alt="">
                            <div class="media-body" title="<?php echo e($reel->description); ?>">
                                <div class="text-title text-wrap line--limit-2 min-w-160 max-w-200px mb-0">
                                    <?php echo e($reel->description); ?>

                                </div>
                            </div>
                        </a>
                    </td>
                    <?php if($showStoreColumn): ?>
                    <td>
                        <div class="d-flex align-items-center flex-nowrap min-w-220 overflow-hidden" title="<?php echo e($reel->restaurant?->name); ?>">
                            <img class="avatar avatar-lg rounded-circle border onerror-image flex-shrink-0"
                                src="<?php echo e($reel->restaurant?->logo_full_url ?? dynamicAsset('assets/admin/img/160x160/img2.jpg')); ?>"
                                data-onerror-image="<?php echo e(dynamicAsset('assets/admin/img/160x160/img2.jpg')); ?>" alt="">
                            <div class="ml-3">
                                <?php if($reel->restaurant && $showRestaurantLink): ?>
                                    <a href="<?php echo e(route($restaurantViewRoute, $reel->restaurant->id)); ?>" class="text-wrap line--limit-1 min-w-160 max-w-200px mb-0 text-title d-inline-block">
                                        <?php echo e($reel->restaurant?->name); ?>

                                        <?php echo $__env->make('partials._verified_restaurant_badge', ['restaurant' => $reel->restaurant], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    </a>
                                <?php else: ?>
                                    <h5 class="text-wrap line--limit-1 min-w-160 max-w-200px mb-0">
                                        <?php echo e($reel->restaurant?->name ?? $deletedStoreLabel); ?>

                                    </h5>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <?php endif; ?>
                    <td class="text-center"><?php echo e($reel->total_views); ?></td>
                    <td class="text-center"><?php echo e($reel->total_likes); ?></td>
                    <td class="text-center"><?php echo e($reel->total_store_visits); ?></td>
                    <td class="text-capitalize">
                        <?php if($reel->is_always_visible): ?>
                            <?php echo e(translate('messages.Always_Visible')); ?>

                        <?php else: ?>
                            <?php echo e(optional($reel->start_date)->format('d M Y')); ?> - <?php echo e(optional($reel->end_date)->format('d M Y')); ?>

                        <?php endif; ?>
                    </td>
                    <td class="text-capitalize text-center">
                        <span class="<?php echo e($statusClasses[$reel->reel_status_label] ?? 'text-muted bg-light'); ?> px-2 py-1 rounded-20 w-max-content mx-auto d-inline-block">
                            <?php echo e(translate('messages.' . ucfirst($reel->reel_status_label))); ?>

                        </span>
                    </td>
                    <td>
                        <label class="toggle-switch toggle-switch-sm" for="reelStatus<?php echo e($reel->id); ?>">
                            <input type="checkbox"
                                data-id="reelStatus<?php echo e($reel->id); ?>"
                                data-image-on="<?php echo e(dynamicAsset('assets/admin/img/reels/reel-stratus-on.png')); ?>"
                                data-image-off="<?php echo e(dynamicAsset('assets/admin/img/reels/reel-stratus-off.png')); ?>"
                                data-title-on="<?php echo e(translate('messages.want_to_turn_on_the_reel?')); ?>"
                                data-title-off="<?php echo e(translate('messages.want_to_turn_off_the_reel?')); ?>"
                                data-text-on="<p><?php echo e(translate('messages.if_you_turn_on_the_reel,_it_will_be_visible_to_customers.')); ?></p>"
                                data-text-off="<p><?php echo e(translate('messages.if_you_turn_off_the_reel,_it_will_no_longer_be_visible_to_customers.')); ?></p>"
                                class="toggle-switch-input dynamic-checkbox"
                                id="reelStatus<?php echo e($reel->id); ?>" <?php echo e($reel->status ? 'checked' : ''); ?>>
                            <span class="toggle-switch-label">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                        <form action="<?php echo e(route($reelStatusRoute, [$reel->id, $reel->status ? 0 : 1])); ?>"
                              method="GET" id="reelStatus<?php echo e($reel->id); ?>_form">
                        </form>
                    </td>
                    <td>
                        <div class="btn--container justify-content-center">
                            <a class="btn action-btn btn--warning btn-outline-warning action-btn offcanvas-trigger"
                               href="javascript:;" data-target="#reelsDetailsOffcanvas<?php echo e($reel->id); ?>" title="<?php echo e(translate('messages.view')); ?>">
                                <i class="tio-invisible"></i>
                            </a>
                            <a class="btn action-btn btn--primary btn-outline-primary"
                               href="<?php echo e(route($reelEditRoute, $reel->id)); ?>" title="<?php echo e(translate('messages.edit')); ?>">
                                <i class="tio-edit"></i>
                            </a>
                            <a class="btn action-btn btn-outline-danger btn--danger" data-toggle="modal"
                               data-target="#confirmation-deletes-<?php echo e($reel->id); ?>" title="<?php echo e(translate('messages.delete')); ?>">
                                <i class="tio-delete-outlined"></i>
                            </a>
                        </div>

                        <div class="modal fade" id="confirmation-deletes-<?php echo e($reel->id); ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered status-warning-modal" role="document">
                                <div class="modal-content">
                                    <div class="modal-header px-2 pt-2 border-0">
                                        <button type="button" class="close btn btn--reset btn-circle"
                                            data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true" class="tio-clear fs-20 opacity-70"></span>
                                        </button>
                                    </div>
                                    <form action="<?php echo e(route($reelDestroyRoute, $reel->id)); ?>" method="post">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('delete'); ?>
                                        <div class="modal-body pb-4 pt-0">
                                            <div class="max-349 mx-auto mt-2 mb-20">
                                                <div class="text-center">
                                                    <img src="<?php echo e(dynamicAsset('assets/admin/img/delete.png')); ?>" alt="icon" class="mb-20">
                                                    <h3 class="mb-2 fs-18"><?php echo e(translate('messages.Want_to_delete_this_Reel')); ?></h3>
                                                    <p class="text-wrap mb-0">
                                                        <?php if($reel->reel_status_label == 'live'): ?>
                                                            <?php echo e(translate('This reel is currently live and has engagement. If you delete it, it will no longer be visible to customers.')); ?>

                                                        <?php else: ?>
                                                            <?php echo e(translate('Please confirm before deleting this reel. This will permanently remove this from the reel list.')); ?>

                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="modal-footer justify-content-center border-0 pt-0 pb-4 gap-2">
                                                <button type="submit" class="btn min-w-120px btn-danger min-h-45px"><?php echo e(translate('messages.Yes,_Delete')); ?></button>
                                                <button type="button" class="btn min-w-120px btn--reset min-h-45px" data-dismiss="modal"><?php echo e(translate('messages.cancel')); ?></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="<?php echo e($showStoreColumn ? 11 : 10); ?>">
                        <div class="empty--data">
                            <img src="<?php echo e(dynamicAsset('assets/admin/svg/illustrations/sorry.svg')); ?>" alt="public">
                            <h5><?php echo e(translate('messages.no_data_found')); ?></h5>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\Modules/ReelsModule\Resources/views/admin/reels/partials/_table.blade.php ENDPATH**/ ?>