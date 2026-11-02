<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bogo_offer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bogo_offer_restaurant_id');

            // Kept live so stock, availability and status resolve against the real food.
            // Everything below it is frozen so later edits to the food cannot move the offer.
            $table->unsignedBigInteger('food_id');

            // buy | get
            $table->string('type', 5);
            $table->unsignedInteger('quantity')->default(1);

            $table->string('food_name')->nullable();
            $table->string('food_image')->nullable();

            // Frozen unit price. Always 0 for get rows - the free item is fully free,
            // add-ons included.
            $table->double('price', 24, 3)->default(0);

            $table->text('variations')->nullable();
            $table->text('variation_options')->nullable();
            $table->text('add_on_ids')->nullable();
            $table->text('add_on_qtys')->nullable();

            $table->timestamps();

            $table->index(['bogo_offer_restaurant_id', 'type']);
            $table->index('food_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bogo_offer_items');
    }
};
