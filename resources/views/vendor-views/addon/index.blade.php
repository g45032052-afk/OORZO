@extends('layouts.vendor.app')

@section('title',translate('messages.add_new_addon'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <div class="card-header-icon d-inline-flex mr-2 img">
                    <img src="{{dynamicAsset('assets/admin/img/addon.png')}}" alt="public">
                </div>
                {{translate('messages.add_new_addon')}}
            </h1>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{route('vendor.addon.store')}}" method="post" class="row">
                    @csrf
                    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                    @php($language = $language->value ?? null)
                    @php($default_lang = str_replace('_', '-', app()->getLocale()))
                    @if($language)
                        <div class="col-12">
                            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                <ul class="nav nav-tabs mb-4">
                                    <li class="nav-item">
                                        <a class="nav-link lang_link active" href="#" id="default-link">{{ translate('Default')}}</a>
                                    </li>
                                    @foreach(json_decode($language) as $lang)
                                        <li class="nav-item">
                                            <a class="nav-link lang_link" href="#" id="{{$lang}}-link">{{\App\CentralLogics\Helpers::get_language_name($lang).'('.strtoupper($lang).')'}}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="form-group col-md-4 lang_form" id="default-form">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}} <span class="text-danger">*</span></label>
                            <input type="text" name="name[]" class="form-control" placeholder="{{ translate('messages.Ex :') }} {{translate('Water')}}" required maxlength="191">
                        </div>

                        <input type="hidden" name="lang[]" value="default">
                        @foreach(json_decode($language) as $lang)
                            <div class="form-group col-md-4 d-none lang_form" id="{{$lang}}-form">
                                <label class="form-label" for="exampleFormControlInput1">{{translate('messages.name')}} ({{strtoupper($lang)}})<span class="text-danger">*</span></label>
                                <input type="text" name="name[]" class="form-control h--45px" placeholder="{{translate('Ex : New Addon ')}}" maxlength="191"   >
                            </div>
                            <input type="hidden" name="lang[]" value="{{$lang}}">
                        @endforeach
                    @else
                        <div class="form-group col-md-4 lang_form" id="default-form">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}}<span class="text-danger">*</span></label>
                            <input type="text" name="name[]" class="form-control" placeholder="{{ translate('messages.Ex :') }} {{translate('Water')}}"  required maxlength="191">
                        </div>
                        <input type="hidden" name="lang[]" value="default">
                    @endif

                    <div class="form-group col-md-4">
                        <label class="form-label" for="exampleFormControlInput1">{{translate('messages.price')}}<span class="text-danger">*</span></label>
                        <input type="number" min="0" max="999999999999.99" name="price" step="0.01" class="form-control h--45px" placeholder="{{ translate('Ex : 100.00') }}" value="{{old('price')}}" required>
                    </div>

                    @if ($productWiseTax)
                        <div class="col-md-4">
                            <div class="form-group">
                                <span class="mb-2 d-block title-clr fw-normal">{{ translate('Select Tax Rate') }}<span class="text-danger">*</span></span>
                                <select name="tax_ids[]" required id="tax__rate"
                                        class="form-control multiple-select2" multiple="multiple"
                                        placeholder="{{translate('--Select Tax Rate--')}}">
                                    @foreach ($taxVats as $taxVat)
                                        <option value="{{ $taxVat->id }}"> {{ $taxVat->name }}
                                            ({{ $taxVat->tax_rate }}%)
                                        </option>
                                    @endforeach
                                </select>

                            </div>
                        </div>
                    @endif

                    <div class="form-group col-md-4">
                        <label class="input-label"
                            for="exampleFormControlInput1">{{ translate('messages.Stock_Type') }} <span class="text-danger">*</span>
                        </label>
                        <select name="stock_type" id="stock_type" class="form-control js-select2-custom">
                            <option value="unlimited">{{ translate('messages.Unlimited_Stock') }}</option>
                            <option value="limited">{{ translate('messages.Limited_Stock')  }}</option>
                            <option value="daily">{{ translate('messages.Daily_Stock')  }}</option>
                        </select>
                    </div>

                    <div class="form-group col-md-4 hide_this">
                        <label class="input-label" for="addon_stock">{{translate('messages.Addon_Stock')}}</label>
                        <input type="number" min="0" id="addon_stock" max="999999999999" name="addon_stock"  readonly value="{{old('addon_stock')}}" class="form-control stock_disable" placeholder="{{ translate('messages.Unlimited') }}" required>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <span class="mb-2 d-block title-clr fw-normal">{{ translate('Category') }} <span class="text-danger">*</span></span>
                            <select name="category_id" id="category_id" required class="form-control js-select2-custom"
                                placeholder="Select Category">
                                <option selected disabled value="">
                                    {{ translate('messages.select_category') }}</option>
                                @foreach ($addonCategories as $addonCategory)
                                    <option value="{{ $addonCategory->id }}"> {{ $addonCategory->name }}
                                    </option>
                                @endforeach
                            </select>

                        </div>
                    </div>

                    <div class="col-12">
                        <div class="btn--container justify-content-end">
                            <button type="reset" id="reset_btn" class="btn btn--reset">{{translate('messages.reset')}}</button>
                            <button type="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header border-0">
                <div class="search--button-wrapper">
                    <h5 class="card-title">
                        {{translate('messages.addon_list')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$addons->total()}}</span>
                    </h5>
                    <div class="d-flex flex-xl-nowrap flex-wrap gap-3">
                        <div id="search-form" class="min-w-xxl-350">
                            <form>
                                <div class="input-group input--group border rounded">
                                    <input autocomplete="false" type="text" class="d-none">
                                    <input type="text" name="search" value="{{ request()?->search ?? null }}"  class="form-control rounded border-0" placeholder="{{ translate('Ex : Search by Addon Name') }}">
                                    <button type="submit" class="btn btn--reset py-1 px-2">
                                        <i class="tio-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="min-w-100px">
                            <select name="stock_type" id="stock_type" class="form-control js-select2-custom set-filter" data-url="{{url()->full()}}" data-filter="stock_type">
                                <option {{ request()?->stock_type =='all'?'selected':''}} value="all">{{ translate('messages.All Stock') }}</option>
                                <option {{ request()?->stock_type =='unlimited'?'selected':''}} value="unlimited">{{ translate('messages.unlimited Stock')  }}</option>
                                <option {{ request()?->stock_type =='limited'?'selected':''}} value="limited">{{ translate('messages.Limited Stock')  }}</option>
                                <option {{ request()?->stock_type =='daily'?'selected':''}} value="daily">{{ translate('messages.Daily Stock')  }}</option>
                            </select>
                        </div>

                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker btn btn-sm fw-500 border text-title bg-transparent dropdown-toggle min-height-40"
                            href="javascript:;"
                            data-hs-unfold-options='{
                                    "target": "#usersExportDropdown",
                                    "type": "css-animation"
                                }'>
                                <i class="tio-arrow-large-downward mr-1 fs-16"></i>
                                {{ translate('messages.export') }}
                            </a>

                            <div id="usersExportDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                <span class="dropdown-header">
                                    {{ translate('messages.download_options') }}
                                </span>

                                <a id="export-excel" class="dropdown-item"
                                href="{{ route('vendor.addon.export', ['type' => 'excel', request()->getQueryString()]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg">
                                    {{ translate('messages.excel') }}
                                </a>

                                <a id="export-csv" class="dropdown-item"
                                href="{{ route('vendor.addon.export', ['type' => 'csv', request()->getQueryString()]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg">
                                    .{{ translate('messages.csv') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Table -->
            <div class="table-responsive datatable-custom pt-0">
                <table id="columnSearchDatatable"
                       class="table table-borderless table-thead-borderedless table-nowrap table-align-middle card-table"
                       data-hs-datatables-options='{
                         "order": [],
                         "orderCellsTop": true,
                         "paging":false
                       }'>
                    <thead class="global-bg-box">
                    <tr>
                        <th class="w-100px">{{translate('messages.sl')}}</th>
                        <th class="w-30p">{{translate('messages.name')}}</th>
                        <th class="w-25p">{{translate('messages.price')}}</th>
                        @if ($productWiseTax)
                            <th class="border-0 w-20p">{{ translate('messages.Vat/Tax') }}</th>
                        @endif
                        <th class="w-26p">{{translate('messages.Stock_Type')}}</th>
                        <th class="w-26p">{{translate('messages.Stock')}}</th>
                        <th class="w-26p">{{translate('messages.Status')}}</th>
                        <th class="text-center w-100px">{{translate('messages.action')}}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($addons as $key=>$addon)
                        <tr>
                            <td>
                                {{$key+1}}
                            </td>
                            <td>
                                <span class="d-block font-size-sm text-body max-w-220px min-w-120px line--limit-1">
                                    {{Str::limit($addon['name'], 20, '...')}}
                                </span>
                            </td>
                            <td>
                                {{\App\CentralLogics\Helpers::format_currency($addon['price'])}}
                            </td>

                            @if ($productWiseTax)
                                <td>
                                    <span class="d-block font-size-sm text-body">
                                        @forelse ($addon?->taxVats?->pluck('tax.name', 'tax.tax_rate')->toArray() as $key => $item)
                                            <span> {{ ucwords($item) }} : <span class="font-bold">
                                                    ({{ $key }}%)
                                                </span> </span>
                                            <br>
                                        @empty
                                            <span> {{ translate('messages.N/A') }} </span>
                                        @endforelse
                                    </span>
                                </td>
                            @endif
                            <td>
                                {{ translate($addon->stock_type) }}
                            </td>
                            <td>
                                {{  $addon->stock_type == 'unlimited'? translate('messages.Unlimited') :  $addon->addon_stock }}
                            </td>
                            <td>
                                <label class="toggle-switch toggle-switch-sm" for="addonCheckbox{{$addon->id}}">
                                    <input type="checkbox" data-url="{{route('vendor.addon.status',[$addon['id'],$addon->status?0:1])}}" class="toggle-switch-input redirect-url" id="addonCheckbox{{$addon->id}}"
                                        {{$addon->status?'checked':''}}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </td>
                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="btn action-btn btn--primary btn-outline-primary"
                                            href="{{route('vendor.addon.edit',[$addon['id']])}}" title="{{translate('messages.edit_addon')}}"><i class="tio-edit"></i></a>
                                    <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:"
                                        data-id="addon-{{$addon['id']}}" data-message="{{ translate('Want to delete this addon ?') }}" title="{{translate('messages.delete_addon')}}"><i class="tio-delete-outlined"></i></a>
                                    <form action="{{route('vendor.addon.delete',[$addon['id']])}}"
                                                method="post" id="addon-{{$addon['id']}}">
                                        @csrf @method('delete')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if(count($addons) === 0)
                <div class="empty--data">
                    <img src="{{dynamicAsset('assets/admin/img/empty.png')}}" alt="public">
                    <h5>
                        {{translate('no_data_found')}}
                    </h5>
                </div>
                @endif
                <table>
                    <tfoot>
                    {!! $addons->withQueryString()->links() !!}
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        "use strict";
        $('#stock_type').on('change', function () {
            stock_type($(this).val());
        });
        stock_type($('#stock_type').val());

    function  stock_type(data){
        if(data == 'unlimited') {
                    $('.stock_disable').prop('readonly', true).prop('required', false).attr('placeholder', '{{ translate('Unlimited') }}').val('');
                     $('.hide_this').addClass('d-none');
                } else {
                    $('.stock_disable').prop('readonly', false).prop('required', true).attr('placeholder', '{{ translate('messages.Ex:_100') }}');
                     $('.hide_this').removeClass('d-none');
                }
    }

        $('#reset_btn').click(function(){
            $('#stock_type').val('unlimited').trigger('change');
            $('#category_id').val(null).trigger('change');
            $('#tax__rate').val(null).trigger('change');

            stock_type('unlimited');
        })
    </script>
@endpush
