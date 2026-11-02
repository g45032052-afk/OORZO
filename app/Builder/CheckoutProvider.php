<?php

namespace App\Builder;

use App\CentralLogics\Helpers;
use App\Models\AddOn;
use App\Http\Controllers\Api\V1\OrderController;
use App\Models\DeliveryRule;
use App\Models\Food;
use App\Models\OfflinePaymentMethod;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Builder\Contracts\CartProvider;
use Modules\Builder\Contracts\CheckoutProvider as CheckoutProviderContract;
use Modules\Builder\Contracts\CouponProvider;
use Modules\Builder\Contracts\LocationProvider;
use Modules\Builder\Services\StorefrontContext;
use Modules\Builder\ValueObjects\Storefront\CheckoutQuoteDTO;
use Modules\Builder\ValueObjects\Storefront\CheckoutSnapshotDTO;
use Modules\Builder\ValueObjects\StorefrontScope;

class CheckoutProvider implements CheckoutProviderContract
{
    public function __construct(
        private StorefrontContext $context,
        private CartProvider $cart,
        private CouponProvider $coupons,
        private LocationProvider $location,
    ) {
    }

    /* ─── snapshot ─────────────────────────────────────────── */

    public function snapshot(?StorefrontScope $scope, ?int $customerId): CheckoutSnapshotDTO
    {
        $restaurantId = $scope?->subTenantId;
        $restaurant   = $restaurantId ? Restaurant::query()->find($restaurantId) : null;

        $deliveryTypes = $this->mapDeliveryTypes($restaurant);
        if ($customerId === null) {
            $deliveryTypes['schedule'] = false;
        }

        return CheckoutSnapshotDTO::fromArray([
            'store'          => $this->mapStore($restaurant),
            'deliveryTypes'  => $deliveryTypes,
            'paymentMethods' => $this->mapPaymentMethods($customerId),
            'features'       => $this->mapFeatures($restaurant),
            'tipPresets'     => (array) config('builder.capabilities.checkout.tipPresets', [10, 15, 20, 40]),
            'mostTipped'     => $this->mostTippedAmount(),
            'scheduleSlots'  => ($deliveryTypes['schedule'] && $restaurantId)
                ? $this->buildScheduleSlots($restaurantId, $deliveryTypes['scheduleSlotDuration'])
                : [],
            'coverage'       => $this->mapCoverage($restaurant),
            'freeDelivery'   => $this->mapFreeDeliverySetup($restaurant),
        ]);
    }

    private function mapStore(?Restaurant $restaurant): ?array
    {
        if (!$restaurant) {
            return null;
        }
        $open = isset($restaurant->open) ? (bool) $restaurant->open : true;

        return [
            'id'               => (int) $restaurant->id,
            'name'             => (string) $restaurant->name,
            'open'             => $open,
            'minOrder'         => (float) ($restaurant->minimum_order ?? 0),
            'freeDeliveryOver' => $restaurant->free_delivery ? 0.0 : null,
        ];
    }

    private function mapCoverage(?Restaurant $restaurant): array
    {
        if (!$restaurant?->zone_id || (int) $restaurant->sub_self_delivery === 1) {
            return [];
        }

        $payload = Helpers::deliveryChargeCoverageForZone($restaurant->zone_id);
        $method  = $payload['delivery_charge_type'] ?? null;

        if (!in_array($method, [DeliveryRule::METHOD_AREA, DeliveryRule::METHOD_ZIP], true)) {
            return [];
        }

        return [
            'method'  => $method,
            'isZip'   => $method === DeliveryRule::METHOD_ZIP,
            'options' => \array_map(fn ($row) => [
                'id'     => (int) $row['id'],
                'name'   => (string) $row['name'],
                'charge' => (float) $row['delivery_charge'],
            ], $payload['data'] ?? []),
        ];
    }

    private function mapFreeDeliverySetup(?Restaurant $restaurant): array
    {
        if (!$restaurant?->zone_id) {
            return [];
        }

        $setup = Helpers::activeFreeDelivery($restaurant->zone_id);

        if (!$setup) {
            return [];
        }

        return [
            'type'      => $setup->apiType(),
            'coversAny' => $setup->coversAnyAmount(),
            'threshold' => $setup->coversAnyAmount() ? 0.0 : (float) $setup->minimum_order_amount,
        ];
    }

    private function mapDeliveryTypes(?Restaurant $restaurant = null): array
    {
        $delivery = (int) Helpers::get_business_settings('home_delivery') === 1;
        $pickup   = (int) Helpers::get_business_settings('take_away') === 1;
        $schedule = (bool) Helpers::get_business_settings('schedule_order');

        if ($restaurant) {
            $delivery = $delivery && (bool) $restaurant->delivery;
            $pickup   = $pickup   && (bool) $restaurant->take_away;
            $schedule = $schedule && (bool) $restaurant->schedule_order;
        }

        return [
            'delivery'             => $delivery,
            'pickup'               => $pickup,
            'schedule'             => $schedule,
            'scheduleSlotDuration' => $this->slotDurationMinutes(),
        ];
    }

    private function slotDurationMinutes(): int
    {
        $raw = (int) (Helpers::get_business_settings('schedule_order_slot_duration') ?? 0);
        if ($raw < 5 || $raw > 240) {
            return 30;
        }
        return $raw;
    }

