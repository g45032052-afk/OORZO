<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Models\Allergy;
use App\Models\Nutrition;
use App\Models\Tag;
use App\Models\Food;
use App\Models\Review;
use App\Models\RestaurantCategory;
use App\Models\Variation;
use App\Models\Translation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\VariationOption;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FoodController extends Controller
{

    public function store(Request $request)
    {
        if(!$request?->vendor?->restaurants[0]?->food_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ],403);
        }

        $request->merge([
            'restaurant_category_id' => Helpers::restaurantCategoryStatus()
                ? Helpers::normalizeRestaurantCategoryId($request->input('restaurant_category_id'))
                : null,
        ]);

        // Conditional required: required when the vendor has at least one of
        // their own categories. Resolved from the authenticated vendor token.
        $vendorRestaurantId = (int) ($request['vendor']->restaurants[0]->id ?? 0);
        $restaurantCategoryRule = Helpers::hasAnyRestaurantCategory($vendorRestaurantId)
            ? 'required'
            : 'nullable';

        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'restaurant_category_id' => [
                $restaurantCategoryRule,
                Rule::exists('restaurant_categories', 'id')->where(function ($query) use ($vendorRestaurantId) {
                    return $query->where('restaurant_id', $vendorRestaurantId);
                }),
            ],
            'price' => 'required|numeric|min:0.01',
            'discount' => 'required|numeric|min:0',
            'veg' => 'required|boolean',
            'translations'=>'required',
            'stock_type'=>'required',
            'image' => 'nullable|max:2048',

        ], [
            'category_id.required' => translate('messages.category_required'),
            'restaurant_category_id.required' => translate('messages.restaurant_category_required'),
        ]);

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
        }

        $data = json_decode($request?->translations, true);

        if (count($data) < 1) {
            $validator->getMessageBag()->add('translations', translate('messages.Name and description in english is required'));
        }

        if ($request['price'] <= $dis || count($data) < 1 || $validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }


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

        $nutrition_ids = [];
        if ($request->nutritions != null) {
            $nutritions =  explode(",", $request->nutritions);
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
            $allergies =  explode(",", $request->allergies);

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


        $food = new Food;
        $food->name = $data[0]['value'];

        $category = [];
        if ($request->category_id != null) {
            array_push($category, [
                'id' => $request->category_id,
                'position' => 1,
            ]);
        }
        if ($request->sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_category_id,
                'position' => 2,
            ]);
        }
        if ($request->sub_sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ]);
        }
        $food->category_id = $request?->sub_category_id ?? $request?->category_id;
        $food->category_ids = json_encode($category);
        $food->description = $data[1]['value'];


        $food->choice_options = json_encode([]);

        $food->variations = json_encode([]);
        $food->price = $request->price;
        $food->image = Helpers::upload(dir:'product/', format:'png', image: $request->file('image'));
        $food->available_time_starts = $request->available_time_starts;
        $food->available_time_ends = $request->available_time_ends;
        $food->discount = $request->discount ?? 0;
        $food->discount_type = $request->discount_type;
        $food->attributes = $request->has('attribute_id') ? $request->attribute_id : json_encode([]);
        $food->add_ons = $request->has('addon_ids') ? json_encode(explode(',',$request->addon_ids)) : json_encode([]);
        $food->restaurant_id = $request['vendor']->restaurants[0]->id;
        $food->restaurant_category_id = $request->restaurant_category_id;
        $food->veg = $request->veg;
        $food->maximum_cart_quantity = $request->maximum_cart_quantity;
        $food->is_halal =  $request->is_halal ?? 0;

        $food->item_stock = $request?->item_stock ?? 0;
        $food->stock_type = $request->stock_type;

        $restaurant=$request['vendor']->restaurants[0];
        if (  $restaurant->restaurant_model == 'subscription' ) {

            $rest_sub = $restaurant?->restaurant_sub;
            if (isset($rest_sub)) {
                if ($rest_sub?->max_product != "unlimited" && $rest_sub?->max_product > 0 ) {
                    $total_food= Food::where('restaurant_id', $restaurant->id)->count()+1;
                    if ( $total_food >= $rest_sub->max_product  ){
                        $restaurant->update(['food_section' => 0]);
                    }
                }
            } else{
                return response()->json([
                    'unsubscribed'=>[
                        ['code'=>'unsubscribed', 'message'=>translate('messages.you_are_not_subscribed_to_any_package')]
                    ]
                ]);
            }
        } elseif($restaurant->restaurant_model == 'unsubscribed'){
            return response()->json([
                'unsubscribed'=>[
                    ['code'=>'unsubscribed', 'message'=>translate('messages.you_are_not_subscribed_to_any_package')]
                ]
            ]);
        }

        $food->save();

        if(isset($request->options))
        {
            foreach(json_decode($request->options, true) as $option)
            {
                $variation=  New Variation ();
                $variation->food_id =$food->id;
                $variation->name = $option['name'];
                $variation->type = $option['type'];
                $variation->min = data_get($option, 'min') > 0 ? data_get($option, 'min') : 0;
                $variation->max = data_get($option, 'max') > 0 ? data_get($option, 'max') : 0;
                $variation->is_required =   data_get($option, 'required') == 'on' ? true : false;
                $variation->save();

                foreach($option['values'] as $value)
                {
                    $VariationOption=  New VariationOption ();
                    $VariationOption->food_id =$food->id;
                    $VariationOption->variation_id =$variation->id;
                    $VariationOption->option_name = $value['label'];
                    $VariationOption->option_price = $value['optionPrice'];
                    $VariationOption->stock_type = $request?->stock_type ?? 'unlimited' ;
                    $VariationOption->total_stock = data_get($value, 'total_stock') == null || $VariationOption->stock_type == 'unlimited' ? 0 : data_get($value, 'total_stock');
                    $VariationOption->save();
                }
            }
        }


        $food?->tags()?->sync($tag_ids);
        $food?->nutritions()?->sync($nutrition_ids);
        $food?->allergies()?->sync($allergy_ids);

