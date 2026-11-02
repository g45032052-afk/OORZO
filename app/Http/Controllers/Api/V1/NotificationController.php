<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function get_notifications(Request $request){

        Helpers::getZoneIds($request);

        $zone_id= json_decode($request->header('zoneId'), true);
        try {
            $notifications = Notification::active()->where('tergat', 'customer')->where(function($q)use($zone_id){
                $q->whereNull('zone_id')->orWhereIn('zone_id', $zone_id);
            })->where('updated_at', '>=', \Carbon\Carbon::today()->subDays(15))->get();
            $notifications->append('data');

            $user_notifications = UserNotification::where('user_id', $request?->user()?->id)->where('created_at', '>=', \Carbon\Carbon::today()->subDays(15))->get();
            $notifications =  $notifications->merge($user_notifications);
            return response()->json($notifications, 200);
        } catch (\Exception $e) {
            info(['Notification api issue_____',$e->getMessage()]);
            return response()->json([], 200);
        }
    }

}
