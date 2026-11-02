<table>
    <thead>
        <tr>
            <th colspan="{{ $data['type'] == 'order' ? '8' : '4' }}" style="text-align: center;"><h1>{{ translate($data['title']) }}</h1></th>
        </tr>
        <tr>
            <th colspan="3">
                @if(isset($data['delivery_man_name']))
                    {{ translate('messages.Delivery_Man') }} - {{ $data['delivery_man_name'] }}
                    <br>
                @endif
                {{ translate('filter_criteria') }} -
                <br>
                {{ translate('filter') }} - {{ translate($data['filter']) }}
                @if ($data['from'])
                <br>
                {{ translate('from') }} - {{ \Carbon\Carbon::parse($data['from'])->format('d M Y') }}
                @endif
                @if ($data['to'])
                <br>
                {{ translate('to') }} - {{ \Carbon\Carbon::parse($data['to'])->format('d M Y') }}
                @endif
            </th>
            <th colspan="{{ $data['type'] == 'order' ? '5' : '1' }}">
                @if(isset($data['search']))
                {{ translate('Search_Bar_Content') }} - {{ $data['search'] }}
                @endif
            </th>
        </tr>
        <tr>
            <th>{{ translate('sl') }}</th>
            @if($data['type'] == 'order')
                <th>{{ translate('messages.Order_ID') }}</th>
                <th>{{ translate('messages.Order_Date') }}</th>
                <th>{{ translate('messages.Delivery_Man') }}</th>
                <th>{{ translate('messages.Delivery_Charge') }}</th>
                <th>{{ translate('messages.Tips') }}</th>
                <th>{{ translate('messages.Commission_Paid') }}</th>
                <th>{{ translate('messages.Net_Profit') }}</th>
            @else
                <th>{{ translate('messages.Transaction_ID') }}</th>
                <th>{{ translate('messages.Transaction_Date') }}</th>
                <th>{{ translate('messages.Incentive') }}</th>
            @endif
        </tr>
    </thead>
    <tbody>
    @foreach($data['transactions'] as $key => $t)
        <tr>
            <td>{{ $key + 1 }}</td>
            @if($data['type'] == 'order')
                <td>{{ $t['order_id'] }}</td>
                <td>
                    @php
                        $date = \Carbon\Carbon::parse($t['order_date']);
                    @endphp
                    {{ $date->format('d M Y') }} {{ $date->format('h:i a') }}
                </td>
                <td>{{ $t['delivery_man'] }}</td>
                <td>{{ \App\CentralLogics\Helpers::format_currency($t['delivery_charge']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::format_currency($t['tips']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::format_currency($t['commission_paid']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::format_currency($t['net_profit']) }}</td>
            @else
                <td>{{ $t['transaction_id'] }}</td>
                <td>
                    @php
                        $date = \Carbon\Carbon::parse($t['transaction_date']);
                    @endphp
                    {{ $date->format('d M Y') }} {{ $date->format('h:i a') }}
                </td>
                <td>{{ \App\CentralLogics\Helpers::format_currency($t['incentive']) }}</td>
            @endif
        </tr>
    @endforeach
    </tbody>
</table>
