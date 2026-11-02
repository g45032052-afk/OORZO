<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Library\Payer;
use App\Library\Payment as PaymentInfo;
use App\Library\Receiver;
use App\Models\BusinessSetting;
use App\Models\DataSetting;
use App\Models\ProCustomerFaq;
use App\Models\ProCustomerSubscription;
use App\Models\ProCustomerSubscriptionPlan;
use App\Models\Translation;
use App\Models\User;
use App\Traits\ManagesProCustomerSubscription;
use App\Traits\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ProCustomerController extends Controller
{
    use ManagesProCustomerSubscription;

    private const SETTINGS_TYPE = 'pro_customer_benefits';

    private const BENEFITS_KEYS = [
        'discount_status',
        'discount_percentage',
        'discount_max_amount',
        'discount_min_order_amount',
        'discount_min_order_status',
        'delivery_fee_status',
        'delivery_fee_offer_type',
        'delivery_fee_min_order_amount',
        'delivery_fee_min_order_status',
        'delivery_charge_discount',
        'coupon_status',
    ];

    /**
     * GET /api/v1/pro-customer/plans
     *
     * Returns the active pro subscription plans together with the currently
     * configured benefit (only one of discount / delivery / coupon is active
     * at any given time). Public endpoint — no auth required for browsing.
     */
    public function plans(): JsonResponse
    {
        $proMemberStatus = (int) Helpers::get_business_settings('pro_member_status');

        if ($proMemberStatus !== 1) {
            return response()->json([
                'pro_member_status' => 0,
                'pro_brand' => null,
                'plans' => [],
                'benefits' => null,
            ], 200);
        }

        $businessName = Helpers::get_business_settings('business_name') ?: 'StackFood';
        $proBrand = trim($businessName) . ' ' . translate('messages.Pro');

        $plans = ProCustomerSubscriptionPlan::where('status', 1)
            ->orderBy('duration')
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => (int) $plan->id,
                    'plan_name' => $plan->plan_name,
                    'plan_type' => $plan->plan_type,
                    'price' => (float) $plan->price,
                    'duration' => (int) $plan->duration,
                    'duration_label' => (int) $plan->duration . ' ' . translate('messages.Days'),
                    'status' => (int) $plan->status,
                ];
            })
            ->values();

        $rows = DataSetting::where('type', self::SETTINGS_TYPE)
            ->whereIn('key', self::BENEFITS_KEYS)
            ->pluck('value', 'key')
            ->toArray();

        return response()->json([
            'pro_member_status' => 1,
            'pro_brand' => $proBrand,
            'plans' => $plans,
            'benefits' => $this->normalizeBenefits($rows),
        ], 200);
    }

    /**
     * GET /api/v1/pro-customer/active-offer
     *
     * Returns the authenticated user's currently-active Pro Customer offer.
     * Shape mirrors the trait method:
     * ['status' => bool, 'message' => string, 'benefit' => array|null, 'plan_details' => array|null].
     *
     * `status` is true only when the subscription is live *right now* - active and not past
     * its end_at - AND the admin has enabled one of the three benefits (discount /
     * delivery_fee / coupon). It is the same test checkout applies, so this endpoint can no
     * longer advertise a benefit place_order would decline to give.
     *
     * `showOnlyActivePlan: false` widens which subscription is *fetched*, not what counts as
     * live: an expired plan still answers with its `plan_details` - dates, totals, days
     * remaining - for the "your plan" screen, and with the `benefit` it used to give, so the
     * response shape never changes under a shipped client. **`status` is the field to branch
     * on** - a populated `benefit` says what the plan grants, not that it currently applies.
     *
     * `status` and `benefit.type` are named identically in checkout-summary's `pro` block, so
     * one parser reads both.
     *
     * Requires auth:api.
     */
    public function activeOffer(Request $request): JsonResponse
    {
        return response()->json($this->getProCustomerOffer(userId: $request->user()->id , showOnlyActivePlan:false), 200);
    }

    /**
     * GET /api/v1/pro-customer/faqs
     *
     * Returns the active pro-customer FAQs ordered by priority.
     * Optional `search` query param matches against question/answer text.
     * Public endpoint.
     */
    public function faqs(Request $request): JsonResponse
    {
        $search = $request->input('search');
        $limit = (int) $request->input('limit', 20);
        $limit = $limit > 0 && $limit <= 100 ? $limit : 20;
        $offset = max(0, (int) $request->input('offset', 0));

        $query = ProCustomerFaq::where('status', 1)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('question', 'like', "%{$search}%")
                        ->orWhere('answer', 'like', "%{$search}%");
                });
            })
            ->orderBy('priority');

        $total = (clone $query)->count();
        $faqs = $query->skip($offset)->take($limit)->get()
            ->map(function ($faq) {
                return [
                    'id' => (int) $faq->id,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                    'priority' => (int) $faq->priority,
                ];
            })
            ->values();

        return response()->json([
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'faqs' => $faqs,
        ], 200);
    }

    /**
     * GET /api/v1/pro-customer/terms-and-conditions
     *
     * Returns the configured pro-customer terms page (title + description).
     * Honours the current locale's translations when present.
     * Public endpoint.
     */
    public function termsAndConditions(): JsonResponse
    {
        $type = 'pro_customer_terms';
        $locale = app()->getLocale();

        $rows = DataSetting::where('type', $type)
            ->whereIn('key', ['page_title', 'page_description'])
            ->get();

        $titleRow = $rows->firstWhere('key', 'page_title');
        $descRow = $rows->firstWhere('key', 'page_description');

        $title = $this->translatedDataSettingValue($titleRow, 'pro_terms_page_title', $locale);
        $description = $this->translatedDataSettingValue($descRow, 'pro_terms_page_description', $locale);

        return response()->json([
            'page_title' => $title,
            'page_description' => $description,
        ], 200);
    }

    private function translatedDataSettingValue(?DataSetting $row, string $translationKey, string $locale): ?string
    {
        if (!$row) {
            return null;
        }

        $translated = Translation::where([
            'translationable_type' => DataSetting::class,
            'translationable_id' => $row->id,
            'key' => $translationKey,
            'locale' => $locale,
        ])->value('value');

        return $translated ?: $row->getRawOriginal('value');
    }

    /**
     * POST /api/v1/customer/pro-customer/subscribe   (auth:api)
     *
     * Body:
     *   plan_id          (required) — id from /pro-customer/plans
     *   payment_type     (required) — "wallet" | "digital_payment"
     *   payment_method   (required when payment_type=digital_payment) — gateway slug e.g. "stripe", "razor_pay"
     *   payment_platform (optional) — "app" | "web"
     *   callback         (optional) — external redirect after gateway success/failure
     *
     * Wallet flow: balance is debited via the trait inside one DB transaction
     * and the subscription becomes active immediately.
     *
     * Digital flow: a PaymentRequest is created (no subscription yet) and a
     * gateway redirect link is returned. The actual subscribe happens inside
     * the global success hook `pro_customer_subscription_success` which calls
     * the same trait method.
     */
    public function subscribe(Request $request): JsonResponse
    {
        if ((int) Helpers::get_business_settings('pro_member_status') !== 1) {
            return response()->json(['errors' => [['code' => 'pro_disabled', 'message' => translate('messages.pro_member_feature_is_disabled')]]], 403);
        }

        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|integer|exists:pro_customer_subscription_plans,id',
            'payment_type' => 'required|in:wallet,digital_payment,free_trial',
            'payment_method' => 'required_if:payment_type,digital_payment|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user = $request->user();
        $plan = ProCustomerSubscriptionPlan::where('id', $request->plan_id)
            ->where('status', 1)
            ->first();
        if (!$plan) {
            return response()->json(['errors' => [['code' => 'plan_unavailable', 'message' => translate('messages.plan_not_available')]]], 404);
        }

        $current = ProCustomerSubscription::where('user_id', $user->id)->latest('id')->first();
        $mode = match (true) {
            !$current => 'start',
            (int) $current->plan_id === (int) $plan->id => 'renew',
            default => 'shift',
        };

        if ($plan->plan_type === 'free_trial') {
            return $this->finalizeSubscription($user, $plan, ['payment_method' => 'free_trial'], $mode);
        }

        if ($request->payment_type === 'wallet') {
            return $this->finalizeSubscription($user, $plan, ['payment_method' => 'wallet'], $mode);
        }

        // payment_type === 'digital_payment' — kick off gateway redirect
        $digital = Helpers::get_business_settings('digital_payment');
        if (($digital['status'] ?? 0) == 0) {
            return response()->json(['errors' => [['code' => 'digital_payment_disabled', 'message' => translate('messages.digital_payment_is_disable')]]], 403);
        }

        return $this->buildGatewayRedirect($request, $user, $plan, $mode);
    }

    /**
     * POST /api/v1/customer/pro-customer/cancel   (auth:api)
     *
     * Cancels the authenticated user's currently-active Pro Customer subscription.
     * Sets status to "canceled", disables auto-renew, and clears the user's pro_status flag.
     * Returns 404 when there is no active subscription to cancel.
     */
    public function cancel(Request $request): JsonResponse
    {
        $user = $request->user();

        $subscription = ProCustomerSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->latest('id')
            ->first();

        if (!$subscription) {
            return response()->json([
                'errors' => [['code' => 'no_active_subscription', 'message' => translate('messages.no_active_subscription_to_cancel')]],
            ], 404);
        }

        $this->cancelProCustomerSubscription($subscription);

        return response()->json([
            'message' => translate('messages.subscription_canceled'),
        ], 200);
    }

    private function finalizeSubscription(User $user, ProCustomerSubscriptionPlan $plan, array $payment, string $mode): JsonResponse
    {
        try {
            $subscription = $this->applyProCustomerPlan($user, $plan, $payment, $mode);
        } catch (RuntimeException $e) {
            if ($e->getMessage() === 'insufficient_wallet_balance') {
                return response()->json(['errors' => [['code' => 'insufficient_wallet_balance', 'message' => translate('messages.wallet_balance_is_insufficient_for_this_plan')]]], 403);
            }
            throw $e;
        }

        return response()->json([
            'message' => translate('messages.subscription_activated'),
            'subscription' => [
                'id' => $subscription->id,
                'plan_id' => $subscription->plan_id,
                'plan_name' => $subscription->plan_name,
                'plan_type' => $subscription->plan_type,
                'plan_price' => (float) $subscription->plan_price,
                'start_at' => optional($subscription->start_at)->toIso8601String(),
                'end_at' => optional($subscription->end_at)->toIso8601String(),
                'status' => $subscription->status,
            ],
        ], 200);
    }

    private function buildGatewayRedirect(Request $request, User $user, ProCustomerSubscriptionPlan $plan, string $mode): JsonResponse
    {
        $payer = new Payer(
            ($user->f_name ?? '') . ' ' . ($user->l_name ?? ''),
            $user->email,
            $user->phone,
            ''
        );

        $currency = BusinessSetting::where('key', 'currency')->first()?->value;
        $businessName = BusinessSetting::where('key', 'business_name')->first()?->value;
        $logo = BusinessSetting::where('key', 'logo')->first()?->value;

        $additional = [
            'business_name' => $businessName,
            'business_logo' => dynamicStorage('storage/app/public/business') . '/' . $logo,
            'plan_id' => (int) $plan->id,
            'mode' => $mode,
        ];

        $paymentInfo = new PaymentInfo(
            success_hook: 'pro_customer_subscription_success',
            failure_hook: 'pro_customer_subscription_failed',
            currency_code: $currency,
            payment_method: $request->payment_method,
            payment_platform: $request->payment_platform,
            payer_id: $user->id,
            receiver_id: '100',
            additional_data: $additional,
            payment_amount: (float) $plan->price,
            external_redirect_link: $request->input('callback', session('callback')),
            attribute: 'pro_customer_subscription_payment',
            attribute_id: (string) $user->id,
        );

        $receiver = new Receiver('receiver_name', 'example.png');

        $redirectLink = Payment::generate_link($payer, $paymentInfo, $receiver);

        if (!$redirectLink) {
            return response()->json(['errors' => [['code' => 'invalid_gateway', 'message' => translate('messages.payment_gateway_not_supported')]]], 400);
        }

        return response()->json(['redirect_link' => $redirectLink], 200);
    }

    private function normalizeBenefits(array $rows): array
    {
        $intOrNull = static fn($v) => isset($v) && $v !== '' ? (int) $v : null;
        $floatOrNull = static fn($v) => isset($v) && $v !== '' ? (float) $v : null;

        $discountActive = (int) ($rows['discount_status'] ?? 0) === 1;
        $deliveryActive = (int) ($rows['delivery_fee_status'] ?? 0) === 1;
        $couponActive = (int) ($rows['coupon_status'] ?? 0) === 1;

        $type = match (true) {
            $discountActive => 'discount',
            $deliveryActive => 'delivery_fee',
            $couponActive   => 'coupon',
            default         => null,
        };

        return [
            'active_type' => $type,
            'discount' => [
                'active' => $discountActive ? 1 : 0,
                'percentage' => $floatOrNull($rows['discount_percentage'] ?? null),
                'max_amount' => $floatOrNull($rows['discount_max_amount'] ?? null),
                'min_order_status' => (int) ($rows['discount_min_order_status'] ?? 0),
                'min_order_amount' => $floatOrNull($rows['discount_min_order_amount'] ?? null),
            ],
            'delivery_fee' => [
                'active' => $deliveryActive ? 1 : 0,
                'offer_type' => $rows['delivery_fee_offer_type'] ?? 'full_free',
                'min_order_status' => (int) ($rows['delivery_fee_min_order_status'] ?? 0),
                'min_order_amount' => $floatOrNull($rows['delivery_fee_min_order_amount'] ?? null),
                'charge_discount_percentage' => $floatOrNull($rows['delivery_charge_discount'] ?? null),
            ],
            'coupon' => [
                'active' => $couponActive ? 1 : 0,
            ],
        ];
    }
}
