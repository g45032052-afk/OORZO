@php
    use App\CentralLogics\Helpers;
    use App\Models\AddOn;
    use App\Scopes\RestaurantScope;
@endphp
<div class="initial-49">
    <form id="add-to-cart-form">
        <div class="modal-header p-0">
            <h4 class="modal-title product-title">
            </h4>
            <button class="close call-when-done" type="button" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body pt-2 pb-0">
            <div class="d-flex flex-row align-items-center mb-xxl-3 mb-2">

                <div class="d-flex align-items-center justify-content-center active h-9rem">
                    <div class="thumb-q position-relative">
                        @if (config('toggle_veg_non_veg'))
                            <span
                                class="badge top-0 left-0 badge-{{ $product->veg ? 'success' : 'danger' }} position-absolute">{{ $product->veg ? translate('messages.veg') : translate('messages.non_veg') }}</span>
                        @endif
                        <img class="img-responsive mr-3 img--100 onerror-image"
                            src="{{ data_get($product, 'image_full_url') ?? dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}"
                            data-onerror-image="{{ dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}"
                            data-zoom="{{ dynamicStorage('storage/app/public/product') }}/{{ data_get($product, 'image') }}"
                            alt="Product image">
                    </div>
                    <div class="cz-image-zoom-pane"></div>
                </div>

                <div class="details pl-2">
                    <a href="{{ route('admin.food.view', $product->id) }}"
                        class="h3 mb-2 product-title text-capitalize text-break d-block">{{ $product->name }}</a>

                    <div class="mb-3 text-dark">
                        <span class="h4 font-weight-normal text-accent mr-1">
                            {{ Helpers::get_price_range($product, true) }}
                        </span>
                        @if ($product->discount > 0 || Helpers::get_effective_restaurant_discount($product->restaurant))
                            <span class="fz-12px line-through">
                                {{ Helpers::get_price_range($product) }}
                            </span>
                        @endif
                    </div>



                    @if ($product->discount > 0)
                        <div class="mb-3 text-dark fs-12">
                            <strong class="fs-12">{{ translate('messages.discount') }} : </strong>
                            <strong class="fs-12" id="set-discount-amount">{{ Helpers::get_product_discount($product) }}</strong>
                        </div>
                    @endif

                </div>
            </div>

            <div class="row pt-2">
                <div class="col-12">
                    <div class="mb-20">
                        <h5 class="lh-1 mb-xl-2 mb-1">{{ translate('messages.description') }}</h5>
                        <span class="product-description text-dark">{!! $product->description !!}</span>
                        <a href="#" class="text-base see-more"
                            style="display:none; margin-left:6px;">{{ translate('messages.see_more') }}</a>
                        </span>
                    </div>
                    @csrf
                    <input type="hidden" name="id" value="{{ $product->id }}">
                    <input type="hidden" name="item_type" value="{{ $item_type }}">
                    <input type="hidden" name="cart_id" value="{{ $cart_item['cart_id'] }}">
                    <input type="hidden" name="order_id" value="{{ $order_id }}">
                    @php($values = [])
                    @php($selected_variations = $cart_item['variations'])
                    @php($names = [])
                    @if (is_array($selected_variations))
                        @foreach ($selected_variations as $key => $var)
                            @if (isset($var['values']))
                                @foreach ($var['values'] as $k => $item)
                                    @php($values[$key][] = data_get($item, 'label'))
                                @endforeach
                            @endif
                        @endforeach
                    @endif
                    <?php
                    $old_selected_variations = [];
                    $old_selected_addons = [];
                    $OrderDetail = App\Models\OrderDetail::where('id', data_get($cart_item, 'order_detail_id'))->first();
                    if ($OrderDetail) {
                        if ($OrderDetail->variation != '[]') {
                            foreach (json_decode($OrderDetail->variation, true) ?? [] as $value) {
                                foreach (data_get($value, 'values', []) as $item) {
                                    if (data_get($item, 'option_id', null) != null) {
                                        $old_selected_variations[data_get($item, 'option_id')] = $OrderDetail['quantity'];
                                    }
                                }
                            }
                        }

                        foreach (json_decode($OrderDetail->add_ons, true) as $old_add_ons) {
                            if (data_get($old_add_ons, 'id', null) != null) {
                                $old_selected_addons[data_get($old_add_ons, 'id')] = data_get($old_add_ons, 'quantity');
                            }
                        }
                    }

                    ?>

                    @foreach (json_decode($product->variations) as $key => $choice)
                        @if (isset($choice->name) && isset($choice->values))
                            <div class="__bg-FAFAFA rounded p-sm-3 p-2 mb-20">
                                <div class="h3 p-0 fs-16">{{ $choice->name }} <small class="text-muted fs-12">
                                        ({{ $choice->required == 'on' ? translate('messages.Required') : translate('messages.optional') }}
                                        ) </small>
                                </div>
                                @if ($choice->min != 0 && $choice->max != 0)
                                    <small class="d-block mb-3">
                                        {{ translate('You_need_to_select_minimum_ ') }} {{ $choice->min }}
                                        {{ translate('to_maximum_ ') }} {{ $choice->max }}
                                        {{ translate('options') }}
                                    </small>
                                @endif
                                <input type="hidden" name="variations[{{ $key }}][min]"
                                    value="{{ $choice->min }}">
                                <input type="hidden" name="variations[{{ $key }}][max]"
                                    value="{{ $choice->max }}">
                                <input type="hidden" name="variations[{{ $key }}][required]"
                                    value="{{ $choice->required }}">
                                <input type="hidden" name="variations[{{ $key }}][name]"
                                    value="{{ $choice->name }}">


                                @foreach ($choice->values as $k => $option)
                                    <div class="form-check form--check d-flex pr-5 mr-5">
                                        <input
                                            class="form-check-input  input-element {{ data_get($option, 'stock_type') && data_get($option, 'stock_type') !== 'unlimited' && data_get($option, 'current_stock') <= 0 ? 'stock_out' : '' }}"
                                            data-option_id="{{ data_get($option, 'option_id') }}"
                                            type="{{ $choice->type == 'multi' ? 'checkbox' : 'radio' }}"
                                            id="choice-option-{{ $key }}-{{ $k }}"
                                            name="variations[{{ $key }}][values][label][]"
                                            value="{{ $option->label }}"
                                            @if (isset($values[$key])) {{ in_array($option->label, $values[$key]) && !(data_get($option, 'stock_type') && data_get($option, 'stock_type') !== 'unlimited' && data_get($option, 'current_stock') <= 0) ? 'checked' : '' }} @endif
                                            {{ data_get($option, 'stock_type') && data_get($option, 'stock_type') !== 'unlimited' && data_get($option, 'current_stock') <= 0 ? 'disabled' : '' }}
                                            autocomplete="off">
                                        <label
                                            class="form-check-label {{ data_get($option, 'stock_type') && data_get($option, 'stock_type') !== 'unlimited' && data_get($option, 'current_stock') <= 0 ? 'stock_out text-muted' : 'text-dark' }}"
                                            for="choice-option-{{ $key }}-{{ $k }}">{{ Str::limit($option->label, 20, '...') }}

                                            &nbsp;
                                            <span
                                                class="input-label-secondary text--title text--warning {{ data_get($option, 'stock_type') && data_get($option, 'stock_type') !== 'unlimited' && data_get($option, 'current_stock') <= 0 ? '' : 'd-none' }}"
                                                title="{{ translate('Currently_you_need_to_manage_discount_with_the_Restaurant.') }}">
                                                <i class="tio-info text-gray1 fs-16"></i>
                                                <small>{{ translate('stock_out') }}</small>
                                            </span>

                                        </label>
                                        <span
                                            class="ml-auto">{{ Helpers::format_currency($option->optionPrice) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach

                    {{-- {{ dd(json_encode($old_selected_variations)) }} --}}

                    <input type="hidden" hidden name="old_selected_variations"
                        value="{{ json_encode($old_selected_variations) }}">
                    @if (count($old_selected_variations) == 0)
                        <input type="hidden" hidden name="old_selected_without_variation"
                            value="{{ $cart_item['quantity'] }}">
                    @endif
                    <input type="hidden" hidden name="old_selected_addons"
                        value="{{ json_encode($old_selected_addons) }}">

                    <input type="hidden" hidden name="option_ids" id="option_ids">

                    <!-- Quantity + Add to cart -->
                    <div class="__bg-FAFAFA rounded p-sm-3 p-2 mb-20">
                        <div class="d-flex justify-content-between">
                            <div class="product-description-label mt-2 text-dark h4">
                                {{ translate('messages.quantity') }}:
                            </div>
                            <div class="product-quantity d-flex align-items-center">
                                <div class="input-group input-group--style-2 pr-3 w-160px">
                                    <span class="input-group-btn">
                                        <button class="btn btn-number text-dark" type="button" data-type="minus"
                                            data-field="quantity"
                                            {{ $cart_item['quantity'] <= 1 ? 'disabled="disabled"' : '' }}>
                                            <i class="tio-remove  font-weight-bold"></i>
                                        </button>
                                    </span>
                                    <label for="add_new_product_quantity">
                                    </label>
                                    <input id="add_new_product_quantity" type="text" name="quantity"
                                        class="form-control input-number p-2 rounded text-center cart-qty-field"
                                        placeholder="1" value="{{ $cart_item['quantity'] }}" min="1"
                                        data-maximum_cart_quantity='{{ $cart_item['maximum_cart_quantity']  }}'
                                        max="{{ $cart_item['maximum_cart_quantity'] }}">

                                    <span class="input-group-btn">
                                        <button class="btn btn-number text-dark" type="button" data-type="plus"
                                            id="quantity_increase_button" data-field="quantity">
                                            <i class="tio-add  font-weight-bold"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @php($add_ons = json_decode($product->add_ons))
                    @if (count($add_ons) > 0 && $add_ons[0])
                        <div class="__bg-FAFAFA rounded p-sm-3 p-2 mb-20">
                            <div class="h3 p-0 fs-16">{{ translate('messages.addon') }}
                            </div>

                            <div class="d-flex justify-content-left  addon-check__wrap">
                                @php($addons = array_column(json_decode($cart_item['add_ons'], true), 'quantity', 'id'))


                                @foreach (\App\Models\AddOn::withOutGlobalScope(App\Scopes\RestaurantScope::class)->whereIn('id', $add_ons)->active()->get() as $key => $add_on)
                                    @php($checked = array_key_exists($add_on->id, $addons))
                                    <div class="flex-column pb-2">
                                        <input type="hidden" name="addon-price{{ $add_on->id }}"
                                            value="{{ $add_on->price }}">
                                        <input class="btn-check addon-chek addon-quantity-input-toggle"
                                            type="checkbox" id="addon{{ $key }}" name="addon_id[]"
                                            value="{{ $add_on->id }}" {{ $checked ? 'checked' : '' }}
                                            autocomplete="off">
                                        <label
                                            class="d-flex align-items-center btn btn-sm check-label mx-1 border border addon-input text-break"
                                            for="addon{{ $key }}">{{ Str::limit($add_on->name, 20, '...') }}
                                            <br>
                                            {{ Helpers::format_currency($add_on->price) }}</label>
                                        <label
                                            class="input-group addon-quantity-input mx-1 shadow border rounded bg-white "
                                            for="addon{{ $key }}"
                                            @if (array_key_exists($add_on->id, $addons)) style="visibility:visible;" @endif>

                                            <button class="btn btn-sm h-100 text-dark px-0 decrease-button"
                                                data-id="{{ $add_on->id }}" type="button"><i
                                                    class="tio-remove  font-weight-bold"></i></button>
                                            <input id="addon_quantity_input{{ $add_on->id }}" type="number"
                                                name="addon-quantity{{ $add_on->id }}"
                                                class="form-control text-center border-0 h-100 px-0 " placeholder="1"
                                                value="{{ array_key_exists($add_on->id, $addons) ? $addons[$add_on->id] : 1 }}"
                                                min="1" max="9999999999" readonly>
                                            <button class="btn btn-sm h-100 text-dark px-0 increase-button"
                                                data-id="{{ $add_on->id }}" type="button"><i
                                                    class="tio-add  font-weight-bold"></i></button>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="modal-footer border-0 quick-view-shadow">
            <div
                class="custom-modal-fix__footer w-100 flex-wrap gap-2 d-flex justify-content-between align-items-center bg-white">
                <div class="d-flex align-items-center gap-1 d-none text-dark" id="chosen_price_div">
                    <div class="">
                        <div class="product-description-label text-nowrap">{{ translate('messages.Total_Price') }}:
                        </div>
                    </div>
                    <div class="">
                        <div class="product-price">
                            <strong id="chosen_price"></strong>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-2">
                    @if (isset($item_count) && $item_count <= 1)
                        <button class="btn btn-sm btn--secondary" @disabled(true) type="button">
                            <i class="tio-delete"></i>
                            {{ translate('messages.delete') }}
                        </button>
                    @else
                        <button class="btn btn-sm btn--danger remove_from_cart_id" data-toggle="modal"
                            data-target="#remove-from-cart" data-cart_id="{{ $cart_item['cart_id'] }}"
                            type="button">
                            <i class="tio-delete"></i>
                            {{ translate('messages.delete') }}
                        </button>
                    @endif
                    <button class="btn btn-sm btn--primary add-To-Cart" type="button">
                        <i class="tio-edit"></i>
                        {{ translate('messages.update') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    "use strict";
    cartQuantityInitialize();
    getVariantPrice();
    $('#add-to-cart-form input').on('change', function() {
        getVariantPrice();
    });

    function getCheckedInputs() {

        var checkedInputs = [];
        var checkedElements = document.querySelectorAll('.input-element:checked');
        checkedElements.forEach(function(element) {
            checkedInputs.push(element.getAttribute('data-option_id'));
        });
        $('#option_ids').val(checkedInputs.join(','));

    }
    var inputElements = document.querySelectorAll('.input-element');
    inputElements.forEach(function(element) {
        element.addEventListener('change', getCheckedInputs);
    });

    $(document).ready(function() {
        $('.product-description').each(function() {
            var $desc = $(this);
            var fullText = $desc.text().trim();

            if (fullText.length > 200) {
                var shortText = fullText.substring(0, 200) + '...';
                $desc.data('full-text', fullText).text(shortText);
                $desc.next('.see-more').show();
            } else {
                $desc.next('.see-more').remove();
            }
        });

        $(document).on('click', '.see-more', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $link = $(this);
            var $desc = $link.prev('.product-description');
            var fullText = $desc.data('full-text');

            if ($link.text() === 'See More') {
                $desc.text(fullText);
                $link.text('See Less');
            } else {
                $desc.text(fullText.substring(0, 200) + '...');
                $link.text('See More');
            }
        });
    });
</script>
