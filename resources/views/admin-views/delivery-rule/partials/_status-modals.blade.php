{{-- Turning a rule ON: the zone's current rule is displaced automatically, so
     this only needs confirming. --}}
<div class="modal fade" id="rule-turn-on-modal">
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
                        <h5 class="modal-title mb-3">{{ translate('messages.Do_You_Want_To_Turn_On_The_Rule?') }}</h5>
                    </div>
                    <div class="text-center">
                        <p>{{ translate('messages.If_you_turn_on_this_rule_current_charge_rule_will_be_turned_off_automatically_&_all_charge_will_calculate_based_on_new_rule.') }}
                        </p>
                    </div>
                    <div class="btn--container justify-content-center">
                        <button type="button" class="btn btn--reset min-w-120px"
                            data-dismiss="modal">{{ translate('messages.Cancel') }}</button>
                        <button type="button" id="rule-turn-on-confirm"
                            class="btn btn--primary min-w-120px">{{ translate('messages.Turn_On') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Turning a rule OFF: a zone must keep exactly one active rule, so the admin
     has to name the replacement before this can proceed. --}}
<div class="modal fade" id="rule-turn-off-modal">
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
                        <h5 class="modal-title mb-3">{{ translate('messages.Do_You_Want_To_Turn_Off_The_Rule?') }}</h5>
                    </div>
                    <div class="text-center">
                        <p>{{ translate('messages.If_you_turn_off_this_rule_you_must_choose_another_rule_for_this_zone._Please_select_another_rule_from_bellow.') }}
                        </p>
                    </div>

                    <div class="form-group text-left">
                        <label class="input-label" for="replacement_id">{{ translate('messages.Select_Rule') }}</label>
                        <select id="replacement_id" class="form-control h--45px"></select>
                        <span class="d-none text-danger fs-12 mt-1" id="replacement-empty">
                            {{ translate('messages.This_zone_has_no_other_rule_to_switch_to._Create_one_first.') }}
                        </span>
                    </div>

                    <div class="btn--container justify-content-center">
                        <button type="button" class="btn btn--reset min-w-120px"
                            data-dismiss="modal">{{ translate('messages.Cancel') }}</button>
                        <button type="button" id="rule-turn-off-confirm"
                            class="btn btn--primary min-w-120px">{{ translate('messages.Update') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
