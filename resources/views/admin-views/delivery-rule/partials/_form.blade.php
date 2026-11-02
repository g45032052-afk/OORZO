@php
    // $rule is null on create.
    $method = old('pricing_method', $rule->pricing_method ?? \App\Models\DeliveryRule::METHOD_AREA);
    // On create the zone may have been named on the way in — see
    // DeliveryRuleController::create() — and otherwise the first is offered.
    $selectedZone = old('zone_id', $rule->zone_id ?? ($selectedZoneId ?? $zones->first()->id ?? null));

    // Existing amounts keyed by target id, so the tables can prefill.
    $areaCharges = [];
    $zipCharges = [];
    foreach ($rule->charges ?? [] as $charge) {
        if ($charge->area_id) { $areaCharges[$charge->area_id] = $charge->charge; }
        if ($charge->zip_code_id) { $zipCharges[$charge->zip_code_id] = $charge->charge; }
    }

    $methods = [
        \App\Models\DeliveryRule::METHOD_AREA => ['Area_Wise', 'Set_a_fixed_delivery_charge_for_each_service_area.'],
        \App\Models\DeliveryRule::METHOD_ZIP => ['Zip_Code_Wise', 'Set_a_fixed_delivery_charge_for_each_ZIP_code.'],
        \App\Models\DeliveryRule::METHOD_DISTANCE => ['Distance_Wise', 'Configure_delivery_charges_based_on_the_delivery_distance'],
        \App\Models\DeliveryRule::METHOD_FIXED => ['Fixed_Amount', 'Apply_the_same_delivery_charge_to_all_bookings.'],
    ];
@endphp

