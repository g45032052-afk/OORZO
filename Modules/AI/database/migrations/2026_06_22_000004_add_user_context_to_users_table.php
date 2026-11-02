<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Free-text AI persona / context for a customer (fed by the chat assistant's
 * UpdateUserContext tool and used to enrich personalization keyword prompts).
 * Idempotent: guarded by hasColumn, no `after` (column order is irrelevant).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'user_context')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->text('user_context')->nullable();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'user_context')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('user_context');
            });
        }
    }
};