    private function buildScheduleSlots(int $restaurantId, int $slotDurationMin): array
    {
        $duration = max(5, $slotDurationMin);
        $rows = DB::table('restaurant_schedule')
            ->where('restaurant_id', $restaurantId)
            ->get(['day', 'opening_time', 'closing_time']);

        if ($rows->isEmpty()) {
            return [];
        }

        $byDay = [];
        foreach ($rows as $r) {
            $byDay[(int) $r->day][] = ['open' => $r->opening_time, 'close' => $r->closing_time];
        }

        $now    = \Carbon\Carbon::now();
        $today  = $now->copy()->startOfDay();
        $result = [];

        for ($i = 0; $i < 7; $i++) {
            $day    = $today->copy()->addDays($i);
            $dow    = (int) $day->dayOfWeek;
            $ranges = $byDay[$dow] ?? [];
            $slots  = [];

            foreach ($ranges as $range) {
                $start   = \Carbon\Carbon::parse($day->format('Y-m-d') . ' ' . $range['open']);
                $closing = \Carbon\Carbon::parse($day->format('Y-m-d') . ' ' . $range['close']);

                while ($start->copy()->addMinutes($duration)->lte($closing)) {
                    $end = $start->copy()->addMinutes($duration);
                    if ($i === 0 && $start->lt($now)) {
                        $start = $end;
                        continue;
                    }
                    $slots[] = [
                        'start' => $start->format('H:i'),
                        'end'   => $end->format('H:i'),
                        'iso'   => $start->format('Y-m-d H:i:s'),
                        'label' => $start->format('g:i A') . ' - ' . $end->format('g:i A'),
                    ];
                    $start = $end;
                }
            }

            $result[] = [
                'date'  => $day->format('Y-m-d'),
                'label' => $i === 0 ? 'Today' : ($i === 1 ? 'Tomorrow' : $day->format('D, M j')),
                'slots' => $slots,
            ];
        }

        return $result;
    }

    private function mapPaymentMethods(?int $customerId): array
    {
        $cod            = Helpers::get_business_settings('cash_on_delivery');
        $digital        = Helpers::get_business_settings('digital_payment');
        $offlineEnabled = (int) Helpers::get_business_settings('offline_payment_status') === 1;

        $walletFeaturesEnabled = (bool) config('builder.wallet_features_enabled', true);
        $partialEnabled = $walletFeaturesEnabled
            && (int) Helpers::get_business_settings('partial_payment_status') === 1;
        $partialMethod  = Helpers::get_business_settings('partial_payment_method');
        $walletEnabled  = $walletFeaturesEnabled
            && (int) Helpers::get_business_settings('wallet_status') === 1;

        $walletBalance = 0.0;
        if ($walletEnabled && $customerId) {
            $walletBalance = (float) (User::query()->where('id', $customerId)->value('wallet_balance') ?? 0);
        }

        $offlineMethods = $offlineEnabled
            ? OfflinePaymentMethod::query()
                ->where('status', 1)
                ->get(['id', 'method_name', 'method_fields', 'method_informations'])
                ->map(fn ($m) => [
                    'id'           => (int) $m->id,
                    'name'         => (string) $m->method_name,
                    'fields'       => is_array($m->method_fields) ? $m->method_fields
                        : (json_decode((string) $m->method_fields, true) ?: []),
                    'informations' => is_array($m->method_informations) ? $m->method_informations
                        : (json_decode((string) $m->method_informations, true) ?: []),
                ])
                ->all()
            : [];

        $gateways = [];
        if (is_array($digital) && (int) ($digital['status'] ?? 0) === 1) {
            foreach (Helpers::getActivePaymentGateways() as $g) {
                $g = (array) $g;
                $gateways[] = [
                    'key'      => (string) ($g['gateway'] ?? ''),
                    'title'    => (string) ($g['gateway_title'] ?? $g['gateway'] ?? ''),
                    'imageUrl' => $g['gateway_image_full_url'] ?? null,
                ];
            }
        }

        return [
            'cod'      => ['enabled' => is_array($cod) && (int) ($cod['status'] ?? 0) === 1, 'allowChangeAmount' => true],
            'offline'  => ['enabled' => $offlineEnabled, 'methods' => $offlineMethods],
            'gateways' => $gateways,
            'partial'  => ['enabled' => $partialEnabled, 'method' => $partialMethod ?: null],
            'wallet'   => ['enabled' => $walletEnabled, 'balance' => $walletBalance],
        ];
    }

    private function mapFeatures(?Restaurant $restaurant): array
    {
        $additionalChargeEnabled = (int) Helpers::get_business_settings('additional_charge_status') === 1;
        $additionalChargeAmount  = (float) Helpers::get_business_settings('additional_charge');
        $additionalChargeName    = (string) (Helpers::get_business_settings('additional_charge_name') ?: 'Service Charge');

        $extraPackagingFee      = 0.0;
        $extraPackagingEnabled  = false;
        $extraPackagingRequired = false;
        if ((int) (Helpers::get_business_settings('extra_packaging_charge') ?? 0) === 1
            && $restaurant && ($cfg = $restaurant->restaurant_config ?? null)) {
            $extraPackagingEnabled  = (int) ($cfg->is_extra_packaging_active ?? 0) === 1;
            $extraPackagingRequired = $extraPackagingEnabled && (int) ($cfg->extra_packaging_status ?? 0) === 1;
            $extraPackagingFee      = $extraPackagingEnabled ? (float) ($cfg->extra_packaging_amount ?? 0) : 0.0;
        }

        $taxIncluded = false;
        if (\addon_published_status('TaxModule')) {
            $sys = \Modules\TaxModule\Entities\SystemTaxSetup::query()
                ->where('is_active', 1)->where('is_default', 1)->first();
            $taxIncluded = (int) ($sys?->is_included ?? 0) === 1;
        }

        return [
            'tipsEnabled' => (int) Helpers::get_business_settings('dm_tips_status') === 1,
            'additionalCharge' => [
                'enabled' => $additionalChargeEnabled,
                'name'    => $additionalChargeName,
                'amount'  => $additionalChargeAmount,
            ],
            'extraPackaging' => [
                'enabled'  => $extraPackagingEnabled,
                'required' => $extraPackagingRequired,
                'fee'      => $extraPackagingFee,
            ],
            'taxIncluded'    => $taxIncluded,
        ];
    }

