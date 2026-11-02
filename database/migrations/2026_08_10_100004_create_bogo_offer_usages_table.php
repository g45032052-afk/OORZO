<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bogo_offer_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bogo_offer_id');
            $table->unsignedBigInteger('order_id');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('is_guest')->default(0);

            // Guests are identified by phone, normalised to E.164 on write. The raw value
            // in orders.delivery_address is unnormalised, so matching on it directly would
            // let the same person re-use the offer under a different formatting.
            $table->string('phone', 30)->nullable();

            // Bundles taken in this order, not line quantity.
            $table->unsignedInteger('quantity')->default(1);

            $table->timestamps();

            $table->index('bogo_offer_id');
            $table->index('order_id');
            $table->index(['bogo_offer_id', 'user_id']);
            $table->index(['bogo_offer_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bogo_offer_usages');
    }
};
