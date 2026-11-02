<div class="row g-3 mb-20">
    <div class="col-lg-4">
        <div class="bg-success-gradient text-white rounded-10 p-3 p-xxl-20 d-flex gap-2 justify-content-between align-items-start overflow-wrap-anywhere cursor-pointer "
            data-url="">
            <div class="flex-grow-1">
                <div class="opacity-lg">{{ translate('messages.Total_Earnings') }}</div>
                <h2 class="font-medium fs-32 fs-18-mobile text-white mb-0">
                    {{ App\CentralLogics\Helpers::format_currency($summary['admin_earning']) }}</h2>
                @if (in_array(request()->filter, ['this_week', 'this_month', 'this_year', 'custom', 'previous_year']))
                    <div class="opacity-lg"> {{$summary['admin_earning_positive'] ? '↑' : '↓'   }}
                        {{ abs($summary['admin_earning_percentage']) }}%

                        @if (request()->filter == 'this_week')
                            {{ translate('messages.vs last week') }}
                        @elseif (request()->filter == 'this_month')
                            {{ translate('messages.vs last month') }}
                        @elseif (request()->filter == 'this_year')
                            {{ translate('messages.vs last year') }}
                        @elseif (request()->filter == 'previous_year')
                            {{ translate('messages.vs two years ago') }}
                        @else
                            {{ translate('messages.vs last period') }}
                        @endif

                    </div>
                @endif

            </div>
            <div class="flex-shrink-0 bg-white rounded-10 w-48 aspect-1-1 d-flex justify-content-center align-items-center"
                style="--bs-bg-opacity: 0.2;">
                <img width="24" src="{{ dynamicAsset('assets/admin/img/report/new/earning.png') }}" alt="earning">
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="bg-warning-gradient text-white rounded-10 p-3 p-xxl-20 d-flex gap-2 justify-content-between align-items-start overflow-wrap-anywhere cursor-pointer "
            data-url="">
            <div class="flex-grow-1">
                <div class="opacity-lg">{{ translate('messages.Total_Expenses') }}</div>
                <h2 class="font-medium fs-32 fs-18-mobile text-white mb-0">
                    {{ App\CentralLogics\Helpers::format_currency($summary['admin_expense']) }}</h2>
                @if (in_array(request()->filter, ['this_week', 'this_month', 'this_year', 'custom', 'previous_year']))
                    <div class="opacity-lg">{{$summary['admin_expense_positive'] ? '↑' : '↓'   }}
                        {{ abs($summary['admin_expense_percentage']) }}%
                        @if (request()->filter == 'this_week')
                            {{ translate('messages.vs last week') }}
                        @elseif (request()->filter == 'this_month')
                            {{ translate('messages.vs last month') }}
                        @elseif (request()->filter == 'this_year')
                            {{ translate('messages.vs last year') }}
                        @elseif (request()->filter == 'previous_year')
                            {{ translate('messages.vs two years ago') }}
                        @else
                            {{ translate('messages.vs last period') }}
                        @endif
                    </div>
                @endif
            </div>
            <div class="flex-shrink-0 bg-white rounded-10 w-48 aspect-1-1 d-flex justify-content-center align-items-center"
                style="--bs-bg-opacity: 0.2;">
                <img width="24" src="{{ dynamicAsset('assets/admin/img/report/new/earning.png') }}" alt="earning">
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="bg-info-gradient text-white rounded-10 p-3 p-xxl-20 d-flex gap-2 justify-content-between align-items-start overflow-wrap-anywhere cursor-pointer "
            data-url="">
            <div class="flex-grow-1">
                <div class="opacity-lg d-flex align-items-center gap-1">
                    <span>{{ translate('messages.Net_Profit') }}</span>
                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip" data-placement="top" title="{{ translate('Net Profit is the amount of money a business earns after deducting all expenses from its total earnings') }}">
                        <i class="tio-info text-white"></i>
                    </span>
                </div>
                <h2 class="font-medium fs-32 fs-18-mobile text-white mb-0">
                    {{ App\CentralLogics\Helpers::format_currency($summary['net_profit']) }}</h2>
                @if (in_array(request()->filter, ['this_week', 'this_month', 'this_year', 'custom', 'previous_year']))
                    <div class="opacity-lg">{{$summary['net_profit_positive'] ? '↑' : '↓'   }}
                        {{ abs($summary['net_profit_percentage']) }}%
                        @if (request()->filter == 'this_week')
                            {{ translate('messages.vs last week') }}
                        @elseif (request()->filter == 'this_month')
                            {{ translate('messages.vs last month') }}
                        @elseif (request()->filter == 'this_year')
                            {{ translate('messages.vs last year') }}
                        @elseif (request()->filter == 'previous_year')
                            {{ translate('messages.vs two years ago') }}
                        @else
                            {{ translate('messages.vs last period') }}
                        @endif
                    </div>
                @endif
            </div>
            <div class="flex-shrink-0 bg-white rounded-10 w-48 aspect-1-1 d-flex justify-content-center align-items-center"
                style="--bs-bg-opacity: 0.2;">
                <img width="24" src="{{ dynamicAsset('assets/admin/img/report/new/wallet.png') }}" alt="profit">
            </div>
        </div>
    </div>
</div>
