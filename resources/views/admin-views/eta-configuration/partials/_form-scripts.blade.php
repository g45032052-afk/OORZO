<script>
    "use strict";

    (function () {
        const DISTANCE = @json(\App\Models\EtaConfiguration::METHOD_DISTANCE);
        const MIN_UNIT = @json(translate('messages.eta_minute_unit'));

        function selectedMethod() {
            return $('input[name="calculation_method"]:checked').val() || DISTANCE;
        }

        function minutes(selector) {
            const value = parseInt($(selector).val(), 10);
            return isNaN(value) || value < 0 ? 0 : value;
        }

        // The estimate the customer would be shown. The minimum delivery time is
        // a floor, not a term: the buffers and the travel time are added up
        // first, and it only takes over when that total comes in under it. There
        // is no travel time to add while the form is being filled in, so the
        // example is the shortest estimate this setup can ever produce.
        function renderPreview() {
            const buffers = minutes('#preparation_buffer') + minutes('#transit_buffer');
            const from = Math.max(buffers, minutes('#minimum_delivery_time'));
            const to = from + minutes('#time_gap');

            $('#eta-preview-range').text(from + ' ' + MIN_UNIT + ' - ' + to + ' ' + MIN_UNIT);
        }

        function render() {
            const isDistance = selectedMethod() === DISTANCE;

            // The gap belongs to the distance based method alone, and the three
            // fields beside it spread to fill the row it leaves behind.
            $('#time-gap-wrapper').toggleClass('d-none', !isDistance);
            $('.eta-time-col')
                .toggleClass('col-md-6', isDistance)
                .toggleClass('col-md-4', !isDistance);

            renderPreview();
        }

        $(document).on('change', '.eta-method-input', render);
        $(document).on('input', '.eta-minute-field', renderPreview);

        // Reset restores the fields after this handler would run, so the picker
        // and the example are redrawn once the browser has finished.
        $(document).on('reset', '#eta-configuration-form', function () {
            setTimeout(render, 0);
        });

        /* ── character counter ────────────────────────────────────────────── */

        $('.eta-count-input').each(function () {
            const $input = $(this);
            const $counter = $input.siblings('.eta-char-count');
            const max = parseInt($input.attr('maxlength'), 10) || 100;

            function sync() {
                $counter.text($input.val().length + '/' + max);
            }

            $input.on('input', sync);
            $(document).on('reset', '#eta-configuration-form', function () {
                setTimeout(sync, 0);
            });
            sync();
        });

        render();

        $('#eta-configuration-form').on('submit', function (e) {
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
                    $('#eta-configuration-success-view').attr('href', data.redirect);
                    $('#eta-configuration-success-modal').modal('show');
                    // The modal confirms what happened; the admin should not have to dismiss it
                    // to reach the list, so the redirect follows on its own.
                    if (data.redirect) {
                        setTimeout(function () { window.location.href = data.redirect; }, 1800);
                    }
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
