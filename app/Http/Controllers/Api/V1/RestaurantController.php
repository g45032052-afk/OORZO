<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Coupon;
use App\Models\Review;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\CentralLogics\RestaurantLogic;
use Illuminate\Support\Facades\Validator;

class RestaurantController extends Controller
{
    public function get_restaurants(Request $request, $filter_data="all")
    {
                Helpers::getZoneIds($request);

        $additional_data=[
            'zone_id'=> json_decode($request->header('zoneId'), true),
            'filter'=> $request->query('filter_data') ?? $filter_data,
            'limit' =>$request['limit'] ?? 25,
            'offset' =>$request['offset'] ?? 1,
            'type' =>$request->query('type', 'all') ?? 'all',
            'name' =>$request->query('name') ?? null,
            'longitude' =>$request->header('longitude') ?? 0,
            'latitude' => $request->header('latitude') ?? 0,
            'cuisine' => $request->query('cuisine', 'all') ?? 'all',
            'veg' =>$request->veg ?? null,
            'non_veg' =>$request->non_veg ?? null,
            'discount' =>$request->discount ?? null,
            'top_rated' =>$request->top_rated  ?? null,
            'featured' =>$request->featured ?? null,
            'delivery' =>$request->delivery ?? null,
            'takeaway' =>$request->takeaway ?? null,
            'avg_rating' =>$request->avg_rating ?? null,
            'sort_by' => $request->query('sort_by') ?? null,
            'filter_by' => $request->query('filter_by', []) ?? [],
            'cuisine_id' => $request->query('cuisine_id') ? (is_array($request->query('cuisine_id')) ? $request->query('cuisine_id') : json_decode($request->query('cuisine_id'), true)) : (is_array($request->query('cuisine')) ? $request->query('cuisine') : (json_decode($request->query('cuisine'), true) ?? [])),
            'order_type' => $request->query('order_type'),
            'request' => $request,
        ];


        $restaurants = RestaurantLogic::get_restaurants(additional_data: $additional_data );

        // The compact listing row - ad, is_new, free_delivery and the rest of the card's
        // badges - in place of the full restaurant record. Only this endpoint is changed;
        // every other listing still goes through restaurant_data_formatting().
        $restaurants['restaurants'] = RestaurantLogic::format_restaurants_for_listing(
            $restaurants['restaurants'],
            $this->topItemsContext($request, $restaurants['restaurants'])
        );

        return response()->json($restaurants, 200);
    }

    /**
     * "Recommended for you" home rail. Personalized for a signed in customer, engagement
     * ranked for everyone else. Same compact row as get_restaurants.
     */
    public function get_recommended_for_you(Request $request)
    {
        $restaurants = RestaurantLogic::get_recommended_for_you_restaurants(
            additional_data: $this->sectionInputs($request)
        );

        $restaurants['restaurants'] = RestaurantLogic::format_restaurants_for_listing(
            $restaurants['restaurants'],
            $this->topItemsContext($request, $restaurants['restaurants'])
        );

        return response()->json($restaurants, 200);
    }

    /**
     * "Exclusive deals" - restaurants running a restaurant wide discount, deepest discount first.
     * Optionally carries a strip of each restaurant's discounted food, like the Offers screen.
     */
    public function get_exclusive_deals(Request $request)
    {
        $data = RestaurantLogic::get_exclusive_deals(additional_data: $this->sectionInputs($request));

        // A deals rail shows deals, so this strip is the discounted food rather than the best
        // selling one.
        $data['restaurants'] = RestaurantLogic::format_restaurants_for_listing(
            $data['restaurants'],
            $this->topItemsContext($request, $data['restaurants'], 'discounted')
        );

        return response()->json($data, 200);
    }

    /** "Featured" home rail - the restaurants the admin curated, nearest first. */
    public function get_featured_restaurants(Request $request)
    {
        $restaurants = RestaurantLogic::get_featured_restaurants(
            additional_data: $this->sectionInputs($request)
        );

        $restaurants['restaurants'] = RestaurantLogic::format_restaurants_for_listing(
            $restaurants['restaurants'],
            $this->topItemsContext($request, $restaurants['restaurants'])
        );

        return response()->json($restaurants, 200);
    }

