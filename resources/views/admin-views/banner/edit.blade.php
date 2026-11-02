@extends('layouts.admin.app')

@section('title',translate('messages.update_banner'))


@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header mb-1">
            <div class="row align-items-center">
                <div class="col-sm">
                    <h1 class="page-header-title fs-24">
                        <!-- <i class="tio-add-circle-outlined"></i> -->
                        {{translate('messages.add_new_banner')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.banner.update', [$banner->id])}}" method="post" id="banner_form">
                            @csrf
                            <div class="row g-3">
                                <div class="col-lg-8">
                                    <div class="global-bg-box p-xxl-20 p-12 rounded">
                                        <div class="row">
                                           <div class="col-md-12">
                                               <div class="form-group">
                                                   @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                                                   @php($language = $language->value ?? null)
                                                   @php($default_lang = str_replace('_', '-', app()->getLocale()))
                                                   @if($language)
                                                   <div class="js-nav-scroller tabs-slide-language mb-4 hs-nav-scroller-horizontal">
                                                       <ul class="nav nav-tabs border-0">
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
                                                       <div class="lang_form" id="default-form">
                                                           <div class="form-group">
                                                               <label class="input-label" for="default_title">{{translate('messages.title')}} ({{translate('messages.default')}})<span class="text-danger">*</span></label>
                                                               <input type="text" name="title[]" id="default_title" class="form-control" placeholder="{{translate('messages.new_banner')}}" value="{{$banner->getRawOriginal('title')}}"  >
                                                           </div>
                                                           <input type="hidden" name="lang[]" value="default">
                                                       </div>
                                                       @foreach(json_decode($language) as $lang)
                                                           <?php
                                                               if(count($banner['translations'])){
                                                                   $translate = [];
                                                                   foreach($banner['translations'] as $t)
                                                                   {
                                                                       if($t->locale == $lang && $t->key=="title"){
                                                                           $translate[$lang]['title'] = $t->value;
                                                                       }
                                                                   }
                                                               }
                                                           ?>
                                                           <div class="d-none lang_form" id="{{$lang}}-form">
                                                               <div class="form-group">
                                                                   <label class="input-label" for="{{$lang}}_title">{{translate('messages.title')}} ({{strtoupper($lang)}})</label>
                                                                   <input type="text" name="title[]" id="{{$lang}}_title" class="form-control" placeholder="{{translate('messages.new_banner')}}" value="{{$translate[$lang]['title']??''}}"  >
                                                               </div>
                                                               <input type="hidden" name="lang[]" value="{{$lang}}">
                                                           </div>
                                                       @endforeach
                                                   @else
                                                   <div id="default-form">
                                                       <div class="form-group">
                                                           <label class="input-label" for="exampleFormControlInput1">{{translate('messages.title')}} ({{ translate('messages.default') }})<span class="text-danger">*</span></label>
                                                           <input type="text" name="title[]" class="form-control" placeholder="{{translate('messages.new_banner')}}"  value="{{$banner->getRawOriginal('title')}}" maxlength="100" >
                                                       </div>
                                                       <input type="hidden" name="lang[]" value="default">
                                                   </div>
                                                   @endif
                                               </div>
                                           </div>
                                           <div class="col-md-6">
                                               <div class="form-group">
                                                   <label class="input-label" for="title">{{translate('messages.zone')}}<span class="text-danger">*</span></label>
                                                   <select name="zone_id" id="zone" class="form-control js-select2-custom get-request" data-url="{{url('/')}}/admin/food/get-foods?zone_id=" data-id="choice_item">
                                                       <option disabled selected>---{{translate('messages.select')}}---</option>
                                                       @php($zones=\App\Models\Zone::active()->get(['id','name']))

                                                       @foreach($zones as $zone)
                                                           @if(auth('admin')?->user()?->zone_id)
                                                               @if(auth('admin')->user()->zone_id == $zone->id)
                                                                   <option value="{{$zone['id']}}" {{$zone->id == $banner->zone_id ? 'selected' : ''}}>{{$zone['name']}}</option>
                                                               @endif
                                                           @else
                                                               <option value="{{$zone['id']}}" {{$zone->id == $banner->zone_id ? 'selected' : ''}}>{{$zone['name']}}</option>
                                                           @endif
                                                       @endforeach
                                                   </select>
                                               </div>
                                           </div>
                                           <div class="col-md-6">
                                               <div class="form-group">
                                                   <label class="input-label" for="exampleFormControlInput1">{{translate('messages.banner_type')}}<span class="text-danger">*</span></label>
                                                   <select id="banner_type" name="banner_type" class="form-control banner_type_change">
                                                       <option value="restaurant_wise" {{$banner->type == 'restaurant_wise'? 'selected':'' }}>{{translate('messages.restaurant_wise')}}</option>
                                                       <option value="item_wise" {{$banner->type == 'item_wise'? 'selected':'' }}>{{translate('messages.food_wise')}}</option>
                                                   </select>
                                               </div>
                                           </div>
                                           <div class="col-md-12">
                                               <div class="form-group" id="restaurant_wise">
                                                   <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.restaurant')}}<span class="text-danger">*</span><span
                                                           class="input-label-secondary"></span></label>
                                                   <select name="restaurant_id" class="js-data-example-ajax" id="resturant_ids"  title="Select Restaurant">
                                                       @if($banner->type=='restaurant_wise')
                                                        @php($restaurant = \App\Models\Restaurant::where('id', $banner->data)->first())
                                                           @if($restaurant)
                                                               <option value="{{$restaurant->id}}" data-verified="{{ (int) $restaurant->verified_seller }}" selected>{{$restaurant->name}}</option>
                                                           @endif
                                                       @endif
                                                   </select>
                                               </div>
                                           </div>
                                           <div class="col-md-12">
                                               <div class="form-group" id="item_wise">
                                                   <label class="input-label" for="exampleFormControlInput1">{{translate('messages.select_food')}}<span class="text-danger">*</span></label>
                                                   <select name="item_id" id="choice_item" class="form-control js-select2-custom" placeholder="{{translate('messages.select_food')}}">

                                                   </select>
                                               </div>
                                           </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="p-xxl-20 d-flex align-items-center justify-content-center p-12 global-bg-box text-center rounded h-100">
                                        <div class="">
                                            <div class="mb-lg-4 mb-3 text-start">
                                                <h5 class="mb-1">
                                                    {{ translate('Banner Image') }} <span class="text-danger">*</span>
                                                </h5>
                                                <p class="mb-0 fs-12 gray-dark">{{ translate('Upload your image here') }}</p>
                                            </div>
                                            <div class="upload-file  mx-auto">
                                                <input type="file" name="image" class="upload-file__input single_file_input"
                                                        accept=".webp, .jpg, .jpeg, .png, .gif" @if(!$banner->image) required @endif>
                                                <label class="upload-file__wrapper ratio-2-1 m-0">
                                                    <div class="upload-file-textbox text-center" style="">
                                                        <img width="22" class="svg" src="{{dynamicAsset('assets/admin/img/image-upload.png')}}" alt="img">
                                                        <h6 class="mt-1 text-gray1 fw-medium fs-10 lh-base text-center">
                                                            <span class="text-info">{{translate('Click to upload')}}</span>
                                                            <br>
                                                            {{translate('Or drag and drop')}}
                                                        </h6>
                                                    </div>
                                                    <img class="upload-file-img" loading="lazy" src="{{ $banner['image_full_url'] }}" data-default-src="{{ $banner['image_full_url'] }}" alt="" style="display: none;">
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
                                            <p class="fs-10 text-center max-w-187px mx-auto mb-0 mt-20">
                                                {{ translate('Supported format : JPG, JPEG, PNG, Gif mage size : Max 2 MB')}} <span class="font-medium text-title">{{ translate('(2:1)')}}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="btn--container justify-content-end mt-4">
                                <button id="reset_btn" type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                                <button type="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')
<script src="{{dynamicAsset('assets/admin')}}/js/view-pages/banner-index.js"></script>
<script>
    "use strict";
    $(document).on('ready', function () {
        let zone_id = {{$banner->zone_id}};
        banner_type_change('{{$banner->type}}');

        $(document).on('change', '#zone', function(){
            zone_id = $(this).val() || true;
        });

        $('.js-data-example-ajax').select2({
            ajax: {
                url: '{{url('/')}}/admin/restaurant/get-restaurants',
                data: function (params) {
                    return {
                        q: params.term,
                        zone_ids: [zone_id],
                        page: params.page
                    };
                },
                processResults: function (data) {
                    return { results: data };
                }
            }
        });

        $('.js-select2-custom').each(function () {
            $.HSCore.components.HSSelect2.init($(this));
        });
    });

    $('.banner_type_change').on('change', function (){
        let order_type = $(this).val();
        banner_type_change(order_type);
    });

    function banner_type_change(order_type) {
        if(order_type=='item_wise') {
            $('#restaurant_wise').hide();
            $('#item_wise').show();
            getRequest('{{url('/')}}/admin/food/get-foods?zone_id={{$banner->zone_id}}&data[]={{$banner->data}}','choice_item');
        } else if(order_type=='restaurant_wise') {
            $('#restaurant_wise').show();
            $('#item_wise').hide();
        } else {
            $('#item_wise').hide();
            $('#restaurant_wise').hide();
        }
    }

    @if($banner->type == 'item_wise')
    getRequest('{{url('/')}}/admin/food/get-foods?zone_id={{$banner->zone_id}}&data[]={{$banner->data}}','choice_item');
    @endif

    $('#banner_form').on('submit', function (e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: '{{route('admin.banner.update', [$banner['id']])}}',
            method: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                if (data.errors) {
                    data.errors.forEach(error => toastr.error(error.message));
                } else {
                    toastr.success('{{translate('messages.banner_updated_successfully')}}');
                    setTimeout(() => location.href = '{{route('admin.banner.add-new')}}', 2000);
                }
            }
        });
    });

    $('#reset_btn').click(function(){
        // Reset text fields
        $('#default_title').val("{{$banner->getRawOriginal('title')}}");
        $('#banner_type').val("{{$banner->type}}").trigger('change');

        // Reset image
        let defaultImgSrc = $('.upload-file-img').data('default-src') || '{{ $banner['image_full_url'] }}';
        $('.upload-file-img').attr('src', defaultImgSrc).show();
        $('.upload-file-textbox').hide();

        // ✅ Reset zone (Select2 সহ)
        let defaultZone = "{{$banner->zone_id}}";
        $('#zone').val(defaultZone).trigger('change.select2');

        // ✅ Reset restaurant / item after zone reset
        setTimeout(function () {
            @if($banner->type == 'restaurant_wise')
            $('#resturant_ids').val("{{$banner->data}}").trigger('change.select2');
            @elseif($banner->type == 'item_wise')
            $('#choice_item').val("{{$banner->data}}").trigger('change.select2');
            @endif
        }, 400);
    });

    function getRequest(route, id) {
        $.get({
            url: route,
            dataType: 'json',
            success: function (data) {
                $('#' + id).empty().append(data.options);
            },
        });
    }
</script>

@endpush
