<div>
            <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                <div class="px-3 py-3 d-flex justify-content-between w-100">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h2 class="mb-0 fs-18 text-title font-medium">{{ translate('Top 10 Most Ordered Items') }}</h2>

                    </div>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;
                    </button>
                </div>
            </div>
            <div class="custom-offcanvas-body p-lg-4 p-3 h-100vh">
                <div class="d-flex flex-column gap-20px">

                    @foreach ($foods as $key => $food)
                <?php
                    if (!$food->name){
                        $food_details = json_decode($food->food_details, true);
                        $food->name = $food_details['name'] ?? null;
                        $food->image = $food_details['image'] ?? null;
                        $food->id = null;
                    }
                ?>


                    <div class="d-flex text-dark align-items-sm-center gap-10 global-bg-box rounded py-sm-3 py-2 px-xxl-4 px-sm-3 px-2">
                        @if ($food->is_campaign)
                            <a target="{{ $food->id ? '_blank' : '' }}" href="{{ $food->id ?  route('admin.campaign.view', ['item', $food->id]) : 'javascript:void(0);'}}" class="w-48 min-w-40">
                            <img width="48" height="48" src="{{ \App\CentralLogics\Helpers::get_full_url('product',$food->image,$food->value??'public');}}" alt="img" class="rounded">
                        </a>
                        @else
                        <a target="{{ $food->id ? '_blank' : '' }}" href="{{ $food->id ?  route('admin.food.view', [$food->id]) : 'javascript:void(0);'}}" class="w-48 min-w-40">
                            <img width="48" height="48" src="{{ \App\CentralLogics\Helpers::get_full_url('product',$food->image,$food->value??'public');}}" alt="img" class="rounded">
                        </a>
                        @endif
                        <div class="d-flex justify-content-between align-items-center gap-1 flex-grow-1">

                            <div>
                                @if ($food->is_campaign)
                                <h5 class="mb-0 font-normal text-color d-flex align-items-center gap-2">
                                    <a target="{{ $food->id ? '_blank' : '' }}" class="text-dark line--limit-1 max-w-130px" href="{{ $food->id  ? route('admin.campaign.view', ['item', $food->id]) : 'javascript:void(0);'}}">{{ Str::limit($food->name, 20, '...')  }}</a>
                                    @if(!$food->id)
                                    <span class="text-danger" data-toggle="tooltip" data-placement="right" title="{{ translate('messages.product_deleted') }}"><i class="tio-warning"></i></span>
                                    @endif
                                </h5>
                                @else
                                <h5 class="mb-0 font-normal text-color d-flex align-items-center gap-2">
                                    <a target="{{ $food->id && !$food->is_campaign ? '_blank' : '' }}" class="text-dark line--limit-1 max-w-130px" href="{{ $food->id && !$food->is_campaign ? route('admin.food.view', [$food->id]) : 'javascript:void(0);'}}">{{ Str::limit($food->name, 20, '...')  }}</a>
                                    @if(!$food->id)
                                    <span class="text-danger" data-toggle="tooltip" data-placement="right" title="{{ translate('messages.product_deleted') }}"><i class="tio-warning"></i></span>
                                    @endif
                                </h5>
                                @endif
                               <span class="fs-12 text-gray"> <a href="{{ route('admin.restaurant.view', $food->restaurant_id) }}" target="_blank" rel="noopener noreferrer" class="text-secondary"> {{ Str::limit($food->restaurant_name, 20, '...')  }}</a> </span>
                            </div>
                            <h5 class="m-0 fs-12 font-regular text-gray">{{ translate('Ordered') }} {{ $food->order_count }} {{ translate('items') }}</h5>
                        </div>
                    </div>



                    @endforeach

                </div>

                @if (count($foods) === 0)
                <div class="h-100 d-flex align-items-center justify-content-center">
                        <div class="empty--data">
                            <img src="{{ dynamicAsset('assets/admin/img/no-data.png') }}" alt="public" class="mb-2">
                            <h5>
                                {{ translate('no_data_found') }}
                            </h5>
                        </div>
                    </div>
                    @endif
            </div>

        </div>
