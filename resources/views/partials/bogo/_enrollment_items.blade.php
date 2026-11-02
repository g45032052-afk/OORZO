{{--
    The Buy Food / Get Food lists of one enrolment, shared by the admin and vendor drawers so
    the two cannot drift apart.

    Every row is read from the frozen snapshot on the enrolment, never from the menu: a food
    edited after the restaurant joined must not change what this enrolment says it agreed to.

    A row carries the food it points at and the selection frozen against it, and clicking one
    reopens the picker's own food config modal in read-only mode - the same panel used to choose
    the food in the first place, with its options ticked, inert, and nothing to submit.

    Expects: $enrollment, $addOnNames (id => name), $surface (css class for the row badge).
    Optional: $addOnPrices (id => price).
--}}
@php
    $surface = $surface ?? 'bg-light';
    $addOnPrices = $addOnPrices ?? collect();
@endphp

@foreach([
    ['buy', $enrollment->buyItems, translate('messages.Buy_Food'), translate('messages.Customers_must_buy_the_selected_Foods_with_the_specific_quantities_to_qualify')],
    ['get', $enrollment->getItems, translate('messages.Get_Food'), translate('messages.Customers_must_buy_all_selected_Foods_in_the_specified_quantities_to_qualify')],
] as [$type, $items, $heading, $blurb])
    <h5 class="fs-18 font-bold mb-2 {{ $type === 'get' ? 'mt-4' : '' }}">{{ $heading }}</h5>
    <p class="fs-14 text-muted mb-3">{{ $blurb }}</p>

    <div class="bg-white rounded-8">
        @forelse($items as $item)
            @php
                $variationLines = collect($item->variations ?? [])
                    ->map(fn ($g) => data_get($g, 'name').' : '.implode(', ', data_get($g, 'values.label', [])))
                    ->filter()->values();

                // Name, how many of it, and what it costs - the quantity and the price were
                // both missing, so an add-on read as a bare word with no bearing on the total.
                $addOnQtys = collect($item->add_on_qtys ?? [])->values();
                $addOns = collect($item->add_on_ids ?? [])->values()
                    ->map(function ($id, $index) use ($addOnNames, $addOnPrices, $addOnQtys) {
                        if (! isset($addOnNames[$id])) {
                            return null;
                        }

                        return [
                            'name' => $addOnNames[$id],
                            'quantity' => (int) ($addOnQtys[$index] ?? 1),
                            'price' => isset($addOnPrices[$id]) ? (float) $addOnPrices[$id] : null,
                        ];
                    })
                    ->filter()->values();

                // What the line is worth. A get item is priced at 0 because it is the give-away,
                // so its worth lives in original_price - and that is the figure worth showing.
                $worth = (float) ($item->original_price ?? $item->price);

                // What the food config modal needs to reopen this line read-only: the food to
                // fetch, and the selection frozen on the enrolment to tick inside it.
                $payload = [
                    'food_id' => (int) $item->food_id,
                    'restaurant_id' => (int) $enrollment->restaurant_id,
                    'is_free' => $type === 'get',
                    'price' => \App\CentralLogics\Helpers::format_currency($worth),
                    'selected_variations' => $item->variations ?? [],
                    'add_on_ids' => collect($item->add_on_ids ?? [])->map('intval')->values()->all(),
                    'add_on_qtys' => collect($item->add_on_qtys ?? [])->map('intval')->values()->all(),
                ];
            @endphp

            <div class="d-flex align-items-center gap-3 p-3 pointer bogo-item-row {{ $loop->last ? '' : 'border-bottom' }}"
                 role="button" tabindex="0"
                 title="{{ translate('messages.View_item_details') }}"
                 data-item="{{ json_encode($payload) }}">
                <img class="rounded-8 onerror-image flex-shrink-0"
                     style="width:50px;height:50px;object-fit:cover"
                     data-onerror-image="{{ dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}"
                     src="{{ \App\CentralLogics\Helpers::get_full_url('product', $item->food_image, 'public', 'product') }}"
                     alt="food">

                <div class="flex-grow-1" style="min-width:0">
                    <span class="d-block fs-15 mb-1">{{ $item->food_name }}</span>
                    @foreach($variationLines as $line)
                        <span class="d-block fs-13 text-muted">{{ $line }}</span>
                    @endforeach
                    @if($addOns->count())
                        <span class="d-block fs-13 text-muted">
                            {{ translate('messages.Add_Ons') }} :
                            {{ $addOns->map(fn ($a) => $a['name'].' x'.$a['quantity'])->implode(', ') }}
                        </span>
                    @endif
                </div>

                {{-- The price, not the quantity: a bundle line is always one of the item, so the
                     "1" said nothing that the row did not already imply. --}}
                <span class="{{ $surface }} rounded-8 d-center fs-15 flex-shrink-0 font-medium"
                      style="min-width:40px;height:36px;padding:0 10px">{{ $payload['price'] }}</span>
            </div>
        @empty
            <p class="fs-14 text-muted mb-0 p-3">{{ translate('no_data_found') }}</p>
        @endforelse
    </div>
@endforeach
