@extends('layouts.admin.app')

@section('title', translate('messages.Free_Delivery'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/admin-shared.css') }}?v={{ @filemtime(public_path('assets/admin/css/admin-shared.css')) }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/free-delivery.css') }}?v={{ @filemtime(public_path('assets/admin/css/free-delivery.css')) }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{ translate('messages.Free_Delivery') }}</h1>
                </div>
            </div>
        </div>

        <div class="admin-alert mb-3">
            <span class="admin-alert__icon">i</span>
            <span>{{ translate('messages.Each_zone_can_hold_one_free_delivery_setup._To_change_the_terms_of_a_zone_that_already_has_one,_edit_it_instead_of_creating_another.') }}</span>
        </div>

        @if ($freeDeliveries->total() === 0 && !request()->has('search'))
            <div class="card">
                <div class="card-body empty-state">
                    <img class="empty-state__icon" src="{{ dynamicAsset('assets/admin/img/price-emty.png') }}"
                        alt="{{ translate('messages.Free_Delivery') }}">
                    <h5 class="empty-state__title">{{ translate('messages.Currently_You_Dont_Have_Any_Free_Delivery') }}
                    </h5>
                    <p class="empty-state__text">
                        {{ translate('messages.To_enable_free_delivery,_you_must_create_at_least_one_Free_Delivery_setup._In_this_page_you_see_all_the_free_delivery_you_added.') }}
                    </p>
                    <a href="{{ route('admin.free-delivery.create') }}" class="btn btn--primary">
                        <i class="tio-add-circle-outlined"></i> {{ translate('messages.Add_New') }}
                    </a>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header py-2 border-0">
                    <div class="search--button-wrapper">
                        <h5 class="card-title">
                            {{ translate('messages.Free_Delivery_List') }}
                            <span class="badge badge-soft-dark ml-2">{{ $freeDeliveries->total() }}</span>
                        </h5>

                        <form>
                            <div class="input--group input-group input-group-merge input-group-flush">
                                <input type="search" name="search" class="form-control"
                                    value="{{ request()?->search ?? null }}"
                                    placeholder="{{ translate('messages.Search_here') }}" required>
                                <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                            </div>
                        </form>

                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn export--btn btn-outline-primary btn--primary font--sm"
                                href="javascript:;"
                                data-hs-unfold-options='{"target": "#freeDeliveryExportDropdown","type": "css-animation"}'>
                                <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                            </a>
                            <div id="freeDeliveryExportDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                                <a class="dropdown-item"
                                    href="{{ route('admin.free-delivery.export', ['type' => 'excel', 'search' => request()->search]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg" alt="">
                                    {{ translate('messages.excel') }}
                                </a>
                                <a class="dropdown-item"
                                    href="{{ route('admin.free-delivery.export', ['type' => 'csv', 'search' => request()->search]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                        alt="">
                                    {{ translate('messages.csv') }}
                                </a>
                            </div>
                        </div>

                        <a href="{{ route('admin.free-delivery.create') }}" class="btn btn--primary">
                            <i class="tio-add-circle-outlined"></i> {{ translate('messages.Add_New') }}
                        </a>
                    </div>
                </div>

                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{ translate('messages.SL') }}</th>
                                <th class="border-0">{{ translate('messages.Zone') }}</th>
                                <th class="border-0">
                                    <div class="min-w-180px">{{ translate('messages.Free_Delivery_Type') }}</div>
                                </th>
                                <th class="border-0">{{ translate('messages.Status') }}</th>
                                <th class="border-0 text-center">{{ translate('messages.Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($freeDeliveries as $key => $freeDelivery)
                                <tr>
                                    <td class="pl-4">{{ $key + $freeDeliveries->firstItem() }}</td>
                                    <td>{{ $freeDelivery->zone?->name ?? translate('messages.N/A') }}</td>
                                    <td>
                                        <div class="free-delivery-cell">
                                            <span class="free-delivery-cell__value">{{ $freeDelivery->typeLabel() }}</span>
                                            {{-- Only a criteria setup with an amount has a term worth stating;
                                                 the rest free every order, which the label already says. --}}
                                            @if (!$freeDelivery->coversAnyAmount())
                                                <span class="free-delivery-cell__meta">
                                                    {{ translate('messages.Minimum_Order_Amount') }} :
                                                    {{ \App\CentralLogics\Helpers::format_currency($freeDelivery->minimum_order_amount) }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        {{-- Both directions need confirming, so the toggle only opens a
                                             modal; the request is sent from there. --}}
                                        <label class="toggle-switch toggle-switch-sm mb-0">
                                            <input type="checkbox" class="toggle-switch-input free-delivery-status-toggle"
                                                {{ $freeDelivery->status ? 'checked' : '' }}
                                                data-id="{{ $freeDelivery->id }}"
                                                data-status="{{ $freeDelivery->status }}">
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--primary btn-outline-primary"
                                                href="{{ route('admin.free-delivery.edit', [$freeDelivery->id]) }}"
                                                title="{{ translate('messages.edit_free_delivery') }}">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger free-delivery-delete-btn"
                                                href="javascript:" data-id="free-delivery-{{ $freeDelivery->id }}"
                                                title="{{ translate('messages.delete_free_delivery') }}">
                                                <i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{ route('admin.free-delivery.delete', [$freeDelivery->id]) }}"
                                                method="post" id="free-delivery-{{ $freeDelivery->id }}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (count($freeDeliveries) !== 0)
                    <div class="page-area px-4 pb-3">{!! $freeDeliveries->withQueryString()->links() !!}</div>
                @else
                    <div class="empty--data">
                        <img src="{{ dynamicAsset('assets/admin/svg/illustrations/sorry.svg') }}" alt="">
                        <h5>{{ translate('messages.no_data_found') }}</h5>
                    </div>
                @endif
            </div>
        @endif
    </div>

    @include('admin-views.free-delivery.partials._status-modals')

    <div class="modal fade" id="free-delivery-delete-modal">
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
                            <img class="mb-20" src="{{ dynamicAsset('assets/admin/img/modal/delete-icon.png') }}" alt="">
                            <h5 class="modal-title mb-3">{{ translate('messages.Want_to_Delete_this_Free_Delivery?') }}
                            </h5>
                        </div>
                        <div class="text-center">
                            <p>{{ translate('messages.Are_you_sure_you_want_to_delete_this_free_delivery_&_remove_it_permanently?') }}
                            </p>
                        </div>
                        <div class="btn--container justify-content-center">
                            <button type="button" class="btn btn--reset min-w-120px"
                                data-dismiss="modal">{{ translate('messages.No') }}</button>
                            <button type="button" id="free-delivery-delete-confirm"
                                class="btn btn--danger min-w-120px">{{ translate('messages.Delete') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    @include('admin-views.free-delivery.partials._status-scripts')

    <script>
        "use strict";

        let pendingDeleteFormId = null;

        $(document).on('click', '.free-delivery-delete-btn', function () {
            pendingDeleteFormId = $(this).data('id');
            $('#free-delivery-delete-modal').modal('show');
        });

        $(document).on('click', '#free-delivery-delete-confirm', function () {
            if (pendingDeleteFormId) {
                $('#' + pendingDeleteFormId).submit();
            }
        });
    </script>
@endpush
