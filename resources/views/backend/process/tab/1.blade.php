<div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">
    <div class="form-group mt-3">
        <label for="nombre">Nombre
            <a onclick="ga('cuenta.send', 'event', 'Mejora UX', 'Ayuda_pestana_definición');" href="/ayuda/simple/backend/modelamiento-del-proceso/disenador.html#pestana_definicion"
               target="_blank">
                <i class="material-icons">help</i>
            </a>
        </label>
        <input class="form-control" id="nombre" name="nombre" type="text" value="<?= $tarea->nombre ?>"/>
    </div>
    <br/>
    <label><strong>Activación</strong></label>
    <div class="row">
        <div class="col-6">
            <div class="form-check">
                <input class="form-check-input" name="inicial" id="inicial" value="1"
                       type="checkbox" <?= $tarea->inicial ? 'checked' : '' ?>>
                <label class="form-check-label" for="inicial">
                    Tarea Inicial
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" name="final" id="final" value="1"
                       type="checkbox" <?= $tarea->final ? 'checked' : '' ?>>
                <label class="form-check-label" for="final">
                    Tarea Final
                </label>
            </div>
        </div>
        <div class="col-6">
            <script>
                function MostrarExponer() {
                    if ($('#inicial').prop('checked')) {
                        $("#DivExponer").show();
                    } else {
                        $("#DivExponer").hide();
                        $("#exponer_tramite").prop('checked', false);
                    }
                }

                $(document).ready(function () {
                    MostrarExponer();
                    $("input[name=activacion]").change(function () {
                        if ($("input[name=activacion]:checked").val() == 'entre_fechas') {
                            $("#activacionEntreFechas").show();
                        } else {
                            $("#activacionEntreFechas").hide();
                        }
                    }).change();
                    $("#inicial").click(function () {
                        MostrarExponer();
                    });
                });
            </script>
            <div id="DivExponer" style="display:none;">
                <div class="form-check">
                    <input class="form-check-input" name="exponer_tramite" id="exponer_tramite" value="1"
                           type="checkbox" <?= $tarea->exponer_tramite ? 'checked' : '' ?>>
                    <label class="form-check-label" for="exponer_tramite">
                        Exponer trámite
                    </label>
                </div>
            </div>
            <div class="form-check">
                <input class="form-check-input" name="activacion" id="activacion" value="si"
                       type="radio" <?= $tarea->activacion == 'si' ? 'checked' : '' ?>>
                <label class="form-check-label" for="activacion">
                    Tarea activada
                </label>
            </div>

            <div class="form-check">
                <input class="form-check-input" name="activacion" id="activacion_entre_fechas" value="entre_fechas"
                       type="radio" <?= $tarea->activacion == 'entre_fechas' ? 'checked' : '' ?>>
                <label class="form-check-label" for="activacion_entre_fechas">
                    Tarea activa entre fechas
                </label>
            </div>

            <div id="activacionEntreFechas" class="hide" style="margin-left: 20px;">
                <label>Fecha inicial</label>
                <input class="datetimepicker form-control" rel="tooltip"
                       title="Deje el campo en blanco para no considerar una fecha inicial"
                       type="text"
                       name="activacion_inicio"
                       value="<?= $tarea->activacion_inicio ? \Carbon\Carbon::parse($tarea->activacion_inicio)->format('d-m-Y') : '' ?>"
                       placeholder="DD-MM-AAAA"/>
                <label>Fecha final</label>
                <input class="datetimepicker form-control" rel="tooltip"
                       title="Deje el campo en blanco para no considerar una fecha final"
                       type="text"
                       name="activacion_fin"
                       value="<?= $tarea->activacion_fin ? \Carbon\Carbon::parse($tarea->activacion_fin)->format('d-m-Y') : '' ?>"
                       placeholder="DD-MM-AAAA"/>
            </div>
            <div class="form-check">
                <input class="form-check-input" name="activacion" id="activacion_desactivada" value="no"
                       type="radio" <?= $tarea->activacion == 'no' ? 'checked' : '' ?>>
                <label class="form-check-label" for="activacion_desactivada">
                    Tarea desactivada
                </label>
            </div>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                <label for="previsualizacion">Información para previsualización</label>
                <textarea class="form-control" rows="5" id="previsualizacion"
                          name="previsualizacion"><?=$tarea->previsualizacion?></textarea>
            </div>
            <small class="form-text text-muted">
                Información que aparecera en la bandeja de entrada al pasar el cursor por
                encima.
            </small>
        </div>
    </div>
</div>
       