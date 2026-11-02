<?php
    $metaData = \App\CentralLogics\Helpers::get_data_settings([
        'meta_data_title','meta_data_description','meta_data_image','admin_meta_index','admin_meta_no_follow','admin_meta_no_image_index','admin_meta_no_archive','admin_meta_no_snippet',
        'admin_meta_max_snippet','admin_meta_max_snippet_value','admin_meta_max_video_preview','admin_meta_max_video_preview_value','admin_meta_max_image_preview','admin_meta_max_image_preview_value'
    ]);

    $businessName = \App\CentralLogics\Helpers::get_business_settings('business_name');
    $title = $metaData['meta_data_title']?->value ?? $businessName;
    $description = $metaData['meta_data_description']?->value ?? $businessName . ' — best platform for your needs.';
    $image = \App\CentralLogics\Helpers::get_full_url(
        'meta_data_image',
        $metaData['meta_data_image']?->value ?? '',
        $metaData['meta_data_image']?->storage[0]?->value ?? 'public',
        'upload_image'
    );
    $url = url()->current();

$robotsContent=null;
    if( isset($metaData['admin_meta_index'])){
        $robots = [
            ($metaData['admin_meta_index']?->value ?? 1) == 0 ? 'noindex' : 'index',
            ($metaData['admin_meta_no_follow']?->value ?? null) ?: 'follow',
            $metaData['admin_meta_no_image_index']?->value ?: null,
            $metaData['admin_meta_no_archive']?->value ?: null,
            $metaData['admin_meta_no_snippet']?->value ?: null,
            $metaData['admin_meta_max_snippet']?->value && $metaData['admin_meta_max_snippet_value']?->value ? 'max-snippet:' . $metaData['admin_meta_max_snippet_value']->value : null,
            $metaData['admin_meta_max_video_preview']?->value && $metaData['admin_meta_max_video_preview_value']?->value ? 'max-video-preview:' . $metaData['admin_meta_max_video_preview_value']->value : null,
            $metaData['admin_meta_max_image_preview']?->value && $metaData['admin_meta_max_image_preview_value']?->value ? 'max-image-preview:' . $metaData['admin_meta_max_image_preview_value']->value : null,
        ];

        $robotsContent = implode(', ', array_filter($robots));
    }

?>

    <!-- ==================== BASIC SEO (Google, Bing, etc.) ==================== -->

    <meta name="description" content="<?php echo e($description); ?>">

    <meta name="author" content="<?php echo e($businessName); ?>">
    <link rel="canonical" href="<?php echo e($url); ?>">

    <!-- ==================== OPEN GRAPH (Facebook, LinkedIn, WhatsApp, etc.) ==================== -->
    <meta property="og:title" content="<?php echo e($title); ?>">
    <meta property="og:description" content="<?php echo e($description); ?>">
    <meta property="og:image" content="<?php echo e($image); ?>">
    <meta property="og:url" content="<?php echo e($url); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo e($businessName); ?>">
    <meta property="og:locale" content="<?php echo e(app()->getLocale()); ?>">


    <!-- ==================== FACEBOOK ==================== -->
    <meta property="fb:app_id" content="<?php echo e($businessName); ?>">
    <meta property="og:updated_time" content="<?php echo e(now()->toIso8601String()); ?>">

    <!-- ==================== TWITTER ==================== -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo e($title); ?>">
    <meta name="twitter:description" content="<?php echo e($description); ?>">
    <meta name="twitter:image" content="<?php echo e($image); ?>">
    <meta name="twitter:url" content="<?php echo e($url); ?>">
    <meta name="twitter:site" content="<?php echo e($businessName); ?>">
    <meta name="twitter:creator" content="<?php echo e($businessName); ?>">

    <!-- ==================== LINKEDIN ==================== -->
    <meta property="og:image:alt" content="<?php echo e($title); ?>">
    <meta name="linkedin:owner" content="<?php echo e($businessName); ?>">

    <!-- ==================== PINTEREST ==================== -->
    <meta name="pinterest-rich-pin" content="true">
    <meta property="og:see_also" content="<?php echo e($url); ?>">
    <meta name="pinterest:title" content="<?php echo e($title); ?>">
    <meta name="pinterest:description" content="<?php echo e($description); ?>">
    <meta name="pinterest:image" content="<?php echo e($image); ?>">

    <!-- ==================== TIKTOK ==================== -->
    <meta name="tiktok:card" content="summary_large_image">
    <meta name="tiktok:title" content="<?php echo e($title); ?>">
    <meta name="tiktok:description" content="<?php echo e($description); ?>">
    <meta name="tiktok:image" content="<?php echo e($image); ?>">

    <!-- ==================== SNAPCHAT ==================== -->
    <meta name="snapchat:card" content="summary_large_image">
    <meta name="snapchat:title" content="<?php echo e($title); ?>">
    <meta name="snapchat:description" content="<?php echo e($description); ?>">
    <meta name="snapchat:image" content="<?php echo e($image); ?>">

    <!-- ==================== UNIVERSAL MESSAGING APPS (WhatsApp, Discord, Telegram, Slack, etc.) ==================== -->
    <meta property="og:image:secure_url" content="<?php echo e($image); ?>">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- ==================== OPTIONAL ENHANCEMENTS ==================== -->
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-title" content="<?php echo e($title); ?>">
    <meta name="application-name" content="<?php echo e($title); ?>">

    <meta name="robots" content="<?php echo e($robotsContent); ?>">

<?php /**PATH F:\Oorzo\Admin panel new install V9.3\resources\views/layouts/landing/seo.blade.php ENDPATH**/ ?>