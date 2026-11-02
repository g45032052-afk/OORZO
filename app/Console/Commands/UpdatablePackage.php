<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class UpdatablePackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prepare:updatable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Artisan::call('debugbar:clear');

        // .txt is the only copy of the real provider that reaches a prepared package - the
        // .php beside it is the wizard stub - and the wizard copies it back when it finishes.
        // Snapshotting it here stops it drifting from the provider in the repo.
        $liveRoutes = base_path('app/Providers/RouteServiceProvider.php');
        $restoreRoutes = base_path('app/Providers/RouteServiceProvider.txt');
        if (!copy($liveRoutes, $restoreRoutes)) {
            $this->error('Could not refresh RouteServiceProvider.txt - package not prepared.');

            return 1;
        }

        $routes = base_path('app/Providers/RouteServiceProvider.php');
        $new_routes = base_path('installation/activate_update_routes.txt');
        copy($new_routes, $routes);

        return 0;
    }
}
