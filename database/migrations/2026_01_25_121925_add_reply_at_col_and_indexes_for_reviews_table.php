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
        Schema::table('reviews', function (Blueprint $table) {
            $table->dateTime('reply_at')->nullable();
            $table->index('food_id');
            $table->index('user_id');
            $table->index('restaurant_id');
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('reply_at');
            $table->dropIndex(['food_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['restaurant_id']);
            $table->dropIndex(['order_id']);
        });
    }
};
