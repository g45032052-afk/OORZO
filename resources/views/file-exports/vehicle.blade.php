
<div class="row">
    <div class="col-lg-12 text-center"><h1>{{ translate('Vehicle_Types') }}</h1></div>
    <div class="col-lg-12">
        <table>
            <thead>
                <tr>
                    <th>{{ translate('Filter_Criteria') }}</th>
                    <th></th>
                    <th>{{ translate('Search_Bar_Content') }}: {{ $data['search'] ?? translate('N/A') }}</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th>{{ translate('sl') }}</th>
                    <th>{{ translate('Vehicle_Id') }}</th>
                    <th>{{ translate('Vehicle_type') }}</th>
                    <th>{{ translate('Coverage_Area') }} ({{ \App\CentralLogics\Helpers::distanceUnitLabel() }})</th>
                    <th>{{ translate('Extra_charges') }} ({{ \App\CentralLogics\Helpers::currency_symbol() }})</th>
                    <th>{{ translate('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['data'] as $key => $vehicle)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ $vehicle->id }}</td>
                        <td>{{ $vehicle->type }}</td>
                        <td>{{ $vehicle->starting_coverage_area }} - {{ $vehicle->maximum_coverage_area }}</td>
                        <td>{{ $vehicle->extra_charges }}</td>
                        <td>{{ $vehicle->status == 1 ? translate('Active') : translate('Inactive') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
