<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">
    <i class="icon-close icon--top"></i>
    </button>
    <h3 id="myModalLabel">Cancelar Cita</h3>
</div>
    <div class="modal-body">
        <div class="validacion valcancelcita"></div>
        <form id="frmcanccita">
            <label>Motivo</label>
            <textarea id="txtmotivo" name="motivo" class="motcancelcitafun"></textarea>    
        </form>
        
    </div>
<div class="modal-footer">
    <button class="button button--lightgray js_cerrar_vcancelar" data-dismiss="modal">Cerrar</button>
    <button onclick="confirmar_cancelar_cita(<?= $idcita ?>);" class="button" id="cancelar_cita_">Cancelar Cita</button>
</div>
