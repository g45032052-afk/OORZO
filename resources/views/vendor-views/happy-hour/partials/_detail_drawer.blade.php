@php
    $status = $enrollment?->status;

    // One word for the enrolment, so the footer never has to recombine status and
    // requested_by. An admin assignment waits on the restaurant; a restaurant request waits
    // on the admin.
    $state = ! $enrollment
        ? 'not_joined'
        : ($status === 'pending'
            ? ($enrollment->requested_by === 'admin' ? 'admin_requested' : 'pending')
            : $status);

    // Admin-requested shows no badge in the design - the footer's Deny/Approve pair is the
    // signal. Only the states the restaurant owns get one.
    $badge = match ($state) {
        'pending'  => ['badge-soft-info', translate('messages.Pending')],
        'approved' => ['badge-soft-success', translate('messages.Approved')],
        'rejected' => ['badge-soft-danger', translate('messages.Rejected')],
        default    => null,
    };

    $rows = [translate('messages.Discount') => $happyHour->discount.'%'];

    $rows[translate('messages.Min_Order_Amount')] = $happyHour->min_order_amount
        ? \App\CentralLogics\Helpers::format_currency($happyHour->min_order_amount)
        : 'N/A';

    if ($happyHour->duration_type === 'custom') {
        $rows[translate('messages.Offer_Schedule')] = translate('messages.Custom');
        $rows[translate('messages.days_selected')] = count($happyHour->custom_days ?? []);
    } else {
        $rows[translate('messages.Offer_Active')] =
            ($happyHour->start_time ? \App\CentralLogics\Helpers::time_format($happyHour->start_time) : '')
            .' - '.($happyHour->end_time ? \App\CentralLogics\Helpers::time_format($happyHour->end_time) : '')
            .' ('.translate('messages.1_Hour').')';

        if ($happyHour->duration_type === 'weekly') {
            $rows[translate('messages.Weekly')] = implode(', ', $happyHour->weekly_days ?? []);
        }

        $rows[translate('messages.Schedule_Date')] = $happyHour->is_permanent
            ? translate('messages.Permanent')
            : ($happyHour->start_date ? $happyHour->start_date->format('d M Y') : '')
                .' - '.($happyHour->end_date ? $happyHour->end_date->format('d M Y') : '');
    }

    $description = $happyHour->short_description ?? '';
@endphp

<div class="d-flex flex-column" style="height:100%">
    <div class="custom-offcanvas-header d-flex justify-content-between align-items-center px-3 py-3">
        <div class="d-flex align-items-center gap-3">
            <h2 class="mb-0 fs-18 font-bold">{{ translate('messages.Happy_Hour') }} #{{ $happyHour->id }}</h2>
            @if($badge)
                <span class="badge {{ $badge[0] }} fs-13 fw-400 px-2 py-1">{{ $badge[1] }}</span>
            @endif
        </div>
        <button type="button"
                class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                aria-label="Close">&times;</button>
    </div>

    <div class="custom-offcanvas-body p-20 flex-grow-1">
        <img class="rounded-8 w-100 mb-3 onerror-image"
             data-onerror-image="{{ dynamicAsset('assets/admin/img/900x400/img1.png') }}"
             src="{{ $happyHour->cover_image_full_url }}" alt="{{ $happyHour->title }}">

        <h4 class="mb-2 font-bold">{{ $happyHour->title }}</h4>

        <p class="opacity-75 mb-3">
            @if(Str::length($description) > 160)
                <span class="description-short">{{ Str::limit($description, 160, '...') }}</span>
                <span class="description-full d-none">{{ $description }}</span>
                <a href="javascript:" class="toggle-description">{{ translate('messages.See_More') }}</a>
            @else
                {{ $description }}
            @endif
        </p>

        <div class="bg-light rounded-8 p-20">
            @foreach($rows as $label => $value)
                <div class="d-flex align-items-start {{ $loop->last ? '' : 'mb-2' }}">
                    <span class="opacity-75 flex-shrink-0" style="width:150px">{{ $label }}</span>
                    <span class="opacity-75 flex-shrink-0 px-2">:</span>
                    <strong>{{ $value }}</strong>
                </div>
            @endforeach
        </div>

        @if($state === 'rejected')
            {{-- Name who said no: a denial the restaurant issued itself should not read as
                 the admin's. --}}
            <div class="alert-soft-danger rounded-8 p-3 mt-20 fs-14">
                <strong class="d-block mb-1">
                    {{ $enrollment->rejected_by === 'restaurant'
                        ? translate('messages.You_declined_this_happy_hour')
                        : translate('messages.Denied_by_admin') }}
                </strong>
                @if($enrollment->rejection_reason)
                    {{ translate('messages.Rejection_Reason') }}: {{ $enrollment->rejection_reason }}
                @endif
            </div>
        @endif
    </div>

    <div class="offcanvas-footer bg-white p-3 d-flex align-items-center gap-3">
        @if($state === 'admin_requested')
            <button type="button" class="btn btn-soft-danger h--45px flex-fill form-alert"
                    data-id="drawer-hh-decline-{{ $happyHour->id }}"
                    data-message="{{ translate('messages.Want_to_decline_this_happy_hour') }}">
                {{ translate('messages.Deny') }}
            </button>
            <button type="button" class="btn btn--primary h--45px flex-fill form-alert"
                    data-id="drawer-hh-accept-{{ $happyHour->id }}"
                    data-message="{{ translate('messages.Want_to_join_this_happy_hour') }}">
                {{ translate('messages.Approve') }}
            </button>
        @else
            <button type="button" class="btn btn--reset h--45px flex-fill offcanvas-close">
                {{ translate('messages.Cancel') }}
            </button>

            @if($state === 'not_joined')
                <button type="button" class="btn btn--primary h--45px flex-fill form-alert"
                        data-id="drawer-hh-join-{{ $happyHour->id }}"
                        data-message="{{ translate('messages.Want_to_join_this_happy_hour') }}">
                    {{ translate('messages.Join') }}
                </button>
            @else
                {{-- The same delete either way; only the word changes. There is nothing to
                     rework in a happy hour, so a denied restaurant cancels here and joins
                     again from a clean row. --}}
                <button type="button" class="btn btn--danger h--45px flex-fill form-alert"
                        data-id="drawer-hh-leave-{{ $happyHour->id }}"
                        data-message="{{ $state === 'approved'
                            ? translate('messages.Want_to_leave_this_happy_hour')
                            : translate('messages.Want_to_cancel_this_request') }}">
                    {{ $state === 'approved'
                        ? translate('messages.Leave')
                        : translate('messages.Cancel_Request') }}
                </button>
            @endif
        @endif
    </div>

    {{-- Outside the footer so they never take a slot in the button flex row. --}}
    @if($state === 'admin_requested')
        <form action="{{ route('vendor.happy-hour.respond', [$happyHour->id, 'approved']) }}"
              method="post" id="drawer-hh-accept-{{ $happyHour->id }}">@csrf</form>
        <form action="{{ route('vendor.happy-hour.respond', [$happyHour->id, 'rejected']) }}"
              method="post" id="drawer-hh-decline-{{ $happyHour->id }}">@csrf</form>
    @elseif($state === 'not_joined')
        <form action="{{ route('vendor.happy-hour.join', $happyHour->id) }}"
              method="post" id="drawer-hh-join-{{ $happyHour->id }}">@csrf</form>
    @else
        <form action="{{ route('vendor.happy-hour.leave', $happyHour->id) }}"
              method="post" id="drawer-hh-leave-{{ $happyHour->id }}">@csrf @method('delete')</form>
    @endif
</div>
