@extends('layouts.vendor.app')
@section('title',translate('Edit Role'))
@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h2 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img src="{{dynamicAsset('assets/admin/img/resturant-panel/page-title/employee-role.png')}}" alt="public">
                        </div>
                        <span>
                        {{translate('messages.custom_role')}}
                    </span>
                    </h2>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-header">
                <h5 class="card-title my-1">
                <span class="card-header-icon">
                    <i class="tio-document-text-outlined"></i>
                </span>
                    <span>
                    {{translate('messages.role_form')}}
                </span>
                </h5>
            </div>
            <div class="card-body">
                <div class="px-xl-2">
                    <form action="{{route('vendor.custom-role.update',[$role['id']])}}" method="post">
                        @csrf
                        @php($language = \App\Models\BusinessSetting::where('key','language')->first())
                        @php($language = $language->value ?? null)
                        @php($default_lang = str_replace('_', '-', app()->getLocale()))
                        @if($language)
                            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                <ul class="nav nav-tabs mb-4">
                                    <li class="nav-item">
                                        <a class="nav-link lang_link active"
                                           href="#"
                                           id="default-link">{{translate('messages.default')}}</a>
                                    </li>
                                    @foreach (json_decode($language) as $lang)
                                        <li class="nav-item">
                                            <a class="nav-link lang_link"
                                               href="#"
                                               id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="lang_form" id="default-form">
                            <div class="form-group">
                                <label class="input-label " for="name">{{translate('messages.role_name')}} ({{ translate('messages.default') }})</label>
                                <input type="text" name="name[]" class="form-control" id="name" value="{{$role?->getRawOriginal('name')}}"
                                       placeholder="{{translate('messages.role_name')}}" >
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                        </div>

                        @if($language)
                            @foreach(json_decode($language) as $lang)
                                    <?php
                                    if(count($role['translations'])){
                                        $translate = [];
                                        foreach($role['translations'] as $t)
                                        {
                                            if($t->locale == $lang && $t->key == "name"){
                                                $translate[$lang]['name'] = $t->value;
                                            }
                                        }
                                    }
                                    ?>
                                <div class="d-none lang_form" id="{{$lang}}-form">
                                    <div class="form-group">
                                        <label class="input-label" for="{{$lang}}_title">{{translate('messages.role_name')}} ({{strtoupper($lang)}})</label>
                                        <input type="text" name="name[]" id="{{$lang}}_title" class="form-control" placeholder="{{translate('role_name_example')}}" value="{{$translate[$lang]['name']??''}}"  >
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                </div>
                            @endforeach
                        @endif

                        <div class="bg-light rounded p-20">
                            <div class="d-flex align-items-center flex-sm-nowrap flex-wrap gap-1 justify-content-between mb-20">
                                <h4 class="input-label m-0 text-capitalize">{{translate('messages.Module Wise Permission')}} : </h4>
                                <div class="check-item pb-0 w-auto">
                                    <div class="form-group form-check d-flex align-items-center gap-2 form--check inlines-0">
                                        <label class="form-check-label me-3 pe-3 font-medium fz-14px text-title d-block" for="select-all">{{ translate('All Module Permission') }}</label>
                                        <input type="checkbox" class="form-check-input position-relative"
                                               id="select-all">
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-4">
                                    <div class="bg-white rounded">
                                        <div class="d-flex align-items-center justify-content-between gap-1 flex-wrap p-3 border-bottom">
                                            <h5 class="mb-0">{{translate('messages.General')}}</h5>
                                            <div class="check-item pb-0 w-auto">
                                                <div class="form-group form-check d-flex align-items-center gap-2 form--check inlines-0">
                                                    <label class="form-check-label me-3 pe-3 font-medium fz-14px text-title d-block" for="general">{{ translate('Select All') }}</label>
                                                    <input type="checkbox" class="form-check-input position-relative"
                                                           id="general">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="check--items-inner d-flex flex-wrap p-sm-3 p-2">
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group mb-0 form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="dashboard" class="form-check-input"
                                                           id="dashboard"
                                                        {{ in_array('dashboard', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="dashboard">{{ translate('messages.Dashboard') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="chat" class="form-check-input"
                                                           id="chat"
                                                        {{ in_array('chat', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2  text-dark" for="chat">{{ translate('messages.chat') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="pos" class="form-check-input"
                                                           id="pos"
                                                        {{ in_array('pos', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2  text-dark" for="pos">{{ translate('messages.pos_system') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="bg-white rounded">
                                        <div class="d-flex align-items-center justify-content-between gap-1 flex-wrap p-3 border-bottom">
                                            <h5 class="mb-0">{{translate('messages.Ads & promotions')}}</h5>
                                            <div class="check-item pb-0 w-auto">
                                                <div class="form-group form-check d-flex align-items-center gap-2 form--check inlines-0">
                                                    <label class="form-check-label me-3 pe-3 font-medium fz-14px text-title d-block" for="promotions">{{ translate('Select All') }}</label>
                                                    <input type="checkbox" class="form-check-input position-relative"
                                                           id="promotions">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="check--items-inner d-flex flex-wrap p-sm-3 p-2">
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group mb-0 form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="new_ads" class="form-check-input"
                                                           id="new_ads"
                                                        {{ in_array('new_ads', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="new_ads">{{ translate('messages.New Ads') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group mb-0 form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="ads_list" class="form-check-input"
                                                           id="ads_list"
                                                        {{ in_array('ads_list', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="ads_list">{{ translate('messages.Ads List') }}</label>
                                                </div>
                                            </div>
                                            @if (
                                                addon_published_status('ReelsModule')
                                                && \App\CentralLogics\Helpers::get_business_settings('vendor_can_upload_reels')
                                            )
                                                <div class="check-item mb-2 p-0">
                                                    <div class="form-group mb-0 form-check form--check">
                                                        <input type="checkbox" name="modules[]" value="reels" class="form-check-input"
                                                               id="reels"
                                                            {{ in_array('reels', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                        <label class="form-check-label ml-2 text-dark" for="reels">{{ translate('messages.reels') }}</label>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="campaign" class="form-check-input"
                                                           id="campaign"
                                                        {{ in_array('campaign', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="campaign">{{ translate('messages.campaign') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="bogo_offer" class="form-check-input"
                                                           id="bogo_offer"
                                                        {{ in_array('bogo_offer', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="bogo_offer">{{ translate('messages.BOGO_Offer') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="happy_hour" class="form-check-input"
                                                           id="happy_hour"
                                                        {{ in_array('happy_hour', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="happy_hour">{{ translate('messages.Happy_Hour') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="coupon" class="form-check-input"
                                                           id="coupon"
                                                        {{ in_array('coupon', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="coupon">{{ translate('messages.coupon') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="bg-white rounded">
                                        <div class="d-flex align-items-center justify-content-between gap-1 flex-wrap p-3 border-bottom">
                                            <h5 class="mb-0">{{translate('messages.Food management')}}</h5>
                                            <div class="check-item pb-0 w-auto">
                                                <div class="form-group form-check d-flex align-items-center gap-2 form--check inlines-0">
                                                    <label class="form-check-label me-3 pe-3 font-medium fz-14px text-title d-block" for="foodManagement">{{ translate('Select All') }}</label>
                                                    <input type="checkbox" class="form-check-input position-relative"
                                                           id="foodManagement">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="check--items-inner d-flex flex-wrap p-sm-3 p-2">
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="food" class="form-check-input"
                                                           id="food"
                                                        {{ in_array('food', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="food">{{ translate('messages.food') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="category" class="form-check-input"
                                                           id="category"
                                                        {{ in_array('category', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="category">{{ translate('messages.category') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="addon" class="form-check-input"
                                                           id="addon"
                                                        {{ in_array('addon', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="addon">{{ translate('messages.addon') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="reviews" class="form-check-input"
                                                           id="reviews"
                                                        {{ in_array('reviews', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="reviews">{{ translate('messages.Reviews') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="bg-white rounded">
                                        <div class="d-flex align-items-center justify-content-between gap-1 flex-wrap p-3 border-bottom">
                                            <h5 class="mb-0">{{translate('messages.Order managements')}}</h5>
                                            <div class="check-item pb-0 w-auto">
                                                <div class="form-group form-check d-flex align-items-center gap-2 form--check inlines-0">
                                                    <label class="form-check-label me-3 pe-3 font-medium fz-14px text-title d-block" for="orderManagements">{{ translate('Select All') }}</label>
                                                    <input type="checkbox" class="form-check-input position-relative"
                                                           id="orderManagements">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="check--items-inner d-flex flex-wrap p-sm-3 p-2">
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="regular_order" class="form-check-input"
                                                           id="regular_order"
                                                        {{ in_array('regular_order', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="regular_order">{{ translate('messages.Regular Orders') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="subscription_order" class="form-check-input"
                                                           id="subscription_order"
                                                        {{ in_array('subscription_order', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="subscription_order">{{ translate('messages.Subscription Orders') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="bg-white rounded">
                                        <div class="d-flex align-items-center justify-content-between gap-1 flex-wrap p-3 border-bottom">
                                            <h5 class="mb-0">{{translate('messages.Wallet management')}}</h5>
                                            <div class="check-item pb-0 w-auto">
                                                <div class="form-group form-check d-flex align-items-center gap-2 form--check inlines-0">
                                                    <label class="form-check-label me-3 pe-3 font-medium fz-14px text-title d-block" for="walletManagement">{{ translate('Select All') }}</label>
                                                    <input type="checkbox" class="form-check-input position-relative"
                                                           id="walletManagement">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="check--items-inner d-flex flex-wrap p-sm-3 p-2">
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="my_wallet" class="form-check-input"
                                                           id="my_wallet"
                                                        {{ in_array('my_wallet', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="my_wallet">{{ translate('messages.My Wallet') }}</label>
                                                </div>
                                            </div>
                                            {{-- <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="wallet_method" class="form-check-input"
                                                           id="wallet_method"
                                                        {{ in_array('wallet_method', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="wallet_method">{{ translate('messages.Wallet Method') }}</label>
                                                </div>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="bg-white rounded">
                                        <div class="d-flex align-items-center justify-content-between gap-1 flex-wrap p-3 border-bottom">
                                            <h5 class="mb-0">{{translate('messages.Employee management')}}</h5>
                                            <div class="check-item pb-0 w-auto">
                                                <div class="form-group form-check d-flex align-items-center gap-2 form--check inlines-0">
                                                    <label class="form-check-label me-3 pe-3 font-medium fz-14px text-title d-block" for="employeeManagement">{{ translate('Select All') }}</label>
                                                    <input type="checkbox" class="form-check-input position-relative"
                                                           id="employeeManagement">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="check--items-inner d-flex flex-wrap p-sm-3 p-2">
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="role_management" class="form-check-input"
                                                           id="role_management"
                                                        {{ in_array('role_management', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="role_management">{{ translate('messages.Role Management') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="all_employee" class="form-check-input"
                                                           id="all_employee"
                                                        {{ in_array('all_employee', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="all_employee">{{ translate('messages.All Employee') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-6">
                                    <div class="bg-white rounded">
                                        <div class="d-flex align-items-center justify-content-between gap-1 flex-wrap p-3 border-bottom">
                                            <h5 class="mb-0">{{translate('messages.Reports')}}</h5>
                                            <div class="check-item pb-0 w-auto">
                                                <div class="form-group form-check d-flex align-items-center gap-2 form--check inlines-0">
                                                    <label class="form-check-label me-3 pe-3 font-medium fz-14px text-title d-block" for="reports">{{ translate('Select All') }}</label>
                                                    <input type="checkbox" class="form-check-input position-relative"
                                                           id="reports">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="check--items-inner d-flex flex-wrap p-sm-3 p-2">
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="expense_report" class="form-check-input"
                                                           id="expense_report"
                                                        {{ in_array('expense_report', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="expense_report">{{ translate('messages.Expense Report') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="transaction" class="form-check-input"
                                                           id="transaction"
                                                        {{ in_array('transaction', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="transaction">{{ translate('messages.transaction') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="disbursement" class="form-check-input"
                                                           id="disbursement"
                                                        {{ in_array('disbursement', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="disbursement">{{ translate('messages.disbursement') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="order_report" class="form-check-input"
                                                           id="order_report"
                                                        {{ in_array('order_report', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="order_report">{{ translate('messages.Order Report') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="food_report" class="form-check-input"
                                                           id="food_report"
                                                        {{ in_array('food_report', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="food_report">{{ translate('messages.Food Report') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="tax_report" class="form-check-input"
                                                           id="tax_report"
                                                        {{ in_array('tax_report', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="tax_report">{{ translate('messages.Tax Report') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-6">
                                    <div class="bg-white rounded">
                                        <div class="d-flex align-items-center justify-content-between gap-1 flex-wrap p-3 border-bottom">
                                            <h5 class="mb-0">{{translate('messages.Business management')}}</h5>
                                            <div class="check-item pb-0 w-auto">
                                                <div class="form-group form-check d-flex align-items-center gap-2 form--check inlines-0">
                                                    <label class="form-check-label me-3 pe-3 font-medium fz-14px text-title d-block" for="businessManagement">{{ translate('Select All') }}</label>
                                                    <input type="checkbox" class="form-check-input position-relative"
                                                           id="businessManagement">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="check--items-inner d-flex flex-wrap p-sm-3 p-2">
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="my_restaurant" class="form-check-input"
                                                           id="my_restaurant"
                                                        {{ in_array('my_restaurant', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="my_restaurant">{{ translate('messages.My Restaurant') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="restaurant_config" class="form-check-input"
                                                           id="restaurant_config"
                                                        {{ in_array('restaurant_config', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="restaurant_config">{{ translate('messages.Restaurant Config') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="business_plan" class="form-check-input"
                                                           id="business_plan"
                                                        {{ in_array('business_plan', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="business_plan">{{ translate('messages.Business Plan') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="my_qr_code" class="form-check-input"
                                                           id="MyQRCode"
                                                        {{ in_array('my_qr_code', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="MyQRCode">{{ translate('messages.My QR Code') }}</label>
                                                </div>
                                            </div>
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="notification_setup" class="form-check-input"
                                                           id="NotificationSetup"
                                                        {{ in_array('notification_setup', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="NotificationSetup">{{ translate('messages.Notification Setup') }}</label>
                                                </div>
                                            </div>
                                            @if (\App\CentralLogics\Helpers::check_website_builder_status())
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="custom_website" class="form-check-input"
                                                           id="CustomWebsite"
                                                        {{ in_array('custom_website', (array) json_decode($role['modules'])) ? 'checked' : '' }}>
                                                    <label class="form-check-label ml-2 text-dark" for="CustomWebsite">{{ translate('messages.Custom Website') }}</label>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="btn--container mt-4 justify-content-end">
                            <button type="reset" id="reset-btn" class="btn btn--reset">{{translate('messages.reset')}}</button>
                            <button type="submit" class="btn btn--primary">{{translate('messages.update')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        "use strict";

        $(document).ready(function () {

            const moduleSelectAllIDs = [
                'general',
                'promotions',
                'foodManagement',
                'orderManagements',
                'walletManagement',
                'employeeManagement',
                'reports',
                'businessManagement'
            ];

            $('#select-all').on('change', function () {
                const checked = $(this).is(':checked');
                $('input.form-check-input[type="checkbox"]').not(this).prop('checked', checked).trigger('change');
            });

            moduleSelectAllIDs.forEach(function (id) {
                $('#' + id).on('change', function () {
                    let container = $(this).closest('.bg-white');
                    const checked = $(this).is(':checked');
                    container.find('input.form-check-input[type="checkbox"]').not(this).prop('checked', checked);
                    updateGlobalCheckbox();
                });
            });

            $('input.form-check-input[type="checkbox"]').not('#select-all').on('change', function () {
                const $this = $(this);

                if (!moduleSelectAllIDs.includes($this.attr('id'))) {
                    let container = $this.closest('.bg-white');
                    let total = container.find('input.form-check-input[type="checkbox"]').not(function () {
                        return moduleSelectAllIDs.includes($(this).attr('id'));
                    }).length;

                    let checked = container.find('input.form-check-input[type="checkbox"]:checked').not(function () {
                        return moduleSelectAllIDs.includes($(this).attr('id'));
                    }).length;

                    container.find('input.form-check-input[type="checkbox"]').filter(function () {
                        return moduleSelectAllIDs.includes($(this).attr('id'));
                    }).prop('checked', total === checked);
                }

                updateGlobalCheckbox();
            });

            function updateGlobalCheckbox() {
                let totalBoxes = $('input.form-check-input[type="checkbox"]').not('#select-all').length;
                let checkedBoxes = $('input.form-check-input[type="checkbox"]:checked').not('#select-all').length;
                $('#select-all').prop('checked', totalBoxes === checkedBoxes);
            }

            function updateAllCheckboxStates() {
                moduleSelectAllIDs.forEach(function (id) {
                    let container = $('#' + id).closest('.bg-white');
                    let total = container.find('input.form-check-input[type="checkbox"]').not('#' + id).length;
                    let checked = container.find('input.form-check-input[type="checkbox"]:checked').not('#' + id).length;
                    $('#' + id).prop('checked', total === checked && total > 0);
                });

                updateGlobalCheckbox();
            }

            updateAllCheckboxStates();
        });

    </script>
@endpush
