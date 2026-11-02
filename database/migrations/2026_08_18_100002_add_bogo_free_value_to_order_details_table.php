<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What one free BOGO unit was worth, recorded on the line that gave it away.
 *
 * orders.bogo_discount_amount already records the give-away, but only for the order as a
 * whole. The Food Report attributes every figure to a food, so it had nothing to show: the
 * free line is priced at 0 and carries no discount_on_food, and the offer's frozen worth sits
 * in bogo_offer_items, which a report cannot rely on - an enrolment can be edited or deleted
 * long after the order was placed.
 *
 * Per unit rather than per line, matching discount_on_food beside it, so a report multiplies
 * by quantity the same way for both. Null on every non-BOGO line and on rows written before
 * this column existed; nothing back-fills it, because the worth of a free item at the moment
 * of an old sale is not recoverable from today's menu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->double('bogo_free_value', 24, 3)->nullable()->after('is_free_item');
        });
    }

    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('bogo_free_value');
        });
    }
};
