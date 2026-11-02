<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CustomerSubscriptionReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer-subscription:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to Pro customers whose subscription is nearing expiration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $runner = new class { use \App\Traits\ManagesProCustomerSubscription; };
        $runner->expireDueSubscriptions();
        $runner->sendCustomerSubscriptionExpireNotification();
        $this->info('Subscription expire reminder notifications sent successfully.');
    }
}
