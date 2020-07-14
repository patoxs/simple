<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Editar Conexiones</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="formEditarConexion" class="ajaxForm" method="POST"
                  action="<?= url('backend/procesos/editar_conexiones_form/' . $conexiones[0]->TareaOrigen->id) ?>">
                <div class="validacion"></div>

                <label>Tipo
                    <?php if ($conexiones[0]->tipo == 'evaluacion') { ?>
                    <a href="/assets/ayuda/simple/backend/modelamiento-del-proceso/disenador.html#evaluacion"
                       target="_blank">
                        <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                    <?php } if ($conexiones[0]->tipo == 'paralelo') { ?>
                    <a href="/assets/ayuda/simple/backend/modelamiento-del-proceso/disenador.html#paralelo"
                       target="_blank">
                        <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                    <?php } if ($conexiones[0]->tipo == 'paralelo_evaluacion') { ?>
                    <a href="/assets/ayuda/simple/backend/modelamiento-del-proceso/disenador.html#paralelo_evaluacion"
                       target="_blank">
                        <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                    <?php } if ($conexiones[0]->tipo == 'union') { ?>
                    <a href="/assets/ayuda/simple/backend/modelamiento-del-proceso/disenador.html#pestana_union"
                       target="_blank">
                        <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                    <?php } ?>
                </label>
                <input type="text" class="form-control" value="<?= $conexiones[0]->tipo ?>" disabled/>

                <br/><br/>

                <?php if($conexiones[0]->tipo != 'secuencial'):?>
                <div>
                    <button class="btn btn-light botonNuevaConexion" type="button">
                        <i class="material-icons">add</i> Nueva
                    </button>
                </div>
                <?php endif ?>

                <table class="table mt-3">
                    <thead>
                    <tr>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Regla</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($conexiones as $key=>$conexion):?>
                    <tr>
                        <td><?= $conexion->TareaOrigen->nombre ?></td>
                        <td>
                            <select name="conexiones[<?=$key?>][tarea_id_destino]" class="form-control">
                                <option value="">Fin del proceso</option>
                                <?php foreach($conexion->TareaOrigen->Proceso->Tareas as $t):?>
                                <option value="<?=$t->id?>" <?=$t->id == $conexion->tarea_id_destino ? 'selected' : ''?>><?=$t->nombre?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <?php if ($conexion->tipo == 'evaluacion' || $conexion->tipo == 'paralelo_evaluacion'): ?>
                            <div class="form-inline">
                                <input type="text" class="reglas form-control col-10"
                                       name="conexiones[<?=$key?>][regla]"
                                       value="<?= htmlspecialchars($conexion->regla) ?>"
                                       title="Los nombres de campos escribalos anteponiendo @@. Ej: @@edad >= 18"/>
                                <div class="btn-group asistencia col-2"
                                     style="display: inline-block; vertical-align: top;">
                                    <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                        <i class="icon-th-list"></i><span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <?php foreach ($conexion->TareaOrigen->Proceso->getCampos() as $c): ?>
                                        <li><a href="#">@@<?= $c->nombre ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            <?php else: ?>
                            <p>N/A</p>
                            <?php endif; ?>
                            <p class="message" style="color: red; display: block;"></p>
                        </td>
                        <td>
                            <input type="hidden" name="conexiones[<?=$key?>][tipo]" value="<?=$conexion->tipo?>"/>
                            <button class="btn btn-light botonEliminarConexion" type="button">
                                <i class="material-icons">clear</i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        </div>
        <div class="modal-footer">
            <a href="<?= url('backend/procesos/eliminar_conexiones/' . $conexiones[0]->TareaOrigen->id) ?>"
               class="btn btn-danger pull-left"
               onclick="return confirm('¿Esta seguro que desea eliminar esta conexión?')">Eliminar</a>
            <a href="#" data-dismiss="modal" class="btn btn-light">Cerrar</a>
            <a href="#" onclick="javascript:$('#formEditarConexion').submit();return false;"
               class="btn btn-primary">Guardar</a>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("[title]").tooltip();

        // Funcionalidad del llenado de nombre usando el boton de asistencia
        $("#formEditarConexion").on("click", ".asistencia .dropdown-menu a", function () {
            var nombre = $(this).text();
            $(this).closest("td").find(":input").val(nombre);
        });

        $("#formEditarConexion").on("click", "button.botonNuevaConexion", function () {
            var html = $("#formEditarConexion table tbody tr:last").clone();
            $("#formEditarConexion table tbody").append(html);
            $("#formEditarConexion table tbody tr").each(function (i, row) {
                $(row).find("[name]").each(function (j, el) {
                    el.name = el.name.replace(/\[\w+\]/, "[" + i + "]");
                });
            });
        });

        $("#formEditarConexion").on("click", "button.botonEliminarConexion", function () {
            //solo puede eliminar si hay mas de 1 <tr> en la tabla, de lo contrario no podra crear una nueva
            if ($(this).parent().parent().parent().find("tr").length > 1) {
                $(this).closest("tr").remove();
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
                success: function (data) {
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