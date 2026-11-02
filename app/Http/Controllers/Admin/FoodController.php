<?php

namespace App\Http\Controllers\Admin;

use App\Models\Allergy;
use App\Models\Nutrition;
use Carbon\Carbon;
use App\Models\Tag;
use App\Models\Food;
use App\Models\Review;
use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Support\Str;
use App\Models\ItemCampaign;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Exports\FoodListExport;
use App\Scopes\RestaurantScope;
use App\Exports\FoodReviewExport;
use Illuminate\Support\Facades\DB;
use App\CentralLogics\ProductLogic;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Exports\RestaurantFoodExport;
use App\Models\FoodSeoData;
use App\Models\RestaurantCategory;
use App\Models\Variation;
use App\Models\VariationOption;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FoodController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where(['position' => 0])->get();

        $taxData = Helpers::getTaxSystemType();
        $productWiseTax = $taxData['productWiseTax'];
        $taxVats = $taxData['taxVats'];
        $restaurant = null;

        if($request->restaurant_id){
            $restaurant = Restaurant::find($request->restaurant_id);
        }

        return view('admin-views.product.index', compact('categories', 'productWiseTax', 'taxVats', 'restaurant'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'restaurant_category_id' => Helpers::restaurantCategoryStatus()
                ? Helpers::normalizeRestaurantCategoryId($request->input('restaurant_category_id'))
                : null,
        ]);

        $validator = Validator::make($request->all(), [
            'name.0' => 'required',
            'name.*' => 'max:191',
            'category_id' => 'required',
            'restaurant_category_id' => [
                'nullable',
                Rule::exists('restaurant_categories', 'id')->where(function ($query) use ($request) {
                    return $query->where('restaurant_id', $request->restaurant_id);
                }),
            ],
            'image' => 'nullable|max:2048',
            'price' => 'required|numeric|between:.01,999999999999.99',
            'discount' => 'required|numeric|min:0',
            'restaurant_id' => 'required',
            'description.*' => 'max:65535',
            'veg' => 'required'
        ], [
            'description.*.max' => translate('messages.Description must be in 65535 char limit'),
            'name.0.required' => translate('messages.item_name_required'),
            'category_id.required' => translate('messages.category_required'),
            'veg.required' => translate('messages.item_type_is_required'),
            'restaurant_id.required' => translate('You must select a restaurant')
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
            $tags = explode(",", $request->tags);
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
            $category[] = [
                'id' => $request->category_id,
                'position' => 1,
            ];
        }
        if ($request->sub_category_id != null) {
            $category[] = [
                'id' => $request->sub_category_id,
                'position' => 2,
            ];
        }
        if ($request->sub_sub_category_id != null) {
            $category[] = [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ];
        }

        $food->category_ids = json_encode($category);
        $food->category_id = $request?->sub_category_id ?? $request?->category_id;
        $food->description = $request->description[array_search('default', $request->lang)];
        $food->choice_options = json_encode([]);
        $food->variations = json_encode([]);
        $food->price = $request->price;
        $food->image = Helpers::upload(dir: 'product/', format: 'png', image: $request->file('image'));
        $food->available_time_starts = $request->available_time_starts;
        $food->available_time_ends = $request->available_time_ends;
        $food->discount = $request->discount ?? 0;
        $food->discount_type = $request->discount_type;

        $food->attributes = $request->has('attribute_id') ? json_encode($request->attribute_id) : json_encode([]);
        $food->add_ons = $request->has('addon_ids') ? json_encode($request->addon_ids) : json_encode([]);
        $food->restaurant_id = $request->restaurant_id;
        $food->restaurant_category_id = $request->restaurant_category_id;
        $food->veg = $request->veg;
        $food->item_stock = $request?->item_stock ?? 0;
        $food->stock_type = $request->stock_type;
        $food->maximum_cart_quantity = $request->maximum_cart_quantity;
        $food->is_halal = $request->is_halal ?? 0;


        if (isset($request->options)) {
            foreach (array_values($request->options) as $key => $option) {
                if ($option['min'] > 0 && $option['min'] > $option['max']) {
                    $validator->getMessageBag()->add('name', translate('messages.minimum_value_can_not_be_greater_then_maximum_value'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if (!isset($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('messages.please_add_options_for') . $option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if ($option['max'] > count($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('messages.please_add_more_options_or_change_the_max_value_for').' ' . $option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
            }

            $food->save();

            foreach (array_values($request->options) as $key => $option) {
                $variation = new Variation ();
                $variation->food_id = $food->id;
                $variation->name = $option['name'];
                $variation->type = $option['type'];
                $variation->min = $option['min'] ?? 0;
                $variation->max = $option['max'] ?? 0;
                $variation->is_required = data_get($option, 'required') == 'on' ? true : false;
                $variation->save();

                foreach (array_values($option['values']) as $value) {
                    $VariationOption = new VariationOption ();
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

        if (addon_published_status('TaxModule')) {
            $SystemTaxVat = \Modules\TaxModule\Entities\SystemTaxSetup::where('is_active', 1)->where('is_default', 1)->first();
            if ($SystemTaxVat?->tax_type == 'product_wise') {
                foreach ($request['tax_ids'] ?? [] as $tax_id) {
                    \Modules\TaxModule\Entities\Taxable::create(
                        [
                            'taxable_type' => Food::class,
                            'taxable_id' => $food->id,
                            'system_tax_setup_id' => $SystemTaxVat->id,
                            'tax_id' => $tax_id
                        ],
                    );
                }
            }
        }

        Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'Food', data_id: $food->id, data_value: $food->name);
        Helpers::add_or_update_translations(request: $request, key_data: 'description', name_field: 'description', model_name: 'Food', data_id: $food->id, data_value: $food->description);


//        FoodSeoData::create(
//            Helpers::getFoodSEOData($request, $food->id, null)
//        );

        return response()->json(['message' => translate('messages.food_added_successfully'),'redirect' => route('admin.food.list')], 200);
    }

    public function view($id)
    {
        $taxData = Helpers::getTaxSystemType();
        $productWiseTax = $taxData['productWiseTax'];

        $product = Food::with($productWiseTax ? ['taxVats.tax', 'newVariationOptions.variation'] : ['newVariationOptions.variation'])->withoutGlobalScope(RestaurantScope::class)->findOrFail($id);
        $reviews = Review::where(['food_id' => $id])->with('customer')->latest()->paginate(config('default_pagination'));
        return view('admin-views.product.view', compact('product', 'reviews', 'productWiseTax'));
    }

    public function edit($id)
    {
        $product = Food::withoutGlobalScope(RestaurantScope::class)->withoutGlobalScope('translate')->with(['translations', 'foodSeoData', 'newVariations', 'newVariationOptions'])->findOrFail($id);

        if (!$product) {
            Toastr::error(translate('messages.food_not_found'));
            return back();
        }
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

        return view('admin-views.product.edit', compact('product', 'product_category', 'selected_addons', 'categories', 'productWiseTax', 'taxVats', 'taxVatIds'));
    }

    public function status(Request $request)
    {
        $product = Food::withoutGlobalScope(RestaurantScope::class)->findOrFail($request->id);
        $product->status = $request->status;
        $product->save();

        if ($request->status != 1) {
            $product?->carts()?->delete();
        }
        Toastr::success(translate('messages.food_status_updated'));
        return back();
    }

    public function update(Request $request, $id)
    {
        $request->merge([
            'restaurant_category_id' => Helpers::restaurantCategoryStatus()
                ? Helpers::normalizeRestaurantCategoryId($request->input('restaurant_category_id'))
                : null,
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'array',
            'name.0' => 'required',
            'name.*' => 'max:191',
            'category_id' => 'required',
            'restaurant_category_id' => [
                'nullable',
                Rule::exists('restaurant_categories', 'id')->where(function ($query) use ($request) {
                    return $query->where('restaurant_id', $request->restaurant_id);
                }),
            ],
            'price' => 'required|numeric|between:.01,999999999999.99',
            'restaurant_id' => 'required',
            'veg' => 'required',
            'description' => 'array',
            'description.*' => 'max:65535',
            'discount' => 'required|numeric|min:0',
            'image' => 'nullable|max:2048',
        ], [
            'description.*.max' => translate('messages.Description must be in 65535 char limit'),
            'name.0.required' => translate('messages.item_name_required'),
            'category_id.required' => translate('messages.category_required'),
            'veg.required' => translate('messages.item_type_is_required'),
            'restaurant_id.required' => translate('You must select a restaurant')

        ]);



        // A repeat (subscription) order is delivered on its own schedule, so shrinking the
        // available time out from under one would silently break the recurring delivery.
        $repeat_order_times = Helpers::repeat_order_times_lost_by_availability(
            Food::withoutGlobalScope(RestaurantScope::class)->find($id),
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
            $tags = explode(",", $request->tags);
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

        $p = Food::withoutGlobalScope(RestaurantScope::class)->find($id);

        $p->name = $request->name[array_search('default', $request->lang)];
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

        $p->category_id = $request->sub_category_id ?? $request->category_id;
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
                if (!isset($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('messages.please_add_options_for') . $option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if ($option['max'] > count($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('messages.please_add_more_options_or_change_the_max_value_for').' ' . $option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }

                $variation = Variation::updateOrCreate([
                    'id' => $option['variation_id'] ?? null,
                    'food_id' => $p->id,
                ], [
                    "name" => $option['name'],
                    "type" => $option['type'],
                    "min" => $option['min'] ?? 0,
                    "max" => $option['max'] ?? 0,
                    "is_required" => data_get($option, 'required') == 'on' ? true : false,
                ]);

                foreach (array_values($option['values']) as $value) {
                    VariationOption::updateOrCreate([
                        'id' => $value['option_id'] ?? null,
                        'food_id' => $p->id,
                        'variation_id' => $variation->id,
                    ], [
                        "option_name" => $value['label'],
                        "option_price" => $value['optionPrice'],
                        "total_stock" => data_get($value, 'total_stock') == null || $request?->stock_type == 'unlimited' ? 0 : data_get($value, 'total_stock'),
                        "stock_type" => $request?->stock_type ?? 'unlimited',
                        "sell_count" => 0,
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

        $slug = Str::slug($request->name[array_search('default', $request->lang)]);
        $p->slug = $p->slug ? $p->slug : "{$slug}{$p->id}";
        //combinations end
        $p->price = $request->price;
        $p->image = $request->has('image') ? Helpers::update(dir: 'product/', old_image: $p->image, format: 'png', image: $request->file('image')) : $p->image;
        $p->available_time_starts = $request->available_time_starts;
        $p->available_time_ends = $request->available_time_ends;

        $p->discount = $request->discount ?? 0;
        $p->discount_type = $request->discount_type;
        $p->item_stock = $request?->item_stock ?? 0;
        $p->stock_type = $request->stock_type;
        $p->sell_count = 0;

        $p->attributes = $request->has('attribute_id') ? json_encode($request->attribute_id) : json_encode([]);
        $p->add_ons = $request->has('addon_ids') ? json_encode($request->addon_ids) : json_encode([]);
        $p->restaurant_id = $request->restaurant_id;
        $p->restaurant_category_id = $request->restaurant_category_id;
        $p->veg = $request->veg;
        $p->maximum_cart_quantity = $request->maximum_cart_quantity;
        $p->is_halal = $request->is_halal ?? 0;

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
                                'tax_id' => $tax_id
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

        // Opened from the restaurant details food tab: send the user back to that tab, not the global list.
        $redirect = $request->input('from') === 'restaurant' && $p->restaurant_id
            ? route('admin.restaurant.view', [$p->restaurant_id, 'product'])
            : route('admin.food.list');

        return response()->json([
            'message' => translate('messages.food_updated_successfully'),
            'redirect' => $redirect
        ], 200);
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
        $restaurantId = $request->query('restaurant_id');

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
            'has_categories' => $restaurantId
                ? Helpers::hasAnyRestaurantCategory((int) $restaurantId)
                : false,
        ]);
    }

    public function delete(Request $request)
    {
        $product = Food::withoutGlobalScope(RestaurantScope::class)->withoutGlobalScope('translate')->find($request->id);

        if ($product->image) {
            Helpers::check_and_delete('product/', $product['image']);
        }
        $product?->translations()?->delete();
        $product?->carts()?->delete();
        $product?->newVariationOptions()?->delete();
        $product?->newVariations()?->delete();
        $product?->taxVats()->delete();
        $product->delete();
        Toastr::success(translate('messages.product_deleted_successfully'));
        return back();
    }


    public function variant_price(Request $request)
    {
        if ($request->item_type == 'food') {
            $product = Food::withoutGlobalScope(RestaurantScope::class)->find($request->id);
        } else {
            $product = ItemCampaign::find($request->id);
        }
        // $product = Food::withoutGlobalScope(RestaurantScope::class)->find($request->id);
        $str = '';
        $quantity = 0;
        $price = 0;
        $addon_price = 0;

        foreach (json_decode($product->choice_options) as $key => $choice) {
            if ($str != null) {
                $str .= '-' . str_replace(' ', '', $request[$choice->name]);
            } else {
                $str .= str_replace(' ', '', $request[$choice->name]);
            }
        }

        if ($request['addon_id']) {
            foreach ($request['addon_id'] as $id) {
                $addon_price += $request['addon-price' . $id] * $request['addon-quantity' . $id];
            }
        }

        if ($str != null) {
            $count = count(json_decode($product->variations));
            for ($i = 0; $i < $count; $i++) {
                if (json_decode($product->variations)[$i]->type == $str) {
                    $price = json_decode($product->variations)[$i]->price - Helpers::product_discount_calculate($product, json_decode($product->variations)[$i]->price, $product->restaurant);
                }
            }
        } else {
            $price = $product->price - Helpers::product_discount_calculate($product, $product->price, $product->restaurant);
        }

        return array('price' => Helpers::format_currency(($price * $request->quantity) + $addon_price));
    }

    public function get_categories(Request $request)
    {
        $cat = Category::where(['parent_id' => $request->parent_id])->get();
        $res = '<option value="' . 0 . '" disabled selected>---' . translate('messages.Select') . '---</option>';
        foreach ($cat as $row) {
            if ($row->id == $request->sub_category) {
                $res .= '<option value="' . $row->id . '" selected >' . $row->name . '</option>';
            } else {
                $res .= '<option value="' . $row->id . '">' . $row->name . '</option>';
            }
        }
        return response()->json([
            'options' => $res,
        ]);
    }

    public function get_foods(Request $request)
    {
        $foods = Food::withoutGlobalScope(RestaurantScope::class)->with('restaurant')->whereHas('restaurant', function ($query) use ($request) {
            $query->where('zone_id', $request->zone_id);
        })->get();
        $res = '';
        if (count($foods) > 0 && !$request->data) {
            $res = '<option value="' . 0 . '" disabled selected>---' . translate('messages.Select') . '---</option>';
        }

        foreach ($foods as $row) {
            $res .= '<option value="' . $row->id . '" ';
            if ($request->data) {
                $res .= in_array($row->id, $request->data) ? 'selected ' : '';
            }
            $res .= '>' . $row->name . ' (' . $row->restaurant->name . ')' . '</option>';
        }
        return response()->json([
            'options' => $res,
        ]);
    }

    public function list(Request $request)
    {
        $key = explode(' ', $request['search']);

        // New multi-select filters from offcanvas
        $restaurantIds = (array)$request->query('restaurant_ids', []);
        $categoryIds = (array)$request->query('category_ids', []);
        $statuses = (array)$request->query('status', []); // [1], [0] or [1,0]
        $types = (array)$request->query('types', []);     // ['veg'], ['non_veg'] or both
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');

        // Backward compatibility with previous single filters
        $restaurant_id = $request->query('restaurant_id', 'all');
        if ($restaurant_id !== 'all' && is_numeric($restaurant_id) && count($restaurantIds) === 0) {
            $restaurantIds = [$restaurant_id];
        }
        $category_id = $request->query('category_id', 'all');
        if ($category_id !== 'all' && is_numeric($category_id) && count($categoryIds) === 0) {
            $categoryIds = [$category_id];
        }
        $type = $request->query('type', 'all'); // legacy single type

        $foods = Food::with(['restaurant:id,name', 'category.parent', 'newVariations', 'newVariationOptions', 'taxVats.tax'])
            ->when(is_array($restaurantIds) && count($restaurantIds) > 0, function ($query) use ($restaurantIds) {
                return $query->whereIn('restaurant_id', $restaurantIds);
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
            ->when((!is_array($types) || count($types) === 0), function ($q) use ($type) {
                $q->type($type);
            })
            // Status filter - apply only when exactly one is selected; both means all
            ->when(is_array($statuses) && count($statuses) === 1, function ($q) use ($statuses) {
                $q->where('status', (int)$statuses[0]);
            })
            // Price range filter
            ->when(is_numeric($minPrice) && is_numeric($maxPrice) && (float)$minPrice <= (float)$maxPrice, function ($q) use ($minPrice, $maxPrice) {
                $q->whereBetween('price', [(float)$minPrice, (float)$maxPrice]);
            })
            ->latest()
            ->paginate(config('default_pagination'));

        $addonIds = collect($foods->items())
            ->pluck('add_ons')
            ->filter()
            ->flatMap(fn($json) => json_decode($json, true) ?? [])
            ->unique()
            ->values();


        $addons = \App\Models\AddOn::withOutGlobalScope(\App\Scopes\RestaurantScope::class)
            ->whereIn('id', $addonIds)
            ->pluck('name', 'id');


        $restaurant = $restaurant_id != 'all' ? Restaurant::select('id', 'name')->find($restaurant_id) : null;
        $category = $category_id != 'all' ? Category::select('id', 'name', 'position')->with('translations')->find($category_id) : null;
        $taxData = Helpers::getTaxSystemType(getTaxVatList: false);
        $productWiseTax = $taxData['productWiseTax'];

        // Dynamic lists for offcanvas filters
        $restaurantsList = Restaurant::whereHas('vendor', function ($q) {
            $q->where('status', 1);
        })
            ->select('id', 'name')->orderBy('name')->get();
        $categoriesList = Category::select('id', 'name')->orderBy('name')->get();
        $minMaxPrices = Food::selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();
        $foodMinPrice = $minMaxPrices->min_price;
        $foodMaxPrice = $minMaxPrices->max_price;

        return view('admin-views.product.list',
            compact('foods', 'restaurant', 'category', 'type', 'productWiseTax', 'addons',
                'restaurantsList', 'categoriesList', 'foodMinPrice', 'foodMaxPrice'));
    }

    public function search(Request $request)
    {
        $key = explode(' ', $request['search']);
        $restaurant_id = $request->restaurant_id ?? null;

        $foods = Food::withoutGlobalScope(RestaurantScope::class)
            ->when(is_numeric($restaurant_id), function ($query) use ($restaurant_id) {
                return $query->where('restaurant_id', $restaurant_id);
            })
            ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->where('name', 'like', "%{$value}%");
                }
            })
            ->limit(50)->get();
        return response()->json(['count' => count($foods),
            'view' => view('admin-views.product.partials._table', compact('foods'))->render()
        ]);
    }

    public function search_vendor(Request $request)
    {
        $key = explode(' ', $request['search']);
        $restaurant_id = $request->restaurant_id ?? null;
        $foods = Food::withoutGlobalScope(RestaurantScope::class)
            ->when(is_numeric($restaurant_id), function ($query) use ($restaurant_id) {
                return $query->where('restaurant_id', $restaurant_id);
            })
            ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->where('name', 'like', "%{$value}%");
                }
            })->limit(50)->get();
        return response()->json(['count' => count($foods),
            'view' => view('admin-views.vendor.view.partials._product', compact('foods'))->render()
        ]);
    }

    public function review_list(Request $request)
    {
        $key = explode(' ', $request['search']);
        $reviews = Review::with(['customer', 'food' => function ($q) {
            $q->withoutGlobalScope(RestaurantScope::class);
        }])
            ->when(isset($key), function ($query) use ($key) {
                $query->whereHas('food', function ($query) use ($key) {
                    foreach ($key as $value) {
                        $query->where('name', 'like', "%{$value}%");
                    }
                });
            })
            ->latest()->paginate(config('default_pagination'));
        return view('admin-views.product.reviews-list', compact('reviews'));
    }

    public function reviews_status(Request $request)
    {
        $review = Review::find($request->id);
        $review->status = $request->status;
        $review->save();
        Toastr::success(translate('messages.review_visibility_updated'));
        return back();
    }

    public function bulk_import_index()
    {
        return view('admin-views.product.bulk-import');
    }

    public function bulk_import_data(Request $request)
    {
        $request->validate([
            'upload_excel' => 'required|max:2048|mimes:xlsx,csv,xls'
        ], [
            'upload_excel.required' => translate('messages.File_is_required!'),
            'upload_excel.max' => translate('messages.Max_file_size_is_2mb'),
            'upload_excel.mimes' => translate('messages.only_excel_or_csv_file_allowed'),
        ]);

        try {
            $collections = (new FastExcel)->import($request->file('upload_excel'));
        } catch (\Throwable $exception) {
            info(["line___{$exception->getLine()}", $exception->getMessage()]);
            Toastr::error(translate('messages.you_have_uploaded_a_wrong_format_file'));
            return back();
        }

        if (empty($collections) || count($collections) === 0) {
            Toastr::error(translate('messages.uploaded_file_is_empty_or_invalid'));
            return back();
        }

        $data = [];
        $action = $request->button; // 'import' or 'update'

        try {
            DB::beginTransaction();

            // $data = Helpers::import_food_data($collections, $action);
            // /*
            foreach ($collections as $index => $collection) {
                $row = $index + 2; // Excel row number (header + 1)

                // Required field check
                if (
                    !isset($collection['Id']) ||
                    empty(trim($collection['Name'])) ||
                    !isset($collection['CategoryId']) ||
                    !isset($collection['SubCategoryId']) ||
                    !isset($collection['Price']) ||
                    empty($collection['AvailableTimeStarts']) ||
                    empty($collection['AvailableTimeEnds']) ||
                    !isset($collection['RestaurantId']) ||
                    !isset($collection['Discount'])
                ) {
                    throw new \Exception(translate('messages.please_fill_all_required_fields') . " (Row: {$row})");
                }

                // Validations
                if ($collection['Price'] < 0) {
                    throw new \Exception(translate('messages.Price_must_be_greater_then_0_on_id') . ' ' . $collection['Id'] . " (Row: {$row})");
                }
                if ($collection['Discount'] < 0) {
                    throw new \Exception(translate('messages.Discount_must_be_greater_then_0_on_id') . ' ' . $collection['Id'] . " (Row: {$row})");
                }
                if (!empty($collection['Image']) && strlen($collection['Image']) > 30) {
                    throw new \Exception(translate('messages.Image_name_must_be_in_30_char_on_id') . ' ' . $collection['Id'] . " (Row: {$row})");
                }

                try {
                    $t1 = Carbon::parse($collection['AvailableTimeStarts']);
                    $t2 = Carbon::parse($collection['AvailableTimeEnds']);
                    if ($t1->gt($t2)) {
                        throw new \Exception(translate('messages.AvailableTimeEnds_must_be_greater_then_AvailableTimeStarts_on_id') . ' ' . $collection['Id'] . " (Row: {$row})");
                    }
                } catch (\Exception $e) {
                    throw new \Exception(translate('messages.Invalid_AvailableTimeEnds_or_AvailableTimeStarts_on_id') . ' ' . $collection['Id'] . " (Row: {$row})");
                }

                // Prepare category
                $category = [
                    ['id' => $collection['CategoryId'], 'position' => 1],
                ];
                if (!empty($collection['SubCategoryId'])) {
                    $category[] = ['id' => $collection['SubCategoryId'], 'position' => 2];
                }

                $foodData = [
                    'name' => $collection['Name'],
                    'description' => $collection['Description'] ?? null,
                    'image' => $collection['Image'] ?? null,
                    'category_id' => $collection['SubCategoryId'] ?: $collection['CategoryId'],
                    'category_ids' => json_encode($category),
                    'restaurant_id' => $collection['RestaurantId'],
                    'price' => $collection['Price'],
                    'discount' => $collection['Discount'] ?? 0,
                    'discount_type' => $collection['DiscountType'] ?? 'percent',
                    'available_time_starts' => $collection['AvailableTimeStarts'],
                    'available_time_ends' => $collection['AvailableTimeEnds'],
                    'add_ons' => !empty($collection['Addons']) ? $collection['Addons'] : json_encode([]),
                    'veg' => strtolower($collection['Veg'] ?? '') === 'yes' ? 1 : 0,
                    'recommended' => strtolower($collection['Recommended'] ?? '') === 'yes' ? 1 : 0,
                    'status' => strtolower($collection['Status'] ?? '') === 'active' ? 1 : 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                // Handle Import or Update
                $foodId = $collection['Id'];
                $existingFood = DB::table('food')->where('id', $foodId)->first();

                if ($action === 'import' && $existingFood) {
                    throw new \Exception("Food ID {$foodId} already exists.");
                }

                if ($action === 'update' && !$existingFood) {
                    throw new \Exception("Food ID {$foodId} not found for update.");
                }


                // A repeat (subscription) order keeps its own delivery time, so an import is
                // not allowed to move an item's availability off one of those times either.
                if ($existingFood) {
                    $lostRepeatOrderTimes = Helpers::repeat_order_times_lost_by_availability(
                        Food::withoutGlobalScope(RestaurantScope::class)->find($foodId),
                        $collection['AvailableTimeStarts'],
                        $collection['AvailableTimeEnds']
                    );

                    if ($lostRepeatOrderTimes) {
                        throw new \Exception(translate('messages.food_available_time_must_cover_repeat_order', [
                            'times' => implode(', ', array_map(fn ($time) => Helpers::time_format($time), $lostRepeatOrderTimes)),
                        ]) . " (Row: {$row})");
                    }
                }
                // Insert or Update Food
                if ($existingFood) {
                    DB::table('food')->where('id', $foodId)->update($foodData);
                } else {
                    $foodId = DB::table('food')->insertGetId($foodData);
                }

                // Update storage table for image
                if (!empty($collection['Image'])) {
                    Helpers::updateStorageTable(get_class(new Food), $foodId, $collection['Image']);
                }

                // Handle Variations (Main Fix)
                if (!empty($collection['Variations'])) {
                    // Delete old variations
                    DB::table('variations')->where('food_id', $foodId)->delete();
                    DB::table('variation_options')->where('food_id', $foodId)->delete();

                    $variations = is_string($collection['Variations'])
                        ? json_decode($collection['Variations'], true)
                        : $collection['Variations'];

                    if (is_array($variations)) {
                        foreach ($variations as $option) {
                            if (empty($option['name']) || empty($option['values'])) {
                                continue;
                            }

                            $variation = new \App\Models\Variation();
                            $variation->food_id = $foodId;
                            $variation->name = $option['name'];
                            $variation->type = $option['type'] ?? 'single';
                            $variation->min = $option['min'] ?? 0;
                            $variation->max = $option['max'] ?? 0;
                            $variation->is_required = !empty($option['required']);
                            $variation->save();

                            foreach ($option['values'] as $value) {
                                if (empty($value['label'])) continue;

                                $variationOption = new \App\Models\VariationOption();
                                $variationOption->food_id = $foodId;
                                $variationOption->variation_id = $variation->id;
                                $variationOption->option_name = $value['label'];
                                $variationOption->option_price = $value['optionPrice'] ?? 0;
                                $variationOption->stock_type = $collection['StockType'] ?? 'unlimited';
                                $variationOption->total_stock = ($variationOption->stock_type === 'unlimited') ? 0 : ($value['total_stock'] ?? 0);
                                $variationOption->save();
                            }
                        }
                    }
                }

                $data[] = $foodId;
            }
            // */

            DB::commit();
            Toastr::success(translate('messages.product_imported_successfully', ['count' => count($data)]));
            return back();

        } catch (\Exception $e) {
            DB::rollBack();
            info(["bulk_import_error" => $e->getMessage()]);
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function bulk_export_index()
    {
        return view('admin-views.product.bulk-export');
    }

    public function bulk_export_data(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'start_id' => 'required_if:type,id_wise',
            'end_id' => 'required_if:type,id_wise',
            'from_date' => 'required_if:type,date_wise',
            'to_date' => 'required_if:type,date_wise'
        ]);
        $products = Food::when($request['type'] == 'date_wise', function ($query) use ($request) {
            $query->whereBetween('created_at', [$request['from_date'] . ' 00:00:00', $request['to_date'] . ' 23:59:59']);
        })
            ->when($request['type'] == 'id_wise', function ($query) use ($request) {
                $query->whereBetween('id', [$request['start_id'], $request['end_id']]);
            })
            ->withoutGlobalScope(RestaurantScope::class)->get();
        if ($products->count() > 0) {
            return (new FastExcel(ProductLogic::format_export_foods(Helpers::Export_generator($products))))->download('Foods.xlsx');
        } else {
            Toastr::error(translate('messages.there_are_no_foods_in_between_these_ids'));
            return back();
        }
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
                if (!isset($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('messages.please_add_options_for') . $option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if ($option['max'] > count($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('messages.please_add_more_options_or_change_the_max_value_for').' ' . $option['name']);
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
            'variation' => json_encode($food_variations)
        ]);
    }


    public function export(Request $request)
    {
        try {
            $key = explode(' ', $request['search']);

            // New multi-select filters from offcanvas
            $restaurantIds = (array)$request->query('restaurant_ids', []);
            $categoryIds = (array)$request->query('category_ids', []);
            $statuses = (array)$request->query('status', []); // [1], [0] or [1,0]
            $types = (array)$request->query('types', []);     // ['veg'], ['non_veg'] or both
            $minPrice = $request->query('min_price');
            $maxPrice = $request->query('max_price');

            // Backward compatibility with previous single filters
            $restaurant_id = $request->query('restaurant_id', 'all');
            if ($restaurant_id !== 'all' && is_numeric($restaurant_id) && count($restaurantIds) === 0) {
                $restaurantIds = [$restaurant_id];
            }
            $category_id = $request->query('category_id', 'all');
            if ($category_id !== 'all' && is_numeric($category_id) && count($categoryIds) === 0) {
                $categoryIds = [$category_id];
            }
            $type = $request->query('type', 'all'); // legacy single type

            $foods = Food::with(['restaurant:id,name', 'category.parent', 'newVariations', 'newVariationOptions', 'taxVats.tax'])
                ->when(isset($request->is_stock_out), function ($query) {
                    $query->where('stock_type', '!=', 'unlimited')
                        ->where(function ($query) {
                            $query->whereRaw('item_stock - sell_count <= 0')
                                ->orWhereHas('newVariationOptions', function ($query) {
                                    $query->whereRaw('total_stock - sell_count <= 0');
                                });
                        });
                })
                ->when(is_array($restaurantIds) && count($restaurantIds) > 0, function ($query) use ($restaurantIds) {
                    return $query->whereIn('restaurant_id', $restaurantIds);
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
                ->when((!is_array($types) || count($types) === 0), function ($q) use ($type) {
                    $q->type($type);
                })
                // Status filter - apply only when exactly one is selected; both means all
                ->when(is_array($statuses) && count($statuses) === 1, function ($q) use ($statuses) {
                    $q->where('status', (int)$statuses[0]);
                })
                // Price range filter
                ->when(is_numeric($minPrice) && is_numeric($maxPrice) && (float)$minPrice <= (float)$maxPrice, function ($q) use ($minPrice, $maxPrice) {
                    $q->whereBetween('price', [(float)$minPrice, (float)$maxPrice]);
                })
                ->latest()
                ->get();

            $taxData = Helpers::getTaxSystemType(getTaxVatList: false);
            $productWiseTax = $taxData['productWiseTax'];

            $minMaxPrices = Food::selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();
            $foodMinPrice = $minMaxPrices->min_price;
            $foodMaxPrice = $minMaxPrices->max_price;

            $restaurantNames = '';
            $categoryNames = '';

            if (is_array($restaurantIds) && count($restaurantIds) > 0) {
                $restaurantNames = Restaurant::whereIn('id', $restaurantIds)
                    ->pluck('name')
                    ->implode(', ');
            }

            if (is_array($categoryIds) && count($categoryIds) > 0) {
                $categoryNames = Category::whereIn('id', $categoryIds)
                    ->pluck('name')
                    ->implode(', ');
            }

            $data = [
                'data' => $foods,
                'search' => $request['search'] ?? null,
                'restaurant' => $restaurantNames,
                'category' => $categoryNames,
                'productWiseTax' => $productWiseTax,
                'priceRange' => $foodMinPrice . ' - ' . $foodMaxPrice,
                'foodType' => !empty($types) ? ucwords(str_replace('_', ' ', implode(', ', $types))) : '',
                'status' => !empty($statuses) ? ucwords(implode(', ', $statuses)) : '',
            ];

            if (isset($request->is_stock_out)) {
                $filename = 'Stock_out_food_list';
            } else {
                $filename = 'Food_list';
            }

            if ($request->type == 'csv') {
                return Excel::download(new FoodListExport($data), $filename . '.csv');
            }
            return Excel::download(new FoodListExport($data), $filename . '.xlsx');

        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);
            return back();
        }
    }


    public function reviews_export(Request $request)
    {
        try {
            $key = explode(' ', $request['search']);
            $reviews = Review::with(['customer', 'food' => function ($q) {
                $q->withoutGlobalScope(RestaurantScope::class);
            }])
                ->when(isset($key), function ($query) use ($key) {
                    $query->whereHas('food', function ($query) use ($key) {
                        foreach ($key as $value) {
                            $query->where('name', 'like', "%{$value}%");
                        }
                    });
                })
                ->latest()->get();

            $data = [
                'data' => $reviews,
                'search' => $request['search'] ?? null,
            ];

            if ($request->type == 'csv') {
                return Excel::download(new FoodReviewExport($data), 'FoodReview.csv');
            }
            return Excel::download(new FoodReviewExport($data), 'FoodReview.xlsx');
        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);
            return back();
        }
    }


    public function food_wise_reviews_export(Request $request)
    {
        try {
            $product = Food::withoutGlobalScope(RestaurantScope::class)->findOrFail($request->id)?->category_ids;
            $reviews = Review::where(['food_id' => $request->id])->with('customer')->latest()->get();

            $data = [
                'type' => 'single',
                'category' => \App\CentralLogics\Helpers::get_category_name($product),
                'data' => $reviews,
                'search' => $request['search'] ?? null,
                'restaurant' => $request['restaurant'] ?? null,
            ];

            if ($request->type == 'csv') {
                return Excel::download(new FoodReviewExport($data), 'FoodWiseReview.csv');
            }
            return Excel::download(new FoodReviewExport($data), 'FoodWiseReview.xlsx');
        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);
            return back();
        }
    }


    public function restaurant_food_export($type, $restaurant_id)
    {
        try {
            $key = explode(' ', request()?->search);
            $foods = Food::withoutGlobalScope(\App\Scopes\RestaurantScope::class)
                ->with('category.parent')
                ->where('restaurant_id', $restaurant_id)
                ->when(isset($key), function ($q) use ($key) {
                    $q->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->where('name', 'like', "%{$value}%");
                        }
                    });
                })
                ->latest()->get();
            $taxData = Helpers::getTaxSystemType(getTaxVatList: false);
            $productWiseTax = $taxData['productWiseTax'];

            $restaurant = Restaurant::where('id', $restaurant_id)->select(['name', 'zone_id'])->first();
            $data = [
                'data' => $foods,
                'search' => request()?->search ?? null,
                'zone' => Helpers::get_zones_name($restaurant->zone_id),
                'restaurant_name' => $restaurant->name,
                'productWiseTax' => $productWiseTax
            ];
            if ($type == 'csv') {
                return Excel::download(new RestaurantFoodExport($data), 'FoodList.csv');
            }
            return Excel::download(new RestaurantFoodExport($data), 'FoodList.xlsx');
        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);
            return back();
        }
    }

    public function updateStock(Request $request)
    {
        $product = Food::withoutGlobalScope(RestaurantScope::class)->findOrFail($request->food_id);
        $product->item_stock = $request->item_stock;
        $product->sell_count = 0;
        $product->save();
        if ($request->option) {
            foreach ($request->option as $key => $value) {
                VariationOption::where('food_id', $product->id)->where('id', $key)->update([
                    'sell_count' => 0,
                    'total_stock' => $value
                ]);
            }
        }
        Toastr::success(translate('Stock_updated_successfully'));
        return back();
    }

    public function stockOutList(Request $request)
    {
        $key = explode(' ', $request['search']);

        // New multi-select filters from offcanvas
        $restaurantIds = (array)$request->query('restaurant_ids', []);
        $categoryIds = (array)$request->query('category_ids', []);
        $statuses = (array)$request->query('status', []); // [1], [0] or [1,0]
        $types = (array)$request->query('types', []);     // ['veg'], ['non_veg'] or both
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');

        // Backward compatibility with previous single filters
        $restaurant_id = $request->query('restaurant_id', 'all');
        if ($restaurant_id !== 'all' && is_numeric($restaurant_id) && count($restaurantIds) === 0) {
            $restaurantIds = [$restaurant_id];
        }
        $category_id = $request->query('category_id', 'all');
        if ($category_id !== 'all' && is_numeric($category_id) && count($categoryIds) === 0) {
            $categoryIds = [$category_id];
        }
        $type = $request->query('type', 'all'); // legacy single type

        $foods = Food::with(['restaurant:id,name', 'category.parent', 'newVariations', 'newVariationOptions', 'taxVats.tax'])
            ->where('stock_type', '!=', 'unlimited')->where(function ($query) {
                $query->whereRaw('item_stock - sell_count <= 0')->orWhereHas('newVariationOptions', function ($query) {
                    $query->whereRaw('total_stock - sell_count <= 0');
                });
            })
            ->when(is_array($restaurantIds) && count($restaurantIds) > 0, function ($query) use ($restaurantIds) {
                return $query->whereIn('restaurant_id', $restaurantIds);
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
            ->when((!is_array($types) || count($types) === 0), function ($q) use ($type) {
                $q->type($type);
            })
            // Status filter - apply only when exactly one is selected; both means all
            ->when(is_array($statuses) && count($statuses) === 1, function ($q) use ($statuses) {
                $q->where('status', (int)$statuses[0]);
            })
            // Price range filter
            ->when(is_numeric($minPrice) && is_numeric($maxPrice) && (float)$minPrice <= (float)$maxPrice, function ($q) use ($minPrice, $maxPrice) {
                $q->whereBetween('price', [(float)$minPrice, (float)$maxPrice]);
            })
            ->latest()
            ->paginate(config('default_pagination'));

        $addonIds = collect($foods->items())
            ->pluck('add_ons')
            ->filter()
            ->flatMap(fn($json) => json_decode($json, true) ?? [])
            ->unique()
            ->values();


        $addons = \App\Models\AddOn::withOutGlobalScope(\App\Scopes\RestaurantScope::class)
            ->whereIn('id', $addonIds)
            ->pluck('name', 'id');


        $restaurant = $restaurant_id != 'all' ? Restaurant::select('id', 'name')->find($restaurant_id) : null;
        $category = $category_id != 'all' ? Category::select('id', 'name', 'position')->with('translations')->find($category_id) : null;
        $taxData = Helpers::getTaxSystemType(getTaxVatList: false);
        $productWiseTax = $taxData['productWiseTax'];

        // Dynamic lists for offcanvas filters
        $restaurantsList = Restaurant::select('id', 'name')->orderBy('name')->get();
        $categoriesList = Category::select('id', 'name')->orderBy('name')->get();
        $minMaxPrices = Food::where('stock_type', '!=', 'unlimited')->where(function ($query) {
            $query->whereRaw('item_stock - sell_count <= 0')->orWhereHas('newVariationOptions', function ($query) {
                $query->whereRaw('total_stock - sell_count <= 0');
            });
        })
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();
        $foodMinPrice = $minMaxPrices->min_price;
        $foodMaxPrice = $minMaxPrices->max_price;

        return view('admin-views.product.stock-out-list',
            compact('foods', 'restaurant', 'category', 'type', 'productWiseTax', 'addons',
                'restaurantsList', 'categoriesList', 'foodMinPrice', 'foodMaxPrice'));

    }

}
