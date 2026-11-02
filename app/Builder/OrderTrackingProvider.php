<?php

namespace App\Builder;

use App\Models\Order;
use Modules\Builder\Contracts\OrderTrackingProvider as OrderTrackingProviderContract;
use Modules\Builder\ValueObjects\StorefrontScope;

/**
 * Host adapter for storefront order tracking.
 *
 * Mirrors App\Http\Controllers\Api\V1\OrderController::track_order —
 * authenticated lookups join on (user_id, is_guest=0), guest lookups match
 * the contact phone embedded in `delivery_address` JSON with is_guest=1.
 * The DTO mapping is delegated to OrderProvider::formatOrder() so the profile
 * order-details page and the tracking page render through the same renderer.
 * StackFood is food-only: scope filters on `restaurant_id`, no module.
 */
class OrderTrackingProvider implements OrderTrackingProviderContract
{
    public function __construct(private OrderProvider $orderFormatter)
    {
    }

    public function track(
        ?StorefrontScope $scope,
        int $orderId,
        ?string $contactNumber,
        ?int $customerId,
    ): ?array {
        // The host stores the contact number with a leading `+` in the
        // delivery_address JSON — match the same normalization here.
        $normalizedPhone = $contactNumber
            ? (str_starts_with($contactNumber, '+') ? $contactNumber : '+' . ltrim($contactNumber))
            : null;

        $order = Order::query()
            ->with([
                'details', 'restaurant', 'customer',
                'delivery_man.rating', 'delivery_man.last_location',
                // Read by OrderProvider::mapPricing() through Helpers::pro_discount_data().
                'orderProDiscount',
            ])
            ->where('id', $orderId)
            ->when(
                $customerId,
                // Authed: must own the order, must NOT be a guest order.
                fn ($q) => $q->where('user_id', $customerId)->where('is_guest', 0),
                // Guest: phone must match what was captured at place-order.
                fn ($q) => $q
                    ->where('is_guest', 1)
                    ->whereJsonContains('delivery_address->contact_person_number', $normalizedPhone),
            )
            ->when(
                $scope?->subTenantId !== null,
                fn ($q) => $q->where('restaurant_id', $scope->subTenantId),
            )
            ->first();

        return $order ? $this->orderFormatter->formatOrder($order) : null;
    }
}
