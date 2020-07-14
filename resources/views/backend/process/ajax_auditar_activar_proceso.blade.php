<div class="modal-dialog" role="document">
    <form id="formAuditarRetrocesoEtapa" method='POST' class='ajaxForm'
          action="{{route('backend.procesos.activar', [$proceso->id])}}">
        {{csrf_field()}}
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Activación de proceso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class='validacion'></div>
                <label>Indique la razón por la cual activa el proceso:</label>
                <textarea class="form-control" name="descripcion" type="text" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Activar</button>
            </div>
        </div>
    </form>
</div>
