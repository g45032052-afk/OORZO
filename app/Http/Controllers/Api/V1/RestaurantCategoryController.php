<?php

namespace App\Http\Controllers\Api\V1;


use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Food;
use App\Models\Restaurant;
use App\Models\RestaurantCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RestaurantCategoryController extends Controller
{
    public function get_categories(Request $request)
    {
        try {
            $restaurantId = $request->query('restaurant_id');
            $name = $request->query('name');

            $categories = RestaurantCategory::query()
                ->where('status', 1)
                ->when($restaurantId, function ($query) use ($restaurantId) {
                    $query->where('restaurant_id', $restaurantId);
                })
                ->when($name, function ($query) use ($name) {
                    $key = explode(' ', $name);
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('name', 'like', '%' . $value . '%')
                                ->orWhere('slug', 'like', '%' . $value . '%');
                        }
                    });
                })
                ->orderBy('priority', 'asc')
                ->get();

            return response()->json($categories, 200);
        } catch (\Exception $e) {
            return response()->json([$e->getMessage()], 200);
        }
    }

    public function get_by_restaurant($restaurantId, Request $request)
    {
        $request->merge(['restaurant_id' => $restaurantId]);
        return $this->get_categories($request);
    }

    public function get_categories_with_foods(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        try {
            Helpers::getZoneIds($request);

            $restaurantId = $request->query('restaurant_id');

            $cuisine_id = $request->query('cuisine_id') ?? $request->query('cuisine');
            if ($cuisine_id && !is_array($cuisine_id)) {
                $cuisine_id = json_decode($cuisine_id, true);
            }

            $additional_data = [
                'sort_by' => $request->query('sort_by') ?? 'default',
                'filter_by' => $request->query('filter_by', []) ?? [],
                'cuisine_id' => $cuisine_id ?? [],
                'order_type' => $request->query('order_type', []) ?? [],
                'restaurant_category_id' => $request->restaurant_category_id ?? null,
            ];

            $type = $request->query('type', 'all');

            // The search box on this screen. Treated the way every other listing treats it:
            // blank and the literal string "null" both mean "no search".
            $search = trim((string) ($request->query('name') ?? ''));
            $search = ($search === '' || strtolower($search) === 'null') ? null : $search;

            // The rate the cards will advertise is time dependent - a happy hour opens and
            // closes on a schedule - so it belongs in the key. Without it a payload cached
            // moments before a window opened would keep showing the old discount for up to
            // ten minutes, and keep showing the window's rate for ten minutes after it shut.
            //
            // Both rates go in, not the one figure they collapse to: a standing 20% and a happy
            // hour of 20% used to produce identical payloads, so one key served both. They no
            // longer do - the payload now says which promotion is running - and a shared key
            // would hand a happy hour's cards to a store discount for the next ten minutes.
            $discountRestaurant = Restaurant::find($restaurantId);
            $storeWideForKey = $discountRestaurant
                ? Helpers::store_wide_discount_data($discountRestaurant)
                : ['restaurant_discount' => 0.0, 'happy_hour_discount' => 0.0];

            $cacheKey = 'restaurant_cat_foods_' . md5(json_encode([
                'restaurant' => $restaurantId,
                'zone'       => $request->header('zoneId'),
                'type'       => $type,
                'additional' => $additional_data,
                // Every input that narrows the result set belongs here. The rating chips and
                // the `price=[min,max]` range were missing, so the first payload cached for a
                // restaurant was replayed for ten minutes whatever the customer then filtered
                // by - the filters looked dead from the app even though the query honoured them.
                'name'       => $search,
                'rating'     => $request->only([
                    'rating', 'rating_count',
                    'rating_1', 'rating_2', 'rating_3', 'rating_4', 'rating_5',
                    'rating_1_plus', 'rating_2_plus', 'rating_3_plus', 'rating_4_plus',
                ]),
                'price'      => $request->query('price'),
                'min_price'  => $request->query('min_price'),
                'max_price'  => $request->query('max_price'),
                'locale'     => app()->getLocale(),
                'discount'   => $storeWideForKey,
            ]));

            $payload = Cache::remember($cacheKey, now()->addMinutes(10), function () use (
                $request, $restaurantId, $additional_data, $type, $search
            ) {
                $foodTableName = (new Food)->getTable();

                $decode = function ($val) {
                    if (is_array($val)) {
                        return $val;
                    }
                    if (!is_string($val) || $val === '') {
                        return [];
                    }
                    $decoded = json_decode($val, true);

                    return is_array($decoded) ? $decoded : [];
                };

                $applyFoodConstraints = function ($q) use ($additional_data, $type, $request, $search) {
                    $q->active()
                        ->applyFilters($additional_data)
                        ->applySorting($additional_data['sort_by'])
                        ->applyRating($request)
                        ->applyPriceRange($request)
                        ->type($type)
                        // Same match the product listings use: the food's own name, its
                        // translated name, or one of its tags.
                        ->when($search, function ($query) use ($search) {
                            $keys = array_values(array_filter(explode(' ', $search)));
                            $query->where(function ($q) use ($keys) {
                                foreach ($keys as $key) {
                                    $q->orWhere('food.name', 'like', "%{$key}%");
                                }
                                $q->orWhereHas('translations', function ($t) use ($keys) {
                                    $t->where('key', 'name')->where(function ($tt) use ($keys) {
                                        foreach ($keys as $key) {
                                            $tt->orWhere('value', 'like', "%{$key}%");
                                        }
                                    });
                                });
                                $q->orWhereHas('tags', function ($t) use ($keys) {
                                    $t->where(function ($tt) use ($keys) {
                                        foreach ($keys as $key) {
                                            $tt->orWhere('tag', 'like', "%{$key}%");
                                        }
                                    });
                                });
                            });
                        });
                };

                $useRestaurantCategory = $restaurantId
                    ? RestaurantCategory::active()
                        ->where('restaurant_id', $restaurantId)
                        ->whereHas('foods', function ($q) {
                            $q->active();
                        })
                        ->exists()
                    : true;
                $restaurantCategoryAdminStatus = Helpers::get_business_settings('restaurant_category_status') ?? 0;

                if ($useRestaurantCategory && $restaurantCategoryAdminStatus == 1) {
                    $categories = RestaurantCategory::active()
                        ->when($restaurantId, function ($q) use ($restaurantId) {
                            $q->where('restaurant_id', $restaurantId);
                        })
                        ->whereHas('foods', function ($q) {
                            $q->active();
                        })
                        ->orderBy('priority', 'desc')
                        ->orderBy('name', 'asc')
                        ->get(['id', 'name', 'image', 'status', 'priority']);

                    $categoryIds = $categories->pluck('id')->map(fn ($v) => (int) $v)->all();
                    $categoryKeyExpr = "{$foodTableName}.restaurant_category_id";
                    $baseFoodScope = function ($q) use ($foodTableName, $categoryIds) {
                        $q->whereIn("{$foodTableName}.restaurant_category_id", $categoryIds);
                    };
                    $groupBy = 'restaurant_category_id';
                    $categorySource = 'restaurant_category';
                } else {
                    $mainCategoryExpr = "CAST(JSON_UNQUOTE(JSON_EXTRACT({$foodTableName}.category_ids, '$[0].id')) AS UNSIGNED)";

                    $usedMainIds = Food::query()
                        ->where("{$foodTableName}.restaurant_id", $restaurantId)
                        ->tap($applyFoodConstraints)
                        ->reorder()
                        ->whereNotNull("{$foodTableName}.category_ids")
                        ->selectRaw("DISTINCT {$mainCategoryExpr} AS main_id")
                        ->pluck('main_id')
                        ->filter()
                        ->map(fn ($v) => (int) $v)
                        ->all();

                    $categories = empty($usedMainIds)
                        ? collect()
                        : Category::where('position', 0)
                            ->where('status', 1)
                            ->whereIn('id', $usedMainIds)
                            ->orderBy('name', 'asc')
                            ->get(['id', 'name', 'image']);

                    $categoryIds = $categories->pluck('id')->map(fn ($v) => (int) $v)->all();
                    $categoryKeyExpr = $mainCategoryExpr;
                    $baseFoodScope = function ($q) use ($foodTableName, $restaurantId, $mainCategoryExpr, $categoryIds) {
                        $q->where("{$foodTableName}.restaurant_id", $restaurantId);
                        if (!empty($categoryIds)) {
                            $q->whereRaw($mainCategoryExpr . ' IN (' . implode(',', $categoryIds) . ')');
                        }
                    };
                    $groupBy = function ($food) use ($decode) {
                        $ids = $decode($food->category_ids);

                        return (int) ($ids[0]['id'] ?? 0);
                    };
                    $categorySource = 'main_category';
                }

                if (empty($categoryIds)) {
                    return [
                        'total_size' => 0,
                        'category_source' => $categorySource,
                        'categories' => [],
                        'category_wise_foods' => (object) [],
                    ];
                }

                $foodsCountByCategory = Food::query()
                    ->tap($baseFoodScope)
                    ->tap($applyFoodConstraints)
                    ->reorder()
                    ->selectRaw("{$categoryKeyExpr} AS cat_group, COUNT(*) AS cnt")
                    ->groupBy(DB::raw($categoryKeyExpr))
                    ->pluck('cnt', 'cat_group');

                $categoriesData = $categories
                    ->map(function ($category) use ($foodsCountByCategory) {
                        return [
                            'id' => (int) $category->id,
                            'name' => $category->name,
                            'image_full_url' => $category->image_full_url,
                            'foods_count' => (int) ($foodsCountByCategory[$category->id] ?? 0),
                        ];
                    })
                    ->values();

                $totalFoods = (int) $foodsCountByCategory->sum();

                // reorder() below drops whatever ORDER BY came before it, which included the
                // one applySorting() had just built - so sort_by never reached the response.
                // A requested sort now survives and only falls back to name when none is asked
                // for; id stays the tiebreak either way so paging is stable.
                $sortBy = $additional_data['sort_by'] ?? 'default';
                $hasCustomSort = $sortBy && $sortBy !== 'default';

                $foods = Food::query()
                    ->tap($baseFoodScope)
                    ->tap($applyFoodConstraints)
                    ->with([
                        'newVariations',
                        'newVariationOptions',
                        'restaurant' => fn ($q) => $q->with(['restaurant_config', 'storage']),
                    ])
                    ->when(!$hasCustomSort, fn ($q) => $q->reorder()->orderBy("{$foodTableName}.name", 'asc'))
                    ->addSelect("{$foodTableName}.*")
                    ->orderBy("{$foodTableName}.id", 'asc')
                    ->get();

                $restaurant     = $foods->first()?->restaurant;
                $restaurantName = $restaurant?->name;
                $restaurantSlug = $restaurant?->slug;
                $restaurantLogo = $restaurant?->logo_full_url;
                $restaurantFree = $restaurant?->free_delivery;
                $halalTag       = (int) ($restaurant?->restaurant_config?->halal_tag_status ?? 0);
                $verified       = $restaurant ? Helpers::get_verified_seller_status($restaurant, $restaurant->restaurant_config) : 0;

                // The store wide rates in force, resolved once for the whole listing rather than
                // per food: the happy hour lookup behind this is memoised per restaurant, so
                // this costs one lookup however many foods come back. The two are kept apart -
                // only one can be running - so the client knows which banner to draw.
                $storeWide = $restaurant
                    ? Helpers::store_wide_discount_data($restaurant)
                    : ['restaurant_discount' => 0.0, 'happy_hour_discount' => 0.0];

                $mapFood = function ($food) use (
                    $decode, $restaurant, $storeWide, $restaurantName, $restaurantSlug,
                    $restaurantLogo, $restaurantFree, $halalTag, $verified
                ) {
                    // The raw column is the food's own discount and nothing else, so a card
                    // here ignored the store's standing discount and any running happy hour -
                    // the same food advertised one rate on this screen and another everywhere
                    // that formats through product_data_formatting(). The resolver decides it,
                    // by priority rather than by size: while any store wide rate is running the
                    // item's own reports 0, and the rate in force travels in the two keys below.
                    $discountData = $restaurant
                        ? Helpers::product_discount_calculate_data($food, $restaurant)
                        : null;

                    return [
                        'id'            => (int) $food->id,
                        'name'          => $food->name,
                        'restaurant_id' => (int) $food->restaurant_id,
                        'restaurant_slug' => $restaurantSlug,

                        'image_full_url' => $food->image_full_url,

                        'price'         => (float) $food->price,
                        'discount'      => (float) ($discountData['discount_percentage'] ?? $food->discount),
                        'discount_type' => $discountData['original_discount_type'] ?? $food->discount_type,
                        // Carried by every other listing, so a client does not have to infer
                        // whether the rate above came from the item or from the store.
                        'restaurant_discount' => $storeWide['restaurant_discount'],
                        'happy_hour_discount' => $storeWide['happy_hour_discount'],

                        'variations'    => $decode($food->variations),
                        // 'add_ons'       => $decode($food->add_ons),
                        'veg'           => (int) $food->veg,

                        'is_halal'              => (int) $food->is_halal,
                        'halal_tag_status'      => $halalTag,
                        'avg_rating'            => (float) $food->avg_rating,
                        'rating_count'          => (int) $food->rating_count,
                        'maximum_cart_quantity' => (int) ($food->maximum_cart_quantity ?? 0),

                        'available_time_starts' => $food->available_time_starts,
                        'available_time_ends'   => $food->available_time_ends,
                        'status'                => (int) $food->status,

                        'restaurant_name'          => $restaurantName,
                        'restaurant_logo_full_url' => $restaurantLogo,
                        'restaurant' => [
                            'free_delivery'   => $restaurantFree,
                            'logo_full_url'   => $restaurantLogo,
                            'verified_seller' => $verified,
                        ],
                        'restaurant_details' => [
                            'logo_full_url'   => $restaurantLogo,
                            'verified_seller' => $verified,
                        ],
                    ];
                };

                $allFoods = $foods->groupBy($groupBy);

                $grouped = [];
                foreach ($categoryIds as $catId) {
                    $catFoods = $allFoods[$catId] ?? null;

                    if ($catFoods && $catFoods->isNotEmpty()) {
                        $grouped[(string) $catId] = $catFoods->map($mapFood)->values();
                    }
                }

                return [
                    'total_size' => $totalFoods,
                    'category_source' => $categorySource,
                    'categories' => $categoriesData,
                    'category_wise_foods' => (object) $grouped,
                ];
            });

            return response()->json($payload, 200);
        } catch (\Exception $e) {
            return response()->json([$e->getMessage()], 200);
        }
    }
}
