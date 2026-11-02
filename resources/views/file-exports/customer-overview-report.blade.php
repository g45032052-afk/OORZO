<div class="row">
    <div class="col-lg-12 text-center ">
        <h1>{{ translate('customer_report') }}</h1>
    </div>
    <div class="col-lg-12">



        <table>
            <thead>
                <tr>
                    <th>{{ translate('Customer_Analytics') }}</th>
                    <th></th>
                    <th></th>
                    <th>
                        {{ translate('Total_Customer') }}: {{ $data['customers']->count() }}
                        <br>
                        {{ translate('Active_Customer') }}: {{ $data['customers']->where('status', 1)->count() }}
                        <br>
                        {{ translate('Inactive_Customer') }}: {{ $data['customers']->where('status', 0)->count() }}

                    </th>
                    <th> </th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th>{{ translate('Search_Criteria') }}</th>
                    <th></th>
                    <th></th>
                    <th>
                        {{ translate('Search_Bar_Content') }}: {{ $data['search'] ?? translate('N/A') }}
                    </th>
                    <th> </th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th>{{ translate('Filter_Criteria') }}</th>
                    <th></th>
                    <th></th>
                    <th>
                        {{ translate('Customer_Status') }}:
                        {{ $data['filter'] ? translate($data['filter']) : translate('all') }}
                        <br>
                        {{ translate('Sort_by') }}: {{ $data['order_wise'] ?? translate('N/A') }}
                        <br>
                        {{ translate('Show_Limit') }}: {{ $data['show_limit'] ?? translate('N/A') }}
                        <br>
                        {{ translate('From') }}: {{ $data['from_date'] ?? translate('N/A') }}
                        <br>
                        {{ translate('To') }}: {{ $data['to_date'] ?? translate('N/A') }}
                    </th>
                    <th> </th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th>{{ translate('sl') }}</th>
                    <th>{{ translate('Customer Info') }}</th>
                    <th>{{ translate('Join Date') }}</th>
                    <th>{{ translate('Phone') }}</th>
                    <th>{{ translate('Email') }}</th>
                    <th>{{ translate('Total Orders') }}</th>
                    <th>{{ translate('Total Spent') }}</th>
                    <th>{{ translate('AOV') }}</th>
                    <th>{{ translate('Last Purchase') }}</th>
                    <th>{{ translate('Most Used Payment Method') }} </th>
            </thead>
            <tbody>
                @foreach ($data['customers'] as $key => $customer)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $customer['f_name'] ? $customer['f_name'] . ' ' . $customer['l_name'] : translate('Incomplete_profile') }}</td>
                        <td>{{ \App\CentralLogics\Helpers::date_format($customer->created_at) }}</td>
                        <td>{{ \App\CentralLogics\Helpers::export_phone($customer['phone']) }}</td>
                        <td>{{ $customer['email'] }}</td>
                        <td>{{ $customer['orders_count'] }}</td>
                        <td>{{ \App\CentralLogics\Helpers::format_currency($customer['total_order_amount']) }}</td>
                        <td> {{ \App\CentralLogics\Helpers::format_currency($customer->total_order_amount > 0 ? $customer->total_order_amount / $customer->orders_count : 0) }}
                        </td>
                        <td> 
                            @if($customer->days_since_last_order === null)
                                {{ translate('Never ordered') }}
                            @elseif($customer->days_since_last_order === 0)
                                {{ translate('Today') }}
                            @else
                                {{ $customer->days_since_last_order }} 
                                {{ $customer->days_since_last_order == 1 ? translate('day ago') : translate('days ago') }}
                            @endif
                        </td>

                        <td>
                            @if($customer->most_used_payment_method)
                                {{ str_replace('_', ' ', $customer->most_used_payment_method) }}
                            @else
                                {{ translate('messages.N/A')}}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
