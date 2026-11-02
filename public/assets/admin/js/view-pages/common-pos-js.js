     "use strict";
        $("#customer").change(function() {
            if($(this).val())
            {
                $('#customer_data').removeClass('d-none').addClass('d-flex');
                $('#Home_a').prop('disabled', false);

                $('#wallet_payment').prop('disabled', false);
                $('#wallet_payment_li').removeClass('d-none');

                $('#Dine_a').prop('disabled', false);
                $('#customer_id').val($(this).val());


                $.get({
                url: $(this).data('url'),
                dataType: 'json',
                data: {
                    customer_id: $(this).val()
                },
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#customer_name').text(data.user.customer_name );
                    $('#contact_person_name').val(data.user.customer_name );
                    $('#contact_person_number').val(data.user.contact_person_number);
                    $('#customer_phone').text(data.user.customer_phone );
                    $('#customer_wallet').text(data.user.customer_wallet );
                    $('#customer_email').text(data.user.customer_email );
                    $('#customer_data').removeClass('d-none').addClass('d-flex');
                    $('#delivery_address_div').empty().html(data.view);

                    let $orderType = $('[name="order_type"]:checked');
                    if ($orderType.length) {
                        handleOrderType($orderType);
                    } else if (typeof updateCart === 'function') {
                        updateCart();
                    }
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
            } else {

            $.get({
                url: $(this).data('clear-data-url'),
                dataType: 'json',
                success: function (data) {
                    $('#delivery_address_div').empty().addClass('d-none');
                    $('#customer_data').addClass('d-none').removeClass('d-flex');
                    $('#Home_a').prop('disabled', true).prop('checked', false);
                    $('#Dine_a').prop('disabled', true).prop('checked', false);
                    $('#wallet_payment').prop('disabled', true).prop('checked', false);
                    $('#wallet_payment_li').addClass('d-none');
                    $('#take_a').prop('checked', true);

                    $('[name="order_type"]:checked').trigger('click').trigger('change');
                },
            });
            }
        }).trigger('change');


        $(document).on('click', '.call-map-init', function () {
            let name = $('#customer_name').text().trim();
            let phone = $('#customer_phone').text().trim();
            phone = phone.replace(/^\(|\)$/g, '');

            $('#contact_person_name').val(name);
            $('#contact_person_number').val(phone);
            initMap();
            initTelInputs();
        });

        $(document).on('click', '.clear-when-done', function (e) {
            e.preventDefault();

            $('#delivery_address_store')
                .find('input, select, textarea')
                .each(function () {
                    let $field = $(this);
                    let type = $field.attr('type');
                    let name = $field.attr('name');
                    if (name === 'contact_person_name' || name === 'contact_person_number') {
                        return;
                    }

                    if ($field.is('select')) {
                        $field.prop('selectedIndex', 0);
                    } else if (type === 'checkbox' || type === 'radio') {
                        $field.prop('checked', false);
                    } else {
                        $field.val('');
                    }
                });
        });


        $(document).on('click', '.place-order-submit', function () {
           $(this).prop('disabled', true);
            $('#order_place').submit();
        });



        function handleOrderType($element) {
            if ($element.val() === 'delivery') {
                getUserAddress($element.data('user-address-url'));
            } else {
                $('#delivery_address_div').addClass('d-none');
            }

            $.get({
                url: $element.data('url'),
                dataType: 'json',
                data: {
                    type: $element.val(),
                },
                success: function (data) {
                    // loadDeliveryTypes is invoked from posCartUpdated() inside the
                    // cart partial's inline script, so it runs *after* the partial
                    // re-renders with fresh delivery_charge_display, etc.
                    updateCart();
                }
            });
        }

        $(document).on('click', '[name="order_type"]', function () {
            handleOrderType($(this));
        });


        function getUserAddress(url){
            $.get({
                url: url,
                dataType: 'json',
                data: {
                    customer_id: $('#customer').val(),
                },
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#delivery_address_div').empty().html(data.view);
                    $('#delivery_address_div').removeClass('d-none');
                },
                complete: function () {
                    $('#loading').hide();
                },
            });

        }

        $(document).on('click', '.editAddress', function () {
            $.get({
                url: $(this).data('url'),
                dataType: 'json',
                data: {
                    customer_id: $('#customer').val(),
                    address_id: $(this).data('address_id'),
                },
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {

                    $('#show-address-modal').empty().html(data.view);
                    $('#paymentModal').modal('show');
                    initMap(data.latitude, data.longitude);
                       initTelInputs();

                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });

        $(document).on('click', '.choose-address', function () {
            chooseAddress($(this).data('url'),$('#customer').val(),$(this).data('id'));
        });

        function chooseAddress(url,customer_id,address_id){
            console.log(url,customer_id,address_id);
             $.get({
                url:  url,
                dataType: 'json',
                data: {
                    customer_id: customer_id,
                    address_id: address_id,
                },
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                   updateCart();
                    $('#delivery_address_div').empty().html(data.view);
                    $('#delivery_address_div').removeClass('d-none');
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        }
        $(document).on('submit', '#customer-add-form', function (event) {
            event.preventDefault();
            if(document.getElementById('phone').value.length <= 4){
                    toastr.error($(this).data('warning-message'), {
                    CloseButton: true,
                    ProgressBar: true
                });
                }
                else{
                    document.getElementById('customer-add-form').submit();
                }
        });

        // --- Shared delivery_type_section logic (admin + vendor POS) ---
        // Each panel sets window.posDeliveryTypeConfig before document ready with:
        //   setDeliveryTypeUrl, getDeliveryTypesUrl, getDeliveryCoverageUrl,
        //   setDeliveryCoverageUrl, zoneId, zoneMinimumDeliveryTime,
        //   restaurantDeliveryTime, areaLabel, zipLabel, selectPlaceholder,
        //   coverageRequiredNote, currencySymbol, currencySymbolPosition,
        //   roundUpDigit, requireZoneAndRestaurantSelect
        window.deliveryTypeSectionDisabled = false;

        function posDeliveryConfig() {
            return window.posDeliveryTypeConfig || {};
        }

        function formatCurrency(value) {
            let cfg = posDeliveryConfig();
            let digits = cfg.roundUpDigit != null ? cfg.roundUpDigit : 2;
            let symbol = cfg.currencySymbol || '';
            let position = cfg.currencySymbolPosition || 'left';
            let formatted = parseFloat(value).toFixed(digits);
            return position === 'right'
                ? formatted + ' ' + symbol
                : symbol + ' ' + formatted;
        }

        function getDeliveryChargeDisplay() {
            return parseFloat($('#delivery_charge_display').val()) || 0;
        }

        function deliveryReadyForLoad() {
            if (!posDeliveryConfig().requireZoneAndRestaurantSelect) {
                return true;
            }
            return !!($('#zone_id').val() && $('#restaurant_id').val());
        }

        function setDeliveryTypeSectionState(disabled) {
            let $section = $('#delivery_type_section');
            let $options = $('#delivery_type_options');
            let $radios = $options.find('.delivery-type-radio');
            let $note = $('#delivery_type_note');
            let orderIsDelivery = $('input[name="order_type"]:checked').val() === 'delivery';

            if (disabled && window.posProHidesDeliveryType) {
                // Pro customer with delivery_fee benefit: hide entire section.
                $section.addClass('d-none');
                return;
            }

            if (disabled) {
                // Free-delivery-over: show note, hide the options list.
                if (orderIsDelivery && deliveryReadyForLoad()) {
                    $section.removeClass('d-none').show();
                }
                $options.removeClass('d-flex').addClass('d-none');
                $radios.prop('checked', false).prop('disabled', true);
                $note.removeClass('d-none').addClass('d-flex');
            } else {
                // Normal: show the options list, hide the note.
                if (orderIsDelivery && deliveryReadyForLoad()) {
                    $section.removeClass('d-none');
                }
                $options.removeClass('d-none').addClass('d-flex');
                $radios.prop('disabled', false);
                $note.addClass('d-none').removeClass('d-flex');
            }
        }

        // Called by the cart partial's inline `$(function)` on every render
        // (initial load + every updateCart). This is the single trigger point
        // for: (a) recomputing section disable/show state, (b) refetching
        // delivery-type options so prices reflect the latest delivery_charge.
        // The delivery-fee tooltip explaining a vehicle category charge or a surge.
        // theme.min.js arms tooltips once on document ready, but the cart partial is
        // replaced wholesale on every update, so the fresh icon would never be armed.
        function refreshPosTooltips() {
            if (!$.fn.tooltip) return;
            // A replaced trigger can strand its popup, which then floats over the page
            // with nothing to dismiss it. Drop only those - a popup whose owner is gone.
            $('div.tooltip').each(function () {
                if (!$('[aria-describedby="' + this.id + '"]').length) {
                    $(this).remove();
                }
            });
            $('.pos-delivery-note').tooltip();
        }

        function posCartUpdated() {
            refreshPosTooltips();
            if (typeof refreshDeliveryTypeSectionState === 'function') {
                refreshDeliveryTypeSectionState();
            }
            let orderIsDelivery = $('input[name="order_type"]:checked').val() === 'delivery';
            let ready = orderIsDelivery
                && typeof deliveryReadyForLoad === 'function'
                && deliveryReadyForLoad();
            if (ready && typeof loadDeliveryCoverage === 'function') {
                loadDeliveryCoverage();
            } else if (!orderIsDelivery) {
                $('#delivery_coverage_section').addClass('d-none');
            }
            let canLoadOptions = ready && !window.posProHidesDeliveryType;
            if (canLoadOptions && typeof loadDeliveryTypes === 'function') {
                loadDeliveryTypes();
            }
        }

        // --- Area / ZIP picker, for zones whose delivery rule prices coverage-wise ---
        // Distance and fixed-amount rules price the whole zone and need no pick, so the
        // endpoint returns an empty list for them and the section stays hidden. Without
        // a pick the server has nothing to look the charge up by and bills the rule's
        // minimum, which is why this is not optional where it does appear.
        function loadDeliveryCoverage() {
            let cfg = posDeliveryConfig();
            if (!cfg.getDeliveryCoverageUrl) return;

            $.get({
                url: cfg.getDeliveryCoverageUrl,
                success: function (data) {
                    let method = data ? data.delivery_charge_type : null;
                    let items = (data && data.data) ? data.data : [];

                    if ((method !== 'area_wise' && method !== 'zip_code_wise') || items.length === 0) {
                        $('#delivery_coverage_section').addClass('d-none');
                        return;
                    }

                    let isArea = method === 'area_wise';
                    $('#delivery_coverage_label').text(isArea ? cfg.areaLabel : cfg.zipLabel);

                    let selected = data.selected ? String(data.selected) : '';
                    let html = '<option value="">' + cfg.selectPlaceholder + '</option>';
                    items.forEach(function (item) {
                        let id = String(item.id);
                        html += '<option value="' + id + '"' + (id === selected ? ' selected' : '') + '>'
                            + item.name + ' — ' + formatCurrency(item.delivery_charge)
                            + '</option>';
                    });
                    $('#delivery_coverage_select').html(html);

                    let $note = $('#delivery_coverage_note');
                    if (!selected) {
                        $('#delivery_coverage_note_text').text(cfg.coverageRequiredNote);
                        $note.removeClass('d-none').addClass('d-flex');
                    } else {
                        $note.addClass('d-none').removeClass('d-flex');
                    }

                    $('#delivery_coverage_section').removeClass('d-none');
                },
                error: function () {
                    $('#delivery_coverage_section').addClass('d-none');
                }
            });
        }

        $(document).on('change', '#delivery_coverage_select', function () {
            let cfg = posDeliveryConfig();
            if (!cfg.setDeliveryCoverageUrl) return;

            $.get({
                url: cfg.setDeliveryCoverageUrl,
                data: { coverage_id: $(this).val() || '' },
                beforeSend: function () { $('#loading').show(); },
                success: function () {
                    if (typeof updateCart === 'function') updateCart();
                },
                complete: function () { $('#loading').hide(); }
            });
        });

        function refreshDeliveryTypeSectionState(syncSelection) {
            if ($('input[name="order_type"]:checked').val() !== 'delivery') {
                return;
            }
            // Read the panel's config here like every other function in this block does.
            // The pro-customer branch below used a bare `cfg`, which is not in scope: the
            // moment a Pro customer with the delivery-fee benefit was on the counter this
            // threw a ReferenceError, and because posCartUpdated() calls straight into
            // here the rest of the cart refresh - the saver options and the coverage
            // picker - never ran.
            let cfg = posDeliveryConfig();
            let wasDisabled = window.deliveryTypeSectionDisabled;
            // Free delivery no longer hides the saver options. place_order charges an
            // express delivery's extra even when the delivery itself is free, and prices
            // a delayed one at min(reduce_charge, delivery_charge) — which is 0 on a free
            // delivery all by itself. Hiding the options here made the counter refuse a
            // surcharge the app collects on the same basket.
            let shouldDisable = !!window.posProHidesDeliveryType;

            if (shouldDisable) {
                if (!window.deliveryTypeSectionDisabled && cfg.setDeliveryTypeUrl) {
                    $.get({
                        url: cfg.setDeliveryTypeUrl,
                        data: { delivery_type: '', delivery_type_charge: 0 },
                        success: function () {
                            if (typeof updateCart === 'function') updateCart();
                        }
                    });
                }

                window.deliveryTypeSectionDisabled = true;
                setDeliveryTypeSectionState(true);
                return;
            }

            window.deliveryTypeSectionDisabled = false;
            setDeliveryTypeSectionState(false);

            if (syncSelection || wasDisabled) {
                let $checked = $('#delivery_type_options .delivery-type-radio:checked');
                if ($checked.length) {
                    $checked.trigger('change');
                } else {
                    let $first = $('#delivery_type_options .delivery-type-radio').first();
                    if ($first.length) {
                        $first.prop('checked', true).trigger('change');
                    }
                }
            }
        }

        function loadDeliveryTypes() {
            let cfg = posDeliveryConfig();
            if (!cfg.getDeliveryTypesUrl) return;

            let zone_id = cfg.zoneId || '';
            if (!zone_id) return;

            // Capture the user's current selection so we can restore it after
            // rebuilding the radio HTML (which always emits the first option as
            // `checked`). Without this, every cart update would wipe their pick.
            let previouslySelectedType = $('#delivery_type_options .delivery-type-radio:checked').val() || '';

            let zone_minimum_delivery_time = cfg.zoneMinimumDeliveryTime || { value: 0, unit: 'min' };
            let restaurant_delivery_time = cfg.restaurantDeliveryTime || '0-0-min';
            let res_time = restaurant_delivery_time.split('-');
            let res_min = parseInt(res_time[0]) || 0;
            let res_max = parseInt(res_time[1]) || 0;
            let res_unit = res_time[2] || 'min';

            let zone_min = parseInt(zone_minimum_delivery_time.value) || 0;

            if (zone_minimum_delivery_time.unit === 'hour') {
                zone_min = zone_min * 60;
            }
            if (res_unit === 'hours' || res_unit === 'hour') {
                res_max = res_max * 60;
                res_min = res_min * 60;
            }

            $.get({
                url: cfg.getDeliveryTypesUrl,
                data: { zone_id: zone_id },
                beforeSend: function () { $('#loading').show(); },
                success: function (data) {
                    let options = Array.isArray(data) ? data : (data && data.options ? data.options : []);
                    let endpointDeliveryCharge = (data && !Array.isArray(data) && data.current_delivery_charge != null)
                        ? parseFloat(data.current_delivery_charge)
                        : NaN;

                    if (!options || options.length === 0) {
                        $('#delivery_type_section').hide();
                        return;
                    }

                    // The fee the order will be written with, not the notional full fee:
                    // resolveSaverDeliveryOption() prices express as charge + extra and a
                    // delayed one as charge - min(reduce, charge), both against this figure.
                    // Quoting the pre-free-delivery amount here showed a price the order
                    // would never carry.
                    let baseDeliveryCharge = isNaN(endpointDeliveryCharge)
                        ? getDeliveryChargeDisplay()
                        : endpointDeliveryCharge;

                    let html = '';
                    options.forEach(function (option, index) {
                        let extraCharge = parseFloat(option.extra_charge) || 0;
                        let reduceCharge = Math.min(parseFloat(option.reduce_charge) || 0, baseDeliveryCharge);
                        let finalCharge = baseDeliveryCharge + extraCharge - reduceCharge;
                        if (finalCharge < 0) finalCharge = 0;

                        // Every option carries a price, including the ones that come out at
                        // nothing. This used to be blank unless the option moved the fee, so a
                        // free delivery priced Standard at 0 and left Slightly Delay showing no
                        // figure at all - which reads as "unknown", not "free".
                        let chargeText = option.delivery_type === 'standard'
                            ? formatCurrency(baseDeliveryCharge)
                            : formatCurrency(finalCharge);

                        let type = option.delivery_type;
                        let display_min = res_min;
                        let display_max = res_max;

                        if (type === 'standard') {
                            if (zone_min > res_max) {
                                display_max = zone_min;
                            } else if (zone_min > 0 && res_min > zone_min) {
                                display_min = zone_min;
                                display_max = res_max;
                            }
                        } else if (type === 'express') {
                            let reduce_val = 0;
                            if (option.reduce_delivery_time) {
                                reduce_val = parseInt(option.reduce_delivery_time.value) || 0;
                                if (option.reduce_delivery_time.unit === 'hour') {
                                    reduce_val *= 60;
                                }
                            }
                            if (zone_min > res_max) {
                                display_max = zone_min - reduce_val;
                            } else if (zone_min > 0 && res_min > zone_min) {
                                display_min = zone_min;
                                display_max = res_max - reduce_val;
                            } else {
                                display_max = res_max - reduce_val;
                            }
                            if (display_max < display_min) {
                                display_max = display_min;
                            }
                        } else if (type === 'slightly_delay') {
                            let add_val = 0;
                            if (option.add_delivery_time) {
                                add_val = parseInt(option.add_delivery_time.value) || 0;
                                if (option.add_delivery_time.unit === 'hour') {
                                    add_val *= 60;
                                }
                            }
                            if (zone_min > res_max) {
                                display_max = zone_min + add_val;
                            } else if (zone_min > 0 && res_min > zone_min) {
                                display_min = zone_min;
                                display_max = res_max + add_val;
                            } else {
                                display_max = res_max + add_val;
                            }
                        }

                        function formatTime(minutes) {
                            if (minutes >= 60) {
                                let hours = Math.floor(minutes / 60);
                                let mins = minutes % 60;
                                if (mins === 0) {
                                    return hours + (hours === 1 ? " hour" : " hours");
                                }
                                return hours + (hours === 1 ? " hour " : " hours ") + mins + " min";
                            }
                            return minutes + " min";
                        }

                        let timeRange = display_min === display_max
                            ? `upto ${formatTime(display_max)}`
                            : `${formatTime(display_min)} - ${formatTime(display_max)}`;

                        html += `
                        <div class="bg-light rounded py-3 px-md-3 px-2 border mb-3">
                            <div class="form-check form--check ps__22 d-flex align-items-center">

                                <input type="radio"
                                    name="delivery_type_option"
                                    value="${option.delivery_type}"
                                    data-extra-charge="${extraCharge}"
                                    data-reduce-charge="${reduceCharge}"
                                    class="form-check-input mt-0 delivery-type-radio"
                                    id="delivery_type_${option.id}"
                                    ${index === 0 ? 'checked' : ''}>

                                <label class="form-check-label lh-1 ml-0 fs-14 font-normal text-title d-flex gap-2 justify-content-between align-items-center flex-wrap flex-grow-1"
                                    for="delivery_type_${option.id}">

                                    <span class="flex-grow-1 d-flex gap-2 flex-wrap flex-row">
                                        <span class="font-semibold">${option.delivery_type_text}</span>
                                        <span class="">(${timeRange})</span>
                                    </span>

                                    ${chargeText ? `
                                    <span class="flex-shrink-0 bg-white rounded-20 px-2 py-1 font-medium fs-13">
                                        ${chargeText}
                                    </span>` : ''}

                                </label>

                            </div>
                        </div>
                        `;
                    });

                    $('#delivery_type_options').html(html);

                    // Restore the previous selection if the same delivery_type
                    // is still offered for this zone.
                    if (previouslySelectedType) {
                        let $match = $('#delivery_type_options .delivery-type-radio[value="' + previouslySelectedType + '"]');
                        if ($match.length) {
                            $('#delivery_type_options .delivery-type-radio').prop('checked', false);
                            $match.prop('checked', true);
                        }
                    }

                    if (!window.posProHidesDeliveryType) {
                        $('#delivery_type_section').removeClass('d-none').show();
                    }
                    refreshDeliveryTypeSectionState(true);
                },
                complete: function () { $('#loading').hide(); },
                error: function (xhr, status, error) {
                    console.error('Error loading delivery types:', error);
                    $('#loading').hide();
                }
            });
        }

        $(document).on('change', 'input[name="order_type"]', function () {
            if (!window.posDeliveryTypeConfig) return;
            let isDelivery = $(this).val() === 'delivery';
            let ready = deliveryReadyForLoad();
            if (isDelivery && ready && !window.posProHidesDeliveryType) {
                $('#delivery_type_section').removeClass('d-none');
            } else {
                $('#delivery_type_section').addClass('d-none');
                window.deliveryTypeSectionDisabled = false;
                if (isDelivery && !ready && posDeliveryConfig().setDeliveryTypeUrl) {
                    $.get({
                        url: posDeliveryConfig().setDeliveryTypeUrl,
                        data: { delivery_type: '', delivery_type_charge: 0 }
                    });
                }
            }
        });

        $(document).on('change', '.delivery-type-radio', function (event) {
            let cfg = posDeliveryConfig();
            if (!cfg.setDeliveryTypeUrl) return;
            let type = $(this).val();
            let extra = parseFloat($(this).data('extra-charge')) || 0;
            let reduce = parseFloat($(this).data('reduce-charge')) || 0;
            let charge = extra - reduce;
            // Real user events carry event.originalEvent. Programmatic
            // .trigger('change') from refresh's sync block does not — and
            // calling updateCart() from a programmatic sync would cascade
            // back into posCartUpdated → loadDeliveryTypes → refresh(true) → sync
            // → here → updateCart again. Infinite loop. Only update the cart
            // when the user actually picked a different option.
            let userInitiated = !!event.originalEvent;

            $.get({
                url: cfg.setDeliveryTypeUrl,
                data: { delivery_type: type, delivery_type_charge: charge },
                success: function () {
                    if (userInitiated && typeof updateCart === 'function') {
                        updateCart();
                    }
                }
            });
        });

        $(document).ready(function () {
            // The cart partial's inline `$(function)` calls posCartUpdated()
            // which handles the delivery+ready case (loadDeliveryTypes + section state).
            // Only the delivery + !ready branch needs handling here, since the partial
            // doesn't know about admin's zone/restaurant gating.
            if (!window.posDeliveryTypeConfig) return;
            if ($('input[name="order_type"]:checked').val() !== 'delivery') return;
            if (deliveryReadyForLoad()) return;

            $('#delivery_type_section').addClass('d-none');
            window.deliveryTypeSectionDisabled = false;
            let cfg = posDeliveryConfig();
            if (cfg.setDeliveryTypeUrl) {
                $.get({
                    url: cfg.setDeliveryTypeUrl,
                    data: { delivery_type: '', delivery_type_charge: 0 }
                });
            }
        });
