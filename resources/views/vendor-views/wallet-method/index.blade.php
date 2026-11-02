@extends('layouts.vendor.app')

@section('title', translate('messages.payment_information'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        @include('vendor-views.partials.business_setup_header')
        <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-warning mb-3" style="--bs-bg-opacity: 0.1;">
            <span class="text-warning lh-1 fs-14">
                <i class="tio-info"></i>
            </span>
            <span>
                {{ translate('messages.When you add or edit payment info please make sure all data are correct. Other wise you don’t receive any payment.') }}
            </span>
        </div>

        <!-- Card -->
        <div class="card">
            <div class="card-header flex-wrap gap-2 border-0 pt-2 pb-0">
                <div class="search--button-wrapper flex-wrap gap-2">
                    <h3 class="card-title d-flex gap-1">
                        {{ translate('Payment_Methods_List') }}
                        <span class="badge badge-soft-secondary"
                            id="countfoods">{{ $vendor_withdrawal_methods->total() }}</span>
                    </h3>
                    <form>
                        <!-- Search -->
                        <div class="input-group input--group flex-nowrap">
                            <input id="datatableSearch_" type="search" name="search"
                                class="form-control w-260 w-100-mobile"
                                placeholder="{{ translate('Search by payment method name') }}"
                                value="{{ request()?->search ?? null }}" aria-label="Search">
                            <button type="submit" class="btn btn--secondary">
                                <i class="tio-search"></i>
                            </button>
                        </div>
                        <!-- End Search -->
                    </form>
                </div>
                <div class="p--10px">
                    <a class="btn btn--primary btn-outline-primary w-100 offcanvas-trigger" href="javascript:"
                        data-toggle="offcanvas" data-target="#balance-modal">{{ translate('messages.add_new_method') }}</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="datatable"
                        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                        data-hs-datatables-options='{
                            "order": [],
                            "orderCellsTop": true,
                            "paging":false
                        }'>
                        <thead class="thead-light">
                            <tr>
                                <th>{{ translate('messages.sl') }}</th>
                                <th>{{ translate('messages.Payment_method_name') }}</th>
                                <th>{{ translate('messages.Payment_Info') }}</th>
                                <th class="text-center">{{ translate('messages.status') }}</th>
                                <th class="text-center">{{ translate('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="set-rows">
                            @foreach ($vendor_withdrawal_methods as $key => $withdrawal_method)
                                <tr>
                                    <td class="p-3">{{ $vendor_withdrawal_methods->firstitem() + $key }}</td>
                                    <td class="p-3">
                                        {{ $withdrawal_method['method_name'] }}
                                        @if ($withdrawal_method['is_default'] == 1)
                                            <span class="badge badge-soft-success badge-pill ml-1">{{ translate('Default') }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        <div class="more-withdraw-list">
                                            <div class="more-withdraw-inner d-flex flex-column gap-1">
                                                @foreach (array_slice(json_decode($withdrawal_method['method_fields'], true), 0, 3) as $key => $method_field)
                                                    <span class="text-title more-withdraw-item fs-14">
                                                        <span class="mb-1 d-flex gap-2">
                                                            <span class="min-w-120px">{{ translate($key) }}</span>
                                                            <span class="gray-dark d-flex gap-2">:
                                                                {{ $method_field ?? translate('N/A') }}</span>
                                                        </span>
                                                    </span>
                                                @endforeach
                                            </div>
                                            @if (count(json_decode($withdrawal_method['method_fields'], true)) > 1)
                                                <button type="button"
                                                    class="see__more btn p-0 border-0 bg-transparent text-primary fs-12 font-medium offcanvas-trigger"
                                                    data-target="#withdraw_method-offcanvas"
                                                    data-id="{{ $withdrawal_method->id }}"
                                                    data-name="{{ $withdrawal_method['method_name'] }}"
                                                    data-is_default="{{ $withdrawal_method['is_default'] == 1 ? 'Default' : '' }}"
                                                    data-is_active="{{ $withdrawal_method['is_active'] }}"
                                                    data-action="{{ route('vendor.wallet-method.edit', $withdrawal_method->id) }}"
                                                    data-fields='{{ $withdrawal_method['method_fields'] }}'
                                                    data-created_at="{{ \App\CentralLogics\Helpers::time_date_format($withdrawal_method->created_at) }}"
                                                    data-updated_at="{{ \App\CentralLogics\Helpers::time_date_format($withdrawal_method->updated_at) }}">
                                                    {{ translate('See More') }}
                                                </button>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="text-center p-3">
                                        <label class="toggle-switch mx-auto toggle-switch-sm js-status-toggle"
                                            data-url="{{ route('vendor.wallet-method.status-update') }}"
                                            data-id="{{ $withdrawal_method->id }}"
                                            data-title-on="{{ translate('Do You Want to ' . $withdrawal_method['method_name'] . ' Status ON') }}"
                                            data-title-off="{{ translate('Do You Want to ' . $withdrawal_method['method_name'] . ' Status OFF') }}"
                                            data-text-on="{{ translate('If you turn status on for ' . $withdrawal_method['method_name'] . ' it will show in withdraw methods dropdown list') }}"
                                            data-text-off="{{ translate('If you turn status off for ' . $withdrawal_method['method_name'] . ' it will not show in withdraw methods dropdown list') }}"
                                            data-image-on="{{ dynamicAsset('assets/admin/img/status-on-off.png') }}"
                                            data-image-off="{{ dynamicAsset('assets/admin/img/status-on-off.png') }}"
                                            data-button-on="{{ translate('Yes') }}"
                                            data-button-off="{{ translate('No') }}">

                                            <input class="toggle-switch-input" type="checkbox"
                                                {{ $withdrawal_method->is_active ? 'checked' : '' }}>

                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>

                                    <td class="p-3">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <div class="dropdown dropdown-2 hover-gray">
                                                <button type="button"
                                                    class="bg-transparent border rounded px-2 py-1 title-color"
                                                    data-toggle="dropdown" aria-expanded="false">
                                                    <i class="tio-more-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu" dir="ltr">
                                                    <a class="dropdown-item d-flex gap-2 align-items-center default-method {{ $withdrawal_method->is_default ? 'disabled' : '' }}"
                                                        data-id="{{ $withdrawal_method->id }}"
                                                        href="{{ $withdrawal_method->is_default ? 'javascript:void(0)' : 'javascript:' }}"
                                                        aria-disabled="{{ $withdrawal_method->is_default ? 'true' : 'false' }}">
                                                        <i class="tio-checkmark-circle-outlined"></i>
                                                        {{ $withdrawal_method->is_default ? translate('messages.Default') : translate('messages.Mark As Default') }}
                                                    </a>

                                                    <a href="javascript:"
                                                        data-url="{{ route('vendor.wallet-method.edit', [$withdrawal_method->id]) }}"
                                                        data-id="{{ $withdrawal_method->id }}"
                                                        data-target="#withdraw_method_edit-offcanvas"
                                                        class="dropdown-item d-flex gap-2 align-items-center offcanvas-trigger offcanvas-trigger-edit">
                                                        <i class="tio-edit"></i>
                                                        {{ translate('messages.Edit info') }}
                                                    </a>
                                                    <a class="dropdown-item d-flex gap-2 align-items-center see__more offcanvas-trigger"
                                                        data-target="#withdraw_method-offcanvas"
                                                        data-id="{{ $withdrawal_method->id }}"
                                                        data-name="{{ $withdrawal_method['method_name'] }}"
                                                        data-is_default="{{ $withdrawal_method['is_default'] == 1 ? 'Default' : '' }}"
                                                        data-is_active="{{ $withdrawal_method['is_active'] }}"
                                                        data-action="{{ route('vendor.wallet-method.edit', $withdrawal_method->id) }}"
                                                        data-fields='{{ $withdrawal_method['method_fields'] }}'
                                                        data-created_at="{{ \App\CentralLogics\Helpers::time_date_format($withdrawal_method->created_at) }}"
                                                        data-updated_at="{{ \App\CentralLogics\Helpers::time_date_format($withdrawal_method->updated_at) }}"
                                                        href="javascript:;">
                                                        <i class="tio-visible-outlined"></i>
                                                        {{ translate('messages.View') }}
                                                    </a>

                                                    @if (!$withdrawal_method->is_default)
                                                        <a class="dropdown-item d-flex gap-2 align-items-center form-alert"
                                                            href="javascript:" title="{{ translate('messages.Delete') }}"
                                                            data-id="delete-{{ $withdrawal_method->id }}"
                                                            data-message="{{ translate('Want to delete this item') }}">
                                                            <i class="tio-delete-outlined"></i>
                                                            {{ translate('messages.Remove') }}
                                                        </a>
                                                        <form
                                                            action="{{ route('vendor.wallet-method.delete', [$withdrawal_method->id]) }}"
                                                            method="post" id="delete-{{ $withdrawal_method->id }}">
                                                            @csrf @method('delete')
                                                        </form>
                                                    @endif

                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @if (count($vendor_withdrawal_methods) === 0)
                                <tr>
                                    <td colspan="5">
                                        <div class="empty--data py-9">
                                            <img src="{{ dynamicAsset('assets/admin/img/no-payment-method.png') }}"
                                                alt="public">
                                            <div class="fs-16 mt-3">
                                                {{ translate('messages.No Payment Method added yet') }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="page-area">
                    <table>
                        <tfoot>
                            {!! $vendor_withdrawal_methods->links() !!}
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <!-- Card -->

        <div class="modal fade" id="toggle-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog status-warning-modal modal-dialog-centered" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" class="tio-clear"></span>
                        </button>
                    </div>

                    <div class="modal-body pb-5 pt-0">
                        <div class="max-349 mx-auto mb-20">
                            <div class="text-center">
                                <img id="toggle-image" alt="" class="mb-20">
                                <h5 class="modal-title" id="toggle-title"></h5>
                            </div>

                            <div class="text-center" id="toggle-message"></div>

                            <form id="toggle-modal-form" method="POST" action="">
                                @csrf
                                <input type="hidden" name="id" id="toggle-modal-id" value="">
                            </form>

                            <div class="btn--container justify-content-center mt-3">
                                <button type="button" class="btn btn--reset min-w-120" data-dismiss="modal">
                                    {{ translate('No') }}
                                </button>
                                <button type="submit" form="toggle-modal-form" class="btn btn--primary min-w-120">
                                    {{ translate('Yes') }}
                                </button>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Add Payment info Offcanvas -->
        <form action="{{ route('vendor.wallet-method.store') }}" method="post">
            @csrf
            <div id="balance-modal" class="custom-offcanvas d-flex flex-column justify-content-between"
                style="--offcanvas-width: 500px">
                <div>
                    <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                        <div class="px-3 py-3 d-flex justify-content-between w-100">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <h2 class="mb-0 fs-18 text-title font-medium">
                                    {{ translate('messages.Add Payment Info') }}
                                </h2>
                            </div>
                            <button type="button"
                                class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                                aria-label="Close">&times;
                            </button>
                        </div>
                    </div>

                    <div class="custom-offcanvas-body p-20">
                        {{-- <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-warning mb-20" style="--bs-bg-opacity: 0.1;">
                        <span class="text-warning lh-1 fs-14">
                            <i class="tio-info"></i>
                        </span>
                        <span>
                            {{ translate('messages.If you turn on The Status, this payment will show in dropdown list when withdraw request sent to admin.') }}
                        </span>
                    </div> --}}
                        <div class="__bg-F8F9FC-card min-h-100vh-260">
                            <div>
                                <label class="input-label">
                                    {{ translate('Select_payment_Method') }}
                                </label>
                                <select class="form-control" id="withdraw_method" name="withdraw_method" required>
                                    <option value="" selected disabled>{{ translate('Select_Withdraw_Method') }}
                                    </option>
                                    @foreach ($withdrawal_methods as $item)
                                        <option value="{{ $item['id'] }}">{{ $item['method_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="" id="method-filed__div">
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="offcanvas-footer p-3 position-sticky bottom-0 bg-white  d-flex gap-3 align-items-center justify-content-center">
                    <button type="reset" class="btn btn--reset w-100" id="reset_button"
                        data-dismiss="modal">{{ translate('messages.Reset') }}</button>
                    <button type="submit" id="submit_button" disabled
                        class="btn btn--primary w-100">{{ translate('messages.Save') }}</button>
                </div>
            </div>
        </form>

        <!-- Saved Address Offcanvas -->
        <div id="withdraw_method-offcanvas" class="custom-offcanvas d-flex flex-column justify-content-between"
            style="--offcanvas-width: 500px">
            <div>
                <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                    <div class="px-3 py-3 d-flex justify-content-between w-100">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <h2 class="mb-0 fs-18 text-title font-medium">{{ translate('Withdrawal Method Information') }}
                            </h2>
                        </div>
                        <button type="button"
                            class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                            aria-label="Close">&times;
                        </button>
                    </div>
                </div>

                <div class="custom-offcanvas-body p-20">
                    <div class="d-flex flex-column gap-20px">
                        <div class="global-bg-box p-10px rounded mb-3">
                            <div class="bg-white rounded-8 border p-xxl-3 p-2">
                                <div class="d-flex justify-content-between gap-2 flex-wrap mb-3">
                                    <div>
                                        <h5 class="text-secondary fw-400 mb-1 fs-12">
                                            {{ translate('Method Name') }}
                                        </h5>
                                        <h5 class="text-title mb-0 fs-16">
                                            <span id="method-title"></span> <span id="method-is-default"></span>
                                        </h5>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div
                                            class="border rounded d-flex py-1 px-2 gap-4 justify-content-between align-items-center">
                                            <h4 class="text-capitalize mb-0 fs-12">{{ translate('messages.Status') }}</h4>
                                            <label class="toggle-switch mx-auto toggle-switch-sm">
                                                <input type="checkbox" id="offcanvas-status"
                                                    class="toggle-switch-input status js-status-toggle"
                                                    data-url="{{ route('vendor.wallet-method.status-update') }}"
                                                    data-id="" data-title-on="" data-title-off="" data-text-on=""
                                                    data-text-off="" data-image-on="" data-image-off=""
                                                    data-button-on="{{ translate('Yes') }}"
                                                    data-button-off="{{ translate('No') }}">

                                                <span class="toggle-switch-label text">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </div>
                                        <a class="btn btn-sm btn--danger btn-outline-danger action-btn offcanvas-delete-btn"
                                            href="javascript:" data-id="" title="{{ translate('messages.Delete') }}"
                                            style="display:none;">
                                            <i class="tio-delete-outlined"></i>
                                        </a>
                                    </div>
                                </div>
                                <p class="text-secondary fs-12 m-0">{{ translate('Created At') }} : <span
                                        id="method-created-at"></span></p>
                                <p class="text-secondary fs-12 m-0">{{ translate('Last Modified At') }} : <span
                                        id="method-updated-at"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-20px" id="method-fields-container">
                    </div>
                </div>
            </div>

            <div
                class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3 position-sticky">
                <a href="#" id="editMethodBtn" data-target="#withdraw_method_edit-offcanvas"
                    class="btn w-100 btn--secondary offcanvas-trigger offcanvas-trigger-edit">{{ translate('Edit Method') }}</a>
                <a href="#" id="mark" class="btn w-100 btn--primary"
                    style="display:none;">{{ translate('Mark As Default') }}</a>
            </div>
        </div>

        <div id="withdraw_method_edit-offcanvas" class="custom-offcanvas d-flex flex-column justify-content-between"
            style="--offcanvas-width: 500px">
            <div>
                <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                    <div class="px-3 py-3 d-flex justify-content-between w-100">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <h2 class="mb-0 fs-18 text-title font-medium">{{ translate('Update Withdrawal Method') }}</h2>
                        </div>
                        <button type="button"
                            class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                            aria-label="Close">&times;
                        </button>
                    </div>
                </div>

                <div id="data-view"> </div>
            </div>
        </div>

        <div id="offcanvasOverlay" class="offcanvas-overlay"></div>

    </div>
@endsection
@push('script')
    <script>
        "use strict";

        $('#withdraw_method').on('change', function() {
            $('#submit_button').attr("disabled", "true");
            let method_id = this.value;

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: "{{ route('vendor.wallet.method-list') }}" + "?method_id=" + method_id,
                data: {},
                processData: false,
                contentType: false,
                type: 'get',
                success: function(response) {
                    $('#submit_button').removeAttr('disabled');
                    let method_fields = response.content.method_fields;
                    $("#method-filed__div").html("");
                    method_fields.forEach((element) => {
                        $("#method-filed__div").append(`
                    <div class="form-group mt-3 mb-0">
                        <label class="input-label">${element.input_name.replaceAll('_', ' ')}</label>
                        <input type="${element.input_type == 'phone' ? 'number' : element.input_type}"
                               class="form-control"
                               name="${element.input_name}"
                               placeholder="${element.placeholder}"
                               ${element.is_required === 1 ? 'required' : ''}>
                    </div>
                `);
                    });
                }
            });
        });

        $(document).on('click', '.default-method', function(e) {
            if ($(this).hasClass('disabled') || $(this).attr('aria-disabled') === 'true') {
                e.preventDefault();
                return;
            }
            updateDefault($(this).data("id"));
        });

        $(document).on('click', '.see__more', function() {
            let methodId = $(this).data('id');
            let methodName = $(this).data('name');
            let isActive = $(this).data('is_active');
            let isDefault = $(this).data('is_default');
            let action = $(this).data('action');
            let fields = $(this).data('fields');
            let createdAt = $(this).data('created_at');
            let updatedAt = $(this).data('updated_at');

            function formatText(text) {
                if (!text) return '';
                return text.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
            }

            $('#offcanvas-status')
                .prop('checked', Number(isActive) === 1)
                .attr('data-id', methodId)
                .attr('data-title-on', 'Do You Want to ' + methodName + ' Status ON')
                .attr('data-title-off', 'Do You Want to ' + methodName + ' Status OFF')
                .attr('data-text-on', 'If you turn status on for ' + methodName +
                    ' it will show in withdraw methods dropdown list')
                .attr('data-text-off', 'If you turn status off for ' + methodName +
                    ' it will not show in withdraw methods dropdown list')
                .attr('data-image-on', "{{ dynamicAsset('assets/admin/img/status-on-off.png') }}")
                .attr('data-image-off', "{{ dynamicAsset('assets/admin/img/status-on-off.png') }}");

            var isDefaultBool = (String(isDefault).toLowerCase() === 'default');

            if (!isDefaultBool) {
                $('.offcanvas-delete-btn').data('id', methodId).show();
                $('#mark').show();
                $('#method-is-default').text('');
            } else {
                $('#method-is-default').text('(' + formatText(isDefault) + ')');
                $('.offcanvas-delete-btn').hide();
                $('#mark').hide();
            }

            $('#method-title').text(formatText(methodName));
            $('#method-created-at').text(createdAt || '');
            $('#method-updated-at').text(updatedAt || '');

            $('#method-fields-container').empty();
            $.each(fields, function(index, field) {
                let inputName = formatText(index);
                let inputType = formatText(field);

                $('#method-fields-container').append(`
            <div class="global-bg-box p-10px rounded">
                <div class="d-flex align-items-cetner justify-content-between gap-2 flex-wrap mb-10px">
                    <h5 class="text-title m-0 d-flex gap-2">${inputName}</h5>
                </div>
                <div class="bg-white rounded p-10px d-flex flex-column gap-1">
                    <div class="d-flex gap-2">
                        <span class="fs-14 text-title">${inputType}</span>
                    </div>
                </div>
            </div>
        `);
            });

            $('#editMethodBtn').attr('data-url', action);
            $('#withdraw_method-offcanvas').addClass('show');
        });

        $(document).on('click', '.offcanvas-delete-btn', function() {
            let id = $(this).data('id');
            let message = '{{ translate('Want to delete this item') }}';

            Swal.fire({
                title: '{{ translate('messages.Are you sure?') }}',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ translate('messages.no') }}',
                confirmButtonText: '{{ translate('messages.Yes') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ route('vendor.wallet-method.delete', 0) }}".replace(/0$/, id),
                        method: 'POST',
                        data: {
                            _method: 'delete'
                        },
                        success: function() {
                            toastr.success(
                                '{{ translate('messages.withdraw_method_deleted_successfully') ?? 'Withdraw method deleted successfully' }}'
                                );
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        },
                        error: function() {
                            toastr.error(
                                '{{ translate('messages.withdraw_method_delete_failed') ?? 'Withdraw method delete failed' }}'
                                );
                        }
                    });
                }
            });
        });

        $(document).on('click', '#mark', function(e) {
            e.preventDefault();
            updateDefault($('#offcanvas-status').attr('data-id'));
        });

        function updateDefault(id) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ route('vendor.wallet-method.default-status-update') }}",
                method: 'POST',
                data: {
                    id: id
                },
                success: function(data) {
                    if (data.success == true) toastr.success(data.message);
                    else toastr.error(data.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            });
        }

        $(document).on('click', '.offcanvas-trigger-edit', function() {
            let url = $(this).data('url');
            $('#withdraw_method_edit-offcanvas').addClass('show');
            fetch_data(url);
        });

        function fetch_data(url) {
            $.ajax({
                url: url,
                type: "get",
                beforeSend: function() {
                    $('#data-view').empty();
                    $('#loading').show();
                },
                success: function(data) {
                    if (data.error) {
                        offcanvas_close();
                        toastr.error(data.message);
                    } else {
                        $("#data-view").append(data.view);
                    }
                },
                complete: function() {
                    $('#loading').hide();
                }
            });
        }

        $('#reset_button').on('click', function() {
            $('#withdraw_method').prop('selectedIndex', 0).trigger('change');
            $('#method-filed__div').empty();
            $('#submit_button').attr('disabled', true);
        });

        var pending = false;
        var prevChecked = false;
        var $currentCheckbox = null;
        var $currentDataSrc = null;

        $(document).on('click', '.js-status-toggle', function(e) {
            e.preventDefault();
            if (pending) return;

            var $el = $(this);

            if ($el.is('input[type="checkbox"]')) {
                $currentCheckbox = $el;
                $currentDataSrc = $el;
            } else {
                $currentCheckbox = $el.find('input.toggle-switch-input');
                $currentDataSrc = $el;
            }

            prevChecked = $currentCheckbox.is(':checked');
            var willBeOn = $currentDataSrc.is('#offcanvas-status') ? prevChecked : !prevChecked;


            $('#toggle-title').text(willBeOn ? ($currentDataSrc.data('title-on') || '') : ($currentDataSrc.data(
                'title-off') || ''));
            $('#toggle-message').text(willBeOn ? ($currentDataSrc.data('text-on') || '') : ($currentDataSrc.data(
                'text-off') || ''));

            var img = willBeOn ? $currentDataSrc.data('image-on') : $currentDataSrc.data('image-off');
            if (img) $('#toggle-image').attr('src', img).show();
            else $('#toggle-image').hide();

            $('#toggle-modal-form').attr('action', $currentDataSrc.data('url'));
            $('#toggle-modal-id').val($currentDataSrc.data('id'));

            $('#toggle-modal').modal('show');
        });

        $(document).on('submit', '#toggle-modal-form', function(e) {
            e.preventDefault();
            if (pending) return;

            var url = $(this).attr('action');
            var id = $('#toggle-modal-id').val();
            var token = $(this).find('input[name="_token"]').val();

            pending = true;

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: token,
                    id: id
                },
                success: function(res) {
                    $('#toggle-modal').modal('hide');

                    if (res && Number(res.success) === 1) {
                        if ($currentCheckbox) $currentCheckbox.prop('checked', !prevChecked);
                        if (window.toastr) toastr.success(res.message || 'Status updated');
                        setTimeout(function() {
                            window.location.reload();
                        }, 800);
                    } else {
                        if ($currentCheckbox) $currentCheckbox.prop('checked', prevChecked);
                        if (window.toastr) toastr.error((res && res.message) ? res.message : 'Failed');
                    }
                },
                error: function(xhr) {
                    if ($currentCheckbox) $currentCheckbox.prop('checked', prevChecked);
                    $('#toggle-modal').modal('hide');

                    var msg = 'Something went wrong!';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;

                    if (window.toastr) toastr.error(msg);
                },
                complete: function() {
                    pending = false;
                }
            });
        });
    </script>
@endpush
