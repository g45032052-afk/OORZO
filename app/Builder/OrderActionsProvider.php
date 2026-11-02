<?php

namespace App\Builder;

use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\Mail\RefundRequest;
use App\Models\AddOn;
use App\Models\Admin;
use App\Models\BusinessSetting;
use App\Models\Cart;
use App\Models\Food;
use App\Models\ItemCampaign;
use App\Models\Order;
use App\Models\OrderCancelReason;
use App\Models\OrderDetail;
use App\Models\Refund;
use App\Models\RefundReason;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Modules\Builder\Contracts\CartProvider;
use Modules\Builder\Contracts\OrderActionsProvider as OrderActionsProviderContract;
use Modules\Builder\ValueObjects\StorefrontScope;

/**
 * Storefront-side counterparts to the host's order customer actions.
 *
 * StackFood is food-only / single-module: the cart and order scope on
 * `restaurant_id` (no module_id), items are `Food`/`ItemCampaign`, there is no
 * flash-sale or per-item stock system (config('module.*') is absent), so the
 * cancel/reorder stock+flash restoration branches collapse away.
 *
 * Ownership mirrors the host: authenticated = (user_id, is_guest=0); guest =
 * phone-match against delivery_address->contact_person_number.
 */
class OrderActionsProvider implements OrderActionsProviderContract
{
    public function cancellationReasons(): array
    {
        return OrderCancelReason::query()
            ->where('status', 1)
            ->where('user_type', 'customer')
            ->orderBy('id')
            ->get(['id', 'reason'])
            ->map(fn ($r) => ['id' => (int) $r->id, 'reason' => (string) $r->reason])
            ->all();
    }

    public function refundReasons(): array
    {
        return RefundReason::query()
            ->where('status', 1)
            ->orderBy('id')
            ->get(['id', 'reason'])
            ->map(fn ($r) => ['id' => (int) $r->id, 'reason' => (string) $r->reason])
            ->all();
    }

    public function cancel(
        ?StorefrontScope $scope,
        int $orderId,
        ?int $customerId,
        ?string $guestPhone,
        ?string $reason = null,
        ?string $note = null,
    ): array {
        $order = $this->loadOrder($scope, $orderId, $customerId, $guestPhone);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found.'];
        }

        // Mirrors the host cancel window: pending OR failed only.
        if (!in_array($order->order_status, ['pending', 'failed'], true)) {
            return ['success' => false, 'error' => 'This order can no longer be cancelled.'];
        }

        if (!$reason && !$note) {
            return ['success' => false, 'error' => 'Please provide a reason or a note for cancelling.'];
        }

        try {
            DB::beginTransaction();

            // Food-only: no per-item stock or flash-sale restoration to undo.
            if ((int) $order->is_guest === 0) {
                try { OrderLogic::refund_before_delivered($order); } catch (\Throwable) { /* best effort */ }
            }

            $order->order_status        = 'canceled';
            $order->canceled            = now();
            $order->cancellation_reason = $reason ?: null;
            $order->cancellation_note   = $note ?: null;
            $order->canceled_by         = 'customer';
            $order->save();

            DB::commit();
        } catch (\Throwable) {
            DB::rollBack();
            return ['success' => false, 'error' => 'Could not cancel the order. Please try again.'];
        }

        try { Helpers::send_order_notification($order); } catch (\Throwable) { /* best effort */ }

