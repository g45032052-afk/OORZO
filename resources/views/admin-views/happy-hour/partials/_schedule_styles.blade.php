{{-- Shared by the create and edit pages. The schedule field and calendar have no house
     equivalent, so they are defined here rather than bent out of existing utilities.
     moment and daterangepicker both ship inside vendor.min.js/.css already, so neither
     needs loading here. --}}
<style>
    /* One control shape for every schedule field. The design puts the icon inside the
       field, which an input-group-append cannot do - it renders its own bordered box, so
       the date fields ended up a different shape from the time fields. */
    .hh-field {
        display: flex;
        align-items: center;
        gap: 8px;
        height: 45px;
        padding: 0 12px;
        background: #fff;
        border: 1px solid var(--border-clr);
        border-radius: 0.3125rem;
    }
    /* Fixed so the list rows line up under the DATE / TIME headers. Wide enough for the
       longest rendering, "02:00 AM - 03:00 AM" plus the icon. */
    .hh-field--sm {
        height: 38px;
        width: 200px;
        padding: 0 10px;
        gap: 6px;
        font-size: 13px;
        flex: 0 0 auto;
        white-space: nowrap;
    }
    .hh-field > input {
        flex: 1 1 auto;
        min-width: 0;
        padding: 0;
        border: 0;
        outline: none;
        background: transparent;
        color: var(--title-clr);
        font: inherit;
    }
    .hh-field > input::placeholder {
        color: var(--title-clr);
        opacity: .5;
    }
    .hh-field > i {
        flex: 0 0 auto;
        opacity: .5;
    }
    .hh-field.is-disabled {
        background: var(--section-bg2);
        opacity: .65;
        pointer-events: none;
    }

    /* Time: only the start is editable, the end is derived text. Until a time is picked
       the native --:-- is kept out of sight behind the placeholder. */
    .hh-time__value {
        display: flex;
        align-items: center;
        gap: 6px;
        flex: 1 1 auto;
        min-width: 0;
    }
    .hh-time__value input[type="time"] {
        flex: 0 0 auto;
        width: auto;
        padding: 0;
        border: 0;
        outline: none;
        background: transparent;
        color: var(--title-clr);
        font: inherit;
    }
    /* The design puts a single clock at the far end of the field, so the browser's own
       indicator is hidden - clicking the field opens the picker instead (see openTimePicker). */
    .hh-time__value input[type="time"]::-webkit-calendar-picker-indicator {
        display: none;
    }
    .hh-time__end {
        white-space: nowrap;
        opacity: .75;
    }
    .hh-time__placeholder {
        flex: 1 1 auto;
        color: var(--title-clr);
        opacity: .5;
    }

    /* Calendar: plain numbers, the picked ones filled with the theme colour. */
    .hh-calendar th {
        font-weight: 400;
        padding: 4px;
    }
    .hh-calendar td {
        padding: 3px;
    }
    .hh-calendar .cal-day {
        width: 36px;
        height: 36px;
        padding: 0;
        border: 0;
        border-radius: 6px;
        background: transparent;
        color: var(--title-clr);
    }
    .hh-calendar .cal-day:hover {
        background: var(--section-bg2);
    }
    .hh-calendar .cal-day.is-picked {
        background: var(--primary-clr);
        color: #fff;
    }
    .hh-calendar .cal-day.is-muted {
        opacity: .35;
    }
    .hh-calendar .cal-day.is-past {
        opacity: .3;
        cursor: not-allowed;
        text-decoration: line-through;
    }
    .hh-calendar .cal-day.is-past:hover {
        background: transparent;
    }

    .hh-cal-nav {
        border: 0;
        background: transparent;
        color: var(--title-clr);
        line-height: 1;
        padding: 4px 8px;
    }

    /* Selected-day summary rows. Column offsets follow the design: the time column sits
       at roughly 44% of the panel, which is what the fixed date width buys. */
    .hh-day-row {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 18px 24px;
    }
    .hh-day-row__index {
        width: 32px;
        flex-shrink: 0;
        opacity: .6;
    }
    .hh-day-row__date {
        width: 36%;
        flex-shrink: 0;
    }
    .hh-day-row__time {
        flex-grow: 1;
    }
    .hh-day-row__btn {
        width: 40px;
        height: 40px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 8px;
    }

    /* Round outlined cross on each selected-day row. */
    .hh-remove {
        width: 24px;
        height: 24px;
        flex-shrink: 0;
        padding: 0;
        line-height: 1;
        border: 1px solid var(--danger-clr);
        border-radius: 50%;
        background: transparent;
        color: var(--danger-clr);
    }
</style>
