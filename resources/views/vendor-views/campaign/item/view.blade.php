@extends('layouts.vendor.app')

@section('title', translate('Food_Campaign_Preview'))

@section('campaign_view')
active
@endsection
@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between">
                <h1 class="page-header-title text-break">{{ $campaign['title'] }}</h1>

            </div>
        </div>
        <!-- End Page Header -->

        <!-- Card -->

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="shadow-md mb-20 rounded p-xl-4 p-3 d-flex flex-md-nowrap flex-wrap align-items-center gap-24px">
                    <div class="campaign-date_thumb w-200px ratio-2-2">
                        <img src="{{ $campaign['image_full_url'] }}" alt="img" class="w-100 rounded">
                    </div>
                    <div class="d-flex flex-column gap-1">
                        <p class="gray-dark fs-16 font-semibold m-0">
                            {{ translate('Campaign starts from') }} : <span
                                class="text-dark">{{ \App\CentralLogics\Helpers::date_format($campaign->start_date) }}</span>
                        </p>
                        <p class="gray-dark fs-16 font-semibold m-0">
                            {{ translate('Campaign ends at') }} : <span
                                class="text-dark">{{ \App\CentralLogics\Helpers::date_format($campaign->end_date) }}</span>
                        </p>
                        <p class="gray-dark fs-16 font-semibold m-0">
                            {{ translate('Available time starts') }} : <span
                                class="text-dark">{{ \App\CentralLogics\Helpers::time_format($campaign->start_time) }}</span>
                        </p>
                        <p class="gray-dark fs-16 font-semibold m-0">
                            {{ translate('Available time ends') }} : <span
                                class="text-dark">{{ \App\CentralLogics\Helpers::time_format($campaign->end_time) }}</span>
                        </p>
                    </div>
                </div>
                <div class="shadow-md rounded p-xl-4 p-3">
                    <div>
                        @php($restaurant_data = \App\CentralLogics\Helpers::get_restaurant_data())

                        <h5 class="text-title d-flex align-items-center gap-2 mb-lg-4 mb-3 pb-3 border-bottom">
                            <img width="15" height="15"
                                src="{{ dynamicAsset('assets/admin/img/shop-up.svg') }}" alt="img">
                            {{ translate('Restaurant Information') }}
                        </h5>
                        <div class="d-flex flex-sm-nowrap flex-wrap align-items-center gap-20px">
                            <div class="campaign-date_thumb w-130px ratio-1 border rounded">
                                <img src="{{ $restaurant_data?->logo_full_url }}" alt="img" class="w-100 rounded">
                            </div>
                            <div class="d-flex flex-column gap-1">
                                <h5 class="text-title mb-2">{{ $restaurant_data?->name }}</h5>
                                <div class="d-flex gap-2">
                                    <img width="15" height="15"
                                        src="{{ dynamicAsset('assets/admin/img/shop-up.svg') }}" alt="img">
                                    <span class="fs-14 text-title max-w-353px">{{ translate('Address') }}
                                        :{{ $restaurant_data?->address }}</span>
                                </div>
                                <div class="d-flex gap-2 text-title">
                                    <i class="tio-call top-02"></i>
                                    <span class="fs-14 text-title max-w-353px">{{ $restaurant_data?->phone }}</span>
                                </div>
                                <div class="d-flex gap-2 text-title">
                                    <i class="tio-email top-02"></i>
                                    <span class="fs-14 text-title max-w-353px">{{ $restaurant_data?->email }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="shadow-md mb-20 rounded p-xl-4 p-3 h-100">
                    <div>
                        <h5 class="text-title d-flex align-items-center gap-2 mb-30 pb-3 border-bottom">
                            <img width="15" height="15"
                                src="{{ dynamicAsset('assets/admin/img/shop-up.svg') }}" alt="img">
                            {{ translate('Food Information') }}
                        </h5>
                        <div class="d-flex flex-sm-nowrap flex-wrap align-items-center gap-20px mb-30">
                            <div class="campaign-date_thumb w-100px ratio-1 border rounded">
                                <img src="{{ $campaign['image_full_url'] }}" alt="img" class="w-100 rounded">
                            </div>


                            <div class="d-flex flex-column gap-1">
                                <h5 class="text-title mb-1">{{ translate('Description') }}:</h5>

                                <span
                                    class="product-description fs-14 m-0 max-w-353px line-limit-4 lh-1 title-clr font-normal">
                                    {!! $campaign['description'] !!}
                                </span>

                                <span class="product-description-full fs-14 m-0 max-w-353px lh-1 title-clr font-normal"
                                    style="display:none;">
                                    {!! $campaign['description'] !!}
                                </span>

                                <a href="#" class="text-base see-more" style="display: none; margin-left: 6px;">
                                    {{ translate('messages.see_more') }}
                                </a>
                            </div>

                        </div>
                        <div class="row g-4">
                            <div class="col-sm-6">
                                <h5 class="fs-14 mb-2">{{ translate('Price Info') }}</h5>
                                <div class="d-flex flex-column gap-2">
                                    <div class="d-flex gap-3 text-title">
                                        <span class="fs-13 min-w-90">{{ translate('Price') }}</span>
                                        <span>:</span>
                                        <div>
                                            <span
                                                class="text-title">{{ \App\CentralLogics\Helpers::format_currency($campaign['price']) }}</span>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-3 text-title">
                                        <span class="fs-13 min-w-90">{{ translate('Discount') }}</span>
                                        <span>:</span>
                                        <div>
                                            <span
                                                class="text-title">{{ $campaign['discount_type'] == 'percent' ? $campaign['discount'] . ' %' : \App\CentralLogics\Helpers::format_currency($campaign['discount']) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if (count(json_decode($campaign['add_ons'], true) ?? []) > 0)
                                <div class="col-sm-6">
                                    <h5 class="fs-14 mb-2">{{ translate('Addons') }}</h5>
                                    <div class="d-flex flex-column gap-2">
                                        @foreach (\App\Models\AddOn::whereIn('id', json_decode($campaign['add_ons'], true))->get() as $addon)
                                            <div class="d-flex gap-3 text-title">

                                                <span class="fs-13 min-w-90 text-capitalize">
                                                    {{ $addon['name'] }} :
                                                    <strong>{{ \App\CentralLogics\Helpers::format_currency($addon['price']) }}</strong>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                            @endif

                            @if (count(json_decode($campaign['variations'], true) ?? []) > 0)
                                <div class="col-sm-6">
                                    <h5 class="fs-14 mb-2">{{ translate('Variation Info') }}</h5>
                                    <div class="d-flex flex-column gap-2">

                                        @foreach (json_decode($campaign['variations'], true) ?? [] as $item)
                                            <div class="d-flex gap-3 text-title">
                                                {{-- @dump($item) --}}
                                                <span class="fs-13 text-bold min-w-90"> <strong> {{ $item['name'] }}
                                                    </strong>

                                                    <span> - {{ translate($item['type']) . ' ' . translate('Select') }}
                                                    </span>
                                                </span>


                                            </div>
                                            @foreach ($item['values'] as $value)
                                                <div class="d-flex gap-3 text-title">

                                                    <span class="fs-13 min-w-90">{{ $value['label'] }} </span>
                                                    <span>:</span>
                                                    <div>
                                                        <span
                                                            class="text-title">{{ \App\CentralLogics\Helpers::format_currency($value['optionPrice']) }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endforeach




                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- End Card -->
    </div>
@endsection

@push('script_2')
    <script>
        $(document).ready(function() {
            $('.product-description').each(function() {
                const $desc = $(this);
                const $fullDesc = $desc.next('.product-description-full');
                const $seeMore = $fullDesc.next('.see-more');

                const plainText = $desc.text().trim();

                if (plainText.length > 200) {
                    const shortText = plainText.substring(0, 200) + '...';
                    $desc.text(shortText);
                    $seeMore.show();
                } else {
                    $seeMore.remove();
                }
            });

            $(document).on('click', '.see-more', function(e) {
                e.preventDefault();

                const $link = $(this);
                const $fullDesc = $link.prev('.product-description-full');
                const $shortDesc = $fullDesc.prev('.product-description');

                const seeMoreText = "{{ translate('messages.see_more') }}";
                const seeLessText = "{{ translate('messages.see_less') ?? 'See Less' }}";

                const isExpanded = $fullDesc.is(':visible');

                if (isExpanded) {
                    $fullDesc.hide();
                    $shortDesc.show();
                    $link.text(seeMoreText);
                } else {
                    $fullDesc.show();
                    $shortDesc.hide();
                    $link.text(seeLessText);
                }
            });
        });
    </script>
@endpush
