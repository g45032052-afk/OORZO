<?php

namespace App\Http\Middleware;

use App\CentralLogics\Helpers;
use App\Models\Vendor;
use App\Models\VendorEmployee;
use Brian2694\Toastr\Facades\Toastr;
use Closure;

class ModulePermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $module)
    {
        if (auth('admin')->check() && Helpers::module_permission_check($module)) {
            return $next($request);
        }
        else if (auth('vendor_employee')->check() || auth('vendor')->check()) {
            if(Helpers::employee_module_permission_check($module))
            {
                return $next($request);
            }
        }
        // The restaurant app authenticates by bearer token, so no guard is ever logged in -
        // vendor.api has already resolved the caller onto the request instead. The owner
        // passes; an employee is held to the same role modules the panel enforces.
        //
        // Tested for the resolved model, not merely for a truthy value: request input is
        // caller controlled, and a form field named "vendor" must never open this branch.
        else if ($request['vendor'] instanceof Vendor) {
            $employee = $request['vendor_employee'];

            if (! $employee instanceof VendorEmployee) {
                return $next($request);
            }

            if (in_array($module, (array) json_decode($employee->role?->modules ?? '[]'), true)) {
                return $next($request);
            }
        }

        // An API caller cannot follow a redirect, and a flash message would be lost on it.
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'errors' => [
                    ['code' => 'permission_denied', 'message' => translate('messages.access_denied')],
                ],
            ], 403);
        }

        Toastr::error(translate('messages.access_denied'));
        return back();
    }
}
