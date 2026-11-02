@extends('layouts.vendor.app')
@section('title', translate('Employee Add'))

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
     <div class="page-header">
        <h2 class="page-header-title text-capitalize">
            <div class="card-header-icon d-inline-flex mr-2 img">
                <img src="{{dynamicAsset('assets/admin/img/resturant-panel/page-title/employee-role.png')}}" alt="public">
            </div>
            <span>
                {{translate('Add New Employee')}}
            </span>
        </h2>
    </div>
    <!-- End Page Header -->

    <!-- Content Row -->
    <form action="{{route('vendor.employee.add-new')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <span class="card-header-icon">
                        <i class="tio-user"></i>
                    </span>
                    <span>
                        {{ translate('General Information') }}
                    </span>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="fname">{{translate('messages.first_name')}}</label>
                            <input type="text" name="f_name" class="form-control h--45px" id="fname"
                                    placeholder="{{ translate('Ex : Sakeef Ameer') }}" value="{{old('f_name')}}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="lname">{{translate('messages.last_name')}}</label>
                            <input type="text" name="l_name" class="form-control h--45px" id="lname" value="{{old('l_name')}}"
                                    placeholder="{{ translate('Ex : Prodhan') }}" value="{{old('name')}}">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="phone">{{translate('messages.phone')}}</label>
                            <input type="tel" name="phone" value="{{old('phone')}}" class="form-control h--45px" id="phone"
                                    placeholder="{{ translate('Ex : +88017********') }}" required>
                        </div>
                        <div class="form-group mb-md-0">
                            <label class="form-label" for="role_id">{{translate('messages.Role')}}</label>
                            <select class="form-control h--45px custom-select2 w-100" name="role_id" required>
                                <option value="" selected disabled>{{translate('messages.select_Role')}}</option>
                                @foreach($rls as $r)
                                    <option value="{{$r->id}}">{{$r->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="h-100 d-flex flex-column">
                            <div class="card h-100">
                                <div class="d-flex flex-column align-items-center gap-3 p-8">
                                        <p class="mb-0 mt-5">{{ translate('Employee image') }} <span class="text-danger">{{ translate('ratio (1:1)') }}</span></p>

                                        @include('admin-views.partials._image-uploader', [
                                    'id' => 'image-input',
                                    'name' => 'image',
                                    'isRequired' => true,
                                    'existingImage' => null,
                                    'ratio' => '1:1',
                                    'imageExtension' => IMAGE_EXTENSION,
                                    'imageFormat' => IMAGE_FORMAT,
                                    'maxSize' => 1,
                                    ])
                                       
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <span class="card-header-icon">
                                <i class="tio-user"></i>
                            </span>
                            <span>
                                {{translate('messages.account_info')}}
                            </span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label" for="email">{{translate('messages.email')}}</label>
                                <input type="email" name="email" value="{{old('email')}}" class="form-control" id="email"
                                        placeholder="{{ translate('Ex : ex@gmail.com') }}" required>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group m-0">
                                    <div class="js-form-message form-group">
                                        <label class="input-label"
                                               for="signupSrPassword">{{ translate('messages.password') }} <span class="text-danger">*</span>
                                            <span class="input-label-secondary ps-1" data-toggle="tooltip" title="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"><i class="tio-info text-muted fs-14"></i></span>

                                        </label>

                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control h--45px" name="password"
                                                   id="signupSrPassword"
                                                   pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"

                                                   placeholder="{{ translate('messages.Ex:_7+_Character') }}"
                                                   aria-label="{{translate('messages.password_length_7+')}}"
                                                   required data-msg="Your password is invalid. Please try again."
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
                                        <ul id="password-rules" class=" gap-1 mt-2 small list-unstyled text-muted" style="display: none;">
                                            <li>
                                                <ul class="d-flex flex-wrap gap-1 list-unstyled">
                                                    <li id="rule-length"><i class="text-danger">&#10060;</i> {{ translate('At least 8 characters') }}</li>
                                                    <li id="rule-lower"><i class="text-danger">&#10060;</i> {{ translate('At least one lowercase letter') }}</li>
                                                    <li id="rule-upper"><i class="text-danger">&#10060;</i> {{ translate('At least one uppercase letter') }}</li>
                                                    <li id="rule-number"><i class="text-danger">&#10060;</i> {{ translate('At least one number') }}</li>
                                                    <li id="rule-symbol"><i class="text-danger">&#10060;</i> {{ translate('At least one symbol') }}</li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="js-form-message form-group">
                                    <label class="input-label"
                                           for="signupSrConfirmPassword">{{ translate('messages.confirm_password') }} <span class="text-danger">*</span></label>

                                    <div class="input-group input-group-merge">
                                        <input type="password" class="js-toggle-password form-control h--45px" name="confirmPassword"
                                               id="signupSrConfirmPassword"
                                               pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"

                                               placeholder="{{ translate('messages.Ex:_7+_Character') }}"
                                               aria-label="{{translate('messages.password_length_7+')}}"
                                               required data-msg="Password does not match the confirm password."
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
                                    <small id="confirm-password-feedback" class="text-danger d-none">{{ translate('Passwords do not match.') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="btn--container justify-content-end mt-3">
                    <button type="reset" id="reset_btn" class="btn btn--reset">{{translate('messages.reset')}}</button>
                    <button type="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

@push('script_2')
    <script>
        "use strict";

        $("#customFileUpload").change(function () {
            readURL(this);
        });

        $('#reset_btn').click(function(){
            location.reload();
        });


    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const passwordInput = document.getElementById("signupSrPassword");
            const rulesContainer = document.getElementById("password-rules");

            const rules = {
                length: document.getElementById("rule-length"),
                lower: document.getElementById("rule-lower"),
                upper: document.getElementById("rule-upper"),
                number: document.getElementById("rule-number"),
                symbol: document.getElementById("rule-symbol"),

            };

            passwordInput.addEventListener("input", function () {
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

            passwordInput.addEventListener("blur", function () {
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
        document.addEventListener("DOMContentLoaded", function () {
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
        document.getElementById('customFileUpload').addEventListener('change', function () {

            const allowedExtensions = "{{ IMAGE_EXTENSION }}"
                .replace(/\s/g, '')
                .toLowerCase()
                .split(',');

            const file = this.files[0];
            const viewer = document.getElementById('viewer');
            const defaultImage = viewer.getAttribute('data-default-src');

            if (!file) {
                viewer.src = defaultImage;
                return;
            }

            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

            if (!allowedExtensions.includes(fileExtension)) {

                this.value = '';

                viewer.src = '';
                setTimeout(() => {
                    viewer.src = defaultImage;
                }, 10);

                toastr.error(
                    'Invalid file type. Allowed types: ' + allowedExtensions.join(', '),
                    'File Not Allowed'
                );

                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                viewer.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    </script>

@endpush
