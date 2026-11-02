<script>
    "use strict";

    (function () {
        const NOTE_PREFIX = @json(translate('messages.Youve_already_set_a_minimum_delivery_charge_of'));
        const NOTE_SUFFIX = @json(translate('messages.for_this_zone.'));
        const NO_RULE = @json(translate('messages.This_zone_has_no_active_delivery_charge_rule_yet,_so_no_minimum_charge_applies.'));
        const TIME_PREFIX = @json(translate('messages.This_zones_minimum_delivery_time_limit_is'));
        const MIN_UNIT = @json(translate('messages.Min'));

        function esc(value) {
            return $('<div>').text(value == null ? '' : value).html();
        }

        // The floor belongs to the zone's active delivery rule, so the note is
        // refetched rather than shipped for every zone up front.
        $(document).on('change', '#zone_id', function () {
            const zoneId = $(this).val();
            if (!zoneId) { return; }

            $.get('{{ url('admin/additional-charge/zone-minimum') }}/' + zoneId, function (data) {
                // There is a zone now, so the note has something to be about.
                $('#minimum-charge-note').removeClass('d-none');

                if (data.formatted === null || data.formatted === undefined) {
                    $('#minimum-charge-text').text(NO_RULE);
                } else {
                    $('#minimum-charge-text').html(
                        esc(NOTE_PREFIX) + ' <strong>' + esc(data.formatted) + '</strong> ' + esc(NOTE_SUFFIX)
                    );
                }

                // A zone without a time floor has nothing to warn about.
                const minutes = data.minimum_delivery_time || 0;
                $('#minimum-time-note').toggleClass('d-none', minutes <= 0);
                $('#minimum-time-text').html(
                    esc(TIME_PREFIX) + ' <strong>' + minutes + ' ' + esc(MIN_UNIT) + '</strong>.'
                );
            });
        });

        $('#additional-charge-form').on('submit', function (e) {
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
                    $('#charge-success-view').attr('href', data.redirect);
                    $('#charge-success-modal').modal('show');
                },
                error: function (xhr) {
                    $submit.prop('disabled', false);

                    // A zone holds one setup, so a second one is refused rather than
                    // replacing the first. Checked before the toastr branch, which would
                    // otherwise also print the refusal the modal is already carrying.
                    if (xhr.status === 409) {
                        $('#charge-duplicate-modal').modal('show');
                        return;
                    }

                    const errs = xhr.responseJSON && xhr.responseJSON.errors;
                    if (errs) { errs.forEach(function (e) { toastr.error(e.message); }); }
                    else { toastr.error(@json(translate('messages.An_unexpected_error_occurred.'))); }
                }
            });
        });
    })();
</script>
