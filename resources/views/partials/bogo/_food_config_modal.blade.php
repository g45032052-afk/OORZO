{{-- Opened when a food is picked in the Add Restaurant drawer, and again from a card's
     Edit action. Body is rendered client side from the restaurant-foods payload. --}}
<div class="modal fade" id="foodConfigModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 align-items-start">
                <div class="d-flex align-items-start gap-3">
                    <div class="position-relative flex-shrink-0">
                        <img id="fc_image" class="rounded onerror-image" style="width:90px;height:90px;object-fit:cover"
                             data-onerror-image="{{dynamicAsset('assets/admin/img/100x100/food-default-image.png')}}"
                             src="#" alt="food">
                        <span id="fc_veg_badge" class="badge position-absolute" style="top:6px;left:6px"></span>
                    </div>
                    <div>
                        <h4 id="fc_name" class="mb-2 font-bold"></h4>
                        <div class="d-flex align-items-baseline gap-2 mb-2">
                            <del id="fc_old_price" class="fs-16 opacity-75"></del>
                            <span id="fc_price" class="fs-24 font-bold"></span>
                        </div>
                        <span id="fc_discount" class="d-block fs-14 opacity-75"></span>
                    </div>
                </div>
                <button type="button" class="btn-close w-30px h-30px border rounded-circle d-center bg--secondary p-0"
                        data-dismiss="modal" aria-label="Close">&times;</button>
            </div>

            <div class="modal-body">
                <div id="fc_description_wrap" class="mb-4">
                    <strong class="d-block mb-2">{{ translate('messages.Description') }} :</strong>
                    <p class="mb-0">
                        <span id="fc_description_short"></span>
                        <span id="fc_description_full" class="d-none"></span>
                        <a href="javascript:" id="fc_description_toggle" class="d-none"
                           style="text-decoration:underline">{{ translate('messages.See_More') }}</a>
                    </p>
                </div>

                {{-- One panel per variation group, honouring its own required/min/max rule. --}}
                <div id="fc_variations"></div>

                {{-- No food quantity here by design: the same food is added again rather than
                     given a count, so each add is one line. Add-ons do carry their own count,
                     which the stepper inside a selected card sets. --}}
                <div class="bg-global-gray rounded-8 p-3 mb-0" id="fc_addon_wrap">
                    <strong class="d-block mb-2">{{ translate('messages.Addon') }}</strong>
                    <div id="fc_addons" class="d-flex flex-nowrap gap-2 pb-1" style="overflow-x:auto"></div>
                </div>
            </div>

            <div class="modal-footer border-top d-flex align-items-center justify-content-between">
                <div>
                    <span class="fs-16 opacity-75">{{ translate('messages.Total_Price') }} : </span>
                    <span id="fc_total" class="fs-24 font-bold"></span>
                </div>
                <button type="button" class="btn btn--primary min-w-120" id="fc_submit">
                    {{ translate('messages.Add_This_Food') }}
                </button>
            </div>
        </div>
    </div>
</div>
