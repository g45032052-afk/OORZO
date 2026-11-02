@extends('layouts.landing.app')
@section('title', translate('messages.deliveryman_registration'))
@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset('assets/landing') }}/css/style.css"/>
    <link rel="stylesheet" href="{{ dynamicAsset('assets/landing') }}/css/shift-multi-select.css"/>
@endpush

@section('content')
    <!-- Page Header Gap -->
    <div class="h-148px"></div>
    <!-- Page Header Gap -->

    <section class="m-0">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-header-title text-center fs-22 lh-sm mb-0">
                    {{ translate('messages.Deliveryman_Registration_Application') }}
                </h1>
            </div>
            <!-- End Page Header -->

            <form class="card-body" id="deliveryman-form" method="post" enctype="multipart/form-data">
                @csrf

                <div class="card card-body mb-4">
                    <h4 class="text-capitalize fs-18 mb-3">{{ translate('messages.Deliveryman info') }}</h4>
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="row g-4">
                                <div class="col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="form-label font-regular"
                                            for="exampleFormControlInput1">{{ translate('messages.first_name') }}<small
                                                class="text-danger">*</small></label>
                                        <input type="text" name="f_name" class="form-control"
                                            placeholder="{{ translate('messages.first_name') }}" required
                                            value="{{ old('f_name') }}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="form-label font-regular"
                                            for="exampleFormControlInput1">{{ translate('messages.last_name') }}<small
                                                class="text-danger">*</small></label>
                                        <input type="text" name="l_name" class="form-control"
                                            placeholder="{{ translate('messages.last_name') }}" value="{{ old('l_name') }}"
                                            required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="form-label font-regular"
                                            for="exampleFormControlInput1">{{ translate('messages.email') }}<small
                                                class="text-danger">*</small></label>
                                        <input type="email" name="email" class="form-control"
                                            placeholder="{{ translate('messages.Ex :') }} ex@example.com"
                                            value="{{ old('email') }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="form-label font-regular"
                                            for="exampleFormControlInput1">{{ translate('messages.deliveryman_type') }}</label>
                                        <select name="earning" class="form-control">
                                            <option value="1">{{ translate('messages.freelancer') }}</option>
                                            <option value="0">{{ translate('messages.salary_based') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="form-label font-regular"
                                            for="exampleFormControlInput1">{{ translate('messages.zone') }}<small
                                                class="text-danger">*</small></label>
                                        <select name="zone_id" class="form-control js-select2-custom" required
                                            data-placeholder="{{ translate('messages.select_zone') }}">
                                            <option value="" readonly="true" hidden="true">
                                                {{ translate('messages.select_zone') }}</option>
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
                                </div>
                                <div class="col-sm-6 col-12" id="shift-view">
                                    <div class="form-group mb-0 pb-1">
                                        <label class="form-label font-regular"
                                               for="exampleFormControlInput1">{{ translate('messages.Working Shift') }}<small
                                                class="text-danger">*</small></label>
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
                                <div class="col-12">
                                    <div class="alert py-2 bg-warning-soft rounded" role="alert">
                                        <i class="tio-info fs-16 text-warning"></i>
                                        <span class="mb-0 fs-13" id="shift-info-text">
                                            {{ translate('Please select a shift to see availability.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="p-3 p-xxl-4 border rounded-10 h-100">
                                <div class="pb-lg-1">
                                    <div class="mb-4">
                                        <h5 class="fs-14 mb-1">
                                            {{ translate('Deliveryman Image') }} <span class="text-danger">*</span>
                                        </h5>
                                        <p class="mb-0 fs-12 text-muted">{{ translate('Upload your Business Logo') }}
                                        </p>
                                    </div>
                                    <div class="text-center">
                                        @include('partials._image-uploader', [
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

                <div class="card card-body mb-4">
                    <h4 class="text-capitalize fs-18 mb-3">{{ translate('messages.Identification_Information') }}</h4>
                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="form-group mb-0">
                                <label class="form-label font-regular"
                                    for="exampleFormControlInput1">{{ translate('messages.Vehicle') }}<small
                                        class="text-danger">*</small></label>
                                <select name="vehicle_id" class="form-control js-select2-custom h--45px" required
                                    data-placeholder="{{ translate('messages.select_vehicle') }}">
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
                            <div class="form-group mb-0">
                                <label class="form-label font-regular"
                                    for="exampleFormControlInput1">{{ translate('messages.identity_type') }}</label>
                                <select name="identity_type" class="form-control">
                                    <option value="passport">{{ translate('messages.passport') }}</option>
                                    <option value="driving_license">{{ translate('messages.driving_license') }}
                                    </option>
                                    <option value="nid">{{ translate('messages.nid') }}</option>
                                    {{-- <option value="restaurant_id">{{ translate('messages.restaurant_id') }}</option> --}}
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group mb-0">
                                <label class="form-label font-regular"
                                    for="exampleFormControlInput1">{{ translate('messages.identity_number') }}
                                    <small class="text-danger">*</small></label>
                                <input type="text" name="identity_number" class="form-control"
                                    value="{{ old('identity_number') }}"
                                    placeholder="{{ translate('messages.Ex :') }} DH-23434-LS" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label class="form-label font-semibold mb-1"
                                    for="exampleFormControlInput1">{{ translate('messages.identity_image') }}
                                    <small class="text-danger">*</small>
                                </label>
                                <p class="fs-12 mb-0">{{ translate('messages.JPG, JPEG, PNG ,WEBP, Less Than 1MB (Ratio 2:1)') }}</p>
                                <div class="position-relative">
                                    <div class="multi_image_picker d-flex gap-4 pt-3 px-2" data-ratio="2/1"
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
                
                @include('partials._custom-inputs')

                <div class="card card-body">
                    <h4 class="text-capitalize fs-18 mb-3">{{ translate('messages.Login info') }}</h4>
                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="form-group mb-0">
                                <label class="form-label font-regular" for="phone">{{ translate('messages.phone') }}<small
                                        class="text-danger">*</small></label>
                                <div class="input-group">
                                    <input type="tel" id="phone"
                                        placeholder="{{ translate('messages.Ex :') }} 017********"
                                        class="form-control" value="{{ old('phone') }}" required>
                                    <input type="hidden" name="phone" id="phone_hidden">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group mb-0">
                                <label class="form-label font-regular"
                                    for="password">{{ translate('messages.password') }} <small
                                        class="text-danger">*</small>

                                </label>
                                <label class="position-relative m-0 d-block input-group-passward">
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
                                <label class="form-label font-regular"
                                    for="confirmPassword">{{ translate('messages.confirm_password') }}
                                    <small class="text-danger">*</small></label>
                                <label class="position-relative m-0 d-block input-group-passward">
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

                <div class="">
                    @php($recaptcha = \App\CentralLogics\Helpers::get_business_settings('recaptcha'))
                    @if (isset($recaptcha) && $recaptcha['status'] == 1)
                        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                        <input type="hidden" name="set_default_captcha" id="set_default_captcha_value"
                            value="0">
                        <div class="row p-2 d-none mb-3" id="reload-captcha">
                            <div class="col-6 pr-0">
                                <input type="text" class="form-control form-control-lg border-0"
                                    name="custome_recaptcha" id="custome_recaptcha"
                                    placeholder="{{ translate('Enter recaptcha value') }}" autocomplete="off"
                                    value="{{ getEnvMode() == 'dev' ? session('six_captcha') : '' }}">
                            </div>
                            <div class="col-6 bg-white rounded d-flex">
                                <img src="<?php echo $custome_recaptcha->inline(); ?>" class="rounded w-100" />
                                <div class="p-3 pr-0 capcha-spin reloadCaptcha">
                                    <i class="tio-cached"></i>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="form-group mb-3">
                            <div class="row p-2" id="reload-captcha">

                                <div class="col-6 pr-0">
                                    <input type="text" class="form-control form-control-lg form-recapcha"
                                        name="custome_recaptcha" id="custome_recaptcha"
                                        placeholder="{{ translate('Enter recaptcha value') }}"
                                        autocomplete="off"
                                        value="{{ getEnvMode() == 'dev' ? session('six_captcha') : '' }}">
                                </div>
                                <div class="col-6 bg-white rounded d-flex">
                                    <img src="<?php echo $custome_recaptcha->inline(); ?>" class="rounded w-100" />
                                    <div class="p-3 pr-0 capcha-spin reloadCaptcha">
                                        <i class="tio-cached"></i>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endif

                </div>

                <div class="d-flex flex-wrap justify-content-end gap-3 pt-4">
                    <button type="reset" id='reset-btn'
                            class="btn btn--reset px-5">{{ translate('Reset') }}
                    </button>
                    <button type="submit"
                        class="btn btn-primary px-5 submitBtn">{{ translate('messages.submit') }}</button>
                </div>

            </form>

        </div>

    </section>
    <!-- Page Header Gap -->
    <div class="h-148px"></div>
    <!-- Page Header Gap -->
@endsection

@push('script_2')

    @if ($errors->any())
        <script>
            @foreach ($errors->all() as $error)
                toastr.error('{{ $error }}');
            @endforeach
        </script>
    @endif

    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#viewer').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function() {
            readURL(this);
        });

            $('#deliveryman-form').on('submit', function (e) {
                e.preventDefault();
                // Capture phone value from intlTelInput
                const phoneInput = document.getElementById('phone');
                const phoneHiddenInput = document.getElementById('phone_hidden');
                const intlTelInputInstance = window.intlTelInputGlobals.getInstance(phoneInput);
                if (intlTelInputInstance) {
                    phoneHiddenInput.value = intlTelInputInstance.getNumber();
                } else {
                    phoneHiddenInput.value = phoneInput.value;
                }
                let formData = new FormData(this);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.post({
                    url: '{{route('deliveryman.store')}}',
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $('#loading').show();
                    },
                    success: function (data) {
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
                            toastr.success('{{ translate('application_placed_successfully') }}', {
                                CloseButton: true,
                                ProgressBar: true
                            });
                            setTimeout(function () {
                                location.reload();
                            }, 2000);
                        }
                    }
            });
        });
    </script>

    <script src="{{ dynamicAsset('assets/landing') }}/js/shift-multi-select.js"></script>
    <script src="{{ dynamicAsset('assets/admin/js/spartan-multi-image-picker.js') }}"></script>
    <script src="{{ dynamicAsset('assets/landing/assets_new/js/multiple-image-upload.js') }}"></script>

    <script src="{{ dynamicAsset('assets/admin') }}/js/file-preview/pdf.min.js"></script>
    <script src="{{ dynamicAsset('assets/admin') }}/js/file-preview/pdf-worker.min.js"></script>
    <script src="{{ dynamicAsset('assets/admin') }}/js/file-preview/multiple-document-upload.js"></script>

    <script src="{{ dynamicAsset('assets/admin') }}/plugins/file-upload/upload-file-new.js"></script>

    <script type="text/javascript">
        $(function() {
            $("#coba").spartanMultiImagePicker({
                fieldName: 'identity_image[]',
                maxCount: 5,
                rowHeight: '120px',
                groupClassName: 'col-lg-2 col-md-4 col-sm-4 col-6',
                maxFileSize: {{ MAX_FILE_SIZE * 1024 * 1024 }},
                placeholderImage: {
                    image: '{{ dynamicAsset('assets/admin/img/400x400/img2.jpg') }}',
                    width: '70%'
                },
                dropFileLabel: "Drop Here",
                onAddRow: function(index, file) {

                },
                onRenderedPreview: function(index) {
                    const item = $('#coba')
                        .find('.spartan_item_wrapper')
                        .eq(index);
                    item.css({
                        width: '13%',
                    });

                    item.find('img').css({
                        width: '100%',
                        objectFit: 'cover'
                    });
                },
                onRemoveRow: function(index) {

                },
                onExtensionErr: function(index, file) {
                    toastr.error(
                    '{{ translate('messages.please_only_input_png_or_jpg_type_file') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function(index, file) {
                    toastr.error('{{ translate('messages.file_size_too_big') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });
    </script>

    @if (isset($recaptcha) && $recaptcha['status'] == 1)
        <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptcha['site_key'] }}"></script>
    @endif
    @if (isset($recaptcha) && $recaptcha['status'] == 1)
        <script>
            $(document).ready(function() {
                $('#signInBtn').click(function(e) {
                    if ($('#set_default_captcha_value').val() == 1) {
                        $('#contact-form-id').submit();
                        return true;
                    }
                    e.preventDefault();
                    if (typeof grecaptcha === 'undefined') {
                        toastr.error(
                            'Invalid recaptcha key provided. Please check the recaptcha configuration.');
                        $('#reload-captcha').removeClass('d-none');
                        $('#set_default_captcha_value').val('1');

                        return;
                    }
                    grecaptcha.ready(function() {
                        grecaptcha.execute('{{ $recaptcha['site_key'] }}', {
                            action: 'submit'
                        }).then(function(token) {
                            $('#g-recaptcha-response').value = token;
                            $('#contact-form-id').submit();
                        });
                    });
                    window.onerror = function(message) {
                        var errorMessage =
                            'An unexpected error occurred. Please check the recaptcha configuration';
                        if (message.includes('Invalid site key')) {
                            errorMessage =
                                'Invalid site key provided. Please check the recaptcha configuration.';
                        } else if (message.includes('not loaded in api.js')) {
                            errorMessage =
                                'reCAPTCHA API could not be loaded. Please check the recaptcha API configuration.';
                        }
                        $('#reload-captcha').removeClass('d-none');
                        $('#set_default_captcha_value').val('1');
                        toastr.error(errorMessage)
                        return true;
                    };
                });
            });
        </script>
    @endif
    <script>
        $(document).on('click', '.reloadCaptcha', function() {
            $.ajax({
                url: "{{ route('reload-captcha') }}",
                type: "GET",
                dataType: 'json',
                beforeSend: function() {
                    $('#loading').show()
                    $('.capcha-spin').addClass('active')
                },
                success: function(data) {
                    $('#reload-captcha').html(data.view);
                },
                complete: function() {
                    $('#loading').hide()
                    $('.capcha-spin').removeClass('active')
                }
            });
        });
    </script>

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
    </script>
@endpush
