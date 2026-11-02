<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Exceptions\SurgeConflictException;
use App\Exports\SurgePriceExport;
use App\Http\Controllers\Controller;
use App\Models\SurgePrice;
use App\Models\Zone;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class SurgePriceController extends Controller
{
    /**
     * How far ahead a single rule may be materialised. A rule spanning years
     * would write one surge_price_dates row per day and bloat the hot lookup
     * table for no practical gain.
     */
    private const MAX_SPAN_DAYS = 366;

    public function index(Request $request)
    {
        $key = $this->searchTerms($request['search'] ?? null);

        $surges = SurgePrice::with('zone')
            ->when($key, function ($query) use ($key) {
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('surge_price_name', 'like', "%{$value}%");
                    }
                });
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('admin-views.surge-price.index', compact('surges'));
    }

    /**
     * Search terms, trimmed and with the blanks dropped.
     *
     * Padding matters here: a raw explode(' ') on ' HIGH DEMAND ' yields empty tokens, each of
     * which becomes LIKE '%%' and matches every row - so a padded search silently returned the
     * whole table instead of narrowing it.
     *
     * Returns an empty array when nothing usable is left, which callers use to skip the clause.
     */
    private function searchTerms(?string $search): array
    {
        return array_values(array_filter(
            preg_split('/\s+/', trim((string) $search)) ?: [],
            fn ($term) => $term !== ''
        ));
    }

    public function create()
    {
        $language = getWebConfig('language');
        // Every zone, not just the switched-on ones. A zone is switched OFF precisely when it
        // has no delivery or ETA setup - see the 2026_08_20 deactivate_zones_without_delivery
        // _or_eta_setup migration - so filtering these dropdowns by status hid exactly the
        // zones an admin comes here to set up, and an edit whose zone had since been switched
        // off lost its own selection from the list.
        $zones = Zone::orderBy('name')->get();

        return view('admin-views.surge-price.create', compact('language', 'zones'));
    }

    public function store(Request $request)
    {
        $this->trimTextInput($request);

        $validator = $this->validateSurge($request);

        if ($validator->fails()) {
            return $this->validationFailed($request, $validator);
        }

        DB::beginTransaction();

        try {
            $surge = new SurgePrice();
            $surge->zone_id = $request->zone_id;
            $this->fillSurge($surge, $request);
            $surge->save();

            $this->saveTranslations($request, $surge);
            $this->insertSurgePriceDates($surge);

            DB::commit();

            return $this->successResponse($request, translate('messages.surge_price_created_successfully'));
        } catch (SurgeConflictException $e) {
            DB::rollBack();

            return $this->conflictResponse($request, $e);
        } catch (\Exception $e) {
            DB::rollBack();
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return $this->serverErrorResponse($request, $e);
        }
    }

    public function edit($id)
    {
        $surge = SurgePrice::withoutGlobalScope('translate')
            ->with(['translations', 'details'])
            ->findOrFail($id);

        $language = getWebConfig('language');
        // Every zone, not just the switched-on ones. A zone is switched OFF precisely when it
        // has no delivery or ETA setup - see the 2026_08_20 deactivate_zones_without_delivery
        // _or_eta_setup migration - so filtering these dropdowns by status hid exactly the
        // zones an admin comes here to set up, and an edit whose zone had since been switched
        // off lost its own selection from the list.
        $zones = Zone::orderBy('name')->get();

        return view('admin-views.surge-price.edit', compact('surge', 'language', 'zones'));
    }

    public function update(Request $request, $id)
    {
        $this->trimTextInput($request);

        $validator = $this->validateSurge($request);

        if ($validator->fails()) {
            return $this->validationFailed($request, $validator);
        }

        DB::beginTransaction();

        try {
            $surge = SurgePrice::findOrFail($id);
            $surge->zone_id = $request->zone_id;
            $this->fillSurge($surge, $request);
            $surge->save();

            // Drop the old calendar before re-materialising so the rule never
            // conflicts with its own previous rows.
            $surge->details()->delete();

            $this->saveTranslations($request, $surge);
            $this->insertSurgePriceDates($surge);

            DB::commit();

            return $this->successResponse($request, translate('messages.surge_price_updated_successfully'));
        } catch (SurgeConflictException $e) {
            DB::rollBack();

            return $this->conflictResponse($request, $e);
        } catch (\Exception $e) {
            DB::rollBack();
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return $this->serverErrorResponse($request, $e);
        }
    }

    public function status(Request $request, $id, $status)
    {
        $surge = SurgePrice::findOrFail($id);
        $surge->status = $status;
        $surge->save();

        Toastr::success(translate('messages.surge_price_status_updated'));

        return back();
    }

    public function destroy(Request $request, $id)
    {
        $surge = SurgePrice::findOrFail($id);
        $surge->details()->delete();
        $surge->translations()->delete();
        $surge->delete();

        Toastr::success(translate('messages.surge_price_deleted_successfully'));

        return back();
    }

    public function export(Request $request, $type)
    {
        try {
            $key = $this->searchTerms($request['search'] ?? null);

            $surges = SurgePrice::with('zone')
                ->when($key, function ($query) use ($key) {
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('surge_price_name', 'like', "%{$value}%");
                        }
                    });
                })
                ->latest()
                ->get();

            $data = [
                'data' => $surges,
                'search' => $request['search'] ?? null,
            ];

            if ($type == 'csv') {
                return Excel::download(new SurgePriceExport($data), 'SurgePrice.csv');
            }

            return Excel::download(new SurgePriceExport($data), 'SurgePrice.xlsx');
        } catch (\Exception $e) {
            Toastr::error("line___{$e->getLine()}", $e->getMessage());
            info(["line___{$e->getLine()}", $e->getMessage()]);

            return back();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    /**
     * Trim the per-language free-text fields in place, before validation reads them.
     *
     * The app has no global TrimStrings middleware, so ' High Demand ' would otherwise be
     * validated, stored and rendered with its padding - and the max: rules would count the
     * spaces against the limit.
     */
    private function trimTextInput(Request $request): void
    {
        foreach (['surge_price_name', 'customer_note'] as $field) {
            if (! is_array($request->input($field))) {
                continue;
            }

            $request->merge([
                $field => array_map(
                    fn ($value) => is_string($value) ? trim($value) : $value,
                    $request->input($field)
                ),
            ]);
        }
    }

    private function validateSurge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'surge_price_name' => 'required|array',
            'surge_price_name.0' => 'required|max:100',
            'customer_note' => 'nullable|array',
            'customer_note.*' => 'nullable|max:300',
            // The note is the whole explanation the customer gets for paying more, so
            // switching the toggle on without writing one ships an empty reason. The form
            // marks the field required from the same toggle, but that is markup - the
            // request can arrive without it, and the toggle is what decides here too.
            'customer_note.0' => [
                Rule::requiredIf(fn () => $request->has('customer_note_status')),
                'max:300',
            ],
            'zone_id' => 'required|exists:zones,id',
            'price' => [
                'required',
                'numeric',
                // Greater than zero, not min:0 - a zero rate passes every other check and
                // stores a surge that applyToFee() then returns unchanged, so the record
                // exists but does nothing.
                'gt:0',
                Rule::when($request->price_type === 'percent', ['max:100']),
            ],
            'price_type' => 'required|in:percent,amount',
            'duration_type' => 'required|in:daily,weekly,custom',
            'daily_date_range' => 'required_if:duration_type,daily',
            'daily_time_range' => 'required_if:duration_type,daily',
            'weekly_date_range' => [
                Rule::requiredIf(function () use ($request) {
                    return $request->duration_type === 'weekly' && $request->is_permanent != 1;
                }),
            ],
            'weekly_time_range' => 'required_if:duration_type,weekly',
            'weekly_days' => 'required_if:duration_type,weekly',
            'custom_days' => 'required_if:duration_type,custom',
            'custom_times' => 'required_if:duration_type,custom',
        ], [
            'surge_price_name.0.required' => translate('messages.default_surge_price_name_required'),
            'surge_price_name.0.max' => translate('messages.surge_price_name_max_100'),
            'customer_note.*.max' => translate('messages.customer_note_max_300'),
            'customer_note.0.required' => translate('messages.customer_note_required_when_shown'),
            'customer_note.0.max' => translate('messages.customer_note_max_300'),
            'zone_id.required' => translate('messages.zone_is_required'),
            'daily_date_range.required_if' => translate('messages.daily_date_range_required'),
            'daily_time_range.required_if' => translate('messages.daily_time_range_required'),
            'weekly_date_range.required_if' => translate('messages.weekly_date_range_required'),
            'weekly_time_range.required_if' => translate('messages.weekly_time_range_required'),
            'weekly_days.required_if' => translate('messages.weekly_days_required'),
            'custom_days.required_if' => translate('messages.custom_days_required'),
            'custom_times.required_if' => translate('messages.custom_times_required'),
            'price.max' => translate('messages.percent_price_cannot_exceed_100'),
        ]);

        // A window whose end is at or before its start (10 PM - 2 AM) would make
        // every overlap check and every runtime lookup silently miss the rule,
        // so it is rejected rather than stored as an unreachable record.
        $validator->after(function ($validator) use ($request) {
            foreach ($this->collectTimeRanges($request) as $label => $range) {
                if ($range === null) {
                    continue;
                }
                [$start, $end] = $range;
                if ($end <= $start) {
                    $validator->errors()->add($label, translate('messages.surge_end_time_must_be_after_start_time'));
                }
            }

            $span = $this->requestedSpan($request);
            if ($span !== null && $span > self::MAX_SPAN_DAYS) {
                $validator->errors()->add(
                    $request->duration_type === 'weekly' ? 'weekly_date_range' : 'daily_date_range',
                    translate('messages.surge_date_range_too_long')
                );
            }
        });

        return $validator;
    }

    /**
     * Every time window the request carries, keyed by the field to attach an
     * error to. Values are [start, end] as H:i:s, or null when unparseable.
     *
     * @return array<string, array{0:string,1:string}|null>
     */
    private function collectTimeRanges(Request $request): array
    {
        $ranges = [];

        if ($request->duration_type === 'daily') {
            $ranges['daily_time_range'] = $this->parseTimeRange($request->daily_time_range);
        } elseif ($request->duration_type === 'weekly') {
            $ranges['weekly_time_range'] = $this->parseTimeRange($request->weekly_time_range);
        } elseif ($request->duration_type === 'custom') {
            foreach ($this->splitList($request->custom_times) as $i => $time) {
                $ranges['custom_times.' . $i] = $this->parseTimeRange($time);
            }
        }

        return $ranges;
    }

    /**
     * Days covered by the requested date range, or null when the rule has no
     * bounded range (custom rules, and permanent weekly rules).
     */
    private function requestedSpan(Request $request): ?int
    {
        $raw = match ($request->duration_type) {
            'daily' => $request->daily_date_range,
            'weekly' => $request->is_permanent == 1 ? null : $request->weekly_date_range,
            default => null,
        };

        $range = $this->parseDateRange($raw);
        if ($range === null) {
            return null;
        }

        [$start, $end] = $range;

        return (strtotime($end) - strtotime($start)) / 86400 + 1;
    }

    /*
    |--------------------------------------------------------------------------
    | Persistence
    |--------------------------------------------------------------------------
    */

    private function fillSurge(SurgePrice $surge, Request $request): void
    {
        $defaultIndex = array_search('default', (array) $request->lang);

        $surge->surge_price_name = $request->surge_price_name[$defaultIndex];
        $surge->customer_note = $request->customer_note[$defaultIndex] ?? null;
        $surge->customer_note_status = $request->has('customer_note_status') ? 1 : 0;
        $surge->duration_type = $request->duration_type;
        $surge->price = $request->price;
        $surge->price_type = $request->price_type;

        // Each branch clears the columns the other branches own, so switching a
        // rule's schedule type on edit never leaves stale dates behind.
        if ($surge->duration_type === 'daily') {
            [$startDate, $endDate] = $this->parseDateRange($request->daily_date_range);
            [$startTime, $endTime] = $this->parseTimeRange($request->daily_time_range);

            $surge->start_date = $startDate;
            $surge->end_date = $endDate;
            $surge->start_time = $startTime;
            $surge->end_time = $endTime;
            $surge->is_permanent = 0;
            $surge->weekly_days = null;
            $surge->custom_days = null;
            $surge->custom_times = null;
        } elseif ($surge->duration_type === 'weekly') {
            [$startTime, $endTime] = $this->parseTimeRange($request->weekly_time_range);

            $surge->start_time = $startTime;
            $surge->end_time = $endTime;
            $surge->weekly_days = $this->splitList($request->weekly_days);
            $surge->is_permanent = $request->is_permanent == 1 ? 1 : 0;

            if ($surge->is_permanent) {
                $surge->start_date = null;
                $surge->end_date = null;
            } else {
                [$startDate, $endDate] = $this->parseDateRange($request->weekly_date_range);
                $surge->start_date = $startDate;
                $surge->end_date = $endDate;
            }

            $surge->custom_days = null;
            $surge->custom_times = null;
        } else {
            $surge->custom_days = $this->splitList($request->custom_days);
            $surge->custom_times = $this->splitList($request->custom_times);
            $surge->is_permanent = 0;
            $surge->start_date = null;
            $surge->end_date = null;
            $surge->start_time = null;
            $surge->end_time = null;
            $surge->weekly_days = null;
        }
    }

    private function saveTranslations(Request $request, SurgePrice $surge): void
    {
        Helpers::add_or_update_translations($request, 'surge_price_name', 'surge_price_name', 'SurgePrice', $surge->id, $surge->surge_price_name);
        Helpers::add_or_update_translations($request, 'customer_note', 'customer_note', 'SurgePrice', $surge->id, $surge->customer_note);
    }

    /**
     * Expand the rule into one surge_price_dates row per applicable date,
     * refusing any date whose window overlaps an existing surge in the zone.
     *
     * Permanent weekly rules are deliberately NOT materialised — they have no
     * end date, so they are matched at runtime by the resolver's fallback
     * query. They are still validated against the existing calendar here.
     */
    private function insertSurgePriceDates(SurgePrice $surge): void
    {
        $rows = [];

        foreach ($this->plannedDates($surge) as [$dateStr, $startTime, $endTime]) {
            $dayName = date('l', strtotime($dateStr));

            $this->guardAgainstPermanentWeekly($surge, $dayName, $startTime, $endTime);
            $this->guardAgainstMaterialisedDates($surge, $dateStr, $startTime, $endTime);

            // Permanent rules validate only; there is no finite set of dates to
            // write, and the resolver reads them straight off surge_prices.
            if ($surge->is_permanent) {
                continue;
            }

            $rows[] = [
                'surge_price_id' => $surge->id,
                'zone_id' => $surge->zone_id,
                'status' => 1,
                'applicable_date' => $dateStr,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('surge_price_dates')->insert($chunk);
        }
    }

    /**
     * Every (date, start, end) the rule covers.
     *
     * A permanent weekly rule has no bounded range, so it yields one probe per
     * selected weekday — enough for the conflict checks to run, since a
     * permanent rule collides with anything on that weekday at that time.
     *
     * @return \Generator<array{0:string,1:string,2:string}>
     */
    private function plannedDates(SurgePrice $surge): \Generator
    {
        if ($surge->duration_type === 'custom') {
            $days = $surge->custom_days ?? [];
            $times = $surge->custom_times ?? [];

            foreach ($days as $i => $day) {
                $range = $this->parseTimeRange($times[$i] ?? null);
                if ($range === null) {
                    continue;
                }
                yield [date('Y-m-d', strtotime($day)), $range[0], $range[1]];
            }

            return;
        }

        $weekdays = $surge->duration_type === 'weekly' ? ($surge->weekly_days ?? []) : null;

        if ($surge->is_permanent) {
            // Probe the next occurrence of each selected weekday purely so the
            // guards below have a concrete date to reason about.
            foreach ($weekdays as $weekday) {
                yield [date('Y-m-d', strtotime("next {$weekday}")), $surge->start_time, $surge->end_time];
            }

            return;
        }

        $period = CarbonPeriod::create($surge->start_date, $surge->end_date);

        foreach ($period as $date) {
            if ($weekdays !== null && ! in_array($date->format('l'), $weekdays, true)) {
                continue;
            }
            yield [$date->format('Y-m-d'), $surge->start_time, $surge->end_time];
        }
    }

    /**
     * A permanent weekly rule occupies its weekday forever, so nothing else may
     * claim an overlapping window on that weekday.
     */
    private function guardAgainstPermanentWeekly(SurgePrice $surge, string $weekday, string $startTime, string $endTime): void
    {
        $conflict = DB::table('surge_prices')
            ->where('zone_id', $surge->zone_id)
            ->where('duration_type', 'weekly')
            ->where('is_permanent', 1)
            ->where('id', '!=', $surge->id)
            ->whereJsonContains('weekly_days', $weekday)
            ->where(fn ($q) => $this->applyOverlapPredicate($q, $startTime, $endTime))
            ->exists();

        if ($conflict) {
            throw new SurgeConflictException(
                "Time conflict: a permanent weekly surge already covers {$weekday} in this zone."
            );
        }
    }

    private function guardAgainstMaterialisedDates(SurgePrice $surge, string $dateStr, string $startTime, string $endTime): void
    {
        $conflict = DB::table('surge_price_dates')
            ->where('zone_id', $surge->zone_id)
            ->where('applicable_date', $dateStr)
            ->where('surge_price_id', '!=', $surge->id)
            ->where(fn ($q) => $this->applyOverlapPredicate($q, $startTime, $endTime))
            ->exists();

        if ($conflict) {
            throw new SurgeConflictException("Time conflict on {$dateStr} in this zone.");
        }
    }

    /**
     * Canonical half-open interval overlap: two windows collide when each
     * starts before the other ends. Windows that merely touch (one ends
     * exactly when the next begins) do not collide.
     */
    private function applyOverlapPredicate($query, string $startTime, string $endTime)
    {
        return $query->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);
    }

    /*
    |--------------------------------------------------------------------------
    | Parsing helpers
    |--------------------------------------------------------------------------
    */

    /** @return array{0:string,1:string}|null */
    private function parseDateRange($value): ?array
    {
        if (! $value || ! str_contains($value, ' - ')) {
            return null;
        }

        [$start, $end] = explode(' - ', $value, 2);
        $start = strtotime(trim($start));
        $end = strtotime(trim($end));

        if (! $start || ! $end) {
            return null;
        }

        return [date('Y-m-d', $start), date('Y-m-d', $end)];
    }

    /** @return array{0:string,1:string}|null */
    private function parseTimeRange($value): ?array
    {
        if (! $value || ! str_contains($value, ' - ')) {
            return null;
        }

        [$start, $end] = explode(' - ', $value, 2);
        $start = strtotime(trim($start));
        $end = strtotime(trim($end));

        if (! $start || ! $end) {
            return null;
        }

        return [date('H:i:s', $start), date('H:i:s', $end)];
    }

    /**
     * The form posts weekly_days / custom_days / custom_times as one
     * comma-joined string built in JS.
     *
     * @return array<int, string>
     */
    private function splitList($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value), fn ($v) => $v !== ''));
        }

        if (! $value) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value)), fn ($v) => $v !== ''));
    }

    /*
    |--------------------------------------------------------------------------
    | Responses
    |--------------------------------------------------------------------------
    */

    private function validationFailed(Request $request, $validator)
    {
        if ($request->ajax()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        return back()->withErrors($validator)->withInput();
    }

    private function successResponse(Request $request, string $message)
    {
        if ($request->ajax()) {
            return response()->json(['status' => true, 'message' => $message]);
        }

        Toastr::success($message);

        return redirect()->route('admin.surge-price.list');
    }

    private function conflictResponse(Request $request, SurgeConflictException $e)
    {
        if ($request->ajax()) {
            return response()->json([
                'status' => false,
                'type' => 'conflict',
                'message' => $e->getMessage(),
            ], 409);
        }

        Toastr::error($e->getMessage());

        return back()->withInput();
    }

    private function serverErrorResponse(Request $request, \Exception $e)
    {
        if ($request->ajax()) {
            return response()->json([
                'status' => false,
                'message' => 'Server Error: ' . $e->getMessage(),
            ], 500);
        }

        Toastr::error($e->getMessage());

        return back()->withInput();
    }
}
