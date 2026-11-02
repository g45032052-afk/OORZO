<?php

namespace App\Builder;

use App\Models\CustomerAddress;
use App\Models\Zone;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Modules\Builder\Contracts\LocationProvider as LocationProviderContract;
use Modules\Builder\Services\StorefrontContext;
use Modules\Builder\ValueObjects\Storefront\AddressDTO;
use Modules\Builder\ValueObjects\StorefrontScope;

/**
 * StackFood location + saved-address adapter.
 *
 * StackFood is food-only / single-module, so the 6amMart Store/module axes
 * collapse: the active storefront's region is a `zones` row (StorefrontScope
 * regionId = zone_id), and saved addresses are `App\Models\CustomerAddress`
 * rows keyed by `user_id`.
 *
 * Zone membership is resolved exactly like the host API
 * (CustomerController::add_new_address / ConfigController::get_zone):
 *   Zone::whereContains('coordinates', new Point($lat, $lng, POINT_SRID))
 * i.e. a server-side ST_Contains against the zone polygon. There is no
 * client-trusted zone id — coordinates are always re-validated here.
 *
 * "Current selection" (current/setCurrent/clear) has NO host DB column in
 * StackFood, so it is persisted in the session, scoped per zone so switching
 * storefront zones doesn't surface a selection from another zone. This mirrors
 * the reference's per-shopper, zone-scoped selection semantics with the storage
 * StackFood actually has.
 */
class LocationProvider implements LocationProviderContract
{
    private const SESSION_KEY = 'builder_selected_location';

    public function __construct(private StorefrontContext $context)
    {
    }

    public function current(): ?array
    {
        $stored = Session::get(self::SESSION_KEY);
        if (!\is_array($stored) || !isset($stored['lat'], $stored['lng'])) {
            return null;
        }

        // Drop a selection that belongs to a different storefront zone so the
        // topbar never shows a location outside the active zone.
        $zoneId = $this->currentZoneId();
        if ($zoneId !== null && isset($stored['zone_id']) && (int) $stored['zone_id'] !== $zoneId) {
            return null;
        }

        return [
            'id'      => isset($stored['id']) ? (int) $stored['id'] : null,
            'lat'     => (float) $stored['lat'],
            'lng'     => (float) $stored['lng'],
            'address' => (string) ($stored['address'] ?? ''),
            'label'   => $stored['label'] ?? null,
            'source'  => (string) ($stored['source'] ?? 'map'),
        ];
    }

