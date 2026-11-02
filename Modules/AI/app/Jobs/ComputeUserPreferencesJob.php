<?php

namespace Modules\AI\app\Jobs;

use Modules\AI\app\Services\Personalization\PersonalizationService;
use Modules\AI\app\Models\CustomerPreferenceSummary;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Rebuilds a customer's preference summary (or all dirty summaries).
 * Single-module (food) — no module_id.
 */
class ComputeUserPreferencesJob implements ShouldQueue
{
    use Queueable;

    public ?int $userId;

    public function __construct(?int $userId = null)
    {
        $this->userId = $userId;
    }

    public function handle(): void
    {
        if ($this->userId) {
            PersonalizationService::rebuildSummary($this->userId);
            return;
        }

        $dirtyUserIds = CustomerPreferenceSummary::where('update_count', '>=', PersonalizationService::REBUILD_THRESHOLD)
            ->pluck('user_id');

        foreach ($dirtyUserIds as $userId) {
            PersonalizationService::rebuildSummary((int) $userId);
        }
    }
}
