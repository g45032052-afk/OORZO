@php
    // One word for the enrolment. An admin assignment waits on the restaurant, so the admin
    // may only cancel it - the Approve/Reject pair belongs to requests the restaurant raised.
    $state = $enrollment->status === 'pending'
        ? ($enrollment->requested_by === 'admin' ? 'admin_requested' : 'pending')
        : $enrollment->status;

    $statusBadge = [
        'pending'         => ['badge-soft-info', translate('messages.Pending')],
        'admin_requested' => ['badge-soft-secondary', translate('messages.Waiting_on_Restaurant')],
        'approved'        => ['badge-soft-success', translate('messages.Approved')],
        'rejected'        => ['badge-soft-danger', translate('messages.Denied')],
    ][$state] ?? ['badge-soft-secondary', translate($enrollment->status)];

    // Only an approved enrolment has a customer-facing state to report: everything else is
    // still waiting on somebody, which the status badge already says.
    $live = $state !== 'approved'
        ? null
        : (empty($hiddenReasons ?? [])
            ? ['badge-soft-success', translate('messages.Running_now')]
            : ['badge-soft-warning', translate('messages.Not_visible_to_customers')]);

    // Frozen items mapped back into the drawer's working shape for the edit buttons. Both
    // post to the same endpoint, which decides the outcome from the state: a pending request
    // is approved, a denied one goes back out as an admin request.
    $editPayload = [
        'restaurant_id' => $enrollment->restaurant_id,
        // The add drawer's dropdown only lists restaurants that have not joined yet, so the
        // one being edited is never among them - the name lets us inject its option.
        'restaurant_name' => $enrollment->restaurant->name ?? '',
        'enrollment_id' => $enrollment->id,
        'url' => route('admin.bogo-offer.update-enrollment', [$offer->id, $enrollment->id]),
        'is_resubmit' => $state === 'rejected',
        'submit_label' => $state === 'rejected'
            ? translate('messages.Edit_&_Resubmit')
            : translate('messages.Edit_&_Approve'),
        'buy' => $enrollment->buyItems->map(fn ($i) => [
            'food_id' => $i->food_id,
            'quantity' => $i->quantity,
            'selected_variations' => $i->variations ?? [],
            'add_on_ids' => $i->add_on_ids ?? [],
            'add_on_qtys' => $i->add_on_qtys ?? [],
        ])->values(),
        'get' => $enrollment->getItems->map(fn ($i) => [
            'food_id' => $i->food_id,
            'quantity' => $i->quantity,
            'selected_variations' => $i->variations ?? [],
            'add_on_ids' => $i->add_on_ids ?? [],
            'add_on_qtys' => $i->add_on_qtys ?? [],
        ])->values(),
    ];
@endphp

