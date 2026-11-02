
<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate('Surge_Price_List') }}</h1></div>
    <div class="col-lg-12">
        <table>
            <thead>
                <tr>
                    <th>{{ translate('Search_Criteria') }}</th>
                    <th></th>
                    <th></th>
                    <th>{{ translate('Search_Bar_Content') }}: {{ $data['search'] ?? translate('N/A') }}</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th>{{ translate('sl') }}</th>
                    <th>{{ translate('Title') }}</th>
                    <th>{{ translate('Zone') }}</th>
                    <th>{{ translate('Price_Increase_Rate') }}</th>
                    <th>{{ translate('Surge_Price_Schedule') }}</th>
                    <th>{{ translate('Duration') }}</th>
                    <th>{{ translate('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['data'] as $key => $surge)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ $surge->surge_price_name }}</td>
                        <td>{{ $surge->zone?->name ?? translate('N/A') }}</td>
                        <td>{{ number_format($surge->price, 2) }}{{ $surge->price_type === 'percent' ? '%' : ' ' . \App\CentralLogics\Helpers::currency_symbol() }}</td>
                        <td>{{ translate(ucfirst($surge->duration_type)) }}</td>
                        <td>{{ \App\CentralLogics\Helpers::surgeDurationSummary($surge) }}</td>
                        <td>{{ $surge->status ? translate('Active') : translate('Inactive') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
