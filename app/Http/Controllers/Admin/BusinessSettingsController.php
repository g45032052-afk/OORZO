<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\DataSetting;
use App\Models\EmailTemplate;
use App\Models\NotificationMessage;
use App\Models\NotificationSetting;
use App\Models\OrderCancelReason;
use App\Models\PriorityList;
use App\Models\Restaurant;
use App\Models\RestaurantConfig;
use App\Models\RestaurantSubscription;
use App\Models\Setting;
use App\Models\Translation;
use App\Traits\Processor;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class BusinessSettingsController extends Controller
{
    use Processor;

    public function business_index(Request $request, $tab = 'business')
    {
        if (! Helpers::module_permission_check('settings')) {
            Toastr::error(translate('messages.access_denied'));

            return back();
        }
        if ($tab == 'business') {
            return view('admin-views.business-settings.business-index');
        } elseif ($tab == 'customer') {
            $data = BusinessSetting::where('key', 'like', 'wallet_%')
                ->orWhere('key', 'like', 'loyalty_%')
                ->orWhere('key', 'like', 'ref_earning_%')
                ->orWhere('key', 'like', 'add_fund_status%')
                ->orWhere('key', 'like', 'customer_%')
                ->orWhere('key', 'like', 'guest_checkout_status')
                ->orWhere('key', 'like', 'pro_member_status')
                ->orWhere('key', 'like', 'wallet_status')
                ->orWhere('key', 'like', 'new_customer_discount_%')->get();
            $data = array_column($data->toArray(), 'value', 'key');

            return view('admin-views.business-settings.customer-index', compact('data'));
        } elseif ($tab == 'deliveryman') {
            return view('admin-views.business-settings.deliveryman-index');
        } elseif ($tab == 'order') {

            $key = ['canceled_by_restaurant', 'canceled_by_deliveryman', 'order_delivery_verification', 'admin_order_notification', 'home_delivery', 'take_away', 'instant_order', 'repeat_order_option', 'order_subscription', 'schedule_order', 'customer_date_order_sratus', 'canceled_by_restaurant', 'canceled_by_deliveryman', 'order_confirmation_model', 'schedule_order_slot_duration', 'schedule_order_slot_duration_time_formate', 'customer_order_date', 'dine_in_order_option', 'can_restaurant_edit_order', 'order_notification_type'];
            $settings = array_column(BusinessSetting::whereIn('key', $key)->get()->toArray(), 'value', 'key');
            $language = getWebConfig('language');
            $user_type = $request['user_type'] ?? '';
            $search = $request['search'] ?? '';
            $reasons = OrderCancelReason::when($user_type, function ($query) use ($user_type) {
                $query->where('user_type', $user_type);
            })
                ->when($search, function ($query) use ($search) {
                    $query->where('reason', 'like', "%{$search}%");
                })
                ->latest()
                ->paginate(config('default_pagination'));

            return view('admin-views.business-settings.order-index', compact('settings', 'reasons', 'language', 'user_type', 'search'));
        } elseif ($tab == 'restaurant') {
            return view('admin-views.business-settings.restaurant-index');
        } elseif ($tab == 'disbursement') {
            return view('admin-views.business-settings.disbursement-index');
        } elseif ($tab == 'priority') {
            return view('admin-views.business-settings.priority-index');
        } elseif ($tab == 'payment-setup') {
            $keys = [
                'cash_on_delivery',
                'digital_payment',
                'offline_payment_status',
                'partial_payment_status',
                'partial_payment_method',
                'cod_to_digital_payment',
            ];

            $settings = BusinessSetting::whereIn('key', $keys)
                ->pluck('value', 'key');

            $cash_on_delivery = isset($settings['cash_on_delivery'])
                ? json_decode($settings['cash_on_delivery'], true)['status']
                : 0;

            $digital_payment = isset($settings['digital_payment'])
                ? json_decode($settings['digital_payment'], true)['status']
                : 0;

            $offline_payment = $settings['offline_payment_status'] ?? 0;
            $partial_payment = $settings['partial_payment_status'] ?? 0;
            $partial_payment_method = $settings['partial_payment_method'] ?? 'offline_payment';
            // Lets a customer settle a cash order through a gateway before it is delivered.
            $cod_to_digital_payment = $settings['cod_to_digital_payment'] ?? 0;

            return view(
                'admin-views.business-settings.payment-setup',
                compact('offline_payment', 'partial_payment', 'cash_on_delivery', 'digital_payment', 'partial_payment_method', 'cod_to_digital_payment')
            );
        } elseif ($tab == 'food') {
            $key = ['toggle_veg_non_veg'];
            $settings = array_column(BusinessSetting::whereIn('key', $key)->get()->toArray(), 'value', 'key');

            return view('admin-views.business-settings.food-setup', compact('settings'));
        }
    }

    public function updatePaymentSetup(Request $request)
    {
        $offline = $request->offline_payment_status ? 1 : 0;
        $cod = $request->cash_on_delivery ? 1 : 0;
        $digital = $request->digital_payment ? 1 : 0;

        if ($offline == 0 && $cod == 0 && $digital == 0) {
            Toastr::error(translate('messages.at_least_one_payment_method_must_be_active'));

            return back();
        }
        // Converting a cash order to a digital one needs all three: cash orders to convert,
        // digital payment to convert them to, and a live gateway to send the customer to.
        $codToDigital = $request->cod_to_digital_payment ? 1 : 0;
        $wasCodToDigital = (int) (BusinessSetting::where('key', 'cod_to_digital_payment')->first()?->value ?? 0);

        $missing = null;

        if ($codToDigital) {
            if (! $cod) {
                $missing = translate('messages.Cash_On_Delivery_must_be_active_to_enable_COD_to_digital_payment');
            } elseif (! $digital) {
                $missing = translate('messages.Digital_Payment_must_be_active_to_enable_COD_to_digital_payment');
            } elseif (! Setting::where('settings_type', 'payment_config')->where('is_active', 1)->exists()) {
                $missing = translate('messages.At_least_one_payment_gateway_must_be_active_to_enable_COD_to_digital_payment');
            }
        }

        if ($missing) {
            // Turning the feature ON without its prerequisites is a mistake worth stopping, so
            // the admin is sent back with the box as they left it and told what is missing.
            if (! $wasCodToDigital) {
                Toastr::error($missing);

                return back();
            }

            // Turning a prerequisite OFF while the feature is already on is a different act:
            // rejecting the whole payment-setup save would strand the admin, unable to switch
            // COD off at all. The dependent feature is disabled instead and the change is
            // reported, which is what the runtime guard already enforces.
            $codToDigital = 0;
            Toastr::warning($missing);
        }

        $options = [
            'offline_payment_status' => $offline,
            'cash_on_delivery' => ['status' => $cod],
            'digital_payment' => ['status' => $digital],
            'partial_payment_status' => $request->partial_payment_status ? 1 : 0,
            'partial_payment_method' => $request->partial_payment_method,
            'cod_to_digital_payment' => $codToDigital,
        ];

        foreach ($options as $key => $value) {
            Helpers::businessUpdateOrInsert(
                ['key' => $key],
                [
                    'value' => is_array($value) ? json_encode($value) : $value,
                    'updated_at' => now(),
                ]
            );
        }

        Toastr::success(translate('messages.updated_successfully'));

        return back();
    }

    public function update_restaurant(Request $request)
    {
        $settings = [
            'cash_in_hand_overflow_restaurant' => $request['cash_in_hand_overflow_restaurant'] ?? 0,
            'cash_in_hand_overflow_restaurant_amount' => $request['cash_in_hand_overflow_restaurant_amount'],
            'min_amount_to_pay_restaurant' => $request['min_amount_to_pay_restaurant'],
            'canceled_by_restaurant' => $request['canceled_by_restaurant'],
            'restaurant_review_reply' => $request['restaurant_review_reply'],
            'extra_packaging_charge' => $request['extra_packaging_charge'] ?? 0,
            'toggle_restaurant_registration' => $request['toggle_restaurant_registration'] ?? 0,
            'verified_seller_badge' => $request['verified_seller_badge'] ?? 0,
            'restaurant_category_status' => $request['restaurant_category_status'] ?? 0,
            // Master switch for the vendor Website Builder (storefront) addon.
            'admin_website_builder_status' => $request['admin_website_builder_status'] ?? 0,
        ];

        Helpers::bulkAddOrUpdateBusinessSettings($settings);

        $reelsSettingsController = $this->getReelsBusinessSettingsController();
        if ($reelsSettingsController) {
            $reelsSettingsController->persistStoreSettings($request);
        }

        Toastr::success(translate('messages.successfully_updated_to_changes_restart_app'));

        return back();
    }

    private function getReelsBusinessSettingsController(): ?object
    {
        $controllerClass = 'Modules\\ReelsModule\\Http\\Controllers\\ReelsBusinessSettingsController';

        if (!addon_published_status('ReelsModule')) {
            return null;
        }

        if (!class_exists($controllerClass)) {
            return null;
        }

        return app($controllerClass);
    }

    public function update_dm(Request $request)
    {
        if (getEnvMode() == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));

            return back();
        }
        Helpers::businessUpdateOrInsert(['key' => 'min_amount_to_pay_dm'], [
            'value' => $request['min_amount_to_pay_dm'],
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'cash_in_hand_overflow_delivery_man'], [
            'value' => $request['cash_in_hand_overflow_delivery_man'] ?? 0,
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'dm_tips_status'], [
            'value' => $request['dm_tips_status'],
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'dm_tips_status'], [
            'value' => $request['dm_tips_status'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'dm_maximum_orders'], [
            'value' => $request['dm_maximum_orders'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'canceled_by_deliveryman'], [
            'value' => $request['canceled_by_deliveryman'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'show_dm_earning'], [
            'value' => $request['show_dm_earning'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'toggle_dm_registration'], [
            'value' => $request['dm_self_registration'],
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'dm_max_cash_in_hand'], [
            'value' => $request['dm_max_cash_in_hand'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'dm_picture_upload_status'], [
            'value' => $request['dm_picture_upload_status'],
        ]);

        Toastr::success(translate('messages.successfully_updated_to_changes_restart_app'));

        return back();
    }

    public function update_disbursement(Request $request)
    {
        if (getEnvMode() == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));

            return back();
        }

        Helpers::businessUpdateOrInsert(['key' => 'disbursement_type'], [
            'value' => $request['disbursement_type'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'restaurant_disbursement_time_period'], [
            'value' => $request['restaurant_disbursement_time_period'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'restaurant_disbursement_week_start'], [
            'value' => $request['restaurant_disbursement_week_start'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'restaurant_disbursement_waiting_time'], [
            'value' => $request['restaurant_disbursement_waiting_time'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'restaurant_disbursement_create_time'], [
            'value' => $request['restaurant_disbursement_create_time'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'restaurant_disbursement_min_amount'], [
            'value' => $request['restaurant_disbursement_min_amount'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'dm_disbursement_time_period'], [
            'value' => $request['dm_disbursement_time_period'],
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'dm_disbursement_week_start'], [
            'value' => $request['dm_disbursement_week_start'],
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'dm_disbursement_waiting_time'], [
            'value' => $request['dm_disbursement_waiting_time'],
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'dm_disbursement_create_time'], [
            'value' => $request['dm_disbursement_create_time'],
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'dm_disbursement_min_amount'], [
            'value' => $request['dm_disbursement_min_amount'],
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'system_php_path'], [
            'value' => $request['system_php_path'],
        ]);

        // Disbursement cadence is now driven by the Laravel scheduler
        // (App\Providers\ScheduleServiceProvider) via a single
        // `* * * * * php artisan schedule:run` crontab entry. Its when() gate
        // reads the settings saved above through
        // App\Support\DisbursementScheduleResolver, so a schedule change takes
        // effect on the next tick. The legacy exec()-based crontab installer was
        // removed: it interpolated the admin-supplied system_php_path into a
        // shell command (injection surface) and silently did nothing on Docker /
        // shared hosts without a cron daemon.
        Toastr::success(translate('messages.successfully_updated_disbursement_functionality'));

        return back();
    }

    public function update_order(Request $request)
    {

        if ($request?->home_delivery == null && $request?->take_away == null) {
            Toastr::warning(translate('messages.can_not_disable_both_take_away_and_delivery'));

            return back();
        }
        if ($request?->instant_order == null && $request?->schedule_order == null) {
            Toastr::warning(translate('messages.can_not_disable_both_schedule_order_and_instant_order'));

            return back();
        }
        $time = $request['schedule_order_slot_duration'];
        if ($request['schedule_order_slot_duration_time_formate'] == 'hour') {
            $time = $request['schedule_order_slot_duration'] * 60;
        }
        $settings = [
            'order_delivery_verification' => $request['order_delivery_verification'] ?? 0,
            'order_notification_type' => $request['order_notification_type'],
            'admin_order_notification' => $request['admin_order_notification'],
            'instant_order' => $request['instant_order'] ?? 0,
            'customer_date_order_sratus' => $request['customer_date_order_sratus'] ?? 0,
            'customer_order_date' => $request['customer_order_date'] ?? 0,
            'schedule_order' => $request['schedule_order'] ?? 0,
            'home_delivery' => $request['home_delivery'] ?? 0,
            'take_away' => $request['take_away'] ?? 0,
            'dine_in_order_option' => $request['dine_in_order_option'] ?? 0,
            'repeat_order_option' => $request['repeat_order_option'] ?? 0,
            'order_subscription' => $request['order_subscription'] ?? 0,
            'schedule_order_slot_duration' => $time,
            'schedule_order_slot_duration_time_formate' => $request['schedule_order_slot_duration_time_formate'],
            'canceled_by_restaurant' => $request['canceled_by_restaurant'],
            'canceled_by_deliveryman' => $request['canceled_by_deliveryman'],
            'can_restaurant_edit_order' => $request['can_restaurant_edit_order'],
            'order_confirmation_model' => $request['order_confirmation_model'],
        ];

        // if ($request['take_away'] != 1) {
        //     Restaurant::where('take_away', 1)->update(['take_away' => 0]);
        // }
        if ($request['dine_in_order_option'] != 1) {
            RestaurantConfig::where('dine_in', true)->update(['dine_in' => 0]);
        }
        // if ($request['home_delivery'] != 1) {
        //     Restaurant::where('delivery', 1)->update(['delivery' => 0]);
        // }
        if($request['order_subscription']  ==! 1){
            Restaurant::where('order_subscription_active', 1)->update([ 'order_subscription_active' => 0]);
        }
        Helpers::bulkAddOrUpdateBusinessSettings($settings);

        Toastr::success(translate('messages.successfully_updated_to_changes_restart_app'));

        return back();
    }

    public function updateFood(Request $request)
    {
        Helpers::businessUpdateOrInsert(['key' => 'toggle_veg_non_veg'], [
            'value' => $request['vnv'] ?? 0,
        ]);

        Toastr::success(translate('messages.successfully_updated_to_changes_restart_app'));

        return back();
    }

    public function update_priority(Request $request)
    {
        $list = ['popular_food', 'popular_restaurant', 'new_restaurant', 'all_restaurant', 'campaign_food', 'best_reviewed_food', 'category_list', 'cuisine_list', 'category_food', 'search_bar'];
        foreach ($list as $item) {
            Helpers::businessUpdateOrInsert(['key' => $item.'_default_status'], [
                'value' => $request[$item.'_default_status'] ?? 0,
            ]);

            if ($request[$item.'_default_status'] == '0') {

                if (! $request[$item.'_sort_by_general'] && $item != 'search_bar') {
                    Toastr::error(translate('you_must_selcet_an_option_for').' '.translate($item));

                    return back();
                }

                if ($request[$item.'_sort_by_general']) {
                    PriorityList::query()->updateOrInsert(['name' => $item.'_sort_by_general', 'type' => 'general'], [
                        'value' => $request[$item.'_sort_by_general'],
                    ]);
                }
                if ($request[$item.'_sort_by_unavailable']) {
                    PriorityList::query()->updateOrInsert(['name' => $item.'_sort_by_unavailable', 'type' => 'unavailable'], [
                        'value' => $request[$item.'_sort_by_unavailable'],
                    ]);
                }
                if ($request[$item.'_sort_by_temp_closed']) {
                    PriorityList::query()->updateOrInsert(['name' => $item.'_sort_by_temp_closed', 'type' => 'temp_closed'], [
                        'value' => $request[$item.'_sort_by_temp_closed'],
                    ]);
                }
                if ($request[$item.'_sort_by_rating']) {
                    PriorityList::query()->updateOrInsert(['name' => $item.'_sort_by_rating', 'type' => 'rating'], [
                        'value' => $request[$item.'_sort_by_rating'],
                    ]);
                }
            }
        }

        Toastr::success(translate('messages.successfully_updated_to_changes_restart_app'));

        return back();
    }

    public function update_payment_setup(Request $request)
    {
        return back();
    }

    public function business_setup(Request $request)
    {
        if (getEnvMode() == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));

            return back();
        }

        $key = ['logo', 'icon'];
        $settings = array_column(BusinessSetting::whereIn('key', $key)->get()->toArray(), 'value', 'key');

        $validator = Validator::make($request->all(), [
            'logo' => isset($settings['logo']) ? 'nullable|max:'.MAX_FILE_SIZE * 1024 : 'required|max:'.MAX_FILE_SIZE * 1024,
            'icon' => isset($settings['icon']) ? 'nullable|max:'.MAX_FILE_SIZE * 1024 : 'required|max:'.MAX_FILE_SIZE * 1024,
            // Saved further down by this same request. Without a rule a blank or unknown value
            // was silently rewritten to 'km', so an admin who cleared the field was told the
            // save succeeded and never learned their choice had been discarded.
            'distance_unit' => 'required|in:km,mi',
        ], [
            'logo.required' => translate('Logo is required'),
            'icon.required' => translate('Favicon is required'),
            'logo.max' => translate('Image size must be within '.MAX_FILE_SIZE.'mb'),
            'icon.max' => translate('Image size must be within '.MAX_FILE_SIZE.'mb'),
            'distance_unit.required' => translate('messages.distance_unit_is_required'),
            'distance_unit.in' => translate('messages.distance_unit_is_required'),
        ]);

        if ($validator->fails()) {
            Toastr::error($validator->errors()->first());

            return back();
        }

        $key = ['logo', 'icon'];
        $settings = array_column(BusinessSetting::whereIn('key', $key)->get()->toArray(), 'value', 'key');


        Helpers::businessUpdateOrInsert(['key' => 'country_picker_status'], [
            'value' => $request['country_picker_status'] ? $request['country_picker_status'] : 0,
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'additional_charge_status'], [
            'value' => $request['additional_charge_status'] ? $request['additional_charge_status'] : null,
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'additional_charge_name'], [
            'value' => $request['additional_charge_name'] ? $request['additional_charge_name'] : null,
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'additional_charge'], [
            'value' => $request['additional_charge'] ? $request['additional_charge'] : null,
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'business_name'], [
            'value' => $request['restaurant_name'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'currency'], [
            'value' => $request['currency'],
        ]);

        Config::set('currency', $request['currency']);
        Config::set('currency_symbol_position', $request['currency_symbol_position']);
        Config::set('currency_symbol', $request['currency_symbol']);

        Helpers::businessUpdateOrInsert(['key' => 'timezone'], [
            'value' => $request['timezone'],
        ]);

        if ($request->has('logo')) {

            $image_name = Helpers::update(dir: 'business/', old_image: $settings['logo'], format: 'png', image: $request->file('logo'));
        } else {
            $image_name = $settings['logo'];
        }

        Helpers::businessUpdateOrInsert(['key' => 'logo'], [
            'value' => $image_name,
        ]);

        if ($request->has('icon')) {

            $image_name = Helpers::update(dir: 'business/', old_image: $settings['icon'], format: 'png', image: $request->file('icon'));
        } else {
            $image_name = $settings['icon'];
        }

        Helpers::businessUpdateOrInsert(['key' => 'icon'], [
            'value' => $image_name,
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'phone'], [
            'value' => $request['phone'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'email_address'], [
            'value' => $request['email'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'address'], [
            'value' => $request['address'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'footer_text'], [
            'value' => $request['footer_text'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'cookies_text'], [
            'value' => $request['cookies_text'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'currency_symbol_position'], [
            'value' => $request['currency_symbol_position'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'admin_commission'], [
            'value' => $request['admin_commission'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'country'], [
            'value' => $request['country'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'default_location'], [
            'value' => json_encode(['lat' => $request['latitude'], 'lng' => $request['longitude']]),
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'timeformat'], [
            'value' => $request['time_format'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'digit_after_decimal_point'], [
            'value' => $request['digit_after_decimal_point'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'delivery_charge_comission'], [
            'value' => $request['admin_comission_in_delivery_charge'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'distance_unit'], [
            'value' => in_array($request['distance_unit'], ['km', 'mi']) ? $request['distance_unit'] : 'km',
        ]);

        if (! isset($request->commission) && ! isset($request->subscription)) {
            Toastr::error(translate('You_must_select_at_least_one_business_model_between_commission_and_subscription'));

            return back();
        }

        // For commission Model
        if (isset($request->commission) && ! isset($request->subscription)) {

            if (RestaurantSubscription::where('status', 1)->count() > 0) {
                Toastr::warning(translate('You_need_to_switch_your_subscribers_to_commission_first'));

                return back();
            }

            Helpers::businessUpdateOrInsert(['key' => 'business_model'], [
                'value' => json_encode(['commission' => 1, 'subscription' => 0]),
            ]);
            $business_model = BusinessSetting::where('key', 'business_model')->first()?->value;
            $business_model = json_decode($business_model, true) ?? [];

            if ($business_model && $business_model['subscription'] == 0) {
                Restaurant::query()->update(['restaurant_model' => 'commission']);
            }
        }
        // For subscription model
        elseif (isset($request->subscription) && ! isset($request->commission)) {
            Helpers::businessUpdateOrInsert(['key' => 'business_model'], [
                'value' => json_encode(['commission' => 0, 'subscription' => 1]),
            ]);
            $business_model = BusinessSetting::where('key', 'business_model')->first()?->value;
            $business_model = json_decode($business_model, true) ?? [];

            if ($business_model && $business_model['commission'] == 0) {
                Restaurant::where('restaurant_model', 'commission')
                    ->update([
                        'restaurant_model' => 'unsubscribed',
                        'status' => 0,
                    ]);
            }
        } else {
            Helpers::businessUpdateOrInsert(['key' => 'business_model'], [
                'value' => json_encode(['commission' => 1, 'subscription' => 1]),
            ]);
        }
        Toastr::success(translate('Successfully updated. To see the changes in app restart the app.'));

        return back();
    }

    public function storage_connection_index(Request $request)
    {
        return view('admin-views.business-settings.storage-connection-index');
    }

    public function openAI()
    {
        return view('admin-views.business-settings.3rd_party.open_ai_config');
    }

    public function openAISettings()
    {
        $data = array_column(BusinessSetting::whereIn('key', [
            'section_wise_ai_limit',
            'image_upload_limit_for_ai',
            'ai_chat_status',
        ])->get(['key', 'value'])->toArray(), 'value', 'key');

        return view('admin-views.business-settings.3rd_party.open_ai_settings', compact('data'));
    }

    public function openAISettingsUpdate(Request $request)
    {
        $limits = [
            'section_wise_ai_limit' => $request->section_wise_ai_limit ?? 0,
            'image_upload_limit_for_ai' => $request->image_upload_limit_for_ai ?? 0,
            'ai_chat_status' => $request->ai_chat_status ?? 0,
        ];

        foreach ($limits as $key => $value) {
            Helpers::businessUpdateOrInsert(['key' => $key], [
                'key' => $key,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        Toastr::success(translate('messages.updated_successfully'));

        return back();
    }

    public function storage_connection_update(Request $request, $name)
    {
        if ($name == 'local_storage') {
            Helpers::businessUpdateOrInsert(['key' => 'local_storage'], [
                'key' => 'local_storage',
                'value' => $request->status ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Helpers::businessUpdateOrInsert(['key' => '3rd_party_storage'], [
                'key' => '3rd_party_storage',
                'value' => $request->status == '1' ? 0 : 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        if ($name == '3rd_party_storage') {
            Helpers::businessUpdateOrInsert(['key' => '3rd_party_storage'], [
                'key' => '3rd_party_storage',
                'value' => $request->status ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Helpers::businessUpdateOrInsert(['key' => 'local_storage'], [
                'key' => 'local_storage',
                'value' => $request->status == '1' ? 0 : 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        if ($name == 'storage_connection') {
            Helpers::businessUpdateOrInsert(['key' => 's3_credential'], [
                'key' => 's3_credential',
                'value' => json_encode([
                    'key' => $request['key'],
                    'secret' => $request['secret'],
                    'region' => $request['region'],
                    'bucket' => $request['bucket'],
                    'url' => $request['url'],
                    'end_point' => $request['end_point'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Toastr::success(translate('messages.updated_successfully'));

        return back();
    }

    public function mail_index()
    {
        return view('admin-views.business-settings.mail-index');
    }

    public function mail_config(Request $request)
    {
        if (getEnvMode() == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));

            return back();
        }
        Helpers::businessUpdateOrInsert(
            ['key' => 'mail_config'],
            [
                'value' => json_encode([
                    'status' => $request['status'],
                    'name' => $request['name'],
                    'host' => $request['host'],
                    'driver' => $request['driver'],
                    'port' => $request['port'],
                    'username' => $request['username'],
                    'email_id' => $request['email'],
                    'encryption' => $request['encryption'],
                    'password' => $request['password'],
                ]),
                'updated_at' => now(),
            ]
        );
        Toastr::success(translate('messages.configuration_updated_successfully'));

        return back();
    }

    public function mail_config_status(Request $request)
    {
        if (getEnvMode() == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));

            return back();
        }
        $config = BusinessSetting::where(['key' => 'mail_config'])->first();

        $data = $config ? json_decode($config['value'], true) : null;

        Helpers::businessUpdateOrInsert(
            ['key' => 'mail_config'],
            [
                'value' => json_encode([
                    'status' => $request['status'] ?? 0,
                    'name' => $data['name'] ?? '',
                    'host' => $data['host'] ?? '',
                    'driver' => $data['driver'] ?? '',
                    'port' => $data['port'] ?? '',
                    'username' => $data['username'] ?? '',
                    'email_id' => $data['email_id'] ?? '',
                    'encryption' => $data['encryption'] ?? '',
                    'password' => $data['password'] ?? '',
                ]),
                'updated_at' => now(),
            ]
        );
        Toastr::success(translate('messages.configuration_updated_successfully'));

        return back();
    }

    public function openAIConfigStatus(Request $request)
    {
        if (getEnvMode() == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));

            return back();
        }
        $config = BusinessSetting::where(['key' => 'openai_config'])->first();

        $data = $config ? json_decode($config['value'], true) : null;

        Helpers::businessUpdateOrInsert(
            ['key' => 'openai_config'],
            [
                'value' => json_encode([
                    'status' => $request['status'] ?? 0,
                    'OPENAI_ORGANIZATION' => $data['OPENAI_ORGANIZATION'] ?? '',
                    'OPENAI_API_KEY' => $data['OPENAI_API_KEY'] ?? '',
                ]),
                'updated_at' => now(),
            ]
        );
        Toastr::success(translate('messages.configuration_updated_successfully'));

        return back();
    }

    public function openAIConfigUpdate(Request $request)
    {
        if (getEnvMode() == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));

            return back();
        }
        $config = BusinessSetting::where(['key' => 'openai_config'])->first();

        $data = $config ? json_decode($config['value'], true) : null;

        Helpers::businessUpdateOrInsert(
            ['key' => 'openai_config'],
            [
                'value' => json_encode([
                    'status' => $data['status'] ?? 0,
                    'OPENAI_ORGANIZATION' => $request['OPENAI_ORGANIZATION'] ?? '',
                    'OPENAI_API_KEY' => $request['OPENAI_API_KEY'] ?? '',
                ]),
                'updated_at' => now(),
            ]
        );
        Toastr::success(translate('messages.configuration_updated_successfully'));

        return back();
    }

    public function payment_index()
    {
        $published_status = addon_published_status('Gateways');

        $routes = config('addon_admin_routes');
        $desiredName = 'payment_setup';
        $payment_url = '';
        foreach ($routes as $routeArray) {
            foreach ($routeArray as $route) {
                if ($route['name'] === $desiredName) {
                    $payment_url = $route['url'];
                    break 2;
                }
            }
        }
        $data_values = Setting::whereIn('settings_type', ['payment_config'])->whereIn('key_name', ['ssl_commerz', 'paypal', 'stripe', 'razor_pay', 'senang_pay', 'paytabs', 'paystack', 'paymob_accept', 'paytm', 'flutterwave', 'liqpay', 'bkash', 'mercadopago'])->get();

        return view('admin-views.business-settings.payment-index', compact('published_status', 'payment_url', 'data_values'));
    }

    public function payment_config_update(Request $request)
    {
        $request['status'] = $request->status ?? 0;

        $validation = [
            'gateway' => 'required|in:ssl_commerz,paypal,stripe,razor_pay,senang_pay,paytabs,paystack,paymob_accept,paytm,flutterwave,liqpay,bkash,mercadopago',
            'mode' => 'required|in:live,test',
        ];

        $currency_check = Helpers::checkCurrency($request['gateway'], 'payment_gateway');

        if ($request['status'] == 1 && $currency_check !== true) {
            Toastr::warning(translate($currency_check).' '.translate('does_not_support_your_current_currency'));
        }

        $additional_data = [];

        if ($request['gateway'] == 'ssl_commerz') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'store_id' => 'required_if:status,1',
                'store_password' => 'required_if:status,1',
            ];
        } elseif ($request['gateway'] == 'paypal') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'client_id' => 'required_if:status,1',
                'client_secret' => 'required_if:status,1',
            ];
        } elseif ($request['gateway'] == 'stripe') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'api_key' => 'required_if:status,1',
                'published_key' => 'required_if:status,1',
            ];
        } elseif ($request['gateway'] == 'razor_pay') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'api_key' => 'required_if:status,1',
                'api_secret' => 'required_if:status,1',
            ];
        } elseif ($request['gateway'] == 'senang_pay') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'callback_url' => 'required_if:status,1',
                'secret_key' => 'required_if:status,1',
                'merchant_id' => 'required_if:status,1',
            ];
        } elseif ($request['gateway'] == 'paytabs') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'profile_id' => 'required_if:status,1',
                'server_key' => 'required_if:status,1',
                'base_url' => 'required_if:status,1',
            ];
        } elseif ($request['gateway'] == 'paystack') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'public_key' => 'required_if:status,1',
                'secret_key' => 'required_if:status,1',
                'merchant_email' => 'required_if:status,1',
            ];
        } elseif ($request['gateway'] == 'paymob_accept') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'callback_url' => 'required_if:status,1',
                'api_key' => 'required_if:status,1',
                'iframe_id' => 'required_if:status,1',
                'integration_id' => 'required_if:status,1',
                'hmac' => 'required_if:status,1',
            ];
        } elseif ($request['gateway'] == 'mercadopago') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'access_token' => 'required_if:status,1',
                'public_key' => 'required_if:status,1',
                'supported_country' => 'required_if:status,1',
            ];
        } elseif ($request['gateway'] == 'liqpay') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'private_key' => 'required_if:status,1',
                'public_key' => 'required_if:status,1',
            ];
        } elseif ($request['gateway'] == 'flutterwave') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'secret_key' => 'required_if:status,1',
                'public_key' => 'required_if:status,1',
                'hash' => 'required_if:status,1',
            ];
        } elseif ($request['gateway'] == 'paytm') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'merchant_key' => 'required_if:status,1',
                'merchant_id' => 'required_if:status,1',
                'merchant_website_link' => 'required_if:status,1',
            ];
        } elseif ($request['gateway'] == 'bkash') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'app_key' => 'required_if:status,1',
                'app_secret' => 'required_if:status,1',
                'username' => 'required_if:status,1',
                'password' => 'required_if:status,1',
            ];
        }

        $request->validate(array_merge($validation, $additional_data));
        if ($request['gateway']) {
            $settings = Setting::where('key_name', $request['gateway'])->where('settings_type', 'payment_config')->first();
            $additional_data_image = $settings['additional_data'] != null ? json_decode($settings['additional_data']) : null;
            $storage = $additional_data_image?->storage ?? 'public';
            if ($request->has('gateway_image')) {
                $gateway_image = $this->file_uploader('payment_modules/gateway_image/', 'png', $request['gateway_image'], $additional_data_image != null ? $additional_data_image->gateway_image : '');
                $storage = Helpers::getDisk();
            } else {
                $gateway_image = $additional_data_image != null ? $additional_data_image->gateway_image : '';

            }

            $payment_additional_data = [
                'gateway_title' => $request['gateway_title'],
                'gateway_image' => $gateway_image,
                'storage' => $storage,
            ];
            $validator = Validator::make($request->all(), array_merge($validation, $additional_data));

            Setting::updateOrCreate(['key_name' => $request['gateway'], 'settings_type' => 'payment_config'], [
                'key_name' => $request['gateway'],
                'live_values' => $validator->validate(),
                'test_values' => $validator->validate(),
                'settings_type' => 'payment_config',
                'mode' => $request['mode'],
                'is_active' => $request['status'],
                'additional_data' => json_encode($payment_additional_data),
            ]);
        }

        Toastr::success(GATEWAYS_DEFAULT_UPDATE_200['message']);

        return back();
    }

    public function theme_settings()
    {
        return view('admin-views.business-settings.theme-settings');
    }

    public function update_theme_settings(Request $request)
    {
        if (getEnvMode() == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));

            return back();
        }
        Helpers::businessUpdateOrInsert(['key' => 'theme'], [
            'value' => $request['theme'],
        ]);
        Toastr::success(translate('theme_settings_updated'));

        return back();
    }

    public function app_settings()
    {
        $key = [
            'app_minimum_version_android_restaurant','app_minimum_version_android','app_url_android',
            'app_url_android_restaurant','app_minimum_version_ios_restaurant','app_url_ios_restaurant','app_minimum_version_android_deliveryman','tax_included','order_subscription','app_minimum_version_ios',
            'app_url_android_deliveryman','app_url_ios','popular_food','popular_restaurant','new_restaurant',
            'most_reviewed_foods','app_minimum_version_ios_deliveryman','app_url_ios_deliveryman'
        ];
        $settings = BusinessSetting::whereIn('key', $key)->pluck('value','key')->toArray();
        $app_minimum_version_android=$settings['app_minimum_version_android'] ?? null;
        $app_url_android=$settings['app_url_android']?? null;
        $app_minimum_version_ios=$settings['app_minimum_version_ios'] ?? null;
        $app_url_ios=$settings['app_url_ios'] ?? null;
        $popular_food=$settings['popular_food'] ?? null;
        $popular_restaurant=$settings['popular_restaurant'] ?? null;
        $new_restaurant=$settings['new_restaurant'] ?? null;
        $most_reviewed_foods=$settings['most_reviewed_foods'] ?? null;
        $app_minimum_version_android_restaurant=$settings['app_minimum_version_android_restaurant'] ?? null;
        $app_url_android_restaurant= $settings['app_url_android_restaurant'] ?? null;
        $app_minimum_version_ios_restaurant=$settings['app_minimum_version_ios_restaurant'] ?? null;
        $app_url_ios_restaurant=$settings['app_url_ios_restaurant'] ?? null;
        $app_minimum_version_android_deliveryman=$settings['app_minimum_version_android_deliveryman'] ?? null;
        $app_url_android_deliveryman=$settings['app_url_android_deliveryman'] ?? null;
        $app_url_ios_deliveryman=$settings['app_url_ios_deliveryman'] ?? null;
        $app_minimum_version_ios_deliveryman=$settings['app_minimum_version_ios_deliveryman'] ?? null;

        $user_app_download_status = \App\Models\DataSetting::where('key', 'user_app_download_status')->first()?->value ?? 0;
        $user_app_download_title = \App\Models\DataSetting::withoutGlobalScope('translate')->with('translations')->where('key', 'user_app_download_title')->first();

        return view('admin-views.business-settings.app-settings', compact(
            'app_minimum_version_android',
            'app_url_android',
            'app_minimum_version_ios',
            'app_url_ios',
            'popular_food',
            'popular_restaurant',
            'new_restaurant',
            'most_reviewed_foods',
            'app_minimum_version_android_restaurant',
            'app_url_android_restaurant',
            'app_minimum_version_ios_restaurant',
            'app_url_ios_restaurant',
            'app_minimum_version_android_deliveryman',
            'app_url_android_deliveryman',
            'app_url_ios_deliveryman',
            'app_minimum_version_ios_deliveryman',
            'user_app_download_status',
            'user_app_download_title'
        ));
    }

    public function user_app_download_update(Request $request)
    {
        if (getEnvMode() == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));
            return back();
        }

        $this->update_data_setting_data($request, 'user_app_download_title');
        DataSetting::updateOrCreate(
            ['key' => 'user_app_download_status'],
            [
                'type' => 'app_settings',
                'value' => $request->user_app_download_status ?? 0,
            ]
        );

        Toastr::success(translate('messages.User_app_download_updated'));
        return back();
    }

    public function update_app_settings(Request $request)
    {
        if (getEnvMode() == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));

            return back();
        }
        if ($request->type == 'user_app') {
            Helpers::businessUpdateOrInsert(['key' => 'app_minimum_version_android'], [
                'value' => $request['app_minimum_version_android'],
            ]);
            Helpers::businessUpdateOrInsert(['key' => 'app_url_android'], [
                'value' => $request['app_url_android'],
            ]);
            Helpers::businessUpdateOrInsert(['key' => 'app_minimum_version_ios'], [
                'value' => $request['app_minimum_version_ios'],
            ]);

            Helpers::businessUpdateOrInsert(['key' => 'app_url_ios'], [
                'value' => $request['app_url_ios'],
            ]);
            Toastr::success(translate('messages.User_app_settings_updated'));

            return back();
        }
        if ($request->type == 'restaurant_app') {
            Helpers::businessUpdateOrInsert(['key' => 'app_minimum_version_android_restaurant'], [
                'value' => $request['app_minimum_version_android_restaurant'],
            ]);
            Helpers::businessUpdateOrInsert(['key' => 'app_url_android_restaurant'], [
                'value' => $request['app_url_android_restaurant'],
            ]);
            Helpers::businessUpdateOrInsert(['key' => 'app_minimum_version_ios_restaurant'], [
                'value' => $request['app_minimum_version_ios_restaurant'],
            ]);
            Helpers::businessUpdateOrInsert(['key' => 'app_url_ios_restaurant'], [
                'value' => $request['app_url_ios_restaurant'],
            ]);
            Toastr::success(translate('messages.Restaurant_app_settings_updated'));

            return back();
        }
        if ($request->type == 'delivery_app') {
            Helpers::businessUpdateOrInsert(['key' => 'app_minimum_version_android_deliveryman'], [
                'value' => $request['app_minimum_version_android_deliveryman'],
            ]);
            Helpers::businessUpdateOrInsert(['key' => 'app_url_android_deliveryman'], [
                'value' => $request['app_url_android_deliveryman'],
            ]);
            Helpers::businessUpdateOrInsert(['key' => 'app_minimum_version_ios_deliveryman'], [
                'value' => $request['app_minimum_version_ios_deliveryman'],
            ]);
            Helpers::businessUpdateOrInsert(['key' => 'app_url_ios_deliveryman'], [
                'value' => $request['app_url_ios_deliveryman'],
            ]);

            Toastr::success(translate('messages.Delivery_app_settings_updated'));

            return back();
        }

        return back();
    }

    private function update_data_setting_data($request, $key_data)
    {
        $data = DataSetting::firstOrNew(
            [
                'key' => $key_data,
            ],
        );

        $data->type = 'app_settings';
        $data->value = $request->{$key_data}[array_search('default', $request->lang)];
        $data->save();
        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang as $index => $key) {
            if ($default_lang == $key && ! ($request->{$key_data}[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\DataSetting',
                            'translationable_id' => $data->id,
                            'locale' => $key,
                            'key' => $key_data,
                        ],
                        ['value' => $data->value]
                    );
                }
            } else {
                if ($request->{$key_data}[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\DataSetting',
                            'translationable_id' => $data->id,
                            'locale' => $key,
                            'key' => $key_data,
                        ],
                        ['value' => $request->{$key_data}[$index]]
                    );
                }
            }
        }
    }


    private function update_data($request, $key_data)
    {
        $data = DataSetting::firstOrNew(
            [
                'key' => $key_data,
                'type' => 'admin_landing_page',
            ],
        );

        $data->value = $request->{$key_data}[array_search('default', $request->lang)];
        $data->save();
        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang as $index => $key) {
            if ($default_lang == $key && ! ($request->{$key_data}[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\DataSetting',
                            'translationable_id' => $data->id,
                            'locale' => $key,
                            'key' => $key_data,
                        ],
                        ['value' => $data->value]
                    );
                }
            } else {
                if ($request->{$key_data}[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\DataSetting',
                            'translationable_id' => $data->id,
                            'locale' => $key,
                            'key' => $key_data,
                        ],
                        ['value' => $request->{$key_data}[$index]]
                    );
                }
            }
        }

        return true;
    }

    private function policy_status_update($key_data, $status)
    {
        $data = DataSetting::firstOrNew(
            [
                'key' => $key_data,
                'type' => 'admin_landing_page',
            ],
        );
        $data->value = $status;
        $data->save();

        return true;
    }

    public function terms_and_conditions()
    {
        $terms_and_conditions = DataSetting::withoutGlobalScope('translate')->where('type', 'admin_landing_page')->where('key', 'terms_and_conditions')->first();

        return view('admin-views.business-settings.terms-and-conditions', compact('terms_and_conditions'));
    }

    public function terms_and_conditions_update(Request $request)
    {
        $this->update_data($request, 'terms_and_conditions');
        Toastr::success(translate('messages.terms_and_condition_updated'));

        return back();
    }

    public function privacy_policy()
    {
        $privacy_policy = DataSetting::withoutGlobalScope('translate')->where('type', 'admin_landing_page')->where('key', 'privacy_policy')->first();

        return view('admin-views.business-settings.privacy-policy', compact('privacy_policy'));
    }

    public function privacy_policy_update(Request $request)
    {
        $this->update_data($request, 'privacy_policy');
        Toastr::success(translate('messages.privacy_policy_updated'));

        return back();
    }

    public function refund_policy()
    {
        $refund_policy = DataSetting::withoutGlobalScope('translate')->where('type', 'admin_landing_page')->where('key', 'refund_policy')->first();
        $refund_policy_status = DataSetting::where('type', 'admin_landing_page')->where('key', 'refund_policy_status')->first();

        return view('admin-views.business-settings.refund_policy', compact('refund_policy', 'refund_policy_status'));
    }

    public function refund_policy_update(Request $request)
    {
        $this->update_data($request, 'refund_policy');
        Toastr::success(translate('messages.refund_policy_updated'));

        return back();
    }

    public function refund_policy_status($status)
    {
        $this->policy_status_update('refund_policy_status', $status);

        return response()->json(['status' => 'changed']);
    }

    public function shipping_policy()
    {

        $shipping_policy = DataSetting::withoutGlobalScope('translate')->where('type', 'admin_landing_page')->where('key', 'shipping_policy')->first();
        $shipping_policy_status = DataSetting::where('type', 'admin_landing_page')->where('key', 'shipping_policy_status')->first();

        return view('admin-views.business-settings.shipping_policy', compact('shipping_policy', 'shipping_policy_status'));
    }

    public function shipping_policy_update(Request $request)
    {
        $this->update_data($request, 'shipping_policy');
        Toastr::success(translate('messages.shipping_policy_updated'));

        return back();
    }

    public function shipping_policy_status($status)
    {
        $this->policy_status_update('shipping_policy_status', $status);

        return response()->json(['status' => 'changed']);
    }

    public function cancellation_policy()
    {
        $cancellation_policy = DataSetting::withoutGlobalScope('translate')->where('type', 'admin_landing_page')->where('key', 'cancellation_policy')->first();
        $cancellation_policy_status = DataSetting::where('type', 'admin_landing_page')->where('key', 'cancellation_policy_status')->first();

        return view('admin-views.business-settings.cancellation_policy', compact('cancellation_policy', 'cancellation_policy_status'));
    }

    public function cancellation_policy_update(Request $request)
    {
        $this->update_data($request, 'cancellation_policy');
        Toastr::success(translate('messages.cancellation_policy_updated'));

        return back();
    }

    public function cancellation_policy_status($status)
    {
        $this->policy_status_update('cancellation_policy_status', $status);

        return response()->json(['status' => 'changed']);
    }

    public function about_us()
    {
        $about_us = DataSetting::withoutGlobalScope('translate')->where('type', 'admin_landing_page')->where('key', 'about_us')->first();

        return view('admin-views.business-settings.about-us', compact('about_us'));
    }

    public function about_us_update(Request $request)
    {
        $this->update_data($request, 'about_us');
        Toastr::success(translate('messages.about_us_updated'));

        return back();
    }

    public function fcm_index()
    {
        $fcm_credentials = Helpers::get_business_settings('fcm_credentials');

        return view('admin-views.business-settings.fcm-config', compact('fcm_credentials'));
    }

    public function notificationMessages(Request $request)
    {
        $messageKey = $this->getPushNotificationMessageKey($request['message-type'] ?? 'user');
        $notificationMessage = NotificationMessage::withOutGlobalScope('translate')->where('user_type', $request['message-type'] ?? 'user')->whereIn('key', $messageKey)->with('translations')->select(['id', 'key', 'message', 'status'])
            ->get()->keyBy('key');

        $cart_reminder_after_time = DataSetting::where(['key' => 'cart_reminder_after_time', 'type' => 'notification_settings'])->first()?->value ?? 0;
        $cart_reminder_after = DataSetting::where(['key' => 'cart_reminder_after', 'type' => 'notification_settings'])->first()?->value ?? 'min';

        $subscription_reminder_before_time = DataSetting::where(['key' => 'subscription_reminder_before_time', 'type' => 'notification_settings'])->first()?->value ?? 0;
        $subscription_reminder_before = DataSetting::where(['key' => 'subscription_reminder_before', 'type' => 'notification_settings'])->first()?->value ?? 'days';

        return view('admin-views.business-settings.notification.notification_message', compact('notificationMessage', 'messageKey', 'cart_reminder_after_time', 'cart_reminder_after', 'subscription_reminder_before_time', 'subscription_reminder_before'));
    }

    public function update_fcm(Request $request)
    {
        Helpers::businessUpdateOrInsert(['key' => 'push_notification_service_file_content'], [
            'value' => $request['push_notification_service_file_content'],
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'fcm_project_id'], [
            'value' => $request['projectId'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'fcm_credentials'], [
            'value' => json_encode([
                'apiKey' => $request->apiKey,
                'authDomain' => $request->authDomain,
                'projectId' => $request->projectId,
                'storageBucket' => $request->storageBucket,
                'messagingSenderId' => $request->messagingSenderId,
                'appId' => $request->appId,
                'measurementId' => $request->measurementId,
                // Web Push VAPID public key — used by the Builder storefront's
                // web-push subscription (SettingsProvider::firebaseConfig()).
                'vapidKey' => $request->vapidKey,
            ]),
        ]);

        self::firebase_message_config_file_gen();

        Toastr::success(translate('messages.settings_updated'));
        session()->put('fcm_updated', 1);

        return redirect()->back();
    }

    public function firebase_message_config_file_gen()
    {
        $config = Helpers::get_business_settings('fcm_credentials');

        $apiKey = $config['apiKey'] ?? '';
        $authDomain = $config['authDomain'] ?? '';
        $projectId = $config['projectId'] ?? '';
        $storageBucket = $config['storageBucket'] ?? '';
        $messagingSenderId = $config['messagingSenderId'] ?? '';
        $appId = $config['appId'] ?? '';
        $measurementId = $config['measurementId'] ?? '';

        // The browser always asks for /firebase-messaging-sw.js at the origin root, so the
        // file has to live in whichever directory the domain points at. Both layouts are
        // supported (root docroot on shared hosting, public/ otherwise) and the deployment
        // is not knowable from here, so write both - a stale copy in the unused one would
        // silently break registration after the credentials change.
        $filePaths = array_unique([base_path('firebase-messaging-sw.js'), public_path('firebase-messaging-sw.js')]);

        try {
            // Kept in step with the committed firebase-messaging-sw.js. This template is the
            // only writer of that file, so anything added there by hand has to be mirrored
            // here or the next credentials save silently reverts it.
            $fileContent = <<<JS
                importScripts("https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js");
                importScripts("https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js");

                firebase.initializeApp({
                    apiKey: "$apiKey",
                    authDomain: "$authDomain",
                    projectId: "$projectId",
                    storageBucket: "$storageBucket",
                    messagingSenderId: "$messagingSenderId",
                    appId: "$appId",
                    measurementId: "$measurementId"
                });

                const messaging = firebase.messaging();

                messaging.onBackgroundMessage((payload) => {
                    const data = payload.data || {};
                    const title = data.title || (payload.notification && payload.notification.title) || "Notification";
                    const body  = data.body  || (payload.notification && payload.notification.body)  || "";
                    const image = data.image || (payload.notification && payload.notification.image) || undefined;

                    self.registration.showNotification(title, {
                        body,
                        icon: image,
                        data,
                    });
                });

                self.addEventListener("notificationclick", (event) => {
                    event.notification.close();
                    const data = event.notification.data || {};
                    const url = resolveTargetUrl(data);

                    event.waitUntil(
                        self.clients
                            .matchAll({ type: "window", includeUncontrolled: true })
                            .then((windowClients) => {
                                const existing = windowClients.find((c) => c.url.startsWith(self.location.origin));
                                if (existing) {
                                    existing.focus();
                                    return existing.navigate(url);
                                }
                                return self.clients.openWindow(url);
                            }),
                    );
                });

                function resolveTargetUrl(data) {
                    const base = self.location.origin;
                    if (data && data.type === "order_status" && data.order_id) {
                        return base + "/profile?page=orders&orderId=" + encodeURIComponent(data.order_id);
                    }
                    if (data && data.type === "message") {
                        return base + "/profile?page=inbox";
                    }
                    return base + "/";
                }
                JS;

            foreach ($filePaths as $filePath) {
                // Each path is tried on its own: a locked-down project root must not stop
                // the public/ copy from being written.
                try {
                    if (file_exists($filePath) && ! is_writable($filePath) && ! chmod($filePath, 0644)) {
                        throw new \Exception('File is not writable and permission change failed: '.$filePath);
                    }

                    if (file_put_contents($filePath, $fileContent) === false) {
                        throw new \Exception('Failed to write to file: '.$filePath);
                    }
                } catch (\Exception $e) {
                    info(['firebase_sw_write' => $e->getMessage()]);
                }
            }
        } catch (\Exception $e) {
            //
        }
    }

    private function getPushNotificationMessageKey($userType)
    {
        if ($userType == 'deliveryman') {

            $messageKey = [
                'deliveryman_account_block_message' => 'deliveryman_account_block',
                'deliveryman_account_unblock_message' => 'deliveryman_account_unblock',
                'deliveryman_collect_cash_message' => 'deliveryman_collect_cash',
                'deliveryman_order_assign_message' => 'deliveryman_order_assign',
                'deliveryman_order_unassign_message' => 'deliveryman_order_unassign',
                'deliveryman_order_proceed_for_cooking_message' => 'deliveryman_order_proceed_for_cooking',
                'deliveryman_order_ready_for_delivery_message' => 'deliveryman_order_ready_for_delivery',
                'deliveryman_new_order_message' => 'deliveryman_new_order',
            ];

        } elseif ($userType == 'restaurant') {
            $messageKey = [
                'restaurant_account_block_message' => 'restaurant_account_block',
                'restaurant_account_unblock_message' => 'restaurant_account_unblock',
                'restaurant_withdraw_approve_message' => 'restaurant_withdraw_approve',
                'restaurant_withdraw_rejaction_message' => 'restaurant_withdraw_rejaction',
                'restaurant_campaign_join_approve_message' => 'restaurant_campaign_join_approve',
                'restaurant_campaign_join_rejaction_message' => 'restaurant_campaign_join_rejaction',
                'restaurant_order_notification_message' => 'restaurant_order_notification',
                'restaurant_advertisement_added_by_admin_message' => 'restaurant_advertisement_added_by_admin',
                'restaurant_advertisement_approve_message' => 'restaurant_advertisement_approve',
                'restaurant_advertisement_deny_message' => 'restaurant_advertisement_deny',
                'restaurant_advertisement_pause_message' => 'restaurant_advertisement_pause',
                'restaurant_advertisement_resume_message' => 'restaurant_advertisement_resume',
                'restaurant_subscription_success_message' => 'restaurant_subscription_success',
                'restaurant_subscription_renew_message' => 'restaurant_subscription_renew',
                'restaurant_subscription_shift_message' => 'restaurant_subscription_shift',
                'restaurant_subscription_cancel_message' => 'restaurant_subscription_cancel',
                'restaurant_subscription_plan_update_from_admin_panel' => 'restaurant_subscription_plan_update',
                // BOGO and Happy Hour. This list drives both the render and the save on the
                // Notification Messages screen, so a key missing here is a message the admin
                // can neither see nor edit - and the send path reads exactly these rows.
                //
                // Push keys only. restaurant_bogo_join_request and its happy hour twin are
                // deliberately absent: the restaurant's own join request is acknowledged by
                // mail, never by push, so a message body for them would be a field the admin
                // can edit that nothing ever sends. Their mail toggles still live in
                // notification_settings, which is where mail is configured.
                'restaurant_bogo_join_approval_message' => 'restaurant_bogo_join_approval',
                'restaurant_bogo_join_rejection_message' => 'restaurant_bogo_join_rejection',
                'restaurant_bogo_invitation_message' => 'restaurant_bogo_invitation',
                'restaurant_happy_hour_join_approval_message' => 'restaurant_happy_hour_join_approval',
                'restaurant_happy_hour_join_rejection_message' => 'restaurant_happy_hour_join_rejection',
                'restaurant_happy_hour_invitation_message' => 'restaurant_happy_hour_invitation',
            ];

        } else {
            $messageKey = [
                'order_pending_message' => 'order_pending_message',
                'order_confirmation_message' => 'order_confirmation_msg',
                'order_processing_message' => 'order_processing_message',
                'restaurant_handover_message' => 'order_handover_message',
                'order_out_for_delivery_message' => 'out_for_delivery_message',
                'order_delivered_message' => 'order_delivered_message',
                'deliveryman_assign_message' => 'delivery_boy_assign_message',
                'deliveryman_delivered_message' => 'delivery_boy_delivered_message',
                'order_canceled_message' => 'order_cancled_message',
                'order_refunded_message' => 'order_refunded_message',
                'order_refund_cancel_message' => 'refund_request_canceled',
                'offline_order_accept_message' => 'offline_order_accept_message',
                'offline_order_deny_message' => 'offline_order_deny_message',
                'customer_delivery_verification_message' => 'customer_delivery_verification',
                'customer_dine_in_table_or_token_message' => 'customer_dine_in_table_or_token',
                'customer_add_fund_to_wallet_message' => 'customer_add_fund_to_wallet',
                'customer_referral_bonus_earning_message' => 'customer_referral_bonus_earning',
                'customer_new_referral_join_message' => 'customer_new_referral_join',
                'customer_cashback_message' => 'customer_cashback',
                'customer_account_block_message' => 'customer_account_block',
                'customer_account_unblock_message' => 'customer_account_unblock',
                'subscription_expire_reminder' => 'subscription_expire_reminder',
                'subscription_activated' => 'subscription_activated',
                'subscription_expired' => 'subscription_expired',
                'subscription_canceled' => 'subscription_canceled',
                'cart_abandon'=>'cart_abandon'
            ];
        }

        return $messageKey;
    }

    public function updateFcmMessages(Request $request)
    {

        $messageKey = $this->getPushNotificationMessageKey($request->user_type);

        $previousStatuses = [];
        if ($request->user_type == 'user') {
            $previousStatuses = NotificationMessage::where('user_type', 'user')
                ->whereIn('key', ['cart_abandon', 'subscription_expire_reminder'])
                ->pluck('status', 'key')
                ->all();
        }

        foreach ($messageKey as $msgKey) {

            $defaultLangIndex = array_search('default', $request->lang, true);

            if ($defaultLangIndex !== false) {
                $request->validate([
                    "{$msgKey}.{$defaultLangIndex}" => "required_if:{$msgKey}_status,1",
                ], [
                    "{$msgKey}.{$defaultLangIndex}.required_if" =>$msgKey .' '. translate('default field is required when status is active.'),
                ]);
            }

            if ($request[$msgKey][array_search('default', $request->lang)] != '') {
                $notification = NotificationMessage::firstOrNew([
                    'user_type' => $request->user_type,
                    'key' => $msgKey,
                ]);

                $notification->message = $request[$msgKey][array_search('default', $request->lang)];
                $notification->status = $request[$msgKey.'_status'] ?? 0;
                $notification->user_type = $request->user_type;
                $notification->save();
                Helpers::add_or_update_translations(request: $request, key_data: $msgKey, name_field: $msgKey, model_name: 'NotificationMessage', data_id: $notification->id, data_value: $notification->message);
            }

        }

        if ($request->user_type == 'user') {
            if ($request->has('cart_reminder_after_time')) {
                DataSetting::updateOrCreate(
                    ['key' => 'cart_reminder_after_time', 'type' => 'notification_settings'],
                    ['value' => $request->cart_reminder_after_time]
                );
            }

            if ($request->has('cart_reminder_after')) {
                DataSetting::updateOrCreate(
                    ['key' => 'cart_reminder_after', 'type' => 'notification_settings'],
                    ['value' => $request->cart_reminder_after]
                );
            }

            if ($request->has('subscription_reminder_before_time')) {
                DataSetting::updateOrCreate(
                    ['key' => 'subscription_reminder_before_time', 'type' => 'notification_settings'],
                    ['value' => $request->subscription_reminder_before_time]
                );
            }

            if ($request->has('subscription_reminder_before')) {
                DataSetting::updateOrCreate(
                    ['key' => 'subscription_reminder_before', 'type' => 'notification_settings'],
                    ['value' => $request->subscription_reminder_before]
                );
            }

            if ($request->input('cart_abandon_status') == 1 && ($previousStatuses['cart_abandon'] ?? 0) != 1) {
                Session::flash('cart_abandon_dependency', true);
            }

            if ($request->input('subscription_expire_reminder_status') == 1 && ($previousStatuses['subscription_expire_reminder'] ?? 0) != 1) {
                Session::flash('subscription_reminder_dependency', true);
            }

        }

        Toastr::success(translate('Push notification messages updated successfully'));

        return back();
    }

    public function config_setup()
    {
        return view('admin-views.business-settings.config');
    }

    public function config_update(Request $request)
    {
        Helpers::businessUpdateOrInsert(['key' => 'map_api_key'], [
            'value' => $request['map_api_key'],
        ]);

        Helpers::businessUpdateOrInsert(['key' => 'map_api_key_server'], [
            'value' => $request['map_api_key_server'],
        ]);

        Toastr::success(translate('messages.config_data_updated'));

        return back();
    }

    public function toggle_settings($key, $value)
    {
        Helpers::businessUpdateOrInsert(['key' => $key], [
            'value' => $value,
        ]);

        Toastr::success(translate('messages.app_settings_updated'));

        return back();
    }

    public function viewSocialLogin()
    {
        $data = BusinessSetting::where('key', 'social_login')->first();
        if (! $data) {
            Helpers::insert_business_settings_key('social_login', '[{"login_medium":"google","client_id":"","client_secret":"","status":"0"},{"login_medium":"facebook","client_id":"","client_secret":"","status":""}]');
            $data = BusinessSetting::where('key', 'social_login')->first();
        }
        $apple = BusinessSetting::where('key', 'apple_login')->first();
        if (! $apple) {
            Helpers::insert_business_settings_key('apple_login', '[{"login_medium":"apple","client_id":"","client_secret":"","team_id":"","key_id":"","service_file":"","redirect_url":"","status":""}]');
            $apple = BusinessSetting::where('key', 'apple_login')->first();
        }
        $appleLoginServices = json_decode($apple?->value, true);
        $socialLoginServices = json_decode($data?->value, true);

        return view('admin-views.business-settings.social-login.view', compact('socialLoginServices', 'appleLoginServices'));
    }

    public function updateSocialLogin($service, Request $request)
    {
        $login_setup_status = Helpers::get_business_settings($service.'_login_status') ?? 0;
        if ($login_setup_status && ($request['status'] == 0)) {
            Toastr::warning(translate($service.'_login_status_is_enabled_in_login_setup._First_disable_from_login_setup.'));

            return redirect()->back();
        }
        $socialLogin = BusinessSetting::where('key', 'social_login')->first();
        $credential_array = [];
        foreach (json_decode($socialLogin['value'], true) as $key => $data) {
            if ($data['login_medium'] == $service) {
                $cred = [
                    'login_medium' => $service,
                    'client_id' => $request['client_id'],
                    'client_secret' => $request['client_secret'],
                    'status' => $request['status'],
                ];
                array_push($credential_array, $cred);
            } else {
                array_push($credential_array, $data);
            }
        }
        Helpers::businessUpdateOrInsert(['key' => 'social_login'], [
            'value' => $credential_array,
        ]);

        Toastr::success(translate('messages.credential_updated', ['service' => $service]));

        return redirect()->back();
    }

    public function updateAppleLogin($service, Request $request)
    {
        $login_setup_status = Helpers::get_business_settings($service.'_login_status') ?? 0;
        if ($login_setup_status && ($request['status'] == 0)) {
            Toastr::warning(translate($service.'_login_status_is_enabled_in_login_setup._First_disable_from_login_setup.'));

            return redirect()->back();
        }
        $appleLogin = BusinessSetting::where('key', 'apple_login')->first();
        $credential_array = [];
        if ($request->hasfile('service_file')) {
            $fileName = Helpers::upload(dir: 'apple-login/', format: 'p8', image: $request->file('service_file'));
        }
        foreach (json_decode($appleLogin['value'], true) as $key => $data) {
            if ($data['login_medium'] == $service) {
                $cred = [
                    'login_medium' => $service,
                    'client_id' => $request['client_id'],
                    'client_secret' => $request['client_secret'],
                    'status' => $request['status'],
                    'team_id' => $request['team_id'],
                    'key_id' => $request['key_id'],
                    'service_file' => isset($fileName) ? $fileName : $data['service_file'],
                    'redirect_url' => $request['redirect_url'],
                ];
                array_push($credential_array, $cred);
            } else {
                array_push($credential_array, $data);
            }
        }
        Helpers::businessUpdateOrInsert(['key' => 'apple_login'], [
            'value' => $credential_array,
        ]);
        Toastr::success(translate('messages.credential_updated', ['service' => $service]));

        return redirect()->back();
    }

    // recaptcha
    public function recaptcha_index(Request $request)
    {
        return view('admin-views.business-settings.recaptcha-index');
    }

    public function recaptcha_update(Request $request)
    {
        // dd( $request['status']);
        Helpers::businessUpdateOrInsert(['key' => 'recaptcha'], [
            'key' => 'recaptcha',
            'value' => json_encode([
                'status' => $request['status'],
                'site_key' => $request['site_key'],
                'secret_key' => $request['secret_key'],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Toastr::success(translate('messages.updated_successfully'));

        return back();
    }

    public function send_mail(Request $request)
    {
        $response_flag = 0;
        $message = 'success';
        try {

            Mail::to($request->email)->send(new \App\Mail\TestEmailSender);
            $response_flag = 1;
        } catch (\Exception $exception) {
            info([$exception->getFile(), $exception->getLine(), $exception->getMessage()]);
            $response_flag = 2;
            $message = $exception->getMessage();
        }

        return response()->json(['success' => $response_flag, 'message' => $message]);
    }

    public function site_direction(Request $request)
    {
        if (getEnvMode() == 'demo') {
            session()->put('site_direction', ($request->status == 1 ? 'ltr' : 'rtl'));

            return response()->json();
        }
        if ($request->status == 1) {
            Helpers::businessUpdateOrInsert(['key' => 'site_direction'], [
                'value' => 'ltr',
            ]);
        } else {
            Helpers::businessUpdateOrInsert(['key' => 'site_direction'], [
                'value' => 'rtl',
            ]);
        }

    }

    public function email_index(Request $request, $type, $tab)
    {
        $template = $request->query('template', null);

        $overrides = [
            'new-order'               => 'place-order-format',
            'forgot-password'         => 'forgot-pass-format',
            'offline-payment-approve' => 'offline-approved-format',
            'offline-payment-deny'    => 'offline-deny-format',
        ];

        $allowed = [
            'new-order', 'forgot-password', 'restaurant-registration', 'dm-registration',
            'registration', 'approve', 'deny', 'withdraw-request', 'withdraw-approve',
            'withdraw-deny', 'campaign-request', 'campaign-approve', 'campaign-deny',
            'bogo-request', 'bogo-approve', 'bogo-deny',
            'happy-hour-request', 'happy-hour-approve', 'happy-hour-deny',
            'refund-request', 'login', 'suspend', 'unsuspend', 'cash-collect',
            'registration-otp', 'login-otp', 'order-verification', 'refund-request-deny',
            'add-fund', 'refund-order', 'offline-payment-approve', 'offline-payment-deny',
            'pos-registration', 'new-advertisement', 'update-advertisement',
            'advertisement-create', 'advertisement-approved', 'advertisement-deny',
            'advertisement-resume', 'advertisement-pause', 'subscription-successful',
            'subscription-renew', 'subscription-shift', 'subscription-cancel',
            'subscription-deadline', 'subscription-plan_upadte', 'profile-verification',
        ];

        if (! in_array($tab, $allowed, true)) {
            abort(404);
        }

        $format = $overrides[$tab] ?? $tab.'-format';

        return view('admin-views.business-settings.email-format-setting.'.$type.'-email-formats.'.$format, compact('template'));
    }

    public function update_email_index(Request $request, $type, $tab)
    {
        if (getEnvMode() == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));

            return back();
        }

        $email_types = [
            'new-order' => 'new_order',
            'forget-password' => 'forget_password',
            'restaurant-registration' => 'restaurant_registration',
            'dm-registration' => 'dm_registration',
            'registration' => 'registration',
            'approve' => 'approve',
            'deny' => 'deny',
            'withdraw-request' => 'withdraw_request',
            'withdraw-approve' => 'withdraw_approve',
            'withdraw-deny' => 'withdraw_deny',
            'campaign-request' => 'campaign_request',
            'campaign-approve' => 'campaign_approve',
            'campaign-deny' => 'campaign_deny',
            'bogo-request' => 'bogo_request',
            'bogo-approve' => 'bogo_approve',
            'bogo-deny' => 'bogo_deny',
            'happy-hour-request' => 'happy_hour_request',
            'happy-hour-approve' => 'happy_hour_approve',
            'happy-hour-deny' => 'happy_hour_deny',
            'refund-request' => 'refund_request',
            'login' => 'login',
            'suspend' => 'suspend',
            'unsuspend' => 'unsuspend',
            'cash-collect' => 'cash_collect',
            'registration-otp' => 'registration_otp',
            'login-otp' => 'login_otp',
            'order-verification' => 'order_verification',
            'refund-request-deny' => 'refund_request_deny',
            'add-fund' => 'add_fund',
            'refund-order' => 'refund_order',
            'offline-payment-deny' => 'offline_payment_deny',
            'offline-payment-approve' => 'offline_payment_approve',
            'pos-registration' => 'pos_registration',
            'new-advertisement' => 'new_advertisement',
            'update-advertisement' => 'update_advertisement',
            'advertisement-pause' => 'advertisement_pause',
            'advertisement-approved' => 'advertisement_approved',
            'advertisement-create' => 'advertisement_create',
            'advertisement-deny' => 'advertisement_deny',
            'advertisement-resume' => 'advertisement_resume',
            'subscription-successful' => 'subscription-successful',
            'subscription-renew' => 'subscription-renew',
            'subscription-shift' => 'subscription-shift',
            'subscription-cancel' => 'subscription-cancel',
            'subscription-deadline' => 'subscription-deadline',
            'subscription-plan_upadte' => 'subscription-plan_upadte',
            'profile-verification' => 'profile_verification',
        ];

        $email_type = $email_types[$tab] ?? null;
        $template = EmailTemplate::firstOrNew([
            'type' => $type,
            'email_type' => $email_type,
        ]);

        $template->title = $request->title[array_search('default', $request->lang)];
        $template->body = $request->body[array_search('default', $request->lang)];

        $template->body_2 = $request?->body_2 ? $request->body_2[array_search('default', $request->lang)] : null;
        $template->button_name = $request->button_name ? $request->button_name[array_search('default', $request->lang)] : '';
        $template->footer_text = $request->footer_text[array_search('default', $request->lang)];
        $template->copyright_text = $request->copyright_text[array_search('default', $request->lang)];
        $template->background_image = $request->has('background_image') ? Helpers::update('email_template/', $template->background_image, 'png', $request->file('background_image')) : $template->background_image;
        $template->image = $request->has('image') ? Helpers::update('email_template/', $template->image, 'png', $request->file('image')) : $template->image;
        $template->logo = $request->has('logo') ? Helpers::update('email_template/', $template->logo, 'png', $request->file('logo')) : $template->logo;
        $template->icon = $request->has('icon') ? Helpers::update('email_template/', $template->icon, 'png', $request->file('icon')) : $template->icon;
        $template->email_type = $email_type;
        $template->type = $type;
        $template->button_url = $request->button_url ?? '';
        $template->email_template = $request->email_template;
        $template->privacy = $request->privacy ? '1' : 0;
        $template->refund = $request->refund ? '1' : 0;
        $template->cancelation = $request->cancelation ? '1' : 0;
        $template->contact = $request->contact ? '1' : 0;
        $template->facebook = $request->facebook ? '1' : 0;
        $template->instagram = $request->instagram ? '1' : 0;
        $template->twitter = $request->twitter ? '1' : 0;
        $template->linkedin = $request->linkedin ? '1' : 0;
        $template->pinterest = $request->pinterest ? '1' : 0;
        $template->save();

        Helpers::add_or_update_translations(request: $request, key_data: 'title', name_field: 'title', model_name: 'EmailTemplate', data_id: $template->id, data_value: $template->title);
        Helpers::add_or_update_translations(request: $request, key_data: 'body', name_field: 'body', model_name: 'EmailTemplate', data_id: $template->id, data_value: $template->body);
        if ($request?->body_2) {
            Helpers::add_or_update_translations(request: $request, key_data: 'body_2', name_field: 'body_2', model_name: 'EmailTemplate', data_id: $template->id, data_value: $template->body_2);
        }
        if ($request->button_name) {
            Helpers::add_or_update_translations(request: $request, key_data: 'button_name', name_field: 'button_name', model_name: 'EmailTemplate', data_id: $template->id, data_value: $template->button_name);
        }
        Helpers::add_or_update_translations(request: $request, key_data: 'footer_text', name_field: 'footer_text', model_name: 'EmailTemplate', data_id: $template->id, data_value: $template->footer_text);
        Helpers::add_or_update_translations(request: $request, key_data: 'copyright_text', name_field: 'copyright_text', model_name: 'EmailTemplate', data_id: $template->id, data_value: $template->copyright_text);

        Toastr::success(translate('messages.template_added_successfully'));

        return back();
    }

    public function update_email_status(Request $request, $type, $tab, $status)
    {
        if (getEnvMode() == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));

            return back();
        }

        $allowed = [
            'place-order', 'forgot-password', 'restaurant-registration', 'dm-registration',
            'registration', 'approve', 'deny', 'withdraw-request', 'withdraw-approve',
            'withdraw-deny', 'campaign-request', 'campaign-approve', 'campaign-deny',
            'bogo-request', 'bogo-approve', 'bogo-deny',
            'happy-hour-request', 'happy-hour-approve', 'happy-hour-deny',
            'refund-request', 'login', 'suspend', 'unsuspend', 'cash-collect',
            'registration-otp', 'login-otp', 'order-verification', 'refund-request-deny',
            'add-fund', 'refund-order', 'offline-payment-deny', 'offline-payment-approve',
            'pos-registration', 'new-advertisement', 'update-advertisement',
            'advertisement-resume', 'advertisement-approved', 'advertisement-create',
            'advertisement-pause', 'advertisement-deny', 'subscription-successful',
            'subscription-renew', 'subscription-shift', 'subscription-cancel',
            'subscription-deadline', 'subscription-plan_upadte', 'profile-verification',
        ];

        if (in_array($tab, $allowed, true)) {
            $prefix = $tab == 'forgot-password' ? 'forget_password' : str_replace('-', '_', $tab);
            Helpers::businessUpdateOrInsert(['key' => $prefix.'_mail_status_'.$type], [
                'value' => $status,
            ]);
        }

        Toastr::success(translate('messages.email_status_updated'));

        return back();
    }

    public function login_settings()
    {
        $data = array_column(BusinessSetting::whereIn('key', [
            'manual_login_status',
            'otp_login_status',
            'social_login_status',
            'google_login_status',
            'facebook_login_status',
            'apple_login_status',
            'email_verification_status',
            'phone_verification_status',
            'send_otp_via',
        ])->get(['key', 'value'])->toArray(), 'value', 'key');

                $social_login = [];
        foreach (['social_login', 'apple_login'] as $login_key) {
            foreach (Helpers::get_business_settings($login_key) ?? [] as $social) {
                $social_login[$social['login_medium']] = (bool) $social['status'];
            }
        }

        $google_login_status = (bool) ($social_login['google'] ?? false);
        $facebook_login_status = (bool) ($social_login['facebook'] ?? false);
        $apple_login_status = (bool) ($social_login['apple'] ?? false);
        $is_firebase_active = (bool) (Helpers::get_business_settings('firebase_otp_verification') ?? 0);
        $is_sms_active = Setting::where('is_active', 1)
            ->whereJsonContains('live_values->status', '1')
            ->where('settings_type', 'sms_config')
            ->exists();
        $is_mail_active = (bool) config('mail.status');

        return view('admin-views.login-setup.login_page', compact(
            'data',
            'google_login_status',
            'facebook_login_status',
            'apple_login_status',
            'is_firebase_active',
            'is_sms_active',
            'is_mail_active'
        ));
    }

    public function login_settings_update(Request $request)
    {
        $social_login = [];
        foreach (['social_login', 'apple_login'] as $login_key) {
            foreach (Helpers::get_business_settings($login_key) ?? [] as $social) {
                $social_login[$social['login_medium']] = (bool) $social['status'];
            }
        }

        $manual_login_status = (bool) $request['manual_login_status'];
        $otp_login_status = (bool) $request['otp_login_status'];
        $social_login_status = (bool) $request['social_login_status'];
        $google_login_status = (bool) $request['google_login_status'];
        $facebook_login_status = (bool) $request['facebook_login_status'];
        $apple_login_status = (bool) $request['apple_login_status'];
        $email_verification_status = (bool) $request['email_verification_status'];
        $phone_verification_status = (bool) $request['phone_verification_status'];

        $is_firebase_active = Helpers::get_business_settings('firebase_otp_verification') ?? 0;

        $is_sms_active = Setting::where('is_active', 1)->whereJsonContains('live_values->status', '1')->where('settings_type', 'sms_config')->exists();

        $is_mail_active = config('mail.status');

        $response = match (true) {
            ! $manual_login_status && ! $otp_login_status && ! $social_login_status => (function () {
                Session::flash('select-one-method', true);

                return back();
            })(),
            $otp_login_status && ! $is_sms_active && ! $is_firebase_active => (function () {
                Session::flash('sms-config', true);

                return back();
            })(),
            $otp_login_status && ! $phone_verification_status => (function () {
                Toastr::error(translate('messages.Phone verification is required when OTP login is enabled.'));

                return back()->withInput();
            })(),
            ! $manual_login_status && ! $otp_login_status && $social_login_status && ! $google_login_status && ! $facebook_login_status => (function () {
                Session::flash('select-one-method-android', true);

                return back();
            })(),
            $social_login_status && ! $google_login_status && ! $facebook_login_status && ! $apple_login_status => (function () {
                Session::flash('select-one-method-social-login', true);

                return back();
            })(),
            (($social_login_status && $google_login_status && ! isset($social_login['google'])) || ($social_login_status && $google_login_status && isset($social_login['google']) && ! $social_login['google'])) => (function () {
                Session::flash('setup-google', true);

                return back();
            })(),
            (($social_login_status && $facebook_login_status && ! isset($social_login['facebook'])) || ($social_login_status && $facebook_login_status && isset($social_login['facebook']) && ! $social_login['facebook'])) => (function () {
                Session::flash('setup-facebook', true);

                return back();
            })(),
            (($social_login_status && $apple_login_status && ! isset($social_login['apple'])) || ($social_login_status && $apple_login_status && isset($social_login['apple']) && ! $social_login['apple'])) => (function () {
                Session::flash('setup-apple', true);

                return back();
            })(),
            $phone_verification_status && ! $is_sms_active && ! $is_firebase_active => (function () {
                Session::flash('sms-config-verification', true);

                return back();
            })(),
            $email_verification_status && ! $is_mail_active => (function () {
                Session::flash('mail-config-verification', true);

                return back();
            })(),
            default => null,
        };

        if ($response) {
            return $response;
        }

        $settings = [
            'manual_login_status' => $manual_login_status ? 1 : 0,
            'otp_login_status' => $otp_login_status ? 1 : 0,
            'social_login_status' => $social_login_status ? 1 : 0,
            'google_login_status' => $social_login_status ? ($google_login_status ? 1 : 0) : 0,
            'facebook_login_status' => $social_login_status ? ($facebook_login_status ? 1 : 0) : 0,
            'apple_login_status' => $social_login_status ? ($apple_login_status ? 1 : 0) : 0,
            'email_verification_status' => $email_verification_status ? 1 : 0,
            'phone_verification_status' => $phone_verification_status ? 1 : 0,
            'send_otp_via' => $request['send_otp_via'] ?? 'sms',
        ];

        foreach ($settings as $key => $value) {
            Helpers::businessUpdateOrInsert(['key' => $key], [
                'value' => $value,
            ]);
        }

        Toastr::success(translate('messages.login_settings_data_updated_successfully'));

        return back();
    }

    public function firebase_otp_index(Request $request)
    {
        $is_sms_active = Setting::where('is_active', 1)->whereJsonContains('live_values->status', '1')->where('settings_type', 'sms_config')
            ->exists();
        $is_mail_active = config('mail.status');

        return view('admin-views.business-settings.firebase-otp-index', compact('is_sms_active', 'is_mail_active'));
    }

    public function firebase_otp_update(Request $request)
    {
        $login_setup_status = Helpers::get_business_settings('otp_login_status') ?? 0;
        $phone_verification_status = Helpers::get_business_settings('phone_verification_status') ?? 0;
        $is_sms_active = Setting::where('is_active', 1)->whereJsonContains('live_values->status', '1')->where('settings_type', 'sms_config')
            ->exists();
        if (! $is_sms_active && $login_setup_status && ($request['firebase_otp_verification'] == 0)) {
            Toastr::warning(translate('otp_login_status_is_enabled_in_login_setup._First_disable_from_login_setup.'));

            return redirect()->back();
        }
        if (! $is_sms_active && $phone_verification_status && ($request['firebase_otp_verification'] == 0)) {
            Toastr::warning(translate('phone_verification_status_is_enabled_in_login_setup._First_disable_from_login_setup.'));

            return redirect()->back();
        }
        Helpers::businessUpdateOrInsert(['key' => 'firebase_otp_verification'], [
            'value' => $request['firebase_otp_verification'] ?? 0,
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'firebase_web_api_key'], [
            'value' => $request['firebase_web_api_key'],
        ]);

        Toastr::success(translate('messages.updated_successfully'));

        return back();
    }

    public function login_url_page()
    {
        $data = array_column(DataSetting::whereIn('key', [
            'restaurant_employee_login_url',
            'restaurant_login_url',
            'admin_employee_login_url',
            'admin_login_url',
        ])->get(['key', 'value'])->toArray(), 'value', 'key');

        return view('admin-views.login-setup.login_setup', compact('data'));
    }

    public function login_url_page_update(Request $request)
    {

        $request->validate([
            'type' => 'required',
            'admin_login_url' => 'nullable|regex:/^[a-zA-Z0-9\-\_]+$/u|unique:data_settings,value',
            'admin_employee_login_url' => 'nullable|regex:/^[a-zA-Z0-9\-\_]+$/u|unique:data_settings,value',
            'restaurant_login_url' => 'nullable|regex:/^[a-zA-Z0-9\-\_]+$/u|unique:data_settings,value',
            'restaurant_employee_login_url' => 'nullable|regex:/^[a-zA-Z0-9\-\_]+$/u|unique:data_settings,value',
        ]);

        if ($request->type == 'admin') {
            Helpers::dataUpdateOrInsert(['key' => 'admin_login_url', 'type' => 'login_admin'], [
                'value' => $request->admin_login_url,
            ]);
            // Config::set('admin_login_url', $request->admin_login_url);
        } elseif ($request->type == 'admin_employee') {
            Helpers::dataUpdateOrInsert(['key' => 'admin_employee_login_url', 'type' => 'login_admin_employee'], [
                'value' => $request->admin_employee_login_url,
            ]);
        } elseif ($request->type == 'restaurant') {
            Helpers::dataUpdateOrInsert(['key' => 'restaurant_login_url', 'type' => 'login_restaurant'], [
                'value' => $request->restaurant_login_url,
            ]);
        } elseif ($request->type == 'restaurant_employee') {
            Helpers::dataUpdateOrInsert(['key' => 'restaurant_employee_login_url', 'type' => 'login_restaurant_employee'], [
                'value' => $request->restaurant_employee_login_url,
            ]);
        }
        Toastr::success(translate('messages.update_successfull'));

        return back();
    }

    public function remove_image(Request $request)
    {

        $request->validate([
            'model_name' => 'required',
            'id' => 'required',
            'image_path' => 'required',
            'field_name' => 'required',
        ]);
        try {

            $model_name = $request->model_name;
            $model = app("\\App\\Models\\{$model_name}");
            $data = $model->where('id', $request->id)->first();
            // dd($request->image_path);

            $data_value = $data?->{$request->field_name};
            if (! $data_value) {
                $data_value = json_decode($data?->value, true);
            }

            //         dd($data_value);

            if ($request?->json == 1) {
                Helpers::check_and_delete($request->image_path.'/', $data_value[$request->field_name]);
                $data_value[$request->field_name] = null;
                $data->value = json_encode($data_value);
            } else {
                Helpers::check_and_delete($request->image_path.'/', $data_value);
                $data->{$request->field_name} = null;
            }

            $data?->save();

        } catch (\Throwable $th) {
            Toastr::error($th->getMessage().'Line....'.$th->getLine());

            return back();
        }
        Toastr::success(translate('messages.Image_removed_successfully'));

        return back();
    }

    public function notification_setup(Request $request)
    {

        if (NotificationSetting::count() == 0) {
            Helpers::notificationDataSetup();
        }
        Helpers::addNewAdminNotificationSetupDataSetup();
        $data = NotificationSetting::when($request?->type == null || $request?->type == 'admin', function ($query) {
            $query->where('type', 'admin');
        })
            ->when($request?->type == 'restaurant', function ($query) {
                $query->where('type', 'restaurant');
            })
            ->when($request?->type == 'customers', function ($query) {
                $query->where('type', 'customer');
            })
            ->when($request?->type == 'deliveryman', function ($query) {
                $query->where('type', 'deliveryman');
            })->get();

        $business_name = BusinessSetting::where('key', 'business_name')->first()?->value;

        return view('admin-views.business-settings.notification.notification_setup', compact('business_name', 'data'));

    }

    public function notification_status_change($key, $user_type, $type)
    {
        $data = NotificationSetting::where('type', $user_type)->where('key', $key)->first();
        if (! $data) {
            Toastr::error(translate('messages.Notification_settings_not_found'));

            return back();
        }
        if ($type == 'Mail') {
            $data->mail_status = $data->mail_status == 'active' ? 'inactive' : 'active';
        } elseif ($type == 'push_notification') {
            $data->push_notification_status = $data->push_notification_status == 'active' ? 'inactive' : 'active';
        } elseif ($type == 'SMS') {
            $data->sms_status = $data->sms_status == 'active' ? 'inactive' : 'active';
        }
        $data?->save();

        Toastr::success(translate('messages.Notification_settings_updated'));

        return back();
    }

    public function invoiceSetup(Request $request)
    {
        if ($request->isMethod('post')) {
            $settings = [
                'invoice_logo_status' => $request->invoice_logo_status ? 1 : 0,
                'invoice_logo_type' => $request->invoice_logo_type,
                'business_identity_status' => $request->business_identity_status ? 1 : 0,
                'business_identity_type' => $request->business_identity_type,
                'tax_number' => $request->tax_number,
                'bin_number' => $request->bin_number,
                'musak_number' => $request->musak_number,
                'terms&condition_status' => $request->input('terms&condition_status') ? 1 : 0,
                'terms&condition_content' => $request->input('terms&condition_content'),
                'copyright_text_status' => $request->copyright_text_status ? 1 : 0,
                'copyright_text_content' => $request->copyright_text_content,
            ];

            foreach ($settings as $key => $value) {
                Helpers::dataUpdateOrInsert(['key' => $key, 'type' => 'invoice_settings'], [
                    'value' => $value,
                ]);
            }

            if ($request->invoice_logo_type == 'upload_new_logo' && $request->has('invoice_logo')) {
                $image = DataSetting::where('key', 'invoice_logo')->where('type', 'invoice_settings')->first();
                $imageName = Helpers::update('business/', $image?->value, 'png', $request->file('invoice_logo'));
                Helpers::dataUpdateOrInsert(['key' => 'invoice_logo', 'type' => 'invoice_settings'], [
                    'value' => $imageName,
                ]);
            }

            Toastr::success(translate('messages.invoice_settings_updated_successfully'));

            return back();
        }

        $data = array_column(DataSetting::whereIn('key', [
            'invoice_logo_status',
            'invoice_logo_type',
            'invoice_logo',
            'business_identity_status',
            'business_identity_type',
            'tax_number',
            'bin_number',
            'musak_number',
            'terms&condition_status',
            'terms&condition_content',
            'copyright_text_status',
            'copyright_text_content',
        ])->where('type', 'invoice_settings')->get(['key', 'value'])->toArray(), 'value', 'key');

        return view('admin-views.business-settings.invoice-setup.index', compact('data'));
    }
}
