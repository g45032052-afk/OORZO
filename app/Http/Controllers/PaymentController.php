<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;

use App\CentralLogics\OrderLogic;
use App\Library\Payer;
use App\Traits\Payment;
use App\Library\Receiver;
use App\Library\Payment as PaymentInfo;


class PaymentController extends Controller
{
    public function __construct(){
        if (is_dir('App\Traits') && trait_exists('App\Traits\Payment')) {
            $this->extendWithPaymentGatewayTrait();
        }
    }

    private function extendWithPaymentGatewayTrait()
    {
        if (!class_exists('ExtendedController')) {
            $extendedControllerClass = $this->generateExtendedControllerClass();
            eval($extendedControllerClass);
        }
    }

    private function generateExtendedControllerClass()
    {
        $baseControllerClass = get_class($this);
        $traitClassName = 'App\Traits\Payment';

        $extendedControllerClass = "
            class ExtendedController extends $baseControllerClass {
                use $traitClassName;
            }
        ";

        return $extendedControllerClass;
    }
    public function payment(Request $request)
    {
        if ($request->has('callback')) {
            Order::where(['id' => $request->order_id])->update(['callback' => $request['callback']]);
        }

        session()->put('customer_id', $request['customer_id']);
        session()->put('payment_platform', $request['payment_platform']);
        session()->put('order_id', $request->order_id);

        $order = Order::where(['id' => $request->order_id, 'user_id' => $request['customer_id']])->first();

        if(!$order){
            return response()->json(['errors' => ['code' => 'order-payment', 'message' => 'Data not found']], 403);
        }

        //guest user check
        if ($order->is_guest) {
            $address = json_decode($order['delivery_address'] , true);
            $customer = collect([
                'first_name' => $address['contact_person_name'],
                'last_name' => '',
                'phone' => $address['contact_person_number'],
                'email' => $address['contact_person_email'],
            ]);

        } else {
            // Gateway callbacks can resolve a storefront customer (tenant > 0)
            // outside a storefront request context, so bypass HostScope here.
            $customer = User::withoutGlobalScope(\App\Scopes\HostScope::class)->find($request['customer_id']);
            $customer = collect([
                'first_name' => $customer['f_name'],
                'last_name' => $customer['l_name'],
                'phone' => $customer['phone'],
                'email' => $customer['email'],
            ]);
        }



        if (session()->has('payment_method') == false) {
            session()->put('payment_method', 'ssl_commerz_payment');
        }

        $order_amount = $order->order_amount - $order->partially_paid_amount;

            if (!isset($customer)) {
                return response()->json(['errors' => ['message' => 'Customer not found']], 403);
            }

            if (!isset($order_amount)) {
                return response()->json(['errors' => ['message' => 'Amount not found']], 403);
            }

            if (!$request->has('payment_method')) {
                return response()->json(['errors' => ['message' => 'Payment not found']], 403);
            }

            $payer = new Payer($customer['first_name'].' '.$customer['last_name'], $customer['email'], $customer['phone'], '');

            $currency=BusinessSetting::where(['key'=>'currency'])->first()->value;
            $additional_data = [
                'business_name' => BusinessSetting::where(['key'=>'business_name'])->first()?->value,
                'business_logo' => dynamicStorage('storage/app/public/business') . '/' .BusinessSetting::where(['key' => 'logo'])->first()?->value
            ];
            $payment_info = new PaymentInfo(
                success_hook: 'order_place',
                failure_hook: 'order_failed',
                currency_code: $currency,
                payment_method: $request->payment_method,
                payment_platform: $request['payment_platform'],
                payer_id: $request['customer_id'],
                receiver_id: '100',
                additional_data: $additional_data,
                payment_amount: $order_amount,
                external_redirect_link: $request->has('callback')?$request['callback']:session('callback'),
                attribute: 'order',
                attribute_id: $order->id
            );

            $receiver_info = new Receiver('receiver_name','example.png');

            $redirect_link = Payment::generate_link($payer, $payment_info, $receiver_info);

            return redirect($redirect_link);


        //for default payment gateway

        if (isset($customer) && isset($order)) {
            $data = [
                'name' => $customer['f_name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'],
            ];
            session()->put('data', $data);
            return view('payment-view');
        }

        return response()->json(['errors' => ['code' => 'order-payment', 'message' => 'Data not found']], 403);

    }


