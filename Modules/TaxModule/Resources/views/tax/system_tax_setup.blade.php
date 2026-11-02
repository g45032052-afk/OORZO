@extends('layouts.admin.app')

@section('title', translate('messages.Setup Tax Calculation'))

 @section('taxModule')
    active
@endsection
    @section('taxModuleDisplay')
    block
@endsection
@section('tax_system_setup')
    show active
@endsection




@section('content')
    @php($tax_payer = $tax_payer ?? 'vendor')


    <div class="content container-fluid">
        <h2 class="mb-20">{{ translate('messages.Setup Tax Calculation') }}</h3>

            <div class="card p-20 mb-20">
                <div class="row g-md-3 g-2 justify-content-between">
                    <div class="col-md-8">
                        <h3 class="mb-1 text-capitalize">
                            {{ translate('messages.Allow Tax Calculation For Restaurant') }} ?</h3>
                        <p class="fz-12 mb-0">{{ translate('messages.To active tax calculation Turn On The Status.') }}</p>
                    </div>
                    <div class="col-md-4 col-xxl-3">
                        <label class="border d-flex align-items-center justify-content-between rounded p-10px px-3">
                            {{ translate('messages.Status') }}
                            <div class="toggle-switch ml-auto justify-content-end toggle-switch-sm confirmStatus"
                                data-id="{{ $systemTaxVat?->id }}"
                                data-on_title="{{ translate('messages.Turn On The Status?') }}"
                                data-off_title="{{ translate('messages.Turn Off The Status?') }}"
                                data-on_message= "{{ translate('do you want to turn on the VAT status from your system ? It will  effect on tax calculation & report.') }}"
                                data-off_message= "{{ translate('do you want to turn off the VAT status from your system ? It will  effect on tax calculation & report.') }}"
                                data-url="{{ route('taxvat.systemTaxVatVendorStatus', ['id' => $systemTaxVat?->id, 'country_code' =>$country_code ?? ($systemTaxVat?->country_code ?? null) , 'type' => $tax_payer ]) }}"
                                data-env="{{ getEnvMode() }}"
                                for="vendor_tax_status">
                                <input type="checkbox" class="toggle-switch-input"
                                    {{ $systemTaxVat?->is_active == 1 ? 'checked' : '' }} id="vendor_tax_status">
                                <span class="toggle-switch-label">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            <div id='tax_settings' class="{{ $systemTaxVat?->is_active == 1 ? '' : 'disabled' }}">

                <form action="{{ route('taxvat.systemTaxVatStore') }}" method="post">
                    @csrf
                    @method('put')
                    <input type="hidden" name="country_code"
                        value="{{ $country_code ?? ($systemTaxVat?->country_code ?? null) }}">
                    <input type="hidden" id="system_tax_id" name="system_tax_id" value="{{ $systemTaxVat?->id }}">
                    <div class="card p-20">
                        <div class="bg--secondary p-15 rounded mb-20">
                            <div class="mb-20">

                                @php($productType = translate('Product Price'))
                                <h4 class="mb-1">{{ translate('Tax calculation based on') . ' ' . $productType }} </h4>
                            </div>
                            <div class="bg-white border rounded p-15">
                                <div class="row g-lg-4 g-md-3 g-2">
                                    <div class="col-md-6">
                                        <div class="custom-radio d-flex align-items-start gap-2">
                                            <input class="w-20px h-20px" type="radio" id="include1" name="tax_status"
                                                value="include"
                                                {{ !$systemTaxVat || $systemTaxVat?->is_included == 1 ? 'checked' : '' }}>
                                            <label for="include1" class="fz-14 mb-0 d-flex flex-column">
                                                <h5 class="mb-1">{{ translate('Calculate Tax Include') }}
                                                    {{ $productType }}
                                                </h5>
                                                <p class="mb-0 fz-11 fw-normal">
                                                    {{ translate('By selecting this option no need to setup VAT from here. If enabled the customer will see the total food price including VAT.') }}
                                                </p>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="custom-radio d-flex align-items-start gap-2">
                                            <input class="w-20px h-20px" type="radio" id="include2" name="tax_status"
                                                {{ $systemTaxVat && $systemTaxVat?->is_included == 0 ? 'checked' : '' }}
                                                value="exclude">
                                            <label for="include2" class="fz-14 mb-0 d-flex flex-column">
                                                <h5 class="mb-1">{{ translate('Calculate Tax Exclude') }}
                                                    {{ $productType }}
                                                </h5>
                                                <p class="mb-0 fz-11 fw-normal">
                                                    {{ translate('By selecting this option you will need to setup individual vat rate for different types of income source.') }}
                                                </p>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tax_rate_setup"
                            class="{{ !$systemTaxVat || $systemTaxVat?->is_included == 1 ? 'disabled' : '' }}">



                            <div class="bg--secondary rounded p-20 mb-20">
                                <div class="row g-lg-4 g-md-3 g-2">
                                    <div class="col-md-6">
                                        <h3 class="mb-1">{{ translate('messages.Basic Setup') }}</h3>
                                        {{-- <p class="mb-0 fz-12">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p> --}}
                                        <div class="danger-notes-bg px-2 py-2 rounded fz-11  gap-2 align-items-center mt-10px d-none"
                                            id=tax_type_change_alert>
                                            <svg width="14" height="15" viewBox="0 0 14 15" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_13920_306)">
                                                    <path
                                                        d="M13.464 9.10734L8.75069 1.664C8.35402 1.09234 7.69485 0.748169 7.00069 0.748169C6.30652 0.748169 5.64735 1.0865 5.23319 1.6815L0.543187 9.09567C-0.051813 9.94734 -0.162646 10.9682 0.25152 11.7557C0.659854 12.5432 1.51735 12.9923 2.59069 12.9923H11.4107C12.4899 12.9923 13.3415 12.5432 13.7499 11.7557C14.1582 10.9682 14.0474 9.95317 13.464 9.10734ZM6.41735 4.24817C6.41735 3.92734 6.67985 3.66484 7.00069 3.66484C7.32152 3.66484 7.58402 3.92734 7.58402 4.24817V7.74817C7.58402 8.069 7.32152 8.3315 7.00069 8.3315C6.67985 8.3315 6.41735 8.069 6.41735 7.74817V4.24817ZM7.00069 11.2482C6.51652 11.2482 6.12569 10.8573 6.12569 10.3732C6.12569 9.889 6.51652 9.49817 7.00069 9.49817C7.48485 9.49817 7.87569 9.889 7.87569 10.3732C7.87569 10.8573 7.48485 11.2482 7.00069 11.2482Z"
                                                        fill="#FF4040" />
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_13920_306">
                                                        <rect width="14" height="14" fill="white"
                                                            transform="translate(0 0.164795)" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            <span>
                                                {{ translate('messages.When you change') }} <span
                                                    class="font-semibold title-clr">{{ translate('messages.Tax Type') }}</span>
                                                {{ translate('to product wise.Restaurants will have control to setup the taxes of their products.') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex flex-column gap-lg-4 gap-3">
                                            <div class="">
                                                <span
                                                    class="mb-2 d-block title-clr fw-normal">{{ translate('messages.Select Tax Type') }}</span>
                                                <select id="tax_type"
                                                    class="custom-select multiple-select2 js-select2-custom custom-select-color border rounded w-100"
                                                    name="tax_type"
                                                    data-current_seclected="{{ $systemTaxVat?->tax_type }}">

                                                    @php($tax_calculate_on = $tax_payer == 'vendor' ? 'tax_calculate_on' : 'tax_calculate_on_' . $tax_payer)

                                                    @foreach (data_get($systemData, $tax_calculate_on, ['order_wise', 'product_wise', 'category_wise']) as $item)
                                                        <option {{ $systemTaxVat?->tax_type == $item ? 'selected' : '' }}
                                                            value="{{ $item }}"> {{ translate($item) }} </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div id="tax_rate_div"
                                                class="{{ !$systemTaxVat || in_array($systemTaxVat?->tax_type, ['order_wise', 'trip_wise']) ? '' : 'd-none' }}">
                                                <span
                                                    class="mb-2 d-block title-clr fw-normal">{{ translate('Select Tax Rate') }}</span>
                                                <select
                                                    {{ in_array($systemTaxVat?->tax_type, ['order_wise', 'trip_wise']) ? 'selected' : '' }}
                                                    name="tax_ids[]" id="tax__rate"
                                                    class="form-control js-select2-custom" multiple="multiple"
                                                    placeholder="{{translate('--Select Tax Rate--')}}">
                                                    @foreach ($taxVats as $taxVat)
                                                        <option
                                                            {{ in_array($taxVat->id, $systemTaxVat?->tax_ids ?? []) ? 'selected' : '' }}
                                                            value="{{ $taxVat->id }}"> {{ $taxVat->name }}
                                                            ({{ $taxVat->tax_rate }}%)
                                                        </option>
                                                    @endforeach

                                                </select>
                                            </div>
                                            <div id="info_notes"
                                                class="info-notes-bg px-2 py-2 rounded fz-11  gap-2 align-items-center {{ in_array($systemTaxVat?->tax_type, ['category_wise', 'product_wise']) ? 'd-flex' : 'd-none' }} ">
                                                <svg width="20" height="21" viewBox="0 0 20 21" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="url(#clip0_13899_104013)">
                                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                                            d="M10.3125 2.53979V1.28979C10.3125 1.11729 10.1725 0.977295 10 0.977295C9.8275 0.977295 9.6875 1.11729 9.6875 1.28979V2.53979C9.6875 2.71229 9.8275 2.85229 10 2.85229C10.1725 2.85229 10.3125 2.71229 10.3125 2.53979Z"
                                                            fill="#245BD1" />
                                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                                            d="M5.34578 4.31882L4.47078 3.44382C4.34891 3.32195 4.15078 3.32195 4.02891 3.44382C3.90703 3.5657 3.90703 3.76382 4.02891 3.8857L4.90391 4.7607C5.02578 4.88257 5.22391 4.88257 5.34578 4.7607C5.46766 4.63882 5.46766 4.4407 5.34578 4.31882Z"
                                                            fill="#245BD1" />
                                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                                            d="M3.125 9.10229H1.875C1.7025 9.10229 1.5625 9.24229 1.5625 9.41479C1.5625 9.58729 1.7025 9.72729 1.875 9.72729H3.125C3.2975 9.72729 3.4375 9.58729 3.4375 9.41479C3.4375 9.24229 3.2975 9.10229 3.125 9.10229Z"
                                                            fill="#245BD1" />
                                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                                            d="M4.90391 14.0688L4.02891 14.9438C3.90703 15.0657 3.90703 15.2638 4.02891 15.3857C4.15078 15.5076 4.34891 15.5076 4.47078 15.3857L5.34578 14.5107C5.46766 14.3888 5.46766 14.1907 5.34578 14.0688C5.22391 13.9469 5.02578 13.9469 4.90391 14.0688Z"
                                                            fill="#245BD1" />
                                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                                            d="M14.6539 14.5107L15.5289 15.3857C15.6508 15.5076 15.8489 15.5076 15.9708 15.3857C16.0927 15.2638 16.0927 15.0657 15.9708 14.9438L15.0958 14.0688C14.9739 13.9469 14.7758 13.9469 14.6539 14.0688C14.532 14.1907 14.532 14.3888 14.6539 14.5107Z"
                                                            fill="#245BD1" />
                                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                                            d="M16.875 9.72729H18.125C18.2975 9.72729 18.4375 9.58729 18.4375 9.41479C18.4375 9.24229 18.2975 9.10229 18.125 9.10229H16.875C16.7025 9.10229 16.5625 9.24229 16.5625 9.41479C16.5625 9.58729 16.7025 9.72729 16.875 9.72729Z"
                                                            fill="#245BD1" />
                                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                                            d="M15.0958 4.7607L15.9708 3.8857C16.0927 3.76382 16.0927 3.5657 15.9708 3.44382C15.8489 3.32195 15.6508 3.32195 15.5289 3.44382L14.6539 4.31882C14.532 4.4407 14.532 4.63882 14.6539 4.7607C14.7758 4.88257 14.9739 4.88257 15.0958 4.7607Z"
                                                            fill="#245BD1" />
                                                        <path
                                                            d="M7.5 16.6023V15.6648C7.5 14.9773 7.1875 14.321 6.625 13.9148C5.25 12.8835 4.375 11.2585 4.375 9.41477C4.375 6.10227 7.25 3.44602 10.625 3.82102C13.2188 4.10227 15.2812 6.16477 15.5938 8.75852C15.8438 10.8835 14.9062 12.7898 13.375 13.9148C12.8125 14.321 12.5 14.9773 12.5 15.6648V16.6023H7.5Z"
                                                            fill="#BED2FE" />
                                                        <path
                                                            d="M7.5 16.2898H12.5V18.2273C12.5 18.5398 12.25 18.7898 11.9375 18.7898H11.25C11.25 19.4773 10.6875 20.0398 10 20.0398C9.3125 20.0398 8.75 19.4773 8.75 18.7898H8.0625C7.75 18.7898 7.5 18.5398 7.5 18.2273V16.2898Z"
                                                            fill="#245BD1" />
                                                    </g>
                                                    <defs>
                                                        <clipPath id="clip0_13899_104013">
                                                            <rect width="20" height="20" fill="white"
                                                                transform="translate(0 0.664795)" />
                                                        </clipPath>
                                                    </defs>
                                                </svg>
                                                <span
                                                    class="{{ $systemTaxVat?->tax_type == 'category_wise' ? '' : 'd-none' }}"
                                                    id="info_for_category">
                                                    {{ translate('messages.Please specify the tax rate while creating a category from') }}
                                                    <span
                                                        class="font-semibold theme-clr text-decoration-underline">{{ translate('Category List') }}.</span>
                                                    {{ translate('If you already created category without tax then go to category edit & update tax.') }}
                                                </span>
                                                <span
                                                    class="{{ $systemTaxVat?->tax_type == 'product_wise' ? '' : 'd-none' }}"
                                                    id="info_for_item">
                                                    {{ translate('messages.Please specify the tax rate while creating a Product from') }}
                                                    <span
                                                        class="font-semibold theme-clr text-decoration-underline">{{ translate('Products List') }}.</span>
                                                    {{ translate('If you already created Products without tax then go to edit Product and update tax.') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                               @php($additional_tax = $tax_payer == 'vendor' ? 'additional_tax' : 'additional_tax_' . $tax_payer)

                            @if (data_get($systemData, $additional_tax, null))

                                <div class="bg--secondary rounded p-20">
                                    <div class="row g-lg-4 g-md-3 g-2">
                                        <div class="col-md-6">
                                            <h3 class="mb-1">{{ translate('messages.Additional Setup') }}</h3>
                                            {{-- <p class="mb-0 fz-12">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p> --}}
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex flex-column gap-lg-4 gap-3">
                                                @foreach ($systemData[$additional_tax] as $item)
                                                    @php($additionalData = $systemTaxVat?->additionalData?->where('name', $item)->first())
                                                    <div>
                                                        <div
                                                            class="d-flex align-items-center gap-1 justify-content-between mb-2">
                                                            <span
                                                                class="title-clr fw-normal">{{ translate($item) }}</span>
                                                            <label class="toggle-switch toggle-switch-sm"
                                                                for="services__charge_{{ $item }}">
                                                                <input type="checkbox"
                                                                    name="additional_status[{{ $item }}]"
                                                                    class="toggle-switch-input check_additional_data"
                                                                    {{ $additionalData?->is_active ? 'checked' : '' }}
                                                                    data-id="{{ $item }}" value="1"
                                                                    id="services__charge_{{ $item }}">
                                                                <span class="toggle-switch-label">
                                                                    <span class="toggle-switch-indicator"></span>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <select id="additional_charge_{{ $item }}"
                                                            name="additional[{{ $item }}][]"
                                                            class="form-control multiple-select2 js-select2-custom service__charge"
                                                            multiple="multiple" placeholder="{{translate('--Select Tax Rate--')}}">
                                                            @foreach ($taxVats as $taxVat)
                                                                <option
                                                                    {{ in_array($taxVat->id, $additionalData?->tax_ids ?? []) ? 'selected' : '' }}
                                                                    value="{{ $taxVat->id }}"> {{ $taxVat->name }}
                                                                    ({{ $taxVat->tax_rate }}%)
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-end mt-4 gap-md-3 gap-2">
                        <button type="button"
                            class="btn bg--secondary h--42px title-clr px-4">{{ translate('messages.Reset') }}</button>
                        <button type="{{ getEnvMode() != 'demo' ? 'submit' : 'button' }}" class="btn btn--primary call-demo">{{ translate('Save Information') }}</button>
                    </div>
                </form>
            </div>

    </div>
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img src="{{ dynamicModuleAsset('TaxModule', 'assets/admin/img/status-ons.png') }}" class="mb-20"
                        alt="">
                    <h3 class="title-clr mb-2" id="confirmationTitle"></h3>
                    <p class="fz--14px" id="confirmationMessage"></p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 gap-2">
                    <button type="button" class="btn min-w-120px btn--secondary"
                        data-dismiss="modal">{{ translate('No') }}</button>
                    <button type="button" id="seturl" data-url=""
                        class="btn min-w-120px btn--primary">{{ translate('Yes') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('script_2')
    <script src="{{ dynamicModuleAsset('TaxModule', 'assets/js/admin/toastr_notification.js') }}"></script>
    <script src="{{ dynamicModuleAsset('TaxModule', 'assets/js/admin/system_taxvat.js') }}"></script>
    <script>
        $(document).on('click', '.call-demo', function () {
            @if(getEnvMode() =='demo')
                toastr.info('{{ translate('Update option is disabled for demo!') }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            @endif
        });
    </script>
@endpush
