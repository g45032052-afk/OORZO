<script>
    "use strict";

    $(document).ready(function() {
        $('.js-select2-custom').each(function () {
            if (window?.$.HSCore?.components?.HSSelect2) {
                $.HSCore.components.HSSelect2.init($(this));
            }
        });

        $('.filter-button-show').on('click', function(){
            $('#datatableFilterSidebar,.hs-unfold-overlay').show(500);
            $('body').addClass('modal-open');
        });

        $('.filter-button-hide').on('click', function(){
            $('#datatableFilterSidebar,.hs-unfold-overlay').hide(500);
            $('body').removeClass('modal-open');
        });

        function toggleCustomDate(wrapper) {
            wrapper.find('#custom_date_wrapper').toggleClass('d-none', wrapper.find('.date-type-select').val() !== 'custom');
        }

        $(document).on('change', '.date-type-select', function () {
            let wrapper = $(this).closest('.sidebar-body');
            toggleCustomDate(wrapper);
        });

        const reelStatusSelector = '#reels_filter_form input[name="reel_status[]"]';
        const allReelStatusSelector = '#reels_filter_form input[name="reel_status[]"][value="all"]';

        function syncReelStatusAllCheckbox() {
            const $statusCheckboxes = $(reelStatusSelector).not('[value="all"]');
            const $allCheckbox = $(allReelStatusSelector);
            const allSelected = $statusCheckboxes.length > 0 && $statusCheckboxes.length === $statusCheckboxes.filter(':checked').length;

            $allCheckbox.prop('checked', allSelected);
        }

        $(document).on('change', reelStatusSelector, function () {
            const value = $(this).val();
            const isChecked = $(this).is(':checked');
            const $statusCheckboxes = $(reelStatusSelector).not('[value="all"]');
            const $allCheckbox = $(allReelStatusSelector);

            if (value === 'all') {
                $statusCheckboxes.prop('checked', isChecked);
            } else if (!isChecked) {
                $allCheckbox.prop('checked', false);
            } else {
                syncReelStatusAllCheckbox();
            }
        });

        syncReelStatusAllCheckbox();

        $('.sidebar-body').each(function () {
            toggleCustomDate($(this));
        });

        // Keep the custom date range valid: the end date can never be earlier
        // than the start date. Update each input's bound when the other changes.
        $(document).on('change', '#start_date', function () {
            const startDate = $(this).val();
            const $endDate = $('#end_date');

            $endDate.attr('min', startDate);

            if (startDate && $endDate.val() && $endDate.val() < startDate) {
                $endDate.val('');
            }
        });

        $(document).on('change', '#end_date', function () {
            $('#start_date').attr('max', $(this).val());
        });

        // After a search/filter/pagination the page reloads as a GET request and
        // would otherwise land at the very top. Keep the user focused on the list
        // by scrolling it into view when one of those params is present.
        (function focusReelsListAfterFilter() {
            const reelsListSection = document.getElementById('reels-list-section');
            if (!reelsListSection) {
                return;
            }

            const params = new URLSearchParams(window.location.search);
            const filterKeys = [
                'search', 'status_filter', 'restaurant_ids', 'reel_status',
                'sort_by', 'filter_date', 'start_date', 'end_date', 'page',
            ];

            let hasFilter = false;
            for (const key of params.keys()) {
                if (filterKeys.indexOf(key.replace('[]', '')) !== -1) {
                    hasFilter = true;
                    break;
                }
            }

            if (hasFilter) {
                setTimeout(function () {
                    reelsListSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 200);
            }
        })();
    });

    $(document).on('click', '.reels-video-wrapper', function (e) {
        if ($(e.target).closest('.reels-close-btn').length) {
            return;
        }

        const wrapper = $(this);
        const video = wrapper.find('.reels-video').get(0);

        if (!video) {
            return;
        }

        $('video.reels-video').each(function () {
            this.pause();
            this.currentTime = 0;
            const currentWrapper = $(this).closest('.reels-video-wrapper');
            currentWrapper.find('.reels-video').hide();
            currentWrapper.find('.reels-thumbnail, .reels-play-btn').show();
            currentWrapper.find('.reels-close-btn').hide();
        });

        wrapper.find('.reels-thumbnail, .reels-play-btn').hide();
        wrapper.find('.reels-video').show();
        wrapper.find('.reels-close-btn').css('display', 'flex');
        video.load();
        video.play().catch(function () {
            wrapper.find('.reels-video').attr('controls', true);
        });
    });

    $(document).on('click', '.reels-close-btn', function (e) {
        e.stopPropagation();

        const wrapper = $(this).closest('.reels-video-wrapper');
        const video = wrapper.find('.reels-video').get(0);

        if (video) {
            video.pause();
            video.currentTime = 0;
        }

        wrapper.find('.reels-video').hide();
        wrapper.find('.reels-thumbnail, .reels-play-btn').show();
        wrapper.find('.reels-close-btn').hide();
    });

    $(document).on('click', '.offcanvas-close, #offcanvasOverlay', function () {
        $('video.reels-video').each(function () {
            this.pause();
            this.currentTime = 0;
            const wrapper = $(this).closest('.reels-video-wrapper');
            wrapper.find('.reels-video').hide();
            wrapper.find('.reels-thumbnail, .reels-play-btn').show();
            wrapper.find('.reels-close-btn').hide();
        });
    });
</script>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\Modules/ReelsModule\Resources/views/shared/_reels-scripts.blade.php ENDPATH**/ ?>