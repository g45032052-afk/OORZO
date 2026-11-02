<?php

namespace App\CentralLogics;

use App\Models\Category;
use App\Models\Food;
use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CategoryLogic
{
    public static function parents()
    {
        $query = Category::where('position', 0);
        // Personalization: order parent categories toward the customer's
        // interests (no-op when the toggle is off / customer is a guest).
        $query = \App\CentralLogics\PersonalizationService::applyCategoryPersonalization($query, auth('api')->id());
        return $query->get();
    }

    public static function child($parent_id)
    {
        return Category::where(['parent_id' => $parent_id])->get();
    }

    public static function products(array $additional_data)
    {
        $category_food_default_status = \App\Models\BusinessSetting::where('key', 'category_food_default_status')->first();
        $category_food_default_status = $category_food_default_status ? $category_food_default_status->value : 1;
        $category_food_sort_by_general = \App\Models\PriorityList::where('name', 'category_food_sort_by_general')->where('type','general')->first();
        $category_food_sort_by_general = $category_food_sort_by_general ? $category_food_sort_by_general->value : '';
        $category_food_sort_by_unavailable = \App\Models\PriorityList::where('name', 'category_food_sort_by_unavailable')->where('type','unavailable')->first();
        $category_food_sort_by_unavailable = $category_food_sort_by_unavailable ? $category_food_sort_by_unavailable->value : '';
        $category_food_sort_by_temp_closed = \App\Models\PriorityList::where('name', 'category_food_sort_by_temp_closed')->where('type','temp_closed')->first();
        $category_food_sort_by_temp_closed = $category_food_sort_by_temp_closed ? $category_food_sort_by_temp_closed->value : '';

       $category_id= Category::where(fn($q) =>$q->where('id', $additional_data['category_id'])->orWhere('slug', $additional_data['category_id']))->select(['id'])->first()?->id ;

        $query = Food::active()->whereHas('restaurant', function($query)use($additional_data){
            return $query->whereIn('zone_id', $additional_data['zone_id']);
        })
        ->whereHas('category',function($q)use($category_id){
            return $q->whereId($category_id)->orWhere('parent_id', $category_id);
        })

        ->when($additional_data['veg'] == 1 && $additional_data['non_veg'] == 0 , function($query) {
            $query->where('veg',1);
        })

        ->when($additional_data['non_veg'] == 1 && $additional_data['veg'] == 0  , function($query) {
            $query->where('veg',0);
        })
        ->type($additional_data['type'])
        ->select(['food.*'])
        // ->leftJoin('restaurants', 'food.restaurant_id', '=', 'restaurants.id')
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

        if ($category_food_default_status == '1'){
            // Newest first, then by id: two items added in the same second tie on created_at,
            // and tied rows come back in whatever order MySQL likes, which shows up as an item
            // appearing on two pages of the same list.
            $query = $query->latest()->orderByDesc('food.id');
        }elseif ($category_food_default_status == '0'){
            $time = Carbon::now()->toTimeString();

            if($category_food_sort_by_unavailable == 'remove'){
                $query = $query->available($time);
            }elseif($category_food_sort_by_unavailable == 'last'){
                $query = $query->orderBy(DB::raw("CASE WHEN available_time_starts <= '$time' AND available_time_ends >= '$time' THEN 0 ELSE 1 END"));
            }

            if($category_food_sort_by_temp_closed == 'remove'){
                $query = $query->having('temp_available', '>', 0);
            }elseif($category_food_sort_by_temp_closed == 'last'){
                $query = $query->orderByDesc('temp_available');
            }

            if ($category_food_sort_by_general == 'nearest_first') {
                $query = $query->selectSub(function ($subQuery) use ($additional_data) {
                    $subQuery->selectRaw('ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) as distance', [$additional_data['longitude'], $additional_data['latitude']])
                        ->from('restaurants')
                        ->whereColumn('restaurants.id', 'food.restaurant_id');
                }, 'distance')
                    ->orderBy('distance');
            } elseif ($category_food_sort_by_general == 'rating') {
                $query = $query->orderByDesc('avg_rating');
            } elseif ($category_food_sort_by_general == 'review_count') {
                $query = $query->withCount('reviews')->orderByDesc('reviews_count');
            } elseif ($category_food_sort_by_general == 'order_count') {
                $query = $query->orderByDesc('order_count');
            } elseif ($category_food_sort_by_general == 'a_to_z') {
                $query = $query->orderBy('name');
            } elseif ($category_food_sort_by_general == 'z_to_a') {
                $query = $query->orderByDesc('name');
            }

        }

        // Personalization: order this category's foods toward the customer.
        $query = \App\CentralLogics\PersonalizationService::applyItemPersonalization($query, auth('api')->id());
        $paginator = $query
        ->applyFilters($additional_data)
        ->applyRating($additional_data['request'] ?? null)
        ->applyPriceRange($additional_data['request'] ?? null)
        ->applySorting($additional_data['sort_by'])
        ->paginate($additional_data['limit'], ['*'], 'page', $additional_data['offset']);


        $maxPrice = Food::whereHas('restaurant', function($query)use($additional_data){
            return $query->whereIn('zone_id', $additional_data['zone_id']);
        })
            ->whereHas('category',function($q)use($category_id){
            return $q->whereId($category_id)->orWhere('parent_id', $category_id);
        })->max('price');

        return [
            'total_size' => $paginator->total(),
            'limit' => $additional_data['limit'],
            'offset' => $additional_data['offset'],
            'products' => $paginator->items(),
            'max_price'=> (float) $maxPrice??0
        ];
    }


    public static function restaurants(array $additional_data)
    {
        $all_restaurant_default_status = \App\Models\BusinessSetting::where('key', 'all_restaurant_default_status')->first();
        $all_restaurant_default_status = $all_restaurant_default_status ? $all_restaurant_default_status->value : 1;
        $all_restaurant_sort_by_general = \App\Models\PriorityList::where('name', 'all_restaurant_sort_by_general')->where('type','general')->first();
        $all_restaurant_sort_by_general = $all_restaurant_sort_by_general ? $all_restaurant_sort_by_general->value : '';
        $all_restaurant_sort_by_unavailable = \App\Models\PriorityList::where('name', 'all_restaurant_sort_by_unavailable')->where('type','unavailable')->first();
        $all_restaurant_sort_by_unavailable = $all_restaurant_sort_by_unavailable ? $all_restaurant_sort_by_unavailable->value : '';
        $all_restaurant_sort_by_temp_closed = \App\Models\PriorityList::where('name', 'all_restaurant_sort_by_temp_closed')->where('type','temp_closed')->first();
        $all_restaurant_sort_by_temp_closed = $all_restaurant_sort_by_temp_closed ? $all_restaurant_sort_by_temp_closed->value : '';

        $category_id= Category::where(fn($q) =>$q->where('id', $additional_data['category_id'])->orWhere('slug', $additional_data['category_id']))->select(['id'])->first()?->id ;


        $query = Restaurant::withOpen($additional_data['longitude'] , $additional_data['latitude'] )->with(['discount'=>function($q){
            return $q->validate();
        }])->whereIn('zone_id', $additional_data['zone_id'] )
        ->whereHas('foods.category', function($query)use($category_id){
            return $query->whereId( $category_id)->orWhere('parent_id', $category_id);
        })

        ->when($additional_data['veg'] == 1  , function($query) {
            $query->where('veg',1);
        })
        ->when($additional_data['non_veg'] == 1  , function($query) {
            $query->where('non_veg',1);
        })
        ->when(count(is_array($additional_data['cuisine']) ? $additional_data['cuisine'] : json_decode($additional_data['cuisine'], true)) > 0  , function($query)use($additional_data) {
            $query->cuisine(is_array($additional_data['cuisine']) ? $additional_data['cuisine'] : json_decode($additional_data['cuisine'], true));
        })

        ->active()->withcount('foods')->type($additional_data['type']);

        if($all_restaurant_default_status == '1') {
            $query = $query->latest();
        }else{

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
                        ->groupBy('food.restaurant_id');
                }, 'avg_r')->orderBy('avg_r', 'desc');
            }elseif($all_restaurant_sort_by_general == 'review_count') {
                $query = $query->withCount('reviews')->orderBy('reviews_count', 'desc');
            }elseif($all_restaurant_sort_by_general == 'order_count') {
                $query = $query->withCount('orders')->orderBy('orders_count', 'desc');
            } elseif ($all_restaurant_sort_by_general == 'a_to_z') {
                $query = $query->orderBy('name');
            } elseif ($all_restaurant_sort_by_general == 'z_to_a') {
                $query = $query->orderByDesc('name');
            }

        }

        // Personalization: order this category's restaurants toward the customer.
        $query = \App\CentralLogics\PersonalizationService::applyStorePersonalization($query, auth('api')->id());
        $paginator = $query
        ->applyFilters($additional_data)
        ->applyRating($additional_data['request'] ?? null)
        ->applyPriceRange($additional_data['request'] ?? null)
        ->applySorting($additional_data['sort_by'])
        ->paginate($additional_data['limit'], ['*'], 'page', $additional_data['offset']);


        return [
            'total_size' => $paginator->total(),
            'limit' => $additional_data['limit'],
            'offset' => $additional_data['offset'],
            'restaurants' => $paginator->items()
        ];
    }


    public static function all_products($id)
    {
        $cate_ids=[];
        array_push($cate_ids,(int)$id);
        foreach (CategoryLogic::child($id) as $ch1){
            array_push($cate_ids,$ch1['id']);
            foreach (CategoryLogic::child($ch1['id']) as $ch2){
                array_push($cate_ids,$ch2['id']);
            }
        }
        $query = Food::whereIn('category_id', $cate_ids);
        // Personalization: order a category's items toward the customer's preferences.
        $query = \App\CentralLogics\PersonalizationService::applyItemPersonalization($query, auth('api')->id());
        return $query->get();
    }


    public static function export_categories($collection){
        $data = [];
        foreach($collection as $key=>$item){
            $data[] = [
                'Id'=>$item->id,
                'Name'=>$item->name,
                'Image'=>$item->image,
                'ParentId'=>$item->parent_id,
                'Position'=>$item->position,
                'Priority'=>$item->priority,
                'Status'=>$item->status == 1 ? 'active' : 'inactive',
            ];
        }
        return $data;
    }
}
