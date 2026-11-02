
        <div>
            <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                <div class="px-3 py-3 d-flex justify-content-between w-100">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h2 class="mb-0 fs-18 text-title font-medium">{{ translate('Ratings & Reviews Quick View') }}</h2>
                    </div>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;
                    </button>
                </div>
            </div>

            <div class="custom-offcanvas-body p-20">
                <div class="d-flex flex-column gap-20px">
                    <div class="d-flex align-items-sm-center gap-10 global-bg-box rounded p-xxl-20 p-16">
                        <a href="{{$review->food ?  route('admin.food.view', $review->food_id) : '#' }}" class="w-40 min-w-40">
                            <img width="40" height="40"
                                src="{{ $review?->food?->image_full_url ?? dynamicAsset('assets/admin/img/160x160/img2.jpg') }}" alt="img"
                                class="rounded">
                        </a>
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 flex-grow-1">
                            <div>
                                <h5 class="mb-0 max-w-187px line--limit-1 text-color font-medium">
                                    <a href="{{$review->food ?  route('admin.food.view', $review->food_id) : '#' }}" class="text-dark">
                                        {{ Str::limit($review->food?->name, 40, '...') ?? translate('food_not_found') }}
                                    </a>
                                </h5>
                               <a href="{{ route('admin.order.details',$review->order_id) }}">
                                    <span class="fs-12 text-secondary">{{ translate('Order ID') }}:
                                        {{ $review->order_id }}
                                    </span>
                                </a>
                            </div>
                            <div
                                class="m-0 bg-white rounded py-sm-2 py-1 px-xxl-5 px-sm-3 px-2 fs-18 text-title font-medium">
                                {{ $review->rating }}<i class="tio-star brand-base-clr"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-10px">
                            <h4 class="m-0 text-title">{{ translate('Review') }}</h4>
                            <span class="gray-dark"> {{ \App\CentralLogics\Helpers::time_date_format($review->created_at) }}</span>
                        </div>
                        <div class="global-bg-box rounded p-sm-3 p-2">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="min-w-35px rounded-circle position-relative">
                                    <img width="35" height="35"
                                        src="{{$review->customer->image_full_url}}"
                                        alt="img" class="rounded-circle">
                                    @if ($review->customer && $review->customer->pro_status)
                                        @include('partials._pro_customer_badge')
                                    @endif
                                </div>
                                <div>
                                    <h5 class="mb-0 font-normal text-color font-medium">{{$review->customer->full_name}}</h5>
                                </div>
                            </div>




                            <div class="pragraph-description mb-2" data-limit="350">
                                <p class="m-0 gray-dark product-description fs-14">
                                    {{ $review->comment }}
                                </p>
                                <a href="#0" style="display:none; "
                                    data-more="{{ translate('See More') }}"
                                    data-less="{{ translate('See Less') }}"
                                    class="theme-clr d-inline-block cursor-pointer text-underline see-more">{{ translate('messages.see_more') }}</a>
                            </div>
                            {{-- <div class="d-flex align-items-center gap-3">
                                <div class="rounded">
                                    <img src="{{ dynamicAsset('assets/admin/img/xl-view.png') }}" alt="img"
                                        class="rounded">
                                </div>
                                <div class="rounded">
                                    <img src="{{ dynamicAsset('assets/admin/img/pdf-view.png') }}" alt="img"
                                        class="rounded">
                                </div>
                            </div> --}}
                        </div>
                    </div>
                    @if ($review->restaurant)
                    <div>
                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-10px">
                            <h4 class="m-0 text-title">{{ translate('Reply') }}</h4>
                            <span class="gray-dark">{{ $review->reply_at ? \App\CentralLogics\Helpers::time_date_format($review->reply_at) : \App\CentralLogics\Helpers::time_date_format($review->updated_at)  }}</span>
                        </div>
                        <div class="global-bg-box rounded p-sm-3 p-2">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="min-w-35px rounded-circle">
                                    <img width="35" height="35"
                                        src="{{ $review?->restaurant?->logo_full_url ?? dynamicAsset('assets/admin/img/160x160/img2.jpg') }}"
                                        alt="img" class="rounded-circle">
                                </div>
                                <div>
                                    <h5 class="mb-0 font-normal text-color font-medium">
                                        {{ $review?->restaurant?->name }}
                                        @include('partials._verified_restaurant_badge', ['restaurant' => $review?->restaurant])
                                    </h5>
                                </div>
                            </div>
                               <div class="pragraph-description mb-2" data-limit="350">
                                <p class="m-0 gray-dark product-description fs-14">
                                    {{ $review->reply }}
                                </p>
                                <a href="#0" style="display:none; "
                                    data-more="{{ translate('See More') }}"
                                    data-less="{{ translate('See Less') }}"
                                    class="theme-clr d-inline-block cursor-pointer text-underline see-more">{{ translate('messages.see_more') }}</a>
                            </div>
                        </div>
                    </div>

                    @endif
                </div>
            </div>
        </div>
