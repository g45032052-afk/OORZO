@extends('layouts.vendor.app')

@section('title', translate('messages.Reels_List'))
@section('vendor_reels', 'active')
@section('vendor_reels_list', 'active')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- reels.css is already loaded in the layout <head>; moment/daterangepicker
         are bundled in theme.min.js. They were previously re-included here as
         render-blocking <script> tags in <head>, which caused a flash of
         unstyled content on reload. --}}
@endpush

@php
    $storeLabel = translate('messages.Restaurant');
    $storeInformationLabel = $storeLabel . ' ' . translate('messages.information');
    $deletedStoreLabel = $storeLabel . ' ' . translate('messages.deleted');
    $filterCount = $filterCount ??'';

    $overviewCards = [
        [
            'value' => $overview['total_reels'] ?? 0,
            'label' => translate('messages.Total_Reels'),
            'icon' => 'tio-video-camera-outlined',
            'color' => 'text-purple',
            'bg' => 'bg-purple bg-opacity-10',
        ],
        [
            'value' => $overview['total_views'] ?? 0,
            'label' => translate('messages.Total_Views'),
            'icon' => 'tio-invisible',
            'color' => 'text-info',
            'bg' => 'bg-info bg-opacity-10',
        ],
        [
            'value' => $overview['total_likes'] ?? 0,
            'label' => translate('messages.Total_Likes'),
            'icon' => 'tio-heart-outlined',
            'color' => 'text-danger',
            'bg' => 'bg-danger bg-opacity-10',
        ],
        [
            'value' => $overview['total_store_visits'] ?? 0,
            'label' => translate('messages.Total_Store_visit'),
            'icon' => 'tio-home-vs-2-outlined',
            'color' => 'text-success',
            'bg' => 'bg-success bg-opacity-10',
        ],
        [
            'value' => \App\CentralLogics\Helpers::format_currency($overview['total_sale_amount'] ?? 0),
            'label' => translate('messages.Total_Sale_Amount'),
            'tooltip' => translate('messages.Total_order_value_from_Reel_Order_Now_purchases'),
            'icon' => null,
            'icon_image' => dynamicAsset('assets/admin/img/reels/total-sale-amount-icon.svg'),
            'color' => 'text-purple',
            'bg' => 'bg-purple-dark bg-opacity-10',
        ],
        [
            'value' => $overview['total_sale'] ?? 0,
            'label' => translate('messages.Total_Sale'),
            'tooltip' => translate('messages.Total_orders_placed_using_the_Reel_Order_Now_button'),
            'icon' => null,
            'icon_image' => dynamicAsset('assets/admin/img/reels/total-sale-icon.svg'),
            'color' => 'text-warning',
            'bg' => 'bg-warning bg-opacity-10',
        ],
    ];
@endphp

