<?php

namespace Modules\ReelsModule\Http\Resources;

use App\CentralLogics\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReelListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'reel_id' => $this->id,
            'description' => $this->description,
            'thumbnail_full_url' => $this->thumbnail_full_url,
            'restaurant_id' => $this->restaurant_id,
            'restaurant_name' => $this->restaurant?->name,
            'restaurant_logo_full_url' => $this->restaurant?->logo_full_url,
            'food_id' => $this->food_id,
            'order_now_button' => (bool) $this->order_now_button && $this->activeFood !== null,
            'verified_seller' => Helpers::get_verified_seller_status($this->restaurant, $this->restaurant?->restaurant_config),
            'stats' => (new ReelStatsResource($this->resource))->resolve(),
        ];
    }
}
