<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Exports\ZipCodeExport;
use App\Http\Controllers\Controller;
use App\Models\ZipCode;
use App\Models\Zone;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ZipCodeController extends Controller
{
    public function list(Request $request)
    {
        $zipCodes = ZipCode::with('zone')
            ->when($request['search'], function ($query) use ($request) {
                $query->where('zip_code', 'like', "%{$request['search']}%");
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('admin-views.zip-code.list', compact('zipCodes'));
    }

    /** Returns the offcanvas body, injected by the list page. */
    public function create()
    {
        // Every zone, not just the switched-on ones. A zone is switched OFF precisely when it
        // has no delivery or ETA setup - see the 2026_08_20 deactivate_zones_without_delivery
        // _or_eta_setup migration - so filtering these dropdowns by status hid exactly the
        // zones an admin comes here to set up, and an edit whose zone had since been switched
        // off lost its own selection from the list.
        $zones = Zone::orderBy('name')->get();

        return response()->json([
            'view' => view('admin-views.zip-code.partials._create', compact('zones'))->render(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = $this->validateZipCode($request);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $zipCode = new ZipCode();
        $zipCode->zone_id = $request->zone_id;
        $zipCode->zip_code = trim($request->zip_code);
        $zipCode->status = 1;
        $zipCode->save();

        return response()->json(['success' => translate('messages.zip_code_added_successfully')]);
    }

    public function edit($id)
    {
        $zipCode = ZipCode::findOrFail($id);
        // Every zone, not just the switched-on ones. A zone is switched OFF precisely when it
        // has no delivery or ETA setup - see the 2026_08_20 deactivate_zones_without_delivery
        // _or_eta_setup migration - so filtering these dropdowns by status hid exactly the
        // zones an admin comes here to set up, and an edit whose zone had since been switched
        // off lost its own selection from the list.
        $zones = Zone::orderBy('name')->get();

        return response()->json([
            'view' => view('admin-views.zip-code.partials._edit', compact('zipCode', 'zones'))->render(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = $this->validateZipCode($request, $id);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $zipCode = ZipCode::findOrFail($id);
        $zipCode->zone_id = $request->zone_id;
        $zipCode->zip_code = trim($request->zip_code);
        $zipCode->save();

        return response()->json(['success' => translate('messages.zip_code_updated_successfully')]);
    }

    public function status(Request $request, $id, $status)
    {
        $zipCode = ZipCode::findOrFail($id);
        $zipCode->status = $status;
        $zipCode->save();

        Toastr::success(translate('messages.zip_code_status_updated'));

        return back();
    }

    public function destroy(Request $request, $id)
    {
        ZipCode::findOrFail($id)->delete();

        Toastr::success(translate('messages.zip_code_deleted_successfully'));

        return back();
    }

    public function export(Request $request, $type)
    {
        try {
            $zipCodes = ZipCode::with('zone')
                    ->when($request['search'], function ($query) use ($request) {
                    $query->where('zip_code', 'like', "%{$request['search']}%");
                })
                ->latest()
                ->get();

            $data = [
                'data' => $zipCodes,
                'search' => $request['search'] ?? null,
            ];

            if ($type == 'csv') {
                return Excel::download(new ZipCodeExport($data), 'ZipCode.csv');
            }

            return Excel::download(new ZipCodeExport($data), 'ZipCode.xlsx');
        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return back();
        }
    }

    /**
     * A code has to be unique inside its zone — the same postal code cannot be
     * registered twice for one zone — but neighbouring zones may legitimately
     * share a code where their coverage overlaps.
     */
    private function validateZipCode(Request $request, $ignoreId = null)
    {
        return Validator::make($request->all(), [
            'zone_id' => 'required|exists:zones,id',
            'zip_code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9][A-Za-z0-9\- ]*$/',
                Rule::unique('zip_codes', 'zip_code')
                    ->where(fn ($q) => $q->where('zone_id', $request->zone_id))
                    ->ignore($ignoreId),
            ],
        ], [
            'zone_id.required' => translate('messages.please_select_a_zone'),
            'zip_code.required' => translate('messages.zip_code_is_required'),
            'zip_code.regex' => translate('messages.zip_code_may_only_contain_letters_numbers_spaces_and_hyphens'),
            'zip_code.unique' => translate('messages.this_zip_code_already_exists_in_the_selected_zone'),
        ]);
    }
}
