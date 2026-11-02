{{--
    Why an approved bundle is not reaching customers, spelled out on demand.

    The reasons started life in a hover tooltip on the row's warning icon, which was the wrong
    container for them: several sentences, some naming a food and a time window, none of them
    readable while the pointer had to stay still. They are shown the way every other blocking
    condition in the panel is shown - the surge price overlap modal - so the admin and the
    vendor read the same layout they already know.

    One modal per page, filled from whichever warning icon was clicked; the reasons ride on
    the icon itself, so opening this costs no request.

    The copy names the promotion rather than calling it "the bundle", so the same strings read
    correctly whichever list the vendor opened them from.
--}}
{{-- Named by whichever list included it, so the copy reads correctly on both. --}}
@php($promotion = ['promotion' => $promotionLabel ?? translate('messages.BOGO_offer')])
@include('partials.promotion._warning_styles')

<div class="modal fade" id="promo-visibility-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered status-warning-modal promo-visibility-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true" class="tio-clear"></span>
                </button>
            </div>
            <div class="modal-body pb-5 pt-0 px-4">
                {{-- The heading is kept narrow and centred, the way every other warning modal
                     in the panel reads. The reasons are not: they are full sentences carrying
                     a food name and often a time window, and at 349px they wrapped three deep
                     and read as one paragraph. --}}
                <div class="max-349 mx-auto text-center">
                    <img class="mb-20" src="{{ dynamicAsset('assets/admin/img/modal-error.png') }}" alt="">
                    <h5 class="modal-title mb-2">{{ translate('messages.Offer_Not_Visible_to_Customers') }}</h5>
                    <p class="mb-20">{{ translate('messages.This_promotion_is_approved_but_customers_can_not_see_or_order_it_right_now', $promotion) }}</p>
                </div>

                <div class="promo-reasons mb-20">
                    <span class="promo-reasons__label">{{ translate('messages.Reasons_the_promotion_is_on_hold', $promotion) }}</span>
                    <ul class="promo-reasons__list" id="promo_visibility_reasons"></ul>
                </div>

                <p class="promo-reasons__footnote text-center mb-20">
                    {{ translate('messages.The_promotion_returns_to_the_customer_app_automatically_once_these_are_resolved', $promotion) }}
                </p>

                <div class="btn--container justify-content-center">
                    <button type="button" class="btn btn--primary min-w-120px"
                            data-dismiss="modal">{{ translate('messages.Okay') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script_2')
    <script>
        "use strict";

        // Delegated: the admin table is rebuilt by pagination and the vendor list by search,
        // so binding to the icons themselves would go stale on the first page change.
        $(document).on('click', '.promo-visibility-warning', function () {
            const reasons = $(this).data('reasons') || [];
            const $list = $('#promo_visibility_reasons').empty();

            // .text() rather than .html(): a reason names foods and variations the restaurant
            // typed itself, and nothing here is meant to render as markup.
            reasons.forEach(function (reason) {
                $list.append($('<li>').text(reason));
            });

            $('#promo-visibility-modal').modal('show');
        });
    </script>
@endpush
