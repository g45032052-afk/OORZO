{{-- Turning a setup ON: the zone starts offering express and slightly delayed
     delivery beside the standard one. --}}
<div class="modal fade" id="charge-turn-on-modal">
    <div class="modal-dialog status-warning-modal">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true" class="tio-clear"></span>
                </button>
            </div>
            <div class="modal-body pb-5 pt-0">
                <div class="max-349 mx-auto mb-20">
                    <div class="text-center">
                        <img class="mb-20" src="{{ dynamicAsset('assets/admin/img/price-emty.png') }}" alt="">
                        <h5 class="modal-title mb-3">
                            {{ translate('messages.Do_You_Want_To_Turn_On_This_Additional_Charge?') }}</h5>
                    </div>
                    <div class="text-center">
                        <p>{{ translate('messages.If_you_turn_on_this_setup_customers_of_this_zone_can_choose_express_or_slightly_delayed_delivery_at_checkout.') }}
                        </p>
                    </div>
                    <div class="btn--container justify-content-center">
                        <button type="button" class="btn btn--reset min-w-120px"
                            data-dismiss="modal">{{ translate('messages.Cancel') }}</button>
                        <button type="button" id="charge-turn-on-confirm"
                            class="btn btn--primary min-w-120px">{{ translate('messages.Turn_On') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Turning a setup OFF: an additional charge is optional, so the zone simply
     stops offering express and delayed delivery. --}}
<div class="modal fade" id="charge-turn-off-modal">
    <div class="modal-dialog status-warning-modal">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true" class="tio-clear"></span>
                </button>
            </div>
            <div class="modal-body pb-5 pt-0">
                <div class="max-349 mx-auto mb-20">
                    <div class="text-center">
                        <img class="mb-20" src="{{ dynamicAsset('assets/admin/img/price-emty.png') }}" alt="">
                        <h5 class="modal-title mb-3">
                            {{ translate('messages.Do_You_Want_To_Turn_Off_This_Additional_Charge?') }}</h5>
                    </div>
                    <div class="text-center">
                        <p>{{ translate('messages.If_you_turn_off_this_setup_customers_of_this_zone_will_no_longer_get_express_or_slightly_delayed_delivery_options.') }}
                        </p>
                    </div>
                    <div class="btn--container justify-content-center">
                        <button type="button" class="btn btn--reset min-w-120px"
                            data-dismiss="modal">{{ translate('messages.Cancel') }}</button>
                        <button type="button" id="charge-turn-off-confirm"
                            class="btn btn--primary min-w-120px">{{ translate('messages.Turn_Off') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
