<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Makes the BOGO and Happy Hour approve/reject pushes actually reach the vendor app.
 *
 * The earlier seeders filled notification_settings, which is the admin-facing toggle list.
 * The send path does not read that table. Every restaurant-bound push is gated on two others:
 *
 *   Helpers::getRestaurantNotificationStatusData($restaurantId, $key)  -> restaurant_notification_settings
 *   Helpers::getPushNotificationMessage($key, 'restaurant', ...)       -> notification_messages
 *
 * Of those two, only the second could block anything. The first heals itself - when the row is
 * missing, getRestaurantNotificationStatusData() rebuilds it from
 * NotificationDataSetUpTrait::getRestaurantNotificationSetupData(), which carries these eight
 * keys. notification_messages has no such fallback, and it held no row for any promotion key.
 *
 * So the four approve/reject sends, which are guarded on
 *
 *   $pushStatus?->push_notification_status == 'active' && $message
 *
 * had $message null every time and never reached send_push_notif_to_device(). Nothing errored;
 * the guard simply never opened. The two invitation sends were unaffected - they fall back to
 * a translate() string when the message row is missing - but they are seeded here too so all
 * six read from one place and are editable and translatable like every other push.
 *
 * {restaurantName} is the placeholder Helpers::text_variable_data_format() substitutes.
 */
return new class extends Migration
{
    /**
     * [key, per-restaurant sub_title, push message]
     *
     * A null message means the key gates mail only - the restaurant's own join request is
     * acknowledged by mail, never by push - so it gets a toggle row but no message body.
     * Seeding one would put an editable field on the push-messages screen that nothing sends.
     */
    private array $rows = [
        [
            'restaurant_bogo_join_request',
            'Get_notification_on_bogo_join_request',
            null,
        ],
        [
            'restaurant_bogo_join_approval',
            'Get_notification_on_bogo_join_approval',
            '{restaurantName} Your BOGO offer request has been approved',
        ],
        [
            'restaurant_bogo_join_rejection',
            'Get_notification_on_bogo_join_rejection',
            '{restaurantName} Your BOGO offer request has been denied',
        ],
        [
            'restaurant_bogo_invitation',
            'Get_notification_when_the_admin_assigns_a_bogo_offer',
            '{restaurantName} The admin has invited you to join a BOGO offer. Review and approve the request to participate',
        ],
        [
            'restaurant_happy_hour_join_request',
            'Get_notification_on_happy_hour_join_request',
            null,
        ],
        [
            'restaurant_happy_hour_join_approval',
            'Get_notification_on_happy_hour_join_approval',
            '{restaurantName} Your Happy Hour request has been approved',
        ],
        [
            'restaurant_happy_hour_join_rejection',
            'Get_notification_on_happy_hour_join_rejection',
            '{restaurantName} Your Happy Hour request has been denied',
        ],
        [
            'restaurant_happy_hour_invitation',
            'Get_notification_when_the_admin_assigns_a_happy_hour',
            '{restaurantName} The admin has invited you to join a Happy Hour. Review and approve the request to participate',
        ],
    ];

    public function up(): void
    {
        $now = now();

        // The push body. Without a row here getPushNotificationMessage() returns null and the
        // approve/reject sends bail even when the toggle is on. Mail-only keys are skipped.
        foreach ($this->rows as [$key, , $message]) {
            if ($message === null) {
                continue;
            }

            DB::table('notification_messages')->updateOrInsert(
                ['key' => $key, 'user_type' => 'restaurant'],
                ['message' => $message, 'status' => 1, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        // The per-restaurant toggle, for every restaurant that already exists.
        //
        // Missing rows are only ever inserted, never updated: updateOrInsert() would write its
        // values over a row that is already there, so a re-run - or a second deploy - would
        // silently switch a toggle the admin had turned off back on. A setting the admin owns
        // is not the seeder's to reset.
        //
        // This is a convenience rather than the fix. getRestaurantNotificationStatusData()
        // already rebuilds a missing row from getRestaurantNotificationSetupData() on first
        // use, so the toggle heals itself; seeding here just means the rows are on the
        // vendor's notification screen before the first send rather than after it.
        $existing = DB::table('restaurant_notification_settings')
            ->whereIn('key', array_column($this->rows, 0))
            ->get(['restaurant_id', 'key'])
            ->map(fn ($row) => $row->restaurant_id.'|'.$row->key)
            ->flip();

        $insert = [];

        foreach (DB::table('restaurants')->pluck('id') as $restaurantId) {
            foreach ($this->rows as [$key, $subTitle]) {
                if ($existing->has($restaurantId.'|'.$key)) {
                    continue;
                }

                $insert[] = [
                    'restaurant_id' => $restaurantId,
                    'key' => $key,
                    'title' => $key,
                    'sub_title' => $subTitle,
                    'mail_status' => 'active',
                    'sms_status' => 'disable',
                    'push_notification_status' => 'active',
                    'updated_at' => $now,
                    'created_at' => $now,
                ];
            }
        }

        foreach (array_chunk($insert, 500) as $chunk) {
            DB::table('restaurant_notification_settings')->insert($chunk);
        }
    }

    public function down(): void
    {
        $keys = array_column($this->rows, 0);

        DB::table('notification_messages')->whereIn('key', $keys)->where('user_type', 'restaurant')->delete();
        DB::table('restaurant_notification_settings')->whereIn('key', $keys)->delete();
    }
};
