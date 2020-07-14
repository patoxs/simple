<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Agregar D&iacute;a</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="validacion"></div>
            <div class="alert alert-warning">
                <strong>Advertencia!</strong> "Recuerde que agregar un dia feriado no eliminar&aacute; citas futuras
                sobre las agenda".
            </div>
            <label>Por favor, ingrese el detalle del d&iacute;a <strong><span id="nombredia"></span></strong> </label>
            <textarea id="descdia"></textarea>
        </div>
        <div class="modal-footer">
            <button class="btn js_cerrar_vcancelar" data-dismiss="modal">Cerrar</button>
            <a href="#" onclick="agergar_dia('<?= $fecha ?>');" class="btn btn-primary">Agregar</a>
        </div>
    </div>
</div>
<script>
    $(function () {
        moment.lang('es');
        var fe = '<?= $fecha ?>';
        $arrfecha = fe.split('-');
        var fechanombre = moment($arrfecha[1] + '/' + $arrfecha[0] + '/' + $arrfecha[2]).format("d of MMMM");
        var dia = moment($arrfecha[1] + '/' + $arrfecha[0] + '/' + $arrfecha[2]).format("DD");
        var mes = moment($arrfecha[1] + '/' + $arrfecha[0] + '/' + $arrfecha[2]).format("MMMM");
        $('#nombredia').html(dia + ' de ' + mes);
    });

    function agergar_dia(fe) {
        $('.validacion').html('');
        $arrfecha = fe.split('-');
        var fecha = $arrfecha[0] + '/' + $arrfecha[1] + '/' + $arrfecha[2];
        var $items = $('.js_row_calendar');
        var swexiste = false;
        var fechasel = jQuery.trim($arrfecha[0] + '-' + $arrfecha[1]);
        $.each($items, function (index, element) {
            if (fechasel == jQuery.trim($(element).attr('data-fecha'))) {
                swexiste = true;
            }
        });
        if (swexiste) {
            $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>No se puede grabar este dia porque ya ha sido ingresado .</div>');
        } else {
            var desc = $('#descdia').val();
            if (jQuery.trim(desc) != '') {
                var dateinsert = jQuery.trim($arrfecha[2] + '-' + $arrfecha[1] + '-' + $arrfecha[0]);
                var url = "<?= base_url('manager/diaferiado/ajax_agregar_dia_feriado') ?>";
                $.ajax({
                    url: url,
                    dataType: "json",
                    data: {
                        fecha: dateinsert,
                        name: jQuery.trim(desc)
                    },
                    success: function (data) {
                        if (data.code == 201 || data.code == 200) {
                            location.href = '<?= base_url('manager/diaferiado') ?>';
                        } else {
                            $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>' + data.mensaje + ' .</div>');
                        }
                    }
                });
            } else {
                $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>Debe ingresar el detalle .</div>');
            }
        }
    }
</script>