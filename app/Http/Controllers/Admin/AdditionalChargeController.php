<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Exports\AdditionalChargeExport;
use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\ZoneDeliveryOption;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

/**
 * The Additional Delivery Charge screens.
 *
 * The charges themselves are the saver delivery options the zone already owns —
 * zone_delivery_options rows plus the zone's additional_delivery_option_status
 * flag — which Zone > Settings has always edited inline. This controller is the
 * standalone screen for the same data, so a zone holds exactly one setup: the
 * express and slightly-delayed pair.
 *
 * The one thing that differs from the zone settings page: the floor a reduce
 * charge may not cross now comes from the zone's active delivery rule rather
 * than the zone's own minimum_delivery_charge field.
 *
 * One setup per zone is a settled product decision (19 Aug 2026, QA AC TC_32), not an
 * accident of the routing. There is deliberately no "turn on a second setup and the first
 * turns off" behaviour: the routes are keyed by {zone}, a zone resolves to exactly one row
 * set, and the list banner says so. A test asserting sibling deactivation is asserting a
 * model this module does not have.
 */
class AdditionalChargeController extends Controller
{
    /** The rows a setup is made of; 'standard' is written too so the customer's picker stays complete. */
    private const TYPES = ['standard', 'express', 'slightly_delay'];

    /** Largest value zone_delivery_options.extra_charge / reduce_charge - decimal(10,4) - can hold. */
    private const MAX_CHARGE = 999999.9999;

