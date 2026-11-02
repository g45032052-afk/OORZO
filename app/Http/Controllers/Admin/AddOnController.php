<?php

namespace App\Http\Controllers\Admin;

use App\Models\AddOn;
use App\Models\AddonCategory;
use App\Models\Restaurant;
use App\Models\Translation;
use App\Exports\AddonExport;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Scopes\RestaurantScope;
use Illuminate\Support\Facades\DB;
use App\CentralLogics\ProductLogic;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;

class AddOnController extends Controller
{
    public function index(Request $request)
    {
        $key = explode(' ', $request['search']);
        $restaurant_id = $request->query('restaurant_id', 'all');
        $taxData = Helpers::getTaxSystemType();
        $productWiseTax = $taxData['productWiseTax'];
        $taxVats = $taxData['taxVats'];


        $addons = AddOn::withoutGlobalScope(RestaurantScope::class)->with($productWiseTax ? ['taxVats.tax', 'restaurant'] : ['restaurant'])
            ->when(is_numeric($restaurant_id), function ($query) use ($restaurant_id) {
                return $query->where('restaurant_id', $restaurant_id);
            })
            ->when(isset($key), function ($q1) use ($key) {
                $q1->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                });
            })
            ->orderBy('name')->paginate(config('default_pagination'));
        $restaurant = $restaurant_id != 'all' ? Restaurant::findOrFail($restaurant_id) : null;
        $addonCategories = AddonCategory::where('status', 1)->select('id', 'name')->get();


        return view('admin-views.addon.index', compact('addons', 'restaurant', 'productWiseTax', 'taxVats', 'addonCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name.*' => 'max:191',
            'name' => 'array|required',
            'restaurant_id' => 'required|numeric',
            'category_id' => 'required|numeric',
            'price' => 'required|numeric|between:0,999999999999.99',
            'name.0' => 'required',
        ], [
            'name.required' => translate('messages.Name is required!'),
            'restaurant_id.required' => translate('messages.please_select_restaurant'),
            'restaurant_id.numeric' => translate('messages.please_select_restaurant'),
            'category_id.required' => translate('messages.please_select_category'),
            'name.0.required' => translate('default_data_is_required'),
        ]);


        $addon = new AddOn();
        $addon->name = $request->name[array_search('default', $request->lang)];
        $addon->price = $request->price;
        $addon->stock_type = $request->stock_type;
        $addon->addon_stock = $request->stock_type != 'unlimited' ?  $request->addon_stock : 0;
        $addon->restaurant_id = $request->restaurant_id;
        $addon->addon_category_id = $request->category_id;
        $addon->save();

        Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'AddOn', data_id: $addon->id, data_value: $addon->name);

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

        Toastr::success(translate('messages.addon_added_successfully'));
        return back();
    }

    public function edit($id)
    {
        $addonCategories = AddonCategory::where('status', 1)->select('id', 'name')->get();
        $addon = AddOn::withoutGlobalScope(RestaurantScope::class)->withoutGlobalScope('translate')->with('translations')->findOrFail($id);

        $productWiseTax = false;
        $taxVats = [];
        $taxVatIds = [];
        if (addon_published_status('TaxModule')) {
            $taxVatIds = $addon->taxVats()->pluck('tax_id')->toArray();

            $SystemTaxVat = \Modules\TaxModule\Entities\SystemTaxSetup::where('is_active', 1)->where('is_default', 1)->first();
            if ($SystemTaxVat?->tax_type == 'product_wise') {
                $productWiseTax = true;
                $taxVats =  \Modules\TaxModule\Entities\Tax::where('is_active', 1)->where('is_default', 1)->get(['id', 'name', 'tax_rate']);
            }
        }

        $language = getWebConfig('language');

        return response()->json([
            'view' => view('admin-views.addon._edit', compact('addon', 'taxVats', 'productWiseTax', 'taxVatIds', 'addonCategories', 'language'))->render(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:191',
            'restaurant_id' => 'required|numeric',
            'category_id' => 'required|numeric',
            'price' => 'required|numeric|between:0,999999999999.99',
            'name.0' => 'required',
        ], [
            'name.required' => translate('messages.Name is required!'),
            'restaurant_id.required' => translate('messages.please_select_restaurant'),
            'restaurant_id.numeric' => translate('messages.please_select_restaurant'),
            'category_id.required' => translate('messages.please_select_category'),
            'name.0.required' => translate('default_data_is_required'),
        ]);
        $addon = AddOn::withoutGlobalScope(RestaurantScope::class)->find($id);
        $addon->name = $request->name[array_search('default', $request->lang)];
        $addon->price = $request->price;
        $addon->restaurant_id = $request->restaurant_id;
        $addon->addon_category_id = $request->category_id;
        $addon->stock_type = $request->stock_type;
        $addon->addon_stock = $request->stock_type != 'unlimited' ?  $request->addon_stock : 0;
        $addon->sell_count = 0;
        $addon->save();

        Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'AddOn', data_id: $addon->id, data_value: $addon->name);

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
        Toastr::success(translate('messages.addon_updated_successfully'));
        return redirect(route('admin.addon.add-new'));
    }

    public function delete(Request $request)
    {
        $addon = AddOn::withoutGlobalScope(RestaurantScope::class)->find($request->id);
        $addon?->translations()?->delete();
        $addon?->taxVats()->delete();
        $addon->delete();
        Toastr::success(translate('messages.addon_deleted_successfully'));
        return back();
    }

    public function status($addon, Request $request)
    {
        $addon_data = AddOn::withoutGlobalScope(RestaurantScope::class)->find($addon);
        $addon_data->status = $request->status;
        $addon_data->save();
        Toastr::success(translate('messages.addon_status_updated'));
        return back();
    }


    public function export_addons(Request $request)
    {
        try {
            $key = explode(' ', $request['search']);
            $restaurant_id = $request->query('restaurant_id', 'all');
            $taxData = Helpers::getTaxSystemType();
            $productWiseTax = $taxData['productWiseTax'];
            $addons = AddOn::withoutGlobalScope(RestaurantScope::class)->with($productWiseTax ? ['taxVats.tax', 'restaurant'] : ['restaurant'])
                ->when(is_numeric($restaurant_id), function ($query) use ($restaurant_id) {
                    return $query->where('restaurant_id', $restaurant_id);
                })
                ->when(isset($key), function ($q1) use ($key) {
                    $q1->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('name', 'like', "%{$value}%");
                        }
                    });
                })
                ->orderBy('name')->get();


            $data = [
                'data' => $addons,
                'search' => $request['search'] ?? null,
                'restaurant' => $restaurant_id != 'all' ? Restaurant::findOrFail($restaurant_id)?->name : null,
                'productWiseTax' => $productWiseTax
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
    public function bulk_import_index()
    {
        return view('admin-views.addon.bulk-import');
    }

    public function bulk_import_data(Request $request)
    {
        $request->validate([
            'upload_excel' => 'required|file|max:2048|mimes:xlsx,csv,xls'
        ], [
            'upload_excel.required' => translate('messages.File_is_required!'),
            'upload_excel.max' => translate('messages.Max_file_size_is_2mb'),
            'upload_excel.mimes' => translate('messages.only_excel_or_csv_file_allowed'),
        ]);

        try {
            $collections = (new FastExcel)->import($request->file('upload_excel'));
        } catch (\Throwable $exception) {
            info($exception->getMessage());
            Toastr::error(translate('messages.you_have_uploaded_a_wrong_format_file'));
            return back();
        }

        if (empty($collections) || count($collections) === 0) {
            Toastr::error(translate('messages.uploaded_file_is_empty_or_invalid'));
            return back();
        }

        $data = [];

        // If Import button pressed
        if ($request->button === 'import') {

            foreach ($collections as $collection) {
                if (!isset($collection['Name'], $collection['RestaurantId'])) {
                    Toastr::error(translate('messages.please_fill_all_required_fields'));
                    return back();
                }

                if (!is_numeric($collection['RestaurantId'])) {
                    Toastr::error(translate('messages.Restaurant_ID_must_be_numeric'));
                    return back();
                }

                if (isset($collection['Price']) && $collection['Price'] < 0) {
                    Toastr::error(translate('messages.Price_must_be_greater_then_0'));
                    return back();
                }

                $data[] = [
                    'name' => $collection['Name'],
                    'price' => $collection['Price'] ?? 0,
                    'restaurant_id' => $collection['RestaurantId'],
                    'status' => (isset($collection['Status']) && strtolower($collection['Status']) === 'active') ? 1 : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            try {
                DB::beginTransaction();
                foreach (array_chunk($data, 100) as $chunk) {
                    DB::table('add_ons')->insert($chunk);
                }
                DB::commit();

                Toastr::success(translate('messages.addon_imported_successfully', ['count' => count($data)]));
                return back();

            } catch (\Throwable $e) {
                DB::rollBack();
                info(["line___{$e->getLine()}", $e->getMessage()]);
                Toastr::error(translate('messages.failed_to_import_data'));
                return back();
            }
        }

        // Update Section
        foreach ($collections as $collection) {
            if (!isset($collection['Id'], $collection['Name'], $collection['Price'], $collection['RestaurantId'])) {
                Toastr::error(translate('messages.please_fill_all_required_fields'));
                return back();
            }

            if (!is_numeric($collection['RestaurantId'])) {
                Toastr::error(translate('messages.Restaurant_ID_must_be_numeric'));
                return back();
            }

            if ($collection['Price'] < 0) {
                Toastr::error(translate('messages.Price_must_be_greater_then_0'));
                return back();
            }

            $data[] = [
                'id' => $collection['Id'],
                'name' => $collection['Name'],
                'price' => $collection['Price'],
                'restaurant_id' => $collection['RestaurantId'],
                'status' => (isset($collection['Status']) && strtolower($collection['Status']) === 'active') ? 1 : 0,
                'updated_at' => now(),
            ];
        }

        try {
            DB::beginTransaction();
            foreach (array_chunk($data, 100) as $chunk) {
                DB::table('add_ons')->upsert($chunk, ['id'], ['name', 'price', 'restaurant_id', 'status']);
            }
            DB::commit();

            Toastr::success(translate('messages.addon_updated_successfully', ['count' => count($data)]));
            return back();

        } catch (\Throwable $e) {
            DB::rollBack();
            info(["line___{$e->getLine()}", $e->getMessage()]);
            Toastr::error(translate('messages.failed_to_import_data'));
            return back();
        }
    }

    public function bulk_export_index()
    {
        return view('admin-views.addon.bulk-export');
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
        $query = AddOn::when($request['type'] == 'date_wise', function ($query) use ($request) {
            $query->whereBetween('created_at', [$request['from_date'] . ' 00:00:00', $request['to_date'] . ' 23:59:59']);
        })
            ->when($request['type'] == 'id_wise', function ($query) use ($request) {
                $query->whereBetween('id', [$request['start_id'], $request['end_id']]);
            })
            ->withoutGlobalScope(RestaurantScope::class);

        if (!$query->exists()) {
            if($request['type'] == 'date_wise') {
                Toastr::error(translate('messages.there_are_no_addons_in_between_these_dates'));
            } elseif($request['type'] == 'id_wise') {
                Toastr::error(translate('Invalid ID. No file generated'));
            } else
            {
                Toastr::error(translate('messages.no_data_to_export'));
            }
            return back();
        }

            $addons = $query->get();
        if ($addons->count() > 0) {
            return (new FastExcel(ProductLogic::format_export_addons(Helpers::Export_generator($addons))))->download('Addons.xlsx');
        } else {
            Toastr::error(translate('messages.there_are_no_addons_in_between_these_ids'));
            return back();
        }
    }
}
