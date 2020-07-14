<div class="tab-eventos tab-pane fade" id="tab5" role="tabpanel" aria-labelledby="tab5-tab">
    <table class="table">
        <thead>
        <tr class="form-agregar-evento">
            <td></td>
            <td>
                <select class="eventoAccion form-control">
                    <?php foreach ($acciones as $f): ?>
                    <option value="<?= $f->id ?>"><?= $f->nombre ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <input class="eventoRegla form-control reglas" type="text"
                       placeholder="Escribir regla condición"/>
                <p class="message" style="color: red; display: block;"></p>
            </td>
            <td>
                <select class="eventoInstante form-control">
                    <option value="antes">Antes</option>
                    <option value="durante">Durante</option>
                    <option value="despues">Después</option>
                    </select>
            </td>
            <td>
                <input class="eventoCampoAsociado form-control col-md-10" 
                    type="text" placeholder="@@@boton">
                <p class="messageEventoAsociado" style="color: red; display: block;"></p>
            </td>
            <td>
                <select class="eventoPasoId form-control">
                    <option value="">Ejecutar Tarea</option>
                    <?php foreach ($tarea->Pasos as $p): ?>
                    <option value="<?=$p->id?>" title="<?=$p->Formulario->nombre?>">Ejecutar
                        Paso <?=$p->orden?></option>
                    <?php endforeach ?>
                    <?php foreach ($tarea->EventosExternos as $ee): ?>
                    <option value="<?=$ee->id?>" title="<?=$ee->nombre?>">Evento
                        Externo <?=$ee->nombre?></option>
                    <?php endforeach ?>
                </select>
            </td>
            <td>
                <button type="button" class="btn btn-light" title="Agregar">
                    <i class="material-icons">add</i>
                </button>
            </td>
        </tr>
        <tr>
            <th>#</th>
            <th>Acción</th>
            <th>Condición</th>
            <th>Instante</th>
            <th>Botón asíncrono(opcional)</th>
            <th>Momento</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($tarea->Eventos as $key => $p): ?>
        <tr>
            <td><?= $key + 1 ?></td>
            <td><a tyle="width:30px" title="Editar" target="_blank"
                   href="<?= url('backend/acciones/editar/' . $p->Accion->id) ?>"><?= $p->Accion->nombre ?></a>
            </td>
            <td><input type="text" class="form-control" name="eventos[<?= $key + 1 ?>][regla]" value="<?= $p->regla ?>"/></td>
            <td><select class="eventoInstante form-control" name="eventos[<?= $key + 1 ?>][instante]" style="width: 120px;">
                    <option value="antes" <?= $p->instante=='antes' ? 'selected' : '' ?> >Antes</option>
                    <option value="durante" <?= $p->instante=='durante' ? 'selected' : '' ?> >Durante</option>
                    <option value="despues" <?= $p->instante=='despues' ? 'selected' : '' ?>>Después</option>
                </select>
            </td>
            <td><input type="text" class="form-control" name="eventos[<?= $key + 1 ?>][campo_asociado]" value="<?= $p->campo_asociado ?>"/></td>
            <td>
                <select class="eventoPasoId form-control" name="eventos[<?= $key + 1 ?>][paso_id]" style="width: 160px;">
                    <?php if($p->paso_id): ?>
                        <?php foreach ($tarea->Pasos as $paso): ?>
                            <?php if($paso->id===$p->paso_id): ?>
                                <option selected value="<?=$paso->id?>" title="<?=$paso->Formulario->nombre?>">Ejecutar Paso <?=$paso->orden?></option>
                            <?php else: ?>
                                <option value="<?=$paso->id?>" title="<?=$paso->Formulario->nombre?>">Ejecutar Paso <?=$paso->orden?></option>
                            <?php endif; ?>
                        <?php endforeach ?>

                        <?php foreach ($tarea->EventosExternos as $ee): ?>
                        <?php if($ee->id===$p->evento_externo_id): ?>
                            <option selected value="<?=$ee->id?>" title="<?=$ee->nombre?>">Evento Externo <?=$ee->nombre?></option>
                        <?php else: ?>
                            <option value="<?=$ee->id?>" title="<?=$ee->nombre?>">Evento Externo <?=$ee->nombre?></option>
                        <?php endif; ?>
                        <?php endforeach ?>
                        <option value="">Ejecutar Tarea</option>
                    <?php endif; ?>

                    <?php if($p->evento_externo_id): ?>
                        <?php foreach ($tarea->Pasos as $paso): ?>
                            <?php if($paso->id===$p->paso_id): ?>
                                <option selected value="<?=$paso->id?>" title="<?=$paso->Formulario->nombre?>">Ejecutar Paso <?=$paso->orden?></option>
                            <?php else: ?>
                                <option value="<?=$paso->id?>" title="<?=$paso->Formulario->nombre?>">Ejecutar Paso <?=$paso->orden?></option>
                            <?php endif; ?>
                        <?php endforeach ?>

                        <?php foreach ($tarea->EventosExternos as $ee): ?>
                        <?php if($ee->id===$p->evento_externo_id): ?>
                            <option selected value="<?=$ee->id?>" title="<?=$ee->nombre?>">Evento Externo <?=$ee->nombre?></option>
                        <?php else: ?>
                            <option value="<?=$ee->id?>" title="<?=$ee->nombre?>">Evento Externo <?=$ee->nombre?></option>
                        <?php endif; ?>
                        <?php endforeach ?>
                        <option value="">Ejecutar Tarea</option>
                    <?php endif; ?>

                    <?php if(is_null($p->paso_id) && is_null($p->evento_externo_id)): ?>
                        <?php foreach ($tarea->Pasos as $paso): ?>
                            <?php if($paso->id===$p->paso_id): ?>
                                <option selected value="<?=$paso->id?>" title="<?=$paso->Formulario->nombre?>">Ejecutar Paso <?=$paso->orden?></option>
                            <?php else: ?>
                                <option value="<?=$paso->id?>" title="<?=$paso->Formulario->nombre?>">Ejecutar Paso <?=$paso->orden?></option>
                            <?php endif; ?>
                        <?php endforeach ?>

                        <?php foreach ($tarea->EventosExternos as $ee): ?>
                        <?php if($ee->id===$p->evento_externo_id): ?>
                            <option selected value="<?=$ee->id?>" title="<?=$ee->nombre?>">Evento Externo <?=$ee->nombre?></option>
                        <?php else: ?>
                            <option value="<?=$ee->id?>" title="<?=$ee->nombre?>">Evento Externo <?=$ee->nombre?></option>
                        <?php endif; ?>
                        <?php endforeach ?>
                        <?php if(is_null($p->paso_id) && is_null($p->evento_externo_id)): ?>
                            <option value="" selected>Ejecutar Tarea</option>
                        <?php else: ?>
                            <option value="">Ejecutar Tarea</option>
                        <?php endif; ?>
                    <?php endif; ?>
                </select>
            </td>
            <td>
                <input type="hidden" class="eventoAccionId" name="eventos[<?= $key + 1 ?>][accion_id]"
                       value="<?= $p->accion_id ?>"/>
                <a class="delete" title="Eliminar" href="#"><i class="material-icons">close</i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <label class="checkbox">Para mayor información puedes consultar en el siguiente enlace.
        <a onclick="ga('cuenta.send', 'event', 'Mejora UX', 'Ayuda_pestana_eventos');" href="/ayuda/simple/backend/modelamiento-del-proceso/disenador.html#pestana_eventos"
           target="_blank">
            <i class="material-icons">help</i>
        </a>
    </label>
