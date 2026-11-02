<?php

use App\Models\Admin;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\AdminWallet;
use App\Models\DeliveryMan;
use App\Models\WalletPayment;
use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Mail\OrderVerificationMail;
use App\CentralLogics\CustomerLogic;
use Illuminate\Support\Facades\Mail;
use App\Models\SubscriptionBillingAndRefundHistory;
use Brian2694\Toastr\Facades\Toastr;

/*
 * True when the given user belongs to a storefront (tenant or sub-tenant set)
 * AND the Builder wallet-features master switch is off. Lets the shared host
 * pipeline (wallet/loyalty/referral/cashback credits, refund flow, related
 * notifications) skip side effects for storefront customers without affecting
 * host customers (who carry tenant_id = sub_tenant_id = 0).
 */
if (! function_exists('storefront_wallet_disabled_for_user')) {
    function storefront_wallet_disabled_for_user($userId): bool
    {
        if (\config('builder.wallet_features_enabled', true)) {
            return false;
        }
        if (! $userId) {
            return false;
        }
        $user = \App\Models\User::withoutGlobalScope(\App\Scopes\HostScope::class)
            ->select(['id', 'tenant_id', 'sub_tenant_id'])
            ->find($userId);
        if (! $user) {
            return false;
        }
        return ((int) $user->tenant_id) > 0 || ((int) $user->sub_tenant_id) > 0;
    }
}

if (! function_exists('getDisallowedExtensionsListArray')) {
    /**
     * Dangerous / executable file extensions rejected on user uploads
     * (e.g. the storefront inbox chat). Lowercase, no leading dot.
     * Mirrors the legacy web-chat upload gate the Builder module expects.
     */
    function getDisallowedExtensionsListArray(): array
    {
        return [
            'php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'pht', 'phar',
            'exe', 'com', 'bat', 'cmd', 'msi', 'scr', 'cpl', 'jar', 'app',
            'sh', 'bash', 'bin', 'run', 'csh', 'ksh', 'ps1',
            'vbs', 'vbe', 'js', 'jse', 'wsf', 'wsh', 'hta',
            'dll', 'so', 'sys', 'html', 'htm', 'shtml', 'svg', 'htaccess',
        ];
    }
}


if (! function_exists('write_language_file')) {
    /**
     * Rewrite a language file so no request can ever read it half written.
     *
     * translate() rewrites the whole file the moment it meets a key it has not seen, and a
     * page meets several at once - the area list is one. Two requests doing that together
     * interleaved inside a single file_put_contents(), and what survived was a torn array:
     * a string cut in the middle, or one entry's quotes spliced into another's. Every later
     * request then died in `include` with a ParseError pointing at wherever the tear landed,
     * which is why the same page reported two different broken lines on two different days.
     *
     * The new contents go to a temporary file beside the target and are moved into place
     * with rename(), which is atomic on a POSIX filesystem: a reader sees either the whole
     * old file or the whole new one. Two writers can still race, and the later one simply
     * wins - a key it dropped is written again the next time it is missing, which costs a
     * rewrite rather than a broken deployment.
     */
    function write_language_file(string $path, array $translations): bool
    {
        $directory = dirname($path);

        if (! is_dir($directory) && ! @mkdir($directory, 0777, true) && ! is_dir($directory)) {
            return false;
        }

        $temporary = $path . '.' . getmypid() . '.' . uniqid() . '.tmp';

        if (@file_put_contents($temporary, "<?php return " . var_export($translations, true) . ";") === false) {
            return false;
        }

        if (! @rename($temporary, $path)) {
            @unlink($temporary);

            return false;
        }

        // The include below is served from the opcode cache otherwise, so a fresh key would
        // not be seen until the cache expired.
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($path, true);
        }

        return true;
    }
}

