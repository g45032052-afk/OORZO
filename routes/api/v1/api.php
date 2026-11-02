<?php

use App\Http\Controllers\Api\V1\AddonCategoryController;
use App\Http\Controllers\Api\V1\AdvertisementController;
use App\Http\Controllers\Api\V1\Auth\CustomerAuthController;
use App\Http\Controllers\Api\V1\Auth\DeliveryManLoginController;
use App\Http\Controllers\Api\V1\Auth\DMPasswordResetController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\Auth\VendorLoginController;
use App\Http\Controllers\Api\V1\Auth\VendorPasswordResetController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\BogoOfferController;
use App\Http\Controllers\Api\V1\CampaignController;
use App\Http\Controllers\Api\V1\HappyHourController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CashBackController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ConfigController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\CouponController as UserCouponController;
use App\Http\Controllers\Api\V1\CuisineController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\DeliverymanController;
use App\Http\Controllers\Api\V1\DeliverymanEarningReportController;
use App\Http\Controllers\Api\V1\DeliveryManReviewController;
use App\Http\Controllers\Api\V1\Erp\ErpDeliveryManController;
use App\Http\Controllers\Api\V1\Erp\ErpRefundController;
use App\Http\Controllers\Api\V1\Erp\ErpStoreController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\LoyaltyPointController;
use App\Http\Controllers\Api\V1\NewsletterController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\OrderSubscriptionController;
use App\Http\Controllers\Api\V1\ProCustomerController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\RestaurantCategoryController;
use App\Http\Controllers\Api\V1\RestaurantController;
use App\Http\Controllers\Api\V1\Vendor\AddOnController;
use App\Http\Controllers\Api\V1\Vendor\AdvertisementController as VendorAdvertisementController;
use App\Http\Controllers\Api\V1\Vendor\AttributeController;
use App\Http\Controllers\Api\V1\Vendor\BogoOfferController as VendorBogoOfferController;
use App\Http\Controllers\Api\V1\Vendor\BusinessSettingsController;
use App\Http\Controllers\Api\V1\Vendor\ConversationController as VendorConversationController;
use App\Http\Controllers\Api\V1\Vendor\CouponController;
use App\Http\Controllers\Api\V1\Vendor\DeliveryManController as VendorDeliveryManController;
use App\Http\Controllers\Api\V1\Vendor\FoodController;
use App\Http\Controllers\Api\V1\Vendor\HappyHourController as VendorHappyHourController;
use App\Http\Controllers\Api\V1\Vendor\POSController;
use App\Http\Controllers\Api\V1\Vendor\ReportController;
use App\Http\Controllers\Api\V1\Vendor\RestaurantCategoryController as VendorRestaurantCategoryController;
use App\Http\Controllers\Api\V1\Vendor\RestaurantEarningReportController;
use App\Http\Controllers\Api\V1\Vendor\SubscriptionController;
use App\Http\Controllers\Api\V1\Vendor\VendorCategoryController;
use App\Http\Controllers\Api\V1\Vendor\VendorController;
use App\Http\Controllers\Api\V1\Vendor\VendorOrderController;
use App\Http\Controllers\Api\V1\Vendor\WithdrawMethodController;
use App\Http\Controllers\Api\V1\WalletController;
use App\Http\Controllers\Api\V1\WishlistController;
use App\Http\Controllers\Api\V1\ZoneController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => 'Api\V1', 'as' => 'api.v1.', 'middleware' => ['localization', 'react']], function () {

    Route::get('zone/list', [ZoneController::class, 'get_zones']);
    Route::get('zone/check', [ZoneController::class, 'zonesCheck']);

    // Areas or zip codes to pick from, per the active delivery rule of the
    // restaurant's zone. Pass restaurant_id at checkout — without it the zoneId
    // header decides, which overlapping zones make unreliable.
    Route::get('delivery-charge/coverage-list', [OrderController::class, 'deliveryChargeCoverageList']);

    Route::get('advertisement/list', [AdvertisementController::class, 'get_adds']);
    Route::get('addon-category/list', [AddonCategoryController::class, 'getList']);

    Route::get('terms-and-conditions', [HomeController::class, 'terms_and_conditions']);
    Route::get('about-us', [HomeController::class, 'about_us']);
    Route::get('privacy-policy', [HomeController::class, 'privacy_policy']);
    Route::get('refund-policy', [HomeController::class, 'refund_policy']);
    Route::get('shipping-policy', [HomeController::class, 'shipping_policy']);
    Route::get('cancellation-policy', [HomeController::class, 'cancellation_policy']);

    Route::group(['prefix' => 'auth', 'namespace' => 'Auth', 'middleware' => 'rateLimiter'], function () {

        Route::post('sign-up', [CustomerAuthController::class, 'register'])->name('register');
        Route::post('login', [CustomerAuthController::class, 'login'])->name('login');
        Route::post('verify-phone', [CustomerAuthController::class, 'verify_phone_or_email']);
        Route::post('update-info', [CustomerAuthController::class, 'update_info']);

        Route::post('forgot-password', [PasswordResetController::class, 'resetPasswordRequest'])->name('forgot-password');
        Route::post('verify-token', [PasswordResetController::class, 'verifyToken']);
        Route::put('reset-password', [PasswordResetController::class, 'resetPasswordSubmit'])->name('reset-password');
        Route::put('firebase-reset-password', [PasswordResetController::class, 'firebase_auth_verify']);

        Route::post('guest/request', [CustomerAuthController::class, 'guest_request']);

        Route::post('firebase-verify-token', [CustomerAuthController::class, 'firebase_auth_verify']);

        Route::group(['prefix' => 'delivery-man', 'as' => 'delivery-man.'], function () {
            Route::post('login', [DeliveryManLoginController::class, 'login'])->name('login');
            Route::post('biometric-login', [DeliveryManLoginController::class, 'biometricLogin']);
            Route::post('store', [DeliveryManLoginController::class, 'store'])->name('store');
            Route::post('forgot-password', [DMPasswordResetController::class, 'reset_password_request'])->name('forgot-password');
            Route::post('verify-token', [DMPasswordResetController::class, 'verify_token']);
            Route::post('firebase-verify-token', [DMPasswordResetController::class, 'firebase_auth_verify']);
            Route::put('reset-password', [DMPasswordResetController::class, 'reset_password_submit'])->name('reset-password');

            Route::group(['middleware' => ['dm.api']], function () {
                Route::post('enable-biometric', [DeliveryManLoginController::class, 'enableBiometric']);
                Route::post('disable-biometric', [DeliveryManLoginController::class, 'disableBiometric']);
                Route::post('check-password', [DeliveryManLoginController::class, 'checkPassword']);

            });
        });
        Route::group(['prefix' => 'vendor', 'as' => 'vendor.'], function () {
            Route::post('login', [VendorLoginController::class, 'login'])->name('login');
            Route::post('forgot-password', [VendorPasswordResetController::class, 'reset_password_request'])->name('forgot-password');
            Route::post('verify-token', [VendorPasswordResetController::class, 'verify_token']);
            Route::put('reset-password', [VendorPasswordResetController::class, 'reset_password_submit'])->name('reset-password');
            Route::post('register', [VendorLoginController::class, 'register'])->name('register');
        });
    });

    // Store Subscription
    Route::group(['prefix' => 'vendor', 'namespace' => 'Vendor'], function () {
        Route::get('package-view', [SubscriptionController::class, 'package_view']);
        Route::post('business_plan', [SubscriptionController::class, 'business_plan']);
        Route::post('cancel-subscription', [SubscriptionController::class, 'cancelSubscription']);
        Route::get('check-product-limits', [SubscriptionController::class, 'checkProductLimits']);
    });

    // DM Start
    Route::group(['prefix' => 'delivery-man'], function () {
        Route::get('last-location', [DeliverymanController::class, 'get_last_location']);

        Route::group(['prefix' => 'reviews', 'middleware' => ['auth:api']], function () {
            Route::get('/{delivery_man_id}', [DeliveryManReviewController::class, 'get_reviews']);
            Route::get('rating/{delivery_man_id}', [DeliveryManReviewController::class, 'get_rating']);
            Route::post('/submit', [DeliveryManReviewController::class, 'submit_review']);
        });
        Route::group(['middleware' => ['dm.api']], function () {
            Route::get('profile', [DeliverymanController::class, 'get_profile']);
            Route::get('notifications', [DeliverymanController::class, 'get_notifications']);
            Route::put('update-profile', [DeliverymanController::class, 'update_profile']);
            Route::post('update-active-status', [DeliverymanController::class, 'activeStatus']);
            Route::get('current-orders', [DeliverymanController::class, 'get_current_orders']);
            Route::get('latest-orders', [DeliverymanController::class, 'get_latest_orders']);
            Route::post('record-location-data', [DeliverymanController::class, 'record_location_data']);
            Route::get('all-orders', [DeliverymanController::class, 'get_all_orders']);
            Route::get('order-delivery-history', [DeliverymanController::class, 'get_order_history']);
            Route::put('accept-order', [DeliverymanController::class, 'accept_order']);
            Route::put('update-order-status', [DeliverymanController::class, 'update_order_status']);
            Route::put('update-payment-status', [DeliverymanController::class, 'order_payment_status_update']);
            Route::get('order-details', [DeliverymanController::class, 'get_order_details']);
            Route::get('order', [DeliverymanController::class, 'get_order']);
            Route::put('update-fcm-token', [DeliverymanController::class, 'update_fcm_token']);
            Route::get('dm-shift', [DeliverymanController::class, 'dm_shift']);
            Route::get('dm-topic', [DeliverymanController::class, 'getDmTopic']);
            // Remove account
            Route::delete('remove-account', [DeliverymanController::class, 'remove_account']);
            Route::get('get-withdraw-method-list', [DeliverymanController::class, 'withdraw_method_list']);
            Route::get('get-disbursement-report', [DeliverymanController::class, 'disbursement_report']);

            // Chatting
            Route::group(['prefix' => 'message'], function () {
                Route::get('list', [ConversationController::class, 'dm_conversations']);
                Route::get('search-list', [ConversationController::class, 'dm_search_conversations']);
                Route::get('details', [ConversationController::class, 'dm_messages']);
                Route::post('send', [ConversationController::class, 'dm_messages_store']);
            });

            Route::group(['prefix' => 'withdraw-method'], function () {
                Route::get('list', [DeliverymanController::class, 'get_disbursement_withdrawal_methods']);
                Route::post('store', [DeliverymanController::class, 'disbursement_withdrawal_method_store']);
                Route::post('make-default', [DeliverymanController::class, 'disbursement_withdrawal_method_default']);
                Route::delete('delete', [DeliverymanController::class, 'disbursement_withdrawal_method_delete']);
            });

            Route::put('send-order-otp', [DeliverymanController::class, 'send_order_otp']);

            Route::post('make-collected-cash-payment', [DeliverymanController::class, 'make_payment'])->name('make_payment');
            Route::post('make-wallet-adjustment', [DeliverymanController::class, 'make_wallet_adjustment'])->name('make_wallet_adjustment');

            Route::get('wallet-payment-list', [DeliverymanController::class, 'wallet_payment_list'])->name('wallet_payment_list');
            Route::get('wallet-provided-earning-list', [DeliverymanController::class, 'wallet_provided_earning_list'])->name('wallet_provided_earning_list');
            Route::get('earning-report', [DeliverymanEarningReportController::class, 'getEarningReport'])->name('earning-report');
        });
    });
    // DM End

    // vendor start
    Route::group(['prefix' => 'vendor', 'namespace' => 'Vendor', 'middleware' => ['vendor.api']], function () {

        Route::get('notifications', [VendorController::class, 'get_notifications']);
        Route::get('profile', [VendorController::class, 'get_profile']);
        Route::post('update-active-status', [VendorController::class, 'active_status']);
        Route::get('earning-info', [VendorController::class, 'get_earning_data']);
        Route::put('update-profile', [VendorController::class, 'update_profile']);
        Route::get('current-orders', [VendorController::class, 'get_current_orders']);
        Route::get('completed-orders', [VendorController::class, 'get_completed_orders']);
        Route::get('all-orders', [VendorController::class, 'get_all_orders']);
        Route::put('update-order-status', [VendorController::class, 'update_order_status']);
        Route::get('order-details', [VendorController::class, 'get_order_details']);
        Route::get('order', [VendorController::class, 'get_order']);
        Route::put('update-fcm-token', [VendorController::class, 'update_fcm_token']);
        Route::get('get-basic-campaigns', [VendorController::class, 'get_basic_campaigns']);
        Route::put('campaign-leave', [VendorController::class, 'remove_restaurant']);
        Route::put('campaign-join', [VendorController::class, 'addrestaurant']);
        Route::get('get-withdraw-list', [VendorController::class, 'withdraw_list']);
        Route::get('get-products-list', [VendorController::class, 'get_products']);
        Route::put('update-bank-info', [VendorController::class, 'update_bank_info']);
        Route::post('request-withdraw', [VendorController::class, 'request_withdraw']);

        Route::get('get-searched-food', [VendorOrderController::class, 'getSearchedFoods']);
        Route::put('customer-address-update', [VendorOrderController::class, 'customerAddressUpdate']);
        Route::post('update-order', [VendorOrderController::class, 'updateOrder']);

        Route::put('update-announcment', [VendorController::class, 'update_announcment']);

        Route::post('make-collected-cash-payment', [VendorController::class, 'make_payment'])->name('make_payment');

        Route::post('make-wallet-adjustment', [VendorController::class, 'make_wallet_adjustment'])->name('make_wallet_adjustment');

        Route::get('wallet-payment-list', [VendorController::class, 'wallet_payment_list'])->name('wallet_payment_list');

        // Report
        Route::get('get-expense', [ReportController::class, 'expense_report']);
        Route::get('get-tax-report', [ReportController::class, 'vendorTax']);
        Route::get('earning-report', [RestaurantEarningReportController::class, 'getEarningReport']);

        Route::get('get-transaction-report', [ReportController::class, 'day_wise_report']);
        Route::get('generate-transaction-statement', [ReportController::class, 'generate_transaction_statement']);
        Route::get('get-order-report', [ReportController::class, 'order_report']);
        Route::get('get-campaign-order-report', [ReportController::class, 'campaign_order_report']);
        Route::get('get-food-wise-report', [ReportController::class, 'food_wise_report']);
        Route::get('get-disbursement-report', [ReportController::class, 'disbursement_report']);
        Route::get('subscription-transaction', [SubscriptionController::class, 'transaction']);

        Route::get('get-withdraw-method-list', [VendorController::class, 'withdraw_method_list']);

        Route::group(['prefix' => 'withdraw-method'], function () {
            Route::get('list', [WithdrawMethodController::class, 'get_disbursement_withdrawal_methods']);
            Route::post('store', [WithdrawMethodController::class, 'disbursement_withdrawal_method_store']);
            Route::post('make-default', [WithdrawMethodController::class, 'disbursement_withdrawal_method_default']);
            Route::delete('delete', [WithdrawMethodController::class, 'disbursement_withdrawal_method_delete']);
        });

        Route::get('coupon-list', [CouponController::class, 'list']);
        Route::get('coupon-view', [CouponController::class, 'view']);
        Route::post('coupon-store', [CouponController::class, 'store'])->name('store');
        Route::post('coupon-update', [CouponController::class, 'update']);
        Route::post('coupon-status', [CouponController::class, 'status'])->name('status');
        Route::post('coupon-delete', [CouponController::class, 'delete'])->name('delete');
        Route::post('coupon-search', [CouponController::class, 'search'])->name('search');
        Route::get('coupon/view-without-translate', [CouponController::class, 'view_without_translate']);

        Route::group(['prefix' => 'advertisement', 'as' => 'advertisement.'], function () {
            Route::get('/', [VendorAdvertisementController::class, 'index']);
            Route::get('details/{id}', [VendorAdvertisementController::class, 'show']);
            Route::delete('delete/{id}', [VendorAdvertisementController::class, 'destroy']);
            Route::post('store', [VendorAdvertisementController::class, 'store']);
            Route::post('update/{id}', [VendorAdvertisementController::class, 'update']);
            Route::put('/status', [VendorAdvertisementController::class, 'status'])->name('status');
            Route::post('copy-add-post', [VendorAdvertisementController::class, 'copyAddPost']);

        });

        // remove account
        Route::delete('remove-account', [VendorController::class, 'remove_account']);

        // Business setup
        Route::put('update-basic-info', [BusinessSettingsController::class, 'update_restaurant_basic_info']);
        Route::put('update-business-setup', [BusinessSettingsController::class, 'update_restaurant_setup']);
        Route::get('get-characteristic-suggestion', [BusinessSettingsController::class, 'suggestion_list']);
        Route::post('opening-closing-status', [BusinessSettingsController::class, 'openingClosingSchedules']);
       // Route::post('verified-seller/popup-seen', [BusinessSettingsController::class, 'verifiedBadgePopupSeen']);

        // Reataurant schedule
        Route::post('schedule/store', [BusinessSettingsController::class, 'add_schedule']);
        Route::delete('schedule/{restaurant_schedule}', [BusinessSettingsController::class, 'remove_schedule']);

        // Attributes
        Route::get('attributes', [AttributeController::class, 'list']);

        // Addon
        Route::group(['prefix' => 'addon'], function () {
            Route::get('/', [AddOnController::class, 'list']);
            Route::post('store', [AddOnController::class, 'store']);
            Route::put('update', [AddOnController::class, 'update']);
            Route::get('status', [AddOnController::class, 'status']);
            Route::delete('delete', [AddOnController::class, 'delete']);
        });

        Route::group(['prefix' => 'delivery-man'], function () {
            Route::post('store', [VendorDeliveryManController::class, 'store']);
            Route::get('list', [VendorDeliveryManController::class, 'list']);
            Route::get('preview', [VendorDeliveryManController::class, 'preview']);
            Route::get('status', [VendorDeliveryManController::class, 'status']);
            Route::post('update/{id}', [VendorDeliveryManController::class, 'update']);
            Route::delete('delete', [VendorDeliveryManController::class, 'delete']);
            Route::post('search', [VendorDeliveryManController::class, 'search']);

            Route::get('get-delivery-man-list', [VendorDeliveryManController::class, 'get_delivery_man_list']);
            Route::get('assign-deliveryman', [VendorDeliveryManController::class, 'assign_deliveryman']);
        });
        // Food
        Route::group(['prefix' => 'product'], function () {
            Route::post('store', [FoodController::class, 'store']);
            Route::put('update', [FoodController::class, 'update']);
            Route::delete('delete', [FoodController::class, 'delete']);
            Route::get('status', [FoodController::class, 'status']);
            Route::get('recommended', [FoodController::class, 'recommended']);
            Route::POST('search', [FoodController::class, 'search']);
            Route::get('reviews', [FoodController::class, 'reviews']);
            Route::put('reply-update', [FoodController::class, 'update_reply']);
            Route::get('details/{id}', [FoodController::class, 'get_product']);
            Route::put('update-stock', [FoodController::class, 'updateStock']);
        });

        // POS
        Route::group(['prefix' => 'pos'], function () {
            Route::get('orders', [POSController::class, 'order_list']);
            Route::post('place-order', [POSController::class, 'place_order']);
            Route::get('customers', [POSController::class, 'get_customers']);
        });

        // Chatting
        Route::group(['prefix' => 'message'], function () {
            Route::get('list', [VendorConversationController::class, 'conversations']);
            Route::get('search-list', [VendorConversationController::class, 'search_conversations']);
            Route::get('details', [VendorConversationController::class, 'messages']);
            Route::post('send', [VendorConversationController::class, 'messages_store']);
        });
        Route::put('send-order-otp', [VendorController::class, 'send_order_otp']);
        Route::put('add-dine-in-table-number/{order}', [VendorController::class, 'add_dine_in_table_number']);

        Route::group(['prefix' => 'categories'], function () {
            Route::get('/', [VendorCategoryController::class, 'get_categories']);
            Route::get('childes/{category_id}', [VendorCategoryController::class, 'get_childes']);
            Route::get('category-wise-products', [VendorCategoryController::class, 'category_wise_products']);
        });

        // BOGO - the restaurant joins an offer the admin published with its own buy/get
        // selection, answers one the admin assigned it, reworks a rejected one, or leaves.
        Route::group(['prefix' => 'bogo-offer', 'middleware' => ['module:bogo_offer']], function () {
            Route::get('list', [VendorBogoOfferController::class, 'list']);
            Route::get('details/{id}', [VendorBogoOfferController::class, 'details']);
            Route::get('foods', [VendorBogoOfferController::class, 'foods']);
            Route::post('join/{id}', [VendorBogoOfferController::class, 'join']);
            Route::post('resubmit/{id}', [VendorBogoOfferController::class, 'resubmit']);
            Route::post('respond/{id}', [VendorBogoOfferController::class, 'respond']);
            Route::delete('leave/{id}', [VendorBogoOfferController::class, 'leave']);
        });

        // Happy Hour - no food selection, so no resubmit: a rejected restaurant joins again.
        Route::group(['prefix' => 'happy-hour', 'middleware' => ['module:happy_hour']], function () {
            Route::get('list', [VendorHappyHourController::class, 'list']);
            Route::get('details/{id}', [VendorHappyHourController::class, 'details']);
            Route::post('join/{id}', [VendorHappyHourController::class, 'join']);
            Route::post('respond/{id}', [VendorHappyHourController::class, 'respond']);
            Route::delete('leave/{id}', [VendorHappyHourController::class, 'leave']);
        });

        Route::group(['prefix' => 'restaurant-categories'], function () {
            Route::get('/', [VendorRestaurantCategoryController::class, 'list']);
            Route::get('details/{id}', [VendorRestaurantCategoryController::class, 'details']);
            Route::post('store', [VendorRestaurantCategoryController::class, 'store']);
            Route::post('update', [VendorRestaurantCategoryController::class, 'update']);
            Route::delete('delete', [VendorRestaurantCategoryController::class, 'delete']);
            Route::post('status', [VendorRestaurantCategoryController::class, 'status']);
            Route::post('priority', [VendorRestaurantCategoryController::class, 'priority']);
            Route::get('items/{id}', [VendorRestaurantCategoryController::class, 'getProducts']);

            // Assign foods to a My Category
            Route::get('assignable-foods/{id}', [VendorRestaurantCategoryController::class, 'assignableFoods']);
            Route::post('assign-foods', [VendorRestaurantCategoryController::class, 'assignFoods']);
        });
    });
    // vendor end
    Route::group(['prefix' => 'config'], function () {
        Route::get('/', [ConfigController::class, 'configuration']);
        Route::get('/get-zone-id', [ConfigController::class, 'get_zone']);
        Route::get('place-api-autocomplete', [ConfigController::class, 'place_api_autocomplete']);
        Route::get('distance-api', [ConfigController::class, 'distance_api']);
        Route::get('place-api-details', [ConfigController::class, 'place_api_details']);
        Route::get('geocode-api', [ConfigController::class, 'geocode_api']);
        Route::get('get-analytic-scripts', [ConfigController::class, 'analyticScripts']);
    });

    Route::get('food/get-allergy-name-list', [ProductController::class, 'getAllergyNameList']);
    Route::get('food/get-nutrition-name-list', [ProductController::class, 'getNutritionNameList']);

    Route::get('customer/order/cancellation-reasons', [OrderController::class, 'cancellation_reason']);
    Route::get('customer/order/send-notification/{order_id}', [OrderController::class, 'order_notification'])->middleware('apiGuestCheck');

    Route::group(['prefix' => 'pro-customer'], function () {
        Route::get('plans', [ProCustomerController::class, 'plans']);
        Route::get('faqs', [ProCustomerController::class, 'faqs']);
        Route::get('terms-and-conditions', [ProCustomerController::class, 'termsAndConditions']);
    });

    Route::group(['prefix' => 'products'], function () {
        Route::get('latest', [ProductController::class, 'get_latest_products']);
        Route::get('popular', [ProductController::class, 'get_popular_products']);
        Route::get('restaurant-popular-products', [ProductController::class, 'get_restaurant_popular_products']);
        Route::get('recommended', [ProductController::class, 'get_recommended']);
        Route::get('most-reviewed', [ProductController::class, 'get_most_reviewed_products']);
        Route::get('set-menu', [ProductController::class, 'get_set_menus']);
        Route::get('search', [ProductController::class, 'get_searched_products']);
        Route::get('details/{id}', [ProductController::class, 'get_product']);
        Route::get('related-products/{food_id}', [ProductController::class, 'get_related_products']);
        Route::get('reviews/{food_id}', [ProductController::class, 'get_product_reviews']);
        Route::get('rating/{food_id}', [ProductController::class, 'get_product_rating']);
        Route::post('reviews/submit', [ProductController::class, 'submit_product_review'])->middleware('auth:api');
        Route::get('food-or-restaurant-search', [ProductController::class, 'food_or_restaurant_search']);
        Route::get('recommended/most-reviewed', [ProductController::class, 'recommended_most_reviewed']);
    });

    Route::group(['prefix' => 'restaurants'], function () {
        Route::get('get-restaurants/{filter_data}', [RestaurantController::class, 'get_restaurants']);
        Route::get('latest', [RestaurantController::class, 'get_latest_restaurants']);
        Route::get('popular', [RestaurantController::class, 'get_popular_restaurants']);
        Route::get('dine-in', [RestaurantController::class, 'get_dine_in_restaurants']);
        Route::get('details/{id}', [RestaurantController::class, 'get_details']);  // visitor logs
        Route::get('reviews', [RestaurantController::class, 'reviews']);
        Route::get('search', [RestaurantController::class, 'get_searched_restaurants']);
        Route::get('recently-viewed-restaurants', [RestaurantController::class, 'recently_viewed_restaurants']);
        Route::get('get-coupon', [RestaurantController::class, 'get_coupons']);

        Route::get('recommended', [RestaurantController::class, 'get_recommended_restaurants']);
        // Home rails: personalized picks, and fastest delivery first.
        Route::get('recommended-for-you', [RestaurantController::class, 'get_recommended_for_you']);
        Route::get('quick-delivery', [RestaurantController::class, 'get_quick_delivery_restaurants']);
        Route::get('featured', [RestaurantController::class, 'get_featured_restaurants']);
        Route::get('exclusive-deals', [RestaurantController::class, 'get_exclusive_deals']);
        Route::get('visit-again', [RestaurantController::class, 'get_visited_restaurants'])->middleware('apiGuestCheck');
        Route::get('bogo-offers', [BogoOfferController::class, 'restaurantOffers'])->middleware('apiGuestCheck');
    });

    Route::group(['prefix' => 'banners'], function () {
        Route::get('/', [BannerController::class, 'get_banners']);
    });

    // What customers in this zone are searching for right now.
    // Offers screen: the Food tab and the Restaurants tab of the same page.
    Route::group(['prefix' => 'offers'], function () {
        Route::get('items', [ProductController::class, 'getOfferItems']);
        Route::get('restaurants', [ProductController::class, 'getOfferRestaurants']);
    });

    Route::get('trending-searches', [SearchController::class, 'getTrendingSearches']);

    Route::get('categories-cuisines', [CategoryController::class, 'get_categories_and_cuisines']);

    Route::group(['prefix' => 'categories'], function () {
        Route::get('/', [CategoryController::class, 'get_categories']);
        // Ranked by what this zone actually orders, not by the admin's priority.
        Route::get('top', [CategoryController::class, 'get_top_categories']);
        Route::get('childes/{category_id}', [CategoryController::class, 'get_childes']);
        Route::get('products/{category_id}', [CategoryController::class, 'get_products']);   // visitor logs
        Route::get('products/{category_id}/all', [CategoryController::class, 'get_all_products']);
        Route::get('restaurants/{category_id}', [CategoryController::class, 'get_restaurants']);
    });

    Route::group(['prefix' => 'restaurant-categories'], function () {
        Route::get('/', [RestaurantCategoryController::class, 'get_categories']);
        Route::get('restaurant/{restaurant_id}', [RestaurantCategoryController::class, 'get_by_restaurant']);
        Route::get('foods', [RestaurantCategoryController::class, 'get_categories_with_foods']);
    });

    Route::group(['prefix' => 'cuisine'], function () {
        Route::get('/', [CuisineController::class, 'get_all_cuisines']);
        Route::get('get_restaurants/', [CuisineController::class, 'get_restaurants']);
    });

    Route::group(['prefix' => 'customer', 'middleware' => 'auth:api'], function () {
        Route::get('notifications', [NotificationController::class, 'get_notifications']);
        Route::get('info', [CustomerController::class, 'info']);
        Route::get('update-zone', [CustomerController::class, 'update_zone']);
        Route::post('update-profile', [CustomerController::class, 'update_profile']);
        Route::post('update-interest', [CustomerController::class, 'update_interest']);
        Route::put('cm-firebase-token', [CustomerController::class, 'update_cm_firebase_token']);
        Route::get('suggested-foods', [CustomerController::class, 'get_suggested_food']);
        // Remove account
        Route::delete('remove-account', [CustomerController::class, 'remove_account']);

        Route::group(['prefix' => 'loyalty-point'], function () {
            Route::post('point-transfer', [LoyaltyPointController::class, 'point_transfer']);
            Route::get('transactions', [LoyaltyPointController::class, 'transactions']);
        });

        Route::group(['prefix' => 'wallet'], function () {
            Route::get('transactions', [WalletController::class, 'transactions']);
            Route::get('bonuses', [WalletController::class, 'get_bonus']);
            Route::post('add-fund', [WalletController::class, 'add_fund']);
        });

        Route::group(['prefix' => 'address'], function () {
            Route::get('list', [CustomerController::class, 'address_list']);
            Route::post('add', [CustomerController::class, 'add_new_address']);
            Route::put('update/{id}', [CustomerController::class, 'update_address']);
            Route::delete('delete', [CustomerController::class, 'delete_address']);
            Route::post('set-default', [CustomerController::class, 'set_default_address']);
        });

        // Chatting
        Route::group(['prefix' => 'message'], function () {
            Route::get('list', [ConversationController::class, 'conversations']);
            Route::get('search-list', [ConversationController::class, 'get_searched_conversations']);
            Route::get('details', [ConversationController::class, 'messages']);
            Route::post('send', [ConversationController::class, 'messages_store']);
            Route::post('chat-image', [ConversationController::class, 'chat_image']);
        });

        Route::group(['prefix' => 'wish-list'], function () {
            Route::get('/', [WishlistController::class, 'wish_list']);
            Route::post('add', [WishlistController::class, 'add_to_wishlist']);
            Route::delete('remove', [WishlistController::class, 'remove_from_wishlist']);
            Route::delete('clear-all', [WishlistController::class, 'clear_all']);
        });

        Route::prefix('subscription')->group(function () {
            Route::get('/', [OrderSubscriptionController::class, 'index'])->name('subscription.index');
            Route::get('edit/{id}', [OrderSubscriptionController::class, 'edit'])->name('subscription.edit');
            Route::put('update/{id}', [OrderSubscriptionController::class, 'update'])->name('subscription.update');
            Route::put('update_schedule/{subscription}', [OrderSubscriptionController::class, 'update_schedule']);
            // RESTful alias of `update/{id}`. Declared before the catch-all so a
            // PUT here reaches update() instead of silently falling through to
            // the read-only show().
            Route::put('{id}', [OrderSubscriptionController::class, 'update']);
            Route::match(['get', 'post'], '{id}/{tab?}', [OrderSubscriptionController::class, 'show']);
        });

        Route::prefix('pro-customer')->group(function () {
            Route::post('subscribe', [ProCustomerController::class, 'subscribe']);
            Route::post('cancel', [ProCustomerController::class, 'cancel']);
            Route::get('active-offer', [ProCustomerController::class, 'activeOffer']);
        });

    });

    Route::group(['prefix' => 'customer', 'as' => 'customer.', 'middleware' => 'apiGuestCheck'], function () {
        Route::group(['prefix' => 'order'], function () {
            // Route::get('list', [OrderController::class, 'get_order_list']);
            // My Orders: running, repeat and history from one endpoint, told
            // apart by `status` and `type`.
            Route::get('list', [OrderController::class, 'orderList']);
            // Swiping an order away hides it from the customer's list alone;
            // the order itself is kept for everyone else who needs it.
            Route::delete('delete', [OrderController::class, 'deleteOrder']);
            Route::get('get-latest-list', [OrderController::class, 'get_order_latest_list']);
            Route::get('order-subscription-list', [OrderController::class, 'get_order_subscription_list']);
            Route::get('running-orders', [OrderController::class, 'get_running_orders']);
            Route::get('details', [OrderController::class, 'get_order_details']);
            Route::post('place', [OrderController::class, 'place_order'])->name('order.place')->middleware('rateLimiter');
            // Everything the checkout page shows, in one call. get-Tax,
            // get-surge-price and cashback/getCashback remain for shipped clients.
            Route::post('checkout-summary', [OrderController::class, 'checkoutSummary']);
            Route::post('get-Tax', [OrderController::class, 'getTaxFromCart']);
            Route::post('get-surge-price', [OrderController::class, 'getSurgePriceAmount']);
            Route::post('check-restaurant-validation', [OrderController::class, 'check_restaurant_validation']);
            Route::put('cancel', [OrderController::class, 'cancel_order']);
            Route::post('refund-request', [OrderController::class, 'refund_request']);
            Route::get('refund-reasons', [OrderController::class, 'refund_reasons']);
            Route::get('track', [OrderController::class, 'track_order']);
            Route::put('payment-method', [OrderController::class, 'update_payment_method']);
            // Settling a cash order through a gateway before it is delivered.
            Route::get('digital-payment-status', [OrderController::class, 'digital_payment_status']);
            Route::post('pay-digitally', [OrderController::class, 'pay_digitally']);
            Route::put('offline-payment', [OrderController::class, 'offline_payment']);
            Route::put('offline-payment-update', [OrderController::class, 'update_offline_payment_info']);
        });
        Route::get('getPendingReviews', [OrderController::class, 'getPendingReviews']);

        Route::post('food-list', [OrderController::class, 'food_list']);
        Route::get('order-again', [OrderController::class, 'order_again']);

        Route::group(['prefix' => 'cart'], function () {
            Route::get('get-all', [CartController::class, 'get_all_carts']);
            Route::get('list', [CartController::class, 'get_carts']);
            // What a store wide discount would come to on this cart, and how much more the
            // basket needs. Separate from list because that answers with a bare array.
            Route::get('discount-eligibility', [CartController::class, 'discount_eligibility']);
            Route::post('add', [CartController::class, 'add_to_cart']);
            Route::post('update', [CartController::class, 'update_cart']);
            Route::delete('remove-item', [CartController::class, 'remove_cart_item']);
            Route::delete('remove', [CartController::class, 'remove_cart']);
            Route::post('add-multiple', [CartController::class, 'add_to_cart_multiple']);

        });

    });

    Route::group(['prefix' => 'banners'], function () {
        Route::get('/', [BannerController::class, 'get_banners']);
    });

    Route::group(['prefix' => 'campaigns'], function () {
        Route::get('basic', [CampaignController::class, 'get_basic_campaigns']);
        Route::get('basic-campaign-details', [CampaignController::class, 'basic_campaign_details']);
        Route::get('item', [CampaignController::class, 'get_item_campaigns']);
    });

    // Offer and restaurant identifiers accept an id or a slug. apiGuestCheck so a guest can
    // browse with guest_id while a logged in customer additionally gets remaining_uses.
    Route::group(['prefix' => 'happy-hour', 'middleware' => 'apiGuestCheck'], function () {
        Route::get('stores', [HappyHourController::class, 'stores']);
        // Home screen banner: the one happy hour running in this zone, or none.
        Route::get('running', [HappyHourController::class, 'running']);
    });

    Route::group(['prefix' => 'bogo', 'middleware' => 'apiGuestCheck'], function () {
        Route::get('home', [BogoOfferController::class, 'home']);
        Route::get('offers', [BogoOfferController::class, 'offers']);
        Route::get('offers/{id}', [BogoOfferController::class, 'details']);
    });

    Route::group(['prefix' => 'coupon', 'middleware' => 'auth:api'], function () {
        Route::get('list', [UserCouponController::class, 'list']);
        Route::get('apply', [UserCouponController::class, 'apply']);
    });

    Route::group(['prefix' => 'cashback', 'middleware' => 'auth:api'], function () {
        Route::get('list', [CashBackController::class, 'list']);
        Route::get('getCashback', [CashBackController::class, 'getCashback']);
    });

    Route::get('coupon/restaurant-wise-coupon', [UserCouponController::class, 'restaurant_wise_coupon']);

    Route::post('newsletter/subscribe', [NewsletterController::class, 'index'])->name('newsletter.subscribe')->middleware('rateLimiter');
    // Route::get('landing-page', [ConfigController::class, 'landing_page']);
    Route::get('get-page-meta-data', [ConfigController::class, 'getPageMetaData']);
    Route::get('react-landing-page', [ConfigController::class, 'react_landing_page']);
    Route::get('react-registration-page', [ConfigController::class, 'react_registration_page']);

    Route::get('vehicle/extra_charge', [ConfigController::class, 'extra_charge']);
    Route::get('most-tips', [OrderController::class, 'most_tips']);
    Route::get('get-vehicles', [ConfigController::class, 'get_vehicles']);
    Route::get('get-PaymentMethods', [ConfigController::class, 'getPaymentMethods']);
    Route::get('offline_payment_method_list', [ConfigController::class, 'offline_payment_method_list']);
    Route::get('dm-shifts', [ConfigController::class, 'dm_shifts']);
});

Route::prefix('erp')->middleware('erp.api')->group(function () {
    Route::get('/ping', fn () => response()->json(['status' => 'ok', 'time' => now()->toIso8601String()]));

    Route::get('/vendors-count', [ErpStoreController::class, 'count']);
    Route::get('/vendors', [ErpStoreController::class, 'index']);
    Route::get('/vendors/{id}', [ErpStoreController::class, 'show']);

    Route::get('/delivery-men-count', [ErpDeliveryManController::class, 'count']);
    Route::get('/delivery-men', [ErpDeliveryManController::class, 'index']);
    Route::get('/delivery-men/{id}', [ErpDeliveryManController::class, 'show']);

    Route::get('/refunds-count', [ErpRefundController::class, 'count']);
    Route::get('/refunds', [ErpRefundController::class, 'index']);
    Route::get('/refunds/{id}', [ErpRefundController::class, 'show']);
});
