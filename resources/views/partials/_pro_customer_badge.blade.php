@php
    $size = $size ?? 14;
    $position = $position ?? 'top-0';
    $style = $style ?? null;
@endphp
<img width="{{ $size }}" height="{{ $size }}" src="{{ dynamicAsset('assets/admin/img/subscription-win-badge.png') }}" alt="img" data-toggle="tooltip" title="{{ translate('messages.pro_customer') }}" class="rounded-circle position-absolute end--0 {{ $position }}"@if($style) style="{{ $style }}"@endif>
