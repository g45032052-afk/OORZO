<!DOCTYPE html>

<?php
    if(session()->get('landing_site_direction') ){
        $site_direction = session()->get('landing_site_direction');
    } else{
        $site_direction = session()->get('site_direction');
    }
    $country= \App\CentralLogics\Helpers::get_business_settings('country');
    $countryCode= strtolower($country??'auto');
?>

<?php ( $direction= ( $site_direction === 'rtl') ? "true" : "false"); ?>

<html dir="<?php echo e($site_direction); ?>" lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" >

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title><?php echo $__env->yieldContent('title','Landing Page | '); ?></title>
    <?php echo $__env->make('layouts.landing.seo', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin')); ?>/vendor/icon-set/style.css">

    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/landing/assets_new/css/bootstrap.min.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/landing/assets_new/css/animate.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/landing/assets_new/css/all.min.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/landing/assets_new/css/owl.min.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/landing/assets_new/css/main.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin/css/toastr.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin/css/select2.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('assets/admin/intltelinput/css/intlTelInput.css')); ?>">
    <?php ($icon = \App\CentralLogics\Helpers::get_business_settings('icon')); ?>
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo e(dynamicStorage('storage/app/public/business/'. $icon ?? '')); ?>">


    <?php ($landing_page_links = \App\CentralLogics\Helpers::get_business_settings('landing_page_links') ?? []); ?>
    <?php ($backgroundChange =\App\CentralLogics\Helpers::get_business_settings('backgroundChange') ); ?>

    <?php if(isset($backgroundChange['primary_1_hex']) ): ?>
        <style>
            :root {
                --base-1: <?php echo $backgroundChange['primary_1_hex']; ?>;
                --base-rgb: <?php echo $backgroundChange['primary_1_rgb']; ?>;
            }
        </style>
    <?php endif; ?>
    <style>
        .oorzo-wordmark {
            display: inline-flex;
            align-items: center;
            font-size: 30px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -1.5px;
            text-decoration: none;
            text-transform: uppercase;
        }

        .oorzo-wordmark span:first-child {
            color: #202631;
        }

        .oorzo-wordmark span:last-child {
            color: #f4511e;
        }

        .oorzo-wordmark--footer span:first-child {
            color: #ffffff;
        }
    </style>
    <?php echo $__env->yieldPushContent('css_or_js'); ?>
</head>

