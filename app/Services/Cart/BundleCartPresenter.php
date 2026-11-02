<?php

namespace App\Services\Cart;

use App\Models\BogoOfferRestaurant;
use App\Traits\HandlesBogoCart;
use Illuminate\Support\Collection;

/**
 * How a cart holding BOGO bundles is presented to a customer.
 *
 * Extracted verbatim from CartController so /api/v1/customer/cart/list and the AI
 * chat assistant answer with the same shape. They had to agree and could not have
 * been kept in step by hand: a bundle reading as one line on one surface and as
 * its separate rows on the other is worse than either choice made consistently.
 *
 * The move was proven by qa_c7_cartbundles.php, which recorded the endpoint's
 * response before the extraction and asserts it byte for byte afterwards.
 */
class BundleCartPresenter
{
    use HandlesBogoCart;

    /**
     * Collapse each bundle's rows into a single entry so the cart reads the way a customer
     * sees it: one entry per ordinary item, one per bundle - a bundle of three foods is one
     * line, not three.
     *
     * The entry keeps the ordinary cart shape untouched. A bundle is represented by one of
     * its rows with price and quantity restated as the bundle's, so a client that only knows
     * the old format still totals the cart correctly, and everything specific to the offer
     * hangs off bogo_details. That key is present on every entry and null for ordinary items.
     */
    public function present(Collection $carts, $userId, int $isGuest): array
    {
        $entries = [];

        foreach ($carts as $cart) {
            if ($cart->bogo_group_id) {
                continue;
            }

            $cart->bogo_details = null;
            $entries[] = $cart;
        }

        // How many copies of each offer the whole cart holds. A bundle is judged on that total,
        // not on the group being rendered: an offer added twice is one allowance and one stock
        // to draw on, so both entries have to carry the verdict the checkout will give. Resolved
        // once here rather than per group, which would re-read the cart for every bundle in it.
        $heldPerOffer = collect($this->bogoOffersInCart($userId, $isGuest))
            ->mapWithKeys(fn ($offer) => [
                $offer['bogo_offer_id'].':'.($offer['enrollment']?->restaurant_id ?? 0) => $offer['quantity'],
            ]);

        foreach ($carts->filter(fn ($c) => $c->bogo_group_id)->groupBy('bogo_group_id') as $groupId => $rows) {
            $first = $rows->first();
            $enrollment = BogoOfferRestaurant::where('bogo_offer_id', $first->bogo_offer_id)
                ->where('restaurant_id', $first->restaurant_id)
                ->with(['items', 'restaurant', 'bogo_offer'])
                ->first();

            // Bundle count, derived from a line rather than stored so it cannot drift.
            $quantity = 1;

            if ($enrollment) {
                $reference = $enrollment->items->firstWhere('type', 'buy') ?: $enrollment->items->first();
                $row = $reference ? $rows->firstWhere('item_id', $reference->food_id) : null;

                if ($reference && $row && $reference->quantity > 0) {
                    $quantity = max(1, (int) round($row->quantity / $reference->quantity));
                }
            }

            $held = (int) ($heldPerOffer[$first->bogo_offer_id.':'.$first->restaurant_id] ?? $quantity);

            $reason = $enrollment
                ? $this->bogoUnavailableReason($enrollment, max($quantity, $held), $userId, $isGuest)
                : translate('messages.Offer_not_found');

            $bundlePrice = $enrollment ? (float) $enrollment->bundle_price : 0;
            $digits = config('round_up_to_digit', 2);

            // The same figures the offer screens show, from the same method - a happy hour is
            // the one promotion that reaches a bundle, and the cart was the only surface that
            // never said whether one was running.
            $pricing = $enrollment ? $this->bogoBundlePricing($enrollment) : null;

            // A bundle is not one food, so everything item-specific is nulled - leaving a
            // real item_id here would describe the bundle as whichever line happened to be
            // first, and would invite a client to call remove-item with that row's id, which
            // the guard then refuses. The keys stay present so the shape never changes.
            // Cloned because the row also appears inside bogo_details.buy_items, and pointing
            // the entry at that object would make the payload reference itself.
            $entry = clone ($rows->firstWhere('is_free_item', 0) ?: $first);

            foreach (['id', 'item_id', 'item_type', 'reel_id', 'variations',
                      'variation_options', 'add_on_ids', 'add_on_qtys', 'is_free_item'] as $itemField) {
                $entry->{$itemField} = null;
            }

            // item is a relation, so nulling the attribute would not survive serialisation -
            // the loaded relation wins. Replace the relation itself.
            $entry->setRelation('item', null);

            // What the bundle costs, so sum(price x quantity) still totals the cart.
            $entry->price = round($bundlePrice, $digits);
            $entry->quantity = $quantity;
            $entry->bogo_details = [
                'bogo_group_id' => $groupId,
                'bogo_offer_id' => $first->bogo_offer_id,
                'offer_title' => $enrollment?->bogo_offer?->title,
                'offer_slug' => $enrollment?->bogo_offer?->slug,
                // The window the offer runs in, so the app can check a chosen delivery slot
                // against it before checkout. Placement applies the same rule and refuses a
                // scheduled order the offer would have expired before; sending the dates here
                // lets the customer be told while they are still picking a time.
                'offer_start_date' => $enrollment?->bogo_offer?->start_date?->format('Y-m-d H:i:s'),
                'offer_end_date' => $enrollment?->bogo_offer?->end_date?->format('Y-m-d H:i:s'),
                'bundle_id' => $enrollment?->id,
                'quantity' => $quantity,
                'bundle_price' => round($bundlePrice, $digits),
                'total_price' => round($bundlePrice * $quantity, $digits),
                // What the bundle would have cost item by item, so the saving can be shown.
                'original_price' => $pricing['original_price'] ?? round($bundlePrice, $digits),
                // Per bundle, matching bundle_price; the total_ pair is the whole group.
                'discount_percentage' => $pricing['discount_percentage'] ?? 0,
                'discount_amount' => $pricing['discount_amount'] ?? 0,
                'final_price' => $pricing['final_price'] ?? round($bundlePrice, $digits),
                'total_discount_amount' => round(($pricing['discount_amount'] ?? 0) * $quantity, $digits),
                'total_final_price' => round(($pricing['final_price'] ?? $bundlePrice) * $quantity, $digits),
                'is_happy_hour' => (bool) ($pricing['is_happy_hour'] ?? false),
                'happy_hour' => $pricing['happy_hour'] ?? null,
                'is_available' => $reason === null,
                'unavailable_reason' => $reason,
                'buy_items' => $rows->where('is_free_item', 0)->values()->all(),
                'free_items' => $rows->where('is_free_item', 1)->values()->all(),
            ];

            $entries[] = $entry;
        }

        return $entries;
    }
}
