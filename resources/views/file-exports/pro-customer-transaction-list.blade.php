<div class="row">
    <div class="col-lg-12 text-center">
        <h1>{{ translate('messages.Pro_Customer_Transaction_List') }}</h1>
    </div>
    <div class="col-lg-12">
        <table>
            <thead>
                <tr>
                    <th>{{ translate('messages.Transaction_Analytics') }}</th>
                    <th></th>
                    <th></th>
                    <th>
                        {{ translate('messages.Total_Transactions') }}: {{ $data['transactions']->count() }}

                    </th>
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
                </tr>
                <tr>
                    <th>{{ translate('messages.Filter_Criteria') }}</th>
                    <th></th>
                    <th></th>
                    <th>
                        {{ translate('messages.Plan') }}: {{ $data['plan_name'] ?? translate('messages.All') }}
                        <br>
                        {{ translate('messages.Customer') }}: {{ $data['customer_name'] ?? translate('messages.All') }}
                        <br>
                        {{ translate('messages.Payment_Status') }}: {{ $data['plan_type'] ? translate('messages.' . $data['plan_type']) : translate('messages.All') }}
                        <br>
                      
                        {{ translate('messages.Date_Range') }}: {{ $data['dates'] ?? translate('messages.N/A') }}
                    </th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th>{{ translate('messages.SL') }}</th>
                    <th>{{ translate('messages.Transaction_ID') }}</th>
                    <th>{{ translate('messages.Transaction_Date') }}</th>
                    <th>{{ translate('messages.Customer_Name') }}</th>
                    <th>{{ translate('messages.Email') }}</th>
                    <th>{{ translate('messages.Plan_Name') }}</th>
                    <th>{{ translate('messages.Pricing') }}</th>
                    <th>{{ translate('messages.Plan_Validity') }}</th>
                    <th>{{ translate('messages.Payment_By') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['transactions'] as $key => $txn)
                    @php
                        $user = $txn->user;
                        $txnDate = $txn->paid_at ?? $txn->created_at;
                    @endphp
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $txn->id }}</td>
                        <td>{{ $txnDate ? \Carbon\Carbon::parse($txnDate)->format('d M Y H:i') : translate('messages.N/A') }}</td>
                        <td>{{ $user ? trim(($user->f_name ?? '') . ' ' . ($user->l_name ?? '')) : translate('messages.N/A') }}</td>
                        <td>{{ $user?->email ?? translate('messages.N/A') }}</td>
                        <td>{{ $txn->plan_name }}</td>
                        <td>{{ $txn->plan_type === 'free_trial' ? translate('messages.Free_Trial') : \App\CentralLogics\Helpers::format_currency((float) $txn->amount) }}</td>
                        <td>
                            {{ $txn->start_at ? \Carbon\Carbon::parse($txn->start_at)->format('d M Y H:i') : '' }}
                            -
                            {{ $txn->end_at ? \Carbon\Carbon::parse($txn->end_at)->format('d M Y H:i') : '' }}
                        </td>
                        <td>{{ $txn->payment_method ? ucwords(str_replace('_', ' ', $txn->payment_method)) : ($txn->plan_type === 'free_trial' ? translate('messages.Free_Trial') : translate('messages.N/A')) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
