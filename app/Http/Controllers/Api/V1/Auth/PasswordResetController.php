<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Mail\UserPasswordResetMail;
use App\Models\BusinessSetting;
use App\Models\PasswordReset;
use App\Models\Setting;
use App\Models\User;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Illuminate\Support\Carbon;
use App\CentralLogics\SMS_module;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Modules\Gateways\Traits\SmsGateway;

class PasswordResetController extends Controller
{
    /**
     * Host customers live at the (0,0) scope. Raw DB::table() aux-table ops
     * bypass HostScope, so pin this scope on every key/where to avoid touching
     * storefront (Builder) password-reset rows that share the scoped key.
     */
    private const HOST_SCOPE = ['tenant_id' => 0, 'sub_tenant_id' => 0];

    /**
     * Rules for the phone/email the customer resets with.
     *
     * Format is checked here so a mistyped number is reported as a bad number.
     * Without it anything at all reaches the account lookup and comes back as
     * "no account found", which reads as "you have no account" rather than
     * "check what you typed".
     */
    private function identifierRules(array $extraPhone = [], array $extraEmail = []): array
    {
        return [
            'verification_method' => 'required|in:phone,email',
            'phone' => array_merge([
                'nullable', 'required_if:verification_method,phone',
                'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:9',
            ], $extraPhone),
            'email' => array_merge([
                'nullable', 'required_if:verification_method,email', 'email',
            ], $extraEmail),
        ];
    }

    /**
     * The stock required_if/format wording names `verification_method` - an
     * internal field the customer never sees, which is where "The email field is
     * required when verification method is email" came from. These say what to
     * type instead.
     */
    private function identifierMessages(): array
    {
        return [
            'verification_method.required' => translate('messages.Choose_whether_to_reset_with_your_phone_number_or_email_address.'),
            'verification_method.in' => translate('messages.Choose_whether_to_reset_with_your_phone_number_or_email_address.'),
            'phone.required_if' => translate('messages.Enter_your_phone_number.'),
            'phone.regex' => translate('messages.Enter_a_valid_phone_number.'),
            'phone.min' => translate('messages.Enter_a_valid_phone_number.'),
            'phone.exists' => translate('messages.No_account_found_with_this_phone_number.'),
            'email.required_if' => translate('messages.Enter_your_email_address.'),
            'email.email' => translate('messages.Enter_a_valid_email_address.'),
            'email.exists' => translate('messages.No_account_found_with_this_email_address.'),
        ];
    }

    /**
     * No account exists behind the phone or email the customer typed.
     *
     * Reported the way every other rejected-credential response in the API is:
     * 403 with an `errors` array keyed by the field, so the apps can surface the
     * message against the input. A 404 cannot be used here - the clients read it
     * as a missing endpoint and never show its body.
     */
    private function accountNotFound(string $identifier)
    {
        return response()->json(['errors' => [
            [
                'code' => $identifier,
                'message' => $identifier === 'email'
                    ? translate('messages.No_account_found_with_this_email_address.')
                    : translate('messages.No_account_found_with_this_phone_number.'),
            ],
        ]], 403);
    }

    public function resetPasswordRequest(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            $this->identifierRules(),
            $this->identifierMessages()
        );

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $identifier = $request->verification_method;
        $value = $request->$identifier;

        $firebaseOtpVerification = BusinessSetting::where('key', 'firebase_otp_verification')->value('value') ?? 0;

        $customer = User::where($identifier, $value)->first();

        if (!$customer) {
            return $this->accountNotFound($identifier);
        }

        $sendOtpVia = BusinessSetting::where('key', 'send_otp_via')->value('value') ?? 'sms';

        if ($firebaseOtpVerification && $sendOtpVia == 'firebase') {
            return response()->json(['message' => translate('messages.otp_sent_successfull')], 200);
        }

        $otpInterval = 60; // seconds
        $now = now();

        $resetData = DB::table('password_resets')
            ->where([$identifier => $value] + self::HOST_SCOPE)
            ->first();

        if ($resetData && Carbon::parse($resetData->created_at)->diffInSeconds($now) < $otpInterval) {
            $wait = $otpInterval - Carbon::parse($resetData->created_at)->diffInSeconds($now);
            return response()->json([
                'errors' => [
                    ['code' => 'otp', 'message' => translate('messages.please_try_again_after_') . $wait . ' ' . translate('messages.seconds')]
                ]
            ], 405);
        }

