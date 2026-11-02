<div class="table-responsive datatable-custom pt-0">
    <table class="table table-borderless table-thead-borderless table-nowrap table-align-middle card-table">
        <thead class="table-light">
            <tr>
                <th class="text-center">
                    <span class="form-check form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" value="1" id="check_all">
                    </span>
                </th>
                <th>{{ translate('messages.Customer Name') }}</th>
                <th>{{ translate('messages.Contact') }}</th>
                <th>{{ translate('messages.Total Orders') }}</th>
                <th>{{ translate('messages.Total Amount') }}</th>
                <th class="text-center">{{ translate('messages.Status') }}</th>
                <th class="text-center">{{ translate('messages.Type') }}</th>
                <th>{{ translate('messages.Joined Date') }}</th>
                <th class="text-center">{{ translate('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td class="text-center">
                        <span class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input row-check" type="checkbox" value="{{ $customer['id'] }}">
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar avatar-sm" title="{{ $customer['name'] }}">
                                <img class="avatar-img" src="{{ $customer['image'] ?? dynamicAsset('assets/admin/img/160x160/img2.jpg') }}" alt="{{ $customer['name'] }}" onerror="this.src='{{ dynamicAsset('assets/admin/img/160x160/img2.jpg') }}'">
                            </div>
                            <div>
                                <h6 class="mb-0 fs-14">{{ $customer['name'] }}</h6>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fs-13">
                            <div>{{ $customer['email'] }}</div>
                            <div class="text-muted">{{ $customer['phone'] }}</div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-light-primary">{{ $customer['total_orders'] }}</span>
                    </td>
                    <td>
                        <span class="font-semibold">{{ $customer['total_amount'] }}</span>
                    </td>
                    <td class="text-center">
                        @if($customer['status'] === 'Active')
                            <span class="badge bg-light-success">{{ translate('messages.Active') }}</span>
                        @else
                            <span class="badge bg-light-danger">{{ translate('messages.Inactive') }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light-info">{{ $customer['type'] }}</span>
                    </td>
                    <td>{{ $customer['joined_date'] }}</td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm" role="group">
                            {{-- <a href="{{ route('admin.customer.view', ['user' => $customer['id']]) }}" class="btn btn-primary py-2 px-3" title="{{ translate('messages.View') }}">
                                <i class="tio-visible"></i>
                            </a> --}}
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <p class="text-muted">{{ translate('messages.No customers found') }}</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(count($customers) > 0)
    <div class="page-area px-4 pb-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <p class="text-muted mb-0">{{ translate('messages.Showing') }} <span class="fw-semibold">{{ $pagination['from'] }}</span>
                    {{ translate('to') }} <span class="fw-semibold">{{ $pagination['to'] }}</span>
                    {{ translate('of') }} <span class="fw-semibold">{{ $pagination['total'] }}</span>
                    {{ translate('messages.Customers') }}</p>
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm gap-2 justify-content-center">
                    @if($pagination['current_page'] > 1)
                        <li class="page-item">
                            <a class="page-link customer-pagination" href="#" data-page="1">{{ translate('First') }}</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link customer-pagination" href="#" data-page="{{ $pagination['current_page'] - 1 }}">{{ translate('Prev') }}</a>
                        </li>
                    @endif

                    @for($i = 1; $i <= $pagination['last_page']; $i++)
                        <li class="page-item {{ $i === $pagination['current_page'] ? 'active' : '' }}">
                            <a class="page-link customer-pagination" href="#" data-page="{{ $i }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if($pagination['current_page'] < $pagination['last_page'])
                        <li class="page-item">
                            <a class="page-link customer-pagination" href="#" data-page="{{ $pagination['current_page'] + 1 }}">{{ translate('Next') }}</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link customer-pagination" href="#" data-page="{{ $pagination['last_page'] }}">{{ translate('Last') }}</a>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    </div>
@endif
