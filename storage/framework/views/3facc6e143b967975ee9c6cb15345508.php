        <div id="offcanvas__customBtn3" class="custom-offcanvas d-flex flex-column justify-content-between">
        <div class="d-flex flex-column flex-grow-1">
            <div class="custom-offcanvas-header bg-white d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
                <h3 class="mb-0 fs-18 text-title fw-semibold"><?php echo e(translate('Verification Recommendations')); ?></h3>
                        <button type="button" class="close offcanvas-close " >
                            <span aria-hidden="true" class="tio-clear"></span>
                        </button>

            </div>
            <div class="custom-offcanvas-body p-4 d-flex flex-column gap-3">
                <p class="fs-14 lh-base color-5d6167 mb-0">
                    <?php echo e(translate('We have detected that')); ?> <strong><?php echo e(number_format($recommended_restaurants)); ?></strong>
                    <?php echo e(translate('messages.restaurants_have_good_performance_based_on_their_overall_activity_you_can_give_them_a_verified_badge_which_will_appear_next_to_the_restaurant_name_to_build_customer_trust')); ?>

                </p>

                <div class="bg--secondary rounded p-4">
                    <h4 class="mb-3 fs-18 text-title fw-semibold"><?php echo e(translate('They qualified the criteria')); ?></h4>

                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-start gap-2">
                            <span class="d-center flex-shrink-0 mt-1 w-18px h-18px rounded-circle bg-success">
                                <i class="tio-done text-white fz-10px"></i>
                            </span>
                            <span class="fs-14 color-5d6167">
                                <strong class="text-title"><?php echo e(config('verified_seller.restaurants.minimum_total_orders', 10)); ?>+</strong> <?php echo e(translate('messages.orders_completed')); ?>

                            </span>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <span class="d-center flex-shrink-0 mt-1 w-18px h-18px rounded-circle bg-success">
                                <i class="tio-done text-white fz-10px"></i>
                            </span>
                            <span class="fs-14 color-5d6167">
                                <?php echo e(translate('messages.order_completion_rate_above')); ?> <strong class="text-title"><?php echo e(config('verified_seller.restaurants.minimum_success_rate', 40)); ?>%</strong>
                            </span>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <span class="d-center flex-shrink-0 mt-1 w-18px h-18px rounded-circle bg-success">
                                <i class="tio-done text-white fz-10px"></i>
                            </span>
                            <span class="fs-14 color-5d6167">
                                <strong class="text-title"><?php echo e(config('verified_seller.restaurants.minimum_account_age_months', 3)); ?>+</strong> <?php echo e(translate('messages.months_since_account_creation')); ?>

                            </span>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <span class="d-center flex-shrink-0 mt-1 w-18px h-18px rounded-circle bg-success">
                                <i class="tio-done text-white fz-10px"></i>
                            </span>
                            <span class="fs-14 color-5d6167">
                                <strong class="text-title"><?php echo e(translate('messages.positive')); ?></strong> <?php echo e(translate('messages.customer_feedback_trend')); ?>

                            </span>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <span class="d-center flex-shrink-0 mt-1 w-18px h-18px rounded-circle bg-success">
                                <i class="tio-done text-white fz-10px"></i>
                            </span>
                            <span class="fs-14 color-5d6167">
                                <strong class="text-title"><?php echo e(config('verified_seller.restaurants.minimum_avg_rating', 2)); ?>+</strong> <?php echo e(translate('Rating')); ?>/5.00
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center mt-auto offcanvas-footer p-3 position-sticky border-top">
                    <button type="button" id="open-eligible-store-list" class="btn w-100 btn--reset h--40px"><?php echo e(translate('messages.view_list')); ?></button>
            <button type="button" id="verify-all-summary" class="btn w-100 btn--primary h--40px"><?php echo e(translate('messages.verify_all')); ?></button>
        </div>
    </div>
    <div id="offcanvas__eligibleStores" class="custom-offcanvas d-flex flex-column justify-content-between">
        <div class="d-flex flex-column flex-grow-1">
            <div class="custom-offcanvas-header bg-white d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <h3 class="mb-0 fs-18 text-title fw-semibold d-flex align-items-center gap-2">
                        <span id="back-to-verification" class="d-inline-flex align-items-center justify-content-center w-20px h-20px rounded-circle bg-light">
                            <i class="tio-arrow-backward fs-12"></i>
                        </span>
                        <?php echo e(translate('messages.restaurants_list')); ?>

                    </h3>
                    <span class="badge badge-soft-dark"><?php echo e($recommended_restaurants); ?></span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="form-group form-check form--check">

                        <label class="d-flex align-items-center gap-2 mb-0 cursor-pointer">
                            <input type="checkbox" id="select-all-eligible-stores" class="form-check-input mt-0">
                            <span class="fs-14 text-title"><?php echo e(translate('messages.select_all')); ?></span>
                        </label>
                    </div>
                         <button type="button" class="close offcanvas-close " >
                            <span aria-hidden="true" class="tio-clear"></span>
                        </button>
                </div>
            </div>
            <div class="custom-offcanvas-body p-3 p-md-4 d-flex flex-column gap-3" id="eligible-store-list">
                <?php $__currentLoopData = $recommended_restaurant_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $restaurant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg--F6F6F6 rounded-10 p-3 eligible-store-item">
                        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                            <div class="d-flex align-items-center gap-3">
                                <div class="flex-shrink-0">
                                    <img class="w-50px h-50px rounded-circle border object-fit-cover"
                                         src="<?php echo e($restaurant['logo_full_url']); ?>">
                                </div>
                                <div>
                                    <h4 class="mb-1 fs-16 text-title fw-semibold"><?php echo e($restaurant['name']); ?></h4>
                                    <div class="d-flex flex-wrap gap-2 fs-13 color-6c757d">
                                        <span><?php echo e(translate('messages.Rating')); ?> <strong> <?php echo e(number_format($restaurant['avg_rating'], 1)); ?>/5</strong> </span> |
                                        <span><?php echo e(translate('messages.Order')); ?> <strong><?php echo e(number_format($restaurant['total_orders'])); ?></strong> </span>
                                    </div>
                                </div>
                            </div>

                             <div class="eligible-action-wrapper d-flex align-items-center justify-content-end min-w-110px">
                            <a href="<?php echo e(route('admin.restaurant.verified-seller', [$restaurant['id']])); ?>" class="btn btn--primary btn-sm bg-success-gradient border-0 eligible-give-btn">
                                <i class="tio-verified mr-1"></i><?php echo e(translate('Give Badge')); ?>

                            </a>
                            <label class="form-check m-0 d-none eligible-store-check">
                                <input type="checkbox" class="form-check-input mt-0 opacity-0" checked disabled>
                                <div class="w-30px aspect-1-1 bg-white rounded-circle d-flex justify-content-center align-items-center">
                                    <i class="tio-done text-success fs-20"></i>
                                </div>
                            </label>
                        </div>

                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <div id="eligible-store-footer" class=" d-none">
            <div class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center mt-auto offcanvas-footer p-3 position-sticky border-top">
                <div class="d-flex gap-3 w-100 justify-content-center">
                    <button type="button" class="btn w-100 btn--reset offcanvas-close h--40px"><?php echo e(translate('messages.cancel')); ?></button>
                    <button type="button" id="verify-all-stores" class="btn w-100 btn--primary h--40px"><?php echo e(translate('messages.verify_all')); ?></button>
                </div>
            </div>
        </div>
    </div>
    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>
    <div class="modal shedule-modal fade" id="verify-all-modal" tabindex="-1" aria-labelledby="verifyAllModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-custom-sm">
            <div class="modal-content pb-2 max-w-500">
               <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true" class="tio-clear"></span>
                        </button>
                    </div>
                <div class="modal-body">
                    <div class="text-center">
                        <img src="<?php echo e(dynamicAsset('assets/admin/img/badge-big.png')); ?>" alt="icon" class="mb-3">
                <h3 class="mb-2"><?php echo e(translate('messages.verify_all_qualified_restaurants')); ?></h3>
                        <p class="mb-0"><?php echo e(translate('messages.this_will_give_a_verified_badge_to_every_restaurant_that_matches_the_verification_criteria')); ?></p>
                    </div>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 gap-2">
                            <button type="button" class="btn min-w-120px btn--reset" data-dismiss="modal"><?php echo e(translate('messages.Cancel')); ?></button>
                    <a href="<?php echo e(route('admin.restaurant.verified-seller-all')); ?>" class="btn min-w-120px btn--primary"><?php echo e(translate('messages.Yes')); ?></a>
                </div>
            </div>
        </div>
    </div>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/vendor/partials/_verification-recomend-offcanvas.blade.php ENDPATH**/ ?>