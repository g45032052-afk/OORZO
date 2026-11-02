<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One charge line of an area-wise or zip-code-wise rule.
     *
     * Exactly one of area_id / zip_code_id is set, decided by the parent rule's
     * pricing_method. Distance and fixed rules keep their figures on the rule
     * itself and store nothing here.
     *
     * Rows are deleted and rebuilt whenever the rule is saved, so they always
     * mirror the areas or codes the zone holds at that moment.
     */
    public function up(): void
    {
        if (Schema::hasTable('delivery_rule_charges')) {
            return;
        }

        Schema::create('delivery_rule_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_rule_id');
            $table->foreignId('area_id')->nullable();
            $table->foreignId('zip_code_id')->nullable();
            $table->decimal('charge', 10, 2)->default(0);
            $table->timestamps();

            $table->index('delivery_rule_id', 'delivery_rule_charges_rule_index');
            $table->index(['delivery_rule_id', 'area_id'], 'delivery_rule_charges_area_index');
            $table->index(['delivery_rule_id', 'zip_code_id'], 'delivery_rule_charges_zip_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_rule_charges');
    }
};
