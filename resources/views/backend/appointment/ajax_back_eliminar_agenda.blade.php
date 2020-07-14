<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
                Eliminar Agenda
                <a href="/ayuda/simple/backend/agenda-editar.html#eliminar" target="_blank">
                    <span class="glyphicon glyphicon-info-sign" style="font-size: 16px;"></span>
                </a>
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="frmeliminar">
                <div class="validacion validacioneliminar"></div>
                <label>Esta seguro de querer eliminar la agenda <strong>"<?= $nombre ?>"</strong> que pertenece a
                    <strong>"<?= $pertenece ?>"</strong></label>
                <div class="form-group">
                    <label>Motivo</label>
                    <textarea id="motivo" name="motivo" class="form-control" style="resize:none;"></textarea>
                </div>
                <input type="hidden" name="nombre" value="<?= $nombre ?>">
                <input type="hidden" name="pertenece" value="<?= $pertenece ?>">
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn js_cerrar_vcancelar" data-dismiss="modal">Cerrar</button>
            <a href="#" onclick="ajax_eliminar_agenda(<?= $id ?>);" class="btn btn-primary">Eliminar Agenda</a>
        </div>
    </div>
</div>
<script>
    function ajax_eliminar_agenda(id) {
        $('.validacion').html('');
        var param = $('#frmeliminar').serialize() + '&id=' + id;
        $.ajax({
            url: "<?= url('/backend/agendas/ajax_eliminar_agenda') ?>",
            data: param,
            dataType: "json",
            success: function (data) {
                if (data.code == 200) {
                    $('.js-del-' + id).remove();
                    $("#modalnuevaagenda").modal('toggle');
                } else {
                    $('.validacioneliminar').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>' + data.mensaje + ' .</div>');
                }
            }
        });
    }
</script>