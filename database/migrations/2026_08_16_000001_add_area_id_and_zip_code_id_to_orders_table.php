<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Records which area or zip code the customer picked at checkout.
     *
     * When the zone's active delivery rule prices area-wise or zip-code-wise, the
     * charge comes from the customer's pick rather than from the distance. Without
     * this there is no record of *why* an order was charged what it was — the fee
     * would be unexplainable after the fact, and unreproducible if the admin later
     * edits the rule.
     *
     * Nullable and unconstrained: most orders are priced by distance or a fixed
     * amount and have neither, and an area must stay deletable.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'area_id')) {
                $table->foreignId('area_id')->nullable();
            }
            if (! Schema::hasColumn('orders', 'zip_code_id')) {
                $table->foreignId('zip_code_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['area_id', 'zip_code_id'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
