<?php

namespace App\Traits;

use App\CentralLogics\Helpers;
use App\Models\DataSetting;
use App\Models\NotificationMessage;
use App\Models\OrderProDiscount;
use App\Models\ProCustomerSubscription;
use App\Models\ProCustomerSubscriptionPlan;
use App\Models\ProCustomerTransaction;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

trait ManagesProCustomerSubscription
{
    /**
     * Resolve the active Pro Customer offer for a user.
     *
     * Returns ['status' => bool, 'message' => string, 'benefit' => array|null].
     * Only when status is true does `benefit` carry the active offer config.
     * Pass $incrementCount=true to bump order_count on the user's latest
     * pro_customer_transactions row when an active offer is found.
     */
    public function getProCustomerOffer($userId, bool $incrementCount = false , bool $showOnlyActivePlan = true): array
    {
        $fail = static fn(string $message) => [
            // plan_details is always present so a client never has to branch on the key
            // existing - the success path carries it too, null when nothing was asked for.
            'status' => false, 'message' => $message, 'benefit' => null, 'plan_details' => null,
        ];

        if (!$userId) return $fail('no_user');

        $user = User::find($userId);
        if (!$user ) return $fail('no_user');

        $subscription = ProCustomerSubscription::where('user_id', $user->id)
            ->when($showOnlyActivePlan, function ($query) {
                $query->where('status', 'active')
                ->where(fn($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', now()));
            })

            ->latest('id')
            ->first();

        if (!$subscription) return $fail('no_active_subscription');

        $rows = DataSetting::where('type', 'pro_customer_benefits')
            ->pluck('value', 'key')
            ->toArray();

        $type = match (true) {
            (int) ($rows['discount_status'] ?? 0) === 1     => 'discount',
            (int) ($rows['delivery_fee_status'] ?? 0) === 1 => 'delivery_fee',
            (int) ($rows['coupon_status'] ?? 0) === 1       => 'coupon',
            default                                          => null,
        };

        if (!$type) return $fail('no_benefit_enabled');

        $f = static fn($v) => isset($v) && $v !== '' ? (float) $v : null;

        $latestTransaction = null;
        if (!$showOnlyActivePlan || $incrementCount) {
            $latestTransaction = ProCustomerTransaction::where('user_id', $user->id)
                ->where('subscription_id', $subscription->id)
                ->latest('id')
                ->first();
        }

        if ($showOnlyActivePlan === false) {
            $totals = OrderProDiscount::where('user_id', $user->id)
                ->where('transaction_id', $latestTransaction?->id)
                ->selectRaw('COALESCE(SUM(amount_saved), 0) + COALESCE(SUM(delivery_fee_reduction_amount), 0) as total_saved')
                ->selectRaw('COUNT(*) as total_orders')
                ->first();

            $now = now();
            $endAt = $subscription->end_at;
            $daysRemaining = $endAt ? max(0, $now->startOfDay()->diffInDays($endAt->copy()->startOfDay(), false)) : null;

            $plan_details = [
                'plan_name'      => $subscription->plan_name,
                'total_saved'    => (float) ($totals->total_saved ?? 0),
                'total_orders'   => (int) ($totals->total_orders ?? 0),
                'start_at'       => $subscription->start_at?->format('Y-m-d'),
                'end_at'         => $endAt ? $endAt->format('Y-m-d') : null,
                'days_remaining' => $daysRemaining,
                'paid_by'        => $latestTransaction?->payment_method,
            ];
        }

        $benefit = [
            'type' => $type,
            'subscription_id' => $subscription->id,
            'plan_id' => $subscription->plan_id,
        ];
        if ($type === 'discount') {
            $benefit += [
                'percentage'       => $f($rows['discount_percentage'] ?? null),
                'max_amount'       => $f($rows['discount_max_amount'] ?? null),
                'min_order_status' => (int) ($rows['discount_min_order_status'] ?? 0),
                'min_order_amount' => $f($rows['discount_min_order_amount'] ?? null),
            ];
        } elseif ($type === 'delivery_fee') {
            $benefit += [
                'offer_type'                 => $rows['delivery_fee_offer_type'] ?? 'full_free',
                'min_order_status'           => (int) ($rows['delivery_fee_min_order_status'] ?? 0),
                'min_order_amount'           => $f($rows['delivery_fee_min_order_amount'] ?? null),
                'charge_discount_percentage' => $f($rows['delivery_charge_discount'] ?? null),
            ];
        }

        if ($incrementCount) {
            $latestTransaction?->increment('order_count');
        }

        // Live means what checkout means by it, whatever $showOnlyActivePlan asked for.
        //
        // That flag widens which *row* is fetched - the "your plan" screen wants an expired
        // plan's dates and totals - and it used to widen the verdict with it, because status
        // was read from the column alone. `end_at` in the past with the column still saying
        // active is the ordinary state of a subscription between expiring and being swept, so
        // the offer endpoint promised a benefit checkout would decline to give: checkout tests
        // end_at, and this did not. The column is never the whole answer, however promptly
        // expireDueSubscriptions() runs.
        $expired = $subscription->end_at && $subscription->end_at->lessThan(now());
        $isLive = $subscription->status === 'active' && ! $expired;

        return [
            'status' => $isLive,
            // The column reports 'active' right up until the sweep runs, so an expired plan is
            // named as one here rather than repeating a value that has been overtaken.
            'message' => $expired && $subscription->status === 'active' ? 'expired' : $subscription->status,
            // Left populated even when the plan is not live, so the shape never changes under a
            // shipped client: an app that reads benefit.type without a null check kept working
            // through this fix, and the "your plan" screen can still say what the expired plan
            // gave. `status` is what decides whether the benefit applies - it is the only field
            // that moved here, and checkout has always agreed with it.
            'benefit' => $benefit,
            'plan_details' => $plan_details ?? null,
        ];
    }

    /**
     * Resolve the user's active Pro offer and apply the `discount` benefit to a price.
     *
     * Returns ['offer' => array, 'discount' => float, 'total_price' => float].
     *  - `offer` is the same shape returned by getProCustomerOffer(); pass it through
     *    to downstream blocks (delivery_fee, recordOrderProDiscount, etc.) so they
     *    don't have to re-query.
     *  - `discount` is the absolute amount removed from `total_price`.
     *  - `total_price` is the post-discount price (clamped at 0).
     * When the user is not pro, has no active subscription, or the active benefit
     * isn't `discount`, both `discount` is 0 and `total_price` is unchanged.
     */
    public function applyProCustomerDiscount(?int $userId, float $subtotal, float $totalPrice): array
    {
        $offer    = $this->getProCustomerOffer(userId: $userId);
        $discount = 0.0;

        if ($offer['status'] && $offer['benefit']['type'] === 'discount') {
            $cfg   = $offer['benefit'];
            $minOk = $cfg['min_order_status'] !== 1
                || ($cfg['min_order_amount'] !== null && $subtotal >= $cfg['min_order_amount']);

            if ($minOk && $cfg['percentage']) {
                $discount = $totalPrice * ((float) $cfg['percentage'] / 100);
                if ($cfg['max_amount'] !== null) {
                    $discount = min($discount, (float) $cfg['max_amount']);
                }
            }
        }

        return [
            'offer'       => $offer,
            'discount'    => (float) $discount,
            'total_price' => (float) max($totalPrice - $discount, 0),
        ];
    }

    /**
     * Apply the `delivery_fee` Pro Customer benefit to an order's delivery charge.
     *
     * Returns ['delivery_charge' => float, 'free_delivery_by' => string|null, 'savings' => float].
     *  - `delivery_charge` is the post-benefit charge (clamped at 0).
     *  - `free_delivery_by` becomes `'admin'` when the benefit applies; otherwise unchanged.
     *  - `savings` is the absolute amount removed from the delivery charge.
     * No-op (savings = 0, charge/free_delivery_by unchanged) when the user is not pro,
     * the active benefit isn't `delivery_fee`, or the incoming charge is already 0.
     * `full_free` zeroes the charge subject to the optional min-order check; `partial_free`
     * reduces by `charge_discount_percentage`, never below $minimumCharge.
     *
     * @param float $minimumCharge The floor `partial_free` may not discount past — the same
     *                             figure the pricing engine already clamped its own output to,
     *                             from {@see \App\CentralLogics\Helpers::minimumDeliveryCharge()}.
     *                             A percentage off is still a delivery charge, so it obeys the
     *                             zone's floor exactly as the saver reduction does; `full_free`
     *                             is deliberately exempt, since a fee floored at the minimum
     *                             would not be free.
     */
    public function applyProCustomerDeliveryFee(
        array $proOffer,
        float $deliveryCharge,
        float $totalPrice,
        ?string $freeDeliveryBy = null,
        float $minimumCharge = 0.0,
    ): array {
        $savings = 0.0;

        $applies = ($proOffer['status'] ?? false)
            && ($proOffer['benefit']['type'] ?? null) === 'delivery_fee'
            && $deliveryCharge > 0;

        if (!$applies) {
            return [
                'delivery_charge'  => $deliveryCharge,
                'free_delivery_by' => $freeDeliveryBy,
                'savings'          => 0.0,
            ];
        }

        $cfg = $proOffer['benefit'];

        if ($cfg['offer_type'] === 'full_free') {
            $minOk = $cfg['min_order_status'] !== 1
                || ($cfg['min_order_amount'] !== null && $totalPrice >= $cfg['min_order_amount']);
            if ($minOk) {
                $savings        = $deliveryCharge;
                $deliveryCharge = 0.0;
                $freeDeliveryBy = 'admin';
            }
        } elseif ($cfg['offer_type'] === 'partial_free' && $cfg['charge_discount_percentage']) {
            $reduction = $deliveryCharge * ((float) $cfg['charge_discount_percentage'] / 100);

            // The floor wins over the percentage. The engines already clamp their own output to
            // the same minimum, so without this the only discount on the platform able to price
            // a delivery under its zone's floor would be this one - the saver reduction is held
            // to it at setup time, in AdditionalChargeController.
            //
            // Bounded above by the incoming charge as well: a benefit may only ever reduce. A
            // zone whose maximum is set below its minimum hands the engine a charge under the
            // floor, and lifting it to meet one would charge a Pro member *more* for being one.
            $reduced        = min($deliveryCharge, max($minimumCharge, $deliveryCharge - $reduction));
            $savings        = max(0.0, $deliveryCharge - $reduced);
            $deliveryCharge = max(0.0, $reduced);

            // Only a discount that actually happened is attributed to the admin, who is billed
            // `savings` for it in OrderLogic. A charge already at its floor gives nothing away,
            // so it must not be recorded as an admin-borne free delivery.
            $freeDeliveryBy = $savings > 0 ? 'admin' : $freeDeliveryBy;
        }

        return [
            'delivery_charge'  => (float) $deliveryCharge,
            'free_delivery_by' => $freeDeliveryBy,
            'savings'          => $savings,
        ];
    }

    /**
     * Persist a per-order Pro Customer benefit audit row.
     *
     * Snapshots the rule config in effect at order-time so historical
     * reports stay correct even if admin changes settings later.
     * Returns null when there's nothing meaningful to record.
     */
    public function recordOrderProDiscount(
        int $orderId,
        int $userId,
        array $proOffer,
        float $amountSaved,
        ?float $originalDeliveryCharge = null,
    ): ?OrderProDiscount {
        if (!($proOffer['status'] ?? false)) {
            return null;
        }

        $benefit = $proOffer['benefit'];
        $type    = $benefit['type'];

        // Coupon is informational (no auto-applied savings); always record it.
        // For other types, skip the row when the benefit didn't actually save anything.
        if ($amountSaved <= 0 && $type !== 'coupon') {
            return null;
        }
        $transaction_id = ProCustomerTransaction::where('user_id', $userId)
            ->where('subscription_id', $benefit['subscription_id'])
            ->latest('id')
            ->value('id');

        return OrderProDiscount::create([
            'order_id'                            => $orderId,
            'user_id'                             => $userId,
            'subscription_id'                     => $benefit['subscription_id'] ?? null,
            'plan_id'                             => $benefit['plan_id'] ?? null,
            'benefit_type'                        => $type,
            'transaction_id'                      => $transaction_id,
            'amount_saved'                        => $type === 'discount' ? $amountSaved : 0,
            'discount_percentage'                 => $type === 'discount' ? ($benefit['percentage'] ?? null) : null,
            'max_discount_amount'                 => $type === 'discount' ? ($benefit['max_amount'] ?? null) : null,
            'min_order_amount'                    => in_array($type, ['discount', 'delivery_fee'], true)
                ? ($benefit['min_order_amount'] ?? null)
                : null,
            'delivery_offer_type'                 => $type === 'delivery_fee' ? ($benefit['offer_type'] ?? null) : null,
            'delivery_charge_discount_percentage' => $type === 'delivery_fee' ? ($benefit['charge_discount_percentage'] ?? null) : null,
            'delivery_fee_reduction_amount'       => $type === 'delivery_fee' ? $amountSaved : null,
            'original_delivery_charge'            => $type === 'delivery_fee' ? $originalDeliveryCharge : null,
        ]);
    }

    public function applyProCustomerPlan(User $user, ProCustomerSubscriptionPlan $plan, array $payment = [], string $mode = 'start'): ProCustomerSubscription
    {
        $rawPlanName = ProCustomerSubscriptionPlan::withoutGlobalScope('translate')
            ->where('id', $plan->id)
            ->value('plan_name');

        $isFreeTrial = $plan->plan_type === 'free_trial';
        $duration = (int) $plan->duration;
        $price = (float) $plan->price;
        $paymentMethodInput = $payment['payment_method'] ?? null;

        if (!$isFreeTrial && $paymentMethodInput === 'wallet'
            && (float) $user->wallet_balance < $price) {
            throw new RuntimeException('insufficient_wallet_balance');
        }

        $subscription = DB::transaction(function () use ($user, $plan, $rawPlanName, $isFreeTrial, $duration, $price, $paymentMethodInput, $payment, $mode) {
            $subscription = ProCustomerSubscription::where('user_id', $user->id)
                ->latest('id')
                ->first()
                ?? new ProCustomerSubscription(['user_id' => $user->id]);

            $isNew = !$subscription->exists;
            $now = now();

            $extending = $mode === 'renew'
                && !$isNew
                && $subscription->status === 'active'
                && $subscription->end_at
                && $subscription->end_at->greaterThanOrEqualTo($now);

            if ($extending) {
                $startAt = $subscription->start_at ?? $now;
                $endAt = $subscription->end_at->copy()->addDays($duration);
            } else {
                $startAt = $now;
                $endAt = (clone $now)->addDays($duration);
            }

            $subscription->fill([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan_name' => $rawPlanName,
                'plan_type' => $plan->plan_type,
                'plan_price' => $isFreeTrial ? 0 : $price,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'status' => 'active',
            ]);
            if ($isNew) {
                $subscription->auto_renew = 0;
            }
            $subscription->save();

            $paymentMethod = $payment['payment_method']
                ?? ($isFreeTrial ? 'free_trial' : null);
            $paymentStatus = $payment['payment_status']
                ?? (($isFreeTrial || $paymentMethod) ? 'success' : 'pending');
            $paidAt = $payment['paid_at']
                ?? ($paymentStatus === 'success' ? $now : null);

            $walletTxnUuid = (!$isFreeTrial && $paymentMethodInput === 'wallet' && $price > 0)
                ? (string) Str::uuid()
                : null;

            $transaction = ProCustomerTransaction::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'plan_id' => $plan->id,
                'transaction_reference' => $payment['transaction_reference'] ?? $walletTxnUuid,
                'plan_name' => $rawPlanName,
                'plan_type' => $plan->plan_type,
                'plan_price' => $isFreeTrial ? 0 : $price,
                'amount' => $isFreeTrial ? 0 : $price,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'order_count' => 0,
                'paid_at' => $paidAt,
            ]);

            if ($walletTxnUuid !== null) {
                $newBalance = (float) $user->wallet_balance - $price;

                $walletTxn = new WalletTransaction();
                $walletTxn->user_id = $user->id;
                $walletTxn->transaction_id = $walletTxnUuid;
                $walletTxn->reference = 'pro_subscription_' . $transaction->id;
                $walletTxn->reference_id = (string) $transaction->id;
                $walletTxn->transaction_type = 'pro_subscription';
                $walletTxn->debit = $price;
                $walletTxn->credit = 0;
                $walletTxn->admin_bonus = 0;
                $walletTxn->balance = $newBalance;
                $walletTxn->created_at = $now;
                $walletTxn->updated_at = $now;
                $walletTxn->save();

                $user->wallet_balance = $newBalance;
            }

            $user->pro_status = 1;
            $user->save();

            return $subscription;
        });

       $this->sendUserPushNotification(
            user: $user,
            messageKey: 'subscription_activated',
            title: $mode === 'renew' ? translate('messages.Subscription Renewed') : translate('messages.Subscription Activated'),
            type: 'customer_subscription_activated',
            dataId: $subscription->id,
        );

        return $subscription;
    }

