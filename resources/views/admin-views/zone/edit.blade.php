@extends('layouts.admin.app')

@section('title',translate('Update_Zone'))


@section('content')

    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title text-capitalize"><i class="tio-edit"></i> {{translate('messages.Business_Zone_update')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
            <form action="{{route('admin.zone.update', $zone->id)}}" method="post" id="zone_form" class="shadow--card">
                    @csrf
                    <div class="row">
                        <div class="col-md-5">
                            <div class="zone-setup-instructions">
                                <div class="zone-setup-top">
                                    <h6 class="subtitle">{{translate('messages.instructions')}}</h6>
                                    <p>
                                        {{translate('messages.Create_&_connect_dots_in_a_specific_area_on_the_map_to_add_a_new_business_zone.')}}
                                    </p>
                                </div>
                                <div class="zone-setup-item">
                                    <div class="zone-setup-icon">
                                        <i class="tio-hand-draw"></i>
                                    </div>
                                    <div class="info">
                                        {{translate('messages.Use_this_‘Hand_Tool’_to_find_your_target_zone.')}}
                                    </div>
                                </div>
                                <div class="zone-setup-item">
                                    <div class="zone-setup-icon">
                                        <i class="tio-free-transform"></i>
                                    </div>
                                    <div class="info">
                                        {{translate('messages.Use this ‘Shape Tool’ to point out the areas and connect the dots. A minimum of 3 points/dots is required.')}}
                                    </div>
                                </div>
                                <div class="instructions-image mt-4">
                                    <img src={{dynamicAsset('assets/admin/img/instructions.gif')}} alt="instructions">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-7 zone-setup">
                            <div class="form-group">
                                @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                                @php($language = $language->value ?? null)
                                @php($default_lang = str_replace('_', '-', app()->getLocale()))
                                <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                <ul class="nav nav-tabs mb-4">
                                    <li class="nav-item">
                                        <a class="nav-link lang_link active"
                                        href="#"
                                        id="default-link">{{translate('messages.default')}}  <span class="form-label-secondary text-danger mt-2"
                                        data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ translate('Choose_your_preferred_language_&_set_your_zone_name.') }}"><img
                                        src="{{ dynamicAsset('assets/admin/img/info-circle.svg') }}"
                                        alt="{{ translate('messages.veg_non_veg') }}"></span></a>
                                    </li>
                                    @if($language)
                                        @foreach (json_decode($language) as $lang)
                                            <li class="nav-item">
                                                <a class="nav-link lang_link"
                                                    href="#"
                                                    id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                            </li>
                                        @endforeach
                                    @endif

                                    </ul>
                                </div>
                            </div>

                            <div class="form-group lang_form" id="default-form">
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label class="input-label" for="exampleFormControlInput1">{{ translate('messages.business_Zone_name')}} ({{ translate('messages.default') }})</label>
                                        <input type="text" name="name[]" class="form-control md-3" placeholder="{{translate('messages.Type_new_zone_name_here')}}" maxlength="191" value="{{$zone?->getRawOriginal('name')}}"   >
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.Zone_Display_Name')}} ({{ translate('messages.default') }})</label>
                                        <input type="text" name="display_name[]" class="form-control" placeholder="{{translate('messages.Zone_Display_Name')}}" maxlength="191" value="{{$zone?->getRawOriginal('display_name')}}"  >
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                            @if($language)
                            @foreach(json_decode($language) as $lang)
                            <?php
                                if($zone?->translations){
                                    $translate = [];
                                    foreach($zone['translations'] as $t)
                                    {
                                        if($t->locale == $lang && $t->key=="name"){
                                            $translate[$lang]['name'] = $t->value;
                                        }
                                        if($t->locale == $lang && $t->key=="display_name"){
                                            $translate[$lang]['display_name'] = $t->value;
                                        }
                                    }
                                }
                            ?>
                            <div class="form-group d-none lang_form" id="{{$lang}}-form">
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label class="input-label" for="exampleFormControlInput1">{{ translate('messages.business_Zone_name')}} ({{strtoupper($lang)}})</label>
                                        <input type="text" name="name[]" class="form-control mb-3" placeholder="{{translate('messages.Type_new_zone_name_here')}}" maxlength="191" value="{{$translate[$lang]['name']??''}}"  >
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.Zone_Display_Name')}} ({{strtoupper($lang)}})</label>
                                        <input type="text" name="display_name[]" class="form-control" placeholder="{{translate('messages.display_name')}}" maxlength="191" value="{{$translate[$lang]['display_name']??''}}"  >
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="lang[]" value="{{$lang}}">
                        @endforeach
                        @endif
                            <div class="form-group mb-3 initial-hidden">
                                <label class="input-label"
                                    for="exampleFormControlInput1">{{translate('messages.Coordinates')}}<span
                                    class="input-label-secondary" title="{{translate('messages.draw_your_zone_on_the_map')}}">{{translate('messages.draw_your_zone_on_the_map')}}</span></label>
                                    <textarea  type="text" name="coordinates"  id="coordinates" class="form-control">
                                        @foreach($area['coordinates'] as $key=>$coords)
                                        <?php if(count($area['coordinates']) != $key+1) {if($key != 0) echo(','); ?>({{$coords[1]}}, {{$coords[0]}})
                                        <?php } ?>
                                        @endforeach
                                    </textarea>
                            </div>

                            <div class="initial-60">
                                <input id="pac-input" class="controls rounded initial-8" title="{{translate('messages.search_your_location_here')}}" type="text" placeholder="{{translate('messages.search_here')}}"/>
                                <div id="map-canvas" class="h-100 m-0 p-0"></div>
                            </div>
                            <div class="btn--container mt-3 justify-content-end">
                                <button id="reset_btn" type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                                <button type="submit" class="btn btn--primary">{{translate('messages.update')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')
<script src="https://maps.googleapis.com/maps/api/js?v=3.61&key={{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}&libraries=places,marker"></script>
<script>
    "use strict";
    auto_grow();
    function auto_grow() {
        let element = document.getElementById("coordinates");
        element.style.height = "5px";
        element.style.height = (element.scrollHeight)+"px";
    }

    const mapId = "{{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}";
    const { AdvancedMarkerElement } = google.maps.marker;

    let map; // Global declaration of the map
    let lat_longs = new Array();
    let drawingPolyline = null; // polyline used while drawing (before the shape is closed)
    let drawingPolygon = null;  // editable polygon created after the shape is closed
    let polygonClosed = false;  // true once the user clicks the first dot to close
    let lastpolygon = null; // kept for backward compatibility with existing handlers
    let bounds = new google.maps.LatLngBounds();
    let polygons = [];
    let drawingMode = false; // true = Shape tool (draw), false = Hand tool (pan)
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
        let myLatlng = new google.maps.LatLng({{trim(explode(' ',$zone->center)[1], 'POINT()')}}, {{trim(explode(' ',$zone->center)[0], 'POINT()')}});
        let myOptions = {
            zoom: 13,
            center: myLatlng,
            mapId: mapId,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);

        const polygonCoords = [

            @foreach($area['coordinates'] as $coords)
            { lat: {{$coords[1]}}, lng: {{$coords[0]}} },
            @endforeach
        ];

        // Existing zone — shown read-only as a reference (blue).
        let existingZone = new google.maps.Polygon({
            map: map,
            paths: polygonCoords,
            editable: false,
            clickable: false,
            strokeColor: "#050df2",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: "#050df2",
            fillOpacity: 0.1,
        });

        existingZone.getPaths().forEach(function(path) {
            path.forEach(function(latlng) {
                bounds.extend(latlng);
                map.fitBounds(bounds);
            });
        });

        // New drawing surface — starts empty. Use the Shape Tool and click the map to
        // draw a fresh zone; it replaces the existing one on save.
        // Polyline (not Polygon) during drawing — Polygon would auto-render the closing
        // edge + fill once it has 3 vertices, pre-empting the user's "click first dot to
        // close" gesture. (Google removed DrawingManager as of Maps JS v3.65.)
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
        setDrawingMode(false); // start with Hand tool; existing zone is shown for reference

        const resetDiv = document.createElement("div");
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
                // Clear out the old markers (AdvancedMarkerElement uses .map, not setMap).
                markers.forEach((marker) => {
                marker.map = null;
                });
                markers = [];
                // For each place, get the name and location.
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

    function set_all_zones()
    {
        $.get({
            url: '{{route('admin.zone.zoneCoordinates')}}/{{$zone->id}}',
            dataType: 'json',
            success: function (data) {
                for(let i=0; i<data.length;i++)
                {
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
    $(document).on('ready', function(){
        set_all_zones();

        $("#zone_form").on('keydown', function(e){
            if (e.keyCode === 13) {
                e.preventDefault();
            }
        });

        // Only block submit if the user started a NEW shape but didn't close it;
        // submitting with no new drawing keeps the existing zone coordinates.
        $("#zone_form").on('submit', function (e) {
            const startedDrawing = drawingPolyline && drawingPolyline.getPath().getLength() > 0;
            if (startedDrawing && !polygonClosed) {
                e.preventDefault();
                toastr.warning("{{ translate('Connect_the_last_dot_to_the_first_dot_to_close_the_polygon_before_saving') }}");
                return false;
            }
        });
    });


    $('#reset_btn').click(function() {
        $('#zone_form input[type="text"]').val('');
        clearDrawing();
    });

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
</script>
@endpush
