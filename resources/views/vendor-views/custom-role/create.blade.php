@extends('layouts.vendor.app')
@section('title',translate('Create Role'))
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
                        {{ translate('Employee Role') }}
                    </span>
                    </h2>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Content Row -->
        <div class="card mb-3">
            <div class="cards-header border-bottom p-20">
                <h3 class="mb-1">{{translate('messages.Role form')}}</h3>
                <p class="mb-0">{{translate('messages.Create role and assignee the role module & usage permission.')}}</p>
            </div>
            <div class="card-body">
                <form action="{{route('vendor.custom-role.create')}}" method="post">
                    @csrf
                    <div class="bg-light mb-20 rounded p-3">
                        @php($language = \App\Models\BusinessSetting::where('key','language')->first())
                        @php($language = $language->value ?? null)
                        @php($default_lang = str_replace('_', '-', app()->getLocale()))
                        @if ($language)
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
                        <input type="hidden" name="lang[]" value="default">

                        <div class="form-group mb-0 lang_form" id="default-form">
                            <label class="form-label input-label " for="name">{{ translate('messages.role_name') }} ({{ translate('messages.default') }})</label>
                            <input type="text" name="name[]" class="form-control" placeholder="{{translate('role_name_example')}}" maxlength="191"   >
                        </div>

                        @if ($language)
                            @foreach(json_decode($language) as $lang)
                                <div class="form-group mb-0 d-none lang_form" id="{{$lang}}-form">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.role_name')}} ({{strtoupper($lang)}})</label>
                                    <input type="text" name="name[]" class="form-control" placeholder="{{translate('role_name_example')}}" maxlength="191"  >
                                </div>
                                <input type="hidden" name="lang[]" value="{{$lang}}">
                            @endforeach
                        @endif
                    </div>

                    <div class="bg-light rounded p-20">
                        <div class="d-flex align-items-center flex-sm-nowrap flex-wrap gap-1 justify-content-between mb-20">
                            <h4 class="input-label m-0 text-capitalize">{{translate('messages.Module Wise Permission')}} : </h4>
                            <div class="check-item pb-0 w-auto">
                                <div class="form-group  form-check check-custom d-flex align-items-center gap-2 form--check inlines-0">
                                    <label class="form-check-label  me-3 pe-3 font-medium fz-14px text-title d-block" for="select-all">{{ translate('All Module Permission') }}</label>
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
                                                       id="dashboard">
                                                <label class="form-check-label ml-0 text-dark" for="dashboard">{{translate('messages.Dashboard')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="chat" class="form-check-input"
                                                       id="chat">
                                                <label class="form-check-label ml-0  text-dark" for="chat">{{translate('messages.chat')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="pos" class="form-check-input"
                                                       id="pos">
                                                <label class="form-check-label ml-0  text-dark" for="pos">{{translate('messages.pos_system')}}</label>
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
                                                       id="new_ads">
                                                <label class="form-check-label ml-0 text-dark" for="new_ads">{{translate('messages.New Ads')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group mb-0 form-check form--check">
                                                <input type="checkbox" name="modules[]" value="ads_list" class="form-check-input"
                                                       id="ads_list">
                                                <label class="form-check-label ml-0 text-dark" for="ads_list">{{translate('messages.Ads List')}}</label>
                                            </div>
                                        </div>
                                        @if (
                                            addon_published_status('ReelsModule')
                                            && \App\CentralLogics\Helpers::get_business_settings('vendor_can_upload_reels')
                                        )
                                            <div class="check-item mb-2 p-0">
                                                <div class="form-group mb-0 form-check form--check">
                                                    <input type="checkbox" name="modules[]" value="reels" class="form-check-input"
                                                           id="reels">
                                                    <label class="form-check-label ml-0 text-dark" for="reels">{{translate('messages.reels')}}</label>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="campaign" class="form-check-input"
                                                       id="campaign">
                                                <label class="form-check-label ml-0  text-dark" for="campaign">{{translate('messages.campaign')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="bogo_offer" class="form-check-input"
                                                       id="bogo_offer">
                                                <label class="form-check-label ml-0  text-dark" for="bogo_offer">{{translate('messages.BOGO_Offer')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="happy_hour" class="form-check-input"
                                                       id="happy_hour">
                                                <label class="form-check-label ml-0  text-dark" for="happy_hour">{{translate('messages.Happy_Hour')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="coupon" class="form-check-input"
                                                       id="coupon">
                                                <label class="form-check-label ml-0  text-dark" for="coupon">{{translate('messages.coupon')}}</label>
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
                                                       id="food">
                                                <label class="form-check-label ml-0  text-dark" for="food">{{translate('messages.food')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="category" class="form-check-input"
                                                       id="category">
                                                <label class="form-check-label ml-0  text-dark" for="category">{{translate('messages.category')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="addon" class="form-check-input"
                                                       id="addon">
                                                <label class="form-check-label ml-0  text-dark" for="addon">{{translate('messages.addon')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="reviews" class="form-check-input"
                                                       id="reviews">
                                                <label class="form-check-label ml-0  text-dark" for="reviews">{{translate('messages.Reviews')}}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="bg-white rounded">
                                    <div class="d-flex align-items-center justify-content-between gap-1 flex-wrap p-3 border-bottom">
                                        <h5 class="mb-0">{{translate('messages.Order management')}}</h5>
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
                                                       id="regular_order">
                                                <label class="form-check-label ml-0  text-dark" for="regular_order">{{translate('messages.Regular Orders')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="subscription_order" class="form-check-input"
                                                       id="subscription_order">
                                                <label class="form-check-label ml-0  text-dark" for="subscription_order">{{translate('messages.Subscription Orders')}}</label>
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
                                                       id="my_wallet">
                                                <label class="form-check-label ml-0  text-dark" for="my_wallet">{{translate('messages.My Wallet')}}</label>
                                            </div>
                                        </div>
                                        {{-- <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="wallet_method" class="form-check-input"
                                                       id="wallet_method">
                                                <label class="form-check-label ml-0  text-dark" for="wallet_method">{{translate('messages.Wallet Method')}}</label>
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
                                                       id="role_management">
                                                <label class="form-check-label ml-0  text-dark" for="role_management">{{translate('messages.Role Management')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="all_employee" class="form-check-input"
                                                       id="all_employee">
                                                <label class="form-check-label ml-0  text-dark" for="all_employee">{{translate('messages.All Employee')}}</label>
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
                                    <div class="check--items-inner custom-em d-flex flex-wrap p-sm-3 p-2">
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="expense_report" class="form-check-input"
                                                       id="expense_report">
                                                <label class="form-check-label ml-0  text-dark" for="expense_report">{{translate('messages.Expense Report')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="transaction" class="form-check-input"
                                                       id="transaction">
                                                <label class="form-check-label ml-0  text-dark" for="transaction">{{translate('messages.transaction')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="disbursement" class="form-check-input"
                                                       id="disbursement">
                                                <label class="form-check-label ml-0  text-dark" for="disbursement">{{translate('messages.disbursement')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="order_report" class="form-check-input"
                                                       id="order_report">
                                                <label class="form-check-label ml-0  text-dark" for="order_report">{{translate('messages.Order Report')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="food_report" class="form-check-input"
                                                       id="food_report">
                                                <label class="form-check-label ml-0  text-dark" for="food_report">{{translate('messages.Food Report')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="tax_report" class="form-check-input"
                                                       id="tax_report">
                                                <label class="form-check-label ml-0  text-dark" for="tax_report">{{translate('messages.Tax Report')}}</label>
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
                                    <div class="check--items-inner custom-em d-flex flex-wrap p-sm-3 p-2">
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="my_restaurant" class="form-check-input"
                                                       id="my_restaurant">
                                                <label class="form-check-label ml-0  text-dark" for="my_restaurant">{{translate('messages.My Restaurant')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="restaurant_config" class="form-check-input"
                                                       id="restaurant_config">
                                                <label class="form-check-label ml-0  text-dark" for="restaurant_config">{{translate('messages.Restaurant Config')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="business_plan" class="form-check-input"
                                                       id="business_plan">
                                                <label class="form-check-label ml-0  text-dark" for="business_plan">{{translate('messages.Business Plan')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="my_qr_code" class="form-check-input"
                                                       id="MyQRCode">
                                                <label class="form-check-label ml-0  text-dark" for="MyQRCode">{{translate('messages.My QR Code')}}</label>
                                            </div>
                                        </div>
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="notification_setup" class="form-check-input"
                                                       id="NotificationSetup">
                                                <label class="form-check-label ml-0  text-dark" for="NotificationSetup">{{translate('messages.Notification Setup')}}</label>
                                            </div>
                                        </div>
                                        @if (\App\CentralLogics\Helpers::check_website_builder_status())
                                        <div class="check-item mb-2 p-0">
                                            <div class="form-group form-check form--check">
                                                <input type="checkbox" name="modules[]" value="custom_website" class="form-check-input"
                                                       id="CustomWebsite">
                                                <label class="form-check-label ml-0  text-dark" for="CustomWebsite">{{translate('messages.Custom Website')}}</label>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pb-3">
                        <div class="btn--container justify-content-end">
                            <button type="reset" id="reset_btn" class="btn btn--reset">
                                {{translate('messages.reset')}}
                            </button>
                            <button type="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header border-0 py-2">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">{{translate('messages.roles_table')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$rl->total()}}</span></h5>
                            <form >

                                <!-- Search -->
                                <div class="input-group input--group border rounded">
                                    <input id="datatableSearch_" type="search" name="search" class="form-control border-0" value="{{ request()?->search ?? null }}" placeholder="{{ translate('messages.Ex :') }}  {{ translate('Search by Role Name') }}" aria-label="{{translate('messages.search')}}">
                                    <button type="submit" class="btn btn--reset py-1 px-2">
                                        <i class="tio-search"></i>
                                    </button>
                                </div>
                                <!-- End Search -->
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive datatable-custom">
                            <table id="columnSearchDatatable"
                                   class="table table-borderless table-thead-borderedless table-align-middle card-table"
                                   data-hs-datatables-options='{
                                 "order": [],
                                 "orderCellsTop": true,
                                 "paging":false
                               }'>
                                <thead class="global-bg-box">
                                <tr>
                                    <th class="w-70px">{{ translate('messages.sl') }}</th>
                                    <th class="w-100px">{{translate('messages.role_name')}}</th>
                                    <th class="w-200px">{{translate('messages.modules')}}</th>
                                    <th class="w-80px">{{translate('messages.created_at')}}</th>
                                    <th scope="col" class="w-80px text-center">{{translate('messages.action')}}</th>
                                </tr>
                                </thead>
                                <tbody  id="set-rows">
                                @foreach($rl as $k => $r)
                                    <tr>
                                        <td scope="row">{{$k+$rl->firstItem()}}</td>
                                        <td>
                                             <div class="d-flex align-items-center gap-12">
                                                <div class="min-w-58 w-58 h-58 rounded overflow-hidden">
                                                    <img src="{{dynamicAsset('assets/admin/img/160x160/img2.jpg')}}" alt="public">
                                                </div>
                                                <div class="content">
                                                    <h4 class="text-title m-0 font-regular">{{Str::limit($r['name'],20,'...')}}</h4>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-capitalize">
                                            @if($r['modules'] != null)
                                                @foreach((array)json_decode($r['modules']) as $key => $m)
                                                    {{translate(str_replace('_',' ',$m))}},
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            {{  Carbon\Carbon::parse($r['created_at'])->locale(app()->getLocale())->translatedFormat('d M Y') }}
                                        </td>
                                        <td>
                                            <div class="btn--container justify-content-center">
                                                <a class="btn action-btn btn--primary btn-outline-primary offcanvas-trigger" data-target="#employee-offcanvas" href="javascript:void(0)">
                                                    <i class="tio-invisible"></i>
                                                </a>
                                                <a class="btn action-btn btn--primary btn-outline-primary"
                                                   href="{{route('vendor.custom-role.edit',[$r['id']])}}" title="{{translate('messages.edit_role')}}"><i class="tio-edit"></i>
                                                </a>
                                                <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:"
                                                   data-id="role-{{$r['id']}}" data-message="{{translate('messages.Want_to_delete_this_role')}}" title="{{translate('messages.delete_role')}}"><i class="tio-delete-outlined"></i>
                                                </a>
                                                <form action="{{route('vendor.custom-role.delete',[$r['id']])}}"
                                                      method="post" id="role-{{$r['id']}}">
                                                    @csrf @method('delete')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            @if(count($rl) === 0)
                                <div class="empty--data">
                                    <img src="{{dynamicAsset('assets/admin/img/empty.png')}}" alt="public">
                                    <h5>
                                        {{translate('no_data_found')}}
                                    </h5>
                                </div>
                            @endif
                            <div class="page-area">
                                <table>
                                    <tfoot>
                                    {!! $rl->links() !!}
                                    </tfoot>
                                </table>
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
        $(document).ready(function() {
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));
        });
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

        });

    </script>
@endpush
