<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\OrderTransaction;
use App\Models\ProCustomerTransaction;
use App\Models\SubscriptionTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ReportGeneratorTrait;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdminEarningTransactionExport;
use App\Exports\DeliverymanEarningTransactionExport;

class AdminEarningReportController extends Controller
{
    use ReportGeneratorTrait;

    public function getAdminEarningReport(Request $request)
    {
        return view('admin-views.report.admin-earning-report');
    }

    public function getAdminEarningSummary(Request $request)
    {
        [$filter, $from, $to] = $this->resolveDateFilter($request);

        $summary = $this->buildAdminEarningSummary($filter, $from, $to);
        $html = view('admin-views.report.partials._admin-earning-summary', compact('summary'))->render();
        return response()->json(['view' => $html]);
    }

    public function getAdminEarningBreakdown(Request $request)
    {
        [$filter, $from, $to] = $this->resolveDateFilter($request);

        $summary = $this->buildAdminEarningSummary($filter, $from, $to);
        $earnings = $this->buildEarningBreakdown($filter, $from, $to, $summary['admin_earning']);

         $earnings['subscription_earning'] = $summary['subscription_earning'];
         $earnings['subscription_percentage'] = $summary['subscription_percentage'];

         $earnings['pro_customer_subscription'] = $summary['pro_customer_subscription'];
         $earnings['pro_customer_subscription_percentage'] = $summary['pro_customer_subscription_percentage'];


        $html = view('admin-views.report.partials._admin-earning-breakdown', compact('earnings'))->render();
        return response()->json(['view' => $html, 'earnings' => $earnings]);
    }

    public function getAdminExpenseBreakdown(Request $request)
    {
        [$filter, $from, $to] = $this->resolveDateFilter($request);

        // require admin expense total for percentage calculations
        $summary = $this->buildAdminEarningSummary($filter, $from, $to);
        $expenses = $this->buildExpenseBreakdown($filter, $from, $to, $summary['admin_expense']);

        $html = view('admin-views.report.partials._admin-expense-breakdown', compact('expenses'))->render();
        return response()->json(['view' => $html]);
    }


