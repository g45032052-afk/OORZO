<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\AddOn;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Exports\AddonExport;
use App\Models\AddonCategory;
use App\Models\Restaurant;
use Maatwebsite\Excel\Facades\Excel;

class AddOnController extends Controller
{
    public function index(Request $request)
    {
        $key = explode(' ', $request['search']) ?? null;
        $taxData = Helpers::getTaxSystemType();
        $productWiseTax = $taxData['productWiseTax'];
        $addons = AddOn::orderBy('name')->with($productWiseTax ? ['taxVats.tax'] : [])
        ->when(isset($key) , function($query) use($key){
            $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }
            });
        })
        ->when($request->has('stock_type') && $request->stock_type != 'all', function($query) use($request){
            if($request->stock_type == 'unlimited'){
                $query->where('stock_type', 'unlimited');
            }
            elseif($request->stock_type == 'limited'){
                $query->where('stock_type', 'limited');
            }
            elseif($request->stock_type == 'daily'){
                $query->where('stock_type', 'daily');
            }
        })
        ->paginate(config('default_pagination'));
        $addonCategories = AddonCategory::where('status', 1)->select('id', 'name')->get();

        $taxVats = $taxData['taxVats'];

        return view('vendor-views.addon.index', compact('addons','addonCategories', 'productWiseTax', 'taxVats'));
    }

    public function store(Request $request)
    {
        if(!Helpers::get_restaurant_data()->food_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }
        $request->validate([
            'name' => 'required|array',
            'name.*' => 'max:191',
            'price' => 'required|numeric|between:0,999999999999.99',
            'category_id' => 'required',
        ],[
            'name.required' => translate('messages.Name is required!'),
        ]);

        $addon = new AddOn();
        $addon->name = $request->name[array_search('default', $request->lang)];
        $addon->price = $request->price;
        $addon->restaurant_id = \App\CentralLogics\Helpers::get_restaurant_id();
        $addon->stock_type = $request->stock_type ?? 'unlimited';
        $addon->addon_stock = $request->stock_type != 'unlimited' ?  $request->addon_stock : 0;
        $addon->addon_category_id = $request->category_id;
        $addon->save();


        if (addon_published_status('TaxModule')) {
            $SystemTaxVat = \Modules\TaxModule\Entities\SystemTaxSetup::where('is_active', 1)->where('is_default', 1)->first();
            if ($SystemTaxVat?->tax_type == 'product_wise') {
                foreach ($request['tax_ids'] ?? [] as $tax_id) {
                    \Modules\TaxModule\Entities\Taxable::create(
                        [
                            'taxable_type' => AddOn::class,
                            'taxable_id' => $addon->id,
                            'system_tax_setup_id' => $SystemTaxVat->id,
                            'tax_id' => $tax_id
                        ],
                    );
                }
            }
        }


        Helpers::add_or_update_translations(request: $request, key_data:'name' , name_field:'name' , model_name: 'AddOn' ,data_id: $addon->id,data_value: $addon->name);

        Toastr::success(translate('messages.addon_added_successfully'));
        return back();
    }

    public function edit($id)
    {
        if(!Helpers::get_restaurant_data()->food_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }
        $addon = AddOn::withoutGlobalScope('translate')->findOrFail($id);

        $taxData = Helpers::getTaxSystemType();
        $productWiseTax = $taxData['productWiseTax'];
        $taxVats = $taxData['taxVats'];
        $taxVatIds =  $productWiseTax ? $addon->taxVats()->pluck('tax_id')->toArray(): [];

        $addonCategories = AddonCategory::where('status', 1)->select('id', 'name')->get();

        return view('vendor-views.addon.edit', compact('addon','addonCategories', 'taxVats', 'productWiseTax','taxVatIds'));
    }

    public function update(Request $request, $id)
    {
        if(!Helpers::get_restaurant_data()->food_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }
        $request->validate([
            'name' => 'required|max:191',
            'price' => 'required|numeric|between:0,999999999999.99',
        ], [
            'name.required' => translate('messages.Name is required!'),
        ]);

        $addon = AddOn::find($id);
        $addon->name = $request->name[array_search('default', $request->lang)];
        $addon->price = $request->price;
        $addon->stock_type = $request->stock_type ?? 'unlimited' ;
        $addon->addon_stock = $request->stock_type != 'unlimited' ?  $request->addon_stock : 0;
        $addon->sell_count = 0;
          $addon->addon_category_id = $request->category_id;
        $addon?->save();

                if (addon_published_status('TaxModule')) {
            $SystemTaxVat = \Modules\TaxModule\Entities\SystemTaxSetup::where('is_active', 1)->where('is_default', 1)->first();
            if ($SystemTaxVat?->tax_type == 'product_wise') {
                $addon->taxVats()->delete();
                foreach ($request['tax_ids'] ?? [] as $tax_id) {
                    \Modules\TaxModule\Entities\Taxable::create(
                        [
                            'taxable_type' => AddOn::class,
                            'taxable_id' => $addon->id,
                            'system_tax_setup_id' => $SystemTaxVat->id,
                            'tax_id' => $tax_id
                        ],
                    );
                }
            }
        }
        Helpers::add_or_update_translations(request: $request, key_data:'name' , name_field:'name' , model_name: 'AddOn' ,data_id: $addon->id,data_value: $addon->name);
        Toastr::success(translate('messages.addon_updated_successfully'));
        return redirect(route('vendor.addon.add-new'));
    }

    public function delete(Request $request)
    {
        if(!Helpers::get_restaurant_data()->food_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }
        $addon = AddOn::find($request->id);
        $addon?->taxVats()->delete();
        $addon?->delete();
        Toastr::success(translate('messages.addon_deleted_successfully'));
        return back();
    }

    public function status(Request $request)
    {
        $coupon = AddOn::find($request->id);
        $coupon->status = $request->status;
        $coupon->save();
        Toastr::success(translate('messages.Addon_status_updated'));
        return back();
    }

    public function export(Request $request)
    {
        try {
            $key = explode(' ', $request['search']) ?? null;
            $taxData = Helpers::getTaxSystemType();
            $productWiseTax = $taxData['productWiseTax'];
            $addons = AddOn::orderBy('name')->with($productWiseTax ? ['taxVats.tax'] : [])
            ->when(isset($key) , function($query) use($key){
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                });
            })
            ->when($request->has('stock_type') && $request->stock_type != 'all', function($query) use($request){
                if($request->stock_type == 'unlimited'){
                    $query->where('stock_type', 'unlimited');
                }
                elseif($request->stock_type == 'limited'){
                    $query->where('stock_type', 'limited');
                }
                elseif($request->stock_type == 'daily'){
                    $query->where('stock_type', 'daily');
                }
            })
            ->orderBy('name')->get();


            $restaurant_id = \App\CentralLogics\Helpers::get_restaurant_id();


            $data = [
                'data' => $addons,
                'search' => $request['search'] ?? null,
                'restaurant' => $restaurant_id != 'all' ? Restaurant::findOrFail($restaurant_id)?->name : null,
                'productWiseTax' => $productWiseTax,
                'stock' => $request->stock_type ?? 'all',
            ];
            if ($request->type == 'csv') {
                return Excel::download(new AddonExport($data), 'Addons.csv');
            }
            return Excel::download(new AddonExport($data), 'Addons.xlsx');
        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);
            return back();
        }
    }
}
