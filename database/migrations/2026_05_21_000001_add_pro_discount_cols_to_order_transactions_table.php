<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_transactions', function (Blueprint $table) {
            $table->double('pro_discount', 23, 3)->default(0);
            $table->double('pro_delivery_discount', 23, 3)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('order_transactions', function (Blueprint $table) {
            $table->dropColumn(['pro_discount', 'pro_delivery_discount']);
        });
    }
};
