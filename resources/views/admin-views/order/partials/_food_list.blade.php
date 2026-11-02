@php
    // A bundle is one thing the customer chose, so it is one row here too - listing its foods
    // separately invited edits the save has to refuse, and showed the free item at 0.00 as if
    // something were wrong. The row's controls address the whole group.
    $entries = App\CentralLogics\Helpers::bogo_editor_entries($carts);
@endphp

   <div class="d-flex flex-wrap align-items-center gap-2 mb-10px">
       <span class="fs-16 font-semibold text-dark">{{ translate('Food List') }}</span>
       <div
           class="w-20px h-20px text-dark rounded-circle d-flex align-items-center justify-content-center bg-list-count fs-12 font-semibold">
           {{ count($entries) }}</div>
   </div>

   <div class="table-responsive pt-0 card mb-20">
       <table
           class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table dataTable no-footer mb-0">
           <thead class="border-0 initial-94">
               <tr>
                   <th class="border-0">{{ translate('sl') }}</th>
                   <th class="border-0">{{ translate('Item details') }}</th>
                   <th class="border-0 text-center">{{ translate('Qty') }}</th>
                   <th class="border-0 text-right">{{ translate('Total') }}</th>
                   <th class="border-0">{{ translate('Action') }}</th>
               </tr>
           </thead>
           <tbody>
               @foreach (array_reverse($entries) as $key => $entry)
                   @php
                       $isBogo = $entry['is_bogo'];
                       $item = $isBogo ? $entry : $entry['cart'];
                   @endphp
                   <tr class="custom__tr {{ !$isBogo && data_get($item, 'new_item') ? 'active' : '' }} ">
                       <td>
                           <div>
                               {{ $key + 1 }}
                           </div>
                       </td>
                       <td>
                           {{-- Only an ordinary item opens the edit drawer. A bundle's foods,
                                variations and add-ons were fixed when the restaurant enrolled,
                                so there is nothing on it to edit. --}}
                           <div class="list-items-media d-flex align-items-center gap-2 {{ $isBogo ? '' : 'cursor-pointer quick_view_cart_item' }}"
                               @if (!$isBogo) data-cart_id="{{ $item['cart_id'] }}" data-id="{{ $item['item_id'] }}" @endif>
                               <img width="44" height="44"
                                   src="{{ $item['image_full_url'] ?? dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}"
                                   alt="image" class="rounded">
                               <div class="cont d-flex flex-column gap-1">
                                   <p class="fs-12 text-dark mb-0 max-w-187px line--limit-1">{{ $item['name'] ?? '' }}
                                   </p>
                                   @if ($isBogo)
                                       <span class="badge badge-soft-info fs-10 align-self-start">{{ translate('messages.BOGO') }}</span>
                                       <span class="fs-10 text-muted max-w-187px line--limit-1">
                                           @foreach ($entry['lines'] as $line)
                                               {{ $line['name'] }}@if (data_get($line, 'is_free_item')) ({{ translate('messages.Free') }})@endif{{ !$loop->last ? ' + ' : '' }}
                                           @endforeach
                                       </span>
                                   @endif
                               </div>
                           </div>
                       </td>
                       <td>
                           <div class="product-quantity w-105px mx-auto">
                               <div
                                   class="input-group bg-white rounded border d-flex justify-content-center align-items-center">
                                   <span class="input-group-btn w-30px">
                                       <button class="btn px-2 btn-number w-30px decrease-quantity-button"
                                           data-id="{{ $item['cart_id'] }}" type="button" data-type="minus">
                                           <i class="tio-remove  font-weight-bold"></i>
                                       </button>
                                   </span>

                                   {{-- On a bundle this counts whole bundles, and the handler
                                        scales every line in the group by it. --}}
                                   <input type="number"
                                       class="w-25px  form-control p-0 border-0 text-center update-Quantity text-dark"
                                       name="food_quantity" id="update_quantity_{{ $item['cart_id'] }}" placeholder="1"
                                       min="1"
                                       value="{{ $item['quantity'] }}" data-value="{{ $item['quantity'] }}"
                                       data-food_id="{{ $isBogo ? data_get($entry['lines'][0], 'item_id') : $item['item_id'] }}"
                                       data-option_ids="{{ json_encode($isBogo ? [] : $item['variation_options']) }}"
                                       data-key="{{ $item['cart_id'] }}"
                                       data-variation_options_old="{{ json_encode($isBogo ? [] : data_get($item, 'variation_options_old', [])) }}"
                                       data-addon_price="{{ $isBogo ? 0 : data_get($item, 'addon_price', 0) }}"
                                       data-order_detail_id="{{ data_get($item, 'order_detail_id', 0) }}"
                                       data-maximum_quantity="{{ $isBogo ? 9999999999 : $item['maximum_cart_quantity'] }}">


                                   <span class="input-group-btn w-30px">
                                       <button class="btn px-2 btn-number increase-quantity-button w-30px"
                                           data-id="{{ $item['cart_id'] }}" type="button" data-type="plus">
                                           <i class="tio-add  font-weight-bold"></i>
                                       </button>
                                   </span>

                               </div>
                           </div>
                       </td>
                       <td class="fs-14 text-right" >
                        <div id="item_total_price_{{ $item['cart_id'] }}">
                            {{ App\CentralLogics\Helpers::format_currency($item['price'] ) }}
                        </div>
                       </td>
                       <td class="text-center">
                        {{-- Counting entries, not rows: a bundle is one of them, so an order
                             holding only a bundle still refuses to have it removed. --}}
                        @if (count($entries) > 1)
                            <button
                                class="btn rounded-circle mx-auto p-1 d-flex align-items-center justify-content-center w-25px h-25px btn-sm btn--danger remove_from_cart_id"
                                data-toggle="modal" data-target="#remove-from-cart"
                                data-cart_id="{{ $item['cart_id'] }}" type="button">
                                <i class="tio-delete text-white"></i>
                            </button>


                            @else
                            <button
                                class="btn rounded-circle mx-auto p-1 d-flex align-items-center disable-delete-btn-text justify-content-center w-25px h-25px btn-sm btn--secondary "
                                type="button">
                                <i class="tio-delete text-white"></i>
                            </button>


                        @endif
                       </td>
                   </tr>
               @endforeach

           </tbody>
       </table>
   </div>
