<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required Meta Tags Always Come First -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Title -->
    <title>{{translate('messages.error')}} 404 | {{\App\CentralLogics\Helpers::get_business_settings('business_name', false)??'Stack Food'}}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="favicon.ico">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&amp;display=swap" rel="stylesheet">

    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="{{dynamicAsset('assets/admin')}}/css/vendor.min.css">
    <link rel="stylesheet" href="{{dynamicAsset('assets/admin')}}/vendor/icon-set/style.css">

    <!-- CSS Front Template -->
    <link rel="stylesheet" href="{{ dynamicAsset('assets/admin') }}/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ dynamicAsset('assets/admin') }}/css/theme.minc619.css?v=1.0">
    <link rel="stylesheet" href="{{ dynamicAsset('assets/admin') }}/css/style.css">
</head>

<body>

<!-- Content -->
<div class="container">
    <div class="footer-height-offset d-flex justify-content-center align-items-center flex-column">
        <div class="row align-items-sm-center w-100">
            <div class="col-12">
                <div class="text-center mb-5 mb-sm-0">
                    <img class="w-60 w-sm-100 mx-auto initial-61" src="{{dynamicAsset('assets/admin')}}/svg/illustrations/404.svg" alt="Image Description">
                </div>
            </div>

        </div>
        <!-- End Row -->
            <div class="mt-2">
{{--                <h1 class="display-1 mb-0">404</h1>--}}
{{--                <p class="lead">{{translate('messages.404_warning_message')}}.</p>--}}
                {{-- Guarded, as errors/illustrated-layout.blade.php already does it: this page
                     also renders while the site is in install or update mode, where the panel
                     routes do not exist and route() would hide the real failure. --}}
                @php($panel = auth('vendor')->check() ? 'vendor.dashboard' : 'admin.dashboard')
                <a class="btn btn-primary" href="{{ app('router')->has($panel) ? route($panel) : url('/') }}">{{translate('messages.dashboard')}}</a>
            </div>
    </div>
</div>
<!-- End Content -->

<!-- Footer -->
<div class="footer text-center">
    <ul class="list-inline list-separator">
        <li class="list-inline-item">
            <a class="list-separator-link" target="_blank" href="{{ app('router')->has('contact-us') ? route('contact-us') : url('/') }}">{{\App\CentralLogics\Helpers::get_business_settings('business_name', false)??'Stack Food'}} {{translate('messages.support')}}</a>
        </li>
    </ul>
</div>
<!-- End Footer -->


<!-- JS Front -->
<script src="{{dynamicAsset('assets/admin')}}/js/theme.min.js"></script>
</body>

</html>
