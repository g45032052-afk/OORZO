<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use App\Models\Vendor;
use App\Scopes\ZoneScope;
use App\Traits\MasksSensitiveAttributes;
use Illuminate\Support\Facades\DB;
use App\Traits\GeneratesSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\ReportFilter;
use Illuminate\Support\Facades\Config;

class Restaurant extends Model
{
    use HasFactory, ReportFilter, MasksSensitiveAttributes, GeneratesSlug;
    protected $fillable = ['food_section','status'];

    protected $with = ['restaurant_config','translations','storage'];

    protected $casts = [
        'minimum_order' => 'float',
        'comission' => 'float',
        'tax' => 'float',
        'delivery_charge' => 'float',
        'schedule_order'=>'boolean',
        'free_delivery'=>'boolean',
        'vendor_id'=>'integer',
        'status'=>'integer',
        'delivery'=>'boolean',
        'take_away'=>'boolean',
        'zone_id'=>'integer',
        'food_section'=>'boolean',
        'reviews_section'=>'boolean',
        'active'=>'boolean',
        'featured'=>'integer',
        'gst_status'=>'boolean',
        'free_delivery_distance_status'=>'boolean',
        'pos_system'=>'boolean',
        'self_delivery_system'=>'integer',
        'open'=>'integer',
        'gst_code'=>'string',
        'free_delivery_distance_value'=>'float',
        'off_day'=>'string',
        'gst'=>'string',
        'free_delivery_distance'=>'string',
        'veg'=>'integer',
        'non_veg'=>'integer',
        'announcement'=>'integer',
        'minimum_shipping_charge'=>'float',
        'per_km_shipping_charge'=>'float',
        'maximum_shipping_charge'=>'float',
        'cuisine_id'=>'integer',
        // 'order_subscription'=>'boolean',
        'order_subscription_active'=>'boolean',
        'opening_time'=>'datetime',
        'closeing_time'=>'datetime',
        'cutlery'=>'boolean',
        'foods_count'=>'integer',
        'reviews_comments_count'=>'integer',
        'package_id'=>'integer',
        'distance' => 'float',
        'meta_data' => 'array',
    ];

    protected $appends = ['gst_status','gst_code','free_delivery_distance_status','free_delivery_distance_value',
        'logo_full_url','cover_photo_full_url','meta_image_full_url','tin_certificate_image_full_url','additional_documents_full_url','verified_seller'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'gst','free_delivery_distance'
    ];

