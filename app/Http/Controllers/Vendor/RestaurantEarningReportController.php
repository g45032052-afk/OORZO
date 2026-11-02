<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Restaurant;
use App\Traits\ReportGeneratorTrait;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Exports\RestaurantEarningTransactionExport;
use App\Exports\RestaurantSummaryReportExport;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\OrderDetail;
use App\Models\Food;
use Illuminate\Support\Facades\DB;

class RestaurantEarningReportController extends Controller
{
    use ReportGeneratorTrait;

    public function getRestaurantEarningReport(Request $request)
    {
        $restaurant = Restaurant::where('vendor_id', auth('vendor')->id())->first();
        if (!$restaurant) {
            abort(404);
        }

        $restaurant_id = $restaurant->id;
        
        return view('vendor-views.report.restaurant-earning-report', compact('restaurant_id'));
    }

    public function getTopSellingFoods(Request $request)
    {
        $restaurant = Restaurant::where('vendor_id', auth('vendor')->id())->first();
        $filter = $request->query('filter', 'all_time');
        $from = $filter === 'custom' ? $request->from : null;
        $to   = $filter === 'custom' ? $request->to : null;

        $top_selling_foods = OrderDetail::join('food', 'order_details.food_id', '=', 'food.id')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('food.restaurant_id', $restaurant->id)
            ->where('orders.order_status', 'delivered')
            ->applyDateFilter($filter, $from, $to, 'order_details.created_at')
            ->select(
                'food.id',
                'food.name',
                'food.price',
                'food.image',
                DB::raw('SUM(order_details.quantity) as total_sold'),
                DB::raw('SUM(order_details.price * order_details.quantity) as total_revenue')
            )
            ->groupBy('food.id', 'food.name', 'food.price', 'food.image')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        return response()->json([
            'view' => view('admin-views.report.partials._top-selling-foods', compact('top_selling_foods'))->render()
        ]);
    }

    public function getRestaurantEarningSummary(Request $request)
    {
        $restaurant = Restaurant::where('vendor_id', auth('vendor')->id())->first();
        $filter = $request->query('filter', 'all_time');
        $from = $filter === 'custom' ? $request->from : null;
        $to   = $filter === 'custom' ? $request->to : null;

        $summary = $this->get_restaurant_earning_summary_data($restaurant->id, $filter, $from, $to);
        
        return response()->json([
            'view' => view('admin-views.report.partials._restaurant-earning-summary', compact('summary'))->render()
        ]);
    }

    public function getRestaurantEarningBreakdown(Request $request)
    {
        $restaurant = Restaurant::where('vendor_id', auth('vendor')->id())->first();
        $filter = $request->query('filter', 'all_time');
        $from = $filter === 'custom' ? $request->from : null;
        $to   = $filter === 'custom' ? $request->to : null;

        $summary = $this->get_restaurant_earning_summary_data($restaurant->id, $filter, $from, $to);

        return response()->json([
            'view' => view('admin-views.report.partials._restaurant-earning-breakdown', compact('summary'))->render()
        ]);
    }

    public function getRestaurantExpenseBreakdown(Request $request)
    {
        $restaurant = Restaurant::where('vendor_id', auth('vendor')->id())->first();
        $filter = $request->query('filter', 'all_time');
        $from = $filter === 'custom' ? $request->from : null;
        $to   = $filter === 'custom' ? $request->to : null;

        $summary = $this->get_restaurant_earning_summary_data($restaurant->id, $filter, $from, $to);

        return response()->json([
            'view' => view('admin-views.report.partials._restaurant-expense-breakdown', compact('summary'))->render()
        ]);
    }

    public function getRestaurantEarningTrend(Request $request)
    {
        $restaurant = Restaurant::where('vendor_id', auth('vendor')->id())->first();
        $filter = $request->query('filter', 'all_time');
        $from = $filter === 'custom' ? $request->from : null;
        $to   = $request->to;

        $trends = $this->get_restaurant_earning_trend_data($restaurant->id, $filter, $from, $to);

        return response()->json($trends);
    }

