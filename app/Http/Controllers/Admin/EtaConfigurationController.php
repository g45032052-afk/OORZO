<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Exports\EtaConfigurationExport;
use App\Http\Controllers\Controller;
use App\Models\EtaConfiguration;
use App\Models\Zone;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

/**
 * The ETA Configuration screens.
 *
 * A zone holds as many configurations as the admin cares to author but estimates
 * from exactly one of them, so every screen here is about which one that is. The
 * rules follow the delivery rules, which have the same shape:
 *
 *   - the zone's first configuration goes live on save, later ones are created
 *     switched off, because a second one going live would silently displace the
 *     configuration the zone is already estimating from;
 *   - switching one on switches the zone's others off;
 *   - the active one cannot simply be switched off or deleted - the admin names
 *     which configuration takes over, and that swap is what the request performs;
 *   - a zone's last configuration cannot be deleted at all, since the zone would
 *     be left with no way to quote a delivery time.
 *
 * How the estimate is arrived at is the configuration's own business - see
 * {@see EtaConfiguration::minimumEtaMinutes()}.
 */
class EtaConfigurationController extends Controller
{
    public function list(Request $request)
    {
        // How many configurations the row's zone holds, so the list knows whether a
        // row may be switched off or deleted at all - the last one of a zone may not.
        // Counted in the query rather than per row, which would be a query per row.
        $configurations = EtaConfiguration::with('zone')
            ->select('eta_configurations.*')
            // Aliased, because the subquery counts the same table it is correlated
            // against: unaliased, both sides of whereColumn read as the inner one
            // and every row is told its zone holds the whole table.
            ->addSelect(['zone_configuration_count' => DB::table('eta_configurations as zone_sibling')
                ->selectRaw('COUNT(*)')
                ->whereColumn('zone_sibling.zone_id', 'eta_configurations.zone_id'),
            ])
            ->when($request['search'], function ($query) use ($request) {
                $search = $request['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhereHas('zone', function ($zoneQuery) use ($search) {
                            $zoneQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            // A zone's configurations read as a set, and within a set the one the
            // zone is estimating from belongs at the top.
            ->orderBy('zone_id')
            ->orderByDesc('status')
            ->orderByDesc('id')
            ->paginate(config('default_pagination'));

        return view('admin-views.eta-configuration.list', compact('configurations'));
    }

    /**
     * Every zone is offered, whatever it already holds: a zone may be given a
     * second configuration to switch to, and the one it estimates from is only
     * displaced when the admin switches the new one on.
     */
    public function create(Request $request)
    {
        $requestedZoneId = (int) $request->query('zone_id');

        // Every zone, whatever its status and whatever it already holds.
        //
        // Inactive zones are listed because a zone is created switched off and cannot be
        // switched on until it has an active ETA configuration - Zone::hasEtaSetup() is one
        // of the gates - so leaving them out would make that impossible. Configured zones
        // are listed because a zone may hold several configurations and be given another
        // to switch to.
        $zones = Zone::orderBy('name')->get();

        // The form opens on the named zone. Arriving without one leaves the select on its
        // placeholder: falling back to the first zone would save a configuration against a
        // zone the admin never picked.
        $selectedZone = $zones->firstWhere('id', $requestedZoneId);

        return view('admin-views.eta-configuration.create', [
            'language' => getWebConfig('language'),
            'zones' => $zones,
            'selectedZoneId' => $selectedZone?->id,
            'configuration' => null,
        ]);
    }

    public function store(Request $request)
    {
        $validator = $this->validateSetup($request);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        DB::beginTransaction();

        try {
            $configuration = new EtaConfiguration();
            $this->fill($configuration, $request);

            // A zone's first configuration has nothing to displace, so it goes live
            // at once. A later one is created switched off: the zone is already
            // estimating from one, and swapping that out is a decision of its own,
            // taken from the list once the admin has read what was saved.
            $configuration->status = EtaConfiguration::where('zone_id', $request->zone_id)->exists() ? 0 : 1;
            $configuration->save();

            $this->saveTranslations($request, $configuration);

            DB::commit();

            return response()->json([
                'success' => translate('messages.eta_configuration_created_successfully'),
                'redirect' => route('admin.eta-configuration.list'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return response()->json(['errors' => [['code' => 'server', 'message' => $e->getMessage()]]], 500);
        }
    }

    public function edit($id)
    {
        // withoutGlobalScope('translate') so every locale's row is loaded, not just the active
        // one - each language tab prefills from its own.
        $configuration = EtaConfiguration::withoutGlobalScope('translate')
            ->with(['zone', 'translations'])
            ->findOrFail($id);

        return view('admin-views.eta-configuration.edit', [
            'language' => getWebConfig('language'),
            'configuration' => $configuration,
            'zones' => collect([$configuration->zone])->filter(),
        ]);
    }

    /** The zone cannot be moved once chosen, so the form posts the timings only. */
    public function update(Request $request, $id)
    {
        $configuration = EtaConfiguration::findOrFail($id);
        $request->merge(['zone_id' => $configuration->zone_id]);

        $validator = $this->validateSetup($request);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        DB::beginTransaction();

        try {
            $this->fill($configuration, $request);
            $configuration->save();

            $this->saveTranslations($request, $configuration);

            DB::commit();

            return response()->json([
                'success' => translate('messages.eta_configuration_updated_successfully'),
                'redirect' => route('admin.eta-configuration.list'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return response()->json(['errors' => [['code' => 'server', 'message' => $e->getMessage()]]], 500);
        }
    }

    /**
     * The zone's other configurations, as the options of the prompts that ask
     * which one takes over.
     *
     * Fetched rather than read off the page: the list is paginated, so the markup
     * never carries a zone's full set.
     */
    public function zoneConfigurations(Request $request, $zoneId)
    {
        $configurations = EtaConfiguration::where('zone_id', $zoneId)
            ->when($request->exclude, function ($query) use ($request) {
                $query->where('id', '!=', $request->exclude);
            })
            ->orderByDesc('status')
            ->orderBy('id')
            ->get();

        return response()->json([
            'configurations' => $configurations->map(fn (EtaConfiguration $configuration) => [
                'id' => $configuration->id,
                'name' => $configuration->optionLabel(),
            ])->values(),
        ]);
    }

    /**
     * A zone estimates from exactly one configuration, so a status change is
     * always a swap.
     *
     * Switching one on switches the zone's others off. Switching the active one
     * off is only possible by naming which configuration takes over, because a
     * zone left with none has no delivery time to quote; the named one is
     * switched on in the same transaction, so the zone is never without.
     */
    public function status(Request $request, $id)
    {
        $configuration = EtaConfiguration::findOrFail($id);
        $turningOn = (int) $request->status === 1;

        DB::beginTransaction();

        try {
            if ($turningOn) {
                EtaConfiguration::where('zone_id', $configuration->zone_id)
                    ->where('id', '!=', $configuration->id)
                    ->update(['status' => 0]);

                $configuration->status = 1;
                $configuration->save();

                $message = translate('messages.eta_configuration_activated');
            } else {
                $replacement = EtaConfiguration::where('zone_id', $configuration->zone_id)
                    ->where('id', '!=', $configuration->id)
                    ->find($request->replacement_id);

                if (! $replacement) {
                    DB::rollBack();

                    return response()->json([
                        'errors' => [['code' => 'replacement_id', 'message' => translate('messages.please_select_another_eta_configuration_for_this_zone')]],
                    ], 422);
                }

                EtaConfiguration::where('zone_id', $configuration->zone_id)->update(['status' => 0]);
                $replacement->status = 1;
                $replacement->save();

                $message = translate('messages.eta_configuration_switched');
            }

            DB::commit();

            return response()->json(['success' => $message]);
        } catch (\Exception $e) {
            DB::rollBack();
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return response()->json(['errors' => [['code' => 'server', 'message' => $e->getMessage()]]], 500);
        }
    }

    /**
     * A zone keeps a configuration at all times, so its last one cannot be
     * removed and the one it is estimating from is only removed together with
     * the replacement the admin names - the list asks for it before submitting.
     */
    public function destroy(Request $request, $id)
    {
        $configuration = EtaConfiguration::findOrFail($id);

        $siblings = EtaConfiguration::where('zone_id', $configuration->zone_id)
            ->where('id', '!=', $configuration->id);

        if (! $siblings->exists()) {
            Toastr::error(translate('messages.the_only_eta_configuration_of_a_zone_cannot_be_deleted'));

            return back();
        }

        DB::beginTransaction();

        try {
            // Deleting the active one leaves the zone without an estimate unless
            // another takes over in the same breath, so the replacement is named
            // and switched on here rather than left to a second request.
            if ($configuration->status) {
                $replacement = (clone $siblings)->find($request->replacement_id);

                if (! $replacement) {
                    DB::rollBack();
                    Toastr::error(translate('messages.please_select_another_eta_configuration_for_this_zone'));

                    return back();
                }

                EtaConfiguration::where('zone_id', $configuration->zone_id)->update(['status' => 0]);
                $replacement->status = 1;
                $replacement->save();
            }

            $configuration->translations()->delete();
            $configuration->delete();

            DB::commit();

            Toastr::success(translate('messages.eta_configuration_deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            info(["line___{$e->getLine()}", $e->getMessage()]);
            Toastr::error($e->getMessage());
        }

        return redirect()->route('admin.eta-configuration.list');
    }

    public function export(Request $request, $type)
    {
        try {
            $configurations = EtaConfiguration::with('zone')
                ->when($request['search'], function ($query) use ($request) {
                    $search = $request['search'];
                    $query->where(function ($q) use ($search) {
                        $q->where('id', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhereHas('zone', function ($zoneQuery) use ($search) {
                                $zoneQuery->where('name', 'like', "%{$search}%");
                            });
                    });
                })
                ->latest()
                ->get();

            $data = ['data' => $configurations, 'search' => $request['search'] ?? null];

            if ($type == 'csv') {
                return Excel::download(new EtaConfigurationExport($data), 'EtaConfiguration.csv');
            }

            return Excel::download(new EtaConfigurationExport($data), 'EtaConfiguration.xlsx');
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

    private function validateSetup(Request $request)
    {
        // A day is as long as an estimate may sensibly get; the form asks for minutes.
        $minutes = 'integer|min:0|max:1440';

        // The time fields the chosen method actually drives are required: saved empty they are
        // stored NULL, and EtaLogic then produces no ETA at all rather than a wrong one - a
        // silent dead configuration the admin gets no warning about. The gap belongs to the
        // distance method alone, so it is only demanded there.
        $isDistance = $request->calculation_method === EtaConfiguration::METHOD_DISTANCE;

        return Validator::make($request->all(), [
            // One value per language tab; index 0 is the default-language input.
            'name' => 'nullable|array',
            'name.*' => 'nullable|string|max:100',
            // A zone may hold several configurations, so the zone is not unique here;
            // which of them the zone estimates from is settled by status(), not by save.
            'zone_id' => 'required|exists:zones,id',
            'calculation_method' => 'required|in:' . implode(',', EtaConfiguration::METHODS),
            'minimum_delivery_time' => "required|{$minutes}",
            'preparation_buffer' => "required|{$minutes}",
            'transit_buffer' => "required|{$minutes}",
            'time_gap' => $isDistance ? "required|{$minutes}" : "nullable|{$minutes}",
        ], [
            'zone_id.required' => translate('messages.please_select_a_zone'),
            'calculation_method.required' => translate('messages.please_select_an_eta_calculation_method'),
            'minimum_delivery_time.required' => translate('messages.minimum_delivery_time_is_required'),
            'preparation_buffer.required' => translate('messages.preparation_buffer_is_required'),
            'transit_buffer.required' => translate('messages.transit_buffer_is_required'),
            'time_gap.required' => translate('messages.time_gap_is_required'),
        ]);
    }

    /**
     * Persist the non-default language names against the polymorphic translations table.
     *
     * getRawOriginal('name') rather than ->name: the accessor returns the active locale's
     * translation, which would write a locale's value back over the default column.
     */
    private function saveTranslations(Request $request, EtaConfiguration $configuration): void
    {
        Helpers::add_or_update_translations(
            request: $request,
            key_data: 'name',
            name_field: 'name',
            model_name: 'EtaConfiguration',
            data_id: $configuration->id,
            data_value: $configuration->getRawOriginal('name')
        );
    }

    /** The gap belongs to the distance based method alone; the other drops it. */
    private function fill(EtaConfiguration $configuration, Request $request): void
    {
        $defaultIndex = array_search('default', (array) $request->lang);
        $configuration->name = ($request->name[$defaultIndex] ?? null) ?: null;
        $configuration->zone_id = $request->zone_id;
        $configuration->calculation_method = $request->calculation_method;
        $configuration->minimum_delivery_time = $this->minutes($request->minimum_delivery_time);
        $configuration->preparation_buffer = $this->minutes($request->preparation_buffer);
        $configuration->transit_buffer = $this->minutes($request->transit_buffer);
        $configuration->time_gap = $request->calculation_method === EtaConfiguration::METHOD_DISTANCE
            ? $this->minutes($request->time_gap)
            : null;
    }

    /** An empty field is left empty rather than stored as a zero it never was. */
    private function minutes($value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
