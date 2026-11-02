<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('food', function (Blueprint $table) {
            $table->foreignId('restaurant_category_id')
                ->nullable()
                ->after('restaurant_id')
                ->constrained('restaurant_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('food', function (Blueprint $table) {
            $table->dropConstrainedForeignId('restaurant_category_id');
        });
    }
};
