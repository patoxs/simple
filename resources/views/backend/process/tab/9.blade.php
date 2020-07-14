<div class="tab-datos-expuestos tab-pane fade pt-3" id="tab9" role="tabpanel" aria-labelledby="tab9-tab">
    <div class="row-fluid">
        <div class="col-12"><h5>Cuentas a las que desea dar acceso para iniciar este proceso
                <a href="/ayuda/simple/backend/modelamiento-del-proceso/disenador.html#pestana_datos"
                   target="_blank">
                    <span class="glyphicon glyphicon-info-sign"></span>
                </a></h5>
        </div>
    </div>
    <div class="row-fluid">
        <div class="col-12"
             style="overflow-y: auto; height:280px;width:96%;border:.5px solid;border-radius: 5px;border-color:#DDDDDD;">
            <div class="campo control-group">
                <label class="control-label">Suscriptores:</label>
                <div class="controls">
                    @foreach ($cuentas as $cuenta)
                        <div class="form-check">
                            <label class="checkbox">
                                <input type="checkbox" name="cuentas_con_permiso[]"
                                    id="cuenta_{{$cuenta->id}}" value="{{$cuenta->id}}">
                                {{$cuenta->nombre}}
                            </label>
                        </div>

                        @foreach ($cuentas_con_permiso as $cuenta_permiso)
                            @if($cuenta_permiso["id"] == $cuenta->id)
                                <script type="text/javascript">
                                    $("#cuenta_" +{{$cuenta->id}}).prop('checked', true);
                                </script>
                                @break
                            @endif
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>