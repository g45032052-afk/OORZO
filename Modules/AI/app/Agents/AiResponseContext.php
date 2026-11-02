<?php

namespace Modules\AI\app\Agents;

/**
 * Request-scoped context that tools write structured data into. The service
 * reads it after the agent prompt completes to include food/restaurant cards
 * in the API response and to log which tools ran.
 */
class AiResponseContext
{
    private array $products          = [];
    private array $stores            = [];
    private array $categories        = [];
    private array $cuisines          = [];
    private array $bogoOffers        = [];
    private array $happyHours        = [];
    private array $cartItems         = [];
    private array $toolsInvoked      = [];
    private array $excludedProductIds = [];
    private array $pendingVariation  = [];

    public function addProducts(array $products): void
    {
        $this->products = $this->mergeUnique($this->products, $products);
    }

    public function addStores(array $stores): void
    {
        $this->stores = $this->mergeUnique($this->stores, $stores);
    }

    /**
     * Merge cards, dropping repeats. Several tools can run in one turn and
     * return the same item (a veg search plus a popular lookup, say) — plain
     * array_merge renders it twice in the carousel. Keyed by type+id so a food
     * and a campaign sharing an ID stay distinct; first occurrence wins, which
     * keeps the earliest tool's ordering.
     *
     * @param  array<int, array<string, mixed>> $existing
     * @param  array<int, array<string, mixed>> $incoming
     * @return array<int, array<string, mixed>>
     */
    private function mergeUnique(array $existing, array $incoming): array
    {
        $seen = [];
        foreach ($existing as $card) {
            $seen[($card['item_type'] ?? 'item') . ':' . ($card['id'] ?? '')] = true;
        }

        foreach ($incoming as $card) {
            $key = ($card['item_type'] ?? 'item') . ':' . ($card['id'] ?? '');
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key]  = true;
            $existing[] = $card;
        }

        return $existing;
    }

    public function addCategories(array $categories): void
    {
        $this->categories = array_merge($this->categories, $categories);
    }

    /**
     * BOGO offer cards.
     *
     * Stamped with item_type so mergeUnique() cannot confuse one with a food: it keys on
     * type plus id, and an offer #5 alongside a food #5 would otherwise collapse into a
     * single card and one of them would silently vanish from the carousel.
     */
    public function addBogoOffers(array $offers): void
    {
        $this->bogoOffers = $this->mergeUnique($this->bogoOffers, array_map(
            fn (array $card) => $card + ['item_type' => 'bogo_offer'],
            $offers
        ));
    }

    public function getBogoOffers(): array
    {
        return $this->bogoOffers;
    }

    /** Happy hour cards, stamped for the same reason BOGO cards are. */
    public function addHappyHours(array $happyHours): void
    {
        $this->happyHours = $this->mergeUnique($this->happyHours, array_map(
            fn (array $card) => $card + ['item_type' => 'happy_hour'],
            $happyHours
        ));
    }

    public function getHappyHours(): array
    {
        return $this->happyHours;
    }

    public function addCuisines(array $cuisines): void
    {
        $this->cuisines = array_merge($this->cuisines, $cuisines);
    }

    public function addCartItems(array $items): void
    {
        $this->cartItems = $items;
    }

    /**
     * Suppress an item from the recommended-products carousel. Used by the
     * add-to-cart flow: the item the customer is actively adding (or picking a
     * variation for) shouldn't be recommended back to them. Order-independent —
     * getProducts() filters these out however/whenever they were added.
     */
    public function excludeProduct(int $itemId): void
    {
        if ($itemId > 0 && ! in_array($itemId, $this->excludedProductIds, true)) {
            $this->excludedProductIds[] = $itemId;
        }
    }

    /**
     * Remember the item we just asked the customer to choose an option for.
     * Persisted into the assistant turn's metadata so the NEXT turn can tell
     * that a terse reply ("small", "the large one") is answering THIS prompt —
     * and snap the choice back to this item if the model attaches it to the
     * wrong one. Shape: ['item_id' => int, 'name' => string, 'options' => string[]].
     */
    public function setPendingVariation(array $pending): void
    {
        $this->pendingVariation = $pending;
    }

    public function getPendingVariation(): array
    {
        return $this->pendingVariation;
    }

    public function recordTool(string $toolName): void
    {
        if (! in_array($toolName, $this->toolsInvoked, true)) {
            $this->toolsInvoked[] = $toolName;
        }
    }

    public function getProducts(): array
    {
        if (empty($this->excludedProductIds)) {
            return $this->products;
        }

        return array_values(array_filter(
            $this->products,
            fn ($p) => ! in_array((int) ($p['id'] ?? 0), $this->excludedProductIds, true)
        ));
    }

    public function getStores(): array
    {
        return $this->stores;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getCuisines(): array
    {
        return $this->cuisines;
    }

    public function getCartItems(): array
    {
        return $this->cartItems;
    }

    public function getToolsInvoked(): array
    {
        return $this->toolsInvoked;
    }

    public function reset(): void
    {
        $this->products           = [];
        $this->stores             = [];
        $this->categories         = [];
        $this->cuisines           = [];
        $this->bogoOffers         = [];
        $this->happyHours         = [];
        $this->cartItems          = [];
        $this->toolsInvoked       = [];
        $this->excludedProductIds = [];
        $this->pendingVariation   = [];
    }
}
