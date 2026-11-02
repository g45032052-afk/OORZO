@extends('layouts.admin.app')

@section('title', translate('Admin_Landing_Page'))


@section('content')
<?php
use Illuminate\Support\Facades\File;

$filePath = resource_path('views/layouts/landing/custom/index.blade.php');

$custom_file = File::exists($filePath);
$config = \App\CentralLogics\Helpers::get_business_settings('landing_page');
?>

    <div class="content">
        <form id="theme_form" class="validate-form" enctype="multipart/form-data">
            @csrf
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-start">
                <h1 class="page-header-title text-capitalize">
                    <div class="card-header-icon d-inline-flex mr-2 img">
                        <img src="{{ dynamicAsset('assets/admin/img/landing-page.png') }}" class="mw-26px" alt="public">
                    </div>
                    <span>
                        {{ translate('Admin_Landing_Page') }}
                    </span>
                </h1>
                <div class="text--primary-2 py-1 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#how-it-works">
                    <strong class="mr-2">{{translate('See_how_it_works!')}}</strong>
                    <div>
                        <i class="tio-info text-gray1 fs-16"></i>
                    </div>
                </div>
            </div>
            <div class="js-nav-scroller tabs-slide-wrap tabs-slide-language hs-nav-scroller-horizontal">
                @include('admin-views.landing_page.top_menu.admin_landing_menu')

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
                <div class="card card-body mb-3">
                    <h3 class="mb-1">{{ translate('messages.Admin Landing Page Setup ') }}</h3>
                    <p class="fs-12 mb-0">{{ translate('messages.Here you can manage the Landing Page setup which will be displayed') }}</p>
                </div>

                {{-- old code --}}
                <div class="card card-body">
                    @php($landing_integration_type = \App\CentralLogics\Helpers::get_business_data('landing_integration_type'))
                    @php($redirect_url = \App\CentralLogics\Helpers::get_business_data('landing_page_custom_url'))
                    <div class="__bg-F8F9FC-card mb-20">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div>
                                    <h4 class="mb-1">{{ translate('messages.Choose Admin Landing Page') }}</h4>
                                    <p class="fs-12 mb-0">
                                        {{ translate('messages.Setup which types of admin landing page you want to show.') }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="resturant-type-group border bg-white w-100 d-inline-flex gap-3">
                                    <label class="form-check form--check flex-grow-1">
                                    <input class="form-check-input landing-page {{ getEnvMode() == 'demo' ? 'call-demo' : 'dynamic-checkbox-toggle' }}" type="radio"
                                        name="choose_admin_landing" id="default_landing" data-id="landing-page-default"
                                        data-image-on="{{ dynamicAsset('assets/admin/img/status-on-off.png') }}"
                                        data-image-off="{{ dynamicAsset('assets/admin/img/status-off.png') }}"
                                        data-title-on="{{ translate('Want_to_Turn_On_the_Default_Admin_Landing_Page_?') }}"
                                        data-title-off="{{ translate('Want_to_Turn_Off_the_Default_Admin_Landing_Page_?') }}"
                                        data-text-on="<p>{{ translate('If_enabled,_the_landing_page_will_be_visible_to_everyone') }}</p>"
                                        data-text-off="<p>{{ translate('If_disabled,_the_landing_page_won’t_be_visible_to_anyone') }}</p>"
                                        value="default" {{ isset($config) && $config ? 'checked' : '' }}>
                                        <span class="form-check-label">
                                            {{ translate('messages.Default Landing Page') }}
                                        </span>
                                    </label>

                                    <label class="form-check form--check flex-grow-1">
                                    <input class="form-check-input landing-page  {{ getEnvMode() == 'demo' ? 'call-demo' : 'dynamic-checkbox-toggle' }}" type="radio"
                                        name="choose_admin_landing" id="custom_landing" data-id="landing-page-custom"
                                        data-image-on="{{ dynamicAsset('assets/admin/img/status-on-off.png') }}"
                                        data-image-off="{{ dynamicAsset('assets/admin/img/status-off.png') }}"
                                        data-title-on="{{ translate('Want_to_Turn_On_the_Custom_Admin_Landing_Page_?') }}"
                                        data-title-off="{{ translate('Want_to_Turn_Off_the_Custom_Admin_Landing_Page_?') }}"
                                        data-text-on="<p>{{ translate('If_enabled,_the_landing_page_will_be_visible_to_everyone') }}</p>"
                                        data-text-off="<p>{{ translate('If_disabled,_the_landing_page_won’t_be_visible_to_anyone') }}</p>"
                                        value="custom" {{ isset($config) && !$config ? 'checked' : '' }}>
                                        <span class="form-check-label">
                                            {{ translate('messages.Custom Landing Page') }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (isset($config) && $config == 0)
                    <div class="__bg-F8F9FC-card mb-20">
                        <div class="row g-3 mb-20">
                            <div class="col-lg-6">
                                <div>
                                    <h4 class="mb-1">{{ translate('messages.How to Integrate Custom Landing Page') }}</h4>
                                    <p class="fs-12 mb-0">
                                        {{ translate('messages.Select your preferred method for integrating your custom landing page.') }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="resturant-type-group border bg-white w-100 d-inline-flex gap-3">
                                    <label class="form-check form--check flex-grow-1">
                                        <input class="form-check-input" type="radio" value="url" name="landing_integration_via" {{  $landing_integration_type == 'url'?'checked':''  }}>
                                        <span class="form-check-label">
                                            {{ translate('messages.url') }}
                                        </span>
                                    </label>
                                    <label class="form-check form--check flex-grow-1">
                                        <input class="form-check-input" type="radio" value="file_upload" name="landing_integration_via" {{  $landing_integration_type == 'file_upload'?'checked':''  }}>
                                        <span class="form-check-label">
                                            {{ translate('file_upload') }}
                                        </span>
                                    </label>
                                    <label class="form-check form--check flex-grow-1">
                                        <input class="form-check-input" type="radio" value="none" name="landing_integration_via" {{ $landing_integration_type == 'none' ?'checked':'' }}>
                                        <span class="form-check-label">
                                            {{ translate('none') }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="">
                            <div class="__input-tab {{  $landing_integration_type == 'url'?'active':''  }}" id="url">
                                <div class="form-group mb-20">
                                    <label for="redirect_url_input" class="input-label text-capitalize d-flex gap-1 align-items-center">
                                        <span>
                                            {{ translate('landing_page_url') }}
                                            <span class="text-danger" id="url-asterisk" style="{{ $landing_integration_type == 'url' ? '' : 'display:none' }}">*</span>
                                        </span>
                                        <span class="tio-info text-gray1 fs-16" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('messages.Specify the public URL of your landing page. This link will be used to redirect users to the landing page when they access the system.') }}">
                                        </span>
                                    </label>
                                    <input type="url" id="redirect_url_input" placeholder="{{ translate('messages.Ex: https://stackfood-admin.6amtech.com/') }}" class="form-control h--45px" name="redirect_url" value="{{ $redirect_url }}">
                                </div>
                                <div class="fs-12 text-dark px-3 py-2 rounded bg-info" style="--bs-bg-opacity: 0.1;">
                                    <div class="d-flex gap-2 mb-3">
                                        <span class="text-info lh-1 fs-14">
                                            <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                                        </span>
                                        <h4 class="font-medium mb-0">
                                            {{ translate('messages.Instructions') }}
                                        </h4>
                                    </div>
                                    <ul class="d-flex flex-column gap-2">
                                        <li>
                                            {{ translate('messages.If you want to use URL option. Just host you landing page and copy the page URL and click save information.') }}
                                        </li>
                                        <li>
                                            {{ translate('messages.Without save the changes Landing page can’t update properly and you can’t see the updated preview.') }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="__input-tab {{  $landing_integration_type == 'file_upload'?'active':''  }}" id="file_upload">
                                <div class="card card-body">
                                    <div class="mb-20">
                                        <h5 class="mb-1">{{ translate('messages.Upload PHP File') }}</h5>
                                        <p class="fs-12 mb-0">
                                            {{ translate('messages.Here you need to upload your custome designed PHP file that will work as a Admin Landing Page.') }}
                                        </p>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-lg-6">
                                            <div class="fs-12 text-dark px-3 py-2 rounded bg-info d-flex align-items-center h-100" style="--bs-bg-opacity: 0.1;">
                                                <div>
                                                    <div class="d-flex gap-2 mb-3">
                                                        <span class="text-info lh-1 fs-14">
                                                            <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                                                        </span>
                                                        <h4 class="text-info mb-0">
                                                            {{ translate('messages.Instructions') }}
                                                        </h4>
                                                    </div>
                                                    <ul class="d-flex flex-column gap-2">
                                                        <li>
                                                            {{ translate('messages.Create an html file named index.blade.php and insert your landing page design code and make a zip file.') }}
                                                        </li>
                                                        <li>
                                                            {{ translate('messages.Upload file must be zip file format in and click save information.') }}
                                                        </li>
                                                        <li>
                                                            {{ translate('messages.Without save the changes Landing page can’t update properly and you can’t see the updated preview.') }}
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="__bg-F8F9FC-card text-center">
                                                <div class="file-upload-parent max-w-420">
                                                    <div class="custom-file-upload">
                                                        <input type="file" accept=".zip" data-max-file-size="10MB" name="file_upload">
                                                        <div class="text-center p-3 p-sm-4">
                                                            <div class="mb-20">
                                                                <img width="48" height="48" class="svg" src="{{ dynamicAsset('assets/admin/img/svg/upload-cloud.svg') }}" alt="">
                                                            </div>
                                                            <p class="mb-0 fs-14 mb-1">
                                                                {{ translate('messages.Select a file or') }}
                                                                <span class="font-semibold">{{ translate('messages. Drag & Drop') }} </span>
                                                                {{ translate('messages.here') }}
                                                            </p>
                                                            <div class="mb-0 fs-12">{{ translate('messages.PHP file size no more than 10MB') }}</div>
                                                            <div class="btn btn-outline-primary font-semibold mt-4 trigger_input_btn">
                                                                {{ translate('messages.Select File') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="file-preview-list d-flex flex-column gap-4"></div>
                                                    <div id="file-upload-config" data-icon-src="{{ dynamicAsset('assets/admin/img/file-view.png') }}"></div>
                                                </div>
                                                <div class="mt-4">
                                                    <button type="submit" class="btn btn--primary min-w-120 call-demo">{{ translate('messages.Upload') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if ($custom_file)
                                <div class="row g-1 g-sm-2 mt-2">
                                    <div class="col-6 col-md-4 col-xxl-3">
                                        <div class="card theme-card">
                                            <div class="card-body d-flex justify-content-between">
                                                <h3>
                                                    index.blade.php
                                                </h3>
                                                <div class="d-flex gap-2">
                                                    <a class="btn action-btn btn--primary btn-outline-primary border-0" href="{{ route('admin.landing_page.download-custom-landing-page') }}"
                                                title="{{translate('messages.download_index_page')}}"><i class="tio-download-to"></i>
                                                </a>
                                                <a class="btn action-btn btn--danger btn-outline-danger border-0 form-alert" href="javascript:"
                                                data-id="index_page"
                                                data-message="{{ translate('Want to delete this index_page ?') }}"
                                                title="{{translate('messages.delete_index_page')}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                @endif
                                {{-- old code ends --}}

                            </div>
                            <div class="__input-tab {{ $landing_integration_type == 'none' ?'active':'' }}" id="none">
                                <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-warning" style="--bs-bg-opacity: 0.1;">
                                    <span class="text-warning lh-1 fs-14">
                                        <i class="tio-info"></i>
                                    </span>
                                    <span>
                                        {{ translate('messages.Without save the changes,') }}
                                        <span class="font-semibold">{{ translate('messages.Landing Page') }} </span>
                                        {{ translate('messages.can’t update properly and you can’t see the updated preview.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="__bg-F8F9FC-card d-flex flex-wrap gap-3 align-items-center justify-content-between">
                         <div class="">
                            <h4 class="mb-1">{{ translate('messages.Currently You are Using') }}</h4>
                            <p class="fs-12 mb-0">{{ translate('messages.Stackfood Default Admin Landing Page.') }}</p>
                        </div>
                        <a rel="noopener noreferrer" target="_blank" type="button" href="{{ route('home') }}" class="btn btn--primary">
                            {{ translate('Visit_Landing_Page') }}
                            <i class="tio-sign-out"></i>
                        </a>
                    </div>
                    @else
                    <div class="__bg-F8F9FC-card d-flex flex-wrap gap-3 align-items-center justify-content-between">
                         <div class="">
                            <h4 class="mb-1">{{ translate('messages.Currently You are Using') }}</h4>
                            <p class="fs-12 mb-0">{{ translate('messages.Stackfood Default Admin Landing Page.') }}</p>
                        </div>
                        <a rel="noopener noreferrer" target="_blank" href="{{ route('home') }}" class="btn btn--primary">
                            {{ translate('Visit_Landing_Page') }}
                            <i class="tio-sign-out"></i>
                        </a>
                    </div>
                    <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info mt-4" style="--bs-bg-opacity: 0.1;">
                        <span class="text-info lh-1 fs-14">
                            <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                        </span>
                        <span>
                            {{ translate('messages.To setup or edit') }}
                            <span class="font-semibold">{{ translate('messages.Default Landing Page') }} </span>
                            {{ translate('messages.you can visit') }}
                            <a rel="noopener noreferrer" target="_blank" href="{{ route('home') }}" class="font-semibold text-primary text-underline">{{ translate('messages.Admin Landing Page') }}</a>.
                        </span>
                    </div>
                    @endif

                </div>

            </div>
            <div class="footer-sticky mt-2">
                <div class="container-fluid">
                    <div class="d-flex flex-wrap gap-3 justify-content-center py-3">
                        <button type="reset" id="reset_btn" class="btn btn--secondary min-w-120">{{ translate('messages.Reset') }} </button>
                        <button type="submit" class="btn btn--primary zip-upload call-demo" id="update_setting">
                            <i class="tio-save"></i>
                            {{ translate('Save_Information') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <form action="{{route('admin.landing_page.delete-custom-landing-page')}}" method="post" id="index_page">
        @csrf @method('delete')
    </form>

    <div class="modal fade" id="read-instructions">
        <div class="modal-dialog status-warning-modal max-w-842">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body px-4 px-md-5 pb-5 pt-0">
                    <div class="single-item-slider owl-carousel">
                        <div class="item">
                            <div class="mb-20">
                                <div class="text-center">
                                    <img src="{{dynamicAsset('assets/admin/img/read-instructions.png')}}" alt="" class="mb-20">
                                    <h5 class="modal-title">{{translate('If_you_want_to_set_up_your_own_landing_page_please_follow_tha_instructions_below')}}</h5>
                                </div>
                                <ol type="1">
                                    <li>
                                        {{ translate('You_can_add_your_customised_landing_page_via_URL_or_upload_ZIP_file_of_the_landing_page.') }}
                                    </li>
                                    <li>
                                        {{ translate('If_you_want_to_use_URL_option._Just_host_you_landing_page_and_copy_the_page_URL_and_click_save_information.') }}
                                    </li>
                                    <li>
                                        {{ translate('If_you_want_to_Upload_your_landing_page_source_code_file.') }}

                                        <div class="ms-2 mt-1">
                                            {{ translate('a._Create_an_html_file_named') }} <b class="bg--4 text--primary-2">index.blade.php</b>    {{ translate('_and_insert_your_landing_page_design_code_and_make_a_zip_file.') }}

                                        </div>
                                        <div class="ms-2 mt-1">
                                            {{ translate('b._upload_the_zip_file_in_file_upload_section_and_click_save_information.') }}
                                        </div>
                                    </li>
                                </ol>
                            </div>
                        </div>

                    </div>
                    <div class="d-flex justify-content-center">
                        <div class="slide-counter"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script_2')

<script href="{{ dynamicAsset('assets/admin/vendor/swiper/swiper-bundle.min.js')}}"></script>
<script src="{{dynamicAsset('assets/admin/js/view-pages/business-settings-landing-page.js')}}"></script>

<script>

"use strict";

$('#theme_form').on('submit', function(event){
            event.preventDefault();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var formData = new FormData(document.getElementById('theme_form'));
            $.ajax({
                type: 'POST',
                url: "{{route('admin.landing_page.update-landing-setup')}}",
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    if ($('#inputFile').val()) {
                        $('#progress-bar').show();
                    }
                    xhr.upload.addEventListener("progress", function(e) {
                        if (e.lengthComputable) {
                            var percentage = Math.round((e.loaded * 100) / e.total);
                            $("#uploadProgress").val(percentage);
                            $("#progress-label").text(percentage + "%");
                        }
                    }, false);

                    return xhr;
                },
                beforeSend: function () {
                    $('#update_setting').attr('disabled');
                },
                success: function(response) {
                    if (response.status == 'error') {
                        $('#progress-bar').hide();
                        toastr.error(response.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }else if(response.status == 'success'){
                        toastr.success(response.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        location.reload();
                    }
                },
                complete: function () {
                    $('#update_setting').removeAttr('disabled');
                },
            });
        });

        $(document).on('click', '.confirm-Toggle', function () {
    let toggle_id = $('#toggle-ok-button').attr('toggle-ok-button');

    if (toggle_id === 'landing-page-default' || toggle_id === 'landing-page-custom') {
        $.get({
            url: '{{ route('admin.landing-page') }}',
            beforeSend: function () {
                $('#loading').show();
            },
            success: function (data) {
                toastr.success(data.message);
                location.reload();
            },
            complete: function () {
                $('#loading').hide();
            }
        });
    }
});


        $('#reset_btn').click(function () {
        $('.uploadDnD').empty().append(`<div class="form-group mb-0 inputDnD bg-white rounded">
                                                        <input type="file" name="file_upload" class="form-control-file text--primary font-weight-bold read-file"
                                                        id="inputFile" accept=".zip" data-title="Drag & drop file or Browse file">
                                                    </div>`)
        $(`.__input-tab`).removeClass('active')
        $(`#{{ $landing_integration_type }}`).addClass('active')
    })

</script>
@endpush
