<div class="modal fade" id="quick_view_faq" tabindex="-1" role="dialog" aria-labelledby="quickViewLabel" aria-hidden="true">
    <div class="modal-dialog max-w-600px mx-auto modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header mb-20">
                <h3 class="mb-0 fs-20 text-center text-capitalize">{{ translate('messages.Quick_View') }}</h3>
                <button type="button" class="close bg-light w-40px h-40px rounded-circle fs-20" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pb-4 px-4 pt-0">
                <div class="bg-light2 p-xl-20 p-3 rounded">
                    <div class="mb-3">
                        <div class="fs-14 fw-semibold font-weight-bold text-dark mb-1 text-capitalize">
                            {{ translate('messages.Question') }}
                        </div>
                        <p class="fs-14 fw-400 mb-0" id="pro-faq-view-question"></p>
                    </div>
                    <hr class="my-3">
                    <div>
                        <div class="fs-14 fw-semibold font-weight-bold text-dark mb-1 text-capitalize">
                            {{ translate('messages.Answer') }}
                        </div>
                        <p class="fs-14 fw-400 mb-0" id="pro-faq-view-answer"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
