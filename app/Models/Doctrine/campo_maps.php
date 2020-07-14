<?php
require_once('campo.php');

use Illuminate\Support\Facades\Log;
use App\Helpers\Doctrine;

class CampoMaps extends Campo
{
    public $requiere_datos = false;
    public $datos_mapa = true;

    protected function display($modo, $dato, $etapa_id = false)
    {

        if ($etapa_id) {
            $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
            $regla = new Regla($this->valor_default);
            $valor_default = $regla->getExpresionParaOutput($etapa->id);
        } else {
            $valor_default = $this->valor_default;
        }

        $columns = $this->extra;

        Log::debug('columns: ' . json_encode($columns));
        Log::debug('dato: ' . json_encode($dato));

        $display = '<label class="control-label" for="' . $this->id . '">' . $this->etiqueta . (!in_array('required', $this->validacion) ? ' (Opcional)' : '') . '</label>';
        $display .= '<div class="controls">';

        if ($modo == 'edicion') {
            $display .= '<input id="' . $this->id . '" type="text" class="form-control col-6" name="' . $this->nombre . '" value="' . ($dato ? htmlspecialchars($dato->valor->address) : "") . '" data-modo="' . $modo . '" style="position: absolute; z-index: 2; margin: 10px;" />';
        }

        $display .= '<div class="map" id="map_' . $this->id . '" data-id="' . $this->id . '" style="width=590px;"></div>';
        $display .= '<input type="hidden" id="maph_' . $this->id . '" name="' . $this->nombre . '" value=\'' . ($dato ? json_encode($dato->valor) : $valor_default) . '\' />';

        if ($this->ayuda)
            $display .= '<span class="form-text text-muted">' . $this->ayuda . '</span>';
        $display .= '</div>';

        $display .= '

            <script>
                $(() => {
                    initMap_' . $this->id . '();
                });

                var markers_' . $this->id . ' = [];

                function initMap_' . $this->id . '() {

                    var bounds = new google.maps.LatLngBounds();
                    var geocoder = new google.maps.Geocoder;
                    var infowindow = new google.maps.InfoWindow;
                    var marker = null;
                    var objLocation;

                    var map_' . $this->id . ' = new google.maps.Map(document.getElementById("map_' . $this->id . '"), {
                        zoom: 17,
                        maxZoom: 17,
                        center: {lat: -33.4429046, lng: -70.6560586},
                        mapTypeControl: false,
                        streetViewControl: false,
                    });

                    if ($("#maph_' . $this->id . '").val().length) {

                        var obj = JSON.parse($("#maph_' . $this->id . '").val());

                        marker = new google.maps.Marker({
                            mapTypeControl: false,
                            anchorPoint: new google.maps.Point(0, -29),
                            animation: google.maps.Animation.DROP,
                            draggable: true,
                            position: {lat: obj.latitude, lng: obj.longitude},
                            map: map_' . $this->id . ',
                            title: obj.address
                        });
                        markers_' . $this->id . '.push(marker);

                        var infowindow = new google.maps.InfoWindow();

                        infowindow.setContent("<div><strong>" + obj.address + "</strong><br>");
                        infowindow.open(map_' . $this->id . ', marker);

                        bounds.extend(marker.position);
                        map_' . $this->id . '.fitBounds(bounds);
                    }

                    if ($("#' . $this->id . '").length > 0) {
                        // This event listener will call addMarker() when the map is clicked.
                        map_' . $this->id . '.addListener("click", function(event) {

                            marker = new google.maps.Marker({
                                anchorPoint: new google.maps.Point(0, -29),
                                animation: google.maps.Animation.DROP,
                                draggable: true,
                                title: "' . $this->nombre . '",
                                position: event.latLng,
                                map: map_' . $this->id . '
                            });

                            geocoder.geocode({"location": event.latLng}, function(results, status) {
                                if (status === "OK") {
                                    if (results[0]) {
                                        infowindow.setContent(results[0].formatted_address);
                                        infowindow.open(map_' . $this->id . ', marker);
                                        objLocation = {"latitude": event.latLng.lat() , "longitude": event.latLng.lng() , "address": results[0].formatted_address};
                                        $("#maph_' . $this->id . '").val(JSON.stringify(objLocation));
                                    }
                                } else {
                                    objLocation = {"latitude": event.latLng.lat() , "longitude": event.latLng.lng() , "address": "Sin información"};
                                    $("#maph_' . $this->id . '").val(JSON.stringify(objLocation));
                                }
                            });

                            deleteMarkers_' . $this->id . '();
                            markers_' . $this->id . '.push(marker);
                            map_' . $this->id . '.panTo(marker.getPosition());
                        });
                    }
        ';

        if ($modo == 'edicion') {
            $display .= '
            var pac_input = document.getElementById(' . $this->id . ');

            (function pacSelectFirst(input) {
                // store the original event binding function
                var _addEventListener = (input.addEventListener) ? input.addEventListener : input.attachEvent;

                function addEventListenerWrapper(type, listener) {
                    // Simulate a down arrow keypress on hitting return when no pac suggestion is selected,
                    // and then trigger the original listener.
                    if (type == "keydown") {
                        var orig_listener = listener;
                        listener = function(event) {
                            var suggestion_selected = $(".pac-item-selected").length > 0;
                            if (event.which == 13 && !suggestion_selected) {
                                event.preventDefault();
                                var simulated_downarrow = $.Event("keydown", {
                                    keyCode: 40,
                                    which: 40
                                });
                                orig_listener.apply(input, [simulated_downarrow]);
                            }
                            orig_listener.apply(input, [event]);
                        };
                    }
                    _addEventListener.apply(input, [type, listener]);
                }

                input.addEventListener = addEventListenerWrapper;
                input.attachEvent = addEventListenerWrapper;

                var autocomplete = new google.maps.places.Autocomplete(input);
                return false;
            })(pac_input);
            ';
        }

        if ($columns) {
            Log::debug('columns: ' . json_encode($columns));
            $i = 0;
            foreach ($columns as $key => $c) {

                if (strlen($c->latitude) > 0 && strlen($c->longitude) > 0) {
                    $display .= '

                        marker = new google.maps.Marker({
                            mapTypeControl: false,
                            anchorPoint: new google.maps.Point(0, -29),
                            animation: google.maps.Animation.DROP,
                            draggable: true,
                            position: {lat: ' . $c->latitude . ', lng: ' . $c->longitude . '},
                            map: map_' . $this->id . ',
                            title: "' . $c->address . '"
                        });
                        markers_' . $this->id . '.push(marker);

                        var infowindow = new google.maps.InfoWindow();

                        infowindow.setContent("<div><strong>' . $c->address . '</strong><br>");
                        infowindow.open(map_' . $this->id . ', marker);

                        bounds.extend(marker.position);
                    ';
                    $i++;
                }
            }
        }

        $display .= '

                    if ($("#' . $this->id . '").length > 0) {
                        new AutocompleteDirectionsHandler_' . $this->id . '(map_' . $this->id . ');
                    }

                    if ($("#' . $this->id . '").length > 0 && markers_' . $this->id . '.length == 0) {
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(function(objPosition) {

                                map_' . $this->id . '.panTo({lat: objPosition.coords.latitude, lng: objPosition.coords.longitude});
                                bounds.extend({lat: objPosition.coords.latitude, lng: objPosition.coords.longitude});
                                map_' . $this->id . '.fitBounds(bounds);

                            }, function(objPositionError) {
                                myLatLng = {lat: -25.363, lng: 131.044};
                            }, {
                                maximumAge: 75000,
                                timeout: 15000
                            });
                        }
                    }
                    map_' . $this->id . '.fitBounds(bounds);
                }