@section('content')
    <div class="content container-fluid">
        <div class="page-header pb-0">
            <div class="d-flex flex-wrap justify-content-between align-items-start">
                <h1 class="mb-0">{{ translate('messages.Reels_List') }}</h1>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <div class="card card-body">
                    <div class="row g-3">
                        @foreach($overviewCards as $card)
                            <div class="col-sm-6 col-lg-4">
                                <a href="#" class="h-100">
                                    <div class="p-3 rounded-10 border overflow-wrap-anywhere h-100 d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                        <div>
                                            <h3 class="fs-20 mb-1">{{ $card['value'] }}</h3>
                                            <p class="text-muted mb-0 d-flex align-items-center gap-1">
                                                {{ $card['label'] }}
                                                @if(!empty($card['tooltip']))
                                                    <span class="form-label-secondary text-dark" data-toggle="tooltip" data-placement="top" data-title="{{ $card['tooltip'] }}">
                                                        <i class="tio-info-outined"></i>
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="{{ $card['bg'] }} p-2 rounded-10 lh--1 w-max-content mb-3">
                                            @if (!empty($card['icon_image']))
                                                <img width="20" src="{{ $card['icon_image'] }}" alt="">
                                            @else
                                                <i class="{{ $card['icon'] }} {{ $card['color'] }} fs-20"></i>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-12" id="reels-list-section">
                <h2 class="fs-20 d-flex gap-2 align-items-center text-capitalize lh-1 mb-20">
                    <span class="page-header-icon">
                        <i class="tio-filter-list fs-24"></i>
                    </span>
                    <span>{{ translate('messages.Reels_List') }}</span>
                    <span class="badge badge-soft-dark">{{ $reels->total() }}</span>
                </h2>
                <div class="card">
                    <div class="card-header py-1 border-0">
                        <div class="search--button-wrapper justify-content-end flex-wrap">
                            <h4 class="flex-grow-1 mb-0">{{ translate('messages.Reels_List') }}</h4>
                            <form class="search-form min--260" action="{{ route('vendor.reels.index') }}" method="GET">
                                @foreach(request()->except(['search', 'page']) as $key => $value)
                                    @if(is_array($value))
                                        @foreach($value as $item)
                                            <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                        @endforeach
                                    @else
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endif
                                @endforeach
                                <div class="input-group input--group">
                                    <input id="datatableSearch_" type="search" name="search" class="form-control h--40px"
                                        placeholder="{{ translate('messages.Search_here') }}" value="{{ request('search') }}" aria-label="Search">
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                                </div>
                            </form>

                            <div class="hs-unfold mr-2">
                                <a class="btn btn-outline-primary btn-white filter-button-show h--40px js-hs-unfold-invoker px-4 w-max-content" href="javascript:;">
                                    <i class="tio-filter-list mr-1"></i> {{ translate('messages.Filter') }}
                                    <span class="badge badge-success badge-pill ml-1" id="filter_count">{{ $filterCount }}</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    @include('reelsmodule::admin.reels.partials._table', [
                        'reels' => $reels,
                        'storeInformationLabel' => $storeInformationLabel,
                        'deletedStoreLabel' => $deletedStoreLabel,
                        'showRestaurantLink' => false,
                        'showStoreColumn' => false,
                        'reelStatusRoute' => 'vendor.reels.status',
                        'reelEditRoute' => 'vendor.reels.edit',
                        'reelDestroyRoute' => 'vendor.reels.destroy',
                    ])

                    @if($reels->count())
                        <hr>
                    @endif
                    <div class="page-area">
                        {!! $reels->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="datatableFilterSidebar" class="hs-unfold-content_ sidebar sidebar-bordered sidebar-box-shadow initial-hidden w-500px max-w-100">
        <div class="card card-lg sidebar-card sidebar-footer-fixed">
            <div class="card-header">
                <h4 class="card-header-title">{{ translate('messages.Filter') }}</h4>
                <a class="js-hs-unfold-invoker_ p-1 rounded-circle btn-sm btn-ghost-dark ml-2 filter-button-hide" href="javascript:;">
                    <i class="tio-clear tio-lg"></i>
                </a>
            </div>

            <form class="card-body sidebar-body sidebar-scrollbar" action="{{ route('vendor.reels.index') }}" method="GET" id="reels_filter_form">
                <input type="hidden" name="search" value="{{ request('search') }}">

                <div class="bg-light rounded p-xxl-20 p-3 mb-3 mb-sm-4">
                    <label class="form-label">{{ translate('messages.Reel_Status') }}</label>
                    <div class="py-2 px-3 rounded min-h-45px bg-white">
                        <div class="row g-1">
                            @php
                                $selectedStatuses = (array) request('reel_status', ['all']);
                            @endphp
                            @foreach (['all', 'upcoming', 'live', 'expired', 'deactivated'] as $status)
                                <div class="col-sm-6">
                                    <label class="custom_checkbox d-flex align-items-center gap-1 flex-grow-1 m-0 pt-2px">
                                        <input type="checkbox" value="{{ $status }}" name="reel_status[]" {{ in_array($status, $selectedStatuses) ? 'checked' : '' }}>
                                        <span class="label-text">{{ translate('messages.' . $status) }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="bg-light rounded p-xxl-20 p-3 mb-3 mb-sm-4">
                    <label class="form-label">{{ translate('messages.Sort_By') }}</label>
                    <div class="py-2 px-3 rounded min-h-45px bg-white">
                        <div class="row g-1">
                            @foreach ([
                                'all' => 'all',
                                'most_viewed' => 'most_viewed',
                                'most_liked' => 'most_liked',
                                'most_store_visit' => 'most_store_visit',
                            ] as $sortValue => $sortLabel)
                                <div class="col-sm-6">
                                    <label class="custom-control custom-radio mb-0">
                                        <input type="radio" class="custom-control-input" value="{{ $sortValue }}" name="sort_by" {{ request('sort_by', 'all') === $sortValue ? 'checked' : '' }}>
                                        <span class="custom-control-label fs-12 text-capitalize">{{ translate('messages.' . $sortLabel) }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="bg-light rounded p-xxl-20 p-3 mb-3 mb-sm-4">
                    <div class="d-flex flex-column gap-3 gap-sm-4">
                        <div>
                            <label class="form-label">{{ translate('messages.Reel_Upload_Date') }}</label>
                            <select name="filter_date" id="filter_date" class="form-control custom-select date-type-select">
                                <option value="all_time" {{ request('filter_date', 'all_time') === 'all_time' ? 'selected' : '' }}>{{ translate('messages.All_Time') }}</option>
                                <option value="this_week" {{ request('filter_date') === 'this_week' ? 'selected' : '' }}>{{ translate('messages.This_Week') }}</option>
                                <option value="this_month" {{ request('filter_date') === 'this_month' ? 'selected' : '' }}>{{ translate('messages.This_Month') }}</option>
                                <option value="custom" {{ request('filter_date') === 'custom' ? 'selected' : '' }}>{{ translate('messages.Custom') }}</option>
                            </select>
                        </div>
                        <div id="custom_date_wrapper" class="{{ request('filter_date') === 'custom' ? '' : 'd-none' }}">
                            <div class="mb-3">
                                <label class="form-label">{{ translate('messages.Start_Date') }}</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}" max="{{ request('end_date') }}">
                            </div>
                            <div>
                                <label class="form-label">{{ translate('messages.End_Date') }}</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}" min="{{ request('start_date') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer sidebar-footer">
                    <div class="row gx-2">
                        <div class="col">
                            <a href="{{ route('vendor.reels.index') }}" class="btn btn-block btn--reset" id="reset">{{ translate('messages.Reset') }}</a>
                        </div>
                        <div class="col">
                            <button type="submit" class="btn btn-block btn-primary">{{ translate('messages.Filter') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script_2')
    @include('reelsmodule::shared._reels-scripts')
@endpush

@include('reelsmodule::shared._reels-details-offcanvas', [
    'reels' => $reels,
    'deletedStoreLabel' => $deletedStoreLabel,
])
