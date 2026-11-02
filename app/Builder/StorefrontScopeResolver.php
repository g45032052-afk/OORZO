<?php

namespace App\Builder;

use App\Models\Restaurant;
use Illuminate\Http\Request;
use Modules\Builder\Contracts\StorefrontScopeResolver as StorefrontScopeResolverContract;
use Modules\Builder\Entities\TenantDomainConfig;
use Modules\Builder\ValueObjects\StorefrontScope;

/**
 * StackFood storefront scope — resolves a Restaurant from the request host
 * (tenant_domain_configs) or a `?restaurant=`/`?store=` admin-preview fallback.
 * Food-only: moduleId null, moduleType 'food'.
 */
class StorefrontScopeResolver implements StorefrontScopeResolverContract
{
    public function resolveFromRequest(Request $request): ?StorefrontScope
    {
        $restaurant = $this->resolveRestaurant($request);

        if (!$restaurant) {
            return null;
        }

        return new StorefrontScope(
            tenantId: $restaurant->vendor_id,
            subTenantId: $restaurant->id,
            moduleId: null,
            regionId: $restaurant->zone_id,
            logoUrl: $this->safeLogoUrl($restaurant),
            displayName: $restaurant->slug ?? null,
            moduleType: 'food',
            coverImageUrl: $this->safeCoverImageUrl($restaurant),
            contactNumber: $restaurant->phone ?? null,
        );
    }

    private function resolveRestaurant(Request $request): ?Restaurant
    {
        $host = $this->normalizeHost($request->getHost());

        if ($host) {
            $restaurant = $this->resolveByDomain($host);
            if ($restaurant) {
                return $restaurant;
            }
        }

        $identifier = $request->query('restaurant_id', $request->query('restaurant', $request->query('store')));

        if (!$identifier) {
            return null;
        }

        return Restaurant::query()
            ->select(['id', 'vendor_id', 'slug', 'zone_id', 'logo', 'cover_photo', 'phone'])
            ->when(
                is_numeric($identifier),
                fn ($query) => $query->where('id', (int) $identifier),
                fn ($query) => $query->where('slug', $identifier)
            )
            ->first();
    }

    private function resolveByDomain(string $host): ?Restaurant
    {
        $domainConfig = TenantDomainConfig::query()
            ->select(['tenant_id', 'sub_tenant_id', 'website_visibility'])
            ->where(function ($query) use ($host) {
                $query->where('domain', $host);

                if (\str_starts_with($host, 'www.')) {
                    $query->orWhere('domain', \substr($host, 4));
                } else {
                    $query->orWhere('domain', 'www.' . $host);
                }
            })
            ->where(function ($query) {
                $query->where('is_connected', true)
                      ->orWhere('type', 'sub-domain');
            })
            ->first();

        if (!$domainConfig) {
            return null;
        }

        // Website Visibility toggle — when off, refuse to resolve so
        // RequireStorefrontScope 404s the storefront (taken offline).
        if (!$domainConfig->website_visibility) {
            return null;
        }

        return Restaurant::query()
            ->select(['id', 'vendor_id', 'slug', 'zone_id', 'logo', 'cover_photo', 'phone'])
            ->where('id', $domainConfig->sub_tenant_id)
            ->where('vendor_id', $domainConfig->tenant_id)
            ->first();
    }

    private function normalizeHost(?string $host): ?string
    {
        if (!$host) {
            return null;
        }

        return \strtolower(\trim($host));
    }

    private function safeLogoUrl(Restaurant $restaurant): ?string
    {
        try {
            return $restaurant->logo_full_url;
        } catch (\Throwable) {
            return null;
        }
    }

    private function safeCoverImageUrl(Restaurant $restaurant): ?string
    {
        try {
            return $restaurant->cover_photo_full_url;
        } catch (\Throwable) {
            return null;
        }
    }
}
