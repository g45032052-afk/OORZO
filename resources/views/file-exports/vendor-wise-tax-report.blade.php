<div class="row">
    <div class="col-lg-12 text-center ">
        <h1>{{ translate('Restaurant_Tax_Reports') }}</h1>
    </div>
    <div class="col-lg-12">



        <table>
            <thead>
                <tr>
                    <th>{{ translate('Search_Criteria') }}</th>
                    <th></th>
                    <th></th>
                    <th>

                        @if (isset($data['summary']))
                            <br>
                            {{ translate('total_orders') }} - {{  \App\CentralLogics\Helpers::format_currency($data['summary']->total_orders ??0) }}
                            <br>
                            {{ translate('total_order_amount') }} - {{  \App\CentralLogics\Helpers::format_currency($data['summary']->total_order_amount ??0) }}
                            <br>
                            {{ translate('total_tax') }} - {{  \App\CentralLogics\Helpers::format_currency($data['summary']->total_tax ??0) }}
                        @endif
                        @if ($data['from'])
                            <br>
                            {{ translate('from') }} -
                            {{ $data['from'] ? Carbon\Carbon::parse($data['from'])->format('d M Y') : '' }}
                        @endif
                        @if ($data['to'])
                            <br>
                            {{ translate('to') }} -
                            {{ $data['to'] ? Carbon\Carbon::parse($data['to'])->format('d M Y') : '' }}
                        @endif
                        <br>

                        {{ translate('Search_Bar_Content') }}- {{ $data['search'] ?? translate('N/A') }}
                        <br>

                    </th>
                    <th> </th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th class="border-0">{{ translate('sl') }}</th>
                    <th class="border-0">{{ translate('Restaurant Info') }}</th>
                    <th class="border-0">{{ translate('Total Order') }}</th>
                    <th class="border-0">{{ translate('Total Order Amount') }}</th>
                    <th class="border-0">{{ translate('Tax Amount') }}</th>
            </thead>
            <tbody>
                @foreach ($data['restaurants'] as $key => $restaurant)
                    <tr>
                        <td>
                            {{ $key +1 }}
                        </td>
                        <td>
                            <span class="fz-14 title-clr">
                                {{ $restaurant->restaurant_name }}
                                <span class="fz-11 d-block">{{ \App\CentralLogics\Helpers::export_phone($restaurant->restaurant_phone) }}</span>
                            </span>
                        </td>
                        <td>
                            {{ $restaurant->total_orders }}
                        </td>
                        <td>
                            {{ \App\CentralLogics\Helpers::format_currency($restaurant->total_order_amount) }}
                        </td>
                        <td>
                            @php($sum_tax_amount=collect($restaurant->tax_data)->sum('total_tax_amount'))

                            <div class="d-flex flex-column gap-1">
                                @if ($restaurant->restaurant_total_tax_amount - $sum_tax_amount > 0 )
                                <div class="d-flex fz-14 gap-3 align-items-center title-clr">
                                    {{ translate('Total Tax Amount:') }} <span>
                                                    {{ \App\CentralLogics\Helpers::format_currency($restaurant->restaurant_total_tax_amount - $sum_tax_amount) }}</span>
                                </div>
                                @endif
                                @if ($sum_tax_amount > 0 )
                                    <div class="d-flex fz-14 gap-3 align-items-center title-clr">
                                        {{ translate('Sum of Taxes:') }} <span>
                                                    {{ \App\CentralLogics\Helpers::format_currency($sum_tax_amount) }}</span>
                                    </div>
                                    @foreach ($restaurant->tax_data as $tax)
                                        <div class="d-flex fz-11 gap-3 align-items-center">
                                            {{ $tax['tax_name'] }}:
                                            <span>{{ \App\CentralLogics\Helpers::format_currency($tax['total_tax_amount']) }}
                                                    </span>
                                        </div>
                                    @endforeach

                                @endif
                            </div>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
