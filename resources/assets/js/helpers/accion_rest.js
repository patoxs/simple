var validJsonR=0;

function validateForm(){
    var casoR=0;
    var select =$("#tipoMetodo").val(); 
    // if (select=='POST' || select=="PUT"){
    //     var resultR = isJsonR();
    //     if (resultR!='0'){
    //         $("#request").addClass('invalido');
    //         $("#resultRequest").text("Formato requerido / json");
    //         casoR=1;
    //     }else{
    //         $("#request").removeClass('invalido');
    //         $("#resultRequest").text("");
    //         casoR=0;   
    //     }
    // }
    if(casoR==0){
        var obj=$("#header").val();
        var caso=0;
        if (obj.length>1){
            var result=isJsonH();
            if (result!='0'){
                $("#header").addClass('invalido');
                $("#resultHeader").text("Formato requerido / json");
                caso=1;
            }else{
                $("#header").removeClass('invalido');
                $("#resultHeader").text("");   
            }
        }
        if (caso==0){
            javascript:$('#plantillaForm').submit();
        }
    }
}

function isJsonH(){
    try {
        JSON.parse($("#header").val());
    }catch (e){
        return 1;
    }        
    return 0;
}

function isJsonR(){
    try {
        JSON.parse($("#request").val());
    }catch (e){
        return 1;
    }        
    return 0;
}

$(document).ready(function(){
    $("#tipoMetodo").change(function(){
     	switch ($("#tipoMetodo").val()){                
     		case "POST": case "PUT":
     			$("#divObject").show();
     		break;
     		case "GET": case "DELETE":
     			$("#divObject").hide();
                $("#request").val("");
     		break;
     		default:
     			$("#divObject").hide();
     		break;
     	}
    });
});