<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Services\RestaurantCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RestaurantCategoryController extends Controller
{
    public function __construct(private readonly RestaurantCategoryService $restaurantCategoryService)
    {
        $this->middleware(function ($request, $next) {
            if (!Helpers::restaurantCategoryStatus()) {
                return response()->json([
                    'errors' => [
                        ['code' => 'restaurant_category', 'message' => translate('Restaurant category feature is disabled')],
                    ],
                ], 403);
            }

            return $next($request);
        });
    }

    public function list(Request $request)
    {
        $restaurantId = $request['vendor']?->restaurants[0]?->id;

        if (!$restaurantId) {
            return response()->json([], 200);
        }

        $categories = $this->restaurantCategoryService
            ->getCategoryQueryForRestaurant($restaurantId)
            ->select(['id', 'name', 'slug', 'priority', 'status', 'image'])
            ->withCount('foods')
            ->search($request->input('search'))
            ->when(!$request->filled('name'), fn ($q) => $q->latest())
            ->get()
            ->map(fn ($cat) => [
                'id'             => $cat->id,
                'name'           => $cat->name,
                'slug'           => $cat->slug,
                'priority'       => $cat->priority,
                'status'         => $cat->status,
                'image_full_url' => $cat->image_full_url,
                'products_count'    => (int) $cat->foods_count,
            ])  ;

        return response()->json($categories, 200);
    }

    public function details($id, Request $request)
    {
        $restaurantCategory = $this->restaurantCategoryService->getCategory(
            id: $id,
            restaurantId: $request['vendor']?->restaurants[0]?->id,
            withoutGlobalScope: true,
            with: ['translations', 'storage']
        );
        return response()->json($restaurantCategory, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'priority' => 'nullable|integer|in:0,1,2',
            'image' => 'nullable|max:2048',
        ]);

        $data = $request->translations ?? [];
        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }
        if (count($data) < 1) {
            $validator->getMessageBag()->add('translations', translate('messages.Name in english is required'));
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);

        }

        if ($validator->fails() || count($data) < 1) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $this->restaurantCategoryService->create([
            'restaurant_id' => $request['vendor']?->restaurants[0]?->id,
            'priority' => $request->priority,
            'image' => $request->file('image'),
            'translations' => $data,
        ]);

        return response()->json(['message' => translate('Restaurant category added successfully')], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'priority' => 'nullable|integer|in:0,1,2',
            'image' => 'nullable|max:2048',
        ]);

        $data = $request->translations ?? [];
        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }
        if (count($data) < 1) {
            $validator->getMessageBag()->add('translations', translate('messages.Name in english is required'));
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);

        }

        if ($validator->fails() || count($data) < 1) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $restaurantCategory = $this->restaurantCategoryService->getCategory(
            id: $request->id,
            restaurantId: $request['vendor']?->restaurants[0]?->id
        );
        $this->restaurantCategoryService->update($restaurantCategory, [
            'priority' => $request->priority,
            'image' => $request->file('image'),
            'translations' => $data,
            'image_remove' => $request->input('image_remove', '0'),
        ]);

        return response()->json(['message' => translate('Restaurant category updated successfully')], 200);
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $restaurantCategory = $this->restaurantCategoryService->getCategory(
            id: $request->id,
            restaurantId: $request['vendor']?->restaurants[0]?->id,
            withoutGlobalScope: true,
            with: ['translations', 'storage']
        );
        $this->restaurantCategoryService->delete($restaurantCategory);

        return response()->json(['message' => translate('Restaurant category deleted successfully')], 200);
    }

    public function status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $restaurantCategory = $this->restaurantCategoryService->getCategory(
            id: $request->id,
            restaurantId: $request['vendor']?->restaurants[0]?->id
        );
        $this->restaurantCategoryService->updateStatus($restaurantCategory, $request->status);

        return response()->json(['message' => translate('Restaurant category status updated')], 200);
    }

    public function priority(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'priority' => 'required|integer|in:0,1,2',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $restaurantCategory = $this->restaurantCategoryService->getCategory(
            id: $request->id,
            restaurantId: $request['vendor']?->restaurants[0]?->id
        );
        $this->restaurantCategoryService->updatePriority($restaurantCategory, (int) $request->priority);

        return response()->json(['message' => translate('Restaurant category priority updated successfully')], 200);
    }

    /**
     * GET /vendor/restaurant-categories/assignable-foods/{id}
     * Returns foods that are either uncategorized OR already assigned to this category,
     * scoped to the logged-in vendor's restaurant. Used by the "Select Foods For Category"
     * off-canvas in the mobile vendor app.
     *
     * Query params (all optional):
     *   - search: filter by name or id
     *   - limit:  page size (default 25)
     *   - offset: page number (default 1)
     */
    public function assignableFoods($id, Request $request)
    {
        $restaurantId = $request['vendor']?->restaurants[0]?->id;
        if (!$restaurantId) {
            return response()->json(['errors' => [['code' => 'no-restaurant', 'message' => translate('No restaurant found for this vendor')]]], 403);
        }

        $category = $this->restaurantCategoryService->getCategory(
            id: $id,
            restaurantId: $restaurantId
        );

        $limit  = (int) $request->query('limit', 25);
        $offset = (int) $request->query('offset', 1);
        if ($limit < 1)  { $limit = 25; }
        if ($offset < 1) { $offset = 1; }

        $query = $this->queryAssignableFoods($restaurantId, (int) $category->id, $request->query('search'));

        $paginator = $query->paginate($limit, ['*'], 'page', $offset);

        // Count of strictly-unassigned foods for the "There are X foods unassigned..." banner.
        $unassignedCount = Food::query()
            ->where('restaurant_id', $restaurantId)
            ->whereNull('restaurant_category_id')
            ->count();

        $foods = collect($paginator->items())->map(function ($food) use ($category) {
            return [
                'id'                      => (int) $food->id,
                'name'                    => $food->name,
                'image_full_url'          => $food->image_full_url,
                'price'                   => $food->price,
                'restaurant_category_id'  => $food->restaurant_category_id ? (int) $food->restaurant_category_id : null,
                'is_assigned'             => ((int) $food->restaurant_category_id === (int) $category->id),
                'variations_count'        => (int) ($food->new_variations_count ?? 0),
            ];
        });

        return response()->json([
            'category' => [
                'id'   => (int) $category->id,
                'name' => $category->name,
            ],
            'unassigned_count' => $unassignedCount,
            'total_size'       => $paginator->total(),
            'limit'            => $limit,
            'offset'           => $offset,
            'foods'            => $foods,
        ], 200);
    }

    /**
     * POST /vendor/restaurant-categories/assign-foods
     * Body: { category_id: int, food_ids: int[] }
     * Sets restaurant_category_id = category_id on submitted food ids (scoped to vendor's
     * restaurant). Foods that were previously in this category but not in the new list
     * are reverted to NULL.
     */
    public function assignFoods(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer',
            'food_ids'    => 'nullable|array',
            'food_ids.*'  => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $restaurantId = $request['vendor']?->restaurants[0]?->id;
        if (!$restaurantId) {
            return response()->json(['errors' => [['code' => 'no-restaurant', 'message' => translate('No restaurant found for this vendor')]]], 403);
        }

        $category = $this->restaurantCategoryService->getCategory(
            id: $request->category_id,
            restaurantId: $restaurantId
        );

        $submittedIds = collect($request->input('food_ids', []))
            ->map(fn ($v) => (int) $v)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $allowedNewIds = [];
        if (!empty($submittedIds)) {
            $allowedNewIds = Food::query()
                ->where('restaurant_id', $restaurantId)
                ->whereIn('id', $submittedIds)
                ->where(function ($q) use ($category) {
                    $q->whereNull('restaurant_category_id')
                      ->orWhere('restaurant_category_id', $category->id);
                })
                ->pluck('id')
                ->all();
        }

        if (!empty($allowedNewIds)) {
            Food::query()
                ->where('restaurant_id', $restaurantId)
                ->whereIn('id', $allowedNewIds)
                ->update(['restaurant_category_id' => $category->id]);
        }

        // Un-assign foods previously in this category but unchecked.
        Food::query()
            ->where('restaurant_id', $restaurantId)
            ->where('restaurant_category_id', $category->id)
            ->when(!empty($allowedNewIds), fn ($q) => $q->whereNotIn('id', $allowedNewIds))
            ->update(['restaurant_category_id' => null]);

        return response()->json([
            'message'        => translate('Foods assigned successfully'),
            'assigned_count' => count($allowedNewIds),
        ], 200);
    }

    /**
     * Reusable builder for assignable foods, shared by the GET endpoint.
     */
    private function queryAssignableFoods(int $restaurantId, int $categoryId, ?string $search = null)
    {
        return Food::query()
            ->where('restaurant_id', $restaurantId)
            ->withCount('newVariations')
            ->where(function ($q) use ($categoryId) {
                $q->whereNull('restaurant_category_id')
                  ->orWhere('restaurant_category_id', $categoryId);
            })
            ->when($search, function ($q) use ($search) {
                $term = '%' . trim($search) . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)
                          ->orWhere('id', 'like', $term);
                });
            })
            ->orderByDesc('id');
    }

    public function getProducts(Request $request, $id)
    {
        if (!Helpers::restaurantCategoryStatus()) {
            return response()->json([] , 200);
        }

        $limit = $request->limit ?? 25;
        $offset = $request->offset ?? 1;

        $paginator = Food::where('restaurant_category_id', $id)
            ->where('restaurant_id', $request['vendor']?->restaurants[0]?->id)
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => Helpers::product_data_formatting($paginator->items(), true, false, app()->getLocale()),
        ], 200);
    }

}
