@extends('layouts.admin.app')

@section('title',translate('Customer_Details'))

@push('css_or_js')
    <style>
        .pac-container {
            z-index: 100000 !important;
        }
    </style>
@endpush
@section('customerDetails')
    active
@endsection

@section('content')
    <div class="content container-fluid">
@php
use App\CentralLogics\Helpers;
@endphp
        <!-- Customer Details for 8.5 Start -->
        <div class="page-header">
            <div class="row flex-nowrap align-items-center mb-1 g-2">
                <div class="col-sm">
                    <h1 class="page-header-title mb-0 gap-1 flex-wrap">
                       {{translate('messages.customer_id')}}  <span class="gray-dark"> #{{$customer['id']}}</span>
                    </h1>
                </div>
                <div class="col-auto m-0 p-0">
                    <div class="d-flex align-items-center gap-3 flex-wrap">

                        <label class="toggle-switch toggle-switch-xs py-2 px-3 d-flex align-items-center gap-3 cursor-pointer bg-white rounded border min-h-41">
                            <span class="pr-1 d-flex align-items-center switch--label">
                                <span class="fs-14 fw-500 text-title">
                                    <span class="d-sm-inline-block d-none">{{translate('messages.Active')}}</span> {{translate('messages.Status')}}
                                </span>
                            </span>
                            <input type="checkbox" class="status status_change_alert toggle-switch-input"
                                data-url="{{ route('admin.customer.status', [$customer->id]) }}"  data-status="{{ $customer->status }}"
                            {{ $customer->status ? 'checked' : '' }} >
                            <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        @include('admin-views.customer.partials._user_details_urls')
        <div class="row g-3 mt-lg-2">
            <div class="col-lg-8">
                <div class="single-customer-leftside-bar">
                    <div class="row g-2 mb-2">
                        <div class="col-sm-6">
                            <div class="py-xxl-4 shadow-effect-hover1 global-shadow bg-white gap-10 d-flex rounded-8 gap-2 p--20">
                                <div class="w-40 min-w-40 h-40 rounded-circle d-center bg-white">
                                    <img width="22" src="{{dynamicAsset('assets/admin/img/total-spent-new.svg')}}" alt="img">
                                </div>
                                <div>
                                    <span class="text-gray fs-12 lh-1">{{translate('messages.Total Spent')}} </span>
                                    <h5 class="text-color m-0">{{ \App\CentralLogics\Helpers::format_currency($customer->total_order_amount) }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="py-xxl-4 shadow-effect-hover1 global-shadow bg-white gap-10 d-flex rounded-8 gap-2 p--20">
                                <div class="w-40 min-w-40 h-40 rounded-circle d-center bg-white">
                                    <img width="22" src="{{dynamicAsset('assets/admin/img/last-purchase.svg')}}" alt="img">
                                </div>
                                <div>
                                    <span class="text-gray fs-12 lh-1">{{translate('messages.Last Purchase')}} </span>
                                    <h5 class="text-color m-0">{{ $customer->lastOrder ? \App\CentralLogics\Helpers::human_time_format($customer->lastOrder->created_at) : translate('messages.N/A') }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="py-xxl-4 shadow-effect-hover1 global-shadow bg-white gap-10 d-flex rounded-8 gap-2 p--20">
                                <div class="w-40 min-w-40 h-40 rounded-circle d-center bg-white">
                                    <img width="22" src="{{dynamicAsset('assets/admin/img/order-value.svg')}}" alt="img">
                                </div>
                                <div>
                                    <span class="text-gray fs-12 lh-1">{{translate('messages.Avg. Order Value')}} </span>
                                    <h5 class="text-color m-0"> {{ $customer->total_order_amount > 0 && $customer->orders_count > 0 ? \App\CentralLogics\Helpers::format_currency($customer->total_order_amount / $customer->orders_count) : 0 }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="py-xxl-4 shadow-effect-hover1 global-shadow bg-white gap-10 d-flex rounded-8 gap-2 p--20">
                                <div class="w-40 min-w-40 h-40 rounded-circle d-center bg-white">
                                    <img width="22" src="{{dynamicAsset('assets/admin/img/order-price-range.svg')}}" alt="img">
                                </div>
                                <div>
                                    <span class="text-gray fs-12 lh-1"> {{translate('messages.Order price range')}} </span>
                                    <h5 class="text-color m-0">{{ \App\CentralLogics\Helpers::format_currency($orderRange->min_amount == $orderRange->max_amount ?  0 : $orderRange->min_amount) }} - {{ \App\CentralLogics\Helpers::format_currency($orderRange->max_amount ?? 0) }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="py-xxl-4 shadow-effect-hover1 global-shadow bg-white gap-10 d-flex rounded-8 p--20 h-100">
                                <div class="py-xxl-4 px-xxl-3 flex-grow-1 global-bg-box py-2 px-2 rounded h-100">
                                    <span class="text-gray fs-12 mb-2 d-block">{{translate('messages.Total Ordered Items')}} </span>
                                    <div class="d-flex align-items-center gap-1 justify-content-between">
                                        <h4 class="text-title m-0">{{ $itemsCount }}</h4>

                                        <a href="javascript:void(0)" class="fs-12 theme-clr text-underline offcanvas-trigger data-info-show"
                                            data-target="#offcanvas__customBtn3"
                                            data-id="{{ $customer->id }}"
                                            data-url="{{ route('admin.customer.top-items', [$customer->id]) }}">{{ translate('View Top 10') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="py-xxl-4 shadow-effect-hover1 global-shadow bg-white gap-10 d-flex rounded-8 p--20 h-100">
                                <div class="py-xxl-4 px-xxl-3 flex-grow-1 global-bg-box py-2 px-2 rounded h-100">
                                    <span class="text-gray fs-12 mb-2 d-block">{{translate('messages.Total Ordered Restaurants')}}</span>
                                    <div class="d-flex align-items-center gap-1 justify-content-between">
                                        <h4 class="text-title m-0">{{ $restaurantsCount }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-xxl-4 p-15 global-shadow bg-white rounded-10">
                        <div class="row g-2">
                            <div class="col-lg-3 col-md-4">
                                <div class="shadow-effect-hover1 global-shadow text-center global-bg-box gap-10 d-flex flex-column align-items-center justify-content-center rounded-8 gap-2 p--20 h-100">
                                    <div class="mb-4">
                                        <img width="35" src="{{dynamicAsset('assets/admin/img/total-box-list.svg')}}" alt="img">
                                    </div>
                                    <div>
                                        <h3 class="m-0" data-text-color="#3C76F1">{{ $orderRange->total_orders }}</h3>
                                        <span class="text-gray fs-14 lh-1"> {{ translate('Total Orders') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-9 col-md-8">
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <div class="py-xxl-4 shadow-effect-hover1 global-shadow global-bg-box gap-10 d-flex rounded-8 gap-2 p-15">
                                            <div class="w-40 min-w-40 h-40 rounded-circle d-center bg-white">
                                                <img width="22" src="{{dynamicAsset('assets/admin/img/delivery-new-box.svg')}}" alt="img">
                                            </div>
                                            <div>
                                                <h5 class="m-0" data-text-color="#019463">{{ $orderRange->total_delivered ?? 0 }}</h5>
                                                <span class="text-gray fs-12 lh-1"> {{ translate('Delivered') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="py-xxl-4 shadow-effect-hover1 global-shadow global-bg-box gap-10 d-flex rounded-8 gap-2 p-15">
                                            <div class="w-40 min-w-40 h-40 rounded-circle d-center bg-white">
                                                <img width="22" src="{{dynamicAsset('assets/admin/img/ongoing-new-box.svg')}}" alt="img">
                                            </div>
                                            <div>
                                                <h5 class="m-0" data-text-color="#E6A832">{{ $orderRange->total_on_going ?? 0 }}</h5>
                                                <span class="text-gray fs-12 lh-1"> {{ translate('Ongoing') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="py-xxl-4 shadow-effect-hover1 global-shadow position-relative global-bg-box gap-10 d-flex rounded-8 gap-2 p-15">
                                            <div class="w-40 min-w-40 h-40 rounded-circle d-center bg-white">
                                                <img width="22" src="{{dynamicAsset('assets/admin/img/cancel-new-box.svg')}}" alt="img">
                                            </div>
                                            <div>
                                                <h5 class="m-0" data-text-color="#C33D3D">{{ $orderRange->total_canceled ?? 0}}</h5>
                                                <span class="text-gray fs-12 lh-1"> {{ translate('Canceled') }}</span>
                                            </div>
                                            <span class="text-info text-left position-absolute right-0 top-0 m-2" data-toggle="tooltip" data-placement="top" data-original-title="{{translate('messages.Failed orders are also counted as canceled orders.')}}">
                                                <i class="tio-info fs-14"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="py-xxl-4 shadow-effect-hover1 global-shadow global-bg-box gap-10 d-flex rounded-8 gap-2 p-15">
                                            <div class="w-40 min-w-40 h-40 rounded-circle d-center bg-white">
                                                <img width="22" src="{{dynamicAsset('assets/admin/img/refund-new.svg')}}" alt="img">
                                            </div>
                                            <div>
                                                <h5 class="m-0" data-text-color="#FF4040">{{ $orderRange->total_refunded ?? 0}}</h5>
                                                <span class="text-gray fs-12 lh-1"> {{ translate('Refunded') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="h-100 single-customer-rightside-bar bg-white global-shadow rounded-8 p-xxl-4 p-15">
                    <div class="dropdown">
                        <button class="btn border-0 p-0 bg-transparent lh-1 fs-18 position-absolute right-0 top-0" type="button" data-toggle="dropdown" aria-expanded="false">
                            <i class="tio-more-horizontal lh-1"></i>
                        </button>
                        <div class="dropdown-menu me-3">

                            @if ((int) ($customer->sub_tenant_id ?? 0) <= 0)
                            <div class="dropdown-item d-flex align-items-center gap-1 fs-14 text-gray cursor-pointer">
                              <a href="{{ route('admin.customer.wallet.add-fund', ['customer_id' => $customer->id]) }}" target="_blank" rel="noopener noreferrer" class="d-flex align-items-center gap-1"> <i class="tio-wallet theme-clr"></i> {{ translate('Add Fund') }} </a>
                            </div>
                            @endif
                            <div class="dropdown-item d-flex align-items-center gap-1 fs-14 text-gray cursor-pointer  addressAdd__modal" data-url="{{ route('admin.customer.add-customer-address', $customer->id) }}" data-target="#addressEdit__modal">
                                <i class="tio-poi theme-clr"></i> {{ translate('Add Address') }}
                            </div>
                        </div>
                    </div>
                    <div class="mb-20">
                        <div class="text-center mb-3 rounded-circle position-relative d-inline-block w-100">
                            <span class="position-relative d-inline-block">
                                <img width="60" height="60" src="{{$customer->image_full_url}}" alt="img" class="rounded-circle">
                                @if ($customer->pro_status)
                                    @include('partials._pro_customer_badge', ['size' => 20])
                                @endif
                            </span>
                        </div>
                        <div class="mb-15 text-center">
                            <h4 class="text-color mb-1 lh-1">{{$customer->full_name}}</h4>
                            <span class="text-gray fs-12">{{ translate('Joined on') }} {{ \App\CentralLogics\Helpers::date_format($customer->created_at) }}</span>
                        </div>
                        <div class="social-media-group gap-3 d-flex align-items-center justify-content-center">
                            <a href="mailto:{{$customer->email}}" class="d-center rounded-10">
                                <i class="tio-messages"></i>
                            </a>
                            <a href="{{ route('admin.message.list',['tab'=> 'customer','customer_id' => $customer->id]) }}" target="_blank" class="d-center rounded-10">
                                <i class="tio-sms"></i>
                            </a>
                            <a href="tel:{{$customer->phone}}" class="d-center rounded-10">
                                <i class="tio-call"></i>
                            </a>
                        </div>
                    </div>
                    <div class="global-bg-box p-xxl-4 p-15 d-flex flex-column gap-3 mb-20">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-12 before-info text-gray w-60px">{{ translate('Phone') }}</span>
                            <span class="fs-12 text-color">{{$customer->phone}}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-12 before-info text-gray w-60px">{{ translate('Email') }}</span>
                            <span class="fs-12 text-color">{{$customer->email}}</span>
                        </div>
                        @php
                            $addressCount = $customer->addresses ? $customer->addresses()->count() : null  @endphp
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-12 before-info text-gray w-60px">{{ translate('Address') }}</span>
                            @if ($addressCount > 0)
                            <div class="d-flex align-items-center cursor-pointer">
                                <span class="fs-12 theme-clr offcanvas-trigger" style="text-decoration: underline;" data-target="#saved-address_offcanvas">{{ $addressCount }} {{ translate('Address saved') }}</span><i class="tio-chevron-right"></i>
                            </div>
                            @else

                                <div class="text-primary d-flex align-items-center gap-1 fs-12 cursor-pointer addressAdd__modal"
                                data-url="{{ route('admin.customer.add-customer-address', $customer->id) }}">
                                <i class="tio-poi theme-clr"></i> {{ translate('Add Address') }}
                            </div>

                            @endif


                        </div>
                    </div>
                    {{-- Wallet balance + loyalty points are hidden for storefront
                         (Builder) customers (sub_tenant_id > 0). --}}
                    @if ((int) ($customer->sub_tenant_id ?? 0) <= 0)
                    <div class="d-flex flex-column gap-3">
                        <div class="p-xxl-4 shadow-effect-hover d-flex align-items-center rounded-8 justify-content-between gap-2 p-15" data-bg-color="#F0FFF3">
                           <div>
                                <h4 class="fs-16 m-0" data-text-color="#019463">{{\App\CentralLogics\Helpers::format_currency($customer->wallet_balance)}}</h4>
                                <span class="fs-12" data-text-color="#656566">
                                   {{ translate('Wallet Balance') }}
                                </span>
                           </div>
                            <img width="38" height="38" src="{{dynamicAsset('assets/admin/img/new-wallet.svg')}}" alt="img">
                        </div>
                        <div class="p-xxl-4 shadow-effect-hover d-flex align-items-center rounded-8 justify-content-between gap-2 p-15" data-bg-color="#FFF9F0">
                           <div>
                                <h4 class="fs-16 m-0" data-text-color="#FFBB38">{{ $customer->loyalty_point ?? 0}}</h4>
                                <span class="fs-12" data-text-color="#656566">
                                      {{ translate('Loyalty Points') }}
                                </span>
                           </div>
                            <img width="40" height="40" src="{{dynamicAsset('assets/admin/img/new-star.svg')}}" alt="img">
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>


     <!-- Saved Address Offcanvas -->
    <div id="saved-address_offcanvas" class="custom-offcanvas d-flex flex-column justify-content-between" style="--offcanvas-width: 500px">
        <div>
            <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                <div class="px-3 py-3 d-flex justify-content-between w-100">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h2 class="mb-0 fs-18 text-title font-medium">{{ translate('Saved Address') }}</h2>

                    </div>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;
                    </button>
                </div>
            </div>
            <div class="custom-offcanvas-body p-20">
                <div class="d-flex flex-column gap-20px pb-5">

                        @forelse ($customer->addresses ?? [] as $address)

                        <div class="global-bg-box p-10px rounded">
                            <div class="d-flex align-items-cetner justify-content-between gap-2 flex-wrap mb-10px">
                                <h5 class="text-title m-0 text-capitalize">
                                    {{ $address->address_type }}
                                    {{-- <span class="gray-dark">(Shipping Address)</span> --}}
                                </h5>
                                <button type="button" class="btn p-0 bg-transparent text-primary addressEdit__modal"
                                        data-url="{{ route('admin.customer.edit-customer-address', $address->id) }}"
                                                data-address_id="{{ $address->id }}"  >
                                    <i class="tio-edit"></i>
                                </button>
                            </div>
                            <div class="bg-white rounded p-10px d-flex flex-column gap-1">
                                <div class="d-flex gap-2">
                                    <span class="before-info w-90px min-w-90 gray-dark fs-12">
                                        {{ translate('Name') }}
                                    </span>
                                    <span class="fs-12 text-title">
                                        {{ $address->contact_person_name }}
                                        ({{ $address->contact_person_number }})
                                    </span>
                                </div>
                                @if ($address->emai)
                                <div class="d-flex gap-2">
                                    <span class="before-info w-90px min-w-90 gray-dark fs-12">
                                        {{ translate('Email') }}
                                    </span>
                                    <span class="fs-12 text-title">
                                        {{ $address->email }}
                                    </span>
                                </div>
                                @endif
                                <div class="d-flex gap-2">
                                    <span class="before-info w-90px min-w-90 gray-dark fs-12">
                                        {{ translate('Address Type') }}
                                    </span>
                                    <span class="fs-12 text-title">
                                        {{ ucfirst($address->address_type) }}
                                    </span>
                                </div>

                                @if ($address->road )
                                <div class="d-flex gap-2">
                                    <span class="before-info w-90px min-w-90 gray-dark fs-12">
                                        {{ translate('Road') }}
                                    </span>
                                    <span class="fs-12 text-title">
                                        {{ $address->road }}
                                    </span>
                                </div>
                                @endif


                                @if ($address->house)
                                <div class="d-flex gap-2">
                                    <span class="before-info w-90px min-w-90 gray-dark fs-12">
                                        {{ translate('house') }}
                                    </span>
                                    <span class="fs-12 text-title">
                                        {{ $address->house }}
                                    </span>
                                </div>

                                @endif

                                @if ($address->floor)
                                <div class="d-flex gap-2">
                                    <span class="before-info w-90px min-w-90 gray-dark fs-12">
                                        {{ translate('Floor') }}
                                    </span>
                                    <span class="fs-12 text-title">
                                        {{ $address->floor }}
                                    </span>
                                </div>

                                @endif
                                <div class="d-flex gap-2">
                                    <span class="before-info align-items-start w-90px min-w-90 gray-dark fs-12">
                                        {{ translate('Address') }}
                                    </span>
                                    <span class="fs-12 text-title">
                                        @if ($address->latitude && $address->longitude)
                                        <a
                                        href="https://www.google.com/maps/search/?api=1&query={{ $address->latitude }},{{ $address->longitude }}"
                                        target="_blank"  class="fs-12 text-title" >
                                        {{ $address->address }}  </a>
                                        @else
                                        {{ $address->address }}
                                        @endif


                                    </span>
                                </div>
                            </div>
                        </div>

                        @empty

                        @endforelse

                </div>
            </div>
        </div>
        <div class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3 position-sticky">
            <button type="button"  data-url="{{ route('admin.customer.add-customer-address', $customer->id) }}"
            class="btn w-100 btn--primary addressAdd__modal">{{ translate('Add New Address') }}</button>
        </div>
    </div>







    <div id="offcanvas__customBtn3" class="custom-offcanvas d-flex flex-column justify-content-between">
        <div id="data-view" class="h-100">  </div>
    </div>
    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>




    <!--- Address Add Modal --->
    <div id="data-view-address-empty-address" class=""></div>

    <!--- Address Edit Modal --->
    <div id="data-view-address" class=""></div>



        <!-- Confirmation modal -->
    <div class="modal fade" id="confirmation_modal_customer" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
        aria-hidden="true">
        <div class=" modal-dialog max-w-500px mx-auto modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <form class="set_status_url" method="post">
                            @method('put')
                            @csrf
                            <div class="text-center">
                                <div>
                                    <img src="{{ dynamicAsset('assets/admin/img/subscription-plan/package-status-disable.png') }}"
                                        class="mb-20">

                                    <h5 class="modal-title"></h5>
                                </div>
                                <div class="text-center pb-0">
                                    <h3 class="mb-2 fs-18"> {{ translate('Are you sure ?') }}</h3>
                                    <div>
                                        <p>{{ translate('You want to Active this customer.') }}</h3>
                                        </p>
                                    </div>
                                </div>

                                <div class="btn--container justify-content-center mt-4 pt-1">
                                    <button data-dismiss="modal"
                                        class="btn btn--reset min-w-120">{{ translate('No') }}</button>
                                    <button type="submit" id=""
                                        class="btn btn--primary min-w-120">{{ translate('Yes') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Confirmation modal -->
    <div class="modal fade" id="confirmation_modal_customer_inactive" tabindex="-1" role="dialog"
        aria-labelledby="modalLabel" aria-hidden="true">
        <div class=" modal-dialog max-w-500px mx-auto modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <form class="set_status_url" method="post">
                            @method('put')
                            @csrf
                            <div class="text-center">
                                <div>
                                    <img src="{{ dynamicAsset('assets/admin/img/subscription-plan/package-status-disable.png') }}"
                                        class="mb-20">

                                    <h5 class="modal-title"></h5>
                                </div>
                                <div class="text-center pb-0">
                                    <h3 class="mb-2 fs-18"> {{ translate('Are you sure ?') }}</h3>
                                    <div>
                                        <p>{{ translate('You want to Inactive this customer.') }}</h3>
                                        </p>
                                    </div>
                                </div>

                                <div class="btn--container justify-content-center mt-4 pt-1">
                                    <button type="submit" id=""
                                        class="btn btn--danger min-w-120">{{ translate('Yes') }}</button>
                                    <button data-dismiss="modal"
                                        class="btn btn--reset min-w-120">{{ translate('No') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')


  <script src="{{dynamicAsset('assets/admin/js/view-pages/offcanvas-edit.js')}}"></script>

    <script  src="https://maps.googleapis.com/maps/api/js?key={{ Helpers::get_business_settings('map_api_key') }}&libraries=places,marker&callback=initMap&v=3.61"></script>

   <script>

        function fetch_address_data(url) {
            $.ajax({
                url: url,
                type: "get",
                beforeSend: function () {
                    $('#data-view-address-empty-address').empty();
                     $('#data-view-address').empty();
                    $('#loading').show()
                },
                success: function (data) {
                    $("#data-view-address").append(data.view);
                    $('#addressEdit__modal').modal('show');

                    initTextMaxLimit();
                    initMap(data.latitude, data.longitude);
                    initTelInputs();
                },
                complete: function () {
                    $('#loading').hide()
                }
            })
        }

        function fetch_address_empty_data(url) {
            $.ajax({
                url: url,
                type: "get",
                beforeSend: function () {
                    $('#data-view-address-empty-address').empty();
                     $('#data-view-address').empty();
                    $('#loading').show()
                },
                success: function (data) {
                    $("#data-view-address-empty-address").append(data.view);
                    $('#addressAdd__modal').modal('show');
                    initMap();
                    initTelInputs();
                },
                complete: function () {
                    $('#loading').hide()
                }
            })
        }


        $('.addressEdit__modal').on('click', function(event) {
            event.preventDefault();
            let address_id = $(this).data('address_id') || null;
            $('#address_id').val(address_id);
            let url = $(this).data('url');
            fetch_address_data(url);

        })
        $('.addressAdd__modal').on('click', function(event) {
            event.preventDefault();
            let url = $(this).data('url');
            fetch_address_empty_data(url);
        })

        $('.status_change_alert').on('click', function(event) {
            event.preventDefault();
            let url = $(this).data('url');
            let status = $(this).data('status');
            $('.set_status_url').attr('action', url);
            if (status) {
                $('#confirmation_modal_customer_inactive').modal().show();
            } else {
                $('#confirmation_modal_customer').modal().show();
            }
        })


        function initMap(lat = null, lng = null) {
            const defaultLat = {{  23.757989 }};
            const defaultLng = {{  90.360587 }};
            const mapId = "{{ Helpers::get_business_settings('map_api_key')  }}";

            const { AdvancedMarkerElement } = google.maps.marker;

                const map = new google.maps.Map(document.getElementById("location_map_canvas"), {
                    zoom: 13,
                    center: {
                        lat: lat != null ? parseFloat(lat) : defaultLat,
                        lng: lng != null ? parseFloat(lng) : defaultLng
                    },
                    mapId: mapId,
                });

            const geocoder = new google.maps.Geocoder();
            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);

            input.addEventListener("keydown", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                }
            });

            let marker = new AdvancedMarkerElement({
                map,
                draggable: true
            });

            if (lat != null && lng != null) {
                setLocation(parseFloat(lat), parseFloat(lng));

            }
            map.addListener("click", (e) => {
                setLocation(e.latLng.lat(), e.latLng.lng());
            });


            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();
                if (places.length === 0) return;

                const place = places[0];
                if (!place.geometry || !place.geometry.location) return;

                setLocation(place.geometry.location.lat(), place.geometry.location.lng());
                map.panTo(place.geometry.location);
                map.setZoom(15);
            });

            marker.addListener("dragend", () => {
                const pos = marker.getPosition();
                setLocation(pos.lat(), pos.lng());
            });

            function setLocation(lat, lng) {
                document.getElementById("latitude").value = lat;
                document.getElementById("longitude").value = lng;

                const latlng = { lat: lat, lng: lng };
                marker.position = latlng;

                geocoder.geocode({ location: latlng }, (results, status) => {
                    if (status === "OK" && results[0]) {
                        document.getElementById("address").value = results[0].formatted_address;
                    }
                });
            }
        }


    </script>

@endpush
