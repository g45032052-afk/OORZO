@extends('layouts.admin.app')

@section('title', translate('messages.Delivery_Rule') . ' #' . $rule->id)

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/surge-price.css') }}?v={{ @filemtime(public_path('assets/admin/css/surge-price.css')) }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/delivery-rule.css') }}?v={{ @filemtime(public_path('assets/admin/css/delivery-rule.css')) }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h1 class="page-header-title mb-0">
                    {{ translate('messages.Delivery_Rule') }} #{{ $rule->id }}</h1>

                <div class="d-flex align-items-center gap-2">
                    {{-- A zone must always keep one active rule, so the active one
                         offers no delete. --}}
                    @if (!$rule->status)
                        <a href="javascript:" class="btn btn--danger rule-delete-btn" data-id="rule-{{ $rule->id }}">
                            <i class="tio-delete-outlined"></i> {{ translate('messages.Delete') }}
                        </a>
                        <form action="{{ route('admin.delivery-rule.delete', [$rule->id]) }}" method="post"
                            id="rule-{{ $rule->id }}">@csrf @method('delete')</form>
                    @endif

                    <span class="rule-status-pill">
                        {{ translate('messages.Status') }}
                        <label class="toggle-switch toggle-switch-sm mb-0 ml-2">
                            <input type="checkbox" class="toggle-switch-input rule-status-toggle"
                                {{ $rule->status ? 'checked' : '' }} data-id="{{ $rule->id }}"
                                data-status="{{ $rule->status }}" data-zone="{{ $rule->zone_id }}"
                                data-name="{{ $rule->name }}">
                            <span class="toggle-switch-label"><span class="toggle-switch-indicator"></span></span>
                        </label>
                    </span>

                    <a href="{{ route('admin.delivery-rule.edit', [$rule->id]) }}" class="btn btn--primary">
                        <i class="tio-edit"></i> {{ translate('messages.Edit') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="surge-section mb-20">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <h4 class="surge-section__title mb-1">{{ $rule->name }}</h4>
                            <p class="surge-section__subtitle mb-0">
                                {{ \App\CentralLogics\Helpers::date_format($rule->created_at) }}
                                {{ \App\CentralLogics\Helpers::time_format($rule->created_at) }}
                            </p>
                        </div>
                        <div class="col-md-8">
                            <div class="rule-summary">
                                <div>
                                    <span class="rule-summary__label">{{ translate('messages.Delivery_Zone') }}</span>
                                    <span class="rule-summary__value">{{ $rule->zone?->name ?? translate('messages.N/A') }}</span>
                                </div>
                                <div>
                                    <span class="rule-summary__label">{{ translate('messages.Delivery_Pricing_Method') }}</span>
                                    <span class="rule-summary__value">{{ $rule->methodLabel() }}</span>
                                </div>
                                @if ($rule->usesChargeTable())
                                    <div>
                                        <span class="rule-summary__label">
                                            {{ $rule->pricing_method === \App\Models\DeliveryRule::METHOD_AREA ? translate('messages.Total_Area') : translate('messages.Total_Zip_Code') }}
                                        </span>
                                        <span class="rule-summary__value">{{ $rule->charges->count() }}</span>
                                    </div>
                                @endif
                                <div>
                                    <span class="rule-summary__label">{{ translate('messages.Minimum_Delivery_Charge') }}</span>
                                    <span class="rule-summary__value">
                                        {{ \App\CentralLogics\Helpers::format_currency($rule->minimum_delivery_charge) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        @php($isArea = $rule->pricing_method === \App\Models\DeliveryRule::METHOD_AREA)
                        <h5 class="surge-section__title">
                            @switch($rule->pricing_method)
                                @case(\App\Models\DeliveryRule::METHOD_AREA)
                                    {{ translate('messages.Area-wise_Delivery_Charges') }} @break
                                @case(\App\Models\DeliveryRule::METHOD_ZIP)
                                    {{ translate('messages.ZIP-code-wise_Delivery_Charges') }} @break
                                @case(\App\Models\DeliveryRule::METHOD_DISTANCE)
                                    {{ translate('messages.Distance-based_Delivery_Charges') }} @break
                                @default
                                    {{ translate('messages.Fixed_Delivery_Charge') }}
                            @endswitch
                        </h5>
                        <p class="surge-section__subtitle">
                            {{ $rule->pricing_method === \App\Models\DeliveryRule::METHOD_DISTANCE
                                ? translate('messages.Base_delivery_charges_configured_according_to_the_delivery_distance.')
                                : translate('messages.Base_delivery_charges_configured_for_this_rule.') }}
                        </p>
                        <div class="rule-hint">
                            <img src="{{ dynamicAsset('assets/admin/img/svg/bulb.svg') }}" class="svg" alt="">
                            {{-- Vehicle charges only: this platform has no weight or size charge
                                 to apply, so naming them promised a setting that does not exist. --}}
                            <span>{{ $rule->pricing_method === \App\Models\DeliveryRule::METHOD_DISTANCE
                                ? translate('messages.The_delivery_charge_is_calculated_using_the_configured_per-distance_rate,_minimum_charge,_and_maximum_charge._Additional_vehicle_charges_are_applied_separately_if_configured.')
                                : translate('messages.Customers_are_charged_the_configured_base_delivery_fee._Additional_vehicle_charges_are_applied_separately_if_configured.') }}</span>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="rule-charge-table">
                            <table class="rule-charge-table__table">
                                <thead>
                                    <tr>
                                        <th class="rule-charge-table__sl">{{ translate('messages.SL') }}</th>
                                        <th>
                                            {{ $rule->usesChargeTable() ? ($isArea ? translate('messages.Area_Name') : translate('messages.Zip_Code')) : translate('messages.Name') }}
                                        </th>
                                        <th>
                                            {{ translate('messages.Delivery_Charge') }}
                                            ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($rule->usesChargeTable())
                                        @forelse ($rule->charges as $index => $charge)
                                            <tr>
                                                <td class="rule-charge-table__sl">{{ $index + 1 }}</td>
                                                <td>{{ $charge->label() }}</td>
                                                <td>{{ \App\CentralLogics\Helpers::format_currency($charge->charge) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center">
                                                    {{ translate('messages.no_data_found') }}</td>
                                            </tr>
                                        @endforelse
                                    @elseif ($rule->pricing_method === \App\Models\DeliveryRule::METHOD_DISTANCE)
                                        <tr>
                                            <td class="rule-charge-table__sl">1</td>
                                            {{-- The rate is entered and applied per configured distance
                                                 unit, so the label follows the setting instead of naming
                                                 kilometres outright - see Helpers::deliveryRuleBaseCharge(). --}}
                                            <td>{{ translate('messages.Per_Unit_Delivery_Charge') }}
                                                ({{ \App\CentralLogics\Helpers::distanceUnitLabel() }})</td>
                                            <td>{{ \App\CentralLogics\Helpers::format_currency($rule->per_km_charge) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="rule-charge-table__sl">2</td>
                                            <td>{{ translate('messages.Maximum_delivery_charge') }}</td>
                                            <td>{{ \App\CentralLogics\Helpers::format_currency($rule->maximum_delivery_charge) }}</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td class="rule-charge-table__sl">1</td>
                                            <td>{{ translate('messages.Delivery_Charge') }}</td>
                                            <td>{{ \App\CentralLogics\Helpers::format_currency($rule->fixed_charge) }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin-views.delivery-rule.partials._status-modals')

    <div class="modal fade" id="rule-delete-modal">
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
                            <h5 class="modal-title mb-3">{{ translate('messages.Want_to_Delete_this_Delivery_Rule?') }}</h5>
                        </div>
                        <div class="text-center">
                            <p>{{ translate('messages.Are_you_sure_you_want_to_delete_this_delivery_rule_&_remove_it_permanently?') }}
                            </p>
                        </div>
                        <div class="btn--container justify-content-center">
                            <button type="button" class="btn btn--reset min-w-120px"
                                data-dismiss="modal">{{ translate('messages.No') }}</button>
                            <button type="button" id="rule-delete-confirm"
                                class="btn btn--danger min-w-120px">{{ translate('messages.Delete') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    @include('admin-views.delivery-rule.partials._status-scripts')

    <script>
        "use strict";

        let pendingDeleteFormId = null;

        $(document).on('click', '.rule-delete-btn', function () {
            pendingDeleteFormId = $(this).data('id');
            $('#rule-delete-modal').modal('show');
        });

        $(document).on('click', '#rule-delete-confirm', function () {
            if (pendingDeleteFormId) { $('#' + pendingDeleteFormId).submit(); }
        });
    </script>
@endpush