    public function getMonthlyEarningsReport(Request $request)
    {
        [$filter, $from, $to] = $this->resolveDateFilter($request);

        $today = Carbon::now();
        $months = collect();
        $dateFormat = ($filter === 'this_week' || $filter === 'this_month') ? '%Y-%m-%d' : '%Y-%m';
        $singleDayCustom = $filter === 'custom' && $from && $to && $from === $to;

        if ($filter === 'this_year') {
            $startMonth = Carbon::now()->startOfYear();
            for ($i = 0; $i <= $today->month - 1; $i++) {
                $months->push($startMonth->copy()->addMonths($i)->format('Y-m'));
            }

        } elseif ($filter === 'this_month') {
            $daysInMonth = Carbon::now()->daysInMonth;
            $startOfMonth = Carbon::now()->startOfMonth();
            for ($i = 0; $i < $daysInMonth; $i++) {
                $months->push($startOfMonth->copy()->addDays($i)->format('Y-m-d'));
            }

        } elseif ($filter === 'this_week') {
            $startOfWeek = Carbon::now()->startOfWeek();
            for ($i = 0; $i <= 6; $i++) {
                $months->push($startOfWeek->copy()->addDays($i)->format('Y-m-d'));
            }

        } elseif ($filter === 'custom' && $from && $to) {
            $start = Carbon::parse($from)->startOfDay();
            $end = Carbon::parse($to)->endOfDay();
            $diffDays = $start->diffInDays($end);

            if ($diffDays > 365) {
                $dateFormat = '%Y';
                $temp = $start->copy()->startOfYear();
                while ($temp->year <= $end->year) {
                    $months->push($temp->format('Y'));
                    $temp->addYear();
                }
            } elseif ($diffDays > 31) {
                $dateFormat = '%Y-%m';
                $temp = $start->copy()->startOfMonth();
                // Ensure to cover the full range of months
                while ($temp->format('Y-m') <= $end->format('Y-m')) {
                    $months->push($temp->format('Y-m'));
                    $temp->addMonth();
                }
            } else {
                $dateFormat = '%Y-%m-%d';
                $temp = $start->copy();
                while ($temp->lte($end)) {
                    $months->push($temp->format('Y-m-d'));
                    $temp->addDay();
                }
            }

        } else {
            for ($i = 11; $i >= 0; $i--) {
                $months->push($today->copy()->subMonths($i)->format('Y-m'));
            }
        }

        $baseTransactionQuery = OrderTransaction::query()->whereNull('status')
            ->join('orders', 'orders.id', '=', 'order_transactions.order_id')
            ->applyDateFilter($filter, $from, $to, 'order_transactions.created_at');

        $earningFormula = $this->getAdminTotalEarningQuery();

        $earnings = $baseTransactionQuery
            ->selectRaw("DATE_FORMAT(order_transactions.created_at, '$dateFormat') as month")
            ->selectRaw("$earningFormula as total_earning")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_earning', 'month');

        // subscriptions
         $subscriptionQuery = SubscriptionTransaction::where('is_trial', 0)
            ->where('payment_status', 'success')
            ->applyDateFilter($filter, $from, $to, 'subscription_transactions.created_at')
            ->selectRaw("DATE_FORMAT(subscription_transactions.created_at, '$dateFormat') as month")
            ->selectRaw("SUM(paid_amount) as total_sub_earning")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_sub_earning', 'month');

        // pro customer subscriptions
        $proCustomerQuery = ProCustomerTransaction::where('payment_status', 'success')
            ->where('plan_type', 'paid')
            ->applyDateFilter($filter, $from, $to, 'pro_customer_transactions.created_at')
            ->selectRaw("DATE_FORMAT(pro_customer_transactions.created_at, '$dateFormat') as month")
            ->selectRaw("SUM(amount) as total_pro_customer_earning")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_pro_customer_earning', 'month');


            $expenses = Expense::where('created_by', 'admin')
                ->applyDateFilter($filter, $from, $to, 'expenses.created_at')
                ->selectRaw("DATE_FORMAT(expenses.created_at, '$dateFormat') as month")
                ->selectRaw("SUM(amount) as total_expense")
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total_expense', 'month');



        $earningSeries = $months->map(function ($m) use ($earnings, $subscriptionQuery, $proCustomerQuery) {
                $orderEarning = $earnings[$m] ?? 0;
                $subscriptionEarning = $subscriptionQuery[$m] ?? 0;
                $proCustomerEarning = $proCustomerQuery[$m] ?? 0;
                return round($orderEarning + $subscriptionEarning + $proCustomerEarning, 2);
            });
        $expenseSeries = $months->map(fn($m) => round($expenses[$m] ?? 0, 2));


        return response()->json([
            'categories' => $months->map(function ($m) use ($filter, $dateFormat, $singleDayCustom) {
                if ($filter === 'this_week') {
                    return Carbon::parse($m)->format('D');
                }
                if ($filter === 'this_month') {
                    return Carbon::parse($m)->format('j');
                }
                if ($filter === 'custom') {
                    if ($singleDayCustom) {
                        return Carbon::parse($m)->format('d M Y');
                    }
                    if ($dateFormat === '%Y') return $m;
                    if ($dateFormat === '%Y-%m') return Carbon::parse($m . '-01')->format('M');
                    if ($dateFormat === '%Y-%m-%d') return Carbon::parse($m)->format('j');
                }
                return Carbon::parse($m . '-01')->format('M');
            }),
            'earning_series' => $earningSeries,
            'expense_series' => $expenseSeries
        ]);
    }


