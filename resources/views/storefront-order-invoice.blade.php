<!DOCTYPE html>
<?php
    use App\CentralLogics\Helpers;
    $lang = Helpers::system_default_language();
    $dir  = Helpers::system_default_direction();

    // Storefront invoice brands the SELLING RESTAURANT (logo, name, address,
    // phone, email) rather than the platform — the customer bought from a
    // specific restaurant. Mirrors the 6amMart storefront-order-invoice fork.
    $restaurant = $order->restaurant ?? null;
    $deliveryAddress = is_array($order->delivery_address ?? null)
        ? $order->delivery_address
        : (json_decode($order->delivery_address ?? '', true) ?: []);

    $contactName = $deliveryAddress['contact_person_name']
        ?? trim(($order->customer->f_name ?? '') . ' ' . ($order->customer->l_name ?? ''));
    $contactPhone = $deliveryAddress['contact_person_number'] ?? ($order->customer->phone ?? '');
    $deliveryLine = $deliveryAddress['address'] ?? '';

    $subTotal    = 0;
    $addOnsCost  = 0;
?>
<html lang="{{ $lang }}" dir="{{ $dir }}">
<head>
    <meta charset="UTF-8">
    <title>{{ translate('messages.Order_Invoice') }} #{{ $order->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #444; }
        .wrap { width: 100%; max-width: 720px; margin: 0 auto; padding: 24px; }
        h1, h2, h3, h4 { color: #222; margin: 0; }
        .head { text-align: center; border-bottom: 1px dashed #bbb; padding-bottom: 14px; margin-bottom: 14px; }
        .head img { max-height: 64px; margin-bottom: 8px; }
        .head .r-name { font-size: 18px; font-weight: 700; }
        .head .r-meta { font-size: 12px; color: #666; margin-top: 2px; }
        .meta-row { display: flex; justify-content: space-between; margin: 3px 0; }
        .meta-row .k { color: #777; }
        .box { border: 1px dashed #bbb; border-radius: 6px; padding: 10px 12px; margin: 12px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { text-align: left; border-bottom: 1px dashed #979797; padding: 6px 4px; font-size: 12px; }
        td { padding: 6px 4px; vertical-align: top; border-bottom: 1px solid #f0f0f0; }
        td.r, th.r { text-align: right; }
        .sub { font-size: 11px; color: #777; }
        .totals { margin-top: 10px; }
        .totals .t-row { display: flex; justify-content: space-between; padding: 2px 0; }
        .totals .t-row .lbl { color: #777; }
        .totals .grand { font-size: 16px; font-weight: 700; color: #222; border-top: 1px dashed #bbb; margin-top: 6px; padding-top: 8px; }
        .foot { text-align: center; margin-top: 18px; border-top: 1px dashed #bbb; padding-top: 12px; }
        .foot .ty { font-weight: 700; color: #222; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="head">
        @if ($restaurant?->logo_full_url)
            <img src="{{ $restaurant->logo_full_url }}" alt="{{ $restaurant->name ?? '' }}">
        @endif
        <div class="r-name">{{ $restaurant->name ?? '' }}</div>
        @if ($restaurant?->address)<div class="r-meta">{{ $restaurant->address }}</div>@endif
        @if ($restaurant?->phone)<div class="r-meta">{{ translate('phone') }}: {{ $restaurant->phone }}</div>@endif
        @if ($restaurant?->email)<div class="r-meta">{{ $restaurant->email }}</div>@endif
    </div>

    <div class="meta-row"><span class="k">{{ translate('messages.Order_ID') }}</span><span>#{{ $order->id }}</span></div>
    <div class="meta-row"><span class="k">{{ translate('messages.date') }}</span>
        <span>{{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d M Y, h:i A') }}</span></div>
    <div class="meta-row"><span class="k">{{ translate('messages.Order_Type') }}</span>
        <span>{{ $order->order_type == 'delivery' ? translate('messages.Home_Delivery') : translate(str_replace('_', ' ', $order->order_type)) }}</span></div>

    @if ($contactName || $contactPhone || $deliveryLine)
        <div class="box">
            @if ($contactName)<div><strong>{{ $contactName }}</strong></div>@endif
            @if ($contactPhone)<div>{{ $contactPhone }}</div>@endif
            @if ($deliveryLine)<div class="sub">{{ $deliveryLine }}</div>@endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width:12%">{{ translate('messages.QTY') }}</th>
                <th>{{ translate('messages.item') }}</th>
                <th class="r" style="width:26%">{{ translate('messages.price') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->details as $detail)
                @php
                    $name = json_decode($detail->food_details ?? '', true)['name']
                        ?? ($detail->campaign['title'] ?? translate('messages.item'));
                    $variations = json_decode($detail->variation ?? '', true) ?: [];
                    $addons     = json_decode($detail->add_ons ?? '', true) ?: [];
                    $lineAmount = $detail->price * $detail->quantity;
                    $subTotal  += $lineAmount;
                @endphp
                <tr>
                    <td>{{ $detail->quantity }}x</td>
                    <td>
                        {{ \Illuminate\Support\Str::limit($name, 60) }}
                        @foreach ($variations as $variation)
                            @if (isset($variation['name']) && isset($variation['values']))
                                <div class="sub"><strong>{{ $variation['name'] }}:</strong>
                                    @foreach ($variation['values'] as $value){{ $value['label'] ?? '' }}@if(!$loop->last), @endif @endforeach
                                </div>
                            @elseif (is_array($variation))
                                @foreach ($variation as $vk => $vv)
                                    @if (!is_array($vv))<div class="sub">{{ $vk }}: {{ $vv }}</div>@endif
                                @endforeach
                            @endif
                        @endforeach
                        @foreach ($addons as $addon)
                            @php($addOnsCost += ($addon['price'] ?? 0) * ($addon['quantity'] ?? 0))
                            <div class="sub">+ {{ $addon['name'] ?? '' }} ({{ $addon['quantity'] ?? 0 }} × {{ Helpers::format_currency($addon['price'] ?? 0) }})</div>
                        @endforeach
                    </td>
                    <td class="r">{{ Helpers::format_currency($lineAmount) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="t-row"><span class="lbl">{{ translate('messages.Items_Price') }}</span><span>{{ Helpers::format_currency($subTotal) }}</span></div>
        @if ($addOnsCost > 0)
            <div class="t-row"><span class="lbl">{{ translate('messages.Addon_Cost') }}</span><span>{{ Helpers::format_currency($addOnsCost) }}</span></div>
        @endif
        @if (($order->restaurant_discount_amount ?? 0) > 0)
            <div class="t-row"><span class="lbl">{{ translate('messages.discount') }}</span><span>- {{ Helpers::format_currency($order->restaurant_discount_amount) }}</span></div>
        @endif
        @if (($order->coupon_discount_amount ?? 0) > 0)
            <div class="t-row"><span class="lbl">{{ translate('messages.coupon_discount') }}</span><span>- {{ Helpers::format_currency($order->coupon_discount_amount) }}</span></div>
        @endif
        @if (($order->ref_bonus_amount ?? 0) > 0)
            <div class="t-row"><span class="lbl">{{ translate('messages.Referral_Discount') }}</span><span>- {{ Helpers::format_currency($order->ref_bonus_amount) }}</span></div>
        @endif
        @if (($order->tax_status ?? null) !== 'included' && ($order->total_tax_amount ?? 0) > 0)
            <div class="t-row"><span class="lbl">{{ translate('messages.vat/tax') }}</span><span>+ {{ Helpers::format_currency($order->total_tax_amount) }}</span></div>
        @endif
        @if (($order->dm_tips ?? 0) > 0)
            <div class="t-row"><span class="lbl">{{ translate('messages.delivery_man_tips') }}</span><span>+ {{ Helpers::format_currency($order->dm_tips) }}</span></div>
        @endif
        @if (($order->delivery_charge ?? 0) > 0)
            <div class="t-row"><span class="lbl">{{ translate('messages.delivery_charge') }}</span><span>+ {{ Helpers::format_currency($order->delivery_charge) }}</span></div>
        @endif
        @if (($order->additional_charge ?? 0) > 0)
            <div class="t-row"><span class="lbl">{{ Helpers::get_business_data('additional_charge_name') ?? translate('messages.additional_charge') }}</span><span>+ {{ Helpers::format_currency($order->additional_charge) }}</span></div>
        @endif
        @if (($order->extra_packaging_amount ?? 0) > 0)
            <div class="t-row"><span class="lbl">{{ translate('messages.Extra_Packaging_Amount') }}</span><span>+ {{ Helpers::format_currency($order->extra_packaging_amount) }}</span></div>
        @endif
        <div class="t-row grand"><span>{{ translate('messages.total') }}</span><span>{{ Helpers::format_currency($order->order_amount) }}</span></div>
    </div>

    <div class="foot">
        <div class="ty">{{ translate('messages.THANK_YOU') }}</div>
        <div class="sub">{{ translate('for_ordering_food_from') }} {{ Helpers::get_business_settings('business_name', false) }}</div>
    </div>
</div>
</body>
</html>
