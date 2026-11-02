<?php

namespace Modules\AI\app\Agents\Tools;

use App\CentralLogics\Helpers;
use App\Services\HappyHour\HappyHourCatalog;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\AI\app\Agents\AiResponseContext;

/**
 * Happy hour: a scheduled window during which a restaurant takes a percentage off its
 * whole menu.
 *
 * Distinct from the other three things the platform calls a discount — an item's own
 * discount, the admin's standing restaurant discount, and a campaign price — and only one
 * of them ever applies to a given item. Reads through HappyHourCatalog, the same service
 * /api/v1/happy-hour answers from, because a window reaches a customer only through an
 * approved enrolment in their own zone and whether it is live can only be decided in PHP.
 */
class GetHappyHourTool implements Tool
{
    public function __construct(
        private readonly AiResponseContext $context,
        private readonly array             $zoneIds   = [],
        private readonly ?float            $latitude  = null,
        private readonly ?float            $longitude = null,
    ) {}

    public function description(): string
    {
        return 'Get happy hour information — whether one is running right now in the customer\'s area, and which restaurants take part. Use for "happy hour", "is there a happy hour", "any discounts on now", "which restaurants have offers today". A happy hour is a scheduled window with a percentage off a restaurant\'s whole menu, NOT a per-item discount and NOT a BOGO bundle.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'running_only' => $schema->boolean()->description('true for only the restaurants whose window is open right now, false or null for every restaurant taking part')->required()->nullable(),
            'limit'        => $schema->number()->description('How many restaurants to return, default 6, or null')->required()->nullable(),
        ];
    }

    public function handle(Request $request): string
    {
        $this->context->recordTool('GetHappyHourTool');

        $args        = $request->all();
        $runningOnly = (bool) ($args['running_only'] ?? false);
        $limit       = min((int) ($args['limit'] ?? 6), 10);

        if (! $this->zoneIds) {
            return 'No delivery zone is known for this chat session, so happy hours cannot be looked up.';
        }

        $catalog = app(HappyHourCatalog::class);
        $running = $catalog->runningIn($this->zoneIds);

        if ($running) {
            $card = $catalog->happyHourPayload($running) + $catalog->windowPayload($running, $this->zoneIds);
            $this->context->addHappyHours([$card]);
        }

        $query = $catalog->storesQuery($this->zoneIds, $this->longitude, $this->latitude);
        if ($runningOnly) {
            $query = $catalog->restrictToLive($query, $this->zoneIds, $this->longitude, $this->latitude);
        }
        $stores = $query->limit($limit)->get();

        if ($stores->isEmpty()) {
            return $runningOnly
                ? 'No restaurant has a happy hour running right now in this area.'
                    .($running ? ' A happy hour is scheduled but no store is currently open in it.' : '')
                : 'No restaurant in this area takes part in a happy hour.';
        }

        // Named per store rather than for the window in general: the window is the schedule,
        // but the rate in force is the restaurant's, and it can be the admin's standing
        // discount instead when that one is larger.
        $lines = $stores->map(function ($store) use ($catalog) {
            $live = $catalog->runningHappyHour($store);
            $rate = Helpers::get_effective_restaurant_discount($store);
            $off = $rate ? ' — '.(float) $rate['discount'].'% off right now' : '';

            return $store->name.' [STORE ID:'.$store->id.']'
                .($live ? ' (happy hour live'.$off.')' : ' (enrolled, not running now)');
        })->implode(' | ');

        $headline = $running
            ? 'A happy hour is running now: "'.($running->title ?? 'Happy Hour').'".'
            : 'No happy hour window is open at this moment.';

        return $headline.' '.$stores->count().' restaurant(s): '.$lines
            .'. A happy hour takes a percentage off the restaurant\'s whole menu while the window'
            .' is open; it is not a per-item discount and not a BOGO bundle.';
    }
}
