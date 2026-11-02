@extends('layouts.admin.app')

@section('title', $restaurant->name . "'s" . translate('messages.Food'))

@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{ dynamicAsset('assets/admin/css/croppie.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">

            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <h1 class="page-header-title text-break">
                    <i class="tio-museum"></i> <span>{{ $restaurant->name }} @include('partials._verified_restaurant_badge', ['restaurant' => $restaurant])</span>
                </h1>
            </div>
            <!-- Nav Scroller -->
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                <span class="hs-nav-scroller-arrow-prev initial-hidden">
                    <a class="hs-nav-scroller-arrow-link" href="javascript:">
                        <i class="tio-chevron-left"></i>
                    </a>
                </span>

                <span class="hs-nav-scroller-arrow-next initial-hidden">
                    <a class="hs-nav-scroller-arrow-link" href="javascript:">
                        <i class="tio-chevron-right"></i>
                    </a>
                </span>

                <!-- Nav -->
                @include('admin-views.vendor.view.partials._header', ['restaurant' => $restaurant])

                <!-- End Nav -->
            </div>
            <!-- End Nav Scroller -->
        </div>
        <!-- End Page Header -->

        <!-- End Page Header -->

        <div class="resturant-card-navbar px-xl-4 justify-content-evenly">
            <div class="order-info-item">
                <div class="order-info-icon icon-sm">
                    <img src="{{ dynamicAsset('assets/admin/img/resturant/foods/all.png') }}" alt="public">
                </div>
                @php(  $food = \App\Models\Food::withoutGlobalScope(\App\Scopes\RestaurantScope::class)->where(['restaurant_id' => $restaurant->id])->count())
                @php($food = $food == null ? 0 : $food)
                <h6 class="card-subtitle">{{ translate('messages.all') }}<span
                        class="amount text--primary">{{ $food }}</span></h6>
            </div>
            <span class="order-info-seperator"></span>
            <div class="order-info-item">
                <div class="order-info-icon icon-sm">
                    <img src="{{ dynamicAsset('assets/admin/img/resturant/foods/active.png') }}" alt="public">
                </div>
                @php(
    $food = \App\Models\Food::withoutGlobalScope(\App\Scopes\RestaurantScope::class)->where(['restaurant_id' => $restaurant->id, 'status' => 1])->count())
                @php($food = $food == null ? 0 : $food)
                <h6 class="card-subtitle">{{ translate('Active_Food') }}<span
                        class="amount text--primary">{{ $food }}</span></h6>
            </div>
            <span class="order-info-seperator"></span>
            <div class="order-info-item">
                <div class="order-info-icon icon-sm">
                    <img src="{{ dynamicAsset('assets/admin/img/resturant/foods/inactive.png') }}" alt="public">
                </div>
                @php(
    $food = \App\Models\Food::withoutGlobalScope(\App\Scopes\RestaurantScope::class)->where(['restaurant_id' => $restaurant->id, 'status' => 0])->count())
                @php($food = $food == null ? 0 : $food)
                <h6 class="card-subtitle">{{ translate('Inactive_Food') }}<span
                        class="amount text--primary">{{ $food }}</span></h6>
            </div>
        </div>
        <!-- End Page Header -->
        <!-- Page Heading -->
        <div class="card h-100">
            <div class="card-header flex-wrap border-0 py-2">
                <div class="search--button-wrapper">
                    <h3 class="card-title d-flex align-items-center"> <span class="card-header-icon mr-1"><i
                                class="tio-restaurant"></i></span> {{ translate('messages.foods') }} <span
                            class="badge badge-soft-dark ml-2 badge-circle">{{ $foods->total() }}</span></h3>
                    <form class="my-2 ml-auto mr-sm-2 mr-xl-4 ml-sm-auto flex-grow-1 flex-grow-sm-0">
                        <!-- Search -->
                        <input type="hidden" name="restaurant_id" value="{{ $restaurant->id }}">
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input id="datatableSearch_" type="search" name="search" class="form-control"
                                value="{{ request()?->search ?? null }}" placeholder="{{ translate('Search_by_name.') }}"
                                aria-label="{{ translate('messages.search') }}" required>
                            <button type="submit" class="btn btn--secondary secondary-cmn"><i class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>
                    <!-- Static Export Button -->
                    <div class="hs-unfold ml-3 mr-3">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm"
                            href="javascript:"
                            data-hs-unfold-options='{
                            "target": "#usersExportDropdown",
                            "type": "css-animation"
                        }'>
                            <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                        </a>

                        <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">

                            <span class="dropdown-header">{{ translate('messages.download_options') }}</span>

                            <a target="__blank" id="export-excel" class="dropdown-item"
                                href="{{ route('admin.food.restaurant-food-export', ['type' => 'excel', 'restaurant_id' => $restaurant->id, request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>

                            <a target="__blank" id="export-csv" class="dropdown-item"
                                href="{{ route('admin.food.restaurant-food-export', ['type' => 'csv', 'restaurant_id' => $restaurant->id, request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                {{ translate('messages.csv') }}
                            </a>
                        </div>
                    </div>
                    <!-- Static Export Button -->
                    <a href="{{ route('admin.food.add-new', ['restaurant_id' => $restaurant->id]) }}" class="btn btn--primary pull-right"><i
                            class="tio-add-circle"></i> {{ translate('messages.add_new_food') }}</a>
                </div>
            </div>
            <div class="table-responsive datatable-custom">
                <table id="columnSearchDatatable"
                    class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                    data-hs-datatables-options='{
                        "order": [],
                        "orderCellsTop": true,
                        "paging": false
                    }'>
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center pl-4 w-100px">{{ translate('messages.sl') }}</th>
                            <th class="w-120px">{{ translate('messages.name') }}</th>
                            <th class="w-120px">{{ translate('messages.category') }}</th>
                            <th class="text-center w-120px pr-80px">
                                {{ translate('messages.price') }}
                            </th>
                            @if ($productWiseTax)
                                <th class="w-100px">{{ translate('messages.Vat/Tax') }}</th>
                            @endif
                            <th class="w-100px">{{ translate('messages.status') }}</th>
                            <th class="w-60px text-center">{{ translate('messages.action') }}</th>
                        </tr>
                    </thead>

                    <tbody id="set-rows">

                        @foreach ($foods as $key => $food)
                            @php($stock_out = null)

                            <tr>
                                <td class="text-center">

                                    {{ $key + $foods->firstItem() }}
                                </td>
                                <td class="py-2">
                                    <a class="media align-items-center"
                                        href="{{ route('admin.food.view', [$food['id']]) }}">
                                        <img class="avatar avatar-lg mr-3 onerror-image"
                                            src="{{ $food['image_full_url'] ?? dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}"
                                            data-onerror-image="{{ dynamicAsset('assets/admin/img/100x100/food-default-image.png') }}"
                                            alt="{{ $food->name }} image">
                                        <div class="media-body">
                                            <h5 class="text-hover-primary mb-0">{{ Str::limit($food['name'], 20, '...') }}


                                                @if ($food->stock_type != 'unlimited' && $food->item_stock <= 0)
                                                    @php($stock_out = true)

                                                    <span
                                                        class="badge badge-soft-warning badge-pill font-medium">{{ translate('Out Of Stock') }}</span>
                                                    <span class="input-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ translate('messages.Your_main_stock_is_out_of_stock.') }}"><img
                                                            src="{{ dynamicAsset('assets/admin/img/info-circle.svg') }}"
                                                            alt="public/img"></span>
                                                @else
                                                    <?php

                                                    if (isset($food->variations)) {
                                                        foreach (json_decode($food->variations, true) as $item) {
                                                            if (isset($item['values']) && is_array($item['values'])) {
                                                                foreach ($item['values'] as $value) {
                                                                    if (isset($value['stock_type']) && $value['stock_type'] != 'unlimited' && $value['current_stock'] <= 0) {
                                                                        $stock_out = true;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    @if ($stock_out)
                                                        {{-- <span class="badge badge-soft-warning badge-pill font-medium">{{ translate('Out Of Stock') }}</span> --}}
                                                        <span class="input-label-secondary" data-toggle="tooltip"
                                                            data-placement="right"
                                                            data-original-title="{{ translate('messages.One_or_more_variations_are_out_of_stock.') }}"><img
                                                                src="{{ dynamicAsset('assets/admin/img/info-circle.svg') }}"
                                                                alt="public/img"></span>
                                                    @endif
                                                @endif


                                            </h5>
                                        </div>
                                    </a>
                                </td>
                                <td>
                                    <div>
                                        {{ Str::limit(
                                            ($food?->category?->parent ? $food?->category?->parent?->name : $food?->category?->name) ??
                                                translate('messages.uncategorize'),
                                            20,
                                            '...',
                                        ) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="table--food-price text-right">
                                        @php($price = \App\CentralLogics\Helpers::format_currency($food['price']))
                                        {{ $price }}
                                    </div>
                                </td>
                                @if ($productWiseTax)
                                    <td>
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
                                    <label class="toggle-switch toggle-switch-sm"
                                        for="stocksCheckbox{{ $food->id }}">
                                        <input type="checkbox"
                                            data-url="{{ route('admin.food.status', [$food['id'], $food->status ? 0 : 1]) }}"
                                            class="toggle-switch-input redirect-url"
                                            id="stocksCheckbox{{ $food->id }}" {{ $food->status ? 'checked' : '' }}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </td>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        @if ($stock_out)
                                            <a class="btn btn-sm btn--primary btn-outline-primary action-btn "
                                                href="#update-stock{{ $food['id'] }}"
                                                title="{{ translate('update_stock') }}" data-toggle="modal">
                                                <i class="tio-autorenew"></i>
                                            </a>
                                        @endif
                                        <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                            href="{{ route('admin.food.edit', [$food['id'], 'from' => 'restaurant']) }}"
                                            title="{{ translate('messages.edit_food') }}"><i class="tio-edit"></i>
                                        </a>
                                        <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert"
                                            href="javascript:" data-id="food-{{ $food['id'] }}"
                                            data-message="{{ translate('Want to delete this item') }}"
                                            title="{{ translate('messages.delete_food') }}"><i
                                                class="tio-delete-outlined"></i>
                                        </a>
                                        <form action="{{ route('admin.food.delete', [$food['id']]) }}" method="post"
                                            id="food-{{ $food['id'] }}">
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
                                                        <span
                                                            class="badge badge-soft-success bg-white position-absolute top-0 left-0 m-1 rounded fs-10">{{ translate('Veg') }}</span>
                                                    @else
                                                        <span
                                                            class="badge badge-soft-danger bg-white position-absolute top-0 left-0 m-1 rounded fs-10">{{ translate('Non_Veg') }}</span>
                                                    @endif
                                                </div>
                                                <div class="info fs-12 text-body">
                                                    <span class="d-block text-title fs-16 mb-1">
                                                        <span class="line-limit-1">
                                                            {{ $food['name'] }}
                                                        </span>
                                                    </span>
                                                    <span class="rating d-flex align-items-center gap-1 mb-1">
                                                        <i class="tio-star"></i>
                                                        <span class="text-title font-semibold">({{ round($food->avg_rating,2) }}/5)</span>
                                                        <span class="text-gray1">(25+)</span>
                                                    </span>
                                                    <div class="fs-14 price-food-modal d-flex algin-items-center gap-1 flex-wrap">
                                                        <span class="gray-dark font-regular">{{ translate('Price') }} :</span>
                                                        <span class="font-medium text-title">{{ \App\CentralLogics\Helpers::format_currency($food['price']) }}</span>
                                                        <span class="line mx-1 d-sm-block d-none"></span> <span class="gray-dark font-regular">{{ translate('Discount') }} :</span> <span class="font-medium text-title">
                                                            {{ $food->discount_type == 'percent' ? $food->discount . ' %' : \App\CentralLogics\Helpers::format_currency($food['discount']) }}</span>
                                                    </div>
                                                    <div>
                                                        <?php
                                                        $addonIds = json_decode($food->add_ons, true) ?? [];
                                                        $addonNames = collect($addonIds)->map(fn($id) => $addons[$id] ?? null)->filter();
                                                        ?>

                                                        {{ translate('Addons') }}: <span class="font-medium">
                                                            @if ($addonNames->isNotEmpty())
                                                                {{ $addonNames->join(', ') }}.
                                                            @else
                                                                {{ translate('No_addons_found.') }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <form action="{{ route('admin.food.updateStock') }}" method="POST">
                                                @method('post')
                                                @csrf
                                                <div class="stock-body-controller p-md-4 p-3 pt-0">
                                                <div class="__bg-F8F9FC-card d-flex flex-column gap-3">
                                                    <input type="hidden" value="{{ $food->id }}" name="food_id">
                                                    <div class="__bg-F8F9FC-card text-left p-0">
                                                        <label class="input-label">
                                                            {{ translate('Main_Stock') }}
                                                        </label>
                                                        <input type="number" step="1" name="item_stock"
                                                            value="{{ $food->item_stock }}" required min="1"
                                                            max="99999999999" class="form-control" placeholder="Ex : 50">
                                                    </div>
                                                    <div class="__bg-F8F9FC-card text-left p-0">
                                                        @if (isset($food->variations) && count(json_decode($food->variations, true)) > 0)
                                                            <div class="row g-2">
                                                                <div class="col-6">
                                                                    <h4>{{ translate('Variation') }}</h4>
                                                                </div>
                                                                <div class="col-6">
                                                                    <h4>{{ translate('Stock') }}</h4>
                                                                </div>
                                                            </div>
                                                            @php($variations = json_decode($food->variations, true) ?? [])

                                                            @foreach ($variations as $item)
                                                                @if (!is_array($item))
                                                                    @continue
                                                                @endif

                                                                <div class="row g-1 mb-3">

                                                                    <div class="col-12">
                                                                        <h6 class="m-0">
                                                                            {{ $item['name'] ?? 'Variation' }}
                                                                        </h6>
                                                                    </div>

                                                                    @foreach (($item['values'] ?? []) as $value)
                                                                        @if (isset($value['option_id']))
                                                                            <div class="col-12">
                                                                                <div class="row g-1 align-items-center">
                                                                                    <span class="col-6">
                                                                                        {{ $value['label'] ?? '' }} :
                                                                                    </span>

                                                                                    <div class="col-6">
                                                                                        <input
                                                                                            class="form-control"
                                                                                            required
                                                                                            value="{{ $value['current_stock'] ?? 0 }}"
                                                                                            type="number"
                                                                                            min="1"
                                                                                            step="1"
                                                                                            max="999999999"
                                                                                            name="option[{{ $value['option_id'] }}]"
                                                                                            placeholder="Ex : 50">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    @endforeach

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
                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div>
                            {!! $foods->links() !!}
                        </div>
                    </div>
                </div>
            </div>
                @if(count($foods) === 0)
                    <div class="empty--data">
                        <img src="{{dynamicAsset('assets/admin/img/empty.png')}}" alt="public">
                        <h5>
                            {{translate('no_data_found')}}
                        </h5>
                    </div>
                    @endif

        </div>
    </div>
@endsection

@push('script_2')
    <!-- Page level plugins -->
    <script>
        // Call the dataTables jQuery plugin
        $(document).ready(function() {
            $('#dataTable').DataTable();
        });

        $(document).on('ready', function() {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function() {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });

            $('#column2_search').on('keyup', function() {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });

            $('#column3_search').on('change', function() {
                datatable
                    .columns(3)
                    .search(this.value)
                    .draw();
            });

            $('#column4_search').on('keyup', function() {
                datatable
                    .columns(4)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function() {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
    </script>
@endpush
