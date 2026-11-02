<?php

namespace App\Builder;

use App\CentralLogics\Helpers;
use App\Models\Cart;
use App\Models\Food;
use App\Models\ItemCampaign;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Modules\Builder\Contracts\CartProvider as CartProviderContract;
use Modules\Builder\Services\StorefrontContext;

/**
 * StackFood server-side cart adapter.
 *
 * Rebuilt around StackFood's cart schema/logic (NOT a 6amMart rename):
 *   - StackFood's `carts` table keys on `user_id` + `is_guest` + `restaurant_id`
 *     and morphs `item` to `Food` / `ItemCampaign`. There is no `module_id`
 *     (StackFood is single-module food), so the cart is scoped by the active
 *     restaurant (= StorefrontScope sub-tenant) instead.
 *   - The variation choice is stored in the `variations` column (raw JSON, NOT
 *     array-cast). The newer `variation_options` stock system isn't carried by
 *     the module's cart contract, so it's written as `[]` (see NOTE below).
 *   - `reel_id` is resolved via Helpers::resolve_reel_id like the host API.
 *
 * Host logic preserved faithfully:
 *   - maximum_cart_quantity guard
 *   - Helpers::addonAndVariationStockCheck() for Food add-on stock
 *   - Helpers::cart_product_data_formatting() for each row's display payload
 *   - restaurant_id resolved from the item (Helpers/API convention)
 *
 * Module wire alias: the storefront sends model='Item' for a food item; we map
 * 'Item' -> Food (StackFood's food model), 'ItemCampaign' -> ItemCampaign.
 *
 * NOTE (variation_options): StackFood's food variation stock lives in
 * `variation_options`, which the module's cart contract (`variation`/`add_on_*`)
 * does not transmit. We persist `variation_options = []`; full variation-stock
 * enforcement would need the module's ItemProvider/foodDetails to emit the new
 * shape — not done here (no module changes allowed). Add-on stock IS checked.
 */
class CartProvider implements CartProviderContract
{
    private const MODEL_MAP = [
        'Item'         => Food::class,
        'ItemCampaign' => ItemCampaign::class,
    ];

    public function __construct(private StorefrontContext $context) {}

    public function list(): array
    {
        $this->healMissingRestaurantId();
        $rows = $this->baseQuery()->get()->map(fn ($row) => $this->formatRow($row))->values()->all();
        return $this->withTotals($rows);
    }

