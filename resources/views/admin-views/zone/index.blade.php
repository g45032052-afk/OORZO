@extends('layouts.admin.app')

@section('title', translate('Add_new_zone'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet"
        href="{{ dynamicAsset('assets/admin/css/admin-shared.css') }}?v={{ @filemtime(public_path('assets/admin/css/admin-shared.css')) }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <img src="{{ dynamicAsset('assets/admin/img/zone.png') }}" alt="" class="mr-2">
                        {{ translate('messages.Add_New_Business_Zone') }}
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="javascript:" method="post" id="zone_form" class="shadow--card">
                    @csrf
                    <div class="row justify-content-between">
                        <div class="col-md-5">
                            <div class="zone-setup-instructions">
                                <div class="zone-setup-top">
                                    <h6 class="subtitle">{{ translate('messages.instructions') }}</h6>
                                    <p>
                                        {{ translate('messages.Create_&_connect_dots_in_a_specific_area_on_the_map_to_add_a_new_business_zone.') }}
                                    </p>
                                </div>
                                <div class="zone-setup-item">
                                    <div class="zone-setup-icon">
                                        <i class="tio-hand-draw"></i>
                                    </div>
                                    <div class="info">
                                        {{ translate('messages.Use_this_‘Hand_Tool’_to_find_your_target_zone.') }}
                                    </div>
                                </div>
                                <div class="zone-setup-item">
                                    <div class="zone-setup-icon">
                                        <i class="tio-free-transform"></i>
                                    </div>
                                    <div class="info">
                                        {{ translate('messages.Use_this_‘Shape_Tool’_to_point_out_the_areas_and_connect_the_dots._A_minimum_of_3_points/dots_is_required.') }}
                                    </div>
                                </div>
                                <div class="instructions-image mt-4">
                                    <img src={{ dynamicAsset('assets/admin/img/instructions.gif') }}
                                        alt="instructions">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-7 zone-setup">
                            <div class="pl-xl-5 pl-xxl-0">
                                @php($language = \App\Models\BusinessSetting::where('key', 'language')->first())
                                @php($language = $language?->value)
                                @php($default_lang = str_replace('_', '-', app()->getLocale()))
                                <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                    <ul class="nav nav-tabs mb-4">
                                        <li class="nav-item">
                                            <a class="nav-link lang_link active" href="#"
                                                id="default-link">{{ translate('messages.default') }} <span
                                                    class="form-label-secondary text-danger mt-2" data-toggle="tooltip"
                                                    data-placement="right"
                                                    data-original-title="{{ translate('Choose_your_preferred_language_&_set_your_zone_name.') }}"><i class="tio-info text-muted"></i></span></a>

                                        </li>
                                        @if ($language)
                                            @forelse (json_decode($language) as $lang)
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link" href="#"
                                                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                </li>
                                            @empty
                                            @endforelse
                                        @endif

                                    </ul>
                                </div>
                                <div class="tab-content">

                                    <div class="lang_form" id="default-form">
                                        <div class="form-group mb-3 " id="">
                                            <div class="row g-3">
                                                <div class="col-sm-6">
                                                    <label class="input-label"
                                                        for="exampleFormControlInput1">{{ translate('messages.business_Zone_name') }}
                                                        ({{ translate('messages.default') }})</label>
                                                    <input type="text" name="name[]" class="form-control mb-3"
                                                        placeholder="{{ translate('messages.Type_new_zone_name_here') }}"
                                                        maxlength="191" id="default-form-input"
                                                        oninvalid="document.getElementById('default-form-input').click()">
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="input-label"
                                                        for="exampleFormControlInput1">{{ translate('messages.Zone_Display_Name') }}
                                                        ({{ translate('messages.default') }})</label>
                                                    <input type="text" name="display_name[]" class="form-control"
                                                        placeholder="{{ translate('messages.Write_a_New_Display_Zone_Name') }}"
                                                        maxlength="191">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden" name="lang[]" value="default">
                                    @if ($language)
                                        @forelse (json_decode($language) as $lang)
                                            <div class="d-none lang_form" id="{{ $lang }}-form">
                                                <div class="form-group mb-3">
                                                    <div class="row g-3">
                                                        <div class="col-sm-6">
                                                            <label class="input-label"
                                                                for="exampleFormControlInput1">{{ translate('messages.business_Zone_name') }}
                                                                ({{ strtoupper($lang) }})</label>
                                                            <input id="name" type="text" name="name[]"
                                                                class="form-control mb-3 h--45px"
                                                                placeholder="{{ translate('messages.Type_new_zone_name_here') }}">
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <label class="input-label"
                                                                for="exampleFormControlInput1">{{ translate('messages.Zone_Display_Name') }}
                                                                ({{ strtoupper($lang) }})</label>
                                                            <input type="text" name="display_name[]" class="form-control"
                                                                placeholder="{{ translate('messages.Write_a_New_Display_Zone_Name') }}"
                                                                maxlength="191">
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>
                                            <input type="hidden" name="lang[]" value="{{ $lang }}">
                                        @empty
                                        @endforelse
                                    @endif
                                </div>


                                <div class="form-group mb-3 d-none">
                                    <label class="input-label"
                                        for="exampleFormControlInput1">{{ translate('Coordinates') }}<span
                                            class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages.draw_your_zone_on_the_map') }}">{{ translate('messages.draw_your_zone_on_the_map') }}</span></label>
                                    <textarea type="text" rows="8" name="coordinates" id="coordinates" class="form-control" readonly></textarea>
                                </div>

                                <div class="map-warper overflow-hidden rounded">
                                    <input id="pac-input" class="controls rounded initial-8"
                                        title="{{ translate('messages.search_your_location_here') }}" type="text"
                                        placeholder="{{ translate('messages.search_here') }}" />
                                    <div id="map-canvas" class="h-100 m-0 p-0"></div>
                                </div>
                                <div class="btn--container mt-3 justify-content-end">
                                    <button id="reset_btn" type="button"
                                        class="btn btn--reset">{{ translate('messages.reset') }}</button>
                                    <button type="submit"
                                        class="btn btn--primary">{{ translate('messages.submit') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-sm-12 col-lg-12 mb-3 my-lg-2">
                <div class="card">
                    <div class="card-header py-2 flex-wrap border-0 align-items-center">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">{{ translate('messages.zone_list') }}<span
                                    class="badge badge-soft-dark ml-2" id="itemCount">{{ $zones->total() }}</span></h5>
                            <form class="my-2 mr-sm-2 mr-xl-4 ml-sm-auto flex-grow-1 flex-grow-sm-0">
                                <!-- Search -->

                                <div class="input--group input-group input-group-merge input-group-flush">
                                    <input id="datatableSearch_" type="search" name="search" class="form-control"
                                        value="{{ request()?->search ?? null }}"
                                        placeholder="{{ translate('messages.Search_by_name') }}"
                                        aria-label="{{ translate('messages.search') }}">
                                    <button type="submit" class="btn btn--secondary">
                                        <i class="tio-search"></i>
                                    </button>
                                </div>
                                <!-- End Search -->
                            </form>
                            <!-- Unfold -->
                            <div class="hs-unfold ml-3">
                                <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm"
                                    href="javascript:"
                                    data-hs-unfold-options='{
                                        "target": "#usersExportDropdown",
                                        "type": "css-animation"
                                    }'>
                                    <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                                </a>

                                <div id="usersExportDropdown"
                                    class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                    <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                                    <a target="__blank" id="export-excel" class="dropdown-item"
                                        href="{{ route('admin.zone.export-zones', ['type' => 'excel', request()->getQueryString()]) }}">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="{{ dynamicAsset('assets/admin') }}/svg/components/excel.svg"
                                            alt="Image Description">
                                        {{ translate('messages.excel') }}
                                    </a>
                                    <a target="__blank" id="export-csv" class="dropdown-item"
                                        href="{{ route('admin.zone.export-zones', ['type' => 'csv', request()->getQueryString()]) }}">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="{{ dynamicAsset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                            alt="Image Description">
                                        {{ translate('messages.csv') }}
                                    </a>
                                </div>
                            </div>
                            <!-- End Unfold -->
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table id="columnSearchDatatable"
                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                            data-hs-datatables-options='{
                                    "order": [],
                                    "orderCellsTop": true,
                                    "paging":false
                                }'>
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ translate('messages.sl') }}</th>
                                    <th class="text-center">{{ translate('messages.zone_id') }}</th>
                                    <th class="pl-5">{{ translate('messages.name') }}</th>
                                    <th class="pl-5">{{ translate('messages.Zone_Display_Name') }}</th>
                                    <th class="text-center">{{ translate('messages.restaurants') }}</th>
                                    <th class="text-center">{{ translate('messages.deliverymen') }}</th>
                                    <th class="text-center">{{ translate('Default_Status') }}</th>
                                    <th>{{ translate('messages.status') }}</th>
                                    <th class="w-40px text-center">{{ translate('messages.action') }}</th>
                                </tr>
                            </thead>

                            <tbody id="set-rows">
                             @includeIf('admin-views.zone.partials._table')
                            </tbody>
                        </table>
                        @if (count($zones) === 0)
                            <div class="empty--data">
                                <img src="{{ dynamicAsset('assets/admin/img/empty.png') }}" alt="public">
                                <h5>
                                    {{ translate('no_data_found') }}
                                </h5>
                            </div>
                        @endif
                        <div class="page-area px-4 pb-3">
                            <div class="d-flex align-items-center justify-content-end">
                                <div>
                                    {!! $zones->withQueryString()->links() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Table -->
        </div>
    </div>

    {{-- The zone settings panel. The markup is fetched per zone, so the drawer
         itself stays empty until an icon is clicked. --}}
    <div id="offcanvas__zone_settings" class="custom-offcanvas d-flex flex-column justify-content-between">
        <div id="zone-settings-view" class="h-100"></div>
    </div>
    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>


{{-- Shown when an admin tries to switch on a zone that is not set up yet. The
     toggle is inert until both setups exist; this says which are missing and
     links straight to them. --}}
<div class="modal fade" id="zone-setup-required-modal">
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
                        <h5 class="modal-title mb-3">{{ translate('messages.Zone_Setup_Incomplete!') }}</h5>
                    </div>
                    <div class="text-center">
                        {{-- Replaced on open with the line for whichever setups are actually
                             missing; this is the both-missing wording it falls back to. --}}
                        <p id="zone-setup-required-text">{{ translate('messages.This_zone_can_not_be_activated_until_it_can_price_a_delivery_and_quote_a_time_for_it._Complete_the_setups_below,_then_switch_the_zone_on.') }}</p>
                    </div>
                    <ul class="mb-20 pl-3" id="zone-setup-required-list"></ul>
                    <div class="btn--container justify-content-center">
                        <button type="button" class="btn btn--primary min-w-120px"
                            data-dismiss="modal">{{ translate('messages.Okay') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('script_2')
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}&libraries=places,marker&v=3.61">
    </script>

    <script>
        "use strict";

        // The settings icons are re-rendered whenever a zone is added, and the
        // panel's own markup arrives by ajax, so every handler here is delegated.
        (function () {
            const $drawer = $('#offcanvas__zone_settings');

            function closeDrawer() {
                $drawer.removeClass('open');
                $('#offcanvasOverlay').removeClass('show');
                $('body').removeClass('modal-open');
            }

            $(document).on('click', '.zone-settings-trigger', function (e) {
                e.preventDefault();

                const url = $(this).data('url');

                $.get({
                    url: url,
                    beforeSend: function () {
                        $('#zone-settings-view').empty();
                        $('#loading').show();
                    },
                    success: function (data) {
                        if (data.errors) {
                            data.errors.forEach(function (err) { toastr.error(err.message); });
                            return;
                        }
                        $('#zone-settings-view').html(data.view);
                        $drawer.addClass('open');
                        $('#offcanvasOverlay').addClass('show');
                        $('body').addClass('modal-open');
                    },
                    error: function (xhr) {
                        const errs = xhr.responseJSON && xhr.responseJSON.errors;
                        if (errs) { errs.forEach(function (err) { toastr.error(err.message); }); }
                        else { toastr.error('{{ translate('messages.An_unexpected_error_occurred.') }}'); }
                    },
                    complete: function () {
                        $('#loading').hide();
                    }
                });
            });

            $(document).on('click', '#offcanvas__zone_settings .offcanvas-close, #offcanvasOverlay', closeDrawer);

            // A setting is its switch and the fields under it, so the fields are
            // only shown while the switch says the setting is in use.
            function syncFields($toggle) {
                $($toggle.data('fields')).toggleClass('d-none', !$toggle.is(':checked'));
            }

            $(document).on('change', '.zone-setting-toggle', function () {
                syncFields($(this));
            });

            // Reset restores the switches after this handler would run, so the
            // fields are redrawn once the browser has finished.
            $(document).on('reset', '#zone-settings-offcanvas-form', function () {
                setTimeout(function () {
                    $('#zone-settings-offcanvas-form .zone-setting-toggle').each(function () {
                        syncFields($(this));
                    });
                }, 0);
            });

            $(document).on('submit', '#zone-settings-offcanvas-form', function (e) {
                e.preventDefault();

                const $form = $(this);
                const $submit = $form.find('button[type="submit"]');
                $submit.prop('disabled', true);

                $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

                $.post({
                    url: $form.attr('action'),
                    data: $form.serialize(),
                    success: function (data) {
                        $submit.prop('disabled', false);
                        if (data.errors) {
                            data.errors.forEach(function (err) { toastr.error(err.message); });
                            return;
                        }
                        toastr.success(data.success);
                        closeDrawer();
                    },
                    error: function (xhr) {
                        $submit.prop('disabled', false);
                        const errs = xhr.responseJSON && xhr.responseJSON.errors;
                        if (errs) { errs.forEach(function (err) { toastr.error(err.message); }); }
                        else { toastr.error('{{ translate('messages.An_unexpected_error_occurred.') }}'); }
                    }
                });
            });
        })();
    </script>
    <script>
        "use strict";

        function status_form_alert(id, message, e) {
            e.preventDefault();
            Swal.fire({
                title: "{{ translate('messages.are_you_sure_?') }}",
                text: message,
                type: 'warning',
                showCloseButton: true,
                showCancelButton: true,
                cancelButtonColor: 'var(--secondary-clr)',
                confirmButtonColor: 'var(--primary-clr)',
                cancelButtonText: '{{ translate('Cancel') }}',
                confirmButtonText: '{{ translate('yes') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $('#' + id).submit()
                }
            })
        }

        auto_grow();

        function auto_grow() {
            let element = document.getElementById("coordinates");
            element.style.height = "5px";
            element.style.height = (element.scrollHeight) + "px";
        }

        $(document).on('ready', function() {

            $("#zone_form").on('keydown', function(e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                }
            })
        });


        const mapId = "{{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}";
        const {
            AdvancedMarkerElement
        } = google.maps.marker;

        let map; // Global declaration of the map
        let drawingPolyline = null; // polyline used while drawing (before the shape is closed)
        let drawingPolygon = null;  // editable polygon created after the shape is closed
        let polygonClosed = false;  // true once the user clicks the first dot to close
        let lastpolygon = null; // kept for backward compatibility with existing handlers
        let polygons = [];
        let drawingMode = true; // true = Shape tool (draw), false = Hand tool (pan)
        let vertexMarkers = []; // visible dot at each polygon vertex (drawing feedback)
        let handToolEl = null;
        let shapeToolEl = null;
        const MIN_VERTICES = 3;

        // translateY(50%) compensates for AdvancedMarkerElement's bottom-center
        // anchor so the circle's center sits on the LatLng.
        function vertexElement(highlighted) {
            const size = highlighted ? 20 : 12;
            const div = document.createElement('div');
            div.style.cssText =
                'width:' + size + 'px;' +
                'height:' + size + 'px;' +
                'border-radius:50%;' +
                'background:' + (highlighted ? '#00b35c' : '#FF0000') + ';' +
                'border:2px solid #fff;' +
                'box-shadow:0 1px 3px rgba(0,0,0,0.3);' +
                'cursor:' + (highlighted ? 'pointer' : 'default') + ';' +
                'transform:translateY(50%);';
            return div;
        }

        function currentPath() {
            if (polygonClosed && drawingPolygon) return drawingPolygon.getPath().getArray();
            if (drawingPolyline) return drawingPolyline.getPath().getArray();
            return [];
        }

        function syncVertexMarkers() {
            // AdvancedMarkerElement unmounts via `.map = null`, not setMap().
            vertexMarkers.forEach(function (m) { m.map = null; });
            vertexMarkers = [];
            // After close, the editable Polygon draws its own vertex handles.
            if (polygonClosed) return;
            const { AdvancedMarkerElement } = google.maps.marker;
            const path = currentPath();
            path.forEach(function (latLng, idx) {
                const isFirst = idx === 0;
                const canClose = isFirst && path.length >= MIN_VERTICES;
                const marker = new AdvancedMarkerElement({
                    position: latLng,
                    map: map,
                    content: vertexElement(canClose),
                    gmpClickable: canClose,
                    title: canClose ? "{{ translate('Click_to_close_polygon') }}" : "",
                    zIndex: 9999,
                });
                // AdvancedMarkerElement fires 'gmp-click', not 'click'.
                if (canClose) marker.addListener("gmp-click", closePolygon);
                vertexMarkers.push(marker);
            });
        }

        function clearDrawing() {
            if (drawingPolygon) {
                drawingPolygon.setMap(null);
                drawingPolygon = null;
            }
            if (drawingPolyline) {
                drawingPolyline.getPath().clear();
                drawingPolyline.setMap(map);       // re-show in case close hid it
                lastpolygon = drawingPolyline;
            }
            polygonClosed = false;
            vertexMarkers.forEach(function (m) { m.map = null; });
            vertexMarkers = [];
            $('#coordinates').val('');
            auto_grow();
        }

        function updateCoordinates() {
            const path = currentPath();
            $('#coordinates').val(path.length ? path.toString() : '');
            auto_grow();
            syncVertexMarkers();
        }

        function closePolygon() {
            if (!drawingPolyline) return;
            const path = drawingPolyline.getPath().getArray();
            if (path.length < MIN_VERTICES) return;

            drawingPolyline.setMap(null);
            drawingPolygon = new google.maps.Polygon({
                map: map,
                paths: path,
                editable: true,
                clickable: false,
                strokeColor: "#FF0000",
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: "#FF0000",
                fillOpacity: 0.1,
            });
            polygonClosed = true;
            lastpolygon = drawingPolygon;

            const polyPath = drawingPolygon.getPath();
            google.maps.event.addListener(polyPath, "set_at", updateCoordinates);
            google.maps.event.addListener(polyPath, "insert_at", updateCoordinates);
            google.maps.event.addListener(polyPath, "remove_at", updateCoordinates);

            // Polygon's built-in editable handles replace our custom dots.
            vertexMarkers.forEach(function (m) { m.map = null; });
            vertexMarkers = [];
            updateCoordinates();
        }

        // On-map Hand/Shape toolbar (custom map control replacing the removed DrawingManager).
        function setDrawingMode(drawing) {
            drawingMode = drawing;
            if (map) {
                map.setOptions({ draggableCursor: drawing ? "crosshair" : null });
            }
            if (shapeToolEl) {
                shapeToolEl.style.backgroundColor = drawing ? "#e7f0ff" : "#fff";
                shapeToolEl.style.color = drawing ? "#050df2" : "#444";
            }
            if (handToolEl) {
                handToolEl.style.backgroundColor = drawing ? "#fff" : "#e7f0ff";
                handToolEl.style.color = drawing ? "#444" : "#050df2";
            }
        }

        function buildDrawingControl() {
            const wrapper = document.createElement("div");
            wrapper.style.cssText = "margin:10px;display:flex;border-radius:4px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.3);background:#fff;font-family:Roboto,Arial,sans-serif;";

            handToolEl = document.createElement("div");
            handToolEl.title = "Hand Tool — pan the map";
            handToolEl.style.cssText = "cursor:pointer;display:flex;align-items:center;justify-content:center;width:36px;height:36px;font-size:18px;color:#444;";
            handToolEl.innerHTML = `<i class="tio-hand-draw"></i>`;

            shapeToolEl = document.createElement("div");
            shapeToolEl.title = "Shape Tool — click the map to connect the dots";
            shapeToolEl.style.cssText = "cursor:pointer;display:flex;align-items:center;justify-content:center;width:36px;height:36px;font-size:18px;color:#444;border-left:1px solid #e6e6e6;";
            shapeToolEl.innerHTML = `<i class="tio-free-transform"></i>`;

            handToolEl.addEventListener("click", function () { setDrawingMode(false); });
            shapeToolEl.addEventListener("click", function () { setDrawingMode(true); });

            wrapper.appendChild(handToolEl);
            wrapper.appendChild(shapeToolEl);
            return wrapper;
        }

        function resetMap(controlDiv) {
            // Set CSS for the control border.
            const controlUI = document.createElement("div");
            controlUI.style.backgroundColor = "#fff";
            controlUI.style.border = "2px solid #fff";
            controlUI.style.borderRadius = "3px";
            controlUI.style.boxShadow = "0 2px 6px rgba(0,0,0,.3)";
            controlUI.style.cursor = "pointer";
            controlUI.style.marginTop = "8px";
            controlUI.style.marginBottom = "22px";
            controlUI.style.textAlign = "center";
            controlUI.title = "Reset map";
            controlDiv.appendChild(controlUI);
            // Set CSS for the control interior.
            const controlText = document.createElement("div");
            controlText.style.color = "rgb(25,25,25)";
            controlText.style.fontFamily = "Roboto,Arial,sans-serif";
            controlText.style.fontSize = "10px";
            controlText.style.lineHeight = "16px";
            controlText.style.paddingLeft = "2px";
            controlText.style.paddingRight = "2px";
            controlText.innerHTML = "X";
            controlUI.appendChild(controlText);
            // Setup the click event listeners: simply set the map to Chicago.
            controlUI.addEventListener("click", () => {
                clearDrawing();
            });
            controlUI.setAttribute("class", "reset-map-btn");
        }

        function initialize() {
            @php($default_location = \App\Models\BusinessSetting::where('key', 'default_location')->first())
            @php($default_location = $default_location->value ? json_decode($default_location->value, true) : 0)
            let myLatlng = {
                lat: {{ $default_location ? $default_location['lat'] : '23.757989' }},
                lng: {{ $default_location ? $default_location['lng'] : '90.360587' }}
            };


            let myOptions = {
                zoom: 13,
                center: myLatlng,
                mapId: mapId,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            }
            map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);

            // Polyline (not Polygon) during drawing — Polygon would auto-render
            // the closing edge + fill once it has 3 vertices, pre-empting the
            // user's "click first dot to close" gesture. (Google removed
            // DrawingManager as of Maps JS v3.65.)
            drawingPolyline = new google.maps.Polyline({
                map: map,
                editable: false,
                clickable: false,
                strokeColor: "#FF0000",
                strokeOpacity: 0.8,
                strokeWeight: 2,
            });
            drawingPolyline.setPath([]);
            const polylinePath = drawingPolyline.getPath();
            lastpolygon = drawingPolyline;

            google.maps.event.addListener(polylinePath, "set_at", updateCoordinates);
            google.maps.event.addListener(polylinePath, "insert_at", updateCoordinates);
            google.maps.event.addListener(polylinePath, "remove_at", updateCoordinates);

            google.maps.event.addListener(map, "click", function (event) {
                if (!drawingMode) return; // Hand tool active — just pan
                if (polygonClosed) return; // shape already closed
                polylinePath.push(event.latLng);
                updateCoordinates();
            });

            map.controls[google.maps.ControlPosition.LEFT_TOP].push(buildDrawingControl());
            setDrawingMode(true); // start in Shape (draw) mode, like the old DrawingManager


            //get current location block
            // infoWindow = new google.maps.InfoWindow();
            // Try HTML5 geolocation.
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const pos = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };
                        map.setCenter(pos);
                    });
            }

            const resetDiv = document.createElement("div")
            resetMap(resetDiv, lastpolygon);
            map.controls[google.maps.ControlPosition.RIGHT_TOP].push(resetDiv);

            // Create the search box and link it to the UI element.
            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
            // Bias the SearchBox results towards current map's viewport.
            map.addListener("bounds_changed", () => {
                searchBox.setBounds(map.getBounds());
            });
            let markers = [];
            // Listen for the event fired when the user selects a prediction and retrieve
            // more details for that place.
            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();

                if (places.length == 0) {
                    return;
                }

                // Clear out the old markers.
                // markers.forEach((marker) => {
                // marker.setMap(null);
                // });

                markers.forEach(m => m.map = null);
                markers = [];
                // For each place, get the icon, name and location.
                const bounds = new google.maps.LatLngBounds();
                places.forEach((place) => {
                    if (!place.geometry || !place.geometry.location) {
                        console.log("Returned place contains no geometry");
                        return;
                    }

                    // Create a marker for each place.
                    markers.push(
                        new AdvancedMarkerElement({
                            map,
                            title: place.name,
                            position: place.geometry.location,
                        })
                    );

                    if (place.geometry.viewport) {
                        // Only geocodes have viewport.
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });
                map.fitBounds(bounds);
            });
        }

        google.maps.event.addDomListener(window, 'load', initialize);

        function set_all_zones() {
            $.get({
                url: '{{ route('admin.zone.zoneCoordinates') }}',
                dataType: 'json',
                success: function(data) {

                    for (let i = 0; i < data.length; i++) {
                        polygons.push(new google.maps.Polygon({
                            paths: data[i],
                            strokeColor: "#FF0000",
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: "#FF0000",
                            fillOpacity: 0.1,
                            clickable: false, // let clicks pass through to the map for drawing
                        }));
                        polygons[i].setMap(map);
                    }

                },
            });
        }

        $(document).on('ready', function() {
            set_all_zones();
        });

        $('#reset_btn').click(function() {
            $('.tab-content').find('input:text').val('');
            clearDrawing();
        })

        $(document).on('ready', function() {

            $("#maximum_shipping_charge_status").on('change', function() {
                if ($("#maximum_shipping_charge_status").is(':checked')) {
                    $('#maximum_shipping_charge').removeAttr('readonly');
                } else {
                    $('#maximum_shipping_charge').attr('readonly', true);
                    $('#maximum_shipping_charge').val('Ex : 0');
                }
            });
            $("#max_cod_order_amount_status").on('change', function() {
                if ($("#max_cod_order_amount_status").is(':checked')) {
                    $('#max_cod_order_amount').removeAttr('readonly');
                } else {
                    $('#max_cod_order_amount').attr('readonly', true);
                    $('#max_cod_order_amount').val('Ex : 0');
                }
            });



        });

        $('#zone_form').on('submit', function(e) {
            if (!polygonClosed) {
                e.preventDefault();
                toastr.warning("{{ translate('Connect_the_last_dot_to_the_first_dot_to_close_the_polygon_before_saving') }}");
                return false;
            }
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{ route('admin.zone.store') }}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        $('.tab-content').find('input:text').val('');
                        $('input[name="name"]').val(null);
                        clearDrawing();
                        toastr.success(
                            "{{ translate('messages.New_Business_Zone_Created_Successfully!') }}", {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        $('#set-rows').html(data.view);
                        $('#itemCount').html(data.total);

                        // A new zone still needs its settings and its two setups,
                        // so its drawer is opened straight away - the drawer names
                        // what is missing and links to each setup form.
                        $('#set-rows')
                            .find('.zone-settings-trigger[data-zone="' + data.zone_id + '"]')
                            .first()
                            .trigger('click');
                    }
                },
                complete: function() {
                    $('#loading').hide();
                },
            });
        });
    </script>
