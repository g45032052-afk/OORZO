<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Free delivery, authored per zone.
     *
     * A zone holds at most one setup — the unique index is what enforces it —
     * and the setup decides when a customer ordering inside that zone pays no
     * delivery charge. `type` says on what terms:
     *
     *   all_restaurant     every order in the zone, whatever it costs
     *   specific_criteria  only once the order reaches minimum_order_amount;
     *                      a null amount means every order still qualifies
     *
     * The cost is always borne by the admin, so an order freed by this setup is
     * recorded with free_delivery_by = 'admin', same as the global setting.
     */
    public function up(): void
    {
        if (Schema::hasTable('free_deliveries')) {
            return;
        }

        Schema::create('free_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id');
            // See FreeDelivery::TYPE_*
            $table->string('type', 30)->default('all_restaurant')->comment('all_restaurant | specific_criteria');
            $table->decimal('minimum_order_amount', 10, 2)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique('zone_id', 'free_deliveries_zone_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('free_deliveries');
    }
};
