@extends('layouts.admin.app')

@section('title',translate('messages.landing_page_settings'))

@section('content')
<div class="content container-fluid">
    <div class="page-header pb-0">
        <div class="d-flex flex-wrap justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{ dynamicAsset('assets/admin/img/landing-page.png') }}" class="mw-26px" alt="public">
                </span>
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

    <form class="validate-form" action="{{ route('admin.react_landing_page.service_update',[$service->id]) }}" method="POST" enctype="multipart/form-data">
        <div class="tab-content py-3 px-xxl-4 px-3 mb-15 mt-4">
            <div class="tab-pane fade show active">
                @csrf
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-8">
                                <div class="bg-light2 p-xl-20 p-3 rounded h-100">
                                    <div class="col-md-12">
                                        @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                                        @php($language = $language->value ?? null)
                                        @php($default_lang = str_replace('_', '-', app()->getLocale()))
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
                                    </div>
                                    <div class="col-md-12 lang_form" id="default-form">
                                        <input type="hidden" name="lang[]" value="default">

                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label for="title" class="form-label fw-400">{{translate('messages.title')}} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                    <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ translate('Write_the_title_within_50_characters') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                                </label>
                                                <input required id="title" type="text" data-maxlength="50" name="title[]"   value="{{ $service?->getRawOriginal('title') }}" class="form-control" placeholder="{{translate('Ex:_John')}}">
                                                <div class="d-flex justify-content-end">
                                                <span class="text-body-light text-right d-block mt-1">0/50</span>
                                            </div>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="sub_title" class="form-label fw-400">{{translate('messages.Subtitle')}} ({{ translate('messages.default') }})
                                                    <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ translate('Write_the_subtitle_within_100_characters') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                                </label>
                                                <input required id="sub_title" type="text" data-maxlength="100" name="sub_title[]"  value="{{ $service?->getRawOriginal('sub_title') }}" class="form-control" placeholder="{{translate('Very_Good_Company')}}">
                                                <div class="d-flex justify-content-end">
                                                <span class="text-body-light text-right d-block mt-1">0/100</span>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                    @forelse(json_decode($language) as $lang)
                                    <?php
                                        if($service?->translations){
                                            $translate = [];
                                            foreach($service?->translations as $t)
                                            {
                                                if($t->locale == $lang && $t->key=="react_service_title"){
                                                    $translate[$lang]['react_service_title'] = $t->value;
                                                }
                                                if($t->locale == $lang && $t->key=="react_service_sub_title"){
                                                    $translate[$lang]['react_service_sub_title'] = $t->value;
                                                }
                                            }
                                        }

                                        ?>

                                    <div class="col-md-12 d-none lang_form" id="{{$lang}}-form1">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label for="title{{$lang}}" class="form-label fw-400">{{translate('title')}} ({{strtoupper($lang)}})
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                    data-placement="right"
                                                    data-original-title="{{ translate('Write_the_title_within_50_characters') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                </label>
                                                <input  id="title{{$lang}}" type="text" data-maxlength="50" name="title[]" value="{{ $translate[$lang]['react_service_title']??'' }}" class="form-control" placeholder="{{translate('messages.name_here...')}}">
                                                <div class="d-flex justify-content-end">
                                                <span class="text-body-light text-right d-block mt-1">0/50</span>
                                            </div>
                                            </div>
                                            <div class="col-12">
                                                <label for="sub_title{{$lang}}" class="form-label fw-400">{{translate('Subtitle')}} ({{strtoupper($lang)}})
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                    data-placement="right"
                                                    data-original-title="{{ translate('Write_the_subtitle_within_100_characters') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                </label>
                                                    <input id="sub_title{{$lang}}"  type="text" data-maxlength="100" name="sub_title[]"  value="{{ $translate[$lang]['react_service_sub_title']??'' }}"  class="form-control" placeholder="{{translate('Very_Good_Company')}}">
                                                    <div class="d-flex justify-content-end">
                                                <span class="text-body-light text-right d-block mt-1">0/100</span>
                                            </div>
                                                </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                    @empty
                                    @endforelse
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="p-xxl-20 d-flex align-items-center justify-content-center p-12 global-bg-box text-center rounded h-100">
                                    <div class="">
                                        <div class="mb-xxl-4 mb-xl-4 mb-3 text-start">
                                            <h5 class="mb-1">{{ translate('Icon_Image') }} <span class="text-danger">*</span></h5>
                                            <p class="mb-0 fs-12 gray-dark">{{ translate('Upload service icon/image') }}</p>
                                        </div>
                                        @include('admin-views.partials._image-uploader', [
                                            'id' => 'image-input',
                                            'name' => 'image',
                                            'ratio' => '1:1',
                                            'isRequired' => $service->image ? false : true,
                                            'existingImage' => $service?->image_full_url ?? dynamicAsset('assets/admin/img/aspect-1.png'),
                                            'imageExtension' => IMAGE_EXTENSION,
                                            'imageFormat' => IMAGE_FORMAT,
                                            'maxSize' => MAX_FILE_SIZE,
                                        ])
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="btn--container justify-content-end mt-4">
                            <button type="reset" class="btn btn--reset">{{translate('Reset')}}</button>
                            <button type="submit"   class="btn btn--primary">{{translate('messages.Update')}}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
    <form  id="remove_image_1_form" action="{{ route('admin.remove_image') }}" method="post">
        @csrf
        <input type="hidden" name="id" value="{{  $service?->id}}" >
        <input type="hidden" name="model_name" value="ReactService" >
        <input type="hidden" name="image_path" value="react_service_image" >
        <input type="hidden" name="field_name" value="image" >
    </form>

@include('admin-views.landing_page.react.partials.service_guideline')

@endsection
