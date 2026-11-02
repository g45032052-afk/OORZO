@extends('layouts.vendor.app')

@section('title', translate('messages.BOGO_Offer'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.bogo._food_picker_styles')

    @include('partials.promotion._list_styles')
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">{{ translate('messages.BOGO_Offer') }}</h1>
        </div>

        <div class="card">
            <div class="card-header border-0 py-2 search--button-wrapper">
                <h5 class="card-title">
                    {{ translate('messages.BOGO_Offer_List') }}
                    <span class="badge badge-soft-dark ml-2">{{ $offers->total() }}</span>
                </h5>
                <form id="search-form" action="{{ url()->current() }}" method="GET">
                    <div class="input--group input-group input-group-merge input-group-flush">
                        <input id="datatableSearch_" type="search" name="search" value="{{ request('search') }}"
                               class="form-control" placeholder="{{ translate('messages.search_here') }}" aria-label="Search">
                        <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                    </div>
                </form>
            </div>

            <div class="table-responsive datatable-custom">
                <table class="font-size-sm table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('messages.SL') }}</th>
                        <th>{{ translate('messages.Title') }}</th>
                        <th>{{ translate('messages.Image') }}</th>
                        <th>{{ translate('messages.Duration') }}</th>
                        <th>{{ translate('messages.status') }}</th>
                        <th class="text-center">{{ translate('messages.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @include('vendor-views.bogo-offer.partials._offer_table')
                    </tbody>
                </table>

                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div>{!! $offers->links() !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail drawer, filled on demand by the eye action and the title link. --}}
    <div id="offcanvas__offer_detail" class="custom-offcanvas d-flex flex-column justify-content-between"
         style="--offcanvas-width: 620px">
        <div id="offer-detail-body" class="h-100"></div>
    </div>

    @include('vendor-views.bogo-offer.partials._join_drawer')
    @include('partials.bogo._food_config_modal')

    {{-- Opened by the warning icon beside any offer this restaurant runs but customers
         cannot currently see. --}}
    @include('partials.promotion._visibility_modal', [
        'promotionLabel' => translate('messages.BOGO_offer'),
    ])

    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>
@endsection

@push('script_2')
    <script src="{{ dynamicAsset('assets/admin/js/offcanvas.js') }}"></script>
    <script>
        "use strict";

        const requiredQty = {buy: 0, get: 0};
        const selected = {buy: [], get: []};
        let foodCatalog = [];

        // Offer being joined or resubmitted; null when no drawer is open.
        let activeOffer = null;
        let activeMode = 'join';

        $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});

        const drawer = $('#offcanvas__join_offer');

        // Used by the picker to reopen an enrolled line read-only.
        const foodsEndpoint = '{{ route('vendor.bogo-offer.restaurant-foods') }}';
    </script>

    {{-- Food picker engine (select2 rows, config modal, selection list). Needs
         requiredQty / selected / foodCatalog / drawer, defined just above. --}}
    @include('partials.bogo._food_picker_scripts')

    <script>
        "use strict";

        function openOffcanvas($el) {
            $el.addClass('open');
            $('#offcanvasOverlay').addClass('show');
        }

        // offcanvas.js binds .offcanvas-close on ready, so it never sees the detail drawer's
        // button - that markup arrives later over ajax. Close here instead, delegated.
        $(document).on('click', '.offcanvas-close, #offcanvasOverlay', function () {
            $('.custom-offcanvas').removeClass('open');
            $('#offcanvasOverlay').removeClass('show');
        });

        $(document).on('click', '.offer-detail', function () {
            const target = $('#offcanvas__offer_detail');

            $('#offer-detail-body').html(
                '<div class="d-flex align-items-center justify-content-center h-100">' +
                '<div class="spinner-border text--primary" role="status"></div></div>'
            );
            openOffcanvas(target);

            $.get($(this).data('url'), function (html) {
                $('#offer-detail-body').html(html);
            }).fail(function () {
                toastr.error('{{ translate('messages.something_went_wrong') }}');
                target.removeClass('open');
                $('#offcanvasOverlay').removeClass('show');
            });
        });

        // Both entry points share one drawer; only the title, button label and target URL
        // differ between joining fresh and reworking a rejected selection.
        function openJoinDrawer(offerId, mode) {
            activeOffer = offerId;
            activeMode = mode;

            $('#offcanvas__offer_detail').removeClass('open');

            $('#join_drawer_title').text(mode === 'resubmit'
                ? '{{ translate('messages.Update_Food') }}'
                : '{{ translate('messages.Add_Food') }}');
            $('#join_submit').text(mode === 'resubmit'
                ? '{{ translate('messages.Resubmit') }}'
                : '{{ translate('messages.Join') }}');

            $.get('{{ route('vendor.bogo-offer.detail', ':id') }}'.replace(':id', offerId), function (html) {
                const $html = $('<div>').html(html);
                const meta = $html.find('#offer_requirements').data('meta');

                // Both the requirements and any existing selection ride on the drawer, so
                // whatever opened it does not have to carry a payload.
                const prefill = $html.find('#offer_requirements').data('items') || [];

                requiredQty.buy = meta.buy_qty;
                requiredQty.get = meta.get_qty;

                $('.buy-required').text(meta.buy_qty);
                $('.get-required').text(meta.get_qty);

                selected.buy = [];
                selected.get = [];

                $.get('{{ route('vendor.bogo-offer.restaurant-foods') }}', function (foods) {
                    foodCatalog = foods;

                    prefill.forEach(function (row) {
                        const food = foodCatalog.find(f => f.id == row.food_id);
                        if (!food) return;

                        selected[row.side].push({
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

                    $('.food-picker').each(function () {
                        const $picker = $(this);
                        if ($picker.hasClass('select2-hidden-accessible')) $picker.select2('destroy');
                        $picker.prop('disabled', false).html(
                            '<option value="">{{ translate('messages.Select_Food') }}</option>' +
                            foods.map(f => `<option value="${f.id}" ${f.is_available ? '' : 'disabled'}>${escapeHtml(f.name)}</option>`).join('')
                        );
                        initFoodSelect2($picker, '{{ translate('messages.Select_Food') }}');
                    });

                    render('buy');
                    render('get');
                    openOffcanvas(drawer);
                });
            });
        }

        $(document).on('click', '.join-offer', function () {
            openJoinDrawer($(this).data('id'), 'join');
        });

        // Reworking a denial and changing what is already running both land on the same
        // drawer and the same resubmit endpoint.
        $(document).on('click', '.edit-and-resubmit', function () {
            openJoinDrawer($(this).data('id'), 'resubmit');
        });

        $('#join_reset').on('click', resetSelections);

        $('#join_submit').on('click', function () {
            const payload = type => selected[type].map(i => ({
                food_id: i.food_id,
                quantity: i.quantity,
                variations: i.selected_variations,
                variation_options: [],
                add_on_ids: i.add_on_ids,
                add_on_qtys: i.add_on_qtys
            }));

            const url = activeMode === 'resubmit'
                ? '{{ route('vendor.bogo-offer.resubmit', ':id') }}'.replace(':id', activeOffer)
                : '{{ route('vendor.bogo-offer.join', ':id') }}'.replace(':id', activeOffer);

            $.post({
                url: url,
                data: {buy_items: payload('buy'), get_items: payload('get')},
                success: function () {
                    toastr.success(activeMode === 'resubmit'
                        ? '{{ translate('messages.your_request_has_been_resubmitted') }}'
                        : '{{ translate('messages.your_request_has_been_sent_for_approval') }}');
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

        $(document).on('click', '.toggle-description', function () {
            const wrapper = $(this).closest('p');
            wrapper.find('.description-short, .description-full').toggleClass('d-none');
            $(this).text($(this).text().trim() === '{{ translate('messages.See_More') }}'
                ? '{{ translate('messages.See_Less') }}'
                : '{{ translate('messages.See_More') }}');
        });
    </script>
@endpush
