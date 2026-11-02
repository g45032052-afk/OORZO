@extends('layouts.admin.app')

@section('title',translate('messages.vehicle_types'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{ translate('messages.vehicle_types') }}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        {{-- The empty state takes over the whole card, but only when no vehicle type
             has ever been added — a search that matches nothing keeps the table so
             the search box stays reachable. --}}
        @if ($vehicles->total() === 0 && !request()->has('search'))
            <div class="card">
                <div class="card-body empty--data py-5">
                    <img src="{{ dynamicAsset('assets/admin/img/price-emty.png') }}"
                        alt="{{ translate('messages.vehicle_types') }}">
                    <h5 class="mb-2">{{ translate('messages.Currently_you_dont_have_any_Vehicle_Types') }}</h5>
                    <p class="fs-12 mx-auto mb-4 max-w-400px">
                        {{ translate('messages.By_adding_vehicle_type_when_deliveryman_created_the_deliveryman_need_to_select_their_preferable_vehicle_for_better_performance.') }}
                    </p>
                    <a href="javascript:void(0)" class="btn btn--primary offcanvas-trigger"
                        data-target="#offcanvas__vehicle" data-action="create">
                        {{ translate('messages.Add_New') }}
                    </a>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header py-2 border-0">
                    <div class="search--button-wrapper">
                        <h5 class="card-title">
                            {{ translate('messages.Type_list') }}
                            <span class="badge badge-soft-dark ml-2" id="itemCount">{{ $vehicles->total() }}</span>
                        </h5>

                        <form>
                            <!-- Search -->
                            <div class="input--group input-group input-group-merge input-group-flush">
                                <input id="datatableSearch_" type="search" name="search" class="form-control"
                                    value="{{ request()?->search ?? null }}"
                                    placeholder="{{ translate('messages.Search_here') }}"
                                    aria-label="{{ translate('messages.search') }}" required>
                                <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                            </div>
                            <!-- End Search -->
                        </form>

                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn export--btn btn-outline-primary btn--primary font--sm"
                                href="javascript:;"
                                data-hs-unfold-options='{"target": "#vehicleExportDropdown","type": "css-animation"}'>
                                <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                            </a>
                            <div id="vehicleExportDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                                <a id="export-excel" class="dropdown-item"
                                    href="{{ route('admin.vehicle.export', ['type' => 'excel', 'search' => request()->search]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg" alt="">
                                    {{ translate('messages.excel') }}
                                </a>
                                <a id="export-csv" class="dropdown-item"
                                    href="{{ route('admin.vehicle.export', ['type' => 'csv', 'search' => request()->search]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                        alt="">
                                    {{ translate('messages.csv') }}
                                </a>
                            </div>
                        </div>

                        <a href="javascript:void(0)" class="btn btn--primary offcanvas-trigger"
                            data-target="#offcanvas__vehicle" data-action="create">
                            <i class="tio-add-circle-outlined"></i> {{ translate('messages.Add_New') }}
                        </a>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{ translate('messages.sl') }}</th>
                                <th class="border-0">{{ translate('messages.Vehicle_Info') }}</th>
                                <th class="border-0">
                                    <div class="min-w-135px">
                                        {{ translate('messages.Coverage_Area') }} ({{ \App\CentralLogics\Helpers::distanceUnitLabel() }})
                                    </div>
                                </th>
                                <th class="border-0">
                                    <div class="min-w-135px">
                                        {{ translate('messages.Extra_charges') }}
                                        ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                    </div>
                                </th>
                                <th class="border-0">{{ translate('messages.status') }}</th>
                                <th class="border-0 text-center">{{ translate('messages.action') }}</th>
                            </tr>
                        </thead>

                        <tbody id="set-rows">
                            @foreach ($vehicles as $key => $vehicle)
                                <tr>
                                    <td class="pl-4">{{ $key + $vehicles->firstItem() }}</td>
                                    <td>
                                        <a class="d-block text-title max-w-220px"
                                            href="{{ route('admin.vehicle.view', [$vehicle->id]) }}"
                                            title="{{ $vehicle['type'] }}">
                                            {{ Str::limit($vehicle['type'], 25, '...') }}
                                        </a>
                                        <span class="fs-12 text-muted">#{{ $vehicle->id }}</span>
                                    </td>
                                    <td>
                                        {{ $vehicle->starting_coverage_area }} - {{ $vehicle->maximum_coverage_area }}
                                    </td>
                                    {{-- The currency symbol already sits in the column heading, so the
                                         cell only carries the amount. --}}
                                    <td>{{ number_format($vehicle->extra_charges, 2) }}</td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm"
                                            for="stocksCheckbox{{ $vehicle->id }}">
                                            <input type="checkbox"
                                                data-url="{{ route('admin.vehicle.status', [$vehicle['id'], $vehicle->status ? 0 : 1]) }}"
                                                class="toggle-switch-input redirect-url"
                                                id="stocksCheckbox{{ $vehicle->id }}" {{ $vehicle->status ? 'checked' : '' }}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--primary btn-outline-primary offcanvas-trigger"
                                                href="javascript:" data-target="#offcanvas__vehicle" data-action="edit"
                                                data-url="{{ route('admin.vehicle.edit', [$vehicle['id']]) }}"
                                                title="{{ translate('messages.edit_vehicle') }}">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger form-alert"
                                                href="javascript:" data-id="vehicle-{{ $vehicle['id'] }}"
                                                data-message="{{ translate('messages.Want_to_delete_this_item') }}"
                                                title="{{ translate('messages.delete_vehicle') }}">
                                                <i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{ route('admin.vehicle.delete', ['vehicle' => $vehicle['id']]) }}"
                                                method="post" id="vehicle-{{ $vehicle['id'] }}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- End Table -->

                @if (count($vehicles) !== 0)
                    <div class="page-area px-4 pb-3">
                        <div class="d-flex align-items-center justify-content-end">
                            <div>
                                {!! $vehicles->withQueryString()->links() !!}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="empty--data">
                        <img src="{{ dynamicAsset('assets/admin/svg/illustrations/sorry.svg') }}" alt="">
                        <h5>{{ translate('messages.no_data_found') }}</h5>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div id="offcanvas__vehicle" class="custom-offcanvas d-flex flex-column justify-content-between">
        <div id="data-view" class="h-100"></div>
    </div>
    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>