    /**
     * Gateway entry for a cash order the customer is settling before delivery.
     *
     * Deliberately a separate entry from payment() rather than a flag on it: the only
     * difference is the pair of hooks, and those hooks are the whole safety story. The order
     * already exists and is in flight, so success must move the payment and nothing else,
     * and failure must leave the order completely alone.
     *
     * Eligibility is re-checked here, not just in the API that produced this link - the URL
     * is handed to a webview and could be opened again later, after delivery.
     */
    public function codPayment(Request $request)
    {
        $order = Order::find($request->order_id);

        if (! $order || ! $this->codPaymentAllowed($order, $request['customer_id'])) {
            return response()->json(['errors' => [['code' => 'order-payment', 'message' => 'This order can no longer be paid online']]], 403);
        }

        if ($request->has('callback')) {
            Order::where(['id' => $request->order_id])->update(['callback' => $request['callback']]);
        }

        session()->put('customer_id', $request['customer_id']);
        session()->put('payment_platform', $request['payment_platform']);
        session()->put('order_id', $order->id);

        if ($order->is_guest) {
            $address = json_decode($order['delivery_address'], true);
            $customer = collect([
                'first_name' => $address['contact_person_name'] ?? '',
                'last_name' => '',
                'phone' => $address['contact_person_number'] ?? '',
                'email' => $address['contact_person_email'] ?? '',
            ]);
        } else {
            $user = User::withoutGlobalScope(\App\Scopes\HostScope::class)->find($order->user_id);
            $customer = collect([
                'first_name' => $user['f_name'],
                'last_name' => $user['l_name'],
                'phone' => $user['phone'],
                'email' => $user['email'],
            ]);
        }

        if (! $request->has('payment_method')) {
            return response()->json(['errors' => [['code' => 'payment_method', 'message' => 'Payment not found']]], 403);
        }

        // What is still owed, so a partial payment order only settles its remainder.
        $order_amount = $order->order_amount - $order->partially_paid_amount;

        $payer = new Payer($customer['first_name'].' '.$customer['last_name'], $customer['email'], $customer['phone'], '');

        $additional_data = [
            'business_name' => BusinessSetting::where(['key' => 'business_name'])->first()?->value,
            'business_logo' => dynamicStorage('storage/app/public/business').'/'.BusinessSetting::where(['key' => 'logo'])->first()?->value,
        ];

        $payment_info = new PaymentInfo(
            success_hook: 'cod_payment_success',
            failure_hook: 'cod_payment_failed',
            currency_code: BusinessSetting::where(['key' => 'currency'])->first()->value,
            payment_method: $request->payment_method,
            payment_platform: $request['payment_platform'],
            payer_id: $order->user_id,
            receiver_id: '100',
            additional_data: $additional_data,
            payment_amount: $order_amount,
            external_redirect_link: $request->has('callback') ? $request['callback'] : session('callback'),
            attribute: 'order',
            attribute_id: $order->id
        );

        return redirect(Payment::generate_link($payer, $payment_info, new Receiver('receiver_name', 'example.png')));
    }

    /**
     * The same rules the API applies, kept here so a stale link cannot bypass them.
     */
    private function codPaymentAllowed(Order $order, $customerId): bool
    {
        return OrderLogic::cod_to_digital_blocked_reason($order, $customerId) === null;
    }

    public function success()
    {
        $order = Order::where(['id' => session('order_id'), 'user_id'=>session('customer_id')])->first();
        if (isset($order) && $order->callback != null) {
            return redirect($order->callback . '&status=success');
        }
        return response()->json(['message' => 'Payment succeeded'], 200);
    }

    public function fail()
    {
        $order = Order::where(['id' => session('order_id'), 'user_id'=>session('customer_id')])->first();
        if (isset($order) && $order->callback != null) {
            return redirect($order->callback . '&status=fail');
        }
        return response()->json(['message' => 'Payment failed'], 403);
    }
    public function cancel(Request $request)
    {
        $order = Order::where(['id' => session('order_id'), 'user_id'=>session('customer_id')])->first();
        if (isset($order) && $order->callback != null) {
            return redirect($order->callback . '&status=fail');
        }
        return response()->json(['message' => 'Payment failed'], 403);
    }

}
