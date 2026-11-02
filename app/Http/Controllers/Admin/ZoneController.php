<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Exports\ZoneExport;
use App\Http\Controllers\Controller;
use App\Models\Incentive;
use App\Models\Zone;
use App\Scopes\ZoneScope;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;

class ZoneController extends Controller
{
    public function index(Request $request)
    {
        $key = explode(' ', $request['search'] ?? null);
        $zones = Zone::withCount(['restaurants', 'deliverymen', 'deliveryRules as active_delivery_rules_count' => function ($query) {
            $query->where('status', 1);
        }, 'etaConfigurations as active_eta_count' => function ($query) {
            $query->where('status', 1);
        }])
            ->when(isset($key), function ($query) use ($key) {
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                });
            })
            ->latest()->paginate(config('default_pagination'));

        return view('admin-views.zone.index', compact('zones'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:zones|max:191',
            'display_name' => 'nullable|unique:zones|max:255',
            'coordinates' => 'required',
        ]);

        if ($request->name[array_search('default', $request->lang)] == '') {
            $validator->getMessageBag()->add('title', translate('messages.default_Business_zone_name_is_required'));

            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }
        $value = $request->coordinates;
        foreach (explode('),(', trim($value, '()')) as $index => $single_array) {
            if ($index == 0) {
                $lastcord = explode(',', $single_array);
            }
            $coords = explode(',', $single_array);
            $polygon[] = new Point($coords[0], $coords[1]);
        }
        $zone_id = Zone::all()->count() + 1;
        $polygon[] = new Point($lastcord[0], $lastcord[1]);
        $zone = new Zone;
        $zone->name = $request->name[array_search('default', $request->lang)];
        $zone->display_name = $request->display_name[array_search('default', $request->lang)];
        $zone->coordinates = new Polygon([new LineString($polygon)]);
        $zone->restaurant_wise_topic = 'zone_'.$zone_id.'_restaurant';
        $zone->customer_wise_topic = 'zone_'.$zone_id.'_customer';
        $zone->deliveryman_wise_topic = 'zone_'.$zone_id.'_delivery_man';
        $zone->per_km_shipping_charge = $request->per_km_delivery_charge ?? 0;
        $zone->minimum_shipping_charge = $request->minimum_delivery_charge ?? 0;
        $zone->maximum_shipping_charge = $request->maximum_shipping_charge ?? null;
        $zone->max_cod_order_amount = $request->max_cod_order_amount ?? null;
        // A brand new zone has no delivery rule yet, so it stays switched off
        // until one is added — the status toggle enforces the same thing.
        $zone->status = 0;
        $zone->save();

        Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'Zone', data_id: $zone->id, data_value: $zone->name);
        Helpers::add_or_update_translations(request: $request, key_data: 'display_name', name_field: 'display_name', model_name: 'Zone', data_id: $zone->id, data_value: $zone->display_name);

        $new_data = 1;
        $zones = Zone::withCount(['restaurants', 'deliverymen', 'deliveryRules as active_delivery_rules_count' => function ($query) {
            $query->where('status', 1);
        }, 'etaConfigurations as active_eta_count' => function ($query) {
            $query->where('status', 1);
        }])->latest()->paginate(config('default_pagination'));

        return response()->json([
            'view' => view('admin-views.zone.partials._table', compact('zones', 'new_data'))->render(),
            'total' => $zones->count(),
            // The list opens the new zone's settings drawer straight away, so it
            // needs to know which row was just added.
            'zone_id' => $zone->id,
        ]);
    }

    public function edit($id)
    {
        if (getEnvMode() == 'demo' && $id == 1) {
            Toastr::warning(translate('messages.you_can_not_edit_this_zone_please_add_a_new_zone_to_edit'));

            return back();
        }
        $zone = Zone::selectRaw('*,ST_AsText(ST_Centroid(`coordinates`)) as center')->withoutGlobalScope('translate')->with('translations')->findOrFail($id);
        $area = json_decode($zone->coordinates[0]->toJson(), true);

        return view('admin-views.zone.edit', compact(['zone', 'area']));
    }

    /**
     * The settings of the zone that was just created.
     *
     * The panel is opened from the zone list, so this answers with the same
     * markup {@see zone_settings()} does — only the zone is found rather than
     * named.
     */
    public function latest_zone_settings()
    {
        if (getEnvMode() == 'demo') {
            return response()->json(['errors' => [['code' => 'demo', 'message' => translate('messages.you_can_not_edit_this_zone_please_add_a_new_zone_to_edit')]]], 403);
        }

        $zone = Zone::with('incentives')->latest()->first();

        if (! $zone) {
            return response()->json(['errors' => [['code' => 'zone', 'message' => translate('messages.zone_not_found')]]], 404);
        }

        return response()->json(['view' => view('admin-views.zone.partials._settings-offcanvas', compact('zone'))->render()]);
    }

    /** The settings panel, drawn into the drawer the zone list holds open. */
    public function zone_settings($id)
    {
        if (getEnvMode() == 'demo' && $id == 1) {
            return response()->json(['errors' => [['code' => 'demo', 'message' => translate('messages.you_can_not_edit_this_zone_please_add_a_new_zone_to_edit')]]], 403);
        }

        // The counts drive the setup shortcuts in the drawer, the same way they do
        // in the list popover - see ZoneController::index().
        $zone = Zone::with('incentives')
            ->withCount(['deliveryRules as active_delivery_rules_count' => function ($query) {
                $query->where('status', 1);
            }, 'etaConfigurations as active_eta_count' => function ($query) {
                $query->where('status', 1);
            }])
            ->findOrFail($id);

        return response()->json(['view' => view('admin-views.zone.partials._settings-offcanvas', compact('zone'))->render()]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:191|unique:zones,name,'.$id,
            'display_name' => 'nullable|max:255|unique:zones,display_name,'.$id,
            'coordinates' => 'required',
        ]);

        if ($request->name[array_search('default', $request->lang)] == '') {
            Toastr::error(translate('default_Business_zone_name_is_required'));

            return back();

        }

        $value = $request->coordinates;
        foreach (explode('),(', trim($value, '()')) as $index => $single_array) {
            if ($index == 0) {
                $lastcord = explode(',', $single_array);
            }
            $coords = explode(',', $single_array);
            $polygon[] = new Point((float) $coords[0], (float) $coords[1]);
        }
        $polygon[] = new Point((float) $lastcord[0], (float) $lastcord[1]);
        $zone = Zone::findOrFail($id);
        $zone->name = $request->name[array_search('default', $request->lang)];
        $zone->display_name = $request->display_name[array_search('default', $request->lang)];
        $zone->restaurant_wise_topic = 'zone_'.$id.'_restaurant';
        $zone->customer_wise_topic = 'zone_'.$id.'_customer';
        $zone->deliveryman_wise_topic = 'zone_'.$id.'_delivery_man';
        $zone->save();

        Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'Zone', data_id: $zone->id, data_value: $zone->name);
        Helpers::add_or_update_translations(request: $request, key_data: 'display_name', name_field: 'display_name', model_name: 'Zone', data_id: $zone->id, data_value: $zone->display_name);

        try {
            $zone->coordinates = new Polygon([new LineString($polygon)]);
            $zone->save();
        } catch (\Exception $exception) {

        }

        Toastr::success(translate('messages.zone_updated_successfully'));

        return redirect()->route('admin.zone.home');
    }

    /**
     * The two settings the drawer holds: the zone's COD ceiling and its
     * deliverymen's incentive.
     *
     * Delivery charges, saver delivery options and free delivery each have a
     * screen of their own now, so nothing else on the zone is written here —
     * whatever those screens saved must survive a save from this drawer.
     */
    public function zone_settings_update(Request $request, $id)
    {
        $zone = Zone::with('incentives')->findOrFail($id);

        $codOn = $request->boolean('max_cod_order_amount_status');
        $incentiveOn = $request->boolean('incentive_status');

        // The tier the drawer edits: a zone may hold several, and only the
        // first is shown, so the rest are neither read nor written.
        $incentive = $zone->incentives->first();

        $validator = Validator::make($request->all(), [
            'max_cod_order_amount' => ($codOn ? 'required|' : 'nullable|').'numeric|between:0.001,999999999999.99',
            'earning' => [
                $incentiveOn ? 'required' : 'nullable',
                'numeric',
                'between:1,999999999999.99',
                Rule::unique('incentives', 'earning')->where('zone_id', $zone->id)->ignore($incentive?->id),
            ],
            'incentive' => ($incentiveOn ? 'required|' : 'nullable|').'numeric|between:1,999999999999.99',
        ], [
            'max_cod_order_amount.required' => translate('messages.max_cod_order_amount_is_required'),
            'earning.required' => translate('messages.daily_earning_target_is_required'),
            'earning.unique' => translate('This_earning_amount_already_exists'),
            'incentive.required' => translate('messages.incentive_amount_is_required'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        try {
            \DB::transaction(function () use ($zone, $request, $codOn, $incentiveOn, $incentive) {
                // Switched off means no ceiling at all, which is what an empty
                // column has always meant to the order screens.
                $zone->max_cod_order_amount = $codOn ? $request->max_cod_order_amount : null;

                // Switched off only stops the payouts: the tiers stay as they
                // are, so switching back on restores what was being given.
                $zone->incentive_status = $incentiveOn;
                $zone->save();

                if ($incentiveOn) {
                    $tier = $incentive ?: new Incentive(['zone_id' => $zone->id]);
                    $tier->earning = $request->earning;
                    $tier->incentive = $request->incentive;
                    $tier->save();
                }
            });
        } catch (\Exception $e) {
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return response()->json(['errors' => [['code' => 'server', 'message' => $e->getMessage()]]], 500);
        }

        return response()->json(['success' => translate('messages.zone_settings_updated_successfully')]);
    }

    public function destroy(Zone $zone)
    {
        if (getEnvMode() == 'demo' && $zone->id == 1) {
            Toastr::warning(translate('messages.you_can_not_delete_this_zone_please_add_a_new_zone_to_delete'));

            return back();
        }
        $zone->delete();
        Toastr::success(translate('messages.zone_deleted_successfully'));

        return back();
    }

    public function status(Request $request)
    {
        if (getEnvMode() == 'demo' && $request->id == 1) {
            Toastr::warning('Sorry!You can not inactive this zone!');

            return back();
        }
        $zone = Zone::findOrFail($request->id);
        if ($zone->is_default && $request->status == 0) {
            Toastr::warning('Sorry! This zone is set as default.You can not inactive this zone!');

            return back();
        }

        // A zone can only serve customers once it can both price a delivery and
        // quote a time for it, so it must not be switched on without an active
        // delivery rule and an active ETA configuration. The list disables the
        // toggle too; this is the guard for a direct hit on the URL.
        if ($request->status == 1 && $gaps = $zone->readinessGaps()) {
            Toastr::warning(translate($zone->readinessMessageKey($gaps)));

            return back();
        }

        $zone->status = $request->status;
        $zone->save();
        Toastr::success(translate('messages.zone_status_updated'));

        return back();
    }

    public function defaultStatus(Request $request)
    {
        $zone = Zone::findOrFail($request->id);

        // Making a zone default switches it on, so it needs the same guard as the
        // status toggle — and the whole guard, not half of it. Checking only the
        // delivery charge setup let "make default" put a zone with no ETA
        // configuration live, which the toggle beside it refuses to do.
        if ($gaps = $zone->readinessGaps()) {
            Toastr::warning(translate($zone->readinessMessageKey($gaps)));

            return back();
        }

        $zone->is_default = 1;
        $zone->status = 1;
        $zone->save();
        Zone::where('id', '!=', $request->id)->update(['is_default' => 0]);
        Toastr::success(translate('messages.zone_default_status_updated'));

        return back();
    }

    public function get_coordinates($id)
    {
        $zone = Zone::withoutGlobalScopes()->selectRaw('*,ST_AsText(ST_Centroid(`coordinates`)) as center')->findOrFail($id);
        $area = json_decode($zone->coordinates[0]->toJson(), true);
        $data = Helpers::format_coordiantes($area['coordinates']);
        $center = (object) ['lat' => (float) trim(explode(' ', $zone->center)[1], 'POINT()'), 'lng' => (float) trim(explode(' ', $zone->center)[0], 'POINT()')];

        return response()->json(['coordinates' => $data, 'center' => $center]);
    }

    public function get_zone(Request $request)
    {
        $zone = Helpers::getCoordinatesZone($request->lat,$request->lng);

        return response()->json($zone);
    }

    public function zone_list(Request $request)
    {
        $zones = Zone::withoutGlobalScope(ZoneScope::class)
            ->where('status', 1)
            ->when($request->zone_id, function ($query) use ($request) {
                return $query->where('id', $request->zone_id);
            })
            ->when($request->q, function ($query) use ($request) {
                return $query->where('name', 'like', '%' . $request->q . '%');
            })
            ->limit(8)
            ->get(['id', 'name'])
            ->map(function ($zone) {
                return ['id' => $zone->id, 'text' => $zone->name];
            });

        return response()->json($zones);
    }

    public function zone_filter($id)
    {
        if ($id == 'all') {
            if (session()->has('zone_id')) {
                session()->forget('zone_id');
            }
        } else {
            session()->put('zone_id', $id);
        }

        return back();
    }

    public function get_all_zone_cordinates($id = 0)
    {
        $zones = Zone::where('id', '<>', $id)->active()->get();
        $data = [];
        foreach ($zones as $zone) {
            $area = json_decode($zone->coordinates[0]->toJson(), true);
            $data[] = Helpers::format_coordiantes($area['coordinates']);
        }

        return response()->json($data, 200);
    }

    public function export_zones(Request $request, $type)
    {

        $key = explode(' ', $request['search']);
        $collection = Zone::withCount(['restaurants', 'deliverymen'])
            ->when(isset($key), function ($q) use ($key) {
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                });
            })
            ->get();
        $data = [
            'data' => $collection,
            'search' => $request['search'] ?? null,
        ];
        if ($type == 'csv') {
            return Excel::download(new ZoneExport($data), 'Zone.csv');
        }

        return Excel::download(new ZoneExport($data), 'Zone.xlsx');
    }

    public function store_incentive(Request $request, $zone_id)
    {
        $request->validate([
            'earning' => [
                'required',
                'numeric',
                'between:1,999999999999.99',
                Rule::unique('incentives', 'earning')->where('zone_id', $zone_id),
            ],
            'incentive' => 'required|numeric|between:1,999999999999.99',
        ], [
            'earning.unique' => translate('This_earning_amount_already_exists'),
        ]);

        $incentive = new Incentive;
        $incentive->earning = $request->earning;
        $incentive->incentive = $request->incentive;
        $incentive->zone_id = $zone_id;
        $incentive->save();
        Toastr::success(translate('messages.incentive_inserted_successfully'));

        return back();
    }

    public function destroy_incentive(Request $request, $id)
    {
        $incentive = Incentive::findOrFail($id);
        $incentive?->delete();
        Toastr::success(translate('messages.incentive_deleted_successfully'));

        return back();
    }
    public function checkLocation(Request $request)
    {
        if (! $request->filled(['latitude', 'longitude', 'zone_id'])) {
            return response()->json([
                'errors' => [
                    ['code' => 'validation', 'message' => translate('messages.Please_select_a_location_within_the_selected_zone.')]
                ]
            ], 200);
        }

        $zone = Zone::query()
            ->where('id', $request->zone_id)
            ->whereContains(
                'coordinates',
                new Point($request->latitude, $request->longitude, POINT_SRID)
            )
            ->first();

        if (! $zone) {
            return response()->json([
                'errors' => [
                    [
                        'code' => 'coordinates',
                        'message' => translate('messages.Please_select_a_location_within_the_selected_zone.')
                    ]
                ]
            ], 200);
        }

        return response()->json(['status' => 'ok'], 200);
    }

}
