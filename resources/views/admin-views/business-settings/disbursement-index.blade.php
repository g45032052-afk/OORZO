@extends('layouts.admin.app')

@section('title', translate('messages.Disbursement_settings'))


@section('content')
    <div class="content">
         <form action="{{ route('admin.business-settings.update-disbursement') }}" method="post" enctype="multipart/form-data">
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
                @php($disbursement_type = \App\Models\BusinessSetting::where('key', 'disbursement_type')->first())
                @php($disbursement_type = $disbursement_type ? $disbursement_type->value : 'manual')
                @php($restaurant_disbursement_command = \App\Models\BusinessSetting::where('key', 'restaurant_disbursement_command')->first())
                @php($restaurant_disbursement_command = $restaurant_disbursement_command ? $restaurant_disbursement_command->value : '')
                @php($dm_disbursement_command = \App\Models\BusinessSetting::where('key', 'dm_disbursement_command')->first())
                @php($dm_disbursement_command = $dm_disbursement_command ? $dm_disbursement_command->value : '')

                <div class="card card-body mb-3" id="disbursement_setup_section">
                    <h3 class="mb-0">{{ translate('messages.Disbursement_Setup') }}</h3>
                    <p class="fs-12 mb-0">{{ translate('messages.Configure how and when payout requests are generated for restaurants and delivery partners.') }}</p>
                </div>
                <div class="card card-body mb-3" id="disbursement_request_type_section">
                    @if($disbursement_type == 'automated')
                    <div class="mb-3 text-right">
                        <button type="button" class="btn btn--primary" data-toggle="modal" data-target="#myModal">{{ translate('messages.Check_Dependencies') }}</button>
                    </div>
                    @endif
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-6">
                             <div class="">
                                <h4 class="mb-0">{{ translate('messages.Disbursement Request Type') }}</h4>
                                <p class="fs-12 mb-0">{{ translate('messages.Choose Manual or Automated Disbursement Requests. In Automated mode withdrawal requests for disbursement are generated automatically. In Manual mode restaurants need to request withdrawals manually.') }}</p>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="resturant-type-group border">
                                <label class="form-check form--check mr-2 mr-md-4 flex-grow-1">
                                    <input class="form-check-input" type="radio" value="manual"
                                            name="disbursement_type" id="disbursement_type"
                                        {{ $disbursement_type == 'manual' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{ translate('Manual_Request') }}
                                    </span>
                                </label>
                                <label class="form-check form--check mr-2 mr-md-4 flex-grow-1">
                                    <input class="form-check-input" type="radio" value="automated"
                                            name="disbursement_type" id="disbursement_type2"
                                        {{ $disbursement_type == 'automated' ? 'checked' : '' }}>
                                    <span class="form-check-label">
                                        {{ translate('Automated_Request') }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-body mb-3 automated_disbursement_section {{ $disbursement_type == 'manual' ? 'd-none' : '' }}" id="system_php_path_section">
                    <div class="d-flex gap-3 justify-content-between align-items-center flex-wrap mb-20">
                        <div class="">
                            <h4 class="mb-0">{{ translate('messages.System PHP Path') }}</h4>
                            <p class="fs-12 mb-0">{{ translate('messages.Enter the server’s PHP executable path required for automated disbursement processing.') }}</p>
                        </div>
                        <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info" style="--bs-bg-opacity: 0.1;">
                            <span class="text-info lh-1 fs-14">
                                <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                            </span>
                            <span>
                                {{ translate('messages.To learn more click') }}
                                <a href="javascript:" data-toggle="offcanvas" data-target="#offcanvasSetupGuide" class="font-semibold text-primary text-underline offcanvas-trigger">{{ translate('messages.How to get it?') }} </a>
                            </span>
                        </div>
                    </div>
                    <div class="bg-light rounded-10 p-12 p-xxl-20">
                        @php($system_php_path = \App\Models\BusinessSetting::where('key', 'system_php_path')->first())
                        @php($system_php_path = $system_php_path ? $system_php_path->value : '')
                        <input type="text" placeholder="{{translate('Ex:_/usr/bin/php')}}" class="form-control h--45px" min="0" name="system_php_path" value="{{ $system_php_path }}" required>
                    </div>
                </div>
                <div class="card card-body mb-3 automated_disbursement_section {{ $disbursement_type == 'manual' ? 'd-none' : '' }}" id="disbursement_request_setup_section">
                    <div class="mb-20">
                        <h4 class="mb-0">{{ translate('messages.Restaurant Panel Disbursement Request') }}</h4>
                        <p class="fs-12 mb-0">{{ translate('messages.Set the rules for generating and completing payout requests for restaurant partners.') }}</p>
                    </div>

                    <div class="bg-light rounded-10 p-12 p-xxl-20">
                        @php($restaurant_disbursement_waiting_time = \App\Models\BusinessSetting::where('key', 'restaurant_disbursement_waiting_time')->first())
                        @php($restaurant_disbursement_waiting_time = $restaurant_disbursement_waiting_time ? $restaurant_disbursement_waiting_time->value : '')
                        <div class="row g-3">
                            @php($restaurant_disbursement_time_period = \App\Models\BusinessSetting::where('key', 'restaurant_disbursement_time_period')->first())
                            @php($restaurant_disbursement_time_period = $restaurant_disbursement_time_period ? $restaurant_disbursement_time_period->value : 1)
                            <div class='{{ $restaurant_disbursement_time_period=='weekly'?'col-sm-6 col-lg-4':'col-12' }}' id="restaurant_time_period_section">
                                <div class="form-group lang_form default-form mb-0">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label text-capitalize m-0">
                                            {{translate('Generate_Disbursements')}}
                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Choose_how_the_disbursement_request_will_be_generated:_Monthly,_Weekly_or_Daily.')}}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                        </label>
                                    </div>
                                    <select name="restaurant_disbursement_time_period" id="restaurant_disbursement_time_period" class="form-control" required>
                                        <option value="daily" {{ $restaurant_disbursement_time_period=='daily'?'selected':'' }}>
                                            {{ translate('messages.daily') }}
                                        </option>
                                        <option value="weekly" {{ $restaurant_disbursement_time_period=='weekly'?'selected':'' }}>
                                            {{ translate('messages.weekly') }}
                                        </option>
                                        <option value="monthly" {{ $restaurant_disbursement_time_period=='monthly'?'selected':'' }}>
                                            {{ translate('messages.monthly') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class='col-sm-6 col-lg-4 {{ $restaurant_disbursement_time_period=='weekly'?'':'d-none' }}' id="restaurant_week_day_section">
                                @php($restaurant_disbursement_week_start = \App\Models\BusinessSetting::where('key', 'restaurant_disbursement_week_start')->first())
                                @php($restaurant_disbursement_week_start = $restaurant_disbursement_week_start ? $restaurant_disbursement_week_start->value : 'saturday')
                                <div class="form-group lang_form default-form mb-0">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label text-capitalize m-0">
                                            {{translate('Beginning_of_the_Week')}}
                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Choose_when_the_week_starts_for_the_new_disbursement_request._This_section_will_only_appear_when_weekly_disbursement_is_selected.')}}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                        </label>
                                    </div>
                                    <select name="restaurant_disbursement_week_start" id="" class="form-control" required>
                                        <option value="saturday" {{ $restaurant_disbursement_week_start == 'saturday'?'selected':'' }}>
                                            {{ translate('messages.saturday') }}
                                        </option>
                                        <option value="sunday" {{ $restaurant_disbursement_week_start == 'sunday'?'selected':'' }}>
                                            {{ translate('messages.sunday') }}
                                        </option>
                                        <option value="monday" {{ $restaurant_disbursement_week_start == 'monday'?'selected':'' }}>
                                            {{ translate('messages.monday') }}
                                        </option>
                                        <option value="tuesday" {{ $restaurant_disbursement_week_start == 'tuesday'?'selected':'' }}>
                                            {{ translate('messages.tuesday') }}
                                        </option>
                                        <option value="wednesday" {{ $restaurant_disbursement_week_start == 'wednesday'?'selected':'' }}>
                                            {{ translate('messages.wednesday') }}
                                        </option>
                                        <option value="thursday" {{ $restaurant_disbursement_week_start == 'thursday'?'selected':'' }}>
                                            {{ translate('messages.thursday') }}
                                        </option>
                                        <option value="friday" {{ $restaurant_disbursement_week_start == 'friday'?'selected':'' }}>
                                            {{ translate('messages.friday') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class='col-sm-6 col-lg-4'>
                                @php($restaurant_disbursement_create_time = \App\Models\BusinessSetting::where('key', 'restaurant_disbursement_create_time')->first())
                                @php($restaurant_disbursement_create_time = $restaurant_disbursement_create_time ? $restaurant_disbursement_create_time->value : 1)
                                <div class="form-group lang_form default-form mb-0">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label text-capitalize m-0">
                                            {{translate('Generated_Time')}}
                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Define_when_the_new_disbursement_request_will_be_generated_automatically.')}}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                        </label>
                                    </div>
                                    <input type="time" placeholder="{{translate('Ex:_7')}}" class="form-control h--45px" name="restaurant_disbursement_create_time" value="{{ $restaurant_disbursement_create_time }}" required>
                                </div>
                            </div>
                            <div class='col-sm-6 col-lg-4'>
                                @php($restaurant_disbursement_min_amount = \App\Models\BusinessSetting::where('key', 'restaurant_disbursement_min_amount')->first())
                                @php($restaurant_disbursement_min_amount = $restaurant_disbursement_min_amount ? $restaurant_disbursement_min_amount->value : 'saturday')
                                <div class="form-group lang_form default-form mb-0">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label text-capitalize m-0">
                                            {{translate('Min Amount for Auto Generating Request')}} ($)
                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Enter_the_minimum_amount_to_be_eligible_for_generating_an_auto-disbursement_request.')}}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                        </label>
                                    </div>
                                    <input type="number" placeholder="{{translate('Ex:_100')}}" class="form-control h--45px" min="0" name="restaurant_disbursement_min_amount" value="{{ $restaurant_disbursement_min_amount }}" required>
                                </div>
                            </div>
                            @php($restaurant_disbursement_waiting_time = \App\Models\BusinessSetting::where('key', 'restaurant_disbursement_waiting_time')->first())
                            @php($restaurant_disbursement_waiting_time = $restaurant_disbursement_waiting_time ? $restaurant_disbursement_waiting_time->value : '')
                            <div class="col-sm-6 col-lg-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label text-capitalize m-0">
                                        {{translate('Disbursement_Completion_Timeline')}}
                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Enter_the_number_of_days_in_which_the_disbursement_will_be_completed.')}}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                </div>
                                <input type="number" placeholder="{{translate('Ex:_7')}}" min="0" class="form-control h--45px" name="restaurant_disbursement_waiting_time" value="{{ $restaurant_disbursement_waiting_time }}" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-body mb-3 automated_disbursement_section {{ $disbursement_type == 'manual' ? 'd-none' : '' }}">
                    <div class="mb-20">
                        <h4 class="mb-0">{{ translate('messages.Delivery Man Disbursement Request') }}</h4>
                        <p class="fs-12 mb-0">{{ translate('messages.Set the rules for generating and completing payout requests for delivery partners.') }}</p>
                    </div>

                    <div class="bg-light rounded-10 p-12 p-xxl-20">
                        @php($dm_disbursement_time_period = \App\Models\BusinessSetting::where('key', 'dm_disbursement_time_period')->first())
                        @php($dm_disbursement_time_period = $dm_disbursement_time_period ? $dm_disbursement_time_period->value : '')
                        <div class="row g-3">
                            <div class='{{ $dm_disbursement_time_period=='weekly'?'col-sm-6 col-lg-4':'col-12' }}' id="dm_time_period_section">
                                <div class="form-group lang_form default-form mb-0">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label text-capitalize m-0">
                                            {{translate('Generate_Disbursements')}}
                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Choose_how_the_disbursement_request_will_be_generated:_Monthly,_Weekly_or_Daily.')}}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                        </label>
                                    </div>
                                    <select name="dm_disbursement_time_period" id="dm_disbursement_time_period" class="form-control" required>
                                        <option value="daily" {{ $dm_disbursement_time_period=='daily'?'selected':'' }}>
                                            {{ translate('messages.daily') }}
                                        </option>
                                        <option value="weekly" {{ $dm_disbursement_time_period=='weekly'?'selected':'' }}>
                                            {{ translate('messages.weekly') }}
                                        </option>
                                        <option value="monthly" {{ $dm_disbursement_time_period=='monthly'?'selected':'' }}>
                                            {{ translate('messages.monthly') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                                @php($dm_disbursement_week_start = \App\Models\BusinessSetting::where('key', 'dm_disbursement_week_start')->first())
                                @php($dm_disbursement_week_start = $dm_disbursement_week_start ? $dm_disbursement_week_start->value : 'saturday')
                            <div class='col-sm-6 col-lg-4 {{ $dm_disbursement_time_period=='weekly'?'':'d-none' }}' id="dm_week_day_section">
                                <div class="form-group lang_form default-form mb-0">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label text-capitalize m-0">
                                            {{translate('Beginning of the Week')}}
                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Choose_when_the_week_starts_for_the_new_disbursement_request._This_section_will_only_appear_when_weekly_disbursement_is_selected.')}}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                        </label>
                                    </div>
                                    <select name="dm_disbursement_week_start" id="" class="form-control" required>
                                        <option value="saturday" {{ $dm_disbursement_week_start == 'saturday'?'selected':'' }}>
                                            {{ translate('messages.saturday') }}
                                        </option>
                                        <option value="sunday" {{ $dm_disbursement_week_start == 'sunday'?'selected':'' }}>
                                            {{ translate('messages.sunday') }}
                                        </option>
                                        <option value="monday" {{ $dm_disbursement_week_start == 'monday'?'selected':'' }}>
                                            {{ translate('messages.monday') }}
                                        </option>
                                        <option value="tuesday" {{ $dm_disbursement_week_start == 'tuesday'?'selected':'' }}>
                                            {{ translate('messages.tuesday') }}
                                        </option>
                                        <option value="wednesday" {{ $dm_disbursement_week_start == 'wednesday'?'selected':'' }}>
                                            {{ translate('messages.wednesday') }}
                                        </option>
                                        <option value="thursday" {{ $dm_disbursement_week_start == 'thursday'?'selected':'' }}>
                                            {{ translate('messages.thursday') }}
                                        </option>
                                        <option value="friday" {{ $dm_disbursement_week_start == 'friday'?'selected':'' }}>
                                            {{ translate('messages.friday') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class='col-sm-6 col-lg-4'>
                                @php($dm_disbursement_create_time = \App\Models\BusinessSetting::where('key', 'dm_disbursement_create_time')->first())
                                @php($dm_disbursement_create_time = $dm_disbursement_create_time ? $dm_disbursement_create_time->value : 1)
                                <div class="form-group lang_form default-form mb-0">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label text-capitalize m-0">
                                            {{translate('Generated_Time')}}
                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Define_when_the_new_disbursement_request_will_be_generated_automatically.')}}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                        </label>
                                    </div>
                                    <input type="time" placeholder="{{translate('Ex:_7')}}" class="form-control h--45px" name="dm_disbursement_create_time" value="{{ $dm_disbursement_create_time }}" required>
                                </div>
                            </div>
                            <div class='col-sm-6 col-lg-4'>
                                @php($dm_disbursement_min_amount = \App\Models\BusinessSetting::where('key', 'dm_disbursement_min_amount')->first())
                                @php($dm_disbursement_min_amount = $dm_disbursement_min_amount ? $dm_disbursement_min_amount->value : 'saturday')
                                <div class="form-group lang_form default-form mb-0">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label text-capitalize m-0">
                                            {{translate('Min Amount for Auto Generating Request')}} ($)
                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Enter_the_minimum_amount_to_be_eligible_for_generating_an_auto-disbursement_request.')}}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                        </label>
                                    </div>
                                    <input type="number" placeholder="{{translate('Ex:_100')}}" class="form-control h--45px" min="0" name="dm_disbursement_min_amount" value="{{ $dm_disbursement_min_amount }}" required>
                                </div>
                            </div>
                            @php($dm_disbursement_waiting_time = \App\Models\BusinessSetting::where('key', 'dm_disbursement_waiting_time')->first())
                            @php($dm_disbursement_waiting_time = $dm_disbursement_waiting_time ? $dm_disbursement_waiting_time->value : '')
                            <div class="col-sm-6 col-lg-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label text-capitalize m-0">
                                        {{translate('Disbursement Completion Timeline')}}
                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('Enter_the_number_of_days_in_which_the_disbursement_will_be_completed.')}}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                </div>
                                <input type="number" min="0" placeholder="{{translate('Ex:_7')}}" class="form-control h--45px" name="dm_disbursement_waiting_time" value="{{ $dm_disbursement_waiting_time }}" required>
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
            <h3 class="mb-0">{{ translate('messages.Disbursement Guideline') }}</h3>
            <button type="button"
                class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                aria-label="Close">&times;</button>
        </div>

        <div class="custom-offcanvas-body offcanvas-height-100 py-3 px-md-4 px-3">
             <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0 collapsed"
                        type="button" data-toggle="collapse" data-target="#disbursement_setup" aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span
                            class="font-semibold text-left fs-14 text-title">{{ translate('messages.Disbursement Setup') }}</span>
                    </button>
                    <a href="#disbursement_setup_section"
                        class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                </div>
                <div class="collapse mt-3 show" id="disbursement_setup">
                    <div class="card card-body">
                        <div class="">
                            <h5 class="mb-3">{{translate('Disbursement Setup')}}</h5>
                            <p class="fs-12 mb-0">
                                {{ translate('messages.The Disbursement Setup feature allows the admin to manage the payout of earnings to restaurants and delivery personnel. It ensures timely and accurate settlements based on completed orders, commissions, and deductions.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>


            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0 collapsed"
                        type="button" data-toggle="collapse" data-target="#disbursement_request_type" aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span
                            class="font-semibold text-left fs-14 text-title">{{ translate('messages.Disbursement Request Type') }}</span>
                    </button>
                    <a href="#disbursement_request_type_section"
                        class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                </div>
                <div class="collapse mt-3" id="disbursement_request_type">
                    <div class="card card-body">
                        <div class="">
                            <h5 class="mb-3">{{translate('Disbursement Request Type')}}</h5>
                            <p class="fs-12 mb-3">
                                {{ translate('messages.The system supports two types of disbursement requests for restaurants and delivery personnel: Manual and Automated. These settings determine how earnings are transferred from the platform to the recipients.') }}
                            </p>
                            <ul class="mb-0 fs-12">
                                <li class="font-semibold">
                                    {{ translate('messages.Manual Disbursement') }}
                                </li>
                                <p class="mb-3">
                                    {{ translate('messages.Admin reviews and approves each payout request before processing.') }}
                                </p>
                                <li class="font-semibold">
                                    {{ translate('messages.Automated Disbursement') }}
                                </li>
                                <p class="mb-3">
                                    {{ translate('messages.The system automatically processes payouts according to predefined schedules (daily, weekly, or monthly).') }}
                                </p>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0 collapsed"
                        type="button" data-toggle="collapse" data-target="#system_php_path" aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span
                            class="font-semibold text-left fs-14 text-title">{{ translate('messages.System PHP Path') }}</span>
                    </button>
                    <a href="#system_php_path_section"
                        class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                </div>
                <div class="collapse mt-3" id="system_php_path">
                    <div class="card card-body">
                        <div class="">
                            <h5 class="mb-3">{{translate('System PHP Path')}}</h5>
                            <p class="fs-12 mb-0">
                                {{ translate('messages.The System PHP Path specifies the location of the PHP executable file that will be used to run automated disbursement scripts. Setting the correct PHP path ensures that the system can execute scheduled or automated disbursement processes without errors.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                    <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0 collapsed"
                        type="button" data-toggle="collapse" data-target="#disbursement_request_setup" aria-expanded="true">
                        <div
                            class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                            <i class="tio-down-ui"></i>
                        </div>
                        <span
                            class="font-semibold text-left fs-14 text-title">{{ translate('messages.Disbursement Request Setup') }}</span>
                    </button>
                    <a href="#disbursement_request_setup_section"
                        class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                </div>
                <div class="collapse mt-3" id="disbursement_request_setup">
                    <div class="card card-body">
                        <div class="">
                            <h5 class="mb-3">{{translate('Disbursement Request Setup')}}</h5>
                            <p class="fs-12 mb-0">
                                {{ translate('messages.This feature allows the admin to configure how and when earnings are disbursed to restaurants and delivery personnel. Proper setup ensures timely payouts, automated processing, and compliance with operational rules.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        <?php
            $disbursementCronLine = '* * * * * cd ' . base_path() . ' && php artisan schedule:run >> /dev/null 2>&1';
            $disbursementSchedulerSupervisor = "[program:stackfood-scheduler]\n"
                . "process_name=%(program_name)s\n"
                . "command=php " . base_path('artisan') . " schedule:work\n"
                . "autostart=true\n"
                . "autorestart=true\n"
                . "user=www-data\n"
                . "numprocs=1\n"
                . "redirect_stderr=true\n"
                . "stdout_logfile=" . storage_path('logs/scheduler.log') . "\n"
                . "stopwaitsecs=60";
        ?>

        <div class="modal" id="myModal" tabindex="-1" role="dialog" aria-labelledby="disbursementSchedulerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="disbursementSchedulerModalLabel">{{ translate('Disbursement Scheduler Dependency') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="fs-13 mb-3">
                            {{ translate('When Automated Request is selected, Laravel\'s scheduler runs dm:disbursement and restaurant:disbursement based on the time period (daily / weekly / monthly) and create-time configured above. Pick ONE launcher for the scheduler.') }}
                        </p>

                        <div class="bg-light rounded p-3 mb-3">
                            <h6 class="mb-2">{{ translate('Option 1 — Cron drives the scheduler') }}</h6>
                            <p class="fs-12 mb-2">
                                {{ translate('Add this single line to your server crontab. Cron will trigger schedule:run every minute and Laravel decides which scheduled commands fire.') }}
                            </p>
                            <div class="input--group input-group">
                                <input type="text" value="{{ $disbursementCronLine }}" class="form-control" id="disbursementCronCommand" readonly>
                                <button type="button" class="btn btn-primary disbursementCronCopy">{{ translate('Copy') }}</button>
                            </div>
                        </div>

                        <div class="bg-light rounded p-3 mb-0">
                            <h6 class="mb-2">{{ translate('Option 2 — Supervisor drives the scheduler (no cron)') }}</h6>
                            <p class="fs-12 mb-2">
                                {{ translate('Use this if you can\'t install a cron entry (Docker, some shared hosts). Supervisor keeps schedule:work alive; it internally invokes schedule:run every 60 seconds.') }}
                            </p>
                            <textarea class="form-control mb-2" id="disbursementSchedulerSupervisorBlock" rows="10" readonly>{{ $disbursementSchedulerSupervisor }}</textarea>
                            <button type="button" class="btn btn-primary disbursementSchedulerSupervisorCopy">{{ translate('Copy') }}</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>


    </div>
@endsection
@push('script_2')
@php($flag = session('disbursement_exec'))
<script src="{{dynamicAsset('assets/admin/js/view-pages/business-settings-disbursement.js')}}"></script>
    <script>
        "use strict";
        $(document).on('ready', function() {
            @if ($disbursement_type == 'manual')
            $('.automated_disbursement_section').hide();
            @endif

            @if (isset($flag) && $flag)
                $('#myModal').modal('show');
            @endif

            $('.offcanvas-close-btn').on('click', function (e) {
                e.preventDefault();
                $('.custom-offcanvas').removeClass('open');
                $('#offcanvasOverlay').removeClass('show');
                $('html, body').animate({
                    scrollTop: $($(this).attr('href')).offset().top - 100
                }, 500);
            });
        });

        // Copy handlers for the scheduler-dependency modal. Registered as
        // document-delegated clicks (not inside a ready handler) so they bind
        // reliably regardless of jQuery's ready quirks.
        function disbursementCopyFromElement(id) {
            var el = document.getElementById(id);
            if (!el) return;
            el.select();
            el.setSelectionRange(0, 99999);
            try {
                document.execCommand('copy');
                if (typeof toastr !== 'undefined') toastr.success('{{ translate('Copied to clipboard!') }}');
            } catch (err) {
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(el.value).then(function () {
                        if (typeof toastr !== 'undefined') toastr.success('{{ translate('Copied to clipboard!') }}');
                    });
                }
            }
        }
        $(document).on('click', '.disbursementCronCopy', function (e) {
            e.preventDefault();
            disbursementCopyFromElement('disbursementCronCommand');
        });
        $(document).on('click', '.disbursementSchedulerSupervisorCopy', function (e) {
            e.preventDefault();
            disbursementCopyFromElement('disbursementSchedulerSupervisorBlock');
        });
    </script>
@endpush
