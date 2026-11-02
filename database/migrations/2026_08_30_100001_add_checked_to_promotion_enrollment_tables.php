<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Whether the restaurant has seen the admin's invitation yet.
 *
 * The vendor panel prompts on an unanswered admin request, and with nothing recording that
 * the vendor had already looked, the prompt came back on every single page load - including
 * the pages they had opened to go and deal with it.
 *
 * Same mechanism the panels already use for a new order: orders.checked is 0 until the order
 * list is opened, and the new-order popup counts the zeroes. This is that column for the two
 * promotion pivots, so a request is announced once and then again only if the admin sends a
 * new one.
 *
 * Existing rows start at 0 on purpose: nobody can say whether those invitations were seen,
 * and announcing an outstanding one once more is the safe way to be wrong.
 */
return new class extends Migration
{
    private array $tables = ['bogo_offer_restaurant', 'happy_hour_restaurant'];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->boolean('checked')->default(0)->after('requested_by');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn('checked');
            });
        }
    }
};
