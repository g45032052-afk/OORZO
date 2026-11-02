<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * How long the map said the journey would take, in seconds.
     *
     * The app asks the distance API for it at checkout and posts it with the
     * order, exactly as it already posts `distance`. It is stored in the unit it
     * arrives in — converting on the way in would hide the unit, and the unit is
     * the one thing about this column worth remembering.
     *
     * Nullable: take-away, dine-in and POS orders never have one, and neither
     * does an order placed by an app build older than this column.
     * {@see App\CentralLogics\EtaLogic} falls back for those.
     */
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'delivery_duration')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('delivery_duration')->nullable()->after('distance');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'delivery_duration')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('delivery_duration');
            });
        }
    }
};
