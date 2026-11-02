<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A delivery pricing rule for one zone.
     *
     * A zone may hold many rules but only one active at a time — the active one
     * decides how that zone's delivery charge is calculated. Which columns carry
     * meaning depends on `pricing_method`:
     *
     *   area_wise      per-area amounts live in delivery_rule_charges
     *   zip_code_wise  per-zip amounts live in delivery_rule_charges
     *   distance_wise  per_km_charge + maximum_delivery_charge
     *   fixed_amount   fixed_charge
     *
     * `minimum_delivery_charge` is the floor under every method.
     */
    public function up(): void
    {
        if (Schema::hasTable('delivery_rules')) {
            return;
        }

        Schema::create('delivery_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id');
            $table->string('name');
            $table->decimal('minimum_delivery_charge', 10, 2)->default(0);
            // See DeliveryRule::METHOD_*
            $table->string('pricing_method', 20)->default('fixed_amount')->comment('area_wise | zip_code_wise | distance_wise | fixed_amount');

            // distance
            $table->decimal('per_km_charge', 10, 2)->nullable();
            $table->decimal('maximum_delivery_charge', 10, 2)->nullable();

            // fixed
            $table->decimal('fixed_charge', 10, 2)->nullable();

            // A new rule is created switched off; activating it deactivates the
            // zone's current rule. See DeliveryRuleController::status().
            $table->boolean('status')->default(false);
            $table->timestamps();

            $table->index(['zone_id', 'status'], 'delivery_rules_zone_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_rules');
    }
};
