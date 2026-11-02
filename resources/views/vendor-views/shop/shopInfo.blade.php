@extends('layouts.vendor.app')
@section('title',translate('messages.my_restaurant'))
@section('content')
    <div class="content container-fluid">
        @include('vendor-views.partials.business_setup_header')
        <div class="card card-body mb-3">
            <div class="row g-3 justify-content-between">
                <div class="col-xxl-9 col-lg-8 col-md-7 col-sm-6">
                    <div class="">
                        <h3 class="mb-1">{{ translate('messages.Restaurant_Availability') }}</h3>
                        <p class="fs-12 mb-0">
                            {{ translate('messages.Turning off the status will deactivate your restaurant and will show temporary off in the customer app & websites') }}
                        </p>
                    </div>
                </div>
                <div class="col-xxl-3 col-lg-4 col-md-5 col-sm-6">
                    <div class="maintainance-mode-toggle-bar rounded d-flex justify-content-between border align-items-center w-100">
                        <span class="text-dark">{{ translate('messages.Active_Status') }}</span>

                        <label class="toggle-switch toggle-switch-sm">
                            <input type="checkbox" id="" class="status toggle-switch-input restaurant-open-status" {{ $shop->active ? 'checked' : '' }}>
                            <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card-from-sm mb-3">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h3 class="mb-1"> {{translate('Restaurant_Details')}} </h3>
                    <p class="fs-12 mb-0">{{translate('Created_at')}} {{ \App\CentralLogics\Helpers::time_date_format($shop->created_at) }}</p>
                </div>
                <a href="{{route('vendor.shop.edit')}}" class="btn btn--primary d-flex gap-2 align-items-baseline">
                    <i class="tio-open-in-new"></i> {{translate('Edit_Information')}}
                </a>
            </div>
            <div class="card-body">
                <!-- Banner -->
                <section class="shop-details-banner">
                    <div class="card mb-3">
                        <div class="card-body px-0 pt-0">
                            <!-- <img  class="shop-details-banner-img"
                                src="{{ $shop?->cover_photo_full_url ?? dynamicAsset('assets/admin/img/900x400/img1.jpg') }}"
                                alt="image"> -->

                            <div class="shop-details__thumb-wrap block-size-custom position-relative">
                                <div class="upload-file mx-auto w-100">
                                    <input type="file" name="photo" class="upload-file__input single_file_input"
                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="upload-file__wrapper m-0">
                                        <div class="upload-file-textbox text-center" style="">
                                            <img width="34" class="svg" src="{{dynamicAsset('assets/admin/img/image-upload.png')}}" alt="img">
                                            <h6 class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">
                                                <span class="text-info">{{ translate('messages.Click_to_upload') }}</span>
                                                <br>
                                                {{ translate('messages.or_drag_and_drop') }}
                                            </h6>
                                        </div>
                                        <img class="upload-file-img" loading="lazy"
                                            src="{{ $shop?->cover_photo_full_url ?? dynamicAsset('assets/admin/img/restaurant_cover.jpg') }}"
                                            data-default-src="{{ $shop?->cover_photo_full_url ?? dynamicAsset('assets/admin/img/restaurant_cover.jpg') }}"
                                            alt="" style="display: none;">
                                    </label>
                                </div>
                                <button type="button" class="btn z-index-2 bg-white text-hover-primary btn-outline-info w-30px h-30px p-0 edit_btn-main end--0 m-xxl-4 m-xl-3 m-2 top--0 position-absolute">
                                    <i class="tio-edit"></i>
                                </button>
                            </div>

                            <div class="shop-details-banner-content px-3 px-xxl-4 z-index-2 position-relative">
                                <div class="shop-details-banner-content-thumbnail w-100px rounded bg-white">
                                    <!-- <img class="thumbnail rounded"
                                    src="{{ $shop?->logo_full_url ?? dynamicAsset('assets/admin/img/160x160/img1.jpg') }}"
                                    alt="image"> -->
                                    <div class="shop-details__thumb-wrap2 position-relative">
                                        <div class="upload-file mx-auto">
                                            <input type="file" name="image" class="upload-file__input single_file_input"
                                                    accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                            <label class="upload-file__wrapper ratio-1 mx-auto m-0">
                                                <div class="upload-file-textbox text-center" style="">
                                                    <img width="34" class="svg" src="{{dynamicAsset('assets/admin/img/image-upload.png')}}" alt="img">
                                                    <h6 class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">
                                                        <span class="text-info">{{ translate('messages.Click_to_upload') }}</span>
                                                        <br>
                                                        {{ translate('messages.or_drag_and_drop') }}
                                                    </h6>
                                                </div>
                                                <img class="upload-file-img" loading="lazy"
                                                    src="{{ $shop?->logo_full_url ?? dynamicAsset('assets/admin/img/image-place-holder.png') }}"
                                                    data-default-src="{{ $shop?->logo_full_url ?? dynamicAsset('assets/admin/img/image-place-holder.png') }}"
                                                    alt="" style="display: none;">
                                            </label>
                                        </div>
                                        <button type="button" class="btn z-index-2 bg-white text-hover-primary btn-outline-info w-30px h-30px p-0 edit_btn-main end--0 m-2 top--0 position-absolute">
                                            <i class="tio-edit"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="shop-details-banner-content-content">
                                    <div class="mt-sm-4 pt-sm-3 mb-4 d-none d-sm-block">
                                        <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between">
                                            <div>
                                                <h2 class="h3 mb-1">
                                                    {{$shop->name}}
                                                    @include('partials._verified_restaurant_badge', ['restaurant' => $shop])
                                                </h2>
                                                {{-- <p class="fs-12 mb-0">{{translate('Created_at')}} {{ \App\CentralLogics\Helpers::time_date_format($shop->created_at) }}</p> --}}
                                            </div>
                                            @if(!empty(\App\CentralLogics\Helpers::get_business_data('landing_page_links')['web_app_url']))
                                            <a target="_blank"
                                               href="{{ \App\CentralLogics\Helpers::get_business_data('landing_page_links')['web_app_url'] .'restaurant/' . $shop->slug . '?id=' . $shop->id . '&form_dine_in=false' }}"
                                               class="btn btn-outline-primary d-flex gap-2 align-items-baseline">
                                                {{translate('messages.Visit_Website')}} <i class="tio-open-in-new"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="shop-details-model flex-xxl-nowrap">
                                        <div class="shop-details-model-item align-items-start flex-grow-1 flex-shrink-0">
                                            <img src="{{dynamicAsset('assets/admin/new-img/icon-1.png')}}" alt="">
                                            <div class="shop-details-model-item-content fs-13">
                                                <h5 class="mb-1 text-nowrap">  {{ translate('Business_Model') }} </h5>
                                                @if($shop->restaurant_model == 'commission')
                                                    <div>{{translate('Commission_Base')}}</div>
                                                @elseif($shop->restaurant_model == 'none')
                                                    <div>{{translate('Not_chosen')}}</div>
                                                @else
                                                    <div>{{translate('Subscription')}}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="shop-details-model-item align-items-start flex-grow-1 flex-shrink-0">
                                            <img src="{{dynamicAsset('assets/admin/new-img/icon_6.png')}}" alt="">
                                            <div class="shop-details-model-item-content fs-13">
                                                <h5 class="mb-1 text-nowrap">  {{ translate('admin_Commission') }} </h5>
                                                <div> {{(isset($shop->comission)?$shop->comission:\App\Models\BusinessSetting::where('key','admin_commission')->first()?->value)}} %</div>
                                            </div>
                                        </div>
                                        <div class="shop-details-model-item align-items-start flex-grow-1 flex-shrink-0">
                                            <img src="{{dynamicAsset('assets/admin/new-img/icon-3.png')}}" alt="">
                                            <div class="shop-details-model-item-content fs-13">
                                                <h5 class="mb-1 text-nowrap">{{ translate('Phone') }} </h5>
                                                <div>{{$shop->phone}}</div>
                                            </div>
                                        </div>
                                        <div class="shop-details-model-item align-items-start flex-grow-1">
                                            <img src="{{dynamicAsset('assets/admin/new-img/icon-4.png')}}" alt="">
                                            <div class="shop-details-model-item-content fs-13">
                                                <h5 class="mb-1 text-nowrap"> {{ translate('Address') }} </h5>
                                                <div class="overflow-wrap-anywhere">{{ \Illuminate\Support\Str::limit($shop->address, 50) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- Banner -->
            </div>
        </div>
        <div class="card card-body">
            <div class="view-details-container">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <h3 class="mb-1">{{ translate('messages.Announcement') }}</h3>
                        <p class="mb-0 fs-12">
                            {{ translate('Enable this feature to share my announcements with customers.') }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <div
                            class="view-btn text-primary cursor-pointer font-semibold d-flex align-items-center gap-1">
                            <span class="text-underline">{{ translate('messages.view') }}</span>
                            <i class="tio-down-ui fs-12"></i>
                        </div>
                        <label class="toggle-switch toggle-switch-sm m-0">
                            <input type="checkbox"  name="announcement" class="toggle-switch-input update-status" data-url="{{route('vendor.business-settings.toggle-settings',[$shop->id,$shop->announcement?0:1, 'announcement'])}}" id="announcement" {{$shop->announcement?'checked':''}} >
                            <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </div>
                </div>
                <div class="view-details mt-4">
                    <form action="{{route('vendor.shop.update-message')}}" method="post">
                        @csrf
                        <div class="__bg-F8F9FC-card mb-20">
                             <label class="input-label text-capitalize d-flex gap-1 align-items-center mb-0">
                                {{ translate('Announcement_Text') }}
                                <span class="tio-info text-gray1 fs-16" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('messages.This_announcement_shown_in_the_user_app/web') }}">
                                </span>
                            </label>
                            <textarea name="announcement_message" id="" maxlength="254" class="form-control h-100px" placeholder="{{ translate('messages.ex_:_ABC_Company') }}">{{ $shop->announcement_message??'' }}</textarea>
                        </div>
                        <div class="btn--container justify-content-end">
                            <button type="reset" class="btn btn--reset min-w-120">{{translate('messages.reset')}}</button>
                            <button type="submit" class="btn btn--primary min-w-120">{{translate('messages.publish')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <!--- Image Modal --->
        <div class="modal fade" id="restaurant-image__modal" data-backdrop="static">
            <div class="modal-dialog modal-dialog-centered max-w-1050px modal-dialog-scrollable">
                <div class="modal-content rounded-20">
                    <div class="modal-header cmn__quick p-0">
                        <button type="button" class="close w-35px h-35px min-h-35px clear-when-done" data-dismiss="modal" aria-label="Close">
                            <span class="top-0 m-0" aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{route('vendor.shop.cover-update')}}" class="mt-lg-4 mt-3 pb-2" method="post"
                    enctype="multipart/form-data">
                    <div class="modal-body pt-0 pb-0">
                        <div class="modal-title flex-grow-1">
                            <h3 class="">
                                {{ translate('Restaurant Cover Photo') }}
                            </h3>
                            <p class="fs-10 mb-0 mt-1">
                                {{ translate('messages.After crop the image here is the final image JPG, JPEG, PNG, Gif Image size : Max 2 MB') }} <span class="fw-500 text-title">({{ translate('messages.1100 x 320') }})</span>
                            </p>
                        </div>
                            @csrf
                            <div class="shop-details__thumb-wrap-modal block-size-custom position-relative">
                                <div class="upload-file w-100 mx-auto position-relative">
                                    <input type="file" name="cover_photo" class="upload-file__input single_file_input"
                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="upload-file__wrapper w-100 mx-auto m-0">
                                        <div class="upload-file-textbox text-center" style="">
                                            <img width="34" class="svg" src="{{dynamicAsset('assets/admin/img/image-upload.png')}}" alt="img">
                                            <h6 class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">
                                                <span class="text-info">{{ translate('messages.Click_to_upload') }}</span>
                                                <br>
                                                {{ translate('messages.or_drag_and_drop') }}
                                            </h6>
                                        </div>
                                        <img class="upload-file-img" loading="lazy"
                                            src="{{ $shop?->cover_photo_full_url ?? dynamicAsset('assets/admin/img/restaurant_cover.jpg') }}"
                                            data-default-src="{{ $shop?->cover_photo_full_url ?? dynamicAsset('assets/admin/img/restaurant_cover.jpg') }}"
                                            alt="" style="display: none;">
                                    </label>
                                </div>
                                <div class="bg-white cursor-pointer d-flex z-index-2 align-items-center gap-1 rounded py-1 px-2 text-hover-primary edit_btn end-10 mt-10px top--0 position-absolute">
                                    {{ translate('Upload again') }}
                                    <button type="button" class="btn btn-outline-info d-center fs-10 h-20px p-0 w-20px">
                                        <i class="tio-photo-camera"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 py-3">
                            <div class="btn--container justify-content-end">
                                <button type="submit" class="btn btn--primary min-w-120" id="btn_update">{{translate('messages.update')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--- Image logo Modal --->
        <div class="modal fade" id="restaurant-logo__modal" data-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content rounded-20">
                    <div class="modal-header cmn__quick p-0">
                        <button type="button" class="close w-35px h-35px min-h-35px clear-when-done" data-dismiss="modal" aria-label="Close">
                            <span class="top-0 m-0" aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{route('vendor.shop.logo-update')}}" class="mt-lg-4 mt-3 pb-2" method="post"
                        enctype="multipart/form-data">
                        @csrf
                    <div class="modal-body pt-0 pb-0">
                        <div class="modal-title flex-grow-1">
                            <h3 class="">
                                {{ translate('Restaurant Logo') }}
                            </h3>
                            <p class="fs-10 mb-0 mt-1">
                                {{ translate('messages.JPG, JPEG, PNG, Gif Image size : Max 2 MB') }} <span class="fw-500 text-title">({{ translate('messages.1:1') }})</span>
                            </p>
                        </div>
                            <div class="shop-details__thumb-wrap-modal">
                                <div class="text-center block-size-150 mx-auto position-relative">
                                    <div class="upload-file mx-auto">
                                        <input type="file" name="logo" class="upload-file__input single_file_input"
                                                accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="upload-file__wrapper ratio-1 mx-auto m-0 block-size-150">
                                            <div class="upload-file-textbox text-center" style="">
                                                <img width="34" class="svg" src="{{dynamicAsset('assets/admin/img/image-upload.png')}}" alt="img">
                                                <h6 class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">
                                                    <span class="text-info">{{ translate('messages.Click_to_upload') }}</span>
                                                    <br>
                                                    {{ translate('messages.or_drag_and_drop') }}
                                                </h6>
                                            </div>
                                            <img class="upload-file-img" loading="lazy"
                                                src="{{ $shop?->logo_full_url ?? dynamicAsset('assets/admin/img/image-place-holder.png') }}"
                                                data-default-src="{{ $shop?->logo_full_url ?? dynamicAsset('assets/admin/img/image-place-holder.png') }}"
                                                alt="" style="display: none;">
                                        </label>
                                    </div>
                                    <div class="bg-white cursor-pointer d-flex z-index-2 align-items-center gap-1 rounded py-1 px-2 text-hover-primary edit_btn end-10 mt-10px top--0 position-absolute">
                                        {{ translate('Upload again') }}
                                        <button type="button" class="btn btn-outline-info d-center fs-10 h-20px p-0 w-20px">
                                            <i class="tio-photo-camera"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 py-3">
                            <div class="btn--container justify-content-end">
                                <button type="submit" class="btn btn--primary min-w-120" id="btn_update">{{translate('messages.update')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection


@push('script_2')
    <script>
        "use strict";
        $('.update-status').on('click', function (){
            let route = $(this).data('url');
            let code = $(this).data('code');
            updateStatus(route, code);
        })

        function updateStatus(route, code) {
            $.get({
                url: route,
                data: {
                    code: code,
                },
                success: function (data) {
                    if (data.error == 403) {
                        toastr.error('{{translate('status_can_not_be_updated')}}');
                        location.reload();
                    }
                    else{
                        toastr.success('{{translate('messages.Restaurant settings updated!')}}');
                    }
                }
            });
        }

        $(document).on('click', '.restaurant-open-status', function (event) {
            Swal.fire({
                title: '{{ !$shop->active ? translate('messages.Want_to_make_your_restaurant_available_for_all') :  translate('messages.Want_to_close_your_restaurant_temporarily')}} ?',
                text: '{{!$shop->active ? translate('messages.If_yes_this_restaurant_will_be_available_for_customers_in_app_and_web') : translate('messages.If_yes_this_restaurant_will_be_unavailable_for_customers_in_apps_and_web') }}',
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#377dff',
                cancelButtonText: '{{translate('messages.no')}}',
                confirmButtonText: '{{translate('messages.yes')}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.get({
                        url: '{{route('vendor.business-settings.update-active-status')}}',
                        contentType: false,
                        processData: false,
                        beforeSend: function () {
                            $('#loading').show();
                        },
                        success: function (data) {
                            toastr.success(data.message);
                        },
                        complete: function () {
                            $('#loading').hide();
                            location.reload();
                        },
                    });
                } else {
                    event.checked = !event.checked;
                }
            })
        });
    </script>
    <!-- Page level plugins -->
    <script>
        "use strict";

        let lastSelectedFile = null;
        let activeModal = null;

        /* --------------------------------
        EDIT BUTTON CLICK
        -------------------------------- */
        $(document).on("click", ".edit_btn-main", function () {
            // cover or logo detect
            if ($(this).closest(".shop-details__thumb-wrap").length) {
                activeModal = "#restaurant-image__modal";
            }
            else if ($(this).closest(".shop-details__thumb-wrap2").length) {
                activeModal = "#restaurant-logo__modal";
            }

            // modal input
            const modalInput = $(`${activeModal} .single_file_input`)[0];
            modalInput.value = "";
            modalInput.click();
        });

        /* --------------------------------
        FILE SELECTED (MODAL INPUT)
        -------------------------------- */
        $(document).on(
            "change",
            "#restaurant-image__modal .single_file_input, #restaurant-logo__modal .single_file_input",
            function () {

                const wrap = $(this).closest(".shop-details__thumb-wrap-modal");
                const img  = wrap.find(".upload-file-img");

                if (!this.files || !this.files.length) {
                    const oldSrc = img.data("old-src");
                    if (oldSrc) img.attr("src", oldSrc).show();
                    return;
                }
                // save file
                lastSelectedFile = this.files[0];

                const reader = new FileReader();
                reader.onload = e => {
                    img.attr("src", e.target.result).show();
                };
                reader.readAsDataURL(lastSelectedFile);

                // modal show
                $(activeModal).modal("show");
            }
        );

        $(document).on("click", ".modal .edit_btn", function (e) {
            e.preventDefault();

            const wrap  = $(this).closest(".shop-details__thumb-wrap-modal");
            const input = wrap.find(".single_file_input")[0];
            const img   = wrap.find(".upload-file-img");
            // current image save
            img.data("old-src", img.attr("src"));

            input.value = "";
            input.click();
        });

    </script>
@endpush
