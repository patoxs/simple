<div class="tab-pane fade pt-3" id="tab3" role="tabpanel" aria-labelledby="tab3-tab">
    <script type="text/javascript">
        $(document).ready(function () {
            $("input[name=acceso_modo]").change(function () {
                if (this.value == "grupos_usuarios") {
                    $("#optionalGruposUsuarios").removeClass("hide");
                } else {
                    $("#optionalGruposUsuarios").addClass("hide");
                }
            });

            $(".reglas").blur(function () {
                var input = this;
                $.ajax({
                    url: '<?=url('backend/configuracion/ajax_get_validacion_reglas')?>',
                    data: {
                        rule: $(input).val(),
                        proceso_id: <?= $proceso_id ?>
                    },
                    dataType: "json",
                    cache: false,
                    success: function (data) {
                        console.log("$data.mensaje: " + data.mensaje);
                        if (data.code == 200) {
                            $(input).parent().find(".message").html(data.mensaje);
                            $(input).parent().find(".message").show();
                        }
                    }
                });
            }).focus(function () {
                $(this).parent().find(".message").hide();
            });
        });
    </script>
    <label>Nivel de Acceso
        <a onclick="ga('cuenta.send', 'event', 'Mejora UX', 'Ayuda_pestana_usuario');" href="/ayuda/simple/backend/modelamiento-del-proceso/disenador.html#pestana_usuarios"
           target="_blank">
            <i class="material-icons">help</i>
        </a>
    </label>
    <div class="form-check">
        <input class="form-check-input" id="acceso_modo_publico" type="radio" name="acceso_modo"
               value="publico" <?= $tarea->acceso_modo == 'publico' ? 'checked' : '' ?> />
        <label for="acceso_modo_publico" class="form-check-label">Cualquier persona puede acceder</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" id="acceso_modo_registrados" type="radio" name="acceso_modo"
               value="registrados" <?= $tarea->acceso_modo == 'registrados' ? 'checked' : '' ?> />
        <label for="acceso_modo_registrados" class="form-check-label">Sólo los usuarios registrados</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" id="acceso_modo_claveunica" type="radio" name="acceso_modo"
               value="claveunica" <?= $tarea->acceso_modo == 'claveunica' ? 'checked' : '' ?> />
        <label for="acceso_modo_claveunica" class="form-check-label">
            Sólo los usuarios registrados con ClaveUnica
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input" id="acceso_modo_anonimo" type="radio" name="acceso_modo"
               value="anonimo" <?= $tarea->acceso_modo == 'anonimo' ? 'checked' : '' ?> />
        <label for="acceso_modo_anonimo" class="form-check-label">
            An&oacute;nimo
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input" id="acceso_modo_grupos_usuarios" type="radio" name="acceso_modo"
               value="grupos_usuarios" <?= $tarea->acceso_modo == 'grupos_usuarios' ? 'checked' : '' ?> />
        <label for="acceso_modo_grupos_usuarios" class="form-check-label">
            Sólo los siguientes grupos de usuarios pueden acceder
        </label>
    </div>
    <div id="optionalGruposUsuarios"
         class="<?= $tarea->acceso_modo == 'grupos_usuarios' ? '' : 'hide' ?>">
        <select id="selectGruposUsuarios" class="form-control" name="grupos_usuarios[]" style="width: 270px;" multiple>
            @foreach($tarea->Proceso->Cuenta->GruposUsuarios as $g)
                <option value="<?=$g->id?>" <?=in_array($g->id, explode(',', $tarea->grupos_usuarios)) ? 'selected' : ''?>><?=$g->nombre?></option>
            @endforeach
            @foreach(explode(',', $tarea->grupos_usuarios) as $g)
                @if(!is_numeric($g))
                    <option selected><?=$g?></option>
                @endif
            @endforeach
        </select>
        <div class="form-text text-muted">
            Puede incluir variables usando @@. Las variables deben contener el numero id del grupo de usuarios.
        </div>
    </div>
</div>