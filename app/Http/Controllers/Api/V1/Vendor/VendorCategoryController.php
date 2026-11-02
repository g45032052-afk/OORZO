<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Models\Food;
use App\Models\Category;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;

class VendorCategoryController extends Controller
{
    public function get_categories(Request $request)
    {
        try {

            $name= $request->query('name');
            $search = $request->query('search');
            $categories = Category::when($search, function($query)use($search){
                return $query->where('name', 'like', "%{$search}%");
            })->withCount(['products','childes'])->with(['childes' => function($query)  {
                $query->withCount(['products','childes']);
            }])
            ->where(['position'=>0,'status'=>1])

            ->when($name, function($q)use($name){
                $key = explode(' ', $name);
                $q->where(function($q)use($key){
                    foreach ($key as $value){
                        $q->orWhere('name', 'like', '%'.$value.'%')->orWhere('slug', 'like', '%'.$value.'%');
                    }
                    return $q;
                });
            }) ->orderBy('priority','desc') ->get();

                foreach ($categories as $category) {
                        $productCountQuery = Food::active()
                            ->where('restaurant_id', $request['vendor']->restaurants[0]->id)

                        ->whereHas('category',function($q)use($category){
                            return $q->whereId($category->id)->orWhere('parent_id', $category->id);
                        })
                        ->withCount('orders');

                        $productCount = $productCountQuery->count();
                        $orderCount = $productCountQuery->sum('order_count');

                        $category['products_count'] = $productCount;
                        $category['order_count'] = $orderCount;
                    // unset($category['childes']);
                }
                return response()->json($categories, 200);
        } catch (\Exception $e) {
            return response()->json([$e->getMessage()]);
        }
    }



    public function get_childes(Request $request,$id)
    {
        try {
            $categories = Category::when(is_numeric($id),function ($qurey) use($id){
                $qurey->where(['parent_id' => $id,'status'=>1]);
                })
                ->when(!is_numeric($id),function ($qurey) use($id){
                    $qurey->where(['slug' => $id,'status'=>1]);
                })
            ->orderBy('priority','desc')->get();
            return response()->json(Helpers::category_data_formatting($categories, true), 200);
        } catch (\Exception $e) {
            return response()->json([$e->getMessage()], 200);
        }
    }

    public function category_wise_products(Request $request)
    {
        $limit = $request->limit?$request->limit:25;
        $offset = $request->offset?$request->offset:1;
        $category_id = $request->category_id ? $request->category_id : 0;

        $type = $request->query('type', 'all');

        $paginator = Food::type($type)->where('restaurant_id', $request['vendor']->restaurants[0]->id);
        if($request->sub_category == 1){
            $paginator = $paginator->where('category_id', $category_id);
        } else {
            $paginator = $paginator->whereRaw(
                "JSON_CONTAINS(category_ids, JSON_OBJECT('id', CAST(? AS CHAR), 'position', ?), '$')",
                [$category_id, 1]
            );
        }
        $paginator = $paginator->latest()->paginate($limit, ['*'], 'page', $offset);
        $data = [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => Helpers::product_data_formatting(data:$paginator->items(), multi_data: true, trans:true, local:app()->getLocale())
        ];

        return response()->json($data, 200);
    }
}