@endsection

@push('script_2')
    <script src="{{ dynamicAsset('assets/admin/js/offcanvas.js') }}"></script>
    <script>
        "use strict";

        // offcanvas.js only slides the panel open; the form itself is pulled in
        // here so add and edit can share one panel.
        $(document).on('click', '.offcanvas-trigger', function () {
            let action = $(this).data('action');
            let url = action === 'edit' ? $(this).data('url') : '{{ route('admin.vehicle.create') }}';
            fetch_form(url);
        });

        $(document).on('click', '.offcanvas-close, #offcanvasOverlay', function () {
            $('.custom-offcanvas').removeClass('open');
            $('#offcanvasOverlay').removeClass('show');
        });

        function fetch_form(url) {
            $.ajax({
                url: url,
                type: 'get',
                beforeSend: function () {
                    $('#data-view').empty();
                    $('#loading').show();
                },
                success: function (data) {
                    $('#data-view').append(data.view);
                },
                complete: function () {
                    $('#loading').hide();
                }
            });
        }

        // The language tabs live inside the injected markup, so they need a
        // delegated handler instead of the page level one.
        $(document).on('click', '#data-view .lang_link', function (e) {
            e.preventDefault();
            let lang = this.id.replace('-link', '');
            $('#data-view .lang_link').removeClass('active');
            $(this).addClass('active');
            $('#data-view .lang_form').addClass('d-none');
            $('#data-view').find('#' + lang + '-form').removeClass('d-none');
        });

        $(document).on('submit', '#vehicle-offcanvas-form', function (e) {
            e.preventDefault();
            let form = $(this);
            let submitBtn = form.find('button[type="submit"]');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: form.attr('action'),
                data: new FormData(this),
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    submitBtn.prop('disabled', true);
                },
                success: function (data) {
                    if (data.errors) {
                        submitBtn.prop('disabled', false);
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        toastr.success(data.success, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    }
                },
                error: function () {
                    submitBtn.prop('disabled', false);
                }
            });
        });
    </script>
@endpush