if (! function_exists('translate')) {
    function translate($key, $replace = [])
    {
        if (strpos($key, 'validation.') === 0 || strpos($key, 'passwords.') === 0 || strpos($key, 'pagination.') === 0 || strpos($key, 'order_texts.') === 0) {
            return trans($key, $replace);
        }

        $key = strpos($key, 'messages.') === 0 ? substr($key, 9) : $key;
        $local = app()->getLocale();
        $path = base_path('resources/lang/' . $local . '/messages.php');
        // A ParseError from a damaged language file is an Error, not an Exception, so it used
        // to pass straight through this catch and take the whole page down with it. Catching
        // Throwable leaves the request rendering off Laravel's own translator instead.
        try {
            $lang_array = include($path);
            $lang_array = is_array($lang_array) ? $lang_array : [];
            $processed_key = ucfirst(str_replace('_', ' ', Helpers::remove_invalid_charcaters($key)));

            if (!array_key_exists($key, $lang_array)) {
                $lang_array[$key] = $processed_key;
                write_language_file($path, $lang_array);
                $result = $processed_key;
            } else {
                $result = trans('messages.' . $key, $replace);
            }
        } catch (\Throwable $exception) {
            info([$exception->getFile(), $exception->getLine(), $exception->getMessage()]);

            // Deliberately not trans() here: Laravel's translator loads the very file that
            // just failed to parse, so the fallback threw a second time - from inside the
            // catch, where nothing was left to handle it. That is how one damaged language
            // file turned every page that reads it into a 500. The humanised key is what a
            // missing key renders as anyway, so the page still reads.
            $result = ucfirst(str_replace('_', ' ', Helpers::remove_invalid_charcaters($key)));

            foreach ($replace as $search => $value) {
                $result = str_replace(':' . $search, (string) $value, $result);
            }
        }

        return $result;
    }
}

if (! function_exists('order_place')) {

    function order_place($data)
    {
        $order = Order::find($data->attribute_id);
        $order->order_status = 'confirmed';
        if ($order->payment_method != 'partial_payment') {
            $order->payment_method = $data->payment_method;
        }
        // $order->transaction_reference=$data->transaction_ref;
        $order->payment_status = 'paid';
        $order->confirmed = now();
        $order->save();

        if ($order->restaurant->restaurant_model == 'subscription' && isset($order->restaurant->restaurant_sub)) {
            if ($order->restaurant->restaurant_sub->max_order != "unlimited" && $order->restaurant->restaurant_sub->max_order > 0) {
                $order->restaurant->restaurant_sub()->decrement('max_order', 1);
            }
        }

        OrderLogic::update_unpaid_order_payment(order_id: $order->id, payment_method: $data->payment_method);
        //PlaceOrderMail
        try {
            Helpers::send_order_notification($order);


            $address = json_decode($order->delivery_address, true);

            $order_verification_mail_status = Helpers::get_mail_status('order_verification_mail_status_user');

            $notification_status = Helpers::getNotificationStatusData('customer', 'customer_delivery_verification');

            if ($notification_status?->mail_status == 'active' &&  config('mail.status') && config('order_delivery_verification') == 1 && $order_verification_mail_status == '1' && $order->is_guest == 0) {
                Mail::to($order->customer?->getRawOriginal('email'))->send(new OrderVerificationMail($order->otp, $order->customer->f_name));
            }

            if ($notification_status?->mail_status == 'active' && $order->is_guest == 1 && config('mail.status') && $order_verification_mail_status == '1' && isset($address['contact_person_email'])) {
                Mail::to($address['contact_person_email'])->send(new OrderVerificationMail($order->otp, $order->customer->f_name));
            }
        } catch (\Exception $exception) {
            info([$exception->getFile(), $exception->getLine(), $exception->getMessage()]);
        }
    }
}
if (! function_exists('cod_payment_success')) {

    /**
     * A cash order the customer chose to settle through a gateway before delivery.
     *
     * Deliberately not order_place(). That hook is written for an order coming into
     * existence: it forces order_status to confirmed, stamps confirmed_at, decrements the
     * restaurant's subscription quota and re-sends the placed-order notification. Running it
     * against an order that is already being cooked or is out for delivery would rewind its
     * status and double-charge the quota. Only the payment moves here; the order carries on
     * exactly where it was.
     */
    function cod_payment_success($data)
    {
        $order = Order::find($data->attribute_id);

        if (! $order) {
            return;
        }

        // A partial payment order keeps its own method - the gateway settled the remainder,
        // it did not replace how the order was paid for.
        if ($order->payment_method != 'partial_payment') {
            $order->payment_method = $data->payment_method;
        }

        $order->payment_status = 'paid';

        // Without a reference the admin panel refuses to mark any non-cash order delivered
        // ("add your payment ref first"), so converting a cash order would strand it. The
        // gateway's own transaction id is that reference; the payment request id is the
        // fallback, since either is enough to trace the money.
        $order->transaction_reference = $order->transaction_reference
            ?: ($data->transaction_id ?? $data->id ?? null);

        $order->save();

        OrderLogic::update_unpaid_order_payment(order_id: $order->id, payment_method: $data->payment_method);

        // If the rider marked it delivered while the customer was inside the gateway, the
        // payout has already booked this as cash against whoever would have collected it.
        $settlement = OrderLogic::settle_cod_paid_digitally($order);

        // Recorded on the order, not just in the log file: the admin's Edit History Log is
        // where a payment-method change has to be visible and dated, and this is the one path
        // that rewrites payment_method after placement.
        $editLog = new \App\Models\OrderEditLog();
        $editLog->order_id = $order->id;
        $editLog->log = 'Payment method changed from cash on delivery to ' . str_replace('_', ' ', (string) $data->payment_method) . ' and payment status marked as paid';
        $editLog->edited_by = 'customer';
        $editLog->save();

        info(['cod_to_digital' => ['order' => $order->id, 'method' => $data->payment_method] + $settlement]);
    }
}

