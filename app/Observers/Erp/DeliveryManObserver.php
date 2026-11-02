<?php

namespace App\Observers\Erp;

use App\Jobs\Erp\SendErpWebhookJob;
use App\Models\DeliveryMan;
use App\Models\ErpApiToken;

class DeliveryManObserver
{
    public function created(DeliveryMan $deliveryMan): void
    {
        if ($deliveryMan->application_status === 'pending') {
            return;
        }

        if (! $this->isAdminSalaried($deliveryMan)) {
            return;
        }

        $this->dispatchCreated($deliveryMan);
    }

    public function updated(DeliveryMan $deliveryMan): void
    {
        if (! $deliveryMan->wasChanged('application_status')) {
            return;
        }

        if ($deliveryMan->application_status !== 'approved') {
            return;
        }

        if (! $this->isAdminSalaried($deliveryMan)) {
            return;
        }

        $this->dispatchCreated($deliveryMan);
    }

    /**
     * Only admin-owned, fixed-salary deliverymen mirror to ERP. Mirrors the
     * filter on the poll endpoint (ErpDeliveryManController). Freelancers
     * (earning != 0) and restaurant-affiliated deliverymen (restaurant_id set)
     * are out of ERP scope.
     */
    protected function isAdminSalaried(DeliveryMan $deliveryMan): bool
    {
        return empty($deliveryMan->restaurant_id)
            && (int) $deliveryMan->earning === 0;
    }

    protected function dispatchCreated(DeliveryMan $deliveryMan): void
    {
        $tokens = ErpApiToken::query()->withWebhook()->get();
        if ($tokens->isEmpty()) {
            return;
        }

        $deliveryMan->loadMissing([
            'zone:id,name',
            'vehicle:id,type',
        ]);
        $payload = $deliveryMan->toArray();

        // StackFood uses restaurant_id but ERP expects store_id
        $payload['store_id'] = $deliveryMan->restaurant_id;

        foreach ($tokens as $token) {
            SendErpWebhookJob::dispatch($token->id, 'delivery_man.created', $payload);
        }
    }
}
