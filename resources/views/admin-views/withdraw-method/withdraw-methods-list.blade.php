@extends('layouts.admin.app')

@section('title', translate('messages.withdraw_method_list'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3">
            <div class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center gap-3 mb-3">
                <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                    {{ translate('messages.withdraw_method_list') }}
                    <span class="badge badge-soft-dark radius-50 fz-12 ml-1"> {{ $withdrawal_methods->total() }}</span>
                </h2>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="p-3">
                        <div class="row gy-1 align-items-center justify-content-between">

                            <div class="col-auto">
                                <form class="search-form">
                                    <div class="input-group input--group border rounded">
                                        <input id="datatableSearch" name="search" type="search"
                                            value="{{ $search }}" class="form-control border-0 h--40px"
                                            placeholder="{{ translate('messages.Search_Method_Name') }}"
                                            aria-label="{{ translate('messages.search_here') }}">
                                        <button type="submit" class="btn btn--reset w-auto px-2 py-2 h-40px min-w-35px"><i
                                                class="tio-search"></i></button>
                                    </div>
                                </form>
                            </div>
                            <div class="d-flex flex-wrap gap-lg-20 gap--10">
                                <a href="{{ route('admin.business-settings.withdraw-method.create') }}"
                                    class="btn px-lg-4 px-3 h-40 py-2 d-flex align-items-center justify-content-center fs-12 btn--primary"><i
                                        class="tio-add-circle mr-1"></i> {{ translate('messages.Add_method') }}</a>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive pt-0">
                        <table id="datatable"
                            class="table table-hover table-borderless table-thead-borderedless table-nowrap table-align-middle card-table w-100">
                            <thead class="global-bg-box thead-50 text-capitalize">
                                <tr>
                                    <th>{{ translate('messages.sl') }}</th>
                                    <th>{{ translate('messages.Payment_method_name') }}</th>
                                    <th>{{ translate('messages.method_fields') }}</th>
                                    <th class="text-center">{{ translate('messages.active_status') }}</th>
                                    <th class="text-center">{{ translate('messages.default_method') }}</th>
                                    <th class="text-center">{{ translate('messages.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($withdrawal_methods as $key => $withdrawal_method)
                                    <tr>
                                        <td class="p-3">{{ $withdrawal_methods->firstitem() + $key }}</td>
                                        <td class="p-3">{{ $withdrawal_method['method_name'] }}</td>


                                        <td class="p-3">

                                            <div class="more-withdraw-list">
                                                <div class="more-withdraw-inner">
                                                    @foreach (array_slice($withdrawal_method['method_fields'], 0, 3) as $key => $method_field)
                                                        <span class="text-title more-withdraw-item fs-14">
                                                            <span class="mb-1 d-inline-block px-1">
                                                                <span>{{ translate('messages.Name') }}:</span> <span
                                                                    class="gray-dark">{{ translate($method_field['input_name']) }}
                                                                </span>

                                                            </span>

                                                            <span class="mb-1 d-inline-block px-1">
                                                                <span>{{ translate('messages.Type') }}:</span> <span
                                                                    class="gray-dark">{{ translate($method_field['input_type']) }}
                                                                </span>
                                                            </span>
                                                            <span class="mb-1 d-inline-block px-1">
                                                                <span>{{ translate('messages.Placeholder') }}:</span> <span
                                                                    class="gray-dark">{{ $method_field['placeholder'] }}
                                                                </span>
                                                            </span>
                                                            <span
                                                                class="btn fs-10 py-1 px-2 lh-1 {{ $method_field['is_required'] ? 'bg-danger-opacity5' : 'badge--info' }}">
                                                                {{ $method_field['is_required'] ? translate('messages.Required') : translate('messages.Optional') }}
                                                            </span>
                                                        </span><br />
                                                    @endforeach
                                                </div>
                                                @if (count($withdrawal_method['method_fields']) > 3)
                                                    <button type="button"
                                                        class="see__more btn p-0 border-0 bg-transparent text-primary fs-12 font-medium offcanvas-trigger"
                                                        data-target="#withdraw_method-offcanvas"
                                                        data-id="{{ $withdrawal_method->id }}"
                                                        data-name="{{ $withdrawal_method['method_name'] }}"
                                                        data-is_default="{{ $withdrawal_method['is_default'] == 1 ? 'Default' : 'Not Default' }}"
                                                        data-is_active="{{ $withdrawal_method['is_active'] }}"
                                                        data-action="{{ route('admin.business-settings.withdraw-method.edit', $withdrawal_method->id) }}"
                                                        data-fields='@json($withdrawal_method['method_fields'])'
                                                        data-created_at="{{ \App\CentralLogics\Helpers::time_date_format($withdrawal_method->created_at) }}"
                                                        data-updated_at="{{ \App\CentralLogics\Helpers::time_date_format($withdrawal_method->updated_at) }}">
                                                        {{ translate('See More') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </td>



                                        <td class="text-center p-3">
                                            <label class="toggle-switch mx-auto toggle-switch-sm">
                                                <input class="toggle-switch-input status featured-status"
                                                    data-id="{{ $withdrawal_method->id }}" type="checkbox"
                                                    {{ $withdrawal_method->is_active ? 'checked' : '' }}>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </td>
                                        <td class="text-center p-3">
                                            <label class="toggle-switch mx-auto toggle-switch-sm">
                                                <input type="checkbox" class="default-method toggle-switch-input"
                                                    id="{{ $withdrawal_method->id }}"
                                                    {{ $withdrawal_method->is_default == 1 ? 'checked' : '' }}>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </td>



                                        <td class="p-3">
                                            <div class="btn--container justify-content-center">
                                                <a class="btn action-btn btn--primary btn-outline-primary see__more offcanvas-trigger"
                                                    data-target="#withdraw_method-offcanvas"
                                                    data-id="{{ $withdrawal_method->id }}"
                                                    data-name="{{ $withdrawal_method['method_name'] }}"
                                                    data-is_default="{{ $withdrawal_method['is_default'] == 1 ? 'Default' : 'Not Default' }}"
                                                    data-is_active="{{ $withdrawal_method['is_active'] }}"
                                                    data-action="{{ route('admin.business-settings.withdraw-method.edit', $withdrawal_method->id) }}"
                                                    data-fields='@json($withdrawal_method['method_fields'])'
                                                    data-created_at="{{ \App\CentralLogics\Helpers::time_date_format($withdrawal_method->created_at) }}"
                                                    data-updated_at="{{ \App\CentralLogics\Helpers::time_date_format($withdrawal_method->updated_at) }}"
                                                    href="javascript:;"><i class="tio-visible-outlined"></i></a>

                                                <a href="{{ route('admin.business-settings.withdraw-method.edit', [$withdrawal_method->id]) }}"
                                                    class="btn btn-sm btn--primary btn-outline-primary action-btn">
                                                    <i class="tio-edit"></i>
                                                </a>

                                                @if (!$withdrawal_method->is_default)
                                                    <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert"
                                                        href="javascript:" title="{{ translate('messages.Delete') }}"
                                                        data-id="delete-{{ $withdrawal_method->id }}"
                                                        data-message="{{ translate('Want to delete this method') }}">
                                                        <i class="tio-delete-outlined"></i>
                                                    </a>
                                                    <form
                                                        action="{{ route('admin.business-settings.withdraw-method.delete', [$withdrawal_method->id]) }}"
                                                        method="post" id="delete-{{ $withdrawal_method->id }}">
                                                        @csrf @method('delete')
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if (count($withdrawal_methods) == 0)
                            <div class="empty--data">
                                <img src="{{ dynamicAsset('assets/admin/img/empty.png') }}" alt="public">
                                <h5>
                                    {{ translate('no_data_found') }}
                                </h5>
                            </div>
                        @endif
                    </div>

                    <div class="table-responsive mt-4">
                        <div class="px-4 d-flex justify-content-center justify-content-md-end">
                            <!-- Pagination -->
                            {{ $withdrawal_methods->links() }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>



    <!-- Saved Address Offcanvas -->
    <div id="withdraw_method-offcanvas" class="custom-offcanvas d-flex flex-column justify-content-between"
        style="--offcanvas-width: 500px">
        <div>
            <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                <div class="px-3 py-3 d-flex justify-content-between w-100">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h2 class="mb-0 fs-18 text-title font-medium">{{ translate('Payment Method Information') }}</h2>
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
                                        <span id="method-title"></span> (<span id="method-is-default"></span>)
                                    </h5>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div
                                        class="border rounded d-flex py-1 px-2 gap-4 justify-content-between align-items-center">
                                        <h4 class="text-capitalize mb-0 fs-12">{{ translate('messages.Status') }}</h4>
                                        <label class="toggle-switch toggle-switch-sm">
                                            <input type="checkbox" id="offcanvas-status"
                                                class="status toggle-switch-input" data-id="">
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
            <a href="#" id="editMethodBtn" class="btn w-100 btn--secondary">{{ translate('Edit Method') }}</a>
            <a href="#" id="mark" class="btn w-100 btn--primary"
                style="display:none;">{{ translate('Mark As Default') }}</a>
        </div>
    </div>

    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>
@endsection


@push('script_2')
    <script>
        "use strict";

        $(document).on('change', '.default-method', function() {
            let id = $(this).attr("id");
            updateDefault(id);
        });

        $(document).on('click', '.featured-status', function() {
            let id = $(this).data('id');
            statusToggle(id);
        })

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
                return text
                    .replace(/_/g, ' ')
                    .toLowerCase()
                    .replace(/\b\w/g, char => char.toUpperCase());
            }

            // Set status toggle and delete button data-id
            $('#offcanvas-status').data('id', methodId).prop('checked', isActive == 1);
            var isDefaultBool = (isDefault.toLowerCase() === 'default');
            if (!isDefaultBool) {
                $('.offcanvas-delete-btn').data('id', methodId).show();
                $('#mark').show();
            } else {
                $('.offcanvas-delete-btn').hide();
                $('#mark').hide();
            }

            let statusBadge = isActive ?
                '<span class="btn fs-10 py-1 px-2 lh-1 badge--success">' + "Active" + '</span>' :
                '<span class="btn fs-10 py-1 px-2 lh-1 bg-danger-opacity5">' + "Inactive" + '</span>';

            $('#method-status').html(statusBadge);
            $('#method-title').text(formatText(methodName));
            $('#method-is-default').text(formatText(isDefault));
            $('#method-created-at').text(createdAt ? createdAt : '');
            $('#method-updated-at').text(updatedAt ? updatedAt : '');

            $('#method-fields-container').empty();

            $.each(fields, function(index, field) {
                let inputName = formatText(field.input_name);
                let inputType = formatText(field.input_type);
                let placeholder = formatText(field.placeholder);

                let requiredBadge = field.is_required ?
                    '<span class="btn fs-10 py-1 px-2 lh-1 bg-danger-opacity5">' + "Required" + '</span>' :
                    '<span class="btn fs-10 py-1 px-2 lh-1 badge--success">' + "Optional" + '</span>';

                let fieldHtml = `
            <div class="global-bg-box p-10px rounded">
                <div class="d-flex align-items-cetner justify-content-between gap-2 flex-wrap mb-10px">
                    <h5 class="text-title m-0 d-flex gap-2">${inputName} <span class="gap-2">${requiredBadge}</span></h5>
                </div>
                <div class="bg-white rounded p-10px d-flex flex-column gap-1">
                    <div class="d-flex gap-2">
                        <span class="before-info w-90px min-w-90 gray-dark fs-12">{{ translate('Type') }}</span>
                        <span class="fs-14 text-title">${inputType}</span>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="before-info w-90px min-w-90 gray-dark fs-12">Placeholder</span>
                        <span class="fs-14 text-title">${placeholder}</span>
                    </div>
                </div>
            </div>
        `;

                $('#method-fields-container').append(fieldHtml);
            });

            $('#editMethodBtn').attr('href', action);

            $('#withdraw_method-offcanvas').addClass('show');
        });

        // Offcanvas status toggle handler
        $(document).on('change', '#offcanvas-status', function() {
            let id = $(this).data('id');
            statusToggle(id);
        });


        function statusToggle(id) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ route('admin.business-settings.withdraw-method.status-update') }}",
                method: 'POST',
                data: {
                    id: id
                },
                success: function(data) {
                    if (data.success == true) {
                        toastr.success(data.message);
                    } else if (data.success == false) {
                        toastr.error(data.message);
                    }
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            });
        }



        // Offcanvas delete button handler
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
                        url: "{{ route('admin.business-settings.withdraw-method.delete', 0) }}"
                            .replace(/0$/, id),
                        method: 'POST',
                        data: {
                            _method: 'delete'
                        },
                        success: function(data) {
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

        // Offcanvas 'Mark As Default' button handler
        $(document).on('click', '#mark', function(e) {
            e.preventDefault();
            var id = $('#offcanvas-status').data('id');
            updateDefault(id);
        });


        function updateDefault(id) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ route('admin.business-settings.withdraw-method.default-status-update') }}",
                method: 'POST',
                data: {
                    id: id
                },
                success: function(data) {
                    if (data.success == true) {
                        toastr.success(data.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else if (data.success == false) {
                        toastr.error(data.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                }
            });
        }
    </script>
@endpush
