
@extends('layouts.vendor.app')
@section('title',translate('messages.edit_restaurant'))
@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{dynamicAsset('assets/admin')}}/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
     <!-- Custom styles for this page -->
     <link href="{{dynamicAsset('assets/admin/css/croppie.css')}}" rel="stylesheet">
     <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@section('content')
    <div class="content container-fluid">
        @include('vendor-views.partials.business_setup_header')
        @php($language=\App\Models\BusinessSetting::where('key','language')->first())
        @php($language = $language->value ?? null)
        @php($default_lang = str_replace('_', '-', app()->getLocale()))
        <form action="{{route('vendor.shop.update')}}" method="post"
        enctype="multipart/form-data">
        @csrf
            <div class="card mb-20">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h3 class="mb-1">{{ translate('messages.Edit_Restaurant') }}</h3>
                        <p class="fs-12 mb-0">{{ translate('messages.Here you setup your all business information.') }}</p>
                    </div>
                    <a href="{{route('vendor.shop.view')}}" class="text-primary font-semibold d-flex gap-1 align-items-center">
                        <i class="tio-arrow-backward"></i>
                        {{ translate('messages.Back to Restaurant Settings') }}
                    </a>
                </div>
                <div class="card-body">
                    <div class="card card-body mb-20">
                        <div class="mb-20">
                            <h4 class="mb-1">{{ translate('messages.Restaurant_Name') }}</h4>
                            <p class="fs-12 mb-0">{{ translate('messages.Here you setup your all business information.') }}</p>
                        </div>
                        <div class="__bg-F8F9FC-card mb-20">
                            @if($language)
                            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                <ul class="nav nav-tabs mb-4">
                                    <li class="nav-item">
                                        <a class="nav-link lang_link active"
                                        href="#"
                                        id="default-link">{{ translate('Default') }}</a>
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
                            <div class="row g-3">
                                <div class="col-md-6">
                                        <div class="form-group mb-0 lang_form" id="default-form">
                                                <label class="input-label d-flex gap-1" for="exampleFormControlInput1">
                                                    {{ translate('messages.restaurant') }}
                                                    {{ translate('messages.name') }} ({{translate('messages.default')}})
                                                    <span class="text-danger">*</span>
                                                </label>
                                            <input type="text" name="name[]" class="form-control" placeholder="{{ translate('messages.restaurant_name') }}" maxlength="191" value="{{$shop?->getRawOriginal('name')}}"  >
                                        </div>
                                        @if ($language)
                                            <input type="hidden" name="lang[]" value="default">
                                            @foreach(json_decode($language) as $lang)
                                                <?php
                                                    if(count($shop['translations'])){
                                                        $translate = [];
                                                        foreach($shop['translations'] as $t)
                                                        {
                                                            if($t->locale == $lang && $t->key=="name"){
                                                                $translate[$lang]['name'] = $t->value;
                                                            }

                                                        }
                                                    }
                                                ?>
                                                <div class="form-group mb-0 d-none lang_form" id="{{$lang}}-form">
                                                    <label class="input-label d-flex gap-1" for="exampleFormControlInput1">
                                                        {{ translate('messages.restaurant_name') }} ({{strtoupper($lang)}})
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" name="name[]" class="form-control" placeholder="{{ translate('messages.restaurant_name') }}" maxlength="191" value="{{$translate[$lang]['name']??''}}"  >
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{$lang}}">
                                            @endforeach
                                        @endif
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0  lang_form default-form"  >
                                        <label for="address" class="input-label d-flex gap-1">
                                            {{ translate('messages.restaurant_address')}} ({{translate('messages.default')}})
                                            <span class="text-danger">*</span>
                                        </label>
                                        <textarea type="text" rows="1" name="address[]" value="" placeholder="{{ translate('Ex : House-45, Road-08, Sector-12, Mirupara, Test City') }}"
                                            class="form-control" id="address">{{$shop->address}}</textarea>
                                    </div>
                                    @if ($language)
                                    @foreach(json_decode($language) as $lang)
                                        <?php
                                            if(count($shop['translations'])){
                                                $translate = [];
                                                foreach($shop['translations'] as $t)
                                                {
                                                    if($t->locale == $lang && $t->key=="address"){
                                                        $translate[$lang]['address'] = $t->value;
                                                    }

                                                }
                                            }
                                        ?>
                                        <div class="form-group mb-0  d-none lang_form" id="{{$lang}}-form1">
                                                <label class="input-label d-flex gap-1" for="exampleFormControlInput1">
                                                    {{ translate('messages.restaurant_address') }} ({{strtoupper($lang)}})
                                                    <span class="text-danger">*</span>
                                                </label>
                                            <textarea type="text" rows="1" name="address[]" value="" placeholder="{{ translate('Ex : House-45, Road-08, Sector-12, Mirupara, Test City') }}"
                                                class="form-control" id="address" >{{  $translate[$lang]['address'] ?? ''}}</textarea>
                                        </div>
                                    @endforeach
                                @endif
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0 pt-lg-1">
                            <label for="contact" class="input-label d-flex gap-1">{{translate('messages.contact_number')}}<span class="text-danger">*</span></label>
                            <input type="tel" name="contact" value="{{$shop->phone}}" placeholder="{{ translate('Ex : +880 123456789') }}" class="form-control h--45px" id="contact"
                                    required>
                        </div>
                    </div>
                    <div class="card card-body mb-20">
                        <div class="mb-20">
                            <h4 class="mb-1">{{ translate('messages.Logo_&_Cover') }}</h4>
                            <p class="fs-12 mb-0">{{ translate('messages.Here you can set your brand logo for website & app.') }}</p>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="p-xxl-20 p-12 global-bg-box rounded mb-20 h-100">
                                    <div class="">
                                        <div class="mb-4">
                                            <h5 class="mb-1">
                                                {{ translate('messages.Restaurant Logo') }}
                                            </h5>
                                            <p class="mb-0 fs-12 gray-dark">{{ translate('messages.Upload your Business Logo') }}</p>
                                        </div>
                                        <div class="text-center">
                                            <div class="upload-file mx-auto">
                                                <input type="file" name="image" class="upload-file__input single_file_input"
                                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                                <label class="upload-file__wrapper ratio-1 mx-auto m-0">
                                                    <div class="upload-file-textbox text-center" style="">
                                                        <img width="34" class="svg" src="{{dynamicAsset('assets/admin/img/image-upload.png')}}" alt="img">
                                                        <h6 class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">
                                                            <span class="text-info">{{ translate('messages.Click_to_upload') }}</span>
                                                            <br>
                                                            {{ translate('messages.or_drag_and_drop') }}
                                                        </h6>
                                                    </div>
                                                    <img class="upload-file-img" loading="lazy"
                                                        src="{{ $shop?->logo_full_url ?? dynamicAsset('assets/admin/img/image-place-holder.png') }}"
                                                        data-default-src="{{ $shop?->logo_full_url ?? dynamicAsset('assets/admin/img/image-place-holder.png') }}"
                                                        alt="" style="display: none;">
                                                </label>
                                                <div class="overlay">
                                                    <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                                        <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                            <i class="tio-invisible"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                            <i class="tio-edit"></i>
                                                        </button>
                                                        {{-- <button type="button" class="remove_btn btn icon-btn">
                                                            <i class="tio-delete text-danger"></i>
                                                        </button> --}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="fs-10 text-center mb-0 mt-4">
                                            {{ translate('messages.JPG, JPEG, PNG, Gif Image size : Max 2 MB') }} <span class="font-medium text-title">({{ translate('messages.1:1') }})</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-xxl-20 p-12 global-bg-box rounded mb-20 h-100">
                                    <div class="">
                                        <div class="mb-4">
                                            <h5 class="mb-1">
                                                {{ translate('Cover Image') }}
                                            </h5>
                                            <p class="mb-0 fs-12 gray-dark">{{ translate('Upload your Business Logo') }}</p>
                                        </div>
                                        <div class="text-center">
                                            <div class="upload-file mx-auto">
                                                <input type="file" name="photo" class="upload-file__input single_file_input"
                                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                                <label class="upload-file__wrapper w-344 mx-auto m-0">
                                                    <div class="upload-file-textbox text-center" style="">
                                                        <img width="34" class="svg" src="{{dynamicAsset('assets/admin/img/image-upload.png')}}" alt="img">
                                                        <h6 class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">
                                                            <span class="text-info">{{ translate('messages.Click_to_upload') }}</span>
                                                            <br>
                                                            {{ translate('messages.or_drag_and_drop') }}
                                                        </h6>
                                                    </div>
                                                    <img class="upload-file-img" loading="lazy"
                                                        src="{{ $shop?->cover_photo_full_url ?? dynamicAsset('assets/admin/img/restaurant_cover.jpg') }}"
                                                        data-default-src="{{ $shop?->cover_photo_full_url ?? dynamicAsset('assets/admin/img/restaurant_cover.jpg') }}"
                                                        alt="" style="display: none;">
                                                </label>
                                                <div class="overlay">
                                                    <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                                        <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                            <i class="tio-invisible"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                            <i class="tio-edit"></i>
                                                        </button>
                                                        {{-- <button type="button" class="remove_btn btn icon-btn">
                                                            <i class="tio-delete text-danger"></i>
                                                        </button> --}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="fs-10 text-center mb-0 mt-4">
                                            {{ translate('messages.JPG, JPEG, PNG, Gif Image size : Max 2 MB') }} <span class="font-medium text-title">({{ translate('messages.1100 x 320') }})</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end">
                        <a class="btn btn--reset min-w-120" href="{{route('vendor.shop.view')}}">{{translate('messages.cancel')}}</a>
                        <button type="submit" class="btn btn--primary min-w-120" id="btn_update">{{translate('messages.update')}}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('script_2')
@endpush
