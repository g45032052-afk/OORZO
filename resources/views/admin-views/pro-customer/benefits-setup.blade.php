@extends('layouts.admin.app')

@section('title', translate('messages.Pro_Customer_Benefits_Setup'))
@section('pro_customer_benefits_setup', 'active')

@section('content')
@php($minStep = \App\CentralLogics\Helpers::getDecimalPlaces())
@php($discountStatus = (int) ($settings['discount_status'] ?? 0))
@php($discountMinOrderStatus = (int) ($settings['discount_min_order_status'] ?? 0))
@php($deliveryStatus = (int) ($settings['delivery_fee_status'] ?? 0))
@php($deliveryOfferType = $settings['delivery_fee_offer_type'] ?? 'full_free')
@php($deliveryMinOrderStatus = (int) ($settings['delivery_fee_min_order_status'] ?? 0))
@php($couponStatus = (int) ($settings['coupon_status'] ?? 0))

<div class="content container-fluid">
    <div class="page-header mb-1">
        <div class="d-flex flex-wrap justify-content-between align-items-start">
            <h1 class="page-header-title text-capitalize">
                <span>{{ translate('messages.Pro_Customer_Benefits_Setup') }}</span>
            </h1>
        </div>
    </div>

    <form action="{{ route('admin.pro-customer.benefits-setup.update') }}" method="post" id="pro-benefits-form">
        @csrf

        {{-- Discount --}}
        <div class="card mb-20 view-details-container">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-1 fs-16">{{ translate('messages.discount') }}</h3>
                        <p class="mb-0 gray-dark fs-12">
                            {{ translate('messages.If enable this option pro customers will receive discount on every order') }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="view-btn text-primary cursor-pointer font-semibold d-flex align-items-center gap-1 {{ $discountStatus === 1 ? 'active' : '' }}">
                            <span class="text-underline">{{ translate('messages.view') }}</span>
                            <i class="tio-down-ui fs-12 {{ $discountStatus === 1 ? 'rotate-180deg' : '' }}"></i>
                        </div>
                        <label class="toggle-switch toggle-switch-sm mb-0">
                            <input type="checkbox" name="discount_status" value="1" class="toggle-switch-input pro-benefit-toggle"
                                data-benefit="discount" {{ $discountStatus === 1 ? 'checked' : '' }}>
                            <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </div>
                </div>
                <div class="bg-light2 p-xl-20 p-3 rounded view-details mt-4" data-section="discount" @if($discountStatus === 1) style="display:block" @endif>
                    <div class="d-flex flex-md-nowrp gap-2 flex-wrap align-items-center justify-content-between">
                        <div class="max-w-595">
                            <h3 class="mb-1 fs-16">{{ translate('messages.discount_setup') }}</h3>
                            <p class="mb-0 gray-dark fs-12">
                                {{ translate('messages.configure discount logic for pro customer') }}
                            </p>
                        </div>
                    </div>
                    <div class="p-xxl-20 p-3 rounded bg-white mt-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label class="input-label fw-400">
                                        {{ translate('messages.Discount (%)') }}
                                        <span class="text-danger">*</span>
                                        <span class="form-label-secondary m-0" data-toggle="tooltip" data-placement="right" data-original-title="Discount percentage for Pro customers.">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input type="number" name="discount_percentage" class="form-control pro-discount-field"
                                        min="{{ $minStep }}" step="{{ $minStep }}" max="100"
                                        placeholder="{{ translate('messages.Ex: 5') }}"
                                        value="{{ $settings['discount_percentage'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label class="input-label fw-400">
                                        {{ translate('messages.Up_To_Discount_Amount') }} ($)
                                        <span class="text-danger">*</span>
                                        <span class="form-label-secondary m-0" data-toggle="tooltip" data-placement="right" data-original-title="Maximum discount per order.">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <input type="number" name="discount_max_amount" class="form-control pro-discount-field"
                                        min="{{ $minStep }}" step="{{ $minStep }}"
                                        placeholder="{{ translate('messages.Ex: 5') }}"
                                        value="{{ $settings['discount_max_amount'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <div class="d-flex align-items-center justify-content-between mb-2 gap-2 flex-wrap">
                                        <label class="input-label mb-0 fw-400">
                                            {{ translate('messages.Minimum Order Amount ($)') }}
                                            <span class="text-danger">*</span>
                                            <span class="form-label-secondary m-0" data-toggle="tooltip" data-placement="right" data-original-title="Minimum order value required for discount">
                                                <i class="tio-info text-gray1 fs-16"></i>
                                            </span>
                                        </label>
                                        <label class="toggle-switch toggle-switch-sm mb-0">
                                            <input type="checkbox" name="discount_min_order_status" value="1"
                                                class="toggle-switch-input pro-min-order-toggle"
                                                data-target="#discount_min_order_amount"
                                                {{ $discountMinOrderStatus === 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                    <input type="number" name="discount_min_order_amount" id="discount_min_order_amount"
                                        class="form-control pro-discount-field"
                                        min="{{ $minStep }}" step="{{ $minStep }}"
                                        placeholder="{{ translate('messages.Ex: 5') }}"
                                        value="{{ $settings['discount_min_order_amount'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Delivery Fee --}}
        <div class="card mb-20 view-details-container">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-1 fs-16">{{ translate('messages.delivery_fee') }}</h3>
                        <p class="mb-0 gray-dark fs-12">
                            {{ translate('messages.If enable this option pro customers will receive free delivery on every order') }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="view-btn text-primary cursor-pointer font-semibold d-flex align-items-center gap-1 {{ $deliveryStatus === 1 ? 'active' : '' }}">
                            <span class="text-underline">{{ translate('messages.view') }}</span>
                            <i class="tio-down-ui fs-12 {{ $deliveryStatus === 1 ? 'rotate-180deg' : '' }}"></i>
                        </div>
                        <label class="toggle-switch toggle-switch-sm mb-0">
                            <input type="checkbox" name="delivery_fee_status" value="1"
                                class="toggle-switch-input pro-benefit-toggle"
                                data-benefit="delivery" {{ $deliveryStatus === 1 ? 'checked' : '' }}>
                            <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </div>
                </div>
                <div class="bg-light2 p-xl-20 p-3 rounded view-details mt-4" data-section="delivery" @if($deliveryStatus === 1) style="display:block" @endif>
                    <div class="row g-3 align-items-start">
                        <div class="col-md-4">
                            <div>
                                <h3 class="mb-1 fs-16">{{ translate('messages.choose_one_offer') }}</h3>
                                <p class="mb-0 gray-dark fs-12">
                                    {{ translate('messages.Select the delivery offer you want to provide to your customers.') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="input-label fw-400">
                                    {{ translate('messages.Offer Type') }}
                                    <span class="text-danger">*</span>
                                    <span class="form-label-secondary m-0" data-toggle="tooltip" data-placement="right" data-original-title="Select a delivery offer type.">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                {{-- Shaped like a form-control so it lines up with the input in the
                                     next column: p-3 made this box taller than a 45px field, so the
                                     two sat at different heights in the same row. Same pattern the
                                     business settings pages use for a control-shaped box. --}}
                                <div class="h--45px d-flex align-items-center gap-3 justify-content-between border rounded px-3 py-0 form-control mb-0">
                                    <label class="form-check radio-sclae-1 d-flex gap-2 mb-0">
                                        <input type="radio" name="delivery_fee_offer_type" value="full_free"
                                            class="form-check-input radio--input pro-delivery-offer-type"
                                            {{ $deliveryOfferType === 'full_free' ? 'checked' : '' }}>
                                        <span class="user-select-none form-check-label">{{ translate('messages.Full_Free') }}</span>
                                    </label>
                                    <label class="form-check radio-sclae-1 d-flex gap-2 mb-0">
                                        <input type="radio" name="delivery_fee_offer_type" value="partial_free"
                                            class="form-check-input radio--input pro-delivery-offer-type"
                                            {{ $deliveryOfferType === 'partial_free' ? 'checked' : '' }}>
                                        <span class="user-select-none form-check-label">{{ translate('messages.Partial_Free') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 pro-delivery-full-free" @if($deliveryOfferType !== 'full_free') style="display:none" @endif>
                            <div class="form-group mb-0">
                                <div class="d-flex align-items-center justify-content-between mb-2 gap-2 flex-wrap">
                                    <label class="input-label mb-0 fw-400">
                                        {{ translate('messages.Minimum Order Amount ($)') }}
                                        <span class="form-label-secondary m-0" data-toggle="tooltip" data-placement="right" data-original-title="Minimum order value required for discount">
                                            <i class="tio-info text-gray1 fs-16"></i>
                                        </span>
                                    </label>
                                    <label class="toggle-switch toggle-switch-sm mb-0">
                                        <input type="checkbox" name="delivery_fee_min_order_status" value="1"
                                            class="toggle-switch-input pro-min-order-toggle"
                                            data-target="#delivery_fee_min_order_amount"
                                            {{ $deliveryMinOrderStatus === 1 ? 'checked' : '' }}>
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                                <input type="number" name="delivery_fee_min_order_amount" id="delivery_fee_min_order_amount"
                                    class="form-control pro-delivery-field"
                                    min="{{ $minStep }}" step="{{ $minStep }}"
                                    placeholder="{{ translate('messages.Ex: 5') }}"
                                    value="{{ $settings['delivery_fee_min_order_amount'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-4 pro-delivery-partial-free" @if($deliveryOfferType !== 'partial_free') style="display:none" @endif>
                            <div class="form-group mb-0">
                                <label class="input-label fw-400">
                                    {{ translate('messages.Delivery_Charge_Discount (%)') }}
                                    <span class="text-danger">*</span>
                                    <span class="form-label-secondary m-0" data-toggle="tooltip" data-placement="right" data-original-title="Delivery fee discount percentage.">
                                        <i class="tio-info text-gray1 fs-16"></i>
                                    </span>
                                </label>
                                <input type="number" name="delivery_charge_discount"
                                    class="form-control pro-delivery-field"
                                    min="{{ $minStep }}" step="{{ $minStep }}" max="100"
                                    placeholder="{{ translate('messages.Ex: 5') }}"
                                    value="{{ $settings['delivery_charge_discount'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Coupon --}}
        <div class="card py-3 px-xxl-4 px-3 mb-15">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h3 class="mb-1 fs-16">{{ translate('messages.coupon') }}</h3>
                    <p class="mb-0 gray-dark fs-12">
                        {{ translate('messages.Create special coupons from') }}
                        <a href="{{ route('admin.coupon.add-new') }}" class="text-underline">{{ translate('messages.coupons') }}</a>
                        {{ translate('messages.and choose Pro Customer as the coupon type.') }}
                    </p>
                </div>
                <label class="toggle-switch toggle-switch-sm mb-0">
                    <input type="checkbox" name="coupon_status" value="1"
                        class="toggle-switch-input pro-benefit-toggle"
                        data-benefit="coupon" {{ $couponStatus === 1 ? 'checked' : '' }}>
                    <span class="toggle-switch-label text">
                        <span class="toggle-switch-indicator"></span>
                    </span>
                </label>
            </div>
        </div>

        <div class="btn--container justify-content-end mt-4">
            <button type="reset" class="btn btn--reset min-w-120">{{ translate('messages.reset') }}</button>
            <button type="submit" class="btn btn--primary min-w-120">
                <i class="tio-save"></i> {{ translate('messages.save_information') }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('script_2')
<script>
    "use strict";

    // Mutual exclusion + always-on: exactly one of discount/delivery/coupon stays enabled.
    $(document).on('change', '.pro-benefit-toggle', function () {
        if (this.checked) {
            $('.pro-benefit-toggle').not(this).each(function () {
                if (this.checked) {
                    this.checked = false;
                    syncSectionVisibility(this);
                }
            });
        } else {
            // User tried to disable the only active benefit. Force-enable Discount.
            var anyOn = $('.pro-benefit-toggle:checked').length > 0;
            if (!anyOn) {
                var $discount = $('.pro-benefit-toggle[data-benefit="discount"]');
                $discount.prop('checked', true);
                syncSectionVisibility($discount[0]);
            }
        }
        syncSectionVisibility(this);
        applyBenefitState();
    });

    // Slide the section open/closed and rotate the chevron in sync with the toggle.
    function syncSectionVisibility(toggleEl) {
        var $container = $(toggleEl).closest('.view-details-container');
        var $details = $container.find('.view-details');
        var $btn = $container.find('.view-btn');
        var $icon = $btn.find('i');
        if (toggleEl.checked) {
            $details.stop(true, true).slideDown(200);
            $btn.addClass('active');
            $icon.addClass('rotate-180deg');
        } else {
            $details.stop(true, true).slideUp(200);
            $btn.removeClass('active');
            $icon.removeClass('rotate-180deg');
        }
    }

    // Min-order toggles inside Discount and Delivery sections drive their own field.
    $(document).on('change', '.pro-min-order-toggle', function () {
        applyBenefitState();
    });

    // Offer type radio toggles which Delivery field is visible/required.
    $(document).on('change', '.pro-delivery-offer-type', function () {
        if ($(this).val() === 'full_free') {
            $('.pro-delivery-full-free').show();
            $('.pro-delivery-partial-free').hide();
        } else {
            $('.pro-delivery-full-free').hide();
            $('.pro-delivery-partial-free').show();
        }
        applyBenefitState();
    });

    function applyBenefitState() {
        var discountOn = $('.pro-benefit-toggle[data-benefit="discount"]').is(':checked');
        var deliveryOn = $('.pro-benefit-toggle[data-benefit="delivery"]').is(':checked');

        // Discount fields
        var discountMinOn = $('input[name="discount_min_order_status"]').is(':checked');
        $('input[name="discount_percentage"]').each(function () {
            setFieldState(this, discountOn, true);
        });
        $('input[name="discount_max_amount"]').each(function () {
            setFieldState(this, discountOn, true);
        });
        $('input[name="discount_min_order_amount"]').each(function () {
            setFieldState(this, discountOn && discountMinOn, true);
        });

        // Delivery fields
        var offerType = $('input[name="delivery_fee_offer_type"]:checked').val() || 'full_free';
        var deliveryMinOn = $('input[name="delivery_fee_min_order_status"]').is(':checked');
        $('input[name="delivery_fee_min_order_amount"]').each(function () {
            var active = deliveryOn && offerType === 'full_free' && deliveryMinOn;
            setFieldState(this, active, true);
        });
        $('input[name="delivery_charge_discount"]').each(function () {
            var active = deliveryOn && offerType === 'partial_free';
            setFieldState(this, active, true);
        });
    }

    function setFieldState(el, active, requireWhenActive) {
        var $el = $(el);
        if (active) {
            $el.prop('readonly', false);
            if (requireWhenActive) $el.prop('required', true);
        } else {
            $el.prop('required', false);
            $el.prop('readonly', true);
        }
    }

    $(function () {
        applyBenefitState();
    });

    // Block submit if more than one benefit toggle is enabled (defense in depth).
    $('#pro-benefits-form').on('submit', function (e) {
        var enabled = $('.pro-benefit-toggle:checked').length;
        if (enabled > 1) {
            e.preventDefault();
            toastr.error('{{ translate('messages.only_one_pro_customer_benefit_can_be_enabled_at_a_time') }}');
        }
    });
</script>
@endpush
