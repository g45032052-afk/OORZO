@extends('layouts.admin.app')

@section('title', translate('messages.New_joining_request'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')

    <div class="content container-fluid">
        <div class="card card-from-sm">
            <div class="card-body">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <div class="page--header-title">
                            <h1 class="page-header-title"> {{ translate('Delivery_Man_Details') }} </h1>
                            <p class="page-header-text"> {{ translate('Requested_to_join_at') }}
                                {{ \App\CentralLogics\Helpers::time_date_format($dm->created_at) }}
                            </p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if($dm->application_status != 'denied')
                                <a data-url="{{ route('admin.delivery-man.application', [$dm['id'], 'denied']) }}"
                                    data-message="{{ translate('messages.you_want_to_deny_this_application_?') }}"
                                    href="javascript:" class="btn btn--danger font-regular request-alert">
                                    {{ translate('Reject') }} </a>
                            @endif
                            @if($dm->application_status != 'approved')

                                <a data-url="{{ route('admin.delivery-man.application', [$dm['id'], 'approved']) }}"
                                    data-message="{{ translate('messages.you_want_to_approve_this_application_?') }}"
                                    href="javascript:" class="btn btn-success request-alert"> {{ translate('Approve') }} </a>

                            @endif

                        </div>
                    </div>
                </div>
                <!-- End Page Header -->
                <!-- Banner -->
                <section class="shop-details-banner">
                    <div class="card">
                        <div class="card-body px-0 pt-0">

                            <div class="shop-details-banner-content m-0">
                                <div class="shop-details-banner-content-thumbnail mt-3">
                                    <img class="onerror-image"
                                        data-onerror-image="{{dynamicAsset('assets/admin/img/160x160/img1.jpg')}}"
                                        src="{{ $dm['image_full_url'] }}" alt="{{$dm['f_name']}} {{$dm['l_name']}}">
                                    <h3 class="mt-4 pt-3 mb-4 d-sm-none">{{ $dm->f_name . ' ' . $dm->l_name }}</h3>
                                </div>
                                <div class="shop-details-banner-content-content">
                                    <h3 class="mt-sm-4 pt-sm-3 mb-4 d-none d-sm-block">{{$dm->f_name . ' ' . $dm->l_name }}
                                    </h3>
                                    <div class="shop-details-model">
                                        <div class="shop-details-model-item">
                                            <img src="{{ dynamicAsset('assets/admin/new-img/icon-1.png') }}" alt="">
                                            <div class="shop-details-model-item-content">
                                                <h6> {{ translate('Job_Type') }} </h6>
                                                @if ($dm->earning == 1)
                                                    <div> {{   translate('Freelance') }}</div>
                                                @else
                                                    <div>

                                                        {{  translate('Salary_Base')}}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="shop-details-model-item">
                                            <img src="{{ dynamicAsset('assets/admin/new-img/icon-2.png') }}" alt="">
                                            <div class="shop-details-model-item-content">
                                                <h6> {{ translate('Vehicle_Type') }} </h6>
                                                <div> {{ $dm?->Vehicle?->type ?? translate('No_vehicle_found')}} </div>
                                            </div>
                                        </div>
                                        <div class="shop-details-model-item">
                                            <img src="{{ dynamicAsset('assets/admin/new-img/icon-4.png') }}" alt="">
                                            <div class="shop-details-model-item-content">
                                                <h6> {{ translate('Zone') }} </h6>
                                                <div>{{ $dm?->zone?->name ?? translate('No_zone_found') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-3">
                        <div class="card-header justify-content-between align-items-center">
                            <label class="input-label text-capitalize d-inline-flex align-items-center m-0">
                                <span class="line--limit-1"><img
                                        src="{{ dynamicAsset('assets/admin/img/company.png') }}" alt="">
                                    {{ translate('Registration_Information') }} </span>
                                <span data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('Registration_Information_Details') }}"
                                    class="input-label-secondary">
                                    <img src="{{ dynamicAsset('assets/admin/img/info-circle.svg') }}"
                                        alt="info"></span>
                            </label>
                        </div>
                        <div class="card-body">
                            <div class="__registration-information">
                                <div class="item">
                                    <h5> {{ translate('General_Information') }}</h5>
                                    <ul>
                                        <li>
                                            <span class="left"> {{ translate('First_Name') }} </span>
                                            <span class="right">{{ $dm->f_name }}</span>
                                        </li>
                                        <li>
                                            <span class="left">{{ translate('Last_Name') }} </span>
                                            <span class="right">{{ $dm->l_name  }}</span>
                                        </li>



                                        <li>
                                            <span class="left"> {{ translate('Email') }} </span>
                                            <span class="right">{{ $dm->email  }}</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="item" style="--width:74px">
                                    <h5> {{ translate('Identity_Information') }} </h5>
                                    <ul>
                                        <li>
                                            <span class="left"> {{ translate('Identity_Type') }}</span>
                                            <span class="right">{{ translate($dm->identity_type) }}</span>
                                        </li>
                                        <li>
                                            <span class="left"> {{ translate('Identity_Number') }}</span>
                                            <span class="right">{{ $dm->identity_number }}</span>
                                        </li>
                                        <li>
                                            <span class="left">{{ translate('Phone') }}</span>
                                            <span class="right">{{ $dm->phone }}</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="item" style="--width:74px">
                                    <h5>{{ translate('Login_Information') }}</h5>
                                    <ul>
                                        <li>
                                            <span class="left">{{ translate('Phone') }}</span>
                                            <span class="right">{{ $dm->phone }}</span>
                                        </li>
                                        <li>
                                            <span class="left">{{ translate('Password') }} </span>
                                            <span class="right">*************</span>
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        </div>
                            <div class="card-body">
                                <h5 class="mb-3"> {{ translate('Shift_Informations') }} </h5>
                                <div class="d-flex flex-wrap gap-4 align-items-start">
                                  @foreach ($dm->shifts as $item)

                                <h5>{{  translate($item->name) }}
                                        :  <span class="font-light">
                                        @if ($item->is_full_day)
                                            {{ translate('Will receive delivery orders 24/7.') }}
                                        @else
                                         {{ translate('Will receive delivery orders') }}   ({{App\CentralLogics\Helpers::time_format($item->start_time)}}  -  {{App\CentralLogics\Helpers::time_format($item->end_time) }})
                                            @endif
                                        </span>
                                </h5>
                                @endforeach
                                </div>
                            </div>

                        @if($dm->identity_image && count(json_decode($dm->identity_image, true)) > 0)
                            <div class="card-body">
                                <h5 class="mb-3"> {{ translate('Identity_Informations') }} </h5>
                                <div class="d-flex flex-wrap gap-4 align-items-start">
                                    @foreach ($dm->identity_image_full_url as $key => $item)
                                        <div class="attachment-card max-w-360">
                                            <a href="{{$item }}" download class="download-icon">
                                                <img src="{{ dynamicAsset('assets/admin/img/download/download.svg') }}"
                                                    alt="">
                                            </a>
                                            <img src="{{$item }}" data-toggle="modal" data-target="#image-modal"
                                                class="aspect-615-350 cursor-pointer mw-100 object--cover" alt="">
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        @endif

                        @if($dm->additional_data && count(json_decode($dm->additional_data, true)) > 0)
                            <div class="row mb-2">

                                <div class="col-lg-12  mt-2">
                                    <div class="card ">

                                        <div class="card-header justify-content-between align-items-center">
                                            <label class="input-label text-capitalize d-inline-flex align-items-center m-0">
                                                <span class="line--limit-1"><img
                                                        src="{{ dynamicAsset('assets/admin/img/company.png') }}" alt="">
                                                    {{ translate('Additional_Information') }} </span>
                                                <span data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ translate('Additional_Information') }}"
                                                    class="input-label-secondary">
                                                    <img src="{{ dynamicAsset('assets/admin/img/info-circle.svg') }}"
                                                        alt="info"></span>
                                            </label>
                                        </div>
                                        <div class="card-body">
                                            <div class="__registration-information">
                                                <div class="item">
                                                    <ul>
                                                        @foreach (json_decode($dm->additional_data, true) as $key => $item)
                                                            @if (is_array($item))


                                                                @foreach ($item as $k => $data)
                                                                    <li>
                                                                        <span class="left"> {{ $k == 0 ? translate($key) : ''}} </span>
                                                                        <span class="right">{{ $data }} </span>
                                                                    </li>
                                                                @endforeach
                                                            @else
                                                                <li>
                                                                    <span class="left"> {{ translate($key) }} </span>
                                                                    <span class="right">{{ $item ?? translate('messages.N/A')}} </span>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    @include('admin-views.partials._additional_documents', ['documents' => $dm->additional_documents, 'prefix' => 'additional_documents/dm'])
                    <!-- Card -->



                </section>
                <!-- Banner -->

            </div>
        </div>

@endsection

    @push('script_2')
        <script>
            "use strict";
            $('.request-alert').on('click', function () {
                let url = $(this).data('url');
                let message = $(this).data('message');
                request_alert(url, message);
            })
            function request_alert(url, message) {
                Swal.fire({
                    title: "{{ translate('messages.are_you_sure_?') }}",
                    text: message,
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: 'default',
                    confirmButtonColor: '#FC6A57',
                    cancelButtonText: "{{ translate('messages.no') }}",
                    confirmButtonText: "{{ translate('messages.yes') }}",
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        location.href = url;
                    }
                })
            }
        </script>
    @endpush
