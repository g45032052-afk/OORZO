@extends('layouts.admin.app')

@section('title',translate('messages.Rejected_Restaurant_List'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')

    <div class="content container-fluid">

        <div class="page-header">
            <h1 class="page-header-title"><i class="tio-filter-list"></i> {{ translate('messages.Rejected_Restaurant_List')}} </h1>
            <!-- Resturent List -->
                    <!-- Resturent Card Wrapper -->
        </div>
        <!-- End Page Header -->

        <div class="d-flex flex-wrap mb-4 __gap-15px">
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                <!-- Nav -->
                <ul class="nav nav-tabs page-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link "  aria-disabled="true" href="{{route('admin.restaurant.pending')}}">{{translate('messages.Pending_Requests')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{route('admin.restaurant.denied')}}"  >{{translate('messages.Rejected_Request')}}</a>
                    </li>
                </ul>
                <!-- End Nav -->
            </div>

        </div>








        <!-- Resturent List -->
        <div class="row gx-2 gx-lg-3 mt-3" >
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <!-- Card Header -->

                    <div class="card-header flex-wrap gap-2 py-3 border-0">
                        <div class="d-flex align-items-center justify-content-between gap-2">

                            <form class="">
                                <!-- Search -->
                                <div class="input--group input-group input-group-merge input-group-flush border rounded">
                                    <input id="datatableSearch_" type="search" name="search" class="form-control border-0"
                                            placeholder="{{ translate('Ex:_Search_by_restaurant_name_phone_or_email') }}" aria-label="{{translate('messages.search')}}" >
                                    <button type="submit" class="btn btn--reset px-3 py-2"><i class="tio-search"></i></button>
                                </div>
                                <!-- End Search -->
                            </form>
                        </div>
                        {{-- <div class="d-flex align-items-center gap-lg-3 gap-2 flex-wrap">
                            <div class="hs-unfold">
                                <a class="js-hs-unfold-invoker btn btn-sm btn--reset dropdown-toggle min-height-40" href="javascript:;"
                                    data-hs-unfold-options='{
                                            "target": "#usersExportDropdown",
                                            "type": "css-animation"
                                        }'>
                                    <i class="tio-download-from-cloud mr-1 fs-16"></i> {{ translate('messages.export') }}
                                </a>

                                <div id="usersExportDropdown"
                                    class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">

                                    <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                                    <a id="export-excel" class="dropdown-item" href="{{ route('admin.food.export', ['type' => 'excel', request()->getQueryString()]) }}">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg"
                                            alt="Image Description">
                                        {{ translate('messages.excel') }}
                                    </a>
                                    <a id="export-csv" class="dropdown-item" href="{{ route('admin.food.export', ['type' => 'csv', request()->getQueryString()]) }}">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                            alt="Image Description">
                                        .{{ translate('messages.csv') }}
                                    </a>
                                </div>
                            </div>
                            <div class="hs-unfold">
                                <a class="js-hs-unfold-invoker btn btn-outline-primary min-w-100px justify-content-center font-medium btn-sm filter-show offcanvas-trigger"
                                        data-target="#filter_pending-request" href="javascript:">
                                    <i class="tio-tune-horizontal mr-1 fs-16"></i>Filter
                                </a>
                            </div>
                        </div> --}}
                    </div>
                    <!-- Card Header -->

                    <!-- Table -->
                    <div class="table-responsive datatable-custom resturant-list-table">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                 "order": [],
                                 "orderCellsTop": true,
                                 "paging":false

                               }'>
                            <thead class="thead-light">
                            <tr>
                                <th class="text-uppercase w-90px">{{translate('messages.sl')}}</th>
                                <th class="initial-58">{{translate('messages.restaurant_info')}}</th>
                                <th class="w-230px text-center">{{translate('messages.owner_info')}} </th>
                                <th class="w-130px">{{translate('messages.zone')}}</th>
                                <th class="w-100px">{{translate('messages.Business_Model')}}</th>
                                <th class="w-100px">{{translate('messages.status')}}</th>
                                <th class="text-center w-60px">{{translate('messages.action')}}</th>
                            </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($restaurants as $key=>$dm)
                                <tr>
                                    <td>{{$key+$restaurants->firstItem()}}</td>
                                    <td>
                                        <a href="{{route('admin.restaurant.view',  ['restaurant'=>$dm->id, 'tab'=> 'pending-list'])}}" alt="view restaurant" class="table-rest-info"min-w-280 max-w-315>
                                            <img  class="onerror-image" data-onerror-image="{{dynamicAsset('assets/admin/img/100x100/food-default-image.png')}}"
                                                src="{{ $dm['logo_full_url'] ?? dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}">
                                            <div class="info">
                                                <span class="d-block text-body">
                                                    {{Str::limit($dm->name,20,'...')}}<br>
                                                </span>
                                            </div>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="d-block owner--name text-center">
                                            {{$dm->vendor->f_name.' '.$dm->vendor->l_name}}
                                        </span>
                                        <span class="d-block font-size-sm text-center">
                                            {{$dm['phone']}}
                                        </span>
                                    </td>
                                    <td>
                                        {{$dm->zone?$dm->zone->name:translate('messages.zone_deleted')}}
                                    </td>

                                    <td>
                                        @if ($dm->restaurant_model == 'commission')
                                            <div>{{ translate('Commission_Base') }}</div>
                                        @elseif($dm->restaurant_model == 'none')
                                            <div>{{ translate('Not_chosen') }}</div>
                                        @else
                                            <div>{{ translate('Subscription_Base') }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if(isset($dm->vendor->status))
                                            @if($dm->vendor->status)
                                            <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{$dm->id}}">
                                                <input type="checkbox" data-url="{{route('admin.restaurant.status',[$dm->id,$dm->status?0:1])}}" data-message="{{translate('messages.you_want_to_change_this_restaurant_status')}}" class="toggle-switch-input status_change_alert" id="stocksCheckbox{{$dm->id}}" {{$dm->status?'checked':''}}>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                            @else
                                            <span class="badge badge-soft-danger">{{translate('messages.denied')}}</span>
                                            @endif
                                        @else
                                            <span class="badge badge-soft-danger">{{translate('messages.not_approved')}}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn btn-sm btn--primary btn-outline-primary action-btn"

                                               data-toggle="tooltip" data-placement="top" title="{{translate('Details')}}"
                                               href="{{route('admin.restaurant.view',  ['restaurant'=>$dm->id, 'tab'=> 'pending-list'])}}"> <i class="tio-invisible font-weight-bold"></i> </a>
                                            <a class="btn btn-sm btn--primary btn-outline-primary action-btn request_alert" data-toggle="tooltip" data-placement="top" title="{{translate('Approve')}}"
                                                data-url="{{route('admin.restaurant.application',[$dm['id'],1])}}" data-message="{{translate('messages.you_want_to_approve_this_application')}}"
                                                    href="javascript:"><i class="tio-done font-weight-bold"></i></a>
                                        </div>

                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if(count($restaurants) === 0)
                        <div class="empty--data">
                            <img src="{{dynamicAsset('assets/admin/img/empty.png')}}" alt="public">
                            <h5>
                                {{translate('no_data_found')}}
                            </h5>
                        </div>
                        @endif
                        <div class="page-area px-4 pb-3">
                            <div class="d-flex align-items-center justify-content-end">
                                <div>
                                    {!! $restaurants->appends(request()->all())->links() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Table -->
                </div>
                <!-- End Card -->
            </div>
        </div>
        <!-- Resturent List -->
    </div>


<!-- filter_pending-request -->
<div id="filter_pending-request" class="custom-offcanvas d-flex flex-column justify-content-between"
         style="--offcanvas-width: 500px">
        <div>
            <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                <div class="px-3 py-3 d-flex justify-content-between w-100">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h2 class="mb-0 fs-18 text-title font-medium">{{ translate('Filter Pending Request') }}</h2>
                    </div>
                    <button type="button"
                            class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                            aria-label="Close">&times;
                    </button>
                </div>
            </div>
            <form id="denied-filter-form" action="{{ route('admin.restaurant.denied') }}" method="GET">
                <div class="custom-offcanvas-body p-20">
                    <input type="hidden" name="search" value="{{ request()->query('search') }}">
                    <div class="d-flex flex-column gap-20px">
                        <div class="global-bg-box rounded p-xl-20 p-16">
                            <h5 class="mb-10px font-regular text-color font-normal">{{ translate('Food Type') }}</h5>
                            <div class="bg-white rounded p-xl-3 p-2">
                                <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2">
                                    <div class="col-sm-6 col-auto">
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="type_veg" name="types[]" value="veg" {{ in_array('veg', (array) request()->query('types', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label text-title" for="type_veg">
                                                    {{translate('messages.Veg')}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-auto">
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="type_non_veg" name="types[]" value="non_veg" {{ in_array('non_veg', (array) request()->query('types', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label text-title" for="type_non_veg">
                                                    {{translate('messages.Non-veg')}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="global-bg-box rounded p-xl-20 p-16">
                            <h5 class="mb-10px font-regular text-color font-normal">{{ translate('Business Model') }}</h5>
                            <div class="bg-white rounded p-xl-3 p-2">
                                <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2">
                                    <div class="col-auto">
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="bm_commission" name="restaurant_models[]" value="commission" {{ in_array('commission', (array) request()->query('restaurant_models', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label text-title" for="bm_commission">
                                                    {{translate('messages.Commission')}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="bm_subscribed" name="restaurant_models[]" value="subscribed" {{ in_array('subscribed', (array) request()->query('restaurant_models', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label text-title" for="bm_subscribed">
                                                    {{translate('messages.Subscribed')}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="bm_unsubscribed" name="restaurant_models[]" value="unsubscribed" {{ in_array('unsubscribed', (array) request()->query('restaurant_models', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label text-title" for="bm_unsubscribed">
                                                    {{translate('messages.Unsubscribed')}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="global-bg-box rounded p-xl-20 p-16">
                            <h5 class="mb-10px font-regular text-color font-normal">{{ translate('Zone') }}</h5>
                            <div class="bg-white rounded p-xl-3 p-2">
                                <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2">
                                    <div class="col-sm-6 col-auto">
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="zones_all">
                                                <label class="custom-control-label text-title" for="zones_all">
                                                    {{translate('messages.all')}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    @foreach(($zonesList ?? collect()) as $z)
                                        <div class="col-sm-6 col-auto">
                                            <div class="form-group m-0">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input zone-checkbox" id="zone_{{ $z->id }}" name="zone_ids[]" value="{{ $z->id }}" {{ in_array($z->id, array_map('intval', (array) request()->query('zone_ids', []))) ? 'checked' : '' }}>
                                                    <label class="custom-control-label text-title" for="zone_{{ $z->id }}">
                                                        {{ $z->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3 position-sticky">
                    <button type="button" class="btn w-100 btn--reset" id="denied-filter-reset">{{ translate('Reset') }}</button>
                    <button type="submit" class="btn w-100 btn--primary">{{ translate('Apply') }}</button>
                </div>
            </form>
        </div>
    </div>
<div id="offcanvasOverlay" class="offcanvas-overlay"></div>

@endsection

@push('script_2')
    <script>
        "use strict";
        $('.status_change_alert').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            status_change_alert(url, message, event)
        })

        function status_change_alert(url, message, e) {
            e.preventDefault();
            Swal.fire({
                title: '{{ translate('Are_you_sure?') }}',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ translate('no') }}',
                confirmButtonText: '{{ translate('yes') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href=url;
                }
            })
        }
        $('.request_alert').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            request_alert(url, message)
        })
        function request_alert(url, message) {
            Swal.fire({
                title: "{{translate('messages.are_you_sure_?')}}",
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: "{{translate('messages.no')}}",
                confirmButtonText: "{{translate('messages.yes')}}",
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = url;
                }
            })
        }
        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function () {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });

            $('#column2_search').on('keyup', function () {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });

            $('#column3_search').on('keyup', function () {
                datatable
                    .columns(3)
                    .search(this.value)
                    .draw();
            });

            $('#column4_search').on('keyup', function () {
                datatable
                    .columns(4)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });

        // Zones Select-All behavior
        (function(){
            function updateZonesAllState(){
                var total = $('.zone-checkbox').length;
                var checked = $('.zone-checkbox:checked').length;
                $('#zones_all').prop('checked', total > 0 && checked === total);
            }
            $(document).on('change', '#zones_all', function(){
                var checked = $(this).is(':checked');
                $('.zone-checkbox').prop('checked', checked);
            });
            $(document).on('change', '.zone-checkbox', function(){
                updateZonesAllState();
            });
            // initialize on load
            $(document).ready(function(){
                updateZonesAllState();
            });
        })();

        // Reset button -> clear all filters
        $(document).on('click', '#denied-filter-reset', function(){
            window.location.href = '{{ route('admin.restaurant.denied') }}';
        });
    </script>

@endpush
