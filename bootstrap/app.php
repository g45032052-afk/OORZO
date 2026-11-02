<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\APIGuestMiddleware;
use App\Http\Middleware\Authenticate;
// Core Laravel web middleware
use App\Http\Middleware\DmTokenIsValid;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\Erp\ErpTokenIsValid;
use App\Http\Middleware\Localization;
use App\Http\Middleware\LocalizationMiddleware;
use App\Http\Middleware\MaintenanceMode;
// Custom middleware
use App\Http\Middleware\ModulePermissionMiddleware;
use App\Http\Middleware\RateLimiterMiddleware;
use App\Http\Middleware\ReactValid;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\Subscription;
use App\Http\Middleware\VendorMiddleware;
use App\Http\Middleware\VendorTokenIsValid;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

return Application::configure(basePath: dirname(__DIR__))

    ->withRouting(
        // commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {

        $middleware->use([
            // Runs first: send the www↔apex counterpart of the panel host to the
            // canonical APP_HOST_DOMAIN so its domain-constrained routes match
            // instead of being shadowed by the Builder storefront's unconstrained
            // `/`. No-op when APP_HOST_DOMAIN is unset. Mirrors 6amMart.
            \App\Http\Middleware\RedirectToCanonicalHost::class,
            HandleCors::class,
        ]);

        $middleware->trustProxies(at: '*');

        $middleware->group('web', [
            // Required for Inertia/axios CSRF: VerifyCsrfToken decrypts the
            // X-XSRF-TOKEN header (sent from the XSRF-TOKEN cookie), which only
            // works when EncryptCookies encrypted that cookie. Without this the
            // Builder (Inertia) Publish/Save POSTs fail with 419 Page Expired.
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            LocalizationMiddleware::class,
            Localization::class,
            \App\Http\Middleware\BlockExternalRedirects::class,
            // Builder storefront uses Inertia. Inertia's middleware passes
            // non-Inertia responses (existing admin/vendor Blade pages)
            // through untouched, so it's safe on the global web group.
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->group('api', [
            SubstituteBindings::class,
        ]);

        $middleware->alias([
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,

            'admin' => AdminMiddleware::class,
            'vendor' => VendorMiddleware::class,
            'vendor.api' => VendorTokenIsValid::class,
            'dm.api' => DmTokenIsValid::class,
            'erp.api' => ErpTokenIsValid::class,
            'module' => ModulePermissionMiddleware::class,
            'localization' => LocalizationMiddleware::class,
            'subscription' => Subscription::class,
            'react' => ReactValid::class,
            'apiGuestCheck' => APIGuestMiddleware::class,
            'maintenance' => MaintenanceMode::class,
            'rateLimiter' => RateLimiterMiddleware::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function ($request, \Throwable $e) {
            return $request->is('api/*') || $request->expectsJson() || $request->wantsJson();
        });
    })

    ->create();

// $requestUri = $_SERVER['REQUEST_URI'] ?? '';
// if (!str_starts_with($requestUri, '/image-proxy')) {
//     header('Access-Control-Allow-Origin: *');
// }
// header('Access-Control-Allow-Methods: *');
// header('Access-Control-Allow-Headers: *');
