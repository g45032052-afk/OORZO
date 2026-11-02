<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Orders a customer has swiped away in My Orders.
     *
     * Removing an order takes it off the customer's own list and nothing more:
     * the restaurant, the admin, the reports and the accounts all still need
     * it, so the row is never destroyed. Only a closed order can be hidden —
     * see OrderController::deleteOrder().
     */
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'is_hidden')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_hidden')->default(false)->after('edited');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('orders', 'is_hidden')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('is_hidden');
        });
    }
};
