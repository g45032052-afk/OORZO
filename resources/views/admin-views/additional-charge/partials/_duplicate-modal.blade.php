{{-- A zone holds one setup, so create refuses a zone that already has one rather than
     replacing it. Raised on the 409 from store(); the same shape as the surge price
     overlap modal, because it is the same kind of answer. --}}
<div class="modal fade" id="charge-duplicate-modal">
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
                        <img class="mb-20" src="{{ dynamicAsset('assets/admin/img/modal-error.png') }}" alt="">
                        <h5 class="modal-title mb-3">{{ translate('messages.Additional_Charge_Setup_Already_Exists!') }}</h5>
                    </div>
                    <div class="text-center">
                        <p>{{ translate('messages.This_zone_already_has_an_additional_delivery_charge_setup._Edit_the_existing_one_from_the_list_to_change_its_amounts.') }}
                        </p>
                    </div>
                    <div class="btn--container justify-content-center">
                        <button type="button" class="btn btn--primary min-w-120px"
                            data-dismiss="modal">{{ translate('messages.Okay') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
