<div class="card h-100">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-1">
            <h4 class="m-0 text-title">{{ translate('messages.Order Statistics ') }}</h4>
            <select name="order-filter" class="custom-select w-auto order-statistics-filter" data-endpoint="{{ route('admin.customer.order-statistics-partial') }}">
                <option value="overall" {{ ($filter ?? 'overall') === 'overall' ? 'selected' : '' }}>
                    {{ translate('Overall') }}
                </option>
                <option value="this_year" {{ ($filter ?? 'overall') === 'this_year' ? 'selected' : '' }}>
                    {{ translate('This Year') }}
                </option>
                <option value="this_month" {{ ($filter ?? 'overall') === 'this_month' ? 'selected' : '' }}>
                    {{ translate('This Month') }}
                </option>
                <option value="this_week" {{ ($filter ?? 'overall') === 'this_week' ? 'selected' : '' }}>
                    {{ translate('This Week') }}
                </option>
                <option value="today" {{ ($filter ?? 'overall') === 'today' ? 'selected' : '' }}>
                    {{ translate('Today') }}
                </option>
            </select>
        </div>
        <div class="customer-overview-chart">
            <div id="order-statistics-chart" data-pos-orders="{{ $stats['pos_orders'] ?? 0 }}" data-non-pos-orders="{{ $stats['non_pos_orders'] ?? 0 }}"></div>
        </div>
    </div>
</div>
