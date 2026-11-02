<?php use App\CentralLogics\Helpers;
 use App\Models\BusinessSetting;
 use App\Models\Order;
 use App\Models\Restaurant;
 use App\Models\Zone;
 use App\Scopes\RestaurantScope; ?>


<?php $__env->startSection('title', translate('messages.pos')); ?>
<?php $__env->startSection('content'); ?>

    <div id="pos-div" class="initial-51">
        <section class="section-content padding-y-sm bg-default mt-1">
            <div class="container-fluid content">
                <div class="d-flex flex-wrap">
                   <div class="order--pos-left order__left-scrolling">
                        <div class="card padding-y-sm card">
                            <div class="card-header bg-light border-0">
                                <h5 class="card-title">
                                <span class="card-header-icon">
                                    <i class="tio-fastfood"></i>
                                </span>
                                    <span>
                                    <?php echo e(translate('food_section')); ?>

                                </span>
                                </h5>
                            </div>
                            <div class="card-header border-0 pt-4">
                                <div class="w-100">
                                    <div class="row g-3 justify-content-around">
                                        <div class="col-sm-6">
                                            <label for="zone_id" class="text-dark"><?php echo e(translate('zone')); ?> <span class="text-danger">*</span></label>
                                            <select name="zone_id"
                                                    class="form-control js-select2-custom h--45x set-filter"
                                                    data-url="<?php echo e(url()->full()); ?>" data-filter="zone_id" id="zone_id">
                                                <option value="" selected disabled><?php echo e(translate('Select_Zone')); ?> <span>*</span>
                                                </option>
                                                <?php $__currentLoopData = Zone::active()->orderBy('name')->get(['id','name']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $z): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($z['id']); ?>"
                                                        <?php echo e(isset($zone) && $zone->id == $z['id'] ? 'selected' : ''); ?>>
                                                        <?php echo e($z['name']); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                            </select>
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="restaurant_id" class="text-dark"><?php echo e(translate('Restaurant')); ?> <span class="text-danger">*</span></label>
                                            <select name="restaurant_id"
                                                    data-url="<?php echo e(url()->full()); ?>" data-filter="restaurant_id"
                                                    data-placeholder="<?php echo e(translate('messages.select_restaurant')); ?>"
                                                    class="form-control js-select2-custom h--45x set-filter"
                                                    id="restaurant_id" disabled>

                                                <option value=""><?php echo e(translate('Select_a_restaurant')); ?></option>
                                                <?php $__currentLoopData = Restaurant::active()->where('active', 1)->orderBy('name')->where('zone_id',request('zone_id'))->get(['id','name']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $restaurant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option
                                                        value="<?php echo e($restaurant['id']); ?>" <?php echo e(request('restaurant_id') && request('restaurant_id')==$restaurant->id? 'selected':''); ?>>
                                                        <?php echo e($restaurant->name); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="category_id" class="text-dark"><?php echo e(translate('Categories')); ?> </label>
                                            <select name="category" id="category"
                                                    class="form-control js-select2-custom mx-1 h--45x set-filter"
                                                    title="<?php echo e(translate('Select_Category')); ?>"
                                                    data-url="<?php echo e(url()->full()); ?>" data-filter="category_id"
                                                    disabled>
                                                <option value="" disabled selected><?php echo e(translate('Select_Categories')); ?></option>
                                                <option value="all" <?php echo e(request()?->category_id == 'all' ? 'selected' : ''); ?> ><?php echo e(translate('All_Categories')); ?></option>
                                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($item->id); ?>"
                                                        <?php echo e($category == $item->id ? 'selected' : ''); ?>>
                                                        <?php echo e(Str::limit($item->name, 20, '...')); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="search_id" class="text-dark"></label>
                                            <form id="search-form" class="mw-100 mt-sm-2">
                                                <div class="input-group input-group-merge input-group-flush w-100">
                                                    <div class="input-group-prepend pl-2">
                                                        <div class="input-group-text">
                                                            <i class="tio-search"></i>
                                                        </div>
                                                    </div>
                                                    <input id="datatableSearch" type="search"
                                                           value="<?php echo e($keyword ?? ''); ?>" name="keyword"
                                                           class="form-control flex-grow-1 pl-5 border rounded h--45x"
                                                           placeholder="<?php echo e(translate('Ex:_Search_Food_Name')); ?>"
                                                           aria-label="<?php echo e(translate('messages.search_here')); ?>"
                                                           disabled>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center" id="items">
                                <?php if(!$products->isEmpty()): ?>
                                    <div class="row g-3 mb-auto order__left-scrolling-body ">
                                        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="order--item-box item-box col-auto">
                                                <?php echo $__env->make('admin-views.pos._single_product', [
                                                    'product' => $product,
                                                    'restaurant_data ' => $restaurant_data,
                                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php else: ?>

                                    <div class="my-auto">
                                        <div class="search--no-found">
                                            <img src="<?php echo e(dynamicAsset('assets/admin/img/search-icon.png')); ?>" alt="img">
                                            <p>
                                                <?php echo e(translate('messages.To get accurate search results, first select a zone, then choose a restaurant. You can then browse food by category or search manually within that restaurant.')); ?>

                                            </p>
                                        </div>
                                    </div>

                                <?php endif; ?>
                            </div>
                            <div class="card-footer border-0 pt-0">
                                <?php echo $products->withQueryString()->links(); ?>

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
                                            <?php echo e(translate('Billing Section')); ?>

                                        </span>
                                    </h5>
                                </div>
                                <div class="w-100 px-1 mt-1">
                                    <div class="bg-light rounded p-2 mb-10px">
                                        <div class="d-flex flex-wrap flex-row add--customer-btn mb-10px">
                                            <label for='customer'></label>
                                            <select id='customer' name="customer_id"
                                                    data-url="<?php echo e(route('admin.pos.getUserData')); ?>"
                                                    data-clear-data-url="<?php echo e(route('admin.pos.clearUserData')); ?>"
                                                    data-placeholder="<?php echo e(translate('Select Customer')); ?>"
                                                    class="js-data-example-ajax form-control">
                                                    <?php if(isset($customer)): ?>
                                                    <option value="<?php echo e($customer->id); ?>"><?php echo e($customer->f_name . ' ' . $customer->l_name); ?> (<?php echo e($customer->phone); ?>) </option>
                                                    <?php endif; ?>
                                                </select>
                                            <button class="btn btn--primary" data-toggle="modal"
                                                    data-target="#add-customer"><?php echo e(translate('Add New Customer')); ?></button>
                                        </div>



                                        <div  id="customer_data" class="<?php echo e(isset($customer) ? 'd-flex': 'd-none'); ?> bg-white  flex-column gap-2 rounded py-lg-3 py-2 px-xl-3 px-2">
                                            <div class="d-flex gap-3">
                                                <span class="fs-13 min-w-50"><?php echo e(translate('Name')); ?></span>
                                                <span>:</span>
                                                <div>
                                                    <span id="customer_name" class="text-dark"><?php echo e(isset($customer) ? $customer->f_name . ' ' . $customer->l_name : ''); ?></span>
                                                    <span id="customer_phone"><?php echo e(isset($customer) ? $customer->phone: ''); ?></span>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-3">
                                                <span class="fs-13 min-w-50"><?php echo e(translate('Email')); ?></span>
                                                <span>:</span>
                                                <div>
                                                    <span class="text-dark" id="customer_email"><?php echo e(isset($customer) ? $customer->email : ''); ?></span>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-3">
                                                <span class="fs-13 min-w-50"><?php echo e(translate('Wallet')); ?></span>
                                                <span>:</span>
                                                <div>
                                                    <span class="text-primary font-semibold" id="customer_wallet" ><?php echo e(isset($customer) ?  Helpers::format_currency($customer->wallet_balance) : ''); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                        <div class="bg-light rounded p-2 mb-10px">
                                            <h6 class="font-semibold mb-2 fs-14"><?php echo e(translate('Select Order Type')); ?></h6>
                                            <div class="bg-white d-flex align-items-center flex-wrap rounded py-2 px-md-3 px-2 border mb-10px order-type-radio-group">
                                                <?php if(  Helpers::get_business_settings('take_away')&& (!$restaurant_data|| $restaurant_data?->take_away == 1)): ?>
                                                <div class="check-item p-0 w-33p-customize">
                                                    <div class="form-group mb-0 form-check form--check ps__22 d-flex align-items-center gap-2">
                                                        <input type="radio" name="order_type" data-user-address-url="<?php echo e(route('admin.pos.getUserAddress')); ?>" data-url="<?php echo e(route('admin.pos.setOrderType')); ?>" value="take_away" class="form-check-input mt-0 flex-shrink-0" id="take_a" <?php echo e(session()->get('order_type') == 'take_away' ||  !session()->get('order_type') ? 'checked' : ''); ?> >
                                                        <label class="form-check-label lh-1 ml-0 fs-14 font-normal text-title mb-0" for="take_a"><?php echo e(translate('Take Away')); ?></label>
                                                    </div>
                                                </div>
                                                <?php endif; ?>

                                                <?php if( Helpers::get_business_settings('home_delivery') &&  (!$restaurant_data|| $restaurant_data?->delivery == 1)): ?>
                                                <div class="check-item p-0 w-33p-customize">
                                                    <div class="form-group mb-0 form-check form--check ps__22 d-flex align-items-center gap-2">
                                                        <input type="radio" name="order_type" data-user-address-url="<?php echo e(route('admin.pos.getUserAddress')); ?>" data-url="<?php echo e(route('admin.pos.setOrderType')); ?>" value="delivery" class="form-check-input mt-0 flex-shrink-0" id="Home_a" <?php echo e(session()->get('order_type') == 'delivery' ? 'checked' : ''); ?> >
                                                        <label class="form-check-label lh-1 ml-0 fs-14 font-normal text-title mb-0" for="Home_a"><?php echo e(translate('Home Delivery')); ?></label>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <?php echo $__env->make('partials._pos_delivery_coverage_section', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            <?php echo $__env->make('partials._pos_delivery_type_section', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            <div id="delivery_address_div" class="d-none"> </div>
                                        </div>
                                </div>

                                <div class='w-100' id="cart">
                                    <?php echo $__env->make('admin-views.pos._cart', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </div>
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
                        <?php echo $__env->make('admin-views.pos._pos_mobile_menu', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
        </div>

        </section>

        <div class="modal quick_modal_init fade" id="quick-view" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" id="quick-view-modal">
                </div>
            </div>
        </div>

        <?php if($order): ?>
            <?php (session(['last_order' => false])); ?>
            <div class="modal fade" id="print-invoice" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?php echo e(translate('messages.print_invoice')); ?>

                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body row  pt-0 ff-emoji">
                            <div id="printableArea" class="col-12">
                                <?php echo $__env->make('new_invoice', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="modal fade" id="add-customer" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-light py-3">
                        <h4 class="modal-title"><?php echo e(translate('add_new_customer')); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="<?php echo e(route('admin.pos.customer-store')); ?>" method="post"
                        data-warning-message="<?php echo e(translate('messages.Must_enter_a_valid_phone_number.')); ?>"
                        id="customer-add-form">

                            <?php echo csrf_field(); ?>
                            <div class="row pl-2">
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="f_name" class="input-label"><?php echo e(translate('first_name')); ?> <span
                                                class="input-label-secondary text-danger">*</span></label>
                                        <input id="f_name" type="text" name="f_name" class="form-control"
                                               value="<?php echo e(old('f_name')); ?>"
                                               placeholder="<?php echo e(translate('first_name')); ?>" required>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="l_name" class="input-label"><?php echo e(translate('last_name')); ?> <span
                                                class="input-label-secondary text-danger">*</span></label>
                                        <input id="l_name" type="text" name="l_name" class="form-control"
                                               value="<?php echo e(old('l_name')); ?>" placeholder="<?php echo e(translate('last_name')); ?>"
                                               required>
                                    </div>
                                </div>
                            </div>
                            <div class="row pl-2">
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="email" class="input-label"><?php echo e(translate('email')); ?><span
                                                class="input-label-secondary text-danger">*</span></label>
                                        <input id="email" type="email" name="email" class="form-control"
                                               value="<?php echo e(old('email')); ?>"
                                               placeholder="<?php echo e(translate('Ex:_ex@example.com')); ?>" required>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="phone" class="input-label"><?php echo e(translate('phone')); ?>

                                            (<?php echo e(translate('with_country_code')); ?>)<span
                                                class="input-label-secondary text-danger">*</span></label>
                                        <input id="phone" type="tel" name="phone" class="form-control"
                                               value="<?php echo e(old('phone')); ?>" placeholder="<?php echo e(translate('phone')); ?>"
                                               required>
                                    </div>
                                </div>
                            </div>


                            <div class="btn--container justify-content-end">
                                <button type="reset" class="btn btn--reset"><?php echo e(translate('reset')); ?></button>
                                <button type="submit" id="submit_new_customer"
                                        class="btn btn--primary"><?php echo e(translate('save')); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=<?php echo e(Helpers::get_business_settings('map_api_key')); ?>&libraries=places,marker&callback=initMap&v=3.61">
    </script>
    <script src="<?php echo e(dynamicAsset('assets/admin/js/view-pages/pos.js')); ?>"></script>
    <script>
        window.posDeliveryTypeConfig = {
            setDeliveryTypeUrl: '<?php echo e(route('admin.pos.set-delivery-type')); ?>',
            getDeliveryTypesUrl: '<?php echo e(route('admin.pos.get-delivery-types')); ?>',
            zoneId: '<?php echo e($zone ? $zone->id : ''); ?>',
            zoneMinimumDeliveryTime: <?php echo json_encode($zone ? $zone->minimum_delivery_time : ['value' => 0, 'unit' => 'min'], 512) ?>,
            restaurantDeliveryTime: '<?php echo e($restaurant_data ? $restaurant_data->delivery_time : '0-0-min'); ?>',
            getDeliveryCoverageUrl: '<?php echo e(route('admin.pos.get-delivery-coverage')); ?>',
            setDeliveryCoverageUrl: '<?php echo e(route('admin.pos.set-delivery-coverage')); ?>',
            areaLabel: '<?php echo e(translate('messages.delivery_area')); ?>',
            zipLabel: '<?php echo e(translate('messages.zip_code')); ?>',
            selectPlaceholder: '<?php echo e(translate('messages.select_an_option')); ?>',
            coverageRequiredNote: '<?php echo e(translate('messages.Pick_the_delivery_area_to_price_this_order.')); ?>',
            currencySymbol: '<?php echo e(\App\CentralLogics\Helpers::currency_symbol()); ?>',
            currencySymbolPosition: '<?php echo e(\App\CentralLogics\Helpers::get_business_settings('currency_symbol_position') ?? 'left'); ?>',
            roundUpDigit: <?php echo e(config('round_up_to_digit') ?? 2); ?>,
            requireZoneAndRestaurantSelect: true
        };
    </script>
    <script src="<?php echo e(dynamicAsset('assets/admin/js/view-pages/common-pos-js.js')); ?>"></script>
    <script>



            $(document).ready(function () {
                <?php if(Session::has('address_id') && Session::get('order_type') == 'delivery'): ?>
                    chooseAddress(
                        '<?php echo e(route('admin.pos.chooseAddress')); ?>',
                        '<?php echo e(Session::get('customer_id')); ?>',
                        '<?php echo e(Session::get('address_id')); ?>'
                    );
                <?php endif; ?>
            });



        $(document).on('click', '.place-order-submit', function (event) {
            event.preventDefault();
            let customer_id = document.getElementById('customer');
            if (customer_id.value) {
                document.getElementById('customer_id').value = customer_id.value;


                if(document.getElementById('contact_person_name').value == ''){
                    toastr.error('<?php echo e(translate('messages.contact_person_name_is_missing')); ?>', {
                    CloseButton: true,
                    ProgressBar: true
                });
                }

                else if(document.getElementById('contact_person_number').value.length <= 4){
                    toastr.error("<?php echo e(translate('messages.contact_person_number_is_missing')); ?>", {
                    CloseButton: true,
                    ProgressBar: true
                });
                }
                else if(document.getElementById('longitude').value == '' ||  document.getElementById('latitude').value == ''){
                    toastr.error("<?php echo e(translate('messages.longitude_&_latitude_is_missing')); ?>", {
                    CloseButton: true,
                    ProgressBar: true
                });
                }
                else if(document.getElementById('cart_food_id').value == '' ){
                    toastr.error("<?php echo e(translate('messages.your_cart_is_empty')); ?>", {
                    CloseButton: true,
                    ProgressBar: true
                });
                }
                else{
                        let form = document.getElementById('order_place');
                        form.submit();
                    }

            }
            else {
                toastr.error('<?php echo e(translate('messages.customer_not_selected')); ?>', {
                    CloseButton: true,
                    ProgressBar: true
                });
            }

        });


        const mapId = "<?php echo e(\App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value); ?>";
        const { AdvancedMarkerElement } = google.maps.marker;


        function initMap(lat = null, lng = null) {
            const defaultLat = <?php echo e($restaurant_data['latitude'] ?? 23.757989); ?>;
            const defaultLng = <?php echo e($restaurant_data['longitude'] ?? 90.360587); ?>;

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
                const pos = marker.position;
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

        initMap();


        $(document).on('ready', function () {
            <?php if($order): ?>
            $('#print-invoice').modal('show');
            <?php endif; ?>
        });


        checkZone();
        checkRestZone();

        $('#search-form').on('submit', function (e) {
            e.preventDefault();
            let keyword = $('#datatableSearch').val();
            let nurl = new URL('<?php echo url()->full(); ?>');
            nurl.searchParams.set('keyword', keyword);
            nurl.searchParams.delete('page');
            location.href = nurl;
        });


        $(document).on('click', '.quick-View', function () {
            $.get({
                url: '<?php echo e(route('admin.pos.quick-view')); ?>',
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
                url: '<?php echo e(route('admin.pos.quick-view-cart-item')); ?>',
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
            if ($('#add-to-cart-form input[name=quantity]').val() > 0) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "POST",
                    url: '<?php echo e(route('admin.pos.variant_price')); ?>',
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
                url: '<?php echo e(route('admin.pos.add-to-cart')); ?>',
                data: $('#' + form_id).serializeArray(),
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    if (data.data === 1) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Cart',
                            text: "<?php echo e(translate('messages.product_already_added_in_cart')); ?>"
                        });
                        return false;
                    } else if (data.data === 2) {
                        updateCart();
                        Swal.fire({
                            icon: 'info',
                            title: 'Cart',
                            text: "<?php echo e(translate('messages.product_has_been_updated_in_cart')); ?>"
                        });
                        return false;
                    } else if (data.data === 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Cart',
                            text: 'Sorry, product out of stock.'
                        });
                        return false;
                    } else if (data.data === 'variation_error') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Cart',
                            text: data.message
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
                            text: "<?php echo e(translate('messages.product_quantity_updated_in_cart')); ?>"
                        });
                        updateCart();
                        return false;
                    }
                    $('.call-when-done').click();
                    toastr.success('<?php echo e(translate('messages.product_has_been_added_in_cart')); ?>', {
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


        $(document).on('click', '.delivery-Address-Store', function (event) {
            if(document.getElementById('contact_person_name').value == ''){
                    toastr.error('<?php echo e(translate('messages.contact_person_name_is_missing')); ?>', {
                    CloseButton: true,
                    ProgressBar: true
                });
                event.preventDefault();
                }

                else if(document.getElementById('contact_person_number').value.length <= 4){
                    toastr.error("<?php echo e(translate('messages.contact_person_number_is_missing')); ?>", {
                    CloseButton: true,
                    ProgressBar: true
                });
                event.preventDefault();
                }
                else if(document.getElementById('longitude').value == '' ||  document.getElementById('latitude').value == ''){
                    toastr.error("<?php echo e(translate('messages.longitude_&_latitude_is_missing')); ?>", {
                    CloseButton: true,
                    ProgressBar: true
                });
                event.preventDefault();
                }

                else{

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        }
                    });
                let form_id = 'delivery_address_store';
                let formData = $('#' + form_id).serializeArray();
                formData.push({ name: 'customer_id', value: $('#customer').val() });

                    $.post({
                        url: '<?php echo e(route('admin.pos.add-delivery-address')); ?>',
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
                            updateCart();
                            $('.call-when-done').click();
                        },
                        complete: function () {
                            $('#loading').hide();
                            $('#paymentModal').modal('hide');
                            $('.modal-backdrop').remove()
                        }
                    });
                }
        });

        function payableAmount(form_id = 'payable_store_amount') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.post({
                url: '<?php echo e(route('admin.pos.paid')); ?>',
                data: $('#' + form_id).serializeArray(),
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function () {
                    updateCart();
                    $('.call-when-done').click();
                },
                complete: function () {
                    $('#loading').hide();
                    $('#insertPayableAmount').modal('hide');
                }
            });
        }

        $(document).on('click', '.remove-From-Cart', function () {
            let key = $(this).data('product-id');
            $.post('<?php echo e(route('admin.pos.remove-from-cart')); ?>', {
                _token: '<?php echo e(csrf_token()); ?>',
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
                    toastr.info('<?php echo e(translate('messages.item_has_been_removed_from_cart')); ?>', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }

            });
        });

        $(document).on('click', '.empty-Cart', function (event) {

                $.post('<?php echo e(route('admin.pos.emptyCart')); ?>', {
                    _token: '<?php echo e(csrf_token()); ?>'
                }, function () {

                    updateCart();
                    toastr.success('<?php echo e(translate('messages.Cart_Cleared_Successfully')); ?>', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                });

                 setTimeout(function () {
                    location.reload();
                }, 200);

        });

        function updateCart() {
            $.post('<?php echo e(route('admin.pos.cart_items')); ?>?restaurant_id=<?php echo e(request('restaurant_id')); ?>', {
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

            let key = element.data('key');
            let option_ids = element.data('option_ids');
            let food_id = element.data('food_id');

            let oldvalue = element.data('value');
            if (valueCurrent >= minValue && maxValue >= valueCurrent) {
                $.post('<?php echo e(route('admin.pos.updateQuantity')); ?>', {
                    _token: '<?php echo e(csrf_token()); ?>',
                    key: key,
                    option_ids: option_ids,
                    food_id: food_id,
                    quantity: valueCurrent
                }, function (data) {
                    if(data.data == 'stock_out'){
                        element.val(oldvalue);
                        Swal.fire({
                            icon: 'error',
                            title: "<?php echo e(translate('Cart')); ?>",
                            text: data.message
                        });
                    }
                    else{
                        updateCart();
                    }
                });
            } else {
                element.val(oldvalue);
                Swal.fire({
                    icon: 'error',
                    title: "<?php echo e(translate('Cart')); ?>",
                    text: "<?php echo e(translate('quantity_unavailable')); ?>"
                });
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
        $(document).on('change', '#customer', function (event) {
            var selectedOption = $(this).find('option:selected');
            var selectedText = selectedOption.text().trim();
            var parts = selectedText.split("(");
            document.getElementById('contact_person_name').value = parts[0];
            document.getElementById('contact_person_number').value = parts[1].replace(/[()]/g, '');
        });


        $('#customer').select2({
            ajax: {
                url: '<?php echo e(route('admin.pos.customers')); ?>',
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

        document.querySelectorAll('[name="keyword"]').forEach(function(element) {
            element.addEventListener('input', function(event) {
                const urlParams = new URLSearchParams(window.location.search);
                if (this.value === "" && urlParams.has('keyword')) {
                        var nurl = new URL('<?php echo url()->full(); ?>');
                        nurl.searchParams.delete("keyword");
                        location.href = nurl;
                }
            });
        });
       $('.map_custom-controls input').removeAttr('style');



        $(document).on('submit', '#order_place', function (event) {
            event.preventDefault();
            let customer_id = document.getElementById('customer');
            if (customer_id.value) {
                document.getElementById('customer_id').value = customer_id.value;
            }
            let form = document.getElementById('order_place');
            form.submit();
        });


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
                    .html(shortText + '… <a href="#" class="see-toggle" data-state="collapsed"><?php echo e(translate('messages.see_more')); ?></a>');
            });

            $(document).off('click', '.see-toggle').on('click', '.see-toggle', function (e) {
                e.preventDefault();
                const $a = $(this);
                const $desc = $a.closest('.product-description');
                const state = $a.attr('data-state');

                const seeMoreText = "<?php echo e(translate('messages.see_more')); ?>";
                const seeLessText = "<?php echo e(translate('messages.see_less') ?? 'See Less'); ?>";

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

<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/pos/index.blade.php ENDPATH**/ ?>