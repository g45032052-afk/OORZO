
@if (\App\CentralLogics\Helpers::get_business_settings('verified_seller_badge') == 1 && isset( $restaurant?->restaurant_config) && $restaurant?->restaurant_config?->verified_seller)
    {{-- <img class="ml-1 w-auto h-auto object-contain" src="{{ dynamicAsset('assets/admin/img/badge-rounded-circle.svg') }}" alt=""> --}}
     <i class="tio-verified text-success" data-toggle="tooltip" data-placement="top"
        title="{{ translate('Verified Restaurant') }}"></i>
@endif
