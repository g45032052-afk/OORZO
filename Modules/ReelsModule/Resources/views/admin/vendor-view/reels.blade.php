@extends('layouts.admin.app')

@section('title', $restaurant->name . ' - ' . translate('messages.reels'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@php
    $storeLabel = translate('messages.Restaurant');
    $storeInformationLabel = $storeLabel . ' ' . translate('messages.information');
    $deletedStoreLabel = $storeLabel . ' ' . translate('messages.deleted');
@endphp

@section('content')
    <div class="content container-fluid">

    <div class="page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <h1 class="page-header-title text-break">
                <i class="tio-museum"></i> <span>{{$restaurant->name}}</span>
            </h1>
        </div>
        <!-- Nav Scroller -->
        <div class="js-nav-scroller hs-nav-scroller-horizontal">
            <span class="hs-nav-scroller-arrow-prev initial-hidden">
                <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                    <i class="tio-chevron-left"></i>
                </a>
            </span>

            <span class="hs-nav-scroller-arrow-next initial-hidden">
                <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                    <i class="tio-chevron-right"></i>
                </a>
            </span>

            <!-- Nav -->
            @include('admin-views.vendor.view.partials._header',['restaurant'=>$restaurant])

            <!-- End Nav -->
        </div>
        <!-- End Nav Scroller -->
    </div>




        <div class="row g-3">
            <div class="col-12" id="reels-list-section">
                <div class="card">
                    <div class="card-header py-1 border-0">
                        <div class="search--button-wrapper justify-content-end flex-wrap">
                            <h4 class="flex-grow-1 mb-0">{{ translate('messages.reels_list') }}</h4>
                            <form class="search-form min--260" action="{{ route('admin.restaurant.view', ['restaurant' => $restaurant->id, 'tab' => 'reels']) }}" method="GET">
                                <input type="hidden" name="tab" value="reels">
                                @foreach(request()->except(['search', 'page', 'tab']) as $key => $value)
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
                                    <span class="badge badge-success badge-pill ml-1" id="filter_count">{{ $filterCount ?: '' }}</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    @include('reelsmodule::admin.reels.partials._table', [
                        'reels' => $reels,
                        'storeInformationLabel' => $storeInformationLabel,
                        'deletedStoreLabel' => $deletedStoreLabel,
                    ])

                    @if(count($reels) !== 0)
                        <hr>
                    @endif
                    <div class="page-area">
                        {!! $reels->links() !!}
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

                <form class="card-body sidebar-body sidebar-scrollbar" action="{{ route('admin.restaurant.view', ['restaurant' => $restaurant->id, 'tab' => 'reels']) }}" method="GET" id="reels_filter_form">
                    <input type="hidden" name="tab" value="reels">
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
                                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                </div>
                                <div>
                                    <label class="form-label">{{ translate('messages.End_Date') }}</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer sidebar-footer">
                        <div class="row gx-2">
                            <div class="col">
                                <a href="{{ route('admin.restaurant.view', ['restaurant' => $restaurant->id, 'tab' => 'reels']) }}" class="btn btn-block btn--reset" id="reset">{{ translate('messages.Reset') }}</a>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-block btn-primary">{{ translate('messages.Filter') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @include('reelsmodule::shared._reels-details-offcanvas', [
            'reels' => $reels,
            'deletedStoreLabel' => $deletedStoreLabel,
        ])
    </div>
@endsection

@push('script_2')
    @include('reelsmodule::shared._reels-scripts')
@endpush
