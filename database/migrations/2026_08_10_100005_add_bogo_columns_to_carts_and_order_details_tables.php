<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BOGO components stay individual rows so existing price/quantity/stock maths keeps
     * working untouched. bogo_group_id ties one bundle instance together; the API groups
     * on it so a bundle presents as a single line and counts as one item.
     */
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->unsignedBigInteger('bogo_offer_id')->nullable();
            $table->string('bogo_group_id', 40)->nullable();
            $table->boolean('is_free_item')->default(0);

            $table->index('bogo_group_id');
            $table->index('bogo_offer_id');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->unsignedBigInteger('bogo_offer_id')->nullable();
            $table->string('bogo_group_id', 40)->nullable();
            $table->boolean('is_free_item')->default(0);

            $table->index('bogo_group_id');
            $table->index('bogo_offer_id');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex(['bogo_group_id']);
            $table->dropIndex(['bogo_offer_id']);
            $table->dropColumn(['bogo_offer_id', 'bogo_group_id', 'is_free_item']);
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropIndex(['bogo_group_id']);
            $table->dropIndex(['bogo_offer_id']);
            $table->dropColumn(['bogo_offer_id', 'bogo_group_id', 'is_free_item']);
        });
    }
};
