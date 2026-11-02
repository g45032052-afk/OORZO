<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The ETA inputs an order was quoted against, frozen at placement.
 *
 * EtaLogic used to recompute from the live eta_configurations row and the live
 * zone_delivery_options row on every order-details call, so editing either shifted the ETA of
 * orders already in flight - a customer could watch their estimate jump backwards. The delivery
 * charge was already frozen on the order; this brings the estimate into line with it.
 *
 * Nullable on purpose: orders placed before this shipped have no snapshot and keep falling back
 * to the live configuration, which is the behaviour they were quoted under anyway.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders') || Schema::hasColumn('orders', 'eta_snapshot')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->json('eta_snapshot')->nullable()->after('delivery_duration');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders') || ! Schema::hasColumn('orders', 'eta_snapshot')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('eta_snapshot');
        });
    }
};
