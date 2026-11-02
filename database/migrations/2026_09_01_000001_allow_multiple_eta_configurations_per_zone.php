<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A zone may now hold several ETA configurations, of which exactly one is active.
     *
     * The unique index that allowed one row per zone is what made a second
     * configuration impossible, so it goes; a plain index takes its place because
     * every read is still "the configuration of this zone". Only one may be active
     * at a time, which the status and delete endpoints enforce - the same
     * arrangement delivery rules already use, and for the same reason: a rule set
     * cannot be replaced without the zone being briefly left with none, and an
     * admin authoring the replacement first is how that is avoided.
     */
    public function up(): void
    {
        if (! Schema::hasTable('eta_configurations')) {
            return;
        }

        $indexes = collect(DB::select('SHOW INDEX FROM eta_configurations'))->pluck('Key_name');

        if ($indexes->contains('eta_configurations_zone_unique')) {
            Schema::table('eta_configurations', function (Blueprint $table) {
                $table->dropUnique('eta_configurations_zone_unique');
            });
        }

        if (! $indexes->contains('eta_configurations_zone_id_index')) {
            Schema::table('eta_configurations', function (Blueprint $table) {
                $table->index('zone_id');
            });
        }

        // Existing data holds at most one row per zone, so there is nothing to
        // reconcile. Should a zone somehow carry two active rows - a database
        // restored from a panel that never had the unique index - the newest wins,
        // because that is the one the admin switched on last.
        $zonesWithSeveralActive = DB::table('eta_configurations')
            ->select('zone_id')
            ->where('status', 1)
            ->groupBy('zone_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('zone_id');

        foreach ($zonesWithSeveralActive as $zoneId) {
            $keep = DB::table('eta_configurations')
                ->where('zone_id', $zoneId)
                ->where('status', 1)
                ->orderByDesc('id')
                ->value('id');

            DB::table('eta_configurations')
                ->where('zone_id', $zoneId)
                ->where('id', '!=', $keep)
                ->update(['status' => 0]);
        }
    }

    /**
     * Restoring the unique index needs the extra rows gone, so every zone is left
     * with the configuration it is estimating from - the active one where there is
     * one, its newest otherwise.
     */
    public function down(): void
    {
        if (! Schema::hasTable('eta_configurations')) {
            return;
        }

        $zoneIds = DB::table('eta_configurations')->distinct()->pluck('zone_id');

        foreach ($zoneIds as $zoneId) {
            $keep = DB::table('eta_configurations')
                ->where('zone_id', $zoneId)
                ->orderByDesc('status')
                ->orderByDesc('id')
                ->value('id');

            DB::table('eta_configurations')
                ->where('zone_id', $zoneId)
                ->where('id', '!=', $keep)
                ->delete();
        }

        $indexes = collect(DB::select('SHOW INDEX FROM eta_configurations'))->pluck('Key_name');

        if ($indexes->contains('eta_configurations_zone_id_index')) {
            Schema::table('eta_configurations', function (Blueprint $table) {
                $table->dropIndex('eta_configurations_zone_id_index');
            });
        }

        if (! $indexes->contains('eta_configurations_zone_unique')) {
            Schema::table('eta_configurations', function (Blueprint $table) {
                $table->unique('zone_id', 'eta_configurations_zone_unique');
            });
        }
    }
};