    public function expireDueSubscriptions(): void
    {
        static $running = false;
        if ($running) {
            return;
        }
        $running = true;

        try {
            $this->doExpireDueSubscriptions();
        } finally {
            $running = false;
        }
    }

    private function doExpireDueSubscriptions(): void
    {
        $now = now();

        $subscriptions = ProCustomerSubscription::query()
            ->select(['id', 'user_id'])
            ->with(['user:id,f_name,l_name,cm_firebase_token,current_language_key'])
            ->where('status', 'active')
            ->whereNotNull('end_at')
            // The moment it ends, not the day. whereDate() compared the date part alone, so a
            // plan ending at 00:30 kept status 'active' - and the user their pro_status flag,
            // and the admin list its "active" badge - until the following day's sweep. The
            // expiry test everywhere else, getProCustomerOffer() included, is end_at >= now.
            ->where('end_at', '<', $now)
            ->lazy();

        foreach ($subscriptions as $subscription) {
            DB::transaction(function () use ($subscription) {
                $subscription->status = 'expired';
                $subscription->auto_renew = 0;
                $subscription->save();

                User::where('id', $subscription->user_id)->update(['pro_status' => 0]);
            });

            if ($subscription->user) {
                $this->sendUserPushNotification(
                    user: $subscription->user,
                    messageKey: 'subscription_expired',
                    title: translate('messages.Subscription Expire Message'),
                    type: 'subscription_expired',
                    dataId: $subscription->id,
                );
            }
        }

        DataSetting::updateOrCreate(
            ['key' => 'subscription_expiry_last_run_at', 'type' => 'notification_settings'],
            ['value' => $now->toDateTimeString()],
        );
    }

