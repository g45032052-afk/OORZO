<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\CentralLogics\PersonalizationService;
use Illuminate\Support\Facades\DB;

/**
 * Records personalization signals when an order is delivered. Fires on the
 * model save regardless of the code path (OrderLogic / POSController / API).
 * The PersonalizationService facade no-ops when the personalization toggle is
 * off, so this is safe to keep registered permanently.
 */
class OrderObserver
{
    public function updated(Order $order): void
    {
        if (!$order->isDirty('order_status') || $order->order_status !== 'delivered' || !$order->user_id) {
            return;
        }

        // Guest orders keep the `guests` table id in `user_id`, so it is not a
        // valid customer. Writing it would break the customer_preferences ->
        // users foreign key. Same for customers deleted after ordering.
        if ($order->is_guest || !DB::table('users')->where('id', $order->user_id)->exists()) {
            return;
        }

        $details = OrderDetail::where('order_id', $order->id)->whereNotNull('food_id')->get();
        foreach ($details as $detail) {
            PersonalizationService::recordItemAction((int) $order->user_id, (int) $detail->food_id, 'order');
        }
    }
}
