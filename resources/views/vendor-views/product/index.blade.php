@extends('layouts.vendor.app')

@section('title',translate('messages.add_new_food'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{dynamicAsset('assets/admin/css/tags-input.min.css')}}" rel="stylesheet">
    <link href="{{ dynamicAsset('assets/admin/css/AI/animation/product/ai-sidebar.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title"><i class="tio-add-circle-outlined"></i> {{translate('messages.add_new_food')}}</h1>
        </div>
          @php($openai_config=\App\CentralLogics\Helpers::get_business_settings('openai_config'))

        <!-- End Page Header -->
        <form action="javascript:" method="post" id="food_form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="restaurant_id" value="{{ \App\CentralLogics\Helpers::get_restaurant_id() }}">
            <input type="hidden" id="request_type" value="vendor">
            <div class="row g-2">
             @includeif('admin-views.product.partials._title_and_discription')
               <div class="col-lg-6">
                    <div class="card shadow--card-2 border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex flex-column align-items-center gap-3 p-10">
                                <p class="mb-0">{{ translate('Food_Image') }}</p>

                                @include('admin-views.partials._image-uploader', [
                                    'id' => 'image-input',
                                    'name' => 'image',
                                    'isRequired' => true,
                                    'existingImage' => null,
                                    'ratio' => '1:1',
                                    'imageExtension' => IMAGE_EXTENSION,
                                    'imageFormat' => IMAGE_FORMAT,
                                    'maxSize' => 1,
                                    ])
                            </div>
                        </div>
                    </div>
                </div>

                @includeif('admin-views.product.partials._category_and_general')
                @includeif('admin-views.product.partials._price_and_stock')
                @includeif('admin-views.product.partials._food_variations')
                @includeif("admin-views.product.partials._ai_sidebar")
                <div class="col-lg-12">
                    @includeif('admin-views.product.partials._seo-section')
                </div>
                <div class="col-lg-12">
                    <div class="btn--container justify-content-end">
                        <button type="reset" id="reset_btn"
                            class="btn btn--reset">{{ translate('messages.reset') }}</button>
                        <button type="submit"
                            class="btn btn--primary">{{ translate('messages.submit') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection

@push('script')

@endpush

@push('script_2')
    <script src="{{dynamicAsset('assets/admin')}}/js/tags-input.min.js"></script>
    <script src="{{dynamicAsset('assets/admin')}}/js/view-pages/vendor/product-index.js"></script>

    <script src="{{ dynamicAsset('assets/admin/js/AI/products/product-title-autofill.js') }}"></script>
    <script src="{{ dynamicAsset('assets/admin/js/AI/products/product-description-autofill.js') }}"></script>
    <script src="{{ dynamicAsset('assets/admin/js/AI/products/general-setup-autofill.js') }}"></script>
    <script src="{{ dynamicAsset('assets/admin/js/AI/products/product-others-autofill.js') }}"></script>
    <script src="{{ dynamicAsset('assets/admin/js/AI/products/variation-setup-auto-fill.js') }}"></script>
    {{-- <script src="{{ dynamicAsset('assets/admin/js/AI/products/seo-section-autofill.js') }}"></script> --}}

    <script src="{{ dynamicAsset('assets/admin/js/AI/products/ai-sidebar.js') }}"></script>

    <script src="{{ dynamicAsset('assets/admin/js/AI/products/compressor/image-compressor.js')}}"></script>
    <script src="{{ dynamicAsset('assets/admin/js/AI/products/compressor/compressor.min.js')}}"></script>
<script>
    "use strict";

let count = 0;
    $(document).ready(function() {
        $('#tax__rate').select2({
            placeholder: $('#tax__rate').data('placeholder'),
            allowClear: true
        });
    });


    $('#stock_type').on('change', function () {
                if($(this).val() == 'unlimited') {
                    $('.stock_disable').prop('readonly', true).prop('required', false).attr('placeholder', '{{ translate('Unlimited') }}').val('');
                    $('.hide_this').addClass('d-none');
                } else {
                    $('.stock_disable').prop('readonly', false).prop('required', true).attr('placeholder', '{{ translate('messages.Ex:_100') }}');
                        $('.hide_this').removeClass('d-none');
                }
        });

        updatestockCount();

        function updatestockCount(){
            if($('#stock_type').val()==  'unlimited'){
                    $('.stock_disable').prop('readonly', true).prop('required', false).attr('placeholder', '{{ translate('Unlimited') }}').val('');
                    $('.hide_this').addClass('d-none');
                } else{
                    $('.stock_disable').prop('readonly', false).prop('required', true).attr('placeholder', '{{ translate('messages.Ex:_100') }}');
                    $('.hide_this').removeClass('d-none');
                }
        }

        function loadRestaurantCategories(selectedId = '') {
            let $restaurantCategory = $('#restaurant_category_id');
            $restaurantCategory.attr('data-id', selectedId);

            $.get({
                url: '{{ route('vendor.food.get-restaurant-categories') }}',
                data: {
                    restaurant_id: $('#restaurant_id').val(),
                    selected_id: selectedId
                },
                dataType: 'json',
                success: function(data) {
                    $restaurantCategory.empty().append(data.options).val(selectedId).trigger('change');
                    // Toggle the "*" mark + required attribute based on whether
                    // this restaurant has any of its own categories.
                    const required = !!(data && data.has_categories);
                    $restaurantCategory.prop('required', required);
                    $('.restaurant-category-required-mark').toggle(required);
                },
            });
        }



        $(document).ready(function() {
            $("#add_new_option_button").click(function() {
                    add_new_option_button();
            });
            loadRestaurantCategories();
        });



        function add_new_option_button() {
            $('#empty-variation').hide();
                count++;
                let add_option_view = `
                        <div class="__bg-F8F9FC-card view_new_option mb-2">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <label class="form-check form--check">
                                        <input id="options[` + count + `][required]" name="options[` + count + `][required]"
                                            class="form-check-input" type="checkbox">
                                        <span class="form-check-label">{{ translate('Required') }}</span>
                                    </label>
                                    <div>
                                        <button type="button" class="btn btn-danger btn-sm delete_input_button"
                                            title="{{ translate('Delete') }}">
                                            <i class="tio-add-to-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-xl-4 col-lg-6">
                                        <label for="">{{ translate('name') }}&nbsp;<span class="form-label-secondary text-danger"
                                                data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ translate('messages.Required.') }}"> *
                                            </span></label>
                                        <input required name=options[` + count + `][name] class="form-control new_option_name" type="text"
                                            data-count="` +  count + `">
                                    </div>

                                    <div class="col-xl-4 col-lg-6">
                                        <div>
                                            <label class="input-label text-capitalize d-flex align-items-center"><span
                                                    class="line--limit-1">{{ translate('messages.Variation_Selection_Type') }} </span>
                                            </label>
                                            <div class="resturant-type-group px-0">
                                                <label class="form-check form--check mr-2 mr-md-4">
                                                    <input class="form-check-input show_min_max" data-count="` + count + `" type="radio"
                                                        value="multi" name="options[` + count + `][type]" id="type` + count + `" checked>
                                                    <span class="form-check-label">
                                                        {{ translate('Multiple Selection') }}
                                                    </span>
                                                </label>

                                                <label class="form-check form--check mr-2 mr-md-4">
                                                    <input class="form-check-input hide_min_max" data-count="` + count + `" type="radio"
                                                        value="single" name="options[` + count + `][type]" id="type` + count +  `">
                                                    <span class="form-check-label">
                                                        {{ translate('Single Selection') }}
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-6">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label for="">{{ translate('Min') }}</label>
                                                <input id="min_max1_` + count + `" required name="options[` + count + `][min]"
                                                    class="form-control" type="number" min="1">
                                            </div>
                                            <div class="col-6">
                                                <label for="">{{ translate('Max') }}</label>
                                                <input id="min_max2_` + count + `" required name="options[` + count + `][max]"
                                                    class="form-control" type="number" min="1">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="option_price_` + count + `">
                                    <div class="bg-white border rounded p-3 pb-0 mt-3">
                                        <div id="option_price_view_` + count + `">
                                            <div class="row g-3 add_new_view_row_class mb-3">
                                                <div class="col-md-3 col-sm-6">
                                                    <label for="">{{ translate('Option_name') }} &nbsp;<span
                                                            class="form-label-secondary text-danger" data-toggle="tooltip"
                                                            data-placement="right"
                                                            data-original-title="{{ translate('messages.Required.') }}"> *
                                                        </span></label>
                                                    <input class="form-control" required type="text"
                                                        name="options[` + count +  `][values][0][label]" id="">
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <label for="">{{ translate('Additional_price') }}
                                                        ({{ \App\CentralLogics\Helpers::currency_symbol() }})&nbsp;<span
                                                            class="form-label-secondary text-danger" data-toggle="tooltip"
                                                            data-placement="right"
                                                            data-original-title="{{ translate('messages.Required.') }}"> *
                                                        </span></label>
                                                    <input class="form-control" required type="number" min="0" step="0.01"
                                                        name="options[` + count + `][values][0][optionPrice]" id="">
                                                </div>
                                                <div class="col-md-3 col-sm-6 hide_this">
                                                    <label for="">{{ translate('Stock') }}</label>
                                                    <input class="form-control stock_disable count_stock" required type="number"
                                                        min="0" max="9999999" name="options[` +  count + `][values][0][total_stock]"
                                                        id="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3 p-3 mr-1 d-flex " id="add_new_button_` + count + `">
                                            <button type="button" class="btn btn--primary btn-outline-primary add_new_row_button"
                                                data-count="` + count + `">{{ translate('Add_New_Option') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                `;

                $("#add_new_option").append(add_option_view);
                updatestockCount();
        }



   function add_new_row_button(data) {
           var countRow = 1 + $('#option_price_view_' + data).children('.add_new_view_row_class').length;
            let add_new_row_view = `
            <div class="row add_new_view_row_class mb-3 position-relative pt-3 pt-sm-0">
                <div class="col-md-3 col-sm-5">
                        <label for="">{{ translate('Option_name') }} &nbsp;<span class="form-label-secondary text-danger"
                                data-toggle="tooltip" data-placement="right"
                                data-original-title="{{ translate('messages.Required.')}}"> *
                                </span></label>
                        <input class="form-control" required type="text" name="options[` + data + `][values][` + countRow + `][label]" id="">
                    </div>
                    <div class="col-md-3 col-sm-5">
                        <label for="">{{ translate('Additional_price') }} ({{ \App\CentralLogics\Helpers::currency_symbol() }})&nbsp;<span class="form-label-secondary text-danger"
                                data-toggle="tooltip" data-placement="right"
                                data-original-title="{{ translate('messages.Required.')}}"> *
                                </span></label>
                        <input class="form-control"  required type="number" min="0" step="0.01" name="options[` +
                        data + `][values][` + countRow + `][optionPrice]" id="">
                    </div>


                    <div class="col-md-3 col-sm-6 hide_this">
                                                <label for="">{{ translate('Stock') }}</label>
                                                <input class="form-control stock_disable count_stock" required type="number" min="0" max="9999999" name="options[` +
                                                data + `][values][` + countRow + `][total_stock]" id="">
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
            updatestockCount();

        }

     function validateImageSize(inputSelector,imageType="Image", maxSizeMB = 2) {
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


    $('#food_form').on('submit', function () {
         if (!validateImageSize('#image-input',"Food image") || !validateImageSize('#image-input2',"SEO image")) {
               return;
         }

        const $submitButton = $(this).find('button[type="submit"]');
        $submitButton.attr('disabled', true).text('Submitting...');

        var formData = new FormData(this);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.post({
            url: '{{route('vendor.food.store')}}',
            data: $('#food_form').serialize(),
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#loading').show();
            },
            success: function (data) {
                $('#loading').hide();
                if (data.errors) {
                    for (var i = 0; i < data.errors.length; i++) {
                        toastr.error(data.errors[i].message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                    $submitButton.attr('disabled', false).text('{{translate('messages.submit')}}');
                } else {
                    toastr.success('{{translate('messages.product_added_successfully')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    setTimeout(function () {
                        location.href = '{{route('vendor.food.list')}}';
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


    $('#reset_btn').click(function() {
            $('#restaurant_id').val(null).trigger('change');
            $('#category_id').val(null).trigger('change');
            $('#categories').val(null).trigger('change');
            $('#sub-veg').val(0).trigger('change');
            $('#add_on').val(null).trigger('change');
            $('#viewer').attr('src', "{{ dynamicAsset('assets/admin/img/upload.png') }}");
            $('#stock_type').val('unlimited').trigger('change');
            updatestockCount();
        })
</script>
@endpush
