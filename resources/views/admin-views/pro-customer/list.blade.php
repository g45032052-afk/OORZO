@extends('layouts.admin.app')

@section('title', translate('messages.Pro_Customer_List'))
@section('pro_customer_list', 'active')

@section('content')
@php
    $activeTab = request()->input('tab', 'all');
    $filtered = request()->hasAny(['plan_id', 'subscription_status', 'renewal_status', 'dates']);
@endphp

<div class="content container-fluid">
    <div class="page-header mb-1">
        <div class="d-flex flex-wrap justify-content-between align-items-start">
            <h1 class="page-header-title text-capitalize">
                <span>{{ translate('messages.Pro_Customer') }}</span>
            </h1>
        </div>
    </div>

    {{-- ============= Stats ============= --}}
    <div class="card mb-3">
        <div class="card-body p-10px">
            <div class="row g-2">
                <div class="col-lg-3 col-sm-6">
                    <div class="shadow-effect-hover d-flex align-items-center gap-3 p-3 rounded card-bg1 w-100 position-relative">
                        <div class="thumb min-w-45px w-45px h-45px rounded-circle d-center bg-white">
                            <img class="resturant-icon" width="20" src="{{ dynamicAsset('assets/admin/img/customer-g.png') }}" alt="img">
                        </div>
                        <div>
                            <h4 class="title fs-18 font-weight-bold text-dark mb-1">{{ $stats['total'] }}</h4>
                            <span class="subtitle fs-14 text-dark fw-normal text-capitalize">{{ translate('messages.Total_Subscriber') }}</span>
                        </div>
                        <button type="button" class="btn text-info text-left position-absolute right-0 top-0 m-2 p-0"
                            data-toggle="tooltip" data-placement="top" data-html="true" data-title="
                            <div class='text-start'>
                                {{ translate('messages.Plan_Wise_Subscribers') }} <br>
                                @foreach($stats['subscriber_breakdown'] as $row)
                                    <span class='d-flex align-items-center gap-1'>
                                        {{ $row['label'] }} :
                                        <span class='fw-500'>{{ (int) $row['total'] }}</span>
                                    </span>
                                @endforeach
                            </div>">
                            <i class="tio-info fs-14"></i>
                        </button>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="shadow-effect-hover d-flex align-items-center gap-3 p-3 rounded card-bg2 w-100 position-relative">
                        <div class="thumb min-w-45px w-45px h-45px rounded-circle d-center bg-white">
                            <img class="resturant-icon" width="20" src="{{ dynamicAsset('assets/admin/img/customer-a.png') }}" alt="img">
                        </div>
                        <div>
                            <h4 class="title fs-18 font-weight-bold text-dark mb-1">{{ $stats['active'] }}</h4>
                            <span class="subtitle fs-14 text-dark fw-normal text-capitalize">{{ translate('messages.Active_Subscriber') }}</span>
                        </div>
                        <button type="button" class="btn text-info text-left position-absolute right-0 top-0 m-2 p-0"
                            data-toggle="tooltip" data-placement="top" data-html="true" data-title="
                            <div class='text-start'>
                                {{ translate('messages.Plan_Wise_Subscribers') }} <br>
                                @foreach($stats['subscriber_breakdown'] as $row)
                                    <span class='d-flex align-items-center gap-1'>
                                        {{ $row['label'] }} :
                                        <span class='fw-500'>{{ (int) $row['active'] }}</span>
                                    </span>
                                @endforeach
                            </div>">
                            <i class="tio-info fs-14"></i>
                        </button>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="shadow-effect-hover d-flex align-items-center gap-3 p-3 rounded card-bg3 w-100 position-relative">
                        <div class="thumb min-w-45px w-45px h-45px rounded-circle d-center bg-white">
                            <img class="resturant-icon" width="20" src="{{ dynamicAsset('assets/admin/img/customer-inactive.png') }}" alt="img">
                        </div>
                        <div>
                            <h4 class="title fs-18 font-weight-bold text-dark mb-1">{{ $stats['inactive'] }}</h4>
                            <span class="subtitle fs-14 text-dark fw-normal text-capitalize">{{ translate('messages.Inactive_Subscriber') }}</span>
                        </div>
                        <button type="button" class="btn text-info text-left position-absolute right-0 top-0 m-2 p-0"
                            data-toggle="tooltip" data-placement="top" data-html="true" data-title="
                            <div class='text-start'>
                                {{ translate('messages.Plan_Wise_Subscribers') }} <br>
                                @foreach($stats['subscriber_breakdown'] as $row)
                                    <span class='d-flex align-items-center gap-1'>
                                        {{ $row['label'] }} :
                                        <span class='fw-500'>{{ (int) $row['inactive'] }}</span>
                                    </span>
                                @endforeach
                            </div>">
                            <i class="tio-info fs-14"></i>
                        </button>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="shadow-effect-hover d-flex align-items-center gap-3 p-3 rounded bg-opacity-warning-10 w-100 position-relative">
                        <div class="thumb min-w-45px w-45px h-45px rounded-circle d-center bg-white">
                            <img class="resturant-icon" width="20" src="{{ dynamicAsset('assets/admin/img/customer-add.png') }}" alt="img">
                        </div>
                        <div>
                            <h4 class="title fs-18 font-weight-bold text-dark mb-1">{{ $stats['new'] }}</h4>
                            <span class="subtitle fs-14 text-dark fw-normal text-capitalize">{{ translate('messages.New_Subscriber') }}</span>
                        </div>
                        <span class="text-info text-left position-absolute right-0 top-0 m-2" data-toggle="tooltip"
                            data-placement="right"
                            data-original-title="{{ translate('messages.Customers_who_joined_in_the_last_2_months_are_considered_new_subscribers') }}">
                            <i class="tio-info fs-14"></i>
                        </span>
                    </div>
                </div>

                <div class="col-lg-6 col-sm-6">
                    <div class="bg--F6F6F6 shadow-effect-hover rounded d-flex align-items-center justify-content-between gap-2 flex-wrap px-3 py-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="thumb min-w-45px w-45px h-45px rounded-circle d-center bg-white">
                                <img class="resturant-icon" width="20" src="{{ dynamicAsset('assets/admin/img/bank-hand.png') }}" alt="img">
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <h4 class="title fs-14 fw-500 text-dark mb-0 text-capitalize">{{ translate('messages.Total_Earned') }}</h4>
                                <button type="button" class="btn text-info text-left p-0"
                                    data-toggle="tooltip" data-placement="top" data-html="true" data-title="
                                    <div class='text-start'>
                                        {{ translate('messages.Plan_Wise_Total_Earned') }} <br>
                                        @foreach($stats['earning_breakdown'] as $row)
                                            <span class='d-flex align-items-center gap-1'>
                                                {{ $row['label'] }} :
                                                <span class='fw-500'>{{ \App\CentralLogics\Helpers::format_currency((float) $row['earned']) }}</span>
                                            </span>
                                        @endforeach
                                    </div>">
                                    <i class="tio-info fs-14"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-20px">
                            <h4 class="mb-0">{{ \App\CentralLogics\Helpers::format_currency((float) $stats['total_earned']) }}</h4>
                            <img class="m-0" width="20" src="{{ dynamicAsset('assets/admin/img/arrow-trend-up.png') }}" alt="img">
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-sm-6">
                    <div class="bg--F6F6F6 shadow-effect-hover rounded d-flex align-items-center justify-content-between gap-2 flex-wrap px-3 py-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="thumb min-w-45px w-45px h-45px rounded-circle d-center bg-white">
                                <img class="resturant-icon" width="20" src="{{ dynamicAsset('assets/admin/img/days-calender.png') }}" alt="img">
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <h4 class="title fs-14 fw-500 text-dark mb-0 text-capitalize">{{ translate('messages.Earned_Last_30_Days') }}</h4>
                                <button type="button" class="btn text-info text-left p-0"
                                    data-toggle="tooltip" data-placement="top" data-html="true" data-title="
                                    <div class='text-start'>
                                        {{ translate('messages.Plan_Wise_Earned_Last_30_days') }} <br>
                                        @foreach($stats['earning_breakdown'] as $row)
                                            <span class='d-flex align-items-center gap-1'>
                                                {{ $row['label'] }} :
                                                <span class='fw-500'>{{ \App\CentralLogics\Helpers::format_currency((float) $row['earned_last_30']) }}</span>
                                            </span>
                                        @endforeach
                                    </div>">
                                    <i class="tio-info fs-14"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-20px">
                            <h4 class="mb-0">{{ \App\CentralLogics\Helpers::format_currency((float) $stats['earned_last_30']) }}</h4>
                            <img class="m-0" width="20" src="{{ dynamicAsset('assets/admin/img/arrow-trend-up.png') }}" alt="img">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============= List card ============= --}}
    <div class="card">
        <div class="card-header gap-2 flex-wrap pt-4 border-0">
            <div class="js-nav-scroller hs-nav-scroller-horizontal mb-0">
                <ul class="nav nav-tabs border-0 nav--tabs nav--pills nav--theme-version">
                    <li class="nav-item">
                        <a class="nav-link text-capitalize {{ $activeTab === 'all' ? 'active' : '' }}"
                            href="{{ route('admin.pro-customer.list', ['tab' => 'all'] + request()->except('tab', 'page')) }}">
                            {{ translate('messages.All') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-capitalize {{ $activeTab === 'active' ? 'active' : '' }}"
                            href="{{ route('admin.pro-customer.list', ['tab' => 'active'] + request()->except('tab', 'page')) }}">
                            {{ translate('messages.Active') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-capitalize {{ $activeTab === 'expired' ? 'active' : '' }}"
                            href="{{ route('admin.pro-customer.list', ['tab' => 'expired'] + request()->except('tab', 'page')) }}">
                            {{ translate('messages.Expired') }}
                        </a>
                    </li>
                </ul>
            </div>
            <div class="search--button-wrapper justify-content-end">
                <form method="get" action="{{ route('admin.pro-customer.list') }}">
                    @foreach (request()->except('search', 'page') as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ is_array($v) ? implode(',', $v) : $v }}">
                    @endforeach
                    <div class="input--group input-group input-group-merge input-group-flush">
                        <input id="datatableSearch_" type="search" name="search" class="form-control"
                            value="{{ request()->search ?? '' }}"
                            placeholder="{{ translate('messages.Search_Here') }}" aria-label="{{ translate('messages.Search') }}">
                        <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                    </div>
                </form>

                <div class="d-flex flex-wrap gpa-3 justify-content-sm-end align-items-sm-center ml-0 mr-0">
                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-outline-primary dropdown-toggle min-height-40 fs-14"
                            href="javascript:;"
                            data-hs-unfold-options='{"target": "#proSubsExportDropdown","type": "css-animation"}'>
                            <i class="tio-download-to mr-1"></i> {{ translate('messages.Export') }}
                        </a>

                        <div id="proSubsExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                            <a id="export-excel" class="dropdown-item"
                                href="{{ route('admin.pro-customer.list.export', ['type' => 'excel', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg" alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a id="export-csv" class="dropdown-item"
                                href="{{ route('admin.pro-customer.list.export', ['type' => 'csv', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg" alt="Image Description">
                                .{{ translate('messages.csv') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="hs-unfold">
                    <a class="js-hs-unfold-invoker btn min-w-100px justify-content-center font-medium btn-sm btn-outline-primary filter-show offcanvas-trigger"
                        data-target="#pro_subs_filter_offcanvas" href="javascript:">
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
                            <th class="table-column-pl-0 border-0 text-capitalize">{{ translate('messages.Customer_Info') }}</th>
                            <th class="border-0 text-capitalize">{{ translate('messages.Plan_Validity') }}</th>
                            <th class="border-0 text-capitalize">{{ translate('messages.Plan_Name') }}</th>
                            <th class="border-0 text-capitalize">{{ translate('messages.Plan_Price') }}</th>
                            <th class="border-0 text-center text-capitalize">{{ translate('messages.Subscription_Status') }}</th>
                            <th class="border-0 text-center text-capitalize">{{ translate('messages.Pro_Status') }}</th>
                            <th class="border-0 text-center text-capitalize">
                                <div class="d-flex align-items-center gap-1 justify-content-center">
                                    {{ translate('messages.Total_Orders') }}
                                    <span class="gray-text" data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ translate('messages.Orders_placed_during_this_subscription') }}">
                                        <i class="tio-info fs-14 text-8797AB"></i>
                                    </span>
                                </div>
                            </th>
                            <th class="border-0 text-center text-capitalize">{{ translate('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscriptions as $k => $sub)
                            @php
                                $user = $sub->user;
                                $statusBadgeMap = [
                                    'active' => 'badge-soft-success',
                                    'expired' => 'badge-soft-warning',
                                    'canceled' => 'badge-soft-danger',
                                ];
                                $badge = $statusBadgeMap[$sub->status] ?? 'badge-soft-secondary';
                                $orderCount = 0;
                                if ($user) {
                                    $orderCount = \App\Models\Order::where('user_id', $user->id)
                                        ->where('created_at', '>=', $sub->start_at)
                                        ->where('created_at', '<=', $sub->end_at)
                                        ->count();
                                }
                            @endphp
                            <tr>
                                <td>{{ $k + $subscriptions->firstItem() }}</td>
                                <td class="table-column-pl-0">
                                    <div class="d-flex align-items-center gap-2 min-w-280">
                                        <img class="rounded-circle aspect-1-1 object-cover" width="40"
                                            src="{{ dynamicAsset('assets/admin/img/rectangle.png') }}" alt="img">
                                        <div>
                                            @if ($user)
                                                <a href="{{ route('admin.customer.subscription-plan', $user->id) }}"
                                                    class="text-dark fw-500 text-hover-primary max-w-215px min-w-135px text-wrap line--limit-1">
                                                    {{ trim(($user->f_name ?? '') . ' ' . ($user->l_name ?? '')) ?: translate('messages.Unknown') }}
                                                </a>
                                                <div>
                                                    <a class="gray-dark fs-12 text-hover-primary max-w-215px min-w-170px text-wrap line--limit-1"
                                                        href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                                                </div>
                                                <div>
                                                    <span class="gray-dark fs-12">{{ $user->phone }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted">{{ translate('messages.Customer_not_found') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ optional($sub->start_at)->format('d M Y h:i a') }} -</div>
                                    <div>{{ optional($sub->end_at)->format('d M Y h:i a') }}</div>
                                </td>
                                <td>{{ $sub->plan_name }}</td>
                                <td>
                                    @if ($sub->plan_type === 'free_trial')
                                        <span class="badge badge-soft-success">{{ translate('messages.Free_Trial') }}</span>
                                    @else
                                        {{ \App\CentralLogics\Helpers::format_currency((float) $sub->plan_price) }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $badge }} text-capitalize">{{ $sub->status }}</span>
                                </td>
                                <td class="text-center">
                                    @if ($user && $user->pro_status)
                                        <span class="badge badge-soft-primary text-capitalize">{{ translate('messages.Pro') }}</span>
                                    @else
                                        <span class="badge badge-soft-secondary text-capitalize">{{ translate('messages.Regular') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ $orderCount }}
                                </td>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        @if ($user)
                                            <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                                href="{{ route('admin.customer.subscription-plan', $user->id) }}"
                                                title="{{ translate('messages.View_Subscription_Plan') }}">
                                                <i class="tio-visible-outlined"></i>
                                            </a>
                                        @endif
                                        @if ($sub->status === 'active')
                                            <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert"
                                                href="javascript:"
                                                data-id="pro-sub-cancel-{{ $sub->id }}"
                                                data-message="{{ translate('messages.Cancel_this_subscription') }}?"
                                                title="{{ translate('messages.Cancel_Subscription') }}">
                                                <i class="tio-clear"></i>
                                            </a>
                                            <form action="{{ route('admin.pro-customer.subscription.cancel', $sub->id) }}"
                                                method="post" id="pro-sub-cancel-{{ $sub->id }}">
                                                @csrf
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($subscriptions->isEmpty())
                <div class="empty--data text-center py-5 my-4">
                    <img src="{{ dynamicAsset('assets/admin/img/empty.png') }}" alt="empty" style="max-width:140px;height:auto;" class="mb-3">
                    <h5 class="fs-16 mb-1 text-capitalize">{{ translate('messages.No_Pro_Subscribers_Yet') }}</h5>
                    <p class="fs-12 gray-dark mb-0">{{ translate('messages.Pro_customer_subscriptions_will_appear_here_once_users_subscribe') }}</p>
                </div>
            @endif

            <div class="page-area px-3 pb-3">{!! $subscriptions->withQueryString()->links() !!}</div>
        </div>
    </div>
</div>

{{-- ============= Filter Offcanvas ============= --}}
<div id="pro_subs_filter_offcanvas" class="custom-offcanvas d-flex flex-column justify-content-between"
    style="--offcanvas-width: 500px">
    <div>
        <form id="proSubsFilterForm" action="{{ route('admin.pro-customer.list') }}" method="GET">
            @if (request()->filled('tab'))
                <input type="hidden" name="tab" value="{{ request()->tab }}">
            @endif
            @if (request()->filled('search'))
                <input type="hidden" name="search" value="{{ request()->search }}">
            @endif
            <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                <div class="px-3 py-3 d-flex justify-content-between w-100">
                    <h2 class="mb-0 fs-18 text-title font-medium text-capitalize">{{ translate('messages.Filter_Subscriber') }}</h2>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;</button>
                </div>
            </div>
            <div class="custom-offcanvas-body p-20">
                <div class="d-flex flex-column gap-20px">
                    <div class="global-bg-box p-xxl-20 p-3 rounded">
                        <div class="d-flex align-items-center text-dark gap-1 fs-14 mb-2">
                            <span class="text-capitalize">{{ translate('messages.Validity_Date_Range') }}</span>
                            <span class="gray-text" data-toggle="tooltip" data-placement="top"
                                data-original-title="{{ translate('messages.Filter_subscriptions_starting_within_the_chosen_range') }}">
                                <i class="tio-info fs-14 text-8797AB"></i>
                            </span>
                        </div>
                        <div class="position-relative">
                            <i class="tio-calendar-month icon-absolute-on-right"></i>
                            <input type="text" class="form-control h-45 position-relative bg-transparent"
                                name="dates" value="{{ request()->dates }}" placeholder="{{ translate('messages.Select_Date') }}">
                        </div>
                    </div>

                    <div class="global-bg-box p-xxl-20 p-3 rounded">
                        <div class="row g-3">
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
                                <label class="form-label fw-400 text-capitalize">{{ translate('messages.Subscription_Status') }}</label>
                                <select name="subscription_status" class="form-control js-select2-custom"
                                    data-placeholder="{{ translate('messages.Select_Status') }}">
                                    <option value="">{{ translate('messages.All') }}</option>
                                    <option value="active" {{ request()->subscription_status === 'active' ? 'selected' : '' }}>{{ translate('messages.Active') }}</option>
                                    <option value="expired" {{ request()->subscription_status === 'expired' ? 'selected' : '' }}>{{ translate('messages.Expired') }}</option>
                                    <option value="canceled" {{ request()->subscription_status === 'canceled' ? 'selected' : '' }}>{{ translate('messages.Canceled') }}</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-400 text-capitalize">{{ translate('messages.Renewal_Status') }}</label>
                                <select name="renewal_status" class="form-control js-select2-custom">
                                    <option value="">{{ translate('messages.All') }}</option>
                                    <option value="upcoming" {{ request()->renewal_status === 'upcoming' ? 'selected' : '' }}>{{ translate('messages.Upcoming_(within_7_days)') }}</option>
                                    <option value="expired" {{ request()->renewal_status === 'expired' ? 'selected' : '' }}>{{ translate('messages.Expired') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    <div class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3 position-sticky">
        <a href="{{ route('admin.pro-customer.list') }}" class="btn w-100 btn--reset offcanvas-close text-capitalize">{{ translate('messages.Reset') }}</a>
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
