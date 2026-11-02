<div class="border rounded-10 p-3 p-xxl-20">
    <div class="row g-lg-5 gx-3 gy-3 earnings-breakdown">
        <div class="col-lg-4 col-sm-6">
            <div class="item border-right">
                 <div class="flex-shrink-0 info rounded-10 w-40px aspect-1-1 d-flex justify-content-center align-items-center mb-3">
                    <img src="{{dynamicAsset('assets/admin/img/report/earning-breakdown/order-commission.svg')}}" alt="earning">
                </div>
                <div class="mb-2">{{ translate('messages.Commission Paid') }}</div>
                <h2 class="font-medium fs-24 fs-18-mobile mb-2">{{ \App\CentralLogics\Helpers::format_currency($summary['breakdown']['admin_commission'] ?? 0) }}</h2>
                <div class="fs-12 bg-light px-2 py-1 rounded-lg w-max-content">
                    {{ $summary['total_expenses'] > 0 ? round(($summary['breakdown']['admin_commission'] / $summary['total_expenses']) * 100, 1) : 0 }}% {{ translate('messages.of Total') }}
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="item border-right">
                 <div class="flex-shrink-0 purple rounded-10 w-40px aspect-1-1 d-flex justify-content-center align-items-center mb-3">
                    <img src="{{dynamicAsset('assets/admin/img/report/earning-breakdown/subscription.svg')}}" alt="earning">
                </div>
                <div class="mb-2">{{ translate('messages.Subscription Fee') }}</div>
                <h2 class="font-medium fs-24 fs-18-mobile mb-2">{{ \App\CentralLogics\Helpers::format_currency($summary['breakdown']['subscription_fee'] ?? 0) }}</h2>
                <div class="fs-12 bg-light px-2 py-1 rounded-lg w-max-content">
                    {{ $summary['total_expenses'] > 0 ? round(($summary['breakdown']['subscription_fee'] / $summary['total_expenses']) * 100, 1) : 0 }}% {{ translate('messages.of Total') }}
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="item text-sm-left">
                 <div class="flex-shrink-0 danger rounded-10 w-40px aspect-1-1 d-flex justify-content-center align-items-center mb-3">
                    <img src="{{dynamicAsset('assets/admin/img/report/4.svg')}}" alt="earning">
                </div>
                <div class="mb-2">{{ translate('messages.Discount on Product') }}</div>
                <h2 class="font-medium fs-24 fs-18-mobile mb-2">{{ \App\CentralLogics\Helpers::format_currency($summary['breakdown']['product_discount'] ?? 0) }}</h2>
                <div class="fs-12 bg-light px-2 py-1 rounded-lg w-max-content">
                    {{ $summary['total_expenses'] > 0 ? round(($summary['breakdown']['product_discount'] / $summary['total_expenses']) * 100, 1) : 0 }}% {{ translate('messages.of Total') }}
                </div>
            </div>
        </div>
        {{-- A BOGO give-away is the restaurant's cost like a product discount, but a separate
             promotion with its own lever, so it gets its own line rather than being folded
             into the one above. --}}
        <div class="col-lg-4 col-sm-6">
            <div class="item border-right">
                 <div class="flex-shrink-0 danger rounded-10 w-40px aspect-1-1 d-flex justify-content-center align-items-center mb-3">
                    <img src="{{dynamicAsset('assets/admin/img/report/4.svg')}}" alt="earning">
                </div>
                <div class="mb-2">{{ translate('messages.BOGO Give Away') }}</div>
                <h2 class="font-medium fs-24 fs-18-mobile mb-2">{{ \App\CentralLogics\Helpers::format_currency($summary['breakdown']['bogo_discount'] ?? 0) }}</h2>
                <div class="fs-12 bg-light px-2 py-1 rounded-lg w-max-content">
                    {{ $summary['total_expenses'] > 0 ? round((($summary['breakdown']['bogo_discount'] ?? 0) / $summary['total_expenses']) * 100, 1) : 0 }}% {{ translate('messages.of Total') }}
                </div>
            </div>
        </div>
        {{-- A happy hour is the restaurant's own promotion too, and was previously folded into
             the product discount above, where no report could separate the two. --}}
        <div class="col-lg-4 col-sm-6">
            <div class="item border-right">
                 <div class="flex-shrink-0 danger rounded-10 w-40px aspect-1-1 d-flex justify-content-center align-items-center mb-3">
                    <img src="{{dynamicAsset('assets/admin/img/report/4.svg')}}" alt="earning">
                </div>
                <div class="mb-2">{{ translate('messages.Happy_Hour_Offer') }}</div>
                <h2 class="font-medium fs-24 fs-18-mobile mb-2">{{ \App\CentralLogics\Helpers::format_currency($summary['breakdown']['happy_hour_discount'] ?? 0) }}</h2>
                <div class="fs-12 bg-light px-2 py-1 rounded-lg w-max-content">
                    {{ $summary['total_expenses'] > 0 ? round((($summary['breakdown']['happy_hour_discount'] ?? 0) / $summary['total_expenses']) * 100, 1) : 0 }}% {{ translate('messages.of Total') }}
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="item border-right">
                 <div class="flex-shrink-0 warning rounded-10 w-40px aspect-1-1 d-flex justify-content-center align-items-center mb-3">
                    <img src="{{dynamicAsset('assets/admin/img/report/earning-breakdown/coupon-offers.svg')}}" alt="earning">
                </div>
                <div class="mb-2">{{ translate('messages.Coupon Contribution') }}</div>
                <h2 class="font-medium fs-24 fs-18-mobile mb-2">{{ \App\CentralLogics\Helpers::format_currency($summary['breakdown']['coupon_contribution'] ?? 0) }}</h2>
                <div class="fs-12 bg-light px-2 py-1 rounded-lg w-max-content">
                    {{ $summary['total_expenses'] > 0 ? round(($summary['breakdown']['coupon_contribution'] / $summary['total_expenses']) * 100, 1) : 0 }}% {{ translate('messages.of Total') }}
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="item border-right">
                 <div class="flex-shrink-0 info rounded-10 w-40px aspect-1-1 d-flex justify-content-center align-items-center mb-3">
                    <img src="{{dynamicAsset('assets/admin/img/report/earning-breakdown/order-commission.svg')}}" alt="earning">
                </div>
                <div class="mb-2">{{ translate('messages.Free Delivery') }}</div>
                <h2 class="font-medium fs-24 fs-18-mobile mb-2">{{ \App\CentralLogics\Helpers::format_currency($summary['breakdown']['free_delivery'] ?? 0) }}</h2>
                <div class="fs-12 bg-light px-2 py-1 rounded-lg w-max-content">
                    {{ $summary['total_expenses'] > 0 ? round(($summary['breakdown']['free_delivery'] / $summary['total_expenses']) * 100, 1) : 0 }}% {{ translate('messages.of Total') }}
                </div>
            </div>
        </div>
    </div>
</div>
