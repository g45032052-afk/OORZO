@extends('layouts.admin.app')

@section('title', translate('Customer_list'))

@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .loader-overlay {
        position: relative;
    }

    .loader-overlay.loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.7);
        background-image: url("{{ dynamicAsset('assets/admin/img/loader.gif') }}");
        background-repeat: no-repeat;
        background-position: center;
        background-size: 40px;
        z-index: 10;
        border-radius: 0.375rem;
    }
    .min-h-100 {
        min-height: 100px;
    }
    .min-h-300 {
        min-height: 300px;
    }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm">
                <h1 class="page-header-title gap-1 flex-wrap">
                    {{ translate('messages.Customer Overview Report') }}
                </h1>
            </div>
        </div>
    </div>
    <div class="mb-2">
        <div class="row g-2">
            <div class="col-lg-6 loader-overlay loading" id="overview-counts-container">
                <div class="row g-2">
                    <div class="col-sm-6">
                        <div class="h-100 customer__card d-flex align-items-center justify-content-center">
                            <div class="card-body d-flex flex-column gap-2" style="min-height: 140px">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="h-100 customer__card d-flex align-items-center justify-content-center">
                            <div class="card-body d-flex flex-column gap-2" style="min-height: 140px"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 loader-overlay loading" id="order-statistics-container">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 140px">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <div class="row g-2">
            <div class="col-lg-6 col-xl-7 col-xxl-8 loader-overlay loading" id="onboarding-statistics-container">
                <div class="card h-100">
                    <div class="card-body" style="min-height: 300px">
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-xl-5 col-xxl-4 loader-overlay loading" id="top-customers-container">
                <div class="card h-100">
                    <div class="card-body" style="min-height: 300px">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Card -->
    <div class="card">
        <!-- Header -->
        <div class="card-header gap-2 flex-wrap pt-4 border-0">
            <h3 class="m-0">
                {{ translate('messages.customer_list') }} <span class="badge badge-soft-dark ml-2"
                    id="count">{{ $customers->total() }}</span>
            </h3>
            <div class="search--button-wrapper justify-content-end">
                <form>
                    <!-- Search -->
                    <div class="input--group input-group input-group-merge input-group-flush">
                        <input id="datatableSearch_" type="search" name="search" class="form-control"
                            value="{{ request()?->search ?? null }}"
                            placeholder="{{ translate('Ex:_Search_by_name') }}" aria-label="Search" required>
                        <button type="submit" class="btn btn--secondary">
                            <i class="tio-search"></i>
                        </button>
                    </div>
                    <!-- End Search -->
                </form>
                <div class="d-flex flex-wrap gpa-3 justify-content-sm-end align-items-sm-center ml-0 mr-0">
                    <!-- Unfold -->
                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-outline-primary dropdown-toggle min-height-40 fs-14"
                            href="javascript:;"
                            data-hs-unfold-options='{
                                "target": "#usersExportDropdown",
                                "type": "css-animation"
                            }'>
                            <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                        </a>

                        <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                            <a id="export-excel" class="dropdown-item"
                                href="{{ route('admin.customer.overview.export', ['type' => 'excel', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a id="export-csv" class="dropdown-item"
                                href="{{ route('admin.customer.overview.export', ['type' => 'csv', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                .{{ translate('messages.csv') }}
                            </a>
                        </div>
                    </div>
                    <!-- End Unfold -->
                </div>
                 <div class="hs-unfold">
                    @php
                    $filtered = request()->hasAny(['filter', 'order_wise', 'show_limit', 'from_date']); @endphp
                    <a class="js-hs-unfold-invoker btn min-w-100px justify-content-center font-medium btn-sm btn-outline-primary filter-show offcanvas-trigger"
                        data-target="#customer_list_offcanvas" href="javascript:">
                        <i class="tio-tune-horizontal mr-1 fs-16"></i> <span
                            class="mt-1">{{ translate('Filter') }}</span>
                        @if ($filtered)
                            <span class="filter-dot"></span>
                        @endif
                    </a>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- End Header -->

        <!-- Table -->
        <div class="table-responsive datatable-custom">
            <table id="datatable"
                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                data-hs-datatables-options='{
                "columnDefs": [{
                    "targets": [0],
                    "orderable": false
                }],
                "order": [],
                "info": {
                "totalQty": "#datatableWithPaginationInfoTotalQty"
                },
                "search": "#datatableSearch",
                "entries": "#datatableEntries",
                "pageLength": 25,
                "isResponsive": false,
                "isShowPaging": false,
                "paging":false
            }'>
                <thead class="thead-light">
                    <tr>
                        <th class="border-0">
                            {{ translate('sl') }}
                        </th>
                        <th class="table-column-pl-0 border-0">{{ translate('messages.customer_info') }}</th>
                        <th class="border-0">{{ translate('messages.Joining_date') }}</th>
                        <th class="border-0">{{ translate('messages.total_order') }}</th>
                        <th class="border-0">{{ translate('messages.total_spent') }}</th>
                        <th class="border-0 text-center">{{ translate('messages.AOV') }}</th>
                        <th class="border-0 text-center">{{ translate('messages.Last Purchase') }}</th>
                        <th class="border-0 text-center">{{ translate('messages.Most Used Payment Method') }}</th>
                        <th class="border-0">{{ translate('messages.actions') }}</th>
                    </tr>
                </thead>
                @php
                    $count = 0;
                @endphp
                <tbody id="set-rows">
                    @foreach ($customers as $key => $customer)
                        <tr class="">
                            <td class="">
                                {{ (request()->input('show_limit') ? $count++ : $key) + $customers->firstItem() }}
                            </td>
                            <td class="table-column-pl-0">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="position-relative">
                                        <img class="rounded-circle aspect-1-1 object-cover" width="40"
                                            data-onerror-image="{{ dynamicAsset('assets/admin/img/160x160/img1.jpg') }}"
                                            src="{{ $customer->image_full_url }}" alt="Image Description">
                                        @if ($customer->pro_status)
                                            @include('partials._pro_customer_badge')
                                        @endif
                                    </div>

                                    <div>
                                        <a href="{{ route('admin.customer.view', [$customer['id']]) }}"
                                            class="text-dark fw-500 text-hover-primary max-w-215px min-w-135px text-wrap line--limit-1">
                                            {{  $customer['f_name']?  $customer['f_name'] . ' ' . $customer['l_name']  : translate('Incomplete_profile') }}
                                        </a>
                                        <div>
                                            <div>
                                                <a href="mailto:{{ $customer['email'] }}" class="gray-dark fs-12 text-hover-primary">
                                                    {{ $customer['email'] }}
                                                </a>
                                            </div>
                                            <div>
                                                <a href="tel:{{ $customer['phone'] }}" class="gray-dark fs-12 text-hover-primary">
                                                    {{ $customer['phone'] }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <label class="badge">
                                    {{ \App\CentralLogics\Helpers::date_format($customer->created_at) }}
                                </label>
                                <br>
                                <label class="badge">
                                    {{ \App\CentralLogics\Helpers::time_format($customer->created_at) }}
                                </label>
                            </td>
                            <td>
                                <label class="badge">
                                    {{ $customer->orders_count }}
                                </label>
                            </td>
                            <td>
                                <label class="badge">
                                    {{ \App\CentralLogics\Helpers::format_currency($customer->total_order_amount) }}
                                </label>
                            </td>
                            <td>
                                <label class="badge">
                                    {{ $customer->orders_count > 0 ? \App\CentralLogics\Helpers::format_currency(($customer->total_order_amount / $customer->orders_count)) : \App\CentralLogics\Helpers::format_currency(0) }}
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="badge">
                                    @if($customer->days_since_last_order === null)
                                        <span class="fs-14 text-muted fst-italic d-block">{{ translate('Never ordered') }}</span>
                                    @elseif($customer->days_since_last_order === 0)
                                        <span class="fs-14 text-dark fw-medium d-block">{{ translate('Today') }}</span>
                                    @else
                                        <span class="fs-14 text-dark fw-medium d-block">
                                            {{ $customer->days_since_last_order }}
                                            {{ $customer->days_since_last_order == 1 ? translate('day ago') : translate('days ago') }}
                                        </span>
                                    @endif
                                </label>
                            </td>
                            <td class="text-center text-capitalize">
                                <label class="badge">
                                    @if($customer->most_used_payment_method)
                                        {{ str_replace('_', ' ', $customer->most_used_payment_method) }}
                                    @else
                                        <span class="text-muted">{{ translate('messages.N/A')}}</span>
                                    @endif
                                </label>
                            </td>
                            <td>
                                <a class="btn action-btn btn-primary btn-outline-primary"
                                    href="{{ route('admin.customer.view', [$customer['id']]) }}"
                                    title="{{ translate('messages.view_customer') }}"><i
                                        class="tio-visible-outlined"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if (count($customers) === 0)
            <div class="empty--data">
                <img src="{{ dynamicAsset('assets/admin/img/empty.png') }}" alt="public">
                <h5>
                    {{ translate('no_data_found') }}
                </h5>
            </div>
        @endif
        <!-- End Table -->
        <div class="page-area px-4 pb-3">
            <div class="d-flex align-items-center justify-content-end">
                <div>
                    {!! $customers->withQueryString()->links() !!}
                </div>
            </div>
        </div>
        <!-- End Footer -->

    </div>
    <!-- End Card -->
</div>



<!-- Filter Offcanvas -->
<div id="customer_list_offcanvas" class="custom-offcanvas d-flex flex-column justify-content-between"
    style="--offcanvas-width: 500px">
        <div>
            <form id="filterForm" action="{{ route('admin.customer.overview.report') }}" method="GET">
            <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                <div class="px-3 py-3 d-flex justify-content-between w-100">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h2 class="mb-0 fs-18 text-title font-medium">{{ translate('Filter - Customer List') }}</h2>

                    </div>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;
                    </button>
                </div>
            </div>
            <div class="custom-offcanvas-body p-20">
                <div class="d-flex flex-column gap-20px">
                    <div class="global-bg-box p-xxl-20 p-3 rounded">
                        <span class="fs-14 d-block mb-2 text-title">{{ translate('Customer Joining Date Range') }}</span>
                        <div class="bg-white rounded p-xxl-3 p-2 d-flex flex-column gap-1">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-400">{{ translate('Start Date') }}</label>
                                    <div class="position-relative">
                                        <input type="date" name="from_date" class="form-control" id="date_from"
                                            value="{{ request()->input('from_date') ?? null }}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-400">{{ translate('End Date') }}</label>
                                    <div class="position-relative">
                                        <input type="date" name="to_date" class="form-control" id="date_to"
                                            value="{{ request()->input('to_date') ?? null }}">
                                    </div>
                                </div>
                                <span id="date_error" style="color:red"></span>
                            </div>
                        </div>
                    </div>
                    <div class="global-bg-box p-xxl-20 p-3 rounded">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-400">{{ translate('Customer status') }}</label>
                                <select name="filter" data-placeholder="{{ translate('messages.Select_Status') }}"
                                    class="form-control js-select2-custom ">
                                    <option value="" selected disabled>
                                        {{ translate('messages.Select_Status') }}
                                    </option>
                                    <option {{ request()->input('filter') == 'all' ? 'selected' : '' }} value="all">
                                        {{ translate('messages.All_Customers') }}</option>
                                    <option {{ request()->input('filter') == 'active' ? 'selected' : '' }}
                                        value="active">
                                        {{ translate('messages.Active_Customers') }}</option>
                                    <option {{ request()->input('filter') == 'blocked' ? 'selected' : '' }}
                                        value="blocked">
                                        {{ translate('messages.Inactive_Customers') }}</option>
                                </select>
                            </div>
                            <div class="col-md-12 text-capitalize">
                                <label class="form-label fw-400">{{ translate('Sort By') }}</label>
                                <select name="order_wise"
                                    data-placeholder="{{ translate('messages.Select Customer Sorting Order') }}"
                                    class="form-control js-select2-custom">
                                    <option value="" selected disabled>
                                        {{ translate('messages.Select Customer Sorting Order') }} </option>
                                    <option class="text-capitalize" {{ request()->input('order_wise') == 'top' ? 'selected' : '' }}
                                        value="top">
                                        {{ translate('Most Ordered') }}</option>
                                    <option class="text-capitalize" {{ request()->input('order_wise') == 'order_amount' ? 'selected' : '' }}
                                        value="order_amount">{{ translate('Most Spent') }}</option>
                                    <option class="text-capitalize" {{ request()->input('order_wise') == 'oldest' ? 'selected' : '' }}
                                        value="oldest">
                                        {{ translate('Oldest') }}</option>
                                    <option class="text-capitalize" {{ request()->input('order_wise') == 'latest' ? 'selected' : '' }}
                                        value="latest">
                                        {{ translate('messages.Default') }} ({{ translate('latest') }}) </option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-400">{{ translate('Choose First') }}</label>
                                <input type="number" min="1" name="show_limit" class="form-control"
                                    value="{{ request()->input('show_limit') }}"
                                    placeholder="{{ translate('Ex : 100') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div  class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3 position-sticky">
            <a href="{{ route('admin.customer.overview.report') }}"
                class="btn w-100 btn--reset offcanvas-close">{{ translate('Reset') }}</a>
            <button type="submit" id="apply_filter" class="btn w-100 btn--primary">{{ translate('Apply') }}</button>
        </form>
        </div>
</div>
<div id="offcanvasOverlay" class="offcanvas-overlay"></div>
<!-- Filter Offcanvas End -->

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

<script src="{{dynamicAsset('assets/admin/apexcharts/apexcharts.min.js')}}"></script>
<script src="{{dynamicAsset('assets/admin/js/view-pages/apex-charts.js')}}"></script>

<script>
    // AJAX Loading Functions - Using jQuery
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    function applyDataColors(container) {
        container = container || $(document);
        container.find("[data-bg-color]").each(function () {
            let bg = $(this).attr("data-bg-color");
            if (bg) $(this).css("background-color", bg);
        });

        container.find("[data-text-color]").each(function () {
            let color = $(this).attr("data-text-color");
            if (color) $(this).css("color", color);
        });
    }

    function loadOverviewCounts() {
        var $container = $('#overview-counts-container');
        if (!$container.length) return;
        $container.addClass('loading');

        $.ajax({
            url: "{{ route('admin.customer.overview-counts-partial') }}",
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(html) {
                $container.html(html);
                if (window.initOverviewCountsChart) {
                    initOverviewCountsChart();
                }
                applyDataColors($container);

                $('.tooltip--custom [data-toggle="tooltip"]').tooltip({
                    html: true,
                    container: 'body',
                    template: '<div class="tooltip tooltip-custom" role="tooltip">' +
                        '<div class="arrow"></div>' +
                        '<div class="tooltip-inner"></div>' +
                        '</div>'
                });
            },
            error: function(xhr, status, error) {
                $container.html('<div class="alert alert-danger">Failed to load overview counts</div>');
                console.error('Load overview counts error:', error);
            },
            complete: function() {
                $container.removeClass('loading');
            }
        });
    }

    function loadOrderStatistics() {
        var $container = $('#order-statistics-container');
        if (!$container.length) return;
        $container.addClass('loading');

        $.ajax({
            url: "{{ route('admin.customer.order-statistics-partial') }}",
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(html) {
                $container.html(html);
                // Re-initialize chart after AJAX load
                setTimeout(function() {
                    var $chartDiv = $container.find('#order-statistics-chart');
                    if ($chartDiv.length) {
                        var posOrders = parseInt($chartDiv.data('pos-orders')) || 0;
                        var nonPosOrders = parseInt($chartDiv.data('non-pos-orders')) || 0;
                        initOrderStatisticsChart({
                            posOrders: posOrders,
                            nonPosOrders: nonPosOrders
                        });
                    }
                }, 100);
            },
            error: function(xhr, status, error) {
                $container.html('<div class="alert alert-danger">Failed to load order statistics</div>');
                console.error('Load order statistics error:', error);
            },
            complete: function() {
                $container.removeClass('loading');
            }
        });
    }

    let onboardingStatisticsChart = null;

    function loadOnboardingStatistics(filter = 'yearly') {
        var $container = $('#onboarding-statistics-container');
        if (!$container.length) return;
        $container.addClass('loading');

        $.ajax({
            url: "{{ route('admin.customer.onboarding-statistics-partial') }}",
            type: 'GET',
            data: { filter: filter },
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function (html) {
                $container.html(html);

                // Re-init chart after DOM update
                setTimeout(function () {
                    var $chartDiv = $container.find('#onboarding-statistics-chart');
                    if ($chartDiv.length) {
                        try {
                            var chartData = JSON.parse($chartDiv.attr('data-chart-data') || '[]');
                            initOnboardingStatisticsChart(chartData);
                        } catch (e) {
                            console.warn('Could not parse chart data:', e);
                            initOnboardingStatisticsChart([]);
                        }
                    }
                }, 100);
            },
            error: function (xhr, status, error) {
                $container.html('<div class="alert alert-danger">Failed to load onboarding statistics</div>');
                console.error('Load onboarding statistics error:', error);
            },
            complete: function() {
                $container.removeClass('loading');
            }
        });
    }


    function loadTopCustomers() {
        var $container = $('#top-customers-container');
        if (!$container.length) return;
        $container.addClass('loading');

        $.ajax({
            url: "{{ route('admin.customer.top-customers-partial') }}",
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(html) {
                $container.html(html);
            },
            error: function(xhr, status, error) {
                $container.html('<div class="alert alert-danger">Failed to load top customers</div>');
                console.error('Load top customers error:', error);
            },
            complete: function() {
                $container.removeClass('loading');
            }
        });
    }

    // Chart Initialization Functions
    function initOrderStatisticsChart(data) {
        const chartElement = document.getElementById('order-statistics-chart');
        if (!chartElement) return;

        const posOrders = data?.posOrders ?? 0;
        const nonPosOrders = data?.nonPosOrders ?? 0;

        const options = {
            series: [posOrders, nonPosOrders],
            labels: ['POS', 'Non-POS'],
            chart: {
                type: 'donut',
                height: 315
            },
            colors: ['#3C76F1', '#04BB7B'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Orders',
                                fontSize: '16px',
                                fontWeight: 600,
                                color: '#333',
                                formatter: function (w) {
                                    return (posOrders + nonPosOrders).toLocaleString();
                                }
                            }
                        }
                    }
                }
            },
            legend: {
                position: 'bottom',
                fontSize: '14px',
                labels: {
                    colors: '#333'
                },
                markers: {
                    radius: 12
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val.toFixed(0) + "%";
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val.toLocaleString();
                    }
                }
            }
        };

        new ApexCharts(chartElement, options).render();
    }

    function initOnboardingStatisticsChart(data) {
        const chartElement = document.getElementById('onboarding-statistics-chart');
        if (!chartElement) return;

        // 🔥 DESTROY previous chart
        if (onboardingStatisticsChart) {
            onboardingStatisticsChart.destroy();
            onboardingStatisticsChart = null;
        }

        const categories = [];
        const seriesData = [];

        if (Array.isArray(data)) {
            data.forEach(item => {
                categories.push(item.date || item.week || item.day || item.hour || '');
                seriesData.push(item.count || 0);
            });
        }

        const options = {
            series: [{
                name: 'New Customers',
                data: seriesData
            }],
            chart: {
                height: 350,
                type: 'line',
                toolbar: { show: false }
            },
            stroke: {
                width: 2,
                curve: 'smooth'
            },
            xaxis: {
                categories: categories
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    gradientToColors: ['#04BB7B'],
                    shadeIntensity: 0.1,
                    type: 'vertical',
                    opacityFrom: 0.7,
                    opacityTo: 0.1,
                    stops: [0, 100]
                }
            },
            grid: {
                strokeDashArray: 4
            },
            tooltip: {
                y: {
                    formatter: val => val + ' customers'
                }
            }
        };

        onboardingStatisticsChart = new ApexCharts(chartElement, options);
        onboardingStatisticsChart.render();
    }


    // Initialize on page load
    $(document).ready(function() {
        loadOverviewCounts();
        loadOrderStatistics();
        loadOnboardingStatistics();
        loadTopCustomers();
    });

    // Filter change handlers
    $(document).on('change', '.order-statistics-filter', function(e) {
        var filter = $(this).val();
        var $container = $('#order-statistics-container');
        $container.addClass('loading');

        $.ajax({
            url: "{{ route('admin.customer.order-statistics-partial') }}",
            type: 'GET',
            data: { filter: filter },
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(html) {
                $container.html(html);
                setTimeout(function() {
                    var $chartDiv = $container.find('#order-statistics-chart');
                    if ($chartDiv.length) {
                        var posOrders = parseInt($chartDiv.data('pos-orders')) || 0;
                        var nonPosOrders = parseInt($chartDiv.data('non-pos-orders')) || 0;
                        initOrderStatisticsChart({
                            posOrders: posOrders,
                            nonPosOrders: nonPosOrders
                        });
                    }
                }, 100);
            },
            error: function(error) {
                console.error('Filter error:', error);
            },
            complete: function() {
                $container.removeClass('loading');
            }
        });
    });

    $(document).on('change', '.onboarding-statistics-filter', function(e) {
        var filter = $(this).val();
        var $container = $('#onboarding-statistics-container');
        $container.addClass('loading');

        $.ajax({
            url: "{{ route('admin.customer.onboarding-statistics-partial') }}",
            type: 'GET',
            data: { filter: filter },
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(html) {
                $container.html(html);
                setTimeout(function() {
                    var $chartDiv = $container.find('#onboarding-statistics-chart');
                    if ($chartDiv.length) {
                        try {
                            var chartData = JSON.parse(
                                $chartDiv.attr('data-chart-data') || '[]'
                            );
                            initOnboardingStatisticsChart(chartData);
                        } catch (e) {
                            console.warn('Could not parse chart data:', e);
                            initOnboardingStatisticsChart([]);
                        }
                    }
                }, 100);
            },
            error: function(error) {
                console.error('Filter error:', error);
            },
            complete: function() {
                $container.removeClass('loading');
            }
        });
    });

    $(document).on('change', '.onboarding-statistics-filter', function () {
        loadOnboardingStatistics($(this).val());
    });

