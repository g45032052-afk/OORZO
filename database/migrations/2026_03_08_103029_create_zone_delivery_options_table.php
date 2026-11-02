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
        Schema::create('zone_delivery_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id');
            $table->enum('delivery_type',['standard','express','slightly_delay'])->default('standard');
            $table->decimal('extra_charge',10,4)->nullable();
            $table->decimal('reduce_charge',10,4)->nullable();
            $table->integer('add_delivery_time')->comment('in minutes')->nullable();
            $table->integer('reduce_delivery_time')->comment('in minutes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zone_delivery_options');
    }
};
