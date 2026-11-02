@extends('layouts.admin.app')

@section('title', translate('Restaurant Tax Report'))

@section('vendor_tax_report')
    active
@endsection
@section('content')
    <div class="content container-fluid">


        <!--- Vendor Tax Report -->
        <h2 class="mb-20">{{ translate('Restaurant Tax Report') }}</h3>
            <div class="card p-20 mb-20">
                <form action="" method="get">
                    <div class="row g-lg-4 g-3 align-items-end">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label">{{ translate('Date Range') }}</label>
                            <div class="position-relative">
                                @php
                                    $dataRange = Carbon\Carbon::parse($startDate)->format('m/d/Y') . ' - ' . Carbon\Carbon::parse($endDate)->format('m/d/Y');
                                @endphp
                                <i class="tio-calendar-month icon-absolute-on-right"></i>
                                <input type="text" data-title="{{ translate('Select_Date_Range') }}" name="dates" value="{{ $dataRange  ?? null }}" class="date-range-picker form-control">
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <span class="mb-2 d-block title-clr fw-normal">{{ translate('Select Restaurant') }}</span>
                            <select name="restaurant_id" data-placeholder="{{ translate('Select Restaurant') }}"
                                class="js-data-example-ajax form-control  custom-select custom-select-color border rounded w-100">
                                @if (isset($restaurant))
                                    <option value="{{ $restaurant->id }}" data-verified="{{ (int) $restaurant->verified_seller }}" selected>{{ $restaurant->name }}</option>
                                @else
                                    <option value="all" selected>{{ translate('messages.all_Restaurants') }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="d-flex justify-content-end">
                                <button type="submit"
                                    class="btn min-w-135px btn--primary">{{ translate('Filter') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card p-20 mb-20">
                <div class="row g-lg-4 g-3">
                    <div class="col-md-6 col-xl-4">
                        <div
                            class="bg--secondary rounded p-15 d-flex align-items-center justify-content-between gap-2 flex-wrap">
                            <div class="d-flex align-items-center gap-2 font-semibold title-clr">
                                <img src="{{ dynamicAsset('assets/admin/img/t-total-order.png') }}" alt="img">
                                {{ translate('Total Orders') }}
                            </div>
                            <h3 class="theme-clr fw-bold mb-0">{{ $totalOrders }}</h3>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div
                            class="bg--secondary rounded p-15 d-flex align-items-center justify-content-between gap-2 flex-wrap">
                            <div class="d-flex align-items-center gap-2 font-semibold title-clr">
                                <img src="{{ dynamicAsset('assets/admin/img/t-toal-amount.png') }}" alt="img">
                                {{ translate('Total Order Amount') }}
                            </div>
                            <h3 class="text-success fw-bold mb-0">
                                {{ \App\CentralLogics\Helpers::format_currency($totalOrderAmount) }}</h3>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div
                            class="bg--secondary rounded p-15 d-flex align-items-center justify-content-between gap-2 flex-wrap">
                            <div class="d-flex align-items-center gap-2 font-semibold title-clr">
                                <img src="{{ dynamicAsset('assets/admin/img/t-tax-amount.png') }}" alt="img">
                                {{ translate('Total Tax Amount') }}
                            </div>
                            <h3 class="text-danger fw-bold mb-0">
                                {{ \App\CentralLogics\Helpers::format_currency($totalTax) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <!--- Vendor Tax Report Here -->
            <div class="card p-20 mt-5">
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-20">
                    <h4 class="mb-0">{{ translate('All Restaurant Taxes') }}</h4>
                    <div class="search--button-wrapper justify-content-end">
                        <form class="search-form min--260">
                            <div class="input-group input--group">
                                <input id="datatableSearch_" type="search" name="search" class="form-control h--40px"
                                    placeholder="{{ translate('messages.Ex: Name') }} "
                                    value="{{ request()?->search ?? null }}"
                                    aria-label="{{ translate('messages.search') }}">
                                <button type="submit" class="btn btn--secondary secondary-cmn"><i class="tio-search"></i></button>
                            </div>
                        </form>
                        @if (request()->input('search'))
                            <button type="reset" class="btn btn--primary ml-2 location-reload-to-base"
                                data-url="{{ url()->full() }}">{{ translate('messages.reset') }}</button>
                        @endif
                        <!-- Datatable Info -->
                        <div id="datatableCounterInfo" class="mr-2 mb-2 mb-sm-0 initial-hidden">
                            <div class="d-flex align-items-center">
                                <span class="font-size-sm mr-3">
                                    <span id="datatableCounter">0</span>
                                    {{ translate('messages.selected') }}
                                </span>
                            </div>
                        </div>
                        <div class="hs-unfold mr-2">
                            <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle h--40px" href="javascript:;"
                                data-hs-unfold-options='{
                            "target": "#usersExportDropdown", "type": "css-animation" }'>
                                <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                            </a>
                            <div id="usersExportDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                                <a id="export-excel" class="dropdown-item" href="{{ route('admin.report.vendorWiseTaxExport', ['export_type' => 'excel', request()->getQueryString()]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg"
                                        alt="Image Description">
                                    {{ translate('messages.excel') }}
                                </a>
                                <a id="export-csv" class="dropdown-item" href="{{ route('admin.report.vendorWiseTaxExport', ['export_type' => 'csv', request()->getQueryString()]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                        alt="Image Description">
                                    .{{ translate('messages.csv') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Table -->
                <div class="table-responsive datatable-custom">
                    <table id="datatable"
                        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table fz--14px">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{ translate('sl') }}</th>
                                <th class="border-0">{{ translate('Restaurant Info') }}</th>
                                <th class="border-0">{{ translate('Total Order') }}</th>
                                <th class="border-0">{{ translate('Total Order Amount') }}</th>
                                <th class="border-0">{{ translate('Tax Amount') }}</th>
                                <th class="border-0 text-center">{{ translate('Action') }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($restaurants as $key => $restaurant)
                                <tr>
                                    <td>
                                        {{ $key + $restaurants->firstItem() }}
                                    </td>
                                    <td>
                                        <span class="fz-14 title-clr">
                                        @php($restaurantModel = \App\Models\Restaurant::find($restaurant->restaurant_id))
                                        <a href="{{route('admin.restaurant.view', $restaurant->restaurant_id)}}" target="_blank" rel="noopener noreferrer">
                                            {{ $restaurant->restaurant_name }}
                                            @include('partials._verified_restaurant_badge', ['restaurant' => $restaurantModel])
                                        </a>

                                            <span  class="fz-11 d-block"> <a href="tel:{{ $restaurant->restaurant_phone }}"> {{ $restaurant->restaurant_phone }}</a></span>
                                        </span>
                                    </td>
                                    <td>
                                        {{ $restaurant->total_orders }}
                                    </td>
                                    <td>
                                        {{ \App\CentralLogics\Helpers::format_currency($restaurant->total_order_amount) }}
                                    </td>

                                    <td>
                                        @php($sum_tax_amount=collect($restaurant->tax_data)->sum('total_tax_amount'))
                                        <div class="d-flex flex-column gap-1">
                                            @if ($restaurant->restaurant_total_tax_amount - $sum_tax_amount > 0 )
                                            <div class="d-flex fz-14 gap-3 align-items-center title-clr">
                                                {{ translate('Total Tax:') }} <span>
                                                    {{ \App\CentralLogics\Helpers::format_currency($restaurant->restaurant_total_tax_amount - $sum_tax_amount) }}</span>
                                            </div>
                                            @endif
                                            @if ($sum_tax_amount > 0 )
                                            <div class="d-flex fz-14 gap-3 align-items-center title-clr">
                                                {{ translate('Sum of Taxes:') }} <span>
                                                    {{ \App\CentralLogics\Helpers::format_currency($sum_tax_amount) }}</span>
                                            </div>
                                                @foreach ($restaurant->tax_data as $tax)
                                                <div class="d-flex fz-11 gap-3 align-items-center">
                                                    {{ $tax['tax_name'] }}:
                                                    <span>{{ \App\CentralLogics\Helpers::format_currency($tax['total_tax_amount']) }}
                                                    </span>
                                                </div>
                                            @endforeach

                                            @endif
                                        </div>
                                    </td>


                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a class="btn btn-sm btn--primary action-btn btn-outline-primary"
                                                href="{{ route('admin.report.vendorTax', ['id' => $restaurant->restaurant_id , 'dates' => $dateRange]) }}">
                                                <i class="tio-invisible"></i>
                                            </a>
                                            <a class="btn btn-sm action-btn success-border btn-outline-varify text-success"
                                                href="{{ route('admin.report.vendorTaxExport', ['export_type' => 'excel', 'id' => $restaurant->restaurant_id ,request()->getQueryString()]) }}">
                                                <i class="tio-arrow-large-downward"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
                <!-- End Table -->
                @if (count($restaurants) !== 0)
                    <hr>
                @endif
                <div class="page-area">
                    {!! $restaurants->withQueryString()->links() !!}
                </div>
                @if (count($restaurants) === 0)
                    <div class="empty--data">
                        <img src="{{ dynamicAsset('assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                        <h5>
                            {{ translate('no_data_found') }}
                        </h5>
                    </div>
                @endif
            </div>
            <!--- Vendor Tax Details Page -->
    </div>

@endsection

@push('script_2')
    <script>
        "use strict";

        $(document).on('ready', function() {
            $('.js-data-example-ajax').select2({
                ajax: {
                    url: '{{ url('/') }}/admin/restaurant/get-restaurants',
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            all: true,

                            page: params.page
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    __port: function(params, success, failure) {
                        let $request = $.ajax(params);

                        $request.then(success);
                        $request.fail(failure);

                        return $request;
                    }
                }
            });
        });
    </script>
@endpush
