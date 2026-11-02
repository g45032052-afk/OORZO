<div class="row">
    <div class="col-lg-12 text-center ">
        <h1> {{ translate('Category_List') }}
        </h1>
    </div>
    <div class="col-lg-12">

        <table>
            <thead>
                <tr>
                    <th>{{ translate('Filter_Criteria') }}</th>
                    <th></th>
                    <th>
                        {{ translate('Search_Bar_Content') }}: {{ $data['search'] ?? translate('N/A') }}

                    </th>
                    <th> </th>
                </tr>


                <tr>
                    <th>{{ translate('sl') }}</th>
                    <th>{{ translate('Category_Name') }}</th>
                    <th>{{ translate('Category_ID') }}</th>

                    <th>{{ translate('Priority') }}</th>
                    <th>{{ translate('Status') }}</th>
                    @if (isset($data['categoryWiseTax']) && $data['categoryWiseTax'])
                        <th class="border-0 w--1">{{ translate('messages.Vat/Tax') }}</th>
                    @endif

            </thead>
            <tbody>
                @foreach ($data['data'] as $key => $category)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->id }}</td>
                        @php
                            $return_value = match ($category->priority) {
                                0 => translate('messages.normal'),
                                1 => translate('messages.medium'),
                                2 => translate('messages.high'),
                            };
                        @endphp
                        <td>{{ $return_value }}</td>
                        <td>{{ $category->status == 1 ? translate('messages.Active') : translate('messages.Inactive') }}
                        </td>

                        @if (isset($data['categoryWiseTax']) && $data['categoryWiseTax'])
                            <td>
                                <span class="d-block font-size-sm text-body">

                                    @forelse ($category?->taxVats?->pluck('tax.name', 'tax.tax_rate')->toArray() as $key => $item)
                                        <span> {{ $item }} : <span class="font-bold">
                                                ({{ $key }}%)
                                            </span> </span> <br>
                                        <br>
                                    @empty
                                        <span> {{ translate('messages.N/A') }} </span>
                                    @endforelse
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
