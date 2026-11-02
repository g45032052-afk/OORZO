<?php

namespace App\Http\Controllers\Admin;

use App\Models\Campaign;
use App\Models\Restaurant;
use Illuminate\Support\Str;
use App\Models\ItemCampaign;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Exports\FoodCampaignExport;
use App\Exports\BasicCampaignExport;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Exports\FoodCampaignOrderListExport;
use App\Models\Allergy;
use App\Models\Nutrition;

class CampaignController extends Controller
{
    function index($type)
    {
        $taxData = Helpers::getTaxSystemType();
        $productWiseTax = $taxData['productWiseTax'];
        $taxVats = $taxData['taxVats'];
        return view('admin-views.campaign.'.$type.'.index', compact('productWiseTax', 'taxVats'));
    }

    function list($type)
    {
         $relationships = [
            'translations' => 'value',
        ];

        $key = explode(' ', request()?->search);
        if($type=='basic')
        {
            $campaigns=Campaign::search(keywords:request()?->search, mainCol: ['title','description'], relations: $relationships)
            ->latest()
            ->paginate(config('default_pagination'));
        }
        else{
            $campaigns=ItemCampaign::search(keywords:request()?->search, mainCol: ['title','description'], relations: $relationships)
            ->latest()
            ->paginate(config('default_pagination'));
        }
        $taxData = Helpers::getTaxSystemType();
        $productWiseTax = $taxData['productWiseTax'];
        $taxVats = $taxData['taxVats'];

        return view('admin-views.campaign.'.$type.'.list', compact('campaigns','productWiseTax', 'taxVats'));
    }

