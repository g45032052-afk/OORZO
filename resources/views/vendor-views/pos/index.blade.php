@php use App\CentralLogics\Helpers;   @endphp
@extends('layouts.vendor.app')

@section('title', translate('messages.pos'))

@section('content')
    <div id="pos-div" class="content container-fluid">

        <div class="d-flex flex-wrap">
           <div class="order--pos-left order__left-scrolling">
                <div class="card">
                    <div class="card-header bg-light border-0">
                        <h5 class="card-title">
                            <span>
                                {{ translate('Food Section') }}
                            </span>
                        </h5>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center" id="items">
                        <div class="row g-2 mb-4">
                            <div class="col-sm-6">
                                <div class="input-group">
                                      <select name="category" id="category"
                                            class="form-control js-select2-custom set-filter"
                                            data-url="{{ url()->full() }}" data-filter="category_id"
                                            title="{{ translate('messages.select_category') }}">
                                        <option value="">{{ translate('messages.all_categories') }}</option>
                                        @foreach ($categories as $item)
                                            <option
                                                value="{{ $item->id }}" {{ $category == $item->id ? 'selected' : '' }}>
                                                {{ Str::limit($item->name, 20, '...') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <form id="search-form" class="header-item w-100 mw-100">
                                    <!-- Search -->
                                    <div class="input-group input-group-merge input-group-flush w-100">
                                        <div class="input-group-prepend pl-2">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch" type="search" value="{{ $keyword ?? '' }}"
                                               name="search" class="form-control flex-grow-1 pl-5 border rounded h--45x"
                                               placeholder="{{ translate('messages.Ex : Search Food Name') }}"
                                               aria-label="{{ translate('messages.search_here') }}">
                                    </div>
                                    <!-- End Search -->
                                </form>
                            </div>
                        </div>
                        @if (!$products->isEmpty())
                            <div class="row g-3 mb-auto order__left-scrolling-body ">
                                @foreach ($products as $product)
                                    <div class="order--item-box item-box col-auto">
                                        @include('vendor-views.pos._single_product', [
                                            'product' => $product,
                                            'restaurant_data' => $restaurant_data,
                                        ])
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="my-auto">
                                <div class="search--no-found">
                                    <img src="{{dynamicAsset('assets/admin/img/search-icon.png')}}" alt="img">
                                    <p>
                                        {{ translate('To get required Foods, Select category wise food or search manually to find food under the restaurant') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>


                    <div class="card-footer">
                        {!! $products->withQueryString()->links() !!}
                    </div>
                </div>
            </div>
            <div class="order--pos-right order__pos-right__mobile desktop-order-vh">
                <div class="card">
                    <div class="py-5 px-0 d-md-none d-block">
                        <div class="pos-cross_arrow w-25px h-25px rounded-circle d-center position-absolute top-0 start-0 mt-3 ml-3">
                            <i class="tio-clear text-danger"></i>
                        </div>
                    </div>
                    <div class="card-data-scrolling">
                        <div class="card-header bg-light border-0 m-1 mb-2">
                            <h5 class="card-title text-dark">
                                <span>
                                    {{ translate('Billing Section') }}
                                </span>
                            </h5>
                        </div>
                        <div class="w-100 px-1 mt-1">
                            <div class="bg-light rounded p-2 mb-10px">
                                <div class="d-flex flex-wrap flex-row add--customer-btn mb-10px">
                                    <label for='customer'></label>
                                    <select id='customer' name="customer_id"
                                            data-url="{{ route('vendor.pos.getUserData') }}"
                                            data-clear-data-url="{{ route('vendor.pos.clearUserData') }}"
                                            data-placeholder="{{ translate('messages.walk_in_customer') }}"
                                            class="js-data-example-ajax form-control">
                                            @if (isset($customer))
                                            <option value="{{ $customer->id }}">{{ $customer->f_name . ' ' . $customer->l_name  }} ({{ $customer->phone }}) </option>
                                            @endif
                                        </select>
                                    <button class="btn btn--primary" data-toggle="modal"
                                            data-target="#add-customer">{{ translate('Add New Customer') }}</button>
                                </div>



                                <div  id="customer_data" class="{{ isset($customer) ? 'd-flex': 'd-none' }} bg-white  flex-column gap-2 rounded py-lg-3 py-2 px-xl-3 px-2">
                                    <div class="d-flex gap-3">
                                        <span class="fs-13 min-w-50">{{ translate('Name') }}</span>
                                        <span>:</span>
                                        <div>
                                            <span id="customer_name" class="text-dark">{{ isset($customer) ? $customer->f_name . ' ' . $customer->l_name : '' }}</span>
                                            <span id="customer_phone">{{ isset($customer) ? $customer->phone: '' }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-3">
                                        <span class="fs-13 min-w-50">{{ translate('Email') }}</span>
                                        <span>:</span>
                                        <div>
                                            <span class="text-dark" id="customer_email">{{ isset($customer) ? $customer->email : '' }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-3">
                                        <span class="fs-13 min-w-50">{{ translate('Wallet') }}</span>
                                        <span>:</span>
                                        <div>
                                            <span class="text-primary font-semibold" id="customer_wallet" >{{ isset($customer) ?  Helpers::format_currency($customer->wallet_balance) : ''  }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                                <div class="bg-light rounded p-2 mb-10px">
                                    <h6 class="font-semibold mb-2 fs-14">{{ translate('Select Order Type') }}</h6>
                                    <div class="bg-white d-flex align-items-center flex-wrap rounded py-2 px-md-3 px-2 border mb-10px order-type-radio-group">
                                        @if (Helpers::get_business_settings('take_away')&&   $restaurant_data->take_away == 1)
                                        <div class="check-item p-0 w-33p-customize">
                                            <div class="form-group mb-0 form-check form--check ps__22 d-flex align-items-center gap-2">
                                                <input type="radio" name="order_type" data-user-address-url="{{ route('vendor.pos.getUserAddress') }}" data-url="{{ route('vendor.pos.setOrderType') }}" value="take_away" class="form-check-input mt-0 flex-shrink-0" id="take_a" {{ session()->get('order_type') == 'take_away' ||  !session()->get('order_type') ? 'checked' : '' }} >
                                                <label class="form-check-label lh-1 ml-0 fs-14 font-normal text-title mb-0" for="take_a">{{ translate('Take Away') }}</label>
                                            </div>
                                        </div>
                                        @endif
                                        @if ( Helpers::get_business_settings('dine_in_order_option') && $restaurant_data?->restaurant_config?->dine_in  == 1)
                                        <div class="check-item p-0 w-33p-customize">
                                            <div class="form-group mb-0 form-check form--check ps__22 d-flex align-items-center gap-2">
                                                <input type="radio" name="order_type" data-user-address-url="{{ route('vendor.pos.getUserAddress') }}" data-url="{{ route('vendor.pos.setOrderType') }}" value="dine_in" class="form-check-input mt-0 flex-shrink-0" id="Dine_a" {{ session()->get('order_type') == 'dine_in' ? 'checked' : '' }}>
                                                <label class="form-check-label lh-1 ml-0 fs-14 font-normal text-title mb-0" for="Dine_a">{{ translate('Dine In') }}</label>
                                            </div>
                                        </div>
                                        @endif
                                        @if ( Helpers::get_business_settings('home_delivery') && $restaurant_data->delivery == 1)
                                        <div class="check-item p-0 w-33p-customize">
                                            <div class="form-group mb-0 form-check form--check ps__22 d-flex align-items-center gap-2">
                                                <input type="radio" name="order_type" data-user-address-url="{{ route('vendor.pos.getUserAddress') }}" data-url="{{ route('vendor.pos.setOrderType') }}" value="delivery" class="form-check-input mt-0 flex-shrink-0" id="Home_a" {{ session()->get('order_type') == 'delivery' ? 'checked' : '' }} >
                                                <label class="form-check-label lh-1 ml-0 fs-14 font-normal text-title mb-0" for="Home_a">{{ translate('Home Delivery') }}</label>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    @include('partials._pos_delivery_coverage_section')
                                    @include('partials._pos_delivery_type_section')
                                    <div id="delivery_address_div" class="d-none"> </div>
                                </div>
                        </div>

                        <div class='w-100' id="cart">
                            @include('vendor-views.pos._cart')
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pos-mobile-menu bg-white w-100 py-3 px-sm-4 px-3">
                <div class="pos-collapse-arrow position-absolute rounded-circle d-center w-30px h-30px">
                    <i class="tio-up-ui text-dark"></i>
                </div>
                    <div id="pos_mobile_menu">
                        @include('vendor-views.pos._pos_mobile_menu')
                    </div>
        </div>

        <div class="modal quick_modal_init fade" id="quick-view" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" id="quick-view-modal"> </div>
            </div>
        </div>

        @if ($order)
            @php(session(['last_order' => false]))
            <div class="modal fade" id="print-invoice" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ translate('messages.print_invoice') }}
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body pt-0 row ff-emoji">

                            <div class="col-12" id="printableArea">
                                @include('new_invoice')
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        @endif




        <!-- Add Customer Modal -->
        <div class="modal fade" id="add-customer" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-light py-3">
                        <h4 class="modal-title">{{ translate('add_new_customer') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('vendor.pos.customer-store') }}" method="post"
                         data-warning-message="{{ translate('messages.Must_enter_a_valid_phone_number.') }}" id="customer-add-form">
                            @csrf
                            <div class="row pl-2">
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="f_name" class="input-label">{{ translate('first_name') }} <span
                                                class="input-label-secondary text-danger">*</span></label>
                                        <input id="f_name" type="text" name="f_name" class="form-control"
                                               value="{{ old('f_name') }}" placeholder="{{ translate('first_name') }}"
                                               required>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="l_name" class="input-label">{{ translate('last_name') }} <span
                                                class="input-label-secondary text-danger">*</span></label>
                                        <input id="l_name" type="text" name="l_name" class="form-control"
                                               value="{{ old('l_name') }}" placeholder="{{ translate('last_name') }}"
                                               required>
                                    </div>
                                </div>
                            </div>
                            <div class="row pl-2">
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="email" class="input-label">{{ translate('email') }}<span
                                                class="input-label-secondary text-danger">*</span></label>
                                        <input id="email" type="email" name="email" class="form-control"
                                               value="{{ old('email') }}"
                                               placeholder="{{ translate('Ex_:_ex@example.com') }}" required>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="phone" class="input-label">{{ translate('phone') }}
                                            ({{ translate('with_country_code') }})<span
                                                class="input-label-secondary text-danger">*</span></label>
                                        <input id="phone" type="tel" name="phone" class="form-control"
                                               value="{{ old('phone') }}" placeholder="{{ translate('phone') }}"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="btn--container justify-content-end">
                                <button type="reset" class="btn btn--reset">{{ translate('reset') }}</button>
                                <button type="submit" id="submit_new_customer"
                                        class="btn btn--primary">{{ translate('save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ Helpers::get_business_settings('map_api_key') }}&libraries=places,marker&callback=initMap&v=3.61">
    </script>
    <script src="{{dynamicAsset('assets/admin/js/view-pages/vendor/pos.js')}}"></script>
    <script>
        window.posDeliveryTypeConfig = {
            setDeliveryTypeUrl: '{{ route('vendor.pos.set-delivery-type') }}',
            getDeliveryTypesUrl: '{{ route('vendor.pos.get-delivery-types') }}',
            zoneId: '{{ $restaurant_data ? $restaurant_data->zone_id : '' }}',
            zoneMinimumDeliveryTime: @json($restaurant_data && $restaurant_data->zone ? $restaurant_data->zone->minimum_delivery_time : ['value' => 0, 'unit' => 'min']),
            restaurantDeliveryTime: '{{ $restaurant_data ? $restaurant_data->delivery_time : '0-0-min' }}',
            getDeliveryCoverageUrl: '{{ route('vendor.pos.get-delivery-coverage') }}',
            setDeliveryCoverageUrl: '{{ route('vendor.pos.set-delivery-coverage') }}',
            areaLabel: '{{ translate('messages.delivery_area') }}',
            zipLabel: '{{ translate('messages.zip_code') }}',
            selectPlaceholder: '{{ translate('messages.select_an_option') }}',
            coverageRequiredNote: '{{ translate('messages.Pick_the_delivery_area_to_price_this_order.') }}',
            currencySymbol: '{{ \App\CentralLogics\Helpers::currency_symbol() }}',
            currencySymbolPosition: '{{ \App\CentralLogics\Helpers::get_business_settings('currency_symbol_position') ?? 'left' }}',
            roundUpDigit: {{ config('round_up_to_digit') ?? 2 }},
            requireZoneAndRestaurantSelect: false
        };
    </script>
    <script src="{{dynamicAsset('assets/admin/js/view-pages/common-pos-js.js')}}"></script>

    <script>
        $(document).on('click', '[name="type"]', function () {
            if($(this).val() == 'wallet'){
                $('#pos--payable-amount').addClass('d-none');
            } else {
                $('#pos--payable-amount').removeClass('d-none');
            }
        });

        $(document).ready(function () {
            @if (Session::has('address_id') && Session::get('order_type') == 'delivery')
                chooseAddress(
                    '{{ route('vendor.pos.chooseAddress') }}',
                    '{{ Session::get('customer_id') }}',
                    '{{ Session::get('address_id') }}'
                );
            @endif
        });



        function initMap(lat = null, lng = null) {
            const defaultLat = {{ $restaurant_data['latitude'] ?? 23.757989 }};
            const defaultLng = {{ $restaurant_data['longitude'] ?? 90.360587 }};
            const mapId = "{{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}";

            const { AdvancedMarkerElement } = google.maps.marker;

                const map = new google.maps.Map(document.getElementById("map"), {
                    zoom: 13,
                    center: {
                        lat: lat != null ? parseFloat(lat) : defaultLat,
                        lng: lng != null ? parseFloat(lng) : defaultLng
                    },
                    mapId: mapId,
                });

            const geocoder = new google.maps.Geocoder();
            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);

            let marker = new AdvancedMarkerElement({
                map,
                draggable: true
            });

            if (lat != null && lng != null) {
                setLocation(parseFloat(lat), parseFloat(lng));

            }
            map.addListener("click", (e) => {
                setLocation(e.latLng.lat(), e.latLng.lng());
            });


            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();
                if (places.length === 0) return;

                const place = places[0];
                if (!place.geometry || !place.geometry.location) return;

                setLocation(place.geometry.location.lat(), place.geometry.location.lng());
                map.panTo(place.geometry.location);
                map.setZoom(15);
            });

            marker.addListener("dragend", () => {
                const pos = marker.getPosition();
                setLocation(pos.lat(), pos.lng());
            });

            function setLocation(lat, lng) {
                document.getElementById("latitude").value = lat;
                document.getElementById("longitude").value = lng;

                const latlng = { lat: lat, lng: lng };
                marker.position = latlng;

                geocoder.geocode({ location: latlng }, (results, status) => {
                    if (status === "OK" && results[0]) {
                        document.getElementById("address").value = results[0].formatted_address;
                    }
                });
            }
        }




        $("#insertPayableAmount").on('keydown', function (e) {
            if (e.keyCode === 13) {
                e.preventDefault();
            }
        })

        $(document).on('ready', function () {
            @if ($order)
            $('#print-invoice').modal('show');
            @endif
        });


        $('#search-form').on('submit', function (e) {
            e.preventDefault();
            let keyword = $('#datatableSearch').val();
            let nurl = new URL('{!! url()->full() !!}');
            nurl.searchParams.set('keyword', keyword);
            location.href = nurl;
        });


        $(document).on('click', '.quick-View', function () {
            $.get({
                url: '{{ route('vendor.pos.quick-view') }}',
                dataType: 'json',
                data: {
                    product_id: $(this).data('id')
                },
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#quick-view').modal('show');
                    $('#quick-view-modal').empty().html(data.view);
                    initProductDescription();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });

        $(document).on('click', '.quick-View-Cart-Item', function () {
            $.get({
                url: '{{ route('vendor.pos.quick-view-cart-item') }}',
                dataType: 'json',
                data: {
                    product_id: $(this).data('product-id'),
                    item_key: $(this).data('item-key'),
                },
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#quick-view').modal('show');
                    $('#quick-view-modal').empty().html(data.view);
                    initProductDescription();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });



        function getVariantPrice() {
            getCheckedInputs();
            if ($('#add-to-cart-form input[name=quantity]').val() > 0 ) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "POST",
                    url: '{{ route('vendor.pos.variant_price') }}',
                    data: $('#add-to-cart-form').serializeArray(),
                    success: function (data) {
                        if (data.error === 'quantity_error') {
                            toastr.error(data.message);
                        }
                        else if(data.error === 'stock_out'){
                            toastr.warning(data.message);
                            if(data.type == 'addon'){
                                $('#addon_quantity_button'+data.id).attr("disabled", true);
                                $('#addon_quantity_input'+data.id).val(data.current_stock);
                            }

                            else{
                                $('#quantity_increase_button').attr("disabled", true);
                                $('#add_new_product_quantity').val(data.current_stock);
                            }
                            getVariantPrice();
                        }

                        else {
                            $('#add-to-cart-form #chosen_price_div').removeClass('d-none');
                            $('#add-to-cart-form #chosen_price_div #chosen_price').html(data.price);
                            $('.add-To-Cart').removeAttr("disabled");
                            $('.increase-button').removeAttr("disabled");
                            $('#quantity_increase_button').removeAttr("disabled");

                        }
                    }
                });
            }
        }

        $(document).on('click', '.add-To-Cart', function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            let form_id = 'add-to-cart-form';
            $.post({
                url: '{{ route('vendor.pos.add-to-cart') }}',
                data: $('#' + form_id).serializeArray(),
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {

                    if (data.data === 1) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Cart',
                            text: "{{ translate('messages.product_already_added_in_cart') }}"
                        });
                        return false;
                    } else if (data.data === 2) {
                        updateCart();
                        Swal.fire({
                            icon: 'info',
                            title: 'Cart',
                            text: "{{ translate('messages.product_has_been_updated_in_cart') }}"
                        });

                        return false;
                    } else if (data.data === 'stock_out') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Cart',
                            text: data.message
                        });
                        return false;
                    } else if (data.data === 'cart_readded') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Cart',
                            text: "{{ translate('messages.product_quantity_updated_in_cart') }}"
                        });
                        updateCart();
                        return false;
                    } else if (data.data === 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Cart',
                            text: '{{ translate('messages.Sorry, product out of stock') }}'
                        });
                        return false;
                    } else if (data.data === 'variation_error') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Cart',
                            text: data.message
                        });
                        return false;
                    }
                    $('.call-when-done').click();

                    toastr.success('{{ translate('messages.product_has_been_added_in_cart') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });

                    updateCart();
                },
                complete: function () {
                    $('#loading').hide();
                }
            });
        });

        $(document).on('click', '.remove-From-Cart', function () {
            let key = $(this).data('product-id');
            $.post('{{ route('vendor.pos.remove-from-cart') }}', {
                _token: '{{ csrf_token() }}',
                key: key
            }, function (data) {
                if (data.errors) {
                    for (let i = 0; i < data.errors.length; i++) {
                        toastr.error(data.errors[i].message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                } else {
                    $('#quick-view').modal('hide');
                    updateCart();
                    toastr.info('{{ translate('messages.item_has_been_removed_from_cart') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }

            });
        });

        $(document).on('click', '.empty-Cart', function () {
            $.post('{{ route('vendor.pos.emptyCart') }}', {
                _token: '{{ csrf_token() }}'
            }, function () {
                $('#del-add').empty();


                toastr.info('{{ translate('messages.item_has_been_removed_from_cart') }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
                setTimeout(function () {
                    location.reload();
                }, 200);
            });
        });

        function updateCart() {
            $.post('<?php echo e(route('vendor.pos.cart_items')); ?>', {
                _token: '<?php echo e(csrf_token()); ?>'
            }, function (data) {
                $('#cart').empty().html(data.cart);
                $('#pos_mobile_menu').empty().html(data.pos_mobile_menu);
                // Tooltips are bound once on document ready, so anything swapped in here
                // would carry the attribute and never open.
                $('#cart [data-toggle="tooltip"]').tooltip();
                refreshDeliveryTypeSectionState();
            });
        }

        $(document).on('click', '.delivery-Address-Store', function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            let form_id = 'delivery_address_store';
                let formData = $('#' + form_id).serializeArray();
                formData.push({ name: 'customer_id', value: $('#customer').val() });
            $.post({
                url: '{{ route('vendor.pos.add-delivery-info') }}',
                data: formData,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        $('#delivery_address_div').empty().html(data.view);
                        updateCart();
                    }

                },
                complete: function () {
                    $('#loading').hide();
                    $('#paymentModal').modal('hide');
                      setTimeout(function () {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open');
                    }, 300);
                }
            });
        });

        $(document).on('click', '.payable-Amount', function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            let form_id = 'payable_store_amount';
                let formData = $('#' + form_id).serializeArray();
                formData.push({ name: 'total_amount', value: $('#total_amount').val() });

            $.post({
                url: '{{ route('vendor.pos.paid') }}',
                data: formData,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                      if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    }else{
                        updateCart();
                    }
                },
                complete: function () {
                    $('#insertPayableAmount').modal('hide');
                    setTimeout(function () {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open');
                    }, 300);
                    $('#loading').hide();
                }
            });
        });

        $(document).on('change', '[name="quantity"]', function (event) {
            getVariantPrice();
            if($('#option_ids').val() == ''){
                $(this).attr('max', $(this).data('maximum_cart_quantity'));
            }
        });

        $(document).on('change', '.update-Quantity', function (event) {
            let element = $(event.target);
            let minValue = parseInt(element.attr('min'));
            let maxValue = parseInt(element.attr('max'));
            let valueCurrent = parseInt(element.val());
            let option_ids = element.data('option_ids');
            let food_id = element.data('food_id');
            let key = element.data('key');
            let oldvalue = element.data('value');
            if (valueCurrent >= minValue && maxValue >= valueCurrent) {
                $.post('{{ route('vendor.pos.updateQuantity') }}', {
                    _token: '{{ csrf_token() }}',
                    key: key,
                    food_id: food_id,
                    option_ids: option_ids,
                    quantity: valueCurrent
                }, function (data) {
                    if(data.data == 'stock_out'){
                        element.val(oldvalue);
                        Swal.fire({
                            icon: 'error',
                            title: "{{ translate('Cart') }}",
                            text: data.message
                        });
                    }
                    else{
                        updateCart();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: "{{ translate('Cart') }}",
                    text: "{{ translate('quantity_unavailable') }}"
                });
                element.val(oldvalue);
            }
            // Allow: backspace, delete, tab, escape, enter and .
            if (event.type === 'keydown') {
                if ($.inArray(event.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                    // Allow: Ctrl+A
                    (event.keyCode === 65 && event.ctrlKey === true) ||
                    // Allow: home, end, left, right
                    (event.keyCode >= 35 && event.keyCode <= 39)) {
                    // let it happen, don't do anything
                    return;
                }
                // Ensure that it is a number and stop the keypress
                if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) && (event.keyCode < 96 || event.keyCode > 105)) {
                    event.preventDefault();
                }
            }
        });

        $('.js-data-example-ajax').select2({
            ajax: {
                url: '{{ route('vendor.pos.customers') }}',
                data: function (params) {
                    return {
                        q: params.term,
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

// $(document).on('change', '#discount_input_type', function () {
//     let discountInput = $('#discount_input');
//     let discountInputType = $(this);
//     let maxLimit = (discountInputType.val() === 'percent') ? 100 : 1000000000;
//     discountInput.attr('max', maxLimit);
// });



// document.addEventListener('DOMContentLoaded', function () {
//     let selectElement = document.querySelector('.discount-type');
//     selectElement.addEventListener('change', function () {
//         document.getElementById('discount_input').max = (this.value === 'percent' ? 100 : 1000000000);
//     });
// });


        $(document).on('submit', '#order_place', function (event) {
            event.preventDefault();
            let customer_id = document.getElementById('customer');
            if (customer_id.value) {
                document.getElementById('customer_id').value = customer_id.value;
            }
            let form = document.getElementById('order_place');
            form.submit();
        });


        $('.map_custom-controls input').removeAttr('style');

        function initProductDescription(limit = 200) {
            $('.product-description').each(function () {
                const $desc = $(this);
                const fullHtml  = $.trim($desc.html());
                const plainText = $.trim($desc.text());

                if (plainText.length <= limit) return;

                const shortText = plainText.substring(0, limit);
                $desc.data({ full: fullHtml, short: shortText });

                // collapsed state: clamp + inline "See more"
                $desc.addClass('line-limit-4')
                    .html(shortText + '… <a href="#" class="see-toggle" data-state="collapsed">{{ translate('messages.see_more') }}</a>');
            });

            $(document).off('click', '.see-toggle').on('click', '.see-toggle', function (e) {
                e.preventDefault();
                const $a = $(this);
                const $desc = $a.closest('.product-description');
                const state = $a.attr('data-state');

                const seeMoreText = "{{ translate('messages.see_more') }}";
                const seeLessText = "{{ translate('messages.see_less') ?? 'See Less' }}";

                if (state === 'collapsed') {
                    // expand: remove clamp, show full + inline "See less"
                    $desc.removeClass('line-limit-4')
                        .html($desc.data('full') + ' <a href="#" class="see-toggle" data-state="expanded">' + seeLessText + '</a>');
                } else {
                    // collapse: add clamp back, show short + inline "See more"
                    $desc.addClass('line-limit-4')
                        .html($desc.data('short') + '… <a href="#" class="see-toggle" data-state="collapsed">' + seeMoreText + '</a>');
                }
            });
        }

    </script>
@endpush
