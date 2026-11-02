<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Scopes\ZoneScope;
use App\Traits\MasksSensitiveAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\ReportFilter;
use Modules\TaxModule\Entities\OrderTax;

class Order extends Model
{
    use ReportFilter,HasFactory ,MasksSensitiveAttributes;
    protected $guarded = ['id'];
    protected $casts = [
        'order_amount' => 'float',
        'coupon_discount_amount' => 'float',
        'total_tax_amount' => 'float',
        'restaurant_discount_amount' => 'float',
        'bogo_discount_amount' => 'float',
        'delivery_address_id' => 'integer',
        'delivery_man_id' => 'integer',
        'delivery_charge' => 'float',
        'original_delivery_charge'=>'float',
        'user_id' => 'integer',
        'scheduled' => 'integer',
        'restaurant_id' => 'integer',
        'details_count' => 'integer',
        'processing_time' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'dm_tips'=>'float',
        'vehicle_id' => 'integer',
        'quantity' => 'integer',
        'distance'=>'float',
        // Seconds, as the map API and the app speak it. See EtaLogic.
        'delivery_duration'=>'integer',
        // The ETA inputs this order was quoted against, frozen at placement. Null on orders
        // placed before the freeze shipped; EtaLogic falls back to the live configuration there.
        'eta_snapshot' => 'array',
        'subscription_id'=>'integer',
        'cutlery'=>'boolean',
        'is_guest'=>'boolean',
        'is_hidden'=>'boolean',
        'additional_charge' => 'float',
        'ref_bonus_amount' => 'float',
        'extra_packaging_amount' => 'float',
        'bring_change_amount'=>'integer',
        'is_pos'=>'boolean',
        'delivery_type_charge' => 'float',
    ];
    protected $appends = ['order_proof_full_url', 'restaurant_discount_type', 'distance_label'];

    public function getOrderProofFullUrlAttribute(){
        $images = [];
        $value = is_array($this->order_proof)
            ? $this->order_proof
            : ($this->order_proof && is_string($this->order_proof) && $this->isValidJson($this->order_proof)
                ? json_decode($this->order_proof, true)
                : []);
        if ($value){
            foreach ($value as $item){
                $item = is_array($item)?$item:(is_object($item) && get_class($item) == 'stdClass' ? json_decode(json_encode($item), true):['img' => $item, 'storage' => 'public']);
                $images[] = Helpers::get_full_url('order',$item['img'],$item['storage']);
            }
        }

        return $images;
    }

