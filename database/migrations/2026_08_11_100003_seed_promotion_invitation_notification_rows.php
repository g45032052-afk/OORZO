<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Push keys for the moment an admin assigns a restaurant to a promotion.
 *
 * Separate from the *_join_request keys, which cover the opposite direction (a restaurant
 * asking to join). Reusing those would mean a vendor could not silence one without
 * silencing the other, and the message text is different in each direction.
 */
return new class extends Migration
{
    private array $notifications = [
        ['title' => 'restaurant_bogo_invitation', 'sub_title' => 'Sent_notification_when_the_admin_assigns_a_bogo_offer', 'key' => 'restaurant_bogo_invitation', 'type' => 'restaurant'],
        ['title' => 'restaurant_happy_hour_invitation', 'sub_title' => 'Sent_notification_when_the_admin_assigns_a_happy_hour', 'key' => 'restaurant_happy_hour_invitation', 'type' => 'restaurant'],
    ];

    public function up(): void
    {
        foreach ($this->notifications as $row) {
            DB::table('notification_settings')->updateOrInsert(
                ['key' => $row['key'], 'type' => $row['type']],
                $row + [
                    'mail_status' => 'disable',
                    'sms_status' => 'disable',
                    // Push only: the standing panel popup already covers the visual prompt,
                    // this is what makes it arrive without a page load.
                    'push_notification_status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('notification_settings')->whereIn('key', array_column($this->notifications, 'key'))->delete();
    }
};
