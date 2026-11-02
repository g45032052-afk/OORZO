@extends('layouts.vendor.app')

@section('title', translate('addon_update'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title"><i class="tio-edit"></i> {{translate('messages.addon_update')}}</h1>
        </div>
        <div class="card">
            <div class="card-body">
                <!-- End Page Header -->
                <div class="row gx-2 gx-lg-3">
                    <div class="col-sm-12 col-lg-12">
                        <form action="{{route('vendor.addon.update',[$addon['id']])}}" method="post" class="row">
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
                            @endif

                            @if ($language)
                                <div class="form-group lang_form col-lg-4 col-md-6" id="default-form">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}}</label>
                                    <input type="text" name="name[]" class="form-control" placeholder="{{translate('messages.new_addon')}}" value="{{ $addon?->getRawOriginal('name')}}" required maxlength="191">
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                    @foreach(json_decode($language) as $lang)
                                    <?php
                                        if(count($addon['translations'])){
                                            $translate = [];
                                            foreach($addon['translations'] as $t)
                                            {
                                                if($t->locale == $lang && $t->key=="name"){
                                                    $translate[$lang]['name'] = $t->value;
                                                }
                                            }
                                        }
                                    ?>
                                    <div class="col-lg-4 col-md-6 form-group d-none lang_form" id="{{$lang}}-form">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}} ({{strtoupper($lang)}})</label>
                                        <input type="text" name="name[]" class="form-control" placeholder="{{translate('messages.new_addon')}}" maxlength="191" value="{{$translate[$lang]['name']??''}}" {{$lang == $default_lang? 'required':''}}  >
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                    @endforeach
                                    @else
                                        <div class="form-group lang_form col-lg-4 col-md-6" id="default-form">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}}</label>
                                            <input type="text" name="name[]" class="form-control" placeholder="{{translate('messages.new_addon')}}" value="{{ $addon['name'] }}" required maxlength="191">
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    @endif

                                    {{-- <div class="form-group col-lg-4 col-md-6">
                                        <label class="input-label" for="">{{translate('messages.Restaurant')}}<span class="text-danger">*</span></label>
                                        <select name="" class="form-control js-select2-custom" required>
                                            <option value="" selected>
                                                Select Restaurant
                                            </option>
                                            <option value="Restaurant1">
                                                Restaurant1
                                            </option>
                                            <option value="Restaurant2">
                                                Restaurant2
                                            </option>
                                        </select>
                                    </div> --}}
                                    <div class="form-group col-lg-4 col-md-6">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.price')}}</label>
                                        <input type="number" min="0" max="999999999999.99" step="0.01" name="price" value="{{$addon['price']}}" class="form-control" placeholder="200" required>
                                    </div>

                                    <div class="form-group col-lg-4 col-md-6">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ translate('messages.Stock_Type') }}
                                        </label>
                                        <select name="stock_type" id="stock_type" class="form-control js-select2-custom">
                                            <option  {{$addon['stock_type'] == 'unlimited' ? 'selected':'' }}  value="unlimited">{{ translate('messages.Unlimited_Stock') }}</option>
                                            <option {{$addon['stock_type'] == 'limited' ? 'selected' : '' }} value="limited">{{ translate('messages.Limited_Stock')  }}</option>
                                            <option {{$addon['stock_type'] == 'daily' ? 'selected' : '' }} value="daily">{{ translate('messages.Daily_Stock')  }}</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-lg-4 col-md-6 hide_this">
                                        <label class="input-label" for="addon_stock">{{translate('messages.Addon_Stock')}}</label>
                                        <input type="number" min="0" id="addon_stock" max="999999999999" name="addon_stock"   {{$addon['stock_type'] == 'unlimited' ? 'readonly':'' }} placeholder="{{$addon['stock_type'] == 'unlimited' ? translate('Unlimited') : translate('messages.Ex:_100')  }}"  value="{{$addon['stock_type'] == 'unlimited' ? '':$addon['addon_stock']  }}" class="form-control stock_disable"  >
                                    </div>

                                    <div class="col-lg-4 col-md-6">
                                        <div class="form-group">
                                            <span class="mb-2 d-block title-clr fw-normal">{{ translate('Category') }}</span>
                                            <select name="category_id" required class="form-control js-select2-custom1"
                                                placeholder="Select Category">
                                                <option selected disabled value=""> {{ translate('messages.select_category') }}
                                                </option>
                                                @foreach ($addonCategories as $addonCategory)
                                                    <option {{ $addonCategory->id == $addon->addon_category_id ? 'selected' : '' }}
                                                        value="{{ $addonCategory->id }}"> {{ $addonCategory->name }}
                                                    </option>
                                                @endforeach
                                            </select>

                                        </div>
                                    </div>

                            @if ($productWiseTax)
                                <div class="col-lg-4 col-md-6">
                                    <span class="mb-2 d-block title-clr fw-normal">{{ translate('Select Tax Rate') }}</span>
                                    <select name="tax_ids[]" required id="" class="form-control multiple-select2"
                                        multiple="multiple" placeholder="{{translate('--Select Tax Rate--')}}">
                                        @foreach ($taxVats as $taxVat)
                                            <option {{ in_array($taxVat->id, $taxVatIds) ? 'selected' : '' }}
                                                value="{{ $taxVat->id }}"> {{ $taxVat->name }}
                                                ({{ $taxVat->tax_rate }}%)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif


                            <div class="col-12 mt-4">
                                <div class="btn--container justify-content-end">
                                    <button type="reset" id="reset_btn" class="btn btn--reset">{{translate('messages.reset')}}</button>
                                    <button type="submit" class="btn btn--primary">{{translate('messages.update')}}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- End Table -->
                </div>
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
        function stock_type(data){
            if(data == 'unlimited') {
                    $('.stock_disable').prop('readonly', true).prop('required', false).attr('placeholder', '{{ translate('Unlimited') }}').val('');
                     $('.hide_this').addClass('d-none');
                } else {
                    $('.stock_disable').prop('readonly', false).prop('required', true).attr('placeholder', '{{ translate('messages.Ex:_100') }}').val('{{$addon['addon_stock']}}');
                     $('.hide_this').removeClass('d-none');
                }
        }

        $('#reset_btn').click(function(){
            location.reload();  

        })
</script>
@endpush