    /** "Quick delivery" home rail - fastest promised delivery first, nearest breaking ties. */
    public function get_quick_delivery_restaurants(Request $request)
    {
        $restaurants = RestaurantLogic::get_quick_delivery_restaurants(
            additional_data: $this->sectionInputs($request) + ['ad' => filter_var($request->query('ad', false), FILTER_VALIDATE_BOOLEAN)]
        );

        $restaurants['restaurants'] = RestaurantLogic::format_restaurants_for_listing(
            $restaurants['restaurants'],
            $this->topItemsContext($request, $restaurants['restaurants'])
        );

        return response()->json($restaurants, 200);
    }

    /**
     * The food strip under each card. Always present - every card that shows a restaurant shows
     * its food - and resolved for the whole page in one query rather than per row.
     *
     * Best selling by default; a deals rail asks for the discounted strip instead. Five is the
     * cap - the strip is a card, not a menu - and `item_limit` can only ask for fewer.
     */
    private function topItemsContext(Request $request, $restaurants, string $variant = 'top'): array
    {
        $ids = collect($restaurants)->pluck('id')->all();
        // A junk value takes the default rather than collapsing the strip to one item.
        $requested = $request->query('item_limit');
        $limit = is_numeric($requested) ? min(5, max(1, (int) $requested)) : 5;

        return [
            // Carried so the card's BOGO list can drop an offer the customer could not order
            // in the mode they are browsing in - dine-in only, read in delivery mode.
            'order_type' => $request->query('order_type'),
            'top_items' => $variant === 'discounted'
                ? RestaurantLogic::offerItemsByRestaurantIds($ids, $limit, $restaurants)
                : RestaurantLogic::topItemsByRestaurantIds($ids, $limit, $restaurants),
        ];
    }

    /**
     * What every home rail reads off the request: where the customer is, which zones that puts
     * them in, who they are, and the filters shared with the all restaurants listing.
     */
    private function sectionInputs(Request $request): array
    {
        Helpers::getZoneIds($request);

        return [
            'zone_id' => json_decode($request->header('zoneId'), true),
            'longitude' => $request->header('longitude') ?? 0,
            'latitude' => $request->header('latitude') ?? 0,
            'limit' => $request->query('limit', 25),
            'offset' => $request->query('offset', 1),
            'type' => $request->query('type', 'all') ?? 'all',
            'user_id' => auth('api')->id(),
            'filter_by' => $request->query('filter_by', []) ?? [],
            'cuisine_id' => $request->query('cuisine_id') ? (is_array($request->query('cuisine_id')) ? $request->query('cuisine_id') : json_decode($request->query('cuisine_id'), true)) : [],
            'order_type' => $request->query('order_type'),
            // The rails are searchable too - the home screen filters them in place.
            'name' => $request->query('name') ?? $request->query('search'),
            'request' => $request,
        ];
    }

    public function get_latest_restaurants(Request $request, $filter_data="all")
    {
               Helpers::getZoneIds($request);
               $additional_data=[
            'sort_by' => $request->query('sort_by') ?? 'default',
            'filter_by' => $request->query('filter_by', []) ?? [],
            'cuisine_id' => $request->query('cuisine_id') ? (is_array($request->query('cuisine_id')) ? $request->query('cuisine_id') : json_decode($request->query('cuisine_id'), true)) : (is_array($request->query('cuisine')) ? $request->query('cuisine') : (json_decode($request->query('cuisine'), true) ?? [])),
            'order_type' => $request->query('order_type'),
            'request' => $request,
        ];

        $type = $request->query('type', 'all');
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');
        $zone_id= json_decode($request->header('zoneId'), true);
        $restaurants = RestaurantLogic::get_latest_restaurants(zone_id:$zone_id, additional_data:$additional_data,limit:$request['limit'], offset:$request['offset'], type:$type ,longitude:$longitude,latitude:$latitude,veg:$request->veg ,non_veg:$request->non_veg, discount:$request->discount,top_rated: $request->top_rated);
        $restaurants['restaurants'] = Helpers::restaurant_data_formatting(data:$restaurants['restaurants'],multi_data: true );

        return response()->json($restaurants['restaurants'], 200);
    }