if (! function_exists('cod_payment_failed')) {

    /**
     * The customer backed out of the gateway, or it declined.
     *
     * Nothing to undo and nothing to record on the order: it was a cash order before the
     * attempt and it stays one, still live, still collectable on delivery. Deliberately not
     * order_failed(), which sets order_status to failed and would kill an order that is
     * already on its way.
     */
    function cod_payment_failed($data)
    {
        info(['cod_to_digital_failed' => ['order' => $data->attribute_id, 'method' => $data->payment_method]]);
    }
}

if (! function_exists('order_failed')) {
    function order_failed($data)
    {
        $order = Order::find($data->attribute_id);
        $order->order_status = 'failed';
        if ($order->payment_method != 'partial_payment') {
            $order->payment_method = $data->payment_method;
        }
        $order->failed = now();
        $order->save();
    }
}

if (! function_exists('wallet_success')) {
    function wallet_success($data)
    {
        $order = WalletPayment::find($data->attribute_id);
        $order->payment_method = $data->payment_method;
        // $order->transaction_reference=$data->transaction_ref;
        $order->payment_status = 'success';
        $order->save();
        $wallet_transaction = CustomerLogic::create_wallet_transaction($data->payer_id, $data->payment_amount, 'add_fund', $data->payment_method);
        if ($wallet_transaction) {
            try {
                Helpers::add_fund_push_notification($data->payer_id,$data->payment_amount);
                $notification_status = Helpers::getNotificationStatusData('customer', 'customer_add_fund_to_wallet');

                if ($notification_status?->mail_status == 'active' && config('mail.status') && Helpers::get_mail_status('add_fund_mail_status_user') == '1') {
                    Mail::to($wallet_transaction->user?->getRawOriginal('email'))->send(new \App\Mail\AddFundToWallet($wallet_transaction));
                }
            } catch (\Exception $exception) {
                info([$exception->getFile(), $exception->getLine(), $exception->getMessage()]);
            }
        }
    }
}
if (! function_exists('wallet_failed')) {
    function wallet_failed($data)
    {
        $order = WalletPayment::find($data->attribute_id);
        $order->payment_status = 'failed';
        $order->payment_method = $data->payment_method;
        $order->save();
    }
}

