@extends('layouts.admin.app')

@section('title', translate('Admin Tax Report'))

@section('tax_report')
    active
@endsection

@section('content')
    <div class="content container-fluid">
        <!--- Admin Tax Report -->
        <h2 class="mb-20">{{ translate('messages.Generate Tax Report') }}</h3>
            <div class="card p-20 mb-20">
                <div class="mb-20">
                    <h3 class="mb-1">{{ translate('messages.Admin Tax Report') }}</h3>
                    <p class="mb-0 fz-12">
                        {{ translate('To generate you tax report please select & input following field and submit for the result') }}.
                    </p>
                </div>
                <div class="bg--secondary rounded p-20 mb-20">
                    <form action="" method="get">
                        <div class="row g-lg-4 g-md-3 g-2">
                            <div class="col-md-6">
                                <div class="d-flex flex-column gap-lg-4 gap-3">
                                    <div>
                                        <span
                                            class="mb-2 d-block title-clr fw-normal">{{ translate('messages.Date Range Type') }}</span>
                                        <select name="date_range_type" id="date_range_type" required
                                                class="custom-select custom-select-color border rounded w-100">
                                            <option value="">{{ translate('Select Date Range') }}</option>
                                            <option value="this_fiscal_year"
                                                {{ $date_range_type == 'this_fiscal_year' ? 'selected' : '' }}>
                                                {{ translate('This Fiscal Year') }}
                                            </option>
                                            <option value="custom" {{ $date_range_type == 'custom' ? 'selected' : '' }}>
                                                {{ translate('Custom') }}
                                            </option>

                                        </select>
                                    </div>
                                    <div class="{{ $date_range_type == 'custom' ? '' : 'd-none' }}" id="date_range">
                                        <label class="form-label">{{ translate('Date Range') }}</label>
                                        <div class="position-relative">
                                            <i class="tio-calendar-month icon-absolute-on-right"></i>
                                            <input type="text" class="form-control h-45 position-relative bg-transparent"
                                                   name="dates" placeholder="{{ translate('messages.Select_Date') }}">
                                        </div>
                                    </div>

                                    <div>
                                        <span
                                            class="mb-2 d-block title-clr fw-normal">{{ translate('Select How to calculate tax') }}</span>
                                        <select name="calculate_tax_on" id="calculate_tax_on" required
                                                class="custom-select custom-select-color border rounded w-100">
                                            <option disabled selected value="">{{ translate('Select Calculate Tax') }}</option>
                                            <option {{ $calculate_tax_on == 'all_source' ? 'selected' : '' }}
                                                    value="all_source">
                                                {{ translate('messages.Same Tax for All Income Source') }}
                                            </option>
                                            <option {{ $calculate_tax_on == 'individual_source' ? 'selected' : '' }}
                                                    value="individual_source">
                                                {{ translate('Different Tax for Different Income Source') }}
                                            </option>

                                        </select>
                                    </div>
                                    <div class="{{ $calculate_tax_on == 'individual_source' ? '' : 'd-none' }}"
                                         id="calculate_commission_tax">
                                        <span class="mb-2 d-block title-clr fw-normal">{{ translate('Tax on Order Commission') }}</span>
                                        <div class="select-class-closest">
                                            <select name="tax_on_order_commission[]" id="select_customer_fiscal1"
                                                    class="form-control js-select2-custom" multiple="multiple"
                                                    placeholder="{{translate('--Select Tax Rate--')}}">

                                            </select>
                                        </div>
                                    </div>
                                    <div class="{{ $calculate_tax_on == 'individual_source' ? '' : 'd-none' }}"
                                         id="calculate_delivery_charge_tax">
                                        <span class="mb-2 d-block title-clr fw-normal">{{ translate('Tax on Delivery Charge Commission') }}</span>
                                        <div class="select-class-closest">
                                            <select name="tax_on_delivery_charge_commission[]" id="select_customer_fiscal2"
                                                    class="form-control js-select2-custom" multiple="multiple"
                                                    placeholder="{{translate('--Select Tax Rate--')}}">

                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex flex-column gap-lg-4 gap-3">
                                    <div class="{{ $calculate_tax_on == 'individual_source' ? '' : 'd-none' }}"
                                         id="calculate_service_charge_tax">
                                        <span class="mb-2 d-block title-clr fw-normal">{{ translate('Tax on Service charge') }}</span>
                                        <div class="select-class-closest">
                                            <select name="tax_on_service_charge[]" id="select_customer_fiscal-3"
                                                    class="form-control js-select2-custom" multiple="multiple"
                                                    placeholder="{{translate('--Select Tax Rate--')}}">

                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-column gap-lg-4 gap-3">
                                    <div class="mt-3 {{ $calculate_tax_on == 'individual_source' ? '' : 'd-none' }}"
                                         id="calculate_subscription_tax">
                                        <span class="mb-2 d-block title-clr fw-normal">{{ translate('Tax on Subscription') }}</span>
                                        <div class="select-class-closest">
                                            <select name="tax_on_subscription[]" id="select_customer_fiscal-6"
                                                    class="form-control js-select2-custom" multiple="multiple"
                                                    placeholder="{{translate('--Select Tax Rate--')}}">

                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-lg-4 gap-3">
                                    <div class="{{ $calculate_tax_on == 'individual_source' ? 'd-none' : '' }}"
                                         id="calculate_tax_rate">
                                        <span class="mb-2 d-block title-clr fw-normal">{{ translate('Select Tax Rates') }}</span>
                                        <div class="select-class-closest">
                                            <select {{ $calculate_tax_on == 'individual_source' ? '' : 'required' }}
                                                    name="tax_rate[]" id="select_customer_fiscal-5"
                                                    class="form-control js-select2-custom" multiple="multiple"
                                                    placeholder="{{translate('--Select Tax Rate--')}}">

                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
                <div class="d-flex align-items-center justify-content-end gap-2">
                    <button type="reset" id="reset_button_id"
                            class="btn bg--secondary h--42px title-clr px-4">{{ translate('Reset') }}</button>
                    <button type="submit" class="btn btn--primary">{{ translate('Submit') }}</button>
                </div>
                </form>
            </div>
            <div class="card p-20 mb-20">
                <div class="row g-lg-4 g-3">
                    <div class="col-md-6">
                        <div class="bg-opacity-primary-10 rounded p-20 d-flex align-items-center gap-2 flex-wrap">
                            <div class="d-flex align-items-center gap-3 title-clr">
                                <img src="{{ dynamicAsset('assets/admin/img/t-toal-amount.png') }}" alt="img">
                                {{ translate('Total Income') }}
                            </div>
                            <h3 class="theme-clr fw-bold mb-0">
                                {{ \App\CentralLogics\Helpers::format_currency($totalBase) }} </h3>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-opacity-warning-10 rounded p-20 d-flex align-items-center gap-2 flex-wrap">
                            <div class="d-flex align-items-center gap-3 title-clr">
                                <img src="{{ dynamicAsset('assets/admin/img/t-tax-amount.png') }}" alt="img">
                                {{ translate('Total Tax') }}
                            </div>
                            <h3 class="text-danger fw-bold mb-0">
                                {{ \App\CentralLogics\Helpers::format_currency($totalTax) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <!--- Vendor Tax Report Here -->
            <div class="card p-20 mb-20">
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-20">
                    <h4 class="mb-0">{{ translate('Tax Report List') }}</h4>
                    <div class="search--button-wrapper justify-content-end">


                        <!-- Datatable Info -->
                        <div id="datatableCounterInfo" class="mr-2 mb-2 mb-sm-0 initial-hidden">
                            <div class="d-flex align-items-center">
                                <span class="font-size-sm mr-3">
                                    <span id="datatableCounter">0</span>
                                    {{ translate('messages.selected') }}
                                </span>
                            </div>
                        </div>
                        <div class="hs-unfold mr-2">
                            <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle h--40px"
                               href="javascript:;"
                               data-hs-unfold-options='{
                            "target": "#usersExportDropdown__admin", "type": "css-animation" }'>
                                <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                            </a>
                            <div id="usersExportDropdown__admin"
                                 class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                                <a id="export-excel" class="dropdown-item" href="{{ route('admin.report.adminTaxReportExport',['export_type' => 'excel', request()->getQueryString()]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                         src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg"
                                         alt="Image Description">
                                    {{ translate('messages.excel') }}
                                </a>
                                <a id="export-csv" class="dropdown-item" href="{{ route('admin.report.adminTaxReportExport',['export_type' => 'csv', request()->getQueryString()]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                         src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                         alt="Image Description">
                                    .{{ translate('messages.csv') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Table -->
                <div class="table-responsive datatable-custom">
                    <table id="datatable"
                           class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table fz--14px">
                        <thead class="thead-light">
                        <tr>
                            <th class="border-0">{{ translate('messages.sl') }}</th>
                            <th class="border-0">{{ translate('Income Source') }}</th>
                            <th class="border-0">{{ translate('Total Income') }}</th>
                            <th class="border-0">{{ translate('Total Tax') }}</th>
                            <th class="border-0 text-center">{{ translate('Action') }}</th>
                        </tr>
                        </thead>

                        <tbody>
                            @php
                                $count = 1;
                            @endphp
                            @forelse ($combinedResults as $key => $item)
                                <tr>
                                    <td>
                                        {{ $count++ }}

                                    </td>
                                    <td>
                                        {{ translate($key) }}
                                    </td>
                                    <td>

                                        {{ \App\CentralLogics\Helpers::format_currency($item['total_base_amount']) }}
                                    </td>
                                    <td>
                                        @php
                                            $totalTaxAmount = collect($item['taxes'] ?? [])
                                                ->flatten(1)
                                                ->sum('total_tax_amount');
                                            $totalTax = collect($item['taxes'] ?? [])
                                                ->flatten(1)
                                                ->sum('tax_rate');
                                        @endphp
                                        <div class="d-flex flex-column gap-1">
                                            <div class="d-flex fz-14 gap-3 align-items-center title-clr">
                                                {{ translate('Total') }} ({{ $totalTax }}%): <span>
                                                    {{ \App\CentralLogics\Helpers::format_currency($totalTaxAmount) }}</span>
                                            </div>

                                            @foreach ($item['taxes'] as $taxName => $taxItems)
                                                @foreach ($taxItems as $tax)
                                                    <div class="d-flex fz-11 gap-3 align-items-center">
                                                        {{ $taxName }} ({{ $tax['tax_rate'] }}%) :
                                                        <span>{{ \App\CentralLogics\Helpers::format_currency($tax['total_tax_amount']) }}</span>
                                                    </div>
                                                @endforeach
                                            @endforeach

                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a class="btn btn-sm theme-border action-btn theme-hover theme-clr"
                                                target="_blank"
                                                href="{{ route('admin.report.getTaxDetails', ['source' => $key, 'totalTaxAmount'=>$totalTaxAmount ,request()->getQueryString()]) }}">
                                                <i class="tio-invisible"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-5">
                                        <div class="text-center max-w-700 mx-auto py-5">
                                            <img src="{{ dynamicAsset('assets/admin/img/tax-error.png') }}"
                                                alt="img" class="mb-20">
                                            <h4 class="mb-2">{{ translate('No Tax Report Generated') }}</h4>
                                            <p class="mb-0 fz-12px">
                                                {{ translate('To generate your tax report please select & input above field and submit for the result') }}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
    </div>



@endsection

@push('script_2')
    <script>
        "use strict";

        $(document).on('ready', function() {
            function updateUI() {
                if ($('#date_range_type').val() == 'custom') {
                    $('#date_range').removeClass('d-none');
                } else {
                    $('#date_range').addClass('d-none');
                }

                if ($('#calculate_tax_on').val() == 'individual_source') {
                    $('#calculate_commission_tax').removeClass('d-none');
                    $('#calculate_delivery_charge_tax').removeClass('d-none');
                    $('#calculate_service_charge_tax').removeClass('d-none');
                    $('#calculate_subscription_tax').removeClass('d-none');
                    $('#calculate_tax_rate').addClass('d-none').find('select').attr('required', false);
                } else {
                    $('#calculate_tax_rate').removeClass('d-none').find('select').attr('required', true);
                    $('#calculate_commission_tax').addClass('d-none');
                    $('#calculate_delivery_charge_tax').addClass('d-none');
                    $('#calculate_service_charge_tax').addClass('d-none');
                    $('#calculate_subscription_tax').addClass('d-none');
                }
            }
            updateUI();
            $('#date_range_type').on('change', updateUI);
            $('#calculate_tax_on').on('change', updateUI);
            $('#reset_button_id').on('click', function() {
                $('.js-select2-custom').val(null).trigger('change');
                setTimeout(() => {
                    updateUI();
                }, 1);
            });
        });


        $(function() {
            $('input[name="dates"]').daterangepicker({
                startDate: moment('{{ $startDate }}'),
                endDate: moment('{{ $endDate }}'),
                maxDate: moment(),
                locale: {
                    format: 'MM/DD/YYYY'
                }
            });
        });


        $(document).on('ready', function() {
            const selectedTax = @json($selectedTax);
            Object.entries(selectedTax).forEach(([key, taxArray]) => {
                const $select = $(`select[name="${key}[]"]`);
                if (!$select.length || !Array.isArray(taxArray)) return;
                taxArray.forEach(tax => {
                    if (!tax.id || !tax.name) return;

                    const displayText = `${tax.name} (${tax.tax_rate}%)`;
                    const option = new Option(displayText, tax.id, true, true);
                    $select.append(option);
                });
                $select.trigger('change');
                setTimeout(function () {
                    $select.select2({
                        placeholder: "Select Tax Rate",
                        dropdownParent: $select.closest('.select-class-closest'),
                        ajax: {
                            url: '{{ route('admin.report.getTaxList') }}',
                            data: function(params) {
                                return {
                                    q: params.term,
                                    page: params.page
                                };
                            },
                            processResults: function(data) {
                                return {
                                    results: data
                                };
                            }
                        }
                    });
                }, 5);
            });
        });
    </script>
@endpush
