@php
    // On create the zone is picked from a dropdown and $configuration is null;
    // on edit the zone is fixed and the configuration carries its own timings.
    $isEdit = (bool) $configuration;

    // Translations keyed by locale, so each language tab can prefill itself.
    $translated = [];
    foreach ($configuration?->translations ?? [] as $translation) {
        $translated[$translation->locale][$translation->key] = $translation->value;
    }

    $method = old('calculation_method', $configuration->calculation_method ?? \App\Models\EtaConfiguration::METHOD_DISTANCE);
    $isDistance = $method === \App\Models\EtaConfiguration::METHOD_DISTANCE;

    $methods = [
        \App\Models\EtaConfiguration::METHOD_DISTANCE => [
            'Distance_Based',
            'ETA_is_calculated_based_on_the_travel_distance_between_the_Restaurant_and_the_delivery_location,_along_with_the_configured_preparation_and_transit_buffer_times.',
        ],
        \App\Models\EtaConfiguration::METHOD_FIXED => [
            'Fixed_Delivery_Time',
            "ETA_is_calculated_using_the_Restaurant's_configured_minimum_and_maximum_delivery_time._Preparation_and_transit_buffer_times_are_added_automatically.",
        ],
    ];

    // The three timings both methods share, and the gap only one of them keeps.
    // Three across when the gap is dropped, two across when it is not.
    $sharedColumn = $isDistance ? 'col-md-6' : 'col-md-4';
    $sharedFields = [
        ['minimum_delivery_time', 'Minimum_Delivery_Time', 'eta_minimum_delivery_time_tooltip'],
        ['preparation_buffer', 'Preparation_Buffer', 'eta_preparation_buffer_tooltip'],
        ['transit_buffer', 'Transit_Buffer', 'eta_transit_buffer_tooltip'],
    ];
@endphp

