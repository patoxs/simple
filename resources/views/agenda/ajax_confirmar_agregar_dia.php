<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">
    <i class="icon-close icon--top"></i>
  </button>
  <h3 id="myModalLabel">Confirmar</h3>
</div>
<div class="modal-body">
  <div class="validacion"></div>
  <label>¿Est&aacute; seguro de agendar su cita para el <strong><span class="js-dia-cita" ><?php echo $ano . '/' . $mes . '/' . $dia ?></span> a las <?= $hora; ?> horas</strong>?</label>
  <label>Observaci&oacute;n</label>
  <textarea id="desccita" placeholder="Observaci&oacute;n de la cita"></textarea>
  <?php 
    if (!isset(UsuarioSesion::usuario()->email) || empty(UsuarioSesion::usuario()->email)) {
      ?>
        <input type="hidden" id="chkcorreo" value="1" />
        <label>Correo electr&oacute;nico</label>
        <input type="text" id="txtcorreo" value="" />
      <?php
    } else {
      echo '<input type="hidden" id="chkcorreo" value="0" />';
    }
  ?>
</div>
<div class="modal-footer">
  <button class="button button--lightgray js_cerrar_vcancelar">Cancelar</button>
  <button id="btnconfadd" class="button">Confirmar</button>
</div>
<script>
  window.fecha = '<?php echo $ano . '-' . $mes . '-' . $dia . ' ' . $hora; ?>';
  window.fechafinal = '<?php echo $fechafinal; ?>';
  window.tzz = jstz.determine().name();

  $(function() {
    moment.lang('es');
    var lab = moment($('.js-dia-cita').text()).format("LL");
    $('.js-dia-cita').text(lab);
    $('#btnconfadd').click(function() {
      $('#btnconfadd').prop("disabled", true);
      $('.js_cerrar_vcancelar').prop("disabled", true);
      ajaxAgregarCita(fecha);
    });
    $('.js_cerrar_vcancelar').click(function() {
      $('#btnconfadd').prop("disabled", true);
      $('.js_cerrar_vcancelar').prop("disabled", true);
      $('.validacion').html('');
      $('#modalconfirmar').modal('toggle');
    });
  });

  function ajaxAgregarCita(fecha) {
    if ($('#chkcorreo').val() >= 1) {
      if (jQuery.trim($('#txtcorreo').val())) {
          procAgregar(jQuery.trim($('#txtcorreo').val()));
      } else {
        $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>Debe ingresar un correo electrónico.</div>');
        $('#btnconfadd').prop("disabled", false);
        $('.js_cerrar_vcancelar').prop("disabled", false);
      }
    } else {
        procAgregar('');
    }
  }

  function procAgregar(email) {
    var idagenda = <?= $idagenda ?>;
    var idtramite = <?= $idtramite ?>;
    var etapa = <?= $etapa ?>;
    var desc = jQuery.trim($('#desccita').val());
    var idobject = <?= $object ?>;
    var idcita = <?= $idcita ?>;

    if (idcita == 0) {
      if (typeof $('#codcita' + idobject) !== "undefined") {
        idcita = $('#codcita' + idobject).val();
      }
    }

    $('.validacion').html('');
    $.ajax({
      url: '<?=site_url('/agenda/ajax_agregar_cita')?>',
      data: {
        tzz: tzz,
        fecha: fecha,
        fechafinal: fechafinal,
        idagenda: idagenda,
        desc: desc,
        email: email,
        idcita: idcita,
        idtramite: idtramite,
        etapa: etapa,
        obj: idobject
      },
      dataType: "json",
      success: function(data) {
        if (data.code == 200 || data.code == 201) {
          var idcita = data.appointment;
          // Mayor que 0 es el editar dentro del tramite y 0 es el editar en mi agenda
          if (idobject > 0) {
            $('#codcita' + idobject).val(idcita);
            $('#' + idobject).val(idcita + '_' + fecha);
            var fe = fecha.split(' ');
            var f = fe[0].split('-');
            var fechares = moment(f[0] + "/" + f[1] + "/" + f[2]).format("LL");
            $('#txtresult' + idobject).html(fechares + " a las " + fe[1] + " horas");
            $('.modal').modal('toggle');
          } else {
            location.href = '<?=site_url('/agenda/miagenda')?>';
          }
        } else {
          switch (data.code) {
            case 2010:
              $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>La cita debe tener una fecha/hora igual o mayor a la actual.</div>');
              break;
            case 2050:
              $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>'+data.mensaje+'</div>');
              break;
            case 2040:
              $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>'+data.mensaje+'</div>');
              break;
            default:
              $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>No se pudo agendar la cita. Si el problema persiste contacte al administrador.</div>');
              break;
          }
          $('#btnconfadd').prop("disabled", false);
          $('.js_cerrar_vcancelar').prop("disabled", false);
        }
      }
    });
  }
</script>
