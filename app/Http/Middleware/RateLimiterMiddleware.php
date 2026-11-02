<?php

namespace App\Http\Middleware;

use Brian2694\Toastr\Facades\Toastr;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimiterMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        [$limit, $delaySeconds] = $this->resolveLimit($request);

        if ($limit === 0) {
            return $next($request);
        }

        $key = 'limiter:'.($request->user()?->id ?: $request->ip()).':'.$request->route()?->getName();

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            $seconds = RateLimiter::availableIn($key);
            if ($request->is('api/v1/*') || $request->expectsJson()) {
                return response()->json([
                    'errors' => [
                        ['code' => 'too_many_requests', 'message' => translate('Too many requests. Please slow down.'),'retry_after'=>$seconds],
                    ],
                ], $request->ajax() ? 200 : 429);
            }
            Toastr::error(translate('Too many requests. Please slow down.'));

            return redirect()->back();

        }

        RateLimiter::hit($key, $delaySeconds);

        $response = $next($request);

        $response->headers->add([
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => RateLimiter::remaining($key, $limit),
        ]);

        return $response;

    }

    protected function resolveLimit(Request $request): array
    {
        $routeName = $request->route()?->getName();

        return match ($routeName) {

            'api.v1.vendor.login' => [6, 60], // [limit, delaySeconds]
            'api.v1.vendor.register' => [6, 60],
            'api.v1.vendor.forgot-password' => [6, 60],
            'api.v1.vendor.reset-password' => [6, 60],

            'api.v1.delivery-man.login' => [6, 60],
            'api.v1.delivery-man.store' => [6, 60],
            'api.v1.delivery-man.forgot-password' => [6, 60],
            'api.v1.delivery-man.reset-password' => [6, 60],

            'api.v1.login' => [6, 60],
            'api.v1.register' => [6, 60],
            'api.v1.forgot-password' => [6, 60],
            'api.v1.reset-password' => [6, 60],

            'api.v1.customer.order.place' => [6, 60],
            'api.v1.newsletter.subscribe' => [6, 60],

            // Every send is a paid provider call and the endpoint accepts
            // guests, so it gets a tighter burst allowance than the default.
            // The reads beside it are ordinary database queries.
            'api.v1.customer.ai-chat.send' => [10, 60],
            'api.v1.customer.ai-chat.conversations' => [30, 60],
            'api.v1.customer.ai-chat.messages' => [30, 60],
            'api.v1.customer.ai-chat.conversations.delete' => [20, 60],

            default => [6, 60],
        };
    }
}
