@extends('layouts.admin.app')

@section('title',translate('Add New Main Sub Category'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $selectedStatusFilters = array_filter((array) request()->query('status', []), fn ($value) => $value !== '' && $value !== null);
        $selectedPriorityFilters = array_filter((array) request()->query('priority', []), fn ($value) => $value !== '' && $value !== null);
        $selectedCategoryFilters = array_filter((array) request()->query('category', []), fn ($value) => $value !== '' && $value !== null && $value !== 'all');
        $subCategoryFilterCount =
            (count($selectedStatusFilters) ? 1 : 0) +
            (count($selectedPriorityFilters) ? 1 : 0) +
            (count($selectedCategoryFilters) ? 1 : 0);
    @endphp
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img src="{{dynamicAsset('assets/admin/img/sub-category.png')}}" alt="">
                        </div>
                        <span>{{ translate('Main Sub Category Setup') }}</span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="card mb-20 resturant--cate-form">
            <div class="card-body">
                <form action="{{isset($category)?route('admin.category.update',[$category['id']]):route('admin.category.store')}}" method="post">
                @csrf
                    <div class="global-bg-box p-xxl-4 p-3 rounded">
                        @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                        @php($language = $language->value ?? null)
                        @php($default_lang = str_replace('_', '-', app()->getLocale()))
                        @if($language)
                        <div class="js-nav-scroller tabs-slide-language hs-nav-scroller-horizontal">
                            <ul class="nav nav-tabs mb-4 border-0">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active" href="#" id="default-link">{{translate('Default')}}</a>
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
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label"
                                        for="parent_id">{{translate('messages.main_category')}}
                                        <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                              data-placement="right"
                                              data-original-title="{{ translate('messages.Required.') }}"> *
                                    </span></label>
                                    <select id="parent_id" name="parent_id" class="form-control js-select2-custom" required>
                                        <option value="" selected disabled>{{ translate('Select_Main_Category') }}</option>
                                        @foreach(\App\Models\Category::where(['position'=>0])->get(['id','name']) as $cat)
                                            <option value="{{$cat['id']}}" {{isset($category)?($category['parent_id']==$cat['id']?'selected':''):''}} >{{$cat['name']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <input name="position" value="1" type="hidden">
                            </div>
                            <div class="col-md-6">

                                <div class="form-group lang_form" id="default-form">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}} ({{translate('Default')}})
                                        <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                              data-placement="right"
                                              data-original-title="{{ translate('messages.Required.') }}"> *
                                    </span>
                                    </label>
                                    <input required type="text" name="name[]" class="form-control" placeholder="{{ translate('Ex: Main Sub Category Name') }}"   maxlength="191">
                                </div>

                                <input type="hidden" name="lang[]" value="default">

                            @foreach(json_decode($language) as $lang)
                                    <div class="form-group d-none lang_form" id="{{$lang}}-form">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}} ({{strtoupper($lang)}})
                                            <span
                                            class="input-label-secondary" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('messages.content need') }}"><i class="tio-info text-gray1 fs-14"></i></span>
                                        </label>
                                        <input type="text" name="name[]" class="form-control" placeholder="{{ translate('Ex: Main Sub Category Name') }}" maxlength="191"  >
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                            @endforeach
                            @else
                                <div class="form-group" id="default-form">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}} {{translate('Default')}}</label>
                                    <input type="text" name="name[]" class="form-control" placeholder="{{ translate('Ex: Main Sub Category Name') }}"  maxlength="191">
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-4">
                        <!-- Static Button -->
                        <button type="reset" id="reset_btn" class="btn btn--reset">{{translate('reset')}}</button>
                        <!-- Static Button -->
                        <button type="submit" class="btn btn--primary">{{isset($category)?translate('messages.update'):translate('messages.Add')}}</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card mt-2">
            <div class="card-header pt-3 pb-1 border-0">
                <div class="search--button-wrapper">
                    <h5 class="card-title">{{ translate('Main Sub Category List') }}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$categories->total()}}</span></h5>

                   <div class="hs-unfold ml-3">
                         <form method="get" >
                        <!-- Search -->
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input id="datatableSearch" type="search" value="{{ request()?->search ?? null }}"  name="search" class="form-control" placeholder="{{ translate('_Search_by_name_or_Id') }}" aria-label="{{translate('messages.search_here')}}">
                            <button type="submit" class="btn btn--secondary secondary-cmn"><i class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>

                   </div>
                    <div class="hs-unfold ml-3">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm" href="javascript:;"
                            data-hs-unfold-options='{
                                "target": "#usersExportDropdown",
                                "type": "css-animation"
                            }'>
                            <i class="tio-download-to mr-1"></i> {{translate('messages.export')}}
                        </a>

                        <div id="usersExportDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <span class="dropdown-header">{{translate('messages.download_options')}}</span>
                            <a target="__blank" id="export-excel" class="dropdown-item" href="{{route('admin.category.export-sub-categories', ['type'=>'excel', request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{dynamicAsset('assets/admin')}}/svg/components/excel.svg"
                                        alt="Image Description">
                                {{translate('messages.excel')}}
                            </a>
                            <a target="__blank" id="export-csv" class="dropdown-item" href="{{route('admin.category.export-sub-categories', ['type'=>'csv', request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{dynamicAsset('assets/admin')}}/svg/components/placeholder-csv-format.svg"
                                        alt="Image Description">
                                {{translate('messages.csv')}}
                            </a>
                        </div>
                    </div>
                    <div class="hs-unfold ml-3">
                        <a class="btn min-w-100px justify-content-center font-medium btn-sm offcanvas-trigger btn-outline-primary" href="javascript:" data-target="#offcanvas__subcate">
                            <i class="tio-tune-horizontal mr-1 fs-16"></i> <span class="mt-1">{{translate('Filter')}}</span>
                            <span class="badge badge-success badge-pill ml-1" id="filter_count">{{ $subCategoryFilterCount ?: '' }}</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body px-0 pt-0">
                <div class="table-responsive datatable-custom">
                    <table id="columnSearchDatatable"
                        class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                        data-hs-datatables-options='{
                            "search": "#datatableSearch",
                            "entries": "#datatableEntries",
                            "isResponsive": false,
                            "isShowPaging": false,
                            "paging":false,
                        }'>
                        <thead class="thead-light">
                            <tr>
                                <th>{{ translate('messages.sl') }}</th>
                                <th>{{ translate('Main Sub Category') }}</th>
                                <th>{{translate('messages.id')}}</th>
                                <th>{{translate('messages.main_category')}}</th>
                                <th>
                                    <div class=""> {{translate('messages.priority level')}}
                                        <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ translate('Set how prominently this sub-category should appear to customers') }}"><i class="tio-info text-gray1 fs-14"></i></span>
                                    </div>
                                </th>
                                <th class="w-100px">{{translate('messages.status')}}</th>
                                <th class="text-center">{{translate('messages.action')}}</th>
                            </tr>
                        </thead>

                        <tbody id="table-div">
                        @foreach($categories as $key=>$category)
                            <tr>
                                <td>{{$key+$categories->firstItem()}}</td>
                                <td>
                                    <span class="d-block font-size-sm text-body">
                                        {{Str::limit($category->name,20,'...')}}
                                    </span>
                                </td>
                                <td>{{$category->id}}</td>
                                <td>
                                    <span class="d-block font-size-sm text-body">
                                        {{Str::limit($category?->parent?->name,20,'...')}}
                                    </span>
                                </td>
                                <td>
                                    <form action="{{route('admin.category.priority',$category->id)}}" class="priority-form">
                                    <select name="priority" id="priority" class="form-control form--control-select priority-select {{$category->priority == 0 ? 'text--title border-dark':''}} {{$category->priority == 1 ? 'text--info border-info':''}} {{$category->priority == 2 ? 'text--success border-success':''}} ">
                                        <option value="0" {{$category->priority == 0?'selected':''}}>{{translate('messages.normal')}}</option>
                                        <option value="1" {{$category->priority == 1?'selected':''}}>{{translate('messages.medium')}}</option>
                                        <option value="2" {{$category->priority == 2?'selected':''}}>{{translate('messages.high')}}</option>
                                    </select>
                                    </form>
                                </td>
                                <td>
                                    <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{$category->id}}">
                                    <input type="checkbox" data-url="{{route('admin.category.status',[$category['id'],$category->status?0:1])}}" class="toggle-switch-input redirect-url" id="stocksCheckbox{{$category->id}}" {{$category->status?'checked':''}}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </td>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        <a class="btn btn-sm text-end action-btn info--outline text--info info-hover offcanvas-trigger get_data data-info-show"
                                           data-target="#offcanvas__customBtn3" data-id="{{ $category['id'] }}"
                                           data-url="{{ route('admin.category.edit', [$category['id']]) }}"
                                           data-model="sub"
                                           href="javascript:" title="{{ translate('Edit Main Sub Category') }}"><i
                                                class="tio-edit"></i>
                                        </a>
                                        <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert" href="javascript:"
                                        data-id="category-{{$category['id']}}" data-message="{{ translate('Want_to_delete_this_sub_category_?') }}" title="{{ translate('Delete Main Sub Category') }}"><i class="tio-delete-outlined"></i>
                                        </a>
                                    </div>
                                    <form action="{{route('admin.category.delete',[$category['id']])}}" method="post" id="category-{{$category['id']}}">
                                        @csrf @method('delete')
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @if(count($categories) === 0)
                    <div class="empty--data">
                        <img src="{{dynamicAsset('assets/admin/img/empty.png')}}" alt="public">
                        <h5>
                            {{translate('no_data_found')}}
                        </h5>
                    </div>
                    @endif
                    <div class="page-area px-4 pt-3 pb-0">
                        <div class="d-flex align-items-center justify-content-end">
                            <div>
                    {!! $categories->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="offcanvas__customBtn3" class="custom-offcanvas d-flex flex-column justify-content-between">
        <div id="data-view" class="h-100">
        </div>
    </div>

    <div id="offcanvas__subcate" class="custom-offcanvas"
        style="--offcanvas-width: 500px">
        <form method="GET" class="d-flex flex-column justify-content-between" id="subCategoryFilterForm">
            <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                <div class="px-3 py-3 d-flex justify-content-between w-100">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h2 class="mb-0 fs-18 text-title font-medium">{{ translate('Sub Category Filter') }}</h2>
                    </div>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;
                    </button>
                </div>
            </div>
            <div>
                <div class="custom-offcanvas-body p-20">
                    <input type="hidden" name="search" value="{{ request()->query('search') }}">
                    <div class="d-flex flex-column gap-20px">
                        <div class="global-bg-box rounded p-xl-20 p-16">
                            <h5 class="mb-10px font-regular text-color font-normal">{{translate('Status')}}</h5>
                            <div class="bg-white rounded p-xl-3 p-2">
                                <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2">
                                    <div class="col-sm-6 col-auto">
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="active__status" name="status[]" value="1" {{ in_array('1', (array) request()->query('status', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label text-title" for="active__status">
                                                    {{translate('messages.Active')}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-auto">
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="inactive__status" name="status[]" value="0" {{ in_array('0', (array) request()->query('status', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label text-title" for="inactive__status">
                                                    {{translate('messages.Inactive')}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="global-bg-box rounded p-xl-20 p-16">
                            <h5 class="mb-10px font-regular text-color font-normal">{{translate('Priority')}} </h5>
                            <div class="bg-white rounded p-xl-3 p-2">
                                <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2 order-status_controller ">
                                    <div class="col-sm-6 col-md-4 col-auto">
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="priority_2" name="priority[]" value="2" {{ in_array('2', (array) request()->query('priority', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label text-title" for="priority_2">
                                                {{translate('messages.High')}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-auto">
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="priority_1" name="priority[]" value="1" {{ in_array('1', (array) request()->query('priority', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label text-title" for="priority_1">
                                                {{translate('messages.Medium')}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-auto">
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="priority_0" name="priority[]" value="0" {{ in_array('0', (array) request()->query('priority', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label text-title" for="priority_0">
                                                {{translate('messages.Normal')}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="global-bg-box rounded p-xl-20 p-16">
                            <h5 class="mb-10px font-regular text-color font-normal">{{translate('Category ')}} </h5>
                            <div class="bg-white rounded p-xl-3 p-2">
                                <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2 order-status_controller custom__select-controller ">
                                    <div class="col-sm-6 col-auto">
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input check-all" id="all" name="category[]" value="all">
                                                <label class="custom-control-label text-title" for="all">
                                                    {{translate('messages.All')}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    @foreach(\App\Models\Category::where(['position'=>0])->get(['id','name']) as $category)
                                    <div class="col-sm-6 col-auto">
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input"
                                                    id="category_{{ $category->id }}"
                                                    name="category[]"
                                                    value="{{ $category->id }}"
                                                    {{ in_array($category->id, (array) request()->query('category', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label text-title" for="category_{{ $category->id }}">
                                                    {{translate('messages.' . $category->name)}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    <div class="col-sm-12">
                                        <div class="text-center w-100">
                                            <button type="button" class="see__more btn mx-auto d-flex fs-12 align-items-center justify-content-center gap-1 p-0 border-0 text--primary font-semibold text-center">
                                                {{translate('See More')}} <span class="text-primary count"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="align-items-center h-84px bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3 position-absolute w-100">
                <button type="button"
                    class="btn w-100 btn--reset"
                    id="subCategoryFilterReset"
                    data-reset-url="{{ route('admin.category.add-sub-category') }}">{{translate('Reset')}}</button>
                <button type="submit" class="btn w-100 btn--primary">{{translate('Apply')}}</button>
            </div>
        </form>
    </div>


    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>
@endsection

@push('script_2')
    <script src="{{dynamicAsset('assets/admin')}}/js/view-pages/sub-category-index.js"></script>
    <script src="{{ dynamicAsset('assets/admin/js/offcanvas.js') }}"></script>

    <script>
        "use strict";
        document.addEventListener("DOMContentLoaded", function () {
            const subCategoryFilterForm = document.getElementById('subCategoryFilterForm');
            const subCategoryFilterReset = document.getElementById('subCategoryFilterReset');

            $('.select2-basic').select2({
                width: '100%'
            });

            if (subCategoryFilterReset && subCategoryFilterForm) {
                subCategoryFilterReset.addEventListener('click', function () {
                    const hasQueryParams = window.location.search.length > 1;

                    if (hasQueryParams) {
                        window.location.href = this.dataset.resetUrl;
                        return;
                    }

                    subCategoryFilterForm.reset();
                    $(subCategoryFilterForm).find('select').val(null).trigger('change');
                    $('.custom-offcanvas').removeClass('open');
                    $('#offcanvasOverlay').removeClass('show');
                });
            }
        });
        $(document).on('click', '.data-info-show', function() {
            let id = $(this).data('id');
            let url = $(this).data('url');
            let model = $(this).data('model');
            fetch_data(id, url, model)
        })

        function fetch_data(id, url, model='category') {
            $.ajax({
                url: url,
                type: "get",
                data: {
                    id: id,
                    model: model
                },
                beforeSend: function() {
                    $('#data-view').empty();
                    $('#loading').show()
                },
                success: function(data) {
                    $("#data-view").append(data.view);
                    initLangTabs();
                    initSelect2Dropdowns();
                },
                complete: function() {
                    $('#loading').hide()
                }
            })
        }



        function initLangTabs() {
            const langLinks = document.querySelectorAll(".lang_link1");
            langLinks.forEach(function(langLink) {
                langLink.addEventListener("click", function(e) {
                    e.preventDefault();
                    langLinks.forEach(function(link) {
                        link.classList.remove("active");
                    });
                    this.classList.add("active");
                    document.querySelectorAll(".lang_form1").forEach(function(form) {
                        form.classList.add("d-none");
                    });
                    let form_id = this.id;
                    let lang = form_id.substring(0, form_id.length - 5);
                    $("#" + lang + "-form1").removeClass("d-none");
                    if (lang === "default") {
                        $(".default-form1").removeClass("d-none");
                    }
                });
            });
        }

        function initSelect2Dropdowns() {
            $('.js-select2-custom1').select2({
                placeholder: 'Select tax rate',
                allowClear: true
            });
            $('.offcanvas-close, #offcanvasOverlay').on('click', function () {
                $('.custom-offcanvas').removeClass('open');
                $('#offcanvasOverlay').removeClass('show');
            });
        }
    </script>
@endpush
