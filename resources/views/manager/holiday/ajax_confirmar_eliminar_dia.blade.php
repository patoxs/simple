<?php
$arfe = explode('-', $fecha);
?>
<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Eliminar d&iacute;a</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="validacion"></div>
            <?php
            if (isset($selecciono) && $selecciono == 1) {
                echo '<label>Esta seguro de querer eliminar el d&iacute;a <strong><span id="diaeliminar"></span></strong> .</label>';
            } else {
                echo '<label>No ha selecciona fecha para eliminar.</label>';
            }
            ?>
        </div>
        <div class="modal-footer">
            <button class="btn js_cerrar_vcancelar" data-dismiss="modal">Cerrar</button>
            <?php
            if (isset($selecciono) && $selecciono == 1) {
                echo '<a href="#" onclick="ajax_eliminar_cita(\'' . $fecha . '\');" class="btn btn-primary">Eliminar d&iacute;a</a>';
            }
            ?>
        </div>
    </div>
</div>
<script>
    $(function () {
        var fecha = '<?= $fecha ?>';
        var $arrfecha = fecha.split('-');
        var fechanombre = moment($arrfecha[1] + '/' + $arrfecha[0] + '/' + $arrfecha[2]).format("LL");
        $('#diaeliminar').html(fechanombre);
    });

    function ajax_eliminar_cita(fecha) {
        var $arrfecha = fecha.split('-');
        var fe = $arrfecha[2] + '-' + $arrfecha[1] + '-' + $arrfecha[0];
        var url = '<?=url('manager/diaferiado/ajax_eliminar_dia_feriado')?>';
        var id =<?= $id ?>;
        $.ajax({
            url: url,
            data: {
                id: id
            },
            dataType: "json",
            success: function (data) {
                if (data.code == 201 || data.code == 200) {
                    location.href = '<?=url('manager/diaferiado')?>';
                } else {
                    $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>' + data.mensaje + ' .</div>');
                }
            }
        });
    }
</script>