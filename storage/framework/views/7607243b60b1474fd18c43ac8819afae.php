<!DOCTYPE html>
<?php
$site_direction = session()->get('site_direction');
$country = \App\CentralLogics\Helpers::get_business_settings('country');
$countryCode = strtolower($country ?? 'auto');
?>

<html dir="<?php echo e($site_direction); ?>" lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>"
    class="<?php echo e($site_direction === 'rtl' ? 'active' : ''); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <!-- Title -->
    <title><?php echo $__env->yieldContent('title'); ?></title>
    <!-- Favicon -->
    <?php ($logo = \App\CentralLogics\Helpers::get_business_settings('icon')); ?>
    <link rel="shortcut icon" href="">
    <link rel="icon" type="image/x-icon" href="<?php echo e(dynamicStorage('storage/app/public/business/' . $logo ?? '')); ?>">
    <!-- Font -->
    <link href="<?php echo e(dynamicAsset('assets/admin/css/fonts.css')); ?>" rel="stylesheet">
    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin/css/vendor.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin/vendor/icon-set/style.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin/css/custom.css')); ?>">
    <!-- CSS Front Template -->
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin/plugins/lightbox/css/lightbox.css')); ?>">

    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin/css/owl.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin/css/bootstrap.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin/css/emojionearea.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin/css/theme.minc619.css?v=1.0')); ?>">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin/css/style.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin/intltelinput/css/intlTelInput.css')); ?>">
    <?php if(addon_published_status('ReelsModule')): ?>
        <link rel="stylesheet" href="<?php echo e(dynamicModuleAsset('ReelsModule', 'assets/css/reels.css')); ?>">
    <?php endif; ?>
    <?php echo $__env->yieldPushContent('css_or_js'); ?>
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin/css/toastr.css')); ?>">
</head>

