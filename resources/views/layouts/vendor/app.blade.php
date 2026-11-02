<!DOCTYPE html>
<?php
    if (getEnvMode() == 'demo') {
        $site_direction = session()->get('site_direction_vendor');
    }else{
        $site_direction = session()->has('vendor_site_direction')?session()->get('vendor_site_direction'):'ltr';
    }
    $country=\App\Models\BusinessSetting::where('key','country')->first();
            $countryCode= strtolower($country?$country->value:'auto');
    $verifiedBadgePopupRestaurant = \App\CentralLogics\Helpers::get_restaurant_data();
    $verifiedBadgePopupShow = (bool) (\App\CentralLogics\Helpers::get_business_settings('verified_seller_badge')
        && $verifiedBadgePopupRestaurant?->restaurant_config?->verified_seller
        && !($verifiedBadgePopupRestaurant?->restaurant_config?->has_seen_verified_badge_popup));
?>
<html dir="{{ $site_direction }}" lang="{{ str_replace('_', '-', app()->getLocale()) }}"  class="{{ $site_direction === 'rtl'?'active':'' }}"><head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Title -->
    <title>@yield('title')</title>
    <!-- Favicon -->
    @php($logo=\App\Models\BusinessSetting::where(['key'=>'icon'])->first()->value)
    <link rel="shortcut icon" href="">
    <link rel="icon" type="image/x-icon" href="{{dynamicStorage('storage/app/public/business/'.$logo??'')}}">
    <!-- Font -->
    <link href="{{dynamicAsset('assets/admin/css/fonts.css')}}" rel="stylesheet">
    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="{{dynamicAsset('assets/admin/css/vendor.min.css')}}">
    <link rel="stylesheet" href="{{dynamicAsset('assets/admin/vendor/icon-set/style.css')}}">
    <!-- CSS Front Template -->
    <link rel="stylesheet" href="{{dynamicAsset('assets/admin/css/owl.min.css')}}">
    <link rel="stylesheet" href="{{ dynamicAsset('assets/admin/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset('assets/admin/css/theme.minc619.css?v=1.0') }}">
    <link rel="stylesheet" href="{{ dynamicAsset('assets/admin/css/style.css') }}">
    <link  rel="stylesheet" href="{{dynamicAsset('assets/admin/plugins/lightbox/css/lightbox.css')}}">
    <!-- Provider Panel Update CSS -->
    <link rel="stylesheet" href="{{dynamicAsset('assets/admin/css/vendor.css')}}">
    <link rel="stylesheet" href="{{dynamicAsset('assets/admin/intltelinput/css/intlTelInput.css')}}">
    @if(addon_published_status('ReelsModule'))
        <link rel="stylesheet" href="{{ dynamicModuleAsset('ReelsModule', 'assets/css/reels.css') }}">
    @endif

    @stack('css_or_js')

    <script src="{{dynamicAsset('assets/admin/vendor/hs-navbar-vertical-aside/hs-navbar-vertical-aside-mini-cache.js')}}"></script>
    <link rel="stylesheet" href="{{dynamicAsset('assets/admin/css/toastr.css')}}">
</head>

<body class="footer-offset">

    @if (getEnvMode()=='demo')
    <div class="direction-toggle">
        <i class="tio-settings"></i>
        <span></span>
    </div>
    @endif

    <div id="pre--loader" class="pre--loader">
    </div>
{{--loader--}}
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div id="loading" class="initial-hidden">
                <div class="loading--1">
                    <img width="200" src="{{dynamicAsset('assets/admin/img/loader.gif')}}">
                </div>
            </div>
        </div>
    </div>
</div>
{{--loader--}}

<!-- Builder -->
@include('layouts.vendor.partials._front-settings')
<!-- End Builder -->

<!-- JS Preview mode only -->
@include('layouts.vendor.partials._sidebar')
<!-- END ONLY DEV -->

<main id="content" role="main" class="main pointer-event">
@include('layouts.vendor.partials._header')
    <!-- Content -->
@yield('content')
<!-- End Content -->

    <!-- Footer -->
@include('layouts.vendor.partials._footer')
<!-- End Footer -->

{{-- Dashboard only, and only for an invitation the restaurant has not opened yet. It used
     to render on every vendor page, so answering it was a race against the next page load
     putting it back up. --}}
@if(request()->routeIs('vendor.dashboard'))
    @include('layouts.vendor.partials._admin_request_alert')
