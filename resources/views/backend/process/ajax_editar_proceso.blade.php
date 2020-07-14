<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Edición de proceso</h5>
            <button type="close" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="formEditarProceso" method='POST' class='ajaxForm'
                  action="<?= url('backend/procesos/editar/' . $proceso->id) ?>">
                <div class='validacion'></div>
                <label>Presione editar si desea modificar el proceso publicado, de lo contrario si desea una nueva
                    versión presione generar.</label>
            </form>
        </div>
        <div class="modal-footer">
            <a href="<?=url('backend/procesos/editar_publicado/' . $proceso->id . '/1')?>"
               class="btn btn-danger">Editar</a>
            <a href="<?=url('backend/procesos/editar_publicado/' . $proceso->id)?>"
               class="btn btn-primary">Generar</a>
        </div>
    </div>
</div>
