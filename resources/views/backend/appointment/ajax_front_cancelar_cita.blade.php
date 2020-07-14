<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3 id="myModalLabel">Cancelar cita</h3>
</div>
<div class="modal-body">
    <div class="validacion"></div>
    <?php
    if(isset($funcionario) && $funcionario){
        echo '<label>Se cancelar&aacute; la cita seleccionada. Ingrese el motivo por el cual solicita esta acci&oacute;n:</label>
        <label>Motivo</label>
        <textarea id="motivo" class="motcancelcitafun" style="width: 500px; resize: none;"></textarea>';
    }else{
        echo '<label>Se cancelar&aacute; la cita seleccionada.</label>';
    }
    ?>
</div>
<div class="modal-footer">
    <button class="btn js_cerrar_vcancelar" data-dismiss="modal">Cerrar</button>
    <a href="#" onclick="ajaxcancelarCita(<?= $id ?>,'<?= $fecha ?>');" class="btn btn-primary">Confirmar</a>
</div>
<script>
    function ajaxcancelarCita(id,fecha){
        var motivo="";
        var sw=true;
        $('.validacion').html('');
        if($("#motivo").length){//verifica si existe
            motivo=$("#motivo").val();
            if(jQuery.trim(motivo)==""){
                sw=false;
            }
        }
        if(sw){
            $.ajax({
                url:'<?= base_url('/agenda/ajax_cancelarCita/'.$id) ?>',
                data:{
                    id:id,
                    motivo:motivo
                },
                dataType: "json",
                success: function( data ) {
                    if(data.code==200){
                        location.href='<?=site_url('/agenda/miagenda')?>';
                    }else{
                        $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>'+data.message+' .</div>');
                    }
                }
            });
        }else{
            $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>Debe registrar un motivo .</div>');
        }
    }
</script>