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
 * Buy-one-get-one bundles the customer could actually take right now.
 *
 * Reads through BogoOfferCatalog, the same service /api/v1/bogo/offers answers from, so
 * the assistant can never advertise a bundle the offer screen does not show. The rules
 * behind that are not ones worth re-deriving: an offer disappears when ANY single food in
 * ANY of its bundles is out of stock, switched off, outside its serving hours, or sits at
 * a closed restaurant. Getting one of those wrong means promising a customer something the
 * cart then refuses.
 */
class GetBogoOffersTool implements Tool
{
    public function __construct(
        private readonly AiResponseContext $context,
        private readonly array             $zoneIds = [],
        private readonly ?User             $user    = null,
        private readonly ?string           $guestId = null,
    ) {}

    public function description(): string
    {
        return 'Get the buy-one-get-one (BOGO) offers running right now — "buy 2 get 1", "BOGO", "buy one get one free", "bundle offers", "any free item deals". These are BUNDLES: the customer buys a set of items and gets others free, at a fixed bundle price. Only offers that can actually be ordered are returned. You CANNOT add one to the cart — the customer taps the offer card to open it in the app.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->number()->description('How many offers to return, default 6, or null for the default')->required()->nullable(),
        ];
    }

    public function handle(Request $request): string
    {
        $this->context->recordTool('GetBogoOffersTool');

        $limit = min((int) ($request->all()['limit'] ?? 6), 10);

        if (! $this->zoneIds) {
            return 'No delivery zone is known for this chat session, so BOGO offers cannot be listed.';
        }

        $catalog = app(BogoOfferCatalog::class);
        $offers = $catalog->availableOffers($this->zoneIds)->latest()->limit($limit)->get();

        if ($offers->isEmpty()) {
            return 'There are no BOGO offers available in this area right now.';
        }

        // The card is built with the request so the customer is told how many uses they
        // have left - the one figure that decides whether the offer is worth mentioning
        // to THEM rather than in general.
        $httpRequest = $this->identityRequest();

        $cards = $offers->map(fn ($offer) => $catalog->offerCard($offer, $httpRequest))->values()->all();
        $this->context->addBogoOffers($cards);

        // A compact line per offer beside the card. Without it the model narrates from
        // memory of the question rather than from the data, and describes "buy 2 get 1"
        // over a card that says something else.
        $lines = array_map(function (array $card) {
            $remaining = $card['remaining_uses'] ?? null;
            $left = $remaining === null ? '' : ' — '.$remaining.' use(s) left for this customer';

            return $card['title'].' [OFFER ID:'.$card['id'].'] '.$card['offer_label']
                .' (valid until '.$card['valid_until'].')'.$left;
        }, $cards);

        return count($cards).' BOGO offer(s) available: '.implode(' | ', $lines)
            .'. Each is a bundle bought as one unit at a fixed price. To take one, the customer'
            .' opens the offer card and picks their items there — you cannot add it for them.';
    }

    /**
     * A request carrying only who is asking.
     *
     * offerCard() reads the caller's identity to work out their remaining uses, and it
     * expects an HTTP request because every other caller is a controller. Chat has the
     * same identity, just not in that shape.
     */
    private function identityRequest(): HttpRequest
    {
        $request = HttpRequest::create('/', 'GET', $this->guestId ? ['guest_id' => $this->guestId] : []);
        $request->merge(['user' => $this->user]);

        return $request;
    }
}
