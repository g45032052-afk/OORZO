<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DataSetting extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $with = ['storage'];

    protected $casts = [
        'id' => 'integer',
        // 'status' => 'integer',
    ];

    protected $fillable = [
        'key',
        'type',
        'value'
    ];

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }


    public function getValueAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == $this->key) {
                    return $translation['value'];
                }
            }
        }
        return $value;
    }
    public function storage()
    {
        return $this->morphMany(Storage::class, 'data');
    }

    protected static function booted()
    {
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                return $query->where('locale', app()->getLocale());
            }]);
        });
    }

    protected static function boot()
    {
        parent::boot();
        static::saved(function ($model) {
            $value = Helpers::getDisk();

            DB::table('storages')->updateOrInsert([
                'data_type' => get_class($model),
                'data_id' => $model->id,
            ], [
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

    }

    public static function invoiceSettings()
    {
        return self::where('type', 'invoice_settings')
            ->whereIn('key', [
                'invoice_logo_status',
                'invoice_logo_type',
                'invoice_logo',
                'business_identity_status',
                'business_identity_type',
                'tax_number',
                'bin_number',
                'musak_number',
                'terms&condition_status',
                'terms&condition_content',
                'copyright_text_status',
                'copyright_text_content',
            ])
            ->pluck('value', 'key')
            ->toArray();
    }

    public function getInvoiceLogoAttribute()
    {
        $data = self::invoiceSettings();

        if (
            isset($data['invoice_logo_type']) &&
            $data['invoice_logo_type'] === 'upload_new_logo' &&
            !empty($data['invoice_logo'])
        ) {
            return dynamicStorage('storage/app/public/business/' . $data['invoice_logo']);
        }

        $logo = BusinessSetting::where('key', 'logo')->first();
        $businessLogoUrl = $logo?->value ? Helpers::get_full_url('business', $logo?->value ?? '', $logo?->storage[0]?->value ?? 'public','restaurant') : null;
        if ($businessLogoUrl) {
            return $businessLogoUrl;
        }

        return dynamicAsset('assets/admin/img/100x100/1.png');
    }

}
