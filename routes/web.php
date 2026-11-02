<?php

use App\Models\ProCustomerTransaction;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaytmController;
use App\Http\Controllers\LiqPayController;
use App\Http\Controllers\PaymobController;
use App\Http\Controllers\PaytabsController;
use App\Http\Controllers\FirebaseController;
use App\Http\Controllers\PaystackController;
use App\Http\Controllers\RazorPayController;
use App\Http\Controllers\SenangPayController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\BkashPaymentController;
use App\Http\Controllers\DeliveryManController;
use App\Http\Controllers\FlutterwaveV3Controller;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaypalPaymentController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\SslCommerzPaymentController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\WalletPaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/subscribeToTopic', [FirebaseController::class, 'subscribeToTopic']);
 Route::get('/', [HomeController::class, 'index'])->name('home');


Route::view('subscription/payment/view' , 'Subscription_payment_view')->name('subscription_payment_view');
Route::get('maintenance-mode', [HomeController::class, 'maintenanceMode'])->name('maintenance_mode');
// ->middleware('maintenance')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

//login

Route::get('login/{tab}', [LoginController::class, 'login'])->name('login');

Route::post('login_submit', [LoginController::class, 'submit'])->name('login_post');
Route::get('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/reload-captcha', [HomeController::class, 'reloadCaptcha'])->name('reload-captcha');
Route::post('/reset-password', [LoginController::class, 'reset_password_request'])->name('reset-password')->middleware('throttle:3,60');
Route::post('/vendor-reset-password', [LoginController::class, 'vendor_reset_password_request'])->name('vendor-reset-password')->middleware('throttle:3,60');
Route::get('/password-reset', [LoginController::class, 'reset_password'])->name('change-password');
Route::post('verify-otp', [LoginController::class, 'verify_token'])->name('verify-otp');
Route::post('reset-password-submit', [LoginController::class, 'reset_password_submit'])->name('reset-password-submit');
Route::get('otp-resent', [LoginController::class, 'otp_resent'])->name('otp_resent');



Route::get('lang/{locale}', [HomeController::class, 'lang'])->name('lang');
Route::get('terms-and-conditions', [HomeController::class, 'terms_and_conditions'])->name('terms-and-conditions');
Route::get('about-us', [HomeController::class, 'about_us'])->name('about-us');
Route::match(['get', 'post'],'contact-us', [HomeController::class, 'contact_us'])->name('contact-us');
Route::get('privacy-policy', [HomeController::class, 'privacy_policy'])->name('privacy-policy');
Route::post('newsletter/subscribe', [NewsletterController::class, 'newsLetterSubscribe'])->name('newsletter.subscribe');

Route::get('refund-policy', [HomeController::class, 'refund_policy'])->name('refund-policy');
Route::get('shipping-policy', [HomeController::class, 'shipping_policy'])->name('shipping-policy');
Route::get('cancellation-policy', [HomeController::class, 'cancellation_policy'])->name('cancellation-policy');



Route::get('subscription-invoice/{id}', [HomeController::class, 'subscription_invoice'])->name('subscription_invoice');



Route::get('authentication-failed', function () {
    $errors = [];
    array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthenticated.']);
    return response()->json([
        'errors' => $errors,
    ], 401);
})->name('authentication-failed');

Route::group(['prefix' => 'payment-mobile'], function () {
    Route::get('/', [PaymentController::class, 'payment'])->name('payment-mobile');
    // Same webview, different hooks: an order that already exists and is in flight, so
    // success moves only the payment and failure leaves the order untouched.
    Route::get('cod-to-digital', [PaymentController::class, 'codPayment'])->name('cod-to-digital-payment');
    Route::get('set-payment-method/{name}', [PaymentController::class, 'set_payment_method'])->name('set-payment-method');
});

Route::get('payment-success', [PaymentController::class, 'success'])->name('payment-success');
Route::get('payment-fail', [PaymentController::class, 'fail'])->name('payment-fail');
Route::get('payment-cancel', [PaymentController::class, 'cancel'])->name('payment-cancel');
Route::get('wallet-payment', [WalletPaymentController::class, 'make_payment'])->name('wallet.payment');

$is_published = 0;
try {
$full_data = file_exists(base_path('Modules/Gateways/Addon/info.php')) ? include(base_path('Modules/Gateways/Addon/info.php')) : ['is_published' => 0];
$is_published = $full_data['is_published'] == 1 ? 1 : 0;
} catch (\Exception $exception) {
            info([$exception->getFile(), $exception->getLine(), $exception->getMessage()]);
}

