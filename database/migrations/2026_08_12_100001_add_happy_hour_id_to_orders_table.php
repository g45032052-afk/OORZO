<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records which happy hour produced an order's restaurant discount.
 *
 * Two jobs. It is the only thing that tells a happy hour discount apart from any other
 * vendor borne product discount once the order is written, so reports can answer what a
 * happy hour actually cost. And OrderLogic reads it to take the no-split payout branch:
 * the restaurant bears the whole discount and the admin's commission is left intact.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('happy_hour_id')->nullable()->after('discount_on_product_by');
            $table->index('happy_hour_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['happy_hour_id']);
            $table->dropColumn('happy_hour_id');
        });
    }
};
