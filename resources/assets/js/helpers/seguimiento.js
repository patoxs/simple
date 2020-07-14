$(document).ready(function(){

});

function drawSeguimiento(actuales, completadas, vencidas, hoy){
    $(completadas).each(function(i,el){
        $("#draw #"+el.identificador).addClass("completado");
    });
    $(actuales).each(function(i,el){
        $("#draw #"+el.identificador).removeClass("completado");
        $("#draw #"+el.identificador).addClass("actual");
    });
    $(vencidas).each(function(i, el){
        $("#draw #"+el.identificador).removeClass("completado");
        $("#draw #"+el.identificador).removeClass("actual");
        $("#draw #"+el.identificador).addClass("vencido");
    });
    $(hoy).each(function(i, el){
        $("#draw #"+el.identificador).removeClass("completado");
        $("#draw #"+el.identificador).removeClass("actual");
        $("#draw #"+el.identificador).removeClass("vencido");
        $("#draw #"+el.identificador).addClass("venceHoy");
    });



    $('#draw .box.actual,#draw .box.completado, #draw .box.vencido, #draw .box.venceHoy').each(
        function(){
            var el=this;
            $.get("/backend/seguimiento/ajax_ver_etapas/"+tramiteId+"/"+el.id,function(d){
                $(el).unbind('hover').popover({
                    html: true,
                    title: "Etapas ejecutadas",
                    content: d,
                    sanitize: false
                });
            });
        });

}