<div class="card mb-20">
    <div class="card-body">
        <h4 class="surge-section__title">{{ translate('messages.Basic_Information') }}</h4>
        <p class="surge-section__subtitle">
            {{ translate('messages.Configure_the_basic_details_of_the_ETA_configuration_rule.') }}</p>

        <div class="surge-section">
            {{-- The tabs head the section rather than sitting inside the name column:
                 a column carrying them stands one tab-row taller than its neighbour, and
                 the zone field beside it then floats above the name input. One input per
                 language, the default first - the label stays unqualified because the tab
                 already names the language. --}}
            @if ($language)
                <div class="js-nav-scroller hs-nav-scroller-horizontal">
                    <ul class="nav nav-tabs mb-3 border-0">
                        <li class="nav-item">
                            <a class="nav-link lang_link active" href="#"
                                id="default-link">{{ translate('messages.default') }}</a>
                        </li>
                        @foreach ($language as $lang)
                            <li class="nav-item">
                                <a class="nav-link lang_link" href="#"
                                    id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-group mb-0">
                        <label class="input-label" for="name_default">
                            {{ translate('messages.ETA_Name') }}
                            <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                data-original-title="{{ translate('messages.eta_name_tooltip') }}"><i
                                    class="tio-info"></i></span>
                        </label>

                        <div class="lang_form" id="default-form">
                            <input type="text" name="name[]" id="name_default"
                                class="form-control h-45 eta-count-input" maxlength="100"
                                placeholder="{{ translate('messages.Type_ETA_name') }}"
                                value="{{ old('name.0', $configuration?->getRawOriginal('name')) }}">
                            <span class="surge-char-count eta-char-count">0/100</span>
                            <input type="hidden" name="lang[]" value="default">
                        </div>

                        @if ($language)
                            @foreach ($language as $lang)
                                <div class="d-none lang_form" id="{{ $lang }}-form">
                                    <input type="text" name="name[]" id="name_{{ $lang }}"
                                        class="form-control h-45 eta-count-input" maxlength="100"
                                        placeholder="{{ translate('messages.Type_ETA_name') }}"
                                        value="{{ $translated[$lang]['name'] ?? '' }}">
                                    <span class="surge-char-count eta-char-count">0/100</span>
                                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="input-label" for="zone_id">{{ translate('messages.Choose_Zone') }}
                        <span class="text-danger">*</span>
                    </label>
                    @if ($isEdit)
                        {{-- The configuration belongs to its zone; moving it would leave the
                             old zone silently estimating the way it did before, so the zone
                             is shown rather than offered. --}}
                        <input type="text" class="form-control h-45" value="{{ $configuration->zone?->name }}" readonly>
                    @elseif (count($zones))
                        <select name="zone_id" id="zone_id" class="form-control js-select2-custom">
                            <option value="">{{ translate('messages.Select_from_dropdown') }}</option>
                            @foreach ($zones as $zoneOption)
                                <option value="{{ $zoneOption->id }}"
                                    {{ old('zone_id', $selectedZoneId ?? null) == $zoneOption->id ? 'selected' : '' }}>
                                    {{ $zoneOption->name }}</option>
                            @endforeach
                        </select>
                    @else
                        {{-- Every zone is offered now that a zone may hold several
                             configurations, so an empty list can only mean there is no zone
                             to configure yet. --}}
                        <div class="admin-alert mt-2">
                            <span class="admin-alert__icon">i</span>
                            <span>{{ translate('messages.There_are_no_zones_yet._Create_a_zone_first,_then_come_back_to_give_it_an_ETA_configuration.') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-20">
    <div class="card-body">
        <h4 class="surge-section__title">{{ translate('messages.ETA_Calculation_Method') }}</h4>
        <p class="surge-section__subtitle">
            {{ translate('messages.Choose_how_the_estimated_delivery_time_(ETA)_will_be_calculated.') }}</p>

        <div class="surge-section mb-20">
            <div class="eta-method-grid">
                @foreach ($methods as $value => [$label, $hint])
                    <label class="rule-method">
                        <span class="rule-method__head">
                            <input type="radio" name="calculation_method" value="{{ $value }}"
                                class="form-check-input eta-method-input" {{ $method === $value ? 'checked' : '' }}>
                            <span class="rule-method__title">{{ translate('messages.' . $label) }}</span>
                        </span>
                        <span class="rule-method__hint">{{ translate('messages.' . $hint) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="surge-section">
            <div class="row g-3">
                @foreach ($sharedFields as [$field, $label, $tooltip])
                    <div class="{{ $sharedColumn }} eta-time-col">
                        <label class="input-label" for="{{ $field }}">
                            {{ translate('messages.' . $label) }}
                            <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                data-original-title="{{ translate('messages.' . $tooltip) }}"><i
                                    class="tio-info"></i></span>
                        </label>
                        <div class="input-group eta-minute-input">
                            <input type="number" step="1" min="0" name="{{ $field }}" id="{{ $field }}"
                                class="form-control h-45 eta-minute-field"
                                placeholder="{{ translate('messages.Ex:_5') }}"
                                value="{{ old($field, $configuration->$field ?? '') }}">
                            <div class="input-group-append">
                                <span class="input-group-text">{{ translate('messages.Min') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Only the distance based method has a gap to set: the other reads its
                     maximum from the restaurant, so it already has a range. --}}
                <div class="col-md-6 {{ $isDistance ? '' : 'd-none' }}" id="time-gap-wrapper">
                    <label class="input-label" for="time_gap">
                        {{ translate('messages.Gap_Between_Min_&_Max_Time') }}
                        <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                            data-original-title="{{ translate('messages.eta_time_gap_tooltip') }}"><i
                                class="tio-info"></i></span>
                    </label>
                    <div class="input-group eta-minute-input">
                        <input type="number" step="1" min="0" name="time_gap" id="time_gap"
                            class="form-control h-45 eta-minute-field" placeholder="{{ translate('messages.Ex:_5') }}"
                            value="{{ old('time_gap', $configuration->time_gap ?? '') }}">
                        <div class="input-group-append">
                            <span class="input-group-text">{{ translate('messages.Min') }}</span>
                        </div>
                    </div>
                    {{-- The example is kept in step with what has been typed, so the admin
                         reads the range the customer would be shown. --}}
                    <div class="eta-preview">
                        {{ translate('messages.Time_will_show_like_this') }} <strong id="eta-preview-range">10
                            {{ translate('messages.eta_minute_unit') }} - 15 {{ translate('messages.eta_minute_unit') }}</strong>
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
