<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Notification toggles and email templates for the BOGO join flow.
 *
 * Mirrors the four campaign rows. Without these, getNotificationStatusData() returns null
 * and every BOGO mail and push is silently skipped - the feature would look wired up and
 * simply never deliver.
 */
return new class extends Migration
{
    private array $notifications = [
        ['title' => 'bogo_join_request', 'sub_title' => 'Sent_notification_on_bogo_join_request', 'key' => 'bogo_join_request', 'type' => 'admin'],
        ['title' => 'restaurant_bogo_join_request', 'sub_title' => 'Sent_notification_on_bogo_join_request', 'key' => 'restaurant_bogo_join_request', 'type' => 'restaurant'],
        ['title' => 'restaurant_bogo_join_approval', 'sub_title' => 'Sent_notification_on_bogo_join_approval', 'key' => 'restaurant_bogo_join_approval', 'type' => 'restaurant'],
        ['title' => 'restaurant_bogo_join_rejection', 'sub_title' => 'Sent_notification_on_bogo_join_rejection', 'key' => 'restaurant_bogo_join_rejection', 'type' => 'restaurant'],
    ];

    private array $templates = [
        ['type' => 'admin', 'email_type' => 'bogo_request', 'title' => 'New BOGO join request', 'body' => 'A restaurant has requested to join a BOGO offer. Review the request from the BOGO offer details page.'],
        ['type' => 'restaurant', 'email_type' => 'bogo_request', 'title' => 'BOGO join request received', 'body' => 'Your request to join the BOGO offer has been received and is waiting for approval.'],
        ['type' => 'restaurant', 'email_type' => 'bogo_approve', 'title' => 'BOGO join request approved', 'body' => 'Your request to join the BOGO offer has been approved.'],
        ['type' => 'restaurant', 'email_type' => 'bogo_deny', 'title' => 'BOGO join request denied', 'body' => 'Your request to join the BOGO offer has been denied.'],
    ];

    public function up(): void
    {
        foreach ($this->notifications as $row) {
            DB::table('notification_settings')->updateOrInsert(
                ['key' => $row['key'], 'type' => $row['type']],
                $row + [
                    'mail_status' => 'active',
                    'sms_status' => 'disable',
                    'push_notification_status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        foreach ($this->templates as $row) {
            DB::table('email_templates')->updateOrInsert(
                ['type' => $row['type'], 'email_type' => $row['email_type']],
                $row + [
                    'email_template' => 1,
                    'footer_text' => 'Thank you for being with us.',
                    'copyright_text' => 'Copyright',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // The format screens read these to show the per-tab mail toggle.
        foreach ([
            'bogo_request_mail_status_admin',
            'bogo_request_mail_status_restaurant',
            'bogo_approve_mail_status_restaurant',
            'bogo_deny_mail_status_restaurant',
        ] as $key) {
            DB::table('business_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => '1', 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // Business settings are cached forever behind a single key, and the model event that
        // clears it does not fire for a query-builder write - so without this the flags above
        // are invisible to get_business_settings() and every mail here is silently skipped.
        Cache::forget('business_settings_all_data');
    }

    public function down(): void
    {
        DB::table('notification_settings')->whereIn('key', array_column($this->notifications, 'key'))->delete();

        DB::table('email_templates')
            ->whereIn('email_type', ['bogo_request', 'bogo_approve', 'bogo_deny'])
            ->delete();

        DB::table('business_settings')->whereIn('key', [
            'bogo_request_mail_status_admin',
            'bogo_request_mail_status_restaurant',
            'bogo_approve_mail_status_restaurant',
            'bogo_deny_mail_status_restaurant',
        ])->delete();

        Cache::forget('business_settings_all_data');
    }
};