    public function get_popular_restaurants(Request $request)
    {
        Helpers::getZoneIds($request);
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');
        $type = $request->query('type', 'all');
        $zone_id= json_decode($request->header('zoneId'), true);
        $additional_data=[
            'sort_by' => $request->query('sort_by') ?? 'default',
            'filter_by' => $request->query('filter_by', []) ?? [],
            'cuisine_id' => $request->query('cuisine_id') ? (is_array($request->query('cuisine_id')) ? $request->query('cuisine_id') : json_decode($request->query('cuisine_id'), true)) : (is_array($request->query('cuisine')) ? $request->query('cuisine') : (json_decode($request->query('cuisine'), true) ?? [])),
            'order_type' => $request->query('order_type'),
            'request' => $request,
        ];
        $restaurants = RestaurantLogic::get_popular_restaurants(zone_id:$zone_id,limit: $request['limit'], offset:$request['offset'],type: $type,longitude:$longitude,latitude:$latitude,veg:$request->veg ,non_veg:$request->non_veg, discount:$request->discount,top_rated: $request->top_rated, additional_data:$additional_data);
        $restaurants['restaurants'] = Helpers::restaurant_data_formatting(data:$restaurants['restaurants'], multi_data:true);
        return response()->json($restaurants['restaurants'], 200);
    }


    public function recently_viewed_restaurants(Request $request)
    {
        Helpers::getZoneIds($request);
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');
        $type = $request->query('type', 'all');
        $zone_id= json_decode($request->header('zoneId'), true);
        $restaurants = RestaurantLogic::recently_viewed_restaurants_data(request:$request,zone_id:$zone_id, limit:$request['limit'], offset:$request['offset'],type: $type,longitude:$longitude,latitude:$latitude, additional_data: $this->sectionInputs($request));
        $restaurants['restaurants'] = Helpers::restaurant_data_formatting(data:$restaurants['restaurants'], multi_data:true);

        return response()->json($restaurants['restaurants'], 200);
    }

    public function get_details(Request $request ,$id)
    {
        // Headers can arrive quoted ("90.12"), the same way scopeWithOpen receives them.
        $longitude = trim($request->header('longitude') ?? '', "\"'");
        $latitude = trim($request->header('latitude') ?? '', "\"'");

        $restaurant = RestaurantLogic::get_restaurant_details($id, $longitude, $latitude);
        if($restaurant)
        {
            $category_ids = DB::table('food')
            ->join('categories', 'food.category_id', '=', 'categories.id')
            ->selectRaw('IF((categories.position = "0"), categories.id, categories.parent_id) as categories')
            ->where('food.restaurant_id', $restaurant->id)
            ->where('categories.status',1)
            ->groupBy('categories')
            ->get();
            // Resolved from the model before formatting, which only knows about restaurants.
            $happyHour = Helpers::get_running_happy_hour($restaurant);
            $activeDiscount = Helpers::get_effective_restaurant_discount($restaurant);

            $restaurant = Helpers::restaurant_data_formatting(data: $restaurant);
            $restaurant['category_ids'] = array_map('intval', $category_ids->pluck('categories')->toArray());

            // Is this store inside a happy hour right this moment. The existing `discount`
            // object is left alone - it is the store's standing discount and clients read it
            // that way - but on its own it does not say what customers are actually being
            // charged, because a live happy hour replaces it. active_discount is that number,
            // taken from the same central resolver every product price goes through.
            $restaurant['is_happy_hour_running'] = (bool) $happyHour;
            $restaurant['happy_hour'] = $happyHour?->customerPayload();
            $restaurant['active_discount'] = $activeDiscount ? (float) $activeDiscount['discount'] : 0;

            // Always present, in metres, so the client never has to branch on the key
            // existing; 0 when the request sent no coordinates to measure from.
            $restaurant['distance'] = (float) ($restaurant['distance'] ?? 0);
            // The readable companions, from the same helper the listing uses - the details screen
            // was the one place a client had to divide by 1000 itself.
            foreach (Helpers::distanceKeysFromMetres($restaurant['distance']) as $key => $value) {
                $restaurant[$key] = $value;
            }

            // Same reason as the personalization call below: without apiGuestCheck on this route
            // $request->user is always null, so this logged nothing and the recently viewed rail
            // it feeds had no views to show - only order counts, written by place_order.
            if($customer_id = ($request->user?->id ?? auth('api')->id())){
                Helpers::visitor_log(model:'restaurant',user_id:$customer_id,visitor_log_id:$restaurant->id,order_count:false);
            }
            // Personalization: viewing a restaurant's details is a store-engagement signal.
            // This route has no apiGuestCheck so $request->user isn't populated —
            // resolve the customer from the api token like 6amMart's auth('api') check.
            if (auth('api')->id()) {
                \App\CentralLogics\PersonalizationService::recordStoreAction((int) auth('api')->id(), (int) $restaurant->id, 'store_view');
            }
        }

        return response()->json($restaurant, 200);
    }

