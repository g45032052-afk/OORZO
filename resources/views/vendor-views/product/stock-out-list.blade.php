@extends('layouts.vendor.app')

@section('title',translate('messages.Out_of_Stock'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="mb-2 mb-sm-0">
                <h1 class="page-header-title"><i class="tio-filter-list"></i> {{translate('messages.Out_of_Stock_Foods')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$foods->total()}}</span></h1>
            </div>
            <div class="">
                <div class="row g-2 align-items-center justify-content-sm-end">

                    <div class="col-auto">
                        <a href="{{route('vendor.food.list')}}" class="btn max-sm-12 badge-soft-primary font-medium py-2 fs-12 w-100"> {{translate('messages.All Foods')}}</a>
                    </div>
                    <div class="col-auto">
                        <a href="{{route('vendor.food.add-new')}}" class="btn max-sm-12 fs-12 py-2 px-3 btn--primary w-100"><i
                                class="tio-add-circle"></i> {{translate('messages.add_new_food')}}</a>
                    </div>

                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-wrap gap-2 border-0">
                <div class="search--button-wrapper search--button-area justify-content-start">
                    <form id="search-form" action="{{ url()->current() }}">
                        <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                        <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                        <input type="hidden" name="search" value="{{ request('search', '') }}">
                        @foreach(request('types', []) as $type)
                            <input type="hidden" name="types[]" value="{{ $type }}">
                        @endforeach
                        @foreach(request('status', []) as $status)
                            <input type="hidden" name="status[]" value="{{ $status }}">
                        @endforeach
                        @foreach(request('category_ids', []) as $id)
                            <input type="hidden" name="category_ids[]" value="{{ $id }}">
                        @endforeach

                        <div class="input-group input--group border rounded">
                            <input id="datatableSearch" type="search" name="search" class="form-control border-0" value="{{ request('search', '') }}"
                                   placeholder="{{ translate('messages.Ex : Search Food Name') }}">
                            <button type="submit" class="btn btn--reset py-1 px-2">
                                <i class="tio-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="d-flex align-items-center gap-lg-3 gap-2 flex-wrap">
                    <div class="hs-unfold">
                        <a class="js-hs-unfold-invoker btn btn-outline-primary min-w-100px justify-content-center font-medium btn-sm filter-show offcanvas-trigger"
                           data-target="#Food-list_filter" href="javascript:">
                            <i class="tio-tune-horizontal mr-1 fs-16"></i>{{translate('Filter')}}
                        </a>
                    </div>
                    <div class="hs-unfold">
                        <a class="js-hs-unfold-invoker btn btn-sm btn--reset dropdown-toggle min-height-40" href="javascript:;"
                           data-hs-unfold-options='{
                                    "target": "#usersExportDropdown",
                                    "type": "css-animation"
                                }'>
                            <i class="tio-download-from-cloud mr-1 fs-16"></i> {{ translate('messages.export') }}
                        </a>

                        <div id="usersExportDropdown"
                             class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">

                            <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                            <a id="export-excel" class="dropdown-item" href="{{ route('admin.food.export', ['type' => 'excel', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg"
                                     alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="{{ route('admin.food.export', ['type' => 'csv', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                     alt="Image Description">
                                .{{ translate('messages.csv') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Header -->

            <!-- Table -->
            <div class="table-responsive datatable-custom pt-0">
                <table id="datatable" class="table table-borderless table-thead-borderedless table-nowrap table-align-middle card-table"
                       data-hs-datatables-options='{
                        "columnDefs": [{
                            "targets": [],
                            "width": "5%",
                            "orderable": false
                        }],
                        "order": [],
                        "info": {
                        "totalQty": "#datatableWithPaginationInfoTotalQty"
                        },

                        "entries": "#datatableEntries",
                        "isResponsive": false,
                        "isShowPaging": false,
                            "paging":false
                    }'>
                    <thead class="global-bg-box">
                    <tr>
                        <th>{{ translate('messages.sl') }}</th>
                        <th class="w-20p">{{translate('messages.name')}}</th>
                        <th class="w-20p">{{translate('messages.category')}}</th>
                        <th class="text-center">{{translate('messages.price')}}</th>
                        @if ($productWiseTax)
                            <th  class="text-center">{{ translate('messages.Vat/Tax') }}</th>
                        @endif
                        <th class="text-center">{{translate('messages.Recommended')}}</th>
                        <th class="text-center">{{translate('messages.status')}}</th>
                        <th class="text-center">{{translate('messages.action')}}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($foods as $key=>$food)
                        @php( $stock_out = null)

                        <tr>
                            <td>{{$key+$foods->firstItem()}}</td>
                            <td>
                                <a class="media align-items-center" href="{{route('vendor.food.view',[$food['id']])}}">
                                    <img class="avatar avatar-lg mr-3 onerror-image" src="{{ $food['image_full_url'] }}"
                                         data-onerror-image="{{dynamicAsset('assets/admin/img/100x100/food-default-image.png')}}" alt="{{$food->name}} image">
                                    <div class="media-body">
                                        <h5 class="text-hover-primary mb-0">
                                            {{ Str::limit($food['name'], 20, '...') }}
                                        </h5>

                                        @php($stock_out = false)

                                        @if ($food->stock_type != 'unlimited' && $food->item_stock <= 0)
                                            @php($stock_out = true)
                                        @else
                                            @if(isset($food->variations))
                                                @foreach (json_decode($food->variations, true) as $item)
                                                    @if(isset($item['values']) && is_array($item['values']))
                                                        @foreach ($item['values'] as $value)
                                                            @if(isset($value['stock_type']) && $value['stock_type'] != 'unlimited' && $value['current_stock'] <= 0)
                                                                @php($stock_out = true)
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endif

                                        @if($stock_out)
                                            <span class="badge badge-soft-warning badge-pill font-medium mt-1 d-inline-block">
                                                {{ translate('Out Of Stock') }}
                                            </span>
                                        @endif
                                    </div>

                                </a>
                            </td>
                            <td>
                                {{ Str::limit(($food?->category?->parent ? $food?->category?->parent?->name : $food?->category?->name )  ?? translate('messages.uncategorize')
                                , 20, '...') }}
                            </td>
                            <td>
                                <div class="text-right mx-auto mw-36px">
                                    {{($food['price'])}}
                                </div>
                            </td>
                            @if ($productWiseTax)
                                <td class="text-center">
                                    <span class="">
                                        @forelse ($food?->taxVats?->pluck('tax.name', 'tax.tax_rate')->toArray() as $key => $tax)
                                            <span> {{ $tax }} : <span class="font-bold">
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
                                <div class="d-flex">
                                    <div class="mx-auto">
                                        <label class="toggle-switch toggle-switch-sm mr-2"  data-toggle="tooltip" data-placement="top" title="{{ translate('messages.Recommend_to_customers') }}" for="stocksCheckbox{{$food->id}}">
                                            <input type="checkbox" data-url="{{route('vendor.food.recommended',[$food['id'],$food->recommended?0:1])}}" class="toggle-switch-input redirect-url" id="stocksCheckbox{{$food->id}}" {{$food->recommended?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <div class="mx-auto">
                                        <label class="toggle-switch toggle-switch-sm mr-2" data-toggle="tooltip" data-placement="top" title="{{ translate('messages.Change_food_visibility_to_customers') }}" for="statusCheckbox{{$food->id}}">
                                            <input type="checkbox" data-url="{{route('vendor.food.status',[$food['id'],$food->status?0:1])}}" class="toggle-switch-input redirect-url" id="statusCheckbox{{$food->id}}" {{$food->status?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn--container justify-content-center">

                                    <a class="btn action-btn btn--primary btn-outline-primary"
                                       href="{{route('vendor.food.edit',[$food['id']])}}" title="{{translate('messages.edit_food')}}"><i class="tio-edit"></i>
                                    </a>

                                    <a class="btn btn-sm btn--primary btn-outline-primary action-btn " href="#update-stock{{ $food['id'] }}" title="{{ translate('update_stock') }}" data-toggle="modal">
                                        <i class="tio-autorenew"></i>
                                    </a>

                                    <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:"
                                       data-id="food-{{$food['id']}}" data-message="{{ translate('Want to delete this item ?') }}" title="{{translate('messages.delete_food')}}"><i class="tio-delete-outlined"></i>
                                    </a>

                                    <form action="{{route('vendor.food.delete',[$food['id']])}}"
                                          method="post" id="food-{{$food['id']}}">
                                        @csrf @method('delete')
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Stock Update Modal --}}
                        <div class="modal fade" id="update-stock{{ $food['id'] }}">
                            <div class="modal-dialog modal-dialog-centered max-w-540px">
                                <div class="modal-content">
                                    <div class="modal-header position-absolute top-0 right-0 px-2 pt-2 z-index-2">
                                        <div></div>
                                        <button type="button" data-dismiss="modal" class="btn p-0">
                                            <i class="tio-clear fs-24"></i>
                                        </button>
                                    </div>
                                    <div class="modal-body p-0 pt-4">
                                        <div class="table-rest-info mr-2 p-md-4 p-3 pt-0 align-items-start">
                                            <div class="thumb position-relative min-w-80">
                                                <img src="{{ $food['image_full_url'] }}" class="w-80px">
                                                @if ($food->veg == 1)
                                                    <span class="badge badge-soft-success bg-white position-absolute top-0 left-0 m-1 rounded fs-10">{{ translate('Veg') }}</span>
                                                @else
                                                    <span class="badge badge-soft-danger bg-white position-absolute top-0 left-0 m-1 rounded fs-10">{{ translate('Non_Veg') }}</span>
                                                @endif
                                            </div>
                                            <div class="info fs-12 text-body">
                                                    <span class="d-block text-title fs-16 mb-1">
                                                        <span class="line-limit-1">
                                                            {{ ucwords($food['name']) }}
                                                        </span>
                                                    </span>
                                                <span class="rating d-flex align-items-center gap-1 mb-1">
                                                        <i class="tio-star"></i>
                                                        <span class="text-title font-semibold">({{ round($food->avg_rating,2) }}/5)</span>
                                                        <span class="text-gray1">
                                                            ({{ $food->rating_count }}{{ $food->rating_count > 24 ? '+' : '' }})
                                                        </span>

                                                    </span>
                                                <div class="fs-14 price-food-modal d-flex algin-items-center gap-1 flex-wrap">
                                                    <span class="gray-dark font-regular">{{ translate('Price') }} : </span> <span class="font-medium text-title">{{ \App\CentralLogics\Helpers::format_currency($food['price'])  }}</span> <span class="line mx-1 d-sm-block d-none"> </span> <span class="gray-dark font-regular">{{ translate('Discount') }} :</span> <span class="font-medium text-title"> {{ $food->discount_type == 'percent' ?  $food->discount . ' %' :  \App\CentralLogics\Helpers::format_currency($food['discount'])   }}</span>
                                                </div>
                                                <div>
                                                        <?php
                                                        $addonIds = json_decode($food->add_ons, true) ?? [];
                                                        $addonNames = collect($addonIds)->map(fn($id) => $addons[$id] ?? null)->filter();
                                                        ?>

                                                    {{ translate('Addons') }}: <span class="font-medium">
                                                                   @if($addonNames->isNotEmpty())
                                                            {{ $addonNames->join(', ') }}.
                                                        @else
                                                            {{ translate('No_addons_found.') }}
                                                        @endif
                                                                </span>
                                                </div>
                                            </div>
                                        </div>
                                        <form action="{{ route('vendor.food.updateStock') }}" method="POST" >
                                            @method("post")
                                            @csrf
                                            <div class="stock-body-controller p-md-4 p-3 pt-0">
                                                <div class="__bg-F8F9FC-card d-flex flex-column gap-3">
                                                    <input type="hidden" value="{{ $food->id }}"  name="food_id">
                                                    <div class="__bg-F8F9FC-card text-left p-0">
                                                        <label class="input-label">
                                                            {{ translate('Main_Stock') }}
                                                        </label>
                                                        <input type="number" step="1" name="item_stock" value="{{ $food->item_stock }}" required min="1" max="99999999999" class="form-control" placeholder="Ex : 50">
                                                    </div>
                                                    <div class="__bg-F8F9FC-card text-left p-0">
                                                        @if (isset($food->variations) && count(json_decode($food->variations,true)) >0 )

                                                            <div class="row g-2">
                                                                <div class="col-6">
                                                                    <h4>{{ translate('Variation') }}</h4>
                                                                </div>
                                                                <div class="col-6">
                                                                    <h4>{{ translate('Stock') }}</h4>
                                                                </div>
                                                            </div>
                                                            @foreach (json_decode($food->variations,true) as $item)
                                                                <div class="row g-1 mb-3">

                                                                    <div class="col-12">
                                                                        <h6 class="m-0">
                                                                            {{ $item['name'] }}
                                                                        </h6>
                                                                    </div>

                                                                    @if (isset($item['values']) && is_array($item['values']))
                                                                        @foreach ($item['values'] as $value)
                                                                            @if (isset($value['option_id']))
                                                                                <div class="col-12">
                                                                                    <div class="row g-1 align-items-center">
                                                                                        <span class="col-6">{{  $value['label']  }} :</span>
                                                                                        <div class="col-6">
                                                                                            <input class="form-control" required value="{{ $value['current_stock'] }}" type="number" min="1" step="1" max="999999999" name="option[{{ $value['option_id'] }}]"  placeholder="Ex : 50">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                            @endif
                                                                        @endforeach

                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer modal-footer-shadow d-flex flex-nowrap justify-content-end gap-lg-3 gap-2 border-0">
                                                <button type="button" data-dismiss="modal" class="btn min-w-120 btn--reset">{{ translate('Cancel') }}</button>
                                                <button type="submit" class="btn min-w-120 btn--primary">{{ translate('Update') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endforeach
                    </tbody>
                </table>
                @if(count($foods) === 0)
                    <div class="empty--data">
                        <img src="{{dynamicAsset('assets/admin/img/empty.png')}}" alt="public">
                        <h5>
                            {{translate('no_data_found')}}
                        </h5>
                    </div>
                @endif
                <div class="page-area">
                    <table>
                        <tfoot class="border-top">
                        {!! $foods->links() !!}
                        </tfoot>
                    </table>
                </div>
            </div>
            <!-- End Table -->
        </div>
        <!-- End Card -->
    </div>

    <!-- Food List Filter -->
    <div id="Food-list_filter" class="custom-offcanvas d-flex flex-column justify-content-between"
         style="--offcanvas-width: 500px">
        <div>
            <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                <div class="px-3 py-3 d-flex justify-content-between w-100">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h2 class="mb-0 fs-18 text-title font-medium">{{ translate('Filter - Food List') }}</h2>
                    </div>
                    <button type="button"
                            class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                            aria-label="Close">&times;
                    </button>
                </div>
            </div>
            <form id="food-filter-form" action="{{ route('vendor.food.stockOutList') }}" method="GET">
                <div class="custom-offcanvas-body p-20">
                    <input type="hidden" name="search" value="{{ request()->query('search') }}">
                    <div class="d-flex flex-column gap-20px">
                        <div class="global-bg-box rounded p-xl-20 p-16">
                            <h5 class="mb-10px font-regular text-color font-normal">{{translate('Food Type')}}</h5>
                            <div class="bg-white rounded p-xl-3 p-2">
                                <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2">
                                    <div class="col-sm-6 col-auto">
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="food_veg" name="types[]" value="veg" {{ in_array('veg', (array) request()->query('types', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label text-title" for="food_veg">
                                                    {{translate('messages.Veg')}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-auto">
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="food_non_veg" name="types[]" value="non_veg" {{ in_array('non_veg', (array) request()->query('types', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label text-title" for="food_non_veg">
                                                    {{translate('messages.Non-veg')}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                            <div class="price-range-container">
                                <label class="fs-14 d-block mb-3 text-title">{{translate('Price Range')}}</label>
                                <div class="slider-wrapper mb-lg-4 mb-3">
                                    @php(
                                        $rangeMinBound = is_numeric($foodMinPrice ?? null) ? (float)$foodMinPrice : 0
                                    )
                                    @php(
                                        $rangeMaxBound = is_numeric($foodMaxPrice ?? null) ? (float)$foodMaxPrice : 0
                                    )
                                    @php(
                                        $sliderMin = request()->query('min_price') !== null ? (float) request()->query('min_price') : $rangeMinBound
                                    )
                                    @php(
                                        $sliderMax = request()->query('max_price') !== null ? (float) request()->query('max_price') : $rangeMaxBound
                                    )
                                    <input type="range" id="min_price" min="{{ $rangeMinBound }}" max="{{ $rangeMaxBound }}" value="{{ $sliderMin }}">
                                    <input type="range" id="max_price" min="{{ $rangeMinBound }}" max="{{ $rangeMaxBound }}" value="{{ $sliderMax }}">
                                    <div class="slider-track">
                                        <div class="slider-track-filled" id="slider_track_filled"></div>
                                    </div>
                                </div>
                                <div class="price-inputs gap-xxl-4 gap-3">
                                    <div class="d-flex w-100 align-items-center justify-content-between gap-1 border rounded h-40 bg-white overflow-hidden">
                                        <div class="gray-dark fs-12 m-0 __bg-FAFAFA px-2 h-100 d-flex align-items-center">{{translate('Min Price')}}:</div>
                                        <div class="d-flex align-items-center text-title fs-14 w-auto">
                                            $
                                            <input type="number" id="min_input" name="min_price" value="{{ $sliderMin }}" min="{{ $rangeMinBound }}" max="{{ $rangeMaxBound }}" class="border-0 bg-transparent text-title">
                                        </div>
                                    </div>
                                    <div class="d-flex w-100 align-items-center justify-content-between gap-1 border rounded h-40 bg-white overflow-hidden">
                                        <div class="gray-dark fs-12 m-0 __bg-FAFAFA px-2 h-100 d-flex align-items-center">{{translate('Max Price')}}:</div>
                                        <div class="d-flex align-items-center text-title fs-14 w-auto">
                                            $
                                            <input type="number" id="max_input" name="max_price" value="{{ $sliderMax }}" min="{{ $rangeMinBound }}" max="{{ $rangeMaxBound }}" class="border-0 bg-transparent text-title">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="global-bg-box rounded p-xl-20 p-16">
                            <h5 class="mb-10px font-regular text-color font-normal">{{translate('Category')}}  </h5>
                            <div class="bg-white rounded p-xl-3 p-2">
                                <div class="row gx-xl-3 gx-2 gy-xl-3 gy-2 order-status_controller restaurent-select-controller">
                                    @php($selectedCategories = (array) request()->query('category_ids', []))
                                    <div class="col-sm-6 col-auto">
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input check-all" id="all2" name="category_check" {{ (isset($categoriesList) && count($categoriesList) > 0 && count($selectedCategories) === count($categoriesList)) ? 'checked' : '' }}>
                                                <label class="custom-control-label text-title" for="all2">
                                                    {{translate('messages.All')}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    @foreach($categoriesList ?? [] as $cat)
                                        <div class="col-sm-6 col-auto">
                                            <div class="form-group m-0">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="category-status{{ $cat->id }}" name="category_ids[]" value="{{ $cat->id }}" {{ in_array($cat->id, $selectedCategories) ? 'checked' : '' }}>
                                                    <label class="custom-control-label text-title" for="category-status{{ $cat->id }}">
                                                        {{ Str::limit($cat->name, 30, '...') }}
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
                <div class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3 position-sticky">
                    <a href="{{ route('admin.food.list') }}" class="btn w-100 btn--reset offcanvas-close">{{translate('Reset')}}</a>
                    <button type="submit" class="btn w-100 btn--primary">{{translate('Apply')}}</button>
                </div>
            </form>
        </div>
    </div>
    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>

@endsection

@push('script_2')
    <script>
        "use script";
        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#datatable'), {
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
                        '<img class="mb-3 w-7rem" src="{{dynamicAsset('assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description">' +
                        '<p class="mb-0">{{ translate('No_data_to_show') }}</p>' +
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

            $('#toggleColumn_index').change(function (e) {
                datatable.columns(0).visible(e.target.checked)
            })
            $('#toggleColumn_name').change(function (e) {
                datatable.columns(1).visible(e.target.checked)
            })

            $('#toggleColumn_type').change(function (e) {
                datatable.columns(2).visible(e.target.checked)
            })

            $('#toggleColumn_status').change(function (e) {
                datatable.columns(4).visible(e.target.checked)
            })
            $('#toggleColumn_price').change(function (e) {
                datatable.columns(3).visible(e.target.checked)
            })
            $('#toggleColumn_action').change(function (e) {
                datatable.columns(5).visible(e.target.checked)
            })
            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });

        $('#category').select2({
            ajax: {
                url: '{{route("vendor.category.get-all")}}',
                data: function (params) {
                    return {
                        q: params.term, // search term
                        all:true,
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
    </script>
    <script>
        $(document).ready(function () {
            const showCount = 8;
            $(".restaurent-select-controller").each(function () {
                const $container = $(this);
                const $items = $container.find(".col-sm-6");
                const $button = $container.find(".see__more");

                // Initially hide extra items
                $items.slice(showCount).hide();

                // Update button text + count
                function updateButton() {
                    const hiddenCount = $items.filter(":hidden").length;
                    if (hiddenCount > 0) {
                        $button.html(`See More <span class="count">(${hiddenCount})</span>`);
                    } else {
                        $button.html(`See Less`);
                    }
                }

                updateButton();

                // Toggle on button click
                $button.on("click", function () {
                    const hiddenCount = $items.filter(":hidden").length;

                    if (hiddenCount > 0) {
                        // Show all
                        $items.show();
                    } else {
                        // Show only first 8 again
                        $items.slice(showCount).hide();
                    }

                    updateButton();
                });
            });
        });
    </script>
    <script>
        $(function() {
            var $minSlider = $('#min_price'),
                $maxSlider = $('#max_price'),
                $minInput = $('#min_input'),
                $maxInput = $('#max_input'),
                $trackFilled = $('#slider_track_filled');

            var rangeMin = parseFloat('{{ $rangeMinBound ?? 0 }}') || 0,
                rangeMax = parseFloat('{{ $rangeMaxBound ?? 0 }}') || 0;

            function updateTrack() {
                var min = parseFloat($minSlider.val()),
                    max = parseFloat($maxSlider.val());

                var denom = Math.max(rangeMax - rangeMin, 1);
                var minPercent = ((min - rangeMin) / denom) * 100;
                var maxPercent = ((max - rangeMin) / denom) * 100;

                $trackFilled.css({
                    'left': minPercent + '%',
                    'width': (maxPercent - minPercent) + '%'
                });
            }

            $minSlider.on('input', function() {
                let minVal = parseFloat($minSlider.val());
                let maxVal = parseFloat($maxSlider.val());

                if (minVal > maxVal) minVal = maxVal;

                $minSlider.val(minVal);
                $minInput.val(minVal);
                updateTrack();
            });

            $maxSlider.on('input', function() {
                let minVal = parseFloat($minSlider.val());
                let maxVal = parseFloat($maxSlider.val());

                if (maxVal < minVal) maxVal = minVal;

                $maxSlider.val(maxVal);
                $maxInput.val(maxVal);
                updateTrack();
            });

            // Input -> Slider sync
            $minInput.on('input', function() {
                let val = parseFloat($minInput.val());
                let maxVal = parseFloat($maxSlider.val());

                if (isNaN(val) || val < rangeMin) val = rangeMin;
                if (val > maxVal) val = maxVal;

                $minSlider.val(val);
                $minInput.val(val);
                updateTrack();
            });

            $maxInput.on('input', function() {
                let val = parseFloat($maxInput.val());
                let minVal = parseFloat($minSlider.val());

                if (isNaN(val) || val > rangeMax) val = rangeMax;
                if (val < minVal) val = minVal;

                $maxSlider.val(val);
                $maxInput.val(val);
                updateTrack();
            });

            // Initialize track on page load
            updateTrack();
        });
    </script>
@endpush