<body class="<?php echo $__env->yieldContent('body_class'); ?>">
        <!-- ==== Preloader ==== -->
        <div id="landing-loader"></div>
        <!-- ==== Preloader ==== -->

    <header>
        <div class="container">
            <div class="header-wrapper">
                <div class="logo">
                    <a class="oorzo-wordmark" href="<?php echo e(route('home')); ?>" aria-label="OORZO home">
                        <span>OOR</span><span>ZO</span>
                    </a>
                </div>
                <div class="nav-toggle d-lg-none">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <ul class="menu">
                    <li>
                        <a href="<?php echo e(route('home')); ?>" class="<?php echo $__env->yieldContent('home'); ?>">
                            <svg width="25" height="8" viewBox="0 0 25 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M25 6.9176L17.1875 5.7514L17.1875 4.8764L25 4.2926L25 3.7088L17.1875 3.125L17.1875 2.25L25 1.0838L25 0.5L14.8438 0.5C14.6366 0.5 14.4379 0.57375 14.2914 0.705025C14.1448 0.836301 14.0625 1.01435 14.0625 1.2L14.0625 3.0228L1.55946 2.3354C1.35875 2.32241 1.15722 2.34688 0.967606 2.40725C0.77799 2.46762 0.604418 2.56259 0.457861 2.68614C0.311302 2.80969 0.194948 2.95915 0.116154 3.12505C0.037361 3.29096 -0.00216116 3.46971 9.1415e-05 3.65L9.13844e-05 4.35C9.13508e-05 5.12 0.70165 5.7108 1.55946 5.6646L14.0625 4.9772L14.0625 6.8C14.0625 6.98565 14.1448 7.1637 14.2914 7.29497C14.4379 7.42625 14.6366 7.5 14.8438 7.5L25 7.5L25 6.9162L25 6.9176Z"
                                    fill="#FB5607" />
                                <path
                                    d="M25 6.9176L17.1875 5.7514L17.1875 4.8764L25 4.2926L25 3.7088L17.1875 3.125L17.1875 2.25L25 1.0838L25 0.5L14.8438 0.5C14.6366 0.5 14.4379 0.57375 14.2914 0.705025C14.1448 0.836301 14.0625 1.01435 14.0625 1.2L14.0625 3.0228L1.55946 2.3354C1.35875 2.32241 1.15722 2.34688 0.967606 2.40725C0.77799 2.46762 0.604418 2.56259 0.457861 2.68614C0.311302 2.80969 0.194948 2.95915 0.116154 3.12505C0.037361 3.29096 -0.00216116 3.46971 9.1415e-05 3.65L9.13844e-05 4.35C9.13508e-05 5.12 0.70165 5.7108 1.55946 5.6646L14.0625 4.9772L14.0625 6.8C14.0625 6.98565 14.1448 7.1637 14.2914 7.29497C14.4379 7.42625 14.6366 7.5 14.8438 7.5L25 7.5L25 6.9162L25 6.9176Z"
                                    fill="#FB5607" />
                            </svg>
                            <?php echo e(translate('messages.home')); ?>

                        </a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('about-us')); ?>"  class="<?php echo $__env->yieldContent('about'); ?>">
                            <svg width="25" height="8" viewBox="0 0 25 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M25 6.9176L17.1875 5.7514L17.1875 4.8764L25 4.2926L25 3.7088L17.1875 3.125L17.1875 2.25L25 1.0838L25 0.5L14.8438 0.5C14.6366 0.5 14.4379 0.57375 14.2914 0.705025C14.1448 0.836301 14.0625 1.01435 14.0625 1.2L14.0625 3.0228L1.55946 2.3354C1.35875 2.32241 1.15722 2.34688 0.967606 2.40725C0.77799 2.46762 0.604418 2.56259 0.457861 2.68614C0.311302 2.80969 0.194948 2.95915 0.116154 3.12505C0.037361 3.29096 -0.00216116 3.46971 9.1415e-05 3.65L9.13844e-05 4.35C9.13508e-05 5.12 0.70165 5.7108 1.55946 5.6646L14.0625 4.9772L14.0625 6.8C14.0625 6.98565 14.1448 7.1637 14.2914 7.29497C14.4379 7.42625 14.6366 7.5 14.8438 7.5L25 7.5L25 6.9162L25 6.9176Z"
                                    fill="#FB5607" />
                                <path
                                    d="M25 6.9176L17.1875 5.7514L17.1875 4.8764L25 4.2926L25 3.7088L17.1875 3.125L17.1875 2.25L25 1.0838L25 0.5L14.8438 0.5C14.6366 0.5 14.4379 0.57375 14.2914 0.705025C14.1448 0.836301 14.0625 1.01435 14.0625 1.2L14.0625 3.0228L1.55946 2.3354C1.35875 2.32241 1.15722 2.34688 0.967606 2.40725C0.77799 2.46762 0.604418 2.56259 0.457861 2.68614C0.311302 2.80969 0.194948 2.95915 0.116154 3.12505C0.037361 3.29096 -0.00216116 3.46971 9.1415e-05 3.65L9.13844e-05 4.35C9.13508e-05 5.12 0.70165 5.7108 1.55946 5.6646L14.0625 4.9772L14.0625 6.8C14.0625 6.98565 14.1448 7.1637 14.2914 7.29497C14.4379 7.42625 14.6366 7.5 14.8438 7.5L25 7.5L25 6.9162L25 6.9176Z"
                                    fill="#FB5607" />
                            </svg>
                            <?php echo e(translate('messages.about')); ?>

                        </a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('privacy-policy')); ?>"  class="<?php echo $__env->yieldContent('privacy-policy'); ?>" >
                            <svg width="25" height="8" viewBox="0 0 25 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M25 6.9176L17.1875 5.7514L17.1875 4.8764L25 4.2926L25 3.7088L17.1875 3.125L17.1875 2.25L25 1.0838L25 0.5L14.8438 0.5C14.6366 0.5 14.4379 0.57375 14.2914 0.705025C14.1448 0.836301 14.0625 1.01435 14.0625 1.2L14.0625 3.0228L1.55946 2.3354C1.35875 2.32241 1.15722 2.34688 0.967606 2.40725C0.77799 2.46762 0.604418 2.56259 0.457861 2.68614C0.311302 2.80969 0.194948 2.95915 0.116154 3.12505C0.037361 3.29096 -0.00216116 3.46971 9.1415e-05 3.65L9.13844e-05 4.35C9.13508e-05 5.12 0.70165 5.7108 1.55946 5.6646L14.0625 4.9772L14.0625 6.8C14.0625 6.98565 14.1448 7.1637 14.2914 7.29497C14.4379 7.42625 14.6366 7.5 14.8438 7.5L25 7.5L25 6.9162L25 6.9176Z"
                                    fill="#FB5607" />
                                <path
                                    d="M25 6.9176L17.1875 5.7514L17.1875 4.8764L25 4.2926L25 3.7088L17.1875 3.125L17.1875 2.25L25 1.0838L25 0.5L14.8438 0.5C14.6366 0.5 14.4379 0.57375 14.2914 0.705025C14.1448 0.836301 14.0625 1.01435 14.0625 1.2L14.0625 3.0228L1.55946 2.3354C1.35875 2.32241 1.15722 2.34688 0.967606 2.40725C0.77799 2.46762 0.604418 2.56259 0.457861 2.68614C0.311302 2.80969 0.194948 2.95915 0.116154 3.12505C0.037361 3.29096 -0.00216116 3.46971 9.1415e-05 3.65L9.13844e-05 4.35C9.13508e-05 5.12 0.70165 5.7108 1.55946 5.6646L14.0625 4.9772L14.0625 6.8C14.0625 6.98565 14.1448 7.1637 14.2914 7.29497C14.4379 7.42625 14.6366 7.5 14.8438 7.5L25 7.5L25 6.9162L25 6.9176Z"
                                    fill="#FB5607" />
                            </svg>
                            <?php echo e(translate('messages.privacy_policy')); ?>

                        </a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('contact-us')); ?>" class="<?php echo $__env->yieldContent('contact'); ?>">
                            <svg width="25" height="8" viewBox="0 0 25 8" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M25 6.9176L17.1875 5.7514L17.1875 4.8764L25 4.2926L25 3.7088L17.1875 3.125L17.1875 2.25L25 1.0838L25 0.5L14.8438 0.5C14.6366 0.5 14.4379 0.57375 14.2914 0.705025C14.1448 0.836301 14.0625 1.01435 14.0625 1.2L14.0625 3.0228L1.55946 2.3354C1.35875 2.32241 1.15722 2.34688 0.967606 2.40725C0.77799 2.46762 0.604418 2.56259 0.457861 2.68614C0.311302 2.80969 0.194948 2.95915 0.116154 3.12505C0.037361 3.29096 -0.00216116 3.46971 9.1415e-05 3.65L9.13844e-05 4.35C9.13508e-05 5.12 0.70165 5.7108 1.55946 5.6646L14.0625 4.9772L14.0625 6.8C14.0625 6.98565 14.1448 7.1637 14.2914 7.29497C14.4379 7.42625 14.6366 7.5 14.8438 7.5L25 7.5L25 6.9162L25 6.9176Z"
                                    fill="#FB5607" />
                                <path
                                    d="M25 6.9176L17.1875 5.7514L17.1875 4.8764L25 4.2926L25 3.7088L17.1875 3.125L17.1875 2.25L25 1.0838L25 0.5L14.8438 0.5C14.6366 0.5 14.4379 0.57375 14.2914 0.705025C14.1448 0.836301 14.0625 1.01435 14.0625 1.2L14.0625 3.0228L1.55946 2.3354C1.35875 2.32241 1.15722 2.34688 0.967606 2.40725C0.77799 2.46762 0.604418 2.56259 0.457861 2.68614C0.311302 2.80969 0.194948 2.95915 0.116154 3.12505C0.037361 3.29096 -0.00216116 3.46971 9.1415e-05 3.65L9.13844e-05 4.35C9.13508e-05 5.12 0.70165 5.7108 1.55946 5.6646L14.0625 4.9772L14.0625 6.8C14.0625 6.98565 14.1448 7.1637 14.2914 7.29497C14.4379 7.42625 14.6366 7.5 14.8438 7.5L25 7.5L25 6.9162L25 6.9176Z"
                                    fill="#FB5607" />
                            </svg>
                            <?php echo e(translate('messages.contact')); ?>

                        </a>
                    </li>
                </ul>

                <?php ( $local = session()->has('landing_local')?session('landing_local'):null); ?>
                <?php ($lang = \App\CentralLogics\Helpers::get_business_settings('system_language') ); ?>
                <?php if($lang): ?>
                    <div class="dropdown--btn-hover position-relative">
                        <a class=" text-dark dropdown--btn border-0 px-3 header--btn text-capitalize d-flex" href="javascript:void(0)">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20">
                                <path d="M16.555 5.412a8.028 8.028 0 00-3.503-2.81 14.899 14.899 0 011.663 4.472 8.547 8.547 0 001.84-1.662zM13.326 7.825a13.43 13.43 0 00-2.413-5.773 8.087 8.087 0 00-1.826 0 13.43 13.43 0 00-2.413 5.773A8.473 8.473 0 0010 8.5c1.18 0 2.304-.24 3.326-.675zM6.514 9.376A9.98 9.98 0 0010 10c1.226 0 2.4-.22 3.486-.624a13.54 13.54 0 01-.351 3.759A13.54 13.54 0 0110 13.5c-1.079 0-2.128-.127-3.134-.366a13.538 13.538 0 01-.352-3.758zM5.285 7.074a14.9 14.9 0 011.663-4.471 8.028 8.028 0 00-3.503 2.81c.529.638 1.149 1.199 1.84 1.66zM17.334 6.798a7.973 7.973 0 01.614 4.115 13.47 13.47 0 01-3.178 1.72 15.093 15.093 0 00.174-3.939 10.043 10.043 0 002.39-1.896zM2.666 6.798a10.042 10.042 0 002.39 1.896 15.196 15.196 0 00.174 3.94 13.472 13.472 0 01-3.178-1.72 7.973 7.973 0 01.615-4.115zM10 15c.898 0 1.778-.079 2.633-.23a13.473 13.473 0 01-1.72 3.178 8.099 8.099 0 01-1.826 0 13.47 13.47 0 01-1.72-3.178c.855.151 1.735.23 2.633.23zM14.357 14.357a14.912 14.912 0 01-1.305 3.04 8.027 8.027 0 004.345-4.345c-.953.542-1.971.981-3.04 1.305zM6.948 17.397a8.027 8.027 0 01-4.345-4.345c.953.542 1.971.981 3.04 1.305a14.912 14.912 0 001.305 3.04z" />
                            </svg>
                                <?php $__currentLoopData = $lang??[]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($data['code']==$local ): ?>
                                        <span class="me-1"><?php echo e($data['code']); ?></span>
                                    <?php elseif(!$local &&  $data['default'] == true): ?>
                                        <span class="me-1"><?php echo e($data['code']); ?></span>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </a>
                        <ul class="dropdown-list py-0 min-width-style" >
                            <?php $__currentLoopData = $lang??[]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key =>$data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($data['status']==1): ?>
                                <li class="py-0">
                                    <a class="" href="<?php echo e(route('lang',[$data['code']])); ?>">
                                        <?php echo e($data['code']); ?>

                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider my-0">
                                </li>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>


                <?php if((isset($toggle_dm_registration)  && $toggle_dm_registration )|| (isset($toggle_restaurant_registration) && $toggle_restaurant_registration)): ?>
                <div class="position-relative">
                    <a class="dropdown--btn btn-base" href="javascript:void(0)">
                        <span><?php echo e(translate('Join us')); ?></span>
                        <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.00224 5.46105L1.33333 0.415128C1.21002 0.290383 1 0.0787335 1 0.0787335C1 0.0787335 0.708488 -0.0458817 0.584976 0.0788632L0.191805 0.475841C0.0680976 0.600389 7.43292e-08 0.766881 7.22135e-08 0.9443C7.00978e-08 1.12172 0.0680976 1.28801 0.191805 1.41266L5.53678 6.80682C5.66068 6.93196 5.82624 7.00049 6.00224 7C6.17902 7.00049 6.34439 6.93206 6.46839 6.80682L11.8082 1.41768C11.9319 1.29303 12 1.12674 12 0.949223C12 0.771804 11.9319 0.605509 11.8082 0.480765L11.415 0.0838844C11.1591 -0.174368 10.9225 0.222512 10.6667 0.480765L6.00224 5.46105Z"
                                fill="white" />
                        </svg>
                    </a>
                    <ul class="dropdown-list">
                        <?php if($toggle_restaurant_registration): ?>
                        <li>
                            <a href="<?php echo e(route('restaurant.create')); ?>"> <?php echo e(translate('messages.join_as_restaurant')); ?></a>
                        </li>
                        <?php endif; ?>
                        <?php if($toggle_dm_registration): ?>
                        <li>
                            <a href="<?php echo e(route('deliveryman.create')); ?>"><?php echo e(translate('messages.join_as_deliveryman')); ?></a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php echo $__env->yieldContent('content'); ?>


        <!-- ======= Footer Section ======= -->
        <?php
            $datas =  \App\Models\DataSetting::with('translations')->where('type','admin_landing_page')->whereIn('key', ['news_letter_title','news_letter_sub_title','footer_data','shipping_policy_status','refund_policy_status','cancellation_policy_status'])->get();
                    $data = [];
                    foreach ($datas as $key => $value) {
                        if(count($value->translations)>0){
                            $cred = [
                                $value->key => $value->translations[0]['value'],
                            ];
                            array_push($data,$cred);
                        }else{
                            $cred = [
                                $value->key => $value->value,
                            ];
                            array_push($data,$cred);
                        }
                    }
                    $landing_data = [];
                    foreach($data as $single_data){
                        foreach($single_data as $key=>$single_value){
                            $landing_data[$key] = $single_value;
                        }
                    }
        ?>
        <footer class="footer-bg">
            <div class="newsletter-section">
                <div class="container">
                    <div class="position-relative">

                            <div class="newsletter-content position-relative">
                                <h3 class="title">
                                    Get local favorites in your inbox
                                    </h3>
                                <div class="text">
                                    Sign up for occasional OORZO updates and food inspiration.
                                </div>
                            <form method="post" action="<?php echo e(route('newsletter.subscribe')); ?>">
                               <?php echo csrf_field(); ?>
                                <div class="input--grp">
                                    <input type="email" name="email" class="form-control" required placeholder="<?php echo e(translate('Enter your email address')); ?>" value="<?php echo e(old('email')); ?>">
                                    <button class="search-btn" type="submit">
                                        <svg width="46" height="47" viewBox="0 0 46 47" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <rect y="0.349609" width="46" height="46" rx="23"
                                                fill="url(#paint0_radial_246_199)" />
                                            <path
                                                d="M25.9667 23.3466L19.3001 29.5718C19.1353 29.7363 18.8556 30.0163 18.8556 30.0163C18.8556 30.0163 18.691 30.405 18.8558 30.5696L19.3803 31.0939C19.5448 31.2588 19.7648 31.3496 19.9992 31.3496C20.2336 31.3496 20.4533 31.2588 20.618 31.0939L27.7448 23.9672C27.9101 23.802 28.0006 23.5813 28 23.3466C28.0006 23.1109 27.9102 22.8904 27.7448 22.7251L20.6246 15.6053C20.46 15.4404 20.2403 15.3496 20.0057 15.3496C19.7713 15.3496 19.5516 15.4404 19.3868 15.6053L18.8624 16.1296C18.5212 16.4708 19.0456 16.7863 19.3868 17.1274L25.9667 23.3466Z"
                                                fill="white" />
                                            <defs>
                                                <radialGradient id="paint0_radial_246_199" cx="0" cy="0" r="1"
                                                    gradientUnits="userSpaceOnUse"
                                                    gradientTransform="translate(23 23.3496) rotate(90) scale(23)">
                                                    <stop stop-color="#FFBE0B" />
                                                    <stop offset="1" stop-color="#FB5607" />
                                                </radialGradient>
                                            </defs>
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="newsletter-bg"></div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="container">
                    <div class="footer-wrapper ps-xl-5">
                        <div class="footer-widget">
                            <div class="footer-logo">

                                <a class="oorzo-wordmark oorzo-wordmark--footer" href="<?php echo e(route('home')); ?>" aria-label="OORZO home">
                                    <span>OOR</span><span>ZO</span>
                                </a>
                            </div>
                            <div class="txt">
                                OORZO brings your town's favorite flavors together. Discover local restaurants and enjoy fresh food delivered to your door.
                            </div>
                        </div>
                        <div class="footer-widget widget-links">
                            <h5 class="subtitle mt-2 text-white"><?php echo e(translate('messages.Support')); ?></h5>
                            <ul>
                                <?php if(isset($landing_data['shipping_policy_status']) && $landing_data['shipping_policy_status']  == 1): ?>
                                <li>
                                    <a
                                        href="<?php echo e(route('shipping-policy')); ?>"><?php echo e(translate('messages.shipping_policy')); ?></a>
                                </li>
                                <?php endif; ?>
                                <li>
                                    <a href="<?php echo e(route('privacy-policy')); ?>"><?php echo e(translate('messages.privacy_policy')); ?></a>
                                </li>

                                <?php if(isset($landing_data['refund_policy_status']) && $landing_data['refund_policy_status']  == 1): ?>
                                <li>
                                    <a
                                        href="<?php echo e(route('refund-policy')); ?>"><?php echo e(translate('messages.refund_policy')); ?></a>
                                </li>
                                <?php endif; ?>
                                <?php if(isset($landing_data['cancellation_policy_status']) && $landing_data['cancellation_policy_status']  == 1): ?>
                                <li>
                                    <a
                                        href="<?php echo e(route('cancellation-policy')); ?>"><?php echo e(translate('messages.cancellation_policy')); ?></a>
                                </li>
                                <?php endif; ?>
                                <li>
                                    <a
                                        href="<?php echo e(route('terms-and-conditions')); ?>"><?php echo e(translate('messages.terms_and_condition')); ?></a>
                                </li>

                            </ul>
                        </div>

                        <div class="footer-widget widget-links">
                            <h5 class="subtitle mt-2 text-white"><?php echo e(translate('messages.quick_links')); ?></h5>
                            <ul>
                                <li>
                                    <a href="<?php echo e(route('about-us')); ?>"><?php echo e(translate('messages.about_us')); ?></a>
                                </li>
                                <li>
                                    <a href="<?php echo e(route('contact-us')); ?>"><?php echo e(translate('messages.contact_us')); ?></a>
                                </li>
                            </ul>
                        </div>
                        <div class="footer-widget widget-links">
                            <h5 class="subtitle mt-2 text-white"><?php echo e(translate('messages.contact_us')); ?></h5>
                            <ul>
                                <li>
                                    <a href="<?php echo e(route('contact-us')); ?>">Send our team a message through the contact form.</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="copyright text-center mt-3">
                        &copy; <?php echo e(date('Y')); ?> OORZO. All rights reserved.
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
        </footer>
        <!-- ======= Footer Section ======= -->



        <script src="<?php echo e(dynamicAsset('assets/landing/assets_new/js/jquery-3.6.0.min.js')); ?>"></script>
        <script src="<?php echo e(dynamicAsset('assets/landing/assets_new/js/bootstrap.min.js')); ?>"></script>
        <script src="<?php echo e(dynamicAsset('assets/landing/assets_new/js/viewport.jquery.js')); ?>"></script>
        <script src="<?php echo e(dynamicAsset('assets/landing/assets_new/js/wow.min.js')); ?>"></script>
        <script src="<?php echo e(dynamicAsset('assets/landing/assets_new/js/owl.min.js')); ?>"></script>
        <script src="<?php echo e(dynamicAsset('assets/landing/assets_new/js/main.js')); ?>"></script>
        <script src="<?php echo e(dynamicAsset('assets/admin/js/external-navigation-guard.js')); ?>"></script>
        <script src="<?php echo e(dynamicAsset('assets/admin/js/toastr.js')); ?>"></script>
        <script src="<?php echo e(dynamicAsset('assets/admin/js/select2.min.js')); ?>"></script>
        <script src="<?php echo e(dynamicAsset('assets/admin/intltelinput/js/intlTelInput.min.js')); ?>"></script>
        <?php echo Toastr::message(); ?>

        <?php if($errors->any()): ?>
        <script>
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            toastr.error('<?php echo e($error); ?>');
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </script>
    <?php endif; ?>

        <?php echo $__env->yieldPushContent('script_2'); ?>

        <script>
            let placeholderImageUrl = "<?php echo e(dynamicAsset('assets/admin/img/svg/image-upload.svg')); ?>";
            const iconPath = "<?php echo e(dynamicAsset('assets/admin/svg/icons/file.svg')); ?>";
        </script>

        <script>
            "user strict";
            (function ($) {
                $(document).ready(function () {

                    var owl = $(".feature-slider").owlCarousel({
                        loop: true,
                        margin: 10,
                        responsiveClass: true,
                        nav: false,
                        dots: false,
                        loop: false,
                        autoplay: true,
                        autoplayTimeout: 1500,
                        autoplayHoverPause: true,
                        rtl: <?php echo e($direction); ?>,
                        responsive: {
                            0: {
                                items: 1.2,
                            },
                            500: {
                                items: 2,
                            },
                            768: {
                                items: 2.6,
                            },
                            992: {
                                items: 2.7,
                                margin: 30,
                            },
                            1200: {
                                items: 4,
                                margin: 30,
                            },
                        },
                    });

                    var sync1 = $("#sync1");
                    var sync2 = $("#sync2");
                    var thumbnailItemClass = ".owl-item";
                    var slides = sync1
                        .owlCarousel({
                            startPosition: 12,
                            items: 1,
                            loop: false,
                            margin: 0,
                            mouseDrag: false,
                            touchDrag: false,
                            pullDrag: false,
                            scrollPerPage: true,
                            autoplayHoverPause: false,
                            nav: false,
                            dots: false,
                            center: true,
                        })
                        .on("changed.owl.carousel", syncPosition);

                    function syncPosition(el) {
                        $owl_slider = $(this).data("owl.carousel");
                        var loop = $owl_slider.options.loop;

                        if (loop) {
                            var count = el.item.count - 1;
                            var current = Math.round(el.item.index - el.item.count / 2 - 0.5);
                            if (current < 0) {
                                current = count;
                            }
                            if (current > count) {
                                current = 0;
                            }
                        } else {
                            var current = el.item.index;
                        }

                        var owl_thumbnail = sync2.data("owl.carousel");
                        var itemClass = "." + owl_thumbnail.options.itemClass;

                        var thumbnailCurrentItem = sync2
                            .find(itemClass)
                            .removeClass("synced")
                            .eq(current);
                        thumbnailCurrentItem.addClass("synced");

                        if (!thumbnailCurrentItem.hasClass("active")) {
                            var duration = 500;
                            sync2.trigger("to.owl.carousel", [current, duration, true]);
                        }
                    }
                    var thumbs = sync2
                        .owlCarousel({
                            startPosition: 12,
                            items: 3,
                            loop: false,
                            margin: 10,
                            autoplay: false,
                            nav: false,
                            dots: false,
                            center: true,
                            mouseDrag: false,
                            touchDrag: false,
                            responsive: {
                                500: {
                                    items: 3,
                                },
                                768: {
                                    items: 5,
                                },
                            },
                            onInitialized: function (e) {
                                var thumbnailCurrentItem = $(e.target)
                                    .find(thumbnailItemClass)
                                    .eq(this._current);
                                thumbnailCurrentItem.addClass("synced");
                            },
                        })
                        .on("click", thumbnailItemClass, function (e) {
                            e.preventDefault();
                            var duration = 500;
                            var itemIndex = $(e.target).parents(thumbnailItemClass).index();
                            sync1.trigger("to.owl.carousel", [itemIndex, duration, true]);
                        })
                        .on("changed.owl.carousel", function (el) {
                            var number = el.item.index;
                            $owl_slider = sync1.data("owl.carousel");
                            $owl_slider.to(number, 500, true);
                        });
                    sync1.owlCarousel();

                    sync2.children().each(function (index) {
                        $(this).attr("data-position", index);
                    });

                    $(".owl-item:not(':first-child') .img").on("click", function () {
                        sync2.trigger("to.owl.carousel", [$(this).data("position")]);
                        $(".img").removeClass("trns");
                    });
                    $(".owl-item:first-child .img").on("click", function () {
                        sync1.trigger("prev.owl.carousel", [0]);
                        sync2.trigger("prev.owl.carousel", [0]);
                        sync1.trigger("prev.owl.carousel", [0]);
                        sync2.trigger("prev.owl.carousel", [0]);
                        $(".img").removeClass("trns");
                    });

                    // Go to the next item
                    $(".client-next").on("click", function () {
                        sync1.trigger("next.owl.carousel");
                        sync2.trigger("next.owl.carousel");
                        $(".img").addClass("trns");
                    });
                    // Go to the previous item
                    $(".client-prev").on("click", function () {
                        $(".img").addClass("trns");
                        sync1.trigger("prev.owl.carousel");
                        sync2.trigger("prev.owl.carousel");
                    });
                    $(".owl-prev").html('<i class="fas fa-angle-left">');
                    $(".owl-next").html('<i class="fas fa-angle-right">');
                });
            })(jQuery);


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


     document.addEventListener('DOMContentLoaded', function() {
                initTelInputs();
            });

        </script>


    </body>

    </html>
<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/layouts/landing/app.blade.php ENDPATH**/ ?>