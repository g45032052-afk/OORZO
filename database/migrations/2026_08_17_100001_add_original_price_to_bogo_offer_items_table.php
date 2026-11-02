<?php

use App\CentralLogics\Helpers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * What each enrolled item was worth when the bundle was built.
 *
 * price already froze the buy items, but a get row is stored at 0 because it costs nothing
 * inside the bundle - so nothing recorded what the free item was actually worth, and every
 * screen that needed that figure fell back to today's menu. Edit the food afterwards and the
 * same food appeared at two prices in one bundle: its frozen price on the buy side and its
 * new price on the free side.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('bogo_offer_items', 'original_price')) {
            Schema::table('bogo_offer_items', function (Blueprint $table) {
                // Nullable rather than defaulted: null means nobody ever recorded this, which
                // is a different thing from an item that is genuinely worth nothing.
                $table->double('original_price', 24, 3)->nullable()->after('price');
            });
        }

        $this->backfill();
    }

    public function down(): void
    {
        if (Schema::hasColumn('bogo_offer_items', 'original_price')) {
            Schema::table('bogo_offer_items', function (Blueprint $table) {
                $table->dropColumn('original_price');
            });
        }
    }

    /**
     * A buy row already carries the figure. A get row never did, so it is recovered from the
     * best source available, in order: the same food frozen on the buy side of the same
     * bundle - identical selection, so identical worth - and failing that the live menu,
     * which is what the code did at read time anyway. Either way the value stops moving from
     * here on.
     */
    private function backfill(): void
    {
        DB::table('bogo_offer_items')->where('type', 'buy')->whereNull('original_price')
            ->update(['original_price' => DB::raw('price')]);

        $rows = DB::table('bogo_offer_items')->where('type', 'get')->whereNull('original_price')->get();

        if ($rows->isEmpty()) {
            return;
        }

        $addOns = DB::table('add_ons')->pluck('price', 'id');

        foreach ($rows as $row) {
            $twin = DB::table('bogo_offer_items')
                ->where('bogo_offer_restaurant_id', $row->bogo_offer_restaurant_id)
                ->where('type', 'buy')
                ->where('food_id', $row->food_id)
                ->where(fn ($q) => $q->where('variations', $row->variations)->orWhereNull('variations'))
                ->where(fn ($q) => $q->where('add_on_ids', $row->add_on_ids)->orWhereNull('add_on_ids'))
                ->value('price');

            DB::table('bogo_offer_items')->where('id', $row->id)->update([
                'original_price' => $twin !== null ? (float) $twin : $this->menuPrice($row, $addOns),
            ]);
        }
    }

    /**
     * The same sum HandlesBogoEnrollment::resolveUnitPrice() makes - food, plus the chosen
     * variation options, plus the chosen add-ons - against the menu as it stands today.
     */
    private function menuPrice($row, $addOns): float
    {
        $food = DB::table('food')->where('id', $row->food_id)->first();

        if (! $food) {
            return 0.0;
        }

        $price = (float) $food->price;
        $chosen = json_decode($row->variations ?? '[]', true) ?: [];

        if ($chosen) {
            $price += (float) Helpers::variation_price(json_decode($food->variations ?? '[]', true) ?: [], $chosen);
        }

        $ids = json_decode($row->add_on_ids ?? '[]', true) ?: [];
        $qtys = json_decode($row->add_on_qtys ?? '[]', true) ?: [];

        foreach ($ids as $index => $id) {
            $price += (float) ($addOns[$id] ?? 0) * (int) ($qtys[$index] ?? 1);
        }

        return $price;
    }
};