    public function getZoneWiseEarnings(Request $request){

        [$filter, $from, $to] = $this->resolveDateFilter($request);

        $earningFormula =$this->getAdminTotalEarningQuery();


        $orderEarningsPerZone = DB::table('restaurants')
            ->join('orders', 'restaurants.id', '=', 'orders.restaurant_id')
            ->join('order_transactions', function($join) use ($filter, $from, $to) {
                $join->on('orders.id', '=', 'order_transactions.order_id');
                if ($filter === 'custom' && $from && $to) {
                    $join->whereBetween('order_transactions.created_at', ["$from 00:00:00", "$to 23:59:59"]);
                } elseif ($filter === 'this_year') {
                    $join->whereYear('order_transactions.created_at', now()->year);
                } elseif ($filter === 'this_month') {
                    $join->whereYear('order_transactions.created_at', now()->year)
                        ->whereMonth('order_transactions.created_at', now()->month);
                } elseif ($filter === 'previous_year') {
                    $join->whereYear('order_transactions.created_at', now()->year - 1);
                } elseif ($filter === 'this_week') {
                    $join->whereBetween('order_transactions.created_at', [
                        now()->startOfWeek()->format('Y-m-d H:i:s'),
                        now()->endOfWeek()->format('Y-m-d H:i:s')
                    ]);
                }
            })
            ->select('restaurants.zone_id')
            ->selectRaw("COALESCE($earningFormula, 0) as admin_earning")
            ->selectRaw("COUNT(order_transactions.id) as total_transactions")
            ->groupBy('restaurants.zone_id');


        $subscriptionEarningsPerZone = DB::table('restaurants')
            ->join('subscription_transactions as sub', function($join) use ($filter, $from, $to) {
                $join->on('restaurants.id', '=', 'sub.restaurant_id')
                    ->where('sub.payment_status', 'success')
                    ->where('sub.is_trial', 0);
                if ($filter === 'custom' && $from && $to) {
                    $join->whereBetween('sub.created_at', ["$from 00:00:00", "$to 23:59:59"]);
                } elseif ($filter === 'this_year') {
                    $join->whereYear('sub.created_at', now()->year);
                } elseif ($filter === 'this_month') {
                    $join->whereYear('sub.created_at', now()->year)
                        ->whereMonth('sub.created_at', now()->month);
                } elseif ($filter === 'previous_year') {
                    $join->whereYear('sub.created_at', now()->year - 1);
                } elseif ($filter === 'this_week') {
                    $join->whereBetween('sub.created_at', [
                        now()->startOfWeek()->format('Y-m-d H:i:s'),
                        now()->endOfWeek()->format('Y-m-d H:i:s')
                    ]);
                }
            })
            ->select('restaurants.zone_id')
            ->selectRaw("COALESCE(SUM(sub.paid_amount), 0) as subscription_earning")
            ->groupBy('restaurants.zone_id');


        $topZones = DB::table('zones')
            ->leftJoinSub($orderEarningsPerZone, 'oe', 'zones.id', '=', 'oe.zone_id')
            ->leftJoinSub($subscriptionEarningsPerZone, 'se', 'zones.id', '=', 'se.zone_id')
            ->select('zones.id', 'zones.name as zone_name')
            ->selectRaw("COALESCE(oe.admin_earning, 0) as admin_earning")
            ->selectRaw("COALESCE(se.subscription_earning, 0) as subscription_earning")
            ->selectRaw("COALESCE(oe.admin_earning, 0) + COALESCE(se.subscription_earning, 0) as total_earning")
            ->selectRaw("COALESCE(oe.total_transactions, 0) as total_transactions")
            ->selectRaw("(SELECT COUNT(DISTINCT id) FROM restaurants WHERE zone_id = zones.id) as total_restaurants")
            ->havingRaw("total_earning > 0")
            ->orderByDesc('total_earning')
            ->limit(10)
            ->get();

        $totalEarningsAllZones = $topZones->sum('total_earning') > 0
            ? DB::table('zones')
                ->leftJoinSub($orderEarningsPerZone, 'oe', 'zones.id', '=', 'oe.zone_id')
                ->leftJoinSub($subscriptionEarningsPerZone, 'se', 'zones.id', '=', 'se.zone_id')
                ->selectRaw("COALESCE(SUM(oe.admin_earning), 0) + COALESCE(SUM(se.subscription_earning), 0) as grand_total")
                ->value('grand_total')
            : 0;

        $topZones = $topZones->map(function($zone) use ($totalEarningsAllZones) {
            return [
                'zone_name'             => $zone->zone_name,
                'total_restaurants'     => $zone->total_restaurants,
                'total_earning'         => $zone->total_earning,
                'percentage_of_earning' => $totalEarningsAllZones > 0
                    ? round(($zone->total_earning / $totalEarningsAllZones) * 100, 2)
                    : 0,
            ];
        });


        $html = view('admin-views.report.partials._top_zones', compact('topZones'))->render();
        return response()->json(['view' => $html]);
    }

