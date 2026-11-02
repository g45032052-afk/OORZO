@extends('layouts.admin.app')

@section('title', translate('Customer_list'))

@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@section('customerDetails')
    active
@endsection

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm">
                <h1 class="page-header-title gap-1 flex-wrap">
                    {{ translate('messages.Customer Details') }} <span class="gray-dark"> #{{ $customer->id }}</span>
                </h1>
            </div>
        </div>
    </div>
    @include('admin-views.customer.partials._user_details_urls')
    <div class="row g-3">

        <div class="col-xxl-6 col-xl-12 col-lg-6">
            <div class="card wishlist__controller-inner">
                <div class="card-header fs-14 text-dark font-semibold">
                    <div>
                        {{ translate('messages.Food') }} <span class="badge badge-soft-dark ml-2"  >{{ $foodWishList->total() }}</span>
                    </div>
                </div>
                <div class="card-body">
                    @if (count($foodWishList) > 0)
                    <div class="row g-3">
                        @foreach ($foodWishList as $wishlist)
                        <div class="col-md-12">
                            <div class="d-flex text-dark align-items-sm-center gap-10 global-bg-box rounded py-sm-3 py-2 px-xxl-4 px-3">
                                <a href="{{ route('admin.food.view', $wishlist->food?->id) }}" target="_blank" class="w-48">
                                    <img width="48" height="48" src="{{$wishlist->food->image_full_url}}" alt="img" class="rounded">
                                </a>
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 flex-grow-1">
                                    <div>
                                        <h5 class="mb-0 font-normal text-color">
                                            <a href="{{ route('admin.food.view', $wishlist->food?->id) }}" target="_blank" class="text-dark">
                                                {{Str::limit($wishlist->food->name,20 , '...')}}
                                            </a>
                                        </h5>
                                       <a href="{{$wishlist->food ? route('admin.restaurant.view', $wishlist->food?->restaurant?->id) :'#' }}" target="_blank" >
                                           <span class="fs-12 text-secondary">
                                               {{Str::limit( $wishlist->food?->restaurant?->name,20 , '...') }}
                                               @include('partials._verified_restaurant_badge', ['restaurant' => $wishlist->food?->restaurant])
                                           </span>
                                       </a>
                                    </div>
                                    <h5 class="m-0 font-regular text-color">{{ \App\CentralLogics\Helpers::format_currency($wishlist->food?->price) }}</h5>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    @if (count($foodWishList) == 0)
                    <div class="d-flex align-items-center justify-content-center w-100 h-100">
                        <div class="text-center text-gray1 fs-16">
                            <img src="{{dynamicAsset('assets/admin/img/no-wishlist-here.svg')}}" alt="no" class="d-block mb-10px mx-auto">
                            {{ translate('No Food Wishlist') }}
                        </div>
                    </div>
                    @endif
                </div>

            <div class="card-footer p-0 border-0">
                <!-- Pagination -->
                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div>
                            {!! $foodWishList->appends(request()->except('foods_page'))->links() !!}
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>


        <div class="col-xxl-6 col-xl-12 col-lg-6">
            <div class="card wishlist__controller-inner">
                <div class="card-header fs-14 text-dark font-semibold">
                    <div>
                        {{ translate('Restaurant') }}  <span class="badge badge-soft-dark ml-2" id="count">{{ $restaurantWishList->total() }}</span>
                    </div>
                </div>
                <div class="card-body">
                    @if (count($restaurantWishList) > 0)
                    <div class="row g-3">
                        @foreach ($restaurantWishList as $wishlist)
                        <div class="col-md-12">
                            <div class="d-flex text-dark align-items-sm-center gap-10 global-bg-box rounded py-sm-3 py-2 px-xxl-4 px-3">
                                <a href="{{ route('admin.restaurant.view', $wishlist->restaurant?->id) }}" target="_blank" class="w-48">
                                    <img width="48" height="48" src="{{$wishlist->restaurant->logo_full_url}}" alt="img" class="rounded">
                                </a>
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 flex-grow-1">
                                    <div>
                                        <h5 class="mb-0 font-normal text-color">
                                            <a href="{{ route('admin.restaurant.view', $wishlist->restaurant?->id) }}" target="_blank" class="text-dark">
                                                {{Str::limit($wishlist->restaurant->name,20 , '...')}}
                                            </a>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    @if (count($restaurantWishList) == 0)
                    <div class="d-flex align-items-center justify-content-center w-100 h-100">
                        <div class="text-center text-gray1 fs-16">
                            <img src="{{dynamicAsset('assets/admin/img/no-wishlist-here.svg')}}" alt="no" class="d-block mb-10px mx-auto">
                            {{ translate('No Restaurant Wishlist') }}
                        </div>
                    </div>
                    @endif
                </div>

            <div class="card-footer p-0 border-0">
                <!-- Pagination -->
                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div>
                            {!! $restaurantWishList->appends(request()->except('restaurant_page'))->links() !!}
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>




@endsection

@push('script_2')
<script>
"use strict";
$(document).on('ready', function() {
    // INITIALIZATION OF NAV SCROLLER
    // =======================================================
    $('.js-nav-scroller').each(function() {
        new HsNavScroller($(this)).init()
    });

    // INITIALIZATION OF SELECT2
    // =======================================================
    $('.js-select2-custom').each(function() {
        let select2 = $.HSCore.components.HSSelect2.init($(this));
    });
});
</script>
@endpush
