@extends('layouts.admin.app')

@section('title', translate('messages.customer_settings'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content">
        <form action="{{ route('admin.customer.update-settings') }}" method="post" enctype="multipart/form-data"
            id="update-settings">
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
                                    <h6><img src="{{dynamicAsset('assets/admin/img/notes.png')}}" alt="">
                                        {{translate('Note')}}</h6>
                                    <div>
                                        {{translate('Don’t_forget_to_click_the_respective_‘Save_Information’_buttons_below_to_save_changes')}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('admin-views.business-settings.partials.nav-menu')
                </div>
                <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info mb-20" style="--bs-bg-opacity: 0.1;">
                    <span class="text-info lh-1 fs-14">
                        <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                    </span>
                    <span>
                        {{ translate('messages.See all customer & manage them from All') }}
                        <a href="{{route('admin.customer.list')}}"
                            class="font-semibold text-primary text-underline">{{ translate('messages.Customer List') }} </a>
                        {{ translate('messages.page.') }}
                    </span>
                </div>

                <div class="card card-body mb-3" id="guest_checkout">
                    <div class="row g-3 mb-20">
                        <div class="col-lg-8">
                            <div>
                                <h4 class="mb-1">{{ translate('messages.Guest Checkout') }}</h4>
                                <p class="fs-12 mb-0">
                                    {{ translate('messages.If enabled customers do not have to login while checking out orders.') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <label
                                class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    {{ translate('messages.Status') }}
                                </span>
                                <input type="checkbox" data-id="guest_checkout_status" data-type="toggle"
                                    data-image-on="{{ dynamicAsset('assets/admin/img/modal/dm-tips-on.png') }}"
                                    data-image-off="{{ dynamicAsset('assets/admin/img/modal/dm-tips-off.png') }}"
                                    data-title-on="{{ translate('Want to enable guest checkout') }} ?"
                                    data-title-off="{{ translate('Want to disable guest checkout') }} ?"
                                    data-text-on="<p>{{ translate('If you enable this guest checkout will be visible when customer is not logged in.') }}</p>"
                                    data-text-off="<p>{{ translate('If you disable this guest checkout will not be visible when customer is not logged in.') }}</p>"
                                    class="toggle-switch-input dynamic-checkbox-toggle" value="1"
                                    name="guest_checkout_status" id="guest_checkout_status" {{ isset($data['guest_checkout_status']) && $data['guest_checkout_status'] == 1 ? 'checked' : '' }}>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info mb-2" style="--bs-bg-opacity: 0.1;">
                        <span class="text-info lh-1 fs-14">
                            <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                        </span>
                        <span>
                            {{ translate('messages.When you turn on this feature, you may increase your orders & order amount.') }}
                        </span>
                    </div>
                </div>

                @if (\Nwidart\Modules\Facades\Module::find('AI')?->isEnabled())
                    <div class="card card-body mb-3" id="customer_personalization">
                        <div class="row g-3 mb-20">
                            <div class="col-lg-8">
                                <div>
                                    <h4 class="mb-1">{{ translate('AI Personalization') }}</h4>
                                    <p class="fs-12 mb-0">
                                        {{ translate('Show each customer a more relevant order of food, restaurants, and categories.') }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label
                                    class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0">
                                    <span class="pr-1 d-flex align-items-center switch--label">
                                        {{ translate('messages.Status') }}
                                    </span>
                                    <input type="checkbox" data-id="customer_personalization_status" data-type="toggle"
                                        data-image-on="{{ dynamicAsset('assets/admin/img/modal/dm-tips-on.png') }}"
                                        data-image-off="{{ dynamicAsset('assets/admin/img/modal/dm-tips-off.png') }}"
                                        data-title-on="{{ translate('Enable AI Personalization') }} ?"
                                        data-title-off="{{ translate('Disable AI Personalization') }} ?"
                                        data-text-on="<p>{{ translate('Listings are tailored to each customer for a more relevant experience.') }}</p>"
                                        data-text-off="<p>{{ translate('Customers will see the standard listing order.') }}</p>"
                                        class="toggle-switch-input dynamic-checkbox-toggle" value="1"
                                        name="customer_personalization_status" id="customer_personalization_status"
                                        {{ isset($data['customer_personalization_status']) && $data['customer_personalization_status'] == 1 ? 'checked' : '' }}>
                                    <span class="toggle-switch-label text">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card card-body mb-3" id="pro_member">
                    <div class="row g-3 mb-20">
                        <div class="col-lg-8">
                            <div>
                                <h4 class="mb-1">{{ translate('messages.pro member') }}</h4>
                                <p class="fs-12 mb-0">
                                    {{ translate('messages.Enable to allow Pro membership. Ensure subscription') }}
                                    @if(isset($data['pro_member_status']) && $data['pro_member_status'] == 1)
                                        <a href="{{ route('admin.pro-customer.benefits-setup') }}" class="fs-12 font-weight-medium text-info text-underline m-0">{{ translate('messages.Setup') }}</a>
                                    @else
                                        <span class="fs-12 font-weight-medium text-muted m-0">{{ translate('messages.Setup') }}</span>
                                    @endif
                                    {{ translate('messages.Is complete and') }}
                                    @if(isset($data['pro_member_status']) && $data['pro_member_status'] == 1)
                                        <a href="{{ route('admin.business-settings.notificationMessages') }}#subscription_notification" class="fs-12 font-weight-medium text-info text-underline m-0">{{ translate('messages.Setup the push notification.') }}</a>
                                    @else
                                        <span class="fs-12 font-weight-medium text-muted m-0">{{ translate('messages.Setup the push notification.') }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <label
                                class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    {{ translate('messages.Status') }}
                                </span>
                                <input type="checkbox" data-id="pro_member_status" data-type="toggle"
                                    data-image-on="{{ dynamicAsset('assets/admin/img/modal/crown_on.png') }}"
                                    data-image-off="{{ dynamicAsset('assets/admin/img/modal/crown_off.png') }}"
                                    data-title-on="{{ translate('Want to enable pro member') }} ?"
                                    data-title-off="{{ translate('Want to disable pro member') }} ?"
                                    data-text-on="<p>{{ translate('If you enable this pro member option will be visible for customers when subscription setup is completed.') }}</p>"
                                    data-text-off="<p>{{ translate('If you disable the subscription plan, new customers won’t be able to subscribe. Existing subscribersSubscribers keep benefits until expiry, then the feature will be unavailable.') }}</p>"
                                    class="toggle-switch-input dynamic-checkbox-toggle" value="1"
                                    name="pro_member_status" id="pro_member_status" {{ isset($data['pro_member_status']) && $data['pro_member_status'] == 1 ? 'checked' : '' }}>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info mb-2" style="--bs-bg-opacity: 0.1;">
                        <span class="text-info lh-1 fs-14">
                            <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                        </span>
                        <span>
                            {{ translate('messages.When you turn on this feature, customers can access pro membership after subscription setup is completed.') }}
                        </span>
                    </div>
                </div>

                <div class="card card-body mb-3" id="customer_wallet">
                    <div class="view-details-container">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <h4 class="mb-1">{{ translate('messages.Customer Wallet') }}</h4>
                                <p class="mb-0 fs-12">
                                    {{ translate('messages.When active this feature customer can Earn & Buy through wallet. See customer wallet from Customers Details page.') }}
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                <div
                                    class="view-btn text-primary cursor-pointer font-semibold d-flex align-items-center gap-1">
                                    <span class="text-underline">{{ translate('messages.view') }}</span>
                                    <i class="tio-down-ui fs-12"></i>
                                </div>
                                <label class="toggle-switch toggle-switch-sm mb-0">
                                    <input type="checkbox" data-id="wallet_status" data-type="toggle"
                                        data-image-on="{{ dynamicAsset('assets/admin/img/modal/wallet-on.png') }}"
                                        data-image-off="{{ dynamicAsset('assets/admin/img/modal/wallet-off.png') }}"
                                        data-title-on="{{ translate('Want to enable the Wallet Feature') }} ?"
                                        data-title-off="{{ translate('Want to disable the Wallet Feature') }} ?"
                                        data-text-on="<p>{{ translate('If enabled Customers can see & use the Wallet option from their profile in the Customer App & Website.') }}</p>"
                                        data-text-off="<p>{{ translate('If disabled the Wallet feature will be hidden from the Customer App & Website') }}</p>"
                                        class="toggle-switch-input dynamic-checkbox-toggle" value="1" name="wallet_status"
                                        id="wallet_status" {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? 'checked' : '' }}>
                                    <span class="toggle-switch-label text mb-0">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="view-details mt-4">
                            <div class="row g-3 bg-light p-12 p-xxl-20 rounded-10 mb-20">
                                <div class="col-lg-4" id="refund_to_wallet_section">
                                    <div class="form-group mb-0">
                                        <label class="input-label d-flex align-items-center gap-1" for="refund_to_wallet">
                                            {{ translate('messages.Refund to Wallet') }}
                                            <span class="tio-info text-gray1 fs-16" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ translate('messages.If_enabled,_Customers_will_automatically_receive_the_refunded_amount_in_their_wallets._But_if_it’s_disabled,_the_Admin_will_handle_the_Refund_Request_in_his_convenient_transaction_channel.') }}">
                                            </span>
                                        </label>
                                        <label class="toggle-switch toggle-switch-sm d-flex justify-content-between border  rounded form-control
                                                {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? '' : 'text-muted' }}
                                                ">
                                            <span>{{ translate('messages.Status') }}</span>
                                            <input type="checkbox" data-id="refund_to_wallet" data-type="toggle"
                                                data-image-on="{{ dynamicAsset('assets/admin/img/modal/refund-on.png') }}"
                                                data-image-off="{{ dynamicAsset('assets/admin/img/modal/refund-off.png') }}"
                                                data-title-on="{{ translate('Want_to_enable_the') }} <strong>{{ translate('Refund to Wallet') }}</strong> {{ translate('feature') }}"
                                                data-title-off="{{ translate('Want_to_disable_the') }} <strong>{{ translate('Refund to Wallet') }}</strong> {{ translate('feature') }}"
                                                data-text-on="<p>{{ translate('If_enabled,_Customers_will_automatically_receive_the_refunded_amount_in_their_wallets.') }}</p>"
                                                data-text-off="<p>{{ translate('If_disabled,_the_Admin_will_handle_the_Refund_Request_in_his_convenient_transaction_channel_other_than_the_wallet.') }}</p>"
                                                class="status toggle-switch-input dynamic-checkbox-toggle" {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? '' : 'disabled' }} name="refund_to_wallet" id="refund_to_wallet" value="1" {{ isset($data['wallet_add_refund']) && $data['wallet_add_refund'] == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-4" id="add_funds_to_wallet">
                                    <div class="form-group mb-0">
                                        <label class="input-label d-flex align-items-center gap-1" for="add_fund_status">
                                            {{ translate('messages.Add Fund to Wallet') }}
                                            <span class="tio-info text-gray1 fs-16" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ translate('messages.With_this_feature,_customers_can_add_fund_to_wallet_if_the_payment_module_is_available.')}}">
                                            </span>
                                        </label>
                                        <label
                                            class="toggle-switch toggle-switch-sm d-flex justify-content-between border rounded form-control {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? '' : 'text-muted' }}">
                                            <span>{{ translate('messages.Status') }}</span>
                                            <input type="checkbox" {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? '' : 'disabled' }} data-id="add_fund_status"
                                                data-type="toggle"
                                                data-image-on="{{ dynamicAsset('assets/admin/img/modal/wallet-on.png') }}"
                                                data-image-off="{{ dynamicAsset('assets/admin/img/modal/wallet-off.png') }}"
                                                data-title-on="{{ translate('messages.Want_to_enable') }} <strong>{{ translate('add_fund_to_Wallet_feature?') }}</strong>"
                                                data-title-off="{{ translate('messages.Want_to_disable') }} <strong>{{ translate('add_fund_to_Wallet_feature?') }}</strong>"
                                                data-text-on="<p>{{ translate('messages.If_you_enable_this,_Customers_can_add_fund_to_wallet_using_payment_module') }}</p>"
                                                data-text-off="<p>{{ translate('messages.If_you_disable_this,_add_fund_to_wallet_will_be_hidden_from_the_Customer_App_&_Website.') }}</p>"
                                                class="status toggle-switch-input dynamic-checkbox-toggle"
                                                name="add_fund_status" id="add_fund_status" value="1" {{ isset($data['add_fund_status']) && $data['add_fund_status'] == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-4" id="minimum_add_amount">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="customer_add_fund_min_amount">
                                            {{ translate('add fund Minimum Amount') }} ($)
                                        </label>
                                        <input id="customer_add_fund_min_amount" type="number" class="form-control"
                                            name="customer_add_fund_min_amount" min="0"
                                            value="{{isset($data['customer_add_fund_min_amount']) ? $data['customer_add_fund_min_amount'] : 0}}"
                                            {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? '' : 'disabled' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info"
                                style="--bs-bg-opacity: 0.1;">
                                <span class="text-info lh-1 fs-14">
                                    <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                                </span>
                                <span>
                                    {{ translate('messages.You can view the customer wallet from the Customer Details page. Go to') }}
                                    <span class="font-semibold">
                                        {{ translate('messages.Customers') }} >
                                        {{ translate('messages.Customer List') }} >
                                        {{ translate('messages.View Details') }}.
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-body mb-3" id="customer_loyalty_point_section">
                    <div class="view-details-container">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <h4 class="mb-1">{{ translate('messages.Customer Loyalty Point') }}</h4>
                                <p class="mb-0 fs-12">
                                    {{ translate('messages.If enabled customers will earn a certain amount of points after each purchase.') }}
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                <div
                                    class="view-btn text-primary cursor-pointer font-semibold d-flex align-items-center gap-1">
                                    <span class="text-underline">{{ translate('messages.view') }}</span>
                                    <i class="tio-down-ui fs-12"></i>
                                </div>
                                <label class="toggle-switch toggle-switch-sm mb-0">
                                    <input type="checkbox" data-id="customer_loyalty_point" data-type="toggle"
                                        data-image-on="{{ dynamicAsset('assets/admin/img/modal/loyalty-on.png') }}"
                                        data-image-off="{{ dynamicAsset('assets/admin/img/modal/loyalty-off.png') }}"
                                        data-title-on="{{ translate('Want_to_enable_the') }} <strong>{{ translate('Loyalty_Point') }}</strong> ?"
                                        data-title-off="{{ translate('Want_to_disable_the') }} <strong>{{ translate('Loyalty_Point') }}</strong> ?"
                                        data-text-on="<p>{{ translate('Customer_will_see_loyalty_point_option_in_his_profile_settings_&_can_earn_&_convert_this_point_to_wallet_money') }}</p>"
                                        data-text-off="<p>{{ translate('Customer_will_no_see_loyalty_point_option_from_his_profile_settings') }}</p>"
                                        class="toggle-switch-input dynamic-checkbox-toggle" name="customer_loyalty_point"
                                        id="customer_loyalty_point" data-section="loyalty-point-section" value="1" {{ isset($data['loyalty_point_status']) && $data['loyalty_point_status'] == 1 ? 'checked' : '' }}>
                                    <span class="toggle-switch-label text">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="view-details mt-4">
                            <div class="row g-3 bg-light p-12 p-xxl-20 rounded-10 mb-20">
                                <div class="col-lg-4">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="loyalty_point_exchange_rate">1
                                            {{ \App\CentralLogics\Helpers::currency_code() }}
                                            {{ translate('equivalent point amount') }}</label>
                                        <input {{ isset($data['loyalty_point_status']) && $data['loyalty_point_status'] == 1 ? 'required' : 'readonly' }} id="loyalty_point_exchange_rate" type="number"
                                            class="form-control" name="loyalty_point_exchange_rate" step=".001" min="0"
                                            value="{{ $data['loyalty_point_exchange_rate'] ?? '0' }}">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="item_purchase_point">
                                            {{ translate('Loyalty_Point_Earn_Per_Order') }} (%)
                                            <small class="text-danger">*</small><span class="tio-info text-gray1 fs-16 p-1"
                                                data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ translate('messages.On_every_purchase_this_percent_of_amount_will_be_added_as_loyalty_point_on_his_account') }}">
                                            </span>

                                        </label>
                                        <input {{ isset($data['loyalty_point_status']) && $data['loyalty_point_status'] == 1 ? 'required' : 'readonly' }} id="item_purchase_point" type="number"
                                            class="form-control" name="item_purchase_point" step=".001" min="0"
                                            value="{{ $data['loyalty_point_item_purchase_point'] ?? '0' }}">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="minimum_transfer_point">
                                            {{ translate('Minimum_Point_Required_To_Convert') }}
                                        </label>
                                        <input {{ isset($data['loyalty_point_status']) && $data['loyalty_point_status'] == 1 ? 'required' : 'readonly' }} id="minimum_transfer_point" type="number"
                                            class="form-control" name="minimun_transfer_point" min="0" step=".001"
                                            value="{{ $data['loyalty_point_minimum_point'] ?? '0' }}">
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info"
                                style="--bs-bg-opacity: 0.1;">
                                <span class="text-info lh-1 fs-14">
                                    <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                                </span>
                                <span>
                                    {{ translate('messages.To see customer loyalty point report visit') }}
                                    <a href="{{route('admin.customer.loyalty-point.report')}}"
                                        class="font-semibold text-primary text-underline">{{ translate('messages.Loyalty Point Report') }}
                                    </a>.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-body mb-3" id="customer_referral_earning_settings">
                    <div class="view-details-container">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <h4 class="mb-1">{{ translate('messages.Customer Referral Earning Settings') }}</h4>
                                <p class="mb-0 fs-12">
                                    {{ translate('messages.Customers will receive this wallet balance rewards for sharing their referral code') }}
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                <div
                                    class="view-btn text-primary cursor-pointer font-semibold d-flex align-items-center gap-1">
                                    <span class="text-underline">{{ translate('messages.view') }}</span>
                                    <i class="tio-down-ui fs-12"></i>
                                </div>
                                <label class="toggle-switch toggle-switch-sm mb-0">
                                    <input type="checkbox" data-id="ref_earning_status" data-type="toggle"
                                        data-image-on="{{ dynamicAsset('assets/admin/img/modal/referral-on.png') }}"
                                        data-image-off="{{ dynamicAsset('assets/admin/img/modal/referral-off.png') }}"
                                        data-title-on="{{ translate('Want_to_enable_the') }} <strong>{{ translate('Referral_Earning') }}</strong> ?"
                                        data-title-off="{{ translate('Want_to_disable_the') }} <strong>{{ translate('Referral_Earning') }}</strong> ?"
                                        data-text-on="<p>{{ translate('If_enabled,_Customers_can_earn_points_by_referring_others_to_sign_up_&_first_purchase_successfully_from_your_business.') }}</p>"
                                        data-text-off="<p>{{ translate('If_disabled,_the_referral-earning_feature_will_be_hidden_from_the_Customer_App_&_Website.') }}</p>"
                                        class="toggle-switch-input dynamic-checkbox-toggle" name="ref_earning_status"
                                        id="ref_earning_status" data-section="referrer-earning" value="1" {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? '' : 'disabled' }} {{  isset($data['ref_earning_status']) && $data['ref_earning_status'] == 1 ? 'checked' : '' }}>
                                    <span class="toggle-switch-label text">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="view-details mt-4">
                            <div class="bg-light p-12 p-xxl-20 rounded-10 mb-20">
                                <div class="row g-3 align-items-end">
                                    <div class="col-lg-4 align-self-center text-center">
                                        <div class="text-left">
                                            <h5 class="align-items-center">
                                                <span>
                                                    {{ translate('Who_Share_the_code') }}
                                                </span>
                                            </h5>
                                            <p>
                                                {{ translate('Customers_will_receive_this_wallet_balance_rewards_for_sharing_their_referral_code_with_friends,_who_use_the_code_when_signing_up_and_completing_their_first_order.') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="card text-left">
                                            <div class="card-body">
                                                <div class="form-group mb-0">
                                                    <label class="input-label d-flex align-items-center gap-1"
                                                        for="ref_earning_exchange_rate">
                                                        {{ translate('messages.Earning Per Referral') }}
                                                        {{ \App\CentralLogics\Helpers::currency_code() }}
                                                        <span class="text-danger">*</span>
                                                        <span class="tio-info text-gray1 fs-16" data-toggle="tooltip"
                                                            data-placement="right"
                                                            data-original-title="{{ translate('messages.Amount earned for each successful referral.') }}">
                                                        </span>
                                                    </label>
                                                    <div @if(isset($data['wallet_status']) && $data['wallet_status'] == 0)
                                                        id="walletTooltip" data-toggle="tooltip" data-html="true"
                                                        data-template='<div class="tooltip custom-clickable-tooltip" role="tooltip">
                                                                                        <div class="arrow"></div>
                                                                                        <div class="tooltip-inner"></div>
                                                                                    </div>' title="<span>
                                                                        {{ translate('messages.Refer amount add to wallet option is disabled. Kindly turn on the option from') }}
                                                                        <a href='#' class='text-info font-semibold text-underline text-nowrap'>
                                                                            {{ translate('messages.Customer Wallet') }}
                                                                        </a>
                                                                        {{ translate('messages.section to complete this settings') }}
                                                    </span>" @endif>
                                                        <input id="ref_earning_exchange_rate" type="number" step=".001"
                                                            min="0" max="99999999999" class="form-control"
                                                            name="ref_earning_exchange_rate"
                                                            disable-title="{{ translate('messages.Refer amount add to wallet option is disabled. Kindly turn on the option from ') }} <a href='{{ route('admin.business-settings.business-setup', ['tab' => 'customer']) }}#wallet_status' class='font-semibold text-underline'>{{ translate('messages.Customer Wallet') }}</a> {{ translate('messages.section to complete this settings') }}"
                                                            value="{{ $data['ref_earning_exchange_rate'] ?? '0' }}" {{ isset($data['wallet_status']) && $data['wallet_status'] == 0 || isset($data['ref_earning_status']) && $data['ref_earning_status'] == 0 ? 'disabled' : '' }}>
                                                    </div>
                                                    <p class="fs-12 mb-0 text-danger mt-1">
                                                        {{ translate('messages.Must Turn on') }}
                                                        <span class="font-semibold">{{ translate('messages.Wallet') }}
                                                        </span>
                                                        {{ translate('messages.option, otherwise customer can’t receive the reward amount.') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 align-self-center text-center">
                                        <div class="text-left">

                                            <h5 class="align-items-center">
                                                <span>
                                                    {{ translate('Who_Use_the_code') }}
                                                </span>
                                            </h5>
                                            <p>
                                                {{ translate('By_applying_the_referral_code_during_signup_and_when_making_their_first_purchase,_customers_will_enjoy_a_discount_for_a_limited_time.') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="card text-left">
                                            <div class="card-body">
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label d-flex align-items-center gap-1"
                                                                for="ref_earning_exchange_rate">
                                                                {{ translate('Customer_will_get_Discount_on_first_order ') }}
                                                                <span class="tio-info text-gray1 fs-16"
                                                                    data-toggle="tooltip" data-placement="right"
                                                                    data-original-title="{{ translate('messages.Configure_discounts_for_newly_registered_users_who_sign_up_with_a_referral_code._Customize_the_discount_type_and_amount_to_incentivize_referrals_and_encourage_user_engagement.') }}">
                                                                </span>
                                                            </label>
                                                            <label
                                                                class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded form-control {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? '' : 'text-muted' }}">
                                                                <span>
                                                                    {{ translate('messages.Status') }}
                                                                </span>
                                                                <input {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 && isset($data['ref_earning_status']) && $data['ref_earning_status'] == 1 ? '' : 'disabled' }}
                                                                    type="checkbox" data-id="new_customer_discount_status"
                                                                    data-type="toggle"
                                                                    data-image-on="{{ dynamicAsset('assets/admin/img/modal/basic_campaign_on.png') }}"
                                                                    data-image-off="{{ dynamicAsset('assets/admin/img/modal/basic_campaign_off.png') }}"
                                                                    data-title-on="{{ translate('messages.Want_to_enable') }} <strong>{{ translate('messages.new_customer_discount?') }}</strong>"
                                                                    data-title-off="{{ translate('messages.Want_to_disable') }} <strong>{{ translate('messages.new_customer_discount?') }}</strong>"
                                                                    data-text-on="<p>{{ translate('messages.If_you_enable_this,_Customers_will_get_discount_on_first_order.') }}</p>"
                                                                    data-text-off="<p>{{ translate('If_you_disable_this,_Customers_won’t_get_any_discount_on_first_order.') }}</p>"
                                                                    class="status toggle-switch-input dynamic-checkbox-toggle "
                                                                    name="new_customer_discount_status"
                                                                    id="new_customer_discount_status" value="1" {{ data_get($data, 'new_customer_discount_status') == 1 ? 'checked' : '' }}>
                                                                <span class="toggle-switch-label text">
                                                                    <span class="toggle-switch-indicator"></span>
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label d-flex align-items-center gap-1"
                                                                for="new_customer_discount_amount">
                                                                {{ translate('Discount_Amount') }}
                                                                <span
                                                                    class="{{  data_get($data, 'new_customer_discount_amount_type') != 'amount' ? '' : 'd-none' }} "
                                                                    id="percentage">(%)</span>
                                                                <span
                                                                    class=" {{  data_get($data, 'new_customer_discount_amount_type') == 'amount' ? '' : 'd-none' }} "
                                                                    id='cuttency_symbol'>
                                                                    ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                                </span>
                                                                <span class="tio-info text-gray1 fs-16"
                                                                    data-toggle="tooltip" data-placement="right"
                                                                    data-original-title="{{ translate('Enter_the_discount_value_for_referral-based_new_user_registrations.') }}">
                                                                </span>
                                                            </label>
                                                            <div class="custom-group-btn form-control single">
                                                                <div class="item flex-sm-grow-1">
                                                                    <input id="new_customer_discount_amount" type="number"
                                                                        step=".001" min="0" {{  isset($data['wallet_status']) && $data['wallet_status'] == 1 && data_get($data, 'new_customer_discount_status') == 1 ? 'required' : 'readonly' }} class="form-control border-0 h-100"
                                                                        name="new_customer_discount_amount"
                                                                        max='{{  data_get($data, 'new_customer_discount_amount_type') != 'amount' ? '100' : '9999999999' }}'
                                                                        value="{{data_get($data, 'new_customer_discount_amount') ?? '0' }}">
                                                                </div>
                                                                <div class="item flex-shrink-0">
                                                                    <select name="new_customer_discount_amount_type"
                                                                        id="new_customer_discount_amount_type" {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 && data_get($data, 'new_customer_discount_status') == 1 ? 'required' : 'disabled' }} class="custom-select w-90px border-0">
                                                                        <option {{ data_get($data, 'new_customer_discount_amount_type') == 'percentage' ? "selected" : '' }} value="percentage">(%)
                                                                        </option>
                                                                        <option {{ data_get($data, 'new_customer_discount_amount_type') == 'amount' ? "selected" : '' }} value="amount">
                                                                            {{ \App\CentralLogics\Helpers::currency_symbol() }}
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label d-flex align-items-center gap-1"
                                                                for="new_customer_discount_amount_validity">
                                                                {{ translate('validity') }}
                                                                <span class="tio-info text-gray1 fs-16"
                                                                    data-toggle="tooltip" data-placement="right"
                                                                    data-original-title="{{ translate('Set_how_long_the_discount_remains_active_after_registration.') }}">
                                                                </span>
                                                            </label>
                                                            <div class="custom-group-btn form-control single">
                                                                <div class="item flex-sm-grow-1">
                                                                    <input id="new_customer_discount_amount_validity"
                                                                        type="number" step="1" min="1" max="999" {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 && data_get($data, 'new_customer_discount_status') == 1 ? 'required' : 'readonly' }} class="form-control border-0 h-100"
                                                                        name="new_customer_discount_amount_validity"
                                                                        value="{{ data_get($data, 'new_customer_discount_amount_validity') ?? '1' }}">
                                                                </div>
                                                                <div class="item flex-shrink-0">
                                                                    <select name="new_customer_discount_validity_type"
                                                                        class="custom-select w-90px border-0"
                                                                        id="new_customer_discount_validity_type" {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 && data_get($data, 'new_customer_discount_status') == 1 ? 'required' : 'disabled' }}>
                                                                        <option {{ data_get($data, 'new_customer_discount_validity_type') == 'day' ? "selected" : '' }} value="day">
                                                                            {{translate('messages.day')}}</option>
                                                                        <option {{ data_get($data, 'new_customer_discount_validity_type') == 'month' ? "selected" : '' }} value="month">
                                                                            {{translate('messages.month')}} </option>
                                                                        <option {{ data_get($data, 'new_customer_discount_validity_type') == 'year' ? "selected" : '' }} value="year">
                                                                            {{translate('messages.year')}} </option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
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
                        <button type="reset" id="reset_btn"
                            class="btn btn--secondary min-w-120 location-reload">{{ translate('messages.Reset') }} </button>
                        <button type="submit" class="btn btn--primary">
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
                <h3 class="mb-0">{{ translate('messages.Customer Guideline') }}</h3>
                <button type="button"
                    class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                    aria-label="Close">&times;</button>
            </div>
            <div class="custom-offcanvas-body offcanvas-height-100 py-3 px-md-4 px-3">
                <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                    <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                        <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                            type="button" data-toggle="collapse" data-target="#guest_checkout_guide" aria-expanded="true">
                            <div
                                class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                                <i class="tio-down-ui"></i>
                            </div>
                            <span
                                class="font-semibold text-left fs-14 text-title">{{ translate('messages.Guest Checkout') }}</span>
                        </button>
                        <a href="#guest_checkout"
                            class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                    </div>
                    <div class="collapse show mt-3" id="guest_checkout_guide">
                        <div class="card card-body">
                            <div class="">
                                <h5 class="mb-3">{{translate('messages.Guest Checkout')}}</h5>
                                <p class="fs-12 mb-3">
                                    {{ translate('messages.The Guest Checkout feature allows customers to place orders without creating a full account or logging in. This streamlines the purchase process, improves user experience, and can increase order completion rates, especially for first-time or infrequent users.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                    <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                        <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                            type="button" data-toggle="collapse" data-target="#customer_wallet_guide" aria-expanded="true">
                            <div
                                class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                                <i class="tio-down-ui"></i>
                            </div>
                            <span
                                class="font-semibold text-left fs-14 text-title">{{ translate('messages.Customer Wallet') }}</span>
                        </button>
                        <a href="#customer_wallet"
                            class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                    </div>
                    <div class="collapse mt-3" id="customer_wallet_guide">
                        <div class="card card-body">
                            <div class="">
                                <h5 class="mb-3">{{translate('messages.Customer Wallet')}}</h5>
                                <p class="fs-12 mb-3">
                                    {{ translate('messages.The Customer Wallet is a digital wallet feature that allows customers to store funds within the platform for quick and convenient transactions. Customers can add money to their wallet, make payments for orders, and even request or receive refunds directly through the wallet.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                    <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                        <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                            type="button" data-toggle="collapse" data-target="#refund_to_wallet_guide" aria-expanded="true">
                            <div
                                class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                                <i class="tio-down-ui"></i>
                            </div>
                            <span
                                class="font-semibold text-left fs-14 text-title">{{ translate('messages.Refund to Wallet') }}</span>
                        </button>
                        <a href="#refund_to_wallet_section"
                            class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                    </div>
                    <div class="collapse mt-3" id="refund_to_wallet_guide">
                        <div class="card card-body">
                            <div class="">
                                <h5 class="mb-3">{{translate('messages.Refund to Wallet')}}</h5>
                                <p class="fs-12 mb-3">
                                    {{ translate('messages.When the wallet feature is enabled, customers can use their wallet balance to pay for orders. Refunds can also be sent directly to the wallet of the customer for easy use in future purchases.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                    <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                        <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                            type="button" data-toggle="collapse" data-target="#add_funds_to_wallet_guide"
                            aria-expanded="true">
                            <div
                                class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                                <i class="tio-down-ui"></i>
                            </div>
                            <span
                                class="font-semibold text-left fs-14 text-title">{{ translate('messages.Add funds to wallet') }}</span>
                        </button>
                        <a href="#add_funds_to_wallet"
                            class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                    </div>
                    <div class="collapse mt-3" id="add_funds_to_wallet_guide">
                        <div class="card card-body">
                            <div class="">
                                <h5 class="mb-3">{{translate('messages.Add funds to wallet')}}</h5>
                                <p class="fs-12 mb-3">
                                    {{ translate('messages.If this option is enabled, customers can add money to their wallet using digital payment methods like bank transfer, mobile wallets, etc.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                    <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                        <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                            type="button" data-toggle="collapse" data-target="#minimum_add_amount_guide"
                            aria-expanded="true">
                            <div
                                class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                                <i class="tio-down-ui"></i>
                            </div>
                            <span
                                class="font-semibold text-left fs-14 text-title">{{ translate('messages.Minimum add Amount') }}</span>
                        </button>
                        <a href="#minimum_add_amount"
                            class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                    </div>
                    <div class="collapse mt-3" id="minimum_add_amount_guide">
                        <div class="card card-body">
                            <div class="">
                                <h5 class="mb-3">{{translate('messages.Minimum add amount')}}</h5>
                                <p class="fs-12 mb-3">
                                    {{ translate('messages.The smallest amount a customer can add to their wallet at one time.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                    <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                        <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                            type="button" data-toggle="collapse" data-target="#customer_loyalty_point_guide"
                            aria-expanded="true">
                            <div
                                class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                                <i class="tio-down-ui"></i>
                            </div>
                            <span
                                class="font-semibold text-left fs-14 text-title">{{ translate('messages.Customer Loyalty Point') }}</span>
                        </button>
                        <a href="#customer_loyalty_point_section"
                            class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                    </div>
                    <div class="collapse mt-3" id="customer_loyalty_point_guide">
                        <div class="card card-body">
                            <div class="">
                                <h5 class="mb-3">{{translate('messages.Customer Loyalty Point')}}</h5>
                                <p class="fs-12 mb-3">
                                    {{ translate('messages.This setting lets the admin define how many loyalty points are equal to 1 unit of currency (Ex: $1 if the system default currency is dollars). It helps customers understand the value of their points when they want to convert them to wallet money.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                    <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                        <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                            type="button" data-toggle="collapse" data-target="#customer_referral_earning_guide"
                            aria-expanded="true">
                            <div
                                class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                                <i class="tio-down-ui"></i>
                            </div>
                            <span
                                class="font-semibold text-left fs-14 text-title">{{ translate('messages.Customer Referral Earning Settings') }}</span>
                        </button>
                        <a href="#customer_referral_earning_settings"
                            class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                    </div>
                    <div class="collapse mt-3" id="customer_referral_earning_guide">
                        <div class="card card-body">
                            <div class="">
                                <h5 class="mb-3">{{translate('messages.Customer Referral Earning Settings')}}</h5>
                                <ul class="mb-0 fs-12">
                                    <li class="mt-2 mb-3">
                                        {{ translate('messages.Customer referral earning settings allow you to specify the wallet balance reward that customers will receive for successfully sharing their unique referral code with new customers who then make a purchase.') }}
                                    </li>
                                    <li class="mt-2 mb-3">
                                        {{ translate('messages.This setting allows the admin to set the amount (in the default currency) that a referring customer will earn. The reward is added to their wallet when someone they refer places and completes their first order on the platform.') }}
                                    </li>
                                </ul>
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
        "use strict";

        $('#new_customer_discount_amount_type').on('change', function () {
            if ($('#new_customer_discount_amount_type').val() == 'amount') {
                $('#percentage').addClass('d-none');
                $('#cuttency_symbol').removeClass('d-none');
                $('#new_customer_discount_amount').attr('max', 99999999999);

            }
            else {
                $('#percentage').removeClass('d-none');
                $('#cuttency_symbol').addClass('d-none');
                $('#new_customer_discount_amount').attr('max', 100);

            }
        });

        // --- Tooltip keep open on hover ---
        $('#walletTooltip').tooltip({
            trigger: 'manual',
            html: true
        });

        $('#walletTooltip').on('mouseenter', function () {
            $(this).tooltip('show');

            $('.custom-clickable-tooltip').on('mouseenter', function () {
                $('#walletTooltip').tooltip('show');
            }).on('mouseleave', function () {
                $('#walletTooltip').tooltip('hide');
            });
        });

        $('#walletTooltip').on('mouseleave', function () {
            setTimeout(() => {
                if (!$('.custom-clickable-tooltip:hover').length) {
                    $('#walletTooltip').tooltip('hide');
                }
            }, 100);
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
