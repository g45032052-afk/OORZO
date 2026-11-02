<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required Meta Tags Always Come First -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Title -->
    <title><?php echo e(translate('messages.error')); ?> 404 | <?php echo e(\App\CentralLogics\Helpers::get_business_settings('business_name', false)??'Stack Food'); ?></title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="favicon.ico">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&amp;display=swap" rel="stylesheet">

    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin')); ?>/css/vendor.min.css">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin')); ?>/vendor/icon-set/style.css">

    <!-- CSS Front Template -->
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin')); ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin')); ?>/css/theme.minc619.css?v=1.0">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin')); ?>/css/style.css">
</head>

<body>

<!-- Content -->
<div class="container">
    <div class="footer-height-offset d-flex justify-content-center align-items-center flex-column">
        <div class="row align-items-sm-center w-100">
            <div class="col-12">
                <div class="text-center mb-5 mb-sm-0">
                    <img class="w-60 w-sm-100 mx-auto initial-61" src="<?php echo e(dynamicAsset('assets/admin')); ?>/svg/illustrations/404.svg" alt="Image Description">
                </div>
            </div>

        </div>
        <!-- End Row -->
            <div class="mt-2">


                
                <?php ($panel = auth('vendor')->check() ? 'vendor.dashboard' : 'admin.dashboard'); ?>
                <a class="btn btn-primary" href="<?php echo e(app('router')->has($panel) ? route($panel) : url('/')); ?>"><?php echo e(translate('messages.dashboard')); ?></a>
            </div>
    </div>
</div>
<!-- End Content -->

<!-- Footer -->
<div class="footer text-center">
    <ul class="list-inline list-separator">
        <li class="list-inline-item">
            <a class="list-separator-link" target="_blank" href="<?php echo e(app('router')->has('contact-us') ? route('contact-us') : url('/')); ?>"><?php echo e(\App\CentralLogics\Helpers::get_business_settings('business_name', false)??'Stack Food'); ?> <?php echo e(translate('messages.support')); ?></a>
        </li>
    </ul>
</div>
<!-- End Footer -->


<!-- JS Front -->
<script src="<?php echo e(dynamicAsset('assets/admin')); ?>/js/theme.min.js"></script>
</body>

</html>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/errors/404.blade.php ENDPATH**/ ?>