    public function export(Request $request)
    {
        $restaurant = Restaurant::where('vendor_id', auth('vendor')->id())->first();
        if (!$restaurant) {
            abort(404);
        }

        $from = null;
        $to = null;
        $filter = $request->query('filter', 'all_time');
        if ($filter == 'custom') {
            $from = $request->from ?? null;
            $to = $request->to ?? null;
        }

        $restaurants = Restaurant::with('reviews', 'vendor')
            ->where('id', $restaurant->id)
            ->withSum('reviews', 'rating')
            ->withCount([
                'reviews',
                'orders',
                'foods' => function ($query) use ($from, $to, $filter) {
                    $query->withoutGlobalScopes()
                        ->applyDateFilter($filter, $from, $to);
                },
                'transaction as without_refund_total_orders_count' => function ($query) use ($from, $to, $filter) {
                    $query->NotRefunded()
                        ->applyDateFilter($filter, $from, $to);
                },
                'orders as canceled_orders' => function ($query) use ($from, $to, $filter) {
                    $query->whereIn('order_status', ['failed', 'canceled'])
                        ->applyDateFilterSchedule($filter, $from, $to);
                },
                'orders as on_going_orders' => function ($query) use ($from, $to, $filter) {
                    $query->whereNotIn('order_status', ['failed', 'canceled', 'delivered'])
                        ->applyDateFilterSchedule($filter, $from, $to);
                },
            ])
            ->withSum([
                'orders as wallet_payment' => function ($query) use ($from, $to, $filter) {
                    $query->where('payment_method', 'wallet')->has('transaction')
                        ->applyDateFilterSchedule($filter, $from, $to);
                },
            ], 'order_amount')
            ->withSum([
                'orders as cash_on_delivery' => function ($query) use ($from, $to, $filter) {
                    $query->where('payment_method', 'cash_on_delivery')->has('transaction')
                        ->applyDateFilterSchedule($filter, $from, $to);
                },
            ], 'order_amount')
            ->withSum([
                'orders as digital_payment' => function ($query) use ($from, $to, $filter) {
                    $query->whereNotIn('payment_method', ['cash_on_delivery', 'wallet'])->has('transaction')
                        ->applyDateFilterSchedule($filter, $from, $to);
                },
            ], 'order_amount')
            ->withSum([
                'transaction' => function ($query) use ($from, $to, $filter) {
                    $query->NotRefunded()
                        ->applyDateFilter($filter, $from, $to);
                },
            ], 'order_amount')
            ->withSum(['transaction' => function ($query) use ($from, $to, $filter) {
                $query->NotRefunded()
                    ->applyDateFilter($filter, $from, $to);
            },
            ], 'tax')
            ->withSum([
                'transaction as transaction_sum_restaurant_expense' => function ($query) use ($from, $to, $filter) {
                    $query->NotRefunded()
                        ->applyDateFilter($filter, $from, $to);
                },
            ], 'discount_amount_by_restaurant')
            ->withSum([
                'transaction' => function ($query) use ($from, $to, $filter) {
                    $query->NotRefunded()
                        ->applyDateFilter($filter, $from, $to);
                },
            ], 'admin_commission')
            ->get();

        $data = [
            'restaurants' => $restaurants,
            'search' => $request->search ?? null,
            'total_restaurants' => $restaurants->count(),
            'orders' => $restaurants->sum('orders_count'),
            'total_ongoing' => $restaurants->sum('on_going_orders'),
            'total_canceled' => $restaurants->sum('canceled_orders'),
            'cash_payments' => Helpers::number_format_short($restaurants->sum('cash_on_delivery')),
            'digital_payments' => Helpers::number_format_short($restaurants->sum('digital_payment')),
            'wallet_payments' => Helpers::number_format_short($restaurants->sum('wallet_payment')),
            'zone' => $restaurant->zone ? $restaurant->zone->name : null,
            'restaurant_model' => $restaurant->restaurant_model,
            'filter' => $filter,
        ];

        if ($request->export_type == 'csv') {
            return Excel::download(new RestaurantSummaryReportExport($data), 'RestaurantReport.csv');
        }
        return Excel::download(new RestaurantSummaryReportExport($data), 'RestaurantReport.xlsx');
    }

    public function getRestaurantEarningTransactions(Request $request)
    {
        $restaurant = Restaurant::where('vendor_id', auth('vendor')->id())->first();
        $filter = $request->query('filter', 'all_time');
        $from = $filter === 'custom' ? $request->from : null;
        $to   = $filter === 'custom' ? $request->to : null;
        $type = $request->query('type', 'order');

        if ($type === 'expense') {
            $transactions = $this->get_restaurant_expense_transactions($request, $restaurant->id, $filter, $from, $to);
        } elseif ($type === 'subscription') {
            $transactions = $this->get_restaurant_subscription_transactions($request, $restaurant->id, $filter, $from, $to);
        } else {
            $transactions = $this->get_restaurant_earning_transactions($request, $restaurant->vendor_id, $filter, $from, $to);
        }

        $view = 'admin-views.report.partials._transaction_table';

        $hide_source_column = true;

        return response()->json([
            'transactions' => $transactions,
            'view' => view()->exists($view) ? view($view, compact('transactions', 'type', 'hide_source_column'))->render() : ''
        ]);
    }

    public function exportRestaurantEarningTransactions(Request $request)
    {
        $restaurant = Restaurant::where('vendor_id', auth('vendor')->id())->first();
        if (!$restaurant) {
            abort(404);
        }

        [$filter, $from, $to] = $this->resolveDateFilter($request);
        $type = $request->query('type', 'order');
        $export_type = $request->query('export_type', 'excel');

        if ($type === 'expense') {
            $transactions = $this->get_restaurant_expense_transactions($request, $restaurant->id, $filter, $from, $to, true);
            $title = 'Restaurant_Expense_Report';
        } elseif ($type === 'subscription') {
            $transactions = $this->get_restaurant_subscription_transactions($request, $restaurant->id, $filter, $from, $to, true);
            $title = 'Restaurant_Subscription_Report';
        } else {
            $transactions = $this->get_restaurant_earning_transactions($request, $restaurant->vendor_id, $filter, $from, $to, true);
            $title = 'Restaurant_Earning_Report';
        }

        $data = [
            'transactions' => $transactions,
            'filter' => $filter,
            'from' => $from,
            'to' => $to,
            'search' => $request->search,
            'title' => $title,
            'restaurant_name' => $restaurant->name,
            'type' => $type,
        ];

        if ($export_type === 'csv') {
            return Excel::download(new RestaurantEarningTransactionExport($data), $title . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new RestaurantEarningTransactionExport($data), $title . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}
