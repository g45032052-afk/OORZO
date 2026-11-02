@extends('layouts.admin.app')

@section('title', translate('messages.Additional_Charge'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/admin-shared.css') }}?v={{ @filemtime(public_path('assets/admin/css/admin-shared.css')) }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/additional-charge.css') }}?v={{ @filemtime(public_path('assets/admin/css/additional-charge.css')) }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{ translate('messages.Additional_Charge') }}</h1>
                </div>
            </div>
        </div>

        <div class="admin-alert mb-3">
            <span class="admin-alert__icon">i</span>
            <span>{{ translate('messages.Each_zone_holds_one_Additional_Delivery_Charge_setup._Turn_it_on_to_offer_Express_and_Slightly_Delay_delivery_in_that_zone.') }}</span>
        </div>

        @if ($zones->total() === 0 && !request()->has('search'))
            <div class="card">
                <div class="card-body empty-state">
                    <img class="empty-state__icon" src="{{ dynamicAsset('assets/admin/img/price-emty.png') }}"
                        alt="{{ translate('messages.Additional_Charge') }}">
                    <h5 class="empty-state__title">
                        {{ translate('messages.Currently_You_Dont_Have_Any_Additional_Delivery_Charge') }}</h5>
                    <p class="empty-state__text">
                        {{ translate('messages.To_enable_Additional_Delivery_Charge,_you_must_create_at_least_Additional_Delivery_Charge._In_this_page_you_see_all_the_Additional_Delivery_Charge_you_added.') }}
                    </p>
                    <a href="{{ route('admin.additional-charge.create') }}" class="btn btn--primary">
                        <i class="tio-add-circle-outlined"></i> {{ translate('messages.Add_Now') }}
                    </a>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header py-2 border-0">
                    <div class="search--button-wrapper">
                        <h5 class="card-title">
                            {{ translate('messages.Additional_Charge_List') }}
                            <span class="badge badge-soft-dark ml-2">{{ $zones->total() }}</span>
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
                                data-hs-unfold-options='{"target": "#additionalChargeExportDropdown","type": "css-animation"}'>
                                <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                            </a>
                            <div id="additionalChargeExportDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                                <a class="dropdown-item"
                                    href="{{ route('admin.additional-charge.export', ['type' => 'excel', 'search' => request()->search]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg" alt="">
                                    {{ translate('messages.excel') }}
                                </a>
                                <a class="dropdown-item"
                                    href="{{ route('admin.additional-charge.export', ['type' => 'csv', 'search' => request()->search]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                        alt="">
                                    {{ translate('messages.csv') }}
                                </a>
                            </div>
                        </div>

                        <a href="{{ route('admin.additional-charge.create') }}" class="btn btn--primary">
                            <i class="tio-add-circle-outlined"></i> {{ translate('messages.Add_New') }}
                        </a>
                    </div>
                </div>

                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{ translate('messages.Zone') }}</th>
                                <th class="border-0">
                                    <div class="min-w-180px">{{ translate('messages.Express_Delivery') }}</div>
                                </th>
                                <th class="border-0">
                                    <div class="min-w-180px">{{ translate('messages.Slightly_Delay_Delivery') }}</div>
                                </th>
                                <th class="border-0">{{ translate('messages.Status') }}</th>
                                <th class="border-0 text-center">{{ translate('messages.Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($zones as $zone)
                                @php($express = $zone->deliveryOptions->firstWhere('delivery_type', 'express'))
                                @php($delayed = $zone->deliveryOptions->firstWhere('delivery_type', 'slightly_delay'))
                                <tr>
                                    <td>{{ $zone->name }}</td>
                                    <td>
                                        <div class="charge-cell">
                                            <span class="charge-cell__value">
                                                {{ translate('messages.Extra_Charge') }} :
                                                {{ \App\CentralLogics\Helpers::format_currency($express->extra_charge ?? 0) }}
                                            </span>
                                            <span class="charge-cell__meta">
                                                {{ translate('messages.Reduce_Delivery_Time') }} :
                                                {{ \App\CentralLogics\Helpers::deliveryOptionTimeLabel($express?->reduce_delivery_time) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="charge-cell">
                                            <span class="charge-cell__value">
                                                {{ translate('messages.Reduce_Charge') }} :
                                                {{ \App\CentralLogics\Helpers::format_currency($delayed->reduce_charge ?? 0) }}
                                            </span>
                                            <span class="charge-cell__meta">
                                                {{ translate('messages.Extra_Delivery_Time') }} :
                                                {{ \App\CentralLogics\Helpers::deliveryOptionTimeLabel($delayed?->add_delivery_time) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        {{-- Both directions need confirming, so the toggle only opens a
                                             modal; the request is sent from there. --}}
                                        <label class="toggle-switch toggle-switch-sm mb-0">
                                            <input type="checkbox" class="toggle-switch-input charge-status-toggle"
                                                {{ $zone->additional_delivery_option_status ? 'checked' : '' }}
                                                data-id="{{ $zone->id }}">
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--primary btn-outline-primary"
                                                href="{{ route('admin.additional-charge.edit', [$zone->id]) }}"
                                                title="{{ translate('messages.edit_additional_charge') }}">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger charge-delete-btn"
                                                href="javascript:" data-id="charge-{{ $zone->id }}"
                                                title="{{ translate('messages.delete_additional_charge') }}">
                                                <i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{ route('admin.additional-charge.delete', [$zone->id]) }}"
                                                method="post" id="charge-{{ $zone->id }}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (count($zones) !== 0)
                    <div class="page-area px-4 pb-3">{!! $zones->withQueryString()->links() !!}</div>
                @else
                    <div class="empty--data">
                        <img src="{{ dynamicAsset('assets/admin/svg/illustrations/sorry.svg') }}" alt="">
                        <h5>{{ translate('messages.no_data_found') }}</h5>
                    </div>
                @endif
            </div>
        @endif
    </div>

    @include('admin-views.additional-charge.partials._status-modals')

    <div class="modal fade" id="charge-delete-modal">
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
                            <h5 class="modal-title mb-3">
                                {{ translate('messages.Want_to_Delete_this_Additional_Charge?') }}</h5>
                        </div>
                        <div class="text-center">
                            <p>{{ translate('messages.Are_you_sure_you_want_to_delete_this_additional_charge_&_remove_it_permanently?') }}
                            </p>
                        </div>
                        <div class="btn--container justify-content-center">
                            <button type="button" class="btn btn--reset min-w-120px"
                                data-dismiss="modal">{{ translate('messages.No') }}</button>
                            <button type="button" id="charge-delete-confirm"
                                class="btn btn--danger min-w-120px">{{ translate('messages.Delete') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    @include('admin-views.additional-charge.partials._status-scripts')

    <script>
        "use strict";

        let pendingDeleteFormId = null;

        $(document).on('click', '.charge-delete-btn', function () {
            pendingDeleteFormId = $(this).data('id');
            $('#charge-delete-modal').modal('show');
        });

        $(document).on('click', '#charge-delete-confirm', function () {
            if (pendingDeleteFormId) {
                $('#' + pendingDeleteFormId).submit();
            }
        });
    </script>
@endpush
