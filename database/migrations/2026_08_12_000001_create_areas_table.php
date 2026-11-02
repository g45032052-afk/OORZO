<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A named area sitting under a business zone.
     *
     * `name` is what the admin recognises in the list; `display_name` is what a
     * customer sees. Both are translatable, so these columns hold the default
     * locale only and the rest live in `translations`.
     */
    public function up(): void
    {
        if (Schema::hasTable('areas')) {
            return;
        }

        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id');
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['zone_id', 'status'], 'areas_zone_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
