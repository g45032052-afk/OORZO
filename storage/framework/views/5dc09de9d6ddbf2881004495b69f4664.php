<?php $__currentLoopData = $reels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $saleEngagements = collect($reel->engagements ?? [])->where('type', \Modules\ReelsModule\Entities\ReelEngagement::TYPE_ORDER);
        $saleCount = $saleEngagements->count();
        $saleAmount = (float) $saleEngagements->sum('amount');
        $offcanvasStatusClasses = [
            'live' => 'text-success bg-success bg-opacity-10',
            'upcoming' => 'text-info bg-info bg-opacity-10',
            'expired' => 'text-danger bg-danger bg-opacity-10',
            'deactivated' => 'text-warning bg-warning bg-opacity-10',
        ];
    ?>
    <div id="reelsDetailsOffcanvas<?php echo e($reel->id); ?>" style="overflow-y: auto;" class="custom-offcanvas d-flex flex-column justify-content-between global_guideline_offcanvas">
        <div>
            <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
                <h3 class="mb-0"><?php echo e(translate('messages.Reels_Details')); ?></h3>
                <button type="button" class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0" aria-label="Close">&times;</button>
            </div>
            <div class="p-3">
                <div class="d-flex justify-content-center align-items-center mb-3">
                    <div class="reels-video-wrapper">
                        <img src="<?php echo e($reel->thumbnail_full_url ?? dynamicAsset('assets/admin/img/160x160/img2.jpg')); ?>" alt="" class="reels-thumbnail">
                        <video class="reels-video" width="400" height="470" preload="none" controls>
                            <source src="<?php echo e($reel->video_full_url); ?>" type="video/mp4">
                            <?php echo e(translate('messages.Your_browser_does_not_support_the_video_tag.')); ?>

                        </video>
                        <div class="reels-play-btn">
                            <div class="d-flex justify-content-center align-items-center w-100 h-100">
                                <i class="tio-play"></i>
                            </div>
                        </div>
                        <div class="reels-close-btn">✕</div>
                    </div>
                </div>

                <div class="bg-light p-3 rounded mb-3">
                    <div class="d-flex gap-2 align-items-center justify-content-between mb-3">
                        <div class="flex-grow-1">
                            <?php echo e(translate('messages.Reel_Id')); ?>: <span class="text-title"><?php echo e($reel->id); ?></span>
                        </div>
                        <span class="<?php echo e($offcanvasStatusClasses[$reel->reel_status_label] ?? 'text-muted bg-light'); ?> px-2 py-1 rounded-20 w-max-content flex-shrink-0">
                            <?php echo e(translate('messages.' . ucfirst($reel->reel_status_label))); ?>

                        </span>
                    </div>
                    <h4 class="mb-2"><?php echo e(translate('messages.Short_Description')); ?></h4>
                    <p class="fw-medium mb-0"><?php echo e($reel->description); ?></p>
                </div>

                <div class="bg-light p-3 rounded mb-3">
                    <h4 class="mb-2"><?php echo e(translate('messages.Reel_Validity')); ?></h4>
                    <div class="d-flex align-items-stretch">
                        <div class="w-50 pe-3">
                            <?php echo e(translate('messages.Upload_Date')); ?>: <span class="text-title"><?php echo e(optional($reel->created_at)->format('d M Y')); ?></span>
                        </div>
                        <div class="border-start"></div>
                        <div class="w-50 ps-3">
                            <?php echo e(translate('messages.Expired_Date')); ?>:
                            <span class="text-title">
                                <?php echo e($reel->is_always_visible ? translate('messages.Always_Visible') : optional($reel->end_date)->format('d M Y')); ?>

                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-light p-3 rounded mb-3">
                    <h4 class="mb-2"><?php echo e(translate('Reel Earning')); ?></h4>
                    <p class="mb-1"><?php echo e(translate('Total Sale Sold')); ?>: <span class="text-title fw-medium"><?php echo e($saleCount ?: ($reel->total_sale ?? $reel->order_count ?? 0)); ?></span></p>
                    <p class="mb-0"><?php echo e(translate('Total Sale Amount')); ?>: <span class="text-title fw-medium"><?php echo e(\App\CentralLogics\Helpers::format_currency($saleAmount ?: ($reel->total_sale_amount ?? 0))); ?></span></p>
                </div>


                <div class="bg-light p-3 rounded mb-3">
                    <h4 class="d-flex gap-1 mb-2"><i class="tio-shop"></i> <?php echo e(translate('messages.Vendor_Information')); ?></h4>
                    <div class="d-flex gap-2 align-items-center">
                        <img class="avatar avatar-70 border onerror-image" src="<?php echo e($reel->restaurant?->logo_full_url ?? dynamicAsset('assets/admin/img/160x160/img2.jpg')); ?>">
                        <div class="flex-grow-1 ">
                            <h5 class="mb-1">
                                <?php echo e($reel->restaurant?->name ?? $deletedStoreLabel); ?>

                                <?php echo $__env->make('partials._verified_restaurant_badge', ['restaurant' => $reel->restaurant], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </h5>
                            <h6 class="mb-1"><?php echo e($reel->restaurant?->phone ?? ''); ?></h6>
                            <p class="fs-12 d-flex gap-1 text-break mb-0">
                                <i class="tio-poi"></i>
                                <?php echo e($reel->restaurant?->address ?? ''); ?>

                            </p>
                        </div>
                    </div>
                </div>

                <div class="row g-2 border-top pt-3">
                    <div class="col-sm-4">
                        <div class="bg-light rounded p-2 text-center">
                            <div class="d-flex gap-1 justify-content-center align-items-center fs-12">
                                <i class="tio-visible-outlined fs-16"></i> <?php echo e(translate('messages.Views')); ?>

                            </div>
                            <h5 class="text-info"><?php echo e($reel->total_views); ?></h5>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="bg-light rounded p-2 text-center">
                            <div class="d-flex gap-1 justify-content-center align-items-center fs-12">
                                <i class="tio-thumbs-up fs-16"></i> <?php echo e(translate('messages.Likes')); ?>

                            </div>
                            <h5 class="text-info"><?php echo e($reel->total_likes); ?></h5>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="bg-light rounded p-2 text-center">
                            <div class="d-flex gap-1 justify-content-center align-items-center fs-12">
                                <i class="tio-shop-outlined fs-16"></i> <?php echo e(translate('messages.Store_Visits')); ?>

                            </div>
                            <h5 class="text-info"><?php echo e($reel->total_store_visits); ?></h5>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="bg-light rounded p-2 text-center">
                            <div class="d-flex gap-1 justify-content-center align-items-center fs-12">
                                <img width="16" src="<?php echo e(dynamicAsset('assets/admin/img/reels/total-sale-icon-black.svg')); ?>" alt="">
                                <span><?php echo e(translate('messages.Total_Sale')); ?></span>
                            </div>
                            <h5 class="text-info"><?php echo e($saleCount ?: ($reel->total_sale ?? $reel->order_count ?? 0)); ?></h5>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="bg-light rounded p-2 text-center">
                            <div class="d-flex gap-1 justify-content-center align-items-center fs-12">
                                <img width="16" src="<?php echo e(dynamicAsset('assets/admin/img/reels/total-sale-amount-icon-black.svg')); ?>" alt="">
                                <span><?php echo e(translate('messages.Total_Sale_Amount')); ?></span>
                            </div>
                            <h5 class="text-info"><?php echo e(\App\CentralLogics\Helpers::format_currency($saleAmount ?: ($reel->total_sale_amount ?? 0))); ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<div id="offcanvasOverlay" class="offcanvas-overlay"></div>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\Modules/ReelsModule\Resources/views/shared/_reels-details-offcanvas.blade.php ENDPATH**/ ?>