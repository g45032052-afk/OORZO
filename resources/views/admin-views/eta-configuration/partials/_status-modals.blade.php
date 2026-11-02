{{-- Turning a configuration ON: the zone estimates from one configuration, so the
     one it is estimating from now is switched off automatically. --}}
<div class="modal fade" id="eta-configuration-turn-on-modal">
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
                            {{ translate('messages.Do_You_Want_To_Turn_On_This_ETA_Configuration?') }}</h5>
                    </div>
                    <div class="text-center">
                        <p>{{ translate('messages.If_you_turn_on_this_configuration_the_current_ETA_configuration_of_this_zone_will_be_turned_off_automatically_&_the_delivery_time_will_be_estimated_from_the_new_one.') }}
                        </p>
                    </div>
                    <div class="btn--container justify-content-center">
                        <button type="button" class="btn btn--reset min-w-120px"
                            data-dismiss="modal">{{ translate('messages.Cancel') }}</button>
                        <button type="button" id="eta-configuration-turn-on-confirm"
                            class="btn btn--primary min-w-120px">{{ translate('messages.Turn_On') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Turning a configuration OFF: a zone must keep one configuration active or it
     has no delivery time to quote, so the admin names which one takes over. --}}
<div class="modal fade" id="eta-configuration-turn-off-modal">
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
                            {{ translate('messages.Do_You_Want_To_Turn_Off_This_ETA_Configuration?') }}</h5>
                    </div>
                    <div class="text-center">
                        <p>{{ translate('messages.If_you_turn_off_this_configuration_you_must_choose_another_ETA_configuration_for_this_zone._Please_select_another_one_from_bellow.') }}
                        </p>
                    </div>

                    {{-- Only the label is left aligned, because a label belongs over its
                         field. Everything the modal says to the admin is centred, the
                         note below included - it reads as a sentence, not as a field. --}}
                    <div class="form-group text-left">
                        <label class="input-label"
                            for="eta_replacement_id">{{ translate('messages.Select_ETA_Configuration') }}</label>
                        <select id="eta_replacement_id" class="form-control h--45px"></select>
                    </div>

                    <p class="d-none text-danger fs-12 text-center" id="eta-replacement-empty">
                        {{ translate('messages.This_zone_has_no_other_ETA_configuration_to_switch_to._Create_one_first.') }}
                    </p>

                    <div class="btn--container justify-content-center">
                        <button type="button" class="btn btn--reset min-w-120px"
                            data-dismiss="modal">{{ translate('messages.Cancel') }}</button>
                        <button type="button" id="eta-configuration-turn-off-confirm"
                            class="btn btn--primary min-w-120px">{{ translate('messages.Update') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
