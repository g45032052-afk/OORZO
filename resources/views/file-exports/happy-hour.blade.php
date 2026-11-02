<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate('Happy_Hour_List') }}</h1></div>
    <div class="col-lg-12">
        <table>
            <thead>
            <tr>
                <th>{{ translate('Search_Bar_Content') }} : {{ $search ?? translate('N/A') }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ translate('Total_Happy_Hour') }}: {{ $data->count() }}
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
                <th>{{ translate('title') }}</th>
                <th>{{ translate('Zone') }}</th>
                <th>{{ translate('Discount') }}</th>
                <th>{{ translate('Min_Order_Amount') }}</th>
                <th>{{ translate('Offer_Schedule') }}</th>
                <th>{{ translate('Duration') }}</th>
                <th>{{ translate('status') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $happyHour)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $happyHour->title }}</td>
                    <td>{{ $happyHour->zone?->name ?? translate('N/A') }}</td>
                    <td>{{ $happyHour->discount }}%</td>
                    <td>{{ $happyHour->min_order_amount ?? translate('N/A') }}</td>
                    <td>{{ translate($happyHour->duration_type) }}</td>
                    <td>
                        @if($happyHour->duration_type === 'custom')
                            {{ count($happyHour->custom_days ?? []) }} {{ translate('days_selected') }}
                        @else
                            {{ $happyHour->start_time ? \App\CentralLogics\Helpers::time_format($happyHour->start_time) : '' }}
                            - {{ $happyHour->end_time ? \App\CentralLogics\Helpers::time_format($happyHour->end_time) : '' }}
                            @if(!$happyHour->is_permanent && $happyHour->start_date && $happyHour->end_date)
                                ({{ $happyHour->start_date->format('d M Y') }} - {{ $happyHour->end_date->format('d M Y') }})
                            @elseif($happyHour->is_permanent)
                                ({{ translate('Permanent') }})
                            @endif
                        @endif
                    </td>
                    <td>{{ $happyHour->status ? translate('active') : translate('inactive') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
