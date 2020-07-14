<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Edición de Formulario</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="formEditarCampo" class="ajaxForm" method="POST"
                  action="<?=route('backend.forms.editar_form', [$formulario->id])?>">
                {{csrf_field()}}
                <div class="validacion"></div>
                <label>Nombre</label>
                <input type="text" class="form-control" name="nombre" value="{{$formulario->nombre}}"/>
                <label>Descripción(Opcional)</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="5" cols="10">{{$formulario->descripcion}}</textarea>
            </form>
        </div>
        <div class="modal-footer">
            <a href="#" data-dismiss="modal" class="btn btn-light">Cerrar</a>
            <a href="#" onclick="javascript:$('#formEditarCampo').submit();return false;" class="btn btn-primary">Guardar</a>
        </div>
    </div>
</div>