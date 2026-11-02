<?php

namespace App\Traits;

/**
 * The enrolment state machine shared by BOGO and Happy Hour, on both panels and in the app.
 *
 * Both features hang a restaurant off a promotion through the same pivot shape - a status, a
 * requested_by and a rejected_by - so what each side is allowed to do next is the same
 * question in both, and it is answered here once. The panels read it to decide which buttons
 * to render; the API returns it so the app renders the same buttons without re-implementing
 * the rules.
 *
 * Two directions of request share the pivot and are told apart by requested_by:
 *   restaurant -> pending, the admin decides       (vendor sees "Pending")
 *   admin      -> pending, the restaurant decides  (vendor sees "Admin Requested")
 *
 * The side that raised a request never also decides it, and the side that did not raise it
 * never edits it - it answers, or it says no and then reworks it. So:
 *
 *   state             restaurant                        admin
 *   not_joined        join                              add restaurant
 *   admin_requested   approve, reject                   cancel
 *   pending           cancel                            approve, edit & approve, reject
 *   approved          edit & resubmit, leave            delete
 *   rejected          edit & resubmit, cancel           edit & resubmit, cancel
 *
 * Every delete is total - there is no dormant row - so rejoining after a leave or a cancel is
 * always a brand-new request. Edit & Resubmit exists for BOGO only: Happy Hour has no food
 * selection to rework, so a denied restaurant cancels and joins again.
 */
trait HandlesPromotionEnrollment
{
    /**
     * One word for what each side is looking at, so a client never has to combine status and
     * requested_by itself and get the admin-requested case wrong.
     */
    protected function enrollmentState($enrollment): string
    {
        if (! $enrollment) {
            return 'not_joined';
        }

        if ($enrollment->status === 'pending') {
            return $enrollment->requested_by === 'admin' ? 'admin_requested' : 'pending';
        }

        return $enrollment->status;
    }

    /**
     * What this restaurant may do right now. Mirrors the vendor panel's buttons exactly.
     *
     * $supportsResubmit is false for Happy Hour: there is no selection to rework.
     *
     * can_cancel and can_leave hit the same endpoint and both delete the row - they are
     * separate flags because they are separate words to the vendor. Withdrawing a request
     * that never went live is a cancel; walking away from a running promotion is a leave.
     */
    protected function enrollmentActions($enrollment, bool $isEligible, bool $supportsResubmit = true): array
    {
        $state = $this->enrollmentState($enrollment);

        return [
            'can_join' => $state === 'not_joined' && $isEligible,
            // An admin request is answered as it stands, never edited: the restaurant that
            // wants different foods rejects it first and then reworks it.
            'can_respond' => $state === 'admin_requested',
            'can_resubmit' => $supportsResubmit
                && in_array($state, ['approved', 'rejected'], true)
                && $isEligible,
            'can_cancel' => in_array($state, ['pending', 'rejected'], true),
            'can_leave' => $state === 'approved',
        ];
    }

    /**
     * Why the restaurant may not delete this row, or null when it may. An unanswered admin
     * request is declined rather than deleted: dropping the row would discard what the admin
     * chose and leave no record of the refusal.
     */
    protected function restaurantDeleteBlockedReason($enrollment): ?string
    {
        if (! $enrollment) {
            return translate('messages.not_found');
        }

        if ($this->enrollmentState($enrollment) === 'admin_requested') {
            return translate('messages.respond_to_the_admin_request_instead_of_leaving');
        }

        return null;
    }

    /**
     * Only an admin-initiated pending row can be answered - a restaurant cannot approve a
     * request it raised itself.
     */
    protected function respondBlockedReason($enrollment, string $notPendingMessage): ?string
    {
        if (! $enrollment || $this->enrollmentState($enrollment) !== 'admin_requested') {
            return $notPendingMessage;
        }

        return null;
    }

    /**
     * The restaurant reworks its own selection: after a denial, or to change what is already
     * running. An approved row goes back to pending and stops applying until the admin
     * approves the new selection - only approved enrolments are live.
     */
    protected function restaurantResubmitBlockedReason($enrollment): ?string
    {
        if (! $enrollment) {
            return translate('messages.not_found');
        }

        return in_array($this->enrollmentState($enrollment), ['approved', 'rejected'], true)
            ? null
            : translate('messages.only_a_rejected_or_running_enrolment_can_be_edited');
    }

