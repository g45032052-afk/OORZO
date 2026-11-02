@extends('layouts.vendor.app')

@section('title',translate('add_new_coupon'))


@section('content')
@php($restaurant_data = \App\CentralLogics\Helpers::get_restaurant_data())
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-add-circle-outlined"></i> {{translate('messages.add_new_coupon')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{route('vendor.coupon.store')}}" method="post">
                    @csrf
                    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                    @php($language = $language->value ?? null)
                    @php($default_lang = str_replace('_', '-', app()->getLocale()))
                    <div class="row">
                        <div class="col-12">
                            @if ($language)
                            <ul class="nav nav-tabs mb-3 border-0">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active"
                                    href="#"
                                    id="default-link">{{translate('messages.default')}}</a>
                                </li>
                                @foreach (json_decode($language) as $lang)
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
                                        for="default_title">{{ translate('messages.title') }}
                                        ({{ translate('messages.Default') }}) <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="title[]" id="default_title"
                                        class="form-control remove-data" placeholder="{{ translate('messages.new_coupon') }}"

                                         >
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            </div>
                                @foreach (json_decode($language) as $lang)
                                    <div class="d-none lang_form"
                                        id="{{ $lang }}-form">
                                        <div class="form-group">
                                            <label class="input-label"
                                                for="{{ $lang }}_title">{{ translate('messages.title') }}
                                                ({{ strtoupper($lang) }})
                                            </label>
                                            <input type="text" name="title[]" id="{{ $lang }}_title"
                                                class="form-control remove-data" placeholder="{{ translate('messages.new_coupon') }}"
                                                 >
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                    </div>
                                @endforeach
                            @else
                                <div id="default-form">
                                    <div class="form-group">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ translate('messages.title') }} ({{ translate('messages.default') }})</label>
                                        <input type="text" name="title[]" class="form-control remove-data"
                                            placeholder="{{ translate('messages.new_coupon') }}" >
                                    </div>
                                    <input type="hidden" name="lang[]" value="default">
                                </div>
                            @endif
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.coupon_type')}}</label>
                                <select id="coupon_type" name="coupon_type" class="form-control coupon_type_change">
                                    <option value="default">{{translate('messages.default')}}</option>
                                    @if (($restaurant_data->restaurant_model == 'commission' && $restaurant_data->self_delivery_system == 1) ||($restaurant_data->restaurant_model == 'subscription' &&
                                        isset($restaurant_data->restaurant_sub) && $restaurant_data->restaurant_sub->self_delivery == 1))
                                    <option value="free_delivery">{{translate('messages.free_delivery')}}</option>
                                    @endif
                            </select>
                            </div>
                        </div>

                        <div class="col-lg-4 col-sm-6">
                            <div class="form-group">
                                <div class="d-flex justify-content-between">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.code')}} <span class="text-danger">*</span></label>
                                    <label class="input-label generate-code" id="generate_code"><i class="tio-hand-draw"></i>{{translate('messages.Generate Code')}}</label>
                                </div>
                                <input id="coupon_code" type="text" name="code" class="form-control"
                                       placeholder="{{\Illuminate\Support\Str::random(8)}}" required maxlength="100">
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.limit_for_same_user')}}
                                    <span class="text-danger">*</span>
                                </label>
                                <input required type="number" name="limit" id="coupon_limit" class="form-control" placeholder="{{ translate('messages.Ex :') }} 10" max="100">
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.start_date')}} <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" id="date_from" required>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.expire_date')}} <span class="text-danger">*</span></label>
                                <input type="date" name="expire_date" class="form-control" id="date_to" required>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6" id="discount_type_div">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.discount_type')}} </label>
                                <select name="discount_type" class="form-control" id="discount_type">
                                    <option value="amount">
                                            {{ translate('messages.amount').' ('.\App\CentralLogics\Helpers::currency_symbol().')'  }}
                                    </option>
                                    <option value="percent"> {{ translate('messages.percent').' (%)' }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6" id="discount_div">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.discount')}} <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="1" value="0" max="999999999999.99" name="discount" id="discount" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6" id="max_discount_div">
                            <div class="form-group">
                                <label class="input-label" for="max_discount">{{translate('messages.max_discount')}}
                                    <span class="input-label-secondary" title="" data-toggle="tooltip" data-original-title="{{ translate('If left empty the field, no maximum limit will be applied.') }}">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <input type="number" step="0.01" min="0" value="" max="999999999999.99" name="max_discount" id="max_discount" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6" id="min_purchase_div">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.min_purchase')}}
                                    <span class="text-danger">*</span>
                                </label>
                                <input required id="min_purchase" type="number" step="0.01" name="min_purchase" value="0" min="1" max="999999999999.99" class="form-control"
                                    placeholder="100">
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
                    <form method="get">

                        <!-- Search -->
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input id="datatableSearch" type="search" name="search" class="form-control" placeholder="{{ translate('messages.Ex :_Search by title or code') }}" aria-label="{{translate('messages.search_here')}}">
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
                            {{ route('vendor.coupon.coupon_export', ['type' => 'excel', request()->getQueryString()]) }}
                                ">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="
                            {{ route('vendor.coupon.coupon_export', ['type' => 'csv', request()->getQueryString()]) }}">
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
                            <td>{{'-'}}</td>
                            @endif

                            <td>{{$coupon['start_date']}}</td>
                            <td>{{$coupon['expire_date']}}</td>
                            <td>
                                <label class="toggle-switch toggle-switch-sm" for="couponCheckbox{{$coupon->id}}">
                                    <input type="checkbox" data-url="{{route('vendor.coupon.status',[$coupon['id'],$coupon->status?0:1])}}" class="toggle-switch-input redirect-url" id="couponCheckbox{{$coupon->id}}" {{$coupon->status?'checked':''}}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </td>
                            <td>
                                <div class="btn--container justify-content-center">
                                    <button class="btn btn-sm btn--primary btn-outline-primary action-btn get-coupon-data"
                                    data-id="{{$coupon['id']}}"
                                    data-url="{{route('vendor.coupon.view',[$coupon['id']])}}"
                                    title="view_details">
                                        <i class="tio-invisible"></i>
                                    </button>

                                    <a class="btn btn-sm btn--primary btn-outline-primary action-btn" href="{{route('vendor.coupon.update',[$coupon['id']])}}" title="{{translate('messages.edit_coupon')}}"><i class="tio-edit"></i>
                                    </a>
                                    <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert" href="javascript:" data-id="coupon-{{$coupon['id']}}" data-message="{{ translate('Want_to_delete_this_coupon_?') }}" title="{{translate('messages.delete_coupon')}}"><i class="tio-delete-outlined"></i>
                                    </a>
                                    <form action="{{route('vendor.coupon.delete',[$coupon['id']])}}"
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

    <!-- Coupon Details -->
    <div class="modal fade cmn__quick" id="coupon__details">
        <div class="modal-dialog modal-dialog-centered modal-custom-sm" style="--modal-mxwidth: 570px;">
            <div class="modal-content coupon-details-wrap position-relative pb-sm-4">
                <div class="modal-header p-0">
                    <button type="button" class="close close-light-remove" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="tio-clear"></i></span>
                    </button>
                </div>
                <div id="data-view">  </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
<script>
    "use strict";





        $(document).on('click', '.get-coupon-data', function() {
            fetch_coupon_data($(this).data('url'));
        })

        function fetch_coupon_data(url) {
            $.ajax({
                url: url,
                type: "get",
                beforeSend: function() {
                    $('#data-view').empty();
                    $('#loading').show()
                },
                success: function(data) {
                    if(data.error === 'not_found'){
                        toastr.error(data.message, { CloseButton: true,ProgressBar: true });
                    } else {
                        $('#coupon__details').modal('show');
                        $("#data-view").append(data.view);
                    }
                },
                complete: function() {
                    $('#loading').hide()
                }
            })
        }








    $("#date_from").on("change", function () {
        $('#date_to').attr('min',$(this).val());
    });

    $("#date_to").on("change", function () {
        $('#date_from').attr('max',$(this).val());
    });

    $(document).on('ready', function () {
        $('#discount_type').on('change', function() {
         if($('#discount_type').val() == 'amount')
            {
                $('#max_discount').attr("readonly","true");
                $('#max_discount').val("").trigger("change");
            }
            else
            {
                $('#max_discount').removeAttr("readonly");
            }
        });

        $('#date_from').attr('min',(new Date()).toISOString().split('T')[0]);
        $('#date_to').attr('min',(new Date()).toISOString().split('T')[0]);

            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'), {
                select: {
                    style: 'multi',
                    classMap: {
                        checkAll: '#datatableCheckAll',
                        counter: '#datatableCounter',
                        counterInfo: '#datatableCounterInfo'
                    }
                },
                language: {
                    zeroRecords: '<div class="text-center p-4">' +
                    '<img class="w-7rem mb-3" src="{{dynamicAsset('assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description">' +
                    '<p class="mb-0">{{ translate('No_data_to_show') }}</p>' +
                    '</div>'
                }
            });

            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });

        $(".coupon_type_change").on("change", function () {
            let coupon_type = $(this).val();
            if(coupon_type=='first_order')
            {
                $('#coupon_limit').val(1);
                $('#coupon_limit').attr("readonly","true");
                $('#customer_wise').hide();
            }
            else{
                $('#coupon_limit').val('');
                $('#coupon_limit').removeAttr("readonly");
                $('#customer_wise').show();
            }

            if(coupon_type=='free_delivery')
            {
                $('#discount_type').attr("disabled","true");
                $('#discount_type_div').hide();
                $('#discount_type').val("").trigger( "change" );

                $('#discount_div').hide();
                $('#discount').val(0);
                $('#discount').attr("readonly","true");

                $('#max_discount_div').hide();
                $('#max_discount').val("").trigger("change");
                $('#max_discount').attr("readonly","true");

                $('#min_purchase_div').hide();
                $('#min_purchase').val("").trigger("change");
                $('#min_purchase').attr("readonly","true");
                
            }
            else{

                $('#max_discount').removeAttr("readonly");
                $('#max_discount').val("").trigger("change");
                $('#max_discount_div').show();

                $('#discount_type').removeAttr("disabled");
                $('#discount_type').val("amount").trigger( "change" );
                $('#discount_type_div').show();

                $('#discount_div').show();
                $('#discount').removeAttr("readonly");
                $('#discount').val(0);

                $('#discount_type').attr("required","true");

                $('#min_purchase_div').show();
                $('#min_purchase').removeAttr("readonly");
                $('#min_purchase').val(0);
            }
        })

        $('#dataSearch').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('vendor.coupon.search')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#table-div').html(data.view);
                    $('#itemCount').html(data.count);
                    $('.page-area').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });

        $('#reset_btn').click(function(){
            $('.remove-data').val('');
            $('#coupon_title').val('');
            $('#coupon_code').val(null);
            $('#coupon_limit').val(null);
            $('#date_from').val(null);
            $('#date_to').val(null);
            $('#discount_type').val('amount');
            $('#discount').val(null);
            $('#max_discount').val(0);
            $('#min_purchase').val(0);
            $('#select_customer').val(null).trigger('change');
        })

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
                url: '{{ route('vendor.coupon.check.code') }}',
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
