@extends('layouts.admin.app')

@section('title', translate('Restaurant_setup'))


@section('content')
    <div class="content" id="restaurant_section">
        <form action="{{ route('admin.business-settings.update-restaurant') }}" method="post" enctype="multipart/form-data" id="restaurant-settings-form">
            @csrf
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="page-header pb-0">
                    <div class="d-flex flex-wrap justify-content-between align-items-start">
                        <h1 class="mb-0">{{ translate('messages.business_setup') }}</h1>
                        <div class="d-flex flex-wrap justify-content-end align-items-center flex-grow-1">
                            <div class="blinkings active">
                                <i class="tio-info text-gray1 fs-16"></i>
                                <div class="business-notes">
                                    <h6><img src="{{dynamicAsset('assets/admin/img/notes.png')}}" alt=""> {{translate('Note')}}</h6>
                                    <div>
                                        {{translate('Don’t_forget_to_click_the_respective_‘Save_Information’_buttons_below_to_save_changes')}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('admin-views.business-settings.partials.nav-menu')
                </div>

                <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info mb-3" style="--bs-bg-opacity: 0.1;">
                    <span class="text-info lh-1 fs-14">
                        <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                    </span>
                    <span>
                        {{ translate('messages.All Restaurant you can show & manage them from') }}
                        <a href="{{ route('admin.restaurant.list') }}" class="font-semibold text-primary text-underline">{{ translate('messages.Restaurants List') }} </a>
                        {{ translate('messages.page.') }}
                    </span>
                </div>

                <!-- Vendor Website Builder -->
                @php($admin_website_builder_status = App\CentralLogics\Helpers::get_business_settings('admin_website_builder_status'))
                <div class="card card-body mb-3" id="admin_website_builder_section">
                    <div class="row g-1 align-items-center">
                        <div class="col-xxl-9 col-lg-8 col-md-7 col-sm-6">
                            <div>
                                <h4 class="mb-1">{{ translate('Vendor Website Builder') }}</h4>
                                <p class="mb-0 fs-12">{{ translate('Enable this option to allow vendors to set up and manage their own website.') }}</p>
                            </div>
                        </div>
                        <div class="col-xxl-3 col-lg-4 col-md-5 col-sm-6">
                            <div class="form-group mb-0">
                                <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0">
                                    <span class="pr-1 d-flex align-items-center switch--label">
                                        <span class="line--limit-1">{{ translate('messages.Status') }}</span>
                                    </span>
                                    <input type="checkbox"
                                        data-id="admin_website_builder_status"
                                        data-type="toggle"
                                        data-title-on="<strong>{{ translate('Are you sure to enable vendor Website setup?') }}</strong>"
                                        data-title-off="<strong>{{ translate('Are you sure to disable vendor Website setup?') }}</strong>"
                                        data-text-on="<p>{{ translate('If enabled, vendors will have the freedom to create, edit, and manage their own websites independently.') }}</p>"
                                        data-text-off="<p>{{ translate('If disabled, vendors will not be able to create or manage their own websites.') }}</p>"
                                        class="status toggle-switch-input dynamic-checkbox-toggle"
                                        value="1"
                                        name="admin_website_builder_status" id="admin_website_builder_status"
                                        {{ $admin_website_builder_status == 1 ? 'checked' : '' }}>
                                    <span class="toggle-switch-label text">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                @php($default_location = \App\Models\BusinessSetting::where('key', 'default_location')->first())
                @php($default_location = $default_location->value ? json_decode($default_location->value, true) : 0)
                <div class="card card-body mb-3" id="basic_settings">
                    <div class="mb-20">
                        <h4 class="mb-1">{{ translate('messages.Basic_Settings') }}</h4>
                        <p class="fs-12 mb-0">{{ translate('messages.Setup your business time zone and format from here') }}.</p>
                    </div>
                    <div class="bg-light rounded-10 p-12 p-xxl-20 mb-20">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-4">
                                @php($toggle_restaurant_registration = App\CentralLogics\Helpers::get_business_settings('toggle_restaurant_registration'))
                                <div class="form-group mb-0">
                                    <label class="input-label d-flex align-items-center gap-1" for="toggle_restaurant_registration">
                                        {{ translate('messages.restaurant_self_registration') }}
                                        <span class="tio-info text-gray1 fs-16"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('If_enabled,_a_restaurant_can_send_a_registration_request_through_their_restaurant_or_customer_app,_website,_or_admin_landing_page._The_admin_will_receive_an_email_notification_and_can_accept_or_reject_the_request') }}">
                                        </span>
                                    </label>
                                    <label
                                        class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0">
                                        <span class="pr-1 d-flex align-items-center switch--label">
                                            {{ translate('messages.Status') }}
                                        </span>
                                        <input type="checkbox"

                                            data-id="toggle_restaurant_registration"
                                            data-type="toggle"
                                            data-image-on="{{ dynamicAsset('assets/admin/img/modal/store-self-reg-on.png') }}"
                                            data-image-off="{{ dynamicAsset('assets/admin/img/modal/store-self-reg-off.png') }}"
                                            data-title-on="{{ translate('Want_to_enable') }} <strong>{{ translate('restaurant Self Registration') }}</strong> ?"
                                            data-title-off="{{ translate('Want_to_disable') }} <strong>{{ translate('restaurant Self Registration') }}</strong> ?"
                                            data-text-on="<p>{{ translate('If_enabled,_restaurants_can_do_self-registration_from_the_restaurant_or_customer_app_or_website') }}</p>"
                                            data-text-off="<p>{{ translate('If_disabled,_the_restaurant_Self-Registration_feature_will_be_hidden_from_the_restaurant_or_customer_app,_website,_and_admin_landing_page') }}</p>"
                                            class="toggle-switch-input dynamic-checkbox-toggle"

                                            value="1"
                                            name="toggle_restaurant_registration" id="toggle_restaurant_registration"
                                            {{ $toggle_restaurant_registration == 1 ? 'checked' : '' }}>
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                @php($restaurant_review_reply = \App\Models\BusinessSetting::where('key', 'restaurant_review_reply')->first())
                                @php($restaurant_review_reply = $restaurant_review_reply ? $restaurant_review_reply->value : 0)
                                <div class="form-group mb-0">
                                    <label class="input-label d-flex align-items-center gap-1" for="restaurant_review_reply1">
                                        {{ translate('messages.Restaurant_Can_Reply_Review') }}
                                        <span class="tio-info text-gray1 fs-16"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('If_enabled,_a_restaurant_can_reply_to_a_review') }}">
                                        </span>
                                    </label>
                                    <label
                                        class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0">
                                        <span class="pr-1 d-flex align-items-center switch--label">
                                            {{ translate('messages.Status') }}
                                        </span>
                                        <input type="checkbox"

                                        data-id="restaurant_review_reply1"
                                        data-type="toggle"
                                        data-image-on="{{ dynamicAsset('assets/admin/img/modal/store-self-reg-on.png') }}"
                                        data-image-off="{{ dynamicAsset('assets/admin/img/modal/store-self-reg-off.png') }}"
                                        data-title-on="{{ translate('Want_to_enable') }} <strong>{{ translate('restaurant Reply Review') }}</strong> ?"
                                        data-title-off="{{ translate('Want_to_disable') }} <strong>{{ translate('restaurant Reply Review') }}</strong> ?"
                                        data-text-on="<p>{{ translate('If_enabled,_a_restaurant_can_reply_to_a_review') }}</p>"
                                        data-text-off="<p>{{ translate('If_disabled,_a_restaurant_can_not_reply_to_a_review') }}</p>"
                                        class="toggle-switch-input dynamic-checkbox-toggle"

                                        value="1"
                                            name="restaurant_review_reply" id="restaurant_review_reply1"
                                            {{ $restaurant_review_reply == 1 ? 'checked' : '' }}>
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                @php($extra_packaging_charge = \App\Models\BusinessSetting::where('key', 'extra_packaging_charge')->first())
                                @php($extra_packaging_charge = $extra_packaging_charge ? $extra_packaging_charge->value : 0)
                                <div class="form-group mb-0">
                                    <label class="input-label d-flex align-items-center gap-1" for="extra_packaging_charge">
                                        {{ translate('messages.Extra_Packaging_Charge') }}
                                        <span class="tio-info text-gray1 fs-16"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('With_this_feature,_restaurant_will_get_the_option_to_offer_extra_packaging_charge_to_the_customer.') }}">
                                        </span>
                                    </label>
                                    <label
                                        class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0">
                                        <span class="pr-1 d-flex align-items-center switch--label">
                                            {{ translate('messages.Status') }}
                                        </span>
                                        <input type="checkbox"

                                            data-id="extra_packaging_charge"
                                            data-type="toggle"
                                            data-image-on="{{ dynamicAsset('assets/admin/img/modal/veg-on.png') }}"
                                            data-image-off="{{ dynamicAsset('assets/admin/img/modal/veg-off.png') }}"
                                            data-title-on="{{ translate('want_to_enable') }} <strong>{{ translate('extra_packaging_charge') }}</strong>?"
                                            data-title-off="{{ translate('want_to_disable') }} <strong>{{ translate('extra_packaging_charge') }}</strong>?"
                                            data-text-on="<p>{{ translate('if_enabled,_restaurant_will_get_the_option_to_offer_extra_packaging_charge_to_the_customer') }}</p>"
                                            data-text-off="<p>{{ translate('if_disabled,_restaurant_will_not_get_the_option_to_offer_extra_packaging_charge_to_the_customer') }}</p>"
                                            class="toggle-switch-input dynamic-checkbox-toggle"

                                            value="1"
                                            name="extra_packaging_charge" id="extra_packaging_charge"
                                            {{ $extra_packaging_charge == 1 ? 'checked' : '' }}>
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                @php($verified_seller_badge = \App\CentralLogics\Helpers::get_business_settings('verified_seller_badge'))
                                <div class="form-group mb-0">
                                    <label class="input-label d-flex align-items-center gap-1" for="verified_seller_badge">
                                        {{ translate('show verified badge') }}
                                        <span class="tio-info text-gray1 fs-16"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('This feature enables the admin to grant a verified badge to vendors who fulfill the required criteria.') }}">
                                        </span>
                                    </label>
                                    <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0">
                                        <span class="pr-1 d-flex align-items-center switch--label">
                                            {{ translate('messages.Status') }}
                                        </span>
                                        <input type="checkbox"
                                            data-id="verified_seller_badge"
                                            data-type="toggle"
                                            data-image-on="{{ dynamicAsset('assets/admin/img/badge-big.png') }}"
                                            data-image-off="{{ dynamicAsset('assets/admin/img/remove-badge.png') }}"
                                            data-title-on="{{ translate('want_to_enable') }} <strong>{{ translate('verified_seller_badge') }}</strong>?"
                                            data-title-off="{{ translate('want_to_disable') }} <strong>{{ translate('verified_seller_badge') }}</strong>?"
                                            data-text-on="<p>{{ translate('if_enabled,_restaurant_owners_can_receive_a_verified_seller_badge') }}</p>"
                                            data-text-off="<p>{{ translate('if_disabled,_The_badege_will_not_be_available_for_restaurant_owners') }}</p>"
                                            class="toggle-switch-input dynamic-checkbox-toggle"

                                        value="1" name="verified_seller_badge" id="verified_seller_badge"
                                            {{ $verified_seller_badge == 1 ? 'checked' : '' }}>
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-8" >
                                <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-warning bg-opacity-10">
                                    <span class="text-warning lh-1 fs-14">
                                        <i class="tio-info"></i>
                                    </span>
                                    <span>
                                        {{ translate('You can add additional fields in Registration Form from ') }} <a href="{{ route('admin.business-settings.restaurant_page_setup') }}" class="font-semibold text-info text-underline" target="_blank" rel="noopener noreferrer">{{ translate('Join us page Setup') }}</a> {{ translate('page.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @php($restaurant_category_status = \App\CentralLogics\Helpers::get_business_settings('restaurant_category_status'))
                <div class="card card-body mb-3">
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <div class="">
                                <h4 class="mb-1">{{ translate('messages.Restaurant Category') }}</h4>
                                <p class="fs-12 mb-0">{{ translate('messages.Enable this option to allow vendors to create and manage their own categories.') }}.</p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <label
                                class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    {{ translate('messages.Status') }}
                                </span>
                                <input type="checkbox"
                                    data-id="restaurant_category_status"
                                    data-type="toggle"
                                    data-image-on="{{ dynamicAsset('assets/admin/img/modal/warning.png') }}"
                                    data-image-off="{{ dynamicAsset('assets/admin/img/modal/warning.png') }}"
                                    data-title-on="{{ translate('Want_to_enable') }} <strong>{{ translate('Restaurant Category') }}</strong> ?"
                                    data-title-off="{{ translate('Want_to_disable') }} <strong>{{ translate('Restaurant Category') }}</strong> ?"
                                    data-text-on="<p>{{ translate('If_enabled,_restaurants_can_create_and_manage_their_own_restaurant_categories_and_assign_them_to_foods.') }}</p>"
                                    data-text-off="<p>{{ translate('If_disabled,_restaurants_can_not_manage_restaurant_categories_and_foods_will_ignore_this_field.') }}</p>"
                                    class="toggle-switch-input dynamic-checkbox-toggle"
                                    value="1"
                                    name="restaurant_category_status" id="restaurant_category_status"
                                    {{ $restaurant_category_status == 1 ? 'checked' : '' }}>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                @if (addon_published_status('ReelsModule'))
                    @include('admin-views.business-settings.partials._reels-settings')
                @endif
                <div class="card card-body mb-3" id="cash_in_hand">
                    <div class="mb-20">
                        <h4 class="mb-1">{{ translate('messages.Cash in Hand Controls') }}</h4>
                        <p class="fs-12 mb-0">{{ translate('messages.Setup your cash collection from here') }}.</p>
                    </div>
                    <div class="bg-light rounded-10 p-12 p-xxl-20 mb-20">
                        <div class="row g-3">
                            <div class="col-lg-4">
                                @php($cash_in_hand_overflow = \App\Models\BusinessSetting::where('key', 'cash_in_hand_overflow_restaurant')->first())
                                @php($cash_in_hand_overflow = $cash_in_hand_overflow ? $cash_in_hand_overflow->value : 0)
                                <div class="form-group mb-0">
                                    <label class="input-label d-flex align-items-center gap-1" for="cash_in_hand_overflow">
                                        {{ translate('messages.Suspend_on_Cash_In_Hand_Overflow') }}
                                        <span class="tio-info text-gray1 fs-16"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('If enabled  restaurants will be automatically suspended by the system when their ‘Cash in Hand’ limit is exceeded.') }}">
                                        </span>
                                    </label>
                                    <label
                                        class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                        <span class="pr-1 d-flex align-items-center switch--label">
                                            {{ translate('messages.Status') }}
                                        </span>
                                        <input type="checkbox"

                                        data-id="cash_in_hand_overflow"
                                            data-type="toggle"
                                            data-image-on="{{ dynamicAsset('assets/admin/img/modal/show-earning-in-apps-on.png') }}"
                                            data-image-off="{{ dynamicAsset('assets/admin/img/modal/show-earning-in-apps-off.png') }}"
                                            data-title-on="{{ translate('Want_to_enable') }} <br> <strong>{{ translate('Cash_In_Hand_Overflow') }}</strong> ?"
                                            data-title-off="{{ translate('Want_to_disable') }} <br> <strong>{{ translate('Cash_In_Hand_Overflow') }}</strong>  ?"
                                            data-text-on="<p>{{ translate('If_enabled,_restaurants_have_to_provide_collected_cash_by_them_self') }}</p>"
                                            data-text-off="<p>{{ translate('If_disabled,_restaurants_do_not_have_to_provide_collected_cash_by_them_self') }}</p>"
                                            class="toggle-switch-input dynamic-checkbox-toggle"


                                        value="1"
                                            name="cash_in_hand_overflow_restaurant" id="cash_in_hand_overflow"
                                            {{ $cash_in_hand_overflow == 1 ? 'checked' : '' }}>
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                @php($cash_in_hand_overflow_restaurant_amount = \App\Models\BusinessSetting::where('key', 'cash_in_hand_overflow_restaurant_amount')->first())
                                <div class="form-group mb-0">
                                    <label class="input-label d-flex align-items-center gap-1" for="cash_in_hand_overflow_restaurant_amount">
                                        {{ translate('Cash_In_hand_Max_Amount') }} ({{ App\CentralLogics\Helpers::currency_symbol() }})
                                        <span class="tio-info text-gray1 fs-16"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('Enter_the_maximum_cash_amount_restaurants_can_hold._If_this_number_exceeds,_restaurants_will_be_suspended_and_not_receive_any_orders.') }}">
                                        </span>
                                    </label>
                                    <input type="number" name="cash_in_hand_overflow_restaurant_amount" class="form-control"
                                        id="cash_in_hand_overflow_restaurant_amount" min="0" step=".001"
                                        value="{{ $cash_in_hand_overflow_restaurant_amount ? $cash_in_hand_overflow_restaurant_amount->value : '' }}"  {{ $cash_in_hand_overflow  == 1 ? 'required' : 'readonly' }} >
                                    <p class="text-info fs-12 mb-0" id="validation-error-message" style="display: none;">{{ translate('messages.Amount must be greater then Minimum Payable Amount') }}</p>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                @php($min_amount_to_pay_restaurant = \App\Models\BusinessSetting::where('key', 'min_amount_to_pay_restaurant')->first())
                                <div class="form-group mb-0">
                                    <label class="input-label d-flex align-items-center gap-1" for="min_amount_to_pay_restaurant">
                                        {{ translate('Minimum_Payable_Amount') }} ({{ App\CentralLogics\Helpers::currency_symbol() }})
                                        <span class="tio-info text-gray1 fs-16"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('Enter_the_minimum_cash_amount_restaurants_can_pay') }}">
                                        </span>
                                    </label>
                                    <input type="number" name="min_amount_to_pay_restaurant" class="form-control"
                                        id="min_amount_to_pay_restaurant" min="0" step=".001"
                                        value="{{ $min_amount_to_pay_restaurant ? $min_amount_to_pay_restaurant->value : '' }}"  {{ $cash_in_hand_overflow  == 1 ? 'required' : 'readonly' }} >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info" style="--bs-bg-opacity: 0.1;">
                        <span class="text-info lh-1 fs-14">
                            <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                        </span>
                        <span>
                            {{ translate('messages.To setup restaurant cash withdraw method visit') }}
                            <a href="{{ route('admin.business-settings.withdraw-method.list') }}" class="font-semibold text-primary text-underline">{{ translate('messages.Withdraw Method List') }} </a>
                            {{ translate('messages.page.') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="footer-sticky mt-2">
                <div class="container-fluid">
                <div class="d-flex flex-wrap gap-3 justify-content-center py-3">
                    <button type="reset" class="btn btn--secondary min-w-120">{{ translate('messages.Reset') }} </button>
                    <button type="{{ getEnvMode() != 'demo' ? 'submit' : 'button' }}" class="btn btn--primary call-demo">
                        <i class="tio-save"></i>
                        {{ translate('Save_Information') }}
                    </button>
                </div>
                </div>
            </div>
        </form>
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
                    <h3 class="mb-0">{{ translate('messages.Restaurant Guideline') }}</h3>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;</button>
                </div>
                <div class="custom-offcanvas-body offcanvas-height-100 py-3 px-md-4 px-3">
                    <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                        <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                            <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                                type="button" data-toggle="collapse" data-target="#basic_settings_guide"
                                aria-expanded="true">
                                <div
                                    class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                                    <i class="tio-down-ui"></i>
                                </div>
                                <span
                                    class="font-semibold text-left fs-14 text-title">{{ translate('messages.Basic Settings') }}</span>
                            </button>
                            <a href="#basic_settings"
                                class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                        </div>
                        <div class="collapse show mt-3" id="basic_settings_guide">
                            <div class="card card-body">
                                <div class="">
                                    <h5 class="mb-3">{{translate('messages.Basic Settings')}}</h5>
                                    <p class="fs-12 mb-3">
                                        {{ translate('messages.Manage restaurant registration availability, control whether restaurants can reply to customer reviews, configure additional packaging charges, and define whether taxes are applied inclusively or exclusively. These essential settings are managed at the restaurant level.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                        <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                            <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                                type="button" data-toggle="collapse" data-target="#cash_in_hand_guide"
                                aria-expanded="true">
                                <div
                                    class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                                    <i class="tio-down-ui"></i>
                                </div>
                                <span
                                    class="font-semibold text-left fs-14 text-title">{{ translate('messages.Cash-in-Hand Controls') }}</span>
                            </button>
                            <a href="#cash_in_hand"
                                class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                        </div>
                        <div class="collapse mt-3" id="cash_in_hand_guide">
                            <div class="card card-body">
                                <div class="">
                                    <h5 class="mb-3">{{translate('messages.Cash-in-Hand Controls')}}</h5>
                                    <p class="fs-12 mb-3">
                                        {{ translate('messages.Cash-in-hand control allows the platform to monitor and limit the amount of cash collected by restaurants from Cash on Delivery (COD) orders. This feature helps reduce financial risk and ensures timely settlement between restaurants and the platform.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        $(document).on('ready', function () {


            function checkCashInHand() {
                let maxAmount = parseFloat($('#cash_in_hand_overflow_restaurant_amount').val());
                let minAmount = parseFloat($('#min_amount_to_pay_restaurant').val());

                if (maxAmount && minAmount && maxAmount <= minAmount) {
                    $('#validation-error-message').show();
                } else {
                    $('#validation-error-message').hide();
                }
            }

            $('#cash_in_hand_overflow_restaurant_amount, #min_amount_to_pay_restaurant').on('keyup change', function () {
                checkCashInHand();
            });

            $('#restaurant-settings-form').on('submit', function (e) {
                let maxAmount = parseFloat($('#cash_in_hand_overflow_restaurant_amount').val());
                let minAmount = parseFloat($('#min_amount_to_pay_restaurant').val());

                if (maxAmount && minAmount && maxAmount <= minAmount) {
                    e.preventDefault();
                    toastr.error("{{ translate('messages.Amount must be greater then Minimum Payable Amount') }}");
                    $('#validation-error-message').show();
                    $('html, body').animate({
                        scrollTop: $("#cash_in_hand_overflow_restaurant_amount").offset().top - 100
                    }, 500);
                }
            });

            // Initial check
            checkCashInHand();
        });

        $(".collapse-div-toggler").on('change', function () {
            if ($(this).val() == '0') {
                $(this).closest('.sorting-card').find('.inner-collapse-div').slideDown();
            } else {
                $(this).closest('.sorting-card').find('.inner-collapse-div').slideUp();
            }
        });

        $(window).on('load', function () {
            $('.collapse-div-toggler').each(function () {
                if ($(this).prop('checked') == true && $(this).val() == '0') {
                    $(this).closest('.sorting-card').find('.inner-collapse-div').show();
                } else if ($(this).prop('checked') == true && $(this).val() == '1') {
                    $(this).closest('.sorting-card').find('.inner-collapse-div').hide();
                }
            });
        })


        $('.offcanvas-close-btn').on('click', function (e) {
            e.preventDefault();
            $('.custom-offcanvas').removeClass('open');
            $('#offcanvasOverlay').removeClass('show');
            $('html, body').animate({
                scrollTop: $($(this).attr('href')).offset().top - 100
            }, 500);
        });
    </script>
@endpush
