<div class="tab-pasos tab-pane" id="tab4" role="tabpanel" aria-labelledby="tab4-tab">
    <table class="table">
        <thead>
        <tr class="form-agregar-paso">
            <td></td>
            <td>
                <select class="pasoFormulario form-control">
                    @foreach ($formularios as $f)
                        @if(!is_null($f->descripcion))
                            <option value="<?= $f->id ?>"><?= $f->nombre ." | ".$f->descripcion ?></option>
                        @else
                        <option value="<?= $f->id ?>"><?= $f->nombre ?></option>
                        @endif
                    @endforeach
                </select>
            </td>
            <td>
                <div class="input-group mb-2 mr-sm-2">
                    <input class="pasoRegla reglas form-control" type="text"
                           placeholder="Escribir regla condición aquí"/>
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <a onclick="ga('cuenta.send', 'event', 'Mejora UX', 'Ayuda_reglas_de_negocio_and_validacion');" href="/ayuda/simple/backend/modelamiento-del-proceso/reglas-de-negocio-y-reglas-de-validacion.html"
                               target="_blank">
                                <i class="material-icons align-middle">help</i>
                            </a>
                            <p class="message" style="color: red; display: block;"></p>
                        </div>
                    </div>
                </div>
            </td>
            <td>
                <select class="pasoModo form-control">
                    <option value="edicion">Edición</option>
                    <option value="visualizacion">Visualización</option>
                </select>
            </td>
            <td>
                <button type="button" class="btn btn-light" title="Agregar"><i class="material-icons">add</i></button>
            </td>
        </tr>
        <tr>
            <th>#</th>
            <th>Formulario</th>
            <th>Condición</th>
            <th>Modo</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($tarea->Pasos as $key => $p)
            <tr>
                <td><?= $key + 1 ?></td>
                @if(!is_null($p->Formulario->descripcion))
                <td><a title="Editar" target="_blank"
                       href="<?= url('backend/formularios/editar/' . $p->Formulario->id) ?>"><?= $p->Formulario->nombre ." | ".$p->Formulario->descripcion ?></a>
                </td>
                @else
                <td><a title="Editar" target="_blank"
                       href="<?= url('backend/formularios/editar/' . $p->Formulario->id) ?>"><?= $p->Formulario->nombre ?></a>
                </td>
                @endif
                <td><input type="text" class="form-control" name="pasos[<?= $key + 1 ?>][regla]" value="<?= $p->regla ?>"/></td>
                <td><?= $p->modo ?></td>
                <td>
                    <input type="hidden" name="pasos[<?= $key + 1 ?>][id]"
                           value="<?= $p->id ?>"/>
                    <input type="hidden" name="pasos[<?= $key + 1 ?>][formulario_id]"
                           value="<?= $p->formulario_id ?>"/>
                    <input type="hidden" name="pasos[<?= $key + 1 ?>][modo]"
                           value="<?= $p->modo ?>"/>
                    <a class="delete" title="Eliminar" href="#"><i class="material-icons">close</i></a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="form-check">
        <input class="form-check-input" id="paso_confirmacion" type="checkbox" name="paso_confirmacion"
               value="1" <?=$tarea->paso_confirmacion ? 'checked' : ''?> >
        <label for="paso_confirmacion" class="form-check-label">
            Incluir último paso de confirmación antes de avanzar la tarea
            <a onclick="ga('cuenta.send', 'event', 'Mejora UX', 'Ayuda_pestana_pasos');" href="/ayuda/simple/backend/modelamiento-del-proceso/disenador.html#pestana_pasos"
               target="_blank">
                <i class="material-icons">help</i>
            </a>
        </label>

        <script>
            $(document).ready(function () {
                $("input[name=paso_confirmacion]").change(function () {
                    if ($("input[name=paso_confirmacion]:checked").length > 0) {
                        $("#activacionTextos").show();
                    }else{
                        $("#activacionTextos").hide();
                    }
                }).change();

                $('#paso_confirmacion_contenido').each(function(){
                    $(this).val($(this).val().trim());
                });
            });
        </script>

    </div>
    <div id="activacionTextos" class="hide" style="margin-left: 20px;">
        <label>Título (opcional) </label>
        <input class="form-control" rel="tooltip"
               title="Deje el campo en blanco para no considerar un título"
               type="text"
               name="paso_confirmacion_titulo"
               value="<?= $tarea->paso_confirmacion_titulo ?>"/>
        <label>Contenido (opcional)</label>
        <textarea class="form-control" id="paso_confirmacion_contenido" name="paso_confirmacion_contenido" rel="tooltip" title="Deje el campo en blanco para no considerar el texto del botón">
        <?=$tarea->paso_confirmacion_contenido ?>
        </textarea>
        <label>Texto del botón de confirmación (opcional)</label>
        <input class="form-control" rel="tooltip"
               title="Deje el campo en blanco para no considerar el texto del botón"
               type="text"
               name="paso_confirmacion_texto_boton_final"
               value="<?= $tarea->paso_confirmacion_texto_boton_final ?>"/>
    </div>
</div>