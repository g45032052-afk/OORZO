<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use App\Scopes\ZoneScope;
use App\Traits\ReportFilter;
use Illuminate\Support\Facades\DB;
use App\Traits\GeneratesSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\TaxModule\Entities\Taxable;

class ItemCampaign extends Model
{
    use HasFactory ,ReportFilter, GeneratesSlug;

    protected $casts = [
        'tax' => 'float',
        'price' => 'float',
        'discount' => 'float',
        'status' => 'integer',
        'restaurant_id' => 'integer',
        'category_id' => 'integer',
        'veg' => 'integer',
        'created_at'=>'datetime',
        'start_date'=>'datetime',
        'updated_at'=>'datetime',
        'end_date'=>'datetime',
        'start_time'=>'datetime',
        'end_time'=>'datetime',
        'maximum_cart_quantity' => 'integer',

    ];
    protected $appends = ['image_full_url'];

    public function getImageFullUrlAttribute(){
        $value = $this->image;
        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] == 'image') {
                    return Helpers::get_full_url('campaign',$value,$storage['value']);
                }
            }
        }

        return Helpers::get_full_url('campaign',$value,'public');
    }

    public function carts()
    {
    return $this->morphMany(Cart::class, 'item');
    }


    public function getTitleAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'title') {
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

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function orderdetails()
    {
        return $this->hasMany(OrderDetail::class)->latest();
    }

    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }

    public function scopeRunning($query)
    {
        return $query->whereDate('end_date', '>=', date('Y-m-d'))->whereDate('start_date', '<=', date('Y-m-d'))->whereTime('start_time' , '<=' , now()->format('H:i:s'))->whereTime('end_time' , '>=' , now()->format('H:i:s'));

    }

    public function storage()
    {
        return $this->morphMany(Storage::class, 'data');
    }

    protected static function booted()
    {
        static::addGlobalScope(new ZoneScope);
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                return $query->where('locale', app()->getLocale());
            }]);
        });
        // static::addGlobalScope('storage', function ($builder) {
        //     $builder->with('storage');
        // });
    }

    protected static function boot()
    {
        parent::boot();
        static::created(function ($itemcampaign) {
            $itemcampaign->slug = $itemcampaign->generateSlug($itemcampaign->title);
            $itemcampaign->save();
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
    }

    public function taxVats()
    {
        return $this->morphMany(Taxable::class, 'taxable');
    }

        public function foodSeoData()
    {
        return $this->hasOne(FoodSeoData::class, 'item_campaign_id', 'id');
    }
    public function allergies()
    {
        return $this->belongsToMany(Allergy::class);
    }
    public function nutritions()
    {
        return $this->belongsToMany(Nutrition::class);
    }


}
