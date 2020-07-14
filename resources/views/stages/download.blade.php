<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Descarga de documentos</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form enctype="multipart/form-data" id="formDescargarDocumentos" method='POST'
                  action="{{ url('etapas/descargar_form/') }}">
                {{csrf_field()}}

                <label>Seleccione:</label>
                
                <div class="radio">
                    <label>
                        <input type="radio" name="opcionesDescarga" id="opcionesDescarga1" value="documento"> 
                        Generados: Documentación que el sistema genera al usuario.   
                    </label>
                </div>
                @if (!Auth::user()->open_id)
                <div class="radio">
                    <label>
                        <input type="radio" name="opcionesDescarga" id="opcionesDescarga2" value="dato">
                        Subidos: Documentación que el usuario adjuntó en el trámite.
                    </label>
                </div>
                @endif                
                @if (Auth::user()->open_id)
                <div class="radio">
                    <label>
                        <input type="radio" name="opcionesDescarga" id="opcionesDescarga4" value="datounico" checked>
                        Subidos: Documentación subida por el usuario de Clave Única.
                    </label>
                </div>
                @endif
                <input type="hidden" id="tramites" name="tramites" value="<?= $tramites ?>">
            </form>
        </div>
        <div class="modal-footer">
            <a class="btn btn-light closeModal" data-dismiss="modal">Cerrar</a>
            <a href="#" onclick="javascript:$('#formDescargarDocumentos').submit();$('#modal').modal('hide')"
               class="btn btn-primary">Descargar</a>
        </div>
    </div>
</div>