    public function get_searched_restaurants(Request $request)
    {
        Helpers::getZoneIds($request);
        // Personalization: record the restaurant search keyword for logged-in users.
        if (auth('api')->id() && isset($request['name']) && $request['name'] != 'null') {
            \App\CentralLogics\PersonalizationService::recordSearchAction((int) auth('api')->id(), $request['name']);
        }
        $additional_data=[
            'sort_by' => $request->query('sort_by') ?? 'default',
            'filter_by' => $request->query('filter_by', []) ?? [],
            'cuisine_id' => $request->query('cuisine_id') ? (is_array($request->query('cuisine_id')) ? $request->query('cuisine_id') : json_decode($request->query('cuisine_id'), true)) : (is_array($request->query('cuisine')) ? $request->query('cuisine') : (json_decode($request->query('cuisine'), true) ?? [])),
            'order_type' => $request->query('order_type'),
            'request' => $request,
        ];

        $type = $request->query('type', 'all');
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');
        $zone_id= json_decode($request->header('zoneId'), true);
        $cuisine = $request->query('cuisine', []) ?? [];
        $restaurants = RestaurantLogic::search_restaurants(name:$request['name'], zone_id:$zone_id, category_id:$request->category_id,limit:$request['limit'], offset:$request['offset'],type: $type,longitude:$longitude,latitude:$latitude ,popular: $request->popular ,new: $request->new ,rating: $request->rating,
        rating_3_plus:$request->rating_3_plus,rating_4_plus:$request->rating_4_plus ,rating_5:$request->rating_5 ,
        discounted: $request->discounted ,sort_by: $request->sort_by , dine_in:  $request->dine_in , open:  $request->open,cuisine: $cuisine , additional_data:$additional_data, halal: $request->halal);
        $restaurants['restaurants'] = Helpers::restaurant_data_formatting( data: $restaurants['restaurants'],multi_data: true);

        // restaurant_data_formatting() attaches foods as bare id/image/name rows,
        // which a search card cannot price. They are replaced here with the listing
        // shape — price, discounted_price, badges — resolved in one query for the
        // page. Only this endpoint is changed; every other caller of
        // restaurant_data_formatting() keeps the rows it has always had.
        $searchFoods = RestaurantLogic::foodsByRestaurantIds(
            collect($restaurants['restaurants'])->pluck('id')->all(),
            5,
            $restaurants['restaurants']
        );

        foreach ($restaurants['restaurants'] as $restaurant) {
            $restaurant['foods'] = $searchFoods[$restaurant['id']] ?? [];
        }

        // Feeds the trending-searches rail. Logged after the query because trending only counts
        // searches that found something, and guests count too, keyed by their guest_id.
        app(\App\Services\TrendingSearchService::class)->log(
            keyword: $request['name'] ?? null,
            userId: auth('api')->id(),
            guestId: $request->guest_id,
            zoneId: (string) $request->header('zoneId'),
            resultCount: (int) ($restaurants['total_size'] ?? 0),
        );

        return response()->json($restaurants, 200);
    }

    public function reviews(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $id = $request['restaurant_id'];


        $reviews = Review::with(['customer', 'food'])
        ->whereHas('food', function($query)use($id){
            return $query->where('restaurant_id', $id);
        })
        ->active()->latest()->get();

        $storage = [];
        foreach ($reviews as $item) {
            $item->attachment = json_decode($item->attachment);
            $item->food_name = null;
            $item->food_image = null;
            $item->customer_name = null;
            if($item->food)
            {
                $item->food_id = $item->food->id;
                $item->food_name = $item->food->name;
                $item->food_image = $item->food->image;
                $item->food_image_full_url = $item->food->image_full_url;
                if(count($item->food->translations)>0)
                {
                    $translate = array_column($item->food->translations->toArray(), 'value', 'key');
                    $item->food_name = $translate['name'];
                }
            }
            if($item->customer)
            {
                $item->customer_name = $item->customer->f_name.' '.$item->customer->l_name;
            }

            unset($item->food);
            unset($item->customer);
            array_push($storage, $item);
        }

        return response()->json($storage, 200);
    }

