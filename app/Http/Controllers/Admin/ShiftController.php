<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Models\Shift;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    public function list(Request $request)
    {
        $key = $request['search'] ? explode(' ', $request['search']) : null;

        $shifts = Shift::latest()->where('is_full_day', 0)
            ->when(isset($key), function ($query) use ($key) {
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                });
            })

            ->paginate(config('default_pagination'));

         $language = getWebConfig('language');
        return view('admin-views.shift.list', [
            'shifts' => $shifts,
            'total' => $shifts->total(),
            'language' => $language
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'name.*' => 'max:191',
            'name.0' => 'required',
        ], [
            'end_time.after' => translate('messages.End time must be after the start time'),
            'name.0.required' => translate('messages.default_shift_name_is_required')
        ]);

        $temp = Shift::where('is_full_day', 0)->where(function ($q) use ($request) {
            return $q->where(function ($query) use ($request) {
                return $query->where('start_time', '<=', $request->start_time)->where('end_time', '>=', $request->start_time);
            })->orWhere(function ($query) use ($request) {
                return $query->where('start_time', '<=', $request->end_time)->where('end_time', '>=', $request->end_time);
            });
        })
            ->first();

        if (isset($temp)) {
            Toastr::error(translate('messages.Shift_overlaped_with_') . $temp->name);
            return back();
        }

        $shift = new Shift();
        $shift->name = $request->name[array_search('default', $request->lang)];
        $shift->start_time = $request->start_time;
        $shift->end_time = $request->end_time;
        $shift->save();
        Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'Shift', data_id: $shift->id, data_value: $shift->name);

        Toastr::success(translate('messages.shift_added_successfully'));
        return back();
    }

    public function status(Request $request)
    {
        $shift = Shift::findOrFail($request->id);
        $shift->status = $request->status;
        $shift->save();
        Toastr::success(translate('messages.shift_status_updated'));
        return back();
    }
    public function update(Request $request)
    {
        $id = $request->id;
        $request->validate([
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'name.*' => 'max:191',
            'name.0' => 'required',
        ], [
            'end_time.after' => translate('messages.End time must be after the start time'),
            'name.0.required' => translate('messages.default_shift_name_is_required')
        ]);


        $temp = Shift::where('id', '!=', $id)->where('is_full_day', 0)->where(function ($q) use ($request) {
            return $q->where(function ($query) use ($request) {
                return $query->where('start_time', '<=', $request->start_time)->where('end_time', '>=', $request->start_time);
            })->orWhere(function ($query) use ($request) {
                return $query->where('start_time', '<=', $request->end_time)->where('end_time', '>=', $request->end_time);
            });
        })
            ->first();

         if (isset($temp)) {
            Toastr::error(translate('messages.Shift_overlaped_with_') . $temp->name);
            return back();
        }
        $shift = Shift::find($id);
        if ($shift->is_full_day == 1) {

            Toastr::warning(translate('messages.full_day_shift_cannot_be_edited'));
            return back();
        }

        $shift->name = $request->name[array_search('default', $request->lang)];
        $shift->start_time = $request->start_time;
        $shift->end_time = $request->end_time;
        $shift->save();
        Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'Shift', data_id: $shift->id, data_value: $shift->name);

        Toastr::success(translate('messages.shift_updated_successfully'));
        return back();
    }
    public function destroy(Shift $shift)
    {
        if ($shift->is_full_day == 1) {
            Toastr::warning(translate('messages.full_day_shift_cannot_be_deleted'));
            return back();
        }


        if (DB::table('delivery_man_shift')->where('shift_id', $shift->id)->exists()) {
            Toastr::warning(translate('this_shift_is_assigned_to_delivery_man_._Update_delivery_man_shift_first'));
            return back();
        }

        $shift->delete();
        $shift?->translations()?->delete();
        Toastr::success(translate('messages.shift_deleted_successfully'));
        return back();
    }
     public function edit($id)
    {
        $shift = Shift::withoutGlobalScope('translate')->with('translations')->find($id);
        $language = getWebConfig('language');
        return response()->json([
            'view' => view('admin-views.shift.partials._edit', compact('shift','language'))->render(),
        ]);
    }

}
