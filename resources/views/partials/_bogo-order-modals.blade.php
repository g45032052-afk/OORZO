{{--
    What is inside each BOGO bundle on this order, opened by clicking its row.

    Rendered after the table rather than inside it: a modal nested in a <tbody> is invalid
    markup and browsers move it, which detaches it from the trigger.

    Expects: $bogo_groups (from Helpers::bogo_order_groups).
--}}
@foreach($bogo_groups['bundles'] as $bogo)
    {{-- A php block rather than the inline directive form: the inline one stops at the first
         closing bracket, so a nested call leaves the tag open and the next line fails. --}}
    @php
        $modalId = 'bogo_details_'.\Illuminate\Support\Str::slug($bogo['group_id']);
    @endphp
    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $bogo['offer_title'] ?? translate('messages.BOGO_Offer') }}
                        <span class="badge badge-soft-info ml-1">{{ translate('messages.BOGO') }}</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between font-size-sm mb-3">
                        <span class="text-muted">{{ translate('qty') }} : <strong>{{ $bogo['quantity'] }}</strong></span>
                        <span class="text-muted">
                            {{ \App\CentralLogics\Helpers::format_currency($bogo['unit_price']) }}
                            {{ translate('messages.per_bundle') }}
                        </span>
                        <span><strong>{{ \App\CentralLogics\Helpers::format_currency($bogo['total']) }}</strong></span>
                    </div>

                    @foreach ([
                        ['label' => translate('messages.Buying_Item'), 'rows' => $bogo['buy'], 'free' => false],
                        ['label' => translate('messages.Free_Item'), 'rows' => $bogo['free'], 'free' => true],
                    ] as $side)
                        @if(count($side['rows']))
                            <div class="mb-3">
                                <div class="font-size-sm text-muted mb-2">{{ $side['label'] }}</div>
                                @foreach($side['rows'] as $line)
                                    @php
                                        $lineFood = \App\Models\Food::select('id', 'image')->with('storage')->find($line->food_id);
                                        $details = $line->food_details ? json_decode($line->food_details, true) : [];
                                        $variations = is_string($line->variation) ? json_decode($line->variation, true) : $line->variation;

                                        // The chosen option, not the question it answers. Printing
                                        // $variation['name'] alone rendered every line of a bundle as
                                        // "Size · color", so a Buy 2 Get 3 of one pizza in three
                                        // different sizes read as three identical lines.
                                        $chosen = collect((array) ($variations ?? []))
                                            ->map(function ($variation) {
                                                $labels = collect(data_get($variation, 'values', []))
                                                    ->pluck('label')->filter()->implode(', ');

                                                return $labels === ''
                                                    ? data_get($variation, 'name')
                                                    : data_get($variation, 'name').': '.$labels;
                                            })
                                            ->filter()->all();

                                        $lineAddons = collect(json_decode((string) $line->add_ons, true) ?: [])
                                            ->map(fn ($addon) => data_get($addon, 'name').' x'.data_get($addon, 'quantity', 1))
                                            ->filter()->all();
                                    @endphp
                                    <div class="media media--sm align-items-center mb-2">
                                        <div class="avatar avatar-sm mr-2">
                                            <img class="img-fluid rounded onerror-image"
                                                 src="{{ $lineFood?->image_full_url ?? dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}"
                                                 data-onerror-image="{{ dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}"
                                                 alt="{{ $details['name'] ?? '' }}">
                                        </div>
                                        <div class="media-body">
                                            <div class="font-weight-bold">{{ $details['name'] ?? translate('messages.item') }}</div>
                                            <div class="font-size-sm text-muted">
                                                {{ translate('qty') }} : {{ $line->quantity }}
                                                @foreach($chosen as $label)
                                                    &middot; {{ $label }}
                                                @endforeach
                                            </div>
                                            @if(count($lineAddons))
                                                <div class="font-size-sm text-muted">
                                                    {{ translate('messages.addons') }} : {{ implode(', ', $lineAddons) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            @if($side['free'])
                                                <span class="badge badge-soft-success">{{ translate('messages.Free') }}</span>
                                                {{-- What the restaurant gave away on this line, so a
                                                     bundle of three free items is not three identical
                                                     badges with no worth between them. --}}
                                                @if((float) ($line->bogo_free_value ?? 0) > 0)
                                                    <div class="font-size-sm text-muted">
                                                        <del>{{ \App\CentralLogics\Helpers::format_currency($line->bogo_free_value) }}</del>
                                                    </div>
                                                @endif
                                            @else
                                                <strong>{{ \App\CentralLogics\Helpers::format_currency($line->price) }}</strong>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endforeach
