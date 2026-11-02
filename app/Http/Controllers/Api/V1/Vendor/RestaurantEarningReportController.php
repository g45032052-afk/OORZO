<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Traits\ReportGeneratorTrait;
use Illuminate\Http\Request;

class RestaurantEarningReportController extends Controller
{
    use ReportGeneratorTrait;

    public function getEarningReport(Request $request)
    {
        $restaurant_id = $request->restaurant_id;
        if(!isset($restaurant_id)){
            return response()->json([
                'message' => translate('Restaurant ID is required')
            ], 400);
        }
        $filter = $request->query('filter', 'all_time');
        $from = $filter === 'custom' ? $request->from : null;
        $to   = $filter === 'custom' ? $request->to : null;
        $type = $request->query('type', 'earning');
        $limit = $request->query('limit', config('default_pagination', 25));
        $offset = $request->query('offset', 1);

        $summary = $this->get_restaurant_earning_summary_data($restaurant_id, $filter, $from, $to);
        $trends = $this->get_restaurant_earning_trend_data($restaurant_id, $filter, $from, $to);
        if ($request->type == 'expense') {
            $transactions = $this->get_restaurant_expense_transactions($request, $restaurant_id, $filter, $from, $to, false, $limit, $offset);
        } elseif ($request->type == 'subscription') {
            $transactions = $this->get_restaurant_subscription_transactions($request, $restaurant_id, $filter, $from, $to, false, $limit, $offset);
        } else {
            $transactions = $this->get_restaurant_earning_transactions($request, $restaurant_id, $filter, $from, $to, false, $limit, $offset);
        }
        
        return response()->json([
            'summary' => $summary,
            'trends'  => $trends,
            'total_size' => $transactions->total(),
            'limit' => (int)$limit,
            'offset' => (int)$offset,
            'transactions' => $transactions->items()
        ], 200);
    }
}
