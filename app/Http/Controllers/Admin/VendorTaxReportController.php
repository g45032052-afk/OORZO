<?php

namespace App\Http\Controllers\Admin;

use App\Exports\VendorTaxExport;
use App\Exports\VendorWiseTaxExport;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Maatwebsite\Excel\Facades\Excel;

class VendorTaxReportController extends Controller
{
    public function __construct()
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
    }
    public function vendorWiseTaxes(Request $request)
    {
        $dateRange = $request->dates ?? now()->subDays(6)->format('m/d/Y') . ' - ' . now()->format('m/d/Y');
        $key = explode(' ', $request['search']);

        list($startDate, $endDate) = explode(' - ', $dateRange);
        $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate));
        $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate));
        $startDate = $startDate->startOfDay();
        $endDate = $endDate->endOfDay();

        $restaurant_id = $request->query('restaurant_id', 'all');
        $restaurant = is_numeric($restaurant_id) ? Restaurant::findOrFail($restaurant_id) : null;

        // $start = microtime(true);

        $data = $this->vendorWiseTaxData($restaurant, $startDate, $endDate, $key);
        $result = $data['result'];

        $totalOrders = $result->total_orders;
        $totalOrderAmount = $result->total_order_amount;
        $totalTax = $result->total_tax;

        $restaurantQuery = $data['restaurantQuery'];
        $restaurantQuery =  $restaurantQuery->paginate(config('default_pagination'))->withQueryString();
        $restaurantIds = $restaurantQuery->pluck('restaurant_id')->toArray();

        $restaurants = $this->getOrderTaxData($startDate, $endDate, $restaurantIds, $restaurantQuery);
        // $time = microtime(true) - $start;
        // dd("Query took {$time} seconds", $restaurants);
        $startDate = Carbon::parse($startDate)->toIso8601String();
        $endDate = Carbon::parse($endDate)->toIso8601String();
        return view('admin-views.report.tax-report.vendor-tax-report', compact('totalOrders', 'totalOrderAmount', 'totalTax', 'restaurant', 'restaurants', 'dateRange', 'startDate', 'endDate'));
    }

    private function  vendorWiseTaxData($restaurant, $startDate, $endDate, $search)
    {
        $query = DB::table('orders')
            ->selectRaw('COUNT(*) as total_orders,
                        SUM(order_amount) as total_order_amount,
                        SUM(total_tax_amount) as total_tax')
            ->whereIn('order_status', ['delivered', 'refund_requested', 'refund_request_canceled']);

        if (isset($restaurant)) {
            $query->where('restaurant_id', $restaurant->id);
        }

        if (isset($startDate) && isset($endDate)) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if (isset($search)) {
            $query->whereExists(function ($subQuery) use ($search) {
                $subQuery->select(DB::raw(1))
                    ->from('restaurants')
                    ->whereRaw('restaurants.id = orders.restaurant_id')
                    ->where(function ($q) use ($search) {
                        foreach ($search as $value) {
                            $q->orWhere('restaurants.name', 'like', "%{$value}%");
                        }
                    });
            });
        }

        $result = $query->first();

        $restaurantQuery = DB::table('restaurants as restaurants')
            ->selectRaw(' restaurants.id as restaurant_id,
                            restaurants.name as restaurant_name,
                            restaurants.phone as restaurant_phone,
                            COUNT(DISTINCT orders.id) as total_orders,
                            SUM(orders.order_amount) as total_order_amount,
                            SUM(orders.total_tax_amount) as total_tax_amount ')
            ->join('orders as orders', function ($join) use ($startDate, $endDate) {
                $join->on('orders.restaurant_id', '=', 'restaurants.id')
                    ->whereIn('orders.order_status', ['delivered', 'refund_requested', 'refund_request_canceled']);

                if ($startDate && $endDate) {
                    $join->whereBetween('orders.created_at', [$startDate, $endDate]);
                }
            })
            ->when(isset($restaurant), fn($query) => $query->where('restaurants.id', $restaurant->id))
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    foreach ($search as $searchTerm) {
                        $q->orWhere('restaurants.name', 'like', "%{$searchTerm}%");
                    }
                });
            })->groupBy('restaurants.id');
        return [
            'result' => $result,
            'restaurantQuery' => $restaurantQuery,
        ];
    }

    private function getOrderTaxData($startDate, $endDate, $restaurantIds, $restaurantQuery, $export = false)
    {
        $taxGrouped = [];
        $taxQuery = DB::table('order_taxes as order_taxes')
            ->selectRaw('orders.restaurant_id, order_taxes.tax_name, SUM(order_taxes.tax_amount) as total_tax_amount')
            ->join('orders', 'order_taxes.order_id', '=', 'orders.id')
            ->where('order_taxes.order_type', 'App\\Models\\Order')
            ->whereIn('orders.order_status', ['delivered', 'refund_requested', 'refund_request_canceled'])
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('orders.created_at', [$startDate, $endDate]);
            })
            ->whereIn('orders.restaurant_id',  $restaurantIds)
            ->groupBy('orders.restaurant_id', 'order_taxes.tax_name')
            ->get();

        foreach ($taxQuery as $tax) {
            $taxGrouped[$tax->restaurant_id][] = [
                'tax_name' => $tax->tax_name,
                'total_tax_amount' => (float)$tax->total_tax_amount,
            ];
        }
        if ($export) {

            $restaurants = $restaurantQuery->map(function ($restaurant) use ($taxGrouped) {
                return (object)[
                    'restaurant_id' => $restaurant->restaurant_id,
                    'restaurant_name' => $restaurant->restaurant_name,
                    'restaurant_phone' => $restaurant->restaurant_phone,
                    'restaurant_total_tax_amount' => $restaurant->total_tax_amount,
                    'total_orders' => (int)$restaurant->total_orders,
                    'total_order_amount' => (float)$restaurant->total_order_amount,
                    'tax_data' => $taxGrouped[$restaurant->restaurant_id] ?? [],
                ];
            });

            return $restaurants;
        }
        $restaurants = $restaurantQuery->getCollection()->map(function ($restaurant) use ($taxGrouped) {
            return (object)[
                'restaurant_id' => $restaurant->restaurant_id,
                'restaurant_name' => $restaurant->restaurant_name,
                'restaurant_phone' => $restaurant->restaurant_phone,
                'total_orders' => (int)$restaurant->total_orders,
                'restaurant_total_tax_amount' => $restaurant->total_tax_amount,
                'total_order_amount' => (float)$restaurant->total_order_amount,
                'tax_data' => $taxGrouped[$restaurant->restaurant_id] ?? [],
            ];
        });


        $restaurants = $restaurantQuery->setCollection($restaurants);
        return $restaurants;
    }

    public function vendorWiseTaxExport(Request $request)
    {
        $dateRange = $request->dates ?? now()->subDays(6)->format('m/d/Y') . ' - ' . now()->format('m/d/Y');
        $key = explode(' ', $request['search']);

        list($startDate, $endDate) = explode(' - ', $dateRange);
        $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate));
        $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate));
        $startDate = $startDate->startOfDay();
        $endDate = $endDate->endOfDay();

        $restaurant_id = $request->query('restaurant_id', 'all');
        $restaurant = is_numeric($restaurant_id) ? Restaurant::findOrFail($restaurant_id) : null;

        // $start = microtime(true);

        $data = $this->vendorWiseTaxData($restaurant, $startDate, $endDate, $key);
        $summary = $data['result'];
        $restaurantQuery = $data['restaurantQuery'];
        $restaurantQuery =  $restaurantQuery->cursor();
        $restaurantIds = $restaurantQuery->pluck('restaurant_id')->toArray();

        $restaurants = $this->getOrderTaxData($startDate, $endDate, $restaurantIds, $restaurantQuery, true);

        $startDate = Carbon::parse($startDate)->toIso8601String();
        $endDate = Carbon::parse($endDate)->toIso8601String();
        $data = [
            'restaurants' => $restaurants,
            'search' => $request->search ?? null,
            'from' => $startDate,
            'to' => $endDate,
            'summary' => $summary
        ];
        // dd($request->export_type);
        if ($request->export_type == 'excel') {
            return Excel::download(new VendorWiseTaxExport($data), 'VendorWiseTaxExport.xlsx');
        } else if ($request->export_type == 'csv') {
            return Excel::download(new VendorWiseTaxExport($data), 'VendorWiseTaxExport.csv');
        }
    }


    public function vendorTax(Request $request)
    {

        $dateRange = $request->dates ?? now()->subDays(6)->format('m/d/Y') . ' - ' . now()->format('m/d/Y');

        list($startDate, $endDate) = explode(' - ', $dateRange);
        $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate));
        $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate));
        $startDate = $startDate->startOfDay();
        $endDate = $endDate->endOfDay();

        $restaurant_id = $request->id;
        $restaurant = is_numeric($restaurant_id) ? Restaurant::select('id', 'name', 'phone')->findOrFail($restaurant_id) : null;

        $vendortaxData =   $this->getVendortaxData($restaurant?->id, $startDate, $endDate);
        $summary =   $vendortaxData['summary'];
        $orders = $vendortaxData['orders'];

        $totalOrders = $summary->total_orders;
        $totalOrderAmount = $summary->total_order_amount;
        $totalTax = $summary->total_tax;

        $orders = $orders->paginate(config('default_pagination'))->withQueryString();
        $startDate = Carbon::parse($startDate)->format('d M, Y');
        $endDate = Carbon::parse($endDate)->format('d M, Y');
        return view('admin-views.report.tax-report.vendor-tax-detail-report', compact('totalOrders', 'totalOrderAmount', 'totalTax', 'restaurant', 'orders', 'startDate', 'endDate'));
    }

    public function vendorTaxExport(Request $request)
    {
        $dateRange = $request->dates ?? now()->subDays(6)->format('m/d/Y') . ' - ' . now()->format('m/d/Y');
        list($startDate, $endDate) = explode(' - ', $dateRange);
        $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate));
        $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate));
        $startDate = $startDate->startOfDay();
        $endDate = $endDate->endOfDay();

        $restaurant_id = $request->id;
        $restaurant = is_numeric($restaurant_id) ? Restaurant::select('id', 'name', 'phone')->findOrFail($restaurant_id) : null;


        $vendortaxData =   $this->getVendortaxData($restaurant->id, $startDate, $endDate);
        $summary =   $vendortaxData['summary'];
        $orders = $vendortaxData['orders'];

        $orders = $orders->cursor();

        $startDate = Carbon::parse($startDate)->format('d M, Y');
        $endDate = Carbon::parse($endDate)->format('d M, Y');

        $data = [
            'orders' => $orders,
            'search' => $request->search ?? null,
            'from' => $startDate,
            'to' => $endDate,
            'summary' => $summary
        ];

        if ($request->export_type == 'excel') {
            return Excel::download(new VendorTaxExport($data), $restaurant->name .'s TaxExport.xlsx');
        } else if ($request->export_type == 'csv') {
            return Excel::download(new VendorTaxExport($data),  $restaurant->name .'s TaxExport.csv');
        }
    }

    private function getVendortaxData($restaurant_id, $startDate, $endDate)
    {
        $summary = DB::table('orders')
            ->where('restaurant_id', $restaurant_id)
            ->whereIn('order_status', ['delivered', 'refund_requested', 'refund_request_canceled'])
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->selectRaw('COUNT(*) as total_orders, SUM(order_amount) as total_order_amount, SUM(total_tax_amount) as total_tax')
            ->first();

        $orders = Order::with([
            'orderTaxes' => function (MorphMany $query) {
                $query->where('order_type', Order::class)
                    ->select('id', 'order_id', 'tax_name', 'tax_amount','tax_on','tax_type');
                }
            ])
            ->where('restaurant_id', $restaurant_id)
            ->whereIn('order_status', ['delivered', 'refund_requested', 'refund_request_canceled'])
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->select(['id', 'order_amount', 'total_tax_amount','order_type' ,'created_at'])
            ->latest('created_at');

        return ['summary' => $summary, 'orders' => $orders];
    }
}
