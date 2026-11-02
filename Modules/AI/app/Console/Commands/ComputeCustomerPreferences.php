<?php

namespace Modules\AI\app\Console\Commands;

use Modules\AI\app\Services\Personalization\PersonalizationService;
use Modules\AI\app\Models\CustomerPreferenceSummary;
use Illuminate\Console\Command;

/**
 * Rebuild customer preference summaries. Single-module (food) — no module_id.
 */
class ComputeCustomerPreferences extends Command
{
    protected $signature = 'preferences:compute {--user_id= : Rebuild summary for a specific user} {--rebuild : Rebuild only dirty summaries}';
    protected $description = 'Rebuild customer preference summaries';

    public function handle()
    {
        $userId = $this->option('user_id');

        if ($userId) {
            $this->info("Rebuilding summary for user #{$userId}...");
            PersonalizationService::rebuildSummary((int) $userId);
            $this->info('Done.');
            return;
        }

        $query = CustomerPreferenceSummary::query();
        if ($this->option('rebuild')) {
            $query->where('update_count', '>=', PersonalizationService::REBUILD_THRESHOLD);
        }
        $userIds = $query->pluck('user_id');

        if ($userIds->isEmpty()) {
            $this->info('No summaries found. Users need to interact first to generate preferences.');
            return;
        }

        $this->info("Rebuilding {$userIds->count()} summaries...");
        $bar = $this->output->createProgressBar($userIds->count());
        $bar->start();

        foreach ($userIds as $uid) {
            PersonalizationService::rebuildSummary((int) $uid);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done.');
    }
}
