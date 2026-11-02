<?php

namespace App\Builder;

use App\CentralLogics\Helpers;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Http;
use Modules\Builder\Contracts\ContactProvider as ContactProviderContract;

/**
 * StackFood host adapter for ContactProvider.
 *
 * Writes storefront contact messages into the same `contact_messages` table the
 * admin landing's HomeController uses (Admin → Contact Messages). StackFood's
 * model is mass-assignable (`$guarded = ['id']`) and stores name/email/message
 * (no subject column — subject is only used for the notification email).
 *
 * Captcha mirrors the storefront SPA model: Google reCAPTCHA v3 when the host
 * has it enabled in business settings; skipped otherwise. The blade-only
 * session captcha (`six_captcha`) is intentionally not ported.
 */
class ContactProvider implements ContactProviderContract
{
    public function submit(array $payload): array
    {
        $recaptchaError = $this->verifyRecaptcha(
            $payload['recaptchaToken'] ?? null,
            $payload['ip'] ?? null,
        );
        if ($recaptchaError) {
            return ['success' => false, 'errors' => [$recaptchaError]];
        }

        try {
            ContactMessage::create([
                'name'    => (string) ($payload['name']    ?? ''),
                'email'   => (string) ($payload['email']   ?? ''),
                'message' => (string) ($payload['message'] ?? ''),
            ]);
        } catch (\Throwable) {
            return ['success' => false, 'errors' => [[
                'code'    => 'persist',
                'message' => translate('messages.failed_to_send_message') ?: 'Could not send your message.',
            ]]];
        }

        return ['success' => true];
    }

    public function recaptchaSiteKey(): ?string
    {
        $settings = Helpers::get_business_settings('recaptcha');
        if (!is_array($settings) || (int) ($settings['status'] ?? 0) !== 1) {
            return null;
        }
        $key = (string) ($settings['site_key'] ?? '');
        return $key !== '' ? $key : null;
    }

    /** Null when reCAPTCHA is off OR passes; an errors[] entry when it fails. */
    private function verifyRecaptcha(?string $token, ?string $ip): ?array
    {
        $settings = Helpers::get_business_settings('recaptcha');
        if (!is_array($settings) || (int) ($settings['status'] ?? 0) !== 1) {
            return null;
        }

        $base = translate('messages.ReCAPTCHA Failed') ?: 'ReCAPTCHA failed.';

        if (!$token) {
            return ['code' => 'recaptcha', 'message' => $base];
        }

        $secret = (string) ($settings['secret_key'] ?? '');
        if ($secret === '') {
            return ['code' => 'recaptcha', 'message' => $base];
        }

        try {
            $response = Http::asForm()->timeout(8)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => $ip ?? '',
            ]);

            if (!$response->successful()) {
                return ['code' => 'recaptcha', 'message' => $base];
            }

            $body = $response->json();
            if (($body['success'] ?? false) !== true) {
                $codes = is_array($body['error-codes'] ?? null) ? implode(', ', $body['error-codes']) : 'unknown';
                report(new \RuntimeException('reCAPTCHA soft-fail (matching host behavior): ' . $codes));
            }
        } catch (\Throwable $e) {
            report($e);
            return ['code' => 'recaptcha', 'message' => $base];
        }

        return null;
    }
}
