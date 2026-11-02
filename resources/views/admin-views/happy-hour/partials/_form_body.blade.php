@php($hh = $happyHour ?? null)
@php($language=\App\Models\BusinessSetting::where('key','language')->first())
@php($language = $language->value ?? null)

<div class="card mb-3">
    <div class="card-body">
        <h5>{{translate('messages.Create_Happy_Hour')}}</h5>
        <p class="opacity-75">{{translate('messages.Enter_the_Happy_Hour_name_add_a_short_description_and_upload_the_cover_image_and_icon_to_help_customers_recognize_the_offer')}}</p>

        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="bg-light rounded p-3 h-100">
                    @if($language)
                        <div class="js-nav-scroller hs-nav-scroller-horizontal">
                            <ul class="nav nav-tabs mb-3">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active" href="#" id="default-link">{{ translate('Default') }}</a>
                                </li>
                                @foreach(json_decode($language) as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link" href="#" id="{{$lang}}-link">{{\App\CentralLogics\Helpers::get_language_name($lang).'('.strtoupper($lang).')'}}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="lang_form" id="default-form">
                            <div class="form-group">
                                <label class="input-label">
                                    {{translate('messages.Happy_Hour_Title')}} ({{ translate('Default') }}) <span class="text-danger">*</span>
                                    <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                          data-original-title="{{translate('messages.The_name_customers_will_see_for_this_Happy_Hour')}}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <input type="text" name="title[]" class="form-control h--45px" maxlength="100"
                                       data-counter="count_title_default" placeholder="{{ translate('messages.Type_Title') }}"
                                       value="{{ $hh?->getRawOriginal('title') }}">
                                <small class="d-block text-right opacity-75">
                                    <span id="count_title_default">{{ strlen($hh?->getRawOriginal('title') ?? '') }}</span>/100
                                </small>
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                            <div class="form-group mb-0">
                                <label class="input-label">
                                    {{translate('messages.Short_Description')}} ({{ translate('Default') }})
                                    <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                          data-original-title="{{translate('messages.A_short_line_explaining_what_this_Happy_Hour_offers')}}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <textarea name="short_description[]" class="form-control" maxlength="150"
                                          data-counter="count_desc_default"
                                          placeholder="{{ translate('messages.Type_about_the_description') }}">{{ $hh?->getRawOriginal('short_description') }}</textarea>
                                <small class="d-block text-right opacity-75">
                                    <span id="count_desc_default">{{ strlen($hh?->getRawOriginal('short_description') ?? '') }}</span>/150
                                </small>
                            </div>
                        </div>

                        @foreach(json_decode($language) as $lang)
                            <?php
                                $translate = [];
                                if ($hh && count($hh['translations'])) {
                                    foreach ($hh['translations'] as $t) {
                                        if ($t->locale == $lang && $t->key == 'title') {
                                            $translate[$lang]['title'] = $t->value;
                                        }
                                        if ($t->locale == $lang && $t->key == 'short_description') {
                                            $translate[$lang]['short_description'] = $t->value;
                                        }
                                    }
                                }
                            ?>
                            <div class="d-none lang_form" id="{{$lang}}-form">
                                <div class="form-group">
                                    <label class="input-label">
                                        {{translate('messages.Happy_Hour_Title')}} ({{strtoupper($lang)}})
                                        <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                              data-original-title="{{translate('messages.The_name_customers_will_see_for_this_Happy_Hour')}}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input type="text" name="title[]" class="form-control h--45px" maxlength="100"
                                           data-counter="count_title_{{$lang}}" placeholder="{{ translate('messages.Type_Title') }}"
                                           value="{{ $translate[$lang]['title'] ?? '' }}">
                                    <small class="d-block text-right opacity-75">
                                        <span id="count_title_{{$lang}}">{{ strlen($translate[$lang]['title'] ?? '') }}</span>/100
                                    </small>
                                </div>
                                <input type="hidden" name="lang[]" value="{{$lang}}">
                                <div class="form-group mb-0">
                                    <label class="input-label">
                                        {{translate('messages.Short_Description')}} ({{strtoupper($lang)}})
                                        <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                              data-original-title="{{translate('messages.A_short_line_explaining_what_this_Happy_Hour_offers')}}">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <textarea name="short_description[]" class="form-control" maxlength="150"
                                              data-counter="count_desc_{{$lang}}"
                                              placeholder="{{ translate('messages.Type_about_the_description') }}">{{ $translate[$lang]['short_description'] ?? '' }}</textarea>
                                    <small class="d-block text-right opacity-75">
                                        <span id="count_desc_{{$lang}}">{{ strlen($translate[$lang]['short_description'] ?? '') }}</span>/150
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div id="default-form">
                            <div class="form-group">
                                <label class="input-label">{{translate('messages.Happy_Hour_Title')}} <span class="text-danger">*</span></label>
                                <input type="text" name="title[]" class="form-control h--45px" maxlength="100"
                                       data-counter="count_title_default" placeholder="{{ translate('messages.Type_Title') }}"
                                       value="{{ $hh?->getRawOriginal('title') }}">
                                <small class="d-block text-right opacity-75">
                                    <span id="count_title_default">{{ strlen($hh?->getRawOriginal('title') ?? '') }}</span>/100
                                </small>
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                            <div class="form-group mb-0">
                                <label class="input-label">{{translate('messages.Short_Description')}}</label>
                                <textarea name="short_description[]" class="form-control" maxlength="150"
                                          data-counter="count_desc_default"
                                          placeholder="{{ translate('messages.Type_about_the_description') }}">{{ $hh?->getRawOriginal('short_description') }}</textarea>
                                <small class="d-block text-right opacity-75">
                                    <span id="count_desc_default">{{ strlen($hh?->getRawOriginal('short_description') ?? '') }}</span>/150
                                </small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- The shared uploader the food form uses, so both screens drag, preview,
                 validate size and reset the same way. isRequired is true on both create
                 and edit: the partial then asks for a file only when there is no stored
                 one (create), and hides the remove button, which is right here because
                 the controller only ever replaces an image - it has no path to clear one. --}}
            @foreach([
                ['key' => 'cover_image', 'input' => 'cover-input', 'title' => 'Cover_image', 'sub' => 'Upload_Happy_Hour_Cover_image', 'ratio' => '3:1'],
                ['key' => 'icon', 'input' => 'icon-input', 'title' => 'Icon', 'sub' => 'Upload_Happy_Hour_Icon', 'ratio' => '1:1'],
            ] as $img)
                <div class="col-md-3 mb-3">
                    <div class="bg-light rounded p-3 h-100 text-center d-flex flex-column">
                        <h6 class="mb-1">{{translate('messages.'.$img['title'])}}</h6>
                        <p class="opacity-75 font-size-sm mb-3">{{translate('messages.'.$img['sub'])}}</p>

                        {{-- Guarded on the column, not on the accessor: *_full_url falls back
                             to a placeholder, which the uploader would then show as though a
                             real image had been saved. --}}
                        @php($existing = $hh && $hh->{$img['key']}
                            ? ($img['key'] === 'icon' ? $hh->icon_full_url : $hh->cover_image_full_url)
                            : null)

                        @include('admin-views.partials._image-uploader', [
                            'id' => $img['input'],
                            'name' => $img['key'],
                            'isRequired' => true,
                            'existingImage' => $existing,
                            'defaultImage' => $existing,
                            'ratio' => $img['ratio'],
                            'maxSize' => MAX_FILE_SIZE,
                            'imageExtension' => IMAGE_EXTENSION,
                            'imageFormat' => IMAGE_FORMAT,
                        ])
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h5>{{translate('messages.Discount_Setup')}}</h5>
        <p class="opacity-75">{{translate('messages.Set_the_discount_percentage_and_if_needed_define_a_minimum_order_amount_required_to_apply_the_offer')}}</p>

        <div class="bg-light rounded p-3">
            {{-- Zone is not in the design but the overlap rule is zone scoped, so the offer
                 cannot be saved without one. --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="input-label">
                            {{translate('messages.Zone')}} <span class="text-danger">*</span>
                            <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                  data-original-title="{{translate('messages.Two_happy_hours_in_the_same_zone_cannot_overlap_in_time')}}">
                                <i class="tio-info text-gray1 fs-16"></i>
                            </span>
                        </label>
                        <select name="zone_id" id="zone_id" class="form-control h--45px" required>
                            <option value="" disabled {{ $hh ? '' : 'selected' }}>{{translate('messages.Select_Zone')}}</option>
                            @foreach($zones as $zone)
                                <option value="{{$zone->id}}" {{ $hh && $hh->zone_id == $zone->id ? 'selected' : '' }}>{{$zone->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-md-0">
                        <label class="input-label">
                            {{translate('messages.Discount')}} <span class="text-danger">*</span>
                            <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                  data-original-title="{{translate('messages.Percentage_taken_off_the_order_while_the_Happy_Hour_runs')}}">
                                <i class="tio-info text-gray1 fs-16"></i>
                            </span>
                        </label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" name="discount" class="form-control h--45px"
                                   placeholder="{{translate('Ex: 5')}}" value="{{ $hh?->discount }}" required>
                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-0">
                        {{-- Toggle sits at the far end of the row, per design. --}}
                        <div class="d-flex align-items-center justify-content-between">
                            <label class="input-label mb-0">
                                {{translate('messages.Min_Order_Amount')}}({{\App\CentralLogics\Helpers::currency_symbol()}})
                                <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                      data-original-title="{{translate('messages.Orders_below_this_amount_will_not_get_the_discount')}}">
                                    <i class="tio-info text-gray1 fs-16"></i>
                                </span>
                            </label>
                            <label class="toggle-switch toggle-switch-sm mb-0" for="min_order_toggle">
                                <input type="checkbox" class="toggle-switch-input" id="min_order_toggle" {{ $hh && $hh->min_order_amount ? 'checked' : '' }}>
                                <span class="toggle-switch-label"><span class="toggle-switch-indicator"></span></span>
                            </label>
                        </div>
                        {{-- The toggle itself is not posted, so its state travels here and the server can
                             require an amount when it is on rather than saving a minimum of nothing. --}}
                        <input type="hidden" name="min_order_enabled" id="min_order_enabled"
                               value="{{ $hh && $hh->min_order_amount ? 1 : 0 }}">
                        <input type="number" step="0.01" min="0" name="min_order_amount" id="min_order_amount"
                               class="form-control h--45px mt-1" placeholder="{{translate('Ex: 50')}}" value="{{ $hh?->min_order_amount }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h5>{{translate('messages.Schedule_&_Duration_Setup')}}</h5>
        <p class="opacity-75">{{translate('messages.Choose_when_the_Happy_Hour_offer_will_run_by_selecting_a_schedule_type_date_range_and_time_range')}}</p>

        <div class="form-group">
            <label class="input-label">
                {{translate('messages.Offer_Schedule_Type')}} <span class="text-danger">*</span>
                <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                      data-original-title="{{translate('messages.Daily_repeats_every_day_Weekly_repeats_on_chosen_weekdays_Custom_picks_individual_dates')}}">
                    <i class="tio-info text-gray1 fs-16"></i>
                </span>
            </label>
            <div class="row border rounded mx-0 py-3">
                @foreach(['daily' => 'Daily_Schedule', 'weekly' => 'Weekly_Schedule', 'custom' => 'Custom_Schedule'] as $value => $label)
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input duration-type" type="radio" name="duration_type" value="{{$value}}"
                                   id="duration_{{$value}}" {{ ($hh?->duration_type ?? 'daily') === $value ? 'checked' : '' }}>
                            <label class="form-check-label" for="duration_{{$value}}">{{translate('messages.'.$label)}}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="alert alert-soft-warning alert--note d-flex gap-2">
            <i class="tio-error"></i>
            <span>{{translate('messages.When_creating_a_Happy_Hour_the_selected_time_must_not_overlap_with_an_existing_happy_hour_in_the_same_zone')}}</span>
        </div>

        {{-- Daily: pick a range and a start time; every date in the range gets that window. --}}
        <div class="bg-light rounded p-3 daily-block d-none">
            <div class="row align-items-center">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h6 class="mb-1">{{translate('messages.Select_Date_&_Time')}}</h6>
                    <p class="opacity-75 font-size-sm mb-0">{{translate('messages.Select_your_suitable_time_within_a_time_range')}}</p>
                </div>
                <div class="col-md-8">
                    <div class="bg-white rounded p-3">
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="input-label">
                                    {{translate('messages.Date_Range')}} <span class="text-danger">*</span>
                                    <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                          data-original-title="{{translate('messages.The_first_and_last_day_this_Happy_Hour_runs')}}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <div class="hh-field cursor-pointer">
                                    <input type="text" id="daily_range_display" class="cursor-pointer"
                                           placeholder="{{translate('messages.Select_Date_Range')}}" readonly>
                                    <i class="tio-calendar"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="input-label">
                                    {{translate('messages.Time_Range')}} <span class="text-danger">*</span>
                                    <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                          data-original-title="{{translate('messages.Pick_the_start_time_the_window_always_ends_one_hour_later')}}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                {{-- Only the start is editable: the end is always start + 1h. --}}
                                <div class="hh-field hh-time cursor-pointer">
                                    <span class="hh-time__placeholder">{{translate('messages.Select_Time_Range')}}</span>
                                    <span class="hh-time__value d-none">
                                        <input type="time" max="22:59" id="daily_start_time" class="start-time">
                                        <span>-</span>
                                        <span class="hh-time__end derived-end"></span>
                                    </span>
                                    <i class="tio-time"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Weekly: the weekday set, range and permanent flag all live in the Select Days modal. --}}
        <div class="bg-light rounded p-3 weekly-block d-none">
            <div class="row align-items-center">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h6 class="mb-1">{{translate('messages.Select_Date_&_Time')}}</h6>
                    <p class="opacity-75 font-size-sm mb-0">{{translate('messages.Select_your_suitable_time_within_a_time_range')}}</p>
                </div>
                <div class="col-md-8">
                    <div class="bg-white rounded p-3">
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="input-label">
                                    {{translate('messages.Date_Range')}} <span class="text-danger">*</span>
                                    <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                          data-original-title="{{translate('messages.Opens_the_day_picker_where_the_weekdays_and_range_are_chosen')}}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <div class="hh-field cursor-pointer" id="open_weekly_modal">
                                    <input type="text" id="weekly_range_display" class="cursor-pointer"
                                           placeholder="{{translate('messages.Select_Date_Range')}}" readonly>
                                    <i class="tio-calendar"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="input-label">
                                    {{translate('messages.Time_Range')}} <span class="text-danger">*</span>
                                    <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                          data-original-title="{{translate('messages.Pick_the_start_time_the_window_always_ends_one_hour_later')}}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <div class="hh-field hh-time cursor-pointer">
                                    <span class="hh-time__placeholder">{{translate('messages.Select_Time_Range')}}</span>
                                    <span class="hh-time__value d-none">
                                        <input type="time" max="22:59" id="weekly_start_time" class="start-time"
                                               value="{{ $hh && $hh->start_time ? substr($hh->start_time, 0, 5) : '' }}">
                                        <span>-</span>
                                        <span class="hh-time__end derived-end"></span>
                                    </span>
                                    <i class="tio-time"></i>
                                </div>
                            </div>
                        </div>

                        <p class="text-center mb-0 mt-3" id="weekly_summary"></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Custom: individual dates, each with its own start time. --}}
        <div class="bg-light rounded p-3 custom-block d-none">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h6 class="mb-1">{{translate('messages.Select_Date_&_Time')}}</h6>
                    <p class="opacity-75 font-size-sm mb-0">{{translate('messages.Select_your_suitable_time_within_a_time_range')}}</p>
                </div>
                <div class="col-md-8">
                    <div class="bg-white rounded p-3 mb-3">
                        <label class="input-label">
                            {{translate('messages.Date_&_Time_Range')}} <span class="text-danger">*</span>
                            <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                  data-original-title="{{translate('messages.Opens_the_calendar_where_each_date_gets_its_own_start_time')}}">
                                <i class="tio-info text-gray1 fs-16"></i>
                            </span>
                        </label>
                        <div class="hh-field cursor-pointer" id="open_custom_modal">
                            <input type="text" id="custom_range_display" class="cursor-pointer"
                                   placeholder="{{translate('messages.Select_Date_&_Time_Range')}}" readonly>
                            <i class="tio-calendar"></i>
                        </div>
                    </div>

                    {{-- Whole block stays out of the way until dates exist, otherwise the
                         white panel shows as an empty strip under a "0 days selected". --}}
                    <div id="custom_summary_wrap" class="d-none">
                        {{-- Count is dark, the label beside it is muted, per design. --}}
                        <p class="mb-2">
                            <strong id="custom_count">0</strong>
                            <span class="opacity-75">{{translate('messages.days_selected')}}</span>
                        </p>
                        <div class="bg-white rounded-8 py-2" id="custom_summary_list"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Populated by the schedule scripts; these are what the controller reads. --}}
        <input type="hidden" name="date_range" id="date_range_input">
        <input type="hidden" name="start_time" id="start_time_input">
        <input type="hidden" name="weekly_days" id="weekly_days_input">
        <input type="hidden" name="is_permanent" id="is_permanent_input" value="0">
        <input type="hidden" name="custom_days" id="custom_days_input">
        <input type="hidden" name="custom_times" id="custom_times_input">

        <div class="alert alert-soft-info alert--note d-flex gap-2 mt-3 mb-0">
            <img src="{{ dynamicAsset('assets/admin/img/svg/bulb.svg') }}" class="svg flex-shrink-0" alt="">
            <span>{{translate('messages.The_Happy_Hour_offer_will_only_be_available_during_the_selected_schedule')}}</span>
        </div>
    </div>
</div>
