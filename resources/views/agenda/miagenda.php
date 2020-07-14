<link rel="stylesheet" href= "<?= base_url('assets/calendar/css/calendar.css') ?>" >
<script src= "<?= base_url('/assets/js/jquery-ui/js/jquery-ui.js') ?>"></script>
<script src= "<?= base_url('/assets/js/moment.min.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/components/underscore/underscore-min.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/components/jstimezonedetect/jstz.min.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/js/language/es-CO.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/js/calendar.js?v=0.1') ?>"></script>
<script src="<?= base_url() ?>assets/js/collapse.js"></script>
<script src="<?= base_url() ?>assets/js/transition.js"></script>
<script src="<?= base_url() ?>assets/js/bootstrap-datetimepicker.min.js"></script>
<input type="hidden" name="" id="urlbase" value="<?= base_url() ?>">
<script src= "<?= base_url('/assets/calendar/js/moment-2.2.1.js') ?>"></script>
<h2>Mis citas</h2>
<div class="containter-tab-agenda">
  <table class="table js-tab-agenda">
    <thead>
      <tr>
        <th>Tr&aacute;mite</th>
        <th>Responsable</th>
        <th>Fecha</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php
        if (isset($data) && is_array($data) && count($data) > 0) {
          foreach ($data as $item) {
            $serverItemTime = $item->appointment_time;
            $fecha = date('d/m/Y H:i:s', strtotime($item->appointment_time));
            $fecha2 = date('d/m/Y H:i', strtotime($item->appointment_time));
            $tmp = explode(' ', $fecha);
            $fe = explode('/', $tmp[0]);
            $fechaparam = $fe[0] . '-' . $fe[1] . '-' . $fe[2];
            $acciones = '<a class="btn btn-primary" onclick="editar(' . $item->appointment_id . ',' . $item->calendar_id . ',' . $item->idtramite . ',' . $item->etapa . ');" href="#"><i class="icon-white icon-edit"></i> Editar</a> <a class="btn btn-danger" href="#" onclick="cancelarCita(\'' . $item->appointment_id . '\',\'' . $fechaparam . '\');"><i class="icon-white icon-remove"></i> Cancelar</a>';
            echo '<tr> <td>' . $item->tramite . '</td><td>' . $item->owner_name . '</td><td><span class="navtime" data-time="' . $serverItemTime . '"></span></td><td>' . $acciones . '</td></tr>';
          }
        } else {
          echo '<tr><td colspan="4">No existen citas</td></tr>';
        }
      ?>
    </tbody>
  </table>
</div>
<div id="paginador" style="text-align: center;" class="clearfix">
  <ul class="pagination" style="max-width: 255px;">
    <li><a href="<?=site_url('/agenda/miagenda')?>">&laquo;</a></li>
    <?php
      for ($i = $pagina_desde; $i <= $pagina_hasta; $i++) {
        if ($i > 0 && $i <= $total_paginas) {
           echo '<li><a href="' . site_url('/agenda/miagenda/' . $i) . '">' . $i . '</a></li>';
        }
      }
    ?>
    <li><a href="<?=site_url('/agenda/miagenda/' . $total_paginas)?>">&raquo;</a></li>
  </ul>
</div>
<script type="text/javascript">

  function cancelarCita(id, fecha) {
    $("#modalcancelar").load(site_url + "backend/agendasusuario/ajax_cancelar_cita?id=" + id + "&fecha=" + fecha);
    $("#modalcancelar").modal();
  }

  function editar(idcita, idcalendar, tramite, etapa) {
    calendarioFront(idcalendar, 0, idcita, parseInt(tramite), etapa);
  }

  function getTimeBrowserES(time) {
    var localDate = moment(time).toDate();
    var min = ('0' + localDate.getMinutes()).slice(-2);
    var localMonth;

    switch (localDate.getMonth()+1) {
      case 1:
        localMonth = "Enero";
        break;
      case 2:
        localMonth = "Febrero";
        break;
      case 3:
        localMonth = "Marzo";
        break;
      case 4:
        localMonth = "Abril";
        break;
      case 5:
        localMonth = "Mayo";
        break;
      case 6:
        localMonth = "Junio";
      case 7:
        localMonth = "Julio";
        break;
      case 8:
        localMonth = "Agosto";
        break;
      case 9:
        localMonth = "Septiembre";
        break;
      case 10:
        localMonth = "Octubre";
        break;
      case 11:
        localMonth = "Noviembre";
        break;
      case 12:
        localMonth = "Diciembre";
        break;
      }

    var result = localDate.getDate() + ' de ' + localMonth + ' de ' + localDate.getFullYear() 
      + ' a las ' + localDate.getHours() + ':' + min;

    return result;
  }

 $(document).ready(function() {
    moment.lang('es');

    $('.navtime').each(function () {
      var timeserver = $(this).attr('data-time');
      $(this).text(getTimeBrowserES(timeserver));
    });
  });
</script>
<div id="modalcancelar" class="modal hide fade"></div>
<div id="modalcalendar" class="modal hide fade modalconfg modcalejec"></div>