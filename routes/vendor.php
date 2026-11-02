<?php

use App\Http\Controllers\Vendor\AddOnController;
use App\Http\Controllers\Vendor\AdvertisementController;
use App\Http\Controllers\Vendor\BogoOfferController;
use App\Http\Controllers\Vendor\BusinessSettingsController;
use App\Http\Controllers\Vendor\CampaignController;
use App\Http\Controllers\Vendor\CategoryController;
use App\Http\Controllers\Vendor\ConversationController;
use App\Http\Controllers\Vendor\CouponController;
use App\Http\Controllers\Vendor\CustomRoleController;
use App\Http\Controllers\Vendor\DashboardController;
use App\Http\Controllers\Vendor\DeliveryManController;
use App\Http\Controllers\Vendor\EmployeeController;
use App\Http\Controllers\Vendor\FoodController;
use App\Http\Controllers\Vendor\HappyHourController;
use App\Http\Controllers\Vendor\LanguageController;
use App\Http\Controllers\Vendor\OrderController;
use App\Http\Controllers\Vendor\OrderSubscriptionController;
use App\Http\Controllers\Vendor\POSController;
use App\Http\Controllers\Vendor\ProfileController;
use App\Http\Controllers\Vendor\ReportController;
use App\Http\Controllers\Vendor\RestaurantEarningReportController;
use App\Http\Controllers\Vendor\RestaurantController;
use App\Http\Controllers\Vendor\RestaurantCategoryController;
use App\Http\Controllers\Vendor\ReviewController;
use App\Http\Controllers\Vendor\SearchRoutingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Vendor\SubscriptionController;
use App\Http\Controllers\Vendor\VendorTaxReportController;
use App\Http\Controllers\Vendor\WalletController;
use App\Http\Controllers\Vendor\WalletMethodController;

use Illuminate\Http\Request;

