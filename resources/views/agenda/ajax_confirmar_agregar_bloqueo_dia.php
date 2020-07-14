<?php
  $fechainicio = date('Y-m-d H:i', $start / 1000);
  $fechafinal = date('Y-m-d H:i', $end / 1000);
?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">
    <i class="icon-close icon--top"></i>
  </button>
  <h3 id="myModalLabel">Bloquear d&iacute;a</h3>
</div>
<input type="hidden" name="fechainicio" />
<input type="hidden" name="fechafinal" />
<input type="hidden" name="idagenda" />
<div class="modal-body">
  <div class="validacion"></div>
  <label id="labdesc">¿Est&aacute; seguro de bloquear el d&iacute;a <strong><?= date('d/m/Y', $start / 1000) ?>)</strong>?.</label>
  <textarea id="txtrazon" class="descbloq" style="width: 715px; resize: none;" placeholder="Escriba la Raz&oacute;n"></textarea>
</div>
<div class="modal-footer">
  <button class="button button--lightgray js_cerrar_vcancelar" data-dismiss="modal">Cerrar</button>
  <button class="button" id="btnconfirmarbloqueo" >Confirmar</button>
</div>
<script>
  $(function() {
    moment.lang('es');
    var y=<?= date('Y',$start/1000) ?>;
    var m=<?= date('m',$start/1000) ?>;
    var d=<?= date('d',$start/1000) ?>;
    var dia=moment(y+"/"+m+"/"+d).format('LL');
    var mendesc='¿Está seguro de bloquear el día '+dia+'?';
    $('#labdesc').text(mendesc);
    $('#btnconfirmarbloqueo').click(function() {
      $('#btnconfirmarbloqueo').prop("disabled", true);
      $('.js_cerrar_vcancelar').prop("disabled", true);
      $('.validacion').html('');
      var idagenda=<?= $id ?>;
      var fechainicio='<?= $fechainicio ?>';
      var fechafinal='<?= $fechafinal ?>';
      var razon=jQuery.trim($('#txtrazon').val());
      //var urlbase='<?=site_url('/agenda/ajax_agregar_bloqueo')?>';
      var urlbase='<?=site_url('/agenda/ajax_agregar_bloqueo_dia_completo')?>';
      $("#frmdataranghorbloq").append('<input type="hidden" name="idagenda" value="' + idagenda + '" />');
      $("#frmdataranghorbloq").append('<input type="hidden" name="razon" value="' + razon + '" />');

      var param = $("#frmdataranghorbloq").serialize();
      if (razon != '') {
        var form = $('#modalcancelar');
        $(form).append("<div class='ajaxLoader ajaxLoaderfunc'>Cargando</div>");
        var ajaxLoader = $(form).find(".ajaxLoader");
        $(ajaxLoader).css({
            left: ($(form).width() / 2 - $(ajaxLoader).width() / 2) + "px", 
            top: ($(form).height() / 2 - $(ajaxLoader).height() / 2) + "px"
        });
        $.ajax({
            url: urlbase,
            data: param,
            dataType: "json",
            success: function(data) {
              if (data.code == 200 || data.code == 201) {
                reload_dia();
                $('#modalcancelar').modal('toggle');
                $(".ajaxLoader").remove();
              } else {
                $(".ajaxLoader").remove();
                $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>' + data.mensaje + '.</div>');
                $('#btnconfirmarbloqueo').prop("disabled", false);
                $('.js_cerrar_vcancelar').prop("disabled", false);
              }
            }
        });
      } else {
        $(".ajaxLoader").remove();
        $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>Debe escribir una raz&oacute;n.</div>');
        $('#btnconfirmarbloqueo').prop("disabled", false);
        $('.js_cerrar_vcancelar').prop("disabled", false);
      }   
    });
  });
</script>