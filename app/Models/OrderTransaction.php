<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ReportFilter;

class OrderTransaction extends Model
{
    use HasFactory , ReportFilter;

    protected $casts = [
        'admin_expense' => 'float',
        'restaurant_expense' => 'float',
        'commission_percentage' => 'float',
        'discount_amount_by_restaurant' => 'float',
        'additional_charge' => 'float',
        'pro_discount' => 'float',
        'pro_delivery_discount' => 'float',
        //restaurant business model
        'is_subscribed'=>'integer',
        //subscription order
        'is_subscription'=>'boolean',
    ];

    protected $fillable = array('delivery_man_id');

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function delivery_man()
    {
        return $this->belongsTo(DeliveryMan::class, 'delivery_man_id');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class,'vendor_id','vendor_id');
    }

    public function scopeNotRefunded($query)
    {
        $table = $this->getTable();
        return $query->where(function($query) use ($table) {
            $query->whereNotIn($table . '.status', ['refunded_with_delivery_charge', 'refunded_without_delivery_charge'])->orWhereNull($table . '.status');
        });
    }

    /**
     * The food amount the transaction report has always shown: the order less tax, delivery,
     * tips and the separate charges, with every discount added back.
     *
     * Named for what it is rather than for what it is used for. The report also drives its
     * Admin Commission column from this, which is only the amount actually charged when no
     * discount applies - on a discounted order commission is taken on the discounted total,
     * so the column reads high. That predates BOGO and is left alone here; a bundle is
     * unaffected either way, since a bundle carries no item discount.
     */
    public function getReportBaseItemAmountAttribute(): float
    {
        $order = $this->order;

        return (float) ($order['order_amount'] - $this->additional_charge - $order['dm_tips']
            - $order['delivery_charge'] - $this['tax'] - $order['extra_packaging_amount']
            + $order['coupon_discount_amount'] + $order['restaurant_discount_amount']
            + $order['ref_bonus_amount'] + (float) ($this->pro_discount ?? 0));
    }

    /**
     * Total Item Amount: what left the kitchen, priced as if nothing had been given away.
     *
     * A BOGO free item is charged at zero, so without its recorded worth the column showed a
     * bundle order as though only the paid half had ever existed - and the discount columns
     * beside it showed nothing at all, which read as "no promotion was applied here".
     *
     * Adding it back here and in the two discount columns keeps the row's own arithmetic
     * true - item amount less discounted amount is still what the customer paid for food -
     * without moving any money: commission and the restaurant's earning are worked out from
     * report_base_item_amount, which deliberately does not include the give-away.
     */
    public function getReportItemAmountAttribute(): float
    {
        return $this->report_base_item_amount + (float) ($this->order?->bogo_discount_amount ?? 0);
    }

    /** Item Discount: the restaurant's item-level discount plus any BOGO item given away. */
    public function getReportItemDiscountAttribute(): float
    {
        return (float) ($this->order?->restaurant_discount_amount ?? 0)
            + (float) ($this->order?->bogo_discount_amount ?? 0);
    }

    /** Discounted Amount: every discount on the order, the BOGO give-away included. */
    public function getReportDiscountedAmountAttribute(): float
    {
        $order = $this->order;

        return (float) ($order['coupon_discount_amount'] + $order['restaurant_discount_amount']
            + $order['ref_bonus_amount'] + (float) ($this->pro_discount ?? 0)
            + (float) ($order->bogo_discount_amount ?? 0));
    }
}
