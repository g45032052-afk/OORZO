@php use App\CentralLogics\Helpers; @endphp
@extends('layouts.admin.app')

@section('title', translate('messages.landing_page_settings'))

@section('content')

    <div class="content container-fluid">
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-start">
                <h1 class="page-header-title text-capitalize fs-24">
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
                    <div class="py-2 px-3 rounded d-flex justify-content-between border align-items-center w-300">
                        <h5 class="text-capitalize fw-normal mb-0">{{translate('Status') }}</h5>
                        <form
                            action="{{ route('admin.landing_page.statusUpdate', ['type' => 'react_landing_page', 'key' => 'react_registration_section_status']) }}"
                            method="get" id="RegistrationCheckboxStatus_form">
                        </form>
                        <label class="toggle-switch toggle-switch-sm" for="RegistrationCheckboxStatus">
                            <input type="checkbox" data-id="RegistrationCheckboxStatus" data-type="status"
                                   data-image-on="{{ dynamicAsset('assets/admin/img/status-ons.png') }}"
                                   data-image-off="{{ dynamicAsset('assets/admin/img/off-danger.png') }}"
                                   data-title-on="{{ translate('Do you want turn on this section ?') }}"
                                   data-title-off="{{ translate('Do you want to turn off this section ?') }}"
                                   data-text-on="<p>{{ translate('If you turn on this section will be show in react landing page.') }}"
                                   data-text-off="<p>{{ translate('If you turn off this section will not be show in react landing page.') }}</p>"
                                   class="toggle-switch-input  status dynamic-checkbox" id="RegistrationCheckboxStatus"
                                {{ $react_registration_section_status?->value ? 'checked' : '' }}>
                            <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>


        <div class="card mb-15">
            <div class="card-header">
                <div class="">
                    <h3 class="mb-1">{{ translate('Earn Money Section') }}</h3>
                    <p class="mb-0 gray-dark fs-12">{{ translate('Manage the main headline and sub-headline for the partner recruitment section.') }}</p>
                </div>
            </div>
            @php($default_lang = str_replace('_', '-', app()->getLocale()))
            <div class="card-body">
                <form class="validate-form" action="{{ route('admin.react_landing_page.earn_money_section') }}" method="POST">
                    @csrf
                    <input type="hidden" name="page_type" value="react_landing_page">
                    <div class="card-custom-xl">
                        <div class="bg-light2 rounded p-xxl-4 p-3">
                            <div class="">
                                @if($language)
                                    <ul class="nav nav-tabs mb-4 border-">
                                        <li class="nav-item">
                                            <a class="nav-link lang_link1 active"
                                               href="#"
                                               id="default-link1">{{translate('messages.default')}}</a>
                                        </li>
                                        @foreach (json_decode($language) as $lang)
                                            <li class="nav-item">
                                                <a class="nav-link lang_link1"
                                                   href="#"
                                                   id="{{ $lang }}-link1">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                <div class="card-body p-0">
                                    <div class="row g-3 lang_form-float default-form-float">
                                        <input type="hidden" name="lang[]" value="default">

                                        <div class="col-sm-12">
                                            <div class="form-group mb-0">
                                                <label for="title" class="form-label fw-400">
                                                    {{ translate('messages.Title') }} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                    <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                          data-original-title="{{ translate('This is the main headline for the partner recruitment section.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                </label>
                                                <input required type="text"
                                                       data-maxlength="50"
                                                       id="title" class="form-control" name="react_earn_money_section_title[]"
                                                       value="{{ $react_earn_money_section_title?->getRawOriginal('value') ?? null }}"
                                                       placeholder="{{ translate('messages.Enter_Title') }}">
                                                <span class="text-body-light text-right d-block mt-1">0/50</span>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 mt-2">
                                            <div class="form-group mb-0">
                                                <label for="description" class="form-label fw-400">
                                                    {{translate('messages.Description')}} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                    <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                          data-original-title="{{ translate('Write a brief overview explaining the two partnership paths. Keep it under 100 characters.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                </label>
                                                <textarea required id="description" data-maxlength="100" class="form-control min-h-90px ckeditor" name="react_earn_money_section_description[]" placeholder="{{translate('messages.Description')}}">{{ $react_earn_money_section_description?->getRawOriginal('value') ?? null }}</textarea>
                                                <span class="text-body-light text-right d-block mt-1">0/100</span>
                                            </div>
                                        </div>
                                    </div>

                                    @forelse(json_decode($language) as $lang)
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">

                                            <?php
                                            $description_translate = [];
                                            $title_translate = [];
                                            if(isset($react_earn_money_section_title->translations) && count($react_earn_money_section_title->translations)){
                                                foreach($react_earn_money_section_title->translations as $t){
                                                    if($t->locale == $lang && $t->key=='react_earn_money_section_title'){
                                                        $title_translate[$lang]['value'] = $t->value;
                                                    }
                                                }
                                            }

                                            if(isset($react_earn_money_section_description->translations) && count($react_earn_money_section_description->translations)){
                                                foreach($react_earn_money_section_description->translations as $t){
                                                    if($t->locale == $lang && $t->key=='react_earn_money_section_description'){
                                                        $description_translate[$lang]['value'] = $t->value;
                                                    }
                                                }
                                            }
                                            ?>

                                        <div class="row g-3 d-none lang_form-float" id="{{ $lang }}-form-float">
                                            <div class="col-sm-12">
                                                <div class="form-group mb-0">
                                                    <label for="title{{$lang}}" class="form-label fw-400">
                                                        {{ translate('messages.Title') }} ({{ strtoupper($lang) }})
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                              data-original-title="{{ translate('This is the main headline for the partner recruitment section.') }}">
                                                                <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                    </label>
                                                    <input type="text"
                                                           data-maxlength="50"
                                                           id="title{{$lang}}" class="form-control"
                                                           name="react_earn_money_section_title[]" value="{{ $title_translate[$lang]['value'] ?? '' }}"
                                                           placeholder="{{ translate('messages.Enter_Title') }}">
                                                    <span class="text-body-light text-right d-block mt-1">0/50</span>
                                                </div>
                                            </div>

                                            <div class="col-sm-12 mt-2">
                                                <div class="form-group mb-0">
                                                    <label for="description{{$lang}}" class="form-label fw-400">
                                                        {{translate('messages.Description')}} ({{ strtoupper($lang) }})
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                              data-original-title="{{ translate('Write a brief overview explaining the two partnership paths. Keep it under 100 characters.') }}">
                                                                <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                    </label>
                                                    <textarea id="description{{$lang}}" data-maxlength="100" class="form-control min-h-90px ckeditor" name="react_earn_money_section_description[]" placeholder="{{translate('messages.Description')}}">{{ $description_translate[$lang]['value'] ?? '' }}</textarea>
                                                    <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-4">
                        <button type="reset" class="btn btn--reset">{{ translate('Reset') }}</button>
                        <button type="submit"
                            class="btn btn--primary">{{ translate('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-15">
            <div class="card-header">
                <div class="">
                    <h3 class="mb-1">{{ translate('Restaurant Section') }}</h3>
                    <p class="mb-0 gray-dark fs-12">{{ translate('Configure the content for the section encouraging new restaurants to join your platform.') }}</p>
                </div>
            </div>
            <div class="card-body">
                <form class="validate-form" action="{{ route('admin.react_landing_page.settings', 'react-regisrtation-section-content') }}"
                    enctype="multipart/form-data" method="post">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card-custom-xl h-100">
                                <div class="d-flex gap-2 justify-content-between flex-md-nowrap flex-wrap mb-20">
                                    <div>
                                        <h4 class="mb-1">{{ translate('Restaurant Registration Button ') }}</h4>
                                        <p class="mb-0 fs-12 text-gray1">{{ translate('Set your button name here, and the link will be handled dynamically.') }}</p>
                                    </div>
                                    <label class="toggle-switch toggle-switch-sm m-0">
                                        <input type="checkbox" class="status toggle-switch-input" value="1"  name="react_restaurant_registration_button_status" {{$react_restaurant_registration_button_status['value']??0 == 1 ? 'checked' : ''}} >
                                        <span class="toggle-switch-label text mb-0">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                                <div class="bg-light2 rounded p-xxl-4 p-3">
                                    <div>
                                        @if($language)
                                            <ul class="nav nav-tabs mb-4 border-">
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link2 active"
                                                       href="#"
                                                       id="default-link2">{{translate('messages.default')}}</a>
                                                </li>
                                                @foreach (json_decode($language) as $lang)
                                                    <li class="nav-item">
                                                        <a class="nav-link lang_link2"
                                                           href="#"
                                                           id="{{ $lang }}-link2">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif

                                        <div class="card-body p-0">
                                            <div class="row g-3 lang_form-float2 default-form-float2">
                                                <input type="hidden" name="lang[]" value="default">

                                                <div class="col-sm-12">
                                                    <div class="form-group mb-0">
                                                        <label for="title2" class="form-label fw-400">
                                                            {{ translate('messages.Title') }} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                                  data-original-title="{{ translate('The headline specifically for restaurant partners. Keep it under 50 characters.') }}">
                                                                <i class="tio-info text-gray1 fs-16"></i>
                                                            </span>
                                                        </label>
                                                        <input required type="text" data-maxlength="50" id="title2" class="form-control"
                                                               name="react_restaurant_section_title[]"
                                                               value="{{ $react_restaurant_section_title?->getRawOriginal('value') ?? null }}"
                                                               placeholder="{{ translate('messages.Write_the_title_within_50_characters') }}">
                                                        <span class="text-body-light text-right d-block mt-1">0/50</span>
                                                    </div>
                                                </div>

                                                <div class="col-sm-12 mt-2">
                                                    <div class="form-group mb-0">
                                                        <label for="description2" class="form-label fw-400">
                                                            {{translate('messages.Subtitle')}} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                                  data-original-title="{{ translate('Write the key benefit for restaurants. Keep it under 100 characters.') }}">
                                                                <i class="tio-info text-gray1 fs-16"></i>
                                                            </span>
                                                        </label>
                                                        <input required type="text" data-maxlength="100" id="description2" class="form-control"
                                                               name="react_restaurant_section_sub_title[]"
                                                               value="{{ $react_restaurant_section_sub_title?->getRawOriginal('value') ?? null }}"
                                                               placeholder="{{ translate('Write_the_sub_title') }}">
                                                        <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                    </div>
                                                </div>

                                                <div class="col-sm-12 mt-2">
                                                    <div class="form-group mb-0">
                                                        <label for="description2" class="form-label fw-400">
                                                            {{translate('messages.Button Name')}} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                                  data-original-title="{{ translate('Enter a proper button name that helps users make a decision.') }}">
                                                                <i class="tio-info text-gray1 fs-16"></i>
                                                            </span>
                                                        </label>
                                                        <input required type="text"
{{--                                                               data-maxlength="20" --}}
                                                               id="title2" class="form-control"
                                                               name="react_restaurant_section_button_name[]"
                                                               value="{{ $react_restaurant_section_button_name?->getRawOriginal('value') ?? null }}"
                                                               placeholder="{{ translate('Write_the_button_name') }}">
{{--                                                        <span class="text-body-light text-right d-block mt-1">0/20</span>--}}
                                                    </div>
                                                </div>
                                            </div>

                                            @foreach(json_decode($language) as $lang)
                                                    <?php
                                                    $description_translate2 = [];
                                                    $title_translate2 = [];
                                                    $button_translate2 = [];
                                                    if(isset($react_restaurant_section_title->translations) && count($react_restaurant_section_title->translations)){
                                                        foreach($react_restaurant_section_title->translations as $t){
                                                            if($t->locale == $lang && $t->key=='react_restaurant_section_title'){
                                                                $title_translate2[$lang]['value'] = $t->value;
                                                            }
                                                        }
                                                    }

                                                    if(isset($react_restaurant_section_sub_title->translations) && count($react_restaurant_section_sub_title->translations)){
                                                        foreach($react_restaurant_section_sub_title->translations as $t){
                                                            if($t->locale == $lang && $t->key=='react_restaurant_section_sub_title'){
                                                                $description_translate2[$lang]['value'] = $t->value;
                                                            }
                                                        }
                                                    }

                                                    if(isset($react_restaurant_section_button_name->translations) && count($react_restaurant_section_button_name->translations)){
                                                        foreach($react_restaurant_section_button_name->translations as $t){
                                                            if($t->locale == $lang && $t->key=='react_restaurant_section_button_name'){
                                                                $button_translate2[$lang]['value'] = $t->value;
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">

                                                <div class="row g-3 d-none lang_form-float2" id="{{ $lang }}-form-float2">
                                                    <div class="col-sm-12">
                                                        <div class="form-group mb-0">
                                                            <label for="title2{{$lang}}" class="form-label fw-400">
                                                                {{ translate('messages.Title') }} ({{ strtoupper($lang) }})
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                                      data-original-title="{{ translate('The headline specifically for restaurant partners. Keep it under 50 characters.') }}">
                                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                                </span>
                                                            </label>
                                                            <input type="text" data-maxlength="50" id="title2{{$lang}}" class="form-control"
                                                                   name="react_restaurant_section_title[]" value="{{ $title_translate2[$lang]['value'] ?? '' }}"
                                                                   placeholder="{{ translate('messages.Enter_Title') }}">
                                                            <span class="text-body-light text-right d-block mt-1">0/50</span>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-12 mt-2">
                                                        <div class="form-group mb-0">
                                                            <label for="description2{{$lang}}" class="form-label fw-400">
                                                                {{translate('messages.Subtitle')}} ({{ strtoupper($lang) }})
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                                      data-original-title="{{ translate('Write the key benefit for restaurants. Keep it under 100 characters.') }}">
                                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                                </span>
                                                            </label>
                                                            <input id="description2{{$lang}}" data-maxlength="100" class="form-control min-h-90px ckeditor"
                                                                      name="react_restaurant_section_sub_title[]" placeholder="{{translate('messages.Subtitle')}}" value="{{ $description_translate2[$lang]['value'] ?? '' }}">
                                                            <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-12">
                                                        <div class="form-group mb-0">
                                                            <label for="button2{{$lang}}" class="form-label fw-400">
                                                                {{ translate('messages.Button Name') }} ({{ strtoupper($lang) }})
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                                      data-original-title="{{ translate('Enter a proper button name that helps users make a decision.') }}">
                                                                <i class="tio-info text-gray1 fs-16"></i>
                                                            </span>
                                                            </label>
                                                            <input type="text"
{{--                                                                   data-maxlength="20"--}}
                                                                   id="button2{{$lang}}" class="form-control"
                                                                   name="react_restaurant_section_button_name[]"
                                                                   value="{{ $button_translate2[$lang]['value'] ?? '' }}"
                                                                   placeholder="{{ translate('messages.Write_the_button_name') }}">
{{--                                                            <span class="text-body-light text-right d-block mt-1">0/20</span>--}}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="p-xxl-20 d-flex align-items-center justify-content-center p-12 global-bg-box text-center rounded h-100 mt-5">
                                            <div class="">
                                                <div class="mb-xxl-5 mb-xl-4 mb-3 text-start">
                                                    <h5 class="mb-1">{{ translate('Section Background Image') }} <span class="text-danger">*</span></h5>
                                                    <p class="mb-0 fs-12 gray-dark">
                                                        {{ translate('Upload your section background image.') }}</p>
                                                </div>
                                                <div class="text-center">
                                                @include('admin-views.partials._image-uploader', [
                                                'id' => 'image-input',
                                                'name' => 'react_restaurant_section_image',
                                                'ratio' => '3:1',
                                                'isRequired' => $react_restaurant_section_image?->value ? false : true,
                                                'existingImage' => $react_restaurant_section_image?->value ? Helpers::get_full_url('react_restaurant_section_image', $react_restaurant_section_image?->value, $react_restaurant_section_image?->storage[0]?->value ?? 'public', 'upload_1_1') : null,
                                                'imageExtension' => IMAGE_EXTENSION,
                                                'imageFormat' => IMAGE_FORMAT,
                                                'maxSize' => MAX_FILE_SIZE,
                                                'pixel'=>'1260 x 360'
                                            ])
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-custom-xl h-100">
                                <div class="d-flex gap-2 justify-content-between flex-md-nowrap flex-wrap mb-20">
                                    <div>
                                        <h4 class="mb-1">{{ translate('Restaurant App Download') }}</h4>
                                        <p class="mb-0 fs-12 text-gray1">{{ translate('Manage the promotional text and links for the deliverymen application.') }}</p>
                                    </div>
                                    <label class="toggle-switch toggle-switch-sm m-0">
                                        <input type="checkbox" class="status toggle-switch-input" name="react_restaurant_app_download_status" value="1" {{ $react_restaurant_app_download_status['value'] ?? 0 == 1 ? 'checked' : '' }}>
                                        <span class="toggle-switch-label text mb-0">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>

                                <div class="bg-light2 rounded p-xxl-4 p-3">
                                    @if ($language)
                                        <ul class="nav nav-tabs mb-4 border-bottom">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link3 active" href="#" id="default-link3">{{ translate('messages.default') }}</a>
                                            </li>
                                            @foreach (json_decode($language) as $lang)
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link3" href="#" id="{{ $lang }}-link3">
                                                        {{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                        <!-- Default Language -->
                                    <div class="lang_form-float3" id="default-form3">
                                        <div class="form-group mb-2">
                                            <label for="title_download_default" class="form-label fw-400">
                                                {{ translate('Title') }} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                      data-original-title="{{ translate('The headline for the restaurant partner app. Keep it under 50 characters.') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                            </label>
                                            <textarea required id="title_download_default" data-maxlength="50" rows="1" name="react_restaurant_app_download_title[]" class="form-control"
                                                      placeholder="{{ translate('Ex: Download Now') }}">{{ $react_restaurant_app_download_title?->getRawOriginal('value') }}</textarea>
                                            <div class="d-flex justify-content-end">
                                                <span class="text-body-light text-right d-block mt-1">0/50</span>
                                            </div>
                                        </div>

                                        <div class="form-group mb-0">
                                            <label for="subtitle_download_default" class="form-label fw-400">
                                                {{ translate('Subtitle') }} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                      data-original-title="{{ translate('A short line describing the app\'s purpose for restaurant owners. Keep it under 100 characters.') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                            </label>
                                            <textarea required id="subtitle_download_default" data-maxlength="100" rows="1" name="react_restaurant_app_download_sub_title[]" class="form-control"
                                                      placeholder="{{ translate('Ex: Scan to Download') }}">{{ $react_restaurant_app_download_sub_title?->getRawOriginal('value') }}</textarea>
                                            <div class="d-flex justify-content-end">
                                                <span class="text-body-light text-right d-block mt-1">0/100</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Dynamic Language Tabs -->
                                    @if ($language)
                                        @foreach (json_decode($language) as $lang)
                                            <?php
                                            $description_translate3 = [];
                                            $title_translate3 = [];
                                            if(isset($react_restaurant_app_download_title->translations) && count($react_restaurant_app_download_title->translations)){
                                                foreach($react_restaurant_app_download_title->translations as $t){
                                                    if($t->locale == $lang && $t->key=='react_restaurant_app_download_title'){
                                                        $title_translate3[$lang]['value'] = $t->value;
                                                    }
                                                }
                                            }

                                            if(isset($react_restaurant_app_download_sub_title->translations) && count($react_restaurant_app_download_sub_title->translations)){
                                                foreach($react_restaurant_app_download_sub_title->translations as $t){
                                                    if($t->locale == $lang && $t->key=='react_restaurant_app_download_sub_title'){
                                                        $description_translate3[$lang]['value'] = $t->value;
                                                    }
                                                }
                                            }
                                            ?>
                                            <div class="d-none lang_form-float3" id="{{ $lang }}-form3">
                                                <div class="form-group mb-2">
                                                    <label class="form-label fw-400">
                                                        {{ translate('Title') }} ({{ strtoupper($lang) }})
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                              data-original-title="{{ translate('The headline for the restaurant partner app. Keep it under 50 characters.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                    </label>
                                                    <textarea data-maxlength="50" rows="1" name="react_restaurant_app_download_title[]" class="form-control"
                                                              placeholder="{{ translate('Ex: Download Now') }}">{{ $title_translate3[$lang]['value'] ?? '' }}</textarea>
                                                    <div class="d-flex justify-content-end">
                                                        <span class="text-body-light text-right d-block mt-1">0/50</span>
                                                    </div>
                                                </div>

                                                <div class="form-group mb-0">
                                                    <label class="form-label fw-400">
                                                        {{ translate('Subtitle') }} ({{ strtoupper($lang) }})
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                              data-original-title="{{ translate('A short line describing the app\'s purpose for restaurant owners. Keep it under 100 characters.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                    </label>
                                                    <textarea data-maxlength="100" rows="1" name="react_restaurant_app_download_sub_title[]" class="form-control"
                                                              placeholder="{{ translate('Ex: Scan to Download') }}">{{ $description_translate3[$lang]['value'] ?? '' }}</textarea>
                                                    <div class="d-flex justify-content-end">
                                                        <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                <div class="bg-light2 rounded p-xxl-4 p-3 mt-4">
                                    <div class="form-group mb-md-0 mt-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label text-capitalize fw-400 m-0">
                                                {{ translate('Download Link For Android') }} <span class="text-danger">*</span>
                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                      data-original-title="{{ translate('Enter the direct link to the restaurant partner app on the Google Play Store.') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                            </label>
                                        </div>
                                        <input required type="url"
                                               placeholder="{{ translate('Ex:_https://www.play.google.com/') }}"
                                               class="form-control h--45px"
                                               name="react_restaurant_app_download_link"
                                               value="{{ $react_restaurant_app_download_link['value'] ?? '' }}" >
                                    </div>
                                    <div class="form-group mb-md-0 mt-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label text-capitalize fw-400 m-0">
                                                {{ translate('Download Link For IOS') }} <span class="text-danger">*</span>
                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                      data-original-title="{{ translate('Enter the direct link to the restaurant partner app on the APP Store.') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                            </label>
                                        </div>
                                        <input required type="url"
                                               placeholder="{{ translate('Ex:_https://www.apple.com/app-store/') }}"
                                               class="form-control h--45px"
                                               name="react_restaurant_app_download_link_for_ios"
                                               value="{{ $react_restaurant_app_download_link_for_ios['value'] ?? '' }}" >
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="btn--container justify-content-end mt-4">
                        <button type="reset" class="btn btn--reset">{{ translate('Reset') }}</button>
                        <button type="submit"
                            class="btn btn--primary">{{ translate('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-15">
            <div class="card-header">
                <div class="">
                    <h3 class="mb-1">{{ translate('Deliveryman Section') }}</h3>
                    <p class="mb-0 gray-dark fs-12">{{ translate('Configure the content for the section encouraging new deliverymen to sign up.') }}</p>
                </div>
            </div>

            <div class="card-body">
                <form class="validate-form" action="{{ route('admin.react_landing_page.settings', 'react-deliveryman-section-content') }}"
                      enctype="multipart/form-data" method="post">
                    @csrf
                    <input type="hidden" name="lang[]" value="default">

                    <div class="row g-4">
                        <!-- Deliveryman Registration Button -->
                        <div class="col-md-6">
                            <div class="card-custom-xl h-100">
                                <div class="d-flex gap-2 justify-content-between flex-md-nowrap flex-wrap mb-20">
                                    <div>
                                        <h4 class="mb-1">{{ translate('Deliveryman Registration Button') }}</h4>
                                        <p class="mb-0 fs-12 text-gray1">{{ translate('Set your button name here, and the link will be handled dynamically.') }}</p>
                                    </div>
                                    <label class="toggle-switch toggle-switch-sm m-0">
                                        <input type="checkbox" class="status toggle-switch-input"
                                               name="react_delivery_registration_button_status"
                                               value="1" {{ ($react_delivery_registration_button_status['value'] ?? 0) == 1 ? 'checked' : '' }}>
                                        <span class="toggle-switch-label text mb-0">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                                    </label>
                                </div>

                                <div class="bg-light2 rounded p-xxl-4 p-3">
                                    @if ($language)
                                        <ul class="nav nav-tabs mb-4 border-bottom">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link5 active" href="#" id="default-link5">{{ translate('messages.default') }}</a>
                                            </li>
                                            @foreach (json_decode($language) as $lang)
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link5" href="#" id="{{ $lang }}-link5">
                                                        {{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    <!-- Default Language -->
                                        <div class="lang_form-float5" id="default-form5">
                                        <div class="form-group mb-2">
                                            <label for="deliveryman_title_default" class="form-label fw-400">{{ translate('Title') }} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                      data-original-title="{{ translate('The headline specifically for recruiting delivery drivers.') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                            </label>
                                            <input required type="text" class="form-control"
                                                   data-maxlength="50"
                                                   name="react_delivery_section_title[]"
                                                   value="{{ $react_delivery_section_title?->getRawOriginal('value') ?? '' }}"
                                                   placeholder="{{ translate('Enter Title...') }}">
                                            <div class="d-flex justify-content-end">
                                                <span class="text-body-light text-right d-block mt-1">0/50</span>
                                            </div>
                                        </div>

                                        <div class="form-group mb-2">
                                            <label for="deliveryman_subtitle_default" class="form-label fw-400">{{ translate('Subtitle') }} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                      data-original-title="{{ translate('The key benefit or offer for delivery drivers.') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                            </label>
                                            <input required type="text" class="form-control" data-maxlength="100"
                                                   name="react_delivery_section_sub_title[]"
                                                   value="{{ $react_delivery_section_sub_title?->getRawOriginal('value') ?? '' }}"
                                                   placeholder="{{ translate('Enter Subtitle...') }}">
                                            <div class="d-flex justify-content-end">
                                                <span class="text-body-light text-right d-block mt-1">0/100</span>
                                            </div>
                                        </div>

                                        <div class="form-group mb-0">
                                            <label for="deliveryman_button_default" class="form-label fw-400">{{ translate('Button Name') }} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                      data-original-title="{{ translate('Enter a proper button name that helps users make a decision.') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                            </label>
                                            <input required type="text" class="form-control"
{{--                                                   data-maxlength="15"--}}
                                                   name="react_delivery_section_button_name[]"
                                                   value="{{ $react_delivery_section_button_name?->getRawOriginal('value') ?? '' }}"
                                                   placeholder="{{ translate('Ex: Register Now') }}">
{{--                                            <div class="d-flex justify-content-end">--}}
{{--                                                <span class="text-body-light text-right d-block mt-1">0/10</span>--}}
{{--                                            </div>--}}
                                        </div>
                                    </div>

                                    <!-- Dynamic Languages -->
                                    @if ($language)
                                        @foreach (json_decode($language) as $lang)
                                            <?php
                                                $title_translate = $react_delivery_section_title->translations->firstWhere('locale', $lang)?->value ?? '';
                                                $subtitle_translate = $react_delivery_section_sub_title->translations->firstWhere('locale', $lang)?->value ?? '';
                                                $button_translate = $react_delivery_section_button_name->translations->firstWhere('locale', $lang)?->value ?? '';
                                            ?>
                                            <input type="hidden" name="lang[]" value="{{ $lang }}">
                                            <div class="d-none lang_form-float5" id="{{ $lang }}-form5">
                                                <div class="form-group mb-2">
                                                    <label class="form-label fw-400">{{ translate('Title') }} ({{ strtoupper($lang) }})
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                              data-original-title="{{ translate('The headline specifically for recruiting delivery drivers.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                    </label>
                                                    <input type="text" class="form-control" data-maxlength="50"
                                                           name="react_delivery_section_title[]"
                                                           value="{{ $title_translate }}" placeholder="{{ translate('Enter Title...') }}">
                                                    <div class="d-flex justify-content-end">
                                                        <span class="text-body-light text-right d-block mt-1">0/50</span>
                                                    </div>
                                                </div>

                                                <div class="form-group mb-2">
                                                    <label class="form-label fw-400">{{ translate('Subtitle') }} ({{ strtoupper($lang) }})
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                              data-original-title="{{ translate('The key benefit or offer for delivery drivers.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                    </label>
                                                    <input type="text" class="form-control" data-maxlength="100"
                                                           name="react_delivery_section_sub_title[]"
                                                           value="{{ $subtitle_translate }}" placeholder="{{ translate('Enter Subtitle...') }}">
                                                    <div class="d-flex justify-content-end">
                                                        <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                    </div>
                                                </div>

                                                <div class="form-group mb-0">
                                                    <label class="form-label fw-400">{{ translate('Button Name') }} ({{ strtoupper($lang) }})
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                              data-original-title="{{ translate('Enter a proper button name that helps users make a decision.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                    </label>
                                                    <input type="text" class="form-control"
{{--                                                           data-maxlength="15"--}}
                                                           name="react_delivery_section_button_name[]"
                                                           value="{{ $button_translate }}" placeholder="{{ translate('Ex: Register Now') }}">
{{--                                                    <div class="d-flex justify-content-end">--}}
{{--                                                        <span class="text-body-light text-right d-block mt-1">0/10</span>--}}
{{--                                                    </div>--}}
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

                                        <div class="p-xxl-20 d-flex align-items-center justify-content-center p-12 global-bg-box text-center rounded h-100 mt-5">
                                            <div class="">
                                                <div class="mb-xxl-5 mb-xl-4 mb-3 text-start">
                                                    <h5 class="mb-1">{{ translate('Section Background Image') }}</h5>
                                                    <p class="mb-0 fs-12 gray-dark">
                                                        {{ translate('Upload your section background image.') }}</p>
                                                </div>
                                                <div class="text-center">
                                                @include('admin-views.partials._image-uploader', [
                                                'id' => 'image-input',
                                                'name' => 'react_delivery_section_image',
                                                'ratio' => '3:1',
                                                'isRequired' => $react_delivery_section_image?->value ? false : true,
                                                'existingImage' => $react_delivery_section_image?->value ? Helpers::get_full_url('react_delivery_section_image', $react_delivery_section_image?->value, $react_delivery_section_image?->storage[0]?->value ?? 'public', 'upload_1_1') : null,
                                                'imageExtension' => IMAGE_EXTENSION,
                                                'imageFormat' => IMAGE_FORMAT,
                                                'maxSize' => MAX_FILE_SIZE,
                                                'pixel'=>'1260 x 360'
                                                ])
                                                </div>
                                            </div>
                                        </div>
                                </div>
                            </div>
                        </div>

                        <!-- Deliveryman App Download -->
                        <div class="col-md-6">
                            <div class="card-custom-xl h-100">
                                <div class="d-flex gap-2 justify-content-between flex-md-nowrap flex-wrap mb-20">
                                    <div>
                                        <h4 class="mb-1">{{ translate('Deliveryman App Download') }}</h4>
                                        <p class="mb-0 fs-12 text-gray1">{{ translate('Manage the promotional text and links for the deliverymen application.') }}</p>
                                    </div>
                                    <label class="toggle-switch toggle-switch-sm m-0">
                                        <input type="checkbox" class="status toggle-switch-input"
                                               name="react_delivery_app_download_status"
                                               value="1" {{ ($react_delivery_app_download_status['value'] ?? 0) == 1 ? 'checked' : '' }}>
                                        <span class="toggle-switch-label text mb-0">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>

                                <div class="bg-light2 rounded p-xxl-4 p-3">
                                    @if ($language)
                                        <ul class="nav nav-tabs mb-4 border-bottom">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link6 active" href="#" id="default-link6">{{ translate('messages.default') }}</a>
                                            </li>
                                            @foreach (json_decode($language) as $lang)
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link6" href="#" id="{{ $lang }}-link6">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    <!-- Default Language -->
                                    <div class="lang_form-float6" id="default-form6">
                                        <div class="form-group mb-2">
                                            <label for="title_deliveryman_default" class="form-label fw-400">{{ translate('Title') }} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                      data-original-title="{{ translate('The headline for the delivery partner app.') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                            </label>
                                            <textarea required id="title_deliveryman_default" rows="1" data-maxlength="50"
                                                      class="form-control" name="react_delivery_app_download_title[]"
                                                      placeholder="{{ translate('Ex: Download Now') }}">{{ $react_delivery_app_download_title?->getRawOriginal('value') ?? '' }}</textarea>
                                            <div class="d-flex justify-content-end">
                                                <span class="text-body-light text-right d-block mt-1">0/50</span>
                                            </div>
                                        </div>

                                        <div class="form-group mb-0">
                                            <label for="subtitle_deliveryman_default" class="form-label fw-400">{{ translate('Subtitle') }} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                      data-original-title="{{ translate(' A short line describing the app\'s purpose for drivers.') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                            </label>
                                            <textarea required id="subtitle_deliveryman_default" rows="1" data-maxlength="100"
                                                      class="form-control" name="react_delivery_app_download_sub_title[]"
                                                      placeholder="{{ translate('Ex: Scan to Download') }}">{{ $react_delivery_app_download_sub_title?->getRawOriginal('value') ?? '' }}</textarea>
                                            <div class="d-flex justify-content-end">
                                                <span class="text-body-light text-right d-block mt-1">0/100</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Dynamic Languages -->
                                    @if ($language)
                                        @foreach (json_decode($language) as $lang)
                                            <?php
                                            $description_translate4 = [];
                                            $title_translate4 = [];
                                            if(isset($react_delivery_app_download_title->translations) && count($react_delivery_app_download_title->translations)){
                                                foreach($react_delivery_app_download_title->translations as $t){
                                                    if($t->locale == $lang && $t->key=='react_delivery_app_download_title'){
                                                        $title_translate4[$lang]['value'] = $t->value;
                                                    }
                                                }
                                            }

                                            if(isset($react_delivery_app_download_sub_title->translations) && count($react_delivery_app_download_sub_title->translations)){
                                                foreach($react_delivery_app_download_sub_title->translations as $t){
                                                    if($t->locale == $lang && $t->key=='react_delivery_app_download_sub_title'){
                                                        $description_translate4[$lang]['value'] = $t->value;
                                                    }
                                                }
                                            }
                                            ?>

                                            <div class="d-none lang_form-float6" id="{{ $lang }}-form6">
                                                <div class="form-group mb-2">
                                                    <label class="form-label fw-400">{{ translate('Title') }} ({{ strtoupper($lang) }})
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                              data-original-title="{{ translate('The headline for the delivery partner app.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                    </label>
                                                    <textarea rows="1" data-maxlength="50" class="form-control" name="react_delivery_app_download_title[]"
                                                              placeholder="{{ translate('Ex: Download Now') }}">{{ $title_translate4[$lang]['value'] ?? '' }}</textarea>
                                                    <div class="d-flex justify-content-end">
                                                        <span class="text-body-light text-right d-block mt-1">0/50</span>
                                                    </div>
                                                </div>

                                                <div class="form-group mb-0">
                                                    <label class="form-label fw-400">{{ translate('Subtitle') }} ({{ strtoupper($lang) }})
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                              data-original-title="{{ translate(' A short line describing the app\'s purpose for drivers.') }}">
                                                            <i class="tio-info text-gray1 fs-16"></i>
                                                        </span>
                                                    </label>
                                                    <textarea rows="1" data-maxlength="100" class="form-control" name="react_delivery_app_download_sub_title[]"
                                                              placeholder="{{ translate('Ex: Scan to Download') }}">{{ $description_translate4[$lang]['value'] ?? '' }}</textarea>
                                                    <div class="d-flex justify-content-end">
                                                        <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                <div class="bg-light2 rounded p-xxl-4 p-3">
                                    <div class="form-group mt-3">
                                        <label class="form-label fw-400">{{ translate('Download Link For Android') }} <span class="text-danger">*</span>
                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                  data-original-title="{{ translate('The direct link to the delivery driver app on the Google Play Store.') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                        </label>
                                        <input required type="url" class="form-control h--45px" name="react_delivery_app_download_link"
                                               placeholder="{{ translate('Ex:_https://www.playstore.com/') }}"
                                               value="{{ $react_delivery_app_download_link['value'] ?? '' }}">
                                    </div>
                                    <div class="form-group mt-3">
                                        <label class="form-label fw-400">{{ translate('Download Link For IOS') }} <span class="text-danger">*</span>
                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right"
                                                  data-original-title="{{ translate('The direct link to the delivery driver app on the Google Play Store.') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                        </label>
                                        <input required type="url" class="form-control h--45px" name="react_delivery_app_download_link_for_ios"
                                               placeholder="{{ translate('Ex:_https://www.apple.com/app-store/') }}"
                                               value="{{ $react_delivery_app_download_link_for_ios['value'] ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="btn--container justify-content-end mt-4">
                        <button type="reset" class="btn btn--reset">{{ translate('Reset') }}</button>
                        <button type="submit" class="btn btn--primary">{{ translate('Save') }}</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    @include('admin-views.landing_page.react.partials.registration_section_guideline')

@endsection

@push('script_2')
    <script>

        $(".lang_link1").click(function(e) {
            e.preventDefault();
            $(".lang_link1").removeClass('active');
            $(".lang_form-float").addClass('d-none');
            $(this).addClass('active');
            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 6);
            $("#" + lang + "-form-float").removeClass('d-none');
            if (lang === 'default') {
                $(".default-form-float").removeClass('d-none');
            }
        });

        $(".lang_link2").click(function(e) {
            e.preventDefault();
            $(".lang_link2").removeClass('active');
            $(".lang_form-float2").addClass('d-none');
            $(this).addClass('active');
            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 6);
            $("#" + lang + "-form-float2").removeClass('d-none');
            if (lang === 'default') {
                $(".default-form-float2").removeClass('d-none');
            }
        });

        $(".lang_link3").click(function (e) {
            e.preventDefault();
            $(".lang_link3").removeClass("active");
            $(this).addClass("active");

            let id = this.id.split("-")[0];
            $(".lang_form-float3").addClass("d-none");
            $("#" + id + "-form3").removeClass("d-none");
        });

        $(".lang_link5").click(function (e) {
            e.preventDefault();
            $(".lang_link5").removeClass("active");
            $(this).addClass("active");

            let id = this.id.split("-")[0];
            $(".lang_form-float5").addClass("d-none");
            $("#" + id + "-form5").removeClass("d-none");
        });

        $(".lang_link6").click(function (e) {
            e.preventDefault();
            $(".lang_link6").removeClass("active");
            $(this).addClass("active");

            let id = this.id.split("-")[0];
            $(".lang_form-float6").addClass("d-none");
            $("#" + id + "-form6").removeClass("d-none");
        });

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.upload-file').forEach(function (container) {
                const fileInput = container.querySelector('input[type="file"]');
                const previewImg = container.querySelector('.upload-file-img');
                const uploadText = container.querySelector('.upload-file-textbox');
                const removeBtn = container.querySelector('.remove_btn');

                if (!fileInput || !previewImg || !removeBtn) return;

                let removeFlag = container.querySelector('input[type="hidden"][name$="_remove"]');
                if (!removeFlag) {
                    removeFlag = document.createElement('input');
                    removeFlag.type = 'hidden';
                    removeFlag.name = fileInput.name + '_remove';
                    removeFlag.value = '0';
                    container.appendChild(removeFlag);
                }

                removeBtn.addEventListener('click', function () {
                    removeFlag.value = '1';
                    fileInput.value = '';

                    previewImg.removeAttribute('src');
                    previewImg.removeAttribute('data-default-src');
                    previewImg.style.display = 'none';

                    if (uploadText) uploadText.style.display = 'block';
                });

                fileInput.addEventListener('change', function () {
                    removeFlag.value = '0';
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            previewImg.src = e.target.result;
                            previewImg.style.display = 'block';
                            if (uploadText) uploadText.style.display = 'none';
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            });
        });

    </script>
@endpush