    /**
     * The admin decides only what the restaurant asked for. Its own request waits on the
     * restaurant, and a decided row is reworked or cancelled rather than re-decided.
     */
    protected function adminDecisionBlockedReason($enrollment): ?string
    {
        if (! $enrollment) {
            return translate('messages.not_found');
        }

        return match ($this->enrollmentState($enrollment)) {
            'pending' => null,
            'admin_requested' => translate('messages.this_request_is_waiting_on_the_restaurant'),
            default => translate('messages.only_a_pending_request_can_be_approved_or_rejected'),
        };
    }

    /**
     * The admin reworks a denied enrolment. It goes back out as an admin request, so the
     * restaurant still gets the last word on foods it did not choose.
     */
    protected function adminResubmitBlockedReason($enrollment): ?string
    {
        if (! $enrollment) {
            return translate('messages.not_found');
        }

        return $this->enrollmentState($enrollment) === 'rejected'
            ? null
            : translate('messages.only_a_rejected_enrolment_can_be_resubmitted');
    }

    /**
     * Why the admin may not delete this row, or null when it may. A restaurant's pending
     * request is rejected, never deleted: the restaurant is owed the refusal and its reason.
     */
    protected function adminRemoveBlockedReason($enrollment): ?string
    {
        if (! $enrollment) {
            return translate('messages.not_found');
        }

        if ($this->enrollmentState($enrollment) === 'pending') {
            return translate('messages.reject_the_request_instead_of_removing_it');
        }

        return null;
    }

    /**
     * Rejecting records who said no. requested_by cannot answer that once both sides can
     * resubmit, since a resubmit flips the direction.
     */
    protected function markRejected($enrollment, string $by, ?string $reason): void
    {
        $enrollment->status = 'rejected';
        $enrollment->rejected_by = $by;
        // Trimmed so a field of spaces stores as nothing rather than as whitespace.
        $enrollment->rejection_reason = $reason ? trim($reason) : null;
        $enrollment->save();
    }

    /**
     * The words the panels put on screen for a state, so the app says them too.
     *
     * enrollmentState() answers in slugs, which is what a client branches on - but both
     * panels then turn that slug into a phrase, and the app was left to invent its own. Two
     * screens describing one enrolment in two vocabularies is a support call, so the phrase
     * is decided here and travels with the slug.
     *
     * $expired takes over the whole label rather than qualifying it: what the enrolment had
     * got to is a live negotiation, and reading one beside a finished promotion had the
     * vendor chasing something that was over.
     */
    protected function enrollmentStateLabel($enrollment, bool $expired = false): string
    {
        if ($expired) {
            return translate('messages.Expired');
        }

        return match ($this->enrollmentState($enrollment)) {
            'not_joined' => translate('messages.Not_Join_Yet'),
            'admin_requested' => translate('messages.Admin_Requested'),
            'pending' => translate('messages.Pending'),
            'approved' => translate('messages.Approved'),
            'rejected' => translate('messages.Rejected'),
            default => translate($enrollment->status),
        };
    }

    /**
     * Who said no, and why, in the one line the panels show on a denied row.
     *
     * The two directions land in the same state and need different words: a denial the
     * restaurant issued itself must not read as the admin's. $promotion only decides how the
     * restaurant's own refusal is worded - "You declined this offer" against "You declined
     * this happy hour" - since the admin's reads the same either way.
     */
    protected function enrollmentRejectionNote($enrollment, string $promotion = 'bogo'): ?string
    {
        if (! $enrollment || $this->enrollmentState($enrollment) !== 'rejected') {
            return null;
        }

        $who = $enrollment->rejected_by === 'restaurant'
            ? ($promotion === 'happy_hour'
                ? translate('messages.You_declined_this_happy_hour')
                : translate('messages.You_declined_this_offer'))
            : translate('messages.Denied_by_admin');

        return trim($who.($enrollment->rejection_reason ? ' : '.$enrollment->rejection_reason : ''));
    }

    /**
     * The promotion's name, for the sentences that talk about it.
     *
     * Returned as the replacement array translate() takes, so a caller writes
     * translate('messages.The_offer_has_ended.', $label) and the one string serves both
     * features - "The BOGO offer has ended" and "The Happy Hour has ended" - instead of the
     * two of them sharing a vague "offer" or, worse, wording it differently each time.
     *
     * @return array{promotion: string}
     */
    protected function promotionLabel(string $promotion): array
    {
        return ['promotion' => $promotion === 'happy_hour'
            ? translate('messages.Happy_Hour')
            : translate('messages.BOGO_offer')];
    }

