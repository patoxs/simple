<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3 id="myModalLabel">Asistencia de Cita</h3>
</div>
<div class="modal-body">
    <div class="validacion"></div>
    <label>Esta seguro de que la persona <?php if($asistencia==1){ echo '"SI"';}else{echo '"NO"';} ?> asistio</label>
</div>
<div class="modal-footer">
    <button class="btn js_cerrar_vcancelar" data-dismiss="modal">Cerrar</button>
    <a href="#" onclick="ajax_asistencia();" class="btn btn-primary">Aceptar</a>
</div>
<script>
    function ajax_asistencia(){
        $('.validacion').html('');
        var asistencia=<?= $asistencia; ?>;
        var idtramite=<?= $idtramite; ?>;
        var calendario=<?= $calendario; ?>;
        var idcita=<?= $idcita; ?>;
        $.ajax({
            url:'<?= base_url('backend/agendasusuario/ajax_confirmo_asistencia') ?>',
            data:{
                idcita:<?= $idcita; ?>,
                asistencia:<?= $asistencia; ?>,
                idtramite:idtramite,
                calendario:calendario
            },
            dataType: "json",
            success: function( data ) {
                if(data.code==200){
                    $('#modalcancelar').modal('toggle');
                    if(asistencia==1){
                        $.each($('.js-sia'),function(index,value){
                            if($(this).attr('data-idcita')==idcita){
                                $("a[data-idcita='"+idcita+"']").removeClass('active');
                                $(this).addClass('active');
                            }
                        });
                    }else{
                        $.each($('.js-noa'),function(index,value){
                            if($(this).attr('data-idcita')==idcita){
                                $("a[data-idcita='"+idcita+"']").removeClass('active');
                                $(this).addClass('active');
                            }
                        });
                    }
                }else{
                    $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>'+data.message+' .</div>');
                }
            }
        });
    }
</script>