</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var options = {
            series: [23410, 6903],
            labels: ['App & Web', 'Pos'],
            chart: {
                type: 'donut',
                height: 315
            },
            colors: ['#04BB7B', '#3C76F1'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Order',
                                fontSize: '16px',
                                fontWeight: 600,
                                color: '#333',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString();
                                }
                            }
                        }
                    }
                }
            },
            legend: {
                position: 'bottom',
                fontSize: '14px',
                labels: {
                    colors: '#333'
                },
                markers: {
                    radius: 12
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val.toFixed(0) + "%";
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val.toLocaleString();
                    }
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
    });
</script>
<!-- Customer Joining Chart -->
<script>
    var options = {
          series: [{
          name: 'Sales',
          data: [4, 3, 10, 9, 29, 19, 22, 9, 12, 7, 19, 5, 13, 9, 17, 2, 7, 5]
        }],
          chart: {
          height: 390,
          type: 'line',
            toolbar: {
                show: false,
            },
        },
        forecastDataPoints: {
          count: 0
        },
        stroke: {
          width: 2,
          curve: 'smooth'
        },
        xaxis: {
          type: 'datetime',
          categories: ['1/11/2000', '2/11/2000', '3/11/2000', '4/11/2000', '5/11/2000', '6/11/2000', '7/11/2000', '8/11/2000', '9/11/2000', '10/11/2000', '11/11/2000', '12/11/2000', '1/11/2001', '2/11/2001', '3/11/2001','4/11/2001' ,'5/11/2001' ,'6/11/2001'],
          tickAmount: 10,
          labels: {
            formatter: function(value, timestamp, opts) {
              return opts.dateFormatter(new Date(timestamp), 'dd MMM')
            }
          }
        },
        fill: {
          type: 'gradient',
          gradient: {
            shade: 'dark',
            gradientToColors: [ '#019463'],
            shadeIntensity: 1,
            type: 'horizontal',
            opacityFrom: 1,
            opacityTo: 1,
            stops: [0, 0, 0, 0]
          },
        },
        grid: {
            show: true,
            xaxis: {
                lines: {
                    show: true
                }
            },
            strokeDashArray: 4
        }
    };

    var chart = new ApexCharts(document.querySelector("#chart2"), options);
    chart.render();
</script>
@endpush
