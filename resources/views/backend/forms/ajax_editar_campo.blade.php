@php
    $idagendaeditar = htmlspecialchars($campo->agenda_campo);
    if (!isset($idagendaeditar) || !is_numeric($idagendaeditar)) {
        $idagendaeditar = 0;
    }
@endphp
<style>
    .fa {
        float: left;
        position: relative;
        line-height: 20px;
    }
</style>

<script src="{{asset('js/helpers/fileuploader.js')}}"></script>
<script>
    var uploader = new qq.FileUploader({
        element: document.getElementById('file-uploader'),
        params: {_token: '{{csrf_token()}}'},
        action: '/backend/uploader/masiva',
        onComplete: function (id, filename, respuesta) {
            $("input[name=logo]").val(respuesta.file_name);
            $("img.logo").attr("src", "/logos/" + respuesta.file_name);
            $('#file_carga_masiva').val(respuesta.file_name);
        }
    });
</script>

<script type="text/javascript">
    $(document).ready(function () {
        $('.validacion').typeahead({
            mode: "multiple",
            delimiter: "|",
            source: ["required", "rut", "min:num", "max:num", "digits:num", 
            "alpha", "alpha_dash", "alpha_num", "numeric", "integer", 
            "email", "emails", "ip", "digits_between:min,max", "between:min,max", "nullable", "regex"]
        });
        $('.validacion-columna').typeahead({
            mode: "multiple",
            delimiter: "|",
            source: ["required", "rut", "min:num", "max:num", "digits:num", 
            "alpha", "alpha_dash", "alpha_num", "numeric", "integer", 
            "email", "emails", "ip", "digits_between:min,max", "between:min,max", "nullable", "regex"]
        });

        // Funcionalidad del llenado de nombre usando el boton de asistencia
        $("#formEditarCampo .asistencia .dropdown-menu a").click(function () {
            var nombre = $(this).text();
            $("#formEditarCampo input[name=nombre]").val(nombre);
        });

        // Funcionalidad del llenado de dependiente usando el boton de asistencia
        $("#formEditarCampo .dependiente .dropdown-menu a").click(function () {
            var nombre = $(this).text();
            $("#formEditarCampo input[name=dependiente_campo]").val(nombre);
        });

        // Funcionalidad en campo dependientes para seleccionar entre tipo regex y string
        $buttonRegex = $("#formEditarCampo .campoDependientes .buttonRegex");
        $buttonString = $("#formEditarCampo .campoDependientes .buttonString");
        $buttonNumeric = $("#formEditarCampo .campoDependientes .buttonNumeric");
        $inputDependienteTipo = $("#formEditarCampo input[name=dependiente_tipo]");
        $buttonString.attr("disabled", $inputDependienteTipo.val() == "string");
        $buttonRegex.attr("disabled", $inputDependienteTipo.val() == "regex");
        $buttonNumeric.attr("disabled", $inputDependienteTipo.val() == "numeric");

        $buttonRegex.click(function () {
            $buttonString.prop("disabled", false);
            $buttonRegex.prop("disabled", true);
            $buttonNumeric.prop("disabled", false);
            $inputDependienteTipo.val("regex");
        });

        $buttonString.click(function () {
            $buttonString.prop("disabled", true);
            $buttonRegex.prop("disabled", false);
            $buttonNumeric.prop("disabled", false);
            $inputDependienteTipo.val("string");
        });

        $buttonNumeric.click(function () {
            $buttonString.prop("disabled", false);
            $buttonRegex.prop("disabled", false);
            $buttonNumeric.prop("disabled", true);
            $inputDependienteTipo.val("numeric");
        });

        // Funcionalidad en campo dependientes para seleccionar entre tipo igualdad y desigualdad
        $buttonDesigualdad = $("#formEditarCampo .campoDependientes .buttonDesigualdad");
        $buttonIgualdad = $("#formEditarCampo .campoDependientes .buttonIgualdad");
        $buttonMayorque = $("#formEditarCampo .campoDependientes .buttonMayorque");
        $buttonMenorque = $("#formEditarCampo .campoDependientes .buttonMenorque");
        $buttonMayoroigualque = $("#formEditarCampo .campoDependientes .buttonMayoroigualque");
        $buttonMenoroigualque = $("#formEditarCampo .campoDependientes .buttonMenoroigualque");
        $inputDependienteRelacion = $("#formEditarCampo input[name=dependiente_relacion]");
        $buttonIgualdad.attr("disabled", $inputDependienteRelacion.val() == "==");
        $buttonDesigualdad.attr("disabled", $inputDependienteRelacion.val() == "!=");
        $buttonMayorque.attr("disabled", $inputDependienteRelacion.val() == ">");
        $buttonMenorque.attr("disabled", $inputDependienteRelacion.val() == "<");
        $buttonMayoroigualque.attr("disabled", $inputDependienteRelacion.val() == ">=");
        $buttonMenoroigualque.attr("disabled", $inputDependienteRelacion.val() == "<=");

        $buttonDesigualdad.click(function () {
            $buttonIgualdad.prop("disabled", false);
            $buttonDesigualdad.prop("disabled", true);
            $buttonMayorque.prop("disabled",false);
            $buttonMenorque.prop("disabled", false);
            $buttonMayoroigualque.prop("disabled", false);
            $buttonMenoroigualque.prop("disabled", false);
            $inputDependienteRelacion.val("!=");
        });

        $buttonIgualdad.click(function () {
            $buttonIgualdad.prop("disabled", true);
            $buttonDesigualdad.prop("disabled", false);
            $buttonMayorque.prop("disabled",false);
            $buttonMenorque.prop("disabled", false);
            $buttonMayoroigualque.prop("disabled", false);
            $buttonMenoroigualque.prop("disabled", false);
            $inputDependienteRelacion.val("==");
        });

        $buttonMayorque.click(function(){
            $buttonIgualdad.prop("disabled", false);
            $buttonDesigualdad.prop("disabled", false);
            $buttonMayorque.prop("disabled",true);
            $buttonMenorque.prop("disabled", false);
            $buttonMayoroigualque.prop("disabled", false);
            $buttonMenoroigualque.prop("disabled", false);
            $inputDependienteRelacion.val(">");
        });

        $buttonMenorque.click(function(){
            $buttonIgualdad.prop("disabled", false);
            $buttonDesigualdad.prop("disabled", false);
            $buttonMayorque.prop("disabled",false);
            $buttonMenorque.prop("disabled", true);
            $buttonMayoroigualque.prop("disabled", false);
            $buttonMenoroigualque.prop("disabled", false);
            $inputDependienteRelacion.val("<");
        });

        $buttonMayoroigualque.click(function(){
            $buttonIgualdad.prop("disabled", false);
            $buttonDesigualdad.prop("disabled", false);
            $buttonMayorque.prop("disabled",false);
            $buttonMenorque.prop("disabled", false);
            $buttonMayoroigualque.prop("disabled", true);
            $buttonMenoroigualque.prop("disabled", false);
            $inputDependienteRelacion.val(">=");
        });

        $buttonMenoroigualque.click(function(){
            $buttonIgualdad.prop("disabled", false);
            $buttonDesigualdad.prop("disabled", false);
            $buttonMayorque.prop("disabled",false);
            $buttonMenorque.prop("disabled", false);
            $buttonMayoroigualque.prop("disabled", false);
            $buttonMenoroigualque.prop("disabled", true);
            $inputDependienteRelacion.val("<=");
        });

        // Llenado automatico del campo nombre
        $("#formEditarCampo input[name=etiqueta]").blur(function () {
            ellipsize($("#formEditarCampo input[name=etiqueta]"), $("#formEditarCampo input[name=nombre]"));
        });

        // Llenado automatico del campo valor
        $("#formEditarCampo").on("blur", "input[name$='[etiqueta]']", function () {
            var campoOrigen = $(this);
            var campoDestino = $(this).closest("tr").find("input[name$='[valor]']")
            ellipsize(campoOrigen, campoDestino);
        });

        function ellipsize(campoOrigen, campoDestino) {
            if ($(campoDestino).val() == "") {
                var string = $(campoOrigen).val().trim();
                string = string.toLowerCase();
                string = string.replace(/\s/g, "_");
                string = string.replace(/á/g, "a");
                string = string.replace(/é/g, "e");
                string = string.replace(/í/g, "i");
                string = string.replace(/ó/g, "o");
                string = string.replace(/ú/g, "u");
                string = string.replace(/\W/g, "");
                $(campoDestino).val(string);
            }
        }

        /* Prevenir espacios en campo nombre y que puedan pegar contenido en el mismo campo */
        $(document).on('keypress', '#nombre', function (e) {
            return !(e.keyCode == 32);
        });

        $('#nombre').bind('paste', function (e) {
            e.preventDefault();
        });


		$(document).on('keypress', '#validacion', function (e) {
            return !(e.keyCode == 32);
        });

        $('#validacion').bind('paste', function (e) {
            e.preventDefault();
        });
    });
