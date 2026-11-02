@extends('layouts.admin.app')

@section('title',translate('Add_new_campaign'))

@push('css_or_js')
    <link href="{{dynamicAsset('assets/admin/css/tags-input.min.css')}}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title fs-24">
                        {{translate('messages.Add_new_campaign')}}
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <form action="javascript:" method="post" id="campaign_form" enctype="multipart/form-data">
            @csrf
            @php($language=\App\Models\BusinessSetting::where('key','language')->first())
            @php($language = $language->value ?? null)
            <div class="card mb-20">
                <div class="card-header">
                    <div>
                        <h3 class="mb-1">{{ translate('Food_Info') }}</h3>

                    </div>
                </div>
                <div class="card-body p-xxl-4 p-3">
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <div class="global-bg-box p-3 rounded">
                                @if($language)
                                <div class="js-nav-scroller mb-4 tabs-slide-language hs-nav-scroller-horizontal">
                                    <ul class="nav nav-tabs border-0">
                                        <li class="nav-item">
                                            <a class="nav-link lang_link active" href="#" id="default-link"> {{ translate('Default') }}</a>
                                        </li>
                                        @foreach(json_decode($language) as $lang)
                                            <li class="nav-item">
                                                <a class="nav-link lang_link" href="#" id="{{$lang}}-link">{{\App\CentralLogics\Helpers::get_language_name($lang).'('.strtoupper($lang).')'}}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <div class="arrow-area">
                                        <div class="button-prev align-items-center">
                                            <button type="button"
                                                class="btn btn-click-prev mr-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                                                <i class="tio-chevron-left fs-24"></i>
                                            </button>
                                        </div>
                                        <div class="button-next align-items-center">
                                            <button type="button"
                                                class="btn btn-click-next ml-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                                                <i class="tio-chevron-right fs-24"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if($language)
                                    <div class="mb-1 lang_form" id="default-form">
                                        <div class="form-group">
                                            <label class="input-label" for="default_title">{{translate('messages.title')}} ({{ translate('Default') }})  <span class="text-danger">*</span></label>
                                            <input type="text"  name="title[]" id="default_title" class="form-control" placeholder="{{translate('messages.new_campaign')}}"  >
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                        <div class="form-group mb-0">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('messages.short_description')}} ({{ translate('Default') }}) <span class="text-danger">*</span></label>
                                            <textarea type="text" rows="1" maxlength="100" name="description[]" class="form-control ckeditor"></textarea>
                                             <div class="d-flex justify-content-end">
                                                <span class="text-body-light text-right d-block mt-1">0/100</span>
                                            </div>
                                        </div>
                                    </div>
                                    @foreach(json_decode($language) as $lang)
                                        <div class="mb-1 d-none lang_form" id="{{$lang}}-form">
                                            <div class="form-group">
                                                <label class="input-label" for="{{$lang}}_title">{{translate('messages.title')}} ({{strtoupper($lang)}})</label>
                                                <input type="text"  name="title[]" id="{{$lang}}_title" class="form-control" placeholder="{{translate('messages.new_campaign')}}"  >
                                            </div>
                                            <input type="hidden" name="lang[]" value="{{$lang}}">
                                            <div class="form-group mb-0">
                                                <label class="input-label" for="exampleFormControlInput1">{{translate('messages.short_description')}} ({{strtoupper($lang)}})</label>
                                                <textarea type="text" rows="1" maxlength="100" name="description[]" class="form-control ckeditor"></textarea>
                                                <div class="d-flex justify-content-end">
                                                    <span class="text-body-light text-right d-block mt-1">0/100</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach


                                @endif
                            </div>
                        </div>
                        <div class="col-lg-4">

                            <div class="p-xxl-20 d-flex align-items-center justify-content-center p-12 global-bg-box text-center rounded h-100">
                                <div class="">
                                    <div class="mb-20 text-start">
                                        <h5 class="mb-1">
                                            {{ translate('Food Image') }}  <span class="text-danger">*</span>
                                        </h5>
                                        <p class="mb-0 fs-12 gray-dark">{{ translate('Upload food Image for campaign') }}</p>
                                    </div>
                                    <div class="upload-file  mx-auto">
                                        <input type="file" name="image" class="upload-file__input single_file_input"
                                                accept="{{IMAGE_EXTENSION}}" required>
                                        <label class="upload-file__wrapper ratio-1 m-0">
                                            <div class="upload-file-textbox text-center" >
                                                <img width="22" class="svg" src="{{dynamicAsset('assets/admin/img/image-upload.png')}}" alt="img">
                                                <h6 class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">
                                                    <span class="text-info">{{translate('Click to upload')}}</span>
                                                    <br>
                                                    {{translate('Or drag and drop')}}
                                                </h6>
                                            </div>
                                            <img class="upload-file-img" loading="lazy" src="" data-default-src="" alt="" style="display: none;">
                                        </label>
                                        <div class="overlay">
                                            <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                                <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                    <i class="tio-invisible"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                    <i class="tio-edit"></i>
                                                </button>
                                                <button type="button" class="remove_btn btn icon-btn">
                                                    <i class="tio-delete text-danger"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="fs-10 text-center mb-0 mt-20">
                                        {{ translate(IMAGE_FORMAT.' Less Than 2MB') }} <span class="font-medium text-title">{{ translate('(1:1)')}}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-20">
                <div class="card-header">
                    <h3 class="m-0">
                       {{translate('Food_Details')}}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="global-bg-box p-xxl-4 p-3 rounded">
                        <div class="row g-2">

                            <div class="col-sm-6 col-lg-4">
                                <div class="form-group mb-0">
                                    <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.restaurant')}} <span class="text-danger">*</span></label>
                                    <select name="restaurant_id" required id="restaurant_id" class="js-data-example-ajax form-control get-restaurant" data-url="{{url('/')}}/admin/restaurant/get-addons?data[]=0&restaurant_id=" data-id="add_on"  title="Select Restaurant" required>
                                    <option selected value="">{{translate('select_restaurant')}}</option>

                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <div class="form-group mb-0">
                                    @php($categories=\App\Models\Category::where(['position' => 0])->get(['id','name']))

                                    <label class="input-label"
                                        for="exampleFormControlSelect1">{{ translate('messages.category') }} <span class="text-danger">*</span></label>
                                    <select name="category_id" required id="category_id"
                                        class="form-control js-select2-custom get-request"
                                        >
                                        <option value="" selected disabled>
                                            {{ translate('Select_Category') }}</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category['id'] }}">{{ $category['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <div class="form-group mb-0">
                                    <label class="input-label"
                                        for="exampleFormControlSelect1">{{ translate('messages.sub_category') }} <span
                                            class="input-label-secondary" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('messages.category_required_warning') }}"><i class="tio-info text-gray1 fs-14"></i></span></label>
                                    <select name="sub_category_id" id="sub-categories"
                                        class="form-control js-select2-custom">
                                        <option value="" selected disabled>
                                            {{ translate('Select_Sub_Category') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <div class="form-group mb-0">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.item_type')}}  <span class="text-danger">*</span></label>
                                    <select name="veg" id="item_type" required class="form-control js-select2-custom">
                                        <option value="0" selected>{{translate('messages.non_veg')}}</option>
                                        <option value="1">{{translate('messages.veg')}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <div class="form-group mb-0">
                                    <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.addon')}}
                                        <span class="input-label-secondary" data-toggle="tooltip"
                                            data-placement="right" title="{{translate('messages.Make_sure_you_have_selected_a_restaurant_first_!')}}">
                                            <i class="tio-info text-gray1 fs-14"></i>
                                        </span>
                                    </label>
                                    <select name="addon_ids[]" id="add_on" class="form-control multiple-select2" multiple="multiple">
                                    </select>
                                </div>
                            </div>



                            @php($product_nutritions = isset($product) ? $product->nutritions->pluck('id') : null)
                            @php($product_allergies = isset($product) ? $product->allergies->pluck('id') : null)


                            <div class="col-sm-6 col-lg-4" id="nutrition">
                                <label class="input-label" for="sub-categories">
                                    {{ translate('Nutrition') }}
                                    <span class="input-label-secondary"
                                        title="{{ translate('Specify the necessary keywords relating to energy values for the item and type this content & press enter.') }}"
                                        data-toggle="tooltip">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <select name="nutritions[]" class="form-control multiple-select2" id="nutritions_input"
                                    data-placeholder="{{ translate('messages.Type your content and  press enter') }}" multiple>
                                    @php($nutritions = \App\Models\Nutrition::select(['id','nutrition'])->get() ?? [])

                                    @foreach ($nutritions as $nutrition)
                                        <option
                                            {{ $product_nutritions && $product_nutritions->contains($nutrition->id) ? 'selected' : '' }}
                                            value="{{ $nutrition->nutrition }}">{{ $nutrition->nutrition }}</option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-sm-6 col-lg-4" id="allergy">
                                <label class="input-label" for="sub-categories">
                                    {{ translate('Allegren Ingredients') }}
                                    <span class="input-label-secondary"
                                        title="{{ translate('Specify the ingredients of the item which can make a reaction as an allergen and type this content & press enter.') }}"
                                        data-toggle="tooltip">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <select name="allergies[]" class="form-control multiple-select2" id="allergy_input"
                                    data-placeholder="{{ translate('messages.Type your content and  press enter') }}" multiple>
                                    @php($allergies = \App\Models\Allergy::select(['id','allergy'])->get() ?? [])

                                    @foreach ($allergies as $allergy)
                                        <option
                                            {{ $product_allergies && $product_allergies->contains($allergy->id) ? 'selected' : '' }}
                                            value="{{ $allergy->allergy }}">{{ $allergy->allergy }}</option>
                                    @endforeach
                                </select>
                            </div>


                            {{-- <div class="col-sm-6 col-lg-3" id="halal">
                                <div class="form-check mb-0 p-4">
                                    <input class="form-check-input" name="is_halal" type="checkbox" value="1"
                                        id="is_halal" {{ isset($product) && $product?->is_halal == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_halal">
                                        {{ translate('messages.Is_It_Halal') }}
                                    </label>
                                </div>
                            </div> --}}


                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-20">
                <div class="card-header">
                    <h5 class="m-0">
                       {{translate('amount')}}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="global-bg-box rounded p-xxl-4 p-3">
                        <div class="row g-2">
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group mb-0">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.price')}}  <span class="text-danger">*</span></label>
                                    <input type="number" min=".001" max="999999999999.99" step="0.001" value="1" name="price" class="form-control"
                                            placeholder="{{ translate('messages.Ex_:_100') }}" required>
                                </div>
                            </div>
                            @if ($productWiseTax)
                                <div class="col-lg-4 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('Select Tax Rate')}} (%)  <span class="text-danger">*</span></label>
                                        <select name="tax_ids[]" required id="tax__rate" class="form-control multiple-select2"  multiple="multiple" placeholder="{{ translate('Type & Select Tax Rate') }}">
                                            @foreach ($taxVats as $taxVat)
                                                <option value="{{ $taxVat->id }}"> {{ $taxVat->name }}
                                                    ({{ $taxVat->tax_rate }}%)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                            <div class="col-lg-4 col-sm-6">
                                <div class="form-group mb-0">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.discount')}}
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ translate('Currently_you_need_to_manage_discount_with_the_Restaurant.') }}">
                                        <i class="tio-info text-gray1 fs-14"></i>
                                    </span>  <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-flex align-items-center border rounded overflow-hidden">
                                        <input type="number" min="0" max="9999999" step=".001" value="0" name="discount" class="form-control border-0 outline-0"
                                                placeholder="{{ translate('messages.Ex_:_100') }}" >
                                        <select name="discount_type" class="w-auto border-0 rounded-0 custom-select section-bg1">
                                            <option value="percent">{{translate('messages.percent').' (%)'}}</option>
                                            <option value="amount">{{translate('messages.amount').' ('.\App\CentralLogics\Helpers::currency_symbol().')' }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-lg-4" id="maximum_cart_quantity">
                                <div class="form-group mb-0">
                                    <label class="input-label"
                                        for="maximum_cart_quantity">{{ translate('messages.maximum_cart_quantity') }}</label>
                                    <input type="number" class="form-control" name="maximum_cart_quantity" min="0" id="cart_quantity">
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-20">
                <div class="card-header border-0 flex-wrap gap-2">
                    <h3 class="m-0">
                       {{ translate('messages.food_variations') }}
                    </h3>
                    <a class="theme-clr cursor-pointer fs-12 font-weight-bold" id="add_new_option_button">
                        <i class="tio-add-circle-outlined"></i> {{ translate('add_variation') }}
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="row g-2">
                        <div class="col-md-12">
                            <div id="add_new_option">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-20">
                <div class="card-header">
                    <h5 class="m-0">
                      {{translate('Time_Schedule')}}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="global-bg-box rounded p-xxl-4 p-3">
                        <div class="row g-2">
                            <div class="col-lg-3 col-sm-6">
                                <div class="form-group m-0">
                                    <label class="input-label" for="title">{{translate('messages.start_date')}}  <span class="text-danger">*</span></label>
                                    <input type="date" id="date_from" class="form-control" required name="start_date">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6">
                                <div class="form-group m-0">
                                    <label class="input-label" for="title">{{translate('messages.end_date')}}  <span class="text-danger">*</span></label>
                                    <input type="date" id="date_to" class="form-control" required name="end_date">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6">
                                <div class="form-group m-0">
                                    <label class="input-label" for="title">{{translate('messages.start_time')}}  <span class="text-danger">*</span></label>
                                    <input type="time" id="start_time" class="form-control" required name="start_time">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6">
                                <div class="form-group m-0">
                                    <label class="input-label" for="title">{{translate('messages.end_time')}}  <span class="text-danger">*</span></label>
                                    <input type="time" id="end_time" class="form-control"  required name="end_time">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-2">
                <div class="col-12">
                </div>
                <div class="col-12">

                </div>
                <div class="col-12">
                </div>

            </div>

            <div class="btn--container justify-content-end mt-3">
                <button type="reset" id="reset_btn" class="btn btn--reset">{{translate('messages.reset')}}</button>
                <button type="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
            </div>
        </form>
    </div>

@endsection

@push('script_2')
    <script src="{{dynamicAsset('assets/admin')}}/js/tags-input.min.js"></script>


    <script>
        "use strict";
        let count= 0;
        let element = "";
        let countRow = 0;
        $(document).ready(function(){
            $("#add_new_option_button").click(function(e) {
                count++;
                var add_option_view = `
                <div class="card view_new_option m-3" >
                    <div class="card-header">
                        <label for="" id=new_option_name_` + count + `> {{ translate('add_new') }}</label>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-lg-3 col-md-6">
                                <label for="">{{ translate('name') }} <span class="text-danger">*</span>    </label>
                                <input required name=options[` + count +
                    `][name] class="form-control new_option_name" type="text" data-count="`+
                    count +`">
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="form-group">
                                    <label class="input-label text-capitalize d-flex alig-items-center"><span class="line--limit-1">{{ translate('messages.selcetion_type') }}  </span> <span class="text-danger">*</span>
                                    </label>
                                    <div class="resturant-type-group border">
                                        <label class="form-check form--check mr-2 mr-md-4">
                                            <input class="form-check-input show_min_max" data-count="`+count+`" type="radio" value="multi"
                                            name="options[` + count + `][type]" id="type` + count + `" checked">
                                            <span class="form-check-label">
                                                {{ translate('Multiple') }}
                    </span>
                </label>

                <label class="form-check form--check mr-2 mr-md-4">
                    <input class="form-check-input hide_min_max" data-count="`+count+`" type="radio" value="single"
                                            name="options[` + count + `][type]" id="type` + count + `">
                                            <span class="form-check-label">
                                                {{ translate('Single') }}
                    </span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="row g-2">
            <div class="col-sm-6 col-md-4">
                <label for="">{{ translate('Min') }}</label>
                                        <input id="min_max1_` + count + `" required  name="options[` + count + `][min]" class="form-control" type="number" min="1">
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <label for="">{{ translate('Max') }}</label>
                                        <input id="min_max2_` + count + `"   required name="options[` + count + `][max]" class="form-control" type="number" min="1">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="d-md-block d-none">&nbsp;</label>
                                            <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <input id="options[` + count + `][required]" name="options[` +
                    count + `][required]" type="checkbox">
                                                <label for="options[` + count + `][required]" class="m-0">{{ translate('Required') }}</label>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-danger btn-sm delete_input_button"
                                                    title="{{ translate('Delete') }}">
                                                    <i class="tio-add-to-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="option_price_` + count + `" >
                            <div class="border rounded p-3 pb-0 mt-3">
                                <div  id="option_price_view_` + count + `">
                                    <div class="row g-3 add_new_view_row_class mb-3">
                                        <div class="col-md-4 col-sm-6">
                                            <label for="">{{ translate('Option_name') }}  <span class="text-danger">*</span></label>
                                            <input class="form-control" required type="text" name="options[` +
                    count +
                    `][values][0][label]" id="">
                                        </div>
                                        <div class="col-md-4 col-sm-6">
                                            <label for="">{{ translate('Additional_price') }}  <span class="text-danger">*</span></label>
                                            <input class="form-control" required type="number" min="0" step="0.01" name="options[` +
                    count + `][values][0][optionPrice]" id="">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3 p-3 mr-1 d-flex "  id="add_new_button_` + count +
                    `">
                                    <button type="button" class="btn btn-outline-primary add_new_row_button" data-count="`+
                    count +`" >{{ translate('Add_New_Option') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;

                $("#add_new_option").append(add_option_view);
            });
        });
        function show_min_max(data){
            $('#min_max1_'+data).removeAttr("readonly");
            $('#min_max2_'+data).removeAttr("readonly");
            $('#min_max1_'+data).attr("required","true");
            $('#min_max2_'+data).attr("required","true");
        }
        function hide_min_max (data){
            $('#min_max1_'+data).val(null).trigger('change');
            $('#min_max2_'+data).val(null).trigger('change');
            $('#min_max1_'+data).attr("readonly","true");
            $('#min_max2_'+data).attr("readonly","true");
            $('#min_max1_'+data).attr("required","false");
            $('#min_max2_'+data).attr("required","false");
        }

        $(document).on('change', '.show_min_max', function () {
            let data = $(this).data('count');
            show_min_max(data);
        });

        $(document).on('change', '.hide_min_max', function () {
            let data = $(this).data('count');
            hide_min_max(data);
        });

        function new_option_name(value,data)
        {
            $("#new_option_name_"+data).empty();
            $("#new_option_name_"+data).text(value)
            console.log(value);
        }
        function removeOption(e)
        {
            element = $(e);
            element.parents('.view_new_option').remove();
        }
        function deleteRow(e)
        {
            element = $(e);
            element.parents('.add_new_view_row_class').remove();
        }

        $(document).on('click', '.delete_input_button', function () {
            let e = $(this);
            removeOption(e);
        });


        $(document).on('click', '.deleteRow', function () {
            let e = $(this);
            deleteRow(e);
        });

        $(document).on('keyup', '.new_option_name', function () {
            let data = $(this).data('count');
            let value = $(this).val();
            new_option_name(value, data);
        });


        function add_new_row_button(data) {
            count = data;
            countRow = 1 + $('#option_price_view_' + data).children('.add_new_view_row_class').length;
            var add_new_row_view = `
        <div class="row add_new_view_row_class mb-3 position-relative pt-3 pt-sm-0">
            <div class="col-md-4 col-sm-5">
                    <label for="">{{ translate('Option_name') }}  <span class="text-danger">*</span></label>
                    <input class="form-control" required type="text" name="options[` + count + `][values][` +
                countRow + `][label]" id="">
                </div>
                <div class="col-md-4 col-sm-5">
                    <label for="">{{ translate('Additional_price') }}  <span class="text-danger">*</span></label>
                    <input class="form-control"  required type="number" min="0" step="0.01" name="options[` +
                count +
                `][values][` + countRow + `][optionPrice]" id="">
                </div>
                <div class="col-sm-2 max-sm-absolute">
                    <label class="d-none d-sm-block">&nbsp;</label>
                    <div class="mt-1">
                        <button type="button" class="btn btn-danger btn-sm deleteRow"
                            title="{{ translate('Delete') }}">
                            <i class="tio-add-to-trash"></i>
                        </button>
                    </div>
            </div>
        </div>`;
            $('#option_price_view_' + data).append(add_new_row_view);

        }

        $(document).on('click', '.add_new_row_button', function () {
            let data = $(this).data('count');
            add_new_row_button(data);
        });



        $('.get-restaurant').on('change',function (){
            let route = '{{url('/')}}/admin/restaurant/get-addons?data[]=0&restaurant_id='+$(this).val();
            let id = $(this).data('id');
            console.log(id);
            getRestaurantData(route, id);
        })

        function getRestaurantData(route, id) {
            $.get({
                url: route,
                dataType: 'json',
                success: function (data) {
                    $('#' + id).empty().append(data.options);
                },
            });
            $.get({
                url:route,
                dataType: 'json',
                success: function(data) {
                    if(data.available_time_starts != null && data.available_time_ends != null){
                        let opening_time = data.available_time_starts;
                        let closeing_time = data.available_time_ends;
                        $('#available_time_ends').attr('min', opening_time);
                        $('#available_time_starts').attr('min', opening_time);
                        $('#available_time_ends').attr('max', closeing_time);
                        $('#available_time_starts').attr('max', closeing_time);
                        $('#available_time_starts').val(opening_time);
                        $('#available_time_ends').val(closeing_time);
                    }
                },
            });
        }






        $('.get-request').on('change',function (){
            let route = $(this).data('url')+$(this).val;
            let id = $(this).data('id');
            getRequest(route, id);
        })
        function getRequest(route, id) {
            $.get({
                url: route,
                dataType: 'json',
                success: function (data) {
                    $('#' + id).empty().append(data.options);
                },
            });
        }
        function readURL(input) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this);
        });


        function show_item(type) {
            if (type === 'product') {
                $("#type-product").show();
                $("#type-category").hide();
            } else {
                $("#type-product").hide();
                $("#type-category").show();
            }
        }
        $("#date_from").on("change", function () {
            $('#date_to').attr('min',$(this).val());
        });

        $("#date_to").on("change", function () {
            $('#date_from').attr('max',$(this).val());
        });


        $(document).ready(function(){
            $('#date_from').attr('min',(new Date()).toISOString().split('T')[0]);
            $('#date_to').attr('min',(new Date()).toISOString().split('T')[0]);
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
            let zone_id = [];
            $('#zone').on('change', function(){
                if($(this).val())
                {
                    zone_id = [$(this).val()];
                }
                else
                {
                    zone_id = [];
                }
            });


            $('.js-data-example-ajax').select2({
                ajax: {
                    url: '{{url('/')}}/admin/restaurant/get-restaurants',
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            zone_ids: zone_id,
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

        $('#campaign_form').on('submit', function () {
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.campaign.store-item')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        toastr.success('{{ translate('Campaign_added_successfully!') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function () {
                            location.href = '{{route('admin.campaign.list', 'item')}}';
                        }, 2000);
                    }
                }
            });
        });

        $('.get-request').on('change', function () {
            let route = '{{ url('/') }}/admin/food/get-categories?parent_id='+$(this).val();
            let id = 'sub-categories';
            getRequest(route, id);
        });

        function getRequest(route, id) {
            $.get({
                url: route,
                dataType: 'json',
                success: function(data) {
                    $('#' + id).empty().append(data.options);
                },
            });
        }

        $('#reset_btn').click(function(){
            location.reload(true);
        })
    </script>
@endpush
