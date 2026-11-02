<?php

namespace App\Observers\Erp;

use App\Jobs\Erp\SendErpWebhookJob;
use App\Models\ErpApiToken;
use App\Models\Restaurant;

/**
 * Fires an ERP webhook for every active token that has a webhook_url
 * configured. Runs on Restaurant::created so any code path that inserts
 * a restaurant row (admin form, API, seeder) propagates to the ERP.
 * Re-fires on Restaurant::updated when status flips to 1 — that's the
 * admin-approval transition for a previously-pending self-registered
 * vendor, and the ERP uses it to promote the existing CRM Lead to a
 * CRM Customer.
 */
class RestaurantObserver
{
    public function created(Restaurant $restaurant): void
    {
        $this->dispatchVendorCreated($restaurant);
    }

    /**
     * Self-registered vendors land with restaurant.status=0. When admin
     * approves them (VendorController::update_application), status flips
     * 0→1 and we re-fire vendor.created so the ERP can convert the Lead
     * to a Customer. Other Restaurant updates don't pass the wasChanged
     * gate and are correctly ignored.
     */
    public function updated(Restaurant $restaurant): void
    {
        if (! $restaurant->wasChanged('status')) {
            return;
        }
        if ((int) $restaurant->status !== 1) {
            return;
        }
        $this->dispatchVendorCreated($restaurant);
    }

    protected function dispatchVendorCreated(Restaurant $restaurant): void
    {
        $tokens = ErpApiToken::query()->withWebhook()->get();
        if ($tokens->isEmpty()) {
            return;
        }

        // Pull DB-default values back into the model. Admin\VendorController::store()
        // doesn't set status explicitly and relies on the restaurants.status DB
        // default (=1) — without refresh the in-memory model has status=null and
        // the ERP would route the payload to a CRM Lead instead of a CRM Customer.
        $restaurant->refresh();
        $restaurant->loadMissing([
            'zone:id,name',
            'vendor:id,f_name,l_name,email,phone,status',
        ]);
        $payload = $restaurant->toArray();

        foreach ($tokens as $token) {
            SendErpWebhookJob::dispatch($token->id, 'vendor.created', $payload);
        }
    }
}
