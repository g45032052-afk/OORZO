<?php

namespace App\Http\Controllers\Admin;

use App\Models\DataSetting;
use App\Models\Food;
use App\Models\Zone;
use App\Models\AddOn;
use App\Models\Order;
use App\Models\Refund;
use App\Models\Restaurant;
use App\Models\DeliveryMan;
use App\Models\OrderDetail;
use App\Models\Translation;
use App\Exports\OrderExport;
use App\Mail\RefundRejected;
use App\Models\ItemCampaign;
use App\Models\OrderPayment;
use App\Models\RefundReason;
use App\Traits\PlaceNewOrder;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\CentralLogics\EtaLogic;
use App\Models\BusinessSetting;
use App\Scopes\RestaurantScope;
use App\CentralLogics\OrderLogic;
use App\Models\OrderCancelReason;
use App\Exports\OrderRefundExport;
use Illuminate\Support\Facades\DB;
use App\CentralLogics\CustomerLogic;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RestaurantOrderlistExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use MatanYadaev\EloquentSpatial\Objects\Point;
use function JmesPath\search;

class OrderController extends Controller
{
    use PlaceNewOrder;
    public function list($status, Request $request)
    {
        $key = explode(' ', $request['search']);

        if (session()->has('zone_filter') == false) {
            session()->put('zone_filter', 0);
        }

        if (session()->has('order_filter') && $status != 'dine_in') {
            $request = json_decode(session('order_filter'));
        }

        Order::where(['checked' => 0])->update(['checked' => 1]);

        $orders = Order::with(['customer', 'restaurant'])
            ->when(isset($request->zone), function ($query) use ($request) {
                return $query->whereHas('restaurant', function ($q) use ($request) {
                    return $q->whereIn('zone_id', $request->zone);
                });
            })

            ->when($status == 'searching_for_deliverymen', function ($query) {
                return $query->SearchingForDeliveryman();
            })
            ->when($status == 'pending', function ($query) {
                return $query->Pending();
            })
            ->when($status == 'accepted', function ($query) {
                return $query->AccepteByDeliveryman();
            })
            ->when($status == 'processing', function ($query) {
                return $query->Preparing();
            })
            ->when($status == 'food_on_the_way', function ($query) {
                return $query->FoodOnTheWay();
            })
            ->when($status == 'delivered', function ($query) {
                return $query->Delivered();
            })
            ->when($status == 'canceled', function ($query) {
                return $query->Canceled();
            })
            ->when($status == 'failed', function ($query) {
                return $query->failed();
            })
            ->when($status == 'requested', function ($query) {
                return $query->Refund_requested();
            })
            ->when($status == 'rejected', function ($query) {
                return $query->Refund_request_canceled();
            })
            ->when($status == 'refunded', function ($query) {
                return $query->Refunded();
            })
            ->when($status == 'scheduled', function ($query) {
                return $query->Scheduled();
            })
            ->when($status == 'on_going', function ($query) {
                return $query->Ongoing();
            })
            ->when($status == 'dine_in', function ($query) {
                return $query->where('order_type', 'dine_in');
            })
            ->when(!in_array($status, ['all', 'scheduled', 'canceled', 'requested', 'refunded', 'delivered', 'failed', 'dine_in']), function ($query) {
                return $query->OrderScheduledIn(30);
            })
            ->when(isset($request->vendor), function ($query) use ($request) {
                return $query->whereHas('restaurant', function ($query) use ($request) {
                    return $query->whereIn('id', $request->vendor);
                });
            })
            ->when(isset($request->orderStatus) && $status == 'all', function ($query) use ($request) {
                return $query->whereIn('order_status', $request->orderStatus);
            })
            ->when(isset($request->scheduled) && $status == 'all', function ($query) {
                return $query->scheduled();
            })
            // Paid / Unpaid across every tab. The existing payment_status handling below is
            // scoped to the offline_payments tab only, so a converted COD order could not be
            // isolated anywhere in the list.
            ->when(in_array($request->payment_status, ['paid', 'unpaid'], true), function ($query) use ($request) {
                return $query->where('payment_status', $request->payment_status);
            })
            ->when(isset($request->order_type), function ($query) use ($request) {
                return $query->whereIn('order_type', $request->order_type);
            })
            ->when($request?->from_date != null && $request?->to_date != null, function ($query) use ($request) {
                return $query->whereBetween('created_at', [$request->from_date . " 00:00:00", $request->to_date . " 23:59:59"]);
            })
            ->when(isset($key), function ($query) use ($key) {
                return $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('id', 'like', "%{$value}%")
                            ->orWhere('order_status', 'like', "%{$value}%")
                            ->orWhere('transaction_reference', 'like', "%{$value}%")

                            ->orWhereHas('customer', function ($customerQuery) use ($value) {
                                $customerQuery->where('f_name', 'like', "%{$value}%")
                                    ->orWhere('l_name', 'like', "%{$value}%")
                                    ->orWhere('phone', 'like', "%{$value}%");
                            })

                            ->orWhere(function ($sub) use ($value) {
                                $sub->where('is_guest', 1)
                                    ->where(function ($json) use ($value) {
                                        $json->where('delivery_address->contact_person_name', 'like', "%{$value}%")
                                            ->orWhere('delivery_address->contact_person_number', 'like', "%{$value}%");
                                    });
                            });
                    }
                });
            })

            ->Notpos()
            ->hasSubscriptionToday()
            ->orderBy('schedule_at', 'desc')
            ->paginate(config('default_pagination'));

        $orderstatus = $request?->orderStatus ?? [];
        $scheduled = $request?->scheduled ?? 0;
        $vendor_ids = $request?->vendor ?? [];
        $zone_ids = $request?->zone ?? [];
        $from_date = $request?->from_date ?? null;
        $to_date = $request?->to_date ?? null;
        $order_type = $request?->order_type ?? [];
        // Echoed back so the sidebar keeps the chosen Paid/Unpaid radio after the filter runs.
        $payment_status = in_array($request?->payment_status, ['paid', 'unpaid'], true) ? $request->payment_status : null;
        $total = $orders->total();

        return view('admin-views.order.list', compact('orders', 'status', 'orderstatus', 'scheduled', 'vendor_ids', 'zone_ids', 'from_date', 'to_date', 'total', 'order_type', 'payment_status'));
    }

    public function export_orders($status, $type, Request $request)
    {

        try {
            $key = explode(' ', $request['search']);

            if (session()->has('zone_filter') == false) {
                session()->put('zone_filter', 0);
            }

            if (session()->has('order_filter')) {
                $request = json_decode(session('order_filter'));
            }

            $orders = Order::with(['customer', 'restaurant', 'refund'])
                ->when(isset($request->zone), function ($query) use ($request) {
                    return $query->whereHas('restaurant', function ($q) use ($request) {
                        return $q->whereIn('zone_id', $request->zone);
                    });
                })
                ->when($status == 'scheduled', function ($query) {
                    return $query->whereRaw('created_at <> schedule_at');
                })
                ->when($status == 'searching_for_deliverymen', function ($query) {
                    return $query->SearchingForDeliveryman();
                })
                ->when($status == 'pending', function ($query) {
                    return $query->Pending();
                })
                ->when($status == 'accepted', function ($query) {
                    return $query->AccepteByDeliveryman();
                })
                ->when($status == 'processing', function ($query) {
                    return $query->Preparing();
                })
                ->when($status == 'food_on_the_way', function ($query) {
                    return $query->FoodOnTheWay();
                })
                ->when($status == 'delivered', function ($query) {
                    return $query->Delivered();
                })
                ->when($status == 'canceled', function ($query) {
                    return $query->Canceled();
                })
                ->when($status == 'failed', function ($query) {
                    return $query->failed();
                })
                ->when($status == 'requested', function ($query) {
                    return $query->Refund_requested();
                })
                ->when($status == 'rejected', function ($query) {
                    return $query->Refund_request_canceled();
                })
                ->when($status == 'refunded', function ($query) {
                    return $query->Refunded();
                })
                ->when($status == 'rejected', function ($query) {
                    return $query->Refund_request_canceled();
                })
                ->when($status == 'scheduled', function ($query) {
                    return $query->Scheduled();
                })
                ->when($status == 'on_going', function ($query) {
                    return $query->Ongoing();
                })
                ->when($status == 'dine_in', function ($query) {
                    return $query->where('order_type', 'dine_in');
                })
                ->when(!in_array($status, ['all', 'scheduled', 'canceled', 'requested', 'refunded', 'delivered', 'failed', 'dine_in']), function ($query) {
                    return $query->OrderScheduledIn(30);
                })
                ->when(isset($request->vendor), function ($query) use ($request) {
                    return $query->whereHas('restaurant', function ($query) use ($request) {
                        return $query->whereIn('id', $request->vendor);
                    });
                })
                ->when(isset($request->orderStatus) && $status == 'all', function ($query) use ($request) {
                    return $query->whereIn('order_status', $request->orderStatus);
                })
                ->when(isset($request->scheduled) && $status == 'all', function ($query) {
                    return $query->scheduled();
                })
                ->when(in_array($request->payment_status, ['paid', 'unpaid'], true), function ($query) use ($request) {
                    return $query->where('payment_status', $request->payment_status);
                })
                ->when(isset($request->order_type), function ($query) use ($request) {
                    return $query->where('order_type', $request->order_type);
                })
                ->when($request?->from_date != null && $request?->to_date != null, function ($query) use ($request) {
                    return $query->whereBetween('created_at', [$request->from_date . " 00:00:00", $request->to_date . " 23:59:59"]);
                })
                ->when(isset($key), function ($query) use ($key) {
                    return $query->where(function ($q) use ($key) {
                        foreach ($key as $k => $value) {
                            $q->orWhere('id', 'like', "%{$value}%")
                                ->orWhere('order_status', 'like', "%{$value}%")
                                ->orWhere('transaction_reference', 'like', "%{$value}%");
                            $search[$k] = $value;
                        }
                    });
                })
                ->Notpos()
                ->orderBy('schedule_at', 'desc')
                ->hasSubscriptionToday()


                ->when($status == 'offline_payments' && $request?->payment_status == 'pending', function ($query) {
                    return $query->whereHas('offline_payments', function ($query) {
                        return $query->where('status', 'pending');
                    });
                })
                ->when($status == 'offline_payments' && $request?->payment_status == 'all', function ($query) {
                    return $query->has('offline_payments');
                })
                ->when($status == 'offline_payments' && $request?->payment_status == 'denied', function ($query) {
                    return $query->whereHas('offline_payments', function ($query) {
                        return $query->where('status', 'denied');
                    });
                })
                ->when($status == 'offline_payments' && $request?->payment_status == 'verified', function ($query) {
                    return $query->whereHas('offline_payments', function ($query) {
                        return $query->where('status', 'verified');
                    });
                })
                ->get();

            if (in_array($status, ['requested', 'rejected', 'refunded'])) {
                $data = [
                    'orders' => $orders,
                'type' => is_array($request->order_type) ? implode(', ', $request->order_type) : $status ?? translate('messages.all'),
                    'status' => $status,
                    'order_status' => isset($request->orderStatus) ? implode(', ', $request->orderStatus) : null,
                    'search' => $request->search ?? $key[0] ?? null,
                    'from' => $request->from_date ?? null,
                    'to' => $request->to_date ?? null,
                    'zones' => isset($request->zone) ? Helpers::get_zones_name($request->zone) : null,
                    'restaurant' => isset($request->vendor) ? Helpers::get_restaurant_name($request->vendor) : null,
                ];

                if ($type == 'excel') {
                    return Excel::download(new OrderRefundExport($data), 'RefundOrders.xlsx');
                } else if ($type == 'csv') {
                    return Excel::download(new OrderRefundExport($data), 'RefundOrders.csv');
                }
            }


            $data = [
                'orders' => $orders,
                'type' => is_array($request->order_type) ? implode(', ', $request->order_type) : $status ?? translate('messages.all'),
                'status' => $status,
                'order_status' => isset($request->orderStatus) ? implode(', ', $request->orderStatus) : null,
                'search' => $request->search ?? $key[0] ?? null,
                'from' => $request->from_date ?? null,
                'to' => $request->to_date ?? null,
                'zones' => isset($request->zone) ? Helpers::get_zones_name($request->zone) : null,
                'restaurant' => isset($request->vendor) ? Helpers::get_restaurant_name($request->vendor) : null,
            ];

            if ($type == 'excel') {
                return Excel::download(new OrderExport($data), 'Orders.xlsx');
            } else if ($type == 'csv') {
                return Excel::download(new OrderExport($data), 'Orders.csv');
            }
        } catch (\Exception $e) {
            dd($e);
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);
            return back();
        }
    }

    public function dispatch_list($status, Request $request)
    {

        $key = explode(' ', $request?->search);
        if (session()->has('order_filter')) {
            $request = json_decode(session('order_filter'));
            $zone_ids = $request?->zone ?? 0;
        }

        Order::where(['checked' => 0])->update(['checked' => 1]);

        $orders = Order::with(['customer', 'restaurant'])
            ->when(isset($request->zone), function ($query) use ($request) {
                return $query->whereHas('restaurant', function ($query) use ($request) {
                    return $query->whereIn('zone_id', $request->zone);
                });
            })
            ->when($status == 'searching_for_deliverymen', function ($query) {
                return $query->SearchingForDeliveryman();
            })
            ->when($status == 'on_going', function ($query) {
                return $query->Ongoing();
            })
            ->when(isset($request->vendor), function ($query) use ($request) {
                return $query->whereHas('restaurant', function ($query) use ($request) {
                    return $query->whereIn('id', $request->vendor);
                });
            })
            ->when(isset($key), function ($query) use ($key) {
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('id', 'like', "%{$value}%")
                            ->orWhere('order_status', 'like', "%{$value}%")
                            ->orWhere('transaction_reference', 'like', "%{$value}%");
                    }
                });
            })
            ->when($request?->from_date != null && $request?->to_date != null, function ($query) use ($request) {
                return $query->whereBetween('created_at', [$request->from_date . " 00:00:00", $request->to_date . " 23:59:59"]);
            })

            ->Notpos()
            ->OrderScheduledIn(30)
            ->hasSubscriptionToday()
            ->orderBy('schedule_at', 'desc')
            ->paginate(config('default_pagination'));

        $orderstatus = $request?->orderStatus ?? [];
        $scheduled = $request?->scheduled ?? 0;
        $vendor_ids = $request?->vendor ?? [];
        $zone_ids = $request?->zone ?? [];
        $from_date = $request?->from_date ?? null;
        $to_date = $request?->to_date ?? null;
        $total = $orders->total();

        return view('admin-views.order.distaptch_list', compact('orders', 'status', 'orderstatus', 'scheduled', 'vendor_ids', 'zone_ids', 'from_date', 'to_date', 'total'));
    }

    public function refund_settings(Request $request)
    {
        $refund_active_status = BusinessSetting::where(['key' => 'refund_active_status'])->first();
        $search = $request->search ?? '';
        $reasons = RefundReason::withoutGlobalScope('translate')->with('translations')
        ->when($search, function ($query) use ($search) {
            return $query->where('reason', 'like', "%{$search}%");
        })
        ->orderBy('id', 'desc')->paginate(config('default_pagination'));
        $language = BusinessSetting::where('key', 'language')->first();
        $language = $language->value ?? null;

        return view('admin-views.refund.index', compact('refund_active_status', 'reasons', 'language'));
    }

    public function refund_reason(Request $request)
    {
        $request->validate([
            'reason' => 'required|max:191',
        ]);

        if ($request->reason[array_search('default', $request->lang)] == '') {
            Toastr::error(translate('default_reason_is_required'));
            return back();
        }


        $reason = new RefundReason();
        $reason->reason = $request->reason[array_search('default', $request->lang)];
        $reason->save();
        $data = [];
        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang as $index => $key) {
            if ($default_lang == $key && !($request->reason[$index])) {
                if ($key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\RefundReason',
                        'translationable_id' => $reason->id,
                        'locale' => $key,
                        'key' => 'reason',
                        'value' => $reason->reason,
                    ));
                }
            } else {
                if ($request->reason[$index] && $key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\RefundReason',
                        'translationable_id' => $reason->id,
                        'locale' => $key,
                        'key' => 'reason',
                        'value' => $request->reason[$index],
                    ));
                }
            }
        }
        Translation::insert($data);
        Toastr::success(translate('Refund Reason Added Successfully'));
        return back();
    }
    public function reason_edit(Request $request)
    {
        $request->validate([
            'reason' => 'required|max:100',
        ]);
        if ($request->reason[array_search('default', $request->lang1)] == '') {
            Toastr::error(translate('default_reason_is_required'));
            return back();
        }
        $refund_reason = RefundReason::findOrFail($request->reason_id);
        $refund_reason->reason = $request->reason[array_search('default', $request->lang1)];
        $refund_reason->save();

        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang1 as $index => $key) {
            if ($default_lang == $key && !($request->reason[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\RefundReason',
                            'translationable_id' => $refund_reason->id,
                            'locale' => $key,
                            'key' => 'reason'
                        ],
                        ['value' => $refund_reason->reason]
                    );
                }
            } else {
                if ($request->reason[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\RefundReason',
                            'translationable_id' => $refund_reason->id,
                            'locale' => $key,
                            'key' => 'reason'
                        ],
                        ['value' => $request->reason[$index]]
                    );
                }
            }
        }


        Toastr::success(translate('Refund Reason Updated Successfully'));
        return back();
    }
    public function reason_status(Request $request)
    {
        $refund_reason = RefundReason::findOrFail($request->id);
        $refund_reason->status = $request->status;
        $refund_reason->save();
        Toastr::success(translate('messages.status_updated'));
        return back();
    }
    public function reason_delete(Request $request)
    {
        $refund_reason = RefundReason::findOrFail($request->id);
        $refund_reason?->translations()?->delete();
        $refund_reason->delete();
        Toastr::success(translate('Refund Reason Deleted Successfully'));
        return back();
    }

    public function order_refund_rejection(Request $request)
    {

        $request->validate([
            'order_id' => 'required',
            'admin_note' => 'nullable|string|max:65535',
        ]);
        Refund::where('order_id', $request->order_id)->update([
            'order_status' => 'refund_request_canceled',
            'admin_note' => $request?->admin_note ?? null,
            'refund_status' => 'rejected',
            'refund_method' => 'canceled',
        ]);

        $order = Order::Notpos()->find($request->order_id);
        $order->order_status = 'refund_request_canceled';
        $order->refund_request_canceled = now();
        $order->save();

        try {
            $notification_status = Helpers::getNotificationStatusData('customer', 'customer_refund_request_rejaction');

            if ($notification_status?->mail_status == 'active' && config('mail.status') && $order?->customer?->email && Helpers::get_mail_status('refund_request_deny_mail_status_user') == '1') {
                Mail::to($order->customer?->getRawOriginal('email'))->send(new RefundRejected($order->id));
            }
        } catch (\Throwable $th) {
            info($th->getMessage());
            Toastr::error(translate('messages.Failed_to_send_mail'));
        }

        Toastr::success(translate('Refund Rejection Successfully'));

        if (!Helpers::send_order_notification($order)) {
            Toastr::warning(translate('messages.push_notification_faild'));
        }
        return back();
    }


    public function refund_mode()
    {
        $refund_mode = BusinessSetting::where('key', 'refund_active_status')->first();
        Helpers::businessUpdateOrInsert(
            ['key' => 'refund_active_status'],
            ['value' => $refund_mode?->value == 1 ? 0 : 1,]
        );

        if ($refund_mode?->value) {
            Toastr::success(translate('messages.Refund_is_off'));
            return back();
        }
        Toastr::success(translate('messages.Refund_is_on'));
        return back();
    }



    public function details(Request $request, $id)
    {
        Session::forget('order_edit_cart');
        Session::forget('order_edit_logs');
        $order = Order::with([
            'offline_payments',
            'orderEditLogs',
            'payments',
            'subscription',
            'subscription.schedule_today',
            'details',
            'refund',
            'orderProDiscount',
            'restaurant' => function ($query) {
                return $query->withCount('orders');
            },
            'customer' => function ($query) {
                return $query->withCount('orders');
            },
            'delivery_man' => function ($query) {
                return $query->withCount('orders');
            },
            'details.food' => function ($query) {
                return $query->withoutGlobalScope(RestaurantScope::class);
            },
            'details.campaign' => function ($query) {
                return $query->withoutGlobalScope(RestaurantScope::class);
            }
        ])->where(['id' => $id])->Notpos()->first();
        if ($order) {
            if (
                ($order?->restaurant?->self_delivery_system && $order?->restaurant?->restaurant_model == 'commission') ||
                ($order?->restaurant?->restaurant_model == 'subscription' && $order?->restaurant?->restaurant_sub?->self_delivery == 1)
            ) {
                $deliveryMen = DeliveryMan::with('last_location')->where('restaurant_id', $order->restaurant_id)->available()->active()->get();
            } else {
                if ($order->restaurant !== null) {
                    $deliveryMen = DeliveryMan::with(['last_location', 'wallet'])->where('zone_id', $order->restaurant->zone_id)->where(function ($query) use ($order) {
                        $query->where('vehicle_id', $order->vehicle_id)->orWhereNull('vehicle_id');
                    })
                        ->available()->active()->get();
                } else {
                    $deliveryMen = DeliveryMan::with(['last_location', 'wallet'])->where('zone_id', '=', NULL)->where('vehicle_id', $order->vehicle_id)->active()->get();
                }
            }


            $deliveryMen = Helpers::deliverymen_list_formatting(data: $deliveryMen, restaurant_lat: $order?->restaurant?->latitude, restaurant_lng: $order?->restaurant?->longitude);


            $selected_delivery_man = DeliveryMan::with('last_location')->where('id', $order->delivery_man_id)->first() ?? [];
            if ($order->delivery_man) {
                $selected_delivery_man = Helpers::deliverymen_list_formatting(data: $selected_delivery_man, restaurant_lat: $order?->restaurant?->latitude, restaurant_lng: $order?->restaurant?->longitude, single_data: true);
            }

            // Null for a take-away order, a finished one, or a zone with no live ETA
            // configuration — the view simply omits the row in those cases.
            $eta = EtaLogic::forOrder($order);

            return view('admin-views.order.order-view', compact('order', 'deliveryMen', 'selected_delivery_man', 'eta'));
        } else {
            Toastr::info(translate('messages.no_more_orders'));
            return back();
        }
    }

    public function status(Request $request)
    {
        $request->validate([
            'reason' => 'required_if:order_status,canceled'
        ]);
        $order = Order::Notpos()->with(['subscription_logs', 'details'])->findOrFail($request->id);

        if (in_array($order->order_status, ['refunded', 'failed'])) {
            Toastr::warning(translate('messages.you_can_not_change_the_status_of_a_completed_order'));
            return back();
        }


        if (in_array($order->order_status, ['refund_requested']) && BusinessSetting::where(['key' => 'refund_active_status'])->first()?->value == false) {
            Toastr::warning(translate('Refund Option is not active. Please active it from Refund Settings'));
            return back();
        }

        if ($order['delivery_man_id'] == null && $request->order_status == 'out_for_delivery') {
            Toastr::warning(translate('messages.please_assign_deliveryman_first'));
            return back();
        }

        if ($request->order_status == 'delivered' && $order['transaction_reference'] == null && !in_array($order['payment_method'], ['cash_on_delivery', 'wallet'])) {
            Toastr::warning(translate('messages.add_your_paymen_ref_first'));
            return back();
        }

        if ($request->order_status == 'delivered') {

            if (isset($order->subscription_id) && count($order->subscription_logs) == 0) {
                Toastr::warning(translate('messages.You_Can_Not_Delivered_This_Subscription_order_Before_Schedule'));
                return back();
            }


            if ($order->transaction == null || isset($order->subscription_id)) {
                $unpaid_payment = OrderPayment::where('payment_status', 'unpaid')->where('order_id', $order->id)->first()?->payment_method;
                $unpaid_pay_method = 'digital_payment';
                if ($unpaid_payment) {
                    $unpaid_pay_method = $unpaid_payment;
                }
                if ($order->payment_method == "cash_on_delivery" || $unpaid_pay_method == 'cash_on_delivery') {
                    if (in_array($order['order_type'], ['dine_in', 'take_away'])) {
                        $ol = OrderLogic::create_transaction(order: $order, received_by: 'restaurant', status: null);
                    } else if ($order->delivery_man_id) {
                        $ol = OrderLogic::create_transaction(order: $order, received_by: 'admin', status: null);
                    } else if ($order->user_id) {
                        $ol = OrderLogic::create_transaction(order: $order, received_by: false, status: null);
                    }
                } else {
                    $ol = OrderLogic::create_transaction(order: $order, received_by: 'admin', status: null);
                }
                if (!$ol) {
                    Toastr::warning(translate('messages.faield_to_create_order_transaction'));
                    return back();
                }
            } else if ($order->delivery_man_id) {
                $order->transaction->update(['delivery_man_id' => $order->delivery_man_id]);
            }

            $order->payment_status = 'paid';
            if ($order->delivery_man) {
                $dm = $order->delivery_man;
                $dm->increment('order_count');
                $dm->current_orders = $dm->current_orders > 1 ? $dm->current_orders - 1 : 0;
                $dm->save();
            }
            $order->details->each(function ($item, $key) {
                if ($item->food) {
                    $item->food->increment('order_count');
                }
            });
            $order->customer ? $order->customer->increment('order_count') : '';
            $order->restaurant->increment('order_count');

            OrderLogic::update_unpaid_order_payment(order_id: $order->id, payment_method: $order->payment_method);
        } else if ($request->order_status == 'refunded' && BusinessSetting::where('key', 'refund_active_status')->first()?->value == 1) {
            if ($order->payment_status == "unpaid") {
                Toastr::warning(translate('messages.you_can_not_refund_a_cod_order'));
                return back();
            }
            if (isset($order->delivered)) {
                $rt = OrderLogic::refund_order($order);

                if (!$rt) {
                    Toastr::warning(translate('messages.faield_to_create_order_transaction'));
                    return back();
                }
            }
            $refund_method = $request->refund_method ?? 'manual';
            $wallet_status = BusinessSetting::where('key', 'wallet_status')->first()?->value;
            $refund_to_wallet = BusinessSetting::where('key', 'wallet_add_refund')->first()?->value;

            // A guest has no account and so no wallet to credit. create_wallet_transaction()
            // resolves the id with User::find() and then reads a property off the result, which
            // for a guest is null - so this threw a 500 from here *after* the order and its
            // transaction had already been written, leaving a half-refunded record the admin
            // could neither finish nor undo. A guest refund is settled by hand, exactly as one
            // is when the wallet feature is switched off.
            if ($order->is_guest == 0 && $order->payment_status == "paid" && $wallet_status == 1 && $refund_to_wallet == 1) {
                $refund_amount = round($order->order_amount - $order->delivery_charge - $order->dm_tips, config('round_up_to_digit'));
                CustomerLogic::create_wallet_transaction(user_id: $order->user_id, amount: $refund_amount, transaction_type: 'order_refund', referance: $order->id);
                Toastr::info(translate('Refunded amount added to customer wallet'));
                $refund_method = 'wallet';
            } else {
                // Told apart, because "the wallet feature is off" and "this customer has no
                // wallet" need different words to act on.
                Toastr::warning($order->is_guest
                    ? translate('messages.a_guest_order_has_no_wallet_refund_it_manually')
                    : translate('Customer Wallet Refund is not active.Plase Manage the Refund Amount Manually'));
                $refund_method = $request->refund_method ?? 'manual';
            }

            Refund::where('order_id', $order->id)->update([
                'order_status' => 'refunded',
                'admin_note' => $request->admin_note ?? null,
                'refund_status' => 'approved',
                'refund_method' => $refund_method,
            ]);

            Helpers::increment_order_count($order->restaurant);

            if ($order->delivery_man) {
                $dm = $order->delivery_man;
                $dm->current_orders = $dm->current_orders > 1 ? $dm->current_orders - 1 : 0;
                $dm->save();
            }
            try {
                $notification_status = Helpers::getNotificationStatusData('customer', 'customer_refund_request_approval');

                $message = Helpers::getOrderPushNotificationMessage($order, 'refunded', 'user' ,$order->customer ? $order?->customer?->current_language_key : 'en');
                $fcm_token = $order->customer ? $order->customer->cm_firebase_token : null;
                if ($message && isset($fcm_token)) {
                    $data= Helpers::makeDataForPushNotification(title:translate('order_refunded'), message:$message,orderId: $order->id, type: 'order_status', orderStatus: $order->order_status);
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                    Helpers::insertDataOnNotificationTable($data , 'user', $order->user_id);
                }

                if ($notification_status?->mail_status == 'active' && config('mail.status') && $order?->customer?->email && Helpers::get_mail_status('refund_order_mail_status_user') == '1') {
                    Mail::to($order->customer?->getRawOriginal('email'))->send(new \App\Mail\RefundedOrderMail($order->id));
                }
            } catch (\Throwable $th) {
                info($th->getMessage());
                Toastr::error(translate('messages.Failed_to_send_mail'));
            }
        } else if ($request->order_status == 'canceled') {
            if (in_array($order->order_status, ['delivered', 'canceled', 'refund_requested', 'refunded', 'failed'])) {
                Toastr::warning(translate('messages.you_can_not_cancel_a_completed_order'));
                return back();
            }
            if (isset($order->subscription_id)) {
                $order->subscription()->update(['status' => 'canceled']);
                if ($order?->subscription->log) {
                    $order->subscription->log->update([
                        'order_status' => $request->order_status,
                        'canceled' => now(),
                    ]);
                }
            }
            $order->cancellation_reason = $request->reason;
            $order->cancellation_note = $request->note;
            $order->canceled_by = 'admin';

            if ($order->delivery_man) {
                $dm = $order->delivery_man;
                $dm->current_orders = $dm->current_orders > 1 ? $dm->current_orders - 1 : 0;
                $dm->save();
            }
            Helpers::decreaseSellCount(order_details: $order->details);

            Helpers::increment_order_count($order->restaurant);
            OrderLogic::refund_before_delivered($order);
        }
        $order->order_status = $request->order_status;
        if ($request->order_status == 'processing') {
            $order->processing_time = ($request?->processing_time) ? $request->processing_time : explode('-', $order['restaurant']['delivery_time'])[0];
        }
        $order[$request->order_status] = now();
        $order->save();

        OrderLogic::update_subscription_log($order);
        if (!Helpers::send_order_notification($order)) {
            Toastr::warning(translate('messages.push_notification_faild'));
        }
        Toastr::success(translate('messages.order_status_updated'));
        return back();
    }

    public function add_delivery_man($order_id, $delivery_man_id)
    {
        if ($delivery_man_id == 0) {
            return response()->json(['message' => translate('messages.deliveryman_not_found')], 404);
        }
        $order = Order::Notpos()->with(['subscription.schedule_today'])->find($order_id);
        $deliveryman = DeliveryMan::where('id', $delivery_man_id)->available()->active()->first();
        if ($order->delivery_man_id == $delivery_man_id) {
            return response()->json(['message' => translate('messages.order_already_assign_to_this_deliveryman')], 400);
        }
        if ($deliveryman) {
            if ($deliveryman->current_orders >= config('dm_maximum_orders')) {
                return response()->json(['message' => translate('messages.dm_maximum_order_exceed_warning')], 400);
            }
            $cash_in_hand = $deliveryman?->wallet?->collected_cash ?? 0;
            $dm_max_cash = BusinessSetting::where('key', 'dm_max_cash_in_hand')->first();
            $cash_in_hand_overflow_delivery_man=Helpers::get_business_data('cash_in_hand_overflow_delivery_man');
            $value = $dm_max_cash?->value ?? 0;
            if ($order->payment_method == "cash_on_delivery" && $cash_in_hand_overflow_delivery_man && (($cash_in_hand + $order->order_amount) >= $value)) {
                return response()->json(['message' => translate('delivery man max cash in hand exceeds')], 400);
            }
            if ($order->delivery_man) {
                $dm = $order->delivery_man;
                $dm->current_orders = $dm->current_orders > 1 ? $dm->current_orders - 1 : 0;
                // $dm->decrement('assigned_order_count');
                $dm->save();

                $message =Helpers::getPushNotificationMessage(status:'deliveryman_order_unassign',userType: 'deliveryman' , lang:$dm->current_language_key, deliveryManName:$dm->f_name.' '.$dm->l_name, orderId:$order->id);
                if ( $message && isset($dm->fcm_token)) {
                    $data= Helpers::makeDataForPushNotification(title:translate('order_unassigned'), message:$message,orderId: '', type: 'unassign', orderStatus: '');
                    Helpers::send_push_notif_to_device($dm->fcm_token, $data);
                    Helpers::insertDataOnNotificationTable($data , 'delivery_man', $dm->id);
                }
            }
            $order->delivery_man_id = $delivery_man_id;
            $order->order_status = in_array($order->order_status, ['pending', 'confirmed']) ? 'accepted' : $order->order_status;
            $order->accepted = now();
            $order->save();
            OrderLogic::update_subscription_log($order);
            $deliveryman->current_orders = $deliveryman->current_orders + 1;
            $deliveryman->save();
            $deliveryman->increment('assigned_order_count');




            try {

                $fcm_token = ($order->is_guest == 0 ? $order?->customer?->cm_firebase_token : $order?->guest?->fcm_token) ?? null;
                 $message = Helpers::getOrderPushNotificationMessage($order, 'accepted', 'user' ,$order->customer ? $order?->customer?->current_language_key : 'en');

                if ($message && isset($fcm_token)) {
                    $data= Helpers::makeDataForPushNotification(title:translate('Order_notification'), message:$message,orderId: $order->id, type: 'order_status', orderStatus: $order->order_status);
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                    Helpers::insertDataOnNotificationTable($data , 'user', $order->user_id);
                }

                $message =Helpers::getPushNotificationMessage(status:'deliveryman_order_assign',userType: 'deliveryman' , lang:$deliveryman->current_language_key, deliveryManName:$deliveryman->f_name.' '.$deliveryman->l_name, orderId:$order->id);
                if ( $message && isset($deliveryman->fcm_token)) {
                    $data= Helpers::makeDataForPushNotification(title:translate('Order Assigned'), message:$message,orderId: $order->id, type: 'assign', orderStatus: '');
                    Helpers::send_push_notif_to_device($deliveryman->fcm_token, $data);
                    Helpers::insertDataOnNotificationTable($data , 'delivery_man', $deliveryman->id);
                }

            } catch (\Exception $e) {
                info($e->getMessage());
                Toastr::warning(translate('messages.push_notification_faild'));
            }
            return response()->json([], 200);
        }
        return response()->json(['message' => translate('Deliveryman not available!')], 400);
    }
    public function update_shipping(Request $request, Order $order)
    {

        $request->validate([
            'contact_person_name' => 'required',
            'address_type' => 'required',
            'contact_person_number' => 'required',
        ]);
        if ($request->latitude && $request->longitude) {
            $zone = Zone::where('id', $order->restaurant->zone_id)->whereContains('coordinates', new Point($request->latitude, $request->longitude, POINT_SRID))->first();
            if (!$zone) {
                Toastr::error(translate('messages.out_of_coverage'));
                return back();
            }
        }
        $address = [
            'contact_person_name' => $request->contact_person_name,
            'contact_person_number' => $request->contact_person_number,
            'address_type' => $request->address_type,
            'address' => $request->address,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'floor' => $request->floor,
            'house' => $request->house,
            'road' => $request->road,
        ];

        $order->delivery_address = json_encode($address);
        $order->save();
        $this->makeEditOrderLogs($order->id, 'edited_delivery_info', 'admin');

        Toastr::success(translate('messages.delivery_address_updated'));
        return back();
    }

    public function generate_invoice($id)
    {
        $order = Order::Notpos()->where('id', $id)->with(['payments'])->first();
        $invoiceSettings = DataSetting::invoiceSettings();
        return view('admin-views.order.invoice', compact('order','invoiceSettings'));
    }

    public function add_payment_ref_code(Request $request, $id)
    {
        $request->validate([
            'transaction_reference' => 'max:30'
        ]);
        Order::Notpos()->where(['id' => $id])->update([
            'transaction_reference' => $request['transaction_reference']
        ]);

        Toastr::success(translate('messages.payment_reference_code_is_added'));
        return back();
    }

    public function restaurnt_filter($id)
    {
        session()->put('restaurnt_filter', $id);
        return back();
    }

    public function filter(Request $request)
    {
        $request->validate([
            'from_date' => 'required_if:to_date,true',
            'to_date' => 'required_if:from_date,true',
        ]);
        session()->put('order_filter', json_encode($request->all()));
        return back();
    }
    public function filter_reset(Request $request)
    {
        session()->forget('order_filter');
        return back();
    }



    public function remove_from_cart(Request $request)
    {
        $carts = Session::get('order_edit_cart', []);

        // A bundle is shown as one row, so removing that row removes the whole group - half a
        // bundle is not a thing the offer can express.
        $target = collect($carts)->firstWhere('cart_id', $request->cart_id);
        $groupId = data_get($target, 'bogo_group_id');

        foreach ($carts as $key => $cart) {
            $isTarget = $groupId
                ? data_get($cart, 'bogo_group_id') === $groupId
                : $cart['cart_id'] == $request->cart_id;

            if (! $isTarget) {
                continue;
            }

            if (data_get($cart, 'order_detail_id')) {
                $edit_logs = Session::get('order_edit_logs', []);
                $edit_logs[] = 'delete_item';
                Session::put('order_edit_logs', $edit_logs);
            }
            unset($carts[$key]);

            if (! $groupId) {
                break;
            }
        }
        $carts = array_values($carts);
        foreach ($carts as $index => &$cart) {
            $cart['cart_id'] = $index + 1;
        }
        Session::put('order_edit_cart', $carts);
        return response()->json([
            'view' => view('admin-views.order.partials._food_list', compact('carts'))->render(),
        ]);
    }
    public function getSearchedFoods(Request $request)
    {

        $foods = Food::withoutGlobalScope(RestaurantScope::class)->where('restaurant_id', $request->restaurant_id)
            ->active()
            ->search(keywords:$request->search,mainCol:'name')
            ->paginate(10);


        return response()->json([
            'view' => view('admin-views.order.partials._searched_food_list', compact('foods'))->render(),
        ]);
    }

    public function add_to_cart(Request $request)
    {
        $old_selected_addons = [];
        $old_selected_variations = [];
        $old_selected_without_variation = $request?->old_selected_without_variation ?? 0;

        if ($request?->old_selected_variations) {
            $old_selected_variations = json_decode($request->old_selected_variations, true) ?? [];
        }
        if ($request?->old_selected_addons) {
            $old_selected_addons = json_decode($request->old_selected_addons, true) ?? [];
        }

        if ($request->item_type == 'campaign') {
            $product = ItemCampaign::withOutGlobalScope(RestaurantScope::class)->find($request->id);
        } else {
            $product = Food::withOutGlobalScope(RestaurantScope::class)->with('restaurant')->find($request->id);
        }

        $variations = [];
        $price = 0;
        $addon_price = 0;
        $variation_price = 0;
        $optionIds = [];
        $carts = [];
        $add_ons = [];
        $add_on_qtys = [];
        $add_on_ids = [];

        $product_variations = json_decode($product->variations, true);
        if ($request->variations && count($product_variations)) {
            foreach ($request->variations as $value) {

                if ($value['required'] == 'on' && isset($value['values']) == false) {
                    return response()->json([
                        'data' => 'variation_error',
                        'message' => translate('Please select items from') . ' ' . $value['name'],
                    ]);
                }
                if (isset($value['values']) && $value['min'] != 0 && $value['min'] > count($value['values']['label'])) {
                    return response()->json([
                        'data' => 'variation_error',
                        'message' => translate('Please select minimum ') . $value['min'] . translate(' For ') . $value['name'] . '.',
                    ]);
                }
                if (isset($value['values']) && $value['max'] != 0 && $value['max'] < count($value['values']['label'])) {
                    return response()->json([
                        'data' => 'variation_error',
                        'message' => translate('Please select maximum ') . $value['max'] . translate(' For ') . $value['name'] . '.',
                    ]);
                }
            }

            $variation_data = Helpers::get_varient($product_variations, $request->variations, $product->id);
            $variation_price = $variation_data['price'];
            $variations = $variation_data['variations'];
            $optionIds = $variation_data['optionIds'];
        }

        if ($request['addon_id']) {
            foreach ($request['addon_id'] as $id) {
                $add_on_ids[] = $id;
                $addon_price += $request['addon-price' . $id] * $request['addon-quantity' . $id];
                $add_on_qtys[] = $request['addon-quantity' . $id];
            }
            $add_ons = $request['addon_id'];
        }

        $addonAndVariationStock = Helpers::addonAndVariationStockCheck(product: $product, quantity: $request->quantity, add_on_qtys: $add_on_qtys, variation_options: $optionIds, add_on_ids: $add_on_ids, old_selected_variations: $old_selected_variations, old_selected_without_variation: $old_selected_without_variation, old_selected_addons: $old_selected_addons);
        if (data_get($addonAndVariationStock, 'out_of_stock') != null) {
            return response()->json([
                'data' => 'stock_out',
                'message' => data_get($addonAndVariationStock, 'out_of_stock'),
                'current_stock' => data_get($addonAndVariationStock, 'current_stock'),
                'id' => data_get($addonAndVariationStock, 'id'),
                'type' => data_get($addonAndVariationStock, 'type'),
            ], 203);
        }

        $addon_data = Helpers::calculate_addon_price(addons: AddOn::withOutGlobalScope(RestaurantScope::class)->whereIn('id', $add_ons)->get(), add_on_qtys: $add_on_qtys, old_selected_addons: $old_selected_addons);

        $addon_price = $addon_data['total_add_on_price'] ?? 0;
        $price = $product->price + $variation_price;
        $price = $price - Helpers::product_discount_calculate(product: $product, price: $price, restaurant: $product->restaurant);
        $price = $price * $request->quantity + $addon_price;

        $carts = Session::get('order_edit_cart', []);

        if (!$request->cart_id) {
            $newVariations = $optionIds;
            sort($newVariations);

            $duplicateExists = false;
            foreach ($carts as $cartItem) {
                if ($cartItem['item_id'] == $product->id) {
                    $existingVariations = $cartItem['variation_options'];
                    sort($existingVariations);

                    if ($existingVariations === $newVariations) {
                        $duplicateExists = true;
                        break;
                    }
                }
            }

            if ($duplicateExists) {
                return response()->json(['data' => 1]);
            }
            $cart['cart_id'] = count($carts) + 1;
            $cart['item_type'] = 'food';
            $cart['name'] = $product->name;
            $cart['image_full_url'] = $product->image_full_url;
            $cart['item_id'] = $product->id;
            $cart['quantity'] = $request->quantity;
            $cart['price'] = $price;
            $cart['maximum_cart_quantity'] = min($product?->maximum_cart_quantity ?? 9999999999, $product?->stock_type == 'unlimited' ? 999999999 : $product?->item_stock + $old_selected_without_variation);
            $cart['add_on_ids'] = $add_on_ids;
            $cart['add_on_qtys'] = $add_on_qtys;
            $cart['add_ons'] = json_encode($addon_data['addons']);
            $cart['variation_options'] = $optionIds;
            $cart['addon_price'] = $addon_price;
            $cart['variations'] = $variations;
            $cart['new_item'] = true;
            $carts[] = $cart;

            $edit_logs = Session::get('order_edit_logs', []);
            $edit_logs[] = 'add_new_item';
            Session::put('order_edit_logs', $edit_logs);
            $message = translate('product_has_been_added_in_cart');
        } else {
            $index = array_search($request->cart_id, array_column($carts, 'cart_id'));
            if ($index !== false) {
                // A bundle is an offer, not a product: its foods, variations and add-ons were
                // fixed when the restaurant enrolled. Only the quantity may move, and that is
                // handled by the quantity endpoint. Rewriting the line here would re-price it
                // from the menu and break the group.
                if (! empty($carts[$index]['bogo_group_id'])) {
                    return response()->json([
                        'data' => 'bogo_locked',
                        'message' => translate('messages.a_bogo_offer_can_only_have_its_quantity_changed'),
                    ]);
                }

                if (data_get($carts[$index], 'order_detail_id') && $request->quantity != data_get($carts[$index], 'quantity')) {
                    $edit_logs = Session::get('order_edit_logs', []);
                    $edit_logs[] = 'edited_item_quantity';
                    Session::put('order_edit_logs', $edit_logs);
                }
                $carts[$index]['item_type'] = 'food';
                $carts[$index]['name'] = $product->name;
                $carts[$index]['image_full_url'] = $product->image_full_url;
                $carts[$index]['item_id'] = $product->id;
                $carts[$index]['quantity'] = $request->quantity;
                $carts[$index]['price'] = $price;
                $carts[$index]['maximum_cart_quantity'] = min($product?->maximum_cart_quantity ?? 9999999999, $product?->stock_type == 'unlimited' ? 999999999 : $product?->item_stock + $old_selected_without_variation);
                $carts[$index]['add_on_ids'] = $add_on_ids;
                $carts[$index]['add_on_qtys'] = $add_on_qtys;
                $carts[$index]['add_ons'] = json_encode($addon_data['addons']);
                $carts[$index]['variation_options'] = $optionIds;
                $carts[$index]['variations'] = $variations;
                $carts[$index]['addon_price'] = $addon_price;
                $message = translate('product_has_been_updated');
            }
        }


        Session::put('order_edit_cart', $carts);
        return response()->json([
            'data' => 0,
            'message' => $message,
            'view' => view('admin-views.order.partials._food_list', compact('carts'))->render(),
        ]);
    }


    public function edit(Request $request, Order $order)
    {
        Session::forget('order_edit_cart');

        $order = Order::withoutGlobalScope(RestaurantScope::class)
            ->where(['id' => $order->id])->with('details.food', 'restaurant')->first();

        $carts = [];
        foreach ($order->details ?? [] as $key => $detail) {

            if (!$detail->food) {
                return response()->json([
                    'error' => 'food_not_found'
                ]);
            }

            $optionIds = [];
            $add_on_ids = [];
            $add_on_qtys = [];
            $old_selected_variations = [];
            if ($detail->variation != '[]') {
                foreach (json_decode($detail->variation, true) as $value) {
                    foreach (data_get($value, 'values', []) as $item) {
                        if (data_get($item, 'option_id', null) != null) {
                            $optionIds[] = data_get($item, 'option_id', null);
                            $old_selected_variations[data_get($item, 'option_id')] = $detail->quantity;
                        }
                    }
                }
            }

            foreach (json_decode($detail->add_ons, true) as $add_ons) {
                if (data_get($add_ons, 'id', null) != null) {
                    $add_on_ids[] = data_get($add_ons, 'id', null);
                    $add_on_qtys[] = data_get($add_ons, 'quantity', 1);
                }
            }

            $product_variations = json_decode($detail->variation, true);
            $variations = [];
            if (count($product_variations)) {
                $variation_data = Helpers::getVariationPrice($optionIds);
                $variations = $variation_data['variations'];
                if (count($variations) == 0) {
                    $variation_data = Helpers::get_edit_varient($product_variations, json_decode($detail->variation, true));
                    $variations = $variation_data['variations'];
                }
                $price = $detail->food->price + $variation_data['price'];
            } else {
                $price = $detail->food->price;
            }

            // A bundle line is not an ordinary product: its price came from the offer, the
            // free line costs nothing, and neither takes the food's own discount. Re-pricing
            // it from the menu here is what let a no-change save bill the free item.
            if ($detail->bogo_group_id) {
                $price = $detail->is_free_item ? 0 : (float) $detail->price;
                $price = $price * $detail->quantity + $detail->total_add_on_price;
            } else {
                $price = $price - Helpers::product_discount_calculate(product: $detail->food, price: $price, restaurant: $order->restaurant);
                $price = $price * $detail->quantity + $detail->total_add_on_price;
            }

            // Carried so the save can put the bundle back exactly as it was, and so the
            // editor can offer quantity only - a bundle cannot be re-added once removed,
            // because it is an offer rather than something on the menu.
            $cart['bogo_group_id'] = $detail->bogo_group_id;
            $cart['bogo_offer_id'] = $detail->bogo_offer_id;
            $cart['is_free_item'] = (int) $detail->is_free_item;
            $cart['is_bogo'] = (bool) $detail->bogo_group_id;

            $cart['order_detail_id'] = $detail->id;
            $cart['cart_id'] = $key + 1;
            $cart['item_type'] = 'food';
            $cart['name'] = $detail->food->name;
            $cart['image_full_url'] = $detail->food?->image_full_url;
            $cart['maximum_cart_quantity'] = min($detail->food?->maximum_cart_quantity ?? 9999999999, $detail->food?->stock_type == 'unlimited' ? 999999999 : $detail->food?->item_stock + $detail->quantity);
            $cart['item_id'] = $detail->food_id;
            $cart['quantity'] = $detail->quantity;
            $cart['price'] = $price;
            $cart['add_on_ids'] = $add_on_ids;
            $cart['add_on_qtys'] = $add_on_qtys;
            $cart['add_ons'] = $detail->add_ons;
            $cart['variation_options'] = $optionIds;
            $cart['variation_options_old'] = $old_selected_variations;
            $cart['addon_price'] = $detail->total_add_on_price;
            $cart['variations'] = json_decode($detail->variation, true);
            $carts[] = $cart;
        }

        // How many whole bundles each group represents, so the row's quantity control can
        // scale every line in it.
        $carts = Helpers::bogo_stamp_editor_bundles($carts);

        Session::put('order_edit_cart', $carts);

        return response()->json([
            'view' => view('admin-views.order.partials._food_list', compact('carts'))->render(),
        ]);
    }

    public function getSingleFoodPrice(Request $request)
    {

        $variation_price = 0;
        $old_selected_without_variation = 0;
        $old_selected_variations = [];

        if ($request->order_detail_id) {
            $order_detail = OrderDetail::where('id', $request->order_detail_id)->first();

            if ($request?->variation_options_old) {
                $old_selected_variations = $request->variation_options_old ?? [];
            }
            $old_selected_without_variation = $order_detail?->quantity ?? 0;
        }

        $product = Food::withoutGlobalScope(RestaurantScope::class)->with('restaurant')->find($request->food_id);

        if ($product->maximum_cart_quantity && $request->quantity > $product->maximum_cart_quantity) {
            return response()->json([
                'data' => 'maximum_cart_quantity',
                'message' => translate('Maximum cart quantity for this item is ' . $product->maximum_cart_quantity),
            ], 203);
        }
        $addonAndVariationStock = Helpers::addonAndVariationStockCheck(
            product: $product,
            quantity: $request->quantity,
            variation_options: $request?->option_ids,
            old_selected_without_variation: $old_selected_without_variation,
            old_selected_variations: $old_selected_variations
        );
        if (data_get($addonAndVariationStock, 'out_of_stock') != null) {
            return response()->json([
                'data' => 'stock_out',
                'message' => data_get($addonAndVariationStock, 'out_of_stock'),
                'current_stock' => data_get($addonAndVariationStock, 'current_stock'),
                'id' => data_get($addonAndVariationStock, 'id'),
                'type' => data_get($addonAndVariationStock, 'type'),
            ], 203);
        }

        $price = $product->price;
        $carts = Session::get('order_edit_cart', []);
        $index = array_search($request->cart_id, array_column($carts, 'cart_id'));

        if ($index !== false) {
            // A bundle is one row in the editor and moves as a whole: the quantity posted is
            // a number of bundles, and every line in the group scales by it. Prices come from
            // what the lines already carry - re-pricing from the menu would charge for the
            // free item and add the food's own discount back on.
            if (! empty($carts[$index]['bogo_group_id'])) {
                if (data_get($carts[$index], 'order_detail_id') && $request->quantity != data_get($carts[$index], 'bogo_bundle_quantity')) {
                    $edit_logs = Session::get('order_edit_logs', []);
                    $edit_logs[] = 'edited_item_quantity';
                    Session::put('order_edit_logs', $edit_logs);
                }

                $carts = Helpers::bogo_scale_editor_bundle($carts, $carts[$index]['bogo_group_id'], (int) $request->quantity);
                Session::put('order_edit_cart', $carts);

                return response()->json([
                    'data' => 0,
                    'message' => translate('messages.quantity_updated_successfully'),
                    'view' => view('admin-views.order.partials._food_list', compact('carts'))->render(),
                ]);
            }

            {
                $product_variations = json_decode($product->variations, true);
                if (count($carts[$index]['variations']) && count($product_variations)) {
                    $variation_data = Helpers::get_edit_varient($product_variations, $carts[$index]['variations'], $product->id);
                    $variation_price = $variation_data['price'];
                }
                $price = $price + $variation_price;
                $price = $price - Helpers::product_discount_calculate(product: $product, price: $price, restaurant: $product->restaurant);
                $price = $price * $request->quantity + $carts[$index]['addon_price'];
            }

            if (data_get($carts[$index], 'order_detail_id') && $request->quantity != data_get($carts[$index], 'quantity')) {
                $edit_logs = Session::get('order_edit_logs', []);
                $edit_logs[] = 'edited_item_quantity';
                Session::put('order_edit_logs', $edit_logs);
            }
            $carts[$index]['quantity'] = $request->quantity;
            $carts[$index]['price'] = $price;

        }

        Session::put('order_edit_cart', $carts);
        return response()->json([
            'data' => 0,
            'message' => translate('messages.quantity_updated_successfully'),
            'view' => view('admin-views.order.partials._food_list', compact('carts'))->render(),
        ]);
    }


    public function update(Request $request, Order $order)
    {
        $carts = Session::get('order_edit_cart', []);
        $order_edit_logs = Session::get('order_edit_logs', []);

        $order = $this->makeEditOrderDetails(order: $order, carts: $carts, restaurant: $order->restaurant, editedBy: 'admin', editLogs: $order_edit_logs);

        if (data_get($order, 'status_code') !== 200) {
            Toastr::error(data_get($order, 'message'));
            return back();
        }

        Toastr::success(translate('messages.order_updated_successfully'));
        return back();
    }

    public function quick_view(Request $request)
    {
        $product = Food::withOutGlobalScope(RestaurantScope::class)->select(['id', 'name', 'image', 'veg', 'stock_type', 'item_stock', 'restaurant_id', 'discount', 'discount_type', 'description', 'maximum_cart_quantity', 'variations', 'add_ons', 'price'])->with('restaurant')->findOrFail($request->product_id);
        $item_type = 'food';
        $order_id = $request->order_id;

        return response()->json([
            'success' => 1,
            'view' => view('admin-views.order.partials._quick-view', compact('product', 'order_id', 'item_type'))->render(),
        ]);
    }

    public function quick_view_cart_item(Request $request)
    {
        $carts = Session::get('order_edit_cart', []);
        $cart_item = collect($carts)->firstWhere('cart_id', $request->cart_id);
        $order_id = $request->order_id;
        $item_id = $request->key;
        $product = Food::withOutGlobalScope(RestaurantScope::class)->select(['id', 'name', 'image', 'veg', 'stock_type', 'item_stock', 'restaurant_id', 'discount', 'discount_type', 'description', 'maximum_cart_quantity', 'variations', 'add_ons', 'price'])->with('restaurant')->findOrFail($item_id);
        $item_type = 'food';

        $item_count = count($carts);
        // dd($carts);
        return response()->json([
            'success' => 1,
            'view' => view('admin-views.order.partials._quick-view-cart-item', compact('order_id', 'product', 'cart_item', 'item_id', 'item_type', 'item_count'))->render(),
        ]);
    }

    public function orders_export(Request $request, $type, $restaurant_id)
    {
        try {
            $key = explode(' ', $request['search']);

            $orders = Order::where('restaurant_id', $restaurant_id)->with('customer', 'orderProDiscount')
                ->when(isset($key), function ($q) use ($key) {
                    $q->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('id', 'like', "%{$value}%");
                        }
                    });
                })
                ->latest()->Notpos()->get();

            $restaurant = Restaurant::where('id', $restaurant_id)->select(['id', 'zone_id', 'name'])->first();
            $data = [
                'data' => $orders,
                'search' => request()->search ?? null,
                'zone' => Helpers::get_zones_name($restaurant->zone_id),
                'restaurant' => $restaurant->name,
            ];

            if ($type == 'csv') {
                return Excel::download(new RestaurantOrderlistExport($data), 'OrderList.csv');
            }
            return Excel::download(new RestaurantOrderlistExport($data), 'OrderList.xlsx');
        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);
            return back();
        }
    }



    public function add_order_proof(Request $request, $id)
    {
        $order = Order::find($id);
        $img_names = $order->order_proof ? json_decode($order->order_proof) : [];
        $images = [];
        $total_file = (is_array($request->order_proof) ? count($request->order_proof) : 0) + count($img_names);
        if (!$img_names) {
            $request->validate([
                'order_proof' => 'required|array|max:5',
            ]);
        }

        if ($total_file > 5) {
            Toastr::error(translate('messages.order_proof_must_not_have_more_than_5_item'));
            return back();
        }

        if (!empty($request->file('order_proof'))) {
            foreach ($request->order_proof as $img) {
                $image_name = Helpers::upload('order/', 'png', $img);
                array_push($img_names, ['img' => $image_name, 'storage' => Helpers::getDisk()]);
            }
            $images = $img_names;
        }

        $order->order_proof = json_encode($images);
        $order->save();

        Toastr::success(translate('messages.order_proof_added'));
        return back();
    }
    public function remove_proof_image(Request $request)
    {
        $order = Order::find($request['id']);
        $array = [];
        $proof = isset($order->order_proof) ? json_decode($order->order_proof, true) : [];
        if (count($proof) < 2) {
            Toastr::warning(translate('all_image_delete_warning'));
            return back();
        }
        Helpers::check_and_delete('order/', $request['name']);
        foreach ($proof as $image) {
            $image = is_array($image) ? $image : (is_object($image) && get_class($image) == 'stdClass' ? json_decode(json_encode($image), true) : ['img' => $image, 'storage' => 'public']);
            if ($image['img'] != $request['name']) {
                array_push($array, $image);
            }
        }
        Order::where('id', $request['id'])->update([
            'order_proof' => json_encode($array),
        ]);
        Toastr::success(translate('order_proof_image_removed_successfully'));
        return back();
    }

    public function offline_payment(Request $request)
    {
        $order = Order::findOrFail($request->id);
        if ($request->verify == 'yes') {
            $order->payment_status = 'paid';
            $order->confirmed = now();
            $order->order_status = 'confirmed';
            $order->save();
            $order->offline_payments()->update([
                'status' => 'verified'
            ]);

            $payment_method_name = data_get(json_decode($order->offline_payments->payment_info, true), 'method_name', $order->payment_method);
            if ($order->payment_method == 'partial_payment') {
                $order->payments()->where('payment_status', 'unpaid')->update([
                    'payment_method' => $payment_method_name,
                    'payment_status' => 'paid',
                ]);
            }

            // $order->payment_method = $payment_method_name;
            // $order->save();
            $this->sent_notification_on_offline_payment('approved', $order->id);
        } elseif ($request->verify == 'switched_to_cod') {
            $order->offline_payments()->update([
                'status' => 'verified'
            ]);
            if ($order->payment_method == 'partial_payment') {
                $order->payments()->where('payment_status', 'unpaid')->update([
                    'payment_method' => 'cash_on_delivery',
                ]);
            } else {

                $order->payment_method = 'cash_on_delivery';
                $order->save();
            }

            if ($order->restaurant->restaurant_model == 'subscription' && isset($order->restaurant->restaurant_sub)) {
                if ($order->restaurant->restaurant_sub->max_order != "unlimited" && $order->restaurant->restaurant_sub->max_order > 0) {
                    $order->restaurant->restaurant_sub()->decrement('max_order', 1);
                }
            }
            Helpers::send_order_notification($order);
        } else {
            $order->offline_payments()->update([
                'status' => 'denied',
                'note' => $request->note ?? null
            ]);

            $this->sent_notification_on_offline_payment('denied', $order->id);
        }

        Toastr::success(translate('Payment_status_updated'));
        return back();
    }



    private function sent_notification_on_offline_payment($status, $id)
    {
        $order = Order::findOrfail($id);
        if ($status == 'approved') {
            Helpers::send_order_notification($order);

            $notification_text = 'offline_verified';
            $notification_title = translate('messages.Your_Offline_payment_was_approved');
            $mail_sattus = Helpers::get_mail_status('offline_payment_approve_mail_status_user');
            $mail_sattus_type = 'approved';
            $notification_status = Helpers::getNotificationStatusData('customer', 'customer_offline_payment_approve');


            if ($order->restaurant->restaurant_model == 'subscription' && isset($order->restaurant->restaurant_sub)) {
                if ($order->restaurant->restaurant_sub->max_order != "unlimited" && $order->restaurant->restaurant_sub->max_order > 0) {
                    $order->restaurant->restaurant_sub()->decrement('max_order', 1);
                }
            }
        } else {
            $notification_text = 'offline_denied';
            $notification_title = translate('messages.Your_Offline_payment_was_rejected');
            $mail_sattus_type = 'denied';
            $mail_sattus = Helpers::get_mail_status('offline_payment_deny_mail_status_user');
            $notification_status = Helpers::getNotificationStatusData('customer', 'customer_offline_payment_deny');
        }
        try {

              $fcm_token = ($order->is_guest == 0 ? $order?->customer?->cm_firebase_token : $order?->guest?->fcm_token) ?? null;
                $message = Helpers::getOrderPushNotificationMessage($order, $notification_text, 'user' ,$order->customer ? $order?->customer?->current_language_key : 'en');
                if ($message && isset($fcm_token)) {
                    $data= Helpers::makeDataForPushNotification(title:$notification_title, message:$message,orderId: $order->id, type: 'order_status', orderStatus: $order->order_status);
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                    Helpers::insertDataOnNotificationTable($data , 'user', $order->user_id);
                }




            if ($order?->customer?->email && config('mail.status') && $mail_sattus == '1' && $notification_status?->mail_status == 'active') {
                Mail::to($order?->customer?->getRawOriginal('email'))->send(new \App\Mail\UserOfflinePaymentMail($order?->customer?->f_name . ' ' . $order?->customer?->l_name, $mail_sattus_type));
            }
        } catch (\Exception $e) {
            Toastr::error(translate('Failed_to_Send_Email'));
            info($e->getMessage());
            return true;
        }
        return true;
    }




    public function offline_verification_list(Request $request, $status)
    {
        $reasons = OrderCancelReason::where('status', 1)->where('user_type', 'admin')->get();

        $key = explode(' ', $request['search']);
        $orders = Order::with(['customer', 'restaurant'])->has('offline_payments')

            ->when(isset($key), function ($query) use ($key) {
                return $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('id', 'like', "%{$value}%")
                            ->orWhere('order_status', 'like', "%{$value}%")
                            ->orWhere('transaction_reference', 'like', "%{$value}%");
                    }
                });
            })
            ->when($status == 'pending', function ($query) {
                return $query->whereHas('offline_payments', function ($query) {
                    return $query->where('status', 'pending');
                });
            })
            ->when($status == 'denied', function ($query) {
                return $query->whereHas('offline_payments', function ($query) {
                    return $query->where('status', 'denied');
                });
            })
            ->when($status == 'verified', function ($query) {
                return $query->whereHas('offline_payments', function ($query) {
                    return $query->where('status', 'verified');
                });
            })
            ->orderBy('schedule_at', 'desc')
            ->paginate(config('default_pagination'));

        return view('admin-views.order.offline_verification_list', compact('orders', 'status', 'reasons'));
    }
    public function add_dine_in_table_number(Order $order, Request $request)
    {
        $request->validate([
            'table_number' => 'nullable|max:255|required_without_all:token_number',
            'token_number' => 'nullable|max:255|required_without_all:table_number',
        ], [
            'table_number.required_without_all' => translate('you_must_set_a_table_or_token_number'),
            'token_number.required_without_all' => translate('you_must_set_a_table_or_token_number'),
        ]);

        if ($order?->order_type == 'dine_in') {
            $order->OrderReference()->update([
                'token_number' => $request->token_number ?? null,
                'table_number' => $request->table_number ?? null
            ]);
        }

        Helpers::dineInOrderTokenUpdatePushNotification($order, $request->table_number, $request->token_number);

        Toastr::success($request->table_number ? translate('table_number_updated_successfully') : translate('token_number_updated_successfully'));
        return back();
    }
    public function updateSchedule(Request $request)
    {

        $order = Order::where('id', $request->order_id)->first();
        $schedule_at = Carbon::parse($request->date);

        $restaurant = Restaurant::selectRaw('*, IF(((select count(*) from `restaurant_schedule` where `restaurants`.`id` = `restaurant_schedule`.`restaurant_id` and `restaurant_schedule`.`day` = ' . (int) $schedule_at->format('w') . ' and `restaurant_schedule`.`opening_time` < "' . $schedule_at->format('H:i:s') . '" and `restaurant_schedule`.`closing_time` >"' . $schedule_at->format('H:i:s') . '") > 0), true, false) as open')->where('id', $order->restaurant_id)->first();

        if ($restaurant->open) {
            $order->schedule_at = $schedule_at;
            $order->save();
            $data = ['status' => 'success', 'message' => translate('messages.order_has_been_scheduled')];
        } else {
            $data = ['status' => 'closed', 'message' => translate('restaurant_is_closed_on_this_time')];
        }

        $this->makeEditOrderLogs($order->id, 'edited_schedule_date_&_time', 'admin');
        return response()->json($data, 200);
    }
}
