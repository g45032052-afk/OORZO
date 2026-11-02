@extends('layouts.admin.app')

@section('title', translate('messages.Rules_Setup'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/admin-shared.css') }}?v={{ @filemtime(public_path('assets/admin/css/admin-shared.css')) }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/delivery-rule.css') }}?v={{ @filemtime(public_path('assets/admin/css/delivery-rule.css')) }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{ translate('messages.Rules_Setup') }}</h1>
                </div>
            </div>
        </div>

        <div class="admin-alert mb-3">
            <span class="admin-alert__icon">i</span>
            <span>{{ translate('messages.Each_zone_can_be_create_multiple_delivery_rules,_but_only_one_rule_can_be_active_at_a_time.') }}</span>
        </div>

        @if ($rules->total() === 0 && !request()->has('search'))
            <div class="card">
                <div class="card-body empty-state">
                    <img class="empty-state__icon" src="{{ dynamicAsset('assets/admin/img/price-emty.png') }}"
                        alt="{{ translate('messages.Rules_Setup') }}">
                    <h5 class="empty-state__title">
                        {{ translate('messages.No_Delivery_Charge_Rules_Setup_Is_Available') }}</h5>
                    <p class="empty-state__text">
                        {{ translate("messages.Without_any_delivery_charge_rule_zones_are_not_work_proper_&_can't_get_any_order_from_the_zone.") }}
                    </p>
                    <a href="{{ route('admin.delivery-rule.create') }}" class="btn btn--primary">
                        <i class="tio-add-circle-outlined"></i> {{ translate('messages.Add_New') }}
                    </a>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header py-2 border-0">
                    <div class="search--button-wrapper">
                        <h5 class="card-title">
                            {{ translate('messages.Delivery_Rule_List') }}
                            <span class="badge badge-soft-dark ml-2">{{ $rules->total() }}</span>
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
                                data-hs-unfold-options='{"target": "#deliveryRuleExportDropdown","type": "css-animation"}'>
                                <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                            </a>
                            <div id="deliveryRuleExportDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                                <a class="dropdown-item"
                                    href="{{ route('admin.delivery-rule.export', ['type' => 'excel', 'search' => request()->search]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg" alt="">
                                    {{ translate('messages.excel') }}
                                </a>
                                <a class="dropdown-item"
                                    href="{{ route('admin.delivery-rule.export', ['type' => 'csv', 'search' => request()->search]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                        alt="">
                                    {{ translate('messages.csv') }}
                                </a>
                            </div>
                        </div>

                        <a href="{{ route('admin.delivery-rule.create') }}" class="btn btn--primary">
                            <i class="tio-add-circle-outlined"></i> {{ translate('messages.Add_New_Delivery_Rule') }}
                        </a>
                    </div>
                </div>

                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{ translate('messages.SL') }}</th>
                                <th class="border-0">{{ translate('messages.Rule_Name') }}</th>
                                <th class="border-0">{{ translate('messages.Zone') }}</th>
                                <th class="border-0">
                                    <div class="min-w-135px">{{ translate('messages.Delivery_Method') }}</div>
                                </th>
                                <th class="border-0">{{ translate('messages.Status') }}</th>
                                <th class="border-0 text-center">{{ translate('messages.Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rules as $key => $rule)
                                <tr>
                                    <td class="pl-4">{{ $key + $rules->firstItem() }}</td>
                                    <td>
                                        <span class="line--limit-2 max-w-220px text-title"
                                            title="{{ $rule->name }}">{{ $rule->name }}</span>
                                    </td>
                                    <td>{{ $rule->zone?->name ?? translate('messages.N/A') }}</td>
                                    <td>{{ $rule->methodLabel() }}</td>
                                    <td>
                                        {{-- Both directions need confirming, so the toggle only opens a
                                             modal; the request is sent from there. --}}
                                        <label class="toggle-switch toggle-switch-sm mb-0">
                                            <input type="checkbox" class="toggle-switch-input rule-status-toggle"
                                                {{ $rule->status ? 'checked' : '' }} data-id="{{ $rule->id }}"
                                                data-status="{{ $rule->status }}" data-zone="{{ $rule->zone_id }}"
                                                data-name="{{ $rule->name }}">
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--primary btn-outline-primary"
                                                href="{{ route('admin.delivery-rule.edit', [$rule->id]) }}"
                                                title="{{ translate('messages.edit_delivery_rule') }}">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--primary btn-outline-primary"
                                                href="{{ route('admin.delivery-rule.show', [$rule->id]) }}"
                                                title="{{ translate('messages.view_delivery_rule') }}">
                                                <i class="tio-visible-outlined"></i>
                                            </a>
                                            {{-- A zone must always keep a way to price delivery, so the
                                                 active rule cannot be removed. --}}
                                            @if (!$rule->status)
                                                <a class="btn action-btn btn--danger btn-outline-danger rule-delete-btn"
                                                    href="javascript:" data-id="rule-{{ $rule->id }}"
                                                    title="{{ translate('messages.delete_delivery_rule') }}">
                                                    <i class="tio-delete-outlined"></i>
                                                </a>
                                                <form action="{{ route('admin.delivery-rule.delete', [$rule->id]) }}"
                                                    method="post" id="rule-{{ $rule->id }}">
                                                    @csrf @method('delete')
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (count($rules) !== 0)
                    <div class="page-area px-4 pb-3">{!! $rules->withQueryString()->links() !!}</div>
                @else
                    <div class="empty--data">
                        <img src="{{ dynamicAsset('assets/admin/svg/illustrations/sorry.svg') }}" alt="">
                        <h5>{{ translate('messages.no_data_found') }}</h5>
                    </div>
                @endif
            </div>
        @endif
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
            if (pendingDeleteFormId) {
                $('#' + pendingDeleteFormId).submit();
            }
        });
    </script>
@endpush