    public function getLogoFullUrlAttribute(){
        $value = $this->logo;
        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] == 'logo') {
                    return Helpers::get_full_url('restaurant',$value,$storage['value']);
                }
            }
        }

        return Helpers::get_full_url('restaurant',$value,'public');
    }
    public function getCoverPhotoFullUrlAttribute(){
        $value = $this->cover_photo;
        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] == 'cover_photo') {
                    return Helpers::get_full_url('restaurant/cover',$value,$storage['value']);
                }
            }
        }

        return Helpers::get_full_url('restaurant/cover',$value,'public');
    }
    public function getMetaImageFullUrlAttribute(){
        $value = $this->meta_image;
        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] == 'meta_image') {
                    return Helpers::get_full_url('restaurant',$value,$storage['value']);
                }
            }
        }

        return Helpers::get_full_url('restaurant',$value,'public');
    }
    public function getTinCertificateImageFullUrlAttribute(){
        $value = $this->tin_certificate_image;
        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] == 'tin_certificate_image') {
                    return Helpers::get_full_url('restaurant',$value,$storage['value']);
                }
            }
        }

        return Helpers::get_full_url('restaurant',$value,'public');
    }

    public function getAdditionalDocumentsFullUrlAttribute()
    {
        $images = [];

        $value = is_array($this->additional_documents)
            ? $this->additional_documents
            : ($this->additional_documents && is_string($this->additional_documents) && $this->isValidJson($this->additional_documents)
                ? json_decode($this->additional_documents, true)
                : []);

        if ($value) {
            foreach ($value as $group => $items) {
                if (is_array($items)) {
                    foreach ($items as $item) {
                        $file = $item['file'] ?? null;
                        $storage = $item['storage'] ?? 'public';

                        if ($file) {
                            $images[] = Helpers::get_full_url('additional_documents', $file, $storage);
                        }
                    }
                }
            }
        }

        return $images;
    }

    public function getVerifiedSellerAttribute(): int
    {
        return Helpers::get_verified_seller_status($this, $this->restaurant_config);
    }

    private function isValidJson($string)
    {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }


    public function wallet()
    {
        return $this->hasOne(RestaurantWallet::class,'vendor_id','vendor_id');
    }


    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }


    public function characteristics()
    {
        return $this->belongsToMany(Characteristic::class);
    }

    public function restaurant_config()
    {
        return $this->hasOne(RestaurantConfig::class);
    }


    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }
    public function transaction()
    {
        return $this->hasMany(OrderTransaction::class,'vendor_id','vendor_id');
    }
    public function coupon()
    {
        return $this->hasMany(Coupon::class,'restaurant_id');
    }
    public function notification_setup()
    {
        return $this->hasMany(RestaurantNotificationSetting::class,'restaurant_id');
    }

    public function restaurantCategories()
    {
        return $this->hasMany(RestaurantCategory::class, 'restaurant_id');
    }
    public function coupon_valid()
    {
        return $this->hasMany(Coupon::class,'restaurant_id')->where('status',1)->whereDate('expire_date', '>=', date('Y-m-d'))->whereDate('start_date', '<=', date('Y-m-d'));
    }

    public function restaurant_sub()
    {
        return $this->hasOne(RestaurantSubscription::class)->where('status',1)->latestOfMany();
    }

    public function restaurant_subs()
    {
        return $this->hasMany(RestaurantSubscription::class,'restaurant_id');
    }
    public function restaurant_sub_trans()
    {
        return $this->hasOne(SubscriptionTransaction::class)->latest();
    }
    public function restaurant_sub_update_application()
    {
        return $this->hasOne(RestaurantSubscription::class)->latestOfMany();
    }
    public function restaurant_all_sub_trans()
    {
        return $this->hasMany(SubscriptionTransaction::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function foods()
    {
        return $this->hasMany(Food::class);
    }
    public function foods_for_reorder()
    {
        return $this->foods()->where('status',1)->orderby('avg_rating','desc')->orderby('recommended','desc');
    }

    public function schedules()
    {
        return $this->hasMany(RestaurantSchedule::class)->orderBy('opening_time');
    }

    public function deliverymen()
    {
        return $this->hasMany(DeliveryMan::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class,'restaurant_id');
    }

    public function discount()
    {
        return $this->hasOne(Discount::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class);
    }

    public function bogoOffers()
    {
        return $this->belongsToMany(BogoOffer::class, 'bogo_offer_restaurant')
            ->withPivot(['id', 'status', 'rejection_reason', 'rejected_by', 'requested_by', 'joined_at', 'bundle_price', 'combination_signature'])
            ->withTimestamps();
    }

    public function bogoEnrollments()
    {
        return $this->hasMany(BogoOfferRestaurant::class);
    }

    public function happyHours()
    {
        return $this->belongsToMany(HappyHour::class, 'happy_hour_restaurant')
            ->withPivot(['id', 'status', 'rejection_reason', 'rejected_by', 'requested_by', 'joined_at'])
            ->withTimestamps();
    }

    public function happyHourEnrollments()
    {
        return $this->hasMany(HappyHourRestaurant::class);
    }

    public function itemCampaigns()
    {
        return $this->hasMany(ItemCampaign::class);
    }

    public function reviews()
    {
        return $this->hasManyThrough(Review::class, Food::class);
    }
    public function reviews_comments()
    {
        return $this->reviews()->whereNotNull('comment');
    }

    public function getScheduleOrderAttribute($value)
    {
        return (boolean)(\App\CentralLogics\Helpers::schedule_order()?$value:0);
    }
    public function getRatingAttribute($value)
    {
        $ratings = $value ? json_decode($value, true) : [];
        $rating5 = $ratings?$ratings[5]:0;
        $rating4 = $ratings?$ratings[4]:0;
        $rating3 = $ratings?$ratings[3]:0;
        $rating2 = $ratings?$ratings[2]:0;
        $rating1 = $ratings?$ratings[1]:0;
        return [$rating5, $rating4, $rating3, $rating2, $rating1];
    }

    public function getGstStatusAttribute()
    {
        return (boolean)($this->gst?json_decode($this->gst, true)['status']:0);
    }

    public function getGstCodeAttribute()
    {
        return (string)($this->gst?json_decode($this->gst, true)['code']:'');
    }

    public function getFreeDeliveryDistanceStatusAttribute()
    {
        return (boolean)($this->free_delivery_distance?json_decode($this->free_delivery_distance, true)['status']:0);
    }

    public function getFreeDeliveryDistanceValueAttribute()
    {
        return (string)($this->free_delivery_distance?json_decode($this->free_delivery_distance, true)['value']:'');
    }

    public function scopeDelivery($query)
    {
        $query->where('delivery',1);
    }

    public function scopeTakeaway($query)
    {
        $query->where('take_away',1);
    }

    // public function scopeActive($query)
    // {
    //     if(!\App\CentralLogics\Helpers::commission_check()){
    //         $query = $query->where('restaurant_model','!=','commission');
    //     }
    //     return $query->where('status', 1);
    // }
    public function scopeActive($query): mixed
    {
        $query =  $query->where('status', 1)
        ->where(function($query) {
            $query->where('restaurant_model', 'commission')
                    ->orWhereHas('restaurant_sub', function($query) {
                        $query->where(function($query) {
                            $query->where('max_order', 'unlimited')->orWhere('max_order', '>', 0);
                        });
                    });
            });
        return $query;
    }


    public function getSubSelfDeliveryAttribute(): mixed
    {
        if( $this->restaurant_model == 'subscription' && isset($this->restaurant_sub)){
            return (int)   $this->restaurant_sub?->self_delivery ;
            unset($this->restaurant_sub);
        }
        return $this->self_delivery_system;
    }
    public function getChatPermissionAttribute(): mixed
    {
        if( $this->restaurant_model == 'subscription' && isset($this->restaurant_sub)){
            return (int)   $this->restaurant_sub->chat ;
            unset($this->restaurant_sub);
        }
        return 0;
    }
    public function getReviewPermissionAttribute(): mixed
    {
        if( $this->restaurant_model == 'subscription' && isset($this->restaurant_sub)){
            return (int)   $this->restaurant_sub->review ;
            unset($this->restaurant_sub);
        }
        return $this->reviews_section;
    }
    public function getIsValidSubscriptionAttribute(): mixed
    {
        if( $this->restaurant_model == 'subscription' && isset($this->restaurant_sub)){
            return (int)   1 ;
            unset($this->restaurant_sub);
        }
        return 0;
    }




    public function scopeOpened($query)
    {
        return $query->where('active', 1);
    }

    /**
     * Stores a customer could order from at this moment: not temporarily closed, not on an off
     * day, and inside today's opening hours.
     *
     * The same window withOpen() computes for its `open` flag, asked as a filter rather than a
     * selected column - a selectRaw cannot be used inside whereHas(), where it lands in an
     * EXISTS subquery and the SQL is invalid. Deliberately not combined with active(): whether
     * a store is switched on and whether it is open right now are different questions, and
     * some callers want only the first.
     */
    public function scopeOpenNow($query)
    {
        return $query->Opened()->Weekday()->whereHas('schedules', function ($schedule) {
            $schedule->where('day', now()->dayOfWeek)
                ->where('opening_time', '<', now()->format('H:i:s'))
                ->where('closing_time', '>', now()->format('H:i:s'));
        });
    }

    /**
     * Whether this store offers a given order type.
     *
     * delivery and take_away are columns on the store; dine_in lives on its config row, which
     * is how scopeOrderType already resolves them. `home_delivery` is the older name some
     * clients still send for `delivery`.
     */
    public function servesOrderType(string $orderType): bool
    {
        return match ($orderType) {
            'delivery', 'home_delivery' => (bool) $this->delivery,
            'take_away' => (bool) $this->take_away,
            'dine_in' => (bool) ($this->restaurant_config->dine_in ?? false),
            default => false,
        };
    }

    /**
     * Whether the store offers at least one of these order types.
     *
     * An empty list means the promotion did not restrict itself, so every store qualifies.
     */
    public function servesAnyOrderType(array $orderTypes): bool
    {
        if (! $orderTypes) {
            return true;
        }

        foreach ($orderTypes as $orderType) {
            if ($this->servesOrderType($orderType)) {
                return true;
            }
        }

        return false;
    }

    public function scopeWithOpen($query,$longitude,$latitude)
    {
        $longitude = trim($longitude, "\"'");
        $latitude  = trim($latitude, "\"'");

        $longitude = (float)$longitude;
        $latitude  = (float)$latitude;
        $query->selectRaw('*, IF(((select count(*) from `restaurant_schedule` where `restaurants`.`id` = `restaurant_schedule`.`restaurant_id` and `restaurant_schedule`.`day` = '.now()->dayOfWeek.' and `restaurant_schedule`.`opening_time` < "'.now()->format('H:i:s').'" and `restaurant_schedule`.`closing_time` >"'.now()->format('H:i:s').'") > 0), true, false) as open,ST_Distance_Sphere(point(longitude, latitude),point('.$longitude.', '.$latitude.')) as distance');
    }

    /**
     * withOpen(), plus the fastest promised delivery as a number the database can sort on.
     *
     * delivery_time is free text ("10-52-min", "1-2 hours"), so ordering by it puts "1-2 hours"
     * ahead of "10-52-min". This reads the lower bound, normalises hours to minutes, and parks
     * anything unparseable at 9999 so a restaurant with no delivery time set never leads a
     * quick delivery rail.
     */
    public function scopeWithOpenWithDeliveryTime($query, $longitude, $latitude, bool $with_distance = true)
    {
        $longitude = (float) trim((string) ($longitude ?: 0), "\"'");
        $latitude  = (float) trim((string) ($latitude ?: 0), "\"'");

        // A caller that neither sorts by distance nor shows it can ask for the rest of the
        // scope without the great-circle sum over the whole zone.
        $distance_column = $with_distance
            ? 'ST_Distance_Sphere(point(longitude, latitude),point('.$longitude.', '.$latitude.')) as distance,'
            : '';

        $query->selectRaw('*, IF(((select count(*) from `restaurant_schedule` where `restaurants`.`id` = `restaurant_schedule`.`restaurant_id` and `restaurant_schedule`.`day` = '.now()->dayOfWeek.' and `restaurant_schedule`.`opening_time` < "'.now()->format('H:i:s').'" and `restaurant_schedule`.`closing_time` >"'.now()->format('H:i:s').'") > 0), true, false) as open,
            '.$distance_column.'
            CASE
                WHEN delivery_time IS NULL THEN 9999
                WHEN delivery_time LIKE "%hour%" THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(delivery_time, "-", 1), " ", 1) AS UNSIGNED) * 60
                WHEN delivery_time LIKE "%min%" THEN CAST(SUBSTRING_INDEX(delivery_time, "-", 1) AS UNSIGNED)
                ELSE 9999
            END AS min_delivery_time');
    }

    /** Admin curated. Drives the Featured rail and the ?featured=1 listing filter. */
    public function scopeFeatured($query)
    {
        return $query->where('featured', '=', 1);
    }

    public function scopeWeekday($query)
    {
        return $query->where('off_day', 'not like', "%".now()->dayOfWeek."%");
    }


    public function scopeType($query, $type)
    {
        if($type == 'veg')
        {
            return $query->where('veg', true);
        }
        else if($type == 'non_veg')
        {
            return $query->where('non_veg', true);
        }
        else if($type == 'home_delivery')
        {
            return $query->where('delivery', true);
        }
        else if($type == 'take_away')
        {
            return $query->where('take_away', true);
        }
        else if($type == 'dine_in')
        {
            return $query->whereHas('restaurant_config', function ($query) {
                $query->where('dine_in',true);
            });
        }

        return $query;

    }
    public function scopeRestaurantModel($query, $type)
    {
        if($type == 'commission')
        {
            return $query->where('restaurant_model', 'commission');
        }
        else if($type == 'subscribed')
        {
            return $query->where('restaurant_model', 'subscription');
        }
        else if($type == 'unsubscribed')
        {
            return $query->where('restaurant_model', 'unsubscribed');
        }
        else if($type == 'none')
        {
            return $query->where('restaurant_model', 'none');
        }
        return $query;
    }

    public function scopeCuisine($query, $cuisine)
    {
        if ($cuisine == 'all') {
            return $query;
        }

        return $query->whereHas('cuisine', function ($query) use ($cuisine) {

            if (is_array($cuisine)) {
                $query->whereIn('cuisine_restaurant.cuisine_id', $cuisine)
                    ->orWhereIn('cuisines.slug', $cuisine);
            } else {
                $query->where(function ($q) use ($cuisine) {
                    $q->where('cuisine_restaurant.cuisine_id', $cuisine)
                    ->orWhere('cuisines.slug', $cuisine);
                });
            }

        });
    }

    public function scopeMultiCuisine($query, $cuisine_id)
    {
        // Callers pass either the raw json string or an already decoded list - search_restaurants
        // hands over $request->query('cuisine', []), which is an array. Decoding that throws a
        // TypeError on PHP 8, which is why restaurants/search answered 500 whenever no cuisine
        // filter was sent.
        $cuisine_id = is_string($cuisine_id) ? json_decode($cuisine_id) : $cuisine_id;
        if(is_array($cuisine_id) && count($cuisine_id)>0){
            return $query->whereHas('cuisine', function ($query) use ($cuisine_id){
                $query->whereIn('cuisine_restaurant.cuisine_id', $cuisine_id)->orWhereIn('cuisines.slug', $cuisine_id);
            });
        }
            return $query;
    }

    public function storage()
    {
        return $this->morphMany(Storage::class, 'data');
    }

    protected static function boot()
    {
        parent::boot();
        static::created(function ($restaurant) {
            $restaurant->slug = $restaurant->generateSlug($restaurant->name);
            $restaurant->save();
        });

        // Nothing references bogo_offers or its pivot by foreign key, so a deleted restaurant
        // used to leave its enrolments and their frozen items behind: the offer went on
        // counting a store that no longer existed, and its detail page listed a blank row.
        // Placed on the model rather than in the controller that deletes it, because the
        // enrolment is meaningless without the restaurant however the restaurant goes.
        static::deleted(function ($restaurant) {
            $enrollmentIds = DB::table('bogo_offer_restaurant')
                ->where('restaurant_id', $restaurant->id)->pluck('id');

            if ($enrollmentIds->isNotEmpty()) {
                DB::table('bogo_offer_items')->whereIn('bogo_offer_restaurant_id', $enrollmentIds)->delete();
                DB::table('bogo_offer_restaurant')->whereIn('id', $enrollmentIds)->delete();
            }

            DB::table('happy_hour_restaurant')->where('restaurant_id', $restaurant->id)->delete();
        });

        static::saved(function ($model) {
            Helpers::deleteCacheData('banners_');
            Helpers::deleteCacheData('advertisements_');
            if($model->isDirty('logo')){
                $value = Helpers::getDisk();

                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'logo',
                ], [
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            if($model->isDirty('cover_photo')){
                $value = Helpers::getDisk();

                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'cover_photo',
                ], [
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            if($model->isDirty('meta_image')){
                $value = Helpers::getDisk();

                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'meta_image',
                ], [
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

    }

    public function cuisine()
    {
        return $this->belongsToMany(Cuisine::class);
    }
    public function disbursement_method()
    {
        return $this->hasOne(DisbursementWithdrawalMethod::class)->where('is_default',1);
    }
    public function users()
    {
        return $this->morphToMany(User::class ,'visitor_log');
    }

    public function schedule_today()
    {
        return $this->hasMany(RestaurantSchedule::class)->orderBy('opening_time')->where('day',now()->dayOfWeek);
    }


    public function getNameAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'name') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }
    public function getAddressAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'address') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }



    protected static function booted()
    {
        // static::addGlobalScope('storage', function ($builder) {
        //     $builder->with('storage');
        // });
        static::addGlobalScope(new ZoneScope);
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function($query){
                return $query->where('locale', app()->getLocale());
            }]);
        });

        static::retrieved(function () {
            $current_date = date('Y-m-d');
            // $check_daily_subscription_validity_check= Helpers::get_business_settings('check_daily_subscription_validity_check');
            $check_daily_subscription_validity_check = BusinessSetting::where('key','check_daily_subscription_validity_check')->first()?->value;
            if(!$check_daily_subscription_validity_check){
                Helpers::insert_business_settings_key('check_daily_subscription_validity_check', $current_date);
                $check_daily_subscription_validity_check= $current_date;
            }

            if($check_daily_subscription_validity_check != $current_date){
                $restaurantIds = RestaurantSubscription::where('status', 1)
                    ->whereDate('expiry_date', '<=', $current_date)
                    ->pluck('restaurant_id');

                Restaurant::whereIn('id', $restaurantIds)
                    ->update([
                        'status' => 0,
                        'pos_system' => 1,
                        'self_delivery_system' => 1,
                        'reviews_section' => 1,
                        'free_delivery' => 0,
                        'restaurant_model' => 'unsubscribed',
                    ]);
                RestaurantSubscription::where('status',1)->whereDate('expiry_date', '<=', $current_date)->update([
                    'status' => 0
                ]);

                Helpers::businessUpdateOrInsert(['key' => 'check_daily_subscription_validity_check'], [
                    'value' => $current_date,
                ]);

            }
        });


    }

    /**
     * The restaurant's average review score, exposed once as `avg_rating_all`.
     *
     * The top_rated chip, the high_rated sort and the rating floor all need this column and
     * each used to attach its own copy of the subselect. Asking for two of them in one
     * request - which the search screen does - made MySQL reject the whole query with
     * "Duplicate column name 'avg_rating_all'", a 500 on the listing. Adding it only when it
     * is not already selected keeps every caller reading the same alias.
     */
    /**
     * The keyword match every restaurant listing should use.
     *
     * search_restaurants() grew this inline and the home rails never got it, so "burger"
     * returned the whole rail unfiltered. One implementation here keeps the rails answering
     * the same way the search screen does: the restaurant's own name, its translated name,
     * its tags, its cuisines, and the names of the food it sells.
     */
    public function scopeSearchKeyword($query, $name)
    {
        $name = trim((string) $name);

        if ($name === '' || strtolower($name) === 'null') {
            return $query;
        }

        $keys = array_values(array_filter(explode(' ', $name)));

        return $query->where(function ($q) use ($keys) {
            foreach ($keys as $key) {
                $q->orWhere('restaurants.name', 'like', "%{$key}%");
            }
            $q->orWhereHas('translations', function ($t) use ($keys) {
                $t->where('translationable_type', 'App\\Models\\Restaurant')->where('key', 'name')
                    ->where(function ($tt) use ($keys) {
                        foreach ($keys as $key) {
                            $tt->orWhere('value', 'like', "%{$key}%");
                        }
                    });
            });
            $q->orWhereHas('tags', function ($t) use ($keys) {
                $t->where(function ($tt) use ($keys) {
                    foreach ($keys as $key) {
                        $tt->orWhere('tag', 'like', "%{$key}%");
                    }
                });
            });
            $q->orWhereHas('cuisine', function ($c) use ($keys) {
                $c->where(function ($cc) use ($keys) {
                    foreach ($keys as $key) {
                        $cc->orWhere('cuisines.name', 'like', "%{$key}%");
                    }
                });
            });
            $q->orWhereHas('foods', function ($f) use ($keys) {
                $f->where(function ($ff) use ($keys) {
                    foreach ($keys as $key) {
                        $ff->orWhere('food.name', 'like', "%{$key}%");
                    }
                });
            });
        });
    }

    /**
     * Keep the table's own columns when a scope adds a subselect - selectSub() on a query that
     * has selected nothing yet replaces the implicit `*` instead of adding to it.
     */
    public function scopeEnsureBaseSelect($query)
    {
        if (empty($query->getQuery()->columns)) {
            $query->select($query->getModel()->getTable().'.*');
        }

        return $query;
    }

    public function scopeSelectsAlias($query, string $alias): bool
    {
        $grammar = $query->getQuery()->getGrammar();

        foreach ($query->getQuery()->columns ?? [] as $column) {
            $sql = $column instanceof \Illuminate\Contracts\Database\Query\Expression
                ? $column->getValue($grammar)
                : (string) $column;

            if (str_contains($sql, $alias)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The great-circle distance as a sortable `distance` column.
     *
     * withOpen() already selects it, so the distance sort adding its own copy produced a
     * second `distance` alias. It stayed hidden while pagination counted with a stripped
     * select list, and became "Duplicate column name 'distance'" - a 500 - the moment any
     * filter added a HAVING and forced the count to wrap the full select instead.
     */
    public function scopeWithDistance($query, $longitude, $latitude)
    {
        if ($query->selectsAlias('distance')) {
            return $query;
        }

        $longitude = (float) trim((string) $longitude, "\"'");
        $latitude  = (float) trim((string) $latitude, "\"'");

        return $query->ensureBaseSelect()
            ->addSelect(DB::raw("ST_Distance_Sphere(point(longitude, latitude), point($longitude, $latitude)) as distance"));
    }

    public function scopeWithAvgRatingAll($query)
    {
        if ($query->selectsAlias('avg_rating_all')) {
            return $query;
        }

        return $query->ensureBaseSelect()->selectSub(function ($query) {
            $query->selectRaw('AVG(reviews.rating)')
                ->from('reviews')
                ->join('food', 'food.id', '=', 'reviews.food_id')
                ->whereColumn('food.restaurant_id', 'restaurants.id')
                // Active reviews only, so the chip ranks by the same rating the card prints.
                ->where('reviews.status', 1)
                ->groupBy('food.restaurant_id');
        }, 'avg_rating_all');
    }

    public function scopeApplyFilters($query, array $filters)
    {
        return $query
            ->when(isset($filters['cuisine_id']) && is_array($filters['cuisine_id']) && count($filters['cuisine_id']) > 0, function($q) use($filters){
                $q->whereHas('cuisine', function($query) use($filters){
                    $query->whereIn('cuisine_restaurant.cuisine_id', $filters['cuisine_id'])->orWhereIn('cuisines.slug', $filters['cuisine_id']);
                });
            })
            ->when(isset($filters['order_type']), function($q) use($filters){
                // Several order types are a union, not a priority list: asking for take away
                // and delivery asks for restaurants doing either. The if/elseif chain this
                // replaces applied only the first type it recognised, so adding take_away to
                // a delivery filter changed nothing at all.
                //
                // Grouped, because an ungrouped OR would escape the zone, status and search
                // conditions the listing has already applied and hand back the whole table.
                // Same shape Food::scopeApplyFilters() uses, so the item and restaurant
                // listings answer the same question the same way.
                $orderTypes = Helpers::filter_value_list($filters['order_type']);

                if (! $orderTypes) {
                    return;
                }

                $q->where(function($query) use($orderTypes){
                    // 'delivery' and 'home_delivery' are one thing written two ways - the
                    // panels store the first, some clients send the second.
                    if(array_intersect(['delivery', 'home_delivery'], $orderTypes)){
                        $query->orWhere('delivery', 1);
                    }
                    if(in_array('take_away', $orderTypes, true)){
                        $query->orWhere('take_away', 1);
                    }
                    if(in_array('dine_in', $orderTypes, true)){
                        $query->orWhereHas('restaurant_config', function($config){
                            $config->where('dine_in', 1);
                        });
                    }
                });
            })
            ->when(isset($filters['filter_by']) && is_array($filters['filter_by']), function ($q) use ($filters) {
                // Same reasoning as Food::scopeApplyFilters(): these three chips say
                // how to order, and CategoryLogic::restaurants() has already ordered
                // by the time this runs. Appending left them as tiebreakers on a key
                // that is already unique, so the chip appeared to do nothing.
                $orderingChips = array_intersect($filters['filter_by'], ['popular', 'new_arrivals', 'top_rated']);

                if ($orderingChips) {
                    $q->reorder();
                }

                foreach ($filters['filter_by'] as $item) {

                    if ($item == 'free_delivery') {
                        $q->where('free_delivery', 1);

                    } elseif ($item == 'discounted') {
                        $q->whereHas('discount', function ($query) {
                            return $query->validate();
                        });

                    } elseif ($item == 'popular') {
                        $hasOrdersCount = false;
                        $existingColumns = $q->getQuery()->columns ?? [];
                        $grammar = $q->getQuery()->getGrammar();
                        foreach ($existingColumns as $col) {
                            $colStr = $col instanceof \Illuminate\Contracts\Database\Query\Expression
                                ? $col->getValue($grammar)
                                : (string) $col;
                            if (str_contains($colStr, 'orders_count')) {
                                $hasOrdersCount = true;
                                break;
                            }
                        }
                        if (!$hasOrdersCount) {
                            $q->withCount('orders');
                        }
                        $q->orderBy('orders_count', 'desc');

                    } elseif ($item == 'new_arrivals') {
                        $q->latest('restaurants.created_at');
                    } elseif ($item == 'top_rated') {
                        // Rated restaurants only, the same way the food chip keeps rated items
                        // only. Without the floor a "Top rated" chip returned the entire zone
                        // with the unreviewed restaurants trailing behind, which reads as the
                        // chip having done nothing.
                        $q->withAvgRatingAll()
                            ->having('avg_rating_all', '>', 0)
                            ->orderBy('avg_rating_all', 'desc')
                            // Settles equal ratings, so a paginated chip cannot repeat one
                            // restaurant on two pages and drop another entirely.
                            ->orderBy('restaurants.id', 'desc');
                    } elseif ($item == 'veg') {
                        $q->where('veg', 1);
                    } elseif ($item == 'non_veg') {
                        $q->where('non_veg', 1);
                    } elseif ($item == 'currently_available') {
                        $q->whereHas('schedules', function($query){
                            $query->where('day', now()->dayOfWeek)
                                ->where('opening_time', '<', now()->format('H:i:s'))
                                ->where('closing_time', '>', now()->format('H:i:s'));
                        });
                    } elseif ($item == 'halal') {
                        $q->whereHas('foods', function($query){
                            $query->where('is_halal', 1);
                        });
                    }
                }
            });
    }

    public function scopeApplySorting($query, $sortBy)
    {
        return $query->when($sortBy && $sortBy !== 'default', function ($q) use ($sortBy) {

            if ($sortBy == 'fast_delivery') {
                $q->reorder()->orderBy(function($query){
                    $query->selectRaw('IF(((select count(*) from `restaurant_schedule` where `restaurants`.`id` = `restaurant_schedule`.`restaurant_id` and `restaurant_schedule`.`day` = '.now()->dayOfWeek.' and `restaurant_schedule`.`opening_time` < "'.now()->format('H:i:s').'" and `restaurant_schedule`.`closing_time` >"'.now()->format('H:i:s').'") > 0), true, false)')
                    ->from('restaurants')->whereColumn('restaurants.id', 'restaurants.id')->limit(1);
                }, 'desc')
                ->orderByRaw("
                    CASE
                        WHEN delivery_time LIKE '%hour%'
                            THEN CAST(SUBSTRING_INDEX(delivery_time,'-',1) AS UNSIGNED) * 60
                        WHEN delivery_time LIKE '%min%'
                            THEN CAST(SUBSTRING_INDEX(delivery_time,'-',1) AS UNSIGNED)
                        ELSE CAST(SUBSTRING_INDEX(delivery_time,'-',1) AS UNSIGNED)
                    END ASC
                ");

            } elseif ($sortBy == 'a_to_z') {
                $q->reorder()->orderBy('name', 'asc');

            } elseif ($sortBy == 'z_to_a') {
                $q->reorder()->orderBy('name', 'desc');
            } elseif ($sortBy == 'distance') {
                $longitude = request()?->header('longitude') ?? request('longitude');
                $latitude = request()?->header('latitude') ?? request('latitude');

                if ($longitude !== null && $latitude !== null && $longitude !== '' && $latitude !== '') {
                    $longitude = trim($longitude, "\"'");
                    $latitude  = trim($latitude, "\"'");

                    $longitude = (float)$longitude;
                    $latitude  = (float)$latitude;

                    $q->reorder()
                        ->withDistance($longitude, $latitude)
                        ->orderBy('distance', 'asc');
                }

            } elseif ($sortBy == 'high_rated') {
                $q->withAvgRatingAll()->reorder()->orderBy('avg_rating_all', 'desc');
            }
        });
    }

    public function scopeApplyRating($query, $request)
    {
        if (! $request) {
            return $query;
        }

        // Same inclusive floor the food listing uses, measured over the restaurant's reviews.
        $threshold = Helpers::rating_filter_threshold($request);

        return $query->when($request->rating == 1, function ($query) {
            return $query->withCount('reviews')->orderBy('reviews_count', 'desc');
        })
        ->when($threshold !== null, function ($query) use ($threshold) {
            $query->withAvgRatingAll()->having('avg_rating_all', '>=', $threshold);
        });
    }

    public function scopeApplyPriceRange($query, $request)
    {
        if (!$request) {
        return $query;
    }
        $price = $request->price ?? null;
        if (is_string($price)) {
            $price = str_replace(['[', ']'], '', $price);
            $price = explode(',', $price);
        }

        return $query->when(($price && count($price) == 2 && is_numeric($price[0]) && is_numeric($price[1])) || $request->min_price || $request->max_price, function ($query) use ($price, $request) {
            $query->whereHas('foods', function($q) use($price, $request){
                $q->applyPriceRange($request);
            });
        });
    }

}
