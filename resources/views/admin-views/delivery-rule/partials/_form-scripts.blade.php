<script>
    "use strict";

    (function () {
        const HINTS = {
            [@json(\App\Models\DeliveryRule::METHOD_AREA)]: @json(translate('messages.A_separate_charge_applies_to_each_area_in_the_selected_zone.')),
            [@json(\App\Models\DeliveryRule::METHOD_ZIP)]: @json(translate('messages.A_separate_charge_applies_to_each_ZIP_code_in_the_selected_zone.')),
            [@json(\App\Models\DeliveryRule::METHOD_DISTANCE)]: @json(translate('messages.The_charge_scales_with_distance,_bounded_by_the_minimum_and_maximum.')),
            [@json(\App\Models\DeliveryRule::METHOD_FIXED)]: @json(translate('messages.This_fixed_charge_applies_to_every_order_in_the_selected_zone.')),
        };

        function activeMethod() {
            return $('.rule-method-input:checked').val();
        }

        function syncPanes() {
            const method = activeMethod();
            $('.rule-pane').addClass('d-none');
            $('.rule-pane[data-pane="' + method + '"]').removeClass('d-none');
            $('.rule-method').removeClass('is-selected');
            $('.rule-method-input:checked').closest('.rule-method').addClass('is-selected');
            $('#method-hint').text(HINTS[method] || '');
        }

        $(document).on('change', '.rule-method-input', syncPanes);
        syncPanes();

        // Everything _charge-table.blade.php needs to redraw a pane client-side.
        // Both shapes below must stay identical to that partial.
        const PANES = {
            [@json(\App\Models\DeliveryRule::METHOD_AREA)]: {
                wrapper: '#area-charge-wrapper',
                labelKey: 'name',
                field: 'area_charges',
                heading: @json(translate('messages.Area_Name')),
                title: @json(translate('messages.Area_Wise_Charge_Setup')),
                description: @json(translate('messages.Configure_fixed_delivery_charges_for_each_service_area_within_a_zone.')),
                hintText: @json(translate('messages.All_areas_under_this_zone_will_be_listed_automatically._Set_the_delivery_charge_for_each_area.')),
                hintLinkText: @json(translate('messages.Setup_Area')),
                link: @json(route('admin.area.list')),
                emptyText: @json(translate('messages.No_areas_have_been_created_for_this_Zone._To_set_up_Area_Wise_Charges,_first_create_an_area_for_this_Zone_from')),
                emptyLinkText: @json(translate('messages.Area_Setup')),
            },
            [@json(\App\Models\DeliveryRule::METHOD_ZIP)]: {
                wrapper: '#zip-charge-wrapper',
                labelKey: 'zip_code',
                field: 'zip_charges',
                heading: @json(translate('messages.Zip_Code')),
                title: @json(translate('messages.Zip_Code_Wise_Charge_Setup')),
                description: @json(translate('messages.Configure_fixed_delivery_charges_for_each_ZIP_code_within_a_zone.')),
                hintText: @json(translate('messages.All_ZIP_codes_under_this_zone_will_be_listed_automatically._Set_the_delivery_charge_for_each_code.')),
                hintLinkText: @json(translate('messages.Setup_Zip_Code')),
                link: @json(route('admin.zip-code.list')),
                emptyText: @json(translate('messages.No_ZIP_codes_have_been_created_for_this_Zone._To_set_up_ZIP_Code_Wise_Charges,_first_create_a_ZIP_code_for_this_Zone_from')),
                emptyLinkText: @json(translate('messages.Zip_Code_Setup')),
            },
        };

        // Amounts already typed or saved, kept per zone.
        //
        // Switching zone refetches that zone's areas and redraws the table, which
        // used to discard whatever was in the inputs. Leaving a zone and coming
        // back therefore wiped the rule's own saved charges — the editor's real
        // data — so what is on screen is stashed before every redraw and restored
        // when that zone is shown again. Seeded from the saved rule so its own
        // zone is right even before it is left once.
        @php
            // Derived here rather than reused from _form.blade.php: that partial
            // is a sibling include, so its locals are not in scope.
            $seedRule = $rule ?? null;
            $seedArea = [];
            $seedZip = [];
            foreach ($seedRule?->charges ?? [] as $seedCharge) {
                if ($seedCharge->area_id) { $seedArea[$seedCharge->area_id] = $seedCharge->charge; }
                if ($seedCharge->zip_code_id) { $seedZip[$seedCharge->zip_code_id] = $seedCharge->charge; }
            }
            $chargeSeed = $seedRule?->zone_id
                ? [(string) $seedRule->zone_id => ['area_charges' => (object) $seedArea, 'zip_charges' => (object) $seedZip]]
                : (object) [];
        @endphp
        const chargeStore = @json($chargeSeed);

        let shownZoneId = String($('#zone_id').val() ?? '');

        /** Stash what is currently in the inputs against the zone it belongs to. */
        function stashCharges(zoneId) {
            if (!zoneId) { return; }
            chargeStore[zoneId] = chargeStore[zoneId] || {};

            Object.values(PANES).forEach(function (pane) {
                const values = {};
                $(pane.wrapper).find('input[name^="' + pane.field + '["]').each(function () {
                    const id = $(this).attr('name').replace(pane.field + '[', '').replace(']', '');
                    if ($(this).val() !== '') { values[id] = $(this).val(); }
                });
                chargeStore[zoneId][pane.field] = values;
            });
        }

        function storedCharge(zoneId, field, rowId) {
            const v = ((chargeStore[zoneId] || {})[field] || {})[rowId];
            return v === undefined || v === null ? '' : v;
        }

        const CURRENCY = @json(\App\CentralLogics\Helpers::currency_symbol());
        const SL = @json(translate('messages.SL'));
        const CHARGE = @json(translate('messages.Delivery_Charge'));
        const EX = @json(translate('messages.Ex:_2'));

        function esc(value) {
            return $('<div>').text(value == null ? '' : value).html();
        }

        // The charge rows belong to the chosen zone, so they are refetched rather
        // than shipped for every zone up front.
        $(document).on('change', '#zone_id', function () {
            const zoneId = String($(this).val() ?? '');
            if (!zoneId) { return; }

            stashCharges(shownZoneId);
            shownZoneId = zoneId;

            $.get('{{ url('admin/delivery-rule/zone-coverage') }}/' + zoneId, function (data) {
                rebuild(PANES[@json(\App\Models\DeliveryRule::METHOD_AREA)], data.areas, zoneId);
                rebuild(PANES[@json(\App\Models\DeliveryRule::METHOD_ZIP)], data.zip_codes, zoneId);
            });
        });

        function rebuild(pane, rows, zoneId) {
            const $wrapper = $(pane.wrapper);

            if (!rows || rows.length === 0) {
                $wrapper.html(
                    '<div class="rule-coverage-empty">' +
                    '<h5 class="rule-coverage-empty__title">' + esc(pane.title) + '</h5>' +
                    '<p class="rule-coverage-empty__text">' + esc(pane.emptyText) + '</p>' +
                    '<a href="' + pane.link + '">' + esc(pane.emptyLinkText) + '</a>' +
                    '</div>'
                );
                return;
            }

            let body = '';
            rows.forEach(function (row, i) {
                body += '<tr>' +
                    '<td class="rule-charge-table__sl">' + (i + 1) + '</td>' +
                    '<td>' + esc(row[pane.labelKey]) + '</td>' +
                    '<td><input type="number" step="0.01" min="0" name="' + pane.field + '[' + row.id + ']" ' +
                    'class="form-control h-45" placeholder="' + esc(EX) + '" value="' +
                    esc(storedCharge(zoneId, pane.field, row.id)) + '"></td>' +
                    '</tr>';
            });

            $wrapper.html(
                '<div class="row g-3">' +
                '<div class="col-md-4">' +
                '<h5 class="surge-section__title">' + esc(pane.title) + '</h5>' +
                '<p class="surge-section__subtitle mb-0">' + esc(pane.description) + '</p>' +
                '<div class="rule-hint"><img src="{{ dynamicAsset('assets/admin/img/svg/bulb.svg') }}" class="svg" alt=""><span>' + esc(pane.hintText) +
                ' <a href="' + pane.link + '">' + esc(pane.hintLinkText) + '</a>.</span></div>' +
                '</div>' +
                '<div class="col-md-8">' +
                '<div class="rule-charge-table"><table class="rule-charge-table__table"><thead><tr>' +
                '<th class="rule-charge-table__sl">' + esc(SL) + '</th>' +
                '<th>' + esc(pane.heading) + ' <span class="text-danger">*</span></th>' +
                '<th>' + esc(CHARGE) + ' (' + esc(CURRENCY) + ') <span class="text-danger">*</span></th>' +
                '</tr></thead><tbody>' + body + '</tbody></table></div>' +
                '</div></div>'
            );
        }

        $('#delivery-rule-form').on('submit', function (e) {
            e.preventDefault();

            const $form = $(this);
            const $submit = $form.find('button[type="submit"]');
            $submit.prop('disabled', true);

            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            $.post({
                url: $form.attr('action'),
                data: new FormData(this),
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.errors) {
                        $submit.prop('disabled', false);
                        data.errors.forEach(function (err) { toastr.error(err.message); });
                        return;
                    }
                    $('#rule-success-view').attr('href', data.redirect);
                    $('#rule-success-modal').modal('show');

                    // The rule is saved and the form has nothing left to do, so the
                    // admin is returned to the list unless they open the rule first.
                    setTimeout(function () {
                        window.location.href = @json(route('admin.delivery-rule.list'));
                    }, 3000);
                },
                error: function (xhr) {
                    $submit.prop('disabled', false);
                    const errs = xhr.responseJSON && xhr.responseJSON.errors;
                    if (errs) { errs.forEach(function (e) { toastr.error(e.message); }); }
                    else { toastr.error(@json(translate('messages.An_unexpected_error_occurred.'))); }
                }
            });
        });
    })();
</script>
