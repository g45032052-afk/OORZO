{{--
    One BOGO bundle as a single order line, shared by the admin and vendor order pages.

    No thumbnail and no badge on the line itself: an offer has no single image that means
    anything, and the two strips already say what it is. The row is clickable and opens the
    bundle's contents; the strips are only a preview, because a bundle can hold more items
    than a table row has space for.

    Expects: $bogo (one entry from Helpers::bogo_order_groups), $sl, $has_addons.
--}}
@php
    $thumbLimit = 3;
    $modalId = 'bogo_details_'.\Illuminate\Support\Str::slug($bogo['group_id']);
@endphp
<tr class="bogo-order-row pointer" data-toggle="modal" data-target="#{{ $modalId }}"
    title="{{ translate('messages.View_bundle_items') }}">
    <td>
        <div>{{ $sl }}</div>
    </td>
    <td>
        <div class="media media--sm">
            <div class="media-body">
                <div class="mb-1">
                    <strong class="line--limit-1">
                        {{ $bogo['offer_title'] ?? translate('messages.BOGO_Offer') }}
                    </strong>
                </div>

                <div class="font-size-sm text-body">
                    {{ \App\CentralLogics\Helpers::format_currency($bogo['unit_price']) }}
                    <span class="text-muted">x</span> {{ $bogo['quantity'] }}
                </div>

                <div class="d-flex flex-wrap align-items-start mt-2" style="gap: 1.25rem;">
                    @foreach ([
                        ['label' => translate('messages.Buying_Item'), 'rows' => $bogo['buy']],
                        ['label' => translate('messages.Free_Item'), 'rows' => $bogo['free']],
                    ] as $side)
                        @if(count($side['rows']))
                            <div>
                                <div class="font-size-sm text-muted mb-1">{{ $side['label'] }}</div>
                                <div class="d-flex align-items-center">
                                    @foreach($side['rows']->take($thumbLimit) as $line)
                                        @php
                                            $lineFood = \App\Models\Food::select('id', 'image')->with('storage')->find($line->food_id);
                                            $lineName = $line->food_details
                                                ? (json_decode($line->food_details, true)['name'] ?? '')
                                                : '';
                                        @endphp
                                        <div class="avatar avatar-sm mr-1" title="{{ $lineName }}">
                                            <img class="img-fluid rounded onerror-image"
                                                 src="{{ $lineFood?->image_full_url ?? dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}"
                                                 data-onerror-image="{{ dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}"
                                                 alt="{{ $lineName }}">
                                        </div>
                                    @endforeach
                                    @if(count($side['rows']) > $thumbLimit)
                                        <span class="font-size-sm text-body ml-1">+{{ count($side['rows']) - $thumbLimit }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </td>
    @if(isset($has_addons) && $has_addons)
        {{-- Deliberately empty. A bundle's add-ons are inside its frozen price, so listing them
             here with their menu prices read as a charge on top - beside an Addon cost of 0.00.
             The cell itself stays so the row keeps the table's column count; which add-ons a
             bundle carries is shown, without prices, inside the bundle's own modal. --}}
        <td></td>
    @endif
    <td class="text-right">
        <div>
            <h5>{{ \App\CentralLogics\Helpers::format_currency($bogo['total']) }}</h5>
        </div>
    </td>
</tr>