</script>

<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
                Edición de Campo
                <a onclick="ga('cuenta.send', 'event', 'Mejora_UX_Ayuda_Campo', '<?= $campo->tipo ?>');" href="/ayuda/simple/backend/modelamiento-del-proceso/diseno-de-formularios.html#btn_<?= $campo->tipo ?>"
                   target="_blank">
                    <i class="material-icons">help</i>
                </a>
            </h5>
            <button type="close" class="close" onclick="CloseModal();" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>

        </div>
        <div class="modal-body">
            <form id="formEditarCampo" class="ajaxForm" method="POST"
                  action="<?= route('backend.forms.editar_campo_form', ($edit ? [$campo->id] : '')) ?>">
                {{csrf_field()}}
                <div class="validacion"></div>
                @if (!$edit)
                    <input type="hidden" name="formulario_id" value="<?= $formulario->id ?>"/>
                    <input type="hidden" name="tipo" value="<?= $campo->tipo ?>"/>
                @endif
                @if($campo->tipo==='btn_siguiente')
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Advertencia!</strong> Al agregar este campo, reemplazará el botón siguiente por defecto y ocupará su posición.
                    </div>
                @endif
                @if($campo->tipo==='file_s3')
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Advertencia!</strong> El campo File Transfer, solo se debe ocupar en trámites que tengan interoperabilidad de documentos. La url en la cual se almacena el adjunto tiene una vigencia de 30 días.
                    </div>
                @endif
                <label>Etiqueta
                </label>
                @if ($campo->etiqueta_tamano == 'xxlarge')
                    <textarea class="form-control col-8" rows="5"
                              name="etiqueta"><?= htmlspecialchars($campo->etiqueta) ?></textarea>
                @else
                    <input type="text" class="form-control col-4" name="etiqueta"
                           value="<?= htmlspecialchars($campo->etiqueta) ?>"/>
                @endif

                <?php if ($campo->requiere_nombre): ?>
                <label>Nombre</label>
                <?php endif; ?>

                <div class="input-group">
                    <?php if ($campo->requiere_nombre): ?>
                    <input type="text" class="form-control col-4" id="nombre" name="nombre"
                           value="<?= $campo->nombre ?>"/>
                    <?php $campos_asistencia = $formulario->Proceso->getNombresDeCampos($campo->tipo, false) ?>

                    <?php if (count($campos_asistencia)): ?>
                    <div class="input-group-append asistencia">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                            <i class="material-icons">list</i><span class="caret"></span>
                        </button>
                        <div class="dropdown-menu">
                            @foreach ($campos_asistencia as $c)
                                <a class="dropdown-item" href="#"><?= $c ?></a>
                            @endforeach
                        </div>
                    </div>
                    <br>
                    <?php endif ?>
                    <?php else: ?>
                    <input type="hidden" name="nombre" value="<?=$campo->nombre ? $campo->nombre : uniqid();?>"/>
                    <?php endif; ?>
                </div>

                <?php if (!$campo->estatico):?>
                <label>Ayuda contextual (Opcional)</label>
                <input type="text" class="form-control col-4" name="ayuda" value="<?=$campo->ayuda?>"/>
                <?php endif ?>

                <?php if (!$campo->estatico): ?>
                <?php if (isset($campo->datos_agenda) && $campo->datos_agenda) { ?>
                <label style="display: none;" class="checkbox">
                    <input type="checkbox" name="readonly" value="1" <?=$campo->readonly ? 'checked' : ''?> />
                    Solo lectura
                </label>
                <?php } else { ?>
                <label class="checkbox"><input type="checkbox" name="readonly"
                                               value="1" <?=$campo->readonly ? 'checked' : ''?> /> Solo lectura</label>
                <?php } ?>
                <?php endif; ?>
                <?php if (!$campo->estatico): ?>
                <?php if (isset($campo->datos_agenda) && $campo->datos_agenda) { ?>
                <label style="display:none;">Reglas de validación
                    <a onclick="ga('cuenta.send', 'event', 'Mejora_UX', 'Ayuda_reglas_validacion_campos');" href="/ayuda/simple/backend/modelamiento-del-proceso/reglas-de-negocio-y-reglas-de-validacion.html"
                       target="_blank">
                        <i class="material-icons">help</i>
                    </a>
                </label>
                <input style="display: none;" class='validacion' type="text" name="validacion"
                       value="<?= $edit ? implode('|', $campo->validacion) : 'required' ?>"/>
                <?php } else { ?>
					<br/>
                <label>Reglas de validación
                    <a onclick="ga('cuenta.send', 'event', 'Mejora_UX', 'Ayuda_reglas_validacion_campos_visibles');" href="/ayuda/simple/backend/modelamiento-del-proceso/reglas-de-negocio-y-reglas-de-validacion.html#validacion_campos"
                       target="_blank">
                        <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                </label>
                <input class='validacion form-control' type="text" id="validacion" name="validacion"
                       value="<?= $edit ? implode('|', $campo->validacion) : 'required' ?>"/>
                <?php } ?>

                <?php endif; ?>
                <?php if (!$campo->estatico): ?>
                <?php if ((isset($campo->datos_agenda) && $campo->datos_agenda) || (isset($campo->datos_mapa) && $campo->datos_mapa)) { ?>
                <label style="display:none;">Valor por defecto</label>
                <input style="display:none;" class="form-control" type="text" name="valor_default"
                       value="<?=htmlspecialchars($campo->valor_default)?>"/>
                <?php } else { ?>
                <label>Valor por defecto</label>
                <input type="text" class="form-control" name="valor_default"
                       value="<?=htmlspecialchars($campo->valor_default)?>"/>
                <?php } ?>
                <?php endif ?>
                <label>Visible solo si</label>
                    <div class="campoDependientes">
                        <div class="form-inline">
                            <input type="text" class="form-control col-2" name="dependiente_campo"
                                   value="<?=$campo->dependiente_campo?>"/>
                            <div class="btn-group dependiente ml-1" style="display: inline-block; vertical-align: top;">
                                <a class="btn btn-light dropdown-toggle" data-toggle="dropdown" href="#">
                                    <i class="material-icons">view_list</i> <span class="caret align-middle"></span>
                                </a>
                                <div class="dropdown-menu">
                                    <button class="dropdown-item" type="button"><b>Campos</b></button>
                                    @foreach ($formulario->Proceso->getCampos() as $c)
                                        <a class="dropdown-item" href="#"><?= $c->nombre ?></a>
                                    @endforeach
                                    <div class="dropdown-divider"></div>
                                    <button class="dropdown-item" type="button"><b>Variables</b></button>
                                    @foreach ($formulario->Proceso->getVariables() as $v)
                                        <a class="dropdown-item" href="#"><?= $v->extra->variable ?></a>
                                    @endforeach
                                </div>
                            </div>
                       <div class="btn-group btn-group-sm">
                                <button type="button" class="buttonIgualdad btn btn-secondary">=</button>
                                <button type="button" class="buttonDesigualdad btn btn-secondary">!=</button>
                                <button type="button" class="buttonMayorque btn btn-secondary">></button>
                                <button type="button" class="buttonMenorque btn btn-secondary"><</button>
                                <button type="button" class="buttonMayoroigualque btn btn-secondary">>=</button>
                                <button type="button" class="buttonMenoroigualque btn btn-secondary"><=</button>
                            </div>

                            <input type="hidden" name="dependiente_relacion"
                                   value="<?=isset($campo) && $campo->dependiente_relacion ? $campo->dependiente_relacion : '==' ?>"/>

                            <span class="input-append">
                           
                            <input type="text" class="form-control" name="dependiente_valor"
                                   value="<?= isset($campo) ? $campo->dependiente_valor : '' ?>"/>
                            <button type="button" class="buttonString btn btn-secondary">String</button>
                            <button type="button" class="buttonRegex btn btn-secondary">Regex</button>
                            <button type="button" class="buttonNumeric btn btn-secondary">Numeric</button>
                        </span>
                            <input type="hidden" name="dependiente_tipo"
                                   value="<?=isset($campo) && $campo->dependiente_tipo ? $campo->dependiente_tipo : 'string' ?>"/>

                            @if (isset($campo->datos_mapa) && $campo->datos_mapa)
                                <script type="text/javascript">
                                    $(function () {
                                        $("[name=readonly]").click(function () {
                                            if (this.checked) {
                                                $('.columnas').show();
                                            } else {
                                                $("#formEditarCampo .columnas table tbody tr").remove();
                                                $('.columnas').hide();
                                            }
                                        });
                                    });
                                </script>
                            @endif
                        </div>

                        <?php if (isset($campo->datos_agenda) && $campo->datos_agenda): ?>
                        <div class="form-group">
                            <label>Pertenece a: </label>
                            <div class="input-group mb-3">
                                <select id="selectgrupo" class="form-control col-4" name="grupos_usuarios"></select>
                                <div class="input-group-append">
                                    <button class="btn btn_filtrar_agenda vtop btn-light" type="button">Filtrar</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Agenda:</label>
                            <select id="miagenda" class="form-control col-4" name="agenda_campo">
                                <option value="1">Seleccione(Opcional)</option>
                            </select>
                        </div>
                        <script type="text/javascript">
                            $(function () {
                                $("#selectgrupo").select2({
                                    placeholder: "Seleccione(Opcional)",
                                    allowClear: true,
                                    multiple: false,
                                    templateSelection: selection,
                                    templateResult: format
                                });

                                $("#selectgrupo").change(function () {
                                    $("#miagenda").html('');
                                    var idseleccionado = $(this).val();
                                    $.ajax({
                                        url: '<?= route('backend.forms.ajax_mi_calendario') ?>',
                                        dataType: "json",
                                        data: {
                                            pertenece: idseleccionado
                                        },
                                        success: function (data) {
                                            if (data.code == 200) {
                                                var items = data.calendars;
                                                $('#miagenda').html('');
                                                if (items.length > 0) {
                                                    $("#miagenda").removeAttr('disabled');
                                                    $.each(items, function (index, element) {
                                                        $("#miagenda").append('<option value="' + element.id + '">' + element.name + '</option>');
                                                    });
                                                    var swedit = <?php echo (isset($edit) && $edit) ? 1 : 0; ?>;
                                                    if (swedit == 1) {
                                                        var idagenda = <?= $idagendaeditar ?>;
                                                        $('#miagenda').val(idagenda);
                                                    }
                                                }
                                            }
                                        }
                                    });
                                });

                                $.ajax({
                                    url: '<?= route('backend.forms.listarPertenece') ?>',
                                    dataType: "json",
                                    success: function (data) {
                                        if (data.code == 200) {
                                            var items = data.resultado.items;
                                            $.each(items, function (index, element) {
                                                console.log(element);
                                                var icon = 'person';
                                                if (element.tipo == 1) {
                                                    icon = 'group';
                                                }
                                                $("#selectgrupo").append('<option value="' + element.id + '" data-icon="' + icon + '" >' + element.nombre + '</option>');
                                            });
                                        }
                                    }
                                });
                                var swedit = <?php echo (isset($edit) && $edit) ? 1 : 0; ?>;
                                if (swedit == 1) {
                                    var idagenda = <?= $idagendaeditar ?>;
                                    cargar_service(idagenda);
                                }
                            });

                            function format(icon) {
                                var originalOption = icon.element;
                                return $('<span><i class="material-icons" style="top: 1px;">' + $(originalOption).data('icon') + '</i>&nbsp;&nbsp;' + icon.text + '</span>');
                            }

                            function selection(icon) {
                                var originalOption = icon.element;
                                return $('<span><i class="material-icons" style="top: 7px;">' + $(originalOption).data('icon') + '</i>&nbsp;&nbsp;' + icon.text + '</span>');
                            }

                            function cargar_service(idagenda) {
                                $.ajax({
                                    url: '<?= route('backend.forms.obtener_agenda') ?>',
                                    dataType: "json",
                                    data: {
                                        idagenda: idagenda
                                    },
                                    success: function (data) {
                                        if (data.code == 200) {
                                            var options = $('#selectgrupo').find('option');
                                            var owner = data.calendario_owner;
                                            var indexpertenece = 0;
                                            var v = 0;
                                            $.each(options, function (index, value) {
                                                if ($(value).text().indexOf(owner) >= 0) {
                                                    indexpertenece = index;
                                                }
                                            });
                                            $("#selectgrupo").select2("val", options[indexpertenece].value);
                                        }
                                    }
                                });
                            }
                        </script>
                        <?php endif; ?>

                        <br><br>
                        <label>Condiciones de visibilidad adicionales</label><br><button class="btn btn-light condicion" type="button"><i class="material-icons">add</i>Nuevo</button>
                        <div class="camposDependientesAdicionales">
                        
                            <?php if ($campo->condiciones_extra_visible): ?>
                            <?php $i = 0 ?>
                            <?php foreach ($campo->condiciones_extra_visible as $key => $d): ?>
                            <div class="item form-inline">
                                <input type="text" class="form-control col-4" name="condiciones[<?= $i ?>][campo]" value="<?=$d->campo?>"/>

                                <div class="btn-group col-8">
                                    <select name="condiciones[<?= $i ?>][igualdad]" class="form-control col-3">
                                        <option value="==" <?=$d->igualdad=='=' ? 'selected="selected"' : '' ?>>Es igual a</button>
                                        <option value="!=" <?=$d->igualdad=='!=' ? 'selected="selected"' : '' ?>>Distinto a</button>
                                        <option value=">" <?=$d->igualdad=='>' ? 'selected="selected"' : '' ?>>Mayor que</button>
                                        <option value="<" <?=$d->igualdad=='<' ? 'selected="selected"' : '' ?>>Menor que</button>
                                        <option value=">=" <?=$d->igualdad=='>=' ? 'selected="selected"' : '' ?>>Mayor ó = que</button>
                                        <option value="<=" <?=$d->igualdad=='<=' ? 'selected="selected"' : '' ?>>Menor ó = que</button>
                                    </select>
                                    <span class="input-append"></span>
                                    <input type="text" class="form-control" name="condiciones[<?= $i ?>][valor]" value="<?=$d->valor?>" />
                                    <select name="condiciones[<?= $i ?>][tipo]" class="form-control">
                                        <option value="string" <?=$d->tipo=='string' ? 'selected="selected"' : '' ?>>String</button>
                                        <option value="regex" <?=$d->tipo=='regex' ? 'selected="selected"' : '' ?>>Regex</button>
                                        <option value="numeric" <?=$d->tipo=='numeric' ? 'selected="selected"' : '' ?>>Numeric</button>
                                    </select>
                                    <button type="button" class="btn btn-light delete-condition"><i class="material-icons">close</i>Eliminar</button>
                                </div>
                            </div>
                            <?php $i++ ?>
                            <?php endforeach; ?>
                            <?php endif ?>

                        </div>


                        <script type="text/javascript">
                            $('#formEditarCampo .campoDependientes .condicion').click(function () {
                                var registro = $('#formEditarCampo .campoDependientes .camposDependientesAdicionales .item').length;
                                var html = '<div class="item form-inline">';
                                
                                html += '<input type="text" class="form-control col-2" name="condiciones['+registro+'][campo]"/>';
                                
                                html += '<div class="btn-group col-8">';
                                html += '<select name="condiciones['+registro+'][igualdad]" class="form-control col-3">';
                                html += '<option value="==">Es igual a</button>';
                                html += '<option value="!=">Distinto a</button>';
                                html += '<option value=">">Mayor que</button>';
                                html += '<option value="<">Menor que</button>';
                                html += '<option value=">=">Mayor ó = que</button>';
                                html += '<option value="<=">Menor ó = que</button>';
                                html += '</select>';
                                html += '<span class="input-append"></span>';
                                html += '<input type="text" class="form-control" name="condiciones['+registro+'][valor]" />';                                
                                html += '<select name="condiciones['+registro+'][tipo]" class="form-control">';
                                html += '<option value="string">String</button>';
                                html += '<option value="regex">Regex</button>';
                                html += '<option value="numeric">Numeric</button>';
                                html += '</select>';
                                html += '<button type="button" class="btn btn-light delete-condition"><i class="material-icons">close</i>Eliminar</button>';
                                html += '</div>';

                                html += '</div>';
                                $('#formEditarCampo .campoDependientes .camposDependientesAdicionales').append(html);
                            });

                            $('#formEditarCampo .campoDependientes .camposDependientesAdicionales').on('click', '.delete-condition', function () {
                                $(this).closest('.item').remove();
                            });
                        </script>



                    </div>
                    


                <?=$campo->extraForm() ? $campo->extraForm() : '' ?>

                <?php if ($campo->requiere_datos): ?>
                <div class="datos">
                    <script type="text/javascript">
                        $(document).ready(function () {
                            $('#formEditarCampo .datos .nuevo').click(function () {
                                var pos = $('#formEditarCampo .datos table tbody tr').length;
                                var html = '<tr>';
                                html += '<td><input type="text" name="datos[' + pos + '][etiqueta]" class="form-control" /></td>';
                                html += '<td><input class="form-control" type="text" name="datos[' + pos + '][valor]" /></td>';
                                html += '<td><button type="button" class="btn btn-light eliminar"><i class="material-icons">close</i> Eliminar</button></td>';
                                html += '</tr>';

                                $('#formEditarCampo .datos table tbody').append(html);
                            });
                            $('#formEditarCampo .datos').on('click', '.eliminar', function () {
                                $(this).closest('tr').remove();
                            });
                        });

                        //Para la carga mediante archivo
                        // document.getElementById('file-input').addEventListener('change', leerArchivo, false);

                        function leerArchivo(e){
                            var archivo = e.target.files[0];
                            if(!archivo){
                                return;
                            }
                            var lector = new FileReader();
                            lector.onload = function(e){
                                var contenido = e.target.result;
                                var etiqueta = contenido.split(';');
                                mostrarContenido(contenido);
                            };
                            lector.readAsText(archivo);
                        }

                        function mostrarContenido(contenido){
                            var lines = contenido.split('\n');
                            var pos = $('#formEditarCampo .datos table tbody tr').length;
                            $.each(lines, function(lineNo, line){
                                var items = line.split(',');
                                $.each(items, function(itemNo, item){
                                    var separado =(item.split(';',2));
                                    var html = '<tr>';
                                    html += '<td><input type="text" name="datos[' + pos + '][etiqueta]" class="form-control" value="'+separado[0]+'"/></td>';
                                    html += '<td><input class="form-control" type="text" name="datos[' + pos + '][valor]" value="'+separado[1]+'"/></td>';
                                    html += '<td><button type="button" class="btn btn-light eliminar"><i class="material-icons">close</i> Eliminar</button></td>';
                                    html += '</tr>';
                                    pos++;
                                    $('#formEditarCampo .datos table tbody').append(html);
                                });
                                
                            });
                            //var elemento = document.getElementById('contenido-archivo');
                            //elemento.innerHTML = contenido;
                            document.getElementsByName('extra[ws]')[0].value='';
                        }
                    </script>
                    <h4>Datos</h4>
                    <button class="btn btn-light nuevo" type="button"><i class="material-icons">add</i> Nuevo
                    </button>
                    <br/>
                    <!-- Para cargar registros masivos mediante archivo, en formato .CSV, separado por punto y coma(;).
                    <input type="file" name="file-input" id="file-input"/> -->
                    <!--<pre id="contenido-archivo"></pre>-->
                    <table class="table mt-3">
                        <thead>
                        <tr>
                            <th>Etiqueta</th>
                            <th>Valor</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($campo->datos): ?>
                        <?php $i = 0 ?>
                        <?php foreach ($campo->datos as $key => $d): ?>
                        <tr>
                            <td>
                                <input type="text" name="datos[<?= $i ?>][etiqueta]" value="<?= $d->etiqueta ?>"
                                       class="form-control"/>
                            </td>
                            <td>
                                <input class="form-control" type="text" name="datos[<?= $i ?>][valor]"
                                       value="<?= $d->valor ?>"/>
                            </td>
                            <td>
                                <button type="button" class="btn btn-light eliminar">
                                    <i class="material-icons">close</i> Eliminar
                                </button>
                            </td>
                        </tr>
                        <?php $i++ ?>
                        <?php endforeach; ?>
                        <?php endif ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                <?=$campo->backendExtraFields()?>
            </form>
        </div>
        <div class="modal-footer">
             <button href="#" type="button" data-dismiss="modal" onclick="CloseModal();"  class="btn btn-light">Cerrar</button>
            <a href="#" onclick="javascript:$('#formEditarCampo').submit();return false;" class="btn btn-primary">Guardar</a>
        </div>
    </div>
</div>
<script>
function myNewFunction(element) {
    var text = element.options[element.selectedIndex].text;
    // ...
}
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
    window.location.replace("/backend/formularios/editar/" + formularioId);

    }
</script>