@endif

    <div class="modal fade" id="popup-modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="text-center">
                                <h2 class="color-8a8a8a">
                                    <i class="tio-shopping-cart-outlined"></i> {{translate('messages.You have new order, Check Please.')}}
                                </h2>
                                <hr>
                                <button  class="btn btn-primary check-order">{{translate('messages.Ok, let me check')}}</button>
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
                                <h2 class="8a8a8a">
                                    <i class="tio-messages"></i> {{translate('messages.message_description')}}
                                </h2>
                                <hr>
                                <button class="btn btn-primary check-message">{{translate('messages.Ok, let me check')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="app-toast-container" class="app-toast-container"></div>

    {{-- Image View Modal --}}
    <div id="imageModal" class="imageModal modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header justify-content-end gap-3 border-0 p-2">
                    <button type="button" class="modal_img-btn border-0 btn-circle rounded-circle bg-section2 shadow-none fs-8 m-0"
                            data-dismiss="modal" aria-label="Close">
                            <i class="tio-clear"></i>
                    </button>
                </div>
                <div class="modal-body text-center p-10 pt-0">
                    <div class="imageModal_img_wrapper">
                        <img src="" class="img-fluid imageModal_img" alt="{{ translate('Preview_Image') }}">
                        <div class="imageModal_btn_wrapper">
                            <a href="javascript:" class="btn icon-btn download_btn" title="{{ translate('Download') }}" download>
                                <i class="tio-arrow-large-downward"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <div id="password-rules-texts" class="d-none" data-length="{{ translate('7+ characters') }}"
            data-upper="{{ translate('Uppercase letter') }}"
            data-lower="{{ translate('Lowercase letter') }}"
            data-number="{{ translate('Number') }}"
            data-symbol="{{ translate('Symbol') }}"
            data-special-character="{{ translate('messages.password_special_character') }}">

        </div>

</main>
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
                                <h5 class="modal-title" id="toggle-title"></h5>
                            </div>
                            <div class="text-center" id="toggle-message">
                            </div>
                        </div>
                        <div class="btn--container justify-content-center">
                            <button type="button" id="toggle-ok-button" class="btn btn--primary min-w-120 confirm-Toggle" data-dismiss="modal" >{{translate('Ok')}}</button>
                            <button id="reset_btn" type="reset" class="btn btn--cancel min-w-120" data-dismiss="modal">
                                {{translate("Cancel")}}
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
                                <img id="toggle-status-image" alt="" class="mb-20">
                                <h5 class="modal-title" id="toggle-status-title"></h5>
                            </div>
                            <div class="text-center" id="toggle-status-message">
                            </div>
                        </div>
                        <div class="btn--container justify-content-center">
                            <button type="button" id="toggle-status-ok-button" class="btn btn--primary min-w-120 confirm-Status-Toggle" data-dismiss="modal" >{{translate('Ok')}}</button>
                            <button id="reset_btn" type="reset" class="btn btn--cancel min-w-120" data-dismiss="modal">
                                {{translate("Cancel")}}
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
                                <textarea class="form-control" placeholder="{{ translate('your_note_here') }}" id="get-text-note" cols="5" ></textarea>
                            </div>
                        <div class="btn--container justify-content-center">
                            <div id="hide-buttons">
                                <button data-dismiss="modal" id="cancel_btn_text" class="btn btn-outline-secondary min-w-120" >{{translate("Not_Now")}}</button> &nbsp;
                                <button type="button" id="new-dynamic-ok-button" class="btn btn-outline-danger confirm-model min-w-120">{{translate('Yes')}}</button>
                            </div>

                            <button data-dismiss="modal"  type="button" id="new-dynamic-ok-button-show" class="btn btn--primary  d-none min-w-120">{{translate('Okay')}}</button>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="{{dynamicAsset('assets/admin/js/custom.js')}}"></script>
<script src="{{dynamicAsset('assets/admin/js/jquery.min.js')}}"></script>

    <script>
        "use strict";
        setTimeout(hide_loader, 1000);
            function hide_loader(){
            $('#pre--loader').removeClass("pre--loader");;
        }

        // Open offcanvas
        $(document).ready(function () {
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
<script src="{{dynamicAsset('assets/admin/js/firebase.min.js')}}"></script>

<!-- JS Implementing Plugins -->

@stack('script')
<script src="{{dynamicAsset('assets/admin/js/vendor.min.js')}}"></script>
<script src="{{dynamicAsset('assets/admin/js/theme.min.js')}}"></script>
{{-- Centralized verified-restaurant badge for every select2 dropdown (must load right after select2/theme). --}}
<script src="{{dynamicAsset('assets/admin/js/verified-select2.js')}}"></script>
<script src="{{dynamicAsset('assets/admin/js/sweet_alert.js')}}"></script>
<script src="{{dynamicAsset('assets/admin/js/toastr.js')}}"></script>
<script src="{{dynamicAsset('assets/admin/js/owl.min.js')}}"></script>
<script src="{{ dynamicAsset('assets/admin/plugins/lightbox/js/lightbox.min.js')}}"></script>

<script src="{{dynamicAsset('assets/admin/intltelinput/js/intlTelInput.min.js')}}"></script>



{!! Toastr::message() !!}

@if ($errors->any())
    <script>
            "use strict";
        @foreach($errors->all() as $error)
        toastr.error('{{translate($error)}}');
        @endforeach
    </script>
@endif

<script>
    "use strict";

    $('.blinkings').on('mouseover', ()=> $('.blinkings').removeClass('active'))
    $('.blinkings').addClass('open-shadow')
    setTimeout(() => {
        $('.blinkings').removeClass('active')
    }, 10000);
    setTimeout(() => {
        $('.blinkings').removeClass('open-shadow')
    }, 5000);

    $(function(){
        var owl = $('.single-item-slider');
        owl.owlCarousel({
            autoplay: false,
            items:1,
            onInitialized  : counter,
            onTranslated : counter,
            autoHeight: true,
            dots: true
        });

        function counter(event) {
            var element   = event.target;         // DOM element, in this example .owl-carousel
                var items     = event.item.count;     // Number of items
                var item      = event.item.index + 1;     // Position of the current item

            // it loop is true then reset counter from 1
            if(item > items) {
                item = item - items
            }
            $('.slide-counter').html(+item+"/"+items)
        }
    });
    $(document).on('ready', function(){
        $(".direction-toggle").on("click", function () {
            if($('html').hasClass('active')){
                $('html').removeClass('active')
                setDirection(1);
            }else {
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
            if (status == 1) {
                $("html").attr('dir', 'ltr');
                $(".direction-toggle").find('span').text('Toggle RTL')
            } else {
                $("html").attr('dir', 'rtl');
                $(".direction-toggle").find('span').text('Toggle LTR')
            }
            $.get({
                    url: '{{ route('vendor.business-settings.site_direction_vendor') }}',
                    dataType: 'json',
                    data: {
                        status: status,
                    },
                    success: function() {
                        alert(ok);
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
        $('.js-nav-tooltip-link').tooltip({boundary: 'window'})

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
            $('#js-daterangepicker-predefined .js-daterangepicker-predefined-preview').html(start.format('MMM D') + ' - ' + end.format('MMM D, YYYY'));
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
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);

        cb(start, end);

        $('.js-clipboard').each(function () {
            var clipboard = $.HSCore.components.HSClipboard.init(this);
        });
    });
</script>

@stack('script_2')
    <script src="{{dynamicAsset('assets/admin/js/view-pages/common.js')}}"></script>
    <script src="{{dynamicAsset('assets/admin/js/keyword-highlighted.js')}}"></script>
    @if(addon_published_status('ReelsModule'))
        <script src="{{ dynamicModuleAsset('ReelsModule', 'assets/js/reel-upload.js') }}"></script>
    @endif
    <script src="{{dynamicAsset('assets/admin/js/theme-builder/theme-builder.js')}}"></script>

<audio id="myAudio">
    <source src="{{dynamicAsset('assets/admin/sound/notification.mp3')}}" type="audio/mpeg">
</audio>

<script>
        "use strict";
    let audio = document.getElementById("myAudio");

    function playAudio() {
        audio.play();
    }

    function pauseAudio() {
        audio.pause();
    }


    function route_alert(route, message) {
        Swal.fire({
            title: '{{ translate('messages.Are you sure ?') }}',
            text: message,
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: '#FC6A57',
            cancelButtonText: '{{ translate('messages.No') }}',
            confirmButtonText: '{{ translate('messages.Yes') }}',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                location.href = route;
            }
        })
    }

    // Delegated, not bound directly: markup that arrives over ajax after ready - the offer
    // detail drawers, ajax-reloaded tables - carries .form-alert too, and a direct binding
    // never sees it.
    $(document).on('click', '.form-alert', function (){
        let id = $(this).data('id')
        let message = $(this).data('message')
        Swal.fire({
            title: '{{ translate('messages.Are you sure?') }}',
            text: message,
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: '#FC6A57',
            cancelButtonText: '{{ translate('messages.no') }}',
            confirmButtonText: '{{ translate('messages.Yes') }}',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                $('#'+id).submit()
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
                    data: {search: searchKeyword, _token: $('input[name="_token"]').val()},
                    success: function (response) {
                        if (response.length === 0) {
                            $('#searchResults').html('<div class="fs-16 fw-500 mb-2">' + @json(translate('Search Result')) + '</div>' +
                                '<div class="search-list h-300 d-flex flex-column gap-2 justify-content-center align-items-center fs-16">' +
                                '<img width="30" src="' + @json(dynamicAsset('assets/admin/img/modal/no-search-found.png')) + '" alt="">' + ' ' +
                                @json(translate('No result found')) +
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
                            $('#searchResults').html('<div class="fs-16 fw-500 mb-2">' + @json(translate('Search Result')) + '</div>' + '<div class="search-list d-flex flex-column">' + resultHtml + '</div>');

                            $('.search-list-item').click(function () {
                                var routeName = $(this).data('route-name');
                                var routeUri = $(this).data('route-uri');
                                var routeFullUrl = $(this).data('route-full-url');

                                $.ajax({
                                    type: 'POST',
                                    url: '{{ route('vendor.store.clicked.route') }}',
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
                $('#searchResults').html('<div class="text-center text-muted py-5">{{translate('Write a minimum of one characters.')}}.</div>');
            }
        });
    });

    document.addEventListener('keydown', function(event) {
        if (event.ctrlKey && event.key === 'k') {
            event.preventDefault();
            document.getElementById('modalOpener').click();
        }
    });

    $(document).ready(function () {
        $("#staticBackdrop").on("shown.bs.modal", function () {
            $(this).find("#searchForm input[type=search]").val('');
            $('#searchResults').html('<div class="text-center text-muted py-5">{{translate('Loading recent searches')}}...</div>');
            $(this).find("#searchForm input[type=search]").focus();

            $.ajax({
                type: 'GET',
                url: '{{ route('vendor.recent.search') }}',
                success: function (response) {
                    if (response.length === 0) {
                        $('#searchResults').html('<div class="text-center text-muted py-5">{{translate('It appears that you have not yet searched.')}}.</div>');
                    } else {
                        var resultHtml = '';
                        response.forEach(function (route) {
                            resultHtml += '<a href="' + route.route_full_url + '" class="search-list-item d-flex flex-column" data-route-name="' + route.route_name + '" data-route-uri="' + route.route_uri + '" data-route-full-url="' + route.route_full_url + '" aria-current="true">';
                            resultHtml += '<h5>' + route.route_name + '</h5>';
                            resultHtml += '<p class="text-muted fs-12  mb-0">' + route.route_uri + '</p>';
                            resultHtml += '</a>';
                        });
                        $('#searchResults').html('<div class="recent-search fs-16 fw-500 animate">' +
                            @json(translate('Recent Search')) + '<div class="search-list d-flex flex-column mt-2">' + resultHtml + '</div></div>');

                        $('.search-list-item').click(function () {
                            var routeName = $(this).data('route-name');
                            var routeUri = $(this).data('route-uri');
                            var routeFullUrl = $(this).data('route-full-url');
                            var searchKeyword = $('input[type=search]').val().trim();

                            $.ajax({
                                type: 'POST',
                                url: '{{ route('vendor.store.clicked.route') }}',
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
                    $('#searchResults').html('<div class="text-center text-muted py-5">{{translate('Error loading recent searches')}}.</div>');
                }
            });
        });
    });

    $("#staticBackdrop").on("hidden.bs.modal", function () {
        $('#searchResults').empty();
    });

    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('search', function() {
        if (!this.value.trim()) {
            $('#searchResults').html('<div class="text-center text-muted py-5"></div>');
        }
    });

    $('#searchForm').submit(function (event) {
        event.preventDefault();
    });

    @php($fcm_credentials = \App\CentralLogics\Helpers::get_business_settings('fcm_credentials'))
    var firebaseConfig = {
        apiKey: "{{isset($fcm_credentials['apiKey']) ? $fcm_credentials['apiKey'] : ''}}",
        authDomain: "{{isset($fcm_credentials['authDomain']) ? $fcm_credentials['authDomain'] : ''}}",
        projectId: "{{isset($fcm_credentials['projectId']) ? $fcm_credentials['projectId'] : ''}}",
        storageBucket: "{{isset($fcm_credentials['storageBucket']) ? $fcm_credentials['storageBucket'] : ''}}",
        messagingSenderId: "{{isset($fcm_credentials['messagingSenderId']) ? $fcm_credentials['messagingSenderId'] : ''}}",
        appId: "{{isset($fcm_credentials['appId']) ? $fcm_credentials['appId'] : ''}}",
        measurementId: "{{isset($fcm_credentials['measurementId']) ? $fcm_credentials['measurementId'] : ''}}"
    };

    @if (isset($fcm_credentials['apiKey']) && is_string($fcm_credentials['apiKey']) && strlen($fcm_credentials['apiKey'])  > 3 )
        firebase.initializeApp(firebaseConfig);
        const messaging = firebase.messaging();
    @endif

        function startFCM() {
            messaging
                .requestPermission()
                .then(function() {
                    return messaging.getToken();
                })
                .then(function(token) {
                    // console.log('FCM Token:', token);
                    @php($restaurant_id=\App\CentralLogics\Helpers::get_restaurant_id())
                    @php($panel_restaurant=\App\CentralLogics\Helpers::get_restaurant_data())
                    // Send the token to your backend to subscribe to topic
                    subscribeTokenToBackend(token, 'restaurant_panel_{{$restaurant_id}}_message');

                    // The panel's browser token is its own, separate from the app's device
                    // token. Without these two it would never receive a promotion broadcast -
                    // those go to the zone topics the mobile app subscribes itself to.
                    subscribeTokenToBackend(token, 'all_zone_restaurant');
                    subscribeTokenToBackend(token, 'zone_{{ $panel_restaurant?->zone_id }}_restaurant');
                }).catch(function(error) {
                console.error('Error getting permission or token:', error);
            });
        }

        function subscribeTokenToBackend(token, topic) {
            fetch('{{url('/')}}/subscribeToTopic', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
            if (sParameterName[0] === sParam) {
                return sParameterName[1];
            }
        }
    }

    function converationList() {
        var tab = getUrlParameter('tab');
        $.ajax({
            url: "{{ route('vendor.message.list') }}"+ '?tab=' + tab,
            success: function(data) {
                $('#conversation-list').empty();
                $('#admin-conversation-list').empty();
                $("#conversation-list").append(data.html);
                $("#admin-conversation-list").append(data.admin_html);
                var user_id = getUrlParameter('user');
                $('.customer-list').removeClass('conv-active');
                $('#customer-' + user_id).addClass('conv-active');
            }
        })
    }

    function conversationView() {
        var conversation_id = getUrlParameter('conversation');
        var user_id = getUrlParameter('user');
        var url= '{{url('/')}}/restaurant-panel/message/view/'+conversation_id+'/' + user_id;
        $.ajax({
            url: url,
            success: function(data) {
                $('#view-conversation').html(data.view);
            }
        })
    }
    @php($order_notification_type = \App\Models\BusinessSetting::where('key', 'order_notification_type')->first())
    @php($order_notification_type = $order_notification_type ? $order_notification_type->value : 'firebase')
    var order_type = 'all';


    @if (isset($fcm_credentials['apiKey']) && is_string($fcm_credentials['apiKey']) && strlen($fcm_credentials['apiKey'])  > 3 )

    messaging.onMessage(function (payload) {
        console.log(payload.data);

        if(payload.data.order_id && payload.data.type === 'new_order'){
            @if(\App\CentralLogics\Helpers::employee_module_permission_check('order') && $order_notification_type == 'firebase')
            order_type = payload.data.order_type
            playAudio();
            $('#popup-modal').appendTo("body").modal('show');
            @endif
        }else if(payload.data.type === 'bogo_offer' || payload.data.type === 'happy_hour'){
            // Promotion invitations and approvals. The panel may be sitting on any page, so
            // announce it here and refresh the list only when that is what is on screen.
            playAudio();
            // House toastr styling: green tick, bold title, body, progress bar.
            toastr.success(payload.data.body || payload.data.description || '', payload.data.title || '', {
                CloseButton: true,
                ProgressBar: true,
                timeOut: 8000
            });

            var listPath = payload.data.type === 'bogo_offer'
                ? '/restaurant-panel/bogo-offer/list'
                : '/restaurant-panel/happy-hour/list';

            if (window.location.pathname.indexOf(listPath) === 0) {
                setTimeout(function () { location.reload(); }, 1500);
            }
        }else if(payload.data.type === 'message'){
            var conversation_id = getUrlParameter('conversation');
            var user_id = getUrlParameter('user');
            var url= '{{url('/')}}/restaurant-panel/message/view/'+conversation_id+'/' + user_id;
            $.ajax({
                url: url,
                success: function(data) {
                    $('#view-conversation').html(data.view);
                }
            })
            toastr.success('{{ translate('messages.New message arrived') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });

            if($('#conversation-list').scrollTop() == 0){
                converationList();
            }
        }
    });

    @endif

    @if(\App\CentralLogics\Helpers::employee_module_permission_check('order') && $order_notification_type == 'manual')
        setInterval(function () {
            $.get({
                url: '{{route('vendor.get-restaurant-data')}}',
                dataType: 'json',
                success: function (response) {
                    let data = response.data;
                    if (data.new_pending_order > 0) {
                        order_type = 'pending';
                        playAudio();
                        $('#popup-modal').appendTo("body").modal('show');
                    }
                    else if(data.new_confirmed_order > 0)
                    {
                        order_type = 'confirmed';
                        playAudio();
                        $('#popup-modal').appendTo("body").modal('show');
                    }
                },
            });
        }, 10000);
        @endif

        $(document).on('click', '.check-order', function () {
            location.href = '{{url('/')}}/restaurant-panel/order/list/all';
        });
        $(document).on('click', '.check-message', function () {
            var tab = getUrlParameter('tab');
            location.href = '{{ route('vendor.message.list') }}'+ '?tab=' + tab;
        });

        function verifiedBadgePopupShow() {
            new_tostar(
                'success',
                '{{ translate('messages.verified') }}',
                '{{ translate('Congratulations! Your restaurant account is now verified.') }}',
                {
                    onClose: function () {
                        $.ajax({
                            type: 'POST',
                            url: '{{ route('vendor.shop.verified-badge.popup-seen') }}',
                            data: {
                                _token: '{{ csrf_token() }}',
                            },
                        });
                    }
                }
            );
        }


    @if (isset($fcm_credentials['apiKey']) && is_string($fcm_credentials['apiKey']) && strlen($fcm_credentials['apiKey'])  > 3 )
        startFCM();
        @endif
    converationList();

    if(getUrlParameter('conversation')){
        conversationView();
    }

    $(document).on('click', '.call-demo', function () {
            @if(getEnvMode() =='demo')
            toastr.info('{{ translate('Update option is disabled for demo!') }}', {
                CloseButton: true,
                ProgressBar: true
            });
            @endif
        });

    $(window).on('load', ()=> $('.pre--loader').fadeOut(600))

    $('.log-out').on('click',function (){
        Swal.fire({
        title: '{{ translate('Do you want to logout?') }}',
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonColor: '#FC6A57',
        cancelButtonColor: '#363636',
        confirmButtonText: `{{ translate('yes')}}`,
        cancelButtonText: `{{ translate('cancel')}}`,
        }).then((result) => {
        if (result.value) {
            location.href='{{route('logout')}}';
            }
        })
    });


        function initTelInputs() {
            const inputs = document.querySelectorAll('input[type="tel"]');

            inputs.forEach(input => {

                const iti = window.intlTelInput(input, {
                    initialCountry: "{{$countryCode}}",
                    utilsScript: "{{ dynamicAsset('assets/admin/intltelinput/js/utils.js') }}",
                    autoInsertDialCode: true,
                    nationalMode: false,
                    formatOnDisplay: false,
                    strictMode: true,
                    @if (\App\CentralLogics\Helpers::get_business_settings('country_picker_status') != 1)
                        onlyCountries: ["{{$countryCode}}"],
                    @endif
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


    document.addEventListener('DOMContentLoaded', function() {
                initTelInputs();
                @if ($verifiedBadgePopupShow)
                    verifiedBadgePopupShow();
                @endif
            });

</script>


    <script src="{{ dynamicAsset('assets/admin/js/external-navigation-guard.js') }}"></script>
</body>
</html>
