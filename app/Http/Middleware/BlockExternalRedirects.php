<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class BlockExternalRedirects
{
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $response = $next($request);

        if (!$response->isRedirect()) {
            return $response;
        }

        $location = $response->headers->get('Location');
        if (!$location) {
            return $response;
        }

        $target = parse_url($location);
        if ($target === false) {
            Log::notice('Blocked malformed redirect target.', ['host' => $request->getHost()]);

            return response('External website navigation is disabled.', 403);
        }

        if (!isset($target['host'])) {
            return $response;
        }

        $targetHost = strtolower(rtrim($target['host'], '.'));
        $requestHost = strtolower(rtrim($request->getHost(), '.'));
        $targetScheme = strtolower($target['scheme'] ?? $request->getScheme());
        $targetPort = $target['port'] ?? ($targetScheme === 'https' ? 443 : 80);
        $requestPort = $request->getPort();

        if ($targetHost === $requestHost && $targetPort === $requestPort) {
            return $response;
        }

        Log::notice('Blocked external redirect.', [
            'host' => $requestHost,
            'target_host' => $targetHost,
        ]);

        return response('External website navigation is disabled.', 403);
    }
}
