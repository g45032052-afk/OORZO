<?php

namespace App\Traits;

use App\Models\Expense;
use App\Models\OrderTransaction;
use App\Models\ProCustomerTransaction;
use App\Models\Restaurant;
use App\Models\SubscriptionTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait ReportGeneratorTrait
{
    public function getAdminTotalEarningQuery() {
        return "
                SUM(
                    (
                        (
                            orders.order_amount
                            - order_transactions.additional_charge
                            - orders.dm_tips
                            - orders.delivery_charge
                            - order_transactions.tax
                            - orders.extra_packaging_amount
                            - orders.delivery_type_charge
                            + orders.coupon_discount_amount
                            + orders.restaurant_discount_amount
                            + orders.ref_bonus_amount
                            + order_transactions.pro_discount
                        ) * order_transactions.commission_percentage / 100
                    )
                    + order_transactions.delivery_fee_comission
                    + order_transactions.additional_charge
                    + (CASE WHEN orders.delivery_type = 'express' THEN orders.delivery_type_charge ELSE 0 END)
                )
            ";
    }

    public function buildAdminEarningSummary($filter, $from, $to)
    {
        $earningFormula = $this->getAdminTotalEarningQuery();
        $previousPeriodRange = $this->getPreviousPeriodRange($filter, $from, $to);

        $baseTransactionQuery = OrderTransaction::query()->whereNull('status')
            ->join('orders', 'orders.id', '=', 'order_transactions.order_id');

        // current & previous earnings
        $admin_earning = (clone $baseTransactionQuery)
            ->applyDateFilter($filter, $from, $to, 'order_transactions.created_at')
            ->selectRaw($earningFormula . " as admin_earning")
            ->value('admin_earning') ?? 0;

        $admin_previous_earning = 0;
        if ($previousPeriodRange) {
            $admin_previous_earning = (clone $baseTransactionQuery)
                ->whereBetween('order_transactions.created_at', $previousPeriodRange)
                ->selectRaw($earningFormula . " as admin_earning")
                ->value('admin_earning') ?? 0;
        }

        // expenses
        $expenseQuery = Expense::where('created_by', 'admin');

        $admin_expense = (float) (clone $expenseQuery)
            ->applyDateFilter($filter, $from, $to, 'expenses.created_at')
            ->sum('amount');

        $admin_previous_expense = 0;
        if ($previousPeriodRange) {
            $admin_previous_expense = (clone $expenseQuery)
                ->whereBetween('expenses.created_at', $previousPeriodRange)
                ->sum('amount');
        }

        // subscriptions
        $subscriptionQuery = SubscriptionTransaction::where('is_trial', 0)
            ->where('payment_status', 'success');

        $subscription_earning = (clone $subscriptionQuery)
            ->applyDateFilter($filter, $from, $to, 'subscription_transactions.created_at')
            ->sum('paid_amount');

        $subscription_previous_earning = 0;
        if ($previousPeriodRange) {
            $subscription_previous_earning = (clone $subscriptionQuery)
                ->whereBetween('subscription_transactions.created_at', $previousPeriodRange)
                ->sum('paid_amount');
        }

        $admin_earning += $subscription_earning;
        $admin_previous_earning += $subscription_previous_earning;

        // pro customer subscriptions
        $proCustomerQuery = ProCustomerTransaction::where('payment_status', 'success')
            ->where('plan_type', 'paid');

        $pro_customer_subscription = (clone $proCustomerQuery)
            ->applyDateFilter($filter, $from, $to, 'pro_customer_transactions.created_at')
            ->sum('amount');

        $pro_customer_subscription_previous = 0;
        if ($previousPeriodRange) {
            $pro_customer_subscription_previous = (clone $proCustomerQuery)
                ->whereBetween('pro_customer_transactions.created_at', $previousPeriodRange)
                ->sum('amount');
        }

        $admin_earning += $pro_customer_subscription;
        $admin_previous_earning += $pro_customer_subscription_previous;

        $net_profit = $admin_earning - $admin_expense;
        $previous_net_profit = $admin_previous_earning - $admin_previous_expense;

        [$admin_earning_percentage, $admin_earning_positive] =
            $this->calculatePercentageData($admin_earning, $admin_previous_earning);

        [$net_profit_percentage, $net_profit_positive] =
            $this->calculatePercentageData($net_profit, $previous_net_profit);

        [$admin_expense_percentage, $admin_expense_positive] =
            $this->calculatePercentageData($admin_expense, $admin_previous_expense);

        [$subscription_percentage, $subscription_positive] =
            $this->calculatePercentage($subscription_earning, $admin_earning);

        [$pro_customer_subscription_percentage, $pro_customer_subscription_positive] =
            $this->calculatePercentage($pro_customer_subscription, $admin_earning);

        return [
            'admin_earning' => $admin_earning,
            'admin_previous_earning' => $admin_previous_earning,
            'admin_earning_positive' => $admin_earning_positive,
            'admin_earning_percentage' => $admin_earning_percentage,

            'admin_expense' => $admin_expense,
            'admin_expense_percentage' => $admin_expense_percentage,
            'admin_previous_expense' => $admin_previous_expense,
            'admin_expense_positive' => $admin_expense_positive,

            'net_profit' => $net_profit,
            'previous_net_profit' => $previous_net_profit,
            'net_profit_percentage' => $net_profit_percentage,
            'net_profit_positive' => $net_profit_positive,

            'subscription_earning' => $subscription_earning,
            'subscription_previous_earning' => $subscription_previous_earning,
            'subscription_percentage' => $subscription_percentage,
            'subscription_positive' => $subscription_positive,

            'pro_customer_subscription' => $pro_customer_subscription,
            'pro_customer_subscription_previous' => $pro_customer_subscription_previous,
            'pro_customer_subscription_percentage' => $pro_customer_subscription_percentage,
            'pro_customer_subscription_positive' => $pro_customer_subscription_positive,
        ];
    }

    private function getPreviousPeriodRange($filter, $from, $to): ?array
    {
        $now = Carbon::now();

        if ($filter === 'custom' && $from && $to) {
            $currentStart = Carbon::parse($from)->startOfDay();
            $currentEnd = Carbon::parse($to)->endOfDay();
        } elseif ($filter === 'this_month') {
            $currentStart = $now->copy()->startOfMonth();
            $currentEnd = $now->copy();
        } elseif ($filter === 'this_year') {
            $currentStart = $now->copy()->startOfYear();
            $currentEnd = $now->copy();
        } elseif ($filter === 'this_week') {
            $currentStart = $now->copy()->startOfWeek();
            $currentEnd = $now->copy();
        } elseif ($filter === 'previous_year') {
            $currentStart = $now->copy()->subYear()->startOfYear();
            $currentEnd = $now->copy()->subYear();
        } else {
            return null;
        }

        if ($currentEnd->lt($currentStart)) {
            return null;
        }

        $durationInSeconds = $currentStart->diffInSeconds($currentEnd);
        $previousEnd = $currentStart->copy()->subSecond();
        $previousStart = $previousEnd->copy()->subSeconds($durationInSeconds);

        return [
            $previousStart->format('Y-m-d H:i:s'),
            $previousEnd->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Detailed earnings breakdown (order commission, delivery fee, additional charge)
     */
    public function buildEarningBreakdown($filter, $from, $to, $admin_earning)
    {
        $baseTransactionQuery = OrderTransaction::query()->whereNull('status')
            ->join('orders', 'orders.id', '=', 'order_transactions.order_id');

        $earning_data = (clone $baseTransactionQuery)
            ->applyDateFilter($filter, $from, $to,'order_transactions.created_at')
            ->selectRaw("
            SUM(
                (
                    (
                        orders.order_amount
                        - order_transactions.additional_charge
                        - orders.dm_tips
                        - orders.delivery_charge
                        - order_transactions.tax
                        - orders.extra_packaging_amount
                        - orders.delivery_type_charge
                        + orders.coupon_discount_amount
                        + orders.restaurant_discount_amount
                        + orders.ref_bonus_amount
                        + order_transactions.pro_discount
                    ) * order_transactions.commission_percentage / 100
                )
            ) as admin_earning,
            SUM(order_transactions.delivery_fee_comission) as delivery_fee_comission,
            SUM(order_transactions.additional_charge) as additional_charge,
            SUM(CASE WHEN orders.delivery_type = 'express' THEN orders.delivery_type_charge ELSE 0 END) as express_charge ")
            ->first();
        [$order_commission_percentage, $order_commission_positive] =
            $this->calculatePercentage($earning_data->admin_earning, $admin_earning);

        [$delivery_fee_comission_percentage, $delivery_fee_comission_positive] =
            $this->calculatePercentage($earning_data->delivery_fee_comission, $admin_earning);

        [$additional_charge_percentage, $additional_charge_positive] =
            $this->calculatePercentage($earning_data->additional_charge, $admin_earning);

        [$express_charge_percentage, $express_charge_positive] =
            $this->calculatePercentage($earning_data->express_charge, $admin_earning);

        return [
            'order_commission' =>round( $earning_data->admin_earning , config('round_up_to_digit')),
            'order_commission_percentage' =>  $order_commission_percentage,
            'delivery_fee_comission' =>  round($earning_data->delivery_fee_comission, config('round_up_to_digit')),
            'delivery_fee_comission_percentage' => $delivery_fee_comission_percentage,
            'additional_charge' => round($earning_data->additional_charge, config('round_up_to_digit')),
            'additional_charge_percentage' => $additional_charge_percentage,
            'express_charge' => round($earning_data->express_charge, config('round_up_to_digit')),
            'express_charge_percentage' => $express_charge_percentage,
        ];
    }


    public function buildExpenseBreakdown($filter, $from, $to, $admin_expense)
    {
        $expenseQuery = Expense::where('created_by', 'admin');

        $all_expense = (clone $expenseQuery)
            ->applyDateFilter($filter, $from, $to,'expenses.created_at')
            ->selectRaw("
            SUM(CASE WHEN expenses.type = 'free_delivery' THEN expenses.amount ELSE 0 END) as free_delivery,
            SUM(CASE WHEN expenses.type = 'pro_free_delivery' THEN expenses.amount ELSE 0 END) as pro_free_delivery,
            SUM(CASE WHEN expenses.type = 'partial_free_delivery' THEN expenses.amount ELSE 0 END) as partial_free_delivery,
            SUM(CASE WHEN expenses.type = 'pro_partial_free_delivery' THEN expenses.amount ELSE 0 END) as pro_partial_free_delivery,
            SUM(CASE WHEN expenses.type = 'coupon_discount' THEN expenses.amount ELSE 0 END) as coupon_discount,
            SUM(CASE WHEN expenses.type = 'pro_coupon_discount' THEN expenses.amount ELSE 0 END) as pro_coupon_discount,
            SUM(CASE WHEN expenses.type = 'discount_on_product' THEN expenses.amount ELSE 0 END) as discount_on_product,
            SUM(CASE WHEN expenses.type = 'pro_discount_on_product' THEN expenses.amount ELSE 0 END) as pro_discount_on_product,
            SUM(CASE WHEN expenses.type = 'add_fund_bonus' THEN expenses.amount ELSE 0 END) as add_fund_bonus,
            SUM(CASE WHEN expenses.type = 'dm_admin_bonus' THEN expenses.amount ELSE 0 END) as dm_admin_bonus,
            SUM(CASE WHEN expenses.type = 'incentive' THEN expenses.amount ELSE 0 END) as incentive,
            SUM(CASE WHEN expenses.type = 'slightly_delay_delivery_charge' THEN expenses.amount ELSE 0 END) as slightly_delay,
            SUM(CASE WHEN expenses.type = 'CashBack' THEN expenses.amount ELSE 0 END) as cashback,
            SUM(CASE WHEN expenses.type = 'referral_discount' THEN expenses.amount ELSE 0 END) as referral_discount")
            ->first();

        // Fold Pro Customer delivery/coupon subsidies into their natural buckets so the
        // breakdown reconciles to the admin_expense total. Legacy rows created before the
        // pro_* rename keep the old type strings and are still summed via the base buckets.
        $total_free_delivery = $all_expense->free_delivery + $all_expense->pro_free_delivery;
        $total_partial_free_delivery = $all_expense->partial_free_delivery + $all_expense->pro_partial_free_delivery;
        $total_coupon_discount = $all_expense->coupon_discount + $all_expense->pro_coupon_discount;

        [$free_delivery_percentage, $free_delivery_positive] =
            $this->calculatePercentage($total_free_delivery, $admin_expense);

        [$partial_free_delivery_percentage, $partial_free_delivery_positive] =
            $this->calculatePercentage($total_partial_free_delivery, $admin_expense);

        [$coupon_discount_percentage, $coupon_discount_positive] =
            $this->calculatePercentage($total_coupon_discount, $admin_expense);

        [$discount_on_product_percentage, $discount_on_product_positive] =
            $this->calculatePercentage($all_expense->discount_on_product, $admin_expense);

        [$pro_discount_on_product_percentage, $pro_discount_on_product_positive] =
            $this->calculatePercentage($all_expense->pro_discount_on_product, $admin_expense);

        [$add_fund_bonus_percentage, $add_fund_bonus_positive] =
            $this->calculatePercentage($all_expense->add_fund_bonus, $admin_expense);

        [$cashback_percentage, $cashback_positive] =
            $this->calculatePercentage($all_expense->cashback, $admin_expense);

        [$delay_percentage, $delay_positive] =
            $this->calculatePercentage($all_expense->slightly_delay, $admin_expense);

        [$other_percentage, $other_positive] =
            $this->calculatePercentage(
                $all_expense->dm_admin_bonus + $all_expense->incentive + $all_expense->referral_discount,
                $admin_expense
            );

        return [
            'free_delivery'=> $total_free_delivery,
            'free_delivery_percentage'=> $free_delivery_percentage,
            'partial_free_delivery'=> $total_partial_free_delivery,
            'partial_free_delivery_percentage'=> $partial_free_delivery_percentage,
            'discount_on_product'=> $all_expense->discount_on_product,
            'discount_on_product_percentage'=> $discount_on_product_percentage,
            'pro_discount_on_product'=> $all_expense->pro_discount_on_product,
            'pro_discount_on_product_percentage'=> $pro_discount_on_product_percentage,
            'coupon_discount'=> $total_coupon_discount,
            'coupon_discount_percentage'=> $coupon_discount_percentage,
            'add_fund_bonus'=> $all_expense->add_fund_bonus,
            'add_fund_bonus_percentage'=> $add_fund_bonus_percentage,
            'cashback'=> $all_expense->cashback,
            'cashback_percentage'=> $cashback_percentage,
            'slightly_delay' => $all_expense->slightly_delay,
            'slightly_delay_percentage' => $delay_percentage,
            'other'=> $all_expense->dm_admin_bonus + $all_expense->incentive + $all_expense->referral_discount,
            'other_percentage'=> $other_percentage,
        ];
    }


    private function calculatePercentageData($current, $previous)
    {
        if ($previous == 0) {
            if ($current == 0) {
                return [0, false];
            }
            return [100, true];
        }

        $percentage = (($current - $previous) / abs($previous)) * 100;
        $percentage =round($percentage ,2);
        return [$percentage, $percentage >= 0];
    }

    private function calculatePercentage($part, $total)
    {
        if ($total == 0) return [0, false];

        $percentage = ($part / $total) * 100;

        $percentage =round($percentage ,2);
        return [$percentage, true];
    }

    public function get_restaurant_earning_summary_data($restaurant_id, $filter, $from, $to)
    {
        $previousPeriodRange = $this->getPreviousPeriodRange($filter, $from, $to);
        $restaurantExpenseQuery = Expense::query()
            ->when($restaurant_id !== 'all' && $restaurant_id !== null, function ($query) use ($restaurant_id) {
                return $query->where('restaurant_id', Restaurant::where('vendor_id', $restaurant_id)->value('id'));
            });

        $baseQuery = OrderTransaction::join('orders', 'orders.id', '=', 'order_transactions.order_id')
            ->NotRefunded()
            ->when($restaurant_id !== 'all' && $restaurant_id !== null, function ($query) use ($restaurant_id) {
                return $query->where('order_transactions.vendor_id', $restaurant_id);
            });

        $earningFormula = "
            SUM(
                (
                    orders.order_amount
                    - order_transactions.additional_charge
                    - orders.dm_tips
                    - orders.delivery_charge
                    - order_transactions.tax
                    - orders.extra_packaging_amount
                    - orders.delivery_type_charge
                    + orders.coupon_discount_amount
                    + orders.restaurant_discount_amount
                    + orders.ref_bonus_amount
                    + order_transactions.pro_discount
                ) * (1 - order_transactions.commission_percentage / 100)
            ) as order_sales,
            SUM(order_transactions.tax) as tax_collected,
            SUM(orders.extra_packaging_amount) as packaging_fee_collected,
            SUM(CASE
                WHEN orders.free_delivery_by = 'vendor'
                THEN orders.original_delivery_charge ELSE 0
            END) as free_delivery_amount,

            SUM(
                (
                    orders.order_amount
                    - order_transactions.additional_charge
                    - orders.dm_tips
                    - orders.delivery_charge
                    - order_transactions.tax
                    - orders.extra_packaging_amount
                    - orders.delivery_type_charge
                    + orders.coupon_discount_amount
                    + orders.restaurant_discount_amount
                    + orders.ref_bonus_amount
                    + order_transactions.pro_discount
                ) * order_transactions.commission_percentage / 100
            ) as admin_commission,

            SUM(order_transactions.restaurant_expense) as restaurant_expense,

            SUM(CASE
                WHEN orders.coupon_created_by = 'vendor'
                THEN orders.coupon_discount_amount ELSE 0
            END) as coupon_contribution,

            SUM(CASE
                WHEN orders.discount_on_product_by = 'vendor'
                THEN orders.restaurant_discount_amount ELSE 0
            END) as product_discount,

            SUM(order_transactions.additional_charge) as service_charge_paid,
            SUM(order_transactions.tax) as tax_payments,

            COUNT(DISTINCT order_transactions.id) as total_orders
        ";

        $current_data = (clone $baseQuery)
            ->applyDateFilter($filter, $from, $to, 'order_transactions.created_at')
            ->selectRaw($earningFormula)
            ->first();

        $previous_data = null;
        if ($previousPeriodRange) {
            $previous_data = (clone $baseQuery)
                ->whereBetween('order_transactions.created_at', $previousPeriodRange)
                ->selectRaw($earningFormula)
                ->first();
        }
        $previous_data = $previous_data ?? (object) [];

        $current_expense_breakdown = (clone $restaurantExpenseQuery)
            ->applyDateFilter($filter, $from, $to, 'created_at')
            ->selectRaw("
                SUM(CASE WHEN type = 'discount_on_product' AND created_by = 'vendor' THEN amount ELSE 0 END) as product_discount,
                SUM(CASE WHEN type = 'bogo_discount' AND created_by = 'vendor' THEN amount ELSE 0 END) as bogo_discount,
                SUM(CASE WHEN type = 'happy_hour_discount' AND created_by = 'vendor' THEN amount ELSE 0 END) as happy_hour_discount,
                SUM(CASE WHEN type = 'coupon_discount' AND created_by = 'vendor' THEN amount ELSE 0 END) as coupon_contribution,
                SUM(CASE WHEN type = 'free_delivery' AND created_by = 'vendor' THEN amount ELSE 0 END) as free_delivery
            ")
            ->first();

        $previous_expense_breakdown = null;
        if ($previousPeriodRange) {
            $previous_expense_breakdown = (clone $restaurantExpenseQuery)
                ->whereBetween('created_at', $previousPeriodRange)
                ->selectRaw("
                    SUM(CASE WHEN type = 'discount_on_product' AND created_by = 'vendor' THEN amount ELSE 0 END) as product_discount,
                SUM(CASE WHEN type = 'bogo_discount' AND created_by = 'vendor' THEN amount ELSE 0 END) as bogo_discount,
                SUM(CASE WHEN type = 'happy_hour_discount' AND created_by = 'vendor' THEN amount ELSE 0 END) as happy_hour_discount,
                    SUM(CASE WHEN type = 'coupon_discount' AND created_by = 'vendor' THEN amount ELSE 0 END) as coupon_contribution,
                    SUM(CASE WHEN type = 'free_delivery' AND created_by = 'vendor' THEN amount ELSE 0 END) as free_delivery
                ")
                ->first();
        }
        $previous_expense_breakdown = $previous_expense_breakdown ?? (object) [];

        // Subscription Fees
        $subQuery = SubscriptionTransaction::where('payment_status', 'success')
            ->when($restaurant_id !== 'all' && $restaurant_id !== null, function ($query) use ($restaurant_id) {
                return $query->where('restaurant_id', $restaurant_id);
            });

        $current_subs_data = (clone $subQuery)
            ->applyDateFilter($filter, $from, $to, 'created_at')
            ->selectRaw("SUM(paid_amount) as total_amount, COUNT(id) as total_count")
            ->first();

        $previous_subs_data = null;
        if ($previousPeriodRange) {
            $previous_subs_data = (clone $subQuery)
                ->whereBetween('created_at', $previousPeriodRange)
                ->selectRaw("SUM(paid_amount) as total_amount, COUNT(id) as total_count")
                ->first();
        }
        $previous_subs_data = $previous_subs_data ?? (object) [];

        $current_subs = $current_subs_data->total_amount ?? 0;
        $previous_subs = $previous_subs_data->total_amount ?? 0;

        // Expenses count (from separate Expense table)
        $expenseQuery = Expense::when($restaurant_id !== 'all' && $restaurant_id !== null, function ($query) use ($restaurant_id) {
            return $query->where('restaurant_id', $restaurant_id);
        });

        $current_expense_count = (clone $expenseQuery)
            ->applyDateFilter($filter, $from, $to, 'created_at')
            ->count();

        $previous_expense_count = 0;
        if ($previousPeriodRange) {
            $previous_expense_count = (clone $expenseQuery)
                ->whereBetween('created_at', $previousPeriodRange)
                ->count();
        }

        $current_product_discount = $current_expense_breakdown->product_discount ?? 0;
        $previous_product_discount = $previous_expense_breakdown->product_discount ?? 0;
        // Counted in the totals exactly like the product discount - it is the restaurant's
        // cost either way - but reported on its own line so the two can be told apart.
        $current_bogo_discount = $current_expense_breakdown->bogo_discount ?? 0;
        $previous_bogo_discount = $previous_expense_breakdown->bogo_discount ?? 0;
        // The same treatment for the same reason: a happy hour is the restaurant's own cost,
        // so it belongs in the totals, but on its own line rather than inside the product
        // discount it used to be filed under.
        $current_happy_hour_discount = $current_expense_breakdown->happy_hour_discount ?? 0;
        $previous_happy_hour_discount = $previous_expense_breakdown->happy_hour_discount ?? 0;
        $current_coupon_contribution = $current_expense_breakdown->coupon_contribution ?? 0;
        $previous_coupon_contribution = $previous_expense_breakdown->coupon_contribution ?? 0;
        $current_free_delivery = $current_expense_breakdown->free_delivery ?? 0;
        $previous_free_delivery = $previous_expense_breakdown->free_delivery ?? 0;

        $current_earnings = ($current_data->order_sales ?? 0) + ($current_data->tax_collected ?? 0) + ($current_data->packaging_fee_collected ?? 0) + ($current_data->admin_commission ?? 0);
        $current_expenses = ($current_data->admin_commission ?? 0) + $current_product_discount + $current_bogo_discount + $current_happy_hour_discount + ($current_data->restaurant_expense ?? 0) + $current_subs;
        $current_net_profit = $current_earnings - $current_expenses;

        $previous_earnings = ($previous_data->order_sales ?? 0) + ($previous_data->tax_collected ?? 0) + ($previous_data->packaging_fee_collected ?? 0) + ($previous_data->admin_commission ?? 0);
        $previous_expenses = ($previous_data->admin_commission ?? 0) + $previous_product_discount + $previous_bogo_discount + $previous_happy_hour_discount + ($previous_data->restaurant_expense ?? 0) + $previous_subs;
        $previous_net_profit = $previous_earnings - $previous_expenses;

        $current_transaction_earning = $current_data->total_orders ?? 0;
        $current_transaction_expense = ($current_data->total_orders ?? 0) + ($current_subs_data->total_count ?? 0) + $current_expense_count;
        $current_transaction_subscription = $current_subs_data->total_count ?? 0;
        $current_total_transaction = $current_transaction_earning + $current_transaction_expense;

        $previous_transaction_earning = $previous_data->total_orders ?? 0;
        $previous_transaction_expense = ($previous_data->total_orders ?? 0) + ($previous_subs_data->total_count ?? 0) + $previous_expense_count;
        $previous_transaction_subscription = $previous_subs_data->total_count ?? 0;
        $previous_total_transaction = $previous_transaction_earning + $previous_transaction_expense;

        [$earning_percentage, $earning_positive] = $this->calculate_percentage_info($current_earnings, $previous_earnings);
        [$expense_percentage, $expense_positive] = $this->calculate_percentage_info($current_expenses, $previous_expenses);
        [$profit_percentage, $profit_positive] = $this->calculate_percentage_info($current_net_profit, $previous_net_profit);

        [$transaction_percentage, $transaction_positive] = $this->calculate_percentage_info($current_total_transaction, $previous_total_transaction);
        [$transaction_earning_percentage, $transaction_earning_positive] = $this->calculate_percentage_info($current_transaction_earning, $previous_transaction_earning);
        [$transaction_expense_percentage, $transaction_expense_positive] = $this->calculate_percentage_info($current_transaction_expense, $previous_transaction_expense);
        [$transaction_subscription_percentage, $transaction_subscription_positive] = $this->calculate_percentage_info($current_transaction_subscription, $previous_transaction_subscription);

        return [
            'total_earnings_with_admin_commission' => $current_earnings,
            'total_earnings_percentage' => $earning_percentage,
            'total_earnings_positive' => $earning_positive,

            'total_expenses' => $current_expenses,
            'total_expenses_percentage' => $expense_percentage,
            'total_expenses_positive' => $expense_positive,

            'net_profit' => $current_net_profit,
            'net_profit_percentage' => $profit_percentage,
            'net_profit_positive' => $profit_positive,

            'total_transaction' => $current_total_transaction,
            'total_transaction_percentage' => $transaction_percentage,
            'total_transaction_positive' => $transaction_positive,

            'total_transaction_expense' => $current_transaction_expense,
            'total_transaction_expense_percentage' => $transaction_expense_percentage,
            'total_transaction_expense_positive' => $transaction_expense_positive,

            'total_transaction_earning' => $current_transaction_earning,
            'total_transaction_earning_percentage' => $transaction_earning_percentage,
            'total_transaction_earning_positive' => $transaction_earning_positive,

            'total_transaction_subscription' => $current_transaction_subscription,
            'total_transaction_subscription_percentage' => $transaction_subscription_percentage,
            'total_transaction_subscription_positive' => $transaction_subscription_positive,

            'breakdown' => [
                'order_sales' => $current_data->order_sales ?? 0,
                'tax_collected' => $current_data->tax_collected ?? 0,
                'packaging_fee_collected' => $current_data->packaging_fee_collected ?? 0,
                'total' => ($current_data->order_sales ?? 0) + ($current_data->tax_collected ?? 0) + ($current_data->packaging_fee_collected ?? 0),
                'admin_commission' => $current_data->admin_commission ?? 0,
                'restaurant_expense' => $current_data->restaurant_expense ?? 0,
                'product_discount' => $current_product_discount,
                'bogo_discount' => $current_bogo_discount,
                'happy_hour_discount' => $current_happy_hour_discount,
                'subscription_fee' => $current_subs,
                'coupon_contribution' => $current_coupon_contribution,
                'free_delivery' => $current_free_delivery,
            ]
        ];
    }

    public function get_restaurant_earning_trend_data($vendor_id, $filter, $from, $to)
    {
        $today = Carbon::now();
        $months = collect();
        $dateFormat = ($filter === 'this_week' || $filter === 'this_month') ? '%Y-%m-%d' : '%Y-%m';
        $singleDayCustom = $filter === 'custom' && $from && $to && $from === $to;

        if ($filter === 'this_year') {
            $startMonth = Carbon::now()->startOfYear();
            for ($i = 0; $i <= $today->month - 1; $i++) {
                $months->push($startMonth->copy()->addMonths($i)->format('Y-m'));
            }
        } elseif ($filter === 'this_month') {
            $daysInMonth = Carbon::now()->daysInMonth;
            $startOfMonth = Carbon::now()->startOfMonth();
            for ($i = 0; $i < $daysInMonth; $i++) {
                $months->push($startOfMonth->copy()->addDays($i)->format('Y-m-d'));
            }
        } elseif ($filter === 'this_week') {
            $startOfWeek = Carbon::now()->startOfWeek();
            for ($i = 0; $i < 7; $i++) {
                $months->push($startOfWeek->copy()->addDays($i)->format('Y-m-d'));
            }
        } elseif ($filter === 'custom' && $from && $to) {
            $start = Carbon::parse($from)->startOfDay();
            $end = Carbon::parse($to)->endOfDay();
            $diffDays = $start->diffInDays($end);

            if ($diffDays > 365) {
                $dateFormat = '%Y';
                $temp = $start->copy()->startOfYear();
                while ($temp->year <= $end->year) {
                    $months->push($temp->format('Y'));
                    $temp->addYear();
                }
            } elseif ($diffDays > 31) {
                $dateFormat = '%Y-%m';
                $temp = $start->copy()->startOfMonth();
                while ($temp->format('Y-m') <= $end->format('Y-m')) {
                    $months->push($temp->format('Y-m'));
                    $temp->addMonth();
                }
            } else {
                $dateFormat = '%Y-%m-%d';
                $temp = $start->copy();
                while ($temp->lte($end)) {
                    $months->push($temp->format('Y-m-d'));
                    $temp->addDay();
                }
            }
        } else {
            for ($i = 11; $i >= 0; $i--) {
                $months->push($today->copy()->subMonths($i)->format('Y-m'));
            }
        }

        $baseTransactionQuery = OrderTransaction::join('orders', 'orders.id', '=', 'order_transactions.order_id')
            ->NotRefunded()
            ->when($vendor_id !== 'all' && $vendor_id !== null, function ($query) use ($vendor_id) {
                return $query->where('order_transactions.vendor_id', $vendor_id);
            })
            ->applyDateFilter($filter, $from, $to, 'order_transactions.created_at');

        $earningFormula = "
            SUM(
                orders.order_amount
                - order_transactions.additional_charge
                - orders.dm_tips
                - orders.delivery_charge
                - orders.delivery_type_charge
                + orders.coupon_discount_amount
                + orders.restaurant_discount_amount
                + orders.ref_bonus_amount
                + order_transactions.pro_discount
            )
        ";

        $expenseFormula = "
            SUM(
                (
                    orders.order_amount
                    - order_transactions.additional_charge
                    - orders.dm_tips
                    - orders.delivery_charge
                    - order_transactions.tax
                    - orders.extra_packaging_amount
                    - orders.delivery_type_charge
                    + orders.coupon_discount_amount
                    + orders.restaurant_discount_amount
                    + orders.ref_bonus_amount
                    + order_transactions.pro_discount
                ) * order_transactions.commission_percentage / 100
                + order_transactions.restaurant_expense
            )
        ";

        $transactions = (clone $baseTransactionQuery)
            ->selectRaw("DATE_FORMAT(order_transactions.created_at, '$dateFormat') as month")
            ->selectRaw("$earningFormula as total_earning")
            ->selectRaw("$expenseFormula as total_expense")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $earnings = $transactions->map(fn($item) => $item->total_earning);
        $expenses = $transactions->map(fn($item) => $item->total_expense);

        // Subscriptions expenses (paid by restaurant)
        $subQuery = SubscriptionTransaction::where('payment_status', 'success')
            ->when($vendor_id !== 'all' && $vendor_id !== null, function ($query) use ($vendor_id) {
                return $query->where('restaurant_id', Restaurant::where('vendor_id', $vendor_id)->first()?->id);
            })
            ->applyDateFilter($filter, $from, $to, 'created_at');

        $subExpenses = (clone $subQuery)
            ->selectRaw("DATE_FORMAT(created_at, '$dateFormat') as month")
            ->selectRaw("SUM(paid_amount) as total_sub")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_sub', 'month');

        $productDiscountQuery = Expense::query()
            ->when($vendor_id !== 'all' && $vendor_id !== null, function ($query) use ($vendor_id) {
                return $query->where('restaurant_id', Restaurant::where('vendor_id', $vendor_id)->value('id'));
            })
            ->where('type', 'discount_on_product')
            ->where('created_by', 'vendor')
            ->applyDateFilter($filter, $from, $to, 'created_at');

        $productDiscounts = (clone $productDiscountQuery)
            ->selectRaw("DATE_FORMAT(created_at, '$dateFormat') as month")
            ->selectRaw("SUM(amount) as total_discount")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_discount', 'month');

        $earningSeries = $months->map(fn($m) => round($earnings[$m] ?? 0, 2));
        $expenseSeries = $months->map(function ($m) use ($expenses, $subExpenses, $productDiscounts) {
            return round(($expenses[$m] ?? 0) + ($productDiscounts[$m] ?? 0) + ($subExpenses[$m] ?? 0), 2);
        });

        return [
            'categories' => $months->map(function ($m) use ($filter, $dateFormat, $singleDayCustom) {
                if ($filter === 'this_week') {
                    return Carbon::parse($m)->format('D');
                }
                if ($filter === 'this_month') {
                    return Carbon::parse($m)->format('j');
                }
                if ($filter === 'custom') {
                    if ($singleDayCustom) {
                        return Carbon::parse($m)->format('d M Y');
                    }
                    if ($dateFormat === '%Y')
                        return $m;
                    if ($dateFormat === '%Y-%m')
                        return Carbon::parse($m . '-01')->format('M');
                    if ($dateFormat === '%Y-%m-%d')
                        return Carbon::parse($m)->format('j');
                }
                return Carbon::parse($m . '-01')->format('M');
            }),
            'earning_series' => $earningSeries,
            'expense_series' => $expenseSeries
        ];
    }

    public function get_deliveryman_earning_summary_data($delivery_man_id, $filter, $from, $to)
    {
        $previousPeriodRange = $this->getPreviousPeriodRange($filter, $from, $to);
        $baseQuery = OrderTransaction::
            NotRefunded()
              ->whereNotNull('delivery_man_id')
            ->when($delivery_man_id !== 'all' && $delivery_man_id !== null, function ($query) use ($delivery_man_id) {
                return $query->where('order_transactions.delivery_man_id', $delivery_man_id);
            });

        $earningFormula = "
            SUM(order_transactions.original_delivery_charge) as delivery_charge,
            SUM(order_transactions.dm_tips) as dm_tips,
            SUM(order_transactions.delivery_fee_comission) as admin_commission
        ";

        $current_data = (clone $baseQuery)
            ->applyDateFilter($filter, $from, $to, 'order_transactions.created_at')
            ->selectRaw($earningFormula)
            ->first();

        $previous_data = null;
        if ($previousPeriodRange) {
            $previous_data = (clone $baseQuery)
                ->whereBetween('order_transactions.created_at', $previousPeriodRange)
                ->selectRaw($earningFormula)
                ->first();
        }
        $previous_data = $previous_data ?? (object) [];

        // Incentives from expenses table
        $incentiveQuery = Expense::where('type', 'incentive')
            ->when($delivery_man_id !== 'all' && $delivery_man_id !== null, function ($query) use ($delivery_man_id) {
                return $query->where('delivery_man_id', $delivery_man_id);
            });

        $current_incentives = (clone $incentiveQuery)
            ->applyDateFilter($filter, $from, $to, 'created_at')
            ->sum('amount');

        $previous_incentives = 0;
        if ($previousPeriodRange) {
            $previous_incentives = (clone $incentiveQuery)
                ->whereBetween('created_at', $previousPeriodRange)
                ->sum('amount');
        }

        $current_earnings = ($current_data->delivery_charge ?? 0) + ($current_data->dm_tips ?? 0) + $current_incentives + ($current_data->admin_commission ?? 0);
        $current_expenses = ($current_data->admin_commission ?? 0);
        $current_net_profit = $current_earnings - $current_expenses;

        $previous_earnings = ($previous_data->delivery_charge ?? 0) + ($previous_data->dm_tips ?? 0) + $previous_incentives + ($previous_data->admin_commission ?? 0);
        $previous_expenses = ($previous_data->admin_commission ?? 0);
        $previous_net_profit = $previous_earnings - $previous_expenses;

        [$earning_percentage, $earning_positive] = $this->calculate_percentage_info($current_earnings, $previous_earnings);
        [$expense_percentage, $expense_positive] = $this->calculate_percentage_info($current_expenses, $previous_expenses);
        [$profit_percentage, $profit_positive] = $this->calculate_percentage_info($current_net_profit, $previous_net_profit);

        return [
            'total_earnings' => $current_earnings,
            'total_earnings_percentage' => $earning_percentage,
            'total_earnings_positive' => $earning_positive,

            'total_expenses' => $current_expenses,
            'total_expenses_percentage' => $expense_percentage,
            'total_expenses_positive' => $expense_positive,

            'net_profit' => $current_net_profit,
            'net_profit_percentage' => $profit_percentage,
            'net_profit_positive' => $profit_positive,

            'breakdown' => [
                'delivery_charge' => (float)(($current_data->delivery_charge ?? 0) + ($current_data->admin_commission ?? 0)),
                'dm_tips' => (float)($current_data->dm_tips ?? 0),
                'incentives' => number_format((float) $current_incentives, 2, '.', ''),
                'admin_commission' => (float)($current_data->admin_commission ?? 0),
            ]
        ];
    }

    public function get_deliveryman_earning_trend_data($delivery_man_id, $filter, $from, $to)
    {
        $today = Carbon::now();
        $months = collect();
        $dateFormat = ($filter === 'this_week' || $filter === 'this_month') ? '%Y-%m-%d' : '%Y-%m';
        $singleDayCustom = $filter === 'custom' && $from && $to && $from === $to;

        if ($filter === 'this_year') {
            $startMonth = Carbon::now()->startOfYear();
            for ($i = 0; $i <= $today->month - 1; $i++) {
                $months->push($startMonth->copy()->addMonths($i)->format('Y-m'));
            }
        } elseif ($filter === 'this_month') {
            $daysInMonth = Carbon::now()->daysInMonth;
            $startOfMonth = Carbon::now()->startOfMonth();
            for ($i = 0; $i < $daysInMonth; $i++) {
                $months->push($startOfMonth->copy()->addDays($i)->format('Y-m-d'));
            }
        } elseif ($filter === 'this_week') {
            $startOfWeek = Carbon::now()->startOfWeek();
            for ($i = 0; $i < 7; $i++) {
                $months->push($startOfWeek->copy()->addDays($i)->format('Y-m-d'));
            }
        } elseif ($filter === 'custom' && $from && $to) {
            $start = Carbon::parse($from)->startOfDay();
            $end = Carbon::parse($to)->endOfDay();
            $diffDays = $start->diffInDays($end);

            if ($diffDays > 365) {
                $dateFormat = '%Y';
                $temp = $start->copy()->startOfYear();
                while ($temp->year <= $end->year) {
                    $months->push($temp->format('Y'));
                    $temp->addYear();
                }
            } elseif ($diffDays > 31) {
                $dateFormat = '%Y-%m';
                $temp = $start->copy()->startOfMonth();
                while ($temp->format('Y-m') <= $end->format('Y-m')) {
                    $months->push($temp->format('Y-m'));
                    $temp->addMonth();
                }
            } else {
                $dateFormat = '%Y-%m-%d';
                $temp = $start->copy();
                while ($temp->lte($end)) {
                    $months->push($temp->format('Y-m-d'));
                    $temp->addDay();
                }
            }
        } else {
            for ($i = 11; $i >= 0; $i--) {
                $months->push($today->copy()->subMonths($i)->format('Y-m'));
            }
        }

        $baseTransactionQuery = OrderTransaction::NotRefunded()
            ->whereNotNull('delivery_man_id')
            ->when($delivery_man_id !== 'all' && $delivery_man_id !== null, function ($query) use ($delivery_man_id) {
                return $query->where('order_transactions.delivery_man_id', $delivery_man_id);
            })
            ->applyDateFilter($filter, $from, $to, 'order_transactions.created_at');

        $earningFormula = "SUM(order_transactions.original_delivery_charge + order_transactions.dm_tips)";
        $expenseFormula = "SUM(order_transactions.delivery_fee_comission)";

        $earnings = (clone $baseTransactionQuery)
            ->selectRaw("DATE_FORMAT(order_transactions.created_at, '$dateFormat') as month")
            ->selectRaw("$earningFormula as total_earning")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_earning', 'month');

        $expenses = (clone $baseTransactionQuery)
            ->selectRaw("DATE_FORMAT(order_transactions.created_at, '$dateFormat') as month")
            ->selectRaw("$expenseFormula as total_expense")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_expense', 'month');

        // Incentives
        $incentiveQuery = Expense::where('type', 'incentive')
            ->when($delivery_man_id !== 'all' && $delivery_man_id !== null, function ($query) use ($delivery_man_id) {
                return $query->where('delivery_man_id', $delivery_man_id);
            })
            ->applyDateFilter($filter, $from, $to, 'created_at');

        $incentiveData = (clone $incentiveQuery)
            ->selectRaw("DATE_FORMAT(created_at, '$dateFormat') as month")
            ->selectRaw("SUM(amount) as total_incentive")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_incentive', 'month');

        $earningSeries = $months->map(function ($m) use ($earnings, $incentiveData) {
            return round(($earnings[$m] ?? 0) + ($incentiveData[$m] ?? 0), 2);
        });
        $expenseSeries = $months->map(fn($m) => round($expenses[$m] ?? 0, 2));

        return [
            'categories' => $months->map(function ($m) use ($filter, $dateFormat, $singleDayCustom) {
                if ($filter === 'this_week') {
                    return Carbon::parse($m)->format('D');
                }
                if ($filter === 'this_month') {
                    return Carbon::parse($m)->format('j');
                }
                if ($filter === 'custom') {
                    if ($singleDayCustom) {
                        return Carbon::parse($m)->format('d M Y');
                    }
                    if ($dateFormat === '%Y')
                        return $m;
                    if ($dateFormat === '%Y-%m')
                        return Carbon::parse($m . '-01')->format('M');
                    if ($dateFormat === '%Y-%m-%d')
                        return Carbon::parse($m)->format('j');
                }
                return Carbon::parse($m . '-01')->format('M');
            }),
            'earning_series' => $earningSeries,
            'expense_series' => $expenseSeries
        ];

    }

    private function calculate_percentage_info($current, $previous)
    {
        if ($previous == 0) {
            if ($current == 0) {
                return [0, false];
            }
            return [100, true];
        }

        $percentage = (($current - $previous) / abs($previous)) * 100;
        $percentage = round($percentage, 2);
        return [abs($percentage), $percentage >= 0];
    }

    public function get_order_earning_transactions($request, $filter, $from, $to, $nopaginate = false)
    {
        $search = $request->search ?? null;

        $query = OrderTransaction::join('orders', 'orders.id', '=', 'order_transactions.order_id')
            ->NotRefunded()
            ->applyDateFilter($filter, $from, $to, 'order_transactions.created_at')
            ->when($search, function ($query) use ($search) {
                $keywords = is_array($search) ? $search : explode(' ', $search);
                $keywords = array_filter(array_map('trim', $keywords));

                return $query->where(function ($subQuery) use ($keywords) {
                    foreach ($keywords as $word) {
                        $subQuery->where('order_transactions.id', 'like', "%{$word}%")
                            ->orWhere('orders.id', 'like', "%{$word}%");
                    }
                });
            })
            ->select('order_transactions.*', 'orders.order_amount', 'orders.delivery_charge', 'orders.dm_tips', 'orders.extra_packaging_amount', 'orders.coupon_discount_amount', 'orders.restaurant_discount_amount', 'orders.ref_bonus_amount', 'orders.coupon_created_by', 'orders.discount_on_product_by', 'orders.delivery_type_charge', 'orders.delivery_type')
            ->latest('order_transactions.created_at');

        if ($nopaginate) {
            $transactions = $query->get();
        } else {
            $transactions = $query->paginate(config('default_pagination', 25))->withQueryString();
        }

        $collection = $nopaginate ? $transactions : $transactions->getCollection();

        $collection->transform(function ($transaction) {
            $order_val = $transaction->order_amount
                - $transaction->additional_charge
                - $transaction->dm_tips
                - $transaction->delivery_charge
                - $transaction->tax
                - $transaction->extra_packaging_amount
                - $transaction->delivery_type_charge
                + $transaction->coupon_discount_amount
                + $transaction->restaurant_discount_amount
                + $transaction->ref_bonus_amount
                + ($transaction->pro_discount ?? 0);

            $admin_commission = ($order_val * $transaction->commission_percentage / 100);

            $admin_commission = max(0, $admin_commission);

            $express_charge = $transaction->delivery_type == 'express' ? $transaction->delivery_type_charge : 0;

            $amount = $admin_commission + $transaction->delivery_fee_comission + $transaction->additional_charge + $express_charge;

            return [
                'transaction_id' => '#TXN ' . $transaction->id,
                'date' => $transaction->created_at->format('d M Y h:i a'),
                'source' => $transaction->vendor_id ? ($transaction->restaurant ? $transaction->restaurant->name : 'Restaurant') : ($transaction->delivery_man_id ? ($transaction->delivery_man ? $transaction->delivery_man->f_name . ' ' . $transaction->delivery_man->l_name : 'Delivery Man') : 'Admin'),
                'source_type' => $transaction->vendor_id ? 'Restaurant' : ($transaction->delivery_man_id ? 'Delivery Man' : 'Admin'),
                'earning_from' => '#ORD ' . $transaction->order_id,
                'order_id' => $transaction->order_id,
                'earning_from_badge' => $transaction->delivery_man_id ? 'Delivery Commission' : null,
                'amount' => $amount,
                'breakdown' => [
                    'order_sales' => $admin_commission,
                    'delivery_fee_comission' => $transaction->delivery_fee_comission,
                    'packaging_fee_collected' => $transaction->additional_charge,
                    'express_charge' => $express_charge,
                ]
            ];
        });

        return $transactions;
    }

    public function get_subscription_earning_transactions($request, $filter, $from, $to, $nopaginate = false)
    {
        $search = $request->search ?? null;

        $query = SubscriptionTransaction::with(['restaurant'])
            ->where('payment_status', 'success')
            ->where('paid_amount', '>', 0)
            ->applyDateFilter($filter, $from, $to, 'created_at')
            ->search($search, ['restaurant' => 'name'], ['id'])
            ->latest();

        if ($nopaginate) {
            $transactions = $query->get();
        } else {
            $transactions = $query->paginate(config('default_pagination', 25))->withQueryString();
        }

        $collection = $nopaginate ? $transactions : $transactions->getCollection();

        $collection->transform(function ($transaction) {
            $type = match ($transaction->plan_type) {
                'renew' => 'Renew Subscription',
                'new_plan' => 'Migrate to New Plan',
                'first_purchased' => 'First Purchased',
                'free_trial' => 'Free Trial',
                default => ucwords(str_replace('_', ' ', $transaction->plan_type)),
            };

            $typeBadgeStyle = match ($transaction->plan_type) {
                'renew' => 'background-color: #F0F2F7; color: #4B5563;',
                'new_plan' => 'background-color: #FFF6E6; color: #B76E00;',
                'first_purchased' => 'background-color: #EAF7EE; color: #1F7A4D;',
                'free_trial' => 'background-color: #EDF4FF; color: #295EBC;',
                default => 'background-color: #F4F5F7; color: #4B5563;',
            };

            return [
                'transaction_id' => $transaction->id,
                'date' => $transaction->created_at->format('d M Y h:i a'),
                'restaurant' => $transaction->restaurant ? $transaction->restaurant->name : 'Restaurant',
                'transaction_type' => $type,
                'transaction_type_badge_style' => $typeBadgeStyle,
                'amount' => $transaction->paid_amount,
            ];
        });

        return $transactions;
    }

    public function get_pro_customer_subscription_transactions($request, $filter, $from, $to, $nopaginate = false)
    {
        $search = $request->search ?? null;

        $query = ProCustomerTransaction::with(['user'])
            ->where('payment_status', 'success')
            ->where('plan_type', 'paid')
            ->where('amount', '>', 0)
            ->applyDateFilter($filter, $from, $to, 'created_at')
            ->search($search, ['user' => 'f_name'], ['id'])
            ->latest();

        if ($nopaginate) {
            $transactions = $query->get();
        } else {
            $transactions = $query->paginate(config('default_pagination', 25))->withQueryString();
        }

        $collection = $nopaginate ? $transactions : $transactions->getCollection();

        $collection->transform(function ($transaction) {
            $customer = $transaction->user
                ? trim($transaction->user->f_name . ' ' . $transaction->user->l_name)
                : translate('messages.Customer');

            return [
                'transaction_id' => $transaction->id,
                'date' => $transaction->created_at->format('d M Y h:i a'),
                'source' => $customer,
                'transaction_type' => $transaction->plan_name,
                'transaction_type_badge_style' => 'background-color: #F3E8FF; color: #6B21A8;',
                'amount' => $transaction->amount,
            ];
        });

        return $transactions;
    }

    public function get_expense_transactions($request, $filter, $from, $to, $nopaginate = false)
    {
        $search = $request->search ?? null;

        $expenseQuery = Expense::with(['delivery_man', 'user', 'order.customer'])
            ->when($search, function ($query) use ($search) {
                $search = str_replace(['#ORD', '#TXN', '#'], '', $search);
                $query->where('order_id', 'like', "%{$search}%");
            })
            ->where(function ($query) {
                // Part 1: General Admin Expenses
                $query->where(function ($q) {
                    $q->where('created_by', 'admin')
                      ->whereNotIn('type', ['free_delivery', 'partial_free_delivery']);
                })
                // Part 2: Slightly Delay Expenses
                ->orWhere(function ($q) {
                    $q->where('type', 'slightly_delay')
                      ->where('amount', '>', 0);
                })
                // Part 3: Free Delivery Expenses (regular + Pro Customer)
                ->orWhere(function ($q) {
                    $q->whereIn('type', ['free_delivery', 'partial_free_delivery'])
                      ->where('created_by', 'admin')
                      ->where('amount', '>', 0);
                });
            })
            ->applyDateFilter($filter, $from, $to, 'created_at')
            ->latest('created_at');

        if ($nopaginate) {
            $results = $expenseQuery->get();
        } else {
            $results = $expenseQuery->paginate(config('default_pagination', 25))->withQueryString();
        }

        // Map Results
        $formattedData = ($nopaginate ? $results : $results->getCollection())->map(function ($transaction) {
            $source = 'Admin';
            $source_type = 'Admin';

            if ($transaction->delivery_man) {
                $source = $transaction->delivery_man->f_name . ' ' . $transaction->delivery_man->l_name;
                $source_type = 'Delivery Man';
            } elseif ($transaction->user) {
                $source = $transaction->user->f_name . ' ' . $transaction->user->l_name;
                $source_type = 'Customer';
            } elseif ($transaction->order && $transaction->order->is_guest) {
                $address = $transaction->order->delivery_address;
                $address = is_array($address) ? $address : json_decode($address ?? '', true);
                $source = is_array($address) ? ($address['contact_person_name'] ?? 'Guest') : 'Guest';
                $source_type = 'Customer';
            } elseif ($transaction->order && $transaction->order->customer) {
                $source = $transaction->order->customer->f_name . ' ' . $transaction->order->customer->l_name;
                $source_type = 'Customer';
            }

            return [
                'transaction_id' => '#TXN ' . $transaction->id,
                'date' => $transaction->created_at->format('d M Y h:i a'),
                'source' => $source,
                'source_type' => $source_type,
                'expense_source' => $transaction->order_id ? '#ORD ' . $transaction->order_id : '',
                'order_id' => $transaction->order_id,
                'expense_source_badge' => ucwords(str_replace('_', ' ', $transaction->type)),
                'amount' => $transaction->amount,
                'breakdown' => []
            ];
        });

        if ($nopaginate) {
            return $formattedData;
        }

        $results->setCollection($formattedData);
        return $results;
    }

    public function get_restaurant_earning_transactions($request, $restaurant_id, $filter, $from, $to, $nopaginate = false, $limit = null, $offset = null)
    {
        $search = $request->search ?? null;

        $query = OrderTransaction::join('orders', 'orders.id', '=', 'order_transactions.order_id')
            ->NotRefunded()
            ->when($restaurant_id && $restaurant_id !== 'all', function ($query) use ($restaurant_id) {
                return $query->where('order_transactions.vendor_id', $restaurant_id);
            })
            ->when($search, function ($query) use ($search) {
                $search = str_replace(['#ORD', '#TXN', '#'], '', $search);
                return $query->where('order_transactions.order_id', 'like', "%{$search}%");
            })
            ->applyDateFilter($filter, $from, $to, 'order_transactions.created_at')
            ->select('order_transactions.*', 'orders.order_amount', 'orders.delivery_charge', 'orders.dm_tips', 'orders.extra_packaging_amount', 'orders.coupon_discount_amount', 'orders.restaurant_discount_amount', 'orders.ref_bonus_amount', 'orders.coupon_created_by', 'orders.discount_on_product_by', 'orders.delivery_type_charge')
            ->latest('order_transactions.created_at');

        if ($nopaginate) {
            $transactions = $query->get();
        } else {
            $perPage = $limit ?? config('default_pagination', 25);
            $transactions = $query->paginate($perPage, ['*'], 'page', $offset)->withQueryString();
        }

        $collection = $nopaginate ? $transactions : $transactions->getCollection();

        $collection->transform(function ($transaction) {
            $order_val = $transaction->order_amount
                - $transaction->additional_charge
                - $transaction->dm_tips
                - $transaction->delivery_charge
                - $transaction->tax
                - $transaction->extra_packaging_amount
                - $transaction->delivery_type_charge
                + $transaction->coupon_discount_amount
                + $transaction->restaurant_discount_amount
                + $transaction->ref_bonus_amount
                + ($transaction->pro_discount ?? 0);

            $admin_commission = ($order_val * $transaction->commission_percentage / 100);

            $order_sales = $order_val - $admin_commission;
            $order_sales = max(0, $order_sales);
            $total_earning = $order_sales + $transaction->tax + $transaction->extra_packaging_amount;

            return [
                'transaction_id' => '#TXN ' . $transaction->id,
                'date' => $transaction->created_at->format('d M Y h:i a'),
                'source' => $transaction->restaurant ? $transaction->restaurant->name : 'Restaurant',
                'source_type' => 'Restaurant',
                'earning_from' => '#ORD ' . $transaction->order_id,
                'order_id' => $transaction->order_id,
                'amount' => $total_earning,
                'breakdown' => [
                    'order_sales' => $order_sales,
                    'tax_collected' => $transaction->tax,
                    'packaging_fee_collected' => $transaction->extra_packaging_amount,
                ]
            ];
        });

        return $transactions;
    }
    public function get_restaurant_expense_transactions($request, $restaurant_id, $filter, $from, $to, $nopaginate = false, $limit = null, $offset = null)
    {
        $search = $request->search ?? null;

        // Part 1: Commission Paid
        $commissionQuery = OrderTransaction::query()
            ->join('orders', 'orders.id', '=', 'order_transactions.order_id')
            ->NotRefunded()
            ->when($restaurant_id && $restaurant_id !== 'all' && $restaurant_id !== null, function ($query) use ($restaurant_id) {
                return $query->where('order_transactions.vendor_id', $restaurant_id);
            })
            ->applyDateFilter($filter, $from, $to, 'order_transactions.created_at')
            ->whereRaw("
                (
                    (
                        orders.order_amount
                        - order_transactions.additional_charge
                        - orders.dm_tips
                        - orders.delivery_charge
                        - order_transactions.tax
                        - orders.extra_packaging_amount
                        - orders.delivery_type_charge
                        + orders.coupon_discount_amount
                        + orders.restaurant_discount_amount
                        + orders.ref_bonus_amount
                        + order_transactions.pro_discount
                    ) * order_transactions.commission_percentage / 100
                ) > 0
            ")
            ->selectRaw("
                order_transactions.id as id,
                order_transactions.created_at as date,
                'Commission Paid' as type,
                (
                    (
                        orders.order_amount
                        - order_transactions.additional_charge
                        - orders.dm_tips
                        - orders.delivery_charge
                        - order_transactions.tax
                        - orders.extra_packaging_amount
                        - orders.delivery_type_charge
                        + orders.coupon_discount_amount
                        + orders.restaurant_discount_amount
                        + orders.ref_bonus_amount
                        + order_transactions.pro_discount
                    ) * order_transactions.commission_percentage / 100
                ) as amount,
                order_transactions.order_id as source_id
            ");

        // Part 2: Discount on Product
        $productDiscountQuery = Expense::query()
            ->applyDateFilter($filter, $from, $to, 'created_at')
            ->where('type', 'discount_on_product')
            ->where('created_by', 'vendor')
            ->when($restaurant_id && $restaurant_id !== 'all' && $restaurant_id !== null, function ($query) use ($restaurant_id) {
                return $query->where('restaurant_id', $restaurant_id);
            })
            ->selectRaw("
                id as id,
                created_at as date,
                'Discount on Product' as type,
                amount as amount,
                order_id as source_id
            ");

        // Part 3: Coupon Contribution
        $couponDiscountQuery = Expense::query()
            ->applyDateFilter($filter, $from, $to, 'created_at')
            ->where('type', 'coupon_discount')
            ->where('created_by', 'vendor')
            ->when($restaurant_id && $restaurant_id !== 'all' && $restaurant_id !== null, function ($query) use ($restaurant_id) {
                return $query->where('restaurant_id', $restaurant_id);
            })
            ->selectRaw("
                id as id,
                created_at as date,
                'Coupon Contribution' as type,
                amount as amount,
                order_id as source_id
            ");

        // Part 4: Free Delivery
        $freeDeliveryQuery = Expense::query()
            ->applyDateFilter($filter, $from, $to, 'created_at')
            ->where('type', 'free_delivery')
            ->where('created_by', 'vendor')
            ->when($restaurant_id && $restaurant_id !== 'all' && $restaurant_id !== null, function ($query) use ($restaurant_id) {
                return $query->where('restaurant_id', $restaurant_id);
            })
            ->selectRaw("
                id as id,
                created_at as date,
                'Free Delivery' as type,
                amount as amount,
                order_id as source_id
            ");

        // Apply search filter to each branch individually using native column names
        if ($search) {
            $search = str_replace(['#ORD', '#TXN', '#'], '', $search);
            $commissionQuery->where('order_transactions.order_id', 'like', "%{$search}%");
            $productDiscountQuery->where('order_id', 'like', "%{$search}%");
            $couponDiscountQuery->where('order_id', 'like', "%{$search}%");
            $freeDeliveryQuery->where('order_id', 'like', "%{$search}%");
        }

        // Combine using UNION ALL
        $combinedQuery = $commissionQuery
            ->unionAll($productDiscountQuery)
            ->unionAll($couponDiscountQuery)
            ->unionAll($freeDeliveryQuery)
            ->orderBy('date', 'desc');

        if ($nopaginate) {
            $results = $combinedQuery->get();
        } else {
            $perPage = $limit ?? config('default_pagination', 25);
            $results = $combinedQuery->paginate($perPage, ['*'], 'page', $offset)->withQueryString();
        }

        // Map data to the expected format
        $formattedData = ($nopaginate ? $results : $results->getCollection())->map(function ($row) {
            $date = Carbon::parse($row->date);

            $source = 'Restaurant';
            $t = OrderTransaction::with('restaurant')->find($row->id);
            $source = $t && $t->restaurant ? $t->restaurant->name : 'Restaurant';

            return [
                'transaction_id' => '#TXN ' . $row->id,
                'date' => $date->format('d M Y h:i a'),
                'source' => $source,
                'source_type' => 'Restaurant',
                'expense_source' => '#ORD ' . $row->source_id,
                'order_id' => $row->source_id,
                'expense_source_badge' => $row->type,
                'amount' => $row->amount,
                'breakdown' => []
            ];
        });

        if ($nopaginate) {
            return $formattedData;
        }

        $results->setCollection($formattedData);
        return $results;
    }


    public function get_restaurant_subscription_transactions($request, $restaurant_id, $filter, $from, $to, $nopaginate = false, $limit = null, $offset = null)
    {
        $search = $request->search ?? null;
        // Subscription transactions
        $subQuery = SubscriptionTransaction::where('payment_status', 'success')->where('paid_amount', '>', 0)
            ->when($restaurant_id !== 'all' && $restaurant_id !== null, function ($query) use ($restaurant_id) {
                return $query->where('restaurant_id', $restaurant_id);
            })
            ->applyDateFilter($filter, $from, $to, 'created_at')
            ->search($search, ['restaurant' => 'name'], ['id'])
            ->latest();

        if ($nopaginate) {
            $subsData = $subQuery->get();
        } else {
            $perPage = $limit ?? config('default_pagination', 25);
            $subsData = $subQuery->paginate($perPage, ['*'], 'page', $offset)->withQueryString();
        }

        $subscriptionTransactions = $subsData->map(function ($t) {
            $type = match ($t->plan_type) {
                'renew' => 'Renew Subscription',
                'new_plan' => 'Migrate to New Plan',
                'first_purchased' => 'First Purchased',
                'free_trial' => 'Free Trial',
                default => ucwords(str_replace('_', ' ', $t->plan_type)),
            };

            $typeBadgeStyle = match ($t->plan_type) {
                'renew' => 'background-color: #F0F2F7; color: #4B5563;',
                'new_plan' => 'background-color: #FFF6E6; color: #B76E00;',
                'first_purchased' => 'background-color: #EAF7EE; color: #1F7A4D;',
                'free_trial' => 'background-color: #EDF4FF; color: #295EBC;',
                default => 'background-color: #F4F5F7; color: #4B5563;',
            };

            return [
                'transaction_id' => $t->id,
                'date' => $t->created_at->format('d M Y h:i a'),
                'restaurant' => $t->restaurant ? $t->restaurant->name : 'Restaurant',
                'transaction_type' => $type,
                'transaction_type_badge_style' => $typeBadgeStyle,
                'amount' => $t->paid_amount,
            ];
        });

        if ($nopaginate) {
            return $subscriptionTransactions;
        }

        $subsData->setCollection($subscriptionTransactions);
        return $subsData;
    }


    public function get_deliveryman_earning_transactions($request, $delivery_man_id, $filter, $from, $to, $nopaginate = false)
    {
        $search = $request->search ?? null;

        $query = OrderTransaction::with(['order', 'delivery_man'])
            ->NotRefunded()
            ->when($delivery_man_id && $delivery_man_id !== 'all', function ($query) use ($delivery_man_id) {
                return $query->where('delivery_man_id', $delivery_man_id);
            })
            ->whereNotNull('delivery_man_id')
            ->applyDateFilter($filter, $from, $to, 'created_at')
            ->search($search, ['delivery_man' => 'f_name'], ['order_id'])
            ->latest();

        if ($nopaginate) {
            $transactions = $query->get();
        } else {
            $transactions = $query->paginate(config('default_pagination', 25))->withQueryString();
        }

        $collection = $nopaginate ? $transactions : $transactions->getCollection();

        $collection->transform(function ($transaction) {
            $is_pos = $transaction->order && $transaction->order->order_type == 'pos' ? ' (POS)' : '';
            return [
                'order_id' => $transaction->order_id . $is_pos,
                'raw_order_id' => $transaction->order_id,
                'order_date' => $transaction->created_at->format('d M Y h:i a'),
                'delivery_man' => $transaction->delivery_man ? $transaction->delivery_man->f_name . ' ' . $transaction->delivery_man->l_name : 'Delivery Man',
                'delivery_charge' => $transaction->original_delivery_charge + $transaction->delivery_fee_comission,
                'tips' => $transaction->dm_tips,
                'commission_paid' => $transaction->delivery_fee_comission,
                'net_profit' => $transaction->original_delivery_charge + $transaction->dm_tips,
            ];
        });

        return $transactions;
    }

    public function get_deliveryman_incentive_transactions($request, $delivery_man_id, $filter, $from, $to, $nopaginate = false)
    {
        $search = $request->search ?? null;

        $query = Expense::with(['delivery_man'])
            ->where('type', 'incentive')
            ->when($delivery_man_id && $delivery_man_id !== 'all', function ($query) use ($delivery_man_id) {
                return $query->where('delivery_man_id', $delivery_man_id);
            })
            ->whereNotNull('delivery_man_id')
            ->applyDateFilter($filter, $from, $to, 'created_at')
            ->search($search, [], ['id'])
            ->latest();

        if ($nopaginate) {
            $transactions = $query->get();
        } else {
            $transactions = $query->paginate(config('default_pagination', 25))->withQueryString();
        }

        $collection = $nopaginate ? $transactions : $transactions->getCollection();

        $collection->transform(function ($transaction) {
            return [
                'transaction_id' => '#TXN ' . $transaction->id,
                'transaction_date' => $transaction->created_at->format('d M Y h:i a'),
                'incentive' => $transaction->amount,
            ];
        });

        return $transactions;
    }

    public function get_restaurant_transaction_components($request, $restaurant_id, $filter, $from, $to, $type)
    {
        $search = $request->search ?? null;
        $limit = $request->query('limit', 25);
        $offset = $request->query('offset', 1);

        $combinedQuery = null;

        if ($type === 'earning') {
            $orderSalesQuery = OrderTransaction::query()
                ->NotRefunded()
                ->when($restaurant_id && $restaurant_id != 'all', function ($q) use ($restaurant_id) {
                    return $q->where('vendor_id', $restaurant_id);
                })
                ->applyDateFilter($filter, $from, $to, 'created_at')
                ->where('restaurant_amount', '>', 0)
                ->selectRaw("
                    id as id,
                    created_at as date,
                    'Order Sales' as earning_source,
                    order_id as source_id,
                    restaurant_amount as amount,
                    'earning' as type
                ");

            $packagingQuery = OrderTransaction::query()
                ->NotRefunded()
                ->when($restaurant_id && $restaurant_id != 'all', function ($q) use ($restaurant_id) {
                    return $q->where('vendor_id', $restaurant_id);
                })
                ->applyDateFilter($filter, $from, $to, 'created_at')
                ->where('extra_packaging_amount', '>', 0)
                ->selectRaw("
                    id as id,
                    created_at as date,
                    'Extra Packaging' as earning_source,
                    order_id as source_id,
                    extra_packaging_amount as amount,
                    'earning' as type
                ");

            $taxQuery = OrderTransaction::query()
                ->NotRefunded()
                ->when($restaurant_id && $restaurant_id != 'all', function ($q) use ($restaurant_id) {
                    return $q->where('vendor_id', $restaurant_id);
                })
                ->applyDateFilter($filter, $from, $to, 'created_at')
                ->where('tax', '>', 0)
                ->selectRaw("
                    id as id,
                    created_at as date,
                    'Tax Collected' as earning_source,
                    order_id as source_id,
                    tax as amount,
                    'earning' as type
                ");

            if ($search) {
                $searchStr = str_replace(['#ORD', '#TXN', '#'], '', $search);
                $orderSalesQuery->where('order_id', 'like', "%{$searchStr}%");
                $packagingQuery->where('order_id', 'like', "%{$searchStr}%");
                $taxQuery->where('order_id', 'like', "%{$searchStr}%");
            }

            $combinedQuery = $orderSalesQuery->unionAll($packagingQuery)->unionAll($taxQuery);
        } else {
            $commissionQuery = OrderTransaction::query()
                ->NotRefunded()
                ->when($restaurant_id && $restaurant_id != 'all', function ($q) use ($restaurant_id) {
                    return $q->where('vendor_id', $restaurant_id);
                })
                ->applyDateFilter($filter, $from, $to, 'created_at')
                ->where('admin_commission', '>', 0)
                ->selectRaw("
                    id as id,
                    created_at as date,
                    'Commission Paid' as earning_source,
                    order_id as source_id,
                    admin_commission as amount,
                    'expense' as type
                ");

            $productDiscountQuery = Expense::query()
                ->applyDateFilter($filter, $from, $to, 'created_at')
                ->where('type', 'discount_on_product')
                ->where('created_by', 'vendor')
                ->when($restaurant_id && $restaurant_id != 'all', function ($q) use ($restaurant_id) {
                    return $q->where('restaurant_id', $restaurant_id);
                })
                ->selectRaw("
                    id as id,
                    created_at as date,
                    'Discount on Product' as earning_source,
                    order_id as source_id,
                    amount as amount,
                    'expense' as type
                ");

            $couponDiscountQuery = Expense::query()
                ->applyDateFilter($filter, $from, $to, 'created_at')
                ->where('type', 'coupon_discount')
                ->where('created_by', 'vendor')
                ->when($restaurant_id && $restaurant_id != 'all', function ($q) use ($restaurant_id) {
                    return $q->where('restaurant_id', $restaurant_id);
                })
                ->selectRaw("
                    id as id,
                    created_at as date,
                    'Coupon Contribution' as earning_source,
                    order_id as source_id,
                    amount as amount,
                    'expense' as type
                ");

            $freeDeliveryQuery = Expense::query()
                ->applyDateFilter($filter, $from, $to, 'created_at')
                ->where('type', 'free_delivery')
                ->where('created_by', 'vendor')
                ->when($restaurant_id && $restaurant_id != 'all', function ($q) use ($restaurant_id) {
                    return $q->where('restaurant_id', $restaurant_id);
                })
                ->selectRaw("
                    id as id,
                    created_at as date,
                    'Free Delivery' as earning_source,
                    order_id as source_id,
                    amount as amount,
                    'expense' as type
                ");

            $generalExpensesQuery = Expense::query()
                ->applyDateFilter($filter, $from, $to, 'created_at')
                ->whereNotNull('restaurant_id')
                ->when($restaurant_id && $restaurant_id !== 'all', function ($q) use ($restaurant_id) {
                    return $q->where('restaurant_id', $restaurant_id);
                })
                ->selectRaw("
                    id as id,
                    created_at as date,
                    REPLACE(type, '_', ' ') as earning_source,
                    order_id as source_id,
                    amount as amount,
                    'expense' as type
                ");

            if ($search) {
                $searchStr = str_replace(['#ORD', '#TXN', '#'], '', $search);
                $commissionQuery->where('order_transactions.order_id', 'like', "%{$searchStr}%");
                $productDiscountQuery->where('order_id', 'like', "%{$searchStr}%");
                $couponDiscountQuery->where('order_id', 'like', "%{$searchStr}%");
                $freeDeliveryQuery->where('order_id', 'like', "%{$searchStr}%");
                $generalExpensesQuery->where('order_id', 'like', "%{$searchStr}%");
            }

            $combinedQuery = $commissionQuery
                ->unionAll($productDiscountQuery)
                ->unionAll($couponDiscountQuery)
                ->unionAll($freeDeliveryQuery)
                ->unionAll($generalExpensesQuery);
        }

        $finalQuery = DB::table(DB::raw("({$combinedQuery->toSql()}) as combined_transactions"))
            ->mergeBindings($combinedQuery->getQuery())
            ->orderBy('date', 'desc');

        $total_size = $finalQuery->count();

        $offset_adjusted = ((int) $offset - 1) * (int) $limit;
        $results = $finalQuery->offset($offset_adjusted)->limit((int) $limit)->get();

        $components = $results->map(function ($row) {
            $date = Carbon::parse($row->date);
            return [
                'transaction_id' => '#TXN ' . $row->id,
                'date' => $date->format('d M Y h:i a'),
                'earning_source' => ucwords(strtolower($row->earning_source)),
                'order_id' => $row->source_id ? '#ORD ' . $row->source_id : '',
                'raw_order_id' => $row->source_id,
                'amount' => (float) $row->amount,
                'type' => $row->type
            ];
        })->toArray();

        return [
            'total_size' => $total_size,
            'limit' => (int) $limit,
            'offset' => (int) $offset,
            'data' => $components
        ];
    }

    public function get_deliveryman_transaction_components($request, $delivery_man_id, $filter, $from, $to)
    {
        $search = $request->search ?? null;
        $limit = $request->query('limit', 25);
        $offset = $request->query('offset', 1);
        $type = $request->query('type', 'order');

        if ($type === 'incentive') {
            $query = Expense::with(['delivery_man'])
                ->where('type', 'incentive')
                ->when($delivery_man_id && $delivery_man_id !== 'all', function ($query) use ($delivery_man_id) {
                    return $query->where('delivery_man_id', $delivery_man_id);
                })
                ->whereNotNull('delivery_man_id')
                ->applyDateFilter($filter, $from, $to, 'created_at')
                ->search($search, [], ['id'])
                ->latest();

            $transactions = $query->paginate($limit, ['*'], 'page', $offset);

            $components = $transactions->getCollection()->transform(function ($transaction) {
                return [
                    'transaction_id' => '#TXN ' . $transaction->id,
                    'transaction_date' => $transaction->created_at->format('d M Y h:i a'),
                    'incentive' => $transaction->amount,
                ];
            })->values()->all();
        } else {
            $query = OrderTransaction::where('delivery_man_id', $delivery_man_id)
                ->NotRefunded()
                ->applyDateFilter($filter, $from, $to, 'created_at')
                ->search($search, [], ['id'])
                ->latest();

            $transactions = $query->paginate($limit, ['*'], 'page', $offset);
            $transaction_order_ids = $transactions->getCollection()->pluck('order_id');
            $incentives = Expense::where('type', 'incentive')
                ->where('delivery_man_id', $delivery_man_id)
                ->whereIn('order_id', $transaction_order_ids)
                ->selectRaw('order_id, SUM(amount) as total_incentive')
                ->groupBy('order_id')
                ->pluck('total_incentive', 'order_id');

            $components = $transactions->getCollection()->transform(function ($transaction) use ($incentives) {
                $incentive = (float) ($incentives[$transaction->order_id] ?? 0);

                $delivery_charge = (float) ($transaction->original_delivery_charge + $transaction->delivery_fee_comission);
                $tips = (float) $transaction->dm_tips;
                $commission = (float) $transaction->delivery_fee_comission;

                $net_profit = (float)($transaction->original_delivery_charge + $incentive + $tips);

                return [
                    'order_id' => '#' . $transaction->order_id,
                    'date' => $transaction->created_at->format('d M Y h:i a'),
                    'delivery_charge' => $delivery_charge,
                    'incentive' => $incentive,
                    'tips' => $tips,
                    'commission_paid' => $commission,
                    'net_profit' => $net_profit
                ];
            })->values()->all();
        }

        return [
            'total_size' => $transactions->total(),
            'limit' => (int) $limit,
            'offset' => (int) $offset,
            'data' => $components
        ];
    }

    public function resolveDateFilter(Request $request): array
    {
        $filter = $request->query('filter', 'all_time');

        return [
            $filter,
            $filter === 'custom' ? $request->from : null,
            $filter === 'custom' ? $request->to : null,
        ];
    }
}
