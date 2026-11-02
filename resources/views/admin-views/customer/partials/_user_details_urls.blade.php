<div class="js-nav-scroller hs-nav-scroller-horizontal mb-3">
            <ul class="nav nav-tabs border-0 nav--tabs nav--pills nav--theme-version">
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('admin.customer.view') ? 'active' : '' }}" href="{{ route('admin.customer.view', $customer->id) }}">{{ translate('Overview') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('admin.customer.order-list') ? 'active' : '' }}" href="{{ route('admin.customer.order-list', $customer->id) }}">{{ translate('Order List') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('admin.customer.wish-list') ? 'active' : '' }}" href="{{ route('admin.customer.wish-list', $customer->id) }}">{{ translate('Wishlist') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  {{ Route::is('admin.customer.review-list') ? 'active' : '' }}" href="{{ route('admin.customer.review-list', $customer->id) }}">{{ translate('Rating & Reviews') }}</a>
                </li>
                {{-- Wallet / loyalty / referral are hidden for storefront (Builder)
                     customers (sub_tenant_id > 0); host customers only. --}}
                @if ((int) ($customer->sub_tenant_id ?? 0) <= 0)
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.customer.wallet-history') ? 'active' : '' }}" href="{{ route('admin.customer.wallet-history', $customer->id) }}">{{ translate('Wallet') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.customer.loyalty-point') ? 'active' : '' }} " href="{{ route('admin.customer.loyalty-point', $customer->id) }}">Loyalty point</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.customer.referral') ? 'active' : '' }} " href="{{ route('admin.customer.referral', $customer->id) }}">{{ translate('Referral') }}</a>
                    </li>
                @endif
                @if (\App\CentralLogics\Helpers::get_business_settings('pro_member_status') == 1)
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.customer.subscription-plan') ? 'active' : '' }}"
                            href="{{ route('admin.customer.subscription-plan', $customer->id) }}">{{ translate('messages.Subscription_Plan') }}</a>
                    </li>
                @endif
            </ul>
        </div>
