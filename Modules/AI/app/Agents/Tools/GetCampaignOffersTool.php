<?php

namespace Modules\AI\app\Agents\Tools;

use Modules\AI\app\Agents\AiResponseContext;
use Modules\AI\app\Support\FoodPricing;
use App\Models\ItemCampaign;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * The campaign offers the storefront's "Special Food Offers" page shows.
 *
 * These are ItemCampaign rows — a different table from Food. Answering an
 * "offers" question from Food.discount (GetBestDealsTool) returns a completely
 * different list than the page the customer is looking at, which is exactly the
 * inconsistency this tool exists to remove. Mirrors CampaignController::
 * get_item_campaigns: active + running (inside its date/time window) + the
 * restaurant open on this weekday, active, and inside the customer's zone.
 */
class GetCampaignOffersTool implements Tool
{
    /** @param int[] $zoneIds Overlapping zones the user falls inside. */
    public function __construct(
        private readonly AiResponseContext $context,
        private readonly array $zoneIds = [],
    ) {}

    public function description(): string
    {
        return 'Get the platform\'s special offers / campaign items — the same promotions shown on the "Special Food Offers" page. Use for "offers", "special offers", "campaigns", "promotions", "what\'s on offer". These are campaign items, NOT regular discounted foods (for plain item discounts use GetBestDealsTool).';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->number()->description('Number of offers to return, default 8, or null for default')->required()->nullable(),
        ];
    }

    public function handle(Request $request): string
    {
        $args  = $request->all();
        $limit = min((int) ($args['limit'] ?? 8), 15);

        $campaigns = ItemCampaign::with('restaurant')
            ->active()
            ->running()
            ->whereHas('restaurant', function ($q) {
                $q->weekday()->active();
                if (! empty($this->zoneIds)) {
                    $q->whereIn('zone_id', $this->zoneIds);
                }
            })
            ->limit($limit)
            ->get();

        $offers = $campaigns->map($this->format(...))->values()->all();

        $this->context->recordTool('GetCampaignOffersTool');
        $this->context->addProducts($offers);

        if (count($offers) === 0) {
            return 'No special offers are running right now.';
        }

        $lines = implode('; ', array_map(function (array $o): string {
            $base = $o['name'] . ' [OFFER ID:' . $o['id'] . '] ' . $o['discounted_price']
                . ' (was ' . $o['price'] . ', ' . $o['discount_label'] . ')'
                . ' from ' . ($o['store_name'] ?? 'a restaurant');
            if (! empty($o['variations'])) {
                $base .= ' [variations:' . implode('/', array_column($o['variations'], 'type')) . ']';
            }
            return $base;
        }, $offers));

        // The cart pipeline is Food-only, so an offer cannot be added through
        // AddToCartTool — passing an OFFER ID there would hit an unrelated food
        // with the same ID. Say so in the tool result, not just in the prompt.
        return count($offers) . ' special offer(s) running: ' . $lines
            . '. NOTE: these are campaign offers — they cannot be added to the cart from chat.'
            . ' Point the customer to the offer card / Offers page to order one.';
    }

    private function parseVariations(mixed $raw): array
    {
        if (empty($raw)) return [];
        $decoded = \is_array($raw) ? $raw : json_decode((string) $raw, true);
        return \is_array($decoded) ? $decoded : [];
    }

    private function format(ItemCampaign $campaign): array
    {
        $price   = (float) $campaign->getAttribute('price');
        $pricing = FoodPricing::resolve($campaign, $price);

        return [
            'id'                  => $campaign->getKey(),
            // Campaign IDs live in their own table and can collide with food IDs —
            // the type tells the widget (and the agent) which one this is.
            'item_type'           => 'campaign',
            'name'                => $campaign->getAttribute('title'),
            'price'               => $price,
            'discounted_price'    => $pricing['discounted_price'],
            'discount'            => $pricing['discount'],
            'discount_type'       => $pricing['discount_type'],
            'discount_label'      => $pricing['discount_label'],
            'image'               => $campaign->getAttribute('image'),
            'image_full_url'      => $campaign->image_full_url,
            'veg'                 => (bool) $campaign->getAttribute('veg'),
            'is_available'        => true,
            'unavailable_reason'  => null,
            'availability_label'  => null,
            'maximum_cart_quantity' => (int) $campaign->getAttribute('maximum_cart_quantity'),
            'store_id'            => (int) $campaign->getAttribute('restaurant_id'),
            'store_name'          => $campaign->restaurant?->getAttribute('name'),
            'store_logo'          => $campaign->restaurant?->getAttribute('logo'),
            'store_logo_full_url' => $campaign->restaurant?->logo_full_url,
            'delivery_time'       => $campaign->restaurant?->getAttribute('delivery_time'),
            'minimum_order'       => $campaign->restaurant?->getAttribute('minimum_order'),
            'free_delivery'       => (bool) $campaign->restaurant?->getAttribute('free_delivery'),
            'available_date_ends' => $campaign->getAttribute('end_date'),
            'variations'          => $this->parseVariations($campaign->getAttribute('variations')),
        ];
    }
}
