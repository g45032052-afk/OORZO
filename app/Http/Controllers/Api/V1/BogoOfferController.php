<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\AddOn;
use App\Models\BogoOffer;
use App\Models\BogoOfferItem;
use App\Models\BogoOfferRestaurant;
use App\Models\BogoOfferUsage;
use App\Models\Food;
use App\Models\Restaurant;
use App\Services\Bogo\BogoOfferCatalog;
use App\Traits\HandlesBogoCart;
use App\Traits\HandlesBogoEnrollment;
use Illuminate\Http\Request;

/**
 * Customer-facing BOGO offers: the home card, the offer list, an offer's participating
 * restaurants, and the buy/get items of one restaurant's bundle.
 *
 * Read-only. Adding a bundle to the cart is not wired yet - the carts/order_details bogo
 * columns exist but nothing writes them - so `remaining_uses` reports the full allowance
 * until order placement starts recording bogo_offer_usages.
 *
 * Every identifier accepts an id or a slug, matching basic_campaign_details.
 */
class BogoOfferController extends Controller
{
    /**
     * Reading the catalogue lives in a service because the AI chat assistant answers
     * offer questions from the same rules. Kept as a constructor dependency rather
     * than resolved inline so the coupling is visible at the top of the class.
     */
    public function __construct(private readonly BogoOfferCatalog $catalog) {}

    // resolveUnitPrice()/decodeArray() come from here so customer pricing can never drift
    // from what the admin and vendor panels compute when the bundle is built.
    use HandlesBogoEnrollment;

    // bogoItemsUnavailableReason() comes from here, so what is advertised and what the cart
    // will accept are decided by one rule rather than two that can drift apart.
    use HandlesBogoCart;

    /**
     * Home "BOGO Offer Is Live" card plus a short preview of the offers behind it.
     */
    public function home(Request $request)
    {
        $zoneIds = $this->zoneIds($request);
        $limit = (int) ($request['limit'] ?? 6);

        $query = $this->catalog->availableOffers($zoneIds, $request['order_type']);
        $total = (clone $query)->count();

        $offers = $query->latest()->take($limit)->get()
            ->map(fn ($offer) => $this->catalog->offerCard($offer))->values();

        return response()->json([
            'is_live' => $total > 0,
            'title' => translate('messages.Hurry_Up_BOGO_Offer_Is_Live'),
            'description' => translate('messages.Get_the_best_value_from_your_order_with_our_exclusive_BOGO_deals'),
            'total_offers' => $total,
            'offers' => $offers,
        ], 200);
    }

    /**
     * The BOGO offer list screen.
     */
    public function offers(Request $request)
    {
        $zoneIds = $this->zoneIds($request);
        $limit = (int) ($request['limit'] ?? 10);
        $offset = (int) ($request['offset'] ?? 1);

        $paginator = $this->catalog->availableOffers($zoneIds, $request['order_type'])
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'offers' => collect($paginator->items())->map(fn ($offer) => $this->catalog->offerCard($offer))->values(),
        ], 200);
    }

    /**
     * Offer details: the offer itself plus every participating restaurant's bundle. One
     * restaurant contributes exactly one bundle per offer (the pivot is unique on the pair).
     */
    public function details(Request $request, $id)
    {
        $zoneIds = $this->zoneIds($request);
        $offer = $this->catalog->availableOffers($zoneIds, $request['order_type'])->where(
            fn ($q) => $q->where('id', $id)->orWhere('slug', $id)
        )->first();

        if (! $offer) {
            return response()->json(['errors' => [['code' => 'bogo_offer', 'message' => translate('messages.Offer_not_found')]]], 404);
        }

        $limit = (int) ($request['limit'] ?? 10);
        $offset = (int) ($request['offset'] ?? 1);

        $paginator = $this->catalog->bundleQuery($offer, $zoneIds, $request)
            ->paginate($limit, ['*'], 'page', $offset);

        $bundles = collect($paginator->items())
            ->map(fn ($enrollment) => $this->catalog->bundleCard($enrollment))
            ->values();

        return response()->json($this->catalog->offerCard($offer, $request) + [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'bundles' => $bundles,
        ], 200);
    }

    /**
     * BOGO offers a single restaurant runs, for the store details flow.
     */
    public function restaurantOffers(Request $request)
    {
        $zoneIds = $this->zoneIds($request);

        if (! $request['restaurant_id']) {
            return response()->json(['errors' => [['code' => 'restaurant_id', 'message' => translate('messages.Restaurant_id_is_required')]]], 403);
        }

        $restaurant = Restaurant::active()->whereIn('zone_id', $zoneIds)
            ->where(fn ($q) => $q->where('id', $request['restaurant_id'])->orWhere('slug', $request['restaurant_id']))
            ->first();

        if (! $restaurant) {
            return response()->json(['errors' => [['code' => 'restaurant', 'message' => translate('messages.Restaurant_not_found')]]], 404);
        }

        $limit = (int) ($request['limit'] ?? 10);
        $offset = (int) ($request['offset'] ?? 1);

        // This restaurant's own bundle has to be servable, not merely someone's in the zone -
        // and the same store rules apply here as anywhere else, so a closed store's page shows
        // no offers rather than offers nobody can order.
        $servableHere = $this->catalog->orderableEnrollments(
            $this->catalog->atServableStore(
                BogoOfferRestaurant::approved()->where('restaurant_id', $restaurant->id),
                $zoneIds
            )
        )->pluck('bogo_offer_id')->unique()->values()->all();

        $paginator = $this->catalog->availableOffers($zoneIds, $request['order_type'])
            ->whereIn('id', $servableHere)
            ->whereHas('enrollments', fn ($q) => $q->approved()->where('restaurant_id', $restaurant->id))
            // restaurant is eager loaded because pricing() resolves the live discount from it;
            // items.food because a free item is valued at today's price, and without it every
            // item on every offer would fetch its food one query at a time.
            ->with(['enrollments' => fn ($q) => $q->approved()->where('restaurant_id', $restaurant->id)->with(['items.food', 'restaurant'])])
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        $offers = collect($paginator->items())->map(function ($offer) use ($request) {
            $enrollment = $offer->enrollments->first();

            if (! $enrollment) {
                return $this->catalog->offerCard($offer, $request) + ['bundle_id' => null, 'buy_count' => 0, 'get_count' => 0];
            }

            // The same bundle card the offer details endpoint returns, so a store row carries
            // its buy/free items and needs no second call - minus the restaurant object, which
            // the store flow already has.
            return $this->catalog->offerCard($offer, $request) + $this->catalog->bundleCard($enrollment, withRestaurant: false);
        })->values();

        return response()->json([
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'offers' => $offers,
        ], 200);
    }

    // ---------------------------------------------------------------- helpers

    private function zoneIds(Request $request): array
    {
        Helpers::getZoneIds($request);

        return json_decode($request->header('zoneId'), true) ?? [];
    }

}
