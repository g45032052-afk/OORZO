
<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate('messages.Delivery_Rule_List') }}</h1></div>
    <div class="col-lg-12">
        <table>
            <thead>
                <tr>
                    <th>{{ translate('messages.Search_Criteria') }}</th>
                    <th></th>
                    <th></th>
                    <th>{{ translate('messages.Search_Bar_Content') }}: {{ $data['search'] ?? translate('N/A') }}</th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th>{{ translate('messages.sl') }}</th>
                    <th>{{ translate('messages.Rule_Name') }}</th>
                    <th>{{ translate('messages.Zone') }}</th>
                    <th>{{ translate('messages.Delivery_Method') }}</th>
                    <th>{{ translate('messages.Minimum_Delivery_Charge') }}</th>
                    <th>{{ translate('messages.Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['data'] as $key => $rule)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ $rule->name }}</td>
                        <td>{{ $rule->zone?->name ?? translate('messages.N/A') }}</td>
                        <td>{{ $rule->methodLabel() }}</td>
                        <td>{{ number_format($rule->minimum_delivery_charge, 2) }}</td>
                        <td>{{ $rule->status ? translate('messages.Active') : translate('messages.Inactive') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
