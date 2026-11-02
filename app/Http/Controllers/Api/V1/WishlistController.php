<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\CentralLogics\OrderLogic;

class WishlistController extends Controller
{
    public function add_to_wishlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'food_id' => 'required_without:restaurant_id',
            'restaurant_id' => 'required_without:food_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        if ($request->food_id && $request->restaurant_id) {
            $errors = [];
            array_push($errors, ['code' => 'data', 'message' => translate('messages.can_not_add_both_food_and_restaurant_at_same_time')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $wishlist = Wishlist::where('user_id', $request?->user()?->id)->where('food_id', $request->food_id)->where('restaurant_id', $request->restaurant_id)->first();
        if (empty($wishlist)) {
            $wishlist = new Wishlist;
            $wishlist->user_id = $request?->user()?->id;
            $wishlist->food_id = $request->food_id;
            $wishlist->restaurant_id = $request->restaurant_id;
            $wishlist->save();

            // Personalization: favouriting a food or restaurant is a positive preference signal.
            if ($request?->user()?->id) {
                if ($request->food_id) {
                    \App\CentralLogics\PersonalizationService::recordItemAction((int) $request->user()->id, (int) $request->food_id, 'item_wishlist');
                }
                if ($request->restaurant_id) {
                    \App\CentralLogics\PersonalizationService::recordStoreAction((int) $request->user()->id, (int) $request->restaurant_id, 'store_wishlist');
                }
            }

            return response()->json(['message' => translate('messages.added_to_wish_list')], 200);
        }

        return response()->json(['message' => translate('messages.already_in_wishlist')], 409);
    }

    public function remove_from_wishlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'food_id' => 'required_without:restaurant_id',
            'restaurant_id' => 'required_without:food_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $wishlist = Wishlist::when($request->food_id, function($query)use($request){
            return $query->where('food_id', $request->food_id);
        })
        ->when($request->restaurant_id, function($query)use($request){
            return $query->where('restaurant_id', $request->restaurant_id);
        })
        ->where('user_id', $request?->user()?->id)->first();

        if ($wishlist) {
            $wishlist?->delete();
            return response()->json(['message' => translate('messages.Removed_from_wish_list')], 200);

        }
        return response()->json(['message' => translate('messages.not_found')], 404);
    }

    public function wish_list(Request $request)
    {
        Helpers::getZoneIds($request);
        $zone_id= json_decode($request->header('zoneId'), true);
        $longitude= $request->header('longitude');
        $latitude= $request->header('latitude');
        $wishlists = Wishlist::where('user_id', $request?->user()?->id)->with(['food'=>function($q)use($zone_id){
            return $q->whereHas('restaurant', function($q)use($zone_id){
                $q->whereIn('zone_id', $zone_id)->active();
            });
        }, 'restaurant'=>function($q)use($zone_id,$longitude,$latitude){
            return $q->withOpen($longitude,$latitude)->whereIn('zone_id', $zone_id)->active()->withCount('foods');
        }])
        ->get();
        $wishlists = Helpers::wishlist_data_formatting(data:$wishlists,multi_data: true);
        OrderLogic::check_subscription($request->user());
        return response()->json($wishlists, 200);
    }

    public function clear_all(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:food,restaurant',
        ]);
        if($validator->fails()){
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        if($request->type == 'food'){
            $wishlist = Wishlist::where('user_id', $request?->user()?->id)->where('food_id', '!=', null)->delete();
        }else{
            $wishlist = Wishlist::where('user_id', $request?->user()?->id)->where('restaurant_id', '!=', null)->delete();
        }
        if ($wishlist) {
            return response()->json(['message' => translate('messages.Clear_all_from_wishlist')], 200);
        }
        return response()->json(['message' => translate('messages.you_have_nothing_in_your_wishlist')], 404);
    }
}
