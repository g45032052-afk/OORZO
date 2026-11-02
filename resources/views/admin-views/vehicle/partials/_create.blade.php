<form action="{{ route('admin.vehicle.store') }}" method="post" class="d-flex flex-column h-100"
      id="vehicle-offcanvas-form">
    @csrf
    <div>
        <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
            <h3 class="mb-0">{{ translate('messages.Add_New_Vehicle_Type') }}</h3>
            <button type="button"
                    class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                    aria-label="Close">&times;
            </button>
        </div>
        <div class="custom-offcanvas-body p-20">
            <div class="bg--secondary rounded p-20 mb-20">
                @if ($language)
                    <div class="js-nav-scroller hs-nav-scroller-horizontal">
                        <ul class="nav nav-tabs mb-4 border-0">
                            <li class="nav-item">
                                <a class="nav-link lang_link active" href="#"
                                   id="default-link">{{ translate('messages.default') }}</a>
                            </li>
                            @foreach ($language as $lang)
                                <li class="nav-item">
                                    <a class="nav-link lang_link" href="#"
                                       id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-12">
                        @if ($language)
                            <div class="form-group lang_form" id="default-form">
                                <label class="input-label"
                                       for="type_default">{{ translate('messages.Vehicle_type') }}
                                    ({{ translate('messages.default') }})
                                    <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                          data-placement="right"
                                          data-original-title="{{ translate('messages.Required.') }}"> *
                                    </span>
                                </label>
                                <input type="text" name="type[]" id="type_default" class="form-control h--45px"
                                       placeholder="{{ translate('messages.Ex:_Bike') }}" maxlength="254">
                            </div>
                            <input type="hidden" name="lang[]" value="default">

                            @foreach ($language as $lang)
                                <div class="form-group d-none lang_form" id="{{ $lang }}-form">
                                    <label class="input-label"
                                           for="type_{{ $lang }}">{{ translate('messages.Vehicle_type') }}
                                        ({{ strtoupper($lang) }})
                                    </label>
                                    <input type="text" name="type[]" id="type_{{ $lang }}"
                                           class="form-control h--45px"
                                           placeholder="{{ translate('messages.Ex:_Bike') }}" maxlength="254">
                                </div>
                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                            @endforeach
                        @else
                            <div class="form-group">
                                <label class="input-label"
                                       for="type_default">{{ translate('messages.Vehicle_type') }}
                                    <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                          data-placement="right"
                                          data-original-title="{{ translate('messages.Required.') }}"> *
                                    </span>
                                </label>
                                <input type="text" name="type[]" id="type_default" class="form-control h--45px"
                                       placeholder="{{ translate('messages.Ex:_Bike') }}" maxlength="254">
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                        @endif

                        <div class="form-group">
                            <label class="input-label" for="extra_charges">
                                {{ translate('messages.extra_charges') }}
                                ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                      data-original-title="{{ translate('messages.This_amount_will_be_added_with_delivery_charge') }}">
                                    <img src="{{ dynamicAsset('assets/admin/img/info-circle.svg') }}" alt="">
                                </span>
                            </label>
                            <input type="number" name="extra_charges" id="extra_charges" class="form-control h--45px"
                                   step="0.001" min="0" placeholder="{{ translate('messages.Ex:_10') }}" required>
                        </div>

                        <div class="form-group">
                            <label class="input-label" for="starting_coverage_area">
                                {{ translate('messages.starting_coverage_area') }} ({{ \App\CentralLogics\Helpers::distanceUnitLabel() }})
                                <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                      data-original-title="{{ translate('messages.This_value_is_the_minimum_distance_for_a_vehicle_in_this_category_to_serve_an_order.') }}">
                                    <img src="{{ dynamicAsset('assets/admin/img/info-circle.svg') }}" alt="">
                                </span>
                            </label>
                            <input type="number" name="starting_coverage_area" id="starting_coverage_area"
                                   class="form-control h--45px" step="0.001" min="0"
                                   placeholder="{{ translate('messages.Ex:_0') }}" required>
                        </div>

                        <div class="form-group mb-0">
                            <label class="input-label" for="maximum_coverage_area">
                                {{ translate('messages.maximum_coverage_area') }} ({{ \App\CentralLogics\Helpers::distanceUnitLabel() }})
                                <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                      data-original-title="{{ translate('messages.This_value_is_the_maximum_distance_for_a_vehicle_in_this_category_to_serve_an_order.') }}">
                                    <img src="{{ dynamicAsset('assets/admin/img/info-circle.svg') }}" alt="">
                                </span>
                            </label>
                            <input type="number" name="maximum_coverage_area" id="maximum_coverage_area"
                                   class="form-control h--45px" step="0.001" min="0"
                                   placeholder="{{ translate('messages.Ex:_10') }}" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center mt-auto offcanvas-footer p-3 position-sticky">
        <button type="button"
                class="btn w-100 btn--secondary offcanvas-close h--40px">{{ translate('messages.Cancel') }}</button>
        <button type="submit" class="btn w-100 btn--primary h--40px">{{ translate('messages.Add') }}</button>
    </div>
</form>
