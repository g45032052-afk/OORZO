<!-- Header -->
<div class="card-header">
    <h5 class="card-header-title align-items-center d-flex">
        <img src="{{ dynamicAsset('assets/admin/img/dashboard/most-rated.png') }}" alt="dashboard"
            class="card-header-icon mr-2 mb-1">
        <span>{{ translate('messages.top_rated_foods') }}</span>
    </h5>
</div>
<!-- End Header -->

<!-- Body -->
<div class="card-body">
    @if (count($most_rated_foods) > 0)

        <div class="row g-2">
            @foreach ($most_rated_foods as $key => $item)
                <div class="col-md-4 col-6">
                    <a href="{{ route('vendor.food.view', [$item['id']]) }}" class="grid-card top--rated-food pb-4">
                        <div class="text-center py-3">
                            <img class="initial-42 onerror-image" src="{{ $item['image_full_url'] }}"
                                data-onerror-image="{{ dynamicAsset('assets/admin/img/100x100/2.png') }}"
                                alt="{{ $item->name }} image">
                        </div>
                        <div class="text-center mt-3">
                            <h5 class="name m-0 mb-1">
                                {{ Str::limit($item->name ?? translate('messages.Food_deleted!'), 20, '...') }}
                            </h5>
                            <div class="rating">
                                <span class="text-warning"><i class="tio-star"></i>
                                    {{ round($item['avg_rating'], 1) }}</span>
                                <span class="text--title">({{ $item['rating_count'] }}
                                    {{ translate('Reviews') }})</span>
                            </div>
                        </div>
                    </a>
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
