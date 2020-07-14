<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Eliminación de trámite</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="formAuditarRetrocesoEtapa" method='POST' class='ajaxForm'
                  action="<?= url('tramites/borrar_tramite/' . $tramite) ?>">
                {{csrf_field()}}
                <div class='validacion'></div>
                <label>Indique el motivo por el cual elimina el trámite (máximo 500 caracteres):</label>
                <textarea class="form-control col-12" name='motivo' type='text' maxlength="500" rows="7" required></textarea>
            </form>

        </div>
        <div class="modal-footer">
            <button class="btn btn-light" data-dismiss="modal">Cerrar</button>
            <a href="#" onclick="javascript:$('#formAuditarRetrocesoEtapa').submit();
        return false;" class="btn btn-primary">Guardar</a>
        </div>
    </div>
</div>
