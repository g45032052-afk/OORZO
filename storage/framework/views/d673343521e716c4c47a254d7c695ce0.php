<?php $__env->startSection('title', translate('messages.Create_Reels')); ?>
<?php $__env->startSection('reels', 'active'); ?>
<?php $__env->startSection('reels_create', 'active'); ?>

<?php $__env->startPush('css_or_js'); ?>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo e(dynamicAsset('assets/admin/css/daterangepicker.css')); ?>"/>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content container-fluid">
        <h2 class="fs-20 text-capitalize lh-1 mb-20"><?php echo e(translate('messages.Create_Reels')); ?></h2>

        <form id="reel-form" action="<?php echo e(route('admin.reels.store')); ?>" method="POST" class="validate-form" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <?php echo $__env->make('reelsmodule::admin.reels.partials._form', ['isEdit' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <script type="text/javascript" src="<?php echo e(dynamicAsset('assets/admin/js/moment.min.js')); ?>"></script>
    <script type="text/javascript" src="<?php echo e(dynamicAsset('assets/admin/js/daterangepicker.min.js')); ?>"></script>
    <script>
        "use strict";

        function initReelDateRange() {
            const $dates = $('input[name="dates"]');
            if (!$dates.length) {
                return;
            }

            $dates.daterangepicker({
                drops: 'up',
                opens: 'right',
                startDate: moment().startOf('day'),
                endDate: moment().endOf('day'),
                minDate: new Date(),
                autoUpdateInput: false,
                autoApply: false,
                alwaysShowCalendars: true,
                locale: {
                    format: 'MM/DD/YYYY',
                    cancelLabel: 'Clear'
                },
            });

            $dates.on('apply.daterangepicker', function (ev, picker) {
                const value = picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY');
                $(this).val(value).data('last-value', value);
                clearReelDateValidation(this);
            });

            $dates.on('cancel.daterangepicker', function () {
                $(this).val('').data('last-value', '');
            });
        }

        function clearReelDateValidation(input) {
            if (window.FormValidation) {
                window.FormValidation.clearError(input);
            }
            $(input).removeClass('is-invalid');
        }

        function toggleAlwaysVisibleState() {
            const $dates = $('#dates');
            const dates = $dates[0];
            const checked = $('#is_always_visible').is(':checked');

            if (checked && $dates.val()) {
                $dates.data('last-value', $dates.val());
            }

            $dates.prop('disabled', checked).prop('required', !checked);
            if (checked) {
                $dates.val('');
            } else if (!$dates.val() && $dates.data('last-value')) {
                $dates.val($dates.data('last-value'));
            }
            clearReelDateValidation(dates);
        }

        function updateTextCounters() {
            $('.reel-des-textarea').each(function () {
                const count = $(this).val().length;
                $(this).siblings('.text-counting').text(count + '/200');
            });
        }

        function submitReelForm(form) {
            const formData = new FormData(form);
            const thumbnailInput = form.querySelector('input[name="thumbnail"]');
            const videoInput = form.querySelector('input[name="video"]');

            if (thumbnailInput && thumbnailInput.files && thumbnailInput.files[0]) {
                formData.set('thumbnail', thumbnailInput.files[0]);
            }

            if (videoInput && videoInput.files && videoInput.files[0]) {
                formData.set('video', videoInput.files[0]);
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: $(form).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (response) {
                    if (response.errors && response.errors.length) {
                        response.errors.forEach(function (error) {
                            toastr.error(error.message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        });
                        return;
                    }

                    toastr.success(response.message);
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                },
                error: function (xhr) {
                    const response = xhr.responseJSON || {};
                    const errors = Array.isArray(response.errors) ? response.errors : [];

                    if (errors.length) {
                        errors.forEach(function (error) {
                            toastr.error(error.message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        });
                        return;
                    }

                    toastr.error(response.message || '<?php echo e(translate('messages.Something_went_wrong')); ?>', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                complete: function () {
                    $('#loading').hide();
                }
            });
        }

        $(function () {
            initReelDateRange();
            toggleAlwaysVisibleState();
            updateTextCounters();

            $(document).on('change', '#is_always_visible', toggleAlwaysVisibleState);
            $(document).on('input', '.reel-des-textarea', updateTextCounters);

            $('#reel-form').on('submit', function (e) {
                e.preventDefault();
                submitReelForm(this);
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\Modules/ReelsModule\Resources/views/admin/reels/create.blade.php ENDPATH**/ ?>