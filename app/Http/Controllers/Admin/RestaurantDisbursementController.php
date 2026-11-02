<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Exports\DisbursementExport;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\Restaurant;
use App\Models\Disbursement;
use App\Models\DisbursementDetails;
use App\Models\RestaurantWallet;
use App\Models\WithdrawRequest;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;

class RestaurantDisbursementController extends Controller
{
    public function list(Request $request)
    {
        $status = $request->status??'all';
        $disbursements = Disbursement::
        when($status!='all', function($q) use($status){
                return $q->where('status',$status);
        })
        ->where('created_for','restaurant')
        ->latest()->paginate(config('default_pagination'));
        return view('admin-views.restaurant-disbursement.index', compact('disbursements','status'));
    }

    public function view(Request $request,$id)
    {
        $key = explode(' ', $request['search']);
        $restaurant_id = $request->query('restaurant_id', 'all');
        $payment_method_id = $request->query('payment_method_id', 'all');
        $disbursement = Disbursement::findOrFail($id);
        $disbursements=DisbursementDetails::with('restaurant','withdraw_method')->where(['disbursement_id'=>$id])
            ->when(isset($key) , function($q) use($key){
                $q->whereHas('restaurant', function ($q) use($key){
                    $q->where(function($query)use ($key){
                        $query->orWhereHas('vendor', function ($q) use ($key) {
                            foreach ($key as $value) {
                                $q->orWhere('f_name', 'like', "%{$value}%")
                                    ->orWhere('l_name', 'like', "%{$value}%")
                                    ->orWhere('email', 'like', "%{$value}%")
                                    ->orWhere('phone', 'like', "%{$value}%");
                            }
                        })
                            ->where(function ($q) use ($key) {
                                foreach ($key as $value) {
                                    $q->orWhere('name', 'like', "%{$value}%")
                                        ->orWhere('email', 'like', "%{$value}%")
                                        ->orWhere('phone', 'like', "%{$value}%");
                                }
                            });
                    });
                });
            })
            ->when((isset($restaurant_id) && is_numeric($restaurant_id)), function ($query) use ($restaurant_id){
                $query->where('restaurant_id', $restaurant_id);
            })
            ->when((isset($payment_method_id) && is_numeric($payment_method_id)), function ($query) use ($payment_method_id){
                $query->whereHas('withdraw_method', function ($q) use($payment_method_id){
                    return $q->where('withdrawal_method_id', $payment_method_id);
                });
            })
            ->latest();
        $restaurant_ids = json_encode($disbursements->pluck('restaurant_id')->toArray());
        $disbursement_restaurants = $disbursements->paginate(config('default_pagination'));
        return view('admin-views.restaurant-disbursement.view', compact('disbursement','disbursement_restaurants','restaurant_ids','restaurant_id','payment_method_id'));
    }
    public function export(Request $request,$id, $type = 'excel')
    {
        $key = explode(' ', $request['search']);
        $restaurant_id = $request->query('restaurant_id', 'all');
        $payment_method_id = $request->query('payment_method_id', 'all');
        $disbursement = Disbursement::findOrFail($id);
        $disbursements=DisbursementDetails::where(['disbursement_id'=>$id])
            ->when(isset($key) , function($q) use($key){
                $q->whereHas('restaurant', function ($q) use($key){
                    $q->where(function($query)use ($key){
                        $query->orWhereHas('vendor', function ($q) use ($key) {
                            foreach ($key as $value) {
                                $q->orWhere('f_name', 'like', "%{$value}%")
                                    ->orWhere('l_name', 'like', "%{$value}%")
                                    ->orWhere('email', 'like', "%{$value}%")
                                    ->orWhere('phone', 'like', "%{$value}%");
                            }
                        })
                            ->where(function ($q) use ($key) {
                                foreach ($key as $value) {
                                    $q->orWhere('name', 'like', "%{$value}%")
                                        ->orWhere('email', 'like', "%{$value}%")
                                        ->orWhere('phone', 'like', "%{$value}%");
                                }
                            });
                    });
                });
            })
            ->when((isset($restaurant_id) && is_numeric($restaurant_id)), function ($query) use ($restaurant_id){
                $query->where('restaurant_id', $restaurant_id);
            })
            ->when((isset($payment_method_id) && is_numeric($payment_method_id)), function ($query) use ($payment_method_id){
                $query->whereHas('withdraw_method', function ($q) use($payment_method_id){
                    return $q->where('withdrawal_method_id', $payment_method_id);
                });
            })
            ->latest()->get();
        $data=[
            'type'=>'restaurant',
            'disbursement' =>$disbursement,
            'disbursements' =>$disbursements,
        ];
        if($type == 'pdf'){
            $mpdf_view = View::make('admin-views.restaurant-disbursement.pdf', compact('disbursement','disbursements')
            );
            Helpers::gen_mpdf(view: $mpdf_view,file_prefix: 'Disbursement',file_postfix: $id);
        }elseif($type == 'csv'){
            return Excel::download(new DisbursementExport($data), 'Disbursement.csv');
        }
        return Excel::download(new DisbursementExport($data), 'Disbursement.xlsx');
    }

