<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bogo_offers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();

            // Fixed at the offer level, with no upper bound. A restaurant enrols once and
            // must supply buy items summing to buy_qty and get items summing to get_qty.
            // Both become immutable as soon as any restaurant joins.
            $table->unsignedInteger('buy_qty')->default(1);
            $table->unsignedInteger('get_qty')->default(1);

            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();

            // Reaching usage_limit_total hides the offer outright; reaching
            // usage_limit_per_customer leaves it visible but blocks adding.
            $table->unsignedInteger('usage_limit_total')->nullable();
            $table->unsignedInteger('usage_limit_per_customer')->nullable();
            $table->unsignedInteger('total_uses')->default(0);

            // ['delivery', 'take_away', 'dine_in'] - intersected against the restaurant's
            // own enabled order types when deciding whether to list it.
            $table->json('order_types')->nullable();

            $table->boolean('status')->default(1);
            $table->string('slug')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('slug');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bogo_offers');
    }
};
