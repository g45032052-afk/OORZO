@php
    $delivery_man_id = $delivery_man_id ?? 'all';
    $delivery_men = $delivery_men ?? [];
@endphp

<style>
    #pre--loader,
    .pre--loader {
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

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
</style>

<div class="card card-body mb-20">
    <h3 class="mb-20">{{ translate('messages.Filter_Data') }}</h3>
    <form action="">
        <div class="__bg-F8F9FC-card">
            <div class="row g-3 date-filter-wrapper">
                <div class="col-lg-6">
                    <label for="" class="input-label text-capitalize">
                        {{ translate('messages.Select_Delivery_Man') }}
                    </label>
                    <select name="delivery_man_id" id="delivery_man_id" class="form-control js-select2-custom">
                        <option value="all" {{ $delivery_man_id == 'all' ? 'selected' : '' }}>{{ translate('messages.All_Delivery_Man') }}</option>
                        @foreach($delivery_men as $dm)
                            <option value="{{ $dm->id }}" {{ $delivery_man_id == $dm->id ? 'selected' : '' }}>{{ $dm->f_name }} {{ $dm->l_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-6">
                    <label for="" class="input-label text-capitalize">
                        {{ translate('messages.Date_Range') }}
                    </label>
                    <select name="filter" id="filter" class="form-control custom-select date-type-select">
                        <option value="all_time" {{ request('filter') == 'all_time' ? 'selected' : '' }}>{{ translate('messages.All_Time') }}</option>
                        {{-- <option value="this_week" {{ request('filter') == 'this_week' ? 'selected' : '' }}>{{ translate('messages.This_Week') }}</option> --}}
                        <option value="this_month" {{ request('filter') == 'this_month' ? 'selected' : '' }}>{{ translate('messages.This_Month') }}</option>
                        <option value="this_year" {{ request('filter') == 'this_year' ? 'selected' : '' }}>{{ translate('messages.This_Year') }}</option>
                        <option value="custom" {{ request('filter') == 'custom' ? 'selected' : '' }}>{{ translate('messages.Custom_Range') }}</option>
                    </select>
                </div>
                <div class="col-lg-6 custom-date-div d--none">
                    <label for="" class="input-label text-capitalize">
                        {{ translate('messages.Start_Date') }} <span class="text-danger">*</span> 
                    </label>
                    <input type="date" name="from" id="from" value="{{ request('from') }}" class="form-control">
                </div>
                <div class="col-lg-6 custom-date-div d--none">
                    <label for="" class="input-label text-capitalize">
                        {{ translate('messages.End_Date') }} <span class="text-danger">*</span> 
                    </label>
                    <input type="date" name="to" id="to" value="{{ request('to') }}" class="form-control">
                </div>
            </div>
        </div>
        <div class="btn--container mt-4 justify-content-end">
            <button id="resetbtn" type="reset"
                data-url="{{ $reset_url }}"
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
    <div id="deliveryman_earning_summary"></div>

    <h4 class="mb-3">{{ translate('messages.Earnings_Breakdown') }}</h4>
    <div id="deliveryman_earning_breakdown"></div>

</div>

<div class="card h-100 mb-20">
    <div class="card-header border-0 d-block pb-0">
        <h3 class="mb-1 text-title">{{ translate('messages.Delivery Man Earnings Trend') }}</h3>
        <p class="mb-1">{{ translate('messages.Revenue performance over time') }}</p>
    </div>
    <div class="card-body px-3 px-sm-4 pt-2 pb-3">
        <div class="report-chart-frame">
            <div class="report-chart-y-axis">{{ translate('messages.Earning_Amount') }}</div>
            <div class="report-chart-body">
                <div id="earning-trend-chart"></div>
                <div class="report-chart-x-axis">{{ translate('messages.Time_Period') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card card-body">
    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap border-0">
        <div>
            <h3 class="mb-20">{{ translate('messages.Recent_Transactions') }}</h3>
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                <ul class="nav nav-tabs border-0 nav--tabs nav--pills transaction-nav-tabs">
            <li class="nav-item">
                <a class="nav-link active transaction-tab" data-type="order" href="#" aria-disabled="true">{{ translate('messages.Order_Earnings') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link transaction-tab" data-type="incentive" href="#" aria-disabled="true">{{ translate('messages.Incentives') }}</a>
            </li>
        </ul>
            </div>
        </div>        
        <div class="search--button-wrapper justify-content-sm-end">
            <form id="deliveryman-transaction-search-form">
                <div class="input--group input-group input-group-merge input-group-flush">
                    <input id="datatableSearch_" type="search" name="search" class="form-control"
                        value=""
                        placeholder="{{ translate('messages.Search_by_Order_ID_or_Delivery_Man_Name') }}" aria-label="Search" required>
                    <button type="submit" class="btn btn--secondary">
                        <i class="tio-search"></i>
                    </button>
                </div>
            </form>
            <div class="d-flex flex-wrap gpa-3 justify-content-sm-end align-items-sm-center mx-0 flex-grow-0">
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
                            href="javascript:;">
                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                                src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg"
                                alt="Image Description">
                            {{ translate('messages.excel') }}
                        </a>
                        <a id="export-csv" class="dropdown-item"
                            href="javascript:;">
                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                                src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                alt="Image Description">
                            .{{ translate('messages.csv') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive datatable-custom" id="transaction_table_container">
        
    </div>
</div>

@push('script_2')
    <script src="{{dynamicAsset('assets/admin/apexcharts/apexcharts.min.js')}}"></script>
    <script src="{{dynamicAsset('assets/admin/js/view-pages/apex-charts.js')}}"></script>
    <script>
        let earningTrendChart;
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
        
        function initEarningTrendStatisticsChart(categories, seriesData) {

            const chartElement = document.getElementById('earning-trend-chart');
            if (!chartElement) return;

            if (earningTrendChart) {
                earningTrendChart.destroy();
                earningTrendChart = null;
            }

            const chartData = buildSinglePointParabola(categories, seriesData);
            categories = chartData.categories;
            seriesData = chartData.values;

            const options = {
                series: [{
                    name: '{{ translate('messages.Total_Earnings') }}',
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
                    tickAmount: 4,
                    labels: {
                        offsetX: -10,
                        formatter: function(val) {
                            return formatGraphValue(val);
                        }
                    }
                },
                grid: {
                    strokeDashArray: 4
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

        function loadEarningTrendChart() {
            $.ajax({
                url: '{{ $trend_url }}',
                type: 'get',
                data: {
                    delivery_man_id: $('#delivery_man_id').val(),
                    filter: $('#filter').val(),
                    from: $('#from').val(),
                    to: $('#to').val(),
                },
                beforeSend: function() {
                    $('#loading').show()
                },
                success: function(data) {
                    initEarningTrendStatisticsChart(data.categories, data.earning_series);
                },
                complete: function() {
                    $('#loading').hide()
                }
            });
        }

        $(document).ready(function () {
            
            fetch_all_data();

            function fetch_all_data() {
                fetch_data('deliveryman_earning_summary', '{{ $summary_url }}');
                fetch_data('deliveryman_earning_breakdown', '{{ $breakdown_url }}');
                fetch_data('deliveryman_expense_breakdown', '{{ $expense_url }}');
                loadEarningTrendChart();
                fetchTransactions();
            }

            var currentTransactionType = 'order';
            var currentTransactionSearch = '';

            function fetchTransactions(page = 1) {
                let filter = $('#filter').val();
                let from = $('#from').val();
                let to = $('#to').val();
                let delivery_man_id = $('#delivery_man_id').val();

                $.ajax({
                    url: "{{ route('admin.report.admin-deliveryman-earning-transactions') }}?page=" + page,
                    type: "GET",
                    data: {
                        type: currentTransactionType,
                        search: currentTransactionSearch,
                        filter: filter,
                        from: from,
                        to: to,
                        delivery_man_id: delivery_man_id
                    },
                    beforeSend: function() {
                        $('#transaction_table_container').empty();
                        $('#loading').show();
                    },
                    success: function(data) {
                        $('#transaction_table_container').html(data.view);
                    },
                    complete: function() {
                        $('#loading').hide();
                    }
                });
            }

            $(document).on('click', '.transaction-tab', function(e) {
                e.preventDefault();
                $('.transaction-tab').removeClass('active');
                $(this).addClass('active');
                currentTransactionType = $(this).data('type');
                currentTransactionSearch = '';
                $('#datatableSearch_').val('');

                let placeholder = "{{ translate('messages.Search_by_Order_ID_or_Delivery_Man_Name') }}";
                if (currentTransactionType === 'incentive') {
                    placeholder = "{{ translate('messages.Search_by_Transaction_ID') }}";
                }
                $('#datatableSearch_').attr('placeholder', placeholder);

                fetchTransactions();
            });

            $('#deliveryman-transaction-search-form').on('submit', function(e) {
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

            $(document).on('click', '#transaction_table_container .page-area .pagination a', function (e) {
                e.preventDefault();
                let page = $(this).attr('href').split('page=')[1];
                fetchTransactions(page);
            });

            $(document).on('click', '#export-excel', function() {
                let filter = $('#filter').val();
                let from = $('#from').val();
                let to = $('#to').val();
                let delivery_man_id = $('#delivery_man_id').val();
                let search = $('#datatableSearch_').val();
                let url = "{{ route('admin.report.admin-deliveryman-earning-export') }}";
                url += "?filter=" + filter + "&from=" + from + "&to=" + to + "&delivery_man_id=" + delivery_man_id + "&type=" + currentTransactionType + "&search=" + search + "&export_type=excel";
                location.href = url;
            });

            $(document).on('click', '#export-csv', function() {
                let filter = $('#filter').val();
                let from = $('#from').val();
                let to = $('#to').val();
                let delivery_man_id = $('#delivery_man_id').val();
                let search = $('#datatableSearch_').val();
                let url = "{{ route('admin.report.admin-deliveryman-earning-export') }}";
                url += "?filter=" + filter + "&from=" + from + "&to=" + to + "&delivery_man_id=" + delivery_man_id + "&type=" + currentTransactionType + "&search=" + search + "&export_type=csv";
                location.href = url;
            });

            function fetch_data(id, url) {
                $.ajax({
                    url: url,
                    type: "get",
                    data: { 
                        delivery_man_id: $('#delivery_man_id').val(),
                        filter: $('#filter').val(),
                        from: $('#from').val(),
                        to: $('#to').val(),
                    },
                    beforeSend: function() {
                        $('#'+id).empty();
                        $('#loading').show()
                    },
                    success: function(data) {
                        $("#"+id).append(data.view);
                        $('[data-toggle="tooltip"]').tooltip();
                    },
                    complete: function() {
                        $('#loading').hide()
                    }
                })
            }

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

            $('#from').on('change', function () {
                $('#to').attr('min', $(this).val());
            });

            $('#to').on('change', function () {
                $('#from').attr('max', $(this).val());
            });

            // Initialize min/max on page load
            let initialStartDate = $('#from').val();
            let initialEndDate = $('#to').val();
            if (initialStartDate) {
                $('#to').attr('min', initialStartDate);
            }
            if (initialEndDate) {
                $('#from').attr('max', initialEndDate);
            }
        });

        $('#resetbtn').on('click', function() {
           $('.custom-date-div').hide();
           $('#from').removeAttr('max');
           $('#to').removeAttr('min');
        })

    </script>
@endpush
