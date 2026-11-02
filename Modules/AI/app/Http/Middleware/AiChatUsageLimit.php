<?php

namespace Modules\AI\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\AI\app\Core\AiModule;
use Symfony\Component\HttpFoundation\Response;

/**
 * Caps how many chat messages one caller can send in a day.
 *
 * Distinct from the burst limiter on the same route. That one stops a caller
 * hammering the endpoint; this one bounds the bill. Every send is a paid OpenAI
 * call, and the endpoint accepts guests, so without a daily ceiling anyone who
 * knows the URL can spend the platform's credit until it runs out.
 *
 * Guests are counted by IP, NOT by guest_id. guest_id arrives in the request
 * body and nothing validates it, so a per-guest_id counter is bypassed by
 * sending a new one on every call. IP is imperfect behind NAT but it is the
 * only identifier a caller cannot simply rewrite. (It does require the app's
 * trusted proxies to be configured, or every request behind a load balancer
 * reports the balancer's address and shares one bucket.)
 */
class AiChatUsageLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        // A request with nothing to say never reaches the provider - the controller's
        // validator rejects it - so it costs nothing and must not spend the allowance.
        // Counting it meant a client looping on a malformed payload could empty a
        // customer's day without a single AI call. The per-minute limiter still applies,
        // which is the right guard for that kind of traffic.
        if (trim((string) $request->input('message')) === '') {
            return $next($request);
        }

        // Always a positive number - AiModule refuses to resolve a cap of zero
        // or less, so there is no uncapped path through here to guard against.
        [$limit, $scope] = $this->resolveLimit($request);

        $key  = 'ai_chat_used:'.$scope.':'.now()->toDateString();
        $used = (int) Cache::get($key, 0);

        if ($used >= $limit) {
            return response()->json([
                'errors' => [[
                    'code' => 'ai_chat_limit',
                    'message' => getEnvMode() === 'demo'
                        ? translate('Demo Mode Restriction: AI chat can be used :limit times a day. It resets tomorrow.', ['limit' => $limit])
                        : translate('You have reached today\'s AI chat limit of :limit messages. Please try again tomorrow.', ['limit' => $limit]),
                    'limit' => $limit,
                ]],
            ], 429);
        }

        // Counted before the call, not after: a request that fails inside the
        // agent has usually already been billed by the provider, so charging the
        // caller only for successful replies would leave the meter under the
        // real spend - which is the number this guard exists to bound.
        Cache::put($key, $used + 1, now()->endOfDay());

        $response = $next($request);

        $response->headers->add([
            'X-AiChat-Daily-Limit'     => $limit,
            'X-AiChat-Daily-Remaining' => max(0, $limit - ($used + 1)),
        ]);

        return $response;
    }

    /**
     * The ceiling for this caller, and the bucket it is counted in.
     *
     * @return array{0:int, 1:string}
     */
    private function resolveLimit(Request $request): array
    {
        // Demo mode caps everyone the same way and ignores who is signed in:
        // the demo's accounts are shared and public, so an account-scoped
        // allowance there is no allowance at all.
        if (getEnvMode() === 'demo') {
            return [AiModule::chatDemoDailyLimit(), 'demo:'.$request->ip()];
        }

        $userId = auth('api')->id();

        return $userId
            ? [AiModule::chatDailyLimitForUser(), 'user:'.$userId]
            : [AiModule::chatDailyLimitForGuest(), 'ip:'.$request->ip()];
    }
}
