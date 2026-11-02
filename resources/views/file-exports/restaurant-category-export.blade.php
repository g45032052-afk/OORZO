<div class="row">
    <div class="col-lg-12 text-center ">
        <h1>{{ translate('Restaurant Category List') }}</h1>
    </div>
    <div class="col-lg-12">
        <table>
            <thead>
                <tr>
                    <th>
                        {{ translate('Search_Bar_Content') }}: {{ $data['search'] ?? translate('N/A') }}
                        @if (!empty($data['show_restaurant']))
                            &nbsp;&nbsp;|&nbsp;&nbsp;{{ translate('messages.Restaurant') }}: {{ $data['restaurant'] ?? translate('messages.all') }}
                        @endif
                    </th>
                </tr>
                <tr>
                    <th>{{ translate('sl') }}</th>
                    <th>{{ translate('messages.Category_Name') }}</th>
                    @if (!empty($data['show_restaurant']))
                        <th>{{ translate('messages.Restaurant') }}</th>
                    @endif
                    <th>{{ translate('Priority') }}</th>
                    <th>{{ translate('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['data'] as $key => $category)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $category->name }}</td>
                        @if (!empty($data['show_restaurant']))
                            <td>{{ $category?->restaurant?->name ?? translate('messages.N/A') }}</td>
                        @endif
                        @php
                            $priorityLabel = match ($category->priority) {
                                0 => translate('messages.normal'),
                                1 => translate('messages.medium'),
                                2 => translate('messages.high'),
                            };
                        @endphp
                        <td>{{ $priorityLabel }}</td>
                        <td>{{ $category->status == 1 ? translate('messages.Active') : translate('messages.Inactive') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
