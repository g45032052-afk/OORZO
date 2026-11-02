@php use App\CentralLogics\Helpers; @endphp
@extends('layouts.admin.app')

@section('title', translate('messages.landing_page_settings'))
@section('content')

    <div class="content container-fluid">
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-start">
                <h1 class="page-header-title fs-24 text-capitalize">
                    <div class="card-header-icon d-inline-flex mr-2 img">
                        <img src="{{ dynamicAsset('assets/admin/img/landing-page.png') }}" class="mw-26px"
                            alt="public">
                    </div>
                    <span>
                        {{ translate('React_Landing_Page') }}
                    </span>
                </h1>
            </div>
        </div>
        <div class="mb-15">
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
        </div>

        <div class="card">
            <div class="card-header">
                <div class="">
                    <h3 class="mb-1">{{ translate('Header_Section') }}</h3>
                     <p class="mb-0 gray-dark fs-12">{{translate('Manage main banner content including title, subtitle, and background image')}}</p>
                </div>
            </div>
            <div class="card-body p-xl-20 p-3">
                <div class="card-custom-xl mb-10px">
                    <form class="validate-form" action="{{ route('admin.react_landing_page.settings', 'react-header') }}" method="POST"
                        enctype="multipart/form-data">

                        <div class="row g-3">
                            <div class="col-lg-8">
                                <div class="bg-light2 p-xl-20 p-3 rounded">
                                    @csrf
                                    @if ($language)
                                        <ul class="nav nav-tabs mb-4 border-">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link active" href="#"
                                                    id="default-link">{{ translate('messages.default') }}</a>
                                            </li>
                                            @foreach ($language as $lang)
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link" href="#"
                                                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    <div class="lang_form default-form">
                                        <div class="row g-3">
                                            <input type="hidden" name="lang[]" value="default">
                                            <div class="col-md-12">
                                                <label for="react_header_title"
                                                    class="form-label fw-400">{{ translate('Title') }}
                                                    ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                    <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ translate('This is the main website headline, keep it short and impactful. Write it under 50 characters') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                </label>
                                                <input required id="react_header_title" data-maxlength="50" type="text"
                                                    name="react_header_title[]" class="form-control"
                                                    placeholder="{{ translate('Ex:_John') }}"
                                                    value="{{ $react_header_title?->getRawOriginal('value') ?? '' }}">
                                                <div class="d-flex justify-content-end">
                                                    <span class="text-body-light text-right d-block mt-1">0/50</span>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="react_header_sub_title"
                                                    class="form-label fw-400">{{ translate('messages.Subtitle') }}
                                                    ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                    <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ translate('This is the supporting text that explains your service. Write it under 100 characters') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                </label>
                                                <input required id="react_header_sub_title" type="text"
                                                    name="react_header_sub_title[]"
                                                    placeholder="{{ translate('Very_Good_Company') }}" data-maxlength="100"
                                                    class="form-control"
                                                    value="{{ $react_header_sub_title?->getRawOriginal('value') ?? '' }}">
                                                <span class="text-body-light text-right d-block mt-1">0/100</span>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($language)
                                        @forelse($language as $lang)
                                            <div class="col-lg-12 d-none lang_form" id="{{ $lang }}-form">
                                                <?php
                                                if ($react_header_title?->translations) {
                                                    $react_header_title_translate = [];
                                                    foreach ($react_header_title->translations as $t) {
                                                        if ($t->locale == $lang && $t->key == 'react_header_title') {
                                                            $react_header_title_translate[$lang]['value'] = $t->value;
                                                        }
                                                    }
                                                }
                                                if ($react_header_sub_title?->translations) {
                                                    $react_header_sub_title_translate = [];
                                                    foreach ($react_header_sub_title->translations as $t) {
                                                        if ($t->locale == $lang && $t->key == 'react_header_sub_title') {
                                                            $react_header_sub_title_translate[$lang]['value'] = $t->value;
                                                        }
                                                    }
                                                }

                                                ?>
                                                <div class="row g-3">
                                                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                    <div class="col-md-12">
                                                        <label for="react_header_title{{ $lang }}"
                                                            class="form-label fw-400">{{ translate('Title') }}
                                                            ({{ strtoupper($lang) }})
                                                            <span class="input-label-secondary text--title"
                                                                data-toggle="tooltip" data-placement="right"
                                                                data-original-title="{{ translate('This is the main website headline, keep it short and impactful. Write it under 50 characters') }}">
                                                                <i class="tio-info text-gray1 fs-16"></i>
                                                            </span>
                                                        </label>
                                                        <input id="react_header_title{{ $lang }}"
                                                            data-maxlength="50" type="text"
                                                            name="react_header_title[]" class="form-control"
                                                            placeholder="{{ translate('Ex:_John') }}"
                                                            value="{{ $react_header_title_translate[$lang]['value'] ?? '' }}">
                                                        <span class="text-body-light text-right d-block mt-1">0/50</span>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <label for="react_header_sub_title{{ $lang }}"
                                                            class="form-label fw-400">{{ translate('messages.Subtitle') }}
                                                            ({{ strtoupper($lang) }})
                                                            <span class="input-label-secondary text--title"
                                                                data-toggle="tooltip" data-placement="right"
                                                                data-original-title="{{ translate('This is the supporting text that explains your service. Write it under 100 characters') }}">
                                                                <i class="tio-info text-gray1 fs-16"></i>
                                                            </span>
                                                        </label>
                                                        <input id="react_header_sub_title{{ $lang }}"
                                                            type="text" data-maxlength="100"
                                                            name="react_header_sub_title[]"
                                                            placeholder="{{ translate('Very_Good_Company') }}"
                                                            class="form-control"
                                                            value="{{ $react_header_sub_title_translate[$lang]['value'] ?? '' }}">
                                                        <span class="text-body-light text-right d-block mt-1">0/100</span>

                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                        @endforelse
                                    @endif
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="p-xxl-20 d-flex align-items-center justify-content-center p-12 global-bg-box text-center rounded h-100">
                                    <div class="">
                                        <div class="mb-xxl-5 mb-xl-4 mb-3 text-start">
                                            <h5 class="mb-1">{{ translate('Section Background Image') }} <span class="text-danger">*</span></h5>
                                            <p class="mb-0 fs-12 gray-dark">
                                                {{ translate('Upload your section background image.') }}</p>
                                        </div>
                                        @include('admin-views.partials._image-uploader', [
                                            'id' => 'image-input',
                                            'name' => 'react_header_image',
                                            'ratio' => '3:1',
                                            'isRequired' => $react_header_image?->value ? false : true,
                                            'existingImage' => $react_header_image?->value ? Helpers::get_full_url('react_header', $react_header_image?->value, $react_header_image?->storage[0]?->value ?? 'public', 'upload_1_1') : null,
                                            'imageExtension' => IMAGE_EXTENSION,
                                            'imageFormat' => IMAGE_FORMAT,
                                            'maxSize' => MAX_FILE_SIZE,
                                        ])
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




                <div class="card-custom-xl mb-10px">
                    <form class="validate-form" action="{{ route('admin.react_landing_page.settings', 'react-header-location-picker') }}"
                        method="post">
                        @method('post')
                        @csrf
                        <div class="mb-20">
                            <h4 class="mb-1">{{ translate('location_picker_section') }}</h4>
                             <p class="mb-0 gray-dark fs-12">{{translate('Customize location search bar and placeholder text to help users find nearby restaurants.')}}</p>
                        </div>
                        <div class="bg-light2 p-xl-20 p-3 rounded">
                            @if ($language)
                                <ul class="nav nav-tabs mb-4 border-0">
                                    <li class="nav-item">
                                        <a class="nav-link lang_link1 active" href="#"
                                            id="default-link1">{{ translate('messages.default') }}</a>
                                    </li>
                                    @foreach ($language as $lang)
                                        <li class="nav-item">
                                            <a class="nav-link lang_link1" href="#"
                                                id="{{ $lang }}-link1">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            <div class="lang_form-float default-form-float">
                                <div class="row g-3">
                                    <input type="hidden" name="lang[]" value="default">
                                    <div class="col-md-12">
                                        <label for="header_location_picker_title"
                                            class="form-label fw-400">{{ translate('Placeholder') }}
                                            ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                            <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ translate('This text appears as the label or heading above the location search bar. Write it under 50 characters.') }}">
                                                <i class="tio-info text-gray1 fs-16"></i>
                                            </span>
                                        </label>
                                        <input required id="header_location_picker_title" data-maxlength="50" type="text"
                                            name="header_location_picker_title[]" class="form-control"
                                            placeholder="{{ translate('Ex:') }}"
                                            value="{{ $header_location_picker_title?->getRawOriginal('value') ?? '' }}">
                                        <div class="d-flex justify-content-end">
                                            <span class="text-body-light text-right d-block mt-1">0/50</span>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            @forelse($language as $lang)
                                <?php
                                if ($header_location_picker_title?->translations) {
                                    $header_location_picker_title_translate = [];
                                    foreach ($header_location_picker_title->translations as $t) {
                                        if ($t->locale == $lang && $t->key == 'header_location_picker_title') {
                                            $header_location_picker_title_translate[$lang]['value'] = $t->value;
                                        }
                                    }
                                }

                                ?>

                                <div class="d-none lang_form-float" id="{{ $lang }}-form-float">
                                    <div class="row g-3">
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                        <div class="col-md-12">
                                            <label for="header_location_picker_title{{ $lang }}"
                                                class="form-label fw-400">{{ translate('Placeholder') }}
                                                ({{ strtoupper($lang) }})
                                                <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                    data-placement="right"
                                                    data-original-title="{{ translate('This text appears as the label or heading above the location search bar. Write it under 50 characters.') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                            </label>
                                            <input id="header_location_picker_title{{ $lang }}" data-maxlength="50"
                                                type="text" name="header_location_picker_title[]"
                                                value="{{ $header_location_picker_title_translate[$lang]['value'] ?? '' }}"
                                                class="form-control" placeholder="{{ translate('Ex:') }}"
                                                value="">
                                            <div class="d-flex justify-content-end">
                                                <span class="text-body-light text-right d-block mt-1">0/50</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="btn--container justify-content-end mt-4">
                            <button type="reset" class="btn btn--reset">{{ translate('Reset') }}</button>
                            <button type="submit" class="btn btn--primary">{{ translate('Save') }}</button>
                        </div>

                    </form>
                </div>
                <div class="card-custom-xl">
                    <div class="mb-20">
                        <h4 class="mb-1">{{ translate('Business Statistics Section') }}</h4>
                        <p class="mb-0 gray-dark fs-12">{{translate('Display key business statistics like total restaurants, happy customers, and average delivery time.')}}</p>
                    </div>
                    <div class="bg-light2 p-xl-20 p-3 rounded">
                        <form class="validate-form" action="{{ route('admin.react_landing_page.settings', 'react-header-floating-icon') }}"
                            method="post">
                            @method('post')
                            @csrf
                            <div class="row g-3">
                                <div class="col-sm-6 col-lg-4">
                                    <label for="restaurant" class="form-label fw-400">{{ translate('Restaurant') }} <span class="text-danger">*</span>
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right" data-original-title="{{ translate('Enter the total number of restaurant partners on your platform.') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input id="restaurant" type="number" min="0" max="999999" name="floating_icon_restaurant"
                                        class="form-control" placeholder="{{ translate('200') }}"
                                        value="{{ $floating_icon_restaurant?->value ?? 0 }}" required>
                                </div>
                                <div class="col-sm-6 col-lg-4">
                                    <label for="customer" class="form-label fw-400">{{ translate('Happy_Customer') }} <span class="text-danger">*</span>
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('Enter the total number of served customers or a significant milestone.') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input id="customer" type="number" min="0" name="floating_icon_customer" max="999999"
                                        class="form-control" placeholder="{{ translate('1000') }}"
                                        value="{{ $floating_icon_customer?->value ?? 0 }}" required>
                                </div>
                                <div class="col-sm-6 col-lg-4">
                                    <label for="average_delivery"
                                        class="form-label fw-400">{{ translate('Average_Delivery_(Minutes)') }} <span class="text-danger">*</span>
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('Enter the average delivery time range in minutes') }}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input id="average_delivery" type="text" min="0" max="7"
                                           data-maxlength="7"
                                        name="floating_icon_average_delivery" class="form-control"
                                        placeholder="{{ translate('30') }}"
                                        value="{{ $floating_icon_average_delivery?->value ?? 0 }}" required>
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
        </div>

        @include('admin-views.landing_page.react.partials.header_guideline')

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
            })

            document.addEventListener('DOMContentLoaded', function() {
                var removeBtn = document.getElementById('remove_header_image_btn');
                var removeFlag = document.getElementById('react_header_image_remove');
                var fileInput = document.querySelector('input[name="react_header_image"]');
                var previewImg = document.querySelector('.upload-file-img');
                var uploadText = document.querySelector('.upload-file-textbox');
                var form = fileInput ? fileInput.closest('form') : null;

                if (removeBtn && removeFlag && previewImg) {
                    removeBtn.addEventListener('click', function () {
                        removeFlag.value = '1';
                        fileInput.value = '';
                        fileInput.setAttribute('required', 'required');

                        previewImg.style.display = 'none';
                        previewImg.removeAttribute('src');
                        previewImg.removeAttribute('data-default-src');

                        if (uploadText) uploadText.style.display = 'block';
                    });
                }

                if (form && removeFlag) {
                    form.addEventListener('reset', function () {
                        removeFlag.value = '0';
                        if (previewImg && previewImg.dataset.defaultSrc) {
                            previewImg.src = previewImg.dataset.defaultSrc;
                            previewImg.style.display = 'block';
                        }
                    });
                }

                if (fileInput && removeFlag) {
                    fileInput.addEventListener('change', function () {
                        removeFlag.value = '0';
                        if (previewImg) previewImg.style.display = 'block';
                        if (uploadText) uploadText.style.display = 'none';
                    });
                }
            });

        </script>


    @endpush


