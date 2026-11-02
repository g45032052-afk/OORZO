<?php

namespace App\Traits;

use App\CentralLogics\HappyHourScheduleLogic;
use App\CentralLogics\Helpers;
use App\Models\Admin;
use App\Models\HappyHour;
use App\Models\HappyHourRestaurant;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Mail;

/**
 * Happy Hour enrolment rules shared by the vendor panel and the vendor app.
 *
 * There is no food selection here - joining is one click - so the whole surface is: see the
 * happy hours for my zone, join one, answer one the admin assigned me, or leave. What the
 * two callers must agree on is when a happy hour is joinable and who gets told about it.
 */
trait HandlesHappyHourEnrollment
{
    use HandlesPromotionEnrollment;

    /**
     * Why this restaurant cannot join, or null when it can. A happy hour reaches a
     * restaurant only through its zone, so an out-of-zone id is not found rather than
     * refused.
     */
    protected function happyHourJoinBlockedReason(?HappyHour $happyHour): ?array
    {
        if (! $happyHour) {
            return ['code' => 'happy_hour', 'message' => translate('messages.not_found')];
        }

        if (! $happyHour->status) {
            return ['code' => 'happy_hour', 'message' => translate('messages.this_offer_is_not_running')];
        }

        // hasEnded() rather than end_date: a custom schedule keeps its days in
        // happy_hour_dates and leaves end_date null, so this guard could never see one.
        if ($happyHour->hasEnded()) {
            return ['code' => 'happy_hour', 'message' => translate('messages.this_offer_has_already_ended')];
        }

        return null;
    }

