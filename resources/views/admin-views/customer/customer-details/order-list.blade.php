@extends('layouts.admin.app')

@section('title', translate('Customer_list'))
@section('customerDetails')
    active
@endsection


@section('content')
    <div class="content container-fluid">
        <!--  -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm">
                    <h1 class="page-header-title gap-1 flex-wrap">
                        {{ translate('messages.Customer Details') }} <span class="gray-dark"> #{{ $customer->id }}</span>
                    </h1>
                </div>
            </div>
        </div>
        @include('admin-views.customer.partials._user_details_urls')
        <div class="card">
            <div class="card-header py-xl-20 flex-wrap gap-2 border-0">
                <h5 class="card-header-title">{{ translate('messages.Order_List') }}
                    <span class="badge badge-soft-secondary" id="itemCount">{{ $orders->total() }}</span>
                </h5>
                <div class="search--button-wrapper flex-xxs-nowrap">
                    <form>
                        <!-- Search -->
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input id="datatableSearch_" type="search" name="search" class="form-control"
                                value="{{ request()?->search ?? null }}" placeholder="{{ translate('Search By Order ID') }}"
                                aria-label="Search" required>
                            <button type="submit" class="btn btn--secondary">
                                <i class="tio-search"></i>
                            </button>
                        </div>
                        <!-- End Search -->
                    </form>
                    <div>
                         @php  $filtered = request()->hasAny(['from_date', 'to_date','order_status','order_type','payment_type','scheduled']); @endphp
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker btn min-w-100px justify-content-center font-medium btn-sm btn-outline-primary filter-show offcanvas-trigger"
                                data-target="#order-list_filter" href="javascript:">
                                <i class="tio-tune-horizontal mr-1 fs-16"></i>{{ translate('Filters') }} <span
                                    class="badge badge-success badge-pill ml-1" id="filter_count"></span>
                                     @if ($filtered)
                                    <span class="filter-dot"></span>
                                @endif
                            </a>
                        </div>



                    </div>

                </div>
            </div>
            <!-- Table -->
            <div class="px-xxl-20 px-3">
                <div class="table-responsive datatable-custom pt-0">
                    <table id="columnSearchDatatable"
                        class="table table-borderless table-thead-borderless table-nowrap table-align-middle card-table"
                        data-hs-datatables-options='{
                                    "order": [],
                                    "orderCellsTop": true,
                                    "paging":false
                                }'>
                        <thead class="global-bg-box">
                            <tr>
                                <th class="py-3 fs-14 text-capitalize">{{ translate('messages.sl') }}</th>
                                <th class="py-3 fs-14 text-capitalize">{{ translate('messages.Order_id') }}</th>
                                <th class="py-3 fs-14 text-capitalize">{{ translate('messages.Order_Date') }}</th>
                                <th class="py-3 fs-14 text-capitalize">{{ translate('messages.Restaurant') }}</th>
                                <th class="py-3 fs-14 text-capitalize  ">{{ translate('messages.total_amount') }}</th>
                                <th class="py-3 fs-14 text-capitalize ">{{ translate('messages.status') }}</th>
                                <th class="py-3 fs-14 text-capitalize text-center">{{ translate('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $key => $order)
                                <tr>
                                    <td>{{ $key + $orders->firstItem() }}</td>
                                    <td>
                                        <a class="text--title d-flex algin-items-center gap-1"
                                            href="{{ route('admin.order.details', ['id' => $order['id']]) }}">
                                            {{ $order['id'] }}
                                            @if ($order->is_pos == 1)
                                                <span class="text--warning font-500">({{ translate('POS') }})</span>
                                            @endif
                                            <br>
                                        </a>
                                        @if ($order->edited == 1)
                                            <span class="text-info fs-12 d-block font-500">({{ translate('Edited') }})</span>
                                        @endif
                                    </td>
                                    <td class="text-uppercase fs-12">
                                        <div>
                                            {{ \App\CentralLogics\Helpers::date_format($order->created_at) }}
                                        </div>
                                        <div>
                                            {{ \App\CentralLogics\Helpers::time_format($order->created_at) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-title  max-w-220px text-wrap line--limit-1">
                                            <a href="{{ route('admin.restaurant.view', $order->restaurant_id) }}"
                                                class="text--title" alt="view restaurant">
                                                {{ Str::limit($order->restaurant ? $order->restaurant->name : translate('messages.Restaurant_deleted!'), 20, '...') }}
                                                @include('partials._verified_restaurant_badge', ['restaurant' => $order->restaurant])
                                            </a>
                                        </div>
                                    </td>
                                    <td class="">
                                        <div class="text-title">
                                            {{ \App\CentralLogics\Helpers::format_currency($order['order_amount']) }}</div>
                                        {{-- <div class="text-success fs-12 font-medium">Paid</div> --}}

                                        @if ($order->payment_status == 'paid')
                                            <div class="text-success">
                                                {{ translate('messages.paid') }}
                                            </div>
                                        @elseif($order->payment_status == 'partially_paid')
                                            <div class="text-success">
                                                {{ translate('messages.partially_paid') }}
                                            </div>
                                        @else
                                            <div class="text-danger">
                                                {{ translate('messages.unpaid') }}
                                            </div>
                                        @endif


                                    </td>


                                    @if (isset($order->subscription) && $order->subscription->status != 'canceled')
                                        @php
                                            $order->order_status = $order->subscription_log
                                                ? $order->subscription_log->order_status
                                                : $order->order_status;
                                        @endphp
                                    @endif
                                    <td class="text-capitalize">
                                        @if ($order['order_status'] == 'pending')
                                            <span class="badge badge-soft-info mb-1">
                                                {{ translate('messages.pending') }}
                                            </span>
                                        @elseif($order['order_status'] == 'confirmed')
                                            <span class="badge badge-soft-info mb-1">
                                                {{ translate('messages.confirmed') }}
                                            </span>
                                        @elseif($order['order_status'] == 'processing')
                                            <span class="badge badge-soft-warning mb-1">
                                                {{ translate('messages.processing') }}
                                            </span>
                                        @elseif($order['order_status'] == 'picked_up')
                                            <span class="badge badge-soft-warning mb-1">
                                                {{ translate('messages.out_for_delivery') }}
                                            </span>
                                        @elseif($order['order_status'] == 'delivered')
                                            <span class="badge badge-soft-success mb-1">
                                                {{ $order?->order_type == 'dine_in' ? translate('messages.Completed') : translate('messages.delivered') }}
                                            </span>
                                        @elseif($order['order_status'] == 'failed')
                                            <span class="badge badge-soft-danger mb-1">
                                                {{ translate('messages.payment_failed') }}
                                            </span>
                                        @else
                                            <span class="badge badge-soft-danger mb-1">
                                                {{ translate(str_replace('_', ' ', $order['order_status'])) }}
                                            </span>
                                        @endif
                                        <div class="text-capitalze opacity-7">
                                            @if ($order['order_type'] == 'take_away')
                                                <span>
                                                    {{ translate('messages.take_away') }}
                                                </span>
                                            @elseif ($order['order_type'] == 'dine_in')
                                                <span>
                                                    {{ translate('Dine_in') }}
                                                </span>
                                            @else
                                                <span>
                                                    {{ translate('home_delivery') }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="ml-2 btn btn-sm btn--warning btn-outline-warning action-btn"
                                                href="{{ route('admin.order.details', ['id' => $order['id']]) }}">
                                                <i class="tio-invisible"></i>
                                            </a>

                                            <a class="ml-2 btn btn-sm btn--primary btn-outline-primary download--btn action-btn"
                                                href={{ route('admin.order.generate-invoice', [$order['id']]) }}>
                                                <i class="tio-print"></i>
                                            </a>

                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>

                    </table>

                    <!-- Pagination -->
                </div>
            </div>
            @if (count($orders) === 0)
                <div class="empty--data">
                    <img src="{{ dynamicAsset('assets/admin/img/empty.png') }}" alt="public">
                    <h5>
                        {{ translate('no_data_found') }}
                    </h5>
                </div>
            @endif
            <div class="card-footer p-0 border-0">
                <!-- Pagination -->
                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div>
                            {!! $orders->appends($_GET)->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>


    <!-- Most Order Items -->
    <div id="order-list_filter" class="custom-offcanvas d-flex flex-column justify-content-between"
        style="--offcanvas-width: 500px">
        <form id="filterForm">
        <div>
            <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                <div class="px-3 py-3 d-flex justify-content-between w-100">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h2 class="mb-0 fs-18 text-title font-medium">{{ translate('Filter') }}</h2>
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
                    <div class="global-bg-box rounded p-xl-20 p-16">
                        <h5 class="mb-10px font-regular text-color font-normal">{{ translate('Order Status') }}</h5>
                        <div class="bg-white rounded p-xl-3 p-2">
                            <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2 order-status_controller">
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" {{ is_array($order_status)  && in_array('all', $order_status) ? 'checked' : '' }} class="custom-control-input check-all" value="all" id="all"
                                                name="order_status[]">
                                            <label class="custom-control-label text-color" for="all">
                                                {{ translate('messages.All') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox"  {{ is_array($order_status)  && in_array('pending', $order_status) ? 'checked' : '' }} class="custom-control-input" value="pending" id="order-status2"
                                                name="order_status[]">
                                            <label class="custom-control-label text-color" for="order-status2">
                                                {{ translate('messages.Pending') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" {{ is_array($order_status)  && in_array('accepted', $order_status) ? 'checked' : '' }} value="accepted" id="order-status3"
                                                name="order_status[]">
                                            <label class="custom-control-label text-color" for="order-status3">
                                                {{ translate('messages.Accepted') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" {{ is_array($order_status)  && in_array('processing', $order_status) ? 'checked' : '' }} value="processing" id="order-status4"
                                                name="order_status[]">
                                            <label class="custom-control-label text-color" for="order-status4">
                                                {{ translate('messages.Processing') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" {{ is_array($order_status)  && in_array('picked_up', $order_status) ? 'checked' : '' }} value="picked_up" id="order-status5"
                                                name="order_status[]">
                                            <label class="custom-control-label text-color" for="order-status5">
                                                {{ translate('messages.On the Way') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" {{ is_array($order_status)  && in_array('delivered', $order_status) ? 'checked' : '' }} value="delivered" id="order-status6"
                                                name="order_status[]">
                                            <label class="custom-control-label text-color" for="order-status6">
                                                {{ translate('messages.Delivered') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" value="canceled" {{ is_array($order_status)  && in_array('canceled', $order_status) ? 'checked' : '' }} class="custom-control-input" id="order-status7"
                                                name="order_status[]">
                                            <label class="custom-control-label text-color" for="order-status7">
                                                {{ translate('messages.Canceled') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" {{ is_array($order_status)  && in_array('failed', $order_status) ? 'checked' : '' }} value="failed" id="order-status8"
                                                name="order_status[]">
                                            <label class="custom-control-label text-color" for="order-status8">
                                                {{ translate('messages.Payment Failed') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" {{ is_array($order_status)  && in_array('refunded', $order_status) ? 'checked' : '' }} value="refunded" id="order-status9"
                                                name="order_status[]">
                                            <label class="custom-control-label text-color" for="order-status9">
                                                {{ translate('messages.Refunded') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                    <div class="global-bg-box rounded p-xl-20 p-16">
                        <h5 class="mb-10px font-regular text-color font-normal">{{ translate('Order Type') }}</h5>
                        <div class="bg-white rounded p-xl-3 p-2">
                            <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2 order-status_controller">
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input check-all" {{ is_array($order_type)  && in_array('all', $order_type) ? 'checked' : '' }} value="all" id="order_type-all"
                                                name="order_type[]">
                                            <label class="custom-control-label text-color" for="order_type-all">
                                                {{ translate('messages.All') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" {{ is_array($order_type)  && in_array('dine_in', $order_type) ? 'checked' : '' }} class="custom-control-input" value="dine_in" id="order_type-dine_in"
                                                name="order_type[]">
                                            <label class="custom-control-label text-color" for="order_type-dine_in">
                                                {{ translate('Dine In') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input"  {{ is_array($order_type)  && in_array('take_away', $order_type) ? 'checked' : '' }} value="take_away" id="order_type-take_away"
                                                name="order_type[]">
                                            <label class="custom-control-label text-color" for="order_type-take_away">
                                                {{ translate('Take Away') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" {{ is_array($order_type)  && in_array('delivery', $order_type) ? 'checked' : '' }} value="delivery" id="order_type-delivery"
                                                name="order_type[]">
                                            <label class="custom-control-label text-color" for="order_type-delivery">
                                                {{ translate('Home Delivery') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="global-bg-box rounded p-xl-20 p-16">
                        <h5 class="mb-10px font-regular text-color font-normal">{{ translate('Payment Type') }}</h5>
                        <div class="bg-white rounded p-xl-3 p-2">
                            <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2 order-status_controller">
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" {{ is_array($payment_type)  && in_array('all', $payment_type) ? 'checked' : '' }} class="custom-control-input check-all" value="all" id="payment_type-all"
                                                name="payment_type[]">
                                            <label class="custom-control-label text-color" for="payment_type-all">
                                                {{ translate('messages.All') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" {{ is_array($payment_type)  && in_array('cash_on_delivery', $payment_type) ? 'checked' : '' }} value="cash_on_delivery" id="cash_on_delivery"
                                                name="payment_type[]">
                                            <label class="custom-control-label text-color" for="cash_on_delivery">
                                                {{ translate('messages.Cash On Delivery') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input"  {{ is_array($payment_type)  && in_array('digital_payment', $payment_type) ? 'checked' : '' }} value="digital_payment" id="digital_payment"
                                                name="payment_type[]">
                                            <label class="custom-control-label text-color" for="digital_payment">
                                                {{ translate('messages.Digital Payment') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" {{ is_array($payment_type)  && in_array('wallet', $payment_type) ? 'checked' : '' }} value="wallet" id="wallet"
                                                name="payment_type[]">
                                            <label class="custom-control-label text-color" for="wallet">
                                                {{ translate('messages.Wallet') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" {{ is_array($payment_type)  && in_array('offline_payment', $payment_type) ? 'checked' : '' }} value="offline_payment" id="offline_payment"
                                                name="payment_type[]">
                                            <label class="custom-control-label text-color" for="offline_payment">
                                                {{ translate('messages.Offline Payment') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" {{ is_array($payment_type)  && in_array('partial_payment', $payment_type) ? 'checked' : '' }} value="partial_payment" id="partial_payment"
                                                name="payment_type[]">
                                            <label class="custom-control-label text-color" for="partial_payment">
                                                {{ translate('messages.Partial Payment') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                    <div class="global-bg-box rounded p-xl-20 p-16">
                        {{-- <h5 class="mb-10px font-regular text-color font-normal">{{ translate('Payment Type') }}</h5> --}}
                        <div class="bg-white rounded p-xl-3 p-2">
                            <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2 order-status_controller">
                                <div class="col-sm-6 col-auto">
                                    <div class="form-group m-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" {{ request()?->scheduled ? 'checked' : '' }} class="custom-control-input " value="scheduled" id="scheduled"
                                                name="scheduled">
                                            <label class="custom-control-label text-color" for="scheduled">
                                                {{ translate('Scheduled') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div
            class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3 position-sticky">
          <a href="{{ route('admin.customer.order-list', $customer->id) }}"  class="btn w-100 btn--reset offcanvas-close">{{ translate('Reset') }}</a>
            <button type="submit" class="btn w-100 btn--primary">{{ translate('Apply') }}</button>
        </div>
        </form>
    </div>
    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>


@endsection

@push('script_2')
<script>
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
});
</script>
@endpush
