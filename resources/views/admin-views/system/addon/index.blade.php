@extends('layouts.admin.app')

@section('title', translate('system_addons'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .form-group {
            margin-bottom: 10px;
        }
    </style>
       <link rel="stylesheet" href="{{ dynamicAsset('assets/admin/vendor/swiper/swiper-bundle.min.css')}}" />
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-start">
                <!-- Page Header -->
                <h1 class="page-header-title text-capitalize">
                    <div class="card-header-icon d-inline-flex mr-2 img">
                        <img src="{{ dynamicAsset('assets/admin/img/business.png') }}" class="w--20" alt="">
                    </div>
                    <span>{{translate('system_addons')}}</span>

                </h1>
                <div class="text--primary-2 py-1 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#settingModal">
                    <strong class="mr-2">{{translate('See_how_it_works')}}</strong>
                    <div>
                        <i class="tio-info-outined ripple-animation"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="" tabindex="-1" aria-labelledby="settingModal" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                        <button
                            type="button"
                            class="btn-close border-0"
                            data-dismiss="modal"
                            aria-label="Close"
                        ><i class="tio-clear"></i></button>
                    </div>
                    <div class="modal-body px-4 px-sm-5 pt-0 text-center">
                        <div class="row g-2 g-sm-3 mt-lg-0">
                            <div class="col-12">
                                <div class="swiper mySwiper pb-3">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <img src="{{dynamicAsset('assets/admin/img/slider-1.png')}}" loading="lazy"
                                                alt="" class="dark-support rounded">
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="d-flex flex-column align-items-center mx-w450 mx-auto">
                                                <p>
                                                    Upload a module ZIP and enable it from the add-ons list.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="d-flex flex-column align-items-center mx-w450 mx-auto">
                                                <img src="{{dynamicAsset('assets/admin/img/slider-3.png')}}" loading="lazy"
                                                    alt="" class="dark-support rounded mb-4">
                                                <p>
                                                    {{ translate('now_you’ll_be_successfully_able_to_use_the_theme_for_your_website') }}
                                                </p>
                                                <p>
                                                    {{ translate('N:B_you_can_upload_only_theme_templates') }}.
                                                </p>
                                                <button class="btn btn-primary px-10 mt-3" data-dismiss="modal">{{ translate('Got_It') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-pagination"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- File Upload Card -->
        <div class="card mb-5">
            <div class="card-body pl-md-10">
                <h4 class="mb-3 text-capitalize d-flex align-items-center">{{translate('Upload_payment_&_sms_module')}}</h4>
                <form enctype="multipart/form-data" id="theme_form">
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-5 col-xl-4 col-xxl-3">
                            <!-- Drag & Drop Upload -->
                            <div class="uploadDnD">
                                <div class="form-group inputDnD">
                                    <input type="file" name="file_upload" class="form-control-file text--primary font-weight-bold read-file"
                                    id="inputFile" accept=".zip" data-title="{{ translate('Drag_&_drop_file_or_Browse_file') }}">
                                </div>
                            </div>

                            <div class="mt-5 card px-3 py-2 d--none" id="progress-bar">
                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <div class="">
                                        <img width="24" src="{{dynamicAsset('assets/admin/img/zip.png')}}" alt="">
                                    </div>
                                    <div class="flex-grow-1 text-start">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <span id="name_of_file" class="text-truncate fz-12"></span>
                                            <span class="text-muted fz-12" id="progress-label">0%</span>
                                        </div>
                                        <progress id="uploadProgress" class="w-100" value="0" max="100"></progress>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php($condition_one=str_replace('MB','',ini_get('upload_max_filesize'))>=20 && str_replace('MB','',ini_get('upload_max_filesize'))>=20)
                        @php($condition_two=str_replace('MB','',ini_get('post_max_size'))>=20 && str_replace('MB','',ini_get('post_max_size'))>=20)
                        <div class="col-sm-6 col-lg-5 col-xl-4 col-xxl-9">
                            <div class="pl-sm-5">
                                <h5 class="mb-3 d-flex">{{ translate('instructions') }}</h5>
                                <ul class="pl-3 d-flex flex-column gap-2 instructions-list">
                                    <li>
                                        1. {{ translate('please_make_sure') }}, {{ translate('your_server_php') }}
                                        "upload_max_filesize" {{translate('value_is_grater_or_equal_to_20MB') }}. {{ translate('current_value_is') }}
                                        - {{ini_get('upload_max_filesize')}}B
                                    </li>
                                    <li>
                                        2. {{ translate('please_make_sure')}}, {{ translate('your_server_php')}}
                                        "post_max_size"
                                        {{translate('value_is_grater_or_equal_to_20MB')}}
                                        . {{translate('current_value_is') }} - {{ini_get('post_max_size')}}B
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-end mt-3">
                                <button type="button"
                                    class="btn btn--primary px-4  {{ getEnvMode() == 'demo' ? 'call-demo' : 'zip-upload' }}" id="upload_theme">{{ (isset($addons) && count($addons)>0)?translate('update'):translate('upload')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Buttons Card -->

        {{-- Builder addon needs wildcard sub-domains so each vendor's storefront
             resolves on its own sub-domain. Uses the platform's standard info-hint
             bar; only shown once Builder is active. --}}
        @if(addon_published_status('Builder'))
            @php($appHost = parse_url(config('app.url'), PHP_URL_HOST) ?: 'yourdomain.com')
            <div class="d-flex align-items-start gap-2 fs-12 text-dark px-3 py-2 rounded bg-info mb-5" style="--bs-bg-opacity: 0.1;">
                <span class="text-info lh-1 fs-14">
                    <img src="{{ dynamicAsset('assets/admin/img/svg/bulb.svg') }}" class="svg" alt="">
                </span>
                <span>
                    <strong>{{ translate('Builder addon is active') }}.</strong>
                    {{ translate('To let vendors open their storefronts on their own sub-domains, configure a wildcard domain on your server') }}
                    (<code>*.{{ $appHost }}</code>)
                    {{ translate('pointing to this server, with a matching wildcard SSL certificate. Without it, vendor sub-domains will not resolve and their storefronts will not open.') }}
                </span>
            </div>
        @endif

        <!-- Theme Items -->
        <div class="row g-1 g-sm-2">
            @foreach($addons as $key => $addon)
            <?php
                $data= include base_path($addon.'/Addon/info.php');
                ?>

            <div class="col-6 col-md-4 col-xxl-3">
                <div class="card theme-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            {{$data['name']}}
                        </h3>

                        <div class="d-flex gap-2 gap-sm-3 align-items-center">
                            @if ($data['is_published'] == 0)
                                <button class="text-danger bg-transparent p-0 border-0 mr-2" data-toggle="modal" data-target="#deleteThemeModal_{{$key}}"><img src="{{dynamicAsset('assets/admin/img/delete.svg')}}" class="svg" alt=""></button>
                                <!-- Delete Theme Modal -->
                                <div class="modal fade" id="deleteThemeModal_{{$key}}" tabindex="-1" aria-labelledby="deleteThemeModal_{{$key}}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                                                <button
                                                    type="button"
                                                    class="btn-close border-0"
                                                    data-dismiss="modal"
                                                    aria-label="Close"
                                                ><i class="tio-clear"></i></button>
                                            </div>
                                            <div class="modal-body px-4 px-sm-5 text-center">
                                                <div class="mb-3 text-center">
                                                    <img width="75" src="{{dynamicAsset('assets/admin/img/delete.png')}}" alt="">
                                                </div>

                                                <h3>{{ translate('are_you_sure_you_want_to_delete_the').' '.$data['name'] }}?</h3>
                                                <p class="mb-5">{{ translate('once_you_delete') }}, {{ translate('you_will_lost_the_this') .' '.$data['name']  }}</p>
                                                <div class="btn--container justify-content-center">
                                                    <button type="button" class="btn btn--primary min-w-120" data-dismiss="modal">{{ translate('cancel') }}</button>
                                                    <button type="submit" class="btn btn--cancel min-w-120 theme-delete" data-dismiss="modal" data-path="{{$addon}}">{{ translate('delete') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                                <button class="{{$data['is_published'] == 1 ? 'checkbox-color-primary' : 'text-muted'}} bg-transparent p-0 border-0" data-toggle="modal" data-target="#shiftThemeModal_{{$key}}"><img src="{{dynamicAsset('assets/admin/img/check.svg')}}" class="svg" alt=""></button>

                                <div class="modal fade" id="shiftThemeModal_{{$key}}" tabindex="-1" aria-labelledby="shiftThemeModalLabel_{{$key}}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                                                <button
                                                    type="button"
                                                    class="btn-close border-0"
                                                    data-dismiss="modal"
                                                    aria-label="Close"
                                                ><i class="tio-clear"></i></button>
                                            </div>
                                            <div class="modal-body px-4 px-sm-5 text-center">
                                                <div class="mb-3 text-center">
                                                    <img width="75" src="{{dynamicAsset('assets/admin/img/success_image 2.png')}}" alt="">
                                                </div>

                                                <h3>{{ translate('are_you_sure?') }}</h3>
                                                @if ($data['is_published'])
                                                <p class="mb-5">{{ translate('want_to_disabled_this_'.' '.$data['name']) }}</p>
                                                @else
                                                <p class="mb-5">{{ translate('want_to_activate_this_'.' '.$data['name']) }}</p>
                                                @endif
                                                <div class="btn--container justify-content-center">
                                                    <button type="button" class="btn btn--cancel min-w-120" data-dismiss="modal">{{ translate('no') }}</button>
                                                    <button type="button" class="btn btn--primary min-w-120 publish-addon" data-dismiss="modal" data-path="{{$addon}}">{{ translate('yes') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>

                    <div class="p-2 p-sm-3">
                        <div class="aspect-ration-3:2 border border-color-primary-light radius-10">
                            <img class="img-fit radius-10"
                                onerror='this.src="{{dynamicAsset('assets/admin/img/image-place-holder.png')}}"'
                                src="{{ route('admin.business-settings.system-addon.image', ['addon' => $addon]) }}">
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>




    <div class="modal fade" id="settingModal">
        <div class="modal-dialog modal-lgstatus-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="single-item-slider owl-carousel">
                        <div class="item">
                            <div class="mb-20">
                                <div class="">
                                    <img src="{{dynamicAsset('assets/admin/img/addon_setting.png')}}" loading="lazy" alt="" class="dark-support rounded mb-4 mw-100">
                                        <ol>
                                            <li>Download the module package from your authorized source.</li>
                                            <li>Upload its ZIP file here.</li>
                                            <li>Enable the module from the add-ons list.</li>
                                            <li>Configure the module settings before using it.</li>
                                        </ol>
                                        <div class="swiper-slide">
                                            <div class="d-flex flex-column align-items-center mx-w450 mx-auto">
                                                <button class="btn btn-primary px-10 mt-3" data-dismiss="modal">{{ translate('Got_It') }}</button>
                                            </div>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
{{--                    <div class="d-flex justify-content-center">--}}
{{--                        <div class="slide-counter"></div>--}}
{{--                    </div>--}}
                </div>
            </div>
        </div>
    </div>



@endsection

@push('script_2')
<script href="{{ dynamicAsset('assets/admin/vendor/swiper/swiper-bundle.min.js')}}"></script>

<script>
    "use strict";
    $("img.svg").each(function () {
    let $img = jQuery(this);
    let imgID = $img.attr("id");
    let imgClass = $img.attr("class");
    let imgURL = $img.attr("src");

        jQuery.get(
          imgURL,
          function (data) {
            // Get the SVG tag, ignore the rest
            let $svg = jQuery(data).find("svg");

            // Add replaced image's ID to the new SVG
            if (typeof imgID !== "undefined") {
              $svg = $svg.attr("id", imgID);
            }
            // Add replaced image's classes to the new SVG
            if (typeof imgClass !== "undefined") {
              $svg = $svg.attr("class", imgClass + " replaced-svg");
            }

            // Remove any invalid XML tags as per http://validator.w3.organim
            $svg = $svg.removeAttr("xmlns:a");

            // Check if the viewport is set, else we gonna set it if we can.
            if (
              !$svg.attr("viewBox") &&
              $svg.attr("height") &&
              $svg.attr("width")
            ) {
              $svg.attr(
                "viewBox",
                "0 0 " + $svg.attr("height") + " " + $svg.attr("width")
              );
            }

            // Replace image with new SVG
            $img.replaceWith($svg);
          },
          "xml"
        );
      });
        $('.read-file').on('change', function () {
            readUrl(this);
        });
        function readUrl(input) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = (e) => {
                    let imgData = e.target.result;
                    let imgName = input.files[0].name;
                    input.setAttribute("data-title", imgName);
                    // console.log(e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }


        $('.zip-upload').on('click', function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let formData = new FormData(document.getElementById('theme_form'));
            $.ajax({
                type: 'POST',
                url: "{{route('admin.business-settings.system-addon.upload')}}",
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    let xhr = new window.XMLHttpRequest();
                    $('#progress-bar').show();

                    // Listen to the upload progress event
                    xhr.upload.addEventListener("progress", function(e) {
                        if (e.lengthComputable) {
                            let percentage = Math.round((e.loaded * 100) / e.total);
                            $("#uploadProgress").val(percentage);
                            $("#progress-label").text(percentage + "%");
                        }
                    }, false);

                    return xhr;
                },
                beforeSend: function () {
                    $('#upload_theme').attr('disabled');
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
                    $('#upload_theme').removeAttr('disabled');
                },
            });
        })

    $('.publish-addon').on('click', function () {
        let path = $(this).data('path');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                    url: '{{route('admin.business-settings.system-addon.publish')}}',
                    data: {
                        'path': path
                    },
                    success: function (data) {
                        if (data.errors) {
                            for (let i = 0; i < data.errors.length; i++) {
                                toastr.error(data.errors[i].message, {
                                    CloseButton: true,
                                    ProgressBar: true
                                });
                            }
                        } else {
                            toastr.success('{{ translate("updated successfully!") }}', {
                                CloseButton: true,
                                ProgressBar: true
                            });
                            setTimeout(function () {
                                location.reload()
                            }, 2000);
                        }
                    }
                });
            })

    $('.theme-delete').on('click', function () {
        let path = $(this).data('path');
                $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.business-settings.system-addon.delete')}}',
                data: {
                    path
                },
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    if (data.status === 'success') {
                        setTimeout(function () {
                            location.reload()
                        }, 2000);

                        toastr.success(data.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }else if(data.status === 'error'){
                        toastr.error(data.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        })

        let swiper = new Swiper(".mySwiper", {
            pagination: {
                el: ".swiper-pagination",
                dynamicBullets: true,
            },
        });
    </script>
@endpush
