<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Translation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Exports\CategoryExport;
use Illuminate\Support\Facades\DB;
use App\CentralLogics\CategoryLogic;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;

class CategoryController extends Controller
{
    function index(Request $request)
    {
        $relationships = [
            'translations' => 'value',
        ];

        $taxData = Helpers::getTaxSystemType();
        $categoryWiseTax = $taxData['categoryWiseTax'];
        $taxVats = $taxData['taxVats'];

        $priority = $request['priority'];
        $categories = Category::where(['position' => 0])->latest()
            ->search(keywords:request()?->search, mainCol: ['name','id'], relations: $relationships)
            ->when(isset($priority), function ($q) use ($priority) {
                $q->where('priority', $priority);
            })
            ->with($categoryWiseTax ? ['taxVats.tax'] : [])
            ->paginate(config('default_pagination'));


        return view('admin-views.category.index', compact('categories', 'categoryWiseTax', 'taxVats'));
    }

    function sub_index(Request $request)
    {

        $relationships = [
            'translations' => 'value',
        ];
        $categories = Category::with(['parent'])->where(['position' => 1])->search(keywords:request()?->search, mainCol: ['name','id'], relations: $relationships);

        if ($request->has('status') && count($request->status) > 0) {
            $categories->whereIn('status', $request->status);
        }

        if ($request->priority != "") {
            $categories->where('priority', $request->priority);
        }
        if ($request->category != "") {
            $categories->where('parent_id', $request->category);
        }

        $categories = $categories->latest()->paginate(config('default_pagination'))->appends($request->all());
        return view('admin-views.category.sub-index', compact('categories'));
    }

    function sub_sub_index()
    {
        return view('admin-views.category.sub-sub-index');
    }

    function sub_category_index()
    {
        return view('admin-views.category.index');
    }

    function sub_sub_category_index()
    {
        return view('admin-views.category.index');
    }

