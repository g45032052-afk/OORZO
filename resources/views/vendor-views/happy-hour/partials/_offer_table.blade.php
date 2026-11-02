@forelse($happyHours as $key => $happyHour)
    @php
        // Eager loaded for this restaurant only, so first() is its own enrolment or null.
        $enrollment = $happyHour->enrollments->first();

        // One word for the row, so the buttons below never have to recombine status and
        // requested_by. An admin assignment waits on the restaurant; a restaurant request
        // waits on the admin.
        $state = ! $enrollment
            ? 'not_joined'
            : ($enrollment->status === 'pending'
                ? ($enrollment->requested_by === 'admin' ? 'admin_requested' : 'pending')
                : $enrollment->status);

        // Both composed in the controller, from the helpers the restaurant app reads too, so
        // one enrolment is never worded two ways.
        $rejectionNote = $happyHour->rejection_note ?? null;
        $stateLabel = $happyHour->state_label ?? null;

        $weekdays = $happyHour->duration_type === 'weekly'
            ? implode(', ', $happyHour->weekly_days ?? [])
            : null;

        // Everything the row still allows, as words. The view button stays out on its own -
        // reading a row is always allowed - and the rest moves behind the menu, where a label
        // says what an icon used to have to imply. An empty menu is not drawn.
        //
        // Which options appear is unchanged. Note there is no closed-record rule here as
        // there is on BOGO: a happy hour has no selection to rework, so a denied restaurant
        // cancels and joins again, and taking the cancel away would strand it.
        $menu = [];

        if ($state === 'admin_requested') {
            $menu[] = ['label' => translate('messages.Approve'), 'class' => 'form-alert', 'data' => [
                'id' => 'hh-accept-'.$happyHour->id,
                'message' => translate('messages.Want_to_join_this_happy_hour'),
            ]];
            $menu[] = ['label' => translate('messages.Deny'), 'class' => 'form-alert text-danger', 'data' => [
                'id' => 'hh-decline-'.$happyHour->id,
                'message' => translate('messages.Want_to_decline_this_happy_hour'),
            ]];
        } elseif ($state === 'not_joined') {
            $menu[] = ['label' => translate('messages.Join'), 'class' => 'form-alert', 'data' => [
                'id' => 'hh-join-'.$happyHour->id,
                'message' => translate('messages.Want_to_join_this_happy_hour'),
            ]];
        } else {
            // The same delete either way; only the word changes.
            $menu[] = [
                'label' => $state === 'approved'
                    ? translate('messages.Leave')
                    : translate('messages.Cancel_Request'),
                'class' => 'form-alert text-danger',
                'data' => [
                    'id' => 'hh-leave-'.$happyHour->id,
                    'message' => $state === 'approved'
                        ? translate('messages.Want_to_leave_this_happy_hour')
                        : translate('messages.Want_to_cancel_this_request'),
                ],
            ];
        }
    @endphp

    <tr>
        <td>{{ $key + $happyHours->firstItem() }}</td>
        <td>
            {{-- Truncated by the cell rather than by Str::limit: the column keeps whatever
                 width it has, the full title is on the link's own tooltip, and the warning
                 beside it cannot be pushed out of the row by a long name. --}}
            <div class="promo-name">
                <a href="javascript:" class="text-body offer-detail promo-name__text"
                   data-url="{{ route('vendor.happy-hour.detail', $happyHour->id) }}"
                   title="{{ $happyHour->title }}">
                    {{ $happyHour->title }}
                </a>

                {{-- Approved and still discounting nothing for customers. Nothing is drawn
                     while the happy hour is running or simply waiting for its next window. --}}
                @include('partials.promotion._visibility_warning', [
                    'reasons' => $happyHour->hidden_reasons ?? [],
                    'class' => 'promo-name-warning',
                ])
            </div>
        </td>
        <td>
            <img class="rounded onerror-image" style="width:90px;height:36px;object-fit:cover"
                 data-onerror-image="{{ dynamicAsset('assets/admin/img/900x400/img1.png') }}"
                 src="{{ $happyHour->cover_image_full_url }}" alt="{{ $happyHour->title }}">
        </td>
        <td>{{ $happyHour->discount }}%</td>
        <td>
            @if($happyHour->duration_type === 'custom')
                <span class="d-block">{{ count($happyHour->custom_days ?? []) }} {{ translate('messages.days_selected') }}</span>
            @else
                <span class="d-block">
                    {{ $happyHour->start_time ? \App\CentralLogics\Helpers::time_format($happyHour->start_time) : '' }}
                    - {{ $happyHour->end_time ? \App\CentralLogics\Helpers::time_format($happyHour->end_time) : '' }}
                </span>
            @endif

            <span class="d-block opacity-75">
                @if($happyHour->is_permanent)
                    {{ translate('messages.Permanent') }}
                @else
                    {{ $happyHour->start_date ? $happyHour->start_date->format('d M Y') : '' }}
                    - {{ $happyHour->end_date ? $happyHour->end_date->format('d M Y') : '' }}
                @endif
            </span>

            @if($weekdays)
                <span class="d-block opacity-75">{{ $weekdays }}</span>
            @endif
        </td>
        <td>
            {{ $happyHour->min_order_amount
                ? \App\CentralLogics\Helpers::format_currency($happyHour->min_order_amount)
                : 'N/A' }}
        </td>
        <td class="text-capitalize">{{ translate('messages.'.$happyHour->duration_type) }}</td>
        <td>
            {{-- One phrase, decided in the controller. Not joined and admin-requested read
                 as plain text in the design; only the states the restaurant itself owns get a
                 coloured badge. --}}
            @if(in_array($state, ['not_joined', 'admin_requested'], true))
                <span class="opacity-75">{{ $stateLabel }}</span>
            @elseif($state === 'pending')
                <span class="badge badge-soft-info">{{ $stateLabel }}</span>
            @elseif($state === 'approved')
                <span class="badge badge-soft-success">{{ $stateLabel }}</span>
            @else
                <span class="badge badge-soft-danger" data-toggle="tooltip" title="{{ $rejectionNote }}">
                    {{ $stateLabel }}
                </span>
            @endif
        </td>
        <td>
            <div class="promo-actions">
                <a class="btn btn--primary btn-outline-primary promo-action-icon offer-detail" href="javascript:"
                   data-url="{{ route('vendor.happy-hour.detail', $happyHour->id) }}"
                   title="{{ translate('messages.view') }}">
                    <i class="tio-visible"></i>
                </a>

                @if($menu)
                    <div class="dropdown dropdown-2">
                        <button type="button" class="btn btn--primary btn-outline-primary promo-action-icon"
                                data-toggle="dropdown" aria-expanded="false"
                                title="{{ translate('messages.action') }}">
                            <i class="tio-more-vertical"></i>
                        </button>
                        {{-- Right aligned: the action column is the last one, so a menu
                             hanging to the left of the button stays on the screen. --}}
                        <ul class="dropdown-menu dropdown-menu-right" dir="ltr">
                            @foreach($menu as $item)
                                <li>
                                    <a class="dropdown-item {{ $item['class'] }}" href="javascript:"
                                       @foreach($item['data'] as $key => $value)
                                           data-{{ $key }}="{{ $value }}"
                                       @endforeach>
                                        {{ $item['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Kept outside .promo-actions so they never take a slot in the button flex row. --}}
            @if($state === 'admin_requested')
                <form action="{{ route('vendor.happy-hour.respond', [$happyHour->id, 'approved']) }}"
                      method="post" id="hh-accept-{{ $happyHour->id }}">@csrf</form>
                <form action="{{ route('vendor.happy-hour.respond', [$happyHour->id, 'rejected']) }}"
                      method="post" id="hh-decline-{{ $happyHour->id }}">@csrf</form>
            @elseif($state === 'not_joined')
                <form action="{{ route('vendor.happy-hour.join', $happyHour->id) }}"
                      method="post" id="hh-join-{{ $happyHour->id }}">@csrf</form>
            @else
                <form action="{{ route('vendor.happy-hour.leave', $happyHour->id) }}"
                      method="post" id="hh-leave-{{ $happyHour->id }}">@csrf @method('delete')</form>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center py-5">{{ translate('no_data_found') }}</td>
    </tr>
@endforelse
