@extends('layouts.admin.app')

@section('title',translate('Banner'))


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
            <div class="col-sm-12 col-lg-12 mb-20">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.banner.store')}}" method="post" id="banner_form">
                            @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                            @php($language = $language->value ?? null)
                            @php($default_lang = str_replace('_', '-', app()->getLocale()))
                            @csrf
                            <div class="row g-3">
                                <div class="col-lg-8">
                                    <div class="global-bg-box p-xxl-20 p-12 rounded">
                                        <div class="row">
                                            <div class="col-md-12">
                                                @if ($language)
                                                    <div class="tabs-slide-language mb-4">
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
                                                            <label class="input-label"
                                                                for="default_title">{{ translate('messages.banner title') }}
                                                                (Default)
                                                                <span class="text-danger">*</span>

                                                            </label>
                                                            <input type="text" name="title[]" id="default_title"
                                                                class="form-control" placeholder="{{ translate('messages.new_banner') }}"

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
                                                                        class="form-control" placeholder="{{ translate('messages.new_banner') }}"
                                                                        >
                                                                </div>
                                                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                            </div>
                                                        @endforeach
                                                    @else
                                                    <div id="default-form">
                                                        <div class="form-group">
                                                            <label class="input-label"
                                                                for="exampleFormControlInput1">{{ translate('messages.banner title') }} ({{ translate('messages.default') }})
                                                                <span class="text-danger">*</span>

                                                            </label>
                                                            <input type="text" name="title[]" class="form-control"
                                                                placeholder="{{ translate('messages.new_banner') }}" >
                                                        </div>
                                                        <input type="hidden" name="lang[]" value="default">
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="input-label" for="title">{{translate('messages.zone')}}
                                                        <span class="text-danger">*</span>

                                                    </label>
                                                    <select name="zone_id" id="zone" class="form-control js-select2-custom get-request" data-url="{{url('/')}}/admin/food/get-foods?zone_id=" data-id="choice_item">
                                                        <option disabled selected value="">---{{translate('messages.select')}}---</option>
                                                        @php($zones=\App\Models\Zone::active()->get(['id','name']))
                                                        @foreach($zones as $zone)
                                                            @if(auth('admin')?->user()?->zone_id)
                                                                @if(auth('admin')->user()->zone_id == $zone->id)
                                                                    <option value="{{$zone->id}}" selected>{{$zone->name}}</option>
                                                                @endif
                                                            @else
                                                                <option value="{{$zone['id']}}">{{$zone['name']}}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.banner_type')}}
                                                        <span class="text-danger">*</span>

                                                    </label>
                                                    <select name="banner_type" id="banner_type" class="form-control bg-white banner_type_change">
                                                        <option value="restaurant_wise">{{translate('messages.restaurant_wise')}}</option>
                                                        <option value="item_wise">{{translate('messages.food_wise')}}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group" id="restaurant_wise">
                                                    <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.restaurant')}}<span
                                                            class="input-label-secondary"></span>
                                                        <span class="text-danger">*</span>

                                                        </label>
                                                    <select name="restaurant_id" class="js-data-example-ajax form-control bg-white"  title="Select Restaurant">
                                                        <option selected disabled>{{ translate('messages.Select') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group" id="item_wise">
                                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.select_food')}}
                                                        <span class="text-danger">*</span>

                                                    </label>
                                                    <select name="item_id" id="choice_item" class="form-control js-select2-custom get-foods" placeholder="{{translate('messages.select_food')}}">
                                                        <option selected disabled>{{ translate('select_food') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <!-- <div class="d-flex flex-column align-items-center gap-3">
                                        <p class="mb-0">{{ translate('Banner_image') }}</p>

                                        <div class="image-box banner2">
                                            <label for="image-input" class="d-flex flex-column align-items-center justify-content-center h-100 cursor-pointer gap-2">
                                                <img width="30" class="upload-icon" src="{{dynamicAsset('assets/admin/img/upload-icon.png')}}" alt="Upload Icon">
                                                <span class="upload-text">{{ translate('Upload Image')}}</span>
                                                <img src="#" alt="Preview Image" class="preview-image">
                                            </label>
                                            <button type="button" class="delete_image">
                                                <i class="tio-delete"></i>
                                            </button>
                                            <input type="file" id="image-input" name="image" accept="image/*" hidden>
                                        </div>

                                        <p class="opacity-75 max-w220 mx-auto text-center">
                                            {{ translate('Image format - jpg png jpeg gif Image Size -maximum size 2 MB Image Ratio - 2:1')}}
                                        </p>
                                    </div> -->
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
                                                        accept=".webp, .jpg, .jpeg, .png, .gif" required>
                                                <label class="upload-file__wrapper ratio-2-1 m-0">
                                                    <div class="upload-file-textbox text-center" style="">
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

            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-header flex-wrap gap-3">
                        <h5 class="card-title">{{translate('messages.banner_list')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$banners->count()}}</span></h5>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <select name="allitems-select" id="bannerType" class="custom-select h-40px min-w-140 w-auto">
                                <option value="">{{translate('All Banner')}}</option>
                                <option value="restaurant_wise" {{request('type') == 'restaurant_wise' ? 'selected' : ''}}>{{translate('Restaurant Wise')}} </option>
                                <option value="item_wise" {{request('type') == 'item_wise' ? 'selected' : ''}}>{{translate('Food Wise')}} </option>
                            </select>
                            <form class="mt-0" action="{{ url()->current() }}">
                                <!-- Search -->
                                <input type="hidden" name="type" value="{{ request('type') }}">
                                <div class="input--group input-group input-group-merge input-group-flush">
                                    <input id="datatableSearch" type="search" name="search" class="form-control" placeholder="{{ translate('Ex_:_Search_by_title_...') }}"
                                           aria-label="{{translate('messages.search_here')}}" value="{{ request()?->search ?? null }}">
                                    <button type="submit" class="btn btn--secondary">
                                        <i class="tio-search"></i>
                                    </button>
                                </div>
                                <!-- End Search -->
                            </form>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive pt-0 datatable-custom">
                        <table id="columnSearchDatatable"
                               class="table table-border table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                "order": [],
                                "orderCellsTop": true,
                                "search": "#datatableSearch",
                                "entries": "#datatableEntries",
                                "isResponsive": false,
                                "isShowPaging": false,
                                "paging": false,
                               }'>
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center">{{ translate('messages.sl') }}</th>
                                    <th>{{translate('messages.Banner info')}}</th>
                                    <th>{{translate('messages.zone')}}</th>
                                    <th>{{translate('messages.Banner Type')}}</th>
                                    <th>{{translate('messages.status')}}</th>
                                    <th class="text-center">{{translate('messages.action')}}</th>
                                </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($banners as $key=>$banner)
                                <tr>
                                    <td class="text-center">{{$key+$banners->firstItem()}}</td>
                                    <td>
                                        <span class="media align-items-center">
                                                            <img class="avatar mr-3 min-w-60 avatar-4by3 onerror-image" src="{{ $banner['image_full_url'] }}"
                                                                 data-onerror-image="{{dynamicAsset('assets/admin/img/900x400/img1.jpg')}}" alt="{{$banner->name}} image">
                                            <div class="media-body">
                                                <h5 class="text-hover-primary mb-0">{{Str::limit($banner['title'], 25, '...')}}</h5>
                                            </div>
                                        </span>
                                    <span class="d-block font-size-sm text-body">

                                    </span>
                                    </td>
                                    <td>{{$banner?->zone?->name ?? translate('messages.N/A')}}</td>
                                    <td>{{translate('messages.'.$banner['type'])}}</td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm" for="statusCheckbox{{$banner->id}}">
                                            <input type="checkbox" data-url="{{route('admin.banner.status',[$banner['id'],$banner->status?0:1])}}" class="toggle-switch-input redirect-url" id="statusCheckbox{{$banner->id}}" {{$banner->status?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn btn-sm btn--primary btn-outline-primary action-btn" href="{{route('admin.banner.edit',[$banner['id']])}}"title="{{translate('messages.edit_banner')}}"><i class="tio-edit"></i>
                                            </a>
                                            <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert" href="javascript:" data-id="banner-{{$banner['id']}}" data-message="{{translate('messages.Want_to_delete_this_banner')}}" title="{{translate('messages.delete_banner')}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('admin.banner.delete',[$banner['id']])}}"
                                                        method="post" id="banner-{{$banner['id']}}">
                                                    @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if(count($banners) === 0)
                        <div class="empty--data">
                            <img src="{{dynamicAsset('assets/admin/img/empty.png')}}" alt="public">
                            <h5>
                                {{translate('messages.no_data_found')}}
                            </h5>
                        </div>
                        @endif
                        <div class="page-area px-4 pb-3">
                            <div class="d-flex align-items-center justify-content-end">
                                <div>
                                    {!! $banners->links() !!}
                                </div>
                            </div>
                        </div>
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
let zone_id ={{ auth('admin')?->user()?->zone_id ? auth('admin')->user()->zone_id : '[]' }} ;
$(document).on('ready', function () {
        let select_control = $('#banner_type, #restaurant_wise select, #item_wise select');
        $('#zone').on('change', function(){
            if($(this).val())
            {
                zone_id = $(this).val();
            }
            else
            {
                zone_id = [];
            }

            let order_type = $('#banner_type').val();
            banner_type_change(order_type);

            $("#restaurant_wise select[name='restaurant_id']").val(null).trigger('change');
            $("#item_wise select[name='item_id']").val(null).trigger('change');

            if($('#zone').val() == undefined) {
                select_control.attr('disabled', '')
            } else {
                select_control.removeAttr('disabled')
            }
        });
        if($('#zone').val() == undefined) {
            select_control.attr('disabled', '')
        } else {
            select_control.removeAttr('disabled')
        }

        $('.js-data-example-ajax').select2({

            ajax: {
                url: '{{url('/')}}/admin/restaurant/get-restaurants',
                data: function (params) {
                    return {
                        q: params.term, // search term
                        zone_ids: [zone_id],
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
                    '<p class="mb-0">{{ translate('messages.No_data_to_show') }}</p>' +
                    '</div>'
                }
            });

            $('#datatableSearch').on('mouseup', function (e) {
                let $input = $(this),
                    oldValue = $input.val();

                if (oldValue == "") return;

                setTimeout(function(){
                    let newValue = $input.val();

                    if (newValue == ""){
                    // Gotcha
                    datatable.search('').draw();
                    }
                }, 1);
            });

            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
        $('#item_wise').hide();
        $('.banner_type_change').on('change', function (){
            let order_type = $(this).val();
            banner_type_change(order_type);
        })
        function banner_type_change(order_type) {
           if(order_type=='item_wise')
            {
                $('#restaurant_wise').hide();
                $('#item_wise').show();
                let route ='{{url('/')}}/admin/food/get-foods?zone_id='+zone_id;
                let id ='choice_item';

            $.get({
                url: route,
                dataType: 'json',
                success: function (data) {
                    $('#' + id).empty().append(data.options);
                },
            });


            }
            else if(order_type=='restaurant_wise')
            {
                $('#restaurant_wise').show();
                $('#item_wise').hide();
            }
            else{
                $('#item_wise').hide();
                $('#restaurant_wise').hide();
            }
        }

        $('#banner_form').on('submit', function (e) {
            e.preventDefault();

            const $submitButton = $(this).find('button[type="submit"]');
            $submitButton.attr('disabled', true).text('Submitting...');

            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.post({
                url: '{{route('admin.banner.store')}}',
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
                        $submitButton.attr('disabled', false).text('{{translate('messages.submit')}}');
                    } else {
                        toastr.success('{{ translate('messages.Banner_uploaded_successfully!') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function () {
                            location.href = '{{route('admin.banner.add-new')}}';
                        }, 2000);
                    }
                },

                error: function (xhr, status, error) {
                    toastr.error('{{ translate('messages.Submission_failed._Please_try_again.') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    $submitButton.attr('disabled', false).text('{{translate('messages.submit')}}');
                }
            });
        });

        $(document).ready(function() {
            $('#bannerType').on('change', function() {
                const selectedValue = $(this).val();
                const baseUrl = window.location.href.split('?')[0];
                let newUrl = baseUrl;

                if (selectedValue) {
                    newUrl = baseUrl + '?type=' + selectedValue;
                }

                window.location.href = newUrl;
            });
        });

        $('#reset_btn').click(function(){
            $('#zone').val(null).trigger('change');
            $('#choice_item').val(null).trigger('change');
            $('#viewer').attr('src','{{dynamicAsset('assets/admin/img/900x400/img1.jpg')}}');
        })
    </script>
@endpush
