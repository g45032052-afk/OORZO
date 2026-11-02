@extends('layouts.vendor.app')

@section('title', translate('Update delivery-man'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-edit"></i> {{translate('messages.update_deliveryman')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <form action="javascript:" method="post"
                enctype="multipart/form-data" id="deliaveryman_form">
            @csrf
            <div class="row g-2">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-title-icon"><i class="tio-user"></i></span>
                                <span>
                                    {{translate('messages.General_Information')}}
                                </span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="form-label" for="exampleFormControlInput1">{{translate('messages.first_name')}} <span class="text-danger ps-3px">*</span></label>
                                        <input type="text" value="{{$delivery_man['f_name']}}" name="f_name"
                                                class="form-control h--45px" placeholder="{{translate('messages.first_name')}}"
                                                required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="form-label" for="exampleFormControlInput1">{{translate('messages.last_name')}} <span class="text-danger ps-3px">*</span></label>
                                        <input type="text" value="{{$delivery_man['l_name']}}" name="l_name"
                                                class="form-control h--45px" placeholder="{{translate('messages.last_name')}}"
                                                required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="form-label" for="exampleFormControlInput1">{{translate('messages.email')}} <span class="text-danger ps-3px">*</span></label>
                                        <input type="email" value="{{$delivery_man['email']}}" name="email" class="form-control h--45px"
                                                placeholder="{{ translate('messages.Ex :') }} ex@example.com"
                                                required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="form-label" for="exampleFormControlInput1">{{translate('messages.identity_type')}}</label>
                                        <select name="identity_type" class="form-control h--45px">
                                            <option
                                                value="passport" {{$delivery_man['identity_type'] == 'passport' ? 'selected' : ''}}>
                                                {{translate('messages.passport')}}
                                            </option>
                                            <option
                                                value="driving_license" {{$delivery_man['identity_type'] == 'driving_license' ? 'selected' : ''}}>
                                                {{translate('messages.driving_license')}}
                                            </option>
                                            <option value="nid" {{$delivery_man['identity_type'] == 'nid' ? 'selected' : ''}}>{{translate('messages.nid')}}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="form-label" for="exampleFormControlInput1">{{translate('messages.identity_number')}} <span class="text-danger ps-3px">*</span></label>
                                        <input type="text" name="identity_number" value="{{$delivery_man['identity_number']}}"
                                                class="form-control h--45px"
                                                placeholder="{{ translate('messages.Ex :') }} DH-23434-LS"
                                                required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="form-label m-0" for="exampleFormControlInput1">{{translate('messages.identity_image')}}
                                <small class="text-danger">* ( {{translate('messages.ratio')}} 190x120 )</small></h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-2" id="coba">
                                @foreach($delivery_man['identity_image_full_url'] as $img)
                                    <div class="col-6 col-sm-4 spartan_item_wrapper">
                                        <img class="initial-77" src="{{$img}}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header py-3">
                            <h5 class="form-label mb-0">

                                {{translate('messages.Delivery_Man_Image')}}
                                <small class="text-danger">* ({{translate('messages.Ratio')}}  1:1 )</small>
                            </h5>
                        </div>
                        <div class="card-body pt-0 d-flex flex-column">
                                <center class="py-3 my-auto">
                                    <img class="initial-78" id="viewer"
                                         src="{{ $delivery_man['image_full_url'] }}" alt="delivery-man image"/>
                                </center>
                                <div class="custom-file mt-0">
                                    <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label" for="customFileEg1">{{translate('messages.choose_file')}}</label>
                                </div>
                        </div>
                    </div>
                </div>
                <div class="card col-lg-12">
                    <div class="card-header">
                        <div>
                            <h3 class="mb-1">
                                <!-- <span class="card-header-icon"><i class="tio-user"></i></span> -->
                                <span>{{ translate('messages.Account_Information') }}</span>
                            </h3>
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
                                            <input type="tel" name="phone" id="phone" value="{{$delivery_man['phone']}}"
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
                            <div class="btn--container mt-3 justify-content-end">
                                <button type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                                <button type="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

@endsection

@push('script_2')
    <script src="{{dynamicAsset('assets/admin/js/spartan-multi-image-picker.js')}}"></script>
    <script>
        "use strict";
        $("#customFileEg1").change(function () {
            readURL(this);
        });


        $(function () {
            $("#coba").spartanMultiImagePicker({
                fieldName: 'identity_image[]',
                maxCount: 5,
                rowHeight: '120px',
                groupClassName: 'col-6 col-sm-4',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{dynamicAsset('assets/admin/img/100x100/user2.png')}}',
                    width: '100%'
                },
                dropFileLabel: "Drop Here",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
                onExtensionErr: function (index, file) {
                    toastr.error('Please only input png or jpg type file', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function (index, file) {
                    toastr.error('File size too big', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });

        $('#deliaveryman_form').on('submit', function (e) {
            const newFilesCount = $('#coba input[type="file"]').filter(function () {
                return this.files && this.files.length > 0;
            }).length;

            const existingFilesCount = $('#coba .spartan_item_wrapper img').length;

            if ((newFilesCount + existingFilesCount) === 0) {
                toastr.error('Please upload at least one Identity image', {
                    CloseButton: true,
                    ProgressBar: true
                });
                e.preventDefault();
                return false;
            }
            let $submitButton = $(this).find('button[type="submit"]');
            $submitButton.attr('disabled', true).text('Submitting...');
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('vendor.delivery-man.update', [$delivery_man['id']])}}',
                // data: $('#food_form').serialize(),
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                        $submitButton.attr('disabled', false).text('Submit');
                    } else if(data.message){
                        toastr.success(data.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function () {
                            location.href = '{{route('vendor.delivery-man.list')}}';
                        }, 2000);
                    }
                },
                error: function (xhr, status, error) {
                    $submitButton.attr('disabled', false).text('Submit');
                    toastr.error(error, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
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
            const submitBtn = document.querySelector("button[type='submit']");

            function validateMatch() {
                if (confirmInput.value.length === 0) {
                    feedback.classList.add("d-none");
                    return;
                }

                if (confirmInput.value === passwordInput.value) {
                    feedback.classList.remove("text-danger");
                    feedback.classList.add("text-success");
                    feedback.textContent = "{{ translate('Passwords match.') }}";
                    submitBtn.disabled = false;
                    feedback.classList.remove("d-none");
                } else {
                    feedback.classList.remove("text-success");
                    feedback.classList.add("text-danger");
                    feedback.textContent = "{{ translate('Passwords do not match.') }}";
                    submitBtn.disabled = true;
                    feedback.classList.remove("d-none");
                }
            }

            confirmInput.addEventListener("input", validateMatch);
            passwordInput.addEventListener("input", validateMatch); // In case password changes after confirm input
        });
    </script>
@endpush
