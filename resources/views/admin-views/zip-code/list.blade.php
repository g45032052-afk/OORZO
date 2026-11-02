@extends('layouts.admin.app')

@section('title', translate('messages.Zip_Code_Setup'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/admin-shared.css') }}?v={{ @filemtime(public_path('assets/admin/css/admin-shared.css')) }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{ translate('messages.Zip_Code_Setup') }}</h1>
                </div>
            </div>
        </div>

        {{-- The empty state takes over the whole card, but only when no code has
             ever been added — a search that matches nothing keeps the table so the
             search box stays reachable. --}}
        @if ($zipCodes->total() === 0 && !request()->has('search'))
            <div class="card">
                <div class="card-body empty-state">
                    <img class="empty-state__icon" src="{{ dynamicAsset('assets/admin/img/price-emty.png') }}"
                        alt="{{ translate('messages.Zip_Code_Setup') }}">
                    <h5 class="empty-state__title">
                        {{ translate('messages.Currently_You_Dont_Have_Any_ZIP_Code_Setup') }}</h5>
                    <p class="empty-state__text">
                        {{ translate('messages.To_setup_ZIP_code_under_a_zone_you_can_use_ZIP_code_wise_delivery_charge_when_customer_delivery_charges_calculated.') }}
                    </p>
                    <a href="javascript:void(0)" class="btn btn--primary offcanvas-trigger"
                        data-target="#offcanvas__zip_code" data-action="create">
                        <i class="tio-add-circle-outlined"></i> {{ translate('messages.Add_New') }}
                    </a>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header py-2 border-0">
                    <div class="search--button-wrapper">
                        <h5 class="card-title">
                            {{ translate('messages.Zip_Code_List') }}
                            <span class="badge badge-soft-dark ml-2" id="itemCount">{{ $zipCodes->total() }}</span>
                        </h5>

                        <form>
                            <div class="input--group input-group input-group-merge input-group-flush">
                                <input id="datatableSearch_" type="search" name="search" class="form-control"
                                    value="{{ request()?->search ?? null }}"
                                    placeholder="{{ translate('messages.Search_here') }}"
                                    aria-label="{{ translate('messages.search') }}" required>
                                <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                            </div>
                        </form>

                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn export--btn btn-outline-primary btn--primary font--sm"
                                href="javascript:;"
                                data-hs-unfold-options='{"target": "#zipCodeExportDropdown","type": "css-animation"}'>
                                <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                            </a>
                            <div id="zipCodeExportDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                                <a id="export-excel" class="dropdown-item"
                                    href="{{ route('admin.zip-code.export', ['type' => 'excel', 'search' => request()->search]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg" alt="">
                                    {{ translate('messages.excel') }}
                                </a>
                                <a id="export-csv" class="dropdown-item"
                                    href="{{ route('admin.zip-code.export', ['type' => 'csv', 'search' => request()->search]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                        alt="">
                                    {{ translate('messages.csv') }}
                                </a>
                            </div>
                        </div>

                        <a href="javascript:void(0)" class="btn btn--primary offcanvas-trigger"
                            data-target="#offcanvas__zip_code" data-action="create">
                            <i class="tio-add-circle-outlined"></i> {{ translate('messages.Add_New_Zip_Code') }}
                        </a>
                    </div>
                </div>

                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{ translate('messages.SL') }}</th>
                                <th class="border-0">{{ translate('messages.Zip_Code') }}</th>
                                <th class="border-0">{{ translate('messages.Zone_Name') }}</th>
                                <th class="border-0">{{ translate('messages.Status') }}</th>
                                <th class="border-0 text-center">{{ translate('messages.Action') }}</th>
                            </tr>
                        </thead>

                        <tbody id="set-rows">
                            @foreach ($zipCodes as $key => $zipCode)
                                <tr>
                                    <td class="pl-4">{{ $key + $zipCodes->firstItem() }}</td>
                                    <td><span class="text-title">{{ $zipCode->zip_code }}</span></td>
                                    <td>
                                        <span class="line--limit-2 max-w-220px"
                                            title="{{ $zipCode->zone?->name }}">{{ $zipCode->zone?->name ?? translate('messages.N/A') }}</span>
                                    </td>
                                    <td>
                                        {{-- Same confirm pipeline as Surge Price and Area Setup. --}}
                                        <label class="toggle-switch toggle-switch-sm" for="status-{{ $zipCode->id }}">
                                            <input type="checkbox" class="toggle-switch-input dynamic-checkbox"
                                                id="status-{{ $zipCode->id }}" {{ $zipCode->status ? 'checked' : '' }}
                                                data-id="status-{{ $zipCode->id }}" data-type="status"
                                                data-image-on="{{ dynamicAsset('assets/admin/img') }}/status-on-off.png"
                                                data-image-off="{{ dynamicAsset('assets/admin/img') }}/status-off.png"
                                                data-title-on="{{ translate('messages.Turn_On_The_Status?') }}"
                                                data-title-off="{{ translate('messages.Turn_Off_The_Status?') }}"
                                                data-text-on="<p>{{ translate('messages.Are_you_sure,_do_you_want_to_turn_on_the_Zip_Code_status_from_your_system.') }}</p>"
                                                data-text-off="<p>{{ translate('messages.Are_you_sure,_do_you_want_to_turn_off_the_Zip_Code_status_from_your_system.') }}</p>">
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                        <form
                                            action="{{ route('admin.zip-code.status', [$zipCode->id, $zipCode->status ? 0 : 1]) }}"
                                            method="get" id="status-{{ $zipCode->id }}_form"></form>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--primary btn-outline-primary offcanvas-trigger"
                                                href="javascript:" data-target="#offcanvas__zip_code" data-action="edit"
                                                data-url="{{ route('admin.zip-code.edit', [$zipCode->id]) }}"
                                                title="{{ translate('messages.edit_zip_code') }}">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger zip-code-delete-btn"
                                                href="javascript:" data-id="zip-code-{{ $zipCode->id }}"
                                                title="{{ translate('messages.delete_zip_code') }}">
                                                <i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{ route('admin.zip-code.delete', [$zipCode->id]) }}"
                                                method="post" id="zip-code-{{ $zipCode->id }}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (count($zipCodes) !== 0)
                    <div class="page-area px-4 pb-3">
                        {!! $zipCodes->withQueryString()->links() !!}
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

    <div id="offcanvas__zip_code" class="custom-offcanvas d-flex flex-column justify-content-between">
        <div id="data-view" class="h-100"></div>
    </div>
    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>

    {{-- The shared .form-alert helper renders a SweetAlert, which does not match
         the design, so deletion gets its own confirm modal. --}}
    <div class="modal fade" id="zip-code-delete-modal">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div class="text-center">
                            <img class="mb-20" src="{{ dynamicAsset('assets/admin/img/modal/delete-icon.png') }}"
                                alt="">
                            <h5 class="modal-title mb-3">{{ translate('messages.Want_to_Delete_this_Zip_Code?') }}</h5>
                        </div>
                        <div class="text-center">
                            <p>{{ translate('messages.Are_you_sure_you_want_to_delete_this_Zip_Code_&_remove_it_permanently?') }}
                            </p>
                        </div>
                        <div class="btn--container justify-content-center">
                            <button type="button" class="btn btn--reset min-w-120px"
                                data-dismiss="modal">{{ translate('messages.No') }}</button>
                            <button type="button" id="zip-code-delete-confirm"
                                class="btn btn--danger min-w-120px">{{ translate('messages.Delete') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="{{ dynamicAsset('assets/admin/js/offcanvas.js') }}"></script>
    <script>
        "use strict";

        // Deletion uses a page local confirm rather than the shared .form-alert
        // SweetAlert, matching Surge Price and Area Setup.
        let pendingDeleteFormId = null;

        $(document).on('click', '.zip-code-delete-btn', function () {
            pendingDeleteFormId = $(this).data('id');
            $('#zip-code-delete-modal').modal('show');
        });

        $(document).on('click', '#zip-code-delete-confirm', function () {
            if (pendingDeleteFormId) {
                $('#' + pendingDeleteFormId).submit();
            }
        });

        // offcanvas.js only slides the panel open; the form itself is pulled in
        // here so add and edit can share one panel.
        $(document).on('click', '.offcanvas-trigger', function () {
            let action = $(this).data('action');
            let url = action === 'edit' ? $(this).data('url') : '{{ route('admin.zip-code.create') }}';
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
                    // select2 has to be initialised after the markup lands.
                    $('#data-view .js-select2-custom').select2({
                        dropdownParent: $('#offcanvas__zip_code'),
                        placeholder: '{{ translate('messages.Select_zone') }}',
                    });
                },
                complete: function () {
                    $('#loading').hide();
                }
            });
        }

        $(document).on('submit', '#zip-code-offcanvas-form', function (e) {
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