Route::group(['namespace' => 'Vendor', 'as' => 'vendor.'], function () {
    Route::group(['middleware' => ['vendor' ,'maintenance']], function () {

        Route::get('lang/{locale}', [LanguageController::class, 'lang'])->name('lang');

        Route::post('search-routing', [SearchRoutingController::class, 'index'])->name('search.routing');
        Route::get('recent-search', [SearchRoutingController::class, 'recentSearch'])->name('recent.search');
        Route::post('store-clicked-route', [SearchRoutingController::class, 'storeClickedRoute'])->name('store.clicked.route');


        Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/get-restaurant-data', [DashboardController::class, 'restaurant_data'])->name('get-restaurant-data');
        Route::post('/store-token', [DashboardController::class, 'updateDeviceToken'])->name('store.token');
        Route::post('/verified-badge/popup-seen', [DashboardController::class, 'verifiedBadgePopupSeen'])->name('shop.verified-badge.popup-seen');
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews')->middleware(['module:reviews' ,'subscription:reviews']);
        Route::post('/store-reply/{id}', [ReviewController::class, 'update_reply'])->name('review-reply')->middleware(['module:reviews' ,'subscription:reviews']);


        Route::group(['prefix' => 'pos', 'as' => 'pos.'], function () {
            Route::post('variant_price', [POSController::class, 'variant_price'])->name('variant_price');
            Route::group(['middleware' => ['module:pos','subscription:pos']], function () {
                Route::get('/', [POSController::class, 'index'])->name('index');
                Route::get('quick-view', [POSController::class, 'quick_view'])->name('quick-view');
                Route::get('quick-view-cart-item', [POSController::class, 'quick_view_card_item'])->name('quick-view-cart-item');
                Route::post('add-to-cart', [POSController::class, 'addToCart'])->name('add-to-cart');
                Route::post('add-delivery-info', [POSController::class, 'addDeliveryInfo'])->name('add-delivery-info');
                Route::post('remove-from-cart', [POSController::class, 'removeFromCart'])->name('remove-from-cart');
                Route::post('cart-items', [POSController::class, 'cart_items'])->name('cart_items');
                Route::get('get-delivery-types', [POSController::class, 'getDeliveryTypes'])->name('get-delivery-types');
                Route::get('set-delivery-type', [POSController::class, 'setDeliveryType'])->name('set-delivery-type');
                Route::get('get-delivery-coverage', [POSController::class, 'getDeliveryCoverage'])->name('get-delivery-coverage');
                Route::get('set-delivery-coverage', [POSController::class, 'setDeliveryCoverage'])->name('set-delivery-coverage');
                Route::post('update-quantity', [POSController::class, 'updateQuantity'])->name('updateQuantity');
                Route::post('empty-cart', [POSController::class, 'emptyCart'])->name('emptyCart');
                Route::post('tax', [POSController::class, 'update_tax'])->name('tax');
                Route::post('paid', [POSController::class, 'update_paid'])->name('paid');
                Route::post('discount', [POSController::class, 'update_discount'])->name('discount');
                Route::get('customers', [POSController::class, 'get_customers'])->name('customers');
                Route::post('order', [POSController::class, 'place_order'])->name('order');
                Route::get('orders', [POSController::class, 'order_list'])->name('orders');
                Route::post('search', [POSController::class, 'search'])->name('search');
                Route::get('order-details/{id}', [POSController::class, 'order_details'])->name('order-details');
                Route::get('invoice/{id}', [POSController::class, 'generate_invoice']);
                Route::post('customer-store', [POSController::class, 'customer_store'])->name('customer-store');

                Route::get('get-user-data', [POSController::class, 'getUserData'])->name('getUserData');
                Route::get('get-user-address', [POSController::class, 'getUserAddress'])->name('getUserAddress');
                Route::get('choose-address', [POSController::class, 'chooseAddress'])->name('chooseAddress');
                Route::get('edit-address', [POSController::class, 'editAddress'])->name('editAddress');
                Route::get('clear-user-data', [POSController::class, 'clearUserData'])->name('clearUserData');
                Route::get('set-order-type', [POSController::class, 'setOrderType'])->name('setOrderType');
            });
        });

        Route::group(['prefix' => 'advertisement', 'as' => 'advertisement.', 'middleware' => ['subscription:advertisement']], function () {

            Route::group(['middleware' => ['module:ads_list']], function () {
                Route::get('/', [AdvertisementController::class, 'index'])->name('index');
                Route::get('details/{advertisement}', [AdvertisementController::class, 'show'])->name('show');
                Route::get('{advertisement}/edit', [AdvertisementController::class, 'edit'])->name('edit');
                Route::put('update/{advertisement}', [AdvertisementController::class, 'update'])->name('update');
                Route::delete('delete/{id}', [AdvertisementController::class, 'destroy'])->name('destroy');
                Route::get('/status', [AdvertisementController::class, 'status'])->name('status');
            });

            Route::group(['middleware' => ['module:new_ads']], function () {
                Route::get('create/', [AdvertisementController::class, 'create'])->name('create');
                Route::post('store', [AdvertisementController::class, 'store'])->name('store');
                Route::get('/copy-advertisement/{advertisement}', [AdvertisementController::class, 'copyAdd'])->name('copyAdd');
                Route::post('/copy-add-post/{advertisement}', [AdvertisementController::class, 'copyAddPost'])->name('copyAddPost');
            });

        });


        // Theme Builder Test Routes
        Route::get('/test', function(){
            // return view('vendor-views.test.theme-builder.index');
            // return view('vendor-views.test.theme-builder.domain-setting');
            // return view('vendor-views.test.theme-builder.blog.list');
            return view('vendor-views.test.theme-builder.blog.create');
        });
        Route::post('/test-menu', function (Request $request) {
            dd($request->all());
        })->name('test.menu');

        Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.'], function () {
            Route::post('order-stats', [DashboardController::class, 'order_stats'])->name('order-stats');
        });

        Route::group(['prefix' => 'category', 'as' => 'category.', 'middleware' => ['module:category','subscription:food']], function () {
            Route::get('get-all', [CategoryController::class, 'get_all'])->name('get-all');
            Route::get('list', [CategoryController::class, 'index'])->name('add');
            Route::get('sub-category-list', [CategoryController::class, 'sub_index'])->name('add-sub-category');
        });

        Route::group(['prefix' => 'restaurant-category', 'as' => 'restaurant-category.', 'middleware' => ['module:category','subscription:food']], function () {
            Route::get('list', [RestaurantCategoryController::class, 'index'])->name('index');
            Route::get('export/{type}', [RestaurantCategoryController::class, 'export'])->name('export');
            Route::get('create', [RestaurantCategoryController::class, 'create'])->name('create');
            Route::post('store', [RestaurantCategoryController::class, 'store'])->name('store');
            Route::get('edit/{id}', [RestaurantCategoryController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [RestaurantCategoryController::class, 'update'])->name('update');
            Route::get('update-priority/{id}', [RestaurantCategoryController::class, 'updatePriority'])->name('priority');
            Route::get('status/{id}/{status}', [RestaurantCategoryController::class, 'status'])->name('status');
            Route::delete('delete/{id}', [RestaurantCategoryController::class, 'delete'])->name('delete');

            // Assign uncategorized foods to a My Category
            Route::get('foods/{id}', [RestaurantCategoryController::class, 'assignFoodsView'])->name('foods');
            Route::get('foods/{id}/search', [RestaurantCategoryController::class, 'searchAssignableFoods'])->name('foods.search');
            Route::post('foods/{id}/assign', [RestaurantCategoryController::class, 'storeAssignedFoods'])->name('foods.assign');
        });

        Route::group(['prefix' => 'custom-role', 'as' => 'custom-role.', 'middleware' => ['module:role_management','subscription:custom_role']], function () {
            Route::get('create', [CustomRoleController::class, 'create'])->name('create');
            Route::post('create', [CustomRoleController::class, 'store'])->name('store');
            Route::get('edit/{id}', [CustomRoleController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [CustomRoleController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [CustomRoleController::class, 'destroy'])->name('delete');
        });

        Route::group(['prefix' => 'delivery-man', 'as' => 'delivery-man.', 'middleware' => ['module:deliveryman','subscription:deliveryman']], function () {
            Route::get('add', [DeliveryManController::class, 'index'])->name('add');
            Route::post('store', [DeliveryManController::class, 'store'])->name('store');
            Route::get('list', [DeliveryManController::class, 'list'])->name('list');
            Route::get('preview/{id}/{tab?}', [DeliveryManController::class, 'preview'])->name('preview');
            Route::get('status/{id}/{status}', [DeliveryManController::class, 'status'])->name('status');
            Route::get('earning/{id}/{status}', [DeliveryManController::class, 'earning'])->name('earning');
            Route::get('edit/{id}', [DeliveryManController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [DeliveryManController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [DeliveryManController::class, 'delete'])->name('delete');
            Route::get('get-deliverymen', [DeliveryManController::class, 'get_deliverymen'])->name('get-deliverymen');

            Route::group(['prefix' => 'reviews', 'as' => 'reviews.'], function () {
                Route::get('list', [DeliveryManController::class, 'reviews_list'])->name('list');
            });
        });

        Route::group(['prefix' => 'employee', 'as' => 'employee.', 'middleware' => ['module:all_employee','subscription:employee']], function () {
            Route::get('add-new', [EmployeeController::class, 'add_new'])->name('add-new');
            Route::post('add-new', [EmployeeController::class, 'store']);
            Route::get('list', [EmployeeController::class, 'list'])->name('list');
            Route::get('list-export', [EmployeeController::class, 'list_export'])->name('export-employee');
            Route::get('edit/{id}', [EmployeeController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [EmployeeController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [EmployeeController::class, 'destroy'])->name('delete');
            Route::post('search', [EmployeeController::class, 'search'])->name('search');
        });

        Route::post('food/food-variation-generate', [FoodController::class, 'food_variation_generator'])->name('food.food-variation-generate');
        Route::group(['prefix' => 'food', 'as' => 'food.', 'middleware' => ['module:food','subscription:food']], function () {
            Route::get('add-new', [FoodController::class, 'index'])->name('add-new');
            Route::post('store', [FoodController::class, 'store'])->name('store');
            Route::get('edit/{id}', [FoodController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [FoodController::class, 'update'])->name('update');
            Route::get('list', [FoodController::class, 'list'])->name('list');
            Route::delete('delete/{id}', [FoodController::class, 'delete'])->name('delete');
            Route::get('status/{id}/{status}', [FoodController::class, 'status'])->name('status');
            Route::get('recommended/{id}/{status}', [FoodController::class, 'recommended'])->name('recommended');
            Route::post('search', [FoodController::class, 'search'])->name('search');
            Route::get('view/{id}', [FoodController::class, 'view'])->name('view');
            Route::get('get-categories', [FoodController::class, 'get_categories'])->name('get-categories');
            Route::get('get-restaurant-categories', [FoodController::class, 'get_restaurant_categories'])->name('get-restaurant-categories');
            Route::get('out-of-stock-list', [FoodController::class, 'stockOutList'])->name('stockOutList');
            Route::post('update-stock', [FoodController::class, 'updateStock'])->name('updateStock');
            Route::post('/add-to-session', [FoodController::class, 'addToSession'])->name('addToSession');
            Route::get('export', [FoodController::class, 'export'])->name('export');


            //Import and export
            Route::get('bulk-import', [FoodController::class, 'bulk_import_index'])->name('bulk-import');
            Route::post('bulk-import', [FoodController::class, 'bulk_import_data']);
            Route::get('bulk-export', [FoodController::class, 'bulk_export_index'])->name('bulk-export-index');
            Route::post('bulk-export', [FoodController::class, 'bulk_export_data'])->name('bulk-export');
        });


        Route::group(['prefix' => 'campaign', 'as' => 'campaign.', 'middleware' => ['module:campaign','subscription:campaign']], function () {
            Route::get('list', [CampaignController::class, 'list'])->name('list');
            Route::get('item/list', [CampaignController::class, 'itemlist'])->name('itemlist');
            Route::get('remove-restaurant/{campaign}/{restaurant}', [CampaignController::class, 'remove_restaurant'])->name('remove-restaurant');
            Route::get('add-restaurant/{campaign}/{restaurant}', [CampaignController::class, 'addrestaurant'])->name('addrestaurant');
            Route::get('status/{id}', [CampaignController::class, 'status'])->name('status');
            Route::get('/view/{campaign}', [CampaignController::class, 'view'])->name('view');
        });

        Route::group(['prefix' => 'bogo-offer', 'as' => 'bogo-offer.', 'middleware' => ['module:bogo_offer']], function () {
            Route::get('list', [BogoOfferController::class, 'list'])->name('list');
            Route::get('detail/{id}', [BogoOfferController::class, 'detail'])->name('detail');
            Route::get('restaurant-foods', [BogoOfferController::class, 'restaurantFoods'])->name('restaurant-foods');
            Route::post('join/{id}', [BogoOfferController::class, 'join'])->name('join');
            Route::post('resubmit/{id}', [BogoOfferController::class, 'resubmit'])->name('resubmit');
            Route::post('respond/{id}/{status}', [BogoOfferController::class, 'respond'])->name('respond');
            Route::delete('leave/{id}', [BogoOfferController::class, 'leave'])->name('leave');
        });

        Route::group(['prefix' => 'happy-hour', 'as' => 'happy-hour.', 'middleware' => ['module:happy_hour']], function () {
            Route::get('list', [HappyHourController::class, 'list'])->name('list');
            Route::get('detail/{id}', [HappyHourController::class, 'detail'])->name('detail');
            Route::post('join/{id}', [HappyHourController::class, 'join'])->name('join');
            Route::post('respond/{id}/{status}', [HappyHourController::class, 'respond'])->name('respond');
            Route::delete('leave/{id}', [HappyHourController::class, 'leave'])->name('leave');
        });

        Route::group(['prefix' => 'wallet', 'as' => 'wallet.', 'middleware' => ['module:my_wallet','subscription:wallet']], function () {
            Route::get('/', [WalletController::class, 'index'])->name('index');
            Route::post('request', [WalletController::class, 'w_request'])->name('withdraw-request');
            Route::delete('close/{id}', [WalletController::class, 'close_request'])->name('close-request');
            Route::get('method-list', [WalletController::class, 'method_list'])->name('method-list');
            Route::post('make-collected-cash-payment', [WalletController::class, 'make_payment'])->name('make_payment');
            Route::post('make-wallet-adjustment', [WalletController::class, 'make_wallet_adjustment'])->name('make_wallet_adjustment');

            Route::get('wallet-payment-list', [WalletController::class, 'wallet_payment_list'])->name('wallet_payment_list');
            Route::get('disbursement-list', [WalletController::class, 'getDisbursementList'])->name('getDisbursementList');
            Route::get('export', [WalletController::class, 'getDisbursementExport'])->name('export');

        });

        Route::group(['prefix' => 'withdraw-method', 'as' => 'wallet-method.', 'middleware' => ['module:wallet_method','subscription:wallet']], function () {
            Route::get('/', [WalletMethodController::class, 'index'])->name('index');
            Route::post('store/', [WalletMethodController::class, 'store'])->name('store');
            Route::get('default/{id}/{default}', [WalletMethodController::class, 'default'])->name('default');
            Route::delete('delete/{id}', [WalletMethodController::class, 'delete'])->name('delete');

            Route::get('edit/{id}', [WalletMethodController::class, 'edit'])->name('edit');
            Route::put('update', [WalletMethodController::class, 'update'])->name('update');
            Route::post('status-update', [WalletMethodController::class, 'status_update'])->name('status-update');
            Route::post('default-status-update', [WalletMethodController::class, 'default_status_update'])->name('default-status-update');

        });


        Route::group(['prefix' => 'coupon', 'as' => 'coupon.', 'middleware' => ['module:coupon','subscription:coupon']], function () {
            Route::get('add-new', [CouponController::class, 'add_new'])->name('add-new');
            Route::post('store', [CouponController::class, 'store'])->name('store');
            Route::get('update/{id}', [CouponController::class, 'edit'])->name('update');
            Route::post('update/{id}', [CouponController::class, 'update']);
            Route::get('status/{id}/{status}', [CouponController::class, 'status'])->name('status');
            Route::delete('delete/{id}', [CouponController::class, 'delete'])->name('delete');
            Route::post('search', [CouponController::class, 'search'])->name('search');
            Route::get('check-code', [CouponController::class, 'checkCode'])->name('check.code');
            Route::get('view/{coupon}', [CouponController::class, 'view'])->name('view');
            Route::get('coupon-export', [CouponController::class, 'coupon_export'])->name('coupon_export');
        });

        Route::group(['prefix' => 'addon', 'as' => 'addon.', 'middleware' => ['module:addon','subscription:addon']], function () {
            Route::get('add-new', [AddOnController::class, 'index'])->name('add-new');
            Route::post('store', [AddOnController::class, 'store'])->name('store');
            Route::get('edit/{id}', [AddOnController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [AddOnController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [AddOnController::class, 'delete'])->name('delete');
            Route::get('status/{id}/{status}', [AddOnController::class, 'status'])->name('status');
            Route::get('export-addons', [AddOnController::class, 'export'])->name('export');

        });

        Route::group(['prefix' => 'order', 'as' => 'order.' , 'middleware' => ['module:regular_order']], function () {
            Route::get('list/{status}', [OrderController::class, 'list'])->name('list');
            Route::put('status-update/{id}', [OrderController::class, 'status'])->name('status-update');
            Route::post('search', [OrderController::class, 'search'])->name('search');
            Route::post('add-to-cart', [OrderController::class, 'add_to_cart'])->name('add-to-cart');
            Route::post('remove-from-cart', [OrderController::class, 'remove_from_cart'])->name('remove-from-cart');
            Route::get('update/{order}', [OrderController::class, 'update'])->name('update');
            Route::get('edit-order/{order}', [OrderController::class, 'edit'])->name('edit');
            Route::get('details/{id}', [OrderController::class, 'details'])->name('details')->withoutMiddleware(['module:regular_order']);
            Route::get('status', [OrderController::class, 'status'])->name('status');
            Route::get('quick-view', [OrderController::class, 'quick_view'])->name('quick-view');
            Route::get('quick-view-cart-item', [OrderController::class, 'quick_view_cart_item'])->name('quick-view-cart-item');
            Route::get('generate-invoice/{id}', [OrderController::class, 'generate_invoice'])->name('generate-invoice')->withoutMiddleware(['module:regular_order']);
            Route::post('add-payment-ref-code/{id}', [OrderController::class, 'add_payment_ref_code'])->name('add-payment-ref-code');

            Route::get('orders-export/{status}', [OrderController::class, 'orders_export'])->name('export');
            Route::post('add-order-proof/{id}', [OrderController::class, 'add_order_proof'])->name('add-order-proof');
            Route::get('remove-proof-image', [OrderController::class, 'remove_proof_image'])->name('remove-proof-image');
            Route::get('add-delivery-man/{order_id}/{delivery_man_id}', [OrderController::class, 'add_delivery_man'])->name('add-delivery-man');
            Route::put('add-dine-in-table-number/{order}', [OrderController::class, 'add_dine_in_table_number'])->name('add_dine_in_table_number');

            //order update
            Route::post('add-to-cart', [OrderController::class, 'add_to_cart'])->name('add-to-cart');
            Route::post('remove-from-cart', [OrderController::class, 'remove_from_cart'])->name('remove-from-cart');
            Route::post('update/{order}', [OrderController::class, 'update'])->name('update');
            Route::get('edit-order/{order}', [OrderController::class, 'edit'])->name('edit');
            Route::get('quick-view', [OrderController::class, 'quick_view'])->name('quick-view');
            Route::get('quick-view-cart-item', [OrderController::class, 'quick_view_cart_item'])->name('quick-view-cart-item');
            Route::get('getSearchedFoods', [OrderController::class, 'getSearchedFoods'])->name('getSearchedFoods');
            Route::post('getSingleFoodPrice', [OrderController::class, 'getSingleFoodPrice'])->name('getSingleFoodPrice');
            Route::post('updateSchedule', [OrderController::class, 'updateSchedule'])->name('updateSchedule');
            Route::post('update-shipping/{order}', [OrderController::class, 'update_shipping'])->name('update-shipping');

        });


        Route::group(['prefix' => 'order','as' => 'order.subscription.', 'middleware' => ['module:subscription_order']], function () {
            Route::get('subscription/update-status/{supscription_id}/{status}', [OrderSubscriptionController::class, 'view'])->name('update-status');
            Route::get('subscription', [OrderSubscriptionController::class, 'index'])->name('index');
            Route::get('subscription/show/{subscription}', [OrderSubscriptionController::class, 'show'])->name('show');
            Route::get('subscription/edit/{subscription}', [OrderSubscriptionController::class, 'edit'])->name('edit');
            Route::put('subscription/update/{subscription}', [OrderSubscriptionController::class, 'update'])->name('update');

        });

        Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.', 'middleware' => ['subscription:restaurant_setup' ]], function () {
            Route::get('restaurant-setup', [BusinessSettingsController::class, 'restaurant_index'])->name('restaurant-setup')->middleware('module:restaurant_config');
            Route::get('notification-setup', [BusinessSettingsController::class, 'notification_index'])->name('notification-setup')->middleware('module:notification_setup');
            Route::get('notification-status-change/{key}/{type}', [BusinessSettingsController::class, 'notification_status_change'])->name('notification_status_change')->middleware('module:notification_setup');;
            Route::post('add-schedule', [BusinessSettingsController::class, 'add_schedule'])->name('add-schedule');
            Route::get('remove-schedule/{restaurant_schedule}', [BusinessSettingsController::class, 'remove_schedule'])->name('remove-schedule');
            Route::get('update-active-status', [BusinessSettingsController::class, 'active_status'])->name('update-active-status');
            Route::post('update-setup/{restaurant}', [BusinessSettingsController::class, 'restaurant_setup'])->name('update-setup');
            Route::get('toggle-settings-status/{restaurant}/{status}/{menu}', [BusinessSettingsController::class, 'restaurant_status'])->name('toggle-settings');
            Route::get('site_direction_vendor', [BusinessSettingsController::class, 'site_direction_vendor'])->name('site_direction_vendor');
            Route::post('update-meta-data/{restaurant}', [BusinessSettingsController::class, 'updateStoreMetaData'])->name('update-meta-data');
            Route::post('update-opening-closing-status/{restaurant}', [BusinessSettingsController::class, 'updateOpeningClosingStatus'])->name('update-opening-closing-status');

        });

        Route::group(['prefix' => 'profile', 'as' => 'profile.', 'middleware' => ['module:bank_info','subscription:bank_info' ]], function () {
            Route::get('view', [ProfileController::class, 'view'])->name('view');
            // Route::get('update', 'ProfileController@edit')->name('update');
            Route::post('update', [ProfileController::class, 'update'])->name('update');
            Route::post('settings-password', [ProfileController::class, 'settings_password_update'])->name('settings-password');
            // Route::get('bank-view', 'ProfileController@bank_view')->name('bankView');
            // Route::get('bank-edit', 'ProfileController@bank_edit')->name('bankInfo');
            // Route::post('bank-update', 'ProfileController@bank_update')->name('bank_update');
            // Route::post('bank-delete', 'ProfileController@bank_delete')->name('bank_delete');
        });

        Route::group(['prefix' => 'restaurant', 'as' => 'shop.', 'middleware' => ['module:my_restaurant','subscription:my_shop' ]], function () {
            Route::get('view', [RestaurantController::class, 'view'])->name('view');
            Route::get('edit', [RestaurantController::class, 'edit'])->name('edit');
            Route::post('update', [RestaurantController::class, 'update'])->name('update');
            Route::post('logo/update', [RestaurantController::class, 'logo_update'])->name('logo-update');
            Route::post('cover/update', [RestaurantController::class, 'cover_update'])->name('cover-update');
            Route::post('update-message', [RestaurantController::class, 'update_message'])->name('update-message');
            Route::post('qr-store', [RestaurantController::class, 'qr_store'])->name('qr-store');
            Route::get('qr-view', [RestaurantController::class, 'qr_view'])->name('qr-view')->withoutMiddleware('module:my_restaurant')->middleware('module:my_qr_code');
            Route::get('qr-print', [RestaurantController::class, 'qr_print'])->name('qr-print');
        });

        Route::group(['prefix' => 'message', 'as' => 'message.', 'middleware' => ['module:chat','subscription:chat'] ], function () {
            Route::get('list', [ConversationController::class, 'list'])->name('list');
            Route::post('store/{user_id}/{user_type}', [ConversationController::class, 'store'])->name('store');
            Route::get('view/{conversation_id}/{user_id}', [ConversationController::class, 'view'])->name('view');
        });

        Route::group(['prefix' => 'subscription' , 'as' => 'subscriptionackage.'], function () {
            Route::get('/subscriber-detail',  [SubscriptionController::class, 'subscriberDetail'])->name('subscriberDetail')->middleware('module:business_plan');
            Route::get('/invoice/{id}',  [SubscriptionController::class, 'invoice'])->name('invoice');
            Route::get('/subscriber-list',  [SubscriptionController::class, 'subscriberList'])->name('subscriberList');
            Route::post('/cancel-subscription/{id}',  [SubscriptionController::class, 'cancelSubscription'])->name('cancelSubscription');
            Route::post('/switch-to-commission/{id}',  [SubscriptionController::class, 'switchToCommission'])->name('switchToCommission');
            Route::get('/package-view/{id}/{store_id}',  [SubscriptionController::class, 'packageView'])->name('packageView');
            Route::get('/subscriber-transactions/{id}',  [SubscriptionController::class, 'subscriberTransactions'])->name('subscriberTransactions');
            Route::get('/subscriber-transaction-export',  [SubscriptionController::class, 'subscriberTransactionExport'])->name('subscriberTransactionExport');
            Route::get('/subscriber-wallet-transactions',  [SubscriptionController::class, 'subscriberWalletTransactions'])->name('subscriberWalletTransactions');
            Route::post('/package-buy',  [SubscriptionController::class, 'packageBuy'])->name('packageBuy');
            Route::post('/add-to-session',  [SubscriptionController::class, 'addToSession'])->name('addToSession');
        });

        Route::group(['prefix' => 'report', 'as' => 'report.', 'middleware' => ['subscription:report']], function () {

            // Routes without module-based middleware
            Route::post('set-date', [ReportController::class, 'set_date'])->name('set-date');
            Route::get('generate-statement/{id}', [ReportController::class, 'generate_statement'])->name('generate-statement');

            Route::get('restaurant-earning-report', [RestaurantEarningReportController::class, 'getRestaurantEarningReport'])->name('restaurant-earning-report');
            Route::get('restaurant-earning-summary', [RestaurantEarningReportController::class, 'getRestaurantEarningSummary'])->name('restaurant-earning-summary');
            Route::get('restaurant-earning-breakdown', [RestaurantEarningReportController::class, 'getRestaurantEarningBreakdown'])->name('restaurant-earning-breakdown');
            Route::get('restaurant-expense-breakdown', [RestaurantEarningReportController::class, 'getRestaurantExpenseBreakdown'])->name('restaurant-expense-breakdown');
            Route::get('restaurant-earning-trend', [RestaurantEarningReportController::class, 'getRestaurantEarningTrend'])->name('restaurant-earning-trend');
            Route::get('top-selling-foods', [RestaurantEarningReportController::class, 'getTopSellingFoods'])->name('top-selling-foods');
            Route::get('restaurant-earning-export', [RestaurantEarningReportController::class, 'export'])->name('restaurant-earning-export');
            Route::get('restaurant-earning-transactions', [RestaurantEarningReportController::class, 'getRestaurantEarningTransactions'])->name('restaurant-earning-transactions');
            Route::get('restaurant-earning-transactions-export', [RestaurantEarningReportController::class, 'exportRestaurantEarningTransactions'])->name('restaurant-earning-transactions-export');

            Route::group(['middleware' => ['module:tax_report']], function () {
                Route::get('food-report', [ReportController::class, 'food_report'])->name('food-report');
                Route::get('food-report-export', [ReportController::class, 'food_report_export'])->name('food-report-export');
                Route::get('vendor-tax-report', [VendorTaxReportController::class, 'vendorTax'])->name('vendorTax');
                Route::get('vendor-tax-export', [VendorTaxReportController::class, 'vendorTaxExport'])->name('vendorTaxExport');
            });

            // expense_report group
            Route::group(['middleware' => ['module:expense_report']], function () {
                Route::get('expense-report', [ReportController::class, 'expense_report'])->name('expense-report');
                Route::get('expense-export', [ReportController::class, 'expense_export'])->name('expense-export');
                Route::post('expense-report-search', [ReportController::class, 'expense_search'])->name('expense-report-search');
            });

            // transaction group
            Route::group(['middleware' => ['module:transaction']], function () {
                Route::get('transaction-report', [ReportController::class, 'day_wise_report'])->name('day-wise-report');
                Route::get('transaction-report-export', [ReportController::class, 'day_wise_report_export'])->name('day-wise-report-export');
            });

            // order_report group
            Route::group(['middleware' => ['module:order_report']], function () {
                Route::get('order-report', [ReportController::class, 'order_report'])->name('order-report');
                Route::get('order-report-export', [ReportController::class, 'order_report_export'])->name('order-report-export');
                Route::get('campaign-order-report', [ReportController::class, 'campaign_order_report'])->name('campaign_order-report');
                Route::get('campaign-order-report-export', [ReportController::class, 'campaign_report_export'])->name('campaign_report_export');
            });

            // food_report group
            Route::group(['middleware' => ['module:food_report']], function () {
                Route::get('food-wise-report', [ReportController::class, 'food_wise_report'])->name('food-wise-report');
                Route::get('food-wise-report-export', [ReportController::class, 'food_wise_report_export'])->name('food-wise-report-export');
            });

            // disbursement group
            Route::group(['middleware' => ['module:disbursement']], function () {
                Route::get('disbursement-report', [ReportController::class, 'disbursement_report'])->name('disbursement-report');
                Route::get('disbursement-report-export/{type}', [ReportController::class, 'disbursement_report_export'])->name('disbursement-report-export');
            });
        });

        Route::group(['prefix' => 'file-manager', 'as' => 'file-manager.'], function () {
            Route::get('/download/{file_name}/{storage?}', [OrderController::class, 'download'])->name('download');
        });
    });

    Route::post('digital_payment', [SubscriptionController::class, 'digital_payment'])->name('subscription.digital_payment');
    Route::get('pay/now/{subscription_transaction_id}', [SubscriptionController::class, 'getPaymentMethods'])->name('subscription.digital_payment_methods');
});
