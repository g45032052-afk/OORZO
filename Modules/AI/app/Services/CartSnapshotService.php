<?php

namespace Modules\AI\app\Services;

use Modules\AI\app\Support\FoodPricing;
use App\Models\BogoOffer;
use App\Models\Cart;
use App\Models\Food;
use App\Models\Restaurant;
use App\Services\Cart\BundleCartPresenter;

/**
 * Builds the AI-chat cart snapshot.
 *
 * `items()` returns the canonical FLAT item list shared by every cart tool
 * (applied unit price with item discount, plus a human variation label).
 * `grouped()` folds that flat list into the multi-store cart shape the chat
 * response exposes under `metadata.cart` — a customer's cart can hold items
 * from several restaurants, each checked out separately, so the UI needs the
 * per-store breakdown + subtotals (mirrors the 6amMart storefront shape).
 */
class CartSnapshotService
{
    /**
     * Flat cart-item list for the given customer / guest.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function items(int|string $cartUserId, bool $isGuest): array
    {
        $carts = Cart::where('user_id', $cartUserId)
            ->where('is_guest', $isGuest)
            ->where('item_type', Food::class)
            // `restaurant.discount` feeds FoodPricing: an active restaurant
            // campaign overrides the item's own discount, exactly as the
            // storefront prices it.
            ->with(['item:id,name,price,discount,discount_type,restaurant_id,image', 'item.restaurant.discount'])
            ->get();

        if ($carts->isEmpty()) {
            return [];
        }

        $restaurantIds = $carts->pluck('restaurant_id')->filter()->unique()->values()->all();
        $restaurantsById = $restaurantIds
            ? Restaurant::whereIn('id', $restaurantIds)->get(['id', 'name'])->keyBy('id')
            : collect();

        // A bundle is one line, never its separate rows. Split here rather than in the
        // loop below so an ordinary cart takes exactly the path it always did, and so the
        // assistant never sees a free row priced 0 that it has no way to explain - or,
        // worse, names one of them as something to remove.
        $bundleRows = $carts->filter(fn ($row) => $row->bogo_group_id);
        $carts = $carts->reject(fn ($row) => $row->bogo_group_id);

        $data = [];
        foreach ($carts as $row) {
            $item = $row->item;
            $qty  = (int) $row->getAttribute('quantity');
            $unit = (float) $row->getAttribute('price');

            if ($item) {
                // Cart rows store the UNDISCOUNTED unit price (core
                // CartController does the same and discounts at checkout).
                $unit = FoodPricing::resolve($item, $unit)['discounted_price'];
            }

            $rid = (int) ($row->getAttribute('restaurant_id') ?? $item?->getAttribute('restaurant_id') ?? 0);

            $data[] = [
                'cart_id'        => (int) $row->getKey(),
                'item_id'        => (int) $row->getAttribute('item_id'),
                'store_id'       => $rid,
                'store_name'     => $restaurantsById->get($rid)?->name ?? ('Restaurant #' . $rid),
                'name'           => $item?->getAttribute('name') ?? 'Unknown item',
                'variation'      => self::variationLabel($row->getAttribute('variations')),
                'image'          => $item?->getAttribute('image'),
                'image_full_url' => $item?->image_full_url,
                'quantity'       => $qty,
                'unit_price'     => $unit,
                'line_total'     => round($unit * $qty, 2),
                // Present on every entry so the two kinds of line share one shape,
                // exactly as /cart/list does. Null means "an ordinary item".
                'bogo_details'   => null,
            ];
        }

        return array_merge($data, self::bundleEntries($bundleRows, $cartUserId, $isGuest, $restaurantsById));
    }

    /**
     * One entry per bundle, carrying the same `bogo_details` block /cart/list returns.
     *
     * Built by the shared BundleCartPresenter so the assistant and the cart screen can
     * never disagree about what a bundle is. The offer-specific figures are taken as they
     * come: a bundle price is frozen when the offer is enrolled in, so re-pricing it here
     * the way an ordinary line is re-priced would quote the customer less than checkout
     * will actually charge whenever a happy hour happens to be running.
     *
     * @param  \Illuminate\Support\Collection $bundleRows Cart rows that carry a bogo_group_id
     * @return array<int, array<string, mixed>>
     */
    private static function bundleEntries($bundleRows, int|string $cartUserId, bool $isGuest, $restaurantsById): array
    {
        if ($bundleRows->isEmpty()) {
            return [];
        }

        $presented = app(BundleCartPresenter::class)
            ->present($bundleRows, $cartUserId, (int) $isGuest);

        // One lookup for every offer in the cart rather than one per bundle: the image is
        // the offer's, and bogo_details carries the offer's identity but not its artwork.
        $offerIds = collect($presented)->pluck('bogo_details.bogo_offer_id')->filter()->unique();
        $offers = $offerIds->isNotEmpty()
            ? BogoOffer::whereIn('id', $offerIds)->get(['id', 'image'])->keyBy('id')
            : collect();

        $entries = [];
        foreach ($presented as $entry) {
            $details = $entry->bogo_details;
            $rid = (int) ($entry->restaurant_id ?? 0);
            $offer = $offers->get($details['bogo_offer_id'] ?? null);

            $entries[] = [
                // No single cart row represents a bundle, and pointing at one would invite
                // a remove-item call the cart then refuses. bogo_group_id addresses it.
                'cart_id'        => null,
                'item_id'        => null,
                'store_id'       => $rid,
                'store_name'     => $restaurantsById->get($rid)?->name ?? ('Restaurant #' . $rid),
                'name'           => $details['offer_title'] ?? 'Offer',
                'variation'      => '',
                'image'          => $offer?->getRawOriginal('image'),
                'image_full_url' => $offer?->image_full_url,
                'quantity'       => (int) ($details['quantity'] ?? 1),
                'unit_price'     => (float) ($details['bundle_price'] ?? 0),
                'line_total'     => (float) ($details['total_price'] ?? 0),
                'bogo_details'   => $details,
            ];
        }

        return $entries;
    }

