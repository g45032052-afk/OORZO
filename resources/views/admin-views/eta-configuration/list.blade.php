@extends('layouts.admin.app')

@section('title', translate('messages.ETA_Configuration'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/admin-shared.css') }}?v={{ @filemtime(public_path('assets/admin/css/admin-shared.css')) }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/eta-configuration.css') }}?v={{ @filemtime(public_path('assets/admin/css/eta-configuration.css')) }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{ translate('messages.ETA_Configuration') }}</h1>
                </div>
            </div>
        </div>

        <div class="admin-alert mb-3">
            <span class="admin-alert__icon">i</span>
            <span>{{ translate('messages.A_zone_can_hold_several_ETA_configurations_but_estimates_from_one_of_them._Turning_one_on_turns_the_zones_others_off,_and_a_zone_must_always_keep_one_turned_on.') }}</span>
        </div>

        @if ($configurations->total() === 0 && !request()->has('search'))
            <div class="card">
                <div class="card-body empty-state">
                    <img class="empty-state__icon" src="{{ dynamicAsset('assets/admin/img/price-emty.png') }}"
                        alt="{{ translate('messages.ETA_Configuration') }}">
                    <h5 class="empty-state__title">
                        {{ translate('messages.Currently_You_Dont_Have_Any_ETA_Configuration') }}</h5>
                    <p class="empty-state__text">
                        {{ translate('messages.To_estimate_delivery_times_per_zone,_you_must_create_at_least_one_ETA_Configuration._In_this_page_you_see_all_the_ETA_configurations_you_added.') }}
                    </p>
                    <a href="{{ route('admin.eta-configuration.create') }}" class="btn btn--primary">
                        <i class="tio-add-circle-outlined"></i> {{ translate('messages.Add_New') }}
                    </a>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header py-2 border-0">
                    <div class="search--button-wrapper">
                        <h5 class="card-title">
                            {{ translate('messages.ETA_Configuration_List') }}
                            <span class="badge badge-soft-dark ml-2">{{ $configurations->total() }}</span>
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
                                data-hs-unfold-options='{"target": "#etaConfigurationExportDropdown","type": "css-animation"}'>
                                <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                            </a>
                            <div id="etaConfigurationExportDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                                <a class="dropdown-item"
                                    href="{{ route('admin.eta-configuration.export', ['type' => 'excel', 'search' => request()->search]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg" alt="">
                                    {{ translate('messages.excel') }}
                                </a>
                                <a class="dropdown-item"
                                    href="{{ route('admin.eta-configuration.export', ['type' => 'csv', 'search' => request()->search]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                        alt="">
                                    {{ translate('messages.csv') }}
                                </a>
                            </div>
                        </div>

                        <a href="{{ route('admin.eta-configuration.create') }}" class="btn btn--primary">
                            <i class="tio-add-circle-outlined"></i> {{ translate('messages.Add_New') }}
                        </a>
                    </div>
                </div>

                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{ translate('messages.SL') }}</th>
                                <th class="border-0">{{ translate('messages.Name') }}</th>
                                <th class="border-0">{{ translate('messages.Zone') }}</th>
                                <th class="border-0">
                                    <div class="min-w-135px">{{ translate('messages.ETA_Type') }}</div>
                                </th>
                                <th class="border-0">
                                    <div class="min-w-180px">{{ translate('messages.Time_Duration') }}</div>
                                </th>
                                <th class="border-0">
                                    <div class="min-w-180px">{{ translate('messages.Buffer_Time') }}</div>
                                </th>
                                <th class="border-0">{{ translate('messages.Status') }}</th>
                                <th class="border-0 text-center">{{ translate('messages.Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($configurations as $key => $configuration)
                                <tr>
                                    <td class="pl-4">{{ $key + $configurations->firstItem() }}</td>
                                    <td>
                                        <span class="line--limit-2 max-w-220px text-title"
                                            title="{{ $configuration->name }}">{{ $configuration->name ?? translate('messages.N/A') }}</span>
                                    </td>
                                    <td>{{ $configuration->zone?->name ?? translate('messages.N/A') }}</td>
                                    <td>{{ $configuration->methodLabel() }}</td>
                                    <td>
                                        <div class="eta-cell">
                                            <span class="eta-cell__line">
                                                {{ translate('messages.Minimum_Delivery_Time') }} :
                                                <strong>{{ (int) $configuration->minimum_delivery_time }}
                                                    {{ translate('messages.eta_minute_unit') }}</strong>
                                            </span>
                                            {{-- Only the distance based method keeps a gap; the other takes
                                                 its maximum from the restaurant. --}}
                                            @if ($configuration->usesTimeGap())
                                                <span class="eta-cell__line">
                                                    {{ translate('messages.Gap_Between_Min_&_Max_Time') }} :
                                                    <strong>{{ (int) $configuration->time_gap }}
                                                        {{ translate('messages.eta_minute_unit') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="eta-cell">
                                            <span class="eta-cell__line">
                                                {{ translate('messages.Preparation_Buffer') }} :
                                                <strong>{{ (int) $configuration->preparation_buffer }}
                                                    {{ translate('messages.eta_minute_unit') }}</strong>
                                            </span>
                                            <span class="eta-cell__line">
                                                {{ translate('messages.Transit_Buffer') }} :
                                                <strong>{{ (int) $configuration->transit_buffer }}
                                                    {{ translate('messages.eta_minute_unit') }}</strong>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        {{-- Both directions need confirming, so the toggle only opens a
                                             modal; the request is sent from there. --}}
                                        <label class="toggle-switch toggle-switch-sm mb-0">
                                            <input type="checkbox"
                                                class="toggle-switch-input eta-configuration-status-toggle"
                                                {{ $configuration->status ? 'checked' : '' }}
                                                data-id="{{ $configuration->id }}"
                                                data-status="{{ $configuration->status }}"
                                                data-zone="{{ $configuration->zone_id }}">
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--primary btn-outline-primary"
                                                href="{{ route('admin.eta-configuration.edit', [$configuration->id]) }}"
                                                title="{{ translate('messages.edit_eta_configuration') }}">
                                                <i class="tio-edit"></i>
                                            </a>
                                            {{-- A zone's last configuration cannot be deleted, and deleting
                                                 the one it is estimating from means naming which takes over,
                                                 so the button carries what the prompt needs to decide. --}}
                                            <a class="btn action-btn btn--danger btn-outline-danger eta-configuration-delete-btn"
                                                href="javascript:" data-id="eta-configuration-{{ $configuration->id }}"
                                                data-configuration="{{ $configuration->id }}"
                                                data-zone="{{ $configuration->zone_id }}"
                                                data-status="{{ $configuration->status }}"
                                                data-only="{{ (int) $configuration->zone_configuration_count <= 1 ? 1 : 0 }}"
                                                title="{{ translate('messages.delete_eta_configuration') }}">
                                                <i class="tio-delete-outlined"></i>
                                            </a>
                                            <form
                                                action="{{ route('admin.eta-configuration.delete', [$configuration->id]) }}"
                                                method="post" id="eta-configuration-{{ $configuration->id }}">
                                                @csrf @method('delete')
                                                {{-- Filled from the prompt when the configuration being deleted
                                                     is the active one; the zone is switched to it as it goes. --}}
                                                <input type="hidden" name="replacement_id" value="">
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (count($configurations) !== 0)
                    <div class="page-area px-4 pb-3">{!! $configurations->withQueryString()->links() !!}</div>
                @else
                    <div class="empty--data">
                        <img src="{{ dynamicAsset('assets/admin/svg/illustrations/sorry.svg') }}" alt="">
                        <h5>{{ translate('messages.no_data_found') }}</h5>
                    </div>
                @endif
            </div>
        @endif
    </div>

    @include('admin-views.eta-configuration.partials._status-modals')

    <div class="modal fade" id="eta-configuration-delete-modal">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-body pb-5 pt-5">
                    <div class="max-349 mx-auto mb-20">
                        <div class="text-center">
                            <img class="mb-20" src="{{ dynamicAsset('assets/admin/img/modal/delete-icon.png') }}" alt="">
                            <h5 class="modal-title mb-3">
                                {{ translate('messages.Want_to_Delete_this_ETA_Configuration?') }}</h5>
                        </div>
                        <div class="text-center">
                            <p id="eta-delete-text">
                                {{ translate('messages.Are_you_sure_you_want_to_delete_this_ETA_configuration_&_remove_it_permanently?') }}
                            </p>
                        </div>

                        {{-- Deleting the configuration a zone is estimating from is a
                             handover: the zone keeps estimating, from whichever one is
                             named here. Hidden for an inactive one, which nothing is
                             estimating from, and for a zone's last one, which cannot be
                             deleted at all - that state has nothing to answer, so it
                             drops both actions and leaves the one button that closes. --}}
                        <div class="form-group text-left d-none" id="eta-delete-replacement-wrapper">
                            <label class="input-label"
                                for="eta_delete_replacement_id">{{ translate('messages.Select_ETA_Configuration') }}</label>
                            <select id="eta_delete_replacement_id" class="form-control h--45px"></select>
                        </div>

                        <div class="btn--container justify-content-center">
                            <button type="button" class="btn btn--reset min-w-120px" id="eta-delete-cancel"
                                data-dismiss="modal">{{ translate('messages.No') }}</button>
                            <button type="button" id="eta-configuration-delete-confirm"
                                class="btn btn--danger min-w-120px">{{ translate('messages.Delete') }}</button>
                            <button type="button" id="eta-delete-close" class="btn btn--primary min-w-120px d-none"
                                data-dismiss="modal">{{ translate('messages.Close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    @include('admin-views.eta-configuration.partials._status-scripts')

    <script>
        "use strict";

        (function () {
            const DELETE_TEXT = @json(translate('messages.Are_you_sure_you_want_to_delete_this_ETA_configuration_&_remove_it_permanently?'));
            const HANDOVER_TEXT = @json(translate('messages.This_zone_is_estimating_from_this_configuration._Choose_which_one_takes_over,_and_it_will_be_turned_on_as_this_one_is_deleted.'));
            const LAST_ONE_TEXT = @json(translate('messages.This_is_the_only_ETA_configuration_of_this_zone_and_cannot_be_deleted._Create_another_one_for_the_zone_first.'));

            let pendingDeleteFormId = null;

            $(document).on('click', '.eta-configuration-delete-btn', function () {
                const $button = $(this);
                const isOnlyOne = Number($button.data('only')) === 1;
                const isActive = Number($button.data('status')) === 1;

                pendingDeleteFormId = $button.data('id');

                const $text = $('#eta-delete-text');
                const $wrapper = $('#eta-delete-replacement-wrapper');
                const $confirm = $('#eta-configuration-delete-confirm');
                const $cancel = $('#eta-delete-cancel');
                const $close = $('#eta-delete-close');

                $('#' + pendingDeleteFormId).find('input[name="replacement_id"]').val('');

                // Asking, until the row says otherwise: two actions and, where a
                // successor is needed, the select to name one.
                $confirm.removeClass('d-none').prop('disabled', false);
                $cancel.removeClass('d-none');
                $close.addClass('d-none');

                // A zone always keeps a configuration, so its last one is refused here
                // as well as by the server. There is nothing to answer, so the prompt
                // says why and closes on its one button.
                if (isOnlyOne) {
                    $text.text(LAST_ONE_TEXT);
                    $wrapper.addClass('d-none');
                    $confirm.addClass('d-none');
                    $cancel.addClass('d-none');
                    $close.removeClass('d-none');
                    $('#eta-configuration-delete-modal').modal('show');
                    return;
                }

                if (!isActive) {
                    $text.text(DELETE_TEXT);
                    $wrapper.addClass('d-none');
                    $('#eta-configuration-delete-modal').modal('show');
                    return;
                }

                $text.text(HANDOVER_TEXT);
                $wrapper.removeClass('d-none');
                $confirm.prop('disabled', true);
                $('#eta-configuration-delete-modal').modal('show');

                // Same options as the turn off prompt, from the same endpoint.
                window.etaLoadReplacements(
                    $('#eta_delete_replacement_id'),
                    $button.data('zone'),
                    $button.data('configuration'),
                    function (hasOptions) {
                        if (hasOptions) {
                            $confirm.prop('disabled', false);
                            return;
                        }

                        // The count said there were others and the endpoint says there
                        // are none - deleted from another tab in between. Same dead end
                        // as a zone's last configuration, so it reads the same.
                        $text.text(LAST_ONE_TEXT);
                        $wrapper.addClass('d-none');
                        $confirm.addClass('d-none');
                        $cancel.addClass('d-none');
                        $close.removeClass('d-none');
                    }
                );
            });

            $(document).on('click', '#eta-configuration-delete-confirm', function () {
                if (!pendingDeleteFormId) {
                    return;
                }

                const $form = $('#' + pendingDeleteFormId);

                if (!$('#eta-delete-replacement-wrapper').hasClass('d-none')) {
                    const replacement = $('#eta_delete_replacement_id').val();
                    if (!replacement) { return; }
                    $form.find('input[name="replacement_id"]').val(replacement);
                }

                $form.submit();
            });
        })();
    </script>
@endpush
