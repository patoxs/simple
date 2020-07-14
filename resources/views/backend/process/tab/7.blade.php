<div class="tab-eventos-externos tab-pane fade pt-3" id="tab7" role="tabpanel" aria-labelledby="tab7-tab">
    <script type="text/javascript">
        $(document).ready(function () {
            $("input[name=almacenar_usuario]").click(function () {
                if (this.checked)
                    $("#optionalAlmacenarUsuario").removeClass("hide");
                else
                    $("#optionalAlmacenarUsuario").addClass("hide");
            });

            $("input[name=externa]").click(function () {
                if (this.checked)
                    $("#optionalTareaExterna").removeClass("hide");
                else
                    $("#optionalTareaExterna").addClass("hide");
            });
        });
    </script>
    <div class="form-check">
        <input class="form-check-input" id="almacenar_usuario" type="checkbox" name="almacenar_usuario"
               value="1" <?= $tarea->almacenar_usuario ? 'checked' : '' ?> />
        <label for="almacenar_usuario" class="form-check-label">
            ¿Almacenar el identificador del usuario que lleva a cabo esta tarea?
            <a onclick="ga('cuenta.send','event','Mejora UX', 'Ayuda_pestana_otros');"href="/ayuda/simple/backend/modelamiento-del-proceso/disenador.html#pestana_otros"
               target="_blank">
                <i class="material-icons">help</i>
            </a>
        </label>
    </div>
    <div id="optionalAlmacenarUsuario" class="<?= $tarea->almacenar_usuario ? '' : 'hide' ?>">
        <div class="col-auto">
            <label for="inlineFormInputGroup">Variable</label>
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <div class="input-group-text">@@</div>
                </div>
                <input class="form-control col-4" type="text" name="almacenar_usuario_variable"
                       value="<?= $tarea->almacenar_usuario_variable ?>"/>
            </div>
        </div>
    </div>

    <div class="form-check">
        <input class="form-check-input" id="checkbox_externa" type="checkbox" name="externa"
               value="1" <?= $tarea->externa ? 'checked' : '' ?> />
        <label for="checkbox_externa" class="form-check-label">¿Tarea externa?</label>
    </div>
    <div id="optionalTareaExterna" class="<?= $tarea->externa ? '' : 'hide' ?>">
        <table class="table">
            <thead>
            <tr class="form-agregar-evento-externo">
                <td>
                    <input class="eventoExterno form-control" id="nombre" type="text"
                           placeholder="Nombre"/>
                </td>
                <td>
                    <select class="eventoSentido form-control">
                        <option value="Ninguno">Ninguno</option>
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                    </select>
                </td>
                <td>
                    <input class="form-control" id="url" type="text" placeholder="URL"/>
                </td>
                <td>
                    <textarea class="form-control" name="mensaje" id="mensaje"
                              placeholder="Mensaje"></textarea>
                </td>
                <td>
                    <input class="form-control" id="regla" name="regla" type="text"
                           placeholder="Condición"/>
                </td>
                <td>
                    <input class="form-control" id="opciones" name="opciones" type="text"
                           placeholder="Opciones"/>
                </td>
                <td>
                    <button type="button" class="btn btn-light" title="Agregar">
                        <i class="material-icons">add</i>
                    </button>
                </td>
            </tr>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Metodo</th>
                <th>URL</th>
                <th>Mensaje</th>
                <th>Condición</th>
                <th>Opciones</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($tarea->EventosExternos as $key => $p)
                <tr>
                    <td><?= $key + 1 ?></td>
                    <td><?= $p->nombre ?></td>
                    <td><?= is_null($p->metodo) ? 'Ninguno' : $p->metodo ?></td>
                    <td><?= $p->url ?></td>
                    <td><?= $p->mensaje ?></td>
                    <td><?= $p->regla ?></td>
                    <td><?= $p->opciones ?></td>
                    <td>
                        <input type="hidden" name="eventos_externos[<?= $key + 1 ?>][id]"
                               value="<?= $p->id ?>"/>
                        <input type="hidden" name="eventos_externos[<?= $key + 1 ?>][nombre]"
                               value="<?= $p->nombre ?>"/>
                        <input type="hidden" name="eventos_externos[<?= $key + 1 ?>][metodo]"
                               value="<?= $p->metodo ?>"/>
                        <input type="hidden" name="eventos_externos[<?= $key + 1 ?>][url]"
                               value="<?= $p->url ?>"/>
                        <input type="hidden" name="eventos_externos[<?= $key + 1 ?>][mensaje]"
                               value="<?= htmlspecialchars($p->mensaje) ?>"/>
                        <input type="hidden" name="eventos_externos[<?= $key + 1 ?>][regla]"
                               value="<?= $p->regla ?>"/>
                        <input type="hidden" name="eventos_externos[<?= $key + 1 ?>][opciones]"
                               value="<?= $p->opciones ?>"/>
                        <a class="delete" title="Eliminar" href="#"><i class="material-icons">close</i></a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('.eventoSentido').change(function(evt){
            if(evt.target.value != 'ninguno'){
                // habilitar campo
                $('#url').prop('disabled', false);
                $('#mensaje').prop('disabled', false);
                $('#opciones').prop('disabled', false);
            }else{
                // deshabilitar campo
                $('#url').prop('disabled', true);
                $('#mensaje').prop('disabled', true);
                $('#opciones').prop('disabled', true);
            }
        });

        $('#url').prop('disabled', true);
        $('#mensaje').prop('disabled', true);
        $('#opciones').prop('disabled', true);
    });
</script>