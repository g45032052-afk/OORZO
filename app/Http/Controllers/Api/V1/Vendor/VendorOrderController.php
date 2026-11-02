<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Models\Food;
use App\Models\Order;
use App\Models\Zone;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Traits\PlaceNewOrder;
use Illuminate\Support\Facades\Validator;
use MatanYadaev\EloquentSpatial\Objects\Point;

class VendorOrderController extends Controller
{
    use PlaceNewOrder;
    public function getSearchedFoods(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $vendor = $request['vendor'];
        $key = explode(' ', $request['name']);
        $limit = $request['limit'] ?? 25;
        $offset = $request['offset'] ?? 1;

        $products = Food::active()->with('restaurant.restaurant_sub')
            ->where('restaurant_id', $vendor->restaurants[0]?->id)
            ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }
                $q->orWhereHas('tags', function ($query) use ($key) {
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->where('tag', 'like', "%{$value}%");
                        };
                    });
                });
            })
            ->paginate($limit, ['*'], 'page', $offset);
        $data = [
            'total_size' => $products->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => Helpers::product_data_formatting(data: $products, multi_data: true, trans: false, local: app()->getLocale())
        ];


        return response()->json($data, 200);
    }


    public function customerAddressUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'address_type' => 'required',
            'contact_person_number' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'order_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $restaurant = $request['vendor']->restaurants[0];

        $order = Order::whereIn('order_status', ['pending', 'confirmed', 'processing'])->where('restaurant_id', $restaurant->id)->where('id', $request->order_id)->first();
        if (!$order) {
            return response()->json(['errors' =>  'order_id', 'message' => translate('Order not found')], 403);
        }

        $zone = Zone::where('id', $restaurant->zone_id)->whereContains('coordinates', new Point($request->latitude, $request->longitude, POINT_SRID))->first();
        if (!$zone) {
            return response()->json(['errors' =>  'order_id', 'message' => translate('out_of_coverage')], 403);
        }
        $address = [
            'contact_person_name' => $request->contact_person_name,
            'contact_person_number' => $request->contact_person_number,
            'address_type' => $request->address_type,
            'address' => $request->address,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'floor' => $request->floor,
            'house' => $request->house,
            'road' => $request->road,
        ];

        $order->delivery_address = json_encode($address);
        $order->save();
        $this->makeEditOrderLogs($order->id, 'edited_delivery_info', 'vendor');

        return response()->json(['message' => translate('messages.Address_updated')], 200);
    }



    public function updateOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $restaurant = $request['vendor']->restaurants[0];

        $order = Order::with('details')
            ->whereIn('order_status', ['pending', 'confirmed','accepted', 'processing'])->where('restaurant_id', $restaurant->id)
            ->where('id', $request->order_id)->first();
        if (!$order) {
            return response()->json(['errors' =>  'order_id', 'message' => translate('Order not found')], 403);
        }

       $order= $this->makeEditOrderDetails(order:$order, carts:$request->carts,restaurant: $restaurant,editedBy: 'vendor_app',editLogs:$request->edit_history_log);

        if(data_get($order,'status_code') !== 200 ){
                return response()->json([
                    'errors' => [
                        ['code' => data_get($order,'code'), 'message' => data_get($order,'message')]
                    ]
                ], data_get($order,'status_code'));
            }

        return response()->json(['message' => translate('messages.Order_updated')], 200);
    }
}
