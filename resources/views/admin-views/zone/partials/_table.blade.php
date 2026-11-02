
                                @foreach ($zones as $key => $zone)
                                    {{-- A zone is only effective once an active delivery rule prices it,
                                         so an unpriced one is flagged here and cannot be switched on. --}}
                                    @php($has_delivery_charge_setup = ($zone->active_delivery_rules_count ?? 0) > 0)
                                    @php($has_eta_setup = ($zone->active_eta_count ?? 0) > 0)
                                    {{-- A zone serves customers only once it can price a delivery
                                         and quote a time for it. --}}
                                    @php($zone_ready = $has_delivery_charge_setup && $has_eta_setup)
                                    {{-- Named rather than counted: an admin who has already done one of
                                         the two should not be told to do it again. --}}
                                    @php($zone_setup_notice = match (true) {
                                        $has_eta_setup => translate('messages.The_Business_Zone_will_NOT_work_until_you_add_a_delivery_charge_setup_for_it.'),
                                        $has_delivery_charge_setup => translate('messages.The_Business_Zone_will_NOT_work_until_you_add_an_ETA_configuration_for_it.'),
                                        default => translate('messages.The_Business_Zone_will_NOT_work_until_you_add_both_a_delivery_charge_setup_and_an_ETA_configuration_for_it.'),
                                    })
                                    <tr>
                                        <td>{{ $key + $zones?->firstItem() }}</td>
                                        <td class="text-center">
                                            <span class="move-left">
                                                {{ $zone->id }}
                                            </span>
                                        </td>
                                        <td class="pl-5">
                                            <span class="d-block font-size-sm text-body">
                                                {{ $zone['name'] }}
                                            </span>
                                        </td>
                                        <td class="pl-5">
                                            <span class="d-block font-size-sm text-body">
                                                {{ $zone['display_name'] }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="move-left">
                                                {{ $zone->restaurants_count }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="move-left">
                                                {{ $zone->deliverymen_count }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if ($zone->is_default)
                                                <button
                                                    class="btn btn-sm btn-ghost-success">{{ translate('messages.default_zone') }}
                                                    <span data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ translate('This zone is set as the default for customers who visit the app or website without choosing a location.') }}"
                                                        class="input-label-secondary text-success"><i class="tio-info text-success"></i></span>

                                                </button>
                                            @else
                                                <a href="{{ route('admin.zone.defaultStatus', ['id' => $zone['id']]) }}" class="btn border rounded py-2 px-3">{{ translate('messages.make_default') }}</a>
                                            @endif
                                        </td>
                                        <td>
                                            @if (!$zone_ready && !$zone->status)
                                                {{-- Clickable on purpose: a disabled switch says only that
                                                     it cannot be used, the modal says what is missing. --}}
                                                <label class="toggle-switch toggle-switch-sm zone-setup-required"
                                                    data-zone="{{ $zone['id'] }}"
                                                    data-has-rule="{{ (int) $has_delivery_charge_setup }}"
                                                    data-has-eta="{{ (int) $has_eta_setup }}">
                                                    <input type="checkbox" class="toggle-switch-input" onclick="return false;">
                                                    <span class="toggle-switch-label">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                            @else
                                            <label class="toggle-switch toggle-switch-sm">
                                                <input type="checkbox" class="toggle-switch-input dynamic-checkbox"
                                                    id="status-{{ $zone['id'] }}" {{ $zone->status ? 'checked' : '' }}
                                                    data-id="status-{{ $zone['id'] }}" data-type="status"
                                                    data-image-on='{{ dynamicAsset('assets/admin/img/modal') }}/zone-status-on.png'
                                                    data-image-off="{{ dynamicAsset('assets/admin/img/modal') }}/zone-status-off.png"
                                                    data-title-on="{{ translate('Want_to_activate_this_Zone?') }}"
                                                    data-title-off="{{ translate('Want_to_deactivate_this_Zone?') }}"
                                                    data-text-on="<p>{{ translate('If_you_activate_this_zone,_Customers_can_see_all_restaurants_&_products_available_under_this_Zone_from_the_Customer_App_&_Website.') }}</p>"
                                                    data-text-off="<p>{{ translate('If_you_deactivate_this_zone,_Customers_Will_NOT_see_all_restaurants_&_products_available_under_this_Zone_from_the_Customer_App_&_Website.') }}</p>">
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                            <form
                                                action="{{ route('admin.zone.status', [$zone['id'], $zone->status ? 0 : 1]) }}"
                                                method="get" id="status-{{ $zone['id'] }}_form">
                                            </form>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn--container justify-content-center">
                                                <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                                    href="{{ route('admin.zone.edit', [$zone['id']]) }}"
                                                    title="{{ translate('messages.edit_zone') }}"><i
                                                        class="tio-edit"></i>
                                                </a>
                                                {{-- The settings are a drawer on this page now, so the icon
                                                     pulls the panel in rather than leaving the list. --}}
                                                <a class="btn active action-btn btn--warning btn-outline-warning zone-settings-trigger"
                                                    href="javascript:"
                                                    data-zone="{{ $zone['id'] }}"
                                                    data-url="{{ route('admin.zone.settings', ['id' => $zone['id']]) }}"
                                                    title="{{ translate('messages.zone_settings') }}">
                                                    <i class="tio-settings"></i>
                                                </a>
                                                @unless ($zone_ready)
                                                    {{-- A mark rather than a control: it says the zone is unfinished,
                                                         and hovering it says what is missing. The notice used to be
                                                         open on every unfinished row the moment the page loaded,
                                                         which buried the table it was describing. --}}
                                                    <div class="popover-wrapper zone-setup-warning">
                                                        <span class="btn btn-sm btn--danger btn-outline-danger action-btn zone-setup-warning__mark"
                                                            tabindex="0" role="img"
                                                            aria-label="{{ translate('messages.Important!') }}">
                                                            <i class="tio-warning"></i>
                                                        </span>
                                                        <div class="popover __popover">
                                                            <div class="arrow"></div>
                                                            <h3 class="popover-header">
                                                                <span>{{ translate('messages.Important!') }}</span>
                                                            </h3>
                                                            <div class="popover-body">
                                                                {{ $zone_setup_notice }}
                                                                @include('admin-views.zone.partials._setup-links')
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endunless
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
