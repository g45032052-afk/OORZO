<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delivery_man_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_man_id');
            $table->string('device_id')->unique();
            $table->string('device_name')->nullable();
            $table->string('biometric_token')->nullable();
            $table->boolean('biometric_enabled')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_man_devices');
    }
};
