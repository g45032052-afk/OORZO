@extends('layouts.landing.app')
@section('title', translate('messages.restaurant_registration'))
@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset('assets/admin/css/style.css') }}">
<link href="{{ dynamicAsset('assets/admin/css/tags-input.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ dynamicAsset('assets/landing/css/style.css') }}" />

    {{-- <link rel="stylesheet" href="{{ dynamicAsset('assets/admin/css/select2.min.css') }}"/> --}}
    <style>
        .password-feedback {
            /* display: none; */
            width: 100%;
            margin-top: .25rem;
            font-size: .875em;
            color: #35dc80;
        }
        .valid {
            color: green;
        }

        .invalid {
            color: red;
        }
    </style>
@endpush
@section('content')
    <!-- Page Header Gap -->
    <div class="h-148px"></div>
    <!-- Page Header Gap -->

    <section class="m-0 landing-inline-1 section-gap pt-0">
        <div class="container">
            <!-- Page Header -->
            <div class="step__header">
                <h4 class="title"> {{ translate('messages.Restaurant_registration_application') }}</h4>
                <div class="step__wrapper">
                    <div id="show-step1" class="step__item current">
                        <span class="shapes"></span>
                        {{ translate('General Information') }}
                    </div>
                    <div id="show-step2" class="step__item">
                        <span class="shapes"></span>
                        {{ translate('Business Plan') }}
                    </div>
                    <div class="step__item">
                        <span class="shapes"></span>
                        {{ translate('Complete') }}
                    </div>
                </div>

            </div>
            <!-- End Page Header -->
            @php($language = \App\CentralLogics\Helpers::get_business_settings('language'))

            <form class="global-ajax-form" action="{{ route('restaurant.store') }}" method="post"
                  enctype="multipart/form-data" id="form-id">
                @csrf
                <div id="reg-form-div">
                    <div class="card card-body mb-4">
                         <h4 class="text-capitalize fs-18 mb-3">
                            {{ translate('messages.restaurant_info') }}
                        </h4>
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="bg-light p-3 rounded-10">
                                    @if ($language)
                                        <div class="js-nav-scroller hs-nav-scroller-horizontal overflow-x-auto mb-4">
                                            <ul class="nav nav-tabs flex-nowrap text-nowrap">
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link active" href="#"
                                                       id="default-link">{{ translate('Default') }}</a>
                                                </li>
                                                @foreach ($language??[] as $lang)
                                                    <li class="nav-item">
                                                        <a class="nav-link lang_link" href="#"
                                                           id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    @if ($language)
                                        <div class="form-group lang_form" id="default-form">
                                            <label class="form-label font-regular d-flex gap-1"
                                                   for="exampleFormControlInput1">{{ translate('messages.restaurant_name') }}
                                                ({{ translate('messages.default') }})  <span class="text-danger">
                                                    *</span></label>
                                            <input  type="text" id="default_name" name="name[]"
                                                   value="{{ old('name.0') }}" required
                                                   data-field-name="{{ translate('Default_Restaurant_Name') }}"
                                                   class="form-control"
                                                   placeholder="{{ translate('messages.Ex :_ABC Company') }}" maxlength="191">
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                        @foreach ($language??[] as $key => $lang)
                                            <div class="form-group d-none lang_form" id="{{ $lang }}-form">
                                                <label class="form-label font-regular"
                                                       for="exampleFormControlInput1">{{ translate('messages.restaurant_name') }}
                                                    ({{ strtoupper($lang) }})
                                                </label>
                                                <input type="text" name="name[]" value="{{ old('name.' . $key + 1) }}"
                                                       class="form-control"
                                                       placeholder="{{ translate('messages.Ex :_ABC Company') }}"
                                                       maxlength="191">
                                            </div>
                                            <input type="hidden" name="lang[]" value="{{ $lang }}">
                                        @endforeach
                                    @else
                                        <div class="form-group mb-0">
                                            <label class="form-label font-regular"
                                                   for="exampleFormControlInput1">{{ translate('messages.restaurant_name') }}</label>
                                            <input type="text" name="name[]" class="form-control"
                                                   placeholder="{{ translate('messages.Ex :_ABC Company') }}" maxlength="191">
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    @endif

                                    <div class="lang_form default-form">
                                        <div class="form-group mb-0">
                                            <label class="form-label font-regular d-flex gap-1"
                                                for="address">{{ translate('messages.restaurant_address') }}
                                                ({{ translate('messages.default') }}) <span class="text-danger">
                                                    *</span></label>
                                            <textarea type="text" rows="1" id="address" name="address[]" required
                                                    data-field-name="{{ translate('Default_Restaurant_Address') }}" class="form-control min-h-35px"
                                                    placeholder="{{ translate('messages.restaurant_address') }}">{{ old('address.0') }}</textarea>
                                        </div>
                                    </div>

                                    @if ($language)

                                        @foreach ($language??[] as $key => $lang)
                                            <div class="d-none lang_form" id="{{ $lang }}-form1">
                                                <div class="form-group mb-0">
                                                    <label class="form-label font-regular"
                                                        for="address">{{ translate('messages.restaurant_address') }}
                                                        ({{ strtoupper($lang) }})
                                                    </label>
                                                    <textarea type="text" rows="1" name="address[]" class="form-control min-h-35px"
                                                            placeholder="{{ translate('messages.restaurant_address') }}">{{ old('address.' . $key + 1) }}</textarea>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

                                </div>
                                <div class="form-group">
                                    <label class="form-label font-regular"
                                           for="cuisine">{{ translate('messages.cuisine') }}
                                    </label>
                                    <select name="cuisine_ids[]" id="cuisine"
                                            class="form-control js-select2-custom select2-container--default"
                                            multiple="multiple"
                                            data-placeholder="{{ translate('messages.select_Cuisine') }}">
                                        <option value="" disabled>{{ translate('messages.select_Cuisine') }}
                                        </option>
                                        @foreach (\App\Models\Cuisine::where('status', 1)->get(['id', 'name']) as $cu)
                                            <option value="{{ $cu->id }}">{{ $cu->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-regular d-flex gap-1"
                                           for="choice_zones">{{ translate('messages.zone') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select name="zone_id" id="choice_zones" required
                                            data-field-name="{{ translate('Zone') }}"
                                            class="form-control js-select2-custom select2-container--default"
                                            data-placeholder="{{ translate('messages.select_zone') }}">
                                        <option value="" selected disabled>
                                            {{ translate('messages.select_zone') }}
                                        </option>
                                        @foreach (\App\Models\Zone::active()->get(['id', 'name']) as $zone)
                                            @if (auth('admin')?->user()?->zone_id)
                                                @if (auth('admin')->user()->zone_id == $zone->id)
                                                    <option value="{{ $zone->id }}" selected>{{ $zone->name }}
                                                    </option>
                                                @endif
                                            @else
                                                <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label font-regular d-flex gap-1" for="time">{{ translate('Estimated Delivery Time ( Min & Maximum Time )') }}<span class="text-danger">*</span></label>
                                    <div class="floating--date-inner mb-0 d-flex align-items-center border rounded overflow-hidden">
                                        <div class="item w-100">
                                            <input required type="number" id="minimum_delivery_time" name="minimum_delivery_time"
                                                class="form-control w-100 h--45px border-0 rounded-0" placeholder="{{ translate('messages.Min: 20') }}" pattern="^[0-9]{2}$" required
                                                data-field-name="{{ translate('minimum_delivery_time') }}"
                                                value="{{ old('minimum_delivery_time') }}">
                                        </div>
                                        <div class="item w-100 border-inline-start">
                                            <input required type="number" id="max_delivery_time" name="maximum_delivery_time"
                                                class="form-control w-100 h--45px border-0 rounded-0" placeholder="{{ translate('messages.Max: 30') }}" pattern="[0-9]{2}" required
                                                data-field-name="{{ translate('maximum_delivery_time') }}"
                                                value="{{ old('maximum_delivery_time') }}">
                                        </div>
                                        <div class="item smaller min-w-100px">
                                            <select name="delivery_time_type" required id="delivery_time_type"
                                                class="form-control bg-light h--45px border-0 rounded-0">
                                                    <option selected value="min">{{ translate('messages.minutes') }}</option>
                                                    <option value="hours">{{ translate('messages.hours') }}</option>
                                                </select>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="map_custom-controls position-relative w-100 h-100">
                                    <input id="pac-input" class="controls rounded initial-8" title="{{translate('messages.search_your_location_here')}}" type="text" placeholder="{{translate('messages.search_here')}}"/>
                                    <div class="h-100 min-h-200" id="map"></div>

                                    <div class="d-flex bg-white align-items-center gap-1 laglng-controller">
                                            <div id="outOfZone" class="map-alert bg-dark d-flex align-items-center justify-content-center rounded-8 py-2 px-2 fs-12 text-white mb-2">
                                                <img class="" src="{{dynamicAsset('assets/admin/img/warning-cus.png')}}" onerror="this.style.display='none';" alt="img"> {{ translate('Please place the marker inside the available zones.') }}
                                            </div>
                                            <div id="latlng" class="d-flex">
                                                <input type="" class="border-0 outline-0" id="latitude" name="latitude" placeholder="{{ translate('messages.Ex:_-94.22213') }} " value="{{ old('latitude') }}" required readonly>
                                                <span class="text-gray1">|</span>
                                                <input type="" class="border-0 outline-0" name="longitude" placeholder="{{ translate('messages.Ex:_103.344322') }} "   id="longitude" value="{{ old('longitude') }}" required readonly>
                                            </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-3 p-xxl-4 border rounded-10 h-100">
                                    <div class="mb-4">
                                        <h5 class="fs-14 mb-1">
                                            {{ translate('Restaurant_Logo') }} <span class="text-danger">*</span>
                                        </h5>
                                        <p class="mb-0 fs-12 text-muted">{{ translate('Upload your website Logo') }}
                                        </p>
                                    </div>
                                    <div class="text-center">
                                        @include('partials._image-uploader', [
                                            'id' => 'image-input',
                                            'name' => 'logo',
                                            'ratio' => '1:1',
                                            'isRequired' => true,
                                            'existingImage' => null,
                                            'imageExtension' => IMAGE_EXTENSION,
                                            'imageFormat' => IMAGE_FORMAT,
                                            'maxSize' => MAX_FILE_SIZE,
                                        ])
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-3 p-xxl-4 border rounded-10 h-100">
                                    <div class="mb-4">
                                        <h5 class="fs-14 mb-1">
                                            {{ translate('Restaurant_Cover') }} <span class="text-danger">*</span>
                                        </h5>
                                        <p class="mb-0 fs-12 text-muted">{{ translate('Upload your Business Cover') }}
                                        </p>
                                    </div>
                                    <div class="text-center">
                                        @include('partials._image-uploader', [
                                            'id' => 'image-input-cover',
                                            'name' => 'cover_photo',
                                            'ratio' => '2:1',
                                            'isRequired' => true,
                                            'existingImage' => null,
                                            'imageExtension' => IMAGE_EXTENSION,
                                            'imageFormat' => IMAGE_FORMAT,
                                            'maxSize' => MAX_FILE_SIZE,
                                        ])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-body mb-4">
                        <h4 class="text-capitalize fs-18 mb-3">
                            {{ translate('messages.restaurant_info') }}
                        </h4>
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="form-group mb-0">
                                    <label class="form-label font-regular d-flex gap-1"
                                        for="f_name">{{ translate('messages.first_name') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="f_name" name="f_name" class="form-control"
                                           placeholder="{{ translate('messages.first_name') }}"
                                           value="{{ old('f_name') }}" data-field-name="{{ translate('first_name') }}"
                                           required>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group mb-0">
                                    <label class="form-label font-regular d-flex gap-1"
                                        for="l_name">{{ translate('messages.last_name') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="l_name" name="l_name" class="form-control"
                                           data-field-name="{{ translate('last_name') }}"
                                           placeholder="{{ translate('messages.last_name') }}"
                                           value="{{ old('l_name') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group mb-0">
                                    <label class="form-label font-regular d-flex gap-1"
                                           for="phone">{{ translate('messages.phone') }}<span class="text-danger">
                                            *</span></label>
                                    <input type="tel" id="phone" name="phone" class="form-control"
                                           data-field-name="{{ translate('phone') }}"
                                           placeholder="{{ translate('messages.Ex :') }} 017********"
                                           value="{{ old('phone') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card card-body mb-4">
                        <h4 class="text-capitalize fs-18 mb-3">
                            {{ translate('messages.Tags') }}
                        </h4>
                        <div>
                            <label class="form-label font-regular" for="tags">{{ translate('messages.tags') }}</label>
                            <input type="text" class="form-control" name="tags" placeholder="{{translate('messages.Enter tags')}}"
                                    data-role="tagsinput">
                        </div>
                    </div>

                    @include('partials._custom-inputs')

                    <div class="card card-body mb-4">
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <h4 class="text-capitalize font-medium mb-3">
                                    {{ translate('messages.Business TIN') }}
                                </h4>
                                <div class="form-group">
                                    <label class="form-label font-regular d-flex gap-1" for="exampleFormControlInput1">
                                        {{translate('Taxpayer Identification Number(TIN)')}}
                                        <span class="text-danger">*</span>
                                    </label>
                                    </label>
                                    <input type="text" id="tin" name="tin" placeholder="{{translate('Type Your Taxpayer Identification Number(TIN)')}}" class="form-control" required>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label font-regular d-flex gap-1" for="exampleFormControlInput1">
                                        {{translate('Expire Date')}} <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" id="tin_expire_date" name="tin_expire_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="h-100 single-document-uploaderwrap" data-document-uploader>
                                    <div class="text-center mb-4">
                                        <h4 class="fs-14 mb-2">{{translate('TIN Certificate')}}</h4>
                                        <p class="fs-12 lh-1 mb-0">{{translate('pdf, doc, jpg. File size : max 2 MB')}}</p>
                                    </div>
                                    <div>
                                        <div class="file-assets"
                                             data-picture-icon="{{ dynamicAsset('assets/admin/img/picture.svg') }}"
                                             data-document-icon="{{ dynamicAsset('assets/admin/img/document.svg') }}"
                                             data-blank-thumbnail="{{ dynamicAsset('assets/admin/img/picture.svg') }}">
                                        </div>
                                        <!-- Upload box -->
                                        <div class="position-relative max-w-280 mx-auto">
                                            <div class="d-flex justify-content-center pdf-container">
                                                <div class="doc_edit_btn_wrapper d--none">
                                                    <button type="button" class="doc_edit_btn m-0 w-30px h-30px rounded d-flex align-items-center justify-content-center px-3 icon-btn">
                                                        <i class="tio-edit"></i>
                                                    </button>
                                                </div>
                                                <div class="document-upload-wrapper">
                                                    <input type="file" name="tin_certificate_image" class="document_input" accept=".doc, .pdf, .jpg, .png, .jpeg">
                                                    <div class="textbox">
                                                        <img width="40" height="40" class="svg"
                                                             src="{{ dynamicAsset('assets/admin/img/doc-uploaded.png') }}"
                                                             alt="">
                                                        <p class="fs-12 mb-0">{{ translate('Select a file or') }} <span class="font-semibold">{{ translate('Drag & Drop') }}</span>
                                                            {{ translate('here') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="card card-body">
                        <h4 class="text-capitalize fs-18 mb-3">
                            {{ translate('messages.login_info') }}
                        </h4>
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="form-group mb-0">
                                    <label class="form-label font-regular d-flex gap-1"
                                           for="email">{{ translate('messages.email') }} <span class="text-danger">
                                            *</span></label>
                                    <input type="email" id="email" name="email"
                                           data-field-name="{{ translate('messages.Email') }}"
                                           class="form-control __form-control"
                                           placeholder="{{ translate('messages.Ex:') }} ex@example.com"
                                           value="{{ old('email') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group mb-0">
                                    <label class="form-label font-regular d-flex gap-1"
                                        title="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"
                                        for="exampleInputPassword">{{ translate('messages.password') }}  <span class="text-danger">
                                        *</span>
                                    </label>
                                    <label class="position-relative m-0 d-block">
                                        <input type="password" name="password"
                                               placeholder="{{ translate('messages.password_length_placeholder', ['length' => '7+']) }}"
                                               class="form-control __form-control form-control __form-control-user"
                                               minlength="6" id="passwordWithRules" required
                                               data-field-name="{{ translate('messages.password') }}"
                                               value="{{ old('password') }}">
                                        <span class="show-password">
                                        <span class="icon-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                 viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                 class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </span>
                                        <span class="icon-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                 viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                 class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                            </svg>
                                        </span>
                                    </span>
                                    </label>
                                    <div id="password-feedback" class="pass d-none password-feedback">
                                        {{ translate('messages.password_not_matched') }}</div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group mb-0">
                                    <label class="form-label font-regular d-flex gap-1"
                                           for="exampleRepeatPassword">{{ translate('messages.confirm_password') }}  <span class="text-danger">
                                            *</span></label>
                                    <label class="position-relative m-0 d-block">
                                        <input type="password" name="confirm-password"
                                               class="form-control __form-control form-control __form-control-user"
                                               minlength="6" id="exampleRepeatPassword"
                                               placeholder="{{ translate('messages.password_length_placeholder', ['length' => '7+']) }}"
                                               data-field-name="{{ translate('messages.confirm_password') }}"
                                               required value="{{ old('confirm-password') }}">
                                        <span class="show-password">
                                        <span class="icon-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                 viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                 class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </span>
                                        <span class="icon-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                 viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                 class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                            </svg>
                                        </span>
                                    </span>
                                    </label>
                                    <div class="pass invalid-feedback">
                                        {{ translate('messages.password_not_matched') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end pt-4 d-flex flex-wrap justify-content-end gap-3">
                        <button type="reset" id='reset-btn'
                                class="btn btn--reset ">{{ translate('Reset') }}</button>

                        @if (\App\CentralLogics\Helpers::subscription_check() != 1)
                            <button  type="submit" class="btn btn--primary submitBtn">{{ translate('Next') }}</button>
                        @else
                            <button  type="button" id="show-business-plan-div" class="btn btn--primary submitBtn">{{ translate('Next') }}</button>
                        @endif
                    </div>
                </div>
                @if (\App\CentralLogics\Helpers::subscription_check())
                    <div class="d-none" id="business-plan-div">
                        <h4 class="register--title text-center mb-40px"> {{ translate('messages.business_plans') }}</h4>

                        <div class="card __card mb-3">
                            <h4 class="card-title text-center pt-4">
                                @if (count($packages) > 0 && \App\CentralLogics\Helpers::commission_check())
                                    {{ translate('Choose Your Business Plan') }}
                                @elseif (!count($packages) && !\App\CentralLogics\Helpers::commission_check())
                                    {{ translate('No business plan is available') }}
                                @else
                                    {{ translate('Your Business Plan') }}
                                @endif
                            </h4>
                            <div class="card-body mb-2 p-4">
                                <div class="row g-4">
                                    @if (\App\CentralLogics\Helpers::commission_check())
                                        <div class="col-sm-6">
                                            <label class="plan-check-item">
                                                <input type="radio" name="business_plan" value="commission-base"
                                                       class="d-none" checked>
                                                <div class="plan-check-item-inner">
                                                    <h5>{{ translate('Commision_Base') }}</h5>
                                                    <p>
                                                        {{ translate('restaurant will pay') }}
                                                        {{ $admin_commission }}% {{ translate('commission to') }}
                                                        {{ $business_name }}
                                                        {{ translate('from each order. You will get access of all the features and options  in restaurant panel , app and interaction with user.') }}
                                                    </p>
                                                </div>
                                            </label>
                                        </div>
                                    @endif
                                    @if (count($packages) > 0)
                                        <div class="col-sm-6">
                                            <label class="plan-check-item">
                                                <input type="radio" name="business_plan" value="subscription-base" {{ !\App\CentralLogics\Helpers::commission_check() ? 'checked' : ''  }}
                                                class="d-none">
                                                <div class="plan-check-item-inner">
                                                    <h5>{{ translate('Subscription Base') }}</h5>
                                                    <p>
                                                        {{ translate('Run restaurant by puchasing subsciption packages. You will have access the features of in restaurant panel , app and interaction with user according to the subscription packages.') }}
                                                    </p>
                                                </div>
                                            </label>
                                        </div>
                                    @endif



                                    @if ( !\App\CentralLogics\Helpers::commission_check() && !count($packages) )
                                        <div class="col-12">
                                            <div class="empty--data text-center">
                                                <img src="{{dynamicAsset('assets/admin/img/empty.png')}}" alt="public">
                                                <h5>
                                                </h5>
                                            </div>
                                        </div>
                                    @endif




                                </div>
                                <div id="subscription-plan">
                                    <br>
                                    <h4 class="card-title text-center">
                                        {{ translate('Choose Subscription Package') }}
                                    </h4>
                                    <div class="card-body">
                                        <div class="plan-slider owl-theme owl-carousel owl-refresh">

                                            @forelse ($packages as $key=> $package)
                                                <label
                                                    class="__plan-item {{ count($packages) > 4 &&  $key == 2 ||( count($packages) < 5 &&  $key == 1) || count($packages) == 1 ? 'active' : '' }} ">
                                                    <input type="radio" name="package_id" {{ count($packages) > 4 &&  $key == 2 ||( count($packages) < 5 &&  $key == 1) || count($packages) == 1 ? 'checked' : '' }}  value="{{ $package->id }}"  class="d-none">
                                                    <div class="inner-div">
                                                        <div class="text-center">

                                                            <h3 class="title">{{ $package->package_name }}</h3>
                                                            <h2 class="price">
                                                                {{ \App\CentralLogics\Helpers::format_currency($package->price) }}
                                                            </h2>
                                                            <div class="day-count">{{ $package->validity }}
                                                                {{ translate('messages.days') }}</div>
                                                        </div>
                                                        <ul class="info">

                                                            @if ($package->pos)
                                                                <li>
                                                                    <img src="{{ dynamicAsset('assets/landing/img/check-1.svg') }}"
                                                                         class="check" alt="">
                                                                    <img src="{{ dynamicAsset('assets/landing/img/check-2.svg') }}"
                                                                         class="check-white" alt=""> <span>
                                                                            {{ translate('messages.POS') }} </span>
                                                                </li>
                                                            @endif
                                                            @if ($package->mobile_app)
                                                                <li>
                                                                    <img src="{{ dynamicAsset('assets/landing/img/check-1.svg') }}"
                                                                         class="check" alt="">
                                                                    <img src="{{ dynamicAsset('assets/landing/img/check-2.svg') }}"
                                                                         class="check-white" alt=""> <span>
                                                                            {{ translate('messages.mobile_app') }} </span>
                                                                </li>
                                                            @endif
                                                            @if ($package->chat)
                                                                <li>
                                                                    <img src="{{ dynamicAsset('assets/landing/img/check-1.svg') }}"
                                                                         class="check" alt="">
                                                                    <img src="{{ dynamicAsset('assets/landing/img/check-2.svg') }}"
                                                                         class="check-white" alt=""> <span>
                                                                            {{ translate('messages.chatting_options') }}
                                                                        </span>
                                                                </li>
                                                            @endif
                                                            @if ($package->review)
                                                                <li>
                                                                    <img src="{{ dynamicAsset('assets/landing/img/check-1.svg') }}"
                                                                         class="check" alt="">
                                                                    <img src="{{ dynamicAsset('assets/landing/img/check-2.svg') }}"
                                                                         class="check-white" alt=""> <span>
                                                                            {{ translate('messages.review_section') }} </span>
                                                                </li>
                                                            @endif
                                                            @if ($package->self_delivery)
                                                                <li>
                                                                    <img src="{{ dynamicAsset('assets/landing/img/check-1.svg') }}"
                                                                         class="check" alt="">
                                                                    <img src="{{ dynamicAsset('assets/landing/img/check-2.svg') }}"
                                                                         class="check-white" alt=""> <span>
                                                                            {{ translate('messages.self_delivery') }} </span>
                                                                </li>
                                                            @endif
                                                            @if ($package->max_order == 'unlimited')
                                                                <li>
                                                                    <img src="{{ dynamicAsset('assets/landing/img/check-1.svg') }}"
                                                                         class="check" alt="">
                                                                    <img src="{{ dynamicAsset('assets/landing/img/check-2.svg') }}"
                                                                         class="check-white" alt=""> <span>
                                                                            {{ translate('messages.Unlimited_Orders') }}
                                                                        </span>
                                                                </li>
                                                            @else
                                                                <li>
                                                                    <img src="{{ dynamicAsset('assets/landing/img/check-1.svg') }}"
                                                                         class="check" alt="">
                                                                    <img src="{{ dynamicAsset('assets/landing/img/check-2.svg') }}"
                                                                         class="check-white" alt=""> <span>
                                                                            {{ $package->max_order }}
                                                                        {{ translate('messages.Orders') }} </span>
                                                                </li>
                                                            @endif
                                                            @if ($package->max_product == 'unlimited')
                                                                <li>
                                                                    <img src="{{ dynamicAsset('assets/landing/img/check-1.svg') }}"
                                                                         class="check" alt="">
                                                                    <img src="{{ dynamicAsset('assets/landing/img/check-2.svg') }}"
                                                                         class="check-white" alt=""> <span>
                                                                            {{ translate('messages.Unlimited_uploads') }}
                                                                        </span>
                                                                </li>
                                                            @else
                                                                <li>
                                                                    <img src="{{ dynamicAsset('assets/landing/img/check-1.svg') }}"
                                                                         class="check" alt="">
                                                                    <img src="{{ dynamicAsset('assets/landing/img/check-2.svg') }}"
                                                                         class="check-white" alt=""> <span>
                                                                            {{ $package->max_product }}
                                                                        {{ translate('messages.uploads') }} </span>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </label>

                                            @empty
                                            @endforelse

                                        </div>
                                    </div>
                                </div>
                                <div class="text-end mt-4 d-flex flex-wrap justify-content-end gap-3">
                                    <button type="button" id="back-to-form"
                                            class="btn btn--reset">{{ translate('Back') }}</button>
                                    <button type="submit" {{ !\App\CentralLogics\Helpers::commission_check() && !count($packages) ? 'disabled'  : ''}}
                                    class="btn btn--primary submitBtn">{{ translate('Next') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </section>
    <!-- Page Header Gap -->
    <div class="h-148px"></div>
    <!-- Page Header Gap -->

@endsection
@push('script_2')
    <script src="{{ dynamicAsset('assets/admin/js/view-pages/common.js') }}"></script>

    <script src="{{ dynamicAsset('assets/admin') }}/js/file-preview/document-upload.js"></script>
    <script src="{{ dynamicAsset('assets/admin') }}/js/file-preview/pdf.min.js"></script>
    <script src="{{ dynamicAsset('assets/admin') }}/js/file-preview/pdf-worker.min.js"></script>
    <script src="{{ dynamicAsset('assets/admin') }}/js/file-preview/multiple-document-upload.js"></script>

    <script src="{{ dynamicAsset('assets/admin') }}/js/view-pages/map-functionality.js"></script>


    <script src="{{ dynamicAsset('assets/admin') }}/js/tags-input.min.js"></script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ \App\CentralLogics\Helpers::get_business_settings('map_api_key') }}&libraries=drawing,places,marker,geometry&v=3.61&language={{ str_replace('_', '-', app()->getLocale()) }}&callback=initMap"
        async defer>
    </script>


    @php($default_location = \App\Models\BusinessSetting::where('key', 'default_location')->first())
    @php($default_location = $default_location->value ? json_decode($default_location->value, true) : 0)

    <script>
        window.mapConfig = {
            mapApiKey: "{{ \App\CentralLogics\Helpers::get_business_settings('map_api_key') }}",
            defaultLocation: {!! json_encode($default_location) !!},
            oldLat: parseFloat("{{ old('latitude') }}"),
            oldLng: parseFloat("{{ old('longitude') }}"),
            oldZoneId: "{{ old('zone_id') }}",
            oldAddress: @json(old('address.0')),
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

    <script>
        "use strict";

        document.querySelectorAll('input').forEach(function (input) {
            input.addEventListener('invalid', function (e) {
                e.preventDefault();
                const fieldName = input.getAttribute('data-field-name') || input.name || 'This field';
                toastr.error(`${fieldName} is invalid or required`, {
                    CloseButton: true,
                    ProgressBar: true
                });
            });
        });

        $('#tin_expire_date').attr('min', (new Date()).toISOString().split('T')[0]);

        $(".lang_link").click(function(e) {
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang_form").addClass('d-none');
            $(this).addClass('active');
            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);
            $("#" + lang + "-form").removeClass('d-none');
            $("#" + lang + "-form1").removeClass('d-none');
            if (lang === "default") {
                $(".default-form").removeClass("d-none");
            }
        });


        $('select').select2({
            width: '100%',
            placeholder: "Select an Option",
            allowClear: true
        });
    </script>
    <script src="{{ dynamicAsset('assets/admin/js/select2.min.js') }}"></script>

    <script>
        $('#passwordWithRules, #exampleRepeatPassword').on('keyup', function () {
        let pass = $("#passwordWithRules").val();
        let passRepeat = $("#exampleRepeatPassword").val();

        if (passRepeat.length > 0 && pass !== passRepeat) {
            $('.pass').show();
        } else {
            $('.pass').hide();
        }
    });
        $('#show-business-plan-div').on('click', function(e) {
            e.preventDefault();
            const maxFileSize = 2097152; // 2MB in bytes
            const requiredFields = $('input[required]');
            let isValid = true;
            requiredFields.each(function() {
                if ($(this).val().trim() === '') {
                    var fieldName = $(this).attr('data-field-name');

                    if (fieldName) {
                        toastr.error(fieldName + " {{ translate('field is required') }}");
                        isValid = false;
                        return false;
                    }
                }
            });

            if (isValid) {
                $.get({
                    url: '{{ route('admin.zone.check-location') }}',
                    dataType: 'json',
                    data: {
                        zone_id: $('#choice_zones').val(),
                        latitude: $('#latitude').val(),
                        longitude: $('#longitude').val()
                    },
                    beforeSend: function () {
                        $('#loading').show();
                    },
                    success: function (data) {
                        $('#loading').hide();
                        if (data.errors) {
                            for (let i = 0; i < data.errors.length; i++) {
                                toastr.error(data.errors[i].message, {
                                    CloseButton: true,
                                    ProgressBar: true
                                });
                            }
                        } else {
                            $('#business-plan-div').removeClass('d-none');
                            $('#reg-form-div').addClass('d-none');
                            $('#show-step2').addClass('current');
                            $('#show-step1').removeClass('current').addClass('active');
                            $(window).scrollTop(0);

                            @if (isset($recaptcha) && $recaptcha['status'] == 1)
                                if (typeof grecaptcha === 'undefined') {
                                    toastr.error('Invalid recaptcha key provided. Please check the recaptcha configuration.');
                                    return;
                                }
                                grecaptcha.ready(function () {
                                    grecaptcha.execute('{{$recaptcha['site_key']}}', {action: 'submit'}).then(function (token) {
                                        $('#g-recaptcha-response').value = token;

                                    });
                                });
                                window.onerror = function (message) {
                                    var errorMessage = 'An unexpected error occurred. Please check the recaptcha configuration';
                                    if (message.includes('Invalid site key')) {
                                        errorMessage = 'Invalid site key provided. Please check the recaptcha configuration.';
                                    } else if (message.includes('not loaded in api.js')) {
                                        errorMessage = 'reCAPTCHA API could not be loaded. Please check the recaptcha API configuration.';
                                    }
                                    toastr.error(errorMessage)
                                    return true;
                                };
                            @endif
                        }
                    },
                    error: function (xhr) {
                        $('#loading').hide();
                        var message = "{{ translate('messages.failed_to_check_zone') }}";
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                message = response.message;
                            } else if (response.errors && response.errors[0] && response.errors[0].message) {
                                message = response.errors[0].message;
                            }
                        } catch (e) {}
                        toastr.error(message);
                    }
                });
            }
        });

        $('#back-to-form').on('click', function() {
            $('#business-plan-div').addClass('d-none');
            $('#reg-form-div').removeClass('d-none');
            $('#show-step1').addClass('current').removeClass('active') ;
            $('#show-step2').removeClass('current');
            $(window).scrollTop(0);
        })
        $("#form-id").on('submit', function(e) {
            const radios = document.querySelectorAll('input[name="business_plan"]');
            let selectedValue = null;

            for (const radio of radios) {
                if (radio.checked) {
                    selectedValue = radio.value;
                    break;
                }
            }

            if (selectedValue === 'subscription-base') {
                const package_radios = document.querySelectorAll('input[name="package_id"]');
                let selectedpValue = null;
                for (const pradio of package_radios) {
                    if (pradio.checked) {
                        selectedpValue = pradio.value;
                        break;
                    }
                }

                if (!selectedpValue) {
                    toastr.error("{{ translate('You_must_select_a_package') }}");
                    e.preventDefault();
                }
            }

        });

        $('.plan-slider').owlCarousel({
            loop: false,
            margin: 0,
            responsiveClass: true,
            nav:false,
            dots:false,
            items: 3,
            startPosition: 0,

            responsive: {
                0: {
                    items: 1.1,
                },
                375: {
                    items: 1.3,
                },
                576: {
                    items: 1.7,
                },
                768: {
                    items: 2.2,
                },
                992: {
                    items: 3,
                },
                1200: {
                    items: 4,
                }
            }
        })

        $(window).on('load', function() {
            $('input[name="business_plan"]').each(function() {
                if ($(this).is(':checked')) {
                    if ($(this).val() == 'subscription-base') {
                        $('#subscription-plan').show()
                    } else {
                        $('#subscription-plan').hide()
                    }
                }
            })
            $('input[name="package_id"]').each(function() {
                if ($(this).is(':checked')) {
                    $(this).closest('.__plan-item').addClass('active')
                }
            })
        })
        $('input[name="business_plan"]').on('change', function() {
            if ($(this).val() == 'subscription-base') {
                $('#subscription-plan').slideDown()
            } else {
                $('#subscription-plan').slideUp()
            }
        })
        $('input[name="package_id"]').on('change', function() {
            $('input[name="package_id"]').each(function() {
                $(this).closest('.__plan-item').removeClass('active')
            })
            $(this).closest('.__plan-item').addClass('active')
        })
        $('#reset-btn').on('click', function() {
            location.reload()
        })

    </script>
    <script>
        $(document).on('change', '.single-document-uploaderwrap .document_input', function () {
            const $wrapper = $(this).closest('.pdf-container');
            const $editBtn = $wrapper.find('.doc_edit_btn_wrapper');

            if (this.files && this.files.length > 0) {
                $editBtn.show();
            } else {
                $editBtn.hide();
            }
        });
    </script>
@endpush
