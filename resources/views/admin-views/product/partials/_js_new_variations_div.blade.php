`
<div class="__bg-F8F9FC-card view_new_option mb-2">
    <div>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <label class="form-check form--check">
                <input id="options[` + count + `][required]" name="options[` + count + `][required]"
                    class="form-check-input" type="checkbox">
                <span class="form-check-label">{{ translate('Required') }}</span>
            </label>
            <div>
                <button type="button" class="btn btn-danger btn-sm delete_input_button"
                    title="{{ translate('Delete') }}">
                    <i class="tio-add-to-trash"></i>
                </button>
            </div>
        </div>
        <div class="row g-2">
            <div class="col-xl-4 col-lg-6">
                <label for="">{{ translate('name') }}&nbsp;<span class="form-label-secondary text-danger"
                        data-toggle="tooltip" data-placement="right"
                        data-original-title="{{ translate('messages.Required.') }}"> *
                    </span></label>
                <input required name=options[` + count + `][name] class="form-control new_option_name" type="text"
                    data-count="` + count + `">
            </div>

            <div class="col-xl-4 col-lg-6">
                <div>
                    <label class="input-label text-capitalize d-flex align-items-center"><span
                            class="line--limit-1">{{ translate('messages.Variation_Selection_Type') }} </span>
                    </label>
                    <div class="resturant-type-group px-0">
                        <label class="form-check form--check mr-2 mr-md-4">
                            <input class="form-check-input show_min_max" data-count="` + count + `" type="radio"
                                value="multi" name="options[` + count + `][type]" id="type` + count + `" checked>
                            <span class="form-check-label">
                                {{ translate('Multiple Selection') }}
                            </span>
                        </label>

                        <label class="form-check form--check mr-2 mr-md-4">
                            <input class="form-check-input hide_min_max" data-count="` + count + `" type="radio"
                                value="single" name="options[` + count + `][type]" id="type` + count + `">
                            <span class="form-check-label">
                                {{ translate('Single Selection') }}
                            </span>
                        </label>
                    </div>
                </div>
            </div>
            <div id="min_max_div_` + count + `" class="col-xl-4 col-lg-6">
                <div class="row g-2">
                    <div class="col-6">
                        <label for="">{{ translate('Min') }}</label>
                        <input id="min_max1_` + count + `" required name="options[` + count + `][min]"
                            class="form-control" type="number" min="1">
                    </div>
                    <div class="col-6">
                        <label for="">{{ translate('Max') }}</label>
                        <input id="min_max2_` + count + `" required name="options[` + count + `][max]"
                            class="form-control" type="number" min="1">
                    </div>
                </div>
            </div>
        </div>

        <div id="option_price_` + count + `">
            <div class="bg-white border rounded p-3 pb-0 mt-3">
                <div id="option_price_view_` + count + `">
                    <div class="row g-3 add_new_view_row_class mb-3">
                        <div class="col-md-3 col-sm-6">
                            <label for="">{{ translate('Option_name') }} &nbsp;<span
                                    class="form-label-secondary text-danger" data-toggle="tooltip"
                                    data-placement="right" data-original-title="{{ translate('messages.Required.') }}">
                                    *
                                </span></label>
                            <input class="form-control" required type="text"
                                name="options[` + count + `][values][0][label]" id="">
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label for="">{{ translate('Additional_price') }}
                                ({{ \App\CentralLogics\Helpers::currency_symbol() }})&nbsp;<span
                                    class="form-label-secondary text-danger" data-toggle="tooltip"
                                    data-placement="right" data-original-title="{{ translate('messages.Required.') }}">
                                    *
                                </span></label>
                            <input class="form-control" required type="number" min="0" step="0.01"
                                name="options[` + count + `][values][0][optionPrice]" id="">
                        </div>
                        <div class="col-md-3 col-sm-6 hide_this">
                            <label for="">{{ translate('Stock') }}</label>
                            <input class="form-control stock_disable count_stock" required type="number" min="0"
                                max="9999999" name="options[` + count + `][values][0][total_stock]" id="">
                        </div>
                    </div>
                </div>
                <div class="row mt-3 p-3 mr-1 d-flex " id="add_new_button_` + count + `">
                    <button type="button" class="btn btn--primary btn-outline-primary add_new_row_button"
                        data-count="` + count + `">{{ translate('Add_New_Option') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

`
