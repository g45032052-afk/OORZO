<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The notification a restaurant gets when an offer it had joined is deleted.
 *
 * Withdrawal used to reuse the "a new BOGO is available" broadcast, so deleting an offer told
 * every restaurant in every zone to enrol in it while the ones that had actually lost it heard
 * nothing. This seeds the pieces a real withdrawal notice needs, the same three the join keys
 * use: the toggle row the vendor sees, the per-restaurant setting, and the push body itself -
 * without which getPushNotificationMessage() returns null and the send bails even with the
 * toggle on.
 */
return new class extends Migration
{
    private string $key = 'restaurant_bogo_offer_withdrawn';

    private string $subTitle = 'Get_notification_when_a_bogo_offer_is_withdrawn';

    private string $message = '{restaurantName} A BOGO offer you had joined has been withdrawn by the admin';

    public function up(): void
    {
        $now = now();

        DB::table('notification_settings')->updateOrInsert(
            ['key' => $this->key, 'type' => 'restaurant'],
            [
                'title' => $this->key,
                'sub_title' => 'Sent_notification_when_a_bogo_offer_is_withdrawn',
                'mail_status' => 'active',
                'sms_status' => 'disable',
                'push_notification_status' => 'active',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('notification_messages')->updateOrInsert(
            ['key' => $this->key, 'user_type' => 'restaurant'],
            ['message' => $this->message, 'status' => 1, 'updated_at' => $now, 'created_at' => $now]
        );

        // Only ever inserted, never updated: a toggle the admin has turned off is not the
        // seeder's to switch back on if this runs twice.
        $existing = DB::table('restaurant_notification_settings')
            ->where('key', $this->key)
            ->pluck('restaurant_id')
            ->flip();

        $insert = [];

        foreach (DB::table('restaurants')->pluck('id') as $restaurantId) {
            if ($existing->has($restaurantId)) {
                continue;
            }

            $insert[] = [
                'restaurant_id' => $restaurantId,
                'key' => $this->key,
                'title' => $this->key,
                'sub_title' => $this->subTitle,
                'mail_status' => 'active',
                'sms_status' => 'disable',
                'push_notification_status' => 'active',
                'updated_at' => $now,
                'created_at' => $now,
            ];
        }

        foreach (array_chunk($insert, 500) as $chunk) {
            DB::table('restaurant_notification_settings')->insert($chunk);
        }
    }

    public function down(): void
    {
        DB::table('notification_settings')->where('key', $this->key)->where('type', 'restaurant')->delete();
        DB::table('notification_messages')->where('key', $this->key)->where('user_type', 'restaurant')->delete();
        DB::table('restaurant_notification_settings')->where('key', $this->key)->delete();
    }
};
