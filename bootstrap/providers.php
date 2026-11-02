<?php

return [
    // The Builder module's service provider scans modules via module_path()
    // during register(), which constructs nwidart Module objects that resolve
    // $app['cache'] (Module::__construct). Register the cache provider first so
    // `cache` is bound before that scan — registerConfiguredProviders() registers
    // Illuminate\* providers ahead of third-party ones, so this lands early.
    Illuminate\Cache\CacheServiceProvider::class,
    App\Providers\AppServiceProvider::class,
    App\Providers\ConfigServiceProvider::class,
    App\Providers\FirebaseServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
    App\Providers\ScheduleServiceProvider::class,
];
