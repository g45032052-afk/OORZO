@extends('layouts.admin.app')

@section('title', $restaurant->name . "'s" . translate('messages.settings'))

@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{ dynamicAsset('assets/admin/css/croppie.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <h1 class="page-header-title text-break">
                    <i class="tio-museum"></i> <span>{{ $restaurant->name }} @include('partials._verified_restaurant_badge', ['restaurant' => $restaurant])</span>
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
                @include('admin-views.vendor.view.partials._header', ['restaurant' => $restaurant])

                <!-- End Nav -->
            </div>
            <!-- End Nav Scroller -->
        </div>
        <!-- End Page Header -->
        <!-- Page Heading -->
        @if ($restaurant->discount)
            <div class="card">
                <div class="card-header py-3 flex-md-nowrap gap-3 flex-wrap">

                        <div class="">
                            <h5 class="card-title fs-18 mb-1 text-title">
                                {{ translate('messages.discount_info') }}
                            </h5>
                            <p class="m-0 fs-12">
                                {{ translate('messages.This discount is applied on all the foods in your restaurant') }}</p>
                        </div>
                        <div class="search--button-wrapper flex-sm-nowrap flex-wrap">
                            <button type="button" data-toggle="modal" data-target="#delete_modal"
                                class="hover-text-white text-sm-nowrap text-wrap btn btn--danger min-w-100px">
                                <i class="tio-delete-outlined"></i>
                                {{ translate('messages.clear discount') }}
                            </button>
                            <button type="button" class="btn min-w-100px btn--primary" data-toggle="modal"
                                data-target="#updatesettingsmodal">
                                <i class="tio-open-in-new"></i>
                                {{ $restaurant->discount ? translate('messages.Edit') : translate('messages.add_discount') }}
                            </button>
                        </div>

                </div>
                <div class="card-body">

                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <div
                                class="discount-item global-bg-box rounded-8 p-3 h-100 d-flex align-items-center gap-xxl-20 gap-3">
                                <img width="36" height="36"
                                    src="{{ dynamicAsset('assets/admin/img/percentage-badge.png') }}"
                                    alt="badge">
                                <div>
                                    <h4 class="amount fs-32 mt-0 mb-0">
                                        {{ $restaurant->discount ? round($restaurant->discount->discount) : 0 }}%</h4>
                                    <h5 class="subtitle mb-0 fs-14">{{ translate('messages.discount_amount') }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="discount-item style-2 global-bg-box rounded-8 p-3 h-100">
                                <h5 class="subtitle">{{ translate('messages.purchase_conditions') }}</h5>
                                <ul class="list-unstyled list-unstyled-py-3 text-dark mb-0">
                                    <li class="p-0 pt-1">
                                        <span class="fs-14">{{ translate('messages.max_purchase_discount') }} :</span>
                                        <strong
                                            class="fs-14 fw-500">{{ \App\CentralLogics\Helpers::format_currency($restaurant->discount ? $restaurant->discount->max_discount : 0) }}</strong>
                                    </li>
                                    <li class="p-0 pt-1">
                                        <span class="fs-14">{{ translate('messages.min_purchase_amount') }} :</span>
                                        <strong
                                            class="fs-14 fw-500">{{ \App\CentralLogics\Helpers::format_currency($restaurant->discount ? $restaurant->discount->min_purchase : 0) }}</strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="discount-item global-bg-box rounded-8 p-3 h-100">
                                <div class="d-flex mb-3 gap-2">
                                    <h5 class="subtitle fs-14 mt-0 mb-0">{{ translate('messages.duration') }}</h5>

                                    @php
                                        $now = now();
                                        $start = optional($restaurant->discount)->start_date;
                                        $end = optional($restaurant->discount)->end_date;
                                    @endphp

                                    @if ($start && $end)
                                        @if ($now->lt($start))
                                            <span
                                                class="badge badge-soft-primary rounded-pill fw-500 px-2 d-inline-block text-center fs-12">
                                                {{ translate('Upcoming') }}
                                            </span>
                                        @elseif($now->between($start, $end))
                                            <span
                                                class="badge badge-soft-warning rounded-pill fw-500 px-2 d-inline-block text-center fs-12">
                                                {{ translate('Ongoing') }}
                                            </span>
                                        @else
                                            <span
                                                class="badge badge-soft-danger rounded-pill fw-500 px-2 d-inline-block text-center fs-12">
                                                {{ translate('Expired') }}
                                            </span>
                                        @endif
                                    @endif



                                </div>
                                <ul class="list-unstyled list-unstyled-py-3 text-dark mb-0">
                                    <li class="p-0 pt-1">
                                        <span class="fs-14">{{ translate('messages.start_date') }} :</span> <strong
                                            class="fs-14 fw-500">
                                            {{ $restaurant->discount ? \App\CentralLogics\Helpers::time_date_format($restaurant->discount->start_date . $restaurant->discount->start_time) : '' }}
                                        </strong>
                                    </li>
                                    <li class="p-0 pt-1">
                                        <span class="fs-14">{{ translate('messages.end_date') }} :</span> <strong
                                            class="fs-14 fw-500">
                                            {{ $restaurant->discount ? \App\CentralLogics\Helpers::time_date_format($restaurant->discount->end_date . $restaurant->discount->end_time) : '' }}
                                        </strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card mt-3">
                <div class="card-body">
                    <label class="d-flex justify-content-center py-5 rounded px-4" for="restaurant_status">
                        <div class="text-center mx-auto py-5">
                            <img width="36" height="36"
                                src="{{ dynamicAsset('assets/admin/img/percentage-badge.png') }}" alt="badge"
                                class="mb-20">
                            <h4 class="fs-16 fw-semibold text-title mb-2">
                                {{ translate('messages.Add discount on full Restaurant') }}</h4>
                            <p class="fs-12 mb-20">
                                {{ translate('messages.Discount will be applied to all food under this restaurant') }}</p>
                            <button type="button" class="btn min-w-100px btn--primary" data-toggle="modal"
                                data-target="#updatesettingsmodal">
                                <i class="tio-add-circle"></i>
                                {{ translate('messages.add_discount') }}
                            </button>
                        </div>
                    </label>
                </div>
            </div>
        @endif


    </div>
    <!-- Modal -->
    <div class="modal fade" id="updatesettingsmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header pb-3 shadow">
                    <h4 class="modal-title m-0" id="exampleModalCenterTitle">
                        {{ $restaurant->discount ? translate('messages.update') : translate('messages.add_discount') }}</h4>
                    <button type="button" class="close w-30px h-30px w-30px h-30px secondary-cmn rounded-pill rounded-pill"
                        data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.restaurant.discount', [$restaurant['id']]) }}" method="post" >
                        @csrf
                        <div class="row gx-2">
                            <div class="col-md-4 col-6">
                                <div class="form-group">
                                    <label class="form-label font-medium text-capitalize"
                                        for="title">{{ translate('messages.discount_amount') }} (%)</label>
                                    <input type="number" min="0" max="100" step="0.01" name="discount"
                                        class="form-control" required
                                        value="{{ $restaurant->discount ? $restaurant->discount->discount : '0' }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="form-group">
                                    <label class="form-label font-medium text-capitalize"
                                        for="title">{{ translate('messages.min_purchase') }}
                                        ({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                                    <input type="number" name="min_purchase" step="0.01" min="0"
                                        max="100000" class="form-control" placeholder="100"
                                        value="{{ $restaurant->discount ? $restaurant->discount->min_purchase : '0' }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="form-group">
                                    <label class="form-label font-medium text-capitalize"
                                        for="title">{{ translate('messages.max_discount') }}
                                        ({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                                    <input type="number" min="1" max="1000000" step="0.01"
                                        name="max_discount" class="form-control"
                                        value="{{ $restaurant->discount ? $restaurant->discount->max_discount : '0' }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-6">
                                <div class="form-group">
                                    <label class="form-label font-medium text-capitalize"
                                        for="title">{{ translate('messages.start_date') }}</label>
                                    <input type="date" id="date_from" class="form-control" required name="start_date"
                                        value="{{ $restaurant->discount ? date('Y-m-d', strtotime($restaurant->discount->start_date)) : '' }}">
                                </div>
                            </div>
                            <div class="col-md-6 col-6">
                                <div class="form-group">
                                    <label class="form-label font-medium text-capitalize"
                                        for="title">{{ translate('messages.end_date') }}</label>
                                    <input type="date" id="date_to" class="form-control" required name="end_date"
                                        value="{{ $restaurant->discount ? date('Y-m-d', strtotime($restaurant->discount->end_date)) : '' }}">
                                </div>

                            </div>
                            <div class="col-md-6 col-6">
                                <div class="form-group">
                                    <label class="form-label font-medium text-capitalize"
                                        for="title">{{ translate('messages.start_time') }}</label>
                                    <input type="time" id="start_time" class="form-control" required
                                        name="start_time"
                                        value="{{ $restaurant->discount ? date('H:i', strtotime($restaurant->discount->start_time)) : '00:00' }}">
                                </div>
                            </div>
                            <div class="col-md-6 col-6">
                                <label class="form-label font-medium text-capitalize"
                                    for="title">{{ translate('messages.end_time') }}</label>
                                <input type="time" id="end_time" class="form-control" required name="end_time"
                                    value="{{ $restaurant->discount ? date('H:i', strtotime($restaurant->discount->end_time)) : '23:59' }}">
                            </div>
                        </div>
                        <div class="form-group text-right mb-0">
                            @if ($restaurant->discount)
                                <button type="reset"
                                    class="btn btn--reset mr-2 h--37px">{{ translate('messages.reset') }}</button>
                            @endif
                            <button type="submit"
                                class="btn btn--primary h--37px">{{ $restaurant->discount ? translate('messages.update') : translate('messages.add') }}</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="delete_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog max-w-500px modal-dialog-centered" role="document">
            <div class="modal-content pb-3">
                <div class="modal-header">
                    <h4 class="modal-title m-0"></h4>
                    <button type="button"
                        class="close w-30px h-30px w-30px h-30px secondary-cmn rounded-pill rounded-pill"
                        data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-2">
                        <img width="36" height="36"
                            src="{{ dynamicAsset('assets/admin/img/percentage-badge.png') }}" alt="badge"
                            class="mb-20">
                        <h4 class="fs-18 fw-semibold text-title mb-2">
                            {{ translate('messages.Clear Discount for the restaurant ?') }}</h4>
                        <p class="fs-14 mb-0">
                            {{ translate('messages.Discount will no longer be applicable to this restaurant.') }}</p>
                    </div>
                </div>

                <form action="{{ route('admin.restaurant.clear-discount', [$restaurant->id]) }}" method="post" >
                        @csrf @method('delete')
                        <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap pb-4 px-4">
                            <button type="reset"  data-dismiss="modal" class="btn btn--reset mr-2 min-w-120px">
                                {{ translate('No') }}
                            </button>
                            <button type="submit" class="btn btn--danger min-w-120px hover-text-white">
                                {{ translate('Yes') }}
                            </button>
                        </div>
                    </form>

            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        "use strict";
        $(document).on('ready', function() {

            $('#date_from').attr('min', (new Date()).toISOString().split('T')[0]);
            $('#date_to').attr('min', (new Date()).toISOString().split('T')[0]);

            $("#date_from").on("change", function() {
                $('#date_to').attr('min', $(this).val());
            });

            $("#date_to").on("change", function() {
                $('#date_from').attr('max', $(this).val());
            });
        });

    </script>
@endpush
