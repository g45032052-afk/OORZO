@extends('layouts.admin.app')

@section('title', translate('Update_Food'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ dynamicAsset('assets/admin/css/tags-input.min.css') }}" rel="stylesheet">
    <link href="{{ dynamicAsset('assets/admin/css/AI/animation/product/ai-sidebar.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title"><i class="tio-edit"></i>
                {{ translate('messages.food_update') }}
            </h1>
        </div>


        @php($openai_config = \App\CentralLogics\Helpers::get_business_settings('openai_config'))

        <form class="validate-form global-ajax-form" action="{{ route('admin.food.update', [$product['id']]) }}"
            method="post" id="" enctype="multipart/form-data">
            <input type="hidden" id="request_type" value="admin">
            @csrf
            {{-- Marks the edit as opened from the restaurant details food tab, so the update returns there. --}}
            <input type="hidden" name="from" value="{{ request('from') }}">
            <input type="hidden" id="removedVariationIDs" name="removedVariationIDs" value="">
            <input type="hidden" id="removedVariationOptionIDs" name="removedVariationOptionIDs" value="">
            <div class="row g-2">


                @includeif('admin-views.product.partials._title_and_discription')
                <div class="col-lg-6">
                    <div class="card shadow--card-2 border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex flex-column align-items-center gap-3">
                                <p class="mb-0">{{ translate('Food_Image') }} </p>

                                @include('admin-views.partials._image-uploader', [
                                    'id' => 'image-input',
                                    'name' => 'image',
                                    'isRequired' => true,
                                    'existingImage' => $product['image_full_url'],
                                    'ratio' => '1:1',
                                    'maxSize' => MAX_FILE_SIZE,
                                    'imageExtension' => IMAGE_EXTENSION,
                                    'imageFormat' => IMAGE_FORMAT,
                                ])

                            </div>
                        </div>
                    </div>
                </div>
                @includeif('admin-views.product.partials._category_and_general')
                @includeif('admin-views.product.partials._price_and_stock')

                <div class="col-lg-12">
                    <div class="general_wrapper">
                        <div class="outline-wrapper">
                            <div class="card shadow--card-2 border-0 bg-animate">
                                <div class="card-header flex-wrap">
                                    <h5 class="card-title">
                                        <span class="card-header-icon mr-2">
                                            <i class="tio-canvas-text"></i>
                                        </span>
                                        <span>{{ translate('messages.food_variations') }}</span>
                                    </h5>
                                    <div>
                                        <a class="btn text--primary-2" id="add_new_option_button">
                                            {{ translate('add_new_variation') }}
                                            <i class="tio-add"></i>
                                        </a>
                                        @if (isset($openai_config) && data_get($openai_config, 'status') == 1)
                                            <button type="button"
                                                class="btn bg-white text-primary opacity-1 generate_btn_wrapper variation_setup_auto_fill"
                                                id="variation_setup_auto_fill"
                                                data-route="{{ route('admin.product.variation-setup-auto-fill') }}"
                                                data-lang="en">
                                                <div class="btn-svg-wrapper">
                                                    <img width="18" height="18" class=""
                                                        src="{{ dynamicAsset('assets/admin/img/svg/blink-right-small.svg') }}"
                                                        alt="">
                                                </div>
                                                <span class="ai-text-animation d-none" role="status">
                                                    {{ translate('Just_a_second') }}
                                                </span>
                                                <span class="btn-text">{{ translate('Generate') }}</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <input type="hidden" name="remove_all_old_variations" value="0"
                                    id="remove_all_old_variations">
                                <div class="card-body">
                                    <div class="row g-2">
                                        <div class="col-md-12">
                                            <div id="add_new_option">
                                                @if (isset($product->variations))
                                                    @foreach (json_decode($product->variations, true) as $key_choice_options => $item)
                                                        @if (isset($item['price']))
                                                            @break

                                                        @else
                                                            @include(
                                                                'admin-views.product.partials._new_variations',
                                                                [
                                                                    'item' => $item,
                                                                    'key' => $key_choice_options + 1,
                                                                ]
                                                            )
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @includeif('admin-views.product.partials._ai_sidebar')
                <div class="col-lg-12">
                    @includeif('admin-views.product.partials._seo-section-edit')
                </div>


                <div class="col-lg-12">
                    <div class="btn--container justify-content-end">
                        <button type="reset" id="reset_btn"
                            class="btn btn--reset">{{ translate('messages.reset') }}</button>
                        <button type="submit" class="btn btn--primary">{{ translate('messages.submit') }}</button>
                    </div>
                </div>
            </div>
        </form>

    </div>
@endsection

@push('script')
@endpush

