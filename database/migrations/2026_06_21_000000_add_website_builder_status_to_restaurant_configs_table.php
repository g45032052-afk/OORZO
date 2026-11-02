<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('restaurant_configs', 'website_builder_status')) {
                // Vendor-side master switch for the storefront Website Builder.
                // The builder is active for a restaurant only when this AND the
                // admin `admin_website_builder_status` business setting are on.
                $table->boolean('website_builder_status')->default(0)->after('restaurant_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_configs', function (Blueprint $table) {
            if (Schema::hasColumn('restaurant_configs', 'website_builder_status')) {
                $table->dropColumn('website_builder_status');
            }
        });
    }
};
