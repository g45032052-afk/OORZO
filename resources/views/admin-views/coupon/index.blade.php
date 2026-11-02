@extends('layouts.admin.app')

@section('title',translate('Add_New_Coupon'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-add-circle-outlined"></i> {{translate('messages.Add_New_Coupon')}}</h1>
                </div>
            </div>
        </div>

        @php($language = \App\CentralLogics\Helpers::get_business_settings('language'))

        <!-- End Page Header -->
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{route('admin.coupon.store')}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-12">

                            <ul class="nav nav-tabs mb-3 border-0">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active"
                                    href="#"
                                    id="default-link">{{translate('messages.default')}}</a>
                                </li>
                                @foreach ($language as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link"
                                            href="#"
                                            id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="lang_form" id="default-form">
                                <div class="form-group">
                                    <label class="input-label"
                                        for="default_title">{{ translate('messages.title') }} ({{ translate('messages.Default') }})
                                            <span class="text-danger">*</span>

                                    </label>
                                    <input type="text" name="title[]" id="default_title"
                                        class="form-control custom_invalid_message" placeholder="{{ translate('messages.new_coupon') }}"

                                         >
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            </div>
                                @foreach ($language as $lang)
                                    <div class="d-none lang_form"
                                        id="{{ $lang }}-form">
                                        <div class="form-group">
                                            <label class="input-label"
                                                for="{{ $lang }}_title">{{ translate('messages.title') }}
                                                ({{ strtoupper($lang) }})

                                            </label>
                                            <input type="text" name="title[]" id="{{ $lang }}_title"
                                                class="form-control" placeholder="{{ translate('messages.new_coupon') }}"
                                                 >
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                    </div>
                                @endforeach


                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.coupon_type')}}   <span class="text-danger">*</span></label>
                                <select id="coupon_type" required  name="coupon_type" data-invalid-text="{{ translate('messages.Please_select_the_coupon_type') }}" class="form-control custom_invalid_message">
                                    <option value="restaurant_wise">{{translate('messages.restaurant_wise')}}</option>
                                    <option value="zone_wise">{{translate('messages.zone_wise')}}</option>
                                    <option value="free_delivery">{{translate('messages.free_delivery')}}</option>
                                    <option value="first_order">{{translate('messages.first_order')}}</option>
                                    @if (\App\CentralLogics\Helpers::get_business_settings('pro_member_status') == 1)
                                        <option value="pro_customer">{{translate('messages.pro_customer')}}</option>
                                    @endif
                                    <option value="default">{{translate('messages.default')}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-lg-3 col-sm-6" id="restaurant_wise">
                            <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.restaurant')}}
                                <span class="text-danger">*</span>
                                </label>
                            <select id="select_restaurant" name="restaurant_ids[]" class="js-data-example-ajax custom_invalid_message form-control"
                                    data-invalid-text="{{ translate('messages.Please_select_a_restaurant') }}" data-placeholder="{{translate('messages.select_restaurant')}}" title="{{translate('messages.select_restaurant')}}">
                            </select>
                        </div>

                        <div class="form-group counter__unique-wrap col-lg-3 col-sm-6" id="customer_wise">
                            <div class="pickup-zone-tag">
                                <label class="input-label" for="select_customer">{{translate('messages.select_customer')}} <span class="text-danger">*</span></label>

                                <select name="customer_ids[]"  id="select_customer"
                                        data-invalid-text="{{ translate('messages.Please_select_a_customer') }}"
                                        data-placeholder="{{ translate('messages.select_customer') }}"
                                        multiple="multiple" class="form-control js-data-example-ajax-2 custom_invalid_message counter-select2">
                                </select>

                            </div>
                        </div>

                        <div class="form-group col-lg-3 col-sm-6" id="zone_wise">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('messages.select_zone')}} <span class="text-danger">*</span></label>
                            <select name="zone_ids[]" id="choice_zones"
                                class="form-control  multiple-select2 js-select2-custom"
                                data-invalid-text="{{ translate('messages.Please_select_a_zone') }}"
                                multiple="multiple" data-placeholder="{{translate('messages.select_zone')}}">
                            @foreach(\App\Models\Zone::where('status',1)->get(['id','name']) as $zone)
                                <option value="{{$zone->id}}">{{$zone->name}}</option>
                            @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group">
                                <div class="d-flex justify-content-between">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.code')}} <span class="text-danger">*</span></label>
                                    <label class="input-label generate-code" id="generate_code"><i class="tio-hand-draw"></i>{{translate('messages.Generate Code')}}</label>
                                </div>
                                <input id="coupon_code" type="text" name="code" class="form-control custom_invalid_message"
                                       placeholder="{{\Illuminate\Support\Str::random(8)}}" required maxlength="100" data-invalid-text="{{ translate('messages.Please_enter_a_coupon_code') }}">
                            </div>
                        </div>
                        <div id="limit_for_same_user"  class="col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.limit_for_same_user')}}  <span class="text-danger">*</span></label>
                                <input type="number" name="limit" id="coupon_limit"  class="form-control custom_invalid_message" placeholder="{{ translate('messages.Ex:_10') }}" max="100" data-invalid-text="{{ translate('messages.Please_enter_a_limit') }}">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.start_date')}} <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control custom_invalid_message" id="date_from" required data-invalid-text="{{ translate('messages.Please_select_a_start_date') }}">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.expire_date')}} <span class="text-danger">*</span></label>
                                <input type="date" name="expire_date" class="form-control custom_invalid_message" id="date_to" required data-invalid-text="{{ translate('messages.Please_select_an_expire_date') }}">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.discount_type')}} <span class="text-danger">*</span></label>
                                <select name="discount_type" required class="form-control custom_invalid_message" id="discount_type" data-invalid-text="{{ translate('messages.Please_select_a_discount_type') }}">
                                    <option value="amount">
                                            {{ translate('messages.amount').' ('.\App\CentralLogics\Helpers::currency_symbol().')'  }}
                                    </option>
                                    <option value="percent"> {{ translate('messages.percent').' (%)' }}</option>
                                </select>
                            </div>
                        </div>
                          <div class="col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.min_purchase')}} ({{ \App\CentralLogics\Helpers::currency_symbol() }}) <span class="text-danger">*</span></label>
                                <input id="min_purchase" type="number" step="0.01" name="min_purchase" value="0" min="1" max="999999999999.99" class="form-control custom_invalid_message"
                                    placeholder="100" data-invalid-text="{{ translate('messages.Please_enter_a_min_purchase') }}">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.discount')}} <span class="text-danger">*</span>
                            </label>
                                <input type="number" step="0.01" min="1" max="999999999999.99" name="discount" id="discount" class="form-control custom_invalid_message" required data-invalid-text="{{ translate('messages.Please_enter_a_discount') }}">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label class="input-label" for="max_discount">{{translate('messages.max_discount')}} ({{ \App\CentralLogics\Helpers::currency_symbol() }}) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" value="0" max="999999999999.99" name="max_discount" id="max_discount" class="form-control custom_invalid_message" data-invalid-text="{{ translate('messages.Please_enter_a_max_discount') }}">
                            </div>
                        </div>

                    </div>
                    <div class="btn--container justify-content-end">
                        <button id="reset_btn" type="button" class="btn btn--reset">{{translate('messages.reset')}}</button>
                        <button type="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header py-2">
                <div class="search--button-wrapper">
                    <h5 class="card-title">{{translate('messages.coupon_list')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$coupons->total()}}</span></h5>
                    <form method="get" >
                        <!-- Search -->
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input id="datatableSearch" type="search" value="{{ request()?->search ?? null }}"  name="search" class="form-control" placeholder="{{ translate('messages.Ex:_Search_by_title_or_code') }}" aria-label="{{translate('messages.search_here')}}">
                            <button type="submit" class="btn btn--secondary secondary-cmn"><i class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>

                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40" href="javascript:;"
                            data-hs-unfold-options='{
                                    "target": "#usersExportDropdown",
                                    "type": "css-animation"
                                }'>
                            <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                        </a>

                        <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">

                            <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                            <a id="export-excel" class="dropdown-item" href="
                            {{ route('admin.coupon.coupon_export', ['type' => 'excel', request()->getQueryString()]) }}
                                ">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="
                            {{ route('admin.coupon.coupon_export', ['type' => 'csv', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                {{ translate('messages.csv') }}
                            </a>
                        </div>
                    </div>


                </div>
            </div>
            <!-- Table -->
            <div class="table-responsive datatable-custom" id="table-div">
                <table id="columnSearchDatatable"
                        class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                        data-hs-datatables-options='{
                        "order": [],
                        "orderCellsTop": true,

                        "entries": "#datatableEntries",
                        "isResponsive": false,
                        "isShowPaging": false,
                        "paging":false,
                        }'>
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('messages.sl') }}</th>
                        <th>{{translate('messages.title')}}</th>
                        <th>{{translate('messages.code')}}</th>
                        <th>{{translate('messages.type')}}</th>
                        <th>{{translate('messages.total_uses')}}</th>
                        <th>{{translate('messages.min_purchase')}}</th>
                        <th>{{translate('messages.max_discount')}}</th>
                        <th>
                            <div class="text-center">
                                {{translate('messages.discount')}}
                            </div>
                        </th>
                        <th>{{translate('messages.discount_type')}}</th>
                        <th>{{translate('messages.start_date')}}</th>
                        <th>{{translate('messages.expire_date')}}</th>
                        <th>{{translate('messages.Customer_type')}}</th>
                        <th>{{translate('messages.status')}}</th>
                        <th class="text-center">{{translate('messages.action')}}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($coupons as $key=>$coupon)
                        <tr>
                            <td>{{$key+$coupons->firstItem()}}</td>
                            <td>
                            <span class="d-block font-size-sm text-body">
                                {{Str::limit($coupon['title'],15,'...')}}
                            </span>
                            </td>
                            <td>{{$coupon['code']}}</td>
                            <td>{{translate('messages.'.$coupon->coupon_type)}}</td>
                            <td>{{$coupon->total_uses}}</td>
                            <td>
                                <div class="text-right mw-87px">
                                    {{\App\CentralLogics\Helpers::format_currency($coupon['min_purchase'])}}
                                </div>
                            </td>
                            <td>
                                <div class="text-right mw-87px">
                                    {{\App\CentralLogics\Helpers::format_currency($coupon['max_discount'])}}
                                </div>
                            </td>
                            <td>
                                <div class="text-center">
                                    {{$coupon['discount']}}
                                </div>
                            </td>
                            @if ($coupon['discount_type'] == 'percent')
                            <td>{{ translate('messages.percent')}}</td>
                            @elseif ($coupon['discount_type'] == 'amount')
                            <td>{{ translate('messages.amount')}}</td>
                            @else
                            <td>{{$coupon['discount_type'] == ''? '--':$coupon['discount_type']}}</td>
                            @endif

                            <td>{{$coupon['start_date']}}</td>
                            <td>{{$coupon['expire_date']}}</td>

                            <td>
                                <span class="d-block font-size-sm text-body">
                                    @if (in_array('all', json_decode($coupon->customer_id)))
                                    {{translate('messages.all_customers')}}
                                    @else
                                    {{translate('messages.Selected_customers')}}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <label class="toggle-switch toggle-switch-sm" for="couponCheckbox{{$coupon->id}}">
                                    <input type="checkbox" data-url="{{route('admin.coupon.status',[$coupon['id'],$coupon->status?0:1])}}" class="toggle-switch-input redirect-url" id="couponCheckbox{{$coupon->id}}" {{$coupon->status?'checked':''}}>
                                    <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                </label>
                            </td>
                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="btn btn-sm btn--primary btn-outline-primary action-btn" href="{{route('admin.coupon.update',[$coupon['id']])}}"title="{{translate('messages.edit_coupon')}}"><i class="tio-edit"></i>
                                    </a>
                                    <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert" href="javascript:" data-id="coupon-{{$coupon['id']}}" data-message="{{ translate('Want to delete this coupon ?') }}" title="{{translate('messages.delete_coupon')}}"><i class="tio-delete-outlined"></i>
                                    </a>
                                    <form action="{{route('admin.coupon.delete',[$coupon['id']])}}"
                                    method="post" id="coupon-{{$coupon['id']}}">
                                    @csrf @method('delete')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if(count($coupons) === 0)
                <div class="empty--data">
                    <img src="{{dynamicAsset('assets/admin/img/empty.png')}}" alt="public">
                    <h5>
                        {{translate('no_data_found')}}
                    </h5>
                </div>
                @endif
                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div>
                            {!! $coupons->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Table -->
    </div>

@endsection

@push('script_2')
     <script src="{{dynamicAsset('assets/admin/js/view-pages/coupon-index.js')}}"></script>
    <script>
        "use strict";

        $(document).on('ready', function () {
            let $select = $('.js-data-example-ajax-2');

            $select.select2({
                ajax: {
                    url: '{{ route('admin.customer.select-list') }}',
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            all:true,
                            all_value: "all",
                            @if (isset($zone))
                            zone_ids: [{{ $zone->id }}],
                            @endif
                                @if (request('restaurant_id'))
                            restaurant_id: {{ request('restaurant_id') }},
                            @endif
                            page: params.page
                        };
                    },
                    processResults: function(data) {
                        let selected_customers = $('#select_customer').val();
                        if(Array.isArray(selected_customers) && selected_customers.includes("all")) {
                            for(let i = 0; i<data.length; i++) {
                                if(data[i].id !== "all") {
                                    data[i].disabled = true;
                                }
                            }
                        }

                        return {
                            results: data
                        };
                    },
                    __port: function(params, success, failure) {
                        var $request = $.ajax(params);

                        $request.then(success);
                        $request.fail(failure);

                        return $request;
                    }
                }
            });

            $select.on('select2:select', function (e) {
                let selected = $(this).val() || [];

                if (e.params.data.id === 'all') {
                    $(this).val(['all']).trigger('change');
                }
                else if (selected.includes('all')) {
                    let filtered = selected.filter(id => id !== 'all');
                    $(this).val(filtered).trigger('change');
                }
            });

        });

        $(document).on('ready', function () {
            $('.js-data-example-ajax').select2({
                ajax: {
                    url: '{{ route('admin.restaurant.get-restaurants') }}',
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    __port: function (params, success, failure) {
                        let $request = $.ajax(params);

                        $request.then(success);
                        $request.fail(failure);

                        return $request;
                    }
                }
            });


        });
        $(document).ready(function() {
            $('#generate_code').click(function() {
                generateUniqueCode();
            });

            function generateUniqueCode() {
                let code = generateRandomCode();
                checkCodeExists(code, function(exists) {
                    if (exists) {
                        generateUniqueCode();
                    } else {
                        $('#coupon_code').val(code);
                    }
                });
            }

            function generateRandomCode() {
                let length = 8;
                let result = '';
                let characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                let charactersLength = characters.length;
                for (let i = 0; i < length; i++) {
                    result += characters.charAt(Math.floor(Math.random() * charactersLength));
                }
                return result;
            }

            function checkCodeExists(code, callback) {
                $.ajax({
                    url: '{{ route('admin.coupon.check.code') }}',
                    method: 'get',
                    data: { code: code },
                    success: function(response) {
                        callback(response.exists);
                    },
                    error: function() {
                        callback(false);
                    }
                });
            }
        });

    </script>



@endpush