    /**
     * Why this restaurant cannot join on top of what it is already in, or null.
     *
     * Two happy hours the same restaurant is in must not run at the same time - the discount
     * is store-wide, so an overlap means two different discounts claiming one basket, and
     * get_running_happy_hour() would settle it by whichever row the database returned first.
     * The admin side keeps a zone's offers from overlapping, which covers this for a store
     * that stays put; a store moved between zones carries enrolments that rule never
     * compared, so the restaurant's own set is checked here rather than assumed.
     *
     * Only pending and approved enrolments count. A rejected row is not a membership - the
     * restaurant is not in that offer and joining an overlapping one takes nothing away.
     *
     * Run at the join itself rather than inside happyHourJoinBlockedReason(): that one is
     * asked for every row of a list to decide whether the Join button shows, and this costs
     * queries that a listing should not pay for something the zone rule already prevents.
     */
    protected function happyHourOverlapBlockedReason(HappyHour $happyHour, int $restaurantId): ?array
    {
        $committedIds = HappyHourRestaurant::where('restaurant_id', $restaurantId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('happy_hour_id', '!=', $happyHour->id)
            ->pluck('happy_hour_id')
            ->all();

        $clash = HappyHourScheduleLogic::firstClash($happyHour, $committedIds);

        if (! $clash) {
            return null;
        }

        return [
            'code' => 'overlap',
            // Names the offer and the window in the way, so the vendor knows what to leave
            // instead of being told only that something collided.
            'message' => translate('messages.this_happy_hour_overlaps_an_offer_you_are_already_in', [
                'title' => $clash['happy_hour']->title,
                'window' => $clash['window'],
            ]),
        ];
    }

    protected function notifyAdminOfHappyHourJoinRequest(int $restaurantId): void
    {
        try {
            $restaurant = Restaurant::with('vendor')->find($restaurantId);
            $admin = Admin::where('role_id', 1)->first();

            $adminStatus = Helpers::getNotificationStatusData('admin', 'happy_hour_join_request');

            if ($adminStatus?->mail_status == 'active' && config('mail.status')
                && Helpers::get_mail_status('happy_hour_request_mail_status_admin') == '1' && $admin) {
                Mail::to($admin->getRawOriginal('email'))
                    ->send(new \App\Mail\HappyHourRequestMail($restaurant?->name));
            }

            $vendorStatus = Helpers::getNotificationStatusData('restaurant', 'restaurant_happy_hour_join_request');
            $restaurantStatus = Helpers::getRestaurantNotificationStatusData($restaurantId, 'restaurant_happy_hour_join_request');

            if ($vendorStatus?->mail_status == 'active' && $restaurantStatus?->mail_status == 'active'
                && config('mail.status')
                && Helpers::get_mail_status('happy_hour_request_mail_status_restaurant') == '1'
                && $restaurant?->vendor) {
                Mail::to($restaurant->vendor->getRawOriginal('email'))
                    ->send(new \App\Mail\VendorHappyHourRequestMail($restaurant->name, 'pending'));
            }
        } catch (\Exception $e) {
            // Never let a mail failure undo a saved enrolment.
            info(['happy_hour_join_notify' => $e->getMessage()]);
        }
    }

    protected function notifyAdminOfHappyHourDenial(int $restaurantId): void
    {
        $this->notifyAdminOfHappyHourDecision($restaurantId, 'denied');
    }

    /**
     * The other half of the same answer. An assignment the restaurant accepts starts
     * discounting its whole menu the moment its window opens, so the admin hears about the
     * yes for the same reason it hears about the no - only the no was ever sent, which left
     * silence meaning either "not answered yet" or "running since Tuesday".
     */
    protected function notifyAdminOfHappyHourAcceptance(int $restaurantId): void
    {
        $this->notifyAdminOfHappyHourDecision($restaurantId, 'accepted');
    }

    /**
     * Why an approved happy hour is not discounting anything for customers right now, or an
     * empty list when it is.
     *
     * The BOGO twin of this reproduces the offer endpoints' own filters; this one reproduces
     * the single place a happy hour reaches a customer - Helpers::get_running_happy_hour(),
     * read by store_wide_discount_data() for the store banner and by food_discount_calculate()
     * for every price - so what the vendor is told and what the customer is charged come from
     * one reading of the same rule:
     *
     *   HappyHour::active()               - switched on
     *   ->where('zone_id', ...)           - the store's own zone
     *   ->whereHas('enrollments' approved)- this enrolment
     *   ->isRunningNow()                  - the window is open, which is the STATUS rather
     *                                       than a fault, so it is not listed here
     *   discount > 0                      - store_wide_discount_data() drops a zero rate
     *
     * The store being closed is deliberately not asked. A happy hour is a rate on a store
     * card, and a closed store is still listed and still shows it - unlike a BOGO offer,
     * which its own endpoints hide outright.
     *
     * @return string[] plain sentences, most decisive first
     */
    public function happyHourVisibilityProblems(?HappyHourRestaurant $enrollment, ?HappyHour $happyHour = null, ?Restaurant $restaurant = null): array
    {
        $happyHour ??= $enrollment?->happy_hour;
        $restaurant ??= $enrollment?->restaurant;
        $label = $this->promotionLabel('happy_hour');
        $reasons = [];

        if (! $happyHour) {
            return [translate('messages.This_offer_no_longer_exists.', $label)];
        }

        if (! $happyHour->status) {
            $reasons[] = translate('messages.The_offer_is_switched_off.', $label);
        }

        if ($happyHour->start_date && $happyHour->start_date->isFuture()) {
            $reasons[] = translate('messages.The_offer_has_not_started_yet.', $label)
                .' ('.translate('messages.Starts').' '.$happyHour->start_date->format('d M, Y').')';
        }

        if ($happyHour->hasEnded()) {
            // The day it finished is the end date when it has one and the last of its
            // hand-picked days when it does not, so a custom schedule names its own last day.
            $endedOn = $happyHour->endsOn();

            $reasons[] = translate('messages.The_offer_has_ended.', $label)
                .($endedOn ? ' ('.translate('messages.Ended').' '.$endedOn->format('d M, Y').')' : '');
        }

        // Nothing comes off the bill at zero, so the rate is dropped before it ever reaches a
        // store card - the customer would otherwise see a badge advertising no saving.
        if ((float) $happyHour->discount <= 0) {
            $reasons[] = translate('messages.The_discount_is_set_to_zero_so_customers_see_nothing_taken_off.');
        }

        if (! $restaurant) {
            $reasons[] = translate('messages.The_restaurant_no_longer_exists.');

            return $reasons;
        }

        // A happy hour reaches a restaurant through its zone only. The two part company when
        // the admin moves the store afterwards, and the enrolment survives the move - so the
        // restaurant keeps an approved happy hour that can never run for it again.
        if ((int) $happyHour->zone_id !== (int) $restaurant->zone_id) {
            $reasons[] = translate('messages.The_offer_runs_in_a_different_zone_than_this_restaurant.', $label);
        }

        // Closed is not asked: see the note above.
        return array_merge($reasons, $this->promotionStoreProblems($restaurant, hiddenWhenClosed: false));
    }

    /**
     * What a customer would see of this enrolment right now - the happy hour twin of
     * bogoCustomerVisibility(), answering in the same words so the app renders one badge for
     * both features.
     *
     * @return array{status: string, label: string|null, reasons: string[]}
     */
    public function happyHourCustomerVisibility(?HappyHourRestaurant $enrollment, ?HappyHour $happyHour = null, ?Restaurant $restaurant = null): array
    {
        $happyHour ??= $enrollment?->happy_hour;

        if (! $enrollment || $enrollment->status !== 'approved' || ! $happyHour) {
            return $this->promotionVisibility('not_applicable');
        }

        $reasons = $this->happyHourVisibilityProblems($enrollment, $happyHour, $restaurant);

        // Between windows is the normal state of a happy hour - it runs an hour a day - so it
        // is reported as scheduled rather than as something to fix. Ended and not-yet-started
        // come off the dates for the same reason they do on the BOGO side.
        $status = match (true) {
            $happyHour->hasEnded() => 'ended',
            (bool) ($happyHour->start_date && $happyHour->start_date->isFuture()) => 'scheduled',
            (bool) $reasons => 'not_visible',
            $happyHour->isRunningNow() => 'running',
            default => 'scheduled',
        };

        return $this->promotionVisibility($status, $reasons);
    }

    private function notifyAdminOfHappyHourDecision(int $restaurantId, string $decision): void
    {
        try {
            $restaurant = Restaurant::find($restaurantId);
            $admin = Admin::where('role_id', 1)->first();

            // The mail flag name is not free: the settings screen derives it from the tab slug
            // as <tab with dashes as underscores>_mail_status_<type>, so happy-hour-approve
            // for the admin can only read happy_hour_approve_mail_status_admin.
            [$notificationKey, $mailKey] = $decision === 'denied'
                ? ['happy_hour_join_denied', 'happy_hour_deny_mail_status_admin']
                : ['happy_hour_join_accepted', 'happy_hour_approve_mail_status_admin'];

            $status = Helpers::getNotificationStatusData('admin', $notificationKey);

            if ($status?->mail_status == 'active' && config('mail.status')
                && Helpers::get_mail_status($mailKey) == '1' && $admin) {
                Mail::to($admin->getRawOriginal('email'))
                    ->send(new \App\Mail\HappyHourRequestMail($restaurant?->name, $decision));
            }
        } catch (\Exception $e) {
            info(['happy_hour_decision_notify' => $e->getMessage()]);
        }
    }
}
