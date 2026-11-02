@php use App\CentralLogics\Helpers; @endphp
<div class="product-card card cursor-pointer quick-View" data-id="{{$product->id}}">
    <div class="card-header inline_product clickable p-0 initial-34 position-relative">
        <div class="thumb position-relative">
            <img class="w-100 rounded onerror-image"
                src="{{ $product?->image_full_url ?? dynamicAsset('assets/admin/img/100x100/food-default-image.png')}}"
                data-onerror-image="{{ dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}"
                alt="Product image">
        </div>
        {{-- <div class="product-quantity position-absolute pos-card__add pos-car-add_vendor w-105px mx-auto">
            <div class="input-group bg-white flex-nowrap rounded-pill border d-flex justify-content-center align-items-center p-1">
                <span class="input-group-btn w-30px h-30px min-w-30px">
                    <button class="btn p-0 btn-number w-30px h-30px min-w-30px" type="button" data-type="minus">
                        <i class="tio-remove  font-weight-bold"></i>
                    </button>
                </span>
                <input type="text" class="w-25px h-30px input-number form-control p-0 border-0 text-center text-dark" placeholder="1" min="1" data-maximum_quantity="150">
                <span class="input-group-btn w-30px h-30px min-w-30px">
                    <button class="btn p-0 btn-number w-30px h-30px min-w-30px" type="button" data-type="plus">
                        <i class="tio-add  font-weight-bold"></i>
                    </button>
                </span>
            </div>
        </div> --}}
    </div>

    <div class="card-body inline_product text-center px-2 py-2 clickable">
        <div class="product-title1 position-relative text-dark font-weight-bold text-capitalize">
            {{ Str::limit($product['name'], 12,'...') }}
        </div>
        <div class="justify-content-between text-center">
            <div class="product-price text-center">
                <div class="justify-content-between text-center">
                    <div class="product-price text-center">
                        <span class="text-accent font-weight-bold color-f8923b">
                            {{Helpers::format_currency($product['price']-Helpers::display_discount_amount($product, $product['price'], $restaurant_data))}}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
