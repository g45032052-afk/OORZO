
<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate('messages.Area_list') }}</h1></div>
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
                    <th>{{ translate('messages.Area_Name') }}</th>
                    <th>{{ translate('messages.Zone_Name') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['data'] as $key => $area)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ $area->name }}</td>
                        <td>{{ $area->zone?->name ?? translate('messages.N/A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
