{{-- One drawer serves both joining and reworking a rejected selection; the title and the
     submit label are swapped in by openJoinDrawer(). --}}
<div id="offcanvas__join_offer" class="custom-offcanvas d-flex flex-column justify-content-between"
     style="--offcanvas-width: 620px">
    <div class="d-flex flex-column" style="height:100%">
        <div class="custom-offcanvas-header d-flex justify-content-between align-items-center px-3 py-3">
            <h2 class="mb-0 fs-18 font-bold" id="join_drawer_title">{{ translate('messages.Add_Food') }}</h2>
            <button type="button"
                    class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                    aria-label="Close">&times;</button>
        </div>

        <div class="custom-offcanvas-body p-20 flex-grow-1">
            <h5 class="mb-3">{{ translate('messages.Select_Foods_for_BOGO_offer') }}</h5>

            @foreach(['buy', 'get'] as $type)
                <div class="bg-light rounded-8 p-3 mb-3">
                    <h6 class="mb-1">
                        {{ $type === 'buy' ? translate('messages.Buy_Food') : translate('messages.Get_Food') }}
                    </h6>
                    <p class="font-size-sm opacity-75">
                        {{ $type === 'buy'
                            ? translate('messages.Customers_must_buy_the_selected_Foods_with_the_specific_quantities_to_qualify')
                            : translate('messages.Customers_must_buy_all_selected_Foods_in_the_specified_quantities_to_qualify') }}
                    </p>

                    {{-- The inline contrast fix this used to carry now lives in .alert--note,
                         which the vendor layout loads from the same style.css. --}}
                    <div class="alert alert-soft-warning alert--note d-flex align-items-start gap-2 py-2">
                        <i class="tio-error lh-base"></i>
                        <span>
                            {{ translate('messages.You_must_add') }}
                            <strong class="{{ $type }}-required">0</strong>
                            {{ $type === 'buy'
                                ? translate('messages.foods_as_the_buy_quantity_for_this_BOGO_offer')
                                : translate('messages.foods_as_the_get_quantity_for_this_BOGO_offer') }}
                            <span class="{{ $type }}-total-wrap d-none">
                                (<strong class="{{ $type }}-total">0</strong>/<span class="{{ $type }}-required">0</span>)
                            </span>
                        </span>
                    </div>

                    <div class="form-group mb-2">
                        <label class="input-label">
                            {{ translate('messages.Select_Food') }} <span class="text-danger">*</span>
                            <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                  data-original-title="{{ translate('messages.Search_and_pick_a_food_from_your_menu') }}">
                                <i class="tio-info text-gray1 fs-16"></i>
                            </span>
                        </label>
                        <select class="form-control h--45px food-picker" data-type="{{ $type }}" disabled>
                            <option value="">{{ translate('messages.Select_Food') }}</option>
                        </select>
                    </div>

                    <div class="selected-items" data-type="{{ $type }}"></div>
                </div>
            @endforeach
        </div>

        <div class="offcanvas-footer bg-white p-3 d-flex align-items-center gap-3">
            <button type="button" class="btn btn--reset h--45px flex-fill" id="join_reset">
                {{ translate('messages.Reset') }}
            </button>
            <button type="button" class="btn btn--primary h--45px flex-fill" id="join_submit">
                {{ translate('messages.Join') }}
            </button>
        </div>
    </div>
</div>