    private function mostTippedAmount(): ?float
    {
        try {
            return Cache::remember('builder.checkout.mostTipped', now()->addHours(6), function () {
                $val = DB::table('orders')
                    ->where('dm_tips', '>', 0)
                    ->select('dm_tips', DB::raw('count(*) as n'))
                    ->groupBy('dm_tips')
                    ->orderByDesc('n')
                    ->limit(1)
                    ->value('dm_tips');
                return $val !== null ? (float) $val : null;
            });
        } catch (\Throwable) {
            return null;
        }
    }

    /* ─── quote ────────────────────────────────────────────── */

    public function quote(?StorefrontScope $scope, ?int $customerId, array $state): CheckoutQuoteDTO
    {
        $cart  = $this->cart->list();
        $items = $cart['items'] ?? [];

        // Priced here rather than read off the cart rows. A row holds what the browser sent
        // when the item went in, and a store wide rate cannot be judged from one row: it has a
        // minimum purchase and a cap that only the whole basket answers, and a happy hour has
        // a window that may have closed since. Both are decided now, against this basket.
        $pricing        = $this->cartPricing($items, $scope);
        $itemPrice      = $pricing['gross'];
        $itemDiscount   = $pricing['discount'];

        $couponCode      = $state['couponCode'] ?? null;
        $couponDiscount  = 0.0;
        $couponTitle     = null;
        $couponError     = null;
        $couponFreeDeliv = false;
        if ($couponCode) {
            $base = max(0.0, $itemPrice - $itemDiscount);
            $r = $this->coupons->validate((string) $couponCode, $customerId, $scope, $base);
            if ($r['ok'] ?? false) {
                $couponCode      = $r['code'] ?? $couponCode;
                $couponDiscount  = (float) ($r['discount'] ?? 0);
                $couponTitle     = $r['title'] ?? null;
                $couponFreeDeliv = (bool) ($r['freeDelivery'] ?? false);
            } else {
                $couponError = (string) ($r['error'] ?? 'Invalid coupon');
                $couponCode  = null;
            }
        }

        $discountedSubtotal = max(0.0, $itemPrice - $itemDiscount - $couponDiscount);
        $state['couponCode'] = $couponCode;
        $taxIncluded = $this->isTaxIncluded();
        $taxData = $this->computeTaxBreakdown($scope, $customerId, $state, $discountedSubtotal);
        $tax = $taxData['tax'];
        $proDiscount = $taxData['proDiscount'];
        $referralDiscount = $taxData['referralDiscount'];

        if ($taxData['totalPrice'] !== null) {
            $discountedSubtotal = max(0.0, $taxData['totalPrice']);
        }

        $deliveryType = $state['deliveryType'] ?? 'delivery';
        [$deliveryFee, $deliveryFeeNote, $distanceKm, $freeDelivery, $surge] = $deliveryType === 'pickup'
            ? [0.0, null, null, ['active' => false, 'reason' => 'pickup'], self::NO_SURGE]
            : $this->computeDelivery($scope, $state, $discountedSubtotal, $couponFreeDeliv);

        $proDeliverySavings = 0.0;
        if ($deliveryType !== 'pickup' && $deliveryFee > 0) {
            [$deliveryFee, $proDeliverySavings, $freeDelivery] = $this->applyProDelivery(
                $scope, $customerId, $deliveryFee, $discountedSubtotal, $freeDelivery
            );
        }

        $additionalCharge = ($deliveryType !== 'pickup' && (int) Helpers::get_business_settings('additional_charge_status') === 1)
            ? (float) Helpers::get_business_settings('additional_charge')
            : 0.0;

        $tipsEnabled = (int) Helpers::get_business_settings('dm_tips_status') === 1;
        $dmTip = ($tipsEnabled && $deliveryType !== 'pickup') ? (float) ($state['tip'] ?? 0) : 0.0;

        $packagingFee = $this->extraPackagingFee($scope, $state);

        $total = $discountedSubtotal + ($taxIncluded ? 0 : $tax) + $deliveryFee + $additionalCharge + $dmTip + $packagingFee;
        $cashback = $this->computeCashback($customerId, $total);

        return CheckoutQuoteDTO::fromArray([
            'itemPrice'        => $this->roundMoney($itemPrice),
            'itemDiscount'     => $this->roundMoney($itemDiscount),
            'couponCode'       => $couponCode,
            'couponTitle'      => $couponTitle,
            'couponDiscount'   => $this->roundMoney($couponDiscount),
            'couponError'      => $couponError,
            'tax'              => $this->roundMoney($tax),
            'taxEnabled'       => true,
            'taxIncluded'      => $taxIncluded,
            'deliveryFee'      => $this->roundMoney($deliveryFee),
            'deliveryFeeNote'  => $deliveryFeeNote,
            'additionalCharge' => $this->roundMoney($additionalCharge),
            'dmTip'            => $this->roundMoney($dmTip),
            'extraPackaging'   => $this->roundMoney($packagingFee),
            'distance'         => $distanceKm,
            'distanceLabel'    => $distanceKm > 0 ? Helpers::formatDistance($distanceKm) : null,
            'total'            => $this->roundMoney($total),
            'cashback'         => $cashback,
            'freeDelivery'     => $freeDelivery,
            'surge'            => $surge,
            'proDiscount'        => $this->roundMoney($proDiscount),
            'proDeliverySavings' => $this->roundMoney($proDeliverySavings),
            'referralDiscount'   => $this->roundMoney($referralDiscount),
            'eligibleAmount'     => $this->roundMoney($discountedSubtotal),
            'itemDiscountNote'   => self::discountNoteFor($pricing['source']),
        ]);
    }

