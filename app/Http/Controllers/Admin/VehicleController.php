<?php

namespace App\Http\Controllers\Admin;
use App\Models\Vehicle;
use App\Models\DeliveryMan;
use App\Models\Translation;
use App\Exports\VehicleExport;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $key = explode(' ', $request['search']);
        $vehicles = Vehicle::when(isset($key) ,function ($q) use ($key) {
            foreach ($key as $value) {
                $q->where('type', 'like', "%{$value}%");
            }
        })->latest()->paginate(config('default_pagination'));
        return view('admin-views.vehicle.list', compact('vehicles') );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $language = getWebConfig('language');

        return response()->json([
            'view' => view('admin-views.vehicle.partials._create', compact('language'))->render(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|max:254|unique:vehicles',
            'extra_charges' => 'required|numeric|between:0,999999999999.99',
            'starting_coverage_area' => 'required|numeric|between:0,999999999999.99',
            'maximum_coverage_area' => 'required|numeric|between:.01,999999999999.99|gt:starting_coverage_area',
        ]);

        if($request->type[array_search('default', $request->lang)] == '' ){
            $validator->getMessageBag()->add('type', translate('messages.default_vehicle_type_is_required'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
            }

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        // Stored exactly as typed. A coverage area is a setup value, not a measurement: the
        // number stands and the business setting says what unit it is read in, so a range of
        // 0-400 is 400 km today and 400 miles if the setting changes. Nothing is converted on
        // the way in or out - it is the live trip distance that is converted to meet it, in
        // Helpers::vehicle_extra_charge().
        $starting_coverage_area = $request->starting_coverage_area;
        $maximum_coverage_area = $request->maximum_coverage_area;

        $temp = Vehicle::where(function ($query) use ($starting_coverage_area) {
            $query->where('starting_coverage_area', '<=', $starting_coverage_area)->where('maximum_coverage_area', '>=', $starting_coverage_area);
        })->orWhere(function ($query) use ($maximum_coverage_area) {
            $query->where('starting_coverage_area', '<=', $maximum_coverage_area)->where('maximum_coverage_area', '>=', $maximum_coverage_area);
        })->orWhere(function ($query) use ($starting_coverage_area,$maximum_coverage_area) {
            $query->where('starting_coverage_area', '>=', $starting_coverage_area)->where('maximum_coverage_area', '<=', $maximum_coverage_area);
        })
        ->first();

        if (isset($temp)) {
            return response()->json(['errors' => [
                ['code' => 'Vehicle_overlaped', 'message' => translate('messages.Coverage_area_overlapped')]
            ]]);
        }

        $vehicle = new Vehicle();
        $vehicle->type = $request->type[array_search('default', $request->lang)];
        $vehicle->status = 1;
        $vehicle->extra_charges = $request->extra_charges;
        $vehicle->starting_coverage_area = $starting_coverage_area;
        $vehicle->maximum_coverage_area = $maximum_coverage_area;
        $vehicle->save();
        $default_lang = str_replace('_', '-', app()->getLocale());

        $data = [];
        foreach($request->lang as $index=>$key)
        {
            if($default_lang == $key && !($request->type[$index])){
                if($key != 'default')
                {
                    array_push($data, Array(
                        'translationable_type'  => 'App\Models\Vehicle',
                        'translationable_id'    => $vehicle->id,
                        'locale'                => $key,
                        'key'                   => 'type',
                        'value'                 => $vehicle->type,
                    ));
                }
            }else{
                if($request->type[$index] && $key != 'default')
                {
                    array_push($data, Array(
                        'translationable_type'  => 'App\Models\Vehicle',
                        'translationable_id'    => $vehicle->id,
                        'locale'                => $key,
                        'key'                   => 'type',
                        'value'                 => $request->type[$index],
                    ));
                }
            }
        }

        if(count($data))
        {
            Translation::insert($data);
        }


        return response()->json(['success' => translate('messages.Vehicle_created')]);
    }



    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function view($vehicle, Request $request)
    {
        $vehicle = Vehicle::findOrFail($vehicle);
        $key = explode(' ', $request['search']);
        $delivery_men = DeliveryMan::when(isset($key),function($query)use($key){
            $query->where(function($query)use($key){
                foreach ($key as $value) {
                    $query->orWhere('f_name', 'like', "%{$value}%")->orWhere('l_name', 'like', "%{$value}%");
                }
            });
        })
        ->with('vehicle')
        ->where('vehicle_id',$vehicle->id)
        ->latest()->paginate(config('default_pagination'));
        return view('admin-views.vehicle.view', compact('vehicle','delivery_men') );
    }



    public function status($id, $status)
    {
        $Vehicle = Vehicle::findOrFail($id);
        $Vehicle->status = $status;
        $Vehicle->save();
        Toastr::success(translate('messages.Vehicle_status_updated'));
        return back();
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function edit($vehicle)
    {
        $vehicle = Vehicle::withoutGlobalScope('translate')->with('translations')->findOrFail($vehicle);
        $language = getWebConfig('language');

        return response()->json([
            'view' => view('admin-views.vehicle.partials._edit', compact('vehicle', 'language'))->render(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|max:254|unique:vehicles,type,'.$vehicle->id,
            'extra_charges' => 'required|numeric|between:0,999999999999.99',
            'starting_coverage_area' => 'required|numeric|between:0,999999999999.99',
            'maximum_coverage_area' => 'required|numeric|between:.01,999999999999.99|gt:starting_coverage_area',
        ]);

        if($request->type[array_search('default', $request->lang)] == '' ){
            $validator->getMessageBag()->add('type', translate('messages.default_vehicle_type_is_required'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
            }

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }
        // Stored as typed, exactly as on create - see the note there.
        $starting_coverage_area = $request->starting_coverage_area;
        $maximum_coverage_area = $request->maximum_coverage_area;

        $temp = Vehicle::where('id' ,'!=', $vehicle->id)->where(function ($q) use ($starting_coverage_area,$maximum_coverage_area ){
            $q->where(function ($query) use ($starting_coverage_area) {
                $query->where('starting_coverage_area', '<=', $starting_coverage_area)->where('maximum_coverage_area', '>=', $starting_coverage_area);
            })->orWhere(function ($query) use ($maximum_coverage_area) {
                $query->where('starting_coverage_area', '<=', $maximum_coverage_area)->where('maximum_coverage_area', '>=', $maximum_coverage_area);
            })->orWhere(function ($query) use ($starting_coverage_area, $maximum_coverage_area) {
                $query->where('starting_coverage_area', '>=', $starting_coverage_area)->where('maximum_coverage_area', '<=', $maximum_coverage_area);
            });
        })
        ->first();
        if (isset($temp)) {
            return response()->json(['errors' => [
            ['code' => 'Vehicle_overlaped', 'message' => translate('messages.Coverage_area_overlapped')]
            ]]);
        }

        $vehicle->type = $request->type[array_search('default', $request->lang)];
        $vehicle->extra_charges = $request->extra_charges;
        $vehicle->starting_coverage_area = $starting_coverage_area;
        $vehicle->maximum_coverage_area = $maximum_coverage_area;
        $vehicle->save();

        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang as $index => $key) {
            if ($default_lang == $key && !($request->type[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\Vehicle',
                            'translationable_id' => $vehicle->id,
                            'locale' => $key,
                            'key' => 'type'
                        ],
                        ['value' => $vehicle->type]
                    );
                }
            } else {
                if ($request->type[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\Vehicle',
                            'translationable_id' => $vehicle->id,
                            'locale' => $key,
                            'key' => 'type'
                        ],
                        ['value' => $request->type[$index]]
                    );
                }
            }

        }
        return response()->json(['success' => translate('messages.Vehicle_updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $vehicle = Vehicle::findOrFail($request->vehicle);
        $vehicle?->translations()?->delete();

        $vehicle->delete();
        Toastr::success(translate('messages.vehicle_removed'));
        return back();
    }

    public function export(Request $request)
    {
        try {
            $key = explode(' ', $request['search']);
            $vehicles = Vehicle::when(isset($request['search']), function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->where('type', 'like', "%{$value}%");
                }
            })->latest()->get();

            $data = [
                'data' => $vehicles,
                'search' => $request['search'] ?? null,
            ];

            if ($request->type == 'csv') {
                return Excel::download(new VehicleExport($data), 'Vehicle.csv');
            }

            return Excel::download(new VehicleExport($data), 'Vehicle.xlsx');
        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return back();
        }
    }
}
