<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate('BOGO_Offer_List') }}</h1></div>
    <div class="col-lg-12">
        <table>
            <thead>
            <tr>
                <th>{{ translate('Search_Bar_Content') }} : {{ $search ?? translate('N/A') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('Total_BOGO_Offer') }}: {{ $data->count() }}
                    <br>
                    {{ translate('Currently_Running') }}: {{ $data->where('status', 1)->count() }}
                </th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th>{{ translate('sl') }}</th>
                <th>{{ translate('Offer_Name') }}</th>
                <th>{{ translate('Buy_Food_Qty') }}</th>
                <th>{{ translate('Get_Food_Qty') }}</th>
                <th>{{ translate('Start_Date') }}</th>
                <th>{{ translate('End_Date') }}</th>
                <th>{{ translate('Restaurant') }}</th>
                <th>{{ translate('status') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $offer)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $offer->title }}</td>
                    <td>{{ $offer->buy_qty }}</td>
                    <td>{{ $offer->get_qty }}</td>
                    <td>{{ $offer->start_date ? $offer->start_date->format('d M y, h:i a') : translate('N/A') }}</td>
                    <td>{{ $offer->end_date ? $offer->end_date->format('d M y, h:i a') : translate('N/A') }}</td>
                    <td>{{ $offer->enrollments_count ?? $offer->enrollments()->whereIn('status', ['approved', 'pending'])->count() }}</td>
                    <td>{{ $offer->status ? translate('active') : translate('inactive') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