                function AutocompleteDirectionsHandler_' . $this->id . '(map_' . $this->id . ') {
                    this.map_' . $this->id . ' = map_' . $this->id . ';
                    var originInput = document.getElementById("' . $this->id . '");
                    this.directionsService = new google.maps.DirectionsService;
                    this.directionsDisplay = new google.maps.DirectionsRenderer;
                    this.directionsDisplay.setMap(map_' . $this->id . ');

                    var autocomplete = new google.maps.places.Autocomplete(originInput);
                    autocomplete.bindTo("bounds", map_' . $this->id . ');

                    this.map_' . $this->id . '.controls[google.maps.ControlPosition.TOP_LEFT].push(originInput);

                    autocomplete.addListener("place_changed", function() {
                        var place = autocomplete.getPlace();
                        if (!place.geometry) {
                            // User entered the name of a Place that was not suggested and
                            // pressed the Enter key, or the Place Details request failed.
                            window.alert("No existe información para la dirección ingresada: \'" + place.name + "\'");
                            return;
                        }

                        // If the place has a geometry, then present it on a map.
                        if (place.geometry.viewport) {
                            var objLocation = {"latitude": place.geometry.location.lat() , "longitude": place.geometry.location.lng() , "address":  $("#' . $this->id . '").val()};
                            $("#maph_' . $this->id . '").val(JSON.stringify(objLocation));
                            map_' . $this->id . '.fitBounds(place.geometry.viewport);
                        } else {
                            map_' . $this->id . '.setCenter(place.geometry.location);
                            map_' . $this->id . '.setZoom(17);  // Why 17? Because it looks good.
                        }

                        deleteMarkers_' . $this->id . '();

                        var marker = new google.maps.Marker({
                          map: map_' . $this->id . ',
                          animation: google.maps.Animation.DROP,
                          position: place.geometry.location,
                          anchorPoint: new google.maps.Point(0, -29)
                        });
                        markers_' . $this->id . '.push(marker);

                        var infowindow = new google.maps.InfoWindow();
                        var address = "";
                        if (place.address_components) {
                            address = [
                                (place.address_components[0] && place.address_components[0].short_name || ""),
                                (place.address_components[1] && place.address_components[1].short_name || ""),
                                (place.address_components[2] && place.address_components[2].short_name || "")
                            ].join(" ");
                        }

                        infowindow.setContent("<div><strong>" + place.name + "</strong><br>" + address);
                        infowindow.open(map_' . $this->id . ', marker);
                    });
                }

