@extends('layouts.admin.app')

@section('title', translate('messages.add_delivery_man'))

@section('content')

    <div class="content">
        <form class="validate-form" id="deliveryman-form" method="post" enctype="multipart/form-data">
            @csrf
            <div class="content container-fluid">
                <!-- Page Header -->
                <div class="page-header">
                    <h1 class="page-header-title mb-2 text-capitalize">
                        <!-- <div class="card-header-icon d-inline-flex mr-2 img">
                                                <img src="{{ dynamicAsset('assets/admin/img/delivery-man.png') }}" alt="public">
                                            </div> -->
                        <span>
                            {{ translate('messages.add_new_deliveryman') }}
                        </span>
                    </h1>
                </div>
                <!-- End Page Header -->
                <div class="card mb-20">
                    <div class="card-header">
                        <div>
                            <h3 class="mb-1 gap-1">
                                <!-- <span class="card-title-icon"><i class="tio-user"></i></span> -->
                                <span>
                                    {{ translate('messages.general_info') }}
                                </span>
                            </h3>
                            <p class="fs-12 mb-0">{{ translate('messages.Here you setup your general information.') }}</p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-8">
                                <div class="p-xxl-20 p-12 global-bg-box rounded h-100">
                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <div class="form-group m-0">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ translate('messages.first_name') }}
                                                    <span class="text-danger">*</span></label>
                                                <input type="text" name="f_name" class="form-control h--45px"
                                                    placeholder="{{ translate('Ex:_Jhone') }}" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group m-0">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ translate('messages.last_name') }}
                                                    <span class="text-danger">*</span></label>
                                                <input type="text" name="l_name" class="form-control h--45px"
                                                    placeholder="{{ translate('Ex:_Joe') }}" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group m-0">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ translate('messages.email') }} <span
                                                        class="text-danger">*</span></label>
                                                <input type="email" name="email" class="form-control h--45px"
                                                    placeholder="{{ translate('Ex:_ex@example.com') }}" required>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group m-0">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ translate('messages.deliveryman_type') }}
                                                    <span class="text-danger">*</span></label>
                                                <select name="earning" class="form-control js-select2-custom h--45px" data-search-placeholder="{{ translate('search_deliveryman') }}"
                                                    required>
                                                    <option value="" readonly="true" hidden="true">
                                                        {{ translate('messages.delivery_man_type') }}</option>
                                                    <option value="1">{{ translate('messages.freelancer') }}</option>
                                                    <option value="0">{{ translate('messages.salary_based') }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group m-0">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ translate('messages.zone') }} <span
                                                        class="text-danger">*</span></label>
                                                <select name="zone_id" id="dm_zone_id" class="form-control js-select2-custom h--45px"
                                                    required data-search-placeholder="{{ translate('search_zone') }}" data-placeholder="{{ translate('messages.select_zone') }}">
                                                    <option value="" readonly="true" hidden="true">
                                                        {{ translate('Ex:_XYZ_Zone') }}</option>
                                                    @if (auth('admin')?->user()?->zone_id)
                                                        @php
                                                        $admin_zone = \App\Models\Zone::where('status', 1)->where('id', auth('admin')->user()->zone_id)->first(['id', 'name'])
                                                        @endphp
                                                        @if ($admin_zone)
                                                            <option value="{{ $admin_zone->id }}" selected>{{ $admin_zone->name }}</option>
                                                        @endif
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-6" id="shift-view">
                                            <div class="form-group m-0">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ translate('messages.Working Shift') }}
                                                    <span class="text-danger">*</span></label>
                                                @php
                                                    $shifts_translations = [
                                                        'full_day' => translate('messages.full_day'),
                                                        'select_shifts' => translate('messages.select_shifts'),
                                                        'salary_text' => translate(
                                                            'Salary based delivery men work according to their contract/assigned hours.',
                                                        ),
                                                        'full_day_text' => translate(
                                                            'You will receive delivery orders 24/7.',
                                                        ),
                                                        'specific_shift_text_1' => translate(
                                                            'You will only receive delivery orders during the',
                                                        ),
                                                        'specific_shift_text_2' => translate(
                                                            'shifts. Orders outside these time slots will not be received.',
                                                        ),
                                                        'no_shift_text' => translate(
                                                            'Please select a shift to see availability.',
                                                        ),
                                                    ];
                                                @endphp
                                                <div class="multi-select-container"
                                                    data-translations='{{ json_encode($shifts_translations) }}'>
                                                    <div class="select-box">
                                                        <div class="tags-container"></div>
                                                        <span class="arrow"><i class="tio-down-ui fs-10"></i></span>
                                                    </div>
                                                    <div class="dropdown-list">
                                                        @foreach (\App\Models\Shift::orderBy('is_full_day', 'desc')->get() as $shift)
                                                            <label
                                                                class="option-item {{ $shift->is_full_day ? 'full-day-wrapper' : '' }}">
                                                                <span>
                                                                    {{ $shift->name }}
                                                                    @unless ($shift->is_full_day)
                                                                        ({{ \App\CentralLogics\Helpers::time_format($shift->start_time) }}
                                                                        -
                                                                        {{ \App\CentralLogics\Helpers::time_format($shift->end_time) }})
                                                                    @endunless
                                                                </span>
                                                                <input type="checkbox" name="shifts[]"
                                                                    class="{{ $shift->is_full_day ? 'full-day-checkbox' : 'slot-checkbox' }}"
                                                                    value="{{ $shift->id }}"
                                                                    data-name="{{ $shift->name }}"
                                                                    data-time-range="{{ \App\CentralLogics\Helpers::time_format($shift->start_time) }} - {{ \App\CentralLogics\Helpers::time_format($shift->end_time) }}"
                                                                    {{ $shift->is_full_day ? 'checked' : '' }}>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card shadow-none border-0 bg-soft-warning mt-20 rounded">
                                        <div class="card-body py-2 px-3 d-flex">
                                            <i class="mr-2 text-warning tio-info mt-1"></i>
                                            <p class="fs-12 text-dark m-0" id="shift-info-text">
                                                {{ translate('You will only receive delivery orders during the') }}
                                                <strong>{{ translate('Morning (05:01 AM – 11:59 AM) and Evening (06:00 PM – 09:00 PM) ') }}</strong>
                                                {{ translate('shifts. Orders outside these time slots will not be received.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="p-xxl-20 p-12 global-bg-box rounded mb-20 h-100">
                                    <div class="pb-lg-1">
                                        <div class="mb-4">
                                            <h5 class="mb-1">
                                                {{ translate('Deliveryman Image') }} <span class="text-danger">*</span>
                                            </h5>
                                            <p class="mb-0 fs-12 gray-dark">{{ translate('Upload your Business Logo') }}
                                            </p>
                                        </div>
                                        <div class="text-center">
                                            @include('admin-views.partials._image-uploader', [
                                                'id' => 'image-input',
                                                'name' => 'image',
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
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-20">
                    <div class="card-header">
                        <div>
                            <h3 class="mb-1 gap-1">
                                <span>
                                    {{ translate('messages.Identification_Information') }}
                                </span>
                            </h3>
                            <p class="fs-12 mb-0">
                                {{ translate('messages.Here you setup your identification information') }}</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="p-xxl-20 p-12 global-bg-box rounded">
                            <div class="row g-3">
                                <div class="col-lg-4">
                                    <div class="form-group m-0">
                                        <label class="input-label">{{ translate('messages.Vehicle') }} <span
                                                class="text-danger">*</span></label>
                                        <select name="vehicle_id" class="form-control js-select2-custom h--45px"
                                           data-search-placeholder="{{ translate('search_vehicle') }}" data-placeholder="{{ translate('messages.select_vehicle') }}" required>
                                            <option value="" readonly="true" hidden="true">
                                                {{ translate('messages.select_vehicle') }}</option>
                                            @foreach (\App\Models\Vehicle::where('status', 1)->get(['id', 'type']) as $v)
                                                <option value="{{ $v->id }}">{{ $v->type }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group m-0">
                                        <label class="input-label">{{ translate('messages.identity_type') }} <span
                                                class="text-danger">*</span></label>
                                        <select name="identity_type" class="form-control h--45px" required>
                                            <option value="passport">{{ translate('messages.passport') }}</option>
                                            <option value="driving_license">{{ translate('messages.driving_license') }}
                                            </option>
                                            <option value="nid">{{ translate('messages.nid') }}</option>
                                            {{-- <option value="restaurant_id">{{ translate('messages.restaurant_id') }} --}}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group m-0">
                                        <label class="input-label">{{ translate('messages.identity_number') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="identity_number" class="form-control h--45px"
                                            min="6" max="30"
                                            placeholder="{{ translate('Ex:_DH-23434-LS') }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-xxl-20 p-12 global-bg-box rounded mt-3">
                            <div class="form-group m-0">
                                <div class="mb-20">
                                    <label class="input-label fs-14 font-semibold mb-1">
                                        {{ translate('messages.identity_image') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <p class="m-0 fs-12">
                                        {{ translate('messages.JPG, JPEG, PNG ,WEBP, Less Than 2MB') }}
                                        ({{ translate('messages.Ratio 2:1') }})
                                    </p>
                                </div>
                                <div class="position-relative">
                                    <div class="multi_image_picker d-flex gap-4 pt-4 px-2" data-ratio="2/1"
                                        data-field-name="identity_image[]" data-max-count="5"
                                        data-max-filesize="{{ defined('MAX_FILE_SIZE') ? MAX_FILE_SIZE : 2 }}"
                                        data-required="true" data-accept="{{ IMAGE_EXTENSION }}"
                                        data-doc-image="{{ dynamicAsset('assets/admin/img/document.svg') }}">
                                        <div>
                                            <div class="imageSlide_prev">
                                                <div class="d-flex justify-content-center align-items-center h-100">
                                                    <button type="button"
                                                        class="btn btn-circle border-0 text-body bg-white shadow-sm">
                                                        <i class="tio-chevron-left fs-24"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="imageSlide_next">
                                                <div class="d-flex justify-content-center align-items-center h-100">
                                                    <button type="button"
                                                        class="btn btn-circle border-0 text-body bg-white shadow-sm">
                                                        <i class="tio-chevron-right fs-24"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @include('admin-views.partials._custom-fields', [
                    'page_data' => $page_data,
                    'additional_data' => [],
                    'additional_documents' => [],
                ])

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="mb-1">
                                <!-- <span class="card-header-icon"><i class="tio-user"></i></span> -->
                                <span>{{ translate('messages.Account_Information') }}</span>
                            </h3>
                            <p class="fs-12 mb-0">
                                {{ translate('messages.Setup your business time zone and format from here') }}</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="p-xxl-20 p-12 global-bg-box rounded">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-group iti_flat-bg m-0">
                                        <label class="input-label" for="phone">{{ translate('messages.phone') }} <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="tel" name="phone" id="phone"
                                                placeholder="{{ translate('Ex:_017********') }}"
                                                class="form-control h--45px" pattern="^\+[1-9][0-9]{6,14}$" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group m-0">
                                        <div class="js-form-message">
                                            <label class="input-label"
                                                for="signupSrPassword">{{ translate('messages.password') }} <span
                                                    class="text-danger">*</span>
                                                <span class="input-label-secondary ps-1" data-toggle="tooltip"
                                                    title="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"><i
                                                        class="tio-info text-muted fs-14"></i></span>

                                            </label>

                                            <div class="input-group input-group-merge">
                                                <input type="password" class="js-toggle-password form-control h--45px"
                                                    name="password" id="signupSrPassword"
                                                    pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                                    title="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"
                                                    placeholder="{{ translate('messages.Ex:_8+_Character') }}"
                                                    aria-label="{{ translate('messages.password_length_8+') }}" required
                                                    data-msg="Your password is invalid. Please try again."
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
                                            <ul id="password-rules"
                                                class=" gap-1 mt-2 mb-0 small list-unstyled text-muted"
                                                style="display: none;">
                                                <li>
                                                    <ul class="d-flex flex-wrap gap-1 list-unstyled">
                                                        <li id="rule-length"><i class="text-danger">&#10060;</i>
                                                            {{ translate('At least 8 characters') }}</li>
                                                        <li id="rule-lower"><i class="text-danger">&#10060;</i>
                                                            {{ translate('At least one lowercase letter') }}</li>
                                                        <li id="rule-upper"><i class="text-danger">&#10060;</i>
                                                            {{ translate('At least one uppercase letter') }}</li>
                                                        <li id="rule-number"><i class="text-danger">&#10060;</i>
                                                            {{ translate('At least one number') }}</li>
                                                        <li id="rule-symbol"><i class="text-danger">&#10060;</i>
                                                            {{ translate('At least one symbol') }}</li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <!-- This is Static -->
                                <div class="col-md-4">
                                    <div class="js-form-message form-group">
                                        <label class="input-label"
                                            for="signupSrConfirmPassword">{{ translate('messages.confirm_password') }}
                                            <span class="text-danger">*</span></label>

                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control h--45px"
                                                name="confirmPassword" id="signupSrConfirmPassword"
                                                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                                title="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"
                                                placeholder="{{ translate('messages.Ex:_8+_Character') }}"
                                                aria-label="{{ translate('messages.password_length_8+') }}" required
                                                data-msg="Password does not match the confirm password."
                                                data-hs-toggle-password-options='{
                                                                                        "target": [".js-toggle-password-target-2"],
                                                                                        "defaultClass": "tio-hidden-outlined",
                                                                                        "showClass": "tio-visible-outlined",
                                                                                        "classChangeTarget": ".js-toggle-passowrd-show-icon-2"
                                                                                        }'>
                                            <div class="js-toggle-password-target-2 input-group-append">
                                                <a class="input-group-text" href="javascript:;">
                                                    <i class="js-toggle-passowrd-show-icon-2 tio-visible-outlined"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <!-- Feedback for match/mismatch -->
                                        <small id="confirm-password-feedback"
                                            class="text-danger d-none">{{ translate('Passwords do not match.') }}</small>
                                    </div>
                                </div>
                                <!-- This is Static -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-sticky mt-2">
                <div class="container-fluid">
                    <div class="d-flex flex-wrap gap-3 justify-content-center py-3">
                        <button type="reset" id="reset_btn"
                            class="btn btn--secondary min-w-120">{{ translate('messages.Reset') }} </button>
                        <button type="submit" class="btn btn--primary submitBtn">
                            <i class="tio-save"></i>
                            {{ translate('Save_Information') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection

@push('script_2')
    <script src="{{ dynamicAsset('assets/admin/js/spartan-multi-image-picker.js') }}"></script>
    <script src="{{ dynamicAsset('assets/admin/js/view-pages/multiple-image-upload.js') }}"></script>

    <script src="{{ dynamicAsset('assets/admin') }}/js/file-preview/pdf.min.js"></script>
    <script src="{{ dynamicAsset('assets/admin') }}/js/file-preview/pdf-worker.min.js"></script>
    <script src="{{ dynamicAsset('assets/admin') }}/js/file-preview/multiple-document-upload.js"></script>

    <script>
        "use strict";
        const IDENTITY_MAX = 5;
        let elementCustomUploadInputFileByID = $('.custom-upload-input-file');
        let elementCustomUploadInputFileByID2 = $('.custom-upload-input-file2');

        $('.action-add-more-image').on('change', function() {
            let parentDiv = $(this).closest('div');
            parentDiv.find('.delete_file_input').removeClass('d-none');
            parentDiv.find('.delete_file_input').fadeIn();
            addMoreImage(this, $(this).data('target-section'))
        })
        $('.action-add-more-image2').on('change', function() {
            let parentDiv = $(this).closest('div');
            parentDiv.find('.delete_file_input').removeClass('d-none');
            parentDiv.find('.delete_file_input').fadeIn();
            addMoreImage2(this, $(this).data('target-section'))
        })

        function addMoreImage(thisData, targetSection) {
            // Count selected files for identity_image[]
            const $allInputs = $(targetSection + " input[type='file'][name='" + thisData.name + "']");
            const selectedCount = $allInputs.filter(function() {
                return this.files && this.files.length > 0;
            }).length;

            uploadColorImage(thisData)

            // If limit reached, remove any empty uploader slots and stop
            if (selectedCount >= IDENTITY_MAX) {
                $(targetSection + " .custom_upload_input").each(function() {
                    const $inp = $(this).find("input[type='file']");
                    if ($inp.length && $inp.prop('files').length === 0) {
                        $(this).closest('[class*="col-"]').remove();
                    }
                });
                return;
            }

            // If there is no empty uploader slot, append one (still under limit)
            let emptySlots = 0;
            $(targetSection + " .custom_upload_input").each(function() {
                const $inp = $(this).find("input[type='file']");
                if ($inp.length && $inp.prop('files').length === 0) emptySlots++;
            });


            if (emptySlots === 0) {

                // Compute next unique index
                let maxIndex = 0;
                $allInputs.each(function() {
                    const idx = parseInt(this.dataset.index || '0', 10);
                    if (!isNaN(idx) && idx > maxIndex) maxIndex = idx;
                });
                let datasetIndex = maxIndex + 1;

                let newHtmlData = `<div class="col-sm-6 col-md-4 col-lg-3 col-xxl-2">
                                <div class="custom_upload_input custom_upload_preview  position-relative bg-white border-dashed-2">
                                    <input type="file" name="${thisData.name}" class="custom-upload-input-file action-add-more-image" data-index="${datasetIndex}" data-imgpreview="additional_Image_${datasetIndex}"
                                        accept="{{ DOCUMENT_EXTENSION . ', ' . IMAGE_EXTENSION }}" data-target-section="${targetSection}">

                                    <span class="delete_file_input delete_file_input_section btn btn-outline-danger btn-sm square-btn d-none">
                                        <i class="tio-delete"></i>
                                    </span>
                                    <div class="overlay">
                                        <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                            <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                <i class="tio-invisible"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                <i class="tio-edit"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="img_area_with_preview z-index-2 p-0">
                                        <img alt="" id="additional_Image_${datasetIndex}" class="bg-white d-none" src="">
                                    </div>
                                    <div class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                        <div class="d-flex flex-column justify-content-center align-items-center">
                                            <img alt="" width="30"
                                                         src="{{ dynamicAsset('assets/admin/img/doc-uploaded.png') }}">
                                            <div class="text-title mt-3 fs-12">Select a file or <span class="font-semibold text-title">Drag & Drop</span> here</div>
                                        </div>
                                    </div>
                                </div>
                            </div>`;

                $(targetSection).append(newHtmlData);
            }

            elementCustomUploadInputFileByID.on('change', function() {
                if (parseFloat($(this).prop('files').length) !== 0) {
                    let parentDiv = $(this).closest('div');
                    parentDiv.find('.delete_file_input').fadeIn();
                }
            })

            $('.delete_file_input_section').off('click').on('click', function() {
                const target = $(this).closest('div').parent();
                target.remove();

                // After deletion, if below limit and no empty slot present, add one
                const $allInputsNow = $(targetSection + " input[type='file'][name='" + thisData.name + "']");
                const selectedCountNow = $allInputsNow.filter(function() {
                    return this.files && this.files.length > 0;
                }).length;

                let hasEmpty = false;
                $(targetSection + " .custom_upload_input").each(function() {
                    const $inp = $(this).find("input[type='file']");
                    if ($inp.length && $inp.prop('files').length === 0) hasEmpty = true;
                });

                if (selectedCountNow < IDENTITY_MAX && !hasEmpty) {
                    // Compute next unique index
                    let maxIndex = 0;
                    $allInputsNow.each(function() {
                        const idx = parseInt(this.dataset.index || '0', 10);
                        if (!isNaN(idx) && idx > maxIndex) maxIndex = idx;
                    });
                    const datasetIndex2 = maxIndex + 1;

                    const newHtmlData2 = `<div class="col-sm-6 col-md-4 col-lg-3 col-xxl-2">
                                <div class="custom_upload_input custom_upload_preview  position-relative bg-white border-dashed-2">
                                    <input type="file" name="${thisData.name}" class="custom-upload-input-file action-add-more-image" data-index="${datasetIndex2}" data-imgpreview="additional_Image_${datasetIndex2}"
                                        accept="{{ DOCUMENT_EXTENSION . ', ' . IMAGE_EXTENSION }}" data-target-section="${targetSection}">

                                    <span class="delete_file_input delete_file_input_section btn btn-outline-danger btn-sm square-btn d-none">
                                        <i class="tio-delete"></i>
                                    </span>
                                    <div class="overlay">
                                        <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                            <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                <i class="tio-invisible"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                <i class="tio-edit"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="img_area_with_preview z-index-2 p-0">
                                        <img alt="" id="additional_Image_${datasetIndex2}" class="bg-white d-none" src="">
                                    </div>
                                    <div class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                        <div class="d-flex flex-column justify-content-center align-items-center">
                                            <img alt="" width="30"
                                                     src="{{ dynamicAsset('assets/admin/img/doc-uploaded.png') }}">
                                            <div class="text-title mt-3 fs-12">Select a file or <span class="font-semibold text-title">Drag & Drop</span> here</div>
                                        </div>
                                    </div>
                                </div>
                            </div>`;

                    $(targetSection).append(newHtmlData2);

                    // Re-bind change for dynamically added inputs
                    $('.action-add-more-image').off('change').on('change', function() {
                        let parentDiv = $(this).closest('div');
                        parentDiv.find('.delete_file_input').removeClass('d-none');
                        parentDiv.find('.delete_file_input').fadeIn();
                        addMoreImage(this, $(this).data('target-section'))
                    });
                }
            });


            $('.action-add-more-image').on('change', function() {
                let parentDiv = $(this).closest('div');
                parentDiv.find('.delete_file_input').removeClass('d-none');
                parentDiv.find('.delete_file_input').fadeIn();
                addMoreImage(this, $(this).data('target-section'))
            })

        }

        function addMoreImage2(thisData, targetSection) {

            let $fileInputs = $(targetSection + " input[type='file']");
            let nonEmptyCount = 0;
            $fileInputs.each(function() {
                if (parseFloat($(this).prop('files').length) === 0) {
                    nonEmptyCount++;
                }
            });
            var count = 0;

            console.log(thisData.dataset.image_count_key);
            uploadColorImage(thisData)
            $('.image_count_' + thisData.dataset.image_count_key).each(function() {
                const dataIndexElements = $(this).find('[data-index]');

                count += dataIndexElements.length;
            });

            if (count === 5) {
                console.log('done');
                return true;
            }
            if (nonEmptyCount === 0) {

                let datasetIndex = thisData.dataset.index + 1;

                let newHtmlData = ` <div class="col-sm-6 col-md-4 col-lg-3">
                <p class="mb-2 form-label">&nbsp;</p>
                        <div class=" custom_upload_input position-relative border-dashed-2">
                            <input type="file" name="${thisData.name}" class="custom-upload-input-file2 action-add-more-image2"
                                    data-index="${datasetIndex}" data-imgpreview="additional_data_Image_${datasetIndex}"
                                    accept="${thisData.accept}"
                                    data-target-section="${targetSection}"
                                    data-image_count_key="${thisData.dataset.image_count_key}"
                            >

                            <span class="delete_file_input delete_file_input_section btn btn-outline-danger btn-sm square-btn d-none">
                                <i class="tio-delete"></i>
                            </span>

                            <div class="img_area_with_preview z-index-2 p-0">
                                <img id="additional_data_Image_${datasetIndex}" class="bg-white d-none"
                                        src="{{ dynamicAsset('assets/admin/img/upload-icon.png-dummy') }}" alt="">
                            </div>
                            <div
                                class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                <div
                                    class="d-flex flex-column justify-content-center align-items-center">
                                    <img alt="" width="30"
                                            src="{{ dynamicAsset('assets/admin/img/upload-icon.png') }}">
                                    <div class="text-muted mt-3">{{ translate('Upload_Picture') }}</div>
                                    <div class="fs-10 text-muted mt-1">{{ translate('Upload jpg, png, jpeg, gif maximum 2 MB') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>`;







                $(targetSection).append(newHtmlData);
            }
            elementCustomUploadInputFileByID2.on('change', function() {
                if (parseFloat($(this).prop('files').length) !== 0) {
                    let parentDiv = $(this).closest('div');


                    parentDiv.find('.delete_file_input').fadeIn();
                }
            })

            $('.delete_file_input_section').click(function() {
                $(this).closest('div').parent().remove();
            });


            $('.action-add-more-image2').on('change', function() {
                let parentDiv = $(this).closest('div');
                parentDiv.find('.delete_file_input').removeClass('d-none');
                parentDiv.find('.delete_file_input').fadeIn();
                addMoreImage2(this, $(this).data('target-section'))
            })

        }

        $('.delete_file_input').on('click', function() {
            let $parentDiv = $(this).parent().parent();
            $parentDiv.find('input[type="file"]').val('');
            $parentDiv.find('.img_area_with_preview img').addClass("d-none");
            $(this).removeClass('d-flex');
            $(this).hide();
        });

        function uploadColorImage(thisData = null) {
            if (thisData) {
                let file = thisData.files[0];
                let fileType = file.type;
                let previewImage = document.getElementById(thisData.dataset.imgpreview);

                if (fileType.startsWith('image/')) {
                    previewImage.setAttribute("src", window.URL.createObjectURL(file));
                } else {
                    previewImage.setAttribute("src", "{{ dynamicAsset('assets/admin/img/doc-uploaded.png') }}");
                }
                previewImage.classList.remove('d-none');
            }
        }


        $('#deliveryman-form').on('submit', function(e) {
            e.preventDefault();
            if (typeof FormValidation !== 'undefined' && !FormValidation.validateForm(this)) {
                return;
            }

            // Validate Identity Image
            if (typeof validateRequiredImages === 'function') {
                if (!validateRequiredImages()) {
                    return;
                }
            }

            // Validate Additional Files
            let isValid = true;
            $('#deliveryman-form .document_input[required]').each(function() {
                if ($(this).closest('.upload-file').find('.pdf-single').length === 0 && $(this).val() ===
                    '') {
                    toastr.error('{{ translate('messages.please_upload_required_files') }}');
                    isValid = false;
                    return false;
                }
            });

            if (!isValid) return;
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{ route('admin.delivery-man.store') }}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    if (data.errors) {
                        $('#loading').hide();
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        $('#loading').hide();
                        toastr.success('{{ translate('deliveryman_added_successfully!') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function() {
                            location.href = '{{ route('admin.delivery-man.list') }}';
                        }, 2000);
                    }
                }
            });
        });



        $('#reset_btn').click(function() {
            location.reload();
            $('#viewer').attr('src', '{{ dynamicAsset('assets/admin/img/900x400/img1.jpg') }}');
            $('#coba').attr('src', '{{ dynamicAsset('assets/admin/img/900x400/img1.jpg') }}');
        })
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
                    feedback.textContent = "{{ translate('Passwords do not match.') }}";
                    feedback.classList.remove("d-none");
                }
            }

            confirmInput.addEventListener("input", validateMatch);
            passwordInput.addEventListener("input", validateMatch); // In case password changes after confirm input
        });
    </script>

    <script>
        $(document).ready(function() {
            // --------- Existing toggleActiveClass & observer code ---------
            function toggleActiveClass() {
                $("#additional_Image_Section .custom_upload_input").each(function() {
                    var $img = $(this).find("img");

                    if ($img.attr("src") && $img.attr("src").trim() !== "") {
                        $(this).addClass("active");
                    } else {
                        $(this).removeClass("active");
                    }
                });
            }
            toggleActiveClass();

            $("#additional_Image_Section").on("load", "img", toggleActiveClass);

            var observer = new MutationObserver(function(mutationsList) {
                mutationsList.forEach(function(mutation) {
                    if (mutation.type === "childList" && mutation.addedNodes.length > 0) {
                        toggleActiveClass();
                    }
                });
            });

            observer.observe(document.getElementById("additional_Image_Section"), {
                childList: true,
                subtree: true,
            });

            // --------- Feature 1: View image on view_btn click ---------
            $("#additional_Image_Section").on("click", ".view_btn", function(e) {
                e.preventDefault();
                var $container = $(this).closest(".custom_upload_input");
                var src = $container.find("img").attr("src");

                if (src) {
                    // Remove existing modal if any
                    $(".image-modal-overlay").remove();

                    // Append modal to body
                    var modalHtml = `
                            <div class="image-modal-overlay">
                                <div class="image-modal-content">
                                    <span class="close-modal_img">&times;</span>
                                    <div class="main-image-modal">
                                        <img src="${src}" alt="Preview Image"/>
                                    </div>
                                </div>
                            </div>
                        `;
                    $("body").append(modalHtml);
                }
            });

            // Close modal when clicking close button
            $("body").on("click", ".close-modal_img", function() {
                $(this).closest(".image-modal-overlay").remove();
            });

            // Close modal when clicking outside the image
            $("body").on("click", ".image-modal-overlay", function(e) {
                if ($(e.target).hasClass("image-modal-overlay")) {
                    $(this).remove();
                }
            });

            // --------- Feature 2: Re-upload on edit_btn click ---------
            $("#additional_Image_Section").on("click", ".edit_btn", function() {
                var $container = $(this).closest(".custom_upload_input");
                var $fileInput = $container.find('input[type="file"]');

                if ($fileInput.length) {
                    $fileInput.trigger("click");
                }
            });

            // Update image when file is selected
            $("#additional_Image_Section").on("change", 'input[type="file"]', function() {
                var file = this.files[0];
                var $container = $(this).closest(".custom_upload_input");
                var $img = $container.find("img");

                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $img.attr("src", e.target.result);
                        toggleActiveClass();
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
@endpush

@push('script_2')
    <script>
        $(document).ready(function() {
            // ---------------------------------------------------------
            // CUSTOM MULTI-SELECT FOR SHIFTS
            // ---------------------------------------------------------
            var $container = $('.multi-select-container');
            var $selectBox = $container.find('.select-box');
            var $dropdown = $container.find('.dropdown-list');
            var $tagsContainer = $container.find('.tags-container');
            var $arrow = $container.find('.arrow');

            // Toggle dropdown
            $selectBox.on('click', function(e) {
                // e.stopPropagation();
                $dropdown.toggleClass('open');
                $selectBox.toggleClass('open');
            });

            // Close when clicking outside
            $(document).on('click', function(e) {
                if (!$container.is(e.target) && $container.has(e.target).length === 0) {
                    $dropdown.removeClass('open');
                    $selectBox.removeClass('open');
                }
            });

            // Handle Checkbox Changes
            $container.on('change', 'input[type="checkbox"]', function() {
                var $this = $(this);
                var $group = $this.closest('.dropdown-list');
                var isFullDay = $this.hasClass('full-day-checkbox');
                var isChecked = $this.prop('checked');

                if (isFullDay) {
                    // If "Full Day" is checked, uncheck and disable all slots
                    if (isChecked) {
                        // Uncheck other slots
                        $container.find('.slot-checkbox').prop('checked', false).prop('disabled', true);
                        $group.find('.full-day-wrapper').addClass('active');
                    } else {
                        // Enable other slots
                        $container.find('.slot-checkbox').prop('disabled', false);
                        $group.find('.full-day-wrapper').removeClass('active');
                    }
                } else {
                     // If a slot is checked, ensure Full Day is unchecked and inactive
                     $container.find('.full-day-checkbox').prop('checked', false);
                     $group.find('.full-day-wrapper').removeClass('active');
                }

                updateTags();
            });

            $container.on('click', '.option-item', function (e) {

                // If the user clicked the checkbox directly, let natural behavior happen
                // The 'change' handler above will take care of the logic.
                if ($(e.target).is('input[type="checkbox"]')) {
                    return;
                }

                e.preventDefault();

                // If clicked on the label/row, manually toggle
                var $checkbox = $(this).find('input[type="checkbox"]');
                var newState = !$checkbox.prop('checked');
                $checkbox.prop('checked', newState).trigger('change');

            });


            // Update Tags Trigger
            function updateTags() {
                $tagsContainer.empty();
                var selected = [];
                var selectedNames = [];

                // Full Day Check
                var fullDayChecked = $container.find('.full-day-checkbox').prop('checked');
                if (fullDayChecked) {
                    selected.push({
                        name: "{{ translate('messages.full_day') }}",
                        id: 'full_day',
                        isFullDay: true
                    });
                    selectedNames.push("{{ translate('messages.full_day') }}");
                } else {
                    $container.find('.slot-checkbox:checked').each(function() {
                        selected.push({
                            name: $(this).data('name'),
                            id: $(this).val(),
                            isFullDay: false
                        });
                        selectedNames.push($(this).data('name') + ' (' + $(this).data('time-range') + ')');
                    });
                }

                // Render Tags
                if (selected.length === 0) {
                    $tagsContainer.html(
                        '<span class="placeholder-text">{{ translate('messages.select_shifts') }}</span>');
                } else {
                    selected.forEach(function(item) {
                        var $tag = $('<span class="tag">' + item.name +
                            ' <span class="remove-tag" data-id="' + item.id + (item.isFullDay ?
                                '" data-fullday="true"' : '"') + '>&times;</span></span>');
                        $tagsContainer.append($tag);
                    });
                }

                updateShiftInfo(fullDayChecked, selectedNames);
            }

            // Start Update Shift Info
            function updateShiftInfo(isFullDay, selectedShifts) {
                var type = $('select[name="earning"]').val();
                var $infoText = $('#shift-info-text');

                if (type == '0') { // Salary Based
                    $infoText.html(
                        "{{ translate('Salary based delivery men work according to their contract/assigned hours.') }}"
                    );
                } else { // Freelancer
                    if (isFullDay) {
                        $infoText.html("{{ translate('You will receive delivery orders 24/7.') }}");
                    } else if (selectedShifts.length > 0) {
                        // Join names with comma
                        var shiftNames = selectedShifts.join(', ');
                        $infoText.html(
                            "{{ translate('You will only receive delivery orders during the') }} <strong>" +
                            shiftNames +
                            "</strong> {{ translate('shifts. Orders outside these time slots will not be received.') }}"
                        );
                    } else {
                        $infoText.html("{{ translate('Please select a shift to see availability.') }}");
                    }
                }
            }
            // End Update shift info

            // Remove Tag
            $tagsContainer.on('click', '.remove-tag', function(e) {
                e.stopPropagation();
                var id = $(this).data('id');
                var isFullDay = $(this).data('fullday');

                if (isFullDay) {
                    $container.find('.full-day-checkbox').prop('checked', false).trigger('change');
                } else {
                    $container.find('input[value="' + id + '"]').prop('checked', false).trigger('change');
                }
            });

            // Initial State - disable slots if full day is checked by default (it is in our case)
            if ($container.find('.full-day-checkbox').prop('checked')) {
                $container.find('.slot-checkbox').prop('disabled', true);
            }
            updateTags();

            // ---------------------------------------------------------
            // CONDITIONAL VISIBILITY (EXISTING LOGIC)
            // ---------------------------------------------------------
            function toggleShiftView() {
                var type = $('select[name="earning"]').val();
                var shiftLabel = $('#shift-view .input-label');
                var shiftRequiredSpan = shiftLabel.find('.text-danger');

                if (type == '1') { // Freelancer
                    $('#shift-view').show();
                    shiftRequiredSpan.show();
                } else {
                    $('#shift-view').hide();
                }
                // Also update the text when switching type
                // We need to re-calculate current state
                var isFullDay = $container.find('.full-day-checkbox').prop('checked');
                var selectedNames = [];
                if (isFullDay) {
                    selectedNames.push("{{ translate('messages.full_day') }}");
                } else {
                    $container.find('.slot-checkbox:checked').each(function() {
                        selectedNames.push($(this).data('name'));
                    });
                }
                updateShiftInfo(isFullDay, selectedNames);
            }

            $('select[name="earning"]').on('change', toggleShiftView);
            toggleShiftView();
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#dm_zone_id').select2({
                ajax: {
                    url: '{{ route('admin.zone.list') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            zone_id: '{{ auth('admin')?->user()?->zone_id }}',
                            page: params.page
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    __port: function(params, success, failure) {
                        let $request = $.ajax(params);
                        $request.then(success);
                        $request.fail(failure);
                        return $request;
                    }
                },
                placeholder: '{{ translate('messages.select_zone') }}'
            });
        });
    </script>
@endpush
