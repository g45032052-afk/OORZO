<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CartAbandonReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cart:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to users with abandoned carts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        (new \App\Http\Controllers\Admin\NotificationController)->sendCartAbandonNotification();
        $this->info('Cart abandonment notifications sent successfully.');
    }
}