        // Generate token
        $token = getEnvMode() === 'test' ? '123456' : rand(100000, 999999);

        DB::table('password_resets')->updateOrInsert(
            [$identifier => $value] + self::HOST_SCOPE,
            ['token' => $token, 'created_at' => $now]
        );

        if (getEnvMode() === 'test') {
            return response()->json(['message' => translate('messages.Use_test_OTP')], 200);
        }

        // Send via SMS
        $isSmsActive = Setting::whereJsonContains('live_values->status', '1')
            ->where('settings_type', 'sms_config')
            ->exists();

        if ($isSmsActive && $identifier === 'phone') {
            $response = null;

            $publishedStatus = config('get_payment_publish_status')[0]['is_published'] ?? 0;

            if ($publishedStatus == 1) {
                $response = SmsGateway::send($value, $token);
            } else {
                $response = SMS_module::send($value, $token);
            }

            if ($response === 'success') {
                return response()->json(['message' => translate('messages.Otp_Successfully_Sent_To_Your_Phone')], 200);
            } else {
                return response()->json(['errors' => [
                    ['code' => 'otp', 'message' => translate('messages.failed_to_send_sms')]
                ]], 403);
            }
        }

        // Send via Mail
        if (config('mail.status') && $identifier === 'email') {
            try {
                $mailResponse = null;
                if (Helpers::get_mail_status('forget_password_mail_status_user') == '1' && $customer->email) {
                    Mail::to($customer?->getRawOriginal('email'))->send(new UserPasswordResetMail($token, $customer->f_name));
                    $mailResponse = 'success';
                }

                if ($mailResponse === 'success') {
                    return response()->json(['message' => translate('messages.Otp_Successfully_Sent_To_Your_Mail')], 200);
                }
            } catch (\Throwable $th) {
                info($th->getMessage());
            }

            return response()->json(['errors' => [
                ['code' => 'otp', 'message' => translate('messages.failed_to_send_mail')]
            ]], 403);
        }