    /**
     * What this basket is worth before any discount, and what comes off it.
     *
     * Priced from the Food models rather than from the cart rows' stored prices, and shaped
     * to match makeOrderDetails() + applyStoreWideDiscount() - the pipeline that will charge
     * the order - so the summary cannot promise a figure the order then refuses:
     *
     *   line worth   = (item price + variation uplift) x quantity + its add-ons
     *   store wide   = checkAdminDiscount() over the whole basket, honouring the rate's
     *                  minimum purchase and cap exactly as the order does
     *   otherwise    = each line's own discount, which a store wide rate would have displaced
     *
     * `source` names which rate applied, so the summary can say so; null when nothing did.
     *
     * @return array{gross: float, discount: float, source: ?string}
     */
    private function cartPricing(array $items, ?StorefrontScope $scope): array
    {
        $empty = ['gross' => 0.0, 'discount' => 0.0, 'source' => null];

        try {
            // A campaign item is sold at its campaign price and no store wide rate reaches it,
            // so those rows are outside this entirely.
            $foodIds = [];
            foreach ($items as $row) {
                if (($row['item_type'] ?? null) === 'Item' && ! empty($row['item_id'])) {
                    $foodIds[] = (int) $row['item_id'];
                }
            }

            if (! $foodIds) {
                return $empty;
            }

            $foods = Food::with('restaurant.discount')
                ->whereIn('id', array_unique($foodIds))
                ->get()
                ->keyBy('id');

            $lines = [];
            $gross = 0.0;

            foreach ($items as $row) {
                $food = $foods->get((int) ($row['item_id'] ?? 0));
                $qty  = (int) ($row['quantity'] ?? 0);

                if (! $food || $qty <= 0) {
                    continue;
                }

                $variations = \is_array($food->variations)
                    ? $food->variations
                    : (\json_decode((string) $food->variations, true) ?: []);

                $base = (float) $food->price + (float) Helpers::variation_price(
                    $variations,
                    $row['variation'] ?? []
                );

                // Add-ons count towards the basket and are discounted with the food they were
                // ordered with - bogoLineWorth() treats them the same way, and the two figures
                // have to agree or the minimum purchase is judged against a different basket.
                // Coerced rather than trusted: these arrive decoded from the cart row, and a
                // row written with a double-encoded "[]" decodes to a string. Left as one it
                // reached whereIn() and threw, and the catch below then priced the whole
                // basket at nothing - a visible zero on a checkout nobody could explain.
                $addOnIds  = \is_array($row['add_on_ids'] ?? null) ? $row['add_on_ids'] : [];
                $addOnQtys = \is_array($row['add_on_qtys'] ?? null) ? $row['add_on_qtys'] : [];
                $addOns    = 0.0;

                if ($addOnIds) {
                    $addOns = (float) (Helpers::calculate_addon_price(
                        AddOn::whereIn('id', $addOnIds)->get(),
                        $addOnQtys
                    )['total_add_on_price'] ?? 0);
                }

                $gross += $base * $qty + $addOns;
                $lines[] = ['food' => $food, 'base' => $base, 'qty' => $qty];
            }

            if (! $lines) {
                return $empty;
            }

            $restaurant = $lines[0]['food']->restaurant;
            $rate = Helpers::get_effective_restaurant_discount($restaurant);

            if ($rate) {
                $discount = (float) Helpers::checkAdminDiscount(
                    price: $gross,
                    discount: $rate['discount'],
                    max_discount: $rate['max_discount'],
                    min_purchase: $rate['min_purchase'],
                );

                return [
                    'gross' => $gross,
                    'discount' => $discount,
                    // Below the minimum purchase nothing came off, so there is no rate to name.
                    'source' => $discount > 0
                        ? (Helpers::get_running_happy_hour($restaurant) ? 'happy_hour' : 'restaurant_discount')
                        : null,
                ];
            }

            // No store wide rate in force, so each line keeps whatever discount it carries of
            // its own - the one case the item cards still show.
            $discount = 0.0;
            foreach ($lines as $line) {
                $calc = Helpers::food_discount_calculate($line['food'], $line['base'], $line['food']->restaurant, false);
                $discount += (float) ($calc['discount_amount'] ?? 0) * $line['qty'];
            }

            return ['gross' => $gross, 'discount' => $discount, 'source' => null];
        } catch (\Throwable $e) {
            // A checkout must never fail over a pricing lookup.
            \info(['storefront_cart_pricing' => $e->getMessage()]);

            return $empty;
        }
    }

