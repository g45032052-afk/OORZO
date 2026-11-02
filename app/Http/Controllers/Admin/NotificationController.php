<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cart;
use App\Models\User;
use App\Models\DataSetting;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\Notification;
use App\Models\UserNotification;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\NotificationMessage;
use App\Exports\PushNotificationExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\info;

class NotificationController extends Controller
{
    function index(Request $request)
    {
        $key = explode(' ', $request['search']);
        $notifications = Notification::with('zone','storage')->latest()
        ->when(isset($key), function ($q) use ($key){
            $q->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('title', 'like', "%{$value}%");
                }
            });
        })
        ->paginate(config('default_pagination'));
        return view('admin-views.notification.index', compact('notifications'));
    }

    public function store(Request $request)
    {
        if (getEnvMode() == 'demo') {
            return response()->json(['errors' => Helpers::error_formater('feature-disable', 'This option is disabled for demo!')]);
        }
        $validator = Validator::make($request->all(), [
            'notification_title' => 'required|max:191',
            'description' => 'required|max:1000',
            'tergat' => 'required',
            'zone'=>'required',
            'image' => 'nullable|max:2048',
        ], [
            'notification_title.required' => 'Title is required!',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        if ($request->has('image')) {
            $image_name = Helpers::upload(dir:'notification/', format:'png', image: $request->file('image'));
        } else {
            $image_name = null;
        }

        $notification = new Notification;
        $notification->title = $request->notification_title;
        $notification->description = $request->description;
        $notification->image = $image_name;
        $notification->tergat= $request->tergat;
        $notification->status = 1;
        $notification->zone_id = $request->zone=='all'?null:$request->zone;
        $notification->save();

        $topic_all_zone=[
            'customer'=>'all_zone_customer',
            'deliveryman'=>'all_zone_delivery_man',
            'restaurant'=>'all_zone_restaurant',
        ];

        $topic_zone_wise=[
            'customer'=>'zone_'.$request->zone.'_customer',
            'deliveryman'=>'zone_'.$request->zone.'_delivery_man',
            'restaurant'=>'zone_'.$request->zone.'_restaurant',
        ];
        $topic = $request->zone == 'all'?$topic_all_zone[$request->tergat]:$topic_zone_wise[$request->tergat];

        if($request->has('image'))
        {
            $notification->image = $notification->toArray()['image_full_url'];
//            $notification->image = url('/').'/storage/app/public/notification/'.$image_name;
        }

        try {
            Helpers::send_push_notif_to_topic($notification, $topic, 'general');
        } catch (\Exception $e) {
            info($e->getMessage());
            Toastr::warning(translate('messages.push_notification_faild'));
        }

        return response()->json([], 200);
    }

    public function edit($id)
    {
        $notification = Notification::findOrFail($id);
        return view('admin-views.notification.edit', compact('notification'));
    }

    public function update(Request $request, $id)
    {
        if (getEnvMode() == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));
            return back();
        }
        $request->validate([
            'notification_title' => 'required|max:191',
            'description' => 'required|max:1000',
            'tergat' => 'required',
            'image' => 'nullable|max:2048',
        ]);

        $notification = Notification::findOrFail($id);

        if ($request->has('image')) {
            $image_name = Helpers::update(dir:'notification/', old_image: $notification->image, format: 'png', image:$request->file('image'));
        } else {
            $image_name = $notification['image'];
        }

        $notification->title = $request->notification_title;
        $notification->description = $request->description;
        $notification->image = $image_name;
        $notification->tergat= $request->tergat;
        $notification->zone_id = $request->zone=='all'?null:$request->zone;
        $notification->updated_at = now();
        $notification->save();

        $topic_all_zone=[
            'customer'=>'all_zone_customer',
            'deliveryman'=>'all_zone_delivery_man',
            'restaurant'=>'all_zone_restaurant',
        ];

        $topic_zone_wise=[
            'customer'=>'zone_'.$request->zone.'_customer',
            'deliveryman'=>'zone_'.$request->zone.'_delivery_man',
            'restaurant'=>'zone_'.$request->zone.'_restaurant',
        ];
        $topic = $request->zone == 'all'?$topic_all_zone[$request->tergat]:$topic_zone_wise[$request->tergat];
            if($image_name){
                $notification->image = $notification->toArray()['image_full_url'];
//                $notification->image = url('/').'/storage/app/public/notification/'.$image_name;
            }

        try {
            Helpers::send_push_notif_to_topic($notification, $topic, 'general');
        } catch (\Exception $e) {
            info($e->getMessage());
            Toastr::warning(translate('messages.push_notification_faild'));
        }
        Toastr::success(translate('messages.notification_updated_successfully'));
        return back();
    }

    public function status(Request $request)
    {
        $notification = Notification::findOrFail($request->id);
        $notification->status = $request->status;
        $notification?->save();
        Toastr::success(translate('messages.notification_status_updated'));
        return back();
    }

    public function delete(Request $request)
    {
        $notification = Notification::findOrFail($request->id);
        Helpers::check_and_delete('notification/' , $notification['image']);
        $notification?->delete();
        Toastr::success(translate('messages.notification_deleted_successfully'));
        return back();
    }

    public function export(Request $request){
        try{
            $key = explode(' ', $request['search']);
            $Notification =  Notification::
                when(isset($key ), function ($q) use ($key){
                    $q->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('title', 'like', "%{$value}%");
                        }
                    });
                })->latest()
            ->latest()->get();
            $data=[
                'data' =>$Notification,
                'search' =>$request['search'] ?? null
            ];
            if($request->type == 'csv'){
                return Excel::download(new PushNotificationExport($data), 'PushNotification.csv');
            }
            return Excel::download(new PushNotificationExport($data), 'PushNotification.xlsx');
        }  catch(\Exception $e)
            {
                Toastr::error("line___{$e->getLine()}",$e->getMessage());
                info(["line___{$e->getLine()}",$e->getMessage()]);
                return back();
            }
    }

    public function sendCartAbandonNotification()
    {
        $cart_reminder_after_time = DataSetting::where(['key' => 'cart_reminder_after_time', 'type' => 'notification_settings'])->first()?->value ?? 0;
        $cart_reminder_after = DataSetting::where(['key' => 'cart_reminder_after', 'type' => 'notification_settings'])->first()?->value ?? 'min';

        if ($cart_reminder_after_time <= 0) {
            return;
        }

        $thresholdTime = now();
        if ($cart_reminder_after == 'hour') {
            $thresholdTime = $thresholdTime->subHours($cart_reminder_after_time);
        } else {
            $thresholdTime = $thresholdTime->subMinutes($cart_reminder_after_time);
        }

        $abandoned_users = DB::table('users')
            ->select(['id', 'f_name', 'l_name', 'cm_firebase_token'])
            ->whereIn('id', function ($query) use ($thresholdTime) {
                $query->select('user_id')
                    ->from('carts')
                    ->where('updated_at', '<=', $thresholdTime)
                    ->whereNotNull('user_id');
            })
            ->whereNotNull('cm_firebase_token')
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')
                    ->from('user_notifications')
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.type')) = 'cart_abandon'");
            })
            ->get();

            if ($abandoned_users->isEmpty()) {
            return;
        }

        $notificationMessage = NotificationMessage::where('key', 'cart_abandon')->first();
        if (!$notificationMessage || $notificationMessage->status != 1) {
            return;
        }

        foreach ($abandoned_users as $user) {
            if ($user && $user->cm_firebase_token && $user->cm_firebase_token != '@') {
                $data = [
                    'title' => translate('messages.Cart Abandon Reminder'),
                    'description' => str_replace('{userName}', $user->f_name . ' ' . $user->l_name, $notificationMessage->message),
                    'image' => '',
                    'order_id' => '',
                    'type' => 'cart_abandon',
                ];

                try {
                    Helpers::send_push_notif_to_device($user->cm_firebase_token, $data);
                    Helpers::insertDataOnNotificationTable($data, 'user', $user->id);
                } catch (\Exception $e) {
                    info($e->getMessage());
                }
            }
        }
    }
}
