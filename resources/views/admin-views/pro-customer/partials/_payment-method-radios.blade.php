
@php
    $idPrefix = $idPrefix ?? 'pm';
    $balance = (float) ($customer->wallet_balance ?? 0);
    $price = (float) ($price ?? 0);
    $hasEnough = $balance >= $price;
@endphp
<div class="payment-method-section pt-3 js-payment-method" data-wallet-balance="{{ $balance }}">
    <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
        <p class="fs-14 mb-0 text-capitalize text-dark fw-500">{{ translate('messages.Choose_Payment_Method') }}</p>
        <span class="gray-dark fs-12">
            {{ translate('messages.Wallet_Balance') }}:
            <span class="fw-500 text-dark">{{ \App\CentralLogics\Helpers::format_currency($balance) }}</span>
        </span>
    </div>
    <div class="row g-2">
        <div class="col-md-6">
            <label class="border rounded p-3 d-flex align-items-center gap-2 cursor-pointer mb-0 w-100 js-pay-wallet-label {{ $hasEnough ? '' : 'opacity-50' }}">
                <input type="radio" name="payment_method" value="wallet"
                    id="{{ $idPrefix }}_pay_wallet"
                    class="js-pay-wallet"
                    {{ $hasEnough ? 'checked' : 'disabled' }}>
                <span class="flex-grow-1 fs-14 text-dark text-capitalize">{{ translate('messages.Pay_via_Wallet') }}</span>
                <img width="22" src="{{ dynamicAsset('assets/admin/img/new-wallet.svg') }}" alt="wallet">
            </label>
            <small class="text-danger d-{{ $hasEnough ? 'none' : 'block' }} mt-1 js-wallet-low-msg">
                {{ translate('messages.Insufficient_wallet_balance') }}
            </small>
        </div>
        <div class="col-md-6">
            <label class="border rounded p-3 d-flex align-items-center gap-2 cursor-pointer mb-0 w-100">
                <input type="radio" name="payment_method" value="manual"
                    id="{{ $idPrefix }}_pay_manual"
                    {{ $hasEnough ? '' : 'checked' }}>
                <span class="flex-grow-1 fs-14 text-dark text-capitalize">{{ translate('messages.Manually_Pay') }}</span>
            </label>
        </div>
    </div>
</div>
