{{--
  Renders the currently-enabled pro-customer benefits inside the plan modal.
  Required: $benefits — array keyed by data-setting key from `pro_customer_benefits`.
  At most one of discount / delivery_fee / coupon is enabled at a time.
--}}
@php
    $discountOn = (int) ($benefits['discount_status'] ?? 0) === 1;
    $deliveryOn = (int) ($benefits['delivery_fee_status'] ?? 0) === 1;
    $couponOn   = (int) ($benefits['coupon_status'] ?? 0) === 1;

    $trim = fn($v) => rtrim(rtrim((string) $v, '0'), '.');

    $items = [];

    if ($discountOn) {
        $pct = $benefits['discount_percentage'] ?? null;
        $maxAmt = $benefits['discount_max_amount'] ?? null;
        $minOrderOn = (int) ($benefits['discount_min_order_status'] ?? 0) === 1;
        $minOrderAmt = $benefits['discount_min_order_amount'] ?? null;

        $title = $pct
            ? translate('messages.Up_to') . ' ' . $trim($pct) . '% ' . translate('messages.off_on_all_orders')
            : translate('messages.Discount_on_every_order');

        $details = [];
        if ($maxAmt) {
            $details[] = translate('messages.Max_discount') . ': ' . \App\CentralLogics\Helpers::format_currency((float) $maxAmt);
        }
        if ($minOrderOn && $minOrderAmt) {
            $details[] = translate('messages.On_orders_above') . ' ' . \App\CentralLogics\Helpers::format_currency((float) $minOrderAmt);
        }

        $items[] = [
            'title' => $title,
            'subtitle' => $details
                ? implode(' • ', $details)
                : translate('messages.Applied_automatically_at_checkout'),
        ];
    }

    if ($deliveryOn) {
        $offerType = $benefits['delivery_fee_offer_type'] ?? 'full_free';

        if ($offerType === 'partial_free') {
            $deliveryPct = $benefits['delivery_charge_discount'] ?? null;
            $items[] = [
                'title' => $deliveryPct
                    ? $trim($deliveryPct) . '% ' . translate('messages.off_on_delivery_charge')
                    : translate('messages.Discount_on_delivery_charge'),
                'subtitle' => translate('messages.Reduced_delivery_fee_on_every_order'),
            ];
        } else {
            $minOn = (int) ($benefits['delivery_fee_min_order_status'] ?? 0) === 1;
            $minAmt = $benefits['delivery_fee_min_order_amount'] ?? null;
            $items[] = [
                'title' => translate('messages.Free_Delivery'),
                'subtitle' => ($minOn && $minAmt)
                    ? translate('messages.On_orders_above') . ' ' . \App\CentralLogics\Helpers::format_currency((float) $minAmt)
                    : translate('messages.On_every_order'),
            ];
        }
    }

    if ($couponOn) {
        $items[] = [
            'title' => translate('messages.Exclusive_Coupons'),
            'subtitle' => translate('messages.Pro_only_deals_and_early_access'),
        ];
    }
@endphp

@if (count($items))
    <div class="d-flex flex-column gap-20px p-20">
        @foreach ($items as $item)
            <div class="d-flex gap-2">
                <div class="btn-circle bg-white" style="--size: 20px">
                    <i class="tio-done text-success fs-14 ml-2px"></i>
                </div>
                <div class="cont">
                    <h3 class="mb-1 fs-16 fw-400 lh-1">{{ $item['title'] }}</h3>
                    <p class="mb-0 fs-12">{{ $item['subtitle'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
@endif
