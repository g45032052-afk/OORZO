<div class="modal-body pt-2">
    <div class="coupon-details-inner d-flex align-items-center gap-24px">
        <div class="campaign-cont-box">
            <div class="mb-4 pb-xxl-3">

                @if ($coupon->coupon_type == 'free_delivery')
                <h5 class="mb-2 text-dark">{{ translate('Free Delivery') }}</h5>
                @else

                @if ($coupon->discount_type == 'amount')
                <h5 class="mb-2 text-dark">{{  \App\CentralLogics\Helpers::format_currency($coupon->discount)  }}  {{ translate('Flat discount on Purchase') }}</h5>
                @else
                <h5 class="mb-2 text-dark">{{ $coupon->discount }} {{ translate('% discount on Purchase') }}</h5>
                @endif


                @endif
                <h5 class="mb-2 text-dark">{{ translate('Code') }} : {{ $coupon->code }}</h5>

                @if ($coupon->coupon_type == 'free_delivery')
                    <p class="mb-0">{{ translate('Free Delivery') }}</p>
                    @else
                    <p class="mb-0">{{ translate('Discount On Purchase') }}</p>

                @endif
            </div>
            <div class="d-flex flex-column gap-2">
                <div class="d-flex gap-3">
                    <span class="fs-13 min-w-135px">{{ translate('Minimum Purchase') }}</span>
                    <span>:</span>
                    <div>
                        <span class="text-title fw-500">{{ \App\CentralLogics\Helpers::format_currency($coupon->min_purchase) }}</span>
                    </div>
                </div>
                @if ($coupon->coupon_type != 'free_delivery' && $coupon->discount_type != 'amount')
                <div class="d-flex gap-3">
                    <span class="fs-13 min-w-135px">{{ translate('Maximum Discount') }}</span>
                    <span>:</span>
                    <div>
                        <span class="text-title fw-500">{{ \App\CentralLogics\Helpers::format_currency($coupon->max_discount) }}</span>
                    </div>
                </div>

                @endif
                <div class="d-flex gap-3">
                    <span class="fs-13 min-w-135px">{{ translate('Start Date') }}</span>
                    <span>:</span>
                    <div>
                        <span class="text-title fw-500">{{ \App\CentralLogics\Helpers::date_format($coupon->start_date) }}</span>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <span class="fs-13 min-w-135px">{{ translate('End Date') }}</span>
                    <span>:</span>
                    <div>
                        <span class="text-title fw-500">{{ \App\CentralLogics\Helpers::date_format($coupon->expire_date) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="discount-off d-flex align-items-center justify-content-center bg-white">
            <div>
                @if ($coupon->coupon_type == 'free_delivery')
                <h2 class="text-title text-center m-0 fs-24">{{ translate('Free Delivery') }}
                </h2>
                @else
                <h2 class="text-title text-center m-0 fs-24">{{ $coupon->discount_type == 'amount' ? \App\CentralLogics\Helpers::format_currency($coupon->discount) : $coupon->discount .'%' }} <br>
                    <small class="text-muted">{{ translate('Off') }}</small>
                </h2>

                @endif
            </div>
        </div>
    </div>
</div>
