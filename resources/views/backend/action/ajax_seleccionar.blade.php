<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
                Seleccione el tipo de acción
                <a href="/ayuda/simple/backend/modelamiento-del-proceso/acciones.html#acciones_tipo" target="_blank">
                    <i class="material-icons align-middle" style="font-size: 15px;">help</i>
                </a>
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="formAgregarAccion" class="ajaxForm" method="POST"
                  action="<?= route('backend.action.seleccionar_form', [$proceso_id]) ?>">
                {{csrf_field()}}
                <div class="validacion"></div>
                <label>Tipo de acción</label>
                <select name="tipo" class="form-control">
                    <option value="enviar_correo">Enviar Correo</option>
                    <option value="evento_analytics">Evento Google Analytics</option>
                    <option value="variable">Generar Variable</option>
                    <option value="rest">Consultar Rest</option>
                    <option value="soap">Consultar Soap</option>
                    <option value="callback">Generar Callback</option>
                    <option value="iniciar_tramite">Iniciar Trámite</option>
                    <option value="continuar_tramite">Continuar Trámite</option>
                    <option value="webhook">Notificaciones</option>
                    <option value="descarga_documento">Descargar Documento</option>
                    <option value="redirect">Redirección</option>
                    <option value="generar_documento">Generar Documento</option>
                </select>
            </form>
        </div>
        <div class="modal-footer">
            <a href="#" data-dismiss="modal" class="btn btn-light">Cerrar</a>
            <a href="#" onclick="javascript:$('#formAgregarAccion').submit();return false;"
               class="btn btn-primary">Continuar</a>
        </div>
    </div>
</div>
