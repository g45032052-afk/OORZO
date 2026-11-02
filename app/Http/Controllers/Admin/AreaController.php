<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Exports\AreaExport;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Zone;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class AreaController extends Controller
{
    public function list(Request $request)
    {
        $key = explode(' ', $request['search'] ?? '');

        $areas = Area::with('zone')
            ->when($request['search'], function ($query) use ($key) {
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%")
                            ->orWhere('display_name', 'like', "%{$value}%");
                    }
                });
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('admin-views.area.list', compact('areas'));
    }

    /** Returns the offcanvas body, injected by the list page. */
    public function create()
    {
        $language = getWebConfig('language');
        // Every zone, not just the switched-on ones. A zone is switched OFF precisely when it
        // has no delivery or ETA setup - see the 2026_08_20 deactivate_zones_without_delivery
        // _or_eta_setup migration - so filtering these dropdowns by status hid exactly the
        // zones an admin comes here to set up, and an edit whose zone had since been switched
        // off lost its own selection from the list.
        $zones = Zone::orderBy('name')->get();

        return response()->json([
            'view' => view('admin-views.area.partials._create', compact('language', 'zones'))->render(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = $this->validateArea($request);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        DB::beginTransaction();

        try {
            $defaultIndex = array_search('default', (array) $request->lang);

            $area = new Area();
            $area->zone_id = $request->zone_id;
            $area->name = $request->name[$defaultIndex];
            $area->display_name = $request->display_name[$defaultIndex] ?? null;
            $area->status = 1;
            $area->save();

            $this->saveTranslations($request, $area);

            DB::commit();

            return response()->json(['success' => translate('messages.area_added_successfully')]);
        } catch (\Exception $e) {
            DB::rollBack();
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return response()->json(['errors' => [['code' => 'server', 'message' => $e->getMessage()]]]);
        }
    }

    public function edit($id)
    {
        $area = Area::withoutGlobalScope('translate')->with('translations')->findOrFail($id);
        $language = getWebConfig('language');
        // Every zone, not just the switched-on ones. A zone is switched OFF precisely when it
        // has no delivery or ETA setup - see the 2026_08_20 deactivate_zones_without_delivery
        // _or_eta_setup migration - so filtering these dropdowns by status hid exactly the
        // zones an admin comes here to set up, and an edit whose zone had since been switched
        // off lost its own selection from the list.
        $zones = Zone::orderBy('name')->get();

        return response()->json([
            'view' => view('admin-views.area.partials._edit', compact('area', 'language', 'zones'))->render(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = $this->validateArea($request, $id);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        DB::beginTransaction();

        try {
            $defaultIndex = array_search('default', (array) $request->lang);

            $area = Area::findOrFail($id);
            $area->zone_id = $request->zone_id;
            $area->name = $request->name[$defaultIndex];
            $area->display_name = $request->display_name[$defaultIndex] ?? null;
            $area->save();

            $this->saveTranslations($request, $area);

            DB::commit();

            return response()->json(['success' => translate('messages.area_updated_successfully')]);
        } catch (\Exception $e) {
            DB::rollBack();
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return response()->json(['errors' => [['code' => 'server', 'message' => $e->getMessage()]]]);
        }
    }

    public function status(Request $request, $id, $status)
    {
        $area = Area::findOrFail($id);
        $area->status = $status;
        $area->save();

        Toastr::success(translate('messages.area_status_updated'));

        return back();
    }

    public function destroy(Request $request, $id)
    {
        $area = Area::findOrFail($id);
        $area->translations()->delete();
        $area->delete();

        Toastr::success(translate('messages.area_deleted_successfully'));

        return back();
    }

    public function export(Request $request, $type)
    {
        try {
            $key = explode(' ', $request['search'] ?? '');

            $areas = Area::with('zone')
                    ->when($request['search'], function ($query) use ($key) {
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('name', 'like', "%{$value}%")
                                ->orWhere('display_name', 'like', "%{$value}%");
                        }
                    });
                })
                ->latest()
                ->get();

            $data = [
                'data' => $areas,
                'search' => $request['search'] ?? null,
            ];

            if ($type == 'csv') {
                return Excel::download(new AreaExport($data), 'Area.csv');
            }

            return Excel::download(new AreaExport($data), 'Area.xlsx');
        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return back();
        }
    }

    /**
     * An area name has to be unique inside its zone — two "Banani" areas in one
     * zone would be indistinguishable in the list — but the same name may be
     * reused in a different zone.
     */
    private function validateArea(Request $request, $ignoreId = null)
    {
        return Validator::make($request->all(), [
            'zone_id' => 'required|exists:zones,id',
            'name' => 'required|array',
            'name.0' => [
                'required',
                'max:191',
                function ($attribute, $value, $fail) use ($request, $ignoreId) {
                    $exists = Area::where('zone_id', $request->zone_id)
                        ->where('name', $value)
                        ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                        ->exists();

                    if ($exists) {
                        $fail(translate('messages.this_area_name_already_exists_in_the_selected_zone'));
                    }
                },
            ],
            'display_name' => 'nullable|array',
            'display_name.*' => 'nullable|max:191',
        ], [
            'zone_id.required' => translate('messages.please_select_a_zone'),
            'name.0.required' => translate('messages.default_area_name_is_required'),
        ]);
    }

    private function saveTranslations(Request $request, Area $area): void
    {
        Helpers::add_or_update_translations($request, 'name', 'name', 'Area', $area->id, $area->name);
        Helpers::add_or_update_translations($request, 'display_name', 'display_name', 'Area', $area->id, $area->display_name);
    }
}
