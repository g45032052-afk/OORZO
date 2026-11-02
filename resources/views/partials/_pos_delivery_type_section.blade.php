<div class="bg-light rounded p-2 mb-10px d-none" id="delivery_type_section">
    <h6 class="font-semibold mb-2 fs-14">{{ translate('messages.delivery_type') }}</h6>
    <div class="bg-white rounded-10 d-flex flex-column gap-3 p-lg-3 p-2 mb-10px" id="delivery_type_options">
    </div>
    <div class="gap-2 fs-12 text-dark px-3 py-2 rounded bg-warning d-none" id="delivery_type_note" style="--bs-bg-opacity: 0.1;">
        <span class="text-warning lh-1 fs-14 flex-shrink-0">
            <i class="tio-info"></i>
        </span>
        <span>
            {{ translate('messages.Free delivery applies to this order amount, so delivery type charge options are disabled.') }}
        </span>
    </div>
</div>
