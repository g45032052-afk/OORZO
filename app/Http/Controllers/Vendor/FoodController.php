<?php

namespace App\Http\Controllers\Vendor;

use App\CentralLogics\Helpers;
use App\CentralLogics\ProductLogic;
use App\Exports\FoodListExport;
use App\Http\Controllers\Controller;
use App\Models\Allergy;
use App\Models\Category;
use App\Models\Food;
use App\Models\FoodSeoData;
use App\Models\Nutrition;
use App\Models\RestaurantCategory;
use App\Models\Review;
use App\Models\Tag;
use App\Models\Variation;
use App\Models\VariationOption;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Validation\Rule;

class FoodController extends Controller
{
    public function index()
    {
        if (! Helpers::get_restaurant_data()->food_section) {
            Toastr::warning(translate('messages.permission_denied'));

            return back();
        }
        $categories = Category::where(['position' => 0])->get();
        $taxData = Helpers::getTaxSystemType();
        $productWiseTax = $taxData['productWiseTax'];
        $taxVats = $taxData['taxVats'];

        return view('vendor-views.product.index', compact('categories', 'productWiseTax', 'taxVats'));
    }

    public function store(Request $request)
    {
        if (! Helpers::get_restaurant_data()->food_section) {
            return response()->json([
                'errors' => [
                    ['code' => 'unauthorized', 'message' => translate('messages.permission_denied')],
                ],
            ]);
        }

        $request->merge([
            'restaurant_category_id' => Helpers::restaurantCategoryStatus()
                ? Helpers::normalizeRestaurantCategoryId($request->input('restaurant_category_id'))
                : null,
        ]);

        // Conditional required: restaurant_category_id is required when the
        // vendor has at least one of their own categories. If the restaurant
        // has no My Categories yet, the field stays optional.
        $restaurantCategoryRule = Helpers::hasAnyRestaurantCategory(Helpers::get_restaurant_id())
            ? 'required'
            : 'nullable';

        $validator = Validator::make($request->all(), [
            'name' => 'array',
            'name.0' => 'required',
            'name.*' => 'max:191',
            'category_id' => 'required',
            'restaurant_category_id' => [
                $restaurantCategoryRule,
                Rule::exists('restaurant_categories', 'id')->where(function ($query) {
                    return $query->where('restaurant_id', Helpers::get_restaurant_id());
                }),
            ],
            'image' => 'nullable|max:2048',
            'price' => 'required|numeric|between:.01,999999999999.99',
            'description.*' => 'max:65535',
            'discount' => 'required|numeric|min:0',
        ], [
            'name.0.required' => translate('messages.item_name_required'),
            'category_id.required' => translate('messages.category_required'),
            'restaurant_category_id.required' => translate('messages.restaurant_category_required'),
            'veg.required' => translate('messages.item_type_is_required'),
            'description.*.max' => translate('messages.Description must be in 65535 char limit'),
        ]);

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
        }

