<?php

namespace App\Http\Controllers\Admin;


use App\CentralLogics\Helpers;
use App\Exports\RestaurantCategoryExport;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantCategory;
use App\Services\RestaurantCategoryService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RestaurantCategoryController extends Controller
{
    public function __construct(private readonly RestaurantCategoryService $restaurantCategoryService)
    {
        // Admin can always manage restaurant categories. The
        // restaurant_category_status setting only governs vendor-side management,
        // so it must not block admin access here.
    }

    public function index(Request $request)
    {
        $relationships = [
            'translations' => 'value',
            'restaurant.translations' => 'value',
        ];

        $priority = $request->query('priority');
        $restaurantId = $request->query('restaurant_id');

        $categories = RestaurantCategory::query()
            ->with('restaurant')
            ->search(
                keywords: $request->search,
                mainCol: ['name', 'id'],
                relations: $relationships
            )
            ->when($priority !== null && $priority !== '', function ($query) use ($priority) {
                $query->where('priority', $priority);
            })
            ->when($restaurantId !== null && $restaurantId !== '', function ($query) use ($restaurantId) {
                $query->where('restaurant_id', $restaurantId);
            })
            ->latest()
            ->paginate(config('default_pagination'))
            ->appends($request->all());

        $restaurants = Restaurant::orderBy('name')->get(['id', 'name']);

        return view('admin-views.restaurant-category.index', compact('categories', 'restaurants'));
    }

    public function create()
    {
        $language = getWebConfig('language');
        $restaurants = Restaurant::orderBy('name')->get(['id', 'name']);

        return response()->json([
            'view' => view('admin-views.restaurant-category.partials._form', [
                'restaurantCategory' => null,
                'language' => $language,
                'restaurants' => $restaurants,
            ])->render(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name.0' => 'required|max:255',
            'image' => 'nullable|max:2048',
            'priority' => 'nullable|integer|in:0,1,2',
        ], [
            'restaurant_id.required' => translate('messages.Restaurant_is_required'),
            'name.0.required' => translate('default_name_is_required'),
            'image' => translate('image_must_be_less_than_2mb'),
        ]);

        $this->restaurantCategoryService->create([
            'restaurant_id' => (int) $request->restaurant_id,
            'name' => $request->name,
            'lang' => $request->lang,
            'priority' => $request->priority,
            'image' => $request->file('image'),
        ]);

        Toastr::success(translate('Restaurant category added successfully'));
        return back();
    }

    public function edit($id)
    {
        $language = getWebConfig('language');
        $restaurants = Restaurant::orderBy('name')->get(['id', 'name']);
        $restaurantCategory = $this->restaurantCategoryService->getCategory(
            id: $id,
            withoutGlobalScope: true,
            with: ['translations', 'restaurant']
        );

        return response()->json([
            'view' => view('admin-views.restaurant-category.partials._form', compact('restaurantCategory', 'language', 'restaurants'))->render(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name.0' => 'required|max:255',
            'image' => 'nullable|max:2048',
            'priority' => 'nullable|integer|in:0,1,2',
        ], [
            'restaurant_id.required' => translate('messages.Restaurant_is_required'),
            'name.0.required' => translate('default_name_is_required'),
            'image' => translate('image_must_be_less_than_2mb'),
        ]);

        $restaurantCategory = $this->restaurantCategoryService->getCategory($id);
        $this->restaurantCategoryService->update($restaurantCategory, [
            'restaurant_id' => (int) $request->restaurant_id,
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
        $restaurantCategory = $this->restaurantCategoryService->getCategory($id);
        $this->restaurantCategoryService->updateStatus($restaurantCategory, $status);

        Toastr::success(translate('Restaurant category status updated'));
        return back();
    }

    public function updatePriority(RestaurantCategory $restaurantCategory, Request $request)
    {
        $this->restaurantCategoryService->updatePriority($restaurantCategory, (int) ($request->priority ?? 0));

        Toastr::success(translate('Restaurant category priority updated successfully'));
        return back();
    }

    public function delete($id)
    {
        $restaurantCategory = $this->restaurantCategoryService->getCategory($id);
        $this->restaurantCategoryService->delete($restaurantCategory);

        Toastr::success(translate('Restaurant category removed successfully'));
        return back();
    }

    public function export(Request $request, string $type)
    {
        try {
            $relationships = [
                'translations' => 'value',
                'restaurant.translations' => 'value',
            ];

            $priority = $request->query('priority');
            $restaurantId = $request->query('restaurant_id');

            $categories = RestaurantCategory::query()
                ->with('restaurant')
                ->search(
                    keywords: $request->search,
                    mainCol: ['name', 'id'],
                    relations: $relationships
                )
                ->when($priority !== null && $priority !== '', function ($query) use ($priority) {
                    $query->where('priority', $priority);
                })
                ->when($restaurantId !== null && $restaurantId !== '', function ($query) use ($restaurantId) {
                    $query->where('restaurant_id', $restaurantId);
                })
                ->orderByDesc('id')
                ->get();

            $data = [
                'data' => $categories,
                'search' => $request->search,
                'priority' => $priority,
                'restaurant' => $restaurantId ? Restaurant::find($restaurantId)?->name : null,
                'show_restaurant' => true,
            ];

            if ($type === 'csv') {
                return Excel::download(new RestaurantCategoryExport($data), 'RestaurantCategories.csv');
            }

            return Excel::download(new RestaurantCategoryExport($data), 'RestaurantCategories.xlsx');
        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);
            return back();
        }
    }
}