    function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories|max:100',
            'image' => 'nullable|max:2048',
            'name.0' => 'required',
            'meta_image' => 'nullable|max:2048',
            'priority' => 'nullable|integer|in:0,1,2',

        ], [
            'name.required' => translate('messages.Name is required!'),
            'name.0.required' => translate('default_name_is_required'),
            'image' => translate('image_must_be_less_than_2mb'),
            'meta_image' => translate('meta_image_must_be_less_than_2mb'),
        ]);

        $category = new Category();
        $category->name = $request->name[array_search('default', $request->lang)];
        $category->image = $request->has('image') ? Helpers::upload(dir: 'category/', format: 'png', image: $request->file('image')) : 'def.png';
        $category->parent_id = $request?->parent_id ?? 0;
        $category->position = $request->position;
        $category->priority = $request->priority ?? 0;

        if ($category->position == 0) {
            $category->meta_title = $request->meta_title[array_search('default', $request->lang)];
            $category->meta_description = $request->meta_description[array_search('default', $request->lang)];
            $category->meta_image = $request->file('meta_image') ? Helpers::upload(dir: 'meta_image/', format: 'png', image: $request->file('meta_image')) : $category->meta_image;
            $category->meta_data = (Helpers::formatMetaData($request->all(), $category->meta_data));
        }
        $category->save();
        // Helpers::add_or_update_translations(request: $request, key_data: 'meta_title', name_field: 'meta_title', model_name: 'Category', data_id: $category->id, data_value: $category->meta_title);
        // Helpers::add_or_update_translations(request: $request, key_data: 'meta_description', name_field: 'meta_description', model_name: 'Category', data_id: $category->id, data_value: $category->meta_description);
        Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'Category', data_id: $category->id, data_value: $category->name);


        if ($category->parent_id == 0) {
            if (addon_published_status('TaxModule')) {
                $SystemTaxVat = \Modules\TaxModule\Entities\SystemTaxSetup::where('is_active', 1)->where('is_default', 1)->first();
                if ($SystemTaxVat?->tax_type == 'category_wise') {

                    foreach ($request['tax_ids'] ?? [] as $tax_ids) {
                        \Modules\TaxModule\Entities\Taxable::create(
                            [
                                'taxable_type' => Category::class,
                                'taxable_id' => $category->id,
                                'system_tax_setup_id' => $SystemTaxVat->id
                                , 'tax_id' => $tax_ids
                            ],
                        );
                    }

                }
            }
            Toastr::success(translate('messages.category_added_successfully'));
        } else {
            Toastr::success(translate('messages.sub_category_added_successfully'));
        }

        return back();
    }

    public function create()
    {
        $taxData = Helpers::getTaxSystemType();
        $categoryWiseTax = $taxData['categoryWiseTax'];
        $taxVats = $taxData['taxVats'];
        $language = getWebConfig('language');

        return response()->json([
            'view' => view('admin-views.category.partials._create', compact('taxVats', 'categoryWiseTax', 'language'))->render(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        $category = Category::withoutGlobalScope('translate')->findOrFail($id);
        $taxData = Helpers::getTaxSystemType();
        $categoryWiseTax = $taxData['categoryWiseTax'];
        $taxVats = $taxData['taxVats'];
        $taxVatIds = $categoryWiseTax ? $category->taxVats()->pluck('tax_id')->toArray() : [];
        $language = getWebConfig('language');
        $model = $request->model;

        return response()->json([
            'view' => view('admin-views.category.partials._edit', compact('category', 'taxVats', 'categoryWiseTax', 'language', 'taxVatIds', 'model'))->render(),
        ]);
    }

    public function status(Request $request)
    {
        $category = Category::find($request->id);
        $category->status = $request->status;
        $category->save();
        if ($category->parent_id == 0) {
            Toastr::success(translate('messages.category_status_updated'));
        } else {
            Toastr::success(translate('messages.sub_category_status_updated'));
        }
        return back();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:100|unique:categories,name,' . $id,
            'image' => 'nullable|max:2048',
            'name.0' => 'required',
            'meta_image' => 'nullable|max:2048',
            'priority' => 'nullable|integer|in:0,1,2',
        ], [
            'name.0.required' => translate('default_name_is_required'),
            'image' => translate('image_must_be_less_than_2mb'),
            'meta_image' => translate('meta_image_must_be_less_than_2mb'),
        ]);

        $category = Category::find($id);
        $slug = Str::slug($request->name[array_search('default', $request->lang)]);
        $category->slug = $category->slug ? $category->slug : "{$slug}{$category->id}";
        $category->name = $request->name[array_search('default', $request->lang)];
        $category->image = $request->has('image') ? Helpers::update(dir: 'category/', old_image: $category->image, format: 'png', image: $request->file('image')) : $category->image;
        $category->priority = $request->priority ?? 0;
        if ($category->position == 0) {
            $category->meta_title = $request->meta_title[array_search('default', $request->lang)];
            $category->meta_description = $request->meta_description[array_search('default', $request->lang)];
            if ($request->input('meta_image_remove') == "1") {
                Helpers::check_and_delete('meta_image/', $category->meta_image);
                $category->meta_image = null;
            }
            $category->meta_image = $request->file('meta_image') ? Helpers::upload(dir: 'meta_image/', format: 'png', image: $request->file('meta_image')) : $category->meta_image;
            $category->meta_data = (Helpers::formatMetaData($request->all(), $category->meta_data));
        }
        $category->save();

        Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'Category', data_id: $category->id, data_value: $category->name);
        // Helpers::add_or_update_translations(request: $request, key_data: 'meta_description', name_field: 'meta_description', model_name: 'Category', data_id: $category->id, data_value: $category->meta_description);

        if ($category->parent_id == 0) {
            if (addon_published_status('TaxModule') && $category['position'] == 0) {
                $taxVatIds = $category->taxVats()->pluck('tax_id')->toArray() ?? [];
                $newTaxVatIds = array_map('intval', $request['tax_ids'] ?? []);
                sort($newTaxVatIds);
                sort($taxVatIds);
                if ($newTaxVatIds != $taxVatIds) {
                    $category->taxVats()->delete();
                    $SystemTaxVat = \Modules\TaxModule\Entities\SystemTaxSetup::where('is_active', 1)->where('is_default', 1)->first();
                    if ($SystemTaxVat?->tax_type == 'category_wise') {
                        foreach ($request['tax_ids'] ?? [] as $tax_ids) {
                            \Modules\TaxModule\Entities\Taxable::create(
                                [
                                    'taxable_type' => Category::class,
                                    'taxable_id' => $category->id,
                                    'system_tax_setup_id' => $SystemTaxVat->id
                                    , 'tax_id' => $tax_ids
                                ],
                            );
                        }

                    }
                }
            }

            Toastr::success(translate('messages.category_updated_successfully'));
        } else {
            Toastr::success(translate('messages.sub_category_updated_successfully'));
        }
        return back();
    }

    public function delete(Request $request)
    {
        $category = Category::findOrFail($request->id);
        if ($category?->childes?->count() == 0) {
            $category?->translations()?->delete();
            $category?->taxVats()->delete();
            $category->delete();
            if ($category->parent_id == 0) {
                Toastr::success(translate('messages.Category removed!'));
            } else {
                Toastr::success(translate('messages.Sub Category removed!'));
            }
        } else {
            Toastr::warning(translate('messages.remove_sub_categories_first'));
        }
        return back();
    }

    public function get_all(Request $request)
    {
        $data = Category::where('name', 'like', '%' . $request->q . '%')->limit(8)->get()
            ->map(function ($category) {
                $data = $category->position == 0 ? translate('messages.main') : translate('messages.sub');
                return [
                    'id' => $category->id,
                    'text' => $category->name . ' (' . $data . ')',
                ];
            });

        $data[] = (object)['id' => 'all', 'text' => 'All'];

        return response()->json($data);
    }

    public function update_priority(Category $category, Request $request)
    {
        $priority = $request->priority ?? 0;
        $category->priority = $priority;
        $category->save();
        Toastr::success(translate('messages.category_priority_updated successfully'));
        return back();

    }

    public function bulk_import_index()
    {
        return view('admin-views.category.bulk-import');
    }

    public function bulk_import_data(Request $request)
    {
        $request->validate([
            'upload_excel' => 'required|max:2048'
        ], [
            'upload_excel.required' => translate('File is empty. Please upload a valid file.'),
            'upload_excel.max' => translate('messages.Max_file_size_is_2mb'),
        ]);

        try {
            $collections = (new FastExcel)->import($request->file('upload_excel'));
        } catch (\Exception $exception) {
            info([$exception->getFile(), $exception->getLine(), $exception->getMessage()]);
            Toastr::error(translate('Invalid file format. Please upload the file in the correct format.'));
            return back();
        }

        if (empty($collections) || count($collections) === 0) {
            Toastr::error(translate('File is empty. Please upload a valid file.'));
            return back();
        }



        $existingNames = DB::table('categories')->pluck('name')->map(fn($n) => strtolower($n))->toArray();

        if ($request->button == 'import') {
            try {
                $data = [];

                foreach ($collections as $collection) {
                    if (!isset($collection['Name']) || $collection['Name'] === "" || !isset($collection['Image']) || !isset($collection['Position'])) {
                        Toastr::error(translate('Invalid file format. Please upload the file in the correct format.'));
                        return back();
                    }

                    if (in_array(strtolower($collection['Name']), $existingNames)) {
                        continue;
                    }

                    $parent_id = is_numeric($collection['ParentId']) ? $collection['ParentId'] : 0;
                    $data[] = [
                        'name' => $collection['Name'],
                        'image' => $collection['Image'],
                        'parent_id' => $parent_id,
                        'position' => $collection['Position'],
                        'priority' => is_numeric($collection['Priority']) ? $collection['Priority'] : 0,
                        'status' => $collection['Status'] == 'active' ? 1 : 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];

                    $existingNames[] = strtolower($collection['Name']);
                }

                DB::beginTransaction();

                $chunkSize = 100;
                $chunk_categories = array_chunk($data, $chunkSize);

                foreach ($chunk_categories as $chunk_category) {
                    foreach ($chunk_category as $category) {
                        $insertedId = DB::table('categories')->insertGetId($category);
                        Helpers::updateStorageTable(get_class(new Category), $insertedId, $category['image']);
                    }
                }

                DB::commit();
                Toastr::success(translate('messages.category_imported_successfully', ['count' => count($data)]));
                return back();

            } catch (\Exception $exception) {
                DB::rollBack();
                info([$exception->getFile(), $exception->getLine(), $exception->getMessage()]);
                Toastr::error(translate('messages.failed_to_import_data'));
                return back();
            }
        }

        try {
            $data = [];

            foreach ($collections as $collection) {
                if (!isset($collection['Name']) || $collection['Name'] === "" || !isset($collection['Id']) || !isset($collection['Image']) || !isset($collection['Position'])) {
                    Toastr::error(translate('Invalid file format. Please upload the file in the correct format.'));
                    return back();
                }

                $parent_id = is_numeric($collection['ParentId']) ? $collection['ParentId'] : 0;

                $categoryExists = DB::table('categories')
                    ->whereRaw('LOWER(name) = ?', [strtolower($collection['Name'])])
                    ->when(isset($collection['Id']), function ($q) use ($collection) {
                        return $q->where('id', '<>', $collection['Id']);
                    })
                    ->exists();

                if ($categoryExists) {
                    continue;
                }

                $data[] = [
                    'id' => $collection['Id'] ?? null,
                    'name' => $collection['Name'],
                    'image' => $collection['Image'],
                    'parent_id' => $parent_id,
                    'position' => $collection['Position'],
                    'priority' => is_numeric($collection['Priority']) ? $collection['Priority'] : 0,
                    'status' => $collection['Status'] == 'active' ? 1 : 0,
                    'updated_at' => now()
                ];

                $existingNames[] = strtolower($collection['Name']);
            }

            DB::beginTransaction();

            $chunkSize = 100;
            $chunk_categories = array_chunk($data, $chunkSize);

            foreach ($chunk_categories as $chunk_category) {
                foreach ($chunk_category as $category) {
                    if (isset($category['id']) && DB::table('categories')->where('id', $category['id'])->exists()) {
                        DB::table('categories')->where('id', $category['id'])->update($category);
                        Helpers::updateStorageTable(get_class(new Category), $category['id'], $category['image']);
                    } else {
                        $insertedId = DB::table('categories')->insertGetId($category);
                        Helpers::updateStorageTable(get_class(new Category), $insertedId, $category['image']);
                    }
                }
            }

            DB::commit();
            Toastr::success(translate('messages.category_updated_successfully', ['count' => count($data)]));
            return back();

        } catch (\Exception $e) {
            DB::rollBack();
            info(["line___{$e->getLine()}", $e->getMessage()]);
            Toastr::error(translate('messages.failed_to_import_data'));
            return back();
        }
    }

    public function bulk_export_index()
    {
        return view('admin-views.category.bulk-export');
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
        $query = Category::when($request['type'] == 'date_wise', function ($query) use ($request) {
            $query->whereBetween('created_at', [$request['from_date'] . ' 00:00:00', $request['to_date'] . ' 23:59:59']);
        })
            ->when($request['type'] == 'id_wise', function ($query) use ($request) {
                $query->whereBetween('id', [$request['start_id'], $request['end_id']]);
            });

        if (!$query->exists()) {
            if($request['type'] == 'date_wise') {
                Toastr::error(translate('messages.there_are_no_categories_in_between_these_dates'));
            } elseif($request['type'] == 'id_wise') {
                Toastr::error(translate('Invalid ID. No file generated'));
            } else
            {
                Toastr::error(translate('messages.no_data_to_export'));
            }
            return back();
        }

            $categories = $query->get();
        return (new FastExcel(CategoryLogic::export_categories(Helpers::Export_generator($categories))))->download('Categories.xlsx');
    }


    public function export_categories(Request $request)
    {
        try {

            $relationships = [
                'translations' => 'value',
            ];
            $taxData = Helpers::getTaxSystemType();
            $priority = $request['priority'];
            $categoryWiseTax = $taxData['categoryWiseTax'];
            $categories = Category::where('position', '0')
               ->search(keywords:request()?->search, mainCol: ['name','id'], relations: $relationships)
                ->when(isset($priority), function ($q) use ($priority) {
                    $q->where('priority', $priority);
                })
                ->with($categoryWiseTax ? ['taxVats.tax'] : [])
                ->orderBy('id', 'desc')->get();
            $data = [
                'data' => $categories,
                'search' => $request['search'] ?? null,
                'categoryWiseTax' => $categoryWiseTax
            ];
            if ($request->type == 'csv') {
                return Excel::download(new CategoryExport($data), 'Categories.csv');
            }
            return Excel::download(new CategoryExport($data), 'Categories.xlsx');
        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);
            return back();
        }
    }

    public function export_sub_categories(Request $request)
    {
        try {
            $relationships = [
                'translations' => 'value',
            ];

            $query = Category::with(['parent'])->where('position', 1)->search(keywords:request()?->search, mainCol: ['name','id'], relations: $relationships);

            if ($request->has('status') && count($request->status) > 0) {
                $query->whereIn('status', $request->status);
            }

            if ($request->priority != "") {
                $query->where('priority', $request->priority);
            }

            if ($request->category != "") {
                $query->where('parent_id', $request->category);
            }

            $categories = $query->orderBy('id', 'desc')->get();

            $data = [
                'data' => $categories,
                'search' => $request['search'] ?? null,
                'filters' => [
                    'status' => $request->status ?? [],
                    'priority' => $request->priority ?? [],
                    'category' => $request->category ?? []
                ]
            ];

            if ($request->type == 'csv') {
                return Excel::download(new CategoryExport($data), 'SubCategories.csv');
            }
            return Excel::download(new CategoryExport($data), 'SubCategories.xlsx');
        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);
            return back();
        }
    }
}
