<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Links restaurants and deliverymen to an area.
     *
     * The Area Setup list shows a Vendor and a Deliveryman count per area, which
     * needs a foreign key on each side. Nullable and unconstrained so existing
     * rows are untouched and an area can be deleted without blocking.
     *
     * NOTE: nothing assigns these yet — no screen in the supplied design lets an
     * admin pick an area for a restaurant or a deliveryman, so both counts read
     * zero until that is decided.
     */
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (! Schema::hasColumn('restaurants', 'area_id')) {
                $table->foreignId('area_id')->nullable();
                $table->index('area_id', 'restaurants_area_id_index');
            }
        });

        Schema::table('delivery_men', function (Blueprint $table) {
            if (! Schema::hasColumn('delivery_men', 'area_id')) {
                $table->foreignId('area_id')->nullable();
                $table->index('area_id', 'delivery_men_area_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (Schema::hasColumn('restaurants', 'area_id')) {
                $table->dropIndex('restaurants_area_id_index');
                $table->dropColumn('area_id');
            }
        });

        Schema::table('delivery_men', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_men', 'area_id')) {
                $table->dropIndex('delivery_men_area_id_index');
                $table->dropColumn('area_id');
            }
        });
    }
};