    public function setCurrent(float $lat, float $lng, string $address, ?int $addressId = null): array
    {
        $label  = null;
        $source = 'map';

        // Saved-address pick: the client only sends the id, so we load the
        // coords/address from the row (ownership + zone enforced below).
        if ($addressId !== null) {
            $row = $this->ownedAddress($addressId);
            if (!$row) {
                throw ValidationException::withMessages([
                    'address_id' => __('Address not found'),
                ]);
            }
            $lat     = (float) $row->latitude;
            $lng     = (float) $row->longitude;
            $address = (string) $row->address;
            $label   = $row->address_type;
            $source  = 'saved';
        }

        $zoneId = $this->resolveZoneIdOrFail($lat, $lng);

        $selection = [
            'id'      => $addressId,
            'lat'     => $lat,
            'lng'     => $lng,
            'address' => $address,
            'label'   => $label,
            'source'  => $source,
            'zone_id' => $zoneId,
        ];

        Session::put(self::SESSION_KEY, $selection);

        return [
            'id'      => $addressId,
            'lat'     => $lat,
            'lng'     => $lng,
            'address' => $address,
            'label'   => $label,
            'source'  => $source,
        ];
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function savedAddresses(): array
    {
        $userId = $this->context->getUserId();
        if (!$userId) {
            return [];
        }

        $zoneId = $this->currentZoneId();

        $rows = CustomerAddress::query()
            ->where('user_id', $userId)
            // Surface only addresses inside the active storefront's zone so the
            // frontend never has to re-check (per the contract).
            ->when($zoneId, fn ($q) => $q->where('zone_id', $zoneId))
            ->latest()
            ->get();

        return $rows
            ->map(fn (CustomerAddress $row) => $this->toAddressDTO($row)->toArray())
            ->values()
            ->all();
    }

    public function addAddress(array $payload): array
    {
        $userId = $this->context->getUserId();
        if (!$userId) {
            throw ValidationException::withMessages([
                '_form' => __('You must be signed in to add an address.'),
            ]);
        }

        $lat = (float) ($payload['latitude'] ?? 0);
        $lng = (float) ($payload['longitude'] ?? 0);
        $zoneId = $this->resolveZoneIdOrFail($lat, $lng);

        $row = new CustomerAddress;
        $row->user_id = $userId;
        $row->zone_id = $zoneId;
        $this->fillAddress($row, $payload, $lat, $lng);
        $row->save();

        return $this->toAddressDTO($row)->toArray();
    }

    public function updateAddress(int $addressId, array $payload): array
    {
        $row = $this->ownedAddress($addressId);
        if (!$row) {
            throw ValidationException::withMessages([
                'address_id' => __('Address not found'),
            ]);
        }

        // Coordinates may be omitted on a partial update; fall back to the
        // existing row so the zone re-check uses the effective coordinates.
        $lat = isset($payload['latitude']) ? (float) $payload['latitude'] : (float) $row->latitude;
        $lng = isset($payload['longitude']) ? (float) $payload['longitude'] : (float) $row->longitude;
        $zoneId = $this->resolveZoneIdOrFail($lat, $lng);

        $row->zone_id = $zoneId;
        $this->fillAddress($row, $payload, $lat, $lng);
        $row->save();

        return $this->toAddressDTO($row)->toArray();
    }

    public function deleteAddress(int $addressId): void
    {
        $row = $this->ownedAddress($addressId);
        if ($row) {
            $row->delete();

            // If the deleted row was the currently selected location, clear it.
            // current() only revalidates zone_id (not existence), so a stale
            // session pointer would otherwise keep surfacing the deleted address.
            $stored = Session::get(self::SESSION_KEY);
            if (\is_array($stored) && isset($stored['id']) && (int) $stored['id'] === $addressId) {
                $this->clear();
            }
        }
    }

    public function storefrontZonePolygon(?StorefrontScope $scope = null): ?array
    {
        $zoneId = $scope?->regionId ?? $this->currentZoneId();
        if (!$zoneId) {
            return null;
        }

        $zone = Zone::query()->find($zoneId, ['id', 'coordinates']);
        if (!$zone || !$zone->coordinates) {
            return null;
        }

        // Zone::coordinates is cast to MatanYadaev\EloquentSpatial Polygon;
        // getCoordinates() returns rings of [lng, lat] pairs (GeoJSON order).
        $points = [];
        foreach ($zone->coordinates->getCoordinates() as $ring) {
            foreach ($ring as $pair) {
                if (!is_array($pair) || count($pair) < 2) {
                    continue;
                }
                $points[] = [
                    'lat' => (float) $pair[1],
                    'lng' => (float) $pair[0],
                ];
            }
        }

        return $points === [] ? null : $points;
    }

    /**
     * Map a CustomerAddress row into the canonical AddressDTO (the module owns
     * the wire shape; the typed constructor keeps keys stable).
     */
    private function toAddressDTO(CustomerAddress $row): AddressDTO
    {
        return new AddressDTO(
            id: (int) $row->id,
            addressType: $row->address_type !== null ? (string) $row->address_type : null,
            address: (string) $row->address,
            latitude: (float) $row->latitude,
            longitude: (float) $row->longitude,
            contactPersonName: $row->contact_person_name !== null ? (string) $row->contact_person_name : null,
            contactPersonNumber: $row->contact_person_number !== null ? (string) $row->contact_person_number : null,
            floor: $row->floor !== null ? (string) $row->floor : null,
            road: $row->road !== null ? (string) $row->road : null,
            house: $row->house !== null ? (string) $row->house : null,
            zoneId: (int) $row->zone_id,
        );
    }

    private function fillAddress(CustomerAddress $row, array $payload, float $lat, float $lng): void
    {
        if (\array_key_exists('address_type', $payload)) {
            $row->address_type = $payload['address_type'];
        }
        if (\array_key_exists('address', $payload)) {
            $row->address = $payload['address'];
        }
        if (\array_key_exists('contact_person_name', $payload)) {
            $row->contact_person_name = $payload['contact_person_name'];
        }
        if (\array_key_exists('contact_person_number', $payload)) {
            $row->contact_person_number = $payload['contact_person_number'];
        }
        if (\array_key_exists('floor', $payload)) {
            $row->floor = $payload['floor'];
        }
        if (\array_key_exists('road', $payload)) {
            $row->road = $payload['road'];
        }
        if (\array_key_exists('house', $payload)) {
            $row->house = $payload['house'];
        }
        $row->latitude = $lat;
        $row->longitude = $lng;
    }

    /** Load a CustomerAddress that belongs to the current customer, or null. */
    private function ownedAddress(int $addressId): ?CustomerAddress
    {
        $userId = $this->context->getUserId();
        if (!$userId) {
            return null;
        }

        return CustomerAddress::query()
            ->whereKey($addressId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Resolve the zone that contains (lat, lng), or throw ValidationException
     * with the `coordinates` key — mirrors the host's add_new_address /
     * get_zone "service not available in this area" behaviour.
     */
    private function resolveZoneIdOrFail(float $lat, float $lng): int
    {
        // When the storefront is scoped to a specific zone (StorefrontScope
        // regionId), the pick MUST lie inside THAT zone and be stored under it.
        // current() drops any stored selection whose zone_id != the active
        // storefront zone — so storing a *different* overlapping zone here
        // (zoneIdFor()'s ->latest() returns the newest of several zones that
        // contain the point, e.g. a Dhaka point falls in zones 1,2,3) made the
        // selection silently vanish on reload: the "Location updated" toast
        // fired but the topbar never changed. Mirror 6amMart — validate against
        // and persist the storefront's own zone so current() keeps it.
        $storefrontZoneId = $this->currentZoneId();
        if ($storefrontZoneId !== null) {
            if (!$this->pointInZone($lat, $lng, $storefrontZoneId)) {
                throw ValidationException::withMessages([
                    'coordinates' => translate('messages.service_not_available_in_this_area'),
                ]);
            }

            return $storefrontZoneId;
        }

        // Unscoped (no active storefront zone): fall back to whichever active
        // zone contains the point.
        $zoneId = $this->zoneIdFor($lat, $lng);
        if ($zoneId === null) {
            throw ValidationException::withMessages([
                'coordinates' => translate('messages.service_not_available_in_this_area'),
            ]);
        }

        return $zoneId;
    }

    /** True when the point lies inside the given active zone's polygon. */
    private function pointInZone(float $lat, float $lng, int $zoneId): bool
    {
        return Zone::query()
            ->whereContains('coordinates', new Point($lat, $lng, POINT_SRID))
            ->where('id', $zoneId)
            ->where('status', 1)
            ->exists();
    }

    /** ST_Contains the zone polygon, host-API style. Null when outside all zones. */
    private function zoneIdFor(float $lat, float $lng): ?int
    {
        $zone = Zone::query()
            ->whereContains('coordinates', new Point($lat, $lng, POINT_SRID))
            ->latest()
            ->first(['id']);

        return $zone ? (int) $zone->id : null;
    }

    /** The active storefront's zone id from the request scope, if any. */
    private function currentZoneId(): ?int
    {
        return $this->context->getScope()?->regionId;
    }
}
