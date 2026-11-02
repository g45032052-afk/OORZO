@extends('layouts.admin.app')

@section('title', translate('messages.New_joining_deliverymen'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-12">
                    <h1 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img src="{{ dynamicAsset('assets/admin/img/delivery-man.png') }}" alt="public">
                        </div>
                        <span>
                            {{ translate('messages.New_joining_request') }}
                        </span>
                    </h1>
                </div>
            </div>
        </div>

        <div class="js-nav-scroller hs-nav-scroller-horizontal mb-20">
            <!-- Nav -->
            <ul class="nav nav-tabs page-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active"
                        href="{{ route('admin.delivery-man.pending') }}">{{ translate('messages.Pending_delivery_man') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link"
                        aria-disabled="true" href="{{ route('admin.delivery-man.denied') }}">{{ translate('messages.denied_deliveryman') }}</a>
                </li>
            </ul>
            <!-- End Nav -->
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header flex-wrap gap-2 pb-1 pt-3 border-0">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            {{-- <h5 class="card-title">{{ translate('messages.deliveryman') }}<span
                                    class="badge badge-soft-dark ml-2" id="itemCount">{{ $delivery_men->total() }}</span>
                            </h5> --}}
                            <form>
                                <!-- Search -->
                                <div class="input--group input-group input-group-merge input-group-flush">
                                    <input id="datatableSearch_" type="search" name="search" class="form-control"
                                        placeholder="{{ translate('Search_by_name') }}" aria-label="Search" value="{{ request()?->search ?? null }}">
                                    <button type="submit" class="btn btn--secondary">
                                        <i class="tio-search"></i>
                                    </button>
                                </div>
                                <!-- End Search -->
                            </form>
                        </div>
                        <div class="search--button-wrapper">
                            {{-- <div class="hs-unfold">
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
                            </div> --}}
                            <a class="btn min-w-100px btn-outline-primary justify-content-center font-medium h-40px offcanvas-trigger" data-target="#joining_filter" href="javascript:">
                                <i class="tio-tune-horizontal mr-1 fs-16"></i> <span class="mt-1">{{translate('Filter')}}</span>
                            </a>
                            <div class="hs-unfold ml-3">
                                <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm" href="javascript:;"
                                   data-hs-unfold-options='{
                                "target": "#usersExportDropdown",
                                "type": "css-animation"
                            }'>
                                    <i class="tio-download-to mr-1"></i> {{translate('messages.export')}}
                                </a>

                                <div id="usersExportDropdown"
                                     class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                    <span class="dropdown-header">{{translate('messages.download_options')}}</span>
                                    <a target="__blank" id="export-excel" class="dropdown-item" href="{{route('admin.delivery-man.export-delivery-man', ['type'=>'excel','status' =>'pending', request()->getQueryString()])}}">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                             src="{{dynamicAsset('assets/admin')}}/svg/components/excel.svg"
                                             alt="Image Description">
                                        {{translate('messages.excel')}}
                                    </a>
                                    <a target="__blank" id="export-csv" class="dropdown-item" href="{{route('admin.delivery-man.export-delivery-man', ['type'=>'csv','status' =>'pending', request()->getQueryString()])}}">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                             src="{{dynamicAsset('assets/admin')}}/svg/components/placeholder-csv-format.svg"
                                             alt="Image Description">
                                        {{translate('messages.csv')}}
                                    </a>
                                </div>
                            </div>

                            <!-- <div class="hs-unfold ">
                                <div class="select-item">
                                    <select name="zone_id" class="form-control js-select2-custom set-filter"
                                            data-url="{{url()->full()}}" data-filter="zone_id">

                                        <option value="all">{{ translate('messages.all_zones') }}</option>
                                        @foreach (\App\Models\Zone::orderBy('name')->get(['id','name']) as $z)
                                            <option value="{{ $z['id'] }}"
                                                {{ isset($zone) && $zone->id == $z['id'] ? 'selected' : '' }}>
                                                {{ $z['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="hs-unfold ">
                                <div class="select-item">
                                    <select name="vehicle_id" class="form-control js-select2-custom set-filter"
                                            data-url="{{url()->full()}}" data-filter="vehicle_id">

                                        <option value="all">{{ translate('messages.all_vehicles') }}</option>
                                        @foreach (\App\Models\Vehicle::orderBy('type')->get(['id','type']) as $v)
                                            <option value="{{ $v['id'] }}"
                                                {{  request()?->vehicle_id  == $v['id'] ? 'selected' : '' }}>
                                                {{ $v['type'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="hs-unfold ">
                                <div class="select-item">
                                    <select name="job_type" class="form-control js-select2-custom set-filter"
                                            data-url="{{url()->full()}}" data-filter="job_type">
                                        <option  value="all">{{ translate('messages.all_job') }}</option>
                                        <option {{ request()?->job_type ==  'salary_base' ? 'selected' : ''}}  value="salary_base">{{ translate('messages.Salary_Base') }}</option>
                                        <option {{ request()?->job_type == 'freelance' ? 'selected' : '' }} value="freelance">{{ translate('messages.Freelance') }}</option>
                                    </select>
                                </div>
                            </div> -->

                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Table -->
                    <div class="table-responsive datatable-custom fz--14px">
                        <table id="columnSearchDatatable"
                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                            data-hs-datatables-options='{
                                 "order": [],
                                 "orderCellsTop": true,
                                 "paging":false,
                                 "columnDefs": [
               { "orderable": false, "targets": [4,5,6,7] }
           ]
                               }'>
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-capitalize">{{ translate('messages.sl') }}</th>
                                    <th class="text-capitalize w-20p">{{ translate('messages.name') }}</th>
                                    <th class="text-capitalize text-center">{{ translate('messages.contact') }}</th>
                                    <th class="text-capitalize">{{ translate('messages.zone') }}</th>
                                    <th class="text-capitalize text-center">{{ translate('Job_Type') }}</th>
                                    <th class="text-capitalize ">{{ translate('Vehicle_Type') }}</th>
                                    <th class="text-capitalize text-center">{{ translate('messages.availability_status') }}</th>
                                    <th class="text-capitalize text-center w-110px">{{ translate('messages.action') }}</th>
                                </tr>
                            </thead>

                            <tbody id="set-rows">
                                @foreach ($delivery_men as $key => $dm)
                                    <tr>
                                        <td>{{ $key + $delivery_men->firstItem() }}</td>
                                        <td>
                                            <a class="table-rest-info min-w-220"
                                                href="{{ route('admin.delivery-man.pending_dm_view', [$dm['id']]) }}">
                                                <img class="onerror-image" data-onerror-image="{{dynamicAsset('assets/admin/img/160x160/img1.jpg')}}"
                                                     src="{{ $dm['image_full_url'] }}"
                                                     alt="{{$dm['f_name']}} {{$dm['l_name']}}">
                                                <div class="info">
                                                    <h5 class="text-hover-primary mb-0">
                                                        {{ Str::limit($dm['f_name'] . ' ' . $dm['l_name'], 20, '...') }}
                                                    </h5>

                                                </div>
                                            </a>
                                        </td>
                                        <td class="text-center">

                                            <div class="info">
                                                <h5 class="text-hover-primary mb-0">
                                                {{  $dm->email }}</h5>
                                                <span class="d-block text-body fs-12">
                                                    {{ $dm['phone'] }}
                                                </span>
                                            </div>

                                        </td>
                                        <td>
                                            @if ($dm->zone)
                                                <span>{{ $dm->zone->name }}</span>
                                            @else
                                                <span>{{ translate('messages.zone_deleted') }}</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @if ($dm->earning == 1)
                                            {{  translate('Freelance')}}
                                            @else
                                            {{  translate('Salary_Base')}}
                                            @endif
                                        </td>

                                        <td>
                                            @if ($dm->vehicle)
                                                <span>{{ $dm->vehicle->type }}</span>
                                            @else
                                                <span>{{ translate('messages.Vehicle_not_found') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($dm->application_status == 'denied')
                                                <div>
                                                    <strong
                                                        class="btn py-1 btn-soft-danger text-capitalize">{{ translate('messages.denied') }}</strong>
                                                </div>
                                            @else
                                                <div>
                                                    <strong
                                                        class="btn py-1 btn-soft-info text-capitalize">{{ translate('messages.pending') }}</strong>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn--container justify-content-center">
                                                <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                                    data-toggle="tooltip" data-placement="top" title="{{translate('Details')}}" href="{{ route('admin.delivery-man.pending_dm_view', [$dm['id']]) }}">
                                                    <i class="tio-invisible font-weight-bold"></i>
                                                </a>
                                                <a class="btn btn-sm btn-outline-success action-btn request-alert"
                                                data-toggle="tooltip" data-placement="top" title="{{translate('Approve')}}"
                                                    data-url="{{ route('admin.delivery-man.application', [$dm['id'], 'approved']) }}" data-message="{{ translate('messages.you_want_to_approve_this_application_?') }}"
                                                    href="javascript:"><i class="tio-done font-weight-bold"></i></a>
                                                @if ($dm->application_status != 'denied')
                                                    <a class="btn btn-sm btn--danger btn-outline-danger action-btn request-alert" data-toggle="tooltip" data-placement="top" title="{{translate('Deny')}}"
                                                        data-url="{{ route('admin.delivery-man.application', [$dm['id'], 'denied']) }}" data-message="{{ translate('messages.you_want_to_deny_this_application_?') }}"
                                                        href="javascript:"><i
                                                        class="tio-clear"></i></a>
                                                @endif

                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @if (count($delivery_men) === 0)
                            <div class="empty--data">
                                <img src="{{ dynamicAsset('assets/admin/img/empty.png') }}" alt="public">
                                <h5>
                                    {{ translate('no_data_found') }}
                                </h5>
                            </div>
                        @endif
                        <div class="page-area px-4 pb-3">
                            <div class="d-flex align-items-center justify-content-end">
                                <div>
                                    {!! $delivery_men->appends(request()->all())->links() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Table -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>


    <div id="joining_filter" class="custom-offcanvas" style="--offcanvas-width: 500px">
        <form action="" method="GET" class="d-flex flex-column justify-content-between">
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

            <div>
                <div class="custom-offcanvas-body p-20">
                    <input type="hidden" name="search" value="{{ request()->query('search') }}">
                    <div class="d-flex flex-column gap-20px">
                        <div class="global-bg-box rounded p-xl-20 p-16">
                            <h5 class="mb-10px font-regular text-color font-normal">{{ translate('Job Type') }}</h5>
                            <div class="bg-white rounded p-xl-3 p-2">
                                <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2">
                                    @php
                                        $jobTypes = [
                                            'salary_base' => translate('messages.Salary_Base'),
                                            'freelance' => translate('messages.Freelance'),
                                        ];
                                    @endphp

                                    @foreach ($jobTypes as $key => $label)
                                        <div class="col-sm-6 col-auto">
                                            <div class="form-group m-0">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox"
                                                        class="custom-control-input"
                                                        id="job_{{ $key }}"
                                                        name="job_type[]"
                                                        value="{{ $key }}"
                                                        {{ is_array(request()->job_type) && in_array($key, request()->job_type) ? 'checked' : '' }}>
                                                    <label class="custom-control-label text-title" for="job_{{ $key }}">
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="global-bg-box rounded p-xl-20 p-16">
                            <h5 class="mb-10px font-regular text-color font-normal">{{ translate('Vehicle Type') }}</h5>
                            <div class="bg-white rounded p-xl-3 p-2">
                                <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2 order-status_controller custom__select-controller">
                                    <div class="col-sm-6 col-auto">
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input check-all" id="vehicle_all">
                                                <label class="custom-control-label text-title" for="vehicle_all">
                                                    {{ translate('messages.All') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    @foreach (\App\Models\Vehicle::orderBy('type')->get(['id','type']) as $vehicle)
                                        <div class="col-sm-6 col-auto">
                                            <div class="form-group m-0">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox"
                                                        class="custom-control-input vehicle-check"
                                                        id="vehicle_{{ $vehicle->id }}"
                                                        name="vehicle_id[]"
                                                        value="{{ $vehicle->id }}"
                                                        {{ is_array(request()->vehicle_id) && in_array($vehicle->id, request()->vehicle_id) ? 'checked' : '' }}>
                                                    <label class="custom-control-label text-title" for="vehicle_{{ $vehicle->id }}">
                                                        {{ $vehicle->type }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="global-bg-box rounded p-xl-20 p-16">
                            <h5 class="mb-10px font-regular text-color font-normal">{{ translate('Zone') }}</h5>
                            <div class="bg-white rounded p-xl-3 p-2">
                                <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2 order-status_controller">
                                    <div class="col-sm-6 col-auto">
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input check-all" id="zone_all">
                                                <label class="custom-control-label text-title" for="zone_all">
                                                    {{ translate('messages.All Zones') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    @foreach (\App\Models\Zone::orderBy('name')->get(['id','name']) as $zone)
                                        <div class="col-sm-6 col-auto">
                                            <div class="form-group m-0">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox"
                                                        class="custom-control-input zone-check"
                                                        id="zone_{{ $zone->id }}"
                                                        name="zone_id[]"
                                                        value="{{ $zone->id }}"
                                                        {{ is_array(request()->zone_id) && in_array($zone->id, request()->zone_id) ? 'checked' : '' }}>
                                                    <label class="custom-control-label text-title" for="zone_{{ $zone->id }}">
                                                        {{ $zone->name }}
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
            </div>

            <div class="align-items-center h-84px bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3 position-absolute w-100">
                <a href="{{ url()->current() }}" class="btn w-100 btn--reset offcanvas-close">{{ translate('Reset') }}</a>
                <button type="submit" class="btn w-100 btn--primary">{{ translate('Apply') }}</button>
            </div>
        </form>
    </div>

    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>
@endsection

@push('script_2')
    <script>
        "use strict";
        $(document).on('ready', function() {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function() {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });

            $('#column2_search').on('keyup', function() {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });

            $('#column3_search').on('keyup', function() {
                datatable
                    .columns(3)
                    .search(this.value)
                    .draw();
            });

            $('#column4_search').on('keyup', function() {
                datatable
                    .columns(4)
                    .search(this.value)
                    .draw();
            });
            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function() {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });

        });

        $('.request-alert').on('click',function (){
            let url = $(this).data('url');
            let message = $(this).data('message');
            request_alert(url, message);
        })

        function request_alert(url, message) {
            Swal.fire({
                title: '{{ translate('messages.Are_you_sure_?') }}',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ translate('messages.no') }}',
                confirmButtonText: '{{ translate('messages.yes') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = url;
                }
            })
        }
    </script>
@endpush