if (!$is_published) {
    Route::group(['prefix' => 'payment'], function () {

        //SSLCOMMERZ
        Route::group(['prefix' => 'sslcommerz', 'as' => 'sslcommerz.'], function () {
            Route::get('pay', [SslCommerzPaymentController::class, 'index'])->name('pay');
            Route::post('success', [SslCommerzPaymentController::class, 'success'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::post('failed', [SslCommerzPaymentController::class, 'failed'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::post('canceled', [SslCommerzPaymentController::class, 'canceled'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //STRIPE
        Route::group(['prefix' => 'stripe', 'as' => 'stripe.'], function () {
            Route::get('pay', [StripePaymentController::class, 'index'])->name('pay');
            Route::get('token', [StripePaymentController::class, 'payment_process_3d'])->name('token')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::get('success', [StripePaymentController::class, 'success'])->name('success')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

      //RAZOR-PAY
      Route::group(['prefix' => 'razor-pay', 'as' => 'razor-pay.'], function () {
        Route::get('pay', [RazorPayController::class, 'index']);
        Route::post('payment', [RazorPayController::class, 'payment'])->name('payment')
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        Route::post('callback', [RazorPayController::class, 'callback'])->name('callback')
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        Route::any('cancel', [RazorPayController::class, 'cancel'])->name('cancel')
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

        Route::any('create-order', [RazorPayController::class, 'createOrder'])->name('create-order')
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        Route::any('verify-payment', [RazorPayController::class, 'verifyPayment'])->name('verify-payment')
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    });

        //PAYPAL
        Route::group(['prefix' => 'paypal', 'as' => 'paypal.'], function () {
            Route::get('pay', [PaypalPaymentController::class, 'payment']);
            Route::any('success', [PaypalPaymentController::class, 'success'])->name('success')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::any('cancel', [PaypalPaymentController::class, 'cancel'])->name('cancel')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //SENANG-PAY
        Route::group(['prefix' => 'senang-pay', 'as' => 'senang-pay.'], function () {
            Route::get('pay', [SenangPayController::class, 'index']);
            Route::any('callback', [SenangPayController::class, 'return_senang_pay'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //PAYTM
        Route::group(['prefix' => 'paytm', 'as' => 'paytm.'], function () {
            Route::get('pay', [PaytmController::class, 'payment']);
            Route::any('response', [PaytmController::class, 'callback'])->name('response')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //FLUTTERWAVE
        Route::group(['prefix' => 'flutterwave-v3', 'as' => 'flutterwave-v3.'], function () {
            Route::get('pay', [FlutterwaveV3Controller::class, 'initialize'])->name('pay');
            Route::get('callback', [FlutterwaveV3Controller::class, 'callback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //PAYSTACK
        Route::group(['prefix' => 'paystack', 'as' => 'paystack.'], function () {
            Route::get('pay', [PaystackController::class, 'index'])->name('pay');
            Route::get('callback', [PaystackController::class, 'handleGatewayCallback'])->name('callback');
            Route::get('cancel', [PaystackController::class, 'cancel'])->name('cancel');
        });

        //BKASH

        Route::group(['prefix' => 'bkash', 'as' => 'bkash.'], function () {
            // Payment Routes for bKash
            Route::get('make-payment', [BkashPaymentController::class, 'make_tokenize_payment'])->name('make-payment');
            Route::any('callback', [BkashPaymentController::class, 'callback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

            // Refund Routes for bKash
            // Route::get('refund', 'BkashRefundController@index')->name('bkash-refund');
            // Route::post('refund', 'BkashRefundController@refund')->name('bkash-refund');
        });

        //Liqpay
        Route::group(['prefix' => 'liqpay', 'as' => 'liqpay.'], function () {
            Route::get('pay', [LiqPayController::class, 'payment'])->name('payment');
            Route::any('callback', [LiqPayController::class, 'callback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //MERCADOPAGO
          Route::group(['prefix' => 'mercadopago', 'as' => 'mercadopago.'], function () {
            Route::get('pay', [MercadoPagoController::class, 'index'])->name('index');
            Route::post('make-payment', [MercadoPagoController::class, 'make_payment'])->name('make_payment')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);;
            Route::any('callback', [MercadoPagoController::class, 'callback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);;
        });

        //PAYMOB
        Route::group(['prefix' => 'paymob', 'as' => 'paymob.'], function () {
            Route::any('pay', [PaymobController::class, 'credit'])->name('pay');
            Route::any('callback', [PaymobController::class, 'callback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //PAYTABS
        Route::group(['prefix' => 'paytabs', 'as' => 'paytabs.'], function () {
            Route::any('pay', [PaytabsController::class, 'payment'])->name('pay');
            Route::any('callback', [PaytabsController::class, 'callback'])->name('callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::any('response', [PaytabsController::class, 'response'])->name('response')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });
    });
}





Route::get('/test',function (){
    Artisan::call('optimize:clear');
    return view('errors.404');
});


//Restaurant Registration
Route::group(['prefix' => 'restaurant', 'as' => 'restaurant.'], function () {
    Route::get('apply', [VendorController::class, 'create'])->name('create');
    Route::post('apply', [VendorController::class, 'store'])->name('store');

    Route::get('back/{restaurant_id}', [VendorController::class, 'back'])->name('back');
    Route::post('payment', [VendorController::class, 'payment'])->name('payment');
    Route::match(['get', 'post'], 'business-plan', [VendorController::class, 'business_plan'])->name('business_plan');
    Route::get('final-step', [VendorController::class, 'final_step'])->name('final_step');
});

//Deliveryman Registration
Route::group(['prefix' => 'deliveryman', 'as' => 'deliveryman.'], function () {
    Route::get('apply', [DeliveryManController::class, 'create'])->name('create');
    Route::post('apply', [DeliveryManController::class, 'store'])->name('store');
});

Route::get('/image-proxy', function () {
    $url = request('url');
    if (!$url) {
        abort(400, 'Missing url parameter');
    }

    $response = Http::withHeaders([
        'User-Agent' => 'Laravel-Image-Proxy'
    ])->get($url);

    return response($response->body(), $response->status())
        ->header('Content-Type', $response->header('Content-Type'))
        ->header('Access-Control-Allow-Origin', '*');
});
