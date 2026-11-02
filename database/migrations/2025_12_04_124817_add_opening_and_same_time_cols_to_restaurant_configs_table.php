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
        Schema::table('restaurant_configs', function (Blueprint $table) {
            $table->boolean('opening_closing_status')->default(0);
            $table->boolean('same_time_for_every_day')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurant_configs', function (Blueprint $table) {
            $table->dropColumn('opening_closing_status');
            $table->dropColumn('same_time_for_every_day');
        });
    }
};
