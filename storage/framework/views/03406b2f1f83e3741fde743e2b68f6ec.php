
    <!-- Nav -->
    <ul class="nav nav-tabs page-header-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request('tab')==null?'active':''); ?>"  href="<?php echo e(route('admin.restaurant.view', $restaurant->id)); ?>"><?php echo e(translate('messages.overview')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='order'?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'order'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.orders')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='product'?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'product'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.foods')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='reviews'?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'reviews'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.reviews')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='discount'?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'discount'])); ?>"  aria-disabled="true"><?php echo e(translate('discounts')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='transaction'?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'transaction'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.transactions')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='settings'?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'settings'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.settings')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='conversations'?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'conversations'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.conversations')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab') =='business_plan' || request('tab') =='subscriptions-transactions' ?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'business_plan'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.Business Plan')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='meta-data' ?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'meta-data'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.meta-data')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='qr-code' ?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'qr-code'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.QR_code')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='disbursements' ?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'disbursements'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.disbursements')); ?></a>
                </li>
                <?php if(addon_published_status('ReelsModule') && \App\CentralLogics\Helpers::get_business_settings('vendor_can_upload_reels')): ?>
                    <li class="nav-item">
                        <a class="nav-link  <?php echo e(request('tab')=='reels' ?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'reels'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.reels')); ?></a>
                    </li>
                <?php endif; ?>
            </ul>
    <div class="modal shedule-modal fade" id="badge-modal-btn" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-custom-sm">
            <div class="modal-content pb-2 ">
               <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true" class="tio-clear"></span>
                        </button>
                    </div>
                <div class="modal-body">
                    <div class="text-center">
                        <img src="<?php echo e(dynamicAsset('assets/admin/img/badge-big.png')); ?>" alt="icon" class="mb-3">
                        <h3 class="mb-2"><?php echo e(translate('messages.give_verified_badge_to_this_restaurant')); ?></h3>
                        <p class="mb-0"><?php echo e(translate('messages.this_will_mark_the_restaurant_as_verified_and_a_trusted_badge_will_be_displayed_on_the_restaurant_details_for_customers')); ?></p>
                    </div>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 gap-2">
                    <button type="button" class="btn min-w-120px btn--reset" data-dismiss="modal"><?php echo e(translate('messages.Cancel')); ?></button>
                    <a href="<?php echo e(route('admin.restaurant.verified-seller', [$restaurant->id])); ?>" class="btn min-w-120px btn--primary"><?php echo e(translate('messages.Yes')); ?></a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal shedule-modal fade" id="remove-badge-btn" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-custom-sm">
            <div class="modal-content pb-2 ">
                <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true" class="tio-clear"></span>
                        </button>
                    </div>
                <div class="modal-body">
                    <div class="text-center">
                        <img src="<?php echo e(dynamicAsset('assets/admin/img/remove-badge.png')); ?>" alt="icon" class="mb-3">
                        <h3 class="mb-2"><?php echo e(translate('messages.want_to_remove_the_verified_badge_from_this_restaurant')); ?></h3>
                        <p class="mb-0"><?php echo e(translate('messages.the_restaurant_will_lose_its_verified_status_but_you_can_reassign_the_badge_at_any_time')); ?></p>
                    </div>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 gap-2">
                    <button type="button" class="btn min-w-120px btn--reset" data-dismiss="modal"><?php echo e(translate('messages.Cancel')); ?></button>
                    <a href="<?php echo e(route('admin.restaurant.verified-seller', [$restaurant->id])); ?>" class="btn min-w-120px btn--danger"><?php echo e(translate('messages.Yes')); ?></a>
                </div>
            </div>
        </div>
    </div>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/vendor/view/partials/_header.blade.php ENDPATH**/ ?>