<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BogoOfferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bogo_offer_restaurant_id',
        'food_id',
        'type',
        'quantity',
        'food_name',
        'food_image',
        'price',
        'original_price',
        'variations',
        'variation_options',
        'add_on_ids',
        'add_on_qtys',
    ];

    protected $casts = [
        'bogo_offer_restaurant_id' => 'integer',
        'food_id' => 'integer',
        'quantity' => 'integer',
        // What the line costs inside the bundle, so a get row is 0 - and what the item was
        // worth when the bundle was built, which is the figure the offer screens show and the
        // payout books. Null on original_price means nobody ever recorded it.
        'price' => 'float',
        'original_price' => 'float',
        'variations' => 'array',
        'variation_options' => 'array',
        'add_on_ids' => 'array',
        'add_on_qtys' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function enrollment()
    {
        return $this->belongsTo(BogoOfferRestaurant::class, 'bogo_offer_restaurant_id');
    }

    /**
     * Live reference, kept only so stock, availability and status resolve against the real
     * food. Display and pricing always use this row's frozen copies.
     */
    public function food()
    {
        return $this->belongsTo(Food::class, 'food_id');
    }

    /**
     * The variation_options rows behind this line's frozen selection.
     *
     * The picker records a selection the way the cart records one - by group name and option
     * label, `[{"name":"Size","values":{"label":["Large"]}}]` - because that is what the food's
     * variations JSON carries. Stock, though, lives on the variation_options table and is
     * addressed by id, so every stock call needs the ids and nothing was ever resolving them:
     * the column sat null on every enrolled item, which meant option stock was neither checked
     * before showing a bundle nor consumed when one was ordered.
     *
     * Resolved on read rather than backfilled, so rows written before this - and any written by
     * something that skips the trait - behave the same as new ones. $variations is whatever the
     * caller holds: the cast array, or the raw JSON string.
     */
    public static function resolveVariationOptionIds($foodId, $variations, $stored = null): array
    {
        // A selection already carrying its ids is taken at its word.
        $stored = is_string($stored) ? json_decode($stored, true) : $stored;

        if (! empty($stored)) {
            return array_values(array_filter(array_map('intval', $stored)));
        }

        $variations = is_string($variations) ? json_decode($variations, true) : $variations;

        if (empty($variations) || ! $foodId) {
            return [];
        }

        $groups = Variation::where('food_id', $foodId)->pluck('id', 'name');

        if ($groups->isEmpty()) {
            return [];
        }

        $wanted = [];

        foreach ($variations as $variation) {
            $variationId = $groups[$variation['name'] ?? null] ?? null;

            if (! $variationId) {
                continue;
            }

            foreach ((array) data_get($variation, 'values.label', []) as $label) {
                $wanted[] = [$variationId, $label];
            }
        }

        if (! $wanted) {
            return [];
        }

        return VariationOption::where('food_id', $foodId)
            ->where(function ($query) use ($wanted) {
                foreach ($wanted as [$variationId, $label]) {
                    $query->orWhere(fn ($q) => $q->where('variation_id', $variationId)->where('option_name', $label));
                }
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
