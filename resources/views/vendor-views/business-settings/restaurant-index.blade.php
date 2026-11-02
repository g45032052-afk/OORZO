@extends('layouts.vendor.app')

@section('title', translate('messages.business_configuration'))

@push('css_or_js')
    <link href="{{ dynamicAsset('assets/admin/css/croppie.css') }}" rel="stylesheet">
    <link href="{{ dynamicAsset('assets/admin/css/tags-input.min.css') }}" rel="stylesheet">
    <link href="{{ dynamicAsset('assets/admin/css/fm.tagator.jquery.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        @include('vendor-views.partials.business_setup_header')
        <form action="{{ route('vendor.business-settings.update-setup', [$restaurant['id']]) }}" method="post"
            enctype="multipart/form-data">
            @csrf
            <div class="card card-body mb-20">
                @if(\App\CentralLogics\Helpers::get_business_settings('admin_website_builder_status') == 1)
                <div class="card card-body mb-20" id="vendor_website_builder">
                    <div class="row g-1 align-items-center">
                        <div class="col-xxl-9 col-lg-8 col-md-7 col-sm-6">
                            <div>
                                <h4 class="mb-1">{{ translate('Vendor Website Builder') }}</h4>
                                <p class="mb-0 fs-12">{{ translate('Enable this option to set up and manage your own storefront website.') }}</p>
                            </div>
                        </div>
                        <div class="col-xxl-3 col-lg-4 col-md-5 col-sm-6">
                            <div class="form-group mb-0">
                                <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0">
                                    <span class="pr-1 d-flex align-items-center switch--label">
                                        <span class="line--limit-1">{{ translate('messages.Status') }}</span>
                                    </span>
                                    <input type="checkbox" class="toggle-switch-input" value="1" name="website_builder_status" id="website_builder_status"
                                        {{ $restaurant->restaurant_config?->website_builder_status == 1 ? 'checked' : '' }}>
                                    <span class="toggle-switch-label text">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="card card-body mb-20">
                    <div class="mb-20">
                        <h4 class="mb-1">{{ translate('messages.Order_Type') }}</h4>
                        <p class="fs-12 mb-0">
                            {{ translate('messages.Select the order type that is suitable for your restaurant') }}
                        </p>
                    </div>
                    <div class="bg-light rounded-10 p-3 p-sm-4 mb-20">
                        <div class="row g-3 border rounded bg-white mx-0 mt-0 mb-20">
                            <div class="col-lg-4">
                                <div class="custom-checkbox custom-control d-flex gap-2 h-100">
                                    <input type="checkbox" class="custom-control-input" value="1" name="delivery"
                                        id='cod' {{ $restaurant->delivery ? 'checked' : '' }}>
                                    <label class="custom-control-label d-flex flex-column justify-content-between mb-0"
                                        for="cod">
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
                                        id='take_away' {{ $restaurant->take_away ? 'checked' : '' }}>
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
                                    <input type="checkbox" class="custom-control-input" value="1" name="dine_in"
                                        id='dine_in' {{ $restaurant->restaurant_config?->dine_in == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label d-flex flex-column justify-content-between mb-0"
                                        for="dine_in">
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
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="form-group m-0">
                                    <label class="input-label text-capitalize d-flex gap-1 align-items-center"
                                        for="title">{{ translate('messages.minimum_order_amount') }}
                                        <span data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('Specify_the_minimum_order_amount_required_for_customers_when_ordering_from_this_restaurant.') }}"
                                            class="tio-info text-gray1 fs-16"></span>

                                    </label>
                                    <input type="number" name="minimum_order" step="0.01" min="0" max="100000"
                                        class="form-control" placeholder="100"
                                        value="{{ $restaurant->minimum_order ?? '0' }}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group mb-0">
                                    <label class="input-label text-capitalize d-flex gap-1 align-items-center"
                                        for="schedule_order_slot_duration">
                                        {{ translate('messages.Interval Time for Dine-in Order') }}
                                        <span class="tio-info text-gray1 fs-16" data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('By_activating_this_feature,_customers_can_choose_their_advance_booking_according_to_a_30-minute_or_1-hour_interval_set_by_the_Admin') }}">
                                        </span>
                                    </label>
                                    <div class="custom-group-btn form-control single"
                                        @if (!$restaurant->restaurant_config?->dine_in) data-toggle="tooltip" data-placement="top"
                                            data-original-title="{{ translate('messages.To active this field check the Dine-in order option.') }}" @endif>
                                        <div class="item flex-grow-1">
                                            <input type="number" name="schedule_advance_dine_in_booking_duration"
                                                class="form-control border-0 h-100"
                                                id="schedule_advance_dine_in_booking_duration"
                                                value="{{ $restaurant->restaurant_config?->schedule_advance_dine_in_booking_duration ?? 0 }}"
                                                min="0" max="9999"
                                                {{ $restaurant->restaurant_config?->dine_in == 1 ? 'required' : 'disabled' }}>
                                        </div>
                                        <div class="item flex-shrink-0">
                                            <select @disabled(!$restaurant->restaurant_config?->dine_in)
                                                id="schedule_advance_dine_in_booking_duration_time_format"
                                                name="schedule_advance_dine_in_booking_duration_time_format"
                                                class="custom-select w-90px border-0">
                                                <option value="min">
                                                    {{ translate('Min') }}</option>
                                                <option value="hour"
                                                    {{ $restaurant->restaurant_config?->schedule_advance_dine_in_booking_duration_time_format == 'hour' ? 'selected' : '' }}>
                                                    {{ translate('Hour') }}</option>
                                                <option value="day"
                                                    {{ $restaurant->restaurant_config?->schedule_advance_dine_in_booking_duration_time_format == 'day' ? 'selected' : '' }}>
                                                    {{ translate('Day') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info" style="--bs-bg-opacity: 0.1;">
                        <span class="text-info lh-1 fs-14 flex-shrink-0">
                            <img src="{{ dynamicAsset('assets/admin/img/svg/bulb.svg') }}" class="svg"
                                alt="">
                        </span>
                        <span>
                            {{ translate('messages.You can check all your order from') }}
                            <a href="{{ route('vendor.order.list', ['all']) }}"
                                class="font-semibold text-primary text-underline">{{ translate('messages.All Orders') }}
                            </a>
                            {{ translate('messages.page.') }}
                            {{ translate('messages.For dine in visit') }}
                            <a href="{{ route('vendor.order.list', ['dine_in']) }}"
                                class="font-semibold text-primary text-underline">{{ translate('messages.Dine In') }} </a>
                            {{ translate('messages.page.') }}
                        </span>
                    </div>
                </div>
                <div class="card card-body mb-20">
                    <div class="mb-20">
                        <h4 class="mb-1">{{ translate('messages.Regular_Order') }}</h4>
                        <p class="fs-12 mb-0">
                            {{ translate('messages.Select how can customer make the regular orders that is suitable for your restaurant') }}
                        </p>
                    </div>
                    <div class="bg-light rounded-10 p-3 p-sm-4 mb-20">
                        <div class="row g-3 border rounded bg-white m-0">
                            <div class="col-lg-4">
                                <div class="custom-checkbox custom-control d-flex gap-2 h-100">
                                    <input type="checkbox" class="custom-control-input" value="1"
                                        name="instant_order" id='instant'
                                        {{ $restaurant?->restaurant_config?->instant_order ? 'checked' : '' }}>
                                    <label class="custom-control-label d-flex flex-column justify-content-between mb-0"
                                        for="instant">
                                        <div>
                                            <h5 class="mb-1">
                                                {{ translate('messages.Instant Order') }}
                                            </h5>
                                            <p class="fs-12 mb-0">
                                                {{ translate('messages.With this feature customers can order instantly from your restaurant.') }}
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="custom-checkbox custom-control d-flex gap-2 h-100">
                                    <input type="checkbox" class="custom-control-input" value="1"
                                        name="schedule_order" id='scheduled'
                                        {{ $restaurant->schedule_order ? 'checked' : '' }}>
                                    <label class="custom-control-label d-flex flex-column justify-content-between mb-0"
                                        for="scheduled">
                                        <div>
                                            <h5 class="mb-1">
                                                {{ translate('messages.Scheduled Order') }}
                                            </h5>
                                            <p class="fs-12 mb-0">
                                                {{ translate('messages.If enabled, customers can choose their preferred delivery time and date to order from your restaurant.') }}
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @php($order_subscription = \App\CentralLogics\Helpers::get_business_settings('order_subscription'))

                            @if ($order_subscription)
                                <div class="col-lg-4">
                                    <div class="custom-checkbox custom-control d-flex gap-2 h-100">
                                        <input type="checkbox" class="custom-control-input" value="1"
                                            name="order_subscription_active" id='subscription'
                                            {{ $restaurant->order_subscription_active ? 'checked' : '' }}>
                                        <label class="custom-control-label d-flex flex-column justify-content-between mb-0"
                                            for="subscription">
                                            <div>
                                                <h5 class="mb-1">
                                                    {{ translate('messages.Subscription Order') }}
                                                </h5>
                                                <p class="fs-12 mb-0">
                                                    {{ translate('messages.If enabled customers can order food on a subscription basis from your restaurant.') }}
                                                </p>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @if ($restaurant->sub_self_delivery == 1)
                    <div class="card card-body mb-20">
                        <div class="mb-20">
                            <h4 class="mb-1">{{ translate('Delivery Setup') }}</h4>
                            <p class="fs-12 mb-0">{{ translate('Setup your delivery options And delivery charges') }}</p>
                        </div>

                        {{-- Free delivery moved to the admin panel's zone-wise setup in 9.3.
                             The restaurant-level toggle and the over-distance field are gone
                             with it, so a vendor no longer configures free delivery at all. --}}
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="form-group m-0">
                                    <label class="input-label text-capitalize"
                                        for="minimum_shipping_charge">{{ translate('messages.minimum_delivery_charge') }}
                                        ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                    </label>
                                    <input type="number" id="minimum_shipping_charge" min="0" max="99999999.99"
                                        step="0.01" name="minimum_delivery_charge" class="form-control shipping_input"
                                        value="{{ isset($restaurant->minimum_shipping_charge) ? $restaurant->minimum_shipping_charge : '' }}">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-group m-0">
                                    <label class="input-label text-capitalize"
                                        {{-- Reads "per km" or "per mi" from the business setting. The column behind it
                                             is still per_km_shipping_charge; only the wording follows the setting. --}}
                                        for="title">{{ translate('messages.delivery_charge_per') }} {{ \App\CentralLogics\Helpers::distanceUnitLabel() }}
                                        ({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                                    <input type="number" name="per_km_delivery_charge" step="0.01" min="0"
                                        max="100000" class="form-control" placeholder="100"
                                        value="{{ $restaurant->per_km_shipping_charge ?? '0' }}">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group m-0">
                                    <label class="input-label text-capitalize"
                                        for="title">{{ translate('messages.maximum_shipping_charge') }}
                                        ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                        <span data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('It will add a limite on total delivery charge.') }}"
                                            class="input-label-secondary"><img
                                                src="{{ dynamicAsset('assets/admin/img/info-circle.svg') }}"
                                                alt="{{ translate('messages.maximum_shipping_charge') }}"></span>
                                    </label>
                                    <input type="number" name="maximum_shipping_charge" step="0.01" min="0"
                                        max="999999999" class="form-control" placeholder="10000"
                                        value="{{ $restaurant->maximum_shipping_charge ?? '' }}">
                                </div>
                            </div>

                        </div>
                    </div>
                @endif


                <div class="card card-body mb-20">
                    <div class="mb-20">
                        <h4 class="mb-1">{{ translate('messages.Setup Your Restaurant Type & Tags') }}</h4>
                        <p class="fs-12 mb-0">
                            {{ translate('messages.Select which types of cousin & restaurant characteristic foods are served by your restaurant.') }}
                        </p>
                    </div>
                    <div class="bg-light rounded-10 p-3 p-sm-4 mb-20">
                        <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info mb-2"
                            style="--bs-bg-opacity: 0.1;">
                            <span class="text-info lh-1 fs-14 flex-shrink-0">
                                <img src="{{ dynamicAsset('assets/admin/img/svg/bulb.svg') }}" class="svg"
                                    alt="">
                            </span>
                            <span>
                                {{ translate('messages.Select your foods cuisine to categories your restaurant. You may not select the cuisine as your business preference.') }}
                            </span>
                        </div>
                        <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info mb-20"
                            style="--bs-bg-opacity: 0.1;">
                            <span class="text-info lh-1 fs-14 flex-shrink-0">
                                <img src="{{ dynamicAsset('assets/admin/img/svg/bulb.svg') }}" class="svg"
                                    alt="">
                            </span>
                            <span>
                                {{ translate('messages.Specify your restaurant characteristic, which type of restaurant your are. It will help your customer to find you easily.') }}
                            </span>
                        </div>
                        <div class="row g-3 pb-3">
                            <div class="col-lg-6">
                                <div class="form-group m-0">
                                    <label class="input-label d-flex gap-1 align-items-center"
                                        for="cuisine">{{ translate('messages.cuisine') }}
                                        <span data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('Choose_your_preferred_cuisines_from_the_drop-down_menu,_and_customers_can_see_them_in_your_restaurant.') }}"
                                            class="tio-info text-gray1 fs-16"></span>
                                    </label>
                                    <select name="cuisine_ids[]" id="cuisine" multiple="multiple"
                                        data-placeholder="{{ translate('messages.select_Cuisine') }}"
                                        class="form-control h--45px min--45 js-select2-custom">
                                        {{ translate('messages.Cuisine') }}</option>
                                        @php($cuisine_array = \App\Models\Cuisine::where('status', 1)->get()->toArray())
                                        @php($selected_cuisine = isset($restaurant->cuisine) ? $restaurant->cuisine->pluck('id')->toArray() : [])
                                        @foreach ($cuisine_array as $cu)
                                            <option value="{{ $cu['id'] }}"
                                                {{ in_array($cu['id'], $selected_cuisine) ? 'selected' : '' }}>
                                                {{ $cu['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group m-0">
                                    <label class="input-label d-flex gap-1 align-items-center" for="cuisine">
                                        {{ translate('messages.Restaurant Characteristics') }}
                                        <span data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages.Select the Restaurant Type that Best Represents Your Establishment') }}"
                                            class="tio-info text-gray1 fs-16"></span>
                                    </label>
                                    <input id="activate_tagator2" type="text" name="characteristics"
                                        class="tagator form-control"
                                        value="@foreach ($restaurant->characteristics as $index => $c){{ $c->characteristic }}{{ $index < count($restaurant->characteristics) - 1 ? ',' : '' }} @endforeach"
                                        data-tagator-show-all-options-on-focus="true"
                                        data-tagator-autocomplete="{{ $combinedNames }}">

                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group m-0">
                                    <label class="input-label d-flex gap-1 align-items-center" for="cuisine">
                                        {{ translate('messages.Restaurant Tags') }}
                                    </label>
                                    <input type="text" class="form-control" name="tags"
                                        value="@foreach ($restaurant->tags as $c) {{ $c->tag . ',' }} @endforeach"
                                        placeholder="Enter tags" data-role="tagsinput">

                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info" style="--bs-bg-opacity: 0.1;">
                            <span class="text-info lh-1 fs-14 flex-shrink-0">
                                <img src="{{ dynamicAsset('assets/admin/img/svg/bulb.svg') }}" class="svg"
                                    alt="">
                            </span>
                            <span>
                                {{ translate('messages.Add search tag to boost up your restaurant better performance when user search any food.') }}
                            </span>
                        </div>
                    </div>
                </div>

                @php($extra_packaging_charge = \App\CentralLogics\Helpers::get_business_settings('extra_packaging_charge'))
                @if ($extra_packaging_charge == 1)
                    <div class="card card-body mb-20">
                        <div class="mb-20">
                            <h4 class="mb-1">{{ translate('messages.Packaging Charge Setup') }}</h4>
                            <p class="fs-12 mb-0">
                                {{ translate('messages.In this section you can setup the extra packaging charge amount feature for your customer.') }}
                            </p>
                        </div>
                        <div class="bg-light rounded-10 p-3 p-sm-4 mb-20">
                            <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-warning mb-20"
                                style="--bs-bg-opacity: 0.1;">
                                <span class="text-warning lh-1 fs-14 flex-shrink-0">
                                    <i class="tio-info"></i>
                                </span>
                                <span>
                                    {{ translate('messages.By enabling the status customer will get the option for choosing extra packaging charge when placing order.') }}
                                </span>
                            </div>
                            <div class="row g-3">


                                <div class="col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label d-flex gap-1 align-items-center"
                                            for="cuisine">{{ translate('messages.Extra_Packaging_Charge') }}
                                            <span data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ translate('messages.By_enabling_the_status_customer_will_get_the_option_for_choosing_extra_packaging_charge_when_placing_order._for_extra_package_offer') }}"
                                                class="tio-info text-gray1 fs-16"></span>
                                        </label>
                                        <label
                                            class="toggle-switch toggle-switch-sm d-flex justify-content-between border  rounded px-3 form-control"
                                            for="is_extra_packaging">
                                            <span class="pr-2 d-flex">
                                                {{ translate('messages.Status') }}
                                            </span>
                                            <input type="checkbox" name="is_extra_packaging_active"
                                                data-id="is_extra_packaging" data-type="status"
                                                data-image-on="{{ dynamicAsset('assets/admin/img/modal/dm-tips-on.png') }}"
                                                data-image-off="{{ dynamicAsset('assets/admin/img/modal/dm-tips-off.png') }}"
                                                data-title-on="{{ translate('Want_to_enable_the_extra_packaging_charge_for_this_restaurant?') }}"
                                                data-title-off="{{ translate('Want_to_disable_the_extra_packaging_charge_for_this_restaurant?') }}"
                                                data-text-on="<p>{{ translate('By_enabling_the_status_customer_will_get_the_option_for_choosing_extra_packaging_charge_when_placing_order._for_extra_package_offer') }}"
                                                data-text-off="<p>{{ translate('If_disabled,_customer_will_not_get_the_option_for_choosing_extra_packaging_charge_when_placing_order._for_extra_package_offer.') }}</p>"
                                                class="toggle-switch-input" id="is_extra_packaging"
                                                {{ $restaurant->restaurant_config?->is_extra_packaging_active == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group m-0">
                                        <label class="input-label text-capitalize"
                                            for="title">{{ translate('messages.extra_packaging_charge_amount') }} ($)
                                        </label>
                                        <div class="extra-packaging-area"
                                            @if ($restaurant->restaurant_config?->is_extra_packaging_active != 1) data-toggle="tooltip" data-placement="top"
                                                    data-original-title="{{ translate('messages.To active this field turn on the feature order option') }}" @endif>
                                            <input type="number" name="extra_packaging_amount" step="0.01"
                                                id="extra_packaging_amounts"
                                                {{ $restaurant->restaurant_config?->is_extra_packaging_active == 1 ? 'required' : 'readonly' }}
                                                min="0" max="100000" class="form-control" placeholder=""
                                                value="{{ $restaurant?->restaurant_config?->extra_packaging_amount ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="input-label d-flex gap-1 align-items-center" for="">
                                        {{ translate('Set_Packaging_Charge_As') }}
                                    </label>
                                    <div class="w-100 bg-white border rounded d-flex justify-content-between align-items-center gap-3 flex-wrap p-3 {{ $restaurant->restaurant_config?->is_extra_packaging_active != 1 ? 'disabled_warning' : '' }}"
                                        id="disable_warning">
                                        <div class="flex-grow-1">
                                            <div class="form-group form-check form--check mb-0">
                                                <input
                                                    {{ $restaurant->restaurant_config?->is_extra_packaging_active != 1 ? 'disabled' : '' }}
                                                    type="radio" name="extra_packaging_status" value="0"
                                                    class="form-check-input " id="optional"
                                                    {{ $restaurant->restaurant_config?->is_extra_packaging_active == 1 && $restaurant?->restaurant_config?->extra_packaging_status == '0' ? 'checked' : '' }}>
                                                <label class="d-flex flex-column justify-content-between mb-0"
                                                    for="optional">
                                                    <h5 class="mb-1">{{ translate('messages.Optional') }}</h5>
                                                    <p class="fs-12 mb-0">
                                                        {{ translate('messages.If you select ‘Optional’ customer don’t need to pay for extra packaging charge during checkout.') }}
                                                    </p>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="form-group form-check form--check mb-0">
                                                <input
                                                    {{ $restaurant->restaurant_config?->is_extra_packaging_active != 1 ? 'disabled' : '' }}
                                                    type="radio" name="extra_packaging_status" value="1"
                                                    class="form-check-input" id="mandatory"
                                                    {{ $restaurant->restaurant_config?->is_extra_packaging_active == 1 && $restaurant?->restaurant_config?->extra_packaging_status == '1' ? 'checked' : '' }}>
                                                <label class="d-flex flex-column justify-content-between mb-0"
                                                    for="mandatory">
                                                    <h5 class="mb-1">{{ translate('messages.Required') }}</h5>
                                                    <p class="fs-12 mb-0">
                                                        {{ translate('messages.If you select ‘Required’ customer need to pay for extra packaging charge during checkout.') }}
                                                    </p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                @endif


                <div class="card card-body mb-20">
                    <div class="mb-20">
                        <h4 class="mb-1">{{ translate('messages.Other Setup') }}</h4>
                        <p class="fs-12 mb-0">
                            {{ translate('Select whether your restaurant serves Veg only or both Veg and Non-Veg items.') }}
                        </p>
                    </div>

                    @php($toggle_veg_non_veg = \App\CentralLogics\Helpers::get_business_settings('toggle_veg_non_veg'))
                    @if ($toggle_veg_non_veg == 1)
                        <div class="bg-light rounded-10 p-3 p-sm-4 mb-20">
                            <div
                                class="bg-white border rounded px-3 py-2 d-flex gap-3 justify-content-between align-items-center flex-wrap">
                                <div class="custom-checkbox custom-control d-flex gap-2 flex-grow-1">
                                    <input type="checkbox" class="custom-control-input" value="1" name="veg"
                                        id='veg' {{ $restaurant->veg ? 'checked' : '' }}>
                                    <label class="custom-control-label mb-0" for="veg">
                                        {{ translate('messages.Veg') }}
                                    </label>
                                </div>
                                <div class="custom-checkbox custom-control d-flex gap-2 flex-grow-1">
                                    <input type="checkbox" class="custom-control-input" value="1" name="non_veg"
                                        id='non' {{ $restaurant->non_veg ? 'checked' : '' }}>
                                    <label class="custom-control-label mb-0" for="non">
                                        {{ translate('messages.Non_Veg') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label class="input-label d-flex gap-1 align-items-center"
                                    for="cuisine">{{ translate('messages.halal_tag_status') }}
                                    <span data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ translate('messages._customers_can_see_halal_tag_on_product') }}"
                                        class="tio-info text-gray1 fs-16"></span>
                                </label>
                                <label
                                    class="toggle-switch toggle-switch-sm d-flex justify-content-between border  rounded px-3 form-control"
                                    for="halal_tag_status">
                                    <span class="pr-2 d-flex">
                                        {{ translate('messages.Status') }}
                                    </span>
                                    <input type="checkbox" data-id="halal_tag_status" data-type="status"
                                        name="halal_tag_status"
                                        data-image-on="{{ dynamicAsset('assets/admin/img/modal/schedule-on.png') }}"
                                        data-image-off="{{ dynamicAsset('assets/admin/img/modal/schedule-off.png') }}"
                                        data-title-on="{{ translate('Want_to_enable_halal_tag_status_for_this_restaurant?') }}"
                                        data-title-off="{{ translate('Want_to_disable_halal_tag_status_for_this_restaurant?') }}"
                                        data-text-on="<p>{{ translate('If_enabled,_customers_can_see_halal_tag_on_product') }}"
                                        data-text-off="<p>{{ translate('If_disabled,_customers_can_not_see_halal_tag_on_product.') }}</p>"
                                        class="toggle-switch-input dynamic-checkbox" id="halal_tag_status"
                                        {{ $restaurant->restaurant_config?->halal_tag_status == 1 ? 'checked' : '' }}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group m-0">
                                <label class="input-label d-flex gap-1 align-items-center"
                                    for="cuisine">{{ translate('messages.Cutlery On Order Delivery') }}
                                    <span data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ translate('messages.If this option is on , customer can choose cutlery in user app.') }}"
                                        class="tio-info text-gray1 fs-16"></span>
                                </label>
                                <label
                                    class="toggle-switch toggle-switch-sm d-flex justify-content-between border rounded px-3 form-control"
                                    for="cutlery">
                                    <span class="pr-2 text-capitalize">
                                        {{ translate('messages.Status') }}
                                    </span>
                                    <input type="checkbox" class="toggle-switch-input dynamic-checkbox" data-id="cutlery"
                                        data-type="status" name="cutlery"
                                        data-image-on='{{ dynamicAsset('assets/admin/img/modal') }}/restaurant-reg-on.png'
                                        data-image-off="{{ dynamicAsset('assets/admin/img/modal') }}/restaurant-reg-off.png"
                                        data-title-on="{{ translate('Want_to_enable_the') }} <strong>{{ translate('cutlery') }}</strong> {{ translate('option') }} ?"
                                        data-title-off="{{ translate('Want_to_disable_the') }} <strong>{{ translate('cutlery') }}</strong> {{ translate('option') }} ?"
                                        data-text-on="<p>{{ translate('If_enabled,_customers_can_order_food_with_or_without_cutlery_from_your_restaurant.') }}</p>"
                                        data-text-off="<p>{{ translate('If_disabled,_the_cutlery_option_will_be_hidden_from_your_restaurant.') }}</p>"
                                        id="cutlery" {{ $restaurant->cutlery ? 'checked' : '' }}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group m-0">
                                <label
                                    class="toggle-switch toggle-switch-sm d-flex justify-content-between input-label mb-1"
                                    for="gst_status">

                                    <span class="input-label d-flex gap-1 align-items-center"
                                        for="cuisine">{{ translate('messages.GST') }}
                                        <span data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages.If_enabled,_the_GST_number_will_be_shown_in_the_invoice') }}"
                                            class="tio-info text-gray1 fs-16"></span>
                                    </span>
                                    <input type="checkbox" class="toggle-switch-input" name="gst_status" id="gst_status"
                                        value="1" {{ $restaurant->gst_status ? 'checked' : '' }}>
                                    <span class="toggle-switch-label text">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                                <input type="text" id="gst" name="gst" class="form-control"
                                    value="{{ $restaurant->gst_code }}"
                                    {{ isset($restaurant->gst_status) ? '' : 'readonly' }}>
                            </div>
                        </div>
                        @if ($restaurant->schedule_order)
                            <div class="col-sm-6">
                                <div class="form-group mb-0">
                                    <label class="input-label d-flex gap-1 align-items-center"
                                        for="cuisine">{{ translate('custom_date_order_status') }}
                                        <span data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('With_this_feature,_customers_can_not_select_schedule_date_over_the_given_days') }}"
                                            class="tio-info text-gray1 fs-16"></span>
                                    </label>
                                    <label
                                        class="toggle-switch toggle-switch-sm d-flex justify-content-between border  rounded px-3 form-control"
                                        for="customer_date_order_sratus">
                                        <span class="pr-2 d-flex">
                                            {{ translate('messages.Status') }}
                                        </span>
                                        <input type="checkbox" name="customer_date_order_sratus"
                                            value="1"
                                            data-id="customer_date_order_sratus" data-type="status"
                                            data-image-on="{{ dynamicAsset('assets/admin/img/modal/dm-tips-on.png') }}"
                                            data-image-off="{{ dynamicAsset('assets/admin/img/modal/dm-tips-off.png') }}"
                                            data-title-on="{{ translate('Want_to_enable_the') }} <strong>{{ translate('custom_date_order_status') }}</strong> {{ translate('option') }} ?"
                                            data-title-off="{{ translate('Want_to_disable_the') }} <strong>{{ translate('custom_date_order_status') }}</strong> {{ translate('option') }} ?"
                                            data-text-on="<p>{{ translate('If_enabled,_customers_can_not_select_schedule_date_over_the_given_days._and_you_must_set_a_date_on_the') }} <b>{{ translate('Customer_Can_Order_Within_field') }}</b></p>"
                                            data-text-off="<p>{{ translate('If_disabled,_customers_can_select_any_schedule_date.') }}</p>"
                                            class="toggle-switch-input" id="customer_date_order_sratus"
                                            {{ $restaurant->restaurant_config?->customer_date_order_sratus == 1 ? 'checked' : '' }}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group m-0">
                                    <label class="input-label text-capitalize"
                                        for="title">{{ translate('Customer_Can_Order_Within') }} ({{ translate('messages.Days') }})
                                        <span data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('customers_can_not_select_schedule_date_over_this_given_days') }}"
                                            class="tio-info text-gray1 fs-16"></span>
                                    </label>
                                    <div class="extra-packaging-area"  >
                                        <input type="number" name="customer_order_date" id="customer_order_date"
                                    {{ $restaurant?->restaurant_config?->customer_date_order_sratus == 1 ? 'required' : 'readonly' }}
                                    min="0" max="99999999" class="form-control" placeholder="30"
                                    value="{{ $restaurant?->restaurant_config?->customer_order_date ?? '0' }}">
                                    </div>
                                </div>
                            </div>

                        @endif


                    </div>
                </div>
                <div class="card card-body mb-20">
                    <div class="mb-20">
                        <h4 class="mb-1">{{ translate('messages.Restaurant Meta Data') }}</h4>
                        <p class="fs-12 mb-0">
                            {{ translate('messages.Here you setup website meta data for your business easy finding during search on website.') }}
                        </p>
                    </div>
                    <div class="row g-2">
                        <div class="col-xl-8 col-lg-7">
                            <div class="bg-light2 rounded">
                                <div class="card-body">

                                        <div class="" id="">
                                            <div class="form-group">
                                                <label class="input-label"
                                                    for="default_title">{{ translate('messages.meta_title') }}

                                                </label>
                                                <input maxlength="100" type="text" name="meta_title"
                                                    id="default_title" class="form-control"
                                                    placeholder="{{ translate('messages.meta_title') }}"
                                                    value="{{ $restaurant->getRawOriginal('meta_title') }}">
                                                <div class="d-flex justify-content-end">
                                                    <span class="text-body-light text-right d-block mt-1">0/160</span>
                                                </div>
                                            </div>

                                            <div class="form-group mb-0">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ translate('messages.meta_description') }}
                                                </label>
                                                <textarea maxlength="160" type="text" name="meta_description"
                                                    placeholder="{{ translate('messages.meta_description') }}" class="form-control min-h-90px ckeditor">{{ $restaurant->getRawOriginal('meta_description') }}</textarea>
                                                <div class="d-flex justify-content-end">
                                                    <span class="text-body-light text-right d-block mt-1">0/160</span>
                                                </div>
                                            </div>
                                        </div>


                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-5">
                            <div class="p-xxl-20 p-12 global-bg-box rounded h-100">
                                <div class="pb-lg-1">
                                    <div class="mb-4">
                                        <h5 class="mb-1">
                                            {{ translate('messages.Restaurant_Meta_Image') }}
                                        </h5>
                                        <p class="mb-0 fs-12 gray-dark">
                                            {{ translate('messages.Upload_your_website_favicon') }}</p>
                                    </div>
                                    <div class="text-center">
                                        <div class="upload-file mx-auto">
                                            <input type="file" name="meta_image"
                                                class="upload-file__input single_file_input"
                                                accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                            <label class="upload-file__wrapper ratio-1 mx-auto m-0">
                                                <div class="upload-file-textbox text-center" style="">
                                                    <img width="34" class="svg"
                                                        src="{{ dynamicAsset('assets/admin/img/image-upload.png') }}"
                                                        alt="img">
                                                    <h6 class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">
                                                        <span
                                                            class="text-info">{{ translate('messages.Click_to_upload') }}</span>
                                                        <br>
                                                        {{ translate('messages.or_drag_and_drop') }}
                                                    </h6>
                                                </div>
                                                <img class="upload-file-img" loading="lazy"
                                                    src="{{ $restaurant?->meta_image_full_url ?? dynamicAsset('assets/admin/img/upload.png') }}"
                                                    data-default-src="{{ $restaurant?->meta_image_full_url ?? dynamicAsset('assets/admin/img/upload.png') }}"
                                                    alt="" style="display: none;">
                                            </label>
                                            <div class="overlay">
                                                <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                                    <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                        <i class="tio-invisible"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                        <i class="tio-edit"></i>
                                                    </button>
                                                    {{--                                                    <button type="button" class="remove_btn btn icon-btn"> --}}
                                                    {{--                                                        <i class="tio-delete text-danger"></i> --}}
                                                    {{--                                                    </button> --}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="fs-10 text-center mb-0 mt-4">
                                        {{ translate('messages.JPG, JPEG, PNG, Gif Image size : Max 2 MB') }} <span
                                            class="font-medium text-title">({{ translate('messages.1:1') }})</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="btn--container justify-content-end">
                    <button type="reset" id="reset_btn"
                        class="btn btn--secondary min-w-120 location-reload">{{ translate('messages.Reset') }} </button>
                    <button type="submit" class="btn btn--primary">
                        <i class="tio-save"></i>
                        {{ translate('Save_Information') }}
                    </button>
                </div>
            </div>
        </form>
        @includeif('vendor-views.business-settings.partials._restaurant_schedule_data', $restaurant)
    </div>


@endsection

@push('script_2')
    <script src="{{ dynamicAsset('assets/admin') }}/js/tags-input.min.js"></script>
    <script src="{{ dynamicAsset('assets/admin') }}/js/fm.tagator.jquery.js"></script>
    <script>
        "use strict";

        $(document).ready(function() {

            $('#dine_in').on('change', function() {
                const $wrap = $('.custom-group-btn');
                if ($(this).is(':checked')) {
                    $('#schedule_advance_dine_in_booking_duration').prop('disabled', false);
                    $('#schedule_advance_dine_in_booking_duration_time_format').prop('disabled', false);
                    $wrap.tooltip('hide').tooltip('disable');
                    $wrap.removeAttr('data-original-title');

                } else {
                    $wrap.tooltip('enable');
                    $wrap.attr('data-original-title',
                        '{{ translate('messages.To active this field check the Dine-in order option') }}'
                        );
                    $('#schedule_advance_dine_in_booking_duration').prop('disabled', true);
                    $('#schedule_advance_dine_in_booking_duration_time_format').prop('disabled', true);
                }
            });

            $('#is_extra_packaging').on('change', function() {
                const $wrap = $('.extra-packaging-area');
                if ($(this).is(':checked')) {
                    $('#extra_packaging_amounts').prop('readonly', false);
                    $('#optional').prop('disabled', false);
                    $('#mandatory').prop('disabled', false);
                    $('#disable_warning').removeClass('disabled_warning');
                    $wrap.tooltip('hide').tooltip('disable');
                    $wrap.removeAttr('data-original-title');

                } else {
                    $wrap.tooltip('enable');
                    $wrap.attr('data-original-title',
                        '{{ translate('messages.To active this field turn on the feature order option') }}'
                        );
                    $('#extra_packaging_amounts').prop('readonly', true);
                    $('#optional').prop('disabled', true);
                    $('#mandatory').prop('disabled', true);
                    $('#disable_warning').addClass('disabled_warning');
                }
            });

        });

        $(document).ready(function() {
            initTextMaxLimit();

            $('input[name="meta_index"][value="noindex"]').on('change', function() {
                if ($(this).is(':checked')) {
                    $('input[name="meta_no_follow"]').prop('checked', true);
                    $('input[name="meta_no_image_index"]').prop('checked', true);
                    $('input[name="meta_no_archive"]').prop('checked', true);
                    $('input[name="meta_no_snippet"]').prop('checked', true);
                }
            });

            $('input[name="meta_index"][value="index"]').on('change', function() {
                if ($(this).is(':checked')) {
                    $('input[name="meta_no_follow"]').prop('checked', false);
                    $('input[name="meta_no_image_index"]').prop('checked', false);
                    $('input[name="meta_no_archive"]').prop('checked', false);
                    $('input[name="meta_no_snippet"]').prop('checked', false);
                }
            });
        });

        $(document).on('click', '.disabled_warning', function(event) {
            toastr.info('{{ translate('messages.extra_packaging_charge_is_disable') }}', {
                CloseButton: true,
                ProgressBar: true
            });
        });

        function call_limite_exceted() {
            toastr.info('{{ translate('You_can_add_max_5_Characteristics') }}', {
                CloseButton: true,
                ProgressBar: true
            });
        }

        $(document).on('click', '.delete-schedule', function() {
            let route = $(this).data('url');
            Swal.fire({
                title: '{{ translate('messages.Want_to_delete_this_day’s_schedule') }}',
                text: '{{ translate('messages.If_yes,_the_schedule_will_be_removed_from_here._However,_you_can_also_add_another_one.') }}',
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#377dff',
                cancelButtonText: '{{ translate('messages.no') }}',
                confirmButtonText: '{{ translate('messages.yes') }}',
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
                                    '{{ translate('messages.Schedule removed successfully') }}', {
                                        CloseButton: true,
                                        ProgressBar: true
                                    });
                            }
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            toastr.error('{{ translate('messages.Schedule not found') }}', {
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

        $("#customFileEg1").change(function() {
            readURL(this);
        });

        $(document).on('ready', function() {
            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function() {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });

            $("#gst_status").on('change', function() {
                if ($("#gst_status").is(':checked')) {
                    $('#gst').removeAttr('readonly');
                } else {
                    $('#gst').attr('readonly', true);
                }
            }).trigger('change');


            $("#customer_date_order_sratus").on('change', function() {
                if ($("#customer_date_order_sratus").is(':checked')) {
                    $('#customer_order_date').removeAttr('readonly');
                } else {
                    $('#customer_order_date').attr('readonly', true);
                }
            }).trigger('change');
        });

        $(document).on('click', '.offcanvas-trigger', function(e) {
            e.preventDefault();

            let day_name = $(this).data('day');
            let day_id = $(this).data('dayid');

            const $offcanvas = $('#offcanvasAddSchedule');

            $offcanvas.find('.custom-offcanvas-header h3')
                .text("{{ translate('messages.Create Schedule For ') }}" + day_name);

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
                url: '{{ route('vendor.business-settings.add-schedule') }}',
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
                        toastr.success('{{ translate('messages.Schedule added successfully') }}', {
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
                    url: "{{ route('vendor.business-settings.update-opening-closing-status', [$restaurant['id']]) }}",
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
                    confirmButtonText: "{{ translate('messages.yes') }}",
                    cancelButtonText: "{{ translate('messages.no') }}",
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
    <script>
        $(document).ready(function() {
            const $charInput = $('input[name="characteristics"]');
            const initialTags = $('input[name="tags"]').val();
            const initialCharacteristics = $charInput.val();

            let rawData = $charInput.attr('data-tagator-autocomplete');
            let autocompleteData = [];
            if (rawData) {
                rawData = rawData
                    .replace(/[\[\]']+/g, '')
                    .replace(/["]+/g, '')
                    .trim();

                autocompleteData = rawData.split(',').map(i => i.trim()).filter(i => i.length > 0);
            }

            function initializeCharacteristicsTagator() {
                $charInput.tagator({
                    autocomplete: autocompleteData,
                    showAllOptionsOnFocus: true
                });
            }

            initializeCharacteristicsTagator();

            $('#reset-btn').on('click', function(e) {
                e.preventDefault();

                const $tags = $('input[name="tags"]');
                $tags.tagsinput('removeAll');
                if (initialTags.trim() !== '') {
                    initialTags.split(',').forEach(tag => {
                        const t = tag.trim();
                        if (t !== '') $tags.tagsinput('add', t);
                    });
                }

                $charInput.tagator('destroy');
                $charInput.val(initialCharacteristics);
                initializeCharacteristicsTagator();

                setTimeout(() => $charInput.focus(), 50);
            });
        });
    </script>
@endpush
