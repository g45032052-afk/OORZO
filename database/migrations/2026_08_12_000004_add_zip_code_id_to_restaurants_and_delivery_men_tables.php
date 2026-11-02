<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Links restaurants and deliverymen to a zip code.
     *
     * The Zip Code Setup list shows a Vendor and a Deliveryman count per code,
     * which needs a foreign key on each side. Nullable and unconstrained, the
     * same as the area columns alongside them.
     *
     * NOTE: nothing assigns these yet — no screen in the supplied design lets an
     * admin pick a zip code for a restaurant or a deliveryman, so both counts
     * read zero until that is decided.
     */
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (! Schema::hasColumn('restaurants', 'zip_code_id')) {
                $table->foreignId('zip_code_id')->nullable();
                $table->index('zip_code_id', 'restaurants_zip_code_id_index');
            }
        });

        Schema::table('delivery_men', function (Blueprint $table) {
            if (! Schema::hasColumn('delivery_men', 'zip_code_id')) {
                $table->foreignId('zip_code_id')->nullable();
                $table->index('zip_code_id', 'delivery_men_zip_code_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (Schema::hasColumn('restaurants', 'zip_code_id')) {
                $table->dropIndex('restaurants_zip_code_id_index');
                $table->dropColumn('zip_code_id');
            }
        });

        Schema::table('delivery_men', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_men', 'zip_code_id')) {
                $table->dropIndex('delivery_men_zip_code_id_index');
                $table->dropColumn('zip_code_id');
            }
        });
    }
};
