<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Definition table. Concrete occurrences are expanded into happy_hour_dates at save
     * time so runtime lookup is a flat date+time query rather than schedule interpretation.
     * Mirrors 6amMart's surge_prices.
     */
    public function up(): void
    {
        Schema::create('happy_hours', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('short_description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('icon')->nullable();

            // Overlap is enforced zone-wide: two happy hours may not share a time window in
            // the same zone even when they target different restaurants.
            $table->unsignedBigInteger('zone_id');

            // Percentage only.
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('min_order_amount', 24, 2)->nullable();

            $table->boolean('status')->default(1);

            // Weekly only. A permanent rule writes no rows into happy_hour_dates and nulls
            // its date range, so the resolver has to consult both tables.
            $table->boolean('is_permanent')->default(0);

            $table->enum('duration_type', ['daily', 'weekly', 'custom'])->default('daily');

            $table->json('weekly_days')->nullable();
            $table->json('custom_days')->nullable();

            // Bare start times. Every window is a fixed one hour, so the end is derived
            // rather than stored as a "H:i - H:i" pair.
            $table->json('custom_times')->nullable();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->string('slug')->nullable();
            $table->timestamps();

            $table->index('zone_id');
            $table->index('status');
            $table->index('duration_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('happy_hours');
    }
};