    public function storeBasic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:campaigns|max:191',
            'description'=>'max:1000',
            'image' => 'required|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'title.0' => 'required',
            'description.0' => 'required',
            'end_time' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->start_date == $request->end_date && strtotime($value) <= strtotime($request->start_time)) {
                        $fail('The end time must be after the start time if the start and end dates are the same.');
                    }
                },
            ],
        ],[
            'title.0.required'=>translate('default_title_is_required'),
            'description.0.required'=>translate('default_description_is_required'),
        ]);



        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $campaign = new Campaign;
        $campaign->title = $request->title[array_search('default', $request->lang)];
        $campaign->description = $request->description[array_search('default', $request->lang)];
        $campaign->image = Helpers::upload(dir: 'campaign/',format: 'png', image: $request->file('image'));
        $campaign->start_date = $request->start_date;
        $campaign->end_date = $request->end_date;
        $campaign->start_time = $request->start_time;
        $campaign->end_time = $request->end_time;
        $campaign->save();

        Helpers::add_or_update_translations(request: $request, key_data: 'title', name_field: 'title', model_name: 'Campaign', data_id: $campaign->id, data_value: $campaign->title);
        Helpers::add_or_update_translations(request: $request, key_data: 'description', name_field: 'description', model_name: 'Campaign', data_id: $campaign->id, data_value: $campaign->description);

        return response()->json([], 200);
    }

    public function update(Request $request, Campaign $campaign)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'required|max:191',
            'description'=>'max:1000',
            'image' => 'nullable|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'title.0' => 'required',
            'description.0' => 'required',
            'end_time' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->start_date == $request->end_date && strtotime($value) <= strtotime($request->start_time)) {
                        $fail('The end time must be after the start time if the start and end dates are the same.');
                    }
                },
            ],
        ],[
            'title.0.required'=>translate('default_title_is_required'),
            'description.0.required'=>translate('default_description_is_required'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }
        $slug = Str::slug($campaign->title);
        $campaign->slug = $campaign->slug ?: "{$slug}-{$campaign->id}";
        $campaign->title = $request->title[array_search('default', $request->lang)];
        $campaign->description = $request->description[array_search('default', $request->lang)];
        $campaign->image = $request->has('image') ? Helpers::update(dir:'campaign/',old_image: $campaign->image, format:'png', image:$request->file('image')) : $campaign->image;;
        $campaign->start_date = $request->start_date;
        $campaign->end_date = $request->end_date;
        $campaign->start_time = $request->start_time;
        $campaign->end_time = $request->end_time;
        $campaign->save();


        Helpers::add_or_update_translations(request: $request, key_data: 'title', name_field: 'title', model_name: 'Campaign', data_id: $campaign->id, data_value: $campaign->title);
        Helpers::add_or_update_translations(request: $request, key_data: 'description', name_field: 'description', model_name: 'Campaign', data_id: $campaign->id, data_value: $campaign->description);

        return response()->json([], 200);
    }

    public function storeItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:191|unique:item_campaigns',
            'image' => 'required|image|mimes:' . IMAGE_FORMAT_FOR_VALIDATION . '|max:2048',
            'category_id' => 'required',
            'price' => 'required|numeric|between:0.01,999999999999.99',
            'restaurant_id' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'start_date' => 'required',
            'veg' => 'required',
            'description'=>'max:1000',
            'title.0' => 'required',
            'description.0' => 'required',
        ], [
            'category_id.required' => translate('messages.select_category'),
            'title.0.required'=>translate('default_title_is_required'),
            'description.0.required'=>translate('default_description_is_required'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
        }

        if ($request['price'] <= $dis || $validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $campaign = new ItemCampaign;

        $category = [];
        if ($request->category_id != null) {
            $category[] = [
                'id' => $request->category_id,
                'position' => 1,
            ];
        }
        if ($request->sub_category_id != null) {
            $category[] = [
                'id' => $request->sub_category_id,
                'position' => 2,
            ];
        }
        if ($request->sub_sub_category_id != null) {
            $category[] = [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ];
        }


         $nutrition_ids = [];
        if ($request->nutritions != null) {
            $nutritions = $request->nutritions;
        }
        if (isset($nutritions)) {
            foreach ($nutritions as $key => $value) {
                $nutrition = Nutrition::firstOrNew(
                    ['nutrition' => $value]
                );
                $nutrition->save();
                array_push($nutrition_ids, $nutrition->id);
            }
        }
        $allergy_ids = [];
        if ($request->allergies != null) {
            $allergies = $request->allergies;
        }
        if (isset($allergies)) {
            foreach ($allergies as $key => $value) {
                $allergy = Allergy::firstOrNew(
                    ['allergy' => $value]
                );
                $allergy->save();
                array_push($allergy_ids, $allergy->id);
            }
        }




        $campaign->category_ids = json_encode($category);

        $campaign->choice_options = json_encode([]);
        $variations = [];
        if($request?->options)
        {
            foreach(array_values($request->options) as $key=>$option)
            {

                $temp_variation['name']= $option['name'];
                $temp_variation['type']= $option['type'];
                $temp_variation['min']= $option['min'] ?? 0;
                $temp_variation['max']= $option['max'] ?? 0;
                if($option['min'] > 0 &&  $option['min'] > $option['max']  ){
                    $validator->getMessageBag()->add('name', translate('messages.minimum_value_can_not_be_greater_then_maximum_value'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if(!isset($option['values'])){
                    $validator->getMessageBag()->add('name', translate('messages.please_add_options_for').$option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if($option['max'] > count($option['values'])  ){
                    $validator->getMessageBag()->add('name', translate('messages.please_add_more_options_or_change_the_max_value_for').$option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                $temp_variation['required']= $option['required']??'off';

                $temp_value = [];
                foreach($option['values'] as $value)
                {
                    if(isset($value['label'])){
                        $temp_option['label'] = $value['label'];
                    }
                    $temp_option['optionPrice'] = $value['optionPrice'];
                    $temp_value[] = $temp_option;
                }
                $temp_variation['values']= $temp_value;
                $variations[] = $temp_variation;
            }
        }

        $slug = Str::slug($request->title[array_search('default', $request->lang)]);
        $campaign->slug = $campaign->slug? $campaign->slug :"{$slug}{$campaign->id}";

        $campaign->admin_id = auth('admin')->id();
        $campaign->title = $request->title[array_search('default', $request->lang)];
        $campaign->description = $request->description[array_search('default', $request->lang)];
        $campaign->image = Helpers::upload(dir: 'campaign/', format: 'png', image: $request->file('image'));
        $campaign->start_date = $request->start_date;
        $campaign->end_date = $request->end_date;
        $campaign->start_time = $request->start_time;
        $campaign->end_time = $request->end_time;
        $campaign->variations = json_encode($variations);
        $campaign->price = $request->price;
        $campaign->discount =  $request->discount ?? 0;
        $campaign->discount_type = $request->discount_type;

        $campaign->add_ons = $request->has('addon_ids') ? json_encode($request->addon_ids) : json_encode([]);
        $campaign->restaurant_id = $request->restaurant_id;
        $campaign->maximum_cart_quantity = $request->maximum_cart_quantity;

        $campaign->veg = $request->veg;
        $campaign->save();


        $campaign->nutritions()->sync($nutrition_ids);
        $campaign->allergies()->sync($allergy_ids);


//        FoodSeoData::create(
//            Helpers::getFoodSEOData($request, $campaign->id, null, 'ItemCampaign')
//        );

        Helpers::add_or_update_translations(request: $request, key_data: 'title', name_field: 'title', model_name: 'ItemCampaign', data_id: $campaign->id, data_value: $campaign->title);
        Helpers::add_or_update_translations(request: $request, key_data: 'description', name_field: 'description', model_name: 'ItemCampaign', data_id: $campaign->id, data_value: $campaign->description);
         if (addon_published_status('TaxModule')) {
            $SystemTaxVat = \Modules\TaxModule\Entities\SystemTaxSetup::where('is_active', 1)->where('is_default', 1)->first();
            if ($SystemTaxVat?->tax_type == 'product_wise') {
                foreach ($request['tax_ids'] ?? [] as $tax_id) {
                    \Modules\TaxModule\Entities\Taxable::create(
                        [
                            'taxable_type' => ItemCampaign::class,
                            'taxable_id' => $campaign->id,
                            'system_tax_setup_id' => $SystemTaxVat->id,
                            'tax_id' => $tax_id
                        ],
                    );
                }
            }
        }
        return response()->json([], 200);
    }

    public function updateItem(ItemCampaign $campaign, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:item_campaigns,title,' . $campaign->id,
            'category_id' => 'required',
            'price' => 'required|numeric|between:0.01,999999999999.99',
            'restaurant_id' => 'required',
            'veg' => 'required',
            'image' => 'nullable|image|mimes:' . IMAGE_FORMAT_FOR_VALIDATION . '|max:2048',
            'description.*'=>'max:1000',
            'title.0' => 'required',
            'description.0' => 'required',
        ],[
            'title.0.required'=>translate('default_title_is_required'),
            'description.0.required'=>translate('default_description_is_required'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
        }

        if ($request['price'] <= $dis || $validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

           $nutrition_ids = [];
        if ($request->nutritions != null) {
            $nutritions = $request->nutritions;
        }
        if (isset($nutritions)) {
            foreach ($nutritions as $key => $value) {
                $nutrition = Nutrition::firstOrNew(
                    ['nutrition' => $value]
                );
                $nutrition->save();
                array_push($nutrition_ids, $nutrition->id);
            }
        }
        $allergy_ids = [];
        if ($request->allergies != null) {
            $allergies = $request->allergies;
        }
        if (isset($allergies)) {
            foreach ($allergies as $key => $value) {
                $allergy = Allergy::firstOrNew(
                    ['allergy' => $value]
                );
                $allergy->save();
                array_push($allergy_ids, $allergy->id);
            }
        }


        $category = [];
        if ($request->category_id != null) {
            $category[] = [
                'id' => $request->category_id,
                'position' => 1,
            ];
        }
        if ($request->sub_category_id != null) {
            $category[] = [
                'id' => $request->sub_category_id,
                'position' => 2,
            ];
        }
        if ($request->sub_sub_category_id != null) {
            $category[] = [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ];
        }

        $campaign->category_ids = json_encode($category);


        $campaign->choice_options = json_encode([]);
        $variations = [];
        if($request?->options)
        {
            foreach(array_values($request->options) as $key=>$option)
            {
                $temp_variation['name']= $option['name'];
                $temp_variation['type']= $option['type'];
                $temp_variation['min']= $option['min'] ?? 0;
                $temp_variation['max']= $option['max'] ?? 0;
                if($option['min'] > 0 &&  $option['min'] > $option['max']  ){
                    $validator->getMessageBag()->add('name', translate('messages.minimum_value_can_not_be_greater_then_maximum_value'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if(!isset($option['values'])){
                    $validator->getMessageBag()->add('name', translate('messages.please_add_options_for').$option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if($option['max'] > count($option['values'])  ){
                    $validator->getMessageBag()->add('name', translate('messages.please_add_more_options_or_change_the_max_value_for').$option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                $temp_variation['required']= $option['required']??'off';

                $temp_value = [];
                foreach($option['values'] as $value)
                {
                    if(isset($value['label'])){
                        $temp_option['label'] = $value['label'];
                    }
                    $temp_option['optionPrice'] = $value['optionPrice'];
                    $temp_value[] = $temp_option;
                }
                $temp_variation['values']= $temp_value;
                $variations[] = $temp_variation;
            }
        }

        $campaign->title = $request->title[array_search('default', $request->lang)];
        $campaign->description = $request->description[array_search('default', $request->lang)];
        $campaign->image = $request->has('image') ? Helpers::update(dir:'campaign/',old_image: $campaign->image,format: 'png', image:$request->file('image')) : $campaign->image;
        $campaign->start_date = $request->start_date;
        $campaign->end_date = $request->end_date;
        $campaign->start_time = $request->start_time;
        $campaign->end_time = $request->end_time;
        $campaign->restaurant_id = $request->restaurant_id;
        $campaign->variations = json_encode($variations);
        $campaign->price = $request->price;
        $campaign->discount =  $request->discount ?? 0;
        $campaign->discount_type = $request->discount_type;

        $campaign->add_ons = $request->has('addon_ids') ? json_encode($request->addon_ids) : json_encode([]);
        $campaign->veg = $request->veg;
        $campaign->maximum_cart_quantity = $request->maximum_cart_quantity;

        $campaign->save();

        $campaign->nutritions()->sync($nutrition_ids);
        $campaign->allergies()->sync($allergy_ids);

//        FoodSeoData::updateOrCreate(
//            ['item_campaign_id' => $campaign->id],
//            Helpers::getFoodSEOData($request, $campaign->id, $campaign->foodSeoData, 'ItemCampaign')
//        );

        Helpers::add_or_update_translations(request: $request, key_data: 'title', name_field: 'title', model_name: 'ItemCampaign', data_id: $campaign->id, data_value: $campaign->title);
        Helpers::add_or_update_translations(request: $request, key_data: 'description', name_field: 'description', model_name: 'ItemCampaign', data_id: $campaign->id, data_value: $campaign->description);
          if (addon_published_status('TaxModule')) {
            $SystemTaxVat = \Modules\TaxModule\Entities\SystemTaxSetup::where('is_active', 1)->where('is_default', 1)->first();
            if ($SystemTaxVat?->tax_type == 'product_wise') {
                $campaign->taxVats()->delete();
                foreach ($request['tax_ids'] ?? [] as $tax_id) {
                    \Modules\TaxModule\Entities\Taxable::create(
                        [
                            'taxable_type' => ItemCampaign::class,
                            'taxable_id' => $campaign->id,
                            'system_tax_setup_id' => $SystemTaxVat->id,
                            'tax_id' => $tax_id
                        ],
                    );
                }
            }
        }


        return response()->json([], 200);
    }

    public function edit($type, $campaign)
    {
        if($type=='basic')
        {
            $campaign = Campaign::withoutGlobalScope('translate')->findOrFail($campaign);
            $taxVats=[];
            $productWiseTax=false;
            $taxVatIds=[];
        }
        else
        {
            $campaign = ItemCampaign::withoutGlobalScope('translate')->with('foodSeoData')->findOrFail($campaign);
            $taxData = Helpers::getTaxSystemType();
            $productWiseTax = $taxData['productWiseTax'];
            $taxVatIds = $productWiseTax ? $campaign?->taxVats()->pluck('tax_id')->toArray() : [];
            $taxVats = $taxData['taxVats'];
        }


        return view('admin-views.campaign.'.$type.'.edit', compact('campaign','taxVats', 'productWiseTax', 'taxVatIds'));
    }

    public function view(Request $request ,$type, $campaign)
    {
        $key = explode(' ', $request['search']);
        $productWiseTax=false;
        if($type=='basic')
        {
            $campaign = Campaign::findOrFail($campaign);

            $restaurants = $campaign->restaurants()->with(['vendor','zone'])
            ->when(isset($key) ,function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->where('name', 'like', "%{$value}%");
                    // ->orWhere('email', 'like', "%{$value}%");
                }
            })
            ->paginate(config('default_pagination'));

            $restaurant_ids = [];
            foreach($campaign?->restaurants as $restaurant)
            {
                $restaurant_ids[] = $restaurant->id;
            }
            return view('admin-views.campaign.basic.view', compact('campaign', 'restaurants', 'restaurant_ids'));
        }
        else
        {
        $taxData = Helpers::getTaxSystemType();
        $productWiseTax = $taxData['productWiseTax'];
            $campaign = ItemCampaign::with($productWiseTax ? ['taxVats.tax','restaurant'] : ['restaurant'])->findOrFail($campaign);

            $orders = $campaign->orderdetails()->with(['order','order.customer','order.restaurant'])

            ->when(isset($key) ,function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->where('order_id', 'like', "%{$value}%");
                }
            })
            ->paginate(config('default_pagination'));

        }
        return view('admin-views.campaign.item.view', compact('campaign','orders','productWiseTax'));

    }

    public function status($type, $id, $status)
    {
        if($type=='item')
        {
            $campaign = ItemCampaign::findOrFail($id);
        }
        else{
            $campaign = Campaign::findOrFail($id);
        }
        $campaign->status = $status;
        $campaign->save();
        Toastr::success(translate('messages.campaign_status_updated'));
        return back();
    }

    public function delete(Campaign $campaign)
    {
        Helpers::check_and_delete('campaign/' , $campaign->image);
        $campaign?->translations()?->delete();
        $campaign->delete();
        Toastr::success(translate('messages.campaign_deleted_successfully'));
        return back();
    }
    public function delete_item(ItemCampaign $campaign)
    {
        Helpers::check_and_delete('campaign/' , $campaign->image);
        $campaign?->translations()?->delete();
        $campaign?->taxVats()->delete();
        $campaign->delete();
        Toastr::success(translate('messages.campaign_deleted_successfully'));
        return back();
    }

    public function remove_restaurant(Campaign $campaign, $restaurant)
    {
        $campaign?->restaurants()?->detach($restaurant);
        $campaign->save();
        Toastr::success(translate('messages.restaurant_remove_from_campaign'));
        return back();
    }
    public function addrestaurant(Request $request, Campaign $campaign)
    {
        $campaign?->restaurants()?->attach($request->restaurant_id,['campaign_status' => 'confirmed']);
        $campaign->save();
        Toastr::success(translate('messages.restaurant_added_to_campaign'));
        return back();
    }

    public function restaurant_confirmation($campaign,$restaurant_id,$status)
    {
        $campaign = Campaign::findOrFail($campaign);
        $campaign?->restaurants()?->updateExistingPivot($restaurant_id,['campaign_status' => $status]);
        $campaign->save();
        try
        {
            $restaurant=Restaurant::find($restaurant_id);

            $reataurant_push_notification_status= null ;
            $reataurant_push_notification_title= '' ;
            $message= null ;

            if($status == 'rejected'){
                $reataurant_push_notification_title= translate('Campaign_Request_Rejected') ;
                $message= Helpers::getPushNotificationMessage(status:'restaurant_campaign_join_rejaction',userType: 'restaurant' ,lang:$restaurant?->vendor?->current_language_key, restaurantName:$restaurant?->name);
                $reataurant_push_notification_status=Helpers::getRestaurantNotificationStatusData($restaurant?->id,'restaurant_campaign_join_rejaction');
                Toastr::success(translate('messages.campaign_join_request_rejected'));
            }

            elseif($status == 'confirmed'){
                $message= Helpers::getPushNotificationMessage(status:'restaurant_campaign_join_approve',userType: 'restaurant' ,lang:$restaurant?->vendor?->current_language_key, restaurantName:$restaurant?->name);
                $reataurant_push_notification_title= translate('Campaign_Request_Approved') ;
                $reataurant_push_notification_status=Helpers::getRestaurantNotificationStatusData($restaurant?->id,'restaurant_campaign_join_approval');
                Toastr::success(translate('messages.restaurant_added_to_campaign'));
            }


            if ($reataurant_push_notification_status?->push_notification_status == 'active' && $message && isset($restaurant?->vendor?->firebase_token)) {
                $data= Helpers::makeDataForPushNotification(title:$reataurant_push_notification_title, message:$message,orderId: '', type: 'campaign', orderStatus: '', dataId: $campaign->id);
                Helpers::send_push_notif_to_device($restaurant?->vendor?->firebase_token, $data);
                Helpers::insertDataOnNotificationTable($data , 'vendor', $restaurant->vendor_id);
            }


            $notification_status= Helpers::getNotificationStatusData('restaurant','restaurant_campaign_join_rejaction');
            $restaurant_notification_status= Helpers::getRestaurantNotificationStatusData($restaurant->id,'restaurant_campaign_join_rejaction');
            if( $notification_status?->mail_status == 'active' && $restaurant_notification_status?->mail_status == 'active' && config('mail.status') && Helpers::get_mail_status('campaign_deny_mail_status_restaurant') == '1' && $status == 'rejected') {
                Mail::to($restaurant->vendor?->getRawOriginal('email'))->send(new \App\Mail\VendorCampaignRequestMail($restaurant->name,'denied'));
                }
                $notification_status= null ;
            $notification_status= Helpers::getNotificationStatusData('restaurant','restaurant_campaign_join_approval');
            $restaurant_notification_status= Helpers::getRestaurantNotificationStatusData($restaurant->id,'restaurant_campaign_join_approval');
            if(  $notification_status?->mail_status == 'active' && $restaurant_notification_status?->mail_status == 'active' && config('mail.status') && Helpers::get_mail_status('campaign_approve_mail_status_restaurant') == '1' && $status == 'confirmed') {
                Mail::to($restaurant->vendor?->getRawOriginal('email'))->send(new \App\Mail\VendorCampaignRequestMail($restaurant->name,'approved'));
            }
        }
        catch(\Exception $e)
        {
            info(["line___{$e->getLine()}",$e->getMessage()]);
        }

        return back();
    }

    public function basic_campaign_export(Request $request){
        try{

            $relationships = [
                'translations' => 'value',
            ];
            $campaigns=Campaign::search(keywords:request()?->search, mainCol: ['title','description'], relations: $relationships)
            ->latest()->get();
            if($request->type == 'csv'){
                return Excel::download(new BasicCampaignExport($campaigns,$request['search']), 'Campaign.csv');
            }
            return Excel::download(new BasicCampaignExport($campaigns,$request['search']), 'Campaign.xlsx');
        }
            catch(\Exception $e)
        {
            Toastr::error("line___{$e->getLine()}",$e->getMessage());
            info(["line___{$e->getLine()}",$e->getMessage()]);
            return back();
        }

    }

    public function item_campaign_export(Request $request){
        try{
            $relationships = [
                'translations' => 'value',
            ];
            $campaigns=ItemCampaign::with('restaurant:id,name')->search(keywords:request()?->search, mainCol: ['title','description'], relations: $relationships)
            ->latest()->get();

            if($request->type == 'csv'){
                return Excel::download(new FoodCampaignExport($campaigns,$request['search']), 'FoodCampaign.csv');
            }
            return Excel::download(new FoodCampaignExport($campaigns,$request['search']), 'FoodCampaign.xlsx');
            }
                catch(\Exception $e)
            {
                Toastr::error("line___{$e->getLine()}",$e->getMessage());
                info(["line___{$e->getLine()}",$e->getMessage()]);
                return back();
            }
    }


    public function food_campaign_list_export(Request $request){
        try{
        $key = explode(' ', $request['search']);
        $campaign = ItemCampaign::with(['restaurant'])->findOrFail($request->campaign_id);

        $orders = $campaign->orderdetails()->with(['order','order.customer','order.restaurant'])
        ->when(isset($key) ,function ($q) use ($key) {
            foreach ($key as $value) {
                $q->where('order_id', 'like', "%{$value}%");
            }
        })
        ->latest()->get();
        $data=[
            'data' => $orders,
            'search' => $request['search'],
            'campaign' => $campaign,
        ];

        if($request->type == 'csv'){
            return Excel::download(new FoodCampaignOrderListExport($data), 'FoodCampaignOrderList.csv');
        }
        return Excel::download(new FoodCampaignOrderListExport($data), 'FoodCampaignOrderList.xlsx');
        }
                catch(\Exception $e)
            {
                Toastr::error("line___{$e->getLine()}",$e->getMessage());
                info(["line___{$e->getLine()}",$e->getMessage()]);
                return back();
            }
        }
}