    /**
     * Backfill `restaurant_id` on this shopper's rows that predate the column
     * being persisted (it was added nullable — see migration
     * 2026_05_03_000001_add_reel_id_&_restaurant_id_to_carts_table) or were
     * reassigned to the user by the guest-cart merge without a restaurant. The
     * host CheckoutProvider and CouponProvider filter the cart by
     * `restaurant_id`, so a NULL there makes them read an empty cart and
     * silently drop those rows (wrong/zero tax, coupons miss items). We only
     * touch rows whose morphed item actually belongs to the active restaurant,
     * so a multi-restaurant shopper never gets another restaurant's rows
     * reassigned. Idempotent (only NULL rows) and a cheap no-op once healed.
     */
    private function healMissingRestaurantId(): void
    {
        [$shopperId, $isGuest, $restaurantId] = $this->shopperContext();
        if ($shopperId === null || $restaurantId === null) {
            return;
        }

        $ids = Cart::query()
            ->where('user_id', $shopperId)
            ->where('is_guest', $isGuest)
            ->whereNull('restaurant_id')
            ->whereHasMorph(
                'item',
                [Food::class, ItemCampaign::class],
                fn ($q) => $q->where('restaurant_id', $restaurantId),
            )
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            Cart::query()->whereIn('id', $ids)->update(['restaurant_id' => (int) $restaurantId]);
        }
    }

    public function add(array $payload): array
    {
        [$shopperId, $isGuest, $restaurantId] = $this->shopperContext();
        $modelClass = $this->resolveModel($payload['model']);
        $itemId     = (int) $payload['item_id'];
        $item       = $modelClass::find($itemId);

        if (!$item) {
            throw ValidationException::withMessages(['item_id' => translate('Item_not_found') ?: 'Item not found']);
        }
        if ((int) ($item->restaurant_id ?? 0) !== (int) $restaurantId) {
            throw ValidationException::withMessages(['item_id' => __('Item does not belong to this store')]);
        }

        $variation = $this->arrayPayload($payload['variation'] ?? []);
        $addOnIds  = \array_values($this->arrayPayload($payload['add_on_ids'] ?? []));
        $addOnQtys = \array_values($this->arrayPayload($payload['add_on_qtys'] ?? []));
        $qtyDelta  = \max(1, (int) ($payload['quantity'] ?? 1));
        $priceDelta = (float) ($payload['price'] ?? 0);

        $existing = $this->findMatchingLine($shopperId, $isGuest, $itemId, $modelClass, $variation, $addOnIds, $addOnQtys);

        if ($existing) {
            $newQty = (int) $existing->quantity + $qtyDelta;
            $this->assertQtyAllowed($item, $newQty);
            $this->assertAddonStock($payload['model'], $item, $newQty, $addOnIds, $addOnQtys);
            $existing->quantity = $newQty;
            $existing->price    = (float) $existing->price + $priceDelta;
            $existing->save();
        } else {
            $this->assertQtyAllowed($item, $qtyDelta);
            $this->assertAddonStock($payload['model'], $item, $qtyDelta, $addOnIds, $addOnQtys);

            $cart = new Cart;
            $cart->user_id       = $shopperId;
            $cart->is_guest      = $isGuest;
            $cart->restaurant_id = (int) ($item->restaurant_id ?? $restaurantId);
            $cart->item_id       = $itemId;
            $cart->item_type     = $modelClass;
            $cart->reel_id       = Helpers::resolve_reel_id(null, $itemId);
            $cart->quantity      = $qtyDelta;
            $cart->price         = $priceDelta;
            // Storage shape mirrors App\Http\Controllers\Api\V1\CartController:
            // variations/variation_options/add_on_* are json_encode()d on write.
            $cart->variations         = \json_encode($variation);
            $cart->variation_options  = \json_encode([]);
            $cart->add_on_ids         = \json_encode($addOnIds);
            $cart->add_on_qtys        = \json_encode($addOnQtys);
            $cart->save();
            $item->carts()->save($cart);
        }

        return $this->list();
    }

    public function update(int $cartId, array $payload): array
    {
        [$shopperId, $isGuest, $restaurantId] = $this->shopperContext();
        $cart = $this->ownedRow($cartId, $shopperId, $isGuest, $restaurantId);

        $modelClass = $this->normalizeStoredItemType($cart->item_type);
        $item       = $modelClass::find($cart->item_id);
        if (!$item) {
            throw ValidationException::withMessages(['cart_id' => translate('Item_not_found') ?: 'Cart item not found']);
        }

        $variation = \array_key_exists('variation', $payload)
            ? $this->arrayPayload($payload['variation'] ?? [])
            : $this->decodeJson($cart->variations);
        $addOnIds = \array_key_exists('add_on_ids', $payload)
            ? \array_values($this->arrayPayload($payload['add_on_ids'] ?? []))
            : \array_values($this->decodeJson($cart->add_on_ids));
        $addOnQtys = \array_key_exists('add_on_qtys', $payload)
            ? \array_values($this->arrayPayload($payload['add_on_qtys'] ?? []))
            : \array_values($this->decodeJson($cart->add_on_qtys));

        $newQty = \max(1, (int) ($payload['quantity'] ?? $cart->quantity));
        $this->assertQtyAllowed($item, $newQty);
        $this->assertAddonStock(
            $cart->item_type === ItemCampaign::class ? 'ItemCampaign' : 'Food',
            $item, $newQty, $addOnIds, $addOnQtys
        );

        $cart->quantity          = $newQty;
        $cart->variations        = \json_encode($variation);
        $cart->add_on_ids        = \json_encode($addOnIds);
        $cart->add_on_qtys       = \json_encode($addOnQtys);
        if (\array_key_exists('price', $payload)) {
            $cart->price = (float) $payload['price'];
        }
        $cart->save();

        return $this->list();
    }

    public function remove(int $cartId): array
    {
        [$shopperId, $isGuest, $restaurantId] = $this->shopperContext();
        $this->ownedRow($cartId, $shopperId, $isGuest, $restaurantId)->delete();
        return $this->list();
    }

    public function clear(): array
    {
        $this->baseQuery()->get()->each->delete();
        return $this->list();
    }

    public function count(): int
    {
        return $this->baseQuery()->count();
    }

    /* ─── query helpers ───────────────────────────────────── */

    private function baseQuery()
    {
        [$shopperId, $isGuest, $restaurantId] = $this->shopperContext();

        if ($shopperId === null || $restaurantId === null) {
            return Cart::query()->whereRaw('1 = 0');
        }

        return Cart::query()
            ->where('user_id', $shopperId)
            ->where('is_guest', $isGuest)
            ->whereHasMorph(
                'item',
                [Food::class, ItemCampaign::class],
                fn ($q) => $q->where('restaurant_id', $restaurantId),
            );
    }

    private function ownedRow(int $cartId, int $shopperId, int $isGuest, int $restaurantId): Cart
    {
        $cart = Cart::query()
            ->where('id', $cartId)
            ->where('user_id', $shopperId)
            ->where('is_guest', $isGuest)
            ->whereHasMorph(
                'item',
                [Food::class, ItemCampaign::class],
                fn ($q) => $q->where('restaurant_id', $restaurantId),
            )
            ->first();

        if (!$cart) {
            throw ValidationException::withMessages(['cart_id' => translate('messages.cart_not_found') ?: 'Cart item not found']);
        }
        return $cart;
    }

    private function findMatchingLine(int $shopperId, int $isGuest, int $itemId, string $modelClass, array $variation, array $addOnIds, array $addOnQtys): ?Cart
    {
        $needle = $this->variationMatchKey($variation);

        return Cart::query()
            ->where('user_id', $shopperId)
            ->where('is_guest', $isGuest)
            ->where('item_id', $itemId)
            ->whereIn('item_type', $this->itemTypeAliases($modelClass))
            ->get()
            ->first(function ($row) use ($needle, $addOnIds, $addOnQtys) {
                return $this->variationMatchKey($this->decodeJson($row->variations)) === $needle
                    && $this->decodeJson($row->add_on_ids)  == $addOnIds
                    && $this->decodeJson($row->add_on_qtys) == $addOnQtys;
            });
    }

    /** Stable variation identity ignoring volatile price/stock fields. */
    private function variationMatchKey(array $variation): string
    {
        $normalized = \array_map(static function ($entry) {
            if (!\is_array($entry)) return $entry;
            $copy = $entry;
            unset($copy['price'], $copy['stock'], $copy['oldPrice'], $copy['discountPercent'], $copy['inStock']);
            return $copy;
        }, $variation);

        return \json_encode($normalized) ?: '';
    }

    /* ─── formatting ──────────────────────────────────────── */

    private function formatRow(Cart $row): array
    {
        $variation = $this->decodeJson($row->variations);
        $addOnIds  = $this->decodeJson($row->add_on_ids);
        $addOnQtys = $this->decodeJson($row->add_on_qtys);

        $itemModel = $row->item;
        $formatted = $itemModel
            ? Helpers::cart_product_data_formatting($itemModel, $variation, $addOnIds, $addOnQtys, false, \app()->getLocale())
            : null;

        // StackFood tracks stock per food row, not per module: `stock_type` is
        // `unlimited` (untracked) or `limited`/`daily` (tracked), and the
        // `item_stock` accessor already returns what is left
        // (raw item_stock − sell_count). Same rule the host cart add enforces
        // via Helpers::addonAndVariationStockCheck().
        $tracksStock = $itemModel
            && $itemModel->stock_type !== null
            && $itemModel->stock_type !== 'unlimited';

        return [
            'id'          => $row->id,
            'item_id'     => $row->item_id,
            'item_type'   => $row->item_type === ItemCampaign::class ? 'ItemCampaign' : 'Item',
            'price'       => (float) $row->price,
            'quantity'    => (int) $row->quantity,
            'variation'   => $variation,
            'add_on_ids'  => $addOnIds,
            'add_on_qtys' => $addOnQtys,
            'item'        => $formatted,
            // Inventory cap for the drawer's +/- stepper. Item-level only:
            // per-variation-option stock lives on VariationOption and the cart
            // row does not carry option ids (see assertAddonStock).
            'tracksStock' => $tracksStock,
            'stock'       => $tracksStock ? (int) $itemModel->item_stock : 0,
        ];
    }

    private function withTotals(array $rows): array
    {
        $count    = \count($rows);
        $quantity = 0;
        $subtotal = 0.0;
        foreach ($rows as $r) {
            $quantity += (int) $r['quantity'];
            $subtotal += (float) $r['price'];
        }
        return [
            'items'  => $rows,
            'totals' => [
                'count'    => $count,
                'quantity' => $quantity,
                'subtotal' => \round($subtotal, $this->context->getDigitAfterDecimalPoint()),
            ],
        ];
    }

    /* ─── small helpers ───────────────────────────────────── */

    private function shopperContext(): array
    {
        return [
            $this->context->getShopperId(),
            $this->context->shopperIsGuestFlag(),
            $this->context->getStoreId(),
        ];
    }

    private function resolveModel(string $alias): string
    {
        if (!isset(self::MODEL_MAP[$alias])) {
            throw ValidationException::withMessages(['model' => __('Invalid item model')]);
        }
        return self::MODEL_MAP[$alias];
    }

    private function normalizeStoredItemType(?string $stored): string
    {
        if ($stored === ItemCampaign::class || $stored === 'ItemCampaign') {
            return ItemCampaign::class;
        }
        return Food::class;
    }

    private function itemTypeAliases(string $modelClass): array
    {
        return $modelClass === ItemCampaign::class
            ? [ItemCampaign::class, 'ItemCampaign']
            : [Food::class, 'Food'];
    }

    private function assertQtyAllowed(Model $item, int $qty): void
    {
        $max = (int) ($item->maximum_cart_quantity ?? 0);
        if ($max > 0 && $qty > $max) {
            throw ValidationException::withMessages([
                'quantity' => translate('messages.maximum_cart_quantity_exceeded') ?: 'Maximum cart quantity exceeded',
            ]);
        }
    }

    /**
     * Add-on stock guard, mirroring the host API's Food path. variation_options
     * is empty (not carried by the module contract), so this enforces add-on
     * stock; variation stock would need the new variation_options shape.
     */
    private function assertAddonStock(string $modelAlias, Model $item, int $qty, array $addOnIds, array $addOnQtys): void
    {
        if ($modelAlias !== 'Food') {
            return;
        }
        try {
            $check = Helpers::addonAndVariationStockCheck(
                product: $item,
                quantity: $qty,
                add_on_qtys: $addOnQtys,
                variation_options: [],
                add_on_ids: $addOnIds,
            );
            $outOfStock = \data_get($check, 'out_of_stock');
            if ($outOfStock != null) {
                throw ValidationException::withMessages(['quantity' => $outOfStock]);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable) {
            // Stock helper signature drift shouldn't hard-fail an add — skip.
        }
    }

    private function arrayPayload($value): array
    {
        return \is_array($value) ? $value : [];
    }

    private function decodeJson($value): array
    {
        if (\is_array($value)) {
            return $value;
        }
        if (\is_string($value) && $value !== '') {
            $decoded = \json_decode($value, true);
            return \is_array($decoded) ? $decoded : [];
        }
        return [];
    }
}
