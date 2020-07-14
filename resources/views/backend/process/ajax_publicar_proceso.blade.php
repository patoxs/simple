<div class="modal-dialog" role="document">
    <form id="formAuditarRetrocesoEtapa" method='POST' class='ajaxForm'
          action="{{route('backend.procesos.publicar', [$proceso->id])}}">
        {{csrf_field()}}
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Publicación de proceso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <label>
                    Esta acción dejará la versión actual del proceso disponible para los usuarios, esta seguro de
                    publicar esta versión del proceso?
                </label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Aceptar</button>
            </div>
        </div>
    </form>
</div>