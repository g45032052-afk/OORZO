@php
    // On create the zone is picked from a dropdown; on edit it is fixed, and
    // $express / $delayed carry the zone's current amounts.
    $isEdit = isset($zone);
    $currency = \App\CentralLogics\Helpers::currency_symbol();

    $units = ['min' => translate('messages.Min'), 'hour' => translate('messages.Hour')];

    $express = $express ?? null;
    $delayed = $delayed ?? null;

    $reduceTime = $express->reduce_delivery_time ?? ['value' => '', 'unit' => 'min'];
    $addTime = $delayed->add_delivery_time ?? ['value' => '', 'unit' => 'min'];
@endphp

<div class="card mb-20">
    <div class="card-body">
        <h4 class="surge-section__title">{{ translate('messages.Additional_Charge_Setup') }}</h4>
        <p class="surge-section__subtitle">
            {{ translate('messages.Configure_additional_delivery_charges_for_this_delivery_rule.') }}</p>

        <div class="surge-section mb-20">
            <label class="input-label" for="zone_id">{{ translate('messages.Choose_Zone') }}
                <span class="text-danger">*</span>
            </label>
            @if ($isEdit)
                {{-- The setup belongs to its zone; moving it would silently empty
                     the old zone, so the zone is shown rather than offered. --}}
                <input type="text" class="form-control h-45" value="{{ $zone->name }}" readonly>
            @else
                {{-- Starts empty rather than on the first zone: preselecting one makes the
                     required-zone rule unreachable and lets an admin save a setup against a
                     zone they never actually chose. --}}
                <select name="zone_id" id="zone_id" class="form-control js-select2-custom">
                    <option value="">{{ translate('messages.Select_from_dropdown') }}</option>
                    @foreach ($zones as $zoneOption)
                        <option value="{{ $zoneOption->id }}"
                            {{ old('zone_id') == $zoneOption->id ? 'selected' : '' }}>
                            {{ $zoneOption->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        <div class="surge-section">
            <div class="rule-hint mt-0 mb-20">
                <img src="{{ dynamicAsset('assets/admin/img/svg/bulb.svg') }}" class="svg" alt="">
                <span>{{ translate('messages.The_standard_delivery_time_follows_the_ETA_Setup_and_the_standard_charge_rules_are_get_from_Delivery_Charge_Rule._All_time_&_charges_are_count_from_same_selected_zone.') }}</span>
            </div>

            {{-- Express: the customer pays more and waits less. --}}
            <div class="charge-block">
                <div class="charge-block__intro">
                    <h5 class="surge-section__title">{{ translate('messages.Express_Delivery') }}</h5>
                    <p class="surge-section__subtitle mb-0">
                        {{ translate('messages.Deliver_faster_by_reducing_delivery_time_with_an_additional_charge.') }}</p>
                </div>

                <div class="charge-block__fields">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="input-label" for="extra_charge">
                                {{ translate('messages.Add_Extra_Charge') }} ({{ $currency }})
                                <span class="text-danger">*</span>
                                <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('messages.express_extra_charge_tooltip') }}"><i
                                        class="tio-info"></i></span>
                            </label>
                            <input type="number" step="0.01" min="0" name="extra_charge" id="extra_charge"
                                class="form-control h-45" placeholder="{{ translate('messages.Ex:_5') }}"
                                value="{{ old('extra_charge', $express->extra_charge ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="input-label" for="reduce_delivery_time">
                                {{ translate('messages.Reduce_Delivery_Time') }}
                                <span class="text-danger">*</span>
                                <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('messages.express_reduce_time_tooltip') }}"><i
                                        class="tio-info"></i></span>
                            </label>
                            <div class="input-group input--group">
                                <input type="number" step="1" min="0" name="reduce_delivery_time"
                                    id="reduce_delivery_time" class="form-control h-45"
                                    placeholder="{{ translate('messages.Ex:_5') }}"
                                    value="{{ old('reduce_delivery_time', $reduceTime['value']) }}">
                                <select name="reduce_delivery_time_unit" class="form-control h-45 charge-unit-select">
                                    @foreach ($units as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ old('reduce_delivery_time_unit', $reduceTime['unit']) === $value ? 'selected' : '' }}>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- The zone's delivery time floor is set in Zone > Settings, and an
                         express order may not be pulled in past it — so it is stated here
                         rather than left to fail on save. --}}
                    <div class="admin-alert mt-3 {{ $minimumMinutes > 0 ? '' : 'd-none' }}" id="minimum-time-note">
                        <span class="admin-alert__icon">i</span>
                        <span id="minimum-time-text">
                            {{ translate('messages.This_zones_minimum_delivery_time_limit_is') }}
                            <strong>{{ $minimumMinutes }} {{ translate('messages.Min') }}</strong>.
                        </span>
                    </div>
                </div>
            </div>

            {{-- Slightly delayed: the customer waits longer and pays less. --}}
            <div class="charge-block">
                <div class="charge-block__intro">
                    <h5 class="surge-section__title">{{ translate('messages.Slightly_Delay_Delivery') }}</h5>
                    <p class="surge-section__subtitle mb-0">
                        {{ translate('messages.Deliver_a_bit_later_and_offer_a_reduced_delivery_charge.') }}</p>
                </div>

                <div class="charge-block__fields">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="input-label" for="reduce_charge">
                                {{ translate('messages.Reduce_Charge') }} ({{ $currency }})
                                <span class="text-danger">*</span>
                                <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('messages.delay_reduce_charge_tooltip') }}"><i
                                        class="tio-info"></i></span>
                            </label>
                            <input type="number" step="0.01" min="0" name="reduce_charge" id="reduce_charge"
                                class="form-control h-45" placeholder="{{ translate('messages.Ex:_5') }}"
                                value="{{ old('reduce_charge', $delayed->reduce_charge ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="input-label" for="add_delivery_time">
                                {{ translate('messages.Add_Extra_Delivery_Time') }}
                                <span class="text-danger">*</span>
                                <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('messages.delay_extra_time_tooltip') }}"><i
                                        class="tio-info"></i></span>
                            </label>
                            <div class="input-group input--group">
                                <input type="number" step="1" min="0" name="add_delivery_time"
                                    id="add_delivery_time" class="form-control h-45"
                                    placeholder="{{ translate('messages.Ex:_5') }}"
                                    value="{{ old('add_delivery_time', $addTime['value']) }}">
                                <select name="add_delivery_time_unit" class="form-control h-45 charge-unit-select">
                                    @foreach ($units as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ old('add_delivery_time_unit', $addTime['unit']) === $value ? 'selected' : '' }}>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- The floor a reduce charge may not cross belongs to the zone's active
                         delivery rule, so the note is rewritten whenever the zone changes -
                         and stays hidden until there is a zone for it to be about, since it
                         speaks of "this zone". --}}
                    <div class="admin-alert mt-3 {{ $isEdit ? '' : 'd-none' }}" id="minimum-charge-note">
                        <span class="admin-alert__icon">i</span>
                        <span id="minimum-charge-text">
                            @if ($minimumCharge !== null)
                                {{ translate('messages.Youve_already_set_a_minimum_delivery_charge_of') }}
                                <strong>{{ \App\CentralLogics\Helpers::format_currency($minimumCharge) }}</strong>
                                {{ translate('messages.for_this_zone.') }}
                            @else
                                {{ translate('messages.This_zone_has_no_active_delivery_charge_rule_yet,_so_no_minimum_charge_applies.') }}
                            @endif
                        </span>
                    </div>
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
