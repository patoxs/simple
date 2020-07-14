<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">
    <i class="icon-close icon--top"></i>
  </button>
  <h3 id="myModalLabel">Cita</h3>
</div>
<div class="modal-body">
  <div class="validacion valcancelcita"></div>
  <table>
    <tr>
      <td style="width: 140px;"><strong>Tramite: </strong></td>
      <td><?= $tramite ?></td>
    </tr>
    <tr>
      <td><strong>Solicitante: </strong></td>
      <td><?= $solicitante ?></td>
    </tr>
    <tr>
      <td><strong>Fecha: </strong></td>
      <td id="txtfechadet"><?= $dia ?></td>
    </tr>
    <tr>
      <td><strong>Hora: </strong></td>
      <td><?= $hora ?></td>
    </tr>
    <tr>
      <td><strong>Correo Solicitante: </strong></td>
      <td><?= $correo ?></td>
    </tr>
    <tr>
      <td><strong>Motivo para cancelar cita: </strong></td>
      <td><textarea id="txtmotivo" class="txtmotivocanfun" ></textarea></td>
    </tr>
  </table>
</div>
<div class="modal-footer">
  <button class="button button--lightgray js_cerrar_vcancelar" data-dismiss="modal">Cerrar</button>
  <button class="button" onclick="confirmar_cancelar_cita(<?= $idcita ?>);" id="cancelar_cita_<?= $idcita ?>">Cancelar Cita</button>
</div>

<?php
  $fe = explode('/', $dia);
?>
<script>
  $(function() {
    moment.lang('es');

    var d = <?= $fe[0] ?>;
    var m = <?= $fe[1] ?>;
    var y = <?= $fe[2] ?>;
    var f = moment(y + "/" + m + "/" + d).format("LL");

    $("#txtfechadet").text(f);
  });
</script>
