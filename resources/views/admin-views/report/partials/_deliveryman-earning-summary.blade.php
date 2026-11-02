@php
    $filter = request()->filter;
    $show_comparison = in_array($filter, ['this_week', 'this_month', 'this_year', 'custom', 'previous_year']);
    $comparison_text = translate('messages.vs last period');
    if ($filter == 'this_week') {
        $comparison_text = translate('messages.vs last week');
    } elseif ($filter == 'this_month') {
        $comparison_text = translate('messages.vs last month');
    } elseif ($filter == 'this_year') {
        $comparison_text = translate('messages.vs last year');
    } elseif ($filter == 'previous_year') {
        $comparison_text = translate('messages.vs two years ago');
    }
@endphp

<div class="row g-3 mb-20">
    <div class="col-lg-4">
        <div class="bg-success-gradient h-100 text-white rounded-10 p-3 p-xxl-20 d-flex gap-2 justify-content-between align-items-start overflow-wrap-anywhere cursor-pointer">
            <div class="flex-grow-1 d-flex flex-column h-100">
                <div class="opacity-lg">{{ translate('messages.Total Earnings with Admin Commission') }}</div>
                <div class="mt-auto">
                    <h2 class="font-medium fs-32 fs-18-mobile text-white mb-0">{{ \App\CentralLogics\Helpers::format_currency($summary['total_earnings'] ?? 0) }}</h2>
                    @if ($show_comparison)
                        <div class="opacity-lg">
                            {{ $summary['total_earnings_positive'] ? '↑' : '↓' }}
                            {{ abs($summary['total_earnings_percentage']) }}% {{ $comparison_text }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="flex-shrink-0 bg-white rounded-10 w-48 aspect-1-1 d-flex justify-content-center align-items-center" style="--bs-bg-opacity: 0.2;">
                <img width="24" src="{{dynamicAsset('assets/admin/img/report/new/earning.png')}}" alt="earning">
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="bg-warning-gradient h-100 text-white rounded-10 p-3 p-xxl-20 d-flex gap-2 justify-content-between align-items-start overflow-wrap-anywhere cursor-pointer">
            <div class="flex-grow-1 d-flex flex-column h-100">
                <div class="opacity-lg">{{ translate('messages.Commissions_Paid') }}</div>
                <div class="mt-auto">
                    <h2 class="font-medium fs-32 fs-18-mobile text-white mb-0">{{ \App\CentralLogics\Helpers::format_currency($summary['total_expenses'] ?? 0) }}</h2>
                    @if ($show_comparison)
                        <div class="opacity-lg">
                            {{ $summary['total_expenses_positive'] ? '↑' : '↓' }}
                            {{ abs($summary['total_expenses_percentage']) }}% {{ $comparison_text }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="flex-shrink-0 bg-white rounded-10 w-48 aspect-1-1 d-flex justify-content-center align-items-center" style="--bs-bg-opacity: 0.2;">
                <img width="24" src="{{dynamicAsset('assets/admin/img/report/new/earning.png')}}" alt="earning">
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="bg-info-gradient h-100 text-white rounded-10 p-3 p-xxl-20 d-flex gap-2 justify-content-between align-items-start overflow-wrap-anywhere cursor-pointer">
            <div class="flex-grow-1 d-flex flex-column h-100">
                <div class="opacity-lg d-flex align-items-center gap-1">
                    <span>{{ translate('messages.Net_Profit') }}</span>
                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip" data-placement="top" title="{{ translate('Net profit shows the amount a deliveryman keeps after adding delivery charge and tips, then subtracting the commission paid.') }}">
                        <i class="tio-info text-white"></i>
                    </span>
                </div>
                <div class="mt-auto">
                    <h2 class="font-medium fs-32 fs-18-mobile text-white mb-0">{{ \App\CentralLogics\Helpers::format_currency($summary['net_profit'] ?? 0) }}</h2>
                    @if ($show_comparison)
                        <div class="opacity-lg">
                            {{ $summary['net_profit_positive'] ? '↑' : '↓' }}
                            {{ abs($summary['net_profit_percentage']) }}% {{ $comparison_text }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="flex-shrink-0 bg-white rounded-10 w-48 aspect-1-1 d-flex justify-content-center align-items-center" style="--bs-bg-opacity: 0.2;">
                <img width="24" src="{{dynamicAsset('assets/admin/img/report/new/wallet.png')}}" alt="profit">
            </div>
        </div>
    </div>
</div>
