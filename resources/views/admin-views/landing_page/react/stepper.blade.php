@extends('layouts.admin.app')

@section('title', translate('messages.landing_page_settings'))

@section('content')

    @php use App\CentralLogics\Helpers; @endphp
    <div class="content container-fluid">
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-start">
                <h1 class="page-header-title text-capitalize fs-24">
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
        <div class="card py-3 px-xxl-4 px-3 mb-15 mt-4">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-xxl-9 col-lg-8 col-md-7 col-sm-6">
                    <div class="">
                        <h3 class="mb-1">{{ translate('Show_on_Website') }}</h3>
                        <p class="mb-0 gray-dark fs-12">
                            {{ translate('If you turn of the availability status, this section will not show in the website') }}
                        </p>
                    </div>
                </div>
                <div class="col-xxl-3 col-lg-4 col-md-5 col-sm-6">
                    <div class="py-2 px-3 rounded d-flex justify-content-between border align-items-center w-300">
                        <h5 class="text-capitalize fw-normal mb-0">{{ translate('Status') }}</h5>
                        <form
                            action="{{ route('admin.landing_page.statusUpdate', ['type' => 'react_landing_page', 'key' => 'stepper_section_status']) }}"
                            method="get" id="CheckboxStatus_form">
                        </form>
                        <label class="toggle-switch toggle-switch-sm" for="CheckboxStatus">
                            <input type="checkbox" data-id="CheckboxStatus" data-type="status"
                                data-image-on="{{ dynamicAsset('assets/admin/img/status-ons.png') }}"
                                data-image-off="{{ dynamicAsset('assets/admin/img/off-danger.png') }}"
                                data-title-on="{{ translate('Do you want turn on this section ?') }}"
                                data-title-off="{{ translate('Do you want to turn off this section ?') }}"
                                data-text-on="<p>{{ translate('If you turn on this section will be show in react landing page.') }}"
                                data-text-off="<p>{{ translate('If you turn off this section will not be show in react landing page.') }}</p>"
                                class="toggle-switch-input  status dynamic-checkbox" id="CheckboxStatus"
                                {{ $stepper_section_status?->value ? 'checked' : '' }}>
                            <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-15">
            <div class="card-header flex-wrap gap-2">
                <div class="">
                    <h3 class="mb-0">{{ translate('Stepper Section ') }}</h3>
                </div>
            </div>
            <div class="card-body pt-2">
                <form action="{{ route('admin.react_landing_page.settings', 'stepper-section') }}" method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="js-nav-scroller hs-nav-scroller-horizontal">

                        @if ($language)
                            <ul class="nav nav-tabs mb-4 ">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active" href="#" id="default-link">{{ translate('messages.default') }}</a>
                                </li>
                                @foreach ($language as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link" href="#" id="{{ $lang }}-link">
                                            {{ Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="row g-lg-4 g-3">
                        @php
                            $totalSteppers = 4;
                        @endphp

                        @for ($i = 1; $i <= $totalSteppers; $i++)
                            @php
                                $stepperImage = ${"stepper_{$i}_image"} ?? null;
                                $stepperTitle = ${"stepper_title_{$i}"} ?? null;
                                $translations = [];

                                if ($stepperTitle?->translations) {
                                    foreach ($stepperTitle->translations as $t) {
                                        if ($t->key === "stepper_title_{$i}") {
                                            $translations[$t->locale] = $t->value;
                                        }
                                    }
                                }

                                $imageUrl = $stepperImage?->value
                                    ? Helpers::get_full_url(
                                        'react_stepper',
                                        $stepperImage->value,
                                        $stepperImage->storage[0]->value ?? 'public',
                                        'upload_1_1',
                                    )
                                    : '';
                            @endphp

                            <div class="col-xxl-3 col-lg-4 col-sm-6">
                                <div class="card-custom-static p-0">
                                    <h4 class="m-0 p-20 py-10px border-bottom">{{ translate("Stepper $i") }}</h4>

                                    <div class="card-body">

                                        {{-- Image upload --}}
                                        <div class="bg-light2 d-flex align-items-center justify-content-center p-xl-20 p-4 rounded h-100 mb-20">
                                            <div class="py-1 text-center">
                                                <h5 class="mb-3">{{ translate('Icon') }}</h5>

                                                <div class="upload-file d-flex image-100 mx-auto position-relative">
                                                    <input type="file" name="stepper_{{ $i }}_image"
                                                           class="upload-file__input single_file_input"
                                                           accept="{{IMAGE_EXTENSION}}">

                                                    {{-- 🔹 Hidden field for delete flag --}}
                                                    <input type="hidden" name="stepper_{{ $i }}_image_deleted"
                                                           class="image-delete-flag" value="0">

                                                    <label class="upload-file__wrapper m-0">
                                                        <div class="upload-file-textbox text-center">
                                                            <img width="30" class="svg"
                                                                 src="{{ dynamicAsset('assets/admin/img/image-upload.png') }}"
                                                                 alt="upload icon">
                                                            <h6 class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">
                                                                <span class="text-info">{{ translate('Click to upload') }}</span>
                                                                <br>
                                                                {{ translate('Or drag and drop') }}
                                                            </h6>
                                                        </div>

                                                        <img class="upload-file-img"
                                                             src="{{ $imageUrl }}"
                                                             alt=""
                                                             style="{{ $imageUrl ? '' : 'display:none;' }}">
                                                    </label>

                                                    <div class="overlay">
                                                        <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                                            <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                                <i class="tio-invisible"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                                <i class="tio-edit"></i>
                                                            </button>
                                                            <button type="button" class="remove_btn btn icon-btn">
                                                                <i class="tio-delete text-danger"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <p class="fs-10 text-center mb-0 mt-20">
                                                    {{ translate(IMAGE_FORMAT.' Less Than 2MB') }}
                                                    <span class="font-medium text-title">{{ translate('(Ratio 1.1)') }}</span>
                                                </p>
                                            </div>
                                        </div>

                                        {{-- Default title --}}
                                        <div class="lang_form default-form">
                                            <div class="form-group m-0">
                                                <label class="form-label fw-400">{{ translate('title') }} ({{ translate('Default') }})</label>
                                                @if ($i == 1)
                                                    <input type="hidden" name="lang[]" value="default">
                                                @endif
                                                <input type="text" data-maxlength="50"
                                                       name="stepper_title_{{ $i }}[]"
                                                       value="{{ $stepperTitle?->getRawOriginal('value') ?? '' }}"
                                                       class="form-control"
                                                       placeholder="{{ translate('Search & Find Food') }}">
                                                <div class="d-flex justify-content-end">
                                                    <span class="text-body-light text-right d-block mt-1">0/50</span>
                                                </div>
                                            </div>
                                        </div>

                                        @foreach ($language as $lang)
                                            <div class="d-none lang_form" id="{{ $lang }}-form{{ $i }}">
                                                @if ($i == 1)
                                                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                @endif
                                                <div class="form-group m-0">
                                                    <label class="form-label fw-400">
                                                        {{ translate('title') }} ({{ strtoupper($lang) }})
                                                    </label>
                                                    <input type="text" data-maxlength="50"
                                                           name="stepper_title_{{ $i }}[]"
                                                           value="{{ $translations[$lang] ?? '' }}"
                                                           class="form-control"
                                                           placeholder="{{ translate('Search & Find Food') }}">
                                                    <div class="d-flex justify-content-end">
                                                        <span class="text-body-light text-right d-block mt-1">0/50</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div class="btn--container justify-content-end mt-20">
                        <button type="reset" class="btn btn--reset">{{ translate('Reset') }}</button>
                        <button type="submit" class="btn btn--primary">{{ translate('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-15">
            <div class="card-header flex-wrap gap-2">
                <div class="">
                    <h3 class="mb-0">{{ translate('Image Section') }}</h3>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.react_landing_page.settings', 'stepper-section-images') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf


                    <div class="card-custom-xl">
                        <h5 class="mb-10px">{{ translate('Show Image') }}</h5>
                        <div class="mb-30">
                            <div class="resturant-type-group upload_image-radio border gap-24px py-sm-1 py-3">
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" value="single"
                                        {{ $stepper_upload_image_type?->value == 'single' ? 'checked' : '' }}
                                        name="stepper_upload_image_type" checked="">
                                    <span class="form-check-label">
                                        {{ translate('Upload Single Image') }}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input" type="radio" value="multiple"
                                        {{ $stepper_upload_image_type?->value == 'multiple' ? 'checked' : '' }}
                                        name="stepper_upload_image_type">
                                    <span class="form-check-label">
                                        {{ translate('Upload Multiple in grid') }}
                                    </span>
                                </label>
                            </div>
                        </div>
                        <h5 class="mb-lg-4 pb-1 mb-3 text-center">{{ translate('Upload Image') }}</h5>
                        <div
                            class="max-w-555px image_single-component d-none align-items-center justify-content-center mx-auto bg-light2 p-md-4 p-3">
                            <div class="">
                                <div class="py-2">
                                    <div class="upload-file d-flex image-250x250 mx-auto">
                                        <input type="file" name="stapper_single_image"
                                            class="upload-file__input single_file_input"
                                            accept="{{IMAGE_EXTENSION}}" id="stapper_single_image">
                                        <input type="hidden" name="stapper_single_image_deleted" class="image-delete-flag" value="0">
                                        <label class="upload-file__wrapper m-0">
                                            <div class="upload-file-textbox text-center" style="">
                                                <img width="30" class="svg"
                                                    src="{{ dynamicAsset('assets/admin/img/image-upload.png') }}"
                                                    alt="img">
                                                <h6 class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">
                                                    <span class="text-info">{{ translate('Click to upload') }}</span>
                                                    <br>
                                                    {{ translate('Or drag and drop') }}
                                                </h6>
                                            </div>
                                            @php
                                                $imageUrl = $stapper_single_image?->value
                                                    ? Helpers::get_full_url(
                                                        'react_stepper',
                                                        $stapper_single_image->value,
                                                        $stapper_single_image->storage[0]->value ?? 'public',
                                                        'upload_1_1',
                                                    )
                                                    : '';
                                            @endphp

                                            <img class="upload-file-img" loading="lazy" src="{{ $imageUrl }}"
                                                alt=""
                                                style="display: none;">
                                        </label>
                                        <div class="overlay">
                                            <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                                <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                    <i class="tio-invisible"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                    <i class="tio-edit"></i>
                                                </button>
                                                <button type="button" class="remove_btn btn icon-btn">
                                                    <i class="tio-delete text-danger"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="fs-10 text-center mb-0 mt-20">
                                        {{ translate(IMAGE_FORMAT.' Less Than 2MB') }} <span
                                            class="font-medium text-title">{{ translate('(Ratio 1.1)') }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="max-w-555px image_multiple-component d-none align-items-center gap-2 justify-content-center mx-auto bg-light2 p-md-4 p-3">
                            <div class="py-2 flex-sm-nowrap flex-wrap d-flex gap-2 justify-content-center align-items-center">
                                <div class="d-flex flex-column align-items-end gap-2 justify-content-end">
                                    <div class="text-start">
                                        <p class="fs-10 mb-1 text-title">
                                            {{ translate('Image 1') }}
                                        </p>
                                        <p class="fs-10 mb-10px">
{{--                                            {{ translate('JPG, JPEG, PNG, ') }}--}}
                                            {{ translate('Size : 356 x 84 px') }}
                                        </p>
                                        <div class="upload-file d-flex image-185 mx-auto">
                                            <input type="file" name="stapper_multiple_image_1"
                                                class="upload-file__input single_file_input"
                                                accept="{{IMAGE_EXTENSION}}">
                                            <input type="hidden" name="stapper_multiple_image_1_deleted" class="image-delete-flag" value="0">
                                            <label class="upload-file__wrapper m-0">
                                                @php
                                                    $imageUrl = $stapper_multiple_image_1?->value
                                                        ? Helpers::get_full_url(
                                                            'react_stepper',
                                                            $stapper_multiple_image_1->value,
                                                            $stapper_multiple_image_1->storage[0]->value ?? 'public',
                                                            'upload_1_1',
                                                        )
                                                        : '';
                                                @endphp

                                                <div class="upload-file-textbox text-center" style="">
                                                    <img width="22" class="svg"
                                                        src="{{ dynamicAsset('assets/admin/img/image-upload.png') }}"
                                                        alt="img">
                                                </div>

                                                <img class="upload-file-img" loading="lazy" src="{{ $imageUrl }}"
                                                    alt=""
                                                    style="{{ $imageUrl ? '' : 'display:none;' }}">
                                            </label>
                                            <div class="overlay">
                                                <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                                    <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                        <i class="tio-invisible"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                        <i class="tio-edit"></i>
                                                    </button>
                                                    <button type="button" class="remove_btn btn icon-btn">
                                                        <i class="tio-delete text-danger"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <div class="upload-file d-flex image-160x190 ml-auto">
                                            <input type="file" name="stapper_multiple_image_2"
                                                class="upload-file__input single_file_input"
                                                accept="{{IMAGE_EXTENSION}}">
                                            <input type="hidden" name="stapper_multiple_image_2_deleted" class="image-delete-flag" value="0">
                                            <label class="upload-file__wrapper ml-auto m-0">
                                                <div class="upload-file-textbox text-center" style="">
                                                    <img width="22" class="svg"
                                                        src="{{ dynamicAsset('assets/admin/img/image-upload.png') }}"
                                                        alt="img">
                                                </div>
                                                @php
                                                    $imageUrl = $stapper_multiple_image_2?->value
                                                        ? Helpers::get_full_url(
                                                            'react_stepper',
                                                            $stapper_multiple_image_2->value,
                                                            $stapper_multiple_image_2->storage[0]->value ?? 'public',
                                                            'upload_1_1',
                                                        )
                                                        : '';
                                                @endphp

                                                <img class="upload-file-img" loading="lazy" src="{{ $imageUrl }}"
                                                    alt=""
                                                    style="{{ $imageUrl ? '' : 'display:none;' }}">
                                            </label>
                                            <div class="overlay">
                                                <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                                    <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                        <i class="tio-invisible"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                        <i class="tio-edit"></i>
                                                    </button>
                                                    <button type="button" class="remove_btn btn icon-btn">
                                                        <i class="tio-delete text-danger"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="fs-10 mb-1 text-title mt-2">
                                            {{ translate('Image 3') }}
                                        </p>
                                        <p class="fs-10 m-0">
{{--                                            {{ translate('JPG, JPEG, PNG, ') }}--}}
                                            {{ translate('Size : 308 x 332 px') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="d-flex flex-column align-items-end gap-2 justify-content-end">
                                    <div class="text-center">
                                        <p class="fs-10 mb-1 text-title">
                                            {{ translate('Image 2') }}
                                        </p>
                                        <p class="fs-10 mb-10px">
{{--                                            {{ translate('JPG, JPEG, PNG, ') }}--}}
                                            {{ translate('Size : 337 x 337 px') }}
                                        </p>
                                        <div class="upload-file d-flex image-112x118 mx-auto">
                                            <input type="file" name="stapper_multiple_image_3"
                                                class="upload-file__input single_file_input"
                                                accept="{{IMAGE_EXTENSION}}">
                                            <input type="hidden" name="stapper_multiple_image_3_deleted" class="image-delete-flag" value="0">
                                            <label class="upload-file__wrapper m-0">
                                                <div class="upload-file-textbox text-center" style="">
                                                    <img width="22" class="svg"
                                                        src="{{ dynamicAsset('assets/admin/img/image-upload.png') }}"
                                                        alt="img">
                                                </div>
                                                @php
                                                    $imageUrl = $stapper_multiple_image_3?->value
                                                        ? Helpers::get_full_url(
                                                            'react_stepper',
                                                            $stapper_multiple_image_3->value,
                                                            $stapper_multiple_image_3->storage[0]->value ?? 'public',
                                                            'upload_1_1',
                                                        )
                                                        : '';
                                                @endphp
                                                <img class="upload-file-img" loading="lazy" src="{{ $imageUrl }}"
                                                    alt=""
                                                    style="{{ $imageUrl ? '' : 'display:none;' }}">
                                            </label>
                                            <div class="overlay">
                                                <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                                    <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                        <i class="tio-invisible"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                        <i class="tio-edit"></i>
                                                    </button>
                                                    <button type="button" class="remove_btn btn icon-btn">
                                                        <i class="tio-delete text-danger"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <div class="upload-file d-flex image-130 ml-auto">
                                            <input type="file" name="stapper_multiple_image_4"
                                                class="upload-file__input single_file_input"
                                                accept="{{IMAGE_EXTENSION}}">
                                            <input type="hidden" name="stapper_multiple_image_4_deleted" class="image-delete-flag" value="0">
                                            <label class="upload-file__wrapper ml-auto m-0">
                                                <div class="upload-file-textbox text-center" style="">
                                                    <img width="22" class="svg"
                                                        src="{{ dynamicAsset('assets/admin/img/image-upload.png') }}"
                                                        alt="img">
                                                </div>
                                                @php
                                                    $imageUrl = $stapper_multiple_image_4?->value
                                                        ? Helpers::get_full_url(
                                                            'react_stepper',
                                                            $stapper_multiple_image_4->value,
                                                            $stapper_multiple_image_4->storage[0]->value ?? 'public',
                                                            'upload_1_1',
                                                        )
                                                        : '';
                                                @endphp
                                                <img class="upload-file-img" loading="lazy" src="{{ $imageUrl }}"
                                                    alt=""
                                                    style="{{ $imageUrl ? '' : 'display:none;' }}">
                                            </label>
                                            <div class="overlay">
                                                <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                                    <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                        <i class="tio-invisible"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                        <i class="tio-edit"></i>
                                                    </button>
                                                    <button type="button" class="remove_btn btn icon-btn">
                                                        <i class="tio-delete text-danger"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="fs-10 mb-1 text-title mt-2">
                                            {{ translate('Image 4') }}
                                        </p>
                                        <p class="fs-10 m-0">
{{--                                            {{ translate('JPG, JPEG, PNG, ') }}--}}
                                            {{ translate('Size : 260 x 260 px') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-center multi-image-instruction d-none">
                            <p class="d-block text-center fs-10 mt-2">
                                {{ translate(IMAGE_FORMAT.' Less Than 2MB') }}
                            </p>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-20">
                        <button type="reset" class="btn btn--reset">{{ translate('Reset') }}</button>
                        <button type="submit" class="btn btn--primary">{{ translate('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @include('admin-views.landing_page.react.partials.stepper_guideline')

@endsection


@push('script_2')
    <script>
        $(document).on('click', '.remove_btn', function () {
            const wrapper = $(this).closest('.upload-file');
            wrapper.find('.upload-file-img').hide();
            wrapper.find('input[type=file]').val('');
            wrapper.find('.image-delete-flag').val('1');
        });

        $(document).ready(function() {

            function toggleStepperImageRequirement() {
                let value = $('[name="stepper_upload_image_type"]:checked').val();

                if (value === 'single') {
                    $(".image_single-component").removeClass("d-none").addClass("d-flex");
                    $(".image_multiple-component").removeClass("d-flex").addClass("d-none");
                    $(".multi-image-instruction").removeClass("d-flex").addClass("d-none");
                } else {
                    $(".image_single-component").removeClass("d-flex").addClass("d-none");
                    $(".image_multiple-component").removeClass("d-none").addClass("d-flex");
                    $(".multi-image-instruction").removeClass("d-none").addClass("d-flex");
                }
            }

            toggleStepperImageRequirement();
            $(document).on('change', '[name="stepper_upload_image_type"]', toggleStepperImageRequirement);
        });
    </script>
@endpush
