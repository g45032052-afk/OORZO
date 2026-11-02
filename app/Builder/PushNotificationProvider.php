<?php

namespace App\Builder;

use App\Models\User;
use Modules\Builder\Contracts\PushNotificationProvider as PushNotificationProviderContract;

/**
 * StackFood host adapter — persists customer FCM tokens against
 * `users.cm_firebase_token`, the same column the mobile API uses
 * (CustomerController::update_cm_firebase_token), so a token registered from
 * the storefront is picked up by StackFood's existing push dispatch pipeline.
 *
 * Single-column = last-device-wins (matches the host model).
 */
class PushNotificationProvider implements PushNotificationProviderContract
{
    public function storeCustomerToken(int $customerId, string $token): void
    {
        User::query()
            ->where('id', $customerId)
            ->update(['cm_firebase_token' => $token]);
    }

    public function clearCustomerToken(int $customerId): void
    {
        User::query()
            ->where('id', $customerId)
            ->update(['cm_firebase_token' => null]);
    }
}
