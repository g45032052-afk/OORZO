<div class="js-nav-scroller hs-nav-scroller-horizontal mb-3">
            <ul class="nav nav-tabs border-0 nav--tabs nav--pills nav--theme-version">
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Route::is('admin.customer.view') ? 'active' : ''); ?>" href="<?php echo e(route('admin.customer.view', $customer->id)); ?>"><?php echo e(translate('Overview')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Route::is('admin.customer.order-list') ? 'active' : ''); ?>" href="<?php echo e(route('admin.customer.order-list', $customer->id)); ?>"><?php echo e(translate('Order List')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Route::is('admin.customer.wish-list') ? 'active' : ''); ?>" href="<?php echo e(route('admin.customer.wish-list', $customer->id)); ?>"><?php echo e(translate('Wishlist')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(Route::is('admin.customer.review-list') ? 'active' : ''); ?>" href="<?php echo e(route('admin.customer.review-list', $customer->id)); ?>"><?php echo e(translate('Rating & Reviews')); ?></a>
                </li>
                
                <?php if((int) ($customer->sub_tenant_id ?? 0) <= 0): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(Route::is('admin.customer.wallet-history') ? 'active' : ''); ?>" href="<?php echo e(route('admin.customer.wallet-history', $customer->id)); ?>"><?php echo e(translate('Wallet')); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(Route::is('admin.customer.loyalty-point') ? 'active' : ''); ?> " href="<?php echo e(route('admin.customer.loyalty-point', $customer->id)); ?>">Loyalty point</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(Route::is('admin.customer.referral') ? 'active' : ''); ?> " href="<?php echo e(route('admin.customer.referral', $customer->id)); ?>"><?php echo e(translate('Referral')); ?></a>
                    </li>
                <?php endif; ?>
                <?php if(\App\CentralLogics\Helpers::get_business_settings('pro_member_status') == 1): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(Route::is('admin.customer.subscription-plan') ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.customer.subscription-plan', $customer->id)); ?>"><?php echo e(translate('messages.Subscription_Plan')); ?></a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/customer/partials/_user_details_urls.blade.php ENDPATH**/ ?>