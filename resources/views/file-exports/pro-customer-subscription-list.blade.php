<div class="row">
    <div class="col-lg-12 text-center">
        <h1>{{ translate('messages.Pro_Customer_Subscription_List') }}</h1>
    </div>
    <div class="col-lg-12">
        <table>
            <thead>
                <tr>
                    <th>{{ translate('messages.Subscription_Analytics') }}</th>
                    <th></th>
                    <th></th>
                    <th>
                        {{ translate('messages.Total_Subscriber') }}: {{ $data['stats']['total'] ?? 0 }}
                        <br>
                        {{ translate('messages.Active_Subscriber') }}: {{ $data['stats']['active'] ?? 0 }}
                        <br>
                        {{ translate('messages.Inactive_Subscriber') }}: {{ $data['stats']['inactive'] ?? 0 }}
                        <br>
                        {{ translate('messages.Total_Earned') }}: {{ \App\CentralLogics\Helpers::format_currency((float) ($data['stats']['total_earned'] ?? 0)) }}
                    </th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th>{{ translate('messages.Search_Criteria') }}</th>
                    <th></th>
                    <th></th>
                    <th>
                        {{ translate('messages.Search_Bar_Content') }}: {{ $data['search'] ?? translate('messages.N/A') }}
                    </th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th>{{ translate('messages.Filter_Criteria') }}</th>
                    <th></th>
                    <th></th>
                    <th>
                        {{ translate('messages.Tab') }}: {{ translate('messages.' . ($data['tab'] ?? 'all')) }}
                        <br>
                        {{ translate('messages.Plan') }}: {{ $data['plan_name'] ?? translate('messages.All') }}
                        <br>
                        {{ translate('messages.Subscription_Status') }}: {{ $data['subscription_status'] ? translate('messages.' . $data['subscription_status']) : translate('messages.All') }}
                        <br>
                        {{ translate('messages.Renewal_Status') }}: {{ $data['renewal_status'] ? translate('messages.' . $data['renewal_status']) : translate('messages.All') }}
                        <br>
                        {{ translate('messages.Date_Range') }}: {{ $data['dates'] ?? translate('messages.N/A') }}
                    </th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th>{{ translate('messages.SL') }}</th>
                    <th>{{ translate('messages.Customer_Name') }}</th>
                    <th>{{ translate('messages.Email') }}</th>
                    <th>{{ translate('messages.Phone') }}</th>
                    <th>{{ translate('messages.Plan_Name') }}</th>
                    <th>{{ translate('messages.Plan_Type') }}</th>
                    <th>{{ translate('messages.Plan_Price') }}</th>
                    <th>{{ translate('messages.Start_Date') }}</th>
                    <th>{{ translate('messages.End_Date') }}</th>
                    <th>{{ translate('messages.Subscription_Status') }}</th>
                    <th>{{ translate('messages.Pro_Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['subscriptions'] as $key => $sub)
                    @php $user = $sub->user; @endphp
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $user ? trim(($user->f_name ?? '') . ' ' . ($user->l_name ?? '')) : translate('messages.N/A') }}</td>
                        <td>{{ $user?->email ?? translate('messages.N/A') }}</td>
                        <td>{{ $user?->phone ? \App\CentralLogics\Helpers::export_phone($user->phone) : translate('messages.N/A') }}</td>
                        <td>{{ $sub->plan_name }}</td>
                        <td>{{ $sub->plan_type === 'free_trial' ? translate('messages.Free_Trial') : translate('messages.Paid') }}</td>
                        <td>{{ \App\CentralLogics\Helpers::format_currency((float) $sub->plan_price) }}</td>
                        <td>{{ $sub->start_at ? \Carbon\Carbon::parse($sub->start_at)->format('d M Y H:i') : translate('messages.N/A') }}</td>
                        <td>{{ $sub->end_at ? \Carbon\Carbon::parse($sub->end_at)->format('d M Y H:i') : translate('messages.N/A') }}</td>
                        <td>{{ translate('messages.' . $sub->status) }}</td>
                        <td>{{ $user && $user->pro_status ? translate('messages.Pro') : translate('messages.Regular') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
