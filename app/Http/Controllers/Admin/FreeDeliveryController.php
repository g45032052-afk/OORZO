<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Exports\FreeDeliveryExport;
use App\Http\Controllers\Controller;
use App\Models\FreeDelivery;
use App\Models\Zone;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

/**
 * The Free Delivery screens.
 *
 * A zone holds one setup and no more: once a zone has been given free delivery
 * the only way to change its terms is to edit that setup, so the create form
 * offers the zones that are still free of one. Which orders ride free is the
 * setup's own business — see {@see FreeDelivery::appliesTo()} — and the cost is
 * always the admin's, as the form states.
 */
class FreeDeliveryController extends Controller
{
    public function list(Request $request)
    {
        $freeDeliveries = FreeDelivery::with('zone')
            ->when($request['search'], function ($query) use ($request) {
                $search = $request['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhereHas('zone', function ($zoneQuery) use ($search) {
                            $zoneQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('admin-views.free-delivery.list', compact('freeDeliveries'));
    }

    /**
     * Only zones without a setup are offered: a zone holds one, and a second one
     * would leave the admin with two answers to the same question.
     */
    public function create()
    {
        // Every zone is offered, including ones that already have a setup. Hiding them
        // left the admin unable to see why a zone was missing; the unique rule in
        // validateSetup() rejects a duplicate on submit and says so.
        // Every zone, not just the switched-on ones. A zone is switched OFF precisely when it
        // has no delivery or ETA setup - see the 2026_08_20 deactivate_zones_without_delivery
        // _or_eta_setup migration - so filtering these dropdowns by status hid exactly the
        // zones an admin comes here to set up, and an edit whose zone had since been switched
        // off lost its own selection from the list.
        $zones = Zone::orderBy('name')->get();

        return view('admin-views.free-delivery.create', [
            'zones' => $zones,
            'freeDelivery' => null,
        ]);
    }

    public function store(Request $request)
    {
        // Answered before the validator so the form gets a status it can tell
        // apart: 409 pops the "already exists" modal, the way a surge overlap
        // does. The unique rule below still stands as the backstop against two
        // admins saving the same zone at once.
        if ($request->zone_id && FreeDelivery::where('zone_id', $request->zone_id)->exists()) {
            return response()->json([
                'status' => false,
                'type' => 'conflict',
                'message' => translate('messages.This_zone_already_has_a_free_delivery_setup.'),
            ], 409);
        }

        $validator = $this->validateSetup($request);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        DB::beginTransaction();

        try {
            $freeDelivery = new FreeDelivery();
            $this->fill($freeDelivery, $request);

            // A setup is only worth creating to be given away, so it goes live at once.
            $freeDelivery->status = 1;
            $freeDelivery->save();

            DB::commit();

            return response()->json([
                'success' => translate('messages.free_delivery_created_successfully'),
                'redirect' => route('admin.free-delivery.list'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return response()->json(['errors' => [['code' => 'server', 'message' => $e->getMessage()]]], 500);
        }
    }

    public function edit($id)
    {
        $freeDelivery = FreeDelivery::with('zone')->findOrFail($id);

        return view('admin-views.free-delivery.edit', [
            'freeDelivery' => $freeDelivery,
            'zones' => collect([$freeDelivery->zone])->filter(),
        ]);
    }

    /** The zone cannot be moved once chosen, so the form posts the terms only. */
    public function update(Request $request, $id)
    {
        $freeDelivery = FreeDelivery::findOrFail($id);
        $request->merge(['zone_id' => $freeDelivery->zone_id]);

        $validator = $this->validateSetup($request, $freeDelivery->id);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        DB::beginTransaction();

        try {
            $this->fill($freeDelivery, $request);
            $freeDelivery->save();

            DB::commit();

            return response()->json([
                'success' => translate('messages.free_delivery_updated_successfully'),
                'redirect' => route('admin.free-delivery.list'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return response()->json(['errors' => [['code' => 'server', 'message' => $e->getMessage()]]], 500);
        }
    }

    /**
     * Switched off the zone charges for delivery again, and the terms are kept
     * for whenever it is switched back on.
     */
    public function status(Request $request, $id)
    {
        $freeDelivery = FreeDelivery::findOrFail($id);
        $turningOn = (int) $request->status === 1;

        $freeDelivery->status = $turningOn;
        $freeDelivery->save();

        return response()->json([
            'success' => $turningOn
                ? translate('messages.free_delivery_activated')
                : translate('messages.free_delivery_deactivated'),
        ]);
    }

    public function destroy($id)
    {
        FreeDelivery::findOrFail($id)->delete();

        Toastr::success(translate('messages.free_delivery_deleted_successfully'));

        return redirect()->route('admin.free-delivery.list');
    }

    public function export(Request $request, $type)
    {
        try {
            $freeDeliveries = FreeDelivery::with('zone')
                ->when($request['search'], function ($query) use ($request) {
                    $search = $request['search'];
                    $query->where(function ($q) use ($search) {
                        $q->where('id', 'like', "%{$search}%")
                            ->orWhereHas('zone', function ($zoneQuery) use ($search) {
                                $zoneQuery->where('name', 'like', "%{$search}%");
                            });
                    });
                })
                ->latest()
                ->get();

            $data = ['data' => $freeDeliveries, 'search' => $request['search'] ?? null];

            if ($type == 'csv') {
                return Excel::download(new FreeDeliveryExport($data), 'FreeDelivery.csv');
            }

            return Excel::download(new FreeDeliveryExport($data), 'FreeDelivery.xlsx');
        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return back();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Internals
    |--------------------------------------------------------------------------
    */

    private function validateSetup(Request $request, $ignoreId = null)
    {
        $uniqueZone = 'unique:free_deliveries,zone_id' . ($ignoreId ? ",{$ignoreId}" : '');

        return Validator::make($request->all(), [
            'zone_id' => "required|exists:zones,id|{$uniqueZone}",
            'type' => 'required|in:' . implode(',', FreeDelivery::TYPES),
            // The criteria type is defined by its threshold - without one it would
            // free every order and duplicate the all-restaurant type, so it is
            // demanded there and dropped for the other type.
            'minimum_order_amount' => 'required_if:type,' . FreeDelivery::TYPE_CRITERIA . '|nullable|numeric|min:0',
        ], [
            'zone_id.required' => translate('messages.please_select_a_zone'),
            'zone_id.unique' => translate('messages.This_zone_already_has_a_free_delivery_setup.'),
            'type.required' => translate('messages.please_select_a_free_delivery_type'),
            'minimum_order_amount.required_if' => translate('messages.minimum_order_amount_is_required_for_specific_criteria'),
        ]);
    }

    /** The minimum amount belongs to the criteria type alone; the other type drops it. */
    private function fill(FreeDelivery $freeDelivery, Request $request): void
    {
        $freeDelivery->zone_id = $request->zone_id;
        $freeDelivery->type = $request->type;
        $freeDelivery->minimum_order_amount = $request->type === FreeDelivery::TYPE_CRITERIA && is_numeric($request->minimum_order_amount)
            ? $request->minimum_order_amount
            : null;
    }
}
