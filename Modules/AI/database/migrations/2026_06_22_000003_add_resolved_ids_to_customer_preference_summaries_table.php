<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI-keyword → concrete-id resolution caches on the summary row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_preference_summaries', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_preference_summaries', 'keyword_item_ids')) {
                $table->json('keyword_item_ids')->nullable()->after('ai_keywords');
            }
            if (!Schema::hasColumn('customer_preference_summaries', 'keyword_category_ids')) {
                $table->json('keyword_category_ids')->nullable()->after('keyword_item_ids');
            }
            if (!Schema::hasColumn('customer_preference_summaries', 'keyword_store_ids')) {
                $table->json('keyword_store_ids')->nullable()->after('keyword_category_ids');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_preference_summaries', function (Blueprint $table) {
            $table->dropColumn(['keyword_item_ids', 'keyword_category_ids', 'keyword_store_ids']);
        });
    }
};