    /**
     * The one wording per rate, so the checkout summary and the order details page say the
     * same thing about the same discount - one reads a live basket, the other a stamped
     * order - and they must not drift into two different sentences.
     */
    public static function discountNoteFor(?string $source): ?string
    {
        return match ($source) {
            'happy_hour' => translate('messages.Happy_Hour_Discount'),
            'restaurant_discount' => translate('messages.Restaurant_Discount'),
            default => null,
        };
    }

    private function computeDelivery(?StorefrontScope $scope, array $state, float $payable, bool $couponFreeDelivery): array
    {
        $restaurantId = $scope?->subTenantId;
        if (!$restaurantId) {
            return [0.0, __('messages.service_not_available_in_this_area'), null, ['active' => false, 'reason' => null], self::NO_SURGE];
        }

        [$destLat, $destLng] = $this->resolveDestinationCoords($state);
        if ($destLat === null || $destLng === null) {
            return [0.0, 'Select a delivery address to see the delivery fee.', null, ['active' => false, 'reason' => null], self::NO_SURGE];
        }

        $restaurant = Restaurant::with(['zone', 'restaurant_sub'])->find($restaurantId);
        if (!$restaurant) {
            return [0.0, null, null, ['active' => false, 'reason' => null], self::NO_SURGE];
        }

        // Checked before the trip is measured: an address outside the zone is not going to
        // be priced, and measuring it would spend a Distance Matrix call to say so.
        if (!$this->pointInRestaurantZone($restaurant, $destLat, $destLng)) {
            return [0.0, translate('messages.out_of_coverage'), null, ['active' => false, 'reason' => null], self::NO_SURGE];
        }

        // The same measurement POS prices from - Google's walking route, cached on the
        // coordinates so both surfaces share one answer. This was a straight-line haversine,
        // which is never longer than the route and was quoting the shopper a lower fee than
        // the counter did for the identical trip.
        $distanceKm = Helpers::routeDistanceKm(
            $restaurant->latitude,
            $restaurant->longitude,
            $destLat,
            $destLng,
        );

        $zoneSurge = $this->resolveZoneSurge($restaurant, $state['scheduleAt'] ?? null);

        [$areaId, $zipCodeId] = $this->validatedCoveragePick($restaurant, $state);

        if ($couponFreeDelivery) {
            return [0.0, 'Coupon includes free delivery.', $distanceKm, ['active' => true, 'reason' => 'coupon'], $this->mapSurge($zoneSurge, true)];
        }

        $api    = app(OrderController::class);
        $calc   = $api->calculateDeliveryCharge(
            $distanceKm,
            $restaurant,
            $payable,
            true,
            $state['scheduleAt'] ?? null,
            $areaId,
            $zipCodeId,
        );
        $fee    = (float) ($calc['delivery_charge'] ?? 0);
        $freeBy = $calc['free_delivery_by'] ?? null;

        if ($freeBy) {
            return [0.0, 'Free delivery.', $distanceKm, ['active' => true, 'reason' => $freeBy], $this->mapSurge($zoneSurge, true)];
        }

        // The counter's wording, verbatim: whatever inflated this fee above the rate card
        // - the vehicle category charge, the zone's surge and the amount it added - is
        // named the same way in both places, because it is one fee and one explanation.
        $note = $api->deliveryChargeNote($calc, $fee);

        // Nothing inflated it, so say what it is instead of leaving the customer an empty
        // tooltip. This is the storefront's own fallback and predates the shared note -
        // the counter shows no icon at all here, because an operator reading their own
        // rate card does not need to be told the fee is distance times rate.
        if (!$note) {
            $note = $fee > 0
                ? sprintf('%s × delivery rate', Helpers::formatDistance($distanceKm))
                : null;
        }

        return [$fee, $note, $distanceKm, ['active' => false, 'reason' => null], $this->mapSurge($zoneSurge)];
    }

    private function resolveZoneSurge(Restaurant $restaurant, $scheduleAt): array
    {
        if ((int) $restaurant->sub_self_delivery === 1) {
            return \App\CentralLogics\SurgePriceLogic::empty();
        }

        return \App\CentralLogics\SurgePriceLogic::resolve(
            $restaurant->zone_id,
            Helpers::parse_schedule_at($scheduleAt) ?? now(),
        );
    }

    private function mapSurge(array $surge, bool $hideNote = false): array
    {
        $amount = (float) ($surge['price'] ?? 0);

        if ($amount <= 0) {
            return self::NO_SURGE;
        }

        return [
            'active' => true,
            'title'  => (string) ($surge['title'] ?? ''),
            'note'   => $hideNote ? null : \App\CentralLogics\SurgePriceLogic::customerNote($surge),
            'amount' => $amount,
            'type'   => (string) ($surge['price_type'] ?? 'amount'),
        ];
    }

    /** No surge in force. One constant so every branch answers the same shape. */
    private const NO_SURGE = ['active' => false, 'title' => '', 'note' => null, 'amount' => 0.0, 'type' => 'amount'];

