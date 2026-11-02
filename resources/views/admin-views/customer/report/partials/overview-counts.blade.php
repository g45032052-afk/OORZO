<div class="row g-2">
    <div class="col-sm-6">
        <div class="h-100 customer__card">
            <div class="card-body">
                <div class="d-flex flex-sm-column gap-lg-20 gap--16">
                    <div class="icon" data-bg-color="#0177CD">
                        <img width="24" height="24"
                            src="{{dynamicAsset('assets/admin/img/customer-report/customer-group.png')}}"
                            alt="img" class="object--contain">
                    </div>
                    <div>
                        <h2 class="mb-1 fs-24 text-title">{{ number_format($counts['total_customers'] ?? 0) }}</h2>
                        <h5 class="m-0 font-regular">{{ translate('messages.Total Customers') }} </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="h-100 customer__card">
            <div class="card-body">
                <div class="d-flex flex-sm-column gap-lg-20 gap--16">
                    <div class="icon" data-bg-color="#14B19E">
                        <img width="24" height="24"
                            src="{{dynamicAsset('assets/admin/img/customer-report/new-customer.png')}}" alt="img"
                            class="object--contain">
                    </div>
                    <div>
                        <h2 class="mb-1 fs-24 text-title">{{ number_format($counts['new_customers'] ?? 0) }}</h2>
                        <h5 class="m-0 font-regular">{{ translate('messages.New Customers') }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="h-100 customer__card">
            <div class="card-body d-flex flex-column gap-lg-20 gap--16">
                <div class="d-flex align-items-center gap-xxl-16 gap--10">
                    <div class="icon" data-bg-color="#0177CD">
                        <img width="24" height="24"
                            src="{{dynamicAsset('assets/admin/img/customer-report/active-customer.png')}}"
                            alt="img" class="object--contain">
                    </div>
                    <div>
                        <h2 class="mb-1 fs-24 text-title">{{ number_format($counts['active_customers'] ?? 0) }}</h2>
                        <h5 class="m-0 font-regular">{{ translate('messages.Active Customer') }}</h5>
                    </div>
                </div>
                <div class="border-bottom"></div>
                <div class="d-flex align-items-center gap-xxl-16 gap--10">
                    <div class="icon" data-bg-color="#14B19E">
                        <img width="24" height="24"
                            src="{{dynamicAsset('assets/admin/img/customer-report/inactive-customer.png')}}"
                            alt="img" class="object--contain">
                    </div>
                    <div>
                        <h2 class="mb-1 fs-24 text-title">{{ number_format($counts['inactive_customers'] ?? 0) }}</h2>
                        <h5 class="m-0 font-regular">{{ translate('messages.Inactive Customer') }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="h-100 customer__card">
            <div class="card-body d-flex flex-column gap-lg-20 gap--16">
                <div class="d-flex align-items-center gap-xxl-16 gap--10">
                    <div class="icon" data-bg-color="#0177CD">
                        <img width="24" height="24"
                            src="{{dynamicAsset('assets/admin/img/customer-report/returning-customer.png')}}"
                            alt="img" class="object--contain">
                    </div>
                    <div>
                        <h2 class="mb-1 fs-24 text-title">{{ number_format($counts['returning_customers'] ?? 0) }}</h2>
                        <h5 class="m-0 font-regular">
                            {{ translate('messages.Returning Customers') }}
                            <div class="tooltip--custom d-inline-block">
                                <i class="tio-info text-secondary p-8px" data-toggle="tooltip" data-placement="top"
                                    data-html="true" title="{{ translate('messages.The count reflects customer orders within the last 3 months.') }}">
                                </i>
                            </div>
                        </h5>
                    </div>
                </div>
                <div class="border-bottom"></div>
                <div class="d-flex align-items-center gap-xxl-16 gap--10">
                    <div class="icon" data-bg-color="#14B19E">
                        <img width="24" height="24"
                            src="{{dynamicAsset('assets/admin/img/customer-report/engaged-customer.png')}}"
                            alt="img" class="object--contain">
                    </div>
                    <div>
                        <h2 class="mb-1 fs-24 text-title">{{ number_format($counts['engaged_customers'] ?? 0) }}</h2>
                        <h5 class="m-0 font-regular">
                            {{ translate('messages.Engaged Customers') }}
                            <div class="tooltip--custom d-inline-block">
                                <i class="tio-info text-secondary p-8px" data-toggle="tooltip" data-placement="top"
                                    data-html="true" title="{{ translate('messages.The count is based on customers placing multiple orders.') }}">
                                </i>
                            </div>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
