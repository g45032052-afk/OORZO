<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('happy_hour_restaurant', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('happy_hour_id');
            $table->unsignedBigInteger('restaurant_id');

            // pending | approved | rejected | left
            $table->string('status', 20)->default('pending');
            $table->text('rejection_reason')->nullable();

            // admin | vendor. An admin-assigned restaurant still needs vendor approval
            // before the happy hour applies to it.
            $table->string('requested_by', 10)->default('admin');

            $table->dateTime('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['happy_hour_id', 'restaurant_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('happy_hour_restaurant');
    }
};