    public function getTopEarningRestaurants(Request $request){

        [$filter, $from, $to] = $this->resolveDateFilter($request);

        $earningFormula = $this->getAdminTotalEarningQuery();

        $subQuery = DB::table('subscription_transactions')
            ->select('restaurant_id')
            ->selectRaw("COALESCE(SUM(paid_amount), 0) as subscription_earning")
            ->where('payment_status', 'success')
            ->where('is_trial', 0)
            ->when(true, function($q) use ($filter, $from, $to) {
                if($filter === 'custom' && $from && $to){
                    $q->whereBetween('created_at', ["$from 00:00:00", "$to 23:59:59"]);
                } elseif($filter === 'this_year') {
                    $q->whereYear('created_at', now()->year);
                } elseif($filter === 'this_month') {
                    $q->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month);
                } elseif($filter === 'previous_year') {
                    $q->whereYear('created_at', now()->year - 1);
                } elseif($filter === 'this_week') {
                    $q->whereBetween('created_at', [
                        now()->startOfWeek()->format('Y-m-d H:i:s'),
                        now()->endOfWeek()->format('Y-m-d H:i:s')
                    ]);
                }
            })
            ->groupBy('restaurant_id');

        $topRestaurants = DB::table('restaurants')
            ->leftJoin('orders','restaurants.id','=','orders.restaurant_id')
            ->leftJoin('order_transactions','orders.id','=','order_transactions.order_id')
            ->leftJoinSub($subQuery, 'sub', 'restaurants.id', '=', 'sub.restaurant_id')
            ->leftJoin('zones','restaurants.zone_id','=','zones.id')
            ->select(
                'restaurants.id',
                'restaurants.logo',
                'restaurants.name as restaurant_name',
                'zones.name as zone_name'
            )
            ->selectRaw("COALESCE($earningFormula, 0) as admin_earning")
            ->selectRaw("COALESCE(sub.subscription_earning, 0) as subscription_earning")
            ->selectRaw("COALESCE($earningFormula, 0) + COALESCE(sub.subscription_earning, 0) as total_earning")
            ->selectRaw("COUNT(order_transactions.id) as total_transactions")
            ->selectSub(function($query) {
                $query->from('storages as storage')
                    ->whereColumn('storage.data_id','restaurants.id')
                    ->where('storage.data_type', \App\Models\Restaurant::class)
                    ->limit(1)
                    ->select('value');
            }, 'storage')
            ->when(true, function($q) use ($filter, $from, $to) {
                if($filter === 'custom' && $from && $to){
                    $q->where(function($q2) use ($from, $to) {
                        $q2->whereBetween('order_transactions.created_at', ["$from 00:00:00", "$to 23:59:59"])
                        ->orWhereNull('order_transactions.created_at');
                    });
                } elseif($filter === 'this_year') {
                    $q->where(function($q2) {
                        $q2->whereYear('order_transactions.created_at', now()->year)
                        ->orWhereNull('order_transactions.created_at');
                    });
                } elseif($filter === 'this_month') {
                    $q->where(function($q2) {
                        $q2->where(function($q3) {
                            $q3->whereYear('order_transactions.created_at', now()->year)
                            ->whereMonth('order_transactions.created_at', now()->month);
                        })->orWhereNull('order_transactions.created_at');
                    });
                } elseif($filter === 'previous_year') {
                    $q->where(function($q2) {
                        $q2->whereYear('order_transactions.created_at', now()->year - 1)
                        ->orWhereNull('order_transactions.created_at');
                    });
                } elseif($filter === 'this_week') {
                    $q->where(function($q2) {
                        $q2->whereBetween('order_transactions.created_at', [
                            now()->startOfWeek()->format('Y-m-d H:i:s'),
                            now()->endOfWeek()->format('Y-m-d H:i:s')
                        ])->orWhereNull('order_transactions.created_at');
                    });
                }
            })
            ->groupBy('restaurants.id', 'restaurants.name', 'zones.name', 'sub.subscription_earning')
            ->havingRaw("total_transactions > 0")
            ->havingRaw("total_earning > 0")
            ->orderByDesc('total_earning')
            ->limit(10)
            ->get()
            ->map(function($restaurant) {
                $restaurant->total_earning = $restaurant->admin_earning + $restaurant->subscription_earning;
                return $restaurant;
            });


