<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Retroceso de Etapa</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="formAuditarRetrocesoEtapa" method='POST' class='ajaxForm'
                  action="<?= url('backend/seguimiento/retroceder_etapa/' . $etapa->id) ?>">
                {{csrf_field()}}
                <div class='validacion'></div>
                <label>Indique la razón del retroceso:</label>
                <textarea class="form-control col-6" name='descripcion' type='text' required></textarea>
            </form>

        </div>
        <div class="modal-footer">
            <button class="btn btn-light" data-dismiss="modal">Cerrar</button>
            <a href="#" onclick="javascript:$('#formAuditarRetrocesoEtapa').submit();
        return false;" class="btn btn-primary">Guardar</a>
        </div>
    </div>
</div>
