<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What the BOGO free items on an order were worth.
 *
 * A free line is charged nothing, so the give-away leaves no trace in any of the money
 * columns - the order simply never mentions the item the restaurant handed over. This
 * records its menu value so the payout can book it the way a happy hour is booked: the
 * restaurant's own promotion, borne entirely by the restaurant.
 *
 * It is deliberately kept out of restaurant_discount_amount. That column is a reduction of
 * what the customer pays, and this is not - the customer was never charged for the item.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('bogo_discount_amount', 24, 3)->default(0)->after('happy_hour_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('bogo_discount_amount');
        });
    }
};