@endpush
@push('script_2')
<script>
    "use strict";
    $(document).on('click', '.zone-setup-required', function (e) {
        // A label click also activates the checkbox inside it, which fires a
        // second click that bubbles back here and opens the modal twice.
        e.preventDefault();

        const missing = [];
        // Both setups are per zone, so the admin lands on the one they came from
        // rather than having to find it in the dropdown again.
        const zoneQuery = '?zone_id=' + encodeURIComponent($(this).data('zone'));

        // The lead line names only what is left to do, so an admin who has already
        // added one of the two is not told to add it again.
        const hasRule = $(this).data('has-rule') == 1;
        const hasEta = $(this).data('has-eta') == 1;

        $('#zone-setup-required-text').text(
            hasEta ? @json(translate('messages.This_zone_can_not_be_activated_until_it_can_price_a_delivery._Complete_the_setup_below,_then_switch_the_zone_on.'))
                : hasRule ? @json(translate('messages.This_zone_can_not_be_activated_until_it_can_quote_a_delivery_time._Complete_the_setup_below,_then_switch_the_zone_on.'))
                : @json(translate('messages.This_zone_can_not_be_activated_until_it_can_price_a_delivery_and_quote_a_time_for_it._Complete_the_setups_below,_then_switch_the_zone_on.'))
        );

        if ($(this).data('has-rule') != 1) {
            missing.push('<li><a class="text-info text-underline" href="{{ route('admin.delivery-rule.create') }}' + zoneQuery + '">'
                + @json(translate('messages.Add_Delivery_Charge_Setup')) + '</a></li>');
        }
        if ($(this).data('has-eta') != 1) {
            missing.push('<li><a class="text-info text-underline" href="{{ route('admin.eta-configuration.create') }}' + zoneQuery + '">'
                + @json(translate('messages.Add_ETA_Configuration')) + '</a></li>');
        }

        $('#zone-setup-required-list').html(missing.join(''));
        $('#zone-setup-required-modal').modal('show');
    });
</script>
@endpush
