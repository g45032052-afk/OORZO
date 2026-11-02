@php
    $status = $enrollment?->status;
    $eligible = $offer->is_eligible ?? true;

    // One word for the enrolment, so the footer never has to recombine status and
    // requested_by. An admin assignment waits on the restaurant; a restaurant request waits
    // on the admin.
    $state = ! $enrollment
        ? 'not_joined'
        : ($status === 'pending'
            ? ($enrollment->requested_by === 'admin' ? 'admin_requested' : 'pending')
            : $status);

    // Admin-requested shows no badge: the Deny/Approve footer is the signal. Only the
    // states the restaurant itself owns get one.
    $badge = match ($state) {
        'pending'  => ['badge-soft-info', translate('messages.Pending')],
        'approved' => ['badge-soft-success', translate('messages.Approved')],
        'rejected' => ['badge-soft-danger', translate('messages.Rejected')],
        default    => null,
    };

    // Approved is the admin's answer, not the customer's view: an approved bundle can be
    // waiting for its start date, past its end date, or hidden today because one of its foods
    // is switched off or sold out. Said plainly here so the vendor does not have to open the
    // app to find out which.
    $live = $state !== 'approved' ? null : (
        (! $offer->status || ($offer->has_ended ?? false))
            ? ['badge-soft-danger', translate('messages.Ended'), null]
            : (! ($offer->has_started ?? true)
                ? ['badge-soft-info', translate('messages.Scheduled'),
                   ($offer->start_date ? $offer->start_date->format('d M, Y h:i A') : null)]
                : (! empty($hiddenReasons)
                    ? ['badge-soft-warning', translate('messages.Not_visible_to_customers'),
                       implode(' | ', $hiddenReasons)]
                    : ['badge-soft-success', translate('messages.Running_now'),
                       translate('messages.Customers_can_order_this_bundle_right_now')]))
    );

    // Denied by the admin, or an offer that has finished running: the drawer stays open for
    // reading and for the one thing an admin's denial is answered with - reworking the
    // selection - but the delete loses its place. Cancelling the admin's refusal would throw
    // it away along with its reason; leaving an offer that has already ended undoes nothing.
    //
    // A denial the restaurant issued itself is not this. It is refusing something it was
    // offered, and it may drop the row rather than carry an invitation it does not want, so
    // Cancel Request stays - the same rule the list row reads.
    $lockedReason = $offer->locked_reason ?? null;
    $locked = (bool) $lockedReason;

    // The join drawer reads buy_qty/get_qty off this element, so it never has to guess the
    // offer's requirements from the table row.
    $meta = ['buy_qty' => $offer->buy_qty, 'get_qty' => $offer->get_qty];

    // Frozen items mapped back into the drawer's working shape for "Edit & Resubmit".
    $resubmitPayload = $enrollment
        ? $enrollment->items->map(fn ($i) => [
            'side' => $i->type,
            'food_id' => $i->food_id,
            'quantity' => $i->quantity,
            'selected_variations' => $i->variations ?? [],
            'add_on_ids' => $i->add_on_ids ?? [],
            'add_on_qtys' => $i->add_on_qtys ?? [],
        ])->values()
        : collect();

    $description = $offer->description ?? '';
@endphp

