@extends('layouts.admin.app')

@section('title', translate('Settings'))

@section('content')
    <div class="content">
        <form action="{{ route('admin.business-settings.update-payment-setup') }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="container-fluid">
                <div class="page-header pb-0">
                    @include('admin-views.business-settings.partials._note')
                    @include('admin-views.business-settings.partials.nav-menu')
                </div>

                <div class="card card-body">
                    <div class="mb-20">
                        <h3 class="mb-1">{{ translate('messages.Payment_Options') }}</h3>
                        <p class="fs-12 mb-0">{{ translate('messages.Setup your business time zone and format from here') }}</p>
                    </div>
                    <div class="bg-light rounded-10 p-3 p-sm-4 mb-20" id="payment_option">
                        <div class="row g-3 border rounded bg-white">
                            <div class="col-lg-4">
                                <div class="custom-checkbox custom-control d-flex gap-2 h-100">
                                    <input type="checkbox" class="custom-control-input" value="cod" name="cash_on_delivery" id='cash_on_delivery' {{ $cash_on_delivery ? 'checked' : '' }}>
                                    <label class="custom-control-label d-flex flex-column justify-content-between mb-0"  for="cash_on_delivery">
                                        <div>
                                            <h5 class="mb-1">
                                                {{ translate('messages.Cash On Delivery') }}
                                            </h5>
                                            <p class="fs-12 mb-0">{{ translate('messages.Allow customers to pay in cash when the order is delivered. This is a convenient option for users who prefer not to pay online.') }}</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="custom-checkbox custom-control d-flex gap-2 h-100">
                                    <input type="checkbox" class="custom-control-input" value="digital_payment" name="digital_payment" id='digital_payment' {{ $digital_payment ? 'checked' : '' }}>
                                    <label class="custom-control-label d-flex flex-column justify-content-between mb-0"  for="digital_payment">
                                        <div>
                                            <h5 class="mb-1 d-flex gap-1 align-items-center">
                                                {{ translate('messages.Digital Payment') }}
                                                @php
                                                    $payment_config_warning = \App\Models\Setting::where('settings_type', 'payment_config')->count() == 0 || \App\Models\Setting::where('settings_type', 'payment_config')->where('is_active', 1)->count() == 0;
                                                @endphp
                                                @if($payment_config_warning)
                                                <i class="tio-warning text-warning"></i>
                                                @endif
                                            </h5>
                                            <p class="fs-12 mb-0">{{ translate('messages.Enable customers to make secure online payments using supported payment gateways. Ideal for fast and seamless transactions.') }}</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="custom-checkbox custom-control d-flex gap-2 h-100">
                                    <input type="checkbox" class="custom-control-input" value="offline_payment_status" name="offline_payment_status" id='offline_payment' {{ $offline_payment ? 'checked' : '' }}>
                                    <label class="custom-control-label d-flex flex-column justify-content-between mb-0"  for="offline_payment">
                                        <div>
                                            <h5 class="mb-1">
                                                {{ translate('messages.Offline payment') }}
                                                @php
                                                    $offline_payment_warning = \App\Models\OfflinePaymentMethod::count() == 0 || \App\Models\OfflinePaymentMethod::where('status', 1)->count() == 0;
                                                @endphp
                                                @if($offline_payment_warning)
                                                <i class="tio-warning text-warning"></i>
                                                @endif
                                            </h5>
                                            <p class="fs-12 mb-0">{{ translate('messages.Let customers pay through offline methods such as bank transfers or manual payments. Use this option when online payment is not available.') }}</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-light rounded-10 p-12 p-xxl-20">
                        <div class="fs-12 text-dark px-3 py-2 rounded bg-warning mb-20" style="--bs-bg-opacity: 0.1;">
                            <div class="d-flex gap-2 ">
                                <span class="text-warning lh-1 fs-14 flex-shrink-0">
                                    <i class="tio-info"></i>
                                </span>
                                <span>
                                    {{ translate('messages.To enable this feature must be activated') }}
                                </span>
                            </div>
                            <ul class="mb-0">
                                <li>
                                    {{ translate('messages.Customer wallet from the') }}
                                    <a href="{{ route('admin.business-settings.business-setup',  ['tab' => 'customer']) }}" class="font-semibold text-info text-underline">{{ translate('messages.Customer Wallet') }}</a>
                                    {{ translate('messages.page.') }}
                                </li>
                                <li>
                                    {{ translate('messages.At least one payment mathod from the previous') }}
                                    <span class="font-semibold">{{ translate('messages.Payment Option') }} </span>
                                    {{ translate('messages.section.') }}
                                </li>
                            </ul>
                        </div>
                        <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-info mb-20" style="--bs-bg-opacity: 0.1;">
                            <span class="text-info lh-1 fs-14 flex-shrink-0">
                                <img src="{{dynamicAsset('assets/admin/img/svg/bulb.svg')}}" class="svg" alt="">
                            </span>
                            <span>
                                {{ translate('messages.To use any payment method for Partial payment you need to active them from Previous Section, otherwise the payment method will remain disable.') }}
                            </span>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-8">
                                <div>
                                    <h4 class="mb-1">{{ translate('messages.Partial Payment') }}</h4>
                                    <p class="fs-12 mb-0">{{ translate('messages.By switching this feature ON, Customer can pay with wallet balance & partially pay from other payment gateways.') }}</p>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                @php($partial_payment = \App\Models\BusinessSetting::where('key', 'partial_payment_status')->first())
                                @php($partial_payment = $partial_payment ? $partial_payment->value : 0)
                                <label
                                    class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0">
                                    <span class="pr-1 d-flex align-items-center switch--label">
                                        {{ translate('messages.Status') }}
                                    </span>
                                    <input type="checkbox"
                                        data-id="partial_payment"
                                        data-type="toggle"
                                        data-image-on="{{ dynamicAsset('assets/admin/img/payment-on.png') }}"
                                        data-image-off="{{ dynamicAsset('assets/admin/img/payment-off.png') }}"
                                        data-title-on="{{ translate('messages.Are you sure turn on') }} <strong>{{ translate('messages.partial_payment_?') }}</strong>"
                                        data-title-off="{{ translate('messages.Are you sure turn off') }} <strong>{{ translate('messages.partial_payment_?') }}</strong>"
                                        data-text-on="<p>{{ translate('messages.Turning on this feature allows your customers to use multiple payment methods for a single order.') }}</p>"
                                        data-text-off="<p>{{ translate('messages.Turning off partial payments prevents customers from applying them to future purchases.') }}</p>"
                                        class="toggle-switch-input dynamic-checkbox-toggle"
                                        value="1"
                                        name="partial_payment_status" id="partial_payment"
                                        {{ $partial_payment == 1 ? 'checked' : '' }}>
                                    <span class="toggle-switch-label text">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="partial-payment-options" id="partial_payment_option" style="display: {{ $partial_payment == 1 ? 'block' : 'none' }};">
                            <div class="mt-20">
                                <label for="" class="input-label d-flex gap-1 pb-3">
                                    {{ translate('messages.Available Option to pay the remaining bill') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="row g-3 border rounded bg-white">
                                    <?php
                                        $partial_payment_method_array = [];
                                        if (isset($partial_payment_method)) {
                                            if (is_string($partial_payment_method)) {
                                                $decoded = json_decode($partial_payment_method, true);
                                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                    $partial_payment_method_array = $decoded;
                                                } else {
                                                    $partial_payment_method_array = [$partial_payment_method];
                                                }
                                            } elseif (is_array($partial_payment_method)) {
                                                $partial_payment_method_array = $partial_payment_method;
                                            }
                                        }
                                    ?>
                                    <div class="col-lg-4">
                                        <div class="custom-checkbox custom-control d-flex gap-2 h-100">
                                            <input type="checkbox" class="custom-control-input" value="cash_on_delivery" name="partial_payment_method[]" id='demo1' {{ in_array('cash_on_delivery', $partial_payment_method_array) ? 'checked' : '' }}>
                                            <label class="custom-control-label mb-0"  for="demo1">
                                                {{ translate('messages.Cash On Delivery') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="custom-checkbox custom-control d-flex gap-2 h-100">
                                            <input type="checkbox" class="custom-control-input" value="digital_payment" name="partial_payment_method[]" id='demo2' {{ in_array('digital_payment', $partial_payment_method_array) ? 'checked' : '' }}>
                                            <label class="custom-control-label mb-0"  for="demo2">
                                                {{ translate('messages.Digital Payment') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="custom-checkbox custom-control d-flex gap-2 h-100">
                                            <input type="checkbox" class="custom-control-input" value="offline_payment" name="partial_payment_method[]" id='demo3' {{ in_array('offline_payment', $partial_payment_method_array) ? 'checked' : '' }}>
                                            <label class="custom-control-label mb-0"  for="demo3">
                                                {{ translate('messages.Offline Payment') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 fs-12 text-dark px-3 py-2 rounded bg-danger mt-20" id="partial-payment-warning" style="--bs-bg-opacity: 0.1; display: none;">
                                <span class="text-danger lh-1 fs-14 flex-shrink-0">
                                    <i class="tio-warning"></i>
                                </span>
                                <span class="warning-text" data-default-text="{{ translate('messages.To use any payment method for Partial payment you need to active them from Previous Section, otherwise the payment method will remain disable.') }}">
                                    {{ translate('messages.To use any payment method for Partial payment you need to active them from Previous Section, otherwise the payment method will remain disable.') }}
                                </span>
                            </div>
                        </div>

                    </div>

                    {{-- Lets a customer settle a cash order through a gateway before it is
                         delivered. Sits in the same card as the other payment sections and
                         reuses the Partial Payment panel's shell, so the page reads as one
                         list of settings rather than a stray card after it. --}}
                    <div class="bg-light rounded-10 p-12 p-xxl-20 mt-20">
                        <div class="row g-3 align-items-center">
                            <div class="col-lg-8">
                                <div>
                                    <h4 class="mb-1">{{ translate('messages.COD to Digital payment') }}</h4>
                                    <p class="fs-12 mb-0">{{ translate('messages.Enable or disable the COD to digital payment conversion feature for customers.') }}</p>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label
                                    class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-0">
                                    <span class="pr-1 d-flex align-items-center switch--label">
                                        {{ translate('messages.Status') }}
                                    </span>
                                    <input type="checkbox"
                                        data-id="cod_to_digital_payment"
                                        data-type="toggle"
                                        data-image-on="{{ dynamicAsset('assets/admin/img/payment-on.png') }}"
                                        data-image-off="{{ dynamicAsset('assets/admin/img/payment-off.png') }}"
                                        data-title-on="{{ translate('messages.Are you sure turn on') }} <strong>{{ translate('messages.COD to Digital payment') }}</strong>"
                                        data-title-off="{{ translate('messages.Are you sure turn off') }} <strong>{{ translate('messages.COD to Digital payment') }}</strong>"
                                        data-text-on="<p>{{ translate('messages.Customers will be able to pay for a cash order through a gateway any time before it is delivered.') }}</p>"
                                        data-text-off="<p>{{ translate('messages.Cash orders will stay cash orders until they are delivered.') }}</p>"
                                        class="toggle-switch-input dynamic-checkbox-toggle"
                                        value="1"
                                        name="cod_to_digital_payment" id="cod_to_digital_payment"
                                        {{ ($cod_to_digital_payment ?? 0) == 1 ? 'checked' : '' }}>
                                    <span class="toggle-switch-label text">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-sticky mt-2">
                <div class="container-fluid">
                <div class="d-flex flex-wrap gap-3 justify-content-center py-3">
                    <button type="reset" id="reset_btn" class="btn btn--secondary min-w-120 location-reload">{{ translate('messages.Reset') }} </button>
                    <button type="{{ getEnvMode() != 'demo' ? 'submit' : 'button' }}" class="btn btn--primary call-demo">
                        <i class="tio-save"></i>
                        {{ translate('Save_Information') }}
                    </button>
                </div>
                </div>
            </div>
        </form>
    </div>
        <!-- Guidline Offcanvas Btn -->
<div class="d-flexgap-2 w-40px gap-2 bg-white position-fixed end-0 translate-middle-y pointer view-guideline-btn flex-column pt-3 px-2 justify-content-center offcanvas-trigger"
    data-toggle="offcanvas" data-target="#offcanvasSetupGuide">
    <span class="arrow bg-primary py-1 px-2 text-white rounded fs-12"><i class="tio-share-vs"></i></span>
    <span class="view-guideline-btn-text text-dark font-semibold pb-2 text-nowrap">
        {{ translate('View_Guideline') }}
    </span>
</div>

<!-- Guidline Offcanvas -->
    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>
    <div class="custom-offcanvas" tabindex="-1" id="offcanvasSetupGuide" aria-labelledby="offcanvasSetupGuideLabel"
        style="--offcanvas-width: 500px">
            <div>
                <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
                    <h3 class="mb-0">{{ translate('messages.Payment Setup Guideline') }}</h3>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;</button>
                </div>
                <div class="custom-offcanvas-body offcanvas-height-100 py-3 px-md-4 px-3">
                    <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                        <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                            <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                                type="button" data-toggle="collapse" data-target="#payment_option_guide"
                                aria-expanded="true">
                                <div
                                    class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                                    <i class="tio-down-ui"></i>
                                </div>
                                <span
                                    class="font-semibold text-left fs-14 text-title">{{ translate('messages.Payment option') }}</span>
                            </button>
                            <a href="#payment_option"
                                class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                        </div>
                        <div class="collapse show mt-3" id="payment_option_guide">
                            <div class="card card-body">
                                <div class="">
                                    <h5 class="mb-3">{{translate('Payment option')}}</h5>
                                    <ul class="mb-0 fs-12">
                                        <li class="font-semibold">
                                            {{ translate('messages.Cash on Delivery') }}
                                        </li>
                                        <p class="mt-2 mb-3">
                                            {{ translate('messages.Cash on Delivery allows customers to pay in cash at the time of order delivery.') }}
                                        </p>
                                        <li class="font-semibold">
                                            {{ translate('messages.Digital Payment') }}
                                        </li>
                                        <p class="mt-2 mb-3">
                                            {{ translate('messages.Digital Payment allows customers to pay online using integrated payment gateways. Supports cards, mobile banking, and digital wallets. Payment is completed before order confirmation.') }}
                                        </p>
                                        <li class="font-semibold">
                                            {{ translate('messages.Offline Payment') }}
                                        </li>
                                        <p class="mt-2 mb-3">
                                            {{ translate('messages.Offline Payment allows customers to place orders using manual payment methods. Supports bank transfer, mobile banking, or other manual methods. Customers must provide a payment reference or proof. Admin or vendor approval may be required before order confirmation.') }}
                                        </p>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="py-3 px-3 bg-light rounded mb-3 mb-sm-20">
                        <div class="d-flex gap-2 align-items-center justify-content-between overflow-hidden">
                            <button class="btn-collapse d-flex gap-2 align-items-center bg-transparent border-0 p-0"
                                type="button" data-toggle="collapse" data-target="#partial_payment_guide" aria-expanded="true">
                                <div
                                    class="btn-collapse-icon w-35px h-35px bg-white d-flex align-items-center justify-content-center border icon-btn rounded-circle fs-12 lh-1">
                                    <i class="tio-down-ui"></i>
                                </div>
                                <span
                                    class="font-semibold text-left fs-14 text-title">{{ translate('messages.Partial Payment') }}</span>
                            </button>
                            <a href="#partial_payment_option"
                                class="text-info text-underline fs-12 text-nowrap offcanvas-close-btn">{{ translate('messages.Let’s Setup') }}</a>
                        </div>
                        <div class="collapse mt-3" id="partial_payment_guide">
                            <div class="card card-body">
                                <div class="">
                                    <h5 class="mb-3">{{translate('Partial Payment')}}</h5>
                                    <p class="fs-12 mb-3">
                                        {{ translate('messages.The Partial Payment feature allows customers to split an order payment into two parts. An initial amount is paid using the customer’s wallet balance, and the remaining amount can be paid using Cash on Delivery (COD), Digital Payment, or Offline Payment.This feature provides greater payment flexibility and helps customers place orders even when their wallet balance is insufficient for the full amount.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




@endsection

@push('script_2')

    <script>
        $(document).on('ready', function () {
            $('#partial_payment').on('change', function () {
                if ($(this).is(':checked')) {
                    $('.partial-payment-options').slideDown();
                } else {
                    $('.partial-payment-options').slideUp();
                }
            });

            $(document).on('click', '.confirm-Toggle', function() {
                let toggle_id = $("#toggle-ok-button").attr("toggle-ok-button");
                if (toggle_id === 'partial_payment') {
                    if ($('#partial_payment').is(':checked')) {
                        $('.partial-payment-options').slideDown();
                    } else {
                        $('.partial-payment-options').slideUp();
                    }
                }
            });

            function checkPaymentMethods() {
                let cod = $('#cash_on_delivery').is(':checked');
                let digital = $('#digital_payment').is(':checked');
                let offline = $('#offline_payment').is(':checked');

                let disabledMethods = [];

                if (!cod) {
                    $('#demo1').prop('disabled', true).prop('checked', false);
                    $("label[for='demo1']").css('font-weight', 'normal');
                    disabledMethods.push("<strong>{{ translate('messages.Cash On Delivery') }}</strong>");
                } else {
                    $('#demo1').prop('disabled', false);
                    $("label[for='demo1']").css('font-weight', '700');
                }

                if (!digital) {
                    $('#demo2').prop('disabled', true).prop('checked', false);
                    $("label[for='demo2']").css('font-weight', 'normal');
                    disabledMethods.push("<strong>{{ translate('messages.Digital Payment') }}</strong>");
                } else {
                    $('#demo2').prop('disabled', false);
                    $("label[for='demo2']").css('font-weight', '700');
                }

                if (!offline) {
                    $('#demo3').prop('disabled', true).prop('checked', false);
                    $("label[for='demo3']").css('font-weight', 'normal');
                    disabledMethods.push("<strong>{{ translate('messages.Offline Payment') }}</strong>");
                } else {
                    $('#demo3').prop('disabled', false);
                    $("label[for='demo3']").css('font-weight', '700');
                }

                if (disabledMethods.length > 0) {
                    let message = disabledMethods.join(' {{ translate('messages.and') }} ') + " {{ translate('messages.option are disable because') }} " + disabledMethods.join(' {{ translate('messages.and') }} ') + " {{ translate('messages.are not active from Previous Section.') }}";
                    $('#partial-payment-warning .warning-text').html(message);
                    $('#partial-payment-warning').show();
                } else {
                    $('#partial-payment-warning .warning-text').text($('#partial-payment-warning .warning-text').data('default-text'));
                    $('#partial-payment-warning').show();
                }

                if (cod || digital || offline) {
                    $('#payment-method-warning').css('text-decoration', 'line-through');
                } else {
                    $('#payment-method-warning').css('text-decoration', 'none');
                }
            }

            $('#cash_on_delivery, #digital_payment, #offline_payment').on('change', function () {
                let cod = $('#cash_on_delivery').is(':checked');
                let digital = $('#digital_payment').is(':checked');
                let offline = $('#offline_payment').is(':checked');

                if (!cod && !digital && !offline) {
                    toastr.error("{{ translate('messages.At least one payment method must be active.') }}");
                    $(this).prop('checked', true);
                    return;
                }
                checkPaymentMethods();
            });

            checkPaymentMethods();
        });

        $(".collapse-div-toggler").on('change', function () {
            if ($(this).val() == '0') {
                $(this).closest('.sorting-card').find('.inner-collapse-div').slideDown();
            } else {
                $(this).closest('.sorting-card').find('.inner-collapse-div').slideUp();
            }
        });

        $(window).on('load', function () {
            $('.collapse-div-toggler').each(function () {
                if ($(this).prop('checked') == true && $(this).val() == '0') {
                    $(this).closest('.sorting-card').find('.inner-collapse-div').show();
                } else if ($(this).prop('checked') == true && $(this).val() == '1') {
                    $(this).closest('.sorting-card').find('.inner-collapse-div').hide();
                }
            });
        })


        $('.offcanvas-close-btn').on('click', function (e) {
            e.preventDefault();
            $('.custom-offcanvas').removeClass('open');
            $('#offcanvasOverlay').removeClass('show');
            $('html, body').animate({
                scrollTop: $($(this).attr('href')).offset().top - 100
            }, 500);
        });
    </script>
@endpush
