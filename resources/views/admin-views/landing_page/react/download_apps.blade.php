@php use App\CentralLogics\Helpers; @endphp
@extends('layouts.admin.app')

@section('title', translate('messages.landing_page_settings'))

@section('content')

<div class="content container-fluid">
    <div class="page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start">
            <h1 class="page-header-title text-capitalize">
                <div class="card-header-icon d-inline-flex mr-2 img">
                    <img src="{{ dynamicAsset('assets/admin/img/landing-page.png') }}" class="mw-26px" alt="public">
                </div>
                <span>
                    {{ translate('React_Landing_Page') }}
                </span>
            </h1>
        </div>
    </div>
    <div class="js-nav-scroller tabs-slide-wrap tabs-slide-language hs-nav-scroller-horizontal">
        @include('admin-views.landing_page.top_menu.react_landing_menu')
        <div class="arrow-area">
            <div class="button-prev align-items-center">
                <button type="button" class="btn btn-click-prev mr-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                    <i class="tio-chevron-left fs-24"></i>
                </button>
            </div>
            <div class="button-next align-items-center">
                <button type="button" class="btn btn-click-next ml-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                    <i class="tio-chevron-right fs-24"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="card py-3 px-xxl-4 px-3 mb-15 mt-4">
        <div class="row g-3 align-items-center justify-content-between">
            <div class="col-xxl-9 col-lg-8 col-md-7 col-sm-6">
                <div class="">
                    <h3 class="mb-1">{{ translate('Show_on_Website') }}</h3>
                    <p class="mb-0 gray-dark fs-12">{{translate('If you turn of the availability status, this section will not show in the website')}}</p>
                </div>
            </div>
            <div class="col-xxl-3 col-lg-4 col-md-5 col-sm-6">
                <div class="py-2 px-3 d-flex justify-content-between border rounded align-items-center w-300">
                    <h5 class="text-capitalize fw-normal mb-0">{{translate('Status') }}</h5>
                    <form
                        action="{{ route('admin.landing_page.statusUpdate', ['type' => 'react_landing_page', 'key' => 'react_download_apps_status']) }}"
                        method="get" id="AppCheckboxStatus_form">
                    </form>
                    <label class="toggle-switch toggle-switch-sm" for="AppCheckboxStatus">
                        <input type="checkbox" data-id="AppCheckboxStatus" data-type="status"
                               data-image-on="{{ dynamicAsset('assets/admin/img/status-ons.png') }}"
                               data-image-off="{{ dynamicAsset('assets/admin/img/off-danger.png') }}"
                               data-title-on="{{ translate('Do you want turn on this section ?') }}"
                               data-title-off="{{ translate('Do you want to turn off this section ?') }}"
                               data-text-on="<p>{{ translate('If you turn on this section will be show in react landing page.') }}"
                               data-text-off="<p>{{ translate('If you turn off this section will not be show in react landing page.') }}</p>"
                               class="toggle-switch-input  status dynamic-checkbox" id="AppCheckboxStatus"
                            {{ $react_download_apps_status?->value ? 'checked' : '' }}>
                        <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                    </label>
                </div>
            </div>
        </div>
    </div>


        <div class="card">
            <div class="card-header">
                <div class="">
                    <h3 class="mb-1">{{ translate('Download App Section') }}</h3>
                    <p class="mb-0 gray-dark fs-12">{{ translate('Manage mobile app download area including QR codes and app store buttons.') }}</p>
                </div>
            </div>
            <div class="card-body">
                <form class="validate-form" action="{{ route('admin.react_landing_page.settings', 'react-download-apps') }}" enctype="multipart/form-data"     method="post">
                    @csrf
                    <div class="row g-4">
                        <div class="col-lg-12">
                            <div class="card-custom-xl">
                                <div class="bg-light2 p-xl-20 p-3 rounded">
                                    @php($default_lang = str_replace('_', '-', app()->getLocale()))
                                    @if($language)
                                        <ul class="nav nav-tabs mb-4 border-">
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
                                    @endif

                                    <div class="lang_form default-form">
                                        <div class="row g-2">
                                                <input type="hidden" name="lang[]" value="default">
                                                <div class="col-12 col-md-6">
                                                    <label for="react_download_apps_title"  class="form-label fw-400">{{translate('title')}}  ({{ translate('default') }})
                                                        <span class="text-danger">*</span>
                                                            <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                            data-placement="right"
                                                            data-original-title="{{ translate('This is the main headline that encourages users to download your app. Keep it under 50 characters.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                    </label>
                                                    <input required id="react_download_apps_title" type="text" data-maxlength="50" class="form-control" placeholder="{{translate('messages.Enter_Title...')}}" name="react_download_apps_title[]"   value="{{ $react_download_apps_title?->getRawOriginal('value') ?? '' }}" >
                                                    <span class="text-body-light text-right d-block mt-1">0/50</span>
                                                </div>
                                            </div>
                                        </div>

                                        @if ($language)
                                            @forelse(json_decode($language) as $lang)
                                                <?php
                                                    if($react_download_apps_title?->translations){
                                                            $react_download_apps_title_translate = [];
                                                            foreach($react_download_apps_title->translations as $t)
                                                            {
                                                                if($t->locale == $lang && $t->key=='react_download_apps_title'){
                                                                    $react_download_apps_title_translate[$lang]['value'] = $t->value;
                                                                }
                                                            }
                                                        }
                                                    if($react_download_apps_sub_title?->translations){
                                                            $react_download_apps_sub_title_translate = [];
                                                            foreach($react_download_apps_sub_title->translations as $t)
                                                            {
                                                                if($t->locale == $lang && $t->key=='react_download_apps_sub_title'){
                                                                    $react_download_apps_sub_title_translate[$lang]['value'] = $t->value;
                                                                }
                                                            }
                                                        }
                                                    if($react_download_apps_tag?->translations){
                                                            $react_download_apps_tag_translate = [];
                                                            foreach($react_download_apps_tag->translations as $t)
                                                            {
                                                                if($t->locale == $lang && $t->key=='react_download_apps_tag'){
                                                                    $react_download_apps_tag_translate[$lang]['value'] = $t->value;
                                                                }
                                                            }
                                                        }
                                                ?>
                                                <input type="hidden" name="lang[]" value="{{$lang}}">
                                                <div class="d-none lang_form" id="{{$lang}}-form1">
                                                    <div class="row g-3">
                                                            <div class="col-12 col-md-6">
                                                                <label  for="react_download_apps_title{{$lang}}"  class="form-label fw-400">{{translate('title')}} ({{strtoupper($lang)}})
                                                                    <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                                        data-placement="right"
                                                                        data-original-title="{{ translate('This is the main headline that encourages users to download your app. Keep it under 20 characters.') }}">
                                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                                    </span>
                                                                </label>
                                                                <input  id="react_download_apps_title{{$lang}}"  type="text" data-maxlength="50" class="form-control"
                                                                        name="react_download_apps_title[]" placeholder="{{translate('messages.Enter_Title...')}}"
                                                                        value="{{ $react_download_apps_title_translate[$lang]['value'] ?? ''}}">
                                                                <span class="text-body-light text-right d-block mt-1">0/50</span>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <label  for="react_download_apps_sub_title{{$lang}}" class="form-label fw-400">{{translate('Subtitle')}} ({{strtoupper($lang)}})
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                                    data-placement="right"
                                                                    data-original-title="{{ translate('This text should explain the key benefits of downloading the app. Keep it under 100 characters.') }}">
                                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                                </span>
                                                                </label>
                                                                <input  id="react_download_apps_sub_title{{$lang}}" type="text" data-maxlength="100" class="form-control"
                                                                        placeholder="{{translate('Enter_Sub_Title')}}" name="react_download_apps_sub_title[]"
                                                                        value="{{ $react_download_apps_sub_title_translate[$lang]['value'] ?? ''}}">

                                                                <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                            </div>
                                                    </div>
                                                </div>
                                                @empty
                                            @endforelse
                                        @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">

                        </div>

                            <!-- <div class="col-md-6">
                                <label class="form-label d-block mb-2">
                                {{ translate('Banner') }}  <span class="text--primary">({{ translate('1:1') }} )</span>
                                </label>
                                <div class="position-relative d-inline-block">
                                    <label class="upload-img-3 m-0">
                                        <div class="img">
                                            <img  src="{{ Helpers::get_full_url('react_download_apps_image', $react_download_apps_image?->value,$react_download_apps_image?->storage[0]?->value ?? 'public','upload_1_1')}}"
                                            data-onerror-image="{{dynamicAsset('assets/admin/img/upload-3.png')}}"
                                            class="vertical-img max-w-187px onerror-image" alt="">
                                        </div>
                                        <input type="file" name="react_download_apps_image" hidden>
                                    </label>

                                    @if ($react_download_apps_image?->value)
                                    <span id="remove_image_1" class="remove_image_button remove-image"
                                            data-id="remove_image_1"
                                            data-title="{{translate('Warning!')}}"
                                            data-text="<p>{{translate('Are_you_sure_you_want_to_remove_this_image_?')}}</p>"
                                        > <i class="tio-clear"></i></span>
                                    @endif
                                </div>
                            </div> -->
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <div class="card-custom-static p-xxl-4 p-3">
                                    <div class="d-flex align-items-center gap-2 justify-content-between flex-sm-nowrap flex-wrap mb-20">
                                        <div>
                                            <h4 class="card-title text-title p-0 mb-1">
                                                <!-- <img src="http://localhost/stackfood/public/assets/admin/img/andriod.png" class="mr-2" alt=""> -->
                                                {{ translate('messages.Play store Download') }}
                                            </h4>
                                            <p class="m-0 fs-12">{{ translate('messages.Add the Play Store link to allow customers to download the app.') }}</p>
                                        </div>
                                        <label class="toggle-switch toggle-switch-sm m-0">
                                            <input type="checkbox"  id="react_download_apps_button_status"
                                            data-id="react_download_apps_button_status"
                                            data-type="toggle"
                                            data-image-on="{{ dynamicAsset('assets/admin/img/modal/play-store-on.png') }}"
                                            data-image-off="{{ dynamicAsset('assets/admin/img/modal/play-store-off.png') }}"
                                            data-title-on="<strong>{{translate('Want_to_enable_the_Customer_App_Download_button_here')}}</strong>"
                                            data-title-off="<strong>{{translate('Want_to_disable_the_Customer_App_Download_button')}}</strong>"
                                            data-text-on="<p>{{translate('If_enabled,_everyone_can_see_the_button_on_the_landing_page')}}</p>"
                                            data-text-off="<p>{{translate('If_disabled,_it_will_be_hidden_from_the_landing_page')}}</p>"
                                            class="status toggle-switch-input dynamic-checkbox-toggle"
                                            name="react_download_apps_button_status" value="1"  {{ $react_download_apps_button_status?->value ?? null  == 1 ? 'checked': ''  }}>
                                            <span class="toggle-switch-label text mb-0">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                    @if($app_url_android)
                                    <div class="__bg-F8F9FC-card">
                                        <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info bg-opacity-10 mt-2">
                                            <span class="text-info lh-1 fs-14 flex-shrink-0">
                                                <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                                            </span>
                                            <span>
                                                {{ translate('messages.App download button URL link is setup successfully. Data is synced from') }}
                                                <a target="_blank" href="{{ route('admin.business-settings.app-settings') }}#user-app-download-section" class="text-info text-underline font-semibold">{{ translate('messages.App & Web Settings') }}</a>
                                            </span>
                                        </div>
                                    </div>
                                    @else
                                    <div class="__bg-F8F9FC-card">
                                        <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-danger bg-opacity-10 mt-2">
                                            <span class="text-danger lh-1 fs-14 flex-shrink-0">
                                                <i class="tio-info"></i>
                                            </span>
                                            <span>
                                                {{ translate('messages.The app download button link is not set up yet. Please complete the setup from') }}
                                                <a target="_blank" href="{{ route('admin.business-settings.app-settings') }}#user-app-download-section" class="text-danger text-underline font-semibold">{{ translate('messages.App & Web Settings') }}</a>
                                                {{ translate('messages.to enable this button') }}.
                                            </span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card-custom-static p-xxl-4 p-3">
                                    <div class="d-flex align-items-center gap-2 justify-content-between flex-sm-nowrap flex-wrap mb-20">
                                        <div>
                                            <h4 class="card-title text-title p-0 mb-1">
                                                {{ translate('messages.Apple store Download') }}
                                            </h4>
                                            <p class="m-0 fs-12">{{ translate('messages.Add the Apple Store link to allow customers to download the app.') }}</p>
                                        </div>
                                        <label class="toggle-switch toggle-switch-sm m-0">
                                            <input type="checkbox"
                                            id="react_download_apps_link_status"
                                                    data-id="react_download_apps_link_status"
                                                    data-type="toggle"
                                                    data-image-on="{{ dynamicAsset('assets/admin/img/modal/apple-on.png') }}"
                                                    data-image-off="{{ dynamicAsset('assets/admin/img/modal/apple-off.png') }}"
                                                    data-title-on="<strong>{{translate('Want_to_enable_the_Customer_App_Download_button_here')}}</strong>"
                                                    data-title-off="<strong>{{translate('Want_to_disable_the_Customer_App_Download_button')}}</strong>"
                                                    data-text-on="<p>{{translate('If_enabled,_everyone_can_see_the_button_on_the_landing_page')}}</p>"
                                                    data-text-off="<p>{{translate('If_disabled,_it_will_be_hidden_from_the_landing_page')}}</p>"
                                                    class="status toggle-switch-input dynamic-checkbox-toggle"
                                                    name="react_download_apps_link_status" value="1"  {{ $react_download_apps_link_data['react_download_apps_link_status'] ?? null  == 1 ? 'checked': ''  }}>
                                            <span class="toggle-switch-label text mb-0">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                    @if($app_url_ios)
                                    <div class="__bg-F8F9FC-card">
                                        <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info bg-opacity-10 mt-2">
                                            <span class="text-info lh-1 fs-14 flex-shrink-0">
                                                <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                                            </span>
                                            <span>
                                                {{ translate('messages.App download button URL link is setup successfully. Data is synced from') }}
                                                <a target="_blank" href="{{ route('admin.business-settings.app-settings') }}#user-app-download-section" class="text-info text-underline font-semibold">{{ translate('messages.App & Web Settings') }}</a>
                                            </span>
                                        </div>
                                    </div>
                                    @else
                                    <div class="__bg-F8F9FC-card">
                                        <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-danger bg-opacity-10 mt-2">
                                            <span class="text-danger lh-1 fs-14 flex-shrink-0">
                                                <i class="tio-info"></i>
                                            </span>
                                            <span>
                                                {{ translate('messages.The app download button link is not set up yet. Please complete the setup from') }}
                                                <a target="_blank" href="{{ route('admin.business-settings.app-settings') }}#user-app-download-section" class="text-danger text-underline font-semibold">{{ translate('messages.App & Web Settings') }}</a>
                                                {{ translate('messages.to enable this button') }}.
                                            </span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    <div class="btn--container justify-content-end mt-4">
                        <button type="reset" class="btn btn--reset">{{translate('Reset')}}</button>
                        <button type="submit"   class="btn btn--primary">{{translate('Save')}}</button>
                    </div>
            </form>
            </div>
        </div>
</div>


<form  id="remove_image_form" action="{{ route('admin.remove_image') }}" method="post">
    @csrf
    <input type="hidden" name="id" value="{{  $react_download_apps_banner_image?->id}}" >
    <input type="hidden" name="model_name" value="DataSetting" >
    <input type="hidden" name="image_path" value="react_download_apps_image" >
    <input type="hidden" name="field_name" value="value" >
</form>
<form  id="remove_image_1_form" action="{{ route('admin.remove_image') }}" method="post">
    @csrf
    <input type="hidden" name="id" value="{{  $react_download_apps_image?->id}}" >
    <input type="hidden" name="model_name" value="DataSetting" >
    <input type="hidden" name="image_path" value="react_download_apps_image" >
    <input type="hidden" name="field_name" value="value" >
</form>

@include('admin-views.landing_page.react.partials.download_apps_guideline')

@endsection