<div class="d-flex flex-column" style="height:100%">
    <div class="custom-offcanvas-header d-flex justify-content-between align-items-center px-3 py-3">
        <div class="d-flex align-items-center gap-3">
            <h2 class="mb-0 fs-18 font-bold">{{ translate('messages.BOGO_Offer') }} #{{ $offer->id }}</h2>
            @if($badge)
                <span class="badge {{ $badge[0] }} fs-13 fw-400 px-2 py-1">{{ $badge[1] }}</span>
            @endif
            @if($live)
                <span class="badge {{ $live[0] }} fs-13 fw-400 px-2 py-1"
                      @if($live[2]) data-toggle="tooltip" title="{{ $live[2] }}" @endif>
                    {{ $live[1] }}
                </span>
            @endif
        </div>
        <button type="button"
                class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                aria-label="Close">&times;</button>
    </div>

    <div class="custom-offcanvas-body p-20 flex-grow-1">
        {{-- The join drawer reads both the offer's requirements and the current selection
             from here, so it never has to be handed a payload by whatever opened it. --}}
        <span id="offer_requirements" data-meta="{{ json_encode($meta) }}"
              data-items="{{ json_encode($resubmitPayload) }}"></span>

        <img class="rounded-8 w-100 mb-3 onerror-image"
             data-onerror-image="{{ dynamicAsset('assets/admin/img/900x400/img1.png') }}"
             src="{{ $offer->image_full_url }}" alt="{{ $offer->title }}">

        <h4 class="mb-2 font-bold">{{ $offer->title }}</h4>

        <p class="opacity-75 mb-3">
            @if(Str::length($description) > 160)
                <span class="description-short">{{ Str::limit($description, 160, '...') }}</span>
                <span class="description-full d-none">{{ $description }}</span>
                <a href="javascript:" class="toggle-description">{{ translate('messages.See_More') }}</a>
            @else
                {{ $description }}
            @endif
        </p>

        @php
            $rows = [
                translate('messages.Offer_Created') => $offer->created_at->format('d M, Y h:i A'),
                translate('messages.Validity') =>
                    ($offer->start_date ? $offer->start_date->format('d M, Y h:i A') : 'N/A')
                    .'  -  '.($offer->end_date ? $offer->end_date->format('d M, Y h:i A') : 'N/A'),
                translate('messages.Usage_Limit') => $offer->usage_limit_per_customer
                    ? translate('messages.Per_person').' '.$offer->usage_limit_per_customer.' '.translate('messages.Order')
                    : 'N/A',
                translate('messages.Usage_Limit_Total') => $offer->usage_limit_total
                    ? $offer->usage_limit_total.' '.translate('messages.Order')
                    : 'N/A',
            ];
        @endphp

        <div class="bg-light rounded-8 p-20 mb-20">
            @foreach($rows as $label => $value)
                <div class="d-flex align-items-start {{ $loop->last ? '' : 'mb-2' }}">
                    <span class="opacity-75 flex-shrink-0" style="width:130px">{{ $label }}</span>
                    <span class="opacity-75 flex-shrink-0 px-2">:</span>
                    <strong>{{ $value }}</strong>
                </div>
            @endforeach
        </div>

        {{-- A tooltip on the badge was the only place the reasons appeared, so a vendor asking
             "it is approved, why can nobody order it?" had to know to hover. They are spelled
             out here instead, one line per reason. --}}
        @if($state === 'approved' && ! empty($hiddenReasons) && ($offer->has_started ?? true) && ! ($offer->has_ended ?? false) && $offer->status)
            <div class="promo-reasons mb-20">
                <span class="promo-reasons__label">{{ translate('messages.Why_customers_can_not_see_this_offer_right_now', ['promotion' => translate('messages.BOGO_offer')]) }}</span>
                <ul class="promo-reasons__list">
                    @foreach($hiddenReasons as $reason)
                        <li>{{ $reason }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($state === 'rejected')
            {{-- Name who said no: a denial the restaurant issued itself should not read as
                 the admin's. --}}
            <div class="alert-soft-danger rounded-8 p-3 mb-20 fs-14">
                <strong class="d-block mb-1">
                    {{ $enrollment->rejected_by === 'restaurant'
                        ? translate('messages.You_declined_this_offer')
                        : translate('messages.Denied_by_admin') }}
                </strong>
                @if($enrollment->rejection_reason)
                    {{-- A reason can run to the 1000 characters the form allows, and one unbroken
                         string of that length - a pasted link, say - pushed the drawer sideways
                         when it was printed inline. A read-only field wraps it and scrolls on its
                         own instead of dragging the panel with it. --}}
                    <label class="input-label fs-12 mb-1">{{ translate('messages.Rejection_Reason') }}</label>
                    <textarea class="form-control bogo-rejection-reason" rows="3" disabled>{{ $enrollment->rejection_reason }}</textarea>
                @endif
            </div>
        @endif

        {{-- Only an enrolled restaurant has a selection to show. --}}
        @if($enrollment)
            <div class="bg-light rounded-8 p-20">
                @include('partials.bogo._enrollment_items', [
                    'enrollment' => $enrollment,
                    'addOnNames' => $addOnNames,
                    'addOnPrices' => $addOnPrices ?? collect(),
                    'surface' => 'bg-light',
                ])
            </div>
        @endif
    </div>

    <div class="offcanvas-footer bg-white p-3 d-flex align-items-center gap-3">
        @if($state === 'admin_requested' && ! $locked)
            {{-- The admin picked these foods: take them as-is or decline. There is no edit
                 here - a restaurant that wants different foods declines first, and reworks
                 the selection as its own request afterwards. --}}
            <button type="button" class="btn btn-soft-danger h--45px flex-fill form-alert"
                    data-id="drawer-bogo-decline-{{ $offer->id }}"
                    data-message="{{ translate('messages.Want_to_decline_this_BOGO_offer') }}">
                {{ translate('messages.Deny') }}
            </button>
            <button type="button" class="btn btn--primary h--45px flex-fill form-alert"
                    data-id="drawer-bogo-accept-{{ $offer->id }}"
                    data-message="{{ translate('messages.Want_to_join_this_BOGO_offer') }}">
                {{ translate('messages.Approve') }}
            </button>
        @elseif($state === 'admin_requested')
            {{-- The invitation outlived the offer: shown so the vendor can see what it was,
                 disabled because there is nothing left to accept. --}}
            <button type="button" class="btn btn-soft-danger h--45px flex-fill" disabled
                    data-toggle="tooltip" title="{{ $lockedReason }}">
                {{ translate('messages.Deny') }}
            </button>
            <button type="button" class="btn btn--primary h--45px flex-fill" disabled
                    data-toggle="tooltip" title="{{ $lockedReason }}">
                {{ translate('messages.Approve') }}
            </button>
        @else
            <button type="button" class="btn btn--reset h--45px flex-fill offcanvas-close">
                {{ translate('messages.Cancel') }}
            </button>

            @if($state === 'not_joined')
                @if($eligible && ! ($offer->has_ended ?? false))
                    <button type="button" class="btn btn--primary h--45px flex-fill join-offer" data-id="{{ $offer->id }}">
                        {{ translate('messages.Join') }}
                    </button>
                @else
                    <button type="button" class="btn btn--primary h--45px flex-fill" disabled
                            data-toggle="tooltip"
                            title="{{ ($offer->has_ended ?? false)
                                ? translate('messages.this_offer_has_already_ended')
                                : translate('messages.your_restaurant_does_not_support_the_order_types_of_this_offer') }}">
                        {{ translate('messages.Join') }}
                    </button>
                @endif
            @else
                {{-- Reworking the selection: allowed on a denial and on what is already
                     running, and gated on the same eligibility rule as joining. --}}
                {{-- Dropped once the offer has ended: resubmit runs through join's own guard,
                     which refuses it, so the button could only ever answer an error. --}}
                @if(in_array($state, ['approved', 'rejected'], true) && $eligible && ! ($offer->has_ended ?? false))
                    <button type="button" class="btn btn--reset h--45px flex-fill edit-and-resubmit"
                            data-id="{{ $offer->id }}" data-payload="{{ json_encode($resubmitPayload) }}">
                        {{ translate('messages.Edit_&_Resubmit') }}
                    </button>
                @endif

                {{-- The same delete either way; only the word changes. Hidden once the
                     record is closed: the endpoint refuses it, so the button could only ever
                     collect an error, and on a denial it would discard the admin's refusal
                     and its reason. --}}
                @if(! $locked)
                    <button type="button" class="btn btn--danger h--45px flex-fill form-alert"
                            data-id="drawer-leave-{{ $offer->id }}"
                            data-message="{{ $state === 'approved'
                                ? translate('messages.Want_to_leave_this_BOGO_offer')
                                : translate('messages.Want_to_cancel_this_request') }}">
                        {{ $state === 'approved'
                            ? translate('messages.Leave')
                            : translate('messages.Cancel_Request') }}
                    </button>
                @endif
            @endif
        @endif
    </div>

    {{-- Kept outside the footer so they never take a slot in the button flex row. --}}
    @if($locked)
    @elseif($state === 'admin_requested')
        <form action="{{ route('vendor.bogo-offer.respond', [$offer->id, 'approved']) }}"
              method="post" id="drawer-bogo-accept-{{ $offer->id }}">@csrf</form>
        <form action="{{ route('vendor.bogo-offer.respond', [$offer->id, 'rejected']) }}"
              method="post" id="drawer-bogo-decline-{{ $offer->id }}">@csrf</form>
    @elseif($state !== 'not_joined')
        <form action="{{ route('vendor.bogo-offer.leave', $offer->id) }}" method="post" id="drawer-leave-{{ $offer->id }}">
            @csrf @method('delete')
        </form>
    @endif
</div>
