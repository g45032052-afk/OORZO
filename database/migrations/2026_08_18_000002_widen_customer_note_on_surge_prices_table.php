<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The customer note is a short explanation shown at checkout, not a title, and 100 characters
 * is not enough to say why a fee went up in a language with long words. Widened to the 300 the
 * form and the validator now allow.
 *
 * Widening only - no stored value can be too long for the new size, so this is safe to run on
 * a live table and reversible without truncation risk unless longer notes have been written.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('surge_prices')) {
            return;
        }

        Schema::table('surge_prices', function (Blueprint $table) {
            $table->string('customer_note', 300)->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('surge_prices')) {
            return;
        }

        Schema::table('surge_prices', function (Blueprint $table) {
            $table->string('customer_note', 100)->nullable()->change();
        });
    }
};
