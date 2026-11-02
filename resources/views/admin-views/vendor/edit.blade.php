@extends('layouts.admin.app')

@section('title', translate('Update_restaurant_info'))
@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset('assets/admin/css/intlTelInput.css') }}" />
    <link href="{{ dynamicAsset('assets/admin/css/tags-input.min.css') }}" rel="stylesheet">
@endpush


@if (request()->type === 'new_join')
@section('restaurant_new_join')
@else
@section('restaurant_list')
@endif
    active
@endsection

@section('content')
    <div class="content container-fluid initial-57">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-shop-outlined"></i>
                        {{ translate('messages.update_restaurant') }}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        @php
            $delivery_time_start = explode('-', $restaurant->delivery_time)[0] ?? 10;
            $delivery_time_end = explode('-', $restaurant->delivery_time)[1] ?? 30;
            $delivery_time_type = explode('-', $restaurant->delivery_time)[2] ?? 'min';
        @endphp
        @php($language = \App\Models\BusinessSetting::where('key', 'language')->first())
        @php($language = $language->value ?? null)
        @php($default_lang = str_replace('_', '-', app()->getLocale()))

        <form action="{{ route('admin.restaurant.update', [$restaurant['id']]) }}" method="post"
            class="validate-form global-ajax-form" id="res_form" enctype="multipart/form-data">
            @csrf
            @if (request()->type === 'new_join')
                <input type="hidden" name="new_join" value="1">
            @endif
            <div class="row g-2">
                <div class="col-md-7 col-xl-8">
                    <div class="card shadow--card-2">
                        <div class="card-body">
                            <div class="bg-light rounded p-md-3 bg-clr-none mb-20">
                                @if ($language)
                                    <div class="js-nav-scroller tabs-slide-language hs-nav-scroller-horizontal">
                                        <ul class="nav border-0 nav-tabs mb-4">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link active" href="#"
                                                    id="default-link">{{ translate('Default') }}</a>
                                            </li>
                                            @foreach (json_decode($language) as $lang)
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link" href="#"
                                                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="arrow-area">
                                            <div class="button-prev align-items-center">
                                                <button type="button"
                                                    class="btn btn-click-prev mr-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                                                    <i class="tio-chevron-left fs-24"></i>
                                                </button>
                                            </div>
                                            <div class="button-next align-items-center">
                                                <button type="button"
                                                    class="btn btn-click-next ml-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                                                    <i class="tio-chevron-right fs-24"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="lang_form" id="default-form">
                                    <div class="form-group ">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ translate('messages.restaurant_name') }}
                                            ({{ translate('messages.default') }}) <span class="text-danger">*</span></label>
                                        <input required type="text" name="name[]" class="form-control"
                                            placeholder="{{ translate('messages.Ex:_ABC_Company') }} " maxlength="191"
                                            value="{{ $restaurant?->getRawOriginal('name') }}">
                                        <div class="d-flex justify-content-end">
                                            <span class="text-body-light text-right d-block mt-1">0/191</span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="lang[]" value="default">

                                    <div>
                                        <label class="input-label"
                                            for="address">{{ translate('messages.restaurant_address') }}
                                            ({{ translate('messages.default') }}) <span
                                                class="text-danger">*</span></label>
                                        <textarea required id="address" maxlength="100" name="address[]" class="form-control h-70px"
                                            placeholder="{{ translate('messages.Ex:_House#94,_Road#8,_Abc_City') }} ">{{ $restaurant?->getRawOriginal('address') }}</textarea>
                                        <div class="d-flex justify-content-end">
                                            <span class="text-body-light text-right d-block mt-1">0/100</span>
                                        </div>
                                    </div>
                                </div>
                                @if ($language)
                                    @foreach (json_decode($language) as $lang)
                                        <?php
                                        if (count($restaurant['translations'])) {
                                            $translate = [];
                                            foreach ($restaurant['translations'] as $t) {
                                                if ($t->locale == $lang && $t->key == 'name') {
                                                    $translate[$lang]['name'] = $t->value;
                                                }
                                                if ($t->locale == $lang && $t->key == 'address') {
                                                    $translate[$lang]['address'] = $t->value;
                                                }
                                            }
                                        }
                                        ?>

                                        <div class="d-none lang_form" id="{{ $lang }}-form">

                                            <div class="form-group mb-0">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ translate('messages.restaurant_name') }}
                                                    ({{ strtoupper($lang) }})
                                                </label>
                                                <input type="text" name="name[]" class="form-control"
                                                    placeholder="{{ translate('messages.Ex:_ABC_Company') }}"
                                                    maxlength="191" value="{{ $translate[$lang]['name'] ?? '' }}">
                                            </div>
                                            <input type="hidden" name="lang[]" value="{{ $lang }}">

                                            <div>
                                                <label class="input-label"
                                                    for="address">{{ translate('messages.restaurant_address') }}
                                                    ({{ strtoupper($lang) }})</label>
                                                <textarea id="address{{ $lang }}" name="address[]" class="form-control h-70px"
                                                    placeholder="{{ translate('messages.Ex:_House#94,_Road#8,_Abc_City') }} "> {{ $translate[$lang]['address'] ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="form-group m-0">
                                        <label class="input-label"
                                            for="cuisine">{{ translate('messages.cuisine') }}</label>
                                        <select name="cuisine_ids[]" id="cuisine"
                                            class="form-control h--45px min--45 js-select2-custom" multiple="multiple"
                                            data-placeholder="{{ translate('messages.select_Cuisine') }}" data-search-placeholder="{{ translate('messages.search_cuisine') }}">
                                            @php($cuisine_array = \App\Models\Cuisine::where('status', 1)->get(['id', 'name'])->toArray())
                                            @php($selected_cuisine = isset($restaurant->cuisine) ? $restaurant->cuisine->pluck('id')->toArray() : [])
                                            @foreach ($cuisine_array as $cu)
                                                {{-- The label stays on one line: select2 copies the option's raw text into the chip's title. --}}
                                                <option value="{{ $cu['id'] }}" {{ in_array($cu['id'], $selected_cuisine) ? 'selected' : '' }}>{{ $cu['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group m-0">
                                        <label class="input-label" for="choice_zones">{{ translate('messages.zone') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select required name="zone_id" id="choice_zones"
                                            data-placeholder="{{ translate('messages.select_zone') }}" data-search-placeholder="{{ translate('search_zone') }}"
                                            class="form-control h--45px js-select2-custom get_zone_data">
                                            <option value="{{ $restaurant->zone_id }}" selected>
                                                {{ $restaurant->zone->name }}</option>
                                            @foreach (\App\Models\Zone::where('status', 1)->get(['id', 'name']) as $zone)
                                                @if (auth('admin')?->user()?->zone_id)
                                                    @if (auth('admin')->user()->zone_id == $zone->id)
                                                        <option value="{{ $zone->id }}"
                                                            {{ $restaurant->zone_id == $zone->id ? 'selected' : '' }}>
                                                            {{ $zone->name }}</option>
                                                    @endif
                                                @else
                                                    @if ($restaurant->zone_id !== $zone->id)
                                                        <option value="{{ $zone->id }}"
                                                            {{ $restaurant->zone_id == $zone->id ? 'selected' : '' }}>
                                                            {{ $zone->name }}</option>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12 map_custom-controls position-relative">
                                    <input id="pac-input" class="controls rounded"
                                        title="{{ translate('messages.search_your_location_here') }}" type="text"
                                        placeholder="{{ translate('messages.search_here') }}">
                                    <div style="height: 220px !important" id="map"></div>

                                    <div class="d-flex bg-white align-items-center gap-1 laglng-controller">
                                                    <div id="outOfZone" class="map-alert bg-dark d-flex align-items-center justify-content-center rounded-8 py-2 px-2 fs-12 text-white mb-2">
                                                        <img class="" src="{{dynamicAsset('assets/admin/img/warning-cus.png')}}" alt="img"  onerror="this.style.display='none';"> {{ translate('Please place the marker inside the available zones.') }}
                                                    </div>
                                                    <div id="latlng" class="d-flex">
                                                        <input type="" class="border-0 outline-0 no-validation-message" id="latitude" name="latitude" placeholder="{{ translate('messages.Ex:_-94.22213') }} " value="{{ old('latitude') }}"  readonly>
                                                        <span class="text-gray1">|</span>
                                                        <input type="" class="border-0 outline-0 no-validation-message" name="longitude" placeholder="{{ translate('messages.Ex:_103.344322') }} "   id="longitude" value="{{ old('longitude') }}"  readonly>
                                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 col-xl-4">
                    <div class="card shadow--card-2">

                        <div class="card-body">
                            <div class="">
                                <!-- <New Markup> -->
                                <div class="p-xxl-20 p-12 global-bg-box rounded mb-20">
                                    <div class="pb-md-5">
                                        <div class="mb-4 text-start">
                                            <h5 class="mb-1">
                                                {{ translate('Logo') }} <span class="text-danger">*</span>
                                            </h5>
                                            <p class="mb-0 fs-12 gray-dark">{{ translate('Upload your website Logo') }}
                                            </p>
                                        </div>
                                        <div class="text-center">
                                            @include('admin-views.partials._image-uploader', [
                                                'id' => 'image-input',
                                                'name' => 'logo',
                                                'ratio' => '1:1',
                                                'isRequired' => false,
                                                'existingImage' => $restaurant->logo_full_url,
                                                'imageExtension' => IMAGE_EXTENSION,
                                                'imageFormat' => IMAGE_FORMAT,
                                                'maxSize' => MAX_FILE_SIZE,
                                            ])

                                        </div>
                                    </div>
                                </div>
                                <!-- <New Markup> -->
                                <div class="p-xxl-20 p-12 global-bg-box rounded mb-20">
                                    <div class="pb-md-5">
                                        <div class="mb-4 text-start">
                                            <h5 class="mb-1">
                                                {{ translate('Restaurant Cover') }} <span class="text-danger">*</span>
                                            </h5>
                                            <p class="mb-0 fs-12 gray-dark">{{ translate('Upload your website Cover') }}
                                            </p>
                                        </div>
                                        <div class="text-center">
                                            @include('admin-views.partials._image-uploader', [
                                                'id' => 'image-input',
                                                'name' => 'cover_photo',
                                                'ratio' => '3:1',
                                                'isRequired' => false,
                                                'existingImage' => $restaurant->cover_photo_full_url,
                                                'imageExtension' => IMAGE_EXTENSION,
                                                'imageFormat' => IMAGE_FORMAT,
                                                'maxSize' => MAX_FILE_SIZE,
                                            ])
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="shadow-sm card">
                        <div class="card-header p-3">
                            <h4 class="m-0">
                                {{ translate('Restaurant_Info') }}
                            </h4>
                        </div>
                        <div class="p-3">
                            <div class="bg-light rounded p-space-0 bg-clr-none p-md-3">
                                <div class="row g-2">

                                    <div class="col-md-6">

                                        <label class="mb-2 fs-14 text-dark"
                                            for="time">{{ translate('Estimated Delivery Time ( Min & Maximum Time )') }}
                                            <span class="text-danger">*</span></label>
                                        <div
                                            class="floating--date-inner d-flex align-items-center border rounded overflow-hidden">
                                            <div class="item w-100">
                                                <input id="minimum_delivery_time" type="number"
                                                    name="minimum_delivery_time"
                                                    class="form-control w-100 h--45px border-0 rounded-0"
                                                    placeholder="{{ translate('messages.Ex:_30') }}"
                                                    required value="{{ $delivery_time_start }}">
                                            </div>
                                            <div class="item w-100 border-inline-start">
                                                <input id="maximum_delivery_time" type="number"
                                                    name="maximum_delivery_time"
                                                    class="form-control w-100 h--45px border-0 rounded-0"
                                                    placeholder="{{ translate('messages.Ex:_60') }}"
                                                    required value="{{ $delivery_time_end }}">
                                            </div>
                                            <div class="item smaller min-w-100px">
                                                <select name="delivery_time_type" id="delivery_time_type"
                                                    class="custom-select bg-light h--45px border-0 rounded-0">
                                                    <option value="min"
                                                        {{ $delivery_time_type == 'min' ? 'selected' : '' }}>
                                                        {{ translate('messages.minutes') }}</option>
                                                    <option
                                                        value="hours"{{ $delivery_time_type == 'hours' ? 'selected' : '' }}>
                                                        {{ translate('messages.hours') }}</option>
                                                </select>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="shadow-sm card">
                        <div class="card-header p-3">
                            <h4 class="m-0">
                                {{ translate('owner_info') }}
                            </h4>
                        </div>
                        <div class="p-3">
                            <div class="bg-light rounded p-space-0 bg-clr-none p-md-3">
                                <div class="row g-3">
                                    <div class="col-md-4 col-12">
                                        <div class="form-group m-0">
                                            <label class="input-label"
                                                for="f_name">{{ translate('messages.first_name') }} <span
                                                    class="text-danger">*</span></label>
                                            <input id="f_name" type="text" name="f_name"
                                                class="form-control h--45px"
                                                placeholder="{{ translate('messages.Ex:_Jhon') }} "
                                                value="{{ $restaurant->vendor->f_name }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <div class="form-group m-0">
                                            <label class="input-label"
                                                for="l_name">{{ translate('messages.last_name') }} <span
                                                    class="text-danger">*</span></label>
                                            <input id="l_name" type="text" name="l_name"
                                                class="form-control h--45px"
                                                placeholder="{{ translate('messages.Ex:_Doe') }} "
                                                value="{{ $restaurant->vendor->l_name }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <div class="form-group m-0">
                                            <label class="input-label" for="phone">{{ translate('messages.phone') }}
                                                <span class="text-danger">*</span></label>
                                            <input id="phone" type="tel" name="phone"
                                                class="form-control h--45px"
                                                placeholder="{{ translate('messages.Ex:_+9XXX-XXX-XXXX') }} "
                                                value="{{ $restaurant->phone }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @include('admin-views.partials._custom-fields', [
                    'page_data' => $page_data,
                    'additional_data' => $additional_data ?? [],
                    'additional_documents' => json_decode($restaurant['additional_documents'] ?? '[]', true),
                    'path' => 'additional_documents/'
                ])

                <div class="col-lg-12">
                    <div class="shadow-sm card">
                        <div class="card-header p-3">
                            <h4 class="m-0">
                                {{ translate('Tags') }}
                            </h4>
                            {{-- <p class="m-0 fs-12">Setup your business time zone and format from here</p> --}}
                        </div>
                        <div class="p-3">
                            <div class="bg-light rounded p-space-0 bg-clr-none p-md-3">
                                <input type="text" class="form-control" name="tags"
                                    value="@foreach ($restaurant->tags as $c) {{ $c->tag . ',' }} @endforeach"
                                    placeholder="Enter tags" data-role="tagsinput">
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-lg-12">
                    <div>
                        <div class="card">
                            <div class="card-header p-3">
                                <h3 class="mb-0">{{ translate('Business TIN') }}</h3>
                                {{-- <p class="fz-12px mb-0">{{translate('Lorem ipsum dolor sit amet, consectetur adipiscing elit.')}}</p> --}}
                            </div>
                            <div class="p-3">
                                <div class="row g-3">
                                    <div class="col-md-8 col-xxl-9">
                                        <div class="bg--secondary rounded p-20 h-100">
                                            <div class="form-group">
                                                <label class="input-label mb-2 d-block title-clr fw-normal"
                                                    for="exampleFormControlInput1">{{ translate('Taxpayer Identification Number(TIN)') }}</label>
                                                <input type="text" name="tin" id="tin"
                                                    placeholder="{{ translate('Type Your Taxpayer Identification Number(TIN)') }}"
                                                    class="form-control" value="{{ $restaurant->tin }}">
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="input-label mb-2 d-block title-clr fw-normal"
                                                    for="exampleFormControlInput1">{{ translate('Expire Date') }}</label>
                                                <input type="date" id="tin_expire_date" name="tin_expire_date"
                                                    class="form-control" value="{{ $restaurant->tin_expire_date }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-xxl-3">

                                        <div class="bg--secondary rounded p-20 h-100 single-document-uploaderwrap"
                                            data-document-uploader>
                                            <div class="d-flex align-items-center gap-1 justify-content-between mb-20">
                                                <div>
                                                    <h4 class="mb-1 fz--14px">{{ translate('TIN Certificate') }}</h4>
                                                    <p class="fz-12px mb-0">
                                                        {{ translate('pdf, doc, jpg. File size : max 2 MB') }}</p>
                                                </div>
                                                <div class="d-flex gap-3 align-items-center">
                                                    <button type="button"
                                                        class="doc_edit_btn w-30px h-30 rounded d-flex align-items-center justify-content-center btn--primary btn px-3 icon-btn">
                                                        <img style="max-width:none!important;" src="{{ dynamicAsset('assets/admin/img/edit.svg') }}" alt="">
                                                    </button>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="file-assets"
                                                    data-picture-icon="{{ dynamicAsset('assets/admin/img/picture.svg') }}"
                                                    data-document-icon="{{ dynamicAsset('assets/admin/img/document.svg') }}"
                                                    data-blank-thumbnail="{{ dynamicAsset('assets/admin/img/picture.svg') }}">
                                                </div>
                                                <!-- Upload box -->
                                                <div class="d-flex justify-content-center pdf-container">
                                                    <div class="document-upload-wrapper d-none">
                                                        <input type="file" name="tin_certificate_image"
                                                            class="document_input" accept=".doc, .pdf, .jpg, .png, .jpeg">
                                                        <div class="textbox">
                                                            <img width="40" height="40" class="svg"
                                                                src="{{ dynamicAsset('assets/admin/img/doc-uploaded.png') }}"
                                                                alt="">
                                                            <p class="fs-12 mb-0">{{ translate('Select a file or') }}
                                                                <span
                                                                    class="font-semibold">{{ translate('Drag & Drop') }}</span>
                                                                {{ translate('here') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="pdf-single" data-file-name="${file.name}"
                                                        data-file-url="{{ $restaurant->tin_certificate_image_full_url ?? dynamicAsset('assets/admin/img/upload-cloud.png') }}">
                                                        <div class="pdf-frame">
                                                            @php($imgPath = $restaurant->tin_certificate_image_full_url ?? dynamicAsset('assets/admin/img/upload-cloud.png'))
                                                            @if ($restaurant->tin_certificate_image && !in_array(pathinfo($restaurant->tin_certificate_image, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tif', 'tiff']))
                                                                @php($imgPath = dynamicAsset('assets/admin/img/document.svg'))
                                                            @endif
                                                            <img class="pdf-thumbnail-alt" src="{{ $imgPath }}"
                                                                alt="File Thumbnail">
                                                        </div>
                                                        <div class="overlay">
                                                            <div class="pdf-info">
                                                                @if (Str::endsWith($imgPath, ['.pdf', '.doc', '.docx']))
                                                                    <img src="{{ dynamicAsset('assets/admin/img/document.svg') }}"
                                                                        width="34" alt="File Type Logo">
                                                                @else
                                                                    <img src="{{ dynamicAsset('assets/admin/img/picture.svg') }}"
                                                                        width="34" alt="File Type Logo">
                                                                @endif
                                                                <div class="file-name-wrapper">
                                                                    <span
                                                                        class="file-name js-filename-truncate text-limit-show"
                                                                        data-limit="15">{{ $restaurant->tin_certificate_image }}</span>
                                                                    <span
                                                                        class="opacity-50">{{ translate('Click to view the file') }}</span>
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


                <div class="col-lg-12">
                    <div class="shadow-sm card">
                        <div class="card-header p-3">
                            <h4 class="m-0">
                                {{ translate('Account info') }}
                            </h4>
                            {{-- <p class="m-0 fs-12">Setup your business time zone and format from here</p> --}}
                        </div>
                        <div class="p-3">
                            <div class="bg-light rounded p-space-0 bg-clr-none p-md-3">
                                <div class="row g-3">
                                    <div class="col-md-4 col-12">
                                        <div class="form-group m-0">
                                            <label class="input-label" for="email">{{ translate('messages.email') }}
                                                <span class="text-danger">*</span></label>
                                            <input id="email" type="email" name="email"
                                                class="form-control h--45px"
                                                placeholder="{{ translate('messages.Ex:_Jhone@company.com') }} "
                                                value="{{ $restaurant->email }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <div class="js-form-message form-group">
                                            <label class="input-label"
                                                for="signupSrPassword">{{ translate('messages.password') }}
                                                <span class="input-label-secondary ps-1" data-toggle="tooltip"
                                                    title="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"><i
                                                        class="tio-info text-muted fs-14"></i></span>

                                            </label>

                                            <div class="input-group input-group-merge">
                                                <input type="password" class="js-toggle-password form-control h--45px"
                                                    name="password" id="signupSrPassword"
                                                    placeholder="{{ translate('messages.Ex:_7+_Character') }}"
                                                    aria-label="{{ translate('messages.password_length_7+') }}"
                                                    data-hs-toggle-password-options='{
                                                                                    "target": [".js-toggle-password-target-1"],
                                                                                    "defaultClass": "tio-hidden-outlined",
                                                                                    "showClass": "tio-visible-outlined",
                                                                                    "classChangeTarget": ".js-toggle-passowrd-show-icon-1"
                                                                                    }'>
                                                <div class="js-toggle-password-target-1 input-group-append">
                                                    <a class="input-group-text" href="javascript:;">
                                                        <i class="js-toggle-passowrd-show-icon-1 tio-visible-outlined"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <!-- Password Rules: Hidden initially -->
                                            <ul id="password-rules" class=" gap-1 mt-2 small list-unstyled text-muted"
                                                style="display: none;">
                                                <li>
                                                    <ul class="d-flex flex-wrap gap-1 list-unstyled">
                                                        <li id="rule-length"><i class="text-danger">&#10060;</i>
                                                            {{ translate('At least 8 characters') }}
                                                        </li>
                                                        <li id="rule-lower"><i class="text-danger">&#10060;</i>
                                                            {{ translate('At least one lowercase letter') }}
                                                        </li>
                                                        <li id="rule-upper"><i class="text-danger">&#10060;</i>
                                                            {{ translate('At least one uppercase letter') }}
                                                        </li>
                                                        <li id="rule-number"><i class="text-danger">&#10060;</i>
                                                            {{ translate('At least one number') }}
                                                        </li>
                                                        <li id="rule-symbol"><i class="text-danger">&#10060;</i>
                                                            {{ translate('At least one symbol') }}
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <div class="js-form-message form-group">
                                            <label class="input-label"
                                                for="signupSrConfirmPassword">{{ translate('messages.confirm_password') }}
                                            </label>

                                            <div class="input-group input-group-merge">
                                                <input type="password" class="js-toggle-password form-control h--45px"
                                                    name="confirmPassword" id="signupSrConfirmPassword"
                                                    placeholder="{{ translate('messages.Ex:_7+_Character') }}"
                                                    aria-label="{{ translate('messages.password_length_7+') }}"
                                                    data-hs-toggle-password-options='{
                                                                                    "target": [".js-toggle-password-target-2"],
                                                                                    "defaultClass": "tio-hidden-outlined",
                                                                                    "showClass": "tio-visible-outlined",
                                                                                    "classChangeTarget": ".js-toggle-passowrd-show-icon-2"
                                                                                    }'>

                                            </div>
                                            <!-- Feedback for match/mismatch -->
                                            <small id="confirm-password-feedback" class="text-danger d-none"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="btn--container justify-content-end mt-3">
                <button id="reset_btn" type="button" class="btn btn--reset">{{ translate('messages.reset') }}</button>
                <button type="submit" class="btn btn--primary h--45px"><i class="tio-save"></i>
                    {{  request()->type === 'new_join' ? translate('Edit_&_Approve') : translate('messages.save_information') }}</button>
            </div>
        </form>

    </div>

@endsection

@push('script_2')
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ \App\CentralLogics\Helpers::get_business_settings('map_api_key') }}&loading=async&libraries=drawing,places,marker,geometry&v=3.61&language={{ str_replace('_', '-', app()->getLocale()) }}&callback=initMap">
    </script>

    @php($default_location = \App\CentralLogics\Helpers::get_business_settings('default_location'))

    <script>
        window.mapConfig = {
            mapApiKey: "{{ \App\CentralLogics\Helpers::get_business_settings('map_api_key') }}",
            defaultLocation: {!! json_encode($default_location) !!},
            oldLat: parseFloat("{{ old('latitude', $restaurant->latitude) }}"),
            oldLng: parseFloat("{{ old('longitude', $restaurant->longitude) }}"),
            oldZoneId: "{{ old('zone_id', $restaurant->zone_id) }}",
            oldAddress: "{{ old('address', $restaurant->address) }}",
            translations: {
                selectedLocation: "{{ translate('Selected Location') }}",
                clickMap: "{{ translate('Click_the_map_inside_the_red_marked_area_to_get_Lat/Lng!!!') }}",
                selectZone: "{{ translate('Select_Zone_From_The_Dropdown') }}",
                geolocationError: "{{ translate('Error:_Your_browser_doesnot_support_geolocation.') }}",
                outOfZone: "{{ translate('messages.out_of_coverage') }}",
            },
            urls: {
                zoneCoordinates: "{{ route('admin.zone.get-coordinates', ['id' => ':coordinatesZoneId']) }}",
                zoneGetZone: "{{ route('admin.zone.get-zone') }}",
            }
        };
    </script>

    <script src="{{ dynamicAsset('assets/admin') }}/js/file-preview/pdf.min.js"></script>
    <script src="{{ dynamicAsset('assets/admin') }}/js/file-preview/pdf-worker.min.js"></script>
    <script src="{{ dynamicAsset('assets/admin') }}/js/file-preview/document-upload.js"></script>
    <script src="{{ dynamicAsset('assets/admin') }}/js/view-pages/map-functionality.js"></script>
    <script src="{{ dynamicAsset('assets/admin/js/spartan-multi-image-picker.js') }}"></script>
    <script src="{{ dynamicAsset('assets/admin') }}/js/tags-input.min.js"></script>

    <script>
        "use strict";

        //Clear All Data
        $("#reset_btn").on("click", function() {
            $("#res_form")[0].reset();

            location.reload();
        });

        $("#res_form").on('submit', function(e){
            const pass = $('#signupSrPassword');
            const confirmPass = $('#signupSrConfirmPassword');
            let passDisabled = false;
            let confirmDisabled = false;

            if (pass.val() === '') {
                pass.prop('disabled', true);
                passDisabled = true;
            }
            if (confirmPass.val() === '') {
                confirmPass.prop('disabled', true);
                confirmDisabled = true;
            }

            setTimeout(function(){
                if(passDisabled) pass.prop('disabled', false);
                if(confirmDisabled) confirmPass.prop('disabled', false);
            }, 100);
        });

        // $('#tin_expire_date').attr('min', (new Date()).toISOString().split('T')[0]);

        $(document).ready(function() {
            function previewFile(inputSelector, previewImgSelector, textBoxSelector) {
                const input = $(inputSelector);
                const imagePreview = $(previewImgSelector);
                const textBox = $(textBoxSelector);

                input.on('change', function() {
                    const file = this.files[0];
                    if (!file) return;

                    const fileType = file.type;
                    const validImageTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];

                    if (validImageTypes.includes(fileType)) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imagePreview.attr('src', e.target.result).removeClass('display-none');
                            textBox.hide();
                        };
                        reader.readAsDataURL(file);
                    } else {
                        imagePreview.attr('src',
                                '{{ dynamicAsset('assets/admin/img/file-icon.png') }}')
                            .removeClass('display-none');
                        textBox.hide();
                    }
                });
            }

            previewFile('#tin_certificate_image', '#logoImageViewer2', '.upload-file__textbox');

            $(document).on('click', '.remove-existing-doc', function (e) {
                e.preventDefault();
                const $item = $(this).closest('.pdf-single');
                const key = $(this).data('key');
                const file = $(this).data('file');

                $('<input>').attr({
                    type: 'hidden',
                    name: `removed_additional_documents[${key}][]`,
                    value: file
                }).appendTo('#res_form');

                $item.remove();

                $(window).trigger('resize');
            });

            $(document).on('click', '.pdf-single .pdf-info', function () {
                let $item = $(this).closest('.pdf-single');
                let fileUrl = $item.data('file-url');
                let fileName = $item.data('file-name');

                if (fileUrl) {
                    let ext = fileName.split('.').pop().toLowerCase();
                    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                         $(".image-modal-overlay").remove();
                         let modalHtml = `
                            <div class="image-modal-overlay">
                                <div class="image-modal-content">
                                    <span class="close-modal_img">&times;</span>
                                    <div class="main-image-modal">
                                        <img src="${fileUrl}" alt="Preview Image"/>
                                    </div>
                                </div>
                            </div>
                        `;
                        $("body").append(modalHtml);
                    } else {
                        window.open(fileUrl, '_blank');
                    }
                }
            });

            $(document).on('click', '.close-modal_img', function () {
                $(this).closest('.image-modal-overlay').remove();
            });

            $(document).on('click', '.image-modal-overlay', function (e) {
                if ($(e.target).hasClass('image-modal-overlay')) {
                    $(this).remove();
                }
            });

            $('<style>.file_upload .overlay { display: flex !important; opacity: 0; transition: opacity 0.3s ease; pointer-events: none; } .file_upload.has-image:hover .overlay { opacity: 1; pointer-events: auto; } .view-identity-image { background: #006fbd !important; color: white !important; border-color: #006fbd !important; }</style>').appendTo('head');
        });


        $("#customFileEg1").change(function() {
            readURL(this, 'viewer');
        });

        $("#coverImageUpload").change(function() {
            readURL(this, 'coverImageViewer');
        });
        $('#res_form').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const passwordInput = document.getElementById("signupSrPassword");
            const rulesContainer = document.getElementById("password-rules");

            const rules = {
                length: document.getElementById("rule-length"),
                lower: document.getElementById("rule-lower"),
                upper: document.getElementById("rule-upper"),
                number: document.getElementById("rule-number"),
                symbol: document.getElementById("rule-symbol"),

            };

            passwordInput.addEventListener("input", function() {
                const val = passwordInput.value;

                // Show rules when user types something
                if (val.length > 0) {
                    rulesContainer.style.display = "block";
                } else {
                    rulesContainer.style.display = "none";
                }

                // Update validation rules
                updateRule(rules.length, val.length >= 8);
                updateRule(rules.lower, /[a-z]/.test(val));
                updateRule(rules.upper, /[A-Z]/.test(val));
                updateRule(rules.number, /\d/.test(val));
                updateRule(rules.symbol, /[!@#$%^&*(),.?":{}|<>]/.test(val));

            });

            passwordInput.addEventListener("blur", function() {
                // Optional: hide rules on blur if empty
                if (passwordInput.value.length === 0) {
                    rulesContainer.style.display = "none";
                }
            });

            function updateRule(element, isValid) {
                const icon = element.querySelector("i");
                icon.className = isValid ? "text-success" : "text-danger";
                icon.innerHTML = isValid ? "&#10004;" : "&#10060;"; // ✓ or ✗
            }
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const confirmInput = document.getElementById("signupSrConfirmPassword");
            const passwordInput = document.getElementById("signupSrPassword");
            const feedback = document.getElementById("confirm-password-feedback");

            function validateMatch() {
                if (confirmInput.value.length === 0) {
                    feedback.classList.add("d-none");
                    return;
                }

                if (confirmInput.value === passwordInput.value) {
                    feedback.classList.remove("text-danger");
                    feedback.classList.add("text-success");
                    feedback.textContent = "{{ translate('Passwords match.') }}";
                    feedback.classList.remove("d-none");
                } else {
                    feedback.classList.remove("text-success");
                    feedback.classList.add("text-danger");
                    feedback.textContent = "{{ translate('Confirm password does not match with Password.') }}";
                    feedback.classList.remove("d-none");
                }
            }

            confirmInput.addEventListener("input", validateMatch);
            passwordInput.addEventListener("input", validateMatch); // In case password changes after confirm input
        });
    </script>
    <script src="{{ dynamicAsset('assets/admin/js/file-preview/multiple-document-upload.js') }}"></script>
@endpush