    private function pointInRestaurantZone(Restaurant $restaurant, float $lat, float $lng): bool
    {
        if (!$restaurant->zone_id) {
            return false;
        }

        return Zone::query()
            ->where('id', $restaurant->zone_id)
            ->whereContains('coordinates', new \MatanYadaev\EloquentSpatial\Objects\Point($lat, $lng, POINT_SRID))
            ->exists();
    }

    private function validatedCoveragePick(Restaurant $restaurant, array $state): array
    {
        $areaId    = $state['areaId'] ?? null;
        $zipCodeId = $state['zipCodeId'] ?? null;

        if ($areaId && !Helpers::coverageBelongsToZone($restaurant->zone_id, (int) $areaId, null)) {
            $areaId = null;
        }

        if ($zipCodeId && !Helpers::coverageBelongsToZone($restaurant->zone_id, null, (int) $zipCodeId)) {
            $zipCodeId = null;
        }

        return [$areaId ? (int) $areaId : null, $zipCodeId ? (int) $zipCodeId : null];
    }

    private function resolveDestinationCoords(array $state): array
    {
        if (!empty($state['lat']) && !empty($state['lng'])) {
            return [(float) $state['lat'], (float) $state['lng']];
        }
        if (!empty($state['addressId'])) {
            $row = \App\Models\CustomerAddress::query()->find((int) $state['addressId']);
            if ($row) {
                return [(float) $row->latitude, (float) $row->longitude];
            }
        }
        $loc = $this->location->current();
        return [$loc['lat'] ?? null, $loc['lng'] ?? null];
    }


    private function extraPackagingFee(?StorefrontScope $scope, array $state): float
    {
        if (!$scope?->subTenantId) {
            return 0.0;
        }
        if ((int) (Helpers::get_business_settings('extra_packaging_charge') ?? 0) !== 1) {
            return 0.0;
        }

        $cfg = Restaurant::find($scope->subTenantId)?->restaurant_config;
        if (!$cfg || (int) ($cfg->is_extra_packaging_active ?? 0) !== 1) {
            return 0.0;
        }

        $required = (int) ($cfg->extra_packaging_status ?? 0) === 1;
        if (!$required && empty($state['extraPackaging'])) {
            return 0.0;
        }

        return (float) ($cfg->extra_packaging_amount ?? 0);
    }

    private function computeTaxBreakdown(?StorefrontScope $scope, ?int $customerId, array $state, float $discountedSubtotal): array
    {
        $empty = ['tax' => 0.0, 'totalPrice' => null, 'proDiscount' => 0.0, 'referralDiscount' => 0.0];

        if (!$scope?->subTenantId || $discountedSubtotal <= 0) {
            return $empty;
        }

        $user    = $customerId ? User::query()->find($customerId) : null;
        $guestId = $user ? null : $this->context->getGuestId();

        if (!$user && !$guestId) {
            return $empty;
        }

        try {
            $request = Request::create('', 'POST', [
                'restaurant_id'          => $scope->subTenantId,
                'order_type'             => ($state['deliveryType'] ?? 'delivery') === 'pickup' ? 'take_away' : 'delivery',
                'order_amount'           => $discountedSubtotal,
                'coupon_code'            => $state['couponCode'] ?? null,
                'extra_packaging_amount' => $this->extraPackagingFee($scope, $state) > 0 ? 1 : 0,
                'is_buy_now'             => 0,
                'guest_id'               => $guestId,
            ]);

            if ($user) {
                $request->setUserResolver(fn () => $user);
                $request->merge(['user' => $user]);
            }

            $response = app(OrderController::class)->getTaxFromCart($request);
            $payload  = $response->getData(true);

            if (isset($payload['tax_amount'])) {
                return [
                    'tax'              => (float) $payload['tax_amount'],
                    'totalPrice'       => isset($payload['total_price']) ? (float) $payload['total_price'] : null,
                    'proDiscount'      => (float) ($payload['pro_discount'] ?? 0),
                    'referralDiscount' => (float) ($payload['ref_bonus_amount'] ?? 0),
                ];
            }

            \info('Builder quote tax: trait returned no tax_amount', [
                'restaurant_id' => $scope->subTenantId,
                'user_id'       => $customerId,
                'guest_id'      => $guestId,
                'payload'       => $payload,
            ]);
        } catch (\Throwable $e) {
            \info('Builder quote tax: trait threw — quoting zero tax. ' . $e->getMessage());
        }

        return $empty;
    }

    private function applyProDelivery(
        ?StorefrontScope $scope,
        ?int $customerId,
        float $deliveryFee,
        float $payable,
        array $freeDelivery,
    ): array {
        if (!$customerId) {
            return [$deliveryFee, 0.0, $freeDelivery];
        }

        $restaurant = $scope?->subTenantId ? Restaurant::with('zone')->find($scope->subTenantId) : null;

        if (!$restaurant) {
            return [$deliveryFee, 0.0, $freeDelivery];
        }

        $pro = new class { use \App\Traits\ManagesProCustomerSubscription; };

        $result = $pro->applyProCustomerDeliveryFee(
            $pro->getProCustomerOffer(userId: $customerId),
            $deliveryFee,
            $payable,
            $freeDelivery['reason'] ?? null,
            Helpers::minimumDeliveryCharge($restaurant),
        );

        $fee     = (float) $result['delivery_charge'];
        $savings = (float) $result['savings'];

        if ($savings > 0 && $fee <= 0) {
            $freeDelivery = ['active' => true, 'reason' => 'pro_customer'];
        }

        return [$fee, $savings, $freeDelivery];
    }

