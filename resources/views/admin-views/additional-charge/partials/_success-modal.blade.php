<div class="modal fade" id="charge-success-modal" data-backdrop="static">
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
                    <h5 class="modal-title mb-3">
                        {{ translate('messages.Additional_Charge_Saved_Successfully') }}</h5>
                    <p>{{ translate('messages.Your_additional_delivery_charge_has_been_saved_successfully_and_is_now_available_for_delivery_charge_calculations.') }}
                    </p>
                    <div class="btn--container justify-content-center">
                        <a href="javascript:" id="charge-success-view"
                            class="btn btn--primary min-w-120px">{{ translate('messages.View_List') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
