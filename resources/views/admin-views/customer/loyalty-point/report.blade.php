@extends('layouts.admin.app')

@section('title', translate('messages.customer_loyalty_point_report'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title text-capitalize">
                <div class="card-header-icon d-inline-flex mr-2 img">
                    <img src="{{ dynamicAsset('assets/admin/img/payment.png') }}" alt="public">
                </div>
                <span>
                    {{ translate('messages.customer_loyalty_point_report') }}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <div class="card mb-3">
            <div class="card-header text-capitalize">
                <h5 class="card-title">
                    <span class="card-header-icon">
                        <i class="tio-filter-outlined"></i>
                    </span>
                    <span>{{ translate('messages.filter_options') }}</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12 pt-3">
                        <form action="{{ route('admin.customer.loyalty-point.report') }}" id="filter_form" method="get">
                            <div class="row">
                                <div class="col-sm-6 col-12">
                                    <div class="mb-3">
                                        <input type="date" name="from" id="from_date"
                                            value="{{ request()->input('from') }}" class="form-control h--45px"
                                            title="{{ translate('messages.from_date') }}">
                                    </div>
                                </div>
                                <div class="col-sm-6 col-12">
                                    <div class="mb-3">
                                        <input type="date" name="to" id="to_date"
                                            value="{{ request()->input('to') }}" class="form-control h--45px"
                                            title="{{ translate('messages.to_date') }}">
                                    </div>
                                </div>
                                <div class="col-sm-6 col-12">
                                    <div class="mb-3">
                                        @php
                                            $transaction_status = request()->input('transaction_type');
                                        @endphp
                                        <select name="transaction_type" id="" class="form-control js-select2-custom h--45px" data-search-placeholder="{{ translate('search_transaction') }}"  data-placeholder="{{ translate('messages.select_transaction_type') }}" title="{{ translate('messages.select_transaction_type') }}">
                                            <option selected disabled value=""> {{ translate('messages.select_transaction_type') }} </option>
                                            <option  value="all"  {{ isset($transaction_status) && $transaction_status == 'all' ? 'selected' : '' }}>{{ translate('messages.all') }}</option>
                                            <option value="point_to_wallet"
                                                {{ isset($transaction_status) && $transaction_status == 'point_to_wallet' ? 'selected' : '' }}>
                                                {{ translate('messages.point_to_wallet') }}</option>
                                            <option value="order_place"
                                                {{ isset($transaction_status) && $transaction_status == 'order_place' ? 'selected' : '' }}>
                                                {{ translate('messages.order_place') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-12">
                                    <div class="mb-3">
                                        <select id='customer' name="customer_id" data-search-placeholder="{{ translate('search_customer') }}"
                                            data-placeholder="{{ translate('messages.select_customer') }}"
                                            class="js-data-example-ajax form-control h--45px"
                                            title="{{ translate('messages.select_customer') }}">
                                            @if (request()->input('customer_id') && ($customer_info = \App\Models\User::find(request()->input('customer_id'))))
                                                <option value="{{ $customer_info->id }}" selected>
                                                    {{ $customer_info->f_name . ' ' . $customer_info->l_name }}({{ $customer_info->phone }})
                                                </option>
                                            @endif

                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="btn--container justify-content-end">

                                <button type="reset" id="reset_btn"
                                    data-url="{{ route('admin.customer.loyalty-point.report') }}"
                                    class="btn btn--reset {{ request()->hasAny(['from', 'to', 'customer_id', 'transaction_type']) ? 'redirect-url' : '' }}">{{ translate('messages.reset') }}</button>

                                <button type="submit" class="btn btn--primary"><i
                                        class="tio-filter-list mr-1"></i>{{ translate('messages.filter') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
        <div class="row g-3">
            @php
                $credit = $data[0]->total_credit ?? 0;
                $debit = $data[0]->total_debit ?? 0;
                $balance = $credit - $debit;
            @endphp
            <!--Debit earned-->
            <div class="col-sm-4">
                <div class="resturant-card dashboard--card bg--2">
                    <h4 class="title">{{ translate('messages.debit') }}</h4>
                    <span class="subtitle">
                        {{ $debit }}
                    </span>
                    <img class="resturant-icon" src="{{ dynamicAsset('assets/admin/img/dashboard/3.png') }}"
                        alt="dashboard">
                </div>
            </div>
            <!--Debit earned End-->
            <!--credit earned-->
            <div class="col-sm-4">
                <div class="resturant-card dashboard--card bg--3">
                    <h4 class="title">{{ translate('messages.credit') }}</h4>
                    <span class="subtitle">
                        {{ $credit }}
                    </span>
                    <img class="resturant-icon" src="{{ dynamicAsset('assets/admin/img/dashboard/4.png') }}"
                        alt="dashboard">
                </div>
            </div>
            <!--credit earned end-->
            <!--balance earned-->
            <div class="col-sm-4">
                <div class="resturant-card dashboard--card bg--1">
                    <h4 class="title">{{ translate('messages.balance') }}</h4>
                    <span class="subtitle">
                        {{ $balance }}
                    </span>
                    <img class="resturant-icon" src="{{ dynamicAsset('assets/admin/img/dashboard/1.png') }}"
                        alt="dashboard">
                </div>
            </div>
            <!--balance earned end-->
        </div>
        <!-- End Stats -->
        <!-- Card -->
        <div class="card mt-3">
            <!-- Header -->
            <div class="card-header text-capitalize border-0">
                <h4 class="card-title">
                    <span class="card-header-icon"><i class="tio-money"></i></span>
                    <span>{{ translate('messages.transactions') }}</span>
                </h4>
                <form class="my-2 ml-auto mr-sm-2 mr-xl-4 ml-sm-auto flex-grow-1 flex-grow-sm-0">

                    <div class="input--group input-group input-group-merge input-group-flush">
                        <input id="datatableSearch_" type="search" name="search" class="form-control"
                            value="{{ request()?->search ?? null }}"
                            placeholder="{{ translate('Ex:_Search_by_TransactionId_or_Reference') }}"
                            aria-label="{{ translate('messages.search') }}" required>
                        <button type="submit" class="btn btn--secondary secondary-cmn"><i class="tio-search"></i></button>

                    </div>
                    <!-- End Search -->
                </form>
                <!-- Unfold -->
                <div class="hs-unfold mr-2">
                    <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40"
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
                            href="{{ route('admin.customer.loyalty-point.export', ['type' => 'excel', request()->getQueryString()]) }}">
                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                                src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg"
                                alt="Image Description">
                            {{ translate('messages.excel') }}
                        </a>
                        <a id="export-csv" class="dropdown-item"
                            href="{{ route('admin.customer.loyalty-point.export', ['type' => 'csv', request()->getQueryString()]) }}">
                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                                src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                alt="Image Description">
                            .{{ translate('messages.csv') }}
                        </a>
                    </div>
                </div>
                <!-- End Unfold -->
            </div>
            <!-- End Header -->

            <!-- Body -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="datatable" class="table table-thead-bordered table-align-middle card-table table-nowrap">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ translate('messages.sl') }}</th>
                                <th>{{ translate('messages.transaction_id') }}</th>
                                <th>{{ translate('messages.Customer') }}</th>
                                <th>{{ translate('messages.credit') }}</th>
                                <th>{{ translate('messages.debit') }}</th>
                                <th>{{ translate('messages.balance') }}</th>
                                <th>{{ translate('messages.transaction_type') }}</th>
                                <th>{{ translate('messages.reference') }}</th>
                                <th>{{ translate('messages.created_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $k => $wt)
                                <tr>
                                    <td>{{ $k + $transactions->firstItem() }}</td>
                                    <td>{{ $wt->reference_id }}</td>
                                    <td><a
                                            href="{{ route('admin.customer.view', ['user_id' => $wt->user_id]) }}">{{ Str::limit($wt->user ? $wt->user->f_name . ' ' . $wt->user->l_name : translate('messages.not_found'), 20, '...') }}</a>
                                    </td>
                                    <td>{{ $wt->credit }}</td>
                                    <td>{{ $wt->debit }}</td>
                                    <td>{{ $wt->balance }}</td>
                                    <td>
                                        <span
                                            class="badge badge-soft-{{ $wt->transaction_type == 'point_to_wallet' ? 'success' : 'dark' }}">
                                            {{ translate('messages.' . $wt->transaction_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if (is_numeric($wt->reference))
                                            <a href="{{ route('admin.order.details', $wt->reference) }}" target="_blank"
                                                rel="noopener noreferrer">
                                                {{ $wt->reference }}
                                            </a>
                                        @else
                                            <div class="text-title">
                                                {{ $wt->reference }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>{{ \App\CentralLogics\Helpers::time_date_format($wt->created_at) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if (!$transactions)
                        <div class="empty--data">
                            <img src="{{ dynamicAsset('assets/admin/img/empty.png') }}" alt="public">
                            <h5>
                                {{ translate('no_data_found') }}
                            </h5>
                        </div>
                    @endif
                </div>
                <!-- Pagination -->
                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div>
                            {!! $transactions->links() !!}
                        </div>
                    </div>
                </div>
                <!-- Pagination -->
            </div>
            <!-- End Body -->

        </div>

    </div>
@endsection



@push('script_2')
    <script>
        "use strict";
        $(document).on('ready', function() {
            $('.js-data-example-ajax').select2({
                ajax: {
                    url: '{{ route('admin.customer.select-list') }}',
                    data: function(params) {
                        return {
                            q: params.term,
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


        $('#from_date,#to_date').change(function() {
            let fr = $('#from_date').val();
            let to = $('#to_date').val();
            if (fr != '' && to != '') {
                if (fr > to) {
                    $('#from_date').val('');
                    $('#to_date').val('');
                    toastr.error('{{ translate('messages.invalid_date_range') }}');
                }
            }

        });

        $('#reset_btn').click(function() {
               setTimeout(function() {
                  $('#customer').val(null).trigger('change');
            }, 100);
        })

        $('#filter_form').on('submit', function (e) {
            const customer        = $('#customer').val();
            const fromDate        = $('#from_date').val();
            const toDate          = $('#to_date').val();
            const transactionType = $('[name="transaction_type"]').val();

            const isEmpty = (value) => !value || value.trim() === '';

            if (
                isEmpty(customer) &&
                isEmpty(fromDate) &&
                isEmpty(toDate) &&
                isEmpty(transactionType)
            ) {
                e.preventDefault();

                toastr.error('{{ translate("messages.please_select_a_filter_options") }}');
            }
        });
    </script>
@endpush
