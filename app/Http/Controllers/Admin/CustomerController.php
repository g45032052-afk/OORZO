<?php

namespace App\Http\Controllers\Admin;

use App\Models\ItemCampaign;
use App\Models\LoyaltyPointTransaction;
use App\Models\DataSetting;
use App\Models\ProCustomerSubscription;
use App\Models\ProCustomerSubscriptionPlan;
use App\Models\ProCustomerTransaction;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Newsletter;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use App\Exports\CustomerListExport;
use App\Exports\CustomerOrderExport;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SubscriberListExport;
use App\Models\CustomerAddress;
use App\Models\Food;
use App\Models\Review;
use App\Models\Wishlist;
use App\Scopes\RestaurantScope;
use Illuminate\Support\Facades\Cache;

class CustomerController extends Controller
{
    public function customer_list(Request $request)
    {
        $show_limit = $request->show_limit ?? null;

        // Storefront (Builder) customers are isolated by (tenant_id, sub_tenant_id);
        // admin bypasses HostScope, so split them into a host "Customers" tab
        // (0/0) and a "Storefront Customers" tab (sub_tenant_id > 0).
        ['tab' => $tab, 'storefronts' => $storefronts, 'storefront_id' => $storefront_id, 'builder_published' => $builder_published]
            = $this->customerTabContext($request);

        $customers = $this->getCustomerListData($request, $tab, $storefront_id);

        $counts = Cache::rememberForever('user_cache_dashboard_' . $tab, function () use ($tab) {
            return DB::table('users')
                ->when($tab === 'storefront',
                    fn ($q) => $q->where('sub_tenant_id', '>', 0),
                    fn ($q) => $q->where('tenant_id', 0)->where('sub_tenant_id', 0))
                ->selectRaw("
                    COUNT(*) as total_customers,
                    SUM(status = 1) as active_customers,
                    SUM(status = 0) as inactive_customers,
                    SUM(created_at > ?) as new_customers
                ", [Carbon::now()->subMonths(6)])
                ->first();
        });


        $total_customers = $counts->total_customers;
        $active_customers = $counts->active_customers;
        $inactive_customers = $counts->inactive_customers;
        $new_customers = $counts->new_customers;


        if (isset($show_limit) && $show_limit > 0) {
            $customers = $customers->take($show_limit)->get();
            $perPage = config('default_pagination');
            $page = $request?->page ?? 1;
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
        return view('admin-views.customer.list', compact('customers', 'active_customers', 'inactive_customers', 'new_customers', 'total_customers', 'tab', 'storefronts', 'storefront_id', 'builder_published'));
    }

    /**
     * Resolve the customer-list tab context. "main" = host customers (0,0);
     * "storefront" = Builder storefront customers (sub_tenant_id > 0), with an
     * optional single-storefront filter. Storefront tab only exists while the
     * Builder addon is published; $storefront_id is validated against the
     * currently-visible storefronts so a hand-crafted id can't slip through.
     */
    private function customerTabContext(Request $request): array
    {
        $builder_published = (bool) addon_published_status('Builder');
        $tab = ($request->tab === 'storefront' && $builder_published) ? 'storefront' : 'main';

        $publishedStoreIds = $builder_published
            ? DB::table('tenant_domain_configs')->where('website_visibility', 1)->pluck('sub_tenant_id')->unique()->values()
            : collect();
        $storefronts = $builder_published
            ? \App\Models\Restaurant::whereIn('id', $publishedStoreIds)->select('id', 'name')->orderBy('name')->get()
            : collect();

        $storefront_id = $request->storefront_id;
        if ($storefront_id !== null && !$publishedStoreIds->contains((int) $storefront_id)) {
            $storefront_id = null;
        }

        return compact('builder_published', 'tab', 'storefronts', 'storefront_id');
    }


    public function export(Request $request)
    {
        $order_wise = $request->order_wise ?? null;
        $show_limit = $request->show_limit ?? null;
        ['tab' => $tab, 'storefront_id' => $storefront_id] = $this->customerTabContext($request);
        $customers = $this->getCustomerListData($request, $tab, $storefront_id);
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
            $order_wise = translate('messages.Sort by newest');
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
            return Excel::download(new CustomerListExport($data), 'Customers.xlsx');
        } else if ($request->type == 'csv') {
            return Excel::download(new CustomerListExport($data), 'Customers.csv');
        }
    }


    private function getCustomerListData($request, string $tab = 'main', $storefront_id = null)
    {

        $zone_id = $request->zone_id ?? null;
        $filter = $request->filter ?? null;
        $order_wise = $request->order_wise ?? null;
        $key = [];
        if ($request->search) {
            $key = explode(' ', $request['search']);
        }

        $order_date_start = null;
        $order_date_end = null;

        $join_date_start = null;
        $join_date_end = null;

        $from_date = null;
        $to_date = $request->to_date == "" ? date('Y-m-d') : $request->to_date ?? null;

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
                $query->whereAny(['f_name', 'l_name', 'email', 'phone'], 'like', "%{$value}%");

            };
        })
            // Tab scoping (admin bypasses HostScope): "main" = host customers
            // (0,0); "storefront" = Builder storefront customers
            // (sub_tenant_id > 0), optionally narrowed to one storefront.
            ->when($tab === 'storefront',
                fn ($q) => $q->where('sub_tenant_id', '>', 0)
                    ->when($storefront_id, fn ($q2) => $q2->where('sub_tenant_id', (int) $storefront_id)),
                fn ($q) => $q->where('tenant_id', 0)->where('sub_tenant_id', 0))
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

            ->when(isset($request->join_date), function ($query) use ($join_date_start, $join_date_end) {
                $query->WhereBetween('created_at', [$join_date_start, $join_date_end]);
            })
            ->when(isset($from_date), function ($query) use ($from_date, $to_date) {
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

        return $customers;
    }



    public function status(User $customer)
    {
        $customer->status = !$customer->status;
        $customer->save();

        try {
            if ($customer->status == 0) {
                $customer->tokens->each(function ($token, $key) {
                    $token->delete();
                });

                $notification_status = Helpers::getNotificationStatusData('customer', 'customer_account_block');

                $message = Helpers::getPushNotificationMessage(status: 'customer_account_block', userType: 'user', lang: $customer?->cm_firebase_token, userName: $customer?->f_name . ' ' . $customer?->l_name);
                if ($message && isset($customer?->cm_firebase_token)) {
                    $data = Helpers::makeDataForPushNotification(title: translate('suspended'), message: $message, orderId: '', type: 'block', orderStatus: '');
                    Helpers::send_push_notif_to_device($customer?->cm_firebase_token, $data);
                    Helpers::insertDataOnNotificationTable($data, 'user', $customer->id);
                }

                $mail_status = Helpers::get_mail_status('suspend_mail_status_user');
                if ($notification_status?->mail_status == 'active' &&  config('mail.status') && $mail_status == '1') {
                    Mail::to($customer?->getRawOriginal('email'))->send(new \App\Mail\UserStatus('suspended', $customer->f_name . ' ' . $customer->l_name));
                }
            } else {

                $message = Helpers::getPushNotificationMessage(status: 'customer_account_unblock', userType: 'user', lang: $customer?->cm_firebase_token, userName: $customer?->f_name . ' ' . $customer?->l_name);
                if ($message && isset($customer?->cm_firebase_token)) {
                    $data = Helpers::makeDataForPushNotification(title: translate('account_activation'), message: $message, orderId: '', type: 'unblock', orderStatus: '');
                    Helpers::send_push_notif_to_device($customer?->cm_firebase_token, $data);
                    Helpers::insertDataOnNotificationTable($data, 'user', $customer->id);
                }

                $notification_status = Helpers::getNotificationStatusData('customer', 'customer_account_unblock');
                $mail_status = Helpers::get_mail_status('unsuspend_mail_status_user');
                if ($notification_status?->mail_status == 'active' &&  config('mail.status') && $mail_status == '1') {
                    Mail::to($customer?->getRawOriginal('email'))->send(new \App\Mail\UserStatus('unsuspended', $customer->f_name . ' ' . $customer->l_name));
                }
            }
        } catch (\Exception $ex) {
            info($ex->getMessage());
        }

        Toastr::success(translate('messages.customer_status_updated'));
        return back();
    }

    public function view($id)
    {
        $key = request()?->search ? explode(' ', request()?->search) : null;
        $customer = User::with(['lastOrder:orders.user_id,created_at', 'addresses'])
            ->withcount([
                'orders' => function ($query) {
                    $query->whereIn('order_status', ['delivered', 'refund_requested', 'refund_request_canceled']);
                }
            ])
            ->withSum([
                'orders as total_order_amount' => function ($query) {
                    $query->whereIn('order_status', ['delivered', 'refund_requested', 'refund_request_canceled']);
                }
            ], 'order_amount')->find($id);


        if (!$customer) {
            Toastr::error(translate('messages.customer_not_found'));
            return back();
        }
        $orderRange = DB::table('orders')
            ->where('user_id', $id)
            ->selectRaw('
                            MIN(order_amount) as min_amount,
                            MAX(order_amount) as max_amount,
                            COUNT(*) as total_orders,

                            SUM(CASE
                                WHEN order_status IN ("delivered", "refund_requested", "refund_request_canceled")
                                THEN 1 ELSE 0
                            END) as total_delivered,


                            SUM(CASE
                                WHEN order_status IN ("canceled", "failed")
                                THEN 1 ELSE 0
                            END) as total_canceled,


                            SUM(CASE
                                WHEN order_status = "refunded"
                                THEN 1 ELSE 0
                            END) as total_refunded,

                            SUM(CASE
                                WHEN order_status IN (
                                    "pending",
                                    "confirmed",
                                    "accepted",
                                    "processing",
                                    "handover",
                                    "picked_up"
                                )
                                THEN 1 ELSE 0
                            END) as total_on_going
                        ')
            ->first();


        $stats = DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('orders.user_id', $customer->id)
            ->whereIn('orders.order_status', ['delivered', 'refund_requested', 'refund_request_canceled'])
            ->selectRaw('
                SUM(order_details.quantity) as total_quantity,
                COUNT(DISTINCT orders.restaurant_id) as total_restaurants
            ')
            ->first();

        $itemsCount = $stats->total_quantity ?? 0;
        $restaurantsCount = $stats->total_restaurants ?? 0;
        return view('admin-views.customer.customer-details.customer-view', compact('customer', 'orderRange', 'itemsCount', 'restaurantsCount', ));
    }

    public function get_customers(Request $request)
    {
        $fullName = trim($request['q'] ?? '');
        $key = explode(' ', $fullName);

        // Host customers only — storefront (Builder) customers are isolated.
        $query = User::query()->where('tenant_id', 0)->where('sub_tenant_id', 0);

        if ($fullName !== '') {
            $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%");
                }
            });

            $query->orderByRaw("
            CASE
                WHEN CONCAT(f_name, ' ', l_name) = ? THEN 1
                WHEN f_name = ? THEN 2
                WHEN l_name = ? THEN 2
                WHEN phone = ? THEN 2
                WHEN f_name LIKE ? THEN 3
                WHEN l_name LIKE ? THEN 3
                WHEN phone LIKE ? THEN 3
                ELSE 4
            END,
            LENGTH(f_name) ASC,
            f_name ASC,
            l_name ASC
        ", [
                $fullName,
                $fullName,
                $fullName,
                $fullName,
                "%{$fullName}%",
                "%{$fullName}%",
                "%{$fullName}%",
            ]);
        } else {
            $query->orderBy('id', 'desc');
        }

        $data = $query->limit(8)
            ->get([
                DB::raw('id, CONCAT(f_name, " ", l_name, " (", phone ,")") as text')
            ]);

        if ($request->all) {
        $allOption = (object) [
            'id'   => $request->all_value === "all" ? "all" : false,
            'text' => translate('messages.all')
        ];

            $data->prepend($allOption);
        }
        return response()->json($data);
    }


    public function settings()
    {
        $data = BusinessSetting::where('key', 'like', 'wallet_%')
            ->orWhere('key', 'like', 'loyalty_%')
            ->orWhere('key', 'like', 'customer_%')
            ->orWhere('key', 'like', 'ref_earning_%')
            ->orWhere('key', 'like', 'ref_earning_%')->get();
        $data = array_column($data->toArray(), 'value', 'key');
        return view('admin-views.customer.settings', compact('data'));
    }

    public function update_settings(Request $request)
    {

        if (getEnvMode() == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));
            return back();
        }

        $request->validate([
            'add_fund_bonus' => 'nullable|numeric|max:100|min:0',
            'loyalty_point_exchange_rate' => 'nullable|numeric',
            'ref_earning_exchange_rate' => 'nullable|numeric',
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'wallet_status'], [
            'value' => $request['wallet_status'] ?? 0
        ]);
        // AI Personalization toggle (only meaningful when the AI module is enabled).
        if (\Nwidart\Modules\Facades\Module::find('AI')?->isEnabled()) {
            Helpers::businessUpdateOrInsert(['key' => 'customer_personalization_status'], [
                'value' => $request['customer_personalization_status'] ?? 0
            ]);
        }
        Helpers::businessUpdateOrInsert(['key' => 'loyalty_point_status'], [
            'value' => $request['customer_loyalty_point'] ?? 0
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'ref_earning_status'], [
            'value' => $request['ref_earning_status'] ?? 0
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'wallet_add_refund'], [
            'value' => $request['refund_to_wallet'] ?? 0
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'loyalty_point_exchange_rate'], [
            'value' => $request['loyalty_point_exchange_rate'] ?? 0
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'ref_earning_exchange_rate'], [
            'value' => $request['ref_earning_exchange_rate'] ?? 0
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'loyalty_point_item_purchase_point'], [
            'value' => $request['item_purchase_point'] ?? 0
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'loyalty_point_minimum_point'], [
            'value' => $request['minimun_transfer_point'] ?? 0
        ]);
        // Helpers::businessUpdateOrInsert(['key' => 'customer_verification'], [
        //     'value' => $request['customer_verification']
        // ]);
        Helpers::businessUpdateOrInsert(['key' => 'add_fund_status'], [
            'value' => $request['add_fund_status'] ?? 0
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'new_customer_discount_status'], [
            'value' => $request['new_customer_discount_status'] ?? 0
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'new_customer_discount_amount'], [
            'value' => $request['new_customer_discount_amount'] ?? 0
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'new_customer_discount_amount_type'], [
            'value' => $request['new_customer_discount_amount_type'] ?? 'percentage'
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'new_customer_discount_amount_validity'], [
            'value' => $request['new_customer_discount_amount_validity'] ?? 1
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'new_customer_discount_validity_type'], [
            'value' => $request['new_customer_discount_validity_type'] ?? 'day'
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'customer_add_fund_min_amount'], [
            'value' => $request['customer_add_fund_min_amount'] ?? 0
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'guest_checkout_status'], [
            'value' => $request['guest_checkout_status'] ?? 0
        ]);
        Helpers::businessUpdateOrInsert(['key' => 'pro_member_status'], [
            'value' => $request['pro_member_status'] ?? 0
        ]);
        Toastr::success(translate('messages.customer_settings_updated_successfully'));
        return back();
    }

    public function subscribedCustomers(Request $request)
    {
        $show_limit = $request->show_limit ?? null;
        $customers = $this->getSubscribersMail($request);
        if (isset($show_limit) && $show_limit > 0) {
            $customers = $customers->take($show_limit)->get();
            $perPage = config('default_pagination');
            $page = $request?->page ?? 1;
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
        $subscribers = $customers;

        return view('admin-views.customer.subscriber.list', compact('subscribers'));
    }


    public function subscribed_customer_export(Request $request)
    {
        $show_limit = $request->show_limit ?? null;
        $customers = $this->getSubscribersMail($request);
        if (isset($show_limit) && $show_limit > 0) {
            $customers = $customers->take($show_limit)->get();
        } else {
            $customers = $customers->get();
        }

        $data = [
            'customers' => $customers,
            'subscription_date' => $request?->join_date,
            'chose_first' => $show_limit,
            'search' => $request->search,
            'filter' => $request->filter ? translate('messages.Sort by') . ' ' . $request->filter : null,

        ];

        if ($request->type == 'excel') {
            return Excel::download(new SubscriberListExport($data), 'Subscribers.xlsx');
        } else if ($request->type == 'csv') {
            return Excel::download(new SubscriberListExport($data), 'Subscribers.csv');
        }
    }



    private function getSubscribersMail($request)
    {
        $filter = $request->filter ?? null;
        $join_date_start = null;
        $join_date_end = null;
        if ($request?->join_date) {
            list($join_date_start, $join_date_end) = explode(' - ', $request?->join_date);
            $join_date_start = Carbon::createFromFormat('m/d/Y', $join_date_start)->startOfDay();
            $join_date_end = Carbon::createFromFormat('m/d/Y', $join_date_end)->endOfDay();
        }
        $key = $request['search'] ? explode(' ', $request['search']) : null;
        $customers = Newsletter::when(isset($key), function ($query) use ($key) {
            $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('email', 'like', "%" . $value . "%");
                }
            });
        })
            ->when(isset($request->join_date), function ($query) use ($join_date_start, $join_date_end) {
                $query->WhereBetween('created_at', [$join_date_start, $join_date_end]);
            });


        if (isset($filter) && $filter == 'oldest') {
            $customers = $customers->oldest();
        } else {
            $customers = $customers->latest();
        }

        return $customers;
    }

    public function customer_order_export(Request $request)
    {
        try {
            $key = $request['search'] ? explode(' ', $request['search']) : null;
            $customer = User::find($request->id);

            $orders = Order::with('orderProDiscount')->latest()->where(['user_id' => $request->id])->Notpos()->where('is_guest', 0)
                ->when(isset($key), function ($q) use ($key) {
                    $q->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->Where('id', 'like', "%{$value}%");
                        }
                    });
                })
                ->get();
            $data = [
                'orders' => $orders,
                'customer_id' => $customer->id,
                'customer_name' => $customer->f_name . ' ' . $customer->l_name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
            ];
            if ($request->type == 'excel') {
                return Excel::download(new CustomerOrderExport($data), 'CustomerOrders.xlsx');
            } else if ($request->type == 'csv') {
                return Excel::download(new CustomerOrderExport($data), 'CustomerOrders.csv');
            }
        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);
            return back();
        }
    }

    public function topItems($id)
    {
        $customer = User::with('lastOrder:orders.user_id,created_at')
            ->withcount([
                'orders' => function ($query) {
                    $query->whereIn('order_status', ['delivered', 'refund_requested', 'refund_request_canceled']);
                }
            ])
            ->withSum([
                'orders as total_order_amount' => function ($query) {
                    $query->whereIn('order_status', ['delivered', 'refund_requested', 'refund_request_canceled']);
                }
            ], 'order_amount')->find($id);



        $foods = DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->leftJoin('food', 'food.id', '=', 'order_details.food_id')
            ->join('restaurants', 'restaurants.id', '=', 'orders.restaurant_id')
            ->leftJoin('item_campaigns', 'item_campaigns.id', '=', 'order_details.item_campaign_id')
            ->leftJoin('storages', function ($join) {
                $join->where(function ($q) {
                    $q->where(function ($q1) {
                        $q1->on('storages.data_id', '=', 'food.id')
                        ->where('storages.data_type', Food::class);
                    })->orWhere(function ($q2) {
                        $q2->on('storages.data_id', '=', 'item_campaigns.id')
                        ->where('storages.data_type', ItemCampaign::class);
                    });
                });
            })
            ->where('orders.user_id', $customer->id)
            ->whereIn('orders.order_status', ['delivered', 'refund_requested', 'refund_request_canceled'])
            ->where(function ($q) {
                $q->whereNotNull('order_details.food_id')
                  ->orWhereNotNull('order_details.item_campaign_id');
            })
            ->select(
                DB::raw('COALESCE(order_details.food_id, order_details.item_campaign_id) as id'),

                DB::raw("
                    CASE
                        WHEN order_details.food_id IS NOT NULL THEN 0
                        ELSE 1
                    END as is_campaign
                "),

                DB::raw("
                    CASE
                        WHEN order_details.food_id IS NOT NULL THEN food.name
                        ELSE item_campaigns.title
                    END as name
                "),

                DB::raw("
                    CASE
                        WHEN order_details.food_id IS NOT NULL THEN food.image
                        ELSE item_campaigns.image
                    END as image
                "),
                'restaurants.id as restaurant_id',
                'restaurants.name as restaurant_name',
                DB::raw('SUM(order_details.quantity) as order_count'),
                'storages.value',
                DB::raw('MAX(order_details.food_details) as food_details')
            )
            ->groupBy(
                DB::raw('COALESCE(order_details.food_id, order_details.item_campaign_id)'),
                DB::raw("
                    CASE
                        WHEN order_details.food_id IS NOT NULL THEN food.name
                        ELSE item_campaigns.title
                    END
                "),
                DB::raw("
                    CASE
                        WHEN order_details.food_id IS NOT NULL THEN food.image
                        ELSE item_campaigns.image
                    END
                "),
                'restaurants.id',
                'restaurants.name',
                'storages.value',
            )
            ->orderByDesc('order_count')
            ->limit(10)
            ->get();


        return response()->json([
            'view' => view('admin-views.customer.partials._top_items', compact('foods', 'customer'))->render(),
        ]);
    }


    public function addCustomerAddress($id)
    {
        $customer = User::find($id);
        return response()->json([
            'view' => view('admin-views.customer.partials._customer_address_add', compact('customer'))->render(),
        ]);
    }

    public function editCustomerAddress($id)
    {
        $address = CustomerAddress::where('id', $id)->first();
        $customer = User::find($address?->user_id);
        return response()->json([
            'latitude' => $address?->latitude,
            'longitude' => $address?->longitude,
            'view' => view('admin-views.customer.partials._customer_address_edit', compact('address', 'customer'))->render(),
        ]);
    }
    public function updateCustomerAddress(Request $request, $id)
    {
        $request->validate([
            'contact_person_name' => 'required|max:50',
            'contact_person_number' => [
                'required',
                'regex:/^\+[1-9][0-9]{6,14}$/',
                'max:20'
            ],
            'longitude' => 'required',
            'latitude' => 'required',
        ], [
            'latitude.required' => translate('Your_map_location_is_required'),
            'longitude.required' => translate('Your_map_location_is_required'),
        ]);

        $data = [
            'contact_person_name' => $request->contact_person_name,
            'contact_person_number' => $request->contact_person_number,
            'address_type' => $request->address_type ?? 'delivery',
            'address' => $request->address,
            'floor' => $request->floor,
            'road' => $request->road,
            'house' => $request->house,
            'longitude' => (string) $request->longitude,
            'latitude' => (string) $request->latitude,
        ];

        $selectedAddress = CustomerAddress::updateOrCreate(
            [
                'id' => $request->address_id,
                'user_id' => $id
            ],
            $data
        );
        Toastr::success(translate($request->address_id ? translate('customer_address_updated_successfully') : translate('customer_address_added_successfully')));
        return back();
    }

    public function getOrderList(Request $request, $id)
    {
        $from_date = null;
        $to_date = $request->to_date == "" ? date('Y-m-d') : $request->to_date ?? null;

        if ($request?->from_date && $request?->from_date != '') {
            $from_date = Carbon::createFromFormat('Y-m-d', $request->from_date)->startOfDay();
            $to_date = Carbon::createFromFormat('Y-m-d', $to_date)->endOfDay();
        }
        $order_status = $request->order_status ?? [];
        $order_type = $request->order_type ?? [];
        $payment_type = $request->payment_type ?? [];

        $customer = User::find($id);

        if (!$customer) {
            Toastr::error(translate('messages.customer_not_found'));
            return back();
        }
        $key = $request['search'] ? explode(' ', $request['search']) : null;
        $orders = Order::latest()->where(['user_id' => $id])->where('is_guest', 0)
            ->when(isset($key), function ($q) use ($key) {
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->Where('id', 'like', "%{$value}%");
                    }
                });
            })
            ->when(isset($from_date), function ($query) use ($from_date, $to_date) {
                $query->WhereBetween('created_at', [$from_date, $to_date]);
            })
            ->when(count($order_status) > 0 && !in_array('all', $order_status), function ($query) use ($order_status) {
                $query->whereIn('order_status', $order_status);
            })
            ->when(count($order_type) > 0 && !in_array('all', $order_type), function ($query) use ($order_type) {
                $query->whereIn('order_type', $order_type);
            })
            ->when(count($payment_type) > 0 && !in_array('all', $payment_type), function ($query) use ($payment_type) {
                $query->PaymentMethod($payment_type);
            })
            ->when($request->scheduled == 'scheduled', function ($query) {
                $query->Scheduled();
            })
            ->paginate(config('default_pagination'));

        return view('admin-views.customer.customer-details.order-list', compact('orders', 'customer', 'order_status', 'order_type', 'payment_type', 'from_date', 'to_date'));

    }
    public function getWishList(Request $request, $id)
    {
        $customer = User::find($id);

        if (!$customer) {
            Toastr::error(translate('messages.customer_not_found'));
            return back();
        }
        $foodWishList = Wishlist::with('food')->has('food')->where('user_id', $customer->id)->whereNull('restaurant_id')->paginate(config('default_pagination'), ['*'], 'foods_page');

        $restaurantWishList = Wishlist::where('user_id', $customer->id)->whereNull('food_id')->has('restaurant')
            ->with([
                'restaurant' => function ($query) {
                    $query->withAvg('reviews', 'rating');
                    $query->withCount('reviews');
                }
            ])
            ->paginate(config('default_pagination'), ['*'], 'restaurants_page');

        return view('admin-views.customer.customer-details.wishlist', compact('foodWishList', 'restaurantWishList', 'customer'));
    }

    public function getReviewList(Request $request, $id)
    {
        $customer = User::find($id);

        if (!$customer) {
            Toastr::error(translate('messages.customer_not_found'));
            return back();
        }
        $key = $request['search'] ? explode(' ', $request['search']) : null;
        $reviews = Review::where('user_id', $customer->id)->with([
            'restaurant',
            'food' => function ($q) {
                $q->withoutGlobalScope(RestaurantScope::class);
            }
        ])
            ->when(isset($key), function ($query) use ($key) {
                $query->whereHas('food', function ($query) use ($key) {
                    foreach ($key as $value) {
                        $query->where('name', 'like', "%{$value}%");
                    }
                });
            })
            ->latest()->paginate(config('default_pagination'));

        return view('admin-views.customer.customer-details.rating-reviews', compact('customer', 'reviews'));
    }

    public function getLoyaltyPointView(Request $request, $id)
    {
        $customer = User::find($id);

        if (!$customer) {
            Toastr::error(translate('messages.customer_not_found'));
            return back();
        }
        // Wallet / loyalty / referral are hidden for storefront customers.
        abort_if((int) ($customer->sub_tenant_id ?? 0) > 0, 404);

        $search = $request->search;
        $loyaltyPoints = LoyaltyPointTransaction::where('user_id', $customer->id)
            ->when($search, function ($q) use ($search) {
                $q->where('reference_id', 'like', "%{$search}%");
            })
            ->paginate(config('default_pagination'));
        $totalDebit = $customer?->loyalty_point_transaction()->sum('debit');
        $totalCredit = $customer?->loyalty_point_transaction()->sum('credit');
        return view('admin-views.customer.customer-details.loyalty-point', compact('customer', 'loyaltyPoints', 'totalDebit', 'totalCredit', 'search'));
    }
    public function getReferralView(Request $request, $id)
    {
        $customer = User::find($id);

        if (!$customer) {
            Toastr::error(translate('messages.customer_not_found'));
            return back();
        }
        // Wallet / loyalty / referral are hidden for storefront customers.
        abort_if((int) ($customer->sub_tenant_id ?? 0) > 0, 404);

        $search = $request->search;
        $referral = WalletTransaction::where('user_id', $customer->id)->where('transaction_type', 'referrer')
            ->when($search, function ($q) use ($search) {
                $q->where('reference_id', 'like', "%{$search}%");
            })
            ->select('id', 'credit', 'reference_id', 'created_at')
            ->paginate(config('default_pagination'));
        $totalJoinedByReferral = WalletTransaction::where('transaction_type', 'referrer')->where('user_id', $customer->id)->count();
        $totalEarnedByReferral = WalletTransaction::where('transaction_type', 'referrer')->where('user_id', $customer->id)->sum('credit');
        return view('admin-views.customer.customer-details.referral', compact('customer', 'referral', 'search', 'totalJoinedByReferral', 'totalEarnedByReferral'));
    }

    public function getWalletHistoryView(Request $request, $id)
    {
        $customer = User::find($id);

        if (!$customer) {
            Toastr::error(translate('messages.customer_not_found'));
            return back();
        }
        // Wallet / loyalty / referral are hidden for storefront customers.
        abort_if((int) ($customer->sub_tenant_id ?? 0) > 0, 404);

        $search = $request->search;
        $walletHistory = WalletTransaction::where('user_id', $customer->id)
            ->when($search, function ($q) use ($search) {
                $q->where('reference_id', 'like', "%{$search}%");
            })
            ->select('id', 'credit', 'reference_id', 'created_at', 'debit', 'admin_bonus','transaction_type', 'reference')
            ->paginate(config('default_pagination'));

        $totals = WalletTransaction::where('user_id', $customer->id)
            ->selectRaw('
                SUM(credit + admin_bonus) as total_credit,
                SUM(debit) as total_debit
            ')
            ->first();

        $totalCredit = $totals->total_credit ?? 0;
        $totalDebit  = $totals->total_debit ?? 0;


        return view('admin-views.customer.customer-details.wallet-history', compact('customer', 'walletHistory', 'search', 'totalCredit', 'totalDebit'));
    }

    public function getSubscriptionPlanView($id)
    {
        if ((int) Helpers::get_business_settings('pro_member_status') !== 1) {
            abort(404);
        }

        $customer = User::find($id);
        if (!$customer) {
            Toastr::error(translate('messages.customer_not_found'));
            return back();
        }


        $subscription = ProCustomerSubscription::with('plan')
            ->where('user_id', $customer->id)
            ->latest('id')
            ->first();

        // A canceled subscription can't be renewed/shifted — the customer must subscribe fresh.
        $isCanceled = $subscription && $subscription->status === 'canceled';


        $latestTransaction = ProCustomerTransaction::where('subscription_id', $subscription?->id)
            ->latest('id')
            ->first();
    

        $plans = ProCustomerSubscriptionPlan::where('status', 1)
            ->orderBy('duration')
            ->get();

        $benefits = DataSetting::where('type', 'pro_customer_benefits')
            ->pluck('value', 'key')
            ->toArray();

        $businessName = Helpers::get_business_settings('business_name') ?: 'StackFood';
        $proBrand = trim($businessName) . ' ' . translate('messages.Pro');

        return view('admin-views.customer.customer-details.subscription-plan', compact(
            'customer', 'subscription', 'latestTransaction', 'plans', 'benefits', 'proBrand', 'isCanceled'
        ));
    }

}