//        FoodSeoData::create(
//            Helpers::getFoodSEOData($request, $food->id, null)
//        );

        foreach ($data as $key=>$item) {
            $data[$key]['translationable_type'] = 'App\Models\Food';
            $data[$key]['translationable_id'] = $food->id;
        }
        Translation::insert($data);

        if (addon_published_status('TaxModule')) {
            $SystemTaxVat = \Modules\TaxModule\Entities\SystemTaxSetup::where('is_active', 1)->where('is_default', 1)->first();
            if ($SystemTaxVat?->tax_type == 'product_wise') {
                foreach (json_decode($request->tax_ids??'[]', true) ?? [] as $tax_id) {
                    \Modules\TaxModule\Entities\Taxable::create(
                        [
                            'taxable_type' => Food::class,
                            'taxable_id' => $food->id,
                            'system_tax_setup_id' => $SystemTaxVat->id,
                            'tax_id' => $tax_id
                        ],
                    );
                }
            }
        }

        return response()->json(['message'=>translate('messages.product_added_successfully')], 200);
    }

    public function status(Request $request)
    {
        if(!$request?->vendor?->restaurants[0]?->food_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ],403);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $product = Food::find($request->id);
        $product->status = $request->status;
        $product?->save();

        if($request->status != 1){
            $product?->carts()?->delete();
        }
        return response()->json(['message' => translate('messages.product_status_updated')], 200);
    }

    public function get_product($id)
    {
            $item = Food::withoutGlobalScope('translate')->with(['tags','taxVats','foodSeoData', 'restaurantCategory'])->where('id',$id)
            ->first();
            $item = Helpers::product_data_formatting_translate($item, false, false, app()->getLocale());
            return response()->json($item, 200);

    }

    public function recommended(Request $request)
    {
        if(!$request?->vendor?->restaurants[0]?->food_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ],403);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $product = Food::find($request->id);
        $product->recommended = $request->status;
        $product?->save();

        return response()->json(['message' => translate('messages.product_recommended_status_updated')], 200);

    }




    public function update(Request $request)
    {
        if(!$request?->vendor?->restaurants[0]?->food_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ],403);
        }

        $request->merge([
            'restaurant_category_id' => Helpers::restaurantCategoryStatus()
                ? Helpers::normalizeRestaurantCategoryId($request->input('restaurant_category_id'))
                : null,
        ]);

        // Conditional required: required when the vendor has at least one of
        // their own categories. Resolved from the authenticated vendor token.
        $vendorRestaurantId = (int) ($request['vendor']->restaurants[0]->id ?? 0);
        $restaurantCategoryRule = Helpers::hasAnyRestaurantCategory($vendorRestaurantId)
            ? 'required'
            : 'nullable';

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'category_id' => 'required',
            'restaurant_category_id' => [
                $restaurantCategoryRule,
                Rule::exists('restaurant_categories', 'id')->where(function ($query) use ($vendorRestaurantId) {
                    return $query->where('restaurant_id', $vendorRestaurantId);
                }),
            ],
            'price' => 'required|numeric|min:0.01',
            'discount' => 'required|numeric|min:0',
            'veg' => 'required|boolean',
            'image' => 'nullable|max:2048',
            'stock_type'=>'required',
        ], [
            'category_id.required' => translate('messages.category_required'),
            'restaurant_category_id.required' => translate('messages.restaurant_category_required'),
        ]);


        // A repeat (subscription) order is delivered on its own schedule, so shrinking the
        // available time out from under one would silently break the recurring delivery.
        $repeat_order_times = Helpers::repeat_order_times_lost_by_availability(
            Food::find($request->id),
            $request->available_time_starts,
            $request->available_time_ends
        );

        if ($repeat_order_times) {
            $validator->getMessageBag()->add('available_time_starts', translate('messages.food_available_time_must_cover_repeat_order', [
                'times' => implode(', ', array_map(fn ($time) => Helpers::time_format($time), $repeat_order_times)),
            ]));
        }

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
        }
        $data = json_decode($request->translations, true);

        if (count($data) < 1) {
            $validator->getMessageBag()->add('translations', translate('messages.Name and description in english is required'));
        }

        if ($request['price'] <= $dis || count($data) < 1 || $validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }


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

        $nutrition_ids = [];
        if ($request->nutritions != null) {
            $nutritions =  explode(",", $request->nutritions);
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
            $allergies =  explode(",", $request->allergies);

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

        $p = Food::findOrFail($request->id);

        $p->name = $data[0]['value'];

        $category = [];
        if ($request->category_id != null) {
            array_push($category, [
                'id' => $request->category_id,
                'position' => 1,
            ]);
        }
        if ($request->sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_category_id,
                'position' => 2,
            ]);
        }
        if ($request->sub_sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ]);
        }

        $p->category_id = $request?->sub_category_id ?? $request->category_id;
        $p->category_ids = json_encode($category);
        $p->description = $data[1]['value'];

        $p->choice_options = json_encode([]);

        if(isset($request->options))
        {
            foreach(json_decode($request->options, true) as $option)
            {
                $variation=Variation::updateOrCreate([
                    'id'=> $option['variation_id'] ?? null,
                    'food_id'=> $p->id,
                    ],[
                        "name" => $option['name'],
                        "type" => $option['type'],
                        "min" => data_get($option, 'min') > 0 ? data_get($option, 'min') : 0,
                        "max" => data_get($option, 'max') > 0 ? data_get($option, 'max') : 0,
                        "is_required" => data_get($option, 'required') == 'on' ? true : false,
                    ]);

                    foreach($option['values'] as $value)
                {
                    VariationOption::updateOrCreate([
                        'id'=> $value['option_id'] ?? null,
                        'food_id'=> $p->id,
                        'variation_id'=> $variation->id,
                    ],[
                        "option_name" =>$value['label'],
                        "option_price" => $value['optionPrice'],
                        "total_stock" =>data_get($value, 'total_stock') == null ||  $request?->stock_type == 'unlimited' ? 0 : data_get($value, 'total_stock'),
                        "stock_type" => $request?->stock_type ?? 'unlimited' ,
                        "sell_count" =>0 ,
                    ]);
                }
            }

        }

        if($request?->removedVariationOptionIDs && is_string($request?->removedVariationOptionIDs)){
            VariationOption::whereIn('id',explode(',',$request->removedVariationOptionIDs))->delete();
        }
        if($request?->removedVariationIDs && is_string($request?->removedVariationIDs)){
            VariationOption::whereIn('variation_id',explode(',',$request->removedVariationIDs))->delete();
            Variation::whereIn('id',explode(',',$request->removedVariationIDs))->delete();
        }

        $p->item_stock = $request?->item_stock ?? 0;
        $p->stock_type = $request->stock_type;

        $slug = Str::slug($p->name);
        $p->slug = $p->slug? $p->slug :"{$slug}-{$p->id}";

        $p->variations = json_encode([]);
        $p->price = $request->price;
        $p->image = $request->has('image') ? Helpers::update(dir:'product/', old_image:$p->image,  format:'png', image: $request->file('image')) : $p->image;
        $p->available_time_starts = $request->available_time_starts;
        $p->available_time_ends = $request->available_time_ends;
        $p->discount = $request->discount ?? 0;
        $p->discount_type = $request->discount_type;
        $p->attributes = $request->has('attribute_id') ? $request->attribute_id : json_encode([]);
        $p->add_ons = $request->has('addon_ids') ? json_encode(explode(',',$request->addon_ids)) : json_encode([]);
        $p->restaurant_category_id = $request->restaurant_category_id;
        $p->veg = $request->veg;
        $p->maximum_cart_quantity = $request->maximum_cart_quantity;
        $p->is_halal =  $request->is_halal ?? 0;
        $p->sell_count = 0;

        $taxIds =json_decode($request->tax_ids??'[]' , true);
        if (addon_published_status('TaxModule') && $taxIds) {
            $taxVatIds = $p->taxVats()->pluck('tax_id')->toArray() ?? [];
            $newTaxVatIds =  array_map('intval', $taxIds ?? []);
            sort($newTaxVatIds);
            sort($taxVatIds);
            if ($newTaxVatIds != $taxVatIds) {
                $p->taxVats()->delete();
                $SystemTaxVat = \Modules\TaxModule\Entities\SystemTaxSetup::where('is_active', 1)->where('is_default', 1)->first();
                if ($SystemTaxVat?->tax_type == 'product_wise') {
                    foreach ($taxIds ?? [] as $tax_id) {
                        \Modules\TaxModule\Entities\Taxable::create(
                            [
                                'taxable_type' => Food::class,
                                'taxable_id' => $p->id,
                                'system_tax_setup_id' => $SystemTaxVat->id,
                                'tax_id' => $tax_id
                            ],
                        );
                    }
                }
            }
        }

        $p?->save();
        $p?->tags()?->sync($tag_ids);
        $p?->nutritions()?->sync($nutrition_ids);
        $p?->allergies()?->sync($allergy_ids);


