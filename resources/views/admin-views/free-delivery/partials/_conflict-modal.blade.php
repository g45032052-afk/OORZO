{{-- Shown when the server rejects the save with a 409 because the chosen zone
     already has a free delivery setup. Mirrors the surge overlap modal. --}}
<div class="modal fade" id="free-delivery-exists-modal">
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
                        <h5 class="modal-title mb-3">{{ translate('messages.Free_Delivery_Setup_Already_Exists!') }}</h5>
                    </div>
                    <div class="text-center">
                        <p>{{ translate('messages.This_zone_already_has_a_free_delivery_setup._Choose_another_zone,_or_edit_the_existing_one_from_the_list.') }}
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
