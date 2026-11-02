@forelse($offers as $key => $offer)
    @php
        // Eager loaded for this restaurant only, so first() is its own enrolment or null.
        $enrollment = $offer->enrollments->first();
        $eligible = $offer->is_eligible;

        // One word for the row, so the buttons below never have to recombine status and
        // requested_by and get the admin-requested case wrong.
        $state = ! $enrollment
            ? 'not_joined'
            : ($enrollment->status === 'pending'
                ? ($enrollment->requested_by === 'admin' ? 'admin_requested' : 'pending')
                : $enrollment->status);

        // An offer that has run its course stays on the list only while the restaurant is on
        // it, so the row says so rather than leaving a finished offer looking live.
        $ended = (bool) ($offer->has_ended ?? false);

        // Why the menu is inert when it is. Resolved in the controller from the same rule the
        // endpoints refuse with, so a button can never offer what the request behind it would
        // reject.
        $lockedReason = $offer->locked_reason ?? null;

        // Who said no. The admin's refusal is a record the restaurant may not erase - it
        // reworks the selection and asks again - while a restaurant that declined an
        // invitation may also drop the row and be rid of it.
        $deniedByAdmin = $state === 'rejected' && $enrollment->rejected_by === 'admin';

        // Everything the row still allows, as words. The view button stays out on its own -
        // reading a row is always allowed - and the rest moves behind the menu, where a
        // label says what an icon used to have to imply.
        //
        // An expired offer keeps its menu button, greyed: the row is a finished record and
        // the button says so by being there and doing nothing. Anything else unavailable is
        // simply not listed, and a menu with nothing in it is not drawn at all.
        $menu = [];

        if (! $ended) {
            if ($state === 'admin_requested') {
                $menu[] = ['label' => translate('messages.Approve'), 'class' => 'form-alert', 'data' => [
                    'id' => 'bogo-accept-'.$offer->id,
                    'message' => translate('messages.Want_to_join_this_BOGO_offer'),
                ]];
                $menu[] = ['label' => translate('messages.Deny'), 'class' => 'form-alert text-danger', 'data' => [
                    'id' => 'bogo-decline-'.$offer->id,
                    'message' => translate('messages.Want_to_decline_this_BOGO_offer'),
                ]];
            } elseif ($state === 'not_joined') {
                if ($eligible) {
                    $menu[] = ['label' => translate('messages.Join'), 'class' => 'join-offer', 'data' => ['id' => $offer->id]];
                }
            } else {
                // Reworking the selection: the answer to a denial, and the way a running
                // offer is changed. Gated on the same eligibility rule as joining.
                if (in_array($state, ['approved', 'rejected'], true) && $eligible) {
                    $menu[] = ['label' => translate('messages.Edit_&_Resubmit'), 'class' => 'edit-and-resubmit', 'data' => ['id' => $offer->id]];
                }

                // The same delete either way; only the word changes. Withdrawing a request
                // that never went live is a cancel, walking away from a running offer is a
                // leave. Left off an admin's denial, which is not the restaurant's to erase -
                // reworking it above is the whole of what it may do.
                if (! $deniedByAdmin) {
                    $menu[] = [
                        'label' => $state === 'approved'
                            ? translate('messages.Leave')
                            : translate('messages.Cancel_Request'),
                        'class' => 'form-alert text-danger',
                        'data' => [
                            'id' => 'leave-offer-'.$offer->id,
                            'message' => $state === 'approved'
                                ? translate('messages.Want_to_leave_this_BOGO_offer')
                                : translate('messages.Want_to_cancel_this_request'),
                        ],
                    ];
                }
            }
        }

        // Both composed in the controller, from the helpers the restaurant app reads too, so
        // one enrolment is never worded two ways.
        $rejectionNote = $offer->rejection_note ?? null;
        $stateLabel = $offer->state_label ?? null;
    @endphp

    <tr>
        <td>{{ $key + $offers->firstItem() }}</td>
        <td>
            {{-- Truncated by the cell rather than by Str::limit: the column keeps whatever
                 width it has, the full title is on the link's own tooltip, and the warning
                 beside it cannot be pushed out of the row by a long name. --}}
            <div class="promo-name">
                <a href="javascript:" class="text-body offer-detail promo-name__text"
                   data-url="{{ route('vendor.bogo-offer.detail', $offer->id) }}"
                   title="{{ $offer->title }}">
                    {{ $offer->title }}
                </a>

                {{-- Approved and still not on the customer's screen. Nothing is drawn while
                     the offer is running normally. --}}
                @include('partials.promotion._visibility_warning', [
                    'reasons' => $offer->hidden_reasons ?? [],
                    'class' => 'promo-name-warning',
                ])
            </div>
        </td>
        <td>
            <img class="rounded onerror-image" style="width:90px;height:36px;object-fit:cover"
                 data-onerror-image="{{ dynamicAsset('assets/admin/img/900x400/img1.png') }}"
                 src="{{ $offer->image_full_url }}" alt="{{ $offer->title }}">
        </td>
        <td>
            <span class="d-block">
                {{ translate('messages.Start_Date') }} &nbsp;:&nbsp;
                {{ $offer->start_date ? $offer->start_date->format('d M Y, h:i a') : 'N/A' }}
            </span>
            <span class="d-block">
                {{ translate('messages.End_Date') }} &nbsp;&nbsp;&nbsp;:&nbsp;
                {{ $offer->end_date ? $offer->end_date->format('d M Y, h:i a') : 'N/A' }}
            </span>
        </td>
        <td>
            {{-- An expired offer says only that. What the enrolment had got to - approved,
                 waiting, denied - is a live negotiation, and reading one beside a finished
                 offer had the vendor chasing an offer that was over. It comes straight back
                 the moment an admin extends the end date, since this is read off the offer
                 rather than recorded anywhere. --}}
            @if($ended)
                <span class="badge badge-soft-danger">{{ $stateLabel }}</span>
            {{-- No enrolment row means the restaurant has simply not joined; the design
                 shows that as plain text rather than a badge. --}}
            @elseif(in_array($state, ['not_joined', 'admin_requested'], true))
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
                   data-url="{{ route('vendor.bogo-offer.detail', $offer->id) }}"
                   title="{{ translate('messages.view') }}">
                    <i class="tio-visible"></i>
                </a>

                @if($ended)
                    {{-- The row keeps its menu button so the action column does not change
                         shape on the one row that has finished; it opens nothing, because
                         there is nothing left to do to an expired offer. --}}
                    <button type="button" class="btn bg-transparent border promo-action-icon" disabled
                            title="{{ $lockedReason }}">
                        <i class="tio-more-vertical"></i>
                    </button>
                @elseif($menu)
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

            {{-- Outside .promo-actions so they never take a slot in the button flex row. A
                 row with no menu has nothing to submit one. --}}
            @if(! $menu)
            @elseif($state === 'admin_requested')
                <form action="{{ route('vendor.bogo-offer.respond', [$offer->id, 'approved']) }}"
                      method="post" id="bogo-accept-{{ $offer->id }}">@csrf</form>
                <form action="{{ route('vendor.bogo-offer.respond', [$offer->id, 'rejected']) }}"
                      method="post" id="bogo-decline-{{ $offer->id }}">@csrf</form>
            @elseif($state !== 'not_joined')
                <form action="{{ route('vendor.bogo-offer.leave', $offer->id) }}" method="post"
                      id="leave-offer-{{ $offer->id }}">
                    @csrf @method('delete')
                </form>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center py-5">{{ translate('no_data_found') }}</td>
    </tr>
@endforelse
