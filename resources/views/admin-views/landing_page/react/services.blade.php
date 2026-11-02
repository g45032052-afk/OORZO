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
            </div>
        </div>
        <div class="mb-15">
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
                    <div class="py-2 px-3 rounded d-flex justify-content-between border align-items-center w-300">
                        <h5 class="text-capitalize fw-normal mb-0">{{translate('Status') }}</h5>
                        <form
                            action="{{ route('admin.landing_page.statusUpdate', ['type' => 'react_landing_page', 'key' => 'react_service_status']) }}"
                            method="get" id="ServiceCheckboxStatus_form">
                        </form>
                        <label class="toggle-switch toggle-switch-sm" for="ServiceCheckboxStatus">
                            <input type="checkbox" data-id="ServiceCheckboxStatus" data-type="status"
                                   data-image-on="{{ dynamicAsset('assets/admin/img/status-ons.png') }}"
                                   data-image-off="{{ dynamicAsset('assets/admin/img/off-danger.png') }}"
                                   data-title-on="{{ translate('Do you want turn on this section ?') }}"
                                   data-title-off="{{ translate('Do you want to turn off this section ?') }}"
                                   data-text-on="<p>{{ translate('If you turn on this section will be show in react landing page.') }}"
                                   data-text-off="<p>{{ translate('If you turn off this section will not be show in react landing page.') }}</p>"
                                   class="toggle-switch-input  status dynamic-checkbox" id="ServiceCheckboxStatus"
                                {{ $react_service_status?->value ? 'checked' : '' }}>
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
                    <h3 class="mb-1">{{ translate('Service_Section') }}</h3>
                    <p class="mb-0 gray-dark fs-12">{{ translate('Manage service cards shown below the banner with title, icon, and short description.') }}</p>
                </div>
            </div>
            <div class="card-body p-xl-20 p-3">
                <div class="card-custom-xl mb-10px">
                    <form class="validate-form" action="{{ route('admin.react_landing_page.service_store') }}" method="post" enctype="multipart/form-data" >
                        @csrf
                        <div class="row g-3">
                            <div class="col-lg-8">
                                <div class="bg-light2 p-xl-20 p-3 rounded">
                                    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                                    @php($language = $language->value ?? null)
                                    @php($default_lang = str_replace('_', '-', app()->getLocale()))
                                    @if($language)
                                        <ul class="nav nav-tabs mb-4 border-">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link active"
                                                href="#"
                                                id="default-link">{{translate('messages.default')}}</a>
                                            </li>
                                            @foreach (json_decode($language) as $lang)
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link"
                                                        href="#"
                                                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    <div class=" lang_form default-form">
                                        <input type="hidden" name="lang[]" value="default">
                                        <div class="form-group mb-2">
                                            <label for="title" class="form-label fw-400">{{translate('title')}} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                                <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ translate('The name of the service feature (e.g., "Fast Delivery"). Keep it under 50 characters.') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                            </label>
                                            <input required id="title"  type="text" data-maxlength="50"  name="title[]" class="form-control" placeholder="{{translate('Ex:_John')}}">
                                            <div class="d-flex justify-content-end">
                                                <span class="text-body-light text-right d-block mt-1">0/50</span>
                                            </div>
                                        </div>
                                        <div class="form-group mb-0 pt-3">
                                            <label for="sub_title" class="form-label fw-400">{{translate('messages.Subtitle')}} ({{ translate('messages.default') }}) <span class="text-danger">*</span>
                                            <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ translate('Write a brief description of the service benefit. Keep it under 100 characters.') }}">
                                                    <i class="tio-info text-gray1 fs-16"></i>
                                                </span>
                                            </label>
                                            <input required id="sub_title" type="text" name="sub_title[]" class="form-control" data-maxlength="100"  placeholder="{{translate('Very_Good_Company')}}">
                                            <div class="d-flex justify-content-end">
                                                <span class="text-body-light text-right d-block mt-1">0/100</span>
                                            </div>
                                        </div>
                                    </div>

                                    @forelse(json_decode($language) as $lang)
                                    <div class=" d-none lang_form" id="{{$lang}}-form1">
                                            <input type="hidden" name="lang[]" value="{{$lang}}">
                                            <div class="form-group">
                                                <label for="title{{$lang}}" class="form-label fw-400">{{translate('title')}} ({{strtoupper($lang)}})
                                                    <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                    data-placement="right"
                                                    data-original-title="{{ translate('The name of the service feature (e.g., "Fast Delivery"). Keep it under 50 characters.') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                </label>
                                                <input id="title{{$lang}}" type="text" data-maxlength="50" name="title[]" class="form-control" placeholder="{{translate('Ex:_John')}}">
                                                <div class="d-flex justify-content-end">
                                                    <span class="text-body-light text-right d-block mt-1">0/50</span>
                                                </div>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label for="sub_title{{$lang}}" class="form-label fw-400">{{translate('messages.Subtitle')}} ({{strtoupper($lang)}})
                                                    <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                    data-placement="right"
                                                    data-original-title="{{ translate('Write a brief description of the service benefit. Keep it under 60 characters.') }}">
                                                        <i class="tio-info text-gray1 fs-16"></i>
                                                    </span>
                                                </label>
                                                <input id="sub_title{{$lang}}" type="text" name="sub_title[]" class="form-control" data-maxlength="100"  placeholder="{{translate('Very_Good_Company')}}">
                                                <div class="d-flex justify-content-end">
                                                    <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                </div>
                                            </div>
                                    </div>
                                    @empty
                                    @endforelse
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="p-xxl-20 d-flex align-items-center justify-content-center p-12 global-bg-box text-center rounded h-100">
                                    <div class="">
                                        <div class="mb-xxl-5 mb-xl-4 mb-3 text-start">
                                            <h5 class="mb-1">{{ translate('Icon_Image') }} <span class="text-danger">*</span></h5>
                                            <p class="mb-0 fs-12 gray-dark">{{ translate('Upload service icon/image') }}</p>
                                        </div>
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
                        <div class="btn--container justify-content-end mt-4">
                            <button type="reset" class="btn btn--reset">{{translate('Reset')}}</button>
                            <button type="submit"   class="btn btn--primary">{{translate('save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


            <div class="card">
                <div class="card-header border-0 pb-1 pt-3">
                    <div class="search--button-wrapper">
                        <h5 class="card-title d-flex align-items-center">{{translate('messages.Services_List')}} <span class="badge badge-secondary ml-1"> {{ $services?->count() }}</span> </h5>
                        <form class="search-form">
                            <div class="input-group input--group">
                                <input id="datatableSearch_" type="search" name="search" value="{{ request()?->search ?? null }}" class="form-control"
                                        placeholder="{{translate('Search_title')}}" aria-label="{{translate('messages.search')}}" >
                                <button type="submit" class="btn btn--secondary secondary-cmn"><i class="tio-search"></i></button>

                            </div>
                        </form>
                        {{--<div class="hs-unfold mr-2">
                            <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40" href="javascript:"
                                data-hs-unfold-options='{
                                        "target": "#usersExportDropdown",
                                        "type": "css-animation"
                                    }'>
                                <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                            </a>

                            <div id="usersExportDropdown" class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                                <a id="export-excel" class="dropdown-item" href="{{ route('admin.react_landing_page.service_export', ['type' => 'excel', request()->getQueryString()]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin/svg/components/excel.svg') }}"
                                        alt="Image Description">
                                    {{ translate('messages.excel') }}
                                </a>
                                <a id="export-csv" class="dropdown-item" href="{{ route('admin.react_landing_page.service_export', ['type' => 'csv', request()->getQueryString()]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin/svg/components/placeholder-csv-format.svg') }}"
                                        alt="Image Description">
                                    {{ translate('messages.csv') }}
                                </a>
                            </div>
                        </div>--}}
                        <!-- End Unfold -->
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-borderless table-align-middle table-nowrap card-table">
                            <thead class="thead-light border-0">
                                <tr>
                                    <th class="border-top-0">{{translate('sl')}}</th>
                                    <th class="border-top-0">{{translate('Title')}}</th>
                                    <th class="border-top-0">{{translate('Subtitle')}}</th>
                                    <th class="border-top-0">{{translate('Image')}}</th>
                                    <th class="border-top-0">{{translate('Status')}}</th>
                                    <th class="text-center border-top-0">{{translate('Action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($services as $key=>$service)
                                <tr>
                                    <td>{{ $key+$services->firstItem() }}</td>
                                    <td>
                                        <div class="text--title">
                                        {{ $service->title }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="word-break min-w-100px line-limit-2 max-w-300 text-wrap">
                                            {{ $service->sub_title }}
                                        </div>
                                    </td>
                                    <td>

                                        <img  src="{{ $service?->image_full_url ?? dynamicAsset('assets/admin/img/aspect-1.png')}}"
                                                  data-onerror-image="{{dynamicAsset('assets/admin/img/upload.png')}}"
                                                  class="__size-105 onerror-image" alt="">
                                        <!-- <div class="w-40px h-40px rounded overflow-hidden">
                                            <img  src="{{ $service?->image_full_url ?? dynamicAsset('assets/admin/img/aspect-1.png')}}"
                                                  data-onerror-image="{{dynamicAsset('assets/admin/img/upload.png')}}"
                                                  class="__size-105 onerror-image object-contain" alt="">
                                        </div> -->
                                    </td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm">
                                            <input type="checkbox"
                                                   data-id="service_status_{{$service->id}}"
                                                   data-type="status"
                                                   data-image-on="{{ dynamicAsset('assets/admin/img/modal/testimonial-on.png') }}"
                                                   data-image-off="{{ dynamicAsset('assets/admin/img/modal/testimonial-off.png') }}"
                                                   data-title-on="{{translate('Want_to_Enable_this')}} <strong>{{translate('services')}}</strong>"
                                                   data-title-off="{{translate('Want_to_Disable_this')}} <strong>{{translate('services')}}</strong>"
                                                   data-text-on="<p>{{translate('If_enabled,_it_will_be_shown_on_the_React_Landing_page')}}</p>"
                                                   data-text-off="<p>{{translate('If_disabled,_it_will_be_hidden_from_the_React_Landing_page')}}</p>"
                                                   class="status toggle-switch-input dynamic-checkbox"

                                                id="service_status_{{$service->id}}" {{$service->status?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                        <form action="{{route('admin.react_landing_page.service_status',[$service->id,$service->status?0:1])}}" method="get" id="service_status_{{$service->id}}_form">
                                        </form>
                                    </td>

                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.react_landing_page.service_edit',[$service['id']])}}">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger form-alert-service" href="javascript:"
                                               data-id="service-{{$service['id']}}"
                                               data-message="{{ translate('Want_to_Delete_this_Service') }}"
                                               data-message-2="{{ translate('If_yes,_the_service_will_be_removed_from_this_list') }}" title="{{translate('messages.delete_service')}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('admin.react_landing_page.service_delete',[$service['id']])}}" method="post" id="service-{{$service['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty

                                @endforelse
                            </tbody>
                        </table>
                        @if(count($services) === 0)
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
                                {!! $services->appends(request()->all())->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @include('admin-views.landing_page.react.partials.service_guideline')

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
    </script>
@endpush

