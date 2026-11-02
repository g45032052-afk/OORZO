<script>
    "use strict";

    // A zone estimates from exactly one configuration, so neither direction is a
    // simple flip: switching one on displaces the zone's current one, and switching
    // the current one off is only possible by naming its successor. Both are
    // therefore confirmed, and the checkbox is held at what the server still has
    // until the answer comes back.
    (function () {
        let pendingConfigurationId = null;

        // The zone's other configurations, as the options of whichever prompt is
        // asking. Fetched because the list is paginated, so a zone's full set is
        // never on the page. `onReady` is handed the count, so a prompt with nothing
        // to offer can say so rather than showing an empty select.
        function loadReplacements($select, zoneId, excludeId, onReady) {
            $select.empty().prop('disabled', true);

            $.get('{{ url('admin/eta-configuration/zone-configurations') }}/' + zoneId, {
                exclude: excludeId
            }, function (data) {
                (data.configurations || []).forEach(function (configuration) {
                    $select.append($('<option>').val(configuration.id).text(configuration.name));
                });

                const hasOptions = $select.find('option').length > 0;
                $select.prop('disabled', !hasOptions);
                onReady(hasOptions);
            });
        }

        window.etaLoadReplacements = loadReplacements;

        $(document).on('click', '.eta-configuration-status-toggle', function (e) {
            // preventDefault undoes the browser's flip on its own, so the switch keeps
            // showing the saved state. The direction has to come from that saved state
            // too: reading `checked` here reads the flip the browser is about to undo.
            e.preventDefault();

            const $input = $(this);
            const turningOn = Number($input.data('status')) !== 1;

            pendingConfigurationId = $input.data('id');

            if (turningOn) {
                $('#eta-configuration-turn-on-modal').modal('show');
                return;
            }

            const $confirm = $('#eta-configuration-turn-off-confirm');

            // Held closed until the zone's set comes back; a prompt that offered an
            // enabled Update before its options arrived would post nothing.
            $('#eta-replacement-empty').addClass('d-none');
            $confirm.prop('disabled', true);
            $('#eta-configuration-turn-off-modal').modal('show');

            loadReplacements($('#eta_replacement_id'), $input.data('zone'), pendingConfigurationId, function (hasOptions) {
                $('#eta-replacement-empty').toggleClass('d-none', hasOptions);
                $confirm.prop('disabled', !hasOptions);
            });
        });

        function send(payload) {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
            $.post({
                url: '{{ url('admin/eta-configuration/status') }}/' + pendingConfigurationId,
                data: payload,
                success: function (data) {
                    if (data.errors) {
                        data.errors.forEach(function (err) { toastr.error(err.message); });
                        return;
                    }
                    toastr.success(data.success);
                    setTimeout(function () { location.reload(); }, 900);
                },
                error: function (xhr) {
                    const errs = xhr.responseJSON && xhr.responseJSON.errors;
                    if (errs) { errs.forEach(function (e) { toastr.error(e.message); }); }
                    else { toastr.error('{{ translate('messages.An_unexpected_error_occurred.') }}'); }
                }
            });
        }

        $(document).on('click', '#eta-configuration-turn-on-confirm', function () {
            $('#eta-configuration-turn-on-modal').modal('hide');
            send({ status: 1 });
        });

        $(document).on('click', '#eta-configuration-turn-off-confirm', function () {
            const replacement = $('#eta_replacement_id').val();
            if (!replacement) { return; }
            $('#eta-configuration-turn-off-modal').modal('hide');
            send({ status: 0, replacement_id: replacement });
        });
    })();
</script>
