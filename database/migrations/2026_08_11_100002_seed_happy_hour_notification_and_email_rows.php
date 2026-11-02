<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Happy Hour's own notification toggles and email templates, plus the "a restaurant
 * declined your invitation" pair for both promotions.
 *
 * Until now Happy Hour borrowed BOGO's bogo_join_request key, so switching BOGO
 * notifications off would have silently killed Happy Hour's, and the admin would have
 * received an email headed BOGO for a Happy Hour request.
 */
return new class extends Migration
{
    private array $notifications = [
        // Restaurant asked to join.
        ['title' => 'happy_hour_join_request', 'sub_title' => 'Sent_notification_on_happy_hour_join_request', 'key' => 'happy_hour_join_request', 'type' => 'admin'],
        // Restaurant turned down an invitation the admin sent.
        ['title' => 'happy_hour_join_denied', 'sub_title' => 'Sent_notification_when_a_restaurant_declines_a_happy_hour_invitation', 'key' => 'happy_hour_join_denied', 'type' => 'admin'],
        ['title' => 'bogo_join_denied', 'sub_title' => 'Sent_notification_when_a_restaurant_declines_a_bogo_invitation', 'key' => 'bogo_join_denied', 'type' => 'admin'],
        // The other half of that answer. Only the no was ever sent, so silence meant either
        // "not answered yet" or "accepted and running" and the admin could not tell which.
        ['title' => 'bogo_join_accepted', 'sub_title' => 'Sent_notification_when_a_restaurant_accepts_a_bogo_invitation', 'key' => 'bogo_join_accepted', 'type' => 'admin'],
        ['title' => 'happy_hour_join_accepted', 'sub_title' => 'Sent_notification_when_a_restaurant_accepts_a_happy_hour_invitation', 'key' => 'happy_hour_join_accepted', 'type' => 'admin'],

        ['title' => 'restaurant_happy_hour_join_request', 'sub_title' => 'Sent_notification_on_happy_hour_join_request', 'key' => 'restaurant_happy_hour_join_request', 'type' => 'restaurant'],
        ['title' => 'restaurant_happy_hour_join_approval', 'sub_title' => 'Sent_notification_on_happy_hour_join_approval', 'key' => 'restaurant_happy_hour_join_approval', 'type' => 'restaurant'],
        ['title' => 'restaurant_happy_hour_join_rejection', 'sub_title' => 'Sent_notification_on_happy_hour_join_rejection', 'key' => 'restaurant_happy_hour_join_rejection', 'type' => 'restaurant'],
    ];

    private array $templates = [
        ['type' => 'admin', 'email_type' => 'happy_hour_request', 'title' => 'New Happy Hour join request', 'body' => 'A restaurant has requested to join a Happy Hour. Review the request from the Happy Hour details page.'],
        ['type' => 'admin', 'email_type' => 'happy_hour_deny', 'title' => 'Happy Hour invitation declined', 'body' => 'A restaurant has declined the Happy Hour you assigned to it.'],
        ['type' => 'admin', 'email_type' => 'happy_hour_approve', 'title' => 'Happy Hour invitation accepted', 'body' => 'A restaurant has accepted the Happy Hour you assigned to it, and it is now running.'],
        ['type' => 'admin', 'email_type' => 'bogo_deny', 'title' => 'BOGO invitation declined', 'body' => 'A restaurant has declined the BOGO offer you assigned to it.'],
        ['type' => 'admin', 'email_type' => 'bogo_approve', 'title' => 'BOGO invitation accepted', 'body' => 'A restaurant has accepted the BOGO offer you assigned to it, and it is now running.'],

        ['type' => 'restaurant', 'email_type' => 'happy_hour_request', 'title' => 'Happy Hour join request received', 'body' => 'Your request to join the Happy Hour has been received and is waiting for approval.'],
        ['type' => 'restaurant', 'email_type' => 'happy_hour_approve', 'title' => 'Happy Hour join request approved', 'body' => 'Your request to join the Happy Hour has been approved.'],
        ['type' => 'restaurant', 'email_type' => 'happy_hour_deny', 'title' => 'Happy Hour join request denied', 'body' => 'Your request to join the Happy Hour has been denied.'],
    ];

    // Format screens read these to render their per-tab mail toggle.
    private array $mailFlags = [
        'happy_hour_request_mail_status_admin',
        'happy_hour_deny_mail_status_admin',
        'happy_hour_approve_mail_status_admin',
        'bogo_deny_mail_status_admin',
        'bogo_approve_mail_status_admin',
        'happy_hour_request_mail_status_restaurant',
        'happy_hour_approve_mail_status_restaurant',
        'happy_hour_deny_mail_status_restaurant',
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

        foreach ($this->mailFlags as $key) {
            DB::table('business_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => '1', 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->forgetSettingsCache();
    }

    /**
     * Every business setting is cached forever behind one key, and the model event that
     * clears it does not fire for a query-builder write. Without this the flags above stay
     * invisible until something else happens to save a setting - which reads as the mail
     * silently never being sent.
     */
    private function forgetSettingsCache(): void
    {
        Cache::forget('business_settings_all_data');
    }

    public function down(): void
    {
        DB::table('notification_settings')->whereIn('key', array_column($this->notifications, 'key'))->delete();

        // Only the rows this migration introduced; bogo_request predates it, and the
        // restaurant-side bogo_approve belongs to the BOGO seed before it.
        DB::table('email_templates')
            ->where(fn ($q) => $q->whereIn('email_type', ['happy_hour_request', 'happy_hour_approve', 'happy_hour_deny'])
                ->orWhere(fn ($q) => $q->where('type', 'admin')->whereIn('email_type', ['bogo_deny', 'bogo_approve'])))
            ->delete();

        DB::table('business_settings')->whereIn('key', $this->mailFlags)->delete();

        $this->forgetSettingsCache();
    }
};
