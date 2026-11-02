{{-- Weekly schedule: pick the weekdays, then either bound the cycle with a date
     range or tick "permanently" to run it with no end date. --}}
<div class="modal shedule-modal fade" id="weeklySelectDaysModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="title-clr mb-0">{{ translate('messages.Select_Days') }}</h3>
                    <p class="fs-12 m-0">{{ translate('messages.Your_Surge_price_active_date') }}</p>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-body-inner">
                    <div class="surge-section mb-3">
                        <div class="surge-days-grid">
                            @foreach ($weekdayOrder as $day)
                                <label class="form-check form--check">
                                    <input class="form-check-input rounded weekly-day-check" type="checkbox"
                                        value="{{ $day }}" {{ in_array($day, $weeklyDays) ? 'checked' : '' }}>
                                    <span class="form-check-label">{{ translate('messages.' . $day) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="surge-section mb-0">
                        <h5 class="surge-section__title">{{ translate('messages.Date_Range') }}</h5>
                        <p class="surge-section__subtitle">
                            {{ translate('messages.Select_the_date_range_you_want_to_repeat_this_cycle_every_week') }}
                        </p>

                        <div class="form-group">
                            <label class="input-label">{{ translate('messages.Date_Range') }} <span
                                    class="text-danger">*</span>
                                <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ translate('messages.weekly_date_range_tooltip') }}"><i
                                        class="tio-info"></i></span>
                            </label>
                            <div class="position-relative date-range__custom">
                                <i class="tio-calendar-month icon-absolute-on-right"></i>
                                <input type="text" id="weekly_modal_date"
                                    class="form-control h-45 surge-picker-input surge-date-range"
                                    placeholder="{{ translate('messages.Select_date') }}" readonly
                                    value="{{ ($surge?->duration_type === 'weekly' && !$surge?->is_permanent && $surge?->start_date) ? \Carbon\Carbon::parse($surge->start_date)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($surge->end_date)->format('d M Y') : '' }}"
                                    {{ $surge?->is_permanent ? 'disabled' : '' }}>
                            </div>
                        </div>

                        <label class="form-check form--check mb-0">
                            <input class="form-check-input rounded" type="checkbox" value="1"
                                id="weekly_is_permanent" {{ $surge?->is_permanent ? 'checked' : '' }}>
                            <span
                                class="form-check-label">{{ translate('messages.Assign_this_surge_price_permanently') }}</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-end border-0 gap-2">
                <button type="button" class="btn btn--reset min-w-120px"
                    data-dismiss="modal">{{ translate('messages.No') }}</button>
                <button type="button" class="btn btn--primary min-w-120px"
                    id="weekly_days_save">{{ translate('messages.Save') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Custom schedule: hand-pick individual dates on the calendar; each picked
     date gets its own time window in the list on the right. --}}
<div class="modal shedule-modal fade" id="customScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="title-clr mb-0">{{ translate('messages.Select_Days_&_Time') }}</h3>
                    <p class="fs-12 m-0">{{ translate('messages.Your_Surge_price_active_date') }}</p>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="surge-calendar">
                            <div class="d-flex align-items-center gap-4 justify-content-center mb-3">
                                <span class="calendar-prev cursor-pointer"><i class="tio-chevron-left fs-24"></i></span>
                                <div class="header-display">
                                    <p class="display fs-14 font-semibold mb-0"></p>
                                </div>
                                <span class="calendar-next cursor-pointer"><i
                                        class="tio-chevron-right fs-24"></i></span>
                            </div>
                            <div class="week">
                                <div>{{ translate('messages.Sun') }}</div>
                                <div>{{ translate('messages.Mon') }}</div>
                                <div>{{ translate('messages.Tue') }}</div>
                                <div>{{ translate('messages.Wed') }}</div>
                                <div>{{ translate('messages.Thu') }}</div>
                                <div>{{ translate('messages.Fri') }}</div>
                                <div>{{ translate('messages.Sat') }}</div>
                            </div>
                            <div class="days"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="selected-listall">
                            <h5 class="mb-3">{{ translate('messages.Selected_Days_List') }}</h5>

                            {{-- Column headings only earn their space once there
                                 is a row beneath them; JS toggles this with the
                                 empty state below. --}}
                            {{-- No `d-flex` here: Bootstrap declares it after
                                 `.d-none` and both are !important, so the row
                                 could never be hidden. Layout lives in the CSS. --}}
                            <div class="mb-2 selected-list-head">
                                <span
                                    class="fs-12 text-title opacity-50 text-uppercase min-w-110px">{{ translate('messages.Date') }}</span>
                                <span
                                    class="fs-12 text-title opacity-50 text-uppercase">{{ translate('messages.Time') }}</span>
                            </div>

                            <div class="selected-list-inner d-flex flex-column gap-2"></div>

                            <div class="selected-list-empty">
                                <img src="{{ dynamicAsset('assets/admin/img/empty.png') }}" alt="">
                                <h6>{{ translate('messages.No_dates_selected_yet') }}</h6>
                                <p>{{ translate('messages.Pick_a_date_from_the_calendar_to_add_it_here.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-end border-0 gap-2">
                <button type="button" class="btn btn--reset min-w-120px"
                    data-dismiss="modal">{{ translate('messages.No') }}</button>
                <button type="button" class="btn btn--primary min-w-120px"
                    id="custom_schedule_save">{{ translate('messages.Save') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Retimes one row of the custom recap table without reopening the calendar. --}}
<div class="modal shedule-modal fade" id="editTimeRangeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="title-clr mb-0" id="edit-time-date-label"></h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="surge-section mb-0">
                    <label class="input-label">{{ translate('messages.Change_Time') }}</label>
                    <div class="position-relative">
                        <i class="tio-time icon-absolute-on-right"></i>
                        <input type="text" id="edit-time-range-input"
                            class="form-control h-45 surge-picker-input surge-time-range"
                            placeholder="{{ translate('messages.Select_Time_Range') }}" readonly>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-end border-0 gap-2">
                <button type="button" class="btn btn--reset min-w-120px"
                    data-dismiss="modal">{{ translate('messages.Cancel') }}</button>
                <button type="button" class="btn btn--primary min-w-120px"
                    id="update-time-btn">{{ translate('messages.Update') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Shown when the server rejects the save with a 409 because the requested
     window collides with an existing surge in the same zone. --}}
<div class="modal fade" id="surge-overlap-modal">
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
                        <img class="mb-20" src="{{ dynamicAsset('assets/admin/img/modal-error.png') }}" alt="">
                        <h5 class="modal-title mb-3">{{ translate('messages.Surge_Price_setup_Overlap!') }}</h5>
                    </div>
                    <div class="text-center">
                        <p>{{ translate('messages.This_surge_price_overlaps_with_another._Please_change_the_zone_or_reschedule_to_fix_it.') }}
                        </p>
                    </div>
                    <div class="btn--container justify-content-center">
                        <button type="button" class="btn btn--primary min-w-120px"
                            data-dismiss="modal">{{ translate('messages.Okay') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
