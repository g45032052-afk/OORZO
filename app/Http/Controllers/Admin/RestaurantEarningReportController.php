<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Restaurant;
use App\Traits\ReportGeneratorTrait;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RestaurantEarningTransactionExport;

class RestaurantEarningReportController extends Controller
{
    use ReportGeneratorTrait;

    public function getRestaurantEarningReport(Request $request)
    {
        $restaurants = Restaurant::orderBy('name')->get(['id', 'name']);
        $restaurant_id = $request->query('restaurant_id', 'all');

        return view('admin-views.report.restaurant-earning-report', compact('restaurants', 'restaurant_id'));
    }

    public function getRestaurantEarningSummary(Request $request)
    {
        $restaurant_id = $request->query('restaurant_id', 'all');
        [$filter, $from, $to] = $this->resolveDateFilter($request);

        $vendor_id = 'all';
        if ($restaurant_id !== 'all') {
            $restaurant = Restaurant::find($restaurant_id);
            $vendor_id = $restaurant ? $restaurant->vendor_id : 'all';
        }

        $summary = $this->get_restaurant_earning_summary_data($vendor_id, $filter, $from, $to);
        
        return response()->json([
            'view' => view('admin-views.report.partials._restaurant-earning-summary', compact('summary'))->render()
        ]);
    }

    public function getRestaurantEarningBreakdown(Request $request)
    {
        $restaurant_id = $request->query('restaurant_id', 'all');
        [$filter, $from, $to] = $this->resolveDateFilter($request);

        $vendor_id = 'all';
        if ($restaurant_id !== 'all') {
            $restaurant = Restaurant::find($restaurant_id);
            $vendor_id = $restaurant ? $restaurant->vendor_id : 'all';
        }

        $summary = $this->get_restaurant_earning_summary_data($vendor_id, $filter, $from, $to);
        
        return response()->json([
            'view' => view('admin-views.report.partials._restaurant-earning-breakdown', compact('summary'))->render()
        ]);
    }

    public function getRestaurantExpenseBreakdown(Request $request)
    {
        $restaurant_id = $request->query('restaurant_id', 'all');
        [$filter, $from, $to] = $this->resolveDateFilter($request);

        $vendor_id = 'all';
        if ($restaurant_id !== 'all') {
            $restaurant = Restaurant::find($restaurant_id);
            $vendor_id = $restaurant ? $restaurant->vendor_id : 'all';
        }

        $summary = $this->get_restaurant_earning_summary_data($vendor_id, $filter, $from, $to);
        
        return response()->json([
            'view' => view('admin-views.report.partials._restaurant-expense-breakdown', compact('summary'))->render()
        ]);
    }

    public function getRestaurantEarningTrend(Request $request)
    {
        $restaurant_id = $request->query('restaurant_id', 'all');
        [$filter, $from, $to] = $this->resolveDateFilter($request);

        $vendor_id = 'all';
        if ($restaurant_id !== 'all' && $restaurant_id !== null) {
            $restaurant = Restaurant::find($restaurant_id);
            $vendor_id = $restaurant ? $restaurant->vendor_id : 'all';
        }

        $trends = $this->get_restaurant_earning_trend_data($vendor_id, $filter, $from, $to);

        return response()->json($trends);
    }

    public function getRestaurantEarningTransactions(Request $request)
    {
        $restaurant_id = $request->query('restaurant_id', 'all');
        [$filter, $from, $to] = $this->resolveDateFilter($request);
        $type = $request->query('type', 'order');

        $vendor_id = 'all';
        if ($restaurant_id !== 'all') {
            $restaurant = Restaurant::find($restaurant_id);
            $vendor_id = $restaurant ? $restaurant->vendor_id : 'all';
        }

        if ($type === 'expense') {
            $transactions = $this->get_restaurant_expense_transactions($request, $restaurant_id, $filter, $from, $to);
        } elseif ($type === 'subscription') {
            $transactions = $this->get_restaurant_subscription_transactions($request, $restaurant_id, $filter, $from, $to);
        } else {
            $transactions = $this->get_restaurant_earning_transactions($request, $vendor_id, $filter, $from, $to);
        }

        $view = 'admin-views.report.partials._transaction_table';

        return response()->json([
            'transactions' => $transactions,
            'view' => view()->exists($view) ? view($view, compact('transactions', 'type'))->render() : ''
        ]);
    }

    public function exportRestaurantEarningTransactions(Request $request)
    {
        $restaurant_id = $request->query('restaurant_id', 'all');
        [$filter, $from, $to] = $this->resolveDateFilter($request);
        $type = $request->query('type', 'order');
        $export_type = $request->query('export_type', 'excel');

        $vendor_id = 'all';
        $restaurant_name = 'All';
        if ($restaurant_id !== 'all') {
            $restaurant = Restaurant::find($restaurant_id);
            $vendor_id = $restaurant ? $restaurant->vendor_id : 'all';
            $restaurant_name = $restaurant ? $restaurant->name : 'N/A';
        }

        if ($type === 'expense') {
            $transactions = $this->get_restaurant_expense_transactions($request, $restaurant_id, $filter, $from, $to, true);
            $title = 'Restaurant_Expense_Report';
        } elseif ($type === 'subscription') {
            $transactions = $this->get_restaurant_subscription_transactions($request, $restaurant_id, $filter, $from, $to, true);
            $title = 'Restaurant_Subscription_Report';
        } else {
            $transactions = $this->get_restaurant_earning_transactions($request, $vendor_id, $filter, $from, $to, true);
            $title = 'Restaurant_Earning_Report';
        }

        $data = [
            'transactions' => $transactions,
            'filter' => $filter,
            'from' => $from,
            'to' => $to,
            'search' => $request->search,
            'title' => $title,
            'restaurant_name' => $restaurant_name,
            'type' => $type,
        ];

        if ($export_type === 'csv') {
            return Excel::download(new RestaurantEarningTransactionExport($data), $title . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }
        return Excel::download(new RestaurantEarningTransactionExport($data), $title . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}
