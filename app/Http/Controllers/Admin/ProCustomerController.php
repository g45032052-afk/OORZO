<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Exports\ProCustomerSubscriptionListExport;
use App\Exports\ProCustomerTransactionListExport;
use App\Http\Controllers\Controller;
use App\Models\DataSetting;
use App\Models\Order;
use App\Models\ProCustomerFaq;
use App\Models\ProCustomerSubscription;
use App\Models\ProCustomerSubscriptionPlan;
use App\Models\ProCustomerTransaction;
use App\Models\User;
use App\Traits\ManagesProCustomerSubscription;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ProCustomerController extends Controller
{
    use ManagesProCustomerSubscription;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if ((int) Helpers::get_business_settings('pro_member_status') !== 1) {
                abort(404);
            }

            return $next($request);
        });
    }

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

    public function benefitsSetup()
    {
        $settings = $this->getBenefitsSettings();

        return view('admin-views.pro-customer.benefits-setup', compact('settings'));
    }

    public function benefitsSetupUpdate(Request $request)
    {
        $minStep = Helpers::getDecimalPlaces();

        $discountStatus = (int) $request->input('discount_status', 0);
        $deliveryStatus = (int) $request->input('delivery_fee_status', 0);
        $couponStatus = (int) $request->input('coupon_status', 0);

        if (($discountStatus + $deliveryStatus + $couponStatus) > 1) {
            Toastr::error(translate('messages.only_one_pro_customer_benefit_can_be_enabled_at_a_time'));
            return back()->withInput();
        }

        $rules = [
            'discount_status' => 'nullable|in:0,1',
            'delivery_fee_status' => 'nullable|in:0,1',
            'coupon_status' => 'nullable|in:0,1',
            'delivery_fee_offer_type' => 'nullable|in:full_free,partial_free',
        ];

        if ($discountStatus === 1) {
            $rules['discount_percentage'] = "required|numeric|min:{$minStep}|max:100";
            $rules['discount_max_amount'] = "required|numeric|min:{$minStep}";
            if ((int) $request->input('discount_min_order_status', 0) === 1) {
                $rules['discount_min_order_amount'] = "required|numeric|min:{$minStep}";
            }
        }

        if ($deliveryStatus === 1) {
            $offerType = $request->input('delivery_fee_offer_type', 'full_free');
            if ($offerType === 'full_free' && (int) $request->input('delivery_fee_min_order_status', 0) === 1) {
                $rules['delivery_fee_min_order_amount'] = "required|numeric|min:{$minStep}";
            }
            if ($offerType === 'partial_free') {
                $rules['delivery_charge_discount'] = "required|numeric|min:{$minStep}|max:100";
            }
        }

        $request->validate($rules);

        $payload = [
            'discount_status' => $discountStatus,
            'discount_percentage' => $request->input('discount_percentage'),
            'discount_max_amount' => $request->input('discount_max_amount'),
            'discount_min_order_amount' => $request->input('discount_min_order_amount'),
            'discount_min_order_status' => (int) $request->input('discount_min_order_status', 0),
            'delivery_fee_status' => $deliveryStatus,
            'delivery_fee_offer_type' => $request->input('delivery_fee_offer_type', 'full_free'),
            'delivery_fee_min_order_amount' => $request->input('delivery_fee_min_order_amount'),
            'delivery_fee_min_order_status' => (int) $request->input('delivery_fee_min_order_status', 0),
            'delivery_charge_discount' => $request->input('delivery_charge_discount'),
            'coupon_status' => $couponStatus,
        ];

        foreach ($payload as $key => $value) {
            Helpers::dataUpdateOrInsert(
                ['key' => $key, 'type' => self::SETTINGS_TYPE],
                ['value' => $value ?? '']
            );
        }

        Toastr::success(translate('messages.pro_customer_benefits_updated_successfully'));

        return back();
    }

    public function priceSetup()
    {
        $plans = ProCustomerSubscriptionPlan::orderBy('id', 'desc')->get();
        $language = getWebConfig('language');
        $defaultLang = str_replace('_', '-', app()->getLocale());

        return view('admin-views.pro-customer.price-setup', compact('plans', 'language', 'defaultLang'));
    }

    public function planStore(Request $request)
    {
        $minPrice = Helpers::getDecimalPlaces();

        $request->validate([
            'plan_name' => 'array',
            'plan_name.*' => 'nullable|string|max:70',
            'plan_name.0' => 'required|string|max:70',
            'plan_type' => 'required|in:free_trial,paid',
            'price' => $request->plan_type === 'paid' ? "required|numeric|min:{$minPrice}" : 'nullable|numeric|min:0',
            'duration' => 'required|integer|min:1|max:3650',
        ], [
            'plan_name.0.required' => translate('messages.default_plan_name_is_required'),
            'plan_name.0.max' => translate('messages.plan_name_cannot_exceed_70_characters'),
            'plan_name.*.max' => translate('messages.plan_name_cannot_exceed_70_characters'),
            'duration.max' => translate('messages.duration_cannot_exceed_10_years'),
            'price.required' => translate('messages.paid_plan_price_must_be_greater_than_zero'),
            'price.min' => translate('messages.paid_plan_price_must_be_greater_than_zero'),
        ]);

        $defaultIndex = array_search('default', $request->lang ?? ['default']);
        $plan = new ProCustomerSubscriptionPlan;
        $plan->plan_name = $request->plan_name[$defaultIndex] ?? $request->plan_name[0];
        $plan->plan_type = $request->plan_type;
        $plan->price = $request->plan_type === 'free_trial' ? 0 : $request->price;
        $plan->duration = $request->duration;
        $plan->status = 1;
        $plan->save();

        if ($request->has('lang')) {
            Helpers::add_or_update_translations(
                request: $request,
                key_data: 'plan_name',
                name_field: 'plan_name',
                model_name: 'ProCustomerSubscriptionPlan',
                data_id: $plan->id,
                data_value: $plan->plan_name
            );
        }

        Toastr::success(translate('messages.pro_customer_plan_added_successfully'));
        return back();
    }

    public function planEdit($id)
    {
        $plan = ProCustomerSubscriptionPlan::withoutGlobalScope('translate')
            ->with('translations')
            ->findOrFail($id);

        return response()->json($plan);
    }

    public function planUpdate(Request $request, $id)
    {
        $plan = ProCustomerSubscriptionPlan::findOrFail($id);

        $minPrice = Helpers::getDecimalPlaces();

        $request->validate([
            'plan_name' => 'array',
            'plan_name.*' => 'nullable|string|max:70',
            'plan_name.0' => 'required|string|max:70',
            'plan_type' => 'required|in:free_trial,paid',
            'price' => $request->plan_type === 'paid' ? "required|numeric|min:{$minPrice}" : 'nullable|numeric|min:0',
            'duration' => 'required|integer|min:1|max:3650',
        ], [
            'plan_name.0.required' => translate('messages.default_plan_name_is_required'),
            'plan_name.0.max' => translate('messages.plan_name_cannot_exceed_70_characters'),
            'plan_name.*.max' => translate('messages.plan_name_cannot_exceed_70_characters'),
            'duration.max' => translate('messages.duration_cannot_exceed_10_years'),
            'price.required' => translate('messages.paid_plan_price_must_be_greater_than_zero'),
            'price.min' => translate('messages.paid_plan_price_must_be_greater_than_zero'),
        ]);

        $defaultIndex = array_search('default', $request->lang ?? ['default']);
        $plan->plan_name = $request->plan_name[$defaultIndex] ?? $request->plan_name[0];
        $plan->plan_type = $request->plan_type;
        $plan->price = $request->plan_type === 'free_trial' ? 0 : $request->price;
        $plan->duration = $request->duration;
        $plan->save();

        if ($request->has('lang')) {
            Helpers::add_or_update_translations(
                request: $request,
                key_data: 'plan_name',
                name_field: 'plan_name',
                model_name: 'ProCustomerSubscriptionPlan',
                data_id: $plan->id,
                data_value: $plan->plan_name
            );
        }

        Toastr::success(translate('messages.pro_customer_plan_updated_successfully'));
        return back();
    }

    public function planStatus($id, $status)
    {
        $plan = ProCustomerSubscriptionPlan::findOrFail($id);
        $plan->status = (int) $status;
        $plan->save();

        Toastr::success(translate('messages.pro_customer_plan_status_updated_successfully'));
        return back();
    }

    public function planDestroy($id)
    {
        $plan = ProCustomerSubscriptionPlan::findOrFail($id);
        $plan->translations()->delete();
        $plan->delete();

        Toastr::success(translate('messages.pro_customer_plan_deleted_successfully'));
        return back();
    }

    public function customerList(Request $request)
    {
        $stats = $this->buildSubscriptionStats();

        $query = $this->buildSubscriptionQuery($request);
        $subscriptions = $query->paginate(config('default_pagination'))->withQueryString();

        $plans = ProCustomerSubscriptionPlan::orderBy('id')->get(['id', 'plan_name']);

        return view('admin-views.pro-customer.list', compact('stats', 'subscriptions', 'plans'));
    }

    public function customerExport(Request $request)
    {
        $subscriptions = $this->buildSubscriptionQuery($request)->get();
        $stats = $this->buildSubscriptionStats();

        $planFilterName = null;
        if ($request->filled('plan_id')) {
            $planFilterName = optional(ProCustomerSubscriptionPlan::find($request->plan_id))->plan_name;
        }

        $data = [
            'subscriptions' => $subscriptions,
            'search' => $request->input('search'),
            'tab' => $request->input('tab', 'all'),
            'plan_id' => $request->input('plan_id'),
            'plan_name' => $planFilterName,
            'subscription_status' => $request->input('subscription_status'),
            'renewal_status' => $request->input('renewal_status'),
            'dates' => $request->input('dates'),
            'stats' => $stats,
        ];

        if ($request->type == 'excel') {
            return Excel::download(new ProCustomerSubscriptionListExport($data), 'ProCustomerSubscriptions.xlsx');
        } else if ($request->type == 'csv') {
            return Excel::download(new ProCustomerSubscriptionListExport($data), 'ProCustomerSubscriptions.csv');
        }

        return back();
    }

    private function buildSubscriptionQuery(Request $request)
    {
        $search = $request->input('search');
        $tab = $request->input('tab', 'all');
        $planId = $request->input('plan_id');
        $subscriptionStatus = $request->input('subscription_status');
        $renewalStatus = $request->input('renewal_status');
        $dates = $request->input('dates');

        $query = ProCustomerSubscription::query()
            ->with(['user', 'plan'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('f_name', 'like', "%{$search}%")
                        ->orWhere('l_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })->orWhere('plan_name', 'like', "%{$search}%");
            })
            ->when($tab === 'active', fn($q) => $q->where('status', 'active'))
            ->when($tab === 'expired', fn($q) => $q->where('status', 'expired'))
            ->when($subscriptionStatus, fn($q) => $q->where('status', $subscriptionStatus))
            ->when(!is_null($planId) && $planId !== '', fn($q) => $q->where('plan_id', $planId))
            ->when($renewalStatus === 'upcoming', fn($q) => $q->where('status', 'active')->whereBetween('end_at', [now(), now()->addDays(7)]))
            ->when($renewalStatus === 'expired', fn($q) => $q->where('end_at', '<', now()))
            ->orderByDesc('id');

        if ($dates) {
            [$from, $to] = $this->parseDateRange($dates);
            if ($from && $to) {
                $query->whereBetween('start_at', [$from, $to]);
            }
        }

        return $query;
    }

    private function buildSubscriptionStats(): array
    {
        $now = now();
        $twoMonthsAgo = $now->copy()->subMonths(2);
        $thirtyDaysAgo = $now->copy()->subDays(30);

        $existingPlans = [];
        foreach (ProCustomerSubscriptionPlan::orderBy('id')->get(['id', 'plan_name']) as $plan) {
            $existingPlans[(int) $plan->id] = $plan->plan_name;
        }

        $subGroups = ProCustomerSubscription::selectRaw("
                plan_id,
                MAX(plan_name) as plan_name,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status <> 'active' THEN 1 ELSE 0 END) as inactive,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as new_count
            ", [$twoMonthsAgo])
            ->groupBy('plan_id')
            ->get();

        $earnGroups = ProCustomerTransaction::where('payment_status', 'success')
            ->where('plan_type', 'paid')
            ->selectRaw("
                plan_id,
                MAX(plan_name) as plan_name,
                COALESCE(SUM(amount), 0) as earned,
                COALESCE(SUM(CASE WHEN paid_at >= ? THEN amount ELSE 0 END), 0) as earned_last_30
            ", [$thirtyDaysAgo])
            ->groupBy('plan_id')
            ->get();

        $subscriberBreakdown = $this->composePlanBreakdown(
            $subGroups, $existingPlans, ['total' => 'int', 'active' => 'int', 'inactive' => 'int']
        );
        $earningBreakdown = $this->composePlanBreakdown(
            $earnGroups, $existingPlans, ['earned' => 'float', 'earned_last_30' => 'float']
        );

        $sum = fn(array $rows, string $key) => array_sum(array_column($rows, $key));

        return [
            'total' => (int) $sum($subscriberBreakdown, 'total'),
            'active' => (int) $sum($subscriberBreakdown, 'active'),
            'inactive' => (int) $sum($subscriberBreakdown, 'inactive'),
            'new' => (int) $subGroups->sum('new_count'),
            'total_earned' => (float) $sum($earningBreakdown, 'earned'),
            'earned_last_30' => (float) $sum($earningBreakdown, 'earned_last_30'),
            'subscriber_breakdown' => $subscriberBreakdown,
            'earning_breakdown' => $earningBreakdown,
        ];
    }

    private function composePlanBreakdown($groups, array $existing, array $metrics): array
    {
        $cast = fn($value, string $type) => $type === 'float' ? (float) $value : (int) $value;

        $rows = [];
        foreach ($existing as $id => $name) {
            $row = ['plan_id' => $id, 'label' => $name];
            foreach ($metrics as $field => $type) {
                $row[$field] = $cast(0, $type);
            }
            $rows[$id] = $row;
        }

        $deleted = [];
        $other = null;

        foreach ($groups as $group) {
            $planId = $group->plan_id !== null ? (int) $group->plan_id : null;

            $values = ['plan_id' => $planId];
            foreach ($metrics as $field => $type) {
                $values[$field] = $cast($group->$field, $type);
            }

            if ($planId !== null && isset($rows[$planId])) {
                $rows[$planId] = ['label' => $rows[$planId]['label']] + $values;
            } elseif ($planId !== null) {
                $deleted[] = ['label' => $group->plan_name ?: translate('messages.Deleted_Plan')] + $values;
            } else {
                $other = ['label' => translate('messages.Other')] + $values;
            }
        }

        usort($deleted, fn($a, $b) => strcasecmp($a['label'], $b['label']));

        $ordered = array_values($rows);
        foreach ($deleted as $deletedRow) {
            $ordered[] = $deletedRow;
        }
        if ($other !== null) {
            $ordered[] = $other;
        }

        return $ordered;
    }

    private function parseDateRange($range): array
    {
        $parts = explode(' - ', $range);
        if (count($parts) !== 2) {
            return [null, null];
        }
        try {
            $from = Carbon::createFromFormat('m/d/Y', trim($parts[0]))->startOfDay();
            $to = Carbon::createFromFormat('m/d/Y', trim($parts[1]))->endOfDay();
            return [$from, $to];
        } catch (\Throwable $e) {
            return [null, null];
        }
    }

    public function subscriptionCancel($id)
    {
        $sub = ProCustomerSubscription::with('user')->findOrFail($id);
        $this->cancelProCustomerSubscription($sub);

        Toastr::success(translate('messages.pro_customer_subscription_canceled_successfully'));
        return back();
    }

    public function subscriptionStart(Request $request, $userId)
    {
        $request->validate([
            'plan_id' => 'required|exists:pro_customer_subscription_plans,id',
        ]);

        $user = User::findOrFail($userId);
        $plan = ProCustomerSubscriptionPlan::findOrFail($request->plan_id);

        return $this->applyAndRedirect($user, $plan, $request, 'start',
            translate('messages.pro_customer_subscription_started_successfully'));
    }

    public function subscriptionRenew(Request $request, $id)
    {
        $request->validate([
            'plan_id' => 'required|exists:pro_customer_subscription_plans,id',
        ]);

        $current = ProCustomerSubscription::with('user')->findOrFail($id);
        $plan = ProCustomerSubscriptionPlan::findOrFail($request->plan_id);
        $user = $current->user ?? User::findOrFail($current->user_id);

        return $this->applyAndRedirect($user, $plan, $request, 'renew',
            translate('messages.pro_customer_subscription_renewed_successfully'));
    }

    public function subscriptionShift(Request $request, $id)
    {
        $request->validate([
            'plan_id' => 'required|exists:pro_customer_subscription_plans,id',
        ]);

        $current = ProCustomerSubscription::with('user')->findOrFail($id);
        $plan = ProCustomerSubscriptionPlan::findOrFail($request->plan_id);

        if ((int) $current->plan_id === (int) $plan->id) {
            Toastr::error(translate('messages.shift_requires_a_different_plan'));
            return back();
        }

        $user = $current->user ?? User::findOrFail($current->user_id);

        return $this->applyAndRedirect($user, $plan, $request, 'shift',
            translate('messages.pro_customer_subscription_shifted_successfully'));
    }

    private function applyAndRedirect(User $user, ProCustomerSubscriptionPlan $plan, Request $request, string $mode, string $successMessage)
    {
        $payment = [];
        $isFreeTrial = $plan->plan_type === 'free_trial';

        if (!$isFreeTrial) {
            $request->validate([
                'payment_method' => 'required|in:wallet,manual',
            ]);
            $payment['payment_method'] = $request->input('payment_method');
        }

        try {
            $this->applyProCustomerPlan($user, $plan, $payment, $mode);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'insufficient_wallet_balance') {
                Toastr::error(translate('messages.customer_wallet_balance_is_insufficient_for_this_plan'));
                return back()->withInput();
            }
            throw $e;
        }

        Toastr::success($successMessage);
        return back();
    }

    public function transactions(Request $request)
    {
        $query = $this->buildTransactionQuery($request);
        $transactions = $query->paginate(config('default_pagination'))->withQueryString();

        $plans = ProCustomerSubscriptionPlan::orderBy('id')->get(['id', 'plan_name']);
        $paymentMethods = ProCustomerTransaction::query()
            ->whereNotNull('payment_method')
            ->where('payment_method', '!=', '')
            ->distinct()
            ->orderBy('payment_method')
            ->pluck('payment_method');

        $selectedCustomer = null;
        if ($request->filled('customer_id') && $request->customer_id !== 'all') {
            $selectedCustomer = User::find($request->customer_id);
        }

        return view('admin-views.pro-customer.transactions', compact('transactions', 'plans', 'paymentMethods', 'selectedCustomer'));
    }

    public function transactionExport(Request $request)
    {
        $transactions = $this->buildTransactionQuery($request)->get();

        $planFilterName = null;
        if ($request->filled('plan_id')) {
            $planFilterName = optional(ProCustomerSubscriptionPlan::find($request->plan_id))->plan_name;
        }

        $customerFilterName = null;
        if ($request->filled('customer_id') && $request->customer_id !== 'all') {
            $customer = User::find($request->customer_id);
            $customerFilterName = $customer ? trim(($customer->f_name ?? '') . ' ' . ($customer->l_name ?? '')) : null;
        }

        $data = [
            'transactions' => $transactions,
            'search' => $request->input('search'),
            'plan_id' => $request->input('plan_id'),
            'plan_name' => $planFilterName,
            'customer_id' => $request->input('customer_id'),
            'customer_name' => $customerFilterName,
            'plan_type' => $request->input('plan_type'),
            'payment_method' => $request->input('payment_method'),
            'dates' => $request->input('dates'),
        ];

        if ($request->type == 'excel') {
            return Excel::download(new ProCustomerTransactionListExport($data), 'ProCustomerTransactions.xlsx');
        } else if ($request->type == 'csv') {
            return Excel::download(new ProCustomerTransactionListExport($data), 'ProCustomerTransactions.csv');
        }

        return back();
    }

    private function buildTransactionQuery(Request $request)
    {
        $search = $request->input('search');
        $planId = $request->input('plan_id');
        $customerId = $request->input('customer_id');
        $planType = $request->input('plan_type');
        $paymentMethod = $request->input('payment_method');
        $dates = $request->input('dates');

        $query = ProCustomerTransaction::query()
            ->with(['user', 'plan', 'subscription'])
            ->when($search, function ($q) use ($search) {
                $needle = ltrim(trim($search), '#');
                $q->where(function ($qq) use ($search, $needle) {
                    $qq->whereHas('user', function ($uq) use ($search) {
                        $uq->where('f_name', 'like', "%{$search}%")
                            ->orWhere('l_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })->orWhere('plan_name', 'like', "%{$search}%")
                      ->orWhere('transaction_reference', 'like', "%{$search}%");

                    if (ctype_digit($needle)) {
                        $qq->orWhere('id', (int) $needle);
                    }
                });
            })
            ->when(!is_null($planId) && $planId !== '', fn($q) => $q->where('plan_id', $planId))
            ->when($customerId && $customerId !== 'all', fn($q) => $q->where('user_id', $customerId))
            ->when($planType, fn($q) => $q->where('plan_type', $planType))
            ->when($paymentMethod, fn($q) => $q->where('payment_method', $paymentMethod))
            ->orderByDesc('id');

        if ($dates) {
            [$from, $to] = $this->parseDateRange($dates);
            if ($from && $to) {
                $query->whereBetween('paid_at', [$from, $to]);
            }
        }

        return $query;
    }

    public function additionalSetup(Request $request)
    {
        $search = $request->input('search');
        $faqs = ProCustomerFaq::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('question', 'like', "%{$search}%")
                        ->orWhere('answer', 'like', "%{$search}%");
                });
            })
            ->orderBy('priority')
            ->paginate(config('default_pagination'))
            ->withQueryString();

        $faqCount = ProCustomerFaq::count();
        $language = getWebConfig('language');

        return view('admin-views.pro-customer.additional-setup', compact(
            'faqs', 'faqCount', 'language'
        ));
    }

    public function termsSetup()
    {
        $language = getWebConfig('language');

        $termsType = 'pro_customer_terms';
        $termsRows = DataSetting::where('type', $termsType)->get();
        $termsTitleRow = $termsRows->firstWhere('key', 'page_title');
        $termsDescRow = $termsRows->firstWhere('key', 'page_description');

        return view('admin-views.pro-customer.terms-and-conditions', compact(
            'language', 'termsTitleRow', 'termsDescRow'
        ));
    }

    public function faqStore(Request $request)
    {
        $request->validate([
            'question' => 'required|array',
            'question.0' => 'required|string|max:50',
            'question.*' => 'nullable|string|max:50',
            'answer' => 'required|array',
            'answer.0' => 'required|string|max:150',
            'answer.*' => 'nullable|string|max:150',
            'priority' => 'required|integer|min:1',
        ], [
            'question.0.required' => translate('messages.default_question_is_required'),
            'answer.0.required' => translate('messages.default_answer_is_required'),
            'question.*.max' => translate('messages.question_cannot_exceed_50_characters'),
            'answer.*.max' => translate('messages.answer_cannot_exceed_150_characters'),
        ]);

        $defaultIndex = array_search('default', $request->lang ?? ['default']);

        DB::transaction(function () use ($request, $defaultIndex) {
            $count = ProCustomerFaq::count();
            $newPriority = (int) $request->priority;
            if ($newPriority > $count + 1) {
                $newPriority = $count + 1;
            }

            // Cascade: bump everyone at or below the target slot down by 1.
            ProCustomerFaq::where('priority', '>=', $newPriority)->increment('priority');

            $faq = new ProCustomerFaq();
            $faq->question = $request->question[$defaultIndex] ?? $request->question[0];
            $faq->answer = $request->answer[$defaultIndex] ?? $request->answer[0];
            $faq->priority = $newPriority;
            $faq->status = 1;
            $faq->save();

            Helpers::add_or_update_translations(
                request: $request,
                key_data: 'pro_faq_question',
                name_field: 'question',
                model_name: 'ProCustomerFaq',
                data_id: $faq->id,
                data_value: $faq->getRawOriginal('question')
            );
            Helpers::add_or_update_translations(
                request: $request,
                key_data: 'pro_faq_answer',
                name_field: 'answer',
                model_name: 'ProCustomerFaq',
                data_id: $faq->id,
                data_value: $faq->getRawOriginal('answer')
            );
        });

        Toastr::success(translate('messages.faq_added_successfully'));
        return back();
    }

    public function faqEdit($id)
    {
        $faq = ProCustomerFaq::withoutGlobalScope('translate')
            ->with('translations')
            ->findOrFail($id);

        $translations = [];
        foreach ($faq->translations as $t) {
            $translations[$t->locale][$t->key] = $t->value;
        }

        return response()->json([
            'id' => $faq->id,
            'question' => $faq->getRawOriginal('question'),
            'answer' => $faq->getRawOriginal('answer'),
            'priority' => $faq->priority,
            'status' => $faq->status,
            'translations' => $translations,
        ]);
    }

    public function faqUpdate(Request $request, $id)
    {
        $request->validate([
            'question' => 'required|array',
            'question.0' => 'required|string|max:50',
            'question.*' => 'nullable|string|max:50',
            'answer' => 'required|array',
            'answer.0' => 'required|string|max:150',
            'answer.*' => 'nullable|string|max:150',
            'priority' => 'required|integer|min:1',
        ], [
            'question.0.required' => translate('messages.default_question_is_required'),
            'answer.0.required' => translate('messages.default_answer_is_required'),
            'question.*.max' => translate('messages.question_cannot_exceed_50_characters'),
            'answer.*.max' => translate('messages.answer_cannot_exceed_150_characters'),
        ]);

        $defaultIndex = array_search('default', $request->lang ?? ['default']);

        DB::transaction(function () use ($request, $id, $defaultIndex) {
            $faq = ProCustomerFaq::lockForUpdate()->findOrFail($id);

            $count = ProCustomerFaq::count();
            $oldPriority = (int) $faq->priority;
            $newPriority = (int) $request->priority;
            if ($newPriority > $count) {
                $newPriority = $count;
            }

            if ($newPriority < $oldPriority) {
                ProCustomerFaq::whereBetween('priority', [$newPriority, $oldPriority - 1])
                    ->where('id', '!=', $faq->id)
                    ->increment('priority');
            } elseif ($newPriority > $oldPriority) {
                ProCustomerFaq::whereBetween('priority', [$oldPriority + 1, $newPriority])
                    ->where('id', '!=', $faq->id)
                    ->decrement('priority');
            }

            $faq->question = $request->question[$defaultIndex] ?? $request->question[0];
            $faq->answer = $request->answer[$defaultIndex] ?? $request->answer[0];
            $faq->priority = $newPriority;
            $faq->save();

            Helpers::add_or_update_translations(
                request: $request,
                key_data: 'pro_faq_question',
                name_field: 'question',
                model_name: 'ProCustomerFaq',
                data_id: $faq->id,
                data_value: $faq->getRawOriginal('question')
            );
            Helpers::add_or_update_translations(
                request: $request,
                key_data: 'pro_faq_answer',
                name_field: 'answer',
                model_name: 'ProCustomerFaq',
                data_id: $faq->id,
                data_value: $faq->getRawOriginal('answer')
            );
        });

        Toastr::success(translate('messages.faq_updated_successfully'));
        return back();
    }

    public function faqStatus($id, $status)
    {
        $faq = ProCustomerFaq::findOrFail($id);
        $faq->status = (int) $status;
        $faq->save();

        Toastr::success(translate('messages.faq_status_updated'));
        return back();
    }

    public function faqDestroy($id)
    {
        DB::transaction(function () use ($id) {
            $faq = ProCustomerFaq::lockForUpdate()->findOrFail($id);
            $deletedPriority = (int) $faq->priority;

            $faq->translations()->delete();
            $faq->delete();

            ProCustomerFaq::where('priority', '>', $deletedPriority)->decrement('priority');
        });

        Toastr::success(translate('messages.faq_deleted_successfully'));
        return back();
    }

    public function termsUpdate(Request $request)
    {
        $request->validate([
            'page_title' => 'required|array',
            'page_title.0' => 'required|string|max:70',
            'page_title.*' => 'nullable|string|max:70',
            'page_description' => 'required|array',
            'page_description.0' => 'required|string',
        ]);

        $defaultIndex = array_search('default', $request->lang ?? ['default']);

        $titleDefault = $request->page_title[$defaultIndex] ?? $request->page_title[0];
        $descDefault = $request->page_description[$defaultIndex] ?? $request->page_description[0];

        $type = 'pro_customer_terms';

        Helpers::dataUpdateOrInsert(
            ['key' => 'page_title', 'type' => $type],
            ['value' => $titleDefault]
        );
        $titleRow = DataSetting::where(['key' => 'page_title', 'type' => $type])->first();

        Helpers::dataUpdateOrInsert(
            ['key' => 'page_description', 'type' => $type],
            ['value' => $descDefault]
        );
        $descRow = DataSetting::where(['key' => 'page_description', 'type' => $type])->first();

        if ($titleRow) {
            Helpers::add_or_update_translations(
                request: $request,
                key_data: 'pro_terms_page_title',
                name_field: 'page_title',
                model_name: DataSetting::class,
                data_id: $titleRow->id,
                data_value: $titleRow->getRawOriginal('value'),
                model_class: true
            );
        }
        if ($descRow) {
            Helpers::add_or_update_translations(
                request: $request,
                key_data: 'pro_terms_page_description',
                name_field: 'page_description',
                model_name: DataSetting::class,
                data_id: $descRow->id,
                data_value: $descRow->getRawOriginal('value'),
                model_class: true
            );
        }

        Toastr::success(translate('messages.pro_customer_terms_updated_successfully'));
        return back();
    }

    private function getBenefitsSettings(): array
    {
        $rows = DataSetting::where('type', self::SETTINGS_TYPE)
            ->whereIn('key', self::BENEFITS_KEYS)
            ->pluck('value', 'key')
            ->toArray();

        $defaults = array_fill_keys(self::BENEFITS_KEYS, null);
        $defaults['delivery_fee_offer_type'] = 'full_free';

        $merged = [...$defaults, ...$rows];

        // Exactly one benefit must be enabled. If none yet, default to Discount.
        $anyOn = ((int) ($merged['discount_status'] ?? 0))
            + ((int) ($merged['delivery_fee_status'] ?? 0))
            + ((int) ($merged['coupon_status'] ?? 0));
        if ($anyOn === 0) {
            $merged['discount_status'] = 1;
        }

        return $merged;
    }
}
