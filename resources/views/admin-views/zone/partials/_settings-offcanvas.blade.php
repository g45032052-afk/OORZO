@php
    // The drawer holds the two settings a zone still owns; delivery charges,
    // saver delivery and free delivery each have a screen of their own.
    $currency = \App\CentralLogics\Helpers::currency_symbol();

    // A ceiling only exists while an amount is stored, so the switch reads it.
    $codEnabled = (float) ($zone->max_cod_order_amount ?? 0) > 0;

    // A zone may hold several tiers; the drawer shows the first and leaves the
    // rest alone — see ZoneController::zone_settings_update().
    $incentive = $zone->incentives->first();
    $incentiveEnabled = (bool) $zone->incentive_status;

    // A zone serves customers only once it can price a delivery and quote a time
    // for it, so the drawer carries the same shortcuts the list popover offers.
    $has_delivery_charge_setup = ($zone->active_delivery_rules_count ?? 0) > 0;
    $has_eta_setup = ($zone->active_eta_count ?? 0) > 0;
    $zone_ready = $has_delivery_charge_setup && $has_eta_setup;
@endphp

<form action="{{ route('admin.zone.zone_settings_update', $zone->id) }}" method="post"
    class="d-flex flex-column h-100" id="zone-settings-offcanvas-form">
    @csrf
    <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
        <h3 class="mb-0 line--limit-1">{{ translate('messages.Setting') }} - {{ $zone->name }}</h3>
        <button type="button"
            class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
            aria-label="Close">&times;</button>
    </div>

    {{-- pb-0 only where the notice below is sticky: a sticky child is clamped
         to its containing block, which is this element's content box, so bottom
         padding here becomes a strip under the pinned card that the fields above
         show through. With the ready-state notice sitting in normal flow the
         padding is wanted, so it stays. --}}
    <div class="custom-offcanvas-body offcanvas-body--fill p-20 @if (! $zone_ready) pb-0 @endif">
        {{-- Max COD order amount --}}
        <div class="bg--secondary rounded p-20 mb-20">
            <div class="d-flex justify-content-between align-items-start __gap-12px">
                <div>
                    <h5 class="mb-1">{{ translate('messages.Max_COD_Order_Amount') }}</h5>
                    <p class="fs-12 mb-0">
                        {{ translate('messages.Customers_cannot_place_COD_orders_above_the_configured_limit.') }}</p>
                </div>
                <label class="toggle-switch toggle-switch-sm mb-0 flex-shrink-0">
                    <input type="checkbox" class="toggle-switch-input zone-setting-toggle"
                        name="max_cod_order_amount_status" id="max_cod_order_amount_status" value="1"
                        data-fields="#max-cod-fields" {{ $codEnabled ? 'checked' : '' }}>
                    <span class="toggle-switch-label">
                        <span class="toggle-switch-indicator"></span>
                    </span>
                </label>
            </div>

            {{-- The amount is the setting itself, so it is only asked for once
                 the switch says there is a ceiling. --}}
            <div class="card card-body border-0 shadow-none mt-3 {{ $codEnabled ? '' : 'd-none' }}" id="max-cod-fields">
                <div class="form-group mb-0">
                    <label class="input-label" for="max_cod_order_amount">
                        {{ translate('messages.Max_COD_Order_Amount') }} ({{ $currency }})
                    </label>
                    <input type="number" step=".01" min="0" name="max_cod_order_amount" id="max_cod_order_amount"
                        class="form-control h--45px" placeholder="{{ translate('messages.Ex_:_100') }}"
                        value="{{ $zone->max_cod_order_amount ?: '' }}">
                </div>
            </div>
        </div>

        {{-- Incentive settings --}}
        <div class="bg--secondary rounded p-20 mb-20">
            <div class="d-flex justify-content-between align-items-start __gap-12px">
                <div>
                    <h5 class="mb-1">{{ translate('messages.Incentive_Settings') }}</h5>
                    <p class="fs-12 mb-0">
                        {{ translate('messages.Motivate_deliverymen_to_achieve_daily_earning_targets_and_provide_additional_incentives_to_encourage_increased_deliveries.') }}
                    </p>
                </div>
                <label class="toggle-switch toggle-switch-sm mb-0 flex-shrink-0">
                    <input type="checkbox" class="toggle-switch-input zone-setting-toggle" name="incentive_status"
                        id="incentive_status" value="1" data-fields="#incentive-fields"
                        {{ $incentiveEnabled ? 'checked' : '' }}>
                    <span class="toggle-switch-label">
                        <span class="toggle-switch-indicator"></span>
                    </span>
                </label>
            </div>

            {{-- Switching off keeps the amounts: they are only stopped from
                 being paid, so they are still shown here. --}}
            <div class="card card-body border-0 shadow-none mt-3 {{ $incentiveEnabled ? '' : 'd-none' }}"
                id="incentive-fields">
                <div class="form-group">
                    <label class="input-label" for="earning">
                        {{ translate('messages.Daily_Earning_Target') }} {{ $currency }}
                        <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                            data-original-title="{{ translate('messages.Set_the_daily_earning_target_for_deliverymen_for_this_business_zone.') }}"><i
                                class="tio-info"></i></span>
                    </label>
                    <input type="number" step=".01" min="1" name="earning" id="earning" class="form-control h--45px"
                        placeholder="{{ translate('messages.Ex_:_100') }}" value="{{ $incentive?->earning ?: '' }}">
                </div>
                <div class="form-group mb-0">
                    <label class="input-label" for="incentive">
                        {{ translate('messages.Incentive_For_Completing_Target') }} {{ $currency }}
                        <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                            data-original-title="{{ translate('messages.Set_the_incentive_amount_for_deliverymen_on_completing_the_daily_earning_target_for_this_business_zone.') }}"><i
                                class="tio-info"></i></span>
                    </label>
                    <input type="number" step=".01" min="1" name="incentive" id="incentive"
                        class="form-control h--45px" placeholder="{{ translate('messages.Ex_:_10') }}"
                        value="{{ $incentive?->incentive ?: '' }}">
                </div>
            </div>
        </div>

        {{-- A zone cannot price a delivery without a rule, so the drawer points at
             the screens that own them while anything is still missing, and
             confirms the zone is live once nothing is. --}}
        @if ($zone_ready)
            <div class="admin-alert admin-alert--success">
                <span class="admin-alert__icon">&check;</span>
                <span>
                    {{ translate('messages.This_business_zone_is_fully_configured_and_ready_to_serve_orders.') }}
                </span>
            </div>
        @else
            <div class="admin-alert admin-alert--sticky">
                <span class="admin-alert__icon">i</span>
                <span>
                    {{ translate('messages.After_setup_everything_you_must_add_both_a_delivery_charge_setup_and_an_ETA_configuration') }}
                    @include('admin-views.zone.partials._setup-links')
                </span>
            </div>
        @endif
    </div>

    <div class="custom-offcanvas-footer offcanvas-footer p-20 d-flex justify-content-end gap-2">
        <button type="reset" class="btn btn--reset min-w-120px">{{ translate('messages.Reset') }}</button>
        <button type="submit" class="btn btn--primary min-w-120px">{{ translate('messages.Save') }}</button>
    </div>
</form>
