@extends('layouts.admin.app')

@section('title',$restaurant->name)

<?php $verified_seller_badge = \App\CentralLogics\Helpers::get_business_settings('verified_seller_badge'); ?>
<?php $verified_seller = $restaurant?->restaurant_config?->verified_seller; ?>
<?php
    $reviewsInfo = $restaurant->reviews()->where('reviews.status', 1)->selectRaw('AVG(reviews.rating) as average_rating, COUNT(reviews.id) as total_reviews')->first();
    $average_rating = round((float) ($reviewsInfo->average_rating ?? 0), 1);
    $review_count = (int) ($reviewsInfo->total_reviews ?? 0);
    $order_stats = $restaurant->orders()->selectRaw("
        COUNT(*) as total_orders,
        SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
        SUM(CASE WHEN order_status = 'canceled' THEN 1 ELSE 0 END) as canceled_orders
    ")->first();
    $total_orders = (int) ($order_stats->total_orders ?? 0);
    $delivered_orders = (int) ($order_stats->delivered_orders ?? 0);
    $canceled_orders = (int) ($order_stats->canceled_orders ?? 0);
    $comparison_orders = $delivered_orders + $canceled_orders;
    $performance_rate = $average_rating > 2 && $comparison_orders > 0 ? round(($delivered_orders / $comparison_orders) * 100) : null;
?>

@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{dynamicAsset('assets/admin/css/croppie.css')}}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <h1 class="page-header-title text-break me-2">
                    <i class="tio-shop"></i> <span>{{$restaurant->name}}</span>
                    @include('partials._verified_restaurant_badge', ['restaurant' => $restaurant])
                </h1>
                @if($restaurant->vendor->status)
                    <div class="d-flex flex-wrap gap-2 my-2">
                           @if ($restaurant->status == 1 && $restaurant->vendor->status == 1 && \App\CentralLogics\Helpers::get_business_settings('verified_seller_badge'))
                            <a href="javascript:"
                               class="btn  {{ $verified_seller ? 'btn btn--secondary ' : 'btn-success bg-success-gradient' }}"
                               data-toggle="modal"
                               data-target="{{ $verified_seller ? '#remove-badge-btn' : '#badge-modal-btn' }}">
                                {{ $verified_seller ? translate('messages.removed_verified_badge') : translate('messages.give_verified_badge') }}
                            </a>
                        @endif
                        <a href="{{route('admin.restaurant.edit',[$restaurant->id])}}" class="btn btn--primary">
                            <i class="tio-edit mr-2"></i> {{translate('messages.edit_restaurant')}}
                        </a>
                    </div>
                @else
                    <div>
                        @if(!isset($restaurant->vendor->status))
                            <a class="btn btn--danger text-capitalize my-2 request_alert"
                               data-url="{{route('admin.restaurant.application',[$restaurant['id'],0])}}" data-message="{{translate('messages.you_want_to_deny_this_application')}}"
                               href="javascript:">{{translate('messages.deny')}}</a>
                        @endif
                        <a class="btn btn--primary text-capitalize my-2 request_alert"
                           data-url="{{route('admin.restaurant.application',[$restaurant['id'],1])}}" data-message="{{translate('messages.you_want_to_approve_this_application')}}"
                           href="javascript:">{{translate('messages.approve')}}</a>
                    </div>
                @endif
            </div>
            @if($restaurant->vendor->status)
                <!-- Nav Scroller -->
                <div class="js-nav-scroller hs-nav-scroller-horizontal">
                    <!-- Nav -->
                    @include('admin-views.vendor.view.partials._header',['restaurant'=>$restaurant])

                    <!-- End Nav -->
                </div>
                <!-- End Nav Scroller -->
            @endif
        </div>
        <!-- End Page Header -->
        <!-- Page Heading -->
        <div class="row my-2 g-3">
            <!-- Earnings (Monthly) Card Example -->
            <div class="for-card col-md-4">
                <div class="card bg--1 h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
                        <div class="cash--subtitle">
                            {{translate('messages.collected_cash_by_restaurant')}}
                        </div>
                        <div class="d-flex align-items-center justify-content-center mt-3">
                            <img class="cash-icon mr-3" src="{{dynamicAsset('assets/admin/img/transactions/cash.png')}}"
                                 alt="transactions">
                            <h2 class="cash--title">{{\App\CentralLogics\Helpers::format_currency($wallet->collected_cash)}}
                            </h2>
                        </div>
                    </div>
                    <div class="card-footer pt-0 bg-transparent">
                        <a class="btn btn-- bg--title h--45px w-100" href="{{route('admin.account-transaction.index')}}"
                           title="{{translate('messages.goto_account_transaction')}}">{{translate('messages.collect_cash_from_restaurant')}}</a>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="row g-3">
                    <!-- Panding Withdraw Card Example -->
                    <div class="col-sm-6">
                        <div class="resturant-card  bg--2">
                            <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($wallet->pending_withdraw)}}
                            </h4>
                            <span class="subtitle">{{translate('messages.pending_withdraw')}}</span>
                            <img class="resturant-icon" src="{{dynamicAsset('assets/admin/img/transactions/pending.png')}}"
                                 alt="transactions">
                        </div>
                    </div>

                    <!-- Earnings (Monthly) Card Example -->
                    <div class="col-sm-6">
                        <div class="resturant-card  bg--3">
                            <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($wallet->total_withdrawn)}}</h4>
                            <span class="subtitle">{{translate('messages.total_withdrawn_amount')}}</span>
                            <img class="resturant-icon"
                                 src="{{dynamicAsset('assets/admin/img/transactions/withdraw-amount.png')}}"
                                 alt="transactions">
                        </div>
                    </div>

                    <!-- Collected Cash Card Example -->
                    <div class="col-sm-6">
                        <div class="resturant-card  bg--5">
                            @if($wallet->balance ==  0)
                                <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($wallet->balance)}}</h4>
                                <span class="subtitle">{{translate('messages.Balance')}}</span>
                            @elseif($wallet->balance >  0)
                                <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($wallet->balance)}}</h4>
                                <span class="subtitle">{{translate('messages.Withdraw_able_balance')}}</span>
                            @else
                                <h4 class="title">{{\App\CentralLogics\Helpers::format_currency(abs($wallet->balance))}}</h4>
                                <span class="subtitle">{{translate('messages.Payable_balance')}}</span>

                            @endif



                            <img class="resturant-icon"
                                 src="{{dynamicAsset('assets/admin/img/transactions/withdraw-balance.png')}}"
                                 alt="transactions">
                        </div>
                    </div>

                    <!-- Pending Requests Card Example -->
                    <div class="col-sm-6">
                        <div class="resturant-card  bg--1">
                            <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($wallet->total_earning)}}</h4>
                            <span class="subtitle">{{translate('messages.total_earning')}}</span>
                            <img class="resturant-icon" src="{{dynamicAsset('assets/admin/img/transactions/earning.png')}}"
                                 alt="transactions">
                        </div>
                    </div>

                </div>

            </div>
        </div>
        <div class="mt-4">
            <div id="restaurant_details" class="row g-3">
                <div class="col-lg-12">
                    <div class="card mt-2">
                        <div class="card-header">
                            <h5 class="card-title m-0 d-flex align-items-center">
                            <span class="card-header-icon mr-2">
                                <i class="tio-shop-outlined"></i>
                            </span>
                                <span class="ml-1">{{translate('messages.restaurant_info')}}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center g-3">
                                <div class="col-lg-6">
                                    <div class="resturant--info-address">
                                        <div class="logo">
                                            <img class="onerror-image" data-onerror-image="{{dynamicAsset('assets/admin/img/100x100/restaurant-default-image.png')}}"

                                                 src="{{ $restaurant->logo_full_url ?? dynamicAsset('assets/admin/img/100x100/restaurant-default-image.png') }}">
                                        </div>
                                        <ul class="address-info list-unstyled list-unstyled-py-3 text-dark">
                                            <li>
                                                <h5 class="name d-flex align-items-center gap-1 flex-wrap">
                                                    <span>{{$restaurant->name}}</span>
                                                    @include('partials._verified_restaurant_badge', ['restaurant' => $restaurant])
                                                </h5>
                                            </li>
                                            <li>
                                                <i class="tio-city nav-icon"></i>
                                                <span class="pl-1">
                                                {{translate('messages.address')}} : {{$restaurant->address}}
                                            </span>
                                            </li>

                                            <li>
                                                <i class="tio-call-talking nav-icon"></i>
                                                <span class="pl-1">
                                                {{translate('messages.phone')}} : <a class="text-dark" href="tel:{{$restaurant->phone}}">{{$restaurant->phone}}</a>
                                            </span>
                                            </li>
                                            <li>
                                                <i class="tio-email nav-icon"></i>
                                                <span class="pl-1">
                                                {{translate('messages.email')}} :  <a class="text-dark" href="mailto:{{$restaurant->email}}">{{$restaurant->email}}</a>
                                            </span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div id="map" class="single-page-map"></div>
                                </div>
                                <div class="col-12">
                                        <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info mb-1" style="--bs-bg-opacity: 0.1;">
                                            <span class="text-info lh-1 fs-14">
                                                <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                                            </span>
                                            <span>
                                                @if (!is_null($performance_rate))
                                                    {{ translate('messages.this_restaurants_performance_is_rated_as') }}
                                                    <span class="fz-12px font-semibold {{ $performance_rate >= 80 ? 'text-success' : ($performance_rate >= 50 ? 'text-warning' : 'text-danger') }}">
                                                        <a class="{{ $performance_rate >= 80 ? 'text-success' : ($performance_rate >= 50 ? 'text-warning' : 'text-danger') }}" href="#0">
                                                            {{ $performance_rate >= 80 ? translate('messages.good') : ($performance_rate >= 50 ? translate('messages.average') : translate('messages.needs_improvement')) }}
                                                        </a>
                                                    </span>
                                                    {{ translate('messages.based_on_its_overall_activity_reliability_and_service_quality') }}
                                                @else
                                                    {{ translate('messages.this_restaurant_does_not_have_much_available_data') }}
                                                @endif
                                            </span>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    @if ($restaurant->tin || $restaurant->tin_certificate_image)
                        <div class="row pt-3 g-3">
                            <div class="col-lg-12">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title m-0 d-flex align-items-center">
                                            <span class="card-header-icon mr-2">
                                                <i class="tio-user"></i>
                                            </span>
                                            <span class="ml-1">{{ translate('TIN Documents') }}</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="resturant--info-address flex-sm-nowrap flex-wrap gap-2 single-document-uploaderwrap" data-document-uploader>
                                            <div>
                                                <div class="file-assets"
                                                     data-picture-icon="{{ dynamicAsset('assets/admin/img/picture.svg') }}"
                                                     data-document-icon="{{ dynamicAsset('assets/admin/img/document.svg') }}"
                                                     data-blank-thumbnail="{{ dynamicAsset('assets/admin/img/picture.svg') }}">
                                                </div>
                                                <!-- Upload box -->
                                                <div class="d-flex align-items-center flex-xxl-nowrap flex-wrap gap-2 justify-content-center pdf-container">
                                                    <div class="document-upload-wrapper d-none">
                                                        <input type="file" name="tin_certificate_image" class="document_input" accept=".doc, .pdf, .jpg, .png, .jpeg">
                                                        <div class="textbox">
                                                            <img width="40" height="40" class="svg"
                                                                 src="{{ dynamicAsset('assets/admin/img/doc-uploaded.png') }}"
                                                                 alt="">
                                                            <p class="fs-12 mb-0">{{ translate('Select a file or') }} <span class="font-semibold">{{ translate('Drag & Drop') }}</span>
                                                                {{ translate('here') }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="pdf-single pdf-single-reponsive" data-file-name="${file.name}" data-file-url="{{ $restaurant->tin_certificate_image_full_url ?? dynamicAsset('assets/admin/img/upload-cloud.png') }}">
                                                        <div class="pdf-frame">
                                                            @php
                                                                $imgPath =  $restaurant->tin_certificate_image_full_url ?? dynamicAsset('assets/admin/img/upload-cloud.png');
                                                            @endphp
                                                            @if(Str::endsWith($imgPath, ['.pdf', '.doc', '.docx']))
                                                                @php
                                                                    $imgPath =  dynamicAsset('assets/admin/img/document.svg');
                                                                @endphp
                                                            @endif
                                                            <img class="pdf-thumbnail-alt" src="{{ $imgPath }}" alt="File Thumbnail">
                                                        </div>
                                                        <div class="overlay">
                                                            <div class="pdf-info">
                                                                @if(Str::endsWith($imgPath, ['.pdf', '.doc', '.docx']))
                                                                    <img src="{{ dynamicAsset('assets/admin/img/document.svg') }}" width="34" alt="File Type Logo">
                                                                @else
                                                                    <img src="{{ dynamicAsset('assets/admin/img/picture.svg') }}" width="34" alt="File Type Logo">
                                                                @endif
                                                                <div class="file-name-wrapper">
                                                                    <span class="file-name js-filename-truncate text-limit-show" data-limit="15">{{ $restaurant->tin_certificate_image }}</span>
                                                                    <span class="opacity-50">{{ translate('Click to view the file') }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if ($restaurant->tin)

                                                        <div class="d-flex-column address-info address-info-2 list-unstyled list-unstyled-py-3">
                                                            <div class=" d-flex justify-content-start gap-1">
                                                    <span class="text-custom-nowrap text-wrap"><strong class=" text-dark">
                                                        {{ translate('Taxpayer Identification Number(TIN)') }}: </strong></span>
                                                                <span class="pl-1">{{ $restaurant->tin }}</span>
                                                            </div>
                                                            <div class=" d-flex justify-content-start gap-1">
                                                        <span class="text-custom-nowrap text-wrap"><strong
                                                                class=" text-dark">{{ translate('Expire Date') }}: </strong></span>
                                                                <span class="pl-1">{{ $restaurant->tin_expire_date }}</span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title m-0 d-flex align-items-center">
                            <span class="card-header-icon mr-2">
                                <i class="tio-user"></i>
                            </span>
                                <span class="ml-1">{{translate('messages.owner_info')}}</span>
                            </h5>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-center">
                            <div class="resturant--info-address">
                                <div class="avatar avatar-xxl avatar-circle avatar-border-lg">
                                    <img class="avatar-img onerror-image" data-onerror-image="{{dynamicAsset('assets/admin/img/160x160/img1.jpg')}}"
                                         src="{{ $restaurant?->vendor?->image_full_url ?? dynamicAsset('assets/admin/img/160x160/img1.jpg') }}"
                                         alt="Image Description">
                                </div>
                                <ul class="address-info address-info-2 list-unstyled list-unstyled-py-3 text-dark">
                                    <li>
                                        <h5 class="name">
                                            {{$restaurant->vendor->f_name}} {{$restaurant->vendor->l_name}}
                                        </h5>
                                    </li>
                                    <li>
                                        <i class="tio-call-talking nav-icon"></i>
                                        <span class="pl-1">
                                            <a class="text-dark" href="tel:{{$restaurant->vendor->phone}}">{{$restaurant->vendor->phone}}</a>
                                        </span>
                                    </li>
                                    <li>
                                        <i class="tio-email nav-icon"></i>
                                        <span class="pl-1">
                                                <a class="text-dark" href="mailto:{{$restaurant->vendor->email}}">{{$restaurant->vendor->email}}</a>
                                            </span>

                                    </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title m-0 d-flex align-items-center">
                            <span class="card-header-icon mr-2">
                                <i class="tio-crown"></i>
                            </span>
                                <span class="ml-1">{{translate('messages.Business_Plan')}}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="resturant--info-address">
                                <ul class="address-info address-info-2 list-unstyled list-unstyled-py-3 text-dark">

                                    @if ($restaurant->restaurant_model == 'commission')
                                        <li>
                                            <span>  <strong>{{translate('messages.Business_Plan')}}</span></strong>  <span>:</span> &nbsp; {{ translate($restaurant->restaurant_model) }}
                                        </li>
                                        @php
                                            $admin_commission = \App\Models\BusinessSetting::where(['key' => 'admin_commission'])->first()?->value;
                                        @endphp
                                        <li>
                                            <span><strong>{{translate('messages.Commission_percentage')}}</strong></span> <span>:</span> &nbsp; {{ $restaurant->comission > 0 ?  $restaurant->comission : $admin_commission }} %
                                        </li>
                                    @elseif ($restaurant->restaurant_model == 'subscription')
                                        <li>
                                            <span>  <strong>{{translate('messages.Business_Plan')}}</span></strong>  <span>:</span> &nbsp; {{ translate($restaurant->restaurant_model) }} &nbsp;
                                            @if ($restaurant?->restaurant_sub_update_application->is_trial == '1')
                                                <small> <span class="badge badge-info" >{{ translate('messages.Free_trial')}}</span> </small>
                                            @endif
                                        </li>
                                        <li>
                                            <span> <strong>{{translate('messages.Package_name')}}</strong></span> <span>:</span> &nbsp; {{ $restaurant?->restaurant_sub_update_application?->package?->package_name  ?? translate('Pacakge_not_found!!!')}}
                                        </li>
                                    @elseif ($restaurant->restaurant_model == 'unsubscribed' && $restaurant?->restaurant_sub_update_application )
                                        <li>
                                            <span>  <strong>{{translate('messages.Business_Plan')}}</span></strong>  <span>:</span> &nbsp; {{ translate($restaurant->restaurant_model) }} &nbsp;

                                            <small> <span class="badge badge-danger" >{{ translate('messages.Expired')}}</span> </small>

                                        </li>
                                        <li>
                                            <span> <strong>{{translate('messages.Package_name')}}</strong></span> <span>:</span> &nbsp; {{ $restaurant?->restaurant_sub_update_application?->package?->package_name  ?? translate('Pacakge_not_found!!!')}}
                                        </li>
                                    @elseif ($restaurant->restaurant_model == 'unsubscribed')
                                        <li>
                                            <span>  <strong>{{translate('messages.Business_Plan')}}</span></strong>  <span>:</span> &nbsp; {{ translate('Plan_isn’t_selected_yet') }} &nbsp;
                                        </li>

                                    @elseif($restaurant->restaurant_model == 'none' && $restaurant->package_id )
                                        <li>
                                            <span>  <strong>{{translate('messages.Business_Plan')}}</span></strong>  <span>:</span> &nbsp; {{translate('messages.Subscription')}}
                                        </li>
                                        <li>
                                            <span>  <strong>{{translate('messages.Package_Name')}}</span></strong>  <span>:</span> &nbsp; {{App\Models\SubscriptionPackage::where('id',$restaurant->package_id)->first()?->package_name   }}
                                        </li>
                                        <li>
                                            <span>  <strong>{{translate('Payment_status')}}</span></strong>  <span>:</span> &nbsp; {{ translate('messages.payment_failed')   }}
                                        </li>
                                    @else
                                        <li>
                                            <span>  <strong>{{translate('messages.Business_Plan')}}</span></strong>  <span>:</span> &nbsp; {{ translate('Plan_isn’t_selected_yet.') }}
                                        </li>
                                    @endif




                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    @if($restaurant->additional_data && count(json_decode($restaurant->additional_data, true)) > 0 )
                        <div class="row mb-2">
                            <div class="col-lg-12  mt-2">
                                <div class="card h-100">

                                    <div class="card-header justify-content-between align-items-center">
                                        <label class="input-label text-capitalize d-inline-flex align-items-center m-0">
                                            <h5 class="d-flex align-items-center gap-2">
                                                <img src="{{ dynamicAsset('assets/admin/img/company.png') }}" alt="">
                                                <span class="line--limit-1 mt-1">
                                            {{ translate('Additional_Information') }}
                                        </span>
                                            </h5>
                                            <span data-toggle="tooltip" data-placement="right"
                                                  data-original-title="{{ translate('Optional details about restaurant.') }}"
                                                  class="input-label-secondary">
                                        <img width="14" src="{{ dynamicAsset('assets/admin/img/info-circle.svg') }}" alt="info"></span>
                                        </label>
                                    </div>
                                    <div class="card-body">
                                        <div class="__registration-information p-0">
                                            <div class="item">
                                                <ul>
                                                    @foreach (json_decode($restaurant->additional_data, true) as $key => $item)
                                                        @if (is_array($item))


                                                            @foreach ($item as $k => $data)
                                                                <li>
                                                                    <span class="left fs-14"> {{ $k == 0 ? translate($key) : ''}} </span>
                                                                    <span class="right fs-14">{{ $data }} </span>
                                                                </li>
                                                            @endforeach
                                                        @else
                                                            <li>
                                                                <span class="left fs-14"> {{ translate($key) }} </span>
                                                                <span class="right fs-14">{{ $item ?? translate('messages.N/A')}} </span>
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
                <div class="col-lg-6">
                    @include('admin-views.partials._additional_documents', ['documents' => $restaurant->additional_documents, 'prefix' => 'additional_documents'])
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{\App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value}}&libraries=marker&callback=initMap&v=3.61">
    </script>
    <script src="{{ dynamicAsset('assets/admin') }}/js/file-preview/document-upload.js"></script>
    <script>
        "use strict";

        $(document).ready(function () {
            $('#dataTable').DataTable();
        });

        const mapId = "{{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}";
        const { AdvancedMarkerElement } = google.maps.marker;

        const myLatLng = { lat: {{$restaurant->latitude}}, lng: {{$restaurant->longitude}} };
        let map;
        initMap();
        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: myLatLng,
                mapId: mapId,
            });
            new AdvancedMarkerElement({
                map,
                position: myLatLng,
                title: "{{$restaurant->name}}",
            });
        }

        $('.request_alert').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            request_alert(url, message)
        })

        function request_alert(url, message) {
            Swal.fire({
                title: "{{translate('messages.are_you_sure_?')}}",
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: "{{translate('messages.no')}}",
                confirmButtonText: "{{translate('messages.yes')}}",
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = url;
                }
            })
        }
    </script>
@endpush
