<?php
namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\CentralLogics\RestaurantLogic;
use App\Models\Advertisement;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdvertisementController extends Controller
{
    public function get_adds(Request $request)
    {


        Helpers::getZoneIds($request);
        $zone_ids= $request->header('zoneId');
        $zone_ids=  json_decode($zone_ids, true)?? [];

        // The food strip drawn under each ad card. Same cap and same clamping the restaurant
        // rails use - five is a card, not a menu, and `item_limit` can only ask for fewer -
        // and part of the cache key, because it changes what is cached.
        $requested = $request->query('item_limit');
        $item_limit = is_numeric($requested) ? min(5, max(1, (int) $requested)) : 5;

        $cacheKey = 'advertisements_' . md5(json_encode($zone_ids) . '_' . $item_limit);

        $Advertisement = Cache::remember($cacheKey, now()->addMinutes(20), function () use ($zone_ids, $item_limit) {
            $Advertisement= Advertisement::valid()->with('restaurant')

                ->when(count($zone_ids) > 0, function($query) use($zone_ids) {
                    $query->wherehas('restaurant', function($query) use($zone_ids){
                        $query->whereIn('zone_id',$zone_ids)->active();
                    });
                })

                ->orderByRaw('ISNULL(priority), priority ASC')
                ->get();

            try {
                $Advertisement->each(function ($advertisement) {
                    $advertisement->reviews_comments_count = (int) $advertisement->restaurant->reviews_comments()->count();
                    $reviewsInfo = $advertisement->restaurant->reviews()->where('reviews.status', 1)
                        ->selectRaw('avg(reviews.rating) as average_rating, count(reviews.id) as total_reviews, food.restaurant_id')
                        ->groupBy('food.restaurant_id')
                        ->first();

                    $advertisement->average_rating = (float)  $reviewsInfo?->average_rating ?? 0;
                });
            } catch (\Exception $e) {
                info($e->getMessage());
            }

            // Attached to the ad's own restaurant, in the shape every restaurant listing
            // returns it - RestaurantLogic::topItemsByRestaurantIds is the one place that
            // builds it, so the ad card and the listing card cannot disagree. Resolved for
            // the whole response in one query rather than per ad.
            $restaurants = $Advertisement->pluck('restaurant')->filter();
            $top_items = RestaurantLogic::topItemsByRestaurantIds(
                $restaurants->pluck('id')->unique()->all(),
                $item_limit,
                $restaurants->unique('id')->values()
            );

            $Advertisement->each(function ($advertisement) use ($top_items) {
                $advertisement->restaurant?->setAttribute(
                    'top_items',
                    $top_items[$advertisement->restaurant->id] ?? []
                );
            });

            return $Advertisement;
        });

        // Personalization: re-order ads so those for the user's preferred restaurants surface first (per-user, outside the shared cache).
        if (auth('api')->id()) {
            $Advertisement = \App\CentralLogics\PersonalizationService::reorderByPreference($Advertisement, (int) auth('api')->id(), 'restaurant_id', 'store');
        }

        return response()->json($Advertisement, 200);
    }

}
