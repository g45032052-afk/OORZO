@if(count($top_selling_foods) > 0)
    <div class="card h-100 mt-3 mt-lg-0">
        <div class="card-header border-0 d-block pb-0">
            <h3 class="mb-1 text-title">{{ translate('messages.Top_Selling_Food') }}</h3>
        </div>
        <div class="card-body">
            <div class="d-flex flex-column gap-3">
                @foreach($top_selling_foods as $food)
                    <div class="d-flex align-items-center justify-content-between gap-2 border-bottom pb-2">
                        <div class="d-flex align-items-center gap-2">
                            <img class="avatar avatar-md rounded-lg" 
                                 src="{{ $food->image_full_url }}" 
                                 onerror="this.src='{{ dynamicAsset('assets/admin/img/160x160/img2.jpg') }}'" 
                                 alt="{{ $food->name }}">
                            <div>
                                <h5 class="mb-0 text-truncate max-width-200" title="{{ $food->name }}">{{ $food->name }}</h5>
                                <span class="fs-12 text-muted">{{ \App\CentralLogics\Helpers::format_currency($food->price) }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <h5 class="mb-0">{{ \App\CentralLogics\Helpers::format_currency($food->total_revenue) }}</h5>
                            <span class="fs-12 text-muted">{{ $food->total_sold }} {{ translate('messages.Sold') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@else
    <div class="card h-100">
        <div class="card-header border-0 d-block pb-0">
            <h3 class="mb-1 text-title">{{ translate('messages.Top_Selling_Food') }}</h3>
        </div>
        <div class="card-body d-flex flex-column justify-content-center align-items-center">
            <img src="{{ dynamicAsset('assets/admin/img/empty.png') }}" class="w--120" alt="">
            <h5 class="text-muted mt-3">{{ translate('messages.no_data_found') }}</h5>
        </div>
    </div>
@endif
