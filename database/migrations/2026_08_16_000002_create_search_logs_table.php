<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->string('keyword');
            // Either one identifies a searcher; trending counts distinct searchers, so a single
            // person hammering the same term cannot make it trend on their own.
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('guest_id')->nullable();
            // The zoneId header verbatim - a json list, since a customer can stand inside
            // overlapping zones and the term trends in all of them.
            $table->string('zone_id');
            // 0 means the search found nothing, and a term that finds nothing is never trending.
            $table->unsignedInteger('result_count')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['zone_id', 'created_at']);
            $table->index(['keyword', 'zone_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_logs');
    }
};
