$(document).ready(function(){

    jsPlumb.Defaults.PaintStyle={
        strokeStyle:"#333",
        lineWidth:1.6
    };
    jsPlumb.Defaults.Endpoint= "Blank";
    jsPlumb.Defaults.Connector=[ "Flowchart" ];
    jsPlumb.Defaults.ConnectionOverlays = [[ "Arrow", {
        location:1,
        width:6 ,
        length:6
    } ]];
});

function graficar(tareas) {
    var indice = 1;
    var bandera = 0;

    jsPlumb.reset();

    $(tareas).each(function(i,e){

        if (e.estado == 'Completado') {
            $("#dibujo").append("<div id='"+indice+"' class='tarea' style='margin-left:70px; background: green;'>"+indice+"</div>");
        }else{
            $("#dibujo").append("<div id='"+indice+"' class='tarea' style='margin-left:70px; background: goldenrod;'>"+indice+"</div>"); //e.tarea_nombre
            bandera = 1;
        };

        indice++;
    });

    $(tareas).each(function(i,e){
        $.get("consultas/portada/ver_etapas/"+e.etapa_id,function(d){
            var id = "#" + (i + 1);
            $(id).unbind('hover').popover({
                html: true,
                title: "Detalle de la Etapa",
                content: d
            });
        });
    });

    for (i = 1; i < (indice - 1); i++) {
        var source = String(i);
        var target = String(i+1);

        var connection = jsPlumb.connect({
            source: source,
            target: target,
            anchors: ["RightMiddle", "LeftMiddle"]
        });

        $("#dibujo #"+target).append('<div class="union"></div>');
    }

    if (bandera == 1) {

        $("#dibujo").append("<div id='"+indice+"' class='tarea' style='margin-left:90px; background: goldenrod; position: relative;'>Fin</div>");


        var source = String(indice - 1);
        var target = String(indice);

        var connection = jsPlumb.connect({
            source: source,
            target: target,
            anchors: ["RightMiddle", "LeftMiddle"],
            paintStyle: { strokeStyle:"#333", lineWidth:1.6, dashstyle:"2 2" },
        });

        $("#dibujo #"+target).append('<div class="union"></div>');
    }

}