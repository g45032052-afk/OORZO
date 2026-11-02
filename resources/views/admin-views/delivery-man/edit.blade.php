@extends('layouts.admin.app')

@section('title', translate('Update_Deliveryman'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title mb-2 text-capitalize">
                <!-- <div class="card-header-icon d-inline-flex mr-2 img">
                                    <img src="{{ dynamicAsset('assets/admin/img/delivery-man.png') }}" alt="public">
                                </div> -->
                <span>
                    {{ translate('messages.Update_Deliveryman') }}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <form class="validate-form" action="{{ route('admin.delivery-man.update', [$delivery_man['id']]) }}"
            id="deliveryman-form" method="post" enctype="multipart/form-data">
            <!-- <form id="deliveryman-form" method="post" -->
            @csrf
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="mb-1">
                            <!-- <span class="card-title-icon"><i class="tio-user"></i></span> -->
                            <span>
                                {{ translate('general_info') }}
                            </span>
                        </h3>
                        {{--                        <p class="fs-12 gray-dark m-0"> --}}
                        {{--                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam odio tellus, laoreet --}}
                        {{--                        </p> --}}
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
                                                for="exampleFormControlInput1">{{ translate('messages.first_name') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" value="{{ $delivery_man['f_name'] }}" name="f_name"
                                                class="form-control h--45px"
                                                placeholder="{{ translate('messages.first_name') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group m-0">
                                            <label class="input-label"
                                                for="exampleFormControlInput1">{{ translate('messages.last_name') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" value="{{ $delivery_man['l_name'] }}" name="l_name"
                                                class="form-control h--45px"
                                                placeholder="{{ translate('messages.last_name') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group m-0">
                                            <label class="input-label"
                                                for="exampleFormControlInput1">{{ translate('messages.email') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" value="{{ $delivery_man['email'] }}" name="email"
                                                class="form-control h--45px"
                                                placeholder="{{ translate('Ex:_ex@example.com') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group m-0">
                                            <label class="input-label"
                                                for="exampleFormControlInput1">{{ translate('messages.deliveryman_type') }}
                                                <span class="text-danger">*</span></label>
                                            <select name="earning" class="form-control h--45px" required>
                                                <option value="1" {{ $delivery_man->earning ? 'selected' : '' }}>
                                                    {{ translate('messages.freelancer') }}</option>
                                                <option value="0" {{ $delivery_man->earning ? '' : 'selected' }}>
                                                    {{ translate('messages.salary_based') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group m-0">
                                            <label class="input-label"
                                                for="exampleFormControlInput1">{{ translate('messages.zone') }} <span
                                                    class="text-danger">*</span></label>
                                            <select name="zone_id" id="dm_zone_id" class="form-control js-select2-custom h--45px" data-search-placeholder="{{ translate('search_zone') }}" data-placeholder="{{ translate('messages.select_zone') }}" required>
                                                @php
                                                    $dm_zone = \App\Models\Zone::withoutGlobalScope(\App\Scopes\ZoneScope::class)->where('status', 1)->where('id', $delivery_man->zone_id)->first(['id', 'name']);
                                                @endphp
                                                @if ($dm_zone)
                                                    <option value="{{ $dm_zone->id }}" selected>{{ $dm_zone->name }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6" id="shift-view">
                                        <div class="form-group m-0">
                                            <label class="input-label"
                                                for="exampleFormControlInput1">{{ translate('messages.Working Shift') }}
                                                <span class="text-danger">*</span></label>
                                            <div class="multi-select-container">
                                                <div class="select-box">
                                                    <div class="tags-container"></div>
                                                    <span class="arrow"><i class="tio-down-ui fs-10"></i></span>
                                                </div>

                                                <div class="dropdown-list">
                                                    @php
                                                        $dm_shifts = $delivery_man->shifts->pluck('id')->toArray();
                                                    @endphp
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
                                                                data-name="{{ $shift->name }}" data-time-range="{{ \App\CentralLogics\Helpers::time_format($shift->start_time) }} - {{ \App\CentralLogics\Helpers::time_format($shift->end_time) }}"
                                                                {{ in_array($shift->id, $dm_shifts) ? 'checked' : '' }}>
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
                                    <div class="mb-4 text-center">
                                        <h5 class="mb-1">
                                            {{ translate('Image ') }} <span class="text-danger">*</span>
                                        </h5>
                                        <p class="mb-0 fs-12 gray-dark">{{ translate('Upload your Business Logo') }}</p>
                                    </div>
                                    <div class="text-center">
                                        @include('admin-views.partials._image-uploader', [
                                            'id' => 'image-input',
                                            'name' => 'image',
                                            'ratio' => '1:1',
                                            'isRequired' => $delivery_man['image_full_url'] ? false : true,
                                            'existingImage' => $delivery_man['image_full_url'] ?? null,
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
            <div class="card mt-3">
                <div class="card-header">
                    <div>
                        <h3 class="mb-1 gap-1">
                            <!-- <span class="card-title-icon"><i class="tio-user"></i></span> -->
                            <span>
                                {{ translate('messages.Identification_Information') }}
                            </span>
                        </h3>
                        {{--                        <p class="m-0 fs-12 gray-dark"> --}}
                        {{--                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam odio tellus, laoreet --}}
                        {{--                        </p> --}}
                    </div>
                </div>
                @csrf
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-12">
                            <div class="p-xxl-20 p-12 global-bg-box rounded h-100">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-group m-0">
                                            <label class="input-label"
                                                for="exampleFormControlInput1">{{ translate('messages.vehicle') }} <span
                                                    class="text-danger">*</span></label>
                                            <select name="vehicle_id" class="form-control js-select2-custom h--45px" data-search-placeholder="{{ translate('search_vehicle') }}"
                                                required>
                                                <option value="" readonly="true" hidden="true">
                                                    {{ translate('messages.select_vehicle') }}</option>
                                                @foreach (\App\Models\Vehicle::where('status', 1)->get(['id', 'type']) as $v)
                                                    <option value="{{ $v->id }}"
                                                        {{ $v->id == $delivery_man->vehicle_id ? 'selected' : '' }}>
                                                        {{ $v->type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group m-0">
                                            <label class="input-label"
                                                for="exampleFormControlInput1">{{ translate('messages.identity_type') }}
                                                <span class="text-danger">*</span></label>
                                            <select name="identity_type" class="form-control h--45px" required>
                                                <option value="passport"
                                                    {{ $delivery_man['identity_type'] == 'passport' ? 'selected' : '' }}>
                                                    {{ translate('messages.passport') }}
                                                </option>
                                                <option value="driving_license"
                                                    {{ $delivery_man['identity_type'] == 'driving_license' ? 'selected' : '' }}>
                                                    {{ translate('messages.driving_license') }}
                                                </option>
                                                <option value="nid"
                                                    {{ $delivery_man['identity_type'] == 'nid' ? 'selected' : '' }}>
                                                    {{ translate('messages.nid') }}
                                                </option>
                                                {{-- <option value="restaurant_id"
                                                    {{ $delivery_man['identity_type'] == 'restaurant_id' ? 'selected' : '' }}>
                                                    {{ translate('messages.restaurant_id') }}
                                                </option> --}}
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group m-0">
                                            <label class="input-label"
                                                for="exampleFormControlInput1">{{ translate('messages.identity_number') }}
                                                <span class="text-danger">*</span></label>
                                            <input type="text" name="identity_number"
                                                value="{{ $delivery_man['identity_number'] }}"
                                                class="form-control h--45px"
                                                placeholder="{{ translate('messages.Ex:DH-23434-LS') }} " required>
                                        </div>
                                    </div>
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
                                    data-field-name="identity_image[]"
                                    data-existng-count="{{ count($delivery_man['identity_image_full_url']) }}"
                                    data-max-count="5" data-accept="{{ IMAGE_EXTENSION }}"
                                    data-max-fileSize="{{ MAX_FILE_SIZE }}"
                                    data-doc-image="{{ dynamicAsset('assets/admin/img/document.svg') }}">
                                    @if (!empty($delivery_man->identity_image))
                                        @foreach ($delivery_man['identity_image_full_url'] as $index => $identityImage)
                                            @if (!empty($identityImage))
                                                <div class="spartan_item_wrapper" style="margin-bottom: 20px;">
                                                    <div style="position: relative;">
                                                        <div class="spartan_item_loader" style="display: none;"></div>
                                                        <label class="file_upload has-image"
                                                            style="width: 100%; height: 100px; border: 2px dashed #ddd; border-radius: 3px; cursor: pointer; text-align: center; overflow: hidden; padding: 5px; margin-top: 5px; margin-bottom: 5px; position: relative; display: flex; align-items: center; margin: auto; justify-content: center; flex-direction: column; aspect-ratio: 2/1;">
                                                            <a href="javascript:void(0)"
                                                                data-existing-name="{{ basename(parse_url($identityImage, PHP_URL_PATH)) }}"
                                                                class="spartan_remove_row_existing z-index-2"
                                                                style="right: -10px;top: -10px;background: rgb(237, 60, 32) !important;border-radius: 50px;width: 30px;height: 30px;line-height: 30px;text-align: center;text-decoration: none;color: rgb(255, 255, 255);position: absolute !important;">
                                                                <i class="tio-clear"></i>
                                                            </a>
                                                            <div class="overlay"
                                                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: none; justify-content: center; align-items: center; z-index: 1;">
                                                                <a href="javascript:void(0)"
                                                                    class="btn btn-outline-info icon-btn view-identity-image"
                                                                    data-url="{{ $identityImage }}"
                                                                    style="background: white; border-radius: 10%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;"><i
                                                                        class="tio-invisible"></i></a>
                                                            </div>
                                                            <img style="width: 100%; vertical-align: middle;"
                                                                 src="{{ (isset($delivery_man['identity_image_full_url'][$index]) && preg_match('/\.(pdf|xlsx|xls|doc|docx)$/i', $delivery_man['identity_image_full_url'][$index])) ? dynamicAsset('assets/admin/img/document.svg') : $identityImage }}"
                                                                 class="img_">
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif

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
                'additional_data' => $additional_data ?? [],
                'additional_documents' => json_decode($delivery_man['additional_documents'] ?? '[]', true),
            ])

            <div class="card mt-3">
                <div class="card-header">
                    <div>
                        <h3 class="mb-1">
                            <!-- <span class="card-header-icon"><i class="tio-user"></i></span> -->
                            <span>{{ translate('messages.account_info') }}</span>
                        </h3>
                        {{--                        <p class="fs-12 m-0 gray-dark"> --}}
                        {{--                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam odio tellus, laoreet --}}
                        {{--                        </p> --}}
                    </div>
                </div>
                <div class="card-body">
                    <div class="p-xxl-20 p-12 global-bg-box rounded">
                        <div class="row g-3">
                            <div class="col-sm-6 col-lg-4">
                                <div class="form-group m-0 iti_flat-bg">
                                    <label class="input-label"
                                        for="exampleFormControlInput1">{{ translate('messages.phone') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="tel" id="phone" name="phone"
                                        value="{{ $delivery_man['phone'] }}" class="form-control h--45px"
                                        placeholder="{{ translate('Ex:_017********') }}" pattern="^\+[1-9][0-9]{6,14}$" required>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4">

                                <div class="js-form-message form-group m-0">
                                    <label class="input-label"
                                        for="signupSrPassword">{{ translate('messages.password') }} <span
                                            class="text-danger {{ $delivery_man['password'] ? 'd-none' : '' }}">*</span>
                                        <span class="input-label-secondary ps-1" data-toggle="tooltip"
                                            title="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"><i
                                                class="tio-info text-muted fs-14"></i></span>
                                    </label>

                                    <div class="input-group input-group-merge">
                                        <input {{ $delivery_man['password'] ? '' : 'required' }} type="password"
                                            class="js-toggle-password form-control h--45px" name="password"
                                            id="signupSrPassword" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                            title="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"
                                            placeholder="{{ translate('messages.password_length_8+') }}"
                                            aria-label="8+ characters required"
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
                                </div>
                            </div>
                            <!-- Static -->
                            <div class="col-sm-6 col-lg-4">

                                <div class="js-form-message form-group m-0">
                                    <label class="input-label"
                                        for="signupSrConfirmPassword">{{ translate('messages.confirm_password') }} <span
                                            class="text-danger {{ $delivery_man['password'] ? 'd-none' : '' }}">*</span>
                                    </label>

                                    <div class="input-group input-group-merge">
                                        <input {{ $delivery_man['password'] ? '' : 'required' }} type="password"
                                            class="js-toggle-password form-control h--45px" name="confirmPassword"
                                            id="signupSrConfirmPassword" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                            title="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"
                                            placeholder="{{ translate('messages.password_length_8+') }}"
                                            aria-label="8+ characters required"
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
                            <!-- Static -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="btn--container mt-4 justify-content-end">
                <button type="reset" id="reset_btn" class="btn btn--reset">{{ translate('messages.reset') }}</button>
                <button type="submit" class="btn btn--primary"><i class="tio-save"></i>
                    {{ translate('messages.submit') }}</button>
            </div>
        </form>
    </div>

@endsection

@push('script_2')
    <script src="{{ dynamicAsset('assets/admin/js/spartan-multi-image-picker.js') }}"></script>
    <script src="{{ dynamicAsset('assets/admin/js/view-pages/multiple-image-upload.js') }}"></script>
    <script>
        "use strict";
        $('#exampleInputPassword ,#exampleRepeatPassword').on('keyup', function() {
            let pass = $("#exampleInputPassword").val();
            let passRepeat = $("#exampleRepeatPassword").val();
            if (pass == passRepeat) {
                $('.pass').hide();
            } else {
                $('.pass').show();
            }
        });

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
            let $newSlotContainers = $(targetSection + " .custom_upload_input").filter(function() {
                return $(this).find('.existing-image-name').length === 0;
            });
            let emptyNewSlots = 0;
            $newSlotContainers.each(function() {
                const $inp = $(this).find("input[type='file']");
                if ($inp.length && $inp.prop('files').length === 0) {
                    emptyNewSlots++;
                }
            });

            uploadColorImage(thisData)

            const totalExisting = $(targetSection + ' .existing-image-name').length;
            const totalNewSelected = $(targetSection + " input[type='file'][name='" + thisData.name + "']").filter(
                function() {
                    return this.files && this.files.length > 0;
                }).length;
            const totalCount = totalExisting + totalNewSelected;

            if (totalCount >= IDENTITY_MAX) {
                $newSlotContainers.each(function() {
                    const $inp = $(this).find("input[type='file']");
                    if ($inp.length && $inp.prop('files').length === 0) {
                        $(this).closest('[class*="col-"]').remove();
                    }
                });
                return;
            }

            if (emptyNewSlots === 0) {

                let maxIndex = 0;
                $(targetSection + " input[type='file'][name='" + thisData.name + "']").each(function() {
                    const idx = parseInt(this.dataset.index || '0', 10);
                    if (!isNaN(idx) && idx > maxIndex) maxIndex = idx;
                });
                let datasetIndex = maxIndex + 1;

                let newHtmlData = `<div class="col-sm-6 col-md-4 col-lg-3 col-xxl-2">
                                <div class="custom_upload_input custom_upload_preview  position-relative bg-white border-dashed-2">
                                    <input type="file" name="${thisData.name}" class="custom-upload-input-file action-add-more-image" data-index="${datasetIndex}" data-imgpreview="additional_Image_${datasetIndex}"
                                        accept="{{ DOCUMENT_EXTENSION . ', ' . IMAGE_EXTENSION }}" data-target-section="${targetSection}">

                                    <span style="right: -10px;top: -10px;background: rgb(237, 60, 32) !important;border-radius: 50px;width: 30px;height: 30px;line-height: 30px;text-align: center;text-decoration: none;color: rgb(255, 255, 255);position: absolute !important;" class="delete_file_input delete_file_input_section btn btn-outline-danger btn-sm square-btn d-none">
                                        <i class="tio-clear"></i>
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

                $('.action-add-more-image').off('change').on('change', function() {
                    let parentDiv = $(this).closest('div');
                    parentDiv.find('.delete_file_input').removeClass('d-none');
                    parentDiv.find('.delete_file_input').fadeIn();
                    addMoreImage(this, $(this).data('target-section'))
                });
            }

            elementCustomUploadInputFileByID.on('change', function() {
                if (parseFloat($(this).prop('files').length) !== 0) {
                    let parentDiv = $(this).closest('div');
                    parentDiv.find('.delete_file_input').fadeIn();
                }
            })

            $('.delete_file_input_section').click(function() {
                $(this).closest('div').parent().remove();
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

                            <span style="right: -10px;top: -10px;background: rgb(237, 60, 32) !important;border-radius: 50px;width: 30px;height: 30px;line-height: 30px;text-align: center;text-decoration: none;color: rgb(255, 255, 255);position: absolute !important;" class="delete_file_input delete_file_input_section btn btn-outline-danger btn-sm square-btn d-none">
                                <i class="tio-clear"></i>
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

        (function() {
            const targetSection = '#additional_Image_Section';
            const totalExisting = $(targetSection + ' .existing-image-name').length;
            const totalNewSelected = $(targetSection + " input[type='file'][name='identity_image[]']").filter(
                function() {
                    return this.files && this.files.length > 0;
                }).length;
            const totalCount = totalExisting + totalNewSelected;
            if (totalCount >= IDENTITY_MAX) {
                $(targetSection + " .custom_upload_input").filter(function() {
                    return $(this).find('.existing-image-name').length === 0 && $(this).find(
                        "input[type='file']").prop('files').length === 0;
                }).closest('[class*="col-"]').remove();
            }
        })();

        function uploadColorImage(thisData = null) {
            if (thisData && thisData.files && thisData.files[0]) {
                if (thisData.files[0].size > 2097152) {
                    toastr.error('{{ translate('messages.Max_file_size_is_2mb') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    thisData.value = "";
                    return;
                }

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

        $('.remove-existing-doc').on('click', function(e) {
            e.preventDefault();
            const $item = $(this).closest('.pdf-single');
            const key = $(this).data('key');
            const file = $(this).data('file');

            $('<input>').attr({
                type: 'hidden',
                name: `removed_additional_documents[${key}][]`,
                value: file
            }).appendTo('#deliveryman-form');

            $item.remove();

            $(window).trigger('resize');
        });

        $(document).on('click', '.pdf-single .pdf-info', function() {
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

        $(document).on('click', '.view-identity-image', function(e) {
            e.preventDefault();
            let fileUrl = $(this).data('url');
            if (fileUrl) {
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
            }
        });

        $(document).on('click', '.close-modal_img', function() {
            $(this).closest('.image-modal-overlay').remove();
        });

        $(document).on('click', '.image-modal-overlay', function(e) {
            if ($(e.target).hasClass('image-modal-overlay')) {
                $(this).remove();
            }
        });

        // CSS for overlay hover
        // CSS for overlay hover
        // CSS for overlay hover
        $('<style>.file_upload .overlay { display: flex !important; opacity: 0; transition: opacity 0.3s ease; pointer-events: none; } .file_upload.has-image:hover .overlay { opacity: 1; pointer-events: auto; } .view-identity-image { background: #006fbd !important; color: white !important; border-color: #006fbd !important; }</style>')
            .appendTo('head');

        $('#deliveryman-form').on('submit', function(e) {
            if (typeof FormValidation !== 'undefined' && !FormValidation.validateForm(this)) {
                return;
            }
            e.preventDefault();
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

            let formData = new FormData(this);

            if (passDisabled) pass.prop('disabled', false);
            if (confirmDisabled) confirmPass.prop('disabled', false);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{ route('admin.delivery-man.update', [$delivery_man['id']]) }}',
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
                        toastr.success('{{ translate('deliveryman_updated_successfully!') }}', {
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
            $('#viewer').attr('src',
                '{{ dynamicStorage('storage/app/public/delivery-man') }}/{{ $delivery_man['image'] }}');
        })
    </script>
    <script src="{{ dynamicAsset('assets/admin') }}/js/file-preview/pdf.min.js"></script>
    <script src="{{ dynamicAsset('assets/admin') }}/js/file-preview/pdf-worker.min.js"></script>
    <script src="{{ dynamicAsset('assets/admin') }}/js/file-preview/multiple-document-upload.js"></script>
    <script src="{{ dynamicAsset('assets/admin/js/view-pages/multiple-image-upload.js') }}"></script>
    <script>
        $(document).on('click', '.spartan_remove_row_existing', function() {
            var existingName = $(this).data('existing-name');
            if (existingName) {
                $('<input>', {
                    type: 'hidden',
                    name: 'deleted_images[]',
                    value: existingName
                }).appendTo('form#deliveryman-form');
            }
            var $wrapper = $(this).closest('.spartan_item_wrapper');
            var $picker = $wrapper.closest('.multi_image_picker');
            $wrapper.remove();
            $picker.trigger('remove-spartan-element');
            $(window).trigger('resize');
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

                if (val.length > 0) {
                    rulesContainer.style.display = "block";
                } else {
                    rulesContainer.style.display = "none";
                }

                updateRule(rules.length, val.length >= 8);
                updateRule(rules.lower, /[a-z]/.test(val));
                updateRule(rules.upper, /[A-Z]/.test(val));
                updateRule(rules.number, /\d/.test(val));
                updateRule(rules.symbol, /[!@#$%^&*(),.?":{}|<>]/.test(val));

            });

            passwordInput.addEventListener("blur", function() {
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

            $("#additional_Image_Section").on("click", ".view_btn", function(e) {
                e.preventDefault();
                var $container = $(this).closest(".custom_upload_input");
                var src = $container.find("img").attr("src");

                if (src) {
                    $(".image-modal-overlay").remove();
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

            $("body").on("click", ".close-modal_img", function() {
                $(this).closest(".image-modal-overlay").remove();
            });

            $("body").on("click", ".image-modal-overlay", function(e) {
                if ($(e.target).hasClass("image-modal-overlay")) {
                    $(this).remove();
                }
            });

            $("#additional_Image_Section").on("click", ".edit_btn", function() {
                var $container = $(this).closest(".custom_upload_input");
                var $fileInput = $container.find('input[type="file"]');

                if ($fileInput.length) {
                    $fileInput.trigger("click");
                }
            });

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

                // **ADDED: Update shift info after any checkbox change**
                updateShiftInfoWrapper();
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

            // **ADDED: Helper function to update shift info based on current state**
            function updateShiftInfoWrapper() {
                var isFullDay = $container.find('.full-day-checkbox').prop('checked');
                var selectedNames = [];
                if (isFullDay) {
                    selectedNames.push("{{ translate('messages.full_day') }}");
                } else {
                    $container.find('.slot-checkbox:checked').each(function() {
                        selectedNames.push($(this).data('name') + ' (' + $(this).data('time-range') + ')');
                    });
                }
                updateShiftInfo(isFullDay, selectedNames);
            }

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

            // **ADDED: Use MutationObserver to detect when common JS updates tags**
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        // Tags container changed, update shift info
                        updateShiftInfoWrapper();
                    }
                });
            });

            // Start observing the tags container for changes
            observer.observe($tagsContainer[0], {
                childList: true,
                subtree: true
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
                        selectedNames.push($(this).data('name') + ' (' + $(this).data('time-range') + ')');
                    });
                }
                updateShiftInfo(isFullDay, selectedNames);
            }

            // Trigger on change and toggle initially
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


