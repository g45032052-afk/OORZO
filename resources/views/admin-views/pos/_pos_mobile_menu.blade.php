<div class="pos-mobile-menu-inner">
    <div class="d-flex align-items-center gap-1 justify-content-between mb-3">
        <div class="product-add-quick d-flex align-items-center">
            <?php
                $subtotal = 0;
                $addon_price = 0;
                $discount_on_product = 0;
                $variation_price = 0;
                $show_ddditional_price = false;
            ?>

            @if (session()->has('cart') && count(session()->get('cart')) > 0)

            <?php
                $cart = session()->get('cart', []);
                $show_ddditional_price = true;
                $cart = $cart->except('restaurant_id');
                $cartCount = count($cart);
                ?>

                @foreach ($cart as $key => $cartItem)
                    @if (is_array($cartItem))

                        <?php
                        $variation_price += $cartItem['variation_price'];
                        $product_subtotal = $cartItem['price'] * $cartItem['quantity'];
                        $discount_on_product += $cartItem['discount'];
                        $subtotal += $product_subtotal;
                        $addon_price += $cartItem['addon_price'];

                        $remainingCount = $cartCount - 2;
                        $isLastVisible = $key === 2 && $remainingCount > 0;

                        ?>
                        @if ($key < 3)
                            <div
                                class="thumb d-center border rounded-circle position-relative {{ $isLastVisible ? 'active-blur' : '' }}">
                                <img width="35" height="35" src="{{ $cartItem['image_full_url'] }}" alt="image"
                                    class="rounded-circle">

                                @if ($isLastVisible)
                                    <span class="position-absolute text-white fs-12 font-semibold">
                                        {{ $remainingCount }}
                                    </span>
                                @endif
                            </div>
                        @endif
                        @endif
                    @endforeach



            @endif

            <?php
            $delivery_fee = session()->get('delivery_charge') ?? 0;
            $total = $subtotal + $addon_price;
            $total -= $discount_on_product;

            $tax_amount = $show_ddditional_price ? session()->get('tax_amount') : 0;

            $tax_included = session()->get('tax_included') ?? 0;

            $additional_charge = 0.0;
            if (\App\CentralLogics\Helpers::get_business_data('additional_charge_status')) {
                $additional_charge = $show_ddditional_price ? \App\CentralLogics\Helpers::get_business_data('additional_charge') : 0;
            }

            $extra_packaging_data = \App\CentralLogics\Helpers::get_business_settings('extra_packaging_charge') ?? 0;
            $extra_packaging_amount = $show_ddditional_price && $extra_packaging_data == 1 && $restaurant_data?->restaurant_config?->is_extra_packaging_active == 1 && $restaurant_data?->restaurant_config?->extra_packaging_status == 1 ? $restaurant_data?->restaurant_config?->extra_packaging_amount : 0;
            $total = $total + $delivery_fee;

            ?>
        </div>


        <div class="text-right">
            <h3 class="m-0">
                {{ \App\CentralLogics\Helpers::format_currency(round($total + $additional_charge + $extra_packaging_amount + $tax_amount, 2)) }}
            </h3>
            @if ($tax_included)
                <p class="m-0 fs-10 text-muted">({{ translate('Inc. VAT/TAX') }})</p>
            @endif
        </div>
    </div>
    <div class="row g-1 mt-5">
        <div class="col-6">
            <button type="button"
                class="btn  btn--primary btn-sm btn-block place-order-submit">{{ translate('messages.place_order') }}
            </button>
        </div>
        <div class="col-6">
            <a href="#" class="btn btn--reset btn-sm btn-block empty-Cart">{{ translate('Clear_Cart') }}</a>
        </div>
    </div>
</div>
