<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BogoOfferRestaurant;
use App\Models\Cart;
use App\Models\Food;
use App\Models\ItemCampaign;
use App\Models\Restaurant;
use App\Services\Cart\BundleCartPresenter;
use App\Traits\HandlesBogoCart;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CartController extends Controller
{
    use HandlesBogoCart;

    public function get_carts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $request->user ? 'nullable' : 'required',
            'restaurant_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        [$userId, $isGuest] = $this->resolveCartOwner($request);
        $restaurantId = $this->resolveRestaurantIdFromInput($request->restaurant_id);

        return response()->json($this->getFormattedCartResponse(
            userId: $userId,
            isGuest: $isGuest,
            restaurantId: $restaurantId
        ), 200);
    }

    public function add_to_cart(Request $request)
    {
        // A bundle is added through this same endpoint - it is addressed by offer_id rather
        // than item_id, because the items it writes come from the enrolment, not the caller.
        if ($request->filled('bogo_id')) {
            return $this->addBogoBundle($request);
        }

        $validator = Validator::make($request->all(), [
            'guest_id' => $request->user ? 'nullable' : 'required',
            'item_id' => 'required|integer',
            'model' => 'required|string|in:Food,ItemCampaign',
            'price' => 'required|numeric',
            'variation_options' => 'nullable|array',
            'quantity' => 'required|integer|min:1',
            'reel_id' => 'nullable|integer',
            'restaurant_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        [$userId, $isGuest] = $this->resolveCartOwner($request);
        $restaurantId = $this->resolveRestaurantIdFromInput($request->restaurant_id);
        $model = $this->resolveItemType($request->model);
        $item = $this->resolveItem($request->model, $request->item_id);

        // variations is optional on this endpoint, and json_encode(null) writes the string
        // "null" rather than an empty selection. Reading that back gave null where an array
        // was expected and 500'd the whole cart listing. Normalised once here so the lookup
        // below and the save further down agree on what "no variations" looks like - they
        // have to match exactly or an add that should merge writes a second row instead.
        $variations = $request->variations ?? [];

        $cart = Cart::where('item_id', $request->item_id)
            ->where('item_type', $model)
            ->where('variations', json_encode($variations))
            ->where('user_id', $userId)
            ->where('is_guest', $isGuest)
            // Never merge an ordinary add into a bundle line - the same food at the same
            // variations can legitimately be both inside a bundle and bought separately.
            ->whereNull('bogo_group_id')
            ->first();

        if (!$item) {
            return response()->json([
                'errors' => [
                    ['code' => 'cart_item', 'message' => translate('Item_not_found')],
                ],
            ], 403);
        }

        if ($cart) {
            return response()->json([
                'errors' => [
                    ['code' => 'cart_item', 'message' => translate('messages.Item_already_exists')],
                ],
            ], 403);
        }

        if ($item?->maximum_cart_quantity && ($request->quantity > $item->maximum_cart_quantity)) {
            return response()->json([
                'errors' => [
                    ['code' => 'cart_item_limit', 'message' => translate('messages.maximum_cart_quantity_exceeded')],
                ],
            ], 403);
        }

        if ($request->model === 'Food') {
            $addonAndVariationStock = Helpers::addonAndVariationStockCheck(
                product: $item,
                quantity: $request->quantity,
                add_on_qtys: $request->add_on_qtys,
                variation_options: $request?->variation_options,
                add_on_ids: $request->add_on_ids
            );

            if (data_get($addonAndVariationStock, 'out_of_stock') != null) {
                return response()->json([
                    'errors' => [
                        ['code' => 'stock_out', 'message' => data_get($addonAndVariationStock, 'out_of_stock')],
                    ],
                ], 403);
            }
        }

        if ($item?->restaurant?->zone_id && $request->hasHeader('zoneId') && !in_array($item->restaurant->zone_id, json_decode($request->header('zoneId'), true) ?? [])) {
            return response()->json([
                'errors' => [
                    ['code' => 'out_of_zone', 'message' => translate('This restaurant is not available in your area')],
                ],
            ], 403);
        }

        $cart = new Cart();
        $cart->user_id = $userId;
        $cart->restaurant_id = $this->resolveRestaurantId($item);
        $cart->item_id = $request->item_id;
        $cart->is_guest = $isGuest;
        $cart->add_on_ids = json_encode($request->add_on_ids);
        $cart->add_on_qtys = json_encode($request->add_on_qtys);
        $cart->item_type = $model;
        $cart->reel_id = Helpers::resolve_reel_id($request->reel_id, $request->item_id);
        $cart->price = $request->price;
        $cart->quantity = $request->quantity;
        $cart->variations = json_encode($variations);
        $cart->variation_options = json_encode($request?->variation_options ?? []);
        $cart->save();

        $item->carts()->save($cart);

        // Personalization: adding a food to the cart is a strong intent signal (logged-in users only; campaign items are not Food rows).
        if (!$isGuest && $request->model === 'Food') {
            \App\CentralLogics\PersonalizationService::recordItemAction((int) $userId, (int) $request->item_id, 'cart');
        }

        return response()->json($this->getFormattedCartResponse(
            userId: $userId,
            isGuest: $isGuest,
            restaurantId: $restaurantId
        ), 200);
    }

    public function update_cart(Request $request)
    {
        if ($request->filled('bogo_group_id')) {
            return $this->updateBogoBundle($request);
        }

        $validator = Validator::make($request->all(), [
            'cart_id' => 'required',
            'guest_id' => $request->user ? 'nullable' : 'required',
            'price' => 'required|numeric',
            'quantity' => 'required|integer|min:1',
            'restaurant_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        [$userId, $isGuest] = $this->resolveCartOwner($request);
        $restaurantId = $this->resolveRestaurantIdFromInput($request->restaurant_id);
        $cart = Cart::find($request->cart_id);

        if (!$cart || (int) $cart->user_id !== (int) $userId || (int) $cart->is_guest !== (int) $isGuest) {
            return response()->json([
                'errors' => [
                    ['code' => 'cart', 'message' => translate('messages.cart_not_found')],
                ],
            ], 404);
        }

        // A bundle is atomic: it only changes as a whole, through the bogo verbs. Letting an
        // ordinary update or remove touch one line would leave a group that no longer matches
        // any enrolment.
        if ($cart->bogo_group_id) {
            return response()->json([
                'errors' => [
                    ['code' => 'bogo_offer', 'message' => translate('messages.Use_the_offer_actions_to_change_this_bundle')],
                ],
            ], 403);
        }

        $item = $cart->item_type === Food::class ? Food::find($cart->item_id) : ItemCampaign::find($cart->item_id);

        if (!$item) {
            return response()->json([
                'errors' => [
                    ['code' => 'cart_item', 'message' => translate('Item_not_found')],
                ],
            ], 404);
        }

        if ($item->maximum_cart_quantity && ($request->quantity > $item->maximum_cart_quantity)) {
            return response()->json([
                'errors' => [
                    ['code' => 'cart_item_limit', 'message' => translate('messages.maximum_cart_quantity_exceeded')],
                ],
            ], 403);
        }

        if ($cart->item_type === Food::class) {
            $addonAndVariationStock = Helpers::addonAndVariationStockCheck(
                product: $item,
                quantity: $request->quantity,
                add_on_qtys: $request->add_on_qtys,
                variation_options: $request?->variation_options,
                add_on_ids: $request->add_on_ids
            );

            if (data_get($addonAndVariationStock, 'out_of_stock') != null) {
                return response()->json([
                    'errors' => [
                        ['code' => 'stock_out', 'message' => data_get($addonAndVariationStock, 'out_of_stock')],
                    ],
                ], 403);
            }
        }

        $cart->user_id = $userId;
        $cart->is_guest = $isGuest;
        $cart->restaurant_id = $this->resolveRestaurantId($item);
        $cart->add_on_ids = $request->add_on_ids ? json_encode($request->add_on_ids) : $cart->add_on_ids;
        $cart->add_on_qtys = $request->add_on_qtys ? json_encode($request->add_on_qtys) : $cart->add_on_qtys;
        $cart->price = $request->price;
        $cart->quantity = $request->quantity;
        $cart->variations = $request->variations ? json_encode($request->variations) : $cart->variations;
        $cart->variation_options = json_encode($request?->variation_options ?? []);
        $cart->save();

        return response()->json($this->getFormattedCartResponse(
            userId: $userId,
            isGuest: $isGuest,
            restaurantId: $restaurantId
        ), 200);
    }

    public function remove_cart_item(Request $request)
    {
        if ($request->filled('bogo_group_id')) {
            return $this->removeBogoBundle($request);
        }

        $validator = Validator::make($request->all(), [
            'cart_id' => 'required',
            'guest_id' => $request->user ? 'nullable' : 'required',
            'restaurant_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        [$userId, $isGuest] = $this->resolveCartOwner($request);
        $restaurantId = $this->resolveRestaurantIdFromInput($request->restaurant_id);
        $cart = Cart::find($request->cart_id);

        if (!$cart || (int) $cart->user_id !== (int) $userId || (int) $cart->is_guest !== (int) $isGuest) {
            return response()->json([
                'errors' => [
                    ['code' => 'cart', 'message' => translate('messages.cart_not_found')],
                ],
            ], 404);
        }

        // A bundle is atomic: it only changes as a whole, through the bogo verbs. Letting an
        // ordinary update or remove touch one line would leave a group that no longer matches
        // any enrolment.
        if ($cart->bogo_group_id) {
            return response()->json([
                'errors' => [
                    ['code' => 'bogo_offer', 'message' => translate('messages.Use_the_offer_actions_to_change_this_bundle')],
                ],
            ], 403);
        }

        $cart->delete();

        return response()->json($this->getFormattedCartResponse(
            userId: $userId,
            isGuest: $isGuest,
            restaurantId: $restaurantId
        ), 200);
    }

    public function remove_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        [$userId, $isGuest] = $this->resolveCartOwner($request);
        $restaurantId = $this->resolveRestaurantIdFromInput($request->restaurant_id);

        Cart::where('user_id', $userId)->where('is_guest', $isGuest)
                ->when($restaurantId !== null, function ($query) use ($restaurantId) {
                    $query->whereHas('item', function ($q) use ($restaurantId) {
                        $q->where('restaurant_id', $restaurantId);
                    });
                })
            ->delete();

        return response()->json($this->getFormattedCartResponse(
            userId: $userId,
            isGuest: $isGuest,
            restaurantId: $restaurantId
        ), 200);
    }

    public function add_to_cart_multiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_list' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $userId = $request->user ? $request->user->id : $request['guest_id'];

        // Validate every item first. If any item is missing/unavailable/out of stock,
        // return a proper error and add NOTHING to the cart (all-or-nothing reorder).
        $validatedItems = [];
        foreach ($request->item_list as $singleItem) {
            $model = $this->resolveItemType($singleItem['model']);
            $item = $this->resolveItem($singleItem['model'], $singleItem['item_id']);

            if (!$item) {
                return response()->json([
                    'errors' => [
                        ['code' => 'cart_item', 'message' => translate('Item_not_found')],
                    ],
                ], 403);
            }
            if (!$this->isItemAvailable($singleItem['model'], $singleItem['item_id'])) {
                return response()->json([
                    'errors' => [
                        ['code' => 'unavailable', 'message' => translate('messages.item_currently_unavailable', ['item' => $item->name])],
                    ],
                ], 403);
            }

            // Same normalisation as the single add: "no variations" has to encode to the
            // same string in the lookup and in the save, or the row never matches itself.
            $singleVariations = $singleItem['variations'] ?? [];

            $cart = Cart::where('item_id', $singleItem['item_id'])
                ->where('item_type', $model)
                ->where('variations', json_encode($singleVariations))
                ->where('user_id', $userId)
                ->where('is_guest', 0)
                ->first();

            if ($cart) {
                return response()->json([
                    'errors' => [
                        ['code' => 'cart_item', 'message' => translate('messages.Item_already_exists')],
                    ],
                ], 403);
            }

            if ($item->maximum_cart_quantity && ($singleItem['quantity'] > $item->maximum_cart_quantity)) {
                return response()->json([
                    'errors' => [
                        ['code' => 'cart_item_limit', 'message' => translate('messages.maximum_cart_quantity_exceeded')],
                    ],
                ], 403);
            }

            if ($singleItem['model'] === 'Food') {
                $addonAndVariationStock = Helpers::addonAndVariationStockCheck(
                    product: $item,
                    quantity: $singleItem['quantity'],
                    add_on_qtys: $singleItem['add_on_qtys'],
                    variation_options: $singleItem['variation_options'],
                    add_on_ids: $singleItem['add_on_ids']
                );

                if (data_get($addonAndVariationStock, 'out_of_stock') != null) {
                    return response()->json([
                        'errors' => [
                            ['code' => 'stock_out', 'message' => data_get($addonAndVariationStock, 'out_of_stock')],
                        ],
                    ], 403);
                }
            }

            $validatedItems[] = ['singleItem' => $singleItem, 'model' => $model, 'item' => $item];
        }

        foreach ($validatedItems as $entry) {
            $singleItem = $entry['singleItem'];
            $model = $entry['model'];
            $item = $entry['item'];

            $cart = new Cart();
            $cart->user_id = $userId;
            $cart->restaurant_id = $this->resolveRestaurantId($item);
            $cart->item_id = $singleItem['item_id'];
            $cart->is_guest = 0;
            $cart->add_on_ids = json_encode($singleItem['add_on_ids']);
            $cart->add_on_qtys = json_encode($singleItem['add_on_qtys']);
            $cart->item_type = $model;
            $cart->reel_id = Helpers::resolve_reel_id(data_get($singleItem, 'reel_id'), $singleItem['item_id']);
            $cart->price = $singleItem['price'];
            $cart->quantity = $singleItem['quantity'];
            $cart->variations = json_encode($singleVariations);
            $cart->variation_options = data_get($singleItem, 'variation_options', []) != null
                ? json_encode(data_get($singleItem, 'variation_options', []))
                : json_encode([]);
            $cart->save();

            $item->carts()->save($cart);
            $restaurantId = $cart->restaurant_id;
        }

        return response()->json($this->getFormattedCartResponse(
            userId: $userId,
            isGuest: 0,
            restaurantId: $restaurantId
        ), 200);
    }

    private function isItemAvailable(string $model, int $itemId): bool
    {
        if ($model === 'Food') {
            return Food::withoutGlobalScope(\App\Scopes\RestaurantScope::class)
                ->active()
                ->whereKey($itemId)
                ->exists();
        }

        return ItemCampaign::active()->whereKey($itemId)->exists();
    }

    private function getFormattedCartResponse($userId, int $isGuest, $restaurantId = null): array
    {
        // A bundle whose offer was deleted, switched off, ran out of dates, or whose
        // restaurant left the offer is deliberately LEFT in the cart. It used to be deleted
        // here, which took the customer's basket apart without a word - they returned to a
        // cart that was quietly smaller and no explanation of why. It now stays, flagged:
        // BundleCartPresenter marks it is_available = false and names the reason, and
        // place_order refuses it with the same sentence. Nothing else in the cart is touched
        // by one dead bundle, and the customer decides whether to remove it.

        $formattedCarts = Cart::with('restaurant')
            ->where('user_id', $userId)
            ->where('is_guest', $isGuest)
            ->when(isset($restaurantId), function ($query) use ($restaurantId) {
                $query->whereHas('item', function ($q) use ($restaurantId) {
                    $q->where('restaurant_id', $restaurantId);
                });
            })
            ->get()
            ->map(function ($cart) {
                // add_on_ids and add_on_qtys are cast to array on the model, so they arrive
                // decoded already and json_decode() would be handed an array. That only went
                // unnoticed while the columns were null; a row that actually carries addons
                // blew up here. Decode only what is still a string.
                $decode = fn ($value) => is_string($value) ? json_decode($value, true) : $value;

                $cart->add_on_ids = $decode($cart->add_on_ids);
                $cart->add_on_qtys = $decode($cart->add_on_qtys);
                $cart->variations = $decode($cart->variations);
                $cart->variation_options = $decode($cart->variation_options);
                $cart->item = Helpers::cart_product_data_formatting(
                    $cart->item,
                    $cart->variations,
                    $cart->add_on_ids,
                    $cart->add_on_qtys,
                    false,
                    app()->getLocale()
                );

                return $cart;
            })
            ->filter(fn($cart) => $cart->item);

        // Every caller gets bundles collapsed into one entry. get_all_carts used to take the
        // raw rows because it groups by restaurant itself, but the two groupings are
        // independent - a bundle entry still carries its restaurant_id - and taking the rows
        // meant a three row bundle appeared there as three separate items.
        return $this->groupBundlesForResponse($formattedCarts, $userId, (int) $isGuest);
    }

    /**
     * Collapse each bundle's rows into a single entry. The work lives in
     * BundleCartPresenter because the AI chat has to present a cart the same way
     * this endpoint does - two implementations would drift, and a bundle that
     * reads as one line here and three lines there is worse than either.
     */
    private function groupBundlesForResponse(Collection $carts, $userId, int $isGuest): array
    {
        return app(BundleCartPresenter::class)->present($carts, $userId, $isGuest);
    }

    /**
     * What a store wide discount would come to on this restaurant's cart, and what is missing.
     *
     * Its own endpoint rather than a key on the cart, because /cart/list answers with a bare
     * array of rows: a summary object has nowhere to sit there without changing the response
     * from a list into an object and breaking every client reading it. The multi restaurant
     * cart is already a list of objects, so there the same block rides along inside each group.
     */
    public function discount_eligibility(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $request->user ? 'nullable' : 'required',
            'restaurant_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        [$userId, $isGuest] = $this->resolveCartOwner($request);
        $restaurant = Restaurant::find($this->resolveRestaurantIdFromInput($request->restaurant_id));

        if (! $restaurant) {
            return response()->json(['errors' => [['code' => 'restaurant', 'message' => translate('messages.Restaurant_not_found')]]], 404);
        }

        return response()->json($this->cartDiscountEligibility($userId, $isGuest, $restaurant), 200);
    }

    public function get_all_carts(Request $request): Collection
    {
        [$userId, $isGuest] = $this->resolveCartOwner($request);
        $carts = collect($this->getFormattedCartResponse(
            userId: $userId,
            isGuest: $isGuest
        ));

        return $carts->groupBy(function ($cart) {
            return $cart->restaurant_id ?? data_get($cart, 'item.restaurant_id') ?? 'unknown';
        })->map(function (Collection $restaurantCarts, $restaurantId) use ($userId, $isGuest) {
            $restaurant = $restaurantCarts->first()->restaurant;

            if (!$restaurant && is_numeric($restaurantId)) {
                $restaurant = Restaurant::find($restaurantId);
            }

            return [
                'restaurant' => [
                    'id' => $restaurant?->id ?? (is_numeric($restaurantId) ? (int) $restaurantId : null),
                    'name' => $restaurant?->name,
                    'slug' => $restaurant?->slug,
                    'logo' => $restaurant?->logo,
                    'logo_full_url' => $restaurant?->logo_full_url,
                    'verified_seller' => (int) ($restaurant?->verified_seller ?? 0),
                    'item_count' => $restaurantCarts->count(),

                ],
                // Per restaurant, because the threshold is that restaurant's: a basket split
                // across three stores can qualify at one and fall short at the other two.
                'discount_eligibility' => $this->cartDiscountEligibility($userId, $isGuest, $restaurant),
                'carts' => $restaurantCarts->values()->all(),
            ];
        })->values();
    }

    private function resolveCartOwner(Request $request): array
    {
        return [$request->user ? $request->user->id : $request['guest_id'], $request->user ? 0 : 1];
    }

    private function resolveItemType(string $model): string
    {
        return $model === 'Food' ? Food::class : ItemCampaign::class;
    }

    private function resolveItem(string $model, int $itemId)
    {
        return $model === 'Food' ? Food::find($itemId) : ItemCampaign::find($itemId);
    }

    private function resolveRestaurantId($item): ?int
    {
        return $item?->restaurant_id ?? $item?->restaurant?->id;
    }

    private function resolveRestaurantIdFromInput($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $id = Restaurant::where('slug', $value)->value('id');

        return $id ? (int) $id : null;
    }

    // ------------------------------------------------------------------ BOGO bundles

    /**
     * Add a BOGO bundle. Quantity is the number of whole bundles, so every enrolled item -
     * bought and free - is multiplied by it, and the rows are written as one group.
     */
    private function addBogoBundle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $request->user ? 'nullable' : 'required',
            'bogo_id' => 'required',
            'quantity' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        [$userId, $isGuest] = $this->resolveCartOwner($request);
        $quantity = (int) ($request['quantity'] ?? 1);

        Helpers::getZoneIds($request);
        $zoneIds = json_decode($request->header('zoneId'), true) ?? [];

        $enrollment = $this->resolveBogoBundleById($request['bogo_id'], $zoneIds);

        if (! $enrollment) {
            return response()->json(['errors' => [['code' => 'bogo_offer', 'message' => translate('messages.Offer_not_found')]]], 404);
        }

        // What the cart would hold afterwards, not this add on its own. Each add writes its own
        // group, so two adds of two were each judged as "two" and the cart ended up with four -
        // more than the stock or the customer's allowance could ever cover. Placement caught it,
        // which is the worst place to hear it.
        $wouldHold = $quantity + $this->bogoBundlesInCart(
            $userId, $isGuest, $enrollment->bogo_offer_id, $enrollment->restaurant_id
        );

        $reason = $this->bogoUnavailableReason($enrollment, $wouldHold, $userId, $isGuest, $request['contact_person_number']);

        if ($reason) {
            return response()->json(['errors' => [['code' => 'bogo_offer', 'message' => $reason]]], 403);
        }

        // No cross-restaurant guard here, deliberately: the cart holds several restaurants at
        // once - get_all_carts groups by restaurant and remove_cart clears one restaurant's
        // rows - and an ordinary add has no such check either. One restaurant per *order* is
        // enforced at checkout, where PlaceNewOrder rejects any line from another restaurant.
        $groupId = (string) Str::uuid();
        Cart::insert($this->bogoCartRows($enrollment, $groupId, $quantity, $userId, $isGuest));

        return response()->json([
            'message' => translate('messages.Offer_added_to_cart'),
            'bogo_group_id' => $groupId,
            'quantity' => $quantity,
            'data' => $this->getFormattedCartResponse($userId, $isGuest, $enrollment->restaurant_id),
        ], 200);
    }

    /**
     * Change how many copies of a bundle the cart holds. The group is atomic, so the rows
     * are rewritten from the enrolment rather than edited line by line.
     */
    private function updateBogoBundle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $request->user ? 'nullable' : 'required',
            'bogo_group_id' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        [$userId, $isGuest] = $this->resolveCartOwner($request);
        $groups = $this->bogoGroupsInCart($userId, $isGuest);
        $group = $groups[$request['bogo_group_id']] ?? null;

        if (! $group || ! $group['enrollment']) {
            return response()->json(['errors' => [['code' => 'bogo_group_id', 'message' => translate('messages.Offer_not_found')]]], 404);
        }

        $quantity = (int) $request['quantity'];
        // Every other group of this offer still counts; this one is being replaced, so it does
        // not count against itself.
        $wouldHold = $quantity + $this->bogoBundlesInCart(
            $userId, $isGuest, $group['enrollment']->bogo_offer_id,
            $group['enrollment']->restaurant_id, $request['bogo_group_id']
        );

        $reason = $this->bogoUnavailableReason($group['enrollment'], $wouldHold, $userId, $isGuest, $request['contact_person_number']);

        if ($reason) {
            return response()->json(['errors' => [['code' => 'bogo_offer', 'message' => $reason]]], 403);
        }

        Cart::where('user_id', $userId)->where('is_guest', $isGuest)
            ->where('bogo_group_id', $request['bogo_group_id'])->delete();
        Cart::insert($this->bogoCartRows($group['enrollment'], $request['bogo_group_id'], $quantity, $userId, $isGuest));

        return response()->json([
            'message' => translate('messages.Cart_updated'),
            'bogo_group_id' => $request['bogo_group_id'],
            'quantity' => $quantity,
            'data' => $this->getFormattedCartResponse($userId, $isGuest, $group['enrollment']->restaurant_id),
        ], 200);
    }

    /**
     * Remove a bundle whole. Individual lines are never removable - a partial bundle is not
     * a bundle the restaurant enrolled.
     */
    private function removeBogoBundle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $request->user ? 'nullable' : 'required',
            'bogo_group_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        [$userId, $isGuest] = $this->resolveCartOwner($request);

        $deleted = Cart::where('user_id', $userId)->where('is_guest', $isGuest)
            ->where('bogo_group_id', $request['bogo_group_id'])->delete();

        if (! $deleted) {
            return response()->json(['errors' => [['code' => 'bogo_group_id', 'message' => translate('messages.Offer_not_found')]]], 404);
        }

        return response()->json([
            'message' => translate('messages.Offer_removed_from_cart'),
            'data' => $this->getFormattedCartResponse($userId, $isGuest),
        ], 200);
    }

}
