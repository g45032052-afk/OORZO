<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI chat conversations. Single-module (food) — no module_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_conversations')) {
            return;
        }

        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('guest_id')->nullable()->index();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->string('title')->nullable();
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_conversations');
    }
};
