<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('surge_prices')) {
            return;
        }

        Schema::create('surge_prices', function (Blueprint $table) {
            $table->id();
            $table->string('surge_price_name', 100);
            $table->string('customer_note', 100)->nullable();
            $table->boolean('customer_note_status')->default(false);
            $table->foreignId('zone_id');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('price_type', 20)->default('amount')->comment('amount | percent');
            $table->boolean('status')->default(true);
            $table->boolean('is_permanent')->default(false);
            $table->string('duration_type', 20)->default('daily')->comment('daily | weekly | custom');
            $table->json('weekly_days')->nullable();
            $table->json('custom_days')->nullable();
            $table->json('custom_times')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();
            $table->index(['zone_id', 'status', 'duration_type', 'is_permanent'], 'surge_prices_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surge_prices');
    }
};
