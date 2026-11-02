<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProCustomerFaq extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'priority' => 'integer',
        'status' => 'integer',
    ];

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getQuestionAttribute($value)
    {
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'pro_faq_question') {
                    return $translation['value'];
                }
            }
        }
        return $value;
    }

    public function getAnswerAttribute($value)
    {
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'pro_faq_answer') {
                    return $translation['value'];
                }
            }
        }
        return $value;
    }

    protected static function booted()
    {
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                return $query->where('locale', app()->getLocale());
            }]);
        });
    }
}
