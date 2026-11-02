<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Default shared Inertia props.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            // Expose Laravel session flashes as pageProps.flash.* for any
            // Inertia page that wants to toast / banner them.
            'flash' => fn () => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
                'info'    => $request->session()->get('info'),
            ],
        ];
    }
}
