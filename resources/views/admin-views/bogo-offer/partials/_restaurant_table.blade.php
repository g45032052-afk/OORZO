@foreach ($enrollments as $key => $enrollment)
    @php
        // One word for the row. An admin assignment waits on the restaurant, so it is not
        // the admin's to decide - only to cancel.
        $state = $enrollment->status === 'pending'
            ? ($enrollment->requested_by === 'admin' ? 'admin_requested' : 'pending')
            : $enrollment->status;

        // Who said no: the admin's own denial and a restaurant declining an admin request
        // land in the same state and need different words.
        $rejectionNote = $state !== 'rejected' ? null : trim(
            ($enrollment->rejected_by === 'restaurant'
                ? translate('messages.The_restaurant_declined_this_offer')
                : translate('messages.You_denied_this_request'))
            .($enrollment->rejection_reason ? ' : '.$enrollment->rejection_reason : '')
        );

        // Frozen items mapped back into the add drawer's working shape, so the row's edit
        // icon opens the same picker the enrolment drawer does.
        $editItems = fn ($items) => $items->map(fn ($i) => [
            'food_id' => $i->food_id,
            'quantity' => $i->quantity,
            'selected_variations' => $i->variations ?? [],
            'add_on_ids' => $i->add_on_ids ?? [],
            'add_on_qtys' => $i->add_on_qtys ?? [],
        ])->values();

        $editPayload = $state !== 'rejected' ? null : [
            'restaurant_id' => $enrollment->restaurant_id,
            // The add drawer's dropdown only lists restaurants yet to join, so the one being
            // edited is never among them - the name lets us inject its option.
            'restaurant_name' => $enrollment->restaurant->name ?? '',
            'enrollment_id' => $enrollment->id,
            'url' => route('admin.bogo-offer.update-enrollment', [$offer->id, $enrollment->id]),
            'is_resubmit' => true,
            'submit_label' => translate('messages.Edit_&_Resubmit'),
            'buy' => $editItems($enrollment->buyItems),
            'get' => $editItems($enrollment->getItems),
        ];
    @endphp

    <tr>
        <td>{{ $key + $enrollments->firstItem() }}</td>
        <td>
            @if($enrollment->restaurant)
                <a href="{{ route('admin.restaurant.view', $enrollment->restaurant->id) }}" class="table-rest-info">
                    <img class="onerror-image" data-onerror-image="{{dynamicAsset('assets/admin/img/100x100/food-default-image.png')}}"
                         src="{{ $enrollment->restaurant->logo_full_url }}" alt="restaurant">
                    <div class="info">
                        <span class="d-block text-body">{{ Str::limit($enrollment->restaurant->name, 20, '...') }}</span>
                        <span class="d-block font-size-sm opacity-75">{{ $enrollment->restaurant->phone }}</span>
                    </div>
                </a>
            @else
                <span class="text-muted">{{ translate('messages.restaurant_deleted') }}</span>
            @endif
        </td>
        <td>{{ $enrollment->joined_at ? $enrollment->joined_at->format('d M Y') : 'N/A' }}</td>

        {{-- A single item shows inline; several collapse to a link that opens the drawer. --}}
        @foreach (['buy' => $enrollment->buyItems, 'get' => $enrollment->getItems] as $items)
            <td>
                @if($items->count() === 0)
                    <span class="text-muted">N/A</span>
                @elseif($items->count() === 1)
                    @php($item = $items->first())
                    @include('admin-views.bogo-offer.partials._food_cell', ['item' => $item, 'addOnNames' => $addOnNames])
                @else
                    <a href="javascript:" class="text-primary enrollment-detail" style="text-decoration:underline"
                       data-url="{{ route('admin.bogo-offer.enrollment-detail', [$offer->id, $enrollment->id]) }}">
                        {{ $items->count() }} {{ translate('messages.Foods') }}
                    </a>
                @endif
            </td>
        @endforeach

        <td class="text-capitalize">
            @if ($state === 'pending')
                <span class="badge badge-soft-info">{{ translate('messages.Pending') }}</span>
            @elseif($state === 'admin_requested')
                {{-- The admin raised this one, so it is not the admin's to decide - it waits
                     on the restaurant's answer. --}}
                <span class="badge badge-soft-secondary">{{ translate('messages.Waiting_on_Restaurant') }}</span>
            @elseif($state === 'approved')
                <span class="badge badge-soft-success">{{ translate('messages.Approved') }}</span>
            @else
                <span class="badge badge-soft-danger" data-toggle="tooltip" title="{{ $rejectionNote }}">
                    {{ translate('messages.Denied') }}
                </span>
            @endif
        </td>

        <td>
            <div class="btn--container justify-content-center">
                <a class="btn btn-sm btn--primary btn-outline-primary action-btn enrollment-detail" href="javascript:"
                   data-url="{{ route('admin.bogo-offer.enrollment-detail', [$offer->id, $enrollment->id]) }}"
                   title="{{ translate('messages.view') }}">
                    <i class="tio-visible"></i>
                </a>

                {{-- Approved and still not on the customer's screen: the reasons are named on
                     hover, so the admin can answer the restaurant without opening the drawer.
                     Nothing is drawn while the bundle is running normally. --}}
                @include('partials.promotion._visibility_warning', [
                    'reasons' => $hiddenReasons[$enrollment->id] ?? [],
                    'class' => 'btn-sm action-btn',
                ])

                @if ($state === 'pending')
                    {{-- The restaurant asked. Approve or deny it here; the drawer also offers
                         Edit & Approve. Denied enrolments are reworked, not re-approved. --}}
                    <a class="btn btn-sm btn-outline-success action-btn approve-enrollment" href="javascript:"
                       data-url="{{ route('admin.bogo-offer.restaurant-confirmation', [$offer->id, $enrollment->id, 'approved']) }}"
                       title="{{ translate('Approve') }}">
                        <i class="tio-done font-weight-bold"></i>
                    </a>
                    <a class="btn btn-sm btn--danger btn-outline-danger action-btn reject-enrollment" href="javascript:"
                       data-url="{{ route('admin.bogo-offer.restaurant-confirmation', [$offer->id, $enrollment->id, 'rejected']) }}"
                       title="{{ translate('Deny') }}">
                        <i class="tio-clear font-weight-bold"></i>
                    </a>
                @else
                    {{-- A denied enrolment can be reworked and sent back out as an admin
                         request, so the restaurant still gets the last word. --}}
                    @if ($state === 'rejected')
                        <a class="btn btn-sm btn--primary btn-outline-primary action-btn edit-and-approve" href="javascript:"
                           data-payload="{{ json_encode($editPayload) }}"
                           title="{{ translate('messages.Edit_&_Resubmit') }}">
                            <i class="tio-edit"></i>
                        </a>
                    @endif

                    {{-- The same delete either way; only the wording changes. A restaurant's
                         pending request is denied instead - it is owed the refusal. --}}
                    <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert" href="javascript:"
                       data-id="enrollment-{{ $enrollment->id }}"
                       data-message="{{ $state === 'approved'
                            ? translate('messages.Want_to_remove_this_restaurant_from_the_offer')
                            : translate('messages.Want_to_cancel_this_request') }}"
                       title="{{ $state === 'approved' ? translate('messages.delete') : translate('messages.Cancel_Request') }}">
                        <i class="tio-delete-outlined"></i>
                    </a>
                    <form action="{{ route('admin.bogo-offer.remove-restaurant', [$offer->id, $enrollment->id]) }}"
                          method="post" id="enrollment-{{ $enrollment->id }}">
                        @csrf @method('delete')
                    </form>
                @endif
            </div>
        </td>
    </tr>
@endforeach
