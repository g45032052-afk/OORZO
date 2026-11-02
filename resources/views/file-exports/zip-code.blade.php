
<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate('messages.Zip_Code_List') }}</h1></div>
    <div class="col-lg-12">
        <table>
            <thead>
                <tr>
                    <th>{{ translate('messages.Search_Criteria') }}</th>
                    <th></th>
                    <th>{{ translate('messages.Search_Bar_Content') }}: {{ $data['search'] ?? translate('N/A') }}</th>
                </tr>
                <tr>
                    <th>{{ translate('messages.sl') }}</th>
                    <th>{{ translate('messages.Zip_Code') }}</th>
                    <th>{{ translate('messages.Zone_Name') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['data'] as $key => $zipCode)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ $zipCode->zip_code }}</td>
                        <td>{{ $zipCode->zone?->name ?? translate('messages.N/A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
