@extends('layouts.admin.app')

@section('title',translate('messages.app_&_web_settings'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header d-flex flex-wrap align-items-center justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{dynamicAsset('assets/admin/img/app.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{translate('messages.app_&_web_settings')}}
                </span>
            </h1>
            <div class="text--primary-2 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#how-it-works">
                <strong class="mr-2">{{translate('See_how_it_works!')}}</strong>
                  <div class="blinkings">
                    <i class="tio-info text-gray1 fs-16"></i>
                </div>
            </div>
        </div>
        <div class="js-nav-scroller hs-nav-scroller-horizontal mb-3">
            <!-- Nav -->
            <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
                <li class="nav-item">
                    <a class="nav-link active d-none" data-toggle="tab" href="#app-web-setup">{{ translate('messages.App & Web Setup') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-none" data-toggle="tab" href="#app-deep-link-setup">{{ translate('messages.App Deep Link Setup') }}</a>
                </li>
            </ul>
            <!-- End Nav -->
        </div>
        <!-- End Page Header -->

        <div class="tab-content">
            <div class="tab-pane fade show active" id="app-web-setup">

        <div class="card mb-3" hidden>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-sm-6 col-12">
                        <div class="form-group m-0">
                            <form action="{{route('admin.business-settings.toggle-settings',['popular_food',$popular_food?0:1, 'popular_food'])}}" id="popular_food_form" method="get">
                            <label class="toggle-switch toggle-switch-sm d-flex justify-content-between border rounded px-3 px-xl-4 form-control" >
                                <span class="pr-2">{{translate('messages.popular_foods')}}
                                      <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ translate('If_enabled,_Popular_Foods_will_be_available_on_the_User_App') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </span>
                                <input type="checkbox"
                                       data-id="popular_food"
                                       data-type="status"
                                       data-image-on="{{ dynamicAsset('assets/admin/img/modal/veg-on.png') }}"
                                       data-image-off="{{ dynamicAsset('assets/admin/img/modal/veg-off.png') }}"
                                       data-title-on="{{ translate('Want_to_enable_the_Popular_Foods_section!') }}"
                                       data-title-off="{{ translate('Want_to_disable_the_Popular_Foods_option?') }}"
                                       data-text-on="<p>{{ translate('If_enabled,_users_can_see_popular_foods_on_the_user_app.') }}</p>"
                                       data-text-off="<p>{{ translate('If_disabled,_users_can_not_see_popular_foods_on_the_user_app.') }}</p>"
                                       class="status toggle-switch-input dynamic-checkbox"

                                id="popular_food" {{$popular_food?'checked':''}}>
                                <span class="toggle-switch-label">
                                    <span class="toggle-switch-indicator"></span>
                                </span>

                            </label>
                        </form>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6 col-12">
                        <div class="form-group m-0">
                            <form action="{{route('admin.business-settings.toggle-settings',['popular_restaurant',$popular_restaurant?0:1, 'popular_restaurant'])}}" id="popular_restaurant_form" method="get">
                            <label class="toggle-switch toggle-switch-sm d-flex justify-content-between border rounded px-3 px-xl-4 form-control" >
                                <span class="pr-2">{{translate('messages.popular_restaurants')}}
                                      <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ translate('If_enabled,_the_Popular_Restaurants_section_will_be_available_on_the_User_App') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </span>
                                <input type="checkbox" name="popular_restaurant"
                                       data-id="popular_restaurant"
                                       data-type="status"
                                       data-image-on="{{ dynamicAsset('assets/admin/img/modal/store-self-reg-on.png') }}"
                                       data-image-off="{{ dynamicAsset('assets/admin/img/modal/store-self-reg-off.png') }}"
                                       data-title-on="{{ translate('Want_to_enable_the_Popular_Restaurants_section') }}"
                                       data-title-off="{{ translate('Want_to_disable_the_Popular_Restaurants_section?!') }}"
                                       data-text-on="<p>{{ translate('If_enabled,_users_can_see_popular_restaurants_section_on_the_user_app.') }}</p>"
                                       data-text-off="<p>{{ translate('If_disabled,_users_can_not_see_popular_restaurants_section_on_the_user_app.') }}</p>"
                                       class="status toggle-switch-input dynamic-checkbox"

                                       id="popular_restaurant" {{$popular_restaurant?'checked':''}}>
                                <span class="toggle-switch-label">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </form>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6 col-12">
                        <div class="form-group m-0">
                            <form action="{{route('admin.business-settings.toggle-settings',['new_restaurant',$new_restaurant?0:1, 'new_restaurant'])}}" id="new_restaurant_form" method="get">
                            <label class="toggle-switch toggle-switch-sm d-flex justify-content-between border rounded px-3 px-xl-4 form-control" >
                                <span class="pr-2 text-capitalize">{{translate('messages.new_restaurants')}}
                                      <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ translate('If_enabled,_the_New_Restaurants_will_be_available_on_the_User_App.') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </span>
                                <input type="checkbox"
                                 id="new_restaurant"
                                       data-id="new_restaurant"
                                       data-type="status"
                                       data-image-on="{{ dynamicAsset('assets/admin/img/modal/store-self-reg-on.png') }}"
                                       data-image-off="{{ dynamicAsset('assets/admin/img/modal/store-self-reg-off.png') }}"
                                       data-title-on="{{ translate('Want_to_enable_the_New_Restaurants_section') }}"
                                       data-title-off="{{ translate('Want_to_disable_the_New_Restaurants_section!') }}"
                                       data-text-on="<p>{{ translate('If_enabled,_users_can_see_new_restaurants_section_on_the_user_app.') }}</p>"
                                       data-text-off="<p>{{ translate('If_disabled,_users_can_not_see_new_restaurants_section_on_the_user_app.') }}</p>"
                                       class="status toggle-switch-input dynamic-checkbox"

                                    {{$new_restaurant?'checked':''}}>
                                <span class="toggle-switch-label">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </form>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6 col-12">
                        <div class="form-group m-0">
                            <form action="{{route('admin.business-settings.toggle-settings',['most_reviewed_foods',$most_reviewed_foods?0:1, 'most_reviewed_foods'])}}" id="most_reviewed_foods_form" method="get">
                            <label class="toggle-switch toggle-switch-sm d-flex justify-content-between border rounded px-3 px-xl-4 form-control" >
                                <span class="pr-2 text-capitalize">{{translate('messages.most_reviewed_foods')}}
                                      <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ translate('If_enabled,_the_Most_Reviewed_Foods_will_be_available_on_the_User_App.') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </span>
                                <input type="checkbox"   id="most_reviewed_foods"
                                        data-id="most_reviewed_foods"
                                        data-type="status"
                                        data-image-on="{{ dynamicAsset('assets/admin/img/modal/veg-on.png') }}"
                                        data-image-off="{{ dynamicAsset('assets/admin/img/modal/veg-off.png') }}"
                                        data-title-on="{{ translate('Want_to_enable_the_Most_Reviewed_Foods_option?!') }}"
                                        data-title-off="{{ translate('Want_to_disable_the_Most_Reviewed_Foods_option?!') }}"
                                        data-text-on="<p>{{ translate('If_enabled,_users_can_see_the_most_reviewed_foods_on_the_user_app.') }}</p>"
                                        data-text-off="<p>{{ translate('If_disabled,_users_can_not_see_the_most_reviewed_foods_on_the_user_app.') }}</p>"
                                        class="toggle-switch-input dynamic-checkbox"

                                {{$most_reviewed_foods?'checked':''}}>
                                <span class="toggle-switch-label">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-body mb-3" id="user-app-download-section">
            @php($language=\App\Models\BusinessSetting::where('key','language')->first())
            @php($language = $language->value ?? null)
            @php($default_lang = str_replace('_', '-', app()->getLocale()))
            <form action="{{ route('admin.business-settings.user-app-download-update') }}" method="post">
                @csrf
                <div class="d-flex gap-3 align-items-center justify-content-between flex-wrap mb-20">
                    <div class="flex-grow-1">
                        <h4 class="mb-1">{{ translate('messages.Show User App Download Section') }}</h4>
                        <p class="fs-12 mb-0">{{ translate('If you turn On the availability status, this app download section will be shown in the website') }}.</p>
                    </div>
                    <div class="flex-grow-1 max-w-360">
                        <label
                            class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control m-0">
                            <span class="pr-1 d-flex align-items-center switch--label">
                                <span>{{ translate('messages.Status') }}</span>
                            </span>
                            <input type="checkbox"
                                data-id="user-app-download-status"
                                data-type="toggle"
                                data-image-on="{{ dynamicAsset('assets/admin/img/modal/dm-tips-on.png') }}"
                                data-image-off="{{ dynamicAsset('assets/admin/img/modal/dm-tips-off.png') }}"
                                data-title-on="<strong>{{ translate('messages.Want_to_enable_user_app_download_section?') }}</strong>"
                                data-title-off="<strong>{{ translate('messages.Want_to_disable_user_app_download_section?') }}</strong>"
                                data-text-on="<p>{{ translate('messages.If_you_enable_this,_the_user_app_download_section_will_be_shown_in_the_landing_page') }}</p>"
                                data-text-off="<p>{{ translate('messages.If_you_disable_this,_the_user_app_download_section_will_not_be_shown_in_the_landing_page.') }}</p>"
                                class="status toggle-switch-input dynamic-checkbox-toggle"
                                value="1"
                                name="user_app_download_status" id="user-app-download-status" {{ isset($user_app_download_status) && $user_app_download_status == 1 ? 'checked' : '' }}>
                            <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </div>
                </div>
                <div class="bg-light rounded-10 p-12 p-xxl-20">
                    <div class="js-nav-scroller hs-nav-scroller-horizontal">
                        <ul class="nav nav-tabs nav--tabs mb-3 border-0">
                            <li class="nav-item">
                                <a class="nav-link lang_link active" href="#" id="default-link">{{ translate('messages.Default') }}</a>
                            </li>
                            @if ($language)
                                @foreach (json_decode($language) as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link" href="#"
                                            id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                    <div>
                        <div class="lang_form default-form">
                            <label for="user_app_download_title" class="input-label d-flex align-items-center gap-1">
                                {{ translate('messages.Title') }}(Default) <span class="text-danger">*</span>
                                <span class="tio-info text-gray1 fs-16" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('This title will appear in the website app download section.') }}">
                                </span>
                            </label>
                            <textarea type="text" name="user_app_download_title[]" class="form-control" maxlength="60" placeholder="{{ translate('messages.Enter Title') }}" rows="1" required="">{{ $user_app_download_title?->getRawOriginal('value') ?? '' }}</textarea>
                            <div class="d-flex justify-content-end mt-1">
                                <span class="text-body-light fs-12">0/60</span>
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                        </div>
                        
                        @if ($language)
                            <?php
                                $translate = [];
                                if ($user_app_download_title && count($user_app_download_title['translations'])) {
                                    foreach ($user_app_download_title['translations'] as $t) {
                                        if ($t->key == 'user_app_download_title') {
                                            $translate[$t->locale]['user_app_download_title'] = $t->value;
                                        }
                                    }
                                }
                            ?>
                            @foreach (json_decode($language) as $lang)
                                <div class="lang_form d-none" id="{{ $lang }}-form">
                                    <label for="user_app_download_title" class="input-label d-flex align-items-center gap-1">
                                        {{ translate('messages.Title') }}({{ strtoupper($lang) }})
                                        <span class="tio-info text-gray1 fs-16" data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('This title will appear in the website app download section.') }}">
                                        </span>
                                    </label>
                                    <textarea type="text" name="user_app_download_title[]" class="form-control" maxlength="60" placeholder="{{ translate('messages.Enter Title') }}" rows="1">{{ $translate[$lang]['user_app_download_title'] ?? '' }}</textarea>
                                    <div class="d-flex justify-content-end mt-1">
                                        <span class="text-body-light fs-12">0/60</span>
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info bg-opacity-10 mt-2">
                    <span class="text-info lh-1 fs-14 flex-shrink-0">
                        <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                    </span>
                    <span>{{ translate('messages.App download button URL link is setup successfully. Data is synced from') }}</span>
                </div>
                <div class="btn--container justify-content-end mt-3">
                    <button type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                    <button type="{{getEnvMode()!='demo'?'submit':'button'}}" class="btn btn--primary call-demo">{{translate('messages.submit')}}</button>
                </div>
            </form>
        </div>


        <form action="{{route('admin.business-settings.update_app_settings')}}" method="post"
        enctype="multipart/form-data">
        @csrf
            <input type="hidden" name="type" value="user_app" >
            <div class="card mb-20">
                <div class="card-header d-block">
                    <h3 class="mb-1"><span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span> <span>{{ translate('User_App_Version_Control') }}</span></h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">
                                <img src="{{dynamicAsset('assets/admin/img/andriod.png')}}" class="mr-2" alt="">
                                {{ translate('For_android') }}
                            </h5>
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group">
                                    <label  for="app_minimum_version_android" class="form-label">
                                        {{translate('messages.Minimum_User_App_Version_for_Force_Update')}} ({{translate('messages.android')}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ translate('The_minimum_user_app_version_required_for_the_app_functionality') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input type="number" id="app_minimum_version_android" placeholder="{{translate('messages.app_minimum_version')}}" class="form-control h--45px" name="app_minimum_version_android"
                                        step="0.001"   min="0" value="{{getEnvMode()!='demo'?$app_minimum_version_android??'':''}}">
                                </div>
                                <div class="form-group mb-md-0">
                                    <label for="app_url_android" class="form-label">
                                        {{translate('messages.Download_URL_for_User_App')}} ({{translate('messages.android')}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ translate('Users_will_download_the_latest_user_app_version_using_this_URL') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input type="text" id="app_url_android" placeholder="{{translate('messages.Download_Url')}}" class="form-control h--45px js-link-validator" name="app_url_android"
                                        value="{{getEnvMode()!='demo'?$app_url_android??'':''}}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">
                                <img src="{{dynamicAsset('assets/admin/img/apple.png')}}" class="mr-2" alt="">
                                {{ translate('For_iOS') }}
                            </h5>
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group">
                                    <label for="$app_minimum_version_ios"  class="form-label">{{translate('messages.Minimum_User_App_Version_for_Force_Update')}} ({{translate('messages.ios')}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ translate('The_minimum_user_app_version_required_for_the_app_functionality') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input type="number" id="$app_minimum_version_ios" placeholder="{{translate('messages.app_minimum_version')}}"  class="form-control h--45px" name="app_minimum_version_ios"
                                    step="0.001"  min="0" value="{{getEnvMode()!='demo'?$app_minimum_version_ios??'':''}}">
                                </div>
                                <div class="form-group mb-md-0">
                                    <label for="app_url_ios" class="form-label">
                                        {{translate('messages.Download_URL_for_User_App')}} ({{translate('messages.ios')}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ translate('Users_will_download_the_latest_user_app_version_using_this_URL') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input type="text" id="app_url_ios" placeholder="{{translate('messages.Download_Url')}}" class="form-control h--45px js-link-validator" name="app_url_ios"
                                    value="{{getEnvMode()!='demo'?$app_url_ios??'':''}}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-3">
                        <button type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                        <button type="{{getEnvMode()!='demo'?'submit':'button'}}" class="btn btn--primary call-demo">{{translate('messages.submit')}}</button>
                    </div>
                </div>
            </div>
        </form>


        <form action="{{route('admin.business-settings.update_app_settings')}}" method="post"
        enctype="multipart/form-data">
        @csrf
            <input type="hidden" name="type" value="restaurant_app" >
            <div class="card mb-20">
                <div class="card-header d-block">
                    <h3 class="mb-1"><span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span> <span>{{ translate('Restaurant_App_Version_Control') }}</span></h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">
                                <img src="{{dynamicAsset('assets/admin/img/andriod.png')}}" class="mr-2" alt="">
                                {{ translate('For_android') }}
                            </h5>
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group">
                                    <label for="app_minimum_version_android_restaurant"  class="form-label text-capitalize">{{translate('messages.Minimum_Restaurant_App_Version_for_Force_Update')}} ({{translate('messages.android')}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ translate('messages.The_minimum_Restaurant_app_version_required_for_the_app_functionality.') }}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                    </label>
                                    <input type="number" id="app_minimum_version_android_restaurant" placeholder="{{translate('messages.app_minimum_version')}}" class="form-control h--45px" name="app_minimum_version_android_restaurant"
                                        step="0.001"   min="0" value="{{getEnvMode()!='demo'?$app_minimum_version_android_restaurant??'':''}}">
                                </div>
                                <div class="form-group mb-md-0">
                                    <label  for="app_url_android_restaurant" class="form-label text-capitalize">
                                        {{translate('messages.Download_URL_for_Restaurant_App')}} ({{translate('messages.android')}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ translate('Users_will_download_the_latest_Restaurant_app_using_this_URL') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input type="text" id="app_url_android_restaurant" placeholder="{{translate('messages.Download_Url')}}" class="form-control h--45px js-link-validator" name="app_url_android_restaurant"
                                        value="{{getEnvMode()!='demo'?$app_url_android_restaurant??'':''}}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">
                                <img src="{{dynamicAsset('assets/admin/img/apple.png')}}" class="mr-2" alt="">
                                {{ translate('For_iOS') }}
                            </h5>
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group">
                                    <label for="app_minimum_version_ios_restaurant"  class="form-label text-capitalize">{{translate('messages.Minimum_Restaurant_App_Version_for_Force_Update')}} ({{translate('messages.ios')}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ translate('messages.The_minimum_Restaurant_app_version_required_for_the_app_functionality.') }}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                    </label>
                                    <input type="number" id="app_minimum_version_ios_restaurant" placeholder="{{translate('messages.app_minimum_version')}}" class="form-control h--45px" name="app_minimum_version_ios_restaurant"
                                    step="0.001"  min="0" value="{{getEnvMode()!='demo'?$app_minimum_version_ios_restaurant??'':''}}">
                                </div>
                                <div class="form-group mb-md-0">
                                    <label for="app_url_ios_restaurant" class="form-label text-capitalize">
                                        {{translate('messages.Download_URL_for_Restaurant_App')}} ({{translate('messages.ios')}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ translate('Users_will_download_the_latest_Restaurant_app_version_using_this_URL') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input type="text" id="app_url_ios_restaurant" placeholder="{{translate('messages.Download_Url')}}" class="form-control h--45px js-link-validator" name="app_url_ios_restaurant"
                                    value="{{getEnvMode()!='demo'?$app_url_ios_restaurant??'':''}}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-3">
                        <button type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                        <button type="{{getEnvMode()!='demo'?'submit':'button'}}"  class="btn btn--primary call-demo">{{translate('messages.submit')}}</button>
                    </div>
                </div>
            </div>
        </form>


        <form action="{{route('admin.business-settings.update_app_settings')}}" method="post"
        enctype="multipart/form-data">
        @csrf
            <input type="hidden" name="type" value="delivery_app" >

            <div class="card mb-20">
                <div class="card-header d-block">
                    <h3 class="mb-1"><span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span> <span>{{ translate('Deliveryman_App_Version_Control') }}</span></h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">
                                <img src="{{dynamicAsset('assets/admin/img/andriod.png')}}" class="mr-2" alt="">
                                {{ translate('For_android') }}
                            </h5>
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group">
                                    <label for="app_minimum_version_android_deliveryman"  class="form-label text-capitalize">{{translate('messages.Minimum_Deliveryman_App_Version_for_Force_Update')}} ({{translate('messages.android')}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ translate('messages.The_minimum_Deliveryman_app_version_required_for_the_app_functionality') }}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                    </label>
                                    <input type="number" id="app_minimum_version_android_deliveryman" placeholder="{{translate('messages.app_minimum_version')}}" class="form-control h--45px" name="app_minimum_version_android_deliveryman"
                                        step="0.001"   min="0" value="{{getEnvMode()!='demo'?$app_minimum_version_android_deliveryman??'':''}}">
                                </div>
                                <div class="form-group mb-md-0">
                                    <label for="app_url_android_deliveryman" class="form-label text-capitalize">
                                        {{translate('messages.Download_URL_for_Deliveryman_App')}} ({{translate('messages.android')}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ translate('Users_will_download_the_latest_Deliveryman_app_using_this_URL') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input type="text" id="app_url_android_deliveryman" placeholder="{{translate('messages.Download_Url')}}" class="form-control h--45px js-link-validator" name="app_url_android_deliveryman"
                                    value="{{getEnvMode()!='demo'?$app_url_android_deliveryman??'':''}}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">
                                <img src="{{dynamicAsset('assets/admin/img/apple.png')}}" class="mr-2" alt="">
                                {{ translate('For_iOS') }}
                            </h5>
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group">
                                    <label for="app_minimum_version_ios_deliveryman"  class="form-label text-capitalize">{{translate('messages.Minimum_Deliveryman_App_Version_for_Force_Update')}} ({{translate('messages.ios')}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ translate('messages.The_minimum_Deliveryman_app_version_required_for_the_app_functionality') }}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                    </label>
                                    <input type="number" id="app_minimum_version_ios_deliveryman" placeholder="{{translate('messages.app_minimum_version')}}" class="form-control h--45px" name="app_minimum_version_ios_deliveryman"
                                    step="0.001"  min="0" value="{{getEnvMode()!='demo'?$app_minimum_version_ios_deliveryman??'':''}}">
                                </div>
                                <div class="form-group mb-md-0">
                                    <label for="app_url_ios_deliveryman" class="form-label text-capitalize">
                                        {{translate('messages.Download_URL_for_Deliveryman_App')}} ({{translate('messages.ios')}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ translate('Users_will_download_the_latest_Deliveryman_app_version_using_this_URL') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input type="text" id="app_url_ios_deliveryman" placeholder="{{translate('messages.Download_Url')}}" class="form-control h--45px js-link-validator" name="app_url_ios_deliveryman"
                                    value="{{getEnvMode()!='demo'?$app_url_ios_deliveryman??'':''}}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-3">
                        <button type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                        <button type="{{getEnvMode()!='demo'?'submit':'button'}}"  class="btn btn--primary call-demo">{{translate('messages.submit')}}</button>
                    </div>
                </div>
            </div>
        </form>
            </div>
            
            <div class="tab-pane fade" id="app-deep-link-setup">

        <div class="bg-info bg-opacity-10 d-flex fs-12 gap-2 px-3 py-2 rounded text-dark mb-20">
            <span class="text-info lh-1 fs-14 flex-shrink-0">
                <img src="{{ dynamicAsset('assets/admin/img/svg/bulb.svg') }}" class="svg"
                    alt="">
            </span>
            <span>
                {{ translate('messages.at_least_one_login_method_must_remain_active_for_the_customer._otherwise_they_will_be_unable_to_log_in_to_the_system') }}
            </span>
        </div>
        <form action="">
            <div class="card mb-20">
                <div class="card-header d-block">
                    <h3 class="mb-1">{{ translate('messages.User_App') }}</h3>
                    <p class="fs-12 mb-0">{{ translate('messages.Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam odio tellus, laoreet') }}</p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">
                                <img src="{{dynamicAsset('assets/admin/img/andriod.png')}}" class="mr-2" alt="">
                                {{ translate('For_android') }}
                            </h5>
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group">
                                    <label for=""  class="form-label text-capitalize">
                                        {{translate('messages.android_package_name_for')}} ({{translate('messages.android')}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                    </label>
                                    <input type="number" id="" placeholder="{{translate('messages.app_minimum_version')}}" class="form-control h--45px" name=""
                                        step="0.001"   min="0" value="">
                                </div>
                                <div class="form-group">
                                    <label for=""  class="form-label text-capitalize">
                                        {{translate('messages.android_sha256_fingerprint_for_user_app')}} ({{translate('messages.android')}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                    </label>
                                    <input type="text" id="" placeholder="{{translate('messages.Download_Url')}}" class="form-control h--45px js-link-validator" name="" value="">
                                </div>
                                <div class="form-group">
                                    <label for=""  class="form-label text-capitalize">
                                        {{translate('messages.play_store_redirect_url')}} ({{translate('messages.android')}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                    </label>
                                    <input type="text" id="" placeholder="{{translate('messages.Download_Url')}}" class="form-control h--45px js-link-validator" name="" value="">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">
                                <img src="{{dynamicAsset('assets/admin/img/apple.png')}}" class="mr-2" alt="">
                                {{ translate('For_iOS') }}
                            </h5>
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group">
                                    <label for=""  class="form-label text-capitalize">
                                        {{translate('messages.IOS_bundle_id_for')}} ({{translate('messages.iOS')}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                    </label>
                                    <input type="number" id="" placeholder="{{translate('messages.app_minimum_version')}}" class="form-control h--45px" name=""
                                        step="0.001"   min="0" value="">
                                </div>
                                <div class="form-group">
                                    <label for=""  class="form-label text-capitalize">
                                        {{translate('messages.IOS_team_id_for')}} ({{translate('messages.iOS')}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                    </label>
                                    <input type="text" id="" placeholder="{{translate('messages.Download_Url')}}" class="form-control h--45px" name="" value="">
                                </div>
                                <div class="form-group">
                                    <label for=""  class="form-label text-capitalize">
                                        {{translate('messages.app_store_redirect_url')}} ({{translate('messages.iOS')}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                    </label>
                                    <input type="text" id="" placeholder="{{translate('messages.Download_Url')}}" class="form-control h--45px" name="" value="">
                                </div>
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
        </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="how-it-works">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="single-item-slider owl-carousel">
                        <div class="item">
                            <div class="mb-20">
                                <div class="text-center">
                                    <img src="{{dynamicAsset('assets/admin/img/app.png')}}" alt="" class="mb-20">
                                    <h5 class="modal-title">{{translate('What is App Version ?')}}</h5>
                                </div>
                                <ul>
                                    <li>
                                        {{ translate('This_app_version_defines_the_Restaurant,_Deliveryman,_and_User_app_version_of_StackFood.') }}
                                    </li>
                                    <li>
                                        {{ translate('It_doesn’t_represent_the_Play_Store_or_App_Store_version') }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="item">
                            <div class="mb-20">
                                <div class="text-center">
                                    <img src="{{dynamicAsset('assets/admin/img/app.png')}}" alt="" class="mb-20">
                                    <h5 class="modal-title">{{translate('App Download Link')}}</h5>
                                </div>
                                <ul>
                                    <li>
                                        {{ translate('The_app_download_link_is_the_URL_from_which_users_can_update_the_app_by_clicking_the_Update_App_button_from_their_app') }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center">
                        <div class="slide-counter"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@push('script_2')
    <script>
        $(".lang_link").click(function (e) {
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang_form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);
            console.log(lang);
            $("#" + lang + "-form").removeClass('d-none');
            if (lang === 'default') {
                $(".default-form").removeClass('d-none');
            }
        })
    </script>
@endpush

@endsection
