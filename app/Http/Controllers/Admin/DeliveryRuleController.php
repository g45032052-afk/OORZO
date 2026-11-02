<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Exports\DeliveryRuleExport;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\DeliveryRule;
use App\Models\DeliveryRuleCharge;
use App\Models\ZipCode;
use App\Models\Zone;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class DeliveryRuleController extends Controller
{
    /**
     * The form field each tabular method posts its rows under.
     *
     * Both panes are rendered and only one is hidden, so both sets of inputs are
     * submitted. Keeping them apart is what stops a blank row of the unused
     * method from being read as a blank row of the chosen one.
     */
    private const CHARGE_FIELDS = [
        DeliveryRule::METHOD_AREA => 'area_charges',
        DeliveryRule::METHOD_ZIP => 'zip_charges',
    ];

    public function list(Request $request)
    {
        $rules = DeliveryRule::with('zone')
            ->when($request['search'], function ($query) use ($request) {
                $query->where('name', 'like', "%{$request['search']}%");
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('admin-views.delivery-rule.list', compact('rules'));
    }

    public function create(Request $request)
    {
        // Inactive zones are listed too: a zone cannot be switched on until it
        // has a rule, so leaving them out would make that impossible.
        $zones = Zone::orderBy('name')->get();

        // A zone may be named on the way in — the zone list sends the admin
        // here the moment a zone is created — so the form opens on that one.
        $selectedZone = $zones->firstWhere('id', (int) $request->query('zone_id')) ?: $zones->first();

        return view('admin-views.delivery-rule.create', [
            'zones' => $zones,
            'selectedZoneId' => $selectedZone?->id,
            'areas' => $selectedZone ? Area::active()->where('zone_id', $selectedZone->id)->orderBy('name')->get() : collect(),
            'zipCodes' => $selectedZone ? ZipCode::active()->where('zone_id', $selectedZone->id)->orderBy('zip_code')->get() : collect(),
        ]);
    }

    /**
     * The charge rows depend on the chosen zone, so the form refetches them
     * whenever the zone changes rather than shipping every zone's areas upfront.
     */
    public function zoneCoverage(Request $request, $zoneId)
    {
        return response()->json([
            'areas' => Area::active()->where('zone_id', $zoneId)->orderBy('name')->get(['id', 'name']),
            'zip_codes' => ZipCode::active()->where('zone_id', $zoneId)->orderBy('zip_code')->get(['id', 'zip_code']),
        ]);
    }

    /**
     * The zone's other rules, as replacement options for the turn off prompt.
     *
     * They are fetched rather than read off the page: the list is paginated and
     * the detail page holds one rule, so the markup never has the full set.
     */
    public function zoneRules(Request $request, $zoneId)
    {
        return response()->json([
            'rules' => DeliveryRule::where('zone_id', $zoneId)
                ->when($request->exclude, function ($query) use ($request) {
                    $query->where('id', '!=', $request->exclude);
                })
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validator = $this->validateRule($request);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        DB::beginTransaction();

        try {
            $rule = new DeliveryRule();
            $this->fillRule($rule, $request);

            // A zone's first rule has nothing to displace, so it goes live at
            // once; later rules are created switched off, as the design states.
            $rule->status = DeliveryRule::where('zone_id', $request->zone_id)->exists() ? 0 : 1;
            $rule->save();

            $this->syncCharges($rule, $request);

            DB::commit();

            return response()->json([
                'success' => translate('messages.delivery_rule_created_successfully'),
                'redirect' => route('admin.delivery-rule.show', $rule->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return response()->json(['errors' => [['code' => 'server', 'message' => $e->getMessage()]]], 500);
        }
    }

    public function show($id)
    {
        $rule = DeliveryRule::with(['zone', 'charges.area', 'charges.zipCode'])->findOrFail($id);

        return view('admin-views.delivery-rule.show', compact('rule'));
    }

    public function edit($id)
    {
        $rule = DeliveryRule::with('charges')->findOrFail($id);
        $zones = Zone::orderBy('name')->get();

        return view('admin-views.delivery-rule.edit', [
            'rule' => $rule,
            'zones' => $zones,
            'areas' => Area::active()->where('zone_id', $rule->zone_id)->orderBy('name')->get(),
            'zipCodes' => ZipCode::active()->where('zone_id', $rule->zone_id)->orderBy('zip_code')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = $this->validateRule($request);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        DB::beginTransaction();

        try {
            $rule = DeliveryRule::findOrFail($id);
            $this->fillRule($rule, $request);
            $rule->save();

            $this->syncCharges($rule, $request);

            DB::commit();

            return response()->json([
                'success' => translate('messages.delivery_rule_updated_successfully'),
                'redirect' => route('admin.delivery-rule.show', $rule->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return response()->json(['errors' => [['code' => 'server', 'message' => $e->getMessage()]]], 500);
        }
    }

    /**
     * Switching a rule on takes the zone's active rule off with it; switching one
     * off requires naming its replacement, so a zone is never left without a way
     * to price delivery.
     */
    public function status(Request $request, $id)
    {
        $rule = DeliveryRule::findOrFail($id);
        $turningOn = (int) $request->status === 1;

        DB::beginTransaction();

        try {
            if ($turningOn) {
                DeliveryRule::where('zone_id', $rule->zone_id)
                    ->where('id', '!=', $rule->id)
                    ->update(['status' => 0]);

                $rule->status = 1;
                $rule->save();

                $message = translate('messages.delivery_rule_activated');
            } else {
                $replacement = DeliveryRule::where('zone_id', $rule->zone_id)
                    ->where('id', '!=', $rule->id)
                    ->find($request->replacement_id);

                if (! $replacement) {
                    DB::rollBack();

                    return response()->json([
                        'errors' => [['code' => 'replacement_id', 'message' => translate('messages.please_select_another_rule_for_this_zone')]],
                    ], 422);
                }

                DeliveryRule::where('zone_id', $rule->zone_id)->update(['status' => 0]);
                $replacement->status = 1;
                $replacement->save();

                $message = translate('messages.delivery_rule_switched');
            }

            DB::commit();

            return response()->json(['success' => $message]);
        } catch (\Exception $e) {
            DB::rollBack();
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return response()->json(['errors' => [['code' => 'server', 'message' => $e->getMessage()]]], 500);
        }
    }

    /** Only an inactive rule may be removed — the list hides the button otherwise. */
    public function destroy(Request $request, $id)
    {
        $rule = DeliveryRule::findOrFail($id);

        if ($rule->status) {
            Toastr::error(translate('messages.an_active_delivery_rule_cannot_be_deleted'));

            return back();
        }

        $rule->charges()->delete();
        $rule->delete();

        Toastr::success(translate('messages.delivery_rule_deleted_successfully'));

        return redirect()->route('admin.delivery-rule.list');
    }

    public function export(Request $request, $type)
    {
        try {
            $rules = DeliveryRule::with('zone')
                ->when($request['search'], function ($query) use ($request) {
                    $query->where('name', 'like', "%{$request['search']}%");
                })
                ->latest()
                ->get();

            $data = ['data' => $rules, 'search' => $request['search'] ?? null];

            if ($type == 'csv') {
                return Excel::download(new DeliveryRuleExport($data), 'DeliveryRule.csv');
            }

            return Excel::download(new DeliveryRuleExport($data), 'DeliveryRule.xlsx');
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

    private function validateRule(Request $request)
    {
        $method = $request->pricing_method;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'zone_id' => 'required|exists:zones,id',
            'minimum_delivery_charge' => 'required|numeric|min:0',
            'pricing_method' => 'required|in:' . implode(',', DeliveryRule::METHODS),

            'per_km_charge' => 'required_if:pricing_method,' . DeliveryRule::METHOD_DISTANCE . '|nullable|numeric|min:0',
            'maximum_delivery_charge' => 'required_if:pricing_method,' . DeliveryRule::METHOD_DISTANCE . '|nullable|numeric|min:0',
            'fixed_charge' => 'required_if:pricing_method,' . DeliveryRule::METHOD_FIXED . '|nullable|numeric|min:0',

            // Which of the two carries meaning follows pricing_method; the rows of
            // the unused one are ignored below rather than validated away.
            'area_charges' => 'array',
            'area_charges.*' => 'nullable|numeric|min:0',
            'zip_charges' => 'array',
            'zip_charges.*' => 'nullable|numeric|min:0',
        ], [
            'name.required' => translate('messages.rule_name_is_required'),
            'zone_id.required' => translate('messages.please_select_a_zone'),
            'minimum_delivery_charge.required' => translate('messages.minimum_delivery_charge_is_required'),
            'per_km_charge.required_if' => translate('messages.per_km_delivery_charge_is_required'),
            'maximum_delivery_charge.required_if' => translate('messages.maximum_delivery_charge_is_required'),
            'fixed_charge.required_if' => translate('messages.delivery_charge_is_required'),
            'charges.required_if' => translate('messages.set_a_delivery_charge_for_every_row'),
        ]);

        $validator->after(function ($validator) use ($request, $method) {
            // A maximum below the per-km rate would cap every order at the floor.
            if ($method === DeliveryRule::METHOD_DISTANCE
                && is_numeric($request->maximum_delivery_charge)
                && is_numeric($request->minimum_delivery_charge)
                && (float) $request->maximum_delivery_charge < (float) $request->minimum_delivery_charge) {
                $validator->errors()->add('maximum_delivery_charge', translate('messages.maximum_delivery_charge_cannot_be_less_than_the_minimum'));
            }

            // Every listed area or code needs an amount; a blank row would price
            // that area at zero without the admin realising.
            if (in_array($method, DeliveryRule::TABULAR_METHODS, true)) {
                $field = self::CHARGE_FIELDS[$method];
                $charges = $this->chargeRows($request);

                if (empty($charges)) {
                    $validator->errors()->add($field, translate('messages.no_coverage_has_been_created_for_this_zone'));

                    return;
                }

                foreach ($charges as $key => $value) {
                    if ($value === null || $value === '') {
                        $validator->errors()->add($field, translate('messages.set_a_delivery_charge_for_every_row'));
                        break;
                    }
                }
            }
        });

        return $validator;
    }

    private function fillRule(DeliveryRule $rule, Request $request): void
    {
        $method = $request->pricing_method;

        $rule->zone_id = $request->zone_id;
        $rule->name = $request->name;
        $rule->minimum_delivery_charge = $request->minimum_delivery_charge;
        $rule->pricing_method = $method;

        // Clear the columns the other methods own, so switching a rule's method
        // never leaves a stale figure behind.
        $rule->per_km_charge = $method === DeliveryRule::METHOD_DISTANCE ? $request->per_km_charge : null;
        $rule->maximum_delivery_charge = $method === DeliveryRule::METHOD_DISTANCE ? $request->maximum_delivery_charge : null;
        $rule->fixed_charge = $method === DeliveryRule::METHOD_FIXED ? $request->fixed_charge : null;
    }

    /** The posted rows of the chosen method, empty for the methods that have none. */
    private function chargeRows(Request $request): array
    {
        $field = self::CHARGE_FIELDS[$request->pricing_method] ?? null;

        return $field ? (array) $request->input($field, []) : [];
    }

    private function syncCharges(DeliveryRule $rule, Request $request): void
    {
        $rule->charges()->delete();

        if (! in_array($request->pricing_method, DeliveryRule::TABULAR_METHODS, true)) {
            return;
        }

        $isArea = $request->pricing_method === DeliveryRule::METHOD_AREA;
        $rows = [];

        foreach ($this->chargeRows($request) as $targetId => $charge) {
            $rows[] = [
                'delivery_rule_id' => $rule->id,
                'area_id' => $isArea ? (int) $targetId : null,
                'zip_code_id' => $isArea ? null : (int) $targetId,
                'charge' => (float) $charge,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DeliveryRuleCharge::insert($chunk);
        }
    }
}