    public function status(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $disbursements = DisbursementDetails::with(['restaurant.vendor', 'withdraw_method'])
                    ->where(['disbursement_id' => $request->disbursement_id])
                    ->whereIn('restaurant_id', $request->restaurant_ids)
                    ->lockForUpdate()
                    ->get();

                foreach ($disbursements as $disbursement) {
                    $this->syncRestaurantDisbursementStatus($disbursement, $request->status);
                }

                self::check_status($request->disbursement_id);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => translate('messages.status_updated')
        ]);
    }

    public function statusById($id, $status)
    {
        try {
            DB::transaction(function () use ($id, $status) {
                $disbursement = DisbursementDetails::with(['restaurant.vendor', 'withdraw_method'])
                    ->lockForUpdate()
                    ->findOrFail($id);

                $this->syncRestaurantDisbursementStatus($disbursement, $status);
                self::check_status($disbursement->disbursement_id);
            });
            Toastr::success(translate('messages.status_updated'));
            return back();
        } catch (\Throwable $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    private function syncRestaurantDisbursementStatus(DisbursementDetails $disbursement, string $status): void
    {
        $restaurant = $disbursement->restaurant;
        $wallet = RestaurantWallet::where('vendor_id', $restaurant->vendor_id)->lockForUpdate()->first();

        if (!$wallet) {
            throw new \RuntimeException(translate('messages.wallet_not_found'));
        }

        $amount = (float) $disbursement->disbursement_amount;
        $currentStatus = $disbursement->status;
        $totalEarning = (float) $wallet->total_earning;
        $totalWithdrawn = (float) $wallet->total_withdrawn;
        $pendingWithdraw = (float) $wallet->pending_withdraw;
        $cashInHand = (float) ($wallet->cash_in_hand ?? 0);

        if (($totalEarning - ($totalWithdrawn + $pendingWithdraw + $cashInHand)) < 0) {
            throw new \RuntimeException(translate('messages.balance_mismatched_total_earning_is_too_low'));
        }

        if ($currentStatus === $status) {
            return;
        }

        if ($status === 'completed') {
            if ($currentStatus === 'pending') {
                if ($pendingWithdraw < $amount) {
                    throw new \RuntimeException(translate('messages.pending_withdraw_is_lower_than_disbursement_amount'));
                }

                $wallet->pending_withdraw = $pendingWithdraw - $amount;
                $wallet->total_withdrawn = $totalWithdrawn + $amount;
            } elseif ($currentStatus === 'canceled') {
                $wallet->total_withdrawn = $totalWithdrawn + $amount;
            }

            $withdraw = WithdrawRequest::firstOrNew([
                'transaction_note' => $disbursement->id,
                'vendor_id' => $restaurant?->vendor?->id,
            ]);

            $withdraw->amount = $amount;
            $withdraw->withdrawal_method_id = $disbursement->payment_method;
            $withdraw->withdrawal_method_fields = $disbursement->withdraw_method?->method_fields;
            $withdraw->approved = 1;
            $withdraw->type = 'disbursement';
            $withdraw->save();
        } elseif ($status === 'canceled') {
            if ($currentStatus === 'completed') {
                throw new \RuntimeException(translate('messages.can_not_cancel_completed_disbursement_,_uncheck_completed_disbursements'));
            }

            if ($currentStatus === 'pending') {
                if ($pendingWithdraw < $amount) {
                    throw new \RuntimeException(translate('messages.pending_withdraw_is_lower_than_disbursement_amount'));
                }

                $wallet->pending_withdraw = $pendingWithdraw - $amount;
            }
        } elseif ($status === 'pending') {
            if ($currentStatus === 'completed') {
                if ($totalWithdrawn < $amount) {
                    throw new \RuntimeException(translate('messages.total_withdrawn_is_lower_than_disbursement_amount'));
                }

                WithdrawRequest::where('transaction_note', $disbursement->id)
                    ->where('vendor_id', $restaurant->vendor_id)
                    ->delete();

                $wallet->total_withdrawn = $totalWithdrawn - $amount;
                $wallet->pending_withdraw = $pendingWithdraw + $amount;
            } elseif ($currentStatus === 'canceled') {
                $wallet->pending_withdraw = $pendingWithdraw + $amount;
            }
        }

        $newBalance = (float) $wallet->total_earning
            - (
                (float) $wallet->total_withdrawn
                + (float) $wallet->pending_withdraw
                + (float) ($wallet->cash_in_hand ?? 0)
            );

        if ($newBalance < 0) {
            throw new \RuntimeException(translate('messages.balance_would_become_negative_after_this_status_change'));
        }

        $wallet->save();
        $disbursement->status = $status;
        $disbursement->save();
    }

    public function generate_disbursement()
    {
        $restaurants = Restaurant::where('status',1)->has('disbursement_method')->with('wallet', 'disbursement_method')->select(['id', 'vendor_id'])->get();
        $disbursement_details = [];
        $total_amount = 0;

        $lastId = Disbursement::max('id') ?? 999;
        $disbursement = new Disbursement();
        $disbursement->id = $lastId + 1;


        $disbursement->title = 'Disbursement # '.$disbursement->id;
        $minimum_amount = BusinessSetting::where(['key' => 'restaurant_disbursement_min_amount'])->first()?->value;
        foreach ($restaurants as $restaurant){
            if(isset($restaurant->wallet)){
                $total_earning = $restaurant->wallet->total_earning ?? 0;
                $total_withdraw = ($restaurant->wallet->total_withdrawn ?? 0) + ($restaurant->wallet->pending_withdraw ?? 0);
                $total_cash_in_hand = $restaurant->wallet->collected_cash ?? 0;

                $disbursement_amount = ((string) $total_earning> (string) ($total_withdraw+$total_cash_in_hand))?(  ($total_earning - ($total_withdraw+$total_cash_in_hand))):0;

                if ($disbursement_amount > $minimum_amount && isset($restaurant->disbursement_method)){
                    $res_d = [
                        'disbursement_id' => $disbursement->id,
                        'restaurant_id' => $restaurant->id,
                        'disbursement_amount' => $disbursement_amount,
                        'payment_method' => $restaurant->disbursement_method->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    $disbursement_details[] = $res_d;
                    $total_amount += $res_d['disbursement_amount'];
                    $restaurant->wallet->pending_withdraw = $restaurant->wallet->pending_withdraw + $disbursement_amount;
                    $restaurant->wallet->save();
                }
            }
        }

        if ($total_amount > 0){
            $disbursement->total_amount = $total_amount;
            $disbursement->created_for = 'restaurant';
            $disbursement->save();

            DisbursementDetails::insert($disbursement_details);
        }
        info("Restaurant-----Disbursement");
        return true;

    }

    public function check_status($id) {
        $disbursements = DisbursementDetails::where(['disbursement_id' => $id])->get();
        $statusCounts = $disbursements->countBy('status');

        $disbursement = Disbursement::find($id);

        if (isset($statusCounts['pending']) && ($statusCounts['pending'] == count($disbursements))) {
            $disbursement->status = 'pending';
        } elseif (isset($statusCounts['canceled']) && ($statusCounts['canceled'] == count($disbursements))) {
            $disbursement->status = 'canceled';
        } elseif (isset($statusCounts['completed']) && ($statusCounts['completed'] == count($disbursements))) {
            $disbursement->status = 'completed';
        } else {
            $disbursement->status = 'partially_completed';
        }

        return $disbursement->save();
    }
}
