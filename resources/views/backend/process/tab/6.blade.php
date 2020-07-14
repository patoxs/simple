<div class="tab-pane fade pt-3" id="tab6"role="tabpanel" aria-labelledby="tab6-tab">
    <script>
        $(document).ready(function () {
            $("input[name=vencimiento]").change(function () {
                if (this.checked)
                    $("#vencimientoConfig").show();
                else
                    $("#vencimientoConfig").hide();
            }).change();

            $("select[name=vencimiento_unidad]").change(function () {
                if (this.value == "D")
                    $("#habilesConfig").show();
                else
                    $("#habilesConfig").hide();
            }).change();
        });
    </script>
    <div class="form-check">
        <input class="form-check-input" id="vencimiento" type="checkbox" name="vencimiento"
               value="1" <?=$tarea->vencimiento ? 'checked' : ''?> />
        <label for="vencimiento" class="form-check-label">
            ¿La etapa tiene vencimiento?
            <a onclick="ga('cuenta.send','event','Mejora UX', 'Ayuda_pestana_vencimiento');" href="/ayuda/simple/backend/modelamiento-del-proceso/disenador.html#pestana_vencimiento"
               target="_blank">
                <i class="material-icons">help</i>
            </a>
        </label>
    </div>

    <div id="vencimientoConfig" class="hide" style="margin-left: 20px;">
        <div class="form-inline form-group">
            La etapa se vencerá
            <input type="text" name="vencimiento_valor" class="ml-1 form-control col-3"
                   value="<?= $tarea->vencimiento_valor ? $tarea->vencimiento_valor : 5 ?>"/>
            <select name="vencimiento_unidad" class="ml-1 mr-1  form-control col-2">
                <option value="D" <?= $tarea->vencimiento_unidad == 'D' ? 'selected' : '' ?>>día/s
                </option>
                <option value="W" <?= $tarea->vencimiento_unidad == 'W' ? 'selected' : '' ?>>
                    semana/s
                </option>
                <option value="M" <?= $tarea->vencimiento_unidad == 'M' ? 'selected' : '' ?>>
                    mes/es
                </option>
                <option value="Y" <?= $tarea->vencimiento_unidad == 'Y' ? 'selected' : '' ?>>año/s
                </option>
            </select>
            después de completada la etapa anterior.
        </div>
        <div class="form-check" id="habilesConfig">
            <input class="form-check-input" id="vencimiento_habiles" type='checkbox'
                   name='vencimiento_habiles'
                   value='1' <?=$tarea->vencimiento_habiles ? 'checked' : ''?> />
            <label for="vencimiento_habiles" class="form-check-label">Considerar solo días habiles.</label>
        </div>
        <div class="form-row form-inline mb-2">
            <input class="form-check-input ml-1" id="vencimiento_notificar" type="checkbox" name="vencimiento_notificar"
                   value="1" <?=$tarea->vencimiento_notificar ? 'checked' : ''?> />
            Notificar cuando quede
            <input class="form-control ml-2 mr-2" type="text"
                   name="vencimiento_notificar_dias"
                   value="<?=$tarea->vencimiento_notificar_dias?>"/>
            día al siguiente correo:
        </div>
        <input class="form-control col-4" style="margin-left: 20px;" type="text" name="vencimiento_notificar_email"
               placeholder="ejemplo@mail.com" value="<?=$tarea->vencimiento_notificar_email?>"/>
        <div style="margin-left: 20px;" class="form-text text-muted">Tambien se pueden usar variables. Ej:
            @@email
        </div>
    </div>
</div>