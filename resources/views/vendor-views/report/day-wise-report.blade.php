@extends('layouts.vendor.app')

@section('title', translate('messages.transaction_report'))

@push('css_or_js')
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{ dynamicAsset('assets/admin/img/report.png') }}" class="w--22" alt="">
            </span>
            <span>
                {{ translate('messages.transaction_report') }}
                @if ($from && $to)
                <span class="h6 mb-0 badge badge-soft-success ml-2">
                ( {{$from}} - {{ $to}} )</span>
                @endif
            </span>
        </h1>
    </div>
    <!-- End Page Header -->
    <div class="card mb-20">
        <div class="card-body">
            <h4 class="">{{ translate('Search_Data') }}</h4>
            @include('vendor-views.report.partials._report_filter')
        </div>
    </div>

    <div class="mb-20">
        <div class="row g-2">
                    <div class="col-sm-4">
                        <a class="__card-3 h-100" href="#">
                            <img src="{{ dynamicAsset('assets/admin/img/report/new/trx1.png') }}" class="icon"
                                alt="report/new">
                            <h3 class="title text-008958">{{ \App\CentralLogics\Helpers::number_format_short($delivered) }}
                            </h3>
                            <h6 class="subtitle">{{ translate('Completed_Transaction') }}</h6>
                            <div class="info-icon" data-toggle="tooltip" data-placement="top"
                                data-original-title="{{ translate('When_the_order_is_successfully_delivered_full_order_amount_goes_to_this_section.') }}">
                                <img src="{{ dynamicAsset('assets/admin/img/report/new/info1.png') }}"
                                    alt="report/new">
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-4">
                        <a class="__card-3 h-100" href="#">
                            <img src="{{ dynamicAsset('assets/admin/img/report/new/trx2.png') }}" class="icon"
                                alt="report/new">
                            <h3 class="title text-FF5A54">{{ \App\CentralLogics\Helpers::number_format_short($on_hold) }}
                            </h3>
                            <h6 class="subtitle">{{ translate('On_Hold_Transaction') }}</h6>
                            <div class="info-icon" data-toggle="tooltip" data-placement="top"
                                data-original-title="{{ translate('The_on_going_orders') }}">
                                <img src="{{ dynamicAsset('assets/admin/img/report/new/info3.png') }}"
                                    alt="report/new">
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-4">
                        <a class="__card-3 h-100" href="#">
                            <img src="{{ dynamicAsset('assets/admin/img/report/new/trx3.png') }}" class="icon"
                                alt="report/new">
                            <h3 class="title text-FF5A54">{{ \App\CentralLogics\Helpers::number_format_short($canceled) }}
                            </h3>
                            <h6 class="subtitle">{{ translate('Refunded_Transaction') }}</h6>
                            <div class="info-icon" data-toggle="tooltip" data-placement="top"
                                data-original-title="{{ translate('If_the_order_is_successfully_refunded,_the_full_order_amount_goes_to_this_section_without_the_delivery_fee_and_delivery_tips.') }}">
                                <img src="{{ dynamicAsset('assets/admin/img/report/new/info3.png') }}"
                                    alt="report/new">
                            </div>
                        </a>
                    </div>

        </div>
    </div>

    <!-- End Stats -->
    <!-- Card -->
    <div class="card mt-3">
        <!-- Header -->
        <div class="card-header border-0 py-2">
            <div class="search--button-wrapper">
                <h3 class="card-title">
                    {{ translate('messages.order_transactions') }} <span
                        class="badge badge-soft-secondary" id="countItems">{{ $order_transactions->total() }}</span>
                </h3>
                <form  class="search-form">
                    <!-- Search -->
                    <div class="input--group input-group input-group-merge input-group-flush">
                        <input class="form-control" value="{{ request()->search ?? null }}" placeholder="{{ translate('Search_by_Order_ID') }}" name="search">
                        <button type="submit" class="btn btn--secondary secondary-cmn"><i class="tio-search"></i></button>
                    </div>
                    <!-- End Search -->
                </form>
                <!-- Static Export Button -->
                <div class="hs-unfold ml-3">
                    <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn font--sm"
                        href="javascript:;"
                        data-hs-unfold-options="{
                            &quot;target&quot;: &quot;#usersExportDropdown&quot;,
                            &quot;type&quot;: &quot;css-animation&quot;
                        }"
                        data-hs-unfold-target="#usersExportDropdown" data-hs-unfold-invoker="">
                        <i class="tio-download-to mr-1"></i> {{ translate('export') }}
                    </a>

                    <div id="usersExportDropdown"
                        class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right hs-unfold-content-initialized hs-unfold-css-animation animated hs-unfold-reverse-y hs-unfold-hidden">

                        <span class="dropdown-header">{{ translate('download_options') }}</span>
                        <a id="export-excel" class="dropdown-item"
                            href="{{ route('vendor.report.day-wise-report-export', ['type' => 'excel', request()->getQueryString()]) }}">
                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                                src="{{ dynamicAsset('assets/admin/svg/components/excel.svg') }}"
                                alt="Image Description">
                            {{ translate('messages.excel') }}
                        </a>
                        <a id="export-csv" class="dropdown-item"
                            href="{{ route('vendor.report.day-wise-report-export', ['type' => 'csv', request()->getQueryString()]) }}">
                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                                src="{{ dynamicAsset('assets/admin/svg/components/placeholder-csv-format.svg') }}"
                                alt="Image Description">
                            {{ translate('messages.csv') }}
                        </a>

                    </div>
                </div>
                <!-- Static Export Button -->
            </div>
        </div>
        <!-- End Header -->

        <!-- Body -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="datatable" class="table table-thead-bordered table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th class="border-0">{{ translate('sl') }}</th>
                            <th class="border-0">{{ translate('messages.order_id') }}</th>
                            <th class="border-0">{{ translate('messages.restaurant') }}</th>
                            <th class="border-0">{{ translate('messages.customer_name') }}</th>
                            <th class="border-0 min-w-120">{{ translate('messages.total_item_amount') }}</th>
                            <th class="border-0">{{ translate('messages.item_discount') }}</th>
                            <th class="border-0">{{ translate('messages.coupon_discount') }}</th>
                            <th class="border-0">{{ translate('messages.referral_discount') }}</th>
                            <th class="border-0">{{ translate('messages.Pro_Discount') }}</th>
                            <th class="border-0">{{ translate('messages.discounted_amount') }}</th>
                            <th class="border-0">{{ translate('messages.vat/tax') }}</th>
                            <th class="border-0">{{ translate('messages.delivery_charge') }}</th>
                            <th class="border-0">{{ translate('messages.order_amount') }}</th>
                            <th class="border-0">{{ translate('messages.admin_discount') }}</th>
                            <th class="border-0">{{ translate('messages.restaurant_discount') }}</th>
                            <th class="border-0">{{ translate('messages.admin_commission') }}
                                <span class="form-label-secondary m-0" data-toggle="tooltip" data-placement="top"
                                      data-original-title="Gross commission on this order's items, before anything the admin funded. The Admin Net Income column nets those off, so the two do not add up.">
                                    <i class="tio-info text-gray1 fs-16"></i>
                                </span>
                            </th>
                            <th class="border-0">{{ \App\CentralLogics\Helpers::get_business_data('additional_charge_name')??translate('messages.additional_charge') }}</th>
                            <th class="border-0">{{ translate('messages.extra_packaging_amount') }}</th>
                            <th class="min-w-140 text-capitalize">{{ translate('commision_on_delivery_charge') }}</th>
                            <th class="min-w-140 text-capitalize">{{ translate('admin_net_income') }}
                                <span class="form-label-secondary m-0" data-toggle="tooltip" data-placement="top"
                                      data-original-title="What the admin actually keeps: this commission and the commission on delivery, LESS what the admin paid for - free delivery, an admin-borne coupon, a referral bonus, or an admin-borne product discount. It is deliberately not the sum of the Admin Commission and Commission On Delivery Charge columns, which are both gross.">
                                    <i class="tio-info text-gray1 fs-16"></i>
                                </span>
                            </th>
                            <th class="min-w-140 text-capitalize">{{ translate('restaurant_net_income') }}</th>
                            <th class="border-0 min-w-120">{{ translate('messages.amount_received_by') }}</th>
                            <th class="border-top border-bottom text-capitalize">{{ translate('messages.payment_method') }}</th>
                            <th class="border-0">{{ translate('messages.payment_status') }}</th>
                            <th class="border-0">{{ translate('messages.action') }}</th>
                        </tr>
                    </thead>
                    <tbody id="set-rows">
                        @foreach ($order_transactions as $k => $ot)
                            <tr scope="row">
                                <td>{{ $k + $order_transactions->firstItem() }}</td>
                                    <td><a
                                            href="{{ route('vendor.order.details', $ot->order_id) }}">{{ $ot->order_id }}</a>
                                    </td>
                                <td  class="text-capitalize">
                                    @if($ot->order->restaurant)
                                        {{Str::limit($ot->order->restaurant->name,25,'...')}}
                                        @include('partials._verified_restaurant_badge', ['restaurant' => $ot->order->restaurant])
                                    @endif
                                </td>
                                <td class="white-space-nowrap">
                                    @if ($ot->order->customer)
                                        <p class="text-body text-capitalize">
                                            <strong>{{ $ot->order->customer['f_name'] . ' ' . $ot->order->customer['l_name'] }}</strong>
                                        </p>
                                        @elseif($ot->order->is_guest)
                                        @php($customer_details = json_decode($ot->order['delivery_address'],true))
                                        <strong>{{$customer_details['contact_person_name']}}</strong>
                                    @else
                                        <label class="badge badge-danger">{{ translate('messages.invalid_customer_data') }}</label>
                                    @endif
                                </td>
                                <?php
                                $discount_by_admin = 0;
                                    if($ot->order->discount_on_product_by == 'admin'){
                                        $discount_by_admin = $ot->order['restaurant_discount_amount'];
                                    };
                                ?>
                                <td class="white-space-nowrap">{{ \App\CentralLogics\Helpers::format_currency($ot->report_item_amount) }}</td>
                                <td class="white-space-nowrap">{{ \App\CentralLogics\Helpers::format_currency($ot->order->details()->sum(DB::raw('discount_on_food * quantity')) + ($ot->order->bogo_discount_amount ?? 0)) }}</td>
                                <td class="white-space-nowrap">{{ \App\CentralLogics\Helpers::format_currency($ot->order['coupon_discount_amount']) }}</td>
                                <td class="white-space-nowrap">{{ \App\CentralLogics\Helpers::format_currency($ot->order['ref_bonus_amount']) }}</td>
                                <td class="white-space-nowrap">{{ \App\CentralLogics\Helpers::format_currency($ot->pro_discount ?? 0) }}</td>

                                <td class="white-space-nowrap">{{ \App\CentralLogics\Helpers::number_format_short($ot->report_discounted_amount) }}</td>
                                <td class="white-space-nowrap">{{ \App\CentralLogics\Helpers::format_currency($ot->tax) }}</td>
                                <td class="white-space-nowrap">{{ \App\CentralLogics\Helpers::format_currency($ot->delivery_charge + $ot->delivery_fee_comission) }}</td>
                                <td class="white-space-nowrap">{{ \App\CentralLogics\Helpers::format_currency($ot->order_amount) }}</td>
                                <td class="white-space-nowrap">{{ \App\CentralLogics\Helpers::format_currency($ot->admin_expense) }}</td>
                                <td class="white-space-nowrap">{{ \App\CentralLogics\Helpers::format_currency($ot->discount_amount_by_restaurant) }}</td>
                                {{-- admin_commission: the same figure the admin panel shows for this
                                     order. It used to be derived here from admin_commission less the
                                     additional charge plus $ot->admin_expens - a property that does
                                     not exist, so it always read as nothing - which put a different
                                     number in front of the restaurant than the admin was looking at. --}}
                                <td class="white-space-nowrap">{{ \App\CentralLogics\Helpers::format_currency(($ot->report_base_item_amount * $ot['commission_percentage']) / 100) }}</td>
                                <td class="white-space-nowrap">{{ \App\CentralLogics\Helpers::format_currency(($ot->additional_charge)) }}</td>
                                <td class="white-space-nowrap">{{ \App\CentralLogics\Helpers::format_currency(($ot->extra_packaging_amount)) }}</td>
                                <td class="white-space-nowrap">{{ \App\CentralLogics\Helpers::format_currency($ot->delivery_fee_comission) }}</td>
                                <td class="white-space-nowrap">{{ \App\CentralLogics\Helpers::format_currency($ot->admin_commission + $ot->delivery_fee_comission ) }}</td>
                                <td class="white-space-nowrap">{{ \App\CentralLogics\Helpers::format_currency($ot->restaurant_amount - $ot->tax) }}</td>
                                @if ($ot->received_by == 'admin')
                                    <td class="text-capitalize white-space-nowrap">{{ translate('messages.admin') }}</td>
                                @elseif ($ot->received_by == 'deliveryman')

                                    <td class="text-capitalize white-space-nowrap">
                                        <div>{{ translate('messages.delivery_man') }} </div>
                                        <div class="text-right mw--85px">
                                            @if (isset($ot->order->delivery_man) && $ot->order->delivery_man->earning == 1)
                                            <span class="badge badge-soft-primary">
                                                {{translate('messages.freelance')}}
                                            </span>
                                            @elseif (isset($ot->order->delivery_man) && $ot->order->delivery_man->earning == 0 && $ot->order->delivery_man->type == 'restaurant_wise')
                                            <span class="badge badge-soft-warning">
                                                {{translate('messages.restaurant')}}
                                            </span>
                                            @elseif (isset($ot->order->delivery_man) && $ot->order->delivery_man->earning == 0 && $ot->order->delivery_man->type == 'zone_wise')
                                            <span class="badge badge-soft-success">
                                                {{translate('messages.admin')}}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                @elseif ($ot->received_by == 'restaurant')
                                    <td class="text-capitalize white-space-nowrap">{{ translate('messages.restaurant') }}</td>
                                @endif
                                <td class="mw--85px text-capitalize min-w-120 ">
                                        {{ translate(str_replace('_', ' ', $ot->order['payment_method'])) }}
                                </td>
                                <td class="text-capitalize white-space-nowrap">
                                    @if ($ot->status)
                                    <span class="badge badge-soft-danger">
                                        {{translate('messages.refunded')}}
                                    </span>
                                    @else
                                    <span class="badge badge-soft-success">
                                        {{translate('messages.completed')}}
                                    </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="btn--container justify-content-center">
                                        <a class="btn btn-outline-success square-btn btn-sm mr-1 action-btn"  href="{{route('vendor.report.generate-statement',[$ot['id']])}}">
                                            <i class="tio-download-to"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <!-- End Body -->
        @if (count($order_transactions) !== 0)
            <hr>
        @endif
        <div class="page-area">
            {!! $order_transactions->links() !!}
        </div>
        @if (count($order_transactions) === 0)
            <div class="empty--data">
                <img src="{{ dynamicAsset('assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                <h5>
                    {{ translate('no_data_found') }}
                </h5>
            </div>
        @endif
    </div>
    <!-- End Card -->
</div>

@endsection

@push('script_2')
    <script src="{{dynamicAsset('assets/admin')}}/js/view-pages/vendor/report.js"></script>
@endpush