    public function list(Request $request)
    {
        $zones = $this->configuredZones()
            ->when($request['search'], function ($query) use ($request) {
                $search = $request['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(config('default_pagination'));

        return view('admin-views.additional-charge.list', compact('zones'));
    }

    /**
     * Every zone is offered, including the ones already set up — a zone holds one
     * setup, so choosing a configured zone loads its amounts and saving replaces
     * them. Hiding those zones only made them look missing.
     *
     * Switched-off zones included, for the same reason: a zone is deactivated when it has no
     * delivery or ETA setup, so a status filter here hid the very zones an admin opens this
     * screen to configure.
     */
    public function create()
    {
        $zones = Zone::with('deliveryOptions')->orderBy('name')->get();

        // Nothing is prefilled, because nothing is chosen yet. The form opens with the zone
        // select on its placeholder, so filling the amounts in from $zones->first() showed
        // one zone's charges under a dropdown that named no zone at all - the admin read
        // them as a new setup carrying old data, and a save would have written them back to
        // whichever zone was picked afterwards. Everything below is populated by the
        // zone-minimum call the moment a zone is actually chosen; see zoneMinimum().
        return view('admin-views.additional-charge.create', [
            'zones' => $zones,
            'zone' => null,
            'express' => null,
            'delayed' => null,
            'minimumCharge' => null,
            'minimumMinutes' => 0,
        ]);
    }

    /**
     * The floor a reduce charge may not cross comes from the zone's active
     * delivery rule, so the form refetches it whenever the zone changes.
     */
    public function zoneMinimum(Request $request, $zoneId)
    {
        $minimum = $this->zoneMinimumCharge($zoneId);

        // The note reads the formatted figure, and currency formatting lives on
        // the server, so it is sent along rather than rebuilt in the browser.
        //
        // Nothing about the zone's existing setup is sent. The form neither fills its
        // fields from one nor warns about one: a zone that already has a setup is refused
        // by store(), which is the only moment the answer can be trusted anyway.
        return response()->json([
            'minimum_delivery_charge' => $minimum,
            'formatted' => $minimum === null ? null : Helpers::format_currency($minimum),
            'minimum_delivery_time' => $this->zoneMinimumMinutes($zoneId),
        ]);
    }

    public function store(Request $request)
    {
        $validator = $this->validateCharge($request);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        // A zone holds one setup, so a second one is refused rather than quietly replacing
        // the first - the same answer a surge price gets when it collides with an existing
        // one. 409 is what the form listens for to raise its modal; the amounts of a setup
        // that already exists are changed on the Edit screen.
        //
        // configuredZones() decides it, so "already has one" means exactly what the list
        // means by it and the two can never disagree.
        if ($this->configuredZones()->whereKey($request->zone_id)->exists()) {
            return response()->json([
                'errors' => [[
                    'code' => 'duplicate',
                    'message' => translate('messages.this_zone_already_has_an_additional_charge_setup'),
                ]],
            ], 409);
        }

        DB::beginTransaction();

        try {
            $zone = Zone::findOrFail($request->zone_id);
            $this->syncOptions($zone, $request);

            // A setup is only worth creating to be offered, so it goes live at once.
            $zone->additional_delivery_option_status = true;
            $zone->save();

            DB::commit();

            return response()->json([
                'success' => translate('messages.additional_charge_created_successfully'),
                'redirect' => route('admin.additional-charge.list'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return response()->json(['errors' => [['code' => 'server', 'message' => $e->getMessage()]]], 500);
        }
    }

    public function edit($zoneId)
    {
        $zone = Zone::with('deliveryOptions')->findOrFail($zoneId);

        return view('admin-views.additional-charge.edit', [
            'zone' => $zone,
            'express' => $zone->deliveryOptions->firstWhere('delivery_type', 'express'),
            'delayed' => $zone->deliveryOptions->firstWhere('delivery_type', 'slightly_delay'),
            'minimumCharge' => $this->zoneMinimumCharge($zone->id),
            'minimumMinutes' => $this->zoneMinimumMinutes($zone->id),
        ]);
    }

    /** The zone cannot be moved once chosen, so the form posts amounts only. */
    public function update(Request $request, $zoneId)
    {
        $request->merge(['zone_id' => $zoneId]);

        $validator = $this->validateCharge($request);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        DB::beginTransaction();

        try {
            $zone = Zone::findOrFail($zoneId);
            $this->syncOptions($zone, $request);

            DB::commit();

            return response()->json([
                'success' => translate('messages.additional_charge_updated_successfully'),
                'redirect' => route('admin.additional-charge.list'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return response()->json(['errors' => [['code' => 'server', 'message' => $e->getMessage()]]], 500);
        }
    }

    /**
     * The same flag Zone > Settings toggles: with it off the zone offers standard
     * delivery only, and the amounts are kept for whenever it is switched back on.
     */
    public function status(Request $request, $zoneId)
    {
        $zone = Zone::findOrFail($zoneId);
        $turningOn = (int) $request->status === 1;

        $zone->additional_delivery_option_status = $turningOn;
        $zone->save();

        return response()->json([
            'success' => $turningOn
                ? translate('messages.additional_charge_activated')
                : translate('messages.additional_charge_deactivated'),
        ]);
    }

    public function destroy(Request $request, $zoneId)
    {
        $zone = Zone::findOrFail($zoneId);

        DB::transaction(function () use ($zone) {
            $zone->deliveryOptions()->delete();

            // Nothing left to offer, so the zone must not advertise the options.
            $zone->additional_delivery_option_status = false;
            $zone->save();
        });

        Toastr::success(translate('messages.additional_charge_deleted_successfully'));

        return redirect()->route('admin.additional-charge.list');
    }

    public function export(Request $request, $type)
    {
        try {
            $zones = $this->configuredZones()
                ->when($request['search'], function ($query) use ($request) {
                    $search = $request['search'];
                    $query->where(function ($q) use ($search) {
                        $q->where('id', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
                })
                ->orderByDesc('id')
                ->get();

            $data = ['data' => $zones, 'search' => $request['search'] ?? null];

            if ($type == 'csv') {
                return Excel::download(new AdditionalChargeExport($data), 'AdditionalCharge.csv');
            }

            return Excel::download(new AdditionalChargeExport($data), 'AdditionalCharge.xlsx');
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

    /** Zones that have an express or slightly delayed option priced. */
    private function configuredZones()
    {
        return Zone::with('deliveryOptions')
            ->whereHas('deliveryOptions', function ($query) {
                $query->whereIn('delivery_type', ['express', 'slightly_delay']);
            });
    }

    /** The floor set by the zone's active delivery rule, null when it has none. */
    private function zoneMinimumCharge($zoneId): ?float
    {
        $rule = Helpers::activeDeliveryRule($zoneId);

        return $rule ? (float) $rule->minimum_delivery_charge : null;
    }

    /**
     * The zone's delivery time floor in minutes, 0 when it has none.
     *
     * Zone reads the column back as a value/unit pair, so the raw minutes have
     * to be rebuilt rather than cast — casting the array would read as 1.
     */
    private function zoneMinimumMinutes($zoneId): int
    {
        // The ETA setup is the floor the runtime actually enforces (EtaLogic::range reads
        // eta_configurations.minimum_delivery_time), so the admin-side check has to read the
        // same column - it used to validate against zones.minimum_delivery_time, a different
        // field edited on Zone Settings, letting a reduce time through that the runtime would
        // then clamp anyway. The zone column is kept as the fallback for a zone with no ETA
        // configuration yet.
        $etaFloor = (int) (\App\Models\EtaConfiguration::where('zone_id', $zoneId)->value('minimum_delivery_time') ?? 0);

        if ($etaFloor > 0) {
            return $etaFloor;
        }

        $time = Zone::find($zoneId)?->minimum_delivery_time;

        return $this->toMinutes($time['value'] ?? 0, $time['unit'] ?? 'min');
    }

    private function validateCharge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|exists:zones,id',

            // The charge columns are decimal(10,4), so anything past this overflows and the
            // save lands in the 500 branch with a raw driver message instead of telling the
            // admin which field is out of range.
            'extra_charge' => 'required|numeric|min:0|max:'.self::MAX_CHARGE,
            'reduce_delivery_time' => 'required|integer|min:0',
            'reduce_delivery_time_unit' => 'required|in:min,hour',

            'reduce_charge' => 'required|numeric|min:0|max:'.self::MAX_CHARGE,
            'add_delivery_time' => 'required|integer|min:0',
            'add_delivery_time_unit' => 'required|in:min,hour',
        ], [
            'zone_id.required' => translate('messages.please_select_a_zone'),
            'extra_charge.required' => translate('messages.express_delivery_extra_charge_is_required'),
            'reduce_delivery_time.required' => translate('messages.express_delivery_reduce_time_is_required'),
            'reduce_charge.required' => translate('messages.delayed_delivery_reduce_charge_is_required'),
            'add_delivery_time.required' => translate('messages.delayed_delivery_extra_time_is_required'),
        ]);

        $validator->after(function ($validator) use ($request) {
            // A discount larger than the zone's floor would price a delayed
            // delivery at nothing. The floor lives on the zone's active delivery
            // rule, so a zone without one has nothing to check against yet.
            $minimum = $this->zoneMinimumCharge($request->zone_id);

            if ($minimum !== null
                && is_numeric($request->reduce_charge)
                && (float) $request->reduce_charge > $minimum) {
                $validator->errors()->add('reduce_charge', translate('messages.reduce_charge_cannot_be_greater_than_the_minimum_delivery_charge_of_this_zone'));
            }

            // The zone's own delivery time floor, as Zone > Settings enforces it:
            // an express order may never be pulled in past that limit.
            $limit = $this->zoneMinimumMinutes($request->zone_id);

            if ($limit > 0 && $this->toMinutes($request->reduce_delivery_time, $request->reduce_delivery_time_unit) > $limit) {
                $validator->errors()->add('reduce_delivery_time', translate('messages.reduce_delivery_time_cannot_be_greater_than_the_minimum_delivery_time_of_this_zone'));
            }
        });

        return $validator;
    }

    /**
     * Rewrite the zone's three option rows.
     *
     * The value/unit pairs are what ZoneDeliveryOption's mutators expect; they
     * store minutes, so the unit never has to be persisted separately.
     */
    private function syncOptions(Zone $zone, Request $request): void
    {
        $rows = [
            'standard' => [
                'extra_charge' => null,
                'reduce_charge' => null,
                'add_delivery_time' => ['value' => 0, 'unit' => 'min'],
                'reduce_delivery_time' => ['value' => 0, 'unit' => 'min'],
            ],
            'express' => [
                'extra_charge' => $request->extra_charge ?: 0,
                'reduce_charge' => null,
                'add_delivery_time' => ['value' => 0, 'unit' => 'min'],
                'reduce_delivery_time' => [
                    'value' => $request->reduce_delivery_time ?: 0,
                    'unit' => $request->reduce_delivery_time_unit ?: 'min',
                ],
            ],
            'slightly_delay' => [
                'extra_charge' => null,
                'reduce_charge' => $request->reduce_charge ?: 0,
                'add_delivery_time' => [
                    'value' => $request->add_delivery_time ?: 0,
                    'unit' => $request->add_delivery_time_unit ?: 'min',
                ],
                'reduce_delivery_time' => ['value' => 0, 'unit' => 'min'],
            ],
        ];

        foreach (self::TYPES as $type) {
            ZoneDeliveryOption::updateOrCreate(
                ['zone_id' => $zone->id, 'delivery_type' => $type],
                $rows[$type]
            );
        }
    }

    private function toMinutes($value, ?string $unit): int
    {
        return (int) $value * ($unit === 'hour' ? 60 : 1);
    }
}
