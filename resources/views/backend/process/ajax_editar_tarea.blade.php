<script type="text/javascript">
    $(document).ready(function () {
        function escapeHtml(text) {
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        $("#selectGruposUsuarios").select2({tags: true});

        $("[rel=tooltip]").tooltip();

        $('.datetimepicker').datetimepicker({
            format: 'DD-MM-YYYY',
            locale: 'es'
        });
        

        $('#formEditarTarea .nav-tabs a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');

        });

        // Permite borrar pasos
        $(".tab-pasos").on("click", ".delete", function () {
            $(this).closest("tr").remove();
            return false;
        });

        // Permite agregar nuevos pasos
        $(".tab-pasos .form-agregar-paso button").click(function () {
            var $form = $(".tab-pasos .form-agregar-paso");

            var pos = 1 + $(".tab-pasos table tbody tr").length;
            var formularioId = $form.find(".pasoFormulario option:selected").val();
            var formularioNombre = $form.find(".pasoFormulario option:selected").text();
            var modo = $form.find(".pasoModo option:selected").val();
            var regla = $form.find(".pasoRegla").val();

            var html = "<tr>";
            html += "<td>" + pos + "</td>";
            html += '<td><a title="Editar" target="_blank" href="/backend/formularios/editar/' + formularioId + '">' + formularioNombre + '</td>';
            html += '<td><input class="form-control" type="text" name="pasos[' + pos + '][regla]" value="'+ escapeHtml(regla) +'" /></td>';
            html += "<td>" + modo + "</td>";
            html += '<td>';
            html += '<input type="hidden" name="pasos[' + pos + '][id]" value="" />';
            html += '<input type="hidden" name="pasos[' + pos + '][formulario_id]" value="' + formularioId + '" />';
            html += '<input type="hidden" name="pasos[' + pos + '][modo]" value="' + modo + '" />';
            html += '<a class="delete" title="Eliminar" href="#"><i class="material-icons">close</i></a>';
            html += '</td>';
            html += "</tr>";

            $(".tab-pasos table tbody").append(html);

            return false;
        });

        //Permite que los pasos sean reordenables
        $(".tab-pasos table tbody").sortable({
            revert: true,
            stop: function () {
                //Reordenamos las posiciones
                $(this).find("tr").each(function (i, e) {
                    $(e).find("td:nth-child(1)").text(i + 1);
                    $(e).find("input[name*=formulario_id]").attr("name", "pasos[" + (i + 1) + "][formulario_id]");
                    $(e).find("input[name*=regla]").attr("name", "pasos[" + (i + 1) + "][regla]");
                    $(e).find("input[name*=modo]").attr("name", "pasos[" + (i + 1) + "][modo]");
                });
            }
        });

        //Permite borrar eventos
        $(".tab-eventos").on("click", ".delete", function () {
            $(this).closest("tr").remove();
            return false;
        });

        //Permite agregar nuevos eventos
        $(".tab-eventos .form-agregar-evento button").click(function () {
            var $form = $(".tab-eventos .form-agregar-evento");

            var pos = 1 + $(".tab-eventos table tbody tr").length;
            var accionId = $form.find(".eventoAccion option:selected").val();
            var accionNombre = $form.find(".eventoAccion option:selected").text();
            var regla = $form.find(".eventoRegla").val();
            var instante = $form.find(".eventoInstante option:selected").val();
            var pasoId = $form.find(".eventoPasoId option:selected").val();
            var pasoNombre = $form.find(".eventoPasoId option:selected").text();
            var pasoTitle = $form.find(".eventoPasoId option:selected").attr("title");
            var campoAsociado = $form.find(".eventoCampoAsociado").val();
            campoAsociado = campoAsociado.replace(/@/g, '').trim();
            if(campoAsociado.length > 0)
                campoAsociado = '@@' + campoAsociado;

            var html = "<tr>";
            html += "<td>" + pos + "</td>";
            html += '<td><a title="Editar" target="_blank" href="/backend/acciones/editar/' + accionId + '">' + accionNombre + '</td>';
            html += '<td><input class="form-control" type="text" name="eventos[' + pos + '][regla]" value="'+ escapeHtml(regla) +'" /></td>';
            html += '<td><select class="form-control" name="eventos['+pos+'][instante]"></select></td>';
            html += '<td><input class="form-control" type="text" name="eventos[' + pos + '][campo_asociado]" value="'+ escapeHtml(campoAsociado) +'" /></td>';
            html += '<td><select class="form-control" name="eventos['+pos+'][paso_id]"></select></td>';
            html += '<td>';
            html += '<input type="hidden" name="eventos[' + pos + '][accion_id]" value="' + accionId + '" />';
            html += '<input type="hidden" name="eventos[' + pos + '][campo_asociado]" value="' + campoAsociado + '" />';
            html += '<a class="delete" title="Eliminar" href="#"><i class="material-icons">close</i></a>';
            html += '</td>';
            html += "</tr>";

            $(".tab-eventos table tbody").append(html);

            var opciones = $('.eventoInstante').html();
            $('select[name="eventos['+pos+'][instante]"').append(opciones);
            $('select[name="eventos['+pos+'][instante]"').val(instante);

            var opciones = $('.eventoPasoId').html();
            $('select[name="eventos['+pos+'][paso_id]"').append(opciones);
            $('select[name="eventos['+pos+'][paso_id]"').val(pasoId);

            return false;
        });


        //Permite agregar nuevos eventos externos
        $(".tab-eventos-externos .form-agregar-evento-externo button").click(function () {
            var $form = $(".tab-eventos-externos .form-agregar-evento-externo");

            var pos = 1 + $(".tab-eventos-externos table tbody tr").length;
            var nombre = $form.find("#nombre").val();
            var metodo = $form.find(".eventoSentido option:selected").val();
            var url = $form.find("#url").val();
            var mensaje = $form.find("#mensaje").val();
            var regla = $form.find("#regla").val();
            var opciones = $form.find("#opciones").val();

            var html = "<tr>";
            html += "<td>" + pos + "</td>";
            html += "<td>" + nombre + "</td>";
            html += "<td>" + metodo + "</td>";
            html += "<td>" + url + "</td>";
            html += "<td>" + mensaje + "</td>";
            html += "<td>" + regla + "</td>";
            html += "<td>" + opciones + "</td>";
            html += '<td>';
            html += '<input type="hidden" name="eventos_externos[' + pos + '][id]" value="" />';
            html += '<input type="hidden" name="eventos_externos[' + pos + '][nombre]" value="' + nombre + '" />';
            html += '<input type="hidden" name="eventos_externos[' + pos + '][metodo]" value="' + metodo + '" />';
            html += '<input type="hidden" name="eventos_externos[' + pos + '][url]" value="' + url + '" />';
            html += '<input type="hidden" name="eventos_externos[' + pos + '][mensaje]" value="' + escapeHtml(mensaje) + '" />';
            html += '<input type="hidden" name="eventos_externos[' + pos + '][regla]" value="' + regla + '" />';
            html += '<input type="hidden" name="eventos_externos[' + pos + '][opciones]" value="' + opciones + '" />';
            html += '<a class="delete" title="Eliminar" href="#"><i class="material-icons">close</i></a>';
            html += '</td>';
            html += "</tr>";

            $(".tab-eventos-externos table tbody").append(html);

            return false;
        });

        $(".tab-eventos-externos").on("click", ".delete", function () {
            $(this).closest("tr").remove();
            return false;
        });

        //Permite que los eventos sean reordenables
        $(".tab-eventos table tbody").sortable({
            revert: true,
            stop: function () {
                //Reordenamos las posiciones
                $(this).find("tr").each(function (i, e) {
                    $(e).find("td:nth-child(1)").text(i + 1);
                    $(e).find(".eventoAccionId").attr("name", "eventos[" + (i + 1) + "][accion_id]");
                    $(e).find("input[name*=regla]").attr("name", "eventos[" + (i + 1) + "][regla]");
                    $(e).find(".eventoInstante").attr("name", "eventos[" + (i + 1) + "][instante]");
                    $(e).find(".eventoPasoId").attr("name", "eventos[" + (i + 1) + "][paso_id]");
                    $(e).find("input[name*=campo_asociado]").attr("name", "eventos[" + (i + 1) + "][campo_asociado]");
                });
            }
        });

        //$("#modalEditarTarea form input[name=socket_id_emisor]").val(socketId);
        //$("#modalEditarTarea .botonEliminar").attr("href",function(i,href){return href+"?socket_id_emisor="+socketId;})
    });
