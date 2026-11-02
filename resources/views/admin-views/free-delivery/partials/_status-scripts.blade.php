<script>
    "use strict";

    // Both directions are confirmed, so the checkbox never changes state on its
    // own — it is reverted immediately and only the server response reloads.
    (function () {
        let pendingFreeDeliveryId = null;
        let pendingTurningOn = false;

        $(document).on('click', '.free-delivery-status-toggle', function (e) {
            e.preventDefault();

            const $input = $(this);
            pendingFreeDeliveryId = $input.data('id');

            // A click handler on a checkbox runs *after* the browser has flipped
            // it, so reading .prop('checked') here reports the state being moved
            // to and asked the opposite question. The server-rendered
            // data-status is the state it is actually in.
            pendingTurningOn = Number($input.data('status')) !== 1;

            // Nothing changes until the server confirms, so put it back.
            $input.prop('checked', !pendingTurningOn);

            $(pendingTurningOn ? '#free-delivery-turn-on-modal' : '#free-delivery-turn-off-modal').modal('show');
        });

        function send(payload) {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
            $.post({
                url: '{{ url('admin/free-delivery/status') }}/' + pendingFreeDeliveryId,
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

        $(document).on('click', '#free-delivery-turn-on-confirm', function () {
            $('#free-delivery-turn-on-modal').modal('hide');
            send({ status: 1 });
        });

        $(document).on('click', '#free-delivery-turn-off-confirm', function () {
            $('#free-delivery-turn-off-modal').modal('hide');
            send({ status: 0 });
        });
    })();
</script>