<div class="card mb-20">
    <div class="card-body">
        <h4 class="surge-section__title">{{ translate('messages.Basic_Information') }}</h4>
        <p class="surge-section__subtitle">
            {{ translate('messages.Create_a_delivery_pricing_rule_for_a_specific_Zone_and_coverage.') }}</p>

        <div class="surge-section">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="input-label" for="name">{{ translate('messages.Rule_Name') }}
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="name" id="name" class="form-control h-45" maxlength="191"
                        placeholder="{{ translate('messages.Type_your_rule_name') }}"
                        value="{{ old('name', $rule->name ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="input-label" for="zone_id">{{ translate('messages.Zone') }}
                        <span class="text-danger">*</span>
                    </label>
                    <select name="zone_id" id="zone_id" class="form-control js-select2-custom">
                        @foreach ($zones as $zone)
                            <option value="{{ $zone->id }}" {{ $selectedZone == $zone->id ? 'selected' : '' }}>
                                {{ $zone->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="input-label" for="minimum_delivery_charge">
                        {{ translate('messages.Minimum_Delivery_Charge') }}({{ \App\CentralLogics\Helpers::currency_symbol() }})
                        <span class="text-danger">*</span>
                    </label>
                    <input type="number" step="0.01" min="0" name="minimum_delivery_charge"
                        id="minimum_delivery_charge" class="form-control h-45"
                        placeholder="{{ translate('messages.Ex:_5') }}"
                        value="{{ old('minimum_delivery_charge', $rule->minimum_delivery_charge ?? '') }}">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-20">
    <div class="card-body">
        <h4 class="surge-section__title">{{ translate('messages.Pricing_Strategy') }}</h4>
        <p class="surge-section__subtitle">
            {{ translate('messages.Choose_how_this_rule_calculates_the_base_delivery_charge.') }}</p>

        <div class="surge-section mb-20">
            <label class="input-label">
                {{ translate('messages.Delivery_Pricing_Method') }} <span class="text-danger">*</span>
                <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                    data-original-title="{{ translate('messages.delivery_pricing_method_tooltip') }}"><i
                        class="tio-info"></i></span>
            </label>

            <div class="rule-method-grid">
                @foreach ($methods as $value => [$label, $hint])
                    <label class="rule-method {{ $method === $value ? 'is-selected' : '' }}">
                        <span class="rule-method__head">
                            <input type="radio" name="pricing_method" value="{{ $value }}"
                                class="form-check-input rule-method-input" {{ $method === $value ? 'checked' : '' }}>
                            <span class="rule-method__title">{{ translate('messages.' . $label) }}</span>
                        </span>
                        <span class="rule-method__hint">{{ translate('messages.' . $hint) }}</span>
                    </label>
                @endforeach
            </div>

            {{-- The bulb is markup, so the script fills the span, not this. --}}
            <div class="rule-hint">
                <img src="{{ dynamicAsset('assets/admin/img/svg/bulb.svg') }}" class="svg" alt="">
                <span id="method-hint"></span>
            </div>
        </div>

        {{-- Area wise --}}
        <div class="surge-section rule-pane" data-pane="{{ \App\Models\DeliveryRule::METHOD_AREA }}">
            <div id="area-charge-wrapper">
                @include('admin-views.delivery-rule.partials._charge-table', [
                    'rows' => $areas,
                    'labelKey' => 'name',
                    'charges' => $areaCharges,
                    'fieldName' => 'area_charges',
                    'heading' => translate('messages.Area_Name'),
                    'title' => translate('messages.Area_Wise_Charge_Setup'),
                    'description' => translate('messages.Configure_fixed_delivery_charges_for_each_service_area_within_a_zone.'),
                    'hintText' => translate('messages.All_areas_under_this_zone_will_be_listed_automatically._Set_the_delivery_charge_for_each_area.'),
                    'hintLinkText' => translate('messages.Setup_Area'),
                    'emptyLink' => route('admin.area.list'),
                    'emptyText' => translate('messages.No_areas_have_been_created_for_this_Zone._To_set_up_Area_Wise_Charges,_first_create_an_area_for_this_Zone_from'),
                    'emptyLinkText' => translate('messages.Area_Setup'),
                ])
            </div>
        </div>

        {{-- Zip code wise --}}
        <div class="surge-section rule-pane d-none" data-pane="{{ \App\Models\DeliveryRule::METHOD_ZIP }}">
            <div id="zip-charge-wrapper">
                @include('admin-views.delivery-rule.partials._charge-table', [
                    'rows' => $zipCodes,
                    'labelKey' => 'zip_code',
                    'charges' => $zipCharges,
                    'fieldName' => 'zip_charges',
                    'heading' => translate('messages.Zip_Code'),
                    'title' => translate('messages.Zip_Code_Wise_Charge_Setup'),
                    'description' => translate('messages.Configure_fixed_delivery_charges_for_each_ZIP_code_within_a_zone.'),
                    'hintText' => translate('messages.All_ZIP_codes_under_this_zone_will_be_listed_automatically._Set_the_delivery_charge_for_each_code.'),
                    'hintLinkText' => translate('messages.Setup_Zip_Code'),
                    'emptyLink' => route('admin.zip-code.list'),
                    'emptyText' => translate('messages.No_ZIP_codes_have_been_created_for_this_Zone._To_set_up_ZIP_Code_Wise_Charges,_first_create_a_ZIP_code_for_this_Zone_from'),
                    'emptyLinkText' => translate('messages.Zip_Code_Setup'),
                ])
            </div>
        </div>

        {{-- Distance wise --}}
        <div class="surge-section rule-pane d-none" data-pane="{{ \App\Models\DeliveryRule::METHOD_DISTANCE }}">
            <h5 class="surge-section__title">{{ translate('messages.Distance_Wise_Charge_Setup') }}</h5>
            <p class="surge-section__subtitle">
                {{ translate('messages.Charges_scale_with_the_delivery_distance,_bounded_by_the_maximum.') }}</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="input-label" for="per_km_charge">
                        {{ translate('messages.Per_Unit_Delivery_Charge') }} ({{ \App\CentralLogics\Helpers::currency_symbol() }}/{{ \App\CentralLogics\Helpers::distanceUnitLabel() }})
                        <span class="text-danger">*</span>
                    </label>
                    <input type="number" step="0.01" min="0" name="per_km_charge" id="per_km_charge"
                        class="form-control h-45" placeholder="{{ translate('messages.Ex:_5') }}"
                        value="{{ old('per_km_charge', $rule->per_km_charge ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="input-label" for="maximum_delivery_charge">
                        {{ translate('messages.Maximum_Delivery_Charge') }}({{ \App\CentralLogics\Helpers::currency_symbol() }})
                        <span class="text-danger">*</span>
                    </label>
                    <input type="number" step="0.01" min="0" name="maximum_delivery_charge"
                        id="maximum_delivery_charge" class="form-control h-45"
                        placeholder="{{ translate('messages.Ex:_5') }}"
                        value="{{ old('maximum_delivery_charge', $rule->maximum_delivery_charge ?? '') }}">
                </div>
            </div>
        </div>

        {{-- Fixed amount --}}
        <div class="surge-section rule-pane d-none" data-pane="{{ \App\Models\DeliveryRule::METHOD_FIXED }}">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <h5 class="surge-section__title">{{ translate('messages.Fixed_Charge_Setup') }}</h5>
                    <p class="surge-section__subtitle mb-0">
                        {{ translate('messages.Assign_a_fixed_delivery_charge_for_service_areas.') }}</p>
                </div>
                <div class="col-md-7">
                    <label class="input-label" for="fixed_charge">
                        {{ translate('messages.Delivery_Charge') }} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                        <span class="text-danger">*</span>
                        <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                            data-original-title="{{ translate('messages.fixed_charge_tooltip') }}"><i
                                class="tio-info"></i></span>
                    </label>
                    <input type="number" step="0.01" min="0" name="fixed_charge" id="fixed_charge"
                        class="form-control h-45" placeholder="{{ translate('messages.Ex:_5') }}"
                        value="{{ old('fixed_charge', $rule->fixed_charge ?? '') }}">
                </div>
            </div>
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
