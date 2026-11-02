<?php

namespace App\Builder;

use App\CentralLogics\CouponLogic;
use App\Models\Coupon;
use App\Models\Restaurant;
use App\Traits\ManagesProCustomerSubscription;
use Illuminate\Support\Carbon;
use Modules\Builder\Contracts\CouponProvider as CouponProviderContract;
use Modules\Builder\ValueObjects\StorefrontScope;

class CouponProvider implements CouponProviderContract
{
    use ManagesProCustomerSubscription;

    public function forCustomer(?int $customerId, ?int $storeId): array
    {
        $zoneIds = [];
        $restaurantId = $storeId !== null ? (int) $storeId : null;
        if ($restaurantId) {
            $zone = Restaurant::query()->whereKey($restaurantId)->value('zone_id');
            if ($zone !== null) {
                $zoneIds = [(int) $zone];
            }
        }

        $proOffer = $this->getProCustomerOffer(userId: $customerId);
        $proCouponEligible = ($proOffer['status'] ?? false)
            && ($proOffer['benefit']['type'] ?? null) === 'coupon';

        $coupons = Coupon::with('restaurant:id,name')
            ->active()
            ->valid()
            ->when($restaurantId !== null, function ($query) use ($restaurantId) {
                $query->where(function ($q) use ($restaurantId) {
                    $q->where('restaurant_id', $restaurantId)
                        ->orWhere(function ($inner) use ($restaurantId) {
                            $inner->where('coupon_type', 'restaurant_wise')
                                ->whereJsonContains('data', $restaurantId);
                        });
                });
            })
            ->when(!$proCouponEligible, function ($query) {
                $query->where('coupon_type', '!=', 'pro_customer');
            })
            ->get();

        $available = [];

        foreach ($coupons as $coupon) {
            if (!$this->isVisible($coupon, $customerId, $zoneIds, $restaurantId, $proCouponEligible)) {
                continue;
            }

            $status = CouponLogic::is_valide(
                $coupon,
                $customerId,
                $coupon->restaurant_id,
                $restaurantId,
                null,
            );

            if ($status === 200) {
                $available[] = $this->toDto($coupon);
            }
        }

        return $available;
    }

    public function validate(string $code, ?int $customerId, ?StorefrontScope $scope, float $cartTotal): array
    {
        $code = \trim($code);
        if ($code === '') {
            return ['ok' => false, 'error' => 'Coupon code is required.'];
        }

        $coupon = Coupon::active()->where('code', $code)->first();
        if (!$coupon) {
            return ['ok' => false, 'error' => \translate('messages.not_found')];
        }

        $restaurantId = $scope?->subTenantId;

        $status = $customerId !== null
            ? CouponLogic::is_valide(
                $coupon,
                $customerId,
                $restaurantId,
                $restaurantId,
                $cartTotal,
            )
            : CouponLogic::is_valid_for_guest($coupon, $restaurantId);

        if ($status !== 200) {
            return ['ok' => false, 'error' => $this->statusError($status)];
        }

        $couponType = (string) $coupon->coupon_type;
        $freeDelivery = $couponType === 'free_delivery';

        $discount = $freeDelivery
            ? 0.0
            : (float) CouponLogic::get_discount($coupon, $cartTotal);

        return [
            'ok' => true,
            'code' => (string) $coupon->code,
            'title' => (string) $coupon->title,
            'discount' => $discount,
            'freeDelivery' => $freeDelivery,
            'couponType' => $couponType,
        ];
    }

    private function isVisible(
        Coupon $coupon,
        ?int $customerId,
        array $zoneIds,
        ?int $restaurantId,
        bool $proCouponEligible,
    ): bool {
        $customerIds = json_decode($coupon->customer_id, true) ?: [];
        $allowedCustomer = \in_array('all', $customerIds, true)
            || ($customerId !== null && \in_array($customerId, $customerIds));

        switch ($coupon->coupon_type) {
            case 'restaurant_wise':
                $data = json_decode($coupon->data, true) ?: [];
                $temp = Restaurant::active()
                    ->when($zoneIds !== [], fn ($q) => $q->whereIn('zone_id', $zoneIds))
                    ->whereIn('id', $data)
                    ->exists();

                return $temp && $allowedCustomer;

            case 'zone_wise':
                $data = json_decode($coupon->data, true) ?: [];
                foreach ($zoneIds as $zoneId) {
                    if (\in_array($zoneId, $data)) {
                        return true;
                    }
                }

                return false;

            case 'pro_customer':
                return $proCouponEligible;

            default:
                if ($coupon->restaurant_id) {
                    return Restaurant::active()
                        ->when($zoneIds !== [], fn ($q) => $q->whereIn('zone_id', $zoneIds))
                        ->where('id', $coupon->restaurant_id)
                        ->exists();
                }

                return $allowedCustomer;
        }
    }


    private function toDto(Coupon $coupon): array
    {
        $couponType   = (string) $coupon->coupon_type;
        $discount     = (float) $coupon->discount;
        $discountType = (string) $coupon->discount_type;
        $minPurchase  = (float) $coupon->min_purchase;
        $maxDiscount  = (float) $coupon->max_discount;

        $isFreeDelivery = $couponType === 'free_delivery';
        $benefit = $isFreeDelivery
            ? 'Free Delivery'
            : ($discountType === 'percent'
                ? $this->trimNumber($discount) . '% Off'
                : '$' . $this->trimNumber($discount) . ' Off');

        $note = $minPurchase > 0
            ? 'Min purchase $' . $this->trimNumber($minPurchase)
            : null;

        return [
            'id'            => (int) $coupon->id,
            'code'          => (string) $coupon->code,
            'title'         => (string) $coupon->title,
            'coupon_type'   => $couponType,
            'type_label'    => $this->typeLabel($couponType),
            'discount'      => $discount,
            'discount_type' => $discountType,
            'min_purchase'  => $minPurchase,
            'max_discount'  => $maxDiscount,
            'valid_from'    => $this->formatDate($coupon->start_date),
            'valid_to'      => $this->formatDate($coupon->expire_date),
            'store_name'    => $coupon->restaurant?->name,
            'benefit'       => $benefit,
            'note'          => $note,
        ];
    }

    private function typeLabel(?string $type): string
    {
        return match ($type) {
            'restaurant_wise' => 'Store Special',
            'zone_wise'       => 'Zone Discount',
            'first_order'     => 'First Order',
            'free_delivery'   => 'Free Delivery',
            'pro_customer'    => 'Pro Customer',
            default           => 'Special Offer',
        };
    }

    private function formatDate(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('M j, Y');
        } catch (\Throwable) {
            return null;
        }
    }

    private function trimNumber(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    /**
     * Maps CouponLogic::is_valide status codes to the storefront error string,
     * reproducing CouponController::apply's switch.
     */
    private function statusError(int $status): string
    {
        return match ($status) {
            406 => \translate('messages.coupon_usage_limit_over'),
            407 => \translate('messages.coupon_expire'),
            408 => \translate('messages.You_are_not_eligible_for_this_coupon'),
            409 => \translate('Minimum_purchase_amount_not_met.'),
            410 => \translate('messages.free_delivery_already_covered_by_pro'),
            default => \translate('messages.not_found'),
        };
    }
}
