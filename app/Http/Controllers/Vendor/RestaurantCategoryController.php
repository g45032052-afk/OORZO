<?php

namespace App\Http\Controllers\Vendor;

use App\Exports\RestaurantCategoryExport;
use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Models\RestaurantCategory;
use App\Services\RestaurantCategoryService;
use App\CentralLogics\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RestaurantCategoryController extends Controller
{
    public function __construct(private readonly RestaurantCategoryService $restaurantCategoryService)
    {
        $this->middleware(function ($request, $next) {
            if (!Helpers::restaurantCategoryStatus()) {
                Toastr::warning(translate('Restaurant category feature is disabled'));
                return back();
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $relationships = [
            'translations' => 'value',
        ];

        $priority = $request->query('priority');

        $categories = RestaurantCategory::query()
            ->where('restaurant_id', Helpers::get_restaurant_id())
            ->search(
                keywords: $request->search,
                mainCol: ['name', 'id'],
                relations: $relationships
            )
            ->when($priority !== null && $priority !== '', function ($query) use ($priority) {
                $query->where('priority', $priority);
            })
            ->latest()
            ->paginate(config('default_pagination'))
            ->appends($request->all());

        $language = getWebConfig('language');

        return view('vendor-views.restaurant-category.index', compact('categories', 'language'));
    }

    public function create()
    {
        $language = getWebConfig('language');

        return response()->json([
            'view' => view('vendor-views.restaurant-category.partials._form', [
                'restaurantCategory' => null,
                'language' => $language,
            ])->render(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name.0' => 'required|max:255',
            'image' => 'nullable|max:2048',
            'priority' => 'nullable|integer|in:0,1,2',
        ], [
            'name.0.required' => translate('default_name_is_required'),
            'image' => translate('image_must_be_less_than_2mb'),
        ]);

        $category = $this->restaurantCategoryService->create([
            'restaurant_id' => Helpers::get_restaurant_id(),
            'name' => $request->name,
            'lang' => $request->lang,
            'priority' => $request->priority,
            'image' => $request->file('image'),
        ]);

        Toastr::success(translate('My category added successfully'));

        // Chain into the "Select Foods For Category" off-canvas (image 3 flow)
        return redirect()->route('vendor.restaurant-category.index', ['assign_foods' => $category->id]);
    }

    public function edit($id)
    {
        $language = getWebConfig('language');
        $restaurantCategory = $this->restaurantCategoryService->getCategory(
            id: $id,
            restaurantId: Helpers::get_restaurant_id(),
            withoutGlobalScope: true,
            with: ['translations']
        );

        return response()->json([
            'view' => view('vendor-views.restaurant-category.partials._form', compact('restaurantCategory', 'language'))->render(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name.0' => 'required|max:255',
            'image' => 'nullable|max:2048',
            'priority' => 'nullable|integer|in:0,1,2',
        ], [
            'name.0.required' => translate('default_name_is_required'),
            'image' => translate('image_must_be_less_than_2mb'),
        ]);

        $restaurantCategory = $this->restaurantCategoryService->getCategory($id, Helpers::get_restaurant_id());
        $this->restaurantCategoryService->update($restaurantCategory, [
            'name' => $request->name,
            'lang' => $request->lang,
            'priority' => $request->priority,
            'image' => $request->file('image'),
            'image_remove' => $request->input('image_remove', '0'),
        ]);

        Toastr::success(translate('Restaurant category updated successfully'));
        return back();
    }

    public function status($id, $status)
    {
        $restaurantCategory = $this->restaurantCategoryService->getCategory($id, Helpers::get_restaurant_id());
        $this->restaurantCategoryService->updateStatus($restaurantCategory, $status);

        Toastr::success(translate('Restaurant category status updated'));
        return back();
    }

    public function updatePriority($id, Request $request)
    {
        $restaurantCategory = $this->restaurantCategoryService->getCategory($id, Helpers::get_restaurant_id());
        $this->restaurantCategoryService->updatePriority($restaurantCategory, (int) ($request->priority ?? 0));

        Toastr::success(translate('Restaurant category priority updated successfully'));
        return back();
    }

    public function delete($id)
    {
        $restaurantCategory = $this->restaurantCategoryService->getCategory($id, Helpers::get_restaurant_id());
        $this->restaurantCategoryService->delete($restaurantCategory);

        Toastr::success(translate('Restaurant category removed successfully'));
        return back();
    }

    public function export(Request $request, string $type)
    {
        try {
            $relationships = [
                'translations' => 'value',
            ];

            $priority = $request->query('priority');

            $categories = RestaurantCategory::query()
                ->where('restaurant_id', Helpers::get_restaurant_id())
                ->search(
                    keywords: $request->search,
                    mainCol: ['name', 'id'],
                    relations: $relationships
                )
                ->when($priority !== null && $priority !== '', function ($query) use ($priority) {
                    $query->where('priority', $priority);
                })
                ->orderByDesc('id')
                ->get();

            $data = [
                'data' => $categories,
                'search' => $request->search,
                'priority' => $priority,
                'show_restaurant' => false,
            ];

            if ($type === 'csv') {
                return Excel::download(new RestaurantCategoryExport($data), 'RestaurantCategories.csv');
            }

            return Excel::download(new RestaurantCategoryExport($data), 'RestaurantCategories.xlsx');
        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            return back();
        }
    }

    /**
     * Render the "Select Foods For Category" offcanvas.
     * Lists foods that are either uncategorized OR already assigned to this category,
     * scoped to the logged-in vendor's restaurant.
     */
    public function assignFoodsView($id, Request $request)
    {
        $restaurantId = Helpers::get_restaurant_id();
        $category = $this->restaurantCategoryService->getCategory($id, $restaurantId);

        $foods = $this->queryAssignableFoods($category->id, $request->input('search'))->get();
        $unassignedCount = $this->queryAssignableFoods($category->id)
            ->whereNull('restaurant_category_id')
            ->count();

        return response()->json([
            'view' => view('vendor-views.restaurant-category.partials._assign_foods', compact(
                'category',
                'foods',
                'unassignedCount'
            ))->render(),
        ]);
    }

    /**
     * AJAX search endpoint — returns just the food list partial.
     */
    public function searchAssignableFoods($id, Request $request)
    {
        $restaurantId = Helpers::get_restaurant_id();
        $category = $this->restaurantCategoryService->getCategory($id, $restaurantId);

        $foods = $this->queryAssignableFoods($category->id, $request->input('search'))->get();

        return response()->json([
            'view' => view('vendor-views.restaurant-category.partials._assign_foods_list', [
                'category' => $category,
                'foods' => $foods,
            ])->render(),
        ]);
    }

    /**
     * Persist the food → restaurant_category_id assignment.
     * Foods explicitly checked get this category id; previously-assigned foods not
     * present in the submitted list get cleared back to NULL.
     */
    public function storeAssignedFoods($id, Request $request)
    {
        $request->validate([
            'food_ids' => 'nullable|array',
            'food_ids.*' => 'integer',
        ]);

        $restaurantId = Helpers::get_restaurant_id();
        $category = $this->restaurantCategoryService->getCategory($id, $restaurantId);

        $submittedIds = collect($request->input('food_ids', []))
            ->map(fn ($v) => (int) $v)
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Only allow assigning foods that currently belong to this vendor's
        // restaurant AND are either uncategorized or already in this category.
        // (RestaurantScope is applied automatically for vendor auth, but we add
        // the explicit restaurant_id filter for defense-in-depth.)
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

        // Set restaurant_category_id for newly-checked foods.
        if (!empty($allowedNewIds)) {
            Food::query()
                ->where('restaurant_id', $restaurantId)
                ->whereIn('id', $allowedNewIds)
                ->update(['restaurant_category_id' => $category->id]);
        }

        // Un-assign foods that were previously in this category but were unchecked.
        Food::query()
            ->where('restaurant_id', $restaurantId)
            ->where('restaurant_category_id', $category->id)
            ->when(!empty($allowedNewIds), fn ($q) => $q->whereNotIn('id', $allowedNewIds))
            ->update(['restaurant_category_id' => null]);

        return response()->json([
            'success' => true,
            'message' => translate('Foods assigned successfully'),
            'assigned_count' => count($allowedNewIds),
        ]);
    }

    /**
     * Reusable builder for the food list shown in the assign offcanvas.
     */
    private function queryAssignableFoods(int $categoryId, ?string $search = null)
    {
        $restaurantId = Helpers::get_restaurant_id();

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
}
