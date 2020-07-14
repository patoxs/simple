<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Editar Proceso</h5>
            <button type="button" class="close" data-dismiss="modal"  onclick="CloseModal();" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="formEditarProceso" class="ajaxForm" method="POST"
                  action="<?=url('backend/procesos/editar_form/' . $proceso->id)?>">
                {{csrf_field()}}
                <div class="validacion" style="padding: 10px;"></div>

                <div style="width: 45%;display: inline-block;">
                    <label>Nombre</label>
                    <input type="text" class="form-control" name="nombre" value="{{ $proceso->nombre }}"/><br>
                     <!--jp-->
                   
                    <!--finjp-->
                    <label>Tamaño de la Grilla</label>
                    <div class="form-group form-inline">
                        <input type="text" name="width" value="{{ $proceso->width }}" class="form-control col-4"/>
                        <input type="text" name="height" value="{{ $proceso->height }}" class="form-control col-4"/>
                    </div>
                </div>
             
                    
                
                <div style="width: 45%;float: right">
                    <label>Categoría</label>
                    <select name="categoria" id="categoria" class="form-control">
                        <option value="0">Todos los trámites</option>
                        @foreach($categorias as $c)
                            @if($proceso->categoria_id == $c->id)
                                <option value="{{ $c->id }}" selected="true">{{ $c->nombre }}</option>
                            @else
                                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                            @endif
                        @endforeach
                    </select>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="destacado"
                               name="destacado" {{$proceso->destacado == 1 ? 'checked' : ''}}>
                        <label class="form-check-label" for="destacado">Destacado </label>
                    </div>
                </div>
                <div>
                    <label>Icono</label>
                    <input id="filenamelogo" type="hidden" name="logo" value="<?= $proceso->icon_ref ?>"/>
                    <a href="javascript:;" id="SelectIcon" class="btn btn-light">Seleccionar ícono</a>
                    @if($proceso->icon_ref)
                        <img id="icn-logo" class="logo icn-logo" src="{{asset('img/icon/' . $proceso->icon_ref)}}"
                             alt="logo"/>
                    @else
                        <img id="icn-logo" class="logo icn-logo" src="{{asset('img/icon/nologo.png')}}" alt="logo"/>
                    @endif
                </div>
                <div>
                    <label>Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="5" cols="10">{{$proceso->descripcion}}</textarea>
                </div>
                <div>
                    <label>Url informativa</label>
                    <input type="text" class="form-control" name="url_informativa" value="<?=$proceso->url_informativa?>"/>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="concurrente"
                            name="concurrente" {{$proceso->concurrente == 1 ? 'checked' : ''}}>
                    <label class="form-check-label" for="concurrente">Permitir la ejecución de varios trámites a la vez por usuario.</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="eliminar_tramites"
                            name="eliminar_tramites" {{$proceso->eliminar_tramites == 1 ? 'checked' : ''}}>
                    <label class="form-check-label" for="eliminar_tramites">Permitir la eliminación de trámites.</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="ocultar_front"
                            name="ocultar_front" {{$proceso->ocultar_front == 1 ? 'checked' : ''}}>
                    <label class="form-check-label" for="ocultar_front">Ocultar tarjeta de inicio de solicitud en frontend.</label>
                </div>
                <br><br>
                <h4>Editor de Ficha de Trámite</h4>
                <p>
                    Aquí podrás añadir una ficha informativa que el usuario podrá ver previo a la solicitud. Te sugerimos
                    que incluyas dentro de la descripción datos relevantes como requisitos previos, puntos en
                    consideración, entre otros.
                </p>
                <div class="form-check form-check">
                    <input class="form-check-input" type="checkbox" id="ficha_informativa"
                           name="ficha_informativa" {{$proceso->ficha_informativa == 1 ? 'checked' : ''}}>
                    <label class="form-check-label" for="ficha_informativa">
                        ¿Deseas añadir esta ficha informativa antes de que el usuario comience el trámite?
                    </label>
                </div>
                <div id="ficha-content" class="hide">
                    <br>
                    <div class="form-group">
                        <label for="ficha_titulo">Título</label>
                        <input type="text" class="form-control" id="ficha_titulo" name="ficha_titulo" value="{{ $proceso->ficha_titulo }}">
                    </div>
                    <div class="form-group">
                        <label for="ficha-contenido">Contenido</label>
                        <textarea class="form-control" id="ficha-contenido" name="ficha_contenido" rows="8">{{ $proceso->ficha_contenido }}</textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer" data-keyboard="false" aria-hidden="true" tabindex="-1">
           <a href="#" type="close" data-dismiss="modal"  onclick="CloseModal();"  class="btn btn-light">Cerrar</a>
           <a href="#" onclick="javascript:$('#formEditarProceso').submit();return false;" class="btn btn-primary">Guardar</a>
        </div>
    </div>
</div>
<script>
$(document).ready(function(){
    // console.log("asdasd");

    checkFichaInformativa($('#ficha_informativa'));

    $(".modal-open").click(function(){
        $("#modal").modal({
            backdrop: 'static',
            keyboard: false

        });
    });


    $('body').on('click', '#ficha_informativa', function() {
        checkFichaInformativa($(this));
    });
});

function checkFichaInformativa($ficha) {
    if ($ficha.is(':checked')) {
        $('#ficha-content').show();
    } else {
        $('#ficha-content').hide();
    }
}

function CloseModal() {
    $( '.modal' ).remove();
    $( '.modal-backdrop' ).remove();
    window.location.replace("/backend/procesos/" + procesoId + "/edit");
}
</script>
<script> 
$('body').removeClass('modal-backdrop show');
</script>
