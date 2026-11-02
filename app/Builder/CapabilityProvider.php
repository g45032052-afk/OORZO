<?php

namespace App\Builder;

use App\CentralLogics\Helpers;
use Modules\Builder\Contracts\CapabilityProvider as CapabilityProviderContract;
use Modules\Builder\ValueObjects\HostCapabilities;
use Modules\Builder\ValueObjects\StorefrontScope;

/**
 * StackFood capability manifest adapter.
 *
 * Passes through the host-owned `config/builder.php` capabilities block, with
 * the legacy `wallet_features_enabled` flag forced over the manifest (back-compat
 * shim) so wallet/partial mirror the operator's single source of truth during
 * the transition. Identical to the 6amMart adapter — the per-project values live
 * in config, not here.
 */
class CapabilityProvider implements CapabilityProviderContract
{
    public function capabilities(?StorefrontScope $scope): HostCapabilities
    {
        $config = (array) config('builder.capabilities', []);

        $walletOn = (bool) config('builder.wallet_features_enabled', true);
        $config['features']['wallet'] = $walletOn;
        $config['payment']['wallet']  = $walletOn;
        $config['payment']['partial'] = $walletOn;

        // Guest checkout reflects the operator's live Business Settings toggle
        // (the same flag the place-order pipeline enforces), so the storefront's
        // checkout-page guard + Proceed-to-Checkout CTA stay in sync with it.
        $config['features']['guestCheckout'] = (bool) Helpers::get_mail_status('guest_checkout_status');

        return HostCapabilities::fromArray($config);
    }
}