        // Fallback
        return response()->json(['errors' => [
            ['code' => 'otp', 'message' => translate('messages.failed_to_send_otp')]
        ]], 403);
    }

    public function verifyToken(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            ['reset_token' => 'required'] + $this->identifierRules(),
            $this->identifierMessages()
        );

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $identifier = $request->verification_method;
        $value = $request->$identifier;

        $user = User::where($identifier, $value)->first();
        if (!$user) {
            return $this->accountNotFound($identifier);
        }

        if (getEnvMode() === 'test') {
            if ($request->reset_token === '123456') {
                return response()->json(['message' => translate('OTP found, you can proceed')], 200);
            }
            return response()->json(['errors' => [
                ['code' => 'invalid', 'message' => translate('Invalid OTP.')]
            ]], 400);
        }

        $resetData = DB::table('password_resets')
            ->where('token', $request->reset_token)
            ->where([$identifier => $value] + self::HOST_SCOPE)
            ->first();

        if ($resetData) {
            return response()->json(['message' => translate('OTP found, you can proceed')], 200);
        }

        // OTP invalid handling
        $maxOtpHit = 5;
        $maxOtpHitTime = 60; // seconds
        $tempBlockTime = 600; // seconds

        $verificationData = DB::table('password_resets')
            ->where([$identifier => $value] + self::HOST_SCOPE)
            ->first();

        if ($verificationData) {
            $now = now();

            if ($verificationData->is_temp_blocked) {
                $blockTime = Carbon::parse($verificationData->temp_block_time);
                $diff = $now->diffInSeconds($blockTime);

                if ($diff <= $tempBlockTime) {
                    $wait = $tempBlockTime - $diff;
                    return response()->json(['errors' => [
                        ['code' => 'otp_block_time', 'message' => translate('messages.please_try_again_after_') . CarbonInterval::seconds($wait)->cascade()->forHumans()]
                    ]], 405);
                }
            }

            if (
                $verificationData->otp_hit_count >= $maxOtpHit &&
                Carbon::parse($verificationData->created_at)->diffInSeconds($now) < $maxOtpHitTime &&
                !$verificationData->is_temp_blocked
            ) {
                DB::table('password_resets')->updateOrInsert(
                    [$identifier => $value] + self::HOST_SCOPE,
                    [
                        'is_temp_blocked' => 1,
                        'temp_block_time' => $now,
                        'created_at' => $now,
                    ]
                );

                return response()->json(['errors' => [
                    ['code' => 'otp_temp_blocked', 'message' => translate('messages.Too_many_attemps')]
                ]], 405);
            }

            if (
                $verificationData->is_temp_blocked &&
                Carbon::parse($verificationData->created_at)->diffInSeconds($now) >= $maxOtpHitTime
            ) {
                DB::table('password_resets')->updateOrInsert(
                    [$identifier => $value] + self::HOST_SCOPE,
                    [
                        'otp_hit_count' => 0,
                        'is_temp_blocked' => 0,
                        'temp_block_time' => null,
                        'created_at' => $now,
                    ]
                );
            }
        }

        // Update hit count
        DB::table('password_resets')->updateOrInsert(
            [$identifier => $value] + self::HOST_SCOPE,
            [
                'otp_hit_count' => DB::raw('otp_hit_count + 1'),
                'created_at' => now(),
                'temp_block_time' => null,
            ]
        );

        return response()->json(['errors' => [
            ['code' => 'invalid', 'message' => translate('Invalid OTP.')]
        ]], 400);
    }

    public function resetPasswordSubmit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reset_token' => 'required',
            'password' => ['required', Password::min(8)],
            'confirm_password' => 'required|same:password',
        ] + $this->identifierRules(['exists:users,phone'], ['exists:users,email']),
            $this->identifierMessages()
        );

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $identifierType = $request->verification_method;
        $identifierValue = $request->{$identifierType};

        $user = User::where($identifierType, $identifierValue)->first();

        $resetData = DB::table('password_resets')
            ->where('token', $request->reset_token)
            ->where([$identifierType => $identifierValue] + self::HOST_SCOPE)
            ->first();

        if (getEnvMode() === 'test' && $request->reset_token === '123456') {
            $user->password = bcrypt($request->password);
            $user->save();

            if ($resetData) {
                DB::table('password_resets')
                    ->where('token', $request->reset_token)
                    ->where([$identifierType => $identifierValue] + self::HOST_SCOPE)
                    ->delete();
            }

            return response()->json(['message' => translate('Password changed successfully.')], 200);
        }

        if ($resetData) {
            $user->password = bcrypt($request->password);
            $user->save();

            DB::table('password_resets')
                ->where('token', $request->reset_token)
                ->where([$identifierType => $identifierValue] + self::HOST_SCOPE)
                ->delete();

            return response()->json(['message' => translate('Password changed successfully.')], 200);
        }

        return response()->json(['errors' => [
            ['code' => 'invalid', 'message' => translate('messages.invalid_otp')]
        ]], 400);
    }

    public function firebase_auth_verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionInfo' => 'required',
            'phoneNumber' => 'required',
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }


        $webApiKey = BusinessSetting::where('key', 'firebase_web_api_key')->first()->value??'';

//        $firebaseOTPVerification = Helpers::get_business_settings('firebase_otp_verification');
//        $webApiKey = $firebaseOTPVerification ? $firebaseOTPVerification['web_api_key'] : '';

        $response = Http::post('https://identitytoolkit.googleapis.com/v1/accounts:signInWithPhoneNumber?key='. $webApiKey, [
            'sessionInfo' => $request->sessionInfo,
            'phoneNumber' => $request->phoneNumber,
            'code' => $request->code,
        ]);

        $responseData = $response->json();

        if (isset($responseData['error'])) {
            $errors = [];
            $errors[] = ['code' => "403", 'message' => $responseData['error']['message']];
            return response()->json(['errors' => $errors], 403);
        }

        $user = User::Where(['phone' => $request->phoneNumber])->first();

        if (isset($user)){
            if ($request['is_reset_token'] == 1){
                DB::table('password_resets')->updateOrInsert(['phone' => $user->phone] + self::HOST_SCOPE,
                    [
                        'token' => $request->code,
                        'created_at' => now(),
                    ]);
                return response()->json(['message'=>"OTP found, you can proceed"], 200);
            }else{
                if ($user->is_phone_verified) {
                    return response()->json([
                        'message' => translate('messages.phone_number_is_already_varified')
                    ], 200);
                }
                $user->is_phone_verified = 1;
                $user->save();

                return response()->json([
                    'message' => translate('messages.phone_number_varified_successfully'),
                    'otp' => 'inactive'
                ], 200);
            }
        }

        return response()->json([
            'message' => translate('messages.not_found')
        ], 404);
    }
}
