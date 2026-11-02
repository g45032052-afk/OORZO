<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Expanded occurrences, one row per applicable date. Rebuilt wholesale on update
     * (delete then re-expand) so a record can never conflict with its own previous rows.
     * Mirrors 6amMart's surge_price_dates.
     */
    public function up(): void
    {
        Schema::create('happy_hour_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('happy_hour_id');
            $table->unsignedBigInteger('zone_id');
            $table->boolean('status')->default(1);
            $table->date('applicable_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();

            $table->index('happy_hour_id');
            $table->index(['zone_id', 'applicable_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('happy_hour_dates');
    }
};
