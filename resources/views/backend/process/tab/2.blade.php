<div class="tab-pane fade pt-3" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">
    <label>Regla de asignación
        <a onclick="ga('cuenta.send','event', 'Mejora UX', 'Ayuda_pestana_asignacion');" href="/ayuda/simple/backend/modelamiento-del-proceso/disenador.html#pestana_asignacion"
           target="_blank">
            <i class="material-icons">help</i>
        </a>
    </label>
    <div class="form-check">
        <input class="form-check-input" id="asignacion_ciclica"
               type="radio" name="asignacion"
               value="ciclica" <?= $tarea->asignacion == 'ciclica' ? 'checked' : '' ?> />
        <label for="asignacion_ciclica" class="form-check-label" rel="tooltip"
               title="Los usuarios se asignan en forma ciclica. Se van turnando dentro del grupo de usuarios en forma circular.">
            Cíclica
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input" id="asignacion_manual"
               type="radio" name="asignacion"
               value="manual" <?= $tarea->asignacion == 'manual' ? 'checked' : '' ?> />
        <label for="asignacion_manual" class="form-check-label" rel="tooltip"
               title="Al finalizar la tarea anterior, se le pregunta al usuario a quien se le va a asignar esta tarea.">
            Manual
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input" id="asignacion_auto_servicio"
               type="radio" name="asignacion"
               value="autoservicio" <?= $tarea->asignacion == 'autoservicio' ? 'checked' : '' ?> />
        <label for="asignacion_auto_servicio" class="form-check-label" rel="tooltip"
               title="La tarea queda sin asignar, y los usuarios mismos deciden asignarsela segun corresponda.">
            Auto Servicio
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input" id="asignacion_usuario"
               type="radio" name="asignacion"
               value="usuario" <?= $tarea->asignacion == 'usuario' ? 'checked' : '' ?> />
        <label for="asignacion_usuario" class="form-check-label" rel="tooltip"
               title="Ingresar el id de usuario a quien se le va asignar. Se puede ingresar una variable que haya almacenado esta información. Ej: @@usuario_inical.">
            Usuario
        </label>
    </div>
    <div id="optionalAsignacionUsuario"
         class="<?= $tarea->asignacion == 'usuario' ? '' : 'hide' ?>">
        <input class="form-control col-4" type="text" name="asignacion_usuario" value="{{$tarea->asignacion_usuario}}"
               placeholder='Ej: @@id'/>
    </div>
    <br/>
    <div class="form-check">
        <input class="form-check-input" id="asignacion_notificar" type="checkbox" name="asignacion_notificar"
               value="1" <?= $tarea->asignacion_notificar ? 'checked' : '' ?> />
        <label for="asignacion_notificar" class="form-check-label">
            Notificar vía correo electrónico al usuario asignado
        </label>
    </div>
</div>