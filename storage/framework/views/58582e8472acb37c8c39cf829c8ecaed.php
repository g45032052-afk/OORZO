<?php
    use App\Models\BusinessSetting;
    use App\CentralLogics\Helpers;
?>

<?php $__env->startSection('title', $restaurant->name . "'s" . translate('messages.settings')); ?>
<?php $__env->startSection('content'); ?>
    <?php ($order_subscription = BusinessSetting::where('key', 'order_subscription')->first()); ?>

    <div class="content container-fluid">
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <h1 class="page-header-title text-break">
                    <i class="tio-museum"></i> <span><?php echo e($restaurant->name); ?> <?php echo $__env->make('partials._verified_restaurant_badge', ['restaurant' => $restaurant], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
                </h1>
            </div>
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                <span class="hs-nav-scroller-arrow-prev initial-hidden">
                    <a class="hs-nav-scroller-arrow-link" href="javascript:">
                        <i class="tio-chevron-left"></i>
                    </a>
                </span>

                <span class="hs-nav-scroller-arrow-next initial-hidden">
                    <a class="hs-nav-scroller-arrow-link" href="javascript:">
                        <i class="tio-chevron-right"></i>
                    </a>
                </span>
                <?php echo $__env->make('admin-views.vendor.view.partials._header', ['restaurant' => $restaurant], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title">
                    <span class="card-header-icon"><i class="tio-fastfood"></i></span> &nbsp;
                    <span><?php echo e(translate('messages.restaurant_settings')); ?></span>
                </h5>
            </div>
            <div class="card-body">
                <div class="row align-items-end g-2">
                    <div class="col-xl-4 col-md-4 col-sm-6">
                        <div class="form-group mb-0">
                            <label
                                class="toggle-switch toggle-switch-sm d-flex justify-content-between border rounded px-3 form-control"
                                for="food_section">
                                <span class="pr-2 d-flex">
                                    <span><?php echo e(translate('messages.Food_Management')); ?></span>
                                    <span data-toggle="tooltip" data-placement="right"
                                        data-original-title='<?php echo e(translate('When_disabled,_the_food_management_feature_will_be_hidden_from_the_restaurant_panel_&_restaurant_app.')); ?>'
                                        class="input-label-secondary">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </span>
                                <input type="checkbox" data-id="food_section" data-type="status"
                                    data-image-on="<?php echo e(dynamicAsset('assets/admin/img/modal/veg-on.png')); ?>"
                                    data-image-off="<?php echo e(dynamicAsset('assets/admin/img/modal/veg-off.png')); ?>"
                                    data-title-on="<?php echo e(translate('Want_to_enable_Food_Management_for_this_restaurant?')); ?>"
                                    data-title-off="<?php echo e(translate('Want_to_disable_Food_Management_for_this_restaurant?')); ?>"
                                    data-text-on="<p><?php echo e(translate('If_enabled,_the_food_management_feature_will_be_available_for_this_restaurant.')); ?></p>"
                                    data-text-off="<p><?php echo e(translate('If_disabled,_the_food_management_feature_will_be_hidden_from_this_restaurant.')); ?></p>"
                                    class="toggle-switch-input dynamic-checkbox" name="food_section" id="food_section"
                                    <?php echo e($restaurant->food_section ? 'checked' : ''); ?>>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                            <form
                                action="<?php echo e(route('admin.restaurant.toggle-settings', [$restaurant->id, $restaurant->food_section ? 0 : 1, 'food_section'])); ?>"
                                method="get" id="food_section_form">
                            </form>
                        </div>
                    </div>


                    <div class="col-xl-4 col-md-4 col-sm-6">
                        <div class="form-group mb-0">
                            <label
                                class="toggle-switch toggle-switch-sm d-flex justify-content-between border  rounded px-3 form-control"
                                for="schedule_order">
                                <span class="pr-2 d-flex">
                                    <span class="line--limit-1">
                                        <?php echo e(translate('messages.scheduled_delivery')); ?>

                                    </span>
                                    <span data-toggle="tooltip" data-placement="right"
                                        data-original-title="<?php echo e(translate('When_enabled,_restaurant_owners_can_take_scheduled_orders_from_customers')); ?>"
                                        class="input-label-secondary">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </span>
                                <input type="checkbox" data-id="schedule_order" data-type="status"
                                    data-image-on="<?php echo e(dynamicAsset('assets/admin/img/modal/schedule-on.png')); ?>"
                                    data-image-off="<?php echo e(dynamicAsset('assets/admin/img/modal/schedule-off.png')); ?>"
                                    data-title-on="<?php echo e(translate('Want_to_enable_Schedule_Order_for_this_restaurant?')); ?>"
                                    data-title-off="<?php echo e(translate('Want_to_disable_Schedule_Order_for_this_restaurant?')); ?>"
                                    data-text-on="<p><?php echo e(translate('If_enabled,_the_scheduled_order_option_will_be_available_for_this_restaurant’s_products.')); ?></p>"
                                    data-text-off="<p><?php echo e(translate('If_disabled,_the_scheduled_order_option_will_be_hidden_for_this_restaurant’s_products.')); ?></p>"
                                    class="toggle-switch-input dynamic-checkbox" id="schedule_order"
                                    <?php echo e($restaurant->schedule_order ? 'checked' : ''); ?>>
                                <span class="toggle-switch-label">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                            <form
                                action="<?php echo e(route('admin.restaurant.toggle-settings', [$restaurant->id, $restaurant->schedule_order ? 0 : 1, 'schedule_order'])); ?>"
                                method="get" id="schedule_order_form">
                            </form>
                        </div>
                    </div>
                    <?php if($restaurant->restaurant_model == 'commission'): ?>
                        <div class="col-xl-4 col-md-4 col-sm-6">
                            <div class="form-group mb-0">
                                <label
                                    class="toggle-switch toggle-switch-sm d-flex justify-content-between border  rounded px-3 form-control"
                                    for="reviews_section">
                                    <span class="pr-2 d-flex">
                                        <span class="line--limit-1">
                                            <?php echo e(translate('messages.Reviews_section')); ?>

                                        </span>
                                        <span data-toggle="tooltip" data-placement="right"
                                            data-original-title="<?php echo e(translate('When_enabled,_restaurant_owners_can_see_customer’s_review.')); ?>"
                                            class="input-label-secondary">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </span>
                                    <input type="checkbox" data-id="reviews_section" data-type="status"
                                        data-image-on="<?php echo e(dynamicAsset('assets/admin/img/modal/this-criteria-on.png')); ?>"
                                        data-image-off="<?php echo e(dynamicAsset('assets/admin/img/modal/this-criteria-off.png')); ?>"
                                        data-title-on="<?php echo e(translate('Want_to_enable_reviews_section_for_this_restaurant?')); ?>"
                                        data-title-off="<?php echo e(translate('Want_to_disable_reviews_section_for_this_restaurant?')); ?>"
                                        data-text-on="<p><?php echo e(translate('If_enabled,_restaurant_owners_can_see_customer’s_review.')); ?></p>"
                                        data-text-off="<p><?php echo e(translate('If_disabled,_restaurant_owners_can_not_see_customer’s_review.')); ?></p>"
                                        class="toggle-switch-input dynamic-checkbox" name="reviews_section"
                                        id="reviews_section" <?php echo e($restaurant->reviews_section ? 'checked' : ''); ?>>
                                    <span class="toggle-switch-label text">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                                <form
                                    action="<?php echo e(route('admin.restaurant.toggle-settings', [$restaurant->id, $restaurant->reviews_section ? 0 : 1, 'reviews_section'])); ?>"
                                    method="get" id="reviews_section_form">
                                </form>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-4 col-sm-6">
                            <div class="form-group mb-0">
                                <label
                                    class="toggle-switch toggle-switch-sm d-flex justify-content-between border  rounded px-3 form-control"
                                    for="pos_system">
                                    <span class="pr-2 d-flex">
                                        <span class="line--limit-1">
                                            <?php echo e(translate('messages.POS_Section')); ?>

                                        </span>
                                        <span data-toggle="tooltip" data-placement="right"
                                            data-original-title="<?php echo e(translate('If this option is turned on, the restaurant panel will get the Point of Sale (POS) option.')); ?>"
                                            class="input-label-secondary">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </span>
                                    <input type="checkbox" data-id="pos_system" data-type="status"
                                        data-image-on="<?php echo e(dynamicAsset('assets/admin/img/modal/criteria-on.png')); ?>"
                                        data-image-off="<?php echo e(dynamicAsset('assets/admin/img/modal/criteria-off.png')); ?>"
                                        data-title-on="<?php echo e(translate('Want_to_enable_pos_system_for_this_restaurant?')); ?>"
                                        data-title-off="<?php echo e(translate('Want_to_disable_pos_system_for_this_restaurant?')); ?>"
                                        data-text-on="<p><?php echo e(translate('If_enabled,_restaurant_owners_use_the_pos_system.')); ?></p>"
                                        data-text-off="<p><?php echo e(translate('If_disabled,_pos_system_will_be_hidden_for_this_restaurant.')); ?></p>"
                                        class="toggle-switch-input dynamic-checkbox" id="pos_system"
                                        <?php echo e($restaurant->pos_system ? 'checked' : ''); ?>>
                                    <span class="toggle-switch-label text">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                                <form
                                    action="<?php echo e(route('admin.restaurant.toggle-settings', [$restaurant->id, $restaurant->pos_system ? 0 : 1, 'pos_system'])); ?>"
                                    method="get" id="pos_system_form">
                                </form>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-4 col-sm-6">
                            <div class="form-group mb-0">
                                <label
                                    class="toggle-switch toggle-switch-sm d-flex justify-content-between border  rounded px-3 form-control"
                                    for="self_delivery_system">
                                    <span class="pr-2 d-flex">
                                        <span class="line--limit-1">
                                            <?php echo e(translate('messages.self_delivery')); ?>

                                        </span>
                                        <span data-toggle="tooltip" data-placement="right"
                                            data-original-title="<?php echo e(translate('When_this_option_is_enabled,_restaurants_need_to_deliver_orders_by_themselves_or_by_their_own_delivery_man._Restaurants_will_also_get_an_option_for_adding_their_own_delivery_man_from_the_restaurant_panel.')); ?>"
                                            class="input-label-secondary">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </span>
                                    <input type="checkbox" data-id="self_delivery_system" data-type="status"
                                        data-image-on="<?php echo e(dynamicAsset('assets/admin/img/modal/home-delivery-on.png')); ?>"
                                        data-image-off="<?php echo e(dynamicAsset('assets/admin/img/modal/home-delivery-off.png')); ?>"
                                        data-title-on="<?php echo e(translate('Want_to_enable_self_delivery_system_for_this_restaurant?')); ?>"
                                        data-title-off="<?php echo e(translate('Want_to_disable_self_delivery_system_for_this_restaurant?')); ?>"
                                        data-text-on="<p><?php echo e(translate('If_enabled,_restaurant_owners_can_use_their_own_delivery_system.')); ?></p>"
                                        data-text-off="<p><?php echo e(translate('If_disabled,_self_delivery_option_will_be_hidden_for_this_restaurant.')); ?></p>"
                                        class="toggle-switch-input dynamic-checkbox" id="self_delivery_system"
                                        <?php echo e($restaurant->self_delivery_system ? 'checked' : ''); ?>>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                                <form
                                    action="<?php echo e(route('admin.restaurant.toggle-settings', [$restaurant->id, $restaurant->self_delivery_system ? 0 : 1, 'self_delivery_system'])); ?>"
                                    method="get" id="self_delivery_system_form">
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="col-xl-4 col-md-4 col-sm-6">
                        <div class="form-group mb-0">
                            <label
                                class="toggle-switch toggle-switch-sm d-flex justify-content-between border  rounded px-3 form-control"
                                for="delivery">
                                <span class="pr-2 d-flex">
                                    <span class="line--limit-1">
                                        <?php echo e(translate('messages.home_delivery')); ?>

                                    </span>
                                    <span data-toggle="tooltip" data-placement="right"
                                        data-original-title="<?php echo e(translate('When_enabled,_customers_can_make_home_delivery_orders_from_this_restaurant.')); ?>"
                                        class="input-label-secondary">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </span>
                                <input type="checkbox" name="delivery" data-id="delivery" data-type="status"
                                    data-image-on="<?php echo e(dynamicAsset('assets/admin/img/modal/dm-self-reg-on.png')); ?>"
                                    data-image-off="<?php echo e(dynamicAsset('assets/admin/img/modal/dm-self-reg-off.png')); ?>"
                                    data-title-on="<?php echo e(translate('Want_to_enable_Home_Delivery_for_this_restaurant?')); ?>"
                                    data-title-off="<?php echo e(translate('Want_to_disable_Home_Delivery_for_this_restaurant?')); ?>"
                                    data-text-on="<p><?php echo e(translate('If_enabled,_the_home_delivery_feature_will_be_available_for_the_restaurant’s_items.')); ?></p>"
                                    data-text-off="<p><?php echo e(translate('If_disabled,_the_home_delivery_feature_will_be_hidden_from_this_restaurant’s_items.')); ?></p>"
                                    class="toggle-switch-input dynamic-checkbox" id="delivery"
                                    <?php echo e($restaurant->delivery ? 'checked' : ''); ?>>
                                <span class="toggle-switch-label">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                            <form
                                action="<?php echo e(route('admin.restaurant.toggle-settings', [$restaurant->id, $restaurant->delivery ? 0 : 1, 'delivery'])); ?>"
                                method="get" id="delivery_form">
                            </form>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-4 col-sm-6">
                        <div class="form-group mb-0">
                            <label
                                class="toggle-switch toggle-switch-sm d-flex justify-content-between border  rounded px-3 form-control"
                                for="take_away">
                                <span class="pr-2 d-flex">
                                    <span class="line--limit-1">
                                        <?php echo e(translate('messages.Takeaway')); ?>

                                    </span>
                                    <span data-toggle="tooltip" data-placement="right"
                                        data-original-title='<?php echo e(translate('When_enabled,_customers_can_place_takeaway/self-pickup_orders_from_this_restaurant.')); ?>'
                                        class="input-label-secondary">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </span>
                                <input type="checkbox" data-id="take_away" data-type="status"
                                    data-image-on="<?php echo e(dynamicAsset('assets/admin/img/modal/takeaway-on.png')); ?>"
                                    data-image-off="<?php echo e(dynamicAsset('assets/admin/img/modal/takeaway-off.png')); ?>"
                                    data-title-on="<?php echo e(translate('Want_to_enable_take_away_for_this_restaurant?')); ?>"
                                    data-title-off="<?php echo e(translate('Want_to_disable_take_away_for_this_restaurant?')); ?>"
                                    data-text-on="<p><?php echo e(translate('If_enabled,_the_takeaway_feature_will_be_available_for_the_restaurant.')); ?></p>"
                                    data-text-off="<p><?php echo e(translate('If_disabled,_the_takeaway_feature_will_be_hidden_from_the_restaurant.')); ?></p>"
                                    class="toggle-switch-input dynamic-checkbox" id="take_away"
                                    <?php echo e($restaurant->take_away ? 'checked' : ''); ?>>
                                <span class="toggle-switch-label">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                            <form
                                action="<?php echo e(route('admin.restaurant.toggle-settings', [$restaurant->id, $restaurant->take_away ? 0 : 1, 'take_away'])); ?>"
                                method="get" id="take_away_form">
                            </form>
                        </div>
                    </div>

                    <?php if(isset($order_subscription) && $order_subscription->value == 1): ?>
                        <div class="col-xl-4 col-md-4 col-sm-6">
                            <div class="form-group mb-0">
                                <label
                                    class="toggle-switch toggle-switch-sm d-flex justify-content-between border  rounded px-3 form-control"
                                    for="order_subscription">
                                    <span class="pr-2 d-flex">
                                        <span class="line--limit-1">
                                            <?php echo e(translate('messages.order_subscription')); ?>

                                        </span>
                                        <span data-toggle="tooltip" data-placement="right"
                                            data-original-title='<?php echo e(translate('If this option is on , customer can place subscription based order in user app.')); ?>'
                                            class="input-label-secondary">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </span>
                                    <input type="checkbox" data-id="order_subscription" data-type="status"
                                        data-image-on="<?php echo e(dynamicAsset('assets/admin/img/modal/store-reg-on.png')); ?>"
                                        data-image-off="<?php echo e(dynamicAsset('assets/admin/img/modal/store-reg-off.png')); ?>"
                                        data-title-on="<?php echo e(translate('Want_to_enable_order_subscription_for_this_restaurant?')); ?>"
                                        data-title-off="<?php echo e(translate('Want_to_disable_order_subscription_for_this_restaurant?')); ?>"
                                        data-text-on="<p><?php echo e(translate('If_enabled,_the_order_subscription_feature_will_be_available_for_the_restaurant.')); ?></p>"
                                        data-text-off="<p><?php echo e(translate('If_disabled,_the_order_subscription_feature_will_be_hidden_from_the_restaurant.')); ?></p>"
                                        class="toggle-switch-input dynamic-checkbox" id="order_subscription"
                                        <?php echo e($restaurant->order_subscription_active == 1 ? 'checked' : ''); ?>>

                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                                <form
                                    action="<?php echo e(route('admin.restaurant.toggle-settings', [$restaurant->id, $restaurant->order_subscription_active ? 0 : 1, 'order_subscription_active'])); ?>"
                                    method="get" id="order_subscription_form">
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="col-xl-4 col-md-4 col-sm-6">
                        <div class="form-group mb-0">
                            <label
                                class="toggle-switch toggle-switch-sm d-flex justify-content-between border  rounded px-3 form-control"
                                for="instant_order">
                                <span class="pr-2 d-flex">
                                    <span class="line--limit-1">
                                        <?php echo e(translate('messages.instant_order')); ?>

                                    </span>
                                    <span data-toggle="tooltip" data-placement="right"
                                        data-original-title='<?php echo e(translate('If_enabled,_customers_can_instantly_order_from_this_restaurant._Otherwise,_customers_can_only_place_“scheduled_orders”.')); ?>'
                                        class="input-label-secondary">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </span>
                                <input type="checkbox" data-id="instant_order" data-type="status"
                                    data-image-on="<?php echo e(dynamicAsset('assets/admin/img/modal/veg-on.png')); ?>"
                                    data-image-off="<?php echo e(dynamicAsset('assets/admin/img/modal/veg-off.png')); ?>"
                                    data-title-on="<?php echo e(translate('Want_to_enable_instant_order_for_this_restaurant?')); ?>"
                                    data-title-off="<?php echo e(translate('Want_to_disable_instant_order_for_this_restaurant?')); ?>"
                                    data-text-on="<p><?php echo e(translate('If_enabled,_customers_can_order_instantly.')); ?></p>"
                                    data-text-off="<p><?php echo e(translate('If_disabled,_customers_can_not_order_instantly.')); ?></p>"
                                    class="toggle-switch-input dynamic-checkbox" id="instant_order"
                                    <?php echo e($restaurant->restaurant_config?->instant_order == 1 ? 'checked' : ''); ?>>
                                <span class="toggle-switch-label">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                            <form
                                action="<?php echo e(route('admin.restaurant.toggle-settings', [$restaurant->id, $restaurant->restaurant_config?->instant_order ? 0 : 1, 'instant_order'])); ?>"
                                method="get" id="instant_order_form">
                            </form>
                        </div>
                    </div>

                    <?php ($self_delivey = 0); ?>
                    <?php if(
                        ($restaurant->restaurant_model == 'subscription' &&
                            isset($restaurant->restaurant_sub) &&
                            $restaurant->restaurant_sub->self_delivery == 1) ||
                            ($restaurant->restaurant_model == 'commission' && $restaurant->self_delivery_system == 1)): ?>
                        <?php ($self_delivey = 1); ?>
                    <?php endif; ?>

                    <?php if($self_delivey == 1): ?>
                        <div class="col-xl-4 col-md-4 col-sm-6">
                            <div class="form-group mb-0">
                                <label
                                    class="toggle-switch toggle-switch-sm d-flex justify-content-between border  rounded px-3 form-control"
                                    for="customer_date_order_sratus">
                                    <span class="pr-2 d-flex">
                                        <span class="line--limit-1">
                                            <?php echo e(translate('messages.custom_date_order_status')); ?>

                                        </span>
                                        <span data-toggle="tooltip" data-placement="right"
                                            data-original-title='<?php echo e(translate('If_enabled,_customers_can_choose_a_custom_date_during_scheduled_order_placement.')); ?>'
                                            class="input-label-secondary">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </span>
                                    <input type="checkbox" data-id="customer_date_order_sratus" data-type="status"
                                        data-image-on="<?php echo e(dynamicAsset('assets/admin/img/modal/schedule-on.png')); ?>"
                                        data-image-off="<?php echo e(dynamicAsset('assets/admin/img/modal/schedule-off.png')); ?>"
                                        data-title-on="<?php echo e(translate('Want_to_enable_custom_date_order_status_for_this_restaurant?')); ?>"
                                        data-title-off="<?php echo e(translate('Want_to_disable_custom_date_order_status_for_this_restaurant?')); ?>"
                                        data-text-on="<p><?php echo e(translate('If_enabled,_customers_can_not_select_schedule_date_over_the_given_days._and_you_must_set_a_date_on_the')); ?> <b><?php echo e(translate('Customer_Can_Order_Within_field')); ?></b></p>"
                                        data-text-off="<p><?php echo e(translate('If_disabled,_customers_can_select_any_schedule_date.')); ?></p>"
                                        class="toggle-switch-input dynamic-checkbox" id="customer_date_order_sratus"
                                        <?php echo e($restaurant->restaurant_config?->customer_date_order_sratus == 1 ? 'checked' : ''); ?>>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                                <form
                                    action="<?php echo e(route('admin.restaurant.toggle-settings', [$restaurant->id, $restaurant->restaurant_config?->customer_date_order_sratus ? 0 : 1, 'customer_date_order_sratus'])); ?>"
                                    method="get" id="customer_date_order_sratus_form">
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="col-xl-4 col-md-4 col-sm-6">
                        <div class="form-group mb-0">
                            <label
                                class="toggle-switch toggle-switch-sm d-flex justify-content-between border  rounded px-3 form-control"
                                for="halal_tag_status">
                                <span class="pr-2 d-flex">
                                    <span class="line--limit-1">
                                        <?php echo e(translate('messages.halal_tag_status')); ?>

                                    </span>
                                    <span data-toggle="tooltip" data-placement="right"
                                        data-original-title='<?php echo e(translate('If_enabled,_customers_can_see_halal_tag_on_product')); ?>'
                                        class="input-label-secondary">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </span>
                                <input type="checkbox" data-id="halal_tag_status" data-type="status"
                                    data-image-on="<?php echo e(dynamicAsset('assets/admin/img/modal/schedule-on.png')); ?>"
                                    data-image-off="<?php echo e(dynamicAsset('assets/admin/img/modal/schedule-off.png')); ?>"
                                    data-title-on="<?php echo e(translate('Want_to_enable_halal_tag_status_for_this_restaurant?')); ?>"
                                    data-title-off="<?php echo e(translate('Want_to_disable_halal_tag_status_for_this_restaurant?')); ?>"
                                    data-text-on="<p><?php echo e(translate('If_enabled,_customers_can_see_halal_tag_on_product')); ?>"
                                    data-text-off="<p><?php echo e(translate('If_disabled,_customers_can_not_see_halal_tag_on_product.')); ?></p>"
                                    class="toggle-switch-input dynamic-checkbox" id="halal_tag_status"
                                    <?php echo e($restaurant->restaurant_config?->halal_tag_status == 1 ? 'checked' : ''); ?>>
                                <span class="toggle-switch-label">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                            <form
                                action="<?php echo e(route('admin.restaurant.toggle-settings', [$restaurant->id, $restaurant->restaurant_config?->halal_tag_status ? 0 : 1, 'halal_tag_status'])); ?>"
                                method="get" id="halal_tag_status_form">
                            </form>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-4 col-sm-6">
                        <div class="form-group mb-0">
                            <label
                                class="toggle-switch toggle-switch-sm d-flex justify-content-between border  rounded px-3 form-control"
                                for="dine_in">
                                <span class="pr-2 d-flex">
                                    <span class="line--limit-1">
                                        <?php echo e(translate('messages.Dine-In')); ?>

                                    </span>
                                    <span data-toggle="tooltip" data-placement="right"
                                        data-original-title='<?php echo e(translate('When_enabled_customers_can_make_Dine-In_orders_from_this_restaurant.')); ?>'
                                        class="input-label-secondary">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </span>
                                <input type="checkbox" data-id="dine_in" data-type="status"
                                    data-image-on="<?php echo e(dynamicAsset('assets/admin/img/modal/takeaway-on.png')); ?>"
                                    data-image-off="<?php echo e(dynamicAsset('assets/admin/img/modal/takeaway-off.png')); ?>"
                                    data-title-on="<?php echo e(translate('Want_to_enable_Dine-In_for_this_restaurant?')); ?>"
                                    data-title-off="<?php echo e(translate('Want_to_disable_Dine-In_for_this_restaurant?')); ?>"
                                    data-text-on="<p><?php echo e(translate('If_enabled,_customers_can_make_Dine-In_orders_from_this_restaurant.')); ?>"
                                    data-text-off="<p><?php echo e(translate('If_disabled,_customers_can_make_Dine-In_orders_from_this_restaurant.')); ?></p>"
                                    class="toggle-switch-input dynamic-checkbox" id="dine_in"
                                    <?php echo e($restaurant->restaurant_config?->dine_in == 1 ? 'checked' : ''); ?>>
                                <span class="toggle-switch-label">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                            <form
                                action="<?php echo e(route('admin.restaurant.toggle-settings', [$restaurant->id, $restaurant->restaurant_config?->dine_in ? 0 : 1, 'dine_in'])); ?>"
                                method="get" id="dine_in_form">
                            </form>
                        </div>
                    </div>
             

                </div>

                <form action="<?php echo e(route('admin.restaurant.update-settings', [$restaurant['id']])); ?>" method="post"
                    enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="row g-2 mt-4">

                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="input-label text-capitalize"><?php echo e(translate('Restaurant veg/Non-veg type')); ?>


                                    <span data-toggle="tooltip" data-placement="right"
                                        data-original-title='<?php echo e(translate('Set_the_food_type_(veg/nonveg/both)_this_restaurant_can_sell.')); ?>'
                                        class="input-label-secondary">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <div class="resturant-type-group border">
                                    <label class="form-check form--check mr-2 mr-md-4">
                                        <?php ($checked = $restaurant->veg == 1 && $restaurant->non_veg == 0 ? 'checked' : ''); ?>
                                        <input class="form-check-input" type="radio" name="menu" id="check-veg"
                                            <?php echo e($checked); ?> value="veg">
                                        <span class="form-check-label">
                                            <?php echo e(translate('messages.veg')); ?>

                                        </span>
                                    </label>
                                    <label class="form-check form--check mr-2 mr-md-4">
                                        <?php ($checked = $restaurant->veg == 0 && $restaurant->non_veg == 1 ? 'checked' : ''); ?>
                                        <input class="form-check-input" type="radio" name="menu" id="check-non-veg"
                                            <?php echo e($checked); ?> value="non-veg">
                                        <span class="form-check-label">
                                            <?php echo e(translate('messages.non_veg')); ?>

                                        </span>
                                    </label>
                                    <label class="form-check form--check">
                                        <?php ($checked = $restaurant->veg == 1 && $restaurant->non_veg == 1 ? 'checked' : ''); ?>
                                        <input class="form-check-input" type="radio" name="menu" id="check-both"
                                            <?php echo e($checked); ?> value="both">
                                        <span class="form-check-label">
                                            <?php echo e(translate('messages.both')); ?>

                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">

                                <label class="input-label text-capitalize"><?php echo e(translate('restaurant_can_edit_order')); ?>

                                    <span data-toggle="tooltip" data-placement="right"
                                        data-original-title='<?php echo e(translate('If_yes,_restaurants_can_edit_orders.')); ?>'
                                        class="input-label-secondary">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <div
                                    class="resturant-type-group <?php echo e(Helpers::get_business_settings('can_restaurant_edit_order') == 1 ? '' : 'disabled'); ?>  border">
                                    <label class="form-check form--check mr-2 mr-md-4">
                                        <input class="form-check-input" type="radio" name="can_edit_order"
                                            id="can_edit_order"
                                            <?php echo e($restaurant?->restaurant_config?->can_edit_order == 1 ? 'checked' : ''); ?>

                                            value="1">
                                        <span class="form-check-label">
                                            <?php echo e(translate('messages.yes')); ?>

                                        </span>
                                    </label>
                                    <label class="form-check form--check mr-2 mr-md-4">
                                        <input class="form-check-input" type="radio" name="can_edit_order"
                                            id="can_edit_order"
                                            <?php echo e($restaurant?->restaurant_config?->can_edit_order == 0 ? 'checked' : ''); ?>

                                            value="0">
                                        <span class="form-check-label">
                                            <?php echo e(translate('messages.no')); ?>

                                        </span>
                                    </label>

                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-sm-6">
                            <div class="form-group">
                                <label class="input-label text-capitalize"
                                    for="minimum_order"><?php echo e(translate('messages.minimum_order_amount')); ?>


                                    <span data-toggle="tooltip" data-placement="right"
                                        data-original-title='<?php echo e(translate('Specify_the_minimum_order_amount_required_for_customers_when_ordering_from_this_restaurant.')); ?>'
                                        class="input-label-secondary">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <input type="number" id="minimum_order" name="minimum_order" step="0.01"
                                    min="0" max="100000" class="form-control"
                                    placeholder="<?php echo e(translate('messages.Ex:_100')); ?> "
                                    value="<?php echo e($restaurant->minimum_order ?? '0'); ?>">
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4">
                            <label class="input-label text-capitalize"
                                for="minimum_delivery_time"><?php echo e(translate('messages.approx_delivery_time')); ?><span
                                    class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                    data-original-title="<?php echo e(translate('Set_the_approx_delivery_time_to_deliver_an_order.')); ?>">
                                    <i class="tio-info text-gray1 fs-16"></i></span></label>
                            <div class="custom-group-btn form-control">
                                <div class="item flex-sm-grow-1">
                                    <label class="floating-label" for="min"><?php echo e(translate('Min')); ?>:</label>
                                    <input id="minimum_delivery_time" type="number" name="minimum_delivery_time"
                                        class="form-control border-0 p-0 h-100" placeholder="Min: 10"
                                        value="<?php echo e(explode('-', $restaurant->delivery_time)[0]); ?>" data-toggle="tooltip"
                                        data-placement="top"
                                        data-original-title="<?php echo e(translate('messages.minimum_delivery_time')); ?>">
                                </div>
                                <div class="item flex-sm-grow-1">
                                    <label class="floating-label" for="max"><?php echo e(translate('Max')); ?>:</label>
                                    <input type="number" name="maximum_delivery_time"
                                        class="form-control border-0 p-0 h-100" placeholder="Max: 20"
                                        value="<?php echo e(explode('-', $restaurant->delivery_time)[1]); ?>" data-toggle="tooltip"
                                        data-placement="top"
                                        data-original-title="<?php echo e(translate('messages.maximum_delivery_time')); ?>">
                                </div>
                                <div class="item flex-shrink-0">
                                    <select name="delivery_time_type" class="custom-select w-90px border-0"
                                        id="" required>
                                        <?php ($data = explode('-', $restaurant->delivery_time)[2] ?? null); ?>
                                        <option value="min" <?php echo e($data == 'min' ? 'selected' : ''); ?>>
                                            <?php echo e(translate('messages.minutes')); ?></option>
                                        <option value="hours" <?php echo e($data == 'hours' ? 'selected' : ''); ?>>
                                            <?php echo e(translate('messages.hours')); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <?php if($self_delivey == 1): ?>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group">
                                    <label class="input-label text-capitalize"
                                        for="customer_order_date"><?php echo e(translate('Customer_Can_Order_Within')); ?>

                                        (<?php echo e(translate('messages.Days')); ?>)
                                        <span data-toggle="tooltip" data-placement="right"
                                            data-original-title="<?php echo e(translate('Enter_the_number_of_days_customers_can_select_for_scheduled_orders.')); ?>"
                                            class="input-label-secondary"><img
                                                src="<?php echo e(dynamicAsset('assets/admin/img/info-circle.svg')); ?>"
                                                alt="i"></span>
                                    </label>
                                    <input type="number" name="customer_order_date" id="customer_order_date"
                                        <?php echo e($restaurant?->restaurant_config?->customer_date_order_sratus == 1 ? 'required' : 'readonly'); ?>

                                        min="0" max="99999999" class="form-control" placeholder="30"
                                        value="<?php echo e($restaurant?->restaurant_config?->customer_order_date ?? ''); ?>">
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if($restaurant->restaurant_config?->dine_in): ?>
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group mb-0">
                                    <label class="input-label text-capitalize d-flex alig-items-center"
                                        for="schedule_order_slot_duration">
                                        <span class="pr-1 d-flex align-items-center switch--label">
                                            <span class="line--limit-1">
                                                <?php echo e(translate('Minimum Time for Dine-In order')); ?>

                                            </span>
                                            <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="<?php echo e(translate('By_activating_this_feature,_customers_can_choose_their_advance_booking_according_to_a_30-minute_or_1-hour_interval_set_by_the_Admin')); ?>"><img
                                                    src="<?php echo e(dynamicAsset('assets/admin/img/info-circle.svg')); ?>"
                                                    alt="<?php echo e(translate('Time_Interval_for_Scheduled_Delivery')); ?>"></span>
                                        </span>
                                    </label>
                                    <div class="custom-group-btn form-control single">
                                        <div class="item flex-sm-grow-1">
                                            <input type="number" name="schedule_advance_dine_in_booking_duration"
                                                class="form-control border-0 h-100"
                                                id="schedule_advance_dine_in_booking_duration"
                                                value="<?php echo e($restaurant->restaurant_config?->schedule_advance_dine_in_booking_duration ?? 0); ?>"
                                                min="0" max="9999"
                                                <?php echo e($restaurant->restaurant_config?->dine_in == 1 ? 'required' : 'disabled'); ?>>
                                        </div>
                                        <div class="item flex-shrink-0">
                                            <select <?php if(!$restaurant->restaurant_config?->dine_in): echo 'disabled'; endif; ?>
                                                name="schedule_advance_dine_in_booking_duration_time_format"
                                                class="custom-select w-90px border-0">
                                                <option value="min">
                                                    <?php echo e(translate('Min')); ?></option>
                                                <option value="hour"
                                                    <?php echo e($restaurant->restaurant_config?->schedule_advance_dine_in_booking_duration_time_format == 'hour' ? 'selected' : ''); ?>>
                                                    <?php echo e(translate('Hour')); ?></option>
                                                <option value="day"
                                                    <?php echo e($restaurant->restaurant_config?->schedule_advance_dine_in_booking_duration_time_format == 'day' ? 'selected' : ''); ?>>
                                                    <?php echo e(translate('Day')); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>










                    </div>
                    <div class="text-right mt-4">
                        <button type="submit"
                            class="btn btn--primary"><?php echo e(translate('messages.save_changes')); ?></button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Vendor Website Builder -->
        <?php if(addon_published_status('Builder') && Helpers::get_business_settings('admin_website_builder_status') == 1): ?>
            <div class="card card-body mb-3" id="admin_website_builder_section">
                <div class="row g-1 align-items-center">
                    <div class="col-xxl-9 col-lg-8 col-md-7 col-sm-6">
                        <div>
                            <h4 class="mb-1"><?php echo e(translate('Vendor Website Builder')); ?></h4>
                            <p class="mb-0 fs-12">
                                <?php echo e(translate('Enable this option to allow this restaurant to set up and manage their own website.')); ?>

                            </p>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-lg-4 col-md-5 col-sm-6">
                        <div class="form-group mb-0">
                            <label
                                class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0"
                                for="website_builder_status">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    <span class="line--limit-1"><?php echo e(translate('messages.Status')); ?></span>
                                </span>
                                <input type="checkbox" data-id="website_builder_status" data-type="status"
                                    data-image-on="<?php echo e(dynamicAsset('assets/admin/img/modal/store-reg-on.png')); ?>"
                                    data-image-off="<?php echo e(dynamicAsset('assets/admin/img/modal/store-reg-off.png')); ?>"
                                    data-title-on="<strong><?php echo e(translate('Are you sure to enable website setup for this restaurant?')); ?></strong>"
                                    data-title-off="<strong><?php echo e(translate('Are you sure to disable website setup for this restaurant?')); ?></strong>"
                                    data-text-on="<p><?php echo e(translate('If enabled, this restaurant will have the freedom to create, edit, and manage their own website independently.')); ?></p>"
                                    data-text-off="<p><?php echo e(translate('If disabled, this restaurant will not be able to create or manage their own website.')); ?></p>"
                                    class="status toggle-switch-input dynamic-checkbox" value="1"
                                    name="website_builder_status" id="website_builder_status"
                                    <?php echo e($restaurant->restaurant_config?->website_builder_status == 1 ? 'checked' : ''); ?>>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                            <form
                                action="<?php echo e(route('admin.restaurant.toggle-settings', [$restaurant->id, $restaurant->restaurant_config?->website_builder_status ? 0 : 1, 'website_builder_status'])); ?>"
                                method="get" id="website_builder_status_form">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($__env->exists('vendor-views.business-settings.partials._restaurant_schedule_data', $restaurant)) echo $__env->make('vendor-views.business-settings.partials._restaurant_schedule_data', $restaurant, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


    </div>

    <!-- Create schedule modal -->

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <script>
        "use strict";





        $(document).on('click', '.delete-schedule', function() {
            let route = $(this).data('url');
            Swal.fire({
                title: '<?php echo e(translate('messages.Want_to_delete_this_day’s_schedule')); ?>',
                text: '<?php echo e(translate('messages.If_yes,_the_schedule_will_be_removed_from_here._However,_you_can_also_add_another_one.')); ?>',
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#377dff',
                cancelButtonText: '<?php echo e(translate('messages.no')); ?>',
                confirmButtonText: '<?php echo e(translate('messages.yes')); ?>',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.get({
                        url: route,
                        beforeSend: function() {
                            $('#loading').show();
                        },
                        success: function(data) {
                            if (data.errors) {
                                for (let i = 0; i < data.errors.length; i++) {
                                    toastr.error(data.errors[i].message, {
                                        CloseButton: true,
                                        ProgressBar: true
                                    });
                                }
                            } else {
                                $('#schedule').empty().html(data.view);
                                applySameTimeUI();
                                toastr.success(
                                    '<?php echo e(translate('messages.Schedule removed successfully')); ?>', {
                                        CloseButton: true,
                                        ProgressBar: true
                                    });
                            }
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            toastr.error('<?php echo e(translate('messages.Schedule not found')); ?>', {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        },
                        complete: function() {
                            $('#loading').hide();
                        },
                    });
                }
            })
        });



        $(document).on('click', '.offcanvas-trigger', function(e) {
            e.preventDefault();

            let day_name = $(this).data('day');
            let day_id = $(this).data('dayid');

            const $offcanvas = $('#offcanvasAddSchedule');

            $offcanvas.find('.custom-offcanvas-header h3')
                .text("<?php echo e(translate('messages.Create Schedule For ')); ?>" + day_name);

            $('#add-schedule').find('#day_id_input').val(day_id);

            $('#offcanvasAddSchedule').addClass('show open');
            $('#offcanvasOverlay').addClass('show');
        });




        $('#add-schedule').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '<?php echo e(route('admin.restaurant.add-schedule')); ?>',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        $('#schedule').empty().html(data.view);
                        applySameTimeUI();
                        $('#offcanvasAddSchedule').removeClass('show open');
                        $('#offcanvasOverlay').removeClass('show');
                        toastr.success('<?php echo e(translate('messages.Schedule added successfully')); ?>', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    toastr.error(XMLHttpRequest.responseText, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                complete: function() {
                    $('#loading').hide();
                },
            });
        });




        (function() {
            "use strict";
            if (window.__oc_final_bind) return;
            window.__oc_final_bind = true;

            function toggleScheduleSection() {
                if ($("#always_open").is(":checked")) $(".schedule_section").addClass("d-none");
                else $(".schedule_section").removeClass("d-none");
            }

            function saveOC() {
                const opening_closing_status = $("#always_open").is(":checked") ? 1 : 0;
                const same_time_for_every_day = $("#same_time_for_every_day").is(":checked") ? 1 : 0;

                toggleScheduleSection();
                if (typeof applySameTimeUI === "function") applySameTimeUI();

                $.ajax({
                    url: "<?php echo e(route('admin.restaurant.update-opening-closing-status', [$restaurant['id']])); ?>",
                    method: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        opening_closing_status,
                        same_time_for_every_day
                    },
                    beforeSend: () => {
                        $("#loading").show();
                    },

                    success: (data) => {
                        $('#schedule').empty().html(data.view);
                        applySameTimeUI();
                        toastr.success("Updated successfully", {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    },

                    error: (xhr) => {
                        toastr.error(xhr.responseText || "Something went wrong", {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    },

                    complete: () => {
                        $("#loading").hide();
                    }

                });
            }

            $(document).ready(function() {
                toggleScheduleSection();
                if (typeof applySameTimeUI === "function") applySameTimeUI();
            });

            document.addEventListener("click", function(e) {
                const $wrap = $(e.target).closest('label.toggle-switch');
                if (!$wrap.length) return;

                const $input = $wrap.find(
                    'input[type="checkbox"]#always_open, input[type="checkbox"]#same_time_for_every_day');
                if (!$input.length) return;

                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                const prev = $input.prop("checked");
                const next = !prev;

                const title = next ? ($input.data("title-on") || "Are you sure?") : ($input.data("title-off") ||
                    "Are you sure?");
                const html = next ? ($input.data("text-on") || "") : ($input.data("text-off") || "");
                const imageUrl = next ? ($input.data("image-on") || "") : ($input.data("image-off") || "");

                Swal.fire({
                    title: title,
                    html: html,
                    imageUrl: imageUrl || undefined,
                    showCancelButton: true,
                    confirmButtonText: "<?php echo e(translate('messages.yes')); ?>",
                    cancelButtonText: "<?php echo e(translate('messages.no')); ?>",
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        $input.prop("checked", next);

                        saveOC();
                    } else {
                        $input.prop("checked", prev);
                        toggleScheduleSection();
                        if (typeof applySameTimeUI === "function") applySameTimeUI();
                    }
                });

            }, true);
        })();



        function applySameTimeUI() {
            const isSame = $('#same_time_for_every_day').is(':checked');

            $('.schedule-item').each(function() {
                const day = parseInt($(this).data('day'), 10);

                if (isSame && day !== 1) {
                    $(this).find('.offcanvas-trigger')
                        .addClass('disabled')
                        .css({
                            'pointer-events': 'none',
                            'opacity': '.4'
                        });

                    $(this).find('.delete-schedule')
                        .addClass('disabled')
                        .css({
                            'pointer-events': 'none',
                            'opacity': '.4'
                        });

                    $(this).addClass('opacity-75');
                } else {
                    $(this).find('.offcanvas-trigger')
                        .removeClass('disabled')
                        .css({
                            'pointer-events': 'auto',
                            'opacity': ''
                        });

                    $(this).find('.delete-schedule')
                        .removeClass('disabled')
                        .css({
                            'pointer-events': 'auto',
                            'opacity': ''
                        });

                    $(this).removeClass('opacity-75');
                }
            });
        }
        $(document).ready(function() {
            applySameTimeUI();
        });
        $(document).on('change', '#same_time_for_every_day', function() {
            applySameTimeUI();
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/vendor/view/settings.blade.php ENDPATH**/ ?>