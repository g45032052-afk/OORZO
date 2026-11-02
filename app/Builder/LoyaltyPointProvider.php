<?php

namespace App\Builder;

use App\CentralLogics\CustomerLogic;
use App\Models\BusinessSetting;
use App\Models\LoyaltyPointTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Builder\Contracts\LoyaltyPointProvider as LoyaltyPointProviderContract;
use Modules\Builder\ValueObjects\PaginatedResult;
use Modules\Builder\ValueObjects\Storefront\LoyaltySummaryDTO;

/**
 * StackFood host adapter for the Builder loyalty-points tab.
 *
 * StackFood is food-only / single-module, so the 6amMart loyalty seam collapses
 * to a single host: `users.loyalty_point` + the `loyalty_point_transactions`
 * table, gated by the `loyalty_point_*` business settings. The conversion math
 * is owned by `App\CentralLogics\CustomerLogic` (the host's authoritative
 * exchange-rate logic) — this adapter only orchestrates the guards, locking,
 * and DTO mapping the storefront wire contract requires.
 */
class LoyaltyPointProvider implements LoyaltyPointProviderContract
{
    /**
     * Humanised labels for the known `transaction_type` values. Unknown values
     * fall back to title-cased text in {@see self::typeLabel()}.
     */
    private const TYPE_LABELS = [
        'point_to_wallet' => 'Point To Wallet',
        'order_place'     => 'Order Place',
    ];

    /**
     * Per-request cache of the loyalty business settings so a `summary()` call
     * followed by a `convertToWallet()` call (the modal-submit path) does not
     * issue two identical `business_settings` queries.
     *
     * @var array<string,string|null>|null
     */
    private ?array $settings = null;

    public function summary(int $customerId): LoyaltySummaryDTO
    {
        $settings = $this->loyaltySettings();

        $user = $customerId > 0
            ? User::query()->find($customerId, ['id', 'loyalty_point', 'wallet_balance'])
            : null;

        return LoyaltySummaryDTO::fromArray([
            'balance'           => (float) ($user->loyalty_point ?? 0),
            'walletBalance'     => (float) ($user->wallet_balance ?? 0),
            'exchangeRate'      => (float) ($settings['loyalty_point_exchange_rate'] ?? 0),
            'minimumPoint'      => (int) ($settings['loyalty_point_minimum_point'] ?? 0),
            'itemPurchasePoint' => (float) ($settings['loyalty_point_item_purchase_point'] ?? 0),
            'status'            => (bool) ((int) ($settings['loyalty_point_status'] ?? 0) === 1),
        ]);
    }

    public function transactions(int $customerId, int $perPage, int $page): PaginatedResult
    {
        $perPage = \max(1, \min($perPage, 100));
        $page    = \max(1, $page);

        if ($customerId <= 0) {
            return new PaginatedResult(items: [], total: 0, perPage: $perPage, currentPage: $page, lastPage: 1);
        }

        $paginator = LoyaltyPointTransaction::query()
            ->where('user_id', $customerId)
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return PaginatedResult::fromPaginator($paginator, fn ($txn) => $this->mapTransaction($txn));
    }

