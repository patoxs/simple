<?php
  $fechainicio=date('Y-m-d H:i',$start/1000);
  $fechafinal=date('Y-m-d H:i',$end/1000);
?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">
    <i class="icon-close icon--top"></i>
  </button>
  <h3 id="myModalLabel">Bloquear Franja</h3>
</div>
<input type="hidden" name="fechainicio" />
<input type="hidden" name="fechafinal" />
<input type="hidden" name="idagenda" />
<div class="modal-body">
  <div class="validacion"></div>
  <label id="labdesc">¿Est&aacute; seguro de bloquear el intervalo del <strong>(<?= date('d/m/Y H:i', $start / 1000) ?>) - (<?= date('d/m/Y H:i', $end / 1000) ?>)</strong>?.</label>
  <textarea id="txtrazon" class="descbloq" style="width:715px; resize: none;" placeholder="Escriba la Raz&oacute;n"></textarea>
</div>
<div class="modal-footer">
  <button class="button button--lightgray js_cerrar_vcancelar" data-dismiss="modal">Cerrar</button>
  <button class="button" id="btnconfirmarbloqueo" >Confirmar</button>
</div>
<script>
  $(function() {
    moment.lang('es');
    var y = <?= date('Y', $start / 1000) ?>;
    var m = <?= date('m', $start / 1000) ?>;
    var d = <?= date('d', $start / 1000) ?>;

    var y2 = <?= date('Y', $end / 1000) ?>;
    var m2 = <?= date('m', $end / 1000) ?>;
    var d2 = <?= date('d', $end / 1000) ?>;

    var valini = moment(y + "/" + m + "/" + d).format('LL') + ' a las <?= date('H:i', $start / 1000) ?> horas';
    var valfin = '<?= date('H:i', $end / 1000) ?>';

    var mendesc = '¿Está seguro de bloquear el intervalo del ' + valini + ' hasta las ' + valfin + ' horas?';
    $('#labdesc').text(mendesc);
    $('#btnconfirmarbloqueo').click(function() {
      $('.validacion').html('');
      $('#btnconfirmarbloqueo').prop("disabled", true);
      $('.js_cerrar_vcancelar').prop("disabled", true);
      var idagenda = <?= $id ?>;
      var fechainicio = '<?= $fechainicio ?>';
      var fechafinal = '<?= $fechafinal ?>';
      var razon = jQuery.trim($('#txtrazon').val());
      var urlbase = '<?=site_url('/agenda/ajax_agregar_bloqueo')?>';
      if (razon != '') {
        $.ajax({
            url: urlbase,
            data: {
              fechainicio:fechainicio,
              fechafinal:fechafinal,
              idagenda:idagenda,
              razon:razon
            },
            dataType: "json",
            success: function(data) {
              if (data.code == 200 || data.code == 201) {
                reload_dia();
                $('#modalcancelar').modal('toggle');
              } else {
                $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>' + data.mensaje + '.</div>');
                  $('#btnconfirmarbloqueo').prop("disabled", false);
                  $('.js_cerrar_vcancelar').prop("disabled", false);
              }
            }
        });
      } else {
        $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>Debe escribir una raz&oacute;n.</div>');
        $('#btnconfirmarbloqueo').prop("disabled", false);
        $('.js_cerrar_vcancelar').prop("disabled", false);
      }
    });
  });
</script>