    /**
     * Fold a flat item list into the per-store cart shape:
     *   ['stores' => [['store_id','store_name','items'=>[…],'store_subtotal'], …],
     *    'grand_total' => float, 'total_items' => int]
     *
     * @param  array<int, array<string, mixed>> $items
     * @return array{stores: array<int, array<string, mixed>>, grand_total: float, total_items: int}
     */
    public static function grouped(array $items): array
    {
        $stores = [];

        foreach ($items as $it) {
            $sid = (int) ($it['store_id'] ?? 0);

            if (! isset($stores[$sid])) {
                $stores[$sid] = [
                    'store_id'       => $sid,
                    'store_name'     => $it['store_name'] ?? ('Restaurant #' . $sid),
                    'items'          => [],
                    'store_subtotal' => 0.0,
                ];
            }

            $stores[$sid]['items'][] = [
                'cart_id'        => $it['cart_id'] ?? null,
                'item_id'        => $it['item_id'] ?? null,
                'name'           => $it['name'] ?? '',
                'variation'      => $it['variation'] ?? '',
                'image'          => $it['image'] ?? null,
                'image_full_url' => $it['image_full_url'] ?? null,
                'quantity'       => (int) ($it['quantity'] ?? 0),
                'unit_price'     => (float) ($it['unit_price'] ?? 0),
                'line_total'     => (float) ($it['line_total'] ?? 0),
                // This list is a whitelist, so a key not named here is dropped on the way
                // out - a bundle would arrive at the client as an anonymous line with no
                // offer behind it.
                'bogo_details'   => $it['bogo_details'] ?? null,
            ];

            $stores[$sid]['store_subtotal'] = round(
                $stores[$sid]['store_subtotal'] + (float) ($it['line_total'] ?? 0),
                2
            );
        }

        $storeList  = array_values($stores);
        $grandTotal = round(array_sum(array_column($storeList, 'store_subtotal')), 2);
        $totalItems = array_sum(array_map(static fn ($s) => count($s['items']), $storeList));

        return [
            'stores'      => $storeList,
            'grand_total' => $grandTotal,
            'total_items' => $totalItems,
        ];
    }

    /**
     * Human label for a StackFood nested variation selection
     * (e.g. [{"name":"Size","values":{"label":["Small"]}}] → "Small").
     */
    private static function variationLabel(mixed $raw): string
    {
        if (empty($raw)) {
            return '';
        }

        $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);
        if (! is_array($decoded)) {
            return '';
        }

        $labels = [];
        foreach ($decoded as $group) {
            foreach ((array) data_get($group, 'values.label', []) as $label) {
                if (is_string($label) && $label !== '') {
                    $labels[] = $label;
                }
            }
        }

        return implode(', ', $labels);
    }
}
