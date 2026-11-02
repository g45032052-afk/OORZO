<?php

namespace Modules\AI\app\Agents\Tools;

use Modules\AI\app\Agents\AiResponseContext;
use Modules\AI\app\Support\FoodAvailability;
use Modules\AI\app\Support\FoodPricing;
use Modules\AI\app\Support\StoreAvailability;
use App\CentralLogics\Helpers;
use App\CentralLogics\PersonalizationService;
use App\Models\Cart;
use App\Models\Food;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class AddToCartTool implements Tool
{
    /**
     * @param array $pendingVariation The option prompt shown on the PREVIOUS
     *        turn (['item_id','name','options']), used to keep a variation reply
     *        attached to the item we actually asked about.
     */
    public function __construct(
        private readonly AiResponseContext $context,
        private readonly ?User             $user             = null,
        private readonly ?string           $guestId          = null,
        private readonly array             $pendingVariation = [],
    ) {}

    public function description(): string
    {
        return 'Add a food item to the customer\'s cart. Works for authenticated users and guests. An item can have SEVERAL variation groups (e.g. Size AND Spice AND Sauce), and some are REQUIRED — the customer must choose from EVERY required group before it can be added. Pass every chosen option label in variation_options (an array). The tool tells you which groups still need a choice; keep asking until all required groups are satisfied, then call again with ALL chosen labels. Use the item ID from a search/popular/deals tool, and always pass item_name as a fallback.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'item_id'           => $schema->number()->description('The ID of the food to add')->required(),
            'item_name'         => $schema->string()->description('Fallback food name the user asked for, used if item_id does not resolve')->required()->nullable(),
            'quantity'          => $schema->number()->description('Quantity to add, default 1, or null for default')->required()->nullable(),
            'variation_options' => $schema->array()->items($schema->string())->description('ALL chosen option labels across every variation group, e.g. ["Large","Hot","Ketchup"]. Include one (or more, for multi-select groups) per required group. Empty or null if the item has no variations.')->required()->nullable(),
            'variation_type'    => $schema->string()->description('Deprecated single-choice fallback — prefer variation_options. A single chosen option label, or null.')->required()->nullable(),
        ];
    }

    public function handle(Request $request): string
    {
        $this->context->recordTool('AddToCartTool');

        if (! $this->user && ! $this->guestId) {
            return 'Cannot add to cart: no customer identity available. The chat session needs a logged-in user or a guest_id.';
        }

        $args     = $request->all();
        $itemId   = (int) ($args['item_id'] ?? 0);
        $itemName = isset($args['item_name']) && $args['item_name'] !== null ? trim((string) $args['item_name']) : null;
        $quantity = max(1, (int) ($args['quantity'] ?? 1));

        // Chosen option labels can arrive as a single variation_type (legacy) or
        // a variation_options array (one+ per group). Normalise to one list.
        $chosenLabels = [];
        if (isset($args['variation_type']) && $args['variation_type'] !== null && trim((string) $args['variation_type']) !== '') {
            $chosenLabels[] = trim((string) $args['variation_type']);
        }
        if (isset($args['variation_options']) && is_array($args['variation_options'])) {
            foreach ($args['variation_options'] as $label) {
                if (is_string($label) && trim($label) !== '') {
                    $chosenLabels[] = trim($label);
                }
            }
        }
        $chosenLabels = array_values(array_unique($chosenLabels));

        /** @var Food|null $item */
        $item = $this->loadItem($itemId);

        if (! $item && $itemName) {
            $item = Food::withoutGlobalScopes()
                ->where('name', 'like', "%{$itemName}%")
                ->orderByDesc('order_count')
                ->first($this->columns());
            if ($item) {
                $itemId = (int) $item->getKey();
            }
        }

        // Keep a variation reply glued to the item we actually asked about: if
        // the model attached the chosen option to a DIFFERENT item that can't
        // satisfy it (a classic ID mix-up on the follow-up turn), snap back to
        // the pending item. Runs before the gates below so they evaluate the
        // corrected item.
        $item = $this->reconcilePendingItem($item, $itemId, $chosenLabels);
        if ($item) {
            $itemId = (int) $item->getKey();
        }

        if (! $item) {
            return $itemName
                ? "I couldn't find a food item matching \"{$itemName}\". Could you confirm the name or search again?"
                : "Item #{$itemId} does not exist.";
        }

        $this->context->excludeProduct($itemId);

        if ((int) $item->getAttribute('status') !== 1) {
            return "\"{$item->getAttribute('name')}\" is currently unavailable and cannot be added to cart.";
        }

        $restaurant = Restaurant::select(['id', 'name', 'status', 'active'])
            ->when(! Helpers::schedule_order(), fn ($q) => $q->with('schedules'))
            ->find((int) $item->getAttribute('restaurant_id'));
        if (! $restaurant || (int) $restaurant->status !== 1) {
            return "\"{$item->getAttribute('name')}\" is from a restaurant that is currently inactive.";
        }

        // Closed right now (temporarily paused, or outside opening hours when
        // scheduled ordering is off) — same rule the storefront enforces.
        $store = StoreAvailability::of($restaurant);
        if (! $store['open']) {
            return "\"{$item->getAttribute('name')}\" can't be added right now — {$store['label']}. Want me to suggest something from an open restaurant?";
        }

        $startsAt = $item->getAttribute('available_time_starts');
        $endsAt   = $item->getAttribute('available_time_ends');
        if ($startsAt && $endsAt) {
            $nowTime = now()->format('H:i:s');
            if ($nowTime < $startsAt || $nowTime > $endsAt) {
                $window = date('g:i A', strtotime($startsAt)) . '–' . date('g:i A', strtotime($endsAt));
                return "\"{$item->getAttribute('name')}\" is not available right now — it's only served between {$window}. Want me to suggest something available now?";
            }
        }

        $stockTypeEarly = (string) $item->getAttribute('stock_type');
        if ($stockTypeEarly !== '' && $stockTypeEarly !== 'unlimited'
            && (int) $item->getAttribute('item_stock') <= 0) {
            return "\"{$item->getAttribute('name')}\" is out of stock and cannot be added to cart. Want me to suggest something similar that's in stock?";
        }

        $name       = $item->getAttribute('name');
        $variations = $this->parse($item->getAttribute('variations'));

        if (FoodAvailability::allRequiredOptionsSoldOut($item)) {
            return "\"{$name}\" is out of stock — every option is sold out, so it can't be added to cart. Want me to suggest something similar that's available?";
        }

        // --- Variation gate (multiple groups; single/multi select; required/min/max) ---
        // A food can have several groups, some required. The customer must satisfy
        // EVERY required group before the item is added.
        $selected          = [];
        $selectedOptionIds = [];
        $selectedMatches   = [];
        $price             = (float) $item->getAttribute('price');

        if (! empty($variations)) {
            // Merge with options already chosen on earlier turns for THIS item, so
            // the customer can answer one group at a time without re-listing prior
            // picks. New picks for a group replace the earlier pick (change of mind).
            $priorSelections = $this->pendingItemId() === $itemId
                ? $this->normaliseSelections((array) ($this->pendingVariation['selections'] ?? []))
                : [];

            [$byGroup, $invalidLabels] = $this->resolveChosenLabels($variations, $chosenLabels);

            $selections = $priorSelections;
            foreach ($byGroup as $groupName => $labels) {
                $selections[$groupName] = array_values(array_unique($labels));
            }

            // Validate every group against its required / min / max rules.
            $needed = [];
            foreach ($variations as $group) {
                $rules  = $this->groupRules((array) $group);
                $chosen = $selections[$rules['name']] ?? [];
                $count  = count($chosen);

                if ($count > $rules['max']) {
                    $needed[] = $this->groupNeed((array) $group, $rules, "choose at most {$rules['max']}");
                } elseif ($rules['required'] && $count < $rules['min']) {
                    $needed[] = $this->groupNeed((array) $group, $rules, $rules['min'] > 1 ? "choose at least {$rules['min']}" : null);
                }
            }

            // Prompt when a required group is still open, an invalid label was
            // given, or this is the first time we've met this item and nothing
            // has been chosen yet (so optional-only items still get offered).
            $firstEncounter = $this->pendingItemId() !== $itemId;
            $nothingChosen  = empty($chosenLabels) && empty($priorSelections);
            if (! empty($needed) || ! empty($invalidLabels) || ($firstEncounter && $nothingChosen)) {
                return $this->variationPrompt($name, $itemId, $variations, $selections, $invalidLabels);
            }

            // All required groups satisfied — build the canonical selection.
            foreach ($variations as $group) {
                $groupName = (string) ($group['name'] ?? '');
                $labels    = $selections[$groupName] ?? [];
                $canonical = [];
                foreach ($labels as $label) {
                    $match = $this->matchOption([$group], $label);
                    if ($match === null) {
                        continue;
                    }
                    $canonical[]         = $match['label'];
                    $selectedMatches[]   = $match;
                    if ($match['option_id'] !== null) {
                        $selectedOptionIds[] = (int) $match['option_id'];
                    }
                }
                if (! empty($canonical)) {
                    $selected[] = ['name' => $groupName, 'values' => ['label' => array_values(array_unique($canonical))]];
                }
            }
            $price = (float) $item->getAttribute('price') + (float) Helpers::variation_price($variations, $selected);
        }

        $payable = FoodPricing::resolve($item, $price)['discounted_price'];

        $maxQty = (int) $item->getAttribute('maximum_cart_quantity');
        if ($maxQty > 0 && $quantity > $maxQty) {
            return "Cannot add {$quantity} of {$name} — maximum allowed per cart is {$maxQty}.";
        }

        $isGuest    = $this->user === null;
        $cartUserId = $isGuest ? $this->guestId : $this->user->getKey();
        $vLabel     = $this->selectionLabel($selected);

        $existing = Cart::where('item_id', $itemId)
            ->where('item_type', Food::class)
            ->where('user_id', $cartUserId)
            ->where('is_guest', $isGuest)
            ->where('variations', json_encode($selected))
            ->first();

        $stockType = (string) $item->getAttribute('stock_type');
        if ($stockType !== '' && $stockType !== 'unlimited') {
            $available  = (int) $item->getAttribute('item_stock');
            $currentQty = $existing ? (int) $existing->getAttribute('quantity') : 0;
            $desiredQty = $currentQty + $quantity;

            if ($available <= 0) {
                return "\"{$name}\"{$vLabel} is out of stock and cannot be added to cart.";
            }
            if ($available < $desiredQty) {
                return $currentQty > 0
                    ? "Only {$available} of {$name}{$vLabel} left in stock, and you already have {$currentQty} in your cart."
                    : "Only {$available} of {$name}{$vLabel} left in stock.";
            }
        }

        // Per-option stock — every chosen option must have enough on hand.
        foreach ($selectedMatches as $match) {
            $optionStock = FoodAvailability::optionStock($match['option']);
            if ($optionStock === null) {
                continue;
            }
            $currentQty = $existing ? (int) $existing->getAttribute('quantity') : 0;
            $desiredQty = $currentQty + $quantity;

            if ($optionStock <= 0) {
                return "\"{$name}\" — {$match['label']} is out of stock. " . $this->inStockOptionsHint($variations);
            }
            if ($optionStock < $desiredQty) {
                return $currentQty > 0
                    ? "Only {$optionStock} of {$name} ({$match['label']}) left in stock, and you already have {$currentQty} in your cart."
                    : "Only {$optionStock} of {$name} ({$match['label']}) left in stock.";
            }
        }

        if ($existing) {
            $newQty = (int) $existing->getAttribute('quantity') + $quantity;
            if ($maxQty > 0 && $newQty > $maxQty) {
                $newQty = $maxQty;
            }
            $existing->update(['quantity' => $newQty, 'price' => $price]);
            $this->recordCartSignal($itemId, $isGuest);
            $this->publishCartSnapshot($cartUserId, $isGuest);
            return "Updated cart: {$name}{$vLabel} quantity is now {$newQty} ({$payable} each).";
        }

        $cart = new Cart();
        $cart->user_id           = $cartUserId;
        $cart->restaurant_id     = (int) $item->getAttribute('restaurant_id');
        $cart->item_id           = $itemId;
        $cart->is_guest          = $isGuest;
        $cart->add_on_ids        = json_encode([]);
        $cart->add_on_qtys       = json_encode([]);
        $cart->item_type         = Food::class;
        $cart->price             = $price;
        $cart->quantity          = $quantity;
        $cart->variations        = json_encode($selected);
        $cart->variation_options = json_encode($selectedOptionIds);
        $cart->save();

        $this->recordCartSignal($itemId, $isGuest);
        $this->publishCartSnapshot($cartUserId, $isGuest);

        return "Added {$quantity}× {$name}{$vLabel} to your cart ({$payable} each).";
    }

    private function recordCartSignal(int $itemId, bool $isGuest): void
    {
        if (! $isGuest && $this->user) {
            PersonalizationService::recordItemAction((int) $this->user->getKey(), $itemId, 'cart');
        }
    }

    private function parse(mixed $raw): array
    {
        if (empty($raw)) {
            return [];
        }
        $decoded = \is_array($raw) ? $raw : json_decode((string) $raw, true);
        return \is_array($decoded) ? $decoded : [];
    }

    private function matchOption(array $variations, string $type): ?array
    {
        $needle = strtolower(trim($type));
        if ($needle === '') {
            return null;
        }

        // Exact label match first.
        foreach ($variations as $group) {
            foreach (($group['values'] ?? []) as $opt) {
                if (strtolower(trim((string) ($opt['label'] ?? ''))) === $needle) {
                    return $this->optionResult((array) $group, (array) $opt);
                }
            }
        }

        // Typo tolerance: "samll" → "Small". Resolve against a clear winner only
        // (see fuzzyMatchLabel), so a genuinely different value never mis-maps.
        $canonical = $this->fuzzyMatchLabel($type, $this->allOptionLabels($variations));
        if ($canonical === null) {
            return null;
        }
        foreach ($variations as $group) {
            foreach (($group['values'] ?? []) as $opt) {
                if ((string) ($opt['label'] ?? '') === $canonical) {
                    return $this->optionResult((array) $group, (array) $opt);
                }
            }
        }
        return null;
    }

    private function optionResult(array $group, array $opt): array
    {
        return [
            'group'     => $group['name'] ?? '',
            'label'     => $opt['label'] ?? '',
            'price'     => (float) ($opt['optionPrice'] ?? 0),
            'option_id' => $opt['option_id'] ?? null,
            'option'    => $opt,
        ];
    }

    private function columns(): array
    {
        return ['id', 'name', 'price', 'discount', 'discount_type', 'maximum_cart_quantity', 'status', 'variations', 'restaurant_id', 'stock_type', 'item_stock', 'sell_count', 'available_time_starts', 'available_time_ends'];
    }

    private function loadItem(int $id): ?Food
    {
        return $id > 0 ? Food::withoutGlobalScopes()->find($id, $this->columns()) : null;
    }

    /**
     * When the customer is answering a variation prompt from the previous turn,
     * keep the choice attached to the item we actually asked about. Only snaps
     * back when the model's supplied item CANNOT satisfy the answer but the
     * pending item CAN — so naming a genuinely different item (that happens to
     * share an option label) is respected.
     *
     * @param string[] $chosenLabels
     */
    private function reconcilePendingItem(?Food $passed, int $passedId, array $chosenLabels): ?Food
    {
        if (empty($chosenLabels) || empty($this->pendingVariation)) {
            return $passed;
        }

        $pendingId = (int) ($this->pendingVariation['item_id'] ?? 0);
        if ($pendingId <= 0 || ($passed && $pendingId === $passedId)) {
            return $passed;
        }

        // At least one answer must be a real option of the item we asked about.
        $pendingOptions   = (array) ($this->pendingVariation['options'] ?? []);
        $answersThePending = false;
        foreach ($chosenLabels as $label) {
            if ($this->fuzzyMatchLabel($label, $pendingOptions) !== null) {
                $answersThePending = true;
                break;
            }
        }
        if (! $answersThePending) {
            return $passed;
        }

        // If the model's item can satisfy any of the answers on its own, respect
        // it — the customer may have switched to a different item.
        if ($passed) {
            $passedVariations = $this->parse($passed->getAttribute('variations'));
            foreach ($chosenLabels as $label) {
                if ($this->matchOption($passedVariations, $label) !== null) {
                    return $passed;
                }
            }
        }

        return $this->loadItem($pendingId) ?? $passed;
    }

    private function pendingItemId(): int
    {
        return (int) ($this->pendingVariation['item_id'] ?? 0);
    }

    /**
     * Normalise a stored selections map (group => labels) into
     * array<string, string[]>, dropping blanks.
     *
     * @return array<string, string[]>
     */
    private function normaliseSelections(array $selections): array
    {
        $out = [];
        foreach ($selections as $group => $labels) {
            $clean = array_values(array_filter(array_map(
                static fn ($l) => is_string($l) ? trim($l) : '',
                is_array($labels) ? $labels : [$labels]
            ), static fn ($l) => $l !== ''));
            if (! empty($clean)) {
                $out[(string) $group] = $clean;
            }
        }
        return $out;
    }

    /**
     * Resolve each chosen label to its variation group (canonical label). Labels
     * that match no option are returned separately as invalid.
     *
     * @param  string[] $labels
     * @return array{0: array<string, string[]>, 1: string[]}
     */
    private function resolveChosenLabels(array $variations, array $labels): array
    {
        $byGroup = [];
        $invalid = [];
        foreach ($labels as $label) {
            $match = $this->matchOption($variations, $label);
            if ($match === null) {
                $invalid[] = $label;
                continue;
            }
            $byGroup[(string) $match['group']][] = (string) $match['label'];
        }
        return [$byGroup, $invalid];
    }

    /**
     * Effective selection rules for a group. single = exactly one; multi honours
     * min/max; required forces min ≥ 1.
     *
     * @return array{name:string, type:string, required:bool, min:int, max:int}
     */
    private function groupRules(array $group): array
    {
        $type     = strtolower((string) ($group['type'] ?? 'single'));
        $required = strtolower((string) ($group['required'] ?? 'off')) === 'on';

        if ($type === 'single') {
            $min = $required ? 1 : 0;
            $max = 1;
        } else {
            $min = (int) ($group['min'] ?? 0);
            if ($required && $min < 1) {
                $min = 1;
            }
            $max = (int) ($group['max'] ?? 0);
            if ($max <= 0) {
                $max = PHP_INT_MAX;
            }
            if ($max < $min) {
                $max = $min;
            }
        }

        return [
            'name'     => (string) ($group['name'] ?? 'Options'),
            'type'     => $type,
            'required' => $required,
            'min'      => $min,
            'max'      => $max,
        ];
    }

    /**
     * A one-line "this group still needs a choice" descriptor for the prompt,
     * listing only in-stock options.
     *
     * @param array{name:string, type:string, required:bool, min:int, max:int} $rules
     */
    private function groupNeed(array $group, array $rules, ?string $note): string
    {
        $opts = [];
        foreach (($group['values'] ?? []) as $opt) {
            if (FoodAvailability::optionSoldOut((array) $opt)) {
                continue;
            }
            $opts[] = '"' . ($opt['label'] ?? '') . '" (+' . ($opt['optionPrice'] ?? 0) . ')';
        }
        $hint = $note ?? ($rules['type'] === 'single' || $rules['max'] === 1 ? 'choose one' : "choose {$rules['min']}–{$rules['max']}");
        return $rules['name'] . ' (' . $hint . '): ' . implode(', ', $opts);
    }

    /** Human label for a built selection, e.g. " (Size: Small, Spice: Hot)". */
    private function selectionLabel(array $selected): string
    {
        $parts = [];
        foreach ($selected as $group) {
            $labels = $group['values']['label'] ?? [];
            if (! empty($labels)) {
                $parts[] = ($group['name'] ?? 'Options') . ': ' . implode(', ', $labels);
            }
        }
        return empty($parts) ? '' : ' (' . implode(', ', $parts) . ')';
    }

    /** All option labels across every variation group, flattened. */
    private function allOptionLabels(array $variations): array
    {
        $labels = [];
        foreach ($variations as $group) {
            foreach (($group['values'] ?? []) as $opt) {
                $label = (string) ($opt['label'] ?? '');
                if ($label !== '') {
                    $labels[] = $label;
                }
            }
        }
        return $labels;
    }

    /**
     * Return the option label the input most likely means, or null if there is
     * no confident single winner. Guards against silent mis-mapping in a cart:
     * the closest label must be within a tight edit-distance tolerance AND
     * clearly ahead of the runner-up (gap ≥ 2), so "9 inch" never becomes
     * "6 inch" while "samll" still becomes "Small".
     */
    private function fuzzyMatchLabel(string $input, array $labels): ?string
    {
        $needle = strtolower(trim($input));
        if ($needle === '') {
            return null;
        }

        foreach ($labels as $label) {
            if (strtolower(trim((string) $label)) === $needle) {
                return (string) $label;
            }
        }

        if (mb_strlen($needle) < 3) {
            return null; // too short to disambiguate a typo safely
        }

        $best = null;
        $bestDist = PHP_INT_MAX;
        $secondDist = PHP_INT_MAX;
        foreach ($labels as $label) {
            $cand = strtolower(trim((string) $label));
            if ($cand === '') {
                continue;
            }
            $dist = levenshtein($needle, $cand);
            if ($dist < $bestDist) {
                $secondDist = $bestDist;
                $bestDist   = $dist;
                $best       = (string) $label;
            } elseif ($dist < $secondDist) {
                $secondDist = $dist;
            }
        }

        if ($best === null) {
            return null;
        }

        $tolerance = (int) max(1, ceil(mb_strlen($best) / 3));
        return ($bestDist <= $tolerance && ($secondDist - $bestDist) >= 2) ? $best : null;
    }

    /**
     * Prompt for the still-open variation groups. Echoes what's already chosen
     * (so an incremental reply need not repeat it) and lists every group that
     * still needs a choice, with its required/min/max rule. Pins the item +
     * accumulated selections so the next turn can continue where this left off.
     *
     * @param array<string, string[]> $selections    Group => canonical labels chosen so far.
     * @param string[]                $invalidLabels  Labels the customer gave that matched no option.
     */
    private function variationPrompt(string $name, int $itemId, array $variations, array $selections = [], array $invalidLabels = []): string
    {
        // Pin item + accumulated choices so a follow-up reply ("small") stays
        // attached to this item and builds on what's already picked.
        $this->context->setPendingVariation([
            'item_id'    => $itemId,
            'name'       => $name,
            'options'    => $this->allOptionLabels($variations),
            'selections' => $selections,
        ]);

        $chosenParts = [];
        $needLines   = [];
        foreach ($variations as $group) {
            $rules  = $this->groupRules((array) $group);
            $chosen = $selections[$rules['name']] ?? [];
            $count  = count($chosen);

            if (! empty($chosen)) {
                $chosenParts[] = $rules['name'] . ': ' . implode(', ', $chosen);
            }

            $satisfied = $count <= $rules['max']
                && (! $rules['required'] || $count >= $rules['min'])
                && ($rules['required'] || $count > 0 || ! $rules['required']);

            // Show a group as "needed" when it's required-and-open, or over-max.
            if ($count > $rules['max']) {
                $needLines[] = $this->groupNeed((array) $group, $rules, "choose at most {$rules['max']}");
            } elseif ($rules['required'] && $count < $rules['min']) {
                $needLines[] = $this->groupNeed((array) $group, $rules, null)
                    . ($rules['required'] ? ' [required]' : '');
            } elseif (empty($selections) && ! $rules['required'] && $count === 0) {
                // First-encounter offer for optional groups (customer may skip).
                $needLines[] = $this->groupNeed((array) $group, $rules, null) . ' [optional]';
            }
        }

        $parts = [];
        if (! empty($invalidLabels)) {
            $parts[] = '"' . implode('", "', $invalidLabels) . '" is not a valid option for this item.';
        }
        $parts[] = "{$name} [ID:{$itemId}] needs your choices.";
        if (! empty($chosenParts)) {
            $parts[] = 'Already chosen — ' . implode('; ', $chosenParts) . '.';
        }
        if (! empty($needLines)) {
            $parts[] = 'Still to choose (one reply can cover all): ' . implode(' | ', $needLines);
        }

        return implode(' ', $parts);
    }

    private function inStockOptionsHint(array $variations): string
    {
        $available = [];
        foreach ($variations as $group) {
            foreach (($group['values'] ?? []) as $option) {
                if (! FoodAvailability::optionSoldOut((array) $option)) {
                    $available[] = (string) ($option['label'] ?? '');
                }
            }
        }
        $available = array_values(array_filter($available));

        return empty($available)
            ? 'No other option is in stock either.'
            : 'Still available: ' . implode(', ', $available) . '.';
    }

    private function publishCartSnapshot(int|string $cartUserId, bool $isGuest): void
    {
        $this->context->addCartItems(
            \Modules\AI\app\Services\CartSnapshotService::items($cartUserId, $isGuest)
        );
    }
}