        if ($request['price'] <= $dis || $validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $tag_ids = [];
        if ($request->tags != null) {
            $tags = explode(',', $request->tags);
        }
        if (isset($tags)) {
            foreach ($tags as $key => $value) {
                $tag = Tag::firstOrNew(
                    ['tag' => $value]
                );
                $tag->save();
                array_push($tag_ids, $tag->id);
            }
        }

        $nutrition_ids = [];
        if ($request->nutritions != null) {
            $nutritions = $request->nutritions;
        }
        if (isset($nutritions)) {
            foreach ($nutritions as $key => $value) {
                $nutrition = Nutrition::firstOrNew(
                    ['nutrition' => $value]
                );
                $nutrition->save();
                array_push($nutrition_ids, $nutrition->id);
            }
        }
        $allergy_ids = [];
        if ($request->allergies != null) {
            $allergies = $request->allergies;
        }
        if (isset($allergies)) {
            foreach ($allergies as $key => $value) {
                $allergy = Allergy::firstOrNew(
                    ['allergy' => $value]
                );
                $allergy->save();
                array_push($allergy_ids, $allergy->id);
            }
        }

        $food = new Food;
        $food->name = $request->name[array_search('default', $request->lang)];

        $category = [];
        if ($request->category_id != null) {
            array_push($category, [
                'id' => $request->category_id,
                'position' => 1,
            ]);
        }
        if ($request->sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_category_id,
                'position' => 2,
            ]);
        }
        if ($request->sub_sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ]);
        }
        $food->category_id = $request->sub_category_id ?? $request->category_id;
        $food->category_ids = json_encode($category);
        $food->description = $request->description[array_search('default', $request->lang)];

        $food->choice_options = json_encode([]);

        $food->variations = json_encode([]);
        $food->price = $request->price;
        $food->veg = $request->veg;
        $food->image = Helpers::upload(dir: 'product/', format: 'png', image: $request->file('image'));
        $food->available_time_starts = $request->available_time_starts;
        $food->available_time_ends = $request->available_time_ends;
        $food->discount = $request->discount ?? 0;
        $food->discount_type = $request->discount_type;
        $food->attributes = $request->has('attribute_id') ? json_encode($request->attribute_id) : json_encode([]);
        $food->add_ons = $request->has('addon_ids') ? json_encode($request->addon_ids) : json_encode([]);
        $food->restaurant_id = Helpers::get_restaurant_id();
        $food->restaurant_category_id = $request->restaurant_category_id;
        $food->maximum_cart_quantity = $request->maximum_cart_quantity;
        $food->is_halal = $request->is_halal ?? 0;
        $food->item_stock = $request?->item_stock ?? 0;
        $food->stock_type = $request->stock_type;

        $restaurant = Helpers::get_restaurant_data();
        if ($restaurant->restaurant_model == 'subscription') {
            $rest_sub = $restaurant?->restaurant_sub;
            if (isset($rest_sub)) {
                if ($rest_sub->max_product != 'unlimited' && $rest_sub->max_product > 0) {
                    $total_food = Food::where('restaurant_id', $restaurant->id)->count() + 1;
                    if ($total_food >= $rest_sub->max_product) {
                        $restaurant->food_section = 0;
                        $restaurant->save();
                    }
                }
            } else {
                return response()->json([
                    'errors' => [
                        ['code' => 'unauthorized', 'message' => translate('messages.you_are_not_subscribed_to_any_package')],
                    ],
                ]);
            }
        } elseif ($restaurant->restaurant_model == 'unsubscribed') {
            return response()->json([
                'errors' => [
                    ['code' => 'unauthorized', 'message' => translate('messages.you_are_not_subscribed_to_any_package')],
                ],
            ]);
        }

        if (isset($request->options)) {
            foreach (array_values($request->options) as $key => $option) {
                if ($option['min'] > 0 && $option['min'] > $option['max']) {
                    $validator->getMessageBag()->add('name', translate('messages.minimum_value_can_not_be_greater_then_maximum_value'));

                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if (! isset($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('messages.please_add_options_for').$option['name']);

                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if ($option['max'] > count($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('messages.please_add_more_options_or_change_the_max_value_for').$option['name']);

                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
            }

            $food->save();

            foreach (array_values($request->options) as $key => $option) {
                $variation = new Variation;
                $variation->food_id = $food->id;
                $variation->name = $option['name'];
                $variation->type = $option['type'];
                $variation->min = $option['min'] ?? 0;
                $variation->max = $option['max'] ?? 0;
                $variation->is_required = data_get($option, 'required') == 'on' ? true : false;
                $variation->save();

                foreach (array_values($option['values']) as $value) {
                    $VariationOption = new VariationOption;
                    $VariationOption->food_id = $food->id;
                    $VariationOption->variation_id = $variation->id;
                    $VariationOption->option_name = $value['label'];
                    $VariationOption->option_price = $value['optionPrice'];
                    $VariationOption->stock_type = $request?->stock_type ?? 'unlimited';
                    $VariationOption->total_stock = data_get($value, 'total_stock') == null || $VariationOption->stock_type == 'unlimited' ? 0 : data_get($value, 'total_stock');
                    $VariationOption->save();
                }
            }
        } else {
            $food->save();
        }
        $food->tags()->sync($tag_ids);
        $food->nutritions()->sync($nutrition_ids);
        $food->allergies()->sync($allergy_ids);

        //        FoodSeoData::create(
        //            Helpers::getFoodSEOData($request, $food->id, null)
        //        );

        Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'Food', data_id: $food->id, data_value: $food->name);
        Helpers::add_or_update_translations(request: $request, key_data: 'description', name_field: 'description', model_name: 'Food', data_id: $food->id, data_value: $food->description);
        if (addon_published_status('TaxModule')) {
            $SystemTaxVat = \Modules\TaxModule\Entities\SystemTaxSetup::where('is_active', 1)->where('is_default', 1)->first();
            if ($SystemTaxVat?->tax_type == 'product_wise') {
                foreach ($request['tax_ids'] ?? [] as $tax_id) {
                    \Modules\TaxModule\Entities\Taxable::create(
                        [
                            'taxable_type' => Food::class,
                            'taxable_id' => $food->id,
                            'system_tax_setup_id' => $SystemTaxVat->id,
                            'tax_id' => $tax_id,
                        ],
                    );
                }
            }
        }

        return response()->json([], 200);
    }

    public function view($id)
    {
        $taxData = Helpers::getTaxSystemType();
        $productWiseTax = $taxData['productWiseTax'];
        $product = Food::with($productWiseTax ? ['taxVats.tax', 'newVariationOptions.variation'] : ['newVariationOptions.variation'])->findOrFail($id);
        $reviews = Review::where(['food_id' => $id])->with('customer')->latest()->paginate(config('default_pagination'));

        return view('vendor-views.product.view', compact('product', 'reviews', 'productWiseTax'));
    }

    public function edit($id)
    {
        if (! Helpers::get_restaurant_data()->food_section) {
            Toastr::warning(translate('messages.permission_denied'));

            return back();
        }

        $product = Food::withoutGlobalScope('translate')->with('foodSeoData', 'newVariations', 'newVariationOptions')->findOrFail($id);
        // json_decode() answers null for a food whose column is empty or not valid json, and
        // the edit views index into this list and count() it - which is a 500, not a missing
        // selection. Normalised here so the views can read it as the list they expect.
        $product_category = json_decode($product->category_ids) ?: [];

        // What the add-on picker marks as already chosen. Same reason: in_array() against a
        // null decode is fatal.
        $selected_addons = json_decode($product->add_ons ?? '[]', true) ?: [];
        $categories = Category::where(['parent_id' => 0])->get();
        $taxData = Helpers::getTaxSystemType();
        $productWiseTax = $taxData['productWiseTax'];
        $taxVats = $taxData['taxVats'];
        $taxVatIds = $productWiseTax ? $product->taxVats()->pluck('tax_id')->toArray() : [];

        return view('vendor-views.product.edit', compact('product', 'product_category', 'selected_addons', 'categories', 'productWiseTax', 'taxVats', 'taxVatIds'));
    }

    public function status(Request $request)
    {
        if (! Helpers::get_restaurant_data()->food_section) {
            Toastr::warning(translate('messages.permission_denied'));

            return back();
        }
        $product = Food::find($request->id);
        $product->status = $request->status;
        $product->save();
        if ($request->status != 1) {
            $product?->carts()?->delete();
        }
        Toastr::success(translate('Food status updated!'));

        return back();
    }

    public function recommended(Request $request)
    {
        if (! Helpers::get_restaurant_data()->food_section) {
            Toastr::warning(translate('messages.permission_denied'));

            return back();
        }
        $product = Food::find($request->id);
        $product->recommended = $request->status;
        $product->save();
        Toastr::success(translate('Food recommendation updated!'));

        return back();
    }

    public function update(Request $request, $id)
    {
        if (! Helpers::get_restaurant_data()->food_section) {
            return response()->json([
                'errors' => [
                    ['code' => 'unauthorized', 'message' => translate('messages.permission_denied')],
                ],
            ]);
        }

        $request->merge([
            'restaurant_category_id' => Helpers::restaurantCategoryStatus()
                ? Helpers::normalizeRestaurantCategoryId($request->input('restaurant_category_id'))
                : null,
        ]);

        // Conditional required: required when the vendor has at least one
        // of their own categories; optional otherwise.
        $restaurantCategoryRule = Helpers::hasAnyRestaurantCategory(Helpers::get_restaurant_id())
            ? 'required'
            : 'nullable';

        $validator = Validator::make($request->all(), [
            'name' => 'array',
            'name.0' => 'required',
            'name.*' => 'max:191',
            'category_id' => 'required',
            'restaurant_category_id' => [
                $restaurantCategoryRule,
                Rule::exists('restaurant_categories', 'id')->where(function ($query) {
                    return $query->where('restaurant_id', Helpers::get_restaurant_id());
                }),
            ],
            'price' => 'required|numeric|between:0.01,999999999999.99',
            'description.*' => 'max:65535',
            'discount' => 'required|numeric|min:0',
            'image' => 'nullable|max:2048',
        ], [
            'name.0.required' => translate('messages.item_name_required'),
            'category_id.required' => translate('messages.category_required'),
            'restaurant_category_id.required' => translate('messages.restaurant_category_required'),
            'veg.required' => translate('messages.item_type_is_required'),
            'description.*.max' => translate('messages.Description must be in 65535 char limit'),
        ]);


        // A repeat (subscription) order is delivered on its own schedule, so shrinking the
        // available time out from under one would silently break the recurring delivery.
        $repeat_order_times = Helpers::repeat_order_times_lost_by_availability(
            Food::find($id),
            $request->available_time_starts,
            $request->available_time_ends
        );

        if ($repeat_order_times) {
            $validator->getMessageBag()->add('available_time_starts', translate('messages.food_available_time_must_cover_repeat_order', [
                'times' => implode(', ', array_map(fn ($time) => Helpers::time_format($time), $repeat_order_times)),
            ]));
        }

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
        }

        if ($request['price'] <= $dis || $validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $tag_ids = [];
        if ($request->tags != null) {
            $tags = explode(',', $request->tags);
        }
        if (isset($tags)) {
            foreach ($tags as $key => $value) {
                $tag = Tag::firstOrNew(
                    ['tag' => $value]
                );
                $tag->save();
                array_push($tag_ids, $tag->id);
            }
        }

        $nutrition_ids = [];
        if ($request->nutritions != null) {
            $nutritions = $request->nutritions;
        }
        if (isset($nutritions)) {
            foreach ($nutritions as $key => $value) {
                $nutrition = Nutrition::firstOrNew(
                    ['nutrition' => $value]
                );
                $nutrition->save();
                array_push($nutrition_ids, $nutrition->id);
            }
        }
        $allergy_ids = [];
        if ($request->allergies != null) {
            $allergies = $request->allergies;
        }
        if (isset($allergies)) {
            foreach ($allergies as $key => $value) {
                $allergy = Allergy::firstOrNew(
                    ['allergy' => $value]
                );
                $allergy->save();
                array_push($allergy_ids, $allergy->id);
            }
        }

        $p = Food::with('foodSeoData')->find($id);

        $p->name = $request->name[array_search('default', $request->lang)];

        $slug = Str::slug($request->name[array_search('default', $request->lang)]);
        $p->slug = $p->slug ? $p->slug : "{$slug}-{$p->id}";

        $category = [];
        if ($request->category_id != null) {
            array_push($category, [
                'id' => $request->category_id,
                'position' => 1,
            ]);
        }
        if ($request->sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_category_id,
                'position' => 2,
            ]);
        }
        if ($request->sub_sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ]);
        }

        $p->category_id = $request->sub_category_id ? $request->sub_category_id : $request->category_id;
        $p->category_ids = json_encode($category);
        $p->description = $request->description[array_search('default', $request->lang)];
        $p->choice_options = json_encode([]);
        $p->variations = json_encode([]);
        if ($request->remove_all_old_variations == 1) {
            $p->newVariations()->delete();
            $p->newVariationOptions()->delete();
        }
        if (isset($request->options)) {
            foreach (array_values($request->options) as $key => $option) {
                if ($option['min'] > 0 && $option['min'] > $option['max']) {
                    $validator->getMessageBag()->add('name', translate('messages.minimum_value_can_not_be_greater_then_maximum_value'));

                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if (! isset($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('messages.please_add_options_for').$option['name']);

                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if ($option['max'] > count($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('messages.please_add_more_options_or_change_the_max_value_for').$option['name']);

                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }

                $variation = Variation::updateOrCreate([
                    'id' => $option['variation_id'] ?? null,
                    'food_id' => $p->id,
                ], [
                    'name' => $option['name'],
                    'type' => $option['type'],
                    'min' => $option['min'] ?? 0,
                    'max' => $option['max'] ?? 0,
                    'is_required' => data_get($option, 'required') == 'on' ? true : false,
                ]);

                foreach (array_values($option['values']) as $value) {
                    VariationOption::updateOrCreate([
                        'id' => $value['option_id'] ?? null,
                        'food_id' => $p->id,
                        'variation_id' => $variation->id,
                    ], [
                        'option_name' => $value['label'],
                        'option_price' => $value['optionPrice'],
                        'total_stock' => data_get($value, 'total_stock') == null || $request?->stock_type == 'unlimited' ? 0 : data_get($value, 'total_stock'),
                        'stock_type' => $request?->stock_type ?? 'unlimited',
                        'sell_count' => 0,
                    ]);
                }
            }

        }
        if ($request?->removedVariationOptionIDs && is_string($request?->removedVariationOptionIDs)) {
            VariationOption::whereIn('id', explode(',', $request->removedVariationOptionIDs))->delete();
        }
        if ($request?->removedVariationIDs && is_string($request?->removedVariationIDs)) {
            VariationOption::whereIn('variation_id', explode(',', $request->removedVariationIDs))->delete();
            Variation::whereIn('id', explode(',', $request->removedVariationIDs))->delete();
        }

        $p->item_stock = $request?->item_stock ?? 0;
        $p->stock_type = $request->stock_type;

        $p->price = $request->price;
        $p->veg = $request->veg;
        $p->image = $request->has('image') ? Helpers::update(dir: 'product/', old_image: $p->image, format: 'png', image: $request->file('image')) : $p->image;
        $p->available_time_starts = $request->available_time_starts;
        $p->available_time_ends = $request->available_time_ends;
        $p->discount = $request->discount ?? 0;
        $p->discount_type = $request->discount_type;
        $p->attributes = $request->has('attribute_id') ? json_encode($request->attribute_id) : json_encode([]);
        $p->add_ons = $request->has('addon_ids') ? json_encode($request->addon_ids) : json_encode([]);
        $p->restaurant_category_id = $request->restaurant_category_id;
        $p->maximum_cart_quantity = $request->maximum_cart_quantity;
        $p->is_halal = $request->is_halal ?? 0;
        $p->sell_count = 0;

        $p->save();
        $p->tags()->sync($tag_ids);
        $p->nutritions()->sync($nutrition_ids);
        $p->allergies()->sync($allergy_ids);
        if (addon_published_status('TaxModule')) {
            $taxVatIds = $p->taxVats()->pluck('tax_id')->toArray() ?? [];
            $newTaxVatIds = array_map('intval', $request['tax_ids'] ?? []);
            sort($newTaxVatIds);
            sort($taxVatIds);
            if ($newTaxVatIds != $taxVatIds) {
                $p->taxVats()->delete();
                $SystemTaxVat = \Modules\TaxModule\Entities\SystemTaxSetup::where('is_active', 1)->where('is_default', 1)->first();
                if ($SystemTaxVat?->tax_type == 'product_wise') {
                    foreach ($request['tax_ids'] ?? [] as $tax_id) {
                        \Modules\TaxModule\Entities\Taxable::create(
                            [
                                'taxable_type' => Food::class,
                                'taxable_id' => $p->id,
                                'system_tax_setup_id' => $SystemTaxVat->id,
                                'tax_id' => $tax_id,
                            ],
                        );
                    }
                }
            }
        }

        //        FoodSeoData::updateOrCreate(
        //            ['food_id' => $p->id],
        //            Helpers::getFoodSEOData($request, $p->id, $p->foodSeoData)
        //        );

        Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'Food', data_id: $p->id, data_value: $p->name);
        Helpers::add_or_update_translations(request: $request, key_data: 'description', name_field: 'description', model_name: 'Food', data_id: $p->id, data_value: $p->description);

        return response()->json([], 200);
    }

    public function get_restaurant_categories(Request $request)
    {
        if (!Helpers::restaurantCategoryStatus()) {
            return response()->json([
                'options' => view('admin-views.product.partials._restaurant_category_options', [
                    'categories' => collect(),
                    'selectedId' => null,
                ])->render(),
                'has_categories' => false,
            ]);
        }

        $selectedId = $request->query('selected_id');
        $restaurantId = Helpers::get_restaurant_id();

        $categories = RestaurantCategory::query()
            ->where('restaurant_id', $restaurantId)
            ->where(function ($query) use ($selectedId) {
                $query->where('status', 1);
                if ($selectedId) {
                    $query->orWhere('id', $selectedId);
                }
            })
            ->orderBy('priority')
            ->latest()
            ->get(['id', 'name', 'status', 'priority']);

        return response()->json([
            'options' => view('admin-views.product.partials._restaurant_category_options', compact('categories', 'selectedId'))->render(),
            // Drives the "Restaurant Category *" asterisk + required attribute
            // on the form. Independent of `status` — any row counts.
            'has_categories' => Helpers::hasAnyRestaurantCategory($restaurantId),
        ]);
    }

    public function delete(Request $request)
    {
        if (! Helpers::get_restaurant_data()->food_section) {
            Toastr::warning(translate('messages.permission_denied'));

            return back();
        }
        $product = Food::find($request->id);

        if ($product->image) {
            Helpers::check_and_delete('product/', $product['image']);
        }
        $product?->carts()?->delete();
        $product?->newVariationOptions()?->delete();
        $product?->newVariations()?->delete();
        $product?->translations()?->delete();
        $product?->taxVats()->delete();
        $product->delete();
        Toastr::success(translate('Food removed!'));

        return back();
    }

    public function get_categories(Request $request)
    {
        $cat = Category::where(['parent_id' => $request->parent_id])->get();
        $res = '<option value="'. 0 .'" disabled selected>---Select---</option>';
        foreach ($cat as $row) {
            if ($row->id == $request->sub_category) {
                $res .= '<option value="'.$row->id.'" selected >'.$row->name.'</option>';
            } else {
                $res .= '<option value="'.$row->id.'">'.$row->name.'</option>';
            }
        }

        return response()->json([
            'options' => $res,
        ]);
    }

    public function list(Request $request)
    {
        $key = explode(' ', $request['search']);

        // New multi-select filters from offcanvas
        $categoryIds = (array) $request->query('category_ids', []);
        $statuses = (array) $request->query('status', []); // [1], [0] or [1,0]
        $types = (array) $request->query('types', []);     // ['veg'], ['non_veg'] or both
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');

        $category_id = $request->query('category_id', 'all');
        if ($category_id !== 'all' && is_numeric($category_id) && count($categoryIds) === 0) {
            $categoryIds = [$category_id];
        }

        $type = $request->query('type', 'all'); // legacy single type

        $foods = Food::with(['category.parent', 'newVariations', 'newVariationOptions', 'taxVats.tax'])
            ->when(is_array($categoryIds) && count($categoryIds) > 0, function ($query) use ($categoryIds) {
                return $query->where(function ($q) use ($categoryIds) {
                    $q->whereIn('category_id', $categoryIds)
                        ->orWhereHas('category', function ($q2) use ($categoryIds) {
                            $q2->whereIn('parent_id', $categoryIds);
                        });
                });
            })
            ->when(isset($key), function ($q) use ($key) {
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->where('name', 'like', "%{$value}%");
                    }
                });
            })
            // Types (veg/non_veg) - apply only when exactly one is selected; both means all
            ->when(is_array($types) && count($types) === 1, function ($q) use ($types) {
                if ($types[0] === 'veg') {
                    $q->where('veg', true);
                } elseif ($types[0] === 'non_veg') {
                    $q->where('veg', false);
                }
            })
            // Legacy single type filter - only when multi-types not supplied
            ->when((! is_array($types) || count($types) === 0), function ($q) use ($type) {
                $q->type($type);
            })
            // Status filter - apply only when exactly one is selected; both means all
            ->when(is_array($statuses) && count($statuses) === 1, function ($q) use ($statuses) {
                $q->where('status', (int) $statuses[0]);
            })
            // Price range filter
            ->when(is_numeric($minPrice) && is_numeric($maxPrice) && (float) $minPrice <= (float) $maxPrice, function ($q) use ($minPrice, $maxPrice) {
                $q->whereBetween('price', [(float) $minPrice, (float) $maxPrice]);
            })
            ->latest()
            ->paginate(config('default_pagination'));

        $category = $category_id != 'all' ? Category::findOrFail($category_id) : null;
        $addonIds = collect($foods->items())
            ->pluck('add_ons')
            ->filter()
            ->flatMap(fn ($json) => json_decode($json, true) ?? [])
            ->unique()
            ->values();

        $addons = \App\Models\AddOn::withOutGlobalScope(\App\Scopes\RestaurantScope::class)
            ->whereIn('id', $addonIds)
            ->pluck('name', 'id');

        $taxData = Helpers::getTaxSystemType(getTaxVatList: false);
        $productWiseTax = $taxData['productWiseTax'];

        $categoriesList = Category::select('id', 'name')->orderBy('name')->get();
        $minMaxPrices = Food::active()->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();
        $foodMinPrice = round($minMaxPrices->min_price, 2);
        $foodMaxPrice = round(($minMaxPrices->max_price + 1), 2);

        return view('vendor-views.product.list', compact('foods', 'category', 'type', 'productWiseTax', 'addons', 'categoriesList', 'foodMinPrice', 'foodMaxPrice'));
    }

    // public function search(Request $request)
    // {
    //     $key = explode(' ', $request['search']);
    //     $foods = Food::where(function ($q) use ($key) {
    //         foreach ($key as $value) {
    //             $q->where('name', 'like', "%{$value}%");
    //         }
    //     })->limit(50)->get();

    //     return response()->json([
    //         'view' => view('vendor-views.product.partials._table', compact('foods'))->render(),
    //     ]);
    // }

    public function bulk_import_index()
    {
        return view('vendor-views.product.bulk-import');
    }

    public function bulk_import_data(Request $request)
    {
        if (! Helpers::get_restaurant_data()->food_section) {
            Toastr::warning(translate('messages.permission_denied'));

            return back();
        }

        $request->validate([
            'products_file' => 'required|max:2048|mimes:xlsx,csv,xls',
        ], [
            'products_file.required' => translate('messages.File_is_required!'),
            'products_file.max' => translate('messages.Max_file_size_is_2mb'),
            'products_file.mimes' => translate('messages.only_excel_or_csv_file_allowed'),
        ]);

        try {
            $collections = (new FastExcel)->import($request->file('products_file'));
        } catch (\Exception $exception) {
            info(["line___{$exception->getLine()}", $exception->getMessage()]);
            Toastr::error(translate('messages.you_have_uploaded_a_wrong_format_file'));

            return back();
        }

        if (empty($collections) || count($collections) === 0) {
            Toastr::error(translate('messages.uploaded_file_is_empty_or_invalid'));

            return back();
        }

        $isImport = $request->button === 'import';
        $restaurant = Helpers::get_restaurant_data();
        $restaurantId = $restaurant->id;

        try {
            DB::beginTransaction();

            $data = [];
            $totalNewFoods = 0;

            // $data = Helpers::import_food_data($collections, $request->button, $restaurant);
            // /*
            foreach ($collections as $index => $collection) {
                $row = $index + 2;

                $id = isset($collection['Id']) ? trim($collection['Id']) : null;

                if ($id === null || $id === '') {
                    DB::rollBack();
                    Toastr::warning("Food ID not found in Row: {$row}.Please enter a valid ID and try again.");
                    return back();
                }

                if (
                    !isset($collection['Id']) ||
                    empty(trim($collection['Name'])) ||
                    !isset($collection['CategoryId']) ||
                    !isset($collection['Price']) ||
                    empty($collection['AvailableTimeStarts']) ||
                    empty($collection['AvailableTimeEnds']) ||
                    !isset($collection['Discount'])
                ) {
                    throw new \Exception(translate('messages.please_fill_all_required_fields') . " (Row: {$row})");
                }

                // $required = [
                //     'Id', 'Name', 'CategoryId', 'Price', 'AvailableTimeStarts',
                //     'AvailableTimeEnds', 'Discount'
                // ];
                // foreach ($required as $field) {
                //     if (empty(trim(data_get($collection, $field, '')))) {
                //         throw new \Exception(translate('messages.please_fill_all_required_fields') . " (Row: {$row})");
                //     }
                // }

                $id = $collection['Id'];
                $price = (float)$collection['Price'];
                $discount = (float)($collection['Discount'] ?? 0);

                if ($price <= 0) {
                    throw new \Exception(translate('messages.Price_must_be_greater_then_0_on_id') . " {$id} (Row: {$row})");
                }
                if ($discount < 0) {
                    throw new \Exception(translate('messages.Discount_must_be_greater_then_0_on_id') . " {$id} (Row: {$row})");
                }
                if (!empty($collection['Image']) && strlen($collection['Image']) > 30) {
                    throw new \Exception(translate('messages.Image_name_must_be_in_30_char_on_id') . " {$id} (Row: {$row})");
                }

                try {
                    $t1 = Carbon::parse($collection['AvailableTimeStarts']);
                    $t2 = Carbon::parse($collection['AvailableTimeEnds']);
                    if ($t1->gt($t2)) {
                        throw new \Exception(translate('messages.AvailableTimeEnds_must_be_greater_then_AvailableTimeStarts_on_id') . " {$id} (Row: {$row})");
                    }
                } catch (\Exception $e) {
                    throw new \Exception(translate('messages.Invalid_AvailableTimeEnds_or_AvailableTimeStarts_on_id') . " {$id} (Row: {$row})");
                }

                // Category setup
                $category = [['id' => $collection['CategoryId'], 'position' => 1]];
                if (!empty($collection['SubCategoryId'])) {
                    $category[] = ['id' => $collection['SubCategoryId'], 'position' => 2];
                }

                $foodData = [
                    'name' => $collection['Name'],
                    'description' => $collection['Description'] ?? null,
                    'image' => $collection['Image'] ?? null,
                    'category_id' => $collection['SubCategoryId'] ?: $collection['CategoryId'],
                    'category_ids' => json_encode($category),
                    'restaurant_id' => $restaurantId,
                    'price' => $price,
                    'discount' => $discount,
                    'discount_type' => $collection['DiscountType'] ?? 'percent',
                    'available_time_starts' => $collection['AvailableTimeStarts'],
                    'available_time_ends' => $collection['AvailableTimeEnds'],
                    'add_ons' => !empty($collection['Addons']) ? $collection['Addons'] : json_encode([]),
                    'veg' => strtolower($collection['Veg'] ?? '') === 'yes' ? 1 : 0,
                    'recommended' => strtolower($collection['Recommended'] ?? '') === 'yes' ? 1 : 0,
                    'status' => strtolower($collection['Status'] ?? '') === 'active' ? 1 : 0,
                ];

                if ($isImport) {
                    $foodData['created_at'] = now();
                    $foodData['updated_at'] = now();
                } else {
                    $foodData['id'] = $id;
                    $foodData['updated_at'] = now();
                }

                $foodId = $isImport ? null : $id;
                $existingFood = !$isImport ? Food::find($id) : null;

                if ($isImport && $existingFood) {
                    throw new \Exception("Food ID {$id} already exists.");
                }

                if (!$isImport && !$existingFood) {
                    throw new \Exception("Food ID {$id} not found for update. (Row: {$row})");
                }

                // A repeat (subscription) order keeps its own delivery time, so an import is
                // not allowed to move an item's availability off one of those times either.
                if ($existingFood) {
                    $lostRepeatOrderTimes = Helpers::repeat_order_times_lost_by_availability(
                        $existingFood,
                        $collection['AvailableTimeStarts'],
                        $collection['AvailableTimeEnds']
                    );

                    if ($lostRepeatOrderTimes) {
                        throw new \Exception(translate('messages.food_available_time_must_cover_repeat_order', [
                            'times' => implode(', ', array_map(fn ($time) => Helpers::time_format($time), $lostRepeatOrderTimes)),
                        ]) . " (Row: {$row})");
                    }
                }

                if ($existingFood) {
                    $existingFood->update($foodData);
                    $foodId = $existingFood->id;
                } else {
                    $foodId = DB::table('food')->insertGetId($foodData);
                    $totalNewFoods++;
                }

                if (!empty($collection['Image'])) {
                    Helpers::updateStorageTable(Food::class, $foodId, $collection['Image']);
                }
                if (!empty($collection['Variations'])) {
                    $variationsJson = is_string($collection['Variations'])
                        ? $collection['Variations']
                        : json_encode($collection['Variations']);

                    $variations = json_decode($variationsJson, true);

                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($variations)) {
                        continue;
                    }

                    DB::table('variations')->where('food_id', $foodId)->delete();
                    DB::table('variation_options')->where('food_id', $foodId)->delete();

                    foreach ($variations as $option) {
                        if (empty($option['name']) || empty($option['values'])) {
                            continue;
                        }

                        $variation = new Variation();
                        $variation->food_id = $foodId;
                        $variation->name = $option['name'];
                        $variation->type = $option['type'] ?? 'single';
                        $variation->min = $option['min'] ?? 0;
                        $variation->max = $option['max'] ?? 0;
                        $variation->is_required = !empty($option['required']);
                        $variation->save();

                        foreach ($option['values'] as $value) {
                            if (empty($value['label'])) continue;

                            $variationOption = new VariationOption();
                            $variationOption->food_id = $foodId;
                            $variationOption->variation_id = $variation->id;
                            $variationOption->option_name = $value['label'];
                            $variationOption->option_price = $value['optionPrice'] ?? 0;
                            $variationOption->stock_type = $collection['StockType'] ?? 'unlimited';
                            $variationOption->total_stock = ($variationOption->stock_type === 'unlimited')
                                ? 0
                                : ($value['total_stock'] ?? 0);
                            $variationOption->save();
                        }
                    }
                }

                $data[] = $foodId;
            }

            if ($isImport && $restaurant->restaurant_model == 'subscription') {
                $restSub = $restaurant->restaurant_sub;
                if ($restSub && $restSub->max_product !== 'unlimited') {
                    $currentFoods = Food::where('restaurant_id', $restaurantId)->count();
                    $available = $restSub->max_product - $currentFoods;

                    if ($totalNewFoods > $available) {
                        DB::rollBack();
                        Toastr::error(translate('messages.you_have_reached_the_maximum_limit_of_food'));
                        return back();
                    }

                    $restSub->decrement('max_product', $totalNewFoods);
                    if ($restSub->max_product <= 0) {
                        $restaurant->update(['food_section' => 0]);
                    }
                }
            }
            // */

            DB::commit();

            $msg = $isImport
                ? translate('messages.product_imported_successfully', ['count' => count($data)])
                : translate('messages.Food_imported_successfully', ['count' => count($data)]);

            Toastr::success($msg);

            return back();

        } catch (\Exception $e) {
            DB::rollBack();
            info(['bulk_import_error' => $e->getMessage()]);
            Toastr::error($e->getMessage());

            return back();
        }
    }

    public function bulk_export_index()
    {
        return view('vendor-views.product.bulk-export');
    }

    public function bulk_export_data(Request $request)
    {
        if (! Helpers::get_restaurant_data()->food_section) {
            Toastr::warning(translate('messages.permission_denied'));

            return back();
        }

        $request->validate([
            'type' => 'required',
            'start_id' => 'required_if:type,id_wise',
            'end_id' => 'required_if:type,id_wise',
            'from_date' => 'required_if:type,date_wise',
            'to_date' => 'required_if:type,date_wise',
        ]);
        $products = Food::when($request['type'] == 'date_wise', function ($query) use ($request) {
            $query->whereBetween('created_at', [$request['from_date'].' 00:00:00', $request['to_date'].' 23:59:59']);
        })
            ->when($request['type'] == 'id_wise', function ($query) use ($request) {
                $query->whereBetween('id', [$request['start_id'], $request['end_id']]);
            })
            ->where('restaurant_id', Helpers::get_restaurant_id())
            ->get();

            if($request->type == 'id_wise' && $products->isEmpty()){
                Toastr::warning(translate('Invalid ID. No file generated.'));
                return back();
            }
            if($request->type == 'date_wise' && $products->isEmpty()){
                Toastr::warning(translate('No data found in between this date range.'));
                return back();
            }

        return (new FastExcel(ProductLogic::format_export_foods($products)))->download('Foods.xlsx');
    }

    public function food_variation_generator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'options' => 'required',
        ]);

        $food_variations = [];
        if (isset($request->options)) {
            foreach (array_values($request->options) as $key => $option) {

                $temp_variation['name'] = $option['name'];
                $temp_variation['type'] = $option['type'];
                $temp_variation['min'] = $option['min'] ?? 0;
                $temp_variation['max'] = $option['max'] ?? 0;
                $temp_variation['required'] = $option['required'] ?? 'off';
                if ($option['min'] > 0 && $option['min'] > $option['max']) {
                    $validator->getMessageBag()->add('name', translate('messages.minimum_value_can_not_be_greater_then_maximum_value'));

                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if (! isset($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('messages.please_add_options_for').$option['name']);

                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if ($option['max'] > count($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('messages.please_add_more_options_or_change_the_max_value_for').' ' .$option['name']);
    
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                $temp_value = [];

                foreach (array_values($option['values']) as $value) {
                    if (isset($value['label'])) {
                        $temp_option['label'] = $value['label'];
                    }
                    $temp_option['optionPrice'] = $value['optionPrice'];
                    array_push($temp_value, $temp_option);
                }
                $temp_variation['values'] = $temp_value;
                array_push($food_variations, $temp_variation);
            }
        }

        return response()->json([
            'variation' => json_encode($food_variations),
        ]);
    }

    public function stockOutList(Request $request)
    {
        $key = explode(' ', $request['search']);

        // New multi-select filters from offcanvas
        $categoryIds = (array) $request->query('category_ids', []);
        $statuses = (array) $request->query('status', []); // [1], [0] or [1,0]
        $types = (array) $request->query('types', []);     // ['veg'], ['non_veg'] or both
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');

        $category_id = $request->query('category_id', 'all');
        if ($category_id !== 'all' && is_numeric($category_id) && count($categoryIds) === 0) {
            $categoryIds = [$category_id];
        }

        $type = $request->query('type', 'all'); // legacy single type

        $foods = Food::with(['category.parent', 'newVariations', 'newVariationOptions', 'taxVats.tax'])
            ->where('stock_type', '!=', 'unlimited')->where(function ($query) {
                $query->whereRaw('item_stock - sell_count <= 0')->orWhereHas('newVariationOptions', function ($query) {
                    $query->whereRaw('total_stock - sell_count <= 0');
                });
            })
            ->when(is_array($categoryIds) && count($categoryIds) > 0, function ($query) use ($categoryIds) {
                return $query->where(function ($q) use ($categoryIds) {
                    $q->whereIn('category_id', $categoryIds)
                        ->orWhereHas('category', function ($q2) use ($categoryIds) {
                            $q2->whereIn('parent_id', $categoryIds);
                        });
                });
            })
            ->when(isset($key), function ($q) use ($key) {
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->where('name', 'like', "%{$value}%");
                    }
                });
            })
            // Types (veg/non_veg) - apply only when exactly one is selected; both means all
            ->when(is_array($types) && count($types) === 1, function ($q) use ($types) {
                if ($types[0] === 'veg') {
                    $q->where('veg', true);
                } elseif ($types[0] === 'non_veg') {
                    $q->where('veg', false);
                }
            })
            // Legacy single type filter - only when multi-types not supplied
            ->when((! is_array($types) || count($types) === 0), function ($q) use ($type) {
                $q->type($type);
            })
            // Status filter - apply only when exactly one is selected; both means all
            ->when(is_array($statuses) && count($statuses) === 1, function ($q) use ($statuses) {
                $q->where('status', (int) $statuses[0]);
            })
            // Price range filter
            ->when(is_numeric($minPrice) && is_numeric($maxPrice) && (float) $minPrice <= (float) $maxPrice, function ($q) use ($minPrice, $maxPrice) {
                $q->whereBetween('price', [(float) $minPrice, (float) $maxPrice]);
            })
            ->latest()
            ->paginate(config('default_pagination'));

        $category = $category_id != 'all' ? Category::findOrFail($category_id) : null;
        $addonIds = collect($foods->items())
            ->pluck('add_ons')
            ->filter()
            ->flatMap(fn ($json) => json_decode($json, true) ?? [])
            ->unique()
            ->values();

        $addons = \App\Models\AddOn::withOutGlobalScope(\App\Scopes\RestaurantScope::class)
            ->whereIn('id', $addonIds)
            ->pluck('name', 'id');

        $taxData = Helpers::getTaxSystemType(getTaxVatList: false);
        $productWiseTax = $taxData['productWiseTax'];

        $categoriesList = Category::select('id', 'name')->orderBy('name')->get();
        $minMaxPrices = Food::where('stock_type', '!=', 'unlimited')->where(function ($query) {
            $query->whereRaw('item_stock - sell_count <= 0')->orWhereHas('newVariationOptions', function ($query) {
                $query->whereRaw('total_stock - sell_count <= 0');
            });
        })
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();
        $foodMinPrice = $minMaxPrices->min_price;
        $foodMaxPrice = $minMaxPrices->max_price;

        return view('vendor-views.product.stock-out-list', compact('foods', 'category', 'type', 'productWiseTax', 'addons', 'categoriesList', 'foodMinPrice', 'foodMaxPrice'));

    }

    public function updateStock(Request $request)
    {
        $product = Food::findOrFail($request->food_id);
        $product->item_stock = $request->item_stock;
        $product->sell_count = 0;
        $product->save();
        if ($request->option) {
            foreach ($request->option as $key => $value) {
                VariationOption::where('food_id', $product->id)->where('id', $key)->update([
                    'sell_count' => 0,
                    'total_stock' => $value,
                ]);
            }
        }
        Toastr::success(translate('Stock_updated_successfully'));

        return back();
    }

    public function addToSession(Request $request)
    {
        Session::put($request->value, true);

        return response()->json(['success' => true]);
    }

    public function export(Request $request)
    {
        try {
            $key = explode(' ', $request['search']);

            $categoryIds = (array) $request->query('category_ids', []);
            $statuses = (array) $request->query('status', []);
            $types = (array) $request->query('types', []);
            $minPrice = $request->query('min_price');
            $maxPrice = $request->query('max_price');

            // Backward compatibility with previous single filters
            $category_id = $request->query('category_id', 'all');
            if ($category_id !== 'all' && is_numeric($category_id) && count($categoryIds) === 0) {
                $categoryIds = [$category_id];
            }
            $type = $request->query('type', 'all'); // legacy single type

            $foods = Food::with(['category.parent', 'newVariations', 'newVariationOptions', 'taxVats.tax'])
                ->when(isset($request->is_stock_out), function ($query) {
                    $query->where('stock_type', '!=', 'unlimited')
                        ->where(function ($query) {
                            $query->whereRaw('item_stock - sell_count <= 0')
                                ->orWhereHas('newVariationOptions', function ($query) {
                                    $query->whereRaw('total_stock - sell_count <= 0');
                                });
                        });
                })
                ->when(is_array($categoryIds) && count($categoryIds) > 0, function ($query) use ($categoryIds) {
                    return $query->where(function ($q) use ($categoryIds) {
                        $q->whereIn('category_id', $categoryIds)
                            ->orWhereHas('category', function ($q2) use ($categoryIds) {
                                $q2->whereIn('parent_id', $categoryIds);
                            });
                    });
                })
                ->when(isset($key), function ($q) use ($key) {
                    $q->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->where('name', 'like', "%{$value}%");
                        }
                    });
                })
                // Types (veg/non_veg) - apply only when exactly one is selected; both means all
                ->when(is_array($types) && count($types) === 1, function ($q) use ($types) {
                    if ($types[0] === 'veg') {
                        $q->where('veg', true);
                    } elseif ($types[0] === 'non_veg') {
                        $q->where('veg', false);
                    }
                })
                // Legacy single type filter - only when multi-types not supplied
                ->when((! is_array($types) || count($types) === 0), function ($q) use ($type) {
                    $q->type($type);
                })
                // Status filter - apply only when exactly one is selected; both means all
                ->when(is_array($statuses) && count($statuses) === 1, function ($q) use ($statuses) {
                    $q->where('status', (int) $statuses[0]);
                })
                // Price range filter
                ->when(is_numeric($minPrice) && is_numeric($maxPrice) && (float) $minPrice <= (float) $maxPrice, function ($q) use ($minPrice, $maxPrice) {
                    $q->whereBetween('price', [(float) $minPrice, (float) $maxPrice]);
                })
                ->latest()
                ->get();

            $taxData = Helpers::getTaxSystemType(getTaxVatList: false);
            $productWiseTax = $taxData['productWiseTax'];

            $minMaxPrices = Food::selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();
            $foodMinPrice = $minPrice ?? $minMaxPrices->min_price;
            $foodMaxPrice = $maxPrice ?? $minMaxPrices->max_price;

            $categoryNames = '';

            if (is_array($categoryIds) && count($categoryIds) > 0) {
                $categoryNames = Category::whereIn('id', $categoryIds)
                    ->pluck('name')
                    ->implode(', ');
            }

            $data = [
                'data' => $foods,
                'search' => $request['search'] ?? null,
                'category' => $categoryNames,
                'productWiseTax' => $productWiseTax,
                'priceRange' => $foodMinPrice.' - '.$foodMaxPrice,
                'foodType' => ! empty($types) ? ucwords(str_replace('_', ' ', implode(', ', $types))) : '',
                'status' => ! empty($statuses) ? ucwords(implode(', ', $statuses)) : '',
            ];

            if (isset($request->is_stock_out)) {
                $filename = 'Stock_out_food_list';
            } else {
                $filename = 'Food_list';
            }

            if ($request->type == 'csv') {
                return Excel::download(new FoodListExport($data), $filename.'.csv');
            }

            return Excel::download(new FoodListExport($data), $filename.'.xlsx');

        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return back();
        }
    }
}
