<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\LoyaltyPointTransaction;
use App\Exports\CustomerLoyaltyTransactionExport;
use Brian2694\Toastr\Facades\Toastr;

class LoyaltyPointController extends Controller
{
    public function report(Request $request)
    {

        $data = $this->getTransactionsData($request);
        $transactions = $data['transactions'];
        $transactions = $transactions->paginate(config('default_pagination'));
        $data = $data['data'];

        return view('admin-views.customer.loyalty-point.report', compact('data', 'transactions'));
    }
    public function export(Request $request)
    {

        try {
            $data = $this->getTransactionsData($request);
            $transactions = $data['transactions'];
            $transactions = $transactions->get();
            $data = $data['data'];


            $data = [
                'transactions' => $transactions,
                'data' => $data,
                'from' => $request->from ?? null,
                'to' => $request->to ?? null,
                'transaction_type' => $request->transaction_type ?? null,
                'customer' => $request->customer_id ? Helpers::get_customer_name($request->customer_id) : null,

            ];

            if ($request->type == 'excel') {
                return Excel::download(new CustomerLoyaltyTransactionExport($data), 'CustomerLoyaltyTransactions.xlsx');
            } else if ($request->type == 'csv') {
                return Excel::download(new CustomerLoyaltyTransactionExport($data), 'CustomerLoyaltyTransactions.csv');
            }
        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);
            return back();
        }
    }



    private function getTransactionsData($request)
    {
        $data = LoyaltyPointTransaction::selectRaw('sum(credit) as total_credit, sum(debit) as total_debit')->with('user')
            ->when(($request->from && $request->to), function ($query) use ($request) {
                $query->whereBetween('created_at', [$request->from . ' 00:00:00', $request->to . ' 23:59:59']);
            })
            ->when($request->transaction_type && $request->transaction_type != 'all', function ($query) use ($request) {
                $query->where('transaction_type', $request->transaction_type);
            })
            ->when($request->customer_id, function ($query) use ($request) {
                $query->where('user_id', $request->customer_id);
            })
            ->search(keywords: $request->search, mainCol: ['transaction_id', 'reference', 'reference_id'])
            ->get();

        $transactions = LoyaltyPointTransaction::with('user')->when(($request->from && $request->to), function ($query) use ($request) {
                $query->whereBetween('created_at', [$request->from . ' 00:00:00', $request->to . ' 23:59:59']);
            })
            ->when($request->transaction_type && $request->transaction_type != 'all', function ($query) use ($request) {
                $query->where('transaction_type', $request->transaction_type);
            })
            ->when($request->customer_id, function ($query) use ($request) {
                $query->where('user_id', $request->customer_id);
            })

            ->search(keywords: $request->search, mainCol: ['transaction_id', 'reference', 'reference_id'])
            ->latest();

        return [
            'data' => $data,
            'transactions' => $transactions,
        ];
    }
}
