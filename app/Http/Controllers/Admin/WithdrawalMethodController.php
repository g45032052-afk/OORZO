<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Models\WithdrawalMethod;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;

class WithdrawalMethodController extends Controller
{
    protected WithdrawalMethod $withdrawal_method;

    public function __construct(WithdrawalMethod $withdrawal_method)
    {
        $this->withdrawal_method = $withdrawal_method;
    }

    public function list(Request $request)
    {
        $search = $request->search;
        $withdrawal_methods = $this->withdrawal_method
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('method_name', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('admin-views.withdraw-method.withdraw-methods-list', compact('withdrawal_methods', 'search'));
    }

    public function create(Request $request)
    {
        return view('admin-views.withdraw-method.withdraw-methods-create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'method_name' => 'required',
            'field_type' => 'required|array',
            'field_name' => 'required|array',
            'placeholder_text' => 'required|array',
        ]);

        $method_fields = [];
        foreach ($request->field_name as $key => $field_name) {
            $method_fields[] = [
                'input_type' => $request->field_type[$key],
                'input_name' => strtolower(str_replace(' ', "_", $request->field_name[$key])),
                'placeholder' => $request->placeholder_text[$key],
                'is_required' => isset($request['is_required']) && isset($request['is_required'][$key]) ? 1 : 0,
            ];
        }

        $data_count = $this->withdrawal_method->get()->count();

        $withdrawal_method_object = $this->withdrawal_method->updateOrCreate(
            ['method_name' => $request->method_name],
            [
                'method_fields' => $method_fields,
                'is_default' => ($request->has('is_default') && $request->is_default || $data_count == 0) == '1' ? 1 : 0,
            ]
        );

        if ($request->has('is_default') && $request->is_default == '1') {
            $this->withdrawal_method->where('id', '!=', $withdrawal_method_object->id)->update(['is_default' => 0]);
        }

        Toastr::success('Withdrawal method added successfully');
        return redirect()->route('admin.business-settings.withdraw-method.list');
    }

    public function edit($id)
    {
        $withdrawal_method = $this->withdrawal_method->find($id);
        return View('admin-views.withdraw-method.withdraw-methods-edit', compact('withdrawal_method'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'method_name' => 'required',
            'field_type' => 'required|array',
            'field_name' => 'required|array',
            'placeholder_text' => 'required|array',
        ]);
        $withdrawal_method = $this->withdrawal_method->find($request['id']);

        if(!isset($withdrawal_method)) {
            Toastr::error('Withdrawal method not found!');
            return back();
        }

        $method_fields = [];
        foreach ($request->field_name as $key=>$field_name) {
            $method_fields[] = [
                'input_type' => $request->field_type[$key],
                'input_name' => strtolower(str_replace(' ', "_", $request->field_name[$key])),
                'placeholder' => $request->placeholder_text[$key],
                'is_required' => isset($request['is_required']) && isset($request['is_required'][$key]) ? 1 : 0,
            ];
        }

        $withdrawal_method->method_name = $request->method_name;
        $withdrawal_method->method_fields = $method_fields;
        $withdrawal_method->is_default = $request->has('is_default') && $request->is_default == '1' ? 1 : 0;
        $withdrawal_method->save();

        if ($request->has('is_default') && $request->is_default == '1') {
            $this->withdrawal_method->where('id', '!=', $withdrawal_method->id)->update(['is_default' => 0]);
        }

        Toastr::success('Withdrawal method update successfully');
        return redirect()->route('admin.business-settings.withdraw-method.list');

    }

    public function status_update(Request $request)
    {
        $withdrawal_method = $this->withdrawal_method->where('id', $request->id)->first();

        $withdrawal_method->is_active = !$withdrawal_method->is_active;
        if($withdrawal_method->is_default == 1  && $withdrawal_method->is_active == 0){
            return response()->json([
            'success' => 0,'message' => translate('messages.You_cannot_disable_a_default_method')
        ], 200);
        }
        $withdrawal_method->save();

        return response()->json([
            'success' => 1,'message' =>   translate('messages.status_updated_successfully')
        ], 200);
    }

    public function default_status_update(Request $request)
    {
        $id = $request->id;
        $withdrawal_method = $this->withdrawal_method->where('id', $id)->first();

        if ($withdrawal_method->is_default == 1) {
            return response()->json([
                'success' => 0,'message' => translate('messages.This_is_already_a_default_method')
            ], 200);

        }

        $this->withdrawal_method->where('id', '!=', $id)->update(['is_default' => 0]);
        $withdrawal_method->is_active = 1;
        $withdrawal_method->is_default = 1;
        $withdrawal_method->save();

       return response()->json([
            'success' => 1,'message' =>   translate('messages.default_method_updated_successfully')
        ], 200);
    }

    public function delete($id)
    {
        $withdrawal_method = $this->withdrawal_method->where('id', $id)->first();

        if($withdrawal_method->is_default == 1){
            Toastr::error('Default withdrawal method can not be deleted!');
            return back();

        }
        $withdrawal_method->delete();
        Toastr::success('Withdraw method removed successfully!');
        return back();
    }
}
