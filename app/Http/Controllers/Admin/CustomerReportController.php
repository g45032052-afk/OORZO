<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CustomerOverviewReportExport;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CustomerAnalyticsService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CustomerReportController extends Controller
{
    protected $analyticsService;

    public function __construct(CustomerAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request)
    {
        $show_limit =  $request->show_limit ?? null;
        $customers = $this->getCustomerListData($request);
        $counts = Cache::rememberForever('user_cache_dashboard', function () {
            return DB::table('users')
                ->selectRaw("
                    COUNT(*) as total_customers,
                    SUM(status = 1) as active_customers,
                    SUM(status = 0) as inactive_customers,
                    SUM(created_at > ?) as new_customers
                ", [Carbon::now()->subMonths(6)])
                ->first();
        });


            $total_customers   = $counts->total_customers;
            $active_customers   = $counts->active_customers;
            $inactive_customers = $counts->inactive_customers;
            $new_customers      = $counts->new_customers;


        if (isset($show_limit) && $show_limit > 0) {
            $customers = $customers->take($show_limit)->get();
            $perPage = config('default_pagination');
            $page =  $request?->page ?? 1;
            $offset = ($page - 1) * $perPage;
            $itemsForCurrentPage = $customers->slice($offset, $perPage);
            $customers = new \Illuminate\Pagination\LengthAwarePaginator(
                $itemsForCurrentPage,
                $customers->count(),
                $perPage,
                $page,
                ['path' => Paginator::resolveCurrentPath(), 'query' => request()->query()]
            );
        } else {
            $customers = $customers->paginate(config('default_pagination'));
        }
        return view('admin-views.customer.report.customer-overview-report', compact('customers'));
    }

    private function getCustomerListData($request)
    {

        $zone_id =  $request->zone_id ?? null;
        $filter =  $request->filter ?? null;
        $order_wise =  $request->order_wise ?? null;
        $key = [];
        if ($request->search) {
            $key = explode(' ', $request['search']);
        }

        $order_date_start = null;
        $order_date_end = null;

        $join_date_start = null;
        $join_date_end = null;

        $from_date = null;
        $to_date =  $request->to_date == "" ? date('Y-m-d') : $request->to_date ?? null;

        if ($request?->order_date) {
            list($order_date_start, $order_date_end) = explode(' - ', $request?->order_date);
            $order_date_start = Carbon::createFromFormat('m/d/Y', $order_date_start)->startOfDay();
            $order_date_end = Carbon::createFromFormat('m/d/Y', $order_date_end)->endOfDay();
        }
        if ($request?->join_date) {
            list($join_date_start, $join_date_end) = explode(' - ', $request?->join_date);
            $join_date_start = Carbon::createFromFormat('m/d/Y', $join_date_start)->startOfDay();
            $join_date_end = Carbon::createFromFormat('m/d/Y', $join_date_end)->endOfDay();
        }

        if ($request?->from_date && $request?->from_date != '') {
            $from_date = Carbon::createFromFormat('Y-m-d', $request->from_date)->startOfDay();
            $to_date = Carbon::createFromFormat('Y-m-d', $to_date)->endOfDay();
        }


        $customers = User::when(count($key) > 0, function ($query) use ($key) {
            foreach ($key as $value) {
                $query->whereAny(['f_name' ,'l_name', 'email', 'phone'], 'like', "%{$value}%");

                };
            })
            ->with('lastOrder:orders.user_id,created_at')
            ->withcount([
                'orders' => function ($query) {
                    $query->whereIn('order_status', ['delivered', 'refund_requested', 'refund_request_canceled']);
                }
            ])
            ->withSum([
                'orders as total_order_amount' => function ($query) {
                    $query->whereIn('order_status', ['delivered', 'refund_requested', 'refund_request_canceled']);
                }
            ], 'order_amount')
            ->addSelect([
                'days_since_last_order' => Order::query()
                    ->selectRaw('COALESCE(DATEDIFF(NOW(), MAX(created_at)))')
                    ->whereColumn('orders.user_id', 'users.id'),
                'most_used_payment_method' => Order::query()
                    ->select('payment_method')
                    ->whereColumn('user_id', 'users.id')
                    ->whereNotNull('payment_method')
                    ->groupBy('payment_method')
                    ->orderByDesc(DB::raw('COUNT(*)'))
                    ->limit(1),
            ])

            ->when(isset($request->join_date), function ($query) use ($join_date_start, $join_date_end) {
                $query->WhereBetween('created_at', [$join_date_start, $join_date_end]);
            })
            ->when(isset($from_date) , function ($query) use ($from_date, $to_date) {
                $query->WhereBetween('created_at', [$from_date, $to_date]);
            })
            ->when(isset($request->order_date), function ($query) use ($order_date_start, $order_date_end) {
                $query->wherehas('orders', function ($query) use ($order_date_start, $order_date_end) {
                    $query->WhereBetween('created_at', [$order_date_start, $order_date_end]);
                });
            })
            ->when(isset($zone_id) && is_numeric($zone_id), function ($query) use ($zone_id) {
                $query->where('zone_id', $zone_id);
            })
            ->when(isset($filter) && $filter == 'active', function ($query) {
                $query->where('status', 1);
            })
            ->when(isset($filter) && $filter == 'blocked', function ($query) {
                $query->where('status', 0);
            })
            ->when(isset($filter) && $filter == 'new', function ($query) {
                $query->whereDate('created_at', '>=', now()->subDays(30)->format('Y-m-d'));
            })
            ->when(isset($order_wise) && $order_wise == 'top', function ($query) {
                $query->orderBy('orders_count', 'desc');
            })
            ->when(isset($order_wise) && $order_wise == 'least', function ($query) {
                $query->orderBy('orders_count', 'asc');
            })
            ->when(isset($order_wise) && $order_wise == 'latest', function ($query) {
                $query->latest();
            })
            ->when(isset($order_wise) && $order_wise == 'oldest', function ($query) {
                $query->oldest();
            })

            ->when(isset($order_wise) && $order_wise == 'order_amount', function ($query) {
                $query->orderByDesc('total_order_amount');
            })
            ->when(!$order_wise, function ($query) {
                $query->latest();
            });

        return  $customers;
    }

    public function export(Request $request)
    {
        $order_wise =  $request->order_wise ?? null;
        $show_limit =  $request->show_limit ?? null;
        $customers = $this->getCustomerListData($request);
        if (isset($show_limit) && $show_limit > 0) {
            $customers = $customers->take($show_limit)->get();
        } else {
            $customers = $customers->get();
        }

        if ($order_wise == 'top') {
            $order_wise = translate('messages.Sort by order count');
        } elseif ($order_wise == 'order_amount') {
            $order_wise = translate('messages.Sort by order amount');
        } elseif ($order_wise == 'oldest') {
            $order_wise = translate('messages.Sort by oldest');
        } elseif ($order_wise == 'latest') {
            $order_wise =  translate('messages.Sort by newest');
        }


        $data = [
            'customers' => $customers,
            'filter' => $request->filter ?? null,
            'order_wise' => $order_wise ?? null,
            'show_limit' => $request->show_limit ?? null,
            'order_date' => $request?->order_date,
            'join_date' => $request?->join_date,
            'from_date' => $request?->from_date,
            'to_date' => $request?->to_date,
            'search' => $request->search ?? null,

        ];

        if ($request->type == 'excel') {
            return Excel::download(new CustomerOverviewReportExport($data), 'Customers.xlsx');
        } else if ($request->type == 'csv') {
            return Excel::download(new CustomerOverviewReportExport($data), 'Customers.csv');
        }
    }

    public function overviewCountsPartial(Request $request)
    {
        try {
            $counts = $this->analyticsService->getOverviewCounts();
            return view('admin-views.customer.report.partials.overview-counts', ['counts' => $counts]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch overview counts',
            ], 500);
        }
    }

    public function orderStatisticsPartial(Request $request)
    {
        try {
            $filter = $request->input('filter', 'overall');
            $customFrom = $request->input('date_from');
            $customTo = $request->input('date_to');

            $stats = $this->analyticsService->getOrderStatistics($filter, $customFrom, $customTo);

            return view('admin-views.customer.report.partials.order-statistics', ['stats' => $stats, 'filter' => $filter]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order statistics',
            ], 500);
        }
    }

    public function onboardingStatisticsPartial(Request $request)
    {
        try {
            $filter = $request->input('filter', 'yearly');
            $data = $this->analyticsService->getOnboardingStatistics($filter);

            return view('admin-views.customer.report.partials.onboarding-statistics', ['data' => $data, 'filter' => $filter]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch onboarding statistics',
            ], 500);
        }
    }

    public function topCustomersPartial(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            $sortBy = $request->input('sort_by', 'orders');

            $customers = $this->analyticsService->getTopCustomers($limit, $sortBy);

            return view('admin-views.customer.report.partials.top-customers', ['customers' => $customers]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch top customers',
            ], 500);
        }
    }
}
