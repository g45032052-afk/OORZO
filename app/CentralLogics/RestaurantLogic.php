<?php

namespace App\CentralLogics;

use App\Models\Campaign;
use App\Models\ItemCampaign;
use App\Models\Coupon;
use App\Models\Restaurant;
use App\Models\PriorityList;
use App\Models\Advertisement;
use App\Models\BusinessSetting;
use App\Models\OrderTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;


class RestaurantLogic
{
    public static function get_restaurants(array $additional_data)
    {
        $all_restaurant_default_status = BusinessSetting::where('key', 'all_restaurant_default_status')->first();
        $all_restaurant_default_status = $all_restaurant_default_status ? $all_restaurant_default_status->value : 1;
        $all_restaurant_sort_by_general = PriorityList::where('name', 'all_restaurant_sort_by_general')->where('type','general')->first();
        $all_restaurant_sort_by_general = $all_restaurant_sort_by_general ? $all_restaurant_sort_by_general->value : '';
        $all_restaurant_sort_by_unavailable = PriorityList::where('name', 'all_restaurant_sort_by_unavailable')->where('type','unavailable')->first();
        $all_restaurant_sort_by_unavailable = $all_restaurant_sort_by_unavailable ? $all_restaurant_sort_by_unavailable->value : '';
        $all_restaurant_sort_by_temp_closed = PriorityList::where('name', 'all_restaurant_sort_by_temp_closed')->where('type','temp_closed')->first();
        $all_restaurant_sort_by_temp_closed = $all_restaurant_sort_by_temp_closed ? $all_restaurant_sort_by_temp_closed->value : '';
        // Chips and flags that ask for a particular order rather than a particular subset.
        $ordering_chip_requested = (bool) array_intersect(
            ['popular', 'new_arrivals', 'top_rated'],
            (array) ($additional_data['filter_by'] ?? [])
        ) || ($additional_data['request']?->rating == 1);

        // avg_rating and top_rated both used to attach their own `avg_r` subselect, and sending
        // the two together made MySQL reject the whole query with "Duplicate column name
        // 'avg_r'" - a 500 on the listing. They are two spellings of the same constraint over
        // the same expression, so they collapse into one floor: the stricter of the two wins,
        // and top_rated on its own still means three stars and up.
        $rating_floor = max(
            (float) ($additional_data['avg_rating'] ?? 0),
            (isset($additional_data['top_rated']) && $additional_data['top_rated'] == 1) ? 3 : 0
        ) ?: null;

        $key = $additional_data['name'] ? explode(' ', $additional_data['name']):null;
        $cuisine = $additional_data['cuisine'] ?? [];
        $cuisine = is_array($cuisine) ? $cuisine : (json_decode($cuisine, true) ?: []);

        $query = Restaurant::
        withOpen($additional_data['longitude'],$additional_data['latitude'])
            ->with(['discount'=>function($q){
                return $q->validate();
            }])
            ->whereIn('zone_id', $additional_data['zone_id'])
            ->withcount('foods')
            ->withcount('reviews_comments')

            ->when($additional_data['filter'] =='delivery', function($q){
                return $q->delivery();
            })
            ->when($additional_data['filter'] =='take_away', function($q){
                return $q->takeaway();
            })
            ->when($additional_data['filter'] =='dine_in', function($q){
                return $q->whereHas('restaurant_config', function ($query) {
                    $query->where('dine_in',true);
                });
            })

            ->when($rating_floor, function($query) use($rating_floor) {
                $query->selectSub(function ($query) {
                    $query->selectRaw('AVG(reviews.rating)')
                        ->from('reviews')
                        ->join('food', 'food.id', '=', 'reviews.food_id')
                        ->whereColumn('food.restaurant_id', 'restaurants.id')
                        // Active reviews only - the same rule the rating shown to the customer uses.
                        ->where('reviews.status', 1)
                        ->groupBy('food.restaurant_id');
                }, 'avg_r')->having('avg_r', '>=', $rating_floor);
            })
            ->when(isset($additional_data['veg'])   && $additional_data['veg'] == 1  , function($query) {
                $query->where('veg',1);
            })
            ->when(isset($additional_data['non_veg']) && $additional_data['non_veg'] == 1   , function($query) {
                $query->where('non_veg',1);
            })

            ->when(isset($additional_data['delivery']) && $additional_data['delivery'] == 1   , function($query) {
                return $query->delivery();
            })
            ->when(isset($additional_data['takeaway']) && $additional_data['takeaway'] == 1   , function($query) {
                return $query->takeaway();
            })

            ->when(isset($additional_data['discount'])  && $additional_data['discount'] == 1  , function($query) {
                $query->whereHas('discount',function($query){
                    return $query->validate();
                });
            })
            ->when(!empty($additional_data['featured']), function($query) {
                return $query->featured();
            })
            ->Active()
            ->type($additional_data['type'])
            ->when(!empty($cuisine), function($query) use ($cuisine) {
                $query->cuisine($cuisine);
            })
            ->when($additional_data['filter'] =='latest', function($q){
                return $q->latest();
            })
            ->when($additional_data['filter'] =='popular', function($q){
                return self::addOrdersCountIfMissing($q)
                    ->orderBy('open', 'desc')
                    ->orderBy('orders_count', 'desc');
            })
            ->when($additional_data['filter'] =='near_by_restaurants', function($q){
                return $q->orderBy('distance');
            })
            ->when(isset($key) , function($query)use($key){
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->Where('name', 'like', "%{$value}%");
                    }
                    $q->orWhereHas('translations',function($query) use($key){
                        foreach ($key as $value) {
                            $query->where('translationable_type', 'App\Models\Restaurant')->where('key','name')->where('value', 'like', "%{$value}%");
                        };
                    });
                    $q->orWhereHas('tags',function($query)use($key){
                        foreach ($key as $value) {
                            $query->where('tag', 'like', "%{$value}%");
                        };
                    });
                });
            });
            if($all_restaurant_default_status == '1' && (!isset($additional_data['sort_by']) || $additional_data['sort_by'] == 'default')) {
                // Open first, always. The popularity tiebreak is dropped when the customer
                // picked an ordering chip: applyFilters() adds the chip's ORDER BY after this
                // one, so `orders_count desc` sitting here decided the whole listing and the
                // chip only ever broke ties that never happened - New arrivals and Top rated
                // came back in the default order. The popular chip re-adds the same tiebreak
                // itself, so that case is unchanged.
                $query = $query->orderBy('open', 'desc');

                if (! $ordering_chip_requested) {
                    $query = self::addOrdersCountIfMissing($query)->orderBy('orders_count', 'desc');
                }
            } elseif(!isset($additional_data['sort_by']) || $additional_data['sort_by'] == 'default') {

                if($all_restaurant_sort_by_temp_closed == 'remove'){
                    $query = $query->where('active', '>', 0);
                }elseif($all_restaurant_sort_by_temp_closed == 'last'){
                    $query = $query->orderByDesc('active');
                }

                if($all_restaurant_sort_by_unavailable == 'remove'){
                    $query = $query->having('open', '>', 0);
                }elseif($all_restaurant_sort_by_unavailable == 'last'){
                    $query = $query->orderBy('open', 'desc');
                }

                if($all_restaurant_sort_by_general == 'latest_created') {
                    $query = $query->latest();
                }elseif($all_restaurant_sort_by_general == 'nearest_first') {
                    $query = $query->orderBy('distance');
                }elseif($all_restaurant_sort_by_general == 'rating') {
                    $query = $query->selectSub(function ($query) {
                                $query->selectRaw('AVG(reviews.rating)')
                                    ->from('reviews')
                                    ->join('food', 'food.id', '=', 'reviews.food_id')
                                    ->whereColumn('food.restaurant_id', 'restaurants.id')
                                    ->where('reviews.status', 1)
                                    ->groupBy('food.restaurant_id');
                            }, 'avg_r')->orderBy('avg_r', 'desc');
                }elseif($all_restaurant_sort_by_general == 'review_count') {
                    $query = $query->withCount('reviews')->orderBy('reviews_count', 'desc');
                }elseif($all_restaurant_sort_by_general == 'order_count') {
                    $query = self::addOrdersCountIfMissing($query)->orderBy('orders_count', 'desc');
                } elseif ($all_restaurant_sort_by_general == 'a_to_z') {
                    $query = $query->orderBy('name');
                } elseif ($all_restaurant_sort_by_general == 'z_to_a') {
                    $query = $query->orderByDesc('name');
                }

            }

        // Personalization: order the restaurant listing toward the customer's preferred restaurants.
        $query = \App\CentralLogics\PersonalizationService::applyStorePersonalization($query, auth('api')->id());
        $paginator = $query
        ->applyFilters($additional_data)
        ->applySorting($additional_data['sort_by'])
        ->applyRating($additional_data['request'] ?? null)
        ->applyPriceRange($additional_data['request'] ?? null)
        ->paginate($additional_data['limit'], ['*'], 'page', $additional_data['offset']);

        return [
            'filter_data'=> $additional_data['filter']  ?? null,
            'total_size' => $paginator->total(),
            'limit' => $additional_data['limit'],
            'offset' => $additional_data['offset'],
            'restaurants' => $paginator->items()
        ];
    }

    public static function get_latest_restaurants($zone_id, $additional_data, $limit = 10, $offset = 1, $type='all',$longitude=0,$latitude=0,
    $veg = null ,$non_veg = null ,$discount = null,$top_rated = null)
    {
        $new_restaurant_default_status = BusinessSetting::where('key', 'new_restaurant_default_status')->first();
        $new_restaurant_default_status = $new_restaurant_default_status ? $new_restaurant_default_status->value : 1;
        $new_restaurant_sort_by_general = PriorityList::where('name', 'new_restaurant_sort_by_general')->where('type','general')->first();
        $new_restaurant_sort_by_general = $new_restaurant_sort_by_general ? $new_restaurant_sort_by_general->value : '';
        $new_restaurant_sort_by_unavailable = PriorityList::where('name', 'new_restaurant_sort_by_unavailable')->where('type','unavailable')->first();
        $new_restaurant_sort_by_unavailable = $new_restaurant_sort_by_unavailable ? $new_restaurant_sort_by_unavailable->value : '';
        $new_restaurant_sort_by_temp_closed = PriorityList::where('name', 'new_restaurant_sort_by_temp_closed')->where('type','temp_closed')->first();
        $new_restaurant_sort_by_temp_closed = $new_restaurant_sort_by_temp_closed ? $new_restaurant_sort_by_temp_closed->value : '';

        $query = Restaurant::withOpen($longitude,$latitude)
        ->with(['discount'=>function($q){
            return $q->validate();
        }])->whereIn('zone_id', $zone_id)
        ->withcount('foods')
            ->withcount('reviews_comments')
        ->when(isset($veg)   && $veg == 1  , function($q) {
            $q->where('veg',1);
        })
        ->when(isset($non_veg) && $non_veg == 1   , function($q) {
            $q->where('non_veg',1);
        })
        ->when(isset($discount)  && $discount == 1  , function($q) {
            $q->whereHas('discount',function($query){
                return $query->validate();
            });
        })
        ->when(isset($top_rated) && $top_rated == 1 , function($query){
            $query->selectSub(function ($query) {
                $query->selectRaw('AVG(reviews.rating)')
                    ->from('reviews')
                    ->join('food', 'food.id', '=', 'reviews.food_id')
                    ->whereColumn('food.restaurant_id', 'restaurants.id')
                    ->groupBy('food.restaurant_id')
                    ->havingRaw('AVG(reviews.rating) > ?', [3]);
            }, 'avg_r')->having('avg_r', '>=', 3);
        })
        ->Active()
        ->type($type);

        // Personalization: surface the customer's preferred restaurants first.
        $query = \App\CentralLogics\PersonalizationService::applyStorePersonalization($query, auth('api')->id());

        if($new_restaurant_default_status == '1' && (!isset($additional_data['sort_by']) || $additional_data['sort_by'] == 'default')) {
            $query = $query->latest();
        }elseif(!isset($additional_data['sort_by']) || $additional_data['sort_by'] == 'default'){

            if($new_restaurant_sort_by_temp_closed == 'remove'){
                $query = $query->where('active', '>', 0);
            }elseif($new_restaurant_sort_by_temp_closed == 'last'){
                $query = $query->orderByDesc('active');
            }

            if($new_restaurant_sort_by_unavailable == 'remove'){
                $query = $query->having('open', '>', 0);
            }elseif($new_restaurant_sort_by_unavailable == 'last'){
                $query = $query->orderBy('open', 'desc');
            }

            if($new_restaurant_sort_by_general == 'latest_created') {
                $query = $query->latest();
            }elseif($new_restaurant_sort_by_general == 'nearby_first') {
                $query = $query->orderBy('distance');
            }elseif($new_restaurant_sort_by_general == 'delivery_time') {
                $query = $query->whereRaw("delivery_time REGEXP '^[0-9]+-[0-9]+-min$'")
                    ->orderByRaw("SUBSTRING_INDEX(delivery_time, '-', 1)")
                    ->orderByRaw("SUBSTRING_INDEX(SUBSTRING_INDEX(delivery_time, '-', -1), '-', 1)");
            }

        }
        info($additional_data['sort_by']);

        $paginator = $query
        ->applyFilters($additional_data)
        ->applySorting($additional_data['sort_by'])
        ->applyRating($additional_data['request'] ?? null)
        ->applyPriceRange($additional_data['request'] ?? null)
        ->limit(20)
        ->get();

        return [
            'total_size' => $paginator->count(),
            'limit' => $limit,
            'offset' => $offset,
            'restaurants' => $paginator
        ];
    }

    public static function get_popular_restaurants($zone_id, $limit = 10, $offset = 1, $type='all',$longitude=0,$latitude=0 ,$veg = null ,$non_veg = null ,$discount = null,$top_rated = null, $additional_data = [])
    {
        $popular_restaurant_default_status = BusinessSetting::where('key', 'popular_restaurant_default_status')->first();
        $popular_restaurant_default_status = $popular_restaurant_default_status ? $popular_restaurant_default_status->value : 1;
        $popular_restaurant_sort_by_general = PriorityList::where('name', 'popular_restaurant_sort_by_general')->where('type','general')->first();
        $popular_restaurant_sort_by_general = $popular_restaurant_sort_by_general ? $popular_restaurant_sort_by_general->value : '';
        $popular_restaurant_sort_by_unavailable = PriorityList::where('name', 'popular_restaurant_sort_by_unavailable')->where('type','unavailable')->first();
        $popular_restaurant_sort_by_unavailable = $popular_restaurant_sort_by_unavailable ? $popular_restaurant_sort_by_unavailable->value : '';
        $popular_restaurant_sort_by_temp_closed = PriorityList::where('name', 'popular_restaurant_sort_by_temp_closed')->where('type','temp_closed')->first();
        $popular_restaurant_sort_by_temp_closed = $popular_restaurant_sort_by_temp_closed ? $popular_restaurant_sort_by_temp_closed->value : '';

        $query = Restaurant::withOpen($longitude,$latitude)
            ->with(['reviews','discount'=>function($q){
                return $q->validate();
            }])->whereIn('zone_id', $zone_id)
            ->withcount('foods')
            ->withcount('reviews_comments')
            ->withCount('reviews')
            ->withCount('orders')
            ->type($type)
            ->when(isset($veg) && $veg == 1  , function($q) {
                $q->where('veg',1);
            })
            ->when(isset($non_veg) && $non_veg == 1   , function($q) {
                $q->where('non_veg',1);
            })
            ->when(isset($discount)  && $discount == 1  , function($q) {
                $q->whereHas('discount',function($query){
                    return $query->validate();
                });
            })
            ->when(isset($top_rated) && $top_rated == 1 , function($query){
                $query->selectSub(function ($query) {
                    $query->selectRaw('AVG(reviews.rating)')
                        ->from('reviews')
                        ->join('food', 'food.id', '=', 'reviews.food_id')
                        ->whereColumn('food.restaurant_id', 'restaurants.id')
                        ->groupBy('food.restaurant_id');
                }, 'avg_r')->having('avg_r', '>=', 3);
            })
            ->search(keywords:request()?->search)
            ->Active();

        // Personalization: surface the customer's preferred restaurants first
        // (no-op when the toggle is off / customer is a guest).
        $query = \App\CentralLogics\PersonalizationService::applyStorePersonalization($query, auth('api')->id());

        if($popular_restaurant_default_status == '1' && $type == 'all' && (!isset($additional_data['sort_by']) || $additional_data['sort_by'] == 'default')) {
            $query = $query->orderBy('open', 'desc')->orderBy('orders_count', 'desc');
        }elseif(!isset($additional_data['sort_by']) || $additional_data['sort_by'] == 'default'){

            if($popular_restaurant_sort_by_temp_closed == 'remove'){
                $query = $query->where('active', '>', 0);
            }elseif($popular_restaurant_sort_by_temp_closed == 'last'){
                $query = $query->orderByDesc('active');
            }

            if($popular_restaurant_sort_by_unavailable == 'remove'){
                $query = $query->having('open', '>', 0);
            }elseif($popular_restaurant_sort_by_unavailable == 'last'){
                $query = $query->orderBy('open', 'desc');
            }

            if($popular_restaurant_sort_by_general == 'rating') {
                $query = $query->selectSub(function ($query) {
                    $query->selectRaw('AVG(reviews.rating)')
                        ->from('reviews')
                        ->join('food', 'food.id', '=', 'reviews.food_id')
                        ->whereColumn('food.restaurant_id', 'restaurants.id')
                        ->groupBy('food.restaurant_id');
                }, 'avg_r')->orderBy('avg_r', 'desc');
            }elseif($popular_restaurant_sort_by_general == 'review_count') {
                $query = $query->orderByDesc('reviews_count');
            }elseif($popular_restaurant_sort_by_general == 'order_count') {
                $query = $query->orderBy('orders_count', 'desc');
            }

        }

        $paginator = $query->applyFilters($additional_data)
            ->applySorting($additional_data['sort_by'] ?? null)
            ->applyRating($additional_data['request'] ?? null)
            ->applyPriceRange($additional_data['request'] ?? null)
            ->limit(50)->get();

        return [
            'total_size' => $paginator->count(),
            'limit' => $limit,
            'offset' => $offset,
            'restaurants' => $paginator
        ];
    }
    public static function get_dine_in_restaurants(array $additional_data)
    {
        $query = Restaurant::withOpen($additional_data['longitude']?? 0,$additional_data['latitude']?? 0)
            ->whereHas('restaurant_config', function ($query) {
                $query->where('dine_in',1);
            })
            ->with(['reviews','discount'=>function($q){
                return $q->validate();
            }])->whereIn('zone_id', $additional_data['zone_id'])
            ->multiCuisine($additional_data['cuisine'])
            ->withcount('foods')
            ->withcount('reviews_comments')
            ->withCount('reviews')
            ->withCount('orders')
            ->type($additional_data['type'] ?? 'all')
            ->Active();

        $paginator = $query->applyFilters($additional_data)
            ->applySorting($additional_data['sort_by'] ?? null)
            ->applyRating($additional_data['request'] ?? null)
            ->applyPriceRange($additional_data['request'] ?? null)
            ->paginate(($additional_data['limit'] ?? 10) , ['*'], 'page', ($additional_data['offset'] ?? 1) );

        return [
            'total_size' => $paginator->total(),
            'limit' => ($additional_data['limit'] ?? 10) ,
            'offset' => ($additional_data['offset'] ?? 1) ,
            'restaurants' => $paginator->items()
        ];
    }

    public static function get_restaurant_details($restaurant_id, $longitude = null, $latitude = null)
    {
        return Restaurant::with(['discount'=>function($q){
            return $q->validate();
        }, 'campaigns', 'schedules','restaurant_sub'])->active()
            ->withcount('reviews_comments')
        // Same measure the listing endpoints expose: metres between the customer's
        // coordinates and the restaurant. Skipped when the request carries no location,
        // so the query stays exactly as it was for callers without coordinates.
        ->when(is_numeric($longitude) && is_numeric($latitude), function ($query) use ($longitude, $latitude) {
            $query->selectRaw('restaurants.*, ST_Distance_Sphere(point(longitude, latitude), point('.(float) $longitude.', '.(float) $latitude.')) as distance');
        })
        ->when(is_numeric($restaurant_id),function ($qurey) use($restaurant_id){
            $qurey-> where('id', $restaurant_id);
        })
        ->when(!is_numeric($restaurant_id),function ($qurey) use($restaurant_id){
            $qurey-> where('slug', $restaurant_id);
        })
        ->first();
    }

    public static function calculate_restaurant_rating($ratings)
    {
        $total_submit = $ratings[0]+$ratings[1]+$ratings[2]+$ratings[3]+$ratings[4];
        $rating = ($ratings[0]*5+$ratings[1]*4+$ratings[2]*3+$ratings[3]*2+$ratings[4])/($total_submit?$total_submit:1);
        return ['rating'=>$rating, 'total'=>$total_submit];
    }
    public static function calculate_positive_rating($ratings)
    {
        $total_submit = $ratings[0]+$ratings[1]+$ratings[2]+$ratings[3]+$ratings[4];
        $rating = (($ratings[0]+$ratings[1]) / ($total_submit?$total_submit:1)) *100;
        return ['rating'=>$rating, 'total'=>$total_submit];
    }

    public static function update_restaurant_rating($ratings, $product_rating)
    {
        $restaurant_ratings = [1=>0 , 2=>0, 3=>0, 4=>0, 5=>0];
        if($ratings)
        {
            $restaurant_ratings[1] = $ratings[4];
            $restaurant_ratings[2] = $ratings[3];
            $restaurant_ratings[3] = $ratings[2];
            $restaurant_ratings[4] = $ratings[1];
            $restaurant_ratings[5] = $ratings[0];
            $restaurant_ratings[$product_rating] = $ratings[5-$product_rating] + 1;
        }
        else
        {
            $restaurant_ratings[$product_rating] = 1;
        }
        return json_encode($restaurant_ratings);
    }

    public static function search_restaurants( $zone_id, $name ='null', $category_id= null,$limit = 10, $offset = 1, $type='all',$longitude=0,$latitude=0 ,$popular=0,$new=0 , $rating=0 , $rating_3_plus = 0,$rating_4_plus = 0 ,$rating_5 = 0 , $discounted =0, $sort_by =null , $dine_in =0 ,$open=0, $cuisine = [], $additional_data = [], $halal = 0)
    {
        $category_ids = Helpers::filter_id_list($category_id);

        $search_bar_default_status = BusinessSetting::where('key', 'search_bar_default_status')->first()?->value ?? 1;
        $search_bar_sort_by_unavailable = PriorityList::where('name', 'search_bar_sort_by_unavailable')->where('type','unavailable')->first()?->value ?? '';
        $search_bar_sort_by_temp_closed = PriorityList::where('name', 'search_bar_sort_by_temp_closed')->where('type','temp_closed')->first()?->value ?? '';

        $key = isset($name) && $name != 'null'  ?  explode(' ', $name) : null;

        if(is_array($key)){
            $key = array_filter($key);
            $key = array_values($key);
        }

        $query = Restaurant::withOpen($longitude,$latitude)->with(['discount'=>function($q){
            return $q->validate();
        }])->whereIn('zone_id', $zone_id)->weekday()
        ->multiCuisine($cuisine)
        ->withcount('foods')
            ->withcount('reviews_comments')
            ->when( isset($key)  , function ($query) use($key) {
                $query->where(function($q) use($key){
                    foreach ($key as $value) {
                        $q->Where('name', 'like', "%{$value}%");
                    }
                    $q->orWhereHas('tags',function($query)use($key){
                        foreach ($key as $value) {
                            $query->where('tag', 'like', "%{$value}%");
                        };
                    });
                    $q->orWhereHas('cuisine',function($query) use($key){
                        foreach ($key as $value) {
                            $query->where('name', 'like', "%{$value}%");
                        };
                    });
                    $q->orWhereHas('foods',function($query) use($key){
                        foreach ($key as $value) {
                            $query->where('name', 'like', "%{$value}%");
                        };
                    });
                    $q->orWhereHas('foods.nutritions',function($query)use($key){
                        $query->where(function($q)use($key){
                            foreach ($key as $value) {
                                $q->where('nutrition', 'like', "%{$value}%");
                            };
                        });
                    });
                    $q->orWhereHas('foods.allergies',function($query)use($key){
                        $query->where(function($q)use($key){
                            foreach ($key as $value) {
                                $q->where('allergy', 'like', "%{$value}%");
                            };
                        });
                    });
                    $q->orWhereHas('translations',function($query) use($key){
                        foreach ($key as $value) {
                            $query->where('translationable_type', 'App\Models\Restaurant')->where('key','name')->where('value', 'like', "%{$value}%");
                        };
                    });
                });
            })

            ->when($new == 1, function($query){
                return $query->latest();
            })
            ->when($popular == 1, function($query){
                return self::addOrdersCountIfMissing($query)->orderBy('orders_count', 'desc');
            })
            ->when($dine_in == 1, function($query){
                return $query->whereHas('restaurant_config', function ($query) {
                    $query->where('dine_in',1);
                });
            })

            ->when($rating == 1 , function($query){
                $query->selectSub(function ($query) {
                    $query->selectRaw('AVG(reviews.rating)')
                        ->from('reviews')
                        ->join('food', 'food.id', '=', 'reviews.food_id')
                        ->whereColumn('food.restaurant_id', 'restaurants.id')
                        ->groupBy('food.restaurant_id')
                        ->havingRaw('AVG(reviews.rating) > ?', [3]);
                }, 'avg_r')->having('avg_r', '>=', 3);
            })

            // The rating chips used to be re-implemented here as three overlapping blocks of
            // exclusive havings, which ANDed with applyRating() further down and pushed the
            // floor back above the star the customer picked. applyRating() is the one place
            // that reads them now - $rating_3_plus and friends stay in the signature only so
            // the named arguments at the call site keep working.
            ->when($discounted == 1  , function($q) {
                $q->whereHas('discount',function($query){
                    return $query->validate();
                });
            })

        ->when($category_ids, function($query)use($category_ids){
            $query->whereHas('foods.category', function($q)use($category_ids){
                return $q->whereIn('id', $category_ids)->orWhereIn('parent_id', $category_ids);
            });
        })
        // products/search has honoured `halal=1` all along; the restaurant tab of the same
        // screen sends the same flag and was ignoring it, so the two tabs disagreed.
        ->when($halal == 1, function($query){
            $query->whereHas('foods', function($q){
                $q->where('is_halal', 1);
            });
        })
        ->when(isset($sort_by) && $sort_by == 'asc' , function($query){
            return $query->orderBy('name' , 'asc');
        })
            ->when(isset($sort_by) && $sort_by == 'desc' , function($query){
            return $query->orderBy('name' , 'desc');
        })
            ->when(isset($sort_by) && $sort_by == 'distance' , function($query){
            return $query->orderBy('distance');
        })
            ->when(isset($sort_by) && $sort_by == 'rating' , function($query){
            return $query->selectSub(function ($query) {
                $query->selectRaw('AVG(reviews.rating)')
                    ->from('reviews')
                    ->join('food', 'food.id', '=', 'reviews.food_id')
                    ->whereColumn('food.restaurant_id', 'restaurants.id')
                    ->groupBy('food.restaurant_id');
            }, 'avg_rate')->orderbyDesc('avg_rate');
        })
            ->when(isset($sort_by) && $sort_by == 'high' ||  $sort_by == 'low' , function($query){
            return $query->latest();
        })

        ->active()->type($type);


        if($open == 1){
            $query = $query->having('open', '>', 0);
        }


        if($search_bar_default_status == '1') {
            $query = $query->orderByRaw("FIELD(name, ?) DESC", [$name])
                            ->orderBy('open', 'desc')
                            ->orderBy('distance');
        }

        if($search_bar_default_status == '0') {
            if($search_bar_sort_by_temp_closed == 'remove'){
                $query = $query->where('active', '>', 0);
            }elseif($search_bar_sort_by_temp_closed == 'last'){
                $query = $query->orderByDesc('active');
            }

            if($search_bar_sort_by_unavailable == 'remove'){
                $query = $query->having('open', '>', 0);
            }elseif($search_bar_sort_by_unavailable == 'last'){
                $query = $query->orderBy('open', 'desc');
            }
        }

        // Personalization: order restaurant search results toward the customer's preferred restaurants.
        $query = \App\CentralLogics\PersonalizationService::applyStorePersonalization($query, auth('api')->id());
        $paginator = $query
        ->applyFilters($additional_data)
        ->applySorting($additional_data['sort_by'])
        ->applyRating($additional_data['request'] ?? null)
        ->applyPriceRange($additional_data['request'] ?? null)
        ->paginate($limit, ['*'], 'page', $offset);


        return [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'restaurants' => $paginator->items()
        ];
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

    public static function get_earning_data($vendor_id)
    {
        $monthly_earning = OrderTransaction::whereMonth('created_at', date('m'))->NotRefunded()->where('vendor_id', $vendor_id)->sum('restaurant_amount');
        $weekly_earning = OrderTransaction::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->NotRefunded()->where('vendor_id', $vendor_id)->sum('restaurant_amount');
        $daily_earning = OrderTransaction::whereDate('created_at', now())->NotRefunded()->where('vendor_id', $vendor_id)->sum('restaurant_amount');

        return['monthely_earning'=>(float)$monthly_earning, 'weekly_earning'=>(float)$weekly_earning, 'daily_earning'=>(float)$daily_earning];
    }

    public static function format_export_restaurants($restaurants)
    {
        $storage = [];
        foreach($restaurants as $item)
        {
            $storage[] = [
                'id'=>$item->restaurants[0]->id,
                'ownerID'=>$item->id,
                'ownerFirstName'=>$item->f_name,
                'ownerLastName'=>$item->l_name,
                'restaurantName'=>$item->restaurants[0]->name,
                'CoverPhoto'=>$item->restaurants[0]->cover_photo,
                'logo'=>$item->restaurants[0]->logo,
                'phone'=>$item->phone,
                'email'=>$item->email,
                'latitude'=>$item->restaurants[0]->latitude,
                'longitude'=>$item->restaurants[0]->longitude,
                'zone_id'=>$item->restaurants[0]->zone_id,
                'Address'=>$item->restaurants[0]->address ?? null,
                'Slug'=> $item->restaurants[0]->slug  ?? null,
                'MinimumOrderAmount'=>$item->restaurants[0]->minimum_order,
                'Comission'=>$item->restaurants[0]->comission ?? 0,
                'Tax'=>$item->restaurants[0]->tax ?? 0,

                'DeliveryTime'=>$item->restaurants[0]->delivery_time ?? '20-30',
                'MinimumDeliveryFee'=>$item->restaurants[0]->minimum_shipping_charge ?? 0,
                // Per kilometre or per mile, whichever the distance_unit setting is on - so the
                // header cannot name a unit. Imports still accept the old PerKmDeliveryFee header.
                'PerDistanceDeliveryFee'=>$item->restaurants[0]->per_km_shipping_charge ?? 0,
                'MaximumDeliveryFee'=>$item->restaurants[0]->maximum_shipping_charge ?? 0,
                // 'order_count'=>$item->restaurants[0]->order_count,
                // 'total_order'=>$item->restaurants[0]->total_order,
                'RestaurantModel'=>$item->restaurants[0]->restaurant_model,
                'ScheduleOrder'=> $item->restaurants[0]->schedule_order == 1 ? 'yes' : 'no',
                'FreeDelivery'=> $item->restaurants[0]->free_delivery == 1 ? 'yes' : 'no',
                'TakeAway'=> $item->restaurants[0]->take_away == 1 ? 'yes' : 'no',
                'Delivery'=> $item->restaurants[0]->delivery == 1 ? 'yes' : 'no',
                'Veg'=> $item->restaurants[0]->veg == 1 ? 'yes' : 'no',
                'NonVeg'=> $item->restaurants[0]->non_veg == 1 ? 'yes' : 'no',
                'OrderSubscription'=> $item->restaurants[0]->order_subscription_active == 1 ? 'yes' : 'no',
                'Status'=> $item->restaurants[0]->status == 1 ? 'active' : 'inactive',
                'FoodSection'=> $item->restaurants[0]->food_section == 1 ? 'active' : 'inactive',
                'ReviewsSection'=> $item->restaurants[0]->reviews_section == 1 ? 'active' : 'inactive',
                'SelfDeliverySystem'=> $item->restaurants[0]->self_delivery_system == 1 ? 'active' : 'inactive',
                'PosSystem'=> $item->restaurants[0]->pos_system == 1 ? 'active' : 'inactive',
                'RestaurantOpen'=> $item->restaurants[0]->active == 1 ? 'yes' : 'no',
                'FeaturedRestaurant'=> $item->restaurants[0]->featured == 1 ? 'yes' : 'no',
                // 'gst'=>$item->restaurants[0]->gst ?? null,
            ];
        }

        return $storage;
    }
    public static function format_restaurant_report_export_data($restaurants)
    {
        $storage = [];
        foreach($restaurants as $key => $restaurant)
        {
            if($restaurant->count()<1)
            {
                break;
            }
            if ($restaurant->reviews_count){
                $reviews_count = $restaurant->reviews_count;
            }
            else{
                $reviews_count = 1;
            }

            $restaurant_rating = round($restaurant->reviews_sum_rating /$reviews_count,1);
            $storage[] = [
                '#'=>$key+1,
                translate('messages.restaurant') =>$restaurant->name,
                translate('messages.total_food') =>$restaurant->foods_count ?? 0,
                translate('messages.total_order') =>$restaurant->without_refund_total_orders_count ?? 0,
                translate('messages.total_order').translate('messages.amount') =>$restaurant->transaction_sum_order_amount ?? 0,
                translate('messages.total_discount_given') =>$restaurant->transaction_sum_restaurant_expense ?? 0,
                translate('messages.total_admin_commission') =>$restaurant->transaction_sum_admin_commission ?? 0,
                translate('messages.total_vat_tax') =>$restaurant->transaction_sum_tax ?? 0,
                translate('messages.average_ratings') =>$restaurant_rating,
            ];
        }
        return $storage;
    }

    private static function addOrdersCountIfMissing($query)
    {
        $columns = $query->getQuery()->columns ?? [];
        $grammar = $query->getQuery()->getGrammar();
        foreach ($columns as $col) {
            $colStr = $col instanceof \Illuminate\Contracts\Database\Query\Expression
                ? $col->getValue($grammar)
                : (string) $col;
            if (str_contains($colStr, 'orders_count')) {
                return $query;
            }
        }
        return $query->withCount('orders');
    }

    public static function recently_viewed_restaurants_data($request, $zone_id, $limit = 10, $offset = 1, $type='all',$longitude=0,$latitude=0, array $additional_data = [])
    {
        // This route carries no apiGuestCheck, so $request->user - which only that middleware
        // populates - is null even for a signed in customer holding a valid token. Reading the
        // id off the api guard as well is what makes the rail work at all; filtering on a null
        // user_id matched nothing, so the endpoint answered [] for everyone.
        $user_id = $request->user?->id ?? auth('api')->id();

        if (! $user_id) {
            return ['total_size' => 0, 'limit' => $limit, 'offset' => $offset, 'restaurants' => collect()];
        }

        $paginator = Restaurant::whereHas('users',function ($query) use($user_id){
            $query->where('user_id',$user_id);
        })
        ->withOpen($longitude,$latitude)
        ->with(['discount'=>function($q){
            return $q->validate();
        }])->whereIn('zone_id', $zone_id)
        ->withcount('foods')
            ->withcount('reviews_comments')
        ->Active()
        ->type($type)
        // The rail is a shortlist of somewhere you have been, but it is still a restaurant
        // listing: the same veg/non-veg chips and keyword the other rails take apply here.
        ->searchKeyword($additional_data['name'] ?? null)
        ->applyFilters($additional_data)
        ->withCount('orders')
        ->orderBy('open', 'desc')
        ->selectRaw( '(SELECT `visit_count` FROM `visitor_logs` WHERE `restaurants`.`id` = `visitor_logs`.`visitor_log_id`
            AND `user_id` = ? ORDER BY `visit_count` DESC LIMIT 1) as v_count,
            (SELECT `order_count` FROM `visitor_logs` WHERE `restaurants`.`id` = `visitor_logs`.`visitor_log_id`
            AND `user_id` = ? ORDER BY  `order_count` DESC LIMIT 1) as o_count',
            [$user_id, $user_id] )
        ->orderBy('o_count', 'desc')
        ->orderBy('v_count', 'desc')
        ->limit(50)
        ->get();
        return [
            'total_size' => $paginator->count(),
            'limit' => $limit,
            'offset' => $offset,
            'restaurants' => $paginator
        ];
    }

    /**
     * "Recommended for you" - mart's StoreLogic::get_recommended_stores() adapted.
     *
     * Mart draws from an admin curated pool (store_config.is_recommended) and personalises within
     * it. StackFood has no such flag, so the pool is every active restaurant in the zone that has
     * a menu, and the recommendation is carried entirely by PersonalizationService - the same
     * scorer the all restaurants listing and order again already rank with. A signed out customer
     * has no preference summary, so for them the rail falls back to engagement: most ordered
     * first, nearest to break ties.
     *
     * The admin still steers it the way every other StackFood rail is steered: the
     * recommended_restaurant_* priority rows, and shuffle_recommended_restaurant for a rail that
     * should look different on each visit.
     */
    public static function get_recommended_for_you_restaurants(array $additional_data)
    {
        $default_status = BusinessSetting::where('key', 'recommended_restaurant_default_status')->first()?->value ?? 1;
        $shuffle = BusinessSetting::where('key', 'shuffle_recommended_restaurant')->first()?->value ?? 0;

        $sort_by_general = PriorityList::where('name', 'recommended_restaurant_sort_by_general')->where('type', 'general')->first()?->value ?? '';
        $sort_by_unavailable = PriorityList::where('name', 'recommended_restaurant_sort_by_unavailable')->where('type', 'unavailable')->first()?->value ?? '';
        $sort_by_temp_closed = PriorityList::where('name', 'recommended_restaurant_sort_by_temp_closed')->where('type', 'temp_closed')->first()?->value ?? '';
        $sort_by_rating = PriorityList::where('name', 'recommended_restaurant_sort_by_rating')->where('type', 'rating')->first()?->value ?? '';

        $query = Restaurant::withOpen($additional_data['longitude'], $additional_data['latitude'])
            ->with(['discount' => function ($q) {
                return $q->validate();
            }])
            ->withCount('foods')
            ->withCount('reviews_comments')
            ->whereIn('zone_id', $additional_data['zone_id'])
            ->Active()
            ->type($additional_data['type'])
            ->searchKeyword($additional_data['name'] ?? null)
            // Nothing to recommend from an empty menu.
            ->has('foods');

        if ($default_status == '1') {
            if ($shuffle == '1') {
                $query = $query->inRandomOrder();
            }

            $query = \App\CentralLogics\PersonalizationService::applyStorePersonalization($query, $additional_data['user_id'] ?? null);
            $query = $query->orderBy('open', 'desc');

            if ($shuffle != '1') {
                $query = self::addOrdersCountIfMissing($query)->orderBy('orders_count', 'desc')->orderBy('distance', 'asc');
            }
        } else {
            if ($sort_by_temp_closed == 'remove') {
                $query = $query->where('active', '>', 0);
            } elseif ($sort_by_temp_closed == 'last') {
                $query = $query->orderByDesc('active');
            }

            if ($sort_by_unavailable == 'remove') {
                $query = $query->having('open', '>', 0);
            } elseif ($sort_by_unavailable == 'last') {
                $query = $query->orderBy('open', 'desc');
            }

            // A floor on the average rating, not an ordering - mart draws the same distinction.
            if ($sort_by_rating && $sort_by_rating != 'none') {
                $rating_floor = match ($sort_by_rating) {
                    'four_plus' => 4,
                    'three_half_plus' => 3.5,
                    'three_plus' => 3,
                    'two_plus' => 2,
                    default => 0,
                };

                $query = $query->selectSub(function ($query) use ($rating_floor) {
                    $query->selectRaw('AVG(reviews.rating)')
                        ->from('reviews')
                        ->join('food', 'food.id', '=', 'reviews.food_id')
                        ->whereColumn('food.restaurant_id', 'restaurants.id')
                        // Active reviews only, so the floor is measured against the same
                        // average the row reports as avg_rating.
                        ->where('reviews.status', 1)
                        ->groupBy('food.restaurant_id')
                        ->havingRaw('AVG(reviews.rating) >= ?', [$rating_floor]);
                }, 'avg_r')->having('avg_r', '>=', $rating_floor);
            }

            if ($sort_by_general == 'rating') {
                $query = $query->selectSub(function ($query) {
                    $query->selectRaw('AVG(reviews.rating)')
                        ->from('reviews')
                        ->join('food', 'food.id', '=', 'reviews.food_id')
                        ->whereColumn('food.restaurant_id', 'restaurants.id')
                        ->where('reviews.status', 1)
                        ->groupBy('food.restaurant_id');
                }, 'avg_rat')->orderBy('avg_rat', 'desc');
            } elseif ($sort_by_general == 'review_count') {
                $query = $query->orderByDesc('reviews_comments_count');
            } elseif ($sort_by_general == 'order_count') {
                $query = self::addOrdersCountIfMissing($query)->orderBy('orders_count', 'desc');
            } elseif ($sort_by_general == 'nearest_first') {
                $query = $query->orderBy('distance', 'asc');
            }
        }

        $paginator = $query
            ->applyFilters($additional_data)
            ->paginate($additional_data['limit'], ['*'], 'page', $additional_data['offset']);

        return [
            'total_size' => $paginator->total(),
            'limit' => $additional_data['limit'],
            'offset' => $additional_data['offset'],
            'restaurants' => $paginator->items(),
        ];
    }

    /**
     * "Featured" - the restaurants the admin has curated into the rail.
     *
     * mart exposes featured only as a filter on its store listing; this keeps that (see the
     * ?featured=1 branch in get_restaurants above) and adds a rail of its own, so the home screen
     * asks for what it wants rather than paging a filtered listing.
     *
     * Curation decides membership, so nothing here re-ranks by rating or discount. Open first, a
     * closed restaurant sorts last rather than vanishing, then the customer's own preferences,
     * then distance.
     */
    public static function get_featured_restaurants(array $additional_data)
    {
        $query = Restaurant::withOpen($additional_data['longitude'], $additional_data['latitude'])
            ->with(['discount' => function ($q) {
                return $q->validate();
            }])
            ->withCount('foods')
            ->withCount('reviews_comments')
            ->whereIn('zone_id', $additional_data['zone_id'])
            ->Active()
            ->Featured()
            ->type($additional_data['type'])
            ->has('foods');

        $query = $query->applyFilters($additional_data)->orderBy('open', 'desc');
        $query = \App\CentralLogics\PersonalizationService::applyStorePersonalization($query, $additional_data['user_id'] ?? null);

        $paginator = $query
            ->orderBy('distance', 'asc')
            ->paginate($additional_data['limit'], ['*'], 'page', $additional_data['offset']);

        return [
            'total_size' => $paginator->total(),
            'limit' => $additional_data['limit'],
            'offset' => $additional_data['offset'],
            'restaurants' => $paginator->items(),
        ];
    }

    /**
     * "Quick delivery" - mart's StoreLogic::get_quick_delivery_stores() adapted.
     *
     * Ordered by how fast the restaurant promises to deliver, which delivery_time cannot express
     * as text, hence withOpenWithDeliveryTime(). Distance is not measured here at all - the rail
     * asks a question delivery_time already answers - so the scope is asked for its schedule and
     * delivery time columns without the great-circle sum, and equal windows are settled by id so
     * a paginated rail cannot repeat one restaurant and drop another.
     *
     * Closed restaurants sort last rather than being dropped: a rail that empties out at night is
     * worse than one that shows what will be quick when the customer comes back.
     */
    public static function get_quick_delivery_restaurants(array $additional_data)
    {
        $query = Restaurant::withOpenWithDeliveryTime($additional_data['longitude'], $additional_data['latitude'], with_distance: false)
            ->with(['discount' => function ($q) {
                return $q->validate();
            }])
            ->withCount('foods')
            ->withCount('reviews_comments')
            ->whereIn('zone_id', $additional_data['zone_id'])
            ->Active()
            ->type($additional_data['type'])
            ->searchKeyword($additional_data['name'] ?? null)
            ->has('foods');

        $query = $query->applyFilters($additional_data);

        // Sponsored first, and only when the caller asks for it - the rail is about speed, so
        // paid placement is opt in rather than the default.
        if (! empty($additional_data['ad'])) {
            $advertised_ids = Advertisement::valid()
                ->whereHas('restaurant', function ($q) use ($additional_data) {
                    $q->Active()->whereIn('zone_id', $additional_data['zone_id']);
                })
                ->pluck('restaurant_id')->unique()->values()->all();

            if (! empty($advertised_ids)) {
                $placeholders = implode(',', array_fill(0, count($advertised_ids), '?'));
                $query = $query->orderByRaw("CASE WHEN restaurants.id IN ($placeholders) THEN 0 ELSE 1 END", $advertised_ids);
            }
        }

        $query = $query->orderBy('open', 'desc');
        $query = \App\CentralLogics\PersonalizationService::applyStorePersonalization($query, $additional_data['user_id'] ?? null);

        $paginator = $query
            ->orderBy('min_delivery_time', 'asc')
            ->orderBy('restaurants.id', 'desc')
            ->paginate($additional_data['limit'], ['*'], 'page', $additional_data['offset']);

        return [
            'total_size' => $paginator->total(),
            'limit' => $additional_data['limit'],
            'offset' => $additional_data['offset'],
            'restaurants' => $paginator->items(),
        ];
    }

    /**
     * "Exclusive deals" - restaurants running a restaurant wide discount, deepest first.
     *
     * Ported from mart's StoreLogic::get_exclusive_deals(). Membership and ordering both come
     * from the discounts table: the running discount is selected as a column so the deepest deal
     * can lead, which a PHP side resolution could not do across a paginated set.
     *
     * Narrower than the Offers screen on purpose. offers/restaurants lists anything the card can
     * badge - item discounts, campaigns, BOGO - ranked by advertisement and distance. This rail
     * is only the restaurant wide percentage, ranked by how deep it is.
     */
    public static function get_exclusive_deals(array $additional_data)
    {
        $today = now()->format('Y-m-d');
        $now = now()->format('H:i:s');

        $query = Restaurant::withOpenWithDeliveryTime($additional_data['longitude'], $additional_data['latitude'])
            ->with(['discount' => function ($q) {
                return $q->validate();
            }])
            ->withCount('foods')
            ->withCount('reviews_comments')
            ->whereIn('zone_id', $additional_data['zone_id'])
            ->Active()
            ->type($additional_data['type'] ?? 'all')
            ->whereHas('discount', function ($q) {
                $q->validate();
            })
            // The discount as a sortable column. A restaurant can only have one running discount,
            // but the limit keeps the sub select honest if that ever stops being true.
            ->selectSub(function ($q) use ($today, $now) {
                $q->select('discount')
                    ->from('discounts')
                    ->whereColumn('discounts.restaurant_id', 'restaurants.id')
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->whereTime('start_time', '<=', $now)
                    ->whereTime('end_time', '>=', $now)
                    ->orderByDesc('discount')
                    ->limit(1);
            }, 'restaurant_discount_value')
            ->applyFilters($additional_data)
            ->applyRating($additional_data['request'] ?? null)
            ->orderByDesc('restaurant_discount_value');

        $query = \App\CentralLogics\PersonalizationService::applyStorePersonalization($query, $additional_data['user_id'] ?? null);

        $paginator = $query->paginate($additional_data['limit'], ['*'], 'page', $additional_data['offset']);

        return [
            'total_size' => $paginator->total(),
            'limit' => $additional_data['limit'],
            'offset' => $additional_data['offset'],
            'restaurants' => $paginator->items(),
        ];
    }

    /**
     * The Offers screen's restaurant tab: restaurants running an offer, each with a strip of the
     * discounted food underneath.
     *
     * Ported from mart's ProductLogic::get_offer_stores(). Mart requires a running store wide
     * discount; here a restaurant qualifies on any of the three things the card can badge - a
     * restaurant discount, discounted items of its own, or an approved BOGO offer - because the
     * screen shows all three and a restaurant carrying only a BOGO would otherwise be missing
     * from a screen that badges it.
     *
     * Advertised restaurants lead, matching the "Ad" badge in the design, then open, then the
     * customer's preferences, then distance.
     */
    public static function get_offer_restaurants(array $additional_data)
    {
        $search = trim((string) ($additional_data['search'] ?? ''));
        $search = ($search === '' || strtolower($search) === 'null') ? null : $search;

        $query = Restaurant::withOpenWithDeliveryTime($additional_data['longitude'], $additional_data['latitude'])
            ->with(['discount' => function ($q) {
                return $q->validate();
            }])
            ->withCount('foods')
            ->withCount('reviews_comments')
            ->whereIn('zone_id', $additional_data['zone_id'])
            ->Active()
            ->type($additional_data['type'] ?? 'all')
            // A food-level filter has to be answered with food. `type` alone reads the
            // restaurants.veg / non_veg flags, which are set on nearly every restaurant, so a
            // veg request came back with restaurants whose every offer is non-veg - the filter
            // looked ignored. `halal` was not read here at all. Both now require the restaurant
            // to actually hold an active item of the kind asked for.
            ->when(self::offerFoodFilters($additional_data), function ($q) use ($additional_data) {
                $q->whereHas('foods', function ($f) use ($additional_data) {
                    $f->active();
                    self::applyOfferFoodFilters($f, $additional_data);
                });
            })
            // The sheet's category picks were read off the request and then never used here,
            // so choosing a category changed nothing on this tab. A restaurant qualifies when
            // it sells something in one of them - the sub categories of a pick count too, the
            // same rule the restaurant search uses.
            ->when(! empty($additional_data['category_ids']), function ($q) use ($additional_data) {
                $q->whereHas('foods', function ($f) use ($additional_data) {
                    $f->active()->whereHas('category', function ($c) use ($additional_data) {
                        $c->where(function ($inner) use ($additional_data) {
                            $inner->whereIn('id', $additional_data['category_ids'])
                                ->orWhereIn('parent_id', $additional_data['category_ids']);
                        });
                    });
                });
            })
            ->where(function ($q) use ($additional_data) {
                $q->whereHas('discount', function ($d) {
                    $d->validate();
                })
                ->orWhereHas('foods', function ($f) {
                    $f->active()->where('discount', '>', 0);
                })
                ->orWhereHas('bogoOffers', function ($b) {
                    // Both tables carry a `status`, so the offer's own is named outright -
                    // BogoOffer::scopeActive() leaves it unqualified and MySQL rejects it here.
                    $b->where('bogo_offers.status', 1)->running()->where('bogo_offer_restaurant.status', 'approved');
                })
                // A happy hour has to qualify a restaurant here, because it no longer qualifies
                // any of its items on the other tab: the window suppresses the item rates, so a
                // restaurant whose only offer is a happy hour would otherwise appear on neither
                // and the promotion would be undiscoverable. The window is a schedule rather
                // than a column, so the ids are resolved first and constrained on.
                ->orWhereIn('id', Helpers::restaurant_ids_in_running_happy_hour($additional_data['zone_id'] ?? []));
            })
            ->when($search, function ($q) use ($search) {
                $keys = array_values(array_filter(explode(' ', $search)));
                $q->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('name', 'like', "%{$key}%");
                    }
                    $query->orWhereHas('translations', function ($t) use ($keys) {
                        $t->where('translationable_type', 'App\\Models\\Restaurant')->where('key', 'name')
                            ->where(function ($tt) use ($keys) {
                                foreach ($keys as $key) {
                                    $tt->orWhere('value', 'like', "%{$key}%");
                                }
                            });
                    });
                    $query->orWhereHas('cuisine', function ($c) use ($keys) {
                        $c->where(function ($cc) use ($keys) {
                            foreach ($keys as $key) {
                                $cc->orWhere('cuisines.name', 'like', "%{$key}%");
                            }
                        });
                    });
                });
            })
            ->applyFilters($additional_data)
            ->applyRating($additional_data['request'] ?? null);

        $advertised_ids = Advertisement::valid()
            ->whereHas('restaurant', function ($q) use ($additional_data) {
                $q->Active()->whereIn('zone_id', $additional_data['zone_id']);
            })
            ->pluck('restaurant_id')->map('intval')->unique()->values()->all();

        if (! empty($advertised_ids)) {
            $placeholders = implode(',', array_fill(0, count($advertised_ids), '?'));
            $query = $query->orderByRaw("CASE WHEN restaurants.id IN ($placeholders) THEN 0 ELSE 1 END", $advertised_ids);
        }

        $query = $query->orderBy('open', 'desc');
        $query = \App\CentralLogics\PersonalizationService::applyStorePersonalization($query, $additional_data['user_id'] ?? null);

        $paginator = $query
            ->orderBy('distance', 'asc')
            ->paginate($additional_data['limit'], ['*'], 'page', $additional_data['offset']);

        return [
            'total_size' => $paginator->total(),
            'limit' => $additional_data['limit'],
            'offset' => $additional_data['offset'],
            'restaurants' => $paginator->items(),
            'advertised_ids' => $advertised_ids,
        ];
    }

    /**
     * The best selling food of each restaurant, for the strip under a listing card.
     *
     * Ported from mart's Store::topItemsByIds(). "Top" is order count here - what the restaurant
     * actually sells - where the Offers screen's strip is the deepest discounts instead.
     *
     * The price after discount is carried too, so a card that shows a struck through price does
     * not have to re-derive it and risk disagreeing with the menu.
     */
    public static function topItemsByRestaurantIds(array $restaurantIds, int $limit = 5, $restaurants = null): \Illuminate\Support\Collection
    {
        $restaurantIds = self::normalizeIds($restaurantIds);

        if (empty($restaurantIds)) {
            return collect();
        }

        // Handed in by the caller: re-loading them through the foods hydrates a Restaurant per
        // row, and this model's retrieved hook plus its $with cost three queries each time.
        $restaurants = collect($restaurants)->keyBy('id');
        Helpers::prime_running_happy_hours($restaurants);

        return \App\Models\Food::active()
            ->whereIn('restaurant_id', $restaurantIds)
            ->with(['storage'])
            ->orderBy('restaurant_id')
            ->orderByDesc('order_count')
            // Items tie on order_count all the time, and MySQL is free to order tied rows
            // differently from one query to the next - so the same restaurant's strip came
            // back in one order on a home rail and another on an ad card. Settled here, the
            // way offerItemsByRestaurantIds() already settles its own ties.
            ->orderByDesc('id')
            ->get()
            ->groupBy('restaurant_id')
            ->map(function ($foods) use ($limit, $restaurants) {
                return $foods->take(max(1, $limit))->map(function ($food) use ($restaurants) {
                    $restaurant = $restaurants[$food->restaurant_id] ?? $food->restaurant;
                    $price = (float) $food->price;
                    $discount_amount = (float) Helpers::product_discount_calculate($food, $price, $restaurant);
                    $discount_data = Helpers::product_discount_calculate_data($food, $restaurant);

                    return [
                        'id' => (int) $food->id,
                        'name' => $food->name,
                        'image_full_url' => $food->image_full_url,
                        'price' => $price,
                        'discounted_price' => max(0, round($price - $discount_amount, config('round_up_to_digit'))),
                        'discount' => (float) ($discount_data['discount_percentage'] ?? 0),
                        'discount_type' => $discount_data['original_discount_type'] ?? $food->discount_type,
                        'veg' => (int) $food->veg,
                        'is_halal' => (int) $food->is_halal,
                        'avg_rating' => (float) $food->avg_rating,
                        'order_count' => (int) $food->order_count,
                    ];
                })->values()->all();
            });
    }

    /**
     * The strip of discounted food shown under each restaurant on the Offers screen - up to
     * $limit per restaurant, deepest discount first, resolved for the whole page in one query.
     */
    /**
     * One food as a listing card draws it: the price, what it costs after any
     * discount, and the few badges shown beside it.
     *
     * Shared by offerItemsByRestaurantIds() and foodsByRestaurantIds() so the two
     * cannot drift into returning different shapes for the same thing.
     */
    public static function format_food_for_listing($food, $restaurant): array
    {
        $price = (float) $food->price;
        $discount_amount = (float) Helpers::product_discount_calculate($food, $price, $restaurant);
        $discount_data = Helpers::product_discount_calculate_data($food, $restaurant);
        $store_wide = Helpers::store_wide_discount_data($restaurant);

        return [
            'id' => (int) $food->id,
            'name' => $food->name,
            'image_full_url' => $food->image_full_url,
            'price' => $price,
            'discounted_price' => max(0, round($price - $discount_amount, config('round_up_to_digit'))),
            'discount' => (float) ($discount_data['discount_percentage'] ?? 0),
            'discount_type' => $discount_data['original_discount_type'] ?? $food->discount_type,
            'restaurant_discount' => $store_wide['restaurant_discount'],
            'happy_hour_discount' => $store_wide['happy_hour_discount'],
            'veg' => (int) $food->veg,
            'is_halal' => (int) $food->is_halal,
            'avg_rating' => (float) $food->avg_rating,
        ];
    }

    /**
     * A few foods per restaurant in the listing shape, keyed by restaurant id.
     *
     * Unlike offerItemsByRestaurantIds() this does not filter to discounted items —
     * it is the restaurant's foods, shown on a search result — but the row shape is
     * identical.
     *
     * One query for the whole page.
     */
    public static function foodsByRestaurantIds(array $restaurantIds, int $limit = 5, $restaurants = null): \Illuminate\Support\Collection
    {
        $restaurantIds = self::normalizeIds($restaurantIds);

        if (empty($restaurantIds)) {
            return collect();
        }

        $restaurants = collect($restaurants)->keyBy('id');
        Helpers::prime_running_happy_hours($restaurants);

        // storage is what the image url accessor reads — without it this is one
        // query per food.
        return \App\Models\Food::active()
            ->whereIn('restaurant_id', $restaurantIds)
            ->with(['storage'])
            ->orderByDesc('id')
            ->get()
            ->groupBy('restaurant_id')
            ->map(fn ($foods) => $foods->take($limit)
                ->map(fn ($food) => self::format_food_for_listing($food, $restaurants[$food->restaurant_id] ?? $food->restaurant))
                ->values()->all());
    }

    /**
     * The food-level filters the Offers screen can send: veg / non veg, and halal.
     *
     * Returned as a list so the restaurant query and the strip under each card are decided by
     * the same answer - a card that survived a veg filter must not then draw a non-veg strip.
     */
    public static function offerFoodFilters(array $additional_data): array
    {
        $type = $additional_data['type'] ?? 'all';
        $filters = [];

        if (in_array($type, ['veg', 'non_veg'], true)) {
            $filters['veg'] = $type === 'veg' ? 1 : 0;
        }

        if (! empty($additional_data['halal'])) {
            $filters['is_halal'] = 1;
        }

        return $filters;
    }

    /** Applies whatever offerFoodFilters() found to a Food query. */
    public static function applyOfferFoodFilters($query, array $additional_data)
    {
        foreach (self::offerFoodFilters($additional_data) as $column => $value) {
            $query->where($column, $value);
        }

        return $query;
    }

    public static function offerItemsByRestaurantIds(array $restaurantIds, int $limit = 5, $restaurants = null, array $additional_data = []): \Illuminate\Support\Collection
    {
        $restaurantIds = self::normalizeIds($restaurantIds);

        if (empty($restaurantIds)) {
            return collect();
        }

        // The caller already holds these models. Re-loading them through the foods would hydrate
        // a Restaurant per row, and this model's retrieved hook plus its $with cost three queries
        // every time one is hydrated.
        $restaurants = collect($restaurants)->keyBy('id');
        Helpers::prime_running_happy_hours($restaurants);

        // storage is what the image url accessor reads - without it this is one query per food.
        return \App\Models\Food::active()
            ->whereIn('restaurant_id', $restaurantIds)
            ->with(['storage'])
            // Deliberately wider than Food::scopeItemDiscounted(), which is what the
            // `discounted` filter and the Offers food tab match on. This is not a filter over
            // items - it is what one restaurant is offering, drawn under its own card - and a
            // store wide rate discounts everything it sells. Narrowing it to items with a rate
            // of their own left a card advertising a discount above an empty strip.
            ->where(function ($q) {
                $q->where('discount', '>', 0)
                    ->orWhereHas('restaurant.discount', function ($d) {
                        $d->validate();
                    });
            })
            // The screen's veg / non veg / halal filter reaches the strip too: filtering the
            // list and then drawing whatever the restaurant sells is what made the filter look
            // broken - a veg request showed a card full of non-veg items.
            ->when($additional_data, fn ($q) => self::applyOfferFoodFilters($q, $additional_data))
            ->orderByDesc('discount')
            ->orderByDesc('id')
            ->get()
            ->groupBy('restaurant_id')
            ->map(function ($foods) use ($limit, $restaurants) {
                return $foods->take($limit)
                    ->map(fn ($food) => self::format_food_for_listing($food, $restaurants[$food->restaurant_id] ?? $food->restaurant))
                    ->values()->all();
            });
    }

    /**
     * The compact listing row, modelled on 6amMart's StoreLogic::format_store_for_listing().
     *
     * restaurant_data_formatting() hands back the whole restaurants row plus five foods, ten
     * coupons, the cuisine list and a reviews aggregate - per restaurant, in a loop. A listing
     * needs none of that; it needs the badges the card actually draws. This returns only those,
     * and resolves every one of them in a fixed number of queries for the whole page.
     *
     * Named for this domain where mart is named for its own: items_count is foods_count and
     * store_discount is restaurant_discount. module_id/module_type have no meaning here and are
     * dropped. Everything else keeps mart's key names and types exactly.
     */
    public static function format_restaurants_for_listing($restaurants, array $context = []): array
    {
        $restaurants = $restaurants instanceof EloquentCollection
            ? $restaurants
            : EloquentCollection::make(is_array($restaurants) ? $restaurants : iterator_to_array($restaurants));

        if ($restaurants->isEmpty()) {
            return [];
        }

        $ids = $restaurants->pluck('id')->filter()->map('intval')->unique()->values()->all();

        // Loaded here rather than per row: the listing queries do not eager load either, and
        // reading them off the model inside the loop is one query per restaurant.
        $restaurants->loadMissing(['schedules', 'restaurant_config', 'discount']);

        // get_effective_restaurant_discount() consults the happy hour for each row; primed
        // here so the page costs one lookup instead of one per restaurant.
        Helpers::prime_running_happy_hours($restaurants);

        $new_restaurant_days = (int) (Helpers::get_business_settings('new_restaurant_tag_days') ?: 30);

        // Resolved once and shared: both the bogo_offers key and the offers badge are built
        // from it, and the enrolment lookup behind it is not cheap.
        $bogoOffers = self::bogoOffersByRestaurantIds($ids, $context['order_type'] ?? null);

        $context += [
            'advertised_ids' => Advertisement::valid()->whereIn('restaurant_id', $ids)->pluck('restaurant_id')->map('intval')->unique()->all(),
            'new_threshold' => now()->subDays($new_restaurant_days),
            'ratings' => self::ratingsByRestaurantIds($ids),
            'categories' => self::topCategoriesByRestaurantIds($ids, 5),
            'bogo_offers' => $bogoOffers,
            'offers' => self::offersByRestaurantIds($ids, $bogoOffers),
            'coupons' => self::couponsByRestaurantIds($ids),
            'avg_item_discount' => self::avgItemDiscountByRestaurantIds($ids),
        ];

        return $restaurants->map(fn ($restaurant) => self::format_restaurant_for_listing($restaurant, $context))->values()->all();
    }

    /**
     * One listing row. $context carries the page-wide lookups built by
     * format_restaurants_for_listing(); each is optional, and a row asked for on its own
     * simply resolves what it needs itself.
     */
    public static function format_restaurant_for_listing($restaurant, array $context = []): array
    {
        $id = (int) $restaurant->id;

        $advertised_ids = $context['advertised_ids']
            ?? Advertisement::valid()->where('restaurant_id', $id)->pluck('restaurant_id')->map('intval')->all();

        $new_threshold = $context['new_threshold']
            ?? now()->subDays((int) (Helpers::get_business_settings('new_restaurant_tag_days') ?: 30));

        $rating = ($context['ratings'] ?? self::ratingsByRestaurantIds([$id]))[$id] ?? null;
        $category = ($context['categories'] ?? self::topCategoriesByRestaurantIds([$id], 5))[$id] ?? [];
        $offers = ($context['offers'] ?? self::offersByRestaurantIds([$id]))[$id] ?? [];
        $bogo_offers = ($context['bogo_offers'] ?? self::bogoOffersByRestaurantIds([$id], $context['order_type'] ?? null))[$id] ?? [];
        $coupons = ($context['coupons'] ?? self::couponsByRestaurantIds([$id]))[$id] ?? [];
        $avg_item_discount = ($context['avg_item_discount'] ?? self::avgItemDiscountByRestaurantIds([$id]))[$id] ?? 0.0;

        // "20-30 min" -> 20 and 30. The unit is left in delivery_time, which clients already
        // render as-is; the ints are for sorting and for "under 30 min" style filters.
        $delivery_parts = $restaurant->delivery_time ? explode('-', $restaurant->delivery_time) : [];
        $min_delivery_time = isset($delivery_parts[0]) ? (int) $delivery_parts[0] : 0;
        $max_delivery_time = isset($delivery_parts[1]) ? (int) preg_replace('/[^0-9]/', '', $delivery_parts[1]) : 0;

        // withOpen() measures in metres. Both are returned so no client has to convert.
        $distance = (float) ($restaurant->distance ?? 0);

        // The discount actually in force, so a live happy hour is reflected rather than the
        // standing discount it replaces. Happy hour is always a percentage.
        $active_discount = Helpers::get_effective_restaurant_discount($restaurant);
        $happy_hour = Helpers::get_running_happy_hour($restaurant);
        $restaurant_discount = $active_discount ? [
            'discount' => (float) $active_discount['discount'],
            'discount_type' => $happy_hour
                ? 'percent'
                : ($restaurant->discount?->discount_type ?? 'percent'),
        ] : null;

        $row = [
            'id' => $id,
            'name' => $restaurant->name,
            'slug' => $restaurant->slug,
            'logo_full_url' => $restaurant->logo_full_url,
            'cover_photo_full_url' => $restaurant->cover_photo_full_url,
            'zone_id' => (int) ($restaurant->zone_id ?? 0),
            // The store's own point, passed through as stored the way every other endpoint
            // returns it - a map pin cannot be drawn from `distance`, which is only ever
            // measured against the coordinates the request happened to carry.
            'latitude' => $restaurant->latitude,
            'longitude' => $restaurant->longitude,
            'avg_rating' => (float) ($rating->avg_rating ?? 0),
            'reviews_count' => (int) ($rating->reviews_count ?? 0),
            'foods_count' => (int) ($restaurant->foods_count ?? 0),
            'category_ids' => $category['ids'] ?? [],
            'category_names' => $category['names'] ?? [],
            'delivery_time' => $restaurant->delivery_time,
            'min_delivery_time' => $min_delivery_time,
            'max_delivery_time' => $max_delivery_time,
            // ST_Distance_Sphere answers in metres, and `distance` keeps carrying exactly that -
            // renaming or rescaling a key an app already reads would break it. The readable
            // companions come from one helper, shared with the details screen.
            'distance' => $distance,
            ...Helpers::distanceKeysFromMetres($distance),
            'open' => (int) ($restaurant->open ?? 0),
            'current_opening_time' => Helpers::getNextOpeningTime($restaurant->schedules) ?? 'closed',
            'active' => (int) $restaurant->active,
            'free_delivery' => (int) ($restaurant->free_delivery ?? 0),
            'featured' => (int) ($restaurant->featured ?? 0),
            'is_new' => (int) ($restaurant->created_at && $restaurant->created_at->greaterThanOrEqualTo($new_threshold)),
            'ad' => (int) in_array($id, $advertised_ids),
            'avg_item_discount_percentage' => (float) $avg_item_discount,
            'discount' => $restaurant_discount,
            // The window itself, not just the rate `discount` collapses it into: a happy hour
            // has a banner, a countdown and a minimum of its own, and a card cannot draw any
            // of that from a number. Null when no window is open.
            'is_happy_hour_running' => (bool) $happy_hour,
            'happy_hour' => $happy_hour?->customerPayload(),
            'offers' => $offers,
            // Every BOGO bundle this store can serve right now, in full. `offers` carries the
            // same thing as one badge among campaigns and coupons; this is what a BOGO strip
            // on the card is drawn from.
            'bogo_offers' => $bogo_offers,
            'coupons' => $coupons,
            // What the card prints beside the delivery time. Helpers::getDeliveryFee() answers
            // 'free_delivery' or 'out_of_range' as well as an amount, so it is passed through
            // as given rather than cast to a number.
            'delivery_fee' => Helpers::getDeliveryFee($restaurant),
            'verified_seller' => Helpers::get_verified_seller_status($restaurant, $restaurant->restaurant_config),
        ];

        // Only the rails that show a food strip under the restaurant ask for this, so the key is
        // absent rather than empty everywhere else.
        if (array_key_exists('top_items', $context)) {
            $row['top_items'] = $context['top_items'][$id] ?? [];
        }

        return $row;
    }

    /**
     * Average rating, review count and the 5-to-1 star breakdown per restaurant, for the
     * whole page in one query.
     *
     * The one place a restaurant's rating is counted, so the admin panel, the vendor panel
     * and every API answer the same numbers. Only reviews the admin has left active count -
     * a hidden review is hidden from the customer, so it cannot sit in the average either.
     *
     * The restaurants.rating column is NOT used: it is a running total incremented when a
     * review is submitted and never corrected when one is hidden or deleted, so it drifts
     * above the reviews that actually exist.
     */
    public static function ratingsByRestaurantIds(array $restaurantIds): \Illuminate\Support\Collection
    {
        $restaurantIds = self::normalizeIds($restaurantIds);

        if (empty($restaurantIds)) {
            return collect();
        }

        return DB::table('reviews')
            ->join('food', 'food.id', '=', 'reviews.food_id')
            ->whereIn('food.restaurant_id', $restaurantIds)
            ->where('reviews.status', 1)
            ->groupBy('food.restaurant_id')
            ->selectRaw('food.restaurant_id, AVG(reviews.rating) as avg_rating, COUNT(reviews.id) as reviews_count,
                SUM(CASE WHEN reviews.rating = 5 THEN 1 ELSE 0 END) as rating_5,
                SUM(CASE WHEN reviews.rating = 4 THEN 1 ELSE 0 END) as rating_4,
                SUM(CASE WHEN reviews.rating = 3 THEN 1 ELSE 0 END) as rating_3,
                SUM(CASE WHEN reviews.rating = 2 THEN 1 ELSE 0 END) as rating_2,
                SUM(CASE WHEN reviews.rating = 1 THEN 1 ELSE 0 END) as rating_1')
            ->get()
            ->keyBy('restaurant_id');
    }

    /**
     * One restaurant's rating summary, in the shape a page or a payload needs it:
     * ['average' => float, 'count' => int, 'buckets' => [5 star, 4, 3, 2, 1]].
     *
     * `buckets` is ordered the way Restaurant::getRatingAttribute() ordered the column it
     * replaces, so anything already reading that order keeps working.
     */
    public static function ratingSummary($restaurantId): array
    {
        $row = self::ratingsByRestaurantIds([$restaurantId])[(int) $restaurantId] ?? null;

        return [
            'average' => (float) ($row->avg_rating ?? 0),
            'count' => (int) ($row->reviews_count ?? 0),
            'buckets' => [
                (int) ($row->rating_5 ?? 0),
                (int) ($row->rating_4 ?? 0),
                (int) ($row->rating_3 ?? 0),
                (int) ($row->rating_2 ?? 0),
                (int) ($row->rating_1 ?? 0),
            ],
        ];
    }

    /**
     * The categories a restaurant actually sells, ranked by how often they have been ordered.
     * A parent category is carried into ids alongside its child, so a filter on the parent
     * still matches - the same shape mart's extract_top_category_data() returns.
     */
    public static function topCategoriesByRestaurantIds(array $restaurantIds, int $limit = 5): \Illuminate\Support\Collection
    {
        $restaurantIds = self::normalizeIds($restaurantIds);

        if (empty($restaurantIds)) {
            return collect();
        }

        $rows = DB::table('food')
            ->join('categories', 'food.category_id', '=', 'categories.id')
            ->join('order_details', 'order_details.food_id', '=', 'food.id')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->selectRaw('food.restaurant_id, CAST(categories.id AS UNSIGNED) as id, categories.parent_id, categories.name, COUNT(order_details.id) as order_count')
            ->whereIn('food.restaurant_id', $restaurantIds)
            ->where('categories.status', 1)
            ->whereNotIn('orders.order_status', ['failed', 'canceled'])
            ->groupBy('food.restaurant_id', 'id', 'categories.parent_id', 'categories.name')
            ->orderByDesc('order_count')
            ->get();

        return $rows->groupBy('restaurant_id')->map(function ($group) use ($limit) {
            $category_ids = [];
            $category_names = [];

            foreach ($group->take(max(1, $limit)) as $row) {
                if ($row->id != 0) {
                    $category_ids[] = (int) $row->id;
                    $category_names[] = $row->name;
                }
                if ($row->parent_id != 0) {
                    $category_ids[] = (int) $row->parent_id;
                }
            }

            return [
                'ids' => array_values(array_unique($category_ids)),
                'names' => array_values(array_unique($category_names)),
            ];
        });
    }

    /**
     * Running promotions per restaurant: the item campaigns it is running itself, and the
     * admin campaigns it has been confirmed into. Mart lists flash sales here too - stackfood
     * has no such feature, so a food card carries these two types only.
     */
    /**
     * The coupons a customer could use at each restaurant, keyed by restaurant id.
     *
     * Two shapes count as belonging to a restaurant, matching what
     * CouponController::restaurant_wise_coupon() returns for a single one:
     *   - the restaurant's own coupon (restaurant_id)
     *   - a restaurant_wise coupon whose `data` lists the restaurant
     *
     * Zone-wide coupons are deliberately left out: they are not tied to a
     * restaurant, so putting them on every card would misrepresent them as the
     * restaurant's own offer.
     *
     * Coupons aimed at named customers are skipped — a listing has no signed-in
     * customer to judge them against, so only the ones open to everyone are safe
     * to advertise.
     *
     * One query for the whole page, like the other listing lookups.
     */
    public static function couponsByRestaurantIds(array $restaurantIds): \Illuminate\Support\Collection
    {
        $restaurantIds = self::normalizeIds($restaurantIds);

        if (empty($restaurantIds)) {
            return collect();
        }

        $today = date('Y-m-d');

        $coupons = Coupon::active()
            ->whereDate('start_date', '<=', $today)
            ->whereDate('expire_date', '>=', $today)
            ->where(function ($query) use ($restaurantIds) {
                $query->whereIn('restaurant_id', $restaurantIds)
                    ->orWhere('coupon_type', 'restaurant_wise');
            })
            ->get(['id', 'title', 'code', 'coupon_type', 'discount', 'discount_type',
                   'min_purchase', 'max_discount', 'start_date', 'expire_date',
                   'limit', 'data', 'customer_id', 'restaurant_id']);

        $byRestaurant = [];

        foreach ($coupons as $coupon) {
            $customers = is_array($coupon->customer_id) ? $coupon->customer_id : json_decode($coupon->customer_id, true);

            if (is_array($customers) && ! in_array('all', $customers, true)) {
                continue;
            }

            $targets = [];

            if ($coupon->restaurant_id && in_array((int) $coupon->restaurant_id, $restaurantIds, true)) {
                $targets[] = (int) $coupon->restaurant_id;
            }

            if ($coupon->coupon_type === 'restaurant_wise') {
                $listed = is_array($coupon->data) ? $coupon->data : json_decode($coupon->data, true);

                foreach ((array) $listed as $listedId) {
                    if (in_array((int) $listedId, $restaurantIds, true)) {
                        $targets[] = (int) $listedId;
                    }
                }
            }

            foreach (array_unique($targets) as $restaurantId) {
                $byRestaurant[$restaurantId][] = [
                    'id' => (int) $coupon->id,
                    'title' => $coupon->title,
                    'code' => $coupon->code,
                    'coupon_type' => $coupon->coupon_type,
                    'discount' => (float) $coupon->discount,
                    'discount_type' => $coupon->discount_type,
                    'min_purchase' => (float) $coupon->min_purchase,
                    'max_discount' => (float) $coupon->max_discount,
                    'start_date' => $coupon->start_date,
                    'expire_date' => $coupon->expire_date,
                    'limit' => $coupon->limit,
                ];
            }
        }

        return collect($byRestaurant);
    }

    public static function offersByRestaurantIds(array $restaurantIds, $bogoOffers = null): \Illuminate\Support\Collection
    {
        $restaurantIds = self::normalizeIds($restaurantIds);

        if (empty($restaurantIds)) {
            return collect();
        }

        $offers = [];

        $item_campaigns = ItemCampaign::active()->running()
            ->whereIn('restaurant_id', $restaurantIds)
            ->get(['id', 'title', 'restaurant_id']);

        foreach ($item_campaigns as $campaign) {
            if (! $campaign->title) {
                continue;
            }

            $offers[(int) $campaign->restaurant_id][] = [
                'type' => 'item_campaign',
                'id' => (int) $campaign->id,
                'name' => $campaign->title,
            ];
        }

        // Read through the pivot rather than the relation: eager loading Campaign::restaurants
        // hydrates full Restaurant models, and this model's retrieved hook and $with cost
        // several queries for each one - all to learn an id the pivot already holds.
        $enrolments = DB::table('campaign_restaurant')
            ->whereIn('restaurant_id', $restaurantIds)
            ->where('campaign_status', 'confirmed')
            ->get(['campaign_id', 'restaurant_id']);

        if ($enrolments->isNotEmpty()) {
            $campaigns = Campaign::active()->running()
                ->whereIn('id', $enrolments->pluck('campaign_id')->unique()->all())
                ->get(['id', 'title'])
                ->keyBy('id');

            foreach ($enrolments as $enrolment) {
                $campaign = $campaigns[$enrolment->campaign_id] ?? null;

                if (! $campaign || ! $campaign->title) {
                    continue;
                }

                $offers[(int) $enrolment->restaurant_id][] = [
                    'type' => 'campaign',
                    'id' => (int) $campaign->id,
                    'name' => $campaign->title,
                ];
            }
        }

        // "Buy 1 Get 1 Free" on the card. Built from the same resolver the bogo_offers key
        // uses, so the badge and the list can never disagree about the same card: an approved
        // enrolment is not enough - the foods have to be orderable and the store open.
        // Handed in by a listing that has already resolved them, so the page pays for the
        // enrolment lookup once instead of once per key.
        foreach ($bogoOffers ?? self::bogoOffersByRestaurantIds($restaurantIds) as $restaurantId => $forRestaurant) {
            foreach ($forRestaurant as $offer) {
                $offers[(int) $restaurantId][] = [
                    'type' => 'bogo',
                    'id' => (int) $offer['id'],
                    'name' => $offer['title'] ?: $offer['offer_label'],
                    'buy_qty' => (int) $offer['buy_qty'],
                    'get_qty' => (int) $offer['get_qty'],
                ];
            }
        }

        return collect($offers);
    }

    /**
     * Running BOGO offers per restaurant, for a whole listing in a fixed number of queries.
     *
     * offersByRestaurantIds() answers a narrower question - one badge among campaigns and
     * coupons, built from the offer's own switches. This is the list a client draws a BOGO
     * section from, so it asks everything the offer screens ask: the enrolled foods have to be
     * in stock and switched on, and the store has to serve at least one of the order types the
     * offer runs for. Otherwise a card would promise a bundle the store's own bogo-offers
     * endpoint then answers nothing for.
     *
     * A closed store is asked about too. An offer nobody can order right now is not an offer
     * worth advertising, and a card that promises one leads to a store page that answers with
     * nothing - the offer screens have always hidden them, and the listing now agrees.
     */
    public static function bogoOffersByRestaurantIds(array $restaurantIds, $orderType = null): \Illuminate\Support\Collection
    {
        $restaurantIds = self::normalizeIds($restaurantIds);

        // The customer can be browsing in more than one mode at once - the listing takes
        // order_type as a list - so this takes whatever the listing was given rather than
        // insisting on a single string, which is what made a bracketed order_type 500 the
        // whole restaurant listing. An offer qualifies if it runs for any mode asked for.
        $orderTypes = Helpers::filter_value_list($orderType);

        if (empty($restaurantIds)) {
            return collect();
        }

        $availability = self::bogoAvailability();

        // Which of these stores could actually take an order this minute. Resolved once for
        // the page rather than per enrolment - openNow() is a schedule join.
        $openIds = \App\Models\Restaurant::withoutGlobalScopes()
            ->active()->openNow()->whereIn('id', $restaurantIds)
            ->pluck('id')->map('intval')->all();

        // items.food because the stock rule reads each enrolled food, bogo_offer.storage
        // because the card wants the offer image, and restaurant_config because dine_in is
        // the one order type that does not live on the restaurants row.
        $enrolments = \App\Models\BogoOfferRestaurant::approved()
            ->whereIn('restaurant_id', $openIds)
            ->whereHas('bogo_offer', fn ($q) => $q->active()->running()->notExhausted())
            ->with(['items.food', 'bogo_offer.storage', 'restaurant.restaurant_config'])
            ->get();

        // Every enrolled food resolved in one query before the availability rule is asked
        // about any bundle. The rule tests each food against Food::active() - the platform's
        // own definition, which a disabled category fails while the food's own status column
        // still reads 1 - and asking that per item would turn one card row into a query each.
        $availability->primeActiveFoods(
            $enrolments->flatMap(fn ($enrolment) => $enrolment->items->pluck('food_id'))->all()
        );

        $offers = [];

        foreach ($enrolments as $enrolment) {
            $offer = $enrolment->bogo_offer;

            if (! $offer || ! $enrolment->restaurant) {
                continue;
            }

            // What the customer is browsing with, when they said. An offer published for
            // dine-in only has no business on a card being read in delivery mode.
            $servesAnyRequestedType = ! $orderTypes || (bool) array_filter(
                $orderTypes,
                fn ($type) => $availability->servesOrderType($offer, (string) $type)
            );

            if (! $servesAnyRequestedType) {
                continue;
            }

            if (\App\Models\BogoOffer::orderTypesEnabled()
                && ! $enrolment->restaurant->servesAnyOrderType($offer->order_types ?? [])) {
                continue;
            }

            if ($availability->unavailableReason($enrolment, 1) !== null) {
                continue;
            }

            $offers[(int) $enrolment->restaurant_id][] = [
                'id' => (int) $offer->id,
                'slug' => $offer->slug,
                // Names the offer at this restaurant, which an offer id alone cannot when
                // several restaurants run the same one. The bundle endpoints take it.
                'bundle_id' => (int) $enrolment->id,
                'title' => $offer->title,
                'image_full_url' => $offer->image_full_url,
                'buy_qty' => (int) $offer->buy_qty,
                'get_qty' => (int) $offer->get_qty,
                'offer_label' => translate('messages.Buy').' '.$offer->buy_qty.' '.translate('messages.Get').' '.$offer->get_qty,
                'start_date' => $offer->start_date?->format('Y-m-d H:i:s'),
                'end_date' => $offer->end_date?->format('Y-m-d H:i:s'),
            ]
            // Omitted rather than sent empty while order types are switched off, so no client
            // starts reading a restriction that is not being applied.
            + (\App\Models\BogoOffer::orderTypesEnabled()
                ? ['order_types' => $offer->order_types ?? []]
                : []);
        }

        return collect($offers);
    }

    /**
     * The cart's own BOGO rules, reachable from this static class.
     *
     * bogoItemsUnavailableReason() is where "can this bundle actually be served" is decided,
     * and it has to stay the single answer - a listing advertising a bundle the cart then
     * refuses is exactly the drift the trait exists to prevent.
     */
    private static function bogoAvailability()
    {
        static $probe;

        return $probe ??= new class
        {
            use \App\Traits\HandlesBogoCart;

            public function unavailableReason(\App\Models\BogoOfferRestaurant $enrollment, int $bundleQuantity): ?string
            {
                return $this->bogoItemsUnavailableReason($enrollment, $bundleQuantity);
            }

            /**
             * This object is held in a `static` and so lives for the whole process. Priming
             * REPLACES whatever it knew, so each listing answers from the state it actually
             * ran against rather than from the first one this process ever served.
             */
            public function primeActiveFoods(array $foodIds): void
            {
                $this->bogoPrimeActiveFoods($foodIds);
            }

            public function servesOrderType(\App\Models\BogoOffer $offer, string $orderType): bool
            {
                return $this->bogoServesOrderType($offer, $orderType);
            }

        };
    }

    /**
     * How deeply a restaurant is discounting its menu, averaged over the items that carry a
     * percentage discount. Items discounted by a flat amount are left out - averaging currency
     * into a percentage would be meaningless.
     */
    public static function avgItemDiscountByRestaurantIds(array $restaurantIds): \Illuminate\Support\Collection
    {
        $restaurantIds = self::normalizeIds($restaurantIds);

        if (empty($restaurantIds)) {
            return collect();
        }

        return DB::table('food')
            ->whereIn('restaurant_id', $restaurantIds)
            ->where('status', 1)
            ->where('discount', '>', 0)
            ->where('discount_type', 'percent')
            ->groupBy('restaurant_id')
            ->selectRaw('restaurant_id, AVG(discount) as avg_discount')
            ->pluck('avg_discount', 'restaurant_id')
            ->map(fn ($value) => round((float) $value, 2));
    }

    private static function normalizeIds(array $restaurantIds): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $restaurantIds))));
    }
}
