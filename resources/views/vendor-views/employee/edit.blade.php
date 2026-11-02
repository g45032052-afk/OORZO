@extends('layouts.vendor.app')
@section('title','Employee Edit')


@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
     <div class="page-header">
        <h2 class="page-header-title text-capitalize">
            <div class="card-header-icon d-inline-flex mr-2 img">
                <img src="{{dynamicAsset('assets/admin/img/resturant-panel/page-title/employee-role.png')}}" alt="public">
            </div>
            <span>
                {{translate('messages.Employee_update')}}
            </span>
        </h2>
    </div>
    <!-- End Page Header -->

    <!-- Content Row -->
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
            <form action="{{route('vendor.employee.update',[$e['id']])}}" method="post" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="f_name">{{translate('messages.first_name')}}</label>
                            <input type="text" name="f_name" value="{{$e['f_name']}}" class="form-control h--45px" id="f_name"
                                    placeholder="{{translate('messages.first_name')}}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="l_name">{{translate('messages.last_name')}}</label>
                            <input type="text" name="l_name" value="{{$e['l_name']}}" class="form-control h--45px" id="l_name"
                                    placeholder="{{translate('messages.last_name')}}">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="phone">{{translate('messages.phone')}}</label>
                            <input type="tel" value="{{$e['phone']}}" required name="phone" class="form-control h--45px" id="phone"
                                    placeholder="{{ translate('Ex : +88017********') }}">
                        </div>

                        <div class="form-group mb-md-0">
                            <label class="form-label" for="role_id">{{translate('messages.Role')}}</label>
                            <select class="form-control h--45px w-100" name="role_id">
                                    <option value="" selected disabled>{{translate('messages.select_Role')}}</option>
                                    @foreach($rls as $r)
                                        <option
                                            value="{{$r->id}}" {{$r['id']==$e['employee_role_id']?'selected':''}}>{{$r->name}}</option>
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
                                    'existingImage' => $e?->image_full_url,
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
                        <div class="row gy-2">
                            <div class="col-md-4">
                                <label class="form-label" for="email">{{translate('messages.email')}}</label>
                                <input type="email" value="{{$e['email']}}" name="email" class="form-control h--45px" id="email" placeholder="{{ translate('messages.Ex :') }} ex@gmail.com">
                            </div>
                            <div class="col-md-4">
                                <div class="form-group m-0">
                                    <div class="js-form-message form-group">
                                        <label class="input-label"
                                            for="signupSrPassword">{{ translate('messages.password') }}
                                            <span class="input-label-secondary ps-1" data-toggle="tooltip" title="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"><img src="{{dynamicAsset('assets/admin/img/info-circle.svg')}}" alt="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"></span>

                                        </label>

                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control h--45px" name="password"
                                                id="signupSrPassword"
                                                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"

                                                placeholder="{{ translate('messages.Ex:_7+_Character') }}"
                                                aria-label="{{translate('messages.password_length_7+')}}"
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
                            </div>
                            <div class="col-md-4">
                                <div class="js-form-message form-group">
                                    <label class="input-label"
                                        for="signupSrConfirmPassword">{{ translate('messages.confirm_password') }}</label>

                                    <div class="input-group input-group-merge">
                                        <input type="password" class="js-toggle-password form-control h--45px" name="confirmPassword"
                                            id="signupSrConfirmPassword"
                                            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"

                                            placeholder="{{ translate('messages.Ex:_7+_Character') }}"
                                            aria-label="{{translate('messages.password_length_7+')}}"
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="btn--container justify-content-end mt-3">
                    <button type="reset" id="reset_btn" class="btn btn--reset">{{translate('messages.reset')}}</button>
                    <button type="submit" class="btn btn--primary">{{translate('messages.update')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script_2')
    <script>
        "use strict";
        $("#customFileUpload").change(function () {
            readURL(this);
        });


        $(".js-example-theme-single").select2({
            theme: "classic"
        });

        $(".js-example-responsive").select2({
            width: 'resolve'
        });

        $('#reset_btn').click(function(){
            $('#viewer').attr('src','{{dynamicStorage('storage/app/public/vendor')}}/{{$e['image']}}');
        })
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
