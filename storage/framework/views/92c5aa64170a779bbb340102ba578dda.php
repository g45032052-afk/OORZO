
<script>
    "use strict";

    $(document).on('click', '.status_change_alert', function (event) {
        event.preventDefault();

        const url = $(this).data('url');
        const message = $(this).data('message');

        Swal.fire({
            title: '<?php echo e(translate('Are_you_sure?')); ?>',
            text: message,
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: '#FC6A57',
            cancelButtonText: '<?php echo e(translate('no')); ?>',
            confirmButtonText: '<?php echo e(translate('yes')); ?>',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                location.href = url;
            }
        });
    });
</script>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/admin-views/partials/_status_change_alert.blade.php ENDPATH**/ ?>