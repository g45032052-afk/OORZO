<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Food;
use App\Models\Category;
use App\Models\Cuisine;
use Illuminate\Support\Facades\DB;
use App\Models\PriorityList;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\CentralLogics\CategoryLogic;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function get_categories(Request $request)
    {
        try {
            $category_list_default_status = BusinessSetting::where('key', 'category_list_default_status')->first()?->value ?? 1;
            $category_list_sort_by_general = PriorityList::where('name', 'category_list_sort_by_general')->where('type','general')->first()?->value ?? '';
            Helpers::getZoneIds($request);
            $zone_id=  $request->header('zoneId') ? json_decode($request->header('zoneId'), true) : [];
            $name= $request->query('name');
            $categories = Category::withCount(['products','childes'])->with(['childes' => function($query)  {
                // Same order the dedicated childes endpoint returns them in, so a
                // sub category does not sit in one place on the category list and
                // another after the customer opens its parent.
                $query->withCount(['products','childes'])->orderBy('priority','desc');
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
            })

            ->when($category_list_default_status  == 1 , function ($query) {
                $query->orderBy('priority','desc');
            })


            ->when($category_list_default_status  != 1 &&  $category_list_sort_by_general == 'latest', function ($query) {
                $query->latest();
            })
            ->when($category_list_default_status  != 1 &&  $category_list_sort_by_general == 'oldest', function ($query) {
                $query->oldest();
            })
            ->when($category_list_default_status  != 1 &&  $category_list_sort_by_general == 'a_to_z', function ($query) {
                $query->orderby('name');
            })
            ->when($category_list_default_status  != 1 &&  $category_list_sort_by_general == 'z_to_a', function ($query) {
                $query->orderby('name','desc');
            });

            $categories = \App\CentralLogics\PersonalizationService::applyCategoryPersonalization($categories, auth('api')->id())
            ->get();



            if(count($zone_id) > 0){
                // One grouped pass instead of a query per category. Food::category is a
                // plain belongsTo on category_id, so counting by that column and adding
                // each parent's children back in reproduces the old
                // "this category or its children" count exactly.
                $categoryIds = $categories->pluck('id')
                    ->merge($categories->pluck('childes')->flatten()->pluck('id'))
                    ->filter()->unique()->values();

                $counts = Food::active()
                    ->whereHas('restaurant', function ($query) use ($zone_id) {
                        $query->whereIn('zone_id', $zone_id);
                    })
                    ->whereIn('category_id', $categoryIds)
                    ->groupBy('category_id')
                    ->selectRaw('category_id, COUNT(*) as products_count, COALESCE(SUM(order_count), 0) as order_count')
                    ->get()
                    ->keyBy('category_id');

                foreach ($categories as $category) {
                    $productCount = (int) ($counts[$category->id]->products_count ?? 0);
                    $orderCount   = (int) ($counts[$category->id]->order_count ?? 0);

                    // A subcategory with nothing in it opens onto an empty screen, so it
                    // is dropped the same way its parent would be.
                    $childes = collect($category->childes ?? [])->filter(function ($child) use ($counts, &$productCount, &$orderCount) {
                        $childProducts = (int) ($counts[$child->id]->products_count ?? 0);
                        $productCount += $childProducts;
                        $orderCount   += (int) ($counts[$child->id]->order_count ?? 0);

                        $child['products_count'] = $childProducts;

                        return $childProducts > 0;
                    })->values();

                    $category->setRelation('childes', $childes);
                    $category['childes_count']  = $childes->count();
                    $category['products_count'] = $productCount;
                    $category['order_count']    = $orderCount;
                }

                // Nothing to open means nothing to show - the same rule the merged
                // categories-cuisines strip applies.
                $categories = $categories->filter(fn ($category) => $category['products_count'] > 0)->values();

                if($category_list_default_status  != 1 &&  $category_list_sort_by_general == 'order_count'){

                    $categories = $categories->sortByDesc('order_count')->values()->all();
                }


                return response()->json($categories, 200);
            }

            $categories = $categories->filter(fn ($category) => ($category->products_count ?? 0) > 0)->values();

            return response()->json(Helpers::category_data_formatting($categories, true), 200);
        } catch (\Exception $e) {
            return response()->json([$e->getMessage()]);
        }
    }
    public function get_categories_and_cuisines(Request $request)
    {
        try {
            Helpers::getZoneIds($request);
            $zone_id = $request->header('zoneId') ? json_decode($request->header('zoneId'), true) : [];
            $zone_id = is_array($zone_id) ? $zone_id : [];

            $type = $request->query('type') ?: 'all';
            $name = $request->query('name');
            $limit = $request->query('limit');
            $offset = max(1, (int) ($request->query('offset') ?? 1));

            // Nothing to open means nothing to show, and each side is ranked by what it
            // actually sells. Priority is deliberately not consulted here - this strip is
            // ordered by demand, not by the admin's weighting.
            $rank = function (array $rows) {
                $rows = array_values(array_filter($rows, fn ($row) => $row['item_count'] > 0));

                // Alphabetical settles equal order counts, so a strip that is paginated
                // cannot repeat one row on two pages and drop another entirely.
                usort($rows, fn ($a, $b) => [$b['order_count'], $a['name']] <=> [$a['order_count'], $b['name']]);

                return $rows;
            };

            $categories = ($type == 'all' || $type == 'category')
                ? $rank($this->merged_categories($zone_id, $name))
                : [];

            $cuisines = ($type == 'all' || $type == 'cuisine')
                ? $rank($this->merged_cuisines($zone_id, $name))
                : [];

            $data = $type == 'all'
                ? $this->interleave_categories_and_cuisines($categories, $cuisines)
                : array_merge($categories, $cuisines);

            $total_size = count($data);

            if ($limit !== null && $limit !== '') {
                $limit = max(1, (int) $limit);
                $data = array_slice($data, ($offset - 1) * $limit, $limit);
            }

            return response()->json([
                'total_size' => $total_size,
                'limit' => $limit,
                'offset' => $offset,
                'data' => array_values($data),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([$e->getMessage()], 200);
        }
    }

    /**
     * Two categories, then one cuisine, repeating.
     *
     * Whichever side runs out first, the remainder of the other is appended rather
     * than dropped, so the strip never loses a row just because the ratio no longer
     * divides evenly.
     */
    private function interleave_categories_and_cuisines(array $categories, array $cuisines): array
    {
        $merged = [];
        $c = 0;
        $u = 0;

        while ($c < count($categories) || $u < count($cuisines)) {
            for ($i = 0; $i < 2 && $c < count($categories); $i++) {
                $merged[] = $categories[$c++];
            }

            if ($u < count($cuisines)) {
                $merged[] = $cuisines[$u++];
            }
        }

        return $merged;
    }

    private function merged_categories(array $zone_id, $name)
    {
        $categories = Category::with('childes:id,parent_id')
            ->where(['position' => 0, 'status' => 1])
            ->when($name, function ($q) use ($name) {
                $key = explode(' ', $name);
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', '%'.$value.'%')->orWhere('slug', 'like', '%'.$value.'%');
                    }
                    return $q;
                });
            })
            ->get();

        // One grouped count for every category, rather than a query per row.
        $food_counts = Food::active()
            ->when(count($zone_id) > 0, function ($q) use ($zone_id) {
                $q->whereHas('restaurant', function ($r) use ($zone_id) {
                    $r->whereIn('zone_id', $zone_id);
                });
            })
            ->selectRaw('category_id, count(*) as total, COALESCE(SUM(order_count), 0) as orders')
            ->groupBy('category_id')
            ->get();

        $order_counts = $food_counts->pluck('orders', 'category_id');
        $food_counts  = $food_counts->pluck('total', 'category_id');

        return $categories->map(function ($category) use ($food_counts, $order_counts) {
            $count = (int) ($food_counts[$category->id] ?? 0);
            $orders = (int) ($order_counts[$category->id] ?? 0);

            foreach ($category->childes as $child) {
                $count  += (int) ($food_counts[$child->id] ?? 0);
                $orders += (int) ($order_counts[$child->id] ?? 0);
            }

            return [
                'type' => 'category',
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'image_full_url' => $category->image_full_url,
                'item_count' => $count,
                'order_count' => $orders,
            ];
        })->all();
    }

    private function merged_cuisines(array $zone_id, $name)
    {
        $cuisines = Cuisine::where('status', 1)
            ->search($name, ['translations' => 'name'])
            ->withCount(['restaurants' => function ($q) use ($zone_id) {
                // A restaurant with no active food is nothing to browse, so it is not counted.
                $q->active()->whereHas('foods', function ($f) {
                    $f->active();
                })->when(count($zone_id) > 0, function ($r) use ($zone_id) {
                    $r->whereIn('zone_id', $zone_id);
                });
            }])
            ->get();

        // How much a cuisine actually sells, summed over its restaurants' items -
        // the same figure a category is ranked by, so the two are comparable. One
        // grouped query rather than a lookup per cuisine.
        $order_counts = DB::table('cuisine_restaurant')
            ->join('restaurants', 'restaurants.id', '=', 'cuisine_restaurant.restaurant_id')
            ->join('food', 'food.restaurant_id', '=', 'restaurants.id')
            ->where('restaurants.status', 1)
            ->where('food.status', 1)
            ->when(count($zone_id) > 0, function ($q) use ($zone_id) {
                $q->whereIn('restaurants.zone_id', $zone_id);
            })
            ->groupBy('cuisine_restaurant.cuisine_id')
            ->selectRaw('cuisine_restaurant.cuisine_id as cuisine_id, COALESCE(SUM(food.order_count), 0) as orders')
            ->pluck('orders', 'cuisine_id');

        return $cuisines->map(function ($cuisine) use ($order_counts) {
            return [
                'type' => 'cuisine',
                'id' => $cuisine->id,
                'name' => $cuisine->name,
                'slug' => $cuisine->slug,
                'image_full_url' => $cuisine->image_full_url,
                'item_count' => (int) $cuisine->restaurants_count,
                'order_count' => (int) ($order_counts[$cuisine->id] ?? 0),
            ];
        })->all();
    }
    public function get_top_categories(Request $request)
    {
        try {
            Helpers::getZoneIds($request);

            $zone_id = $request->header('zoneId') ? json_decode($request->header('zoneId'), true) : [];
            $zone_ids = is_array($zone_id) ? array_filter($zone_id, 'is_numeric') : [];
            $zone_condition = ! empty($zone_ids) ? 'AND restaurants.zone_id IN ('.implode(',', array_map('intval', $zone_ids)).')' : '';

            $limit = (int) $request->query('limit', 20);
            $offset = (int) $request->query('offset', 1);

            $paginator = Category::with(['childes' => function ($query) use ($zone_ids) {
                    // Same rule as the parents: a sub category with no food here is not listed.
                    $query->where('status', 1)
                        ->whereHas('products', function ($q) use ($zone_ids) {
                            $q->active()->when(! empty($zone_ids), function ($p) use ($zone_ids) {
                                $p->whereHas('restaurant', function ($r) use ($zone_ids) {
                                    $r->whereIn('zone_id', $zone_ids);
                                });
                            });
                        })
                        ->select('id', 'name', 'image', 'slug', 'parent_id');
                }])
                ->select('categories.*')
                ->selectRaw("(SELECT COALESCE(SUM(food.order_count), 0) FROM food
                                JOIN restaurants ON restaurants.id = food.restaurant_id
                                WHERE food.status = 1
                                    AND JSON_CONTAINS(food.category_ids, JSON_OBJECT('id', CAST(categories.id AS CHAR), 'position', 1), '$')
                                    $zone_condition ) AS total_order_count")
                ->where(['position' => 0, 'status' => 1])
                ->orderByDesc('total_order_count')
                ->paginate($limit, ['*'], 'page', $offset);

            $categories = collect($paginator->items())->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image_full_url' => $category->image_full_url,
                    'order_count' => (int) $category->total_order_count,
                    'slug' => $category->slug,
                    'childes' => $category->childes->map(fn ($child) => [
                        'id' => $child->id,
                        'name' => $child->name,
                        'slug' => $child->slug,
                    ]),
                ];
            });

            return response()->json([
                'total_size' => $paginator->total(),
                'limit' => $limit,
                'offset' => $offset,
                'categories' => $categories,
            ], 200);
        } catch (\Exception $e) {
            info(['top_categories' => $e->getMessage()]);

            return response()->json(['total_size' => 0, 'limit' => (int) $request->query('limit', 20), 'offset' => (int) $request->query('offset', 1), 'categories' => []], 200);
        }
    }

    public function get_childes($id, Request $request)
    {
        $categoryId = Category::when(!is_numeric($id),function ($qurey) use($id){
                    $qurey->where('slug',$id);
                },function ($qurey) use($id){
                    $qurey->where('id',$id);
                })->first()?->id;
        try {
            Helpers::getZoneIds($request);
            $zone_id = $request->header('zoneId') ? json_decode($request->header('zoneId'), true) : [];
            $zone_id = is_array($zone_id) ? $zone_id : [];

            $categories = Category::where(['parent_id' => $categoryId,'status'=>1])
                // A sub category with no food to open in this zone is left out of the list.
                ->whereHas('products', function ($q) use ($zone_id) {
                    $q->active()->when(count($zone_id) > 0, function ($p) use ($zone_id) {
                        $p->whereHas('restaurant', function ($r) use ($zone_id) {
                            $r->whereIn('zone_id', $zone_id);
                        });
                    });
                })
                ->orderBy('priority','desc')->get();
            return response()->json(Helpers::category_data_formatting($categories, true), 200);
        } catch (\Exception $e) {
            return response()->json([$e->getMessage()], 200);
        }
    }

    public function get_products($id, Request $request)
    {
        Helpers::getZoneIds($request);
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        // The storefront sends these as flat `param=1` flags and the filter sheet sends the
        // same things as `filter_by[]` chips. Only the chips were ever read here, so a flat
        // new=1, popular=1 or discounted=1 was collected below and then quietly ignored -
        // the same mapping products/latest already does.
        $filter_by = Helpers::filter_value_list($request->query('filter_by', []));

        foreach (['new' => 'new_arrivals', 'popular' => 'popular', 'discounted' => 'discounted', 'top_rated' => 'top_rated'] as $param => $chip) {
            if ($request->$param == 1 && ! in_array($chip, $filter_by, true)) {
                $filter_by[] = $chip;
            }
        }

        $additional_data=[
            'category_id' =>  $id,
            'zone_id' => json_decode($request->header('zoneId'), true),
            'limit' =>  $request['limit'] ?? 25,
            'offset' =>  $request['offset'] ?? 1,
            'type' =>  $request->query('type', 'all') ?? 'all',
            'veg' =>  $request->veg ?? 0,
            'non_veg' =>  $request->non_veg ?? 0,
            'new' =>  $request->new ?? 0,
            'avg_rating' => $request->avg_rating ?? 0,
            'top_rated' =>  $request->top_rated ?? 0,
            'request' => $request,
            'longitude' =>$request->header('longitude') ?? 0,
            'latitude' => $request->header('latitude') ?? 0,
            'sort_by' => $request->query('sort_by') ?? 'default',
            'filter_by' => $filter_by,
            // The filter sheet sends the cuisine picks with this screen too, and
            // applyFilters() knows what to do with them - they were simply never passed
            // through, so choosing a cuisine here changed nothing. Both spellings, as the
            // search endpoint accepts both.
            'cuisine_id' => Helpers::filter_id_list($request->query('cuisine_id') ?? $request->query('cuisine')),
            'order_type' => $request->query('order_type',[]) ?? [],
        ];


        $data = CategoryLogic::products($additional_data);
        $data['products'] = Helpers::product_data_formatting($data['products'] , true, false, app()->getLocale());

        if($request->user !== null){
            $customer_id = $request->user->id;
            Helpers::visitor_log('category',$customer_id,$id,false);
        }

        return response()->json($data, 200);
    }


    public function get_restaurants($id, Request $request)
    {
        Helpers::getZoneIds($request);
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $additional_data=[
            'category_id' =>  $id,
            'zone_id' => json_decode($request->header('zoneId'), true),
            'limit' =>  $request['limit'] ?? 25,
            'offset' =>  $request['offset'] ?? 1,
            'type' =>  $request->query('type', 'all') ?? 'all',
            'longitude' =>$request->header('longitude') ?? 0,
            'latitude' => $request->header('latitude') ?? 0,
            'veg' =>  $request->veg ?? 0,
            'non_veg' =>  $request->non_veg ?? 0,
            'new' =>  $request->new ?? 0,
            'avg_rating' => $request->avg_rating ?? 0,
            'top_rated' =>  $request->top_rated ?? 0,
            'request' => $request,
            'sort_by' => $request->query('sort_by') ?? 'default',
            'filter_by' => $request->query('filter_by', []) ?? [],
            'cuisine' => $request->query('cuisine', []) ?? [],
            'order_type' => $request->query('order_type',[]) ?? [],
        ];


        $data = CategoryLogic::restaurants($additional_data);
        $data['restaurants'] = Helpers::restaurant_data_formatting($data['restaurants'] , true);

        return response()->json($data, 200);
    }



    public function get_all_products($id,Request $request)
    {
        try {
            return response()->json(Helpers::product_data_formatting(CategoryLogic::all_products($id), true, false, app()->getLocale()), 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
}
