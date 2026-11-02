<table>
    <thead>
        <tr>
            <th colspan="6" style="text-align: center;"><h1>{{ translate($data['title']) }}</h1></th>
        </tr>
        <tr>
            <th colspan="3">
                @if(isset($data['restaurant_name']))
                    {{ translate('messages.Restaurant') }} - {{ $data['restaurant_name'] }}
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
            <th colspan="3">
                @if(isset($data['search']))
                {{ translate('Search_Bar_Content') }} - {{ $data['search'] }}
                @endif
            </th>
        </tr>
        <tr>
            <th>{{ translate('sl') }}</th>
            <th>{{ translate('messages.Transaction_ID') }}</th>
            <th>{{ translate('messages.Date') }}</th>
            <th>{{ translate('messages.Source') }}</th>
            <th>
                @if(($data['type'] ?? 'order') === 'expense')
                    {{ translate('messages.Expense_Source') }}
                @else
                    {{ translate('messages.Earning_Source') }}
                @endif
            </th>
            <th>{{ translate('messages.Amount') }}</th>
        </tr>
    </thead>
    <tbody>
    @foreach($data['transactions'] as $key => $t)
        <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $t['transaction_id'] }}</td>
            <td>
                @php
                    $date = \Carbon\Carbon::parse($t['date']);
                @endphp
                {{ $date->format('d M Y') }} {{ $date->format('h:i a') }}
            </td>
            <td>
                {{ $t['source'] ?? $t['restaurant'] ?? '' }}
                @if(isset($t['source_type']))
                    ({{ translate($t['source_type']) }})
                @endif
            </td>
            <td>
                @php
                    $badge = $t['earning_from_badge'] ?? $t['expense_source_badge'] ?? $t['transaction_type'] ?? '';
                    $from = $t['earning_from'] ?? $t['expense_source'] ?? '';
                @endphp
                {{ $badge ? translate($badge) : '' }}
                {{ $from ? '('.$from.')' : '' }}
            </td>
            <td>{{ \App\CentralLogics\Helpers::format_currency($t['amount']) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