    public function get_coupons(Request $request){

        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $restaurant_id=$request->restaurant_id;
        $customer_id=$request->customer_id ?? null;

        $coupons = Coupon::Where(function ($q) use ($restaurant_id,$customer_id) {
            $q->Where('coupon_type', 'restaurant_wise')->whereJsonContains('data', [$restaurant_id])
                ->where(function ($q1) use ($customer_id) {
                    $q1->whereJsonContains('customer_id', [$customer_id])->orWhereJsonContains('customer_id', ['all']);
                });
        })->orWhereHas('restaurant',function($q) use ($restaurant_id){
            $q->where('id',$restaurant_id);
        })
        ->active()->whereDate('expire_date', '>=', date('Y-m-d'))->whereDate('start_date', '<=', date('Y-m-d'))
        ->get();
        return response()->json($coupons, 200);
    }


    public function get_recommended_restaurants(Request $request){
        Helpers::getZoneIds($request);

        $longitude= $request->header('longitude') ?? 0;
        $latitude= $request->header('latitude') ?? 0;
        // $user_id = $request->user ? $request->user->id : $request['guest_id'];
        $zone_id= json_decode($request->header('zoneId'), true);
        $data = Restaurant::withOpen($longitude,$latitude)

        ->withcount('foods')
        ->with(['foods_for_reorder'])
        ->Active()
        ->whereIn('zone_id', $zone_id)
        ->orderBy('open', 'desc')
        ->orderBy('distance', 'asc')
        ->inRandomOrder()->limit(20)
        ->get()
		->map(function ($data) {
			$data->foods = $data->foods_for_reorder->take(5);
            unset($data->foods_for_reorder);
			return $data;
		});

        return response()->json(Helpers::restaurant_data_formatting($data, true), 200);
    }


    public function get_visited_restaurants(Request $request){
        Helpers::getZoneIds($request);

        $longitude= $request->header('longitude') ?? 0;
        $latitude= $request->header('latitude') ?? 0;
        $user_id = $request->user ? $request->user->id : $request['guest_id'];
        // dd($user_id);
        $zone_id= json_decode($request->header('zoneId'), true);
        $data = Restaurant::withOpen($longitude,$latitude)
        ->wherehas('users', function($q) use($user_id) {
            $q->where('user_id',$user_id);
        })
        ->with('users')
        ->withcount('foods')
        ->with(['foods_for_reorder'])
        ->Active()
        ->whereIn('zone_id', $zone_id)

        ->selectRaw('(SELECT `visit_count` FROM `visitor_logs` WHERE `restaurants`.`id` = `visitor_logs`.`visitor_log_id` AND `user_id` = ? ORDER BY `visit_count` DESC LIMIT 1) as visit_count', [$user_id])

        ->orderBy('visit_count', 'desc')

        ->limit(20)
        ->get()
		->map(function ($data) {
			$data->foods = $data->foods_for_reorder->take(5);
            unset($data->foods_for_reorder);
			return $data;
		});

        return response()->json(Helpers::restaurant_data_formatting($data, true), 200);
    }



    public function get_dine_in_restaurants(Request $request)
    {
        Helpers::getZoneIds($request);
        $longitude= $request->header('longitude')?? 0;
        $latitude= $request->header('latitude')?? 0;
        $type = $request->query('type', 'all');
        $zone_id= json_decode($request->header('zoneId'), true);

        $additional_data=[
            'zone_id'=>$zone_id,
            'limit'=> $request['limit'],
            'offset'=>$request['offset'],
            'type'=> $type,
            'longitude'=>$longitude,
            'latitude'=>$latitude,
            'veg'=>$request->veg ,
            'non_veg'=>$request->non_veg,
            'discount'=>$request->discount,
            'top_rated'=> $request->top_rated,
            'new'=>$request->new,
            'rating_5'=> $request->rating_5,
            'rating_4_plus'=> $request->rating_4_plus,
            'rating_3_plus'=>$request->rating_3_plus,
            'sort_by'=> $request->sort_by,
            'open' => $request->open,
            'cuisine' => $request->query('cuisine', []) ?? [],
            'request' => $request,
        ];


        $restaurants = RestaurantLogic::get_dine_in_restaurants(additional_data:$additional_data);
        $restaurants['restaurants'] = Helpers::restaurant_data_formatting(data:$restaurants['restaurants'], multi_data:true);
        return response()->json($restaurants, 200);
    }


}