    private function isTaxIncluded(): bool
    {
        if (!\addon_published_status('TaxModule')) {
            return false;
        }
        $sys = \Modules\TaxModule\Entities\SystemTaxSetup::query()
            ->where('is_active', 1)->where('is_default', 1)->first();
        return (int) ($sys?->is_included ?? 0) === 1;
    }

    private function computeCashback(?int $customerId, float $orderTotal): ?array
    {
        if (!$customerId || $orderTotal <= 0 || ! config('builder.wallet_features_enabled', true)) {
            return null;
        }
        try {
            $r = Helpers::getCalculatedCashBackAmount($orderTotal, $customerId);
            if (!is_array($r) || empty($r)) {
                return null;
            }
            return [
                'eligible'    => (float) ($r['cashback_amount'] ?? 0) > 0,
                'amount'      => (float) ($r['cashback_amount'] ?? 0),
                'minPurchase' => (float) ($r['min_purchase'] ?? 0),
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    private function roundMoney(float $value): float
    {
        return round($value, (int) (config('round_up_to_digit') ?? 2));
    }

    private const DIGITAL_GATEWAYS = [
        'paypal', 'stripe', 'razor_pay', 'senang_pay', 'paystack', 'flutterwave',
        'ssl_commerz', 'paytabs', 'paytm', 'paymob_accept', 'liqpay', 'bkash', 'mercadopago',
    ];

    public function placeOrder(?StorefrontScope $scope, ?int $customerId, array $state): array
    {
        if (! config('builder.wallet_features_enabled', true)) {
            $rawMethod = (string) ($state['paymentMethod'] ?? '');
            if ($rawMethod === 'wallet' || !empty($state['partialPayment'])) {
                return ['success' => false, 'errors' => [['code' => 'wallet_disabled', 'message' => 'Wallet and partial payment are not available. Please pick another payment method.']]];
            }
        }

        $user    = $customerId ? User::query()->find($customerId) : null;
        $guestId = $user ? null : $this->context->getGuestId();

        if (!$user && !$guestId) {
            return ['success' => false, 'errors' => [['code' => 'auth', 'message' => 'Could not identify the current shopper. Please refresh and try again.']]];
        }

        if (!$user) {
            if (!Helpers::get_mail_status('guest_checkout_status')) {
                return ['success' => false, 'errors' => [['code' => 'is_guest', 'message' => 'Guest checkout is currently disabled. Please sign in to place an order.']]];
            }
            $missing = [];
            if (empty($state['contactName']))  $missing[] = 'name';
            if (empty($state['contactPhone'])) $missing[] = 'phone';
            if ($missing) {
                return ['success' => false, 'errors' => [['code' => 'contact', 'message' => 'Please provide your ' . implode(', ', $missing) . ' to continue.']]];
            }
        }

        $quote = $this->quote($scope, $customerId, $state)->toArray();

        $deliveryType = $state['deliveryType'] ?? 'delivery';
        $orderType    = $deliveryType === 'pickup' ? 'take_away' : 'delivery';
        $scheduleAt   = $deliveryType === 'schedule' ? ($state['scheduleAt'] ?? null) : null;
        if ($deliveryType === 'schedule') {
            $orderType = 'delivery';
        }
        if (!$user) {
            $scheduleAt = null;
        }

        [$lat, $lng] = $this->resolveDestinationCoords($state);
        $address = (string) ($state['address'] ?? '');
        if (!$address && !empty($state['addressId'])) {
            $row = \App\Models\CustomerAddress::query()->find((int) $state['addressId']);
            if ($row) {
                $address = (string) $row->address;
            }
        }
        if ($orderType === 'take_away' && $scope?->subTenantId) {
            $restaurant = Restaurant::query()->select('latitude', 'longitude', 'address')->find($scope->subTenantId);
            if ($restaurant && $restaurant->latitude && $restaurant->longitude) {
                $lat = (float) $restaurant->latitude;
                $lng = (float) $restaurant->longitude;
                if (!$address) {
                    $address = (string) ($restaurant->address ?? '');
                }
            }
        }

        $rawMethod  = $state['paymentMethod'] ?? 'cash_on_delivery';
        $hostMethod = $this->isDigitalGateway($rawMethod) ? 'digital_payment' : $rawMethod;

        $body = [
            'order_amount'           => $quote['total'],
            'payment_method'         => $hostMethod,
            'order_type'             => $orderType,
            'restaurant_id'          => $scope?->subTenantId,
            'distance'               => $quote['distance'] ?? 0,
            'address'                => $address,
            'address_type'           => $state['addressType'] ?? 'Delivery',
            'latitude'               => $lat,
            'longitude'              => $lng,
            'house'                  => $state['house'] ?? null,
            'floor'                  => $state['floor'] ?? null,
            'road'                   => $state['road'] ?? null,
            'coupon_code'            => $quote['couponCode'],
            'contact_person_name'    => $state['contactName']  ?? ($user ? trim(($user->f_name ?? '') . ' ' . ($user->l_name ?? '')) : null),
            'contact_person_number'  => $this->normalizePhone($state['contactPhone'] ?? $user?->phone),
            'contact_person_email'   => $state['contactEmail'] ?? $user?->email,
            'dm_tips'                => $quote['dmTip'],
            'extra_packaging_amount' => $this->extraPackagingFee($scope, $state) > 0 ? 1 : 0,
            'unavailable_item_note'  => $state['unavailableAction'] ?? null,
            'delivery_instruction'   => $state['instructions'] ?? null,
            'order_note'             => $state['orderNote'] ?? null,
            'bring_change_amount'    => $state['bringChange'] ?? 0,
            'schedule_at'            => $scheduleAt,
            'partial_payment'        => !empty($state['partialPayment']) ? 1 : 0,
            'guest_id'               => $user ? null : (int) $guestId,
            'area_id'                => $state['areaId'] ?? null,
            'zip_code_id'            => $state['zipCodeId'] ?? null,
        ];

        $request = Request::create('', 'POST', $body);
        if ($user) {
            $request->setUserResolver(fn () => $user);
            $request->merge(['user' => $user]);
        }

        try {
            $response = app(OrderController::class)->place_order($request);
        } catch (\Throwable $e) {
            \info('Builder placeOrder: controller threw — ' . $e->getMessage());
            return ['success' => false, 'errors' => [['code' => 'unknown', 'message' => 'Failed to place order.']]];
        }

        $payload = $response->getData(true);
        $status  = $response->getStatusCode();

        if ($status === 200) {
            $orderId = (int) ($payload['order_id'] ?? 0);

            if (($state['paymentMethod'] ?? null) === 'offline_payment') {
                $regError = $this->registerOfflinePayment($orderId, $state);
                if ($regError) {
                    return ['success' => false, 'errors' => [['code' => 'offline_payment', 'message' => $regError]]];
                }
            }

            return [
                'success'         => true,
                'orderId'         => $orderId,
                'paymentRedirect' => $this->paymentRedirectFor(
                    $state['paymentMethod'] ?? null,
                    $orderId,
                    $user?->id ?? (int) $guestId,
                ),
                'message'         => (string) ($payload['message'] ?? 'Order placed successfully'),
                'total'           => $this->placedOrderTotal($orderId) ?? (float) $quote['total'],
                'contactPhone'    => $this->normalizePhone($state['contactPhone'] ?? null),
            ];
        }

        $errors = $payload['errors'] ?? [['code' => 'unknown', 'message' => 'Failed to place order.']];
        if (!is_array($errors[0] ?? null)) {
            $errors = [['code' => 'unknown', 'message' => is_string($errors) ? $errors : 'Failed to place order.']];
        }
        return ['success' => false, 'errors' => $errors];
    }

    private function placedOrderTotal(int $orderId): ?float
    {
        if ($orderId <= 0) {
            return null;
        }

        $amount = \App\Models\Order::query()->whereKey($orderId)->value('order_amount');

        return $amount === null ? null : (float) $amount;
    }

    private function isDigitalGateway(?string $key): bool
    {
        return $key && in_array($key, self::DIGITAL_GATEWAYS, true);
    }

    private function paymentRedirectFor(?string $method, int $orderId, int $userId): ?string
    {
        if (!$this->isDigitalGateway($method)) {
            return null;
        }
        try {
            $callback = url(route('storefront.payment_callback', ['orderId' => $orderId], false));
            return route('payment-mobile', [
                'order_id'         => $orderId,
                'customer_id'      => $userId,
                'payment_method'   => $method,
                'payment_platform' => 'web',
                'callback'         => $callback,
            ]);
        } catch (\Throwable) {
            return null;
        }
    }

    private function registerOfflinePayment(int $orderId, array $state): ?string
    {
        $info     = $state['offlinePayment'] ?? [];
        $methodId = (int) ($info['methodId'] ?? 0);

        $method = OfflinePaymentMethod::query()->where(['id' => $methodId, 'status' => 1])->first();
        if (!$method) {
            return 'Selected offline payment method is no longer available.';
        }

        $submitted = (array) ($info['fields'] ?? []);
        $fields    = array_column($method->method_informations ?? [], 'customer_input');

        $paymentInfo = [
            'method_id'   => $methodId,
            'method_name' => $method->method_name,
        ];
        foreach ($fields as $field) {
            if (array_key_exists($field, $submitted)) {
                $paymentInfo[$field] = $submitted[$field];
            }
        }

        try {
            $row = \App\Models\OfflinePayments::firstOrNew(['order_id' => $orderId]);
            $row->payment_info  = json_encode($paymentInfo);
            $row->customer_note = $info['customerNote'] ?? null;
            $row->method_fields = json_encode($method->method_fields);
            $row->save();

            \App\Models\Order::where('id', $orderId)->update(['order_status' => 'pending', 'payment_method' => 'offline_payment']);
            return null;
        } catch (\Throwable $e) {
            return 'Could not register offline payment details.';
        }
    }

    private function normalizePhone(?string $phone): ?string
    {
        $trimmed = $phone === null ? null : trim($phone);
        if ($trimmed === null || $trimmed === '') {
            return null;
        }
        return str_starts_with($trimmed, '+') ? $trimmed : '+' . ltrim($trimmed, '+ ');
    }

    public function shippingMethods(?StorefrontScope $scope, ?int $customerId): array
    {
        return ['enabled' => false, 'groups' => []];
    }

    public function selectShippingMethod(?StorefrontScope $scope, ?int $customerId, string $cartGroupId, int $shippingMethodId): array
    {
        return ['success' => false, 'error' => 'Shipping method selection is not available.'];
    }

    public function resolveDigitalPaymentReturn(array $query): array
    {
        return ['orderId' => null, 'phone' => null];
    }
}
