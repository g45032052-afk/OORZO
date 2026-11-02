<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            // Admin curated: the restaurants the admin wants shown in the Featured rail.
            // Indexed because the customer API filters on it on every home load.
            $table->boolean('featured')->default(0)->index();
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('featured');
        });
    }
};