    /**
     * The store side of "can a customer see anything this restaurant is running".
     *
     * Everything here is a condition on the STORE rather than on the promotion, so both
     * features ask it - but they do not ask the same question about a closed store, which is
     * why that one is a parameter:
     *
     *   BOGO       hides the offer outright while the store is closed, because
     *              BogoOfferCatalog::atServableStore() filters on openNow().
     *   Happy Hour does not - the discount rides on the store card, and a closed store is
     *              still listed - so reporting it would name a problem that is not one.
     *
     * @return string[]
     */
    protected function promotionStoreProblems($restaurant, bool $hiddenWhenClosed): array
    {
        $reasons = [];

        if (! $restaurant->status) {
            $reasons[] = translate('messages.The_restaurant_is_switched_off.');
        } elseif (! $this->restaurantHasOrderAllowance($restaurant)) {
            // Restaurant::active() - which every customer-side query runs the store through -
            // also asks this, and so does Food::active() for each item. Without it the panel
            // blamed the food instead: a store out of subscription orders had every one of
            // its foods reported "currently unavailable", which sent the restaurant looking
            // at a menu that was perfectly fine.
            $reasons[] = translate('messages.The_restaurants_subscription_has_no_orders_left.');
        }

        if ($hiddenWhenClosed && ! $this->restaurantIsOpenNow($restaurant)) {
            $reasons[] = translate('messages.The_restaurant_is_closed_right_now.');
        }

        return $reasons;
    }

    /**
     * The row-level twin of the subscription half of Restaurant::scopeActive().
     *
     * A commission store is always allowed to take orders; a subscribed one only while its
     * package still has orders on it.
     */
    protected function restaurantHasOrderAllowance($restaurant): bool
    {
        if ($restaurant->restaurant_model === 'commission') {
            return true;
        }

        $subscription = $restaurant->restaurant_sub;

        return $subscription
            && ($subscription->max_order === 'unlimited' || (int) $subscription->max_order > 0);
    }

    /**
     * The row-level twin of Restaurant::scopeOpenNow(), for a store already in hand.
     *
     * The scope is a query filter and cannot be asked of a single model, and the callers need
     * the answer for exactly one store - so the same three conditions are read off the row
     * rather than issuing a query that returns the store or nothing.
     */
    protected function restaurantIsOpenNow($restaurant): bool
    {
        if (! $restaurant->active) {
            return false;
        }

        $offDays = array_filter(array_map('trim', explode(',', (string) $restaurant->off_day)), 'strlen');

        if (in_array((string) now()->dayOfWeek, $offDays, true)) {
            return false;
        }

        return $restaurant->schedules()
            ->where('day', now()->dayOfWeek)
            ->where('opening_time', '<', now()->format('H:i:s'))
            ->where('closing_time', '>', now()->format('H:i:s'))
            ->exists();
    }

    /**
     * One word for what a customer would see of this enrolment right now, and the sentences
     * behind it - the answer both panels draw a badge from and the app renders as its own.
     *
     * Deliberately separate from enrollmentState(): that says where the request stands
     * between the admin and the restaurant, which is a different question and was the only
     * one either side could answer. "Approved" is not "running".
     *
     *   not_applicable - not approved, so there is nothing to be visible yet; the enrolment
     *                    state is the whole story and reasons stay empty
     *   ended          - the promotion's own window is behind us
     *   scheduled      - nothing is wrong, it is simply not this promotion's turn yet
     *   not_visible    - approved and in date, and a customer still cannot see it
     *   running        - a customer can see it right now
     *
     * @param  string[]  $reasons
     * @return array{status: string, label: string|null, reasons: string[]}
     */
    protected function promotionVisibility(string $status, array $reasons = []): array
    {
        return [
            'status' => $status,
            'label' => match ($status) {
                'running' => translate('messages.Running_now'),
                'scheduled' => translate('messages.Scheduled'),
                'ended' => translate('messages.Ended'),
                'not_visible' => translate('messages.Not_visible_to_customers'),
                default => null,
            },
            'reasons' => array_values($reasons),
        ];
    }
}
