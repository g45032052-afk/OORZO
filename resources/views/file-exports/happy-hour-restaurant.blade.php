<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate('Restaurant_list') }} - {{ $happyHour->title }}</h1></div>
    <div class="col-lg-12">
        <table>
            <thead>
            <tr>
                <th>{{ translate('Search_Bar_Content') }} : {{ $search ?? translate('N/A') }}</th>
                <th></th>
                <th>
                    {{ translate('Total_Restaurant_Joined') }}: {{ $data->count() }}
                    <br>
                    {{ translate('Approved') }}: {{ $data->where('status', 'approved')->count() }}
                </th>
                <th></th>
            </tr>
            <tr>
                <th>{{ translate('sl') }}</th>
                <th>{{ translate('Restaurant') }}</th>
                <th>{{ translate('Joining_Offer_Date') }}</th>
                <th>{{ translate('status') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $enrollment)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $enrollment->restaurant?->name ?? translate('messages.restaurant_deleted') }}</td>
                    <td>{{ $enrollment->joined_at ? $enrollment->joined_at->format('d M Y') : translate('N/A') }}</td>
                    <td>{{ translate($enrollment->status) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
