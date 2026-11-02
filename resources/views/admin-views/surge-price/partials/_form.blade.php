@php
    // $surge is null on create. Every prefill below tolerates that so this one
    // partial can back both screens.
    $durationType = old('duration_type', $surge->duration_type ?? 'daily');
    $weeklyDays = $surge->weekly_days ?? [];
    $customDays = $surge->custom_days ?? [];
    $customTimes = $surge->custom_times ?? [];

    $dateRange = $surge && $surge->start_date && $surge->end_date
        ? \Carbon\Carbon::parse($surge->start_date)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($surge->end_date)->format('d M Y')
        : '';
    // Must match the daterangepicker display format the JS reads back.
    $timeRange = $surge && $surge->start_time && $surge->end_time
        ? date('h:i A', strtotime($surge->start_time)) . ' - ' . date('h:i A', strtotime($surge->end_time))
        : '';

    // The note only reaches customers while the toggle beside it is on, so that is
    // also when it has to be filled in. Rendered from the same state the toggle is,
    // so a form that comes back with the toggle on comes back with the rule on too.
    $noteRequired = (bool) old('customer_note_status', $surge->customer_note_status ?? 0);

    $weekdayOrder = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    // Translations keyed by locale, so each language tab can prefill itself.
    $translated = [];
    foreach ($surge->translations ?? [] as $translation) {
        $translated[$translation->locale][$translation->key] = $translation->value;
    }
@endphp

