<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Cuisine;
use App\Models\Restaurant;
use App\Models\PriorityList;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\CentralLogics\RestaurantLogic;
use App\Models\BusinessSetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CuisineController extends Controller
{
    public function get_all_cuisines(Request $request)
    {

        $cuisine_list_default_status = BusinessSetting::where('key', 'cuisine_list_default_status')->first()?->value ?? 1;
        $cuisine_list_sort_by_general = PriorityList::where('name', 'cuisine_list_sort_by_general')->where('type','general')->first()?->value ?? '';
        $relationships = [
            'translations' => 'name',
        ];
        $Cuisines = Cuisine::where('status',1)
        ->search($request->name,$relationships)

        ->when($cuisine_list_default_status  == 1  || ($cuisine_list_default_status  != 1 &&  $cuisine_list_sort_by_general == 'latest') , function ($query) {
            $query->latest();
        })

        ->when($cuisine_list_default_status  != 1 &&  $cuisine_list_sort_by_general == 'oldest', function ($query) {
            $query->oldest();
        })
        ->when($cuisine_list_default_status  != 1 &&  $cuisine_list_sort_by_general == 'a_to_z', function ($query) {
            $query->orderby('name');
        })
        ->when($cuisine_list_default_status  != 1 &&  $cuisine_list_sort_by_general == 'z_to_a', function ($query) {
            $query->orderby('name','desc');
        })
        ->when($cuisine_list_default_status  != 1 &&  $cuisine_list_sort_by_general == 'restaurant_count', function ($query) {
            $query->withCount('restaurants')
            ->orderByDesc('restaurants_count');
        })

        ->get();




        return response()->json( ['Cuisines' => $Cuisines], 200);
    }
    public function get_restaurants(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cuisine' => 'required',
            'limit' => 'required',
            'offset' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 203);
        }
        Helpers::getZoneIds($request);

        $additional_data=[
            'zone_id'=> json_decode($request->header('zoneId'), true),
            'filter'=> $request->query('filter_data') ?? $request->filter_data,
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
            'delivery' =>$request->delivery ?? null,
            'takeaway' =>$request->takeaway ?? null,
            'avg_rating' =>$request->avg_rating ?? null,
            'sort_by' => $request->query('sort_by') ?? 'default',
            'filter_by' => $request->query('filter_by', []) ?? [],
            'cuisine_id' => $request->query('cuisine_id')
                ? (is_array($request->query('cuisine_id')) ? $request->query('cuisine_id') : (json_decode($request->query('cuisine_id'), true) ?? $request->query('cuisine_id')))
                : (is_array($request->query('cuisine')) ? $request->query('cuisine') : (json_decode($request->query('cuisine'), true) ?? [])),
            'order_type' => $request->query('order_type'),
            'request' => $request,
        ];

        $data = RestaurantLogic::get_restaurants(additional_data: $additional_data );

        $restaurants_data = Helpers::restaurant_data_formatting(data:$data['restaurants'],multi_data: true);

        $data = [
            'total_size' => $data['total_size'],
            'limit' => $data['limit'],
            'offset' => $data['offset'],
            'restaurants' => $restaurants_data,
        ];
        return response()->json($data, 200);

    }
}