if (! function_exists('pro_customer_subscription_success')) {
    function pro_customer_subscription_success($data)
    {
        // Idempotency: avoid double-applying if the gateway retries the callback.
        $reference = 'pr_' . $data->id;
        if (\App\Models\ProCustomerTransaction::where('transaction_reference', $reference)->exists()) {
            return true;
        }

        $additional = is_array($data->additional_data) ? $data->additional_data : json_decode($data->additional_data ?? '[]', true);
        $planId = $additional['plan_id'] ?? null;
        $mode = $additional['mode'] ?? 'start';

        $user = \App\Models\User::find($data->payer_id);
        $plan = \App\Models\ProCustomerSubscriptionPlan::find($planId);
        if (!$user || !$plan) {
            return false;
        }

        // Anonymous class lets us reach the trait method without coupling helpers
        // to a controller. The trait creates the subscription + transaction row.
        $applier = new class { use \App\Traits\ManagesProCustomerSubscription; };
        $applier->applyProCustomerPlan($user, $plan, [
            'payment_method' => $data->payment_method,
            'payment_status' => 'success',
            'transaction_reference' => $reference,
            'paid_at' => now(),
        ], $mode);

        return true;
    }
}

if (! function_exists('pro_customer_subscription_failed')) {
    function pro_customer_subscription_failed($data)
    {
        // No subscription/transaction was created up-front, so nothing to revert.
        // PaymentRequest itself records the failed attempt.
        return true;
    }
}


if (! function_exists('sub_success')) {
    function sub_success($data)
    {
        $type = 'renew';
        if ($data->attribute == 'restaurant_subscription_payment') {
            $type = 'new_plan';
        } elseif ($data->attribute == 'restaurant_subscription_new_join') {
            $type = 'new_join';
        }

        $pending_bill = SubscriptionBillingAndRefundHistory::where([
            'restaurant_id' => $data->payer_id,
            'transaction_type' => 'pending_bill',
            'is_success' => 0
        ])?->sum('amount') ?? 0;
        Helpers::subscription_plan_chosen(restaurant_id: $data->payer_id, package_id: $data->attribute_id, payment_method: $data->payment_method, discount: 0, pending_bill: $pending_bill, reference: $data->attribute, type: $type);
        if ($type !== 'new_join') {
            Toastr::success($type == 'renew' ?  translate('Subscription_Package_Renewed_Successfully.') : translate('Subscription_Package_Shifted_Successfully.'));
        }

        return true;
    }
}

if (! function_exists('sub_fail')) {
    function sub_fail($data)
    {
        return true;
    }
}


if (! function_exists('collect_cash_fail')) {
    function collect_cash_fail($data)
    {
        return 0;
    }
}
if (! function_exists('collect_cash_success')) {
    function collect_cash_success($data)
    {

        try {
            $account_transaction = new AccountTransaction();
            if ($data->attribute === 'restaurant_collect_cash_payments') {
                $restaurant = Restaurant::where('vendor_id', $data->attribute_id)->first();
                $restaurant->status = 1;
                $restaurant->save();
                $user_data = $restaurant?->vendor;
                $current_balance = $user_data?->wallet?->collected_cash ?? 0;
                $account_transaction->from_type = 'restaurant';
                $account_transaction->from_id = $restaurant?->vendor?->id;
                $account_transaction->created_by = 'restaurant';
            } elseif ($data->attribute === 'deliveryman_collect_cash_payments') {
                $user_data = DeliveryMan::findOrFail($data->attribute_id);
                $user_data->status = 1;
                $user_data->save();
                $delivery_man = $user_data;
                $current_balance = $user_data?->wallet?->collected_cash ?? 0;
                $account_transaction->from_type = 'deliveryman';
                $account_transaction->from_id = $user_data->id;
                $account_transaction->created_by = 'deliveryman';
            } else {
                return 0;
            }
            $account_transaction->method = $data->payment_method;
            $account_transaction->ref = $data->attribute;
            $account_transaction->amount = $data->payment_amount;
            $account_transaction->current_balance = $current_balance;

            DB::beginTransaction();
            $account_transaction->save();
            $user_data?->wallet?->decrement('collected_cash', $account_transaction->amount);
            AdminWallet::where('admin_id', Admin::where('role_id', 1)->first()->id)->increment('digital_received',  $account_transaction->amount);

            DB::commit();
        } catch (\Exception $exception) {
            info([$exception->getFile(), $exception->getLine(), $exception->getMessage()]);
            DB::rollBack();
        }
        try {

            $notification_status = Helpers::getNotificationStatusData('deliveryman', 'deliveryman_collect_cash');


            if(isset($delivery_man)){
                $message =Helpers::getPushNotificationMessage(status:'deliveryman_collect_cash',userType: 'deliveryman' , lang: $delivery_man->current_language_key, deliveryManName:$delivery_man->f_name.' '.$delivery_man->l_name);
                if ( $message && isset($delivery_man->fcm_token)) {
                    $data= Helpers::makeDataForPushNotification(title:translate('Cash_Collected'), message:$message,orderId: '', type: 'cash_collect', orderStatus: '');
                    Helpers::send_push_notif_to_device($delivery_man->fcm_token, $data);
                    Helpers::insertDataOnNotificationTable($data , 'delivery_man', $delivery_man->id);
                }
            }


            if ($data->attribute == 'deliveryman_collect_cash_payments' && config('mail.status') && Helpers::get_mail_status('cash_collect_mail_status_dm') == 1) {
                Mail::to($notification_status?->mail_status == 'active' && $user_data?->getRawOriginal('email'))->send(new \App\Mail\CollectCashMail($account_transaction, $user_data['f_name']));
            }
        } catch (\Exception $exception) {
            info([$exception->getFile(), $exception->getLine(), $exception->getMessage()]);
        }
        return true;
    }
}

