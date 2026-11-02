<script>
    "use strict";

    // Both directions are confirmed, so the checkbox never changes state on its
    // own — it is reverted immediately and only the server response reloads.
    (function () {
        let pendingChargeId = null;
        let pendingTurningOn = false;

        $(document).on('click', '.charge-status-toggle', function (e) {
            e.preventDefault();

            const $input = $(this);
            pendingChargeId = $input.data('id');
            pendingTurningOn = !$input.prop('checked');
            $input.prop('checked', !$input.prop('checked'));

            $(pendingTurningOn ? '#charge-turn-on-modal' : '#charge-turn-off-modal').modal('show');
        });

        function send(payload) {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
            $.post({
                url: '{{ url('admin/additional-charge/status') }}/' + pendingChargeId,
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

        $(document).on('click', '#charge-turn-on-confirm', function () {
            $('#charge-turn-on-modal').modal('hide');
            send({ status: 1 });
        });

        $(document).on('click', '#charge-turn-off-confirm', function () {
            $('#charge-turn-off-modal').modal('hide');
            send({ status: 0 });
        });
    })();
</script>