        $html = view('admin-views.report.partials._top_restaurants', compact('topRestaurants'))->render();
        return response()->json(['view' => $html]);
    }

    public function getEarningTransactions(Request $request)
    {
        [$filter, $from, $to] = $this->resolveDateFilter($request);
        $type = $request->query('type', 'order'); // 'order', 'subscription', 'pro_customer', 'expense'

        if ($type === 'subscription') {
            $transactions = $this->get_subscription_earning_transactions($request, $filter, $from, $to);
        } elseif ($type === 'pro_customer') {
            $transactions = $this->get_pro_customer_subscription_transactions($request, $filter, $from, $to);
        } elseif ($type === 'expense') {
            $transactions = $this->get_expense_transactions($request, $filter, $from, $to);
        } else {
            $transactions = $this->get_order_earning_transactions($request, $filter, $from, $to);
        }

        $view = 'admin-views.report.partials._transaction_table';
        
        return response()->json([
            'transactions' => $transactions,
            'view' => view()->exists($view) ? view($view, [
                'transactions' => $transactions,
                'type' => $type,
                'use_additional_charge_name_in_breakdown' => true,
            ])->render() : ''
        ]);
    }
    public function getDeliverymanEarningTransactions(Request $request)
    {
        [$filter, $from, $to] = $this->resolveDateFilter($request);
        $type = $request->query('type', 'order'); // 'order', 'incentive'
        $delivery_man_id = $request->query('delivery_man_id', 'all');

        if ($type === 'incentive') {
            $transactions = $this->get_deliveryman_incentive_transactions($request, $delivery_man_id, $filter, $from, $to);
        } else {
            $transactions = $this->get_deliveryman_earning_transactions($request, $delivery_man_id, $filter, $from, $to);
        }

        $view = 'admin-views.report.partials._transaction_table_deliveryman'; 
        
        return response()->json([
            'transactions' => $transactions,
            'view' => view()->exists($view) ? view($view, compact('transactions', 'type'))->render() : ''
        ]);
    }

    public function exportEarningTransactions(Request $request)
    {
        [$filter, $from, $to] = $this->resolveDateFilter($request);
        $type = $request->query('type', 'order'); // 'order', 'subscription', 'pro_customer', 'expense'
        $export_type = $request->query('export_type', 'excel');

        if ($type === 'subscription') {
            $transactions = $this->get_subscription_earning_transactions($request, $filter, $from, $to, true);
            $title = 'Subscription_Earning_Report';
        } elseif ($type === 'pro_customer') {
            $transactions = $this->get_pro_customer_subscription_transactions($request, $filter, $from, $to, true);
            $title = 'Pro_Customer_Subscription_Report';
        } elseif ($type === 'expense') {
            $transactions = $this->get_expense_transactions($request, $filter, $from, $to, true);
            $title = 'Admin_Expense_Report';
        } else {
            $transactions = $this->get_order_earning_transactions($request, $filter, $from, $to, true);
            $title = 'Admin_Earning_Report';
        }

        $data = [
            'transactions' => $transactions,
            'filter' => $filter,
            'from' => $from,
            'to' => $to,
            'search' => $request->search,
            'title' => $title
        ];

        if ($export_type === 'csv') {
            return Excel::download(new AdminEarningTransactionExport($data), $title . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }
        return Excel::download(new AdminEarningTransactionExport($data), $title . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    public function exportDeliverymanEarningTransactions(Request $request)
    {
        [$filter, $from, $to] = $this->resolveDateFilter($request);
        $type = $request->query('type', 'order'); // 'order', 'incentive'
        $delivery_man_id = $request->query('delivery_man_id', 'all');
        $export_type = $request->query('export_type', 'excel');

        if ($type === 'incentive') {
            $transactions = $this->get_deliveryman_incentive_transactions($request, $delivery_man_id, $filter, $from, $to, true);
            $title = 'Deliveryman_Incentive_Report';
        } else {
            $transactions = $this->get_deliveryman_earning_transactions($request, $delivery_man_id, $filter, $from, $to, true);
            $title = 'Deliveryman_Earning_Report';
        }

        $delivery_man_name = 'All';
        if($delivery_man_id && $delivery_man_id !== 'all'){
            $dm = \App\Models\DeliveryMan::find($delivery_man_id);
            $delivery_man_name = $dm ? $dm->f_name . ' ' . $dm->l_name : 'N/A';
        }

        $data = [
            'transactions' => $transactions,
            'filter' => $filter,
            'from' => $from,
            'to' => $to,
            'search' => $request->search,
            'title' => $title,
            'type' => $type,
            'delivery_man_name' => $delivery_man_name
        ];

        if ($export_type === 'csv') {
            return Excel::download(new DeliverymanEarningTransactionExport($data), $title . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }
        return Excel::download(new DeliverymanEarningTransactionExport($data), $title . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}
