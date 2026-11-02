<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A postal code registered under a business zone.
     *
     * Unlike an area, a zip code carries no translatable text — it is a code, so
     * there is no `display_name` and no `translations` rows. Stored as a string
     * so leading zeros and alphanumeric formats (UK, CA) survive.
     */
    public function up(): void
    {
        if (Schema::hasTable('zip_codes')) {
            return;
        }

        Schema::create('zip_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id');
            $table->string('zip_code', 20);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['zone_id', 'status'], 'zip_codes_zone_status_index');
            $table->index('zip_code', 'zip_codes_code_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zip_codes');
    }
};
