{{-- Turning a setup ON: the zone stops charging for delivery on the terms the
     setup states, and the admin carries the cost. --}}
<div class="modal fade" id="free-delivery-turn-on-modal">
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
                            {{ translate('messages.Do_You_Want_To_Turn_On_This_Free_Delivery?') }}</h5>
                    </div>
                    <div class="text-center">
                        <p>{{ translate('messages.If_you_turn_on_this_setup_the_orders_of_this_zone_will_get_free_delivery_and_the_cost_will_be_borne_by_admin.') }}
                        </p>
                    </div>
                    <div class="btn--container justify-content-center">
                        <button type="button" class="btn btn--reset min-w-120px"
                            data-dismiss="modal">{{ translate('messages.Cancel') }}</button>
                        <button type="button" id="free-delivery-turn-on-confirm"
                            class="btn btn--primary min-w-120px">{{ translate('messages.Turn_On') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Turning a setup OFF: free delivery is a giveaway, so the zone simply goes
     back to charging its usual delivery charge. --}}
<div class="modal fade" id="free-delivery-turn-off-modal">
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
                            {{ translate('messages.Do_You_Want_To_Turn_Off_This_Free_Delivery?') }}</h5>
                    </div>
                    <div class="text-center">
                        <p>{{ translate('messages.If_you_turn_off_this_setup_the_customers_of_this_zone_will_pay_the_usual_delivery_charge_again.') }}
                        </p>
                    </div>
                    <div class="btn--container justify-content-center">
                        <button type="button" class="btn btn--reset min-w-120px"
                            data-dismiss="modal">{{ translate('messages.Cancel') }}</button>
                        <button type="button" id="free-delivery-turn-off-confirm"
                            class="btn btn--primary min-w-120px">{{ translate('messages.Turn_Off') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