<div class="card">
    <div class="card-body">

        {{-- ── Basic Information ───────────────────────────────────────────
             Heading sits on the card; only the fields live in the tinted panel. --}}
        <div class="surge-block">
            <div class="surge-block__heading">
                <h4 class="surge-section__title">{{ translate('messages.Basic_Information') }}</h4>
                <p class="surge-section__subtitle">
                    {{ translate('messages.Configure_the_basic_details_of_the_surge_pricing_rule.') }}</p>
            </div>

            @if ($language)
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
            @endif

            <div class="lang_form surge-section" id="default-form">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group surge-counted-field mb-md-0">
                            {{-- Same wrapper as the note field so both labels sit on
                                 one line despite the toggle making that row taller. --}}
                            <div class="d-flex align-items-center justify-content-between gap-1 flex-wrap surge-label-row">
                                <label class="input-label mb-0" for="surge_price_name_default">
                                    {{ translate('messages.Surge_Price_Name') }} ({{ translate('messages.Default') }})
                                    <span class="text-danger">*</span>
                                    <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ translate('messages.surge_price_name_tooltip') }}"><i
                                            class="tio-info"></i></span>
                                </label>
                            </div>
                            <input type="text" name="surge_price_name[]" id="surge_price_name_default"
                                class="form-control surge-count-input" maxlength="100" required
                                placeholder="{{ translate('messages.Type_surge_price_name') }}"
                                value="{{ old('surge_price_name.0', $surge?->getRawOriginal('surge_price_name')) }}">
                            <span class="surge-char-count">0/100</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group surge-counted-field mb-0">
                            <div class="d-flex align-items-center justify-content-between gap-1 flex-wrap surge-label-row">
                                <label class="input-label mb-0" for="customer_note_default">
                                    {{ translate('messages.Note_For_Customer') }}
                                    ({{ translate('messages.Default') }})
                                    {{-- Always rendered so the label always holds a "*", which is
                                         what stops FormValidation adding a second one of its own. --}}
                                    <span
                                        class="text-danger note-required-star {{ $noteRequired ? '' : 'd-none' }}">*</span>
                                    <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ translate('messages.customer_note_tooltip') }}"><i
                                            class="tio-info"></i></span>
                                </label>
                                <label class="toggle-switch toggle-switch-sm mb-0" for="customer_note_status">
                                    <input type="checkbox" name="customer_note_status" class="toggle-switch-input"
                                        id="customer_note_status" value="1"
                                        {{ $surge?->customer_note_status ? 'checked' : '' }}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                            <textarea name="customer_note[]" id="customer_note_default" class="form-control surge-count-input surge-note-input" rows="1"
                                maxlength="300" {{ $noteRequired ? 'required' : '' }}
                                placeholder="{{ translate('messages.Type_note') }}">{{ old('customer_note.0', $surge?->getRawOriginal('customer_note')) }}</textarea>
                            <span class="surge-char-count">0/300</span>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="lang[]" value="default">
            </div>

            @foreach ($language as $lang)
                <div class="d-none lang_form surge-section" id="{{ $lang }}-form">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group surge-counted-field mb-md-0">
                                <label class="input-label" for="surge_price_name_{{ $lang }}">
                                    {{ translate('messages.Surge_Price_Name') }} ({{ strtoupper($lang) }})
                                </label>
                                <input type="text" name="surge_price_name[]" id="surge_price_name_{{ $lang }}"
                                    class="form-control surge-count-input" maxlength="100"
                                    placeholder="{{ translate('messages.Type_surge_price_name') }}"
                                    value="{{ $translated[$lang]['surge_price_name'] ?? '' }}">
                                <span class="surge-char-count">0/100</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group surge-counted-field mb-0">
                                <label class="input-label" for="customer_note_{{ $lang }}">
                                    {{ translate('messages.Note_For_Customer') }} ({{ strtoupper($lang) }})
                                </label>
                                <textarea name="customer_note[]" id="customer_note_{{ $lang }}" class="form-control surge-count-input surge-note-input" rows="1"
                                    maxlength="300" placeholder="{{ translate('messages.Type_note') }}">{{ $translated[$lang]['customer_note'] ?? '' }}</textarea>
                                <span class="surge-char-count">0/300</span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                </div>
            @endforeach
        </div>

        {{-- ── Zone & Surge Price Setup ────────────────────────────────────
             Here the tinted panel wraps the heading as well as the fields. --}}
        <div class="surge-block surge-section">
            <h4 class="surge-section__title">{{ translate('messages.Zone_&_Surge_Price_Setup') }}</h4>
            <p class="surge-section__subtitle">
                {{ translate('messages.Choose_where_this_surge_pricing_rule_will_be_applied.') }}</p>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-md-0">
                        <label class="input-label" for="zone_id">
                            {{ translate('messages.Choose_Zone') }} <span class="text-danger">*</span>
                            <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                data-original-title="{{ translate('messages.choose_zone_tooltip') }}"><i
                                    class="tio-info"></i></span>
                        </label>
                        <select name="zone_id" id="zone_id" class="form-control js-select2-custom" required
                            data-placeholder="{{ translate('messages.Select_your_business_zone_name') }}">
                            <option value="">{{ translate('messages.Select_your_business_zone_name') }}</option>
                            @foreach ($zones as $zone)
                                <option value="{{ $zone->id }}"
                                    {{ old('zone_id', $surge->zone_id ?? '') == $zone->id ? 'selected' : '' }}>
                                    {{ $zone->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-0">
                        <label class="input-label" for="price">
                            {{ translate('messages.Price_Increase_Rate') }} <span class="text-danger">*</span>
                            <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                data-original-title="{{ translate('messages.price_increase_rate_tooltip') }}"><i
                                    class="tio-info"></i></span>
                        </label>
                        <div class="custom-group-btn border rounded">
                            <div class="flex-sm-grow-1 w-100">
                                <input type="number" name="price" id="price"
                                    class="form-control border-0 pl-unset" placeholder="{{ translate('messages.Ex:_5') }}"
                                    min="0.01" step="0.01" required value="{{ old('price', $surge->price ?? '') }}">
                            </div>
                            <div class="flex-shrink-0">
                                <select name="price_type" id="price_type" class="custom-select ltr border-0">
                                    <option value="percent"
                                        {{ old('price_type', $surge->price_type ?? 'percent') === 'percent' ? 'selected' : '' }}>
                                        %</option>
                                    <option value="amount"
                                        {{ old('price_type', $surge->price_type ?? '') === 'amount' ? 'selected' : '' }}>
                                        {{ \App\CentralLogics\Helpers::currency_symbol() }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Duration Setup ──────────────────────────────────────────────
             A white bordered card, not a tinted panel — the tint moves inside,
             onto the schedule-input panel. --}}
        <div class="surge-block surge-card">
            <h4 class="surge-section__title">{{ translate('messages.Duration_Setup') }}</h4>
            <p class="surge-section__subtitle">
                {{ translate('messages.Configure_when_the_surge_pricing_should_become_active.') }}</p>

            <label class="input-label">
                {{ translate('messages.Surge_Price_Schedule_Type') }}
                <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                    data-original-title="{{ translate('messages.schedule_type_tooltip') }}"><i class="tio-info"></i></span>
            </label>

            <div class="surge-schedule-types">
                @foreach (['daily' => 'Daily_Schedule', 'weekly' => 'Weekly_Schedule', 'custom' => 'Custom_Schedule'] as $value => $label)
                    <label class="form-check form--check">
                        <input class="form-check-input surge-duration-type" type="radio" name="duration_type"
                            value="{{ $value }}" {{ $durationType === $value ? 'checked' : '' }}>
                        <span class="form-check-label">{{ translate('messages.' . $label) }}</span>
                    </label>
                @endforeach
            </div>

            <div class="admin-alert">
                <span class="admin-alert__icon">i</span>
                <span>{{ translate('messages.When_creating_a_surge_price,_the_selected_time_must_not_overlap_with_an_existing_surge_period_in_the_same_zone.') }}</span>
            </div>

            <div class="surge-duration-panel">
                <div class="row align-items-center g-3">
                    <div class="col-md-4">
                        <div class="surge-duration-panel__label">{{ translate('messages.Select_Date_&_Time') }}</div>
                        <p class="surge-duration-panel__hint">
                            {{ translate('messages.Select_your_suitable_time_within_a_time_range_you_want_add_surge_price') }}
                        </p>
                    </div>
                    <div class="col-md-8">
                      <div class="surge-duration-fields">

                        {{-- Daily: a plain date range plus one time window. --}}
                        <div class="surge-schedule-pane" data-pane="daily">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="input-label">{{ translate('messages.Date_Range') }} <span
                                            class="text-danger">*</span>
                                        <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages.daily_date_range_tooltip') }}"><i
                                                class="tio-info"></i></span>
                                    </label>
                                    <div class="position-relative date-range__custom">
                                        <i class="tio-calendar-month icon-absolute-on-right"></i>
                                        <input type="text" name="daily_date_range" id="daily_date_range"
                                            class="form-control h-45 surge-picker-input surge-date-range"
                                            placeholder="{{ translate('messages.Select_Date_Range') }}" readonly
                                            value="{{ $durationType === 'daily' ? $dateRange : '' }}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="input-label">{{ translate('messages.Time_Range') }} <span
                                            class="text-danger">*</span>
                                        <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages.daily_time_range_tooltip') }}"><i
                                                class="tio-info"></i></span>
                                    </label>
                                    <div class="position-relative">
                                        <i class="tio-time icon-absolute-on-right"></i>
                                        <input type="text" name="daily_time_range" id="daily_time_range"
                                            class="form-control h-45 surge-picker-input surge-time-range"
                                            placeholder="{{ translate('messages.Select_Time_Range') }}" readonly
                                            value="{{ $durationType === 'daily' ? $timeRange : '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Weekly: the date range field opens the Select Days modal,
                             which owns the weekday ticks and the permanent flag. --}}
                        <div class="surge-schedule-pane d-none" data-pane="weekly">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="input-label">{{ translate('messages.Date_Range') }} <span
                                            class="text-danger">*</span>
                                        <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages.weekly_date_range_field_tooltip') }}"><i
                                                class="tio-info"></i></span>
                                    </label>
                                    <div class="position-relative date-range__custom cursor-pointer"
                                        data-toggle="modal" data-target="#weeklySelectDaysModal">
                                        <i class="tio-calendar-month icon-absolute-on-right"></i>
                                        <input type="text" name="weekly_date_range" id="weekly_date_range"
                                            class="form-control h-45 surge-picker-input"
                                            placeholder="{{ translate('messages.Select_Date_Range') }}" readonly
                                            value="{{ $durationType === 'weekly' && !($surge->is_permanent ?? false) ? $dateRange : '' }}">
                                    </div>
                                    <p class="fs-12 mt-2 mb-0 weekly-summary {{ $durationType === 'weekly' && count($weeklyDays) ? '' : 'd-none' }}">
                                        {{ translate('messages.Every_week') }}
                                        <span class="font-semibold" id="selected-weekdays-text"></span>
                                    </p>
                                </div>
                                <div class="col-sm-6">
                                    <label class="input-label">{{ translate('messages.Time_Range') }} <span
                                            class="text-danger">*</span>
                                        <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages.weekly_time_range_tooltip') }}"><i
                                                class="tio-info"></i></span>
                                    </label>
                                    <div class="position-relative">
                                        <i class="tio-time icon-absolute-on-right"></i>
                                        <input type="text" name="weekly_time_range" id="weekly_time_range"
                                            class="form-control h-45 surge-picker-input surge-time-range"
                                            placeholder="{{ translate('messages.Select_Time_Range') }}" readonly
                                            value="{{ $durationType === 'weekly' ? $timeRange : '' }}">
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="weekly_days" id="weekly_days"
                                value="{{ implode(',', $weeklyDays) }}">
                            <input type="hidden" name="is_permanent" id="is_permanent"
                                value="{{ $surge->is_permanent ?? 0 }}">
                        </div>

                        {{-- Custom: every chosen date carries its own window, so the
                             modal owns both and this field is just the opener. --}}
                        <div class="surge-schedule-pane d-none" data-pane="custom">
                            <label class="input-label">{{ translate('messages.Date_&_Time_Select') }} <span
                                    class="text-danger">*</span>
                                <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('messages.custom_schedule_tooltip') }}"><i
                                        class="tio-info"></i></span>
                            </label>
                            <div class="position-relative cursor-pointer" data-toggle="modal"
                                data-target="#customScheduleModal">
                                <i class="tio-calendar-month icon-absolute-on-right"></i>
                                <input type="text" id="custom_schedule_input"
                                    class="form-control h-45 surge-picker-input"
                                    placeholder="{{ translate('messages.Select_Date_&_Time') }}"
                                    value="{{ $durationType === 'custom' && count($customDays) ? count($customDays) . ' ' . translate('messages.days_selected') : '' }}"
                                    readonly>
                            </div>
                            <input type="hidden" name="custom_days" id="custom_days"
                                value="{{ implode(',', $customDays) }}">
                            <input type="hidden" name="custom_times" id="custom_times"
                                value="{{ implode(',', $customTimes) }}">

                            <p class="fs-12 mt-3 mb-2 {{ $durationType === 'custom' && count($customDays) ? '' : 'd-none' }}"
                                id="custom-date-range-text">
                                {{ translate('messages.Date_range') }}
                                <span class="font-semibold" id="custom-date-min"></span> -
                                <span class="font-semibold" id="custom-date-max"></span>
                            </p>

                            <div class="table-responsive p-0 date-table {{ $durationType === 'custom' && count($customDays) ? '' : 'd-none' }}"
                                id="custom-schedule-table">
                                <table class="table m-0 table-borderless table-thead-bordered table-align-middle">
                                    <thead class="thead-light border-0">
                                        <tr>
                                            <th class="border-0">{{ translate('messages.SL') }}</th>
                                            <th class="border-0">{{ translate('messages.Title') }}</th>
                                            <th class="border-0 text-center">{{ translate('messages.Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="customScheduleTableBody"></tbody>
                                </table>
                            </div>
                        </div>

                      </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="card-footer border-0">
        <div class="btn--container justify-content-end">
            <button type="reset" id="reset_btn" class="btn btn--reset min-w-120px">
                {{ translate('messages.Reset') }}</button>
            <button type="submit" class="btn btn--primary min-w-120px">{{ translate('messages.Save') }}</button>
        </div>
    </div>
</div>

@include('admin-views.surge-price.partials._modals', ['weekdayOrder' => $weekdayOrder, 'weeklyDays' => $weeklyDays, 'surge' => $surge])
