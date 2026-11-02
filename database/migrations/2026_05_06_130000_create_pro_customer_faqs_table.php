<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pro_customer_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->string('answer');
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_customer_faqs');
    }
};
