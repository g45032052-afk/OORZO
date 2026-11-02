<script>
    "use strict";

    (function () {
        // The two types promise different things, so the standing note is rewritten
        // rather than left to describe whichever was picked first.
        const HINTS = {
            'all_restaurant': @json(translate('messages.Free_delivery_is_active_for_all_Restaurants._Cost_bearer_for_the_free_delivery_is_Admin')),
            'specific_criteria': @json(translate('messages.Set_the_minimum_order_amount_required_to_qualify_for_free_delivery.')),
        };

        function render() {
            const type = $('input[name="type"]:checked').val() || 'all_restaurant';
            const isCriteria = type === 'specific_criteria';

            // Only the criteria type has an amount to set.
            $('#minimum-order-amount-wrapper').toggleClass('d-none', !isCriteria);

            // Hiding the wrapper leaves the typed amount in the input, so switching away and
            // back shows a stale figure the admin never re-entered - and the server drops it
            // anyway. Clear it with the field it belongs to.
            if (!isCriteria) {
                $('#minimum-order-amount-wrapper').find('input').val('');
            }

            $('#free-delivery-hint').text(HINTS[type]);
        }

        $(document).on('change', '.free-delivery-type-input', render);

        // Reset restores the checked radio after this handler would run, so the
        // note is redrawn once the browser has finished.
        $(document).on('reset', '#free-delivery-form', function () {
            setTimeout(render, 0);
        });

        render();

        $('#free-delivery-form').on('submit', function (e) {
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
                    $('#free-delivery-success-view').attr('href', data.redirect);
                    $('#free-delivery-success-modal').modal('show');
                },
                error: function (xhr) {
                    $submit.prop('disabled', false);

                    // The zone already has a setup — a expected outcome with its
                    // own modal, not a toast among other field errors.
                    if (xhr.status === 409) {
                        $('#free-delivery-exists-modal').modal('show');
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
