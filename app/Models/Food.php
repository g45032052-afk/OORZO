<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use App\Scopes\ZoneScope;
use App\Traits\GeneratesSlug;
use App\Scopes\RestaurantScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\ReportFilter;
use Modules\TaxModule\Entities\Taxable;

class Food extends Model
{
    use HasFactory , ReportFilter, GeneratesSlug;
    protected $with = ['storage','translations'];
    protected $casts = [
        'tax' => 'float',
        'price' => 'float',
        'status' => 'integer',
        'discount' => 'float',
        'avg_rating' => 'float',
        'set_menu' => 'integer',
        'category_id' => 'integer',
        'restaurant_id' => 'integer',
        'restaurant_category_id' => 'integer',
        'reviews_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'veg' => 'integer',
        'min' => 'integer',
        'max' => 'integer',
        'maximum_cart_quantity' => 'integer',
        'recommended' => 'integer',
        'order_count'=>'integer',
        'rating_count'=>'integer',
        'is_halal'=>'integer',
    ];
    protected $guarded = [];

    protected $appends = ['image_full_url'];
    public function getImageFullUrlAttribute(){
        $value = $this->image;
        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] == 'image') {
                    return Helpers::get_full_url('product',$value,$storage['value']);
                }
            }
        }

        return Helpers::get_full_url('product',$value,'public');
    }

    public function logs()
    {
        return $this->hasMany(Log::class,'model_id')->where('model','Food');
    }
    public function newVariations()
    {
        return $this->hasMany(Variation::class, 'food_id')->with('variationOptions');
    }

    public function newVariationOptions()
    {
        return $this->hasMany(VariationOption::class, 'food_id');
    }
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class,'food_id');
    }

    public function scopeRecommended($query)
    {
        return $query->where('recommended',1);
    }

    public function carts()
    {
        return $this->morphMany(Cart::class, 'item');
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }


    // public function scopeActive($query)
    // {
    //     return $query->where('status', 1)->whereHas('restaurant', function ($query) {
    //         return $query->where('status', 1);
    //     });
    // }



    public function scopeActive($query)
    {
        return $query->where('status', 1)
        ->whereHas('restaurant', function($query) {
            $query->where('status', 1)
                    ->where(function($query) {
                        $query->where('restaurant_model', 'commission')
                                ->orWhereHas('restaurant_sub', function($query) {
                                    $query->where(function($query) {
                                        $query->where('max_order', 'unlimited')->orWhere('max_order', '>', 0);
                                    });
                                });
                    });
            })
        ->whereHas('category', function ($q) {
            $q->where(function ($q) {
                $q->where([
                        ['parent_id', '=', 0],
                        ['status', '=', 1],
                    ])
                ->orWhere(function ($q) {
                    $q->where('parent_id', '!=', 0)
                        ->whereHas('parent', fn ($p) => $p->where('status', 1));
                });
            });
        });
    }



    public function scopeAvailable($query,$time)
    {
        $query->where(function($q)use($time){
            $q->where('available_time_starts','<=',$time)->where('available_time_ends','>=',$time);
        });
    }

    public function scopePopular($query)
    {
        return $query->orderBy('order_count', 'desc');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function rating()
    {
        return $this->hasMany(Review::class, 'food_id')
            ->select(
                'food_id',
                DB::raw('AVG(rating) as average'),
                DB::raw('COUNT(*) as rating_count'),
                DB::raw('COUNT(CASE WHEN comment IS NOT NULL THEN 1 END) as review_count')
            )
            ->groupBy('food_id');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function restaurantCategory()
    {
        return $this->belongsTo(RestaurantCategory::class, 'restaurant_category_id');
    }

    public function orders()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function storage()
    {
        return $this->morphMany(Storage::class, 'data');
    }

    protected static function booted()
    {
        // dd( app()->getLocale());
        if (auth('vendor')->check() || auth('vendor_employee')->check()) {
            static::addGlobalScope(new RestaurantScope);
        }

        static::addGlobalScope(new ZoneScope);

        // static::addGlobalScope('storage', function ($builder) {
        //     $builder->with('storage');
        // });

        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                return $query->where('locale', app()->getLocale());
            }]);
        });
    }


    public function scopeType($query, $type)
    {
        if ($type == 'veg') {
            return $query->where('veg', true);
        } else if ($type == 'non_veg') {
            return $query->where('veg', false);
        }

        return $query;
    }


    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function allergies()
    {
        return $this->belongsToMany(Allergy::class);
    }
    public function nutritions()
    {
        return $this->belongsToMany(Nutrition::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::created(function ($food) {
            $food->slug = $food->generateSlug($food->name);
            $food->save();
        });

        static::retrieved(function ($food) {
            try {
                if ($food->stock_type != 'daily') return;

                $ordersToday = $food->orders()
                    ->whereDate('created_at', now())
                    ->exists();

                $hasAnyOrder = $food->relationLoaded('orders')
                    ? $food->orders->isNotEmpty()
                    : $food->orders()->exists();

                if ($hasAnyOrder && !$ordersToday) {
                    $food->sell_count = 0;
                    $food->save();
                    $food->newVariationOptions()?->update(['sell_count' => 0]);
                }
            } catch (\Exception $exception) {
                info([$exception->getFile(), $exception->getLine(), $exception->getMessage()]);
            }
        });
        static::saved(function ($model) {
            if($model->isDirty('image')){
                $value = Helpers::getDisk();

                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'image',
                ], [
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        static::saved(function ($model) {
            $ignored = ['sell_count', 'order_count', 'updated_at'];
            if ($model->wasRecentlyCreated || array_diff(array_keys($model->getChanges()), $ignored)) {
                Helpers::deleteCacheData('restaurant_cat_foods_');
            }
        });

        static::deleted(function () {
            Helpers::deleteCacheData('restaurant_cat_foods_');
        });
    }


    public function getNameAttribute($value){
        if (count($this->translations) > 0) {
            // info(count($this->translations));
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'name') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function getDescriptionAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'description') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }
    public function getItemStockAttribute($value){
        return $value - $this->sell_count > 0 ? $value - $this->sell_count : 0 ;
    }
    public function getVariationsAttribute($value)
    {
        try {
            $this->relationLoaded('newVariationOptions');

            if ((is_null($value) || (is_string($value) && empty($decoded = json_decode($value, true)))) && !empty($this->newVariations) && !empty($this->newVariationOptions)) {
                // if( ( $value == null || (is_string($value) &&  (json_decode($value, true) == null || count(json_decode($value, true)) == 0))) && count($this->newVariations) > 0 && count($this->newVariationOptions) > 0 ){
                    $optionsByVariation = $this->newVariationOptions
                ->groupBy('variation_id')
                ->map(fn($options) => $options->map(fn($o) => [
                    'label'         => $o->option_name,
                    'optionPrice'   => $o->option_price,
                    'total_stock'   => (string) $o->total_stock,
                    'stock_type'    => $o->stock_type,
                    'sell_count'    => (string) $o->sell_count,
                    'option_id'     => (int) $o->id,
                    'current_stock' => $o->stock_type === 'unlimited' ? 0 : max(0, $o->total_stock - $o->sell_count),
                ])->all());

            $result = $this->newVariations->map(fn($v) => [
                'variation_id' => (int) $v->id,
                'name'         => $v->name,
                'type'         => $v->type,
                'min'          => (string) $v->min,
                'max'          => (string) $v->max,
                'required'     => $v->is_required ? 'on' : 'off',
                'values'       => $optionsByVariation->get($v->id, []),
            ])->all();

                    return json_encode($result);
                }
                else{
                    return $value ?? json_encode([]);
                }
            } catch (\Exception $exception) {
                info([$exception->getFile(),$exception->getLine(),$exception->getMessage()]);
                return $value ?? json_encode([]);;
        }
    }




    public function taxVats()
    {
        return $this->morphMany(Taxable::class, 'taxable');
    }
    public function foodSeoData()
    {
        return $this->hasOne(FoodSeoData::class, 'food_id', 'id');
    }

    /** Does this query already select the given alias? Guards against a duplicate alias. */
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
     * Keep the table's own columns when a scope adds a subselect.
     *
     * selectSub() on a query that has not selected anything yet REPLACES the implicit `*`
     * rather than adding to it, so a caller that never wrote its own select() - products/latest
     * does not - came back holding nothing but the aggregate, and every item in the response
     * was empty.
     */
    public function scopeEnsureBaseSelect($query)
    {
        if (empty($query->getQuery()->columns)) {
            $query->select($query->getModel()->getTable().'.*');
        }

        return $query;
    }

    /**
     * The item's average review score as a sortable `avg_rating_all` column, counted the way
     * Restaurant::withAvgRatingAll() counts a restaurant's: live, off the active reviews.
     *
     * food.avg_rating is a stored counter written when a review is submitted and never
     * corrected afterwards, so it drifts - a five star item sits there as 4, an item whose
     * only review was hidden still reads above zero, and items with reviews read 0. Filtering
     * or sorting by it therefore disagreed with the rating the customer is shown.
     *
     * Selected only once per query, so asking for the chip and the rating floor together
     * cannot produce "Duplicate column name 'avg_rating_all'".
     */
    public function scopeWithAvgRatingAll($query)
    {
        if ($query->selectsAlias('avg_rating_all')) {
            return $query;
        }

        return $query->ensureBaseSelect()->selectSub(function ($query) {
            $query->selectRaw('AVG(reviews.rating)')
                ->from('reviews')
                ->whereColumn('reviews.food_id', 'food.id')
                ->where('reviews.status', 1)
                ->groupBy('reviews.food_id');
        }, 'avg_rating_all');
    }

    /**
     * How many active reviews the item has, as a sortable `active_reviews_count` column.
     *
     * The tiebreak for the top_rated chip: a five star average off ten reviews is a stronger
     * result than the same average off one. Guarded like withAvgRatingAll(), and deliberately
     * not withCount('reviews') - that alias is claimed by applyRating(), and asking for the
     * chip and the review-count sort together would select `reviews_count` twice.
     */
    public function scopeWithActiveReviewsCount($query)
    {
        if ($query->selectsAlias('active_reviews_count')) {
            return $query;
        }

        return $query->ensureBaseSelect()->selectSub(function ($query) {
            $query->selectRaw('COUNT(*)')
                ->from('reviews')
                ->whereColumn('reviews.food_id', 'food.id')
                ->where('reviews.status', 1);
        }, 'active_reviews_count');
    }

    /**
     * Items that are genuinely on offer in their own right.
     *
     * "Discounted" means the item's own rate and nothing else. A store wide discount belongs
     * to the restaurant - it is advertised on the restaurant card and charged on the whole
     * bill - and a happy hour belongs to the window. Neither makes any one item a discounted
     * item, and both take priority over the item's own rate while they run
     * (Helpers::product_discount_calculate resolves happy hour, then store wide, then the
     * item), so an item sitting under either is not selling at the rate this filter is for.
     *
     * That is why the two exclusions are here rather than only the first: an item with its
     * own 20% inside a running happy hour is not charging 20% today, so listing it as
     * discounted advertises a price the checkout will not honour.
     *
     * $zoneIds narrows the happy hour lookup when the caller knows the customer's zones; a
     * filter applied on the model does not, and then every zone is considered.
     */
    public function scopeItemDiscounted($query, array $zoneIds = [])
    {
        $inHappyHour = $zoneIds
            ? Helpers::restaurant_ids_in_running_happy_hour($zoneIds)
            : Helpers::restaurant_ids_in_any_running_happy_hour();

        return $query->where('food.discount', '>', 0)
            ->whereDoesntHave('restaurant.discount', function ($discount) {
                $discount->validate();
            })
            ->when($inHappyHour, fn ($q) => $q->whereNotIn('food.restaurant_id', $inHappyHour));
    }

    public function scopeApplyFilters($query, array $filters)
    {
        return $query
            ->when(isset($filters['cuisine_id']) && is_array($filters['cuisine_id']) && count($filters['cuisine_id']) > 0, function($q) use($filters){
                $q->whereHas('restaurant.cuisine', function($query) use($filters){
                    $query->whereIn('cuisine_restaurant.cuisine_id', $filters['cuisine_id'])->orWhereIn('cuisines.slug', $filters['cuisine_id']);
                });
            })
            ->when(isset($filters['restaurant_category_id']) && is_numeric($filters['restaurant_category_id']), function($q) use($filters){
                $q->where('restaurant_category_id', $filters['restaurant_category_id']);
            })
            ->when(isset($filters['order_type']), function($q) use($filters){
                // Read through the same normaliser the restaurant listing uses, so a comma
                // list or a json array works here too and not only `order_type[]=`.
                $orderTypes = Helpers::filter_value_list($filters['order_type']);

                if (! $orderTypes) {
                    return;
                }

                $q->whereHas('restaurant', function($query) use($orderTypes){
                    $query->where(function($q) use($orderTypes){
                        if(array_intersect(['delivery', 'home_delivery'], $orderTypes)){
                            $q->orWhere('delivery', 1);
                        }
                        if(in_array('take_away', $orderTypes, true)){
                            $q->orWhere('take_away', 1);
                        }
                        if(in_array('dine_in', $orderTypes, true)){
                            $q->orWhereHas('restaurant_config', function($q){
                                $q->where('dine_in', 1);
                            });
                        }
                    });
                });
            })
            ->when(isset($filters['filter_by']) && is_array($filters['filter_by']), function ($q) use ($filters) {
                // popular / new_arrivals / top_rated say how to order, not what to keep,
                // and the caller has usually ordered already - CategoryLogic sorts
                // newest-first by default. Appending to that left the chip as a
                // tiebreaker on a key that is already unique, so picking one changed
                // nothing at all. Clear the inherited order once, then let the chips
                // below define it, composing in the order they were asked for.
                $orderingChips = array_intersect($filters['filter_by'], ['popular', 'new_arrivals', 'top_rated']);

                if ($orderingChips) {
                    $q->reorder();
                }

                foreach ($filters['filter_by'] as $item) {
                    if ($item == 'free_delivery') {
                        $q->whereHas('restaurant', function($query){
                            $query->where('free_delivery', 1);
                        });
                    } elseif ($item == 'discounted') {
                        // The item's own rate only - see scopeItemDiscounted().
                        $q->itemDiscounted(
                            array_values(array_filter((array) ($filters['zone_id'] ?? [])))
                        );
                    } elseif ($item == 'popular') {
                        // Ties are settled by id, so a paginated list cannot repeat one item
                        // on two pages and drop another - order_count and created_at are both
                        // full of ties, and MySQL is free to order tied rows differently from
                        // one query to the next.
                        $q->orderBy('order_count', 'desc')->orderByDesc('food.id');
                    } elseif ($item == 'new_arrivals') {
                        $q->latest('food.created_at')->orderByDesc('food.id');
                    } elseif ($item == 'top_rated') {
                        // Rated items only - an unreviewed item is not "top rated" - ranked by
                        // the live average of its active reviews, the same figure the item and
                        // restaurant payloads report. Mirrors the restaurant chip.
                        // Ties are the rule here, not the exception - a shelf of items rated
                        // 5.00 - so the order is settled all the way down: better rated first,
                        // then more reviewed, then newest. Without that last step MySQL orders
                        // the tied rows however it likes and the same item shows up on two
                        // pages of the strip while another never appears.
                        $q->withAvgRatingAll()
                            ->withActiveReviewsCount()
                            ->having('avg_rating_all', '>', 0)
                            ->orderBy('avg_rating_all', 'desc')
                            ->orderBy('active_reviews_count', 'desc')
                            ->orderBy('food.id', 'desc');
                    } elseif ($item == 'veg') {
                        $q->where('veg', 1);
                    } elseif ($item == 'non_veg') {
                        $q->where('veg', 0);
                    } elseif ($item == 'currently_available') {
                        $q->available(now()->format('H:i:s'));
                    } elseif ($item == 'currently_open') {
                        // The item's restaurant is open right now — same schedule
                        // expression the restaurant listing uses.
                        $q->whereHas('restaurant', function($query){
                            $query->where('active', 1)->Weekday()
                                ->whereHas('schedules', function($schedule){
                                    $schedule->where('day', now()->dayOfWeek)
                                        ->where('opening_time', '<', now()->format('H:i:s'))
                                        ->where('closing_time', '>', now()->format('H:i:s'));
                                });
                        });
                    } elseif ($item == 'halal') {
                        $q->where('is_halal', 1);
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
                    ->from('restaurants')->whereColumn('restaurants.id', 'food.restaurant_id')->limit(1);
                }, 'desc')
                ->orderBy(function($query){
                    $query->selectRaw("
                        CASE
                            WHEN delivery_time LIKE '%hour%'
                                THEN CAST(SUBSTRING_INDEX(delivery_time,'-',1) AS UNSIGNED) * 60
                            WHEN delivery_time LIKE '%min%'
                                THEN CAST(SUBSTRING_INDEX(delivery_time,'-',1) AS UNSIGNED)
                            ELSE CAST(SUBSTRING_INDEX(delivery_time,'-',1) AS UNSIGNED)
                        END")
                    ->from('restaurants')->whereColumn('restaurants.id', 'food.restaurant_id')->limit(1);
                }, 'asc');
            } elseif ($sortBy == 'a_to_z') {
                $q->reorder()->orderBy('name', 'asc');
            } elseif ($sortBy == 'z_to_a') {
                $q->reorder()->orderBy('name', 'desc');
            } elseif ($sortBy == 'price_low_to_high' || $sortBy == 'price_low') {
                // price_low / price_high are what the search screen sends. They matched no
                // branch here and no branch in the search controller's own sort block, so the
                // request came back in whatever order the database happened to give.
                $q->reorder()->orderBy('price', 'asc');
            } elseif ($sortBy == 'price_high_to_low' || $sortBy == 'price_high') {
                $q->reorder()->orderBy('price', 'desc');
            } elseif ($sortBy == 'distance') {
                $longitude = request()->header('longitude') ?? request('longitude');
                $latitude = request()->header('latitude') ?? request('latitude');

                if ($longitude && $latitude) {
                    $q->reorder()
                        ->selectSub(function ($query) use ($longitude, $latitude) {
                            $query->selectRaw(
                                'ST_Distance_Sphere(point(longitude, latitude), point(?, ?))',
                                [$longitude, $latitude]
                            )
                            ->from('restaurants')
                            ->whereColumn('restaurants.id', 'food.restaurant_id')
                            ->limit(1);
                        }, 'distance')
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

        // One inclusive floor instead of the old ladder of overlapping comparisons:
        // rating_3_plus now keeps items rated exactly 3, not just those above it.
        $threshold = Helpers::rating_filter_threshold($request);

        return $query->when($request->rating == 1, function ($query) {
            // Active reviews only, so the count matches the one shown beside the item.
            return $query->whereHas('reviews', fn ($r) => $r->where('reviews.status', 1))
                ->withCount(['reviews' => fn ($r) => $r->where('reviews.status', 1)])
                ->orderBy('reviews_count', 'desc');
        })
        ->when($threshold !== null, function ($query) use ($threshold) {
            // Same live average the top_rated chip uses, not the drifting stored column.
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

        return $query->when($price && count($price) == 2 && is_numeric($price[0]) && is_numeric($price[1]), function ($query) use ($price) {
            $query->whereBetween('price', [round($price[0], 2), round($price[1], 2)]);
        })->when($request->min_price, function ($query) use ($request) {
            $query->where('price', '>=', $request->min_price);
        })->when($request->max_price, function ($query) use ($request) {
            $query->where('price', '<=', $request->max_price);
        });
    }
}
