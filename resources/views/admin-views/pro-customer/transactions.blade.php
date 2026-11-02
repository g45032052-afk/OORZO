@extends('layouts.admin.app')

@section('title', translate('messages.Pro_Customer_Transactions'))
@section('pro_customer_transactions', 'active')

@section('content')
@php
    $filtered = request()->hasAny(['plan_id', 'customer_id', 'plan_type', 'payment_method', 'dates']);
@endphp

<div class="content container-fluid">
    <div class="page-header mb-1">
        <div class="d-flex flex-wrap justify-content-between align-items-start">
            <h1 class="page-header-title text-capitalize">
                <span>{{ translate('messages.Transactions') }}</span>
                <span class="badge badge-soft-dark ml-2" id="itemCount">{{ $transactions->total() }}</span>
            </h1>
        </div>
    </div>

    <div class="card">
        <div class="card-header gap-2 flex-wrap pt-4 border-0">
            <div class="search--button-wrapper justify-content-start">
                <form method="get" action="{{ route('admin.pro-customer.transactions') }}">
                    @foreach (request()->except('search', 'page') as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ is_array($v) ? implode(',', $v) : $v }}">
                    @endforeach
                    <div class="input--group input-group input-group-merge input-group-flush">
                        <input id="datatableSearch_" type="search" name="search" class="form-control"
                            value="{{ request()->search ?? '' }}"
                            placeholder="{{ translate('messages.Search_Here') }}"
                            aria-label="{{ translate('messages.Search') }}">
                        <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                    </div>
                </form>
            </div>

            <div class="search--button-wrapper justify-content-end">
                <div class="d-flex flex-wrap gpa-3 justify-content-sm-end align-items-sm-center ml-0 mr-0">
                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-outline-primary dropdown-toggle min-height-40 fs-14"
                            href="javascript:;"
                            data-hs-unfold-options='{"target": "#proTxnExportDropdown","type": "css-animation"}'>
                            <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                        </a>

                        <div id="proTxnExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                            <a id="export-excel" class="dropdown-item"
                                href="{{ route('admin.pro-customer.transactions.export', ['type' => 'excel', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a id="export-csv" class="dropdown-item"
                                href="{{ route('admin.pro-customer.transactions.export', ['type' => 'csv', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                .{{ translate('messages.csv') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="hs-unfold">
                    <a class="js-hs-unfold-invoker btn min-w-100px justify-content-center font-medium btn-sm btn-outline-primary filter-show offcanvas-trigger"
                        data-target="#pro_txn_filter_offcanvas" href="javascript:">
                        <i class="tio-tune-horizontal mr-1 fs-16"></i>
                        <span class="mt-1">{{ translate('messages.Filter') }}</span>
                        @if ($filtered)<span class="filter-dot"></span>@endif
                    </a>
                </div>
            </div>
        </div>

        <div class="px-xxl-20 px-3">
            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th class="border-0 text-capitalize">{{ translate('messages.SL') }}</th>
                            <th class="border-0 text-capitalize">{{ translate('messages.Transaction_ID') }}</th>
                            <th class="border-0 text-capitalize">{{ translate('messages.Transaction_Date') }}</th>
                            <th class="table-column-pl-0 border-0 text-capitalize">{{ translate('messages.Customer_Info') }}</th>
                            <th class="border-0 text-center text-capitalize">{{ translate('messages.Plan_Name') }}</th>
                            <th class="border-0 text-center text-capitalize">{{ translate('messages.Pricing') }}</th>
                            <th class="border-0 text-center text-capitalize">{{ translate('messages.Plan_Validity') }}</th>
                            <th class="border-0 text-center text-capitalize">{{ translate('messages.Payment_By') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $k => $txn)
                            @php $user = $txn->user; @endphp
                            <tr>
                                <td>{{ $k + $transactions->firstItem() }}</td>
                                <td>
                                    <div class="min-w-120px">
                                        #{{ $txn->id }}
                                    </div>
                                </td>
                                <td>
                                    @php $txnDate = $txn->paid_at ?? $txn->created_at; @endphp
                                    <div class="min-w-120px">
                                        {{ optional($txnDate)->format('d M Y') }} <br>
                                        <span class="gray-dark fs-12">{{ optional($txnDate)->format('h:i a') }}</span>
                                    </div>
                                </td>
                                <td class="table-column-pl-0">
                                    <div class="d-flex align-items-center gap-2 min-w-280">
                                        <img class="rounded-circle aspect-1-1 object-cover" width="40"
                                            src="{{ $user ? $user->image_full_url : dynamicAsset('assets/admin/img/rectangle.png') }}"
                                            alt="img">
                                        <div>
                                            @if ($user)
                                                <a href="{{ route('admin.customer.view', $user->id) }}"
                                                    class="text-dark fw-500 text-hover-primary max-w-215px min-w-135px text-wrap line--limit-1">
                                                    {{ trim(($user->f_name ?? '') . ' ' . ($user->l_name ?? '')) ?: translate('messages.Unknown') }}
                                                </a>
                                                <div>
                                                    <a class="gray-dark fs-12 text-hover-primary max-w-215px min-w-170px text-wrap line--limit-1"
                                                        href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                                                </div>
                                            @else
                                                <span class="text-muted">{{ translate('messages.Customer_not_found') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="min-w-120px">{{ $txn->plan_name }}</div>
                                </td>
                                <td class="text-center">
                                    @if ($txn->plan_type === 'free_trial')
                                        <span class="badge badge-soft-success text-capitalize">{{ translate('messages.Free_Trial') }}</span>
                                    @else
                                        {{ \App\CentralLogics\Helpers::format_currency((float) $txn->amount) }}
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2 justify-content-center">
                                        <div>
                                            <div>{{ optional($txn->start_at)->format('d M Y h:i a') }} -</div>
                                            <div>{{ optional($txn->end_at)->format('d M Y h:i a') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="min-w-170px text-center text-capitalize">
                                        {{ $txn->payment_method ? str_replace('_', ' ', $txn->payment_method) : ($txn->plan_type === 'free_trial' ? translate('messages.Free_Trial') : translate('messages.N/A')) }}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($transactions->isEmpty())
                <div class="empty--data text-center py-5 my-4">
                    <img src="{{ dynamicAsset('assets/admin/img/empty.png') }}" alt="empty"
                        style="max-width:140px;height:auto;" class="mb-3">
                    <h5 class="fs-16 mb-1 text-capitalize">{{ translate('messages.No_Transactions_Yet') }}</h5>
                    <p class="fs-12 gray-dark mb-0">{{ translate('messages.Pro_customer_transactions_will_appear_here_once_subscriptions_are_purchased') }}</p>
                </div>
            @endif

            <div class="page-area px-3 pb-3">{!! $transactions->withQueryString()->links() !!}</div>
        </div>
    </div>
</div>

{{-- ============= Filter Offcanvas ============= --}}
<div id="pro_txn_filter_offcanvas" class="custom-offcanvas d-flex flex-column justify-content-between"
    style="--offcanvas-width: 500px">
    <div>
        <form id="proTxnFilterForm" action="{{ route('admin.pro-customer.transactions') }}" method="GET">
            @if (request()->filled('search'))
                <input type="hidden" name="search" value="{{ request()->search }}">
            @endif
            <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                <div class="px-3 py-3 d-flex justify-content-between w-100">
                    <h2 class="mb-0 fs-18 text-title font-medium text-capitalize">{{ translate('messages.Transaction_Filter') }}</h2>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;</button>
                </div>
            </div>
            <div class="custom-offcanvas-body p-20">
                <div class="d-flex flex-column gap-20px">
                    <div class="global-bg-box p-xxl-20 p-3 rounded">
                        <div class="d-flex align-items-center text-dark gap-1 fs-14 mb-2">
                            <span class="text-capitalize">{{ translate('messages.Transaction_Date_Range') }}</span>
                            <span class="gray-text" data-toggle="tooltip" data-placement="top"
                                data-original-title="{{ translate('messages.Filter_transactions_by_the_date_the_payment_was_recorded') }}">
                                <i class="tio-info fs-14 text-8797AB"></i>
                            </span>
                        </div>
                        <div class="position-relative">
                            <i class="tio-calendar-month icon-absolute-on-right"></i>
                            <input type="text" class="form-control h-45 position-relative bg-transparent"
                                name="dates" value="{{ request()->dates }}"
                                placeholder="{{ translate('messages.Select_Date') }}">
                        </div>
                    </div>

                    <div class="global-bg-box p-xxl-20 p-3 rounded">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-400 text-capitalize">{{ translate('messages.Customer') }}</label>
                                <select name="customer_id" class="form-control js-ajax-customer-select"
                                    data-placeholder="{{ translate('messages.Select_Customer') }}">
                                    @if (isset($selectedCustomer) && $selectedCustomer)
                                        <option value="all">{{ translate('messages.All') }}</option>
                                        <option value="{{ $selectedCustomer->id }}" selected>
                                            {{ trim(($selectedCustomer->f_name ?? '') . ' ' . ($selectedCustomer->l_name ?? '')) }}
                                            ({{ $selectedCustomer->phone }})
                                        </option>
                                    @else
                                        <option value="all" selected>{{ translate('messages.All') }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-400 text-capitalize">{{ translate('messages.Plan') }}</label>
                                <select name="plan_id" class="form-control js-select2-custom"
                                    data-placeholder="{{ translate('messages.Select_Plan') }}">
                                    <option value="">{{ translate('messages.All') }}</option>
                                    @foreach ($plans as $plan)
                                        <option value="{{ $plan->id }}" {{ (string) request()->plan_id === (string) $plan->id ? 'selected' : '' }}>
                                            {{ $plan->plan_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-400 text-capitalize">{{ translate('messages.Payment_Status') }}</label>
                                <select name="plan_type" class="form-control js-select2-custom"
                                    data-placeholder="{{ translate('messages.Select_Status') }}">
                                    <option value="">{{ translate('messages.All') }}</option>
                                    <option value="paid" {{ request()->plan_type === 'paid' ? 'selected' : '' }}>{{ translate('messages.Paid') }}</option>
                                    <option value="free_trial" {{ request()->plan_type === 'free_trial' ? 'selected' : '' }}>{{ translate('messages.Free_Trial') }}</option>
                                </select>
                            </div>
                            {{-- <div class="col-md-12">
                                <label class="form-label fw-400 text-capitalize">{{ translate('messages.Payment_Method') }}</label>
                                <select name="payment_method" class="form-control js-select2-custom"
                                    data-placeholder="{{ translate('messages.Select_Payment_Method') }}">
                                    <option value="">{{ translate('messages.All') }}</option>
                                    @foreach ($paymentMethods as $method)
                                        <option value="{{ $method }}" {{ request()->payment_method === $method ? 'selected' : '' }}>
                                            {{ ucwords(str_replace('_', ' ', $method)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
    </div>
    <div class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3 position-sticky">
        <a href="{{ route('admin.pro-customer.transactions') }}"
            class="btn w-100 btn--reset offcanvas-close text-capitalize">{{ translate('messages.Reset') }}</a>
        <button type="submit" class="btn w-100 btn--primary text-capitalize">{{ translate('messages.Apply') }}</button>
        </form>
    </div>
</div>
<div id="offcanvasOverlay" class="offcanvas-overlay"></div>
@endsection

@push('script_2')
<script>
    "use strict";
    $(function () {
        // AJAX customer search (reuses the shared admin customer select-list endpoint).
        if ($.fn.select2) {
            $('.js-ajax-customer-select').select2({
                dropdownParent: $('#pro_txn_filter_offcanvas'),
                placeholder: "{{ translate('messages.Select_Customer') }}",
                ajax: {
                    url: '{{ route('admin.customer.select-list') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                            all: true,
                            all_value: 'all',
                            page: params.page
                        };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                }
            });
        }

        if ($.fn.daterangepicker) {
            $('input[name="dates"]').daterangepicker({
                autoUpdateInput: false,
                opens: 'left',
                locale: { format: 'MM/DD/YYYY', cancelLabel: 'Clear' }
            });
            $('input[name="dates"]').on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            });
            $('input[name="dates"]').on('cancel.daterangepicker', function () {
                $(this).val('');
            });
        }
    });
</script>
@endpush
