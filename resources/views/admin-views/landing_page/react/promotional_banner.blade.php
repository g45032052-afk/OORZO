@extends('layouts.admin.app')

@section('title', translate('messages.landing_page_settings'))

@section('content')

    <div class="content container-fluid">
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-start">
                <h1 class="page-header-title text-capitalize fs-24">
                    <div class="card-header-icon d-inline-flex mr-2 img">
                        <img src="{{ dynamicAsset('assets/admin/img/landing-page.png') }}" class="mw-26px" alt="public">
                    </div>
                    <span>
                        {{ translate('React_Landing_Page') }}
                    </span>
                </h1>
                <!-- <div class="text--primary-2 py-1 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#how-it-works">
                    <strong class="mr-2">{{translate('See_how_it_works')}}</strong>
                    <div>
                        <i class="tio-info text-gray1 fs-16"></i>
                    </div>
                </div> -->
            </div>
        </div>
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

        <div class="card py-3 px-xxl-4 px-3 mb-15 mt-4">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-xxl-9 col-lg-8 col-md-7 col-sm-6">
                    <div class="">
                        <h3 class="mb-1">{{ translate('Show_on_Website') }}</h3>
                        <p class="mb-0 gray-dark fs-12">{{translate('If you turn of the availability status, this section will not show in the website')}}</p>
                    </div>
                </div>
                <div class="col-xxl-3 col-lg-4 col-md-5 col-sm-6">
                    <div class="py-2 px-3 rounded d-flex justify-content-between border rounded align-items-center w-300">
                        <h5 class="text-capitalize fw-normal mb-0">{{translate('Status') }}</h5>
                        <form
                            action="{{ route('admin.landing_page.statusUpdate', ['type' => 'react_landing_page', 'key' => 'react_promotional_banner_status']) }}"
                            method="get" id="BannerCheckboxStatus_form">
                        </form>
                        <label class="toggle-switch toggle-switch-sm" for="BannerCheckboxStatus">
                            <input type="checkbox" data-id="BannerCheckboxStatus" data-type="status"
                                   data-image-on="{{ dynamicAsset('assets/admin/img/status-ons.png') }}"
                                   data-image-off="{{ dynamicAsset('assets/admin/img/off-danger.png') }}"
                                   data-title-on="{{ translate('Do you want turn on this section ?') }}"
                                   data-title-off="{{ translate('Do you want to turn off this section ?') }}"
                                   data-text-on="<p>{{ translate('If you turn on this section will be show in react landing page.') }}"
                                   data-text-off="<p>{{ translate('If you turn off this section will not be show in react landing page.') }}</p>"
                                   class="toggle-switch-input  status dynamic-checkbox" id="BannerCheckboxStatus"
                                {{ $react_promotional_banner_status?->value ? 'checked' : '' }}>
                            <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
            <div class="card mb-20">
                <div class="card-header">
                    <div class="">
                        <h3 class="mb-1">{{ translate('Promotional Banner Section') }}</h3>
{{--                        <p class="mb-0 gray-dark fs-12">{{ translate('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam odio tellus, laoreet') }}</p>--}}
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-xxl-4 mb-xl-4 mb-3 text-center">
                        <h5 class="mb-0">{{ translate('Upload Banner Image') }} <span class="text-danger">*</span></h5>
                    </div>
                    <form class="validate-form" action="{{ route('admin.react_landing_page.promotional_banner_store') }}" method="post" enctype="multipart/form-data" >
                        @csrf
                        <div class="card-custom-static p-md-4 p-3">
                            <div class="bg-light2 p-20 max-w-555px rounded mx-auto d-flex align-items-center justify-content-center">
                                <div class="text-center">
                                    @include('admin-views.partials._image-uploader', [
                                                'id' => 'image-input',
                                                'name' => 'image',
                                                'ratio' => '3:1',
                                                'isRequired' => true,
                                                'existingImage' => null,
                                                'imageExtension' => IMAGE_EXTENSION,
                                                'imageFormat' => IMAGE_FORMAT,
                                                'maxSize' => MAX_FILE_SIZE,
                                                'pixel'=>'375 x 155'
                                            ])
                                </div>
                            </div>
                            <div class="btn--container justify-content-end mt-4">
                            <button type="reset" class="btn btn--reset">{{translate('Reset')}}</button>
                            <button type="submit"   class="btn btn--primary">{{translate('Add')}}</button>
                        </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header pb-2 pt-3 border-0">
                    <div class="search--button-wrapper">
                        <h5 class="card-title d-flex align-items-center">{{translate('messages.Promotional_Banner')}} <span class="badge badge-secondary ml-1"> {{ $react_promotional_banners?->count() }}</span> </h5>
                    </div>
                </div>
                <div class="card-body p-0">
                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-align-middle table-nowrap card-table ">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-top-0">{{translate('sl')}}</th>
{{--                                <th class="border-top-0">{{translate('Title')}}</th>--}}
                                <!-- <th class="border-top-0">{{translate('Short_description')}}</th> -->
                                <th class="border-top-0">{{translate('Image')}}</th>
                                <th class="border-top-0">{{translate('Status')}}</th>
                                <th class="text-center border-top-0">{{translate('Action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($react_promotional_banners as $key=>$react_promotional_banner)
                            <tr>
                                <td>{{ $key+$react_promotional_banners->firstItem() }}</td>
                                <td>
                                    <img  src="{{ $react_promotional_banner?->image_full_url ?? dynamicAsset('assets/admin/img/upload-3.png') }}"
                                          data-onerror-image="{{dynamicAsset('assets/admin/img/upload.png')}}"
                                          class="__size-105 onerror-image" alt="">
                                </td>
                                <td>
                                    <label class="toggle-switch toggle-switch-sm">
                                        <input type="checkbox"
                                               data-id="react_promotional_banner_status_{{$react_promotional_banner->id}}"
                                               data-type="status"
                                               data-image-on="{{ dynamicAsset('assets/admin/img/modal/testimonial-on.png') }}"
                                               data-image-off="{{ dynamicAsset('assets/admin/img/modal/testimonial-off.png') }}"
                                               data-title-on="{{translate('Want_to_Enable_this')}} <strong>{{translate('Promotional_Banner')}}</strong>"
                                               data-title-off="{{translate('Want_to_Disable_this')}} <strong>{{translate('Promotional_Banner')}}</strong>"
                                               data-text-on="<p>{{translate('If_enabled,_it_will_be_available_on_the_React_Landing_page')}}</p>"
                                               data-text-off="<p>{{translate('If_disabled,_it_will_be_hidden_from_the_React_Landing_page')}}</p>"
                                               class="status toggle-switch-input dynamic-checkbox"
                                            id="react_promotional_banner_status_{{$react_promotional_banner->id}}" {{$react_promotional_banner->status?'checked':''}}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                    <form action="{{route('admin.react_landing_page.promotional_banner_status',[$react_promotional_banner->id,$react_promotional_banner->status?0:1])}}" method="get" id="react_promotional_banner_status_{{$react_promotional_banner->id}}_form">
                                    </form>
                                </td>

                                <td>
                                    <div class="btn--container justify-content-center">
                                        <a class="btn action-btn btn--primary btn-outline-primary editBannerBtn"
                                           data-toggle="modal"
                                           data-target="#updateBanner"
                                           data-id="{{ $react_promotional_banner->id }}"
                                           data-image="{{ $react_promotional_banner->image_full_url }}"
                                           data-action="{{ route('admin.react_landing_page.promotional_banner_update',[$react_promotional_banner->id]) }}"
                                           href="#0">
                                            <i class="tio-edit"></i>
                                        </a>


                                        <a class="btn action-btn btn--danger btn-outline-danger form-alert-service" href="javascript:"
                                           data-id="react_promotional_banner-{{$react_promotional_banner['id']}}"
                                           data-message="{{ translate('Want_to_Delete_this_Promotional_Banner') }}"
                                           data-message-2="{{ translate('If_yes,_the_banner_will_be_removed_from_this_list') }}"
                                           title="{{translate('messages.delete_react_promotional_banner')}}"><i class="tio-delete-outlined"></i>
                                        </a>
                                        <form action="{{route('admin.react_landing_page.promotional_banner_delete',[$react_promotional_banner['id']])}}" method="post" id="react_promotional_banner-{{$react_promotional_banner['id']}}">
                                            @csrf @method('delete')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty

                            @endforelse
                        </tbody>
                    </table>
                    @if(count($react_promotional_banners) === 0)
                    <div class="empty--data">
                        <img src="{{dynamicAsset('assets/admin/img/empty.png')}}" alt="public">
                        <h5>
                            {{translate('no_data_found')}}
                        </h5>
                    </div>
                    @endif
                </div>
                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div>
                            {!! $react_promotional_banners->appends(request()->all())->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="updateBanner" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header pt-2 px-2">
                        <button type="button" class="close fs-24" data-dismiss="modal" aria-label="Close">
                            <i class="tio-clear fs-24"></i>
                        </button>
                    </div>
                    <div class="modal-body p-xl-4 p-2">
                        <div class="card-body p-0">
                            <div class="mb-xxl-4 mb-xl-4 mb-3 text-center">
                                <h5 class="mb-0">{{ translate('Update Banner Image') }} <span class="text-danger">*</span></h5>
                            </div>
                            <form class="validate-form" method="post" action="" enctype="multipart/form-data">
                                @csrf
                                <div class="card-custom-static p-md-4 p-3">
                                    <div class="bg-light2 p-20 max-w-555px rounded mx-auto d-flex align-items-center justify-content-center">
                                        <div class="text-center">
                                            @include('admin-views.partials._image-uploader', [
                                                'id' => 'image-input',
                                                'name' => 'image',
                                                'ratio' => '3:1',
                                                'isRequired' => $react_promotional_banner['image'] ? false : true,
                                                'existingImage' => $react_promotional_banner['image_full_url'],
                                                'imageExtension' => IMAGE_EXTENSION,
                                                'imageFormat' => IMAGE_FORMAT,
                                                'maxSize' => MAX_FILE_SIZE,
                                                'pixel'=>'375 x 155'
                                            ])
                                        </div>
                                    </div>
                                    <div class="btn--container justify-content-end mt-4">
                                        <button type="reset" class="btn btn--reset">{{translate('Reset')}}</button>
                                        <button type="submit" class="btn btn--primary">{{translate('Update')}}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('admin-views.landing_page.react.partials.promotional_banner_guideline')

@endsection

@push('script_2')
    <script>
        "use strict";
        $(document).on('click', '.form-alert-service', function () {
            Swal.fire({
                title: $(this).data('message'),
                text: $(this).data('message-2'),
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ translate('messages.No') }}',
                confirmButtonText: '{{ translate('messages.Yes') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $('#' + $(this).data('id')).submit()
                }
            })
        });

        $(document).on('click', '.editBannerBtn', function() {
            let imageUrl = $(this).data('image');
            let bannerId = $(this).data('id');
            let action = $(this).data('action');

            let $modal = $('#updateBanner');
            let $img = $modal.find('.upload-file-img');

            if (imageUrl) {
                $img.attr('src', imageUrl).show();
                $modal.find('.upload-file-textbox').hide();
            } else {
                $img.hide();
                $modal.find('.upload-file-textbox').show();
            }

            $modal.find('form').attr('action', action);
        });


    </script>
@endpush
