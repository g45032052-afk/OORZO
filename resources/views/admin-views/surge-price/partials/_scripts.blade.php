<script src="{{ dynamicAsset('assets/admin/js/moment.min.js') }}"></script>
<script src="{{ dynamicAsset('assets/admin/js/daterangepicker.min.js') }}"></script>

<script>
    'use strict';

    (function () {
        // Display formats. The server parses whatever these produce with
        // strtotime(), but they must stay consistent with the values the blade
        // prefills on edit or the pickers will not read their own output back.
        const DATE_FORMAT = 'DD MMM YYYY';
        const TIME_FORMAT = 'hh:mm A';
        const VALUE_DATE_FORMAT = 'YYYY-MM-DD';

        const LANG = {
            selectOneDay: @json(translate('messages.Please_select_at_least_one_day.')),
            selectDateRange: @json(translate('messages.Please_select_a_date_range.')),
            selectDateAndTime: @json(translate('messages.Please_select_both_date_and_time_for_all_custom_entries.')),
            selectTime: @json(translate('messages.Please_select_a_time.')),
            daysSelected: @json(translate('messages.days_selected')),
            selectDateTime: @json(translate('messages.Select_Date_&_Time')),
            unexpected: @json(translate('messages.An_unexpected_error_occurred.')),
            sessionExpired: @json(translate('messages.Your_session_has_expired._Please_log_in_again.')),
            loginUrl: @json(route('home')),
            noteRequired: @json(translate('messages.customer_note_required_when_shown')),
            clear: @json(translate('messages.Clear')),
            cancel: @json(translate('messages.Cancel')),
            apply: @json(translate('messages.Apply')),
        };

        /* ── pickers ──────────────────────────────────────────────────────── */

        // Tags a picker's popover so surge-price.css can size it to its own
        // contents. Applied at construction, not on show, so the panel is
        // already the right width when the library positions it.
        function tagPicker($input) {
            const picker = $input.data('daterangepicker');
            if (picker && picker.container) {
                picker.container.addClass('surge-drp');
            }
        }

        function initDateRangePicker($input) {
            $input.daterangepicker({
                autoUpdateInput: false,
                minDate: moment().startOf('day'),
                locale: {
                    format: DATE_FORMAT,
                    cancelLabel: LANG.clear,
                    applyLabel: LANG.apply,
                },
            });

            tagPicker($input);

            $input.on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format(DATE_FORMAT) + ' - ' + picker.endDate.format(DATE_FORMAT));
            });

            $input.on('cancel.daterangepicker', function () {
                $(this).val('');
            });
        }

        /**
         * The window a newly picked date starts with, so a date is never added
         * with an empty time. Runs from the current hour to the next, which is
         * both a sensible surge window and a valid one — the server rejects any
         * range whose end is not after its start.
         */
        function defaultTimeRange() {
            const start = moment().startOf('hour');
            return start.format(TIME_FORMAT) + ' - ' + start.clone().add(1, 'hour').format(TIME_FORMAT);
        }

        // A time-range picker is a daterangepicker with its calendars hidden by
        // the .calendar-table-custom rule, leaving only the hour/minute selects.
        // Both .drp-calendar halves stay visible: the left holds the start-time
        // selects, the right the end-time selects.
        function initTimeRangePicker($input) {
            $input.daterangepicker({
                timePicker: true,
                timePicker24Hour: false,
                timePickerIncrement: 5,
                autoUpdateInput: false,
                locale: {
                    format: TIME_FORMAT,
                    cancelLabel: LANG.cancel,
                    applyLabel: LANG.apply,
                },
            });

            tagPicker($input);

            const picker = $input.data('daterangepicker');
            if (picker && picker.container) {
                picker.container.addClass('calendar-table-custom');
            }

            // Open on whatever the field already shows, so reopening a row's
            // picker does not silently reset it to "now".
            syncPickerToValue($input);

            $input.on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format(TIME_FORMAT) + ' - ' + picker.endDate.format(TIME_FORMAT));
            });
        }

        function syncPickerToValue($input) {
            const picker = $input.data('daterangepicker');
            const value = ($input.val() || '').trim();

            if (!picker || !value.includes(' - ')) {
                return;
            }

            const [start, end] = value.split(' - ');
            const from = moment(start.trim(), TIME_FORMAT);
            const to = moment(end.trim(), TIME_FORMAT);

            if (from.isValid() && to.isValid()) {
                picker.setStartDate(from);
                picker.setEndDate(to);
            }
        }

        $('.surge-date-range').each(function () { initDateRangePicker($(this)); });
        $('.surge-time-range').each(function () { initTimeRangePicker($(this)); });

        /* ── schedule type panes ──────────────────────────────────────────── */

        function syncSchedulePanes() {
            const active = $('.surge-duration-type:checked').val();
            $('.surge-schedule-pane').addClass('d-none');
            $('.surge-schedule-pane[data-pane="' + active + '"]').removeClass('d-none');
            syncScheduleRequired(active);
        }

        // Only the pane on screen can be filled in, so only its fields are required - a
        // hidden pane left required would block the save over a field nobody can see, with
        // the error drawn somewhere off the form. The weekly period is the one exception:
        // ticking permanent removes the period entirely, so there is nothing to demand.
        function syncScheduleRequired(active) {
            $('.surge-schedule-pane').find('input[type="text"]').each(function () {
                $(this).prop('required', false).removeClass('is-invalid');

                if (window.FormValidation) {
                    FormValidation.clearError(this);
                }
            });

            if (active === 'daily') {
                $('#daily_date_range, #daily_time_range').prop('required', true);
            } else if (active === 'weekly') {
                $('#weekly_time_range').prop('required', true);
                $('#weekly_date_range').prop('required', $('#is_permanent').val() != 1);
            } else if (active === 'custom') {
                $('#custom_schedule_input').prop('required', true);
            }
        }

        $('.surge-duration-type').on('change', syncSchedulePanes);
        syncSchedulePanes();

        /* ── weekly: Select Days modal ────────────────────────────────────── */

        function formatDayList(days) {
            if (days.length === 0) return '';
            if (days.length === 1) return days[0];
            return days.slice(0, -1).join(', ') + ' & ' + days[days.length - 1];
        }

        // Ticking "permanently" removes the bound period entirely, so the date
        // input is cleared and locked rather than left holding a stale range.
        function syncPermanentState() {
            const permanent = $('#weekly_is_permanent').is(':checked');
            const $date = $('#weekly_modal_date');

            $date.prop('disabled', permanent);
            if (permanent) {
                $date.val('');
                $('#weekly_date_range').val('');
            }
        }

        $('#weekly_is_permanent').on('change', syncPermanentState);
        syncPermanentState();

        $('#weekly_days_save').on('click', function () {
            const days = $('.weekly-day-check:checked').map(function () { return $(this).val(); }).get();
            const permanent = $('#weekly_is_permanent').is(':checked') ? 1 : 0;
            const dateRange = $('#weekly_modal_date').val();

            if (days.length === 0) {
                toastr.error(LANG.selectOneDay);
                return;
            }
            if (!permanent && !dateRange) {
                toastr.error(LANG.selectDateRange);
                return;
            }

            $('#weekly_days').val(days.join(','));
            $('#is_permanent').val(permanent);
            $('#weekly_date_range').val(permanent ? '' : dateRange);

            $('#selected-weekdays-text').text(formatDayList(days));
            $('.weekly-summary').toggleClass('d-none', days.length === 0);

            // Permanent may have just been ticked or cleared, and that is what decides
            // whether the period field is demanded.
            syncSchedulePanes();

            $('#weeklySelectDaysModal').modal('hide');
        });

        // Reseed the modal from the committed hidden inputs every time it opens, so the ticks,
        // the date range and the permanent flag always show the last saved selection. Dismissing
        // with No therefore leaves nothing behind - the next open discards it.
        $('#weeklySelectDaysModal').on('show.bs.modal', function () {
            const committed = ($('#weekly_days').val() || '').split(',').filter(Boolean);

            $('.weekly-day-check').each(function () {
                $(this).prop('checked', committed.indexOf($(this).val()) !== -1);
            });

            $('#weekly_is_permanent').prop('checked', $('#is_permanent').val() == 1);
            $('#weekly_modal_date').val($('#weekly_date_range').val() || '');
            syncPermanentState();
        });

        // Restore the summary line when editing an existing weekly rule.
        (function primeWeeklySummary() {
            const days = ($('#weekly_days').val() || '').split(',').filter(Boolean);
            if (days.length) {
                $('#selected-weekdays-text').text(formatDayList(days));
                $('.weekly-summary').removeClass('d-none');
            }
        })();

        /* ── custom: calendar + picked-dates list ─────────────────────────── */

        const $selectedList = $('.selected-list-inner');
        const $days = $('.days');
        const $display = $('.display');

        // Keyed by YYYY-MM-DD so a date can only ever be picked once.
        const selectedDates = new Map();

        let viewMonth = moment().startOf('month');

        function renderCalendar() {
            $display.text(viewMonth.format('MMMM YYYY'));
            $days.empty();

            const firstWeekday = viewMonth.day(); // 0 = Sunday, matching the header
            const daysInMonth = viewMonth.daysInMonth();
            const today = moment().startOf('day');

            for (let i = 0; i < firstWeekday; i++) {
                $days.append($('<div></div>'));
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const date = viewMonth.clone().date(day);
                const key = date.format(VALUE_DATE_FORMAT);
                const $cell = $('<div></div>').text(day).attr('data-date', key);

                // Past dates cannot be scheduled, but a date already saved on
                // this rule stays visible so an old rule can still be edited.
                if (date.isBefore(today) && !selectedDates.has(key)) {
                    $cell.addClass('disabled');
                } else if (selectedDates.has(key)) {
                    $cell.addClass('active');
                }

                $days.append($cell);
            }
        }

        function addSelectedRow(dateKey, timeRange) {
            const label = moment(dateKey, VALUE_DATE_FORMAT).format('ddd, MMM DD');

            const $row = $(
                '<div class="selected-list-item d-flex align-items-center gap-2 py-2 px-2">' +
                '<span class="fs-12 text-title text-nowrap after-date"></span>' +
                '<div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-end">' +
                '<div class="position-relative cursor-pointer flex-grow-1">' +
                '<i class="tio-time icon-absolute-on-right fs-12"></i>' +
                '<input type="text" class="form-control fs-12 h-32px bg-white surge-row-time" readonly>' +
                '</div>' +
                '<button type="button" class="removeDay btn p-0 text-danger">' +
                '<i class="tio-clear-circle-outlined fs-20"></i></button>' +
                '</div>' +
                '</div>'
            );

            $row.attr('data-date', dateKey);
            $row.find('.after-date').text(label);

            const $time = $row.find('.surge-row-time');
            // Never render an empty window — a date is stored with the default
            // the moment it is picked, so this only backfills legacy rows.
            $time.val(timeRange || defaultTimeRange());
            initTimeRangePicker($time);

            // Held in memory only. Committing here would write the edit straight to the
            // hidden inputs, so a later "No" would have nothing left to roll back.
            $time.on('apply.daterangepicker', function () {
                selectedDates.set(dateKey, $(this).val());
            });

            $row.find('.removeDay').on('click', function () {
                selectedDates.delete(dateKey);
                $row.remove();
                $days.find('div[data-date="' + dateKey + '"]').removeClass('active');
                syncSelectedListEmptyState();
            });

            $selectedList.append($row);
        }

        /**
         * Swaps the column headings for the empty state, so an untouched panel
         * explains itself instead of showing two headings over nothing.
         */
        function syncSelectedListEmptyState() {
            const isEmpty = selectedDates.size === 0;

            $('.selected-list-head').toggleClass('d-none', isEmpty);
            $('.selected-list-empty').toggleClass('is-visible', isEmpty);
        }

        // Rows are kept in date order so the list reads like a schedule.
        function rebuildSelectedList() {
            $selectedList.empty();
            Array.from(selectedDates.keys()).sort().forEach(function (key) {
                addSelectedRow(key, selectedDates.get(key));
            });
            syncSelectedListEmptyState();
        }

        $days.on('click', 'div[data-date]', function () {
            const key = $(this).data('date');
            if (selectedDates.has(key)) {
                return;
            }
            // Seed the window so the row is immediately valid and the admin only
            // has to touch the times they actually want to change.
            selectedDates.set(key, defaultTimeRange());
            $(this).addClass('active');
            rebuildSelectedList();
        });

        $('.calendar-prev').on('click', function () {
            viewMonth = viewMonth.clone().subtract(1, 'month');
            renderCalendar();
        });

        $('.calendar-next').on('click', function () {
            viewMonth = viewMonth.clone().add(1, 'month');
            renderCalendar();
        });

        function renderCustomRecapTable() {
            const $body = $('#customScheduleTableBody');
            $body.empty();

            const keys = Array.from(selectedDates.keys()).sort();

            keys.forEach(function (key, index) {
                const label = moment(key, VALUE_DATE_FORMAT).format('ddd, MMM DD');
                const time = selectedDates.get(key) || '';

                $body.append(
                    '<tr data-date="' + key + '">' +
                    '<td class="pl-4">' + (index + 1) + '</td>' +
                    '<td><span class="d-block max-w-220px min-w-176px">' +
                    '<span class="d-block text-title">' + label + '</span>' +
                    '<span class="fs-12">' + time + '</span>' +
                    '</span></td>' +
                    '<td><div class="btn--container justify-content-center">' +
                    '<a href="javascript:" class="btn action-btn btn--primary btn-outline-primary btn-edit-schedule">' +
                    '<i class="tio-edit"></i></a>' +
                    '<a href="javascript:" class="btn action-btn btn--danger btn-outline-danger btn-remove-schedule">' +
                    '<i class="tio-delete-outlined"></i></a>' +
                    '</div></td>' +
                    '</tr>'
                );
            });
        }

        // Mirrors the in-memory selection onto the hidden inputs the form posts,
        // plus the summary line and the recap table.
        function commitCustomSelection() {
            const keys = Array.from(selectedDates.keys()).sort();
            const times = keys.map(function (key) { return selectedDates.get(key); });

            $('#custom_days').val(keys.join(','));
            $('#custom_times').val(times.join(','));

            $('#custom_schedule_input')
                .val(keys.length ? keys.length + ' ' + LANG.daysSelected : '')
                .removeClass('is-invalid');

            if (window.FormValidation) {
                FormValidation.clearError($('#custom_schedule_input')[0]);
            }

            if (keys.length) {
                $('#custom-date-min').text(moment(keys[0], VALUE_DATE_FORMAT).format('DD MMM YYYY'));
                $('#custom-date-max').text(moment(keys[keys.length - 1], VALUE_DATE_FORMAT).format('DD MMM YYYY'));
                $('#custom-date-range-text').removeClass('d-none');
                $('#custom-schedule-table').removeClass('d-none');
            } else {
                $('#custom-date-range-text').addClass('d-none');
                $('#custom-schedule-table').addClass('d-none');
            }

            renderCustomRecapTable();
        }

        $('#custom_schedule_save').on('click', function () {
            if (selectedDates.size === 0) {
                toastr.error(LANG.selectDateAndTime);
                return;
            }

            const missingTime = Array.from(selectedDates.values()).some(function (time) { return !time; });
            if (missingTime) {
                toastr.error(LANG.selectDateAndTime);
                return;
            }

            commitCustomSelection();
            customScheduleSaved = true;
            $('#customScheduleModal').modal('hide');
        });

        /* ── custom schedule: discard unsaved edits ───────────────────────── */

        // Everything the admin does inside the modal - picking a day, removing one, changing a
        // row's window - mutates selectedDates directly. Without a snapshot, dismissing with No
        // or the X would keep those edits, which is what the modal's own Save is for.
        let customScheduleSnapshot = null;
        let customScheduleSaved = false;

        $('#customScheduleModal').on('show.bs.modal', function () {
            customScheduleSnapshot = new Map(selectedDates);
            customScheduleSaved = false;
        });

        $('#customScheduleModal').on('hidden.bs.modal', function () {
            if (customScheduleSaved || customScheduleSnapshot === null) {
                return;
            }

            selectedDates.clear();
            customScheduleSnapshot.forEach(function (time, key) { selectedDates.set(key, time); });

            renderCalendar();
            rebuildSelectedList();
        });

        $(document).on('click', '.btn-remove-schedule', function () {
            const key = $(this).closest('tr').data('date');
            selectedDates.delete(key);
            $days.find('div[data-date="' + key + '"]').removeClass('active');
            rebuildSelectedList();
            commitCustomSelection();
        });

        let editingDateKey = null;

        $(document).on('click', '.btn-edit-schedule', function () {
            editingDateKey = $(this).closest('tr').data('date');

            const existing = selectedDates.get(editingDateKey) || defaultTimeRange();
            $('#edit-time-date-label').text(moment(editingDateKey, VALUE_DATE_FORMAT).format('ddd, MMM DD YYYY'));

            const $editInput = $('#edit-time-range-input');
            $editInput.val(existing);
            // Open the picker on the row's own window rather than on "now".
            syncPickerToValue($editInput);

            $('#editTimeRangeModal').modal('show');
        });

        $('#update-time-btn').on('click', function () {
            const value = $('#edit-time-range-input').val();

            if (!value) {
                toastr.error(LANG.selectTime);
                return;
            }

            if (editingDateKey !== null) {
                selectedDates.set(editingDateKey, value);
                rebuildSelectedList();
                commitCustomSelection();
            }

            $('#editTimeRangeModal').modal('hide');
        });

        // Seed from the hidden inputs so editing an existing custom rule opens
        // with its dates already picked.
        (function primeCustomSelection() {
            const days = ($('#custom_days').val() || '').split(',').filter(Boolean);
            const times = ($('#custom_times').val() || '').split(',');

            days.forEach(function (day, index) {
                const key = moment(day, [VALUE_DATE_FORMAT, 'DD MMM YYYY', moment.ISO_8601]).format(VALUE_DATE_FORMAT);
                selectedDates.set(key, (times[index] || '').trim() || defaultTimeRange());
            });

            if (selectedDates.size) {
                viewMonth = moment(Array.from(selectedDates.keys()).sort()[0], VALUE_DATE_FORMAT).startOf('month');
                rebuildSelectedList();
                commitCustomSelection();
            } else {
                // Nothing seeded — a fresh create never rebuilds the list, so the
                // empty state has to be raised explicitly.
                syncSelectedListEmptyState();
            }
        })();

        renderCalendar();

        /* ── character counters ───────────────────────────────────────────── */

        $('.surge-count-input').each(function () {
            const $input = $(this);
            const $counter = $input.siblings('.surge-char-count');
            const max = parseInt($input.attr('maxlength'), 10) || 100;

            function sync() {
                $counter.text($input.val().length + '/' + max);
            }

            $input.on('input', sync);
            sync();
        });

        /* ── price type cap ───────────────────────────────────────────────── */

        // Percent is capped at 100 server-side too; this only stops the admin
        // typing a value that would certainly be rejected.
        function syncPriceCap() {
            const $price = $('#price');

            if ($('#price_type').val() === 'percent') {
                $price.attr('max', 100);
                if (parseFloat($price.val()) > 100) {
                    $price.val(100);
                }
            } else {
                $price.removeAttr('max');
            }
        }

        $('#price_type').on('change', syncPriceCap);
        $('#price').on('input', function () {
            if ($('#price_type').val() === 'percent' && parseFloat($(this).val()) > 100) {
                $(this).val(100);
            }
        });
        syncPriceCap();

        /* ── reset ────────────────────────────────────────────────────────── */

        // type="reset" only restores the markup's own defaults. Everything the admin picked
        // through a modal lives in JS state and hidden inputs, so the schedule pane, the weekly
        // summary and the custom recap table have to be torn down by hand - otherwise Reset
        // clears the visible fields and leaves the schedule behind.
        $('#reset_btn').on('click', function () {
            // Runs after the browser has applied the native reset to the form controls.
            setTimeout(function () {
                selectedDates.clear();
                $('#custom_days').val('');
                $('#custom_times').val('');
                $('#weekly_days').val('');
                $('#is_permanent').val(0);
                $('#weekly_date_range').val('');

                $('#selected-weekdays-text').text('');
                $('.weekly-summary').addClass('d-none');

                rebuildSelectedList();
                renderCalendar();
                commitCustomSelection();
                syncPermanentState();
                syncSchedulePanes();
                syncPriceCap();
                // Reset restores the toggle to its markup state without firing change, so
                // the required flag would otherwise be left describing the old state.
                syncNoteRequired();

                $('.surge-count-input').trigger('input');
            }, 0);
        });

        /* ── customer note ────────────────────────────────────────────────── */

        // The note is the only explanation the customer gets for a higher delivery
        // charge, so a switched-on toggle with an empty box publishes nothing. The
        // required flag follows the toggle rather than being fixed in the markup, and
        // SurgePriceController applies the same rule - this side is only markup.
        const $noteToggle = $('#customer_note_status');
        const $noteInput = $('#customer_note_default');

        function noteIsMissing() {
            return $noteToggle.is(':checked') && !$noteInput.val().trim();
        }

        function syncNoteRequired() {
            const on = $noteToggle.is(':checked');

            $noteInput.prop('required', on);
            $('.note-required-star').toggleClass('d-none', !on);

            // Switching the toggle off answers the complaint, so the error should not be
            // left standing over a field that is optional again.
            if (!on && window.FormValidation) {
                FormValidation.clearError($noteInput[0]);
                $noteInput.removeClass('is-invalid');
            }
        }

        $noteToggle.on('change', syncNoteRequired);
        syncNoteRequired();

        /* ── submit ───────────────────────────────────────────────────────── */

        function sessionExpired() {
            toastr.error(LANG.sessionExpired);
            setTimeout(function () { window.location.href = LANG.loginUrl; }, 1500);
        }

        $('#surge_form').on('submit', function (e) {
            e.preventDefault();

            const $form = $(this);
            const $submit = $form.find('button[type="submit"]');

            // FormValidation binds its own submit handler on DOMContentLoaded, which is
            // after this one, so its preventDefault can never reach a submit that has
            // already got here. The same check is made explicitly instead - otherwise the
            // request goes out and the rule is left to the server alone.
            if (window.FormValidation && !FormValidation.validateForm(this)) {
                // The inline error the class writes is generic, so the one condition that
                // is not obvious from looking at the field is also said plainly.
                if (noteIsMissing()) {
                    toastr.error(LANG.noteRequired);
                }

                $form.find('.is-invalid').first().trigger('focus');

                return;
            }

            $submit.prop('disabled', true);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function (res) {
                    // An expired session redirects to the login page, which jQuery follows and
                    // hands back as a 200 full of HTML. Without this the save looks like it did
                    // nothing at all - the button simply re-enabled and no record was written.
                    if (typeof res !== 'object' || res === null) {
                        sessionExpired();
                        return;
                    }

                    if (res.status) {
                        toastr.success(res.message);
                        setTimeout(function () {
                            window.location.href = @json(route('admin.surge-price.list'));
                        }, 1200);
                    } else {
                        $submit.prop('disabled', false);
                    }
                },
                error: function (xhr) {
                    $submit.prop('disabled', false);

                    // 401 is an unauthenticated JSON request, 419 an expired CSRF token; both
                    // mean the admin has to log in again before anything can be saved.
                    if (xhr.status === 401 || xhr.status === 419) {
                        sessionExpired();
                    } else if (xhr.status === 422) {
                        const errors = xhr.responseJSON ? xhr.responseJSON.errors : {};
                        Object.values(errors || {}).forEach(function (list) {
                            list.forEach(function (message) { toastr.error(message); });
                        });
                    } else if (xhr.status === 409) {
                        $('#surge-overlap-modal').modal('show');
                    } else {
                        toastr.error((xhr.responseJSON && xhr.responseJSON.message) || LANG.unexpected);
                    }
                },
            });
        });
    })();
</script>
