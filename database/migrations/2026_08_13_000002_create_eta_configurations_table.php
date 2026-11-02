<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ETA configuration, authored per zone.
     *
     * A zone holds at most one configuration — the unique index is what
     * enforces it — and the configuration decides how the estimated delivery
     * time shown to a customer ordering inside that zone is arrived at.
     * `calculation_method` says from where the travel part of the estimate
     * comes:
     *
     *   distance_based        from the distance between restaurant and
     *                         customer; `time_gap` widens the estimate into
     *                         the range the customer is shown
     *   fixed_delivery_time   from the restaurant's own minimum and maximum
     *                         delivery time, so the range is already given and
     *                         no gap is kept
     *
     * Every column here is minutes. The two buffers are added whichever method
     * is chosen, and `minimum_delivery_time` is the floor the estimate never
     * falls below.
     */
    public function up(): void
    {
        if (Schema::hasTable('eta_configurations')) {
            return;
        }

        Schema::create('eta_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->foreignId('zone_id');
            // See EtaConfiguration::METHOD_*
            $table->string('calculation_method', 30)->default('distance_based')->comment('distance_based | fixed_delivery_time');
            $table->unsignedSmallInteger('minimum_delivery_time')->nullable();
            $table->unsignedSmallInteger('preparation_buffer')->nullable();
            $table->unsignedSmallInteger('transit_buffer')->nullable();
            // Distance based only — the other method takes its maximum from the restaurant.
            $table->unsignedSmallInteger('time_gap')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique('zone_id', 'eta_configurations_zone_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eta_configurations');
    }
};
