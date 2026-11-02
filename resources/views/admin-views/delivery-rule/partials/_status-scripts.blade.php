<script>
    "use strict";

    // Both directions are confirmed, so the checkbox never changes state on its
    // own — preventDefault restores it and only the server response reloads.
    (function () {
        let pendingRuleId = null;
        let pendingTurningOn = false;

        $(document).on('click', '.rule-status-toggle', function (e) {
            // preventDefault undoes the browser's flip on its own, so the switch
            // keeps showing the saved state. The direction has to come from that
            // saved state too: reading `checked` here reads the flip the browser
            // is about to undo, which is what opened the opposite modal.
            e.preventDefault();

            const $input = $(this);
            pendingRuleId = $input.data('id');
            pendingTurningOn = Number($input.data('status')) !== 1;

            if (pendingTurningOn) {
                $('#rule-turn-on-modal').modal('show');
                return;
            }

            // Offer the zone's other rules as the replacement. They come from the
            // server because the list is paginated and the detail page holds only
            // this rule, so the markup never carries the full set.
            const $select = $('#replacement_id').empty();
            $('#replacement-empty').addClass('d-none');
            $('#replacement_id').prop('disabled', true);
            $('#rule-turn-off-confirm').prop('disabled', true);
            $('#rule-turn-off-modal').modal('show');

            $.get('{{ url('admin/delivery-rule/zone-rules') }}/' + $input.data('zone'), {
                exclude: pendingRuleId
            }, function (data) {
                (data.rules || []).forEach(function (rule) {
                    $select.append($('<option>').val(rule.id).text(rule.name));
                });

                const hasOptions = $select.find('option').length > 0;
                $('#replacement-empty').toggleClass('d-none', hasOptions);
                $('#replacement_id').prop('disabled', !hasOptions);
                $('#rule-turn-off-confirm').prop('disabled', !hasOptions);
            });
        });

        function send(payload) {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
            $.post({
                url: '{{ url('admin/delivery-rule/status') }}/' + pendingRuleId,
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

        $(document).on('click', '#rule-turn-on-confirm', function () {
            $('#rule-turn-on-modal').modal('hide');
            send({ status: 1 });
        });

        $(document).on('click', '#rule-turn-off-confirm', function () {
            const replacement = $('#replacement_id').val();
            if (!replacement) { return; }
            $('#rule-turn-off-modal').modal('hide');
            send({ status: 0, replacement_id: replacement });
        });
    })();
</script>
