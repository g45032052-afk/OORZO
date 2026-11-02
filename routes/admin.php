<?php

use App\Http\Controllers\Admin\AccountTransactionController;
use App\Http\Controllers\Admin\AdditionalChargeController;
use App\Http\Controllers\Admin\AddonCategoryController;
use App\Http\Controllers\Admin\AddOnController;
use App\Http\Controllers\Admin\AdminEarningReportController;
use App\Http\Controllers\Admin\AdminTaxReportController;
use App\Http\Controllers\Admin\AdvertisementController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\DeliveryRuleController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BusinessSettingsController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\BogoOfferController;
use App\Http\Controllers\Admin\HappyHourController;
use App\Http\Controllers\Admin\CashBackController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactMessages;
use App\Http\Controllers\Admin\ConversationController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CuisineController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerReportController;
use App\Http\Controllers\Admin\CustomerWalletController;
use App\Http\Controllers\Admin\CustomRoleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DatabaseSettingController;
use App\Http\Controllers\Admin\DeliveryManController;
use App\Http\Controllers\Admin\DeliveryManDisbursementController;
use App\Http\Controllers\Admin\DeliverymanEarningReportController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\Erp\ErpIntegrationController;
use App\Http\Controllers\Admin\FileManagerController;
use App\Http\Controllers\Admin\FoodController;
use App\Http\Controllers\Admin\EtaConfigurationController;
use App\Http\Controllers\Admin\FreeDeliveryController;
use App\Http\Controllers\Admin\LandingPageController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\LoyaltyPointController;
use App\Http\Controllers\Admin\Marketing\AnalyticScriptController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OfflinePaymentMethodController;
use App\Http\Controllers\Admin\OrderCancelReasonController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderSubscriptionController;
use App\Http\Controllers\Admin\PageSetupController;
use App\Http\Controllers\Admin\POSController;
use App\Http\Controllers\Admin\ProCustomerController;
use App\Http\Controllers\Admin\ProvideDMEarningController;
use App\Http\Controllers\Admin\RegistrationPageController;
use App\Http\Controllers\Admin\RestaurantCategoryController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RestaurantDisbursementController;
use App\Http\Controllers\Admin\RestaurantEarningReportController;
use App\Http\Controllers\Admin\ReviewsController;
use App\Http\Controllers\Admin\SearchRoutingController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\SMSModuleController;
use App\Http\Controllers\Admin\SocialMediaController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\SurgePriceController;
use App\Http\Controllers\Admin\System\AddonController as SystemAddonController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\VendorTaxReportController;
use App\Http\Controllers\Admin\VisitorLogController;
use App\Http\Controllers\Admin\WalletBonusController;
use App\Http\Controllers\Admin\WithdrawalMethodController;
use App\Http\Controllers\Admin\ZipCodeController;
use App\Http\Controllers\Admin\ZoneController;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Admin', 'as' => 'admin.'], function () {

    // Public route — used by vendor registration (no auth required)
    Route::get('zone/check-location', [ZoneController::class, 'checkLocation'])->name('zone.check-location');

    Route::group(['middleware' => ['admin']], function () {
        // Route::view('test' , 'test.test')->name('test');

        Route::post('search-routing', [SearchRoutingController::class, 'index'])->name('search.routing');
        Route::get('recent-search', [SearchRoutingController::class, 'recentSearch'])->name('recent.search');
        Route::post('store-clicked-route', [SearchRoutingController::class, 'storeClickedRoute'])->name('store.clicked.route');

        Route::get('lang/{locale}', [LanguageController::class, 'lang'])->name('lang');
        Route::get('settings', [SystemController::class, 'settings'])->name('settings');
        Route::get('ajax-system-currency', [SystemController::class, 'system_currency'])->name('system_currency');
        Route::post('settings', [SystemController::class, 'settings_update']);
        Route::post('settings-password', [SystemController::class, 'settings_password_update'])->name('settings-password');
        Route::get('/get-restaurant-data', [SystemController::class, 'restaurant_data'])->name('get-restaurant-data');
        Route::post('/update-fcm-toke', [SystemController::class, 'update_fcm_token'])->name('update-fcm');

        // dashboard
        Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('landing-page', [SystemController::class, 'landing_page'])->name('landing-page');

        Route::middleware('module:account')->group(function () {

            Route::get('account-transaction', [AccountTransactionController::class, 'index'])->name('account-transaction.index');
            Route::post('account-transaction', [AccountTransactionController::class, 'store'])->name('account-transaction.store');
            Route::get('account-transaction/{id}', [AccountTransactionController::class, 'show'])->name('account-transaction.show');
            Route::delete('account-transaction/{id}', [AccountTransactionController::class, 'destroy'])->name('account-transaction.destroy');
        });

        // Route::resource('account-transaction', AccountTransactionController::class)->middleware('module:account');

        Route::get('export-account-transaction', [AccountTransactionController::class, 'export_account_transaction'])->name('export-account-transaction');
        Route::post('search-account-transaction', [AccountTransactionController::class, 'search_account_transaction'])->name('search-account-transaction');

        // Route::resource('provide-deliveryman-earnings', ProvideDMEarningController::class)->middleware('module:provide_dm_earning');
        Route::middleware('module:provide_dm_earning')->group(function () {
            Route::get('provide-deliveryman-earnings', [ProvideDMEarningController::class, 'index'])->name('provide-deliveryman-earnings.index');
            Route::post('provide-deliveryman-earnings', [ProvideDMEarningController::class, 'store'])->name('provide-deliveryman-earnings.store');
            Route::get('provide-deliveryman-earnings/{id}', [ProvideDMEarningController::class, 'show'])->name('provide-deliveryman-earnings.show');
            Route::delete('provide-deliveryman-earnings/{id}', [ProvideDMEarningController::class, 'destroy'])->name('provide-deliveryman-earnings.destroy');
        });

        Route::get('export-deliveryman-earnings', [ProvideDMEarningController::class, 'dm_earning_list_export'])->name('export-deliveryman-earning');
        Route::post('deliveryman-earnings-search', [ProvideDMEarningController::class, 'search_deliveryman_earning'])->name('search-deliveryman-earning');

        Route::post('maintenance-mode', [SystemController::class, 'maintenance_mode'])->name('maintenance-mode');

        Route::group(['prefix' => 'dashboard-stats', 'as' => 'dashboard-stats.'], function () {
            Route::post('order', [DashboardController::class, 'order'])->name('order');
            Route::post('zone', [DashboardController::class, 'zone'])->name('zone');
            Route::post('user-overview', [DashboardController::class, 'user_overview'])->name('user-overview');
            Route::post('business-overview', [DashboardController::class, 'business_overview'])->name('business-overview');
        });

        Route::group(['prefix' => 'custom-role', 'as' => 'custom-role.', 'middleware' => ['module:custom_role']], function () {
            Route::get('create', [CustomRoleController::class, 'create'])->name('create');
            Route::post('create', [CustomRoleController::class, 'store']);
            Route::get('edit/{id}', [CustomRoleController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [CustomRoleController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [CustomRoleController::class, 'distroy'])->name('delete');
            Route::post('search', [CustomRoleController::class, 'search'])->name('search');
            Route::get('export-employee-role', [CustomRoleController::class, 'employee_role_export'])->name('export-employee-role');
        });

        Route::group(['prefix' => 'employee', 'as' => 'employee.', 'middleware' => ['module:employee']], function () {
            Route::get('add-new', [EmployeeController::class, 'add_new'])->name('add-new');
            Route::post('add-new', [EmployeeController::class, 'store']);
            Route::get('list', [EmployeeController::class, 'list'])->name('list');
            Route::get('update/{id}', [EmployeeController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [EmployeeController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [EmployeeController::class, 'distroy'])->name('delete');
            Route::get('export-employee', [EmployeeController::class, 'employee_list_export'])->name('export-employee');
        });
        Route::post('food/food-variation-generate', [FoodController::class, 'food_variation_generator'])->name('food.food-variation-generate');
        Route::post('food/variant-price', [FoodController::class, 'variant_price'])->name('food.variant-price');
        Route::get('food/get-foods', [FoodController::class, 'get_foods'])->name('food.getfoods');

        Route::group(['prefix' => 'food', 'as' => 'food.', 'middleware' => ['module:food']], function () {
            Route::get('add-new', [FoodController::class, 'index'])->name('add-new');
            Route::post('store', [FoodController::class, 'store'])->name('store');
            Route::get('edit/{id}', [FoodController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [FoodController::class, 'update'])->name('update');
            Route::get('list', [FoodController::class, 'list'])->name('list');
            Route::delete('delete/{id}', [FoodController::class, 'delete'])->name('delete');
            Route::get('status/{id}/{status}', [FoodController::class, 'status'])->name('status');
            Route::get('review-status/{id}/{status}', [FoodController::class, 'reviews_status'])->name('reviews.status');
            Route::post('search-restaurant', [FoodController::class, 'search_vendor'])->name('search-restaurant');
            Route::get('reviews', [FoodController::class, 'review_list'])->name('reviews');
            Route::post('update-stock', [FoodController::class, 'updateStock'])->name('updateStock');
            Route::get('restaurant-food-export/{type}/{restaurant_id}', [FoodController::class, 'restaurant_food_export'])->name('restaurant-food-export');
            Route::get('out-of-stock-list', [FoodController::class, 'stockOutList'])->name('stockOutList');
            Route::get('view/{id}', [FoodController::class, 'view'])->name('view');
            // ajax request
            Route::get('get-categories', [FoodController::class, 'get_categories'])->name('get-categories');
            Route::get('get-restaurant-categories', [FoodController::class, 'get_restaurant_categories'])->name('get-restaurant-categories');

            Route::get('export', [FoodController::class, 'export'])->name('export');
            Route::get('reviews-export', [FoodController::class, 'reviews_export'])->name('reviews_export');
            Route::get('food-wise-reviews-export', [FoodController::class, 'food_wise_reviews_export'])->name('food_wise_reviews_export');

            // Import and export
            Route::get('bulk-import', [FoodController::class, 'bulk_import_index'])->name('bulk-import');
            Route::post('bulk-import', [FoodController::class, 'bulk_import_data']);
            Route::get('bulk-export', [FoodController::class, 'bulk_export_index'])->name('bulk-export-index');
            Route::post('bulk-export', [FoodController::class, 'bulk_export_data'])->name('bulk-export');
        });

        Route::group(['prefix' => 'banner', 'as' => 'banner.', 'middleware' => ['module:banner']], function () {
            Route::get('add-new', [BannerController::class, 'index'])->name('add-new');
            Route::post('store', [BannerController::class, 'store'])->name('store');
            Route::get('edit/{banner}', [BannerController::class, 'edit'])->name('edit');
            Route::post('update/{banner}', [BannerController::class, 'update'])->name('update');
            Route::get('status/{id}/{status}', [BannerController::class, 'status'])->name('status');
            Route::delete('delete/{banner}', [BannerController::class, 'delete'])->name('delete');
            Route::post('search', [BannerController::class, 'search'])->name('search');
            Route::get('promotional-banner', [BannerController::class, 'promotional_banner'])->name('promotional_banner');
            Route::post('promotional-banner-update', [BannerController::class, 'promotional_banner_update'])->name('promotional_banner_update');
        });

        Route::group(['prefix' => 'campaign', 'as' => 'campaign.', 'middleware' => ['module:campaign']], function () {
            Route::get('{type}/add-new', [CampaignController::class, 'index'])->name('add-new');
            Route::post('store/basic', [CampaignController::class, 'storeBasic'])->name('store-basic');
            Route::post('store/item', [CampaignController::class, 'storeItem'])->name('store-item');
            Route::get('{type}/edit/{campaign}', [CampaignController::class, 'edit'])->name('edit');
            Route::get('{type}/view/{campaign}', [CampaignController::class, 'view'])->name('view');
            Route::post('basic/update/{campaign}', [CampaignController::class, 'update'])->name('update-basic');
            Route::post('item/update/{campaign}', [CampaignController::class, 'updateItem'])->name('update-item');
            Route::get('remove-restaurant/{campaign}/{restaurant}', [CampaignController::class, 'remove_restaurant'])->name('remove-restaurant');
            Route::post('add-restaurant/{campaign}', [CampaignController::class, 'addrestaurant'])->name('addrestaurant');
            Route::get('{type}/list', [CampaignController::class, 'list'])->name('list');
            Route::get('status/{type}/{id}/{status}', [CampaignController::class, 'status'])->name('status');
            Route::delete('delete/{campaign}', [CampaignController::class, 'delete'])->name('delete');
            Route::delete('item/delete/{campaign}', [CampaignController::class, 'delete_item'])->name('delete-item');
            Route::get('restaurant-confirmation/{campaign}/{id}/{status}', [CampaignController::class, 'restaurant_confirmation'])->name('restaurant_confirmation');
            Route::get('basic-campaign-export', [CampaignController::class, 'basic_campaign_export'])->name('basic_campaign_export');
            Route::get('item-campaign-export', [CampaignController::class, 'item_campaign_export'])->name('item_campaign_export');
            Route::get('food-campaign-order-list-export', [CampaignController::class, 'food_campaign_list_export'])->name('food_campaign_list_export');
        });

        Route::group(['prefix' => 'bogo-offer', 'as' => 'bogo-offer.', 'middleware' => ['module:bogo_offer']], function () {
            Route::get('add-new', [BogoOfferController::class, 'index'])->name('add-new');
            Route::get('list', [BogoOfferController::class, 'list'])->name('list');
            Route::get('export', [BogoOfferController::class, 'export'])->name('export');
            Route::post('store', [BogoOfferController::class, 'store'])->name('store');
            Route::get('edit/{id}', [BogoOfferController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [BogoOfferController::class, 'update'])->name('update');
            Route::get('view/{id}', [BogoOfferController::class, 'view'])->name('view');
            Route::get('status/{id}/{status}', [BogoOfferController::class, 'status'])->name('status');
            Route::delete('delete/{id}', [BogoOfferController::class, 'delete'])->name('delete');
            Route::post('add-restaurant/{id}', [BogoOfferController::class, 'addRestaurant'])->name('add-restaurant');
            Route::post('restaurant-confirmation/{id}/{enrollment_id}/{status}', [BogoOfferController::class, 'restaurantConfirmation'])->name('restaurant-confirmation');
            Route::delete('remove-restaurant/{id}/{enrollment_id}', [BogoOfferController::class, 'removeRestaurant'])->name('remove-restaurant');
            Route::get('restaurant-foods', [BogoOfferController::class, 'restaurantFoods'])->name('restaurant-foods');
            Route::get('enrollment-detail/{id}/{enrollment_id}', [BogoOfferController::class, 'enrollmentDetail'])->name('enrollment-detail');
            Route::post('update-enrollment/{id}/{enrollment_id}', [BogoOfferController::class, 'updateEnrollment'])->name('update-enrollment');
            Route::get('restaurant-export/{id}', [BogoOfferController::class, 'restaurantExport'])->name('restaurant-export');
        });

        Route::group(['prefix' => 'happy-hour', 'as' => 'happy-hour.', 'middleware' => ['module:happy_hour']], function () {
            Route::get('add-new', [HappyHourController::class, 'index'])->name('add-new');
            Route::get('list', [HappyHourController::class, 'list'])->name('list');
            Route::get('export', [HappyHourController::class, 'export'])->name('export');
            Route::post('store', [HappyHourController::class, 'store'])->name('store');
            Route::get('edit/{id}', [HappyHourController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [HappyHourController::class, 'update'])->name('update');
            Route::get('view/{id}', [HappyHourController::class, 'view'])->name('view');
            Route::get('status/{id}/{status}', [HappyHourController::class, 'status'])->name('status');
            Route::delete('delete/{id}', [HappyHourController::class, 'delete'])->name('delete');
            Route::post('add-restaurant/{id}', [HappyHourController::class, 'addRestaurant'])->name('add-restaurant');
            Route::post('restaurant-confirmation/{id}/{enrollment_id}/{status}', [HappyHourController::class, 'restaurantConfirmation'])->name('restaurant-confirmation');
            Route::delete('remove-restaurant/{id}/{enrollment_id}', [HappyHourController::class, 'removeRestaurant'])->name('remove-restaurant');
            Route::get('restaurant-export/{id}', [HappyHourController::class, 'restaurantExport'])->name('restaurant-export');
        });

        Route::group(['prefix' => 'advertisement', 'as' => 'advertisement.', 'middleware' => ['module:advertisement']], function () {

            Route::get('/', [AdvertisementController::class, 'index'])->name('index');
            Route::get('create/', [AdvertisementController::class, 'create'])->name('create');
            Route::get('details/{advertisement}', [AdvertisementController::class, 'show'])->name('show');
            Route::get('{advertisement}/edit', [AdvertisementController::class, 'edit'])->name('edit');
            Route::post('store', [AdvertisementController::class, 'store'])->name('store');
            Route::put('update/{advertisement}', [AdvertisementController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [AdvertisementController::class, 'destroy'])->name('destroy');

            Route::get('/status', [AdvertisementController::class, 'status'])->name('status');
            Route::get('/paidStatus', [AdvertisementController::class, 'paidStatus'])->name('paidStatus');
            Route::get('/priority', [AdvertisementController::class, 'priority'])->name('priority');
            Route::get('/requests', [AdvertisementController::class, 'requestList'])->name('requestList');
            Route::get('/copy-advertisement/{advertisement}', [AdvertisementController::class, 'copyAdd'])->name('copyAdd');
            Route::get('/updateDate/{advertisement}', [AdvertisementController::class, 'updateDate'])->name('updateDate');
            Route::post('/copy-add-post/{advertisement}', [AdvertisementController::class, 'copyAddPost'])->name('copyAddPost');

        });

        Route::group(['prefix' => 'coupon', 'as' => 'coupon.', 'middleware' => ['module:coupon']], function () {
            Route::get('add-new', [CouponController::class, 'add_new'])->name('add-new');
            Route::post('store', [CouponController::class, 'store'])->name('store');
            Route::get('update/{id}', [CouponController::class, 'edit'])->name('update');
            Route::post('update/{id}', [CouponController::class, 'update']);
            Route::get('status/{id}/{status}', [CouponController::class, 'status'])->name('status');
            Route::delete('delete/{id}', [CouponController::class, 'delete'])->name('delete');
            Route::get('coupon-export', [CouponController::class, 'coupon_export'])->name('coupon_export');
            Route::get('check-code', [CouponController::class, 'checkCode'])->name('check.code');

        });

        Route::group(['prefix' => 'cashback', 'as' => 'cashback.', 'middleware' => ['module:cashback']], function () {
            Route::get('/', [CashBackController::class, 'index'])->name('add-new');
            Route::post('store', [CashBackController::class, 'add'])->name('store');
            Route::get('edit/{id}', [CashBackController::class, 'getUpdateView'])->name('edit');
            Route::post('edit/{id}', [CashBackController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [CashBackController::class, 'delete'])->name('delete');
            Route::get('status/{id}/{status}', [CashBackController::class, 'updateStatus'])->name('status');
        });

        Route::group(['prefix' => 'attribute', 'as' => 'attribute.', 'middleware' => ['module:attribute']], function () {
            Route::get('add-new', [AttributeController::class, 'index'])->name('add-new');
            Route::post('store', [AttributeController::class, 'store'])->name('store');
            Route::get('edit/{id}', [AttributeController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [AttributeController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [AttributeController::class, 'delete'])->name('delete');
            Route::post('search', [AttributeController::class, 'search'])->name('search');
            Route::get('export-attributes/{type}', [AttributeController::class, 'export_attributes'])->name('export-attributes');

            // Import and export
            Route::get('bulk-import', [AttributeController::class, 'bulk_import_index'])->name('bulk-import');
            Route::post('bulk-import', [AttributeController::class, 'bulk_import_data']);
            Route::get('bulk-export', [AttributeController::class, 'bulk_export_index'])->name('bulk-export-index');
            Route::post('bulk-export', [AttributeController::class, 'bulk_export_data'])->name('bulk-export');
        });

        Route::get('restaurant/get-restaurants', [VendorController::class, 'get_restaurants'])->name('restaurant.get-restaurants');
        Route::get('ajax-restaurant/get-restaurant-ratings', [VendorController::class, 'get_restaurant_ratings'])->name('restaurant.get-restaurant-ratings');
        Route::group(['prefix' => 'restaurant', 'as' => 'restaurant.', 'middleware' => ['module:restaurant']], function () {
            Route::get('get-restaurants-data/{restaurant}', [VendorController::class, 'get_restaurant_data'])->name('get-restaurants-data');
            Route::get('restaurant-filter/{id}', [VendorController::class, 'restaurant_filter'])->name('restaurantfilter');
            Route::get('get-account-data/{restaurant}', [VendorController::class, 'get_account_data'])->name('get_account_data');
            Route::get('get-addons', [VendorController::class, 'get_addons'])->name('get_addons');
            Route::group(['middleware' => ['module:restaurant']], function () {
                Route::get('update-application/{id}/{status}', [VendorController::class, 'update_application'])->name('application');
                Route::get('add', [VendorController::class, 'index'])->name('add');
                Route::post('store', [VendorController::class, 'store'])->name('store');
                Route::get('edit/{id}', [VendorController::class, 'edit'])->name('edit');
                Route::post('update/{restaurant}', [VendorController::class, 'update'])->name('update');
                Route::post('discount/{restaurant}', [VendorController::class, 'discountSetup'])->name('discount');
                Route::post('update-settings/{restaurant}', [VendorController::class, 'updateRestaurantSettings'])->name('update-settings');
                Route::delete('clear-discount/{restaurant}', [VendorController::class, 'cleardiscount'])->name('clear-discount');
                Route::get('view/{restaurant}/{tab?}/{sub_tab?}', [VendorController::class, 'view'])->name('view');
                Route::get('pending/list', [VendorController::class, 'pending'])->name('pending');
                Route::get('denied/list', [VendorController::class, 'denied'])->name('denied');
                // restaurant Transcation Search
                // message
                Route::get('message/{conversation_id}/{user_id}', [VendorController::class, 'conversation_view'])->name('message-view');
                Route::get('message/list', [VendorController::class, 'conversation_list'])->name('message-list');

                Route::get('list', [VendorController::class, 'list'])->name('list');
                Route::post('search', [VendorController::class, 'search'])->name('search');
                Route::get('status/{restaurant}/{status}', [VendorController::class, 'status'])->name('status');
                Route::get('toggle-settings-status/{restaurant}/{status}/{menu}', [VendorController::class, 'restaurant_status'])->name('toggle-settings');
                Route::get('featured/{restaurant}/{status}', [VendorController::class, 'featured'])->name('featured');
                Route::get('verified-seller/{restaurant}', [VendorController::class, 'verifiedSeller'])->name('verified-seller');
                Route::get('verified-seller-all', [VendorController::class, 'verifiedSellerAll'])->name('verified-seller-all');
                Route::post('status-filter', [VendorController::class, 'status_filter'])->name('status-filter');
                // Import and export
                Route::get('bulk-import', [VendorController::class, 'bulk_import_index'])->name('bulk-import');
                Route::post('bulk-import', [VendorController::class, 'bulk_import_data']);
                Route::get('bulk-export', [VendorController::class, 'bulk_export_index'])->name('bulk-export-index');
                Route::post('bulk-export', [VendorController::class, 'bulk_export_data'])->name('bulk-export');
                Route::get('cash-transaction-export', [VendorController::class, 'cash_transaction_export'])->name('cash-transaction-export');
                Route::get('digital-transaction-export', [VendorController::class, 'digital_transaction_export'])->name('digital-transaction-export');
                Route::get('withdraw-transaction-export', [VendorController::class, 'withdraw_transaction_export'])->name('withdraw-transaction-export');
                // get all restaurants export
                Route::get('restaurants-export/{type}', [VendorController::class, 'restaurants_export'])->name('restaurants-export');
                // Restaurant schedule
                Route::post('add-schedule', [VendorController::class, 'add_schedule'])->name('add-schedule');
                Route::get('remove-schedule/{restaurant_schedule}', [VendorController::class, 'remove_schedule'])->name('remove-schedule');
                Route::post('update-opening-closing-status/{restaurant}', [VendorController::class, 'updateOpeningClosingStatus'])->name('update-opening-closing-status');

                Route::post('update-meta-data/{restaurant}', [VendorController::class, 'updateStoreMetaData'])->name('update-meta-data');
                Route::post('qrcode/store/{restaurant}', [VendorController::class, 'qr_store'])->name('qrcode.store');
                Route::get('qrcode/print/{restaurant}', [VendorController::class, 'print_qrcode'])->name('qrcode.print');

            });

            Route::group(['middleware' => ['module:withdraw_list']], function () {
                Route::post('withdraw-status/{id}', [VendorController::class, 'withdrawStatus'])->name('withdraw_status');
                Route::get('withdraw_list', [VendorController::class, 'withdraw'])->name('withdraw_list');
                Route::post('withdraw_list/search', [VendorController::class, 'withdraw_search'])->name('withdraw_list_search');
                Route::get('withdraw-view/{withdraw_id}/{seller_id}', [VendorController::class, 'withdraw_view'])->name('withdraw_view');
                Route::get('withdraw-list-export', [VendorController::class, 'withdraw_list_export'])->name('withdraw-list-export');
            });
            Route::get('disbursement-export/{id}/{type}', [VendorController::class, 'disbursement_export'])->name('disbursement-export');

            Route::get('restaurant-wise-reviwe-export', [VendorController::class, 'restaurant_wise_reviwe_export'])->name('restaurant_wise_reviwe_export');

        });

        Route::get('addon/system-addons', function () {
            return to_route('admin.system-addon.index');
        })->name('addon.index');
        Route::group(['prefix' => 'addon', 'as' => 'addon.', 'middleware' => ['module:addon']], function () {

            Route::get('addon-category', [AddonCategoryController::class, 'index'])->name('addon-category');
            Route::get('addon-status/{id}', [AddonCategoryController::class, 'status'])->name('addon-category-status');
            Route::get('addon-edit/{id}', [AddonCategoryController::class, 'edit'])->name('addon-category-edit');
            Route::put('addon-update/{id}', [AddonCategoryController::class, 'update'])->name('addon-category-update');
            Route::delete('addon-category/{id}', [AddonCategoryController::class, 'delete'])->name('addon-category-delete');
            Route::post('addon-category-store', [AddonCategoryController::class, 'store'])->name('addon-category-store');
            Route::get('addon-category-export', [AddonCategoryController::class, 'exportAddonCategories'])->name('addon-category-export');

            Route::get('add-new', [AddOnController::class, 'index'])->name('add-new');
            Route::post('store', [AddOnController::class, 'store'])->name('store');
            Route::get('edit/{id}', [AddOnController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [AddOnController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [AddOnController::class, 'delete'])->name('delete');
            Route::get('status/{addon}/{status}', [AddOnController::class, 'status'])->name('status');
            // Import and export
            Route::get('export-addons', [AddOnController::class, 'export_addons'])->name('export_addons');
            Route::get('bulk-import', [AddOnController::class, 'bulk_import_index'])->name('bulk-import');
            Route::post('bulk-import', [AddOnController::class, 'bulk_import_data']);
            Route::get('bulk-export', [AddOnController::class, 'bulk_export_index'])->name('bulk-export-index');
            Route::post('bulk-export', [AddOnController::class, 'bulk_export_data'])->name('bulk-export');
        });

        Route::group(['prefix' => 'category', 'as' => 'category.'], function () {
            Route::get('get-all', [CategoryController::class, 'get_all'])->name('get-all');
            Route::group(['middleware' => ['module:category']], function () {
                Route::get('add', [CategoryController::class, 'index'])->name('add');
                Route::get('add-sub-category', [CategoryController::class, 'sub_index'])->name('add-sub-category');
                Route::get('create', [CategoryController::class, 'create'])->name('create');
                Route::post('store', [CategoryController::class, 'store'])->name('store');
                Route::get('edit/{id}', [CategoryController::class, 'edit'])->name('edit');
                Route::post('update/{id}', [CategoryController::class, 'update'])->name('update');
                Route::get('update-priority/{category}', [CategoryController::class, 'update_priority'])->name('priority');
                Route::post('store', [CategoryController::class, 'store'])->name('store');
                Route::get('status/{id}/{status}', [CategoryController::class, 'status'])->name('status');
                Route::delete('delete/{id}', [CategoryController::class, 'delete'])->name('delete');
                Route::get('export-categories', [CategoryController::class, 'export_categories'])->name('export-categories');
                Route::get('export-sub-categories', [CategoryController::class, 'export_sub_categories'])->name('export-sub-categories');

                // Import and export
                Route::get('bulk-import', [CategoryController::class, 'bulk_import_index'])->name('bulk-import');
                Route::post('bulk-import', [CategoryController::class, 'bulk_import_data']);
                Route::get('bulk-export', [CategoryController::class, 'bulk_export_index'])->name('bulk-export-index');
                Route::post('bulk-export', [CategoryController::class, 'bulk_export_data'])->name('bulk-export');
            });
        });

        Route::group(['prefix' => 'restaurant-category', 'as' => 'restaurant-category.', 'middleware' => ['module:category']], function () {
            Route::get('list', [RestaurantCategoryController::class, 'index'])->name('index');
            Route::get('export/{type}', [RestaurantCategoryController::class, 'export'])->name('export');
            Route::get('create', [RestaurantCategoryController::class, 'create'])->name('create');
            Route::post('store', [RestaurantCategoryController::class, 'store'])->name('store');
            Route::get('edit/{id}', [RestaurantCategoryController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [RestaurantCategoryController::class, 'update'])->name('update');
            Route::get('update-priority/{restaurantCategory}', [RestaurantCategoryController::class, 'updatePriority'])->name('priority');
            Route::get('status/{id}/{status}', [RestaurantCategoryController::class, 'status'])->name('status');
            Route::delete('delete/{id}', [RestaurantCategoryController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'cuisine', 'as' => 'cuisine.'], function () {
            Route::group(['middleware' => ['module:category']], function () {
                Route::get('add', [CuisineController::class, 'index'])->name('add');
                Route::get('create', [CuisineController::class, 'create'])->name('create');
                Route::get('edit/{id}', [CuisineController::class, 'edit'])->name('edit');
                Route::get('status/{id}/{status}', [CuisineController::class, 'status'])->name('status');
                Route::post('update/{id}', [CuisineController::class, 'update'])->name('update');
                Route::post('store', [CuisineController::class, 'store'])->name('store');
                Route::delete('delete', [CuisineController::class, 'destroy'])->name('delete');
                Route::get('export', [CuisineController::class, 'export'])->name('export');
            });
        });

        Route::group(['prefix' => 'order', 'as' => 'order.', 'middleware' => ['module:order']], function () {
            Route::get('list/{status}', [OrderController::class, 'list'])->name('list');
            Route::get('details/{id}', [OrderController::class, 'details'])->name('details');
            Route::get('status', [OrderController::class, 'status'])->name('status');
            Route::get('view/{id}', [OrderController::class, 'view'])->name('view');
            Route::post('update-shipping/{order}', [OrderController::class, 'update_shipping'])->name('update-shipping');
            Route::delete('delete/{id}', [OrderController::class, 'delete'])->name('delete');
            Route::get('orders-export/{type}/{restaurant_id}', [OrderController::class, 'orders_export'])->name('export-orders');

            Route::get('add-delivery-man/{order_id}/{delivery_man_id}', [OrderController::class, 'add_delivery_man'])->name('add-delivery-man');
            Route::get('payment-status', [OrderController::class, 'payment_status'])->name('payment-status');
            Route::get('generate-invoice/{id}', [OrderController::class, 'generate_invoice'])->name('generate-invoice');
            Route::post('add-payment-ref-code/{id}', [OrderController::class, 'add_payment_ref_code'])->name('add-payment-ref-code');
            Route::get('restaurant-filter/{restaurant_id}', [OrderController::class, 'restaurant_filter'])->name('restaurant-filter');
            Route::get('filter/reset', [OrderController::class, 'filter_reset']);
            Route::post('filter', [OrderController::class, 'filter'])->name('filter');
            // Route::post('restaurant-order-search', 'OrderController@restaurant_order_search')->name('restaurant-order-search');
            // order update
            Route::post('add-to-cart', [OrderController::class, 'add_to_cart'])->name('add-to-cart');
            Route::post('remove-from-cart', [OrderController::class, 'remove_from_cart'])->name('remove-from-cart');
            Route::post('update/{order}', [OrderController::class, 'update'])->name('update');
            Route::get('edit-order/{order}', [OrderController::class, 'edit'])->name('edit');
            Route::get('quick-view', [OrderController::class, 'quick_view'])->name('quick-view');
            Route::get('quick-view-cart-item', [OrderController::class, 'quick_view_cart_item'])->name('quick-view-cart-item');
            Route::get('getSearchedFoods', [OrderController::class, 'getSearchedFoods'])->name('getSearchedFoods');
            Route::post('getSingleFoodPrice', [OrderController::class, 'getSingleFoodPrice'])->name('getSingleFoodPrice');
            Route::post('updateSchedule', [OrderController::class, 'updateSchedule'])->name('updateSchedule');

            Route::get('export-orders/{status}/{type}', [OrderController::class, 'export_orders'])->name('export');
            Route::post('add-order-proof/{id}', [OrderController::class, 'add_order_proof'])->name('add-order-proof');
            Route::get('remove-proof-image', [OrderController::class, 'remove_proof_image'])->name('remove-proof-image');
            Route::get('offline-payment', [OrderController::class, 'offline_payment'])->name('offline_payment');
            Route::get('offline/payment/list/{status}', [OrderController::class, 'offline_verification_list'])->name('offline_verification_list');
            Route::put('add-dine-in-table-number/{order}', [OrderController::class, 'add_dine_in_table_number'])->name('add_dine_in_table_number');
        });

        Route::group(['prefix' => 'dispatch', 'as' => 'dispatch.', 'middleware' => ['module:order']], function () {
            Route::get('list/{status}', [OrderController::class, 'dispatch_list'])->name('list');
        });

        Route::group(['prefix' => 'zone', 'as' => 'zone.', 'middleware' => ['module:zone']], function () {
            Route::get('/', [ZoneController::class, 'index'])->name('home');
            Route::post('store', [ZoneController::class, 'store'])->name('store');
            Route::get('edit/{id}', [ZoneController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [ZoneController::class, 'update'])->name('update');
            Route::get('settings/{id}', [ZoneController::class, 'zone_settings'])->name('settings');
            Route::get('latest-settings', [ZoneController::class, 'latest_zone_settings'])->name('latest-settings');
            Route::post('zone-settings-update/{id}', [ZoneController::class, 'zone_settings_update'])->name('zone_settings_update');
            Route::delete('delete/{zone}', [ZoneController::class, 'destroy'])->name('delete');
            Route::get('status/{id}/{status}', [ZoneController::class, 'status'])->name('status');
            Route::get('default-status', [ZoneController::class, 'defaultStatus'])->name('defaultStatus');
            Route::get('zone-filter/{id}', [ZoneController::class, 'zone_filter'])->name('zonefilter');
            Route::get('get-all-zone-cordinates/{id?}', [ZoneController::class, 'get_all_zone_cordinates'])->name('zoneCoordinates');
            Route::get('export-zone-cordinates/{type}', [ZoneController::class, 'export_zones'])->name('export-zones');
            Route::post('store-incentive/{zone_id}', [ZoneController::class, 'store_incentive'])->name('incentive.store');
            Route::delete('destroy-incentive/{id}', [ZoneController::class, 'destroy_incentive'])->name('incentive.destory');
        });

        // Areas sit under a zone, so they reuse the zone permission: anyone who
        // can manage zones can manage the areas inside them.
        Route::group(['prefix' => 'area', 'as' => 'area.', 'middleware' => ['module:zone']], function () {
            Route::get('list', [AreaController::class, 'list'])->name('list');
            Route::get('create', [AreaController::class, 'create'])->name('create');
            Route::post('store', [AreaController::class, 'store'])->name('store');
            Route::get('edit/{id}', [AreaController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [AreaController::class, 'update'])->name('update');
            Route::get('status/{id}/{status}', [AreaController::class, 'status'])->name('status');
            Route::delete('delete/{id}', [AreaController::class, 'destroy'])->name('delete');
            Route::get('export/{type}', [AreaController::class, 'export'])->name('export');
        });

        // Zip codes sit under a zone alongside areas, and reuse the same permission.
        Route::group(['prefix' => 'zip-code', 'as' => 'zip-code.', 'middleware' => ['module:zone']], function () {
            Route::get('list', [ZipCodeController::class, 'list'])->name('list');
            Route::get('create', [ZipCodeController::class, 'create'])->name('create');
            Route::post('store', [ZipCodeController::class, 'store'])->name('store');
            Route::get('edit/{id}', [ZipCodeController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [ZipCodeController::class, 'update'])->name('update');
            Route::get('status/{id}/{status}', [ZipCodeController::class, 'status'])->name('status');
            Route::delete('delete/{id}', [ZipCodeController::class, 'destroy'])->name('delete');
            Route::get('export/{type}', [ZipCodeController::class, 'export'])->name('export');
        });

        // Delivery pricing rules. A zone holds many but only one may be active,
        // which the status endpoint enforces.
        Route::group(['prefix' => 'delivery-rule', 'as' => 'delivery-rule.', 'middleware' => ['module:zone']], function () {
            Route::get('list', [DeliveryRuleController::class, 'list'])->name('list');
            Route::get('create', [DeliveryRuleController::class, 'create'])->name('create');
            Route::post('store', [DeliveryRuleController::class, 'store'])->name('store');
            Route::get('show/{id}', [DeliveryRuleController::class, 'show'])->name('show');
            Route::get('edit/{id}', [DeliveryRuleController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [DeliveryRuleController::class, 'update'])->name('update');
            Route::post('status/{id}', [DeliveryRuleController::class, 'status'])->name('status');
            Route::delete('delete/{id}', [DeliveryRuleController::class, 'destroy'])->name('delete');
            Route::get('export/{type}', [DeliveryRuleController::class, 'export'])->name('export');
            Route::get('zone-coverage/{zone}', [DeliveryRuleController::class, 'zoneCoverage'])->name('zone-coverage');
            Route::get('zone-rules/{zone}', [DeliveryRuleController::class, 'zoneRules'])->name('zone-rules');
        });

        // Express and slightly delayed delivery, priced on top of the zone's
        // delivery rule. The amounts are the zone's own saver delivery options,
        // so a setup is addressed by its zone.
        Route::group(['prefix' => 'additional-charge', 'as' => 'additional-charge.', 'middleware' => ['module:zone']], function () {
            Route::get('list', [AdditionalChargeController::class, 'list'])->name('list');
            Route::get('create', [AdditionalChargeController::class, 'create'])->name('create');
            Route::post('store', [AdditionalChargeController::class, 'store'])->name('store');
            Route::get('edit/{zone}', [AdditionalChargeController::class, 'edit'])->name('edit');
            Route::post('update/{zone}', [AdditionalChargeController::class, 'update'])->name('update');
            Route::post('status/{zone}', [AdditionalChargeController::class, 'status'])->name('status');
            Route::delete('delete/{zone}', [AdditionalChargeController::class, 'destroy'])->name('delete');
            Route::get('export/{type}', [AdditionalChargeController::class, 'export'])->name('export');
            Route::get('zone-minimum/{zone}', [AdditionalChargeController::class, 'zoneMinimum'])->name('zone-minimum');
        });

        // Free delivery, authored per zone. A zone holds one setup, so a setup is
        // addressed by its own id and the create form only offers the zones that
        // are still without one.
        Route::group(['prefix' => 'free-delivery', 'as' => 'free-delivery.', 'middleware' => ['module:zone']], function () {
            Route::get('list', [FreeDeliveryController::class, 'list'])->name('list');
            Route::get('create', [FreeDeliveryController::class, 'create'])->name('create');
            Route::post('store', [FreeDeliveryController::class, 'store'])->name('store');
            Route::get('edit/{id}', [FreeDeliveryController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [FreeDeliveryController::class, 'update'])->name('update');
            Route::post('status/{id}', [FreeDeliveryController::class, 'status'])->name('status');
            Route::delete('delete/{id}', [FreeDeliveryController::class, 'destroy'])->name('delete');
            Route::get('export/{type}', [FreeDeliveryController::class, 'export'])->name('export');
        });

        // Estimated delivery time, authored per zone. A zone holds many
        // configurations but estimates from one, which the status endpoint
        // enforces the same way the delivery rules do.
        Route::group(['prefix' => 'eta-configuration', 'as' => 'eta-configuration.', 'middleware' => ['module:zone']], function () {
            Route::get('list', [EtaConfigurationController::class, 'list'])->name('list');
            Route::get('create', [EtaConfigurationController::class, 'create'])->name('create');
            Route::post('store', [EtaConfigurationController::class, 'store'])->name('store');
            Route::get('edit/{id}', [EtaConfigurationController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [EtaConfigurationController::class, 'update'])->name('update');
            Route::post('status/{id}', [EtaConfigurationController::class, 'status'])->name('status');
            Route::delete('delete/{id}', [EtaConfigurationController::class, 'destroy'])->name('delete');
            Route::get('export/{type}', [EtaConfigurationController::class, 'export'])->name('export');
            Route::get('zone-configurations/{zone}', [EtaConfigurationController::class, 'zoneConfigurations'])->name('zone-configurations');
        });

        // Surge pricing is authored per zone but managed from its own top-level
        // screen, so it sits beside the zone group rather than inside it. It
        // carries its own permission rather than reusing the zone one: raising
        // prices is a commercial decision, and a role may need to draw zones
        // without also being able to charge more inside them.
        Route::group(['prefix' => 'surge-price', 'as' => 'surge-price.', 'middleware' => ['module:surge_price']], function () {
            Route::get('list', [SurgePriceController::class, 'index'])->name('list');
            Route::get('create', [SurgePriceController::class, 'create'])->name('create');
            Route::post('store', [SurgePriceController::class, 'store'])->name('store');
            Route::get('status/{id}/{status}', [SurgePriceController::class, 'status'])->name('status');
            Route::get('edit/{id}', [SurgePriceController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [SurgePriceController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [SurgePriceController::class, 'destroy'])->name('delete');
            Route::get('export/{type}', [SurgePriceController::class, 'export'])->name('export');
        });

        Route::group(['prefix' => 'notification', 'as' => 'notification.', 'middleware' => ['module:notification']], function () {
            Route::get('add-new', [NotificationController::class, 'index'])->name('add-new');
            Route::post('store', [NotificationController::class, 'store'])->name('store');
            Route::get('edit/{id}', [NotificationController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [NotificationController::class, 'update'])->name('update');
            Route::get('status/{id}/{status}', [NotificationController::class, 'status'])->name('status');
            Route::delete('delete/{id}', [NotificationController::class, 'delete'])->name('delete');
            Route::get('export', [NotificationController::class, 'export'])->name('export');
        });

        Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.'], function () {

            Route::middleware(['module:settings'])->group(function () {
                Route::get('business-setup/{tab?}', [BusinessSettingsController::class, 'business_index'])->name('business-setup');
                Route::post('update-dm', [BusinessSettingsController::class, 'update_dm'])->name('update-dm');
                Route::post('update-disbursement', [BusinessSettingsController::class, 'update_disbursement'])->name('update-disbursement');
                Route::post('update-order', [BusinessSettingsController::class, 'update_order'])->name('update-order');
                Route::post('update-food', [BusinessSettingsController::class, 'updateFood'])->name('update-food');
                Route::post('update-priority', [BusinessSettingsController::class, 'update_priority'])->name('update-priority');
                Route::post('update-restaurant', [BusinessSettingsController::class, 'update_restaurant'])->name('update-restaurant');

                Route::post('update-setup', [BusinessSettingsController::class, 'business_setup'])->name('update-setup');
                Route::post('update-payment-setup', [BusinessSettingsController::class, 'updatePaymentSetup'])->name('update-payment-setup');
                Route::get('theme-settings', [BusinessSettingsController::class, 'theme_settings'])->name('theme-settings');
                Route::POST('theme-settings-update', [BusinessSettingsController::class, 'update_theme_settings'])->name('theme-settings-update');

                // Invoice setup
                Route::match(['get', 'post'], 'invoice-setup', [BusinessSettingsController::class, 'invoiceSetup'])->name('invoice-setup');

                Route::get('pages/terms-and-conditions', [BusinessSettingsController::class, 'terms_and_conditions'])->name('terms-and-conditions');
                Route::post('pages/terms-and-conditions', [BusinessSettingsController::class, 'terms_and_conditions_update']);

                Route::get('pages/privacy-policy', [BusinessSettingsController::class, 'privacy_policy'])->name('privacy-policy');
                Route::post('pages/privacy-policy', [BusinessSettingsController::class, 'privacy_policy_update']);

                Route::get('pages/refund-policy', [BusinessSettingsController::class, 'refund_policy'])->name('refund-policy');
                Route::post('pages/refund-policy', [BusinessSettingsController::class, 'refund_policy_update']);
                Route::get('ajax-pages/refund-policy/{status}', [BusinessSettingsController::class, 'refund_policy_status'])->name('refund-policy-status');

                Route::get('pages/shipping-policy', [BusinessSettingsController::class, 'shipping_policy'])->name('shipping-policy');
                Route::post('pages/shipping-policy', [BusinessSettingsController::class, 'shipping_policy_update']);
                Route::get('ajax-pages/shipping-policy/{status}', [BusinessSettingsController::class, 'shipping_policy_status'])->name('shipping-policy-status');

                Route::get('pages/cancellation-policy', [BusinessSettingsController::class, 'cancellation_policy'])->name('cancellation-policy');
                Route::post('pages/cancellation-policy', [BusinessSettingsController::class, 'cancellation_policy_update']);
                Route::get('ajax-pages/cancellation-policy/{status}', [BusinessSettingsController::class, 'cancellation_policy_status'])->name('cancellation-policy-status');

                Route::get('pages/about-us', [BusinessSettingsController::class, 'about_us'])->name('about-us');
                Route::post('pages/about-us', [BusinessSettingsController::class, 'about_us_update']);

                Route::get('social-media/fetch', [SocialMediaController::class, 'fetch'])->name('social-media.fetch');
                Route::get('social-media/status-update', [SocialMediaController::class, 'social_media_status_update'])->name('social-media.status-update');
                // Route::resource('social-media', SocialMediaController::class);
                Route::get('social-media', [SocialMediaController::class, 'index'])->name('social-media.index');
                Route::post('social-media', [SocialMediaController::class, 'store'])->name('social-media.store');
                Route::get('social-media/{id}', [SocialMediaController::class, 'show'])->name('social-media.show');
                Route::get('social-media/{id}/edit', [SocialMediaController::class, 'edit'])->name('social-media.edit');
                Route::put('social-media/{id}', [SocialMediaController::class, 'update'])->name('social-media.update');

                Route::get('site_direction', [BusinessSettingsController::class, 'site_direction'])->name('site_direction');

                Route::get('email-setup/{type}/{tab?}', [BusinessSettingsController::class, 'email_index'])->name('email-setup');
                Route::POST('email-setup/{type}/{tab?}', [BusinessSettingsController::class, 'update_email_index'])->name('email-setup');
                Route::get('email-status/{type}/{tab}/{status}', [BusinessSettingsController::class, 'update_email_status'])->name('email-status');

                // React Registration Page
                Route::group(['prefix' => 'registration-page'], function () {
                    Route::group(['prefix' => 'react', 'as' => 'react-registration-page.'], function () {
                        Route::get('hero', [RegistrationPageController::class, 'react_hero_index'])->name('hero');
                        Route::post('hero-update', [RegistrationPageController::class, 'react_hero_save'])->name('hero-update');
                        Route::get('stepper', [RegistrationPageController::class, 'react_stepper_index'])->name('stepper');
                        Route::post('stepper-update/{tab}', [RegistrationPageController::class, 'update_react_stepper'])->name('stepper-update');
                        // opportunities
                        Route::get('opportunities', [RegistrationPageController::class, 'opportunities'])->name('opportunities');
                        Route::post('opportunity-store/', [RegistrationPageController::class, 'opportunity_store'])->name('opportunity_store');
                        Route::get('opportunity-status/{id}/{status}', [RegistrationPageController::class, 'opportunity_status'])->name('opportunity_status');
                        Route::get('opportunity/edit/{id}', [RegistrationPageController::class, 'opportunity_edit'])->name('opportunity_edit');
                        Route::post('opportunity/update/{id}', [RegistrationPageController::class, 'opportunity_update'])->name('opportunity_update');
                        Route::delete('opportunity/delete/{opportunity}', [RegistrationPageController::class, 'opportunity_destroy'])->name('opportunity_delete');
                        // faqs
                        Route::get('faqs', [RegistrationPageController::class, 'faqs'])->name('faqs');
                        Route::post('faq-store/', [RegistrationPageController::class, 'faq_store'])->name('faq_store');
                        Route::get('faq-status/{id}/{status}', [RegistrationPageController::class, 'faq_status'])->name('faq_status');
                        Route::get('faq/edit/{id}', [RegistrationPageController::class, 'faq_edit'])->name('faq_edit');
                        Route::post('faq/update/{id}', [RegistrationPageController::class, 'faq_update'])->name('faq_update');
                        Route::delete('faq/delete/{faq}', [RegistrationPageController::class, 'faq_destroy'])->name('faq_delete');
                    });
                });

            });

            Route::middleware(['module:system_settings'])->group(function () {
                Route::get('fcm-index', [BusinessSettingsController::class, 'fcm_index'])->name('fcm-index');
                Route::get('notification-messages', [BusinessSettingsController::class, 'notificationMessages'])->name('notificationMessages');
                Route::post('update-fcm', [BusinessSettingsController::class, 'update_fcm'])->name('update-fcm');
                Route::post('update-fcm-messages', [BusinessSettingsController::class, 'updateFcmMessages'])->name('updateFcmMessages');
                Route::get('mail-config', [BusinessSettingsController::class, 'mail_index'])->name('mail-config');
                Route::post('mail-config', [BusinessSettingsController::class, 'mail_config']);
                Route::post('mail-config-status', [BusinessSettingsController::class, 'mail_config_status'])->name('mail-config-status');
                Route::get('ajax-send-mail', [BusinessSettingsController::class, 'send_mail'])->name('mail.send');
                Route::get('payment-method', [BusinessSettingsController::class, 'payment_index'])->name('payment-method');
                Route::post('payment-method-update', [BusinessSettingsController::class, 'payment_config_update'])->name('payment-method-update');
                Route::get('sms-module', [SMSModuleController::class, 'sms_index'])->name('sms-module');
                Route::post('sms-module-update/{sms_module}', [SMSModuleController::class, 'sms_update'])->name('sms-module-update');

                // recaptcha
                Route::get('recaptcha', [BusinessSettingsController::class, 'recaptcha_index'])->name('recaptcha_index');
                Route::post('recaptcha-update', [BusinessSettingsController::class, 'recaptcha_update'])->name('recaptcha_update');
                // firebase-otp
                Route::get('firebase-otp', [BusinessSettingsController::class, 'firebase_otp_index'])->name('firebase_otp_index');
                Route::post('firebase-otp-update', [BusinessSettingsController::class, 'firebase_otp_update'])->name('firebase_otp_update');

                Route::group(['prefix' => 'marketing', 'as' => 'marketing.'], function () {
                    Route::get('analytic-setup', [AnalyticScriptController::class, 'analyticSetup'])->name('analytic');
                    Route::post('analytic-setup-update', [AnalyticScriptController::class, 'analyticUpdate'])->name('analyticUpdate');
                    Route::get('analytic-status', [AnalyticScriptController::class, 'analyticStatus'])->name('analyticStatus');
                });

                Route::get('restaurant/join-us/setup', [PageSetupController::class, 'restaurant_page_setup'])->name('restaurant_page_setup');
                Route::post('restaurant/join-us/update', [PageSetupController::class, 'restaurant_page_setup_update'])->name('restaurant_page_setup_update');
                Route::get('deliveryman/join-us/setup', [PageSetupController::class, 'deliveryman_page_setup'])->name('delivery_man_page_setup');
                Route::post('delivery-man/join-us/update', [PageSetupController::class, 'deliveryman_page_setup_update'])->name('delivery_man_page_setup_update');
                // Offline payment Methods
                Route::get('/offline-payment', [OfflinePaymentMethodController::class, 'index'])->name('offline');
                Route::get('/offline-payment/new', [OfflinePaymentMethodController::class, 'create'])->name('offline.new');
                Route::post('/offline-payment/store', [OfflinePaymentMethodController::class, 'store'])->name('offline.store');
                Route::get('/offline-payment/edit/{id}', [OfflinePaymentMethodController::class, 'edit'])->name('offline.edit');
                Route::post('/offline-payment/update', [OfflinePaymentMethodController::class, 'update'])->name('offline.update');
                Route::post('/offline-payment/delete', [OfflinePaymentMethodController::class, 'delete'])->name('offline.delete');
                Route::get('/offline-payment/status/{id}', [OfflinePaymentMethodController::class, 'status'])->name('offline.status');

                // file_system
                Route::get('storage-connection', [BusinessSettingsController::class, 'storage_connection_index'])->name('storage_connection_index');
                Route::post('storage-connection-update/{name}', [BusinessSettingsController::class, 'storage_connection_update'])->name('storage_connection_update');

                // openAI
                Route::get('open-ai', [BusinessSettingsController::class, 'openAI'])->name('openAI');
                Route::get('open-ai-settings', [BusinessSettingsController::class, 'openAISettings'])->name('openAISettings');
                Route::put('open-ai-settings-update', [BusinessSettingsController::class, 'openAISettingsUpdate'])->name('openAISettingsUpdate');
                Route::get('open-ai-config-status', [BusinessSettingsController::class, 'openAIConfigStatus'])->name('openAIConfigStatus');
                Route::post('openai-update', [BusinessSettingsController::class, 'openAIConfigUpdate'])->name('openAIConfigUpdate');

                // db clean
                Route::get('db-index', [DatabaseSettingController::class, 'db_index'])->name('db-index');
                Route::post('db-clean', [DatabaseSettingController::class, 'clean_db'])->name('clean-db');

                Route::get('app-settings', [BusinessSettingsController::class, 'app_settings'])->name('app-settings');
                Route::POST('app-settings', [BusinessSettingsController::class, 'update_app_settings'])->name('update_app_settings');
                Route::POST('user-app-download-update', [BusinessSettingsController::class, 'user_app_download_update'])->name('user-app-download-update');
                Route::get('notification-setup', [BusinessSettingsController::class, 'notification_setup'])->name('notification_setup');
                Route::get('notification-status-change/{key}/{user_type}/{type}', [BusinessSettingsController::class, 'notification_status_change'])->name('notification_status_change');
                Route::get('config-setup', [BusinessSettingsController::class, 'config_setup'])->name('config-setup');
                Route::post('config-update', [BusinessSettingsController::class, 'config_update'])->name('config-update');
                Route::get('toggle-settings/{key}/{value}', [BusinessSettingsController::class, 'toggle_settings'])->name('toggle-settings');
            });

        });

        Route::group(['prefix' => 'withdraw-method', 'as' => 'business-settings.withdraw-method.', 'middleware' => ['module:withdraw_list']], function () {
            Route::get('list', [WithdrawalMethodController::class, 'list'])->name('list');
            Route::get('create', [WithdrawalMethodController::class, 'create'])->name('create');
            Route::post('store', [WithdrawalMethodController::class, 'store'])->name('store');
            Route::get('edit/{id}', [WithdrawalMethodController::class, 'edit'])->name('edit');
            Route::put('update', [WithdrawalMethodController::class, 'update'])->name('update');
            Route::delete('delete/{id}', [WithdrawalMethodController::class, 'delete'])->name('delete');
            Route::post('status-update', [WithdrawalMethodController::class, 'status_update'])->name('status-update');
            Route::post('default-status-update', [WithdrawalMethodController::class, 'default_status_update'])->name('default-status-update');
        });

        Route::group(['prefix' => 'landing-page', 'as' => 'landing_page.', 'middleware' => ['module:system_settings']], function () {
            Route::post('landing-page-settings/{tab}', [LandingPageController::class, 'update_admin_landing_page_settings'])->name('settings');
            // landing page
            Route::get('setup', [LandingPageController::class, 'landingPageSetup'])->name('setup');
            Route::post('ajax-update-landing-setup', [LandingPageController::class, 'landingPageSettingsUpdate'])->name('update-landing-setup');
            Route::delete('delete-custom-landing-page', [LandingPageController::class, 'deleteCustomLandingPage'])->name('delete-custom-landing-page');
            Route::get('download-custom-landing-page', [LandingPageController::class, 'downloadCustomLandingPage'])->name('download-custom-landing-page');

            Route::get('status-update/{type}/{key}', [LandingPageController::class, 'statusUpdate'])->name('statusUpdate');
            // testimonials
            Route::get('testimonials', [LandingPageController::class, 'testimonial'])->name('testimonial');
            Route::post('testimonial-store/', [LandingPageController::class, 'testimonial_store'])->name('testimonial_store');
            Route::get('testimonial-status/{id}/{status}', [LandingPageController::class, 'testimonial_status'])->name('testimonial_status');
            Route::get('testimonial/edit/{id}', [LandingPageController::class, 'testimonial_edit'])->name('testimonial_edit');
            Route::post('testimonial/update/{id}', [LandingPageController::class, 'testimonial_update'])->name('testimonial_update');
            Route::delete('testimonial/delete/{testimonial}', [LandingPageController::class, 'testimonial_destroy'])->name('testimonial_delete');
            // testimonials end
            // features
            Route::get('features', [LandingPageController::class, 'features'])->name('features');
            Route::post('feature-store/', [LandingPageController::class, 'feature_store'])->name('feature_store');
            Route::get('feature-status/{id}/{status}', [LandingPageController::class, 'feature_status'])->name('feature_status');
            Route::get('feature/edit/{id}', [LandingPageController::class, 'feature_edit'])->name('feature_edit');
            Route::post('feature/update/{id}', [LandingPageController::class, 'feature_update'])->name('feature_update');
            Route::delete('feature/delete/{feature}', [LandingPageController::class, 'feature_destroy'])->name('feature_delete');
            // features end

            Route::get('header', [LandingPageController::class, 'header'])->name('header');
            Route::get('about-us', [LandingPageController::class, 'about_us'])->name('about_us');
            Route::get('why-choose-us', [LandingPageController::class, 'why_choose_us'])->name('why_choose_us');
            Route::get('earn-money', [LandingPageController::class, 'earn_money'])->name('earn_money');
            Route::get('services', [LandingPageController::class, 'services'])->name('services');
            Route::get('fixed-data', [LandingPageController::class, 'fixed_data'])->name('fixed_data');
            Route::get('links', [LandingPageController::class, 'links'])->name('links');
            Route::get('backgroung-color', [LandingPageController::class, 'backgroung_color'])->name('backgroung_color');
            Route::get('available-zone', [LandingPageController::class, 'availableZone'])->name('available_zone');
            Route::post('available-zone-update', [LandingPageController::class, 'availableZoneUpdate'])->name('availableZoneUpdate');

            Route::get('meta-data', [LandingPageController::class, 'meta_data'])->name('meta_data');
        });
        Route::group(['prefix' => 'react-landing-page', 'as' => 'react_landing_page.', 'middleware' => ['module:system_settings']], function () {
            Route::post('landing-page-settings/{tab}', [LandingPageController::class, 'update_react_landing_page_settings'])->name('settings');

            Route::get('header', [LandingPageController::class, 'react_header'])->name('react_header');

            Route::get('stepper-section', [LandingPageController::class, 'stepperSection'])->name('stepperSection');
            Route::get('categories', [LandingPageController::class, 'categories'])->name('categories');
            Route::get('FAQ-section', [LandingPageController::class, 'faqSection'])->name('faqSection');
            Route::get('testimonials', [LandingPageController::class, 'testimonials'])->name('testimonials');
            Route::get('gallery', [LandingPageController::class, 'gallery'])->name('gallery');

            Route::get('fixed-data', [LandingPageController::class, 'react_fixed_data'])->name('react_fixed_data');
            Route::get('meta-data', [LandingPageController::class, 'react_meta_data'])->name('meta_data');
            // services
            Route::get('services', [LandingPageController::class, 'react_services'])->name('react_services');
            Route::post('service-store/', [LandingPageController::class, 'react_service_store'])->name('service_store');
            Route::get('service-status/{id}/{status}', [LandingPageController::class, 'react_service_status'])->name('service_status');
            Route::get('service/edit/{id}', [LandingPageController::class, 'react_service_edit'])->name('service_edit');
            Route::post('service/update/{id}', [LandingPageController::class, 'react_service_update'])->name('service_update');
            Route::delete('service/delete/{service}', [LandingPageController::class, 'react_service_destroy'])->name('service_delete');
            Route::get('service-export', [LandingPageController::class, 'service_export'])->name('service_export');
            // services end

            // testimonials

            Route::post('testimonial-store/', [LandingPageController::class, 'reactTestimonialStore'])->name('reactTestimonialStore');
            Route::get('testimonial-status/{id}/{status}', [LandingPageController::class, 'reactTestimonialStatus'])->name('reactTestimonialStatus');
            Route::get('testimonial/edit/{id}', [LandingPageController::class, 'reactTestimonialEdit'])->name('reactTestimonialEdit');
            Route::post('testimonial/update/{id}', [LandingPageController::class, 'reactTestimonialUpdate'])->name('reactTestimonialUpdate');
            Route::delete('testimonial/delete/{testimonial}', [LandingPageController::class, 'reactTestimonialDestroy'])->name('reactTestimonialDestroy');
            // faqs
            Route::post('faq-store/', [LandingPageController::class, 'reactfaqStore'])->name('reactfaqStore');
            Route::get('faq-status/{id}/{status}', [LandingPageController::class, 'reactfaqStatus'])->name('reactfaqStatus');
            Route::get('faq/edit/{id}', [LandingPageController::class, 'reactfaqEdit'])->name('reactfaqEdit');
            Route::post('faq-data/update/{id}', [LandingPageController::class, 'reactFaqUpdate'])->name('reactFaqUpdate');
            Route::delete('faq/delete/{faq}', [LandingPageController::class, 'reactfaqDestroy'])->name('reactfaqDestroy');

            // promotional_banner
            Route::get('promotional-banner', [LandingPageController::class, 'react_promotional_banner'])->name('promotional_banner');
            Route::post('promotional-banner-store/', [LandingPageController::class, 'react_promotional_banner_store'])->name('promotional_banner_store');
            Route::get('promotional-banner-status/{id}/{status}', [LandingPageController::class, 'react_promotional_banner_status'])->name('promotional_banner_status');
            Route::get('promotional-banner/edit/{id}', [LandingPageController::class, 'react_promotional_banner_edit'])->name('promotional_banner_edit');
            Route::post('promotional-banner/update/{id}', [LandingPageController::class, 'react_promotional_banner_update'])->name('promotional_banner_update');
            Route::delete('promotional-banner/delete/{react_promotional_banner}', [LandingPageController::class, 'react_promotional_banner_destroy'])->name('promotional_banner_delete');
            Route::get('promotional-banner-export', [LandingPageController::class, 'react_promotional_banners_export'])->name('react_promotional_banners_export');
            // promotional_banner end

            Route::get('download-apps', [LandingPageController::class, 'download_apps'])->name('download_apps');

            Route::get('available-zone', [LandingPageController::class, 'reactAvailableZone'])->name('available_zone');
            Route::post('available-zone-update', [LandingPageController::class, 'availableZoneUpdate'])->name('availableZoneUpdate');
            Route::post('location-picker-update', [LandingPageController::class, 'locationPickerUpdate'])->name('locationPickerUpdate');

            // Registration Section
            Route::get('registration-section', [LandingPageController::class, 'registration_section'])->name('registration_section');
            Route::post('earn-money-section', [LandingPageController::class, 'earn_money_section'])->name('earn_money_section');

        });

        Route::get('page-meta-data', [LandingPageController::class, 'pageMetaData'])->name('pageMetaData')->middleware('module:system_settings');
        Route::post('page-meta-data-update', [LandingPageController::class, 'pageMetaDataUpdate'])->name('pageMetaDataUpdate')->middleware('module:system_settings');

        Route::group(['prefix' => 'message', 'as' => 'message.', 'middleware' => ['module:chat']], function () {
            Route::get('list', [ConversationController::class, 'list'])->name('list');
            Route::post('store/{user_id}', [ConversationController::class, 'store'])->name('store');
            Route::get('view/{conversation_id}/{user_id}', [ConversationController::class, 'view'])->name('view');
        });

        Route::group(['prefix' => 'delivery-man', 'as' => 'delivery-man.'], function () {
            Route::get('get-deliverymen', [DeliveryManController::class, 'get_deliverymen'])->name('get-deliverymen');
            Route::get('get-account-data/{deliveryman}', [DeliveryManController::class, 'get_account_data'])->name('get_account_data');
            Route::group(['middleware' => ['module:deliveryman']], function () {
                Route::get('add', [DeliveryManController::class, 'index'])->name('add');
                Route::post('store', [DeliveryManController::class, 'store'])->name('store');
                Route::get('list', [DeliveryManController::class, 'list'])->name('list');
                Route::get('preview/{id}/{tab?}', [DeliveryManController::class, 'preview'])->name('preview');
                Route::get('status/{id}/{status}', [DeliveryManController::class, 'status'])->name('status');
                Route::get('earning/{id}/{status}', [DeliveryManController::class, 'earning'])->name('earning');
                Route::get('update-application/{id}/{status}', [DeliveryManController::class, 'update_application'])->name('application');
                Route::get('edit/{id}', [DeliveryManController::class, 'edit'])->name('edit');
                Route::post('update/{id}', [DeliveryManController::class, 'update'])->name('update');
                Route::delete('delete/{id}', [DeliveryManController::class, 'delete'])->name('delete');
                Route::get('export-delivery-man', [DeliveryManController::class, 'dm_list_export'])->name('export-delivery-man');
                Route::get('pending/list', [DeliveryManController::class, 'pending'])->name('pending');
                Route::get('denied/list', [DeliveryManController::class, 'denied'])->name('denied');
                Route::get('earning-export', [DeliveryManController::class, 'earning_export'])->name('earning-export');
                Route::get('review-export', [DeliveryManController::class, 'review_export'])->name('review-export');
                Route::get('disbursement-export/{id}/{type}', [DeliveryManController::class, 'disbursement_export'])->name('disbursement-export');
                Route::get('pending-delivery-man-view/{id}', [DeliveryManController::class, 'pending_dm_view'])->name('pending_dm_view');

                Route::group(['prefix' => 'reviews', 'as' => 'reviews.'], function () {
                    Route::get('list', [DeliveryManController::class, 'reviews_list'])->name('list');
                    Route::get('status/{id}/{status}', [DeliveryManController::class, 'reviews_status'])->name('status');
                    Route::get('export', [DeliveryManController::class, 'reviews_export'])->name('export');

                });

                // incentive
                Route::get('incentive', [DeliveryManController::class, 'pending_incentives'])->name('incentive');
                Route::get('incentive-history', [DeliveryManController::class, 'get_incentives'])->name('incentive-history');
                Route::put('incentive', [DeliveryManController::class, 'update_incentive_status']);
                Route::post('incentive_all', [DeliveryManController::class, 'update_all_incentive_status'])->name('update-incentive');
                // bonus
                Route::get('bonus', [DeliveryManController::class, 'get_bonus'])->name('bonus');
                Route::post('bonus', [DeliveryManController::class, 'add_bonus'])->name('add-bonus');
                // message
                Route::get('message/{conversation_id}/{user_id}', [DeliveryManController::class, 'conversation_view'])->name('message-view');
                Route::get('{user_id}/message/list', [DeliveryManController::class, 'conversation_list'])->name('message-list');
                Route::get('messages/details', [DeliveryManController::class, 'get_conversation_list'])->name('message-list-search');
            });
        });

        // Pos system
        Route::group(['prefix' => 'pos', 'as' => 'pos.'], function () {
            Route::post('variant_price', [POSController::class, 'variant_price'])->name('variant_price');
            Route::group(['middleware' => ['module:pos']], function () {
                Route::get('/', [POSController::class, 'index'])->name('index');
                Route::get('quick-view', [POSController::class, 'quick_view'])->name('quick-view');
                Route::get('quick-view-cart-item', [POSController::class, 'quick_view_card_item'])->name('quick-view-cart-item');
                Route::post('add-to-cart', [POSController::class, 'addToCart'])->name('add-to-cart');
                Route::post('add-delivery-address', [POSController::class, 'addDeliveryInfo'])->name('add-delivery-address');
                Route::post('remove-from-cart', [POSController::class, 'removeFromCart'])->name('remove-from-cart');
                Route::post('cart-items', [POSController::class, 'cart_items'])->name('cart_items');
                Route::post('update-quantity', [POSController::class, 'updateQuantity'])->name('updateQuantity');
                Route::post('empty-cart', [POSController::class, 'emptyCart'])->name('emptyCart');
                Route::post('tax', [POSController::class, 'update_tax'])->name('tax');
                Route::post('paid', [POSController::class, 'update_paid'])->name('paid');
                Route::post('discount', [POSController::class, 'update_discount'])->name('discount');
                Route::get('customers', [POSController::class, 'get_customers'])->name('customers');
                Route::get('select-customer', [POSController::class, 'select_customer'])->name('select-customer');
                Route::post('place-order', [POSController::class, 'place_order'])->name('order');
                Route::get('orders', [POSController::class, 'order_list'])->name('orders');
                Route::post('search', [POSController::class, 'search'])->name('search');
                Route::get('invoice/{id}', [POSController::class, 'generate_invoice']);
                Route::post('customer-store', [POSController::class, 'customer_store'])->name('customer-store');
                Route::get('get-user-data', [POSController::class, 'getUserData'])->name('getUserData');

                Route::get('get-user-address', [POSController::class, 'getUserAddress'])->name('getUserAddress');
                Route::get('choose-address', [POSController::class, 'chooseAddress'])->name('chooseAddress');
                Route::get('edit-address', [POSController::class, 'editAddress'])->name('editAddress');
                Route::get('clear-user-data', [POSController::class, 'clearUserData'])->name('clearUserData');
                Route::get('set-order-type', [POSController::class, 'setOrderType'])->name('setOrderType');
                Route::get('get-delivery-types', [POSController::class, 'getDeliveryTypes'])->name('get-delivery-types');
                Route::get('set-delivery-type', [POSController::class, 'setDeliveryType'])->name('set-delivery-type');
                Route::get('get-delivery-coverage', [POSController::class, 'getDeliveryCoverage'])->name('get-delivery-coverage');
                Route::get('set-delivery-coverage', [POSController::class, 'setDeliveryCoverage'])->name('set-delivery-coverage');
            });
        });
        Route::group(['prefix' => 'reviews', 'as' => 'reviews.', 'middleware' => ['module:customerList']], function () {
            Route::post('search', [ReviewsController::class, 'search'])->name('search');
            Route::get('details/{review}', [ReviewsController::class, 'details'])->name('details');

        });

        Route::group(['prefix' => 'report', 'as' => 'report.', 'middleware' => ['module:report']], function () {
            Route::get('transaction-report', [ReportController::class, 'day_wise_report'])->name('day-wise-report');
            Route::get('food-wise-report', [ReportController::class, 'food_wise_report'])->name('food-wise-report');
            Route::get('food-wise-report-export', [ReportController::class, 'food_wise_report_export'])->name('food-wise-report-export');
            Route::get('transaction-report-export', [ReportController::class, 'day_wise_report_export'])->name('day-wise-report-export');
            Route::post('set-date', [ReportController::class, 'set_date'])->name('set-date');
            Route::get('expense-report', [ReportController::class, 'expense_report'])->name('expense-report');
            Route::get('expense-export', [ReportController::class, 'expense_export'])->name('expense-export');
            Route::post('expense-report-search', [ReportController::class, 'expense_search'])->name('expense-report-search');

            Route::get('subscription-report', [ReportController::class, 'subscription_report'])->name('subscription-report');
            Route::get('subscription-export', [ReportController::class, 'subscription_export'])->name('subscription-export');

            Route::get('restaurant-report', [ReportController::class, 'restaurant_report'])->name('restaurant-report');
            Route::get('restaurant-export', [ReportController::class, 'restaurant_export'])->name('restaurant-wise-report-export');

            Route::get('generate-statement/{id}', [ReportController::class, 'generate_statement'])->name('generate-statement');
            Route::get('subscription/generate-statement/{id}', [ReportController::class, 'subscription_generate_statement'])->name('subscription.generate-statement');

            Route::get('order-report', [ReportController::class, 'order_report'])->name('order-report');
            Route::post('order-report-search', [ReportController::class, 'search_order_report'])->name('search_order_report');
            Route::get('order-report-export', [ReportController::class, 'order_report_export'])->name('order-report-export');

            Route::get('campaign-order-report', [ReportController::class, 'campaign_order_report'])->name('campaign_order-report');
            Route::get('campaign-order-report-export', [ReportController::class, 'campaign_report_export'])->name('campaign_report_export');

            // Admin Earning Report
            Route::get('admin-earning-report', [AdminEarningReportController::class, 'getAdminEarningReport'])->name('admin-earning-report');
            // new endpoints for partial data retrieval
            Route::get('admin-earning-summary', [AdminEarningReportController::class, 'getAdminEarningSummary'])->name('admin-earning-summary');
            Route::get('admin-earning-breakdown', [AdminEarningReportController::class, 'getAdminEarningBreakdown'])->name('admin-earning-breakdown');
            Route::get('admin-expense-breakdown', [AdminEarningReportController::class, 'getAdminExpenseBreakdown'])->name('admin-expense-breakdown');
            Route::get('admin-monthly-earnings', [AdminEarningReportController::class, 'getMonthlyEarningsReport'])->name('admin-monthly-earnings');
            Route::get('admin-zone-wise-earnings', [AdminEarningReportController::class, 'getZoneWiseEarnings'])->name('admin-zone-wise-earnings');
            Route::get('admin-top-earning-restaurants', [AdminEarningReportController::class, 'getTopEarningRestaurants'])->name('admin-top-earning-restaurants');
            Route::get('admin-earning-transactions', [AdminEarningReportController::class, 'getEarningTransactions'])->name('admin-earning-transactions');
            Route::get('admin-earning-export', [AdminEarningReportController::class, 'exportEarningTransactions'])->name('admin-earning-export');
            Route::get('admin-deliveryman-earning-transactions', [AdminEarningReportController::class, 'getDeliverymanEarningTransactions'])->name('admin-deliveryman-earning-transactions');
            Route::get('admin-deliveryman-earning-export', [AdminEarningReportController::class, 'exportDeliverymanEarningTransactions'])->name('admin-deliveryman-earning-export');

            // Restaurant Earning Report
            Route::get('restaurant-earning-report', [RestaurantEarningReportController::class, 'getRestaurantEarningReport'])->name('restaurant-earning-report');
            Route::get('restaurant-earning-summary', [RestaurantEarningReportController::class, 'getRestaurantEarningSummary'])->name('restaurant-earning-summary');
            Route::get('restaurant-earning-breakdown', [RestaurantEarningReportController::class, 'getRestaurantEarningBreakdown'])->name('restaurant-earning-breakdown');
            Route::get('restaurant-expense-breakdown', [RestaurantEarningReportController::class, 'getRestaurantExpenseBreakdown'])->name('restaurant-expense-breakdown');
            Route::get('restaurant-earning-trend', [RestaurantEarningReportController::class, 'getRestaurantEarningTrend'])->name('restaurant-earning-trend');
            Route::get('restaurant-earning-transactions', [RestaurantEarningReportController::class, 'getRestaurantEarningTransactions'])->name('restaurant-earning-transactions');
            Route::get('restaurant-earning-export', [RestaurantEarningReportController::class, 'exportRestaurantEarningTransactions'])->name('restaurant-earning-export');
            // Route::get('campaign-order-report-export', [ReportController::class, 'campaign_report_export'])->name('campaign_report_export');
            // Deliveryman Earning Report
            Route::get('deliveryman-earning-report', [DeliverymanEarningReportController::class, 'getDeliverymanEarningReport'])->name('deliveryman-earning-report');
            Route::get('deliveryman-earning-summary', [DeliverymanEarningReportController::class, 'getDeliverymanEarningSummary'])->name('deliveryman-earning-summary');
            Route::get('deliveryman-earning-breakdown', [DeliverymanEarningReportController::class, 'getDeliverymanEarningBreakdown'])->name('deliveryman-earning-breakdown');
            Route::get('deliveryman-expense-breakdown', [DeliverymanEarningReportController::class, 'getDeliverymanExpenseBreakdown'])->name('deliveryman-expense-breakdown');
            Route::get('deliveryman-earning-trend', [DeliverymanEarningReportController::class, 'getDeliverymanEarningTrend'])->name('deliveryman-earning-trend');

            Route::get('disbursement-report/{tab?}', [ReportController::class, 'disbursement_report'])->name('disbursement_report');
            Route::get('disbursement-report-export/{type}/{tab?}', [ReportController::class, 'disbursement_report_export'])->name('disbursement_report_export');

            Route::get('vendor-wise-taxes', [VendorTaxReportController::class, 'vendorWiseTaxes'])->name('vendorWiseTaxes');
            Route::get('vendor-wise-taxes-export', [VendorTaxReportController::class, 'vendorWiseTaxExport'])->name('vendorWiseTaxExport');
            Route::get('vendor-tax-report', [VendorTaxReportController::class, 'vendorTax'])->name('vendorTax');
            Route::get('vendor-tax-export', [VendorTaxReportController::class, 'vendorTaxExport'])->name('vendorTaxExport');

            Route::get('get-tax-export', [AdminTaxReportController::class, 'getTaxReport'])->name('getTaxReport');
            Route::get('get-tax-list', [AdminTaxReportController::class, 'getTaxList'])->name('getTaxList');
            Route::get('get-tax-details', [AdminTaxReportController::class, 'getTaxDetails'])->name('getTaxDetails');
            Route::get('tax-details-report-export', [AdminTaxReportController::class, 'adminTaxDetailsExport'])->name('getTaxDetailsExport');
            Route::get('admin-tax-report-export', [AdminTaxReportController::class, 'adminTaxReportExport'])->name('adminTaxReportExport');
        });
        Route::get('customer/wallet/report', [CustomerWalletController::class, 'report'])->name('customer.wallet.report')->middleware('module:report');
        Route::get('customer/wallet/export', [CustomerWalletController::class, 'export'])->name('customer.wallet.export')->middleware('module:report');
        Route::get('customer/overview/report', [CustomerReportController::class, 'index'])->name('customer.overview.report')->middleware('module:report');
        Route::get('customer/overview/export', [CustomerReportController::class, 'export'])->name('customer.overview.export')->middleware('module:report');

        // AJAX endpoints for customer overview report
        Route::group(['prefix' => 'customer/overview', 'as' => 'customer.', 'middleware' => ['module:report']], function () {
            Route::get('counts-partial', [CustomerReportController::class, 'overviewCountsPartial'])->name('overview-counts-partial');
            Route::get('order-statistics-partial', [CustomerReportController::class, 'orderStatisticsPartial'])->name('order-statistics-partial');
            Route::get('onboarding-statistics-partial', [CustomerReportController::class, 'onboardingStatisticsPartial'])->name('onboarding-statistics-partial');
            Route::get('top-customers-partial', [CustomerReportController::class, 'topCustomersPartial'])->name('top-customers-partial');
        });

        Route::get('customer/select-list', [CustomerController::class, 'get_customers'])->name('customer.select-list');
        Route::group(['prefix' => 'customer', 'as' => 'customer.', 'middleware' => ['module:customerList']], function () {
            Route::get('list', [CustomerController::class, 'customer_list'])->name('list');
            Route::get('view/{user_id}', [CustomerController::class, 'view'])->name('view');
            // Route::post('search', [CustomerController::class, 'search'])->name('search');
            // Route::post('order-search', [CustomerController::class, 'order_search'])->name('order_search');
            Route::put('status/{customer}', [CustomerController::class, 'status'])->name('status');
            Route::get('logs', [VisitorLogController::class, 'index'])->name('visitor_logs');
            Route::get('top-items/{id}', [CustomerController::class, 'topItems'])->name('top-items');
            Route::get('edit-customer-address/{id}', [CustomerController::class, 'editCustomerAddress'])->name('edit-customer-address');
            Route::put('update-customer-address/{id}', [CustomerController::class, 'updateCustomerAddress'])->name('update-customer-address');
            Route::get('add-customer-address/{id}', [CustomerController::class, 'addCustomerAddress'])->name('add-customer-address');

             Route::get('order-list/{id}', [CustomerController::class, 'getOrderList'])->name('order-list');
             Route::get('wish-list/{id}', [CustomerController::class, 'getWishList'])->name('wish-list');
             Route::get('review-list/{id}', [CustomerController::class, 'getReviewList'])->name('review-list');
             Route::get('loyalty-point/{id}', [CustomerController::class, 'getLoyaltyPointView'])->name('loyalty-point');
             Route::get('referral/{id}', [CustomerController::class, 'getReferralView'])->name('referral');
             Route::get('wallet-history/{id}', [CustomerController::class, 'getWalletHistoryView'])->name('wallet-history');
             Route::get('subscription-plan/{id}', [CustomerController::class, 'getSubscriptionPlanView'])->name('subscription-plan');

            Route::group(['prefix' => 'wallet', 'as' => 'wallet.', 'middleware' => ['module:customer_wallet']], function () {
                Route::get('add-fund', [CustomerWalletController::class, 'add_fund_view'])->name('add-fund');
                Route::post('add-fund', [CustomerWalletController::class, 'add_fund'])->name('add-fund');
                Route::group(['prefix' => 'bonus', 'as' => 'bonus.'], function () {
                    Route::get('add-new', [WalletBonusController::class, 'add_new'])->name('add-new');
                    Route::post('store', [WalletBonusController::class, 'store'])->name('store');
                    Route::get('update/{id}', [WalletBonusController::class, 'edit'])->name('update');
                    Route::post('update/{id}', [WalletBonusController::class, 'update'])->name('update');
                    Route::get('status/{id}/{status}', [WalletBonusController::class, 'status'])->name('status');
                    Route::delete('delete/{id}', [WalletBonusController::class, 'delete'])->name('delete');
                    Route::post('search', [WalletBonusController::class, 'search'])->name('search');
                });
            });

            // Subscribed customer Routes
            Route::get('subscribed', [CustomerController::class, 'subscribedCustomers'])->name('subscribed');
            Route::get('subscriber-export', [CustomerController::class, 'subscribed_customer_export'])->name('subscriber-export');

            Route::get('loyalty-point-report', [LoyaltyPointController::class, 'report'])->name('loyalty-point.report');
            Route::get('loyalty-point-export', [LoyaltyPointController::class, 'export'])->name('loyalty-point.export');
            Route::post('update-settings', [CustomerController::class, 'update_settings'])->name('update-settings')->withoutMiddleware(['module:customerList']);

            Route::get('export', [CustomerController::class, 'export'])->name('export');
            Route::get('order-export', [CustomerController::class, 'customer_order_export'])->name('order-export');

        });

        Route::group(['prefix' => 'pro-customer', 'as' => 'pro-customer.', 'middleware' => ['module:pro_customer']], function () {
            Route::get('benefits-setup', [ProCustomerController::class, 'benefitsSetup'])->name('benefits-setup');
            Route::post('benefits-setup', [ProCustomerController::class, 'benefitsSetupUpdate'])->name('benefits-setup.update');

            Route::get('price-setup', [ProCustomerController::class, 'priceSetup'])->name('price-setup');
            Route::group(['prefix' => 'plan', 'as' => 'plan.'], function () {
                Route::post('store', [ProCustomerController::class, 'planStore'])->name('store');
                Route::get('edit/{id}', [ProCustomerController::class, 'planEdit'])->name('edit');
                Route::post('update/{id}', [ProCustomerController::class, 'planUpdate'])->name('update');
                Route::get('status/{id}/{status}', [ProCustomerController::class, 'planStatus'])->name('status');
                Route::delete('delete/{id}', [ProCustomerController::class, 'planDestroy'])->name('delete');
            });

            Route::get('list', [ProCustomerController::class, 'customerList'])->name('list');
            Route::get('list/export/{type?}', [ProCustomerController::class, 'customerExport'])->name('list.export');
            Route::post('subscription/cancel/{id}', [ProCustomerController::class, 'subscriptionCancel'])->name('subscription.cancel');
            Route::post('subscription/renew/{id}', [ProCustomerController::class, 'subscriptionRenew'])->name('subscription.renew');
            Route::post('subscription/shift/{id}', [ProCustomerController::class, 'subscriptionShift'])->name('subscription.shift');
            Route::post('subscription/start/{userId}', [ProCustomerController::class, 'subscriptionStart'])->name('subscription.start');

            Route::get('transactions', [ProCustomerController::class, 'transactions'])->name('transactions');
            Route::get('transactions/export', [ProCustomerController::class, 'transactionExport'])->name('transactions.export');

            Route::get('additional-setup', [ProCustomerController::class, 'additionalSetup'])->name('additional-setup');
            Route::group(['prefix' => 'faq', 'as' => 'faq.'], function () {
                Route::post('store', [ProCustomerController::class, 'faqStore'])->name('store');
                Route::get('edit/{id}', [ProCustomerController::class, 'faqEdit'])->name('edit');
                Route::post('update/{id}', [ProCustomerController::class, 'faqUpdate'])->name('update');
                Route::get('status/{id}/{status}', [ProCustomerController::class, 'faqStatus'])->name('status');
                Route::delete('delete/{id}', [ProCustomerController::class, 'faqDestroy'])->name('delete');
            });

            Route::get('terms-and-conditions', [ProCustomerController::class, 'termsSetup'])->name('terms-and-conditions');
            Route::post('terms-and-conditions', [ProCustomerController::class, 'termsUpdate'])->name('terms-and-conditions.update');
        });

        Route::group(['prefix' => 'file-manager', 'as' => 'file-manager.'], function () {
            Route::get('/download/{file_name}/{storage?}', [FileManagerController::class, 'download'])->name('download');
            Route::get('/index/{folder_path?}/{storage?}', [FileManagerController::class, 'index'])->name('index');
            Route::post('/image-upload', [FileManagerController::class, 'upload'])->name('image-upload');
            Route::delete('/delete/{file_path}', [FileManagerController::class, 'destroy'])->name('destroy');
        });

        Route::group(['prefix' => 'subscription', 'as' => 'subscription.', 'middleware' => ['module:restaurant', 'module:business_settings']], function () {
            Route::get('package/list/', [SubscriptionController::class, 'index'])->name('package_list');
            Route::get('package/add', [SubscriptionController::class, 'create'])->name('create');
            Route::post('store/', [SubscriptionController::class, 'store'])->name('subscription_store');
            Route::get('package/details/{id}', [SubscriptionController::class, 'show'])->name('package_details');
            Route::get('package/{id}/edit', [SubscriptionController::class, 'edit'])->name('package_edit');
            Route::put('update/{subscriptionackage}', [SubscriptionController::class, 'update'])->name('subscription_update');
            Route::get('status/{subscriptionackage}/{status}', [SubscriptionController::class, 'statusChange'])->name('package_status');
            Route::get('package/export', [SubscriptionController::class, 'packageExport'])->name('package_list_export');
            Route::get('transcation/list/{id}', [SubscriptionController::class, 'transaction'])->name('transcation_list');
            Route::get('transcation-list/export', [SubscriptionController::class, 'TransactionExport'])->name('transcation_list_export');

            Route::get('invoice/{id}', [SubscriptionController::class, 'invoice'])->name('invoice');

            Route::get('list/', [SubscriptionController::class, 'subscriberList'])->name('subscription_list');

            Route::get('settings/', [SubscriptionController::class, 'settings'])->name('settings');
            Route::post('settings/update/', [SubscriptionController::class, 'settings_update'])->name('settings_update');
            Route::get('/subscriber-detail/{id}', [SubscriptionController::class, 'subscriberDetail'])->name('subscriberDetail');
            Route::get('/subscriber-transactions/{id}', [SubscriptionController::class, 'subscriberTransactions'])->name('subscriberTransactions');
            Route::get('/subscriber-list-export', [SubscriptionController::class, 'subscriberListExport'])->name('subscriberListExport');
            Route::get('/subscriber-transaction-export', [SubscriptionController::class, 'subscriberTransactionExport'])->name('subscriberTransactionExport');

            Route::get('/subscriber-wallet-transactions/{id}', [SubscriptionController::class, 'subscriberWalletTransactions'])->name('subscriberWalletTransactions');

            Route::get('/overView/{subscriptionackage}', [SubscriptionController::class, 'overView'])->name('overView');
            Route::post('/switch-plan', [SubscriptionController::class, 'switchPlan'])->name('switchPlan');
            Route::get('/trial-status', [SubscriptionController::class, 'trialStatus'])->name('trialStatus');
            Route::post('/switch-to-commission/{id}', [SubscriptionController::class, 'switchToCommission'])->name('switchToCommission');
            Route::get('/package-view/{id}/{store_id}', [SubscriptionController::class, 'packageView'])->name('packageView');
            Route::post('/cancel-subscription/{id}', [SubscriptionController::class, 'cancelSubscription'])->name('cancelSubscription');
            Route::post('/switch-to-commission/{id}', [SubscriptionController::class, 'switchToCommission'])->name('switchToCommission');
            Route::get('/package-view/{id}/{store_id}', [SubscriptionController::class, 'packageView'])->name('packageView');
            Route::post('/package-buy', [SubscriptionController::class, 'packageBuy'])->name('packageBuy');

        });

        // social media login
        Route::group(['prefix' => 'social-login', 'as' => 'social-login.', 'middleware' => ['module:system_settings']], function () {
            Route::get('view', [BusinessSettingsController::class, 'viewSocialLogin'])->name('view');
            Route::post('update/{service}', [BusinessSettingsController::class, 'updateSocialLogin'])->name('update');
        });
        Route::group(['prefix' => 'apple-login', 'as' => 'apple-login.'], function () {
            Route::post('update/{service}', [BusinessSettingsController::class, 'updateAppleLogin'])->name('update');
        });

        Route::group(['prefix' => 'contact', 'as' => 'contact.', 'middleware' => ['module:contact_message']], function () {
            Route::get('list', [ContactMessages::class, 'list'])->name('list');
            Route::delete('delete', [ContactMessages::class, 'destroy'])->name('delete');
            Route::get('view/{id}', [ContactMessages::class, 'view'])->name('view');
            Route::post('update/{id}', [ContactMessages::class, 'update'])->name('update');
            Route::post('send-mail/{id}', [ContactMessages::class, 'send_mail'])->name('send-mail');
        });
        Route::group(['prefix' => 'vehicle', 'as' => 'vehicle.', 'middleware' => ['module:deliveryman']], function () {
            Route::get('list', [VehicleController::class, 'list'])->name('list');
            Route::get('add', [VehicleController::class, 'create'])->name('create');
            Route::get('status/{vehicle}/{status}', [VehicleController::class, 'status'])->name('status');
            Route::get('edit/{vehicle}', [VehicleController::class, 'edit'])->name('edit');
            Route::post('store', [VehicleController::class, 'store'])->name('store');
            Route::post('update/{vehicle}', [VehicleController::class, 'update'])->name('update');
            Route::delete('delete', [VehicleController::class, 'destroy'])->name('delete');
            Route::get('view/{vehicle}', [VehicleController::class, 'view'])->name('view');
            Route::get('export', [VehicleController::class, 'export'])->name('export');

        });
        Route::group(['middleware' => ['module:order']], function () {
            Route::get('order-cancel-reasons/status/{id}/{status}', [OrderCancelReasonController::class, 'status'])->name('order-cancel-reasons.status');
            Route::get('order-cancel-reasons/setDefault/{id}/{is_default}', [OrderCancelReasonController::class, 'setDefault'])->name('order-cancel-reasons.setDefault');
            Route::get('order-cancel-reasons', [OrderCancelReasonController::class, 'index'])->name('order-cancel-reasons.index');
            Route::post('order-cancel-reasons/store', [OrderCancelReasonController::class, 'store'])->name('order-cancel-reasons.store');
            Route::put('order-cancel-reasons/update', [OrderCancelReasonController::class, 'update'])->name('order-cancel-reasons.update');
            Route::delete('order-cancel-reasons/{destroy}', [OrderCancelReasonController::class, 'destroy'])->name('order-cancel-reasons.destroy');

            Route::group(['prefix' => 'order', 'as' => 'order.subscription.'], function () {
                Route::get('subscription/update-status/{supscription_id}/{status}', [OrderSubscriptionController::class, 'view'])->name('update-status');
                Route::get('subscription', [OrderSubscriptionController::class, 'index'])->name('index');
                Route::get('subscription/show/{subscription}', [OrderSubscriptionController::class, 'show'])->name('show');
                Route::get('subscription/edit/{subscription}', [OrderSubscriptionController::class, 'edit'])->name('edit');
                Route::put('subscription/update/{subscription}', [OrderSubscriptionController::class, 'update'])->name('update');
                Route::delete('subscription/pause_log_delete/{subscription}', [OrderSubscriptionController::class, 'pause_log_delete'])->name('pause_log_delete');
            });
        });
        Route::group(['prefix' => 'shift', 'as' => 'shift.'], function () {
            Route::get('/', [ShiftController::class, 'list'])->name('list');
            Route::post('store', [ShiftController::class, 'store'])->name('store');
            Route::get('edit/{id}', [ShiftController::class, 'edit'])->name('edit');
            Route::put('update', [ShiftController::class, 'update'])->name('update');
            Route::delete('delete/{shift}', [ShiftController::class, 'destroy'])->name('delete');
            Route::get('status/{id}/{status}', [ShiftController::class, 'status'])->name('status');
        });

        Route::group(['prefix' => 'business-settings', 'as' => 'language.', 'middleware' => ['module:settings']], function () {
            Route::get('language', [LanguageController::class, 'index'])->name('index');
            Route::post('language/add-new', [LanguageController::class, 'store'])->name('add-new');
            Route::get('language/update-status', [LanguageController::class, 'update_status'])->name('update-status');
            Route::get('language/update-default-status', [LanguageController::class, 'update_default_status'])->name('update-default-status');
            Route::post('language/update', [LanguageController::class, 'update'])->name('update');
            Route::get('language/translate/{lang}', [LanguageController::class, 'translate'])->name('translate');
            Route::post('ajax-language/translate-submit/{lang}', [LanguageController::class, 'translate_submit'])->name('translate-submit');
            Route::post('language/remove-key/{lang}', [LanguageController::class, 'translate_key_remove'])->name('remove-key');
            Route::get('language/delete/{lang}', [LanguageController::class, 'delete'])->name('delete');
            Route::any('ajax-language/auto-translate/{lang}', [LanguageController::class, 'auto_translate'])->name('auto-translate');
            Route::get('ajax-language/auto-translate-all/{lang}', [LanguageController::class, 'auto_translate_all'])->name('auto_translate_all');
        });

        Route::group(['prefix' => 'business-settings', 'as' => 'refund.', 'middleware' => ['module:settings']], function () {
            Route::get('refund/settings', [OrderController::class, 'refund_settings'])->name('refund_settings');
            Route::get('refund_mode', [OrderController::class, 'refund_mode'])->name('refund_mode');
            Route::post('refund_reason', [OrderController::class, 'refund_reason'])->name('refund_reason');
            Route::get('refund/status/{id}/{status}', [OrderController::class, 'reason_status'])->name('reason_status');
            Route::put('refund/reason_edit/', [OrderController::class, 'reason_edit'])->name('reason_edit');
            Route::delete('refund/reason_delete/{id}', [OrderController::class, 'reason_delete'])->name('reason_delete');
            Route::put('refund/order_refund_rejection/', [OrderController::class, 'order_refund_rejection'])->name('order_refund_rejection');
        });

        Route::group(['prefix' => 'login-settings', 'as' => 'login-settings.', 'middleware' => ['module:settings']], function () {
            Route::get('login-setup', [BusinessSettingsController::class, 'login_settings'])->name('index');
            Route::post('login-setup/update', [BusinessSettingsController::class, 'login_settings_update'])->name('update');
        });

        Route::group(['prefix' => 'login-url', 'as' => 'login_url.', 'middleware' => ['module:settings']], function () {
            Route::get('login-page-setup', [BusinessSettingsController::class, 'login_url_page'])->name('login_url_page');
            Route::post('login-page-setup/update', [BusinessSettingsController::class, 'login_url_page_update'])->name('login_url_page_update');
        });

        Route::get('refund/{status}', [OrderController::class, 'list'])->name('refund.refund_attr')->middleware('module:order');
        Route::post('remove_image', [BusinessSettingsController::class, 'remove_image'])->name('remove_image');

        Route::group(['prefix' => 'business-settings/integration', 'as' => 'business-settings.integration.', 'middleware' => ['module:settings']], function () {
            Route::get('/', [ErpIntegrationController::class, 'index'])->name('index');
            Route::post('/store', [ErpIntegrationController::class, 'store'])->name('store');
            Route::post('/webhook/{token}', [ErpIntegrationController::class, 'updateWebhook'])->name('update-webhook');
            Route::post('/revoke/{token}', [ErpIntegrationController::class, 'revoke'])->name('revoke');
            Route::delete('/destroy/{token}', [ErpIntegrationController::class, 'destroy'])->name('destroy');
        });

        Route::group(['namespace' => 'System', 'prefix' => 'system-addon', 'as' => 'business-settings.system-addon.', 'middleware' => ['module:system_addon']], function () {
            Route::get('/', [SystemAddonController::class, 'index'])->name('index');
            Route::get('image', [SystemAddonController::class, 'image'])->name('image');
            Route::post('publish', [SystemAddonController::class, 'publish'])->name('publish');
            Route::post('upload', [SystemAddonController::class, 'upload'])->name('upload');
            Route::post('delete', [SystemAddonController::class, 'delete_theme'])->name('delete');
        });

        Route::group(['prefix' => 'restaurant-disbursement', 'as' => 'restaurant-disbursement.', 'middleware' => ['module:disbursement']], function () {
            Route::get('list', [RestaurantDisbursementController::class, 'list'])->name('list');
            Route::get('details/{id}', [RestaurantDisbursementController::class, 'view'])->name('view');
            Route::get('status', [RestaurantDisbursementController::class, 'status'])->name('status');
            Route::get('change-status/{id}/{status}', [RestaurantDisbursementController::class, 'statusById'])->name('change-status');
            Route::get('export/{id}/{type?}', [RestaurantDisbursementController::class, 'export'])->name('export');
        });
        Route::group(['prefix' => 'dm-disbursement', 'as' => 'dm-disbursement.', 'middleware' => ['module:disbursement']], function () {
            Route::get('list', [DeliveryManDisbursementController::class, 'list'])->name('list');
            Route::get('details/{id}', [DeliveryManDisbursementController::class, 'view'])->name('view');
            Route::get('export/{id}/{type?}', [DeliveryManDisbursementController::class, 'export'])->name('export');
            Route::get('status', [DeliveryManDisbursementController::class, 'status'])->name('status');
            Route::get('change-status/{id}/{status}', [DeliveryManDisbursementController::class, 'statusById'])->name('change-status');
            Route::get('export/{id}/{type?}', [DeliveryManDisbursementController::class, 'export'])->name('export');
        });
    }); // Admin auth middleware
    Route::get('zone/get-coordinates/{id}', [ZoneController::class, 'get_coordinates'])->name('zone.get-coordinates');
    Route::get('zone/get-zone', [ZoneController::class, 'get_zone'])->name('zone.get-zone');
    Route::get('zone/list', [ZoneController::class, 'zone_list'])->name('zone.list');

});