    public function convertToWallet(int $customerId, int $point): array
    {
        $settings = $this->loyaltySettings();

        if ($customerId <= 0) {
            return $this->failure('auth', 'You must be signed in to convert loyalty points.');
        }

        if ((int) ($settings['loyalty_point_status'] ?? 0) !== 1) {
            return $this->failure('loyalty_disabled', 'Loyalty points are currently disabled.');
        }

        $exchangeRate = (float) ($settings['loyalty_point_exchange_rate'] ?? 0);
        if ($exchangeRate <= 0) {
            return $this->failure('exchange_rate', 'Loyalty point exchange rate is not configured.');
        }

        $minimumPoint = (int) ($settings['loyalty_point_minimum_point'] ?? 0);

        $user = User::query()->find($customerId, ['id', 'loyalty_point', 'wallet_balance']);
        if (!$user) {
            return $this->failure('auth', 'Customer not found.');
        }

        if ($point < \max(1, $minimumPoint) || $point > (int) $user->loyalty_point) {
            return $this->failure('point', 'Insufficient loyalty points for this conversion.');
        }

        $walletTransaction = null;

        try {
            // Outer transaction + a locked re-read of the user row so two
            // concurrent submissions cannot both see the same balance and
            // overspend. CustomerLogic wraps its own writes in nested
            // transactions; Laravel turns those into savepoints, so an outer
            // rollback unwinds both helpers atomically.
            DB::transaction(function () use ($user, $point, &$walletTransaction) {
                $locked = User::query()->lockForUpdate()->find($user->id);
                if (!$locked || (int) $locked->loyalty_point < $point) {
                    throw new \RuntimeException('insufficient_point');
                }

                // For transaction_type 'loyalty_point' CustomerLogic computes the
                // wallet credit as floor(amount / loyalty_point_exchange_rate)
                // and returns the WalletTransaction (its transaction_id is the
                // reference for the loyalty debit row). It returns false when the
                // host's wallet feature is disabled.
                $walletTransaction = CustomerLogic::create_wallet_transaction(
                    user_id: $locked->id,
                    amount: (float) $point,
                    transaction_type: 'loyalty_point',
                    referance: null,
                );

                if (!$walletTransaction) {
                    throw new \RuntimeException('wallet_transaction_failed');
                }

                // Debits users.loyalty_point and writes the matching
                // point_to_wallet row (host re-checks loyalty_point_status).
                CustomerLogic::create_loyalty_point_transaction(
                    user_id: $locked->id,
                    referance: $walletTransaction->transaction_id,
                    amount: $point,
                    transaction_type: 'point_to_wallet',
                );
            });
        } catch (\Throwable $ex) {
            \info('LoyaltyPointProvider::convertToWallet failed: ' . $ex->getMessage());

            return $this->failure('transfer_failed', 'Failed to convert loyalty points to wallet.');
        }

        $fresh = User::query()->find($customerId, ['id', 'loyalty_point', 'wallet_balance']);

        return [
            'success'          => true,
            'pointsConverted'  => $point,
            'walletCredit'     => (float) ($walletTransaction->credit ?? 0),
            'newBalance'       => (float) ($fresh->loyalty_point ?? 0),
            'newWalletBalance' => (float) ($fresh->wallet_balance ?? 0),
            'errors'           => [],
        ];
    }

    /**
     * Map one loyalty_point_transactions row to the flat wire DTO the React
     * loyalty history table consumes.
     *
     * @return array<string,mixed>
     */
    private function mapTransaction(LoyaltyPointTransaction $txn): array
    {
        $credit = (float) $txn->credit;
        $debit  = (float) $txn->debit;
        $type   = (string) ($txn->transaction_type ?? '');

        // The model declares $timestamps = false but the host writes created_at
        // directly via CustomerLogic, so read it defensively.
        $createdAt = $txn->getAttribute('created_at');
        $date = $createdAt
            ? Carbon::parse($createdAt)->format('d M Y, h:iA')
            : '';

        $direction = $credit >= $debit ? 'credit' : 'debit';
        $amount    = $direction === 'credit' ? $credit : $debit;

        return [
            'id'            => (int) $txn->id,
            'transactionId' => (string) ($txn->transaction_id ?? ''),
            'type'          => $type,
            'typeLabel'     => $this->typeLabel($type),
            'credit'        => $credit,
            'debit'         => $debit,
            'amount'        => $amount,
            'direction'     => $direction,
            'balance'       => (float) $txn->balance,
            'reference'     => $txn->reference !== null ? (string) $txn->reference : null,
            'date'          => $date,
        ];
    }

    private function typeLabel(string $type): string
    {
        if ($type === '') {
            return '';
        }

        return self::TYPE_LABELS[$type] ?? \ucwords(\str_replace('_', ' ', $type));
    }

    /**
     * Loyalty business settings, cached per request.
     *
     * @return array<string,string|null>
     */
    private function loyaltySettings(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        return $this->settings = BusinessSetting::query()
            ->whereIn('key', [
                'loyalty_point_status',
                'loyalty_point_exchange_rate',
                'loyalty_point_minimum_point',
                'loyalty_point_item_purchase_point',
            ])
            ->pluck('value', 'key')
            ->all();
    }

    /**
     * @return array{success:bool,pointsConverted:null,walletCredit:null,newBalance:null,newWalletBalance:null,errors:array<int,array{code:string,message:string}>}
     */
    private function failure(string $code, string $message): array
    {
        return [
            'success'          => false,
            'pointsConverted'  => null,
            'walletCredit'     => null,
            'newBalance'       => null,
            'newWalletBalance' => null,
            'errors'           => [[
                'code'    => $code,
                'message' => $message,
            ]],
        ];
    }
}
