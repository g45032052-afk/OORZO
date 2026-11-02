<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate('messages.Additional_Charge_List') }}</h1></div>
    <div class="col-lg-12">
        <table>
            <thead>
                <tr>
                    <th>{{ translate('messages.Search_Criteria') }}</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>{{ translate('messages.Search_Bar_Content') }}: {{ $data['search'] ?? translate('N/A') }}</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th>{{ translate('messages.sl') }}</th>
                    <th>{{ translate('messages.Additional_Charge_ID') }}</th>
                    <th>{{ translate('messages.Zone') }}</th>
                    <th>{{ translate('messages.Extra_Charge') }}</th>
                    <th>{{ translate('messages.Reduce_Delivery_Time') }}</th>
                    <th>{{ translate('messages.Reduce_Charge') }}</th>
                    <th>{{ translate('messages.Add_Extra_Delivery_Time') }}</th>
                    <th>{{ translate('messages.Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['data'] as $key => $zone)
                    @php($express = $zone->deliveryOptions->firstWhere('delivery_type', 'express'))
                    @php($delayed = $zone->deliveryOptions->firstWhere('delivery_type', 'slightly_delay'))
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>#{{ $zone->id }}</td>
                        <td>{{ $zone->name }}</td>
                        <td>{{ number_format($express->extra_charge ?? 0, 2) }}</td>
                        <td>{{ \App\CentralLogics\Helpers::deliveryOptionTimeLabel($express?->reduce_delivery_time) }}</td>
                        <td>{{ number_format($delayed->reduce_charge ?? 0, 2) }}</td>
                        <td>{{ \App\CentralLogics\Helpers::deliveryOptionTimeLabel($delayed?->add_delivery_time) }}</td>
                        <td>{{ $zone->additional_delivery_option_status ? translate('messages.Active') : translate('messages.Inactive') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
