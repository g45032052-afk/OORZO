<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\CentralLogics\ProductLogic;
use App\CentralLogics\RestaurantLogic;
use App\Http\Controllers\Controller;
use App\Models\Allergy;
use App\Models\Food;
use App\Models\Nutrition;
use App\Models\Restaurant;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function get_latest_products(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'nullable',
            'category_id' => 'nullable',
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        Helpers::getZoneIds($request);
        $cuisine_id = $request->query('cuisine_id') ?? $request->query('cuisine');
        if($cuisine_id && !is_array($cuisine_id)){
            $cuisine_id = json_decode($cuisine_id, true);
        }

        $filter_by = $request->query('filter_by', []) ?? [];
        $filter_by = is_array($filter_by) ? $filter_by : [$filter_by];
        // The storefront sends these as flat `param=1` flags; `filter_by[]` is the
        // chip spelling of the same thing. Honour both.
        $flat_filters = [
            'discounted' => 'discounted',
            'veg' => 'veg',
            'non_veg' => 'non_veg',
            'halal' => 'halal',
            'new' => 'new_arrivals',
            'popular' => 'popular',
            'open' => 'currently_open',
        ];
        // A food is either veg or not, so asking for both is the same as asking for neither.
        if($request->veg == 1 && $request->non_veg == 1){
            unset($flat_filters['veg'], $flat_filters['non_veg']);
        }
        foreach($flat_filters as $param => $chip){
            if($request->$param == 1 && !in_array($chip, $filter_by)){
                $filter_by[] = $chip;
            }
        }

        $additional_data=[
            'sort_by' => $request->query('sort_by') ?? 'default',
            'filter_by' => $filter_by,
            'cuisine_id' => $cuisine_id ?? [],
            'order_type' => $request->query('order_type',[]) ?? [],
            'restaurant_category_id' => $request->restaurant_category_id ?? null,
        ];

        $type = $request->query('type', 'all');
        $zone_id = json_decode($request->header('zoneId'), true);

        $products = ProductLogic::get_latest_products(limit:$request['limit'], offset:$request['offset'],restaurant_id: $request['restaurant_id'],category_id: $request['category_id'],request: $request, type:$type,additional_data: $additional_data, zone_id: $zone_id);
        $products['products'] = Helpers::product_data_formatting(data:$products['products'],multi_data: true, trans:false,local: app()->getLocale());
        return response()->json($products, 200);
    }

    public function get_searched_products(Request $request)
    {
        Helpers::getZoneIds($request);
        // Personalization: record the search keyword so it can be mapped to preferred categories.
        if (auth('api')->id() && isset($request['name']) && $request['name'] != 'null') {
            \App\CentralLogics\PersonalizationService::recordSearchAction((int) auth('api')->id(), $request['name']);
        }
        $cuisine_id = $request->query('cuisine_id') ?? $request->query('cuisine');
        if($cuisine_id && !is_array($cuisine_id)){
            $cuisine_id = json_decode($cuisine_id, true);
        }

        $additional_data=[
            'sort_by' => $request->query('sort_by') ?? 'default',
            'filter_by' => $request->query('filter_by', []) ?? [],
            'cuisine_id' => $cuisine_id ?? [],
            'order_type' => $request->query('order_type',[]) ?? [],
        ];

        $search_bar_default_status = \App\Models\BusinessSetting::where('key', 'search_bar_default_status')->first();
        $search_bar_default_status = $search_bar_default_status ? $search_bar_default_status->value : 1;
        $search_bar_sort_by_unavailable = \App\Models\PriorityList::where('name', 'search_bar_sort_by_unavailable')->where('type','unavailable')->first();
        $search_bar_sort_by_unavailable = $search_bar_sort_by_unavailable ? $search_bar_sort_by_unavailable->value : '';
        $search_bar_sort_by_temp_closed = \App\Models\PriorityList::where('name', 'search_bar_sort_by_temp_closed')->where('type','temp_closed')->first();
        $search_bar_sort_by_temp_closed = $search_bar_sort_by_temp_closed ? $search_bar_sort_by_temp_closed->value : '';

        $zone_id= json_decode($request->header('zoneId'), true);

        $key = isset($request['name']) && $request['name'] != 'null'  ?  explode(' ', $request['name']) : null;

        if(is_array($key)){
            $key = array_filter($key);
            $key = array_values($key);
        }

        $limit = $request['limit']??10;
        $offset = $request['offset']??1;
        $min = $request->query('min_price');
        $max = $request->query('max_price');
        $type = $request->query('type', 'all');
        $is_halal = $request->halal ?? 0;
        // The search screen sends this as `category_id=[19]`; a bare `category_id=19` still works.
        $category_ids = Helpers::filter_id_list($request->query('category_id'));
        $query = Food::Active()->type($type)
            ->when(isset($is_halal) && $is_halal==1,function($query){
                $query->where('is_halal',1);
            })

            ->when($request->restaurant_id, function ($query) use ($request) {
                $query->where('restaurant_id', $request->restaurant_id);
            }, function ($query) use ($zone_id) {
                $query->whereHas('restaurant', function ($q) use ($zone_id) {
                    $q->whereIn('zone_id', $zone_id);
                });
            })

            ->select(['food.*'])
            ->selectSub(function ($subQuery) {
                $subQuery->selectRaw('active as temp_available')
                    ->from('restaurants')
                    ->whereColumn('restaurants.id', 'food.restaurant_id');
            }, 'temp_available')
            ->selectSub(function ($subQuery) {
                $subQuery->selectRaw('IF(((select count(*) from `restaurant_schedule` where `restaurants`.`id` = `restaurant_schedule`.`restaurant_id` and `restaurant_schedule`.`day` = ? and `restaurant_schedule`.`opening_time` < ? and `restaurant_schedule`.`closing_time` > ?) > 0), true, false) as open', [now()->dayOfWeek, now()->format('H:i:s'), now()->format('H:i:s')])
                    ->from('restaurants')
                    ->whereColumn('restaurants.id', 'food.restaurant_id');
            }, 'open')
            ->when($category_ids, function($query)use($category_ids){
                $query->whereHas('category',function($q)use($category_ids){
                    return $q->whereIn('id', $category_ids)->orWhereIn('parent_id', $category_ids);
                });
            })


            ->when($min && $max, function($query)use($min,$max){
                $query->whereBetween('price',[$min,$max]);
            })
            ->when($request->new == 1, function($query){
                return $query->latest();
            })
            ->when( $request->popular == 1, function($query){
                return $query->popular();
            })
            ->applyRating($request)

            // The item's own rate only, the same definition the `discounted` chip uses.
            ->when($request->discounted == 1, function($query) use ($zone_id) {
                return $query->itemDiscounted((array) $zone_id);
            })

            ->when(isset($key) , function($q) use($key){
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                    $q->orWhereHas('translations',function($query)use($key){
                        $query->where(function($q)use($key){
                            foreach ($key as $value) {
                                $q->where('value', 'like', "%{$value}%");
                            };
                        });
                    });
                    $q->orWhereHas('tags',function($query)use($key){
                        $query->where(function($q1)use($key){
                            foreach ($key as $value) {
                                $q1->where('tag', 'like', "%{$value}%");
                            };
                        });
                    });
                    $q->orWhereHas('restaurant.cuisine',function($query) use($key){
                        $query->where(function($q2)use($key){
                            foreach ($key as $value) {
                                $q2->where('name', 'like', "%{$value}%");
                            };
                        });
                    });

                    $q->orWhereHas('allergies',function($query)use($key){
                        $query->where(function($q)use($key){
                            foreach ($key as $value) {
                                $q->where('allergy', 'like', "%{$value}%");
                            };
                        });
                    });
                    $q->orWhereHas('nutritions',function($query)use($key){
                        $query->where(function($q)use($key){
                            foreach ($key as $value) {
                                $q->where('nutrition', 'like', "%{$value}%");
                            };
                        });
                    });


                });
            })

            ->when(isset($request->sort_by) && ($request->sort_by == 'asc' || $request->sort_by == 'desc'), function($query)use($request){
                            return $query->orderBy('name' , $request->sort_by);
                        })
            ->when(isset($request->sort_by) && ( $request->sort_by == 'low') , function($query)use($request){
                            return $query->orderBy('price' ,'asc', );
                        })
            ->when(isset($request->sort_by) && ( $request->sort_by == 'high') , function($query)use($request){
                            return $query->orderBy('price' ,'desc', );
                        })

            ->orderByRaw("FIELD(name, ?) DESC", [$request['name']]);

        // Chips that ask for a particular order rather than a particular subset. applyFilters()
        // adds their ORDER BY after this block, so a default `latest()` sitting here decided the
        // whole listing and the chip only broke ties that never happened - Top rated and New
        // arrivals came back newest-first. Same reason the restaurant listing skips its own
        // popularity tiebreak when a chip is in play.
        $ordering_chip_requested = (bool) array_intersect(
            ['popular', 'new_arrivals', 'top_rated'],
            (array) ($additional_data['filter_by'] ?? [])
        );

        if ((!isset($additional_data['sort_by']) || $additional_data['sort_by'] == 'default')) {
            if ($search_bar_default_status == '1'){
                if (! $ordering_chip_requested) {
                    $query = $query->latest();
                }
            }elseif ($search_bar_default_status == '0'){
                $time = Carbon::now()->toTimeString();

                if($search_bar_sort_by_unavailable == 'remove'){
                    $query = $query->available($time);
                }elseif($search_bar_sort_by_unavailable == 'last'){
                    $query = $query->orderBy(DB::raw("CASE WHEN available_time_starts <= '$time' AND available_time_ends >= '$time' THEN 0 ELSE 1 END"));
                }

                if($search_bar_sort_by_temp_closed == 'remove'){
                    $query = $query->having('temp_available', '>', 0);
                }elseif($search_bar_sort_by_temp_closed == 'last'){
                    $query = $query->orderByDesc('temp_available');
                }
            }
        }
        // Personalization: order search results toward the customer's preferences (primary sort, no-op when off / guest).
        $query = \App\CentralLogics\PersonalizationService::applyItemPersonalization($query, auth('api')->id());

        $products = $query
        ->applyFilters($additional_data)
        ->applySorting($additional_data['sort_by'])
        ->applyPriceRange($request)
        ->paginate($limit, ['*'], 'page', $offset);

        $prices = Food::Active()
            ->type($type)
            ->whereHas('restaurant', function($q) use ($zone_id) {
                $q->whereIn('zone_id', $zone_id);
            })
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        $data =  [
            'total_size' => $products->total(),
            'min_price' => (float)$prices?->min_price,
            'max_price' => (float)$prices?->max_price,
            'limit' => $limit,
            'offset' => $offset,
            'products' => $products->items()
        ];

        $data['products'] = Helpers::product_data_formatting(data:$data['products'],multi_data: true, trans:false,local: app()->getLocale());

        // Logged here rather than at the top of the method because trending only counts searches
        // that found something, and the result count is not known until the query has run.
        $this->logSearch($request, $request['name'] ?? null, (int) $data['total_size']);

        return response()->json($data, 200);
    }

    /** Feed the trending-searches rail. Guests count too, keyed by their guest_id. */
    private function logSearch(Request $request, ?string $keyword, int $resultCount): void
    {
        app(\App\Services\TrendingSearchService::class)->log(
            keyword: $keyword,
            userId: auth('api')->id(),
            guestId: $request->guest_id,
            zoneId: (string) $request->header('zoneId'),
            resultCount: $resultCount,
        );
    }

    public function get_popular_products(Request $request)
    {
        Helpers::getZoneIds($request);
        $cuisine_id = $request->query('cuisine_id') ?? $request->query('cuisine');
        if($cuisine_id && !is_array($cuisine_id)){
            $cuisine_id = json_decode($cuisine_id, true);
        }

        $additional_data=[
            'sort_by' => $request->query('sort_by') ?? 'default',
            'filter_by' => $request->query('filter_by', []) ?? [],
            'cuisine_id' => $cuisine_id ?? [],
            'order_type' => $request->query('order_type'),
        ];

        $type = $request->query('type', 'all');
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');
        $zone_id= json_decode($request->header('zoneId'), true);
        $products = ProductLogic::popular_products(zone_id:$zone_id, request:$request, type: $type,longitude:$longitude,latitude:$latitude,additional_data:$additional_data);
        $products['products'] = Helpers::product_data_formatting(data:$products['products'], multi_data:true, trans:false, local:app()->getLocale());
        return response()->json($products, 200);
    }

    public function get_most_reviewed_products(Request $request)
    {
                Helpers::getZoneIds($request);
        $cuisine_id = $request->query('cuisine_id') ?? $request->query('cuisine');
        if($cuisine_id && !is_array($cuisine_id)){
            $cuisine_id = json_decode($cuisine_id, true);
        }

        $additional_data=[
            'sort_by' => $request->query('sort_by') ?? 'default',
            'filter_by' => $request->query('filter_by', []) ?? [],
            'cuisine_id' => $cuisine_id ?? [],
            'order_type' => $request->query('order_type'),
        ];

        $type = $request->query('type', 'all');
        $zone_id= json_decode($request->header('zoneId'), true);
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');
        $products = ProductLogic::most_reviewed_products(zone_id:$zone_id, request:$request, type:$type,longitude:$longitude,latitude:$latitude,additional_data:$additional_data);
        $products['products'] = Helpers::product_data_formatting(data:$products['products'],multi_data: true, trans:false, local:app()->getLocale());
        return response()->json($products, 200);
    }

    /**
     * Offers screen, Food tab: every discounted item near the customer.
     *
     * Mirrors mart's ItemController@getOfferItems. `total_size` is what the header counts
     * ("420 Result For"), and the same number scoped by `search` ("35 Result For 'Burger'").
     */
    public function getOfferItems(Request $request)
    {
        $products = ProductLogic::get_offer_items(additional_data: $this->offerInputs($request));
        $products['products'] = ProductLogic::format_offer_items($products['products'], auth('api')->id());

        return response()->json($products, 200);
    }

    /**
     * Offers screen, Restaurants tab: restaurants running an offer, each with its discounted
     * food underneath. Mirrors mart's ItemController@getOfferStores.
     */
    public function getOfferRestaurants(Request $request)
    {
        $inputs = $this->offerInputs($request);
        $data = RestaurantLogic::get_offer_restaurants(additional_data: $inputs);

        $restaurants = $data['restaurants'];
        $ids = collect($restaurants)->pluck('id')->all();

        $data['restaurants'] = RestaurantLogic::format_restaurants_for_listing($restaurants, [
            'advertised_ids' => $data['advertised_ids'],
            // Five at most, same cap as every other rail's strip, and read through the same
            // filters the list was built with so the card cannot contradict them.
            'top_items' => RestaurantLogic::offerItemsByRestaurantIds($ids, $this->stripLimit($request), $restaurants, $inputs),
        ]);

        unset($data['advertised_ids']);

        return response()->json($data, 200);
    }

    /** How many foods ride in the strip under a restaurant: 1 to 5, defaulting to 5. */
    private function stripLimit(Request $request): int
    {
        $requested = $request->query('item_limit');

        return is_numeric($requested) ? min(5, max(1, (int) $requested)) : 5;
    }

    /**
     * Everything the Offers screen's filter sheet can send, read once for both tabs: price range,
     * type and halal, the rating radios, categories, cuisines and the sort chips.
     */
    private function offerInputs(Request $request): array
    {
        Helpers::getZoneIds($request);

        // The filter sheet sends a list as `cuisine_id=[3]`, `cuisine_id[]=3` or `3,15`, and a
        // single chip as a bare `cuisine_id=3`. That last one used to be dropped on the floor:
        // json_decode('3') answers the integer 3, which is not an array, so the guard below
        // turned it into an empty list and the filter did nothing at all. A bare slug survived
        // only because json_decode() fails on it and the comma split caught it.
        $decode = function ($value) {
            if ($value === null || $value === '' || $value === []) {
                return [];
            }

            if (! is_array($value)) {
                $decoded = json_decode((string) $value, true);

                $value = match (true) {
                    is_array($decoded) => $decoded,
                    $decoded !== null => [$decoded],
                    default => array_map('trim', explode(',', trim((string) $value, '[]'))),
                };
            }

            // Ids and slugs both ride through here, and so do the filter_by chips, so nothing
            // is cast - only the empties are dropped.
            return array_values(array_filter($value, fn ($item) => $item !== null && $item !== ''));
        };

        return [
            'zone_id' => json_decode($request->header('zoneId'), true),
            'longitude' => $request->header('longitude') ?? 0,
            'latitude' => $request->header('latitude') ?? 0,
            'limit' => $request->query('limit', 20),
            'offset' => $request->query('offset', 1),
            'type' => $request->query('type', 'all') ?? 'all',
            'halal' => $request->query('halal'),
            'search' => $request->query('search') ?? $request->query('name'),
            'user_id' => auth('api')->id(),
            'category_ids' => $decode($request->query('category_ids')),
            'cuisine_id' => $decode($request->query('cuisine_id') ?? $request->query('cuisine')),
            'filter_by' => $decode($request->query('filter_by')),
            'order_type' => $request->query('order_type'),
            'sort_by' => $request->query('sort_by'),
            'request' => $request,
        ];
    }

    public function get_product($id)
    {
        try {
            $product = ProductLogic::get_product($id , request()?->campaign ? true : false);
            // Personalization: record an item_view signal for logged-in users (skip campaign items, which are not Food rows).
            if ($product && !request()?->campaign && auth('api')->id()) {
                \App\CentralLogics\PersonalizationService::recordItemAction((int) auth('api')->id(), (int) $product->id, 'item_view');
            }
            $product = Helpers::product_data_formatting(data:$product, multi_data:false, trans:false, local:app()->getLocale());
            // What other customers actually picked, most ordered first, so the app can lead with
            // the popular size rather than the first one configured. Empty for an item with no
            // variations or no order history.
            $product['recommended_variations'] = ProductLogic::recommended_variations($product);
            return response()->json($product, 200);
        } catch (\Exception $e) {
            info($e->getMessage());
            return response()->json([
                'errors' => ['code' => 'product-001', 'message' => translate('messages.not_found')]
            ], 404);
        }
    }

    public function get_related_products($id)
    {
        if (Food::find($id)) {
            $products = ProductLogic::get_related_products(product_id:$id);
            $products = Helpers::product_data_formatting(data:$products,multi_data: true, trans:false, local:app()->getLocale());
            return response()->json($products, 200);
        }
        return response()->json([
            'errors' => ['code' => 'product-001', 'message' => translate('messages.not_found')]
        ], 404);
    }

    public function get_set_menus()
    {
        try {
            $products = Helpers::product_data_formatting(data:Food::active()->with(['rating'])->where(['set_menu' => 1, 'status' => 1])->get(),multi_data: true, trans:false, local:app()->getLocale());
            return response()->json($products, 200);
        } catch (\Exception $e) {
            info($e->getMessage());
            return response()->json([
                'errors' => ['code' => 'product-001', 'message' => 'Set menu not found!']
            ], 404);
        }
    }

    public function get_product_reviews($food_id)
    {
        $reviews = Review::with(['customer:id,f_name,l_name,image'])->where(['food_id' => $food_id])->active()->get();
        $food=  Food::where(['id' => $food_id])->first();

          try {
                $reviewsInfo = $food->rating()->first();
            } catch (\Exception $e) {
                $reviewsInfo = null;
            }

            $data['rating_count'] = (int) $reviewsInfo?->rating_count ?? 0;
            $data['avg_rating'] = (float) $reviewsInfo?->average ?? 0;
            $data['rating'] =ProductLogic::get_rating($reviews);
            $data['reviews'] = $reviews;

        return response()->json($data, 200);
    }

    public function get_product_rating($id)
    {
        try {
            $product = Food::find($id);
            $overallRating = ProductLogic::get_overall_rating(reviews:$product->reviews);
            return response()->json(floatval($overallRating[0]), 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 403);
        }
    }

    public function submit_product_review(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'food_id' => 'required',
            'order_id' => 'required',
            'comment' => 'nullable',
            'rating' => 'required|numeric|max:5',
            'attachment.*' => 'nullable|max:2048',
        ]);

        $product = Food::find($request->food_id);
        if (isset($product) == false) {
            $validator->errors()->add('food_id', translate('messages.food_not_found'));
        }

        $multi_review = Review::where(['food_id' => $request->food_id, 'user_id' => $request?->user()?->id, 'order_id'=>$request->order_id])->first();
        if (isset($multi_review)) {
            return response()->json([
                'errors' => [
                    ['code'=>'review','message'=> translate('messages.already_submitted')]
                ]
            ], 403);
        } else {
            $review = new Review;
        }

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $image_array = [];
        if (!empty($request->file('attachment'))) {
            foreach ($request->file('attachment') as $image) {
                if ($image != null) {
                    if (!Storage::disk('public')->exists('review')) {
                        Storage::disk('public')->makeDirectory('review');
                    }
                    array_push($image_array, Storage::disk('public')->put('review', $image));
                }
            }
        }

        $review->user_id = $request?->user()?->id;
        $review->food_id = $request->food_id;
        $review->order_id = $request->order_id;
        $review->comment = $request->comment;
        $review->rating = $request->rating;
        $review->attachment = json_encode($image_array);
        $review->save();

        // Personalization: a review is a strong positive signal for the reviewed food.
        if ($request?->user()?->id) {
            \App\CentralLogics\PersonalizationService::recordItemAction((int) $request->user()->id, (int) $request->food_id, 'review');
        }

        if($product->restaurant)
        {
            $restaurant_rating = RestaurantLogic::update_restaurant_rating(ratings:$product?->restaurant?->rating, product_rating:$request->rating);
            $product->restaurant->rating = $restaurant_rating;
            $product?->restaurant?->save();
        }

        $product->rating = ProductLogic::update_rating(ratings:$product->rating, product_rating:$request->rating);
        $product->avg_rating = ProductLogic::get_avg_rating(rating:json_decode($product->rating, true));
        $product?->save();
        $product->increment('rating_count');

        return response()->json(['message' => translate('messages.review_submited_successfully')], 200);
    }


    public function get_recommended(Request $request)
    {
        Helpers::getZoneIds($request);
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $cuisine_id = $request->query('cuisine_id') ?? $request->query('cuisine');
        if($cuisine_id && !is_array($cuisine_id)){
            $cuisine_id = json_decode($cuisine_id, true);
        }

        $additional_data = [
            'sort_by' => $request->query('sort_by') ?? 'default',
            'filter_by' => $request->query('filter_by', []) ?? [],
            'cuisine_id' => $cuisine_id ?? [],
            'order_type' => $request->query('order_type', []) ?? [],
        ];

        $type = $request->query('type', 'all');
        $key = explode(' ', $request['name']);
        $zone_id= json_decode($request->header('zoneId'), true);
        $products = ProductLogic::recommended_products(
            zone_id:$zone_id,
            restaurant_id:$request->restaurant_id,
            request:$request,
            type:$type,
            name:$key,
            limit:$request['limit'],
            offset:$request['offset'],
            additional_data:$additional_data
        );
        $products['products'] = Helpers::product_data_formatting(data:$products['products'],multi_data: true, trans:false, local:app()->getLocale());
        return response()->json($products, 200);
    }




    public function food_or_restaurant_search(Request $request){

        Helpers::getZoneIds($request);
        if (!$request->hasHeader('longitude') || !$request->hasHeader('latitude')) {
            $errors = [];
            array_push($errors, ['code' => 'longitude-latitude', 'message' => translate('messages.longitude-latitude_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        $zone_id= json_decode($request->header('zoneId'), true);
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        // Personalization: record the combined food/restaurant search keyword for logged-in users.
        if (auth('api')->id()) {
            \App\CentralLogics\PersonalizationService::recordSearchAction((int) auth('api')->id(), $request->name);
        }
        $key = explode(' ', $request->name);

        $foods = Food::active()->whereHas('restaurant', function($q)use($zone_id){
            $q->whereIn('zone_id', $zone_id)->Weekday();
        })
        ->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
            $q->orWhereHas('translations',function($query)use($key){
                $query->where(function($q)use($key){
                    foreach ($key as $value) {
                        $q->where('value', 'like', "%{$value}%");
                    };
                });
            });
            $q->orWhereHas('tags',function($query)use($key){
                $query->where(function($q)use($key){
                    foreach ($key as $value) {
                        $q->where('tag', 'like', "%{$value}%");
                    };
                });
            });
            $q->orWhereHas('allergies',function($query)use($key){
                $query->where(function($q)use($key){
                    foreach ($key as $value) {
                        $q->where('allergy', 'like', "%{$value}%");
                    };
                });
            });
            $q->orWhereHas('nutritions',function($query)use($key){
                $query->where(function($q)use($key){
                    foreach ($key as $value) {
                        $q->where('nutrition', 'like', "%{$value}%");
                    };
                });
            });
        })
        ->orderByRaw("FIELD(name, ?) DESC", [$request->name])
        ->limit(50)
        ->get(['id','name','image']);

        $restaurants = Restaurant::withOpen($longitude,$latitude)->whereIn('zone_id', $zone_id)->weekday()
        ->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
            $q->orwhereHas('cuisine', function ($query) use ($key){
                $query->where(function($q)use($key){
                    foreach ($key as $value) {
                        $q->where('name', 'like', "%{$value}%");
                    };
                });
            });
        })
        ->active()
        ->limit(50)
        ->orderByRaw("FIELD(name, ?) DESC", [$request->name])
        ->select(['id','name','logo'])
        ->get();

        $this->logSearch($request, $request->name, $foods->count() + $restaurants->count());

        return [
            'foods' => $foods,
            'restaurants' => $restaurants
        ];

    }

    public function get_restaurant_popular_products(Request $request)
    {
               Helpers::getZoneIds($request);

        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $type = $request->query('type', 'all');
        $key = explode(' ', $request['name']);

        $zone_id= json_decode($request->header('zoneId'), true);
        $products = ProductLogic::get_restaurant_popular_products(zone_id:$zone_id,restaurant_id: $request->restaurant_id, type: $type,name:$key, request:$request);
        $products = Helpers::product_data_formatting(data:$products,multi_data: true, trans:false, local:app()->getLocale());
        return response()->json($products, 200);
    }


    public function recommended_most_reviewed(Request $request)
    {
                Helpers::getZoneIds($request);
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $type = $request->query('type', 'all');
        $key = explode(' ', $request['name']);

        $zone_id= json_decode($request->header('zoneId'), true);
        $products = ProductLogic::recommended_most_reviewed(zone_id:$zone_id,type: $type,name:$key, request:$request, restaurant_id: $request->restaurant_id);


        $products = Helpers::product_data_formatting(data:$products,multi_data: true, trans:false, local:app()->getLocale());
        return response()->json($products, 200);
    }

    public function getAllergyNameList(){
        $names= Allergy::select(['allergy'])->pluck('allergy');
        return response()->json($names, 200);
    }
    public function getNutritionNameList(){
        $names= Nutrition::select(['nutrition'])->pluck('nutrition');
        return response()->json($names, 200);
    }
}
