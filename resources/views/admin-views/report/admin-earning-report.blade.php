@extends('layouts.admin.app')
@section('title', translate('messages.Admin_Earning_Report'))

@push('css_or_js')
    <style>
        #pre--loader,
        .pre--loader {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }
    </style>
@endpush

@section('admin_earning_report')
    active
@endsection

@section('content')

    <style>
        .report-chart-frame {
            display: flex;
            align-items: stretch;
            gap: 12px;
        }

        .report-chart-y-axis {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            color: #4b5563;
            font-size: 12px;
            font-weight: 600;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            text-align: center;
            flex-shrink: 0;
        }

        .report-chart-body {
            flex: 1;
            min-width: 0;
        }

        .report-chart-x-axis {
            margin-top: 10px;
            text-align: center;
            color: #4b5563;
            font-size: 12px;
            font-weight: 600;
        }

        #earning-trend-chart,
        #monthly-earning-expense-graph {
            overflow: visible;
        }

        .report-scroll-list {
            flex: 1;
            min-height: 360px;
            max-height: 360px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .report-equal-height-card {
            display: flex;
            flex-direction: column;
            min-height: 440px;
        }

        .report-donut-chart-wrap {
            position: relative;
            max-width: 430px;
            margin: 0 auto;
        }

        .report-donut-chart {
            min-height: 430px;
        }

        .report-donut-chart-center {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            text-align: center;
        }

        .report-donut-chart-center-content {
            width: 180px;
            max-width: 58%;
            line-height: 1.15;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transform: translateY(0);
        }

        .report-donut-chart-center-label {
            display: block;
            margin-bottom: 4px;
            color: #6b7280;
            font-size: 15px;
            font-weight: 500;
            text-align: center;
        }

        .report-donut-chart-center-value {
            display: block;
            color: #1f2937;
            font-size: 20px;
            font-weight: 700;
            text-align: center;
        }
    </style>

    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header pb-0">
            <div>
                <h1 class="page-header-title text-capitalize">
                    {{translate('messages.Admin_Earning_Report') }}
                </h1>
                <p>
                    {{translate('messages.Comprehensive_financial_overview_and_analytics')}}
                </p>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="card card-body mb-20">
            <h3 class="mb-20">{{ translate('messages.Filter_Data') }}</h3>
            <form action="">
                <div class="__bg-F8F9FC-card">
                    <div class="row g-3 date-filter-wrapper">
                        <div class="col-lg-4">
                            <label for="" class="input-label text-capitalize">
                                {{ translate('messages.Date_Range') }}
                            </label>
                            <select name="filter" id="filter" class="form-control custom-select date-type-select">
                                <option value="all" selected>{{ translate('messages.All_Time') }}</option>
                                {{-- <option {{ request()->filter == 'this_week' ? 'selected' : '' }} value="this_week">{{
                                    translate('messages.This_Week') }}</option> --}}
                                <option {{ request()->filter == 'this_month' ? 'selected' : '' }} value="this_month">
                                    {{ translate('messages.This_Month') }}</option>
                                <option {{ request()->filter == 'this_year' ? 'selected' : '' }} value="this_year">
                                    {{ translate('messages.This_Year') }}</option>
                                <option {{ request()->filter == 'custom' ? 'selected' : '' }} value="custom">
                                    {{ translate('messages.Custom_Range') }}</option>
                            </select>
                        </div>
                        <div class="col-lg-4 custom-date-div d--none">
                            <label for="" class="input-label text-capitalize">
                                {{ translate('messages.Start_Date') }} <span class="text-danger">*</span>
                            </label>
                            <input type="date" id="start_date" name="from" value="{{ request()->from }}"
                                class="form-control">
                        </div>
                        <div class="col-lg-4 custom-date-div d--none">
                            <label for="" class="input-label text-capitalize">
                                {{ translate('messages.End_Date') }} <span class="text-danger">*</span>
                            </label>
                            <input type="date" id="end_date" name="to" value="{{ request()->to }}"
                                class="form-control">
                        </div>
                    </div>
                </div>

                <div class="btn--container mt-4 justify-content-end">
                    <button id="resetbtn" type="reset" data-url="{{ route('admin.report.admin-earning-report') }}"
                        class="btn btn--reset {{ request()->has('filter') ? 'redirect-url' : ''}} ">{{ translate('messages.reset') }}</button>
                    <button type="submit" class="btn btn--primary">{{ translate('messages.filter') }}</button>
                </div>
            </form>
        </div>

        <div class="card card-body mb-20">
            <div class="mb-3">
                <h3 class="mb-1">{{ translate('messages.Earnings_Summary') }}</h3>
                <p class="fs-12 mb-0">{{ translate('messages.Breakdown of Revenue Sources and Performance') }}</p>
            </div>

            <div id="admin_earning_symmary"> </div>


            <h4 class="mb-3">{{ translate('messages.Earnings_Breakdown') }}</h4>
            <div id="admin_earning_breakdown"> </div>

            <h4 class="mb-3">{{ translate('messages.Expenses_Breakdown') }}</h4>
            <div id="admin_expense_breakdown"></div>
        </div>
        <div class="row g-3">
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-header border-0 pb-0">
                        <h3 class="mb-1 text-title">{{ translate('messages.Earnings Trend') }}</h3>
                    </div>
                    <div class="card-body px-3 px-sm-4 pt-2 pb-3">
                        <div class="report-chart-frame">
                            <div class="report-chart-y-axis">{{ translate('messages.Earning_Amount') }}</div>
                            <div class="report-chart-body">
                                <div id="earning-trend-chart" class="w-100"></div>
                                <div class="report-chart-x-axis">{{ translate('messages.Time_Period') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header border-0 pb-0">
                        <h3 class="mb-1 text-title">{{ translate('messages.Earnings vs Expenses') }}</h3>
                    </div>
                    <div class="card-body px-3 px-sm-4 pt-2 pb-3">
                        <div class="report-chart-frame">
                            <div class="report-chart-y-axis">{{ translate('messages.Amount') }}</div>
                            <div class="report-chart-body">
                                <div id="monthly-earning-expense-graph" class="w-100"></div>
                                <div class="report-chart-x-axis">{{ translate('messages.Time_Period') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header border-0 pb-0">
                        <h3 class="mb-1 text-title">{{ translate('messages.Earnings by Source') }}</h3>
                    </div>
                    <div class="card-body px-3 px-sm-4 pt-2 pb-3">
                        <div class="report-donut-chart-wrap">
                            <div id="earnings-pie-chart" class="report-donut-chart"></div>
                            <div class="report-donut-chart-center">
                                <div class="report-donut-chart-center-content">
                                    <span class="report-donut-chart-center-label">{{ translate('messages.Total Earning') }}</span>
                                    <strong id="earnings-pie-total" class="report-donut-chart-center-value">
                                        {{ App\CentralLogics\Helpers::format_currency(0) }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-lg-6">
                <div id="admin_top_earning_restaurants"> </div>
            </div>

            <div class="col-lg-6">
                <div id="admin_zone_wise_earnings"> </div>
            </div>

            <div class="col-12">
                <div class="card card-body">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap border-0">
                        <div>
                            <h3 class="mb-20">{{ translate('messages.Recent_Transactions') }}</h3>
                            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                <!-- Nav -->
                                <ul class="nav nav-tabs border-0 nav--tabs nav--pills transaction-nav-tabs">
                                    <li class="nav-item">
                                        <a class="nav-link active transaction-tab" data-type="order" href="#"
                                            aria-disabled="true">{{ translate('messages.Earnings') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link transaction-tab" data-type="subscription" href="#"
                                            aria-disabled="true">{{ translate('messages.Subscription_Earnings') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link transaction-tab" data-type="pro_customer" href="#"
                                            aria-disabled="true">{{ translate('messages.Pro_Customer_Subscription') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link transaction-tab" data-type="expense" href="#"
                                            aria-disabled="true">{{ translate('messages.Expenses') }}</a>
                                    </li>
                                </ul>
                                <!-- End Nav -->
                            </div>
                        </div>
                        <div class="search--button-wrapper justify-content-end">
                            <form id="transaction-search-form">
                                <!-- Search -->
                                <div class="input--group input-group input-group-merge input-group-flush">
                                    <input id="datatableSearch_" type="search" name="search" class="form-control" value=""
                                        placeholder="{{ translate('Search By Txn ID or Order ID') }}" aria-label="Search"
                                        required>
                                    <button type="submit" class="btn btn--secondary">
                                        <i class="tio-search"></i>
                                    </button>
                                </div>
                                <!-- End Search -->
                            </form>
                            <div
                                class="d-flex flex-wrap gpa-3 justify-content-sm-end align-items-sm-center ml-0 mr-0 flex-grow-0">
                                <!-- Unfold -->
                                <div class="hs-unfold mr-2">
                                    <a class="js-hs-unfold-invoker btn btn-sm btn-outline-primary dropdown-toggle min-height-40 fs-14"
                                        href="javascript:;" data-hs-unfold-options='{
                                                "target": "#usersExportDropdown",
                                                "type": "css-animation"
                                            }'>
                                        <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                                    </a>

                                    <div id="usersExportDropdown"
                                        class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                        <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                                        <a id="export-excel" class="dropdown-item" href="javascript:;">
                                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                                                src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg"
                                                alt="Image Description">
                                            {{ translate('messages.excel') }}
                                        </a>
                                        <a id="export-csv" class="dropdown-item" href="javascript:;">
                                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                                                src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                                alt="Image Description">
                                            .{{ translate('messages.csv') }}
                                        </a>
                                    </div>
                                </div>
                                <!-- End Unfold -->
                            </div>
                        </div>
                        <!-- End Row -->
                    </div>
                    <!-- End Header -->

                    <!-- Table -->
                    <div class="table-responsive datatable-custom" id="transaction_table_container">

                    </div>
                    <!-- End Table -->
                    <!-- End Footer -->

                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{dynamicAsset('assets/admin/apexcharts/apexcharts.min.js')}}"></script>
    <script src="{{dynamicAsset('assets/admin/js/view-pages/apex-charts.js')}}"></script>
    <script>
        let earningTrendChart;
    </script>
@endpush

@push('script_2')

    <script>
        "use strict";

        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }
        document.addEventListener('DOMContentLoaded', function () {
            window.scrollTo(0, 0);
        });
        window.addEventListener('load', function () {
            window.scrollTo(0, 0);
        });

        let earningsChart = null;
        const chartAxisLabels = {
            timePeriod: "{{ translate('messages.Time_Period') }}",
            earningAmount: "{{ translate('messages.Earning_Amount') }}",
            amount: "{{ translate('messages.Amount') }}"
        };
        const reportCurrencySymbol = @json(\App\CentralLogics\Helpers::currency_symbol());
        const reportCurrencyPosition = @json(\App\CentralLogics\Helpers::get_business_settings('currency_symbol_position') ?? 'left');
        const reportCurrencyDecimals = {{ (int) config('round_up_to_digit') }};

        function formatGraphValue(value) {
            const absValue = Math.abs(Number(value) || 0);

            if (absValue >= 1000000000) {
                return (value / 1000000000).toFixed(1).replace(/\.0$/, '') + 'B';
            }

            if (absValue >= 1000000) {
                return (value / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
            }

            if (absValue >= 1000) {
                return (value / 1000).toFixed(1).replace(/\.0$/, '') + 'K';
            }

            return Math.round(value).toString();
        }

        function formatReportCurrency(value) {
            const formattedNumber = Number(value || 0).toLocaleString(undefined, {
                minimumFractionDigits: reportCurrencyDecimals,
                maximumFractionDigits: reportCurrencyDecimals
            });

            return reportCurrencyPosition === 'right'
                ? formattedNumber + ' ' + reportCurrencySymbol
                : reportCurrencySymbol + ' ' + formattedNumber;
        }

        function updateEarningsPieTotal(data) {
            const totalElement = document.getElementById('earnings-pie-total');
            if (!totalElement) {
                return;
            }

            const total = Array.isArray(data)
                ? data.reduce((sum, value) => sum + (Number(value) || 0), 0)
                : 0;

            totalElement.textContent = formatReportCurrency(total);
        }

        function loadEarningsPieChart(data = [0]) {

            const labels = [
                '{{ translate('Order Commission') }}',
                '{{ translate('Subscription Packages') }}',
                '{{ translate('Pro Customer Subscription') }}',
                '{{ translate('Additional Fees') }}',
                '{{ translate('Delivery Fee Commission') }}'
            ];

            const options = {
                chart: {
                    type: 'donut',
                    height: 430
                },
                series: data,
                labels: labels,
                colors: ['#04BB7B', '#8B5CF6', '#3B82F6', '#EC4899', '#F59E0B'],
                stroke: {
                    width: 0,
                    colors: ['#fff']
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    fontSize: '15px',
                    itemMargin: {
                        horizontal: 18,
                        vertical: 10
                    },
                    markers: {
                        width: 12,
                        height: 12,
                        radius: 12
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: '12px',
                        fontWeight: 500
                    },
                    formatter: function (val) {
                        return val.toFixed(0) + '%';
                    }
                },
                tooltip: {
                    enabled: true,
                    y: {
                        formatter: function (val, opts) {
                            const label = opts?.w?.globals?.labels?.[opts.seriesIndex] || '';
                            return label + ": " + formatReportCurrency(val);
                        }
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '60%',
                            labels: {
                                show: false
                            }
                        }
                    }
                }
            };

            const chartEl = document.querySelector("#earnings-pie-chart");
            updateEarningsPieTotal(data);

            if (chartEl) {

                if (earningsChart) {
                    earningsChart.updateSeries(data);
                } else {
                    earningsChart = new ApexCharts(chartEl, options);
                    earningsChart.render();
                }
            }
        }

        $(document).ready(function () {

            function toggleCustomDate(wrapper) {
                let selectValue = wrapper.find('.date-type-select').val();

                if (selectValue === 'custom') {
                    wrapper.find('.custom-date-div').slideDown(200);
                } else {
                    wrapper.find('.custom-date-div').slideUp(200);
                }
            }

            $(document).on('change', '.date-type-select', function () {
                let wrapper = $(this).closest('.date-filter-wrapper');
                toggleCustomDate(wrapper);
            });

            $('.date-filter-wrapper').each(function () {
                toggleCustomDate($(this));
            });

            $('#start_date').on('change', function () {
                $('#end_date').attr('min', $(this).val());
            });

            $('#end_date').on('change', function () {
                $('#start_date').attr('max', $(this).val());
            });

            // Initialize min/max on page load
            let initialStartDate = $('#start_date').val();
            let initialEndDate = $('#end_date').val();
            if (initialStartDate) {
                $('#end_date').attr('min', initialStartDate);
            }
            if (initialEndDate) {
                $('#start_date').attr('max', initialEndDate);
            }
        });


        fetch_data('admin_earning_symmary', '{{ route('admin.report.admin-earning-summary') }}');
        fetch_data('admin_earning_breakdown', '{{ route('admin.report.admin-earning-breakdown') }}');
        fetch_data('admin_expense_breakdown', '{{ route('admin.report.admin-expense-breakdown') }}');

        fetch_data('admin_zone_wise_earnings', '{{ route('admin.report.admin-zone-wise-earnings') }}');
        fetch_data('admin_top_earning_restaurants', '{{ route('admin.report.admin-top-earning-restaurants') }}');

        function fetch_data(id, url) {
            $.ajax({
                url: url,
                type: "get",
                data: {
                    filter: $('#filter').val(),
                    from: $('#start_date').val(),
                    to: $('#end_date').val(),
                },
                beforeSend: function () {
                    $('#' + id).empty();
                    $('#loading').show()
                },
                success: function (data) {
                    $("#" + id).append(data.view);

                    if (id === 'admin_earning_breakdown') {
                        const earnings = data.earnings;
                        const chartData = [
                            earnings.order_commission,
                            earnings.subscription_earning,
                            earnings.pro_customer_subscription,
                            earnings.additional_charge,
                            earnings.delivery_fee_comission,
                        ];

                        loadEarningsPieChart(chartData);
                    }
                    $('[data-toggle="tooltip"]').tooltip();
                },
                complete: function () {
                    $('#loading').hide()
                }
            })
        }



        function loadMonthlyEarningCharts() {

            $.ajax({
                url: "{{ route('admin.report.admin-monthly-earnings') }}",
                type: "GET",
                data: {
                    filter: $('#filter').val(),
                    from: $('#start_date').val(),
                    to: $('#end_date').val(),
                },

                success: function (res) {
                    console.log(res);
                    initEarningTrendStatisticsChart(
                        res.categories,
                        res.earning_series
                    );

                    initEarningVsExpensechart(
                        res.categories,
                        res.earning_series,
                        res.expense_series
                    );

                }
            });
        }

        loadMonthlyEarningCharts();

        function buildSinglePointParabola(categories, values) {
            if (!Array.isArray(categories) || !Array.isArray(values) || categories.length !== 1 || values.length !== 1) {
                return { categories, values };
            }

            const selectedLabel = categories[0];
            const selectedDate = new Date(selectedLabel);
            if (Number.isNaN(selectedDate.getTime())) {
                return { categories, values };
            }

            const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const formatLabel = (date) => {
                const day = String(date.getDate()).padStart(2, '0');
                return `${day} ${monthLabels[date.getMonth()]} ${date.getFullYear()}`;
            };

            const previousDate = new Date(selectedDate);
            previousDate.setDate(previousDate.getDate() - 1);

            const nextDate = new Date(selectedDate);
            nextDate.setDate(nextDate.getDate() + 1);

            return {
                categories: [formatLabel(previousDate), selectedLabel, formatLabel(nextDate)],
                values: [0, Number(values[0]) || 0, 0]
            };
        }

        function initEarningTrendStatisticsChart(categories, earnings) {

            const chartElement = document.getElementById('earning-trend-chart');
            if (!chartElement) return;

            if (earningTrendChart) {
                earningTrendChart.destroy();
                earningTrendChart = null;
            }

            const chartData = buildSinglePointParabola(categories, earnings);
            categories = chartData.categories;
            earnings = chartData.values;

            var maxValue = Math.max(...earnings);
            maxValue = maxValue <= 0 ? 1 : Math.ceil(maxValue * 1.1);

            const seriesData = earnings;

            const options = {
                series: [{
                    name: "{{ translate('messages.Earning') }}",
                    data: seriesData
                }],
                chart: {
                    height: 350,
                    type: 'line',
                    toolbar: { show: false }
                },
                colors: ['#019463'],
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                markers: {
                    size: 4,
                    strokeWidth: 0,
                    hover: {
                        size: 6
                    }
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: categories,
                },
                yaxis: {
                    min: 0,
                    max: maxValue,
                    tickAmount: 4,
                    labels: {
                        offsetX: -10,
                        formatter: function (val) {
                            return formatGraphValue(val);
                        }
                    }
                },
                grid: {
                    strokeDashArray: 4,
                    padding: {
                        left: 18,
                        right: 12,
                        bottom: 12
                    }
                },
                tooltip: {
                    theme: 'dark',
                    shared: false,
                    x: {
                        show: false
                    },
                    y: {
                        formatter: function (val, opts) {
                            const month = opts.w.globals.categoryLabels[opts.dataPointIndex];
                            return month + ' : ' + formatReportCurrency(val);
                        }
                    }
                }
            };

            earningTrendChart = new ApexCharts(chartElement, options);
            earningTrendChart.render();
        }

        let earningExpenseChart = null;

        function initEarningVsExpensechart(categories, earnings, expenses) {

            if (earningExpenseChart) {
                earningExpenseChart.destroy();
                earningExpenseChart = null;
            }

            let maxValue = Math.max(
                Math.max(...earnings),
                Math.max(...expenses)
            );
            maxValue = Math.ceil(maxValue * 1.1);
            let columnWidth = window.innerWidth <= 768 ? '8px' : '13px';

            let options = {
                series: [
                    { name: "Earning", data: earnings },
                    { name: "Expense", data: expenses }
                ],
                chart: {
                    type: 'bar',
                    height: 380,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: columnWidth,
                        borderRadius: 5,
                        borderRadiusApplication: 'end',
                    }
                },

                colors: ['#059669E5', '#D97706E5'],
                xaxis: {
                    categories: categories,
                },
                yaxis: {
                    min: 0,
                    max: maxValue,
                    tickAmount: 4,
                    labels: {
                        formatter: function (val) {
                            return formatGraphValue(val);
                        }
                    }
                },
                dataLabels: { enabled: false },
                grid: {
                    borderColor: '#e5e7eb',
                    padding: {
                        left: 18,
                        right: 12,
                        bottom: 12
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return formatReportCurrency(val);
                        }
                    }
                }
            };

            earningExpenseChart = new ApexCharts(
                document.querySelector("#monthly-earning-expense-graph"),
                options
            );

            earningExpenseChart.render();
        }



        $('#resetbtn').on('click', function () {
            $('.custom-date-div').hide();
            $('#start_date').removeAttr('max');
            $('#end_date').removeAttr('min');
        })


        $(document).on('click', '.collapse-next-tr', function () {
            const $trigger = $(this);
            const $currentRow = $trigger.closest('tr');
            const $targetRow = $currentRow.next('.collapsing-tr');
            const $icon = $trigger.find('i');

            $targetRow.toggleClass('d-none');

            if ($targetRow.hasClass('d-none')) {
                $icon.removeClass('tio-chevron-up').addClass('tio-chevron-down');
            } else {
                $icon.removeClass('tio-chevron-down').addClass('tio-chevron-up');
            }
        });

        let currentTransactionType = 'order';
        let currentTransactionSearch = '';

        function fetchTransactions(page = 1) {
            let filter = $('#filter').val();
            let from = $('#start_date').val();
            let to = $('#end_date').val();

            $.ajax({
                url: "{{ route('admin.report.admin-earning-transactions') }}?page=" + page,
                type: "GET",
                data: {
                    type: currentTransactionType,
                    search: currentTransactionSearch,
                    filter: filter,
                    from: from,
                    to: to
                },
                beforeSend: function () {
                    $('#transaction_table_container').empty();
                    $('#loading').show();
                },
                success: function (data) {
                    $('#transaction_table_container').html(data.view);
                    $('[data-toggle="tooltip"]').tooltip();
                },
                complete: function () {
                    $('#loading').hide();
                }
            });
        }

        $(document).on('click', '.transaction-tab', function (e) {
            e.preventDefault();
            $('.transaction-tab').removeClass('active');
            $(this).addClass('active');
            currentTransactionType = $(this).data('type');
            currentTransactionSearch = '';
            $('#datatableSearch_').val('');

            let placeholder = "{{ translate('messages.Search_by_Transaction_ID') }}";
            if (currentTransactionType === 'subscription') {
                placeholder = "{{ translate('messages.Search_by_Transaction_ID_or_Restaurant_Name') }}";
            }
            else if (currentTransactionType === 'pro_customer') {
                placeholder = "{{ translate('messages.Search_by_Transaction_ID_or_Customer_Name') }}";
            }
            else {
                placeholder = "{{ translate('messages.Search_by_Txn_ID_or_Order_ID') }}";
            }
            $('#datatableSearch_').attr('placeholder', placeholder);

            fetchTransactions();
        });

        $('#transaction-search-form').on('submit', function (e) {
            e.preventDefault();
            currentTransactionSearch = $('#datatableSearch_').val();
            fetchTransactions();
        });

        $('#datatableSearch_').on('input', function () {
            if (this.value === '' && currentTransactionSearch !== '') {
                currentTransactionSearch = '';
                fetchTransactions();
            }
        });

        // initial load
        fetchTransactions();

        // Handle pagination clicks within the transaction table container
        $(document).on('click', '#transaction_table_container .page-area .pagination a', function (e) {
            e.preventDefault();
            let url = new URL($(this).attr('href'), window.location.origin);
            let page = url.searchParams.get('page');
            fetchTransactions(page);
        });

        $(document).on('click', '#export-excel', function () {
            let filter = $('#filter').val();
            let from = $('#start_date').val();
            let to = $('#end_date').val();
            let search = $('#datatableSearch_').val();
            let url = "{{ route('admin.report.admin-earning-export') }}";
            url += "?filter=" + filter + "&from=" + from + "&to=" + to + "&type=" + currentTransactionType + "&search=" + search + "&export_type=excel";
            location.href = url;
        });

        $(document).on('click', '#export-csv', function () {
            let filter = $('#filter').val();
            let from = $('#start_date').val();
            let to = $('#end_date').val();
            let search = $('#datatableSearch_').val();
            let url = "{{ route('admin.report.admin-earning-export') }}";
            url += "?filter=" + filter + "&from=" + from + "&to=" + to + "&type=" + currentTransactionType + "&search=" + search + "&export_type=csv";
            location.href = url;
        });


    </script>
@endpush
