@extends('layouts.vendor.app')

@section('title',translate('messages.test'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-20">
            <h1 class="fs-20 mb-1">{{ translate('Domain Setting') }}</h1>
            <p class="mb-0">{{ translate('Torem ipsum dolor sit amet, consectetur adipiscing elit. Nunc vulputate libero et velit interdum, ac aliquet odio mattis.') }}</p>
        </div>
        <div class="card card-body mb-3">
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="">
                        <h4 class="mb-1">{{ translate('Website Visibility Setup') }}</h4>
                        <p class="fs-12 mb-0">{{ translate('When visibility is turned off, your website will not be accessible to customers. You can still preview and manage your website as a vendor.') }}.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <label
                        class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0">
                        <span class="pr-1 d-flex align-items-center switch--label">
                            {{ translate('Visibility_Status') }}
                        </span>
                        <input type="checkbox"
    
                            data-id=""
                            data-type="toggle"
                            data-image-on="{{ dynamicAsset('assets/admin/img/modal/warning.png') }}"
                            data-image-off="{{ dynamicAsset('assets/admin/img/modal/warning.png') }}"
                            data-title-on="{{ translate('are_you_sure_to_enable_vendor_website_setup') }}?"
                            data-title-off="{{ translate('are_you_sure_to_disable_vendor_website_setup') }}?"
                            data-text-on="<p>{{ translate('vendors_will_be_able_to__customize_and_manage_their_own_websites_independently,_including_updating_content,_design,_and_settings.') }}</p>"
                            data-text-off="<p>{{ translate('vendors_will_not_be_able_to_customize_or_manage_their_own_websites_independently,_including_updating_content,_design,_or_settings') }}</p>"
                            class="toggle-switch-input dynamic-checkbox-toggle"
    
                            value="1"
                            name="" id="">
                        <span class="toggle-switch-label text">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                </div>
            </div>
        </div>
        <div class="card card-body mb-3">
            <div class="mb-20">
                <h4 class="mb-1 d-flex gap-2 align-items-center">
                    {{ translate('Create Sub Domain for Your Website') }}
                    <div class="px-2 py-1 rounded fs-12 text-white bg-primary">{{ translate('Free') }}</div>
                </h4>
                <p class="fs-12 mb-0">{{ translate('Provide Sub domain URL link for your website') }}</p>
            </div>
            <div class="p-3 global-bg-box rounded mb-2 d-flex gap-2 flex-wrap flex-sm-nowrap justify-content-end">
                <div class="input-group flex-grow-1">
                    <input type="text" class="form-control" placeholder="storefront">
                    <span class="input-group-text text-title bg-soft-dark">.6amMart.com</span>
                </div>
                <button type="button" class="btn btn--primary flex-shrink-0">{{ translate('Save URL') }}</button>
            </div>
            <div class="px-3 py-2 global-bg-box rounded">
                <div class="d-flex gap-2">
                    <span class="text-primary">{{ translate('URL Preview') }} :</span>
                    <span class="text-dark font-medium">storefront.6amMart.com</span>
                </div>
            </div>
        </div>

        <div class="text-center mb-3">
            <a href="#customDomainSection" id="advancedSettingsToggle"
               class="btn text-primary bg-white rounded-pill border fs-16 px-4 py-2 d-inline-flex align-items-center gap-2"
               data-toggle="collapse" data-target="#customDomainSection" aria-expanded="false" aria-controls="customDomainSection">
                <span class="toggle-label-show">{{ translate('Advanced Settings') }} <i class="tio-arrow-downward fs-14"></i></span>
                <span class="toggle-label-hide d-none">{{ translate('See Less') }} <i class="tio-arrow-upward fs-14"></i></span>
            </a>
        </div>

        <div class="collapse" id="customDomainSection">
            <div class="card card-body mb-3">
                <div class="mb-20">
                    <h4 class="mb-1 d-flex gap-2 align-items-center">
                        {{ translate('Setup Custom Domain') }}
                    </h4>
                    <p class="fs-12 mb-0">{{ translate('Provide custom URL link for your website') }}</p>
                </div>
                <div class="fs-12 text-dark px-3 py-2 rounded bg-warning mb-20" style="--bs-bg-opacity: 0.1;">
                    <div class="d-flex gap-2">
                        <span class="text-warning lh-1 fs-14 flex-shrink-0">
                            <i class="tio-info"></i>
                        </span>
                        <span>{{ translate('Domain Connection Instructions -') }}</span>
                    </div>
                    <ul class="mb-0 text-body">
                        <li>{{ translate('Go to you domain control panel') }}</li>
                        <li>{{ translate('Add a DNS record (A record) for the IP x.x.x.x') }}</li>
                        <li>{{ translate('If you do not have any DNS Setup option, then connect your domain with cloudflare with nameserver') }}</li>
                        <li>{{ translate('Add the mentioned A record in Cloudflares DNS configuration') }}</li>
                    </ul>
                </div>
                <div class="p-3 global-bg-box rounded mb-2 d-flex gap-2 flex-wrap flex-sm-nowrap justify-content-end">
                    <input type="text" class="form-control flex-grow-1" placeholder="{{ translate('enter_custom_domain_here') }}">
                    <button type="button" class="btn btn--primary flex-shrink-0">{{ translate('Connect') }}</button>
                </div>
                <div class="px-3 py-2 global-bg-box rounded">
                    <div class="d-flex gap-2">
                        <span class="text-primary">{{ translate('URL Preview') }} :</span>
                        <span class="text-dark font-medium">website.com</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
<script>
    "use strict";
    (function () {
        let $toggle  = $('#advancedSettingsToggle');
        let $show    = $toggle.find('.toggle-label-show');
        let $hide    = $toggle.find('.toggle-label-hide');

        $('#customDomainSection').on('show.bs.collapse', function () {
            $show.addClass('d-none');
            $hide.removeClass('d-none');
        }).on('hide.bs.collapse', function () {
            $hide.addClass('d-none');
            $show.removeClass('d-none');
        });
    })();
</script>
@endpush
