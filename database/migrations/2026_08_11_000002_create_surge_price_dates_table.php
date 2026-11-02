<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('surge_price_dates')) {
            return;
        }

        Schema::create('surge_price_dates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('surge_price_id');
            $table->foreignId('zone_id');
            $table->boolean('status')->default(true);
            $table->date('applicable_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();
            $table->index(['zone_id', 'applicable_date'], 'surge_price_dates_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surge_price_dates');
    }
};
