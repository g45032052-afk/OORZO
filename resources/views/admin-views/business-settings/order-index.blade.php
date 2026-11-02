@extends('layouts.admin.app')

@section('title', translate('Order_Settings'))


@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header pb-0">
        <div class="d-flex flex-wrap justify-content-between align-items-start">
            <h1 class="mb-0">{{ translate('messages.business_setup') }}</h1>
            <div class="d-flex flex-wrap justify-content-end align-items-center flex-grow-1">
                <div class="blinkings active">
                    <i class="tio-info text-gray1 fs-16"></i>
                    <div class="business-notes">
                        <h6><img src="{{dynamicAsset('assets/admin/img/notes.png')}}" alt="">
                            {{translate('Note')}}</h6>
                        <div>
                            {{translate('Don’t_forget_to_click_the_respective_‘Save_Information’_and_‘Submit’_buttons_below_to_save_changes')}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('admin-views.business-settings.partials.nav-menu')
    </div>

    <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info mb-20" style="--bs-bg-opacity: 0.1;">
        <span class="text-info lh-1 fs-14 flex-shrink-0">
            <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
        </span>
        <span>
            {{ translate('messages.All order you can show & manage them from') }}
            <a href="{{ route('admin.order.list',['status'=>'all']) }}" class="font-semibold text-primary text-underline">{{ translate('messages.All Orders') }} </a>
            {{ translate('messages.page.') }}
        </span>
    </div>

    <form method="post" action="{{ route('admin.business-settings.update-order') }}" id="orders_setup_section">
        @csrf
        <div class="card card-body mb-3" id="order_type_section">
            <div class="mb-20">
                <h4 class="mb-1">{{ translate('messages.Order_Type') }}</h4>
                <p class="fs-12 mb-0">{{ translate('messages.Which way customer order their food') }}</p>
            </div>
            <div class="bg-light rounded-10 p-4 mb-20">
                <div class="row g-3 border rounded bg-white">
                    <div class="col-lg-4">
                        <div class="custom-checkbox custom-control d-flex gap-2 h-100">
                            <input type="checkbox" class="custom-control-input" value="1" name="home_delivery"
                                id='home_delivery' {{ $settings['home_delivery'] ? 'checked' : '' }}>
                            <label class="custom-control-label d-flex flex-column justify-content-between mb-0"
                                for="home_delivery">
                                <div>
                                    <h5 class="mb-1">
                                        {{ translate('messages.Home Delivery') }}
                                    </h5>
                                    <p class="fs-12 mb-0">
                                        {{ translate('messages.If enabled customers can choose Home Delivery option from the customer app and website') }}
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="custom-checkbox custom-control d-flex gap-2 h-100">
                            <input type="checkbox" class="custom-control-input" value="1" name="take_away"
                                id='take_away' {{ $settings['take_away'] ? 'checked' : '' }}>
                            <label class="custom-control-label d-flex flex-column justify-content-between mb-0"
                                for="take_away">
                                <div>
                                    <h5 class="mb-1">
                                        {{ translate('messages.Takeaway') }}
                                    </h5>
                                    <p class="fs-12 mb-0">
                                        {{ translate('messages.If enabled customers can use Takeaway feature during checkout from the Customer App/Website.') }}
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="custom-checkbox custom-control d-flex gap-2 h-100">
                            <input type="checkbox" class="custom-control-input" value="1" name="dine_in_order_option"
                                id='dine_in_order_option' {{ data_get($settings, 'dine_in_order_option', 0) == 1 ? 'checked' : '' }}>
                            <label class="custom-control-label d-flex flex-column justify-content-between mb-0"
                                for="dine_in_order_option">
                                <div>
                                    <h5 class="mb-1">
                                        {{ translate('messages.Dine In') }}
                                    </h5>
                                    <p class="fs-12 mb-0">
                                        {{ translate('messages.If enabled customer can choose Dine-in option for order from customer App/Website') }}
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-warning" style="--bs-bg-opacity: 0.1;">
                <span class="text-warning lh-1 fs-14 flex-shrink-0">
                    <i class="tio-info"></i>
                </span>
                <span>
                    {{ translate('messages.All the options can\'t be turn Off at a time. At least one option must be active to work your order system properly.') }}
                </span>
            </div>
        </div>
        <div class="card card-body mb-3" id="regular_order_section">
            <div class="mb-20">
                <h4 class="mb-1">{{ translate('messages.Regular_Order') }}</h4>
                <p class="fs-12 mb-0">{{ translate('messages.Setup your business time zone and format from here') }}</p>
            </div>
            <div class="bg-light rounded-10 p-4 mb-20">
                <div class="row g-3 border rounded bg-white">
                    <div class="col-lg-6">
                        <div class="custom-checkbox custom-control d-flex gap-2 h-100">
                            <input type="checkbox" class="custom-control-input" value="1" name="instant_order"
                                id='instant_order' {{ $settings['instant_order'] == 1 ? 'checked' : '' }}>
                            <label class="custom-control-label d-flex flex-column justify-content-between mb-0"
                                for="instant_order">
                                <div>
                                    <h5 class="mb-1">
                                        {{ translate('messages.Instant Order') }}
                                    </h5>
                                    <p class="fs-12 mb-0">
                                        {{ translate('messages.If enabled customer can choose to place order instantly from Customer App/Website') }}
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="custom-checkbox custom-control d-flex gap-2 h-100">
                            <input type="checkbox" class="custom-control-input" value="1"
                                name="schedule_order" id='schedule_order' {{ $settings['schedule_order'] == 1  ? 'checked' : '' }}>
                            <label class="custom-control-label d-flex flex-column justify-content-between mb-0"
                                for="schedule_order">
                                <div>
                                    <h5 class="mb-1">
                                        {{ translate('messages.Scheduled Order') }}
                                    </h5>
                                    <p class="fs-12 mb-0">
                                        {{ translate('messages.If Enabled, customer can choose to order place in their preferable time from Customer App/Website') }}
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div id="schedule_order_slot_duration_section">
                @php($schedule_order_slot_duration = $settings['schedule_order_slot_duration'] ?? 0)
                @php($schedule_order_slot_duration_time_formate = $settings['schedule_order_slot_duration_time_formate'] ?? 'min')
                <div class="form-group mb-0 d-flex">
                    <div class="col-lg-4">
                            <label class="input-label d-flex align-items-center gap-1" for="schedule_order_slot_duration">
                            {{ translate('Time_Interval_for_Scheduled_Delivery') }}
                            <span class="tio-info text-gray1 fs-16" data-toggle="tooltip" data-placement="right"
                                data-original-title="{{ translate('By_activating_this_feature,_customers_can_choose_their_suitable_delivery_slot_according_to_a_30-minute_or_1-hour_interval_set_by_the_Admin') }}">
                            </span>
                        </label>

                        <div class="custom-group-btn form-control single">
                            <div class="item flex-sm-grow-1">
                                <input type="number" name="schedule_order_slot_duration" class="form-control border-0 h-100"
                                    id="schedule_order_slot_duration"
                                    value="{{ $schedule_order_slot_duration ? ($schedule_order_slot_duration_time_formate == 'hour' ? $schedule_order_slot_duration / 60 : $schedule_order_slot_duration) : 0 }}"
                                    min="0" required>
                            </div>
                            <div class="item flex-shrink-0">
                                <select name="schedule_order_slot_duration_time_formate"
                                    class="custom-select w-90px border-0">
                                    <option value="min" {{ $schedule_order_slot_duration_time_formate == 'min' ? 'selected' : '' }}>
                                        {{ translate('Min') }}
                                    </option>
                                    <option value="hour" {{ $schedule_order_slot_duration_time_formate == 'hour' ? 'selected' : '' }}>
                                        {{ translate('Hour') }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group mb-0">
                            @php($customer_date_order_sratus = $settings['customer_date_order_sratus'] ?? 0)
                            <label class="input-label d-flex align-items-center gap-1" for="customer_date_order_sratus">
                                {{ translate('messages.Custom Date Order') }}
                                <span class="tio-info text-gray1 fs-16" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('This_applies_to_Dine_In_orders_only._With_it_on,_a_customer_booking_a_dine_in_slot_can_not_pick_a_date_and_time_beyond_the_given_number_of_days.') }}">
                                </span>
                            </label>

                            <label
                                class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control"
                                data-toggle="modal" data-target="#repeat-order">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    {{ translate('messages.Status') }}
                                </span>
                                <input type="checkbox" data-id="customer_date_order_sratus" data-type="toggle"
                                    data-image-on="{{ dynamicAsset('assets/admin/img/modal/schedule-on.png') }}"
                                    data-image-off="{{ dynamicAsset('assets/admin/img/modal/schedule-off.png') }}"
                                    data-title-on="{{ translate('Want_to_enable') }} <strong>{{ translate('custom_date_order_status') }}</strong>?"
                                    data-title-off="{{ translate('Want_to_disable') }} <strong>{{ translate('custom_date_order_status') }}</strong>?"
                                    data-text-on="<p>{{ translate('If_enabled,_a_customer_booking_a_Dine_In_slot_can_not_pick_a_date_and_time_beyond_the_given_number_of_days.') }}</p>"
                                    data-text-off="<p>{{ translate('If_disabled,_a_customer_booking_a_Dine_In_slot_can_pick_any_date_and_time.') }}</p>"
                                    class="toggle-switch-input dynamic-checkbox-toggle" value="1"
                                    name="customer_date_order_sratus" id="customer_date_order_sratus" {{ $customer_date_order_sratus == 1 ? 'checked' : '' }}>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        @php($customer_order_date = $settings['customer_order_date'] ?? 0)
                        <div class="form-group mb-0">
                            <label class="input-label d-flex align-items-center gap-1" for="customer_order_date">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    <span class="line--limit-1">
                                        {{ translate('Customer_Can_Order_Within') }}
                                        ({{ translate('messages.Days') }})
                                    </span>
                                    <span class="text-danger">*</span>
                                    <span class="tio-info text-gray1 fs-16" data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ translate('Dine_In_only:_a_customer_can_not_book_a_slot_further_ahead_than_this_many_days.') }}">
                                    </span>
                                </span>
                            </label>
                            <div class="d-flex">
                                <input type="number" name="customer_order_date" class="form-control"
                                    id="customer_order_date" {{ $customer_date_order_sratus == 1 ? 'required' : 'readonly' }} value="{{ $customer_order_date }}" min="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @php($order_subscription = $settings['order_subscription'] ?? 0)
        <div class="card card-body mb-3" id="subscription_order_section">
            <div class="mb-0 d-flex justify-content-between">
                <div class="">
                    <h4 class="mb-1">{{ translate('messages.Subscription_Order') }}</h4>
                <p class="fs-12 mb-0">
                    {{ translate('messages.Here you can manage the necessary settings for subscription order booking') }}
                </p>
                </div>
                <div class="col-lg-3">
                <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control"
                                data-toggle="modal" data-target="#repeat-order">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    {{ translate('messages.Status') }}
                                </span>
                                <input type="checkbox" id="subscription_order" name="order_subscription" value="1"
                                    data-id="subscription_order" data-type="toggle"
                                    data-image-on="{{ dynamicAsset('assets/admin/img/modal/home-delivery-on.png') }}"
                                    data-image-off="{{ dynamicAsset('assets/admin/img/modal/home-delivery-off.png') }}"
                                    data-title-on="{{ translate('want_to_enable') }} <strong>{{ translate('messages.Subscription_Order') }}</strong> {{ translate('feature') }}?"
                                    data-title-off="{{ translate('want_to_disable') }} <strong>{{ translate('messages.Subscription_Order') }}</strong> {{ translate('feature') }}?"
                                    data-text-on="<p>{{ translate('if_enabled,customers_can_order_food_on_a_subscription_basis._customers_can_select_time_with_the_delivery_slot_from_the_calendar_to_their_preferences') }}</p>"
                                    data-text-off="<p>{{ translate('if_disabled,customers_won’t_be_able_to_order_food_on_a_subscription-based') }}</p>"
                                    class="toggle-switch-input dynamic-checkbox-toggle" {{ $order_subscription == 1 ? 'checked' : '' }}>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                </label>
                </div>
            </div>
        </div>
        <div class="card card-body mb-3">
            <div class="mb-20">
                <h4 class="mb-1">{{ translate('messages.Notification_Setup') }}</h4>
                <p class="fs-12 mb-0">
                    {{ translate('messages.Here you can manage the notification settings for this panel') }}
                </p>
            </div>
            <div class="bg-light rounded-10 p-4 mb-20">
                <div class="row g-3">
                    <div class="col-lg-4">
                        <div class="form-group mb-0">
                            <label class="input-label d-flex align-items-center gap-1" for="">
                                {{ translate('messages.Order Notification for Admin') }}
                                <span class="tio-info text-gray1 fs-16" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('Admin will get a pop-up notification with sounds for every order placed by customers') }}">
                                </span>
                            </label>

                            <label
                                class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control"
                                data-toggle="modal" data-target="#toggle-modal">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    {{ translate('messages.Status') }}
                                </span>
                                <input type="checkbox" id="admin_order_notification" name="admin_order_notification"
                                    value="1" data-id="admin_order_notification" data-type="toggle"
                                    data-image-on="{{ dynamicAsset('assets/admin/img/modal/home-delivery-on.png') }}"
                                    data-image-off="{{ dynamicAsset('assets/admin/img/modal/home-delivery-off.png') }}"
                                    data-title-on="{{ translate('want_to_enable') }} <strong>{{ translate('admin_order_notification') }}</strong>?"
                                    data-title-off="{{ translate('want_to_disable') }} <strong>{{ translate('admin_order_notification') }}</strong>?"
                                    data-text-on="<p>{{ translate('If_enabled,_Admin_will_receive_notification_for_every_order_placed.') }}</p>"
                                    data-text-off="<p>{{ translate('If_disabled,_Admin_will_NOT_receive_notification_for_orders.') }}</p>"
                                    class="toggle-switch-input dynamic-checkbox-toggle" {{ $settings['admin_order_notification'] == '1' ? 'checked' : '' }}>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group mb-0">
                            <label class="input-label d-flex align-items-center gap-1" for="">
                                {{ translate('messages.Order Notification Type') }}
                                <span class="tio-info text-gray1 fs-16" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('For Firebase a single real-time notification will be sent upon order placement with no repetition. For the Manual option notifications will appear at 10-second intervals until the order is viewed.') }}">
                                </span>
                            </label>
                            <div class="resturant-type-group border">
                                <label class="form-check form--check mr-2 mr-md-4">
                                    <input class="form-check-input" type="radio" value="firebase"
                                        name="order_notification_type" id="" {{ $settings['order_notification_type'] == 'firebase' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{ translate('messages.Firebase') }}
                                    </span>
                                </label>
                                <label class="form-check form--check mr-2 mr-md-4">
                                    <input class="form-check-input" type="radio" value="manual"
                                        name="order_notification_type" id="" {{ $settings['order_notification_type'] == 'manual' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{ translate('messages.Manual') }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="fs-12 text-dark px-3 py-2 rounded bg-warning" style="--bs-bg-opacity: 0.1;">
                <div class="d-flex gap-2 ">
                    <span class="text-warning lh-1 fs-14 flex-shrink-0">
                        <i class="tio-info"></i>
                    </span>
                    <spa class="font-semibold">
                        {{ translate('messages.To work properly the order notification') }}
                    </spa>
                </div>
                <ul>
                    <li>
                        {{ translate('messages.When you select Firebase, the notification will send automatically & you must setup Firebase from ') }} <a href="{{ route('admin.business-settings.fcm-index') }}" class="font-semibold text-underline">{{ translate('messages.Firebase Configuration') }}</a>
                    </li>
                </ul>
            </div>
        </div>
         <div class="card card-body mb-3">
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="">
                        <h4 class="mb-1 d-flex align-items-center gap-1">
                            {{ translate('messages.Re-order Feature') }}
                            <span class="tio-info text-gray1 fs-16" data-toggle="tooltip"
                                title="{{ translate('messages.This feature appears on both the homepage and the restaurant details page.') }}">
                            </span>
                        </h4>
                        <p class="fs-12 mb-0">{{ translate('messages.Allow customers to quickly reorder previously purchased items with a single tap') }}.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <label
                        class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0">
                        <span class="pr-1 d-flex align-items-center switch--label">
                            {{ translate('messages.Status') }}
                        </span>
                         <input type="checkbox" id="repeat_order_option" name="repeat_order_option" value="1"
                                    data-id="repeat_order_option" data-type="toggle"
                                    data-image-on="{{ dynamicAsset('assets/admin/img/modal/home-delivery-on.png') }}"
                                    data-image-off="{{ dynamicAsset('assets/admin/img/modal/home-delivery-off.png') }}"
                                    data-title-on="{{ translate('want_to_enable') }} <strong>{{ translate('re_order_option') }}</strong>?"
                                    data-title-off="{{ translate('want_to_disable') }} <strong>{{ translate('re_order_option') }}</strong>?"
                                    data-text-on="<p>{{ translate('If_enabled,_customers_can_re-order_foods_from_their_previous_orders.') }}</p>"
                                    data-text-off="<p>{{ translate('If_disabled,_customers_will_not_be_able_to_re-order_from_previous_orders.') }}</p>"
                                    class="toggle-switch-input dynamic-checkbox-toggle" {{ $settings['repeat_order_option'] ? 'checked' : '' }}>
                        <span class="toggle-switch-label text">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                </div>
            </div>
        </div>
        <div class="card card-body mb-3" id="others_setup_section">
            <div class="mb-20">
                <h4 class="mb-1">{{ translate('messages.Others Setup') }}</h4>
                <p class="fs-12 mb-0">
                    {{ translate('messages.Here you can manage other necessary settings related to Orders') }}
                </p>
            </div>
            <div class="bg-light rounded-10 p-4">
                <div class="row g-3">

                    <div class="col-lg-4">
                        <div class="form-group mb-0">
                            <label class="input-label d-flex align-items-center gap-1" for="">
                                {{ translate('can_restaurant_edit_order') }}
                                <span class="tio-info text-gray1 fs-16" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('If_enabled,_customers_can_re-order_foods_from_their_previous_orders.') }}">
                                </span>
                            </label>

                            <label
                                class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control"
                                data-toggle="modal" data-target="#toggle-modal">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    {{ translate('messages.Status') }}
                                </span>
                                <input type="checkbox" id="can_restaurant_edit_order" name="can_restaurant_edit_order" value="1"
                                    data-id="can_restaurant_edit_order" data-type="toggle"
                                    data-image-on="{{ dynamicAsset('assets/admin/img/modal/home-delivery-on.png') }}"
                                    data-image-off="{{ dynamicAsset('assets/admin/img/modal/home-delivery-off.png') }}"
                                    data-title-on="{{ translate('want_to_enable') }} <strong>{{ translate('edit_order_option_for_Restaurant') }}</strong>?"
                                    data-title-off="{{ translate('want_to_disable') }} <strong>{{ translate('edit_order_option_for_Restaurant') }}</strong>?"
                                    data-text-on="<p>{{ translate('If_enabled,_Rerstaurants_can_edit_their_own_orders.') }}</p>"
                                    data-text-off="<p>{{ translate('If_disabled,_Rerstaurants_will_not_be_able_to_edit_their_own_orders.') }}</p>"
                                    class="toggle-switch-input dynamic-checkbox-toggle" {{ data_get($settings, 'can_restaurant_edit_order', 0)  == 1 ? 'checked' : '' }}>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group mb-0">
                            <label class="input-label d-flex align-items-center gap-1" for="">
                                {{ translate('messages.Order delivery verification') }}
                                <span class="tio-info text-gray1 fs-16" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('When_a_deliveryman_arrives_for_delivery,_Customers_will_get_a_verification_code_on_the_order_details_section_in_the_Customer_App_and_needs_to_provide_the_code_to_the_delivery_man_to_verify_the_order_delivery') }}">
                                </span>
                            </label>

                            <label
                                class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control"
                                data-toggle="modal" data-target="#toggle-modal">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    {{ translate('messages.Status') }}
                                </span>
                                <input type="checkbox" id="order_delivery_verification" name="order_delivery_verification" value="1"
                                    data-id="order_delivery_verification" data-type="toggle"
                                    data-image-on="{{ dynamicAsset('assets/admin/img/modal/home-delivery-on.png') }}"
                                    data-image-off="{{ dynamicAsset('assets/admin/img/modal/home-delivery-off.png') }}"
                                    data-title-on="{{ translate('want_to_enable') }} <strong>{{ translate('order_delivery_verification') }}</strong>?"
                                    data-title-off="{{ translate('want_to_disable') }} <strong>{{ translate('order_delivery_verification') }}</strong>?"
                                    data-text-on="<p>{{ translate('If_enabled,_customers_will_need_to_provide_a_verification_code_to_the_delivery_man.') }}</p>"
                                    data-text-off="<p>{{ translate('If_disabled,_no_verification_code_will_be_required_for_delivery.') }}</p>"
                                    class="toggle-switch-input dynamic-checkbox-toggle" {{ $settings['order_delivery_verification'] ? 'checked' : '' }}>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        @php($order_confirmation_model = $settings['order_confirmation_model'] ?? 'deliveryman')
                        <div class="form-group mb-0">
                            <label class="input-label d-flex align-items-center gap-1" for="">
                                {{ translate('messages.order_confirmation_model') }}
                                <span class="tio-info text-gray1 fs-16" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('The_chosen_confirmation_model_will_confirm_the_order_first._For_example,_if_the_deliveryman_confirmation_model_is_enabled,_deliverymen_will_receive_and_confirm_orders_before_restaurants._After_that,_restaurants_will_get_orders_and_process_them.') }}">
                                </span>
                            </label>
                            <div class="resturant-type-group border bg-white">
                                <label class="form-check form--check mr-2 mr-md-4">
                                    <input class="form-check-input" type="radio" value="restaurant"
                                        name="order_confirmation_model" id="order_confirmation_model" {{ $order_confirmation_model == 'restaurant' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{ translate('messages.restaurant') }}
                                    </span>
                                </label>
                                <label class="form-check form--check mr-2 mr-md-4">
                                    <input class="form-check-input" type="radio" value="deliveryman"
                                        name="order_confirmation_model" id="order_confirmation_model2" {{ $order_confirmation_model == 'deliveryman' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{ translate('messages.deliveryman') }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group mb-0">
                            <label class="input-label d-flex align-items-center gap-1" for="">
                                {{ translate('messages.Who can Cancel order') }}
                                <span class="tio-info text-gray1 fs-16" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('Choose who has permission to cancel an order in your system.') }}">
                                </span>
                            </label>
                            <div class="resturant-type-group border bg-white">
                                <div class="custom-checkbox custom-control d-flex gap-2 flex-grow-1">
                                    <input type="checkbox" class="custom-control-input" value="canceled_by_restaurant" name="canceled_by_restaurant" id="demo1"
                                        {{ $settings['canceled_by_restaurant'] ? 'checked' : '' }}>
                                    <label class="custom-control-label mb-0" for="demo1">
                                        {{ translate('messages.Restaurant') }}
                                    </label>
                                </div>
                                <div class="custom-checkbox custom-control d-flex gap-2 flex-grow-1">
                                    <input type="checkbox" class="custom-control-input" value="canceled_by_deliveryman" name="canceled_by_deliveryman" id="demo2"
                                        {{ $settings['canceled_by_deliveryman'] ? 'checked' : '' }}>
                                    <label class="custom-control-label mb-0" for="demo2">
                                        {{ translate('messages.Deliveryman') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-sticky mt-2">
                <div class="container-fluid">
                <div class="d-flex flex-wrap gap-3 justify-content-center py-3">
                    <button type="reset" id="reset_btn" class="btn btn--secondary min-w-120 location-reload">{{ translate('messages.Reset') }} </button>
                    <button type="{{ getEnvMode() != 'demo' ? 'submit' : 'button' }}" class="btn btn--primary call-demo">
                        <i class="tio-save"></i>
                        {{ translate('Save_Information') }}
                    </button>
                </div>
                </div>
            </div>

    </form>


    <div class="mt-4" id="order_cancellation_messages_section">
        <div class="card">
            <div class="card-body">
                <div class="mb-20">
                    <h4 class="mb-1">{{ translate('messages.Setup Order Cancellation Messages') }}</h4>
                    <p class="fs-12 mb-0">
                        {{ translate('messages.Users cannot cancel an order if the Admin does not specify a cause for cancellation even though they see the ‘Cancel Order‘ option. So Admin MUST provide a proper Order Cancellation Reason and select the related user.') }}
                    </p>
                </div>

                <div class="bg-light rounded-10 p-12 p-xxl-20 mb-20">
                    <form action="{{ route('admin.order-cancel-reasons.store') }}" method="post">
                        @csrf
                        @if ($language)
                            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                <ul class="nav nav-tabs nav--tabs mb-3 border-0">
                                    <li class="nav-item">
                                        <a class="nav-link lang_link1 active" href="#"
                                            id="default-link1">{{ translate('Default') }}</a>
                                    </li>
                                    @foreach ($language as $lang)
                                        <li class="nav-item">
                                            <a class="nav-link lang_link1" href="#"
                                                id="{{ $lang }}-link1">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="row g-3">
                            <div class="col-lg-6 lang_form1 default-form1">
                                <label for="order_cancellation" class="input-label d-flex align-items-center gap-1">
                                    {{ translate('Order Cancellation Reason') }}
                                    ({{ translate('messages.default') }})
                                    <span class="tio-info text-gray1 fs-16" data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ translate('messages.*Users_cannot_cancel_an_order_if_the_Admin_does_not_specify_a_cause_for_cancellation,_even_though_they_see_the_‘Cancel_Order‘_option._So_Admin_MUST_provide_a_proper_Order_Cancellation_Reason_and_select_the_related_user.') }}">
                                    </span>
                                </label>
                                <input type="text" maxlength="191" class="form-control h--45px" name="reason[]"
                                    id="order_cancellation" placeholder="{{ translate('Ex:_Item_is_Broken') }}">
                                <input type="hidden" name="lang[]" value="default">
                            </div>
                            @if ($language)
                                @foreach ($language as $lang)
                                    <div class="col-lg-6 d-none lang_form1" id="{{ $lang }}-form1">
                                        <label for="order_cancellation{{ $lang }}"
                                            class="input-label d-flex align-items-center gap-1">
                                            {{ translate('Order Cancellation Reason') }}
                                            ({{ strtoupper($lang) }})
                                            <span class="tio-info text-gray1 fs-16" data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ translate('messages.*Users_cannot_cancel_an_order_if_the_Admin_does_not_specify_a_cause_for_cancellation,_even_though_they_see_the_‘Cancel_Order‘_option._So_Admin_MUST_provide_a_proper_Order_Cancellation_Reason_and_select_the_related_user.') }}">
                                            </span>
                                        </label>
                                        <input type="text" class="form-control h--45px" maxlength="191" name="reason[]"
                                            id="order_cancellation{{ $lang }}"
                                            placeholder="{{ translate('Ex:_Item_is_Broken') }}">
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                    </div>
                                @endforeach
                            @endif
                            <div class="col-lg-6">
                                <label for="user_type" class="input-label d-flex align-items-center gap-1">
                                    {{ translate('Order Cancellation Reason') }}
                                    ({{ translate('messages.User Type') }})
                                    <span class="tio-info text-gray1 fs-16" data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ translate('messages.Choose_different_Customers_for_different_Order_Cancelation_Reasons,_such_as_Customer,_Restaurant,_Deliveryman,_and_Admin') }}">
                                    </span>
                                </label>
                                <select id="user_type" name="user_type" class="form-control h--45px" required>
                                    <option value="">{{ translate('messages.select_user_type') }}</option>
                                    <option value="admin">{{ translate('messages.admin') }}</option>
                                    <option value="restaurant">{{ translate('messages.restaurant') }}</option>
                                    <option value="customer">{{ translate('messages.customer') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="btn--container justify-content-end mt-3">
                            <button type="reset"
                                class="btn btn--reset min-w-120">{{ translate('messages.reset') }}</button>
                            <button type="{{ getEnvMode() != 'demo' ? 'submit' : 'button' }}"
                                class="btn btn--primary min-w-120 call-demo">{{ translate('save') }}</button>
                        </div>
                    </form>
                </div>

                <div class="card card-body">
                    <div class="d-flex flex-wrap gap-3 justify-content-end align-items-center mb-3">
                        <h4 class="mb-0 flex-grow-1">
                            {{translate('messages.order_cancellation_reason_list')}}
                        </h4>
                        <form class="d-flex gap-3" action="{{ route('admin.business-settings.business-setup',  ['tab' => 'order']) }}">
                        <select name="user_type" class="form-control max-w-187px filter-user-type">
                            <option class="text-muted" value="" disabled>
                                {{ translate('messages.Select User Type') }}
                            </option>
                            <option {{ request()->user_type == '' ? 'selected' : '' }} value="">
                                {{ translate('messages.All') }}
                            </option>
                            <option {{ request()->user_type == 'admin' ? 'selected' : '' }} value="admin">
                                {{ translate('messages.admin') }}
                            </option>
                            <option {{ request()->user_type == 'customer' ? 'selected' : '' }} value="customer">
                                {{ translate('messages.customer') }}
                            </option>
                            <option {{ request()->user_type == 'restaurant' ? 'selected' : '' }} value="restaurant">
                                {{ translate('messages.restaurant') }}
                            </option>
                        </select>
                        <div class="input--group input-group input-group-merge input-group-flush w-18rem">
                            <input type="search" name="search" value="{{ request()?->search ?? null }}"
                                class="form-control" placeholder="{{ translate('messages.Search Here') }}"
                                aria-label="{{translate('messages.Search_Here')}}">
                            <button type="submit" class="btn btn--secondary secondary-cmn"><i class="tio-search"></i>
                            </button>
                        </div>
                        </form>
                    </div>
                    <!-- Table -->
                    <div class="">
                        <div class="table-responsive datatable-custom">
                            <table id="columnSearchDatatable"
                                class="table table-borderless table-thead-bordered table-align-middle text-nowrap">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="border-0">{{ translate('messages.SL') }}</th>
                                        <th class="border-0">{{ translate('messages.Reason') }}</th>
                                        <th class="border-0">{{ translate('messages.type') }}</th>
                                        <th class="border-0 text-center">{{ translate('messages.status') }}</th>
                                        <th class="border-0 text-center">{{ translate('messages.action') }}</th>
                                    </tr>
                                </thead>

                                <tbody id="table-div">
                                    @foreach ($reasons as $key => $reason)
                                    <tr>
                                        <td>{{ $key + $reasons->firstItem() }}</td>

                                        <td>
                                            <span class="d-block font-size-sm text-body" title="{{ $reason->reason }}">
                                                {{ Str::limit($reason->reason, 25, '...') }}
                                            </span>
                                            @if ($reason->is_default)
                                                <button class="btn btn-sm btn-soft-info">{{ translate('messages.default') }}
                                                    <span data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ translate('This zone is set as the default for customers who visit the app or website without choosing a location.') }}"
                                                        class="input-label-secondary text-success"></span>

                                                </button>
                                            @endif
                                        </td>
                                        <td>{{ Str::title($reason->user_type) }}</td>
                                        <td>
                                            <div class="d-flex justify-content-center align-items-center">
                                                <label class="toggle-switch toggle-switch-sm"
                                                    for="stocksCheckbox{{ $reason->id }}">
                                                    <input type="checkbox" {{ $reason->is_default ? 'disabled' : '' }}
                                                        data-url="{{ route('admin.order-cancel-reasons.status', [$reason['id'], $reason->status ? 0 : 1]) }}"
                                                        class="toggle-switch-input redirect-url"
                                                        id="stocksCheckbox{{ $reason->id }}" {{ $reason->status ? 'checked' : '' }}>
                                                    <span class="toggle-switch-label">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="d-flex justify-content-center align-items-center">
                                                <div class="dropdown dropdown-2 hover-gray">
                                                    <button type="button"
                                                        class="bg-transparent border rounded px-2 py-1 title-color"
                                                        data-toggle="dropdown" aria-expanded="false">
                                                        <i class="tio-more-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu" dir="ltr">
                                                        <a class="dropdown-item d-flex gap-2 align-items-center edit-reason"
                                                            title="{{ translate('messages.edit') }}" data-toggle="modal"
                                                            data-target="#add_update_reason_{{ $reason->id }}">
                                                            {{ translate('messages.edit') }}
                                                        </a>
                                                        @if (!$reason->is_default)
                                                            <a class="dropdown-item d-flex gap-2 align-items-center redirect-url"
                                                                href="javascript:"
                                                                data-url="{{ route('admin.order-cancel-reasons.setDefault', [$reason['id'], 1]) }}">
                                                                {{ translate('messages.Mark As Default') }}
                                                            </a>
                                                        @endif


                                                        @if (!$reason->is_default)
                                                            <a class="dropdown-item d-flex gap-2 align-items-center form-alert"
                                                                href="javascript:" disabled
                                                                data-id="order-cancellation-reason-{{ $reason['id'] }}"
                                                                data-message="{{ translate('messages.If_you_want_to_delete_this_reason,_please_confirm_your_decision.') }}"
                                                                title="{{ translate('messages.delete') }}">
                                                                {{ translate('messages.delete') }}
                                                            </a>
                                                            <form
                                                                action="{{ route('admin.order-cancel-reasons.destroy', $reason['id']) }}"
                                                                method="post"
                                                                id="order-cancellation-reason-{{ $reason['id'] }}">
                                                                @csrf @method('delete')
                                                            </form>
                                                        @endif

                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Modal -->
                                    <div class="modal fade" id="add_update_reason_{{ $reason->id }}" tabindex="-1"
                                        role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">
                                                        {{ translate('messages.order_cancellation_reason') }}
                                                        {{ translate('messages.Update') }}</label>
                                                    </h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="{{ route('admin.order-cancel-reasons.update') }}"
                                                    method="post">
                                                    <div class="modal-body">
                                                        @csrf
                                                        @method('put')

                                                        @php($reason = \App\Models\OrderCancelReason::withoutGlobalScope('translate')->with('translations')->find($reason->id))

                                                        <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                                            <ul class="nav nav-tabs nav--tabs mb-3 border-0">
                                                                <li class="nav-item">
                                                                    <a class="nav-link update-lang_link add_active active"
                                                                        href="#"
                                                                        id="default-link">{{ translate('Default') }}</a>
                                                                </li>
                                                                @if ($language)
                                                                    @foreach ($language as $lang)
                                                                        <li class="nav-item">
                                                                            <a class="nav-link update-lang_link" href="#"
                                                                                data-reason-id="{{ $reason->id }}"
                                                                                id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                                        </li>
                                                                    @endforeach
                                                                @endif
                                                            </ul>
                                                        </div>
                                                        <input type="hidden" name="reason_id"
                                                            value="{{ $reason->id }}" />

                                                        <div class="form-group mb-3 add_active_2  update-lang_form"
                                                            id="default-form_{{ $reason->id }}">
                                                            <label for="reason"
                                                                class="form-label">{{ translate('Order Cancellation Reason') }}
                                                                ({{ translate('messages.default') }}) </label>
                                                            <input id="reason" class="form-control" name='reason[]'
                                                                value="{{ $reason?->getRawOriginal('reason') }}"
                                                                type="text">
                                                            <input type="hidden" name="lang1[]" value="default">
                                                        </div>
                                                        @if ($language)
                                                            @forelse($language as $lang)
                                                                                                            <?php
                                                                if ($reason?->translations) {
                                                                    $translate = [];
                                                                    foreach ($reason?->translations as $t) {
                                                                        if ($t->locale == $lang && $t->key == 'reason') {
                                                                            $translate[$lang]['reason'] = $t->value;
                                                                        }
                                                                    }
                                                                }

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        ?>
                                                                                                            <div class="form-group mb-3 d-none update-lang_form"
                                                                                                                id="{{ $lang }}-langform_{{ $reason->id }}">
                                                                                                                <label for="reason{{ $lang }}"
                                                                                                                    class="form-label">{{ translate('Order Cancellation Reason') }}
                                                                                                                    ({{ strtoupper($lang) }})
                                                                                                                </label>
                                                                                                                <input id="reason{{ $lang }}" class="form-control"
                                                                                                                    name='reason[]'
                                                                                                                    placeholder="{{ translate('Ex:_Item_is_Broken') }}"
                                                                                                                    value="{{ $translate[$lang]['reason'] ?? null }}"
                                                                                                                    type="text">
                                                                                                                <input type="hidden" name="lang1[]" value="{{ $lang }}">
                                                                                                            </div>
                                                            @empty
                                                            @endforelse
                                                        @endif

                                                        <select name="user_type" class="form-control h--45px" required>
                                                            <option value="">
                                                                {{ translate('messages.select_user_type') }}
                                                            </option>
                                                            <option {{ $reason->user_type == 'admin' ? 'selected' : '' }}
                                                                value="admin">
                                                                {{ translate('messages.admin') }}
                                                            </option>
                                                            <option {{ $reason->user_type == 'restaurant' ? 'selected' : '' }} value="restaurant">
                                                                {{ translate('messages.restaurant') }}
                                                            </option>
                                                            <option {{ $reason->user_type == 'customer' ? 'selected' : '' }} value="customer">
                                                                {{ translate('messages.customer') }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">{{ translate('Close') }}</button>
                                                        <button type="submit"
                                                            class="btn btn-primary">{{ translate('Save_changes') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </tbody>
                            </table>
                            @if (count($reasons) === 0)
                                <div class="empty--data">
                                    <img src="{{ dynamicAsset('assets/admin/img/empty.png') }}" alt="public">
                                    <h5>
                                        {{ translate('no_data_found') }}
                                    </h5>
                                </div>
                            @endif
                        </div>
                        <div class="page-area px-4 pb-3">
                            <div class="d-flex align-items-center justify-content-end">
                                <div>
                                    {!! $reasons->appends(request()->all())->links() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Table -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Guidline Offcanvas Btn -->
<div class="d-flexgap-2 w-40px gap-2 bg-white position-fixed end-0 translate-middle-y pointer view-guideline-btn flex-column pt-3 px-2 justify-content-center offcanvas-trigger"
    data-toggle="offcanvas" data-target="#offcanvasSetupGuide">
    <span class="arrow bg-primary py-1 px-2 text-white rounded fs-12"><i class="tio-share-vs"></i></span>
    <span class="view-guideline-btn-text text-dark font-semibold pb-2 text-nowrap">
        {{ translate('View_Guideline') }}
    </span>
</div>

<!-- Guidline Offcanvas -->
<div id="offcanvasOverlay" class="offcanvas-overlay"></div>
<div class="custom-offcanvas" tabindex="-1" id="offcanvasSetupGuide" aria-labelledby="offcanvasSetupGuideLabel"
    style="--offcanvas-width: 500px">
    <div>
        <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
            <h3 class="mb-0">{{ translate('messages.Orders Setup Guideline') }}</h3>
            <button type="button"
                class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                aria-label="Close">&times;</button>
        </div>
        <div class="custom-offcanvas-body offcanvas-height-100 py-3 px-md-4 px-3">
            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0 collapsed"
                        type="button" data-toggle="collapse" data-target="#order_type" aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span
                            class="font-semibold text-left fs-14 text-title">{{ translate('messages.Order Type') }}</span>
                    </button>
                    <a href="#order_type_section"
                        class="text-info text-underline fs-12 text-nowrap">{{ translate('messages.Let’s Setup') }}</a>
                </div>
                <div class="collapse mt-3 show" id="order_type">
                    <div class="card card-body">
                        <div class="">
                            <h5 class="mb-3">{{translate('Order Type')}}</h5>
                            <p class="fs-12 mb-3">
                                {{ translate('messages.The Order Type feature allows customers to place orders based on how they want to receive or consume their food.') }}
                            </p>
                            <ul class="mb-0 fs-12">
                                <li class="font-semibold">
                                    {{ translate('messages.Home Delivery') }}
                                </li>
                                <p class="mb-3">
                                    {{ translate('messages.Allows customers to place an order and have it delivered to their specified address. Delivery is handled by a deliveryman or third-party service. It supports real-time order tracking.') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('messages.Take away') }}
                                </li>
                                <p class="mb-3">
                                    {{ translate('messages.Allows customers to place an order in advance and pick it up directly from the restaurant. No delivery charge is applied. The order is prepared for pickup at the restaurant.') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('messages.Dine-In') }}
                                </li>
                                <p class="mb-0">
                                    {{ translate('messages.Allows customers to place orders for consumption at the restaurant premises. It supports table-based ordering. There will be no delivery or packaging charges.') }}
                                </p>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0 collapsed"
                        type="button" data-toggle="collapse" data-target="#regualr_order" aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span
                            class="font-semibold text-left fs-14 text-title">{{ translate('messages.Regular Order') }}</span>
                    </button>
                    <a href="#regular_order_section"
                        class="text-info text-underline fs-12 text-nowrap">{{ translate('messages.Let’s Setup') }}</a>
                </div>
                <div class="collapse mt-3" id="regualr_order">
                    <div class="card card-body">
                        <div class="">
                            <h5 class="mb-3">{{translate('Regular Order')}}</h5>
                            <p class="fs-12 mb-0">
                                {{ translate('messages.The Regular Order Settings allow you to control how customers place orders—either for immediate processing or for a scheduled time and date. These options help restaurants and service providers manage order flow more efficiently.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0 collapsed"
                        type="button" data-toggle="collapse" data-target="#sub_order" aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span
                            class="font-semibold text-left fs-14 text-title">{{ translate('messages.Subscription Order') }}</span>
                    </button>
                    <a href="#subscription_order_section"
                        class="text-info text-underline fs-12 text-nowrap">{{ translate('messages.Let’s Setup') }}</a>
                </div>
                <div class="collapse mt-3" id="sub_order">
                    <div class="">
                        <div class="owl-carousel single-item-slider_has-arrow card card-body">
                            <div class="item">
                                <div class="">
                                    <h5 class="mb-3">{{translate('Subscription Order')}}</h5>
                                    <p class="fs-12 mb-0">
                                        {{ translate('messages.Offer your customers the convenience of recurring orders. Set up subscription plans, manage billing cycles, and provide exclusive discounts for loyal subscribers.') }}
                                    </p>
                                </div>
                            </div>
                            <div class="item">
                                <div class="">
                                    <h5 class="mb-3">{{translate('Custom Date Order')}}</h5>
                                    <p class="fs-12 mb-3">
                                        {{ translate('messages.Dine In only. Let customers pick the date and time of their Dine In booking at checkout. It does not restrict Home Delivery or Takeaway orders.') }}
                                    </p>
                                    <p class="fs-12 mb-0">
                                        <span
                                            class="font-semibold">{{ translate('messages.Customer Can Order Within (Days)') }}</span>:
                                        {{ translate('messages.Set how many days ahead a Dine In booking may be made. Both the date and the time are limited by it.') }}
                                    </p>
                                </div>
                            </div>
                            <div class="item">
                                <div class="">
                                    <h5 class="mb-3">{{translate('Scheduled Delivery')}}</h5>
                                    <p class="fs-12 mb-3">
                                        {{ translate('messages.Configure your delivery schedule to receive your orders automatically at your convenience.') }}
                                    </p>
                                    <p class="fs-12 mb-0">
                                        <span
                                            class="font-semibold">{{ translate('messages.Time Interval for Scheduled Delivery') }}</span>:
                                        {{ translate('messages.Set the preferred time interval for your scheduled deliveries. Choose how often you want to receive your orders.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            class="slider-bottom position-relative d-flex justify-content-center align-items-center gap-3 my-3 w-150 mx-auto">
                            <div class="slide-counter bg-transparent fs-14 mr-3 mt-2 text-dark"></div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0 collapsed"
                        type="button" data-toggle="collapse" data-target="#notification_setup" aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span
                            class="font-semibold text-left fs-14 text-title">{{ translate('messages.Notification Setup') }}</span>
                    </button>
                    <a href="#notification_setup_section"
                        class="text-info text-underline fs-12 text-nowrap">{{ translate('messages.Let’s Setup') }}</a>
                </div>
                <div class="collapse mt-3" id="notification_setup">
                    <div class="card card-body">
                        <div class="">
                            <h5 class="mb-3">{{translate('Notification Setup')}}</h5>
                            <p class="fs-12 mb-0">
                                {{ translate('messages.The Admin Notification Setup allows the administrator to configure how system notifications are sent and managed. Admin notifications can be delivered either manually or through Firebase, depending on the selected configuration.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0 collapsed"
                        type="button" data-toggle="collapse" data-target="#Others" aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span class="font-semibold text-left fs-14 text-title">{{ translate('messages.Others Setup') }}</span>
                    </button>
                    <a href="#others_setup_section"
                        class="text-info text-underline fs-12 text-nowrap">{{ translate('messages.Let’s Setup') }}</a>
                </div>
                <div class="collapse mt-3" id="Others">
                    <div class="card card-body">
                        <div class="">
                            <h5 class="mb-3">{{translate('Others Setup')}}</h5>
                            <p class="fs-12 mb-3">
                                {{ translate('messages.Configure additional order controls, including enabling the repeat order option for quick reordering, applying delivery verification methods to ensure successful delivery, and selecting the order confirmation model to define how orders are approved and processed within the system.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0 collapsed"
                        type="button" data-toggle="collapse" data-target="#order_cancellation_messages" aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span class="font-semibold text-left fs-14 text-title">{{ translate('messages.Set Up Order Cancellation Messages') }}</span>
                    </button>
                    <a href="#order_cancellation_messages_section"
                        class="text-info text-underline fs-12 text-nowrap">{{ translate('messages.Let’s Setup') }}</a>
                </div>
                <div class="collapse mt-3" id="order_cancellation_messages">
                    <div class="card card-body">
                        <div class="">
                            <h5 class="mb-3">{{translate('Set Up Order Cancellation Messages')}}</h5>
                            <p class="fs-12 mb-3">
                                {{ translate('messages.This section allows the admin to manage order cancellation reasons. Admin can create and configure cancellation reasons based on different user types, control their active status, and mark a reason as default. These reasons will be displayed to users for selection when they attempt to cancel an order.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



@endsection

@push('script_2')
    <script src="{{ dynamicAsset('assets/admin/js/view-pages/business-settings-order-page.js') }}"></script>
    <script>
        'use strict'
        $(document).on('ready', function () {
            var datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'), {
                select: {
                    style: 'multi',
                    classMap: {
                        checkAll: '#datatableCheckAll',
                        counter: '#datatableCounter',
                        counterInfo: '#datatableCounterInfo'
                    }
                },
                language: {
                    zeroRecords: '<div class="text-center p-4">' +
                        '<img class="mb-3" src="{{dynamicAsset("assets/admin/svg/illustrations/sorry.svg")}}" alt="Image Description" style="width: 7rem;">' +
                        '<p class="mb-0">{{ translate("No data to show") }}</p>' +
                        '</div>'
                },
                paging: false,
                isResponsive: false,
                columnDefs: [{
                    targets: [3, 4],
                    orderable: false
                }]
            });

            function toggleScheduleOrderOptions() {
                if ($('#schedule_order').is(':checked')) {
                    $('#schedule_order_slot_duration_section').removeClass('disabled');
                    $('#schedule_order_slot_duration_section').find('input, select').prop('disabled', false);
                } else {
                    $('#schedule_order_slot_duration_section').addClass('disabled');
                    $('#schedule_order_slot_duration_section').find('input, select').prop('disabled', true);
                }
            }

            toggleScheduleOrderOptions();

            $('#schedule_order').on('change', function () {
                toggleScheduleOrderOptions();
            });


            let owl = $('.single-item-slider_has-arrow');

            owl.owlCarousel({
                autoplay: false,
                items: 1,
                onInitialized: setupNavAndCounter,
                onTranslated: counter,
                autoHeight: true,
                dots: false,
                nav: true,
                navText: [
                    '<span class="btn btn-sm  btn-circle rounded border text-primary bg-white"><i class="tio-chevron-left fs-24"></i></span>',
                    '<span class="btn btn-sm  btn-circle rounded border text-primary bg-white"><i class="tio-chevron-right fs-24"></i></span>'
                ],

            });

            function setupNavAndCounter(event) {
                let nav = $(event.target).find('.owl-nav');
                $('.slider-bottom').prepend(nav);
                counter(event);
            }

            function counter(event) {
                let items = event.item.count;
                let item = event.item.index + 1;
                if (item > items) item = item - items;
                $('.slide-counter').html(item + "/" + items);
            }
        });

        $(document).on('click', '.demo_check', function (event) {
            toastr.warning('{{ translate('Sorry! You can not enable maintenance mode in demo!') }}', {
                CloseButton: true,
                ProgressBar: true
            });
            event.preventDefault();
        });

        $('.filter-user-type').on('change', function() {
            $(this).closest('form').submit();
        });

        $(document).ready(function() {
            $('.custom-offcanvas a[href^="#"]').on('click', function(e) {
                e.preventDefault();
                var target = this.hash;
                var $target = $(target);

                $('.offcanvas-close').trigger('click');

                $('html, body').stop().animate({
                    scrollTop: $target.offset().top - 100
                }, 900, 'swing', function() {
                    window.location.hash = target;
                });
            });
        });
    </script>

@endpush
