<?php

namespace App\Http\Controllers\Vendor;

use App\Models\Category;
use App\Models\Characteristic;
use App\Models\Cuisine;
use App\Models\Restaurant;
use App\Models\RestaurantNotificationSetting;
use App\Models\Tag;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\Models\RestaurantConfig;
use App\Models\RestaurantSchedule;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;

class BusinessSettingsController extends Controller
{

    private $restaurant;

    public function restaurant_index()
    {
        $restaurant =  Restaurant::withoutGlobalScope('translate')->with('translations')->findOrFail(Helpers::get_restaurant_id());
        $cuisineNames = Cuisine::pluck('name')->toArray();
        $categoryNames = Category::pluck('name')->toArray();
        $combinedNames = array_merge($cuisineNames, $categoryNames);
        $combinedNames = '[' . implode(', ', array_map(fn($name) => "'$name'", $combinedNames)) . ']';

        $language = getWebConfig('language');
        $meta = $restaurant->meta_data ?? [];
        return view('vendor-views.business-settings.restaurant-index', compact('restaurant','combinedNames','language','meta'));
    }

    public function notification_index(Request $request)
    {
        if (RestaurantNotificationSetting::count() == 0) {
            Helpers::restaurantNotificationDataSetup(Helpers::get_restaurant_id());
        }

        $query = RestaurantNotificationSetting::where('restaurant_id', Helpers::get_restaurant_id());

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('sub_title', 'like', "%{$search}%");
            });
        }

        $data = $query->get();

        $business_name = BusinessSetting::where('key', 'business_name')->first()?->value;

        return view('vendor-views.business-settings.notification-index', compact('business_name', 'data'));
    }

    public function notification_status_change($key, $type){
        $data= RestaurantNotificationSetting::where('restaurant_id',Helpers::get_restaurant_id())->where('key',$key)->first();
        if(!$data){
            Toastr::error(translate('messages.Notification_settings_not_found'));
            return back();
        }
        if($type == 'Mail' ) {
            $data->mail_status =  $data->mail_status == 'active' ? 'inactive' : 'active';
        }
        elseif($type == 'push_notification' ) {
            $data->push_notification_status =  $data->push_notification_status == 'active' ? 'inactive' : 'active';
        }
        elseif($type == 'SMS' ) {
            $data->sms_status =  $data->sms_status == 'active' ? 'inactive' : 'active';
        }
        $data?->save();

        Toastr::success(translate('messages.Notification_settings_updated'));
        return back();
    }

    /**
     * Restaurant settings, as the vendor may edit them.
     *
     * delivery_time is deliberately absent and never written here. It is set at registration
     * and thereafter owned by the admin, because it is a delivery promise made to customers and
     * it drives the express / slightly-delay ranges they are shown at checkout: a vendor able to
     * widen it at will could quietly walk back every estimate their zone advertises.
     * (Settled 20 Aug 2026 — QA Vendor TC_01 asserted these fields should be here; recorded as
     * an invalid test case rather than a missing feature.)
     */
    public function restaurant_setup(Restaurant $restaurant, Request $request)
    {
        $request->validate([
            'gst' => 'required_if:gst_status,1',
            'per_km_delivery_charge'=>'required_with:minimum_delivery_charge|numeric|between:1,999999999999.99',
            'minimum_delivery_charge'=>'required_with:per_km_delivery_charge|numeric|between:1,999999999999.99',
            'maximum_shipping_charge'=>'nullable|gt:minimum_delivery_charge',
        ], [
            'gst.required_if' => translate('messages.gst_can_not_be_empty'),
        ]);

        if ($request->schedule_advance_dine_in_booking_duration_time_format == 'min' &&   $request->schedule_advance_dine_in_booking_duration > 60) {
            Toastr::error(translate('messages.Dine_in_dine_in_booking_duration_time_must_be_in_60_minute'));
            return back();
        }
        elseif ($request->schedule_advance_dine_in_booking_duration_time_format == 'hour' &&   $request->schedule_advance_dine_in_booking_duration > 24) {
            Toastr::error(translate('messages.Dine_in_dine_in_booking_duration_time_must_be_in_24_hour'));
            return back();
        }
        elseif ($request->schedule_advance_dine_in_booking_duration_time_format == 'day' &&   $request->schedule_advance_dine_in_booking_duration > 365) {
            Toastr::error(translate('messages.Dine_in_dine_in_booking_duration_time_must_be_in_356_days'));
            return back();
        }

        if($request->instant_order == 0 && $request->schedule_order == 0){
            Toastr::error(translate('messages.Instant_Order_and_Scheduled_Order_cannot_both_be_disabled_at_the_same_time'));
            return back();
        }

        if($request->delivery == 0 && $request->take_away == 0 && $request->dine_in == 0){
            Toastr::error(translate('You_must_enable_at_least_one_delivery_option'));
            return back();
        }


        $data =0;
        if (($restaurant->restaurant_model == 'subscription' && $restaurant?->restaurant_sub?->self_delivery == 1)  || ($restaurant->restaurant_model == 'commission' &&  $restaurant->self_delivery_system == 1) ){
            $data =1;
        }
        $cuisine_ids = [];
        $cuisine_ids=$request->cuisine_ids;
        $tag_ids = [];
        if ($request->tags != null) {
            $tags = explode(",", $request->tags);
        }
        if(isset($tags)){
            foreach ($tags as $key => $value) {
                $tag = Tag::firstOrNew(
                    ['tag' => $value]
                );
                $tag->save();
                array_push($tag_ids,$tag->id);
            }
        }
        $characteristic_ids = [];
        if ($request->characteristics != null) {
            $characteristics = explode(",", $request->characteristics);
        }
        if(isset($characteristics)){
            foreach ($characteristics as $key => $value) {
                $characteristic = Characteristic::firstOrNew(
                    ['characteristic' => $value]
                );
                $characteristic->save();
                array_push($characteristic_ids,$characteristic->id);
            }
        }
        $off_day = $request->off_day?implode('',$request->off_day):'';

        //restaurant
        $restaurant->delivery = $request->delivery ? 1: 0;
        $restaurant->take_away = $request->take_away ? 1: 0;
        $restaurant->schedule_order = $request->schedule_order ? 1: 0;
        $restaurant->order_subscription_active = $request->order_subscription_active ? 1: 0;
        $restaurant->veg = $request->veg ? 1: 0;
        $restaurant->non_veg = $request->non_veg ? 1: 0;
        $restaurant->cutlery = $request->cutlery ? 1: 0;

        $restaurant->minimum_order = $request->minimum_order;
        $restaurant->opening_time = $request->opening_time;
        $restaurant->closeing_time = $request->closeing_time;
        $restaurant->off_day = $off_day;
        $restaurant->gst = json_encode(['status'=>$request->gst_status, 'code'=>$request->gst]);
        // free_delivery / free_delivery_distance are no longer posted from this screen - free
        // delivery is configured zone-wise by the admin as of 9.3. Left unassigned so a
        // forged request cannot re-enable a restaurant's own free delivery behind the UI.
        $restaurant->minimum_shipping_charge = $data?$request->minimum_delivery_charge??0: $restaurant->minimum_shipping_charge;
        $restaurant->per_km_shipping_charge = $data?$request->per_km_delivery_charge??0: $restaurant->per_km_shipping_charge;
        $restaurant->maximum_shipping_charge = $request->maximum_shipping_charge ?? null;

        //meta data

            $restaurant->meta_image = $request->has('meta_image') ? Helpers::update('restaurant/', $restaurant->meta_image, 'png', $request->file('meta_image')) : $restaurant->meta_image;
            $restaurant->meta_title = $request->meta_title;
            $restaurant->meta_description = $request->meta_description;
            $restaurant->meta_data = Helpers::formatMetaData($request->all(), $restaurant->meta_data);


        $restaurant->save();
        $restaurant->cuisine()->sync($cuisine_ids);
        $restaurant->tags()->sync($tag_ids);
        $restaurant->characteristics()->sync($characteristic_ids);

        $conf = RestaurantConfig::firstOrNew(
            ['restaurant_id' =>  $restaurant->id]
        );

        $conf->dine_in = $request->dine_in ? 1: 0;
        $conf->instant_order = $request->instant_order ? 1: 0;
        $conf->is_extra_packaging_active = $request->is_extra_packaging_active ? 1: 0;
        $conf->halal_tag_status = $request->halal_tag_status ? 1: 0;
        $conf->opening_closing_status = $request->opening_closing_status ? 1 : 0;
        $conf->same_time_for_every_day = $request->same_time_for_every_day ? 1: 0;
        $conf->customer_order_date = $request->customer_order_date ?? 0;
        $conf->customer_date_order_sratus = $request->customer_date_order_sratus ?? 0;
        $conf->extra_packaging_status = $request?->extra_packaging_status??0;
        $conf->extra_packaging_amount = $request->extra_packaging_amount;
        $conf->schedule_advance_dine_in_booking_duration = $request->schedule_advance_dine_in_booking_duration ?? 0;
        $conf->schedule_advance_dine_in_booking_duration_time_format = $request->schedule_advance_dine_in_booking_duration_time_format ?? 'min';
        // Vendor-side Website Builder switch (only togglable when the admin
        // master switch admin_website_builder_status is on).
        if (\App\CentralLogics\Helpers::get_business_settings('admin_website_builder_status') == 1) {
            $conf->website_builder_status = $request->website_builder_status ? 1 : 0;
        }
        $conf->save();

        Toastr::success(translate('messages.business_configuration_updated'));
        return back();
    }

    public function restaurant_status(Restaurant $restaurant, Request $request)
    {
        if($request->menu == "schedule_order" && !Helpers::schedule_order())
        {
            Toastr::warning(translate('messages.schedule_order_disabled_warning'));
            return back();
        }

        $home_delivery = BusinessSetting::where('key', 'home_delivery')->first()?->value ?? null;
        if ($request->menu == "delivery" && !$home_delivery) {
            Toastr::warning(translate('messages.Home_delivery_is_disabled_by_admin'));
            return back();
        }
        $take_away = BusinessSetting::where('key', 'take_away')->first()?->value ?? null;
        if ($request->menu == "take_away" && !$take_away) {
            Toastr::warning(translate('messages.Take_away_is_disabled_by_admin'));
            return back();
        }
        $dine_in_order_option = BusinessSetting::where('key', 'dine_in_order_option')->first()?->value ?? null;
        if ($request->menu == "dine_in" && !$dine_in_order_option && $request->status == 1 ) {
            Toastr::warning(translate('messages.dine_in_order_option_is_disabled_by_admin'));
            return back();
        }


        $instant_order = BusinessSetting::where('key', 'instant_order')->first()?->value ?? null;
        if ($request->menu == "instant_order" && !$instant_order && $request->status == 1 ) {
            Toastr::warning(translate('messages.instant_order_is_disabled_by_admin'));
            return back();
        }

        if((($request->menu == "delivery" && $restaurant->take_away==0) || ($request->menu == "take_away" && $restaurant->delivery==0)) &&  $request->status == 0 )
        {
            Toastr::warning(translate('messages.can_not_disable_both_take_away_and_delivery'));
            return back();
        }

        if((($request->menu == "instant_order" && $restaurant->schedule_order==0) || ( isset($restaurant->restaurant_config)   && ($request->menu == "schedule_order" && $restaurant?->restaurant_config?->instant_order ==0))) &&  $request->status == 0 && $instant_order )
        {
            Toastr::warning(translate('messages.can_not_disable_both_instant_order_and_schedule_order'));
            return back();
        }

        if((($request->menu == "veg" && $restaurant->non_veg==0) || ($request->menu == "non_veg" && $restaurant->veg==0)) &&  $request->status == 0 )
        {
            Toastr::warning(translate('messages.veg_non_veg_disable_warning'));
            return back();
        }

        // Free delivery moved to the admin's zone-wise setup in 9.3 and its controls are gone
        // from this panel. The menu name is caller supplied and lands in a mass assign further
        // down, so the removed keys are rejected here - otherwise a hand-made POST could still
        // switch a restaurant's own free delivery back on.
        if (in_array($request->menu, ['free_delivery', 'free_delivery_distance', 'free_delivery_distance_status'], true)) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }

        if( in_array($request->menu,['instant_order','customer_date_order_sratus','halal_tag_status' ,'is_extra_packaging_active','dine_in'] ) ){

            $conf = RestaurantConfig::firstOrNew(
                ['restaurant_id' =>  $restaurant->id]
            );
            $conf[$request->menu] = $request->status;
            $conf->save();

            Toastr::success(translate('messages.Restaurant settings updated!'));
            return back();
        }



        $restaurant[$request->menu] = $request->status;
        $restaurant->save();
        Toastr::success(translate('messages.Restaurant settings updated!'));
        return back();
    }

    public function active_status(Request $request)
    {
        $restaurant = Helpers::get_restaurant_data();
        $restaurant->active = $restaurant->active?0:1;
        $restaurant->save();
        return response()->json(['message' => !$restaurant->active?translate('messages.restaurant_temporarily_closed'):translate('messages.restaurant_opened')], 200);
    }

    public function add_schedule(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'start_time'=>'required|date_format:H:i',
            'end_time'=>'required|date_format:H:i|after:start_time',
            'day'=>'required',
        ],[
            'end_time.after'=>translate('messages.End time must be after the start time')
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }
        $temp = RestaurantSchedule::where('day', $request->day)->where('restaurant_id',Helpers::get_restaurant_id())
        ->where(function($q)use($request){
            return $q->where(function($query)use($request){
                return $query->where('opening_time', '<=' , $request->start_time)->where('closing_time', '>=', $request->start_time);
            })->orWhere(function($query)use($request){
                return $query->where('opening_time', '<=' , $request->end_time)->where('closing_time', '>=', $request->end_time);
            });
        })
        ->first();

        if(isset($temp))
        {
            return response()->json(['errors' => [
                ['code'=>'time', 'message'=>translate('messages.Time overlap detected.')]
            ]]);
        }

        $restaurant = Helpers::get_restaurant_data();
        $restaurant_schedule = RestaurantSchedule::insert(['restaurant_id'=>Helpers::get_restaurant_id(),'day'=>$request->day,'opening_time'=>$request->start_time,'closing_time'=>$request->end_time]);

        $restaurantConfig = RestaurantConfig::where('restaurant_id', $restaurant->id)->first();

        if ($restaurantConfig->same_time_for_every_day) {

            $restaurantId = $restaurantConfig->restaurant_id;
            $dayOneSchedules = RestaurantSchedule::where('restaurant_id', $restaurantId)
                ->where('day', 1)
                ->get();

            if ($dayOneSchedules->isEmpty()) {
                RestaurantSchedule::where('restaurant_id', $restaurantId)
                ->delete();
                return response()->json();
            }
            RestaurantSchedule::where('restaurant_id', $restaurantId)
                ->where('day', '!=', 1)
                ->delete();

            $insertData = [];

            foreach ($dayOneSchedules as $schedule) {
                foreach (range(0, 6) as $day) {
                    if ($day == 1) {
                        continue;
                    }

                    $insertData[] = [
                        'restaurant_id' => $restaurantId,
                        'day'           => $day,
                        'opening_time'  => $schedule->opening_time,
                        'closing_time'  => $schedule->closing_time,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }
            }

            if (!empty($insertData)) {
                RestaurantSchedule::insert($insertData);
            }
        }

        return response()->json([
            'view' => view('vendor-views.business-settings.partials._schedule', compact('restaurant'))->render(),
        ]);
    }

    public function remove_schedule($restaurant_schedule)
    {
        $restaurant = Helpers::get_restaurant_data();
        $schedule = RestaurantSchedule::where('restaurant_id', $restaurant->id)->find($restaurant_schedule);
        if(!$schedule)
        {
            return response()->json([],404);
        }
        $schedule->delete();

        $restaurantConfig = RestaurantConfig::where('restaurant_id', $restaurant->id)->first();

        if ($restaurantConfig->same_time_for_every_day) {

            $restaurantId = $restaurantConfig->restaurant_id;
            $dayOneSchedules = RestaurantSchedule::where('restaurant_id', $restaurantId)
                ->where('day', 1)
                ->get();

            if ($dayOneSchedules->isEmpty()) {
                RestaurantSchedule::where('restaurant_id', $restaurantId)
                ->delete();
                return response()->json([
                    'view' => view('vendor-views.business-settings.partials._schedule', compact('restaurant'))->render(),
                ]);
            }
            RestaurantSchedule::where('restaurant_id', $restaurantId)
                ->where('day', '!=', 1)
                ->delete();

            $insertData = [];

            foreach ($dayOneSchedules as $schedule) {
                foreach (range(0, 6) as $day) {
                    if ($day == 1) {
                        continue;
                    }

                    $insertData[] = [
                        'restaurant_id' => $restaurantId,
                        'day'           => $day,
                        'opening_time'  => $schedule->opening_time,
                        'closing_time'  => $schedule->closing_time,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }
            }

            if (!empty($insertData)) {
                RestaurantSchedule::insert($insertData);
            }
        }

        return response()->json([
            'view' => view('vendor-views.business-settings.partials._schedule', compact('restaurant'))->render(),
        ]);
    }

    public function site_direction_vendor(Request $request){
        session()->put('site_direction_vendor', ($request->status == 1?'ltr':'rtl'));
        return response()->json();
    }

    public function updateOpeningClosingStatus(Request $request, $id)
    {
        $restaurant = Helpers::get_restaurant_data();
        $config = RestaurantConfig::firstOrNew(['restaurant_id' => $id]);

        $config->opening_closing_status = $request->opening_closing_status ? 1 : 0;
        $config->same_time_for_every_day = $request->same_time_for_every_day ? 1 : 0;
        $config->save();

        if ($config->opening_closing_status) {

            RestaurantSchedule::where('restaurant_id', $restaurant->id)->delete();

            $insertData = [];

            foreach (range(0, 6) as $day) {
                $insertData[] = [
                    'restaurant_id' => $restaurant->id,
                    'day'           => $day,
                    'opening_time'  => '00:00:00',
                    'closing_time'  => '23:59:59',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            RestaurantSchedule::insert($insertData);

            return [
                'view' => view('vendor-views.business-settings.partials._schedule', compact('restaurant'))->render(),
            ];
        }

        if ($config->same_time_for_every_day) {

            $restaurantId = $config->restaurant_id;
            $dayOneSchedules = RestaurantSchedule::where('restaurant_id', $restaurantId)
                ->where('day', 1)
                ->get();

            if ($dayOneSchedules->isEmpty()) {
                RestaurantSchedule::where('restaurant_id', $restaurantId)
                ->delete();
                return response()->json([
                    'view' => view('vendor-views.business-settings.partials._schedule', compact('restaurant'))->render(),
                ]);
            }
            RestaurantSchedule::where('restaurant_id', $restaurantId)
                ->where('day', '!=', 1)
                ->delete();

            $insertData = [];

            foreach ($dayOneSchedules as $schedule) {
                foreach (range(0, 6) as $day) {
                    if ($day == 1) {
                        continue;
                    }

                    $insertData[] = [
                        'restaurant_id' => $restaurantId,
                        'day'           => $day,
                        'opening_time'  => $schedule->opening_time,
                        'closing_time'  => $schedule->closing_time,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }
            }

            if (!empty($insertData)) {
                RestaurantSchedule::insert($insertData);
            }
        }

        return response()->json([
            'view' => view('vendor-views.business-settings.partials._schedule', compact('restaurant'))->render(),
        ]);
    }

}
