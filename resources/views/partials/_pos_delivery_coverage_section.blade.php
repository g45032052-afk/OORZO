{{--
    The area or ZIP code the delivery charge is read from, when the zone's active
    delivery rule prices coverage-wise rather than by distance.

    Without a pick, Helpers::deliveryRuleBaseCharge() has nothing to look the charge
    up by and falls through to the rule's minimum — so every counter order in such a
    zone was billed the same floor whatever address it was going to. The customer API
    refuses the order outright in that state (coverage_selection_check), and this is
    the counter's equivalent of the picker the app shows.

    Hidden until the zone is known to price this way; the JS fills and reveals it.
--}}
<div class="bg-light rounded p-2 mb-10px d-none" id="delivery_coverage_section">
    <h6 class="font-semibold mb-2 fs-14" id="delivery_coverage_label">{{ translate('messages.delivery_area') }}</h6>
    <select class="form-control" id="delivery_coverage_select">
        <option value="">{{ translate('messages.select_an_option') }}</option>
    </select>
    <div class="gap-2 fs-12 text-dark px-3 py-2 mt-2 rounded bg-warning d-none" id="delivery_coverage_note"
         style="--bs-bg-opacity: 0.1;">
        <span class="text-warning lh-1 fs-14 flex-shrink-0"><i class="tio-info"></i></span>
        <span id="delivery_coverage_note_text"></span>
    </div>
</div>
