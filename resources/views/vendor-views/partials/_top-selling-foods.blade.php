<!-- Header -->
<div class="card-header">
    <h5 class="card-header-title align-items-center d-flex">
        <img src="{{ dynamicAsset('assets/admin/img/dashboard/top-selling.png') }}" alt="dashboard"
            class="card-header-icon mr-2 mb-1">
        <span>{{ translate('messages.top_selling_foods') }}</span>
    </h5>
</div>
<!-- End Header -->

<!-- Body -->
<div class="card-body">
    @if (count($top_sell) > 0)
        <div class="row g-2">
            @foreach ($top_sell as $key => $item)
                <div class="col-md-4 col-sm-6">
                    <div class="grid-card top-selling-food-card pt-0 redirect-url"
                        data-url="{{ route('vendor.food.view', [$item['id']]) }}">
                        <div class="position-relative">
                            <span class="sold--count-badge">
                                {{ translate('messages.sold') }} : {{ $item['order_count'] }}
                            </span>
                            <img class="initial-43 onerror-image" src="{{ $item['image_full_url'] }}"
                                data-onerror-image="{{ dynamicAsset('assets/admin/img/100x100/food.png') }}"
                                alt="{{ $item->name }} image">
                        </div>
                        <div class="text-center mt-2">
                            <span>{{ Str::limit($item->name ?? translate('messages.Food_deleted!'), 20, '...') }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="d-flex justify-content-center align-items-center h-100 min-h-200">
            <div class="d-flex flex-column gap-3 justify-content-center align-items-center">
                <img src="{{ dynamicAsset('assets/admin/img/dashboard/top_food.svg') }}" alt="">
                <p>{{ translate('No items available in this zone') }}</p>
            </div>
        </div>

    @endif
</div>
<!-- End Body -->
