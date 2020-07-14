<div class="modal-dialog" role="document">
    <div class="modal-content">

        <div class="modal-header">
            <h5 class="modal-title">Actualizar ID de Trámites</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="formActualizarId" method='POST' class='ajaxForm'
                  action="{{route('backend.tracing.ajaxUpdateIdProcedure')}}">
                {{csrf_field()}}
                <div class='validacion'></div>
                <label>Indique el nuevo Id inicial(Obs: Debe ser mayor a <?=$max?>):</label>
                <input class="form-control col-6" name='id' type='text' required/>
            </form>

        </div>
        <div class="modal-footer">
            <button class="btn btn-light" data-dismiss="modal">Cancelar</button>
            <a href="#"
               onclick="javascript:$('#formActualizarId').submit(); return false;"
               class="btn btn-primary">
                Guardar
            </a>
        </div>
    </div>
</div>
