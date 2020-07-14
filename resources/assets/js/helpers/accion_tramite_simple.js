var validJsonR=0;

function validateForm(){
    var casoR=0;
    var select =$("#tramiteSel").val();
    if (select==''){
        $("#tramiteSel").addClass('invalido');
    }else{
        $("#tramiteSel").removeClass('invalido');
        javascript:$('#plantillaForm').submit();
    }
}

$(document).ready(function(){
    $("#cuentaSel").change(function(){

        console.log("Buscando procesos vinculados a la cuenta");

        var idCuenta = $("#cuentaSel").val();

        var todosLosProcesos = false;
        if($("#cuenta_actual_id").val() == idCuenta){
            todosLosProcesos = true;
        }

        var json='';
        $.ajax({
            url:'/backend/acciones/getProcesosCuentas',
            type:'POST',
            async:false,
            dataType: 'JSON',
            data: {
                idCuenta: idCuenta,
                idCuentaOrigen: $("#cuenta_actual_id").val(),
                todos: todosLosProcesos
            }
        })
            .done(function(d){
                json=d;
            })
            .fail(function(){
                json=0;
            });

        console.log("Respuesta procesos con permisos para cuenta seleccionada: "+json);
        console.log(json.data);

        if(json.data.length > 0){
            $("#tramiteSel").empty();
            $("#tramiteSel").append("<option value=''>Seleccione...</option>");
            for (var i = 0; i < json.data.length; i++){
                console.log("Id: "+json.data[i].id);
                console.log("Nombre: "+json.data[i].nombre);
                $("#tramiteSel").append("<option value='"+json.data[i].id+"'>"+json.data[i].nombre+"</option>");
            }
        }
    });
});