<?php

namespace App\Builder;

use App\CentralLogics\Helpers;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;
use Modules\Builder\Contracts\BuilderScopeResolver as BuilderScopeResolverContract;
use Modules\Builder\ValueObjects\StorefrontScope;

/**
 * StackFood CMS scope — resolved from the authenticated vendor / vendor_employee.
 * Store→Restaurant; food-only so moduleId is null and moduleType is 'food'.
 */
class BuilderScopeResolver implements BuilderScopeResolverContract
{
    public function resolveFromAuth(): ?StorefrontScope
    {
        if (!Auth::guard('vendor')->check() && !Auth::guard('vendor_employee')->check()) {
            return null;
        }

        $vendorId     = Helpers::get_vendor_id() ?: null;
        $restaurantId = Helpers::get_restaurant_id() ?: null;
        $restaurant   = $restaurantId ? $this->loadRestaurant($restaurantId) : null;

        return new StorefrontScope(
            tenantId: $vendorId,
            subTenantId: $restaurantId,
            moduleId: null,
            regionId: $restaurant?->zone_id,
            logoUrl: $this->safeLogoUrl($restaurant),
            displayName: $restaurant?->slug ?? null,
            moduleType: 'food',
        );
    }

    private function loadRestaurant(int $restaurantId): ?Restaurant
    {
        return Restaurant::query()
            ->select(['id', 'vendor_id', 'slug', 'zone_id', 'logo'])
            ->find($restaurantId);
    }

    private function safeLogoUrl(?Restaurant $restaurant): ?string
    {
        if (!$restaurant) {
            return null;
        }

        try {
            return $restaurant->logo_full_url;
        } catch (\Throwable) {
            return null;
        }
    }
}
