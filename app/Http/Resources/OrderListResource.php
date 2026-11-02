<?php

namespace App\Http\Resources;

use App\CentralLogics\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One row of the customer's My Orders screen — the same card the Running,
 * Repeat and History tabs all draw.
 *
 * A repeat order is a subscription order: StackFood keeps one order per
 * subscription and records each occurrence in subscription_logs, so what mart
 * calls a repeat container is this row, and `occurrence_count` is how many
 * times it has come round.
 */
class OrderListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isRepeat = $this->subscription_id !== null;

        return [
            'id' => $this->id,
            'type' => $isRepeat ? 'repeat' : 'regular',
            'is_repeat' => $isRepeat,
            'subscription_id' => $this->subscription_id,
            'occurrence_count' => (int) ($this->subscription_logs_count ?? 0),
            'order_status' => $this->order_status,
            'status_label' => translate('messages.'.$this->order_status),
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'order_type' => $this->order_type,
            'scheduled' => (int) $this->scheduled,
            'schedule_at' => $this->schedule_at ? (string) $this->schedule_at : null,
            'details_count' => (int) ($this->details_count ?? 0),
            'items' => $this->whenLoaded('details', fn () => $this->details->map(fn ($detail) => $this->itemPayload($detail))->values()),
            'order_amount' => (float) $this->order_amount,
            'coupon_code' => $this->coupon_code,
            'is_reviewed' => (int) ($this->reviews_count ?? 0) >= (int) ($this->details_count ?? 0),
            'can_reorder' => ! $isRepeat && ! $this->hasUnrepeatableItem(),
            'currency_symbol' => Helpers::currency_symbol(),
            'restaurant' => Helpers::orderRestaurantPayload($this->resource),
            'customer' => Helpers::orderCustomerPayload($this->resource),
            'created_at' => $this->created_at ? (string) $this->created_at : null,
        ];
    }

    /**
     * Does the order hold an item that cannot simply be ordered again?
     *
     * A campaign item exists only while its campaign runs, and a BOGO line is
     * half of a bundle - its free item is priced by the offer, not by the food -
     * so neither survives being handed back to add-to-cart on its own, and
     * Reorder on those rows only leads to an error at the cart.
     *
     * Read off the eager loaded details so the list costs no extra query; the
     * fallback is for any caller that builds this row without them.
     */
    private function hasUnrepeatableItem(): bool
    {
        if ($this->relationLoaded('details')) {
            return $this->details->contains(
                fn ($detail) => $detail->item_campaign_id !== null || $detail->bogo_offer_id !== null
            );
        }

        return $this->details()
            ->where(fn ($query) => $query->whereNotNull('item_campaign_id')->orWhereNotNull('bogo_offer_id'))
            ->exists();
    }

    private function itemPayload($detail): array
    {
        $item = $detail->food ?? $detail->campaign;
        $snapshot = json_decode($detail->food_details ?? '', true) ?: [];

        return [
            'name' => $item?->name ?? $item?->title ?? ($snapshot['name'] ?? $snapshot['title'] ?? null),
            'image_full_url' => $item?->image_full_url
                ?? (isset($snapshot['image']) ? Helpers::get_full_url('product', $snapshot['image'], 'public') : null),
        ];
    }
}