    public function cancelProCustomerSubscription(ProCustomerSubscription $subscription): void
    {
        $user = $subscription->user ?? User::find($subscription->user_id);

        DB::transaction(function () use ($subscription, $user) {
            $subscription->status = 'canceled';
            $subscription->auto_renew = 0;
            $subscription->save();

            if ($user) {
                $user->pro_status = 0;
                $user->save();
            }
        });

        if ($user) {
            $this->sendUserPushNotification(
                user: $user,
                messageKey: 'subscription_canceled',
                title: translate('messages.Subscription Canceled'),
                type: 'subscription_canceled',
                dataId: $subscription->id,
            );
        }
    }

    public function sendCustomerSubscriptionExpireNotification(): void
    {
        $beforeTime = (int) (DataSetting::where(['key' => 'subscription_reminder_before_time', 'type' => 'notification_settings'])->first()?->value ?? 0);
        $beforeUnit = DataSetting::where(['key' => 'subscription_reminder_before', 'type' => 'notification_settings'])->first()?->value ?? 'days';

        if ($beforeTime <= 0) {
            return;
        }

        $isEnabled = NotificationMessage::where('key', 'subscription_expire_reminder')
            ->where('user_type', 'user')
            ->where('status', 1)
            ->exists();
        if (!$isEnabled) {
            return;
        }

        $target = match ($beforeUnit) {
            'hour'  => now()->addHours($beforeTime),
            'min'   => now()->addMinutes($beforeTime),
            default => now()->addDays($beforeTime),
        };

        [$windowStart, $windowEnd] = $beforeUnit === 'hour'
            ? [$target->copy()->startOfHour(), $target->copy()->endOfHour()]
            : [$target->copy()->startOfDay(), $target->copy()->endOfDay()];

        $subscriptions = ProCustomerSubscription::query()
            ->select(['id', 'user_id'])
            ->with(['user:id,f_name,l_name,cm_firebase_token,current_language_key'])
            ->where('status', 'active')
            ->whereBetween('end_at', [$windowStart, $windowEnd])
            ->whereHas('user', fn ($q) => $q
                ->whereNotNull('cm_firebase_token')
                ->where('cm_firebase_token', '!=', '@'))
            ->lazy();

        foreach ($subscriptions as $subscription) {
            if (!$subscription->user) {
                continue;
            }

            $this->sendUserPushNotification(
                user: $subscription->user,
                messageKey: 'subscription_expire_reminder',
                title: translate('messages.Subscription Expire Reminder'),
                type: 'customer_subscription_expire_reminder',
                dataId: $subscription->id,
            );
        }
    }


    private function sendUserPushNotification(
        User $user,
        string $messageKey,
        string $title,
        string $type,
        int|string|null $dataId = null,
    ): bool {
        if (!$user->cm_firebase_token || $user->cm_firebase_token === '@') {
            return false;
        }

        $notificationMessage =  NotificationMessage::with(['translations' => function ($query) use ($user) {
                                    $query->where('locale', $user->current_language_key ?: 'en');
                                    }])
                                ->where('key', $messageKey)
                                ->where('user_type', 'user')
                                ->where('status', 1)
                                ->first();

        if (!$notificationMessage) {
            return false;
        }

        $description = Helpers::text_variable_data_format(
            value: $notificationMessage->message,
            user_name: trim($user->f_name . ' ' . $user->l_name),
        );

        $data = Helpers::makeDataForPushNotification(
            title: $title,
            message: $description,
            type: $type,
            dataId: $dataId,
        );

        try {
            Helpers::send_push_notif_to_device($user->cm_firebase_token, $data);
            Helpers::insertDataOnNotificationTable($data, 'user', $user->id);
            return true;
        } catch (\Exception $e) {
            info($e->getMessage());
            return false;
        }
    }
}
