<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Mail\ContactMail;
use App\Models\DataSetting;
use App\Models\AdminFeature;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\ContactMessage;
use Illuminate\Support\Carbon;
use App\Models\BusinessSetting;
use App\Models\AdminTestimonial;
use Gregwar\Captcha\CaptchaBuilder;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\SubscriptionTransaction;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $datas =  DataSetting::with('translations')->where('type','admin_landing_page')->get();
        $data = [];
        foreach ($datas as $key => $value) {
            if(count($value->translations)>0){
                $cred = [
                    $value->key => $value->translations[0]['value'],
                ];
                array_push($data,$cred);
            }else{
                $cred = [
                    $value->key => $value->value,
                ];
                array_push($data,$cred);
            }
            if(count($value->storage)>0){
                $cred = [
                    $value->key.'_storage' => $value->storage[0]['value'],
                ];
                array_push($data,$cred);
            }else{
                $cred = [
                    $value->key.'_storage' => 'public',
                ];
                array_push($data,$cred);
            }
        }
        $settings = [];
        foreach($data as $single_data){
            foreach($single_data as $key=>$single_value){
                $settings[$key] = $single_value;
            }
        }


        $features = AdminFeature::latest()->where('status',1)->get()->toArray();
        $testimonials = AdminTestimonial::latest()->where('status',1)->get()->toArray();

        $header_floating_content= json_decode($settings['header_floating_content'] ?? null, true);
        $header_image_content= json_decode($settings['header_image_content'] ?? null, true);

        $zones= Zone::effective()->get(['id','name','display_name']);

        $landing_data = [
            'header_title'=>  $settings['header_title'] ??  'Why Stay Hungry !' ,
            'header_sub_title'=> $settings['header_sub_title'] ?? 'When you can order from' ,
            'header_tag_line'=> $settings['header_tag_line'] ?? 'Get Offers' ,
            'header_app_button_name'=> $settings['header_app_button_name'] ?? 'Order now' ,
            'header_app_button_status'=> $settings['header_app_button_status'] ?? 0 ,
            'header_button_redirect_link'=>   $settings['header_button_content'] ?? null ,

            'header_floating_total_order'=>   $header_floating_content['header_floating_total_order'] ?? null ,
            'header_floating_total_user'=>   $header_floating_content['header_floating_total_user'] ?? null ,
            'header_floating_total_reviews'=>   $header_floating_content['header_floating_total_reviews'] ?? null ,

            'header_content_image'=>   $header_image_content['header_content_image'] ?? 'double_screen_image.png' ,
            'header_content_image_full_url'=>   Helpers::get_full_url('header_image',$header_image_content['header_content_image'] ?? 'double_screen_image.png',$header_image_content['header_content_image_storage']??'public') ,
            'header_bg_image'=>   $header_image_content['header_bg_image'] ?? null ,
            'header_bg_image_full_url'=>   Helpers::get_full_url('header_image',$header_image_content['header_bg_image'] ?? null,$header_image_content['header_bg_image_storage']??'public') ,

            'about_us_title'=>   $settings['about_us_title'] ?? null ,
            'about_us_sub_title'=>   $settings['about_us_sub_title'] ?? null ,
            'about_us_text'=>   $settings['about_us_text'] ?? null ,
            'about_us_app_button_name'=>   $settings['about_us_app_button_name'] ?? 'More' ,
            'about_us_app_button_status'=>   $settings['about_us_app_button_status'] ?? 0 ,

            'about_us_redirect_link'=>   $settings['about_us_button_content'] ?? null ,
            'about_us_image_content'=>   $settings['about_us_image_content'] ??  null ,
            'about_us_image_content_full_url'=>   Helpers::get_full_url('about_us_image',$settings['about_us_image_content'] ??  null,$settings['about_us_image_content_storage']) ,

            'why_choose_us_title'=>   $settings['why_choose_us_title']?? null ,
            'why_choose_us_sub_title'=>   $settings['why_choose_us_sub_title'] ??  null ,
            'why_choose_us_image_1'=>   $settings['why_choose_us_image_1'] ??  null ,
            'why_choose_us_image_1_full_url'=>   Helpers::get_full_url('why_choose_us_image',$settings['why_choose_us_image_1'] ??  null,$settings['why_choose_us_image_1_storage']) ,
            'why_choose_us_title_1'=>   $settings['why_choose_us_title_1'] ??  null ,
            'why_choose_us_title_2'=>   $settings['why_choose_us_title_2'] ??  null ,
            'why_choose_us_image_2'=>   $settings['why_choose_us_image_2'] ??  null ,
            'why_choose_us_image_2_full_url'=>   Helpers::get_full_url('why_choose_us_image',$settings['why_choose_us_image_2'] ??  null,$settings['why_choose_us_image_2_storage']) ,
            'why_choose_us_title_3'=>   $settings['why_choose_us_title_3'] ??  null ,
            'why_choose_us_image_3'=>   $settings['why_choose_us_image_3'] ??  null ,
            'why_choose_us_image_3_full_url'=>   Helpers::get_full_url('why_choose_us_image',$settings['why_choose_us_image_3'] ??  null,$settings['why_choose_us_image_3_storage']) ,
            'why_choose_us_title_4'=>   $settings['why_choose_us_title_4'] ??  null ,
            'why_choose_us_image_4'=>   $settings['why_choose_us_image_4'] ??  null ,
            'why_choose_us_image_4_full_url'=>   Helpers::get_full_url('why_choose_us_image',$settings['why_choose_us_image_4'] ??  null,$settings['why_choose_us_image_4_storage']) ,


            'feature_title'=>   $settings['feature_title'] ??  null ,
            'feature_sub_title'=>   $settings['feature_sub_title'] ??  null ,
            'features'=> $features ?? [] ,

            'services_title'=>   $settings['services_title'] ??  null ,
            'services_sub_title'=>   $settings['services_sub_title'] ??  null ,
            'services_order_title_1'=>   $settings['services_order_title_1'] ??  null ,
            'services_order_title_2'=>   $settings['services_order_title_2'] ??  null ,
            'services_order_description_1'=>   $settings['services_order_description_1'] ??  null ,
            'services_order_description_2'=>   $settings['services_order_description_2'] ??  null ,
            'services_order_button_name'=>   $settings['services_order_button_name'] ??  null ,
            'services_order_button_status'=>   $settings['services_order_button_status'] ??  null ,
            'services_order_button_link'=>   $settings['services_order_button_link'] ??  null ,


            'services_manage_restaurant_title_1'=>   $settings['services_manage_restaurant_title_1'] ??  null ,
            'services_manage_restaurant_title_2'=>   $settings['services_manage_restaurant_title_2'] ??  null ,
            'services_manage_restaurant_description_1'=>   $settings['services_manage_restaurant_description_1'] ??  null ,
            'services_manage_restaurant_description_2'=>   $settings['services_manage_restaurant_description_2'] ??  null ,
            'services_manage_restaurant_button_name'=>   $settings['services_manage_restaurant_button_name'] ??  null ,
            'services_manage_restaurant_button_status'=>   $settings['services_manage_restaurant_button_status'] ??  null ,
            'services_manage_restaurant_button_link'=>   $settings['services_manage_restaurant_button_link'] ??  null ,


            'services_manage_delivery_title_1'=>   $settings['services_manage_delivery_title_1'] ??  null ,
            'services_manage_delivery_title_2'=>   $settings['services_manage_delivery_title_2'] ??  null ,
            'services_manage_delivery_description_1'=>   $settings['services_manage_delivery_description_1'] ??  null ,
            'services_manage_delivery_description_2'=>   $settings['services_manage_delivery_description_2'] ??  null ,
            'services_manage_delivery_button_name'=>   $settings['services_manage_delivery_button_name'] ??  null ,
            'services_manage_delivery_button_status'=>   $settings['services_manage_delivery_button_status'] ??  null ,
            'services_manage_delivery_button_link'=>   $settings['services_manage_delivery_button_link'] ??  null ,

            'testimonial_title'=> $settings['testimonial_title'] ??  null ,
            'testimonials'=> $testimonials ?? [] ,

            'earn_money_title'=>   $settings['earn_money_title'] ??  null ,
            'earn_money_sub_title'=>   $settings['earn_money_sub_title'] ??  null ,
            'earn_money_reg_title'=>   $settings['earn_money_reg_title'] ??  null ,
            'earn_money_restaurant_req_button_name'=>   $settings['earn_money_restaurant_req_button_name'] ??  null ,
            'earn_money_restaurant_req_button_status'=>   $settings['earn_money_restaurant_req_button_status'] ??  null ,
            'earn_money_delivety_man_req_button_name'=>   $settings['earn_money_delivety_man_req_button_name'] ??  null ,
            'earn_money_delivery_man_req_button_status'=>   $settings['earn_money_delivery_man_req_button_status'] ??  0 ,
            'earn_money_reg_image'=>   $settings['earn_money_reg_image'] ??  null ,
            'earn_money_reg_image_full_url'=>   Helpers::get_full_url('earn_money',$settings['earn_money_reg_image'] ??  null,$settings['earn_money_reg_image_storage']) ,

            'earn_money_delivery_req_button_link'=>   $settings['earn_money_delivery_man_req_button_link']??  null ,
            'earn_money_restaurant_req_button_link'=>   $settings['earn_money_restaurant_req_button_link'] ??  null ,

            'business_name' =>  'OORZO',

            'available_zone_status' => (int)((isset($settings['available_zone_status'])) ? $settings['available_zone_status'] : 0),
            'available_zone_title' => (isset($settings['available_zone_title'])) ? $settings['available_zone_title'] : null,
            'available_zone_short_description' => (isset($settings['available_zone_short_description'])) ? $settings['available_zone_short_description'] : null,
            'available_zone_image' => (isset($settings['available_zone_image'])) ? $settings['available_zone_image'] : null,
            'available_zone_image_full_url' => Helpers::get_full_url('available_zone_image', (isset($settings['available_zone_image'])) ? $settings['available_zone_image'] : null, (isset($settings['available_zone_image_storage'])) ? $settings['available_zone_image_storage'] : 'public'),
            'available_zone_list' => $zones ?? [],

        ];

        $landing_data = array_replace($landing_data, [
            'header_app_button_status' => 0,
            'about_us_title' => 'A better way to enjoy your neighborhood',
            'about_us_sub_title' => 'Local favorites, delivered with care',
            'about_us_text' => 'OORZO helps you discover nearby restaurants, explore menus and order the food you love. We bring local kitchens and hungry neighbors together in one easy place.',
            'about_us_app_button_status' => 0,
            'about_us_image_content' => 'oorzo-about-neighborhood.svg',
            'about_us_image_content_full_url' => asset('assets/landing/oorzo/about-neighborhood.svg'),
            'feature_title' => 'Made for everyday cravings',
            'feature_sub_title' => 'Everything you need for a simple, dependable food order.',
            'features' => [
                [
                    'title' => 'Easy ordering',
                    'description' => 'Browse menus and place your order in just a few simple steps.',
                    'image_full_url' => asset('assets/landing/oorzo/feature-order.svg'),
                ],
                [
                    'title' => 'Local favorites',
                    'description' => 'Find neighborhood restaurants and discover something delicious nearby.',
                    'image_full_url' => asset('assets/landing/oorzo/feature-local.svg'),
                ],
                [
                    'title' => 'Doorstep delivery',
                    'description' => 'Follow your order and enjoy your meal when it arrives.',
                    'image_full_url' => asset('assets/landing/oorzo/feature-delivery.svg'),
                ],
            ],
            'services_title' => 'One easy place to order and deliver',
            'services_sub_title' => 'A smoother experience for customers, local restaurants and delivery partners.',
            'services_order_title_1' => 'Find a meal you will love',
            'services_order_description_1' => 'Explore nearby menus, compare dishes and choose what sounds good today.',
            'services_order_title_2' => 'Order with confidence',
            'services_order_description_2' => 'Review your basket and follow order updates from the restaurant to your door.',
            'services_order_button_status' => 0,
            'services_manage_restaurant_title_1' => 'A simpler way to manage orders',
            'services_manage_restaurant_description_1' => 'Local restaurants can receive incoming orders and keep service moving.',
            'services_manage_restaurant_title_2' => 'Keep the details together',
            'services_manage_restaurant_description_2' => 'Menus, order updates and customer requests stay organized in one place.',
            'services_manage_restaurant_button_status' => 0,
            'services_manage_delivery_title_1' => 'Know where your delivery stands',
            'services_manage_delivery_description_1' => 'Order updates help customers and delivery partners stay in sync.',
            'services_manage_delivery_title_2' => 'Made for local delivery',
            'services_manage_delivery_description_2' => 'A clear handoff helps bring each restaurant order to its destination.',
            'services_manage_delivery_button_status' => 0,
            'why_choose_us_title' => 'Good food, closer to home',
            'why_choose_us_sub_title' => 'A local-first experience built around your next favorite meal.',
            'why_choose_us_image_1' => 'choose-easy-order.svg',
            'why_choose_us_image_1_full_url' => asset('assets/landing/oorzo/choose-easy-order.svg'),
            'why_choose_us_title_1' => 'Simple to use',
            'why_choose_us_image_2' => 'choose-local-favorites.svg',
            'why_choose_us_image_2_full_url' => asset('assets/landing/oorzo/choose-local-favorites.svg'),
            'why_choose_us_title_2' => 'Local restaurants',
            'why_choose_us_image_3' => 'choose-delivery-updates.svg',
            'why_choose_us_image_3_full_url' => asset('assets/landing/oorzo/choose-delivery-updates.svg'),
            'why_choose_us_title_3' => 'Order updates',
            'why_choose_us_image_4' => null,
            'earn_money_title' => 'Your next favorite meal is nearby',
            'earn_money_sub_title' => 'Explore local menus, pick what you love and let OORZO bring it to you.',
            'earn_money_reg_title' => null,
            'earn_money_reg_image' => null,
            'earn_money_restaurant_req_button_status' => 0,
            'earn_money_delivery_man_req_button_status' => 0,
            'testimonials' => [],
            'available_zone_status' => 0,
        ]);

        array_walk_recursive($landing_data, static function (&$value, $key): void {
            if (is_string($value) && !preg_match('/(?:url|link|image|storage)/i', (string) $key)) {
                $value = str_ireplace(['stackfood', 'stack food'], 'OORZO', $value);
            }
        });

        foreach ($landing_data as $key => $value) {
            if (!is_string($value) || (!str_ends_with($key, '_link') && !str_ends_with($key, '_redirect_link'))) {
                continue;
            }

            $target = parse_url($value);
            $scheme = is_array($target) ? strtolower($target['scheme'] ?? '') : '';
            $host = is_array($target) ? ($target['host'] ?? null) : null;
            $targetScheme = $scheme !== '' ? $scheme : request()->getScheme();
            $port = is_array($target)
                ? ($target['port'] ?? ($targetScheme === 'https' ? 443 : 80))
                : null;

            if (
                $target === false
                || ($scheme !== '' && !in_array($scheme, ['http', 'https'], true))
                || ($host !== null && strcasecmp(rtrim($host, '.'), rtrim(request()->getHost(), '.')) !== 0)
                || ($host !== null && $port !== null && (int) $port !== request()->getPort())
            ) {
                $landing_data[$key] = null;
            }
        }

        $config = Helpers::get_business_settings('landing_page');
        $landing_integration_type = Helpers::get_business_data('landing_integration_type');
        $redirect_url = Helpers::get_business_data('landing_page_custom_url');

        if(isset($config) && $config){
            $new_user= request()?->new_user ?? null ;
            return view('home',compact('landing_data','new_user'));
        }elseif($landing_integration_type == 'file_upload' && File::exists('resources/views/layouts/landing/custom/index.blade.php')){
            return view('layouts.landing.custom.index');
        }elseif($landing_integration_type == 'url'){
            return redirect($redirect_url);
        }else{
            abort(404);
        }
    }

    public function terms_and_conditions(Request $request)
    {
        $data = self::oorzoTermsAndConditions();
        if ($request->expectsJson()) {
            return response()->json($data);
        }

        $config = Helpers::get_business_settings('landing_page');
        $landing_integration_type = Helpers::get_business_data('landing_integration_type');
        $redirect_url = Helpers::get_business_data('landing_page_custom_url');

        if(isset($config) && $config){
            return view('terms-and-conditions', compact('data'));
        }elseif($landing_integration_type == 'file_upload'){
            return view('layouts.landing.custom.index');
        }elseif($landing_integration_type == 'url'){
            return redirect($redirect_url);
        }else{
            abort(404);
        }
    }

    public function about_us(Request $request)
    {
        $data = self::oorzoAboutUs();
        // $data_title = self::get_settings('about_title');

        if ($request->expectsJson()) {
            return response()->json($data);
        }

        $config = Helpers::get_business_settings('landing_page');
        $landing_integration_type = Helpers::get_business_data('landing_integration_type');
        $redirect_url = Helpers::get_business_data('landing_page_custom_url');

        if(isset($config) && $config){
            return view('about-us', compact('data'));
        }elseif($landing_integration_type == 'file_upload'){
            return view('layouts.landing.custom.index');
        }elseif($landing_integration_type == 'url'){
            return redirect($redirect_url);
        }else{
            abort(404);
        }
    }

    public function contact_us(Request $request)
    {
        if ($request->isMethod('POST')) {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email:filter',
                'message' => 'required',
            ],[
                'name.required' => translate('messages.Name is required!'),
                'email.required' => translate('messages.Email is required!'),
                'email.filter' => translate('messages.Must ba a valid email!'),
                'message.required' => translate('messages.Message is required!'),
            ]);

            $recaptcha = Helpers::get_business_settings('recaptcha');
            if (isset($recaptcha) && $recaptcha['status'] == 1 && !$request?->set_default_captcha) {
                $request->validate([
                    'g-recaptcha-response' => [
                        function ($attribute, $value, $fail) {
                            $secret_key = Helpers::get_business_settings('recaptcha')['secret_key'];
                            $gResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                                'secret' => $secret_key,
                                'response' => $value,
                                'remoteip' => \request()->ip(),
                            ]);

                            if (!$gResponse->successful()) {
                                $fail(translate('ReCaptcha Failed'));
                            }
                        },
                    ],
                ]);
            } else if (strtolower(session('six_captcha')) != strtolower($request->custome_recaptcha)) {
                Toastr::error(translate('messages.ReCAPTCHA Failed'));
                return back();
            }

            $email = Helpers::get_settings('email_address');
            $messageData = [
                'name' => $request->name,
                'email' => $request->email,
                'message' => $request->message,
            ];
            ContactMessage::create($messageData);

            $business_name=Helpers::get_settings('business_name') ?? 'OORZO';
            $subject='Enquiry from '.$business_name;
            try{
                if(config('mail.status')) {
                    Mail::to($email)->send(new ContactMail($messageData,$subject));
                    Toastr::success(translate('messages.Thanks_for_your_enquiry._We_will_get_back_to_you_soon.'));
                }
            }catch(\Exception $exception)
            {
                 Toastr::error(translate('Failed to send mail'));
            }
            return back();
        }


        $config = Helpers::get_business_settings('landing_page');
        $landing_integration_type = Helpers::get_business_data('landing_integration_type');
        $redirect_url = Helpers::get_business_data('landing_page_custom_url');

        $custome_recaptcha = new CaptchaBuilder;
        $custome_recaptcha->build();
        Session::put('six_captcha', $custome_recaptcha->getPhrase());

        if(isset($config) && $config){
            return view('contact-us',compact('custome_recaptcha'));
        }elseif($landing_integration_type == 'file_upload'){
            return view('layouts.landing.custom.index');
        }elseif($landing_integration_type == 'url'){
            return redirect($redirect_url);
        }else{
            abort(404);
        }
    }

    public function privacy_policy(Request $request)
    {
        $data = self::oorzoPrivacyPolicy();
        if ($request->expectsJson()) {
            return response()->json($data);
        }
        $config = Helpers::get_business_settings('landing_page');
        $landing_integration_type = Helpers::get_business_data('landing_integration_type');
        $redirect_url = Helpers::get_business_data('landing_page_custom_url');

        if(isset($config) && $config){
            return view('privacy-policy',compact('data'));
        }elseif($landing_integration_type == 'file_upload'){
            return view('layouts.landing.custom.index');
        }elseif($landing_integration_type == 'url'){
            return redirect($redirect_url);
        }else{
            abort(404);
        }
    }

    public function refund_policy(Request $request)
    {
        $data = self::oorzoRefundPolicy();
        $status = self::get_settings('refund_policy_status');
        if ($request->expectsJson()) {
            return response()->json($data);
        }
        abort_if($status == 0 ,404);

        $config = Helpers::get_business_settings('landing_page');
        $landing_integration_type = Helpers::get_business_data('landing_integration_type');
        $redirect_url = Helpers::get_business_data('landing_page_custom_url');

        if(isset($config) && $config){
            return view('refund_policy',compact('data'));
        }elseif($landing_integration_type == 'file_upload'){
            return view('layouts.landing.custom.index');
        }elseif($landing_integration_type == 'url'){
            return redirect($redirect_url);
        }else{
            abort(404);
        }
    }

    public function shipping_policy(Request $request)
    {
        $data = self::oorzoShippingPolicy();
        $status = self::get_settings('shipping_policy_status');
        if ($request->expectsJson()) {
            return response()->json($data);
        }
        abort_if($status == 0 ,404);

        $config = Helpers::get_business_settings('landing_page');
        $landing_integration_type = Helpers::get_business_data('landing_integration_type');
        $redirect_url = Helpers::get_business_data('landing_page_custom_url');

        if(isset($config) && $config){
            return view('shipping_policy',compact('data'));
        }elseif($landing_integration_type == 'file_upload'){
            return view('layouts.landing.custom.index');
        }elseif($landing_integration_type == 'url'){
            return redirect($redirect_url);
        }else{
            abort(404);
        }
    }

    public function cancellation_policy(Request $request)
    {
        $data = self::oorzoCancellationPolicy();
        $status = self::get_settings('cancellation_policy_status');
        if ($request->expectsJson()) {
            return response()->json($data);
        }
        abort_if($status == 0 ,404);

        $config = Helpers::get_business_settings('landing_page');
        $landing_integration_type = Helpers::get_business_data('landing_integration_type');
        $redirect_url = Helpers::get_business_data('landing_page_custom_url');

        if(isset($config) && $config){
            return view('cancellation_policy',compact('data'));
        }elseif($landing_integration_type == 'file_upload'){
            return view('layouts.landing.custom.index');
        }elseif($landing_integration_type == 'url'){
            return redirect($redirect_url);
        }else{
            abort(404);
        }
    }

    public static function get_settings($name)
    {
        $data = DataSetting::where(['key' => $name])->first()?->value;
        return $data;
    }


    public function lang($local)
    {
        $direction = BusinessSetting::where('key', 'site_direction')->first();
        $direction = $direction->value ?? 'ltr';
        $language = BusinessSetting::where('key', 'system_language')->first();
        foreach (json_decode($language['value'], true) as $key => $data) {
            if ($data['code'] == $local) {
                $direction = isset($data['direction']) ? $data['direction'] : 'ltr';
            }
        }
        session()->forget('landing_language_settings');
        Helpers::landing_language_load();
        session()->put('landing_site_direction', $direction);
        session()->put('landing_local', $local);
        return redirect()->back();
    }
    public static function get_settings_localization($name,$lang)
    {
        $config = null;
        $data = DataSetting::withoutGlobalScope('translate')->with(['translations' => function ($query) use ($lang) {
            return $query->where('locale', $lang);
        }])->where(['key' => $name])->first();
        if($data && count($data->translations)>0){
            $data = $data->translations[0]['value'];
        }else{
            $data = $data ? $data->value: '';
        }
        return $data;
    }

    public function maintenanceMode(){

        $maintenance = Cache::get('maintenance');


        if(!Cache::has('maintenance') ||  $maintenance['restaurant_panel'] == false  ){
            return to_route('home');
        }

        elseif (isset($maintenance['start_date']) && isset($maintenance['end_date'])) {
            $start = Carbon::parse($maintenance['start_date']);
            $end = Carbon::parse($maintenance['end_date']);
            $today = Carbon::now();
            if($today->gt($end)){
                return to_route('home');
            }
        }

        $maintenance_mode_data=   \App\Models\DataSetting::where('type','maintenance_mode')->whereIn('key' ,['maintenance_message_setup'])->pluck('value','key')
        ->map(function ($value) {
            return json_decode($value, true);
        })
        ->toArray();

                $selectedMaintenanceMessage     = data_get($maintenance_mode_data,'maintenance_message_setup',[]);


        $email = Helpers::get_business_data('email_address');
        $phone = Helpers::get_business_data('phone');


        return view('maintenance-mode',compact('email','phone','selectedMaintenanceMessage'));
    }

    public function subscription_invoice($id){

        $id= base64_decode($id);
        $BusinessData= ['admin_commission' ,'business_name','address','phone','logo','email_address'];
        $transaction= SubscriptionTransaction::with(['restaurant.vendor','package:id,package_name,price'])->findOrFail($id);
        $BusinessData=BusinessSetting::whereIn('key', $BusinessData)->pluck('value' ,'key') ;
        $logo=BusinessSetting::where('key', "logo")->first() ;
        $mpdf_view = View::make('subscription-invoice', compact('transaction','BusinessData','logo'));
        Helpers::gen_mpdf(view: $mpdf_view,file_prefix: 'Subscription',file_postfix: $id);
        return back();
    }


        public function reloadCaptcha()
    {
        $custome_recaptcha = new CaptchaBuilder;
        $custome_recaptcha->build();
        Session::put('six_captcha', $custome_recaptcha->getPhrase());

        return response()->json([
            'view' => view('auth.custom-captcha', compact('custome_recaptcha'))->render()
        ], 200);
    }

    private static function oorzoAboutUs(): string
    {
        return <<<'HTML'
<p>OORZO makes it easier to enjoy the food and flavors around your neighborhood. Browse nearby restaurant menus, place an order and follow its progress in one convenient experience.</p>
<h2>Made for local food</h2>
<p>We connect customers with local restaurants and delivery partners. Our goal is to make discovering a meal and getting it to your door clear, convenient and dependable.</p>
<h2>Here for your next meal</h2>
<p>Whether you are planning lunch, sharing dinner or craving a quick bite, OORZO helps bring local choices together. Availability, delivery areas and times depend on the restaurants and services operating in your location.</p>
HTML;
    }

    private static function oorzoPrivacyPolicy(): string
    {
        return <<<'HTML'
<div class="alert alert-warning" role="alert"><strong>Draft for review:</strong> This general privacy notice must be checked against OORZO's actual data practices, service providers and local legal requirements before publication.</div>
<p><strong>Last updated:</strong> 10 June 2026.</p>
<p>This notice explains how OORZO ("we", "us" or "our") may collect, use, store and share information when you browse our website, create or use an account, place an order, contact us or otherwise use our services.</p>
<h2>Information we may collect</h2>
<ul>
<li><strong>Account and contact details:</strong> information such as your name, email address, phone number, delivery address and account credentials that you provide.</li>
<li><strong>Orders and support:</strong> order details, delivery instructions, preferences and messages you send to customer support or through our contact forms.</li>
<li><strong>Payment information:</strong> payment status and transaction references. Payment card or account details may be handled by the payment provider you choose; confirm the actual payment flow before publication.</li>
<li><strong>Device and usage information:</strong> technical details such as browser type, device identifiers, approximate location derived from your device or connection, and how you interact with the service. The information collected depends on enabled device permissions and site technologies.</li>
</ul>
<h2>How we may use information</h2>
<p>We may use information to provide and improve the service; process orders and deliveries; communicate order updates; respond to questions; protect accounts, users and the service; prevent misuse; comply with legal obligations; and, where permitted, send service-related or promotional messages. You can opt out of promotional messages using the instructions provided with them.</p>
<h2>When information may be shared</h2>
<p>Information may be shared with the restaurant fulfilling your order, delivery partners involved in delivery, payment providers processing your payment, and service providers helping us operate the website or support customers. We may also disclose information when required by law, to protect rights and safety, or as part of a business transfer. Confirm the actual recipients and contracts before publication.</p>
<h2>Location and cookies</h2>
<p>Some features may use location information to show nearby restaurants or delivery availability, subject to your device and browser permissions. You can manage location access in your device or browser settings. Cookies or similar technologies may be used for essential site functions, preferences, analytics or other purposes depending on the technologies enabled. Update this section to match the site's actual cookie and analytics configuration.</p>
<h2>Retention and security</h2>
<p>We keep information for as long as reasonably necessary for the purposes described above, including to provide the service, resolve disputes, meet legal requirements and enforce our agreements. We use reasonable safeguards designed to protect information, but no online service or storage system can be guaranteed completely secure.</p>
<h2>Your choices and rights</h2>
<p>Depending on where you live, you may have rights to request access to, correction or deletion of certain personal information, or to object to or restrict some processing. You may also manage account details and device permissions through the available service and device settings. Contact us through the website's Contact Us page to make a privacy request. We may need to verify your identity and may retain information where the law allows or requires it.</p>
<h2>Children's privacy</h2>
<p>OORZO is not intended for children who are not permitted to use the service under the laws that apply to them. If you believe a child has provided personal information inappropriately, contact us through the Contact Us page.</p>
<h2>Third-party services</h2>
<p>Our service may rely on third-party providers or contain links and integrations operated by others. Their privacy practices are governed by their own notices. Review and identify the providers actually used by OORZO before publishing this notice.</p>
<h2>Changes to this notice</h2>
<p>We may update this notice as our practices or legal requirements change. We will publish the revised notice on this page and update its review date. Where required, we will provide additional notice or obtain consent.</p>
<h2>Contact</h2>
<p>For questions or privacy requests, use the Contact Us page on this website.</p>
HTML;
    }

    private static function oorzoTermsAndConditions(): string
    {
        return <<<'HTML'
<div class="alert alert-warning" role="alert"><strong>Draft for review:</strong> Confirm these terms with OORZO's actual operating model and local legal requirements before publication.</div>
<p>These terms govern your access to and use of the OORZO website and services. By using the service, you agree to these terms. If you do not agree, do not use the service.</p>
<h2>Using OORZO</h2>
<p>You must provide accurate information, keep your account credentials secure and use the service only for lawful purposes. You are responsible for activity carried out through your account. We may suspend access where reasonably necessary to protect users, restaurants or the service, or to comply with law.</p>
<h2>Orders, prices and delivery</h2>
<p>Restaurants are responsible for the products, menu descriptions, availability and preparation of their food. Prices, fees, taxes, estimated delivery times and delivery availability should be reviewed at checkout before an order is submitted. An order is subject to acceptance and availability. Delivery estimates are not guarantees and may change because of circumstances outside our control.</p>
<h2>Payments, changes and cancellations</h2>
<p>Available payment methods and any applicable charges are shown during checkout. Any request to change or cancel an order may depend on its preparation or delivery status and the policies shown at the time. Refunds, cancellations and order issues are handled under the applicable on-site policies and mandatory local consumer protections.</p>
<h2>Restaurants and delivery partners</h2>
<p>Restaurants and delivery partners may be independent providers. Their availability and participation can vary by location. Nothing in these terms removes rights you may have directly against a provider under applicable law.</p>
<h2>Acceptable use and content</h2>
<p>Do not misuse the service, interfere with its operation, submit unlawful or misleading content, or attempt unauthorized access. Content you submit must be accurate and must not infringe another person's rights. You retain rights in your content and allow OORZO to use it as reasonably needed to operate and support the service.</p>
<h2>Availability and liability</h2>
<p>We aim to keep the service useful and available, but features may change or be interrupted. To the extent permitted by applicable law, the service is provided without guarantees that it will always be uninterrupted or error-free. Nothing in these terms excludes liability or consumer rights that cannot lawfully be excluded or limited.</p>
<h2>Changes and contact</h2>
<p>We may update these terms from time to time by publishing a revised version on this page. Continued use after an update takes effect means you accept the updated terms where permitted by law. For questions, use the Contact Us page on this website.</p>
HTML;
    }

    private static function oorzoRefundPolicy(): string
    {
        return <<<'HTML'
<div class="alert alert-warning" role="alert"><strong>Draft for review:</strong> Set OORZO's actual refund rules, timelines and payment-provider process here before publication.</div>
<p>If there is a problem with an order, contact OORZO through the Contact Us page as soon as possible and include the order details and a description of the issue. We may ask for information needed to review the request.</p>
<h2>Review and resolution</h2>
<p>Refund eligibility and available remedies depend on the circumstances, the restaurant or delivery service involved, the payment method, the policies presented when the order was placed and applicable consumer law. If a refund is approved, its method and timing may depend on the payment provider. We will communicate the outcome through the contact details associated with the request.</p>
<h2>Legal rights</h2>
<p>This policy does not limit rights or remedies that cannot be excluded under applicable law. Please review the cancellation and order terms shown in the service when placing an order.</p>
HTML;
    }

    private static function oorzoShippingPolicy(): string
    {
        return <<<'HTML'
<div class="alert alert-warning" role="alert"><strong>Draft for review:</strong> Confirm delivery areas, fees, operating hours and estimated-time practices before publication.</div>
<p>Delivery availability depends on your address, the restaurant, operating hours and delivery-partner availability. Enter your delivery location in the service to see whether delivery is available for an order.</p>
<h2>Delivery estimates and fees</h2>
<p>Any delivery estimate and applicable fee are shown during the ordering process where available. Estimates are not guaranteed and may change due to restaurant preparation, traffic, weather, demand or other circumstances. Confirm the final delivery fee and address before submitting your order.</p>
<h2>Receiving an order</h2>
<p>Please provide complete delivery instructions and make sure someone can receive the order at the specified location. If you cannot receive an order or your delivery is delayed, use the order support options in the service or contact us through the Contact Us page.</p>
HTML;
    }

    private static function oorzoCancellationPolicy(): string
    {
        return <<<'HTML'
<div class="alert alert-warning" role="alert"><strong>Draft for review:</strong> Confirm cancellation windows, fees and handling with OORZO's actual restaurant and delivery operations before publication.</div>
<p>Orders may begin preparation soon after they are submitted. If you need to request a change or cancellation, contact support through the service as soon as possible and provide your order details.</p>
<h2>Requests and refunds</h2>
<p>A cancellation request may not be possible after a restaurant has accepted or started preparing an order, or after delivery is underway. Any applicable charges, refund or other resolution will depend on the order's status, the policies shown when you placed it and applicable law. We will let you know the outcome through the available support channel.</p>
<h2>Legal rights</h2>
<p>Nothing in this policy limits consumer rights or remedies that cannot lawfully be excluded.</p>
HTML;
    }

}