if (!function_exists('addon_published_status')) {
    function addon_published_status($module_name): int
    {
        try {
            $path = base_path("Modules/{$module_name}/Addon/info.php");

            if (!file_exists($path)) {
                return 0;
            }

            $full_data = include $path;

            return (isset($full_data['is_published']) && $full_data['is_published'] == 1) ? 1 : 0;

        } catch (\Throwable $exception) {
            info($exception->getMessage());
            return 0;
        }
    }
}

if (!function_exists('dynamicAsset')) {
     function dynamicAsset(string $path): string
    {
        $path = ltrim($path, '/');

        if (DOMAIN_POINTED_DIRECTORY !== 'public') {
            $path = 'public/' . $path;
        }

        return asset($path);
    }
}
if (!function_exists('dynamicStorage')) {
    function dynamicStorage(string $directory): string
    {
        if (DOMAIN_POINTED_DIRECTORY == 'public') {
            $result = str_replace('storage/app/public', 'storage', $directory);
        } else {
            $result = $directory;
        }
        return asset($result);
    }
}

if (!function_exists('getWebConfig')) {
    function getWebConfig($name): string|object|array|null
    {
        return Helpers::get_business_settings(key: $name) ;
    }

    if (!function_exists('getDemoModeFormButton')) {
        function getDemoModeFormButton($type = ''): string
        {
            $result = '';
            if ($type == 'class') {
                $result = getEnvMode() != 'demo' ? '' : 'call-demo';
            } elseif ($type == 'button') {
                $result = getEnvMode() != 'demo' ? 'submit' : 'button';
            }
            return $result;
        }
    }

    if (!function_exists('showDemoModeInputValue')) {
        function showDemoModeInputValue($value = null): string
        {
            return getEnvMode() != 'demo' ? $value : '';
        }
    }
    if (! function_exists('getEnvMode')) {
     function getEnvMode()
        {
            $configKey='envAppMode_conf';
            if (Config::has($configKey)) {
                $data = Config::get($configKey);
            } else {
                 $data = env('APP_MODE')??config('app.app_mode');
                Config::set($configKey, $data);
            }
            return $data;
        }
    }
}

    if (!function_exists('dynamicModuleAsset')) {

        function dynamicModuleAsset(string $moduleName, string $path): string
        {
            $moduleFolder = strtolower($moduleName);
            $cleanPath = $path;
            if (strpos($path, 'public/') === 0) {
                $cleanPath = substr($path, 7);
            }
            if (DOMAIN_POINTED_DIRECTORY == 'public') {
                return asset("modules/{$moduleFolder}/{$cleanPath}");
            }
            return asset("public/modules/{$moduleFolder}/{$cleanPath}");
            // return asset("modules/{$moduleFolder}/public/{$cleanPath}");
        }
    }