                function deleteMarkers_' . $this->id . '() {
                    for (var i = 0; i < markers_' . $this->id . '.length; i++) {
                        markers_' . $this->id . '[i].setMap(null);
                    }
                    markers_' . $this->id . ' = [];
                }
            </script>
        ';

        return $display;
    }

    public function backendExtraFields()
    {
        $columns = array();
        if (isset($this->extra))
            $columns = $this->extra;
        $output = '
            <div class="columnas" ' . ($this->readonly == 0 ? 'style="display: none;"' : '') . '>
                 <script type="text/javascript">
                    $(document).ready(function() {
                        $("#formEditarCampo .columnas .nuevo").click(function() {
                            var pos=$("#formEditarCampo .columnas table tbody tr").lenght;
                            var html="<tr>";
                            html+="<td><input type=\'text\' class=\'form-control\' name=\'extra[" + pos + "][latitude]\' style=\'width:100px;\' /></td>";
                            html+="<td><input type=\'text\' class=\'form-control\' name=\'extra[" + pos + "][longitude]\' style=\'width:100px;\' /></td>";
                            html+="<td><input type=\'text\' class=\'form-control\' name=\'extra[" + pos + "][address]\' style=\'width:140px;\' /></td>";
                            html+="<td><button type=\'button\' class=\'btn btn-light eliminar\'><i class=\'material-icons\'>close</i> Eliminar</button></td>";
                            html+="</tr>";
            
                            $("#formEditarCampo .columnas table tbody").append(html);
                        });
            
                        $("#formEditarCampo .columnas").on("click", ".eliminar", function() {
                            $(this).closest("tr").remove();
                        });
                    });
                </script>
                <h4 class="mt-3">Columnas</h4>
                <button class="btn btn-light nuevo" type="button"><i class="material-icons">add</i> Nuevo</button>
                <table class="table mt-3">
                    <thead>
                        <tr>
                            <th>Latitud</th>
                            <th>Longitud</th>
                            <th>Dirección</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    ';

        if ($columns) {
            $i = 0;
            foreach ($columns as $key => $c) {
                $output .= '
                <tr>
                    <td><input type="text" class="form-control" name="extra[' . $i . '][latitude]" style="width:100px;" value="' . $c->latitude . '" /></td>
                    <td><input type="text" class="form-control" name="extra[' . $i . '][longitude]" style="width:100px;" value="' . $c->longitude . '" /></td>
                    <td><input type="text" class="form-control" name="extra[' . $i . '][address]" style="width:140px;" value="' . $c->address . '" /></td>
                    <td><button type="button" class="btn btn-light eliminar"><i class="material-icons">close</i>Eliminar</button></td>
                </tr>
                ';
                $i++;
            }
        }

        $output .= '
        </tbody>
        </table>
        </div>
        ';

        return $output;
    }

}