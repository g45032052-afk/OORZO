<script>
    "use strict";

    // initialState is defined by the parent view (empty object on create).
    const state = {
        weeklyDays: (initialState.weekly_days || []).slice(),
        isPermanent: !!initialState.is_permanent,
        rangeStart: initialState.start_date || '',
        rangeEnd: initialState.end_date || '',
        customDays: (initialState.custom_days || []).slice(),
    };

    const WEEK_ORDER = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    const PICKER_FORMAT = 'DD MMM YYYY';

    // Nothing before today may be picked. Editing a run that already began keeps its own
    // start as the floor, matching what the server allows.
    const MIN_DATE = (function () {
        const today = moment().startOf('day');
        const stored = initialState.start_date ? moment(initialState.start_date, 'YYYY-MM-DD') : null;

        return stored && stored.isBefore(today) ? stored : today;
    })();

    function addOneHour(time) {
        if (!time) return '';
        const [h, m] = time.split(':').map(Number);
        return String((h + 1) % 24).padStart(2, '0') + ':' + String(m).padStart(2, '0');
    }

    function fmtDate(iso) {
        if (!iso) return '';
        return moment(iso, 'YYYY-MM-DD').format('ddd, MMM DD');
    }

    function fmtRange(start, end) {
        if (!start || !end) return '';
        return moment(start, 'YYYY-MM-DD').format(PICKER_FORMAT) + ' - ' +
               moment(end, 'YYYY-MM-DD').format(PICKER_FORMAT);
    }

    // Colon, not the dot the mockup uses: the start half of every range is a native time
    // input, which always renders hh:mm, and one separator per row has to win.
    function to12h(time) {
        if (!time) return '';
        const [h, m] = time.split(':').map(Number);
        const suffix = h >= 12 ? 'PM' : 'AM';
        const hour = h % 12 === 0 ? 12 : h % 12;
        return `${String(hour).padStart(2, '0')}:${String(m).padStart(2, '0')} ${suffix}`;
    }

    function timeRangeLabel(time) {
        return time ? `${to12h(time)} - ${to12h(addOneHour(time))}` : '';
    }

    /* ---------- schedule type ---------- */

    function applyType(type) {
        $('.daily-block').toggleClass('d-none', type !== 'daily');
        $('.weekly-block').toggleClass('d-none', type !== 'weekly');
        $('.custom-block').toggleClass('d-none', type !== 'custom');
        syncDateRange();
    }

    $('.duration-type').on('change', function () { applyType($(this).val()); });

    /* ---------- title and description counters ---------- */

    // The form renders data-counter attributes and the spans they point at, but the handler
    // that joins them lived only in the BOGO form scripts - so every counter on this page sat
    // at 0 however much was typed.
    $(document).on('input', '[data-counter]', function () {
        $('#' + $(this).data('counter')).text($(this).val().length);
    });

    /* ---------- min order amount toggle ---------- */

    $('#min_order_toggle').on('change', function () {
        const on = $(this).is(':checked');
        // required as well as enabled: without it the form submits with the toggle on and the
        // amount blank, and the happy hour is saved with no minimum it was meant to have.
        $('#min_order_amount').prop('disabled', !on).prop('required', on);
        $('#min_order_enabled').val(on ? 1 : 0);
        if (!on) $('#min_order_amount').val('');
    });

    /* ---------- daily / weekly time ---------- */

    // Empty shows the placeholder rather than the browser's bare "--:-- --".
    function paintTimeField($field) {
        const $input = $field.find('input[type="time"]');
        const value = $input.val();
        const end = addOneHour(value);

        $field.find('.hh-time__placeholder').toggleClass('d-none', !!value);
        $field.find('.hh-time__value').toggleClass('d-none', !value);
        $field.find('.derived-end').text(end ? to12h(end) : '');
    }

    // Clicking the field has to open the picker itself: the native indicator that would
    // normally do it is the one part of the control the design has no room for.
    function openTimePicker($field) {
        const input = $field.find('input[type="time"]')[0];
        if (!input) return;

        $field.find('.hh-time__placeholder').addClass('d-none');
        $field.find('.hh-time__value').removeClass('d-none');

        input.focus();
        if (typeof input.showPicker === 'function') {
            try { input.showPicker(); } catch (e) { /* not allowed without a user gesture */ }
        }
    }

    $(document).on('click', '.hh-time', function (e) {
        if ($(e.target).is('input')) return;
        openTimePicker($(this));
    });

    // input fires per segment, change only once the value is complete - both are wanted so
    // the derived end keeps up while typing.
    $(document).on('change input', '.start-time', function () {
        paintTimeField($(this).closest('.hh-time'));
        syncDateRange();
    });

    // Leaving an untouched field puts the placeholder back.
    $(document).on('blur', '.start-time', function () {
        paintTimeField($(this).closest('.hh-time'));
    });

    /* ---------- date range pickers ---------- */

    // autoUpdateInput off so the field stays empty until a range is actually applied.
    function initRangePicker($input, onApply) {
        $input.daterangepicker({
            autoUpdateInput: false,
            minDate: MIN_DATE.clone(),
            locale: {format: PICKER_FORMAT, cancelLabel: <?php echo json_encode(translate('messages.clear'), 15, 512) ?>},
            parentEl: $input.closest('.modal').length ? $input.closest('.modal-content') : 'body',
        });

        $input.on('apply.daterangepicker', function (ev, picker) {
            onApply(picker.startDate.format('YYYY-MM-DD'), picker.endDate.format('YYYY-MM-DD'));
        });

        $input.on('cancel.daterangepicker', function () { onApply('', ''); });
    }

    initRangePicker($('#daily_range_display'), function (start, end) {
        $('#daily_range_display').val(fmtRange(start, end)).data('start', start).data('end', end);
        syncDateRange();
    });

    // The whole field is clickable, not just the input.
    $('#daily_range_display').closest('.hh-field').on('click', function () {
        $('#daily_range_display').data('daterangepicker').show();
    });

    /* ---------- weekly modal ---------- */

    initRangePicker($('#modal_range_display'), function (start, end) {
        $('#modal_range_display').val(fmtRange(start, end)).data('start', start).data('end', end);
    });

    $('#modal_range_field').on('click', function () {
        if (!$(this).hasClass('is-disabled')) {
            $('#modal_range_display').data('daterangepicker').show();
        }
    });

    $('#open_weekly_modal').on('click', function () {
        $('.weekday-check').each(function () {
            $(this).prop('checked', state.weeklyDays.includes($(this).val()));
        });

        $('#modal_range_display')
            .val(fmtRange(state.rangeStart, state.rangeEnd))
            .data('start', state.rangeStart)
            .data('end', state.rangeEnd);

        $('#modal_is_permanent').prop('checked', state.isPermanent).trigger('change');
        $('#selectDaysModal').modal('show');
    });

    $('#modal_is_permanent').on('change', function () {
        $('#modal_range_field').toggleClass('is-disabled', $(this).is(':checked'));
    });

    $('#save_weekly_days').on('click', function () {
        const days = $('.weekday-check:checked').map(function () { return this.value; }).get();

        if (!days.length) {
            toastr.error('<?php echo e(translate('messages.Please_select_at_least_one_day')); ?>');
            return;
        }

        const permanent = $('#modal_is_permanent').is(':checked');
        const start = $('#modal_range_display').data('start') || '';
        const end = $('#modal_range_display').data('end') || '';

        // A permanent rule has no range at all, so it is the one case that may skip this.
        if (!permanent && (!start || !end)) {
            toastr.error('<?php echo e(translate('messages.Please_select_a_date_range')); ?>');
            return;
        }

        state.isPermanent = permanent;
        state.weeklyDays = WEEK_ORDER.filter(d => days.includes(d));
        state.rangeStart = permanent ? '' : start;
        state.rangeEnd = permanent ? '' : end;

        renderWeekly();
        $('#selectDaysModal').modal('hide');
    });

    function renderWeekly() {
        const names = state.weeklyDays;
        const summary = names.length > 1
            ? names.slice(0, -1).join(', ') + ' & ' + names[names.length - 1]
            : (names[0] || '');

        $('#weekly_summary').html(names.length
            ? `<?php echo e(translate('messages.Every_week')); ?> <strong>${summary}</strong>`
            : `<span class="opacity-75"><?php echo e(translate('messages.No_days_selected_yet')); ?></span>`);

        $('#weekly_range_display').val(
            state.isPermanent ? '<?php echo e(translate('messages.Permanent')); ?>' : fmtRange(state.rangeStart, state.rangeEnd)
        );

        $('#weekly_days_input').val(state.weeklyDays.join(','));
        $('#is_permanent_input').val(state.isPermanent ? 1 : 0);
        syncDateRange();
    }

    /* ---------- custom modal + calendar ---------- */

    let calCursor = moment().startOf('month');

    $('#open_custom_modal').on('click', function () {
        if (state.customDays.length) {
            calCursor = moment(state.customDays[0].date, 'YYYY-MM-DD').startOf('month');
        }
        renderCalendar();
        renderSelectedDays();
        $('#selectDaysTimeModal').modal('show');
    });

    $('#cal_prev').on('click', function () { calCursor.subtract(1, 'month'); renderCalendar(); });
    $('#cal_next').on('click', function () { calCursor.add(1, 'month'); renderCalendar(); });

    function renderCalendar() {
        $('#cal_label').text(calCursor.format('MMMM YYYY'));

        // Monday-first grid; leading and trailing cells show the neighbouring months greyed,
        // which is how the design fills the last row.
        const gridStart = calCursor.clone().startOf('month').startOf('isoWeek');
        const monthIndex = calCursor.month();

        let html = '';

        for (let week = 0; week < 6; week++) {
            const cells = [];

            for (let day = 0; day < 7; day++) {
                const date = gridStart.clone().add(week * 7 + day, 'days');
                const iso = date.format('YYYY-MM-DD');
                const outside = date.month() !== monthIndex;
                const picked = state.customDays.some(d => d.date === iso);
                // Already-stored dates stay selectable on edit even once they are behind us,
                // so an existing schedule can still be opened and adjusted.
                const past = date.isBefore(MIN_DATE, 'day') && !picked;

                cells.push(`<td>
                    <button type="button" class="cal-day${picked ? ' is-picked' : ''}${outside ? ' is-muted' : ''}${past ? ' is-past' : ''}"
                            data-date="${iso}"${past ? ' disabled' : ''}>${date.date()}</button>
                </td>`);
            }

            html += `<tr>${cells.join('')}</tr>`;

            // Stop once the month is behind us, so short months do not render a dead row.
            if (gridStart.clone().add(week * 7 + 6, 'days').isAfter(calCursor.clone().endOf('month'))) break;
        }

        $('#calendar_body').html(html);
    }

    $(document).on('click', '.cal-day', function () {
        if ($(this).is(':disabled')) return;

        const iso = $(this).data('date');
        const index = state.customDays.findIndex(d => d.date === iso);

        if (index >= 0) {
            state.customDays.splice(index, 1);
        } else {
            state.customDays.push({date: iso, time: '11:00'});
            state.customDays.sort((a, b) => a.date.localeCompare(b.date));
        }

        renderCalendar();
        renderSelectedDays();
    });

    // House empty state: centred illustration over a title and a line saying what to do
    // next, rather than a bare sentence hanging under the column headings.
    const EMPTY_STATE = `
        <div class="empty--data h-100 d-flex flex-column align-items-center justify-content-center py-4">
            <img src="<?php echo e(dynamicAsset('assets/admin/img/empty.png')); ?>" alt="<?php echo e(translate('messages.No_days_selected_yet')); ?>"
                 style="max-width:110px">
            <h6 class="mt-3 mb-1"><?php echo e(translate('messages.No_days_selected_yet')); ?></h6>
            <p class="opacity-75 fs-13 mb-0"><?php echo e(translate('messages.Pick_dates_from_the_calendar_to_add_them_here')); ?></p>
        </div>`;

    function renderSelectedDays() {
        $('#selected_days_head').toggleClass('d-none', !state.customDays.length);

        $('#selected_days_list').html(state.customDays.map((d, i) => `
            <div class="d-flex align-items-center gap-2 bg-light rounded-8 p-2 mb-2">
                <strong class="flex-grow-1 fs-14">${fmtDate(d.date)}</strong>
                <div class="hh-field hh-field--sm hh-time cursor-pointer">
                    <span class="hh-time__value">
                        <input type="time" max="22:59" class="custom-time" data-index="${i}" value="${d.time}">
                        <span>-</span>
                        <span class="hh-time__end">${to12h(addOneHour(d.time))}</span>
                    </span>
                    <i class="tio-time"></i>
                </div>
                <button type="button" class="hh-remove remove-custom-day" data-index="${i}"
                        title="<?php echo e(translate('messages.remove')); ?>">&times;</button>
            </div>`).join('') || EMPTY_STATE);
    }

    // Updated in place rather than re-rendering the list, which would drop focus midway
    // through typing a time.
    $(document).on('change input', '.custom-time', function () {
        const value = $(this).val();
        if (!value) return;

        state.customDays[$(this).data('index')].time = value;
        $(this).closest('.hh-time').find('.hh-time__end').text(to12h(addOneHour(value)));
    });

    $(document).on('click', '.remove-custom-day', function () {
        state.customDays.splice($(this).data('index'), 1);
        renderCalendar();
        renderSelectedDays();
    });

    $('#save_custom_days').on('click', function () {
        if (!state.customDays.length) {
            toastr.error('<?php echo e(translate('messages.Please_select_at_least_one_day')); ?>');
            return;
        }
        renderCustomSummary();
        $('#selectDaysTimeModal').modal('hide');
    });

    function renderCustomSummary() {
        $('#custom_summary_wrap').toggleClass('d-none', !state.customDays.length);
        $('#custom_count').text(state.customDays.length);

        // No dividers between rows and generous padding, per the design - the columns are
        // held apart by width, not by rules.
        $('#custom_summary_list').html(state.customDays.map((d, i) => `
            <div class="hh-day-row">
                <span class="hh-day-row__index">${i + 1}</span>
                <strong class="hh-day-row__date">${fmtDate(d.date)}</strong>
                <span class="hh-day-row__time">${timeRangeLabel(d.time)}</span>
                <button type="button" class="btn btn--primary btn-outline-primary hh-day-row__btn edit-custom-day">
                    <i class="tio-edit"></i>
                </button>
                <button type="button" class="btn btn--danger btn-outline-danger hh-day-row__btn drop-custom-day" data-index="${i}">
                    <i class="tio-delete-outlined"></i>
                </button>
            </div>`).join(''));

        // Both the dates and their start times travel as parallel comma lists.
        $('#custom_days_input').val(state.customDays.map(d => d.date).join(','));
        $('#custom_times_input').val(state.customDays.map(d => d.time).join(','));
        $('#custom_range_display').val(
            state.customDays.length
                ? fmtRange(state.customDays[0].date, state.customDays[state.customDays.length - 1].date)
                : ''
        );
    }

    $(document).on('click', '.edit-custom-day', function () { $('#open_custom_modal').trigger('click'); });

    $(document).on('click', '.drop-custom-day', function () {
        state.customDays.splice($(this).data('index'), 1);
        renderCustomSummary();
    });

    /* ---------- visible controls -> hidden inputs the controller reads ---------- */

    function syncDateRange() {
        const type = $('.duration-type:checked').val();

        if (type === 'daily') {
            const s = $('#daily_range_display').data('start') || '';
            const e = $('#daily_range_display').data('end') || '';
            $('#date_range_input').val(s && e ? `${s} - ${e}` : '');
            $('#start_time_input').val($('#daily_start_time').val());
        } else if (type === 'weekly') {
            $('#date_range_input').val(
                !state.isPermanent && state.rangeStart && state.rangeEnd
                    ? `${state.rangeStart} - ${state.rangeEnd}`
                    : ''
            );
            $('#start_time_input').val($('#weekly_start_time').val());
        } else {
            $('#date_range_input').val('');
            $('#start_time_input').val('');
        }
    }

    /* ---------- init ---------- */

    // Daily keeps its range and time on the page rather than behind a modal, so both have
    // to be seeded from the stored record when editing.
    if (initialState.duration_type === 'daily') {
        if (state.rangeStart && state.rangeEnd) {
            $('#daily_range_display')
                .val(fmtRange(state.rangeStart, state.rangeEnd))
                .data('start', state.rangeStart)
                .data('end', state.rangeEnd);
        }

        $('#daily_start_time').val(initialState.start_time || '');
    }

    $('.hh-time').each(function () { paintTimeField($(this)); });

    applyType($('.duration-type:checked').val());
    renderWeekly();
    if (state.customDays.length) renderCustomSummary();
    $('#min_order_toggle').trigger('change');
</script>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/happy-hour/partials/_schedule_scripts.blade.php ENDPATH**/ ?>