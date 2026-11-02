@php use App\CentralLogics\Helpers;@endphp
<div class="search-wrap-manage w-100">
    <div class="search-items-wrap p-sm-3 p-2 rounded bg-white d-flex flex-column gap-2">
        @forelse ($foods as $food)
            @php
                $now = Carbon\Carbon::now();
                $start = Carbon\Carbon::createFromTimeString($food->available_time_starts);
                $end = Carbon\Carbon::createFromTimeString($food->available_time_ends);
                $isAvailable = $now->between($start, $end);
                $should_open_details = $food->newVariations()->count() > 0 || count(json_decode($food->add_ons, true)) > 0;
                $stock=  $food->stock_type == 'unlimited' ? 1 : $food->item_stock;
                $out_of_stock = $stock == 0 ? true:false ;
            @endphp


            <div data-id="{{ $food->id }}"  data-item_type="food"  data-quantity="1"
             class="search-item {{ $isAvailable != true || $out_of_stock ? 'unavailable' : '' }}
                {{$isAvailable == true && $out_of_stock == false && $should_open_details ? 'quick-View' : ($isAvailable == true && $out_of_stock == false ? 'add-To-Cart-Single' : '') }}
                d-flex align-items-sm-center gap-2 p-2 border rounded">
                <div class="list-items-media cursor-pointer">
                    <div class="thumb d-center position-relative rounded overflow-hidden w-65px h-65px">
                        <img width="65" height="65"
                            src="{{ $food->image_full_url ?? dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}"
                            alt="image" class="rounded">
                        @if ($isAvailable != true)
                            <div class="text-white fs-10 font-medium position-absolute unavail">
                                {{ translate('Unavailable') }}</div>
                        @elseif ($out_of_stock)
                                <div class="text-white fs-10 font-medium position-absolute unavail">
                                    {{ translate('Out of Stock') }}</div>
                        @endif
                    </div>
                </div>
                <div class="d-flex w-100 flex-sm-nowrap flex-wrap align-items-center justify-content-between search-items-body">
                    <div class="cont d-flex flex-column gap-0">
                        <p class="fs-14 text-dark mb-0 max-w-187px line--limit-1">{{ $food->name }}</p>
                            @if ($out_of_stock == true)
                            <div class="fs-12">{{ translate('Out of Stock') }}
                            </div>
                            @else
                            <div class="fs-12">{{ translate('Stock Qty') }} : <span
                                    class="text-dark">{{ $food->stock_type == 'unlimited' ? translate('Unlimited') : $food->item_stock }}</span>
                            </div>
                            @endif
                    </div>
                    <div class="text-sm-right cont d-flex flex-column gap-0">
                        <div class="text-dark">{{ translate('Price') }}</div>
                        <div class="d-flex align-items-center gap-1">
                            @if ($food->discount > 0 || Helpers::get_effective_restaurant_discount($food->restaurant))
                                <del class="text-gray1 fs-12">{{ Helpers::get_price_range($food) }}</del>
                            @endif
                            <h6 class="m-0 font-semibold text-dark">{{ Helpers::get_price_range($food, true) }}</h6>
                        </div>
                    </div>
                </div>
            </div>

        @empty
            <h6 class="text-center">
                {{ translate('no_data_found') }}
            </h6>
        @endforelse
    </div>
</div>
