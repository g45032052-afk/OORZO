<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerAnalyticsService
{
    public function getOverviewCounts()
    {
        return [
            'total_customers' => $this->getTotalCustomers(),
            'new_customers' => $this->getNewCustomers(),
            'active_customers' => $this->getActiveCustomers(),
            'inactive_customers' => $this->getInactiveCustomers(),
            'returning_customers' => $this->getReturningCustomers(),
            'engaged_customers' => $this->getEngagedCustomers(),
        ];
    }

    public function getTotalCustomers()
    {
        return User::count();
    }

    public function getNewCustomers($months = 6)
    {
        return User::where('created_at', '>=', Carbon::now()->subMonths($months))
            ->count();
    }

    public function getActiveCustomers()
    {
        return User::where('status', 1)->count();
    }

    public function getInactiveCustomers()
    {
        return User::where('status', 0)->count();
    }

    public function getReturningCustomers()
    {
        return User::whereHas('orders', function ($query) {
            $query->groupBy('user_id')
                ->havingRaw('COUNT(*) > 1');
        })->count();
    }


    public function getEngagedCustomers()
    {
        // Get customers with orders in the last 3 months
        // and have at least one order per month on average
        $threeMonthsAgo = Carbon::now()->subMonths(3);

        return User::whereHas('orders', function ($query) use ($threeMonthsAgo) {
            $query->where('created_at', '>=', $threeMonthsAgo)
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) >= 3'); // At least 1 order per month for 3 months
        })->count();
    }


    public function getOrderStatistics($filter = 'overall', $customFrom = null, $customTo = null)
    {
        $query = Order::selectRaw('is_pos, COUNT(*) as count, SUM(order_amount) as total_amount')
            ->groupBy('is_pos');

        $query = $this->applyDateFilter($query, $filter, $customFrom, $customTo);

        $results = $query->get();
        return [
            'pos_orders' => $results->where('is_pos', 1)->first()?->count ?? 0,
            'non_pos_orders' => $results->where('is_pos', 0)->sum('count') ?? 0,
            'pos_amount' => $results->where('is_pos', 1)->first()?->total_amount ?? 0,
            'non_pos_amount' => $results->where('is_pos', 0)->sum('total_amount') ?? 0,
        ];
    }

    public function getOnboardingStatistics($filter = 'yearly')
    {
        $query = User::query();

        switch ($filter) {

            case 'yearly':
                $query->selectRaw("YEAR(created_at) as label, COUNT(*) as count")
                    ->groupByRaw("YEAR(created_at)")
                    ->orderBy('label');
                break;

            case 'this_year':
                 $query->whereYear('created_at', Carbon::now()->year)
                        ->selectRaw("DATE_FORMAT(created_at, '%b') as label, COUNT(*) as count")
                        ->groupByRaw("MONTH(created_at)")
                        ->orderByRaw("MONTH(created_at)");
                break;

            case 'this_month':
                $query->whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->selectRaw("DATE_FORMAT(created_at, '%d %b') as label, COUNT(*) as count")
                ->groupByRaw("DATE(created_at)")
                ->orderBy('label');
                break;

            case 'this_week':
                $query->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])
                ->selectRaw("DAYNAME(created_at) as label, COUNT(*) as count")
                ->groupByRaw("DAYNAME(created_at)")
                ->orderByRaw("FIELD(DAYNAME(created_at),
                    'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')");
                break;

            case 'today':
                $query->whereDate('created_at', Carbon::today())
                ->selectRaw("HOUR(created_at) as label, COUNT(*) as count")
                ->groupByRaw("HOUR(created_at)")
                ->orderBy('label');
                break;
        }

        return $query->get();
    }

    public function getTopCustomers()
    {
        $topCustomerIds = Order::select(
                'user_id',
                DB::raw('COUNT(*) as order_count')
            )   
            ->where('is_guest', 0)
            ->groupBy('user_id')
            ->orderByDesc('order_count')
            ->limit(6)
            ->pluck('user_id')
            ->toArray();

        if (empty($topCustomerIds)) {
            return collect();
        }

        return User::whereIn('id', $topCustomerIds)
            ->select('id', 'f_name', 'l_name', 'email', 'phone', 'image', 'pro_status', 'created_at')
            ->with(['orders' => function ($q) {
                $q->where('is_guest', 0);
            }])
            ->orderByRaw('FIELD(id, ' . implode(',', $topCustomerIds) . ')')
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => trim($customer->f_name . ' ' . $customer->l_name),
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'image' => $customer->image_full_url ?? null,
                    'pro_status' => (int) $customer->pro_status,
                    'orders_count' => $customer->orders->count(),
                    'total_amount' => $customer->orders->sum('order_amount'),
                ];
            });
    }

    private function applyDateFilter($query, $filter, $customFrom = null, $customTo = null)
    {
        $now = Carbon::now();

        switch ($filter) {
            case 'today':
                $query->whereDate('created_at', $now->today());
                break;
            case 'this_week':
                $query->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupByRaw('DATE(created_at)')
                ->orderBy('date');
                break;
            case 'this_month':
                $query->whereYear('created_at', $now->year)
                    ->whereMonth('created_at', $now->month);
                break;
            case 'this_year':
                $query->whereYear('created_at', $now->year);
                break;
            case 'custom':
                if ($customFrom && $customTo) {
                    $query->whereBetween('created_at', [$customFrom, $customTo]);
                }
                break;
            default:
                break;
        }

        return $query;
    }
}