//        FoodSeoData::updateOrCreate(
//            ['food_id' => $p->id],
//            Helpers::getFoodSEOData($request, $p->id, $p->foodSeoData)
//        );

        foreach ($data as $key=>$item) {
            Translation::updateOrInsert(
                ['translationable_type' => 'App\Models\Food',
                    'translationable_id' => $p->id,
                    'locale' => $item['locale'],
                    'key' => $item['key']],
                ['value' => $item['value']]
            );
        }

        return response()->json(['message'=>translate('messages.product_updated_successfully')], 200);
    }

    public function delete(Request $request)
    {
        if(!$request?->vendor?->restaurants[0]?->food_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ],403);
        }
        $product = Food::findOrFail($request->id);

        if($product?->image)
        {
            Helpers::check_and_delete('product/' , $product['image']);
        }
        $product?->carts()?->delete();
        $product?->newVariationOptions()?->delete();
        $product?->newVariations()?->delete();
        $product?->translations()?->delete();
        $product?->taxVats()->delete();
        $product?->delete();

        return response()->json(['message'=>translate('messages.product_deleted_successfully')], 200);
    }

    public function search(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $key = explode(' ', $request['name']);

        $products = Food::active()
        ->with(['rating'])
        ->where('restaurant_id', $request['vendor']?->restaurants[0]?->id)
        ->when($request->category_id, function($query)use($request){
            $query->whereHas('category',function($q)use($request){
                return $q->whereId($request->category_id)->orWhere('parent_id', $request->category_id);
            });
        })
        ->when($request->restaurant_id, function($query) use($request){
            return $query->where('restaurant_id', $request->restaurant_id);
        })
        ->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
            $q->orWhereHas('tags',function($query)use($key){
                $query->where(function($q)use($key){
                    foreach ($key as $value) {
                        $q->where('tag', 'like', "%{$value}%");
                    };
                });
            });
        })
        ->limit(50)
        ->get();

        $data = Helpers::product_data_formatting(data:$products,multi_data: true,trans: false, local:app()->getLocale());
        return response()->json($data, 200);
    }

    public function reviews(Request $request)
    {
        $id = $request['vendor']?->restaurants[0]?->id;
        $key = explode(' ', $request['search']);

        $reviews = Review::with(['customer', 'food'])
        ->whereHas('food', function($query)use($id){
            return $query->where('restaurant_id', $id);
        })
        ->when(isset($key) , function($q) use($key ,$id) {
            $q->where(function($q) use($key ,$id ){
                foreach ($key as $value) {
                    $q->where('order_id', 'like', "%{$value}%")->orwhereHas('food', function($query)use($value){
                        return $query->where('name', 'like', "%{$value}%");
                    });
                }
            });
        })

        ->latest()->get();

        $storage = [];
        foreach ($reviews as $item) {
            $item['attachment'] = json_decode($item['attachment']);
            $item['food_name'] = null;
            $item['food_image'] = null;
            $item['customer_name'] = null;
            $item['customer_phone'] = null;
            if($item->food)
            {
                $item['food_name'] = $item?->food?->name;
                $item['food_image'] = $item?->food?->image;
                $item['food_image_full_url'] = $item?->food?->image_full_url;
                if(count($item?->food?->translations)>0)
                {
                    $translate = array_column($item?->food?->translations?->toArray(), 'value', 'key');
                    $item['food_name'] = $translate['name'];
                }
            }

            if($item->customer)
            {
                $item['customer_name'] = $item?->customer?->f_name.' '.$item?->customer?->l_name;
                $item['customer_phone'] = $item?->customer?->phone;
            }

            unset($item['food']);
            unset($item['customer']);
            array_push($storage, $item);
        }

        return response()->json($storage, 200);
    }

    public function update_reply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'reply' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $review = Review::findOrFail($request->id);
        $review->reply = $request->reply;
        $review->reply_at = $review->reply_at ?? Carbon::now();
        $review->restaurant_id = $request['vendor']?->restaurants[0]?->id;
        $review->save();

        return response()->json(['message'=>translate('messages.review_reply_updated_successfully')], 200);
    }


    public function updateStock(Request $request){

        $product = Food::findOrFail($request->food_id);
        $product->item_stock = $request->item_stock;
        $product->sell_count =0;
        $product->save() ;

        if(isset($request->option) && is_string($request->option) ){
                foreach(json_decode($request->option,true) ?? [] as $key => $value ){
                    VariationOption::where('food_id',$product->id)->where('id',$key)->update([
                        'sell_count' => 0,
                        'total_stock'=> $value
                    ]);
                }
        }
        return response()->json(['message'=>translate('messages.Stock_updated_successfully')], 200);
    }
}
