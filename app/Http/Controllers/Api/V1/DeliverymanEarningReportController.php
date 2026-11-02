<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeliveryMan;
use App\Traits\ReportGeneratorTrait;
use Illuminate\Http\Request;

class DeliverymanEarningReportController extends Controller
{
    use ReportGeneratorTrait;

    public function getEarningReport(Request $request)
    {
        $dm = DeliveryMan::where(['auth_token' => $request['token']])->first();
        if(!$dm){
            return response()->json([
                'message' => translate('messages.unauthorized')
            ], 401);
        }

        $filter = $request->query('filter', 'all_time');
        $from = $filter === 'custom' ? $request->from : null;
        $to   = $filter === 'custom' ? $request->to : null;

        $summary = $this->get_deliveryman_earning_summary_data($dm->id, $filter, $from, $to);
        $trends = $this->get_deliveryman_earning_trend_data($dm->id, $filter, $from, $to);
        $transactions = $this->get_deliveryman_transaction_components($request, $dm->id, $filter, $from, $to);
        unset($summary['breakdown']['admin_commission']);
        return response()->json([
            'summary' => $summary,
            'trends'  => $trends,
            'transactions' => $transactions
        ], 200);
    }
}
