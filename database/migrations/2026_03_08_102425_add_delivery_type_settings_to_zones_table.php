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
        Schema::table('zones', function (Blueprint $table) {
            // Delivery type feature configuration
            $table->boolean('additional_delivery_option_status')
                ->default(0)
                ->after('increase_delivery_charge_message');

            $table->integer('minimum_delivery_time')
                ->comment('Minimum delivery time in minutes')
                ->nullable()
                ->after('additional_delivery_option_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn([
                'additional_delivery_option_status',
                'minimum_delivery_time'
            ]);
        });
    }
};
