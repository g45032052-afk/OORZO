<div class="card h-100">
    <div class="card-header py-3">
        <h5 class="card-header-title">{{ translate('messages.Top Customers') }}</h5>
        <a href="{{ route('admin.customer.list',['order_wise'=> 'top']) }}" target="_blank" class="fz-12px text--underline">{{ translate('View All') }}</a>
    </div>
    <div class="card-body p-3">
        <div class="row g-2">
            @forelse($customers as $customer)
                <div class="col-sm-6">
                    <div class="top-customer-card text-center h-100 d-flex flex-column justify-content-center">
                        <a href="{{ route('admin.customer.view', $customer['id']) }}" class="d-flex flex-column gap-1 align-items-center">
                            <div class="w-40 h-40 rounded-circle position-relative">
                                <img width="40" height="40" src="{{ $customer['image'] ?? dynamicAsset('assets/admin/img/160x160/img2.jpg') }}" alt="img" class="rounded-circle">
                                @if (!empty($customer['pro_status']))
                                    @include('partials._pro_customer_badge')
                                @endif
                            </div>
                            <div class="mb-1">
                                <div class="mb-1 fs-14 text-title">{{ $customer['name'] }}</div>
                                <h5 class="m-0 font-semibold text--primary rounded py-1 px-2 primary-border-opacity5 fs-12">
                                    {{ translate('messages.Orders') }} : {{ $customer['orders_count'] }}
                                </h5>
                            </div>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-4">
                    <p class="text-muted">{{ translate('messages.No customers found') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
