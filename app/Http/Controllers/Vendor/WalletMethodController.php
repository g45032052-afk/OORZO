<?php

namespace App\Http\Controllers\Vendor;


use App\Models\DisbursementWithdrawalMethod;
use App\CentralLogics\Helpers;
use App\Models\WithdrawalMethod;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class WalletMethodController extends Controller
{
    public function index(Request $request)
    {
        $key = explode(' ', $request['search']);
        $withdrawal_methods = WithdrawalMethod::ofStatus(1)->get();
        $vendor_withdrawal_methods = DisbursementWithdrawalMethod::where('restaurant_id', Helpers::get_restaurant_id())
            ->when( isset($key) , function($query) use($key){
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('method_name', 'like', "%{$value}%");
                    }
                });
            }
            )
            ->latest()->paginate(config('default_pagination'));

        return view('vendor-views.wallet-method.index', compact('withdrawal_methods','vendor_withdrawal_methods'));
    }

    public function store(Request $request)
    {
        $method = WithdrawalMethod::find($request['withdraw_method']);
        $fields = array_column($method->method_fields, 'input_name');
        $values = $request->all();

        $method_data = [];
        foreach ($fields as $field) {
            if(key_exists($field, $values)) {
                $method_data[$field] = $values[$field];
            }
        }

        $data = [
            'restaurant_id' => Helpers::get_restaurant_id(),
            'withdrawal_method_id' => $method['id'],
            'method_name' => $method['method_name'],
            'method_fields' => json_encode($method_data),
            'is_default' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ];

        DB::table('disbursement_withdrawal_methods')->insert($data);
        Toastr::success(translate('Withdraw method stored.'));
        return redirect()->back();
    }

    public function default(Request $request)
    {
        $method = DisbursementWithdrawalMethod::find($request->id);
        $method->is_default = $request->default;
        $method->save();
        DisbursementWithdrawalMethod::whereNot('id', $request->id)->where('restaurant_id',Helpers::get_restaurant_id())->update(['is_default' => 0]);
        Toastr::success(translate('messages.default_method_updated'));
        return back();
    }

    public function delete(Request $request)
    {
        $method = DisbursementWithdrawalMethod::find($request->id);
          if($method->is_default == 1){
            Toastr::error('Default withdrawal method can not be deleted!');
            return back();
        }

        $method->delete();
        Toastr::success(translate('messages.method_deleted_successfully'));
        return back();
    }


    public function status_update(Request $request)
    {
        $withdrawal_method = DisbursementWithdrawalMethod::where('id', $request->id)->first();

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

        $withdrawal_method = DisbursementWithdrawalMethod::where('id', $request->id)->first();

        if ($withdrawal_method->is_default == 1) {
            return response()->json([
                'success' => 0,'message' => translate('messages.This_is_already_a_default_method')
            ], 200);

        }

        DisbursementWithdrawalMethod::where('id', '!=', $request->id)->update(['is_default' => 0]);
        $withdrawal_method->is_default = 1;
        $withdrawal_method->is_active = 1;
        $withdrawal_method->save();
        return response()->json([
            'success' => 1,'message' =>   translate('messages.default_method_updated_successfully')
        ], 200);
    }


       public function edit($id)
    {
        $withdrawal_method = DisbursementWithdrawalMethod::with('withdrawMethod')->where('id', $id)->first();
        if (!$withdrawal_method->withdrawMethod) {
        return response()->json([
                    'error' => 1,'message' => translate('messages.Withdrawal_method_not_found')
                ]);
        }


 return response()->json([
            'view' => view('vendor-views.wallet-method._edit_withdraw_method', compact('withdrawal_method'))->render(),
        ]);

    }

    public function update(Request $request)
    {

        $withdrawal_method = DisbursementWithdrawalMethod::with('withdrawMethod')->find($request['id']);

        if(!isset($withdrawal_method)) {
            Toastr::error('Withdrawal method not found!');
            return back();
        }

        $method = $withdrawal_method->withdrawMethod;
        $fields = array_column($method->method_fields, 'input_name');
        $values = $request->all();

        $method_data = [];
        foreach ($fields as $field) {
            if(key_exists($field, $values)) {
                $method_data[$field] = $values[$field];
            }
        }


        $withdrawal_method->method_name = $method['method_name'];
        $withdrawal_method->method_fields =json_encode($method_data);
        $withdrawal_method->save();


        Toastr::success('Withdrawal method update successfully');
        return back();

    }


}
