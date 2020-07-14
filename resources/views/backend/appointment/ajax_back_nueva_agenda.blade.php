<?php
function conversorSegundosHoras($tiempo_en_segundos)
{
    $horas = floor($tiempo_en_segundos / 3600);
    $horas = ($horas < 10) ? '0' . $horas : $horas;
    $minutos = floor(($tiempo_en_segundos - ($horas * 3600)) / 60);
    $minutos = ($minutos < 10) ? '0' . $minutos : $minutos;
    $segundos = $tiempo_en_segundos - ($horas * 3600) - ($minutos * 60);
    $segundos = ($segundos < 10) ? '0' . $segundos : $segundos;
    return $horas . ':' . $minutos;
}
$service = url('/backend/agendas/ajax_grabar_agenda_back');//este apunta al servicio para grabar
if (isset($editar) && $editar) {
    $service = url('/backend/agendas/ajax_editar_agenda_back');//este apunta al servicio para grabar
}
?>
<style>
    .fa {
        float: left;
        position: relative;
        line-height: 20px;
    }
</style>
<link href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?=$title_form ?>
                <a href="/ayuda/simple/backend/agenda-agregar.html" target="_blank">
                    <i class="material-icons align-middle">help</i>
                </a>
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="validacion valcal"></div>
            <form id="formeditagenda" class="ajaxForm2 frmagenda" method="POST" action="<?= $service ?>">
                <input type="hidden" id="namepertenece" name="namepertenece"/>
                <?php
                if (isset($editar) && $editar) {
                    $idagenda = (isset($id) && is_numeric($id)) ? $id : 0;
                    echo '<input id="codagenda" name="codagenda" type="hidden" value="' . $idagenda . '" />';
                    echo '<div class="alert alert-warning">
                  <strong>Advertencia!</strong> "Recuerde que la edición de una agenda no eliminar&aacute; citas futuras".
                </div>';
                } else {
                    $idagenda = 0;
                    echo '<input id="codagenda" name="codagenda" type="hidden" value="0" />';
                }
                ?>
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="" class="form-control col-4"/>
                </div>
                <div class="form-group">
                    <label>Pertenece a: </label><br>
                    <select id="selectgrupo" name="grupos_usuarios" class="selectgrupo form-control col-4"></select>
                </div>
                <input type="hidden" name="tipopertenece" id="tipopertenece" value=""/>
                <input type="hidden" name="emailpertenece" id="emailpertenece" value=""/><br>
                <label class="labtitlefranja">Franja(s) Horaria(s)</label>
                <button style="display:none;" class="btn js_btn_add_franja btn-warning" type="button">+</button>
                <div class="zfranja">
                    <table>

                    </table>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" value="1" id="ignorarferiados"
                           name="ignorarferiados" style="float: left;"/>
                    <label for="ignorarferiados" class="form-check-label">Ignorar feriados</label>
                </div>
                <div class="form-group mt-2">
                    <label class="labtiematen">Tiempo de atención</label>
                    <input id="txttatencion" name="tatencion" type="hidden">

                    <div class="input-group bootstrap-timepicker timepicker">
                        <input id="txttiempoatencion" readonly="readonly" type="text" class="form-control col-3">
                        <div class="input-group-append">
                            <button class="input-group-addon btn btn-secondary" type="button">
                                <i class="material-icons">access_time</i>
                            </button>
                        </div>
                    </div>
                </div>
                <label class="labcustomagend">Concurrencia</label>
                <select class="cmbhoras form-control col-2" id="concurrencia" name="concurrencia">
                    <?php
                    for ($i = 1; $i <= 10; $i++) {
                        echo '<option value="' . $i . '">' . $i . '</option>';
                    }
                    ?>
                </select>
                <label class="labcustomagend">Tiempo m&iacute;nimo de cancelación</label>
                <select id="tmincancelacion" name="tmincancelacion" class="cmbhoras form-control col-2">
                    <?php
                    for ($i = 1; $i <= 6; $i++) {
                        echo '<option value="' . $i . '">' . $i . 'h</option>';
                    }
                    ?>
                </select>
            </form>
            <div class="modal-footer mt-3">
                <a href="#" data-dismiss="modal" class="btn btn-light">Cerrar</a>
                <?php
                echo '<a href="#" onclick="guardaragenda();" class="btn btn-primary">Guardar</a>';
                ?>
            </div>
        </div>
    </div>
    <script>
        window.idrow = 1;
        window.arraytipo = new Array();
        window.arrcorreos = new Array();
        <?php
            $hinicio = '';
            for ($i = 0; $i <= 1440; ($i = $i + 30)) {
                $texthora = conversorSegundosHoras(($i * 60));
                $hinicio = $hinicio . "<option value='" . $texthora . "'>" . $texthora . "</option>";
            }
            ?>
            window.option = "<?= $hinicio ?>";
        $(function () {
            var idagenda =<?= $idagenda ?>;
            if (idagenda > 0) {
                var form = $('#formeditagenda');
                $('.validacion').html('');
                var modal = $('#modalnuevaagenda');
                $(form).append("<div class='ajaxLoader'>Cargando</div>");
                var ajaxLoader = $(form).find(".ajaxLoader");
                $(ajaxLoader).css({
                    left: ($(modal).width() / 2 - $(ajaxLoader).width() / 2) + "px",
                    top: ($(modal).height() / 2 - $(ajaxLoader).height() / 2) + "px"
                });
            }
            $('#txttiempoatencion').timepicker({
                showMeridian: false,
                minuteStep: 1,
                defaultTime: false,
                icons: {
                    up: "fa fa-chevron-up",
                    down: "fa fa-chevron-down",
                    next: 'fa fa-chevron-right',
                    previous: 'fa fa-chevron-left'
                }
            }).on('changeTime.timepicker', function (e) {
                $('#txttatencion').val(e.time.hours + ':' + e.time.minutes);
            });
            $('#tiempoconfirmacion').timepicker('setTime', '00:05');
            $('.zfranja').find('table').append(crearRow(idrow));
            iniciarSelectDias('cmbdias' + idrow);
            $.ajax({
                url: '<?= url('/backend/formularios/listarPertenece') ?>',
                dataType: "json",
                async: false,
                success: function (data) {
                    if (data.code == 200) {
                        var items = data.resultado.items;
                        $.each(items, function (index, element) {
                            var icon = 'person';
                            if (element.tipo == 1) {
                                icon = 'group';
                            }
                            $("#selectgrupo").append('<option value="' + element.id + '" data-icon="' + icon + '" >' + element.nombre + '</option>');
                            arraytipo[index] = element.tipo;
                            arrcorreos[index] = element.email;
                        });
                    } else {
                        $('.valcal').append('<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>' + data.message + '.</div>');
                    }
                }
            });
            $("#selectgrupo").select2({
                placeholder: "Seleccione (Opcional)",
                allowClear: true,
                multiple: false,
                templateSelection: selection,
                templateResult: format
            });
            cargarDatosEditar();
            eventoadd();
        });

        function eventoadd() {
            $('.js_btn_add_franja').off();
            $('.js_btn_add_franja').on("click", function (e) {
                idrow++;
                $('.zfranja').find('table').append(crearRow(idrow));
                iniciarSelectDias('cmbdias' + idrow);
                eventoadd();
            });
        };

        function format(icon) {
            var originalOption = icon.element;
            return $('<span><i class="material-icons" style="top: 1px;">' + $(originalOption).data('icon') + '</i>&nbsp;&nbsp;' + icon.text + '</span>');
        }

        function selection(icon) {
            var originalOption = icon.element;
            return $('<span><i class="material-icons" style="top: 7px;">' + $(originalOption).data('icon') + '</i>&nbsp;&nbsp;' + icon.text + '</span>');
        }

        function guardaragenda() {
            var form = $('#formeditagenda');
            $('.validacion').html('');
            var url = $('#formeditagenda').attr('action');
            if (validarCampos()) {
                var idcmbgrup = $('#selectgrupo').val();
                $('#namepertenece').val($('#selectgrupo option[value="' + idcmbgrup + '"]').text());
                var indearray = $("#selectgrupo").prop('selectedIndex');
                $('#tipopertenece').val(arraytipo[indearray]);
                $('#emailpertenece').val(arrcorreos[indearray]);
                $(form).append("<div class='ajaxLoader'>Cargando</div>");
                var ajaxLoader = $(form).find(".ajaxLoader");
                $(ajaxLoader).css({
                    left: ($(form).width() / 2 - $(ajaxLoader).width() / 2) + "px",
                    top: ($(form).height() / 2 - $(ajaxLoader).height() / 2) + "px"
                });
                $.ajax({
                    url: url,
                    data: $(form).serialize(),
                    type: form.method,
                    dataType: "json",
                    success: function (data) {
                        $(ajaxLoader).remove();
                        if (data.code == 201 || data.code == 200) {
                            form[0].reset();
                            $("#modalnuevaagenda").modal('toggle');
                            window.location = '<?= url('/backend/agendas') ?>';
                        } else {
                            $('.valcal').append('<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>' + data.message + '.</div>');
                        }
                    },
                    error: function () {
                        $(ajaxLoader).remove();
                        $('.valcal').append('<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>No se pudo grabar la agenda, favor intentelo mas tarde .</div>');
                    }
                });
            }
        }

        function validarCampos() {
            var form = $('#formeditagenda');
            var ajaxLoader = $(form).find(".ajaxLoader");
            $('.validacion').html('');
            if (jQuery.trim($('#nombre').val()) != '') {
                if ($('#selectgrupo').val() != 0) {
                    var sw = true;
                    $('.zfranja').find('table').find('tr').each(function () {
                        var idrow = $(this).attr('id').split("row");
                        if (!selectfin(idrow[1])) {
                            sw = false;
                        }
                    });
                    $('.selectdias').each(function () {
                        if ($(this).val() == null) {
                            sw = false;
                            $('.valcal').append('<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>En la franja horaria debe tener minimo un dia de la semana.</div>');
                        }
                    });
                    $(ajaxLoader).remove();
                    return sw;
                } else {
                    mensaje_error('Pertenece a', ' es obligatorio');
                    $(ajaxLoader).remove();
                    return false;
                }
            } else {
                mensaje_error('Nombre', ' es obligatorio');
                $(ajaxLoader).remove()
                return false;
            }
        }

        function eliminar_franja(id) {
            $("#row" + id).remove();
        }

        function mensaje_error(nombre_cambo, mensaje) {
            $('.valcal').append('<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>El campo "<strong>' + nombre_cambo + '</strong>"' + mensaje + '.</div>');
        }

        function cargarDatosEditar() {
            var idagenda =<?= $idagenda ?>;
            if (idagenda > 0) {
                var form = $('#formeditagenda');
                var ajaxLoader = $(form).find(".ajaxLoader");
                $.ajax({
                    url: '<?= url('backend/agendas/ajax_cargarDatosAgenda/' . $idagenda) ?>',
                    data: {
                        id: idagenda
                    },
                    dataType: "json",
                    success: function (data) {
                        if (data.code == 200) {
                            $('#nombre').val(data.calendar.name);
                            var $select = $("#selectgrupo");
                            $select.val(data.calendar.owner_id).trigger("change");
                            idrow = 0;
                            $.each(data.franja, function (index, element) {
                                idrow = index + 1;
                                if (index > 0) {
                                    $('.zfranja').find('table').append(crearRow(idrow));
                                    iniciarSelectDias('cmbdias' + idrow);
                                }
                                $('#idseli' + idrow).val(element.horainicio).trigger("change");
                                $('#idself' + idrow).val(element.horafinal).trigger("change");
                                var dias = element.dias.split(':');
                                $('#cmbdias' + idrow).val(dias).trigger("change");

                            });
                            if (data.calendar.ignore_non_working_days == 1) {
                                $("#ignorarferiados").prop("checked", "checked");
                            }
                            $('#concurrencia').val(data.calendar.concurrency);
                            var tiempoatencion = getTimeSelect(data.calendar.time_attention);
                            $('#txttiempoatencion').timepicker('setTime', tiempoatencion);
                            $('#txttatencion').val(tiempoatencion);
                            var tiempoconfirmacion = getTimeSelect(data.calendar.time_confirm_appointment);
                            $('#tiempoconfirmacion').timepicker('setTime', tiempoconfirmacion);
                            $('#tiempoconfirmacion').val(tiempoconfirmacion);
                            $('#tconfirmacion').val(getTimeSelect(data.calendar.time_confirm_appointment));
                            eventoadd();
                        } else {
                            $('.valcal').append('<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>' + data.mensaje + '</div>');
                        }
                        $(ajaxLoader).remove();
                    },
                    error: function () {
                        $(ajaxLoader).remove();
                        $('.valcal').append('<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>No se pudieron cargar los datos a editar, vuelva a intentarlo .</div>');
                    }
                });
            }
        }

        function getTimeSelect(val) {
            var d = Number(val * 60);
            var h = Math.floor(d / 3600);
            var m = Math.floor(d % 3600 / 60);
            var tmp = '';
            if (h <= 9) {
                tmp = '0' + h;
            } else {
                tmp = h;
            }
            if (m <= 9) {
                tmp = tmp + ':' + '0' + m;
            } else {
                tmp = tmp + ':' + m;
            }
            return tmp;
        }

        function selectfin(id) {
            if ($("#idself" + id).prop('selectedIndex') < $("#idseli" + id).prop('selectedIndex')) {
                $('.valcal').html('<div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a>La hora inicial no puede superar a la final.</div>');
                return false;
            } else {
                $('.validacion').html('');
                return true;
            }
        }

        function iniciarSelectDias(id) {
            var $opt = '<option value="lunes">Lunes</option>';
            $opt = $opt + '<option value="martes">Martes</option>';
            $opt = $opt + '<option value="miercoles">Miercoles</option>';
            $opt = $opt + '<option value="jueves">Jueves</option>';
            $opt = $opt + '<option value="viernes">Viernes</option>';
            $opt = $opt + '<option value="sabado">Sabado</option>';
            $opt = $opt + '<option value="domingo">Domingo</option>';
            $('#' + id).html($opt);
            $('#' + id).select2({
                placeholder: "Seleccione (Opcional)",
                width: '100%',
                allowClear: true,
                multiple: true
            });
        }

        function obbeneridrow(indexrow) {
            var i = 0;
            var idrow = 0;
            $('.zfranja').find('table').find('tr').each(function () {
                if (i == indexrow) {
                    var idrow = $(this).attr('id').split("row");
                }
                i++;
            });
            return idrow;
        }

        function crearRow(idrow) {
            var row = '';
            if (idrow == 1) {
                row = '<tr id="row' + idrow + '" class="js_rows_franajas" ><td class="collab">Hora Inicio</td><td><select id="idseli' + idrow + '" class="cmbhoras form-control" onchange="selectfin(' + idrow + ');" name="horainicio[]">' + option + '</select></td><td class="collab">Hora Fin</td><td><select name="horafin[]" class="cmbhoras form-control" onchange="selectfin(' + idrow + ');" id="idself' + idrow + '" >' + option + '</select></td><td width="45%"><select class="selectdias" name="cmbdias' + idrow + '[]" id="cmbdias' + idrow + '"></select></td><td><button class="btn js_btn_add_franja btn-warning" type="button">+</button></td><td>&nbsp;</td></tr>';
            } else {
                row = '<tr id="row' + idrow + '" class="js_rows_franajas" ><td class="collab">Hora Inicio</td><td><select id="idseli' + idrow + '" class="cmbhoras form-control" onchange="selectfin(' + idrow + ');" name="horainicio[]">' + option + '</select></td><td class="collab">Hora Fin</td><td><select name="horafin[]" class="cmbhoras form-control" onchange="selectfin(' + idrow + ');" id="idself' + idrow + '" >' + option + '</select></td><td width="45%"><select class="selectdias" name="cmbdias' + idrow + '[]" id="cmbdias' + idrow + '"></select></td><td><button onclick="eliminar_franja(' + idrow + ');" class="btn js_btn_remov_franja btn-danger" type="button">-</button></td><td><button class="btn js_btn_add_franja btn-warning" type="button">+</button></td></tr>';
            }
            return row;
        }
    </script>