<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use App\Traits\GeneratesSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HappyHour extends Model
{
    use HasFactory, GeneratesSlug;

    /**
     * Every window is a fixed one hour, so end_time is always start_time + 1h.
     */
    public const DURATION_MINUTES = 60;

    protected $casts = [
        'status' => 'integer',
        'zone_id' => 'integer',
        'discount' => 'float',
        'min_order_amount' => 'float',
        'is_permanent' => 'boolean',
        'weekly_days' => 'array',
        'custom_days' => 'array',
        'custom_times' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['cover_image_full_url', 'icon_full_url'];

    public function getCoverImageFullUrlAttribute()
    {
        return $this->resolveStoredImage('cover_image', $this->cover_image, 'campaign');
    }

    public function getIconFullUrlAttribute()
    {
        return $this->resolveStoredImage('icon', $this->icon, 'upload_1_1');
    }

    private function resolveStoredImage(string $key, $value, string $placeholder)
    {
        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] == $key) {
                    return Helpers::get_full_url('happy_hour', $value, $storage['value'], $placeholder);
                }
            }
        }

        return Helpers::get_full_url('happy_hour', $value, 'public', $placeholder);
    }

    public function storage()
    {
        return $this->morphMany(Storage::class, 'data');
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getTitleAttribute($value)
    {
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'title') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function getShortDescriptionAttribute($value)
    {
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'short_description') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Expanded occurrences. Empty for permanent weekly rules, which are evaluated from the
     * definition instead - see isRunningNow().
     */
    public function dates()
    {
        return $this->hasMany(HappyHourDate::class);
    }

    public function restaurants()
    {
        return $this->belongsToMany(Restaurant::class, 'happy_hour_restaurant')
            ->withPivot(['id', 'status', 'rejection_reason', 'rejected_by', 'requested_by', 'joined_at'])
            ->withTimestamps();
    }

    public function enrollments()
    {
        return $this->hasMany(HappyHourRestaurant::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * How a happy hour is described to a customer app.
     *
     * On the model rather than in a controller because several endpoints hand the same object
     * back - the store list, the home banner and the store details page - and they must not
     * describe the same happy hour differently.
     */
    public function customerPayload(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'cover_image_full_url' => $this->cover_image_full_url,
            'icon_full_url' => $this->icon_full_url,
            'discount' => (float) $this->discount,
            'min_order_amount' => $this->min_order_amount !== null ? (float) $this->min_order_amount : null,
            'duration_type' => $this->duration_type,
            'is_permanent' => (bool) $this->is_permanent,
            'weekly_days' => $this->weekly_days ?? [],
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'is_running_now' => $this->isRunningNow(),
        ];
    }

    /**
     * Whether this happy hour has run its course - no occurrence of it is left.
     *
     * Not the same question as end_date being in the past, because only some happy hours
     * have an end_date at all:
     *
     *   permanent weekly - no end and no dated rows; it runs until it is switched off
     *   daily / weekly   - bounded by start_date and end_date
     *   custom           - hand-picked days, expanded into happy_hour_dates, with BOTH date
     *                      columns left null - so an end_date test can never answer for one,
     *                      and a custom schedule whose last day was last week still read as
     *                      live everywhere it was asked
     *
     * Compared by day rather than by instant: a happy hour whose last day is today is still
     * running for the rest of today, which is also how the listing queries read it.
     *
     * Only ever true on positive knowledge - an end date, or a dated row - so a schedule that
     * has not been written yet is never mistaken for one that is over.
     */
    public function hasEnded(): bool
    {
        if ($this->is_permanent) {
            return false;
        }

        if ($this->end_date) {
            return $this->end_date->isBefore(today());
        }

        $lastDay = $this->dates()->max('applicable_date');

        return $lastDay !== null && \Illuminate\Support\Carbon::parse($lastDay)->isBefore(today());
    }

    /**
     * The listing twin of hasEnded(): happy hours that still have something left to run.
     *
     * The lists used to ask this as "end_date is null OR end_date >= today", where the null
     * branch was there for permanent weekly rules - but a CUSTOM schedule leaves end_date
     * null too, so every custom happy hour passed for ever, however long ago its last day
     * had been. A restaurant kept an expired one on its list with no way to be rid of it.
     *
     * Kept beside hasEnded() so the row rule and the query rule cannot drift, and phrased the
     * same way round: a row is dropped only when it positively has ended, so a schedule that
     * has not been written yet stays listed rather than vanishing.
     */
    public function scopeNotEnded($query)
    {
        return $query->where(function ($q) {
            $q->where('is_permanent', 1)
                // Null end dates never satisfy this, which is what sends a custom schedule
                // to the branch below.
                ->orWhereDate('end_date', '>=', now()->toDateString())
                ->orWhere(function ($q) {
                    $q->whereNull('end_date')
                        ->where('is_permanent', 0)
                        ->where(function ($q) {
                            $q->whereDoesntHave('dates')
                                ->orWhereHas('dates', fn ($d) => $d->whereDate('applicable_date', '>=', now()->toDateString()));
                        });
                });
        });
    }

    /**
     * The day this happy hour finishes, for the sentences that name it. Null while it has no
     * end at all.
     */
    public function endsOn(): ?\Illuminate\Support\Carbon
    {
        if ($this->is_permanent) {
            return null;
        }

        if ($this->end_date) {
            return $this->end_date;
        }

        $lastDay = $this->dates()->max('applicable_date');

        return $lastDay ? \Illuminate\Support\Carbon::parse($lastDay) : null;
    }

    /**
     * Asked repeatedly of the same window - once while a listing is primed, again for every
     * card that carries the payload - and for a dated happy hour each ask is a query. The
     * schedule cannot move inside one request, so the first answer stands.
     */
    private ?bool $running_now = null;

    public function isRunningNow(): bool
    {
        return $this->running_now ??= $this->resolveRunningNow();
    }

    /**
     * A permanent weekly rule writes no dated rows, so it has to be matched against the
     * definition. Everything else resolves from the expanded table.
     */
    private function resolveRunningNow(): bool
    {
        $now = now();
        $time = $now->format('H:i:s');

        if ($this->is_permanent && $this->duration_type === 'weekly') {
            return in_array($now->format('l'), $this->weekly_days ?? [], true)
                && $this->start_time <= $time
                && $this->end_time >= $time;
        }

        return $this->dates()
            ->whereDate('applicable_date', $now->toDateString())
            ->whereTime('start_time', '<=', $time)
            ->whereTime('end_time', '>=', $time)
            ->exists();
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

        static::created(function ($happyHour) {
            $happyHour->slug = $happyHour->generateSlug($happyHour->title);
            $happyHour->save();
        });

        static::saved(function ($model) {
            foreach (['cover_image', 'icon'] as $key) {
                if ($model->isDirty($key)) {
                    DB::table('storages')->updateOrInsert([
                        'data_type' => get_class($model),
                        'data_id' => $model->id,
                        'key' => $key,
                    ], [
                        'value' => Helpers::getDisk(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }
}
