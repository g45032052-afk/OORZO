<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate('messages.ETA_Configuration_List') }}</h1></div>
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
                    <th></th>
                </tr>
                <tr>
                    <th>{{ translate('messages.sl') }}</th>
                    <th>{{ translate('messages.Name') }}</th>
                    <th>{{ translate('messages.Zone') }}</th>
                    <th>{{ translate('messages.ETA_Type') }}</th>
                    <th>{{ translate('messages.Minimum_Delivery_Time') }}</th>
                    <th>{{ translate('messages.Gap_Between_Min_&_Max_Time') }}</th>
                    <th>{{ translate('messages.Preparation_Buffer') }}</th>
                    <th>{{ translate('messages.Transit_Buffer') }}</th>
                    <th>{{ translate('messages.Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['data'] as $key => $configuration)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ $configuration->name ?? translate('messages.N/A') }}</td>
                        <td>{{ $configuration->zone?->name ?? translate('messages.N/A') }}</td>
                        <td>{{ $configuration->methodLabel() }}</td>
                        <td>{{ (int) $configuration->minimum_delivery_time }}</td>
                        {{-- Only the distance based method keeps a gap; the other takes its
                             maximum from the restaurant. --}}
                        <td>{{ $configuration->usesTimeGap() ? (int) $configuration->time_gap : translate('messages.N/A') }}</td>
                        <td>{{ (int) $configuration->preparation_buffer }}</td>
                        <td>{{ (int) $configuration->transit_buffer }}</td>
                        <td>{{ $configuration->status ? translate('messages.active') : translate('messages.inactive') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