<div class="d-flex flex-column" style="height:100%">
    <div class="custom-offcanvas-header d-flex justify-content-between align-items-center px-3 py-3">
        <div class="d-flex align-items-center gap-3">
            <h2 class="mb-0 fs-18 text-title font-bold">
                {{ translate('messages.Restaurant') }} #{{ $enrollment->id }}
            </h2>
            <span class="badge {{ $statusBadge[0] }} fs-13 fw-400 px-2 py-1">{{ $statusBadge[1] }}</span>
            @if($live)
                <span class="badge {{ $live[0] }} fs-13 fw-400 px-2 py-1">{{ $live[1] }}</span>
            @endif
        </div>
        <button type="button"
                class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                aria-label="Close">&times;</button>
    </div>

    <div class="custom-offcanvas-body p-20 flex-grow-1">
        <div class="bg-global-gray rounded-8 p-20 mb-20">
            <h6 class="fs-16 font-bold mb-3">{{ translate('messages.Restaurant_Information') }}</h6>

            @if($enrollment->restaurant)
                <div class="d-flex align-items-center gap-3">
                    <img class="rounded-circle onerror-image flex-shrink-0"
                         style="width:56px;height:56px;object-fit:cover"
                         data-onerror-image="{{ dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}"
                         src="{{ $enrollment->restaurant->logo_full_url }}" alt="restaurant">
                    <div style="min-width:0">
                        <strong class="d-block fs-16 font-bold mb-1">{{ $enrollment->restaurant->name }}</strong>
                        <span class="d-block fs-13 text-muted">
                            {{ $enrollment->restaurant->phone }} | {{ $enrollment->restaurant->email }}
                        </span>
                        <span class="d-block fs-13 text-muted">
                            {{ translate('messages.Joined_At') }} :
                            {{ $enrollment->joined_at ? $enrollment->joined_at->format('n/j/Y, g:iA') : 'N/A' }}
                        </span>
                    </div>
                </div>
            @else
                <span class="fs-14 text-muted">{{ translate('messages.restaurant_deleted') }}</span>
            @endif
        </div>

        {{-- Approving is the admin's answer to the restaurant; it is not the customer's view
             of the bundle. The two come apart the moment a food sells out, the store closes
             for the day or the offer's usage cap fills, and the drawer used to say only
             "Approved" - leaving the admin with nothing to answer the restaurant with. --}}
        @if(! empty($hiddenReasons ?? []))
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
            {{-- Name who said no: the admin's own denial and a restaurant declining an admin
                 request land in the same state and need different words. --}}
            <div class="alert-soft-danger rounded-8 p-3 mb-20 fs-14">
                <strong class="d-block mb-1">
                    {{ $enrollment->rejected_by === 'restaurant'
                        ? translate('messages.The_restaurant_declined_this_offer')
                        : translate('messages.You_denied_this_request') }}
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

        <div class="bg-global-gray rounded-8 p-20">
            @include('partials.bogo._enrollment_items', [
                'enrollment' => $enrollment,
                'addOnNames' => $addOnNames,
                'addOnPrices' => $addOnPrices ?? collect(),
                'surface' => 'bg-global-gray',
            ])
        </div>
    </div>

    <div class="offcanvas-footer bg-white p-3 d-flex align-items-center gap-3">
        @if($state === 'pending')
            {{-- The restaurant asked: deny it, rework it and approve, or approve as it
                 stands. Removing it outright is not on offer - the restaurant is owed the
                 refusal and its reason. --}}
            <button type="button" class="btn btn-soft-danger h--45px flex-fill reject-enrollment"
                    data-url="{{ route('admin.bogo-offer.restaurant-confirmation', [$offer->id, $enrollment->id, 'rejected']) }}">
                {{ translate('messages.Reject') }}
            </button>

            <button type="button" class="btn btn--reset h--45px flex-fill edit-and-approve"
                    data-payload="{{ json_encode($editPayload) }}">
                {{ translate('messages.Edit_&_Approve') }}
            </button>

            <button type="button" class="btn btn--primary h--45px flex-fill approve-enrollment"
                    data-url="{{ route('admin.bogo-offer.restaurant-confirmation', [$offer->id, $enrollment->id, 'approved']) }}">
                {{ translate('messages.Approve') }}
            </button>
        @else
            <button type="button" class="btn btn--reset h--45px flex-fill offcanvas-close">
                {{ translate('messages.Back') }}
            </button>

            {{-- A denied enrolment can be reworked and sent back out as an admin request, so
                 the restaurant still gets the last word on foods it did not choose. --}}
            @if($state === 'rejected')
                <button type="button" class="btn btn--reset h--45px flex-fill edit-and-approve"
                        data-payload="{{ json_encode($editPayload) }}">
                    {{ translate('messages.Edit_&_Resubmit') }}
                </button>
            @endif

            {{-- The same delete either way; only the word changes. Withdrawing a request that
                 never went live is a cancel, dropping a running restaurant is a delete. --}}
            <button type="button" class="btn btn--danger h--45px flex-fill form-alert"
                    data-id="drawer-enrollment-{{ $enrollment->id }}"
                    data-message="{{ $state === 'approved'
                        ? translate('messages.Want_to_remove_this_restaurant_from_the_offer')
                        : translate('messages.Want_to_cancel_this_request') }}">
                {{ $state === 'approved' ? translate('messages.Delete') : translate('messages.Cancel_Request') }}
            </button>
        @endif
    </div>

    @if($state !== 'pending')
        {{-- Kept outside the footer so it never takes a slot in the button flex row. --}}
        <form action="{{ route('admin.bogo-offer.remove-restaurant', [$offer->id, $enrollment->id]) }}"
              method="post" id="drawer-enrollment-{{ $enrollment->id }}">
            @csrf @method('delete')
        </form>
    @endif
</div>
