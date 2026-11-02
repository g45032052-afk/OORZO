<?php

namespace App\CentralLogics;

use App\Models\Food;
use App\Models\ItemCampaign;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductLogic
{
    public static function get_product($id , $campaign=false)
    {
        if($campaign == true){
            return ItemCampaign::with('foodSeoData')->find($id);
        }
        return Food::active()
                    ->with([
                        'foodSeoData',
                        'reviews' => function ($query) {
                            $query->select('id', 'food_id', 'rating', 'comment', 'user_id', 'created_at')
                                ->where('status', 1);
                        }
                    ])
                    ->when(is_numeric($id),function ($qurey) use($id){
                        $qurey-> where('id', $id);
                    })
                    ->when(!is_numeric($id),function ($qurey) use($id){
                        $qurey-> where('slug', $id);
                    })
                    ->first();
    }

    public static function get_latest_products($limit, $offset, $restaurant_id, $category_id, $request, $type='all',$additional_data = [], $zone_id = null)
    {
        $paginator = Food::active()
                ->applyFilters($additional_data)
                ->applySorting($additional_data['sort_by'])
                ->applyRating($request)
                ->applyPriceRange($request)
                ->type($type);
        // The storefront sends this as `category_id=[16,55]`; a bare `category_id=16` still
        // works. Matching the raw list against a single id silently matched nothing and then
        // - because the string casts to 0 - matched `parent_id = 0`, i.e. every top level
        // category, so the filter returned almost the whole catalogue and read as no filter
        // at all. Same normalisation and same match the search endpoint uses.
        $category_ids = Helpers::filter_id_list($category_id);

        if($category_ids)
        {
            $paginator = $paginator->whereHas('category',function($q)use($category_ids){
                return $q->whereIn('id', $category_ids)->orWhereIn('parent_id', $category_ids);
            });
        }
        if($restaurant_id)
        {
            $paginator = $paginator->where('restaurant_id', $restaurant_id);
        }
        elseif(!empty($zone_id))
        {
            // No restaurant given: fall back to every food in the customer's zones.
            $paginator = $paginator->whereHas('restaurant',function($q)use($zone_id){
                $q->whereIn('zone_id', (array) $zone_id);
            });
        }
        // Personalization: order latest items toward the customer's preferences (no-op for guests / toggle off).
        $paginator = \App\CentralLogics\PersonalizationService::applyItemPersonalization($paginator, auth('api')->id());
        $paginator = $paginator->latest()->paginate($limit, ['*'], 'page', $offset);

        return [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $paginator->items()
        ];
    }

    public static function get_related_products($product_id)
    {
        $product = Food::find($product_id);
        $query = Food::active()
        ->whereHas('restaurant', function($query){
            $query->Weekday();
        })
        ->where('category_ids', $product->category_ids)
        ->where('id', '!=', $product->id);
        // Personalization: order related items toward the customer's preferences.
        $query = \App\CentralLogics\PersonalizationService::applyItemPersonalization($query, auth('api')->id());
        return $query->limit(10)->get();
    }

    public static function search_products($name, $zone_id, $limit = 10, $offset = 1)
    {
        $key = explode(' ', $name);
        $paginator = Food::active()->whereHas('restaurant', function($q)use($zone_id){
            $q->whereIn('zone_id', $zone_id)->Weekday();
        })->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
        });
        // Personalization: order search results toward the customer's preferences.
        $paginator = \App\CentralLogics\PersonalizationService::applyItemPersonalization($paginator, auth('api')->id());
        $paginator = $paginator->paginate($limit, ['*'], 'page', $offset);

        return [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $paginator->items()
        ];
    }

    public static function popular_products($zone_id, $request, $type='all', $longitude=0, $latitude=0, $additional_data=[], $limit = null, $offset = null)
    {
        $popular_food_default_status = \App\Models\BusinessSetting::where('key', 'popular_food_default_status')->first();
        $popular_food_default_status = $popular_food_default_status ? $popular_food_default_status->value : 1;
        $popular_food_sort_by_general = \App\Models\PriorityList::where('name', 'popular_food_sort_by_general')->where('type','general')->first();
        $popular_food_sort_by_general = $popular_food_sort_by_general ? $popular_food_sort_by_general->value : '';
        $popular_food_sort_by_unavailable = \App\Models\PriorityList::where('name', 'popular_food_sort_by_unavailable')->where('type','unavailable')->first();
        $popular_food_sort_by_unavailable = $popular_food_sort_by_unavailable ? $popular_food_sort_by_unavailable->value : '';
        $popular_food_sort_by_temp_closed = \App\Models\PriorityList::where('name', 'popular_food_sort_by_temp_closed')->where('type','temp_closed')->first();
        $popular_food_sort_by_temp_closed = $popular_food_sort_by_temp_closed ? $popular_food_sort_by_temp_closed->value : '';

        $prices = Food::Active()
            ->type($type)
            ->whereHas('restaurant', function($q) use ($zone_id) {
                $q->whereIn('zone_id', $zone_id);
            })
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        if($limit != null && $offset != null)
        {
            if ($popular_food_default_status == '1'){
                $paginator = Food::whereHas('restaurant', function($q)use($zone_id){
                    $q->whereIn('zone_id', $zone_id)->Weekday();
                })->active()->type($type)->has('reviews')
                ->applyFilters($additional_data)
                ->applySorting($additional_data['sort_by'])
                ->applyRating($request)
                ->applyPriceRange($request)
                ->popular();
                // Personalization: lift the customer's preferred items to the top (primary sort, no-op when off / guest).
                $paginator = \App\CentralLogics\PersonalizationService::applyItemPersonalization($paginator, auth('api')->id())
                ->paginate($limit, ['*'], 'page', $offset);
            }

            if ($popular_food_default_status == '0'){
                $time = Carbon::now()->toTimeString();

                $query = Food::Active()->with('restaurant')
                    ->select(['food.*'])
                    ->type($type)
                    // ->leftJoin('restaurants', 'food.restaurant_id', '=', 'restaurants.id')
                    ->whereHas('restaurant', function($q) use ($zone_id) {
                        $q->whereIn('zone_id', $zone_id)->Weekday();
                    })
                    ->selectSub(function ($subQuery) {
                        $subQuery->selectRaw('active as temp_available')
                            ->from('restaurants')
                            ->whereColumn('restaurants.id', 'food.restaurant_id');
                    }, 'temp_available')
                    ->selectSub(function ($subQuery) {
                        $subQuery->selectRaw('IF(((select count(*) from `restaurant_schedule` where `restaurants`.`id` = `restaurant_schedule`.`restaurant_id` and `restaurant_schedule`.`day` = ? and `restaurant_schedule`.`opening_time` < ? and `restaurant_schedule`.`closing_time` > ?) > 0), true, false) as open', [now()->dayOfWeek, now()->format('H:i:s'), now()->format('H:i:s')])
                            ->from('restaurants')
                            ->whereColumn('restaurants.id', 'food.restaurant_id');
                    }, 'open');
                // Personalization: reorder toward the customer's preferences
                // (no-op when the toggle is off / customer is a guest).
                $query = \App\CentralLogics\PersonalizationService::applyItemPersonalization($query, auth('api')->id());
                $query = $query->applyFilters($additional_data)->applySorting($additional_data['sort_by']);

                if($popular_food_sort_by_unavailable == 'remove'){
                    $query = $query->available($time);
                }elseif($popular_food_sort_by_unavailable == 'last'){
                    $query = $query->orderBy(DB::raw("CASE WHEN available_time_starts <= '$time' AND available_time_ends >= '$time' THEN 0 ELSE 1 END"));
                }

                if($popular_food_sort_by_temp_closed == 'remove'){
                    $query = $query->having('temp_available', '>', 0);
                }elseif($popular_food_sort_by_temp_closed == 'last'){
                    $query = $query->orderByDesc('temp_available');
                }

                if ($popular_food_sort_by_general == 'nearest_first') {
                    $query = $query->selectSub(function ($subQuery) use ($longitude, $latitude) {
                        $subQuery->selectRaw('ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) as distance', [$longitude, $latitude])
                            ->from('restaurants')
                            ->whereColumn('restaurants.id', 'food.restaurant_id');
                    }, 'distance')
                        ->orderBy('distance');
                } elseif ($popular_food_sort_by_general == 'rating') {
                    $query = $query->orderByDesc('avg_rating');
                } elseif ($popular_food_sort_by_general == 'review_count') {
                    $query = $query->withCount('reviews')->orderByDesc('reviews_count');
                } elseif ($popular_food_sort_by_general == 'order_count') {
                    $query = $query->orderByDesc('order_count');
                } elseif ($popular_food_sort_by_general == 'a_to_z') {
                    $query = $query->orderBy('name');
                } elseif ($popular_food_sort_by_general == 'z_to_a') {
                    $query = $query->orderByDesc('name');
                }

                $paginator = $query
                ->applyRating($request)
                ->applyPriceRange($request)
                ->paginate($limit, ['*'], 'page', $offset);

            }

            return [
                'total_size' => $paginator->total(),
                'min_price' => (float)$prices?->min_price,
                'max_price' => (float)$prices?->max_price,
                'limit' => $limit,
                'offset' => $offset,
                'products' => $paginator->items()
            ];
        }

        if ($popular_food_default_status == '1'){
            $paginator = Food::whereHas('restaurant', function($q)use($zone_id){
                $q->whereIn('zone_id', $zone_id)->Weekday();
            })->active()->type($type)->has('reviews')
            ->applyFilters($additional_data)
            ->applySorting($additional_data['sort_by'])
            ->applyRating($request)
            ->applyPriceRange($request)
            ->popular();
            // Personalization: lift the customer's preferred items to the top (primary sort, no-op when off / guest).
            $paginator = \App\CentralLogics\PersonalizationService::applyItemPersonalization($paginator, auth('api')->id())
            ->limit(50)->get();
        }

        if ($popular_food_default_status == '0'){
            $time = Carbon::now()->toTimeString();

            $query = Food::Active()->with('restaurant')
                ->select(['food.*'])
                ->type($type)
                // ->leftJoin('restaurants', 'food.restaurant_id', '=', 'restaurants.id')
                ->whereHas('restaurant', function($q) use ($zone_id) {
                    $q->whereIn('zone_id', $zone_id)->Weekday();
                })
                ->selectSub(function ($subQuery) {
                    $subQuery->selectRaw('active as temp_available')
                        ->from('restaurants')
                        ->whereColumn('restaurants.id', 'food.restaurant_id');
                }, 'temp_available')
                ->selectSub(function ($subQuery) {
                    $subQuery->selectRaw('IF(((select count(*) from `restaurant_schedule` where `restaurants`.`id` = `restaurant_schedule`.`restaurant_id` and `restaurant_schedule`.`day` = ? and `restaurant_schedule`.`opening_time` < ? and `restaurant_schedule`.`closing_time` > ?) > 0), true, false) as open', [now()->dayOfWeek, now()->format('H:i:s'), now()->format('H:i:s')])
                        ->from('restaurants')
                        ->whereColumn('restaurants.id', 'food.restaurant_id');
                }, 'open');
            // Personalization: reorder toward the customer's preferences (primary sort, no-op when off / guest).
            $query = \App\CentralLogics\PersonalizationService::applyItemPersonalization($query, auth('api')->id());
            $query = $query->applyFilters($additional_data)->applySorting($additional_data['sort_by'])->applyPriceRange($request);

            if($popular_food_sort_by_unavailable == 'remove'){
                $query = $query->available($time);
            }elseif($popular_food_sort_by_unavailable == 'last'){
                $query = $query->orderBy(DB::raw("CASE WHEN available_time_starts <= '$time' AND available_time_ends >= '$time' THEN 0 ELSE 1 END"));
            }

            if($popular_food_sort_by_temp_closed == 'remove'){
                $query = $query->having('temp_available', '>', 0);
            }elseif($popular_food_sort_by_temp_closed == 'last'){
                $query = $query->orderByDesc('temp_available');
            }

            if ($popular_food_sort_by_general == 'nearest_first') {
                $query = $query->selectSub(function ($subQuery) use ($longitude, $latitude) {
                    $subQuery->selectRaw('ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) as distance', [$longitude, $latitude])
                        ->from('restaurants')
                        ->whereColumn('restaurants.id', 'food.restaurant_id');
                }, 'distance')
                    ->orderBy('distance');
            } elseif ($popular_food_sort_by_general == 'rating') {
                $query = $query->orderByDesc('avg_rating');
            } elseif ($popular_food_sort_by_general == 'review_count') {
                $query = $query->withCount('reviews')->orderByDesc('reviews_count');
            } elseif ($popular_food_sort_by_general == 'order_count') {
                $query = $query->orderByDesc('order_count');
            } elseif ($popular_food_sort_by_general == 'a_to_z') {
                $query = $query->orderBy('name');
            } elseif ($popular_food_sort_by_general == 'z_to_a') {
                $query = $query->orderByDesc('name');
            }

            $paginator = $query
            ->applyRating($request)
            ->applyPriceRange($request)
            ->limit(50)->get();

        }

        return [
            'total_size' => count($paginator),
            'min_price' => (float)$prices?->min_price,
            'max_price' => (float)$prices?->max_price,
            'limit' => $limit,
            'offset' => $offset,
            'products' => $paginator
        ];

    }


    public static function most_reviewed_products($zone_id, $request, $type='all', $longitude=0, $latitude=0, $additional_data = [], $limit = null, $offset = null)
    {
        $best_reviewed_food_default_status = \App\Models\BusinessSetting::where('key', 'best_reviewed_food_default_status')->first();
        $best_reviewed_food_default_status = $best_reviewed_food_default_status ? $best_reviewed_food_default_status->value : 1;
        $best_reviewed_food_sort_by_general = \App\Models\PriorityList::where('name', 'best_reviewed_food_sort_by_general')->where('type','general')->first();
        $best_reviewed_food_sort_by_general = $best_reviewed_food_sort_by_general ? $best_reviewed_food_sort_by_general->value : '';
        $best_reviewed_food_sort_by_unavailable = \App\Models\PriorityList::where('name', 'best_reviewed_food_sort_by_unavailable')->where('type','unavailable')->first();
        $best_reviewed_food_sort_by_unavailable = $best_reviewed_food_sort_by_unavailable ? $best_reviewed_food_sort_by_unavailable->value : '';
        $best_reviewed_food_sort_by_temp_closed = \App\Models\PriorityList::where('name', 'best_reviewed_food_sort_by_temp_closed')->where('type','temp_closed')->first();
        $best_reviewed_food_sort_by_temp_closed = $best_reviewed_food_sort_by_temp_closed ? $best_reviewed_food_sort_by_temp_closed->value : '';
        $best_reviewed_food_sort_by_rating = \App\Models\PriorityList::where('name', 'best_reviewed_food_sort_by_rating')->where('type','rating')->first();
        $best_reviewed_food_sort_by_rating = $best_reviewed_food_sort_by_rating ? $best_reviewed_food_sort_by_rating->value : '';

        if($limit != null && $offset != null)
        {
            $query = Food::Active()->with('restaurant')
                ->select(['food.*'])
                // ->leftJoin('restaurants', 'food.restaurant_id', '=', 'restaurants.id')
                ->whereHas('restaurant', function($q) use ($zone_id) {
                    $q->whereIn('zone_id', $zone_id)->Weekday();
                })
                ->selectSub(function ($subQuery) {
                    $subQuery->selectRaw('active as temp_available')
                        ->from('restaurants')
                        ->whereColumn('restaurants.id', 'food.restaurant_id');
                }, 'temp_available')
                ->whereHas('reviews', function ($query) {
                    $query->where('status', 1);
                })
                ->withCount('reviews')->type($type)->having('reviews_count', '>', 0);

            if($best_reviewed_food_default_status == '1'){
                $query = $query->orderBy('reviews_count','desc');
            }else {
                $time = Carbon::now()->toTimeString();

                if ($best_reviewed_food_sort_by_unavailable == 'remove') {
                    $query = $query->available($time);
                } elseif ($best_reviewed_food_sort_by_unavailable == 'last') {
                    $query = $query->orderBy(DB::raw("CASE WHEN available_time_starts <= '$time' AND available_time_ends >= '$time' THEN 0 ELSE 1 END"));
                }

                if ($best_reviewed_food_sort_by_temp_closed == 'remove') {
                    $query = $query->having('temp_available', '>', 0);
                } elseif ($best_reviewed_food_sort_by_temp_closed == 'last') {
                    $query = $query->orderByDesc('temp_available');
                }

                if ($best_reviewed_food_sort_by_rating == 'four_plus') {
                    $query = $query->where('avg_rating','>',4);
                } elseif ($best_reviewed_food_sort_by_rating == 'three_half_plus') {
                    $query = $query->where('avg_rating','>',3.5);
                } elseif ($best_reviewed_food_sort_by_rating == 'three_plus') {
                    $query = $query->where('avg_rating','>',3);
                }

                if ($best_reviewed_food_sort_by_general == 'nearest_first') {
                    $query = $query->selectSub(function ($subQuery) use ($longitude, $latitude) {
                        $subQuery->selectRaw('ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) as distance', [$longitude, $latitude])
                            ->from('restaurants')
                            ->whereColumn('restaurants.id', 'food.restaurant_id');
                    }, 'distance')
                        ->orderBy('distance');
                } elseif ($best_reviewed_food_sort_by_general == 'rating') {
                    $query = $query->orderByDesc('avg_rating');
                } elseif ($best_reviewed_food_sort_by_general == 'review_count') {
                    $query = $query->orderByDesc('reviews_count');
                }
            }

            // Personalization: order most-reviewed items toward the customer's preferences (preference leads, review count tiebreaks).
            $query = \App\CentralLogics\PersonalizationService::applyItemPersonalization($query, auth('api')->id());
            $paginator = $query
            ->applyFilters($additional_data)
            ->applySorting($additional_data['sort_by'])
            ->orderByDesc('reviews_count')
            ->applyRating($request)
            ->applyPriceRange($request)
            ->paginate($limit, ['*'], 'page', $offset);

            return [
                'total_size' => $paginator->total(),
                'limit' => $limit,
                'offset' => $offset,
                'products' => $paginator->items()
            ];
        }
        $query = Food::Active()->with('restaurant')
            ->select(['food.*'])
            // ->leftJoin('restaurants', 'food.restaurant_id', '=', 'restaurants.id')
            ->whereHas('restaurant', function($q) use ($zone_id) {
                $q->whereIn('zone_id', $zone_id)->Weekday();
            })
            ->selectSub(function ($subQuery) {
                $subQuery->selectRaw('active as temp_available')
                    ->from('restaurants')
                    ->whereColumn('restaurants.id', 'food.restaurant_id');
            }, 'temp_available')
            ->whereHas('reviews', function ($query) {
                    $query->where('status', 1);
                })
            ->withCount('reviews')->type($type)->having('reviews_count', '>', 0);

        if($best_reviewed_food_default_status == '1'){
            $query = $query->orderBy('reviews_count','desc');
        }else {
            $time = Carbon::now()->toTimeString();

            if ($best_reviewed_food_sort_by_unavailable == 'remove') {
                $query = $query->available($time);
            } elseif ($best_reviewed_food_sort_by_unavailable == 'last') {
                $query = $query->orderBy(DB::raw("CASE WHEN available_time_starts <= '$time' AND available_time_ends >= '$time' THEN 0 ELSE 1 END"));
            }

            if ($best_reviewed_food_sort_by_temp_closed == 'remove') {
                $query = $query->having('temp_available', '>', 0);
            } elseif ($best_reviewed_food_sort_by_temp_closed == 'last') {
                $query = $query->orderByDesc('temp_available');
            }

            if ($best_reviewed_food_sort_by_rating == 'four_plus') {
                $query = $query->where('avg_rating','>',4);
            } elseif ($best_reviewed_food_sort_by_rating == 'three_half_plus') {
                $query = $query->where('avg_rating','>',3.5);
            } elseif ($best_reviewed_food_sort_by_rating == 'three_plus') {
                $query = $query->where('avg_rating','>',3);
            }

            if ($best_reviewed_food_sort_by_general == 'nearest_first') {
                $query = $query->selectSub(function ($subQuery) use ($longitude, $latitude) {
                    $subQuery->selectRaw('ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) as distance', [$longitude, $latitude])
                        ->from('restaurants')
                        ->whereColumn('restaurants.id', 'food.restaurant_id');
                }, 'distance')
                    ->orderBy('distance');
            } elseif ($best_reviewed_food_sort_by_general == 'rating') {
                $query = $query->orderByDesc('avg_rating');
            } elseif ($best_reviewed_food_sort_by_general == 'review_count') {
                $query = $query->orderByDesc('reviews_count');
            }
        }
        // Personalization: order most-reviewed items toward the customer's preferences (preference leads, review count tiebreaks).
        $query = \App\CentralLogics\PersonalizationService::applyItemPersonalization($query, auth('api')->id());
        $paginator = $query
            ->applyFilters($additional_data)
            ->applySorting($additional_data['sort_by'])
            ->orderByDesc('reviews_count')
            ->applyRating($request)
            ->applyPriceRange($request)->limit(50)->get();

        return [
            'total_size' => $paginator->count(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $paginator
        ];

    }

    public static function get_product_review($id)
    {
        $reviews = Review::where('product_id', $id)->get();
        return $reviews;
    }

    public static function get_rating($reviews)
    {
        $rating5 = 0;
        $rating4 = 0;
        $rating3 = 0;
        $rating2 = 0;
        $rating1 = 0;
        foreach ($reviews as $key => $review) {
            if ($review->rating == 5) {
                $rating5 += 1;
            }
            if ($review->rating == 4) {
                $rating4 += 1;
            }
            if ($review->rating == 3) {
                $rating3 += 1;
            }
            if ($review->rating == 2) {
                $rating2 += 1;
            }
            if ($review->rating == 1) {
                $rating1 += 1;
            }
        }
        return [$rating5, $rating4, $rating3, $rating2, $rating1];
    }

    public static function get_avg_rating($rating)
    {
        $total_rating = 0;
        $total_rating += $rating[1];
        $total_rating += $rating[2]*2;
        $total_rating += $rating[3]*3;
        $total_rating += $rating[4]*4;
        $total_rating += $rating[5]*5;

        return $total_rating/array_sum($rating);
    }

    public static function get_overall_rating($reviews)
    {
        $totalRating = count($reviews);
        $rating = 0;
        foreach ($reviews as $key => $review) {
            $rating += $review->rating;
        }
        if ($totalRating == 0) {
            $overallRating = 0;
        } else {
            $overallRating = number_format($rating / $totalRating, 2);
        }

        return [$overallRating, $totalRating];
    }

    public static function format_export_foods($foods)
    {
        $storage = [];
        foreach($foods as $item)
        {
            $category_id = 0;
            $sub_category_id = 0;
            foreach(json_decode($item->category_ids, true) as $key=>$category)
            {
                if($key==0)
                {
                    $category_id = $category['id'];
                }
                else if($key==1)
                {
                    $sub_category_id = $category['id'];
                }
            }
            $storage[] = [
                'Id'=>$item->id,
                'Name'=>$item->name,
                'Description'=>$item->description,
                'Image'=>$item->image,
                'CategoryId'=>$category_id,
                'SubCategoryId'=>$sub_category_id,
                'RestaurantId'=>$item->restaurant_id,
                'Price'=>$item->price,
                'Discount'=>$item->discount,
                'DiscountType'=>$item->discount_type,
                'AvailableTimeStarts'=>$item->available_time_starts,
                'AvailableTimeEnds'=>$item->available_time_ends,
                'Variations'=>$item->variations,
                // 'Addons'=>str_replace(['""','[',']'],'',$item->add_ons),
                'Addons'=>$item->add_ons,
                'Veg'=>$item->veg == 1 ? 'yes' :'no' ,
                'Recommended'=>$item->recommended == 1 ? 'yes' :'no' ,
                'Status'=>$item->status == 1 ? 'active' :'inactive' ,
                'AvgRating'=>round($item->avg_rating ,2),
                'TotalRatingCount'=>$item->rating_count,
                // 'Variations'=>str_replace(['{','}','[',']'],['(',')','',''],$item->variations),
                // 'attributes'=>str_replace(['"','[',']'],'',$item->attributes),
                // 'choice_options'=>str_replace(['{','}'],['(',')'],substr($item->choice_options, 1, -1)),
            ];
        }

        return $storage;
    }

    public static function update_food_ratings()
    {
        try{
            $foods = Food::withOutGlobalScopes()->whereHas('reviews')->with('reviews')->get();
            foreach($foods as $key=>$food)
            {
                $foods[$key]->avg_rating = $food?->reviews?->avg('rating');
                $foods[$key]->rating_count = $food?->reviews?->count();
                foreach($food->reviews as $review)
                {
                    $foods[$key]->rating = self::update_rating($foods[$key]->rating, $review->rating);
                }
                $foods[$key]->save();
            }
        }catch(\Exception $e){
            info($e->getMessage());
            return false;
        }
        return true;
    }

    public static function update_rating($ratings, $product_rating)
    {

        $restaurant_ratings = [1=>0 , 2=>0, 3=>0, 4=>0, 5=>0];
        if(isset($ratings))
        {
            $restaurant_ratings = json_decode($ratings, true);
            $restaurant_ratings[$product_rating] = $restaurant_ratings[$product_rating] + 1;
        }
        else
        {
            $restaurant_ratings[$product_rating] = 1;
        }
        return json_encode($restaurant_ratings);
    }



    public static function recommended_products($zone_id,$restaurant_id, $request, $type='all', $name=null, $limit = null, $offset = null, $additional_data = [])
    {
        $data =[];
        if($limit != null && $offset != null)
        {
            $paginator = Food::where('restaurant_id', $restaurant_id)->whereHas('restaurant', function($q)use($zone_id){
                $q->whereIn('zone_id', $zone_id)->Weekday();
            })
            ->when(isset($name) , function($query)use($name){
                $query->where(function ($q) use ($name) {
                    foreach ($name as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                    $q->orWhereHas('tags',function($query)use($name){
                        $query->where(function($q)use($name){
                            foreach ($name as $value) {
                                $q->where('tag', 'like', "%{$value}%");
                            };
                        });
                    });
                });
            })
            ->active()->type($type)->Recommended()
            ->applyFilters($additional_data)
            ->applySorting($additional_data['sort_by'] ?? ($request?->sort_by ?? 'default'))
            ->applyRating($request)
            ->applyPriceRange($request);
            // Personalization: order recommended items toward the customer's preferences.
            $paginator = \App\CentralLogics\PersonalizationService::applyItemPersonalization($paginator, auth('api')->id())->paginate($limit, ['*'], 'page', $offset);
                $data = $paginator->items();
        }
        else{
            $paginator = Food::where('restaurant_id', $restaurant_id)->active()->type($type)->whereHas('restaurant', function($q)use($zone_id){
                $q->whereIn('zone_id', $zone_id)->Weekday();
            })->Recommended()
            ->when(isset($name) , function($query)use($name){
                $query->where(function ($q) use ($name) {
                    foreach ($name as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                    $q->orWhereHas('tags',function($query)use($name){
                        $query->where(function($q)use($name){
                            foreach ($name as $value) {
                                $q->where('tag', 'like', "%{$value}%");
                            };
                        });
                    });
                });
            })
            ->limit(50)
            ->applyFilters($additional_data)
            ->applySorting($additional_data['sort_by'] ?? ($request?->sort_by ?? 'default'))
            ->applyRating($request)
            ->applyPriceRange($request);
            // Personalization: order recommended items toward the customer's preferences.
            $paginator = \App\CentralLogics\PersonalizationService::applyItemPersonalization($paginator, auth('api')->id())->get();
            $data =$paginator;
        }

        return [
            'total_size' => $paginator->count(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $data
        ];
    }


    public static function format_export_addons($addons)
    {
        $storage = [];
        foreach($addons as $item)
        {
            $storage[] = [
                'Id'=>$item->id,
                'Name'=>$item->name,
                'Price'=>$item->price,
                'RestaurantId'=>$item->restaurant_id,
                'Status'=>$item->status == 1 ? 'active' : 'inactive'
            ];
        }

        return $storage;
    }

    public static function get_restaurant_popular_products($zone_id, $restaurant_id, $type='all',$name=null, $request = null)
    {
        $products = Food::active()->type($type)->where('restaurant_id',$restaurant_id)->whereHas('restaurant', function($q)use($zone_id){
            $q->whereIn('zone_id', $zone_id)->Weekday();
        })->has('reviews')
        ->applySorting($request?->sort_by)
        ->popular()
        ->when(isset($name) , function($query)use($name){
            $query->where(function ($q) use ($name) {
                foreach ($name as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }
                $q->orWhereHas('tags',function($query)use($name){
                    $query->where(function($q)use($name){
                        foreach ($name as $value) {
                            $q->where('tag', 'like', "%{$value}%");
                        };
                    });
                });
            });
        })
        ->limit(50)->applyRating($request)
        ->applyPriceRange($request);
        // Personalization: surface this restaurant's popular items toward the customer's preferences.
        $products = \App\CentralLogics\PersonalizationService::applyItemPersonalization($products, auth('api')->id())->get();
        return  $products;
    }

    public static function recommended_most_reviewed($zone_id, $type='all',$name =null, $request = null, $restaurant_id=null, $limit = null, $offset = null)
    {
            $paginator = Food::where('restaurant_id',$restaurant_id)->whereHas('restaurant', function($q)use($zone_id){
                $q->whereIn('zone_id', $zone_id)->Weekday();
            })
            ->has('reviews')->withCount('reviews')
            ->orderBy('reviews_count','desc')
            ->orwhere(function($query) use($restaurant_id){
                $query->where('recommended',1)->where('restaurant_id',$restaurant_id);
            })
            ->orderBy('recommended','desc')
            ->when(isset($name) , function($query)use($name){
                $query->where(function ($q) use ($name) {
                    foreach ($name as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                    $q->orWhereHas('tags',function($query)use($name){
                        $query->where(function($q)use($name){
                            foreach ($name as $value) {
                                $q->where('tag', 'like', "%{$value}%");
                            };
                        });
                    });
                });
            })
            ->active()->type($type)
            ->applySorting($request?->sort_by)
            ->applyRating($request)
            ->applyPriceRange($request)
            ->limit(50);
            // Personalization: order recommended most-reviewed items toward the customer's preferences.
            $paginator = \App\CentralLogics\PersonalizationService::applyItemPersonalization($paginator, auth('api')->id())->get();
            return  $paginator;

    }

    /**
     * The variants of this item that customers actually order, most ordered first.
     *
     * Counted from what was really bought - the variation snapshot each order detail stores -
     * rather than from variation_options.sell_count, which looks like a popularity counter but
     * is not: addonAndVariationStockCheck() only increments it for options with limited stock,
     * so every unlimited option sits at 0 forever.
     *
     * Weighted by quantity, because two Smalls on one line is two people's worth of evidence,
     * and measured over the most recent $scan lines so a hot item does not pay for its whole
     * history on every details request.
     *
     * Only options the item still offers are returned, so a variant the restaurant has since
     * removed can never be recommended.
     */
    public static function recommended_variations($item, int $limit = 10, int $scan = 1000): array
    {
        $variations = is_string($item['variations'] ?? null)
            ? json_decode($item['variations'], true)
            : ($item['variations'] ?? []);

        if (! is_array($variations) || empty($variations)) {
            return [];
        }

        $column = ($item instanceof ItemCampaign) ? 'item_campaign_id' : 'food_id';

        $rows = DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('order_details.'.$column, $item['id'])
            ->whereNotIn('orders.order_status', ['failed', 'canceled'])
            ->whereNotNull('order_details.variation')
            ->orderByDesc('order_details.id')
            ->limit($scan)
            ->get(['order_details.variation', 'order_details.quantity']);

        // Orders placed before variation options had ids stored the label alone, so both keys
        // are tallied and both are consulted below - otherwise an item's older history simply
        // disappears from the count.
        $by_option_id = [];
        $by_label = [];

        foreach ($rows as $row) {
            $ordered = json_decode($row->variation, true);

            if (! is_array($ordered)) {
                continue;
            }

            $quantity = max((int) $row->quantity, 1);

            foreach ($ordered as $variation) {
                foreach (data_get($variation, 'values', []) as $value) {
                    $option_id = data_get($value, 'option_id');

                    if ($option_id) {
                        $by_option_id[(int) $option_id] = ($by_option_id[(int) $option_id] ?? 0) + $quantity;
                    }

                    $label_key = self::variationLabelKey(data_get($variation, 'name'), data_get($value, 'label'));
                    $by_label[$label_key] = ($by_label[$label_key] ?? 0) + $quantity;
                }
            }
        }

        if (empty($by_option_id) && empty($by_label)) {
            return [];
        }

        $recommended = [];

        foreach ($variations as $variation) {
            foreach (data_get($variation, 'values', []) as $value) {
                $option_id = data_get($value, 'option_id');
                $label_key = self::variationLabelKey(data_get($variation, 'name'), data_get($value, 'label'));

                // The label tally already contains every id-tallied line, so the id is preferred
                // and the label is only a fallback - adding them would double count.
                $order_count = $option_id && isset($by_option_id[(int) $option_id])
                    ? $by_option_id[(int) $option_id]
                    : ($by_label[$label_key] ?? 0);

                if ($order_count <= 0) {
                    continue;
                }

                $recommended[] = [
                    'variation_id' => data_get($variation, 'variation_id') ? (int) data_get($variation, 'variation_id') : null,
                    'variation_name' => data_get($variation, 'name'),
                    'option_id' => $option_id ? (int) $option_id : null,
                    'option_name' => data_get($value, 'label'),
                    'option_price' => (float) data_get($value, 'optionPrice', 0),
                    'order_count' => (int) $order_count,
                ];
            }
        }

        usort($recommended, fn ($a, $b) => $b['order_count'] <=> $a['order_count']);

        return array_slice($recommended, 0, $limit);
    }

    private static function variationLabelKey($variation_name, $option_label): string
    {
        return strtolower(trim((string) $variation_name).'::'.trim((string) $option_label));
    }

    /**
     * The Offers screen's food tab: every discounted item in the customer's zones.
     *
     * Ported from mart's ProductLogic::get_offer_items(). "Discounted" here is StackFood's own
     * definition, the one the search endpoint and the `discounted` filter chip already use: the
     * item carries its own discount, or its restaurant is inside a running restaurant wide
     * discount. Deepest discount first, because that is what the screen is selling.
     *
     * Every filter the design's filter sheet offers is honoured through the scopes the rest of
     * the app already filters with, so a chip behaves identically here and in search.
     */
    public static function get_offer_items(array $additional_data)
    {
        $zone_ids = $additional_data['zone_id'] ?? [];
        $search = trim((string) ($additional_data['search'] ?? ''));
        $search = ($search === '' || strtolower($search) === 'null') ? null : $search;

        $category_ids = $additional_data['category_ids'] ?? [];
        $category_ids = array_values(array_filter(array_map('intval', is_array($category_ids) ? $category_ids : [])));

        $query = Food::active()
            ->type($additional_data['type'] ?? 'all')
            ->whereHas('restaurant', function ($q) use ($zone_ids) {
                $q->whereIn('zone_id', $zone_ids)->Active();
            })
            // The offer has to be the item's own - a store wide rate belongs to the
            // restaurant and a happy hour to the window, and both override the item's own
            // while they run. Food::scopeItemDiscounted() is that rule, shared with the
            // `discounted` chip so this tab and the filter cannot answer differently.
            ->itemDiscounted($zone_ids)
            ->when(!empty($additional_data['halal']), function ($q) {
                $q->where('is_halal', 1);
            })
            ->when(!empty($category_ids), function ($q) use ($category_ids) {
                $q->whereHas('category', function ($query) use ($category_ids) {
                    $query->whereIn('id', $category_ids)->orWhereIn('parent_id', $category_ids);
                });
            })
            ->when($search, function ($q) use ($search) {
                $keys = array_values(array_filter(explode(' ', $search)));
                $q->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('name', 'like', "%{$key}%");
                    }
                    $query->orWhereHas('translations', function ($t) use ($keys) {
                        $t->where(function ($tt) use ($keys) {
                            foreach ($keys as $key) {
                                $tt->orWhere('value', 'like', "%{$key}%");
                            }
                        });
                    });
                    $query->orWhereHas('tags', function ($t) use ($keys) {
                        $t->where(function ($tt) use ($keys) {
                            foreach ($keys as $key) {
                                $tt->orWhere('tag', 'like', "%{$key}%");
                            }
                        });
                    });
                    $query->orWhereHas('category', function ($c) use ($keys) {
                        $c->where(function ($cc) use ($keys) {
                            foreach ($keys as $key) {
                                $cc->orWhere('name', 'like', "%{$key}%");
                            }
                        });
                    });
                });
            })
            ->applyFilters($additional_data)
            ->applyRating($additional_data['request'] ?? null)
            ->applyPriceRange($additional_data['request'] ?? null)
            ->orderByDesc('discount');

        $query = \App\CentralLogics\PersonalizationService::applyItemPersonalization($query, $additional_data['user_id'] ?? null);

        $paginator = $query->paginate($additional_data['limit'], ['food.*'], 'page', $additional_data['offset']);

        return [
            'total_size' => $paginator->total(),
            'limit' => $additional_data['limit'],
            'offset' => $additional_data['offset'],
            'products' => $paginator->items(),
        ];
    }

    /**
     * The offer card, exactly what the Offers screen draws and nothing else.
     *
     * product_data_formatting() is not used here on purpose: it resolves addons, tax rows and a
     * Category::find() per category per item, which costs about a thousand queries for a page of
     * twenty - fine for a details page, ruinous for an infinite scroll. mart draws this screen
     * with its own lean formatter (productListDataFormatting) for the same reason.
     *
     * The discount is resolved through the helper the menu and the cart price with, so the
     * struck through price and the one the customer will actually pay always agree - including
     * when a restaurant wide discount or a happy hour outranks the item's own.
     */
    public static function format_offer_items($items, ?int $userId = null): array
    {
        // paginate() hands back a plain array, and only an Eloquent collection can eager load.
        $items = $items instanceof \Illuminate\Database\Eloquent\Collection
            ? $items
            : \Illuminate\Database\Eloquent\Collection::make(is_array($items) ? $items : iterator_to_array($items));

        if ($items->isEmpty()) {
            return [];
        }

        // Loaded for the page: the image url accessor reads storage, and every row prints its
        // restaurant's name and badge.
        $items->loadMissing(['storage', 'restaurant.storage', 'restaurant.restaurant_config', 'restaurant.discount']);

        $wishlisted = $userId
            ? array_flip(DB::table('wishlists')->where('user_id', $userId)->whereIn('food_id', $items->pluck('id')->all())->pluck('food_id')->all())
            : [];

        return $items->map(function ($item) use ($wishlisted) {
            $restaurant = $item->restaurant;
            $price = (float) $item->price;
            $discount_amount = (float) Helpers::product_discount_calculate($item, $price, $restaurant);
            $discount_data = Helpers::product_discount_calculate_data($item, $restaurant);
            $store_wide = Helpers::store_wide_discount_data($restaurant);

            return [
                'id' => (int) $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'image_full_url' => $item->image_full_url,
                'price' => $price,
                'discounted_price' => max(0, round($price - $discount_amount, config('round_up_to_digit'))),
                'discount_amount' => round($discount_amount, config('round_up_to_digit')),
                'discount' => (float) ($discount_data['discount_percentage'] ?? 0),
                'discount_type' => $discount_data['original_discount_type'] ?? $item->discount_type,
                // The store wide rates the item's own discount is not allowed to carry, so a
                // card can draw the store banner without inferring it from the item.
                'restaurant_discount' => $store_wide['restaurant_discount'],
                'happy_hour_discount' => $store_wide['happy_hour_discount'],
                'veg' => (int) $item->veg,
                'is_halal' => (int) $item->is_halal,
                'avg_rating' => (float) $item->avg_rating,
                'rating_count' => (int) $item->rating_count,
                'wishlist' => isset($wishlisted[$item->id]) ? 1 : 0,
                'restaurant_id' => (int) $item->restaurant_id,
                'restaurant_name' => $restaurant?->name,
                'restaurant_logo_full_url' => $restaurant?->logo_full_url,
                'restaurant_status' => (int) ($restaurant?->status ?? 0),
                'verified_seller' => Helpers::get_verified_seller_status($restaurant, $restaurant?->restaurant_config),
            ];
        })->values()->all();
    }
}
