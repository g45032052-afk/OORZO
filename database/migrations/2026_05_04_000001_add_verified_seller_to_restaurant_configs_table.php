<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_configs', function (Blueprint $table) {
            $table->boolean('verified_seller')->default(0)->after('customer_order_date');
            $table->boolean('has_seen_verified_badge_popup')->default(0)->after('verified_seller');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_configs', function (Blueprint $table) {
            $table->dropColumn(['verified_seller', 'has_seen_verified_badge_popup']);
        });
    }
};
