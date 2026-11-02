@php
    // On create the zone is picked from a dropdown and $freeDelivery is null; on
    // edit the zone is fixed and the setup carries its own terms.
    $isEdit = (bool) $freeDelivery;
    $currency = \App\CentralLogics\Helpers::currency_symbol();

    $type = old('type', $freeDelivery->type ?? \App\Models\FreeDelivery::TYPE_ALL);
    $minimumOrderAmount = old('minimum_order_amount', $freeDelivery->minimum_order_amount ?? '');

    $types = [
        \App\Models\FreeDelivery::TYPE_ALL => [
            'Free_Delivery_for_all_Restaurant',
            'Enable_free_delivery_for_all_orders_in_the_selected_Zone._Customers_will_not_be_charged_any_delivery_fee,_regardless_of_the_order_amount.',
        ],
        \App\Models\FreeDelivery::TYPE_CRITERIA => [
            'Specific_Criteria',
            'Enable_free_delivery_only_when_an_order_meets_the_configured_minimum_order_amount._Customers_who_do_not_meet_the_requirement_will_pay_the_normal_delivery_charge.',
        ],
    ];
@endphp

<div class="card mb-20">
    <div class="card-body">
        <h4 class="surge-section__title">{{ translate('messages.Free_Delivery_Configuration') }}</h4>
        <p class="surge-section__subtitle">{{ translate('messages.Configure_Free_Delivery') }}</p>

        <div class="surge-section mb-20">
            <label class="input-label" for="zone_id">{{ translate('messages.Choose_Zone') }}
                <span class="text-danger">*</span>
            </label>
            @if ($isEdit)
                {{-- The setup belongs to its zone; moving it would leave the old zone
                     silently paying again, so the zone is shown rather than offered. --}}
                <input type="text" class="form-control h-45" value="{{ $freeDelivery->zone?->name }}" readonly>
            @elseif (count($zones))
                <select name="zone_id" id="zone_id" class="form-control js-select2-custom">
                    <option value="">{{ translate('messages.Select_from_dropdown') }}</option>
                    @foreach ($zones as $zoneOption)
                        <option value="{{ $zoneOption->id }}"
                            {{ old('zone_id') == $zoneOption->id ? 'selected' : '' }}>
                            {{ $zoneOption->name }}</option>
                    @endforeach
                </select>
            @else
                {{-- Only reachable when no active zone exists at all. --}}
                <div class="admin-alert mt-2">
                    <span class="admin-alert__icon">i</span>
                    <span>{{ translate('messages.No_active_zone_is_available._Create_a_zone_first.') }}</span>
                </div>
            @endif
        </div>

        <div class="surge-section mb-20">
            <div class="free-delivery-type-grid">
                @foreach ($types as $value => [$label, $hint])
                    <label class="rule-method">
                        <span class="rule-method__head">
                            <input type="radio" name="type" value="{{ $value }}"
                                class="form-check-input free-delivery-type-input"
                                {{ $type === $value ? 'checked' : '' }}>
                            <span class="rule-method__title">{{ translate('messages.' . $label) }}</span>
                        </span>
                        <span class="rule-method__hint">{{ translate('messages.' . $hint) }}</span>
                    </label>
                @endforeach
            </div>

            {{-- Only the criteria type has an amount to set; the other frees every
                 order, so the field would have nothing to say. --}}
            <div class="mt-20 {{ $type === \App\Models\FreeDelivery::TYPE_CRITERIA ? '' : 'd-none' }}"
                id="minimum-order-amount-wrapper">
                <label class="input-label" for="minimum_order_amount">
                    {{ translate('messages.Minimum_Order_Amount') }} ({{ $currency }})
                    <span class="text-danger">*</span>
                    <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                        data-original-title="{{ translate('messages.free_delivery_minimum_order_amount_tooltip') }}"><i
                            class="tio-info"></i></span>
                </label>
                <input type="number" step="0.01" min="0" name="minimum_order_amount" id="minimum_order_amount"
                    class="form-control h-45" placeholder="{{ translate('messages.Ex:_5') }}"
                    value="{{ $minimumOrderAmount }}">
            </div>
        </div>

        {{-- What the chosen type means, restated in the admin's terms. The script
             swaps the text, so the bulb stays markup. --}}
        <div class="rule-hint mt-0">
            <img src="{{ dynamicAsset('assets/admin/img/svg/bulb.svg') }}" class="svg" alt="">
            <span id="free-delivery-hint"></span>
        </div>
    </div>

    <div class="card-footer border-0">
        <div class="btn--container justify-content-end">
            <button type="reset" class="btn btn--reset min-w-120px">{{ translate('messages.Reset') }}</button>
            <button type="submit" class="btn btn--primary min-w-120px">
                {{ translate('messages.Save_Information') }}</button>
        </div>
    </div>
</div>
