<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bogo_offer_restaurant', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bogo_offer_id');
            $table->unsignedBigInteger('restaurant_id');

            // pending | approved | rejected | left
            $table->string('status', 20)->default('pending');
            $table->text('rejection_reason')->nullable();

            // admin | vendor - who initiated the enrolment. An admin-initiated one still
            // needs the vendor to accept before the offer goes live for that restaurant.
            $table->string('requested_by', 10)->default('admin');

            $table->dateTime('joined_at')->nullable();

            // Derived at approval: sum of the buy items at full price. Get items and all
            // add-ons contribute nothing.
            $table->double('bundle_price', 24, 3)->default(0);

            // Hash of the sorted buy+get set (food, variation, add-ons, qty, type). A
            // restaurant may reuse the same food across offers, but not the same
            // combination - validated in the controller against non-expired offers so an
            // expired offer's combination becomes reusable.
            $table->string('combination_signature', 64)->nullable();

            $table->timestamps();

            $table->unique(['bogo_offer_id', 'restaurant_id']);
            $table->index(['restaurant_id', 'combination_signature']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bogo_offer_restaurant');
    }
};