</div>

<script>
    $(document).ready(function(){
        $('.eventoInstante').change(function(evt){
            if(evt.target.value === 'durante'){
                // habilitar campo
                $('.eventoCampoAsociado').prop('disabled', false);
            }else{
                // deshabilitar campo
                $('.eventoCampoAsociado').prop('disabled', true);
            }
        });

        $('.eventoPasoId.form-control').change(function(evt){
            var paso_id = $('.eventoPasoId.form-control')[0].value;
            $('.eventoCampoAsociado').parent().find(".messageEventoAsociado").html('');
            $('.eventoCampoAsociado').parent().find(".messageEventoAsociado").hide();
            if( paso_id == ''){
                $('.eventoCampoAsociado').prop('disabled', true);
                return;
            }
            $('.eventoCampoAsociado').prop('disabled', false);
            $('.eventoCampoAsociado').trigger('blur');
        });

        $('.eventoCampoAsociado').blur(function(evt){
            var paso_id = $('.eventoPasoId.form-control')[0].value;
            if(paso_id == '') return;  // es ejecutar tarea
            var campo = evt.target.value;
            if(campo == '' || typeof campo === 'undefined') return;
            $.ajax({
                url: '<?=url('backend/form/existe_campo_en_form')?>',
                data: {
                    campo_nombre: campo,
                    paso_id: paso_id
                },
                method: 'GET',
                dataType: "json",
                cache: false,
                success: function (data) {
                    if( ! data.resultado ) {
                        $('.eventoCampoAsociado').parent().find(".messageEventoAsociado").html(data.mensaje);
                        $('.eventoCampoAsociado').parent().find(".messageEventoAsociado").show();
                    }else{
                        $('.eventoCampoAsociado').parent().find(".messageEventoAsociado").html('');
                        $('.eventoCampoAsociado').parent().find(".messageEventoAsociado").hide();
                    }
                }
            });
            evt.stopPropagation();
        });

        $('.eventoCampoAsociado').focus(function (evt) {
            $(this).parent().find(".messageEventoAsociado").hide();
            evt.stopPropagation();
        });

        $('.eventoInstante').trigger('change');
        $('.eventoCampoAsociado').prop('disabled', true);
    })
</script>