<?php

namespace Modules\ReelsModule\Http\Resources;

use App\CentralLogics\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReelDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $streamUrl = route('customer.reels.show', [
            'reel_id' => $this->id,
            'stream' => 1,
        ]);

        return [
            'id' => $this->id,
            'description' => $this->description,
            'food_id' => $this->food_id,
            'order_now_button' => (bool) $this->order_now_button && $this->activeFood !== null,
            'video_url' => $streamUrl,
            'thumbnail_url' => $this->thumbnail_full_url,
            'restaurant' => [
                'id' => $this->restaurant?->id,
                'name' => $this->restaurant?->name,
                'logo_full_url' => $this->restaurant?->logo_full_url,
                'verified_seller' => Helpers::get_verified_seller_status($this->restaurant, $this->restaurant?->restaurant_config),
            ],
            'total_views' => (int) $this->total_views,
            'total_likes' => (int) $this->total_likes,
            'total_store_visits' => (int) $this->total_store_visits,
            'total_sale' => (int) ($this->total_sale ?? $this->order_count ?? 0),
            'total_sale_amount' => (float) ($this->total_sale_amount ?? 0),
        ];
    }
}
