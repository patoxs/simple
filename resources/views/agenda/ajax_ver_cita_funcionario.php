<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">
        <i class="icon-close icon--top"></i>
    </button>
    <h3 id="myModalLabel">Cita</h3>
</div>
    <div class="modal-body">
        <div class="validacion"></div>
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
                <td><?= $dia ?></td>
            </tr>
            <tr>
                <td><strong>Hora: </strong></td>
                <td><?= $hora ?></td>
            </tr>
            <tr>
                <td><strong>Correo Solicitante: </strong></td>
                <td><?= $correo ?></td>
            </tr>
        </table>
    </div>
<div class="modal-footer">
    <button class="button button--lightgray js_cerrar_vcancelar" data-dismiss="modal">Cerrar</button>
    <!-- <a href="#" onclick="editar_cita(<?= $idcita ?>);" class="btn btn-primary">Editar Cita</a> -->
</div>

