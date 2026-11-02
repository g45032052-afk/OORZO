<div class="card h-100">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-sm-4 mb-3">
            <h4 class="m-0 text-title">{{ translate('messages.Customer Onboarding Statistics') }}</h4>
            <select name="onboarding-filter" class="custom-select w-auto onboarding-statistics-filter" data-endpoint="{{ route('admin.customer.onboarding-statistics-partial') }}">
                <option value="yearly" {{ ($filter ?? 'yearly') === 'yearly' ? 'selected' : '' }}>
                    {{ translate('Yearly') }}
                </option>
                <option value="this_year" {{ $filter === 'this_year' ? 'selected' : '' }}>
                    {{ translate('This Year') }}
                </option>
                <option value="this_month" {{ $filter === 'this_month' ? 'selected' : '' }}>
                    {{ translate('This Month') }}
                </option>
                <option value="this_week" {{ $filter === 'this_week' ? 'selected' : '' }}>
                    {{ translate('This Week') }}
                </option>
                <option value="today" {{ $filter === 'today' ? 'selected' : '' }}>
                    {{ translate('Today') }}
                </option>
            </select>
        </div>
        @php
            $chartData = $data->map(function($item) {
                return [
                    'date' => $item->label,
                    'count' => $item->count
                ];
            });
        @endphp
        <div id="onboarding-statistics-chart" data-chart-data="{{ json_encode($chartData) }}"></div>
    </div>
</div>
