<?php

namespace App\Http\Middleware;

use App\Models\VendorEmployee;
use Closure;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\Vendor;

class VendorTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        Helpers::check_subscription_validity();

        $token=$request->bearerToken();
        if(strlen($token)<1)
        {
            return response()->json([
                'errors' => [
                    ['code' => 'auth-001', 'message' => 'Unauthorized.']
                ]
            ], 401);
        }
        if (!$request->hasHeader('vendorType')) {
            $errors = [];
            array_push($errors, ['code' => 'vendor_type', 'message' => translate('messages.vendor_type_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $vendor_type= $request->header('vendorType');
        if($vendor_type == 'owner'){
            $vendor = Vendor::where('auth_token', $token)->first();
            if(!isset($vendor))
            {
                return response()->json([
                    'errors' => [
                        ['code' => 'auth-001', 'message' => 'Unauthorized.']
                    ]
                ], 401);
            }
            $request['vendor']=$vendor;
        }elseif($vendor_type == 'employee'){
            $vendor = VendorEmployee::where('auth_token', $token)->first();
            if(!isset($vendor))
            {
                return response()->json([
                    'errors' => [
                        ['code' => 'auth-001', 'message' => 'Unauthorized.']
                    ]
                ], 401);
            }
            $request['vendor']=$vendor->vendor;
            $request['vendor_employee']=$vendor;
        }
        return $next($request);
    }
}
