<div class="js-nav-scroller hs-nav-scroller-horizontal mb-3">
    <ul class="nav nav-tabs border-0 nav--tabs nav--pills nav--theme-version" id="pro-additional-tabs">
        <li class="nav-item">
            <a class="nav-link text-capitalize {{ Request::is('admin/pro-customer/additional-setup*') ? 'active' : '' }}"
                href="{{ route('admin.pro-customer.additional-setup') }}">
                {{ translate('messages.FAQ') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-capitalize {{ Request::is('admin/pro-customer/terms-and-conditions*') ? 'active' : '' }}"
                href="{{ route('admin.pro-customer.terms-and-conditions') }}">
                {{ translate('messages.Terms_and_Conditions') }}
            </a>
        </li>
    </ul>
</div>
