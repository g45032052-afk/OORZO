<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Whether a zone's incentives are being given out.
     *
     * The zone settings panel offers incentives as one switch, and switching
     * them off must not throw away what the admin typed — the tiers stay in
     * `incentives` and this flag decides whether they are read. Every zone that
     * already has a tier keeps giving it, so the flag is backfilled on.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('zones', 'incentive_status')) {
            Schema::table('zones', function (Blueprint $table) {
                $table->boolean('incentive_status')->default(false)->after('max_cod_order_amount');
            });
        }

        DB::table('zones')->whereIn('id', function ($query) {
            $query->select('zone_id')->from('incentives');
        })->update(['incentive_status' => 1]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('zones', 'incentive_status')) {
            Schema::table('zones', function (Blueprint $table) {
                $table->dropColumn('incentive_status');
            });
        }
    }
};
