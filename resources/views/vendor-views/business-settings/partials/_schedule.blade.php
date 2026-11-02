@php
    // Group schedules by day
    $schedulesByDay = $restaurant->schedules
        ->groupBy('day')
        ->map(fn ($items) =>
            $items->map(fn ($s) => [
                'id' => $s->id,
                'start_time' => $s->opening_time,
                'end_time' => $s->closing_time,
            ])->toArray()
        )
        ->toArray();

    $deleteRoute = Auth::guard('admin')->check()
        ? 'admin.restaurant.remove-schedule'
        : 'vendor.business-settings.remove-schedule';

    $days = [
        1 => 'monday',
        2 => 'tuesday',
        3 => 'wednesday',
        4 => 'Thursday',
        5 => 'friday',
        6 => 'saturday',
        0 => 'sunday',
    ];
@endphp

@foreach ($days as $dayId => $dayKey)
    <div class="schedule-item p-3 border-bottom" data-day="{{ $dayId }}">
        <span class="btn">{{ translate("messages.$dayKey") }}</span>

        <div class="schedult-date-content border-0 p-0">
            @forelse ($schedulesByDay[$dayId] ?? [] as $schedule)
                <span class="d-inline-flex align-items-center position-relative my-2">
                    <span class="start--time">
                        <span class="info text-nowrap">
                            {{ date(config('timeformat'), strtotime($schedule['start_time'])) }}
                            -
                            {{ date(config('timeformat'), strtotime($schedule['end_time'])) }}
                        </span>
                    </span>

                    <span
                        class="dismiss--date dismiss--date-absolute delete-schedule"
                        data-url="{{ route($deleteRoute, [
                            'restaurant_schedule' => $schedule['id'],
                            'id' => $restaurant->id
                        ]) }}"
                    >
                        <i class="tio-clear-circle-outlined"></i>
                    </span>
                </span>
            @empty
                <span class="btn btn-sm font-semibold text-danger bg-danger m-1" style="--bs-bg-opacity:.1;">
                    {{ translate('messages.Offday') }}
                </span>
            @endforelse

            <span
                class="btn add--primary mt-0 ml-2 offcanvas-trigger"
                style="border-radius:5px!important;"
                data-toggle="offcanvas"
                data-target="#offcanvasAddSchedule"
                data-dayid="{{ $dayId }}"
                data-day="{{ translate("messages.$dayKey") }}"
            >
                <i class="tio-add"></i>
            </span>
        </div>
    </div>
@endforeach