</script>



<script>
$(document).ready(function(){
    $(".modal-open").click(function(){
        $("#modal").modal({
            backdrop: 'static',
           // keyboard: false
           
        });
    });

});

  function CloseModal() {       
   $( '.modal' ).remove();
   $( '.modal-backdrop' ).remove();
    window.location.replace("/backend/procesos/" + procesoId + "/edit");

    }
</script>



<div id="modalExample" class="modal-dialog modal-xl" role="document"  data-keyboard="false">
    <form id="formEditarTarea" class="ajaxForm" method="POST"
          action="<?= route('backend.procesos.editar_tarea_form', [$tarea->id]) ?>">
        {{csrf_field()}}

        <div class="modal-content" style="width: 1200px;">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Editar Tareas</h5>
                <button type="button" data-dismiss="modal" onclick="CloseModal();" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalExample">
                <div class="validacion"></div>

                <div class="tabbable">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab1-tab" data-toggle="tab" href="#tab1" role="tab"
                               aria-controls="tab1"
                               aria-selected="false">Definición</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab2-tab" data-toggle="tab" href="#tab2" role="tab"
                               aria-controls="tab2"
                               aria-selected="false">Asignación</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab3-tab" data-toggle="tab" href="#tab3" role="tab"
                               aria-controls="tab3"
                               aria-selected="false">Usuarios</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab4-tab" data-toggle="tab" href="#tab4" role="tab"
                               aria-controls="tab4"
                               aria-selected="false">Pasos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab5-tab" data-toggle="tab" href="#tab5" role="tab"
                               aria-controls="tab5"
                               aria-selected="false">Eventos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab6-tab" data-toggle="tab" href="#tab6" role="tab"
                               aria-controls="tab6"
                               aria-selected="false">Vencimiento</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab7-tab" data-toggle="tab" href="#tab7" role="tab"
                               aria-controls="tab7"
                               aria-selected="false">Otros</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab8-tab" data-toggle="tab" href="#tab8" role="tab"
                               aria-controls="tab8"
                               aria-selected="false">Datos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab9-tab" data-toggle="tab" href="#tab9" role="tab"
                               aria-controls="tab9"
                               aria-selected="false">Cuentas</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">

                        @include('backend.process.tab.1')
                        @include('backend.process.tab.2')
                        @include('backend.process.tab.3')
                        @include('backend.process.tab.4')
                        @include('backend.process.tab.5')
                        @include('backend.process.tab.6')
                        @include('backend.process.tab.7')
                        @include('backend.process.tab.8')
                        @include('backend.process.tab.9')

                    </div>
                </div>
            </div>
            <div class="modal-footer" data-keyboard="true" aria-hidden="true" id="modal-footer"  tabindex="-1">
                <a href="<?= url('backend/procesos/eliminar_tarea/' . $tarea->id) ?>" class="btn btn-danger"
                   onclick="return confirm('¿Esta seguro que desea eliminar esta tarea?')">Eliminar</a>
                <button type="button" data-dismiss="modal" onclick="CloseModal();" class="btn btn-light">Cerrar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </form>
</div>
     