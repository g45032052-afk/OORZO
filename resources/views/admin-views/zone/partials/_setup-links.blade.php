{{-- The shortcuts to whatever a zone is still missing. Shown both in the list
     popover and in the settings drawer, so they live in one place. --}}
@unless ($has_delivery_charge_setup)
    <a href="{{ route('admin.delivery-rule.create', ['zone_id' => $zone['id']]) }}"
        class="font-semibold text-info text-underline d-block">
        {{ translate('messages.Add_Delivery_Charge_Setup') }}
    </a>
@endunless
@unless ($has_eta_setup)
    <a href="{{ route('admin.eta-configuration.create', ['zone_id' => $zone['id']]) }}"
        class="font-semibold text-info text-underline d-block">
        {{ translate('messages.Add_ETA_Configuration') }}
    </a>
@endunless
