<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    /**
     * Indexes keyed by table. Each value is a list of columns; a string adds a
     * single-column index, an array adds a composite (left-most column wins).
     *
     * @var array<string, array<int, string|array<int, string>>>
     */
    private array $indexes = [
        'orders' => ['created_at'],
        'food' => ['restaurant_id', 'category_id', 'avg_rating', 'order_count', 'slug'],
        'order_details' => ['food_id', 'order_id', 'item_campaign_id', 'category_id'],
        'order_transactions' => ['order_id', 'vendor_id', 'delivery_man_id'],
        'order_payments' => ['order_id'],
        'carts' => ['user_id', 'item_id'],
        'delivery_men' => ['zone_id', 'restaurant_id', 'vehicle_id'],
        'categories' => ['parent_id', 'slug'],
        'restaurants' => ['vendor_id', 'zone_id', 'slug'],
        'wallet_transactions' => ['user_id', 'delivery_man_id', 'transaction_id'],
        'loyalty_point_transactions' => ['user_id', 'transaction_id'],
        'coupons' => ['restaurant_id', 'customer_id'],
        'expenses' => ['order_id', 'restaurant_id', 'delivery_man_id', 'created_at'],
        'conversations' => ['sender_id', 'receiver_id'],
        'messages' => ['conversation_id'],
        'track_deliverymen' => ['order_id', 'delivery_man_id'],
        'delivery_histories' => ['order_id', 'delivery_man_id'],
        'restaurant_wallets' => ['vendor_id'],
        'delivery_man_wallets' => ['delivery_man_id'],
        'wishlists' => ['user_id', 'food_id', 'restaurant_id'],
        'order_references' => ['order_id'],
        'restaurant_subscriptions' => ['restaurant_id', 'package_id'],
        'withdraw_requests' => ['vendor_id', 'delivery_man_id'],
        'disbursement_details' => ['disbursement_id', 'restaurant_id', 'delivery_man_id'],
        'user_notifications' => ['user_id', 'vendor_id', 'delivery_man_id'],
        'notifications' => ['zone_id'],
        'refunds' => ['order_id', 'user_id'],
        'vendor_employees' => ['vendor_id', 'restaurant_id', 'employee_role_id'],

        // Composite matching the global "translate" scope join (type + id + locale).
        'translations' => [['translationable_type', 'translationable_id', 'locale']],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                $cols = (array) $column;
                $name = $this->indexName($table, $cols);

                if (!$this->columnsExist($table, $cols)) {
                    continue;
                }
                if ($this->indexExists($table, $name) || $this->columnIndexed($table, $cols[0])) {
                    continue;
                }

                Schema::table($table, function (Blueprint $t) use ($cols, $name) {
                    $t->index($cols, $name);
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                $name = $this->indexName($table, (array) $column);

                if ($this->indexExists($table, $name)) {
                    Schema::table($table, function (Blueprint $t) use ($name) {
                        $t->dropIndex($name);
                    });
                }
            }
        }
    }

    /**
     * @param array<int, string> $cols
     */
    private function indexName(string $table, array $cols): string
    {
        $name = $table . '_' . implode('_', $cols) . '_index';

        if (strlen($name) > 64) {
            $name = $table . '_' . substr(md5(implode('_', $cols)), 0, 10) . '_index';
        }

        return $name;
    }

    /**
     * @param array<int, string> $cols
     */
    private function columnsExist(string $table, array $cols): bool
    {
        foreach ($cols as $col) {
            if (!Schema::hasColumn($table, $col)) {
                return false;
            }
        }

        return true;
    }

    private function indexExists(string $table, string $name): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $name)
            ->exists();
    }

    private function columnIndexed(string $table, string $column): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('column_name', $column)
            ->where('seq_in_index', 1)
            ->exists();
    }
};
