<?php

namespace Modules\AI\app\Agents\Tools;

use Modules\AI\app\Agents\AiResponseContext;
use App\CentralLogics\Helpers;
use App\CentralLogics\RestaurantLogic;
use App\Models\Restaurant;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * Restaurant details by ID (Store→Restaurant). StackFood Restaurant has
 * schedules + discount relations (no store-level coupons relation).
 */
class GetStoreDetailsTool implements Tool
{
    public function __construct(private readonly AiResponseContext $context) {}

    public function description(): string
    {
        return 'Get full details of a specific restaurant by its ID: open/close schedule, today\'s hours, rating, minimum order, delivery time, free delivery, and contact info. Use after SearchStoresTool returns a restaurant ID.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'store_id' => $schema->number()->description('The ID of the restaurant to get details for')->required(),
        ];
    }

    public function handle(Request $request): string
    {
        $storeId = (int) ($request->all()['store_id'] ?? 0);

        /** @var Restaurant|null $store */
        $store = Restaurant::with(['schedules', 'discount', 'restaurant_config'])->find($storeId);

        if (! $store) {
            return "Restaurant #{$storeId} not found.";
        }

        $schedules = $store->schedules->map(fn ($s) => [
            'day'        => $s->getAttribute('day'),
            'open_time'  => $s->getAttribute('opening_time'),
            'close_time' => $s->getAttribute('closing_time'),
        ])->values()->all();

        // restaurant_schedule.day is an integer day-of-week (0=Sunday).
        $todayDow   = (int) now()->dayOfWeek;
        $todaySched = collect($schedules)->firstWhere('day', $todayDow);
        $schedInfo  = $todaySched
            ? "Today: opens {$todaySched['open_time']}, closes {$todaySched['close_time']}"
            : 'No schedule listed for today';

        // Only announce a campaign that is live RIGHT NOW — a bare
        // `$store->discount` also matches expired/out-of-hours rows, which the
        // storefront never prices with. Restaurant campaigns are always applied
        // as a percentage (see Helpers::product_discount_calculate_data).
        $activeDiscount = Helpers::get_restaurant_discount($store);
        $storeDiscount  = $activeDiscount ? [
            'discount'      => $activeDiscount['discount'],
            'discount_type' => 'percent',
            'min_purchase'  => $activeDiscount['min_purchase'],
            'max_discount'  => $activeDiscount['max_discount'],
        ] : null;

        $avg     = 0.0;
        $buckets = $store->getAttribute('rating');
        if (is_array($buckets) && count($buckets) === 5) {
            $avg = (float) (RestaurantLogic::calculate_restaurant_rating($buckets)['rating'] ?? 0);
        }

        $name         = $store->getAttribute('name');
        $deliveryTime = $store->getAttribute('delivery_time');
        $minOrder     = $store->getAttribute('minimum_order');

        // Open status mirrors the storefront's `open` computation: the
        // restaurant is open when it's active AND today's schedule window
        // covers the current time. (Previously this reflected only the manual
        // `active` toggle and could report OPEN outside business hours.)
        $nowT   = now()->format('H:i:s');
        $isOpen = (bool) $store->getAttribute('active')
            && $todaySched
            && !empty($todaySched['open_time']) && !empty($todaySched['close_time'])
            && $todaySched['open_time'] <= $nowT
            && $todaySched['close_time'] >= $nowT;

        $formatted = [
            'id'             => $store->getKey(),
            'name'           => $name,
            'logo'           => $store->getAttribute('logo'),
            'logo_full_url'  => $store->logo_full_url,
            'cover_photo'          => $store->getAttribute('cover_photo'),
            'cover_photo_full_url' => $store->cover_photo_full_url,
            'verified_seller'      => (int) $store->verified_seller,
            'rating'         => $avg,
            'order_count'    => (int) $store->getAttribute('order_count'),
            'delivery_time'  => $deliveryTime,
            'minimum_order'  => (float) $minOrder,
            'free_delivery'  => (bool) $store->getAttribute('free_delivery'),
            'is_open'        => $isOpen,
            'delivery'       => (bool) $store->getAttribute('delivery'),
            'take_away'      => (bool) $store->getAttribute('take_away'),
            'schedule_order' => (bool) $store->getAttribute('schedule_order'),
            'veg'            => (bool) $store->getAttribute('veg'),
            'non_veg'        => (bool) $store->getAttribute('non_veg'),
            'off_day'        => $store->getAttribute('off_day'),
            'phone'          => $store->getAttribute('phone'),
            'email'          => $store->getAttribute('email'),
            'address'        => $store->getAttribute('address'),
            'schedules'      => $schedules,
            'store_discount' => $storeDiscount,
        ];

        $this->context->recordTool('GetStoreDetailsTool');
        $this->context->addStores([$formatted]);

        $discountInfo = $storeDiscount
            ? ' — Restaurant discount: ' . $storeDiscount['discount'] . ($storeDiscount['discount_type'] === 'percent' ? '%' : ' flat') . ' off'
            : '';

        return "{$name} — Rating: " . number_format($avg, 1) . "/5 — {$schedInfo} — "
            . "Min order: {$minOrder} — Delivery: {$deliveryTime} — "
            . ((bool) $store->getAttribute('free_delivery') ? 'FREE delivery' : 'Paid delivery')
            . $discountInfo
            . ' — ' . ($isOpen ? 'Currently OPEN' : 'Currently CLOSED');
    }
}