        return ['success' => true, 'message' => 'Order cancelled.'];
    }

    public function switchToCod(
        ?StorefrontScope $scope,
        int $orderId,
        ?int $customerId,
        ?string $guestPhone,
    ): array {
        $order = $this->loadOrder($scope, $orderId, $customerId, $guestPhone);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found.'];
        }

        if ($order->payment_method === 'cash_on_delivery') {
            return ['success' => false, 'error' => 'This order is already cash on delivery.'];
        }

        try {
            DB::beginTransaction();

            $order->offline_payments()?->delete();

            if ($order->payment_method === 'partial_payment') {
                $order->payments()
                    ->where('payment_status', 'unpaid')
                    ->update(['payment_method' => 'cash_on_delivery']);
            }

            if ($order->order_status !== 'pending') {
                $order->order_status = 'pending';
            }
            $order->payment_method = 'cash_on_delivery';
            $order->save();

            DB::commit();
        } catch (\Throwable) {
            DB::rollBack();
            return ['success' => false, 'error' => 'Could not switch payment method. Please try again.'];
        }

        try { Helpers::send_order_notification($order); } catch (\Throwable) { /* best effort */ }

        return ['success' => true, 'message' => 'Switched to Cash on Delivery.'];
    }

    public function repay(
        ?StorefrontScope $scope,
        int $orderId,
        int $customerId,
        string $paymentMethod,
    ): array {
        $order = $this->loadOrder($scope, $orderId, $customerId, null);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found.'];
        }

        if ($order->payment_status === 'paid') {
            return ['success' => false, 'error' => 'This order has already been paid.'];
        }

        $order->payment_method = 'digital_payment';
        $order->save();

        // Path-based callback (the host's ?flag= append would collide with a
        // query string). PaymentCallbackController normalises + redirects.
        $callback = url(route(
            'storefront.payment_callback',
            ['orderId' => $orderId],
            false,
        ));

        try {
            $url = route('payment-mobile', [
                'order_id'         => $orderId,
                'customer_id'      => $customerId,
                'payment_method'   => $paymentMethod,
                'payment_platform' => 'web',
                'callback'         => $callback,
            ]);
        } catch (\Throwable) {
            return ['success' => false, 'error' => 'Could not build the payment URL.'];
        }

        return ['success' => true, 'paymentRedirect' => $url ?: null];
    }

    public function requestRefund(
        ?StorefrontScope $scope,
        int $orderId,
        int $customerId,
        string $customerReason,
        ?string $customerNote,
        array $imageFiles,
    ): array {
        if ((int) (BusinessSetting::query()->where('key', 'refund_active_status')->value('value') ?? 0) !== 1) {
            return ['success' => false, 'error' => 'Refund requests are not currently accepted.'];
        }

        $order = $this->loadOrder($scope, $orderId, $customerId, null);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found.'];
        }

        if ($order->order_status !== 'delivered' || $order->payment_status !== 'paid') {
            return ['success' => false, 'error' => 'You can only request a refund on a delivered, paid order.'];
        }

        $imagePaths = [];
        foreach ($imageFiles as $file) {
            try {
                $path = Helpers::upload('refund/', 'png', $file);
                $imagePaths[] = ['img' => $path, 'storage' => Helpers::getDisk()];
            } catch (\Throwable) {
                // Skip individual upload failures rather than aborting the refund.
            }
        }

        $refundAmount = round(
            $order->order_amount - $order->delivery_charge - ($order->dm_tips ?? 0),
            (int) (config('round_up_to_digit') ?? 2),
        );

        try {
            DB::beginTransaction();

            $refund = new Refund();
            $refund->order_id        = $order->id;
            $refund->user_id         = $order->user_id;
            $refund->order_status    = $order->order_status;
            $refund->refund_status   = 'pending';
            $refund->refund_method   = 'wallet';
            $refund->customer_reason = $customerReason;
            $refund->customer_note   = $customerNote;
            $refund->refund_amount   = $refundAmount;
            $refund->image           = json_encode($imagePaths);
            $refund->save();

            $order->order_status     = 'refund_requested';
            $order->refund_requested = now();
            $order->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::warning('Refund request failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Could not file the refund request. Please try again.'];
        }

        try {
            $admin = Admin::query()->where('role_id', 1)->first();
            $mailStatus = Helpers::get_mail_status('refund_request_mail_status_admin');
            if (config('mail.status')
                && $admin?->email
                && $mailStatus == '1'
                && Helpers::getNotificationStatusData('admin', 'order_refund_request', 'mail_status')
            ) {
                Mail::to($admin->getRawOriginal('email'))->send(new RefundRequest($order->id));
            }
        } catch (\Throwable) { /* swallow — refund itself is committed */ }

        return ['success' => true, 'message' => 'Refund request submitted.'];
    }

    /**
     * StackFood's order-invoice blade (`new_invoice`) is driven by a large set
     * of pre-computed view variables assembled in the host's order controllers,
     * and there is no storefront-branded invoice view. Rather than half-render a
     * broken PDF, this performs the ownership check and reports the feature as
     * unavailable until a storefront invoice view is wired. (Other actions are
     * fully functional.)
     */
    public function downloadInvoice(?StorefrontScope $scope, int $orderId, ?int $customerId, ?string $guestPhone = null): array
    {
        $order = $this->loadOrder($scope, $orderId, $customerId, $guestPhone);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found.'];
        }

        try {
            // `storefront-order-invoice` is a self-contained, RESTAURANT-branded
            // invoice blade in the host resource path (it uses host helpers like
            // App\CentralLogics\Helpers and the Restaurant.logo_full_url accessor,
            // which must not leak into the portable Builder module). loadOrder()
            // already eager-loads `restaurant` + `details` — exactly what the
            // blade reads. gen_mpdf streams the PDF straight to the browser ('D').
            $mpdfView = View::make('storefront-order-invoice', ['order' => $order]);
            Helpers::gen_mpdf($mpdfView, 'OrderInvoice', (string) $order->id);
        } catch (\Throwable $e) {
            Log::warning('Storefront invoice download failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => 'Could not generate the invoice. Please try again.'];
        }

        return ['success' => true];
    }

    private function loadOrder(?StorefrontScope $scope, int $orderId, ?int $customerId, ?string $guestPhone): ?Order
    {
        $normalizedPhone = $guestPhone
            ? (str_starts_with($guestPhone, '+') ? $guestPhone : '+' . ltrim($guestPhone, '+ '))
            : null;

        return Order::query()
            ->with(['details', 'restaurant'])
            ->where('id', $orderId)
            ->when(
                $customerId,
                fn ($q) => $q->where('user_id', $customerId)->where('is_guest', 0),
                fn ($q) => $q
                    ->where('is_guest', 1)
                    ->whereJsonContains('delivery_address->contact_person_number', $normalizedPhone),
            )
            ->when(
                $scope?->subTenantId !== null,
                fn ($q) => $q->where('restaurant_id', $scope->subTenantId),
            )
            ->first();
    }

    /* ─── reorder ─────────────────────────────────────────── */

    /**
     * Re-add every item from a past order to the cart. All-or-nothing — if any
     * line fails the live-catalog pre-flight, nothing is added and the caller
     * gets the full list of reasons. Auth-only; the cart is restaurant-scoped.
     */
    public function reorder(?StorefrontScope $scope, int $orderId, int $customerId): array
    {
        $order = Order::query()
            ->with(['details.food', 'details.campaign', 'restaurant'])
            ->where('id', $orderId)
            ->where('user_id', $customerId)
            ->where('is_guest', 0)
            ->when(
                $scope?->subTenantId !== null,
                fn ($q) => $q->where('restaurant_id', $scope->subTenantId),
            )
            ->first();

        if (!$order) {
            return ['success' => false, 'errors' => ['Order not found.']];
        }

        $details = $order->details ?? collect();
        if ($details->isEmpty()) {
            return ['success' => false, 'errors' => ['This order has no items to re-add.']];
        }

        // The cart is restaurant-scoped; require an active storefront restaurant.
        $activeRestaurantId = $scope?->subTenantId;
        if (!$activeRestaurantId) {
            return ['success' => false, 'errors' => ['Open the storefront first, then reorder.']];
        }
        if ((int) $order->restaurant_id !== (int) $activeRestaurantId) {
            return ['success' => false, 'errors' => ['This order is from a different restaurant. Switch restaurants to reorder it.']];
        }

        $cart = \app(CartProvider::class);

        $errors   = [];
        $payloads = [];
        foreach ($details as $d) {
            [$ok, $payload, $err] = $this->planReorderLine($d, $activeRestaurantId, $customerId);
            if (!$ok) {
                $errors[] = $err;
                continue;
            }
            $payloads[] = $payload;
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }
        if (empty($payloads)) {
            return ['success' => false, 'errors' => ['Nothing to add — please contact support.']];
        }

        try {
            DB::beginTransaction();
            foreach ($payloads as $p) {
                $cart->add($p);
            }
            DB::commit();
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            $messages = [];
            foreach ($e->errors() as $fieldMessages) {
                foreach ((array) $fieldMessages as $m) {
                    $messages[] = (string) $m;
                }
            }
            return ['success' => false, 'errors' => $messages ?: ['Could not add items to cart.']];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::warning('Reorder write failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['Could not add items to cart. Please try again.']];
        }

        $count = count($payloads);
        $oid = (int) $order->id;
        return [
            'success' => true,
            'count'   => $count,
            'message' => $count === 1
                ? "Added 1 item from order #{$oid} to your cart."
                : "Added {$count} items from order #{$oid} to your cart.",
        ];
    }

    /**
     * Per-line pre-flight. Returns [true, payload, null] when ready for
     * CartProvider::add(), or [false, null, reason] with a user-facing message.
     */
    private function planReorderLine(OrderDetail $d, int $activeRestaurantId, int $customerId): array
    {
        $isCampaign = !empty($d->item_campaign_id);
        $item = $isCampaign ? ($d->campaign ?? null) : ($d->food ?? null);

        $snapshotName = $this->snapshotItemName($d);

        if (!$item) {
            return [false, null, "{$snapshotName} is no longer available."];
        }
        if ((int) ($item->restaurant_id ?? 0) !== $activeRestaurantId) {
            return [false, null, "{$snapshotName} is from a different restaurant."];
        }
        if ((int) $item->status !== 1) {
            return [false, null, "{$snapshotName} is currently unavailable."];
        }

        if ($isCampaign) {
            $end = $item->end_date ? $item->end_date->format('Y-m-d') : null;
            if ($end && $end < date('Y-m-d')) {
                return [false, null, "The offer for {$snapshotName} has ended."];
            }
        }

        $now = date('H:i:s');
        $startTime = $item->available_time_starts ?? null;
        $endTime   = $item->available_time_ends ?? null;
        if ($startTime && $endTime && ($now < (string) $startTime || $now > (string) $endTime)) {
            return [false, null, "{$snapshotName} isn't available right now."];
        }

        // order_details.variation stores the food-variation snapshot in a
        // DIFFERENT shape than the cart expects. Snapshot shape:
        //     [{name, values: [{label, optionPrice}]}]
        // Cart shape (what Helpers::cart_product_data_formatting reads via
        // `values.label`):
        //     [{name, values: {label: ["small"]}}]
        // Passing the snapshot shape straight through leaves `values.label`
        // undefined → in_array($x, null) TypeError when the cart renders after
        // a reorder. Normalize once, here, before the payload reaches
        // CartProvider::add(). StackFood is food-only, so this runs for every
        // line unconditionally.
        $variation = $this->normalizeFoodVariation($this->decodeJsonArray($d->variation));
        $addOnIds  = array_values(array_map('intval', $this->decodeJsonArray($d->add_on_ids)));
        $addOnQtys = array_values(array_map('intval', $this->decodeJsonArray($d->add_on_qtys)));
        $qty       = max(1, (int) $d->quantity);

        // Addons exist + still active.
        if (!empty($addOnIds)) {
            $liveAddons = AddOn::query()
                ->whereIn('id', $addOnIds)
                ->where('status', 1)
                ->pluck('id')
                ->all();
            foreach ($addOnIds as $aid) {
                if (!in_array($aid, $liveAddons, true)) {
                    return [false, null, "An add-on for {$snapshotName} is no longer available."];
                }
            }
        }

        // Existing cart qty for the same combo (CartProvider::add bumps an
        // existing line by the reorder qty, so check the cap against the sum).
        $existingCartQty = $this->existingCartLineQty($item, $variation, $addOnIds, $addOnQtys, $customerId);
        $maxCartQty = (int) ($item->maximum_cart_quantity ?? 0);
        if ($maxCartQty > 0 && ($qty + $existingCartQty) > $maxCartQty) {
            return [false, null, $existingCartQty > 0
                ? "You can add at most {$maxCartQty} of {$snapshotName} per order (you already have {$existingCartQty} in your cart)."
                : "You can add at most {$maxCartQty} of {$snapshotName} per order."];
        }

        $price = $this->liveLinePrice($item, $variation, $addOnIds, $addOnQtys, $qty);

        $payload = [
            'item_id'     => (int) $item->id,
            'model'       => $isCampaign ? 'ItemCampaign' : 'Item',
            'price'       => $price,
            'quantity'    => $qty,
            'variation'   => $variation,
            'add_on_ids'  => $addOnIds,
            'add_on_qtys' => $addOnQtys,
        ];
        return [true, $payload, null];
    }

    /**
     * Recompute the line price from live food catalog data: each chosen food
     * option's optionPrice ADDS to the base; addons are summed per-line.
     */
    private function liveLinePrice($item, array $variation, array $addOnIds, array $addOnQtys, int $qty): float
    {
        $base = (float) ($item->price ?? 0);

        if (!empty($variation)) {
            $live = $this->decodeJsonArray($item->variations ?? []);
            foreach ($variation as $sel) {
                $name   = (string) ($sel['name'] ?? '');
                $labels = $this->coerceLabels($sel['values']['label'] ?? null);
                if ($name === '') continue;
                foreach ($live as $group) {
                    if (($group['name'] ?? null) !== $name) continue;
                    foreach (($group['values'] ?? []) as $val) {
                        if (in_array((string) ($val['label'] ?? ''), $labels, true)) {
                            $base += (float) ($val['optionPrice'] ?? 0);
                        }
                    }
                }
            }
        }

        $addonExtra = 0.0;
        if (!empty($addOnIds)) {
            $liveAddons = AddOn::query()
                ->whereIn('id', $addOnIds)
                ->where('status', 1)
                ->get(['id', 'price'])
                ->keyBy('id');
            foreach ($addOnIds as $i => $aid) {
                $aq = (int) ($addOnQtys[$i] ?? 0);
                if (isset($liveAddons[$aid])) {
                    $addonExtra += (float) $liveAddons[$aid]->price * $aq;
                }
            }
        }

        return round($base * $qty + $addonExtra, (int) (config('round_up_to_digit') ?? 2));
    }

    private function existingCartLineQty($item, array $variation, array $addOnIds, array $addOnQtys, int $customerId): int
    {
        $itemType = $item instanceof ItemCampaign ? ItemCampaign::class : Food::class;
        $needle = $this->variationMatchKey($variation);
        $wantAddOnIds  = array_values(array_map('intval', $addOnIds));
        $wantAddOnQtys = array_values(array_map('intval', $addOnQtys));

        $rows = Cart::query()
            ->where('user_id', $customerId)
            ->where('is_guest', 0)
            ->where('item_id', $item->id)
            ->whereIn('item_type', [$itemType, class_basename($itemType)])
            ->get(['variations', 'add_on_ids', 'add_on_qtys', 'quantity']);

        $total = 0;
        foreach ($rows as $row) {
            $rowVar       = $this->decodeJsonArray($row->variations);
            $rowAddOnIds  = array_values(array_map('intval', $this->decodeJsonArray($row->add_on_ids)));
            $rowAddOnQtys = array_values(array_map('intval', $this->decodeJsonArray($row->add_on_qtys)));

            if ($this->variationMatchKey($rowVar) === $needle
                && $rowAddOnIds  == $wantAddOnIds
                && $rowAddOnQtys == $wantAddOnQtys) {
                $total += (int) $row->quantity;
            }
        }
        return $total;
    }

    /** Stable variation identity ignoring volatile price/stock fields. Mirrors CartProvider. */
    private function variationMatchKey(array $variation): string
    {
        $normalized = array_map(static function ($entry) {
            if (!is_array($entry)) return $entry;
            $copy = $entry;
            unset($copy['price'], $copy['stock'], $copy['oldPrice'], $copy['discountPercent'], $copy['inStock']);
            return $copy;
        }, $variation);
        return json_encode($normalized) ?: '';
    }

    /**
     * Convert the order-details food-variation snapshot shape into the shape
     * the cart / Helpers::cart_product_data_formatting expects. StackFood
     * stored variation differently on its two surfaces:
     *   carts:         [{name, values: {label: ["small", "large"]}}]
     *   order_details: [{name, values: [{label, optionPrice}, ...]}]
     *
     * The cart renderer reads `values.label` directly — handing it the
     * snapshot shape leaves `values.label` undefined and blows up with a
     * TypeError. Rewrite `values` into cart shape while preserving every
     * other field on the group (name/min/max/required/…).
     *
     * Idempotent/defensive: an empty variation yields an empty array, and a
     * group already in cart shape (`values` is an assoc array with a `label`
     * key) or carrying no usable `values` is passed through unchanged.
     */
    private function normalizeFoodVariation(array $orderVariation): array
    {
        $out = [];
        foreach ($orderVariation as $group) {
            if (!is_array($group)) {
                $out[] = $group;
                continue;
            }

            $values = $group['values'] ?? null;

            // Already cart-shape, or nothing convertible — leave untouched so
            // the transform is idempotent and safe to run over mixed data.
            if (!is_array($values) || array_key_exists('label', $values)) {
                $out[] = $group;
                continue;
            }

            // Order-details snapshot: a list of {label, optionPrice}. Collect
            // the raw labels, then reuse coerceLabels() for the string coercion.
            $labels = [];
            foreach ($values as $v) {
                if (is_array($v) && array_key_exists('label', $v)) {
                    $labels[] = $v['label'];
                }
            }

            $normalized = $group;
            $normalized['values'] = ['label' => $this->coerceLabels($labels)];
            $out[] = $normalized;
        }
        return $out;
    }

    private function snapshotItemName(OrderDetail $d): string
    {
        $details = $this->decodeJsonArray($d->food_details ?? null);
        $name = $details['name'] ?? null;
        return is_string($name) && $name !== '' ? $name : 'Item';
    }

    private function coerceLabels($raw): array
    {
        if (is_array($raw)) {
            return array_values(array_map(static fn ($x) => (string) $x, $raw));
        }
        if (is_string($raw) && $raw !== '') {
            return [$raw];
        }
        return [];
    }

    private function decodeJsonArray($value): array
    {
        if (is_array($value)) return $value;
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    /**
     * StackFood is a food-delivery platform — order items have no digital
     * products to download (a 6amMart e-commerce feature). Explicit
     * not-supported response so the contract is fully satisfied.
     */
    public function downloadDigitalProduct(
        ?StorefrontScope $scope,
        int $orderDetailId,
        ?int $customerId,
        ?string $guestPhone,
    ): array {
        return ['success' => false, 'error' => 'Digital product downloads are not available.'];
    }
}
