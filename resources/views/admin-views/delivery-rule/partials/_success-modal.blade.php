<div class="modal fade" id="rule-success-modal" data-backdrop="static">
    <div class="modal-dialog status-warning-modal">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true" class="tio-clear"></span>
                </button>
            </div>
            <div class="modal-body pb-5 pt-0">
                <div class="max-349 mx-auto mb-20 text-center">
                    <span class="rule-success-mark"><i class="tio-done"></i></span>
                    <h5 class="modal-title mb-3" id="rule-success-title">
                        @if ($isUpdate ?? false)
                            {{ translate('messages.Delivery_Rule_Updated_Successfully') }}
                        @else
                            {{ translate('messages.Delivery_Rule_Created_Successfully') }}
                        @endif
                    </h5>
                    <p>
                        @if ($isUpdate ?? false)
                            {{ translate('messages.Your_delivery_rule_has_been_updated_successfully_and_is_now_available_for_delivery_charge_calculations.') }}
                        @else
                            {{ translate('messages.Your_delivery_rule_has_been_created_successfully_and_is_now_available_for_delivery_charge_calculations.') }}
                        @endif
                    </p>
                    <div class="btn--container justify-content-center">
                        <a href="javascript:" id="rule-success-view"
                            class="btn btn--primary min-w-120px">{{ translate('messages.View_Details') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
