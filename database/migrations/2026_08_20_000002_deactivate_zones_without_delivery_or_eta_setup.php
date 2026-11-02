<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Switches off zones that were already on before delivery rules and ETA configurations existed.
 *
 * ZoneController::status() refuses to switch a zone on unless Zone::isReadyToActivate() passes -
 * one active delivery_rules row and one active eta_configurations row - but that guard only runs
 * when somebody moves the toggle. An installation upgrading into these features carries zones
 * that were switched on years earlier and have neither, and nothing has ever re-checked them. On
 * this database that is 13 of 14 active zones.
 *
 * A zone in that state cannot price a delivery or quote a time for one, so it is switched off
 * here and the admin switches it back on once it is set up - through the same guard every other
 * zone passes. Zone::scopeEffective() already keeps such a zone out of customer routing, so this
 * mostly changes what the panel shows rather than what customers can order; the exception is the
 * readers that test `status` directly, which until now saw an unserviceable zone as live.
 *
 * The default zone is left alone. ZoneController::status() will not let one be deactivated, and
 * Helpers::getZoneIds() falls back to it when a request carries no zone, so switching it off here
 * would create a state the panel cannot undo. It stays on, still filtered out of routing by
 * scopeEffective() until it is configured.
 *
 * Idempotent: a second run finds nothing left to switch off.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! $this->tablesPresent()) {
            return;
        }

        $unready = $this->unreadyActiveZones();

        if ($unready->isEmpty()) {
            return;
        }

        DB::table('zones')->whereIn('id', $unready->keys())->update(['status' => 0]);

        // Named rather than counted, because an admin who finds a zone switched off after an
        // upgrade needs to know it was this and not somebody on their team.
        Log::warning('Zones switched off: no active delivery rule and/or ETA configuration.', [
            'zones' => $unready->all(),
        ]);
    }

    /**
     * Not reversible.
     *
     * Which zones were on beforehand is not recorded, and switching them all back on would
     * re-activate ones an admin had deliberately switched off. They are re-activated from the
     * panel, one at a time, once each is configured.
     */
    public function down(): void
    {
        //
    }

    private function tablesPresent(): bool
    {
        foreach (['zones', 'delivery_rules', 'eta_configurations'] as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return Schema::hasColumn('zones', 'is_default');
    }

    /** Active, not default, and missing either setup — keyed by id, valued by name. */
    private function unreadyActiveZones()
    {
        return DB::table('zones')
            ->where('status', 1)
            ->where('is_default', 0)
            ->where(function ($query) {
                $query->whereNotExists($this->activeSetup('delivery_rules'))
                    ->orWhereNotExists($this->activeSetup('eta_configurations'));
            })
            ->orderBy('id')
            ->pluck('name', 'id');
    }

    /** A correlated "this zone has an active row in $table" subquery. */
    private function activeSetup(string $table): callable
    {
        return function ($query) use ($table) {
            $query->selectRaw('1')
                ->from($table)
                ->whereColumn($table.'.zone_id', 'zones.id')
                ->where($table.'.status', 1);
        };
    }
};
