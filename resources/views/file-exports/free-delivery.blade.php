<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate('messages.Free_Delivery_List') }}</h1></div>
    <div class="col-lg-12">
        <table>
            <thead>
                <tr>
                    <th>{{ translate('messages.Search_Criteria') }}</th>
                    <th></th>
                    <th>{{ translate('messages.Search_Bar_Content') }}: {{ $data['search'] ?? translate('N/A') }}</th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th>{{ translate('messages.sl') }}</th>
                    <th>{{ translate('messages.Zone') }}</th>
                    <th>{{ translate('messages.Free_Delivery_Type') }}</th>
                    <th>{{ translate('messages.Minimum_Order_Amount') }}</th>
                    <th>{{ translate('messages.Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['data'] as $key => $freeDelivery)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ $freeDelivery->zone?->name ?? translate('messages.N/A') }}</td>
                        <td>{{ $freeDelivery->typeLabel() }}</td>
                        <td>{{ $freeDelivery->coversAnyAmount() ? translate('messages.N/A') : number_format($freeDelivery->minimum_order_amount, 2) }}</td>
                        <td>{{ $freeDelivery->status ? translate('messages.Active') : translate('messages.Inactive') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
