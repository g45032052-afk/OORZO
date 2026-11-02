@extends('layouts.admin.app')

@section('title', translate('messages.Delivery_man_settings'))

@section('content')
    <div class="content" id="deliveryman_section">
        <form action="{{ route('admin.business-settings.update-dm') }}" method="post" enctype="multipart/form-data">
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
                        {{ translate('messages.All Deliveryman you can show & manage them from') }}
                        <a href="{{route('admin.delivery-man.list')}}" class="font-semibold text-primary text-underline">{{ translate('messages.Deliveryman List') }} </a>
                        {{ translate('messages.page.') }}
                    </span>
                </div>

                <div class="card card-body mb-3" id="basic_setup">
                    <div class="mb-20">
                        <h4 class="mb-1">{{ translate('messages.Basic_Setup') }}</h4>
                        <p class="fs-12 mb-0">{{ translate('messages.Manage the Deliveryman registration & order related setup from here') }}</p>
                    </div>
                    <div class="bg-light rounded-10 p-12 p-xxl-20 mb-20">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                @php($dm_self_registration = \App\Models\BusinessSetting::where('key', 'toggle_dm_registration')->first())
                                @php($dm_self_registration = $dm_self_registration ? $dm_self_registration->value : 0)
                                <div class="form-group mb-0">
                                    <label class="input-label d-flex align-items-center gap-1" for="dm_self_registration1">
                                        {{ translate('messages.Deliveryman_Self_Registration') }}
                                        <span class="tio-info text-gray1 fs-16"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('With_this_feature,_deliverymen_can_register_themselves_from_the_Customer_App,_Website,_Deliveryman_App_or_Admin_Landing_Page._The_admin_will_receive_an_email_notification_and_can_accept_or_reject_the_request') }}">
                                        </span>
                                    </label>
                                    <label
                                        class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                        <span class="pr-1 d-flex align-items-center switch--label">
                                            {{ translate('messages.Status') }}
                                        </span>
                                        <input type="checkbox"
                                        data-id="dm_self_registration1"
                                        data-type="toggle"
                                        data-image-on="{{ dynamicAsset('assets/admin/img/modal/dm-self-reg-on.png') }}"
                                        data-image-off="{{ dynamicAsset('assets/admin/img/modal/dm-self-reg-off.png') }}"
                                        data-title-on="{{ translate('Want_to_enable_the') }} <strong>{{ translate('DeliveryMan_Self_Registration') }}</strong> ?"
                                        data-title-off="{{ translate('Want_to_disable_the') }} <strong>{{ translate('DeliveryMan_Self_Registration') }}</strong> ?"
                                        data-text-on="<p>{{ translate('If_enabled,_Customers_can_register_as_Deliverymen_from_the_Customer_App,_Website_or_Deliveryman_App_or_Admin_Landing_Page') }}</p>"
                                        data-text-off="<p>{{ translate('If_disabled,_this_feature_will_be_hidden_from_the_Customer_App,_Website,_Deliveryman_App_or_Admin_Landing_Page.') }}</p>"
                                        class="toggle-switch-input dynamic-checkbox-toggle"
                                        value="1"
                                            name="dm_self_registration" id="dm_self_registration1"
                                            {{ $dm_self_registration == 1 ? 'checked' : '' }}>
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                @php($dm_maximum_orders = \App\Models\BusinessSetting::where('key', 'dm_maximum_orders')->first())
                                <div class="form-group mb-0">
                                    <label class="input-label d-flex align-items-center gap-1" for="dm_maximum_orders">
                                        {{ translate('messages.Maximum_Assigned_Order_Limit') }}
                                        <span class="tio-info text-gray1 fs-16"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages.Set_the_maximum_order_limit_a_Deliveryman_can_take_at_a_time') }}">
                                        </span>
                                    </label>
                                    <input type="number" name="dm_maximum_orders" class="form-control"
                                        id="dm_maximum_orders" min="1"
                                        value="{{ $dm_maximum_orders ? $dm_maximum_orders->value : 1 }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-warning" style="--bs-bg-opacity: 0.1;">
                        <span class="text-warning lh-1 fs-14">
                            <i class="tio-info"></i>
                        </span>
                        <span>
                            {{ translate('messages.You may setup Registration Form from') }}
                            <a href="{{route('deliveryman.create')}}" class="font-semibold text-primary text-underline">{{ translate('messages.Deliveryman Registration Form') }} </a>
                            {{ translate('messages.page to work properly.') }}
                        </span>
                    </div>
                </div>

                <div class="card card-body mb-3">
                    <div class="row g-3 mb-20">
                        <div class="col-lg-8">
                            <div>
                                <h4 class="mb-1">{{ translate('messages.Tips For Deliveryman') }}</h4>
                                <p class="fs-12 mb-0">{{ translate('messages.If enabled, Customer get the option to give tips to deliveryman during checkout from Customer App/Website') }}.</p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                             @php($dm_tips_status = \App\Models\BusinessSetting::where('key', 'dm_tips_status')->first())
                            @php($dm_tips_status = $dm_tips_status ? $dm_tips_status->value : 'deliveryman')
                            <label
                                class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    {{ translate('messages.Status') }}
                                </span>
                                <input type="checkbox"
                                    data-id="dm_tips_status"
                                    data-type="toggle"
                                    data-image-on="{{ dynamicAsset('assets/admin/img/modal/dm-tips-on.png') }}"
                                    data-image-off="{{ dynamicAsset('assets/admin/img/modal/dm-tips-off.png') }}"
                                    data-title-on="{{ translate('Want_to_enable_the') }} <strong>{{ translate('Delivery Man Tips') }}</strong> {{ translate('feature') }} ?"
                                    data-title-off="{{ translate('Want_to_disable_the') }} <strong>{{ translate('Delivery Man Tips') }}</strong> {{ translate('feature') }} ?"
                                    data-text-on="<p>{{ translate('If_enabled,_Customers_can_give_tips_to_a_deliveryman_during_checkout') }}</p>"
                                    data-text-off="<p>{{ translate('If_disabled,_the_Tips_for_Deliveryman_feature_will_be_hidden_from_the_Customer_App_and_Website') }}</p>"
                                    class="toggle-switch-input dynamic-checkbox-toggle"
                                    value="1"
                                    name="dm_tips_status" id="dm_tips_status"
                                    {{ $dm_tips_status == '1' ? 'checked' : '' }}>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-warning mb-2" style="--bs-bg-opacity: 0.1;">
                        <span class="text-warning lh-1 fs-14">
                            <i class="tio-info"></i>
                        </span>
                        <span>
                            {{ translate('messages.Admin will not earn any commission from the') }}
                            <span class="font-semibold">{{ translate('messages.Tips For Delivery.') }} </span>
                        </span>
                    </div>
                </div>

                <div class="card card-body mb-3" id="deliveryman_app_setup">
                    <div class="mb-20">
                        <h4 class="mb-1">{{ translate('messages.Deliveryman App Setup') }}</h4>
                        <p class="fs-12 mb-0">{{ translate('messages.Here you can manage the settings related to Deliveryman App') }}</p>
                    </div>
                    <div class="bg-light rounded-10 p-12 p-xxl-20 mb-20">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                @php($show_dm_earning = \App\Models\BusinessSetting::where('key', 'show_dm_earning')->first())
                                @php($show_dm_earning = $show_dm_earning ? $show_dm_earning->value : 0)
                                <div class="form-group mb-0">
                                    <label class="input-label d-flex align-items-center gap-1" for="show_dm_earning">
                                        {{ translate('messages.Show Earnings in App') }}
                                        <span class="tio-info text-gray1 fs-16"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('With_this_feature,_Deliverymen_can_see_their_earnings_on_a_specific_order_while_accepting_it.') }}"
                                        >
                                        </span>
                                    </label>
                                    <label
                                        class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0">
                                        <span class="pr-1 d-flex align-items-center switch--label">
                                            {{ translate('messages.Status') }}
                                        </span>
                                        <input type="checkbox"
                                            data-id="show_dm_earning"
                                            data-type="toggle"
                                            data-image-on="{{ dynamicAsset('assets/admin/img/modal/show-earning-in-apps-on.png') }}"
                                            data-image-off="{{ dynamicAsset('assets/admin/img/modal/show-earning-in-apps-off.png') }}"
                                            data-title-on="{{ translate('Want_to_enable_the') }} <strong>{{ translate('Show Earnings in Apps') }}</strong> ?"
                                            data-title-off="{{ translate('Want_to_disable_the') }} <strong>{{ translate('Show Earnings in Apps') }}</strong> ?"
                                            data-text-on="<p>{{ translate('If_enabled,_Deliverymen_can_see_their_earning_per_order_request_from_the_Order_Details_page_in_the_Deliveryman_App.') }}</p>"
                                            data-text-off="<p>{{ translate('If_disabled,_the_feature_will_be_hidden_from_the_Deliveryman_App') }}</p>"
                                            class="toggle-switch-input dynamic-checkbox-toggle"
                                            value="1"
                                            name="show_dm_earning" id="show_dm_earning"
                                            {{ $show_dm_earning == 1 ? 'checked' : '' }}>
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                    <p class="fs-12 mt-1 mb-0">
                                        {{ translate('messages.To collect cash from delivery man visit') }}
                                        <a href="{{route('admin.account-transaction.index')}}" class="font-semibold text-info text-underline">{{ translate('messages.Collect Cash') }}</a>.
                                    </p>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                @php($dm_picture_upload_status = \App\Models\BusinessSetting::where('key', 'dm_picture_upload_status')->first())
                                <div class="form-group  mb-0">
                                    <label class="input-label d-flex align-items-center gap-1" for="dm_picture_upload_status">
                                        {{ translate('messages.Take Picture for Delivery Proof') }}
                                        <span class="tio-info text-gray1 fs-16"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages.If_enabled,_deliverymen_will_see_an_option_to_take_pictures_of_the_delivered_products_when_he_swipes_the_delivery_confirmation_slide.') }}">
                                        </span>
                                    </label>
                                    <label
                                        class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 mb-2 form-control mb-0">
                                        <span class="pr-1 d-flex align-items-center switch--label">
                                            {{ translate('messages.Status') }}
                                        </span>
                                        <input type="checkbox"
                                            data-id="dm_picture_upload_status"
                                            data-type="toggle"
                                            data-image-on="{{ dynamicAsset('assets/admin/img/modal/dm-self-reg-on.png') }}"
                                            data-image-off="{{ dynamicAsset('assets/admin/img/modal/dm-self-reg-off.png') }}"
                                            data-title-on="{{ translate('messages.Want_to_enable') }} <strong>{{ translate('messages.picture_upload_before_complete?') }}</strong>"
                                            data-title-off="{{ translate('messages.Want_to_disable') }} <strong>{{ translate('messages.picture_upload_before_complete?') }}</strong>"
                                            data-text-on="<p>{{ translate('messages.If_you_enable_this,_delivery_man_can_upload_order_proof_before_order_delivery.') }}</p>"
                                            data-text-off="<p>{{ translate('messages.If_you_disable_this,_this_feature_will_be_hidden_from_the_delivery_man_app.') }}</p>"
                                            class="toggle-switch-input dynamic-checkbox-toggle"
                                            value="1"
                                            name="dm_picture_upload_status" id="dm_picture_upload_status"
                                        {{ $dm_picture_upload_status?->value == 1 ? 'checked' : '' }}>
                                    <span class="toggle-switch-label text">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-body mb-3" id="cash_in_hand">
                    <div class="mb-20">
                        <h4 class="mb-1">{{ translate('messages.Cash in Hand Controls') }}</h4>
                        <p class="fs-12 mb-0">{{ translate('messages.Manage the Cash in Hand balance of Deliveryman from here') }}</p>
                    </div>
                    <div class="bg-light rounded-10 p-12 p-xxl-20">
                        <div class="row g-3">
                            <div class="col-lg-4">
                                @php($cash_in_hand_overflow = \App\Models\BusinessSetting::where('key', 'cash_in_hand_overflow_delivery_man')->first())
                                @php($cash_in_hand_overflow = $cash_in_hand_overflow ? $cash_in_hand_overflow->value : 0)
                                <div class="form-group mb-0">
                                    <label class="input-label d-flex align-items-center gap-1" for="cash_in_hand_overflow">
                                        {{ translate('messages.Suspend_on_Cash_In_Hand_Overflow') }}
                                        <span class="tio-info text-gray1 fs-16"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages._delivery_men_will_be_automatically_suspended_by_the_system_when_their_‘Cash_in_Hand’_limit_is_exceeded.') }}">
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
                                                data-title-on="{{ translate('Want_to_enable') }} <strong>{{ translate('Cash_In_Hand_Overflow') }}</strong> ?"
                                                data-title-off="{{ translate('Want_to_disable') }} <strong>{{ translate('Cash_In_Hand_Overflow') }}</strong> ?"
                                                data-text-on="<p>{{ translate('If_enabled,_delivery_men_have_to_provide_collected_cash_by_them_self') }}</p>"
                                                data-text-off="<p>{{ translate('If_disabled,_delivery_men_do_not_have_to_provide_collected_cash_by_them_self') }}</p>"
                                                class="toggle-switch-input dynamic-checkbox-toggle"


                                                value="1"
                                                    name="cash_in_hand_overflow_delivery_man" id="cash_in_hand_overflow"
                                                    {{ $cash_in_hand_overflow == 1 ? 'checked' : '' }}>
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                               @php($dm_max_cash_in_hand = \App\Models\BusinessSetting::where('key', 'dm_max_cash_in_hand')->first())
                                <div class="form-group mb-0">
                                    <label class="input-label d-flex align-items-center gap-1" for="dm_max_cash_in_hand">
                                        {{translate('Max Amount to Hold Cash')}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                        <span class="tio-info text-gray1 fs-16"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('Deliveryman_can_not_accept_any_orders_when_the_Cash_In_Hand_limit_exceeds_and_must_deposit_the_amount_to_the_admin_before_accepting_new_orders') }}">
                                        </span>
                                    </label>
                                    <input type="number" name="dm_max_cash_in_hand" class="form-control"
                                        id="dm_max_cash_in_hand" min="0" step=".001"
                                        value="{{ $dm_max_cash_in_hand ? $dm_max_cash_in_hand->value : 0 }}" {{ $cash_in_hand_overflow  == 1 ? 'required' : 'readonly' }}>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                @php($min_amount_to_pay_dm = \App\Models\BusinessSetting::where('key', 'min_amount_to_pay_dm')->first())
                                <div class="form-group mb-0">
                                    <label class="input-label d-flex align-items-center gap-1" for="min_amount_to_pay_dm">
                                        {{ translate('Minimum_Amount_To_Pay') }} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                        <span class="tio-info text-gray1 fs-16"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages.Enter_the_minimum_cash_amount_delivery_men_can_pay') }}">
                                        </span>
                                    </label>
                                     <input type="number" name="min_amount_to_pay_dm" class="form-control"
                                        id="min_amount_to_pay_dm" min="0" step=".001"
                                        value="{{ $min_amount_to_pay_dm ? $min_amount_to_pay_dm->value : '' }}"  {{ $cash_in_hand_overflow  == 1 ? 'required' : 'readonly' }} >
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
                    <button type="submit" id="submit" class="btn btn--primary">
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
                    <h3 class="mb-0">{{ translate('messages.Deliveryman Guideline') }}</h3>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;</button>
                </div>
                <div class="custom-offcanvas-body offcanvas-height-100 py-3 px-md-4 px-3">
                    <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                        <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                            <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                                type="button" data-toggle="collapse" data-target="#basic_setup_guide"
                                aria-expanded="true">
                                <div
                                    class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                                    <i class="tio-down-ui"></i>
                                </div>
                                <span
                                    class="font-semibold text-left fs-14 text-title">{{ translate('messages.Basic Setup') }}</span>
                            </button>
                            <a href="#basic_setup"
                                class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                        </div>
                        <div class="collapse show mt-3" id="basic_setup_guide">
                            <div class="card card-body">
                                <div class="">
                                    <h5 class="mb-3">{{translate('messages.Basic Setup')}}</h5>
                                    <p class="fs-12 mb-3">
                                        {{ translate('messages.Manage deliveryman registration availability, set order limits for deliveries, control whether deliverymen can receive tips from customers, and configure other essential settings at the deliveryman level.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                        <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                            <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                                type="button" data-toggle="collapse" data-target="#deliveryman_app_setup_guide"
                                aria-expanded="true">
                                <div
                                    class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                                    <i class="tio-down-ui"></i>
                                </div>
                                <span
                                    class="font-semibold text-left fs-14 text-title">{{ translate('messages.Deliveryman App Setup') }}</span>
                            </button>
                            <a href="#deliveryman_app_setup"
                                class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                        </div>
                        <div class="collapse mt-3" id="deliveryman_app_setup_guide">
                            <div class="card card-body">
                                <div class="">
                                    <h5 class="mb-3">{{translate('messages.Deliveryman App Setup')}}</h5>
                                    <p class="fs-12 mb-3">
                                        {{ translate('messages.The Deliveryman App Setup feature allows the platform administrator to configure key settings that define how delivery personnel interact with the mobile app., whether deliverymen can view their earnings in the app, and whether capturing a photo is required as delivery proof.') }}
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
                                        {{ translate('messages.Cash-in-hand control allows the platform to monitor and limit the amount of cash collected by deliverymen from Cash on Delivery (COD) orders. This feature helps reduce financial risk and ensures timely settlement between the deliveryman and the platform.') }}
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
