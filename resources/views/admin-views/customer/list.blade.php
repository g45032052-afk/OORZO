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

        <div>
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-sm">
                        <h1 class="page-header-title">
                            {{ translate('messages.customers') }}
                        </h1>
                    </div>
                </div>
                <!-- End Row -->
            </div>
            <!-- End Page Header -->

            @if ($builder_published)
                {{-- Host customers (0,0) vs Builder storefront customers (sub_tenant_id > 0). --}}
                <ul class="nav nav-tabs border-0 nav--tabs nav--pills mb-3">
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'main' ? 'active' : '' }}"
                            href="{{ route('admin.customer.list', ['tab' => 'main']) }}">
                            {{ translate('messages.customers') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'storefront' ? 'active' : '' }}"
                            href="{{ route('admin.customer.list', ['tab' => 'storefront']) }}">
                            {{ translate('Storefront Customers') }}
                        </a>
                    </li>
                </ul>
            @endif

            <!-- End Page Header -->
            <div class="card mb-3">
                <div class="card-body p-10px">
                    <div class="row g-2">
                        <div class="col-lg-3 col-sm-6">
                            <a class="shadow-effect-hover d-flex align-items-center gap-3 p-3 rounded card-bg1 w-100" href="#0">
                                <div class="thumb min-w-45px w-45px h-45px rounded-circle d-center bg-white">
                                    <img class="resturant-icon" width="20"
                                        src="{{ dynamicAsset('assets/admin/img/customer-g.png') }}" alt="img">
                                </div>
                                <div>
                                    <h4 class="title fs-18 font-weight-bold text-dark mb-1">{{ $total_customers }}</h4>
                                    <span
                                        class="subtitle fs-14 text-dark fw-normal">{{ translate('messages.Total Customers') }}</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <a class="shadow-effect-hover d-flex align-items-center gap-3 p-3 rounded card-bg2 w-100" href="#0">
                                <div class="thumb min-w-45px w-45px h-45px rounded-circle d-center bg-white">
                                    <img class="resturant-icon" width="20"
                                        src="{{ dynamicAsset('assets/admin/img/customer-a.png') }}" alt="img">
                                </div>
                                <div>
                                    <h4 class="title fs-18 font-weight-bold text-dark mb-1">{{ $active_customers }}</h4>
                                    <span
                                        class="subtitle fs-14 text-dark fw-normal">{{ translate('messages.Active Customer') }}</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <a class="shadow-effect-hover d-flex align-items-center gap-3 p-3 rounded card-bg3 w-100" href="#0">
                                <div class="thumb min-w-45px w-45px h-45px rounded-circle d-center bg-white">
                                    <img class="resturant-icon" width="20"
                                        src="{{ dynamicAsset('assets/admin/img/customer-inactive.png') }}"
                                        alt="img">
                                </div>
                                <div>
                                    <h4 class="title fs-18 font-weight-bold text-dark mb-1">{{ $inactive_customers }}</h4>
                                    <span
                                        class="subtitle fs-14 text-dark fw-normal">{{ translate('messages.Inactive Customer') }}</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <a class="shadow-effect-hover d-flex align-items-center gap-3 p-3 rounded bg-opacity-warning-10 w-100 position-relative"
                                href="#0">
                                <div class="thumb min-w-45px w-45px h-45px rounded-circle d-center bg-white">
                                    <img class="resturant-icon" width="20"
                                        src="{{ dynamicAsset('assets/admin/img/customer-add.png') }}"
                                        alt="img">
                                </div>
                                <div>
                                    <h4 class="title fs-18 font-weight-bold text-dark mb-1">{{ $new_customers }}</h4>
                                    <span
                                        class="subtitle fs-14 text-dark fw-normal">{{ translate('messages.New Customers ') }}</span>
                                </div>
                                <span class="text-info text-left position-absolute right-0 top-0 m-2" data-toggle="tooltip"
                                    data-placement="right"
                                    data-original-title="{{ translate('messages.Customers who joined in the last 6 months are considered new customers.') }}">
                                    <i class="tio-info fs-14"></i>
                                </span>

                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Card -->

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
                            {{-- Keep search/paging on the active tab + storefront filter. --}}
                            <input type="hidden" name="tab" value="{{ $tab }}">
                            @if ($tab === 'storefront' && $storefront_id)
                                <input type="hidden" name="storefront_id" value="{{ $storefront_id }}">
                            @endif
                            <!-- Search -->
                            <div class="input--group input-group input-group-merge input-group-flush">
                                <input id="datatableSearch_" type="search" name="search" class="form-control"
                                    value="{{ request()?->search ?? null }}"
                                    placeholder="{{ translate('Search here') }}" aria-label="Search" required>
                                <button type="submit" class="btn btn--secondary">
                                    <i class="tio-search"></i>
                                </button>
                            </div>
                            <!-- End Search -->
                        </form>
                        @if ($tab === 'storefront' && count($storefronts))
                            <form method="GET" action="{{ route('admin.customer.list') }}" class="mr-2">
                                <input type="hidden" name="tab" value="storefront">
                                <select name="storefront_id" class="form-control" onchange="this.form.submit()">
                                    <option value="">{{ translate('All Storefronts') }}</option>
                                    @foreach ($storefronts as $sf)
                                        <option value="{{ $sf->id }}"
                                            {{ (int) $storefront_id === (int) $sf->id ? 'selected' : '' }}>
                                            {{ $sf->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @endif
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
                                        href="{{ route('admin.customer.export', ['type' => 'excel', request()->getQueryString()]) }}">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg"
                                            alt="Image Description">
                                        {{ translate('messages.excel') }}
                                    </a>
                                    <a id="export-csv" class="dropdown-item"
                                        href="{{ route('admin.customer.export', ['type' => 'csv', request()->getQueryString()]) }}">
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
                <div class="px-xxl-20 px-3">
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
                                    <th class="table-column-pl-0 border-0">{{ translate('Customer Info') }}</th>
                                    @if ($tab === 'storefront')
                                        <th class="border-0">{{ translate('Storefront') }}</th>
                                    @endif
                                    <th class="border-0">{{ translate('Joining Date') }}</th>
                                    <th class="border-0">{{ translate('Total Delivered Order') }}</th>
                                    <th class="border-0">{{ translate('Total Spent') }}</th>
                                    <th class="border-0 ">{{ translate('AOV') }}</th>
                                    <th class="border-0 text-center">{{ translate('Last Purchase') }}</th>
                                    <th class="border-0">
                                        {{ translate('Active') }}/{{ translate('Inactive') }}</th>
                                    <th class="border-0">{{ translate('Actions') }}</th>
                                </tr>
                            </thead>
                            @php
                                $count = 0;
                                $storefrontNames = ($storefronts ?? collect())->pluck('name', 'id');
                            @endphp
                            <tbody id="set-rows">
                                @foreach ($customers as $key => $customer)
                                    <tr class="">
                                        <td class="">
                                            {{ (request()->input('show_limit') ? $count++ : $key) + $customers->firstItem() }}
                                        </td>
                                        <td class="table-column-pl-0">
                                            <div class="d-flex align-items-center gap-2 min-w-280">
                                                <div class="position-relative">
                                                    <img class="rounded-circle aspect-1-1 object-cover" width="40"
                                                        src="{{ $customer->image_full_url }}" alt="Image Description">
                                                        @if ($customer->pro_status)
                                                        @include('partials._pro_customer_badge')
                                                        @endif
                                                </div>

                                                <div>
                                                    <a href="{{ route('admin.customer.view', [$customer['id']]) }}"
                                                        class="text-dark fw-500 text-hover-primary max-w-215px min-w-135px text-wrap line--limit-1">
                                                        {{ $customer['f_name'] ? $customer['f_name'] . ' ' . $customer['l_name'] : translate('Incomplete_profile') }}
                                                    </a>
                                                    <div>
                                                        <div>
                                                            <a href="mailto:{{ $customer['email'] }}"
                                                                class="gray-dark fs-12 text-hover-primary max-w-215px min-w-170px text-wrap line--limit-1">
                                                                {{ $customer['email'] }}
                                                            </a>
                                                        </div>
                                                        <div>
                                                            <a href="tel:{{ $customer['phone'] }}"
                                                                class="gray-dark fs-12 text-hover-primary">
                                                                {{ $customer['phone'] }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        @if ($tab === 'storefront')
                                            <td>
                                                <span class="fw-500 text-dark text-wrap">{{ $storefrontNames[$customer->sub_tenant_id] ?? translate('Storefront') . ' #' . $customer->sub_tenant_id }}</span>
                                            </td>
                                        @endif
                                        <td class=" ">
                                            <div class="d-flex align-items-center gap-2">
                                                <div>
                                                    <div>
                                                        {{ \App\CentralLogics\Helpers::date_format($customer->created_at) }}
                                                    </div>
                                                    <div>
                                                        {{ \App\CentralLogics\Helpers::time_format($customer->created_at) }}
                                                    </div>
                                                </div>
                                            </div>
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
                                            <span  class="fs-14 text-dark text-center">{{ \App\CentralLogics\Helpers::format_currency($customer->total_order_amount > 0 && $customer->orders_count > 0 ? $customer->total_order_amount / $customer->orders_count : 0) }}</span>
                                        </td>

                                        <td>
                                            <span
                                                class="fs-14 text-dark text-center d-block">{{ $customer->lastOrder ? \App\CentralLogics\Helpers::human_time_format($customer->lastOrder->created_at) : translate('messages.N/A') }}</span>
                                        </td>

                                        <td>
                                            <label class="toggle-switch toggle-switch-sm ml-xl-4 "
                                                for="stocksCheckbox{{ $customer->id }}">
                                                <input type="checkbox"
                                                    data-url="{{ route('admin.customer.status', [$customer->id]) }}"
                                                    data-status="{{ $customer->status }}"
                                                    class="toggle-switch-input status_change_alert"
                                                    id="stocksCheckbox{{ $customer->id }}"
                                                    {{ $customer->status ? 'checked' : '' }}>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
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

    </div>


    <!-- Filter Offcanvas -->
    <div id="customer_list_offcanvas" class="custom-offcanvas d-flex flex-column justify-content-between"
        style="--offcanvas-width: 500px">
            <div>
                <form id="filterForm" action="{{ route('admin.customer.list') }}" method="GET">
                <input type="hidden" name="tab" value="{{ $tab }}">
                @if ($tab === 'storefront' && $storefront_id)
                    <input type="hidden" name="storefront_id" value="{{ $storefront_id }}">
                @endif
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
                            <span class="fs-14 d-block mb-2 text-title">{{ translate('Date Range') }}</span>
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
                                    <label class="form-label fw-400">{{ translate('Customer Status') }}</label>
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
                <a href="{{ route('admin.customer.list') }}"
                    class="btn w-100 btn--reset offcanvas-close">{{ translate('Reset') }}</a>
                <button type="submit" id="apply_filter" class="btn w-100 btn--primary">{{ translate('Apply') }}</button>
            </form>
            </div>
    </div>
    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>
    <!-- Filter Offcanvas End -->

    <!-- Confirmation modal -->
    <div class="modal fade" id="confirmation_modal_customer" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
        aria-hidden="true">
        <div class=" modal-dialog max-w-500px mx-auto modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <form class="set_status_url" method="post">
                            @method('put')
                            @csrf
                            <div class="text-center">
                                <div>
                                    <img src="{{ dynamicAsset('assets/admin/img/subscription-plan/package-status-disable.png') }}"
                                        class="mb-20">

                                    <h5 class="modal-title"></h5>
                                </div>
                                <div class="text-center pb-0">
                                    <h3 class="mb-2 fs-18"> {{ translate('Are you sure ?') }}</h3>
                                    <div>
                                        <p>{{ translate('You want to Active this customer.') }}</h3>
                                        </p>
                                    </div>
                                </div>

                                <div class="btn--container justify-content-center mt-4 pt-1">
                                    <button data-dismiss="modal"
                                        class="btn btn--reset min-w-120">{{ translate('No') }}</button>
                                    <button type="submit" id=""
                                        class="btn btn--primary min-w-120">{{ translate('Yes') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Confirmation modal -->
    <div class="modal fade" id="confirmation_modal_customer_inactive" tabindex="-1" role="dialog"
        aria-labelledby="modalLabel" aria-hidden="true">
        <div class=" modal-dialog max-w-500px mx-auto modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <form class="set_status_url" method="post">
                            @method('put')
                            @csrf
                            <div class="text-center">
                                <div>
                                    <img src="{{ dynamicAsset('assets/admin/img/subscription-plan/package-status-disable.png') }}"
                                        class="mb-20">

                                    <h5 class="modal-title"></h5>
                                </div>
                                <div class="text-center pb-0">
                                    <h3 class="mb-2 fs-18"> {{ translate('Are you sure ?') }}</h3>
                                    <div>
                                        <p>{{ translate('You want to Inactive this customer.') }}</h3>
                                        </p>
                                    </div>
                                </div>

                                <div class="btn--container justify-content-center mt-4 pt-1">
                                    <button type="submit" id=""
                                        class="btn btn--danger min-w-120">{{ translate('Yes') }}</button>
                                    <button data-dismiss="modal"
                                        class="btn btn--reset min-w-120">{{ translate('No') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('script_2')
    <script>
        "use strict";
        $('.status_change_alert').on('click', function(event) {
            event.preventDefault();
            let url = $(this).data('url');
            let status = $(this).data('status');
            $('.set_status_url').attr('action', url);
            if (status) {
                $('#confirmation_modal_customer_inactive').modal().show();
            } else {
                $('#confirmation_modal_customer').modal().show();
            }
        })



document.getElementById('filterForm').addEventListener('submit', function(e) {
    const from = document.getElementById('date_from').value.trim();
    const to   = document.getElementById('date_to').value.trim();
    const errorSpan = document.getElementById('date_error');
    errorSpan.textContent = '';

    if ((from && !to) || (!from && to)) {
        e.preventDefault();
        errorSpan.textContent = "{{ translate('Both From and To dates must be filled.') }}";
        return false;
    }

if (!from && !to && !this.querySelector('select[name="filter"]').value && !this.querySelector('select[name="order_wise"]').value && !this.querySelector('input[name="show_limit"]').value) {
    e.preventDefault();
    toastr.error("{{ translate('Please select at least one filter option.') }}");
    return false;
}



});
    </script>
@endpush