<body class="footer-offset">

    <?php if(getEnvMode() == 'demo'): ?>
        <div id="direction-toggle" class="direction-toggle">
            <i class="tio-settings"></i>
            <span></span>
        </div>
    <?php endif; ?>
    <div id="pre--loader" class="pre--loader">
    </div>

    
    <div id="app-toast-container" class="app-toast-container"></div>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div id="loading" class="initial-hidden">
                    <div class="loading--1">
                        <img width="200" src="<?php echo e(dynamicAsset('assets/admin/img/loader.gif')); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Builder -->
    <?php echo $__env->make('layouts.admin.partials._front-settings', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <!-- End Builder -->

    <!-- JS Preview mode only -->
    <?php echo $__env->make('layouts.admin.partials._header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('layouts.admin.partials._sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <!-- END ONLY DEV -->

    <main id="content" role="main" class="main pointer-event">
        <!-- Content -->
        <?php echo $__env->yieldContent('content'); ?>
        <!-- End Content -->

        <!-- Footer -->
        <?php echo $__env->make('layouts.admin.partials._footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <!-- End Footer -->

        <div class="modal fade" id="popup-modal">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="text-center">
                                    <h2 class="color-8a8a8a">
                                        <i class="tio-shopping-cart-outlined"></i>
                                        <?php echo e(translate('messages.You_have_new_order_Check_Please.')); ?>

                                    </h2>
                                    <hr>
                                    <button
                                        class="btn btn-primary check-order"><?php echo e(translate('messages.Ok_let_me_check')); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="popup-modal-msg">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="text-center">
                                    <h2 class="color-8a8a8a">
                                        <i class="tio-messages"></i> <?php echo e(translate('messages.message_description')); ?>

                                    </h2>
                                    <hr>
                                    <button
                                        class="btn btn-primary check-message"><?php echo e(translate('messages.Ok_let_me_check')); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="toggle-modal">
            <div class="modal-dialog status-warning-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true" class="tio-clear"></span>
                        </button>
                    </div>
                    <div class="modal-body pb-5 pt-0">
                        <div class="max-349 mx-auto mb-20">
                            <div>
                                <div class="text-center">
                                    <img id="toggle-image" alt="" class="mb-20">
                                    <h5 class="modal-title font-medium mb-4" id="toggle-title"></h5>
                                </div>
                                <div class="text-center" id="toggle-message">
                                </div>
                            </div>
                            <div class="btn--container justify-content-center">
                                <button type="button" id="toggle-ok-button"
                                    class="btn btn--primary min-w-120 confirm-Toggle"
                                    data-dismiss="modal"><?php echo e(translate('Ok')); ?></button>
                                <button id="reset_btn" type="reset" class="btn btn--cancel min-w-120"
                                    data-dismiss="modal">
                                    <?php echo e(translate("Cancel")); ?>

                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="modal fade" id="toggle-status-modal">
            <div class="modal-dialog status-warning-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true" class="tio-clear"></span>
                        </button>
                    </div>
                    <div class="modal-body pb-5 pt-0">
                        <div class="max-349 mx-auto mb-20">
                            <div>
                                <div class="text-center">
                                    <img id="toggle-status-image" alt="" class="mb-20 initial-10">
                                    <h5 class="modal-title" id="toggle-status-title"></h5>
                                </div>
                                <div class="text-center" id="toggle-status-message">
                                </div>
                            </div>
                            <div class="btn--container justify-content-center">
                                <button type="button" id="toggle-status-ok-button"
                                    class="btn btn--primary min-w-120 confirm-Status-Toggle"
                                    data-dismiss="modal"><?php echo e(translate('Ok')); ?></button>
                                <button id="reset_btn" type="reset" class="btn btn--cancel min-w-120"
                                    data-dismiss="modal">
                                    <?php echo e(translate("Cancel")); ?>

                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="new-dynamic-submit-model">
            <div class="modal-dialog modal-dialog-centered status-warning-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true" class="tio-clear"></span>
                        </button>
                    </div>
                    <div class="modal-body pb-5 pt-0">
                        <div class="max-349 mx-auto mb-20">
                            <div>
                                <div class="text-center">
                                    <img id="image-src" class="mb-20">
                                    <h5 class="modal-title" id="toggle-title"></h5>
                                </div>
                                <div class="text-center" id="toggle-message">
                                    <h3 id="modal-title"></h3>
                                    <div id="modal-text"></div>
                                </div>

                            </div>
                            <div class="mb-4 d-none" id="note-data">
                                <textarea class="form-control" placeholder="<?php echo e(translate('your_note_here')); ?>"
                                    id="get-text-note" cols="5"></textarea>
                            </div>
                            <div class="btn--container justify-content-center">
                                <div id="hide-buttons">
                                    <button data-dismiss="modal" id="cancel_btn_text"
                                        class="btn btn-outline-secondary min-w-120"><?php echo e(translate("Not_Now")); ?></button>
                                    &nbsp;
                                    <button type="button" id="new-dynamic-ok-button"
                                        class="btn btn-outline-danger confirm-model min-w-120"><?php echo e(translate('Yes')); ?></button>
                                </div>

                                <button data-dismiss="modal" type="button" id="new-dynamic-ok-button-show"
                                    class="btn btn--primary  d-none min-w-120"><?php echo e(translate('Okay')); ?></button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--- Global Image -->
        <div id="imageModal" class="imageModal modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header justify-content-end gap-3 border-0 p-2">
                        <button type="button"
                            class="modal_img-btn border-0 btn-circle rounded-circle bg-section2 shadow-none fs-8 m-0"
                            data-dismiss="modal" aria-label="Close">
                            <i class="tio-clear"></i>
                        </button>
                    </div>
                    <div class="modal-body text-center p-10 pt-0">
                        <div class="imageModal_img_wrapper">
                            <img src="" class="img-fluid imageModal_img" alt="<?php echo e(translate('Preview_Image')); ?>">
                            <div class="imageModal_btn_wrapper">
                                <a href="javascript:" class="btn icon-btn download_btn"
                                    title="<?php echo e(translate('Download')); ?>" download>
                                    <i class="tio-arrow-large-downward"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>




        <div id="password-rules-texts" class="d-none" data-length="<?php echo e(translate('7+ characters')); ?>"
            data-upper="<?php echo e(translate('Uppercase letter')); ?>"
            data-lower="<?php echo e(translate('Lowercase letter')); ?>"
            data-number="<?php echo e(translate('Number')); ?>"
            data-symbol="<?php echo e(translate('Symbol')); ?>"
            data-special-character="<?php echo e(translate('messages.password_special_character')); ?>">

        </div>

    </main>
    <!-- ========== END MAIN CONTENT ========== -->

    <!-- ========== END SECONDARY CONTENTS ========== -->
    <script src="<?php echo e(dynamicAsset('assets/admin/js/custom.js')); ?>"></script>
    <!-- JS Implementing Plugins -->
    <!-- The core Firebase JS SDK is always required and must be listed first -->
    <script src="<?php echo e(dynamicAsset('assets/admin/js/jquery.min.js')); ?>"></script>

    <script>
        let placeholderImageUrl = "<?php echo e(dynamicAsset('assets/admin/img/svg/image-upload.svg')); ?>";
        const iconPath = "<?php echo e(dynamicAsset('assets/admin/svg/icons/file.svg')); ?>";
    </script>

    <script>
        "use strict";
        setTimeout(hide_loader, 1000);
        function hide_loader() {
            $('#pre--loader').removeClass("pre--loader");;
        }

        // Open offcanvas
        $(document).ready(function () {
             $('body').tooltip({
                selector: '[data-toggle="tooltip"]',
                container: 'body',
                placement: 'top',
                trigger: 'hover focus'
            });

            $('.offcanvas-trigger').on('click', function (e) {
                e.preventDefault();
                var target = $(this).data('target');
                $(target).addClass('open');
                $('#offcanvasOverlay').addClass('show');
                $('body').addClass('modal-open');
            });

            // Close offcanvas on close button or overlay click
            $('.offcanvas-close, #offcanvasOverlay').on('click', function () {
                $('.custom-offcanvas').removeClass('open');
                $('#offcanvasOverlay').removeClass('show');
                $('body').removeClass('modal-open');
            });
        });
    </script>
    <script>

    </script>
    <script src="<?php echo e(dynamicAsset('assets/admin/js/firebase.min.js')); ?>"></script>

    <?php echo $__env->yieldPushContent('script'); ?>
    <!-- JS Front -->
    <script
        src="<?php echo e(dynamicAsset('assets/admin/vendor/hs-navbar-vertical-aside/hs-navbar-vertical-aside-mini-cache.js')); ?>"></script>
    <script src="<?php echo e(dynamicAsset('assets/admin/js/vendor.min.js')); ?>"></script>
    <script src="<?php echo e(dynamicAsset('assets/admin/js/theme.min.js')); ?>"></script>
    
    <script src="<?php echo e(dynamicAsset('assets/admin/js/verified-select2.js')); ?>"></script>
    <script src="<?php echo e(dynamicAsset('assets/admin/js/sweet_alert.js')); ?>"></script>
    <script src="<?php echo e(dynamicAsset('assets/admin/js/toastr.js')); ?>"></script>
    <script src="<?php echo e(dynamicAsset('assets/admin/js/owl.min.js')); ?>"></script>
    <script src="<?php echo e(dynamicAsset('assets/admin/intltelinput/js/intlTelInput.min.js')); ?>"></script>

    <script src="<?php echo e(dynamicAsset('assets/admin/plugins/lightbox/js/lightbox.min.js')); ?>"></script>
    <script src="<?php echo e(dynamicAsset('assets/admin/plugins/file-upload/multiple-file-upload.js')); ?>"></script>
    <script>
        $(document).ready(function () {
            var $owl = $(".myOffcanvasOwl");
            var $fraction = $(".swiper-pagination-fraction");

            $owl.owlCarousel({
                items: 1,
                margin: 10,
                loop: true,
                dots: false,
                nav: false,
                autoplay: false,
                smartSpeed: 600,
                onInitialized: updateFraction,
                onTranslated: updateFraction,
            });

            function updateFraction(event) {
                var items = event.item.count;
                var item = event.item.index - event.relatedTarget._clones.length / 2;
                if (item > items || item < 1) item = ((item % items) + items) % items;
                $fraction.text(item + "/" + items);
            }

            $(".owl-prev-btn").click(function () {
                $owl.trigger("prev.owl.carousel");
            });

            $(".owl-next-btn").click(function () {
                $owl.trigger("next.owl.carousel");
            });

            $(document).on("shown.bs.collapse", ".collapse", function () {
                $(this).find(".myOffcanvasOwl").each(function () {
                    $(this).trigger("refresh.owl.carousel");
                });
            });
        });
    </script>


    <script>
        "use strict";

        $('.blinkings').on('mouseover', () => $('.blinkings').removeClass('active'))
        $('.blinkings').addClass('open-shadow')
        setTimeout(() => {
            $('.blinkings').removeClass('active')
        }, 10000);
        setTimeout(() => {
            $('.blinkings').removeClass('open-shadow')
        }, 5000);

        $(function () {
            var owl = $('.single-item-slider');
            owl.owlCarousel({
                autoplay: false,
                items: 1,
                onInitialized: counter,
                onTranslated: counter,
                autoHeight: true,
                dots: true,
                rtl: <?php echo e($site_direction == 'rtl' ? "true" : "false"); ?>

           });

            function counter(event) {
                var element = event.target;         // DOM element, in this example .owl-carousel
                var items = event.item.count;     // Number of items
                var item = event.item.index + 1;     // Position of the current item

                // it loop is true then reset counter from 1
                if (item > items) {
                    item = item - items
                }
                $('.slide-counter').html(+item + "/" + items)
            }
        });
    </script>
    <?php echo Toastr::message(); ?>


    <?php if($errors->any()): ?>
        <script>
            "use strict";
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                toastr.error('<?php echo e(translate($error)); ?>');
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </script>
    <?php endif; ?>

    <script>
        "use strict";
        $(document).on('ready', function () {
            $(".direction-toggle").on("click", function () {
                if ($('html').hasClass('active')) {
                    $('html').removeClass('active')
                    setDirection(1);
                } else {
                    setDirection(0);
                    $('html').addClass('active')
                }
            });
            if ($('html').attr('dir') == "rtl") {
                $(".direction-toggle").find('span').text('Toggle LTR')
            } else {
                $(".direction-toggle").find('span').text('Toggle RTL')
            }

            function setDirection(status) {
                if (status === 1) {
                    $("html").attr('dir', 'ltr');
                    $(".direction-toggle").find('span').text('Toggle RTL')
                } else {
                    $("html").attr('dir', 'rtl');
                    $(".direction-toggle").find('span').text('Toggle LTR')
                }
                $.get({
                    url: '<?php echo e(route('admin.business-settings.site_direction')); ?>',
                    dataType: 'json',
                    data: {
                        status: status,
                    },
                    success: function () {
                    },

                });
            }
        });


        $(document).on('ready', function () {

            if (window.localStorage.getItem('hs-builder-popover') === null) {
                $('#builderPopover').popover('show')
                    .on('shown.bs.popover', function () {
                        $('.popover').last().addClass('popover-dark')
                    });

                $(document).on('click', '#closeBuilderPopover', function () {
                    window.localStorage.setItem('hs-builder-popover', true);
                    $('#builderPopover').popover('dispose');
                });
            } else {
                $('#builderPopover').on('show.bs.popover', function () {
                    return false
                });
            }

            // BUILDER TOGGLE INVOKER
            // =======================================================
            $('.js-navbar-vertical-aside-toggle-invoker').click(function () {
                $('.js-navbar-vertical-aside-toggle-invoker i').tooltip('hide');
            });


            // INITIALIZATION OF NAVBAR VERTICAL NAVIGATION
            // =======================================================
            var sidebar = $('.js-navbar-vertical-aside').hsSideNav();


            // INITIALIZATION OF TOOLTIP IN NAVBAR VERTICAL MENU
            // =======================================================
            $('.js-nav-tooltip-link').tooltip({
                boundary: 'window'
            })

            $(".js-nav-tooltip-link").on("show.bs.tooltip", function (e) {
                if (!$("body").hasClass("navbar-vertical-aside-mini-mode")) {
                    return false;
                }
            });


            // INITIALIZATION OF UNFOLD
            // =======================================================
            $('.js-hs-unfold-invoker').each(function () {
                var unfold = new HSUnfold($(this)).init();
            });


            // INITIALIZATION OF FORM SEARCH
            // =======================================================
            $('.js-form-search').each(function () {
                new HSFormSearch($(this)).init()
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                var verifiedConfig = window.hsSelect2VerifiedTemplate
                    ? { templateResult: window.hsSelect2VerifiedTemplate, templateSelection: window.hsSelect2VerifiedTemplate }
                    : {};
                var select2 = $.HSCore.components.HSSelect2.init($(this), verifiedConfig);
            });


            // INITIALIZATION OF DATERANGEPICKER
            // =======================================================
            $('.js-daterangepicker').daterangepicker();

            $('.js-daterangepicker-times').daterangepicker({
                timePicker: true,
                startDate: moment().startOf('hour'),
                endDate: moment().startOf('hour').add(32, 'hour'),
                locale: {
                    format: 'M/DD hh:mm A'
                }
            });

            var start = moment();
            var end = moment();

            function cb(start, end) {
                $('#js-daterangepicker-predefined .js-daterangepicker-predefined-preview').html(start.format(
                    'MMM D') + ' - ' + end.format('MMM D, YYYY'));
            }

            $('#js-daterangepicker-predefined').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')]
                }
            }, cb);

            cb(start, end);


            // INITIALIZATION OF CLIPBOARD
            // =======================================================
            $('.js-clipboard').each(function () {
                var clipboard = $.HSCore.components.HSClipboard.init(this);
            });
        });
    </script>


    <script>
        "use strict";
        let baseUrl = '<?php echo e(url('/')); ?>';
    </script>
    <script src="<?php echo e(dynamicAsset('assets/admin/js/view-pages/common.js')); ?>"></script>
    <script src="<?php echo e(dynamicAsset('assets/admin/js/keyword-highlighted.js')); ?>"></script>
    <?php if(addon_published_status('ReelsModule') && request()->routeIs('admin.reels.create', 'admin.reels.edit')): ?>
        <script src="<?php echo e(dynamicModuleAsset('ReelsModule', 'assets/js/reel-upload.js')); ?>"></script>
    <?php endif; ?>

    <?php echo $__env->yieldPushContent('script_2'); ?>
    <audio id="myAudio">
        <source src="<?php echo e(dynamicAsset('assets/admin/sound/notification.mp3')); ?>" type="audio/mpeg">
    </audio>

    <script>
        "use strict";
        var audio = document.getElementById("myAudio");

        function playAudio() {
            audio.play();
        }

        function pauseAudio() {
            audio.pause();
        }

        $('.route-alert').on('click', function () {
            let route = $(this).data('url')
            let message = $(this).data('message')
            let title = $(this).data('title')
            let processing = $(this).data('processing')
            route_alert(route, message, title, processing);
        })
        function route_alert(route, message, title = "<?php echo e(translate('messages.are_you_sure')); ?>", processing = false) {
            if (processing) {
                Swal.fire({
                    title: title,
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: 'default',
                    confirmButtonColor: '#FC6A57',
                    cancelButtonText: "<?php echo e(translate('messages.Cancel')); ?>",
                    confirmButtonText: "<?php echo e(translate('messages.Submit')); ?>",
                    inputPlaceholder: "<?php echo e(translate('messages.Enter_processing_time')); ?>",
                    input: 'text',
                    html: message + '<br/>' + '<label><?php echo e(translate('messages.Enter_Processing_time_in_minutes')); ?></label>',
                    inputValue: processing,
                    preConfirm: (processing_time) => {
                        location.href = route + '&processing_time=' + processing_time;
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                })
            } else {
                Swal.fire({
                    title: title,
                    text: message,
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: 'default',
                    confirmButtonColor: '#FC6A57',
                    cancelButtonText: '<?php echo e(translate('messages.No')); ?>',
                    confirmButtonText: '<?php echo e(translate('messages.Yes')); ?>',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        location.href = route;
                    }
                })

            }

        }

        // Delegated, not bound directly: markup that arrives over ajax after ready - the
        // enrolment detail drawer, ajax-reloaded tables - carries .form-alert too, and a
        // direct binding never sees it.
        $(document).on('click', '.form-alert', function () {
            let id = $(this).data('id')
            let message = $(this).data('message')
            Swal.fire({
                title: '<?php echo e(translate('messages.Are you sure?')); ?>',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '<?php echo e(translate('messages.no')); ?>',
                confirmButtonText: '<?php echo e(translate('messages.Yes')); ?>',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $('#' + id).submit()
                }
            })
        })

        //search option
        $(document).ready(function () {
            $('#searchForm input[name="search"]').keyup(function () {
                var searchKeyword = $(this).val().trim();

                if (searchKeyword.length >= 1) {
                    $.ajax({
                        type: 'POST',
                        url: $('#searchForm').attr('action'),
                        data: { search: searchKeyword, _token: $('input[name="_token"]').val() },
                        success: function (response) {
                            if (response.length === 0) {
                                $('#searchResults').html('<div class="fs-16 fw-500 mb-2">' + <?php echo json_encode(translate('Search Result'), 15, 512) ?> + '</div>' +
                                    '<div class="search-list h-300 d-flex flex-column gap-2 justify-content-center align-items-center fs-16">' +
                                    '<img width="30" src="' + <?php echo json_encode(dynamicAsset('assets/admin/img/modal/no-search-found.png'), 15, 512) ?> + '" alt="">' + ' ' +
                                    <?php echo json_encode(translate('No result found'), 15, 512) ?> +
                                    '</div>');

                            } else {
                                var resultHtml = '';
                                response.forEach(function (route) {
                                    var fullRouteWithKeyword = route.fullRoute + '?keyword=' + encodeURIComponent(searchKeyword);
                                    resultHtml += '<a href="' + fullRouteWithKeyword + '" class="search-list-item d-flex flex-column" data-route-name="' + route.routeName + '" data-route-uri="' + route.URI + '" data-route-full-url="' + route.fullRoute + '" aria-current="true">';
                                    resultHtml += '<h5>' + route.routeName + '</h5>';
                                    resultHtml += '<p class="text-muted fs-12 mb-0">' + route.URI + '</p>';
                                    resultHtml += '</a>';
                                });
                                $('#searchResults').html('<div class="fs-16 fw-500 mb-2">' + <?php echo json_encode(translate('Search Result'), 15, 512) ?> + '</div>' + '<div class="search-list d-flex flex-column">' + resultHtml + '</div>');

                                $('.search-list-item').click(function () {
                                    var routeName = $(this).data('route-name');
                                    var routeUri = $(this).data('route-uri');
                                    var routeFullUrl = $(this).data('route-full-url');

                                    $.ajax({
                                        type: 'POST',
                                        url: '<?php echo e(route('admin.store.clicked.route')); ?>',
                                        data: {
                                            routeName: routeName,
                                            routeUri: routeUri,
                                            routeFullUrl: routeFullUrl,
                                            searchKeyword: searchKeyword,
                                            _token: $('input[name="_token"]').val()
                                        },
                                        success: function (response) {
                                            console.log(response.message);
                                        },
                                        error: function (xhr, status, error) {
                                            console.error(xhr.responseText);
                                        }
                                    });
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
                } else {
                    $('#searchResults').html('<div class="text-center text-muted py-5"><?php echo e(translate('Write a minimum of one characters.')); ?>.</div>');
                }
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.ctrlKey && event.key === 'k') {
                event.preventDefault();
                document.getElementById('modalOpener').click();
            }
        });

        $(document).ready(function () {
            $("#staticBackdrop").on("shown.bs.modal", function () {
                $(this).find("#searchForm input[type=search]").val('');
                $('#searchResults').html('<div class="text-center text-muted py-5"><?php echo e(translate('Loading recent searches')); ?>...</div>');
                $(this).find("#searchForm input[type=search]").focus();

                $.ajax({
                    type: 'GET',
                    url: '<?php echo e(route('admin.recent.search')); ?>',
                    success: function (response) {
                        if (response.length === 0) {
                            $('#searchResults').html('<div class="text-center text-muted py-5"><?php echo e(translate('It appears that you have not yet searched.')); ?>.</div>');
                        } else {
                            var resultHtml = '';
                            response.forEach(function (route) {
                                resultHtml += '<a href="' + route.route_full_url + '" class="search-list-item d-flex flex-column" data-route-name="' + route.route_name + '" data-route-uri="' + route.route_uri + '" data-route-full-url="' + route.route_full_url + '" aria-current="true">';
                                resultHtml += '<h5>' + route.route_name + '</h5>';
                                resultHtml += '<p class="text-muted fs-12  mb-0">' + route.route_uri + '</p>';
                                resultHtml += '</a>';
                            });
                            $('#searchResults').html('<div class="recent-search fs-16 fw-500 animate">' +
                                <?php echo json_encode(translate('Recent Search'), 15, 512) ?> + '<div class="search-list d-flex flex-column mt-2">' + resultHtml + '</div></div>');

                            $('.search-list-item').click(function () {
                                var routeName = $(this).data('route-name');
                                var routeUri = $(this).data('route-uri');
                                var routeFullUrl = $(this).data('route-full-url');
                                var searchKeyword = $('input[type=search]').val().trim();

                                $.ajax({
                                    type: 'POST',
                                    url: '<?php echo e(route('admin.store.clicked.route')); ?>',
                                    data: {
                                        routeName: routeName,
                                        routeUri: routeUri,
                                        routeFullUrl: routeFullUrl,
                                        searchKeyword: searchKeyword,
                                        _token: $('input[name="_token"]').val()
                                    },
                                    success: function (response) {
                                        console.log(response.message);
                                    },
                                    error: function (xhr, status, error) {
                                        console.error(xhr.responseText);
                                    }
                                });
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error(xhr.responseText);
                        $('#searchResults').html('<div class="text-center text-muted py-5"><?php echo e(translate('Error loading recent searches')); ?>.</div>');
                    }
                });
            });
        });

        $("#staticBackdrop").on("hidden.bs.modal", function () {
            $('#searchResults').empty();
        });

        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('search', function () {
            if (!this.value.trim()) {
                $('#searchResults').html('<div class="text-center text-muted py-5"></div>');
            }
        });

        $('#searchForm').submit(function (event) {
            event.preventDefault();
        });

        <?php ($fcm_credentials = \App\CentralLogics\Helpers::get_business_settings('fcm_credentials')); ?>
        var firebaseConfig = {
            apiKey: "<?php echo e(isset($fcm_credentials['apiKey']) ? $fcm_credentials['apiKey'] : ''); ?>",
            authDomain: "<?php echo e(isset($fcm_credentials['authDomain']) ? $fcm_credentials['authDomain'] : ''); ?>",
            projectId: "<?php echo e(isset($fcm_credentials['projectId']) ? $fcm_credentials['projectId'] : ''); ?>",
            storageBucket: "<?php echo e(isset($fcm_credentials['storageBucket']) ? $fcm_credentials['storageBucket'] : ''); ?>",
            messagingSenderId: "<?php echo e(isset($fcm_credentials['messagingSenderId']) ? $fcm_credentials['messagingSenderId'] : ''); ?>",
            appId: "<?php echo e(isset($fcm_credentials['appId']) ? $fcm_credentials['appId'] : ''); ?>",
            measurementId: "<?php echo e(isset($fcm_credentials['measurementId']) ? $fcm_credentials['measurementId'] : ''); ?>"
        };
        <?php if(isset($fcm_credentials['apiKey']) && is_string($fcm_credentials['apiKey']) && strlen($fcm_credentials['apiKey']) > 3): ?>
            firebase.initializeApp(firebaseConfig);
            const messaging = firebase.messaging();
        <?php endif; ?>

            function startFCM() {
                messaging
                    .requestPermission()
                    .then(function () {
                        return messaging.getToken();
                    })
                    .then(function (token) {
                        // console.log('FCM Token:', token);
                        // Send the token to your backend to subscribe to topic
                        subscribeTokenToBackend(token, 'admin_message');
                    }).catch(function (error) {
                        console.error('Error getting permission or token:', error.message);
                    });
            }

        function subscribeTokenToBackend(token, topic) {
            fetch('<?php echo e(url('/')); ?>/subscribeToTopic', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                body: JSON.stringify({ token: token, topic: topic })
            }).then(response => {
                if (response.status < 200 || response.status >= 400) {
                    return response.text().then(text => {
                        throw new Error(`Error subscribing to topic: ${response.status} - ${text}`);
                    });
                }
                console.log(`Subscribed to "${topic}"`);
            }).catch(error => {
                console.error('Subscription error:', error);
            });
        }

        function getUrlParameter(sParam) {
            var sPageURL = window.location.search.substring(1);
            var sURLVariables = sPageURL.split('&');
            for (var i = 0; i < sURLVariables.length; i++) {
                var sParameterName = sURLVariables[i].split('=');
                if (sParameterName[0] == sParam) {
                    return sParameterName[1];
                }
            }
        }

        function conversationList() {
            var tab = getUrlParameter('tab');
            $.ajax({
                url: "<?php echo e(route('admin.message.list')); ?>" + '?tab=' + tab,
                success: function (data) {
                    $('#conversation-list').empty();
                    $("#conversation-list").append(data.html);
                    var user_id = getUrlParameter('user');
                    $('.customer-list').removeClass('conv-active');
                    $('#customer-' + user_id).addClass('conv-active');
                }
            })
        }

        function conversationView() {
            var conversation_id = getUrlParameter('conversation');
            var user_id = getUrlParameter('user');
            var url = '<?php echo e(url('/')); ?>/admin/message/view/' + conversation_id + '/' + user_id;
            $.ajax({
                url: url,
                success: function (data) {
                    $('#view-conversation').html(data.view);
                }
            })
        }

        function vendorConversationView() {
            var conversation_id = getUrlParameter('conversation');
            var user_id = getUrlParameter('user');
            var url = '<?php echo e(url('/')); ?>/admin/restaurant/message/' + conversation_id + '/' + user_id;
            $.ajax({
                url: url,
                success: function (data) {
                    $('#vendor-view-conversation').html(data.view);
                }
            })
        }

        function dmConversationView() {
            var conversation_id = getUrlParameter('conversation');
            var user_id = getUrlParameter('user');
            var url = '<?php echo e(url('/')); ?>/admin/delivery-man/message/' + conversation_id + '/' + user_id;
            $.ajax({
                url: url,
                success: function (data) {
                    $('#dm-view-conversation').html(data.view);
                }
            })
        }
        <?php ($order_notification_type = \App\CentralLogics\Helpers::get_business_settings('order_notification_type') ?? 'firebase'); ?>
        var new_order_type = 'restaurant_order';

        <?php if(isset($fcm_credentials['apiKey']) && is_string($fcm_credentials['apiKey']) && strlen($fcm_credentials['apiKey']) > 3): ?>

        messaging.onMessage(function (payload) {
            console.log(payload.data);
            if (payload.data.order_id && payload.data.type == "order_request") {
                <?php ($admin_order_notification = \App\CentralLogics\Helpers::get_business_settings('admin_order_notification') ?? 0); ?>
                <?php if(\App\CentralLogics\Helpers::module_permission_check('order') && $admin_order_notification && $order_notification_type == 'firebase'): ?>
                    new_order_type = payload.data.order_type
                    playAudio();
                    $('#popup-modal').appendTo("body").modal('show');
                <?php endif; ?>

            } else if (payload.data.type == 'message') {
                var conversation_id = getUrlParameter('conversation');
                var user_id = getUrlParameter('user');
                var url = '<?php echo e(url('/')); ?>/admin/message/view/' + conversation_id + '/' + user_id;
                console.log(url);
                $.ajax({
                    url: url,
                    success: function (data) {
                        $('#view-conversation').html(data.view);
                    }
                })
                toastr.success('<?php echo e(translate('New_message_arrived')); ?>', {
                    CloseButton: true,
                    ProgressBar: true
                });

                if ($('#conversation-list').scrollTop() == 0) {
                    conversationList();
                }
            }
        });
        <?php endif; ?>


        <?php if(\App\CentralLogics\Helpers::module_permission_check('order') && $order_notification_type == 'manual'): ?>
        <?php ($admin_order_notification = \App\CentralLogics\Helpers::get_business_settings('admin_order_notification') ?? 0); ?>
        <?php if($admin_order_notification): ?>
            setInterval(function () {
                $.get({
                    url: '<?php echo e(route('admin.get-restaurant-data')); ?>',
                    dataType: 'json',
                    success: function (response) {
                        let data = response.data;
                        new_order_type = data.type;
                        if (data.new_order > 0) {
                            playAudio();
                            $('#popup-modal').appendTo("body").modal('show');
                        }
                    },
                });
            }, 10000);
        <?php endif; ?>
        <?php endif; ?>

        <?php if(isset($fcm_credentials['apiKey']) && is_string($fcm_credentials['apiKey']) && strlen($fcm_credentials['apiKey']) > 3): ?>
            startFCM();
        <?php endif; ?>
        conversationList();

        if (getUrlParameter('conversation')) {
            conversationView();
            vendorConversationView();
            dmConversationView();
        }

        $(document).on('click', '.call-demo', function (e) {
            <?php if(getEnvMode() == 'demo'): ?>
                toastr.info('<?php echo e(translate('Update option is disabled for demo!')); ?>', {
                    CloseButton: true,
                    ProgressBar: true
                });
                e.preventDefault();
            <?php endif; ?>
        });
        $(document).on('click', '.check-order', function () {
            location.href = '<?php echo e(route('admin.order.list', ['status' => 'all'])); ?>';
        });
        $(document).on('click', '.check-message', function () {
            var tab = getUrlParameter('tab');
            location.href = '<?php echo e(route('admin.message.list')); ?>' + '?tab=' + tab;
        });

        $(window).on('load', () => $('.pre--loader').fadeOut(600))

        $('.log-out').on('click', function () {
            Swal.fire({
                title: '<?php echo e(translate('Do_You_Want_To_Sign_Out_?')); ?>',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonColor: '#FC6A57',
                cancelButtonColor: '#363636',
                confirmButtonText: `<?php echo e(translate('yes')); ?>`,
                cancelButtonText: `<?php echo e(translate('cancel')); ?>`,
            }).then((result) => {
                if (result.value) {
                    location.href = '<?php echo e(route('logout')); ?>';
                }
            })
        });


        function initTelInputs() {
            const inputs = document.querySelectorAll('input[type="tel"]');

            inputs.forEach(input => {

                const iti = window.intlTelInput(input, {
                    initialCountry: "<?php echo e($countryCode); ?>",
                    utilsScript: "<?php echo e(dynamicAsset('assets/admin/intltelinput/js/utils.js')); ?>",
                    autoInsertDialCode: true,
                    nationalMode: false,
                    formatOnDisplay: false,
                    strictMode: true,
                    <?php if(\App\CentralLogics\Helpers::get_business_settings('country_picker_status') != 1): ?>
                        onlyCountries: ["<?php echo e($countryCode); ?>"],
                    <?php endif; ?>
                });

                const restoreDialCode = () => {
                    if (input.value.trim() === '') {
                        input.value = '+' + iti.getSelectedCountryData().dialCode;
                    }
                };

                input.addEventListener('blur', restoreDialCode);
                input.closest('form')?.addEventListener('submit', restoreDialCode);
            });

            $(document).off('keyup.telinput').on('keyup.telinput', 'input[type="tel"]', function () {
                const iti = window.intlTelInputGlobals.getInstance(this);
                if (!iti) return;

                let val = $(this).val();
                if (val.trim() === '') {
                    val = '+' + iti.getSelectedCountryData().dialCode;
                } else {
                    const plus = val.startsWith('+') ? '+' : '';
                    val = plus + val.replace(/[^\d]/g, '');
                }

                $(this).val(val);
            });
        }


        document.addEventListener('DOMContentLoaded', function () {
            initTelInputs();
            $('body').tooltip({
                selector: 'input[readonly], input[disabled]',
                title: function () {
                    return $(this).attr('disable-title') || "<?php echo e(translate('This field is disabled')); ?>";
                },
                trigger: 'hover',
                placement: 'top',
                html: true
            });
        });

    </script>

    <script src="<?php echo e(dynamicAsset('assets/admin/js/external-navigation-guard.js')); ?>"></script>
</body>

</html>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/layouts/admin/app.blade.php ENDPATH**/ ?>