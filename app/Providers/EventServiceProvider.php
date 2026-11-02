<?php

namespace App\Providers;

use App\Models\BusinessSetting;
use App\Models\DataSetting;
use App\Models\DeliveryMan;
use App\Models\Refund;
use App\Models\Restaurant;
use App\Observers\BusinessSettingObserver;
use App\Observers\DataSettingObserver;
use App\Observers\Erp\DeliveryManObserver;
use App\Observers\Erp\RefundObserver;
use App\Observers\Erp\RestaurantObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        BusinessSetting::observe(BusinessSettingObserver::class);
        DataSetting::observe(DataSettingObserver::class);
        Restaurant::observe(RestaurantObserver::class);
        DeliveryMan::observe(DeliveryManObserver::class);
        Refund::observe(RefundObserver::class);
    }
}
