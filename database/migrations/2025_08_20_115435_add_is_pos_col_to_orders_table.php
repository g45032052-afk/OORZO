<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_pos')->default(0);
            $table->index('zone_id');
            $table->index('user_id');
            $table->index('delivery_man_id');
            $table->index('restaurant_id');
            $table->index('subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('is_pos');
            $table->dropIndex(['zone_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['delivery_man_id']);
            $table->dropIndex(['restaurant_id']);
            $table->dropIndex(['subscription_id']);

        });
    }
};