@push('script_2')
    <script>
        var count = {{ isset($product->variations) ? count(json_decode($product->variations, true)) : 0 }};
    </script>
    <script src="{{ dynamicAsset('assets/admin') }}/js/tags-input.min.js"></script>
    <script src="{{ dynamicAsset('assets/admin') }}/js/view-pages/product-index.js"></script>

    <script src="{{ dynamicAsset('assets/admin/js/AI/products/product-title-autofill.js') }}"></script>
    <script src="{{ dynamicAsset('assets/admin/js/AI/products/product-description-autofill.js') }}"></script>
    <script src="{{ dynamicAsset('assets/admin/js/AI/products/general-setup-autofill.js') }}"></script>
    <script src="{{ dynamicAsset('assets/admin/js/AI/products/product-others-autofill.js') }}"></script>
    <script src="{{ dynamicAsset('assets/admin/js/AI/products/variation-setup-auto-fill.js') }}"></script>
    {{-- <script src="{{ dynamicAsset('assets/admin/js/AI/products/seo-section-autofill.js') }}"></script> --}}

    <script src="{{ dynamicAsset('assets/admin/js/AI/products/ai-sidebar.js') }}"></script>

    <script src="{{ dynamicAsset('assets/admin/js/AI/products/compressor/image-compressor.js') }}"></script>
    <script src="{{ dynamicAsset('assets/admin/js/AI/products/compressor/compressor.min.js') }}"></script>


    <script>
        "use strict";


        $('#stock_type').on('change', function() {
            if ($(this).val() == 'unlimited') {
                $('.stock_disable').prop('readonly', true).prop('required', false).attr('placeholder',
                    '{{ translate('Unlimited') }}').val('');
                $('.hide_this').addClass('d-none');
            } else {
                $('.stock_disable').prop('readonly', false).prop('required', true).attr('placeholder',
                    '{{ translate('messages.Ex:_100') }}');
                $('.hide_this').removeClass('d-none');
            }
        });

        updatestockCount();

        function updatestockCount() {
            if ($('#stock_type').val() == 'unlimited') {
                $('.stock_disable').prop('readonly', true).prop('required', false).attr('placeholder',
                    '{{ translate('Unlimited') }}').val('');
                $('.hide_this').addClass('d-none');
            } else {
                $('.stock_disable').prop('readonly', false).prop('required', true).attr('placeholder',
                    '{{ translate('messages.Ex:_100') }}');
                $('.hide_this').removeClass('d-none');
            }
        }


        $('#restaurant_id').on('change', function() {
            let route = '{{ url('/') }}/admin/restaurant/get-addons?data[]=0&restaurant_id=';
            let restaurant_id = $(this).val();
            let id = 'add_on';
            // Same rule as the add page: no restaurant, nothing to pick addons from.
            $('#add_on').prop('disabled', !restaurant_id);
            $('#addon_hint').toggleClass('d-none', !!restaurant_id);
            getRestaurantData(route, restaurant_id, id);
            loadRestaurantCategories(restaurant_id);

        });
        $('.get-request').on('change', function() {
            let route = '{{ url('/') }}/admin/food/get-categories?parent_id=' + $(this).val();
            let id = 'sub-categories';
            getRequest(route, id);
        });

        function getRestaurantData(route, restaurant_id, id) {
            $.get({
                url: route + restaurant_id,
                dataType: 'json',
                success: function(data) {
                    $('#' + id).empty().append(data.options);
                },
            });
        }

        function getRequest(route, id) {
            $.get({
                url: route,
                dataType: 'json',
                success: function(data) {
                    $('#' + id).empty().append(data.options);
                },
            });
        }

        function loadRestaurantCategories(restaurantId, selectedId = '') {
            let $restaurantCategory = $('#restaurant_category_id');
            $restaurantCategory.attr('data-id', selectedId);

            if (!restaurantId) {
                $restaurantCategory.empty().append('<option value="">{{ translate('Select_Restaurant_Category') }}</option>').val('');
                // No restaurant selected → no asterisk, not required.
                $restaurantCategory.prop('required', false);
                $('.restaurant-category-required-mark').hide();
                return;
            }

            $.get({
                url: '{{ route('admin.food.get-restaurant-categories') }}',
                data: {
                    restaurant_id: restaurantId,
                    selected_id: selectedId
                },
                dataType: 'json',
                success: function(data) {
                    $restaurantCategory.empty().append(data.options).val(selectedId).trigger('change');
                    // Toggle the "*" mark + required attribute based on whether
                    // the selected restaurant has any of its own categories.
                    const required = !!(data && data.has_categories);
                    $restaurantCategory.prop('required', required);
                    $('.restaurant-category-required-mark').toggle(required);
                },
            });
        }

        $(document).ready(function() {
            setTimeout(function() {
                let category = $("#category-id").val();
                let sub_category = '{{ count($product_category) >= 2 ? $product_category[1]->id : '' }}';
                let sub_sub_category =
                    '{{ count($product_category) >= 3 ? $product_category[2]->id : '' }}';
                getRequest('{{ url('/') }}/admin/food/get-categories?parent_id=' + category +
                    '&sub_category=' + sub_category, 'sub-categories');
                getRequest('{{ url('/') }}/admin/food/get-categories?parent_id=' + sub_category +
                    '&sub_category=' + sub_sub_category, 'sub-sub-categories');
                loadRestaurantCategories('{{ $product['restaurant_id'] }}', '{{ $product->restaurant_category_id ?? '' }}');

            }, 1000)

            @if (count(json_decode($product['add_ons'], true)) > 0)
                getRestaurantData(
                    '{{ url('/') }}/admin/restaurant/get-addons?@foreach (json_decode($product['add_ons'], true) as $addon)data[]={{ $addon }}& @endforeach restaurant_id=',
                    '{{ $product['restaurant_id'] }}', 'add_on');
            @else
                getRestaurantData('{{ url('/') }}/admin/restaurant/get-addons?data[]=0&restaurant_id=',
                    '{{ $product['restaurant_id'] }}', 'add_on');
            @endif
        });

        $(document).on('ready', function() {
            $('.js-select2-custom').each(function() {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });

        $('.js-data-example-ajax').select2({
            ajax: {
                url: '{{ url('/') }}/admin/restaurant/get-restaurants',
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function(data) {
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

        $(document).ready(function() {
            $("#add_new_option_button").click(function() {
                add_new_option_button();
            });
        });

        function add_new_option_button() {
            $('#empty-variation').hide();
            count++;
            let add_option_view = @include('admin-views.product.partials._js_new_variations_div');
            $("#add_new_option").append(add_option_view);
            updatestockCount();
        }


        function add_new_row_button(data) {
            var countRow = 1 + $('#option_price_view_' + data).children('.add_new_view_row_class').length;
            let add_new_row_view = `
            <div class="row add_new_view_row_class mb-3 position-relative pt-3 pt-sm-0">
                <div class="col-md-3 col-sm-5">
                        <label for="">{{ translate('Option_name') }}  &nbsp;<span class="form-label-secondary text-danger"
                                data-toggle="tooltip" data-placement="right"
                                data-original-title="{{ translate('messages.Required.') }}"> *
                                </span></label>
                        <input class="form-control" required type="text" name="options[` + data + `][values][` +
                countRow + `][label]" id="">
                    </div>
                    <div class="col-md-3 col-sm-5">
                        <label for="">{{ translate('Additional_price') }}  &nbsp;<span class="form-label-secondary text-danger"
                                data-toggle="tooltip" data-placement="right"
                                data-original-title="{{ translate('messages.Required.') }}"> *
                                </span></label>
                        <input class="form-control"  required type="number" min="0" step="0.01" name="options[` +
                data +
                `][values][` + countRow +
                `][optionPrice]" id="">
                    </div>
                    <div class="col-md-3 col-sm-5 hide_this">
                        <label for="">{{ translate('Stock') }}  </label>
                        <input class="form-control stock_disable count_stock"  required type="number" min="0" max="99999999"  name="options[` +
                data +
                `][values][` + countRow + `][total_stock]" id="">
                    </div>

                    <input type="hidden" hidden name="options[` +
                data +
                `][values][` + countRow + `][option_id]" value="null" >

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
            updatestockCount();

        }

        let removedVariationIDs = [];
        let removedVariationOptionIDs = [];

        $(document).on('click', '.remove_variation', function() {
            removedVariationIDs.push($(this).data('id'));
            $('#removedVariationIDs').val(removedVariationIDs.join(','));
        });
        $(document).on('click', '.remove_variation_option', function() {
            removedVariationOptionIDs.push($(this).data('id'));
            $('#removedVariationOptionIDs').val(removedVariationOptionIDs.join(','));
        });


        function validateImageSize(inputSelector, imageType = "Image", maxSizeMB = 2) {
            let fileInput = $(inputSelector)[0];
            if (fileInput && fileInput.files.length > 0) {
                let fileSize = fileInput.files[0].size;
                if (fileSize > maxSizeMB * 1024 * 1024) {
                    toastr.error(`${imageType} size should not exceed ${maxSizeMB}MB`, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    return false;
                }
            }
            return true;
        }



        $('#reset_btn').click(function() {
            location.reload(true);
        })
    </script>
@endpush
