<?php

namespace App\Providers;

use Exception;
use App\Traits\AddonHelper;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

// ini_set('memory_limit', '512M');
ini_set("memory_limit",-1);
class AppServiceProvider extends ServiceProvider
{
    use AddonHelper;

    /**
     * Register any application services.
     *
     * @return void
     */
    private const BUILDER_ADAPTER_PATH = 'Builder';
    private const BUILDER_CONTRACT_NS  = 'Modules\\Builder\\Contracts\\';
    private const BUILDER_ADAPTER_NS   = 'App\\Builder\\';

    public function register()
    {
        $this->registerBuilderBindings();
    }

    /**
     * Auto-discover Builder host adapters in app/Builder/ and bind each one to
     * whichever Modules\Builder\Contracts\* interface it implements. Gated on
     * the add-on publish status, so it stays dormant until Builder is enabled
     * AND adapters exist — installing the module alone is a no-op.
     */
    private function registerBuilderBindings(): void
    {
        if (!\addon_published_status('Builder')) {
            return;
        }

        $adapterPath = app_path(self::BUILDER_ADAPTER_PATH);
        if (!is_dir($adapterPath)) {
            return;
        }

        foreach (glob($adapterPath . '/*.php') as $file) {
            $class = self::BUILDER_ADAPTER_NS . basename($file, '.php');

            try {
                if (!class_exists($class)) {
                    continue;
                }
                $interfaces = class_implements($class) ?: [];
            } catch (\Throwable $e) {
                // A missing interface is a fatal Error, not a class_exists() miss, so an
                // adapter naming a contract an older Builder module does not ship would take
                // the whole application down on boot. Skipping leaves the module's own Null*
                // default bound. error_log(), not info(): this runs inside register(), and
                // resolving the log manager here can throw - which is what the catch prevents.
                \error_log('Builder adapter skipped - '.$class.': '.$e->getMessage()
                    .' (the installed Builder module is older than this release)');

                continue;
            }

            foreach ($interfaces as $interface) {
                if (str_starts_with($interface, self::BUILDER_CONTRACT_NS)) {
                    $this->app->bind($interface, $class);
                }
            }
        }
    }

    /**
     * Bootstrap any application services.
     *
     */
    public function boot(Request $request)
    {
        if(env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }
        Request::macro('isAny', function (array $patterns) {
            return collect($patterns)->contains(fn ($pattern) => Request::is($pattern));
        });

        // Personalization write trigger — records item signals on order
        // delivery (no-op when the personalization toggle is off).
        \App\Models\Order::observe(\App\Observers\OrderObserver::class);

        // Standing admin-request prompt on the vendor panel. Composed here rather than
        // queried in the blade, and only for a logged-in vendor - the layout also renders
        // on the login screen, where get_restaurant_id() would throw.
        \Illuminate\Support\Facades\View::composer('layouts.vendor.partials._admin_request_alert', function ($view) {
            $restaurantId = (auth('vendor')->check() || auth('vendor_employee')->check())
                ? \App\CentralLogics\Helpers::get_restaurant_id()
                : null;

            // An employee without the module permission cannot open the list the prompt
            // links to, so do not prompt them at all - the owner or a permitted employee
            // still gets it.
            $canBogo = $restaurantId && \App\CentralLogics\Helpers::employee_module_permission_check('bogo_offer');
            $canHappyHour = $restaurantId && \App\CentralLogics\Helpers::employee_module_permission_check('happy_hour');

            // Unanswered AND unseen: checked is cleared to 1 the moment the restaurant opens
            // the promotion's list, so an invitation is announced once rather than on every
            // page load, and again only when the admin sends a new one. Same column and same
            // rule as orders.checked behind the new-order popup.
            $view->with([
                'pendingBogo' => $canBogo && \App\Models\BogoOfferRestaurant::where('restaurant_id', $restaurantId)
                    ->where('status', 'pending')->where('requested_by', 'admin')->where('checked', 0)->exists(),
                'pendingHappyHour' => $canHappyHour && \App\Models\HappyHourRestaurant::where('restaurant_id', $restaurantId)
                    ->where('status', 'pending')->where('requested_by', 'admin')->where('checked', 0)->exists(),
            ]);
        });


        if (!App::runningInConsole()) {
            Config::set('addon_admin_routes',$this->get_addon_admin_routes());
            Config::set('get_payment_publish_status',$this->get_payment_publish_status());
            Config::set('default_pagination', 25);
            Paginator::useBootstrap();
            try {
                foreach(Helpers::get_view_keys() as $key=>$value)
                {
                    view()->share($key, $value);
                }
            } catch (\Exception $e){

            }
        }
    }
}
