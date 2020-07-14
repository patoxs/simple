<div class="tab-datos-expuestos tab-pane fade pt-3" id="tab8" role="tabpanel" aria-labelledby="tab8-tab">
    <script>
        function selectAll() {
            $("#seleccionados").find("*").prop("selected", true);
        }

        function SelectAllFunction() {
            if ($("input[name=SelectAll]:checked").val()) {
                $(".SelectAll").prop('checked', true);
            } else {
                $(".SelectAll").prop('checked', false);
            }
        }

        function seleccionarForm(id) {
            if ($("input[name=" + id + "]:checked").val()) {
                $("." + id).prop('checked', true);
            } else {
                $("." + id).prop('checked', false);
            }
        }
    </script>

    <div class="row">
        <div class="col-6">
            <h5>Variables de formulario
                <a onclick="ga('cuenta.send', 'event', 'Mejora UX', 'Ayuda_pestana_datos');"href="/ayuda/simple/backend/modelamiento-del-proceso/disenador.html#pestana_datos"
                   target="_blank">
                    <i class="material-icons">help</i>
                </a>
            </h5>
        </div>
        <div class="col-6"><h5>Variables de proceso</h5></div>
    </div>
    <div class="row">
        <div class="col-6"
             style="overflow-y: auto; height:280px;border:.5px solid;border-radius: 5px;border-color:#DDDDDD;">
            @php
                $formularios = array();
                $nameform = array();
                foreach ($variablesFormularios as $key => $valuesAry) {
                    $var = $valuesAry['nombre_formulario'];
                    if (!in_array($var, $nameform)) {
                        $nameform[] = $var;
                    }
                    $formIndex = array_search($var, $nameform);
                    $formularios[$formIndex][] = $valuesAry;
                }
            @endphp

            @foreach ($formularios as $key => $res)
                @php
                    $id = $key;
                @endphp
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" onclick="seleccionarForm({{$id}})"
                           name="{{$id}}" id="{{$id}}"
                           value="{{$id}}"/>
                    <label for="{{$id}}" class="form-check-label"><b>{{$res[0]['nombre_formulario']}}</b></label>
                </div>
                @foreach ($res as $d)
                    <div class="form-check ml-3">
                        <input class="form-check-input {{$id}}" type="checkbox"
                               name="varForm[]"
                               id="{{$d['variable_id']}}"
                               value="{{$d['variable_id']}}">
                        <label for="{{$d['variable_id']}}" class="form-check-label">{{$d['nom_variables']}}</label>
                    </div>
                    @if ($d['exponer_campo'] == 1)
                        <script type="text/javascript">
                            $("#" +{{$d['variable_id']}}).prop('checked', true);
                        </script>
                    @endif
                @endforeach
            @endforeach
        </div>
        <div class="col-6"
             style="overflow-y: auto;height:280px;border: 0.5px solid;border-radius: 5px;border-color:#DDDDDD;">
            @php
                $count = count($variablesProcesos);
            @endphp
            @if($count > 0)
                <div class="form-check">
                    <input class="form-check-input" id="checkbox_select_all" type="checkbox"
                           onclick="SelectAllFunction();" name="SelectAll"
                           value="0">
                    <label for="checkbox_select_all" class="form-check-label"><b>All</b></label>
                </div>
                @foreach ($variablesProcesos as $res)
                    @php
                        $variables = json_decode($res['extra']);
                        $variables = get_object_vars($variables);
                    @endphp
                    <div class="form-check ml-3">
                        <input class="form-check-input SelectAll" type="checkbox"
                               name="varPro[]"
                               id="var{{$res['variable_id']}}"
                               value="{{$res['variable_id']}}">
                        <label for="var{{$res['variable_id']}}"
                               class="form-check-label">{{$variables['variable']}}</label>
                    </div>
                    @if($res['exponer_variable'] == 1)
                        <script type="text/javascript">
                            $("#var" +{{$res['variable_id']}}).prop('checked', true);
                        </script>
                    @endif
                @endforeach
            @endif

        </div>
    </div>
</div>