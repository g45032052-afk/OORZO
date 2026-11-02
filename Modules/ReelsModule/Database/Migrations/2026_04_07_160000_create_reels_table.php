<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->text('description');
            $table->string('thumbnail')->nullable();
            $table->string('video')->nullable();
            $table->boolean('is_always_visible')->default(false);
            $table->boolean('order_now_button')->default(false);
            $table->foreignId('food_id')->nullable()->constrained('food')->nullOnDelete();
            $table->unsignedBigInteger('order_count')->default(0);
            $table->decimal('total_sale_amount', 12, 2)->default(0);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('total_views')->default(0);
            $table->unsignedBigInteger('total_likes')->default(0);
            $table->unsignedBigInteger('total_store_visits')->default(0);
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->string('created_by_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reels');
    }
};