    private function isValidJson($string)
    {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    public function cashback_history()
    {
        return $this->hasOne(CashBackHistory::class, 'order_id');
    }
    public function orderProDiscount()
    {
        return $this->hasOne(OrderProDiscount::class, 'order_id');
    }

    public function OrderReference()
    {
        return $this->hasOne(OrderReference::class, 'order_id');
    }

    public function getDeliveryAddressAttribute($value)
    {
        if ($this->shouldMask()) {
            if (is_array($value)) {
                $address = $value;
            } elseif ($value instanceof \stdClass) {
                $address = (array) $value;
            } elseif (is_string($value)) {
                $address = json_decode($value, true);
            } else {
                $address = [];
            }

            if (!empty($address['contact_person_email'])) {
                $address['contact_person_email'] = $this->maskEmail($address['contact_person_email']);
            }

            if (!empty($address['contact_person_number'])) {
                $address['contact_person_number'] = $this->maskPhone($address['contact_person_number']);
            }
            return json_encode($address);
        }
        return $value;
    }


    public function guest()
    {
        return $this->belongsTo(Guest::class, 'user_id','id');
    }

    public function payments()
    {
        return $this->hasMany(OrderPayment::class);
    }
    public function offline_payments(){
        return $this->belongsTo(OfflinePayments::class,'id','order_id' );
    }

    public function logs()
    {
        return $this->hasMany(Log::class,'model_id')->where('model','Order');
    }
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function subscription_logs()
    {
        return $this->hasMany(SubscriptionLog::class);
    }

    /**
     * The food reviews left against this order.
     *
     * An order counts as reviewed once it holds a review for every item, which
     * is why this is counted rather than merely tested for existence.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function subscription_log()
    {
        return $this->hasOne(SubscriptionLog::class)->where(function($q){
            $q->whereDate('schedule_at', now()->format('Y-m-d'))->orWhereNotIn('order_status',['delivered','failed','canceled', 'refund_requested','refund_request_canceled', 'refunded']);
        });
        // ->whereIn('order_status', ['pending', 'accepted','confirmed','processing','handover','picked_up']);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class,'subscription_id');
    }

    public function setDeliveryChargeAttribute($value)
    {
        $this->attributes['delivery_charge'] = round($value, 3);
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function refund()
    {
        return $this->hasOne(Refund::class, 'order_id');
    }

    public function delivery_man()
    {
        return $this->belongsTo(DeliveryMan::class, 'delivery_man_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }

    /**
     * Which store wide promotion produced restaurant_discount_amount on this order.
     *
     * The amount alone cannot say: a happy hour and the admin's standing restaurant discount
     * both land in the same column, and they are different promotions with different owners -
     * a happy hour is the restaurant's own and it bears the whole cost, the standing discount
     * is the admin's. happy_hour_id is what separates them, and this saves every client from
     * having to know that.
     *
     *   happy_hour           a live window produced it
     *   restaurant_discount  the admin's standing discount produced it
     *   null                 the order carries no store wide discount at all
     */
    /**
     * The order's distance ready to print, in whatever unit the business is configured for.
     *
     * `distance` itself stays untouched and stays kilometres - it is the record of how far the
     * order travelled, and switching the unit must not change it. This is the same number said
     * in the configured unit, so a client can render it without knowing the rule.
     *
     * Null when there is no distance to speak of, so a take-away order shows nothing rather
     * than "0 km".
     */
    public function getDistanceLabelAttribute(): ?string
    {
        $distance = (float) ($this->attributes['distance'] ?? 0);

        return $distance > 0 ? Helpers::formatDistance($distance) : null;
    }

    public function getRestaurantDiscountTypeAttribute(): ?string
    {
        if ((float) ($this->attributes['restaurant_discount_amount'] ?? 0) <= 0) {
            return null;
        }

        return ! empty($this->attributes['happy_hour_id']) ? 'happy_hour' : 'restaurant_discount';
    }

    /**
     * Set only when the restaurant discount on this order came from a live happy hour.
     * Null covers both no discount and the admin's standing restaurant discount.
     */
    public function happyHour()
    {
        return $this->belongsTo(HappyHour::class, 'happy_hour_id');
    }

    public function delivery_history()
    {
        return $this->hasMany(DeliveryHistory::class, 'order_id');
    }

    public function dm_last_location()
    {
        // return $this->hasOne(DeliveryHistory::class, 'order_id')->latest();
        return $this->delivery_man->last_location();
    }

    public function transaction()
    {
        return $this->hasOne(OrderTransaction::class);
    }

    public function scopeAccepteByDeliveryman($query)
    {
        return $query->where('order_status', 'accepted');
    }

    public function scopePreparing($query)
    {
        return $query->whereIn('order_status', ['confirmed','processing','handover']);
    }


    /** Every value `order_status` may hold. */
    public const STATUSES = [
        'pending', 'accepted', 'confirmed', 'processing', 'handover', 'picked_up',
        'delivered', 'canceled', 'refund_requested', 'refund_request_canceled', 'refunded', 'failed',
    ];

    /** Still on its way — what the customer's Running tab lists. */
    public const ACTIVE_STATUSES = ['pending', 'accepted', 'confirmed', 'processing', 'handover', 'picked_up'];

    /** Closed, one way or another — what the History tab lists. */
    public const TERMINAL_STATUSES = [
        'delivered', 'canceled', 'refund_requested', 'refund_request_canceled', 'refunded', 'failed',
    ];

    /**
     * The statuses where tracking an order says something.
     *
     * Before the restaurant takes the order, and once it has been delivered or
     * closed, there is no journey to follow.
     */
    public const TRACKABLE_STATUSES = ['accepted', 'confirmed', 'processing', 'handover', 'picked_up'];

    //check from here
    public function scopeOngoing($query)
    {
        return $query->whereIn('order_status', self::TRACKABLE_STATUSES);
    }

    public function scopeFoodOnTheWay($query)
    {
        return $query->where('order_status','picked_up');
    }

    public function scopePending($query)
    {
        return $query->where('order_status','pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('order_status','failed');
    }

    public function scopeCanceled($query)
    {
        return $query->where('order_status','canceled');
    }

    public function scopeDelivered($query)
    {
        return $query->where('order_status','delivered');
    }

    public function scopeRefunded($query)
    {
        return $query->where('order_status','refunded');
    }

    public function scopeRefund_requested($query)
    {
        return $query->where('order_status','refund_requested');
    }

    public function scopeRefund_request_canceled($query)
    {
        return $query->where('order_status','refund_request_canceled');
    }


    public function scopeSearchingForDeliveryman($query)
    {
        return $query->whereNull('delivery_man_id')->where('order_type', '=' , 'delivery')->whereNotIn('order_status',['delivered','failed','canceled', 'refund_requested','refund_request_canceled', 'refunded']);
    }

    public function scopeDelivery($query)
    {
        return $query->where('order_type', '=' , 'delivery');
    }

    public function scopeScheduled($query)
    {
        return $query->whereRaw('created_at <> schedule_at')->where('scheduled', '1');
    }


    public function scopePaymentMethod($query, $method)
    {
        $methods = array_unique((array) $method);

        if (empty($methods)) {
            return $query;
        }
        $nonDigital = ['cash_on_delivery', 'offline_payment', 'partial_payment', 'wallet'];
        $wantsDigital = in_array('digital_payment', $methods);

        if ($wantsDigital) {
            $query->whereNotIn('payment_method', $nonDigital);
            $methods = array_diff($methods, ['digital_payment']);

            if (empty($methods)) {
                return $query;
            }
            $query->where(function ($q) use ($methods) {
                $q->whereIn('payment_method', $methods);
            });
        } else {
            $query->whereIn('payment_method', $methods);
        }

        return $query;
    }

    public function scopeOrderScheduledIn($query, $interval)
    {
        return $query->where(function($query)use($interval){
            $query->whereRaw('created_at <> schedule_at')->where(function($q) use ($interval) {
            $q->whereBetween('schedule_at', [Carbon::now()->toDateTimeString(),Carbon::now()->addMinutes($interval)->toDateTimeString()]);
            })->orWhere('schedule_at','<',Carbon::now()->toDateTimeString());
        })->orWhereRaw('created_at = schedule_at');

    }

    public function scopePos($query)
    {
        return $query->where('order_type', '=' , 'pos');
    }

    public function scopeNotpos($query)
    {
        return $query->where('order_type', '<>' , 'pos');
    }

    /**
     * Leaves out the orders the customer has swiped away in My Orders.
     *
     * Only their own list is affected — every other reader of an order, from
     * the restaurant to the accounts, still sees it.
     */
    public function scopeNotHiddenForCustomer($query)
    {
        return $query->where($this->getTable().'.is_hidden', 0);
    }

    public function getCreatedAtAttribute($value)
    {
        return date('Y-m-d H:i:s',strtotime($value));
    }

    protected static function booted()
    {
        static::addGlobalScope(new ZoneScope);

            // Freeze the ETA inputs onto the order as it is written, so an admin later editing
            // the zone's ETA setup, its Additional Charge setup or the restaurant's delivery time
            // cannot move the estimate of an order already in flight. Done here rather than in
            // each placement path so a future one cannot forget it, and only on insert - an edit
            // to an existing order must not re-quote it against today's configuration.
            static::creating(function ($order) {
                if ($order->eta_snapshot !== null || $order->order_type !== 'delivery') {
                    return;
                }

                // Never block a sale over an estimate.
                try {
                    $order->eta_snapshot = \App\CentralLogics\EtaLogic::snapshotFor($order);
                } catch (\Throwable $e) {
                    info(['eta_snapshot_failed' => $e->getMessage()]);
                }
            });

            static::created(function () {
                Helpers::deleteCacheData('order_stats_summary');
                Helpers::deleteCacheData('order_scheduled_stats');
            });
            static::updated(function(){
                Helpers::deleteCacheData('order_stats_summary');
                Helpers::deleteCacheData('order_scheduled_stats');
        });
    }

    public function scopeNotDigitalOrder($query)
    {
        return $query->where(function ($q){
            $q->whereNotIn('payment_method', ['digital_payment','offline_payment'])->orwhereNot('order_status' , 'pending');
        });

    }

    public function scopeNotRefunded($query)
    {
        return $query->where(function($query){
            $query->whereNotIn('order_status', ['refunded']);
        });
    }

    public function scopeRestaurantOrder($query)
    {
        return $query->whereIn('order_type',['dine_in','take_away','delivery']);
    }

    public function scopeHasSubscriptionToday($query)
    {
        return $query->where(function($query){
            $query->where(function($query){
                $query->whereHas('subscription', function($query){
                    $query->where('status','active')->whereDate('start_at', '<=', now()->format('Y-m-d'))->whereDate('end_at','>=', now()->format('Y-m-d'))

                    ->whereHas('schedules', function($query){
                        $query->where(function($query){
                            $query->where('type', 'weekly')->where('day', (int)now()->format('w'));
                        })->orWhere(function($query){
                            $query->where('type', 'monthly')->where('day', (int)now()->format('d'));
                        })->orWhere('type', 'daily');
                    })->whereDoesntHave('pause', function($query){
                        $query->whereDate('from', '<=', now()->format('Y-m-d'))->whereDate('to','>=', now()->format('Y-m-d'));
                    });
                })
                ->whereDoesntHave('subscription_logs', function($query){
                    $query->whereDate('schedule_at', now()->format('Y-m-d'))->orWhereIn('order_status',['delivered','failed','canceled', 'refund_requested','refund_request_canceled', 'refunded']);
                });
            })->orWhereNull('subscription_id');
        });
    }

    public function scopeHasSubscriptionInStatus($query, array $status_list)
    {
        return $query->orWhereHas('subscription_logs', function($query)use($status_list){
            $query->whereIn('order_status',$status_list);
        });
    }

    public function scopeHasSubscriptionTodayGet($query)
    {
        return $query->where(function($query){
            $query->where(function($query){
                $query->whereHas('subscription', function($query){
                    $query->where('status','active')->whereDate('start_at', '<=', now()->format('Y-m-d'))->whereDate('end_at','>=', now()->format('Y-m-d'))
                    ->whereHas('schedules', function($query){
                        $query->where(function($query){
                            $query->where('type', 'weekly')->where('day', (int)now()->format('w'));
                        })->orWhere(function($query){
                            $query->where('type', 'monthly')->where('day', (int)now()->format('d'));
                        })->orWhere('type', 'daily');
                    })->whereDoesntHave('pause', function($query){
                        $query->whereDate('from', '<=', now()->format('Y-m-d'))->whereDate('to','>=', now()->format('Y-m-d'));
                    });
                });
            });
        });
    }

    public static function scopeApplyDateFilterSchedule($query, $filter, $from = null, $to = null)
    {
        return $query->when(isset($from) && isset($to)  && $filter == 'custom', function ($query) use ($from, $to) {
            return $query->whereBetween('schedule_at', [$from . " 00:00:00", $to . " 23:59:59"]);
        })
        ->when($filter == 'this_year', function ($query) {
            return $query->whereYear('schedule_at', now()->format('Y'));
        })
        ->when($filter == 'this_month', function ($query) {
            return $query->whereMonth('schedule_at', now()->format('m'))->whereYear('schedule_at', now()->format('Y'));
        })
        ->when($filter == 'previous_year', function ($query) {
            return $query->whereYear('schedule_at', date('Y') - 1);
        })
        ->when($filter == 'this_week', function ($query) {
            return $query->whereBetween('schedule_at', [now()->startOfWeek()->format('Y-m-d H:i:s'), now()->endOfWeek()->format('Y-m-d H:i:s')]);
        });
        return $query;
    }

    public function orderTaxes()
    {
        return $this->morphMany(OrderTax::class, 'order');
    }
    public function orderEditLogs()
    {
        return $this->hasMany(OrderEditLog::class)->latest();
    }
}
