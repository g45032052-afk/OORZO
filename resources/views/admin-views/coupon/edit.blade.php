@extends('layouts.admin.app')

@section('title',translate('Coupon_Update'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-edit"></i> {{translate('messages.Coupon_Update')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.coupon.update',[$coupon['id']])}}" method="post">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                          @php($language = \App\CentralLogics\Helpers::get_business_settings('language'))

                                <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                <ul class="nav nav-tabs mb-4">
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
                                </div>
                                <div class="lang_form" id="default-form">
                                    <div class="form-group mb-1">
                                        <label class="input-label" for="default_title">{{translate('messages.title')}} ({{translate('messages.default')}}) <span class="text-danger">*</span></label>
                                        <input type="text"  name="title[]" id="default_title" class="form-control" placeholder="{{translate('messages.new_coupon')}}" value="{{$coupon->getRawOriginal('title')}}"  >
                                    </div>
                                    <input type="hidden" name="lang[]" value="default">
                                </div>
                                @foreach($language as $lang)
                                    <?php
                                        if(count($coupon['translations'])){
                                            $translate = [];
                                            foreach($coupon['translations'] as $t)
                                            {
                                                if($t->locale == $lang && $t->key=="title"){
                                                    $translate[$lang]['title'] = $t->value;
                                                }
                                            }
                                        }
                                    ?>
                                    <div class="d-none lang_form" id="{{$lang}}-form">
                                        <div class="form-group mb-1">
                                            <label class="input-label" for="{{$lang}}_title">{{translate('messages.title')}} ({{strtoupper($lang)}})</label>
                                            <input type="text" name="title[]" id="{{$lang}}_title" class="form-control" placeholder="{{translate('messages.new_coupon')}}" value="{{$translate[$lang]['title']??''}}"  >
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{$lang}}">
                                    </div>
                                @endforeach

                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group mb-1">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.coupon_type')}} <span class="text-danger">*</span></label>
                                <select id="coupon_type" name="coupon_type" class="form-control">
                                    <option value="restaurant_wise" {{$coupon['coupon_type']=='restaurant_wise'?'selected':''}}>{{translate('messages.restaurant_wise')}}</option>
                                    <option value="zone_wise" {{$coupon['coupon_type']=='zone_wise'?'selected':''}}>{{translate('messages.zone_wise')}}</option>
                                    <option value="free_delivery" {{$coupon['coupon_type']=='free_delivery'?'selected':''}}>{{translate('messages.free_delivery')}}</option>
                                    <option value="first_order" {{$coupon['coupon_type']=='first_order'?'selected':''}}>{{translate('messages.first_order')}}</option>
                                    @if (\App\CentralLogics\Helpers::get_business_settings('pro_member_status') == 1 || $coupon['coupon_type'] == 'pro_customer')
                                        <option value="pro_customer" {{$coupon['coupon_type']=='pro_customer'?'selected':''}}>{{translate('messages.pro_customer')}}</option>
                                    @endif
                                    <option value="default" {{$coupon['coupon_type']=='default'?'selected':''}}>{{translate('messages.default')}}</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group col-sm-6 col-lg-3 mb-0" id="restaurant_wise" style="display: {{$coupon['coupon_type']=='restaurant_wise'?'block':'none'}}">
                            <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.restaurant')}} <span class="text-danger">*</span></label>
                            <select name="restaurant_ids[]" class="js-data-example-ajax form-control"  title="Select Restaurant">
                            @if($coupon->coupon_type == 'restaurant_wise')
                            @php($restaurant=\App\Models\Restaurant::find(json_decode($coupon->data)[0]))
                                @if($restaurant)
                                <option value="{{$restaurant->id}}" data-verified="{{ (int) $restaurant->verified_seller }}">{{$restaurant->name}}</option>
                                @endif
                            @else
                            <option selected>{{translate('select_restaurant')}}</option>
                            @endif
                            </select>
                        </div>
                        <div class="form-group counter__unique-wrap col-sm-6 col-lg-3 mb-0" id="zone_wise" style="display: {{$coupon['coupon_type']=='zone_wise'?'block':'none'}}">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('messages.select_zone')}} <span class="text-danger">*</span></label>
                            <select name="zone_ids[]" id="choice_zones"
                                class="form-control js-select2-custom"
                                multiple="multiple" placeholder="{{translate('messages.select_zone')}}">
                            @foreach(\App\Models\Zone::where('status',1)->get(['id','name']) as $zone)
                                <option value="{{$zone->id}}" {{($coupon->coupon_type=='zone_wise'&&json_decode($coupon->data))?(in_array($zone->id, json_decode($coupon->data))?'selected':''):''}}>{{$zone->name}}</option>
                            @endforeach
                            </select>
                        </div>
                        <?php
                        $customer_ids = json_decode($coupon->customer_id, true);
                        $preSelectedCustomers = [];

                        if (is_array($customer_ids) && in_array('all', $customer_ids)) {
                            $preSelectedCustomers[] = ['id' => 'all', 'text' => translate('messages.all')];
                        } else {
                            $preSelectedCustomers = App\Models\User::whereIn('id', $customer_ids)
                                ->selectRaw('id, CONCAT(f_name, " ", l_name, " (", phone, ")") as text')
                                ->get();
                        }
                        ?>
                        <div class="col-sm-6 col-lg-3" style="display: {{in_array($coupon['coupon_type'], ['zone_wise','first_order','pro_customer']) ?'none':'block'}}" >
                            <div class="form-group counter__unique-wrap mb-1" id="customer_wise" >
                                <label class="input-label" for="select_customer">{{translate('messages.select_customer')}}  <span class="text-danger">*</span></label>
                                <select name="customer_ids[]" id="select_customer"
                                        data-placeholder="{{ translate('messages.select_customer') }}"
                                        multiple class="form-control js-data-example-ajax-2">
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group mb-1">
                                <div class="d-flex justify-content-between">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.code')}}  <span class="text-danger">*</span></label>
                                    <label class="input-label generate-code" id="generate_code"><i class="tio-hand-draw"></i>{{translate('messages.Generate Code')}}</label>
                                </div>
                                <input id="coupon_code" type="text" name="code" class="form-control" value="{{$coupon['code']}}"
                                       placeholder="{{\Illuminate\Support\Str::random(8)}}" required maxlength="100">
                            </div>
                        </div>
                        <div id="limit_for_same_user" class="col-sm-6 col-lg-3">
                            <div  class="form-group mb-1">
                                <label class="input-label" for="limit">{{translate('messages.limit_for_same_user')}}  <span class="text-danger">*</span></label>
                                <input type="number" name="limit" id="coupon_limit" value="{{$coupon['limit']}}" class="form-control" max="100"
                                        placeholder="{{ translate('messages.Ex:_100') }}">
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group mb-1">
                                <label class="input-label" for="">{{translate('messages.start_date')}} <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" id="date_from" placeholder="{{translate('messages.select_date')}}" value="{{date('Y-m-d',strtotime($coupon['start_date']))}}">
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group mb-1">
                                <label class="input-label" for="date_to">{{translate('messages.expire_date')}} <span class="text-danger">*</span></label>
                                <input type="date" name="expire_date" class="form-control" placeholder="{{translate('messages.select_date')}}" id="date_to" value="{{date('Y-m-d',strtotime($coupon['expire_date']))}}"
                                        data-hs-flatpickr-options='{
                                        "dateFormat": "Y-m-d"
                                    }'>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group mb-1">
                                <label class="input-label" for="discount_type">{{translate('messages.discount_type')}} <span class="text-danger">*</span></label>
                                <select name="discount_type"  required id="discount_type" class="form-control" {{$coupon['coupon_type']=='free_delivery'?'disabled':''}}>
                                    <option value="amount" {{$coupon['discount_type']=='amount'?'selected':''}}>
                                        {{ translate('messages.amount').' ('.\App\CentralLogics\Helpers::currency_symbol().')'  }}

                                    </option>
                                    <option value="percent" {{$coupon['discount_type']=='percent'?'selected':''}}>
                                       {{ translate('messages.percent').' (%)' }}
                                    </option>
                                </select>
                            </div>
                        </div>
                            <div class="col-sm-6 col-lg-3">
                            <div class="form-group mb-1">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.min_purchase')}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})<span class="text-danger">*</span></label>
                                <input id="min_purchase" type="number" name="min_purchase" step="0.01" value="{{$coupon['min_purchase']}}"
                                        min="0" max="999999999999.99" class="form-control"
                                        placeholder="100">
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group mb-1">
                                <label class="input-label" for="discount">{{translate('messages.discount')}}    <span class="text-danger">*</span>
                                </label>
                                <input type="number" id="discount" min="1" max="999999999999.99" step="0.01" value="{{$coupon['discount']}}"
                                        name="discount" class="form-control" required {{$coupon['coupon_type']=='free_delivery'?'readonly':''}}>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group mb-1">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.max_discount')}} ({{ \App\CentralLogics\Helpers::currency_symbol() }}) <span class="text-danger">*</span></label>
                                <input type="number" min="0" max="999999999999.99" step="0.01"
                                        value="{{$coupon['max_discount']}}" name="max_discount" id="max_discount" class="form-control" {{$coupon['coupon_type']=='free_delivery' || $coupon['discount_type']=='amount' ?'readonly':''}}>
                            </div>
                        </div>

                    </div>
                    <div class="btn--container mt-3 justify-content-end">
                        <button id="reset_btn" type="button" class="btn btn--reset">{{translate('messages.reset')}}</button>
                        <button type="submit" class="btn btn--primary">{{translate('messages.update')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{dynamicAsset('assets/admin/js/view-pages/coupon-index.js')}}"></script>
    <script>
        "use strict";

        $(document).on('ready', function () {
            $('#date_from').attr('max','{{date("Y-m-d",strtotime($coupon["expire_date"]))}}');
            $('#date_to').attr('min','{{date("Y-m-d",strtotime($coupon["start_date"]))}}');
            $('.js-data-example-ajax').select2({
                ajax: {
                    url: '{{ route('admin.restaurant.get-restaurants')  }}',
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
                        var $request = $.ajax(params);

                        $request.then(success);
                        $request.fail(failure);

                        return $request;
                    }
                }
            });
            // INITIALIZATION OF FLATPICKR
            // =======================================================
            $('.js-flatpickr').each(function () {
                $.HSCore.components.HSFlatpickr.init($(this));
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

        $(document).ready(function () {
            let $select = $('.js-data-example-ajax-2');
            const preSelectedCustomers = @json($preSelectedCustomers);

            if (typeof preSelectedCustomers !== 'undefined') {
                preSelectedCustomers.forEach(function (customer) {
                    let option = new Option(customer.text, customer.id, true, true);
                    $select.append(option);
                });
                $select.trigger('change');
            }

            $select.select2({
                ajax: {
                    url: '{{ route('admin.customer.select-list') }}',
                    data: function(params) {
                        return {
                            q: params.term,
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


    </script>
@endpush
