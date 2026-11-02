@extends('layouts.admin.app')

@section('title',translate('BOGO_Offer_Details'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.bogo._food_picker_styles')
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h1 class="page-header-title mb-0">{{translate('messages.BOGO_Offer')}} #{{$offer->id}}</h1>
            <div class="d-flex align-items-center gap-3">
                {{-- What the delete actually costs, said before it happens: the offer is taken
                     off every restaurant on it and the bundle disappears from any cart holding
                     it. A plain "delete this offer?" made that look like deleting a draft. --}}
                @php($joined = (int) ($offer->joined_count ?? 0))
                <a class="btn btn-soft-danger d-flex align-items-center gap-1 form-alert" href="javascript:"
                   data-id="bogo-offer-{{$offer->id}}"
                   data-message="{{ $joined > 0
                        ? $joined.' '.translate('messages.restaurants_have_joined_this_offer_Deleting_it_removes_the_offer_from_all_of_them')
                            .' '.translate('messages.Any_items_customers_added_to_their_cart_from_this_offer_will_be_removed_too')
                            .' '.translate('messages.Want_to_delete_this_BOGO_offer')
                        : translate('messages.Want_to_delete_this_BOGO_offer') }}">
                    <i class="tio-delete"></i> {{translate('messages.Delete')}}
                </a>
                <form action="{{route('admin.bogo-offer.delete',$offer->id)}}" method="post" id="bogo-offer-{{$offer->id}}">
                    @csrf @method('delete')
                </form>

                {{-- Status sits in its own bordered pill, per design. --}}
                <div class="d-flex align-items-center gap-2 border rounded bg-white px-3 py-2">
                    <span class="font-weight-bold">{{translate('messages.Status')}}</span>
                    <label class="toggle-switch toggle-switch-sm mb-0" for="bogoStatus{{$offer->id}}">
                        <input type="checkbox" class="toggle-switch-input status_change_alert"
                               data-url="{{route('admin.bogo-offer.status',[$offer->id, $offer->status ? 0 : 1])}}"
                               data-message="{{ $offer->status
                                    ? translate('messages.Want_to_turn_off_this_BOGO_offer')
                                    : translate('messages.Want_to_turn_on_this_BOGO_offer') }}"
                               id="bogoStatus{{$offer->id}}" {{$offer->status ? 'checked' : ''}}>
                        <span class="toggle-switch-label"><span class="toggle-switch-indicator"></span></span>
                    </label>
                </div>

                <a class="btn btn-soft-primary d-flex align-items-center gap-1" href="{{route('admin.bogo-offer.edit',$offer->id)}}">
                    <i class="tio-edit"></i> {{translate('messages.Edit')}}
                </a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-md-center">
                    <div class="col-md-3 mb-3 mb-md-0">
                        <img class="rounded w-100 onerror-image" data-onerror-image="{{dynamicAsset('assets/admin/img/900x400/img1.png')}}"
                             src="{{ $offer->image_full_url }}" alt="bogo offer">
                    </div>
                    <div class="col-md-5">
                        <h4 class="mb-1">{{$offer->title}}</h4>
                        @php($description = $offer->description ?? '')
                        <p class="mb-0 opacity-75">
                            @if(Str::length($description) > 140)
                                <span class="description-short">{{ Str::limit($description, 140, '...') }}</span>
                                <span class="description-full d-none">{{ $description }}</span>
                                <a href="javascript:" class="toggle-description">{{translate('messages.See_More')}}</a>
                            @else
                                {{ $description }}
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light rounded p-3">
                            <h6 class="d-flex align-items-center gap-2 mb-3">
                                <i class="tio-calendar text-gray1"></i> {{translate('messages.BOGO_Offer_Duration')}}
                            </h6>
                            <div class="d-flex align-items-center mb-2">
                                <span class="opacity-75" style="min-width:130px">{{translate('messages.Start_Date_&_Time')}}</span>
                                <span class="mr-2">:</span>
                                <strong>{{$offer->start_date ? $offer->start_date->format('d M, Y h:i A') : 'N/A'}}</strong>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="opacity-75" style="min-width:130px">{{translate('messages.End_Date_&_Time')}}</span>
                                <span class="mr-2">:</span>
                                <strong>{{$offer->end_date ? $offer->end_date->format('d M, Y h:i A') : 'N/A'}}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                @php($offer_active = \App\CentralLogics\Helpers::bogo_offer_duration($offer))
                @php($stats = [
                    translate('messages.Created_At') => $offer->created_at->format('d M, Y h:i A'),
                    translate('messages.Offer_Active') => $offer_active,
                    translate('messages.Buy_Food_Qty') => $offer->buy_qty,
                    translate('messages.Get_Food_Qty') => $offer->get_qty,
                    translate('messages.Usage_Limit_Total') => $offer->usage_limit_total
                        ? $offer->usage_limit_total.' '.translate('messages.Order')
                        : 'N/A',
                    translate('messages.Usage_Limit') => $offer->usage_limit_per_customer
                        ? translate('messages.Per_person').' '.$offer->usage_limit_per_customer.' '.translate('messages.Order')
                        : 'N/A',
                ])

                <div class="row">
                    @foreach($stats as $label => $value)
                        <div class="col-6 col-md-2 mb-3 mb-md-0">
                            <span class="d-block opacity-75 font-size-sm mb-1">{{ $label }}</span>
                            <span class="d-block fs-16 font-weight-bold text-dark">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-0 py-2 search--button-wrapper">
                <h5 class="card-title">
                    {{translate('messages.Restaurant_list')}}
                    <span class="badge badge-soft-dark ml-2">{{$enrollments->total()}}</span>
                </h5>
                <form id="search-form" action="{{ url()->current() }}" method="GET">
                    <div class="input--group input-group input-group-merge input-group-flush">
                        <input id="datatableSearch_" type="search" name="search" value="{{ request('search') }}"
                               class="form-control" placeholder="{{translate('messages.search_here')}}" aria-label="Search">
                        <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                    </div>
                </form>
                <div class="hs-unfold mr-2">
                    <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm"
                       href="javascript:;"
                       data-hs-unfold-options='{"target": "#bogoRestaurantExportDropdown", "type": "css-animation"}'>
                        <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                    </a>
                    <div id="bogoRestaurantExportDropdown" class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                        <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                        <a target="__blank" class="dropdown-item"
                           href="{{ route('admin.bogo-offer.restaurant-export', ['id' => $offer->id, 'type' => 'excel', 'search' => request('search')]) }}">
                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                                 src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg" alt="excel">
                            {{ translate('messages.excel') }}
                        </a>
                        <a target="__blank" class="dropdown-item"
                           href="{{ route('admin.bogo-offer.restaurant-export', ['id' => $offer->id, 'type' => 'csv', 'search' => request('search')]) }}">
                            <img class="avatar avatar-xss avatar-4by3 mr-2"
                                 src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg" alt="csv">
                            {{ translate('messages.csv') }}
                        </a>
                    </div>
                </div>

                {{-- An expired offer cannot be enrolled into: the endpoint refuses it, and a
                     restaurant added to an offer that is already over would never serve the
                     bundle once. The button says why rather than collecting an error. --}}
                @php($expired = (bool) ($offer->end_date && $offer->end_date->isPast()))

                @if($expired)
                    <span data-toggle="tooltip" data-placement="top"
                          title="{{ translate('messages.this_offer_has_already_ended') }}">
                        <button type="button" class="btn btn--primary" disabled>
                            <i class="tio-add-circle"></i> {{translate('messages.Add_Restaurant')}}
                        </button>
                    </span>
                @else
                    <button type="button" class="btn btn--primary offcanvas-trigger" data-target="#offcanvas__add_restaurant">
                        <i class="tio-add-circle"></i> {{translate('messages.Add_Restaurant')}}
                    </button>
                @endif
            </div>

            @if($enrollments->total() === 0)
                {{-- Same empty panel either way, but the copy has to say which it is: nobody
                     has joined, or the search simply matched nothing. --}}
                <div class="px-4 pb-4">
                    <div class="bg-light rounded d-flex flex-column align-items-center justify-content-center text-center px-3"
                         style="min-height: 420px">
                        <img src="{{dynamicAsset('assets/admin/img/empty.png')}}" alt="empty" style="width:64px;height:auto">
                        <h4 class="mt-4 mb-2 font-weight-bold">
                            {{ request()->filled('search')
                                ? translate('no_data_found')
                                : translate('messages.No_Restaurants_Yet') }}
                        </h4>
                        <p class="opacity-75 mb-0" style="max-width: 480px">
                            {{ request()->filled('search')
                                ? translate('messages.No_restaurant_matched_your_search')
                                : translate('messages.No_restaurants_have_joined_the_BOGO_offer_yet') }}
                        </p>
                    </div>
                </div>
            @else
                <div class="table-responsive datatable-custom">
                    <table class="font-size-sm table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                        <tr>
                            <th>{{ translate('messages.sl') }}</th>
                            <th>{{ translate('messages.Restaurant') }}</th>
                            <th>{{ translate('messages.Joining_Offer_Date') }}</th>
                            <th>{{ translate('messages.Buy_Food') }}</th>
                            <th>{{ translate('messages.Get_Food') }}</th>
                            <th>{{ translate('messages.status') }}</th>
                            <th class="text-center">{{ translate('messages.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @include('admin-views.bogo-offer.partials._restaurant_table', [
                            'enrollments' => $enrollments,
                            'addOnNames' => $addOnNames,
                            'hiddenReasons' => $hiddenReasons,
                        ])
                        </tbody>
                    </table>
                    <div class="page-area px-4 pb-3">
                        <div class="d-flex align-items-center justify-content-end">
                            <div>{!! $enrollments->links() !!}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Add Restaurant : right-hand offcanvas, per design -->
    <div id="offcanvas__add_restaurant" class="custom-offcanvas d-flex flex-column justify-content-between" style="--offcanvas-width: 620px">
        <div class="h-100 d-flex flex-column">
            <div class="custom-offcanvas-header d-flex justify-content-between align-items-center px-3 py-3">
                <h2 class="mb-0 fs-18">{{translate('messages.Add_Restaurant')}}</h2>
                <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;</button>
            </div>

            <div class="custom-offcanvas-body p-20 flex-grow-1">
                <div class="bg-light rounded p-3 mb-4">
                    <div class="form-group mb-0">
                        <label class="input-label">
                            {{translate('messages.Restaurant')}} <span class="text-danger">*</span>
                            <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                  data-original-title="{{translate('messages.Pick_the_restaurant_that_will_run_this_offer')}}">
                                <i class="tio-info text-gray1 fs-16"></i>
                            </span>
                        </label>
                        <select id="bogo_restaurant_id" class="form-control h--45px">
                            <option value="">{{ translate('messages.Select_Restaurant') }}</option>
                            @forelse($availableRestaurants as $restaurant)
                                <option value="{{$restaurant->id}}">{{$restaurant->name}}</option>
                            @empty
                                <option value="" disabled>{{ translate('messages.All_restaurants_have_already_joined_this_offer') }}</option>
                            @endforelse
                        </select>
                    </div>
                </div>

                <h5 class="mb-3">{{translate('messages.Select_Foods_for_BOGO_offer')}}</h5>

                @foreach([['buy', $offer->buy_qty], ['get', $offer->get_qty]] as [$type, $required])
                    <div class="bg-light rounded p-3 mb-3">
                        <h6 class="mb-1">{{ $type === 'buy' ? translate('messages.Buy_Food') : translate('messages.Get_Food') }}</h6>
                        <p class="font-size-sm opacity-75">
                            {{ $type === 'buy'
                                ? translate('messages.Customers_must_buy_the_selected_Foods_with_the_specific_quantities_to_qualify')
                                : translate('messages.Customers_must_buy_all_selected_Foods_in_the_specified_quantities_to_qualify') }}
                        </p>

                        {{-- The inline contrast fix this used to carry now lives in .alert--note,
                             so every note bar on the promotion screens reads the same way. --}}
                        <div class="alert alert-soft-warning alert--note d-flex align-items-start gap-2 py-2">
                            <i class="tio-error lh-base"></i>
                            <span>
                                {{ translate('messages.You_must_add') }} <strong>{{$required}}</strong>
                                {{ $type === 'buy'
                                    ? translate('messages.foods_as_the_buy_quantity_for_this_BOGO_offer')
                                    : translate('messages.foods_as_the_get_quantity_for_this_BOGO_offer') }}
                                <span class="{{$type}}-total-wrap d-none">
                                    (<strong class="{{$type}}-total">0</strong>/{{$required}})
                                </span>
                            </span>
                        </div>

                        <div class="form-group mb-2">
                            <label class="input-label">
                                {{translate('messages.Select_Food')}} <span class="text-danger">*</span>
                                <span class="input-label-secondary text--title ml-0 mr-1" data-toggle="tooltip" data-placement="top"
                                      data-original-title="{{translate('messages.Search_and_pick_a_food_from_this_restaurant')}}">
                                    <i class="tio-info text-gray1 fs-16"></i>
                                </span>
                            </label>
                            <select class="form-control h--45px food-picker" data-type="{{$type}}" disabled>
                                <option value="">{{ translate('messages.Select_Food') }}</option>
                            </select>
                        </div>

                        <div class="selected-items" data-type="{{$type}}"></div>
                    </div>
                @endforeach
            </div>

            <div class="offcanvas-footer bg-white p-3 d-flex align-items-center gap-3">
                <button type="button" class="btn btn--reset h--45px flex-fill" id="bogo_reset">{{translate('messages.Reset')}}</button>
                <button type="button" class="btn btn--primary h--45px flex-fill" id="bogo_add_restaurant">{{translate('messages.Add')}}</button>
            </div>
        </div>
    </div>
    {{-- Enrolment drawer, filled on demand by the eye action and the "N Foods" link. --}}
    <div id="offcanvas__enrollment_detail" class="custom-offcanvas d-flex flex-column justify-content-between" style="--offcanvas-width: 620px">
        <div id="enrollment-detail-body" class="h-100"></div>
    </div>

    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>

    {{-- Opened by the warning icon on any row whose bundle is approved but not reaching
         customers. --}}
    @include('partials.promotion._visibility_modal', ['promotionLabel' => translate('messages.BOGO_offer')])

    <!-- Reject -->
    @include('partials.bogo._food_config_modal')

    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <button type="button" class="close ml-auto" data-dismiss="modal">&times;</button>
                    <i class="tio-warning text-danger" style="font-size:48px"></i>
                    <h4 class="mt-3">{{translate('messages.Reject_Restaurant_Join_Request')}}?</h4>
                    <p class="opacity-75">{{translate('messages.If_you_reject_this_restaurant_it_wont_be_able_to_join_the_BOGO_offer_campaign')}}</p>
                    <form id="reject-form" method="post">
                        @csrf
                        <div class="form-group text-left">
                            <label class="input-label">{{translate('messages.Rejection_Reason')}}</label>
                            <textarea name="rejection_reason" class="form-control" rows="3" maxlength="1000"
                                      data-counter="count_rejection_reason"
                                      placeholder="{{translate('messages.Type_the_reason')}}"></textarea>
                            <small class="d-block text-right opacity-75">
                                <span id="count_rejection_reason">0</span>/1000
                            </small>
                        </div>
                        <div class="btn--container justify-content-center">
                            <button type="button" class="btn btn--reset" data-dismiss="modal">{{translate('messages.close')}}</button>
                            <button type="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="{{ dynamicAsset('assets/admin/js/offcanvas.js') }}"></script>
    @include('admin-views.partials._status_change_alert')
    <script>
        "use strict";

        const requiredQty = {buy: {{ $offer->buy_qty }}, get: {{ $offer->get_qty }}};
        const selected = {buy: [], get: []};
        let foodCatalog = [];

        $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});

        const drawer = $('#offcanvas__add_restaurant');

        // Used by the picker to reopen an enrolled line read-only.
        const foodsEndpoint = '{{ route('admin.bogo-offer.restaurant-foods') }}';

        // dropdownParent keeps the select2 dropdown inside the offcanvas; without it the
        // list renders behind the panel.
        function initSelect2($el, placeholder) {
            $el.select2({
                width: '100%',
                placeholder: placeholder,
                dropdownParent: drawer,
            });
        }

        initSelect2($('#bogo_restaurant_id'), '{{ translate('messages.Select_Restaurant') }}');

    </script>

    {{-- Food picker engine (select2 rows, config modal, selection list). Needs
         requiredQty / selected / foodCatalog / drawer, defined just above. --}}
    @include('partials.bogo._food_picker_scripts')

    <script>
        "use strict";

        $('#bogo_restaurant_id').on('change', function () {
            const restaurantId = $(this).val();
            resetSelections();

            // Clearing the restaurant has to clear its menu too, else the pickers keep
            // offering foods that belong to whoever was selected before.
            if (!restaurantId) {
                foodCatalog = [];

                $('.food-picker').each(function () {
                    const $picker = $(this);
                    if ($picker.hasClass('select2-hidden-accessible')) $picker.select2('destroy');
                    $picker.prop('disabled', true)
                        .html('<option value="">{{ translate('messages.Select_Food') }}</option>');
                    initFoodSelect2($picker, '{{ translate('messages.Select_Food') }}');
                });

                return;
            }

            $.get('{{ route('admin.bogo-offer.restaurant-foods') }}', {restaurant_id: restaurantId}, function (foods) {
                foodCatalog = foods;
                // Plain options: the row markup comes from templateResult, and unavailable
                // is shown as a band on the thumbnail rather than a suffix on the name.
                const options = foods.map(f =>
                    `<option value="${f.id}" ${f.is_available ? '' : 'disabled'}>${escapeHtml(f.name)}</option>`
                ).join('');

                $('.food-picker').each(function () {
                    const $picker = $(this);
                    if ($picker.hasClass('select2-hidden-accessible')) {
                        $picker.select2('destroy');
                    }
                    $picker.prop('disabled', false)
                        .html(`<option value="">{{ translate('messages.Select_Food') }}</option>` + options);
                    initFoodSelect2($picker, '{{ translate('messages.Select_Food') }}');
                });
            });
        });


        $('#bogo_reset').on('click', resetSelections);

        // Null when adding; set to the update URL while editing an existing enrolment.
        let editingUrl = null;
        // True while reworking a denied enrolment, which goes back out as an admin request
        // rather than going live - so it needs a different word on success.
        let editingResubmit = false;

        $(document).on('click', '.edit-and-approve', function () {
            const payload = $(this).data('payload');

            $('#offcanvas__enrollment_detail').removeClass('open');
            editingUrl = payload.url;
            editingResubmit = !!payload.is_resubmit;

            // The restaurant is fixed for an existing enrolment. It is absent from the
            // dropdown (that list is only the ones yet to join), so add it as a temporary
            // option. Disable before change.select2 or the widget redraws still enabled.
            const $restaurant = $('#bogo_restaurant_id');

            if (!$restaurant.find('option[value="' + payload.restaurant_id + '"]').length) {
                $restaurant.append(
                    $('<option>', {value: payload.restaurant_id, text: payload.restaurant_name, 'data-temp': 1})
                );
            }

            $restaurant.val(payload.restaurant_id).prop('disabled', true).trigger('change.select2');
            resetSelections();

            $.get('{{ route('admin.bogo-offer.restaurant-foods') }}', {restaurant_id: payload.restaurant_id}, function (foods) {
                foodCatalog = foods;

                ['buy', 'get'].forEach(type => {
                    (payload[type] || []).forEach(row => {
                        const food = foodCatalog.find(f => f.id == row.food_id);
                        if (!food) return;

                        selected[type].push({
                            food_id: food.id,
                            name: food.name,
                            image: food.image_full_url,
                            quantity: row.quantity || 1,
                            max: food.maximum_cart_quantity || 0,
                            variations: food.variations || [],
                            selected_variations: row.selected_variations || [],
                            add_ons: food.add_ons || [],
                            add_on_ids: (row.add_on_ids || []).map(String),
                            add_on_qtys: (row.add_on_ids || []).map((id, i) => (row.add_on_qtys || [])[i] || 1)
                        });
                    });
                    render(type);
                });

                $('.food-picker').each(function () {
                    const $picker = $(this);
                    if ($picker.hasClass('select2-hidden-accessible')) $picker.select2('destroy');
                    $picker.prop('disabled', false).html(
                        `<option value="">{{ translate('messages.Select_Food') }}</option>` +
                        foods.map(f => `<option value="${f.id}" ${f.is_available ? '' : 'disabled'}>${escapeHtml(f.name)}</option>`).join('')
                    );
                    initFoodSelect2($picker, '{{ translate('messages.Select_Food') }}');
                });

                // The label comes with the payload: the same drawer approves a pending
                // request and resubmits a denied one, and the enrolment's state decides which.
                $('#bogo_add_restaurant').text(payload.submit_label || '{{ translate('messages.Edit_&_Approve') }}');
                $('#offcanvas__add_restaurant').addClass('open');
                $('#offcanvasOverlay').addClass('show');
            });
        });

        // offcanvas.js binds .offcanvas-close directly on ready, so it never sees the
        // enrolment drawer's button - that markup arrives later over ajax. Close here
        // instead, delegated, and drop edit mode so the next open is a clean add.
        $(document).on('click', '.offcanvas-close, #offcanvasOverlay', function () {
            $('.custom-offcanvas').removeClass('open');
            $('#offcanvasOverlay').removeClass('show');

            leaveEditMode();
        });

        // Drops the enrolment being edited so the drawer reopens as a clean add: the
        // injected option has to go too, or an already-joined restaurant stays offerable.
        // A plain add is left untouched - closing it should not discard the user's picks.
        function leaveEditMode() {
            if (!editingUrl) return;

            editingUrl = null;
            editingResubmit = false;

            $('#bogo_restaurant_id')
                .prop('disabled', false)
                .find('option[data-temp]').remove();

            $('#bogo_restaurant_id').val('').trigger('change');
            $('#bogo_add_restaurant').text('{{ translate('messages.Add') }}');
        }

        $('#bogo_add_restaurant').on('click', function () {
            const restaurantId = $('#bogo_restaurant_id').val();

            if (!restaurantId) {
                toastr.error('{{ translate('messages.Please_select_a_restaurant') }}');
                return;
            }

            const payload = type => selected[type].map(i => ({
                food_id: i.food_id,
                quantity: i.quantity,
                variations: i.selected_variations,
                variation_options: [],
                add_on_ids: i.add_on_ids,
                add_on_qtys: i.add_on_qtys
            }));

            $.post({
                url: editingUrl || '{{ route('admin.bogo-offer.add-restaurant', $offer->id) }}',
                data: {restaurant_id: restaurantId, buy_items: payload('buy'), get_items: payload('get')},
                success: function () {
                    toastr.success(editingResubmit
                        ? '{{ translate('messages.the_request_has_been_resent_to_the_restaurant') }}'
                        : '{{ translate('messages.restaurant_added_to_bogo_offer') }}');
                    setTimeout(() => location.reload(), 1500);
                },
                error: function (xhr) {
                    const errors = xhr.responseJSON?.errors || [];
                    errors.length
                        ? errors.forEach(e => toastr.error(e.message))
                        : toastr.error('{{ translate('messages.something_went_wrong') }}');
                }
            });
        });

        $(document).on('click', '.approve-enrollment', function () {
            const url = $(this).data('url');
            Swal.fire({
                title: '{{ translate('Are_you_sure_?') }}',
                text: '{{ translate('messages.you_want_to_approve_this_restaurant') }}',
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ translate('no') }}',
                confirmButtonText: '{{ translate('yes') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.post(url, {}, () => location.reload());
                }
            });
        });

        // The same counter the offer form uses. Bound here because this page does not
        // include _form_scripts, where that handler lives.
        $(document).on('input', '[data-counter]', function () {
            $('#' + $(this).data('counter')).text($(this).val().length);
        });

        $(document).on('click', '.reject-enrollment', function () {
            $('#reject-form').attr('action', $(this).data('url'));
            // One modal serves every row, so the reason typed for the last restaurant - and
            // its count - has to be cleared before it opens for the next one.
            $('#reject-form')[0].reset();
            $('#count_rejection_reason').text('0');
            $('#rejectModal').modal('show');
        });

        // Load the enrolment drawer on demand so the table stays light.
        $(document).on('click', '.enrollment-detail', function () {
            const target = $('#offcanvas__enrollment_detail');

            $('#enrollment-detail-body').html(
                '<div class="d-flex align-items-center justify-content-center h-100">' +
                '<div class="spinner-border text--primary" role="status"></div></div>'
            );
            target.addClass('open');
            $('#offcanvasOverlay').addClass('show');

            $.get($(this).data('url'), function (html) {
                $('#enrollment-detail-body').html(html);
            }).fail(function () {
                toastr.error('{{ translate('messages.something_went_wrong') }}');
                target.removeClass('open');
                $('#offcanvasOverlay').removeClass('show');
            });
        });

        $(document).on('click', '.toggle-description', function () {
            const wrapper = $(this).closest('p');
            wrapper.find('.description-short, .description-full').toggleClass('d-none');
            $(this).text($(this).text().trim() === '{{ translate('messages.See_More') }}'
                ? '{{ translate('messages.See_Less') }}'
                : '{{ translate('messages.See_More') }}');
        });
    </script>
@endpush
