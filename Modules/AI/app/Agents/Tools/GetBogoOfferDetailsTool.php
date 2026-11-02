<?php

namespace Modules\AI\app\Agents\Tools;

use App\Models\User;
use App\Services\Bogo\BogoOfferCatalog;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Http\Request as HttpRequest;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\AI\app\Agents\AiResponseContext;

/**
 * What is actually inside one BOGO offer, restaurant by restaurant.
 *
 * An offer is a headline ("Buy 2 Get 1"); the bundle is what a particular restaurant
 * enrolled with, and two restaurants running the same offer put different food in it at
 * different prices. So "what do I get?" can only be answered per restaurant, which is
 * what this returns.
 */
class GetBogoOfferDetailsTool implements Tool
{
    public function __construct(
        private readonly AiResponseContext $context,
        private readonly array             $zoneIds = [],
        private readonly ?User             $user    = null,
        private readonly ?string           $guestId = null,
    ) {}

    public function description(): string
    {
        return 'Get what is inside one BOGO offer — which restaurants run it, what the customer buys, what they get free, and the bundle price at each. Use after GetBogoOffersTool when the customer asks about a specific offer ("what do I get with that one?", "which restaurants have it?", "how much is it?"). Pass the OFFER ID from the offers list.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'offer_id' => $schema->number()->description('The OFFER ID from GetBogoOffersTool')->required(),
            'limit'    => $schema->number()->description('How many participating restaurants to return, default 5, or null')->required()->nullable(),
        ];
    }

    public function handle(Request $request): string
    {
        $this->context->recordTool('GetBogoOfferDetailsTool');

        $args    = $request->all();
        $offerId = (int) ($args['offer_id'] ?? 0);
        $limit   = min((int) ($args['limit'] ?? 5), 10);

        if (! $offerId) {
            return 'Ask which offer they mean, then call again with its OFFER ID.';
        }
        if (! $this->zoneIds) {
            return 'No delivery zone is known for this chat session, so the offer cannot be looked up.';
        }

        $catalog     = app(BogoOfferCatalog::class);
        $httpRequest = $this->identityRequest();

        // Resolved through availableOffers() rather than by id, so an offer that has ended,
        // sold out or has no servable restaurant left reads as "not available" instead of
        // being described in detail and refused at the cart.
        $offer = $catalog->availableOffers($this->zoneIds)
            ->where(fn ($q) => $q->where('id', $offerId)->orWhere('slug', $offerId))
            ->first();

        if (! $offer) {
            return 'That offer is not available right now — it may have ended, run out, or have no'
                .' participating restaurant open in this area. Suggest the current offers instead.';
        }

        $card = $catalog->offerCard($offer, $httpRequest);
        $this->context->addBogoOffers([$card]);

        $bundles = $catalog->bundleQuery($offer, $this->zoneIds, $httpRequest)->limit($limit)->get()
            ->map(fn ($enrollment) => $catalog->bundleCard($enrollment))
            ->values()->all();

        if (! $bundles) {
            return $card['title'].' ('.$card['offer_label'].') has no restaurant currently able to serve it.';
        }

        $lines = array_map(function (array $bundle) {
            $store = data_get($bundle, 'restaurant.name', 'A restaurant');
            $buy   = collect(data_get($bundle, 'buy_items', []))
                ->map(fn ($i) => data_get($i, 'quantity', 1).'x '.data_get($i, 'name', 'item'))->implode(' + ');
            $get   = collect(data_get($bundle, 'get_items', data_get($bundle, 'free_items', [])))
                ->map(fn ($i) => data_get($i, 'quantity', 1).'x '.data_get($i, 'name', 'item'))->implode(' + ');
            $price = data_get($bundle, 'bundle_price', data_get($bundle, 'final_price'));

            return $store.': buy '.($buy ?: '—').', get '.($get ?: '—').' free'
                .($price !== null ? ' for '.$price : '');
        }, $bundles);

        return $card['title'].' — '.$card['offer_label'].', valid until '.$card['valid_until'].'. '
            .count($bundles).' restaurant(s): '.implode(' | ', $lines)
            .'. The bundle is bought as one unit at that fixed price. The customer takes it from the'
            .' offer card — you cannot add it to their cart.';
    }

    /** Carries only who is asking; offerCard() needs it to work out remaining uses. */
    private function identityRequest(): HttpRequest
    {
        $request = HttpRequest::create('/', 'GET', $this->guestId ? ['guest_id' => $this->guestId] : []);
        $request->merge(['user' => $this->user]);

        return $request;
    }
}
